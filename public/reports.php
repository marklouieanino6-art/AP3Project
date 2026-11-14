<?php require __DIR__.'/../config/config.php';
if(empty($_SESSION['user'])){ header('Location: login.php'); exit; }
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$where = '';
$params = [];
if ($from && $to) { $where = " WHERE harvest_date BETWEEN ? AND ?"; $params = [$from,$to]; }
$total=$pdo->prepare("SELECT COALESCE(SUM(quantity_kg),0) AS qty, COALESCE(SUM(quantity_kg*selling_price_per_kg),0) AS val FROM harvest $where");
$total->execute($params); $total=$total->fetch(PDO::FETCH_ASSOC);
$bycrop=$pdo->query("SELECT c.crop_name, COALESCE(SUM(h.quantity_kg),0) AS qty, COALESCE(SUM(h.quantity_kg*h.selling_price_per_kg),0) AS val FROM harvest h JOIN planting p ON p.planting_id=h.planting_id JOIN crop c ON c.crop_id=p.crop_id GROUP BY c.crop_name ORDER BY c.crop_name")->fetchAll(PDO::FETCH_ASSOC);
$byfarm=$pdo->query("SELECT f.farm_name, fr.first_name, fr.last_name, COUNT(p.planting_id) AS plantings FROM farm f JOIN farmer fr ON fr.farmer_id=f.farmer_id LEFT JOIN planting p ON p.farm_id=f.farm_id GROUP BY f.farm_id, f.farm_name, fr.first_name, fr.last_name ORDER BY f.farm_name")->fetchAll(PDO::FETCH_ASSOC);
$bystatus=$pdo->query("SELECT ps.description AS status, COUNT(*) AS cnt FROM planting p LEFT JOIN planting_status ps ON ps.status_id=p.status_id GROUP BY ps.description ORDER BY ps.description")->fetchAll(PDO::FETCH_ASSOC);
$harvests=$pdo->query("SELECT h.harvest_id, f.farm_name, c.crop_name, h.harvest_date, h.quantity_kg, h.selling_price_per_kg, (h.quantity_kg*h.selling_price_per_kg) AS value FROM harvest h JOIN planting p ON p.planting_id=h.planting_id JOIN farm f ON f.farm_id=p.farm_id JOIN crop c ON c.crop_id=p.crop_id ORDER BY h.harvest_id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Reports</h2>
<form method="get" class="row g-2 mb-3">
<input class="form-control col" type="date" name="from" value="<?=htmlspecialchars($from)?>">
<input class="form-control col" type="date" name="to" value="<?=htmlspecialchars($to)?>">
<button class="btn btn-primary">Filter</button>
</form>
<div class="alert alert-info">Total harvested: <?=number_format($total['qty'],2)?> kg — Estimated value: <?=number_format($total['val'],2)?></div>
<h4>Harvest value by crop</h4>
<table class="table"><thead><tr><th>Crop</th><th>Qty kg</th><th>Value</th></tr></thead><tbody>
<?php foreach($bycrop as $r): ?><tr><td><?=htmlspecialchars($r['crop_name'])?></td><td><?=number_format($r['qty'],2)?></td><td><?=number_format($r['val'],2)?></td></tr><?php endforeach; ?>
</tbody></table>
<h4>Farmers and their farms</h4>
<table class="table"><thead><tr><th>Farm</th><th>Farmer</th><th>Plantings</th></tr></thead><tbody>
<?php foreach($byfarm as $r): ?><tr><td><?=htmlspecialchars($r['farm_name'])?></td><td><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td><td><?=$r['plantings']?></td></tr><?php endforeach; ?>
</tbody></table>
<h4>Plantings by status</h4>
<table class="table"><thead><tr><th>Status</th><th>Count</th></tr></thead><tbody>
<?php foreach($bystatus as $r): ?><tr><td><?=htmlspecialchars($r['status'])?></td><td><?=$r['cnt']?></td></tr><?php endforeach; ?>
</tbody></table>
<h4>Harvest records</h4>
<table class="table"><thead><tr><th>ID</th><th>Farm</th><th>Crop</th><th>Date</th><th>Qty</th><th>Value</th></tr></thead><tbody>
<?php foreach($harvests as $r): ?><tr><td><?=$r['harvest_id']?></td><td><?=htmlspecialchars($r['farm_name'])?></td><td><?=htmlspecialchars($r['crop_name'])?></td><td><?=$r['harvest_date']?></td><td><?=$r['quantity_kg']?></td><td><?=number_format($r['value'],2)?></td></tr><?php endforeach; ?>
</tbody></table>
<a href="index.php" class="btn btn-secondary">Back</a>
</div></body></html>