import test from 'node:test';
import assert from 'node:assert/strict';
import '../skills/manage-project-roadmap/assets/roadmap-layout.js';
const task = (id, dependencies = []) => ({id, dependencies});

test('separated layout stacks roots and keeps a shared descendant unique', () => {
  const tasks = [task('A-001'), task('B-001'), task('A-002', ['A-001']), task('B-002', ['B-001']), task('C-001', ['A-002', 'B-002']), task('C-002', ['C-001'])];
  const levels = new Map([['A-001', 0], ['B-001', 0], ['A-002', 1], ['B-002', 1], ['C-001', 2], ['C-002', 3]]);
  const result = RoadmapLayout.layout(tasks, levels, true);
  assert.deepEqual(result.labels.map((label) => label.id), ['A-001', 'B-001']);
  assert.equal(result.positions.size, tasks.length);
  assert.ok(result.positions.get('A-001').y < result.positions.get('B-001').y);
  for (const item of tasks) {
    const pos = result.positions.get(item.id);
    assert.ok(pos.x >= 0 && pos.y >= 0 && pos.x + 196 <= result.bounds.width && pos.y + 106 <= result.bounds.height);
    for (const prerequisite of item.dependencies) assert.ok(result.positions.get(prerequisite).x < pos.x);
  }
  const cells = [...result.positions.values()].map(({x, y}) => `${x}:${y}`);
  assert.equal(new Set(cells).size, cells.length);
});

test('filtered roots and isolated tasks remain arranged without hidden nodes', () => {
  const tasks = [task('A-002', ['A-001']), task('A-003', ['A-002']), task('D-001')];
  const levels = new Map([['A-002', 1], ['A-003', 2], ['D-001', 0]]);
  const result = RoadmapLayout.layout(tasks, levels, true);
  assert.deepEqual(result.labels.map((label) => label.id), ['A-002', 'D-001']);
  assert.equal(result.positions.size, 3);
  assert.ok(!result.positions.has('A-001'));
});

test('empty and compact layouts preserve bounds and every task', () => {
  assert.equal(RoadmapLayout.layout([], new Map(), true).positions.size, 0);
  const tasks = [task('A-001'), task('B-001'), task('A-002', ['A-001'])];
  const levels = new Map([['A-001', 0], ['B-001', 0], ['A-002', 1]]);
  const result = RoadmapLayout.layout(tasks, levels);
  assert.equal(result.positions.size, 3);
  assert.equal(result.labels.length, 0);
  assert.ok(result.positions.get('A-001').x < result.positions.get('A-002').x);
});

test('module layout keeps each task once and separates bands while preserving dependency direction', () => {
  const tasks = [
    {id:'A-001', module:'a', dependencies:[]}, {id:'B-001', module:'b', dependencies:[]},
    {id:'A-002', module:'a', dependencies:['A-001', 'B-001']}, {id:'B-002', module:'b', dependencies:['A-002']},
  ];
  const levels = new Map([['A-001', 0], ['B-001', 0], ['A-002', 1], ['B-002', 2]]);
  const result = RoadmapLayout.layout(tasks, levels, 'modules', [{id:'a', title:'Módulo A'}, {id:'b', title:'Módulo B'}]);
  assert.deepEqual(result.labels.map((item) => item.title), ['Módulo A', 'Módulo B']);
  assert.equal(result.positions.size, tasks.length);
  assert.ok(result.positions.get('A-001').y < result.positions.get('B-001').y);
  assert.ok(result.positions.get('A-002').y < result.positions.get('B-002').y);
  for (const item of tasks) for (const parent of item.dependencies) assert.ok(result.positions.get(parent).x < result.positions.get(item.id).x);
  for (const {x, y} of result.positions.values()) assert.ok(x + 196 <= result.bounds.width && y + 106 <= result.bounds.height);
});

test('cross-module connections respond to task, group and display mode', () => {
  const a = {id:'A-001', module:'a'}, b = {id:'B-001', module:'b'}, c = {id:'C-001', module:'a'};
  assert.equal(RoadmapLayout.edgeVisible('selected', a, b, null, null), false);
  assert.equal(RoadmapLayout.edgeVisible('selected', a, b, 'A-001', null), true);
  assert.equal(RoadmapLayout.edgeVisible('selected', a, b, 'C-001', null), false);
  assert.equal(RoadmapLayout.edgeVisible('selected', a, b, null, 'b'), true);
  assert.equal(RoadmapLayout.edgeVisible('selected', a, b, null, 'c'), false);
  assert.equal(RoadmapLayout.edgeVisible('all', a, b, null, null), true);
  assert.equal(RoadmapLayout.edgeVisible('none', a, b, 'A-001', 'a'), false);
  assert.equal(RoadmapLayout.edgeVisible('none', a, c, null, null), true);
});
