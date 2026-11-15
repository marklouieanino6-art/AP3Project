<?php require __DIR__.'/../config/config.php'; require __DIR__.'/../src/models/Harvest.php';
if(empty($_SESSION['user'])){ header('Location: login.php'); exit; }
$isAdmin = ($_SESSION['user']['role_id'] ?? 0) == 1;
if (isset($_GET['delete']) && $isAdmin) {
  try { $pdo->prepare('DELETE FROM harvest WHERE harvest_id=?')->execute([$_GET['delete']]); $_SESSION['flash']='Harvest deleted.'; } catch(Exception $e) { $_SESSION['flash']='Cannot delete: '.$e->getMessage(); }
  header('Location: harvests.php'); exit;
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = $editId ? (new Harvest($pdo))->find($editId) : null;
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  if (empty($_POST['planting_id'])) $errors[] = 'Planting is required.';
  if (!is_numeric($_POST['quantity_kg'] ?? '') || (float)$_POST['quantity_kg'] <= 0) $errors[] = 'Quantity must be > 0.';
  if (!is_numeric($_POST['selling_price_per_kg'] ?? '') || (float)$_POST['selling_price_per_kg'] < 0) $errors[] = 'Price must be >= 0.';
  if (empty($errors)) { (new Harvest($pdo))->update((int)$_POST['harvest_id'], $_POST); $_SESSION['flash']='Harvest updated.'; header('Location: harvests.php'); exit; }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && !isset($_POST['update'])) {
  if (empty($_POST['planting_id'])) $errors[] = 'Planting is required.';
  if (!is_numeric($_POST['quantity_kg'] ?? '') || (float)$_POST['quantity_kg'] <= 0) $errors[] = 'Quantity must be > 0.';
  if (!is_numeric($_POST['selling_price_per_kg'] ?? '') || (float)$_POST['selling_price_per_kg'] < 0) $errors[] = 'Price must be >= 0.';
  if (empty($errors)) {
    $pdo->prepare('INSERT INTO harvest (planting_id,harvest_date,quantity_kg,selling_price_per_kg,quality_grade_id,remark_id) VALUES (?,?,?,?,?,?)')->execute([$_POST['planting_id'],$_POST['harvest_date'],$_POST['quantity_kg'],$_POST['selling_price_per_kg'],$_POST['quality_grade_id'],$_POST['remark_id']]);
    $_SESSION['flash']='Harvest created successfully.';
    header('Location: harvests.php'); exit;
  }
}
$plantings=$pdo->query('SELECT p.planting_id, f.farm_name, c.crop_name FROM planting p JOIN farm f ON f.farm_id=p.farm_id JOIN crop c ON c.crop_id=p.crop_id ORDER BY p.planting_id')->fetchAll(PDO::FETCH_ASSOC);
$grades=$pdo->query('SELECT * FROM quality_grade')->fetchAll(PDO::FETCH_ASSOC);
$remarks=$pdo->query('SELECT * FROM harvest_remark')->fetchAll(PDO::FETCH_ASSOC);
$search = trim($_GET['q'] ?? '');
$gradeFilter = trim($_GET['grade'] ?? '');
$sql = "SELECT h.*, f.farm_name, c.crop_name, q.description AS grade, r.description AS remark FROM harvest h JOIN planting p ON p.planting_id=h.planting_id JOIN farm f ON f.farm_id=p.farm_id JOIN crop c ON c.crop_id=p.crop_id LEFT JOIN quality_grade q ON q.quality_grade_id=h.quality_grade_id LEFT JOIN harvest_remark r ON r.remark_id=h.remark_id WHERE 1=1";
$params=[];
if ($search !== '') { $sql .= " AND (f.farm_name ILIKE ? OR c.crop_name ILIKE ? OR q.description ILIKE ? OR r.description ILIKE ?)"; $like='%'.$search.'%'; array_push($params,$like,$like,$like,$like); }
if ($gradeFilter !== '') { $sql .= " AND h.quality_grade_id = ?"; $params[] = $gradeFilter; }
$sort = in_array($_GET['sort'] ?? '', ['harvest_date','quantity_kg']) ? $_GET['sort'] : 'harvest_id';
$dir = (($_GET['dir'] ?? '') === 'desc') ? 'DESC' : 'ASC';
$sql .= " ORDER BY h.$sort $dir";
$st=$pdo->prepare($sql);
$st->execute($params);
$rows=$st->fetchAll(PDO::FETCH_ASSOC);
$grades=$pdo->query('SELECT * FROM quality_grade')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Harvests</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Harvests</h2>
<?php if(!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if(!empty($_SESSION['flash'])): ?><div class="alert alert-info"><?=htmlspecialchars($_SESSION['flash'])?></div><?php unset($_SESSION['flash']); endif; ?>
<form method="get" class="row g-2 mb-3">
<input class="form-control col" name="q" placeholder="Search" value="<?=htmlspecialchars($search ?? '')?>">
<select class="form-select col" name="grade">
<option value="">All grades</option>
<?php foreach($grades as $g): ?><option value="<?=$g['quality_grade_id']?>" <?=($gradeFilter==$g['quality_grade_id']?'selected':'')?>><?=htmlspecialchars($g['description'])?></option><?php endforeach; ?>
</select>
<button class="btn btn-outline-secondary">Apply</button>
</form>
<?php if($editing): ?>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="update" value="1">
<input type="hidden" name="harvest_id" value="<?=$editing['harvest_id']?>">
<select class="form-select col" name="planting_id"><?php foreach($plantings as $p): ?><option value="<?=$p['planting_id']?>" <?=($editing['planting_id']==$p['planting_id']?'selected':'')?>><?=$p['planting_id']?> - <?=htmlspecialchars($p['farm_name'])?> / <?=htmlspecialchars($p['crop_name'])?></option><?php endforeach; ?></select>
<input class="form-control col" type="date" name="harvest_date" value="<?=$editing['harvest_date']?>" required>
<input class="form-control col" type="number" step="0.01" name="quantity_kg" value="<?=$editing['quantity_kg']?>" required>
<input class="form-control col" type="number" step="0.01" name="selling_price_per_kg" value="<?=$editing['selling_price_per_kg']?>" required>
<select class="form-select col" name="quality_grade_id"><?php foreach($grades as $g): ?><option value="<?=$g['quality_grade_id']?>" <?=($editing['quality_grade_id']==$g['quality_grade_id']?'selected':'')?>><?=htmlspecialchars($g['description'])?></option><?php endforeach; ?></select>
<select class="form-select col" name="remark_id"><?php foreach($remarks as $r): ?><option value="<?=$r['remark_id']?>" <?=($editing['remark_id']==$r['remark_id']?'selected':'')?>><?=htmlspecialchars($r['description'])?></option><?php endforeach; ?></select>
<button class="btn btn-primary">Update</button>
<a href="harvests.php" class="btn btn-secondary">Cancel</a>
</form>
<?php endif; ?>
<form method="post" class="row g-2 mb-3">
<select class="form-select col" name="planting_id" required><?php foreach($plantings as $p): ?><option value="<?=$p['planting_id']?>"><?=$p['planting_id']?> - <?=htmlspecialchars($p['farm_name'])?> / <?=htmlspecialchars($p['crop_name'])?></option><?php endforeach; ?></select>
<input class="form-control col" type="date" name="harvest_date" required>
<input class="form-control col" type="number" step="0.01" name="quantity_kg" placeholder="Qty kg" required>
<input class="form-control col" type="number" step="0.01" name="selling_price_per_kg" placeholder="Price/kg" required>
<select class="form-select col" name="quality_grade_id"><?php foreach($grades as $g): ?><option value="<?=$g['quality_grade_id']?>"><?=htmlspecialchars($g['description'])?></option><?php endforeach; ?></select>
<select class="form-select col" name="remark_id"><?php foreach($remarks as $r): ?><option value="<?=$r['remark_id']?>"><?=htmlspecialchars($r['description'])?></option><?php endforeach; ?></select>
<button class="btn btn-primary">Add</button>
</form>
<div class="table-responsive"><table class="table"><thead><tr><th>ID</th><th>Farm</th><th>Crop</th><th><a href="?sort=harvest_date&dir=asc">Date</a></th><th><a href="?sort=quantity_kg&dir=asc">Qty</a></th><th>Grade</th><th>Remark</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?=$r['harvest_id']?></td><td><?=htmlspecialchars($r['farm_name'])?></td><td><?=htmlspecialchars($r['crop_name'])?></td><td><?=$r['harvest_date']?></td><td><?=$r['quantity_kg']?></td><td><?=htmlspecialchars($r['grade'])?></td><td><?=htmlspecialchars($r['remark'])?></td><td><a class="btn btn-sm btn-primary" href="?edit=<?=$r['harvest_id']?>">Edit</a><?php if($isAdmin): ?> <a class="btn btn-sm btn-danger" href="?delete=<?=$r['harvest_id']?>" onclick="return confirm('Delete?')">Delete</a><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<a href="index.php" class="btn btn-secondary">Back</a>
</div></body></html>









