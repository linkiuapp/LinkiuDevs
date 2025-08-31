<?php

namespace App\Http\Controllers;

use App\Services\SystemDebugService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SystemDebugController extends Controller
{
    /**
     * Show debug dashboard
     */
    public function index(Request $request): View
    {
        $level = $request->get('level');
        $limit = (int) $request->get('limit', 50);
        $limit = min($limit, 200); // Max 200 entries
        
        $errors = SystemDebugService::getRecentErrors($limit, $level);
        $stats = SystemDebugService::getErrorStats();
        $systemInfo = SystemDebugService::getSystemInfo();
        
        return view('system-debug.index', compact('errors', 'stats', 'systemInfo', 'level', 'limit'));
    }

    /**
     * Get errors as JSON (for AJAX refresh)
     */
    public function getErrors(Request $request): JsonResponse
    {
        $level = $request->get('level');
        $limit = (int) $request->get('limit', 50);
        $limit = min($limit, 200);
        
        $errors = SystemDebugService::getRecentErrors($limit, $level);
        
        return response()->json([
            'success' => true,
            'errors' => $errors,
            'count' => count($errors)
        ]);
    }

    /**
     * Get system stats as JSON
     */
    public function getStats(): JsonResponse
    {
        $stats = SystemDebugService::getErrorStats();
        $systemInfo = SystemDebugService::getSystemInfo();
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
            'system_info' => $systemInfo
        ]);
    }

    /**
     * Clear old logs
     */
    public function clearLogs(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 7);
        $days = max(1, min($days, 30)); // Between 1 and 30 days
        
        $success = SystemDebugService::clearOldLogs($days);
        
        return response()->json([
            'success' => $success,
            'message' => $success 
                ? "Logs anteriores a {$days} días eliminados exitosamente"
                : 'Error al eliminar logs antiguos'
        ]);
    }

    /**
     * Test error notification
     */
    public function testNotification(): JsonResponse
    {
        $testError = [
            'timestamp' => now()->toDateTimeString(),
            'level' => 'ERROR',
            'message' => 'Test error notification from system debug',
            'stack_trace' => 'This is a test notification to verify email delivery'
        ];
        
        $success = SystemDebugService::sendErrorNotification($testError);
        
        return response()->json([
            'success' => $success,
            'message' => $success 
                ? 'Notificación de prueba enviada exitosamente'
                : 'Error al enviar notificación de prueba'
        ]);
    }

    /**
     * Logout from debug session
     */
    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->session()->forget('debug_authenticated');
        return redirect('/system-debug')->with('message', 'Sesión cerrada exitosamente');
    }
}