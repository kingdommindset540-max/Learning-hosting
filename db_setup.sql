-- Create databases and tables for XAMPP MySQL

CREATE DATABASE IF NOT EXISTS interns_reg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE interns_reg;

CREATE TABLE IF NOT EXISTS interns_application (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    university VARCHAR(255),
    major VARCHAR(255),
    year_of_study VARCHAR(50),
    internship_track VARCHAR(100),
    technical_skills TEXT,
    motivation TEXT,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE DATABASE IF NOT EXISTS Job_reg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Job_reg;

CREATE TABLE IF NOT EXISTS job_applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    desired_position VARCHAR(255),
    years_experience VARCHAR(50),
    education VARCHAR(255),
    technical_skills TEXT,
    experience_description TEXT,
    salary_expectation VARCHAR(100),
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
