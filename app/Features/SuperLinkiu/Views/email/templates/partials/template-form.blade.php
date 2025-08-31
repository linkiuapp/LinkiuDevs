@props([
    'template',
    'action',
    'method' => 'POST'
])

<form action="{{ $action }}" method="POST" id="templateForm">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif
    
    <!-- Template Name -->
    <div class="mb-6">
        <label for="name" class="block text-sm font-medium text-black-400 mb-2">
            Nombre de la Plantilla
        </label>
        <input 
            type="text" 
            id="name"
            name="name" 
            value="{{ old('name', $template->name ?? '') }}"
            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('name') border-error-300 @enderror"
            required
        >
        @error('name')
            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Subject -->
    <div class="mb-6">
        <label for="subject" class="block text-sm font-medium text-black-400 mb-2">
            Asunto del Email
        </label>
        <input 
            type="text" 
            id="subject"
            name="subject" 
            value="{{ old('subject', $template->subject ?? '') }}"
            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('subject') border-error-300 @enderror"
            placeholder="Ej: Bienvenido a {{store_name}}"
            required
        >
        @error('subject')
            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-black-300 mt-1">Puedes usar variables como {{store_name}} en el asunto</p>
    </div>

    <!-- HTML Content -->
    <div class="mb-6">
        <label for="body_html" class="block text-sm font-medium text-black-400 mb-2">
            Contenido HTML
        </label>
        <textarea 
            id="body_html"
            name="body_html" 
            rows="12"
            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 font-mono text-sm @error('body_html') border-error-300 @enderror"
            placeholder="<p>Hola {{admin_name}},</p><p>Tu tienda {{store_name}} ha sido creada exitosamente.</p>"
        >{{ old('body_html', $template->body_html ?? '') }}</textarea>
        @error('body_html')
            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-black-300 mt-1">HTML básico permitido: p, br, strong, em, ul, li, ol, a, div, span</p>
    </div>

    <!-- Text Content -->
    <div class="mb-6">
        <label for="body_text" class="block text-sm font-medium text-black-400 mb-2">
            Contenido de Texto (Opcional)
        </label>
        <textarea 
            id="body_text"
            name="body_text" 
            rows="8"
            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 font-mono text-sm @error('body_text') border-error-300 @enderror"
            placeholder="Hola {{admin_name}}, Tu tienda {{store_name}} ha sido creada exitosamente."
        >{{ old('body_text', $template->body_text ?? '') }}</textarea>
        @error('body_text')
            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-black-300 mt-1">Versión de texto plano para clientes que no soportan HTML</p>
    </div>

    <!-- Active Status -->
    <div class="mb-6">
        <label class="flex items-center gap-3">
            <input 
                type="checkbox" 
                name="is_active" 
                value="1"
                {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}
                class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200"
            >
            <span class="text-sm font-medium text-black-400">Plantilla activa</span>
        </label>
        <p class="text-xs text-black-300 mt-1 ml-7">Solo las plantillas activas se utilizarán para enviar emails</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-between items-center pt-6 border-t border-accent-200">
        <div class="text-sm text-black-300">
            <x-solar-info-circle-outline class="w-4 h-4 inline mr-1" />
            Los cambios se aplicarán inmediatamente
        </div>
        
        <div class="flex gap-3">
            <button type="button" onclick="resetForm()" class="bg-accent-200 hover:bg-accent-300 text-black-400 px-4 py-2 rounded-lg transition-colors">
                Restaurar
            </button>
            <button type="submit" class="bg-primary-200 hover:bg-primary-300 text-accent-50 px-6 py-2 rounded-lg transition-colors">
                Guardar Plantilla
            </button>
        </div>
    </div>
</form>