<?php
class Crop {
    private PDO $db;
    public function __construct(PDO $db){ $this->db=$db; }
    public function all(): array { return $this->db->query('SELECT * FROM crop ORDER BY crop_id')->fetchAll(PDO::FETCH_ASSOC); }
    public function find(int $id): ?array { $st=$this->db->prepare('SELECT * FROM crop WHERE crop_id=?'); $st->execute([$id]); $r=$st->fetch(PDO::FETCH_ASSOC); return $r?:null; }
    public function create(array $d): void { $st=$this->db->prepare('INSERT INTO crop (crop_name,variety,crop_category_id,growing_period_days,recommended_season_id) VALUES (?,?,?,?,?)'); $st->execute([$d['crop_name'],$d['variety'],$d['crop_category_id'],$d['growing_period_days'],$d['recommended_season_id']]); }
    public function update(int $id,array $d): void { $st=$this->db->prepare('UPDATE crop SET crop_name=?,variety=?,crop_category_id=?,growing_period_days=?,recommended_season_id=? WHERE crop_id=?'); $st->execute([$d['crop_name'],$d['variety'],$d['crop_category_id'],$d['growing_period_days'],$d['recommended_season_id'],$id]); }
    public function delete(int $id): void { $st=$this->db->prepare('DELETE FROM crop WHERE crop_id=?'); $st->execute([$id]); }
}