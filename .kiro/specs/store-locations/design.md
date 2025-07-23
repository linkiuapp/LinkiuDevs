# Design Document - Store Locations

## Overview

The Store Locations system provides comprehensive management of physical store locations within the multi-tenant architecture. It implements a flexible scheduling system, social media integration, and plan-based limitations while maintaining the established feature-based architecture pattern.

## Architecture

### Database Schema

#### Primary Tables

**store_locations**
```sql
CREATE TABLE store_locations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    store_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    manager_name VARCHAR(255) NULL,
    phone VARCHAR(20) NOT NULL,
    whatsapp VARCHAR(20) NULL,
    department VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    whatsapp_message TEXT NULL,
    whatsapp_clicks INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
    INDEX idx_store_active (store_id, is_active),
    INDEX idx_store_main (store_id, is_main),
    UNIQUE KEY unique_store_name (store_id, name)
);
```

**location_schedules**
```sql
CREATE TABLE location_schedules (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    location_id BIGINT UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL, -- 0=Sunday, 1=Monday, ..., 6=Saturday
    is_closed BOOLEAN DEFAULT FALSE,
    open_time_1 TIME NULL,
    close_time_1 TIME NULL,
    open_time_2 TIME NULL, -- Optional additional schedule
    close_time_2 TIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (location_id) REFERENCES store_locations(id) ON DELETE CASCADE,
    INDEX idx_location_day (location_id, day_of_week),
    UNIQUE KEY unique_location_day (location_id, day_of_week)
);
```

**location_social_links**
```sql
CREATE TABLE location_social_links (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    location_id BIGINT UNSIGNED NOT NULL,
    platform ENUM('instagram', 'facebook', 'tiktok', 'youtube', 'whatsapp', 'linkiu') NOT NULL,
    url VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (location_id) REFERENCES store_locations(id) ON DELETE CASCADE,
    INDEX idx_location_platform (location_id, platform),
    UNIQUE KEY unique_location_platform (location_id, platform)
);
```

### Plan Limitations

```php
// In Plan model or configuration
'max_locations' => [
    'Explorer' => 1,
    'Master' => 5,
    'Legend' => 10
]
```

## Components and Interfaces

### Models

#### Location Model
```php
<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\BelongsToTenant;

class Location extends Model
{
    use BelongsToTenant;
    
    protected $table = 'store_locations';
    
    protected $fillable = [
        'store_id', 'name', 'description', 'manager_name',
        'phone', 'whatsapp', 'department', 'city', 'address',
        'is_main', 'is_active', 'whatsapp_message'
    ];
    
    protected $casts = [
        'is_main' => 'boolean',
        'is_active' => 'boolean',
        'whatsapp_clicks' => 'integer'
    ];
    
    // Relationships
    public function store() { return $this->belongsTo(Store::class); }
    public function schedules() { return $this->hasMany(LocationSchedule::class); }
    public function socialLinks() { return $this->hasMany(LocationSocialLink::class); }
    
    // Business Logic Methods
    public function isCurrentlyOpen(): bool;
    public function getCurrentStatus(): string;
    public function getNextStatusChange(): ?Carbon;
    public function setAsMain(): void;
    public function incrementWhatsAppClicks(): void;
}
```

#### LocationSchedule Model
```php
<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class LocationSchedule extends Model
{
    protected $fillable = [
        'location_id', 'day_of_week', 'is_closed',
        'open_time_1', 'close_time_1', 'open_time_2', 'close_time_2'
    ];
    
    protected $casts = [
        'is_closed' => 'boolean',
        'day_of_week' => 'integer'
    ];
    
    public function location() { return $this->belongsTo(Location::class); }
    
    public function isOpenAt(Carbon $time): bool;
    public function hasAdditionalSchedule(): bool;
}
```

#### LocationSocialLink Model
```php
<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class LocationSocialLink extends Model
{
    protected $fillable = ['location_id', 'platform', 'url'];
    
    public function location() { return $this->belongsTo(Location::class); }
    
    public static function getPlatforms(): array {
        return ['instagram', 'facebook', 'tiktok', 'youtube', 'whatsapp', 'linkiu'];
    }
    
    public function getPlatformIcon(): string;
    public function getPlatformColor(): string;
}
```

### Controllers

