# ADR-0001: Aislamiento multi-tenant en una base de datos compartida

- **Estado:** Dirección de aislamiento aprobada por producto/arquitectura según confirmación del usuario (2026-10-09); prototipo de FK compuesta validado en MySQL 8.4. La propuesta detallada F1-05 se añadió el 2026-10-13 y aún requiere revisión de arquitectura antes de migraciones. El usuario autorizó crear el esquema destino desde cero, sin trasladar ni cargar datos del conjunto demo. Las migraciones actuales no implementan ni acreditan aislamiento multi-tenant completo.
- **Fecha:** 2026-10-09.
- **Responsables:** Producto y arquitectura aprobaron la dirección. La implementación autorizada se limita al esquema destino vacío; no incluye datos del origen.

## Contexto

La aplicación es actualmente un CakePHP monolítico con tablas de usuarios, roles y perfiles globales y datos operativos sin `tenant_id`. La decisión D-008 es usar una base compartida, y D-010 permite que una identidad pertenezca a varios tenants con permisos independientes. No se trasladará el conjunto local de demostración de MariaDB `horizontal`; una carga real futura requerirá inventario, respaldo, mapeo y autorización separados.

Los filtros de interfaz no son una barrera de aislamiento suficiente. El modelo debe prevenir consultas y relaciones cruzadas en la aplicación y, para relaciones entre filas, en MySQL.

## Decisión

Adoptar **una base de datos compartida con `tenant_id` en cada entidad operativa propiedad de un cliente**, complementado por `copropiedad_id` cuando la fila pertenezca a una copropiedad. El tenant es el cliente SaaS y posee una o más copropiedades.

### Identidad y membresías

- `users` representa identidad/autenticación global; la cuenta puede tener membresías en varios tenants.
- Una membresía vincula usuario y tenant, tiene estado/fechas y un rol o concesiones de permisos propios de ese alcance.
- El alcance a una o más copropiedades se modela explícitamente; no se infiere de un rol global ni de un identificador enviado por el cliente.
- Los roles/perfiles actuales no bastan como autorización multi-tenant. Su migración/reemplazo se diseñará aparte; no se les atribuirán poderes globales por compatibilidad.
- El usuario puede aportar un tenant solicitado para seleccionar contexto, pero nunca concederlo: el servidor lo valida contra membresías activas en cada petición.
- El rol `super_admin` existente no se convierte automáticamente en privilegio global de plataforma. Antes de migrarlo se debe decidir cómo se identifican los operadores SaaS, qué acciones globales tienen y cómo se auditan los accesos excepcionales; soporte no obtiene lectura de datos tenant por defecto.

### Propiedad y relaciones

- Los datos operativos de tenant llevan `tenant_id NOT NULL`; las filas específicas de una copropiedad llevan también `copropiedad_id NOT NULL`.
- Una entidad de copropiedad referencia a su tenant. Los catálogos realmente globales son excepciones explícitas y documentadas, no una forma de omitir el aislamiento.
- Una tabla padre que pueda ser referenciada por tenant/copropiedad publica una clave única compuesta, por ejemplo `(tenant_id, id)`.
- Toda relación hija a esa entidad usa FK compuesta `(tenant_id, copropiedad_id)` → `(tenant_id, id)` o la composición equivalente apropiada al modelo. Restricciones `UNIQUE` y FKs deben impedir que la fila de un tenant apunte a una copropiedad de otro tenant.
- La unicidad de códigos y claves naturales se determina por dominio y alcance. Los índices globales existentes no se cambian hasta decidir la semántica por tenant/copropiedad y hacer backfill validado.
- Las personas/propietarios y demás PII se mantienen dentro del alcance tenant al diseñar las tablas; compartir la misma fila personal entre clientes requiere una justificación y revisión de privacidad explícitas.

### Clasificación inicial de las tablas existentes

Esta clasificación es una propuesta técnica de propiedad del dato, no un inventario exhaustivo del esquema futuro. Se debe contrastar con el DER aprobado antes de crear migraciones.

