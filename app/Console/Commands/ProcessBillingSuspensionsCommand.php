<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Invoice;
use App\Shared\Models\Store;
use App\Shared\Models\Subscription;
use App\Services\BillingNotificationService;
use Carbon\Carbon;

class ProcessBillingSuspensionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'billing:process-suspensions {--dry-run : Run without making changes} {--force-days= : Override suspension days threshold}';

    /**
     * The console command description.
     */
    protected $description = 'Process automatic billing suspensions for overdue invoices';

    protected $notificationService;

    // Configuración de suspensiones
    private const FIRST_WARNING_DAYS = 7;   // Primer aviso
    private const SECOND_WARNING_DAYS = 15; // Segundo aviso
    private const FINAL_WARNING_DAYS = 22;  // Último aviso
    private const SUSPENSION_DAYS = 30;     // Suspensión automática

    public function __construct(BillingNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $forceDays = $this->option('force-days');

        $this->info('🚫 Procesando suspensiones automáticas por falta de pago...');
        
        if ($isDryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios reales');
        }

        if ($forceDays) {
            $this->warn("⚠️  Usando {$forceDays} días como umbral de suspensión (en lugar de " . self::SUSPENSION_DAYS . ")");
        }

        // 1. Procesar avisos escalonados
        $warningCounts = $this->processWarnings($isDryRun);

        // 2. Procesar suspensiones automáticas
        $suspensionCount = $this->processAutomaticSuspensions($isDryRun, $forceDays);

        // Mostrar resumen
        $this->info("\n📊 RESUMEN:");
        $this->info("📧 Primeros avisos enviados (7 días): {$warningCounts['first']}");
        $this->info("⚠️  Segundos avisos enviados (15 días): {$warningCounts['second']}");
        $this->info("🔥 Últimos avisos enviados (22 días): {$warningCounts['final']}");
        $this->info("🚫 Tiendas suspendidas (30 días): {$suspensionCount}");

        if ($isDryRun) {
            $this->warn("⚠️  Esto fue una simulación - no se realizaron cambios");
        } else {
            $this->info("🎉 ¡Proceso completado exitosamente!");
        }
    }

    /**
     * Process escalating warnings for overdue invoices
     */
    private function processWarnings(bool $isDryRun): array
    {
        $counts = ['first' => 0, 'second' => 0, 'final' => 0];

        // Primer aviso (7 días vencidas)
        $counts['first'] = $this->sendWarnings(self::FIRST_WARNING_DAYS, 'first_warning', $isDryRun);
        
        // Segundo aviso (15 días vencidas)
        $counts['second'] = $this->sendWarnings(self::SECOND_WARNING_DAYS, 'second_warning', $isDryRun);
        
        // Último aviso (22 días vencidas)
        $counts['final'] = $this->sendWarnings(self::FINAL_WARNING_DAYS, 'final_warning', $isDryRun);

        return $counts;
    }

    /**
     * Send warnings for specific days overdue
     */
    private function sendWarnings(int $daysOverdue, string $warningType, bool $isDryRun): int
    {
        $targetDate = now()->subDays($daysOverdue)->toDateString();
        
        $overdueInvoices = Invoice::where('status', 'overdue')
            ->where('due_date', $targetDate)
            ->with(['store', 'plan', 'store.subscription'])
            ->get();

        if ($overdueInvoices->isEmpty()) {
            return 0;
        }

        $warningLabel = match($warningType) {
            'first_warning' => 'primer aviso',
            'second_warning' => 'segundo aviso',
            'final_warning' => 'último aviso',
            default => $warningType
        };

        $this->info("📧 Enviando {$warningLabel} a {$overdueInvoices->count()} tiendas ({$daysOverdue} días vencidas):");

        $sent = 0;
        foreach ($overdueInvoices as $invoice) {
            // Verificar que la tienda siga activa
            if ($invoice->store->status !== 'active') {
                continue;
            }

            $this->line("  📬 {$warningLabel} → {$invoice->store->name} (#{$invoice->invoice_number})");
            
            if (!$isDryRun) {
                try {
                    $this->notificationService->sendBillingWarning($invoice, $warningType, $daysOverdue);
                    $sent++;

                    // Log para auditoría
                    \Log::info('Aviso de factura vencida enviado', [
                        'invoice_id' => $invoice->id,
                        'store_id' => $invoice->store_id,
                        'warning_type' => $warningType,
                        'days_overdue' => $daysOverdue
                    ]);
                } catch (\Exception $e) {
                    $this->error("    ❌ Error enviando aviso: " . $e->getMessage());
                    \Log::error('Error enviando aviso de factura vencida', [
                        'invoice_id' => $invoice->id,
                        'warning_type' => $warningType,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Process automatic suspensions for severely overdue invoices
     */
    private function processAutomaticSuspensions(bool $isDryRun, ?int $forceDays = null): int
    {
        $suspensionDays = $forceDays ?? self::SUSPENSION_DAYS;
        $suspensionDate = now()->subDays($suspensionDays)->toDateString();
        
        $severlyOverdue = Invoice::where('status', 'overdue')
            ->where('due_date', '<=', $suspensionDate)
            ->with(['store', 'store.subscription'])
            ->get();

        // Filtrar solo tiendas que aún estén activas
        $suspendableStores = $severlyOverdue->filter(function ($invoice) {
            return $invoice->store && $invoice->store->status === 'active';
        });

        if ($suspendableStores->isEmpty()) {
            $this->line("🚫 No hay tiendas que requieran suspensión automática");
            return 0;
        }

        $this->info("🚫 Suspendiendo {$suspendableStores->count()} tiendas por facturas con +{$suspensionDays} días vencidas:");

        $suspended = 0;
        foreach ($suspendableStores as $invoice) {
            $store = $invoice->store;
            $daysOverdue = $invoice->due_date->diffInDays(now());
            
            $this->line("  🚫 Suspendiendo: {$store->name} (#{$invoice->invoice_number} - {$daysOverdue} días vencida)");
            
            if (!$isDryRun) {
                try {
                    $this->suspendStore($store, $invoice, $daysOverdue);
                    $suspended++;
                } catch (\Exception $e) {
                    $this->error("    ❌ Error suspendiendo tienda: " . $e->getMessage());
                    \Log::error('Error suspendiendo tienda por falta de pago', [
                        'store_id' => $store->id,
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $suspended++;
            }
        }

        return $suspended;
    }

    /**
     * Suspend a store for non-payment
     */
    private function suspendStore(Store $store, Invoice $invoice, int $daysOverdue): void
    {
        // 1. Cambiar estado de la tienda
        $store->update([
            'status' => 'suspended',
            'suspension_reason' => 'billing_overdue',
            'suspended_at' => now(),
            'suspended_invoice_id' => $invoice->id
        ]);

        // 2. Suspender suscripción si existe
        if ($store->subscription) {
            $store->subscription->update([
                'status' => Subscription::STATUS_SUSPENDED,
                'grace_period_end' => null,
                'metadata' => array_merge($store->subscription->metadata ?? [], [
                    'suspended_for' => 'billing_overdue',
                    'suspended_at' => now(),
                    'suspended_invoice' => $invoice->invoice_number,
                    'days_overdue' => $daysOverdue
                ])
            ]);
        }

        // 3. Enviar notificación de suspensión
        $this->notificationService->sendSuspensionNotification($store, $invoice, $daysOverdue);

        // 4. Log para auditoría
        \Log::warning('Tienda suspendida por falta de pago', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'days_overdue' => $daysOverdue,
            'suspended_at' => now()
        ]);

        $this->line("    ✅ Tienda {$store->name} suspendida exitosamente");
    }
}

