<?php require __DIR__.'/../config/config.php'; require __DIR__.'/../src/models/Farmer.php';
if(empty($_SESSION['user'])){ header('Location: login.php'); exit; }
$isAdmin = ($_SESSION['user']['role_id'] ?? 0) == 1;
if (isset($_GET['delete']) && $isAdmin) {
  try { $pdo->prepare('DELETE FROM farmer WHERE farmer_id=?')->execute([$_GET['delete']]); $_SESSION['flash']='Farmer deleted.'; } catch(Exception $e) { $_SESSION['flash']='Cannot delete: '.$e->getMessage(); }
  header('Location: farmers.php'); exit;
}
// Edit
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = $editId ? (new Farmer($pdo))->find($editId) : null;
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  if (trim($_POST['first_name'] ?? '') === '') $errors[] = 'First name is required.';
  if (trim($_POST['last_name'] ?? '') === '') $errors[] = 'Last name is required.';
  if (empty($errors)) {
    (new Farmer($pdo))->update((int)$_POST['farmer_id'], $_POST);
    $_SESSION['flash']='Farmer updated.';
    header('Location: farmers.php'); exit;
  }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && !isset($_POST['update'])) {
  if (trim($_POST['first_name'] ?? '') === '') $errors[] = 'First name is required.';
  if (trim($_POST['last_name'] ?? '') === '') $errors[] = 'Last name is required.';
  if (empty($errors)) {
    (new Farmer($pdo))->create($_POST);
    $_SESSION['flash']='Farmer created.';
    header('Location: farmers.php'); exit;
  }
}

$search = trim($_GET['q'] ?? '');
$addrFilter = trim($_GET['address'] ?? '');
$sort = in_array($_GET['sort'] ?? '', ['first_name','last_name','address']) ? $_GET['sort'] : 'farmer_id';
$dir = (($_GET['dir'] ?? '') === 'desc') ? 'DESC' : 'ASC';
$sql = 'SELECT * FROM farmer WHERE 1=1';
$params = [];
if ($search !== '') { $sql .= " AND (first_name ILIKE ? OR last_name ILIKE ? OR address ILIKE ?)"; $like='%'.$search.'%'; array_push($params,$like,$like,$like); }
if ($addrFilter !== '') { $sql .= " AND address = ?"; $params[] = $addrFilter; }
$sql .= " ORDER BY $sort $dir";
$st=$pdo->prepare($sql);
$st->execute($params);
$rows=$st->fetchAll(PDO::FETCH_ASSOC);
$addresses=$pdo->query('SELECT DISTINCT address FROM farmer ORDER BY address')->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Farmers</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Farmers</h2>
<?php if(!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if(!empty($_SESSION['flash'])): ?><div class="alert alert-info"><?=htmlspecialchars($_SESSION['flash'])?></div><?php unset($_SESSION['flash']); endif; ?>
<form method="get" class="row g-2 mb-3">
<input class="form-control col" name="q" placeholder="Search name/address" value="<?=htmlspecialchars($search ?? '')?>">
<select class="form-select col" name="address">
<option value="">All addresses</option>
<?php foreach($addresses as $a): ?><option value="<?=htmlspecialchars($a)?>" <?=($addrFilter===$a?'selected':'')?>><?=htmlspecialchars($a)?></option><?php endforeach; ?>
</select>
<button class="btn btn-outline-secondary">Apply</button>
</form>
<?php if($editing): ?>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="update" value="1">
<input type="hidden" name="farmer_id" value="<?=$editing['farmer_id']?>">
<input class="form-control col" name="first_name" value="<?=htmlspecialchars($editing['first_name'])?>" required>
<input class="form-control col" name="last_name" value="<?=htmlspecialchars($editing['last_name'])?>" required>
<input class="form-control col" name="contact_number" value="<?=htmlspecialchars($editing['contact_number'])?>">
<input class="form-control col" name="address" value="<?=htmlspecialchars($editing['address'])?>">
<button class="btn btn-primary">Update</button>
<a href="farmers.php" class="btn btn-secondary">Cancel</a>
</form>
<?php endif; ?>
<form method="post" class="row g-2 mb-3">
<input class="form-control col" name="first_name" placeholder="First name" required>
<input class="form-control col" name="last_name" placeholder="Last name" required>
<input class="form-control col" name="contact_number" placeholder="Contact">
<input class="form-control col" name="address" placeholder="Address">
<button class="btn btn-primary">Add</button>
</form>
<div class="table-responsive"><table class="table"><thead><tr>
<th><a href="?sort=farmer_id&dir=asc">ID</a></th>
<th><a href="?sort=first_name&dir=asc">Name</a></th>
<th>Contact</th>
<th><a href="?sort=address&dir=asc">Address</a></th><th>Actions</th>
</tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?=$r['farmer_id']?></td><td><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td><td><?=htmlspecialchars($r['contact_number'])?></td><td><?=htmlspecialchars($r['address'])?></td><td><a class="btn btn-sm btn-primary" href="?edit=<?=$r['farmer_id']?>">Edit</a><?php if($isAdmin): ?> <a class="btn btn-sm btn-danger" href="?delete=<?=$r['farmer_id']?>" onclick="return confirm('Delete?')">Delete</a><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<a href="index.php" class="btn btn-secondary">Back</a>
</div></body></html>









