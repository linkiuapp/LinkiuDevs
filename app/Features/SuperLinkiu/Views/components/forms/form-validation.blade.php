@extends('shared::layouts.admin')

@section('title', 'Validación de Formularios')

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <!-- Estilos Personalizados de Input -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Estilos Personalizados de Input</h2>
            </div>
            <div class="p-6">
                <form class="grid grid-cols-12 gap-4" onsubmit="return false;">
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Placeholder</label>
                        <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" value="info@gmail.com" required>
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">File Input Mediano</label>
                        <input class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-200 file:text-accent-50 file:body-base hover:file:bg-primary-300 transition-colors" type="file">
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Icono</label>
                        <input type="email" class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu email" required>
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Pago</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 border border-accent-200 rounded-l-lg border-r-0 bg-accent-100">
                                <x-solar-card-outline class="w-5 h-5 text-black-300" />
                            </span>
                            <input type="text" class="flex-1 px-4 py-3 bg-accent-50 border border-accent-400 rounded-r-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Número de Tarjeta">
                        </div>
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Teléfono</label>
                        <div class="flex">
                            <select class="px-3 py-3 bg-accent-50 border border-accent-400 rounded-l-lg border-r-0 text-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors w-auto">
                                <option>US</option>
                                <option>ES</option>
                                <option>FR</option>
                                <option>DE</option>
                            </select>
                            <input type="text" class="flex-1 px-4 py-3 bg-accent-50 border border-accent-400 rounded-r-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="+1 (555) 000-0000">
                        </div>
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Botón</label>
                        <div class="flex">
                            <input type="text" class="flex-1 px-4 py-3 bg-accent-50 border border-accent-400 rounded-l-lg border-r-0 text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="www.random.com">
                            <button type="button" class="inline-flex items-center px-4 py-3 bg-primary-400 text-accent-50 border border-primary-400 rounded-r-lg hover:bg-primary-500 transition-colors">
                                <x-solar-copy-outline class="w-5 h-5 mr-2" />
                                Copiar
                            </button>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <button class="bg-primary-200 text-accent-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors" type="submit">Enviar Formulario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-span-12">
        <!-- Estados de Input -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Estados de Input</h2>
            </div>
            <div class="p-6">
                <form class="grid grid-cols-12 gap-4" id="validationForm">
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Nombre</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-user-outline class="w-5 h-5" />
                            </span>
                            <input type="text" class="w-full pl-10 pr-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu nombre" required>
                        </div>
                        <div class="form-error hidden text-error-400 text-sm mt-2">
                            Este campo es requerido
                        </div>
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Apellido</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-user-outline class="w-5 h-5" />
                            </span>
                            <input type="text" class="w-full pl-10 pr-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu apellido" required>
                        </div>
                        <div class="form-error hidden text-error-400 text-sm mt-2">
                            Este campo es requerido
                        </div>
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Email</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-letter-outline class="w-5 h-5" />
                            </span>
                            <input type="email" class="w-full pl-10 pr-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu email" required>
                        </div>
                        <div class="form-error hidden text-error-400 text-sm mt-2">
                            Ingresa un email válido
                        </div>
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Teléfono</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-phone-outline class="w-5 h-5" />
                            </span>
                            <input type="text" class="w-full pl-10 pr-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="+1 (555) 000-0000" required>
                        </div>
                        <div class="form-error hidden text-error-400 text-sm mt-2">
                            Este campo es requerido
                        </div>
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Contraseña</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-lock-password-outline class="w-5 h-5" />
                            </span>
                            <input type="password" class="w-full pl-10 pr-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="*******" required>
                        </div>
                        <div class="form-error hidden text-error-400 text-sm mt-2">
                            La contraseña debe tener al menos 8 caracteres
                        </div>
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Confirmar Contraseña</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-lock-password-outline class="w-5 h-5" />
                            </span>
                            <input type="password" class="w-full pl-10 pr-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="*******" required>
                        </div>
                        <div class="form-error hidden text-error-400 text-sm mt-2">
                            Las contraseñas no coinciden
                        </div>
                    </div>
                    <div class="col-span-12">
                        <button class="bg-primary-200 text-accent-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors" type="submit">Enviar Formulario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Ejemplo de Estados de Validación -->
    <div class="col-span-12">
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Ejemplos de Estados de Validación</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-12 gap-6">
                    <div class="md:col-span-4 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Campo Válido</label>
                        <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-success-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-success-300 focus:ring-1 focus:ring-success-200 transition-colors" value="John Doe" readonly>
                        <div class="text-success-300 text-sm mt-2 flex items-center">
                            <x-solar-check-circle-outline class="w-4 h-4 mr-2" />
                            Campo válido
                        </div>
                    </div>
                    <div class="md:col-span-4 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Campo con Error</label>
                        <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-error-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-error-300 focus:ring-1 focus:ring-error-200 transition-colors" value="John Doe" required>
                        <div class="text-error-200 text-sm mt-2 flex items-center">
                            <x-solar-close-circle-outline class="w-4 h-4 mr-2" />
                            Este campo es requerido
                        </div>
                    </div>
                    <div class="md:col-span-4 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Campo con Advertencia</label>
                        <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-secondary-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-secondary-300 focus:ring-1 focus:ring-secondary-200 transition-colors" value="password123">
                        <div class="text-secondary-300 text-sm mt-2 flex items-center">
                            <x-solar-danger-triangle-outline class="w-4 h-4 mr-2" />
                            Contraseña débil
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-error.show {
        display: block !important;
    }
    
    .input-error {
        border-color: #DC2626 !important;
    }
    
    .input-success {
        border-color: #059669 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Validación de formulario simple
    document.getElementById('validationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const inputs = form.querySelectorAll('input[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            const errorDiv = input.parentElement.nextElementSibling;
            
            if (!input.value.trim()) {
                input.classList.add('input-error');
                errorDiv.classList.remove('hidden');
                errorDiv.classList.add('show');
                isValid = false;
            } else {
                input.classList.remove('input-error');
                input.classList.add('input-success');
                errorDiv.classList.add('hidden');
                errorDiv.classList.remove('show');
            }
        });
        
        if (isValid) {
            alert('Formulario enviado correctamente');
        }
    });
    
    // Validación en tiempo real
    document.querySelectorAll('#validationForm input').forEach(input => {
        input.addEventListener('blur', function() {
            const errorDiv = this.parentElement.nextElementSibling;
            
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('input-error');
                errorDiv.classList.remove('hidden');
                errorDiv.classList.add('show');
            } else {
                this.classList.remove('input-error');
                this.classList.add('input-success');
                errorDiv.classList.add('hidden');
                errorDiv.classList.remove('show');
            }
        });
        
        input.addEventListener('focus', function() {
            this.classList.remove('input-error', 'input-success');
        });
    });
</script>
@endpush 