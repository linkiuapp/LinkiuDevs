@props([
    'label' => '',
    'modelName' => '',
    'placeholder' => '#000000',
    'required' => false
])

<div 
    x-data="colorPicker('{{ $modelName }}')"
    {{ $attributes->merge(['class' => 'space-y-3']) }}
>
    @if($label)
        <label class="text-sm font-medium text-black-400">
            {{ $label }}
            @if($required)
                <span class="text-error-400">*</span>
            @endif
        </label>
    @endif

    <div class="flex items-center gap-3">
        {{-- Color Preview Button --}}
        <button 
            type="button"
            @click="open"
            class="w-10 h-10 rounded-lg ring-1 ring-accent-200 transition-shadow hover:ring-2 hover:ring-primary-400"
            :style="{ backgroundColor: color }"
            :class="{ 'ring-error-400': error }"
        ></button>

        {{-- Color Input --}}
        <div class="relative flex-1">
            <input 
                type="text" 
                id="color_input_{{ $modelName }}"
                name="{{ $modelName }}"
                x-model="color"
                @input="validate"
                @blur="validateFinal"
                :class="{ 'border-error-400 focus:ring-error-400': error }"
                class="w-full px-3 py-2 rounded-lg bg-accent-100 border border-accent-200 focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-colors"
                placeholder="#000000"
            >
            
            {{-- Error Message --}}
            <p 
                x-show="error"
                x-text="error"
                class="absolute left-0 top-full mt-1 text-xs text-error-400"
            ></p>
        </div>

        {{-- Color Picker Popup --}}
        <div
            x-show="isOpen"
            @click.away="close"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-2 bg-accent-50 rounded-lg shadow-lg p-4"
            style="display: none;"
        >
            {{-- Preset Colors --}}
            <div class="space-y-3">
                <h4 class="text-sm font-medium text-black-400">Colores del Tema</h4>
                <div class="grid grid-cols-5 gap-2">
                    {{-- Primary Colors --}}
                    <button 
                        @click="setColor('#350692')"
                        class="w-8 h-8 rounded bg-primary-300 hover:ring-2 hover:ring-primary-100"
                    ></button>
                    <button 
                        @click="setColor('#7432F8')"
                        class="w-8 h-8 rounded bg-primary-200 hover:ring-2 hover:ring-primary-100"
                    ></button>
                    
                    {{-- Secondary Colors --}}
                    <button 
                        @click="setColor('#973D00')"
                        class="w-8 h-8 rounded bg-secondary-300 hover:ring-2 hover:ring-secondary-100"
                    ></button>
                    <button 
                        @click="setColor('#FD6905')"
                        class="w-8 h-8 rounded bg-secondary-200 hover:ring-2 hover:ring-secondary-100"
                    ></button>

                    {{-- Neutral Colors --}}
                    <button 
                        @click="setColor('#DEECFB')"
                        class="w-8 h-8 rounded bg-accent-400 hover:ring-2 hover:ring-accent-100"
                    ></button>
                    <button 
                        @click="setColor('#000273')"
                        class="w-8 h-8 rounded bg-info-200 hover:ring-2 hover:ring-info-100"
                    ></button>
                    <button 
                        @click="setColor('#03030B')"
                        class="w-8 h-8 rounded bg-black-400 hover:ring-2 hover:ring-black-100"
                    ></button>
                    <button 
                        @click="setColor('#F60F21')"
                        class="w-8 h-8 rounded bg-error-200 hover:ring-2 hover:ring-error-100"
                    ></button>
                </div>
            </div>

            {{-- Custom Color Picker --}}
            <div class="mt-4">
                <input 
                    type="color" 
                    x-model="color"
                    @input="validate"
                    class="w-full h-10 rounded cursor-pointer"
                >
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('colorPicker', (modelName) => ({
            color: '',
            error: null,
            isOpen: false,
            placeholder: '#000000',

            init() {
                console.log('Color-picker init - modelName:', modelName);
                console.log('Color-picker init - store value:', this.$store.design[modelName]);
                this.color = this.$store.design[modelName] || '';
                this.$watch('color', (value) => {
                    if (!this.error && value) {
                        console.log('Color-picker watch - updating', modelName, 'to:', value);
                        this.$store.design[modelName] = value;
                        console.log('Color-picker watch - store after update:', this.$store.design);
                    }
                });
            },

            open() {
                this.isOpen = true;
            },

            close() {
                this.isOpen = false;
            },

            setColor(color) {
                this.color = color;
                this.validate();
                // Forzar actualización inmediata del store
                if (!this.error) {
                    console.log('setColor - updating', modelName, 'to:', color);
                    this.$store.design[modelName] = color;
                    console.log('setColor - store after update:', this.$store.design);
                }
                this.close();
            },

            validate() {
                this.error = null;
                const hexRegex = /^#[0-9A-Fa-f]{6}$/;
                
                // Si está vacío y es requerido
                if (!this.color && this.required) {
                    this.error = 'Este campo es requerido';
                    return;
                }

                // Si no está vacío, validar formato
                if (this.color) {
                    if (!this.color.startsWith('#')) {
                        this.color = '#' + this.color;
                    }

                    if (!hexRegex.test(this.color)) {
                        this.error = 'Color inválido';
                        this.color = this.color.replace(/[^0-9A-Fa-f]/g, '');
                        if (this.color.length === 6) {
                            this.color = '#' + this.color;
                            this.error = null;
                        }
                    }
                }
            },

            validateFinal() {
                this.validate();
                if (this.error) {
                    this.color = this.$store.design[modelName] || '';
                    this.error = null;
                }
            }
        }));
    });
</script>
@endpush
@endonce 