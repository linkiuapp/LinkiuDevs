@extends('shared::layouts.tenant-admin')

@section('title', 'Crear Ticket de Soporte')

@section('content')
<div class="container-fluid" x-data="createTicket()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Crear Ticket de Soporte</h1>
            <p class="text-sm text-black-300">Envía tu consulta o problema a nuestro equipo</p>
        </div>
        <a href="{{ route('tenant.admin.tickets.index', ['store' => $store->slug]) }}" 
           class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-arrow-left-outline class="w-4 h-4" />
            Volver
        </a>
    </div>

    <form action="{{ route('tenant.admin.tickets.store', ['store' => $store->slug]) }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf
        
        <!-- Card principal -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-lg font-semibold text-black-400 mb-0">Información del Ticket</h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Columna izquierda -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Categoría *
                            </label>
                            <select name="category" 
                                    class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('category') border-error-300 @enderror"
                                    required>
                                <option value="">Selecciona una categoría</option>
                                @foreach($categories as $slug => $name)
                                    <option value="{{ $slug }}" {{ old('category') == $slug ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Prioridad *
                            </label>
                            <select name="priority" 
                                    class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('priority') border-error-300 @enderror"
                                    required>
                                <option value="">Selecciona la prioridad</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('priority')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Asunto *
                            </label>
                            <input type="text" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   placeholder="Describe brevemente tu problema"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('title') border-error-300 @enderror"
                                   required
                                   maxlength="255">
                            @error('title')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Columna derecha -->
                    <div class="space-y-4">
                        <div class="bg-accent-100 rounded-lg p-4">
                            <h3 class="font-semibold text-black-400 mb-3">Información Automática</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-black-300">Tienda:</span>
                                    <span class="text-black-400">{{ $store->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-black-300">Plan:</span>
                                    <span class="text-black-400">{{ $store->plan->name ?? 'Sin plan' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-black-300">Usuario:</span>
                                    <span class="text-black-400">{{ auth()->user()->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-black-300">Fecha:</span>
                                    <span class="text-black-400">{{ now()->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Archivos Adjuntos
                            </label>
                            <div class="border-2 border-dashed border-accent-200 rounded-lg p-4 text-center"
                                 x-on:dragover.prevent
                                 x-on:drop.prevent="handleFileDrop($event)">
                                <input type="file" 
                                       name="attachments[]" 
                                       multiple
                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt"
                                       class="hidden"
                                       x-ref="fileInput"
                                       x-on:change="handleFileSelect($event)">
                                <div class="cursor-pointer" x-on:click="$refs.fileInput.click()">
                                    <x-solar-upload-outline class="w-8 h-8 text-black-200 mx-auto mb-2" />
                                    <p class="text-sm text-black-300">
                                        Arrastra archivos aquí o <span class="text-primary-300">haz clic para seleccionar</span>
                                    </p>
                                    <p class="text-xs text-black-200 mt-1">
                                        Máximo 3 archivos, 5MB cada uno (JPG, PNG, PDF, DOC, TXT)
                                    </p>
                                </div>
                            </div>

                            <!-- Lista de archivos seleccionados -->
                            <div x-show="selectedFiles.length > 0" class="mt-3">
                                <template x-for="(file, index) in selectedFiles" :key="index">
                                    <div class="flex items-center justify-between p-2 bg-accent-100 rounded-lg mb-2">
                                        <div class="flex items-center gap-2">
                                            <x-solar-document-outline class="w-4 h-4 text-black-300" />
                                            <span class="text-sm text-black-400" x-text="file.name"></span>
                                            <span class="text-xs text-black-200" x-text="formatFileSize(file.size)"></span>
                                        </div>
                                        <button type="button" 
                                                x-on:click="removeFile(index)"
                                                class="text-error-300 hover:text-error-400">
                                            <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                        </button>
                                    </div>
                                </template>
                            </div>

                            @error('attachments.*')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Descripción (ancho completo) -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-black-300 mb-2">
                        Descripción *
                    </label>
                    <textarea name="description" 
                              rows="6"
                              placeholder="Describe detalladamente tu problema o consulta..."
                              class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('description') border-error-300 @enderror"
                              required
                              maxlength="5000">{{ old('description') }}</textarea>
                    <div class="flex justify-between mt-1">
                        @error('description')
                            <p class="text-error-300 text-xs">{{ $message }}</p>
                        @else
                            <p class="text-black-200 text-xs">Sé lo más específico posible para una respuesta más rápida</p>
                        @enderror
                        <p class="text-black-200 text-xs">Máximo 5000 caracteres</p>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-accent-100">
                    <a href="{{ route('tenant.admin.tickets.index', ['store' => $store->slug]) }}" 
                       class="btn-outline-secondary px-6 py-2 rounded-lg">
                        Cancelar
                    </a>
                    <button type="button" 
                            class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2"
                            x-bind:disabled="isSubmitting"
                            x-on:click="submitForm($event)"
                        <x-solar-plain-2-outline class="w-4 h-4" />
                        <span x-text="isSubmitting ? 'Creando...' : 'Crear Ticket'"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function createTicket() {
    return {
        selectedFiles: [],
        isSubmitting: false,

        init() {
            // Si hay errores de validación, restablecer el estado del botón
            @if($errors->any())
                this.isSubmitting = false;
            @endif
        },

        submitForm(event) {
            // Verificar que el formulario es válido antes de enviar
            const form = event.target.closest('form');
            if (form.checkValidity()) {
                this.isSubmitting = true;
                form.submit();
            } else {
                // Si el formulario no es válido, mostrar los errores nativos
                form.reportValidity();
            }
        },

        handleFileSelect(event) {
            this.processFiles(Array.from(event.target.files));
        },

        handleFileDrop(event) {
            this.processFiles(Array.from(event.dataTransfer.files));
        },

        processFiles(files) {
            // Limitar a 3 archivos
            const maxFiles = 3;
            const validFiles = files.slice(0, maxFiles - this.selectedFiles.length);
            
            // Validar tipo y tamaño
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
            const maxSize = 5 * 1024 * 1024; // 5MB

            validFiles.forEach(file => {
                if (allowedTypes.includes(file.type) && file.size <= maxSize) {
                    this.selectedFiles.push(file);
                }
            });

            // Actualizar el input
            this.updateFileInput();
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
            this.updateFileInput();
        },

        updateFileInput() {
            const dt = new DataTransfer();
            this.selectedFiles.forEach(file => dt.items.add(file));
            this.$refs.fileInput.files = dt.files;
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }
}
</script>
@endsection 