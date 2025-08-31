{{-- ================================================================ --}}
{{-- ENHANCED CREDENTIAL MODAL - Tienda Creada --}}
{{-- ================================================================ --}}

@if(session('admin_credentials'))
{{-- Debug script para verificar que se ejecute --}}
<script>
console.log('🟢 SUCCESS MODAL: Modal de éxito detectado en DOM');
console.log('📊 SUCCESS MODAL: Credenciales disponibles:', @json(session('admin_credentials')));
</script>

<x-superlinkiu::enhanced-credential-modal
    :credentials="session('admin_credentials')"
    :show="true"
/>
@endif 