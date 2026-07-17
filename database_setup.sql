-- Add admin flag to voters (run once)
ALTER TABLE voters ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) NOT NULL DEFAULT 0;

-- To make a user admin (replace 'yourusername'):
-- UPDATE voters SET is_admin = 1 WHERE username = 'yourusername';

-- Run this in phpMyAdmin or MySQL CLI to set up the database

CREATE DATABASE IF NOT EXISTS `voting-system` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `voting-system`;

-- Voters table
CREATE TABLE IF NOT EXISTS voters (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    voter_id   VARCHAR(20) UNIQUE NOT NULL,
    username   VARCHAR(50) UNIQUE NOT NULL,
    email      VARCHAR(100) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Candidates table
CREATE TABLE IF NOT EXISTS candidates (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    party        VARCHAR(100) NOT NULL,
    constituency VARCHAR(100),
    photo        VARCHAR(255),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Votes table
CREATE TABLE IF NOT EXISTS votes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    voter_id     INT NOT NULL,
    candidate_id INT NOT NULL,
    voted_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_voter (voter_id),
    FOREIGN KEY (voter_id)     REFERENCES voters(id)    ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);

-- Sample candidates (optional)
INSERT IGNORE INTO candidates (name, party, constituency) VALUES
('Rahul Sharma',  'National Party',      'Delhi North'),
('Priya Patel',   'People\'s Alliance',  'Mumbai South'),
('Amit Verma',    'Progressive Front',   'Bangalore East'),
('Sunita Rao',    'Democratic Union',    'Chennai Central'),
('Vikram Singh',  'National Party',      'Kolkata West');
