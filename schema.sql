-- ============================================================
-- GlobeTrotter India Database Schema
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `community_posts`;
DROP TABLE IF EXISTS `expenses`;
DROP TABLE IF EXISTS `trip_activities`;
DROP TABLE IF EXISTS `activities`;
DROP TABLE IF EXISTS `itinerary_sections`;
DROP TABLE IF EXISTS `trip_stops`;
DROP TABLE IF EXISTS `trips`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `cities`;
DROP TABLE IF EXISTS `countries`;
DROP TABLE IF EXISTS `regions`;
SET FOREIGN_KEY_CHECKS = 1;

-- Regions (e.g. North India, South India, Western Ghats & Deserts, etc.)
CREATE TABLE IF NOT EXISTS regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    tagline VARCHAR(200),
    description TEXT,
    image_url VARCHAR(500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Countries (Primary: India, with international options)
CREATE TABLE IF NOT EXISTS countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    phone_code VARCHAR(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cities / Destinations
CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_id INT NOT NULL,
    region_id INT,
    name VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    population INT,
    cost_index DECIMAL(5,2) DEFAULT 1.00,
    popularity_score INT DEFAULT 80,
    avg_daily_cost INT DEFAULT 2500, -- in INR (₹)
    image_url VARCHAR(500),
    description TEXT,
    best_time_to_visit VARCHAR(100),
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE SET NULL,
    UNIQUE KEY uq_city_country (name, country_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(25),
    password_hash VARCHAR(255) NOT NULL,
    country_id INT,
    city_id INT,
    extra_info TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    photo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trips
CREATE TABLE IF NOT EXISTS trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    country_id INT,
    city_id INT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    notes TEXT,
    start_date DATE,
    end_date DATE,
    cover_photo VARCHAR(500),
    total_budget DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('planned', 'ongoing', 'completed') DEFAULT 'planned',
    is_public BOOLEAN DEFAULT FALSE,
    share_slug VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Itinerary Sections (Stops / Days / Cities in a multi-destination trip)
CREATE TABLE IF NOT EXISTS itinerary_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    city_id INT,
    section_name VARCHAR(150) NOT NULL,
    start_date DATE,
    end_date DATE,
    budget DECIMAL(10,2) DEFAULT 0.00,
    order_index INT DEFAULT 1,
    notes TEXT,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activities
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    category VARCHAR(100) DEFAULT 'Sightseeing',
    cost DECIMAL(10,2) DEFAULT 0.00, -- in INR (₹)
    duration INT DEFAULT 120, -- in minutes
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trip Activities (linking itinerary stops with specific activities)
CREATE TABLE IF NOT EXISTS trip_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stop_id INT NOT NULL,
    activity_id INT NOT NULL,
    scheduled_time VARCHAR(20),
    notes TEXT,
    cost DECIMAL(10,2) DEFAULT 0.00,
    is_completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (stop_id) REFERENCES itinerary_sections(id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    UNIQUE KEY uq_stop_activity (stop_id, activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Expenses (detailed budget & expense tracking per trip/stop)
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    trip_stop_id INT,
    amount DECIMAL(10,2) NOT NULL,
    category ENUM('stay', 'transport', 'meals', 'activities', 'shopping', 'other') DEFAULT 'other',
    expense_date DATE,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_stop_id) REFERENCES itinerary_sections(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Community Posts (travel stories, tips, and shared itineraries)
CREATE TABLE IF NOT EXISTS community_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trip_id INT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(500),
    likes_count INT DEFAULT 0,
    public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audit Logs (system action history)
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
