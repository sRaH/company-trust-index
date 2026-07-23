import { Controller } from '@hotwired/stimulus';

const DEBOUNCE_MS = 200;

export default class extends Controller {
    static targets = ['input', 'results'];

    static values = {
        url: { type: String, default: '/companies/search' },
        minLength: { type: Number, default: 1 },
    };

    connect() {
        this._timer = null;
        this._activeIndex = -1;
    }

    disconnect() {
        clearTimeout(this._timer);
    }

    onInput() {
        clearTimeout(this._timer);
        this._timer = setTimeout(() => this._search(), DEBOUNCE_MS);
    }

    onKeydown(event) {
        if (this.resultsTarget.hidden) {
            return;
        }
        const count = this.resultsTarget.children.length;
        if (count === 0) {
            return;
        }
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this._setActive((this._activeIndex + 1) % count);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            this._setActive((this._activeIndex - 1 + count) % count);
        } else if (event.key === 'Enter' && this._activeIndex >= 0) {
            event.preventDefault();
            this._selectByIndex(this._activeIndex);
        } else if (event.key === 'Escape') {
            this._hide();
        }
    }

    onFocus() {
        if (this.resultsTarget.children.length > 0) {
            this._show();
        }
    }

    onBlur() {
        setTimeout(() => this._hide(), 150);
    }

    async _search() {
        const query = this.inputTarget.value.trim();
        if (query.length < this.minLengthValue) {
            this._hide();
            return;
        }
        try {
            const res = await fetch(`${this.urlValue}?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json' },
            });
            const names = await res.json();
            this._render(Array.isArray(names) ? names : []);
        } catch {
            this._hide();
        }
    }

    _render(names) {
        this.resultsTarget.replaceChildren();
        if (names.length === 0) {
            this._hide();
            return;
        }
        for (const name of names) {
            const item = document.createElement('li');
            item.className = 'list-group-item list-group-item-action';
            item.setAttribute('role', 'option');
            item.textContent = name;
            item.addEventListener('mousedown', (event) => {
                event.preventDefault();
                this._choose(item);
            });
            this.resultsTarget.appendChild(item);
        }
        this._activeIndex = -1;
        this._show();
    }

    _choose(item) {
        const value = item.textContent ?? '';
        this.inputTarget.value = value;
        this._hide();
        this.inputTarget.focus();
        this.dispatch('choose', { detail: { value } });
    }

    _selectByIndex(index) {
        const item = this.resultsTarget.children[index];
        if (item) {
            this._choose(item);
        }
    }

    _setActive(index) {
        const items = this.resultsTarget.children;
        for (const el of items) {
            el.classList.remove('is-active');
        }
        this._activeIndex = index;
        const active = items[index];
        if (active) {
            active.classList.add('is-active');
            active.scrollIntoView({ block: 'nearest' });
        }
    }

    _show() {
        this.resultsTarget.hidden = false;
    }

    _hide() {
        this.resultsTarget.hidden = true;
        this._activeIndex = -1;
    }
}
