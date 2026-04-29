<?php

declare(strict_types=1);

namespace Monorail\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Inertia\Response;
use Monorail\Pages\Page;
use Monorail\Panel\Panel;
use Monorail\Panel\PanelManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait DispatchesPages
{
    abstract protected function panels(): PanelManager;

    protected function resolvePanel(Request $request): Panel
    {
        $panelId = $request->route()?->defaults['panelId'] ?? null;

        if ($panelId === null) {
            throw new NotFoundHttpException('Monorail panel not resolved for this route.');
        }

        $panel = $this->panels()->get($panelId);
        $this->panels()->setCurrent($panelId);

        return $panel;
    }

    /**
     * @param  class-string<Page>  $pageClass
     */
    protected function dispatchToPage(
        Request $request,
        Panel $panel,
        string $pageClass,
        ?string $resource = null
    ): Response {
        $page = new $pageClass;

        if ($resource !== null) {
            $page->resource($resource);
        }

        abort_unless($page->can($request), 403);

        return $page->handle($request, $panel);
    }

    /**
     * @param  class-string<Page>  $pageClass
     */
    protected function dispatchToPageWithRecord(
        Request $request,
        Panel $panel,
        string $pageClass,
        string $resource,
        Model $record
    ): Response {
        $page = (new $pageClass)->resource($resource);
        $page->setRecord($record);

        abort_unless($page->can($request), 403);

        return $page->handle($request, $panel);
    }
}
