# Kit de arquitectura y herramientas del plan

### [HECHO] KIT-001 — Integrar el prompt maestro y kit reutilizable de arquitectura hexagonal y DDD

- **Capa:** SOLICITADO
- **Fuente:** solicitud del usuario del 2026-11-04; wj-004/Promt-AI commit e073de35c24e58ba79a9dd376cd610e5f5a21e41; DEC-2026-11-04-01 y DEC-2026-11-04-02
- **Contexto propietario:** herramientas de desarrollo y arquitectura del proyecto
- **Dependencias:** ninguna
- **Alcance:** prompt maestro portable, instalador, tres skills locales, perfiles base/Laravel/Flutter/Go, plantillas, ficha, arquitectura, decisiones, esquema común, validador, reportes, visor autónomo y sincronización local
- **Fuera de alcance:** funcionalidades de negocio, migraciones, nuevas dependencias de producto e integración del visor en el menú de la aplicación
- **Invariantes:** conservar las reglas Laravel y cambios existentes; un único esquema versión 1; plan tratado como datos; ninguna sobrescritura automática de archivos diferentes; servidor solo en loopback y última versión válida ante errores
- **Contratos y archivos previstos:** AGENTS.md; README.md; resources/kit-inicio-proyecto; .agents/skills y metadatos del proyecto; docs/proyecto.md; docs/arquitectura; docs/decisiones; docs/plantillas; docs/plan-maestro
- **Criterios de aceptación:** tres skills válidas; instalación portable e idempotente sin sobrescrituras; rechazo de planes inválidos, ciclos y dependencias inexistentes; exportación autónoma con todos los campos; servidor recupera cambios válidos y rechaza acceso a otros archivos; visor comprobado en navegador; ZIP con manifiesto; requisitos pendientes documentados
- **Responsable:** Codex
- **Fecha de inicio:** 2026-11-04
- **Última actualización:** 2026-11-04
- **Próximo paso:** utilizar las skills y el visor con los requisitos recibidos en SRS-2026-11-04; continuar las definiciones de INI-002 e INI-003. Para otro proyecto, usar el prompt y el kit portable siguiendo su README.
- **Evidencia actual:** origen fijado al commit indicado; AGENTS.md conciliado; correcciones Windows de reserva exclusiva de puerto y enlaces exportados.
  - python -X utf8 -B -m unittest discover -s resources/kit-inicio-proyecto/tests -v: 39 casos, 36 aprobados y 3 omitidos por WinError 1314 al crear enlaces simbólicos; se conserva este límite de verificación.
  - php artisan test: 2 pruebas y 2 aserciones aprobadas; solo comprueban el esqueleto existente.
  - Validador instalado: plan válido, esquema 1, cuatro tareas y dos dependencias; exportación HTML autónoma generada desde el mismo parser. node --check aprobado para el JavaScript original y el instalado.
  - Tres SKILL.md e interfaces openai.yaml validados con las reglas de quick_validate.py y el parser Symfony YAML 8.1.10 ya instalado; PyYAML no estaba disponible y no se agregó ninguna dependencia.
  - Navegador sobre servidor en 127.0.0.1 con puerto asignado: búsqueda INI-002 devuelve una tarea; detalle conserva los catorce campos; cadena completa muestra INI-001 → INI-002 → INI-003; salir restaura la búsqueda; filtro de estado y zoom comprobados. La suite verifica conservación y recuperación de la última versión válida y restricciones HTTP.
  - storage/app/kit-inicio-proyecto.zip: 32 recursos y MANIFEST.sha256; todos sus hashes comprobados. Los 17 recursos de las skills instaladas coinciden con la fuente reutilizable.
  - Al cierre inicial de esta integración, los requisitos, contextos y plan funcional permanecían pendientes por DEC-2026-11-04-03. Después se recibió SRS-2026-11-04 y DEC-2026-11-04-04 actualizó esa postergación; las especificaciones registran el nuevo alcance. Este cierre histórico no acredita funcionalidades comerciales.

### [HECHO] KIT-002 — Actualizar Kit-AI y conciliar sus herramientas con el proyecto

