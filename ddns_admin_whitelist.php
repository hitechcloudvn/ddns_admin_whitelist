<?php
/**
 * DDNS Admin Whitelist
 *
 * WHMCS addon module of HiTechCloud (org: hitechcloudvn) that resolves DDNS
 * hostnames and safely keeps the WHMCS admin login IP whitelist
 * (WhitelistedIPs) updated with the latest public IPv4.
 *
 * Module addon WHMCS của HiTechCloud (org: hitechcloudvn), phân giải hostname
 * DDNS và cập nhật an toàn whitelist IP đăng nhập admin WHMCS (WhitelistedIPs)
 * bằng địa chỉ IPv4 công khai mới nhất.
 */

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (!defined('DDNS_ADMIN_WHITELIST_MODULE')) {
    define('DDNS_ADMIN_WHITELIST_MODULE', 'ddns_admin_whitelist');
    define('DDNS_ADMIN_WHITELIST_HOSTS_TABLE', 'mod_ddns_admin_whitelist_hosts');
    define('DDNS_ADMIN_WHITELIST_LOGS_TABLE', 'mod_ddns_admin_whitelist_logs');
    define('DDNS_ADMIN_WHITELIST_NOTE_PREFIX', '[ddns_admin_whitelist:');
}

if (!function_exists('ddns_admin_whitelist_config')) {

/**
 * Addon module metadata and configurable defaults.
 * Thông tin metadata và các giá trị cấu hình mặc định của module addon.
 *
 * @return array
 */
function ddns_admin_whitelist_config()
{
    return array(
        'name' => 'DDNS Admin Whitelist',
        'description' => 'Resolve DDNS hostnames and safely update the WHMCS admin login IP whitelist. / Tự động phân giải hostname DDNS và cập nhật an toàn whitelist IP đăng nhập admin WHMCS.',
        'author' => 'HiTechCloud (hitechcloudvn)',
        'language' => 'english',
        'version' => '1.0.0',
        'fields' => array(
            'default_interval' => array(
                'FriendlyName' => ddns_admin_whitelist_lang('cfg_default_interval_name'),
                'Type' => 'text',
                'Size' => '5',
                'Default' => '15',
                'Description' => ddns_admin_whitelist_lang('cfg_default_interval_desc'),
            ),
            'cron_enabled' => array(
                'FriendlyName' => ddns_admin_whitelist_lang('cfg_cron_enabled_name'),
                'Type' => 'yesno',
                'Default' => 'on',
                'Description' => ddns_admin_whitelist_lang('cfg_cron_enabled_desc'),
            ),
            'ui_language' => array(
                'FriendlyName' => ddns_admin_whitelist_lang('cfg_ui_language_name'),
                'Type' => 'dropdown',
                'Options' => 'english,vietnamese',
                'Default' => 'english',
                'Size' => '15',
                'Description' => ddns_admin_whitelist_lang('cfg_ui_language_desc'),
            ),
        ),
    );
}

/**
 * Resolve the configured admin UI language for this module.
 *
 * Xác định ngôn ngữ giao diện admin được cấu hình cho module.
 *
 * @return string
 */
function ddns_admin_whitelist_ui_language()
{
    try {
        $row = Capsule::table('tbladdonmodules')
            ->where('module', DDNS_ADMIN_WHITELIST_MODULE)
            ->where('setting', 'ui_language')
            ->first();

        if ($row && (string) $row->value === 'vietnamese') {
            return 'vietnamese';
        }

        return 'english';
    } catch (\Exception $e) {
        return 'english';
    }
}

/**
 * Load a module language string by key.
 *
 * Tải một chuỗi ngôn ngữ của module theo khóa.
 *
 * @param string $key
 *
 * @return string
 */
function ddns_admin_whitelist_lang($key)
{
    static $langCache = array();

    $language = ddns_admin_whitelist_ui_language();

    if (!isset($langCache[$language])) {
        $strings = array();
        $file = __DIR__ . '/lang/' . $language . '.php';

        if (is_file($file)) {
            $loaded = include $file;
            if (is_array($loaded)) {
                $strings = $loaded;
            }
        }

        $langCache[$language] = $strings;
    }

    if (isset($langCache[$language][$key])) {
        return (string) $langCache[$language][$key];
    }

    if (!isset($langCache['english'])) {
        $strings = array();
        $file = __DIR__ . '/lang/english.php';

        if (is_file($file)) {
            $loaded = include $file;
            if (is_array($loaded)) {
                $strings = $loaded;
            }
        }

        $langCache['english'] = $strings;
    }

    if (isset($langCache['english'][$key])) {
        return (string) $langCache['english'][$key];
    }

    return (string) $key;
}

/**
 * Create module database tables.
 * Tạo các bảng cơ sở dữ liệu của module.
 *
 * @return array
 */
function ddns_admin_whitelist_activate()
{
    try {
        ddns_admin_whitelist_ensure_schema();

        return array(
            'status' => 'success',
            'description' => 'DDNS Admin Whitelist has been activated. Add hostnames from Addons > DDNS Admin Whitelist.',
        );
    } catch (\Exception $e) {
        return array(
            'status' => 'error',
            'description' => 'Activation failed: ' . $e->getMessage(),
        );
    }
}

/**
 * Deactivation intentionally preserves data and whitelist entries.
 * Vô hiệu hoá module nhưng vẫn giữ nguyên dữ liệu và các mục whitelist.
 *
 * @return array
 */
function ddns_admin_whitelist_deactivate()
{
    return array(
        'status' => 'success',
        'description' => 'Module deactivated. Host records, logs, and existing WHMCS WhitelistedIPs entries were preserved for safety.',
    );
}

/**
 * Ensure required schema exists after upgrades.
 * Đảm bảo cấu trúc bảng cần thiết tồn tại sau khi nâng cấp.
 *
 * @param array $vars
 *
 * @return void
 */
function ddns_admin_whitelist_upgrade($vars)
{
    ddns_admin_whitelist_ensure_schema();
}

/**
 * Render and process the admin UI.
 * Hiển thị và xử lý giao diện admin.
 *
 * @param array $vars
 *
 * @return void
 */
function ddns_admin_whitelist_output($vars)
{
    $messages = array();

    try {
        ddns_admin_whitelist_ensure_schema();
    } catch (\Exception $e) {
        echo ddns_admin_whitelist_alert('danger', 'Schema error: ' . $e->getMessage());
        return;
    }

    if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
        try {
            check_token('WHMCS.admin.default');
            $messages[] = ddns_admin_whitelist_handle_admin_post($vars);
        } catch (\Exception $e) {
            $messages[] = array(
                'type' => 'danger',
                'message' => $e->getMessage(),
            );
        }
    }

    echo ddns_admin_whitelist_render_admin($vars, $messages);
}

/**
 * Create the module tables if missing.
 * Tạo các bảng của module nếu chưa tồn tại.
 *
 * @return void
 */
