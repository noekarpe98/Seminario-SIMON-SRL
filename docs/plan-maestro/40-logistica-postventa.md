# Logística y postventa

## Propósito y fuentes

Preparar entregas y documentos, confirmar despachos parciales y registrar devoluciones. El saldo del pedido pertenece a VentasPedidos y el movimiento físico a InventarioTrazabilidad; logística coordina los contratos sin modificar tablas internas de esos contextos.

Fuente SRS-2026-11-04: [texto original suministrado por el usuario](fuentes/2026-11-04-requisitos-originales.txt). Correspondencias y dudas en [requisitos y alcance](02-requisitos-y-alcance.md). La fuente inicial incorporó requisitos al plan; las aclaraciones posteriores autorizan INV-001 y su gestión de permisos ACC-001. Los módulos pospuestos conservan su alcance.

## Lenguaje y fronteras

Contexto propietario: LogisticaPostventa (frontera propuesta). Los contratos del corte autorizado se fijan en INI-002 y la arquitectura; los restantes se precisan al iniciar cada módulo futuro. Se conserva el stack existente; no se crean carpetas ni dependencias con esta documentación.

## Reglas, decisiones pendientes y estado observado

El usuario autorizó implementar baja física y reducción de pares pendientes al confirmar despacho, sin cancelar cuenta corriente monetaria. Se identifica retiro de propietarios sin deducir exención de registro/autorización. Remitos y hojas de ruta actuales son manuales. El 2026-11-18 el usuario dejó fuera del presupuesto RF-013 y LOG-001: emisión e impresión de remitos, rótulos y hojas de ruta, incluidas zonas y formatos (DEC-2026-11-18-01). Devoluciones: seis meses desde fabricación como regla operativa informada, aprobación de Claudia según permiso, reintegro a stock vendible solo si el par es apto y nota negativa sin reposición pendiente.

El estado inicial era un esqueleto sin procesos comerciales. El corte INV-001 incorpora las operaciones descritas en [las aclaraciones](03-decisiones-relevamiento.md); las tareas conservan el trabajo que aún falta.

## Tareas

### [EXCLUIDO] RF-013 — Emitir e imprimir remitos, rótulos y hojas de ruta por zona

- **Capa:** EXCLUIDO
- **Fuente:** SRS-2026-11-04, RF13; aclaraciones posteriores SRS-ACL-2026-11-04; DEC-2026-11-04-05; exclusión presupuestaria expresa del usuario del 2026-11-18; DEC-2026-11-18-01
- **Contexto propietario:** LogisticaPostventa (frontera propuesta)
- **Dependencias:** RF-002, RF-014, LOG-001
- **Alcance:** Generación automática e impresión de remitos de entrega, rótulos de identificación y hojas de ruta agrupadas por zona geográfica.
- **Fuera de alcance:** Optimización de recorridos, GPS o integración con transportistas no solicitados expresamente.
- **Invariantes:** Los documentos corresponden a la entrega, cliente y destino correctos y sus cantidades coinciden con el despacho; definir numeración, regeneración y reimpresión.
- **Contratos y archivos previstos:** Propuesta: contratos de documentos de entrega y agrupación por zona, adaptador de generación/impresión y consultas de destinos/entregas.
- **Criterios de aceptación:** Una entrega genera los tres tipos de documentos con datos y cantidades correctos; varias entregas producen hojas de ruta para las zonas acordadas. Se prueba impresión/reimpresión con las plantillas y dispositivos definidos, sin generar nuevos movimientos de stock.
- **Responsable:** sin asignar
- **Fecha de inicio:** pendiente
- **Última actualización:** 2026-11-18
- **Próximo paso:** no implementar dentro del presupuesto vigente; retomar únicamente con una nueva autorización expresa y su alcance acordado, revisando también LOG-001.
- **Evidencia actual:** Requisito RF13 incorporado originalmente desde la fuente conservada; se mantienen ID, dependencias, alcance y criterios para trazabilidad. El 2026-11-18, ante la consulta sobre zonas y formatos para estos documentos, el usuario indicó «Esto lo dejamos fuera del presupuesto. Ponlo así en el plan maestro». DEC-2026-11-18-01 registra la exclusión de remitos, rótulos y hojas de ruta. RF-013 no se inició porque su prerrequisito LOG-001 seguía abierto; no se declara una entrega funcional.

### [HECHO] RF-014 — Confirmar despachos parciales y reducir el saldo hasta completar el pedido

