@extends('shared::layouts.admin')

@section('title', 'Dropdown')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="col-span-1">
        <!-- Dropdown 1 -->
        <div class="card overflow-visible">
            <div class="card-header">
                <h2 class="title-card">Dropdown Básico</h2>
            </div>
            <div class="card-body flex justify-center items-center min-h-[120px]">
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-semibold text-white-50 bg-primary-200 border border-primary-200 rounded-lg shadow-sm hover:bg-primary-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-100" id="dropdown-button" aria-expanded="true" aria-haspopup="true">
                        <span>Dropdown 1</span>
                        <x-solar-arrow-down-outline class="w-4 h-4 ml-2" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-40 mt-2 w-56 origin-top-right bg-white-50 rounded-lg shadow-lg ring-1 ring-white-200 ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-button" tabindex="-1">
                        <div class="py-1" role="none">
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1" id="dropdown-item-0">Opción 1</a>
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1" id="dropdown-item-1">Opción 2</a>
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1" id="dropdown-item-2">Opción 3</a>
                            <div class="border-t border-white-200"></div>
                            <a href="#" class="text-error-200 hover:bg-error-50 hover:text-error-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1" id="dropdown-item-3">Eliminar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Dropdown 2 -->
        <div class="card overflow-visible">
            <div class="card-header">
                <h2 class="title-card">Dropdown Arriba</h2>
            </div>
            <div class="card-body flex justify-center items-center min-h-[120px]">
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-semibold text-white-50 bg-secondary-200 border border-secondary-200 rounded-lg shadow-sm hover:bg-secondary-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-100" id="dropdown-button-2" aria-expanded="true" aria-haspopup="true">
                        <span>Dropdown 2</span>
                        <x-solar-arrow-up-outline class="w-4 h-4 ml-2" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-10 bottom-full mb-2 w-56 origin-bottom-right bg-white-50 rounded-lg shadow-lg ring-1 ring-white-200 ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-button-2" tabindex="-1">
                        <div class="py-1" role="none">
                            <a href="#" class="text-black-400 hover:bg-secondary-50 hover:text-secondary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 1</a>
                            <a href="#" class="text-black-400 hover:bg-secondary-50 hover:text-secondary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 2</a>
                            <a href="#" class="text-black-400 hover:bg-secondary-50 hover:text-secondary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 3</a>
                            <div class="border-t border-white-200"></div>
                            <a href="#" class="text-error-200 hover:bg-error-50 hover:text-error-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Eliminar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Dropdown 3 -->
        <div class="card overflow-visible">
            <div class="card-header">
                <h2 class="title-card">Dropdown Derecha</h2>
            </div>
            <div class="card-body flex justify-center items-center min-h-[120px]">
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-semibold text-white-50 bg-success-200 border border-success-200 rounded-lg shadow-sm hover:bg-success-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-100" id="dropdown-button-3" aria-expanded="true" aria-haspopup="true">
                        <span>Dropdown 3</span>
                        <x-solar-arrow-right-outline class="w-4 h-4 ml-2" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-full ml-2 z-40 top-0 w-56 origin-top-left bg-white-50 rounded-lg shadow-lg ring-1 ring-white-200 ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-button-3" tabindex="-1">
                        <div class="py-1" role="none">
                            <a href="#" class="text-black-400 hover:bg-success-50 hover:text-success-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 1</a>
                            <a href="#" class="text-black-400 hover:bg-success-50 hover:text-success-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 2</a>
                            <a href="#" class="text-black-400 hover:bg-success-50 hover:text-success-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 3</a>
                            <div class="border-t border-white-200"></div>
                            <a href="#" class="text-error-200 hover:bg-error-50 hover:text-error-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Eliminar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Dropdown 4 -->
        <div class="card overflow-visible">
            <div class="card-header">
                <h2 class="title-card">Dropdown Izquierda</h2>
            </div>
            <div class="card-body flex justify-center items-center min-h-[120px]">
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-semibold text-white-50 bg-info-200 border border-info-200 rounded-lg shadow-sm hover:bg-info-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-100" id="dropdown-button-4" aria-expanded="true" aria-haspopup="true">
                        <span>Dropdown 4</span>
                        <x-solar-arrow-left-outline class="w-4 h-4 ml-2" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-full mr-2 z-40 top-0 w-56 origin-top-right bg-white-50 rounded-lg shadow-lg ring-1 ring-white-200 ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-button-4" tabindex="-1">
                        <div class="py-1" role="none">
                            <a href="#" class="text-black-400 hover:bg-info-50 hover:text-info-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 1</a>
                            <a href="#" class="text-black-400 hover:bg-info-50 hover:text-info-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 2</a>
                            <a href="#" class="text-black-400 hover:bg-info-50 hover:text-info-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 3</a>
                            <div class="border-t border-white-200"></div>
                            <a href="#" class="text-error-200 hover:bg-error-50 hover:text-error-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Eliminar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="col-span-1">
        <!-- Dropdown con Iconos -->
        <div class="card overflow-visible">
            <div class="card-header">
                <h2 class="title-card">Dropdown con Iconos</h2>
            </div>
            <div class="card-body flex justify-center items-center min-h-[160px]">
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-semibold text-black-400 bg-white-50 border border-white-200 rounded-lg shadow-sm hover:bg-white-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-100" id="dropdown-button-5" aria-expanded="true" aria-haspopup="true">
                        <x-solar-settings-outline class="w-4 h-4 mr-2" />
                        <span>Acciones</span>
                        <x-solar-arrow-down-outline class="w-4 h-4 ml-2" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-40 mt-2 w-56 origin-top-right bg-white-50 rounded-lg shadow-lg ring-1 ring-white-200 ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-button-5" tabindex="-1">
                        <div class="py-1" role="none">
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 flex items-center px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">
                                <x-solar-eye-outline class="w-4 h-4 mr-2" />
                                Ver
                            </a>
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 flex items-center px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">
                                <x-solar-pen-outline class="w-4 h-4 mr-2" />
                                Editar
                            </a>
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 flex items-center px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">
                                <x-solar-copy-outline class="w-4 h-4 mr-2" />
                                Duplicar
                            </a>
                            <div class="border-t border-white-200"></div>
                            <a href="#" class="text-error-200 hover:bg-error-50 hover:text-error-300 flex items-center px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">
                                <x-solar-trash-bin-minimalistic-outline class="w-4 h-4 mr-2" />
                                Eliminar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Dropdown con Submenu -->
        <div class="card overflow-visible">
            <div class="card-header">
                <h2 class="title-card">Dropdown con Submenu</h2>
            </div>
            <div class="card-body flex justify-center items-center min-h-[160px]">
                <div class="relative inline-block text-left" x-data="{ open: false, submenu: false }">
                    <button @click="open = !open" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-semibold text-white-50 bg-primary-200 border border-primary-200 rounded-lg shadow-sm hover:bg-primary-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-100" id="dropdown-button-6" aria-expanded="true" aria-haspopup="true">
                        <span>Menú</span>
                        <x-solar-arrow-down-outline class="w-4 h-4 ml-2" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-40 mt-2 w-56 origin-top-right bg-white-50 rounded-lg shadow-lg ring-1 ring-white-200 ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-button-6" tabindex="-1">
                        <div class="py-1" role="none">
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 1</a>
                            <div class="relative" x-data="{ submenu: false }">
                                <button @mouseenter="submenu = true" @mouseleave="submenu = false" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 flex items-center justify-between w-full px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">
                                    <span>Submenu</span>
                                    <x-solar-arrow-right-outline class="w-4 h-4" />
                                </button>
                                <div x-show="submenu" @mouseenter="submenu = true" @mouseleave="submenu = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-full top-0 ml-1 w-48 origin-top-left bg-white-50 rounded-lg shadow-lg ring-1 ring-white-200 ring-opacity-5 focus:outline-none z-50">
                                    <div class="py-1">
                                        <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors">Subopción 1</a>
                                        <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors">Subopción 2</a>
                                        <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors">Subopción 3</a>
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 3</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dropdown Varientes Adicionales -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="col-span-1">
        <!-- Dropdown Warning -->
        <div class="card overflow-visible">
            <div class="card-header">
                <h2 class="title-card">Dropdown Warning</h2>
            </div>
            <div class="card-body flex justify-center items-center min-h-[120px]">
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-semibold text-black-400 bg-warning-200 border border-warning-200 rounded-lg shadow-sm hover:bg-warning-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-100" id="dropdown-button-7" aria-expanded="true" aria-haspopup="true">
                        <span>Warning</span>
                        <x-solar-arrow-down-outline class="w-4 h-4 ml-2" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-40 mt-2 w-56 origin-top-right bg-white-50 rounded-lg shadow-lg ring-1 ring-white-200 ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-button-7" tabindex="-1">
                        <div class="py-1" role="none">
                            <a href="#" class="text-black-400 hover:bg-warning-50 hover:text-warning-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 1</a>
                            <a href="#" class="text-black-400 hover:bg-warning-50 hover:text-warning-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 2</a>
                            <a href="#" class="text-black-400 hover:bg-warning-50 hover:text-warning-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 3</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Dropdown Error -->
        <div class="card overflow-visible">
            <div class="card-header">
                <h2 class="title-card">Dropdown Error</h2>
            </div>
            <div class="card-body flex justify-center items-center min-h-[120px]">
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-semibold text-white-50 bg-error-200 border border-error-200 rounded-lg shadow-sm hover:bg-error-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-error-100" id="dropdown-button-8" aria-expanded="true" aria-haspopup="true">
                        <span>Error</span>
                        <x-solar-arrow-down-outline class="w-4 h-4 ml-2" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-40 mt-2 w-56 origin-top-right bg-white-50 rounded-lg shadow-lg ring-1 ring-white-200 ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-button-8" tabindex="-1">
                        <div class="py-1" role="none">
                            <a href="#" class="text-black-400 hover:bg-error-50 hover:text-error-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 1</a>
                            <a href="#" class="text-black-400 hover:bg-error-50 hover:text-error-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 2</a>
                            <a href="#" class="text-black-400 hover:bg-error-50 hover:text-error-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 3</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Dropdown Outline -->
        <div class="card overflow-visible">
            <div class="card-header">
                <h2 class="title-card">Dropdown Outline</h2>
            </div>
            <div class="card-body flex justify-center items-center min-h-[120px]">
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-semibold text-primary-300 bg-transparent border border-primary-200 rounded-lg shadow-sm hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-100" id="dropdown-button-9" aria-expanded="true" aria-haspopup="true">
                        <span>Outline</span>
                        <x-solar-arrow-down-outline class="w-4 h-4 ml-2" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-40 mt-2 w-56 origin-top-right bg-white-50 rounded-lg shadow-lg ring-1 ring-white-200 ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-button-9" tabindex="-1">
                        <div class="py-1" role="none">
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 1</a>
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 2</a>
                            <a href="#" class="text-black-400 hover:bg-primary-50 hover:text-primary-300 block px-4 py-2 text-sm transition-colors" role="menuitem" tabindex="-1">Opción 3</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 