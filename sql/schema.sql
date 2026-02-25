-- SubTrack Database Schema
-- Run: mysql -u root -p subtrack < sql/schema.sql

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    theme_preference ENUM('light','dark') DEFAULT 'light',
    currency VARCHAR(3) DEFAULT 'GBP',
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(64) NULL,
    google_id VARCHAR(255) NULL UNIQUE,
    google_avatar_url VARCHAR(500) NULL,
    auth_provider ENUM('local','google','both') DEFAULT 'local',
    gdpr_consent TINYINT(1) DEFAULT 0,
    gdpr_consent_at DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) NULL,
    colour VARCHAR(7) NULL,
    sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed categories
INSERT INTO categories (name, slug, icon, colour, sort_order) VALUES
('Streaming & Entertainment', 'streaming',    'tv',                   '#EF4444', 1),
('Music',                     'music',         'musical-note',         '#8B5CF6', 2),
('Cloud Storage',             'cloud-storage', 'cloud',                '#3B82F6', 3),
('Software & Apps',           'software',      'code-bracket',         '#10B981', 4),
('Mobile & Phone',            'mobile',        'device-phone-mobile',  '#F59E0B', 5),
('Broadband & Internet',      'broadband',     'wifi',                 '#6366F1', 6),
('Utilities',                 'utilities',     'bolt',                 '#14B8A6', 7),
('Gaming',                    'gaming',        'puzzle-piece',         '#EC4899', 8),
('News & Magazines',          'news',          'newspaper',            '#F97316', 9),
('Fitness & Health',          'fitness',       'heart',                '#22C55E', 10),
('Business & Productivity',   'business',      'briefcase',            '#64748B', 11),
('Other',                     'other',         'ellipsis-horizontal',  '#9CA3AF', 12);

-- --------------------------------------------------------
-- Table: subscriptions
-- --------------------------------------------------------
DROP TABLE IF EXISTS subscriptions;
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    provider VARCHAR(255) NULL,
    logo_path VARCHAR(500) NULL,
    logo_url VARCHAR(500) NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'GBP',
    billing_cycle ENUM('weekly','monthly','quarterly','biannual','annual') DEFAULT 'monthly',
    billing_day TINYINT NULL,
    billing_weekday TINYINT NULL,
    next_billing_date DATE NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('active','paused','cancelled') DEFAULT 'active',
    auto_renews TINYINT(1) DEFAULT 1,
    notes TEXT NULL,
    url VARCHAR(500) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: payment_log
-- --------------------------------------------------------
DROP TABLE IF EXISTS payment_log;
CREATE TABLE payment_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'GBP',
    amount_gbp DECIMAL(10,2) NULL,
    paid_date DATE NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: reminders
-- --------------------------------------------------------
DROP TABLE IF EXISTS reminders;
CREATE TABLE reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    user_id INT NOT NULL,
    days_before TINYINT NOT NULL DEFAULT 3,
    send_email TINYINT(1) DEFAULT 1,
    last_sent_at DATETIME NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: exchange_rates
-- --------------------------------------------------------
DROP TABLE IF EXISTS exchange_rates;
CREATE TABLE exchange_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL DEFAULT 'GBP',
    rate DECIMAL(15,6) NOT NULL,
    fetched_at DATETIME NOT NULL,
    UNIQUE KEY unique_pair (from_currency, to_currency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: password_resets
-- --------------------------------------------------------
DROP TABLE IF EXISTS password_resets;
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
