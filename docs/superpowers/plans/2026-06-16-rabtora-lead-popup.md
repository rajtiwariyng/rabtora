# Rabtora Lead Capture Popup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a popup lead-capture form to the Rabtora landing page, store submissions in a `rabtora` MySQL database, and update the admin panel to display the new fields.

**Architecture:** All CTA buttons on `landing-page/index.html` intercept click and open a modal. The form POSTs via `fetch()` to `../submit_lead.php`. The backend validates and inserts into the `rabtora` DB `leads` table. The admin dashboard reads from the same table.

**Tech Stack:** PHP 8+, MySQL/MariaDB via PDO, vanilla JavaScript (no framework), HTML/CSS

---

## File Map

| File | Change |
|------|--------|
| `config/db.php` | DB name → `rabtora` |
| `admin/setup.php` | New DB name + updated `leads` schema (add company_name, budget, services; drop interest, community) |
| `submit_lead.php` | Handle `company_name`, `budget`, `services[]`; updated INSERT |
| `landing-page/index.html` | Add modal HTML + `<style>` block + `<script>` block |
| `admin/dashboard.php` | Rebrand, 3-card stats, new table columns, updated filter + CSV |

---

### Task 1: Update DB config

**Files:**
- Modify: `config/db.php`

- [ ] **Step 1: Change database name**

Replace the entire content of `config/db.php`:

```php
<?php
function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=rabtora;charset=utf8mb4',
            'root',
            '',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}
```

- [ ] **Step 2: Commit**

```bash
git add config/db.php
git commit -m "config: point DB connection to rabtora database"
```

---

### Task 2: Update setup.php

**Files:**
- Modify: `admin/setup.php`

- [ ] **Step 1: Rewrite setup.php for rabtora DB with new schema**

Replace entire content of `admin/setup.php`:

```php
<?php
/**
 * ONE-TIME SETUP — run once in browser, then delete this file.
 * http://localhost/rabtora.ae/admin/setup.php
 */

$host   = 'localhost';
$dbname = 'rabtora';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbname}`");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS leads (
            id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
            form_source  VARCHAR(20)   NOT NULL DEFAULT '',
            full_name    VARCHAR(255)  NOT NULL,
            phone        VARCHAR(50)   NOT NULL,
            email        VARCHAR(255)  DEFAULT NULL,
            company_name VARCHAR(255)  DEFAULT NULL,
            budget       VARCHAR(100)  DEFAULT NULL,
            services     VARCHAR(500)  DEFAULT NULL,
            ip_address   VARCHAR(45)   DEFAULT NULL,
            created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_created (created_at),
            INDEX idx_source  (form_source)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username      VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $hash = password_hash('Admin@1234', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT IGNORE INTO admin_users (username, password_hash) VALUES ('admin', ?)")
        ->execute([$hash]);

    echo '<pre style="font-family:monospace;font-size:15px;padding:24px">';
    echo "✅  Database  '{$dbname}' ready.\n";
    echo "✅  Table     'leads' created (or already exists).\n";
    echo "✅  Table     'admin_users' created (or already exists).\n";
    echo "✅  Default admin: admin / Admin\@1234\n\n";
    echo "⚠️   DELETE this file after setup is complete!\n\n";
    echo "→   Admin panel : /rabtora.ae/admin/login.php\n";
    echo '</pre>';

} catch (PDOException $e) {
    echo '<pre style="color:red">';
    echo 'Setup failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    echo '</pre>';
}
```

- [ ] **Step 2: Run setup in browser**

Navigate to: `http://localhost/rabtora.ae/admin/setup.php`

Expected output:
```
✅  Database  'rabtora' ready.
✅  Table     'leads' created (or already exists).
✅  Table     'admin_users' created (or already exists).
✅  Default admin: admin / Admin@1234
```

If it shows an error, check that XAMPP MySQL is running.

- [ ] **Step 3: Commit**

```bash
git add admin/setup.php
git commit -m "feat: update setup.php for rabtora DB with new leads schema"
```

---

### Task 3: Update submit_lead.php

**Files:**
- Modify: `submit_lead.php`

