<?php
// =============================================
// アンケート（その言い訳、バレた？）
//   survey.php?copy=言い訳ID … コピーした直後（ログイン中なら履歴に保存）
//   survey.php?h=履歴ID      … 履歴から開いたとき（ログイン中）
//   survey.php?id=言い訳ID   … ログインしていないとき
// =============================================
require 'common.php';

$user_id = $_SESSION['user_id'] ?? null;

// ---------- コピーした直後：ログイン中なら履歴に保存 ----------
if (isset($_GET['copy'])) {
    $excuse_id = (int)$_GET['copy'];
    if ($user_id) {
        $stmt = $pdo->prepare('INSERT INTO gomen_history (user_id, excuse_id) VALUES (?, ?) RETURNING id');
        $stmt->execute([$user_id, $excuse_id]);
        header('Location: survey.php?h=' . $stmt->fetchColumn() . '&copied=1');
    } else {
        header('Location: survey.php?id=' . $excuse_id . '&copied=1');
    }
    exit;
}

$h  = (int)($_POST['h']  ?? $_GET['h']  ?? 0);   // 履歴ID
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);   // 言い訳ID

// ---------- 言い訳を取り出す ----------
if ($h) {
    // 自分の履歴だけ開ける
    $stmt = $pdo->prepare('SELECT e.*, h.answer FROM gomen_history h JOIN gomen_excuses e ON e.id = h.excuse_id WHERE h.id = ? AND h.user_id = ?');
    $stmt->execute([$h, $user_id]);
    $row = $stmt->fetch();
    $voted = $row && $row['answer'] !== null;          // 履歴に回答が入っていれば回答済み
} else {
    $stmt = $pdo->prepare('SELECT * FROM gomen_excuses WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    $voted = isset($_SESSION['voted'][$id]);           // ログインなしはセッションで判定
}

if (!$row) {
    header('Location: generate.php');
    exit;
}

$before = null;   // 回答前の信ぴょう性

// ---------- 回答したあとに出す一言（ランダムに1つ選ぶ） ----------
$messages = [
    'barenakatta' => [
        '完全犯罪、成立。',
        '何事もなかったかのように、日常へ。',
        'ミッション、コンプリート。',
        'この秘密は、墓場まで。',
        '相手は何も知らない。それでいい。',
        '次に会うときも、何食わぬ顔で。',
        '平和は守られた。',
        '真実は、闇の中へ。',
    ],
    'bareta' => [
        'ドンマイ。正直に謝るなら、今。',
        '相手のほうが一枚上手だった。',
        'バレるまでが、言い訳。',
        'そろそろ「ごめん」の出番。',
        '嘘は、たいていバレる。',
        'ここからは「ごめん」の時間。',
        '素直さこそ、最強の言い訳。',
        '潔く謝れば、まだ間に合う。',
    ],
];
$message = '';

// ---------- 回答を受け取ったら人数を1増やす ----------
$answer = $_POST['answer'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$voted && ($answer === 'bareta' || $answer === 'barenakatta')) {
    $before = round($row['barenakatta'] / ($row['bareta'] + $row['barenakatta']) * 100);
    $message = $messages[$answer][array_rand($messages[$answer])];   // 一言を選ぶ

    // 言い訳の人数を増やす（列名は上で2つに絞っているので安全）
    $pdo->prepare("UPDATE gomen_excuses SET $answer = $answer + 1 WHERE id = ?")->execute([$row['id']]);
    $row[$answer]++;

    if ($h) {
        $pdo->prepare('UPDATE gomen_history SET answer = ? WHERE id = ?')->execute([$answer, $h]);
    } else {
        $_SESSION['voted'][$id] = true;
    }
    $voted = true;
}

// ---------- 信ぴょう性（バレなかった人の割合） ----------
$total = $row['bareta'] + $row['barenakatta'];
$cred  = $total > 0 ? round($row['barenakatta'] / $total * 100) : 0;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>アンケート | ごめんの前に</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php show_header(); ?>

  <main class="survey">
    <?php if (isset($_GET['copied']) && !$voted): ?>
      <p class="copied">
        言い訳をコピーしました。相手に送ってみてください。
        <?php if ($h): ?>（履歴に保存しました）<?php endif; ?>
      </p>
    <?php endif; ?>

    <!-- 送った言い訳 -->
    <section class="survey-card">
      <div class="result-tags">
        <span class="tag tag-orange"><?= h($row['youken']) ?></span>
        <span class="tag tag-blue"><?= h($row['aite']) ?>あて</span>
        <span class="tag"><?= h($row['tone']) ?></span>
      </div>
      <p class="survey-excuse"><?= h($row['excuse']) ?></p>
    </section>

    <?php if (!$voted): ?>
      <!-- アンケート -->
      <section class="survey-question">
        <h1>この言い訳、バレた？</h1>
        <p class="survey-note">あなたの回答が、この言い訳の信ぴょう性に反映されます。</p>
        <form action="survey.php" method="post" class="survey-buttons">
          <input type="hidden" name="h" value="<?= h($h) ?>">
          <input type="hidden" name="id" value="<?= h($row['id']) ?>">
          <button type="submit" name="answer" value="barenakatta" class="btn btn-big btn-safe">バレなかった</button>
          <button type="submit" name="answer" value="bareta" class="btn btn-big btn-out">バレた</button>
        </form>
        <a href="index.php" class="survey-later">まだわからない（あとで答える）</a>
      </section>

    <?php else: ?>
      <!-- 回答後 -->
      <section class="survey-question">
        <?php if ($message): ?>
          <h1 class="survey-message <?= $answer === 'bareta' ? 'is-out' : 'is-safe' ?>"><?= h($message) ?></h1>
        <?php else: ?>
          <h1>回答済みです</h1>
        <?php endif; ?>
        <div class="cred">
          <span class="cred-label">この言い訳の信ぴょう性</span>
          <span class="cred-value">
            <?php if ($before !== null && $before !== $cred): ?>
              <span class="cred-before"><?= h($before) ?>%</span> →
            <?php endif; ?>
            <?= h($cred) ?>%
          </span>
          <div class="cred-bar"><div style="width: <?= h($cred) ?>%"></div></div>
          <span class="cred-count">回答 <?= h($total) ?>人（バレなかった <?= h($row['barenakatta']) ?>人 ／ バレた <?= h($row['bareta']) ?>人）</span>
        </div>
        <div class="result-actions">
          <a href="generate.php" class="btn btn-primary btn-big">新しく言い訳を作る</a>
          <?php if ($h): ?>
            <a href="history.php" class="btn btn-outline btn-big">履歴を見る</a>
          <?php endif; ?>
        </div>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>