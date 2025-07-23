@extends('shared::layouts.admin')

@section('title', 'FAQ')

@section('content')
<div class="card">
    <!-- Header con gradiente -->
    <div class="bg-gradient-to-r from-primary-200 to-primary-100 py-10 px-6 lg:px-12 rounded-t-lg">
        <div class="grid grid-cols-1 xl:grid-cols-2 items-center gap-8">
            <div class="col-span-1">
                <h1 class="text-2xl font-black text-white-50 mb-4">Preguntas Frecuentes</h1>
                <p class="text-base text-white-100">
                    Encuentra respuestas a las preguntas más comunes sobre nuestros servicios y productos. 
                    Si no encuentras lo que buscas, no dudes en contactarnos.
                </p>
            </div>
            <div class="col-span-1 hidden xl:flex justify-end">
                <div class="bg-white-100 rounded-full p-8">
                    <x-solar-question-circle-outline class="w-24 h-24 text-primary-200" />
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="p-6 lg:p-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8" x-data="{ activeTab: 'about', openAccordion: 1 }">
            <!-- Sidebar con tabs -->
            <div class="col-span-12 lg:col-span-4">
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden shadow-sm">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h2 class="text-xl font-black text-black-300">Categorías</h2>
                    </div>
                    <div class="p-2">
                        <nav class="space-y-1">
                            <button 
                                @click="activeTab = 'about'; openAccordion = 1"
                                x-bind:class="activeTab === 'about' ? 'bg-primary-200 text-primary-50 border-primary-200' : 'bg-white-50 text-black-300 border-white-100 hover:bg-white-100'"
                                class="w-full text-left px-4 py-3 rounded-lg border transition-all duration-200 flex items-center space-x-3">
                                <x-solar-info-circle-outline class="w-5 h-5" />
                                <span class="font-semibold">Acerca de Nosotros</span>
                            </button>
                            
                            <button 
                                @click="activeTab = 'design'; openAccordion = 1"
                                x-bind:class="activeTab === 'design' ? 'bg-primary-200 text-primary-50 border-primary-200' : 'bg-white-50 text-black-300 border-white-100 hover:bg-white-100'"
                                class="w-full text-left px-4 py-3 rounded-lg border transition-all duration-200 flex items-center space-x-3">
                                <x-solar-palette-outline class="w-5 h-5" />
                                <span class="font-semibold">Diseño UI/UX</span>
                            </button>
                            
                            <button 
                                @click="activeTab = 'development'; openAccordion = 1"
                                x-bind:class="activeTab === 'development' ? 'bg-primary-200 text-primary-50 border-primary-200' : 'bg-white-50 text-black-300 border-white-100 hover:bg-white-100'"
                                class="w-full text-left px-4 py-3 rounded-lg border transition-all duration-200 flex items-center space-x-3">
                                <x-solar-code-outline class="w-5 h-5" />
                                <span class="font-semibold">Desarrollo</span>
                            </button>
                            
                            <button 
                                @click="activeTab = 'platform'; openAccordion = 1"
                                x-bind:class="activeTab === 'platform' ? 'bg-primary-200 text-primary-50 border-primary-200' : 'bg-white-50 text-black-300 border-white-100 hover:bg-white-100'"
                                class="w-full text-left px-4 py-3 rounded-lg border transition-all duration-200 flex items-center space-x-3">
                                <x-solar-monitor-outline class="w-5 h-5" />
                                <span class="font-semibold">Cómo usar la plataforma</span>
                            </button>
                            
                            <button 
                                @click="activeTab = 'agency'; openAccordion = 1"
                                x-bind:class="activeTab === 'agency' ? 'bg-primary-200 text-primary-50 border-primary-200' : 'bg-white-50 text-black-300 border-white-100 hover:bg-white-100'"
                                class="w-full text-left px-4 py-3 rounded-lg border transition-all duration-200 flex items-center space-x-3">
                                <x-solar-buildings-outline class="w-5 h-5" />
                                <span class="font-semibold">Servicios de Agencia</span>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Contenido con acordeones -->
            <div class="col-span-12 lg:col-span-8">
                <!-- Tab: Acerca de Nosotros -->
                <div x-show="activeTab === 'about'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="space-y-4">
                        <!-- Pregunta 1 -->
                        <div class="card">
                            <button 
                                @click="openAccordion = openAccordion === 1 ? 0 : 1"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Hay una prueba gratuita disponible?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 1 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Sí, puedes probarnos gratis durante 30 días. Si lo deseas, te proporcionaremos una llamada de incorporación personalizada gratuita de 30 minutos para que puedas empezar lo antes posible.
                                </p>
                            </div>
                        </div>

                        <!-- Pregunta 2 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 2 ? 0 : 2"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Puedo cambiar mi plan más adelante?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 2 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Por supuesto. Puedes actualizar o degradar tu plan en cualquier momento desde la configuración de tu cuenta. Los cambios se aplicarán en tu próximo ciclo de facturación.
                                </p>
                            </div>
                        </div>

                        <!-- Pregunta 3 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 3 ? 0 : 3"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Cuál es su política de cancelación?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 3 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Puedes cancelar tu suscripción en cualquier momento. No hay penalizaciones por cancelación temprana. Tu acceso continuará hasta el final del período de facturación actual.
                                </p>
                            </div>
                        </div>

                        <!-- Pregunta 4 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 4 ? 0 : 4"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Cómo funciona la facturación?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 4 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    La facturación es automática y se realiza mensual o anualmente según tu plan. Aceptamos todas las tarjetas de crédito principales y PayPal. Recibirás un recibo por email después de cada pago.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Diseño UI/UX -->
                <div x-show="activeTab === 'design'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="space-y-4">
                        <!-- Pregunta 1 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 1 ? 0 : 1"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Ofrecen servicios de diseño personalizado?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 1 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Sí, nuestro equipo de diseñadores expertos puede crear interfaces personalizadas que se adapten perfectamente a tu marca y necesidades específicas.
                                </p>
                            </div>
                        </div>

                        <!-- Pregunta 2 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 2 ? 0 : 2"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Incluyen prototipado en sus servicios?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 2 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Absolutamente. Creamos prototipos interactivos que te permiten experimentar el flujo de usuario antes del desarrollo final.
                                </p>
                            </div>
                        </div>

                        <!-- Pregunta 3 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 3 ? 0 : 3"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Cuánto tiempo toma un proyecto de diseño?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 3 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Depende del alcance del proyecto. Los proyectos pequeños toman 1-2 semanas, mientras que los proyectos más grandes pueden tomar 4-8 semanas.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Desarrollo -->
                <div x-show="activeTab === 'development'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="space-y-4">
                        <!-- Pregunta 1 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 1 ? 0 : 1"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Qué tecnologías utilizan?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 1 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Utilizamos tecnologías modernas como Laravel, React, Vue.js, Node.js, y bases de datos como MySQL y PostgreSQL para crear soluciones robustas y escalables.
                                </p>
                            </div>
                        </div>

                        <!-- Pregunta 2 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 2 ? 0 : 2"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Ofrecen soporte post-desarrollo?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 2 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Sí, ofrecemos soporte continuo, mantenimiento, actualizaciones de seguridad y mejoras de funcionalidad para todos nuestros proyectos.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Plataforma -->
                <div x-show="activeTab === 'platform'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="space-y-4">
                        <!-- Pregunta 1 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 1 ? 0 : 1"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Cómo empiezo a usar la plataforma?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 1 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Simplemente regístrate, elige tu plan y sigue nuestro tutorial interactivo. También ofrecemos una sesión de incorporación gratuita para nuevos usuarios.
                                </p>
                            </div>
                        </div>

                        <!-- Pregunta 2 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 2 ? 0 : 2"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Hay tutoriales disponibles?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 2 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Sí, tenemos una biblioteca completa de tutoriales en video, documentación detallada y ejemplos prácticos para ayudarte a dominar la plataforma.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Agencia -->
                <div x-show="activeTab === 'agency'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="space-y-4">
                        <!-- Pregunta 1 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 1 ? 0 : 1"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Ofrecen servicios de agencia completos?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 1 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Sí, ofrecemos servicios completos de agencia incluyendo estrategia digital, diseño, desarrollo, marketing y soporte continuo.
                                </p>
                            </div>
                        </div>

                        <!-- Pregunta 2 -->
                        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                            <button 
                                @click="openAccordion = openAccordion === 2 ? 0 : 2"
                                class="w-full text-left px-6 py-4 flex items-center justify-between border-b border-white-100 hover:bg-white-100 transition-colors">
                                <span class="text-base font-semibold text-black-300">¿Cómo funciona la colaboración con su agencia?</span>
                                <x-solar-alt-arrow-down-outline 
                                    class="w-5 h-5 text-primary-200 transition-transform duration-200"
                                    x-bind:class="openAccordion === 2 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordion === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" class="px-6 py-4">
                                <p class="text-black-300 leading-relaxed">
                                    Trabajamos en estrecha colaboración contigo a través de reuniones regulares, reportes de progreso y un portal de cliente donde puedes hacer seguimiento del proyecto en tiempo real.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 