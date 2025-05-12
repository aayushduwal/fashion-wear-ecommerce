-- 1. Users table first
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    reset_token VARCHAR(64),
    reset_expiry DATETIME,
    user_role VARCHAR(10) NOT NULL DEFAULT 'guest',
    PRIMARY KEY (id),
    UNIQUE KEY (username),
    UNIQUE KEY (email)
);

-- 2. Customers table
CREATE TABLE customers (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(15),
    address TEXT,
    city VARCHAR(50),
    country VARCHAR(50),
    postal_code VARCHAR(10),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id)
);

-- 3. Products table
CREATE TABLE products (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    images VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    ratingcount INT(11) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    description TEXT,
    sizes LONGTEXT NOT NULL,
    inventory INT(11) NOT NULL,
    category VARCHAR(255) NOT NULL,
    subcategory VARCHAR(255) DEFAULT NULL,
    show_on_home TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY (slug)
);


-- For men's products
UPDATE products 
SET subcategory = 'Shirts' 
WHERE category = 'Men' AND name LIKE '%shirt%';

UPDATE products 
SET subcategory = 'Jackets' 
WHERE category = 'Men' AND name LIKE '%jacket%';

UPDATE products 
SET subcategory = 'Hoodies' 
WHERE category = 'Men' AND name LIKE '%hoodie%';

UPDATE products 
SET subcategory = 'Sweatshirts' 
WHERE category = 'Men' AND name LIKE '%sweatshirt%';

-- yo herna sql:
-- SELECT name, category, subcategory FROM products WHERE category = 'Men';


-- 4. Orders table (after users and customers tables)
CREATE TABLE orders (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11),
    total_amount DECIMAL(10,2),
    status ENUM('pending','processing','completed','cancelled'),
    shipping_address TEXT,
    order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    customer_id INT(11),
    payment_method VARCHAR(50) NOT NULL,
    payment_ref VARCHAR(255) DEFAULT NULL,
    delivery_zone VARCHAR(50) NOT NULL,
    delivery_date DATE DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);


-- 5. Order Items table (after orders and products tables)
CREATE TABLE order_items (
    id INT(11) NOT NULL AUTO_INCREMENT,
    order_id INT(11),
    product_id INT(11),
    quantity INT(11),
    price DECIMAL(10,2),
    PRIMARY KEY (id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- 6. Customer Details table (after users table)
CREATE TABLE customer_details (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11),
    phone VARCHAR(15),
    address TEXT,
    city VARCHAR(50),
    country VARCHAR(50),
    postal_code VARCHAR(10),
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 7. Admin Users table (admin ko login/signup ko lagi)
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT unique_admin_email UNIQUE (email)
);

CREATE INDEX idx_admin_email ON admin_users(email);


CREATE TABLE cart (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) DEFAULT NULL,
    product_id INT(11) DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) DEFAULT NULL,
    quantity INT(11) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);




