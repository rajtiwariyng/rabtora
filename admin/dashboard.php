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

// ── CSV EXPORT ────────────────────────────────────────────────────────────────
if (isset($_GET['export'])) {
    $stmt = $pdo->prepare("SELECT * FROM leads $whereSQL ORDER BY created_at DESC");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
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
