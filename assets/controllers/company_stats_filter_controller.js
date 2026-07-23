import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['row', 'empty'];

    filter(event) {
        const raw = event.detail?.value ?? event.target?.value ?? '';
        const query = raw.trim().toLowerCase();

        let visible = 0;
        this.rowTargets.forEach((row) => {
            const name = (row.dataset.companyName ?? '').toLowerCase();
            const match = query === '' || name.includes(query);
            row.hidden = !match;
            if (match) {
                ++visible;
            }
        });

        if (this.hasEmptyTarget) {
            this.emptyTarget.hidden = visible !== 0;
        }
    }
}
