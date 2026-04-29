<?php

declare(strict_types=1);

namespace Monorail\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Middleware;
use Monorail\Panel\PanelManager;

class HandleMonorailRequests extends Middleware
{
    public function __construct(private readonly PanelManager $panels) {}

    /**
     * Determine the root Blade view that wraps every Monorail Inertia response.
     */
    public function rootView(Request $request): string
    {
        return (string) config('monorail.inertia.root_view', 'monorail::app');
    }

    /**
     * The asset version used by Inertia to invalidate client caches.
     *
     * Derived from the published Monorail entry file when present, falling back
     * to a static token when the assets have not been published yet.
     */
    public function version(Request $request): ?string
    {
        $entry = (string) config('monorail.assets.js_entry');
        $path = public_path('build/'.ltrim($entry, '/'));

        if (is_file($path)) {
            return (string) filemtime($path);
        }

        return config('monorail.inertia.version', 'monorail');
    }

    private function resolveNotificationsSharedProp(Request $request, ?string $panelId): ?array
    {
        if (! $panelId || ! $this->panels->has($panelId)) {
            return null;
        }

        $panel = $this->panels->get($panelId);

        if (! $panel->isNotificationsEnabled() || ! $request->user()) {
            return null;
        }

        try {
            $count = DatabaseNotification::where('notifiable_type', get_class($request->user()))
                ->where('notifiable_id', $request->user()->getAuthIdentifier())
                ->whereNull('read_at')
                ->count();
        } catch (\Throwable) {
            $count = 0;
        }

        return ['unread_count' => $count];
    }

    /**
     * Data shared with every Monorail Inertia response.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $panelId = $request->route()?->defaults['panelId'] ?? null;

        // Apply session-based locale if available
        if ($panelId && $this->panels->has($panelId)) {
            $panel = $this->panels->get($panelId);
            $sessionKey = 'rocket_locale_'.$panelId;
            $savedLocale = session($sessionKey);

            if ($savedLocale && in_array($savedLocale, $panel->getAvailableLocales(), true)) {
                app()->setLocale($savedLocale);
            }
        }

        return array_merge(parent::share($request), [
            'monorail' => [
                'panel' => fn () => $panelId && $this->panels->has($panelId)
                    ? $this->panels->get($panelId)->toSharedProps()
                    : null,
            ],
            'auth' => [
                'user' => fn () => $request->user(),
            ],
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'notifications' => fn () => $this->resolveNotificationsSharedProp($request, $panelId),
        ]);
    }
}
