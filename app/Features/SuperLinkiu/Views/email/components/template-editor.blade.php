@props([
    'template',
    'availableVariables' => []
])

<div class="bg-accent-50 rounded-lg p-6">
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
</div>