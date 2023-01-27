import { Controller } from "@hotwired/stimulus";
import $ from 'jquery';

export default class extends Controller {
    constructor(context) {
        super(context);
        this.sent = false;
        this.debounce = false;
    }

    ontimeupdate(e) {
        const player = e?.target;
        console.log(e);

        if (player && player?.currentTime && player?.duration) {
            if (!(this.sent || this.debounce) &&
                (player.currentTime / player.duration) >= .8) {
                this.debounce = true;

                $.post({
                    url: '?watched=1',
                    success: () => this.sent = true,
                });

                setTimeout(() => this.debounce = false, 200);
            }
        }
    }
}