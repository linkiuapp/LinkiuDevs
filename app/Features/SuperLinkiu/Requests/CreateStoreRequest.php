<?php

namespace App\Features\SuperLinkiu\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Core\Providers\RouteServiceProvider;

class CreateStoreRequest extends FormRequest
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
        return [
            // Información del propietario
            'owner_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'owner_document_type' => 'required|string|in:cedula,nit,pasaporte',
            'owner_document_number' => 'required|string|max:20',
            'owner_country' => 'required|string|max:100',
            'owner_department' => 'required|string|max:100',
            'owner_city' => 'required|string|max:100',
            'admin_password' => 'required|string|min:8',
            
            // Información de la tienda
            'name' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'slug' => 'required|string|max:255|unique:stores,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'email' => 'nullable|email|unique:stores,email',
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
            'billing_period' => 'nullable|in:monthly,quarterly,biannual',
            'initial_payment_status' => 'nullable|in:pending,paid',
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
            'owner_name.required' => 'El nombre del representante es obligatorio.',
            'admin_email.required' => 'El email del administrador es obligatorio.',
            'admin_email.email' => 'El email del administrador debe ser válido.',
            'admin_email.unique' => 'Este email ya está registrado en el sistema.',
            'owner_document_type.required' => 'El tipo de documento es obligatorio.',
            'owner_document_type.in' => 'El tipo de documento debe ser cédula, NIT o pasaporte.',
            'owner_document_number.required' => 'El número de documento es obligatorio.',
            'owner_country.required' => 'El país es obligatorio.',
            'owner_department.required' => 'El departamento es obligatorio.',
            'owner_city.required' => 'La ciudad es obligatoria.',
            'admin_password.required' => 'La contraseña del administrador es obligatoria.',
            'admin_password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            
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
            'billing_period.in' => 'El período de facturación debe ser mensual, trimestral o semestral.',
            'initial_payment_status.in' => 'El estado de pago debe ser pendiente o pagado.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'owner_name' => 'nombre del representante',
            'admin_email' => 'email del administrador',
            'owner_document_type' => 'tipo de documento',
            'owner_document_number' => 'número de documento',
            'owner_country' => 'país',
            'owner_department' => 'departamento',
            'owner_city' => 'ciudad',
            'admin_password' => 'contraseña del administrador',
            'name' => 'nombre de la tienda',
            'plan_id' => 'plan',
            'slug' => 'URL de la tienda',
            'email' => 'email de la tienda',
            'document_type' => 'tipo de documento de la empresa',
            'document_number' => 'número de documento de la empresa',
            'phone' => 'teléfono',
            'address' => 'dirección',
            'country' => 'país de la tienda',
            'department' => 'departamento de la tienda',
            'city' => 'ciudad de la tienda',
            'description' => 'descripción',
            'status' => 'estado',
            'verified' => 'verificado',
            'billing_period' => 'período de facturación',
            'initial_payment_status' => 'estado de pago inicial',
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
