@extends('shared::layouts.admin')

@section('title', 'Create Forms')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="col-span-1">
        <!-- Formulario de Creación de Usuario -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-3xl text-black-500 mb-0">Crear Usuario</h2>
            </div>
            <div class="p-6">
                <form class="space-y-4">
                    <!-- Información Personal -->
                    <div>
                        <h3 class="text-lg font-semibold text-black-500 mb-3">Información Personal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block body-base text-black-500 font-medium mb-2">Nombre</label>
                                <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu nombre">
                            </div>
                            <div>
                                <label class="block body-base text-black-500 font-medium mb-2">Apellido</label>
                                <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa tu apellido">
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div>
                        <h3 class="text-lg font-semibold text-black-500 mb-3">Información de Contacto</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block body-base text-black-500 font-medium mb-2">Email</label>
                                <input type="email" class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="ejemplo@correo.com">
                            </div>
                            <div>
                                <label class="block body-base text-black-500 font-medium mb-2">Teléfono</label>
                                <input type="tel" class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="+1 (555) 123-4567">
                            </div>
                        </div>
                    </div>

                    <!-- Foto de Perfil -->
                    <div>
                        <label class="block body-base text-black-500 font-medium mb-2">Foto de Perfil</label>
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-accent-100 rounded-full flex items-center justify-center">
                                <x-solar-user-outline class="w-8 h-8 text-black-300" />
                            </div>
                            <div>
                                <input type="file" id="profile-photo" class="hidden" accept="image/*">
                                <label for="profile-photo" class="cursor-pointer inline-flex items-center px-4 py-2 bg-primary-100 text-primary-400 font-medium rounded-lg hover:bg-primary-200 transition-colors">
                                    <x-solar-camera-outline class="w-4 h-4 mr-2" />
                                    Subir Foto
                                </label>
                                <p class="text-sm text-black-300 mt-1">JPG, PNG hasta 5MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block body-base text-black-500 font-medium mb-2">Rol</label>
                        <select class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors">
                            <option value="">Selecciona un rol</option>
                            <option value="admin">Administrador</option>
                            <option value="editor">Editor</option>
                            <option value="user">Usuario</option>
                        </select>
                    </div>

                    <!-- Permisos -->
                    <div>
                        <label class="block body-base text-black-500 font-medium mb-2">Permisos</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="read" class="w-4 h-4 text-primary-400 bg-accent-100 border-accent-300 rounded focus:ring-primary-300 focus:ring-2">
                                <label for="read" class="ml-2 body-base text-black-400">Leer</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="write" class="w-4 h-4 text-primary-400 bg-accent-100 border-accent-300 rounded focus:ring-primary-300 focus:ring-2">
                                <label for="write" class="ml-2 body-base text-black-400">Escribir</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="delete" class="w-4 h-4 text-primary-400 bg-accent-100 border-accent-300 rounded focus:ring-primary-300 focus:ring-2">
                                <label for="delete" class="ml-2 body-base text-black-400">Eliminar</label>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" class="px-6 py-2 bg-accent-50 border border-accent-200 text-black-400 font-medium rounded-lg hover:bg-accent-100 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2 bg-primary-400 text-accent-50 font-medium rounded-lg hover:bg-primary-500 transition-colors">
                            Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Formulario de Creación de Producto -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-3xl text-black-500 mb-0">Crear Producto</h2>
            </div>
            <div class="p-6">
                <form class="space-y-4">
                    <!-- Información Básica -->
                    <div>
                        <h3 class="text-lg font-semibold text-black-500 mb-3">Información Básica</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block body-base text-black-500 font-medium mb-2">Nombre del Producto</label>
                                <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa el nombre del producto">
                            </div>
                            <div>
                                <label class="block body-base text-black-500 font-medium mb-2">Descripción</label>
                                <textarea rows="3" class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Describe tu producto..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Precios -->
                    <div>
                        <h3 class="text-lg font-semibold text-black-500 mb-3">Precios</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block body-base text-black-500 font-medium mb-2">Precio Regular</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">$</span>
                                    <input type="number" class="w-full pl-8 pr-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="0.00">
                                </div>
                            </div>
                            <div>
                                <label class="block body-base text-black-500 font-medium mb-2">Precio de Oferta</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">$</span>
                                    <input type="number" class="w-full pl-8 pr-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label class="block body-base text-black-500 font-medium mb-2">Categoría</label>
                        <select class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors">
                            <option value="">Selecciona una categoría</option>
                            <option value="electronics">Electrónicos</option>
                            <option value="clothing">Ropa</option>
                            <option value="books">Libros</option>
                            <option value="home">Hogar</option>
                        </select>
                    </div>

                    <!-- Imágenes -->
                    <div>
                        <label class="block body-base text-black-500 font-medium mb-2">Imágenes del Producto</label>
                        <div class="border-2 border-dashed border-accent-200 rounded-lg p-6 text-center">
                            <div class="mx-auto w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                                <x-solar-gallery-outline class="w-6 h-6 text-primary-400" />
                            </div>
                            <p class="body-base text-black-500 font-medium mb-2">Arrastra las imágenes aquí</p>
                            <p class="body-small text-black-300 mb-4">o</p>
                            <input type="file" id="product-images" class="hidden" multiple accept="image/*">
                            <label for="product-images" class="cursor-pointer inline-flex items-center px-4 py-2 bg-primary-100 text-primary-400 font-medium rounded-lg hover:bg-primary-200 transition-colors">
                                <x-solar-upload-outline class="w-4 h-4 mr-2" />
                                Seleccionar Archivos
                            </label>
                            <p class="text-sm text-black-300 mt-2">JPG, PNG hasta 10MB cada una</p>
                        </div>
                    </div>

                    <!-- Variantes -->
                    <div>
                        <label class="block body-base text-black-500 font-medium mb-2">Variantes</label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <input type="text" class="flex-1 px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Talla">
                                <input type="text" class="flex-1 px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Color">
                                <input type="number" class="w-20 px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Stock">
                                <button type="button" class="p-3 text-error-400 hover:bg-error-50 rounded-lg transition-colors">
                                    <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                </button>
                            </div>
                            <button type="button" class="w-full py-2 px-4 border border-dashed border-primary-400 text-primary-400 rounded-lg hover:bg-primary-50 transition-colors">
                                <x-solar-add-circle-outline class="w-4 h-4 mr-2 inline" />
                                Agregar Variante
                            </button>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block body-base text-black-500 font-medium mb-2">Estado</label>
                        <div class="flex items-center space-x-6">
                            <div class="flex items-center">
                                <input type="radio" id="active" name="status" value="active" class="w-4 h-4 text-primary-400 bg-accent-100 border-accent-300 focus:ring-primary-300 focus:ring-2">
                                <label for="active" class="ml-2 body-base text-black-400">Activo</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="inactive" name="status" value="inactive" class="w-4 h-4 text-primary-400 bg-accent-100 border-accent-300 focus:ring-primary-300 focus:ring-2">
                                <label for="inactive" class="ml-2 body-base text-black-400">Inactivo</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="draft" name="status" value="draft" class="w-4 h-4 text-primary-400 bg-accent-100 border-accent-300 focus:ring-primary-300 focus:ring-2">
                                <label for="draft" class="ml-2 body-base text-black-400">Borrador</label>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" class="px-6 py-2 bg-accent-50 border border-accent-200 text-black-400 font-medium rounded-lg hover:bg-accent-100 transition-colors">
                            Guardar como Borrador
                        </button>
                        <button type="submit" class="px-6 py-2 bg-primary-400 text-accent-50 font-medium rounded-lg hover:bg-primary-500 transition-colors">
                            Crear Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-span-1 lg:col-span-2">
        <!-- Formulario de Creación de Proyecto -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-3xl text-black-500 mb-0">Crear Proyecto</h2>
            </div>
            <div class="p-6">
                <form class="space-y-6">
                    <!-- Información del Proyecto -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-black-500 mb-3">Información del Proyecto</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block body-base text-black-500 font-medium mb-2">Nombre del Proyecto</label>
                                    <input type="text" class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Ingresa el nombre del proyecto">
                                </div>
                                <div>
                                    <label class="block body-base text-black-500 font-medium mb-2">Descripción</label>
                                    <textarea rows="4" class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="Describe tu proyecto..."></textarea>
                                </div>
                                <div>
                                    <label class="block body-base text-black-500 font-medium mb-2">Prioridad</label>
                                    <select class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors">
                                        <option value="">Selecciona prioridad</option>
                                        <option value="high">Alta</option>
                                        <option value="medium">Media</option>
                                        <option value="low">Baja</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-black-500 mb-3">Fechas y Equipo</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block body-base text-black-500 font-medium mb-2">Fecha de Inicio</label>
                                    <input type="date" class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors">
                                </div>
                                <div>
                                    <label class="block body-base text-black-500 font-medium mb-2">Fecha de Entrega</label>
                                    <input type="date" class="w-full px-4 py-3 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors">
                                </div>
                                <div>
                                    <label class="block body-base text-black-500 font-medium mb-2">Miembros del Equipo</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center space-x-2">
                                            <input type="email" class="flex-1 px-4 py-2 bg-accent-50 border border-accent-200 rounded-lg body-base text-black-400 placeholder-black-300 focus:outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-400 transition-colors" placeholder="correo@ejemplo.com">
                                            <button type="button" class="px-4 py-2 bg-primary-100 text-primary-400 rounded-lg hover:bg-primary-200 transition-colors">
                                                <x-solar-add-circle-outline class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-100 text-primary-400">
                                                juan@ejemplo.com
                                                <button type="button" class="ml-2 text-primary-300 hover:text-primary-500">
                                                    <x-solar-close-circle-outline class="w-4 h-4" />
                                                </button>
                                            </span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-100 text-primary-400">
                                                maria@ejemplo.com
                                                <button type="button" class="ml-2 text-primary-300 hover:text-primary-500">
                                                    <x-solar-close-circle-outline class="w-4 h-4" />
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Archivos del Proyecto -->
                    <div>
                        <h3 class="text-lg font-semibold text-black-500 mb-3">Archivos del Proyecto</h3>
                        <div class="border-2 border-dashed border-accent-200 rounded-lg p-8 text-center">
                            <div class="mx-auto w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                                <x-solar-folder-outline class="w-8 h-8 text-primary-400" />
                            </div>
                            <p class="body-base text-black-500 font-medium mb-2">Arrastra los archivos aquí</p>
                            <p class="body-small text-black-300 mb-4">Archivos soportados: PDF, DOC, XLS, PPT, ZIP (máximo 50MB)</p>
                            <input type="file" id="project-files" class="hidden" multiple>
                            <label for="project-files" class="cursor-pointer inline-flex items-center px-6 py-3 bg-primary-400 text-accent-50 font-medium rounded-lg hover:bg-primary-500 transition-colors">
                                <x-solar-upload-outline class="w-5 h-5 mr-2" />
                                Seleccionar Archivos
                            </label>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" class="px-6 py-2 bg-accent-50 border border-accent-200 text-black-400 font-medium rounded-lg hover:bg-accent-100 transition-colors">
                            Cancelar
                        </button>
                        <button type="button" class="px-6 py-2 bg-accent-100 border border-accent-200 text-black-400 font-medium rounded-lg hover:bg-accent-200 transition-colors">
                            Guardar Borrador
                        </button>
                        <button type="submit" class="px-6 py-2 bg-primary-400 text-accent-50 font-medium rounded-lg hover:bg-primary-500 transition-colors">
                            Crear Proyecto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 