| Tabla existente / concepto | Alcance objetivo | Relación que debe imponerse |
|---|---|---|
| `users` | Identidad global y credenciales; no almacena rol operativo global. | Membresías a tenant en una nueva entidad; la identidad autenticada por sí sola no concede acceso a datos operativos. |
| `roles`, `profiles` | Catálogos de autorización a revisar. Catálogo inmutable/global sólo si se valida; asignaciones siempre por membresía. | No usar `users.role_id`/`profile_id` como concesión efectiva entre tenants; definir roles personalizados tenant-scoped si producto lo necesita. |
| Nuevos `tenants`, planes y suscripciones | Tenant SaaS. | Plan/suscripción referencian al tenant; cada copropiedad referencia tenant mediante clave compuesta. |
| Nueva membresía y alcance de membresía | Tenant; asociación explícita a una o más copropiedades permitidas. | FK a identidad y tenant; unicidad usuario-tenant según el modelo acordado; tabla `tenant_membership_properties` propuesta para verificar alcance explícito. |
| `propiedades_horizontales` | Tenant + copropiedad. | Añadir `tenant_id` y clave candidata `(tenant_id, id)` para relaciones hijas. |
| `viviendas` | Tenant + copropiedad. | Añadir vínculo a propiedad horizontal (hoy ausente) y FK compuesta que impida vivienda bajo tenant incorrecto. |
| `copropietarios` | Tenant-scoped por defecto por contener PII; no asumir que una persona es compartible entre clientes. | Asociaciones propietario-vivienda llevan tenant y copropiedad y validan ambos extremos. |
| `vivienda_copropietarios` | Tenant + copropiedad. | FKs compuestas a vivienda y persona. Decisión de producto del 2026-10-13: permitir varias vigencias históricas por vivienda-propietario, con extremos inclusivos y sin solapamiento para el mismo par; los propietarios distintos pueden ser copropietarios simultáneos. |
| `residentes` | Tenant + copropiedad, vinculada a vivienda. | FK compuesta a vivienda; verificar tenant/copropriedad en consultas de perfil y reserva. |
| `parqueaderos` | Tenant + copropiedad. | Añadir propiedad horizontal y cambiar unicidad global de `code` sólo tras acordar alcance natural. |
| `asignaciones_parqueaderos`, `pagos_parqueaderos` | Tenant + copropiedad. | Todas las FKs a parqueadero, persona, residente, vivienda y asignación deben validar el mismo ámbito; pagos heredan/validan el ámbito de la asignación. |
| `personal_administraciones`, `polizas_aseguramiento` | Tenant + copropiedad. | Reemplazar FK simple a propiedad por relación compuesta y acotar claves únicas a la propiedad/tenant cuando aplique. |
| Reuniones (concepto futuro; no existe tabla en las migraciones actuales) | Tenant + copropiedad. | Al incorporarlas, añadir la propiedad convocante; `created_by` refiere a identidad global, pero cada operación exige membresía vigente y permiso. |
| `access_audits` | Revisar separación entre autenticación global y auditoría de negocio tenant-scoped. | Eventos de login previos a seleccionar tenant pueden carecer de tenant sólo si se clasifican explícitamente como globales; auditoría de operaciones/exportaciones siempre registra tenant y alcance. |

### Diseño técnico propuesto para F1-05: alcance de copropiedad

**Estado del diseño detallado:** propuesta lista para revisión de arquitectura antes de aplicar migraciones. No equivale a aprobación del DER ni habilita CRUD multi-tenant.

Se conserva `propiedades_horizontales` como entidad de copropiedad existente y se usa su nombre de columna `propiedad_horizontal_id` en vez de introducir un segundo nombre (`copropiedad_id`) para el mismo concepto. Las claves referenciadas compuestas son redundantes respecto del `id` global, pero necesarias para que InnoDB compruebe la pertenencia común en cada relación.

