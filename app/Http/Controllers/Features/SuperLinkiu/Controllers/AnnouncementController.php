<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\PlatformAnnouncement;
use App\Shared\Models\Plan;
use App\Shared\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(Request $request): View
    {
        $query = PlatformAnnouncement::query()->with('reads');

        // Filtros
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Ordenar
        $announcements = $query->ordered()->paginate(15)->withQueryString();

        // Estadísticas
        $stats = [
            'total' => PlatformAnnouncement::count(),
            'active' => PlatformAnnouncement::active()->count(),
            'banners' => PlatformAnnouncement::active()->banners()->count(),
            'critical' => PlatformAnnouncement::active()->byType('critical')->count(),
        ];

        return view('superlinkiu::announcements.index', compact('announcements', 'stats'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create(): View
    {
        $plans = Plan::active()->get();
        $stores = Store::orderBy('name')->get();
        
        return view('superlinkiu::announcements.create', compact('plans', 'stores'));
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:critical,important,info',
            'priority' => 'required|integer|min:1|max:10',
            
            // Banner
            'show_as_banner' => 'boolean',
            'banner_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner_link' => 'nullable|url',
            
            // Segmentación
            'target_plans' => 'nullable|array',
            'target_plans.*' => 'string|in:explorer,master,legend',
            'target_stores' => 'nullable|array',
            'target_stores.*' => 'exists:stores,id',
            
            // Fechas
            'published_at' => 'nullable|date|after_or_equal:now',
            'expires_at' => 'nullable|date|after:published_at',
            
            // Comportamiento
            'is_active' => 'boolean',
            'show_popup' => 'boolean',
            'send_email' => 'boolean',
            'auto_mark_read_after' => 'nullable|integer|min:1|max:365',
        ]);

        // Procesar banner si se subió
        $bannerPath = null;
        if ($request->hasFile('banner_image') && $request->boolean('show_as_banner')) {
            $bannerPath = $this->handleBannerUpload($request->file('banner_image'));
        }

        // Preparar datos
        $data = array_merge($validated, [
            'banner_image' => $bannerPath,
            'target_plans' => $request->has('target_plans') ? $validated['target_plans'] : null,
            'target_stores' => $request->has('target_stores') ? $validated['target_stores'] : null,
            'published_at' => $validated['published_at'] ?? now(),
        ]);

        $announcement = PlatformAnnouncement::create($data);

        return redirect()
            ->route('superlinkiu.announcements.show', $announcement)
            ->with('success', 'Anuncio creado exitosamente.');
    }

    /**
     * Display the specified announcement.
     */
    public function show(PlatformAnnouncement $announcement): View
    {
        $announcement->load('reads.store');
        
        // Estadísticas de lectura
        $readStats = [
            'total_stores' => Store::count(),
            'read_count' => $announcement->reads->count(),
            'unread_count' => Store::count() - $announcement->reads->count(),
        ];

        return view('superlinkiu::announcements.show', compact('announcement', 'readStats'));
    }

    /**
     * Show the form for editing the specified announcement.
     */
    public function edit(PlatformAnnouncement $announcement): View
    {
        $plans = Plan::active()->get();
        $stores = Store::orderBy('name')->get();
        
        return view('superlinkiu::announcements.edit', compact('announcement', 'plans', 'stores'));
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, PlatformAnnouncement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:critical,important,info',
            'priority' => 'required|integer|min:1|max:10',
            
            // Banner
            'show_as_banner' => 'boolean',
            'banner_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner_link' => 'nullable|url',
            'remove_banner' => 'boolean',
            
            // Segmentación
            'target_plans' => 'nullable|array',
            'target_plans.*' => 'string|in:explorer,master,legend',
            'target_stores' => 'nullable|array',
            'target_stores.*' => 'exists:stores,id',
            
            // Fechas
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
            
            // Comportamiento
            'is_active' => 'boolean',
            'show_popup' => 'boolean',
            'send_email' => 'boolean',
            'auto_mark_read_after' => 'nullable|integer|min:1|max:365',
        ]);

        // Manejar banner
        $bannerPath = $announcement->banner_image;
        
        if ($request->boolean('remove_banner')) {
            if ($bannerPath) {
                Storage::disk('public')->delete('announcements/banners/' . $bannerPath);
            }
            $bannerPath = null;
        } elseif ($request->hasFile('banner_image')) {
            // Eliminar banner anterior
            if ($bannerPath) {
                Storage::disk('public')->delete('announcements/banners/' . $bannerPath);
            }
            $bannerPath = $this->handleBannerUpload($request->file('banner_image'));
        }

        // Preparar datos
        $data = array_merge($validated, [
            'banner_image' => $bannerPath,
            'target_plans' => $request->has('target_plans') ? $validated['target_plans'] : null,
            'target_stores' => $request->has('target_stores') ? $validated['target_stores'] : null,
        ]);

        $announcement->update($data);

        return redirect()
            ->route('superlinkiu.announcements.show', $announcement)
            ->with('success', 'Anuncio actualizado exitosamente.');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(PlatformAnnouncement $announcement): RedirectResponse
    {
        // Eliminar banner si existe
        if ($announcement->banner_image) {
            Storage::disk('public')->delete('announcements/banners/' . $announcement->banner_image);
        }

        $announcement->delete();

        return redirect()
            ->route('superlinkiu.announcements.index')
            ->with('success', 'Anuncio eliminado exitosamente.');
    }

    /**
     * Handle banner image upload with validation.
     */
    private function handleBannerUpload($file): string
    {
        // Validar dimensiones 320x100
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo[0] !== 320 || $imageInfo[1] !== 100) {
            throw new \Exception('La imagen del banner debe ser exactamente 320x100 píxeles.');
        }

        // Generar nombre único
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        // Guardar en storage/app/public/announcements/banners/
        $file->storeAs('announcements/banners', $filename, 'public');
        
        return $filename;
    }

    /**
     * Toggle announcement active status.
     */
    public function toggleActive(PlatformAnnouncement $announcement): RedirectResponse
    {
        $announcement->update([
            'is_active' => !$announcement->is_active
        ]);

        $status = $announcement->is_active ? 'activado' : 'desactivado';
        
        return redirect()->back()->with('success', "Anuncio {$status} exitosamente.");
    }

    /**
     * Duplicate announcement.
     */
    public function duplicate(PlatformAnnouncement $announcement): RedirectResponse
    {
        $newAnnouncement = $announcement->replicate();
        $newAnnouncement->title = $announcement->title . ' (Copia)';
        $newAnnouncement->is_active = false;
        $newAnnouncement->published_at = now();
        $newAnnouncement->save();

        return redirect()
            ->route('superlinkiu.announcements.edit', $newAnnouncement)
            ->with('success', 'Anuncio duplicado exitosamente.');
    }
}
