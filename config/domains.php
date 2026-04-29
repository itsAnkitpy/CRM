<?php

declare(strict_types=1);

$marketingDomain = trim((string) env('CENTRAL_MARKETING_DOMAIN', 'crm.test'));
$landlordPanelDomain = trim((string) env('LANDLORD_PANEL_DOMAIN', 'hcore.crm.test'));
$tenantBaseDomain = trim((string) env('TENANT_BASE_DOMAIN', 'crm.test'));
$centralDevDomains = array_values(array_filter(array_map(
    static fn (string $domain): string => trim($domain),
    explode(',', (string) env('CENTRAL_DEV_DOMAINS', '127.0.0.1,localhost'))
)));

$uniqueDomains = static fn (array $domains): array => array_values(array_unique(array_filter($domains)));

return [
    'marketing' => $marketingDomain,
    'landlord' => $landlordPanelDomain,
    'tenant_base' => $tenantBaseDomain,
    'marketing_hosts' => $uniqueDomains([
        $marketingDomain,
        ...$centralDevDomains,
    ]),
    'central' => $uniqueDomains([
        $marketingDomain,
        $landlordPanelDomain,
        ...$centralDevDomains,
    ]),
    'session_cookies' => [
        'landlord' => env('LANDLORD_SESSION_COOKIE', 'crm_landlord_session'),
        'tenant' => env('TENANT_SESSION_COOKIE', 'crm_tenant_session'),
    ],
];
