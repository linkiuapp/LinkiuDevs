@extends('shared::layouts.admin')
@section('title', 'Alerts')
@section('content')
<div class="container-fluid">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Default Alerts -->
        <div class="col-span-1 lg:col-span-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="title-card">Default Alerts</h2>
                </div>
                <div class="p-6 flex flex-col gap-4">
                    <div class="alert-primary" role="alert">
                        Esta es una alerta Primary
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-secondary" role="alert">
                        Esta es una alerta Secondary
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-warning" role="alert">
                        Esta es una alerta Warning
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-info" role="alert">
                        Esta es una alerta Info
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-error" role="alert">
                        Esta es una alerta Error
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-success" role="alert">
                        Esta es una alerta Success
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outline Alerts -->
        <div class="col-span-1 lg:col-span-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="title-card">Outline Alerts</h2>
                </div>
                <div class="p-6 flex flex-col gap-4">

                    <div class="alert-outline-primary" role="alert">
                        Esta es una alerta Primary
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-outline-secondary" role="alert">
                        Esta es una alerta Secondary
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-outline-warning" role="alert">
                        Esta es una alerta Warning
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-outline-info" role="alert">
                        Esta es una alerta Info
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-outline-error" role="alert">
                        Esta es una alerta Error
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-outline-success" role="alert">
                        Esta es una alerta Success
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Solid Alerts -->
        <div class="col-span-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="title-card">Solid Alerts</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <div class="col-span-1 lg:col-span-6">
                            <div class="flex flex-col gap-4">
                                <div class="alert-solid-primary" role="alert">
                                    Esta es una alerta Primary
                                    <button class="alert-close">
                                        <x-solar-close-circle-outline class="w-5 h-5" />
                                    </button>
                                </div>
                                <div class="alert-solid-success" role="alert">
                                    Esta es una alerta Success
                                    <button class="alert-close">
                                        <x-solar-close-circle-outline class="w-5 h-5" />
                                    </button>
                                </div>
                                <div class="alert-solid-info" role="alert">
                                    Esta es una alerta Info
                                    <button class="alert-close">
                                        <x-solar-close-circle-outline class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-1 lg:col-span-6">
                            <div class="flex flex-col gap-4">
                                <div class="alert-solid-secondary" role="alert">
                                    Esta es una alerta Secondary
                                    <button class="alert-close">
                                        <x-solar-close-circle-outline class="w-5 h-5" />
                                    </button>
                                </div>
                                <div class="alert-solid-warning" role="alert">
                                    Esta es una alerta Warning
                                    <button class="alert-close">
                                        <x-solar-close-circle-outline class="w-5 h-5" />
                                    </button>
                                </div>
                                <div class="alert-solid-error" role="alert">
                                    Esta es una alerta Error
                                    <button class="alert-close">
                                        <x-solar-close-circle-outline class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts with Icons -->
        <div class="col-span-1 lg:col-span-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="title-card">Alerts with Icons</h2>
                </div>
                <div class="p-6 flex flex-col gap-4">
                    <div class="alert-with-icon-primary" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-shield-warning-outline class="w-5 h-5" />
                            Esta es una alerta Primary
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-with-icon-secondary" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-shield-warning-outline class="w-5 h-5" />
                            Esta es una alerta Secondary
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-with-icon-success" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-check-circle-outline class="w-5 h-5" />
                            Esta es una alerta Success
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-with-icon-warning" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-danger-triangle-outline class="w-5 h-5" />
                            Esta es una alerta Warning
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-with-icon-info" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-info-circle-outline class="w-5 h-5" />
                            Esta es una alerta Info
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-with-icon-error" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-trash-bin-trash-outline class="w-5 h-5" />
                            Esta es una alerta Error
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Left Border Alerts -->
        <div class="col-span-1 lg:col-span-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="title-card">Left Border Alerts</h2>
                </div>
                <div class="p-6 flex flex-col gap-4">
                    <div class="alert-left-border-primary" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-shield-warning-outline class="w-5 h-5" />
                            Esta es una alerta Primary
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-left-border-secondary" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-shield-warning-outline class="w-5 h-5" />
                            Esta es una alerta Secondary
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-left-border-success" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-check-circle-outline class="w-5 h-5" />
                            Esta es una alerta Success
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-left-border-warning" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-danger-triangle-outline class="w-5 h-5" />
                            Esta es una alerta Warning
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-left-border-info" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-info-circle-outline class="w-5 h-5" />
                            Esta es una alerta Info
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="alert-left-border-error" role="alert">
                        <div class="flex items-center gap-2">
                            <x-solar-trash-bin-trash-outline class="w-5 h-5" />
                            Esta es una alerta Error
                        </div>
                        <button class="alert-close">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts with Descriptions -->
        <div class="col-span-1 lg:col-span-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="title-card">Alerts with Descriptions</h2>
                </div>
                <div class="p-6 flex flex-col gap-4">
                    <div class="alert-with-description-primary" role="alert">
                        <div class="flex items-center justify-between">
                            Esta es una alerta Primary
                            <button class="alert-close">
                                <x-solar-close-circle-outline class="w-5 h-5" />
                            </button>
                        </div>
                        <p class="font-normal text-primary-300 body-small mt-2">
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy
                        </p>
                    </div>
                    <div class="alert-with-description-success" role="alert">
                        <div class="flex items-center justify-between">
                            Esta es una alerta Success
                            <button class="alert-close">
                                <x-solar-close-circle-outline class="w-5 h-5" />
                            </button>
                        </div>
                        <p class="font-normal text-success-300 body-small mt-2">
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy
                        </p>
                    </div>
                    <div class="alert-with-description-warning" role="alert">
                        <div class="flex items-center justify-between">
                            Esta es una alerta Warning
                            <button class="alert-close">
                                <x-solar-close-circle-outline class="w-5 h-5" />
                            </button>
                        </div>
                        <p class="font-normal text-warning-300 body-small mt-2">
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy
                        </p>
                    </div>
                    <div class="alert-with-description-info" role="alert">
                        <div class="flex items-center justify-between">
                            Esta es una alerta Info
                            <button class="alert-close">
                                <x-solar-close-circle-outline class="w-5 h-5" />
                            </button>
                        </div>
                        <p class="font-normal text-info-300 body-small mt-2">
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy
                        </p>
                    </div>
                    <div class="alert-with-description-error" role="alert">
                        <div class="flex items-center justify-between">
                            Esta es una alerta Error
                            <button class="alert-close">
                                <x-solar-close-circle-outline class="w-5 h-5" />
                            </button>
                        </div>
                        <p class="font-normal text-error-300 body-small mt-2">
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts with Icons and Descriptions -->
        <div class="col-span-1 lg:col-span-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="title-card">Alerts with Icons & Descriptions</h2>
                </div>
                <div class="p-6 flex flex-col gap-4">
                    <div class="alert-with-icon-and-description-primary" role="alert">
                        <div class="flex items-start justify-between body-base">
                            <div class="flex items-start gap-2">
                                <x-solar-shield-warning-outline class="w-8 h-8 mt-4 mr-2 shrink-0" />
                                <div>
                                    Esta es una alerta Primary
                                    <p class="font-normal text-primary-300 body-small mt-2">
                                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy
                                    </p>
                                </div>
                            </div>
                            <button class="alert-close">
                                <x-solar-close-circle-outline class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    <div class="alert-with-icon-and-description-success" role="alert">
                        <div class="flex items-start justify-between body-base">
                            <div class="flex items-start gap-2">
                                <x-solar-check-circle-outline class="w-8 h-8 mt-4 mr-2 shrink-0" />
                                <div>
                                    Esta es una alerta Success
                                    <p class="font-normal text-success-300 body-small mt-2">
                                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy
                                    </p>
                                </div>
                            </div>
                            <button class="alert-close">
                                <x-solar-close-circle-outline class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    <div class="alert-with-icon-and-description-warning" role="alert">
                        <div class="flex items-start justify-between body-base">
                            <div class="flex items-start gap-2">
                                <x-solar-clock-circle-outline class="w-8 h-8 mt-4 mr-2 shrink-0" />
                                <div>
                                    Esta es una alerta Warning
                                    <p class="font-normal text-warning-300 body-small mt-2">
                                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy
                                    </p>
                                </div>
                            </div>
                            <button class="alert-close">
                                <x-solar-close-circle-outline class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    <div class="alert-with-icon-and-description-info" role="alert">
                        <div class="flex items-start justify-between body-base">
                            <div class="flex items-start gap-2">
                                <x-solar-check-square-outline class="w-8 h-8 mt-4 mr-2 shrink-0" />
                                <div>
                                    Esta es una alerta Info
                                    <p class="font-normal text-info-300 body-small mt-2">
                                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy
                                    </p>
                                </div>
                            </div>
                            <button class="alert-close">
                                <x-solar-close-circle-outline class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    <div class="alert-with-icon-and-description-error" role="alert">
                        <div class="flex items-start justify-between body-base">
                            <div class="flex items-start gap-2">
                                <x-solar-trash-bin-trash-outline class="w-8 h-8 mt-4 mr-2 shrink-0" />
                                <div>
                                    Esta es una alerta Error
                                    <p class="font-normal text-error-300 body-small mt-2">
                                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy
                                    </p>
                                </div>
                            </div>
                            <button class="text-error-500 text-2xl line-height-1">
                                <x-solar-close-circle-outline class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 