# Plan de trabajo: plataforma de propiedad horizontal

**Base del análisis:** especificación funcional y técnica, versión 1.0 (octubre de 2026), y código presente en este repositorio al momento de la revisión.  
**Estado:** Fase 0 en curso. Decisiones D-001–D-010 y diseño lógico multi-tenant aprobados; MySQL local y bases de desarrollo/prueba separadas configurados; las migraciones se ejecutaron en la base de prueba. La suite inicial tuvo 6 pruebas aprobadas y 4 fallos en pruebas de `PagesController` que esperaban códigos HTTP distintos a la redirección observada. Siguen pendientes la validación técnica del diseño, la inspección y conciliación de datos actuales, las validaciones profesionales y el backlog estimado. La especificación se declara documento base sujeto a validación; este plan no sustituye la revisión profesional contable, jurídica y tributaria.

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
- Validar el modelo de datos actual y plan de transición; configurar Oracle MySQL Server con versión, motor de almacenamiento, charset/collation y capacidades operativas acordadas antes de ampliar el esquema. **Avance:** MySQL Community Server 8.4.9 está configurado localmente en loopback, puerto 3307; la configuración local separa los datasources `default` y `test` en bases distintas. Las siete migraciones del proyecto se aplicaron en la base de prueba; falta reproducir la configuración en CI y resolver/triagear cuatro fallos de pruebas de `PagesController`.
- Inventariar sin cambios los datos de `horizontal` en el entorno anterior; acordar si se migran, conservan en MariaDB o se reemplazan. Separar la base de pruebas antes de ejecutar el migrador de tests.
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
| F0-01 | Alcance y decisiones D-001–D-010 | Parcialmente completado | Las diez decisiones están registradas. Aún faltan la muestra/columnas CSV del banco del piloto, el SLA comercial y las reglas contables, tributarias, de mora, quórum y conservación aprobadas por responsables. |
| F0-02 | Entorno MySQL objetivo | Parcialmente completado | MySQL Community Server 8.4.9 corre localmente en puerto 3307; los datasources `default` y `test` usan bases separadas y las siete migraciones se aplicaron en la base de prueba. Falta compatibilidad de CI y arranque de desarrollo reproducible; la ejecución inicial de PHPUnit tuvo cuatro fallos en `PagesController` por respuestas 302 frente a los códigos esperados. |
| F0-03 | Diseño lógico multi-tenant | Aprobado por producto como arquitectura objetivo | El propietario aprobó el diseño descrito abajo. Desarrollo debe validarlo frente a las restricciones compuestas de MySQL 8.4 y registrar el ADR antes de convertirlo en migraciones; no cambiar aún el esquema ni los datos existentes. |
| F0-04 | Inventario y mapeo de datos actuales | Inventario agregado completado; conciliación pendiente | Con autorización del propietario se consultaron sólo metadatos y conteos agregados de `horizontal`: 16 tablas InnoDB, 27 filas totales según `COUNT(*)`. No se leyeron filas ni se modificó la base. Aún se requiere respaldo verificable, confirmar con el dueño de datos la copropiedad/tenant de cada registro y acordar cualquier migración; los conteos no prueban integridad ni propiedad. |
| F0-05 | Separar bases de desarrollo y prueba | Configurado; validación inicial completada | `config/app_local.php` (ignorado por Git) apunta `default` a `alfil_horizontal_dev` y `test` a `alfil_horizontal_test`, ambas en MySQL 8.4 con cuentas separadas y limitadas a su esquema. PHPUnit aplicó las siete migraciones sólo en `test`; `default` contiene únicamente la tabla vacía `cake_migrations` creada al consultar su estado (cero migraciones aplicadas). La base anterior `horizontal` en MariaDB/XAMPP no se modificó. La suite reportó cuatro fallos HTTP en `PagesController`, pendientes de triage independiente. |
| F0-06 | Matriz de roles, permisos y alcance | Pendiente | Convertir los perfiles del PDF en permisos por acción y alcance; aprobar la matriz con producto y probar casos permitidos/denegados antes de Fase 1. |
| F0-07 | Reglas de negocio y cumplimiento | Pendiente | Validación por contador, asesor jurídico/tributario y responsables de la copropiedad; parametrizar diferencias por copropiedad, sin declarar cumplimiento automático del sistema. |
| F0-08 | Historias, criterios de aceptación y dependencias | En elaboración | Desglosar requisitos RF/HU en historias verticales priorizadas; criterios observables, datos de prueba sin PII real, dependencias y responsable funcional para cada una. |
| F0-09 | Estimación y aprobación de alcance | Pendiente | Estimar con el equipo real sólo cuando F0-01, F0-03, F0-06 y F0-08 estén acordados; no prometer fechas antes de la estimación y validación del alcance. |

