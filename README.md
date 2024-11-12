# Task Management System

This is a Task Management System built with Laravel. It allows admins to create tasks and assign them to users, with validation rules to prevent assigning tasks to admins or non-existent users.

## Features

- **Task Management**: Admins can create tasks with details such as title, description, priority, and due date.
- **User Management**: Tasks can be assigned to regular users, but not to admins.
- **Validation**: Ensures tasks are not assigned to non-existent users and handles validation for role restrictions and more.
- **Task Reassignment**: Only the creator of the task can reassign it to another user.
- **Task Dependency **: Task can be depends on one or many tasks.

## Technologies Used

- **Laravel**: A PHP framework for building web applications.
- **MySQL**: Database management system used for storing data.
- **PHPUnit**: For testing the functionality of the system.

## Requirements

- PHP 8.1 or higher
- Composer
- Laravel 9 or higher
- MySQL or a compatible database

## Installation

Follow these steps to get the project up and running on your local machine:

1. **Clone the repository**:
   ```bash
   git clone https://github.com/yourusername/task-management-system.git
   cd task-management-system
   ```
2. **Install dependencies: Install PHP and Composer dependencies:**
    ```bash
    composer install
    npm install
    ```
3. **Set up the environment file: Copy the .env.example file to .env and update the database credentials.**
    ```bash
    cp .env.example .env
    ```
4. **Generate the application key:**
    ```bash
    php artisan key:generate
    ```
5. **Run migrations: Run the migrations to set up the database schema:**
    ```bash
    php artisan migrate
    ```
6. **Seed the database (optional): You can seed the database with dummy data (users, tasks, etc.) by running:**
    ```bash
    php artisan db:seed
    ```
7. **Start the local development server:**
    ```bash
    php artisan serve
    ```

Your application should now be available at http://localhost:8000.

## Collection Json using Postman Api key:

https://api.getpostman.com/collections/12600872-f7975898-6503-4851-8914-a5b3f7c35895

## Running Tests
  
  This project includes tests to ensure functionality works as expected. To run the tests:
    ```bash
    php artisan test 
    ``` 

## Contributing

   If you'd like to contribute to this project, please follow these steps:

    1. Fork the repository.
    2. Create a new branch (git checkout -b feature/your-feature).
    3. Make your changes and commit them (git commit -am 'Add new feature').
    4. Push to your fork (git push origin feature/your-feature).
    5. Open a pull request.