- **Capa:** SOLICITADO
- **Fuente:** SRS-2026-11-04, RF14; aclaraciones posteriores SRS-ACL-2026-11-04; DEC-2026-11-04-05; autorización del usuario 2026-11-17 «SIGUE CON ESA» tras recomendar RF-014; DEC-2026-11-17-15
- **Contexto propietario:** InventarioTrazabilidad (salida física) y VentasPedidos (saldo/estado); LogisticaPostventa conserva la frontera propuesta de documentos y rutas
- **Dependencias:** INV-001
- **Alcance:** Entregar parte de una nota, registrar cantidades efectivas y actualizar automáticamente su saldo adeudado hasta la finalización.
- **Fuera de alcance:** Cerrar la nota completa por una entrega parcial o descontar mercadería aún no despachada.
- **Invariantes:** Una misma entrega no se descuenta dos veces; no descontar cantidades superiores al saldo según la regla acordada. Validación administrativa, trazabilidad y movimiento físico deben corresponder a la misma operación.
- **Contratos y archivos previstos:** PrepareDispatch/DispatchStock/SqlDispatchStock en InventarioTrazabilidad; DispatchQueue/SqlDispatchQueue en VentasPedidos; composición en AppServiceProvider. MoveStock/SqlOrderFulfillment conservan las escrituras transaccionales. Vistas dispatch-form/dispatch-pagination y OperationsController; pruebas PartialDispatchTest, OrderConcurrencyTest, dispatch-addresses.test.mjs y ensayo pending-orders.mjs --dispatch.
- **Criterios de aceptación:** En una nota de 10 pares, entregar 4 deja 6 pendientes y entregar los 6 restantes cierra el saldo; se prueban parciales por artículo, reintentos y dos confirmaciones concurrentes. Un fallo no deja un saldo o stock definitivo incoherente; la estrategia de coordinación se define antes de implementar.
- **Responsable:** Codex
- **Fecha de inicio:** 2026-11-04
- **Última actualización:** 2026-11-17
- **Próximo paso:** ninguno de implementación en RF-014; cerrada el 2026-11-17. Validar motor/red productivos al preparar operación. RF-003, SEC-001/RNF-003 y los documentos/rutas de RF-013/LOG-001 conservan su alcance y estado; no se inicia otra tarea en este cierre.
- **Evidencia actual:** Base histórica de INV-001: Confirmado → Parcial → Entregado, transacción, CAS e idempotencia, con pruebas iniciales 10→4→6; entonces permanecían recorridos por completar. Cierre del 2026-11-17 bajo DEC-2026-11-17-15:
  - Fábrica/Despachos y Administración/Autorizaciones usan la cola paginada de todas las notas Confirmado/Parcial con saldo, ordenadas por antigüedad y buscables por número literal. Los lotes se filtran por artículos pendientes de la página y lote exacto; 25 filas por página sin recorte a las últimas 200 notas ni 500 lotes. Las descripciones se resuelven por contratos, también después de 500 variantes.
  - Entregas parciales por artículo/lote mantienen los demás saldos y solo cierran la nota al terminar todas las líneas. Cada entrega conserva su propio destino y autorización en la firma; el historial RF-009 muestra las direcciones efectivamente utilizadas. Los reintentos no vuelven a descontar stock ni saldo y no registran cobros.
  - Siete pruebas nuevas PartialDispatchTest: nota antigua seleccionable y autorizable tras 205 notas terminadas; 27 notas/501 lotes con páginas/filtros; descripción de variante fuera de las primeras 500; nota de dos artículos con 4+6 y 3 pares en tres lotes y destinos distintos; reversión de saldo/stock/estado ante fallo de auditoría con recuperación del formulario; permisos en Application/HTTP; filtros inválidos y colas vacías.
  - Cuatro pruebas nuevas con procesos PHP independientes y barrera compartida en SQLite temporal: dos despachos de 4+6 se confirman y completan 10; dos reintentos idénticos devuelven un movimiento; 7+7 no supera saldo 10; 4+4 no supera stock 6. Tras cada escenario se verifican cantidades, estado/revisión y cadena firmada. Se conserva además la prueba de despacho frente a anulación concurrente.
  - Cinco pruebas JavaScript nuevas comprueban espera de direcciones, recuperación del destino elegido, selección explícita si desapareció, error de consulta sin descarte silencioso y rechazo de respuestas atrasadas de otro cliente. Las seis pruebas existentes del refresco permanecen aprobadas.
  - Verificación final: suite completa 135 pruebas/1629 aserciones aprobadas; node --test tests/JavaScript/*.test.mjs aprobó 11 pruebas; npm.cmd run build, node --check, Pint --dirty --test y git diff --check aprobados. Sin dependencias ni migraciones nuevas. La base real no recibió datos sintéticos.
  - node tests/Browser/pending-orders.mjs --dispatch, Chrome con SQLite temporal y 1000 notas iniciales, verificó selección de nota antigua, rechazo de exceso con cantidad/dirección conservadas, parcial 4 y saldo 6, cierre solo después del segundo artículo, destinos históricos y móvil de 390 px sin desbordamiento del documento. Capturas rf014-despachos-desktop.png y rf014-despachos-mobile.png en storage/app/private revisadas; reporte storage/logs/rf014-browser.json. Las ejecuciones iniciales tuvieron esperas agotadas en condiciones del ensayo; la verificación final comprueba saldos renderizados e historia persistida sin depender del aviso temporal de éxito y pasó. Los procesos/perfil/base temporales del ensayo final se cerraron y eliminaron.
  - INV-001 estaba HECHO antes de reanudar; se conserva su regresión. El cierre acredita los criterios físicos de RF-014, no remitos/hojas de ruta, devoluciones, revisión visual general UI-001, hardware industrial ni comportamiento de MySQL/red productivos.

### [PENDIENTE] RF-015 — Registrar devoluciones, notas de pedido negativas y reintegro de stock

- **Capa:** SOLICITADO
- **Fuente:** SRS-2026-11-04, RF15; aclaraciones posteriores SRS-ACL-2026-11-04; DEC-2026-11-04-05
- **Contexto propietario:** LogisticaPostventa (frontera propuesta)
- **Dependencias:** RF-014, RNF-003
- **Alcance:** Devoluciones aprobadas por Claudia mediante permiso configurable; plazo operativo de seis meses desde fabricación del calzado. Solo pares aptos se reintegran al stock vendible; nota negativa registra devolución sin generar reemplazo pendiente.
- **Fuera de alcance:** Emitir una conclusión legal, equiparar nota de pedido negativa con nota de crédito fiscal o inventar una política de rechazo.
- **Invariantes:** No reponer pares no aptos al stock vendible ni generar reposiciones pendientes desde la nota negativa. Mantener lote/fabricación, aprobación y operación única; validar aplicabilidad legal por separado.
- **Contratos y archivos previstos:** Propuesta: casos de uso de devolución y contratos de nota negativa en VentasPedidos y reintegro/auditoría en InventarioTrazabilidad; relación con entrega y par por acordar.
- **Criterios de aceptación:** Aplicar la regla operativa desde fabricación con casos dentro/en/fuera del límite, aprobación autorizada y evaluación de aptitud. Una devolución apta genera nota negativa y reintegro una sola vez; una no apta se registra sin sumar stock vendible. Ninguna genera reemplazo pendiente automáticamente.
- **Responsable:** sin asignar
- **Fecha de inicio:** pendiente
- **Última actualización:** 2026-11-04
- **Próximo paso:** implementar el módulo cuando se solicite; conservar estas definiciones sin repetir la consulta de negocio
- **Evidencia actual:** Requisito RF15 incorporado desde la fuente conservada; sin implementación ni pruebas funcionales específicas observadas. Dependencias, contratos y casos de prueba son descomposición técnica del plan, no decisiones comerciales adicionales.

### [EXCLUIDO] LOG-001 — Desarrollar el módulo específico de hojas de ruta

- **Capa:** EXCLUIDO
- **Fuente:** SRS-ACL-2026-11-04; DEC-2026-11-04-05; exclusión presupuestaria expresa del usuario del 2026-11-18; DEC-2026-11-18-01
- **Contexto propietario:** LogisticaPostventa
- **Dependencias:** RF-014
- **Alcance:** Digitalizar las hojas de ruta actualmente manuales en una fase posterior; definir zonas y formatos allí.
- **Fuera de alcance:** Ampliar el trabajo a módulos pospuestos o atribuir aceptación operativa a pruebas locales.
- **Invariantes:** Conservar fuentes, permisos configurables y evidencia; no confundir saldos físicos con dinero ni borrar historia.
- **Contratos y archivos previstos:** Contratos/casos de uso del contexto, adaptadores y pruebas pertinentes; documentación del mismo plan.
- **Criterios de aceptación:** Agrupar las entregas correctas por las zonas definidas, conservar cantidades y destinos y generar documentos reproducibles; optimización de recorridos solo si se solicita.
- **Responsable:** codex-rf013-01a1f00b
- **Fecha de inicio:** 2026-11-18
- **Última actualización:** 2026-11-18
- **Próximo paso:** no desarrollar el módulo dentro del presupuesto vigente; cualquier reactivación necesita una nueva autorización expresa y definición de zonas y formatos.
- **Evidencia actual:** Trabajo registrado originalmente a partir de SRS-ACL-2026-11-04. Se reservó y comenzó el 2026-11-18 como prerrequisito de RF-013; se conservan el responsable y la fecha de inicio. El usuario lo dejó fuera del presupuesto junto con RF-013 por DEC-2026-11-18-01. Se detuvo el desarrollo y se retiraron los cambios de código, migración y pruebas preparados en este chat. Las verificaciones experimentales no acreditan una entrega ni forman parte del producto. No se aplicó ninguna migración ni se cargaron datos en la base real.

### [HECHO] GIT-002 — Conciliar y publicar la PR independiente de RF-014

- **Capa:** SOLICITADO
- **Fuente:** solicitudes del usuario de separar RF-014, publicar su rama y PR y continuar la tarea el 2026-11-18.
- **Contexto propietario:** Integración Git de InventarioTrazabilidad y VentasPedidos.
- **Dependencias:** RF-014
- **Alcance:** resolver conflictos de feature/R014 con main y publicar la entrega parcial compatible con Livewire, autorizaciones y picking existentes.
- **Fuera de alcance:** documentos logísticos, devoluciones, cambios del kit y modificación de permisos del repositorio original.
- **Invariantes:** conservar stock, saldo, auditoría e idempotencia; mantener los contratos remotos y el control de pares identificados; no sobrescribir otros cambios locales.
- **Contratos y archivos previstos:** PrepareDispatch, DispatchQueue, DispatchStock, OperationsController, vistas, pruebas y PR #3.
- **Criterios de aceptación:** rama sin conflictos con main; pruebas de parciales, permisos, direcciones y concurrencia aprobadas junto con la regresión pertinente; PR publicada lista para revisión.
- **Responsable:** codex-pr-r010-r014-01a1eb43
- **Fecha de inicio:** 2026-11-18
- **Última actualización:** 2026-11-18
- **Próximo paso:** ninguno de conciliación; PR #3 lista para revisión. La fusión requiere permiso del repositorio original.
- **Evidencia actual:** conciliación sobre b164f2f preserva Livewire, ManagementActions, las autorizaciones paginadas, picking y los contratos de RF-010. La cola se incorpora a Despachos. 33 pruebas específicas/326 aserciones y suite completa de 215 pruebas/2108 aserciones aprobadas; 10 pruebas JavaScript, build Vite, Pint y diff sin errores. Chrome con SQLite temporal y 1000 notas verificó nota antigua, rechazo de exceso, dirección conservada, parciales 4+6, cierre por todos los artículos, historia por destino y móvil de 390 px sin desbordamiento; capturas de escritorio/móvil revisadas y limpieza completada. El ensayo se adaptó al mensaje de saldo/picking ya existente en main; no se cambió esa regla. No acredita motor/red productivos. PR [#3](https://github.com/stefanoreichert/Seminario-SIMON-SRL/pull/3) publicada desde el fork y lista para revisión, sin conflictos con main.

### [HECHO] GIT-003 — Publicar la exclusión presupuestaria de RF-013 y LOG-001

- **Capa:** SOLICITADO
- **Fuente:** solicitud del usuario del 2026-11-18 de subir el trabajo a sus ramas remotas y abrir las PR; DEC-2026-11-18-01
- **Contexto propietario:** coordinación Git y documentación del plan
- **Dependencias:** ninguna
- **Alcance:** verificar y publicar únicamente la decisión presupuestaria y su reflejo en el plan y visor desde una rama propia hacia main.
- **Fuera de alcance:** implementar los documentos excluidos, modificar dependencias, integrar la PR o publicar los cambios del kit en esta entrega.
- **Invariantes:** conservar los IDs, el historial de RF-014, los cambios guardados y la interfaz existente del visor.
- **Contratos y archivos previstos:** docs/decisiones/README.md; docs/plan-maestro/40-logistica-postventa.md, README.md y visualizacion.html; rama codex/rf-013-documentos-logistica y PR.
- **Criterios de aceptación:** plan válido, diff exclusivamente documental, rama publicada y PR abierta contra main del repositorio principal con alcance y validación documentados.
- **Responsable:** codex-publicacion-01a1f00b
- **Fecha de inicio:** 2026-11-18
- **Última actualización:** 2026-11-18
- **Próximo paso:** ninguno de publicación; PR #4 abierta para revisión. La integración en main corresponde al responsable del repositorio.
- **Evidencia actual:** rama codex/rf-013-documentos-logistica publicada en personal, con los cuatro archivos documentales revisados. PR [#4](https://github.com/stefanoreichert/Seminario-SIMON-SRL/pull/4) abierta contra main de stefanoreichert/Seminario-SIMON-SRL. Validador del plan aprobado con esquema 1 y 39 tareas; git diff --check aprobado. Se conserva la interfaz del visor y no se publica implementación logística ni cambios de dependencias. RF-010 y RF-014 ya están integradas mediante las PR #2 y #3. La integración conectada no permitió crear la PR; la API oficial con la autenticación Git de noekarpe98 confirmó su apertura.
