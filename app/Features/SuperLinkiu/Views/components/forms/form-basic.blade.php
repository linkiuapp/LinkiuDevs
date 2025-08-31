@extends('shared::layouts.admin')

@section('title', 'Formulario Básico')

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="md:col-span-6 col-span-12">
        <!-- Inputs por Defecto -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Inputs por Defecto</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input Básico</label>
                        <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Placeholder</label>
                        <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="info@gmail.com">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Teléfono</label>
                        <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="+1 (555) 253-08515">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input Fecha</label>
                        <input type="date" class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Pago</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 border border-accent-400 rounded-l-lg border-r-0 bg-accent-100">
                                <x-solar-card-outline class="w-5 h-5 text-black-300" />
                            </span>
                            <input type="text" class="flex-1 px-4 py-3 bg-accent-50 border border-accent-400 rounded-r-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Número de Tarjeta">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grupos de Input -->
        <div class="card mt-6">
            <div class="card-header">
                <h2 class="title-card">Grupos de Input</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Icono</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-letter-outline class="w-5 h-5" />
                            </span>
                            <input type="text" class="w-full pl-10 pr-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="info@gmail.com">
                        </div>
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Select</label>
                        <div class="flex">
                            <select class="px-2 py-3 bg-accent-50 border border-accent-400 rounded-l-lg border-r-0 text-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors w-auto">
                                <option>US</option>
                                <option>ES</option>
                                <option>FR</option>
                                <option>DE</option>
                                <option>IT</option>
                            </select>
                            <input type="text" class="flex-1 px-4 py-3 bg-accent-50 border border-accent-400 rounded-r-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="info@gmail.com">
                        </div>
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Select al Final</label>
                        <div class="flex">
                            <input type="text" class="flex-1 px-4 py-3 bg-accent-50 border border-accent-400 rounded-l-lg border-r-0 text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="info@gmail.com">
                            <select class="px-3 py-3 bg-accent-50 border border-accent-400 rounded-r-lg text-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors w-auto">
                                <option>US</option>
                                <option>ES</option>
                                <option>FR</option>
                                <option>DE</option>
                                <option>IT</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Prefijo</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 border border-accent-400 rounded-l-lg border-r-0 bg-accent-100 text-base text-black-400">
                                http://
                            </span>
                            <input type="text" class="flex-1 px-4 py-3 bg-accent-50 border border-accent-400 rounded-r-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="www.random.com">
                        </div>
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input con Botón</label>
                        <div class="flex">
                            <input type="text" class="flex-1 px-4 py-3 bg-accent-50 border border-accent-400 rounded-l-lg border-r-0 text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="www.random.com">
                            <button type="button" class="inline-flex items-center px-4 py-3 bg-primary-400 text-accent-50 border border-primary-400 rounded-r-lg hover:bg-primary-500 transition-colors">
                                <x-solar-copy-outline class="w-5 h-5 mr-2" />
                                Copiar
                            </button>
                        </div>
                        <p class="text-sm text-black-300 mt-2">Este es un texto de ayuda para el usuario.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:col-span-6 col-span-12">
        <!-- Tamaños de Input -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Tamaños de Input</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input Grande</label>
                        <input type="text" class="w-full px-4 py-4 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="info@gmail.com">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input Mediano</label>
                        <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="info@gmail.com">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input Pequeño</label>
                        <input type="text" class="w-full px-3 py-2 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="info@gmail.com">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tamaños de File Input -->
        <div class="card mt-6">
            <div class="card-header">
                <h2 class="title-card">Tamaños de File Input</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">File Input Grande</label>
                        <input class="w-full px-4 py-4 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-200 file:text-accent-50 file:body-base hover:file:bg-primary-300 transition-colors" type="file">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">File Input Mediano</label>
                        <input class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-200 file:text-accent-50 file:body-base hover:file:bg-primary-300 transition-colors" type="file">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">File Input Pequeño</label>
                        <input class="w-full px-3 py-2 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-primary-200 file:text-accent-50 file:body-small hover:file:bg-primary-300 transition-colors" type="file">
                    </div>
                </div>
            </div>
        </div>

        <!-- Formularios Personalizados -->
        <div class="card mt-6">
            <div class="card-header">
                <h2 class="title-card">Formularios Personalizados</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Input Solo Lectura</label>
                        <input type="text" class="w-full px-4 py-3 bg-accent-100 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none cursor-not-allowed" placeholder="info@gmail.com" value="info@gmail.com" readonly>
                    </div>
                    <div class="col-span-12">
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
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">File Input Mediano</label>
                        <input class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-200 file:text-accent-50 file:body-base hover:file:bg-primary-300 transition-colors" type="file">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campos de Textarea -->
    <div class="col-span-12">
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Campos de Textarea</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-12 gap-6">
                    <div class="lg:col-span-4 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Descripción</label>
                        <textarea class="w-full px-4 py-3 bg-accent-50 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors resize-y" rows="4" placeholder="Ingresa una descripción..."></textarea>
                    </div>
                    <div class="lg:col-span-4 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Descripción Solo Lectura</label>
                        <textarea class="w-full px-4 py-3 bg-accent-100 border border-accent-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none cursor-not-allowed resize-y" rows="4" placeholder="Ingresa una descripción..." readonly></textarea>
                    </div>
                    <div class="lg:col-span-4 col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Descripción con Error</label>
                        <textarea class="w-full px-4 py-3 bg-accent-50 border border-error-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-error-500 focus:ring-1 focus:ring-error-400 transition-colors resize-y" rows="4" placeholder="Ingresa una descripción..." required></textarea>
                        <div class="text-error-400 text-sm mt-2">
                            Por favor ingresa un mensaje en el textarea.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 