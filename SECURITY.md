# Chính sách bảo mật / Security Policy

## Các phiên bản được hỗ trợ / Supported versions

| Phiên bản / Version | Được hỗ trợ / Supported |
|---|---|
| 1.x | ✅ Có / Yes |
| < 1.0 | ❌ Không / No |

Chỉ phiên bản mới nhất trong nhánh `1.x` được vá bảo mật.

Only the latest release in the `1.x` branch receives security patches.

## Báo cáo lỗ hổng / Reporting a vulnerability

**KHÔNG** mở issue công khai cho lỗ hổng bảo mật.

**DO NOT** open a public issue for security vulnerabilities.

Hãy gửi email riêng tới:

Please send a private email to:

- **info@photuesoftware.com** — tiêu đề / subject: `[SECURITY] ddns_admin_whitelist`

Nội dung nên bao gồm / Your report should include:

1. Mô tả lỗ hổng và ảnh hưởng tiềm năng / Description of the vulnerability and its potential impact.
2. Các bước tái hiện (PoC, phiên bản WHMCS/PHP) / Steps to reproduce (PoC, WHMCS/PHP versions).
3. Đề xuất khắc phục (nếu có) / Proposed fix (if any).

### Cam kết của chúng tôi / Our commitment

- Phản hồi xác nhận trong **3 ngày làm việc** / Acknowledgement within **3 business days**.
- Đánh giá và khắc phục trong **14 ngày** với lỗ hổng nghiêm trọng / Assessment and fix within **14 days** for critical vulnerabilities.
- Ghi nhận người báo cáo trong advisory phát hành (tuỳ nguyện) / Credit in the release advisory (optional).

## Ghi chú bảo mật đặc thù / Module-specific security notes

- Module chỉ ghi vào `WhitelistedIPs` các mục được đánh dấu `[ddns_admin_whitelist:<id>]` — luôn giữ một đường truy cập admin thay thế (IP tĩnh, VPN) trước khi kích hoạt.
  / The module only writes entries tagged `[ddns_admin_whitelist:<id>]` to `WhitelistedIPs` — always keep an alternative admin access path (static IP, VPN) before enabling it.
- Nếu phân giải DNS thất bại, IP gần nhất được giữ nguyên — whitelist không bao giờ bị xoá trắng do lỗi DNS.
  / On DNS resolution failure the last known IP is kept — the whitelist is never wiped due to DNS errors.
- Không commit chuỗi bí mật, khoá API hoặc thông tin đăng nhập vào repo. Sự cố nghi ngờ lộ khoá: xoá khoá và liên hệ ngay địa chỉ trên.
  / Never commit secrets, API keys, or credentials. If you suspect a key leak: revoke it immediately and contact the address above.
