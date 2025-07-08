CREATE DATABASE IF NOT EXISTS diagnostic_tool;
USE diagnostic_tool;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL
);

CREATE TABLE commands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  example TEXT,
  category_id INT,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);