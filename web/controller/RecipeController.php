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

// Get Recipe by ID Controller
function getRecipeById($pdo, $id) {
    try{
        // Get the data from cache
        //$cacheKey = "recipes:id:$id";

        // Check if the cache exists
        /*$cached = cache_get($cacheKey);
        if ($cached) {
            echo $cached;
            return;
        }*/

        // If not cached, fetch from the database
        $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if recipe exists
        if ($recipe) {
            $json = json_encode($recipe);
            
            // Set the cache
            //cache_set($cacheKey, $json, 300); // Cache for 5 min
            
            echo $json;
        } else {
            // Recipe not found, return 404
            http_response_code(404);
            echo json_encode(['error' => 'Recipe not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
    }
}

// Recipe Update Controller
function updateRecipe($pdo, $id) {
    try{
        // Check if user is logged in
        //verify_jwt_session();

        // Get the request body
        $data = json_decode(file_get_contents("php://input"), true);

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

        // Validate and sanitize boolean properly
        $stmt = $pdo->prepare("
            UPDATE recipes SET 
                name = :name,
                prep_time = :prep_time,
                difficulty = :difficulty,
                vegetarian = :vegetarian
            WHERE id = :id
        ");
        $stmt->execute([
            'name' => $data['name'],
            'prep_time' => $data['prep_time'],
            'difficulty' => $data['difficulty'],
            'vegetarian' => $vegetarian,
            'id' => $id
        ]);

        // delete if updated recipe exists in cache
        //global $redis;
        //$redis->del("recipes:id:$id");
        
        // Clear cache for all recipes
        //clear_cache();

        // Return success response
        echo json_encode(['message' => 'Recipe updated']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
    }
}

// Recipe Delete Controller
function deleteRecipe($pdo, $id) {
    try {
        // Check if user is logged in
        // verify_jwt_session();

        // Validate ID
        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid recipe ID']);
            return;
        }

        // Check if the recipe exists before deleting
        $checkStmt = $pdo->prepare("SELECT id FROM recipes WHERE id = :id");
        $checkStmt->execute(['id' => $id]);

        if ($checkStmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Recipe not found']);
            return;
        }

        // Delete the recipe
        $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = :id");
        $stmt->execute(['id' => $id]);

        // Clear cache for the deleted recipe and global recipe list
        /*global $redis;
        if (isset($redis)) {
            $redis->del("recipes:id:$id");
        }
        clear_cache();*/

        http_response_code(200);
        echo json_encode(['message' => 'Recipe deleted successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
    }
}
