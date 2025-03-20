<?php

require_once __DIR__ . '/Database.php';
use Database\Database;

class MigrationRunner {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function runMigrations() {
        $migrationsDir = __DIR__ . '/migrations/';
        $files = glob($migrationsDir . '*.sql'); // Get all .sql files

        foreach ($files as $file) {
            echo "Running migration: " . basename($file) . "\n";
            $sql = file_get_contents($file);
            try {
                $this->db->exec($sql);
                echo "✅ Migration successful: " . basename($file) . "\n";
            } catch (PDOException $e) {
                echo "❌ Error running migration " . basename($file) . ": " . $e->getMessage() . "\n";
            }
        }
    }
}

$migrator = new MigrationRunner();
$migrator->runMigrations();
