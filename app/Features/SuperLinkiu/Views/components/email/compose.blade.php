@extends('shared::layouts.admin')

@section('title', 'Redactar Email')

@section('content')
<div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
    <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
        <h2 class="text-3xl text-black-500 mb-0">Redactar Email</h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <!-- Sidebar -->
            <div class="col-span-12 xl:col-span-4 2xl:col-span-3">
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <div class="p-6">
                        <button type="button" class="bg-primary-400 hover:bg-primary-500 text-accent-50 text-sm px-3 py-3 w-full rounded-lg flex items-center gap-2 mb-4 transition-colors">
                            <x-solar-add-square-outline class="w-5 h-5" />
                            Nuevo Email
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
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-span-12 xl:col-span-8 2xl:col-span-9">
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <!-- Header -->
                    <div class="border-b border-accent-100 bg-accent-50 py-4 px-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button class="text-black-300 hover:text-black-500 transition-colors">
                                <x-solar-arrow-left-outline class="w-5 h-5" />
                            </button>
                            <h3 class="text-xl text-black-500">Nuevo mensaje</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="text-black-300 hover:text-black-500 transition-colors">
                                <x-solar-minimize-outline class="w-5 h-5" />
                            </button>
                            <button type="button" class="text-black-300 hover:text-black-500 transition-colors">
                                <x-solar-close-square-outline class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Compose Form -->
                    <div class="p-6">
                        <form class="space-y-6">
                            <!-- Para -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                <label class="text-black-400 font-medium md:col-span-2">Para:</label>
                                <div class="md:col-span-10">
                                    <input type="email" class="w-full px-4 py-2 bg-accent-50 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent" placeholder="destinatario@email.com">
                                </div>
                            </div>

                            <!-- CC -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                <label class="text-black-400 font-medium md:col-span-2">CC:</label>
                                <div class="md:col-span-10">
                                    <input type="email" class="w-full px-4 py-2 bg-accent-50 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent" placeholder="copia@email.com">
                                </div>
                            </div>

                            <!-- CCO -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                <label class="text-black-400 font-medium md:col-span-2">CCO:</label>
                                <div class="md:col-span-10">
                                    <input type="email" class="w-full px-4 py-2 bg-accent-50 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent" placeholder="copia.oculta@email.com">
                                </div>
                            </div>

                            <!-- Asunto -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                <label class="text-black-400 font-medium md:col-span-2">Asunto:</label>
                                <div class="md:col-span-10">
                                    <input type="text" class="w-full px-4 py-2 bg-accent-50 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent" placeholder="Asunto del email">
                                </div>
                            </div>

                            <!-- Toolbar -->
                            <div class="border-t border-accent-100 pt-4">
                                <div class="flex flex-wrap items-center gap-2 mb-4 p-3 bg-accent-100 rounded-lg">
                                    <button type="button" class="p-2 text-black-300 hover:text-black-500 hover:bg-accent-200 rounded transition-colors">
                                        <x-solar-text-bold-outline class="w-5 h-5" />
                                    </button>
                                    <button type="button" class="p-2 text-black-300 hover:text-black-500 hover:bg-accent-200 rounded transition-colors">
                                        <x-solar-text-italic-outline class="w-5 h-5" />
                                    </button>
                                    <button type="button" class="p-2 text-black-300 hover:text-black-500 hover:bg-accent-200 rounded transition-colors">
                                        <x-solar-text-underline-outline class="w-5 h-5" />
                                    </button>
                                    <div class="w-px h-6 bg-accent-200"></div>
                                    <button type="button" class="p-2 text-black-300 hover:text-black-500 hover:bg-accent-200 rounded transition-colors">
                                        <x-solar-list-outline class="w-5 h-5" />
                                    </button>
                                    <button type="button" class="p-2 text-black-300 hover:text-black-500 hover:bg-accent-200 rounded transition-colors">
                                        <x-solar-list-check-outline class="w-5 h-5" />
                                    </button>
                                    <div class="w-px h-6 bg-accent-200"></div>
                                    <button type="button" class="p-2 text-black-300 hover:text-black-500 hover:bg-accent-200 rounded transition-colors">
                                        <x-solar-link-outline class="w-5 h-5" />
                                    </button>
                                    <button type="button" class="p-2 text-black-300 hover:text-black-500 hover:bg-accent-200 rounded transition-colors">
                                        <x-solar-gallery-outline class="w-5 h-5" />
                                    </button>
                                    <button type="button" class="p-2 text-black-300 hover:text-black-500 hover:bg-accent-200 rounded transition-colors">
                                        <x-solar-smile-circle-outline class="w-5 h-5" />
                                    </button>
                                </div>

                                <!-- Editor -->
                                <div class="border border-accent-200 rounded-lg">
                                    <textarea 
                                        class="w-full p-4 bg-accent-50 border-0 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-primary-400" 
                                        rows="12" 
                                        placeholder="Escribe tu mensaje aquí..."
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Attachments -->
                            <div class="space-y-4">
                                <h4 class="text-black-400 font-medium">Archivos adjuntos</h4>
                                <div class="border-2 border-dashed border-accent-200 rounded-lg p-6 text-center">
                                    <div class="space-y-2">
                                        <x-solar-upload-outline class="w-8 h-8 text-black-300 mx-auto" />
                                        <p class="text-black-300">Arrastra archivos aquí o <button type="button" class="text-primary-400 hover:text-primary-500 underline">selecciona archivos</button></p>
                                        <p class="text-black-200 text-sm">Formatos admitidos: PDF, DOC, DOCX, JPG, PNG (máx. 10MB)</p>
                                    </div>
                                    <input type="file" class="hidden" multiple>
                                </div>

                                <!-- Lista de archivos (ejemplo) -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between p-3 bg-accent-100 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                                <x-solar-file-text-outline class="w-5 h-5 text-primary-400" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-black-500">documento.pdf</p>
                                                <p class="text-sm text-black-300">2.5 MB</p>
                                            </div>
                                        </div>
                                        <button type="button" class="text-black-300 hover:text-error-400 transition-colors">
                                            <x-solar-trash-bin-minimalistic-outline class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-accent-100">
                                <div class="flex items-center gap-3">
                                    <button type="button" class="bg-primary-400 hover:bg-primary-500 text-accent-50 px-6 py-2 rounded-lg flex items-center gap-2 transition-colors">
                                        <x-solar-plain-2-outline class="w-5 h-5" />
                                        Enviar
                                    </button>
                                    <button type="button" class="bg-accent-100 hover:bg-accent-200 text-black-400 px-6 py-2 rounded-lg flex items-center gap-2 transition-colors">
                                        <x-solar-archive-outline class="w-5 h-5" />
                                        Guardar borrador
                                    </button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="text-black-300 hover:text-black-500 transition-colors">
                                        <x-solar-paperclip-outline class="w-5 h-5" />
                                    </button>
                                    <button type="button" class="text-black-300 hover:text-black-500 transition-colors">
                                        <x-solar-clock-circle-outline class="w-5 h-5" />
                                    </button>
                                    <button type="button" class="text-black-300 hover:text-error-400 transition-colors">
                                        <x-solar-trash-bin-minimalistic-outline class="w-5 h-5" />
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

<style>
/* Estilos para el editor */
.editor-toolbar button.active {
    background-color: rgb(var(--primary-50));
    color: rgb(var(--primary-400));
}

/* Estilos para drag and drop */
.drag-over {
    border-color: rgb(var(--primary-400));
    background-color: rgb(var(--primary-50));
}

/* Estilos para las etiquetas de archivos */
.file-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.file-tag {
    background-color: rgb(var(--accent-100));
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    color: rgb(var(--black-400));
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad de toolbar
    const toolbarButtons = document.querySelectorAll('.editor-toolbar button');
    toolbarButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    });

    // Funcionalidad de drag and drop
    const dropZone = document.querySelector('.border-dashed');
    const fileInput = dropZone.querySelector('input[type="file"]');
    
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', function() {
        this.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        // Aquí se manejarían los archivos
        console.log('Archivos seleccionados:', files);
    }

    // Auto-resize textarea
    const textarea = document.querySelector('textarea');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
});
</script>
@endsection 