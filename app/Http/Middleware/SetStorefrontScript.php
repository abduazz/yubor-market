<?php

namespace App\Http\Middleware;

use App\Support\UzbekCyrillic;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetStorefrontScript
{
    public function handle(Request $request, Closure $next): Response
    {
        $script = $request->session()->get('storefront_script')
            ?? $request->cookie('storefront_script')
            ?? 'lat';

        if (! in_array($script, ['lat', 'cyrl'], true)) {
            $script = 'lat';
        }

        View::share('storefrontScript', $script);

        $response = $next($request);

        if (
            $script === 'cyrl'
            && ! $request->is('admin*')
            && $response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type', 'text/html'), 'text/html')
        ) {
            $content = $response->getContent();
            if (is_string($content) && $content !== '') {
                $response->setContent(UzbekCyrillic::transliterateHtml($content));
            }
        }

        return $response;
    }
}
