
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./bootstrap');

import Echo from "laravel-echo"

import Flow from '@flowjs/flow.js'
window.Flow = Flow;

window.Pusher = require('pusher-js');

window.Pusher.log = function(message) {
    if (window.console && window.console.log) {
        window.console.log(message);
    }
};

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'BotQio-key',
    wsHost: window.location.hostname,
    wsPath: '/ws',
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});