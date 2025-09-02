<?php

namespace App\Features\SuperLinkiu\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Core\Providers\RouteServiceProvider;

class UpdateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $storeId = $this->route('store')->id ?? null;

        return [
            // Información básica de la tienda
            'name' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'slug' => 'required|string|max:255|unique:stores,slug,' . $storeId . '|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'email' => 'nullable|email|unique:stores,email,' . $storeId,
            'document_type' => 'nullable|string|in:nit,cedula',
            'document_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,suspended',
            'verified' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la tienda es obligatorio.',
            'plan_id.required' => 'Debe seleccionar un plan.',
            'plan_id.exists' => 'El plan seleccionado no existe.',
            'slug.required' => 'La URL de la tienda es obligatoria.',
            'slug.unique' => 'Esta URL ya está en uso por otra tienda.',
            'slug.regex' => 'La URL debe contener solo letras minúsculas, números y guiones. No se permiten espacios ni caracteres especiales.',
            'email.email' => 'El email de la tienda debe ser válido.',
            'email.unique' => 'Este email ya está registrado por otra tienda.',
            'document_type.in' => 'El tipo de documento debe ser NIT o cédula.',
            'status.in' => 'El estado debe ser activo, inactivo o suspendido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre de la tienda',
            'plan_id' => 'plan',
            'slug' => 'URL de la tienda',
            'email' => 'email de la tienda',
            'document_type' => 'tipo de documento',
            'document_number' => 'número de documento',
            'phone' => 'teléfono',
            'address' => 'dirección',
            'country' => 'país',
            'department' => 'departamento',
            'city' => 'ciudad',
            'description' => 'descripción',
            'status' => 'estado',
            'verified' => 'verificado',
            'meta_title' => 'meta título',
            'meta_description' => 'meta descripción',
            'meta_keywords' => 'meta keywords',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validación personalizada para slug reservado
            if ($this->slug && RouteServiceProvider::isReservedSlug($this->slug)) {
                $validator->errors()->add('slug', 'Esta URL está reservada por el sistema.');
            }
        });
    }
}
