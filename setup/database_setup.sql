CREATE DATABASE IF NOT EXISTS nabard_db;
USE nabard_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'nabard') NOT NULL DEFAULT 'nabard',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default users if they don't exist
-- Passwords are 'password123' hashed with PASSWORD_DEFAULT (bcrypt usually)
-- Note: In a real scenario, use PHP to generate these hashes. I will use a placeholder hash here.
-- $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi is 'password' (laravel default)
-- Let's generate a quick python script to get a hash for 'password123' to be sure, or just use a known one.
-- Actually, I will insert them via PHP script to ensure hashing is correct for the environment.
