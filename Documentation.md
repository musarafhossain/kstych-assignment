# 📖 Recipe API Documentation

## 1. Introduction

This is a **RESTful Recipe API** built using **PHP**, **PostgreSQL**, **Redis**, **JWT Authentication**, and **Docker**.

### Features:
- Add, view, update, and delete recipes.
- Rate recipes (average rating calculated and stored).
- Protected routes using JWT authentication.
- Redis-based caching for better performance.

### Recipe Schema:
| Field         | Type     | Description                                  |
|---------------|----------|----------------------------------------------|
| `id`          | Integer  | Auto-incremented primary key                 |
| `name`        | String   | Name of the recipe                           |
| `prep_time`   | Integer  | Preparation time in minutes                  |
| `difficulty`  | Integer  | Difficulty level (1 = Easy, 2 = Medium, 3 = Hard) |
| `vegetarian`  | Boolean  | Whether the recipe is vegetarian             |
| `rating`      | Float    | Average rating                               |
| `rating_count`| Integer  | Number of ratings received                   |

---

## 2. Installation Guide

### Prerequisites:
- Docker
- Docker Compose

### Setup Instructions:

1. **Clone the Repository** & Navigate to Project Directory:
   ```bash
   cd /path/to/your/project
   ```

2. **Build and Start Docker Containers**:
   ```bash
   docker-compose build
   docker-compose up -d
   ```

3. **Enter PHP Container**:
   ```bash
   docker exec -it <php_container_name> bash
   ```

4. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

5. **Set Up PostgreSQL Database**:
   ```bash
   docker exec -it <database_container_name> psql -U hellofresh -d hellofresh
   ```

6. **Create Recipes Table**:
   ```sql
   DROP TABLE IF EXISTS recipes;

   CREATE TABLE recipes (
       id SERIAL PRIMARY KEY,
       name VARCHAR(255) NOT NULL,
       prep_time INT NOT NULL,
       difficulty INT CHECK (difficulty BETWEEN 1 AND 3),
       vegetarian BOOLEAN NOT NULL,
       rating FLOAT DEFAULT 0,
       rating_count INT DEFAULT 0
   );
   ```

7. **Exit from PSQL**:
   ```
   \q
   ```

- You’re all set! The project is now ready to use.

---

## 3. API Endpoints

> All protected routes require a valid JWT token.

### Authentication

#### `POST /login`
Dummy login (credentials hardcoded). [**caution:- use the same uid and pass**]
```json
Request Body:
{
  "uid": "admin",
  "pass": "1234"
}
```

```json
Response:
{
  "message": "Login successful",
  "expires": 1744237813
}
```

#### `GET /logout`
```json
Response:
{
  "message": "Logged out"
}
```

---

### Recipes

#### `GET /recipes`
Get all recipes (default: page 1, limit 10).
```json
Response:
{
  "meta": { "total": 1, "page": 1, "limit": 10, "pages": 1 },
  "data": [
    {
      "id": 1,
      "name": "Grilled Salmon",
      "prep_time": 30,
      "difficulty": 3,
      "vegetarian": false,
      "rating": "0",
      "rating_count": 0
    }
  ]
}
```

#### `GET /recipes?page=1&limit=5`
Supports pagination. Max limit is 10.

---

#### `GET /recipes?search=sa`
Search recipes by name (partial match, supports pagination).

---

#### `GET /recipes/{id}`
Fetch a recipe by ID.
```json
Response:
{
  "id": 1,
  "name": "Grilled Salmon",
  "prep_time": 30,
  "difficulty": 3,
  "vegetarian": false,
  "rating": "0",
  "rating_count": 0
}
```

---

#### `POST /recipes` (Protected)
Add a new recipe.
```json
Request Body:
{
  "name": "Lentil Soup",
  "prep_time": 40,
  "difficulty": 3,
  "vegetarian": true
}

Response:
{
  "message": "Recipe added successfully"
}
```

---

#### `PUT /recipes/{id}` (Protected)
Update a recipe by ID.
```json
Request Body:
{
  "name": "Lentil Soup",
  "prep_time": 40,
  "difficulty": 3,
  "vegetarian": true
}

Response:
{
  "message": "Recipe updated"
}
```

---

#### `DELETE /recipes/{id}` (Protected)
Delete a recipe by ID.
```json
Response:
{
  "message": "Recipe deleted successfully"
}
```

---

#### `POST /recipes/{id}/rating`
Submit a rating and update the average.
```json
Request Body:
{
  "rating": 4.6
}

Response:
{
  "message": "Rating added",
  "new_rating": 4.6
}
```

---

## 4. Sample Data for Testing

```json
[
  {
    "name": "Veggie Pasta",
    "prep_time": 20,
    "difficulty": 2,
    "vegetarian": true
  },
  {
    "name": "Chicken Stir Fry",
    "prep_time": 30,
    "difficulty": 3,
    "vegetarian": false
  },
  {
    "name": "Quinoa Salad",
    "prep_time": 15,
    "difficulty": 1,
    "vegetarian": true
  },
  {
    "name": "Biriyani",
    "prep_time": 100,
    "difficulty": 3,
    "vegetarian": false
  },
  {
    "name": "Lentil Soup",
    "prep_time": 40,
    "difficulty": 3,
    "vegetarian": true
  },
  {
    "name": "Grilled Salmon",
    "prep_time": 30,
    "difficulty": 3,
    "vegetarian": false
  }
]
```