<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\EmailConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmailConfigurationController extends Controller
{
    /**
     * Mostrar configuración de email
     */
    public function index(): View
    {
        $config = EmailConfiguration::getActive() ?: new EmailConfiguration();
        $defaultTemplates = EmailConfiguration::getDefaultTemplates();
        
        return view('superlinkiu::email.index', compact('config', 'defaultTemplates'));
    }

    /**
     * Actualizar configuración SMTP
     */
    public function updateSmtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer|between:1,65535',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'required|in:tls,ssl,none',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
        ]);

        // Obtener configuración existente o crear nueva
        $config = EmailConfiguration::getActive() ?: new EmailConfiguration();
        
        // Si no se proporciona password, mantener el existente
        if (empty($validated['smtp_password']) && $config->exists) {
            unset($validated['smtp_password']);
        }

        $config->fill($validated);
        $config->save();

        // Activar esta configuración
        $config->activate();

        return redirect()
            ->route('superlinkiu.email.index')
            ->with('success', 'Configuración SMTP actualizada exitosamente.');
    }

    /**
     * Actualizar plantillas de email
     */
    public function updateTemplates(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_created_template' => 'nullable|string',
            'ticket_response_template' => 'nullable|string',
            'ticket_status_changed_template' => 'nullable|string',
            'ticket_assigned_template' => 'nullable|string',
        ]);

        $config = EmailConfiguration::getActive() ?: new EmailConfiguration();
        $config->fill($validated);
        $config->save();

        return redirect()
            ->route('superlinkiu.email.index')
            ->with('success', 'Plantillas de email actualizadas exitosamente.');
    }

    /**
     * Actualizar configuración de eventos
     */
    public function updateEvents(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'send_on_ticket_created' => 'boolean',
            'send_on_ticket_response' => 'boolean',
            'send_on_status_change' => 'boolean',
            'send_on_ticket_assigned' => 'boolean',
        ]);

        $config = EmailConfiguration::getActive() ?: new EmailConfiguration();
        $config->fill($validated);
        $config->save();

        return redirect()
            ->route('superlinkiu.email.index')
            ->with('success', 'Configuración de eventos actualizada exitosamente.');
    }

    /**
     * Probar conexión SMTP
     */
    public function testConnection(Request $request): JsonResponse
    {
        $config = EmailConfiguration::getActive();
        
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'No hay configuración SMTP activa.'
            ]);
        }

        $testEmail = $request->input('test_email');
        $result = $config->testConnection($testEmail);

        return response()->json($result);
    }

    /**
     * Restaurar plantillas por defecto
     */
    public function restoreDefaultTemplates(): JsonResponse
    {
        $config = EmailConfiguration::getActive() ?: new EmailConfiguration();
        $defaultTemplates = EmailConfiguration::getDefaultTemplates();
        
        $config->update([
            'ticket_created_template' => $defaultTemplates['ticket_created'],
            'ticket_response_template' => $defaultTemplates['ticket_response'],
            'ticket_status_changed_template' => $defaultTemplates['ticket_status_changed'],
            'ticket_assigned_template' => $defaultTemplates['ticket_assigned'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plantillas restauradas a valores por defecto.',
            'templates' => $defaultTemplates
        ]);
    }

    /**
     * Alternar estado activo de la configuración
     */
    public function toggleActive(): JsonResponse
    {
        $config = EmailConfiguration::getActive();
        
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'No hay configuración para activar/desactivar.'
            ]);
        }

        $config->update(['is_active' => !$config->is_active]);

        return response()->json([
            'success' => true,
            'message' => $config->is_active ? 'Configuración activada.' : 'Configuración desactivada.',
            'is_active' => $config->is_active
        ]);
    }
} 