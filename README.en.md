<div align="center">

# DDNS Admin Whitelist

**WHMCS addon that resolves DDNS hostnames and safely updates the admin login IP whitelist**

[**Tiếng Việt**](README.md) | **English**

[![License](https://img.shields.io/badge/License-Source--Available%20Non--Commercial-red?style=flat-square)](LICENSE)
[![WHMCS](https://img.shields.io/badge/WHMCS-8.0%2B-0073AA?style=flat-square)](docs/en/installation.md)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![CI](https://img.shields.io/github/actions/workflow/status/hitechcloudvn/ddns_admin_whitelist/ci.yml?branch=main&style=flat-square&label=CI)](https://github.com/hitechcloudvn/ddns_admin_whitelist/actions/workflows/ci.yml)
[![Made in Vietnam](https://img.shields.io/badge/Made%20in-Vietnam-DA251D?style=flat-square)](https://hitechcloud.vn)

[Introduction](#introduction) · [Features](#features) · [Quick install](#quick-install) · [Documentation](#documentation) · [License](#license) · [Support](#support)

</div>

---

## Introduction

**DDNS Admin Whitelist** is an addon module for WHMCS: every 15–30 minutes it resolves your DDNS hostnames and **safely** updates the admin login IP whitelist (`WhitelistedIPs`) with the latest public IPv4 addresses.

The module only manages entries tagged `[ddns_admin_whitelist:<id>]`, **preserves** every manually added entry, never clears `WhitelistedIPs`, and keeps the last known IP when DNS resolution fails — so you never get locked out of admin.

- Source repository: [github.com/hitechcloudvn/ddns_admin_whitelist](https://github.com/hitechcloudvn/ddns_admin_whitelist)
- GitLab mirror: [gitlab.com/hitechcloudvn/ddns_admin_whitelist](https://gitlab.com/hitechcloudvn/ddns_admin_whitelist)

## Features

| | |
|---|---|
| ✅ Automatic DNS A resolution | Resolves public IPv4 via `dns_get_record` with `gethostbynamel` fallback |
| ✅ Controlled whitelist updates | Only adds/edits module-tagged entries; manual entries untouched |
| ✅ Safe on DNS failure | Keeps the last known IP, logs the event, never wipes the whitelist |
| ✅ IP de-duplication | Skips a hostname if its IP already belongs to another enabled hostname |
| ✅ Automatic unban | Removes matching rows from `tblbannedips` after a successful whitelist |
| ✅ Scheduled checks | `AfterCronJob` hook; check interval enforced within 15–30 minutes |
| ✅ Bilingual admin UI | Vietnamese / English, selectable in the module configuration |
| ✅ Activity log | Detailed log table + prune logs by age |
| ✅ Form security | CSRF tokens via `check_token`, all HTML output escaped |

## Platform support

| Platform | Version | Notes |
|---|---|---|
| WHMCS | 8.0+ | Addon module + `AfterCronJob` hook |
| PHP | 7.4 – 8.3 | Syntax verified via CI matrix |
| MySQL / MariaDB | As per WHMCS | `mod_ddns_admin_whitelist_*` tables created on activation |

## Repository layout

```text
ddns_admin_whitelist/
├── ddns_admin_whitelist.php   # Main addon module
├── hooks.php                  # AfterCronJob hook
├── lang/                      # UI language files (EN + VI)
├── docs/
│   ├── vi/                    # Vietnamese documentation
│   └── en/                    # English documentation
├── .github/workflows/         # CI + Release on GitHub Actions
└── .gitlab-ci.yml             # GitLab CI/CD mirror
```

## Quick install

1. Download the latest release from [Releases](https://github.com/hitechcloudvn/ddns_admin_whitelist/releases).
2. Extract and copy the folder into `modules/addons/ddns_admin_whitelist/`.
3. Go to **WHMCS Admin → System → Addon Modules** and activate **DDNS Admin Whitelist**.
4. Add your DDNS hostnames, set the check interval (15–30 minutes), and click **Add & Check Now**.
5. Make sure the WHMCS cron runs normally — the `AfterCronJob` hook will perform scheduled checks automatically.

Details: [docs/en/installation.md](docs/en/installation.md) · [docs/en/usage.md](docs/en/usage.md)

## Documentation

| Document | Description |
|---|---|
| [Installation guide](docs/en/installation.md) | Install, upgrade, uninstall |
| [Usage guide](docs/en/usage.md) | Hostname management, safety rules, troubleshooting |
| [Changelog](CHANGELOG.md) | Change history (Keep a Changelog) |
| [Contributing](CONTRIBUTING.md) | Contribution workflow |
| [Security](SECURITY.md) | Vulnerability reporting policy |

## License

This is a **source-available** license, **not** an open-source license:

- ✅ **Allowed**: internal install and operation; copying; internal modification; research and education.
- ❌ **Prohibited**: commercial use; selling/reselling; public redistribution; removing copyright notices; relicensing.

Commercial use requires a separate license: [info@photuesoftware.com](mailto:info@photuesoftware.com). Full text: [LICENSE](LICENSE).

## Security

Do not open public issues for vulnerabilities. Report privately to **info@photuesoftware.com** with the subject `[SECURITY]`. See [SECURITY.md](SECURITY.md).

## Support

| Channel | Address |
|---|---|
| GitHub Issues | [hitechcloudvn/ddns_admin_whitelist/issues](https://github.com/hitechcloudvn/ddns_admin_whitelist/issues) |
| GitLab Issues | [gitlab.com/hitechcloudvn/ddns_admin_whitelist/-/issues](https://gitlab.com/hitechcloudvn/ddns_admin_whitelist/-/issues) |
| Commercial support | [info@photuesoftware.com](mailto:info@photuesoftware.com) |

---

<div align="center">

**HiTechCloud** — cloud infrastructure brand of **Pho Tue Technology and Software Solutions JSC**
(CÔNG TY CỔ PHẦN GIẢI PHÁP CÔNG NGHỆ VÀ PHẦN MỀM PHỔ TUỆ)

Business Registration No. 0318222903 · Ho Chi Minh City, Vietnam

Copyright © 2026 Pho Tue Technology and Software Solutions JSC. All rights reserved.

</div>
