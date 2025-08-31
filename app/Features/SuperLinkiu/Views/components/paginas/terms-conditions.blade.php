@extends('shared::layouts.admin')

@section('title', 'Términos y Condiciones')

@section('content')
<div class="card">
    <!-- Header -->
    <div class="card-header">
        <h1 class="title-card">Términos y Condiciones</h1>
    </div>

    <!-- Editor Toolbar -->
    <div class="card-body">
        <div class="flex flex-wrap items-center gap-2">
            <!-- Formato de texto -->
            <div class="flex items-center bg-accent-50 rounded-lg p-1 gap-2">
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Negrita">
                    <x-solar-text-bold-outline class="w-4 h-4 text-black-400" />
                </button>
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Cursiva">
                    <x-solar-text-italic-outline class="w-4 h-4 text-black-400" />
                </button>
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Subrayado">
                    <x-solar-text-underline-outline class="w-4 h-4 text-black-400" />
                </button>
            </div>

            <!-- Encabezados -->
            <div class="flex items-center bg-accent-50 rounded-lg p-1 gap-2">
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Encabezado 1">
                    <span class="text-sm font-bold text-black-400">H1</span>
                </button>
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Encabezado 2">
                    <span class="text-sm font-bold text-black-400">H2</span>
                </button>
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Encabezado 3">
                    <span class="text-sm font-bold text-black-400">H3</span>
                </button>
            </div>

            <!-- Listas -->
            <div class="flex items-center bg-accent-50 rounded-lg p-1 gap-2">
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Lista numerada">
                    <x-solar-list-check-outline class="w-4 h-4 text-black-400" />
                </button>
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Lista con viñetas">
                    <x-solar-list-outline class="w-4 h-4 text-black-400" />
                </button>
            </div>

            <!-- Alineación -->
            <div class="flex items-center bg-accent-50 rounded-lg p-1">
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Alinear izquierda">
                    <x-solar-align-left-outline class="w-4 h-4 text-black-400" />
                </button>
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Alinear centro">
                    <x-solar-align-horizontal-center-outline class="w-4 h-4 text-black-400" />
                </button>
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Alinear derecha">
                    <x-solar-align-right-outline class="w-4 h-4 text-black-400" />
                </button>
            </div>

            <!-- Insertar -->
            <div class="flex items-center bg-accent-50 rounded-lg p-1">
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Insertar enlace">
                    <x-solar-link-outline class="w-4 h-4 text-black-400" />
                </button>
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Insertar imagen">
                    <x-solar-gallery-outline class="w-4 h-4 text-black-400" />
                </button>
            </div>

            <!-- Acciones -->
            <div class="flex items-center bg-accent-50 rounded-lg p-1">
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Deshacer">
                    <x-solar-undo-left-outline class="w-4 h-4 text-black-400" />
                </button>
                <button type="button" class="p-2 bg-accent-300 rounded hover:bg-accent-200 transition-colors" title="Rehacer">
                    <x-solar-undo-right-outline class="w-4 h-4 text-black-400" />
                </button>
            </div>

            <!-- Estado -->
            <div class="ml-auto flex items-center space-x-2">
                <span class="text-sm text-black-400">Última edición:</span>
                <span class="text-sm font-medium text-black-400">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- Editor Content -->
    <div class="p-6">
        <div class="min-h-[600px] bg-accent-50 rounded-lg p-6 border border-accent-100 focus-within:border-black-400 transition-colors">
            <!-- Contenido editable -->
            <div class="prose prose-lg max-w-none">
                <h1 class="text-lg font-bold text-black-400 mb-6">Términos y Condiciones de Uso</h1>
                
                <p class="text-black-400 leading-relaxed mb-6">
                    Esta política explica cómo nuestro sitio web y aplicaciones relacionadas (el "Sitio", "nosotros" o "nuestro") 
                    recopila, utiliza, comparte y protege la información personal que recopilamos a través de este sitio o diferentes canales. 
                    Hemos establecido el sitio para vincular a los usuarios que necesitan servicios digitales.
                </p>

                <h2 class="text-lg font-bold text-black-400 mb-4 mt-8">1. Uso de la Plataforma</h2>
                <p class="text-black-400 leading-relaxed mb-6">
                    Esta política explica cómo nuestro sitio web y aplicaciones relacionadas recopilan, utilizan, comparten y protegen 
                    la información personal que recopilamos a través de este sitio o diferentes canales. Hemos establecido el sitio 
                    para vincular a los usuarios que necesitan servicios digitales o productos que deben ser entregados por nuestros 
                    proveedores afiliados a la ubicación deseada.
                </p>

                <h2 class="text-lg font-bold text-black-400 mb-4 mt-8">2. Propiedad Intelectual</h2>
                <p class="text-black-400 leading-relaxed mb-6">
                    Esta política explica cómo nuestro sitio web y aplicaciones relacionadas recopilan, utilizan, comparten y protegen 
                    la información personal que recopilamos a través de este sitio o diferentes canales. Hemos establecido el sitio 
                    para vincular a los usuarios que necesitan servicios digitales o productos que deben ser entregados por nuestros 
                    proveedores desde los establecimientos afiliados a la ubicación deseada. Esta política también se aplica a cualquier 
                    aplicación móvil que desarrollemos para usar con nuestros servicios.
                </p>

                <h2 class="text-lg font-bold text-black-400 mb-4 mt-8">3. Privacidad y Datos</h2>
                <p class="text-black-400 leading-relaxed mb-6">
                    Esta política explica cómo nuestro sitio web y aplicaciones relacionadas recopilan, utilizan, comparten y protegen 
                    la información personal que recopilamos a través de este sitio o diferentes canales. Hemos establecido el sitio 
                    para vincular a los usuarios que necesitan servicios digitales o productos que deben ser entregados por nuestros 
                    proveedores desde los establecimientos afiliados a la ubicación deseada. Esta política también se aplica a cualquier 
                    aplicación móvil que desarrollemos para usar con nuestros servicios en el Sitio.
                </p>

                <h2 class="text-lg font-bold text-black-400 mb-4 mt-8">4. Responsabilidades del Usuario</h2>
                <p class="text-black-400 leading-relaxed mb-6">
                    Los usuarios son responsables de mantener la confidencialidad de sus credenciales de acceso y de todas las 
                    actividades que ocurran bajo su cuenta. Está prohibido usar el servicio para actividades ilegales, 
                    fraudulentas o que violen los derechos de terceros.
                </p>

                <h2 class="text-lg font-bold text-black-400 mb-4 mt-8">5. Limitación de Responsabilidad</h2>
                <p class="text-black-400 leading-relaxed mb-6">
                    En ningún caso seremos responsables por daños directos, indirectos, incidentales, especiales, 
                    consecuentes o punitivos, incluidos, entre otros, pérdida de beneficios, datos, uso, buena voluntad 
                    u otras pérdidas intangibles resultantes del uso o la imposibilidad de uso del servicio.
                </p>

                <h2 class="text-lg font-bold text-black-400 mb-4 mt-8">6. Modificaciones</h2>
                <p class="text-black-400 leading-relaxed mb-6">
                    Nos reservamos el derecho de modificar estos términos en cualquier momento. Las modificaciones 
                    entrarán en vigor inmediatamente después de su publicación en el sitio web. Es responsabilidad 
                    del usuario revisar periódicamente estos términos.
                </p>

                <div class="bg-info-50 rounded-lg p-6 mt-8">
                    <div class="flex items-center space-x-2 mb-3">
                        <x-solar-info-circle-outline class="w-5 h-5 text-info-200" />
                        <h3 class="text-lg font-semibold text-info-200">Información Importante</h3>
                    </div>
                    <p class="text-info-200">
                        Al usar nuestros servicios, usted acepta estos términos y condiciones. Si no está de acuerdo 
                        con alguna parte de estos términos, no debe usar nuestros servicios.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer con botones -->
    <div class="border-t border-accent-100 bg-accent-50 p-6">
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <!-- Botón Cancelar -->
            <button type="button" 
                    class="bg-error-200 text-error-50 border border-error-200 px-8 py-3 rounded-lg font-semibold hover:bg-error-300 transition-colors duration-200 flex items-center space-x-2">
                <x-solar-close-circle-outline class="w-5 h-5" />
                <span>Cancelar</span>
            </button>

            <!-- Botón Guardar -->
            <button type="button" 
                    class="bg-primary-200 text-accent-50 px-8 py-3 rounded-lg font-semibold hover:bg-primary-300 transition-colors duration-200 flex items-center space-x-2">
                <x-solar-check-circle-outline class="w-5 h-5" />
                <span>Guardar Cambios</span>
            </button>
        </div>

        <!-- Información adicional -->
        <div class="mt-6 pt-4 border-t border-accent-100">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2 text-sm text-black-400">
                    <x-solar-clock-circle-outline class="w-4 h-4" />
                    <span>Guardado automático cada 30 segundos</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                    <button type="button" class="text-primary-200 hover:text-primary-300 transition-colors flex items-center space-x-1">
                        <x-solar-eye-outline class="w-4 h-4" />
                        <span>Vista previa</span>
                    </button>
                    <button type="button" class="text-primary-200 hover:text-primary-300 transition-colors flex items-center space-x-1">
                        <x-solar-download-outline class="w-4 h-4" />
                        <span>Descargar PDF</span>
                    </button>
                    <button type="button" class="text-primary-200 hover:text-primary-300 transition-colors flex items-center space-x-1">
                        <x-solar-history-outline class="w-4 h-4" />
                        <span>Historial</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 