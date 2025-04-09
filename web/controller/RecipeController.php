<?php
// Get Recipe Controller
function getRecipes($pdo) {
    try{
        // Get the search query from the URL (if any)
        // Example: /recipes?search=chicken
        $search = $_GET['search'] ?? '';
        $cacheKey = $search ? "recipes:search:" . strtolower($search) : "recipes:all";

        // Check if the cache exists
        /*$cached = cache_get($cacheKey);
        if ($cached) {
            echo $cached;
            return;
        }*/

        // If not cached, fetch from the database
        if ($search) {
            $stmt = $pdo->prepare("SELECT * FROM recipes WHERE LOWER(name) LIKE LOWER(:search)");
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM recipes");
        }

        // Fetch all recipes
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($recipes);

        // Set the cache
        //cache_set($cacheKey, $json, 300); // Cache for 5 min

        // Return the data as JSON
        http_response_code(200);
        echo $json;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
    }
}

// Recipe Add Controller
function addRecipe($pdo) {
    try {
        // Check if user is logged in
        // verify_jwt_session();

        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);

        // Check if the request body is valid JSON and contains required fields
        if (!isset($data['name'], $data['prep_time'], $data['difficulty'], $data['vegetarian'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        // Validate and sanitize boolean properly
        $vegetarian = $data['vegetarian'];

        // Ensure 'vegetarian' is either true/false or 1/0
        if (!is_bool($vegetarian) && !in_array($vegetarian, [0, 1], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Vegetarian field must be boolean (true/false) or 0/1']);
            return;
        }

        // Normalize vegetarian value to 0 or 1
        $vegetarian = $vegetarian ? 1 : 0;

        // Insert recipe into the database
        $stmt = $pdo->prepare('INSERT INTO recipes (name, prep_time, difficulty, vegetarian) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['prep_time'],
            $data['difficulty'],
            $vegetarian
        ]);

        // Clear cache
        // clear_cache();

        // Return success response
        http_response_code(201);
        echo json_encode(['message' => 'Recipe added successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
    }
}
