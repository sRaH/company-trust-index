import { Controller } from '@hotwired/stimulus';

/**
 * Expandable review text.
 *
 * The full review is always present in the DOM. The preview is clamped to a
 * few lines via CSS; this controller measures whether that clamped preview
 * actually overflows and only then reveals the "read more / show less" toggle.
 * Short reviews therefore stay free of an unnecessary control.
 */
export default class extends Controller {
    static targets = ['text', 'toggle'];

    connect() {
        this._sync = this._sync.bind(this);
        this._onResize = this._onResize.bind(this);
        this._resizeTimer = null;

        // Defer until after the first paint so layout (including the clamp) is ready.
        requestAnimationFrame(this._sync);
        // Web fonts can change line wrapping once loaded; re-measure afterwards.
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(this._sync).catch(() => undefined);
        }
        window.addEventListener('resize', this._onResize);
    }

    disconnect() {
        window.removeEventListener('resize', this._onResize);
        clearTimeout(this._resizeTimer);
    }

    toggle() {
        this.element.classList.toggle('is-expanded');
        this._sync();
    }

    _onResize() {
        clearTimeout(this._resizeTimer);
        this._resizeTimer = setTimeout(this._sync, 150);
    }

    _sync() {
        if (!this.hasToggleTarget) {
            return;
        }
        const expanded = this.element.classList.contains('is-expanded');
        const overflow = this.textTarget.scrollHeight > this.textTarget.clientHeight + 1;

        // Keep the toggle visible while expanded (so it can collapse back), and
        // reveal it on collapse only when there is hidden content to show.
        this.toggleTarget.hidden = !overflow && !expanded;
        this.toggleTarget.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        this.toggleTarget.textContent = expanded
            ? this.toggleTarget.dataset.collapseLabel
            : this.toggleTarget.dataset.expandLabel;
    }
}