function ddns_admin_whitelist_ensure_schema()
{
    $schema = Capsule::schema();

    if (!$schema->hasTable(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)) {
        $schema->create(DDNS_ADMIN_WHITELIST_HOSTS_TABLE, function ($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->string('hostname', 253);
            $table->tinyInteger('enabled')->default(1);
            $table->integer('interval_minutes')->default(15);
            $table->string('current_ip', 45)->nullable();
            $table->string('previous_ip', 45)->nullable();
            $table->dateTime('last_checked_at')->nullable();
            $table->dateTime('last_success_at')->nullable();
            $table->text('last_error')->nullable();
            $table->text('last_message')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unique('hostname');
            $table->index('enabled');
            $table->index('current_ip');
        });
    }

    if (!$schema->hasTable(DDNS_ADMIN_WHITELIST_LOGS_TABLE)) {
        $schema->create(DDNS_ADMIN_WHITELIST_LOGS_TABLE, function ($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->integer('host_id')->unsigned()->nullable();
            $table->string('hostname', 253);
            $table->string('old_ip', 45)->nullable();
            $table->string('new_ip', 45)->nullable();
            $table->tinyInteger('success')->default(0);
            $table->tinyInteger('changed')->default(0);
            $table->tinyInteger('skipped')->default(0);
            $table->text('message')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->index('host_id');
            $table->index('created_at');
        });
    }
}

/**
 * Process an admin POST action.
 * Xử lý một hành động POST từ admin.
 *
 * @param array $vars
 *
 * @return array
 */
function ddns_admin_whitelist_handle_admin_post($vars)
{
    $action = isset($_POST['ddns_action']) ? trim((string) $_POST['ddns_action']) : '';

    switch ($action) {
        case 'add_host':
            return ddns_admin_whitelist_admin_add_host($vars);

        case 'update_host':
            return ddns_admin_whitelist_admin_update_host();

        case 'check_host':
            return ddns_admin_whitelist_admin_check_host();

        case 'check_all':
            return ddns_admin_whitelist_admin_check_all();

        case 'toggle_host':
            return ddns_admin_whitelist_admin_toggle_host();

        case 'delete_host':
            return ddns_admin_whitelist_admin_delete_host();

        case 'prune_logs':
            return ddns_admin_whitelist_admin_prune_logs();

        default:
            throw new \Exception('Unknown action.');
    }
}

/**
 * Add a hostname and run an immediate check.
 * Thêm một hostname và chạy kiểm tra ngay lập tức.
 *
 * @param array $vars
 *
 * @return array
 */
function ddns_admin_whitelist_admin_add_host($vars)
{
    $hostname = ddns_admin_whitelist_normalize_hostname(isset($_POST['hostname']) ? $_POST['hostname'] : '');
    $interval = ddns_admin_whitelist_normalize_interval(
        isset($_POST['interval_minutes']) ? $_POST['interval_minutes'] : ddns_admin_whitelist_default_interval($vars)
    );
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    $error = '';

    if (!ddns_admin_whitelist_validate_hostname($hostname, $error)) {
        throw new \Exception($error);
    }

    $existing = Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->where('hostname', $hostname)
        ->first();

    if ($existing) {
        throw new \Exception('Hostname already exists in this module.');
    }

    $now = ddns_admin_whitelist_now();
    $hostId = Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)->insertGetId(array(
        'hostname' => $hostname,
        'enabled' => $enabled,
        'interval_minutes' => $interval,
        'current_ip' => null,
        'previous_ip' => null,
        'last_checked_at' => null,
        'last_success_at' => null,
        'last_error' => null,
        'last_message' => 'Host created.',
        'created_at' => $now,
        'updated_at' => $now,
    ));

    if (!$enabled) {
        ddns_admin_whitelist_log($hostId, $hostname, null, null, 1, 0, 1, 'Host created disabled. No whitelist change was made.');

        return array(
            'type' => 'info',
            'message' => 'Hostname added disabled. Enable it when ready to run checks.',
        );
    }

    $result = ddns_admin_whitelist_check_host_by_id($hostId, true);

    return array(
        'type' => $result['success'] ? 'success' : 'warning',
        'message' => 'Hostname added. Immediate check result: ' . $result['message'],
    );
}

/**
 * Update hostname settings and run an immediate check if enabled.
 * Cập nhật thiết lập hostname và chạy kiểm tra ngay nếu đang bật.
 *
 * @return array
 */
function ddns_admin_whitelist_admin_update_host()
{
    $host = ddns_admin_whitelist_get_host_from_post();
    $hostname = ddns_admin_whitelist_normalize_hostname(isset($_POST['hostname']) ? $_POST['hostname'] : '');
    $interval = ddns_admin_whitelist_normalize_interval(isset($_POST['interval_minutes']) ? $_POST['interval_minutes'] : $host->interval_minutes);
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    $error = '';

    if (!ddns_admin_whitelist_validate_hostname($hostname, $error)) {
        throw new \Exception($error);
    }

    $existing = Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->where('hostname', $hostname)
        ->where('id', '<>', (int) $host->id)
        ->first();

    if ($existing) {
        throw new \Exception('Another host already uses that hostname.');
    }

    Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->where('id', (int) $host->id)
        ->update(array(
            'hostname' => $hostname,
            'enabled' => $enabled,
            'interval_minutes' => $interval,
            'last_message' => 'Host settings updated. Existing whitelist IP was preserved until the next successful check.',
            'updated_at' => ddns_admin_whitelist_now(),
        ));

    if (!$enabled) {
        ddns_admin_whitelist_log($host->id, $hostname, $host->current_ip, $host->current_ip, 1, 0, 1, 'Host disabled. Existing whitelist entry was preserved.');

        return array(
            'type' => 'info',
            'message' => 'Host updated and disabled. Existing WHMCS whitelist entries were preserved.',
        );
    }

    $result = ddns_admin_whitelist_check_host_by_id($host->id, true);

    return array(
        'type' => $result['success'] ? 'success' : 'warning',
        'message' => 'Host updated. Immediate check result: ' . $result['message'],
    );
}

/**
 * Run a forced check for one host.
 * Chạy kiểm tra bắt buộc cho một hostname.
 *
 * @return array
 */
function ddns_admin_whitelist_admin_check_host()
{
    $host = ddns_admin_whitelist_get_host_from_post();
    $result = ddns_admin_whitelist_check_host($host, true);

    return array(
        'type' => $result['success'] ? 'success' : ($result['skipped'] ? 'warning' : 'danger'),
        'message' => $result['message'],
    );
}

/**
 * Run forced checks for all enabled hosts.
 * Chạy kiểm tra bắt buộc cho tất cả hostname đang bật.
 *
 * @return array
 */
function ddns_admin_whitelist_admin_check_all()
{
    $results = ddns_admin_whitelist_run_due_checks(true);
    $total = count($results);
    $success = 0;
    $changed = 0;
    $skipped = 0;

    foreach ($results as $result) {
        if (!empty($result['success'])) {
            $success++;
        }
        if (!empty($result['changed'])) {
            $changed++;
        }
        if (!empty($result['skipped'])) {
            $skipped++;
        }
    }

    if ($total === 0) {
        return array(
            'type' => 'info',
            'message' => 'No enabled hosts were found.',
        );
    }

    return array(
        'type' => ($success === $total) ? 'success' : 'warning',
        'message' => 'Checked ' . $total . ' enabled host(s): ' . $success . ' success, ' . $changed . ' changed, ' . $skipped . ' skipped.',
    );
}

