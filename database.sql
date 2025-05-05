CREATE DATABASE IF NOT EXISTS adatb CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
USE adatb;

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    family_name VARCHAR(255) NOT NULL,
    given_name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO messages (name, email, message) VALUES
('Teszt Felhasználó', 'teszt@example.com', 'Ez egy teszt üzenet.'),
('Minta Péter', 'peter@example.com', 'Szeretnék rendelni egy pizzát!'),
('Kovács Anna', 'anna@example.com', 'Mikor érkezik meg  a rendelésem?');

INSERT INTO users (family_name, given_name, username, password) VALUES
('Teszt', 'Felhasználó', 'user', 'pass123');