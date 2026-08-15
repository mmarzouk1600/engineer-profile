/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Album module — attach the JWT bearer token (if present) to every axios
 * request, including Inertia's own internal page visits, which share this
 * same axios instance. This lets Laravel's "auth:web,api" middleware
 * authenticate Inertia page loads and plain API calls the same way.
 */
const albumToken = window.localStorage.getItem('album_token');
if (albumToken) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${albumToken}`;
}

export function setAlbumAuthToken(token) {
    if (token) {
        window.localStorage.setItem('album_token', token);
        window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
        window.localStorage.removeItem('album_token');
        delete window.axios.defaults.headers.common['Authorization'];
    }
}

export function getAlbumUser() {
    const raw = window.localStorage.getItem('album_user');
    return raw ? JSON.parse(raw) : null;
}

export function setAlbumUser(user) {
    if (user) {
        window.localStorage.setItem('album_user', JSON.stringify(user));
    } else {
        window.localStorage.removeItem('album_user');
    }
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;
if(import.meta.env.VITE_BROADCAST_DRIVER === 'pusher') {
    // window.Echo = new Echo({
    //     broadcaster: 'pusher',
    //     key: import.meta.env.VITE_PUSHER_APP_KEY,
    //     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    //     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    //     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    //     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    //     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    //     enabledTransports: ['ws', 'wss'],
    //     disableStats: true,
    // });
}
