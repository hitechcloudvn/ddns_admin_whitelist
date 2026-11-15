# Hướng dẫn sử dụng — DDNS Admin Whitelist

[Tiếng Việt](huong-dan-su-dung.md) | [English](../en/usage.md)

## Tổng quan giao diện / UI overview

Vào **System → Addon Modules → DDNS Admin Whitelist**. Trang quản trị gồm:

- **Bảng Hostname** — danh sách hostname DDNS, IP hiện tại, trạng thái, lần kiểm tra kế tiếp.
- **Bảng Nhật ký** — lịch sử phân giải và cập nhật whitelist.
- **Thanh tác vụ** — thêm hostname, kiểm tra tất cả, dọn nhật ký.

Ngôn ngữ giao diện đổi theo trường **UI Language** trong cấu hình module (Tiếng Việt / English).

## Quản lý hostname / Managing hostnames

### Thêm hostname

1. Nhập **Hostname DDNS** (ví dụ: `nha-office.hopto.org`) — hỗ trợ tên miền thường và tên DDNS.
2. Nhập **Ghi chú** (tuỳ chọn).
3. Nhấn **Add & Check Now** — module phân giải DNS ngay và cập nhật whitelist.

Quy tắc hợp lệ / Validation rules: hostname RFC-compliant, tối đa 253 ký tự, không khoảng trắng, không scheme `http://`.

### Kiểm tra thủ công / Manual checks

- **Check Now** (từng hostname): phân giải lại ngay hostname đó.
- **Check All Now**: kiểm tra toàn bộ hostname đang bật.

### Tạm dừng / Bật lại

Nút **Toggle** bật/tắt hostname mà không xoá nó. Hostname bị tắt: không kiểm tra theo lịch, không tham gia chống trùng IP.

### Xoá hostname

Nút **Delete** xoá hostname **và** gỡ mục whitelist tương ứng có đánh dấu `[ddns_admin_whitelist:<id>]` khỏi `WhitelistedIPs`. Mục nhập tay không bị ảnh hưởng.

## Cơ chế an toàn / How the safety mechanism works

Mỗi mục whitelist do module quản lý được đánh dấu trong `WhitelistedIPs` dạng:

```text
1.2.3.4 [ddns_admin_whitelist:12]
```

| Quy tắc | Hành vi |
|---|---|
| Chỉ sửa mục đánh dấu | Mọi mục nhập tay trong `WhitelistedIPs` được giữ nguyên |
| DNS lỗi → giữ IP cũ | Phân giải thất bại: IP gần nhất được giữ, ghi log cảnh báo, không xoá gì |
| Chống trùng IP | Nếu IP đã thuộc hostname khác đang bật, hostname hiện tại bị bỏ qua |
| Không bao giờ xoá trắng | `WhitelistedIPs` không bao giờ bị ghi rỗng |
| Tự gỡ chặn | Sau khi whitelist thành công, dòng khớp trong `tblbannedips` bị xoá |
| Khoảng kiểm tra 15–30 phút | Giá trị ngoài khoảng bị ép về gần nhất trong khoảng |

## Nhật ký / Activity log

Bảng log ghi: thời gian, hostname, hành động (added/updated/kept/failed/skipped), IP cũ → IP mới, thông điệp chi tiết.

**Prune Logs** xoá các bản ghi cũ hơn số ngày cấu hình (mặc định 30 ngày).

## Thực hành khuyến nghị / Best practices

1. **Luôn giữ đường truy cập thay thế** — IP tĩnh hoặc VPN trong whitelist, đặt ngoài sự quản lý của module.
2. **Theo dõi tab Logs** sau 24 giờ đầu để xác nhận phân giải đúng.
3. **Dùng hostname DDNS ổn định** — tránh hostname có DNS trễ hoặc hay đổi NS.
4. **Giữ interval 20–30 phút** nếu IP đổi ít; 15 phút nếu IP đổi thường xuyên.

## Xử lý sự cố / Troubleshooting

| Triệu chứng | Nguyên nhân | Cách xử lý |
|---|---|---|
| IP không cập nhật | DNS trả về sai / hết TTL | Check Now thủ công; kiểm tra `dig <hostname>` từ server WHMCS |
| Bị khoá ngoài admin | IP mới chưa vào whitelist | Dùng IP thay thế; sửa `WhitelistedIPs` trong `configuration.php` hoặc xoá trực tiếp trong DB |
| Log ghi `resolution failed` | DNS timeout hoặc hostname sai | Kiểm tra hostname; kiểm tra resolver của server |
| Hostname bị bỏ qua (`skipped`) | IP trùng hostname khác | Bật/tắt hostname phù hợp hoặc xoá trùng |
| Cron không chạy kiểm tra | Hook không load / cron sai | Xác nhận `hooks.php` nằm trong thư mục module; kiểm tra cron job WHMCS |
| Bảng không tồn tại | Module activate dở dang | Deactivate rồi Activate lại module |

### Khoá ngoài admin — cách khẩn cấp / Emergency recovery

1. Sửa file `configuration.php` của WHMCS, thêm IP của bạn vào:
   ```php
   $WhitelistedIPs = '1.2.3.4';
   ```
   hoặc xoá giá trị để mở whitelist tạm thời, đăng nhập lại rồi cấu hình đúng.
2. Hoặc xoá mục `[ddns_admin_whitelist:*]` khỏi cột `WhitelistedIPs` trong bảng `tblconfiguration` qua phpMyAdmin.

## Câu hỏi thường gặp / FAQ

**Hỏi: module có xoá IP nhập tay trong WhitelistedIPs không?**
Không. Module chỉ thêm/sửa/xoá các mục có đánh dấu `[ddns_admin_whitelist:<id>]`.

**Hỏi: DNS lỗi có làm mất whitelist không?**
Không. IP gần nhất được giữ cho tới khi phân giải thành công trở lại.

**Hỏi: có thể quản lý nhiều hostname không?**
Có — mỗi hostname có dòng riêng, IP được chống trùng giữa các hostname.

**Hỏi: IP IPv6 được hỗ trợ chưa?**
Chưa — hiện tại module chỉ xử lý IPv4 công khai (A record).

---

[Bảng tài liệu](../../README.md#tài-liệu) · [Hướng dẫn cài đặt](huong-dan-cai-dat.md)
