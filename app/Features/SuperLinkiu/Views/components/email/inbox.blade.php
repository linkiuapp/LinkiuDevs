@extends('shared::layouts.admin')

@section('title', 'Email Inbox')

@section('content')
<div class="bg-white-50 rounded-lg p-0 overflow-hidden">
    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
        <h2 class="text-3xl text-black-500 mb-0">Email Inbox</h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <!-- Sidebar -->
            <div class="col-span-12 xl:col-span-4 2xl:col-span-3">
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                    <div class="p-6">
                        <button type="button" class="bg-primary-400 hover:bg-primary-500 text-white-50 text-sm px-3 py-3 w-full rounded-lg flex items-center gap-2 mb-4 transition-colors">
                            <x-solar-add-square-outline class="w-5 h-5" />
                            Redactar
                        </button>

                        <div class="mt-4">
                            <ul>
                                <li class="mb-1.5">
                                    <a href="#" class="hover:bg-primary-50 group hover:text-black-400 px-2.5 py-2.5 w-full rounded-lg text-black-300 flex items-center transition-colors">
                                        <span class="flex items-center gap-2.5 justify-between w-full">
                                            <span class="flex items-center gap-2.5">
                                                <x-solar-inbox-outline class="w-5 h-5 group-hover:text-primary-400" />
                                                <span class="font-semibold">Bandeja de entrada</span>
                                            </span>
                                            <span class="font-medium">800</span>
                                        </span>
                                    </a>
                                </li>
                                <li class="mb-1.5">
                                    <a href="#" class="hover:bg-primary-50 group hover:text-black-400 px-2.5 py-2.5 w-full rounded-lg text-black-300 flex items-center transition-colors">
                                        <span class="flex items-center gap-2.5 justify-between w-full">
                                            <span class="flex items-center gap-2.5">
                                                <x-solar-star-outline class="w-5 h-5 group-hover:text-primary-400" />
                                                <span class="font-semibold">Destacados</span>
                                            </span>
                                            <span class="font-medium">250</span>
                                        </span>
                                    </a>
                                </li>
                                <li class="mb-1.5">
                                    <a href="#" class="hover:bg-primary-50 group hover:text-black-400 px-2.5 py-2.5 w-full rounded-lg text-black-300 flex items-center transition-colors">
                                        <span class="flex items-center gap-2.5 justify-between w-full">
                                            <span class="flex items-center gap-2.5">
                                                <x-solar-plain-2-outline class="w-5 h-5 group-hover:text-primary-400" />
                                                <span class="font-semibold">Enviados</span>
                                            </span>
                                            <span class="font-medium">80</span>
                                        </span>
                                    </a>
                                </li>
                                <li class="mb-1.5">
                                    <a href="#" class="hover:bg-primary-50 group hover:text-black-400 px-2.5 py-2.5 w-full rounded-lg text-black-300 flex items-center transition-colors">
                                        <span class="flex items-center gap-2.5 justify-between w-full">
                                            <span class="flex items-center gap-2.5">
                                                <x-solar-pen-outline class="w-5 h-5 group-hover:text-primary-400" />
                                                <span class="font-semibold">Borradores</span>
                                            </span>
                                            <span class="font-medium">50</span>
                                        </span>
                                    </a>
                                </li>
                                <li class="mb-1.5">
                                    <a href="#" class="hover:bg-primary-50 group hover:text-black-400 px-2.5 py-2.5 w-full rounded-lg text-black-300 flex items-center transition-colors">
                                        <span class="flex items-center gap-2.5 justify-between w-full">
                                            <span class="flex items-center gap-2.5">
                                                <x-solar-danger-outline class="w-5 h-5 group-hover:text-primary-400" />
                                                <span class="font-semibold">Spam</span>
                                            </span>
                                            <span class="font-medium">30</span>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="hover:bg-primary-50 group hover:text-black-400 px-2.5 py-2.5 w-full rounded-lg text-black-300 flex items-center transition-colors">
                                        <span class="flex items-center gap-2.5 justify-between w-full">
                                            <span class="flex items-center gap-2.5">
                                                <x-solar-trash-bin-minimalistic-outline class="w-5 h-5 group-hover:text-primary-400" />
                                                <span class="font-semibold">Papelera</span>
                                            </span>
                                            <span class="font-medium">20</span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                            <div class="mt-6">
                                <h6 class="text-lg font-semibold text-black-400 mb-4">ETIQUETAS</h6>
                                <ul>
                                    <li class="mb-5">
                                        <span class="font-medium text-black-300 text-sm flex items-center gap-2.5">
                                            <span class="w-2 h-2 bg-primary-400 rounded-full"></span>
                                            Personal
                                        </span>
                                    </li>
                                    <li class="mb-5">
                                        <span class="font-medium text-black-300 text-sm flex items-center gap-2.5">
                                            <span class="w-2 h-2 bg-secondary-400 rounded-full"></span>
                                            Social
                                        </span>
                                    </li>
                                    <li class="mb-5">
                                        <span class="font-medium text-black-300 text-sm flex items-center gap-2.5">
                                            <span class="w-2 h-2 bg-success-400 rounded-full"></span>
                                            Promociones
                                        </span>
                                    </li>
                                    <li class="mb-5">
                                        <span class="font-medium text-black-300 text-sm flex items-center gap-2.5">
                                            <span class="w-2 h-2 bg-warning-300 rounded-full"></span>
                                            Negocios
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-span-12 xl:col-span-8 2xl:col-span-9">
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                    <!-- Header -->
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center">
                                    <input type="checkbox" class="rounded border bg-white-50 text-primary-400 focus:ring-primary-400" id="selectAll">
                                    <div class="ml-2">
                                        <button type="button" class="text-black-300 hover:text-black-500 transition-colors">
                                            <x-solar-alt-arrow-down-outline class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="text-black-300 hover:text-error-400 transition-colors">
                                    <x-solar-trash-bin-minimalistic-outline class="w-5 h-5" />
                                </button>
                                <button type="button" class="text-black-300 hover:text-black-500 transition-colors">
                                    <x-solar-refresh-outline class="w-5 h-5" />
                                </button>
                                <button type="button" class="text-black-300 hover:text-black-500 transition-colors">
                                    <x-solar-menu-dots-outline class="w-5 h-5" />
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-black-300">1-12 de 1,253</span>
                                <div class="flex items-center gap-1">
                                    <button class="p-2 text-black-300 hover:text-black-500 border border-white-200 rounded transition-colors">
                                        <x-solar-arrow-left-outline class="w-4 h-4" />
                                    </button>
                                    <button class="p-2 text-black-300 hover:text-black-500 border border-white-200 rounded transition-colors">
                                        <x-solar-arrow-right-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email List -->
                    <div class="overflow-x-auto">
                        <ul class="min-w-max">
                            @for ($i = 0; $i < 12; $i++)
                            <li class="px-6 py-4 flex gap-4 items-center border-b border-white-100 cursor-pointer hover:bg-white-100 transition-colors">
                                <div class="flex items-center">
                                    <input type="checkbox" class="rounded border bg-white-50 text-primary-400 focus:ring-primary-400">
                                </div>
                                <button type="button" class="text-warning-300 hover:text-warning-400 transition-colors">
                                    <x-solar-star-outline class="w-5 h-5" />
                                </button>
                                <a href="#" class="text-black-400 font-medium text-base w-[190px] truncate">
                                    {{ ['Jerome Bell', 'Kristin Watson', 'Cody Fisher', 'Dianne Russell', 'Floyd Miles', 'Devon Lane', 'Annette Black', 'Bessie Cooper', 'Courtney Henry', 'Wade Warren', 'Kathryn Murphy', 'Brooklyn Simmons'][$i] }}
                                </a>
                                <a href="#" class="text-black-400 font-medium flex-1 line-clamp-1">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.
                                </a>
                                <span class="text-black-300 font-medium whitespace-nowrap">
                                    {{ ['6:07 AM', '5:30 PM', '11:45 AM', '2:15 PM', '8:20 AM', '4:55 PM', '10:30 AM', '1:40 PM', '7:25 AM', '3:50 PM', '9:15 AM', '6:30 PM'][$i] }}
                                </span>
                            </li>
                            @endfor
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos adicionales para funcionalidad */
.email-item.active {
    background-color: rgb(var(--primary-50));
}

.starred-button.active {
    color: rgb(var(--warning-400));
}

.delete-button.hidden {
    display: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad del checkbox principal
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('input[type="checkbox"]:not(#selectAll)');
    
    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            const listItem = checkbox.closest('li');
            if (this.checked) {
                listItem?.classList.add('active');
            } else {
                listItem?.classList.remove('active');
            }
        });
    });
    
    // Funcionalidad de checkboxes individuales
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const listItem = this.closest('li');
            if (this.checked) {
                listItem?.classList.add('active');
            } else {
                listItem?.classList.remove('active');
            }
        });
    });
    
    // Funcionalidad de starred
    const starredButtons = document.querySelectorAll('.starred-button');
    starredButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    });
});
</script>
@endsection 