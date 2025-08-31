@extends('shared::layouts.admin')

@section('title', 'Image Upload')

@section('content')
<div class="p-6">

    <!-- Basic Upload -->
    <div class="card">
        <div class="card-header">
            <h2 class="title-card">Basic Upload</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="title-card">Basic Upload</h6>
                    </div>
                    <div class="card-body">
                        <label for="basic-upload" class="border border-primary-300 body-base text-primary-300 px-4 py-3 rounded-xl inline-flex items-center gap-2 cursor-pointer hover:bg-primary-50">
                            <x-solar-upload-outline class="w-5 h-5" />
                            Click to upload
                        </label>
                        <input type="file" id="basic-upload" class="block w-full body-small text-black-400 border border-accent-200 rounded-lg cursor-pointer bg-accent-50 mt-6 p-3">
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h6 class="title-card">Multiple Upload</h6>
                    </div>
                    <div class="card-body">
                        <label for="multiple-upload" class="border border-secondary-300 body-base text-secondary-300 px-4 py-3 rounded-xl inline-flex items-center gap-2 cursor-pointer hover:bg-secondary-50">
                            <x-solar-upload-outline class="w-5 h-5" />
                            Upload Multiple Files
                        </label>
                        <input type="file" id="multiple-upload" multiple class="block w-full body-small text-black-400 border border-accent-200 rounded-lg cursor-pointer bg-accent-50 mt-6 p-3">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Upload with Preview -->
    <div class="card mt-12">
        <div class="card-header">
            <h2 class="title-card">Image Upload with Preview</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <div class="card-header">
                        <h6 class="title-card">Single Image Upload</h6>
                    </div>
                    <div class="card-body">
                        <div class="upload-image-wrapper flex items-center gap-3">
                            <div class="uploaded-img hidden relative h-[120px] w-[120px] border border-dashed border-accent-200 rounded-lg overflow-hidden bg-accent-200">
                                <button type="button" class="uploaded-img__remove absolute top-0 right-0 z-10 text-error-200 hover:text-error-300 m-2 flex">
                                    <x-solar-close-circle-outline class="w-5 h-5" />
                                </button>
                                <img id="uploaded-img__preview" class="w-full h-full object-cover" src="#" alt="Preview">
                            </div>

                            <label class="upload-file h-[120px] w-[120px] border border-dashed border-accent-200 rounded-lg overflow-hidden bg-accent-100 hover:bg-accent-200 flex items-center flex-col justify-center gap-1 cursor-pointer" for="upload-file">
                                <x-solar-camera-outline class="w-6 h-6 text-black-400" />
                                <span class="text-sm text-black-400">Upload</span>
                                <input id="upload-file" type="file" accept="image/*" class="hidden">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h6 class="title-card">Multiple Image Upload</h6>
                    </div>
                    <div class="card-body">
                        <div class="upload-image-wrapper flex items-center gap-3 flex-wrap">
                            <div class="uploaded-imgs-container flex gap-3 flex-wrap"></div>
                            <label class="upload-file-multiple h-[120px] w-[120px] border border-dashed border-accent-200 rounded-lg overflow-hidden bg-accent-200 hover:bg-accent-300 flex items-center flex-col justify-center gap-1 cursor-pointer" for="upload-file-multiple">
                                <x-solar-camera-outline class="w-6 h-6 text-black-400" />
                                <span class="text-sm text-black-400">Upload</span>
                                <input id="upload-file-multiple" type="file" accept="image/*" multiple class="hidden">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload with File List -->
    <div class="card mt-12">
        <div class="card-header">
            <h2 class="title-card">Upload with File List</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="title-card">File List Upload</h6>
                    </div>
                    <div class="card-body">
                        <label for="file-upload-name" class="mb-4 border border-info-300 text-info-300 px-4 py-3 rounded-xl inline-flex items-center gap-2 hover:bg-info-50 cursor-pointer">
                            <x-solar-upload-outline class="w-5 h-5" />
                            Click to upload
                            <input type="file" id="file-upload-name" multiple class="hidden">
                        </label>
                        <ul id="uploaded-img-names" class="mt-4 space-y-2"></ul>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h6 class="title-card">Drag & Drop Upload</h6>
                    </div>
                    <div class="card-body">
                        <div class="border-2 border-dashed border-accent-400 rounded-lg p-8 text-center bg-accent-100 hover:bg-accent-200 transition-colors duration-200 cursor-pointer" id="drag-drop-area">
                            <div class="flex flex-col items-center justify-center gap-4">
                                <x-solar-cloud-upload-outline class="w-12 h-12 text-info-200" />
                                <div>
                                    <p class="text-base text-black-400 mb-2">Drag and drop files here or</p>
                                    <p class="text-info-200 text-base font-semibold">click anywhere to browse</p>
                                    <input type="file" id="drag-drop-input" multiple class="hidden">
                                </div>
                                <p class="text-sm text-black-400">Support for a single or bulk upload</p>
                            </div>
                        </div>
                        <div id="drag-drop-files" class="mt-4 space-y-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload with Progress -->
    <div class="card mt-12">
        <div class="card-header">
            <h2 class="title-card">Upload with Progress</h2>
        </div>
        <div class="card-body">
            <div class="card">
                <div class="card-header">
                    <h6 class="title-card">Progress Upload</h6>
                </div>
                <div class="card-body">
                    <label for="progress-upload" class="border border-success-300 text-success-300 px-4 py-3 rounded-xl inline-flex items-center gap-2 cursor-pointer hover:bg-success-50">
                        <x-solar-upload-outline class="w-5 h-5" />
                        Upload with Progress
                    </label>
                    <input type="file" id="progress-upload" multiple class="hidden">
                    <div id="progress-container" class="mt-4 space-y-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Variants -->
    <div class="card mt-12">
        <div class="card-header">
            <h2 class="title-card">Upload Variants</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Primary Variant -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="title-card">Primary Upload</h6>
                    </div>
                    <div class="card-body">
                        <div id="primary-upload-area" class="border-2 border-dashed border-primary-300 rounded-lg p-6 text-center bg-primary-50 cursor-pointer hover:bg-primary-100 transition-colors duration-200">
                            <x-solar-cloud-upload-outline class="w-8 h-8 text-primary-300 mx-auto mb-3" />
                            <p class="text-base text-primary-300 mb-2">Drop files here</p>
                            <p class="text-primary-300 text-sm">or click anywhere to browse</p>
                            <input type="file" id="primary-upload" multiple class="hidden" accept="image/*">
                        </div>
                    </div>
                </div>

                <!-- Secondary Variant -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="title-card">Secondary Upload</h6>
                    </div>
                    <div class="card-body">
                        <div id="secondary-upload-area" class="border-2 border-dashed border-secondary-300 rounded-lg p-6 text-center bg-secondary-50 cursor-pointer hover:bg-secondary-100 transition-colors duration-200">
                            <x-solar-cloud-upload-outline class="w-8 h-8 text-secondary-300 mx-auto mb-3" />
                            <p class="text-base text-secondary-300 mb-2">Drop files here</p>
                            <p class="text-secondary-300 text-sm">or click anywhere to browse</p>
                            <input type="file" id="secondary-upload" multiple class="hidden" accept="image/*">
                        </div>
                    </div>
                </div>

                <!-- Success Variant -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="title-card">Success Upload</h6>
                    </div>
                    <div class="card-body">
                        <div id="success-upload-area" class="border-2 border-dashed border-success-300 rounded-lg p-6 text-center bg-success-50 cursor-pointer hover:bg-success-100 transition-colors duration-200">
                            <x-solar-cloud-upload-outline class="w-8 h-8 text-success-300 mx-auto mb-3" />
                            <p class="text-base text-success-300 mb-2">Drop files here</p>
                            <p class="text-success-300 text-sm">or click anywhere to browse</p>
                            <input type="file" id="success-upload" multiple class="hidden" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- El JavaScript para este componente se maneja en resources/js/components.js --}}

@endsection 