<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Invoice;
use App\Services\BillingNotificationService;
use Carbon\Carbon;

class SendBillingNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'billing:send-notifications 
                            {--type= : Notification type (due_reminders|overdue|all)}
                            {--dry-run : Run without sending emails}
                            {--days= : Days to look ahead/back for notifications}';

    /**
     * The console command description.
     */
    protected $description = 'Send billing notifications (reminders, overdue notices)';

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
        $type = $this->option('type') ?? 'all';
        $isDryRun = $this->option('dry-run');
        $days = $this->option('days');

        $this->info('📧 Enviando notificaciones de facturación...');
        
        if ($isDryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se enviarán emails reales');
        }

        $results = [];

        switch ($type) {
            case 'due_reminders':
                $results['reminders'] = $this->sendDueReminders($isDryRun, $days);
                break;
            case 'overdue':
                $results['overdue'] = $this->sendOverdueNotifications($isDryRun, $days);
                break;
            case 'all':
            default:
                $results['reminders'] = $this->sendDueReminders($isDryRun, $days);
                $results['overdue'] = $this->sendOverdueNotifications($isDryRun, $days);
                break;
        }

        $this->displayResults($results, $isDryRun);
    }

    /**
     * Send due date reminders
     */
    private function sendDueReminders(bool $isDryRun, ?int $days = null): array
    {
        $lookAheadDays = $days ?? 7;
        $targetDate = now()->addDays($lookAheadDays)->toDateString();
        
        $this->info("📅 Buscando facturas que vencen el {$targetDate} ({$lookAheadDays} días):");

        $upcomingInvoices = Invoice::where('status', 'pending')
            ->where('due_date', $targetDate)
            ->with(['store', 'plan'])
            ->get();

        if ($upcomingInvoices->isEmpty()) {
            $this->line("✅ No hay facturas que venzan en {$lookAheadDays} días");
            return ['count' => 0, 'sent' => 0, 'errors' => 0];
        }

        $sent = 0;
        $errors = 0;

        $this->info("📬 Enviando recordatorios a {$upcomingInvoices->count()} tiendas:");

        foreach ($upcomingInvoices as $invoice) {
            $this->line("  📧 {$invoice->store->name} - #{$invoice->invoice_number} - {$invoice->getFormattedAmount()}");
            
            if (!$isDryRun) {
                try {
                    $this->notificationService->sendInvoiceDueReminder($invoice);
                    $sent++;
                    $this->line("    ✅ Recordatorio enviado");
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("    ❌ Error: " . $e->getMessage());
                    \Log::error('Error enviando recordatorio de vencimiento', [
                        'invoice_id' => $invoice->id,
                        'store_id' => $invoice->store_id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $sent++;
            }
        }

        return [
            'count' => $upcomingInvoices->count(),
            'sent' => $sent,
            'errors' => $errors
        ];
    }

    /**
     * Send overdue notifications
     */
    private function sendOverdueNotifications(bool $isDryRun, ?int $days = null): array
    {
        $lookBackDays = $days ?? 1; // Por defecto, facturas vencidas ayer
        $fromDate = now()->subDays($lookBackDays)->toDateString();
        $toDate = now()->toDateString();
        
        $this->info("⚠️  Buscando facturas vencidas entre {$fromDate} y {$toDate}:");

        $overdueInvoices = Invoice::where('status', 'overdue')
            ->whereBetween('due_date', [$fromDate, $toDate])
            ->with(['store', 'plan'])
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->line("✅ No hay facturas recién vencidas para notificar");
            return ['count' => 0, 'sent' => 0, 'errors' => 0];
        }

        $sent = 0;
        $errors = 0;

        $this->info("⚠️  Enviando notificaciones de vencimiento a {$overdueInvoices->count()} tiendas:");

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = $invoice->due_date->diffInDays(now());
            $this->line("  📧 {$invoice->store->name} - #{$invoice->invoice_number} - {$daysOverdue} días vencida");
            
            if (!$isDryRun) {
                try {
                    $this->notificationService->sendInvoiceOverdueNotification($invoice);
                    $sent++;
                    $this->line("    ✅ Notificación de vencimiento enviada");
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("    ❌ Error: " . $e->getMessage());
                    \Log::error('Error enviando notificación de vencimiento', [
                        'invoice_id' => $invoice->id,
                        'store_id' => $invoice->store_id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $sent++;
            }
        }

        return [
            'count' => $overdueInvoices->count(),
            'sent' => $sent,
            'errors' => $errors
        ];
    }

    /**
     * Display results summary
     */
    private function displayResults(array $results, bool $isDryRun): void
    {
        $this->info("\n📊 RESUMEN DE NOTIFICACIONES:");

        if (isset($results['reminders'])) {
            $r = $results['reminders'];
            $this->info("📅 Recordatorios de vencimiento:");
            $this->info("   - Facturas encontradas: {$r['count']}");
            $this->info("   - Emails enviados: {$r['sent']}");
            if ($r['errors'] > 0) {
                $this->error("   - Errores: {$r['errors']}");
            }
        }

        if (isset($results['overdue'])) {
            $o = $results['overdue'];
            $this->info("⚠️  Notificaciones de vencimiento:");
            $this->info("   - Facturas encontradas: {$o['count']}");
            $this->info("   - Emails enviados: {$o['sent']}");
            if ($o['errors'] > 0) {
                $this->error("   - Errores: {$o['errors']}");
            }
        }

        $totalSent = ($results['reminders']['sent'] ?? 0) + ($results['overdue']['sent'] ?? 0);
        $totalErrors = ($results['reminders']['errors'] ?? 0) + ($results['overdue']['errors'] ?? 0);

        $this->info("\n🎯 TOTAL:");
        $this->info("✅ Emails enviados: {$totalSent}");
        
        if ($totalErrors > 0) {
            $this->error("❌ Total de errores: {$totalErrors}");
        }

        if ($isDryRun) {
            $this->warn("\n⚠️  Esto fue una simulación - no se enviaron emails reales");
        } else {
            $this->info("\n🎉 ¡Notificaciones procesadas exitosamente!");
        }
    }
}










