# Hướng dẫn cài đặt — DDNS Admin Whitelist

[Tiếng Việt](huong-dan-cai-dat.md) | [English](../en/installation.md)

## Yêu cầu hệ thống / Requirements

| Thành phần | Phiên bản |
|---|---|
| WHMCS | 8.0 trở lên |
| PHP | 7.4 – 8.3 |
| MySQL / MariaDB | Theo yêu cầu của WHMCS |
| Quyền admin WHMCS | Có quyền cài addon module |

> ⚠️ **Quan trọng:** trước khi kích hoạt, hãy đảm bảo bạn có **một đường truy cập admin thay thế** (IP tĩnh, VPN, hoặc tạm tắt whitelist trong `configuration.php`) để tránh bị khoá ngoài khi thử nghiệm.

## Cài đặt / Installation

### 1. Tải mã nguồn

Cách 1 — dùng bản phát hành (khuyến nghị):

1. Vào [Releases](https://github.com/hitechcloudvn/ddns_admin_whitelist/releases).
2. Tải file `ddns_admin_whitelist-vX.Y.Z.zip` mới nhất.
3. Giải nén — thư mục con `ddns_admin_whitelist/` chứa toàn bộ mã nguồn.

Cách 2 — clone từ kho:

```bash
cd /path/to/whmcs/modules/addons
git clone https://github.com/hitechcloudvn/ddns_admin_whitelist.git
```

### 2. Sao chép vào WHMCS

Đặt thư mục module tại:

```text
<whmcs-root>/modules/addons/ddns_admin_whitelist/
```

Cấu trúc sau khi cài / Expected layout:

```text
modules/addons/ddns_admin_whitelist/
├── ddns_admin_whitelist.php
├── hooks.php
├── lang/
│   ├── english.php
│   └── vietnamese.php
└── ...
```

### 3. Kích hoạt module

1. Đăng nhập WHMCS Admin.
2. Vào **System → Addon Modules** (Hệ thống → Module bổ trợ).
3. Tìm **DDNS Admin Whitelist**, nhấn **Activate** (Kích hoạt).
4. Cấp quyền cho nhóm admin của bạn (Access Control), nhấn **Save Changes**.

Module tự tạo hai bảng khi kích hoạt / The module creates two tables on activation:

- `mod_ddns_admin_whitelist_hosts`
- `mod_ddns_admin_whitelist_logs`

### 4. Cấu hình

Trong trang cấu hình module / In the module configuration page:

| Trường | Mô tả | Mặc định |
|---|---|---|
| Ngôn ngữ giao diện / UI Language | Tiếng Việt hoặc English | english |
| Khoảng kiểm tra (phút) / Check interval | Ép trong khoảng 15–30 phút | 20 |
| Giữ log (ngày) / Log retention | Số ngày giữ nhật ký | 30 |

Nhấn **Save Changes** rồi thêm hostname đầu tiên (xem [Hướng dẫn sử dụng](huong-dan-su-dung.md)).

## Kiểm tra cron / Cron verification

Module chạy kiểm tra tự động qua hook `AfterCronJob`. Đảm bảo cron của WHMCS được cấu hình đúng:

```bash
# Kiểm tra cron job của hệ thống / Verify the system cron job
crontab -l | grep whmcs
```

Cron của WHMCS phải chạy ít nhất 5 phút/lần để hook hoạt động chính xác.

The WHMCS cron must run at least every 5 minutes for the hook to work correctly.

## Nâng cấp / Upgrade

1. Sao lưu hai bảng `mod_ddns_admin_whitelist_*` (tuỳ chọn nhưng nên làm).
2. Ghi lại phiên bản hiện tại (System → Addon Modules).
3. Thay toàn bộ file trong thư mục module bằng bản mới — **giữ nguyên** file `lang/` nếu bạn có tuỳ chỉnh ngôn ngữ.
4. Vào **System → Addon Modules → DDNS Admin Whitelist** — module tự chạy nâng cấp schema khi cần.
5. Kiểm tra tab **Logs** để xác nhận hoạt động bình thường.

## Gỡ cài đặt / Uninstall

1. Vào **System → Addon Modules**, nhấn **Deactivate** (Huỷ kích hoạt).
   - Deactivate **xoá hai bảng module** và các mục whitelist có đánh dấu `[ddns_admin_whitelist:<id>]` — thao tác này không hoàn tác được.
2. Xoá thư mục `modules/addons/ddns_admin_whitelist/`.

> Mẹo: trước khi deactivate, hãy chụp lại danh sách hostname trong tab **Hosts** để cài lại nhanh sau này.

## Sự cố thường gặp khi cài / Common install issues

| Triệu chứng | Nguyên nhân / Cách xử lý |
|---|---|
| Module không xuất hiện trong Addon Modules | Sai vị trí thư mục — phải đúng `modules/addons/ddns_admin_whitelist/` |
| Lỗi khi Activate | Tài khoản DB thiếu quyền CREATE TABLE |
| Không thấy hostname được kiểm tra tự động | Cron WHMCS chưa chạy hoặc interval đặt ngoài 15–30 phút |
| Bị khoá ngoài admin | Dùng đường truy cập thay thế; sửa `WhitelistedIPs` trong `configuration.php` hoặc qua database |

Chi tiết xử lý sự cố: [Hướng dẫn sử dụng — phần Xử lý sự cố](huong-dan-su-dung.md#xử-lý-sự-cố).

---

[Bảng tài liệu](../../README.md#tài-liệu) · [Hướng dẫn sử dụng](huong-dan-su-dung.md)
