# Requisitos para Métodos de Pago

## Introducción

Esta funcionalidad permite a los administradores de tiendas configurar diferentes métodos de pago para sus clientes, incluyendo efectivo, transferencia bancaria y datáfono. El sistema debe respetar los límites establecidos según el plan de suscripción del usuario, permitir la configuración detallada de cada método de pago y proporcionar una experiencia de usuario intuitiva tanto para administradores como para clientes durante el proceso de checkout.

## Requisitos

### Requisito 1

**Historia de Usuario:** Como administrador de tienda, quiero configurar los métodos de pago disponibles para mi tienda, para ofrecer opciones de pago convenientes a mis clientes.

#### Criterios de Aceptación

1. CUANDO el administrador acceda a la sección de métodos de pago ENTONCES el sistema DEBERÁ mostrar todos los métodos disponibles (Efectivo, Transferencia Bancaria, Datáfono).
2. CUANDO el administrador active o desactive un método de pago ENTONCES el sistema DEBERÁ actualizar su estado inmediatamente.
3. CUANDO el administrador intente desactivar todos los métodos de pago ENTONCES el sistema DEBERÁ mostrar un error y mantener al menos uno activo.
4. CUANDO el administrador cambie el orden de los métodos mediante drag & drop ENTONCES el sistema DEBERÁ actualizar el campo sort_order en la base de datos.
5. CUANDO el administrador configure un método de pago ENTONCES el sistema DEBERÁ permitir agregar instrucciones personalizadas para ese método.
6. CUANDO el administrador configure el método de efectivo ENTONCES el sistema DEBERÁ permitir especificar si se acepta cambio.
7. CUANDO el administrador configure un método de pago ENTONCES el sistema DEBERÁ permitir especificar si está disponible para pickup, entrega a domicilio o ambos.

### Requisito 2

**Historia de Usuario:** Como administrador de tienda, quiero gestionar mis cuentas bancarias para recibir pagos por transferencia, respetando los límites de mi plan de suscripción.

#### Criterios de Aceptación

1. CUANDO el administrador tenga un plan Explorer ENTONCES el sistema DEBERÁ permitir configurar máximo 1 cuenta bancaria.
2. CUANDO el administrador tenga un plan Master ENTONCES el sistema DEBERÁ permitir configurar máximo 3 cuentas bancarias.
3. CUANDO el administrador tenga un plan Legend ENTONCES el sistema DEBERÁ permitir configurar máximo 5 cuentas bancarias.
4. CUANDO el administrador intente agregar una cuenta bancaria por encima del límite de su plan ENTONCES el sistema DEBERÁ mostrar un mensaje indicando el límite alcanzado.
5. CUANDO el administrador agregue una cuenta bancaria ENTONCES el sistema DEBERÁ solicitar: banco, tipo de cuenta, número de cuenta, titular, documento (opcional) y estado.
6. CUANDO el administrador ingrese un número de cuenta ENTONCES el sistema DEBERÁ validar que contenga solo números y tenga entre 10 y 20 dígitos.
7. CUANDO un administrador baje de plan y tenga más cuentas bancarias que las permitidas por el nuevo plan ENTONCES el sistema DEBERÁ inactivar las cuentas excedentes.

### Requisito 3

**Historia de Usuario:** Como cliente de la tienda, quiero seleccionar un método de pago durante el checkout, para completar mi compra de manera conveniente.

#### Criterios de Aceptación

1. CUANDO un cliente acceda al checkout ENTONCES el sistema DEBERÁ mostrar solo los métodos de pago activos configurados por el administrador.
2. CUANDO un cliente seleccione el método de pago en efectivo ENTONCES el sistema DEBERÁ permitir especificar si necesita cambio y el monto.
3. CUANDO un cliente seleccione el método de transferencia bancaria ENTONCES el sistema DEBERÁ mostrar todas las cuentas bancarias activas configuradas.
4. CUANDO un cliente seleccione transferencia bancaria ENTONCES el sistema DEBERÁ proporcionar la opción de subir un comprobante de pago (opcional).
5. CUANDO un cliente seleccione datáfono y la opción de entrega sea a domicilio ENTONCES el sistema DEBERÁ mostrar un mensaje indicando que este método solo está disponible para pickup (si así está configurado).
6. CUANDO un cliente acceda al checkout ENTONCES el sistema DEBERÁ preseleccionar el método de pago por defecto configurado por el administrador.

### Requisito 4

**Historia de Usuario:** Como desarrollador del sistema, quiero implementar una estructura de datos robusta para los métodos de pago, para garantizar la integridad y escalabilidad de la funcionalidad.

#### Criterios de Aceptación

1. CUANDO se implemente la base de datos ENTONCES el sistema DEBERÁ crear las tablas payment_methods, bank_accounts y payment_method_config con los campos especificados.
2. CUANDO se guarde un método de pago ENTONCES el sistema DEBERÁ almacenar: tipo, nombre, estado activo, orden de clasificación, instrucciones y tienda asociada.
3. CUANDO se guarde una cuenta bancaria ENTONCES el sistema DEBERÁ almacenar: método de pago asociado, nombre del banco, tipo de cuenta, número de cuenta, titular, número de documento, estado activo y tienda asociada.
4. CUANDO se guarde la configuración de métodos de pago ENTONCES el sistema DEBERÁ almacenar: tienda asociada, disponibilidad de cambio en efectivo y método de pago por defecto.
5. CUANDO se implemente la tabla payment_method_config ENTONCES el sistema DEBERÁ crear una sola fila por tienda.