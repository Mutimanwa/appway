DROP DATABASE IF EXISTS pcllab;
CREATE DATABASE pcllab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pcllab;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client', 'visitor') DEFAULT 'visitor',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    company VARCHAR(255),
    avatar VARCHAR(255),
    newsletter BOOLEAN DEFAULT FALSE,
    notifications BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('development', 'design', 'consulting', 'maintenance', 'ia'),
    icon VARCHAR(100),
    price VARCHAR(100) NOT NULL,
    duration VARCHAR(50),
    features JSON,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('web', 'mobile', 'desktop', 'cloud', 'ia', 'database'),
    client_id INT,
    status ENUM('draft', 'in_progress', 'completed', 'delivered') DEFAULT 'draft',
    technologies JSON,
    images JSON,
    demo_url VARCHAR(255),
    github_url VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    start_date DATE,
    end_date DATE,
    delivery_date DATE,
    estimated_budget DECIMAL(10,2),
    actual_budget DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'EUR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id)
);

CREATE TABLE team_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100),
    position VARCHAR(255) NOT NULL,
    description TEXT,
    email VARCHAR(255),
    phone VARCHAR(20),
    avatar VARCHAR(255),
    social_links JSON,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(255),
    service_id INT,
    project_type VARCHAR(255),
    budget VARCHAR(100),
    message TEXT,
    status ENUM('new', 'contacted', 'qualified', 'client', 'archived') DEFAULT 'new',
    source ENUM('website', 'referral', 'social', 'event') DEFAULT 'website',
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT,
    author_id INT NOT NULL,
    category ENUM('tech', 'business', 'tutorial', 'news'),
    tags JSON,
    featured_image JSON,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    meta_title VARCHAR(255),
    meta_description TEXT,
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);
CREATE TABLE post_images(
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT,
    IMAGES_URL VARCHAR(150),
    display_order INT DEFAULT 0,
    is_first BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (article_id) REFERENCES articles(id)
);

CREATE TABLE commentaires (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT,
    content TEXT,
    name VARCHAR(50),
    email VARCHAR(150),
    website VARCHAR(150) NULL,
    is_unabled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id)
);