- [ ] **Step 1: Rewrite submit_lead.php**

Replace entire content of `submit_lead.php`:

```php
<?php
header('Content-Type: application/json');
require __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$allowed_budgets = [
    'Under AED 10,000',
    'AED 10,000 – 50,000',
    'AED 50,000 – 100,000',
    'AED 100,000 – 500,000',
    'Above AED 500,000',
];
$allowed_services = ['Hoardings', 'UniPols', 'Digital Hoardings', 'Transit Marketing'];

$source       = trim($_POST['form_source']  ?? '');
$name         = trim($_POST['full_name']    ?? '');
$phone        = trim($_POST['phone']        ?? '');
$email        = trim($_POST['email']        ?? '');
$company_name = trim($_POST['company_name'] ?? '');
$budget       = trim($_POST['budget']       ?? '');
$services_raw = $_POST['services']          ?? [];

if (!$name || !$phone) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Name and phone are required']);
    exit;
}

if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Invalid email address']);
    exit;
}

if ($budget && !in_array($budget, $allowed_budgets, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Invalid budget selection']);
    exit;
}

$services_clean = array_filter(
    (array) $services_raw,
    fn($s) => in_array($s, $allowed_services, true)
);
$services_str = $services_clean ? implode(',', $services_clean) : null;

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';

try {
    get_db()->prepare(
        'INSERT INTO leads
         (form_source, full_name, phone, email, company_name, budget, services, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $source,
        $name,
        $phone,
        $email        ?: null,
        $company_name ?: null,
        $budget       ?: null,
        $services_str,
        $ip           ?: null,
    ]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Database error. Please try again.']);
}
```

- [ ] **Step 2: Test happy path via curl**

```bash
curl -s -X POST "http://localhost/rabtora.ae/submit_lead.php" \
  --data-urlencode "form_source=landing_popup" \
  --data-urlencode "full_name=Test User" \
  --data-urlencode "phone=0501234567" \
  --data-urlencode "email=test@example.com" \
  --data-urlencode "company_name=TestCo" \
  --data-urlencode "budget=AED 10,000 – 50,000" \
  --data-urlencode "services[]=Hoardings" \
  --data-urlencode "services[]=UniPols"
```

Expected: `{"ok":true}`

Verify in phpMyAdmin or MySQL CLI:
```sql
SELECT id, full_name, company_name, budget, services, form_source
FROM rabtora.leads ORDER BY id DESC LIMIT 1;
```
Expected: row with `services = 'Hoardings,UniPols'` and `budget = 'AED 10,000 – 50,000'`

- [ ] **Step 3: Test validation rejections**

```bash
curl -s -X POST "http://localhost/rabtora.ae/submit_lead.php" -d "full_name=&phone="
```
Expected: `{"ok":false,"msg":"Name and phone are required"}`

```bash
curl -s -X POST "http://localhost/rabtora.ae/submit_lead.php" \
  -d "full_name=Test&phone=123&budget=invalid_value"
```
Expected: `{"ok":false,"msg":"Invalid budget selection"}`

- [ ] **Step 4: Commit**

```bash
git add submit_lead.php
git commit -m "feat: add company_name, budget, services to submit_lead.php"
```

---

### Task 4: Add popup modal to landing page

**Files:**
- Modify: `landing-page/index.html`

- [ ] **Step 1: Add modal CSS inside `<head>` before `</head>`**

Insert this `<style>` block just before `</head>` in `landing-page/index.html`:

