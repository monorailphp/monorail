import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import CreateRecord from '../pages/create-record';
import Dashboard from '../pages/dashboard';
import EditRecord from '../pages/edit-record';
import ErrorPage from '../pages/error';
import ForgotPassword from '../pages/forgot-password';
import ListRecords from '../pages/list-records';
import Login from '../pages/login';
import Notifications from '../pages/notifications';
import RocketPage from '../pages/page';
import Profile from '../pages/profile';
import Register from '../pages/register';
import ResetPassword from '../pages/reset-password';
import VerifyEmail from '../pages/verify-email';
import ViewRecord from '../pages/view-record';
import { applyOverrides, type MonorailConfig, type PageComponent, registry } from './registry';

const defaultPages: Record<string, PageComponent> = {
    'monorail/list-records': ListRecords,
    'monorail/create-record': CreateRecord,
    'monorail/edit-record': EditRecord,
    'monorail/view-record': ViewRecord,
    'monorail/dashboard': Dashboard,
    'monorail/notifications': Notifications,
    'monorail/error': ErrorPage,
    'monorail/page': RocketPage,
    'monorail/login': Login,
    'monorail/register': Register,
    'monorail/forgot-password': ForgotPassword,
    'monorail/reset-password': ResetPassword,
    'monorail/verify-email': VerifyEmail,
    'monorail/profile': Profile,
};

let booted = false;

export function createMonorail(config: MonorailConfig = {}): void {
    if (booted) {
        if (typeof console !== 'undefined') {
            console.warn('[Monorail] createMonorail() called more than once; subsequent calls are ignored.');
        }
        return;
    }
    booted = true;

    applyOverrides(config);

    const resolvedPages: Record<string, PageComponent> = {
        ...defaultPages,
        ...registry.pages,
    };

    createInertiaApp({
        resolve: (name) => {
            const Component = resolvedPages[name];
            if (!Component) {
                throw new Error(`Unknown Monorail page: ${name}`);
            }
            return Component;
        },
        setup({ el, App, props }) {
            createRoot(el).render(<App {...props} />);
        },
    });
}
