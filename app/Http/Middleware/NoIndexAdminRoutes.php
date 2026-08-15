<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoIndexAdminRoutes
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->is('admin') || $request->is('admin/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            $this->addRobotsMetaTag($response);
        }

        return $response;
    }

    private function addRobotsMetaTag(Response $response): void
    {
        if (
            ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            || ! method_exists($response, 'getContent')
            || ! method_exists($response, 'setContent')
        ) {
            return;
        }

        $content = $response->getContent();

        if (
            ! is_string($content)
            || stripos($content, '<meta name="robots"') !== false
            || stripos($content, '</head>') === false
        ) {
            return;
        }

        $response->setContent(str_ireplace(
            '</head>',
            '    <meta name="robots" content="noindex, nofollow">'.PHP_EOL.'</head>',
            $content,
        ));
        $response->headers->remove('Content-Length');
    }
}
