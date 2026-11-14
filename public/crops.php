<?php require __DIR__.'/../config/config.php'; require __DIR__.'/../src/models/Crop.php';
if(empty($_SESSION['user'])){ header('Location: login.php'); exit; }
$isAdmin = ($_SESSION['user']['role_id'] ?? 0) == 1;
if (isset($_GET['delete']) && $isAdmin) {
  try { $pdo->prepare('DELETE FROM crop WHERE crop_id=?')->execute([$_GET['delete']]); $_SESSION['flash']='Crop deleted.'; } catch(Exception $e) { $_SESSION['flash']='Cannot delete: '.$e->getMessage(); }
  header('Location: crops.php'); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $st=$pdo->prepare('INSERT INTO crop (crop_name,variety,crop_category_id,growing_period_days,recommended_season_id) VALUES (?,?,?,?,?)');
  $st->execute([$_POST['crop_name'],$_POST['variety'],$_POST['crop_category_id'],$_POST['growing_period_days'],$_POST['recommended_season_id']]);
  $_SESSION['flash']='Crop created successfully.';
  header('Location: crops.php'); exit;
}
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = $editId ? (new Crop($pdo))->find($editId) : null;
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  (new Crop($pdo))->update((int)$_POST['crop_id'], $_POST);
  $_SESSION['flash']='Crop updated.';
  header('Location: crops.php'); exit;
}
$cats=$pdo->query('SELECT * FROM crop_category')->fetchAll(PDO::FETCH_ASSOC);
$seasons=$pdo->query('SELECT * FROM recommended_season')->fetchAll(PDO::FETCH_ASSOC);
$search = trim($_GET['q'] ?? '');
$catFilter = trim($_GET['category'] ?? '');
$sort = in_array($_GET['sort'] ?? '', ['crop_name','variety']) ? $_GET['sort'] : 'crop_id';
$dir = (($_GET['dir'] ?? '') === 'desc') ? 'DESC' : 'ASC';
$sql = "SELECT c.*, cc.description AS cat, rs.description AS season FROM crop c LEFT JOIN crop_category cc ON cc.crop_category_id=c.crop_category_id LEFT JOIN recommended_season rs ON rs.recommended_season_id=c.recommended_season_id WHERE 1=1";
$params=[];
if ($search !== '') { $sql .= " AND (c.crop_name ILIKE ? OR c.variety ILIKE ? OR cc.description ILIKE ?)"; $like='%'.$search.'%'; array_push($params,$like,$like,$like); }
if ($catFilter !== '') { $sql .= " AND c.crop_category_id = ?"; $params[] = $catFilter; }
$sql .= " ORDER BY c.$sort $dir";
$st=$pdo->prepare($sql);
$st->execute($params);
$rows=$st->fetchAll(PDO::FETCH_ASSOC);
$cats=$pdo->query('SELECT * FROM crop_category')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Crops</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Crops</h2>
<?php if(!empty($_SESSION['flash'])): ?><div class="alert alert-info"><?=htmlspecialchars($_SESSION['flash'])?></div><?php unset($_SESSION['flash']); endif; ?>
<form method="get" class="row g-2 mb-3">
<input class="form-control col" name="q" placeholder="Search" value="<?=htmlspecialchars($search ?? '')?>">
<select class="form-select col" name="category">
<option value="">All categories</option>
<?php foreach($cats as $c): ?><option value="<?=$c['crop_category_id']?>" <?=($catFilter==$c['crop_category_id']?'selected':'')?>><?=htmlspecialchars($c['description'])?></option><?php endforeach; ?>
</select>
<button class="btn btn-outline-secondary">Apply</button>
</form>
<?php if($editing): ?>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="update" value="1">
<input type="hidden" name="crop_id" value="<?=$editing['crop_id']?>">
<input class="form-control col" name="crop_name" value="<?=htmlspecialchars($editing['crop_name'])?>" required>
<input class="form-control col" name="variety" value="<?=htmlspecialchars($editing['variety'])?>">
<select class="form-select col" name="crop_category_id"><?php foreach($cats as $c): ?><option value="<?=$c['crop_category_id']?>" <?=($editing['crop_category_id']==$c['crop_category_id']?'selected':'')?>><?=htmlspecialchars($c['description'])?></option><?php endforeach; ?></select>
<input class="form-control col" name="growing_period_days" type="number" value="<?=$editing['growing_period_days']?>">
<select class="form-select col" name="recommended_season_id"><?php foreach($seasons as $s): ?><option value="<?=$s['recommended_season_id']?>" <?=($editing['recommended_season_id']==$s['recommended_season_id']?'selected':'')?>><?=htmlspecialchars($s['description'])?></option><?php endforeach; ?></select>
<button class="btn btn-primary">Update</button>
<a href="crops.php" class="btn btn-secondary">Cancel</a>
</form>
<?php endif; ?>
<form method="post" class="row g-2 mb-3">
<input class="form-control col" name="crop_name" placeholder="Crop name" required>
<input class="form-control col" name="variety" placeholder="Variety">
<select class="form-select col" name="crop_category_id"><?php foreach($cats as $c): ?><option value="<?=$c['crop_category_id']?>"><?=htmlspecialchars($c['description'])?></option><?php endforeach; ?></select>
<input class="form-control col" name="growing_period_days" type="number" placeholder="Growing days">
<select class="form-select col" name="recommended_season_id"><?php foreach($seasons as $s): ?><option value="<?=$s['recommended_season_id']?>"><?=htmlspecialchars($s['description'])?></option><?php endforeach; ?></select>
<button class="btn btn-primary">Add</button>
</form>
<div class="table-responsive"><table class="table"><thead><tr><th>ID</th><th>Name</th><th>Variety</th><th>Category</th><th>Season</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?=$r['crop_id']?></td><td><?=htmlspecialchars($r['crop_name'])?></td><td><?=htmlspecialchars($r['variety'])?></td><td><?=htmlspecialchars($r['cat'])?></td><td><?=htmlspecialchars($r['season'])?></td><td><a class="btn btn-sm btn-primary" href="?edit=<?=$r['crop_id']?>">Edit</a><?php if($isAdmin): ?> <a class="btn btn-sm btn-danger" href="?delete=<?=$r['crop_id']?>" onclick="return confirm('Delete?')">Delete</a><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<a href="index.php" class="btn btn-secondary">Back</a>
</div></body></html>