#### LocationController
```php
<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Location;
use App\Features\TenantAdmin\Services\LocationService;

class LocationController extends Controller
{
    public function __construct(private LocationService $locationService) {}
    
    public function index(Request $request);
    public function create();
    public function store(Request $request);
    public function show(Location $location);
    public function edit(Location $location);
    public function update(Request $request, Location $location);
    public function destroy(Location $location);
    public function toggleStatus(Location $location);
    public function setAsMain(Location $location);
}
```

### Services

#### LocationService
```php
<?php

namespace App\Features\TenantAdmin\Services;

class LocationService
{
    public function canCreateLocation(Store $store): bool;
    public function getRemainingLocationSlots(Store $store): int;
    public function createLocationWithSchedules(array $data): Location;
    public function updateLocationWithSchedules(Location $location, array $data): Location;
    public function setMainLocation(Location $location): void;
    public function calculateCurrentStatus(Location $location): array;
    public function validateScheduleOverlap(array $schedules): bool;
}
```

## Data Models

### Location Status Calculation

```php
public function getCurrentStatus(): string
{
    $now = now();
    $dayOfWeek = $now->dayOfWeek;
    $currentTime = $now->format('H:i:s');
    
    $schedule = $this->schedules()->where('day_of_week', $dayOfWeek)->first();
    
    if (!$schedule || $schedule->is_closed) {
        return 'closed';
    }
    
    // Check primary schedule
    if ($this->isTimeInRange($currentTime, $schedule->open_time_1, $schedule->close_time_1)) {
        return 'open';
    }
    
    // Check additional schedule if exists
    if ($schedule->open_time_2 && $schedule->close_time_2) {
        if ($this->isTimeInRange($currentTime, $schedule->open_time_2, $schedule->close_time_2)) {
            return 'open';
        }
    }
    
    return 'closed';
}
```

### Schedule Validation

```php
public function validateSchedules(array $schedules): array
{
    $errors = [];
    
    foreach ($schedules as $dayOfWeek => $schedule) {
        if ($schedule['is_closed']) continue;
        
        // Validate primary schedule
        if ($schedule['open_time_1'] >= $schedule['close_time_1']) {
            $errors["schedules.{$dayOfWeek}.primary"] = 'Opening time must be before closing time';
        }
        
        // Validate additional schedule if provided
        if ($schedule['open_time_2'] && $schedule['close_time_2']) {
            if ($schedule['open_time_2'] >= $schedule['close_time_2']) {
                $errors["schedules.{$dayOfWeek}.additional"] = 'Additional opening time must be before closing time';
            }
            
            // Check for overlap (optional - based on business rules)
            if ($this->schedulesOverlap($schedule)) {
                $errors["schedules.{$dayOfWeek}.overlap"] = 'Schedules cannot overlap';
            }
        }
    }
    
    return $errors;
}
```

## Error Handling

### Plan Limit Validation
```php
public function validatePlanLimits(Store $store): void
{
    $currentCount = $store->locations()->count();
    $maxLocations = $store->plan->max_locations;
    
    if ($currentCount >= $maxLocations) {
        throw new PlanLimitExceededException(
            "You have reached your plan limit of {$maxLocations} locations. Upgrade your plan to add more locations."
        );
    }
}
```

### Main Location Management
```php
public function handleMainLocationDeactivation(Location $location): void
{
    if (!$location->is_main) return;
    
    $activeLocations = $location->store->locations()
        ->where('is_active', true)
        ->where('id', '!=', $location->id)
        ->get();
    
    if ($activeLocations->count() === 1) {
        // Auto-assign if only one active location remains
        $activeLocations->first()->setAsMain();
    } elseif ($activeLocations->count() > 1) {
        // Require manual selection
        throw new MainLocationSelectionRequiredException(
            'Please select a new main location before deactivating the current main location.'
        );
    }
}
```

## Testing Strategy

### Unit Tests
- Location model business logic (status calculation, main location management)
- LocationService methods (plan validation, schedule validation)
- Schedule overlap detection
- Social link validation

### Feature Tests
- CRUD operations with plan limits
- Main location assignment and deactivation
- Schedule creation and validation
- Social links management
- WhatsApp click tracking

### Integration Tests
- Complete location creation flow with schedules and social links
- Plan limit enforcement across different plan types
- Main location management scenarios

## UI Components

### Consistent UI Pattern (Following SuperAdmin Stores Pattern)

