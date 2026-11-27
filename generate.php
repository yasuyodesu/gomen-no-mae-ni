<?php
// =============================================
// 言い訳を生成する
// 相手・要件・トーンが同じ言い訳の中から、ランダムに1つ取り出す
// =============================================
require 'common.php';

// ---------- 選択肢 ----------
$aite_list   = ['先生', 'バイト先の店長', '友達', '家族'];
$youken_list = ['遅刻', '課題の提出遅れ', '欠席', '飲み会を断る', '既読スルー', '未読スルー'];
$tone_list   = ['丁寧', '普通', 'ネタ'];

// ---------- 入力の受け取り（はじめは先頭の項目を選んでおく） ----------
$aite   = $_POST['aite']   ?? $aite_list[0];
$youken = $_POST['youken'] ?? $youken_list[0];
$tone   = $_POST['tone']   ?? $tone_list[0];

$row = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && in_array($aite, $aite_list) && in_array($youken, $youken_list) && in_array($tone, $tone_list)) {

    // 条件に合う言い訳をランダムに1つ取り出す
    $stmt = $pdo->prepare('SELECT id, excuse, bareta, barenakatta FROM gomen_excuses WHERE aite = ? AND youken = ? AND tone = ? ORDER BY RANDOM() LIMIT 1');
    $stmt->execute([$aite, $youken, $tone]);
    $row = $stmt->fetch();

    // 信ぴょう性（バレなかった人の割合）
    $total = $row['bareta'] + $row['barenakatta'];
    $cred  = $total > 0 ? round($row['barenakatta'] / $total * 100) : 0;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>言い訳を生成する | ごめんの前に</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php show_header(); ?>

  <main class="gen">
    <!-- 入力フォーム -->
    <form action="generate.php#result" method="post" class="gen-form">
      <h1>状況を教えてください</h1>

      <fieldset class="choices">
        <legend>相手</legend>
        <?php foreach ($aite_list as $a): ?>
          <label class="chip">
            <input type="radio" name="aite" value="<?= h($a) ?>" <?= $a === $aite ? 'checked' : '' ?>>
            <span><?= h($a) ?></span>
          </label>
        <?php endforeach; ?>
      </fieldset>

      <fieldset class="choices">
        <legend>要件</legend>
        <?php foreach ($youken_list as $y): ?>
          <label class="chip">
            <input type="radio" name="youken" value="<?= h($y) ?>" <?= $y === $youken ? 'checked' : '' ?>>
            <span><?= h($y) ?></span>
          </label>
        <?php endforeach; ?>
      </fieldset>

      <fieldset class="choices">
        <legend>トーン</legend>
        <?php foreach ($tone_list as $t): ?>
          <label class="chip">
            <input type="radio" name="tone" value="<?= h($t) ?>" <?= $t === $tone ? 'checked' : '' ?>>
            <span><?= h($t) ?></span>
          </label>
        <?php endforeach; ?>
      </fieldset>

      <button type="submit" class="btn btn-primary btn-block">
        言い訳を作る
      </button>
    </form>

    <!-- 結果 -->
    <?php if ($row): ?>
      <section class="result" id="result">
        <div class="result-tags">
          <span class="tag tag-orange"><?= h($youken) ?></span>
          <span class="tag tag-blue"><?= h($aite) ?>あて</span>
          <span class="tag"><?= h($tone) ?></span>
        </div>
        <p id="excuse" class="result-text"><?= h($row['excuse']) ?></p>

        <!-- 信ぴょう性 -->
        <div class="gen-cred">
          <div class="gen-cred-head">
            <span class="gen-cred-label">信ぴょう性</span>
            <span class="gen-cred-value"><?= h($cred) ?><small>%</small></span>
          </div>
          <div class="cred-bar"><div style="width: <?= h($cred) ?>%"></div></div>
          <span class="cred-count">回答 <?= h($total) ?>人（バレなかった <?= h($row['barenakatta']) ?>人 ／ バレた <?= h($row['bareta']) ?>人）</span>
        </div>

        <div class="result-actions">
          <!-- コピーしてアンケートページへ -->
          <a href="survey.php?copy=<?= h($row['id']) ?>" class="btn btn-primary btn-big" onclick="return copyAndGo(this.href);">
            コピーして送信
          </a>

          <!-- 同じ条件で作り直す -->
          <form action="generate.php#result" method="post" class="inline-form">
            <input type="hidden" name="aite" value="<?= h($aite) ?>">
            <input type="hidden" name="youken" value="<?= h($youken) ?>">
            <input type="hidden" name="tone" value="<?= h($tone) ?>">
            <button type="submit" class="btn btn-outline btn-big">作り直す</button>
          </form>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
          <p class="login-hint"><a href="login.php">ログイン</a>すると、コピーした言い訳が履歴に保存されます。</p>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </main>

  <script>
    // 言い訳をコピーしてからアンケートページへ移動する
    function copyAndGo(url) {
      var text = document.getElementById('excuse').textContent;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(text).finally(function () { location.href = url; });
      } else {
        // http のページなど navigator.clipboard が使えないとき用
        var area = document.createElement('textarea');
        area.value = text;
        document.body.appendChild(area);
        area.select();
        document.execCommand('copy');
        document.body.removeChild(area);
        location.href = url;
      }
      return false;
    }
  </script>
</body>
</html>