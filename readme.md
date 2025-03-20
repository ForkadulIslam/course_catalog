# Course Catalog Application

## Overview
This is a **Course Catalog** application that allows users to browse and filter courses based on categories.

- **Backend:** Pure PHP (PSR-12 compliant) with JSON data storage.  
- **Frontend:** A Single Page Application (SPA) using Vanilla JavaScript.  

---

## Installation & Setup

### 1. Clone the Repository
```sh
git clone https://github.com/your-username/course-catalog.git
cd course-catalog
```

### 2. Impot sql directly or run the migration
Create database and import the db.sql. Please update .env file according to your db credentials
### **OR**
Ensure MySql is installed:
First - Run the migration
```sh
php database/migrate.php # Ensure terminal is opend in the root ex:course-catalog/
```

Second - Run seed to insert sample category and course data 
```sh
php database/seed.php # Ensure terminal is opend in the root ex:course-catalog/
```

Now check the database, categories and courses should be found

---


### 3. Configure the Backend
Ensure PHP is installed with the version 8.2 or above. I have tested in 8.3:
```sh
php -v  # Check PHP version
```

Start the backend:
```sh
php -S localhost:8000 -t api
```
The backend API will now be available at:  
🔗 [http://localhost:8000/index.php](http://localhost:8000/index.php)

---


#### 4. Serve the frontend:

- **Option 1:** Open `index.html` directly in a browser. If found issue for cors then follow the **Option 2:**.
- **Option 2:** Open another terminal in root and run the command below:
  ```sh
  php -S localhost:8080 -t front_end
  ```
  Check "http://localhost:8080" in the browser

---

## API Endpoints
The backend provides the following endpoints:

| Method | Endpoint                          | Description                   |
|--------|----------------------------------|-------------------------------|
| GET    | `/index.php/categories`         | Get all categories            |
| GET    | `/index.php/courses`            | Get all courses               |
| GET    | `/index.php/courses/{id}`       | Get a single course by ID     |
| GET    | `/index.php/courses?category={id}` | Get courses by category  |

Test API with:
```sh
curl -X GET http://localhost:8000/index.php/courses
```