#### Inventario agregado de la base anterior `horizontal`

Consulta de sólo lectura ejecutada con autorización: se consultaron nombres de tablas y conteos `COUNT(*)`; no se seleccionaron campos ni filas de negocio, no se creó respaldo y no se alteró la base. Las 16 tablas reportaron motor InnoDB. Estos conteos son una fotografía del entorno local y deben reconciliarse con el responsable de los datos antes de decidir migración.

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

#### Migración del modelo actual propuesta (sin ejecutar todavía)

1. Capturar respaldo verificable y catálogo de tablas/índices/constraints del origen, sin imprimir ni compartir datos personales.
2. Identificar el motor y versión reales de la base origen; la instalación XAMPP local comprobada es MariaDB 10.4.32. No asumir que esa base y MySQL 8.4 del nuevo entorno contienen los mismos datos.
3. Acordar si las tablas existentes corresponden a una sola copropiedad, a varias o a datos de prueba; no inventar el tenant ni la copropiedad propietaria.
4. Diseñar un mapeo explícito para `propiedades_horizontales`, `viviendas`, `copropietarios`, `vivienda_copropietarios`, `residentes`, `parqueaderos`, asignaciones, pagos, usuarios y auditoría.
5. Preparar migración en dos pasos: añadir columnas/relaciones inicialmente conciliables, poblarlas con mapeo aprobado, validar nulos/huérfanos/unicidad, y sólo después imponer `NOT NULL`, índices y FKs; la secuencia exacta depende de prueba en copia.
6. Comparar conteos y totales por tabla/tenant/copropiedad, reportar diferencias y obtener aprobación antes de cortar lectura/escritura al origen.
7. Mantener origen respaldado y plan de reversa; no ejecutar migración destructiva ni eliminar esquema viejo como parte del corte inicial.

#### Propuesta de matriz inicial de acceso (para validación)

| Perfil de la especificación | Alcance inicial propuesto | Restricción a comprobar |
|---|---|---|
| Superadministrador SaaS | Planes, tenants, suscripciones y soporte autorizado. | Sin lectura rutinaria de datos financieros/personales; acceso excepcional temporal y auditado. |
| Administrador de copropiedad | Operación de copropiedades asignadas. | Aprobación/contabilización sólo si su permiso y matriz de montos lo permiten. |
| Contador | Plan de cuentas, comprobantes, cierres, conciliación e informes asignados. | Sin administración global de usuarios; cierre/reapertura con permiso independiente. |
| Tesorero | Recaudos, pagos, caja y bancos asignados. | Separar aprobación de ejecución cuando rija control dual. |
| Consejo / revisor fiscal | Consulta e informes autorizados. | Sólo lectura; exportaciones sensibles auditadas. |
| Propietario | Estado de cuenta y documentos de sus unidades vinculadas y vigentes. | No consultar unidades ajenas ni datos privados de residentes sin autorización. |
| Residente/arrendatario | PQR, reservas, visitantes y comunicación habilitada. | Sin acceso financiero automático por el solo vínculo de residencia. |
| Portería/vigilancia | Visitantes, paquetes y novedades necesarios para su turno. | Minimización de PII y sin acceso a contabilidad. |
| Proveedor/contratista | Órdenes y documentos expresamente asignados y vigentes. | Sin búsqueda global de proveedores, unidades o documentos. |

La matriz es una propuesta funcional, no una autorización implementada. Debe convertirse en permisos verificables por acción (lectura, creación, edición, aprobación, contabilización, anulación, exportación y administración) y por recurso/alcance.

### Fase 1 — Plataforma SaaS, seguridad y aislamiento

**RF/HU:** RF-001, RF-018; HU-01, HU-12, HU-14; RNF-01–03, RNF-07, RNF-10–12.

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
- Aceptación financiera y migración aprobadas por responsables designados; backups restaurados con evidencia.
- Procedimientos de incidentes, soporte, recuperación y actualización documentados; piloto aprobado antes de ampliar clientes.

## 5. Dependencias y orden recomendado

1. **Diseñar y validar la transición a `tenant_id` obligatorio en MySQL**, verificar compatibilidad en la versión real y preservar datos existentes antes de nuevas migraciones de negocio.
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
- Usar migraciones versionadas, claves foráneas/índices compuestos por tenant/copropiedad, restricciones únicas y transacciones.
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

