import { Link } from '@inertiajs/react';
import { ShieldOff, FileQuestion, AlertCircle } from 'lucide-react';
import PanelShell from '../components/panel-shell';
import { Button } from '../components/ui/button';
import { create__ } from '../lib/i18n';

type PanelProp = {
    id: string;
    brand: string;
    path: string;
    navigation: {
        label: string;
        url: string;
        group?: string | null;
        sort?: number;
        icon?: string | null;
    }[];
    translations?: Record<string, string>;
};

type Props = {
    panel?: PanelProp;
    status: number;
    title: string;
    message: string;
};

function iconFor(status: number) {
    if (status === 403) return ShieldOff;
    if (status === 404) return FileQuestion;
    return AlertCircle;
}

export default function ErrorPage({ panel, status, title, message }: Props) {
    const __ = create__(panel?.translations ?? {});
    const Icon = iconFor(status);
    const homeHref = panel?.path ? `/${panel.path}` : '/';

    const body = (
        <div className="flex min-h-[60vh] flex-col items-center justify-center text-center">
            <div className="rounded-full bg-muted p-4">
                <Icon className="size-10 text-muted-foreground" />
            </div>
            <p className="mt-6 font-mono text-sm text-muted-foreground">{status}</p>
            <h1 className="mt-2 text-2xl font-semibold tracking-tight">{title}</h1>
            <p className="mt-2 max-w-md text-sm text-muted-foreground">{message}</p>
            <div className="mt-6 flex items-center gap-2">
                <Button variant="ghost" asChild>
                    <Link href={homeHref}>{__('Back to dashboard')}</Link>
                </Button>
            </div>
        </div>
    );

    if (!panel) {
        return (
            <div className="flex min-h-screen items-center justify-center bg-background p-6">
                {body}
            </div>
        );
    }

    return <PanelShell panel={panel}>{body}</PanelShell>;
}
