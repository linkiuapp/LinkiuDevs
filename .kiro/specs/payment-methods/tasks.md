# Plan de Implementación - Métodos de Pago

## Tareas de Implementación

- [x] 1. Configuración inicial y migraciones




























  - Crear migraciones para las nuevas tablas de base de datos
  - _Requisitos: 4.1, 4.2, 4.3, 4.4_

- [ ] 2. Implementar modelos base


  - [x] 2.1 Crear modelo PaymentMethod


    - Definir atributos, relaciones y métodos básicos
    - _Requisitos: 4.2_
  
  - [x] 2.2 Crear modelo BankAccount















    - Definir atributos, relaciones y métodos básicos
    - _Requisitos: 4.3_
  -

  - [x] 2.3 Crear modelo PaymentMethodConfig










    - Definir atributos, relaciones y métodos básicos
    - _Requisitos: 4.4, 4.5_

- [x] 3. Implementar servicios de negocio












  - [x] 3.1 Crear PaymentMethodService




    - Implementar métodos para gestionar métodos de pago
    - _Requisitos: 1.1, 1.2, 1.3, 1.4_
  
  - [x] 3.2 Crear BankAccountService


    - Implementar métodos para gestionar cuentas bancarias y límites por plan
    - _Requisitos: 2.1, 2.2, 2.3, 2.4, 2.7_

- [-] 4. Implementar controladores para administración


  - [x] 4.1 Crear PaymentMethodController














    - Implementar métodos index y show
    - _Requisitos: 1.1_
  
  - [x] 4.2 Implementar métodos create y store en PaymentMethodController





    - Incluir validaciones y manejo de errores
    - _Requisitos: 1.5, 1.6, 1.7_
  
  - [x] 4.3 Implementar métodos edit y update en PaymentMethodController








    - Incluir validaciones y manejo de errores
    - _Requisitos: 1.2, 1.5, 1.6, 1.7_
  
  - [x] 4.4 Implementar método updateOrder en PaymentMethodController


























    - Manejar drag & drop para ordenar métodos
    - _Requisitos: 1.4_
  
  - [x] 4.5 Implementar método toggleActive en PaymentMethodController




    - Validar que al menos un método quede activo
    - _Requisitos: 1.2, 1.3_
  
  - [x] 4.6 Crear BankAccountController












    - Implementar métodos index, create y store
    - _Requisitos: 2.5, 2.6_
  
  - [x] 4.7 Implementar métodos edit, update y destroy en BankAccountController











    - Incluir validaciones y manejo de errores
    - _Requisitos: 2.5, 2.6_
  - [x] 4.8 Implementar validación de límites por plan en BankAccountController



























































  - [ ] 4.8 Implementar validación de límites por plan en BankAccountController

    - Verificar límites al crear nuevas cuentas
    - _Requisitos: 2.1, 2.2, 2.3, 2.4_

- [ ] 5. Implementar vistas de administración
  - [x] 5.1 Crear vista index para métodos de pago














    - Mostrar listado de métodos con opciones de configuración
    - _Requisitos: 1.1, 1.2_
  
  - [x] 5.2 Implementar funcionalidad drag & drop en vista index






    - Permitir reordenar métodos de pago
    - _Requisitos: 1.4_
  
  - [x] 5.3 Crear formularios para configurar métodos de pago







    - Incluir campos para instrucciones, disponibilidad y opciones específicas
    - _Requisitos: 1.5, 1.6, 1.7_
  
  - [ ] 5.4 Crear vista index para cuentas bancarias



    - Mostrar listado de cuentas con indicador de límite por plan
    - _Requisitos: 2.1, 2.2, 2.3, 2.4_
  
  - [x] 5.5 Crear formularios para gestionar cuentas bancarias




    - Incluir validaciones para número de cuenta y campos obligatorios
    - _Requisitos: 2.5, 2.6_

- [ ] 6. Implementar componentes para checkout
  - [ ] 6.1 Crear componente de selección de método de pago




    - Mostrar solo métodos activos y aplicar filtros según tipo de entrega
    - _Requisitos: 3.1, 3.5, 3.6_
  
  - [ ] 6.2 Implementar opciones específicas para método efectivo
    - Permitir especificar si necesita cambio y el monto
    - _Requisitos: 3.2_
  
  - [ ] 6.3 Implementar opciones específicas para método transferencia
    - Mostrar cuentas bancarias activas y opción de subir comprobante
    - _Requisitos: 3.3, 3.4_
  
  - [ ] 6.4 Implementar validaciones para método datáfono
    - Verificar disponibilidad según tipo de entrega
    - _Requisitos: 3.5_

- [ ] 7. Implementar políticas y manejo de límites
  - [x] 7.1 Crear PaymentMethodPolicy




    - Implementar reglas de acceso y validaciones
    - _Requisitos: 1.3_
  
  - [ ] 7.2 Crear BankAccountPolicy




    - Implementar reglas de acceso y validación de límites por plan
    - _Requisitos: 2.1, 2.2, 2.3, 2.4_
  
  - [ ] 7.3 Implementar manejo de bajada de plan




    - Inactivar cuentas excedentes automáticamente
    - _Requisitos: 2.7_

- [ ] 8. Implementar pruebas
  - [ ] 8.1 Crear pruebas unitarias para modelos
    - Verificar relaciones y atributos
    - _Requisitos: 4.1, 4.2, 4.3, 4.4_
  
  - [ ] 8.2 Crear pruebas para servicios
    - Verificar lógica de negocio y validaciones
    - _Requisitos: 1.3, 2.4, 2.7_
  
  - [ ] 8.3 Crear pruebas de integración para controladores
    - Verificar operaciones CRUD y manejo de errores
    - _Requisitos: 1.2, 1.3, 2.4, 2.6_
  
  - [ ] 8.4 Crear pruebas para componentes de checkout
    - Verificar comportamiento según configuración
    - _Requisitos: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_