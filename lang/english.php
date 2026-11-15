<?php
/**
 * DDNS Admin Whitelist - English language strings.
 *
 * English language file for the DDNS Admin Whitelist addon module of
 * HiTechCloud (org: hitechcloudvn).
 *
 * File ngôn ngữ tiếng Anh cho module addon DDNS Admin Whitelist của
 * HiTechCloud (org: hitechcloudvn).
 */

return array(
    // Config field labels and descriptions.
    // Nhãn và mô tả cho các trường cấu hình.
    'cfg_default_interval_name' => 'Default Check Interval',
    'cfg_default_interval_desc' => 'Minutes between checks for newly added hostnames. Values are clamped to 15-30 minutes.',
    'cfg_cron_enabled_name' => 'Enable Cron Checks',
    'cfg_cron_enabled_desc' => 'Run hostname checks from the WHMCS AfterCronJob hook.',
    'cfg_ui_language_name' => 'Admin UI Language',
    'cfg_ui_language_desc' => 'Language of the module admin UI.',

    // Panel headings.
    // Tiêu đề các bảng điều khiển.
    'panel_add_hostname' => 'Add DDNS Hostname',
    'panel_managed_hosts' => 'Managed Hostnames',
    'panel_latest_logs' => 'Latest Logs',

    // Form labels.
    // Nhãn biểu mẫu.
    'lbl_hostname' => 'Hostname',
    'lbl_interval' => 'Interval',
    'lbl_enabled' => 'Enabled',
    'lbl_minutes' => 'minutes',
    'lbl_days' => 'days',
    'lbl_delete_logs_older' => 'Delete logs older than',
    'lbl_id_prefix' => 'ID #',

    // Units.
    // Đơn vị.
    'unit_min' => 'min',

    // Buttons.
    // Nút thao tác.
    'btn_add_check' => 'Add & Check Now',
    'btn_check_all' => 'Check All Enabled Now',
    'btn_check_now' => 'Check Now',
    'btn_disable' => 'Disable',
    'btn_enable' => 'Enable',
    'btn_delete_record' => 'Delete Record',
    'btn_edit' => 'Edit',
    'btn_save_check' => 'Save & Check',
    'btn_prune_logs' => 'Prune Logs',

    // Table headers.
    // Tiêu đề cột bảng.
    'th_hostname' => 'Hostname',
    'th_status' => 'Status',
    'th_current_ip' => 'Current IP',
    'th_previous_ip' => 'Previous IP',
    'th_interval' => 'Interval',
    'th_last_check' => 'Last Check',
    'th_last_success' => 'Last Success',
    'th_message' => 'Message',
    'th_actions' => 'Actions',
    'th_time' => 'Time',
    'th_old_ip' => 'Old IP',
    'th_new_ip' => 'New IP',

    // Statuses.
    // Trạng thái.
    'status_enabled' => 'Enabled',
    'status_disabled' => 'Disabled',
    'status_success' => 'Success',
    'status_skipped' => 'Skipped',
    'status_failed' => 'Failed',
    'status_changed' => 'Changed',

    // Values.
    // Giá trị hiển thị.
    'val_none' => 'none',

    // Messages and notes.
    // Thông điệp và ghi chú.
    'msg_safety_rules' => '<strong>Safety rules:</strong> this addon updates only entries tagged with <code>ddns_admin_whitelist</code>, preserves manual WHMCS whitelist entries, never clears all <code>WhitelistedIPs</code>, and keeps the latest stored IP when DNS resolution fails.',
    'msg_cron_note' => 'Cron checks use AfterCronJob and each host interval is clamped to 15-30 minutes.',
    'msg_prune_note' => 'Enter 0 to delete all logs; minimum positive value is 7 days.',
    'msg_no_hosts' => 'No hostnames added yet.',
    'msg_no_logs' => 'No logs yet.',
    'msg_delete_confirm' => 'Delete this module host record? Existing WHMCS WhitelistedIPs entries will be preserved.',
);