- **Capa:** SOLICITADO
- **Fuente:** pedido del usuario del 2026-11-17 «ACTUALIZA» con https://github.com/wj-004/Kit-AI.git; origen consultado cd0fe95e6c5a6b592b7176d7f9c7cf1e492917b2, versión 1.8.0
- **Contexto propietario:** herramientas de desarrollo y plan maestro
- **Dependencias:** KIT-001
- **Alcance:** actualizar copia portable, skills locales, plantillas, lanzador, visor y registro de procedencia, conciliando las adaptaciones Windows y las reglas propias
- **Fuera de alcance:** modificar funcionalidades, dependencias de producto, datos reales, permisos de GitHub o publicar commits
- **Invariantes:** conservar IDs, estados, decisiones y evidencia; Laravel mantiene src y Simon\; conservar Filament y las reglas Laravel; no sobrescribir cambios locales ajenos ni alterar el índice Git
- **Contratos y archivos previstos:** resources/kit-inicio-proyecto; .agents/skills; .agents/project.json y project-kit.json; AGENTS.md; ver-plan.py; README y documentos de procedencia; visor del plan
- **Criterios de aceptación:** versión y commit fijados; skills instaladas coinciden con su fuente portable; plan esquema 1 conserva tareas previas; pruebas Python/JavaScript y empaquetado aprobados con límites explícitos; visor comprobado en navegador; hashes de código y dependencias previos conservados
- **Responsable:** codex-chat-01a1cc9a-kit-ai-20261117
- **Fecha de inicio:** 2026-11-17
- **Última actualización:** 2026-11-17
- **Próximo paso:** ninguno de actualización en KIT-002; usar ver-plan.py y task/claim/release en los trabajos posteriores. Conservar coordinación externa entre clones/máquinas y la edición persistente deshabilitada por defecto.
- **Evidencia actual:** comparación inicial y copia previa del kit/instrucciones en storage/app/backups/before-kit-ai-cd0fe95e-20261117.zip; manifiesto de preservación de código, dependencias, documentación de colaboración y tareas en el JSON homónimo. Sin cambios funcionales iniciados por esta tarea.
  - Cierre del 2026-11-17: Kit-AI 1.8.0 fijado a cd0fe95e6c5a6b592b7176d7f9c7cf1e492917b2; 48 recursos portables, 24 recursos de skills instaladas idénticos, plantillas conciliadas y lanzador ver-plan.py. DEC-2026-11-17-16 registra alcance y adaptaciones; project-kit.json conserva versión anterior, procedencia y hashes.
  - Claim de KIT-002 obtenido con el identificador único codex-chat-01a1cc9a-kit-ai-20261117 desde la CLI portable nueva, porque la versión anterior no soportaba reservas. Se conservan las 33 tareas anteriores con sus campos/evidencia y se agrega solo esta tarea técnica.
  - Pruebas Python: 92 ejecutadas, resultado final OK con 11 omisiones de casos/subcasos de enlaces simbólicos por WinError 1314. Log storage/logs/kit-ai-1.8-tests-final.log. El primer ensayo detectó CRLF en plantillas y se normalizaron los recursos a LF; otro ensayo HTTP fue interrumpido con WinError 10053 y la suite final fuera del sandbox pasó. No se debilitaron comprobaciones de seguridad; los casos de archivos incompatibles y no UTF-8 se verifican aparte de los enlaces.
  - Node: 11 pruebas de contexto/layout aprobadas; node --check de los tres activos aprobado. Se adaptó una prueba CommonJS a ES modules sin modificar package.json. ZIP local con 48 recursos y MANIFEST.sha256 verificado; su prueba de empaquetado reproducible y reinstalación aislada pasó.
  - Revisión real del visor por HTTP local de solo lectura: búsqueda RF-010, detalle con 14 campos y prerrequisitos, cadena completa, tema oscuro y agrupación por módulo comprobados. Captura storage/app/private/kit-ai-1.8-visor.png. El navegador rechazó file:// y se utilizó el servidor limitado al plan validado, sin publicar archivos arbitrarios ni habilitar --allow-edit. Las pruebas HTTP cubren modo de solo lectura y guardado revisado en repositorios temporales.
  - Manifiesto previo de preservación: 178 archivos controlados. Código propio, dependencias, PDF de colaboración e índice Git conservan sus hashes; solo cambian documentos de coordinación/índice del plan y la especificación del kit. AGENTS conserva las reglas Laravel/Filament/src y agrega claim/release; las fuentes históricas y autorizaciones se mantienen. No se ejecutó regresión funcional del producto porque no cambió su código ni sus dependencias.

### [HECHO] GIT-004 — Conciliar y publicar Kit-AI 1.8.0 y la guía de colaboración

