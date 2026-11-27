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
    <!-- 最初に見える部分 -->
    <p class="footer-catch">言い訳は、謝るための準備運動。</p>
    <p class="footer-copy">© ごめんの前に</p>

    <!-- 空白（もう終わりだと思わせる） -->
    <div class="footer-gap" aria-hidden="true"></div>

    <!-- スクロールした先の長文 -->
    <section class="footer-long">
      <p>「ごめんの前に」は、<br>言い訳を考える時間を楽しく短くするための、<br>ネタとして遊ぶアプリです。<br>表示される言い訳は、<br>あくまで「こんな言い方もあるかも」という例です。<br>実際に使うかどうかは、<br>あなた自身の判断にお任せします。</p>
      <p>当サイトの言い訳を使ってバレてしまっても、<br>相手との関係が気まずくなっても、<br>成績・評価・アルバイトのシフトなどに影響が出ても、<br>当サイトは一切の責任を負わず、<br>いかなる補償もできません。<br>バレたあとの謝罪も、<br>ご自身でお願いします。</p>
      <p>表示される「信ぴょう性」は、<br>利用者のアンケートの回答をもとに計算した参考値です。<br>その言い訳が通用することを、<br>保証するものではありません。<br>相手や状況によって、<br>結果は大きく変わります。</p>
      <p>当サイトは、<br>あなたの言い訳の共犯者ではありません。<br>目撃者でもありません。<br>たまたまその場に居合わせた、<br>ただの通行人です。<br>事情聴取には応じかねます。</p>
      <p>言い訳の使いすぎによる信用の低下、<br>友達からの「またか」という目、<br>先生からの冷たい視線、<br>店長のため息についても、<br>当サイトは関知しません。</p>
      <p>なお、<br>この注意書きをここまで読むのに使った時間も、<br>お返しできません。<br>これについても、<br>補償はできません。</p>
      <p>どんなに良くできた言い訳よりも、<br>心からの「ごめん」のほうが、<br>強いことがあります。</p>
      <p class="footer-end">準備運動は、ここまで。</p>
    </section>
  </footer>

</body>
</html>
