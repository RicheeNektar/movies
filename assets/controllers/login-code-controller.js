import React from 'react';
import ReactDOM from 'react-dom';
import { Controller } from "@hotwired/stimulus";
import LoginCode from '../features/LoginCode';

export default class extends Controller
{
    static targets = ['qr'];

    connect() {
        ReactDOM.render(
          <LoginCode />,
          this.qrTarget
        );
    }
}