@extends('shared::layouts.admin')

@section('title', 'Radio')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-1 col-span-12">
        <!-- Radio por Defecto -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Radio por Defecto</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center flex-wrap gap-7">
                        <div class="flex items-center gap-2">
                            <input class="radio-primary" type="radio" name="radio_default_active" id="radio1" checked>
                            <label class="text-base font-semibold text-black-400" for="radio1">Primary</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input class="radio-secondary" type="radio" name="radio_default_active" id="radio2">
                            <label class="text-base font-semibold text-black-400" for="radio2">Secondary</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input class="radio-success" type="radio" name="radio_default_active" id="radio3">
                            <label class="text-base font-semibold text-black-400" for="radio3">Success</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input class="radio-warning" type="radio" name="radio_default_active" id="radio4">
                            <label class="text-base font-semibold text-black-400" for="radio4">Warning</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input class="radio-error" type="radio" name="radio_default_active" id="radio5">
                            <label class="text-base font-semibold text-black-400" for="radio5">Error</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input class="radio-info" type="radio" name="radio_default_active" id="radio6">
                            <label class="text-base font-semibold text-black-400" for="radio6">Info</label>
                        </div>
                    </div>
                    
                    <div class="border-t border-accent-100 pt-4">
                        <h3 class="text-sm font-semibold text-black-300 mb-3">Estados Inactivos</h3>
                        <div class="flex items-center flex-wrap gap-7">
                            <div class="flex items-center gap-2">
                                <input class="radio-inactive-primary" type="radio" name="radio_default_inactive" id="radio11">
                                <label class="text-base font-semibold text-black-300" for="radio11">Primary</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input class="radio-inactive-secondary" type="radio" name="radio_default_inactive" id="radio22">
                                <label class="text-base font-semibold text-black-300" for="radio22">Secondary</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input class="radio-inactive-success" type="radio" name="radio_default_inactive" id="radio33">
                                <label class="text-base font-semibold text-black-300" for="radio33">Success</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input class="radio-inactive-warning" type="radio" name="radio_default_inactive" id="radio44">
                                <label class="text-base font-semibold text-black-300" for="radio44">Warning</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input class="radio-inactive-error" type="radio" name="radio_default_inactive" id="radio55">
                                <label class="text-base font-semibold text-black-300" for="radio55">Error</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input class="radio-inactive-info" type="radio" name="radio_default_inactive" id="radio66">
                                <label class="text-base font-semibold text-black-300" for="radio66">Info</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:col-span-1 col-span-12">
        <!-- Radio con Botón -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Radio con Botón</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center flex-wrap gap-4">
                        <div class="bg-primary-50 border border-primary-100 px-4 py-3 rounded-lg hover:bg-primary-100 transition-colors">
                            <span class="flex items-center gap-2">
                                <input class="radio-button-primary" type="radio" name="radio_button" id="radio100" checked>
                                <label class="text-base font-semibold text-primary-300 cursor-pointer" for="radio100">Primary</label>
                            </span>
                        </div>
                        <div class="bg-secondary-50 border border-secondary-100 px-4 py-3 rounded-lg hover:bg-secondary-100 transition-colors">
                            <span class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-secondary-200 bg-accent-100 border-accent-300 focus:ring-secondary-100 focus:ring-2 rounded-full appearance-none checked:bg-secondary-200 checked:border-secondary-200" type="radio" name="radio_button" id="radio200">
                                <label class="peer-checked:text-secondary-300 text-base font-semibold cursor-pointer" for="radio200">Secondary</label>
                            </span>
                        </div>
                        <div class="bg-success-50 border border-success-100 px-4 py-3 rounded-lg hover:bg-success-100 transition-colors">
                            <span class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-success-200 bg-accent-100 border-accent-300 focus:ring-success-100 focus:ring-2 rounded-full appearance-none checked:bg-success-200 checked:border-success-200" type="radio" name="radio_button" id="radio300">
                                <label class="peer-checked:text-success-300 text-base font-semibold cursor-pointer" for="radio300">Success</label>
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center flex-wrap gap-4">
                        <div class="bg-warning-50 border border-warning-100 px-4 py-3 rounded-lg hover:bg-warning-100 transition-colors">
                            <span class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-warning-200 bg-accent-100 border-accent-300 focus:ring-warning-100 focus:ring-2 rounded-full appearance-none checked:bg-warning-200 checked:border-warning-200" type="radio" name="radio_button" id="radio400">
                                <label class="peer-checked:text-warning-300 text-base font-semibold cursor-pointer" for="radio400">Warning</label>
                            </span>
                        </div>
                        <div class="bg-error-50 border border-error-100 px-4 py-3 rounded-lg hover:bg-error-100 transition-colors">
                            <span class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-error-200 bg-accent-100 border-accent-300 focus:ring-error-100 focus:ring-2 rounded-full appearance-none checked:bg-error-200 checked:border-error-200" type="radio" name="radio_button" id="radio500">
                                <label class="peer-checked:text-error-300 text-base font-semibold cursor-pointer" for="radio500">Error</label>
                            </span>
                        </div>
                        <div class="bg-info-50 border border-info-100 px-4 py-3 rounded-lg hover:bg-info-100 transition-colors">
                            <span class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-info-200 bg-accent-100 border-accent-300 focus:ring-info-100 focus:ring-2 rounded-full appearance-none checked:bg-info-200 checked:border-info-200" type="radio" name="radio_button" id="radio600">
                                <label class="peer-checked:text-info-300 text-base font-semibold cursor-pointer" for="radio600">Info</label>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:col-span-1 col-span-12">
        <!-- Radio Horizontal -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Radio Horizontal</h2>
            </div>
            <div class="card-body">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold text-black-300 mb-3">Selecciona una opción:</h3>
                        <div class="flex items-center flex-wrap gap-6">
                            <div class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-primary-200 bg-accent-100 border-accent-300 focus:ring-primary-100 focus:ring-2 rounded-full appearance-none checked:bg-primary-200 checked:border-primary-200" type="radio" name="horizontal_group" id="horizontal1" checked>
                                <label class="peer-checked:text-primary-300 text-base font-medium cursor-pointer" for="horizontal1">Opción 1</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-primary-200 bg-accent-100 border-accent-300 focus:ring-primary-100 focus:ring-2 rounded-full appearance-none checked:bg-primary-200 checked:border-primary-200" type="radio" name="horizontal_group" id="horizontal2">
                                <label class="peer-checked:text-primary-300 text-base font-medium cursor-pointer" for="horizontal2">Opción 2</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-primary-200 bg-accent-100 border-accent-300 focus:ring-primary-100 focus:ring-2 rounded-full appearance-none checked:bg-primary-200 checked:border-primary-200" type="radio" name="horizontal_group" id="horizontal3">
                                <label class="peer-checked:text-primary-300 text-base font-medium cursor-pointer" for="horizontal3">Opción 3</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-primary-200 bg-accent-100 border-accent-300 focus:ring-primary-100 focus:ring-2 rounded-full appearance-none checked:bg-primary-200 checked:border-primary-200" type="radio" name="horizontal_group" id="horizontal4">
                                <label class="peer-checked:text-primary-300 text-base font-medium cursor-pointer" for="horizontal4">Opción 4</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t border-accent-100 pt-4">
                        <h3 class="text-sm font-semibold text-black-300 mb-3">Tamaños de servicio:</h3>
                        <div class="flex items-center flex-wrap gap-6">
                            <div class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-secondary-200 bg-accent-100 border-accent-300 focus:ring-secondary-100 focus:ring-2 rounded-full appearance-none checked:bg-secondary-200 checked:border-secondary-200" type="radio" name="size_group" id="size1">
                                <label class="peer-checked:text-secondary-300 text-base font-medium cursor-pointer" for="size1">Pequeño</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-secondary-200 bg-accent-100 border-accent-300 focus:ring-secondary-100 focus:ring-2 rounded-full appearance-none checked:bg-secondary-200 checked:border-secondary-200" type="radio" name="size_group" id="size2" checked>
                                <label class="peer-checked:text-secondary-300 text-base font-medium cursor-pointer" for="size2">Mediano</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input class="peer w-4 h-4 text-secondary-200 bg-accent-100 border-accent-300 focus:ring-secondary-100 focus:ring-2 rounded-full appearance-none checked:bg-secondary-200 checked:border-secondary-200" type="radio" name="size_group" id="size3">
                                <label class="peer-checked:text-secondary-300 text-base font-medium cursor-pointer" for="size3">Grande</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:col-span-1 col-span-12">
        <!-- Radio Vertical -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Radio Vertical</h2>
            </div>
            <div class="card-body">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold text-black-300 mb-3">Selecciona tu plan:</h3>
                        <div class="flex items-start flex-col gap-4">
                            <div class="flex items-center gap-3 p-3 rounded-lg border border-accent-100 hover:border-primary-200 transition-colors w-full">
                                <input class="peer w-4 h-4 text-primary-200 bg-accent-100 border-accent-300 focus:ring-primary-100 focus:ring-2 rounded-full appearance-none checked:bg-primary-200 checked:border-primary-200" type="radio" name="plan_group" id="plan1" checked>
                                <label class="peer-checked:text-primary-300 cursor-pointer flex-1" for="plan1">
                                    <div class="text-base font-semibold">Plan Básico</div>
                                    <div class="text-sm text-black-300">$9.99/mes - Funciones básicas</div>
                                </label>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-lg border border-accent-100 hover:border-primary-200 transition-colors w-full">
                                <input class="peer w-4 h-4 text-primary-200 bg-accent-100 border-accent-300 focus:ring-primary-100 focus:ring-2 rounded-full appearance-none checked:bg-primary-200 checked:border-primary-200" type="radio" name="plan_group" id="plan2">
                                <label class="peer-checked:text-primary-300 cursor-pointer flex-1" for="plan2">
                                    <div class="text-base font-semibold">Plan Pro</div>
                                    <div class="text-sm text-black-300">$19.99/mes - Funciones avanzadas</div>
                                </label>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-lg border border-accent-100 hover:border-primary-200 transition-colors w-full">
                                <input class="peer w-4 h-4 text-primary-200 bg-accent-100 border-accent-300 focus:ring-primary-100 focus:ring-2 rounded-full appearance-none checked:bg-primary-200 checked:border-primary-200" type="radio" name="plan_group" id="plan3">
                                <label class="peer-checked:text-primary-300 cursor-pointer flex-1" for="plan3">
                                    <div class="text-base font-semibold">Plan Enterprise</div>
                                    <div class="text-sm text-black-300">$49.99/mes - Funciones completas</div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t border-accent-100 pt-4">
                        <h3 class="text-sm font-semibold text-black-300 mb-3">Método de pago:</h3>
                        <div class="flex items-start flex-col gap-3">
                            <div class="flex items-center gap-3">
                                <input class="peer w-4 h-4 text-success-200 bg-accent-100 border-accent-300 focus:ring-success-100 focus:ring-2 rounded-full appearance-none checked:bg-success-200 checked:border-success-200" type="radio" name="payment_group" id="payment1">
                                <label class="peer-checked:text-success-300 text-base font-medium cursor-pointer" for="payment1">
                                    <x-solar-card-outline class="w-5 h-5 inline-block mr-2" />
                                    Tarjeta de Crédito
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <input class="peer w-4 h-4 text-success-200 bg-accent-100 border-accent-300 focus:ring-success-100 focus:ring-2 rounded-full appearance-none checked:bg-success-200 checked:border-success-200" type="radio" name="payment_group" id="payment2" checked>
                                <label class="peer-checked:text-success-300 text-base font-medium cursor-pointer" for="payment2">
                                    <x-solar-wallet-outline class="w-5 h-5 inline-block mr-2" />
                                    PayPal
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <input class="peer w-4 h-4 text-success-200 bg-accent-100 border-accent-300 focus:ring-success-100 focus:ring-2 rounded-full appearance-none checked:bg-success-200 checked:border-success-200" type="radio" name="payment_group" id="payment3">
                                <label class="peer-checked:text-success-300 text-base font-medium cursor-pointer" for="payment3">
                                    <x-solar-card-transfer-outline class="w-5 h-5 inline-block mr-2" />
                                    Transferencia Bancaria
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection 