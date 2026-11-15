# Lịch sử thay đổi / Changelog

Mọi thay đổi đáng chú ý của dự án này được ghi lại trong file này.

All notable changes to this project will be documented in this file.

Định dạng dựa trên [Keep a Changelog](https://keepachangelog.com/vi/1.1.0/),
và dự án tuân thủ [Semantic Versioning](https://semver.org/lang/vi/).

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

_Chưa có thay đổi nào / No changes yet._

## [1.0.0] — 2026-11-16

Bản phát hành đầu tiên — addon module DDNS Admin Whitelist cho WHMCS.

First public release — the DDNS Admin Whitelist addon module for WHMCS.

### Thêm mới / Added

- Module addon WHMCS `ddns_admin_whitelist`: tự động phân giải hostname DDNS và cập nhật an toàn `WhitelistedIPs`.
  / WHMCS addon module `ddns_admin_whitelist`: automatically resolves DDNS hostnames and safely updates `WhitelistedIPs`.
- Bảng `mod_ddns_admin_whitelist_hosts` (quản lý hostname) và `mod_ddns_admin_whitelist_logs` (nhật ký).
  / Tables `mod_ddns_admin_whitelist_hosts` (host management) and `mod_ddns_admin_whitelist_logs` (activity log).
- Hook `AfterCronJob` chạy kiểm tra theo lịch, khoảng ép 15–30 phút.
  / `AfterCronJob` hook for scheduled checks, interval enforced within 15–30 minutes.
- Cơ chế an toàn: chỉ sửa mục có đánh dấu `[ddns_admin_whitelist:<id>]`, giữ IP gần nhất khi DNS lỗi, chống trùng IP, giữ nguyên mục nhập tay.
  / Safety rules: only entries tagged `[ddns_admin_whitelist:<id>]` are modified; last known IP kept on DNS failure; IP de-duplication; manual entries preserved.
- Tự gỡ IP trong `tblbannedips` sau khi whitelist thành công.
  / Automatic removal of matching `tblbannedips` rows after a successful whitelist update.
- Giao diện admin song ngữ Việt/Anh (`lang/vietnamese.php`, `lang/english.php`) với trường cấu hình chọn ngôn ngữ.
  / Bilingual Vietnamese/English admin UI (`lang/vietnamese.php`, `lang/english.php`) with a language selection config field.
- Nhật ký hoạt động chi tiết + dọn log theo số ngày.
  / Detailed activity log with age-based pruning.
- Tài liệu song ngữ: README.md (VI), README.en.md (EN), docs/vi/, docs/en/.
  / Bilingual documentation: README.md (VI), README.en.md (EN), docs/vi/, docs/en/.
- CI/CD: GitHub Actions (`ci.yml` php-lint + secret-scan, `release.yml` build & publish release) và gương GitLab CI.
  / CI/CD: GitHub Actions (`ci.yml` php-lint + secret-scan, `release.yml` build & publish release) and a GitLab CI mirror.

### Đã sửa / Fixed

- Sửa `colspan` sai trong bảng danh sách hostname khi bảng rỗng (9 → 10 cột).
  / Fixed incorrect `colspan` in the empty-state row of the hosts table (9 → 10 columns).

### Bảo mật / Security

- Toàn bộ đầu ra HTML được escape; biểu mẫu dùng CSRF token (`check_token('WHMCS.admin.default')`).
  / All HTML output escaped; forms use CSRF tokens (`check_token('WHMCS.admin.default')`).

[Unreleased]: https://github.com/hitechcloudvn/ddns_admin_whitelist/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/hitechcloudvn/ddns_admin_whitelist/releases/tag/v1.0.0
