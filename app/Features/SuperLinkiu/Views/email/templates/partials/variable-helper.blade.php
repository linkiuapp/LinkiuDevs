@props([
    'availableVariables' => []
])

<div class="bg-accent-50 rounded-lg p-6 sticky top-6">
    <h3 class="text-lg font-semibold text-black-500 mb-4 flex items-center gap-2">
        <x-solar-code-outline class="w-5 h-5" />
        Variables Disponibles
    </h3>
    
    <div class="space-y-4">
        @foreach($availableVariables as $variable => $description)
            <div class="bg-accent-100 p-3 rounded-lg">
                <div class="flex items-center justify-between mb-1">
                    <code class="text-sm font-mono text-primary-400 bg-accent-50 px-2 py-1 rounded">{{ $variable }}</code>
                    <button onclick="insertVariable('{{ $variable }}')" class="text-black-300 hover:text-black-500 transition-colors" title="Insertar variable">
                        <x-solar-copy-outline class="w-4 h-4" />
                    </button>
                </div>
                <p class="text-xs text-black-300">{{ $description }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 p-4 bg-info-100 rounded-lg">
        <h4 class="font-semibold text-info-700 mb-2 flex items-center gap-2">
            <x-solar-lightbulb-outline class="w-4 h-4" />
            Consejos
        </h4>
        <ul class="text-xs text-info-600 space-y-1">
            <li>• Haz clic en una variable para copiarla</li>
            <li>• Usa la vista previa para probar cambios</li>
            <li>• Las variables se reemplazan automáticamente</li>
            <li>• El HTML se sanitiza por seguridad</li>
        </ul>
    </div>
</div>

<script>
function insertVariable(variable) {
    // Get the currently focused textarea
    const activeElement = document.activeElement;
    let targetTextarea = null;
    
    if (activeElement && (activeElement.id === 'subject' || activeElement.id === 'body_html' || activeElement.id === 'body_text')) {
        targetTextarea = activeElement;
    } else {
        // Default to body_html if no textarea is focused
        targetTextarea = document.getElementById('body_html');
    }
    
    if (targetTextarea) {
        const start = targetTextarea.selectionStart;
        const end = targetTextarea.selectionEnd;
        const text = targetTextarea.value;
        
        targetTextarea.value = text.substring(0, start) + variable + text.substring(end);
        targetTextarea.focus();
        targetTextarea.setSelectionRange(start + variable.length, start + variable.length);
    }
    
    // Show feedback
    const button = event.target.closest('button');
    const originalIcon = button.innerHTML;
    button.innerHTML = '<x-solar-check-circle-outline class="w-4 h-4" />';
    button.classList.add('text-success-300');
    
    setTimeout(() => {
        button.innerHTML = originalIcon;
        button.classList.remove('text-success-300');
    }, 1000);
}
</script>