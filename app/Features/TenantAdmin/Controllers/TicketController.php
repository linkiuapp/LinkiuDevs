<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Ticket;
use App\Shared\Models\TicketResponse;
use App\Shared\Models\TicketCategory;
use App\Shared\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Events\TicketResponseAdded;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->route('store');
        
        $query = Ticket::with(['categoryModel', 'responses.user'])
            ->where('store_id', $store->id)
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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Paginación
        $perPage = $request->get('per_page', 15);
        $tickets = $query->paginate($perPage)->withQueryString();

        // Categorías para filtros
        $categories = TicketCategory::getActiveOptions();

        // Estadísticas básicas
        $stats = [
            'total' => Ticket::where('store_id', $store->id)->count(),
            'open' => Ticket::where('store_id', $store->id)->open()->count(),
            'in_progress' => Ticket::where('store_id', $store->id)->inProgress()->count(),
            'resolved' => Ticket::where('store_id', $store->id)->resolved()->count(),
        ];

        return view('tenant-admin::tickets.index', compact('tickets', 'categories', 'stats', 'store'));
    }

    public function show(Request $request, $storeSlug, Ticket $ticket): View
    {
        $store = $request->route('store');
        
        // Verificar que el ticket pertenece a la tienda actual
        if ($ticket->store_id !== $store->id) {
            abort(404);
        }

        $ticket->load(['categoryModel', 'responses.user']);
        
        // Marcar como visto por store admin
        $ticket->markAsViewed();

        return view('tenant-admin::tickets.show', compact('ticket', 'store'));
    }

    public function create(Request $request): View
    {
        $store = $request->route('store');
        $categories = TicketCategory::getActiveOptions();

        return view('tenant-admin::tickets.create', compact('categories', 'store'));
    }

    public function store(Request $request): RedirectResponse
    {
        $store = $request->route('store');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'required|exists:ticket_categories,slug',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,txt', // 5MB máximo
        ]);

        // Agregar información automática
        $userInfo = [
            'browser' => $this->getBrowserInfo($request->header('User-Agent')),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'plan' => $store->plan->name ?? 'Sin plan',
            'created_by' => auth()->user()->name ?? 'Usuario',
        ];

        $validated['store_id'] = $store->id;
        $validated['metadata'] = $userInfo;
        $validated['status'] = 'open';

        $ticket = Ticket::create($validated);

        // Procesar archivos adjuntos si los hay
        if ($request->hasFile('attachments')) {
            $attachments = $this->handleAttachments($request->file('attachments'), $ticket, $store->slug);
            $ticket->update(['attachments' => $attachments]);
        }

        return redirect()
            ->route('tenant.admin.tickets.show', ['store' => $store->slug, 'ticket' => $ticket])
            ->with('success', 'Ticket creado exitosamente.');
    }

    public function addResponse(Request $request, $storeSlug, Ticket $ticket): RedirectResponse
    {
        $store = $request->route('store');
        
        // Verificar que el ticket pertenece a la tienda actual
        if ($ticket->store_id !== $store->id) {
            abort(404);
        }

        // Verificar que el ticket esté abierto o en progreso
        if (!in_array($ticket->status, ['open', 'in_progress'])) {
            return redirect()->back()->with('error', 'No se puede responder a un ticket cerrado o resuelto.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,txt',
        ]);

        // Crear la respuesta
        $response = $ticket->addResponse(
            auth()->id(),
            $validated['message'],
            'response',
            true // Todas las respuestas de store_admin son públicas
        );

        // Disparar evento para WebSocket
        \Log::info('🚀 Dispatching TicketResponseAdded event from TenantAdmin', [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'response_from' => auth()->user()->role,
            'store_slug' => $ticket->store->slug
        ]);
        
        event(new TicketResponseAdded($ticket, $response));

        // Procesar archivos adjuntos si los hay
        if ($request->hasFile('attachments')) {
            $attachments = $this->handleAttachments($request->file('attachments'), $ticket, $store->slug, $response->id);
            $response->update(['attachments' => $attachments]);
        }

        return redirect()
            ->route('tenant.admin.tickets.show', ['store' => $storeSlug, 'ticket' => $ticket])
            ->with('success', 'Respuesta agregada exitosamente.');
    }

    public function updateStatus(Request $request, $storeSlug, Ticket $ticket): JsonResponse
    {
        $store = $request->route('store');
        
        if ($ticket->store_id !== $store->id) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];

        switch ($newStatus) {
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

        // Crear nota del cambio
        $ticket->addResponse(
            auth()->id(),
            "Estado cambiado de '{$ticket->getStatusLabelAttribute()}' a '{$ticket->fresh()->getStatusLabelAttribute()}'",
            'status_change',
            false
        );

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado exitosamente',
            'status' => $ticket->fresh()->status,
            'status_label' => $ticket->fresh()->status_label,
        ]);
    }

    private function handleAttachments(array $files, Ticket $ticket, string $storeSlug, ?int $responseId = null): array
    {
        $attachments = [];
        $maxFiles = 3;

        foreach (array_slice($files, 0, $maxFiles) as $file) {
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = "tickets/{$storeSlug}/{$ticket->id}";
            
            if ($responseId) {
                $path .= "/responses/{$responseId}";
            }

            // Guardar archivo temporalmente en local y luego mover a S3 manualmente
            $tempPath = sys_get_temp_dir() . '/' . $filename;
            if (!move_uploaded_file($file->getPathname(), $tempPath)) {
                throw new \Exception('Error moviendo archivo temporal');
            }
            
            // Por ahora guardamos en local en lugar de S3 para evitar finfo
            $localPath = storage_path('app/tickets/' . $path);
            if (!file_exists($localPath)) {
                mkdir($localPath, 0755, true);
            }
            
            $finalPath = $localPath . '/' . $filename;
            if (!copy($tempPath, $finalPath)) {
                throw new \Exception('Error guardando archivo');
            }
            
            // Limpiar archivo temporal
            unlink($tempPath);
            
            $filePath = 'tickets/' . $path . '/' . $filename;

            // Obtener MIME type de forma segura (sin finfo)
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
                'zip' => 'application/zip',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

            $attachments[] = [
                'original_name' => $file->getClientOriginalName(),
                'filename' => $filename,
                'path' => $filePath,
                'size' => $file->getSize(),
                'mime_type' => $mimeType,
            ];
        }

        return $attachments;
    }

    private function getBrowserInfo(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Desconocido';
        }

        if (str_contains($userAgent, 'Chrome')) {
            preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches);
            return 'Chrome ' . ($matches[1] ?? 'desconocido');
        }

        if (str_contains($userAgent, 'Firefox')) {
            preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches);
            return 'Firefox ' . ($matches[1] ?? 'desconocido');
        }

        if (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) {
            preg_match('/Version\/([0-9.]+)/', $userAgent, $matches);
            return 'Safari ' . ($matches[1] ?? 'desconocido');
        }

        if (str_contains($userAgent, 'Edge')) {
            preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches);
            return 'Edge ' . ($matches[1] ?? 'desconocido');
        }

        return 'Otro navegador';
    }
}