/**
 * Toggle a host and run an immediate check when enabling.
 * Bật/tắt một hostname và chạy kiểm tra ngay khi bật.
 *
 * @return array
 */
function ddns_admin_whitelist_admin_toggle_host()
{
    $host = ddns_admin_whitelist_get_host_from_post();
    $enabled = isset($_POST['enabled']) && (int) $_POST['enabled'] === 1 ? 1 : 0;

    Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->where('id', (int) $host->id)
        ->update(array(
            'enabled' => $enabled,
            'last_message' => $enabled ? 'Host enabled.' : 'Host disabled. Existing whitelist entry was preserved.',
            'updated_at' => ddns_admin_whitelist_now(),
        ));

    if (!$enabled) {
        ddns_admin_whitelist_log($host->id, $host->hostname, $host->current_ip, $host->current_ip, 1, 0, 1, 'Host disabled. Existing whitelist entry was preserved.');

        return array(
            'type' => 'info',
            'message' => 'Host disabled. Existing WHMCS whitelist entries were preserved.',
        );
    }

    $result = ddns_admin_whitelist_check_host_by_id($host->id, true);

    return array(
        'type' => $result['success'] ? 'success' : 'warning',
        'message' => 'Host enabled. Immediate check result: ' . $result['message'],
    );
}

/**
 * Delete module host record only. WHMCS whitelist is intentionally preserved.
 * Chỉ xoá bản ghi hostname của module. Whitelist WHMCS được giữ nguyên một cách chủ đích.
 *
 * @return array
 */
function ddns_admin_whitelist_admin_delete_host()
{
    $host = ddns_admin_whitelist_get_host_from_post();

    ddns_admin_whitelist_log($host->id, $host->hostname, $host->current_ip, $host->current_ip, 1, 0, 1, 'Host record deleted. Existing WHMCS whitelist entry was preserved.');

    Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->where('id', (int) $host->id)
        ->delete();

    return array(
        'type' => 'warning',
        'message' => 'Host record deleted. The latest WHMCS WhitelistedIPs entry was not removed.',
    );
}

/**
 * Delete old logs.
 * Xoá các bản ghi log cũ.
 *
 * @return array
 */
function ddns_admin_whitelist_admin_prune_logs()
{
    $days = isset($_POST['days']) ? (int) $_POST['days'] : 90;
    if ($days < 0) {
        $days = 0;
    }

    if ($days === 0) {
        $deleted = Capsule::table(DDNS_ADMIN_WHITELIST_LOGS_TABLE)->delete();
        $message = 'Deleted all ' . (int) $deleted . ' log row(s).';
    } else {
        if ($days < 7) {
            $days = 7;
        }

        $cutoff = date('Y-m-d H:i:s', time() - ($days * 86400));
        $deleted = Capsule::table(DDNS_ADMIN_WHITELIST_LOGS_TABLE)
            ->where('created_at', '<', $cutoff)
            ->delete();
        $message = 'Deleted ' . (int) $deleted . ' log row(s) older than ' . (int) $days . ' days.';
    }

    return array(
        'type' => 'success',
        'message' => $message,
    );
}

/**
 * Run due checks for cron or forced checks for admin.
 * Chạy các lượt kiểm tra đến hạn cho cron hoặc kiểm tra bắt buộc cho admin.
 *
 * @param bool $force
 *
 * @return array
 */
function ddns_admin_whitelist_run_due_checks($force = false)
{
    ddns_admin_whitelist_ensure_schema();

    $hosts = Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->where('enabled', 1)
        ->orderBy('id', 'asc')
        ->get();

    $results = array();

    foreach ($hosts as $host) {
        if (!$force && !ddns_admin_whitelist_host_is_due($host)) {
            continue;
        }

        $results[] = ddns_admin_whitelist_check_host($host, $force);
    }

    return $results;
}

/**
 * Check whether cron is enabled for this addon.
 * Kiểm tra cron có được bật cho addon này hay không.
 *
 * @return bool
 */
function ddns_admin_whitelist_cron_enabled()
{
    try {
        $row = Capsule::table('tbladdonmodules')
            ->where('module', DDNS_ADMIN_WHITELIST_MODULE)
            ->where('setting', 'cron_enabled')
            ->first();

        if (!$row) {
            return true;
        }

        return (string) $row->value === 'on';
    } catch (\Exception $e) {
        return true;
    }
}

/**
 * Run one hostname check by id.
 * Chạy kiểm tra cho một hostname theo id.
 *
 * @param int  $hostId
 * @param bool $force
 *
 * @return array
 */
function ddns_admin_whitelist_check_host_by_id($hostId, $force = false)
{
    $host = Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->where('id', (int) $hostId)
        ->first();

    if (!$host) {
        throw new \Exception('Host not found.');
    }

    return ddns_admin_whitelist_check_host($host, $force);
}

/**
 * Resolve and sync one host.
 * Phân giải và đồng bộ một hostname.
 *
 * @param object $host
 * @param bool   $force
 *
 * @return array
 */
