import { Controller } from '@hotwired/stimulus';

/**
 * Interactive star rating widget.
 * Connects to a container with data-star-rating-target="stars" (individual star spans)
 * and a hidden input as data-star-rating-target="input".
 */
export default class extends Controller {
    static targets = ['star', 'input'];

    static values = {
        rating: { type: Number, default: 0 },
    };

    connect() {
        this._highlight(this.ratingValue);
    }

    select(event) {
        const value = parseInt(event.currentTarget.dataset.starValue, 10);
        this.ratingValue = value;
        this.inputTarget.value = value;
        this._highlight(value);
    }

    hover(event) {
        const value = parseInt(event.currentTarget.dataset.starValue, 10);
        this._highlight(value);
    }

    leave() {
        this._highlight(this.ratingValue);
    }

    _highlight(value) {
        this.starTargets.forEach((star) => {
            const starValue = parseInt(star.dataset.starValue, 10);
            if (starValue <= value) {
                star.classList.add('star-active');
                star.classList.remove('star-inactive');
            } else {
                star.classList.remove('star-active');
                star.classList.add('star-inactive');
            }
        });
    }
}
