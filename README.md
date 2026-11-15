<div align="center">

# DDNS Admin Whitelist

**Addon WHMCS tự động phân giải hostname DDNS và cập nhật an toàn whitelist IP đăng nhập admin**

**Tiếng Việt** | [English](README.en.md)

[![License](https://img.shields.io/badge/License-Source--Available%20Non--Commercial-red?style=flat-square)](LICENSE)
[![WHMCS](https://img.shields.io/badge/WHMCS-8.0%2B-0073AA?style=flat-square)](docs/vi/huong-dan-cai-dat.md)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![CI](https://img.shields.io/github/actions/workflow/status/hitechcloudvn/ddns_admin_whitelist/ci.yml?branch=main&style=flat-square&label=CI)](https://github.com/hitechcloudvn/ddns_admin_whitelist/actions/workflows/ci.yml)
[![Made in Vietnam](https://img.shields.io/badge/Made%20in-Vietnam-DA251D?style=flat-square)](https://hitechcloud.vn)

[Giới thiệu](#giới-thiệu) · [Tính năng](#tính-năng) · [Cài đặt nhanh](#cài-đặt-nhanh) · [Tài liệu](#tài-liệu) · [Giấy phép](#giấy-phép) · [Hỗ trợ](#hỗ-trợ)

</div>

---

## Giới thiệu

**DDNS Admin Whitelist** là addon module cho WHMCS: cứ mỗi 15–30 phút, module phân giải các hostname DDNS của bạn và cập nhật **an toàn** danh sách IP được phép đăng nhập admin (`WhitelistedIPs`) với địa chỉ IPv4 công khai mới nhất.

Module chỉ quản lý các mục được đánh dấu `[ddns_admin_whitelist:<id>]`, **giữ nguyên** mọi mục whitelist nhập tay, không bao giờ xoá trắng `WhitelistedIPs`, và giữ IP gần nhất khi phân giải DNS thất bại — tránh bị khoá ngoài admin.

- Kho mã nguồn: [github.com/hitechcloudvn/ddns_admin_whitelist](https://github.com/hitechcloudvn/ddns_admin_whitelist)
- Gương GitLab: [gitlab.com/hitechcloudvn/ddns_admin_whitelist](https://gitlab.com/hitechcloudvn/ddns_admin_whitelist)

## Tính năng

| | |
|---|---|
| ✅ Phân giải DNS A tự động | Resolves public IPv4 via `dns_get_record` with `gethostbynamel` fallback |
| ✅ Cập nhật whitelist có kiểm soát | Chỉ thêm/sửa mục có đánh dấu của module; giữ nguyên mục nhập tay |
| ✅ An toàn khi DNS lỗi | Giữ IP gần nhất, ghi log, không xoá whitelist |
| ✅ Chống trùng IP | Bỏ qua hostname nếu IP đã thuộc hostname khác đang bật |
| ✅ Tự gỡ chặn IP | Xoá dòng khớp trong `tblbannedips` sau khi whitelist thành công |
| ✅ Kiểm tra theo lịch | Hook `AfterCronJob`, khoảng kiểm tra ép trong 15–30 phút |
| ✅ Giao diện admin song ngữ | Tiếng Việt / English, chọn trong cấu hình module |
| ✅ Nhật ký hoạt động | Bảng log chi tiết + dọn log theo số ngày |
| ✅ Bảo mật biểu mẫu | CSRF token qua `check_token`, escape toàn bộ HTML đầu ra |

## Hỗ trợ nền tảng

| Nền tảng | Phiên bản | Ghi chú |
|---|---|---|
| WHMCS | 8.0 trở lên | Addon module + hook `AfterCronJob` |
| PHP | 7.4 – 8.3 | Đã kiểm chứng cú pháp qua CI matrix |
| MySQL / MariaDB | Theo WHMCS | Tự tạo bảng `mod_ddns_admin_whitelist_*` khi kích hoạt |

## Cấu trúc repo

```text
ddns_admin_whitelist/
├── ddns_admin_whitelist.php   # Module chính / Main addon module
├── hooks.php                  # Hook AfterCronJob / AfterCronJob hook
├── lang/                      # Ngôn ngữ giao diện EN + VI / UI language files
├── docs/
│   ├── vi/                    # Tài liệu tiếng Việt
│   └── en/                    # English documentation
├── .github/workflows/         # CI + Release trên GitHub Actions
└── .gitlab-ci.yml             # Gương CI/CD trên GitLab
```

## Cài đặt nhanh

1. Tải bản phát hành mới nhất từ [Releases](https://github.com/hitechcloudvn/ddns_admin_whitelist/releases).
2. Giải nén và sao chép thư mục vào `modules/addons/ddns_admin_whitelist/`.
3. Vào **WHMCS Admin → System → Addon Modules**, kích hoạt **DDNS Admin Whitelist**.
4. Thêm hostname DDNS của bạn, đặt khoảng kiểm tra (15–30 phút), nhấn **Add & Check Now**.
5. Đảm bảo cron của WHMCS chạy bình thường — hook `AfterCronJob` sẽ tự kiểm tra theo lịch.

Chi tiết: [docs/vi/huong-dan-cai-dat.md](docs/vi/huong-dan-cai-dat.md) · [docs/vi/huong-dan-su-dung.md](docs/vi/huong-dan-su-dung.md)

## Tài liệu

| Tài liệu | Mô tả |
|---|---|
| [Hướng dẫn cài đặt](docs/vi/huong-dan-cai-dat.md) | Cài đặt, nâng cấp, gỡ cài đặt |
| [Hướng dẫn sử dụng](docs/vi/huong-dan-su-dung.md) | Quản lý hostname, cơ chế an toàn, xử lý sự cố |
| [Changelog](CHANGELOG.md) | Lịch sử thay đổi theo Keep a Changelog |
| [Đóng góp](CONTRIBUTING.md) | Quy trình đóng góp mã nguồn |
| [Bảo mật](SECURITY.md) | Chính sách báo cáo lỗ hổng |

## Giấy phép

Đây là **giấy phép nguồn-mở-có-điều-kiện** (source-available), **không phải** giấy phép mã nguồn mở:

- ✅ **Được phép**: cài đặt và vận hành nội bộ; sao chép; sửa đổi cho nội bộ; nghiên cứu, giáo dục.
- ❌ **Bị cấm**: sử dụng thương mại; bán/bán lại; tái phân phối công khai; xoá dấu bản quyền; cấp phép lại.

Sử dụng thương mại cần giấy phép riêng: [info@photuesoftware.com](mailto:info@photuesoftware.com). Toàn văn: [LICENSE](LICENSE).

## Bảo mật

Không mở issue công khai cho lỗ hổng. Báo cáo riêng tới **info@photuesoftware.com** với tiêu đề `[SECURITY]`. Xem [SECURITY.md](SECURITY.md).

## Hỗ trợ

| Kênh | Địa chỉ |
|---|---|
| GitHub Issues | [hitechcloudvn/ddns_admin_whitelist/issues](https://github.com/hitechcloudvn/ddns_admin_whitelist/issues) |
| GitLab Issues | [gitlab.com/hitechcloudvn/ddns_admin_whitelist/-/issues](https://gitlab.com/hitechcloudvn/ddns_admin_whitelist/-/issues) |
| Hỗ trợ thương mại | [info@photuesoftware.com](mailto:info@photuesoftware.com) |

---

<div align="center">

**HiTechCloud** — thương hiệu hạ tầng đám mây của **CÔNG TY CỔ PHẦN GIẢI PHÁP CÔNG NGHỆ VÀ PHẦN MỀM PHỔ TUỆ**

Mã số doanh nghiệp 0318222903 · Thành phố Hồ Chí Minh, Việt Nam

Copyright © 2026 Pho Tue Technology and Software Solutions JSC. All rights reserved.

</div>
