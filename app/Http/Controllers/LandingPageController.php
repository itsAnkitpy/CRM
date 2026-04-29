<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function central(): View
    {
        return view('welcome', [
            'pageTitle' => config('app.name', 'Highland Core') . ' — Unified Sales & Support CRM',
            'loginUrl' => Filament::getPanel('admin')->getLoginUrl(),
            'primaryCtaLabel' => 'Log in to Highland Core',
            'secondaryCtaLabel' => 'Open admin login',
            'contextMode' => 'central',
        ]);
    }

    public function tenant(Request $request): View|RedirectResponse
    {
        if (auth()->guard('tenant')->check()) {
            return redirect()->to(Filament::getPanel('tenant')->getUrl());
        }

        /** @var Tenant $tenant */
        $tenant = tenant();
        $workspaceName = $tenant->display_name;

        return view('welcome', [
            'pageTitle' => "{$workspaceName} — Highland Core Workspace",
            'loginUrl' => Filament::getPanel('tenant')->getLoginUrl(),
            'primaryCtaLabel' => "Log in to {$workspaceName}",
            'secondaryCtaLabel' => "Open {$workspaceName} login",
            'contextMode' => 'tenant',
            'workspaceName' => $workspaceName,
        ]);
    }
}