| Tabla | Propiedad propuesta | Claves candidatas y referencias |
|---|---|---|
| `propiedades_horizontales` | Raíz de copropiedad; `tenant_id NOT NULL`. | `UNIQUE (tenant_id, id)`; NIT conserva su unicidad global actual, sujeto a confirmar la regla de dominio. |
| `viviendas` | `tenant_id` y `propiedad_horizontal_id` obligatorios. | `UNIQUE (tenant_id, propiedad_horizontal_id, id)`; FK `(tenant_id, propiedad_horizontal_id)` a copropiedad. Códigos y `(type, tower_block, number)` pasan de unicidad global a unicidad por `(tenant_id, propiedad_horizontal_id)`. |
| `copropietarios` | PII limitada al tenant; permite que la misma persona tenga registros separados entre clientes. | `UNIQUE (tenant_id, id)`; documento único por `(tenant_id, document_type, document_number)`. No se usa esta tabla para compartir una identidad personal entre tenants. |
| `vivienda_copropietarios` | `tenant_id` y `propiedad_horizontal_id` obligatorios; corresponde al vínculo histórico de titularidad. | FK `(tenant_id, propiedad_horizontal_id, vivienda_id)` a vivienda; FK `(tenant_id, copropietario_id)` a propietario. `start_date` obligatorio y ambas fechas inclusivas; un propietario puede tener varias vigencias para la misma vivienda, no solapadas. El solapamiento se valida en servicio dentro de transacción bloqueando la vivienda afectada; MySQL no ofrece una restricción nativa de exclusión temporal. Una vigencia abierta (`end_date IS NULL`) se considera vigente sin límite superior. |
| `residentes` | `tenant_id`, `propiedad_horizontal_id` y `vivienda_id` obligatorios. | FK triple a vivienda; código único por copropiedad. Documento único en el tenant para evitar duplicación accidental dentro de un mismo cliente sin cruzar clientes. |
| `parqueaderos` | `tenant_id` y `propiedad_horizontal_id` obligatorios. | `UNIQUE (tenant_id, propiedad_horizontal_id, id)`; código único por copropiedad. |
| `personal_administraciones`, `polizas_aseguramiento` | `tenant_id` redundante junto a `propiedad_horizontal_id` para validar el par tenant-copropiedad. | FK compuesta a `(tenant_id, id)` de copropiedad; número de póliza único por copropiedad. |
| `asignaciones_parqueaderos` | `tenant_id` y `propiedad_horizontal_id` obligatorios; referencias a parqueadero, propietario, residente y vivienda respetan el mismo ámbito. | `UNIQUE (tenant_id, propiedad_horizontal_id, id)` para ser referenciable por pagos; FKs compuestas a parqueadero/vivienda por tenant y copropiedad y a propietario por tenant. Las referencias actualmente opcionales siguen siendo opcionales; reglas de consistencia entre vivienda, residente y propietario son validación de dominio posterior. |
| `pagos_parqueaderos` | `tenant_id` y `propiedad_horizontal_id` obligatorios. | FK `(tenant_id, propiedad_horizontal_id, asignacion_parqueadero_id)` a asignación; unicidad del período pasa a `(tenant_id, asignacion_parqueadero_id, period)`. |
| `tenant_membership_properties` (nueva) | Alcance explícito de una membresía a una copropiedad. | `tenant_id`, `tenant_membership_id`, `propiedad_horizontal_id`; FK `(tenant_id, tenant_membership_id)` a membresía y `(tenant_id, propiedad_horizontal_id)` a copropiedad; `UNIQUE (tenant_membership_id, propiedad_horizontal_id)`. `tenant_memberships` publica `UNIQUE (tenant_id, id)`. Esta tabla no asigna roles ni concede permisos por sí sola. |
| `access_audits` | Auditoría de autenticación global. | No se convierte en tabla de negocio tenant-scoped; los eventos de operación requieren una entidad de auditoría de negocio separada y F1-06. |

El orden de creación/actualización es: claves candidatas de `tenants`/`tenant_memberships`; clave candidata de copropiedad; columnas y claves de vivienda/persona; tabla de alcance de membresía; tablas puente e hijas; y, al final, FKs compuestas. Se reemplazan las FKs simples e índices únicos globales que contradigan el alcance anterior. Todos los componentes de cada FK compuesta deben tener tipos, signo y orden idénticos en los dos extremos; se conserva `RESTRICT` para borrado y `CASCADE` sólo para actualización de identificadores.

Las fechas de titularidad se interpretan como inclusivas: para el mismo par vivienda-propietario, una nueva vigencia no puede empezar el mismo día en que termina la anterior. Vigencias de propietarios distintos sí pueden solaparse, porque la copropiedad simultánea de una vivienda es válida. Para el mismo par hay solapamiento si cada inicio es menor o igual al fin de la otra; un fin nulo equivale a ausencia de límite superior. Se rechazan también vigencias cuyo fin sea anterior a su inicio.

