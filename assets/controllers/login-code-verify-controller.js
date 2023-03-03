import React from 'react';
import ReactDOM from 'react-dom';
import { Controller } from "@hotwired/stimulus";
import LoginCodeVerify from '../features/LoginCodeVerify';

export default class extends Controller
{
    static targets = ['selection'];

    connect() {
        ReactDOM.render(
          <LoginCodeVerify />,
          this.selectionTarget
        );
    }
}