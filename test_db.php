<?php
try {
    $pdo = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=axiom", "postgres", "");
    echo "Connection successful!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
