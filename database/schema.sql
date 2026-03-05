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

CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('phone', 'email') NOT NULL,
    value VARCHAR(255) NOT NULL
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

CREATE TABLE user_contacts (
    user_id INT NOT NULL,
    contact_id INT NOT NULL,
    PRIMARY KEY (user_id, contact_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (contact_id) REFERENCES contacts(id)
);

CREATE TABLE auth (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    login_email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);