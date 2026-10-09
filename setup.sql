-- HerdShield database setup
-- Import this into phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS herdshield_db;
USE herdshield_db;

CREATE TABLE IF NOT EXISTS roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact_number VARCHAR(30) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

CREATE TABLE IF NOT EXISTS vaccination_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    animal_tag VARCHAR(50) NOT NULL,
    vaccine_type VARCHAR(100) NOT NULL,
    requested_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

INSERT INTO roles (role_name)
SELECT 'Admin'
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE role_name = 'Admin');

INSERT INTO roles (role_name)
SELECT 'Owner'
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE role_name = 'Owner');

INSERT INTO users (role_id, full_name, username, email, password, contact_number, is_active)
SELECT 1, 'Dr. Sarah Connor', 'admin_vet', 'admin@herdshield.com', 'password123', '09000000001', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin_vet');

INSERT INTO users (role_id, full_name, username, email, password, contact_number, is_active)
SELECT 2, 'John Doe', 'farmer_john', 'john@herdshield.com', 'password123', '09000000002', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'farmer_john');

SELECT 'Database setup complete.' AS status;
