import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';

const script = await readFile(new URL('../../src/InventarioTrazabilidad/UI/Assets/operations.js', import.meta.url), 'utf8');
const settle = () => new Promise((resolve) => setImmediate(resolve));

function harness(savedAddress = '') {
    const events = new Map();
    const requests = [];
    const source = { selectedOptions: [{ dataset: { addressUrl: '/direcciones/1', destinationLabel: 'Destino del pedido' } }], addEventListener: (name, callback) => events.set(`source:${name}`, callback) };
    const destinations = {
        dataset: { selectedAddress: savedAddress }, value: '', options: [], validity: '',
        replaceChildren(option) { this.options = [option]; this.value = option.value; },
        appendChild(option) { this.options.push(option); if (option.selected) this.value = option.value; },
        setCustomValidity(message) { this.validity = message; },
        addEventListener: (name, callback) => events.set(`destination:${name}`, callback),
    };
    const status = { textContent: '' };
    const form = {
        dataset: {}, querySelector: (selector) => selector === '[data-address-source]' ? source : selector === '[data-address-status]' ? status : destinations,
        addEventListener: (name, callback) => events.set(`form:${name}`, callback),
    };
    class Option {
        constructor(text, value, defaultSelected = false, selected = false) { Object.assign(this, { text, value, defaultSelected, selected }); }
    }
    vm.runInNewContext(script, {
        document: { querySelectorAll: () => [form] }, AbortController, Option,
        fetch: (url, options) => new Promise((resolve, reject) => requests.push({ url, options, resolve, reject })),
    });
    const respond = async (index, addresses) => {
        requests[index].resolve({ ok: true, redirected: false, json: async () => addresses });
        await settle();
    };
    return { events, requests, source, destinations, status, form, respond };
}

test('does not allow confirmation while a destination lookup is pending', async () => {
    const state = harness();
    let prevented = false;
    state.events.get('form:submit')({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.match(state.status.textContent, /Esperá/);
    await state.respond(0, []);
    prevented = false;
    state.events.get('form:submit')({ preventDefault() { prevented = true; } });
    assert.equal(prevented, false);
});

test('restores the previously chosen address after a rejected dispatch', async () => {
    const state = harness('7');
    await state.respond(0, [{ id: 7, label: 'Sucursal', street: 'Calle 7', locality: 'Oberá', province: 'Misiones' }]);
    assert.equal(state.destinations.value, '7');
    assert.equal(state.destinations.validity, '');
});

test('does not silently replace a removed address with the order destination', async () => {
    const state = harness('7');
    await state.respond(0, []);
    assert.equal(state.destinations.value, '7');
    assert.notEqual(state.destinations.validity, '');
    assert.match(state.destinations.options.at(-1).text, /no está disponible/);
    state.destinations.value = '';
    state.events.get('destination:change')();
    assert.equal(state.destinations.validity, '');
});

test('an unavailable address service does not discard a saved destination', async () => {
    const state = harness('7');
    state.requests[0].reject(new Error('Sin conexión'));
    await settle();
    assert.notEqual(state.destinations.validity, '');
    assert.match(state.status.textContent, /No se pudieron cargar/);
});

test('late responses cannot replace the addresses of a different selected customer', async () => {
    const state = harness();
    state.source.selectedOptions = [{ dataset: { addressUrl: '/direcciones/2', destinationLabel: 'Otro destino' } }];
    const next = state.events.get('source:change')();
    assert.equal(state.requests[0].options.signal.aborted, true);
    await state.respond(1, [{ id: 9, label: 'Cliente dos', street: 'Calle 9', locality: 'Posadas', province: 'Misiones' }]);
    await next;
    await state.respond(0, [{ id: 7, label: 'Cliente uno' }]);
    assert.equal(state.destinations.options.some((option) => option.value === '7'), false);
    assert.equal(state.destinations.options.some((option) => option.value === '9'), true);
});
