<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSessionCookieNameByContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $centralDomains = array_map('strtolower', config('tenancy.central_domains', []));

        $cookieName = in_array($host, $centralDomains, true)
            ? config('domains.session_cookies.landlord', 'crm_landlord_session')
            : config('domains.session_cookies.tenant', 'crm_tenant_session');

        config()->set('session.cookie', $cookieName);
        app('session')->forgetDrivers();

        if (app()->bound('session.store')) {
            app('session.store')->setName($cookieName);
        }

        return $next($request);
    }
}
