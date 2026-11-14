<?php require __DIR__.'/../config/config.php';
if(empty($_SESSION['user'])){ header('Location: login.php'); exit; }
$u=$_SESSION['user'];
$counts=[];
foreach (['farmer','farm','crop','planting','harvest'] as $t) {
  $counts[$t]=$pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
}
$recentPlant=$pdo->query("SELECT p.planting_id, f.farm_name, c.crop_name, ps.description AS status FROM planting p JOIN farm f ON f.farm_id=p.farm_id JOIN crop c ON c.crop_id=p.crop_id LEFT JOIN planting_status ps ON ps.status_id=p.status_id ORDER BY p.planting_id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$recentHarv=$pdo->query("SELECT h.harvest_id, f.farm_name, c.crop_name, h.quantity_kg FROM harvest h JOIN planting p ON p.planting_id=h.planting_id JOIN farm f ON f.farm_id=p.farm_id JOIN crop c ON c.crop_id=p.crop_id ORDER BY h.harvest_id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$totalHarv=$pdo->query("SELECT COALESCE(SUM(quantity_kg),0) FROM harvest")->fetchColumn();
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary"><div class="container">
<a class="navbar-brand" href="index.php"><img src="logo.webp" alt="logo" height="30"> AgriSys</a>
<div class="navbar-nav">
<a class="nav-link" href="farmers.php">Farmers</a>
<a class="nav-link" href="farms.php">Farms</a>
<a class="nav-link" href="crops.php">Crops</a>
<a class="nav-link" href="plantings.php">Plantings</a>
<a class="nav-link" href="harvests.php">Harvests</a>
<a class="nav-link" href="reports.php">Reports</a>
<a class="nav-link" href="logout.php">Logout (<?=htmlspecialchars($u['username'])?>)</a>
</div></div></nav>
<div class="container mt-4">
<h2>Dashboard</h2>
<div class="row g-3">
<?php foreach($counts as $k=>$v): ?>
<div class="col-md-2"><div class="card text-center"><div class="card-body"><h6><?=ucfirst($k)?>s</h6><h3><?=$v?></h3></div></div></div>
<?php endforeach; ?>
<div class="col-md-2"><div class="card text-center"><div class="card-body"><h6>Total Harvested kg</h6><h3><?=number_format($totalHarv,2)?></h3></div></div></div>
</div>
<div class="row">
<div class="col-md-6">
<h4 class="mt-4">Recent Plantings</h4>
<table class="table"><thead><tr><th>ID</th><th>Farm</th><th>Crop</th><th>Status</th></tr></thead><tbody>
<?php foreach($recentPlant as $r): ?><tr><td><?=$r['planting_id']?></td><td><?=htmlspecialchars($r['farm_name'])?></td><td><?=htmlspecialchars($r['crop_name'])?></td><td><?=htmlspecialchars($r['status'])?></td></tr><?php endforeach; ?>
</tbody></table>
</div>
<div class="col-md-6">
<h4 class="mt-4">Recent Harvests</h4>
<table class="table"><thead><tr><th>ID</th><th>Farm</th><th>Crop</th><th>Qty kg</th></tr></thead><tbody>
<?php foreach($recentHarv as $r): ?><tr><td><?=$r['harvest_id']?></td><td><?=htmlspecialchars($r['farm_name'])?></td><td><?=htmlspecialchars($r['crop_name'])?></td><td><?=$r['quantity_kg']?></td></tr><?php endforeach; ?>
</tbody></table>
</div>
</div>
</div></body></html>









