<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Invoice;
use App\Services\BillingNotificationService;
use Carbon\Carbon;

class UpdateOverdueInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'billing:update-overdue {--dry-run : Run without making changes} {--send-notifications : Send overdue notifications}';

    /**
     * The console command description.
     */
    protected $description = 'Update overdue invoice statuses and send notifications';

    protected $notificationService;

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
        $sendNotifications = $this->option('send-notifications');

        $this->info('⏰ Actualizando estado de facturas vencidas...');
        
        if ($isDryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios reales');
        }

        // 1. Actualizar facturas pendientes que ya vencieron
        $overdueCount = $this->updateOverdueInvoices($isDryRun);

        // 2. Enviar recordatorios antes del vencimiento (7 días)
        $reminderCount = $this->sendDueReminders($isDryRun, $sendNotifications);

        // 3. Enviar notificaciones de facturas recién vencidas
        $notificationCount = $this->sendOverdueNotifications($isDryRun, $sendNotifications);

        // Mostrar resumen
        $this->info("\n📊 RESUMEN:");
        $this->info("🔄 Facturas marcadas como vencidas: {$overdueCount}");
        
        if ($sendNotifications) {
            $this->info("📧 Recordatorios de vencimiento enviados: {$reminderCount}");
            $this->info("⚠️  Notificaciones de facturas vencidas enviadas: {$notificationCount}");
        }

        if ($isDryRun) {
            $this->warn("⚠️  Esto fue una simulación - no se guardaron cambios");
        } else {
            $this->info("🎉 ¡Proceso completado exitosamente!");
        }
    }

    /**
     * Update pending invoices that are now overdue
     */
    private function updateOverdueInvoices(bool $isDryRun): int
    {
        $pendingOverdue = Invoice::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->with(['store', 'plan'])
            ->get();

        if ($pendingOverdue->isEmpty()) {
            $this->line("✅ No hay facturas pendientes que hayan vencido");
            return 0;
        }

        $this->info("📋 Encontradas {$pendingOverdue->count()} facturas vencidas:");

        foreach ($pendingOverdue as $invoice) {
            $daysOverdue = $invoice->due_date->diffInDays(now());
            
            $this->line("  ⏰ #{$invoice->invoice_number} - {$invoice->store->name} - {$daysOverdue} días vencida");
            
            if (!$isDryRun) {
                $invoice->update(['status' => 'overdue']);
                
                // Log para auditoría
                \Log::info('Factura marcada como vencida', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'store_id' => $invoice->store_id,
                    'days_overdue' => $daysOverdue
                ]);
            }
        }

        return $pendingOverdue->count();
    }

    /**
     * Send due date reminders (7 days before due)
     */
    private function sendDueReminders(bool $isDryRun, bool $sendNotifications): int
    {
        if (!$sendNotifications) {
            return 0;
        }

        $reminderDate = now()->addDays(7)->toDateString();
        
        $upcomingDue = Invoice::where('status', 'pending')
            ->where('due_date', $reminderDate)
            ->with(['store', 'plan'])
            ->get();

        if ($upcomingDue->isEmpty()) {
            $this->line("📧 No hay facturas que venzan en 7 días");
            return 0;
        }

        $this->info("📧 Enviando recordatorios para {$upcomingDue->count()} facturas que vencen en 7 días:");

        $sent = 0;
        foreach ($upcomingDue as $invoice) {
            $this->line("  📬 Recordatorio a {$invoice->store->name} - #{$invoice->invoice_number}");
            
            if (!$isDryRun) {
                try {
                    $this->notificationService->sendInvoiceDueReminder($invoice);
                    $sent++;
                } catch (\Exception $e) {
                    $this->error("    ❌ Error enviando recordatorio: " . $e->getMessage());
                    \Log::error('Error enviando recordatorio de factura', [
                        'invoice_id' => $invoice->id,
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
     * Send overdue notifications for recently overdue invoices
     */
    private function sendOverdueNotifications(bool $isDryRun, bool $sendNotifications): int
    {
        if (!$sendNotifications) {
            return 0;
        }

        // Facturas que vencieron hoy (recién marcadas como overdue)
        $recentlyOverdue = Invoice::where('status', 'overdue')
            ->where('due_date', now()->toDateString())
            ->with(['store', 'plan'])
            ->get();

        if ($recentlyOverdue->isEmpty()) {
            $this->line("⚠️  No hay facturas recién vencidas hoy");
            return 0;
        }

        $this->info("⚠️  Enviando notificaciones de vencimiento para {$recentlyOverdue->count()} facturas:");

        $sent = 0;
        foreach ($recentlyOverdue as $invoice) {
            $this->line("  📬 Notificación de vencimiento a {$invoice->store->name} - #{$invoice->invoice_number}");
            
            if (!$isDryRun) {
                try {
                    $this->notificationService->sendInvoiceOverdueNotification($invoice);
                    $sent++;
                } catch (\Exception $e) {
                    $this->error("    ❌ Error enviando notificación: " . $e->getMessage());
                    \Log::error('Error enviando notificación de factura vencida', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $sent++;
            }
        }

        return $sent;
    }
}

