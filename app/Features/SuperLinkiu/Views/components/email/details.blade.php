@extends('shared::layouts.admin')

@section('title', 'Email Details')

@section('content')
<div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
    <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
        <h2 class="text-3xl text-black-500 mb-0">Email Details</h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <!-- Sidebar -->
            <div class="col-span-12 xl:col-span-4 2xl:col-span-3">
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <div class="p-6">
                        <button type="button" class="bg-primary-400 hover:bg-primary-500 text-accent-50 text-sm px-3 py-3 w-full rounded-lg flex items-center gap-2 mb-4 transition-colors">
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
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <div class="flex flex-col justify-between h-full">
                        <div>
                            <!-- Header -->
                            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6 flex items-center gap-3 justify-between flex-wrap">
                                <div class="flex items-center gap-2">
                                    <button class="text-black-300 hover:text-black-500 transition-colors">
                                        <x-solar-arrow-left-outline class="w-5 h-5" />
                                    </button>
                                    <h6 class="mb-0 text-lg text-black-500">Kathryn Murphy</h6>
                                    <span class="bg-primary-50 text-primary-400 text-sm rounded px-2 py-1">Personal</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button class="text-black-300 hover:text-black-500 transition-colors">
                                        <x-solar-printer-outline class="w-5 h-5" />
                                    </button>
                                    <button class="text-black-300 hover:text-warning-400 transition-colors">
                                        <x-solar-star-outline class="w-5 h-5" />
                                    </button>
                                    <button class="text-black-300 hover:text-error-400 transition-colors">
                                        <x-solar-trash-bin-minimalistic-outline class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>

                            <!-- Email Content -->
                            <div class="p-0">
                                <!-- Email Messages -->
                                <div class="py-4 px-6 border-b border-accent-100">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                            <span class="text-primary-400 font-semibold">KM</span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center flex-wrap gap-2 mb-3">
                                                <h6 class="mb-0 text-lg text-black-500">Kathryn Murphy</h6>
                                                <span class="text-black-300 text-base">kathrynmurphy@gmail.com</span>
                                            </div>
                                            <div class="space-y-4">
                                                <p class="text-black-400">Hola William,</p>
                                                <p class="text-black-400">
                                                    Solo confirmando que transferimos $63.86 a tu cuenta via PayPal 
                                                    <a href="#" class="text-primary-400 underline hover:text-primary-500 transition-colors">(info367@gmail.com)</a> 
                                                    que ganaste en el Market de themewow desde tu último pago.
                                                </p>
                                                <p class="text-black-400">¡Gracias por vender con nosotros!</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reply Message -->
                                <div class="py-4 px-6 border-b border-accent-100">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-secondary-100 rounded-full flex items-center justify-center">
                                            <span class="text-secondary-400 font-semibold">SS</span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center flex-wrap gap-2 mb-3">
                                                <h6 class="mb-0 text-lg text-black-500">Subrata Sen</h6>
                                                <span class="text-black-300 text-base">subratasen@gmail.com</span>
                                            </div>
                                            <div class="space-y-4">
                                                <p class="text-black-400">¡Genial, muchas gracias!</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reply Form -->
                        <div class="py-4 px-6 bg-accent-50 border-t border-accent-100">
                            <form>
                                <div class="flex items-end gap-4">
                                    <div class="flex-1">
                                        <textarea 
                                            class="w-full p-3 bg-transparent border-0 resize-none focus:ring-0 focus:outline-none" 
                                            rows="3" 
                                            placeholder="Escribe tu respuesta..."
                                            style="min-height: 44px; max-height: 120px;"
                                            oninput="this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 120) + 'px';"
                                        ></textarea>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <label for="attachment" class="text-black-300 hover:text-black-500 cursor-pointer transition-colors">
                                                <x-solar-paperclip-outline class="w-5 h-5" />
                                            </label>
                                            <input type="file" id="attachment" class="hidden">
                                        </div>
                                        <div>
                                            <label for="gallery" class="text-black-300 hover:text-black-500 cursor-pointer transition-colors">
                                                <x-solar-gallery-outline class="w-5 h-5" />
                                            </label>
                                            <input type="file" id="gallery" class="hidden" accept="image/*">
                                        </div>
                                        <button type="submit" class="bg-primary-400 hover:bg-primary-500 text-accent-50 text-sm px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                                            <x-solar-plain-2-outline class="w-4 h-4" />
                                            Enviar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para el área de respuesta */
.reply-textarea {
    scrollbar-width: thin;
    scrollbar-color: rgb(var(--accent-200)) transparent;
}

.reply-textarea::-webkit-scrollbar {
    width: 4px;
}

.reply-textarea::-webkit-scrollbar-track {
    background: transparent;
}

.reply-textarea::-webkit-scrollbar-thumb {
    background: rgb(var(--accent-200));
    border-radius: 2px;
}

.reply-textarea::-webkit-scrollbar-thumb:hover {
    background: rgb(var(--accent-300));
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad para ajustar altura del textarea
    const textarea = document.querySelector('textarea');
    
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }
    
    // Funcionalidad del botón starred
    const starButton = document.querySelector('.star-button');
    if (starButton) {
        starButton.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    }
});
</script>
@endsection 