```html
<style>
  .lp-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.78);
    z-index: 9000;
    justify-content: center;
    align-items: center;
    padding: 16px;
  }
  .lp-modal-backdrop.active { display: flex; }

  .lp-modal-card {
    background: #111;
    border: 1px solid #2a2a2a;
    border-radius: 8px;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 36px 32px 32px;
    position: relative;
  }

  .lp-modal-close {
    position: absolute;
    top: 14px;
    right: 18px;
    background: none;
    border: none;
    color: #888;
    font-size: 26px;
    cursor: pointer;
    line-height: 1;
    transition: color 0.15s;
  }
  .lp-modal-close:hover { color: #fff; }

  .lp-modal-title {
    font-family: 'DM Sans', sans-serif;
    font-size: 22px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 6px;
  }
  .lp-modal-sub {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    color: #888;
    margin-bottom: 24px;
  }

  .lp-field { margin-bottom: 16px; }
  .lp-field > label {
    display: block;
    font-family: 'DM Sans', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #aaa;
    margin-bottom: 6px;
  }
  .lp-req { color: #c9a84c; }

  .lp-field input[type="text"],
  .lp-field input[type="tel"],
  .lp-field input[type="email"],
  .lp-field select {
    width: 100%;
    background: #1a1a1a;
    border: 1px solid #2a2a2a;
    border-radius: 4px;
    color: #e2e2e2;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    padding: 11px 14px;
    outline: none;
    transition: border-color 0.2s;
    appearance: none;
    -webkit-appearance: none;
  }
  .lp-field select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23888' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 36px;
    cursor: pointer;
  }
  .lp-field input::placeholder { color: #555; }
  .lp-field input:focus,
  .lp-field select:focus { border-color: #c9a84c; }

  .lp-checkboxes { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .lp-check-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: #ccc;
    cursor: pointer;
    text-transform: none;
    letter-spacing: 0;
  }
  .lp-check-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
    min-width: 16px;
    accent-color: #c9a84c;
    cursor: pointer;
    padding: 0;
    border: none;
  }

  .lp-form-error {
    color: #e07070;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    min-height: 18px;
    margin-bottom: 10px;
  }

  .lp-submit-btn {
    width: 100%;
    background: #c9a84c;
    color: #080808;
    border: none;
    border-radius: 4px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 13px;
    cursor: pointer;
    transition: background 0.15s;
    margin-top: 4px;
  }
  .lp-submit-btn:hover:not(:disabled) { background: #e0bc60; }
  .lp-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

  .lp-success { text-align: center; padding: 32px 0 16px; }
  .lp-success-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(201,168,76,0.15);
    color: #c9a84c;
    font-size: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
  }
  .lp-success h3 {
    font-family: 'DM Sans', sans-serif;
    font-size: 20px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 8px;
  }
  .lp-success p { font-family: 'DM Sans', sans-serif; font-size: 14px; color: #888; }

  @media (max-width: 480px) {
    .lp-modal-card { padding: 28px 20px 24px; }
    .lp-checkboxes { grid-template-columns: 1fr; }
  }
</style>
```

- [ ] **Step 2: Add modal HTML before `<script src="js/main.js"></script>`**

Insert this block immediately before `<script src="js/main.js"></script>`:

