# Online Election Management & Voting System

A professional PHP-based online voting platform with election management, constituency support, admin panel, and secure voting workflow.

---

## Tech Stack

- PHP 8+ (Core PHP, no framework)
- MySQL 5.7+
- HTML5 / CSS3 / Vanilla JS
- Chart.js
- Font Awesome 6

---

## Local Development Setup

1. Clone the repository
2. Start Apache + MySQL (XAMPP / Laragon / WAMP)
3. Create the database:
   ```
   mysql -u root -p -e "CREATE DATABASE \`voting-system\`;"
   mysql -u root -p voting-system < voting_sysem/database_setup.sql
   mysql -u root -p voting-system < voting_sysem/migration.sql
   ```
4. Update `voting_sysem/includes/config.php` with your local DB credentials if different
5. Visit `http://localhost/voting_sysem/`

---

## Deployment to InfinityFree (via GitHub Actions)

### One-time setup

1. Push this repository to GitHub on the `main` branch
2. Go to your GitHub repository → **Settings → Secrets and variables → Actions**
3. Add these 3 repository secrets:

   | Secret Name             | Value                              |
   |-------------------------|------------------------------------|
   | `INFINITY_FTP_SERVER`   | Your InfinityFree FTP host         |
   | `INFINITY_FTP_USERNAME` | Your InfinityFree FTP username     |
   | `INFINITY_FTP_PASSWORD` | Your InfinityFree FTP password     |

   > Find these in your InfinityFree control panel under **FTP Accounts**

4. In `voting_sysem/includes/config.php`, replace the fallback DB values with your InfinityFree database credentials:
   ```php
   define('DB_HOST', getenv('DB_HOST') ?: 'your-infinityfree-db-host');
   define('DB_NAME', getenv('DB_NAME') ?: 'your-db-name');
   define('DB_USER', getenv('DB_USER') ?: 'your-db-user');
   define('DB_PASS', getenv('DB_PASS') ?: 'your-db-password');
   ```

5. Import the database on InfinityFree:
   - Go to InfinityFree control panel → **MySQL Databases** → create a database
   - Open **phpMyAdmin** on InfinityFree
   - Import `voting_sysem/database_setup.sql` first
   - Then import `voting_sysem/migration.sql`

6. Set the admin user:
   ```sql
   UPDATE voters SET is_admin = 1 WHERE username = 'admin';
   ```

### Triggering deployment

Every push to the `main` branch automatically deploys to `/htdocs/` on InfinityFree.

```
git add .
git commit -m "your message"
git push origin main
```

Monitor the deployment under **Actions** tab in your GitHub repository.

---

## Files excluded from deployment

The following are never uploaded to the server:

- `migration.sql` / `database_setup.sql` — run manually via phpMyAdmin
- `router.php` — local PHP dev server only
- `README.md` — documentation only
- `links/` — unused legacy folder
- All `.git*` files

(Removed in cleanup: `ttt.php`, `electors/`, `online_voting_sysytem/`, `includes/dashbord.php`, `includes/login.php`.)

---

## Default Admin Credentials

| Field    | Value    |
|----------|----------|
| Username | `admin`  |
| Password | `admin123` |

> Change the password immediately after first login.