function ddns_admin_whitelist_check_host($host, $force = false)
{
    if (empty($host->enabled)) {
        return array(
            'success' => false,
            'changed' => false,
            'skipped' => true,
            'message' => 'Host ' . $host->hostname . ' is disabled. No whitelist change was made.',
        );
    }

    if (!$force && !ddns_admin_whitelist_host_is_due($host)) {
        return array(
            'success' => true,
            'changed' => false,
            'skipped' => true,
            'message' => 'Host ' . $host->hostname . ' is not due yet.',
        );
    }

    $now = ddns_admin_whitelist_now();
    $oldIp = ddns_admin_whitelist_clean_ip($host->current_ip);
    $resolution = ddns_admin_whitelist_resolve_public_ipv4($host->hostname);

    if (empty($resolution['success'])) {
        Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
            ->where('id', (int) $host->id)
            ->update(array(
                'last_checked_at' => $now,
                'last_error' => $resolution['message'],
                'last_message' => 'DNS check failed. Previous/latest stored IP was preserved.',
                'updated_at' => $now,
            ));

        ddns_admin_whitelist_log($host->id, $host->hostname, $oldIp, null, 0, 0, 1, $resolution['message'] . ' Previous/latest stored IP was preserved.');

        return array(
            'success' => false,
            'changed' => false,
            'skipped' => true,
            'message' => $resolution['message'] . ' Previous/latest stored IP was preserved.',
        );
    }

    $newIp = $resolution['ip'];
    $duplicate = Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->where('enabled', 1)
        ->where('id', '<>', (int) $host->id)
        ->where('current_ip', $newIp)
        ->first();

    if ($duplicate) {
        $message = 'Resolved IP ' . $newIp . ' is already owned by hostname ' . $duplicate->hostname . '. This host was skipped to avoid duplicate ownership.';

        Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
            ->where('id', (int) $host->id)
            ->update(array(
                'last_checked_at' => $now,
                'last_error' => $message,
                'last_message' => $message,
                'updated_at' => $now,
            ));

        ddns_admin_whitelist_log($host->id, $host->hostname, $oldIp, $newIp, 0, 0, 1, $message);

        return array(
            'success' => false,
            'changed' => false,
            'skipped' => true,
            'message' => $message,
        );
    }

    $whitelist = ddns_admin_whitelist_sync_whitelist_entry($host, $newIp);

    if (empty($whitelist['success'])) {
        $message = 'Resolved ' . $host->hostname . ' to ' . $newIp . ', but whitelist update was skipped: ' . $whitelist['message'];

        Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
            ->where('id', (int) $host->id)
            ->update(array(
                'last_checked_at' => $now,
                'last_error' => $message,
                'last_message' => $message,
                'updated_at' => $now,
            ));

        ddns_admin_whitelist_log($host->id, $host->hostname, $oldIp, $newIp, 0, 0, 1, $message);

        return array(
            'success' => false,
            'changed' => false,
            'skipped' => true,
            'message' => $message,
        );
    }

    $changed = ($oldIp !== $newIp);
    $unbanned = ddns_admin_whitelist_unban_ip($newIp);
    $messageParts = array();

    if ($changed) {
        $messageParts[] = 'IP changed from ' . ($oldIp !== '' ? $oldIp : 'none') . ' to ' . $newIp . '.';
    } else {
        $messageParts[] = 'No IP change for ' . $host->hostname . ' (' . $newIp . ').';
    }

    $messageParts[] = $whitelist['message'];

    if ($unbanned > 0) {
        $messageParts[] = 'Removed ' . (int) $unbanned . ' matching tblbannedips row(s).';
    }

    $message = implode(' ', $messageParts);

    $updates = array(
        'current_ip' => $newIp,
        'last_checked_at' => $now,
        'last_success_at' => $now,
        'last_error' => null,
        'last_message' => $message,
        'updated_at' => $now,
    );

    if ($changed) {
        $updates['previous_ip'] = $oldIp !== '' ? $oldIp : null;
    }

    Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->where('id', (int) $host->id)
        ->update($updates);

    ddns_admin_whitelist_log($host->id, $host->hostname, $oldIp, $newIp, 1, $changed ? 1 : 0, 0, $message);

    return array(
        'success' => true,
        'changed' => $changed,
        'skipped' => false,
        'message' => $message,
    );
}

/**
 * Determine if a host is due for a scheduled check.
 * Xác định một hostname đã đến lượt kiểm tra theo lịch hay chưa.
 *
 * @param object $host
 *
 * @return bool
 */
function ddns_admin_whitelist_host_is_due($host)
{
    $lastChecked = isset($host->last_checked_at) ? trim((string) $host->last_checked_at) : '';

    if ($lastChecked === '' || $lastChecked === '0000-00-00 00:00:00') {
        return true;
    }

    $lastTime = strtotime($lastChecked);
    if (!$lastTime) {
        return true;
    }

    $interval = ddns_admin_whitelist_normalize_interval(isset($host->interval_minutes) ? $host->interval_minutes : 15);

    return $lastTime <= (time() - ($interval * 60));
}

/**
 * Safely update only this module's managed whitelist entry for one host.
 * Chỉ cập nhật an toàn mục whitelist do module quản lý cho một hostname.
 *
 * @param object $host
 * @param string $ip
 *
 * @return array
 */
function ddns_admin_whitelist_sync_whitelist_entry($host, $ip)
{
    $error = '';
    $entries = ddns_admin_whitelist_read_whitelisted_ips($error);

    if ($entries === false) {
        return array(
            'success' => false,
            'message' => $error,
        );
    }

    $note = ddns_admin_whitelist_note_for_host($host, $ip);
    $newEntries = array();
    $hostEntryKept = false;
    $hostEntryRemoved = false;

    foreach ($entries as $entry) {
        if (ddns_admin_whitelist_entry_is_for_host($entry, $host->id)) {
            if (!$hostEntryKept && ddns_admin_whitelist_entry_ip($entry) === $ip) {
                $entry['ip'] = $ip;
                $entry['note'] = $note;
                $newEntries[] = $entry;
                $hostEntryKept = true;
            } else {
                $hostEntryRemoved = true;
            }
            continue;
        }

        $newEntries[] = $entry;
    }

    $alreadyWhitelisted = false;

    if (!$hostEntryKept) {
        foreach ($newEntries as $entry) {
            if (ddns_admin_whitelist_entry_ip($entry) === $ip) {
                $alreadyWhitelisted = true;
                break;
            }
        }

        if (!$alreadyWhitelisted) {
            $newEntries[] = array(
                'ip' => $ip,
                'note' => $note,
            );
        }
    }

    if (serialize($entries) !== serialize($newEntries)) {
        ddns_admin_whitelist_save_whitelisted_ips($newEntries);
    }

    if ($alreadyWhitelisted) {
        return array(
            'success' => true,
            'message' => 'IP already existed in WHMCS WhitelistedIPs; duplicate module entry was not added.',
        );
    }

    if ($hostEntryRemoved || !$hostEntryKept) {
        return array(
            'success' => true,
            'message' => 'Module-managed WHMCS WhitelistedIPs entry was added/updated safely.',
        );
    }

    return array(
        'success' => true,
        'message' => 'Module-managed WHMCS WhitelistedIPs entry already matched.',
    );
}

/**
 * Read WHMCS WhitelistedIPs config without clearing invalid data.
 * Đọc cấu hình WhitelistedIPs của WHMCS mà không xoá dữ liệu không hợp lệ.
 *
 * @param string $error
 *
 * @return array|false
 */
function ddns_admin_whitelist_read_whitelisted_ips(&$error)
{
    $row = Capsule::table('tblconfiguration')
        ->where('setting', 'WhitelistedIPs')
        ->first();

    if (!$row || trim((string) $row->value) === '') {
        return array();
    }

    if (function_exists('safe_unserialize')) {
        $entries = safe_unserialize($row->value);
    } else {
        $entries = @unserialize($row->value);
    }

    if (!is_array($entries)) {
        $error = 'Current tblconfiguration WhitelistedIPs value is not a serialized array. It was not overwritten to avoid clearing existing data.';
        return false;
    }

    return $entries;
}

/**
 * Save WHMCS WhitelistedIPs config.
 * Lưu cấu hình WhitelistedIPs của WHMCS.
 *
 * @param array $entries
 *
 * @return void
 */
function ddns_admin_whitelist_save_whitelisted_ips(array $entries)
{
    $value = function_exists('safe_serialize') ? safe_serialize($entries) : serialize($entries);
    $now = ddns_admin_whitelist_now();
    $row = Capsule::table('tblconfiguration')
        ->where('setting', 'WhitelistedIPs')
        ->first();

    if ($row) {
        Capsule::table('tblconfiguration')
            ->where('setting', 'WhitelistedIPs')
            ->update(array(
                'value' => $value,
                'updated_at' => $now,
            ));
        return;
    }

    Capsule::table('tblconfiguration')->insert(array(
        'setting' => 'WhitelistedIPs',
        'value' => $value,
        'created_at' => $now,
        'updated_at' => $now,
    ));
}

