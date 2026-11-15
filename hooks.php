<?php
/**
 * DDNS Admin Whitelist hooks.
 *
 * WHMCS hooks for the DDNS Admin Whitelist addon module of HiTechCloud
 * (org: hitechcloudvn).
 *
 * Các hook WHMCS cho module addon DDNS Admin Whitelist của HiTechCloud
 * (org: hitechcloudvn).
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/ddns_admin_whitelist.php';

add_hook('AfterCronJob', 1, function ($vars) {
    if (!function_exists('ddns_admin_whitelist_cron_enabled') || !ddns_admin_whitelist_cron_enabled()) {
        return;
    }

    try {
        ddns_admin_whitelist_run_due_checks(false);
    } catch (\Exception $e) {
        if (function_exists('logActivity')) {
            logActivity('DDNS Admin Whitelist cron error: ' . $e->getMessage());
        }
    }
});
