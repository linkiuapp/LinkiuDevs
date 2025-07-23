<x-tenant-admin-layout :store="$store">

@section('title', 'Crear Nueva Sede')

@section('content')
<div class="container-fluid" x-data="locationForm">
    {{-- Sistema de Notificaciones --}}
    @include('tenant-admin::locations.components.notifications')

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Crear Nueva Sede</h1>
        <a href="{{ route('tenant.admin.locations.index', ['store' => $store->slug]) }}" class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-arrow-left-outline class="w-5 h-5" />
            Volver
        </a>
    </div>

    <form action="{{ route('tenant.admin.locations.store', ['store' => $store->slug]) }}" method="POST" @submit.prevent="validateForm($event)">
        @csrf
        
        {{-- Card principal con toda la información --}}
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                <h2 class="text-lg font-semibold text-black-400 mb-0">Información de la Sede</h2>
            </div>
            
            <div class="p-6">
                {{-- Sección: Información Básica --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-info-circle-outline class="w-5 h-5" />
                        Información Básica
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Nombre de la Sede <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('name') border-error-200 @enderror"
                                required>
                            @error('name')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Encargado/Responsable
                            </label>
                            <input type="text"
                                name="manager_name"
                                value="{{ old('manager_name') }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('manager_name') border-error-200 @enderror">
                            @error('manager_name')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Descripción
                            </label>
                            <textarea
                                name="description"
                                rows="3"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('description') border-error-200 @enderror"
                                placeholder="Breve descripción de la sede...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Sede Principal
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        name="is_main"
                                        value="1"
                                        class="sr-only peer" 
                                        {{ old('is_main') ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-white-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                <span class="text-sm text-black-300">Establecer como sede principal</span>
                            </div>
                            <p class="text-xs text-black-200 mt-1">
                                Solo puede haber una sede principal por tienda.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Estado
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        name="is_active"
                                        value="1"
                                        class="sr-only peer" 
                                        {{ old('is_active', true) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-white-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                <span class="text-sm text-black-300">Sede activa</span>
                            </div>
                            <p class="text-xs text-black-200 mt-1">
                                Las sedes inactivas no se mostrarán en el frontend.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Sección: Contacto y Ubicación --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-map-point-outline class="w-5 h-5" />
                        Contacto y Ubicación
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Teléfono <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="phone"
                                value="{{ old('phone') }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('phone') border-error-200 @enderror"
                                placeholder="+57 1 234 5678"
                                required>
                            @error('phone')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                WhatsApp
                            </label>
                            <input type="text"
                                name="whatsapp"
                                value="{{ old('whatsapp') }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('whatsapp') border-error-200 @enderror"
                                placeholder="+57 300 123 4567">
                            @error('whatsapp')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Departamento <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="department"
                                value="{{ old('department') }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('department') border-error-200 @enderror"
                                placeholder="Cundinamarca"
                                required>
                            @error('department')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Ciudad <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="city"
                                value="{{ old('city') }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('city') border-error-200 @enderror"
                                placeholder="Bogotá"
                                required>
                            @error('city')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Dirección <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="address"
                                value="{{ old('address') }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('address') border-error-200 @enderror"
                                placeholder="Calle 123 #45-67, Centro"
                                required>
                            @error('address')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Mensaje de WhatsApp
                            </label>
                            <textarea
                                name="whatsapp_message"
                                rows="2"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('whatsapp_message') border-error-200 @enderror"
                                placeholder="Hola, me interesa conocer más sobre sus productos en [Nombre Sede]">{{ old('whatsapp_message') }}</textarea>
                            <p class="text-xs text-black-200 mt-1">
                                Este mensaje se usará cuando los clientes hagan clic en el botón de WhatsApp.
                            </p>
                            @error('whatsapp_message')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección: Horarios --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-clock-circle-outline class="w-5 h-5" />
                        Horarios de Atención
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white-100">
                            <thead class="bg-white-100">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Día
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Cerrado
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Horario Principal
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Horario Adicional
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white-50 divide-y divide-white-100">
                                @php
                                    $days = [
                                        0 => 'Domingo',
                                        1 => 'Lunes',
                                        2 => 'Martes',
                                        3 => 'Miércoles',
                                        4 => 'Jueves',
                                        5 => 'Viernes',
                                        6 => 'Sábado',
                                    ];
                                    
                                    // Default schedules
                                    $defaultSchedules = [
                                        0 => ['is_closed' => false, 'open_1' => '09:00', 'close_1' => '18:00'], // Sunday
                                        1 => ['is_closed' => false, 'open_1' => '09:00', 'close_1' => '18:00'], // Monday
                                        2 => ['is_closed' => false, 'open_1' => '09:00', 'close_1' => '18:00'], // Tuesday
                                        3 => ['is_closed' => false, 'open_1' => '09:00', 'close_1' => '18:00'], // Wednesday
                                        4 => ['is_closed' => false, 'open_1' => '09:00', 'close_1' => '18:00'], // Thursday
                                        5 => ['is_closed' => false, 'open_1' => '09:00', 'close_1' => '18:00'], // Friday
                                        6 => ['is_closed' => false, 'open_1' => '09:00', 'close_1' => '13:00'], // Saturday
                                    ];
                                @endphp
                                
                                @foreach($days as $dayNum => $dayName)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-black-400">{{ $dayName }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <input type="checkbox" 
                                                    name="day_{{ $dayNum }}_closed"
                                                    id="day_{{ $dayNum }}_closed"
                                                    class="schedule-closed-checkbox"
                                                    data-day="{{ $dayNum }}"
                                                    {{ old("day_{$dayNum}_closed", $defaultSchedules[$dayNum]['is_closed'] ?? flase) ? 'checked' : '' }}>
                                                <label for="day_{{ $dayNum }}_closed" class="ml-2 text-sm text-black-300">
                                                    Cerrado
                                                </label>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2" 
                                                 :class="{ 'opacity-50': document.getElementById('day_{{ $dayNum }}_closed')?.checked }">
                                                <input type="time"
                                                    name="day_{{ $dayNum }}_open_1"
                                                    class="px-2 py-1 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none time-input"
                                                    value="{{ old("day_{$dayNum}_open_1", $defaultSchedules[$dayNum]['open_1'] ?? '') }}"
                                                    data-day="{{ $dayNum }}">
                                                <span class="text-black-300">a</span>
                                                <input type="time"
                                                    name="day_{{ $dayNum }}_close_1"
                                                    class="px-2 py-1 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none time-input"
                                                    value="{{ old("day_{$dayNum}_close_1", $defaultSchedules[$dayNum]['close_1'] ?? '') }}"
                                                    data-day="{{ $dayNum }}">
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2"
                                                 :class="{ 'opacity-50': document.getElementById('day_{{ $dayNum }}_closed')?.checked }">
                                                <input type="time"
                                                    name="day_{{ $dayNum }}_open_2"
                                                    class="px-2 py-1 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none time-input"
                                                    value="{{ old("day_{$dayNum}_open_2", $defaultSchedules[$dayNum]['open_2'] ?? '') }}"
                                                    data-day="{{ $dayNum }}">
                                                <span class="text-black-300">a</span>
                                                <input type="time"
                                                    name="day_{{ $dayNum }}_close_2"
                                                    class="px-2 py-1 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none time-input"
                                                    value="{{ old("day_{$dayNum}_close_2", $defaultSchedules[$dayNum]['close_2'] ?? '') }}"
                                                    data-day="{{ $dayNum }}">
                                            </div>
                                            <p class="text-xs text-black-200 mt-1">
                                                Opcional (ej. horario de tarde)
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Sección: Redes Sociales --}}
                <div>
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-share-circle-outline class="w-5 h-5" />
                        Redes Sociales
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($platforms as $platform => $label)
                            <div>
                                <label class="block text-sm font-semibold text-black-300 mb-2">
                                    {{ $label }}
                                </label>
                                <input type="url"
                                    name="social_{{ $platform }}"
                                    value="{{ old("social_{$platform}") }}"
                                    class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error("social_{$platform}") border-error-200 @enderror"
                                    placeholder="https://{{ $platform }}.com/mi-sede">
                                @error("social_{$platform}")
                                    <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Footer con botones --}}
            <div class="border-t border-white-100 bg-white-50 px-6 py-4">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('tenant.admin.locations.index', ['store' => $store->slug]) }}"
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
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('locationForm', () => ({
        showNotification: false,
        notificationMessage: '',
        notificationType: 'success',
        
        init() {
            // Initialize form behavior
            this.initScheduleCheckboxes();
        },
        
        initScheduleCheckboxes() {
            const self = this;
            document.querySelectorAll('.schedule-closed-checkbox').forEach(checkbox => {
                // Remove old event listeners if any
                const oldListener = checkbox._changeListener;
                if (oldListener) {
                    checkbox.removeEventListener('change', oldListener);
                }
                
                // Create a new event listener
                const newListener = function() {
                    const day = this.dataset.day;
                    const inputs = document.querySelectorAll(`[name^="day_${day}_open"], [name^="day_${day}_close"]`);
                    
                    inputs.forEach(input => {
                        input = this.checked;
                    });
                };
                
                // Store the listener reference for future cleanup
                checkbox._changeListener = newListener;
                
                // Add the event listener
                checkbox.addEventListener('change', newListener);
                
                // Set initial state
                newListener.call(checkbox);
            });
        },
        
        validateForm(event) {
            let valid = true;
            const form = event.target;
            
            // Check if at least one schedule is set for each non-closed day
            document.querySelectorAll('.schedule-closed-checkbox').forEach(checkbox => {
                if (!checkbox.checked) {
                    const day = checkbox.dataset.day;
                    const open1 = form.querySelector(`[name="day_${day}_open_1"]`).value;
                    const close1 = form.querySelector(`[name="day_${day}_close_1"]`).value;
                    
                    if (!open1 || !close1) {
                        valid = false;
                        this.showNotificationMessage(`Por favor, complete el horario principal para ${this.getDayName(day)}`, 'error');
                    }
                }
            });
            
            if (valid) {
                form.submit();
            }
        },
        
        getDayName(dayNum) {
            const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            return days[dayNum] || '';
        },
        
        showNotificationMessage(message, type = 'success') {
            this.notificationMessage = message;
            this.notificationType = type;
            this.showNotification = true;
            
            setTimeout(() => {
                this.showNotification = false;
            }, 5000);
        }
    }));
});
</script>
@endpush
@endsection
</x-tenant-admin-layout>
