<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Ticket;
use App\Shared\Models\TicketResponse;
use App\Shared\Models\Store;
use App\Shared\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Events\TicketResponseAdded;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ticket::with(['store', 'assignedTo', 'responses.user'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('store', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Paginación
        $perPage = $request->get('per_page', 15);
        $tickets = $query->paginate($perPage)->withQueryString();

        // Datos para filtros
        $stores = Store::orderBy('name')->get();
        $admins = User::where('role', 'super_admin')->orderBy('name')->get();

        // Estadísticas
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::open()->count(),
            'in_progress' => Ticket::inProgress()->count(),
            'resolved' => Ticket::resolved()->count(),
            'urgent' => Ticket::byPriority('urgent')->whereIn('status', ['open', 'in_progress'])->count(),
            'overdue' => Ticket::whereIn('status', ['open', 'in_progress'])
                ->get()
                ->filter(fn($ticket) => $ticket->is_overdue)
                ->count(),
        ];

        return view('superlinkiu::tickets.index', compact('tickets', 'stores', 'admins', 'stats'));
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['store', 'assignedTo', 'responses.user']);
        
        // Marcar como visto por super admin
        $ticket->markAsViewed();
        
        // Marcar como visto si no está asignado
        if (!$ticket->assigned_to && $ticket->status === 'open') {
            $ticket->markAsInProgress(auth()->id());
        }

        $admins = User::where('role', 'super_admin')->orderBy('name')->get();

        return view('superlinkiu::tickets.show', compact('ticket', 'admins'));
    }

    public function create(): View
    {
        $stores = Store::orderBy('name')->get();
        $admins = User::where('role', 'super_admin')->orderBy('name')->get();

        return view('superlinkiu::tickets.create', compact('stores', 'admins'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:technical,billing,general,feature_request',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket = Ticket::create($validated);

        // Si se asigna a alguien, cambiar estado
        if ($ticket->assigned_to) {
            $ticket->markAsInProgress($ticket->assigned_to);
        }

        // Send ticket creation notification email
        $this->sendTicketCreatedNotification($ticket);

        return redirect()
            ->route('superlinkiu.tickets.show', $ticket)
            ->with('success', 'Ticket creado exitosamente.');
    }

    public function edit(Ticket $ticket): View
    {
        $stores = Store::orderBy('name')->get();
        $admins = User::where('role', 'super_admin')->orderBy('name')->get();

        return view('superlinkiu::tickets.edit', compact('ticket', 'stores', 'admins'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:technical,billing,general,feature_request',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldAssignedTo = $ticket->assigned_to;
        $ticket->update($validated);

        // Si cambió la asignación
        if ($oldAssignedTo != $ticket->assigned_to) {
            if ($ticket->assigned_to && $ticket->status === 'open') {
                $ticket->markAsInProgress($ticket->assigned_to);
            }
        }

        return redirect()
            ->route('superlinkiu.tickets.show', $ticket)
            ->with('success', 'Ticket actualizado exitosamente.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();

        return redirect()
            ->route('superlinkiu.tickets.index')
            ->with('success', 'Ticket eliminado exitosamente.');
    }

    // Métodos para respuestas
    public function addResponse(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'response_type' => 'required|in:response,internal_note,status_change',
            'is_public' => 'boolean',
            'change_status' => 'nullable|in:open,in_progress,resolved,closed',
        ]);

        $isPublic = $request->boolean('is_public', true);
        
        // Crear respuesta
        $response = $ticket->addResponse(
            auth()->id(),
            $validated['message'],
            $validated['response_type'],
            $isPublic
        );

        // Disparar evento para WebSocket
        \Log::info('🚀 Dispatching TicketResponseAdded event', [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'response_from' => auth()->user()->role,
            'store_slug' => $ticket->store->slug
        ]);
        
        event(new TicketResponseAdded($ticket, $response));

        // Cambiar estado si se especificó
        if ($request->filled('change_status')) {
            $newStatus = $request->change_status;
            
            switch ($newStatus) {
                case 'in_progress':
                    $ticket->markAsInProgress(auth()->id());
                    break;
                case 'resolved':
                    $ticket->markAsResolved(auth()->id());
                    break;
                case 'closed':
                    $ticket->markAsClosed(auth()->id());
                    break;
                case 'open':
                    $ticket->update(['status' => 'open']);
                    break;
            }
        }

        // Send ticket response notification email if response is public
        if ($isPublic) {
            $this->sendTicketResponseNotification($ticket, $response);
        }

        return redirect()
            ->route('superlinkiu.tickets.show', $ticket)
            ->with('success', 'Respuesta agregada exitosamente.');
    }

    // Métodos AJAX para cambios rápidos
    public function updateStatus(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];

        switch ($newStatus) {
            case 'in_progress':
                $ticket->markAsInProgress(auth()->id());
                break;
            case 'resolved':
                $ticket->markAsResolved(auth()->id());
                break;
            case 'closed':
                $ticket->markAsClosed(auth()->id());
                break;
            default:
                $ticket->update(['status' => $newStatus]);
                break;
        }

        // Crear nota interna del cambio
        $oldStatusLabel = $ticket->getStatusLabelAttribute();
        $ticket->addResponse(
            auth()->id(),
            "Estado cambiado de '{$oldStatusLabel}' a '{$ticket->fresh()->getStatusLabelAttribute()}'",
            'status_change',
            false
        );

        // Enviar notificación de actualización
        $this->sendTicketUpdateNotification($ticket, "Estado cambiado de '{$oldStatusLabel}' a '{$ticket->fresh()->getStatusLabelAttribute()}'");

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado exitosamente',
            'status' => $ticket->fresh()->status,
            'status_label' => $ticket->fresh()->status_label,
        ]);
    }

    public function assign(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldAssignedTo = $ticket->assigned_to;
        $newAssignedTo = $validated['assigned_to'];

        if ($newAssignedTo) {
            $ticket->assignTo($newAssignedTo);
            $assignedUser = User::find($newAssignedTo);
            $message = "Ticket asignado a {$assignedUser->name}";
        } else {
            $ticket->update(['assigned_to' => null]);
            $message = "Ticket desasignado";
        }

        // Crear nota interna del cambio
        $ticket->addResponse(
            auth()->id(),
            $message,
            'status_change',
            false
        );

        // Enviar notificación de actualización
        $this->sendTicketUpdateNotification($ticket, $message);

        return response()->json([
            'success' => true,
            'message' => 'Asignación actualizada exitosamente',
            'assigned_to' => $ticket->fresh()->assigned_to,
            'assigned_name' => $ticket->fresh()->assignedTo?->name,
        ]);
    }

    public function updatePriority(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $oldPriority = $ticket->priority_label;
        $ticket->update(['priority' => $validated['priority']]);
        $newPriority = $ticket->fresh()->priority_label;

        // Crear nota interna del cambio
        $ticket->addResponse(
            auth()->id(),
            "Prioridad cambiada de '{$oldPriority}' a '{$newPriority}'",
            'status_change',
            false
        );

        // Enviar notificación de actualización
        $this->sendTicketUpdateNotification($ticket, "Prioridad cambiada de '{$oldPriority}' a '{$newPriority}'");

        return response()->json([
            'success' => true,
            'message' => 'Prioridad actualizada exitosamente',
            'priority' => $ticket->fresh()->priority,
            'priority_label' => $ticket->fresh()->priority_label,
        ]);
    }

    // Estadísticas para dashboard
    public function getStats(): JsonResponse
    {
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::open()->count(),
            'in_progress' => Ticket::inProgress()->count(),
            'resolved' => Ticket::resolved()->count(),
            'closed' => Ticket::closed()->count(),
            'urgent' => Ticket::byPriority('urgent')->whereIn('status', ['open', 'in_progress'])->count(),
            'overdue' => Ticket::whereIn('status', ['open', 'in_progress'])
                ->get()
                ->filter(fn($ticket) => $ticket->is_overdue)
                ->count(),
            'unassigned' => Ticket::unassigned()->whereIn('status', ['open', 'in_progress'])->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Send ticket created notification email
     */
    private function sendTicketCreatedNotification(Ticket $ticket): void
    {
        try {
            // Get store admin email
            $storeAdminEmail = $ticket->store->admin_email ?? $ticket->store->email;
            
            if ($storeAdminEmail) {
                \App\Jobs\SendEmailJob::dispatch(
                    'template',
                    $storeAdminEmail,
                    [
                        'template_key' => 'ticket_created',
                        'variables' => [
                            'ticket_id' => $ticket->ticket_number,
                            'ticket_subject' => $ticket->title,
                            'customer_name' => $ticket->store->name,
                            'status' => $ticket->status_label
                        ]
                    ]
                );
            }

            // Also notify support team
            $supportEmail = \App\Services\EmailService::getContextEmail('support');
            if ($supportEmail && $supportEmail !== $storeAdminEmail) {
                \App\Jobs\SendEmailJob::dispatch(
                    'template',
                    $supportEmail,
                    [
                        'template_key' => 'ticket_created',
                        'variables' => [
                            'ticket_id' => $ticket->ticket_number,
                            'ticket_subject' => $ticket->title,
                            'customer_name' => $ticket->store->name,
                            'status' => $ticket->status_label
                        ]
                    ]
                );
            }

        } catch (\Exception $e) {
            \Log::error('Error sending ticket created notification', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send ticket response notification email
     */
    private function sendTicketResponseNotification(Ticket $ticket, TicketResponse $response): void
    {
        try {
            // Get store admin email
            $storeAdminEmail = $ticket->store->admin_email ?? $ticket->store->email;
            
            if ($storeAdminEmail) {
                \App\Jobs\SendEmailJob::dispatch(
                    'template',
                    $storeAdminEmail,
                    [
                        'template_key' => 'ticket_response',
                        'variables' => [
                            'ticket_id' => $ticket->ticket_number,
                            'ticket_subject' => $ticket->title,
                            'customer_name' => $ticket->store->name,
                            'response' => $response->message,
                            'status' => $ticket->status_label
                        ]
                    ]
                );
            }

        } catch (\Exception $e) {
            \Log::error('Error sending ticket response notification', [
                'ticket_id' => $ticket->id,
                'response_id' => $response->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send ticket update notification email
     */
    private function sendTicketUpdateNotification(Ticket $ticket, string $updateDescription): void
    {
        try {
            // Get store admin email
            $storeAdminEmail = $ticket->store->admin_email ?? $ticket->store->email;
            
            if ($storeAdminEmail) {
                \App\Jobs\SendEmailJob::dispatch(
                    'template',
                    $storeAdminEmail,
                    [
                        'template_key' => 'ticket_updated',
                        'variables' => [
                            'ticket_id' => $ticket->ticket_number,
                            'ticket_subject' => $ticket->title,
                            'customer_name' => $ticket->store->name,
                            'update_description' => $updateDescription,
                            'status' => $ticket->status_label,
                            'priority' => $ticket->priority_label,
                            'ticket_url' => route('superlinkiu.tickets.show', $ticket)
                        ]
                    ]
                );
            }

        } catch (\Exception $e) {
            \Log::error('Error sending ticket update notification', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
        }
    }
} 