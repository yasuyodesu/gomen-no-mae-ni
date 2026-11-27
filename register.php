<?php
// =============================================
// アカウント作成
// =============================================
require 'common.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 同じユーザー名がないか調べる
    $stmt = $pdo->prepare('SELECT id FROM gomen_users WHERE username = ?');
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        $error = 'そのユーザー名はすでに使われています。';
    } else {
        // パスワードはハッシュ化して保存する
        $stmt = $pdo->prepare('INSERT INTO gomen_users (username, password) VALUES (?, ?) RETURNING id');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);

        // そのままログイン状態にしてトップへ
        $_SESSION['user_id']  = $stmt->fetchColumn();
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>アカウント作成 | ごめんの前に</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php show_header(); ?>

  <main class="auth">
    <div class="auth-card">
      <h1>アカウント作成</h1>

      <?php if ($error): ?>
        <p class="error"><?= h($error) ?></p>
      <?php endif; ?>

      <form action="register.php" method="post">
        <div class="field">
          <label for="username">ユーザー名</label>
          <input type="text" id="username" name="username" required maxlength="30">
        </div>
        <div class="field">
          <label for="password">パスワード（8文字以上）</label>
          <input type="password" id="password" name="password" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary btn-block">アカウントを作成する</button>
      </form>

      <p class="auth-switch">アカウントをお持ちの方は <a href="login.php">ログイン</a></p>
    </div>
  </main>
</body>
</html>
