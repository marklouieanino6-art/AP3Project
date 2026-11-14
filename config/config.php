<?php
$env = [];
if (file_exists(__DIR__.'/../.env')) {
    $env = parse_ini_file(__DIR__.'/../.env');
}
$host = $env['DB_HOST'] ?? getenv('DB_HOST');
$port = $env['DB_PORT'] ?? getenv('DB_PORT') ?: '5432';
$name = $env['DB_NAME'] ?? getenv('DB_NAME') ?: 'postgres';
$user = $env['DB_USER'] ?? getenv('DB_USER');
$pass = $env['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
$dsn = "pgsql:host=$host;port=$port;dbname=$name;sslmode=require";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
session_start();