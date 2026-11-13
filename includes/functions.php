<?php
/** Shared helper functions. */

/** Escape text for HTML output. */
function e($s): string
{
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Escape and keep line breaks as paragraphs. */
function paras(?string $s): string
{
    $s = trim((string)$s);
    if ($s === '') return '';
    $blocks = preg_split("/\R{2,}/u", $s);
    return implode('', array_map(fn($b) => '<p>' . nl2br(e(trim($b))) . '</p>', $blocks));
}

function lang(): string { return $GLOBALS['LANG'] ?? 'en'; }

/** Translated interface text. */
function t(string $key)
{
    $all = $GLOBALS['TEXT'];
    return $all[lang()][$key] ?? $all['en'][$key] ?? $key;
}

/** Choose the field in the current language, falling back to the other. */
function pick(array $row, string $base): string
{
    $first = $row[$base . '_' . lang()] ?? '';
    $other = $row[$base . '_' . (lang() === 'am' ? 'en' : 'am')] ?? '';
    return (string)($first !== '' ? $first : $other);
}

/** Build a public URL: url('news', ['type' => 'event']) */
function url(string $page = 'home', array $params = []): string
{
    $q = $page === 'home' ? [] : ['page' => $page];
    $q = array_merge($q, $params);
    return 'index.php' . ($q ? '?' . http_build_query($q) : '');
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

function client_ip(): string { return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45); }

/* ---------- Settings (editable page text) ---------- */

function settings_all(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    try {
        foreach (db()->query('SELECT skey, svalue FROM settings') as $r) $cache[$r['skey']] = (string)$r['svalue'];
    } catch (Throwable $e) { /* table not created yet */ }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $v = settings_all()[$key] ?? '';
    return $v !== '' ? $v : $default;
}

/** Bilingual setting with fallback to the other language, then to the default text. */
function setting_t(string $base): string
{
    $s = settings_all();
    $other = lang() === 'am' ? 'en' : 'am';
    foreach ([$base . '_' . lang(), $base . '_' . $other] as $k) {
        if (!empty($s[$k])) return $s[$k];
    }
    return (string)t($base . 'Default');
}

function setting_save(string $key, string $value): void
{
    $pdo = db();
    $pdo->prepare('DELETE FROM settings WHERE skey = ?')->execute([$key]);
    $pdo->prepare('INSERT INTO settings (skey, svalue) VALUES (?, ?)')->execute([$key, $value]);
}

/* ---------- Dates (Gregorian in English, Ethiopian calendar in Amharic) ---------- */

function ethiopian_date(int $y, int $m, int $d): array
{
    // Gregorian -> Julian Day Number
    $a = intdiv(14 - $m, 12);
    $yy = $y + 4800 - $a;
    $mm = $m + 12 * $a - 3;
    $jdn = $d + intdiv(153 * $mm + 2, 5) + 365 * $yy + intdiv($yy, 4) - intdiv($yy, 100) + intdiv($yy, 400) - 32045;
    // Julian Day Number -> Ethiopian (Amete Mihret epoch)
    $epoch = 1723856;
    $r = ($jdn - $epoch) % 1461;
    $n = ($r % 365) + 365 * intdiv($r, 1460);
    $year = 4 * intdiv($jdn - $epoch, 1461) + intdiv($r, 365) - intdiv($r, 1460);
    return [$year, intdiv($n, 30) + 1, ($n % 30) + 1];
}

function fmt_date(?string $iso): string
{
    if (!$iso) return '';
    $ts = strtotime(substr($iso, 0, 10));
    if (!$ts) return '';
    if (lang() === 'am') {
        $months = ['መስከረም','ጥቅምት','ኅዳር','ታኅሣሥ','ጥር','የካቲት','መጋቢት','ሚያዝያ','ግንቦት','ሰኔ','ሐምሌ','ነሐሴ','ጳጉሜን'];
        [$y, $m, $d] = ethiopian_date((int)date('Y', $ts), (int)date('n', $ts), (int)date('j', $ts));
        return $months[$m - 1] . ' ' . $d . '፣ ' . $y . ' ዓ.ም.';
    }
    return date('j F Y', $ts);
}

/* ---------- Security ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

function current_user(): ?array
{
    if (empty($_SESSION['uid'])) return null;
    static $u = false;
    if ($u === false) {
        $st = db()->prepare('SELECT id, name, email, role, active FROM users WHERE id = ?');
        $st->execute([$_SESSION['uid']]);
        $u = $st->fetch() ?: null;
        if ($u && !(int)$u['active']) $u = null;
    }
    return $u;
}

function require_login(string $role = 'editor'): array
{
    $u = current_user();
    if (!$u) redirect('login.php');
    if ($role === 'admin' && $u['role'] !== 'admin') {
        http_response_code(403);
        flash('Only administrators can open that page.', 'error');
        redirect('index.php');
    }
    return $u;
}

function flash(?string $msg = null, string $type = 'ok')
{
    if ($msg !== null) { $_SESSION['flash'][] = [$type, $msg]; return null; }
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/** Login throttling: max 5 failed attempts per IP in 15 minutes. */
function login_blocked(): bool
{
    $since = time() - 900;
    db()->prepare('DELETE FROM login_attempts WHERE attempted_at < ?')->execute([$since]);
    $st = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at >= ?');
    $st->execute([client_ip(), $since]);
    return (int)$st->fetchColumn() >= 5;
}

function login_failed(): void
{
    db()->prepare('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)')->execute([client_ip(), time()]);
}

/* ---------- Image uploads ---------- */

/**
 * Validates an uploaded image, re-encodes it (removing hidden data such as GPS location),
 * resizes large photos and makes a thumbnail. Returns the stored file name.
 */
function save_uploaded_image(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('Choose a photo to upload.');
    if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) throw new RuntimeException('The photo is larger than ' . MAX_UPLOAD_MB . ' MB.');
    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($types[$info[2]])) throw new RuntimeException('Upload a JPG, PNG or WebP photo.');
    $ext = $types[$info[2]];
    $name = date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!is_dir(UPLOAD_DIR . '/thumbs')) mkdir(UPLOAD_DIR . '/thumbs', 0755, true);
    $dest = UPLOAD_DIR . '/' . $name;

    if (function_exists('imagecreatefromstring')) {
        $src = imagecreatefromstring(file_get_contents($file['tmp_name']));
        if (!$src) throw new RuntimeException('The photo could not be read.');
        if ($ext === 'jpg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file['tmp_name']);
            $rot = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
            if ($rot) $src = imagerotate($src, $rot, 0);
        }
        image_write(image_fit($src, 1600), $dest, $ext);
        image_write(image_fit($src, 480), UPLOAD_DIR . '/thumbs/' . $name, $ext);
        imagedestroy($src);
    } else {
        // GD not installed: store the original file
        if (!move_uploaded_file($file['tmp_name'], $dest)) throw new RuntimeException('The photo could not be saved.');
        copy($dest, UPLOAD_DIR . '/thumbs/' . $name);
    }
    return $name;
}

