@extends('shared::layouts.admin')

@section('title', 'Pagination')

@section('content')
<div class="grid grid-cols-1 gap-6">
    <div class="col-span-12">
        <!-- Pagination Outline -->
        <div class="card-lg">
            <div class="card-header">
                <h2 class="title-card">Pagination Outline</h2>
            </div>
            <div class="card-body bg-white-50">
                <ul class="pagination flex flex-wrap items-center gap-2 justify-center">
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                            Primero
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                            Anterior
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-2 py-2 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                            <x-solar-arrow-left-outline class="w-5 h-5" />
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-primary-200 text-primary-200 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px]" href="javascript:void(0)">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-2 py-2 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                            <x-solar-arrow-right-outline class="w-5 h-5" />
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                            Siguiente
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                            Último
                        </a>
                    </li>
                </ul>
                
                <ul class="pagination flex flex-wrap items-center gap-2 justify-center mt-6">
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                            Anterior
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-2 py-2 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                            <x-solar-arrow-left-outline class="w-5 h-5" />
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-primary-200 text-primary-200 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px]" href="javascript:void(0)">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-2 py-2 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                            <x-solar-arrow-right-outline class="w-5 h-5" />
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-400 text-black-400 font-medium rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                            Siguiente
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-span-12">
        <!-- Pagination Filled -->
        <div class="card-lg">
            <div class="card-header">
                <h2 class="title-card">Pagination Filled</h2>
            </div>
            <div class="card-body bg-white-50">
                <ul class="pagination flex flex-wrap items-center gap-2 justify-center">
                    <li class="page-item">
                        <a class="page-link bg-primary-200 text-white-50 font-semibold rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] hover:bg-primary-500 transition-colors" href="javascript:void(0)">
                            Anterior
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-primary-50 text-primary-200 font-semibold rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-200 transition-colors" href="javascript:void(0)">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-primary-200 text-white-50 font-semibold rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px]" href="javascript:void(0)">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-primary-50 text-primary-200 font-semibold rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-200 transition-colors" href="javascript:void(0)">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-primary-50 text-primary-200 font-semibold rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-200 transition-colors" href="javascript:void(0)">4</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-primary-50 text-primary-200 font-semibold rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] w-[48px] hover:bg-primary-200 transition-colors" href="javascript:void(0)">5</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-primary-200 text-white-50 font-semibold rounded-lg px-5 py-2.5 flex items-center justify-center h-[48px] hover:bg-primary-500 transition-colors" href="javascript:void(0)">
                            Siguiente
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-span-12">
        <!-- Pagination con Tamaños -->
        <div class="card-lg">
            <div class="card-header">
                <h2 class="title-card">Pagination con Tamaños</h2>
            </div>
            <div class="card-body bg-white-50">
                <!-- Pequeño -->
                <div>
                    <div class="mb-3">
                        <span class="text-sm text-black-400">Pequeño</span>
                    </div>
                    <ul class="pagination flex flex-wrap items-center gap-1 justify-center">
                        <li class="page-item">
                            <a class="page-link bg-white-50 border border-white-300 text-black-400 font-medium rounded-lg px-3 py-1.5 flex items-center justify-center h-[32px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                                <x-solar-arrow-left-outline class="w-4 h-4" />
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link bg-white-50 border border-white-300 text-black-400 font-medium rounded-lg px-3 py-1.5 flex items-center justify-center h-[32px] w-[32px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">1</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link bg-white-50 border border-primary-200 text-primary-200 font-medium rounded-lg px-3 py-1.5 flex items-center justify-center h-[32px] w-[32px]" href="javascript:void(0)">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link bg-white-50 border border-white-300 text-black-400 font-medium rounded-lg px-3 py-1.5 flex items-center justify-center h-[32px] w-[32px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">3</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link bg-white-50 border border-white-300 text-black-400 font-medium rounded-lg px-3 py-1.5 flex items-center justify-center h-[32px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                                <x-solar-arrow-right-outline class="w-4 h-4" />
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Grande -->
                <div>
                    <div class="mb-3">
                        <span class="text-lg text-black-500">Grande</span>
                    </div>
                    <ul class="pagination flex flex-wrap items-center gap-3 justify-center">
                        <li class="page-item">
                            <a class="page-link bg-white-50 border border-white-300 text-black-400 font-medium rounded-lg px-6 py-4 flex items-center justify-center h-[56px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                                <x-solar-arrow-left-outline class="w-6 h-6" />
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link bg-white-50 border border-white-300 text-black-400 font-medium rounded-lg px-6 py-4 flex items-center justify-center h-[56px] w-[56px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">1</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link bg-white-50 border border-primary-200 text-primary-200 font-medium rounded-lg px-6 py-4 flex items-center justify-center h-[56px] w-[56px]" href="javascript:void(0)">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link bg-white-50 border border-white-300 text-black-400 font-medium rounded-lg px-6 py-4 flex items-center justify-center h-[56px] w-[56px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">3</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link bg-white-50 border border-white-300 text-black-400 font-medium rounded-lg px-6 py-4 flex items-center justify-center h-[56px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" href="javascript:void(0)">
                                <x-solar-arrow-right-outline class="w-6 h-6" />
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12">
        <!-- Pagination Compacta -->
        <div class="card-lg">
            <div class="card-header">
                <h2 class="title-card">Pagination Compacta</h2>
            </div>
            <div class="card-body bg-white-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <a class="page-link bg-primary-200 text-white-50 font-semibold rounded-lg px-4 py-2 flex items-center justify-center h-[40px] hover:bg-primary-500 transition-colors" href="javascript:void(0)">
                            <x-solar-arrow-left-outline class="w-4 h-4 mr-2" />
                            Anterior
                        </a>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span class="text-base text-black-400">Página</span>
                        <span class="text-base text-black-500 font-semibold">2</span>
                        <span class="text-base text-black-400">de</span>
                        <span class="text-base text-black-500 font-semibold">10</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a class="page-link bg-primary-200 text-white-50 font-medium rounded-lg px-4 py-2 flex items-center justify-center h-[40px] hover:bg-primary-500 transition-colors" href="javascript:void(0)">
                            Siguiente
                            <x-solar-arrow-right-outline class="w-4 h-4 ml-2" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 