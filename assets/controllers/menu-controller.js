import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ['menu', 'button'];

    toggle() {
        this.open = !this.open;
        this.buttonTarget.classList[this.open ? 'add' : 'remove']('btn-secondary');
        this.menuTarget.classList[this.open ? 'remove' : 'add']('d-none');
    }
}