@extends('shared::layouts.admin')

@section('title', 'Wizard de Formularios')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Wizard Numerado -->
    <div class="card">
        <div class="card-header">
            <h2 class="title-card">Wizard Numerado</h2>
        </div>
        <div class="card-body">
            <p class="text-black-300 text-sm mt-2">Completa tus datos y procede a los siguientes pasos.</p>
        </div>
        <div class="p-6">
            <!-- Form Wizard Start -->
            <div class="form-wizard">
                <form action="#" method="post">
                    <div class="form-wizard-header overflow-x-auto pb-2 mt-4 mb-8">
                        <ul class="list-none flex justify-center space-x-4">
                            <li class="form-wizard-step flex flex-col items-center" data-step="1">
                                <div class="w-10 h-10 rounded-full bg-primary-200 text-white-50 flex items-center justify-center text-base font-medium mb-2">1</div>
                                <span class="text-sm text-black-300 text-center">Personal</span>
                            </li>
                            <li class="form-wizard-step flex flex-col items-center" data-step="2">
                                <div class="w-10 h-10 rounded-full bg-white-200 text-black-300 flex items-center justify-center text-base font-medium mb-2">2</div>
                                <span class="text-sm text-black-300 text-center">Cuenta</span>
                            </li>
                            <li class="form-wizard-step flex flex-col items-center" data-step="3">
                                <div class="w-10 h-10 rounded-full bg-white-200 text-black-300 flex items-center justify-center text-base font-medium mb-2">3</div>
                                <span class="text-sm text-black-300 text-center">Banco</span>
                            </li>
                            <li class="form-wizard-step flex flex-col items-center" data-step="4">
                                <div class="w-10 h-10 rounded-full bg-white-200 text-black-300 flex items-center justify-center text-base font-medium mb-2">4</div>
                                <span class="text-sm text-black-300 text-center">Pago</span>
                            </li>
                            <li class="form-wizard-step flex flex-col items-center" data-step="5">
                                <div class="w-10 h-10 rounded-full bg-white-200 text-black-300 flex items-center justify-center text-base font-medium mb-2">5</div>
                                <span class="text-sm text-black-300 text-center">Completado</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Paso 1: Información Personal -->
                    <div class="wizard-step active" data-step="1">
                        <h3 class="title-card text-black-400 mb-4">Información Personal</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Nombre*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-200 focus:ring-1 focus:ring-primary-200 transition-colors wizard-required" placeholder="Ingresa tu nombre" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Apellido*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="Ingresa tu apellido" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-base text-black-500 font-semibold mb-2">Email*</label>
                                <input type="email" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="Ingresa tu email" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Contraseña*</label>
                                <input type="password" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="*******" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Confirmar Contraseña*</label>
                                <input type="password" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="*******" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-2">
                                <div class="flex justify-end">
                                    <button type="button" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors wizard-next">Siguiente</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 2: Información de Cuenta -->
                    <div class="wizard-step hidden" data-step="2">
                        <h3 class="title-card text-black-400 mb-4">Información de Cuenta</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="col-span-3">
                                <label class="block text-base text-black-500 font-semibold mb-2">Nombre de Usuario*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="Ingresa tu nombre de usuario" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Número de Tarjeta*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="1234 5678 9012 3456" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Expiración (MM/AA)*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="12/25" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">CVV*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="123" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-3">
                                <div class="flex justify-between">
                                    <button type="button" class="bg-white-200 text-black-400 px-6 py-3 rounded-lg text-base font-medium hover:bg-white-300 transition-colors wizard-prev">Anterior</button>
                                    <button type="button" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors wizard-next">Siguiente</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 3: Información Bancaria -->
                    <div class="wizard-step hidden" data-step="3">
                        <h3 class="title-card text-black-400 mb-4">Información Bancaria</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Nombre del Banco*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="Ingresa nombre del banco" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Nombre de Sucursal*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="Ingresa nombre de sucursal" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Nombre de Cuenta*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="Ingresa nombre de cuenta" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Número de Cuenta*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="Ingresa número de cuenta" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-2">
                                <div class="flex justify-between">
                                    <button type="button" class="bg-white-200 text-black-400 px-6 py-3 rounded-lg text-base font-medium hover:bg-white-300 transition-colors wizard-prev">Anterior</button>
                                    <button type="button" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors wizard-next">Siguiente</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 4: Información de Pago -->
                    <div class="wizard-step hidden" data-step="4">
                        <h3 class="title-card text-black-400 mb-4">Información de Pago</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-base text-black-500 font-semibold mb-2">Nombre del Titular*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="Ingresa nombre del titular" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Número de Tarjeta*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="1234 5678 9012 3456" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-base text-black-500 font-semibold mb-2">Número CVC*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" placeholder="123" required>
                                <div class="wizard-error hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-base text-black-500 font-semibold mb-2">Fecha de Expiración*</label>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <select class="w-full pl-4 pr-8 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" required>
                                            <option value="">Día</option>
                                            <option value="01">01</option>
                                            <option value="02">02</option>
                                            <option value="03">03</option>
                                            <option value="04">04</option>
                                            <option value="05">05</option>
                                        </select>
                                    </div>
                                    <div>
                                        <select class="w-full pl-4 pr-8 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" required>
                                            <option value="">Mes</option>
                                            <option value="01">Enero</option>
                                            <option value="02">Febrero</option>
                                            <option value="03">Marzo</option>
                                            <option value="04">Abril</option>
                                            <option value="05">Mayo</option>
                                        </select>
                                    </div>
                                    <div>
                                        <select class="w-full pl-4 pr-8 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required" required>
                                            <option value="">Año</option>
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2027">2027</option>
                                            <option value="2028">2028</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-2">
                                <div class="flex justify-between">
                                    <button type="button" class="bg-white-200 text-black-400 px-6 py-3 rounded-lg text-base font-medium hover:bg-white-300 transition-colors wizard-prev">Anterior</button>
                                    <button type="button" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors wizard-next">Siguiente</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 5: Completado -->
                    <div class="wizard-step hidden" data-step="5">
                        <div class="text-center">
                            <div class="w-24 h-24 bg-success-200 rounded-full flex items-center justify-center mx-auto mb-6">
                                <x-solar-check-circle-bold class="w-12 h-12 text-white-50" />
                            </div>
                            <h3 class="title-card text-black-400 mb-2">¡Felicitaciones!</h3>
                            <p class="text-black-300 text-base mb-6">Has completado exitosamente el proceso.</p>
                            <div class="flex justify-between">
                                <button type="button" class="bg-white-200 text-black-400 px-6 py-3 rounded-lg text-base font-medium hover:bg-white-300 transition-colors wizard-prev">Anterior</button>
                                <button type="button" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors wizard-submit">Publicar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Form Wizard End -->
        </div>
    </div>

    <!-- Wizard con Etiquetas -->
    <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
            <h2 class="title-card text-black-500 mb-0">Wizard con Etiquetas</h2>
            <p class="text-black-300 text-base mt-2">Completa tus datos y procede a los siguientes pasos.</p>
        </div>
        <div class="p-6">
            <!-- Form Wizard Start -->
            <div class="form-wizard-labeled">
                <form action="#" method="post">
                    <div class="form-wizard-header overflow-x-auto pb-2 mt-4 mb-8">
                        <ul class="list-none flex justify-center space-x-2">
                            <li class="form-wizard-step-labeled active flex flex-col items-center" data-step="1">
                                <div class="w-10 h-10 rounded-full bg-primary-200 text-white-50 flex items-center justify-center text-base font-medium mb-2">1</div>
                                <span class="text-sm text-black-500 font-semibold text-center">Crear Cuenta</span>
                            </li>
                            <li class="form-wizard-step-labeled flex flex-col items-center" data-step="2">
                                <div class="w-10 h-10 rounded-full bg-white-200 text-black-300 flex items-center justify-center text-base font-medium mb-2">2</div>
                                <span class="text-sm text-black-300 text-center">Importar Datos</span>
                            </li>
                            <li class="form-wizard-step-labeled flex flex-col items-center" data-step="3">
                                <div class="w-10 h-10 rounded-full bg-white-200 text-black-300 flex items-center justify-center text-base font-medium mb-2">3</div>
                                <span class="text-sm text-black-300 text-center">Configurar Privacidad</span>
                            </li>
                            <li class="form-wizard-step-labeled flex flex-col items-center" data-step="4">
                                <div class="w-10 h-10 rounded-full bg-white-200 text-black-300 flex items-center justify-center text-base font-medium mb-2">4</div>
                                <span class="text-sm text-black-300 text-center">Agregar Ubicación</span>
                            </li>
                            <li class="form-wizard-step-labeled flex flex-col items-center" data-step="5">
                                <div class="w-10 h-10 rounded-full bg-white-200 text-black-300 flex items-center justify-center text-base font-medium mb-2">5</div>
                                <span class="text-sm text-black-300 text-center">Completado</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Los pasos son similares al wizard anterior pero con layout diferente -->
                    <div class="wizard-step-labeled active" data-step="1">
                        <h3 class="title-card text-black-400 mb-4">Información Personal</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-base text-black-500 font-semibold mb-2">Nombre Completo*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required-labeled" placeholder="Ingresa tu nombre completo" required>
                                <div class="wizard-error-labeled hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div>
                                <label class="block text-base text-black-500 font-semibold mb-2">Email*</label>
                                <input type="email" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required-labeled" placeholder="Ingresa tu email" required>
                                <div class="wizard-error-labeled hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div>
                                <label class="block text-base text-black-500 font-semibold mb-2">Contraseña*</label>
                                <input type="password" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required-labeled" placeholder="*******" required>
                                <div class="wizard-error-labeled hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="flex justify-end mt-4">
                                <button type="button" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors wizard-next-labeled">Siguiente</button>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 2: Importar Datos -->
                    <div class="wizard-step-labeled hidden" data-step="2">
                        <h3 class="title-card text-black-400 mb-4">Importar Datos</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-base text-black-500 font-semibold mb-2">Archivo de Datos*</label>
                                <input class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-200 file:text-white-50 file:body-base hover:file:bg-primary-300 transition-colors wizard-required-labeled" type="file" required>
                                <div class="wizard-error-labeled hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="flex justify-between mt-4">
                                <button type="button" class="bg-white-200 text-black-400 px-6 py-3 rounded-lg text-base font-medium hover:bg-white-300 transition-colors wizard-prev-labeled">Anterior</button>
                                <button type="button" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors wizard-next-labeled">Siguiente</button>
                            </div>
                        </div>
                    </div>

                    <!-- Resto de pasos simplificados -->
                    <div class="wizard-step-labeled hidden" data-step="3">
                        <h3 class="title-card text-black-400 mb-4">Configurar Privacidad</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" class="mr-3 w-5 h-5 text-primary-200 border-white-200 rounded focus:ring-primary-200 focus:ring-2">
                                <label class="text-base text-black-400">Permitir notificaciones por email</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" class="mr-3 w-5 h-5 text-primary-200 border-white-200 rounded focus:ring-primary-200 focus:ring-2">
                                <label class="text-base text-black-400">Hacer perfil público</label>
                            </div>
                            <div class="flex justify-between mt-6">
                                <button type="button" class="bg-white-200 text-black-400 px-6 py-3 rounded-lg text-base font-medium hover:bg-white-300 transition-colors wizard-prev-labeled">Anterior</button>
                                <button type="button" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors wizard-next-labeled">Siguiente</button>
                            </div>
                        </div>
                    </div>

                    <div class="wizard-step-labeled hidden" data-step="4">
                        <h3 class="title-card text-black-400 mb-4">Agregar Ubicación</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-base text-black-500 font-semibold mb-2">País*</label>
                                <select class="w-full pl-4 pr-8 py-3 bg-white-50 border border-white-200 rounded-lg body-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required-labeled" required>
                                    <option value="">Selecciona un país</option>
                                    <option value="es">España</option>
                                    <option value="us">Estados Unidos</option>
                                    <option value="fr">Francia</option>
                                </select>
                                <div class="wizard-error-labeled hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div>
                                <label class="block text-base text-black-500 font-semibold mb-2">Ciudad*</label>
                                <input type="text" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg text-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors wizard-required-labeled" placeholder="Ingresa tu ciudad" required>
                                <div class="wizard-error-labeled hidden text-error-200 text-sm mt-2">Este campo es requerido</div>
                            </div>
                            <div class="flex justify-between mt-4">
                                <button type="button" class="bg-white-200 text-black-400 px-6 py-3 rounded-lg text-base font-medium hover:bg-white-300 transition-colors wizard-prev-labeled">Anterior</button>
                                <button type="button" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors wizard-next-labeled">Siguiente</button>
                            </div>
                        </div>
                    </div>

                    <div class="wizard-step-labeled hidden" data-step="5">
                        <div class="text-center">
                            <div class="w-24 h-24 bg-success-200 rounded-full flex items-center justify-center mx-auto mb-6">
                                <x-solar-check-circle-bold class="w-12 h-12 text-white-50" />
                            </div>
                            <h3 class="title-card text-black-400 mb-2">¡Felicitaciones!</h3>
                            <p class="text-black-300 text-base mb-6">Has completado exitosamente el proceso.</p>
                            <div class="flex justify-between">
                                <button type="button" class="bg-white-200 text-black-400 px-6 py-3 rounded-lg text-base font-medium hover:bg-white-300 transition-colors wizard-prev-labeled">Anterior</button>
                                <button type="button" class="bg-primary-200 text-white-50 px-6 py-3 rounded-lg text-base font-medium hover:bg-primary-300 transition-colors wizard-submit-labeled">Publicar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Form Wizard End -->
        </div>
    </div>
</div>
@endsection 