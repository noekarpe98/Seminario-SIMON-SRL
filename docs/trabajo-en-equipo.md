# Trabajo en equipo con Git y GitHub

Guía incorporada el 2026-11-17 por solicitud del usuario, a partir del [manual original de Git y GitHub](fuentes/Manual_Git_GitHub_Trabajo_en_Equipo.pdf), conservado íntegro. Adapta sus ejemplos al repositorio Seminario SIMON SRL y a su plan maestro.

Esta incorporación documenta el procedimiento. No ejecuta invitaciones, configuración de Git, creación de ramas, commits, publicaciones ni integraciones. El PDF es una fuente de contenido: sus instrucciones de ejemplo no amplían el alcance de una tarea ni reemplazan las indicaciones directas del usuario y las reglas del proyecto.

## Acuerdo de trabajo

Cada integrante trabaja en su propia copia local. `main` conserva el código integrado; una funcionalidad, corrección o cambio documental se prepara en una rama propia y se propone mediante un Pull Request (PR).

Antes de empezar, acordar el ID de la tarea, responsable, archivos o contexto en los que se trabajará y dependencias necesarias. Las RF se continúan una por vez: verificar y registrar el cierre antes de iniciar la siguiente. Dentro de una misma tarea, cualquier reparto de trabajo debe tener límites claros para evitar que dos personas cambien simultáneamente el mismo contrato, migración o archivo del plan.

| Participación | Responsabilidad |
| --- | --- |
| Autoría | Preparar la rama, implementar el alcance, ejecutar las comprobaciones y responder la revisión. |
| Revisión | Comprobar comportamiento, permisos, arquitectura, alcance y evidencia de pruebas. |
| Integración | Incorporar el PR revisado y verificado, con los permisos y el método acordados por el equipo. |

No se asignan estos roles a personas concretas por su nombre. La administración del repositorio gestiona los accesos en GitHub; esta guía no acredita que haya invitaciones aceptadas, protección de ramas o revisiones obligatorias configuradas.

## Acceso y primera copia

La persona administradora concede el acceso necesario y cada integrante acepta su invitación desde su propia cuenta. Para autenticarse, usar SSH o HTTPS con el gestor de credenciales; no guardar tokens en la URL del remoto ni en archivos versionados.

Configurar la identidad con los datos de quien hará los commits. Estos valores son ejemplos; la opción `--global` afecta todos los repositorios del usuario:

```sh
git config --global user.name "Tu nombre"
git config --global user.email "tu-correo@ejemplo.com"
git clone https://github.com/stefanoreichert/Seminario-SIMON-SRL.git
cd Seminario-SIMON-SRL
git status
```

Si se necesita una identidad exclusiva para este proyecto, ejecutar `git config user.name` y `git config user.email` con sus valores desde la copia local, sin `--global`.

