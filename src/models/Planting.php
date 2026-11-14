<?php
class Planting {
    private PDO $db;
    public function __construct(PDO $db){ $this->db=$db; }
    public function all(): array { return $this->db->query('SELECT * FROM planting ORDER BY planting_id')->fetchAll(PDO::FETCH_ASSOC); }
    public function find(int $id): ?array { $st=$this->db->prepare('SELECT * FROM planting WHERE planting_id=?'); $st->execute([$id]); $r=$st->fetch(PDO::FETCH_ASSOC); return $r?:null; }
    public function create(array $d): void { $st=$this->db->prepare('INSERT INTO planting (farm_id,crop_id,planting_date,area_planted,seed_quantity,expected_harvest_date,status_id) VALUES (?,?,?,?,?,?,?)'); $st->execute([$d['farm_id'],$d['crop_id'],$d['planting_date'],$d['area_planted'],$d['seed_quantity'],$d['expected_harvest_date'],$d['status_id']]); }
    public function update(int $id,array $d): void { $st=$this->db->prepare('UPDATE planting SET farm_id=?,crop_id=?,planting_date=?,area_planted=?,seed_quantity=?,expected_harvest_date=?,status_id=? WHERE planting_id=?'); $st->execute([$d['farm_id'],$d['crop_id'],$d['planting_date'],$d['area_planted'],$d['seed_quantity'],$d['expected_harvest_date'],$d['status_id'],$id]); }
    public function delete(int $id): void { $st=$this->db->prepare('DELETE FROM planting WHERE planting_id=?'); $st->execute([$id]); }
}