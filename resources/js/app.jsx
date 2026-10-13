import React from 'react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../css/app.css';

const pages = import.meta.glob('./Pages/**/*.jsx');
// Recheck the server session instead of restoring stale Inertia auth pages.
// Register before Inertia installs its own history listener.
window.addEventListener('popstate', event => {
    event.stopImmediatePropagation();
    window.location.reload();
}, true);
window.addEventListener('pageshow', event => {
    if (event.persisted) window.location.reload();
});

createInertiaApp({
    title: title => title ? `${title} | RestroFlow` : 'RestroFlow',
    resolve: name => resolvePageComponent(`./Pages/${name}.jsx`, pages),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