La migración F1-05 debe ser nueva y forward-only. Antes de su primer DDL, comprobará que todas las tablas operativas afectadas están vacías; si encuentra filas, abortará sin backfill, tenant ficticio ni cambio de datos. Se ejecuta únicamente contra el esquema destino vacío expresamente configurado. No se edita una base demo ni se usa una copia de sus registros para poblar, reconciliar o probar constraints. Como el DDL de MySQL no es atómico en conjunto, una falla posterior al preflight puede dejar cambios estructurales parciales; no debe cambiar datos. El procedimiento de recuperación será reparar/recrear únicamente el destino vacío autorizado y volver a ejecutar la migración. Un rollback sólo puede eliminar la estructura nueva bajo confirmación de que las tablas siguen vacías.

Las pruebas de aceptación deben crear dos tenants y al menos una copropiedad/vivienda por tenant; aceptar FKs dentro del ámbito y rechazar referencias cruzadas de propietarios, viviendas, residentes, parqueaderos, asignaciones, pagos y alcance de membresía. Probar además unicidad local al tenant/copropiedad, asociación temporal no solapada y rechazo de una intersección de vigencias, sin filas parciales tras error.

### Resolución del contexto y autorización

- El tenant activo se resuelve en servidor desde una identidad autenticada y una membresía activa comprobada en cada petición. Antes de operar sobre una copropiedad también se verifica una fila explícita en `tenant_membership_properties`; pertenecer al tenant no implica acceso a todas sus copropiedades.
- Se deniega por defecto cualquier acción no concedida. Leer, crear, editar, aprobar, contabilizar, anular, exportar y administrar son acciones diferenciables.
- Los servicios, consultas, tareas asíncronas, cachés, archivos, webhooks e informes reciben un contexto de tenant explícito y validado. No se confía en un `tenant_id` libre de URL, formulario, API ni payload de trabajo.
- Las tareas persistidas guardan el identificador de tenant/membresía necesario y vuelven a validar estado/permisos antes de operar; los contextos no se reutilizan accidentalmente entre trabajos.
- El soporte SaaS no obtiene acceso implícito a datos del cliente. La suplantación/acceso excepcional debe tener permiso explícito, motivo, vencimiento y auditoría.

## Alternativas consideradas

1. **Una base por tenant:** ofrece aislamiento físico mayor, pero agrega operación, migraciones y costos por cliente, y no corresponde a D-008.
2. **Una instancia o esquema distinto por tenant:** reduce algunos riesgos de consulta cruzada, pero eleva complejidad operativa y no satisface la base compartida aprobada.
3. **Sólo filtrar en controladores o interfaz:** rechazado porque puede omitirse en servicios, consultas, trabajos, exportaciones o rutas nuevas.
4. **Rol global en `users`:** rechazado para permisos de copropiedad porque contradice D-010 y da privilegios fuera del tenant activo.

## Consecuencias

- Se requiere una capa central de contexto tenant y autorización; todos los caminos de acceso a datos deben usarla.
- Las claves/índices redundantes para restricciones compuestas aumentan espacio y trabajo de migración, pero añaden una barrera de integridad referencial.
- La consulta de listas, unicidad, búsquedas, auditoría, documentos, exports y jobs debe auditarse por alcance, no sólo las rutas web.
- Los identificadores globales existentes, la identidad de personas y la granularidad de membresías requieren decisiones de dominio antes de imponer constraints.
- El usuario autorizó migraciones de esquema para construir una base destino vacía, sin migrar ni cargar datos existentes. No copiar el conjunto demo/origen ni alterarlo. El diseño descrito permanece como objetivo; el aislamiento multi-tenant no se considera implementado hasta terminar y probar todas sus capas.
- La migración inicial crea `tenants` y `tenant_memberships` sin insertar registros. Una membresía contiene únicamente tenant, identidad, estado y vigencia; no incorpora roles globales ni concede por sí misma acceso a recursos. La tabla de alcance `tenant_membership_properties` y las claves compuestas de las tablas operativas están diseñadas como propuesta F1-05, pendientes de revisión y migración antes de exponer esos datos.

## Validación técnica realizada

Un prototipo desechable en la base de desarrollo MySQL 8.4 creó tablas InnoDB para tenants, copropiedades y unidades. Verificó que:

1. la relación de una unidad a una copropiedad del mismo tenant se acepta;
2. una unidad con tenant distinto al de su copropiedad se rechaza mediante FK compuesta.

Las tablas de prueba se eliminaron después y se comprobó que la base `alfil_horizontal_dev` conserva únicamente su tabla previa `cake_migrations`. Este prototipo valida la capacidad del motor, no constituye revisión del modelo completo ni autorización para migrar datos.

## Registro de revisión de arquitectura

| Fecha | Artefacto | Resultado | Registro |
|---|---|---|---|
| 2026-10-09 | ADR-0001, dirección de aislamiento multi-tenant | Aprobado | El usuario confirmó que la persona revisora aprobó el ADR. No se recibieron comentarios, condiciones ni nombre para registrar; no inferir que el DER detallado o cada punto de la lista de verificación recibió aprobación individual. |
| 2026-10-13 | F1-05, vigencias de titularidad | Decisión de producto registrada; revisión técnica pendiente | El usuario confirmó varias vigencias históricas por vivienda-propietario, con fechas inclusivas y sin solapamiento para el mismo par; copropietarios distintos pueden tener vigencias simultáneas. No es aprobación de arquitectura del DER o constraints. |

## Pendientes de diseño e implementación

- Revisar con arquitectura el diseño F1-05 detallado de este ADR: DER, cardinalidades, nulabilidad, claves candidatas, índices, constraints y plan seguro de DDL. Registrar fecha, observaciones, decisiones y responsable de cada hallazgo; la aprobación de la dirección del ADR no aprueba automáticamente esta propuesta.
- Confirmar la clasificación de tablas propuesta, la identidad de personas/PII, la unicidad global del NIT y la separación de auditoría de login frente a auditoría de negocio.
- La asociación explícita de membresía a una o más copropiedades está diseñada en F1-05; faltan revisar su implementación, delegaciones/suplencias y la decisión separada de permisos por alcance.
- Definir la identidad y los límites de privilegio del operador SaaS, incluyendo el tratamiento del `super_admin` histórico, suplantación/acceso de soporte y revocación inmediata.
- Revisar índices únicos por dominio y la política de PII/documento en tenant antes de aprobar la implementación.
- El mapa propuesto de constraints compuestos cubre las relaciones de las tablas actuales; comprobar tamaños/tipos/signo/orden idénticos durante su implementación y pruebas.
- Definir ciclo de tenants suspendidos y membresías revocadas, expiración de soporte y auditoría.
- Escribir pruebas negativas sintéticas para cada capa: consultas, servicios, rutas, jobs, archivos, cachés e informes.
- Obtener revisión profesional de privacidad, retención, contabilidad y reglas legales que afecten autorización/conservación.
- El ADR recibió aprobación de arquitectura; cualquier observación posterior o cambio de decisión debe quedar versionado. Completar el DER, índices, restricciones y plan de migración con responsables antes de implementar.

### Lista de verificación para una futura revisión de implementación

Marcar cada punto como **aprobado**, **cambiar** o **no aplica**, con evidencia/decisión. La autorización de esquema desde cero no elimina estos requisitos de diseño/validación ni autoriza copiar los datos existentes:

1. Confirmar la clasificación global/tenant/copropiedad de cada tabla, incluida identidad y PII.
2. Aprobar el modelo de membresías: pertenencia a varios tenants, alcance a varias copropiedades, roles, vigencia y revocación.
3. Resolver el operador SaaS y el `super_admin` actual sin elevar privilegios de tenant accidentalmente.
4. Revisar claves candidatas, FKs compuestas, unicidad por alcance, nulabilidad, cascadas y tipos/orden de columnas.
5. Revisar el tratamiento acordado de titularidad histórica (vigencias inclusivas, no solapadas para el mismo par), la relación vivienda-copropiedad que hoy falta y la asignación de parqueaderos.
6. Acordar migración por etapas, backfill y reconciliación para un futuro conjunto de datos autorizado, sin usar el dataset demo actual.
7. Verificar aislamiento en consultas y servicios además de controladores, incluyendo jobs, exportaciones, caché, archivos y auditoría.
8. Identificar riesgos y tareas de seguimiento; documentar responsables y pruebas de aceptación antes de comenzar las migraciones. El diseño detallado F1-05 debe recibir revisión de arquitectura.