#### Main Layout Structure
```blade
{{-- Main Container --}}
<div class="container-fluid" x-data="locationManagement">
    {{-- Modales y Notificaciones --}}
    @include('tenant-admin::locations.components.delete-modal')
    @include('tenant-admin::locations.components.notifications')
    
    {{-- Header y Acciones --}}
    @include('tenant-admin::locations.components.header')
    
    {{-- Filtros --}}
    @include('tenant-admin::locations.components.filters')
    
    {{-- Contenido Principal --}}
    @include('tenant-admin::locations.components.table-view')
    
    {{-- Paginación --}}
    @include('tenant-admin::locations.components.pagination')
</div>
```

#### Card Structure (Forms & Details)
```blade
{{-- Main Card --}}
<div class="bg-white-50 rounded-lg p-0 overflow-hidden">
    {{-- Header --}}
    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
        <h2 class="text-lg font-semibold text-black-400 mb-0">Información de la Sede</h2>
    </div>
    
    {{-- Content --}}
    <div class="p-6">
        {{-- Form sections here --}}
    </div>
    
    {{-- Footer Actions --}}
    <div class="border-t border-white-100 bg-white-50 px-6 py-4">
        <div class="flex justify-between">
            <a href="{{ route('tenant.admin.locations.show', $location) }}"
                class="btn-outline-primary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-eye-outline class="w-5 h-5" />
                Ver Detalles
            </a>
            <div class="flex gap-3">
                <a href="{{ route('tenant.admin.locations.index') }}"
                    class="btn-outline-secondary px-6 py-2 rounded-lg">
                    Cancelar
                </a>
                <button type="submit"
                    class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-diskette-outline class="w-5 h-5" />
                    Guardar Sede
                </button>
            </div>
        </div>
    </div>
</div>
```

#### Form Components
- **Input Fields**: `w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none`
- **Labels**: `block text-sm font-medium text-black-300 mb-2`
- **Required Indicator**: `<span class="text-error-300">*</span>`
- **Error Messages**: `text-xs text-error-300 mt-1`
- **Toggle Switches**: Same pattern as store verification toggle

#### Modal Components
- **Delete Modal**: Exact same structure as stores delete modal
- **Success Notifications**: Toast notifications in top-right corner
- **Confirmation Dialogs**: Same overlay and animation patterns

#### Table/List Components
- **Action Buttons**: 
  - View: `table-action-show` with `<x-solar-eye-outline class="table-action-icon" />`
  - Edit: `table-action-edit` with `<x-solar-pen-2-outline class="table-action-icon" />`
  - Delete: `table-action-delete` with `<x-solar-trash-bin-trash-outline class="table-action-icon" />`

#### Status Indicators
- **Active/Inactive**: Same badge pattern as stores
- **Open/Closed**: 
  - Open: `<span class="badge-soft-success">🟢 Abierto</span>`
  - Closed: `<span class="badge-soft-error">🔴 Cerrado</span>`
  - Temporarily Closed: `<span class="badge-soft-warning">🟡 Cerrado temporalmente</span>`

#### Plan Limit Indicator
```blade
<div class="flex justify-between items-center mb-6">
    <h1 class="text-lg font-bold text-black-400">Gestión de Sedes</h1>
    <div class="flex items-center gap-4">
        <span class="text-sm text-black-300">
            Sedes: <span class="font-semibold">{{ $currentCount }}/{{ $maxLocations }}</span> 
            (Plan {{ $store->plan->name }})
        </span>
        @if($currentCount < $maxLocations)
            <a href="{{ route('tenant.admin.locations.create') }}" 
                class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-add-circle-outline class="w-5 h-5" />
                Crear Sede
            </a>
        @else
            <button disabled 
                class="btn-primary opacity-50 cursor-not-allowed px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-add-circle-outline class="w-5 h-5" />
                Límite Alcanzado
            </button>
        @endif
    </div>
</div>
```

## Performance Considerations

### Database Optimization
- Indexes on frequently queried fields (store_id, is_active, is_main)
- Eager loading of relationships to prevent N+1 queries
- Caching of current status calculations for high-traffic scenarios

### Real-time Status Updates
- Cache location status with appropriate TTL
- Background job to update status changes
- Efficient time-based queries for status calculation

## Security Considerations

### Data Validation
- Strict validation of phone number formats
- URL validation for social media links
- XSS protection for user-generated content (descriptions, manager names)

### Access Control
- Tenant isolation through middleware
- Plan limit enforcement at controller and service levels
- Proper authorization for location management actions