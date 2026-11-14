<?php
class Farm {
    private PDO $db;
    public function __construct(PDO $db){ $this->db=$db; }
    public function all(): array { return $this->db->query('SELECT * FROM farm ORDER BY farm_id')->fetchAll(PDO::FETCH_ASSOC); }
    public function find(int $id): ?array { $st=$this->db->prepare('SELECT * FROM farm WHERE farm_id=?'); $st->execute([$id]); $r=$st->fetch(PDO::FETCH_ASSOC); return $r?:null; }
    public function create(array $d): void { $st=$this->db->prepare('INSERT INTO farm (farmer_id,farm_name,location,area_hectares,soil_type_id,irrigation_type_id) VALUES (?,?,?,?,?,?)'); $st->execute([$d['farmer_id'],$d['farm_name'],$d['location'],$d['area_hectares'],$d['soil_type_id'],$d['irrigation_type_id']]); }
    public function update(int $id,array $d): void { $st=$this->db->prepare('UPDATE farm SET farmer_id=?,farm_name=?,location=?,area_hectares=?,soil_type_id=?,irrigation_type_id=? WHERE farm_id=?'); $st->execute([$d['farmer_id'],$d['farm_name'],$d['location'],$d['area_hectares'],$d['soil_type_id'],$d['irrigation_type_id'],$id]); }
    public function delete(int $id): void { $st=$this->db->prepare('DELETE FROM farm WHERE farm_id=?'); $st->execute([$id]); }
}