function image_fit($src, int $max)
{
    $w = imagesx($src); $h = imagesy($src);
    $scale = min(1, $max / max($w, $h));
    $nw = max(1, (int)round($w * $scale)); $nh = max(1, (int)round($h * $scale));
    $out = imagecreatetruecolor($nw, $nh);
    imagealphablending($out, false); imagesavealpha($out, true);
    imagecopyresampled($out, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    return $out;
}

function image_write($img, string $path, string $ext): void
{
    if ($ext === 'png') imagepng($img, $path, 7);
    elseif ($ext === 'webp') imagewebp($img, $path, 82);
    else imagejpeg($img, $path, 82);
    imagedestroy($img);
}

function delete_image(string $name): void
{
    $name = basename($name);
    foreach ([UPLOAD_DIR . '/' . $name, UPLOAD_DIR . '/thumbs/' . $name] as $p) if (is_file($p)) unlink($p);
}

/** Has the site been installed yet? */
function is_installed(): bool
{
    try { return (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0; }
    catch (Throwable $e) { return false; }
}

/* ---------- Public page snippets ---------- */

function page_head(string $title, string $lead): string
{
    return '<section class="pagehead"><div class="wrap"><h1>' . e($title) . '</h1><p>' . e($lead) . '</p></div></section>';
}

function excerpt(string $s, int $len = 160): string
{
    $s = trim(preg_replace('/\s+/u', ' ', $s));
    return mb_strlen($s) > $len ? mb_substr($s, 0, $len - 1) . '…' : $s;
}

function post_card(array $p): string
{
    $when = $p['type'] === 'event' && $p['event_date'] ? fmt_date($p['event_date']) : fmt_date($p['publish_date']);
    return '<article class="post panel">'
        . '<span class="tag ' . e($p['type']) . '">' . e(t('tag_' . $p['type'])) . '</span><span class="date">' . e($when) . '</span>'
        . '<h3><a href="' . e(url('post', ['id' => $p['id']])) . '">' . e(pick($p, 'title')) . '</a></h3>'
        . '<p class="excerpt">' . e(excerpt(pick($p, 'body'))) . '</p>'
        . '</article>';
}
