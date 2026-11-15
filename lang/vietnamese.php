<?php
/**
 * DDNS Admin Whitelist - Vietnamese language strings.
 *
 * Vietnamese language file for the DDNS Admin Whitelist addon module of
 * HiTechCloud (org: hitechcloudvn).
 *
 * File ngôn ngữ tiếng Việt cho module addon DDNS Admin Whitelist của
 * HiTechCloud (org: hitechcloudvn).
 */

return array(
    // Config field labels and descriptions.
    // Nhãn và mô tả cho các trường cấu hình.
    'cfg_default_interval_name' => 'Khoảng kiểm tra mặc định',
    'cfg_default_interval_desc' => 'Số phút giữa các lần kiểm tra đối với hostname mới thêm vào. Giá trị được giới hạn trong khoảng 15-30 phút.',
    'cfg_cron_enabled_name' => 'Bật kiểm tra tự động (cron)',
    'cfg_cron_enabled_desc' => 'Chạy kiểm tra hostname từ hook AfterCronJob của WHMCS.',
    'cfg_ui_language_name' => 'Ngôn ngữ giao diện admin',
    'cfg_ui_language_desc' => 'Ngôn ngữ của giao diện quản trị module.',

    // Panel headings.
    // Tiêu đề các bảng điều khiển.
    'panel_add_hostname' => 'Thêm hostname DDNS',
    'panel_managed_hosts' => 'Hostname đang quản lý',
    'panel_latest_logs' => 'Nhật ký gần đây',

    // Form labels.
    // Nhãn biểu mẫu.
    'lbl_hostname' => 'Tên hostname',
    'lbl_interval' => 'Khoảng kiểm tra',
    'lbl_enabled' => 'Bật',
    'lbl_minutes' => 'phút',
    'lbl_days' => 'ngày',
    'lbl_delete_logs_older' => 'Xoá bản ghi cũ hơn',
    'lbl_id_prefix' => 'ID #',

    // Units.
    // Đơn vị.
    'unit_min' => 'phút',

    // Buttons.
    // Nút thao tác.
    'btn_add_check' => 'Thêm & kiểm tra ngay',
    'btn_check_all' => 'Kiểm tra tất cả đang bật',
    'btn_check_now' => 'Kiểm tra ngay',
    'btn_disable' => 'Vô hiệu hoá',
    'btn_enable' => 'Bật',
    'btn_delete_record' => 'Xoá bản ghi',
    'btn_edit' => 'Chỉnh sửa',
    'btn_save_check' => 'Lưu & kiểm tra',
    'btn_prune_logs' => 'Dọn log',

    // Table headers.
    // Tiêu đề cột bảng.
    'th_hostname' => 'Hostname',
    'th_status' => 'Trạng thái',
    'th_current_ip' => 'IP hiện tại',
    'th_previous_ip' => 'IP trước đó',
    'th_interval' => 'Khoảng kiểm tra',
    'th_last_check' => 'Lần kiểm tra cuối',
    'th_last_success' => 'Lần thành công cuối',
    'th_message' => 'Thông điệp',
    'th_actions' => 'Thao tác',
    'th_time' => 'Thời gian',
    'th_old_ip' => 'IP cũ',
    'th_new_ip' => 'IP mới',

    // Statuses.
    // Trạng thái.
    'status_enabled' => 'Đã bật',
    'status_disabled' => 'Đã tắt',
    'status_success' => 'Thành công',
    'status_skipped' => 'Bỏ qua',
    'status_failed' => 'Thất bại',
    'status_changed' => 'Đã thay đổi',

    // Values.
    // Giá trị hiển thị.
    'val_none' => 'không có',

    // Messages and notes.
    // Thông điệp và ghi chú.
    'msg_safety_rules' => '<strong>Quy tắc an toàn:</strong> addon này chỉ cập nhật các mục được gắn thẻ <code>ddns_admin_whitelist</code>, giữ nguyên các mục whitelist WHMCS được thêm thủ công, không bao giờ xoá toàn bộ <code>WhitelistedIPs</code>, và giữ IP đã lưu gần nhất khi phân giải DNS thất bại.',
    'msg_cron_note' => 'Kiểm tra theo cron sử dụng AfterCronJob và khoảng kiểm tra của mỗi hostname được giới hạn trong 15-30 phút.',
    'msg_prune_note' => 'Nhập 0 để xoá toàn bộ log; giá trị dương tối thiểu là 7 ngày.',
    'msg_no_hosts' => 'Chưa có hostname nào được thêm.',
    'msg_no_logs' => 'Chưa có bản ghi nhật ký nào.',
    'msg_delete_confirm' => 'Xoá bản ghi hostname này của module? Các mục WhitelistedIPs hiện có của WHMCS sẽ được giữ nguyên.',
);
