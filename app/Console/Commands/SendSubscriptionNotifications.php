<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Subscription;
use App\Shared\Models\Store;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendSubscriptionNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:send-notifications {--dry-run : Show what notifications would be sent without actually sending them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automatic notifications for subscription renewals and expirations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No emails will be sent');
        }

        $this->info('📧 Processing subscription notifications...');
        
        // Notificaciones a enviar
        $notifications = [
            'renewal_7_days' => $this->processRenewalReminders(7, $isDryRun),
            'renewal_3_days' => $this->processRenewalReminders(3, $isDryRun),
            'renewal_1_day' => $this->processRenewalReminders(1, $isDryRun),
            'expired_subscriptions' => $this->processExpiredSubscriptions($isDryRun),
            'grace_period_ending' => $this->processGracePeriodEnding($isDryRun),
        ];

        // Resumen
        $this->newLine();
        $this->info('📊 Summary:');
        foreach ($notifications as $type => $count) {
            $this->line("  • {$type}: {$count} notifications");
        }

        $totalSent = array_sum($notifications);
        if ($totalSent > 0) {
            $this->info("✅ Total notifications processed: {$totalSent}");
        } else {
            $this->info("✅ No notifications needed at this time");
        }

        return 0;
    }

    /**
     * Process renewal reminders
     */
    private function processRenewalReminders(int $days, bool $isDryRun): int
    {
        $targetDate = now()->addDays($days)->toDateString();
        
        $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('next_billing_date', $targetDate)
            ->with(['store', 'plan'])
            ->get();

        $count = 0;
        
        foreach ($subscriptions as $subscription) {
            if (!$subscription->store) continue;
            
            $storeAdmin = User::where('store_id', $subscription->store_id)
                ->where('role', 'store_admin')
                ->first();
                
            if (!$storeAdmin) continue;

            if ($isDryRun) {
                $this->line("  📅 Would send {$days}-day renewal reminder to: {$storeAdmin->email} ({$subscription->store->name})");
            } else {
                $this->sendRenewalReminderEmail($storeAdmin, $subscription, $days);
                $this->line("  📅 Sent {$days}-day renewal reminder to: {$storeAdmin->email}");
            }
            
            $count++;
        }

        return $count;
    }

    /**
     * Process expired subscriptions
     */
    private function processExpiredSubscriptions(bool $isDryRun): int
    {
        $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('current_period_end', '<', now()->toDateString())
            ->with(['store', 'plan'])
            ->get();

        $count = 0;
        
        foreach ($subscriptions as $subscription) {
            if (!$subscription->store) continue;
            
            $storeAdmin = User::where('store_id', $subscription->store_id)
                ->where('role', 'store_admin')
                ->first();
                
            if (!$storeAdmin) continue;

            if ($isDryRun) {
                $this->line("  ⚠️  Would send expiration notice to: {$storeAdmin->email} ({$subscription->store->name})");
            } else {
                // Marcar como expirada y enviar notificación
                $subscription->update(['status' => Subscription::STATUS_EXPIRED]);
                $this->sendExpirationNoticeEmail($storeAdmin, $subscription);
                $this->line("  ⚠️  Sent expiration notice to: {$storeAdmin->email}");
            }
            
            $count++;
        }

        return $count;
    }

    /**
     * Process grace period ending notifications
     */
    private function processGracePeriodEnding(bool $isDryRun): int
    {
        $subscriptions = Subscription::where('status', Subscription::STATUS_GRACE_PERIOD)
            ->whereNotNull('grace_period_end')
            ->where('grace_period_end', '<=', now()->addDays(3)->toDateString())
            ->where('grace_period_end', '>', now()->toDateString())
            ->with(['store', 'plan'])
            ->get();

        $count = 0;
        
        foreach ($subscriptions as $subscription) {
            if (!$subscription->store) continue;
            
            $storeAdmin = User::where('store_id', $subscription->store_id)
                ->where('role', 'store_admin')
                ->first();
                
            if (!$storeAdmin) continue;

            $daysLeft = now()->diffInDays($subscription->grace_period_end);

            if ($isDryRun) {
                $this->line("  🕐 Would send grace period ending notice to: {$storeAdmin->email} ({$daysLeft} days left)");
            } else {
                $this->sendGracePeriodEndingEmail($storeAdmin, $subscription, $daysLeft);
                $this->line("  🕐 Sent grace period ending notice to: {$storeAdmin->email}");
            }
            
            $count++;
        }

        return $count;
    }

    /**
     * Send renewal reminder email
     */
    private function sendRenewalReminderEmail(User $user, Subscription $subscription, int $days): void
    {
        $data = [
            'user_name' => $user->name,
            'store_name' => $subscription->store->name,
            'days' => $days,
            'amount' => $subscription->next_billing_amount,
            'plan_name' => $subscription->plan->name,
            'billing_url' => route('tenant.admin.billing.index', $subscription->store->slug)
        ];

        try {
            // Use EmailService to send renewal reminder
            \App\Services\EmailService::sendWithTemplate(
                'subscription_renewal_reminder',
                [$user->email],
                $data
            );
            $this->logNotification('renewal_reminder', $user->email, $data);
        } catch (\Exception $e) {
            $this->error("Failed to send renewal reminder: " . $e->getMessage());
        }
    }

    /**
     * Send expiration notice email
     */
    private function sendExpirationNoticeEmail(User $user, Subscription $subscription): void
    {
        $data = [
            'user_name' => $user->name,
            'store_name' => $subscription->store->name,
            'plan_name' => $subscription->plan->name,
            'billing_url' => route('tenant.admin.billing.index', $subscription->store->slug)
        ];

        try {
            // Use EmailService to send expiration notice
            \App\Services\EmailService::sendWithTemplate(
                'subscription_expiration_notice',
                [$user->email],
                $data
            );
            $this->logNotification('expiration_notice', $user->email, $data);
        } catch (\Exception $e) {
            $this->error("Failed to send expiration notice: " . $e->getMessage());
        }
    }

    /**
     * Send grace period ending email
     */
    private function sendGracePeriodEndingEmail(User $user, Subscription $subscription, int $daysLeft): void
    {
        $data = [
            'user_name' => $user->name,
            'store_name' => $subscription->store->name,
            'days_left' => $daysLeft,
            'plan_name' => $subscription->plan->name,
            'billing_url' => route('tenant.admin.billing.index', $subscription->store->slug)
        ];

        try {
            // Use EmailService to send grace period ending notice
            \App\Services\EmailService::sendWithTemplate(
                'subscription_grace_period_ending',
                [$user->email],
                $data
            );
            $this->logNotification('grace_period_ending', $user->email, $data);
        } catch (\Exception $e) {
            $this->error("Failed to send grace period ending notice: " . $e->getMessage());
        }
    }

    /**
     * Log notification (placeholder for actual email sending)
     */
    private function logNotification(string $type, string $email, array $data): void
    {
        // Send email using the new template system
        try {
            $templateKey = match($type) {
                'renewal_reminder' => 'subscription_renewal_reminder',
                'expiration_notice' => 'subscription_expiration_notice',
                'grace_period_ending' => 'subscription_grace_period_ending',
                default => null
            };

            if ($templateKey) {
                $templateData = $this->prepareTemplateData($type, $data);
                
                \App\Services\EmailService::sendWithTemplate(
                    $templateKey,
                    [$email],
                    $templateData
                );

                \Log::info("Subscription notification sent via template", [
                    'type' => $type,
                    'template_key' => $templateKey,
                    'email' => $email,
                    'store_id' => $data['store']->id ?? null,
                    'store_name' => $data['store']->name ?? null,
                    'sent_at' => now()
                ]);
            } else {
                \Log::warning("No template found for subscription notification type: {$type}");
            }

        } catch (\Exception $e) {
            \Log::error("Error sending subscription notification", [
                'type' => $type,
                'email' => $email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Prepare template data for email sending
     */
    private function prepareTemplateData(string $type, array $data): array
    {
        $baseData = [
            'user_name' => $data['user']->name ?? 'Usuario',
            'store_name' => $data['store']->name ?? 'Tu tienda',
            'plan_name' => $data['subscription']->plan->name ?? 'Plan actual',
            'billing_url' => config('app.url') . '/billing' // Adjust as needed
        ];

        return match($type) {
            'renewal_reminder' => array_merge($baseData, [
                'days' => $data['days_until_expiry'] ?? 7,
                'amount' => number_format($data['subscription']->next_billing_amount ?? 0, 2)
            ]),
            'expiration_notice' => $baseData,
            'grace_period_ending' => array_merge($baseData, [
                'days_left' => $data['grace_days_left'] ?? 3
            ]),
            default => $baseData
        };
    }

    /**
     * Get email subject based on notification type
     */
    private function getEmailSubject(string $type, array $data): string
    {
        return match($type) {
            'renewal_reminder' => "Tu suscripción de {$data['store']->name} se renovará en {$data['days']} día(s)",
            'expiration_notice' => "Tu suscripción de {$data['store']->name} ha expirado",
            'grace_period_ending' => "Tu período de gracia termina en {$data['days_left']} día(s)",
            default => "Notificación de suscripción - {$data['store']->name}"
        };
    }
}
