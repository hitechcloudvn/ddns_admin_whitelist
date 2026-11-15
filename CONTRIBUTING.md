# Hướng dẫn đóng góp / Contributing Guide

Cảm ơn bạn quan tâm đến dự án **DDNS Admin Whitelist**!

Thank you for your interest in the **DDNS Admin Whitelist** project!

## Trước khi bắt đầu / Before you start

- Đọc [Giấy phép](LICENSE): dự án dùng giấy phép source-available **phi thương mại** — đóng góp đồng nghĩa với việc chấp nhận các điều khoản này.
  / Read the [License](LICENSE): this project uses a **non-commercial** source-available license — by contributing you accept its terms.
- Sự cố bảo mật: xem [SECURITY.md](SECURITY.md), **không** mở issue công khai.
  / Security issues: see [SECURITY.md](SECURITY.md) — do **not** open a public issue.

## Quy trình đóng góp / Contribution workflow

1. Fork kho mã nguồn và tạo nhánh từ `main`: `git checkout -b feat/ten-tinh-nang`.
   / Fork the repository and branch off `main`: `git checkout -b feat/feature-name`.
2. Giữ mã nguồn **song ngữ Việt–Anh**: chú thích hàm ghi cả tiếng Anh và tiếng Việt; chuỗi giao diện dùng khoá trong `lang/english.php` + `lang/vietnamese.php`.
   / Keep source code **bilingual Vietnamese–English**: function comments in both English and Vietnamese; UI strings as keys in `lang/english.php` + `lang/vietnamese.php`.
3. Kiểm tra cú pháp PHP trước khi đẩy (CI cũng chạy lại):
   ```bash
   php -l ddns_admin_whitelist.php
   php -l hooks.php
   ```
   / Verify PHP syntax before pushing (CI runs it again).
4. Cập nhật [CHANGELOG.md](CHANGELOG.md) theo [Keep a Changelog](https://keepachangelog.com/), mục `## [Unreleased]`.
   / Update [CHANGELOG.md](CHANGELOG.md) following [Keep a Changelog](https://keepachangelog.com/), under `## [Unreleased]`.
5. Mở Pull Request mô tả rõ: vấn đề, cách giải quyết, cách kiểm chứng.
   / Open a Pull Request describing: the problem, the solution, and how to verify it.

## Quy ước mã nguồn / Coding conventions

- PHP 7.4+ tương thích ngược; indent 4 khoảng trắng, LF, UTF-8 (xem `.editorconfig`).
  / PHP 7.4+ backward compatible; 4-space indent, LF, UTF-8 (see `.editorconfig`).
- Toàn bộ HTML đầu ra phải qua escape; mọi form admin phải có CSRF token.
  / All HTML output must be escaped; every admin form must carry a CSRF token.
- Không thay đổi hành vi an toàn của module (chỉ sửa mục đánh dấu, giữ IP khi DNS lỗi, không xoá whitelist).
  / Do not change the module's safety behavior (only tagged entries modified, last IP kept on DNS failure, whitelist never wiped).
- Tên nhánh: `feat/*`, `fix/*`, `docs/*`, `chore/*`.
  / Branch naming: `feat/*`, `fix/*`, `docs/*`, `chore/*`.

## Commit / Commits

- Thông điệp song ngữ: dòng đầu tiếng Anh (tiêu chuẩn), mô tả tiếng Việt trong thân.
  / Bilingual commit messages: first line in English (conventional), Vietnamese description in the body.
- Kết thúc thông điệp bằng trailer ghi nhận công trạng được chỉ định bởi chủ dự án.
  / End commit messages with the attribution trailers specified by the project owner.

## Câu hỏi / Questions

- GitHub Issues: [hitechcloudvn/ddns_admin_whitelist/issues](https://github.com/hitechcloudvn/ddns_admin_whitelist/issues)
- Hỗ trợ thương mại: [info@photuesoftware.com](mailto:info@photuesoftware.com)
  / Commercial support: [info@photuesoftware.com](mailto:info@photuesoftware.com)
