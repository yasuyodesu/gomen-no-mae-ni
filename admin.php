<?php
// =============================================
// 管理者用：言い訳の一覧（フィルター・検索・並べ替え）
// =============================================
require 'common.php';

$aite_list   = ['先生', 'バイト先の店長', '友達', '家族'];
$youken_list = ['遅刻', '課題の提出遅れ', '欠席', '飲み会を断る', '既読スルー', '未読スルー'];
$tone_list   = ['丁寧', '普通', 'ネタ'];

// 並べ替え（キー => [表示名, SQLのORDER BY]）
$sort_list = [
    'id'        => ['ID順',              'id'],
    'cred_desc' => ['信ぴょう性が高い順', 'barenakatta * 100.0 / (bareta + barenakatta) DESC, id'],
    'cred_asc'  => ['信ぴょう性が低い順', 'barenakatta * 100.0 / (bareta + barenakatta) ASC, id'],
    'total'     => ['回答が多い順',       '(bareta + barenakatta) DESC, id'],
];

// ---------- 入力の受け取り ----------
$aite   = $_GET['aite']   ?? '';
$youken = $_GET['youken'] ?? '';
$tone   = $_GET['tone']   ?? '';
$q      = trim($_GET['q'] ?? '');
$sort   = $_GET['sort']   ?? 'id';
if (!isset($sort_list[$sort])) $sort = 'id';

$per_page = 50;
$page     = max(1, (int)($_GET['page'] ?? 1));

// ---------- 条件を組み立てる ----------
$where  = [];
$params = [];
if (in_array($aite, $aite_list))     { $where[] = 'aite = ?';   $params[] = $aite; }
if (in_array($youken, $youken_list)) { $where[] = 'youken = ?'; $params[] = $youken; }
if (in_array($tone, $tone_list))     { $where[] = 'tone = ?';   $params[] = $tone; }
if ($q !== '')                       { $where[] = 'excuse LIKE ?'; $params[] = '%' . $q . '%'; }
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 件数と平均の信ぴょう性
$stmt = $pdo->prepare("SELECT COUNT(*) AS n, AVG(barenakatta * 100.0 / (bareta + barenakatta)) AS avg_cred FROM gomen_excuses $where_sql");
$stmt->execute($params);
$sum   = $stmt->fetch();
$count = (int)$sum['n'];
$pages = max(1, (int)ceil($count / $per_page));
$page  = min($page, $pages);

// 一覧
$offset = ($page - 1) * $per_page;
$stmt = $pdo->prepare("SELECT * FROM gomen_excuses $where_sql ORDER BY {$sort_list[$sort][1]} LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$list = $stmt->fetchAll();

// ページ移動用のURL（今の条件を残す）
function page_url($p)
{
    $query = $_GET;
    $query['page'] = $p;
    return 'admin.php?' . http_build_query($query);
}

// 選択肢の<option>を出す
function options($list, $selected)
{
    echo '<option value="">すべて</option>';
    foreach ($list as $v) {
        echo '<option' . ($v === $selected ? ' selected' : '') . '>' . h($v) . '</option>';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>管理：言い訳一覧 | ごめんの前に</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php show_header(); ?>

  <main class="admin">
    <h1>言い訳一覧（管理用）</h1>

    <!-- フィルター・検索 -->
    <form action="admin.php" method="get" class="admin-filter">
      <div class="field">
        <label for="aite">相手</label>
        <select id="aite" name="aite"><?php options($aite_list, $aite); ?></select>
      </div>
      <div class="field">
        <label for="youken">要件</label>
        <select id="youken" name="youken"><?php options($youken_list, $youken); ?></select>
      </div>
      <div class="field">
        <label for="tone">トーン</label>
        <select id="tone" name="tone"><?php options($tone_list, $tone); ?></select>
      </div>
      <div class="field">
        <label for="sort">並べ替え</label>
        <select id="sort" name="sort">
          <?php foreach ($sort_list as $key => $s): ?>
            <option value="<?= h($key) ?>" <?= $key === $sort ? 'selected' : '' ?>><?= h($s[0]) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field admin-search">
        <label for="q">キーワード</label>
        <input type="search" id="q" name="q" value="<?= h($q) ?>" placeholder="例：電車、寝坊、猫">
      </div>
      <div class="admin-filter-buttons">
        <button type="submit" class="btn btn-primary">絞り込む</button>
        <a href="admin.php" class="btn btn-outline">リセット</a>
      </div>
    </form>

    <!-- 集計 -->
    <p class="admin-summary">
      <b><?= h($count) ?></b> 件
      <?php if ($count): ?>
        ／ 平均の信ぴょう性 <b><?= h(round($sum['avg_cred'])) ?>%</b>
      <?php endif; ?>
    </p>

    <!-- 一覧 -->
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>相手</th>
            <th>要件</th>
            <th>トーン</th>
            <th>言い訳</th>
            <th>バレた</th>
            <th>バレなかった</th>
            <th>信ぴょう性</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <?php $total = $r['bareta'] + $r['barenakatta']; $cred = $total ? round($r['barenakatta'] / $total * 100) : 0; ?>
            <tr>
              <td class="num"><?= h($r['id']) ?></td>
              <td class="nowrap"><?= h($r['aite']) ?></td>
              <td class="nowrap"><?= h($r['youken']) ?></td>
              <td class="nowrap"><?= h($r['tone']) ?></td>
              <td class="excuse-cell"><?= h($r['excuse']) ?></td>
              <td class="num"><?= h($r['bareta']) ?></td>
              <td class="num"><?= h($r['barenakatta']) ?></td>
              <td class="num"><b><?= h($cred) ?>%</b></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$list): ?>
            <tr><td colspan="8" class="admin-empty">条件に合う言い訳はありません。</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- ページ移動 -->
    <?php if ($pages > 1): ?>
      <nav class="pager" aria-label="ページ">
        <?php if ($page > 1): ?>
          <a href="<?= h(page_url($page - 1)) ?>" class="btn btn-outline">← 前へ</a>
        <?php endif; ?>
        <span><?= h($page) ?> / <?= h($pages) ?> ページ</span>
        <?php if ($page < $pages): ?>
          <a href="<?= h(page_url($page + 1)) ?>" class="btn btn-outline">次へ →</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </main>
</body>
</html>
