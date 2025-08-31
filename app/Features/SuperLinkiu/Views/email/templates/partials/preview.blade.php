<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-accent-50 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-accent-200">
                <h3 class="text-lg font-semibold text-black-500">Vista Previa de Plantilla</h3>
                <button onclick="closePreview()" class="text-black-300 hover:text-black-500">
                    <x-solar-close-circle-outline class="w-6 h-6" />
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                <div id="previewContent">
                    <div class="text-center py-8">
                        <div class="animate-spin w-8 h-8 border-2 border-primary-200 border-t-transparent rounded-full mx-auto"></div>
                        <p class="text-black-300 mt-2">Generando vista previa...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('previewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview();
    }
});
</script>