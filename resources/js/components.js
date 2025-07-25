/**
 * Linkiu.bio - Components JavaScript
 * Manejo de funcionalidades de componentes UI
 */

// Utilidad para formatear tamaños de archivo
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Utilidad para crear iconos SVG
function createSVGIcon(type, size = 'default') {
    const sizes = {
        small: 'w-4 h-4',
        default: 'w-5 h-5',
        large: 'w-6 h-6'
    };
    
    const iconSize = sizes[size] || sizes.default;
    
    const icons = {
        close: `<svg class="${iconSize}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>`,
        link: `<svg class="${iconSize} text-black-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
        </svg>`,
        file: `<svg class="${iconSize} text-black-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>`
    };
    return icons[type] || '';
}

/**
 * Image Upload Components
 */
class ImageUploadManager {
    constructor() {
        this.init();
    }

    init() {
        this.initSingleImageUpload();
        this.initMultipleImageUpload();
        this.initFileListUpload();
        this.initDragDropUpload();
        this.initProgressUpload();
    }

    // Subida de imagen individual con preview
    initSingleImageUpload() {
        const fileInput = document.getElementById("upload-file");
        const imagePreview = document.getElementById("uploaded-img__preview");
        const uploadedImgContainer = document.querySelector(".uploaded-img");
        const removeButton = document.querySelector(".uploaded-img__remove");

        if (!fileInput || !imagePreview || !uploadedImgContainer || !removeButton) return;

        fileInput.addEventListener("change", (e) => {
            if (e.target.files.length) {
                const src = URL.createObjectURL(e.target.files[0]);
                imagePreview.src = src;
                uploadedImgContainer.classList.remove("hidden");
            }
        });

        removeButton.addEventListener("click", () => {
            if (imagePreview.src && imagePreview.src.startsWith('blob:')) {
                URL.revokeObjectURL(imagePreview.src);
            }
            imagePreview.src = "";
            uploadedImgContainer.classList.add("hidden");
            fileInput.value = "";
        });
    }

    // Subida múltiple con preview
    initMultipleImageUpload() {
        const fileInputMultiple = document.getElementById("upload-file-multiple");
        const uploadedImgsContainer = document.querySelector(".uploaded-imgs-container");

        if (!fileInputMultiple || !uploadedImgsContainer) return;

        fileInputMultiple.addEventListener("change", (e) => {
            const files = e.target.files;

            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) return;

                const src = URL.createObjectURL(file);
                const imgContainer = this.createImageContainer(src);
                uploadedImgsContainer.appendChild(imgContainer);
            });

