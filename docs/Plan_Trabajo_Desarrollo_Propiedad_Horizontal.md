# Plan de trabajo: plataforma de propiedad horizontal

**Base del análisis:** especificación funcional y técnica, versión 1.0 (octubre de 2026), y código presente en este repositorio al momento de la revisión.  
**Estado:** Fase 1 iniciada por instrucción del usuario (2026-10-09), con regresión de autenticación publicada y primera base de esquema implementada. El usuario aclaró que la aplicación inicia sin datos y autorizó crear el esquema desde cero, sin migrar ni cargar los datos existentes; el conjunto demo queda fuera y sin alteraciones. Las migraciones construyen tablas vacías: se retiró de la migración inicial la creación de roles, perfil y cuenta `Admin`, y la nueva migración tampoco siembra tenants/membresías. No existe aún un flujo seguro para aprovisionar la primera cuenta. El esquema nuevo se aplicó y probó en el datasource `test`; no se aplicó a desarrollo ni a la base demo. La membresía por sí sola no concede acceso a copropiedades u operaciones: falta completar el alcance a copropiedad, el contexto tenant y RBAC antes de exponer datos. El CI remoto de `ee5c485` pasó ([ejecución #1](https://github.com/7iddr2-art/Alfil_Horizontal/actions/runs/37978202455)); la nueva migración requiere su ejecución CI después de publicarse. Siguen pendientes reglas profesionales contables, jurídicas y tributarias, segregación RBAC y estimación con el equipo. La configuración del banco real y SLA comercial se difirió según D-004/D-009.

## 1. Resumen ejecutivo

El repositorio es una base CakePHP con módulos CRUD iniciales para propiedades horizontales, viviendas, copropietarios, residentes, parqueaderos, usuarios y catálogos de roles/perfiles. Incluye autenticación de sesión/formulario y auditoría de accesos. Esos componentes sirven como punto de partida, pero no constituyen todavía el SaaS multiempresa ni los flujos financieros, contables y comunitarios descritos en la especificación.

La brecha de mayor riesgo es estructural: no hay evidencia de aislamiento multi-tenant ni de autorización efectiva por rol, recurso y copropiedad. Deben resolverse antes de exponer información de varios clientes o desarrollar autoservicio, pagos, documentos e informes. Se confirmó Oracle MySQL Server, base compartida con `tenant_id`, contabilidad completa, recaudo manual/importación CSV, aplazar factura electrónica y hardware de portería, y gestión manual de facturación SaaS. También se aceptaron metas provisionales de rendimiento y recuperación sujetas a validación. Continúan pendientes el modelo detallado de tenancy, las reglas funcionales validadas por profesionales y decisiones de operación comercial.

Se recomienda construir por fases y de extremo a extremo, empezando por descubrimiento y fundamentos de seguridad/datos, continuando por el núcleo financiero y contable y luego por operación comunitaria, portal e integraciones. Las fechas y el esfuerzo total deben estimarse al refinar el backlog y confirmar equipo, alcance e integraciones.

## 2. Situación actual del software

| Área | Evidencia disponible | Evaluación frente a la especificación |
|---|---|---|
| Plataforma y tecnología | PHP 8.2+, CakePHP 5.4; autenticación y migraciones de CakePHP declaradas en `composer.json`. | Base técnica compatible con la arquitectura propuesta de monolito modular. |
| Persistencia | `config/app.php` configura el driver MySQL de CakePHP. XAMPP trae MariaDB 10.4.32. Se instaló MySQL Community Server 8.4.9 de forma local por usuario y se verificó en `127.0.0.1:3307`, con `utf8mb4` y `utf8mb4_0900_ai_ci`. | El destino elegido es Oracle MySQL Server. Se inicia manualmente con un script por usuario porque la sesión no tiene permisos de administrador para registrar un servicio de Windows. No se creó aún una base de aplicación ni esquema. CI/producción deben fijar y probar versiones compatibles. |
| Configuración local de la aplicación | El `config/app_local.php` ignorado por Git configura `default` en la base `horizontal` del MariaDB de XAMPP (puerto 3306). El datasource `test` usa actualmente una URL SQLite, por lo que no llega al MySQL/MariaDB configurado en sus otras propiedades. | La prueba no compartía efectivamente la misma base física; aun así no prueba compatibilidad de migraciones con MySQL y la configuración es ambigua. Con autorización del propietario se prepararán bases MySQL separadas para `default` y `test`, preservando `horizontal`. |
| Estructura de copropiedad | CRUDs y modelos para propiedad horizontal, viviendas, parqueaderos y asignaciones; migraciones en `config/Migrations/`. | Parcial: las migraciones revisadas no vinculan viviendas/parqueaderos a una copropiedad ni definen `tenant_id`; códigos de vivienda y parqueadero tienen unicidad global. Faltan jerarquía física, activos/zonas comunes y coeficientes con historial/vigencia. |
| Personas y relaciones | CRUDs para copropietarios, residentes y relación vivienda-copropietario. | Parcial: sirve como catálogo inicial; validar si el modelo actual conserva roles, vigencias e historial requeridos para cambios de titularidad. |
| Acceso | Autenticación con sesión/formulario y contraseñas hasheadas; CRUDs de usuarios, roles y perfiles. | Parcial: no se encontró autorización efectiva basada en permisos para las acciones, recursos y alcance por copropiedad que exige la especificación. |
| Auditoría | Registro y consulta de accesos/sesiones, con exportación CSV. | Parcial: no equivale a la auditoría de negocio de aprobaciones, movimientos, reversiones, cambios de permisos, exportaciones sensibles y acceso a documentos. |
| Parqueaderos y pagos | Catálogo, asignaciones y pagos por período. | Funcionalidad acotada al parqueadero; no es el ciclo de cuotas, cartera, aplicación de recaudos y conciliación de la copropiedad. |
| Finanzas, contabilidad y presupuesto | No se encontró evidencia de liquidaciones, cartera general, comprobantes, cierres ni presupuesto. | Brecha completa respecto a RF-004–RF-009. |
| Operación comunitaria | No se encontró evidencia de PQR, reservas, asambleas, mantenimiento, visitantes o portal por perfil. | Brecha completa respecto a RF-010–RF-014. |
| Documentos e integraciones | No se identificó almacenamiento documental privado ni adaptadores bancarios/de pagos/facturación/control de acceso. | Brecha respecto a RF-015–RF-016 y a las integraciones previstas. |
| API y operación | No se identificó API versionada `/api/v1`, colas de trabajos ni proceso de despliegue/observabilidad específico del producto. | Brecha técnica por resolver antes de ampliar el producto y conectar sistemas externos. |
| Pruebas | Hay pruebas de arranque/páginas y de auditoría de acceso; cobertura de flujos de negocio todavía limitada. | Hace falta una estrategia automatizada para autorización, multi-tenant, finanzas, importaciones, integraciones y regresión. |

**Alcance de esta evaluación:** revisión estática de la estructura, configuración y archivos identificados; no se ejecutaron pruebas, no se validó un despliegue y la ausencia de evidencia en el repositorio no demuestra que no exista trabajo externo o no versionado.

## 3. Matriz de cobertura funcional

Estados: **Parcial** = existe una base relacionada, pero no cumple todo el requisito; **Pendiente** = no se encontró evidencia suficiente de implementación.

| Requisito | Estado | Base existente / brecha principal |
|---|---|---|
| RF-001 Administración SaaS | Pendiente | No se encontró gestión de clientes SaaS, planes, límites, suscripciones ni ciclo de suspensión/reactivación. |
| RF-002 Estructura de copropiedad | Parcial | Existen propiedad horizontal, viviendas y parqueaderos; completar estructura física, activos/zonas comunes y vigencias/historial. |
| RF-003 Personas y titularidad | Parcial | Existen copropietarios, residentes y asociación con viviendas; completar persona natural/jurídica, múltiples roles y vigencia histórica. |
| RF-004 Cuotas y conceptos de cobro | Pendiente | No se encontró motor configurable de conceptos, reglas, previsualización ni generación masiva idempotente. |
| RF-005 Cartera e intereses | Pendiente | No se encontró cartera por unidad/concepto, acuerdos, antigüedad, mora parametrizada ni certificados. |
| RF-006 Recaudos y conciliación | Parcial muy acotado | Hay pagos de parqueadero; falta recaudo general, aplicación a cargos, anticipos/devoluciones, conciliación e idempotencia. |
| RF-007 Compras y cuentas por pagar | Pendiente | No se encontraron proveedores, compras, aprobaciones, soportes ni cuentas por pagar. |
| RF-008 Contabilidad | Pendiente | No se encontró plan de cuentas, comprobantes balanceados, cierres, reversión ni estados financieros. |
| RF-009 Presupuesto y fondos | Pendiente | No se encontraron presupuestos, ejecución, compromisos, saldos ni alertas. |
| RF-010 Asambleas y consejo | Pendiente | No se encontraron convocatorias, poderes, quórum, votaciones, actas ni compromisos. |
| RF-011 PQR y comunicaciones | Pendiente | No se encontró radicación, asignación, seguimiento, vencimientos ni respuestas. |
| RF-012 Reservas de zonas comunes | Pendiente | No se encontró catálogo de recursos, disponibilidad ni prevención de solapamientos. |
| RF-013 Mantenimiento y activos | Pendiente | No se encontraron inventario de activos, planes ni órdenes preventivas/correctivas. |
| RF-014 Seguridad y portería | Pendiente | Los parqueaderos no cubren visitantes, vehículos, paquetes, novedades ni autorización de ingreso. |
| RF-015 Documentos y contratos | Pendiente | No se encontró repositorio privado, clasificación, versiones, permisos ni control de vencimientos. |
| RF-016 Notificaciones | Pendiente | No se encontró flujo funcional de notificaciones, preferencias, reintentos o seguimiento de entrega. |
| RF-017 Informes y analítica | Parcial muy acotado | Hay exportación CSV de auditoría; faltan informes de cartera, tesorería, contabilidad, presupuesto y operación. |
| RF-018 Auditoría y administración | Parcial | Hay auditoría de accesos; falta auditoría integral de negocio con consultas y exportación autorizadas. |

## 4. Plan por fases

Las dependencias indicadas son bloqueantes: la fase posterior no debe habilitar datos reales de clientes hasta satisfacer los criterios de salida previos. Cada entrega debe incluir migración versionada, autorización, auditoría, pruebas y documentación aplicables; no se deben posponer como “hardening final”.

### Fase 0 — Descubrimiento, alcance y decisiones

**Objetivo:** convertir el documento base en un backlog estimable y evitar decisiones incompatibles con el modelo operativo.

**Trabajo**
- Talleres con administración, tesorería, contabilidad, consejo, portería, asesoría jurídica y producto.
- Precisar alcance de primera versión y reglas de liquidación, coeficientes, redondeo, mora, pagos, cierres, aprobaciones y anulaciones.
- Definir perfiles, permisos por acción/recurso, alcance SaaS/copropiedad/unidad y segregación de funciones.
- Validar el modelo de datos actual y plan de transición; configurar Oracle MySQL Server con versión, motor de almacenamiento, charset/collation y capacidades operativas acordadas antes de ampliar el esquema. **Avance:** MySQL Community Server 8.4.9 está configurado localmente en loopback, puerto 3307; la configuración local separa los datasources `default` y `test` en bases distintas. Las siete migraciones y la suite PHPUnit (10 pruebas, 41 aserciones) pasan en test. El ADR técnico de tenancy y una clasificación inicial de tablas están documentados y el ADR recibió aprobación de arquitectura; quedan el DER detallado y la primera ejecución remota de CI.
- Inventariar sin cambios los datos de `horizontal` en el entorno anterior. **Decisión:** el conjunto local es demo/de prueba y se excluirá de migraciones futuras; permanecerá intacto en MariaDB. Separar la base de pruebas antes de ejecutar el migrador de tests.
- Confirmar con el equipo la estrategia para que desarrollo y pruebas usen bases separadas sin perder ni sobreescribir datos existentes.
- Acordar importación/migración inicial, retención documental, respaldo/recuperación y modelo de precios; tomar las metas no funcionales del PDF como objetivos de piloto sujetos a pruebas y análisis de costos, no como garantía comercial.
- Crear matriz de aplicabilidad legal y tributaria versionada con revisión profesional.

**Entregables y salida**
- Backlog priorizado con trazabilidad RF/HU, reglas de aceptación y dependencias.
- Decisiones registradas para las preguntas abiertas de la sección 8 y plan para verificar las decisiones técnicas ya tomadas.
- Diagramas de contexto, dominios, tenancy y flujos financieros; modelo de datos objetivo y estrategia de migración.
- Criterios de aceptación aprobados por responsables funcionales; estimación de esfuerzo basada en ese backlog.

#### Seguimiento de Fase 0

| ID | Actividad / entregable | Estado | Dependencia / condición para cerrar |
|---|---|---|---|
| F0-01 | Alcance y decisiones D-001–D-010 | Parcialmente completado | Las diez decisiones están registradas. Se acordó usar un banco y CSV ficticios para pruebas durante desarrollo y posponer la configuración del banco real hasta terminar las fases de desarrollo. Las metas técnicas siguen provisionales; el SLA comercial se definirá después de medir costos/infraestructura y antes del piloto. Siguen pendientes las reglas contables, tributarias, de mora, quórum y conservación aprobadas por responsables. |
| F0-02 | Entorno MySQL objetivo | Configuración local y CI remoto verificados | MySQL Community Server 8.4.9 corre localmente en puerto 3307; los datasources `default` y `test` usan bases separadas y las siete migraciones pasan en test. El workflow crea esquemas/usuarios aislados, instala PHP 8.2 y ejecuta lint, PHPCS del test actualizado y PHPUnit. La ejecución remota del commit `ee5c485` terminó exitosamente ([CI #1](https://github.com/7iddr2-art/Alfil_Horizontal/actions/runs/37978202455)); localmente también pasan `composer test` (10 pruebas, 41 aserciones), PHPCS enfocado, lint PHP y `composer validate`. El PHPCS global detecta incumplimientos preexistentes y no forma parte del bloqueo de este cambio. |
| F0-03 | Diseño lógico multi-tenant | ADR aprobado por arquitectura; base objetivo en preparación desde cero | El usuario confirmó que el ADR está aprobado y autorizó crear el esquema nuevo desde cero, sin mover ni cargar datos existentes. El diseño detallado de relaciones/alcance y RBAC debe completarse antes de habilitar acceso a datos de negocio. |
| F0-04 | Inventario y mapeo de datos actuales | Completado; conjunto demo excluido | El usuario confirmó que el conjunto actual es demo/de prueba y no se migrará. El esquema de origen no contiene `tenant_id`, `propiedades_horizontales` está vacía y `viviendas` no tiene FK a esa tabla. No copiar, modificar ni borrar el origen. La aplicación destino se construye desde un esquema nuevo sin carga de registros del origen. |
| F0-05 | Separar bases de desarrollo y prueba | Configurado; validación inicial completada | `config/app_local.php` (ignorado por Git) apunta `default` a `alfil_horizontal_dev` y `test` a `alfil_horizontal_test`, ambas en MySQL 8.4 con cuentas separadas y limitadas a su esquema. PHPUnit aplicó las siete migraciones sólo en `test`; `default` conserva únicamente la tabla vacía `cake_migrations` y se verificó que las tablas del prototipo fueron eliminadas. La base anterior `horizontal` en MariaDB/XAMPP no se modificó. La suite completa termina en verde. |
| F0-06 | Matriz de roles, permisos y alcance | Base aprobada por producto; detalles pendientes | Se aprobó la matriz inicial como base para políticas del servidor. Delegaciones, suplencias, umbrales monetarios, doble aprobación y retención operativa requieren definición con los responsables; después se implementan y prueban permisos permitidos/denegados antes de habilitar las funciones correspondientes. |
| F0-07 | Reglas de negocio y cumplimiento | Bloqueante para reanudar; pendiente según confirmación del usuario (2026-10-09) | Aún no se dispone de reglas o concepto escrito de contador ni de asesoría jurídica/tributaria. Validar con contador, asesor jurídico/tributario y responsables de la copropiedad; parametrizar diferencias por copropiedad, sin declarar cumplimiento automático del sistema. |
| F0-08 | Historias, criterios de aceptación y dependencias | Backlog inicial y datos CSV sintéticos preparados; priorización funcional pendiente | El backlog inicial de abajo establece orden, aceptación y dependencias para cerrar descubrimiento y comenzar Fase 1. Producto debe confirmar prioridad y asignar responsables; el equipo estimará después de validar alcance y arquitectura. La integración debe admitir mapeo configurable; no acoplarla a un banco real antes de la configuración posterior a las fases de desarrollo. |
| F0-09 | Estimación y aprobación de alcance | Tallas relativas preparadas; estimación de equipo bloqueante para reanudar | Las tallas S/M/L/XL iniciales están propuestas para Fase 0/Fase 1; no equivalen a puntos ni duración. Recalibrar y estimar con el equipo real una vez asignados integrantes, capacidad, velocidad, alcance funcional y dependencias. |

#### Preparación de revisiones profesionales pendientes

Este registro prepara la revisión con los especialistas; no constituye concepto jurídico, tributario ni contable ni completa reglas del producto. No codificar reglas controvertidas hasta documentar aprobación por responsable.

| Área | Definiciones que debe aportar/revisar el especialista | Evidencia de cierre |
|---|---|---|
| Contabilidad | Plan de cuentas aplicable, reconocimiento de ingresos/gastos, períodos, cierres/reaperturas, asientos de reversión, tratamiento de fondos, trazabilidad entre operación y comprobante y reportes requeridos. | Profesional identificado; versión de reglas aprobada; ejemplos de asientos balanceados y casos de ajuste/cierre firmados o aceptados por escrito. |
| Cartera y mora | Inicio del cómputo, base y método de cálculo, calendario, redondeo, topes o excepciones aplicables, pagos parciales/anticipados, imputación entre conceptos, acuerdos y reversión. | Casos de prueba con fechas/importes de frontera y resultado esperado aprobado; fuente y vigencia de cada regla registradas por responsable competente. |
| Tributación y documentos | Obligaciones aplicables a cada tipo de entidad/operación, documento que corresponde emitir, retenciones y condiciones para integrar facturación electrónica. | Concepto profesional con alcance, supuestos y fecha; decisión de qué documentos genera/no genera el producto. |
| Gobierno de copropiedad | Convocatorias, delegaciones/poderes, quórum, coeficientes, votaciones, mayorías, actas y conservación. | Reglas por escenario, fuente aplicable y casos límite validados por asesor y responsables de la copropiedad; parametrización separada de la validación legal. |
| Privacidad y retención | Finalidades, acceso por rol, minimización, plazos de conservación y borrado/bloqueo para personas, auditoría, visitantes, PQR y documentos; atención de solicitudes. | Matriz de datos/finalidades/retención aprobada por asesoría competente y proceso operativo asignado; revisión de controles y excepciones de conservación. |
| Continuidad y SLA | Capacidad e infraestructura, costos, ventanas de mantenimiento, disponibilidad medible, soporte, RPO/RTO y remedios contractuales si aplican. | Resultados de carga/restauración y análisis de costos; SLA comercial aprobado y separado de los objetivos técnicos provisionales de D-009. |

#### Inventario de esquema y relaciones de la base anterior `horizontal`

Consultas de sólo lectura ejecutadas con autorización: se consultaron nombres de tablas, conteos `COUNT(*)`, nombres/tipos de columnas y claves foráneas. Tras autorización adicional se consultaron únicamente IDs de filas y columnas ID/FK para ver el grafo de relaciones; no se seleccionaron nombres, documentos, contactos, notas, contraseñas ni otros campos personales. No se creó respaldo y no se alteró la base. Las 16 tablas reportaron motor InnoDB.

Hallazgos de estructura: no existe `tenant_id`; `propiedades_horizontales` tiene cero filas; `viviendas` no contiene referencia a copropiedad; la única vivienda y el único vínculo vivienda-copropietario se asocian entre sí por IDs. Personal administrativo y pólizas referencian propiedades horizontales, pero ambas tablas están vacías. Hay 3 usuarios, 1 reunión y 8 auditorías de acceso; sus claves relacionan algunos registros con usuarios, pero no con tenant/copropiedad. Residentes, parqueaderos, asignaciones, pagos, personal administrativo y pólizas aparecen sin filas en este inventario. El usuario confirmó que el conjunto representa una copropiedad, pero es demo/de prueba y queda excluido de una futura migración; no se intentará completar un mapeo para su traslado.

| Tabla | Filas |
|---|---:|
| `access_audits` | 8 |
| `asignaciones_parqueaderos` | 0 |
| `cake_migrations` | 7 |
| `copropietarios` | 1 |
| `meetings` | 1 |
| `pagos_parqueaderos` | 0 |
| `parqueaderos` | 0 |
| `personal_administraciones` | 0 |
| `polizas_aseguramiento` | 0 |
| `profiles` | 3 |
| `propiedades_horizontales` | 0 |
| `residentes` | 0 |
| `roles` | 2 |
| `users` | 3 |
| `viviendas` | 1 |
| `vivienda_copropietarios` | 1 |

#### Diseño lógico multi-tenant aprobado como objetivo (implementación pendiente)

- **Tenant:** representa al cliente comercial SaaS. Suscripción, plan, límites y estado pertenecen a este nivel.
- **Copropiedad:** pertenece a un tenant; un tenant puede tener una o varias copropiedades. Se conserva la entidad de negocio existente de propiedad horizontal, pero debe vincularse al tenant.
- **Usuario y membresía:** mantener identidad/autenticación global separada de membresías; una misma cuenta puede pertenecer a más de un tenant. Cada membresía asigna permisos independientes en un tenant y puede limitarse a una o varias copropiedades. El tenant activo debe elegirse entre membresías autorizadas y verificarse en cada petición; no convertir roles de un tenant en privilegios globales.
- **Datos operativos:** cada fila de negocio lleva `tenant_id` obligatorio y, cuando aplique, `copropiedad_id` obligatorio. En tablas compartidas de catálogo global justificadas explícitamente, documentar excepción; no usar excepciones para evitar controles de aislamiento.
- **Integridad de pertenencia:** relaciones hacia copropiedad deben impedir que una fila de un tenant apunte a una copropiedad de otro tenant; usar claves únicas y foráneas compuestas (`tenant_id`, identificador de copropiedad) donde la versión/configuración de MySQL lo permita.
- **Resolución y consultas:** el tenant se deriva de la identidad autenticada y membresía autorizada. Servicios/repositories aplican el contexto; nunca confiar en `tenant_id` libre enviado por formulario, URL, API, job, caché, archivo o webhook.
- **Identificadores únicos:** revisar índices globales actuales como códigos de vivienda/parqueadero y unicidad de personas; decidir cuáles deben pasar a unicidad por tenant/copropiedad, preservando integridad histórica.
- **Soporte SaaS:** cualquier acceso excepcional a datos del cliente exige autorización explícita, motivo, duración limitada y evento de auditoría; el soporte no obtiene acceso implícito a movimientos/documentos.

#### Política de datos y esquema desde cero

El usuario aclaró el 2026-10-09 que el proyecto destino empieza sin datos y autorizó crear su esquema desde cero, pero no migrar ni cargar ningún registro del conjunto demo/origen. Las migraciones versionadas de esquema para construir el producto están permitidas bajo esa condición; deben crear tablas vacías y ejecutarse en la base destino correspondiente. Mantener intacta la base origen y no copiar registros de prueba.

La autorización no implica acceso seguro ya implementado: la membresía no concede permisos por sí sola. No exponer copropiedades ni datos operativos hasta completar el alcance tenant/copropiedad, la política de autorización y las pruebas negativas. Mantener default-deny durante el desarrollo.

#### Matriz base de acceso (aprobada por producto; parametrización pendiente)

El modelo es RBAC con alcance jerárquico tenant → copropiedad → unidad/recurso. Cada permiso aplica a una membresía concreta, no a la identidad global. En todas las combinaciones no concedidas explícitamente se deniega el acceso; los identificadores de tenant/unidad recibidos del cliente nunca amplían el alcance. “Aprobar”, “contabilizar”, “anular” y “exportar” son permisos distintos de editar o consultar.

| Perfil | Consultar | Crear/editar | Aprobar / contabilizar / anular | Exportar / administrar | Alcance y límites |
|---|---|---|---|---|---|
| Superadministrador SaaS | Metadatos de tenant, plan y estado. | Sin operar transacciones o documentos del cliente. | Soporte excepcional con autorización, motivo, vencimiento y auditoría; administra tenants/planes, no roles de copropiedad. | Exportación de datos del cliente sólo bajo procedimiento excepcional auditado. | Sin lectura rutinaria de datos financieros/personales; soporte no concede acceso implícito. |
| Administrador de copropiedad | Maestros, personas, operación e informes de sus copropiedades. | Gestiona datos operativos y usuarios locales dentro de su alcance. | Aprobaciones sólo con permiso separado y límites autorizados; sin contabilización, pago o anulación automática por el rol. | Exportaciones sensibles autorizadas y auditadas. | Nunca fuera de copropiedades asignadas. |
| Contador | Saldos, comprobantes, cierres, conciliación e informes de sus copropiedades. | Mantiene configuración contable y prepara asientos. | Contabilizar/cerrar/reabrir/anular sólo con permisos específicos y período/reglas aprobados. | Exportes contables limitados a su alcance y auditados. | Sin gestión SaaS ni de credenciales; reabrir período requiere permiso independiente. |
| Tesorero | Cargos, recaudos, pagos, caja y bancos asignados. | Registra pagos, conciliaciones y propuestas de desembolso. | Desembolso/aprobación sujetos a control dual y umbrales definidos; no contabiliza ni modifica períodos cerrados. | Exportación bancaria autorizada y auditada. | Sólo copropiedades asignadas; impedir autoaprobación cuando aplique doble control. |
| Consejo / revisor fiscal | Informes y evidencias expresamente habilitados. | Ninguna modificación de operación. | Sólo lectura; exportación sensible autorizada y auditada. | Exportación con filtros/alcance explícitos. | Sin acceso administrativo por defecto. |
| Propietario | Estado de cuenta y documentos de unidades con vínculo vigente. | Actualiza datos de contacto permitidos o presenta solicitudes. | Sin aprobar cargos/pagos ni consultar unidades ajenas. | Descargas de documentos autorizados, registradas. | Datos privados de residentes sólo con fundamento y permiso explícitos. |
| Residente/arrendatario | PQR, reservas, visitantes y comunicaciones de su vínculo vigente. | Crea solicitudes y administra reservas propias según política. | Sin acceso financiero automático por el solo vínculo residencial. | Sin exportación masiva. | No consulta otros hogares ni historiales ajenos. |
| Portería/vigilancia | Datos mínimos de visitantes, paquetes y novedades necesarios para el turno. | Registra ingresos/novedades de su copropiedad y turno. | Sin acceso contable ni capacidad de aprobar movimientos financieros. | Sin exportación masiva; cualquier reporte limitado y auditado. | Retención limitada al plazo operativo autorizado; minimizar PII. |
| Proveedor/contratista | Órdenes y documentos que le fueron asignados y están vigentes. | Actualiza entregables expresamente habilitados. | Sin aprobar pagos, acceder a contabilidad o modificar órdenes aprobadas. | Descarga sólo de documentos asignados. | Sin búsqueda global de proveedores, unidades o documentos ni datos de residentes. |

La matriz está aprobada como base funcional, pero no constituye autorización implementada. Producto y responsables deben resolver delegaciones y suplencias, segregación de funciones, umbrales monetarios, suspensión de membresías y retención de datos de portería. Las pruebas de autorización deben cubrir como mínimo acceso cruzado entre tenants, copropiedades no asignadas, unidades ajenas, membresía revocada, exportación, aprobación y reintento de tareas en segundo plano.

#### Backlog inicial de cierre de Fase 0 y arranque de Fase 1

Prioridad propuesta: **P0** bloquea decisiones seguras de modelo/datos; **P1** requerida para iniciar una implementación de Fase 1; **P2** necesaria antes del piloto. Talla relativa inicial: **S** = cambio acotado; **M** = una historia con varios componentes o pruebas; **L** = varios flujos/componentes o validación externa; **XL** = alcance amplio con dependencias de varias áreas. No equivale a puntos, jornadas ni fecha y deberá recalibrarse con el equipo. “Responsable” indica el rol que debe aportar o aprobar, todavía sin persona asignada.

| ID / prioridad | Talla | Historia / resultado verificable | Criterio de aceptación | Dependencias | Responsable |
|---|---|---|---|---|---|
| F0-B01 / P0 | S | Formalizar el tratamiento del conjunto demo actual. | Decisión registrada: excluir de futuras migraciones todo el conjunto actual de `horizontal`; no copiarlo ni borrarlo. Cualquier dataset real futuro requiere inventario, respaldo, mapeo tenant/copropiedad confirmado por su dueño y autorización independiente. | F0-04. | Producto + dueño de datos |
| F0-B02 / P0 | L | Aprobar reglas de negocio contables, de mora y tributarias. | Reglas, ejemplos límite, redondeos, vigencias y responsable profesional quedan documentados; no se habilitan automatizaciones que contradigan reglas aún no aprobadas. | F0-01; D-002 y D-005. | Contador + asesor jurídico/tributario |
| F0-B03 / P1 | M | Preparar contrato genérico de importación con un banco ficticio de pruebas. | Dataset sintético documentado y reproducible cubre abonos, débitos, fechas inválidas, duplicados y referencias; el parser/mapeo configurable se prueba sin afirmar compatibilidad con un banco real. Selección del banco y ajuste de columnas reales se posponen hasta terminar las fases de desarrollo. | D-003/D-004; historias del importador en Fase 3. | Desarrollo + QA; tesorería valida al configurar banco real |
| F0-B04 / P1 | M | Aprobar matriz RBAC y segregación de funciones. | Cada perfil tiene alcance y permisos por acción; delegaciones, doble aprobación, umbrales, revocación y exportación sensible reciben aprobación funcional; se documentan casos negativos. | F0-03; matriz base aprobada. | Producto + administradores + contabilidad |
| F0-B05 / P1 | M | Registrar ADR de tenancy y restricciones de MySQL. | **ADR de dirección aprobado por arquitectura** según confirmación del usuario el 2026-10-09. **Prototipo completado:** MySQL 8.4 aceptó FK válida y rechazó la cruzada; tablas de prueba eliminadas. **Avance 2026-10-13:** se redactó en ADR-0001 el DER lógico/constraints propuestos de F1-05, incluyendo alcance explícito de membresía a copropiedad e historia de titularidad con fechas inclusivas. Aún requiere revisión del diseño detallado, evidencias de pruebas y aprobación antes de implementar las constraints. | F0-03. | Desarrollo/arquitectura |
| F0-B06 / P1 | L | Crear pruebas de autorización como criterios de aceptación. | Fixtures sintéticos prueban permitido/denegado para rol, tenant, copropiedad, unidad, exportación y membresía revocada; una prueba de tenant cruzado falla de forma segura. | F0-B04, F0-B05. | Desarrollo + QA |
| F0-B07 / P1 | S | Corregir pruebas de `PagesController` incompatibles con autenticación global. | **Completado:** cuatro casos que comprueban renderizado/404/403 configuran una identidad de sesión de prueba; las pruebas CSRF y el requisito global de autenticación se mantienen. Suite completa: 10 pruebas, 41 aserciones, todas aprobadas. | F0-05; política de autenticación actual. | Desarrollo + QA |
| F0-B08 / P1 | M | Preparar backlog vertical priorizado para Fase 1. | Historias de tenant/membresía, contexto, políticas, auditoría y administración SaaS tienen RF/HU, criterios observables, dependencias, datos sintéticos y responsable funcional; revisión de producto registrada. | F0-B04, F0-B05, F0-B06. | Producto + desarrollo + QA |
| F0-B09 / P2 | L | Aprobar objetivos operativos y legales antes del piloto. | Medir costos/infraestructura y pruebas de carga/recuperación; después acordar un SLA comercial separado de las metas técnicas, con RPO/RTO, retención y privacidad revisados por responsables antes del piloto. | D-009; evaluación profesional y de infraestructura. | Producto + operación + asesoría |

El backlog es base de refinamiento, no estimación ni autorización para migrar datos o desplegar funcionalidades. La decisión actual de excluir el conjunto demo está registrada; cualquier dataset real futuro requiere coordinación con su dueño. F0-B02/F0-B09 requieren profesionales; no se deben sustituir sus decisiones por supuestos técnicos.

#### Desglose candidato del backlog de Fase 1 (tallas relativas iniciales)

Historias técnicas/verticales para refinar con producto. Tallas: **S** cambio acotado, **M** historia con varios componentes/pruebas, **L** varios flujos o controles de seguridad, **XL** alcance amplio con dependencias de varias áreas. Son relativas, no equivalen a tiempo ni puntos; validar con capacidad/velocidad del equipo. Criterios verificables en MySQL 8.4 con datos sintéticos; ninguna historia autoriza trasladar el dataset demo de `horizontal`.

| ID / RF | Talla | Historia | Criterio de aceptación | Dependencias |
|---|---|---|---|---|
| F1-01 / RF-001 | L | Como operador SaaS, quiero crear y administrar el ciclo de vida de un tenant para separar cada cliente comercial. | Alta, cambio de estado, plan y límites dejan auditoría; suspender/reanudar no borra datos; usuarios de copropiedad no pueden crear ni cambiar tenants. | F0-B05; reglas comerciales aprobadas. **Progreso parcial:** migración crea tabla vacía con nombre/estado; sin API/UI, plan/límites ni autorización de operador todavía. |
| F1-02 / RF-001, RF-018 | L | Como identidad autenticada, quiero tener membresías independientes por tenant y una o más copropiedades permitidas. | Un usuario puede pertenecer a varios tenants con permisos distintos; no existe membresía implica denegación; revocar una membresía retira el acceso en la siguiente petición. | F0-B04, F0-B05. **Progreso parcial:** migración crea el vínculo tenant/usuario, estado y vigencia con unicidad y FKs; F1-05 propone la tabla de alcance explícito a copropiedades, pendiente de revisión e implementación. Aún faltan permisos por alcance; la membresía no concede acceso por sí sola. |
| F1-03 / RF-001, RF-018 | M | Como servicio de aplicación, quiero resolver el contexto de tenant desde identidad y membresía verificadas. | Un `tenant_id` manipulado en URL/formulario/API no cambia contexto; tenant inexistente, suspendido o no autorizado se deniega; el contexto se limpia entre peticiones y jobs. | F1-02. **Progreso parcial:** selector autenticado guarda en sesión de servidor la selección validada y `AppController` la revalida en cada petición autenticada; cambio de usuario limpia el contexto y membresía/tenant inválido lo elimina y deniega. Login/logout limpian la selección. El resolver sin estado recibe identificadores explícitos y puede reutilizarse en workers, pero aún no hay trabajos en segundo plano en el repositorio. Falta imponer alcance por copropiedad: no se añade una asociación provisional hasta que F1-05 modele la propiedad y las FKs compuestas de copropiedad bajo tenant. |
| F1-04 / RF-018 | L | Como responsable de seguridad, quiero políticas de autorización centralizadas por acción y alcance. | Default deny; lectura/escritura/aprobación/contabilización/anulación/exportación se verifican separadamente; pruebas cubren roles y recursos fuera de alcance. | F0-B04, F1-02, F1-03. |
| F1-05 / RF-018 | L | Como base de datos, quiero imponer pertenencia tenant/copropiedad en las relaciones. | Claves únicas y FKs compuestas impiden asociar entidades de distintos tenants; migraciones y restricciones se ejecutan en MySQL 8.4; prueba de violación se rechaza sin estado parcial. | F0-B05; prototipo revisado. **Diseño técnico propuesto en ADR-0001, pendiente de revisión de arquitectura:** mapa de propiedad para todas las tablas actuales, FK compuestas e índices por alcance, asignación de copropiedad a membresía y migración forward-only con precondición de tablas vacías. Se acordó permitir vigencias históricas repetidas por par vivienda-propietario con fechas inclusivas y rechazar solapamientos mediante servicio transaccional que serializa por vivienda; copropietarios distintos pueden tener vigencias simultáneas. |
| F1-06 / RF-018 | M | Como revisor autorizado, quiero consultar auditoría de eventos sensibles por alcance. | Cambios de rol/membresía/tenant, acceso de soporte, acciones críticas y exportaciones registran actor, recurso, tenant, fecha y motivo necesario; roles normales no alteran los eventos. | F0-B04, F1-02, F1-04. |
| F1-07 / RF-001, RF-018 | L | Como QA, quiero una matriz automática de pruebas negativas de aislamiento y permisos. | Casos con dos tenants, varias copropiedades y usuario multi-membresía demuestran que el acceso indebido falla en páginas, servicios y consultas; incluye membresía revocada y acceso de soporte vencido. | F1-02 a F1-06. |
| F1-08 / RNF-10–12 | M | Como equipo de desarrollo, quiero ejecutar pruebas reproducibles en MySQL de prueba y CI. | **Base completada:** entorno documentado, configuración local/CI sin secretos versionados, base test aislada, suite verde y ejecución remota exitosa. Se añadió una regresión explícita que verifica que una petición anónima a una página protegida redirige al login; los casos autenticados siguen verificando 200/404/403. | F0-02, F0-B07; CI disponible. |

### Fase 1 — Plataforma SaaS, seguridad y aislamiento

**RF/HU:** RF-001, RF-018; HU-01, HU-12, HU-14; RNF-01–03, RNF-07, RNF-10–12.

**Avance al 2026-10-13:** Se completó la regresión del redirect anónimo al login. La migración de tenants/membresías crea sólo estructura vacía y pasó en MySQL de prueba; no copia ni inserta datos. F1-01/F1-02 quedan parciales hasta definir y asegurar operaciones de administración, permisos y alcance a copropiedades. F1-03 avanza parcialmente: el selector autenticado almacena tenant en sesión de servidor y el contexto validado se adjunta a cada petición; cambios de usuario limpian la selección y membresías/tenants revocados o suspendidos se deniegan. El resolver acepta identidad y tenant explícitos para workers, pero el repositorio aún no tiene trabajos asíncronos. Para F1-05 se documentó en ADR-0001 una propuesta detallada de propiedad, claves, FKs e índices; se acordó conservar vigencias repetidas por par vivienda-propietario con fechas inclusivas y sin solapamientos; propietarios distintos sí pueden tener vigencias simultáneas. El diseño detallado aún requiere revisión de arquitectura antes de migrar el esquema. F1-04–F1-07 siguen sin completarse. No se afirma aislamiento multi-tenant operativo hasta implementar y probar restricciones, contexto y políticas completas. Las reglas financieras/legales permanecen bloqueadas hasta revisión profesional.

**Trabajo**
- Implementar tenant y membresía; resolver el tenant desde la identidad autenticada y su membresía, nunca desde un identificador no confiable del cliente.
- Diseñar acceso centralizado por política/recurso/acción y alcance por tenant/copropiedad/unidad; no asumir que los catálogos actuales de rol/perfil ya autorizan.
- Crear administración SaaS de clientes, estados, planes, límites y cambios auditados; definir suspensión sin borrado de datos.
- Registrar la facturación de suscripciones SaaS manualmente en esta etapa; no implementar cobro recurrente automatizado para el primer lanzamiento.
- Endurecer autenticación: recuperación, política de contraseñas, bloqueo/rate limiting, MFA para perfiles de alto impacto y ciclo de sesión/token.
- Ampliar auditoría a eventos de negocio y restringir cambios/consultas/exportación.
- Probar todas las rutas, tareas, archivos, cachés y futuras exportaciones contra acceso cruzado entre tenants.
- Revisar fallbacks de rutas y reducir superficie no intencional; configurar HTTPS/HSTS y secretos fuera del repositorio en ambientes de despliegue.

**Salida**
- Pruebas negativas automatizadas demuestran que ningún usuario accede a otro tenant.
- Los permisos se evalúan en servidor en cada operación; se prueban denegaciones y segregación de funciones.
- Crear/cambiar/suspender/reactivar un tenant conserva datos y deja auditoría; eventos críticos no pueden alterarse con roles normales.
- Decisiones de almacenamiento de datos e infraestructura aprobadas, con backups y recuperación probados en ambiente no productivo.

### Fase 2 — Maestros, copropiedad e importación

**RF/HU:** RF-002, RF-003; HU-02; reglas RN-01, RN-02.

**Trabajo**
- Evolucionar las entidades actuales hacia copropiedades, estructuras, unidades, zonas comunes, activos y relaciones históricas con personas.
- Capturar coeficiente, área, tipo, estado y período de vigencia; preservar valores previos y responsable de cambios.
- Completar personas naturales/jurídicas, contactos y relaciones de propietario, copropietario, residente, arrendatario y representante con fechas.
- Implementar plantillas de importación con validación, vista previa, reporte de filas aceptadas/rechazadas y reintento seguro.
- Planear conversión de registros actuales a la estructura objetivo, con conciliación y reversibilidad.

**Salida**
- Unicidad de código de unidad y rangos/reglas de coeficiente cubiertos por pruebas.
- Importación repetible con informe de errores sin duplicar ni perder filas válidas.
- Cambios de titularidad/coeficiente preservan historia, fecha de vigencia y actor.
- Datos quedan correctamente asociados al tenant y copropiedad en pruebas de integridad.

### Fase 3 — Núcleo financiero: cargos, cartera y recaudos

**RF/HU:** RF-004, RF-005, RF-006; HU-03, HU-04, HU-05; RN-01–05, RN-07, RN-10.

**Trabajo**
- Configurar conceptos, períodos, vencimientos, descuentos aprobados y reglas de cálculo parametrizables.
- Implementar previsualización y aprobación de liquidaciones masivas, con registro de regla/configuración usada.
- Desarrollar cartera, antigüedad, estados de cuenta, acuerdos, abonos, anticipos, saldos a favor y certificados autorizados.
- Registrar pagos y aplicar explícitamente montos a liquidaciones; soportar pagos parciales, referencias, devoluciones y claves de idempotencia.
- Añadir conciliación inicial por importación de extractos y flujos manuales de revisión para diferencias; la pasarela de pagos queda para una fase posterior.
- Mantener transacciones atómicas, decimales de precisión fija y reversión trazable; no borrar movimientos contabilizados.

**Salida**
- Flujo probado de generación de cargos a aplicación de pago y consulta autorizada de estado de cuenta.
- Reintentar un lote o pago no duplica operación; total aplicado no excede el pago salvo saldo a favor explícito.
- Casos de cálculo y redondeo revisados y aprobados por el profesional contable designado.
- Vistas/exportaciones sólo muestran unidades y períodos autorizados; se auditan accesos sensibles.

### Fase 4 — Proveedores, compras, contabilidad y presupuesto

**RF/HU:** RF-007, RF-008, RF-009; HU-06, HU-07; RN-05, RN-06, RN-11, RN-12.

**Trabajo**
- Implementar proveedores, cotizaciones, órdenes de compra, cuentas por pagar, soportes, vencimientos y aprobaciones.
- Definir plan de cuentas, períodos, centros de costo, reglas de contabilización y clasificación tributaria configurable.
- Generar comprobantes desde operaciones aprobadas, validar balance de débitos/créditos y bloquear períodos cerrados.
- Implementar reversión/ajuste autorizado, historial y separación entre quien aprueba y quien ejecuta cuando aplique.
- Implementar presupuestos, compromisos, ejecución, disponible, fondos y alertas.
- Añadir exportación estructurada de información contable para facilitar intercambios con herramientas externas, sin sustituir la contabilidad propia incluida en el primer lanzamiento.

**Salida**
- Asientos balanceados, períodos cerrados protegidos y toda corrección enlazada al original.
- Reporte de ejecución distingue aprobado, comprometido, ejecutado y disponible, con filtros/fecha de corte reproducibles.
- Escenarios financieros y salidas contables validados por contador designado; clasificación tributaria revisada profesionalmente.

### Fase 5 — Operación y comunidad

**RF/HU:** RF-010–015; HU-08–11, HU-13.

**Trabajo**
- Asambleas/consejo: convocatorias, evidencia de envío, asistencia, poderes, reglas de quórum transparentes, resultados, actas y compromisos.
- PQR: radicado único, asignación, estados, plazos, respuestas, anexos e historial visible sólo para participantes autorizados.
- Reservas: recursos, disponibilidad, restricciones y prevención transaccional de solapamientos; aprobación, cancelación y cobros si aplica.
- Mantenimiento/activos: inventario, garantías, mantenimiento preventivo/correctivo, órdenes, costos y evidencias.
- Seguridad/portería: autorización temporal y registro mínimo de visitantes, vehículos, paquetes y novedades, con retención definida.
- Gestión de documentos/contratos: metadatos, clasificación, versiones, permisos por recurso, vencimientos y descarga auditada; blobs privados fuera de webroot, URLs temporales y análisis de archivos.
- Privacidad: flujo para solicitudes de titulares, identificación verificada, vencimientos configurables y bloqueo de borrado cuando haya deber de conservación.

**Salida**
- Pruebas de acceso a PQR, documentos y datos operativos aseguran que cada perfil sólo vea lo autorizado.
- Reservas concurrentes incompatibles no pueden confirmarse dos veces.
- Cálculos de quórum no se presentan como jurídicamente validados hasta configurar y revisar la regla aplicable.
- Archivos privados no son accesibles sólo con conocer la URL; acciones de consulta/descarga quedan auditadas.

### Fase 6 — Portal, API e integraciones

**RF/HU:** RF-016–017 y superficies API/integraciones de RF-006, RF-014–016; HU-04, HU-09, HU-10.

**Trabajo**
- Publicar API REST `/api/v1` con contratos JSON, autenticación/autorización, paginación, límites, validación y OpenAPI.
- Implementar portal responsivo para administración, propietarios/residentes y portería con acceso segmentado.
- Incorporar notificaciones por correo con plantillas, preferencias, reintentos, estado y mensajes que no revelen datos sensibles.
- Añadir cola para importaciones, lotes, notificaciones y exportaciones; usar contexto de tenant en jobs y sus reintentos.
- Integrar por adaptadores correo, banco/pasarela, contabilidad externa, facturación electrónica cuando aplique y control de acceso según prioridad acordada.
- Validar webhooks firmados, repetidos e idempotentes; nunca confirmar pago sólo por retorno del navegador.
- Crear informes/indicadores por cartera, tesorería, contabilidad, presupuesto, proveedores y operación; exportar CSV/XLSX/PDF sujeto a permiso y auditoría.

**Salida**
- Contratos API documentados y pruebas automáticas para autenticación, permisos, paginación, idempotencia y aislamiento.
- Integraciones soportan repetición, errores del proveedor, reintentos controlados y conciliación de estados.
- Exportaciones incluyen copropiedad/período/filtros/fecha, aplican autorización y generan evento de auditoría.
- Metas de rendimiento (95% de consultas comunes <2,5 s, bajo carga acordada) verificadas antes de comprometer SLA.

### Fase 7 — Migración, piloto y lanzamiento

**Trabajo**
- Elegir copropiedad piloto, preparar plantilla y ejecutar ensayos de migración con totales conciliados.
- Capacitar perfiles, validar aceptación por administración, tesorería, contabilidad y portería y definir soporte/escalamiento.
- Ejecutar pruebas de regresión, seguridad, rendimiento, recuperación y restauración; cerrar vulnerabilidades según criticidad.
- Configurar monitorización, métricas, alertas, backups cifrados, RPO/RTO y plan de reversa/despliegue gradual.
- Revisar términos, privacidad, contratos con proveedores, SLA y plan de salida/exportación de datos.

**Salida a producción**
- Cero defectos críticos/altos abiertos sin mitigación formal aprobada.
- Aislamiento multi-tenant probado para todos los módulos desplegados, incluidos API, jobs, archivos, informes y webhooks.
- Aceptación financiera aprobada por responsables y política de datos autorizada. **Bloqueo vigente:** no desplegar persistencia multi-tenant ni ejecutar migraciones o DDL mientras se mantenga la decisión del usuario.
- Procedimientos de incidentes, soporte, recuperación y actualización documentados; piloto aprobado antes de ampliar clientes.

## 5. Dependencias y orden recomendado

1. **Construir el esquema tenant objetivo vacío en el destino**, sin copiar filas del origen demo; probar claves y restricciones antes de conectar los módulos de negocio.
2. **Aislamiento y autorización primero**; no exponer nuevos módulos ni datos de múltiples clientes antes de superar las pruebas negativas.
3. **Maestros e historia después**; la facturación depende de unidades/coefs/vigencias correctos.
4. **Finanzas antes que contabilidad/informes**; contabilidad consume eventos financieros aprobados y trazables.
5. **Documentos privados y permisos antes del portal**; el portal y notificaciones publican datos sensibles a usuarios externos.
6. **Integraciones tras estabilizar contratos**; definir idempotencia, estados y auditoría antes de conectar bancos/proveedores.
7. **Piloto sólo tras controles operativos** de restauración, migración, soporte y aceptación.

## 6. Calidad y controles transversales

- Mantener trazabilidad requisito → historia → cambio → prueba → evidencia de aceptación.
- Añadir pruebas unitarias para cálculo, mora, redondeo, aplicación de pagos y balance contable; integración para transacciones, colas y adaptadores.
- Añadir pruebas negativas de autorización en cada módulo y matriz de perfiles; nunca basar el aislamiento exclusivamente en la interfaz.
- Usar migraciones versionadas, claves foráneas/índices compuestos por tenant/copropiedad, restricciones únicas y transacciones; el alcance autorizado es esquema destino desde cero sin importar datos demo.
- Usar cantidades monetarias decimales, UTC para almacenamiento de fechas y política explícita de zona horaria por copropiedad.
- Separar secretos de configuración versionada; revisar dependencias y configuración de producción y realizar pruebas de penetración antes del lanzamiento.
- Registrar cambios, aprobaciones, contabilizaciones, reversiones, exportaciones y accesos a información sensible.
- Ejecutar pruebas de carga y recuperación con resultados documentados; los objetivos del PDF son metas iniciales y deben validarse, no asumirse como SLA existente.

## 7. Trazabilidad de requisitos a fases

| Fase | Requisitos principales |
|---|---|
| 0. Descubrimiento | Todos: cierre de alcance, reglas, cumplimiento, arquitectura y decisiones. |
| 1. Plataforma y seguridad | RF-001, RF-018; RNF-01–03, RNF-07, RNF-10–12. |
| 2. Maestros e importación | RF-002, RF-003; HU-02. |
| 3. Núcleo financiero | RF-004, RF-005, RF-006; HU-03–05. |
| 4. Compras, contabilidad, presupuesto | RF-007, RF-008, RF-009; HU-06–07. |
| 5. Operación y comunidad | RF-010, RF-011, RF-012, RF-013, RF-014, RF-015; HU-08–11, HU-13. |
| 6. Portal, API e integraciones | RF-016, RF-017 y superficies de integración de RF-006/RF-014/RF-015; HU-04/HU-09/HU-10. |
| 7. Piloto y lanzamiento | RNF-02, RNF-04–06, RNF-08–12 y criterios de aceptación de producción. |

## 8. Decisiones pendientes del propietario del producto

Resolver durante la fase 0, documentar responsable y fecha objetivo, y no convertir decisiones abiertas en supuestos irreversibles:

1. Después de terminar las fases de desarrollo, ¿qué banco real y muestra/diccionario CSV se usarán para configurar y certificar el mapeo de importación? Durante desarrollo se usa exclusivamente el formato ficticio de prueba definido en D-004.
2. ¿Qué niveles de servicio se ofrecerán comercialmente? Por decisión del propietario, se resolverá antes del piloto, después de medir infraestructura, costos y resultados de carga/recuperación.
3. ¿Qué reglas de liquidación, mora, quórum, conservación y tributación deben validar los profesionales designados?

### Decisión cerrada

| ID | Decisión | Estado | Consecuencia / seguimiento |
|---|---|---|---|
| D-001 | Usar Oracle MySQL Server como base de datos objetivo y adaptar el diseño a esa plataforma. | Aprobada por el propietario del producto durante el inicio de la fase 0. | MySQL Community Server 8.4.9 está instalado/configurado localmente en `127.0.0.1:3307`; los datasources locales apuntan a bases separadas y las siete migraciones/suite completa pasan en test (10 pruebas, 41 aserciones). El prototipo de FK compuesta tenant/copropiedad pasó. Se añadieron CI, guía local reproducible y ADR de tenancy aprobado por arquitectura según confirmación del usuario; el CI remoto también pasó para el commit `ee5c485`. |
| D-002 | Incluir contabilidad completa en el primer lanzamiento. | Aprobada por el propietario del producto durante el inicio de la fase 0. | Dimensionar plan de cuentas, asientos balanceados, períodos/cierres, reversiones, reportes y pruebas; obtener validación del contador responsable. Las exportaciones a terceros serán complementarias. |
| D-003 | Iniciar con registro manual e importación de extractos bancarios; postergar la pasarela de pagos. | Aprobada por el propietario del producto durante el inicio de la fase 0. | La conciliación debe admitir la importación inicial decidida en D-004 y permitir integrar una pasarela luego sin rehacer el núcleo de pagos. |
| D-004 | La importación será CSV. Durante las fases de desarrollo se usará un banco y formato totalmente ficticios para pruebas; la configuración de un banco real se realizará al terminar las fases de desarrollo. | Aprobada por el propietario del producto; el diferimiento del banco real se confirmó durante la continuación de Fase 0. | Para fixtures se usará **Banco Ficticio Alfil** y un CSV sintético UTF-8 con encabezados `fecha,descripcion,referencia,debito,credito,saldo`, fecha ISO `YYYY-MM-DD`, decimal con punto y montos sin separador de miles. No representa el formato de ninguna entidad real ni debe usarse para procesar extractos reales. Las pruebas deben cubrir abono, débito, duplicado, fila inválida e idempotencia. Tras terminar las fases de desarrollo, tesorería/producto seleccionarán el banco real y se adaptará/configurará el mapeo desde una muestra autorizada. |
| D-005 | Posponer la integración de facturación electrónica hasta validar aplicabilidad tributaria; mantener el diseño extensible a un proveedor externo. | Aprobada por el propietario del producto durante el inicio de la fase 0. | No emitir documentos como factura electrónica ni asumir una obligación uniforme. Obtener concepto profesional antes de seleccionar proveedor y habilitar operaciones. |
| D-006 | Primera versión de portería: registro manual de visitantes/novedades y autorización previa; QR/hardware en fase posterior. | Aprobada por el propietario del producto durante el inicio de la fase 0. | El diseño debe proteger datos personales por rol y permitir añadir adaptadores QR/hardware sin acoplarlos a la lógica del núcleo. |
| D-007 | Administrar planes, límites y estado; registrar facturación SaaS manualmente, sin cobro recurrente automatizado en la primera versión. | Aprobada por el propietario del producto durante el inicio de la fase 0. | Diseñar registro auditable de cambios de plan y facturación manual; precisar después el modelo de precios, límites y política de mora/suspensión. |
| D-008 | Usar base de datos compartida con `tenant_id` obligatorio y controles centralizados para aislar clientes. | Aprobada por el propietario del producto durante el inicio de la fase 0. | Como las tablas actuales no muestran `tenant_id` y varias claves son globales, diseñar migración de identidad/propiedad de datos y pruebas entre tenants antes de habilitar multiempresa. |
| D-009 | Usar para el piloto las metas iniciales del PDF: 99,5 % de disponibilidad mensual, RPO ≤ 24 h, RTO ≤ 8 h y 95 % de consultas comunes < 2,5 s. | Aprobadas como objetivos provisionales sujetos a validación técnica y económica; no son un SLA garantizado. | Por decisión del propietario, definir el SLA comercial después de medir costos/infraestructura y antes del piloto/lanzamiento; no fijarlo durante Fase 0 sin esas evidencias. |
| D-010 | Permitir que una identidad de usuario pertenezca a más de un tenant mediante membresías independientes por tenant/copropiedad. | Aprobada por el propietario del producto durante la fase 0. | Los roles/permisos no serán globales; el tenant activo se validará desde una membresía autorizada en cada petición. Añadir pruebas de cambio de tenant y denegación de acceso sin membresía. |

Ejemplo **sintético** para el fixture del Banco Ficticio Alfil (no corresponde a una entidad ni cuenta real):

```csv
fecha,descripcion,referencia,debito,credito,saldo
2026-01-05,Abono de prueba unidad A-101,DEMO-ABONO-001,0.00,125000.00,125000.00
2026-01-06,Cargo de prueba,DEMO-CARGO-001,25000.00,0.00,100000.00
```

## 9. Estimación y siguiente paso

No se recomienda fijar fechas de entrega a partir de esta revisión: faltan el ADR y refinamiento del modelo técnico, validación profesional de reglas contables/tributarias, definición de equipo e infraestructura y medidas de capacidad/velocidad. Las tallas relativas de Fase 0 y Fase 1 son una orientación inicial, no fechas. Al cerrar estos puntos, estimar cada fase con el equipo real y reservar capacidad para pruebas, migración, revisión profesional y seguridad.

**Primer entregable recomendado:** backlog priorizado para Fase 0 y Fase 1, con modelo multi-tenant, matriz de autorización y compatibilidad con MySQL verificada antes de comenzar más CRUDs de dominio.

## Referencias revisadas

- [Especificación funcional y técnica de propiedad horizontal](./Especificacion_Software_Propiedad_Horizontal_Colombia.pdf)
- [`composer.json`](../composer.json)
- [`config/app.php`](../config/app.php)
- [`config/routes.php`](../config/routes.php)
- [`src/Application.php`](../src/Application.php)
- [`src/Controller/AppController.php`](../src/Controller/AppController.php)
- [`config/Migrations/`](../config/Migrations/)
- [`tests/`](../tests/)
