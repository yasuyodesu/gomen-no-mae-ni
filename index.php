<?php
// =============================================
// トップページ（ログアウトの処理もここでする）
// =============================================
require 'common.php';

// ---------- ログアウト ----------
if (isset($_POST['logout'])) {
    $stmt = $pdo->prepare('UPDATE gomen_users SET remember_token = NULL WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);   // 保存していたCookieの合言葉を消す
    setcookie('remember', '', time() - 3600, '/');
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ごめんの前に</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php show_header(); ?>

  <main>
    <section class="hero">
      <h1>その言い訳、<br>通用する？</h1>
      <p>状況を選ぶだけで言い訳を作成。みんなのアンケートで、バレにくさがわかります。</p>
    </section>

    <section class="menu" aria-label="メニュー">
      <!-- 言い訳を生成する -->
      <a href="generate.php" class="card card-main">
        <span class="card-icon">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"></path>
          </svg>
        </span>
        <h2>言い訳を生成する</h2>
        <p>相手・要件・トーンを選ぶと、言い訳と信ぴょう性を表示します。</p>
        <span class="card-go">はじめる →</span>
      </a>

      <!-- 過去の言い訳を確認する -->
      <a href="history.php" class="card card-sub">
        <span class="card-icon">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M12 7v5l3 2"></path>
          </svg>
        </span>
        <h2>過去の言い訳を確認する</h2>
        <p>これまでに作った言い訳の一覧です。バレたかどうかのアンケートにも答えられます。</p>
        <span class="card-go">履歴を見る →</span>
      </a>
    </section>
  </main>

  <footer class="footer">
    ※ネタ用です。本当に大事な場面では、正直に話しましょう。　© ごめんの前に
  </footer>

</body>
</html>
