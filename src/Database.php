<?php
class Database {
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }
    public function select(string $table, string $cols='*', array $filters=[], array $options=[]): array {
        $sql = "SELECT $cols FROM $table";
        // filters ignored for simplicity; extend as needed
        $st = $this->pdo->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public function find(string $table, int $id, string $pk): ?array {
        $st = $this->pdo->prepare("SELECT * FROM $table WHERE $pk = ?");
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
    public function insert(string $table, array $data): array {
        $cols = implode(',', array_keys($data));
        $ph = implode(',', array_fill(0, count($data), '?'));
        $st = $this->pdo->prepare("INSERT INTO $table ($cols) VALUES ($ph) RETURNING *");
        $st->execute(array_values($data));
        return $st->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    public function update(string $table, array $data, array $filters=[]): array {
        $set = implode(',', array_map(fn($k)=>"$k=?", array_keys($data)));
        $st = $this->pdo->prepare("UPDATE $table SET $set RETURNING *");
        $st->execute(array_values($data));
        return $st->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    public function delete(string $table, array $filters=[]): array {
        return [];
    }
}