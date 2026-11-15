<?php
class Harvest {
    private PDO $db;
    public function __construct(PDO $db){ $this->db=$db; }
    public function all(): array { return $this->db->query('SELECT * FROM harvest ORDER BY harvest_id')->fetchAll(PDO::FETCH_ASSOC); }
    public function find(int $id): ?array { $st=$this->db->prepare('SELECT * FROM harvest WHERE harvest_id=?'); $st->execute([$id]); $r=$st->fetch(PDO::FETCH_ASSOC); return $r?:null; }
    public function create(array $d): void { $st=$this->db->prepare('INSERT INTO harvest (planting_id,harvest_date,quantity_kg,selling_price_per_kg,quality_grade_id,remark_id) VALUES (?,?,?,?,?,?)'); $st->execute([$d['planting_id'],$d['harvest_date'],$d['quantity_kg'],$d['selling_price_per_kg'],$d['quality_grade_id'],$d['remark_id']]); }
    public function update(int $id,array $d): void { $st=$this->db->prepare('UPDATE harvest SET planting_id=?,harvest_date=?,quantity_kg=?,selling_price_per_kg=?,quality_grade_id=?,remark_id=? WHERE harvest_id=?'); $st->execute([$d['planting_id'],$d['harvest_date'],$d['quantity_kg'],$d['selling_price_per_kg'],$d['quality_grade_id'],$d['remark_id'],$id]); }
    public function delete(int $id): void { $st=$this->db->prepare('DELETE FROM harvest WHERE harvest_id=?'); $st->execute([$id]); }
}
