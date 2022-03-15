import React from 'react';
import $ from 'jquery';
import bootstrap from 'bootstrap';

import './styles/app.scss';

import './bootstrap';

$(document).ready(() => {
    [].slice.call(document.querySelectorAll('.toast')).forEach(el => {
        new bootstrap.Toast(el).show();
    });
});
