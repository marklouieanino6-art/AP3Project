<?php
$env = parse_ini_file(__DIR__.'/../.env');
$dsn = "pgsql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']};sslmode=require";
$pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASSWORD'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
session_start();