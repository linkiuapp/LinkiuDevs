<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\PlatformAnnouncement;
use App\Shared\Models\AnnouncementRead;
use App\Shared\Models\Store;
use App\Shared\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(Request $request): View
    {
        $query = PlatformAnnouncement::with('reads');

        // Filtros
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
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

        // Estadísticas
        $stats = [
            'total' => PlatformAnnouncement::count(),
            'active' => PlatformAnnouncement::where('is_active', true)->count(),
            'banners' => PlatformAnnouncement::where('show_as_banner', true)->count(),
            'critical' => PlatformAnnouncement::byType('critical')->count(),
        ];

        return view('superlinkiu::announcements.index', compact('announcements', 'stats'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create(): View
    {
        $plans = Plan::where('is_active', true)->orderBy('name')->get();
        $stores = Store::where('status', 'active')->orderBy('name')->get();

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
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'banner_link' => 'nullable|url',
            'target_plans' => 'nullable|array',
            'target_plans.*' => 'string|in:explorer,master,legend',
            'target_stores' => 'nullable|array',
            'target_stores.*' => 'integer|exists:stores,id',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
            'is_active' => 'boolean',
            'show_popup' => 'boolean',
            'send_email' => 'boolean',
            'auto_mark_read_after' => 'nullable|integer|min:1|max:365'
        ]);

        // Procesar imagen del banner si existe
        if ($request->hasFile('banner_image')) {
            try {
                $bannerFilename = $this->handleBannerUpload($request->file('banner_image'));
                $validated['banner_image'] = $bannerFilename;
                $validated['show_as_banner'] = true;
            } catch (\Exception $e) {
                return back()->withErrors(['banner_image' => $e->getMessage()])->withInput();
            }
        }

        // Crear anuncio
        $announcement = PlatformAnnouncement::create($validated);

        return redirect()
            ->route('superlinkiu.announcements.index')
            ->with('success', 'Anuncio creado exitosamente.');
    }

    /**
     * Display the specified announcement.
     */
    public function show(PlatformAnnouncement $announcement): View
    {
        $announcement->load('reads.store');

        // Estadísticas de lectura
        $totalStores = Store::where('status', 'active')->count();
        $readCount = $announcement->reads()->count();
        $unreadCount = $totalStores - $readCount;

        $readStats = [
            'total_stores' => $totalStores,
            'read_count' => $readCount,
            'unread_count' => $unreadCount,
            'read_percentage' => $totalStores > 0 ? round(($readCount / $totalStores) * 100, 1) : 0
        ];

        return view('superlinkiu::announcements.show', compact('announcement', 'readStats'));
    }

    /**
     * Show the form for editing the announcement.
     */
    public function edit(PlatformAnnouncement $announcement): View
    {
        $announcement->load('reads.store');

        $plans = Plan::where('is_active', true)->orderBy('name')->get();
        $stores = Store::where('status', 'active')->orderBy('name')->get();

        // Estadísticas de lectura
        $totalStores = Store::where('status', 'active')->count();
        $readCount = $announcement->reads()->count();
        $unreadCount = $totalStores - $readCount;

        $readStats = [
            'total_stores' => $totalStores,
            'read_count' => $readCount,
            'unread_count' => $unreadCount,
            'read_percentage' => $totalStores > 0 ? round(($readCount / $totalStores) * 100, 1) : 0
        ];

        return view('superlinkiu::announcements.edit', compact('announcement', 'plans', 'stores', 'readStats'));
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
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'banner_link' => 'nullable|url',
            'target_plans' => 'nullable|array',
            'target_plans.*' => 'string|in:explorer,master,legend',
            'target_stores' => 'nullable|array',
            'target_stores.*' => 'integer|exists:stores,id',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
            'is_active' => 'boolean',
            'show_popup' => 'boolean',
            'send_email' => 'boolean',
            'auto_mark_read_after' => 'nullable|integer|min:1|max:365',
            'remove_banner' => 'boolean'
        ]);

        // Manejar eliminación de banner
        if ($request->boolean('remove_banner') && $announcement->banner_image) {
            $this->deleteBannerImage($announcement->banner_image);
            $validated['banner_image'] = null;
            $validated['show_as_banner'] = false;
        }

        // Procesar nueva imagen del banner si existe
        if ($request->hasFile('banner_image')) {
            try {
                // Eliminar banner anterior si existe
                if ($announcement->banner_image) {
                    $this->deleteBannerImage($announcement->banner_image);
                }

                $bannerFilename = $this->handleBannerUpload($request->file('banner_image'));
                $validated['banner_image'] = $bannerFilename;
                $validated['show_as_banner'] = true;
            } catch (\Exception $e) {
                return back()->withErrors(['banner_image' => $e->getMessage()])->withInput();
            }
        }

        // Actualizar anuncio
        $announcement->update($validated);

        return redirect()
            ->route('superlinkiu.announcements.show', $announcement)
            ->with('success', 'Anuncio actualizado exitosamente.');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(PlatformAnnouncement $announcement): RedirectResponse
    {
        // Eliminar imagen del banner si existe
        if ($announcement->banner_image) {
            $this->deleteBannerImage($announcement->banner_image);
        }

        $announcement->delete();

        return redirect()
            ->route('superlinkiu.announcements.index')
            ->with('success', 'Anuncio eliminado exitosamente.');
    }

    /**
     * Toggle active status of announcement.
     */
    public function toggleActive(PlatformAnnouncement $announcement): JsonResponse
    {
        $announcement->update(['is_active' => !$announcement->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $announcement->is_active,
            'message' => $announcement->is_active ? 'Anuncio activado' : 'Anuncio desactivado'
        ]);
    }

    /**
     * Duplicate announcement.
     */
    public function duplicate(PlatformAnnouncement $announcement): RedirectResponse
    {
        $newAnnouncement = $announcement->replicate();
        $newAnnouncement->title = $announcement->title . ' (Copia)';
        $newAnnouncement->is_active = false;
        $newAnnouncement->published_at = null;

        // Duplicar banner si existe
        if ($announcement->banner_image) {
            try {
                $newBannerFilename = $this->duplicateBannerImage($announcement->banner_image);
                $newAnnouncement->banner_image = $newBannerFilename;
            } catch (\Exception $e) {
                // Si falla la duplicación del banner, crear sin banner
                $newAnnouncement->banner_image = null;
                $newAnnouncement->show_as_banner = false;
            }
        }

        $newAnnouncement->save();

        return redirect()
            ->route('superlinkiu.announcements.edit', $newAnnouncement)
            ->with('success', 'Anuncio duplicado exitosamente. Puedes editarlo antes de activarlo.');
    }

    /**
     * Handle banner image upload with validation.
     */
    private function handleBannerUpload($file): string
    {
        // Validar dimensiones (1570x300px)
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo[0] !== 1570 || $imageInfo[1] !== 300) {
            throw new \Exception('La imagen del banner debe ser exactamente 1570x300 píxeles.');
        }

        // Generar nombre único
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        // ✅ Crear directorio si no existe
        $destinationPath = public_path('storage/announcements/banners');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // ✅ GUARDAR con move() - Método estándar obligatorio
        $file->move($destinationPath, $filename);

        return $filename;
    }

    /**
     * Delete banner image from storage.
     */
    private function deleteBannerImage(string $filename): void
    {
        // ✅ Eliminar archivo usando método estándar
        $filePath = public_path('storage/announcements/banners/' . $filename);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Duplicate banner image.
     */
    private function duplicateBannerImage(string $originalFilename): string
    {
        // ✅ Verificar archivo original usando método estándar
        $originalPath = public_path('storage/announcements/banners/' . $originalFilename);
        
        if (!file_exists($originalPath)) {
            throw new \Exception('Archivo original no encontrado');
        }

        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $newFilename = time() . '_' . Str::random(10) . '.' . $extension;
        $newPath = public_path('storage/announcements/banners/' . $newFilename);

        // ✅ Crear directorio si no existe
        $destinationDir = dirname($newPath);
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        // ✅ Copiar archivo usando método estándar
        copy($originalPath, $newPath);

        return $newFilename;
    }
} 