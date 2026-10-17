export class FakeClassList {
    constructor(value = '') {
        this.values = new Set(value.split(/\s+/).filter(Boolean));
    }

    add(value) {
        this.values.add(value);
    }

    remove(value) {
        this.values.delete(value);
    }

    contains(value) {
        return this.values.has(value);
    }
}

export class FakeElement {
    constructor({
        id = '',
        dataset = {},
        className = '',
        textContent = '',
        value = '',
        checked = false,
        requiredSelects = [],
    } = {}) {
        this.id = id;
        this.dataset = { ...dataset };
        this.classList = new FakeClassList(className);
        this.textContent = textContent;
        this.value = value;
        this.checked = checked;
        this.requiredSelects = requiredSelects;
        this.listeners = new Map();
        this.attributes = {};
        this.disabled = false;
        this.inert = false;
        this.ownerDocument = null;
    }

    addEventListener(type, listener) {
        const listeners = this.listeners.get(type) ?? [];
        listeners.push(listener);
        this.listeners.set(type, listeners);
    }

    click() {
        for (const listener of this.listeners.get('click') ?? []) {
            listener({ type: 'click' });
        }
    }

    setAttribute(name, value) {
        this.attributes[name] = value;
    }

    querySelectorAll(selector) {
        return selector === 'select[required]' ? this.requiredSelects : [];
    }

    getBoundingClientRect() {
        return {};
    }

    replaceWith(element) {
        element.ownerDocument = this.ownerDocument;
        this.ownerDocument.elements.set(element.id, element);
        this.ownerDocument.pollingFragment = element;
    }
}

export class FakeDocument {
    constructor({ elements = [], steps = [], pollingFragment = null } = {}) {
        this.elements = new Map(elements.map(element => [element.id, element]));
        this.steps = steps;
        this.pollingFragment = pollingFragment;
        this.listeners = new Map();
        this.hidden = false;
        this.readyState = 'complete';

        for (const element of elements) {
            element.ownerDocument = this;
        }

        if (pollingFragment) {
            pollingFragment.ownerDocument = this;
        }
    }

    addEventListener(type, listener) {
        const listeners = this.listeners.get(type) ?? [];
        listeners.push(listener);
        this.listeners.set(type, listeners);
    }

    dispatchEvent(event) {
        for (const listener of this.listeners.get(event.type) ?? []) {
            listener(event);
        }
    }

    getElementById(id) {
        return this.elements.get(id) ?? null;
    }

    querySelector(selector) {
        return selector === '[data-monitoring-poll-url]' ? this.pollingFragment : null;
    }

    querySelectorAll(selector) {
        return selector === '.wizard-step' ? this.steps : [];
    }
}

export class FakeDOMParser {
    parseFromString(html) {
        const tag = html.match(/<([\w-]+)\b([^>]*)>([\s\S]*?)<\/\1>/);
        const attributes = {};
        let index = 0;

        while (index < tag[2].length) {
            while (/\s/.test(tag[2][index] ?? '')) {
                index++;
            }

            const nameStart = index;

            while (/[\w-]/.test(tag[2][index] ?? '')) {
                index++;
            }

            if (index === nameStart) {
                index++;
                continue;
            }

            const name = tag[2].slice(nameStart, index);

            while (/\s/.test(tag[2][index] ?? '')) {
                index++;
            }

            if (tag[2][index] !== '=') {
                continue;
            }

            index++;

            while (/\s/.test(tag[2][index] ?? '')) {
                index++;
            }

            if (tag[2][index] !== '"') {
                while (index < tag[2].length && !/\s/.test(tag[2][index])) {
                    index++;
                }

                continue;
            }

            index++;
            const valueStart = index;
            const valueEnd = tag[2].indexOf('"', index);

            if (valueEnd === -1) {
                break;
            }

            attributes[name] = tag[2].slice(valueStart, valueEnd);
            index = valueEnd + 1;
        }

        const dataset = Object.fromEntries(
            Object.entries(attributes)
                .filter(([name]) => name.startsWith('data-'))
                .map(([name, value]) => [
                    name.slice(5).replace(/-([a-z])/g, (_, letter) => letter.toUpperCase()),
                    value,
                ])
        );
        const element = new FakeElement({
            id: attributes.id,
            dataset,
            className: attributes.class ?? '',
            textContent: tag[3].trim(),
        });

        return new FakeDocument({ elements: [element] });
    }
}

export function crearFragmentoMonitoreo({ className = '', textContent = 'Actual' } = {}) {
    return new FakeElement({
        id: 'monitoring-fragment',
        dataset: {
            monitoringPollUrl: '/poll',
            monitoringPollInterval: '1000',
        },
        className,
        textContent,
    });
}

export function crearDocumentoWizard({ stepCount = 2, requiredValue = 'completo' } = {}) {
    const steps = Array.from({ length: stepCount }, (_, index) => {
        const requiredSelect = new FakeElement({ value: requiredValue });

        return new FakeElement({
            className: index === 0 ? 'active' : '',
            requiredSelects: [requiredSelect],
        });
    });
    const elements = [
        new FakeElement({ id: 'wizard-steps' }),
        new FakeElement({ id: 'wizard-prev', textContent: 'Anterior' }),
        new FakeElement({ id: 'wizard-next', textContent: 'Siguiente' }),
        new FakeElement({ id: 'wizard-current-step' }),
        new FakeElement({ id: 'wizard-total-steps' }),
        new FakeElement({ id: 'wizard-completion' }),
        new FakeElement({ id: 'wizard-progress-bar' }),
        new FakeElement({ id: 'wizard-tamanio', value: 'mediana' }),
        new FakeElement({ id: 'wizard-luz', value: 'media' }),
        new FakeElement({ id: 'wizard-riego', value: 'semanal' }),
        new FakeElement({ id: 'wizard-ambiente', value: 'interiores' }),
        new FakeElement({ id: 'wizard-nivel', value: 'principiante' }),
        new FakeElement({ id: 'wizard-estetica', value: 'follaje' }),
        new FakeElement({ id: 'wizard-toxicidad', checked: true }),
    ];

    return new FakeDocument({ elements, steps });
}

export function instalarEntornoDom(document, { fetch, reducedMotion = true } = {}) {
    const keys = [
        'document',
        'HTMLElement',
        'DOMParser',
        'window',
        'fetch',
        'alert',
        'PERFIL_UPDATE_URL',
        'CSRF_TOKEN',
        'CLIENT_HOME_URL',
    ];
    const previous = new Map(keys.map(key => [key, Object.getOwnPropertyDescriptor(globalThis, key)]));
    const intervals = [];
    const timeouts = [];

    Object.assign(globalThis, {
        document,
        HTMLElement: FakeElement,
        DOMParser: FakeDOMParser,
        fetch,
        alert: () => {},
        PERFIL_UPDATE_URL: '/perfil/update',
        CSRF_TOKEN: 'csrf-de-prueba',
        CLIENT_HOME_URL: '/home',
        window: {
            location: { href: '' },
            matchMedia: () => ({ matches: reducedMotion }),
            setInterval: callback => {
                intervals.push(callback);
                return intervals.length;
            },
            setTimeout: callback => {
                timeouts.push(callback);
                return timeouts.length;
            },
        },
    });

    return {
        intervals,
        timeouts,
        restore() {
            for (const [key, descriptor] of previous) {
                if (descriptor) {
                    Object.defineProperty(globalThis, key, descriptor);
                } else {
                    delete globalThis[key];
                }
            }
        },
    };
}
