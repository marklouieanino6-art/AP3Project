<?php require __DIR__.'/../config/config.php'; require __DIR__.'/../src/models/Farm.php';
if(empty($_SESSION['user'])){ header('Location: login.php'); exit; }
$isAdmin = ($_SESSION['user']['role_id'] ?? 0) == 1;
if (isset($_GET['delete']) && $isAdmin) {
  try { $pdo->prepare('DELETE FROM farm WHERE farm_id=?')->execute([$_GET['delete']]); $_SESSION['flash']='Farm deleted.'; } catch(Exception $e) { $_SESSION['flash']='Cannot delete: '.$e->getMessage(); }
  header('Location: farms.php'); exit;
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = $editId ? (new Farm($pdo))->find($editId) : null;
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  if (trim($_POST['farm_name'] ?? '') === '') $errors[] = 'Farm name is required.';
  if (!is_numeric($_POST['area_hectares'] ?? '') || (float)$_POST['area_hectares'] <= 0) $errors[] = 'Area must be > 0.';
  if (empty($errors)) { (new Farm($pdo))->update((int)$_POST['farm_id'], $_POST); $_SESSION['flash']='Farm updated.'; header('Location: farms.php'); exit; }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && !isset($_POST['update'])) {
  if (trim($_POST['farm_name'] ?? '') === '') $errors[] = 'Farm name is required.';
  if (!is_numeric($_POST['area_hectares'] ?? '') || (float)$_POST['area_hectares'] <= 0) $errors[] = 'Area must be > 0.';
  if (empty($errors)) {
    $pdo->prepare('INSERT INTO farm (farmer_id,farm_name,location,area_hectares,soil_type_id,irrigation_type_id) VALUES (?,?,?,?,?,?)')->execute([$_POST['farmer_id'],$_POST['farm_name'],$_POST['location'],$_POST['area_hectares'],$_POST['soil_type_id'],$_POST['irrigation_type_id']]);
    $_SESSION['flash']='Farm created successfully.';
    header('Location: farms.php'); exit;
  }
}
$farmers=$pdo->query('SELECT farmer_id, first_name, last_name FROM farmer ORDER BY farmer_id')->fetchAll(PDO::FETCH_ASSOC);
$soils=$pdo->query('SELECT * FROM soil_type')->fetchAll(PDO::FETCH_ASSOC);
$irrs=$pdo->query('SELECT * FROM irrigation_type')->fetchAll(PDO::FETCH_ASSOC);
$search = trim($_GET['q'] ?? '');
$irrFilter = trim($_GET['irrigation'] ?? '');
$sort = in_array($_GET['sort'] ?? '', ['farm_name','location','area_hectares']) ? $_GET['sort'] : 'farm_id';
$dir = (($_GET['dir'] ?? '') === 'desc') ? 'DESC' : 'ASC';
$sql = 'SELECT f.*, fr.first_name, fr.last_name, it.description AS irr FROM farm f JOIN farmer fr ON fr.farmer_id=f.farmer_id LEFT JOIN irrigation_type it ON it.irrigation_type_id=f.irrigation_type_id WHERE 1=1';
$params=[];
if ($search !== '') { $sql .= " AND (f.farm_name ILIKE ? OR f.location ILIKE ? OR fr.first_name ILIKE ? OR fr.last_name ILIKE ?)"; $like='%'.$search.'%'; array_push($params,$like,$like,$like,$like); }
if ($irrFilter !== '') { $sql .= " AND f.irrigation_type_id = ?"; $params[] = $irrFilter; }
$sql .= " ORDER BY f.$sort $dir";
$st=$pdo->prepare($sql);
$st->execute($params);
$rows=$st->fetchAll(PDO::FETCH_ASSOC);
$irrs=$pdo->query('SELECT * FROM irrigation_type')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Farms</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Farms</h2>
<?php if(!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if(!empty($_SESSION['flash'])): ?><div class="alert alert-info"><?=htmlspecialchars($_SESSION['flash'])?></div><?php unset($_SESSION['flash']); endif; ?>
<form method="get" class="row g-2 mb-3">
<input class="form-control col" name="q" placeholder="Search" value="<?=htmlspecialchars($search ?? '')?>">
<select class="form-select col" name="irrigation">
<option value="">All irrigation</option>
<?php foreach($irrs as $i): ?><option value="<?=$i['irrigation_type_id']?>" <?=($irrFilter==$i['irrigation_type_id']?'selected':'')?>><?=htmlspecialchars($i['description'])?></option><?php endforeach; ?>
</select>
<button class="btn btn-outline-secondary">Apply</button>
</form>
<?php if($editing): ?>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="update" value="1">
<input type="hidden" name="farm_id" value="<?=$editing['farm_id']?>">
<select class="form-select col" name="farmer_id"><?php foreach($farmers as $f): ?><option value="<?=$f['farmer_id']?>" <?=($editing['farmer_id']==$f['farmer_id']?'selected':'')?>><?=htmlspecialchars($f['first_name'].' '.$f['last_name'])?></option><?php endforeach; ?></select>
<input class="form-control col" name="farm_name" value="<?=htmlspecialchars($editing['farm_name'])?>" required>
<input class="form-control col" name="location" value="<?=htmlspecialchars($editing['location'])?>">
<input class="form-control col" name="area_hectares" type="number" step="0.01" value="<?=$editing['area_hectares']?>" required>
<select class="form-select col" name="soil_type_id"><?php foreach($soils as $s): ?><option value="<?=$s['soil_type_id']?>" <?=($editing['soil_type_id']==$s['soil_type_id']?'selected':'')?>><?=htmlspecialchars($s['description'])?></option><?php endforeach; ?></select>
<select class="form-select col" name="irrigation_type_id"><?php foreach($irrs as $i): ?><option value="<?=$i['irrigation_type_id']?>" <?=($editing['irrigation_type_id']==$i['irrigation_type_id']?'selected':'')?>><?=htmlspecialchars($i['description'])?></option><?php endforeach; ?></select>
<button class="btn btn-primary">Update</button>
<a href="farms.php" class="btn btn-secondary">Cancel</a>
</form>
<?php endif; ?>
<form method="post" class="row g-2 mb-3">
<select class="form-select col" name="farmer_id" required><?php foreach($farmers as $f): ?><option value="<?=$f['farmer_id']?>"><?=htmlspecialchars($f['first_name'].' '.$f['last_name'])?></option><?php endforeach; ?></select>
<input class="form-control col" name="farm_name" placeholder="Farm name" required>
<input class="form-control col" name="location" placeholder="Location">
<input class="form-control col" name="area_hectares" type="number" step="0.01" placeholder="Area ha" required>
<select class="form-select col" name="soil_type_id"><?php foreach($soils as $s): ?><option value="<?=$s['soil_type_id']?>"><?=htmlspecialchars($s['description'])?></option><?php endforeach; ?></select>
<select class="form-select col" name="irrigation_type_id"><?php foreach($irrs as $i): ?><option value="<?=$i['irrigation_type_id']?>"><?=htmlspecialchars($i['description'])?></option><?php endforeach; ?></select>
<button class="btn btn-primary">Add</button>
</form>
<div class="table-responsive"><table class="table"><thead><tr><th>ID</th><th>Farmer</th><th>Name</th><th>Location</th><th>Area</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?=$r['farm_id']?></td><td><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td><td><?=htmlspecialchars($r['farm_name'])?></td><td><?=htmlspecialchars($r['location'])?></td><td><?=$r['area_hectares']?></td><td><a class="btn btn-sm btn-primary" href="?edit=<?=$r['farm_id']?>">Edit</a><?php if($isAdmin): ?> <a class="btn btn-sm btn-danger" href="?delete=<?=$r['farm_id']?>" onclick="return confirm('Delete?')">Delete</a><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<a href="index.php" class="btn btn-secondary">Back</a>
</div></body></html>









