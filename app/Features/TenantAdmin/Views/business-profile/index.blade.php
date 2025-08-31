<x-tenant-admin-layout :store="$store">
    @section('title', 'Perfil del Negocio')
    @section('subtitle', 'Gestiona la información de tu tienda')

    @section('content')
 
        <!-- Tabs Navigation -->
        <div class="bg-accent-50 rounded-lg overflow-hidden p-6">
            <div class="border-b border-accent-100">
                <nav class="flex space-x-8 px-6 py-4">
                    <button onclick="showTab('owner')" class="tab-button text-primary-300 border-b-2 border-primary-300 pb-2" id="tab-owner">
                        <x-solar-user-outline class="w-5 h-5 inline mr-2" />
                        Propietario
                    </button>
                    <button onclick="showTab('store')" class="tab-button text-black-300 hover:text-primary-300 pb-2" id="tab-store">
                        <x-solar-shop-outline class="w-5 h-5 inline mr-2" />
                        Tienda
                    </button>
                    <button onclick="showTab('fiscal')" class="tab-button text-black-300 hover:text-primary-300 pb-2" id="tab-fiscal">
                        <x-solar-document-text-outline class="w-5 h-5 inline mr-2" />
                        Fiscal
                    </button>
                    <button onclick="showTab('seo')" class="tab-button text-black-300 hover:text-primary-300 pb-2" id="tab-seo">
                        <x-solar-chart-outline class="w-5 h-5 inline mr-2" />
                        SEO
                    </button>
                    <button onclick="showTab('policies')" class="tab-button text-black-300 hover:text-primary-300 pb-2" id="tab-policies">
                        <x-solar-shield-check-outline class="w-5 h-5 inline mr-2" />
                        Políticas
                    </button>
                    <button onclick="showTab('about')" class="tab-button text-black-300 hover:text-primary-300 pb-2" id="tab-about">
                        <x-solar-info-circle-outline class="w-5 h-5 inline mr-2" />
                        Acerca de
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Tab 1: Propietario -->
                <div id="content-owner" class="tab-content">
                    <form action="{{ route('tenant.admin.business-profile.update-owner', ['store' => $store->slug]) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Nombre</label>
                                <input type="text" name="owner_name" value="{{ old('owner_name', auth()->user()->name) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Email</label>
                                <input type="email" name="owner_email" value="{{ old('owner_email', auth()->user()->email) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Tipo de Documento</label>
                                <select name="owner_document_type" class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                                    <option value="CC" {{ old('owner_document_type', $store->document_type) == 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                    <option value="CE" {{ old('owner_document_type', $store->document_type) == 'CE' ? 'selected' : '' }}>Cédula de Extranjería</option>
                                    <option value="PP" {{ old('owner_document_type', $store->document_type) == 'PP' ? 'selected' : '' }}>Pasaporte</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Número de Documento</label>
                                <input type="text" name="owner_document_number" value="{{ old('owner_document_number', $store->document_number) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Teléfono</label>
                                <input type="text" name="owner_phone" value="{{ old('owner_phone', $store->phone) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">País</label>
                                <input type="text" name="owner_country" value="{{ old('owner_country', $store->country) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Departamento</label>
                                <input type="text" name="owner_department" value="{{ old('owner_department', $store->department) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Ciudad</label>
                                <input type="text" name="owner_city" value="{{ old('owner_city', $store->city) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-black-400 mb-2">Dirección</label>
                                <input type="text" name="owner_address" value="{{ old('owner_address', $store->address) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="bg-primary-200 text-accent-50 px-6 py-2 rounded-md hover:bg-primary-300 transition-colors">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Tienda -->
                <div id="content-store" class="tab-content hidden">
                    <form action="{{ route('tenant.admin.business-profile.update-store', ['store' => $store->slug]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Nombre de la Tienda</label>
                                <input type="text" name="name" value="{{ old('name', $store->name) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Slug (No editable)</label>
                                <input type="text" value="{{ $store->slug }}" readonly
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md bg-accent-100 text-black-300">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-black-400 mb-2">Descripción</label>
                                <textarea name="description" rows="3" 
                                          class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('description', $store->description) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Logo</label>
                                <input type="file" name="logo" accept="image/*" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                                @if($store->logo_url)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $store->logo_url) }}" alt="Logo actual" class="w-16 h-16 object-cover rounded">
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Estado</label>
                                <input type="text" value="{{ ucfirst($store->status) }}" readonly
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md bg-accent-100 text-black-300">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="bg-primary-200 text-accent-50 px-6 py-2 rounded-md hover:bg-primary-300 transition-colors">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 3: Fiscal -->
                <div id="content-fiscal" class="tab-content hidden">
                    <form action="{{ route('tenant.admin.business-profile.update-fiscal', ['store' => $store->slug]) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Razón Social</label>
                                <input type="text" name="business_name" value="{{ old('business_name', $store->header_text_title) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">NIT</label>
                                <input type="text" name="tax_id" value="{{ old('tax_id', $store->document_number) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-black-400 mb-2">Dirección Fiscal</label>
                                <input type="text" name="fiscal_address" value="{{ old('fiscal_address', $store->address) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Ciudad</label>
                                <input type="text" name="fiscal_city" value="{{ old('fiscal_city', $store->city) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Departamento</label>
                                <input type="text" name="fiscal_department" value="{{ old('fiscal_department', $store->department) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Código Postal</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code') }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="bg-primary-200 text-accent-50 px-6 py-2 rounded-md hover:bg-primary-300 transition-colors">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 4: SEO -->
                <div id="content-seo" class="tab-content hidden">
                    <form action="{{ route('tenant.admin.business-profile.update-seo', ['store' => $store->slug]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Título SEO</label>
                                <input type="text" name="meta_title" value="{{ old('meta_title', $store->meta_title) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Meta Descripción</label>
                                <textarea name="meta_description" rows="3" 
                                          class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('meta_description', $store->meta_description) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Keywords</label>
                                <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $store->meta_keywords) }}" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Imagen OG</label>
                                <input type="file" name="og_image" accept="image/*" 
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Google Analytics</label>
                                <input type="text" name="google_analytics" value="{{ old('google_analytics') }}" 
                                       placeholder="GA-XXXXXXXXX"
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Facebook Pixel</label>
                                <input type="text" name="facebook_pixel" value="{{ old('facebook_pixel') }}" 
                                       placeholder="123456789"
                                       class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="bg-primary-200 text-accent-50 px-6 py-2 rounded-md hover:bg-primary-300 transition-colors">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 5: Políticas -->
                <div id="content-policies" class="tab-content hidden">
                    <form action="{{ route('tenant.admin.business-profile.update-policies', ['store' => $store->slug]) }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Política de Privacidad</label>
                                <textarea name="privacy_policy" rows="4" 
                                          class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('privacy_policy', $policies->privacy_policy) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Términos y Condiciones</label>
                                <textarea name="terms_conditions" rows="4" 
                                          class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('terms_conditions', $policies->terms_conditions) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Política de Envíos</label>
                                <textarea name="shipping_policy" rows="4" 
                                          class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('shipping_policy', $policies->shipping_policy) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Política de Devoluciones</label>
                                <textarea name="return_policy" rows="4" 
                                          class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('return_policy', $policies->return_policy) }}</textarea>
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="bg-primary-200 text-accent-50 px-6 py-2 rounded-md hover:bg-primary-300 transition-colors">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 6: Acerca de -->
                <div id="content-about" class="tab-content hidden">
                    <form action="{{ route('tenant.admin.business-profile.update-about', ['store' => $store->slug]) }}" method="POST">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">Acerca de Nosotros</label>
                            <textarea name="about_us" rows="6" 
                                      class="w-full px-3 py-2 border border-accent-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('about_us', $policies->about_us) }}</textarea>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="bg-primary-200 text-accent-50 px-6 py-2 rounded-md hover:bg-primary-300 transition-colors">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function showTab(tabName) {
                // Ocultar todos los contenidos
                const contents = document.querySelectorAll('.tab-content');
                contents.forEach(content => content.classList.add('hidden'));
                
                // Resetear todos los botones
                const buttons = document.querySelectorAll('.tab-button');
                buttons.forEach(button => {
                    button.classList.remove('text-primary-300', 'border-b-2', 'border-primary-300');
                    button.classList.add('text-black-300', 'hover:text-primary-300');
                });
                
                // Mostrar el contenido activo
                document.getElementById('content-' + tabName).classList.remove('hidden');
                
                // Activar el botón correspondiente
                const activeButton = document.getElementById('tab-' + tabName);
                activeButton.classList.remove('text-black-300', 'hover:text-primary-300');
                activeButton.classList.add('text-primary-300', 'border-b-2', 'border-primary-300');
            }
        </script>
    @endsection
</x-tenant-admin-layout> 