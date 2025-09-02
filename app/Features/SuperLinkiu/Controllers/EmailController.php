<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    /**
     * Mostrar dashboard principal de emails
     */
    public function index(): View
    {
        $stats = EmailService::getEmailStats();
        $validation = EmailService::validateConfiguration();
        $contexts = EmailService::getContexts();
        $templates = EmailTemplate::where('is_active', true)->get()->groupBy('context');

        return view('superlinkiu::email.index', compact(
            'stats',
            'validation', 
            'contexts',
            'templates'
        ));
    }

    /**
     * Enviar email de prueba
     */
    public function sendTest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'test_email' => 'required|email|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email inválido: ' . $validator->errors()->first()
            ], 400);
        }

        // Rate limiting simple
        $key = 'email_test:' . $request->ip();
        if (cache()->has($key)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor espera 30 segundos antes de enviar otra prueba'
            ], 429);
        }

        // Enviar email de prueba usando sistema de colas
        try {
            \App\Jobs\SendEmailJob::dispatch('test', $request->test_email);
            
            $result = [
                'success' => true,
                'message' => 'Email de prueba enviado a la cola de procesamiento. Se entregará en breve.'
            ];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al encolar email: ' . $e->getMessage()
            ];
        }

        // Cache del rate limiting
        if ($result['success']) {
            cache()->put($key, true, now()->addSeconds(30));
            cache(['last_email_test' => now()->format('d/m/Y H:i:s')], now()->addDays(30));
        }

        return response()->json($result);
    }

    /**
     * Mostrar gestión de plantillas
     */
    public function templates(): View
    {
        $templates = EmailTemplate::orderBy('context')->orderBy('name')->get();
        $contexts = EmailService::getContexts();

        return view('superlinkiu::email.templates', compact('templates', 'contexts'));
    }

    /**
     * Mostrar formulario de edición de plantilla
     */
    public function editTemplate(EmailTemplate $template): View
    {
        $contexts = EmailService::getContexts();
        $availableVariables = EmailTemplate::CONTEXT_VARIABLES[$template->context] ?? [];

        return view('superlinkiu::email.template-edit', compact(
            'template', 
            'contexts', 
            'availableVariables'
        ));
    }

    /**
     * Actualizar plantilla
     */
    public function updateTemplate(Request $request, EmailTemplate $template): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar plantilla
        $template->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'body_html' => $request->body_html,
            'body_text' => $request->body_text ?: strip_tags($request->body_html),
            'is_active' => $request->boolean('is_active', true)
        ]);

        // Validar variables después de actualizar
        $validationIssues = $template->validateVariables();
        if (!empty($validationIssues)) {
            session()->flash('validation_warnings', $validationIssues);
        }

        Log::info('Plantilla de email actualizada', [
            'template_id' => $template->id,
            'template_key' => $template->key,
            'user_id' => auth()->id()
        ]);

        return redirect()->route('superlinkiu.email.templates')
            ->with('success', 'Plantilla actualizada exitosamente');
    }

    /**
     * Probar plantilla con datos de ejemplo
     */
    public function testTemplate(Request $request, EmailTemplate $template): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'test_email' => 'required|email|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email inválido'
            ], 400);
        }

        // Datos de ejemplo por contexto
        $sampleData = $this->getSampleData($template->context);

        // Enviar email con plantilla
        $result = EmailService::sendWithTemplate(
            $template->key,
            $request->test_email,
            $sampleData
        );

        if ($result['success']) {
            Log::info('Plantilla probada exitosamente', [
                'template_key' => $template->key,
                'test_email' => $request->test_email,
                'user_id' => auth()->id()
            ]);
        }

        return response()->json($result);
    }

    /**
     * Vista previa de plantilla
     */
    public function previewTemplate(EmailTemplate $template): JsonResponse
    {
        $sampleData = $this->getSampleData($template->context);
        $rendered = $template->render($sampleData);

        return response()->json([
            'success' => true,
            'preview' => $rendered,
            'sample_data' => $sampleData
        ]);
    }

    /**
     * Activar/desactivar plantilla
     */
    public function toggleTemplate(EmailTemplate $template): JsonResponse
    {
        $template->update(['is_active' => !$template->is_active]);

        Log::info('Estado de plantilla cambiado', [
            'template_key' => $template->key,
            'is_active' => $template->is_active,
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => $template->is_active ? 'Plantilla activada' : 'Plantilla desactivada',
            'is_active' => $template->is_active
        ]);
    }

    /**
     * Restaurar plantillas por defecto
     */
    public function restoreDefaults(): RedirectResponse
    {
        try {
            EmailTemplate::createDefaults();

            Log::info('Plantillas por defecto restauradas', [
                'user_id' => auth()->id()
            ]);

            return redirect()->route('superlinkiu.email.templates')
                ->with('success', 'Plantillas por defecto restauradas exitosamente');

        } catch (\Exception $e) {
            Log::error('Error restaurando plantillas por defecto', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Error al restaurar plantillas: ' . $e->getMessage());
        }
    }

    /**
     * Ver configuración y diagnósticos
     */
    public function configuration(): View
    {
        $validation = EmailService::validateConfiguration();
        $stats = EmailService::getEmailStats();
        $contexts = EmailService::getContexts();

        return view('superlinkiu::email.configuration', compact(
            'validation',
            'stats', 
            'contexts'
        ));
    }

    /**
     * Enviar email simple desde la interfaz
     */
    public function sendSimple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'context' => 'required|string|in:' . implode(',', array_keys(EmailService::getContexts())),
            'recipient_email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_html' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . $validator->errors()->first()
            ], 400);
        }

        $result = EmailService::sendSimple(
            $request->context,
            $request->recipient_email,
            $request->subject,
            $request->body,
            $request->boolean('is_html', true)
        );

        if ($result['success']) {
            Log::info('Email simple enviado desde admin', [
                'context' => $request->context,
                'recipient' => $request->recipient_email,
                'subject' => $request->subject,
                'user_id' => auth()->id()
            ]);
        }

        return response()->json($result);
    }

    /**
     * Obtener datos de ejemplo por contexto
     */
    private function getSampleData(string $context): array
    {
        $sampleData = [
            'store_management' => [
                'store_name' => 'Mi Tienda Demo',
                'admin_name' => 'Juan Pérez',
                'admin_email' => 'admin@mitienda.com',
                'password' => 'password123',
                'login_url' => 'https://mitienda.linkiu.bio/admin/login',
                'store_url' => 'https://mitienda.linkiu.bio',
                'plan_name' => 'Plan Básico'
            ],
            'support' => [
                'ticket_id' => '12345',
                'ticket_subject' => 'Problema con mi tienda',
                'customer_name' => 'María García',
                'admin_name' => 'Carlos Soporte',
                'ticket_url' => route('superlinkiu.tickets.show', 12345),
                'status' => 'Abierto',
                'response' => 'Hemos revisado tu solicitud y te ayudaremos pronto...'
            ],
            'billing' => [
                'invoice_number' => 'INV-2025-001',
                'amount' => '29.99',
                'due_date' => now()->addDays(15)->format('d/m/Y'),
                'store_name' => 'Mi Tienda Demo',
                'plan_name' => 'Plan Básico',
                'invoice_url' => route('superlinkiu.invoices.show', 1)
            ]
        ];

        return $sampleData[$context] ?? [];
    }
}
