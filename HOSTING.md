# HRM Hosting Guide — InfinityFree

## Prerequisites
- InfinityFree account at https://infinityfree.net
- FTP client (FileZilla) or the PHP upload script included in repo

---

## Step 1: Create Hosting Account
1. Sign up at https://infinityfree.net
2. Click **Create Account**
3. Choose a subdomain (e.g. `hrm-2026.xo.je`)
4. Note your FTP credentials:
   - **FTP Host:** `ftpupload.net`
   - **FTP Username:** `if0_XXXXXXXX`
   - **FTP Password:** (your chosen password)
   - **FTP Port:** `21`

---

## Step 2: Create MySQL Database
1. Go to Control Panel → **MySQL Databases**
2. Create a new database
3. Note your MySQL credentials:
   - **Host:** `sql###.infinityfree.com`
   - **Database:** `if0_XXXXXXXX_yourdbname`
   - **Username:** `if0_XXXXXXXX`
   - **Password:** (your chosen password)
   - **Port:** `3306`

---

## Step 3: Import Database
1. Go to Control Panel → **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Upload `hrm_dump.sql` from the repo
5. Click **Go**

---

## Step 4: Upload Files
### Option A: PHP Upload Script (Recommended)
1. Upload `upload.php` from the repo root to `htdocs/` via FTP
2. Visit `http://your-domain/upload.php` in browser
3. Upload `hrm-upload.zip` (create with the command below)
4. Click **Upload & Extract**

**Create the zip:**
```bash
cd /path/to/hrm
zip -r hrm-upload.zip hrm/ \
  -x "hrm/.git/*" \
  -x "hrm/uploads/*" \
  -x "hrm/skin/vendor/*" \
  -x "hrm/skin/jobs/*" \
  -x "hrm/skin/img/flags/*" \
  -x "hrm/tests/*" \
  -x "hrm/migrations/*" \
  -x "hrm/.freebuff/*" \
  -x "hrm/downn/*" \
  -x "hrm/*.sql" \
  -x "hrm/*.pem" \
  -x "hrm/error_log" \
  -x "hrm/WhatsApp*" \
  -x "hrm/Dockerfile" \
  -x "hrm/render.yaml"
```

### Option B: FTP Upload
Use FileZilla to upload all files to `htdocs/`.

---

## Step 5: Configure Database
After upload, edit `application/config/database.php`:

```php
$db_host = 'sql###.infinityfree.com';
$db_port = '3306';
$db_user = 'if0_XXXXXXXX';
$db_pass = 'YOUR_MYSQL_PASSWORD';
$db_name = 'if0_XXXXXXXX_yourdbname';
```

The config auto-detects InfinityFree vs local environment:
- **InfinityFree:** credentials are hardcoded (no env vars needed)
- **Local dev:** uses `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` env vars

---

## Step 6: Fix Permissions
In File Manager, ensure these folders are writable:
- `application/cache/`
- `uploads/`

---

## Step 7: DNS (If Domain Not Resolving)
New InfinityFree domains can take up to 72 hours to propagate.

**Quick fix — add IP to hosts file:**
```bash
sudo echo "185.27.134.160 hrm-2026.xo.je" >> /etc/hosts
```

Find your IP in Control Panel → Domain Details.

---

## Step 8: Post-Deploy Cleanup
Delete these files from `htdocs/` after deployment:
- `upload.php`
- `extract.php`
- `extract2.php`
- `check.php`
- `hrm-upload.zip`

---

## Default Logins
| Username | Password | Role |
|----------|----------|------|
| `softadmin` | `Stalis@2026` | Super Admin |
| `lilian.maina` | `Stalis@2026` | HR Manager |
| `henry.njoroge` | `Stalis@2026` | CEO |

---

## SMTP Configuration
For email notifications, update the SMTP settings in phpMyAdmin → `xin_smtp_settings` table.

Current SMTP: `mail.techriseglow.co.ke:465` (SSL)
- PHPMailer is used (bypasses CI3 mail issues)
- SSL cert mismatch is handled via `SMTPOptions` disabling verification

---

## Troubleshooting

### "System folder path does not appear to be set correctly"
Files are in a subfolder. Move all files from `htdocs/hrm/` to `htdocs/` root.

### "Database Error: Connection refused"
Check that `application/config/database.php` has the correct host.

### "Access denied for user"
Your MySQL username is `if0_XXXXXXXX` (not `if0_XXXXXXXX_dbname`).
Reset password in Control Panel → MySQL Databases → Change Password.

### "DNS_PROBE_FINISHED_NXDOMAIN"
Domain hasn't propagated yet. Use IP directly or add to hosts file.

### PHP Upload Limit
InfinityFree limits PHP uploads to ~10MB. Use the PHP upload script approach.

### File Manager Has No Extract
Use the included `upload.php` script via browser to upload and extract the zip.
