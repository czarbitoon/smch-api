import _ from 'lodash';
window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

// Set Axios to include credentials
axios.defaults.withCredentials = true;

// Set the base URL for Axios
axios.defaults.baseURL = 'http://127.0.0.1:8000/api'; // Ensure the base URL includes /api

// Fetch CSRF cookie
axios.get('/sanctum/csrf-cookie').then(response => {
    // Now you can make authenticated requests
}).catch(error => {
    console.error('CSRF cookie fetch error:', error);
});


/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';
// window.Pusher = Pusher;
// window.Echo = new Echo({...});
