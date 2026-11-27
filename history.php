<?php
// =============================================
// 過去の言い訳を確認する（ログインした人だけ）
// =============================================
require 'common.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 自分がコピーした言い訳を新しい順に取り出す
$stmt = $pdo->prepare(
    'SELECT h.id AS h_id, h.answer, h.created_at, e.aite, e.youken, e.tone, e.excuse, e.bareta, e.barenakatta
       FROM gomen_history h JOIN gomen_excuses e ON e.id = h.excuse_id
      WHERE h.user_id = ?
      ORDER BY h.created_at DESC, h.id DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>過去の言い訳 | ごめんの前に</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php show_header(); ?>

  <main class="history">
    <h1>過去の言い訳</h1>

    <?php if (!$list): ?>
      <div class="history-empty">
        <p>まだ履歴がありません。言い訳を作って「コピーして送信」すると、ここに保存されます。</p>
        <a href="generate.php" class="btn btn-primary btn-big">言い訳を作る</a>
      </div>
    <?php endif; ?>

    <?php foreach ($list as $r): ?>
      <?php $total = $r['bareta'] + $r['barenakatta']; $cred = $total ? round($r['barenakatta'] / $total * 100) : 0; ?>
      <article class="history-item">
        <div class="history-head">
          <div class="result-tags">
            <span class="tag tag-orange"><?= h($r['youken']) ?></span>
            <span class="tag tag-blue"><?= h($r['aite']) ?>あて</span>
            <span class="tag"><?= h($r['tone']) ?></span>
          </div>
          <time class="history-date"><?= h(date('Y/m/d H:i', strtotime($r['created_at']))) ?></time>
        </div>

        <p class="history-text"><?= h($r['excuse']) ?></p>

        <div class="history-foot">
          <span class="history-cred">信ぴょう性 <b><?= h($cred) ?>%</b></span>
          <?php if ($r['answer'] === 'barenakatta'): ?>
            <span class="badge badge-safe">バレなかった</span>
          <?php elseif ($r['answer'] === 'bareta'): ?>
            <span class="badge badge-out">バレた</span>
          <?php else: ?>
            <a href="survey.php?h=<?= h($r['h_id']) ?>" class="btn btn-dark">バレたか答える</a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </main>
</body>
</html>
