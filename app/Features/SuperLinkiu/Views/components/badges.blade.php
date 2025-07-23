@extends('shared::layouts.admin')
@section('title', 'Badges')
@section('content')
<div class="container-fluid">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Default Badges -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Default Badges</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="badge-primary">Primary</span>
                    <span class="badge-secondary">Secondary</span>
                    <span class="badge-success">Success</span>
                    <span class="badge-info">Info</span>
                    <span class="badge-warning">Warning</span>
                    <span class="badge-error">Error</span>
                    <span class="badge-dark">Dark</span>
                </div>
            </div>
        </div>

        <!-- Outline Badges -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Outline Badges</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="badge-outline-primary">Primary</span>
                    <span class="badge-outline-secondary">Secondary</span>
                    <span class="badge-outline-success">Success</span>
                    <span class="badge-outline-info">Info</span>
                    <span class="badge-outline-warning">Warning</span>
                    <span class="badge-outline-error">Error</span>
                    <span class="badge-outline-dark">Dark</span>
                </div>
            </div>
        </div>

        <!-- Soft Badges -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Soft Badges</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="badge-soft-primary">Primary</span>
                    <span class="badge-soft-secondary">Secondary</span>
                    <span class="badge-soft-success">Success</span>
                    <span class="badge-soft-info">Info</span>
                    <span class="badge-soft-warning">Warning</span>
                    <span class="badge-soft-error">Error</span>
                    <span class="badge-soft-dark">Dark</span>
                </div>
            </div>
        </div>

        <!-- Rounded Badges -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Rounded Badges</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="badge-rounded-primary">Primary</span>
                    <span class="badge-rounded-secondary">Secondary</span>
                    <span class="badge-rounded-success">Success</span>
                    <span class="badge-rounded-info">Info</span>
                    <span class="badge-rounded-warning">Warning</span>
                    <span class="badge-rounded-error">Error</span>
                    <span class="badge-rounded-dark">Dark</span>
                </div>
            </div>
        </div>

        <!-- Gradient Badges -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Gradient Badges</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="badge-gradient-primary">Primary</span>
                    <span class="badge-gradient-secondary">Secondary</span>
                    <span class="badge-gradient-success">Success</span>
                    <span class="badge-gradient-info">Info</span>
                    <span class="badge-gradient-warning">Warning</span>
                    <span class="badge-gradient-error">Error</span>
                    <span class="badge-gradient-dark">Dark</span>
                </div>
            </div>
        </div>

        <!-- Badges with Button -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Badges with Button</h2>
            </div>
            <div class="p-6">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="badge-with-button-primary">
                        Primary
                        <span class="badge-span">4</span>
                    </button>
                    <button type="button" class="badge-with-button-secondary">
                        Secondary
                        <span class="badge-span">4</span>
                    </button>
                    <button type="button" class="badge-with-button-success">
                        Success
                        <span class="badge-span">4</span>
                    </button>
                    <button type="button" class="badge-with-button-info">
                        Info
                        <span class="badge-span">4</span>
                    </button>
                    <button type="button" class="badge-with-button-warning">
                        Warning
                        <span class="badge-span">4</span>
                    </button>
                    <button type="button" class="badge-with-button-error">
                        Error
                        <span class="badge-span">4</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Circular Number Badges -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Circular Number Badges</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Solid -->
                    <span class="badge-circle-solid-primary">1</span>
                    <span class="badge-circle-solid-secondary">2</span>
                    <span class="badge-circle-solid-success">3</span>
                    <span class="badge-circle-solid-info">4</span>
                    <span class="badge-circle-solid-warning">5</span>
                    <span class="badge-circle-solid-error">6</span>

                    <!-- Soft -->
                    <span class="badge-circle-soft-primary">1</span>
                    <span class="badge-circle-soft-secondary">2</span>
                    <span class="badge-circle-soft-success">3</span>
                    <span class="badge-circle-soft-info">4</span>
                    <span class="badge-circle-soft-warning">5</span>
                    <span class="badge-circle-soft-error">6</span>

                    <!-- Outline -->
                    <span class="badge-circle-outline-primary">1</span>
                    <span class="badge-circle-outline-secondary">2</span>
                    <span class="badge-circle-outline-success">3</span>
                    <span class="badge-circle-outline-info">4</span>
                    <span class="badge-circle-outline-warning">5</span>
                    <span class="badge-circle-outline-error">6</span>
                </div>
            </div>
        </div>

        <!-- Square Number Badges -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Square Number Badges</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Solid -->
                    <span class="badge-square-solid-primary">1</span>
                    <span class="badge-square-solid-secondary">2</span>
                    <span class="badge-square-solid-success">3</span>
                    <span class="badge-square-solid-info">4</span>
                    <span class="badge-square-solid-warning">5</span>
                    <span class="badge-square-solid-error">6</span>

                    <!-- Soft -->
                    <span class="badge-square-soft-primary">1</span>
                    <span class="badge-square-soft-secondary">2</span>
                    <span class="badge-square-soft-success">3</span>
                    <span class="badge-square-soft-info">4</span>
                    <span class="badge-square-soft-warning">5</span>
                    <span class="badge-square-soft-error">6</span>

                    <!-- Outline -->
                    <span class="badge-square-outline-primary">1</span>
                    <span class="badge-square-outline-secondary">2</span>
                    <span class="badge-square-outline-success">3</span>
                    <span class="badge-square-outline-info">4</span>
                    <span class="badge-square-outline-warning">5</span>
                    <span class="badge-square-outline-error">6</span>
                </div>
            </div>
        </div>

        <!-- Notification Badges -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Notification Badges</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" class="badge-notification-primary">
                            Inbox
                            <span class="badge-notification-counter">2</span>
                        </button>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" class="badge-notification-secondary">
                            Inbox
                            <span class="badge-notification-counter">99+</span>
                        </button>
                    </div>

                    <button class="badge-icon" type="button">
                        <x-solar-letter-outline class="text-black-400 w-8 h-8" />
                        <span class="badge-icon-counter">2</span>
                    </button>

                    <button class="badge-icon" type="button">
                        <x-solar-bell-outline class="text-black-400 w-8 h-8" />
                        <span class="badge-icon-counter">2</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Badge Dots Style -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Badge Dots Style</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="badge-dot-primary"></span>
                        <span class="text-primary-400 font-medium">Primary</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge-dot-secondary"></span>
                        <span class="text-secondary-300 font-medium">Secondary</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge-dot-success"></span>
                        <span class="text-success-400 font-medium">Success</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge-dot-info"></span>
                        <span class="text-info-400 font-medium">Info</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge-dot-warning"></span>
                        <span class="text-warning-300 font-medium">Warning</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge-dot-error"></span>
                        <span class="text-error-400 font-medium">Error</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 