```html
<!-- LEAD CAPTURE MODAL -->
<div class="lp-modal-backdrop" id="lpModalBackdrop" aria-hidden="true">
  <div class="lp-modal-card" role="dialog" aria-modal="true" aria-labelledby="lpModalTitle">
    <button class="lp-modal-close" id="lpModalClose" aria-label="Close">&times;</button>
    <h2 class="lp-modal-title" id="lpModalTitle">Request A Quote</h2>
    <p class="lp-modal-sub">Tell us about your campaign and we'll get back to you within 24 hours.</p>

    <form id="lpLeadForm" novalidate>
      <input type="hidden" name="form_source" value="landing_popup">

      <div class="lp-field">
        <label for="lp_name">Full Name <span class="lp-req">*</span></label>
        <input type="text" id="lp_name" name="full_name" placeholder="Your full name" required>
      </div>

      <div class="lp-field">
        <label for="lp_phone">Mobile <span class="lp-req">*</span></label>
        <input type="tel" id="lp_phone" name="phone" placeholder="+971 50 000 0000" required>
      </div>

      <div class="lp-field">
        <label for="lp_email">Email</label>
        <input type="email" id="lp_email" name="email" placeholder="you@company.com">
      </div>

      <div class="lp-field">
        <label for="lp_company">Company Name</label>
        <input type="text" id="lp_company" name="company_name" placeholder="Your company">
      </div>

      <div class="lp-field">
        <label for="lp_budget">Budget <span class="lp-req">*</span></label>
        <select id="lp_budget" name="budget" required>
          <option value="" disabled selected>Select Budget</option>
          <option value="Under AED 10,000">Under AED 10,000</option>
          <option value="AED 10,000 – 50,000">AED 10,000 – 50,000</option>
          <option value="AED 50,000 – 100,000">AED 50,000 – 100,000</option>
          <option value="AED 100,000 – 500,000">AED 100,000 – 500,000</option>
          <option value="Above AED 500,000">Above AED 500,000</option>
        </select>
      </div>

      <div class="lp-field">
        <label>Services <span class="lp-req">*</span></label>
        <div class="lp-checkboxes">
          <label class="lp-check-label">
            <input type="checkbox" name="services[]" value="Hoardings"> Hoardings
          </label>
          <label class="lp-check-label">
            <input type="checkbox" name="services[]" value="UniPols"> UniPols
          </label>
          <label class="lp-check-label">
            <input type="checkbox" name="services[]" value="Digital Hoardings"> Digital Hoardings
          </label>
          <label class="lp-check-label">
            <input type="checkbox" name="services[]" value="Transit Marketing"> Transit Marketing
          </label>
        </div>
      </div>

      <div class="lp-form-error" id="lpFormError"></div>

      <button type="submit" class="lp-submit-btn" id="lpSubmitBtn">Send Request</button>
    </form>

    <div class="lp-success" id="lpSuccess" style="display:none">
      <div class="lp-success-icon">&#10003;</div>
      <h3>Thank you!</h3>
      <p>We'll be in touch within 24 hours.</p>
    </div>
  </div>
</div>
```

- [ ] **Step 3: Add modal JavaScript after `<script src="js/main.js"></script>`**

Insert this block immediately after `<script src="js/main.js"></script>`:

```html
<script>
(function () {
  var backdrop  = document.getElementById('lpModalBackdrop');
  var closeBtn  = document.getElementById('lpModalClose');
  var form      = document.getElementById('lpLeadForm');
  var errorEl   = document.getElementById('lpFormError');
  var submitBtn = document.getElementById('lpSubmitBtn');
  var successEl = document.getElementById('lpSuccess');

  function openModal() {
    backdrop.classList.add('active');
    backdrop.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    backdrop.classList.remove('active');
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.btn-quote, .btn-book, .btn-cta-quote').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      openModal();
    });
  });

  closeBtn.addEventListener('click', closeModal);

  backdrop.addEventListener('click', function (e) {
    if (e.target === backdrop) closeModal();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    errorEl.textContent = '';

    var checked = form.querySelectorAll('input[name="services[]"]:checked');
    if (checked.length === 0) {
      errorEl.textContent = 'Please select at least one service.';
      return;
    }

    if (!form.budget.value) {
      errorEl.textContent = 'Please select a budget range.';
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending…';

    fetch('../submit_lead.php', { method: 'POST', body: new FormData(form) })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        if (json.ok) {
          form.style.display = 'none';
          successEl.style.display = 'block';
        } else {
          errorEl.textContent = json.msg || 'Something went wrong. Please try again.';
          submitBtn.disabled = false;
          submitBtn.textContent = 'Send Request';
        }
      })
      .catch(function () {
        errorEl.textContent = 'Network error. Please try again.';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Request';
      });
  });
})();
</script>
```

- [ ] **Step 4: Test modal in browser**

Navigate to: `http://localhost/rabtora.ae/landing-page/index.html`

Verify:
- "Request A Quote" (hero) → modal opens
- "Book Now" (header) → modal opens
- "Request A Quote" (CTA section) → modal opens
- × button → modal closes
- Click backdrop area outside card → modal closes
- Press Escape → modal closes
- Submit with empty name → shows "Please select at least one service." (after filling name/phone) or browser required hint
- Submit without checking a service → "Please select at least one service."
- Submit without selecting budget → "Please select a budget range."
- Fill all fields → button shows "Sending…" → success message appears

Check DB:
```sql
SELECT full_name, phone, company_name, budget, services FROM rabtora.leads ORDER BY id DESC LIMIT 1;
```

- [ ] **Step 5: Commit**