- **Capa:** SOLICITADO
- **Fuente:** solicitud del usuario del 2026-11-18 de publicar el trabajo guardado en sus ramas y abrir las PR correspondientes; actualización autorizada registrada en KIT-002.
- **Contexto propietario:** herramientas y coordinación Git
- **Dependencias:** KIT-002
- **Alcance:** recuperar los cambios del kit y colaboración guardados, conciliarlos con main, comprobar el lanzador y herramientas y publicar su rama y PR independientes.
- **Fuera de alcance:** cambios funcionales, exclusión presupuestaria de RF-013/LOG-001, cambio previo de package-lock.json y fusión de la PR.
- **Invariantes:** preservar src, Simon\, Filament, Livewire, decisiones y evidencia actuales; no sobrescribir funcionalidades remotas ni perder el stash.
- **Contratos y archivos previstos:** .agents; resources/kit-inicio-proyecto; ver-plan.py; AGENTS.md; README.md; documentación de coordinación y procedencia; visor.
- **Criterios de aceptación:** integración sin conflictos pendientes, pruebas pertinentes del kit y visor aprobadas, ausencia de cambios funcionales/dependencias, rama remota publicada y PR abierta contra main.
- **Responsable:** codex-publicacion-01a1f00b
- **Fecha de inicio:** 2026-11-18
- **Última actualización:** 2026-11-18
- **Próximo paso:** ninguno de publicación; PR #5 abierta para revisión. Al integrar junto con la PR #4, conservar ambos registros y regenerar el visor resultante.
- **Evidencia actual:** recuperación selectiva del stash 4bf0ead en un worktree desde origin/main 52030ac; el stash permanece íntegro. Se conciliaron las herramientas 1.8.0 con las acciones actuales del visor y se conservaron todas las reglas Laravel/Filament/Livewire de AGENTS.md. Los scripts y activos instalados coinciden con la copia portable; los tres SKILL.md conservan sus adaptaciones del proyecto y Windows. PDF de colaboración idéntico al guardado (SHA-256 d5ba53fb6ca133aef58d868476fc31c972e348313ef6708c8b68b26b3746d0d4). Suite Python: 92 pruebas, OK con 11 omisiones de enlaces simbólicos en Windows; Node: 11 pruebas aprobadas; sintaxis de los tres activos y git diff --check aprobados. ver-plan.py --help reconoce open/claim/release. Revisión del visor por HTTP local de solo lectura: búsqueda KIT-002, detalle y acciones, cadena de tres tareas, tema oscuro y agrupación por módulo correctos. Sin cambios en src, app, rutas, bases ni dependencias del producto. La exclusión presupuestaria está en la PR #4 y package-lock.json se publica por separado.
  - Publicación verificada el 2026-11-18: rama codex/kit-ai-1-8 subida a personal y PR [#5](https://github.com/stefanoreichert/Seminario-SIMON-SRL/pull/5) abierta contra main del repositorio principal. Se conserva el stash de respaldo y no se fusiona la PR.

### [PARCIAL] GIT-006 — Conciliar e integrar la PR del kit con main

- **Capa:** SOLICITADO
- **Fuente:** solicitud del usuario del 2026-11-18 de solucionar conflictos y hacer merge a main.
- **Contexto propietario:** coordinación Git y herramientas del plan
- **Dependencias:** GIT-004
- **Alcance:** incorporar main a la rama del kit, conservar los cambios presupuestarios y validar la conciliación antes de integrar la PR #5.
- **Fuera de alcance:** integrar cambios de package-lock sin resolver su falla de instalación.
- **Invariantes:** conservar los estados EXCLUIDO y la evidencia de RF-013 y LOG-001, las funciones del visor y las reglas del proyecto.
- **Contratos y archivos previstos:** archivos en conflicto de la PR #5 y visor generado.
- **Criterios de aceptación:** conflictos resueltos, plan y pruebas pertinentes aprobados y PR #5 integrada o impedimento explícito registrado.
- **Responsable:** codex-merge-01a1f00b
- **Fecha de inicio:** 2026-11-18
- **Última actualización:** 2026-11-18
- **Próximo paso:** publicar la conciliación y confirmar la integración de PR #5; registrar el resultado en el plan.
- **Evidencia actual:** PR #4 integrada en 8b5ff7a. Conciliados el registro de decisiones y el visor generado, conservando KIT-002, GIT-003 y las exclusiones RF-013/LOG-001. Plan válido de 42 tareas; 11 pruebas JavaScript aprobadas; 92 pruebas Python aprobadas con 11 omisiones de enlaces simbólicos en Windows. Dos ejecuciones iniciales de la suite Python fallaron con WinError 10053 en la prueba HTTP; la prueba aislada y la suite completa posterior aprobaron sin cambios de código. La PR #6 permanece en borrador por npm ci.
