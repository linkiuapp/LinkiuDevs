/**
 * =============================================================================
 * SCRIPT DE VERIFICACIÓN RÁPIDA - SISTEMA DE VERIFICACIÓN DE TIENDAS
 * =============================================================================
 * 
 * INSTRUCCIONES:
 * 1. Ir a: http://localhost:8000/superlinkiu/stores
 * 2. Abrir DevTools (F12)
 * 3. Ir a la pestaña Console
 * 4. Copiar y pegar TODO este código
 * 5. Presionar Enter
 * 
 * El script verificará automáticamente todos los componentes y te dirá
 * exactamente qué está funcionando y qué necesita arreglo.
 * =============================================================================
 */

console.clear();
console.log('🔍 INICIANDO DIAGNÓSTICO COMPLETO DEL SISTEMA DE VERIFICACIÓN...\n');

// =============================================================================
// VERIFICACIÓN DE COMPONENTES BÁSICOS
// =============================================================================

let diagnosis = {
    alpine: false,
    storeUtils: false,
    csrfToken: false,
    toggles: 0,
    storeManagement: false,
    errors: []
};

// 1. Verificar Alpine.js
try {
    if (typeof Alpine !== 'undefined') {
        diagnosis.alpine = true;
        console.log('✅ Alpine.js está disponible');
    } else {
        diagnosis.errors.push('❌ Alpine.js NO está disponible');
        console.error('❌ Alpine.js NO está disponible');
    }
} catch (e) {
    diagnosis.errors.push('❌ Error verificando Alpine.js: ' + e.message);
    console.error('❌ Error verificando Alpine.js:', e);
}

// 2. Verificar StoreUtils
try {
    if (typeof StoreUtils !== 'undefined') {
        diagnosis.storeUtils = true;
        console.log('✅ StoreUtils está disponible');
        
        // Verificar métodos principales
        const methods = ['getCsrfToken', 'apiCall', 'debug'];
        methods.forEach(method => {
            if (typeof StoreUtils[method] === 'function') {
                console.log(`  ✅ StoreUtils.${method}() disponible`);
            } else {
                diagnosis.errors.push(`❌ StoreUtils.${method}() NO está disponible`);
                console.error(`❌ StoreUtils.${method}() NO está disponible`);
            }
        });
    } else {
        diagnosis.errors.push('❌ StoreUtils NO está disponible');
        console.error('❌ StoreUtils NO está disponible');
    }
} catch (e) {
    diagnosis.errors.push('❌ Error verificando StoreUtils: ' + e.message);
    console.error('❌ Error verificando StoreUtils:', e);
}

// 3. Verificar CSRF Token
try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        diagnosis.csrfToken = true;
        console.log('✅ CSRF Token está presente:', csrfToken.substring(0, 20) + '...');
    } else {
        diagnosis.errors.push('❌ CSRF Token NO encontrado');
        console.error('❌ CSRF Token NO encontrado');
    }
} catch (e) {
    diagnosis.errors.push('❌ Error verificando CSRF Token: ' + e.message);
    console.error('❌ Error verificando CSRF Token:', e);
}

// 4. Verificar Toggles de Verificación
try {
    const toggles = document.querySelectorAll('.verification-toggle');
    diagnosis.toggles = toggles.length;
    
    if (toggles.length > 0) {
        console.log(`✅ ${toggles.length} toggle(s) de verificación encontrados`);
        
        toggles.forEach((toggle, index) => {
            console.log(`  Toggle ${index + 1}:`, {
                dataUrl: toggle.dataset.url,
                storeId: toggle.dataset.storeId,
                checked: toggle.checked,
                hasClass: toggle.classList.contains('verification-toggle')
            });
        });
    } else {
        diagnosis.errors.push('⚠️ No se encontraron toggles de verificación en esta página');
        console.warn('⚠️ No se encontraron toggles de verificación');
        console.log('   Esto es normal si no hay tiendas en la lista o estás en otra página');
    }
} catch (e) {
    diagnosis.errors.push('❌ Error verificando toggles: ' + e.message);
    console.error('❌ Error verificando toggles:', e);
}

