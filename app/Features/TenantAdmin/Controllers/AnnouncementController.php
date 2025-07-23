<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\PlatformAnnouncement;
use App\Shared\Models\AnnouncementRead;
use App\Shared\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements for the store.
     */
    public function index(Request $request): View
    {
        $store = $request->route('store');
        
        // Obtener plan de la tienda para filtros
        $storePlan = strtolower($store->plan->name ?? $store->plan->slug ?? 'explorer');
        
        $query = PlatformAnnouncement::active()
            ->forPlan($storePlan)
            ->forStore($store->id)
            ->with('reads');

        // Filtros
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->whereDoesntHave('reads', function ($q) use ($store) {
                    $q->where('store_id', $store->id);
                });
            } elseif ($request->status === 'read') {
                $query->whereHas('reads', function ($q) use ($store) {
                    $q->where('store_id', $store->id);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Ordenar por prioridad y fecha
        $announcements = $query->ordered()->paginate(15)->withQueryString();

        // Marcar como visto (no como leído) los anuncios mostrados
        foreach ($announcements as $announcement) {
            if (!$announcement->isReadBy($store->id)) {
                $announcement->viewed_by_store = true;
            } else {
                $announcement->viewed_by_store = false;
            }
        }

        // Estadísticas
        $stats = [
            'total' => PlatformAnnouncement::active()
                ->forPlan($storePlan)
                ->forStore($store->id)
                ->count(),
            'unread' => AnnouncementRead::getUnreadCount($store->id),
            'banners' => PlatformAnnouncement::active()
                ->forPlan($storePlan)
                ->forStore($store->id)
                ->banners()
                ->count(),
            'critical' => PlatformAnnouncement::active()
                ->forPlan($storePlan)
                ->forStore($store->id)
                ->byType('critical')
                ->count(),
        ];

        return view('tenant-admin::announcements.index', compact('announcements', 'stats', 'store'));
    }

    /**
     * Display the specified announcement.
     */
    public function show(Request $request, $storeSlug, PlatformAnnouncement $announcement): View
    {
        $store = $request->route('store');
        
        // Verificar que el anuncio sea visible para esta tienda
        $storePlan = strtolower($store->plan->name ?? $store->plan->slug ?? 'explorer');
        
        if (!$announcement->is_active || 
            ($announcement->target_plans && !in_array($storePlan, $announcement->target_plans)) ||
            ($announcement->target_stores && !in_array($store->id, $announcement->target_stores))) {
            abort(404);
        }

        // Marcar como leído automáticamente al ver el detalle
        if (!$announcement->isReadBy($store->id)) {
            $announcement->markAsReadBy($store->id);
        }

        return view('tenant-admin::announcements.show', compact('announcement', 'store'));
    }

    /**
     * Mark announcement as read.
     */
    public function markAsRead(Request $request, $storeSlug, PlatformAnnouncement $announcement): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            $announcement->markAsReadBy($store->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Anuncio marcado como leído'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como leído'
            ], 500);
        }
    }

    /**
     * Mark all announcements as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            $storePlan = strtolower($store->plan->name ?? $store->plan->slug ?? 'explorer');
            
            $announcements = PlatformAnnouncement::active()
                ->forPlan($storePlan)
                ->forStore($store->id)
                ->whereDoesntHave('reads', function ($q) use ($store) {
                    $q->where('store_id', $store->id);
                })
                ->get();

            foreach ($announcements as $announcement) {
                $announcement->markAsReadBy($store->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Todos los anuncios marcados como leídos',
                'count' => $announcements->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar todos como leídos'
            ], 500);
        }
    }

    /**
     * Get banners for dashboard carousel.
     */
    public function getBanners(Request $request): JsonResponse
    {
        $store = $request->route('store');
        $storePlan = strtolower($store->plan->name ?? $store->plan->slug ?? 'explorer');
        
        $banners = PlatformAnnouncement::active()
            ->forPlan($storePlan)
            ->forStore($store->id)
            ->banners()
            ->ordered()
            ->get()
            ->map(function ($announcement) use ($store) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'type' => $announcement->type,
                    'type_icon' => $announcement->type_icon,
                    'banner_image_url' => $announcement->banner_image_url,
                    'banner_link' => $announcement->banner_link,
                    'show_url' => route('tenant.admin.announcements.show', [
                        'store' => $store->slug,
                        'announcement' => $announcement->id
                    ])
                ];
            });

        return response()->json($banners);
    }

    /**
     * Get notification count.
     */
    public function getNotificationCount(Request $request): JsonResponse
    {
        $store = $request->route('store');
        $count = AnnouncementRead::getUnreadCount($store->id);
        
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent announcements for notifications.
     */
    public function getRecentAnnouncements(Request $request): JsonResponse
    {
        $store = $request->route('store');
        $storePlan = strtolower($store->plan->name ?? $store->plan->slug ?? 'explorer');
        
        $announcements = PlatformAnnouncement::active()
            ->forPlan($storePlan)
            ->forStore($store->id)
            ->whereDoesntHave('reads', function ($q) use ($store) {
                $q->where('store_id', $store->id);
            })
            ->ordered()
            ->limit(5)
            ->get()
            ->map(function ($announcement) use ($store) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'type' => $announcement->type,
                    'type_icon' => $announcement->type_icon,
                    'type_color' => $announcement->type_color,
                    'content' => \Str::limit($announcement->content, 100),
                    'published_at' => $announcement->published_at ? 
                        $announcement->published_at->format('d/m/Y H:i') : 'Inmediato',
                    'show_url' => route('tenant.admin.announcements.show', [
                        'store' => $store->slug,
                        'announcement' => $announcement->id
                    ])
                ];
            });

        return response()->json($announcements);
    }
} 