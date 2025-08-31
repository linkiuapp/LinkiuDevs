@props([
    'type' => 'logo', // 'logo' o 'favicon'
    'label' => '',
    'accept' => 'image/jpeg,image/png,image/gif,image/webp',
    'maxSize' => 2048, // en KB
    'containerClass' => '',
    'previewClass' => ''
])

<div 
    x-data="imageUploader('{{ $type }}')"
    class="space-y-4 {{ $containerClass }}"
>
    {{-- Label --}}
    @if($label)
        <label class="text-sm font-medium text-black-400">
            {{ $label }}
        </label>
    @endif

    {{-- Drop Zone --}}
    <div 
        class="relative {{ $previewClass ?: 'w-full h-40' }}"
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="handleDrop"
    >
        {{-- Preview Container --}}
        <div 
            class="w-full h-full bg-accent-100 rounded-lg overflow-hidden"
            :class="{ 
                'ring-2 ring-primary-400 bg-primary-50/10': dragOver,
                'ring-2 ring-error-400 bg-error-50/10': error
            }"
        >
            {{-- Empty State --}}
            <template x-if="!preview && !currentImage">
                <div class="w-full h-full flex flex-col items-center justify-center p-4">
                    <x-solar-gallery-add-outline class="w-8 h-8 text-black-300" />
                    <p class="mt-2 text-sm text-black-300 text-center">
                        Arrastra tu imagen aquí o
                        <button 
                            type="button"
                            @click="$refs.fileInput.click()"
                            class="text-primary-400 hover:text-primary-500"
                        >
                            selecciona un archivo
                        </button>
                    </p>
                    <p class="mt-1 text-xs text-black-300">
                        @if($type === 'logo')
                            PNG, JPG o WebP (max. {{ $maxSize }}KB)
                        @else
                            PNG o ICO (max. {{ $maxSize }}KB)
                        @endif
                    </p>
                </div>
            </template>

            {{-- Image Preview --}}
            <template x-if="preview || currentImage">
                <div class="relative w-full h-full">
                    <img 
                        :src="preview || currentImage" 
                        class="w-full h-full object-contain"
                        :alt="'{{ $type === 'logo' ? 'Logo' : 'Favicon' }} preview'"
                    >
                    {{-- Loading Overlay --}}
                    <div 
                        x-show="uploading"
                        class="absolute inset-0 bg-black-500/20 flex items-center justify-center"
                    >
                        <div class="animate-spin rounded-full h-8 w-8 border-4 border-primary-400 border-t-transparent"></div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Error Message --}}
        <div 
            x-show="error"
            x-text="error"
            class="absolute left-0 top-full mt-1 text-xs text-error-400"
        ></div>
    </div>

    {{-- File Input --}}
    <input 
        type="file" 
        x-ref="fileInput"
        @change="handleFileSelect"
        class="hidden" 
        :accept="accept"
    >

    {{-- Action Buttons --}}
    <div class="flex items-center gap-3">
        <button 
            type="button"
            @click="$refs.fileInput.click()"
            class="btn-secondary flex-1 flex items-center justify-center gap-2"
            :disabled="uploading"
        >
            <x-solar-upload-outline class="w-5 h-5" />
            <span x-text="uploading ? 'Subiendo...' : 'Subir {{ $type === 'logo' ? 'Logo' : 'Favicon' }}'"></span>
        </button>
        <button 
            x-show="preview || currentImage"
            @click="removeImage"
            class="btn-error flex items-center justify-center gap-2"
            :disabled="uploading"
        >
            <x-solar-trash-bin-trash-outline class="w-5 h-5" />
            Eliminar
        </button>
    </div>
</div>

@once
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('imageUploader', (type) => ({
            preview: null,
            currentImage: null,
            error: null,
            dragOver: false,
            uploading: false,
            maxSize: {{ $maxSize }},
            accept: '{{ $accept }}',

            init() {
                if (type === 'logo') {
                    this.currentImage = this.$store.design.logo;
                } else {
                    this.currentImage = this.$store.design.favicon;
                }
            },

            validateFile(file) {
                this.error = null;

                // Validar tipo de archivo
                if (!file.type.match('image.*')) {
                    this.error = 'El archivo debe ser una imagen';
                    return false;
                }

                // Validar extensión
                const validTypes = this.accept.split(',');
                if (!validTypes.includes(file.type)) {
                    this.error = 'Formato de imagen no soportado';
                    return false;
                }

                // Validar tamaño
                if (file.size > this.maxSize * 1024) {
                    this.error = `La imagen no debe superar los ${this.maxSize}KB`;
                    return false;
                }

                return true;
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file && this.validateFile(file)) {
                    this.uploadFile(file);
                }
            },

            handleDrop(event) {
                this.dragOver = false;
                const file = event.dataTransfer.files[0];
                if (file && this.validateFile(file)) {
                    this.uploadFile(file);
                }
            },

            async uploadFile(file) {
                // Mostrar vista previa y almacenar base64 en el store
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.preview = e.target.result;
                    
                    // Almacenar base64 en el store para enviar más tarde
                    if (type === 'logo') {
                        this.$store.design.logo = e.target.result;
                        this.$store.design.logo_base64 = e.target.result;
                    } else {
                        this.$store.design.favicon = e.target.result;
                        this.$store.design.favicon_base64 = e.target.result;
                    }
                };
                reader.readAsDataURL(file);

                // No subir inmediatamente, solo almacenar en el store
                this.uploading = false;
                this.error = null;
            },

            removeImage() {
                if (type === 'logo') {
                    this.preview = null;
                    this.currentImage = null;
                    this.$store.design.logo = null;
                    this.$store.design.logo_webp = null;
                    this.$store.design.logo_base64 = null;
                } else {
                    this.preview = null;
                    this.currentImage = null;
                    this.$store.design.favicon = null;
                    this.$store.design.favicon_base64 = null;
                }
            }
        }));
    });
</script>
@endpush
@endonce 