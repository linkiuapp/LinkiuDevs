# Plan de Implementación - Gestión de Cuentas Bancarias

- [ ] 1. Crear Request de validación para cuentas bancarias
  - Implementar BankAccountRequest con reglas de validación robustas
  - Agregar método prepareForValidation para manejar correctamente el campo is_active
  - Incluir mensajes de error personalizados en español
  - _Requisitos: 1.3, 1.4, 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

- [x] 2. Corregir el manejo del campo is_active en formularios





  - Modificar las vistas create.blade.php y edit.blade.php para manejar correctamente el checkbox
  - Agregar campo hidden para asegurar que se envíe un valor cuando el checkbox no está marcado
  - Implementar JavaScript para mejorar la experiencia de usuario del checkbox
  - _Requisitos: 1.3, 2.3, 4.4_

- [ ] 3. Actualizar el controlador BankAccountController
  - Implementar métodos store y update con manejo robusto de errores
  - Agregar validación de límites de plan antes de crear cuentas
  - Implementar método para toggle del estado activo/inactivo
  - Mejorar las respuestas y redirecciones con mensajes apropiados
  - _Requisitos: 1.2, 2.2, 4.1, 4.2_

- [ ] 4. Mejorar el modelo BankAccount
  - Agregar mutadores para encriptar el número de cuenta
  - Implementar accesores para formatear datos sensibles
  - Crear método getFormattedAccountNumber para mostrar números parcialmente ocultos
  - Agregar método getAccountHolderWithDocument para mostrar titular con documento
  - _Requisitos: 3.2, 5.3_

- [ ] 5. Implementar validaciones de seguridad
  - Agregar middleware para verificar permisos de tienda
  - Implementar validación de límites de plan por tipo de suscripción
  - Agregar logging de operaciones críticas para auditoría
  - _Requisitos: 3.4, 5.4_

- [ ] 6. Crear pruebas unitarias para validación
  - Escribir tests para BankAccountRequest incluyendo el caso del checkbox is_active
  - Crear tests para validación de números de cuenta y formatos
  - Implementar tests para límites de plan y restricciones
  - _Requisitos: 1.3, 1.5, 1.6, 6.1, 6.2_

- [ ] 7. Crear pruebas de integración para flujo completo
  - Escribir tests para el flujo completo de creación de cuenta bancaria
  - Implementar tests para edición y actualización de cuentas
  - Crear tests para activación/desactivación de cuentas
  - Agregar tests para eliminación con validaciones de integridad
  - _Requisitos: 1.1, 1.2, 2.1, 2.2, 4.1, 4.2, 5.1, 5.2_

- [ ] 8. Optimizar las vistas y experiencia de usuario
  - Mejorar la validación en tiempo real del formulario con JavaScript
  - Implementar formateo automático del número de cuenta mientras se escribe
  - Agregar confirmaciones más claras para acciones destructivas
  - Mejorar los mensajes de error y éxito en la interfaz
  - _Requisitos: 1.5, 3.3, 4.1, 5.1_

- [ ] 9. Implementar migración de base de datos si es necesaria
  - Verificar estructura actual de la tabla bank_accounts
  - Crear migración para agregar índices de rendimiento si faltan
  - Asegurar que el campo is_active esté correctamente definido como boolean
  - _Requisitos: 6.1, 6.4_

- [ ] 10. Documentar y probar la solución completa
  - Crear documentación de uso para administradores
  - Realizar pruebas manuales del flujo completo
  - Verificar que el error original esté completamente resuelto
  - Probar casos edge como límites de plan y validaciones
  - _Requisitos: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 3.1, 3.4, 4.1, 4.2, 5.1, 5.2_