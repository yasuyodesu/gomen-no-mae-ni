<?php
// =============================================
// ログイン
// =============================================
require 'common.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT id, username, password FROM gomen_users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // ユーザー名とパスワードが合っていればログイン
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];

        // 「ログイン状態を保持する」にチェックがあればCookieを作る
        if (isset($_POST['remember'])) {
            $token = bin2hex(random_bytes(32));                     // ランダムな合言葉
            $stmt = $pdo->prepare('UPDATE gomen_users SET remember_token = ? WHERE id = ?');
            $stmt->execute([hash('sha256', $token), $user['id']]); // DBにはハッシュを保存
            setcookie('remember', $token, time() + 60 * 60 * 24 * 30, '/', '', false, true); // 30日間
        }

        header('Location: index.php');
        exit;
    }
    $error = 'ユーザー名またはパスワードが違います。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログイン | ごめんの前に</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php show_header(); ?>

  <main class="auth">
    <div class="auth-card">
      <h1>ログイン</h1>

      <?php if ($error): ?>
        <p class="error"><?= h($error) ?></p>
      <?php endif; ?>

      <form action="login.php" method="post">
        <div class="field">
          <label for="username">ユーザー名</label>
          <input type="text" id="username" name="username" required>
        </div>
        <div class="field">
          <label for="password">パスワード</label>
          <input type="password" id="password" name="password" required>
        </div>
        <label class="check">
          <input type="checkbox" name="remember"> ログイン状態を保持する（30日間）
        </label>
        <button type="submit" class="btn btn-primary btn-block">ログイン</button>
      </form>

      <p class="auth-switch">はじめての方は <a href="register.php">アカウント作成</a></p>
    </div>
  </main>
</body>
</html>
