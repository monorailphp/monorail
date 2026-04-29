<?php

declare(strict_types=1);

namespace Monorail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Response;
use Monorail\Pages\DashboardPage;
use Monorail\Panel\PanelManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DashboardController extends Controller
{
    public function __construct(private readonly PanelManager $panels) {}

    public function show(Request $request): Response
    {
        $panelId = $request->route()?->defaults['panelId'] ?? null;

        if ($panelId === null) {
            throw new NotFoundHttpException('Monorail panel not resolved for this route.');
        }

        $panel = $this->panels->get($panelId);
        $this->panels->setCurrent($panelId);

        $page = new DashboardPage;

        return $page->handle($request, $panel);
    }
}
