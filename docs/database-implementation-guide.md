# Contact Form Backend Integration (PHP + MySQL)

## 1) Backend flow (short)

1. The existing frontend form (`#contactForm`) keeps its current UI, IDs, classes, and client-side validation unchanged.
2. On valid submit, frontend sends POST data (`name`, `phone`, `message`) to `backend/contact-submit.php`.
3. `backend/contact-submit.php` forwards the request to `backend/submit-consultation.php`.
4. `backend/submit-consultation.php` performs server-side validation and inserts data using a prepared statement into MySQL.
5. API returns JSON success/error response for the existing frontend status message handling.

## 2) SQL table creation script

```sql
CREATE TABLE IF NOT EXISTS consultations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    mobile_number VARCHAR(20) NOT NULL,
    requirement TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mobile_number (mobile_number),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

(also available in `sql/contact_submissions.sql`)

## 3) PHP database connection (`config/db.php`)

```php
<?php

declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_PORT = 3306;
const DB_NAME = 'your_database_name';
const DB_USER = 'your_database_user';
const DB_PASS = 'your_database_password';

function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
```

## 4) PHP form handler (`backend/submit-consultation.php`)

- Accepts POST (JSON or form-encoded)
- Validates full name, mobile number, requirement
- Uses prepared statements for SQL injection protection
- Returns safe JSON messages
- Logs server-side errors without exposing credentials

## 5) Integration notes (what goes where)

- `config/db.php` → shared DB credentials + PDO connection
- `sql/contact_submissions.sql` → import via phpMyAdmin to create table
- `backend/submit-consultation.php` → main backend endpoint
- `backend/contact-submit.php` → compatibility route used by existing JS fetch

### Existing form submission path (no UI redesign)

Your existing frontend already submits to:

```js
fetch("backend/contact-submit.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    "X-Requested-With": "XMLHttpRequest",
  },
  body: JSON.stringify({
    name: name.value.trim(),
    phone: phone.value.trim(),
    message: message.value.trim(),
  }),
});
```

No label/text/layout/class/id changes are required.
