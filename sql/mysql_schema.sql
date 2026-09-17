-- ============================================================
-- VOTING SYSTEM — MYSQL SCHEMA (local / WAMP)
-- Run once in phpMyAdmin or: mysql -u root -p < sql/mysql_schema.sql
-- Re-runnable: every statement is guarded.
--
-- Column-for-column equivalent of sql/postgres_schema.sql, so the same
-- PHP runs against either engine. Flags are TINYINT(1) holding 0/1 —
-- see the design note in the Postgres file for why they are not BOOLEAN.
--
-- Supersedes database_setup.sql + migration.sql, which assumed an older
-- table layout and used non-idempotent ALTER TABLE ... ADD COLUMN.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `voting-system`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `voting-system`;

-- ── 1. CONSTITUENCIES ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS constituencies (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    state       VARCHAR(100) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── 2. VOTERS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS voters (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    voter_id        VARCHAR(20)  NOT NULL UNIQUE,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    full_name       VARCHAR(150) DEFAULT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    phone           VARCHAR(20)  DEFAULT NULL,
    dob             DATE         DEFAULT NULL,
    gender          ENUM('male','female','other') DEFAULT NULL,
    address         TEXT,
    state           VARCHAR(100) DEFAULT NULL,
    district        VARCHAR(100) DEFAULT NULL,
    constituency_id INT          DEFAULT NULL,
    photo           VARCHAR(255) DEFAULT NULL,
    is_verified     TINYINT(1)   NOT NULL DEFAULT 0,
    is_admin        TINYINT(1)   NOT NULL DEFAULT 0,
    password        VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_voters_constituency (constituency_id),
    CONSTRAINT fk_voters_constituency FOREIGN KEY (constituency_id)
        REFERENCES constituencies(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── 3. ELECTIONS ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS elections (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    start_date      DATETIME NOT NULL,
    end_date        DATETIME NOT NULL,
    status          ENUM('draft','upcoming','active','completed') NOT NULL DEFAULT 'draft',
    show_results    TINYINT(1) NOT NULL DEFAULT 0,
    constituency_id INT DEFAULT NULL,
    created_by      INT DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_elections_status_window (status, start_date, end_date),
    CONSTRAINT fk_elections_constituency FOREIGN KEY (constituency_id)
        REFERENCES constituencies(id) ON DELETE SET NULL,
    CONSTRAINT fk_elections_creator FOREIGN KEY (created_by)
        REFERENCES voters(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── 4. CANDIDATES ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS candidates (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    party           VARCHAR(100) NOT NULL,
    party_logo      VARCHAR(255) DEFAULT NULL,
    symbol          VARCHAR(255) DEFAULT NULL,
    constituency_id INT DEFAULT NULL,
    election_id     INT DEFAULT NULL,
    age             INT DEFAULT NULL,
    education       VARCHAR(200) DEFAULT NULL,
    bio             TEXT,
    manifesto       TEXT,
    status          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    photo           VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_candidates_election (election_id),
    KEY idx_candidates_constituency (constituency_id),
    KEY idx_candidates_status (status),
    CONSTRAINT fk_candidates_constituency FOREIGN KEY (constituency_id)
        REFERENCES constituencies(id) ON DELETE SET NULL,
    CONSTRAINT fk_candidates_election FOREIGN KEY (election_id)
        REFERENCES elections(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── 5. VOTES ─────────────────────────────────────────────────
-- The UNIQUE key is load-bearing: vote.php relies on catching the
-- resulting PDOException to block a double vote under a race.
CREATE TABLE IF NOT EXISTS votes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    election_id  INT NOT NULL,
    voter_id     INT NOT NULL,
    candidate_id INT NOT NULL,
    voted_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_voter_election (voter_id, election_id),
    KEY idx_votes_candidate_election (candidate_id, election_id),
    KEY idx_votes_election (election_id),
    CONSTRAINT fk_votes_election FOREIGN KEY (election_id)
        REFERENCES elections(id) ON DELETE CASCADE,
    CONSTRAINT fk_votes_voter FOREIGN KEY (voter_id)
        REFERENCES voters(id) ON DELETE CASCADE,
    CONSTRAINT fk_votes_candidate FOREIGN KEY (candidate_id)
        REFERENCES candidates(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 6. ANNOUNCEMENTS ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS announcements (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(200) NOT NULL,
    body       TEXT NOT NULL,
    type       ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_announcements_active (is_active, created_at),
    CONSTRAINT fk_announcements_creator FOREIGN KEY (created_by)
        REFERENCES voters(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── 7. SEED: CONSTITUENCIES ──────────────────────────────────
INSERT IGNORE INTO constituencies (id, name, state) VALUES
    (1, 'Delhi North',     'Delhi'),
    (2, 'Mumbai South',    'Maharashtra'),
    (3, 'Bangalore East',  'Karnataka'),
    (4, 'Chennai Central', 'Tamil Nadu'),
    (5, 'Kolkata West',    'West Bengal');

-- ── 8. SEED: SAMPLE ELECTION ─────────────────────────────────
INSERT IGNORE INTO elections (id, title, description, start_date, end_date, status, show_results) VALUES
    (1,
     'General Assembly Election 2025',
     'Annual general assembly election for all constituencies.',
     DATE_SUB(NOW(), INTERVAL 1 DAY),
     DATE_ADD(NOW(), INTERVAL 6 DAY),
     'active',
     0);

-- ── 9. SEED: SAMPLE CANDIDATES ───────────────────────────────
INSERT INTO candidates (name, party, constituency_id, election_id, status)
SELECT * FROM (
    SELECT 'Rahul Sharma' AS n, 'National Party'    AS p, 1 AS c, 1 AS e, 'active' AS s UNION ALL
    SELECT 'Priya Patel',       'People\'s Alliance',    2,      1,      'active'        UNION ALL
    SELECT 'Amit Verma',        'Progressive Front',     3,      1,      'active'        UNION ALL
    SELECT 'Sunita Rao',        'Democratic Union',      4,      1,      'active'        UNION ALL
    SELECT 'Vikram Singh',      'National Party',        5,      1,      'active'
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM (SELECT name, election_id FROM candidates) AS existing
    WHERE existing.name = seed.n AND existing.election_id = seed.e
);

-- ── 10. SEED: SAMPLE ANNOUNCEMENT ────────────────────────────
INSERT INTO announcements (title, body, type, is_active)
SELECT 'General Assembly Election 2025 is Now Open',
       'Voting is now open for the General Assembly Election 2025. All registered voters can cast their vote until the election closes.',
       'success', 1
FROM (SELECT 1) AS dummy
WHERE NOT EXISTS (
    SELECT 1 FROM (SELECT title FROM announcements) AS existing
    WHERE existing.title = 'General Assembly Election 2025 is Now Open'
);

-- ============================================================
-- AFTER THIS SCRIPT: create your admin account.
-- 1. Register normally through the site's register.php form.
-- 2. Then run, with your username:
--      UPDATE voters SET is_admin = 1, is_verified = 1 WHERE username = 'yourusername';
-- Do not insert an admin row by hand — the password column must hold a
-- PHP password_hash() value, which only the register form produces.
-- ============================================================