/**
 * Remove a matching ban row after an IP is successfully whitelisted.
 * Xoá bản ghi chặn IP tương ứng sau khi IP được thêm vào whitelist thành công.
 *
 * @param string $ip
 *
 * @return int
 */
function ddns_admin_whitelist_unban_ip($ip)
{
    try {
        if (!Capsule::schema()->hasTable('tblbannedips')) {
            return 0;
        }

        return (int) Capsule::table('tblbannedips')
            ->where('ip', $ip)
            ->delete();
    } catch (\Exception $e) {
        return 0;
    }
}

/**
 * Resolve one public IPv4 address for a hostname.
 * Phân giải một địa chỉ IPv4 công khai cho hostname.
 *
 * @param string $hostname
 *
 * @return array
 */
function ddns_admin_whitelist_resolve_public_ipv4($hostname)
{
    $hostname = ddns_admin_whitelist_normalize_hostname($hostname);
    $error = '';

    if (!ddns_admin_whitelist_validate_hostname($hostname, $error)) {
        return array(
            'success' => false,
            'message' => $error,
        );
    }

    $ips = array();

    if (function_exists('dns_get_record') && defined('DNS_A')) {
        $records = @dns_get_record($hostname, DNS_A);
        if (is_array($records)) {
            foreach ($records as $record) {
                if (isset($record['ip'])) {
                    $ips[] = trim((string) $record['ip']);
                }
            }
        }
    }

    if (!$ips && function_exists('gethostbynamel')) {
        $fallback = @gethostbynamel($hostname);
        if (is_array($fallback)) {
            foreach ($fallback as $ip) {
                $ips[] = trim((string) $ip);
            }
        }
    }

    $ips = array_values(array_unique(array_filter($ips)));

    if (!$ips) {
        return array(
            'success' => false,
            'message' => 'No IPv4 A record was found for hostname ' . $hostname . '.',
        );
    }

    $publicIps = array();
    foreach ($ips as $ip) {
        if (ddns_admin_whitelist_is_public_ipv4($ip)) {
            $publicIps[] = $ip;
        }
    }

    if (!$publicIps) {
        return array(
            'success' => false,
            'message' => 'Hostname ' . $hostname . ' resolved only to private/reserved IPv4 address(es): ' . implode(', ', $ips) . '.',
        );
    }

    return array(
        'success' => true,
        'ip' => $publicIps[0],
        'message' => 'Resolved ' . $hostname . ' to ' . $publicIps[0] . '.',
    );
}

/**
 * Validate a public IPv4 address.
 * Kiểm tra tính hợp lệ của một địa chỉ IPv4 công khai.
 *
 * @param string $ip
 *
 * @return bool
 */
function ddns_admin_whitelist_is_public_ipv4($ip)
{
    $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

    return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
}

/**
 * Normalize hostname input.
 * Chuẩn hoá dữ liệu hostname đầu vào.
 *
 * @param string $hostname
 *
 * @return string
 */
function ddns_admin_whitelist_normalize_hostname($hostname)
{
    $hostname = strtolower(trim((string) $hostname));
    $hostname = rtrim($hostname, '.');

    return $hostname;
}

/**
 * Validate hostname syntax.
 * Kiểm tra cú pháp của hostname.
 *
 * @param string $hostname
 * @param string $error
 *
 * @return bool
 */
function ddns_admin_whitelist_validate_hostname($hostname, &$error)
{
    if ($hostname === '') {
        $error = 'Hostname is required.';
        return false;
    }

    if (strlen($hostname) > 253) {
        $error = 'Hostname is too long.';
        return false;
    }

    if (strpos($hostname, '.') === false) {
        $error = 'Hostname must be a fully-qualified DNS name.';
        return false;
    }

    if (preg_match('/[^a-z0-9.-]/', $hostname)) {
        $error = 'Hostname may contain only letters, numbers, dots, and hyphens.';
        return false;
    }

    $labels = explode('.', $hostname);
    foreach ($labels as $label) {
        $length = strlen($label);
        if ($length < 1 || $length > 63) {
            $error = 'Hostname contains an invalid DNS label length.';
            return false;
        }

        if (!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $label)) {
            $error = 'Hostname label "' . $label . '" is invalid.';
            return false;
        }
    }

    return true;
}

/**
 * Clamp interval to the required 15-30 minute range.
 * Giới hạn khoảng kiểm tra trong phạm vi bắt buộc 15-30 phút.
 *
 * @param mixed $minutes
 *
 * @return int
 */
function ddns_admin_whitelist_normalize_interval($minutes)
{
    $minutes = (int) $minutes;

    if ($minutes < 15) {
        return 15;
    }

    if ($minutes > 30) {
        return 30;
    }

    return $minutes;
}

/**
 * Default interval from module config.
 * Khoảng kiểm tra mặc định lấy từ cấu hình module.
 *
 * @param array $vars
 *
 * @return int
 */
function ddns_admin_whitelist_default_interval($vars)
{
    return ddns_admin_whitelist_normalize_interval(isset($vars['default_interval']) ? $vars['default_interval'] : 15);
}

/**
 * Get host row using posted host_id.
 * Lấy bản ghi hostname dựa trên host_id được gửi lên.
 *
 * @return object
 */
function ddns_admin_whitelist_get_host_from_post()
{
    $hostId = isset($_POST['host_id']) ? (int) $_POST['host_id'] : 0;

    if ($hostId <= 0) {
        throw new \Exception('Invalid host id.');
    }

    $host = Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->where('id', $hostId)
        ->first();

    if (!$host) {
        throw new \Exception('Host not found.');
    }

    return $host;
}

/**
 * Write module log row.
 * Ghi một bản ghi log của module.
 *
 * @param int|null    $hostId
 * @param string      $hostname
 * @param string|null $oldIp
 * @param string|null $newIp
 * @param int         $success
 * @param int         $changed
 * @param int         $skipped
 * @param string      $message
 *
 * @return void
 */
function ddns_admin_whitelist_log($hostId, $hostname, $oldIp, $newIp, $success, $changed, $skipped, $message)
{
    Capsule::table(DDNS_ADMIN_WHITELIST_LOGS_TABLE)->insert(array(
        'host_id' => $hostId ? (int) $hostId : null,
        'hostname' => (string) $hostname,
        'old_ip' => ddns_admin_whitelist_null_if_empty($oldIp),
        'new_ip' => ddns_admin_whitelist_null_if_empty($newIp),
        'success' => $success ? 1 : 0,
        'changed' => $changed ? 1 : 0,
        'skipped' => $skipped ? 1 : 0,
        'message' => (string) $message,
        'created_at' => ddns_admin_whitelist_now(),
    ));
}

