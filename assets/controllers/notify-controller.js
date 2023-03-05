import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { Controller } from "@hotwired/stimulus";
import Toast from '../components/Toast';

export default class extends Controller
{
    static targets = ['list'];
    static values = {
        fetch: String,
        ack: String,
    };

    initialize() {
        this.notificationsOpen = 0;

        this.queueFetch(100);
    }

    queueFetch(ms = 5000) {
        setTimeout(() => {
            this.fetchMessages();
        }, ms);
    }

    fetchMessages() {
        const remaining = 3 - this.notificationsOpen;

        if (remaining > 0) {
            $.get(
                `${this.fetchValue}/${remaining}`,
                null,
                messages => {
                    ReactDOM.render(
                        messages.map(message => <Toast key={message.id} {...message} createAt={new Date(message.createAt)} ackUrl={this.ackValue} />),
                        this.listTarget
                    );

                    this.queueFetch();
                }
            );
        } else {
            this.queueFetch();
        }
    }
}