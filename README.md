Hi sql thing

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff';
);


CREATE TABLE token_blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token TEXT NOT NULL,
    blacklisted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    time_in TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    time_out TIMESTAMP NULL DEFAULT NULL,
    is_logged_in TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE inventory (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    category ENUM('Writing Supplies', 'Paper Materials', 'Arts & Crafts', 'Organizational Tools', 'Miscellaneous') DEFAULT 'Miscellaneous',
    quantity INT NOT NULL CHECK (quantity >= 0),
    status ENUM('Very Low', 'Low', 'Average', 'High', 'Very High') NOT NULL
);

DELIMITER $$

CREATE TRIGGER before_insert_inventory
BEFORE INSERT ON inventory
FOR EACH ROW
BEGIN
    -- Determine category if not one of the four main categories
    IF NEW.category NOT IN ('Writing Supplies', 'Paper Materials', 'Arts & Crafts', 'Organizational Tools') THEN
        SET NEW.category = 'Miscellaneous';
    END IF;

    -- Determine status based on quantity
    IF NEW.quantity BETWEEN 0 AND 20 THEN
        SET NEW.status = 'Very Low';
    ELSEIF NEW.quantity BETWEEN 21 AND 40 THEN
        SET NEW.status = 'Low';
    ELSEIF NEW.quantity BETWEEN 41 AND 60 THEN
        SET NEW.status = 'Average';
    ELSEIF NEW.quantity BETWEEN 61 AND 80 THEN
        SET NEW.status = 'High';
    ELSE
        SET NEW.status = 'Very High';
    END IF;
END$$

DELIMITER ;

CREATE TABLE archive_items (
    item_id INT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    category ENUM('Writing Supplies', 'Paper Materials', 'Arts & Crafts', 'Organizational Tools', 'Miscellaneous') DEFAULT 'Miscellaneous',
    quantity INT NOT NULL CHECK (quantity >= 0),
    status ENUM('Very Low', 'Low', 'Average', 'High', 'Very High') NOT NULL,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);