/**
 * Render admin page.
 * Hiển thị trang admin.
 *
 * @param array $vars
 * @param array $messages
 *
 * @return string
 */
function ddns_admin_whitelist_render_admin($vars, array $messages)
{
    $modulelink = isset($vars['modulelink']) ? $vars['modulelink'] : 'addonmodules.php?module=' . DDNS_ADMIN_WHITELIST_MODULE;
    $csrfToken = generate_token('plain');
    $defaultInterval = ddns_admin_whitelist_default_interval($vars);
    $hosts = Capsule::table(DDNS_ADMIN_WHITELIST_HOSTS_TABLE)
        ->orderBy('hostname', 'asc')
        ->get();
    $logs = Capsule::table(DDNS_ADMIN_WHITELIST_LOGS_TABLE)
        ->orderBy('id', 'desc')
        ->limit(50)
        ->get();

    $html = '<div class="ddns-admin-whitelist">';
    $html .= '<style>';
    $html .= '.ddns-admin-whitelist .muted{color:#6c757d}.ddns-admin-whitelist .nowrap{white-space:nowrap}.ddns-admin-whitelist details{margin-top:8px}.ddns-admin-whitelist .inline-form{display:inline-block;margin:0 8px 8px 0}.ddns-admin-whitelist .table td{vertical-align:middle}.ddns-admin-whitelist .host-edit{padding:12px 12px 8px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;margin-top:8px}.ddns-admin-whitelist code{white-space:normal}.ddns-admin-whitelist .host-avatar{width:32px;height:32px;border-radius:8px;border:1px solid #dfe3ea;object-fit:cover;margin-right:10px;vertical-align:middle}.ddns-admin-whitelist .host-avatar-cell{width:52px}.ddns-admin-whitelist .host-row-title{display:inline-block;vertical-align:middle}.ddns-admin-whitelist .panel-heading{background:#f1f5f9;color:#1f2937;font-weight:600;border-bottom:1px solid #e5e7eb}.ddns-admin-whitelist .panel-footer{background:#f8f9fa}.ddns-admin-whitelist .table thead th{background:#f8f9fa}.ddns-admin-whitelist .btn-xs{padding:3px 8px;font-size:12px;line-height:1.5}.ddns-admin-whitelist .form-inline .form-group{margin-right:12px;margin-bottom:8px}.ddns-admin-whitelist .log-prune-note{font-size:12px;color:#6c757d;margin-top:8px}';
    $html .= '</style>';

    foreach ($messages as $message) {
        if (is_array($message) && isset($message['message'])) {
            $html .= ddns_admin_whitelist_alert(isset($message['type']) ? $message['type'] : 'info', $message['message']);
        }
    }

    $html .= '<div class="alert alert-info">';
    $html .= ddns_admin_whitelist_lang('msg_safety_rules');
    $html .= '</div>';

    $html .= '<div class="panel panel-default">';
    $html .= '<div class="panel-heading"><strong>' . ddns_admin_whitelist_lang('panel_add_hostname') . '</strong></div>';
    $html .= '<div class="panel-body">';
    $html .= '<form method="post" action="' . ddns_admin_whitelist_e($modulelink) . '" class="form-inline">';
    $html .= ddns_admin_whitelist_token_input($csrfToken);
    $html .= '<input type="hidden" name="ddns_action" value="add_host">';
    $html .= '<div class="form-group" style="margin-right:10px">';
    $html .= '<label>' . ddns_admin_whitelist_lang('lbl_hostname') . '&nbsp;</label><input type="text" name="hostname" class="form-control" style="min-width:280px" placeholder="admin-ddns.example.com" required>';
    $html .= '</div>';
    $html .= '<div class="form-group" style="margin-right:10px">';
    $html .= '<label>' . ddns_admin_whitelist_lang('lbl_interval') . '&nbsp;</label><input type="number" min="15" max="30" name="interval_minutes" class="form-control" style="width:90px" value="' . (int) $defaultInterval . '"> <span class="muted">' . ddns_admin_whitelist_lang('lbl_minutes') . '</span>';
    $html .= '</div>';
    $html .= '<label class="checkbox-inline" style="margin-right:10px"><input type="checkbox" name="enabled" value="1" checked> ' . ddns_admin_whitelist_lang('lbl_enabled') . '</label>';
    $html .= '<button type="submit" class="btn btn-primary">' . ddns_admin_whitelist_lang('btn_add_check') . '</button>';
    $html .= '</form>';
    $html .= '</div></div>';

    $html .= '<div class="panel panel-default">';
    $html .= '<div class="panel-heading"><strong>' . ddns_admin_whitelist_lang('panel_managed_hosts') . '</strong></div>';
    $html .= '<div class="panel-body">';
    $html .= '<form method="post" action="' . ddns_admin_whitelist_e($modulelink) . '" class="inline-form">';
    $html .= ddns_admin_whitelist_token_input($csrfToken);
    $html .= '<input type="hidden" name="ddns_action" value="check_all">';
    $html .= '<button type="submit" class="btn btn-success">' . ddns_admin_whitelist_lang('btn_check_all') . '</button>';
    $html .= '</form>';
    $html .= '<span class="muted">' . ddns_admin_whitelist_lang('msg_cron_note') . '</span>';
    $html .= '</div>';
    $html .= ddns_admin_whitelist_render_hosts_table($hosts, $modulelink, $csrfToken);
    $html .= '</div>';

    $html .= '<div class="panel panel-default">';
    $html .= '<div class="panel-heading"><strong>' . ddns_admin_whitelist_lang('panel_latest_logs') . '</strong></div>';
    $html .= ddns_admin_whitelist_render_logs_table($logs);
    $html .= '<div class="panel-footer">';
    $html .= '<form method="post" action="' . ddns_admin_whitelist_e($modulelink) . '" class="form-inline">';
    $html .= ddns_admin_whitelist_token_input($csrfToken);
    $html .= '<input type="hidden" name="ddns_action" value="prune_logs">';
    $html .= '<label>' . ddns_admin_whitelist_lang('lbl_delete_logs_older') . '&nbsp;</label><input type="number" min="0" name="days" value="90" class="form-control" style="width:90px"> <span>' . ddns_admin_whitelist_lang('lbl_days') . '&nbsp;</span>';
    $html .= '<button type="submit" class="btn btn-default">' . ddns_admin_whitelist_lang('btn_prune_logs') . '</button>';
    $html .= '</form>';
    $html .= '<div class="log-prune-note">' . ddns_admin_whitelist_lang('msg_prune_note') . '</div>';
    $html .= '</div></div>';
    $html .= '</div>';

    return $html;
}

/**
 * Render host list table.
 * Hiển thị bảng danh sách hostname.
 *
 * @param iterable $hosts
 * @param string   $modulelink
 * @param string   $csrfToken
 *
 * @return string
 */
