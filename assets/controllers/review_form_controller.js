import { Controller } from '@hotwired/stimulus';

/**
 * Form enhancements: character counter for textarea fields.
 */
export default class extends Controller {
    static targets = ['textarea', 'counter'];

    connect() {
        if (this.hasTextareaTarget) {
            this.counter();
        }
    }

    counter() {
        if (!this.hasCounterTarget) {
            return;
        }

        const current = this.textareaTarget.value.length;
        this.counterTarget.textContent = current;
    }
}
