/* Cálculo de posiciones sin modificar los datos del plan. */
globalThis.RoadmapLayout = (() => {
  "use strict";
  function layout(visible, fullLevels, mode = "compact", modules = []) {
    if (mode === true) mode = "separated";
    if (mode === false) mode = "compact";
    const positions = new Map(), labels = [];
    const levels = [...new Set(visible.map((task) => fullLevels.get(task.id)))].sort((a, b) => a - b);
    const columns = levels.map((value) => visible.filter((task) => fullLevels.get(task.id) === value));
    const xFor = (level) => levels.indexOf(level) * 270 + 24;
    let height;
    if (mode === "compact") {
      const rows = Math.max(1, ...columns.map((column) => column.length));
      columns.forEach((column, columnIndex) => {
        const averageParent = (task) => {
          const ys = task.dependencies.filter((id) => positions.has(id)).map((id) => positions.get(id).y);
          return ys.length ? ys.reduce((sum, y) => sum + y, 0) / ys.length : 0;
        };
        column.sort((a, b) => averageParent(a) - averageParent(b) || a.id.localeCompare(b.id));
        column.forEach((task, row) => positions.set(task.id, {x: columnIndex * 270 + 24, y: 44 + row * 134 + (rows - column.length) * 67}));
      });
      height = rows * 134 + 50;
    } else if (mode === "modules") {
      const groups = new Map();
      for (const task of visible) {
        if (!groups.has(task.module)) groups.set(task.module, []);
        groups.get(task.module).push(task);
      }
      const moduleOrder = modules.map((module) => module.id).filter((id) => groups.has(id));
      for (const id of groups.keys()) if (!moduleOrder.includes(id)) moduleOrder.push(id);
      let top = 44;
      for (const id of moduleOrder) {
        const group = groups.get(id), byLevel = levels.map((value) => group.filter((task) => fullLevels.get(task.id) === value));
        const rows = Math.max(1, ...byLevel.map((column) => column.length));
        labels.push({id, y: top, title: modules.find((module) => module.id === id)?.title || id});
        byLevel.forEach((column, columnIndex) => {
          const averageParent = (task) => {
            const ys = task.dependencies.filter((parent) => positions.has(parent)).map((parent) => positions.get(parent).y);
            return ys.length ? ys.reduce((sum, y) => sum + y, 0) / ys.length : top;
          };
          column.sort((a, b) => averageParent(a) - averageParent(b) || a.id.localeCompare(b.id));
          column.forEach((task, row) => positions.set(task.id, {x: columnIndex * 270 + 24, y: top + 38 + row * 134 + (rows - column.length) * 67}));
        });
        top += 38 + rows * 134 + 52;
      }
      height = Math.max(184, top + 10);
    } else {
      const visibleIds = new Set(visible.map((task) => task.id)), owner = new Map(), groups = new Map();
      // Si varias raíces alcanzan una tarea, se dibuja una sola vez en el primer grupo;
      // las aristas desde las demás raíces siguen conectándose a esa misma tarjeta.
      columns.forEach((column) => column.sort((a, b) => a.id.localeCompare(b.id)).forEach((task) => {
        const parents = task.dependencies.filter((id) => visibleIds.has(id));
        const roots = parents.map((id) => owner.get(id)).filter(Boolean).sort();
        const root = roots[0] || task.id;
        owner.set(task.id, root);
        if (!groups.has(root)) groups.set(root, []);
        groups.get(root).push(task);
      }));
      let top = 44;
      [...groups.keys()].sort().forEach((root) => {
        const group = groups.get(root), byLevel = levels.map((level) => group.filter((task) => fullLevels.get(task.id) === level));
        const rows = Math.max(1, ...byLevel.map((column) => column.length));
        labels.push({id: root, y: top});
        byLevel.forEach((column) => column.forEach((task, row) => {
          positions.set(task.id, {x: xFor(fullLevels.get(task.id)), y: top + 24 + row * 134 + (rows - column.length) * 67});
        }));
        top += 24 + rows * 134 + 40;
      });
      height = Math.max(184, top + 10);
    }
    return {positions, labels, levels, bounds: {width: Math.max(240, levels.length * 270 - 26), height}};
  }
  function edgeVisible(mode, from, to, selectedTask, selectedModule) {
    if (mode === "all" || from.module === to.module) return true;
    if (mode === "none") return false;
    return Boolean(selectedTask && (from.id === selectedTask || to.id === selectedTask) ||
      selectedModule && (from.module === selectedModule || to.module === selectedModule));
  }
  return {layout, edgeVisible};
})();
