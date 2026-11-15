<?php require __DIR__.'/../config/config.php'; require __DIR__.'/../src/models/Planting.php';
if(empty($_SESSION['user'])){ header('Location: login.php'); exit; }
$isAdmin = ($_SESSION['user']['role_id'] ?? 0) == 1;
if (isset($_GET['delete']) && $isAdmin) {
  try { $pdo->prepare('DELETE FROM planting WHERE planting_id=?')->execute([$_GET['delete']]); $_SESSION['flash']='Planting deleted.'; } catch(Exception $e) { $_SESSION['flash']='Cannot delete: '.$e->getMessage(); }
  header('Location: plantings.php'); exit;
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = $editId ? (new Planting($pdo))->find($editId) : null;
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  if (empty($_POST['farm_id']) || empty($_POST['crop_id'])) $errors[] = 'Farm and crop are required.';
  if (!is_numeric($_POST['area_planted'] ?? '') || (float)$_POST['area_planted'] <= 0) $errors[] = 'Area planted must be > 0.';
  if (!is_numeric($_POST['seed_quantity'] ?? '') || (float)$_POST['seed_quantity'] <= 0) $errors[] = 'Seed quantity must be > 0.';
  if (empty($errors)) { (new Planting($pdo))->update((int)$_POST['planting_id'], $_POST); $_SESSION['flash']='Planting updated.'; header('Location: plantings.php'); exit; }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && !isset($_POST['update'])) {
  if (empty($_POST['farm_id']) || empty($_POST['crop_id'])) $errors[] = 'Farm and crop are required.';
  if (!is_numeric($_POST['area_planted'] ?? '') || (float)$_POST['area_planted'] <= 0) $errors[] = 'Area planted must be > 0.';
  if (!is_numeric($_POST['seed_quantity'] ?? '') || (float)$_POST['seed_quantity'] <= 0) $errors[] = 'Seed quantity must be > 0.';
  if (empty($errors)) {
    $pdo->prepare('INSERT INTO planting (farm_id,crop_id,planting_date,area_planted,seed_quantity,expected_harvest_date,status_id) VALUES (?,?,?,?,?,?,?)')->execute([$_POST['farm_id'],$_POST['crop_id'],$_POST['planting_date'],$_POST['area_planted'],$_POST['seed_quantity'],$_POST['expected_harvest_date'],$_POST['status_id']]);
    $_SESSION['flash']='Planting created successfully.';
    header('Location: plantings.php'); exit;
  }
}
$farms=$pdo->query('SELECT farm_id, farm_name FROM farm ORDER BY farm_id')->fetchAll(PDO::FETCH_ASSOC);
$crops=$pdo->query('SELECT crop_id, crop_name FROM crop ORDER BY crop_id')->fetchAll(PDO::FETCH_ASSOC);
$statuses=$pdo->query('SELECT * FROM planting_status')->fetchAll(PDO::FETCH_ASSOC);
$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$sql = "SELECT p.*, f.farm_name, c.crop_name, ps.description AS status FROM planting p JOIN farm f ON f.farm_id=p.farm_id JOIN crop c ON c.crop_id=p.crop_id LEFT JOIN planting_status ps ON ps.status_id=p.status_id WHERE 1=1";
$params=[];
if ($search !== '') { $sql .= " AND (f.farm_name ILIKE ? OR c.crop_name ILIKE ? OR ps.description ILIKE ?)"; $like='%'.$search.'%'; array_push($params,$like,$like,$like); }
if ($statusFilter !== '') { $sql .= " AND p.status_id = ?"; $params[] = $statusFilter; }
$sql .= " ORDER BY p.planting_id";
$st=$pdo->prepare($sql);
$st->execute($params);
$rows=$st->fetchAll(PDO::FETCH_ASSOC);
$statuses=$pdo->query('SELECT * FROM planting_status')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Plantings</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Plantings</h2>
<?php if(!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if(!empty($_SESSION['flash'])): ?><div class="alert alert-info"><?=htmlspecialchars($_SESSION['flash'])?></div><?php unset($_SESSION['flash']); endif; ?>
<form method="get" class="row g-2 mb-3">
<input class="form-control col" name="q" placeholder="Search" value="<?=htmlspecialchars($search ?? '')?>">
<select class="form-select col" name="status">
<option value="">All statuses</option>
<?php foreach($statuses as $s): ?><option value="<?=$s['status_id']?>" <?=($statusFilter==$s['status_id']?'selected':'')?>><?=htmlspecialchars($s['description'])?></option><?php endforeach; ?>
</select>
<button class="btn btn-outline-secondary">Apply</button>
</form>
<?php if($editing): ?>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="update" value="1">
<input type="hidden" name="planting_id" value="<?=$editing['planting_id']?>">
<select class="form-select col" name="farm_id"><?php foreach($farms as $f): ?><option value="<?=$f['farm_id']?>" <?=($editing['farm_id']==$f['farm_id']?'selected':'')?>><?=htmlspecialchars($f['farm_name'])?></option><?php endforeach; ?></select>
<select class="form-select col" name="crop_id"><?php foreach($crops as $c): ?><option value="<?=$c['crop_id']?>" <?=($editing['crop_id']==$c['crop_id']?'selected':'')?>><?=htmlspecialchars($c['crop_name'])?></option><?php endforeach; ?></select>
<input class="form-control col" type="date" name="planting_date" value="<?=$editing['planting_date']?>" required>
<input class="form-control col" type="number" step="0.01" name="area_planted" value="<?=$editing['area_planted']?>" required>
<input class="form-control col" type="number" step="0.01" name="seed_quantity" value="<?=$editing['seed_quantity']?>" required>
<input class="form-control col" type="date" name="expected_harvest_date" value="<?=$editing['expected_harvest_date']?>">
<select class="form-select col" name="status_id"><?php foreach($statuses as $s): ?><option value="<?=$s['status_id']?>" <?=($editing['status_id']==$s['status_id']?'selected':'')?>><?=htmlspecialchars($s['description'])?></option><?php endforeach; ?></select>
<button class="btn btn-primary">Update</button>
<a href="plantings.php" class="btn btn-secondary">Cancel</a>
</form>
<?php endif; ?>
<form method="post" class="row g-2 mb-3">
<select class="form-select col" name="farm_id" required><?php foreach($farms as $f): ?><option value="<?=$f['farm_id']?>"><?=htmlspecialchars($f['farm_name'])?></option><?php endforeach; ?></select>
<select class="form-select col" name="crop_id" required><?php foreach($crops as $c): ?><option value="<?=$c['crop_id']?>"><?=htmlspecialchars($c['crop_name'])?></option><?php endforeach; ?></select>
<input class="form-control col" type="date" name="planting_date" required>
<input class="form-control col" type="number" step="0.01" name="area_planted" placeholder="Area" required>
<input class="form-control col" type="number" step="0.01" name="seed_quantity" placeholder="Seed qty" required>
<input class="form-control col" type="date" name="expected_harvest_date">
<select class="form-select col" name="status_id"><?php foreach($statuses as $s): ?><option value="<?=$s['status_id']?>"><?=htmlspecialchars($s['description'])?></option><?php endforeach; ?></select>
<button class="btn btn-primary">Add</button>
</form>
<div class="table-responsive"><table class="table"><thead><tr><th>ID</th><th>Farm</th><th>Crop</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?=$r['planting_id']?></td><td><?=htmlspecialchars($r['farm_name'])?></td><td><?=htmlspecialchars($r['crop_name'])?></td><td><?=$r['planting_date']?></td><td><?=htmlspecialchars($r['status'])?></td><td><a class="btn btn-sm btn-primary" href="?edit=<?=$r['planting_id']?>">Edit</a><?php if($isAdmin): ?> <a class="btn btn-sm btn-danger" href="?delete=<?=$r['planting_id']?>" onclick="return confirm('Delete?')">Delete</a><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<a href="index.php" class="btn btn-secondary">Back</a>
</div></body></html>









