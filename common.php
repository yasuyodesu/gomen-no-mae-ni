<?php
// =============================================
// 全ページ共通（各ページの最初に require 'common.php'; する）
// =============================================
session_start();

// ---------- データベースに接続 ----------
// 環境変数 DATABASE_URL に Neon の接続文字列が入っている
//   例）postgresql://ユーザー名:パスワード@ホスト/データベース名?sslmode=require
$url = parse_url(getenv('DATABASE_URL'));
$dsn = 'pgsql:host=' . $url['host'] . ';port=' . ($url['port'] ?? 5432) . ';dbname=' . ltrim($url['path'], '/');

// ?sslmode=require などのオプションも接続設定に加える
parse_str($url['query'] ?? '', $options);
foreach ($options as $key => $value) {
    $dsn .= ';' . $key . '=' . $value;
}

$pdo = new PDO($dsn, urldecode($url['user']), urldecode($url['pass']));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// ---------- テーブルがまだなければ、init.sql で作る（最初の1回だけ） ----------
if ($pdo->query("SELECT to_regclass('public.gomen_excuses')")->fetchColumn() === null) {
    $pdo->exec(file_get_contents(__DIR__ . '/init.sql'));
}

// ---------- 文字をHTMLに安全に表示する ----------
function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// ---------- Cookieがあれば自動でログイン ----------
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember'])) {
    $stmt = $pdo->prepare('SELECT id, username FROM gomen_users WHERE remember_token = ?');
    $stmt->execute([hash('sha256', $_COOKIE['remember'])]);
    $row = $stmt->fetch();
    if ($row) {
        $_SESSION['user_id']  = $row['id'];
        $_SESSION['username'] = $row['username'];
    }
}

// ---------- ヘッダーを表示する ----------
function show_header()
{
    ?>
  <header class="header">
    <a href="index.php" class="logo">
      <img src="logo.png" alt="ごめんの前に" class="logo-img" width="339" height="76">
    </a>
    <nav class="account">
      <?php if (isset($_SESSION['user_id'])): ?>
        <span class="user-name"><?= h($_SESSION['username']) ?> さん</span>
        <form action="index.php" method="post" class="inline-form">
          <button type="submit" name="logout" class="btn btn-outline">ログアウト</button>
        </form>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline">ログイン</a>
        <a href="register.php" class="btn btn-dark">アカウント作成</a>
      <?php endif; ?>
    </nav>
  </header>
    <?php
}
