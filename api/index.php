<?php

declare(strict_types=1);

require_once __DIR__ . '/../database/Database.php';

use Database\Database;

// Enable CORS for API access
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database connection
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
} catch (PDOException $e) {
    sendResponse(500, ["error" => "Database connection failed"]);
}

// Main API router
$requestUri = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$resource = $requestUri[1] ?? null;
$id = $requestUri[2] ?? null;
switch ($resource) {
    case 'categories':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($id) {
                getCategoryById($id, $conn);
            } else {
                getAllCategories($conn);
            }
        }
        break;

    case 'courses':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['category'])) {
                getCoursesByCategory($_GET['category'], $conn);
            }else {
                getAllCourses($conn);
            }
        }
        break;

    default:
        sendResponse(404, ["error" => "Resource not found"]);
        break;
}

/**
 * Fetch all categories.
 */
function getAllCategories(PDO $conn): void
{
    try {
        // Fetch all categories
        $stmt = $conn->query("SELECT id, name, parent FROM categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch course count per category
        $stmt = $conn->query("SELECT category_id, COUNT(*) AS course_count FROM courses GROUP BY category_id");
        $courseCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Get an associative array [category_id => course_count]

        // Convert categories into an associative array
        $categoryTree = [];
        foreach ($categories as &$category) {
            $category['count_of_courses'] = $courseCounts[$category['id']] ?? 0; // Direct course count
            $categoryTree[$category['id']] = $category;
        }

        // Recursively add child course counts
        foreach ($categoryTree as $category) {
            if ($category['parent']) {
                addChildCourseCount($categoryTree, $category['parent'], $category['count_of_courses']);
            }
        }

        // Return only categories with aggregated course counts
        sendResponse(200, array_values($categoryTree));
    } catch (PDOException $e) {
        sendResponse(500, ["error" => "Failed to fetch categories", "details" => $e->getMessage()]);
    }
}

/**
 * Recursively adds the child course count to its parent categories
 */
function addChildCourseCount(array &$categories, string $parentId, int $count): void
{
    if (isset($categories[$parentId])) {
        $categories[$parentId]['count_of_courses'] += $count;
        if ($categories[$parentId]['parent']) {
            addChildCourseCount($categories, $categories[$parentId]['parent'], $count);
        }
    }
}


/**
 * Fetch a single category by ID.
 */
function getCategoryById(string $id, PDO $conn): void
{
    if (!isValidUUID($id)) {
        sendResponse(400, ["error" => "Invalid category ID"]);
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($category) {
            sendResponse(200, $category);
        } else {
            sendResponse(404, ["error" => "Category not found"]);
        }
    } catch (PDOException $e) {
        sendResponse(500, ["error" => "Error fetching category"]);
    }
}

/**
 * Fetch all courses.
 */
function getAllCourses(PDO $conn): void
{
    try {
        $sql = "
            SELECT c.id, c.title, c.description, c.image_preview, 
                   c.category_id, cat.name AS category_name 
            FROM courses c
            JOIN categories cat ON c.category_id = cat.id
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        sendResponse(200, $stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        sendResponse(500, ["error" => "Failed to fetch courses", "details" => $e->getMessage()]);
    }
}


/**
 * Fetch courses by category ID.
 */
function getCoursesByCategory(string $categoryId, PDO $conn): void
{
    try {
        // Get all child categories including the given category itself
        $sql = "
            WITH RECURSIVE subcategories AS (
                SELECT id FROM categories WHERE id = :categoryId
                UNION ALL
                SELECT c.id FROM categories c
                JOIN subcategories s ON c.parent = s.id
            )
            SELECT c.id, c.title, c.description, c.image_preview, 
                   c.category_id, cat.name AS category_name
            FROM courses c
            JOIN categories cat ON c.category_id = cat.id
            WHERE c.category_id IN (SELECT id FROM subcategories)
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute(['categoryId' => $categoryId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, $courses);
    } catch (PDOException $e) {
        sendResponse(500, ["error" => "Failed to fetch courses", "details" => $e->getMessage()]);
    }
}



/**
 * Sends a JSON response with a status code.
 */
function sendResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * Validate UUID format.
 */
function isValidUUID(string $uuid): bool
{
    return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $uuid);
}
