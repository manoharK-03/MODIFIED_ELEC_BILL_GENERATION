-- Create Database
USE electricity_billing_system;

-- Table: Admin
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admin (username, password) VALUES ('admin', 'admin123');

-- Table: Employees
CREATE TABLE employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample employees
INSERT INTO employees (username, password, full_name, email) VALUES 
('emp001', 'emp123', 'Rajesh Kumar', 'rajesh@electricity.com'),
('emp002', 'emp123', 'Priya Sharma', 'priya@electricity.com');

-- Table: Categories
CREATE TABLE categories (
    category_id INT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL,
    rate_per_unit DECIMAL(10,2) NOT NULL,
    basic_charge DECIMAL(10,2) NOT NULL
);

-- Insert Categories
INSERT INTO categories (category_id, category_name, rate_per_unit, basic_charge) VALUES
(1, 'Household', 5.50, 150.00),
(2, 'Commercial', 8.00, 300.00),
(3, 'Industry', 12.00, 600.00);

-- Table: Users/Customers
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    service_no VARCHAR(20) UNIQUE NOT NULL,
    meter_id VARCHAR(20) UNIQUE NOT NULL,
    user_name VARCHAR(32) NOT NULL,
    address TEXT NOT NULL,
    pincode VARCHAR(6) NOT NULL,
    phone_number VARCHAR(10) NOT NULL,
    category_id INT NOT NULL,
    prev_reading INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    CONSTRAINT chk_user_name_length CHECK (CHAR_LENGTH(user_name) <= 32),
    CONSTRAINT chk_phone_length CHECK (CHAR_LENGTH(phone_number) = 10)
);

-- Table: Bills
CREATE TABLE bills (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    bill_no VARCHAR(20) UNIQUE NOT NULL,
    service_no VARCHAR(20) NOT NULL,
    meter_id VARCHAR(20) NOT NULL,
    user_name VARCHAR(32) NOT NULL,
    address TEXT NOT NULL,
    pincode VARCHAR(6) NOT NULL,
    phone_number VARCHAR(10) NOT NULL,
    prev_reading INT NOT NULL,
    curr_reading INT NOT NULL,
    units_consumed INT NOT NULL,
    rate_per_unit DECIMAL(10,2) NOT NULL,
    basic_charge DECIMAL(10,2) NOT NULL,
    energy_charge DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    bill_date DATE NOT NULL,
    due_date_without_fine DATE NOT NULL,
    due_date_with_fine DATE NOT NULL,
    fine_amount DECIMAL(10,2) DEFAULT 100.00,
    status ENUM('Pending', 'Paid') DEFAULT 'Pending',
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_no) REFERENCES users(service_no)
);

-- Sample Users
INSERT INTO users (service_no, meter_id, user_name, address, pincode, phone_number, category_id, prev_reading) VALUES
('1-000001', 'MTR-H-001', 'Amit Patel', '123 Gandhi Nagar, Hyderabad', '500001', '9876543210', 1, 0),
('2-000001', 'MTR-C-001', 'Sharma Traders', '456 Market Road, Secunderabad', '500003', '9876543211', 2, 0),
('3-000001', 'MTR-I-001', 'Tech Industries Ltd', '789 Industrial Estate, Patancheru', '502319', '9876543212', 3, 0);