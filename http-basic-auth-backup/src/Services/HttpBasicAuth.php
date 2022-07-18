<?php

namespace A17\HttpBasicAuth\Services;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;

class HttpBasicAuth
{
    protected array $config = [];

    public function checkAuth(Request $request): Response|RedirectResponse|null
    {
        if ($this->disabled()) {
            return null;
        }

        if ($this->routeIsOnIgnore($request)) {
            return null;
        }

        if ($this->userAuthenticated($request)) {
            return null;
        }

        return $this->abort($request);
    }

    public function handle($request, Closure $next, $username = null, $password = null)
    {
        return $next($request);
    }

    public function disabled(): bool
    {
        return !($this->config['enabled'] ?? false);
    }

    public function userAuthenticated(Request $request)
    {
        return $request->getUser() === ($this->config['username'] ?? 'missing') &&
            $request->getPassword() === ($this->config['password'] ?? 'missing');
    }

    public function abort(Request $request)
    {
        $header = ['WWW-Authenticate' => 'Basic realm="Basic Auth", charset="UTF-8"'];

        if ($request->wantsJson()) {
            return response()->json(
                [
                    'message' => '401 Authorization Required',
                ],
                401,
                $header,
            );
        }

        return response('401 Authorization Required', 401, $header);
    }

    public function routeIsOnIgnore(Request $request): bool
    {
        return false;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }
}
