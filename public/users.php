<?php require __DIR__.'/../config/config.php';
if(empty($_SESSION['user']) || ($_SESSION['user']['role_id'] ?? 0)!=1){ header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  $pdo->prepare('UPDATE app_user SET role_id=? WHERE user_id=?')->execute([$_POST['role_id'],$_POST['user_id']]);
  $_SESSION['flash']='Role updated.';
  header('Location: users.php'); exit;
}
$rows=$pdo->query('SELECT u.*, r.description AS role FROM app_user u LEFT JOIN role r ON r.role_id=u.role_id ORDER BY u.user_id')->fetchAll(PDO::FETCH_ASSOC);
$roles=$pdo->query('SELECT * FROM role')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Users (Admin)</h2>
<?php if(!empty($_SESSION['flash'])): ?><div class="alert alert-info"><?=htmlspecialchars($_SESSION['flash'])?></div><?php unset($_SESSION['flash']); endif; ?>
<table class="table"><thead><tr><th>ID</th><th>Username</th><th>Name</th><th>Role</th><th>Change</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr>
<td><?=$r['user_id']?></td>
<td><?=htmlspecialchars($r['username'])?></td>
<td><?=htmlspecialchars($r['full_name'])?></td>
<td><?=htmlspecialchars($r['role'])?></td>
<td>
<form method="post" class="d-flex gap-2">
<input type="hidden" name="update" value="1">
<input type="hidden" name="user_id" value="<?=$r['user_id']?>">
<select class="form-select form-select-sm" name="role_id">
<?php foreach($roles as $rl): ?><option value="<?=$rl['role_id']?>" <?=($r['role_id']==$rl['role_id']?'selected':'')?>><?=htmlspecialchars($rl['description'])?></option><?php endforeach; ?>
</select>
<button class="btn btn-sm btn-primary">Save</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
<a href="index.php" class="btn btn-secondary">Back</a>
</div></body></html>