1. Al definir el banco del piloto, ¿qué muestra de extracto CSV y diccionario de columnas se usarán para configurar el mapeo?
2. ¿Qué niveles de servicio se ofrecerán comercialmente, una vez validados los objetivos técnicos provisionales?
3. ¿Qué reglas de liquidación, mora, quórum, conservación y tributación deben validar los profesionales designados?

### Decisión cerrada

| ID | Decisión | Estado | Consecuencia / seguimiento |
|---|---|---|---|
| D-001 | Usar Oracle MySQL Server como base de datos objetivo y adaptar el diseño a esa plataforma. | Aprobada por el propietario del producto durante el inicio de la fase 0. | MySQL Community Server 8.4.9 está instalado/configurado localmente en `127.0.0.1:3307`; la consulta de verificación confirmó versión, puerto, enlace local y `utf8mb4`. Los datasources locales apuntan a bases separadas de desarrollo y prueba; las siete migraciones se aplicaron en test. CI/arranque persistente y triage de cuatro fallos actuales de PHPUnit siguen pendientes. |
| D-002 | Incluir contabilidad completa en el primer lanzamiento. | Aprobada por el propietario del producto durante el inicio de la fase 0. | Dimensionar plan de cuentas, asientos balanceados, períodos/cierres, reversiones, reportes y pruebas; obtener validación del contador responsable. Las exportaciones a terceros serán complementarias. |
| D-003 | Iniciar con registro manual e importación de extractos bancarios; postergar la pasarela de pagos. | Aprobada por el propietario del producto durante el inicio de la fase 0. | La conciliación debe admitir la importación inicial decidida en D-004 y permitir integrar una pasarela luego sin rehacer el núcleo de pagos. |
| D-004 | La importación inicial de extractos será CSV; el banco aún no está definido. | Aprobada por el propietario del producto durante el inicio de la fase 0. | No fijar columnas ni construir un formato bancario hasta definir el banco y disponer de una muestra real; preparar el diseño para configurar el mapeo CSV después. |
| D-005 | Posponer la integración de facturación electrónica hasta validar aplicabilidad tributaria; mantener el diseño extensible a un proveedor externo. | Aprobada por el propietario del producto durante el inicio de la fase 0. | No emitir documentos como factura electrónica ni asumir una obligación uniforme. Obtener concepto profesional antes de seleccionar proveedor y habilitar operaciones. |
| D-006 | Primera versión de portería: registro manual de visitantes/novedades y autorización previa; QR/hardware en fase posterior. | Aprobada por el propietario del producto durante el inicio de la fase 0. | El diseño debe proteger datos personales por rol y permitir añadir adaptadores QR/hardware sin acoplarlos a la lógica del núcleo. |
| D-007 | Administrar planes, límites y estado; registrar facturación SaaS manualmente, sin cobro recurrente automatizado en la primera versión. | Aprobada por el propietario del producto durante el inicio de la fase 0. | Diseñar registro auditable de cambios de plan y facturación manual; precisar después el modelo de precios, límites y política de mora/suspensión. |
| D-008 | Usar base de datos compartida con `tenant_id` obligatorio y controles centralizados para aislar clientes. | Aprobada por el propietario del producto durante el inicio de la fase 0. | Como las tablas actuales no muestran `tenant_id` y varias claves son globales, diseñar migración de identidad/propiedad de datos y pruebas entre tenants antes de habilitar multiempresa. |
| D-009 | Usar para el piloto las metas iniciales del PDF: 99,5 % de disponibilidad mensual, RPO ≤ 24 h, RTO ≤ 8 h y 95 % de consultas comunes < 2,5 s. | Aprobadas como objetivos provisionales sujetos a validación técnica y económica; no son un SLA garantizado. | Diseñar y ejecutar pruebas de carga/recuperación, comprobar costos e infraestructura y acordar por separado el SLA comercial antes de lanzamiento. |
| D-010 | Permitir que una identidad de usuario pertenezca a más de un tenant mediante membresías independientes por tenant/copropiedad. | Aprobada por el propietario del producto durante la fase 0. | Los roles/permisos no serán globales; el tenant activo se validará desde una membresía autorizada en cada petición. Añadir pruebas de cambio de tenant y denegación de acceso sin membresía. |

## 9. Estimación y siguiente paso

No se recomienda fijar fechas de entrega a partir de esta revisión: faltan decisiones de arquitectura multi-tenant, modelo de datos, tratamiento profesional de reglas contables/tributarias, estrategia de migración, tamaño del equipo e infraestructura. Al cerrar la fase 0, desglosar cada fase en historias verticales, estimar por equipo y reservar capacidad para pruebas, migración, revisión profesional y seguridad.

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