function ddns_admin_whitelist_render_hosts_table($hosts, $modulelink, $csrfToken)
{
    $html = '<div class="table-responsive"><table class="table table-striped table-bordered">';
    $html .= '<thead><tr>';
    $html .= '<th></th><th>' . ddns_admin_whitelist_lang('th_hostname') . '</th><th>' . ddns_admin_whitelist_lang('th_status') . '</th><th>' . ddns_admin_whitelist_lang('th_current_ip') . '</th><th>' . ddns_admin_whitelist_lang('th_previous_ip') . '</th><th>' . ddns_admin_whitelist_lang('th_interval') . '</th><th>' . ddns_admin_whitelist_lang('th_last_check') . '</th><th>' . ddns_admin_whitelist_lang('th_last_success') . '</th><th>' . ddns_admin_whitelist_lang('th_message') . '</th><th>' . ddns_admin_whitelist_lang('th_actions') . '</th>';
    $html .= '</tr></thead><tbody>';

    $hasRows = false;
    foreach ($hosts as $host) {
        $hasRows = true;
        $statusClass = !empty($host->enabled) ? 'label-success' : 'label-default';
        $statusText = !empty($host->enabled) ? ddns_admin_whitelist_lang('status_enabled') : ddns_admin_whitelist_lang('status_disabled');
        $currentIp = ddns_admin_whitelist_clean_ip($host->current_ip);
        $previousIp = ddns_admin_whitelist_clean_ip($host->previous_ip);

        $html .= '<tr>';
        $html .= '<td class="host-avatar-cell">' . ddns_admin_whitelist_avatar_html($host->hostname) . '</td>';
        $html .= '<td><span class="host-row-title"><strong>' . ddns_admin_whitelist_e($host->hostname) . '</strong><br><span class="muted">' . ddns_admin_whitelist_lang('lbl_id_prefix') . (int) $host->id . '</span></span></td>';
        $html .= '<td><span class="label ' . $statusClass . '">' . $statusText . '</span></td>';
        $html .= '<td class="nowrap">' . ($currentIp !== '' ? '<code>' . ddns_admin_whitelist_e($currentIp) . '</code>' : '<span class="muted">' . ddns_admin_whitelist_lang('val_none') . '</span>') . '</td>';
        $html .= '<td class="nowrap">' . ($previousIp !== '' ? '<code>' . ddns_admin_whitelist_e($previousIp) . '</code>' : '<span class="muted">' . ddns_admin_whitelist_lang('val_none') . '</span>') . '</td>';
        $html .= '<td>' . (int) ddns_admin_whitelist_normalize_interval($host->interval_minutes) . ' ' . ddns_admin_whitelist_lang('unit_min') . '</td>';
        $html .= '<td class="nowrap">' . ddns_admin_whitelist_e(ddns_admin_whitelist_format_datetime($host->last_checked_at)) . '</td>';
        $html .= '<td class="nowrap">' . ddns_admin_whitelist_e(ddns_admin_whitelist_format_datetime($host->last_success_at)) . '</td>';
        $html .= '<td>' . ddns_admin_whitelist_e($host->last_message) . '</td>';
        $html .= '<td class="nowrap">';
        $html .= ddns_admin_whitelist_action_form($modulelink, $csrfToken, 'check_host', $host->id, ddns_admin_whitelist_lang('btn_check_now'), 'btn btn-xs btn-success', '');

        if (!empty($host->enabled)) {
            $html .= ddns_admin_whitelist_action_form($modulelink, $csrfToken, 'toggle_host', $host->id, ddns_admin_whitelist_lang('btn_disable'), 'btn btn-xs btn-default', '<input type="hidden" name="enabled" value="0">');
        } else {
            $html .= ddns_admin_whitelist_action_form($modulelink, $csrfToken, 'toggle_host', $host->id, ddns_admin_whitelist_lang('btn_enable'), 'btn btn-xs btn-primary', '<input type="hidden" name="enabled" value="1">');
        }

        $html .= ddns_admin_whitelist_action_form($modulelink, $csrfToken, 'delete_host', $host->id, ddns_admin_whitelist_lang('btn_delete_record'), 'btn btn-xs btn-danger', '', 'return confirm(\'' . ddns_admin_whitelist_lang('msg_delete_confirm') . '\');');
        $html .= '<details><summary>' . ddns_admin_whitelist_lang('btn_edit') . '</summary>';
        $html .= '<div class="host-edit">';
        $html .= '<form method="post" action="' . ddns_admin_whitelist_e($modulelink) . '">';
        $html .= ddns_admin_whitelist_token_input($csrfToken);
        $html .= '<input type="hidden" name="ddns_action" value="update_host">';
        $html .= '<input type="hidden" name="host_id" value="' . (int) $host->id . '">';
        $html .= '<div class="form-group"><label>' . ddns_admin_whitelist_lang('lbl_hostname') . '</label><input type="text" name="hostname" class="form-control input-sm" value="' . ddns_admin_whitelist_e($host->hostname) . '" required></div>';
        $html .= '<div class="form-group"><label>' . ddns_admin_whitelist_lang('lbl_interval') . '</label><input type="number" min="15" max="30" name="interval_minutes" class="form-control input-sm" value="' . (int) ddns_admin_whitelist_normalize_interval($host->interval_minutes) . '"></div>';
        $html .= '<label><input type="checkbox" name="enabled" value="1"' . (!empty($host->enabled) ? ' checked' : '') . '> ' . ddns_admin_whitelist_lang('lbl_enabled') . '</label><br>';
        $html .= '<button type="submit" class="btn btn-xs btn-primary">' . ddns_admin_whitelist_lang('btn_save_check') . '</button>';
        $html .= '</form>';
        $html .= '</div></details>';
        $html .= '</td>';
        $html .= '</tr>';
    }

    if (!$hasRows) {
        $html .= '<tr><td colspan="10" class="text-center muted">' . ddns_admin_whitelist_lang('msg_no_hosts') . '</td></tr>';
    }

    $html .= '</tbody></table></div>';

    return $html;
}

/**
 * Render recent logs table.
 * Hiển thị bảng log gần đây.
 *
 * @param iterable $logs
 *
 * @return string
 */