```bash
git add landing-page/index.html
git commit -m "feat: add lead capture popup modal to Rabtora landing page"
```

---

### Task 5: Update admin/dashboard.php

**Files:**
- Modify: `admin/dashboard.php`

- [ ] **Step 1: Replace entire dashboard.php**

Replace entire content of `admin/dashboard.php`:

```php
<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/../config/db.php';

$pdo = get_db();

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $pdo->prepare('DELETE FROM leads WHERE id = ?')->execute([(int) $_POST['delete_id']]);
    $qs = $_GET;
    unset($qs['delete_id']);
    header('Location: dashboard.php' . ($qs ? '?' . http_build_query($qs) : ''));
    exit;
}

// ── CSV EXPORT ────────────────────────────────────────────────────────────────
if (isset($_GET['export'])) {
    $rows = $pdo->query('SELECT * FROM leads ORDER BY created_at DESC')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="rabtora_leads_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Source', 'Full Name', 'Phone', 'Email', 'Company', 'Budget', 'Services', 'IP', 'Submitted']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'], $r['form_source'], $r['full_name'], $r['phone'],
            $r['email'] ?? '', $r['company_name'] ?? '', $r['budget'] ?? '',
            $r['services'] ?? '', $r['ip_address'] ?? '', $r['created_at'],
        ]);
    }
    fclose($out);
    exit;
}

// ── STATS ─────────────────────────────────────────────────────────────────────
$total = (int) $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
$today = (int) $pdo->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$popup = (int) $pdo->query("SELECT COUNT(*) FROM leads WHERE form_source = 'landing_popup'")->fetchColumn();

// ── SEARCH / FILTER / PAGINATE ────────────────────────────────────────────────
$search = trim($_GET['q']   ?? '');
$src    = trim($_GET['src'] ?? '');
$page   = max(1, (int) ($_GET['p'] ?? 1));
$limit  = 50;
$offset = ($page - 1) * $limit;

$where  = [];
$params = [];

if ($search !== '') {
    $like    = '%' . $search . '%';
    $where[] = '(full_name LIKE ? OR phone LIKE ? OR email LIKE ?)';
    array_push($params, $like, $like, $like);
}
if ($src !== '' && in_array($src, ['landing_popup'], true)) {
    $where[]  = 'form_source = ?';
    $params[] = $src;
}

$whereSQL  = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM leads $whereSQL");
$countStmt->execute($params);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $limit));

$rowStmt = $pdo->prepare("SELECT * FROM leads $whereSQL ORDER BY created_at DESC LIMIT ? OFFSET ?");
$rowStmt->execute(array_merge($params, [$limit, $offset]));
$leads = $rowStmt->fetchAll();

// ── HELPERS ───────────────────────────────────────────────────────────────────
function e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
function qstr(array $merge = [], array $remove = []): string {
    $q = array_merge($_GET, $merge);
    foreach ($remove as $k) unset($q[$k]);
    return $q ? '?' . http_build_query($q) : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leads — Rabtora Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600&family=Cormorant+Garamond:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #080808; --card: #111; --border: #222;
      --gold: #c9a84c; --gold2: #e0bc60; --text: #e2e2e2; --muted: #666; --danger: #c94c4c;
    }
    body { background: var(--bg); color: var(--text); font-family: 'Jost', system-ui, sans-serif; font-size: 13px; min-height: 100vh; }

    .nav { background: #0d0d0d; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 28px; height: 56px; }
    .nav-brand { font-family: 'Cormorant Garamond', serif; font-size: 17px; font-weight: 500; letter-spacing: 0.14em; color: var(--gold); text-transform: uppercase; }
    .nav-brand span { font-family: 'Jost', sans-serif; font-size: 10px; letter-spacing: 0.2em; color: var(--muted); text-transform: uppercase; margin-left: 10px; vertical-align: middle; }
    .nav-right { display: flex; align-items: center; gap: 20px; color: var(--muted); font-size: 12px; }
    .nav-right a { color: var(--muted); text-decoration: none; letter-spacing: 0.06em; transition: color 0.15s; }
    .nav-right a:hover { color: var(--text); }

    .main { max-width: 1400px; margin: 0 auto; padding: 28px 24px 60px; }

    .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 28px; }
    .stat { background: var(--card); border: 1px solid var(--border); border-radius: 4px; padding: 18px 20px; }
    .stat-val { font-size: 28px; font-weight: 300; color: var(--gold); line-height: 1; }
    .stat-label { font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); margin-top: 8px; }

    .toolbar { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
    .toolbar input[type="search"] { background: var(--card); border: 1px solid var(--border); color: var(--text); font-family: 'Jost', sans-serif; font-size: 13px; padding: 9px 14px; border-radius: 3px; outline: none; width: 240px; transition: border-color 0.2s; }
    .toolbar input[type="search"]:focus { border-color: rgba(201,168,76,0.4); }
    .toolbar select { background: var(--card); border: 1px solid var(--border); color: var(--text); font-family: 'Jost', sans-serif; font-size: 13px; padding: 9px 12px; border-radius: 3px; outline: none; cursor: pointer; }
    .btn { display: inline-flex; align-items: center; gap: 6px; font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; padding: 9px 18px; border-radius: 3px; border: none; cursor: pointer; text-decoration: none; transition: background 0.15s; }
    .btn-gold { background: var(--gold); color: #080808; }
    .btn-gold:hover { background: var(--gold2); }
    .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--muted); }
    .btn-outline:hover { border-color: var(--gold); color: var(--gold); }
    .ml-auto { margin-left: auto; }

    .table-wrap { background: var(--card); border: 1px solid var(--border); border-radius: 4px; overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: #0d0d0d; font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); padding: 12px 16px; text-align: left; white-space: nowrap; border-bottom: 1px solid var(--border); }
    tbody tr { border-bottom: 1px solid #1a1a1a; transition: background 0.1s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #161616; }
    tbody td { padding: 12px 16px; color: var(--text); vertical-align: middle; }
    tbody td.muted { color: var(--muted); }

    .badge { display: inline-block; font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; padding: 3px 8px; border-radius: 2px; font-weight: 500; }
    .badge-landing-popup { background: rgba(201,168,76,0.15); color: #c9a84c; }

    .services-list { display: flex; flex-wrap: wrap; gap: 4px; }
    .svc-tag { font-size: 10px; padding: 2px 7px; border-radius: 2px; background: rgba(255,255,255,0.06); color: #aaa; white-space: nowrap; }

    .btn-del { background: transparent; border: 1px solid #2a2a2a; color: var(--muted); font-size: 11px; padding: 5px 12px; border-radius: 2px; cursor: pointer; transition: all 0.15s; }
    .btn-del:hover { border-color: var(--danger); color: var(--danger); }
    .empty { text-align: center; color: var(--muted); padding: 48px 0; font-size: 13px; letter-spacing: 0.06em; }

    .pager { display: flex; align-items: center; justify-content: space-between; margin-top: 16px; color: var(--muted); font-size: 12px; }
    .pager-links { display: flex; gap: 6px; }
    .pager-links a, .pager-links span { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border: 1px solid var(--border); border-radius: 3px; text-decoration: none; color: var(--muted); font-size: 12px; transition: all 0.15s; }
    .pager-links a:hover { border-color: var(--gold); color: var(--gold); }
    .pager-links span.current { background: var(--gold); border-color: var(--gold); color: #080808; }

    @media (max-width: 600px) {
      .stats { grid-template-columns: 1fr 1fr; }
      .toolbar { flex-direction: column; align-items: stretch; }
      .toolbar input[type="search"] { width: 100%; }
      .ml-auto { margin-left: 0; }
    }
  </style>
</head>
<body>

<nav class="nav">
  <div class="nav-brand">Rabtora <span>Admin</span></div>
  <div class="nav-right">
    <span><?= e($_SESSION['admin_username']) ?></span>
    <a href="logout.php">Sign Out</a>
  </div>
</nav>

<main class="main">

  <div class="stats">
    <div class="stat">
      <div class="stat-val"><?= $total ?></div>
      <div class="stat-label">Total Leads</div>
    </div>
    <div class="stat">
      <div class="stat-val"><?= $today ?></div>
      <div class="stat-label">Today</div>
    </div>
    <div class="stat">
      <div class="stat-val"><?= $popup ?></div>
      <div class="stat-label">Landing Popup</div>
    </div>
  </div>

  <form method="GET" class="toolbar">
    <input type="search" name="q" placeholder="Search name, phone or email…" value="<?= e($search) ?>">
    <select name="src" onchange="this.form.submit()">
      <option value="" <?= $src === '' ? 'selected' : '' ?>>All Sources</option>
      <option value="landing_popup" <?= $src === 'landing_popup' ? 'selected' : '' ?>>Landing Popup</option>
    </select>
    <button type="submit" class="btn btn-outline">Search</button>
    <?php if ($search || $src): ?>
      <a href="dashboard.php" class="btn btn-outline">Clear</a>
    <?php endif; ?>
    <a href="<?= qstr(['export' => 1]) ?>" class="btn btn-gold ml-auto">&#8595; Export CSV</a>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Source</th>
          <th>Full Name</th>
          <th>Phone</th>
          <th>Email</th>
          <th>Company</th>
          <th>Budget</th>
          <th>Services</th>
          <th>Submitted</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php if ($leads): ?>
        <?php foreach ($leads as $lead): ?>
          <?php
            $src_slug = str_replace('_', '-', $lead['form_source'] ?: 'landing-popup');
            $dt       = $lead['created_at'] ? date('d M Y, H:i', strtotime($lead['created_at'])) : '—';
            $svcItems = $lead['services'] ? explode(',', $lead['services']) : [];
          ?>
          <tr>
            <td class="muted"><?= $lead['id'] ?></td>
            <td><span class="badge badge-<?= e($src_slug) ?>"><?= e($lead['form_source']) ?></span></td>
            <td><?= e($lead['full_name']) ?></td>
            <td><?= e($lead['phone']) ?></td>
            <td class="muted"><?= $lead['email'] ? e($lead['email']) : '—' ?></td>
            <td class="muted"><?= $lead['company_name'] ? e($lead['company_name']) : '—' ?></td>
            <td class="muted"><?= $lead['budget'] ? e($lead['budget']) : '—' ?></td>
            <td>
              <?php if ($svcItems): ?>
                <div class="services-list">
                  <?php foreach ($svcItems as $svc): ?>
                    <span class="svc-tag"><?= e(trim($svc)) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <span class="muted">—</span>
              <?php endif; ?>
            </td>
            <td class="muted"><?= $dt ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('Delete this lead?')">
                <input type="hidden" name="delete_id" value="<?= (int) $lead['id'] ?>">
                <button type="submit" class="btn-del">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="10" class="empty">No leads found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="pager">
      <span>Showing <?= $offset + 1 ?>–<?= min($offset + $limit, $totalRows) ?> of <?= $totalRows ?> leads</span>
      <div class="pager-links">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="current"><?= $i ?></span>
          <?php else: ?>
            <a href="<?= qstr(['p' => $i]) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    </div>
  <?php endif; ?>

</main>
</body>
</html>
```

- [ ] **Step 2: Verify admin panel in browser**

Navigate to: `http://localhost/rabtora.ae/admin/login.php`
Login: `admin` / `Admin@1234`

Check:
- Page title: "Leads — Rabtora Admin"
- Nav brand: "Rabtora Admin"
- 3 stat cards: Total Leads, Today, Landing Popup
- Table columns: #, Source, Full Name, Phone, Email, Company, Budget, Services, Submitted
- Source filter shows "All Sources" and "Landing Popup"
- Test lead from Task 3 appears with services as gold tags
- Click "Export CSV" — download has 10 columns including Company, Budget, Services
- Delete a test row: confirm prompt appears, row disappears

- [ ] **Step 3: Commit**

```bash
git add admin/dashboard.php
git commit -m "feat: update Rabtora admin — rebrand, new columns, stats, CSV export"
```

---

## Done

All 5 tasks complete. End-to-end flow: CTA click → popup → submit → `rabtora.leads` row → admin dashboard.