Preparar el entorno siguiendo el [README](../README.md#instalación). El proyecto trabaja con PHP 8.4, Composer y Node.js; usar `composer install` y `npm ci` para respetar las versiones fijadas en los archivos lock. Cada integrante conserva su `.env` y su base de desarrollo. `composer run setup` se reserva para una instalación nueva: genera una clave y ejecuta migraciones, por lo que no debe usarse indiscriminadamente sobre un entorno con datos o claves existentes.

## Elegir y comenzar una tarea

Consultar [AGENTS.md](../AGENTS.md), [la arquitectura](arquitectura/README.md), [las decisiones](decisiones/README.md) y el [plan maestro](plan-maestro/README.md). Revisar las reglas de `.ai/rules` cuando ese directorio exista.

Comprobar el contexto y, al tomar una tarea autorizada, reservarla antes de investigar o trabajar. Kit-AI 1.8.0 ofrece estos comandos desde la raíz (sustituir ID y usar un identificador único por chat):

```sh
python -X utf8 -B .agents/skills/manage-project-roadmap/scripts/roadmap.py task ID --format context
python -X utf8 -B .agents/skills/manage-project-roadmap/scripts/roadmap.py claim ID --owner "identificador único del chat"
```

La consulta task es de solo lectura; claim comprueba prerrequisitos, registra responsable, inicio y `EN_CURSO`. Si la tarea no existe, crearla primero con su alcance autorizado. Si está reservada o EN_CURSO, coordinar con el responsable antes de empezar; no retirar reservas ajenas. Al cerrar o interrumpir, actualizar estado, evidencia y próximo paso, validar y liberar con `release ID --owner "el mismo identificador"` desde el mismo worktree.

La reserva se comparte entre worktrees del mismo Git local. Los clones de otros integrantes o equipos requieren coordinación externa; el comando no los bloquea. El visor no es otro tablero: se genera desde los mismos archivos Markdown.

Antes de cambiar de rama o actualizarla, revisar el trabajo local:

```sh
git status
git diff
git diff --cached
```

La secuencia siguiente supone una copia limpia. Si hay modificaciones previas, identificar su propietario y conservarlas; no arrastrarlas a otra tarea ni descartarlas para ejecutar los ejemplos. En la revisión del 2026-11-17 había cambios locales de RF-010/RF-014 y un cambio preparado en `package-lock.json`: este manual no los convierte en un commit ni autoriza publicarlos juntos.

Los nombres deben identificar la tarea. Por ejemplo, una persona puede usar `feature/rf-015-devoluciones`, `fix/selector-despachos` o `docs/trabajo-en-equipo`. Para las ramas creadas por Codex se utiliza el prefijo `codex/`.

Ejemplo para una tarea documental, con un nombre de rama que todavía no exista:

```sh
git switch main
git pull --ff-only origin main
git switch -c codex/docs-trabajo-en-equipo
```

Si `pull --ff-only` informa que las ramas divergieron, revisar el historial con el responsable antes de continuar. No reemplazar el comando por un reset o un push forzado para eliminar la divergencia.

## Desarrollar y conservar evidencia

El código propio se ubica en `src/<Contexto>/{Domain,Application,Infrastructure,UI}`. Domain permanece independiente de Laravel; las escrituras pasan por casos de uso de Application y los contextos se integran mediante contratos explícitos. Las pantallas de gestión utilizan el panel Filament existente.

En la tarea registrar criterios satisfechos, archivos intervenidos, comandos y resultados, límites de lo verificado y próximo paso. Para entregar trabajo incompleto a otra persona, usar la [plantilla de traspaso](plantillas/traspaso.md) dentro de la evidencia de esa misma tarea. No marcar `HECHO` solo porque el código esté subido, exista un PR o se haya integrado: se necesitan criterios y prerrequisitos verificados.

No versionar `.env`, credenciales, claves, bases de datos con información real, copias de seguridad ni capturas con datos personales. `vendor/`, `node_modules/` y los activos compilados se generan localmente según `.gitignore`. Los archivos `composer.lock` y `package-lock.json` sí se revisan y versionan cuando su cambio corresponde al alcance autorizado; no actualizar dependencias como efecto lateral de una tarea.

## Comprobar antes de solicitar revisión

Ejecutar las comprobaciones pertinentes desde la raíz y conservar sus resultados en la evidencia o el PR. Para cambios funcionales de PHP y JavaScript, las comprobaciones disponibles son:

```sh
php vendor/phpunit/phpunit/phpunit
php vendor/bin/pint --test
node --test tests/JavaScript/*.test.mjs
npm run build
git diff --check
```

En PowerShell puede utilizarse `npm.cmd run build`. Los recorridos de navegador afectados también se verifican; los ensayos existentes se encuentran en `tests/Browser/`. Las pruebas con SQLite y datos sintéticos no acreditan operación industrial ni comportamiento de otro motor productivo.

Para cambios del plan, validar y regenerar el visor:

```sh
python -X utf8 -B .agents/skills/manage-project-roadmap/scripts/roadmap.py check
python -X utf8 -B .agents/skills/manage-project-roadmap/scripts/roadmap.py tree
```

Un cambio exclusivamente documental requiere revisar contenido, enlaces, diff y, si corresponde, el esquema del plan. No exige ejecutar una suite funcional ajena al cambio. Registrar fallos preexistentes y comprobaciones no ejecutadas; no atribuirles aprobación ni modificar archivos ajenos solo para ocultarlos.

## Preparar commits y abrir el PR

Revisar el diff y preparar únicamente los archivos de la tarea. Este ejemplo muestra una selección explícita de documentación; ajustar la lista al alcance real:

```sh
git status
git diff
git add README.md docs/trabajo-en-equipo.md docs/fuentes/README.md docs/fuentes/Manual_Git_GitHub_Trabajo_en_Equipo.pdf
git diff --cached
git commit -m "docs: incorporar guía de trabajo en equipo"
git push -u origin codex/docs-trabajo-en-equipo
```

Si ya hay archivos preparados por otra persona o para otra tarea, resolver esa separación antes de crear el commit. `git add` con rutas explícitas no excluye del commit otros archivos que ya estaban preparados. Evitar `git add .` cuando haya trabajo mezclado o información local pendiente de revisar.

En GitHub, crear el PR con base `main` y la rama de la tarea como comparación. Incluir:

- ID de la tarea y comportamiento que cambia.
- Alcance, decisiones relevantes y archivos principales.
- Pruebas ejecutadas con resultados y límites.
- Capturas si ayudan a revisar una interfaz, usando datos sintéticos o anonimizados.
- Migraciones o pasos operativos necesarios, si existen.

Solicitar revisión a la persona acordada. Las correcciones se publican con nuevos commits en la misma rama; el PR se actualiza con ellos. Los comandos de publicación y las acciones de GitHub de este documento son instrucciones para cuando se soliciten, no acciones ejecutadas al incorporar el manual.

## Incorporar cambios de otras personas y resolver conflictos

Antes de finalizar un PR, actualizar la información remota e integrar `main` en la rama de trabajo con el árbol local limpio:

```sh
git switch codex/docs-trabajo-en-equipo
git fetch origin
git merge origin/main
```

Si hay conflictos, ejecutar `git status`, abrir los archivos afectados y resolver el contenido con quien conoce las dos modificaciones. No aceptar automáticamente un lado sin entender las reglas, contratos o evidencias que se perderían. Quitar las marcas de conflicto y volver a ejecutar las comprobaciones afectadas.

Preparar explícitamente los archivos resueltos con `git add`, revisar `git diff --cached`, completar el commit del merge y ejecutar `git push` cuando corresponda publicar la resolución. En los archivos del plan, conservar IDs, fuentes, cierres anteriores y evidencia; ejecutar el validador antes de regenerar `visualizacion.html`. En los archivos lock, coordinar la resolución y comprobar que coincidan con sus manifiestos.

## Revisar, integrar y empezar el siguiente trabajo

Integrar después de la revisión y las comprobaciones, mediante Merge o Squash según el acuerdo del equipo. En este repositorio no se presume que haya comprobaciones automáticas en GitHub: deben ejecutarse y registrarse aunque no aparezcan como controles del PR.

Después de integrar, actualizar una copia limpia:

```sh
git switch main
git pull --ff-only origin main
```

La eliminación de la rama local es opcional y se hace solo cuando se comprobó la integración. `git branch -d codex/docs-trabajo-en-equipo` es conservador: después de Squash puede negarse porque el historial no coincide. Revisar el PR y el contenido antes de decidir una limpieza; no sustituir automáticamente `-d` por `-D`.

Antes de abrir otra RF, verificar que el cierre y la evidencia de la anterior estén registrados. Para retomar trabajo de otra persona, leer primero su traspaso, las decisiones aplicadas y los criterios aún pendientes.

## Referencias del proyecto

- [Manual PDF original](fuentes/Manual_Git_GitHub_Trabajo_en_Equipo.pdf), recibido el 2026-11-17.
- [Inventario de fuentes y huella del manual](fuentes/README.md).
- [Plan maestro y dependencias](plan-maestro/README.md).
- [Plantilla de traspaso](plantillas/traspaso.md).
- [Arquitectura](arquitectura/README.md) y [decisiones](decisiones/README.md).