function ddns_admin_whitelist_render_logs_table($logs)
{
    $html = '<div class="table-responsive"><table class="table table-condensed table-bordered">';
    $html .= '<thead><tr><th>' . ddns_admin_whitelist_lang('th_time') . '</th><th>' . ddns_admin_whitelist_lang('th_hostname') . '</th><th>' . ddns_admin_whitelist_lang('th_old_ip') . '</th><th>' . ddns_admin_whitelist_lang('th_new_ip') . '</th><th>' . ddns_admin_whitelist_lang('th_status') . '</th><th>' . ddns_admin_whitelist_lang('th_message') . '</th></tr></thead><tbody>';
    $hasRows = false;

    foreach ($logs as $log) {
        $hasRows = true;
        $label = !empty($log->success) ? 'label-success' : (!empty($log->skipped) ? 'label-warning' : 'label-danger');
        $status = !empty($log->success) ? ddns_admin_whitelist_lang('status_success') : (!empty($log->skipped) ? ddns_admin_whitelist_lang('status_skipped') : ddns_admin_whitelist_lang('status_failed'));

        if (!empty($log->changed)) {
            $status .= ' / ' . ddns_admin_whitelist_lang('status_changed');
        }

        $html .= '<tr>';
        $html .= '<td class="nowrap">' . ddns_admin_whitelist_e(ddns_admin_whitelist_format_datetime($log->created_at)) . '</td>';
        $html .= '<td>' . ddns_admin_whitelist_e($log->hostname) . '</td>';
        $html .= '<td class="nowrap">' . ddns_admin_whitelist_e(ddns_admin_whitelist_clean_ip($log->old_ip)) . '</td>';
        $html .= '<td class="nowrap">' . ddns_admin_whitelist_e(ddns_admin_whitelist_clean_ip($log->new_ip)) . '</td>';
        $html .= '<td><span class="label ' . $label . '">' . ddns_admin_whitelist_e($status) . '</span></td>';
        $html .= '<td>' . ddns_admin_whitelist_e($log->message) . '</td>';
        $html .= '</tr>';
    }

    if (!$hasRows) {
        $html .= '<tr><td colspan="6" class="text-center muted">' . ddns_admin_whitelist_lang('msg_no_logs') . '</td></tr>';
    }

    $html .= '</tbody></table></div>';

    return $html;
}

/**
 * Render a small POST action form.
 * Hiển thị một biểu mẫu hành động POST nhỏ.
 *
 * @param string $modulelink
 * @param string $csrfToken
 * @param string $action
 * @param int    $hostId
 * @param string $label
 * @param string $class
 * @param string $extraInputs
 * @param string $onsubmit
 *
 * @return string
 */
function ddns_admin_whitelist_action_form($modulelink, $csrfToken, $action, $hostId, $label, $class, $extraInputs, $onsubmit = '')
{
    $html = '<form method="post" action="' . ddns_admin_whitelist_e($modulelink) . '" class="inline-form"' . ($onsubmit ? ' onsubmit="' . ddns_admin_whitelist_e($onsubmit) . '"' : '') . '>';
    $html .= ddns_admin_whitelist_token_input($csrfToken);
    $html .= '<input type="hidden" name="ddns_action" value="' . ddns_admin_whitelist_e($action) . '">';
    $html .= '<input type="hidden" name="host_id" value="' . (int) $hostId . '">';
    $html .= $extraInputs;
    $html .= '<button type="submit" class="' . ddns_admin_whitelist_e($class) . '">' . ddns_admin_whitelist_e($label) . '</button>';
    $html .= '</form>';

    return $html;
}

/**
 * Alert markup.
 * Mã HTML cho khối thông báo.
 *
 * @param string $type
 * @param string $message
 *
 * @return string
 */
function ddns_admin_whitelist_alert($type, $message)
{
    $type = in_array($type, array('success', 'info', 'warning', 'danger')) ? $type : 'info';

    return '<div class="alert alert-' . $type . '">' . ddns_admin_whitelist_e($message) . '</div>';
}

/**
 * Hidden CSRF token input.
 * Ô input ẩn chứa CSRF token.
 *
 * @param string $token
 *
 * @return string
 */
function ddns_admin_whitelist_token_input($token)
{
    return '<input type="hidden" name="token" value="' . ddns_admin_whitelist_e($token) . '">';
}

/**
 * Render an avatar for a hostname when available.
 * Hiển thị avatar cho hostname khi có thể.
 *
 * @param string $hostname
 *
 * @return string
 */
function ddns_admin_whitelist_avatar_html($hostname)
{
    $hostname = trim((string) $hostname);
    $source = 'https://icons.duckduckgo.com/ip3/' . rawurlencode($hostname) . '.ico';
    $fallback = ddns_admin_whitelist_asset_url('modules/addons/' . DDNS_ADMIN_WHITELIST_MODULE . '/image.png');

    return '<img src="' . ddns_admin_whitelist_e($source) . '" class="host-avatar" alt="" onerror="this.onerror=null;this.src=\'' . ddns_admin_whitelist_e($fallback) . '\';">';
}

/**
 * Build a public URL for a module asset relative to the admin script.
 * Tạo URL công khai cho tài nguyên của module tương đối với script admin.
 *
 * @param string $path
 *
 * @return string
 */
function ddns_admin_whitelist_asset_url($path)
{
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '';
    $base = dirname($scriptName);
    if ($base === '\\' || $base === '.') {
        $base = '';
    }
    $url = rtrim($base, '/') . '/../' . ltrim($path, '/');

    return str_replace('\\', '/', $url);
}

/**
 * Build module note.
 * Tạo ghi chú của module.
 *
 * @param object $host
 * @param string $ip
 *
 * @return string
 */
function ddns_admin_whitelist_note_for_host($host, $ip)
{
    return DDNS_ADMIN_WHITELIST_NOTE_PREFIX . (int) $host->id . '] ' . $host->hostname . ' => ' . $ip . ' (managed; do not edit manually)';
}

/**
 * Check if whitelist entry belongs to one host id.
 * Kiểm tra một mục whitelist có thuộc về một host id hay không.
 *
 * @param mixed $entry
 * @param int   $hostId
 *
 * @return bool
 */
function ddns_admin_whitelist_entry_is_for_host($entry, $hostId)
{
    if (!is_array($entry) || !isset($entry['note'])) {
        return false;
    }

    return strpos((string) $entry['note'], DDNS_ADMIN_WHITELIST_NOTE_PREFIX . (int) $hostId . ']') !== false;
}

/**
 * Get IP from one whitelist entry.
 * Lấy IP từ một mục whitelist.
 *
 * @param mixed $entry
 *
 * @return string
 */
function ddns_admin_whitelist_entry_ip($entry)
{
    if (!is_array($entry) || !isset($entry['ip'])) {
        return '';
    }

    return ddns_admin_whitelist_clean_ip($entry['ip']);
}

/**
 * Clean IP value.
 * Làm sạch giá trị IP.
 *
 * @param mixed $ip
 *
 * @return string
 */
function ddns_admin_whitelist_clean_ip($ip)
{
    return trim((string) $ip);
}

/**
 * Null if empty string.
 * Trả về null nếu chuỗi rỗng.
 *
 * @param mixed $value
 *
 * @return string|null
 */
function ddns_admin_whitelist_null_if_empty($value)
{
    $value = trim((string) $value);

    return $value === '' ? null : $value;
}

/**
 * Format date display.
 * Định dạng hiển thị ngày giờ.
 *
 * @param mixed $value
 *
 * @return string
 */
function ddns_admin_whitelist_format_datetime($value)
{
    $value = trim((string) $value);

    if ($value === '' || $value === '0000-00-00 00:00:00') {
        return '-';
    }

    return $value;
}

/**
 * Escape HTML.
 * Thoát các ký tự HTML.
 *
 * @param mixed $value
 *
 * @return string
 */
function ddns_admin_whitelist_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Current SQL datetime.
 * Lấy ngày giờ SQL hiện tại.
 *
 * @return string
 */
function ddns_admin_whitelist_now()
{
    return date('Y-m-d H:i:s');
}

}
