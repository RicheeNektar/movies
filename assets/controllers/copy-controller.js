import { Controller } from "@hotwired/stimulus";
import { Tooltip } from 'bootstrap';

export default class extends Controller
{
    static targets = ['button'];
    static values = {
        link: String
    };

    initialize() {
        this.tooltip = new Tooltip(this.buttonTarget, {
            trigger: 'manual',
        });
    }

    copy() {
        navigator.clipboard.writeText(this.linkValue);
        this.tooltip.show();
        setTimeout(() => this.tooltip.hide(), 1000);
    }
}