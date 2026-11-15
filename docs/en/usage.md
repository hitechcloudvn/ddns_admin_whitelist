# Usage Guide — DDNS Admin Whitelist

[Tiếng Việt](../vi/huong-dan-su-dung.md) | [English](usage.md)

## UI overview

Go to **System → Addon Modules → DDNS Admin Whitelist**. The admin page contains:

- **Hosts table** — your DDNS hostnames, current IP, status, next scheduled check.
- **Logs table** — history of DNS resolutions and whitelist updates.
- **Action bar** — add hostname, check all, prune logs.

The UI language follows the **UI Language** field in the module configuration (Vietnamese / English).

## Managing hostnames

### Add a hostname

1. Enter the **DDNS hostname** (e.g. `office-home.hopto.org`) — regular domains and DDNS names are both supported.
2. Enter an optional **Note**.
3. Click **Add & Check Now** — the module resolves DNS immediately and updates the whitelist.

Validation rules: RFC-compliant hostname, max 253 characters, no whitespace, no `http://` scheme.

### Manual checks

- **Check Now** (per hostname): re-resolves that hostname immediately.
- **Check All Now**: checks every enabled hostname.

### Pause / resume

The **Toggle** button enables or disables a hostname without deleting it. A disabled hostname is skipped by scheduled checks and excluded from IP de-duplication.

### Delete a hostname

The **Delete** button removes the hostname **and** its tagged whitelist entry `[ddns_admin_whitelist:<id>]` from `WhitelistedIPs`. Manually added entries are unaffected.

## How the safety mechanism works

Every module-managed whitelist entry is tagged inside `WhitelistedIPs` like:

```text
1.2.3.4 [ddns_admin_whitelist:12]
```

| Rule | Behavior |
|---|---|
| Only tagged entries are modified | All manually added `WhitelistedIPs` entries are preserved |
| DNS failure → keep last IP | On resolution failure the last known IP is kept, a warning is logged, nothing is removed |
| IP de-duplication | If an IP already belongs to another enabled hostname, the current hostname is skipped |
| Never wipe the whitelist | `WhitelistedIPs` is never written empty |
| Automatic unban | After a successful whitelist update, matching `tblbannedips` rows are removed |
| Check interval 15–30 minutes | Values outside the range are clamped to the nearest bound |

## Activity log

Each log row records: timestamp, hostname, action (added/updated/kept/failed/skipped), old IP → new IP, and a detailed message.

**Prune Logs** deletes records older than the configured retention period (default: 30 days).

## Best practices

1. **Always keep an alternative access path** — a static IP or VPN entry in the whitelist, outside the module's management.
2. **Watch the Logs tab** during the first 24 hours to confirm correct resolution.
3. **Use stable DDNS hostnames** — avoid hostnames with slow DNS or frequent NS changes.
4. **Keep the interval at 20–30 minutes** if your IP rarely changes; 15 minutes if it changes often.

## Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| IP not updating | DNS returns stale/wrong data | Use **Check Now**; run `dig <hostname>` from the WHMCS server |
| Locked out of admin | New IP not yet whitelisted | Use the alternative IP; fix `WhitelistedIPs` in `configuration.php` or remove rows in the DB |
| Log shows `resolution failed` | DNS timeout or wrong hostname | Verify the hostname; check the server's resolver |
| Hostname skipped | IP already owned by another hostname | Toggle or delete the conflicting hostname |
| Cron not running checks | Hook not loaded / cron misconfigured | Confirm `hooks.php` sits in the module folder; verify the WHMCS cron job |
| Tables missing | Activation did not finish | Deactivate and re-activate the module |

### Emergency admin recovery

1. Edit the WHMCS `configuration.php` and add your IP:
   ```php
   $WhitelistedIPs = '1.2.3.4';
   ```
   or clear the value to open the whitelist temporarily, log in, then fix the configuration.
2. Or remove the `[ddns_admin_whitelist:*]` entries from the `WhitelistedIPs` column in the `tblconfiguration` table via phpMyAdmin.

## FAQ

**Q: Does the module remove manually added WhitelistedIPs entries?**
No. It only adds/edits/removes entries tagged `[ddns_admin_whitelist:<id>]`.

**Q: Can a DNS failure wipe the whitelist?**
No. The last known IP is kept until resolution succeeds again.

**Q: Can I manage multiple hostnames?**
Yes — each hostname has its own row, and IPs are de-duplicated across hostnames.

**Q: Is IPv6 supported?**
Not yet — the module currently handles public IPv4 only (A records).

---

[Documentation index](../../README.en.md#documentation) · [Installation guide](installation.md)
