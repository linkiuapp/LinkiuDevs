<?php

namespace App\Events;

use App\Shared\Models\Ticket;
use App\Shared\Models\TicketResponse;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketResponseAdded // Broadcasting DESHABILITADO - funciona sin WebSocket
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;
    public $response;

    /**
     * Create a new event instance.
     */
    public function __construct(Ticket $ticket, TicketResponse $response)
    {
        $this->ticket = $ticket;
        $this->response = $response;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            // Canal para SuperLinkiu (todos los super admins)
            new Channel('superlinkiu-notifications'),
            // Canal específico para la tienda
            new Channel('store.' . $this->ticket->store->slug . '.notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.response.added';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'store_slug' => $this->ticket->store->slug,
            'response_from' => $this->response->user->role,
            'message_preview' => \Str::limit($this->response->message, 50),
            'timestamp' => now()->toISOString(),
        ];
    }
}
