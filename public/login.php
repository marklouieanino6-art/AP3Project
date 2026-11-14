<?php require __DIR__.'/../config/config.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $st=$pdo->prepare('SELECT * FROM app_user WHERE username=?');
  $st->execute([$_POST['username']]);
  $u=$st->fetch(PDO::FETCH_ASSOC);
  if ($u && password_verify($_POST['password'],$u['password_hash'])) {
    $_SESSION['user']=$u; header('Location: index.php'); exit;
  } else { $err='Invalid credentials'; }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="container mt-5">
<h2>Login</h2>
<?php if(!empty($err)) echo '<div class="alert alert-danger">'.$err.'</div>'; ?>
<form method="post">
<input class="form-control mb-2" name="username" placeholder="Username" required>
<input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
<button class="btn btn-primary">Login</button>
</form></body></html>









