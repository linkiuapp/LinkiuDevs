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
 * Bulk Import Progress Updated Event
 * Broadcasts real-time progress updates for bulk import operations
 * Requirements: 7.4, 7.5
 */
class BulkImportProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $batchId;
    public array $progress;
    public int $userId;

    public function __construct(string $batchId, array $progress, int $userId)
    {
        $this->batchId = $batchId;
        $this->progress = $progress;
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
            'progress' => $this->progress,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'progress.updated';
    }
}