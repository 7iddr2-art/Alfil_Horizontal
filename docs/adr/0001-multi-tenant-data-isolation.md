# ADR-0001: Aislamiento multi-tenant en una base de datos compartida

- **Estado:** Diseño objetivo aprobado por producto y arquitectura según confirmación del usuario (2026-10-09); prototipo de FK compuesta validado en MySQL 8.4. El usuario autorizó crear el esquema destino desde cero, sin trasladar ni cargar datos del conjunto demo. Se implementó una primera migración vacía de tenants/membresías; todavía no implementa ni acredita aislamiento multi-tenant.
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
| Nueva membresía y alcance de membresía | Tenant; asociación a copropiedad cuando sea limitada. | FK a identidad y tenant; unicidad usuario-tenant según el modelo acordado; acceso a copropiedad validado mediante asociación explícita. |
| `propiedades_horizontales` | Tenant + copropiedad. | Añadir `tenant_id` y clave candidata `(tenant_id, id)` para relaciones hijas. |
| `viviendas` | Tenant + copropiedad. | Añadir vínculo a propiedad horizontal (hoy ausente) y FK compuesta que impida vivienda bajo tenant incorrecto. |
| `copropietarios` | Tenant-scoped por defecto por contener PII; no asumir que una persona es compartible entre clientes. | Asociaciones propietario-vivienda llevan tenant y copropiedad y validan ambos extremos. |
| `vivienda_copropietarios` | Tenant + copropiedad. | FKs compuestas a vivienda y persona; revisar unicidad histórica para permitir titularidades con vigencia en vez de una sola asociación permanente. |
| `residentes` | Tenant + copropiedad, vinculada a vivienda. | FK compuesta a vivienda; verificar tenant/copropriedad en consultas de perfil y reserva. |
| `parqueaderos` | Tenant + copropiedad. | Añadir propiedad horizontal y cambiar unicidad global de `code` sólo tras acordar alcance natural. |
| `asignaciones_parqueaderos`, `pagos_parqueaderos` | Tenant + copropiedad. | Todas las FKs a parqueadero, persona, residente, vivienda y asignación deben validar el mismo ámbito; pagos heredan/validan el ámbito de la asignación. |
| `personal_administraciones`, `polizas_aseguramiento` | Tenant + copropiedad. | Reemplazar FK simple a propiedad por relación compuesta y acotar claves únicas a la propiedad/tenant cuando aplique. |
| Reuniones (concepto futuro; no existe tabla en las migraciones actuales) | Tenant + copropiedad. | Al incorporarlas, añadir la propiedad convocante; `created_by` refiere a identidad global, pero cada operación exige membresía vigente y permiso. |
| `access_audits` | Revisar separación entre autenticación global y auditoría de negocio tenant-scoped. | Eventos de login previos a seleccionar tenant pueden carecer de tenant sólo si se clasifican explícitamente como globales; auditoría de operaciones/exportaciones siempre registra tenant y alcance. |

### Resolución del contexto y autorización

- El tenant activo se resuelve en servidor desde una identidad autenticada y una membresía activa comprobada en cada petición.
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
- La migración inicial crea `tenants` y `tenant_memberships` sin insertar registros. Una membresía contiene únicamente tenant, identidad, estado y vigencia; no incorpora roles globales ni concede por sí misma acceso a recursos. La relación explícita de membresía con copropiedades y las claves compuestas de las tablas operativas quedan para incrementos posteriores, antes de exponer esos datos.

## Validación técnica realizada

Un prototipo desechable en la base de desarrollo MySQL 8.4 creó tablas InnoDB para tenants, copropiedades y unidades. Verificó que:

1. la relación de una unidad a una copropiedad del mismo tenant se acepta;
2. una unidad con tenant distinto al de su copropiedad se rechaza mediante FK compuesta.

Las tablas de prueba se eliminaron después y se comprobó que la base `alfil_horizontal_dev` conserva únicamente su tabla previa `cake_migrations`. Este prototipo valida la capacidad del motor, no constituye revisión del modelo completo ni autorización para migrar datos.

## Registro de revisión de arquitectura

| Fecha | Artefacto | Resultado | Registro |
|---|---|---|---|
| 2026-10-09 | ADR-0001, dirección de aislamiento multi-tenant | Aprobado | El usuario confirmó que la persona revisora aprobó el ADR. No se recibieron comentarios, condiciones ni nombre para registrar; no inferir que el DER detallado o cada punto de la lista de verificación recibió aprobación individual. |

## Pendientes de diseño e implementación

- Completar la revisión del diagrama entidad-relación, cardinalidades, nulabilidad y tablas globales vs. tenant-scoped antes de migraciones; el ADR de dirección ya fue aprobado. Registrar por separado la fecha, observaciones, decisiones y responsable de cada hallazgo del diseño detallado.
- Confirmar la clasificación inicial de tablas anterior, en especial la ausencia actual de relación vivienda-copropiedad, la identidad de personas/PII y la separación de auditoría de login frente a auditoría de negocio.
- Definir cómo una membresía cubre copropiedades múltiples y cómo aplican delegaciones/suplencias.
- Definir la identidad y los límites de privilegio del operador SaaS, incluyendo el tratamiento del `super_admin` histórico, suplantación/acceso de soporte y revocación inmediata.
- Acordar índices únicos por dominio; revisar identidades de usuario y personas/PII.
- Diseñar constraints compuestos para todas las relaciones operativas y validar tamaños/tipos idénticos en claves referenciadas.
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
5. Aprobar el tratamiento de titularidad histórica, la relación vivienda-copropiedad que hoy falta y la asignación de parqueaderos.
6. Acordar migración por etapas, backfill y reconciliación para un futuro conjunto de datos autorizado, sin usar el dataset demo actual.
7. Verificar aislamiento en consultas y servicios además de controladores, incluyendo jobs, exportaciones, caché, archivos y auditoría.
8. Identificar riesgos y tareas de seguimiento; documentar responsables y pruebas de aceptación antes de comenzar las migraciones.
