@extends('shared::layouts.admin')

@section('title', 'Botones')

@section('content')
<div class="p-6">
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Default Buttons -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Default Buttons</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-primary">Primary</button>
                    <button type="button" class="btn-secondary">Secondary</button>
                    <button type="button" class="btn-success">Success</button>
                    <button type="button" class="btn-info">Info</button>
                    <button type="button" class="btn-warning">Warning</button>
                    <button type="button" class="btn-error">Error</button>
                    <button type="button" class="btn-dark">Dark</button>
                </div>
            </div>
        </div>

        <!-- Outline Buttons -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Outline Buttons</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-outline-primary">Primary</button>
                    <button type="button" class="btn-outline-secondary">Secondary</button>
                    <button type="button" class="btn-outline-success">Success</button>
                    <button type="button" class="btn-outline-info">Info</button>
                    <button type="button" class="btn-outline-warning">Warning</button>
                    <button type="button" class="btn-outline-error">Error</button>
                    <button type="button" class="btn-outline-dark">Dark</button>
                </div>
            </div>
        </div>

        <!-- Rounded Buttons -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Rounded Buttons</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-rounded-primary">Primary</button>
                    <button type="button" class="btn-rounded-secondary">Secondary</button>
                    <button type="button" class="btn-rounded-success">Success</button>
                    <button type="button" class="btn-rounded-info">Info</button>
                    <button type="button" class="btn-rounded-warning">Warning</button>
                    <button type="button" class="btn-rounded-error">Error</button>
                    <button type="button" class="btn-rounded-dark">Dark</button>
                </div>
            </div>
        </div>

        <!-- Rounded Outline Buttons -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Rounded Outline Buttons</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-rounded-outline-primary">Primary</button>
                    <button type="button" class="btn-rounded-outline-secondary">Secondary</button>
                    <button type="button" class="btn-rounded-outline-success">Success</button>
                    <button type="button" class="btn-rounded-outline-info">Info</button>
                    <button type="button" class="btn-rounded-outline-warning">Warning</button>
                    <button type="button" class="btn-rounded-outline-error">Error</button>
                    <button type="button" class="btn-rounded-outline-dark">Dark</button>
                </div>
            </div>
        </div>

        <!-- Soft Buttons -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Soft Buttons</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-soft-primary">Primary</button>
                    <button type="button" class="btn-soft-secondary">Secondary</button>
                    <button type="button" class="btn-soft-success">Success</button>
                    <button type="button" class="btn-soft-info">Info</button>
                    <button type="button" class="btn-soft-warning">Warning</button>
                    <button type="button" class="btn-soft-error">Error</button>
                    <button type="button" class="btn-soft-dark">Dark</button>
                </div>
            </div>
        </div>

        <!-- Text Buttons -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Text Buttons</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-text-primary">Primary</button>
                    <button type="button" class="btn-text-secondary">Secondary</button>
                    <button type="button" class="btn-text-success">Success</button>
                    <button type="button" class="btn-text-info">Info</button>
                    <button type="button" class="btn-text-warning">Warning</button>
                    <button type="button" class="btn-text-error">Error</button>
                    <button type="button" class="btn-text-dark">Dark</button>
                </div>
            </div>
        </div>

        <!-- Buttons with Icons -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Buttons with Icons</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-icon-left">
                        <x-solar-arrow-left-outline class="w-5 h-5" />
                        Left Icon
                    </button>
                    <button type="button" class="btn-icon-left-outline">
                        <x-solar-arrow-left-outline class="w-5 h-5" />
                        Left Icon
                    </button>

                    <button type="button" class="btn-icon-right">
                        Right Icon
                        <x-solar-arrow-right-outline class="w-5 h-5" />
                    </button>
                    <button type="button" class="btn-icon-right-outline">
                        Right Icon
                        <x-solar-arrow-right-outline class="w-5 h-5" />
                    </button>

                    <button type="button" class="btn-icon-only">
                        <x-solar-arrow-up-outline class="w-5 h-5" />
                    </button>
                    <button type="button" class="btn-icon-only-outline">
                        <x-solar-arrow-down-outline class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Buttons with Icons Round -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Buttons with Icons Round</h2>
            </div>
            <div class="p-6">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-icon-left-rounded">
                        <x-solar-arrow-left-outline class="w-5 h-5" />
                        Left Icon
                    </button>
                    <button type="button" class="btn-icon-left-rounded-outline">
                        <x-solar-arrow-left-outline class="w-5 h-5" />
                        Left Icon
                    </button>

                    <button type="button" class="btn-icon-right-rounded">
                        Right Icon
                        <x-solar-arrow-right-outline class="w-5 h-5" />
                    </button>
                    <button type="button" class="btn-icon-right-rounded-outline">
                        Right Icon
                        <x-solar-arrow-right-outline class="w-5 h-5" />
                    </button>

                    <button type="button" class="btn-icon-only-rounded">
                        <x-solar-arrow-up-outline class="w-5 h-5" />
                    </button>
                    <button type="button" class="btn-icon-only-rounded-outline">
                        <x-solar-arrow-down-outline class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Button Sizes -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Button Sizes</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-large">Large Button</button>
                    <button type="button" class="btn-medium">Medium Button</button>
                    <button type="button" class="btn-small">Small Button</button>
                    <button type="button" class="btn-extra-small">Extra Small</button>
                </div>
                <div class="flex flex-wrap items-center gap-3 mt-4">
                    <button type="button" class="btn-large-soft">Large Button</button>
                    <button type="button" class="btn-medium-soft">Medium Button</button>
                    <button type="button" class="btn-small-soft">Small Button</button>
                    <button type="button" class="btn-extra-small-soft">Extra Small</button>
                </div>
            </div>
        </div>

        <!-- Button Groups -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Button Groups</h2>
            </div>
            <div class="card-body">
            </div>
            <div class="p-6 flex flex-wrap items-center gap-5">
                <div class="flex items-center" role="group">
                    <button type="button" class="btn-group-left">Left</button>
                    <button type="button" class="btn-group-middle">Middle</button>
                    <button type="button" class="btn-group-right">Right</button>
                </div>
                <div class="flex items-center" role="group">
                    <button type="button" class="btn-group-left-rounded">Left</button>
                    <button type="button" class="btn-group-middle-rounded">Middle</button>
                    <button type="button" class="btn-group-right-rounded">Right</button>
                </div>
                <div class="flex items-center" role="group">
                    <button type="button" class="btn-group-icon-left">
                        <x-solar-align-left-outline class="w-5 h-5" />
                    </button>
                    <button type="button" class="btn-group-icon-middle">
                        <x-solar-align-horizontal-center-outline class="w-5 h-5" />
                    </button>
                    <button type="button" class="btn-group-icon-right">
                        <x-solar-align-right-outline class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading States -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Loading States</h2>
            </div>
            <div class="card-body">
            </div>
            <div class="p-6">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="loading-state-loading" disabled>
                        <x-solar-refresh-circle-outline class="w-5 h-5 animate-spin" />
                        Loading...
                    </button>
                    <button type="button" class="btn-state-uploading" disabled>
                        <x-solar-upload-outline class="w-5 h-5" />
                        Uploading...
                    </button>
                    <button type="button" class="btn-state-disabled" disabled>
                        Disabled
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 