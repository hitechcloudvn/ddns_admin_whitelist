# Installation Guide — DDNS Admin Whitelist

[Tiếng Việt](../vi/huong-dan-cai-dat.md) | [English](installation.md)

## Requirements

| Component | Version |
|---|---|
| WHMCS | 8.0+ |
| PHP | 7.4 – 8.3 |
| MySQL / MariaDB | As required by WHMCS |
| WHMCS admin rights | Permission to install addon modules |

> ⚠️ **Important:** before activating, make sure you have an **alternative admin access path** (static IP, VPN, or temporarily disabled whitelist in `configuration.php`) so you cannot get locked out while testing.

## Installation

### 1. Get the source

Option 1 — release archive (recommended):

1. Go to [Releases](https://github.com/hitechcloudvn/ddns_admin_whitelist/releases).
2. Download the latest `ddns_admin_whitelist-vX.Y.Z.zip`.
3. Extract — the inner `ddns_admin_whitelist/` folder contains the full module.

Option 2 — clone from the repository:

```bash
cd /path/to/whmcs/modules/addons
git clone https://github.com/hitechcloudvn/ddns_admin_whitelist.git
```

### 2. Copy into WHMCS

Place the module folder at:

```text
<whmcs-root>/modules/addons/ddns_admin_whitelist/
```

Expected layout:

```text
modules/addons/ddns_admin_whitelist/
├── ddns_admin_whitelist.php
├── hooks.php
├── lang/
│   ├── english.php
│   └── vietnamese.php
└── ...
```

### 3. Activate the module

1. Log in to WHMCS Admin.
2. Go to **System → Addon Modules**.
3. Find **DDNS Admin Whitelist** and click **Activate**.
4. Grant access to your admin role(s) under Access Control, then **Save Changes**.

The module creates two tables on activation:

- `mod_ddns_admin_whitelist_hosts`
- `mod_ddns_admin_whitelist_logs`

### 4. Configure

On the module configuration page:

| Field | Description | Default |
|---|---|---|
| UI Language | Vietnamese or English | english |
| Check interval (minutes) | Enforced within 15–30 minutes | 20 |
| Log retention (days) | Days to keep activity logs | 30 |

Click **Save Changes**, then add your first hostname (see the [Usage guide](usage.md)).

## Cron verification

Scheduled checks run through the `AfterCronJob` hook. Make sure the WHMCS cron is configured correctly:

```bash
# Verify the system cron job
crontab -l | grep whmcs
```

The WHMCS cron must run at least every 5 minutes for the hook to work correctly.

## Upgrade

1. Back up the `mod_ddns_admin_whitelist_*` tables (optional but recommended).
2. Note the current version (System → Addon Modules).
3. Replace all files in the module folder with the new release — keep a customized `lang/` folder if you modified translations.
4. Open **System → Addon Modules → DDNS Admin Whitelist** — the module upgrades its schema automatically when needed.
5. Check the **Logs** tab to confirm normal operation.

## Uninstall

1. In **System → Addon Modules**, click **Deactivate**.
   - Deactivation **drops both module tables** and removes whitelist entries tagged `[ddns_admin_whitelist:<id>]` — this cannot be undone.
2. Delete the `modules/addons/ddns_admin_whitelist/` folder.

> Tip: before deactivating, snapshot the hostname list from the **Hosts** tab for quick re-installation later.

## Common install issues

| Symptom | Cause / Fix |
|---|---|
| Module missing from Addon Modules | Wrong folder location — must be `modules/addons/ddns_admin_whitelist/` |
| Activation error | Database account lacks CREATE TABLE permission |
| Hostnames not checked automatically | WHMCS cron not running, or interval set outside 15–30 minutes |
| Locked out of admin | Use the alternative access path; fix `WhitelistedIPs` in `configuration.php` or via the database |

Troubleshooting details: [Usage guide — Troubleshooting](usage.md#troubleshooting).

---

[Documentation index](../../README.en.md#documentation) · [Usage guide](usage.md)
