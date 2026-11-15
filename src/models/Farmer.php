<?php
class Farmer {
    private PDO $db;
    public function __construct(PDO $db){ $this->db=$db; }
    public function all(array $f=[]): array {
        $sql='SELECT * FROM farmer'; $p=[];
        if(!empty($f['address'])){ $sql.=' WHERE address=?'; $p[]=$f['address']; }
        $sql.=' ORDER BY farmer_id';
        $st=$this->db->prepare($sql); $st->execute($p); return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public function find(int $id): ?array {
        $st=$this->db->prepare('SELECT * FROM farmer WHERE farmer_id=?'); $st->execute([$id]); $r=$st->fetch(PDO::FETCH_ASSOC); return $r?:null;
    }
    public function create(array $d): void {
        $st=$this->db->prepare('INSERT INTO farmer (first_name,last_name,contact_number,address,date_registered) VALUES (?,?,?,?,CURRENT_DATE)');
        $st->execute([$d['first_name'],$d['last_name'],$d['contact_number'],$d['address']]);
    }
    public function update(int $id,array $d): void {
        $st=$this->db->prepare('UPDATE farmer SET first_name=?,last_name=?,contact_number=?,address=? WHERE farmer_id=?');
        $st->execute([$d['first_name'],$d['last_name'],$d['contact_number'],$d['address'],$id]);
    }
    public function delete(int $id): void {
        $st=$this->db->prepare('DELETE FROM farmer WHERE farmer_id=?'); $st->execute([$id]);
    }
}