// 5. Verificar que storeManagement esté registrado en Alpine
try {
    // Intentar acceder al componente storeManagement
    const storeElement = document.querySelector('[x-data="storeManagement"]');
    if (storeElement) {
        diagnosis.storeManagement = true;
        console.log('✅ Elemento con x-data="storeManagement" encontrado');
    } else {
        diagnosis.errors.push('⚠️ No se encontró elemento con x-data="storeManagement"');
        console.warn('⚠️ No se encontró elemento con x-data="storeManagement"');
        console.log('   Esto es normal si no estás en la página de stores');
    }
} catch (e) {
    diagnosis.errors.push('❌ Error verificando storeManagement: ' + e.message);
    console.error('❌ Error verificando storeManagement:', e);
}

// =============================================================================
// RESUMEN DEL DIAGNÓSTICO
// =============================================================================

console.log('\n📊 RESUMEN DEL DIAGNÓSTICO:');
console.log('='.repeat(50));

if (diagnosis.alpine && diagnosis.storeUtils && diagnosis.csrfToken) {
    console.log('🎉 ¡SISTEMA BÁSICO FUNCIONANDO CORRECTAMENTE!');
    console.log('✅ Todos los componentes principales están disponibles');
    
    if (diagnosis.toggles > 0) {
        console.log('✅ Se encontraron toggles de verificación');
        console.log('\n🎯 PRÓXIMO PASO: Activar debugging detallado');
        console.log('   Ejecuta: localStorage.setItem("store_debug", "true"); location.reload();');
    } else {
        console.log('\n💡 INFORMACIÓN: No hay toggles en esta página');
        console.log('   Ve a la página de stores para ver los toggles de verificación');
    }
} else {
    console.log('🚨 PROBLEMAS ENCONTRADOS:');
    diagnosis.errors.forEach(error => {
        console.log('   ' + error);
    });
}

// =============================================================================
// FUNCIONES DE TESTING ADICIONALES
// =============================================================================

console.log('\n🔧 FUNCIONES DE TESTING DISPONIBLES:');
console.log('📝 Puedes ejecutar estas funciones para testing adicional:');

// Función para test completo
window.testStoreSystem = function() {
    console.log('🧪 Ejecutando test completo del sistema...');
    
    // Test CSRF
    if (diagnosis.storeUtils) {
        try {
            const token = StoreUtils.getCsrfToken();
            console.log('✅ CSRF Token obtenido exitosamente');
        } catch (e) {
            console.error('❌ Error obteniendo CSRF Token:', e);
        }
    }
    
    // Test de toggles
    if (diagnosis.toggles > 0) {
        const firstToggle = document.querySelector('.verification-toggle');
        if (firstToggle) {
            console.log('✅ Primer toggle encontrado:', {
                url: firstToggle.dataset.url,
                storeId: firstToggle.dataset.storeId,
                currentState: firstToggle.checked
            });
        }
    }
    
    console.log('🎯 Test completo finalizado');
};

// Función para test de API
window.testVerificationAPI = async function(storeId) {
    if (!storeId) {
        console.error('❌ Debes proporcionar un storeId: testVerificationAPI(1)');
        return;
    }
    
    console.log(`🧪 Testing API de verificación para store ${storeId}...`);
    
    try {
        const csrfToken = StoreUtils.getCsrfToken();
        const url = `/superlinkiu/stores/${storeId}/toggle-verification`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        console.log('✅ API Response:', data);
        return data;
    } catch (error) {
        console.error('❌ API Test failed:', error);
        return false;
    }
};

console.log('   • testStoreSystem() - Test completo del sistema');
console.log('   • testVerificationAPI(storeId) - Test de API específico');

console.log('\n' + '='.repeat(50));
console.log('✨ DIAGNÓSTICO COMPLETADO');

// Retornar diagnosis para programmatic access
window.storeDiagnosis = diagnosis; 