@extends('shared::layouts.admin')

@section('title', 'Diseño de Formularios')

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="md:col-span-6 col-span-12">
        <!-- Formulario Vertical -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Formulario Vertical</h2>
            </div>
            <div class="p-6">
                <form class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Nombre</label>
                        <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu nombre">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Apellido</label>
                        <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu apellido">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Email</label>
                        <input type="email" class="w-full px-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu email">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Teléfono</label>
                        <div class="flex">
                            <select class="px-3 py-3 bg-white-50 border border-white-400 rounded-l-lg border-r-0 text-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors w-auto">
                                <option>US</option>
                                <option>ES</option>
                                <option>FR</option>
                                <option>DE</option>
                            </select>
                            <input type="text" class="flex-1 px-4 py-3 bg-white-50 border border-white-400 rounded-r-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="+1 (555) 000-0000">
                        </div>
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Contraseña</label>
                        <input type="password" class="w-full px-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="*******">
                    </div>
                    <div class="col-span-12">
                        <button type="submit" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="md:col-span-6 col-span-12">
        <!-- Formulario con Iconos -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Formulario con Iconos</h2>
            </div>
            <div class="p-6">
                <form class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Nombre</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-user-outline class="w-5 h-5" />
                            </span>
                            <input type="text" class="w-full pl-10 pr-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu nombre">
                        </div>
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Apellido</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-user-outline class="w-5 h-5" />
                            </span>
                            <input type="text" class="w-full pl-10 pr-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu apellido">
                        </div>
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Email</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-letter-outline class="w-5 h-5" />
                            </span>
                            <input type="email" class="w-full pl-10 pr-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu email">
                        </div>
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Teléfono</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-phone-outline class="w-5 h-5" />
                            </span>
                            <input type="text" class="w-full pl-10 pr-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="+1 (555) 000-0000">
                        </div>
                    </div>
                    <div class="col-span-12">
                        <label class="block text-base text-black-500 font-semibold mb-2">Contraseña</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                <x-solar-lock-password-outline class="w-5 h-5" />
                            </span>
                            <input type="password" class="w-full pl-10 pr-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="*******">
                        </div>
                    </div>
                    <div class="col-span-12">
                        <button type="submit" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="md:col-span-6 col-span-12">
        <!-- Formulario Horizontal -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Formulario Horizontal</h2>
            </div>
            <div class="p-6">
                <form>
                    <div class="grid grid-cols-12 gap-y-4 items-center mb-6">
                        <label class="block text-base text-black-500 font-semibold mb-0 sm:col-span-3 col-span-12">Nombre</label>
                        <div class="sm:col-span-9 col-span-12">
                            <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu nombre">
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-y-4 items-center mb-6">
                        <label class="block text-base text-black-500 font-semibold mb-0 sm:col-span-3 col-span-12">Apellido</label>
                        <div class="sm:col-span-9 col-span-12">
                            <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu apellido">
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-y-4 items-center mb-6">
                        <label class="block text-base text-black-500 font-semibold mb-0 sm:col-span-3 col-span-12">Email</label>
                        <div class="sm:col-span-9 col-span-12">
                            <input type="email" class="w-full px-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu email">
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-y-4 items-center mb-6">
                        <label class="block text-base text-black-500 font-semibold mb-0 sm:col-span-3 col-span-12">Teléfono</label>
                        <div class="sm:col-span-9 col-span-12">
                            <div class="flex">
                                <select class="px-3 py-3 bg-white-50 border border-white-400 rounded-l-lg border-r-0 text-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors w-auto">
                                    <option>US</option>
                                    <option>ES</option>
                                    <option>FR</option>
                                    <option>DE</option>
                                </select>
                                <input type="text" class="flex-1 px-4 py-3 bg-white-50 border border-white-400 rounded-r-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="+1 (555) 000-0000">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-y-4 items-center mb-6">
                        <label class="block text-base text-black-500 font-semibold mb-0 sm:col-span-3 col-span-12">Contraseña</label>
                        <div class="sm:col-span-9 col-span-12">
                            <input type="password" class="w-full px-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="*******">
                        </div>
                    </div>
                    <button type="submit" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors">Enviar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="md:col-span-6 col-span-12">
        <!-- Formulario Horizontal con Iconos -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Formulario Horizontal con Iconos</h2>
            </div>
            <div class="p-6">
                <form>
                    <div class="grid grid-cols-12 gap-y-4 items-center mb-6">
                        <label class="block text-base text-black-500 font-semibold mb-0 sm:col-span-3 col-span-12">Nombre</label>
                        <div class="sm:col-span-9 col-span-12">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                    <x-solar-user-outline class="w-5 h-5" />
                                </span>
                                <input type="text" class="w-full pl-10 pr-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu nombre">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-y-4 items-center mb-6">
                        <label class="block text-base text-black-500 font-semibold mb-0 sm:col-span-3 col-span-12">Apellido</label>
                        <div class="sm:col-span-9 col-span-12">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                    <x-solar-user-outline class="w-5 h-5" />
                                </span>
                                <input type="text" class="w-full pl-10 pr-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu apellido">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-y-4 items-center mb-6">
                        <label class="block text-base text-black-500 font-semibold mb-0 sm:col-span-3 col-span-12">Email</label>
                        <div class="sm:col-span-9 col-span-12">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                    <x-solar-letter-outline class="w-5 h-5" />
                                </span>
                                <input type="email" class="w-full pl-10 pr-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu email">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-y-4 items-center mb-6">
                        <label class="block text-base text-black-500 font-semibold mb-0 sm:col-span-3 col-span-12">Teléfono</label>
                        <div class="sm:col-span-9 col-span-12">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                    <x-solar-phone-outline class="w-5 h-5" />
                                </span>
                                <input type="text" class="w-full pl-10 pr-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="+1 (555) 000-0000">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-y-4 items-center mb-6">
                        <label class="block text-base text-black-500 font-semibold mb-0 sm:col-span-3 col-span-12">Contraseña</label>
                        <div class="sm:col-span-9 col-span-12">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                                    <x-solar-lock-password-outline class="w-5 h-5" />
                                </span>
                                <input type="password" class="w-full pl-10 pr-4 py-3 bg-white-50 border border-white-400 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="*******">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors">Enviar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 