            fileInputMultiple.value = "";
        });
    }

    // Crear contenedor de imagen con botón de eliminar
    createImageContainer(src) {
        const imgContainer = document.createElement("div");
        imgContainer.classList.add("relative", "h-[120px]", "w-[120px]", "border", "border-dashed", "border-white-200", "rounded-lg", "overflow-hidden", "bg-white-100");

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.classList.add("absolute", "top-0", "right-0", "z-10", "text-error-400", "hover:text-error-500", "m-2", "flex");
        removeButton.innerHTML = createSVGIcon('close');

        const imagePreview = document.createElement("img");
        imagePreview.classList.add("w-full", "h-full", "object-cover");
        imagePreview.src = src;

        removeButton.addEventListener("click", () => {
            URL.revokeObjectURL(src);
            imgContainer.remove();
        });

        imgContainer.appendChild(removeButton);
        imgContainer.appendChild(imagePreview);

        return imgContainer;
    }

    // Subida con lista de archivos
    initFileListUpload() {
        const fileUploadName = document.getElementById("file-upload-name");
        const uploadedImgNames = document.getElementById("uploaded-img-names");

        if (!fileUploadName || !uploadedImgNames) return;

        fileUploadName.addEventListener("change", (e) => {
            const files = e.target.files;
            
            uploadedImgNames.classList.add("show-uploaded-img-name");

            Array.from(files).forEach(file => {
                const li = this.createFileListItem(file);
                uploadedImgNames.appendChild(li);
            });

            fileUploadName.value = "";
        });
    }

    // Crear elemento de lista de archivo
    createFileListItem(file) {
        const li = document.createElement("li");
        li.classList.add("flex", "items-center", "gap-2", "p-2", "bg-white-100", "rounded-lg", "body-small", "text-black-400");

        const linkIcon = document.createElement("div");
        linkIcon.innerHTML = createSVGIcon('link', 'small');

        const fileName = document.createElement("span");
        fileName.textContent = file.name;
        fileName.classList.add("flex-1");

        const removeIcon = document.createElement("button");
        removeIcon.type = "button";
        removeIcon.classList.add("text-error-400", "hover:text-error-500", "flex");
        removeIcon.innerHTML = createSVGIcon('close', 'small');

        removeIcon.addEventListener("click", () => {
            li.remove();
        });

        li.appendChild(linkIcon);
        li.appendChild(fileName);
        li.appendChild(removeIcon);

        return li;
    }

    // Drag & Drop
    initDragDropUpload() {
        const dragDropArea = document.getElementById("drag-drop-area");
        const dragDropInput = document.getElementById("drag-drop-input");
        const dragDropFiles = document.getElementById("drag-drop-files");

        if (!dragDropArea || !dragDropInput || !dragDropFiles) return;

        // Prevenir comportamientos por defecto
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dragDropArea.addEventListener(eventName, this.preventDefaults, false);
            document.body.addEventListener(eventName, this.preventDefaults, false);
        });

        // Efectos visuales
        ['dragenter', 'dragover'].forEach(eventName => {
            dragDropArea.addEventListener(eventName, () => this.highlight(dragDropArea), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dragDropArea.addEventListener(eventName, () => this.unhighlight(dragDropArea), false);
        });

        // Manejar drop
        dragDropArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            this.handleDragDropFiles(files, dragDropFiles);
        }, false);

        // Manejar input
        dragDropInput.addEventListener('change', (e) => {
            const files = e.target.files;
            this.handleDragDropFiles(files, dragDropFiles);
        }, false);

        // Hacer clic en cualquier parte del área para abrir diálogo
        dragDropArea.addEventListener('click', (e) => {
            // Prevenir que se ejecute si ya se hizo clic en el label
            if (e.target.tagName !== 'INPUT') {
                dragDropInput.click();
            }
        });

        // Inicializar también las áreas de upload variants
        this.initUploadVariants();
    }

    // Inicializar upload variants (Primary, Secondary, Success)
    initUploadVariants() {
        const variants = [
            { area: 'primary-upload-area', input: 'primary-upload' },
            { area: 'secondary-upload-area', input: 'secondary-upload' },
            { area: 'success-upload-area', input: 'success-upload' }
        ];

        variants.forEach(variant => {
            const area = document.getElementById(variant.area);
            const input = document.getElementById(variant.input);
            
            if (!area || !input) return;

            // Prevenir comportamientos por defecto
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                area.addEventListener(eventName, this.preventDefaults, false);
            });

            // Efectos visuales
            ['dragenter', 'dragover'].forEach(eventName => {
                area.addEventListener(eventName, () => this.highlightVariant(area), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                area.addEventListener(eventName, () => this.unhighlightVariant(area), false);
            });

            // Manejar drop
            area.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                this.handleVariantFiles(files, area);
            }, false);

            // Manejar input
            input.addEventListener('change', (e) => {
                const files = e.target.files;
                this.handleVariantFiles(files, area);
            }, false);

            // Hacer clic en cualquier parte del área para abrir diálogo
            area.addEventListener('click', (e) => {
                if (e.target.tagName !== 'INPUT') {
                    input.click();
                }
            });
        });
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    highlight(element) {
        element.classList.add('border-primary-400', 'bg-primary-50');
    }

    unhighlight(element) {
        element.classList.remove('border-primary-400', 'bg-primary-50');
    }

    handleDragDropFiles(files, container) {
        Array.from(files).forEach(file => {
            const fileDiv = document.createElement("div");
            fileDiv.classList.add("flex", "items-center", "gap-3", "p-3", "bg-white-100", "rounded-lg", "body-small", "text-black-400");

            const fileIcon = document.createElement("div");
            fileIcon.innerHTML = createSVGIcon('file', 'small');

            const fileName = document.createElement("span");
            fileName.textContent = file.name;
            fileName.classList.add("flex-1");

            const fileSize = document.createElement("span");
            fileSize.textContent = formatFileSize(file.size);
            fileSize.classList.add("text-black-300");

            fileDiv.appendChild(fileIcon);
            fileDiv.appendChild(fileName);
            fileDiv.appendChild(fileSize);
            container.appendChild(fileDiv);
        });
    }

    // Efectos visuales para variantes de upload
    highlightVariant(element) {
        // Detectar el tipo de variante por sus clases
        if (element.classList.contains('border-primary-400')) {
            element.classList.add('border-primary-500', 'bg-primary-100');
        } else if (element.classList.contains('border-secondary-300')) {
            element.classList.add('border-secondary-400', 'bg-secondary-100');
        } else if (element.classList.contains('border-success-400')) {
            element.classList.add('border-success-500', 'bg-success-100');
        }
    }

    unhighlightVariant(element) {
        // Remover todos los efectos de hover
        element.classList.remove(
            'border-primary-500', 'bg-primary-100',
            'border-secondary-400', 'bg-secondary-100',
            'border-success-500', 'bg-success-100'
        );
    }

    // Manejar archivos de variantes
    handleVariantFiles(files, area) {
        // Crear contenedor de archivos si no existe
        let filesContainer = area.querySelector('.variant-files-container');
        if (!filesContainer) {
            filesContainer = document.createElement('div');
            filesContainer.classList.add('variant-files-container', 'mt-4', 'space-y-2');
            area.appendChild(filesContainer);
        }

        Array.from(files).forEach(file => {
            const fileDiv = document.createElement("div");
            fileDiv.classList.add("flex", "items-center", "gap-3", "p-2", "bg-white-100", "rounded-lg", "body-small", "text-black-400");

            const fileIcon = document.createElement("div");
            fileIcon.innerHTML = createSVGIcon('file');

            const fileName = document.createElement("span");
            fileName.textContent = file.name;
            fileName.classList.add("flex-1");

            const fileSize = document.createElement("span");
            fileSize.textContent = formatFileSize(file.size);
            fileSize.classList.add("text-black-300");

            const removeIcon = document.createElement("button");
            removeIcon.type = "button";
            removeIcon.classList.add("text-error-400", "hover:text-error-500", "flex");
            removeIcon.innerHTML = createSVGIcon('close', 'small');

            removeIcon.addEventListener("click", (e) => {
                e.stopPropagation();
                fileDiv.remove();
            });

            fileDiv.appendChild(fileIcon);
            fileDiv.appendChild(fileName);
            fileDiv.appendChild(fileSize);
            fileDiv.appendChild(removeIcon);
            filesContainer.appendChild(fileDiv);
        });
    }

    // Upload con progreso
    initProgressUpload() {
        const progressUpload = document.getElementById("progress-upload");
        const progressContainer = document.getElementById("progress-container");

        if (!progressUpload || !progressContainer) return;

        progressUpload.addEventListener("change", (e) => {
            const files = e.target.files;
            
            Array.from(files).forEach(file => {
                const progressDiv = this.createProgressItem(file);
                progressContainer.appendChild(progressDiv);
            });

            progressUpload.value = "";
        });
    }

    createProgressItem(file) {
        const progressDiv = document.createElement("div");
        progressDiv.classList.add("p-4", "bg-white-100", "rounded-lg");

        const fileInfo = document.createElement("div");
        fileInfo.classList.add("flex", "items-center", "justify-between", "mb-2");

        const fileName = document.createElement("span");
        fileName.textContent = file.name;
        fileName.classList.add("body-small", "text-black-400");

        const fileSize = document.createElement("span");
        fileSize.textContent = formatFileSize(file.size);
        fileSize.classList.add("body-small", "text-black-300");

        const progressBar = document.createElement("div");
        progressBar.classList.add("w-full", "bg-white-200", "rounded-full", "h-2");

        const progressFill = document.createElement("div");
        progressFill.classList.add("bg-primary-400", "h-2", "rounded-full", "transition-all", "duration-300");
        progressFill.style.width = "0%";

        fileInfo.appendChild(fileName);
        fileInfo.appendChild(fileSize);
        progressBar.appendChild(progressFill);
        progressDiv.appendChild(fileInfo);
        progressDiv.appendChild(progressBar);

        // Simular progreso de subida
        this.simulateUploadProgress(progressFill);

        return progressDiv;
    }

    simulateUploadProgress(progressElement) {
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                progressElement.classList.remove("bg-primary-400");
                progressElement.classList.add("bg-success-400");
            }
            progressElement.style.width = progress + "%";
        }, 200);
    }
}

/**
 * Inicialización de componentes
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Image Upload si existe en la página
    if (document.querySelector('[id*="upload-file"]')) {
        new ImageUploadManager();
    }

    // Inicializar Invoice Manager si existe en la página
    // COMENTADO TEMPORALMENTE - InvoiceManager no está definido
    // if (document.querySelector('#invoice-table, #invoiceTableBody')) {
    //     new InvoiceManager();
    // }

    // Inicializar Form Wizard Manager si existe en la página
    if (document.querySelector('.form-wizard, .form-wizard-labeled')) {
        new FormWizardManager();
    }
});

// Exportar para uso global
window.ImageUploadManager = ImageUploadManager;
// window.InvoiceManager = InvoiceManager; // COMENTADO - no está definido
window.FormWizardManager = FormWizardManager;
window.formatFileSize = formatFileSize; 