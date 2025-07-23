<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Location;
use App\Features\TenantAdmin\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    protected $locationService;
    
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }
    
    /**
     * Display a listing of the locations.
     */
    public function index(Request $request)
    {
        // Get current store from view shared data (set by middleware)
        $store = view()->shared('currentStore');
        
        // Get locations with filtering
        $query = $store->locations();
        
        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Search by name, city, or manager
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }
        
        // Order by main location first, then by name
        $query->orderByDesc('is_main')->orderBy('name');
        
        // Get paginated results
        $locations = $query->paginate(10)->withQueryString();
        
        // Calculate plan limits
        $maxLocations = $this->locationService->getMaxLocations($store);
        $currentCount = $store->locations()->count();
        $remainingSlots = $this->locationService->getRemainingLocationSlots($store);
        
        // Calculate current status for each location
        foreach ($locations as $location) {
            $location->currentStatus = $this->locationService->calculateCurrentStatus($location);
        }
        
        return view('tenant-admin::locations.index', compact(
            'locations',
            'store',
            'maxLocations',
            'currentCount',
            'remainingSlots'
        ));
    }
    
    /**
     * Show the form for creating a new location.
     */
    public function create()
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Check if store can create more locations
        if (!$this->locationService->canCreateLocation($store)) {
            return redirect()->route('tenant.admin.locations.index', ['store' => $store->slug])
                ->with('error', 'Has alcanzado el límite de sedes para tu plan actual.');
        }
        
        // Get available social platforms
        $platforms = \App\Shared\Models\LocationSocialLink::getPlatforms();
        
        return view('tenant-admin::locations.create', compact('store', 'platforms'));
    }
    
    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                Rule::unique('store_locations', 'name')->where('store_id', $store->id)
            ],
            'description' => 'nullable|string|max:500',
            'manager_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'department' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'address' => 'required|string|min:10|max:255',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'whatsapp_message' => 'nullable|string|max:255',
            'schedules' => 'nullable|array',
            'social_links' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Check if store can create more locations
        if (!$this->locationService->canCreateLocation($store)) {
            return redirect()->route('tenant.admin.locations.index', ['store' => $store->slug])
                ->with('error', 'Has alcanzado el límite de sedes para tu plan actual.');
        }
        
        try {
            // Add store_id to data
            $data = $validator->validated();
            $data['store_id'] = $store->id;
            
            // Process schedules from request
            $schedules = [];
            for ($i = 0; $i <= 6; $i++) {
                $dayKey = "day_{$i}";
                $schedules[$i] = [
                    'is_closed' => $request->has("{$dayKey}_closed"),
                    'open_time_1' => $request->input("{$dayKey}_open_1"),
                    'close_time_1' => $request->input("{$dayKey}_close_1"),
                    'open_time_2' => $request->input("{$dayKey}_open_2"),
                    'close_time_2' => $request->input("{$dayKey}_close_2"),
                ];
            }
            $data['schedules'] = $schedules;
            
            // Process social links from request
            $socialLinks = [];
            foreach (\App\Shared\Models\LocationSocialLink::getPlatforms() as $platform => $label) {
                $socialLinks[$platform] = $request->input("social_{$platform}");
            }
            $data['social_links'] = $socialLinks;
            
            // Create location with schedules and social links
            $location = $this->locationService->createLocationWithSchedules($data);
            
            return redirect()->route('tenant.admin.locations.index', ['store' => $store->slug])
                ->with('success', 'Sede creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear la sede: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display the specified location.
     */
    public function show(Request $request, $store, Location $location)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure location belongs to current store
        if ($location->store_id !== $store->id) {
            abort(404);
        }
        
        // Load relationships
        $location->load(['schedules', 'socialLinks']);
        
        // Calculate current status
        $location->currentStatus = $this->locationService->calculateCurrentStatus($location);
        
        // Get schedules organized by day
        $schedulesByDay = $location->schedules->keyBy('day_of_week');
        
        // Get social links organized by platform
        $socialLinksByPlatform = $location->socialLinks->keyBy('platform');
        
        // Get available social platforms
        $platforms = \App\Shared\Models\LocationSocialLink::getPlatforms();
        
        return view('tenant-admin::locations.show', compact(
            'location',
            'store',
            'schedulesByDay',
            'socialLinksByPlatform',
            'platforms'
        ));
    }
    
    /**
     * Show the form for editing the specified location.
     */
    public function edit(Request $request, $store, Location $location)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure location belongs to current store
        if ($location->store_id !== $store->id) {
            abort(404);
        }
        
        // Load relationships
        $location->load(['schedules', 'socialLinks']);
        
        // Get schedules organized by day
        $schedulesByDay = $location->schedules->keyBy('day_of_week');
        
        // Get social links organized by platform
        $socialLinksByPlatform = $location->socialLinks->keyBy('platform');
        
        // Get available social platforms
        $platforms = \App\Shared\Models\LocationSocialLink::getPlatforms();
        
        return view('tenant-admin::locations.edit', compact(
            'location',
            'store',
            'schedulesByDay',
            'socialLinksByPlatform',
            'platforms'
        ));
    }
    
    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, $store, Location $location)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure location belongs to current store
        if ($location->store_id !== $store->id) {
            abort(404);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                Rule::unique('store_locations', 'name')
                    ->where('store_id', $store->id)
                    ->ignore($location->id)
            ],
            'description' => 'nullable|string|max:500',
            'manager_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'department' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'address' => 'required|string|min:10|max:255',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'whatsapp_message' => 'nullable|string|max:255',
            'schedules' => 'nullable|array',
            'social_links' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            // Process data
            $data = $validator->validated();
            
            // Process schedules from request
            $schedules = [];
            for ($i = 0; $i <= 6; $i++) {
                $dayKey = "day_{$i}";
                $schedules[$i] = [
                    'is_closed' => $request->has("{$dayKey}_closed"),
                    'open_time_1' => $request->input("{$dayKey}_open_1"),
                    'close_time_1' => $request->input("{$dayKey}_close_1"),
                    'open_time_2' => $request->input("{$dayKey}_open_2"),
                    'close_time_2' => $request->input("{$dayKey}_close_2"),
                ];
            }
            $data['schedules'] = $schedules;
            
            // Process social links from request
            $socialLinks = [];
            foreach (\App\Shared\Models\LocationSocialLink::getPlatforms() as $platform => $label) {
                $socialLinks[$platform] = $request->input("social_{$platform}");
            }
            $data['social_links'] = $socialLinks;
            
            // Update location with schedules and social links
            $location = $this->locationService->updateLocationWithSchedules($location, $data);
            
            return redirect()->route('tenant.admin.locations.index', ['store' => $store->slug, 'location' => $location->id])
                ->with('success', 'Sede actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar la sede: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Remove the specified location from storage.
     */
    public function destroy(Request $request, $store, Location $location)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure location belongs to current store
        if ($location->store_id !== $store->id) {
            abort(404);
        }
        
        try {
            // Check if this is the main location and there are other active locations
            if ($location->is_main && $store->activeLocations()->where('id', '!=', $location->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar la sede principal. Primero debes establecer otra sede como principal.'
                ], 422);
            }
            
            // Delete location (will cascade to schedules and social links)
            $location->delete();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sede eliminada exitosamente.'
                ]);
            }
            
            return redirect()->route('tenant.admin.locations.index', ['store' => $store->slug])
                ->with('success', 'Sede eliminada exitosamente.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la sede: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al eliminar la sede: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle the active status of a location.
     */
    public function toggleStatus(Request $request, $store, Location $location)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure location belongs to current store
        if ($location->store_id !== $store->id) {
            abort(404);
        }
        
        try {
            // Check if this is the main location and we're trying to deactivate it
            if ($location->is_main && $location->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes desactivar la sede principal. Primero debes establecer otra sede como principal.'
                ], 422);
            }
            
            // Toggle status
            $location->is_active = !$location->is_active;
            $location->save();
            
            $statusText = $location->is_active ? 'activada' : 'desactivada';
            
            return response()->json([
                'success' => true,
                'is_active' => $location->is_active,
                'message' => "Sede {$statusText} exitosamente."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado de la sede: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Set a location as the main location.
     */
    public function setAsMain(Request $request, $store, Location $location)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure location belongs to current store
        if ($location->store_id !== $store->id) {
            abort(404);
        }
        
        try {
            // Check if location is active
            if (!$location->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes establecer una sede inactiva como principal.'
                ], 422);
            }
            
            // Set as main
            $this->locationService->setMainLocation($location);
            
            return response()->json([
                'success' => true,
                'message' => 'Sede establecida como principal exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al establecer la sede como principal: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Increment WhatsApp clicks counter.
     */
    public function incrementWhatsAppClicks(Request $request, $store, Location $location)
    {
        try {
            $location->incrementWhatsAppClicks();
            
            return response()->json([
                'success' => true,
                'clicks' => $location->whatsapp_clicks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al incrementar contador: ' . $e->getMessage()
            ], 500);
        }
    }
}