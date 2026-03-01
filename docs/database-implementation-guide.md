# MilesWeb Shared Hosting: Minimal PHP + MySQL Backend Plan

## 1) Consultant-style explanation

This project is mostly static, so the database should be used only for data that must persist and be reviewed later (for example, contact form enquiries).

A practical production-safe approach on MilesWeb shared hosting is:

- Keep frontend static (`index.html`, `css`, `js`) for fast loading and simple maintenance.
- Add a very small PHP backend only for form submission.
- Store only required fields in MySQL.
- Use prepared statements for every SQL query.
- Keep DB credentials in server-side PHP files only (never in JS).
- Use phpMyAdmin for setup/ops tasks (create table, backup, restore), not for day-to-day form submission logic.

This gives long-term stability with low hosting complexity and no unnecessary overengineering.

## 2) Database usage decision table

| Scenario | Use Database? | Reason |
|---|---|---|
| Hero, About, Services static text | No | Content is static and can stay in HTML/JS translation objects. |
| Contact form submissions | Yes | User enquiries require persistence, follow-up, and audit trail. |
| WhatsApp/phone CTA buttons | No | Direct links do not require persistence in DB. |
| Temporary UI state (menu open/lang toggle) | No | Browser state only; keep in JS/localStorage. |
| Admin reporting on leads | Yes (read-only queries) | Requires stored historical records. |

## 3) Minimal schema

Only one table is required now:

- `contact_submissions`: stores enquiries from the contact form.

Schema file: `sql/contact_submissions.sql`.

## 4) PHP backend examples

- `backend/config.php`: central config constants (DB, app environment, limits).
- `backend/db.php`: PDO connection factory + JSON helper + CORS helper.
- `backend/contact-submit.php`: validates input, rate-limits by phone, inserts via prepared statement.

## 5) Frontend integration notes

- Submit form as JSON with `fetch` to `backend/contact-submit.php`.
- Keep frontend validation for UX.
- Always re-validate server-side before insert (do not trust browser input).
- Treat backend response as source of truth for success/failure.

## 6) Backup, import, export, and security operations

### Backup (phpMyAdmin)
1. Open phpMyAdmin → select database.
2. Click **Export**.
3. Use **Custom** method for routine backups.
4. Include table structure + data.
5. Download `.sql` backup and store off-host (cloud + local encrypted copy).

### Restore / Import
1. Open target database in phpMyAdmin.
2. Click **Import**.
3. Upload `.sql` backup.
4. Verify table counts and app form submission endpoint after import.

### Least privilege
- Use one DB user for app runtime with only: `SELECT`, `INSERT` on required table.
- Avoid broad permissions (`DROP`, `ALTER`, `GRANT`) for runtime user.
- Keep privileged operations only in phpMyAdmin/admin user.

### Error handling and production safety
- Show generic errors to users.
- Log real errors with `error_log` in PHP.
- Keep `APP_ENV='production'` on live server.
- Use HTTPS-only website and backend endpoint.

### Data retention recommendation
- Keep contact leads for a fixed business window (for example 12-18 months), then archive/delete old rows.
