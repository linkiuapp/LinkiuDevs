<?php

namespace App\Features\SuperLinkiu\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Bulk Import Completed Event
 * Broadcasts when a bulk import operation is completed
 * Requirements: 7.4, 7.5
 */
class BulkImportCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $batchId;
    public array $results;
    public int $userId;

    public function __construct(string $batchId, array $results, int $userId)
    {
        $this->batchId = $batchId;
        $this->results = $results;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("bulk-import.{$this->userId}")
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'batch_id' => $this->batchId,
            'results' => [
                'total_processed' => $this->results['total_processed'],
                'success_count' => $this->results['success_count'],
                'error_count' => $this->results['error_count'],
                'status' => $this->results['status'] ?? 'completed',
                'message' => $this->results['message'] ?? 'Import completed'
            ],
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'import.completed';
    }
}