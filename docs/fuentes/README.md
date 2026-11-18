# Fuentes de Seminario SIMON SRL

## Inventario conocido y vigencia

- [SRS-2026-11-04 original](../plan-maestro/fuentes/2026-11-04-requisitos-originales.txt): texto aportado por el usuario con RF01–RF17 y RNF 01–RNF 03; copia íntegra y SHA-256 en el registro del plan.
- [Aclaraciones posteriores](../plan-maestro/03-decisiones-relevamiento.md): registro normalizado de decisiones del chat, incluidas la autorización de altas/despachos, permisos configurables y postergaciones.
- Solicitud posterior de traslado a src: decisión directa del usuario, registrada en DEC-2026-11-04-06 y REF-001.
- [Promt-AI, commit 04f621f12421e819bfeb1cc1518c4e864617d14e](https://github.com/wj-004/Promt-AI/tree/04f621f12421e819bfeb1cc1518c4e864617d14e): referencia técnica para arquitectura/kit; no agrega reglas comerciales.
- Código, Composer y pruebas: evidencia observable de implementación; no sustituyen requisitos de negocio.
- Papel, Excel y otro sistema: fuentes adicionales informadas por el usuario, con exportaciones expresamente pospuestas a DAT-001. No se presume que sean inexistentes ni se requieren para este traslado.

Las instrucciones y aclaraciones directas más recientes del usuario conservan su prioridad y alcance. La actualización del kit no invalida autorizaciones posteriores ni reabre una consulta redundante. Este inventario registra las fuentes conocidas y pendientes, sin afirmar que el usuario haya confirmado que no existen otras. Al aportar exportaciones u otras fuentes se conservan origen, fecha, diferencias y conciliación.

## Manual de trabajo en equipo con Git y GitHub

- **ID:** DOC-GIT-2026-11-17.
- **Recepción y solicitud:** 2026-11-17; el usuario aportó el PDF y pidió agregarlo a la documentación para trabajar en conjunto.
- **Original recibido:** `C:/Users/Noe/Downloads/Manual_Git_GitHub_Trabajo_en_Equipo.pdf`.
- **Copia íntegra:** [Manual Git y GitHub - Trabajo en equipo](Manual_Git_GitHub_Trabajo_en_Equipo.pdf), tres páginas, 96230 bytes.
- **SHA-256 del original y de la copia:** `d5ba53fb6ca133aef58d868476fc31c972e348313ef6708c8b68b26b3746d0d4`.
- **Contenido:** acceso al repositorio, identidad Git, clonación, ramas por tarea, commits, Pull Requests, revisión, integración y resolución de conflictos.
- **Aplicación al proyecto:** [guía de trabajo en equipo](../trabajo-en-equipo.md), enlazada desde el README principal. Conserva la secuencia de RF, la arquitectura y la evidencia del plan; distingue los nombres genéricos de ramas del prefijo `codex/` usado por Codex.
- **Alcance:** incorporación documental. Los comandos, invitaciones y publicaciones del manual son ejemplos, no autorizaciones para ejecutarlos. No se modificaron permisos, remotos, ramas, índice de Git ni estados de tareas funcionales por recibir este archivo.

## Actualización técnica Kit-AI 1.8.0

- Solicitud del usuario del 2026-11-17: actualizar desde https://github.com/wj-004/Kit-AI.git.
- Fuente fijada: [Kit-AI, commit cd0fe95e6c5a6b592b7176d7f9c7cf1e492917b2](https://github.com/wj-004/Kit-AI/tree/cd0fe95e6c5a6b592b7176d7f9c7cf1e492917b2), carpeta kit-inicio-proyecto; versión 1.8.0 verificada en el instalador.
- Sustituye la referencia técnica vigente del kit 1.1.0; los enlaces Promt-AI anteriores conservan su valor histórico y no se borran decisiones, autorizaciones ni evidencia de negocio.
- Conciliación local: instrucciones Laravel/Filament, src y Simon\ preservados; reservas locales de tareas, lanzador y visor actualizados. Las adaptaciones Windows/ES modules, hashes y resultados se registran en KIT-002, DEC-2026-11-17-16 y .agents/project-kit.json.
- La actualización no autoriza cambios funcionales ni publicaciones. Los informes EVALUACION del kit son evidencia de su autor, no mediciones de SIMON SRL.
