CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE passports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    series VARCHAR(4),
    number VARCHAR(6)
);

CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100),
    street VARCHAR(255),
    house VARCHAR(20),
    apartment VARCHAR(20),
    postal_code VARCHAR(6)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    birth_date DATE,
    passport_id INT,
    address_id INT,
    department_id INT,
    position_id INT,
    salary DECIMAL(10,2),
    hire_date DATE,
    role_id INT NOT NULL,
    status_id INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (passport_id) REFERENCES passports(id),
    FOREIGN KEY (address_id) REFERENCES addresses(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (position_id) REFERENCES positions(id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (status_id) REFERENCES statuses(id)
);

CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('phone', 'email') NOT NULL,
    value VARCHAR(255) NOT NULL,
    is_login BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_email (user_id, type, value)
);

CREATE TABLE auth (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    temp_password VARCHAR(255) NULL,
    password_change_required TINYINT DEFAULT 0,
    last_login TIMESTAMP NULL,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
);