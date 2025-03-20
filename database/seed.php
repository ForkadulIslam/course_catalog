<?php

declare(strict_types=1);

require_once __DIR__ . '/../database/Database.php';

use Database\Database;

// Load JSON data
$categoriesJson = file_get_contents(__DIR__ . '/../data/categories.json');
$coursesJson = file_get_contents(__DIR__ . '/../data/course_list.json');

// Decode JSON to array
$categories = json_decode($categoriesJson, true);
$courses = json_decode($coursesJson, true);

// Validate JSON data
if ($categories === null || $courses === null) {
    die("❌ Error: Invalid JSON data. Please check categories.json and course_list.json.\n");
}

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

try {
    // Start transaction
    $conn->beginTransaction();

    // Insert categories
    echo "Inserting categories...\n";
    $stmt = $conn->prepare("INSERT INTO categories (id, name, parent) VALUES (?, ?, ?)");

    foreach ($categories as $category) {
        $stmt->execute([
            $category['id'],
            $category['name'],
            $category['parent'] ?? null
        ]);
    }
    echo "✅ Categories inserted successfully!\n";

    // Insert courses
    echo "Inserting courses...\n";
    $stmt = $conn->prepare("INSERT INTO courses (id, title, description, image_preview, category_id) VALUES (?, ?, ?, ?, ?)");

    foreach ($courses as $course) {
        $stmt->execute([
            $course['course_id'],
            $course['title'],
            $course['description'],
            $course['image_preview'],
            $course['category_id']
        ]);
    }
    echo "✅ Courses inserted successfully!\n";

    // Commit transaction
    $conn->commit();

} catch (PDOException $e) {
    // Rollback if error occurs
    $conn->rollBack();
    die("❌ Error inserting data: " . $e->getMessage() . "\n");
}
