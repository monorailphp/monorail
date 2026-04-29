import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import CreateRecord from './pages/create-record';
import Dashboard from './pages/dashboard';
import EditRecord from './pages/edit-record';
import ErrorPage from './pages/error';
import ForgotPassword from './pages/forgot-password';
import ListRecords from './pages/list-records';
import Login from './pages/login';
import Notifications from './pages/notifications';
import RocketPage from './pages/page';
import Profile from './pages/profile';
import Register from './pages/register';
import ResetPassword from './pages/reset-password';
import VerifyEmail from './pages/verify-email';
import ViewRecord from './pages/view-record';

const pages: Record<string, () => Promise<unknown> | unknown> = {
    'monorail/list-records': () => ({ default: ListRecords }),
    'monorail/create-record': () => ({ default: CreateRecord }),
    'monorail/edit-record': () => ({ default: EditRecord }),
    'monorail/view-record': () => ({ default: ViewRecord }),
    'monorail/dashboard': () => ({ default: Dashboard }),
    'monorail/notifications': () => ({ default: Notifications }),
    'monorail/error': () => ({ default: ErrorPage }),
    'monorail/page': () => ({ default: RocketPage }),
    'monorail/login': () => ({ default: Login }),
    'monorail/register': () => ({ default: Register }),
    'monorail/forgot-password': () => ({ default: ForgotPassword }),
    'monorail/reset-password': () => ({ default: ResetPassword }),
    'monorail/verify-email': () => ({ default: VerifyEmail }),
    'monorail/profile': () => ({ default: Profile }),
};

createInertiaApp({
    resolve: async (name) => {
        const loader = pages[name];
        if (!loader) {
            throw new Error(`Unknown Monorail page: ${name}`);
        }
        const mod = await loader();
        // @ts-expect-error runtime resolved module shape
        return mod.default ? mod : { default: mod };
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
