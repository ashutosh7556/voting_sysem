-- ============================================================
-- VOTING SYSTEM - FULL MIGRATION
-- Run once against the `voting-system` database
-- ============================================================

USE `voting-system`;

-- ── 1. CONSTITUENCIES ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS constituencies (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    state       VARCHAR(100) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── 2. ELECTIONS ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS elections (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(200) NOT NULL,
    description      TEXT,
    start_date       DATETIME NOT NULL,
    end_date         DATETIME NOT NULL,
    status           ENUM('draft','upcoming','active','completed') DEFAULT 'draft',
    show_results     TINYINT(1) DEFAULT 0,
    constituency_id  INT DEFAULT NULL,
    created_by       INT DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (constituency_id) REFERENCES constituencies(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)      REFERENCES voters(id)         ON DELETE SET NULL
);

-- ── 3. ANNOUNCEMENTS ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS announcements (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    body        TEXT NOT NULL,
    type        ENUM('info','success','warning','danger') DEFAULT 'info',
    is_active   TINYINT(1) DEFAULT 1,
    created_by  INT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES voters(id) ON DELETE SET NULL
);

-- ── 4. ALTER VOTERS ──────────────────────────────────────────
ALTER TABLE voters
    ADD COLUMN full_name        VARCHAR(150) DEFAULT NULL AFTER username,
    ADD COLUMN phone            VARCHAR(20)  DEFAULT NULL AFTER email,
    ADD COLUMN dob              DATE         DEFAULT NULL AFTER phone,
    ADD COLUMN gender           ENUM('male','female','other') DEFAULT NULL AFTER dob,
    ADD COLUMN address          TEXT         DEFAULT NULL AFTER gender,
    ADD COLUMN state            VARCHAR(100) DEFAULT NULL AFTER address,
    ADD COLUMN district         VARCHAR(100) DEFAULT NULL AFTER state,
    ADD COLUMN constituency_id  INT          DEFAULT NULL AFTER district,
    ADD COLUMN photo            VARCHAR(255) DEFAULT NULL AFTER constituency_id,
    ADD COLUMN is_verified      TINYINT(1)   DEFAULT 0   AFTER photo,
    ADD FOREIGN KEY (constituency_id) REFERENCES constituencies(id) ON DELETE SET NULL;

-- ── 5. ALTER CANDIDATES ──────────────────────────────────────
ALTER TABLE candidates
    DROP COLUMN constituency,
    ADD COLUMN party_logo       VARCHAR(255) DEFAULT NULL AFTER party,
    ADD COLUMN symbol           VARCHAR(255) DEFAULT NULL AFTER party_logo,
    ADD COLUMN constituency_id  INT          DEFAULT NULL AFTER symbol,
    ADD COLUMN election_id      INT          DEFAULT NULL AFTER constituency_id,
    ADD COLUMN age              INT          DEFAULT NULL AFTER election_id,
    ADD COLUMN education        VARCHAR(200) DEFAULT NULL AFTER age,
    ADD COLUMN bio              TEXT         DEFAULT NULL AFTER education,
    ADD COLUMN manifesto        TEXT         DEFAULT NULL AFTER bio,
    ADD COLUMN status           ENUM('active','inactive') DEFAULT 'active' AFTER manifesto,
    ADD FOREIGN KEY (constituency_id) REFERENCES constituencies(id) ON DELETE SET NULL,
    ADD FOREIGN KEY (election_id)     REFERENCES elections(id)      ON DELETE SET NULL;

-- ── 6. ALTER VOTES ───────────────────────────────────────────
ALTER TABLE votes
    ADD COLUMN election_id INT NOT NULL AFTER id,
    DROP INDEX unique_voter,
    ADD UNIQUE KEY unique_voter_election (voter_id, election_id),
    ADD FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE;

-- ── 7. UPLOADS DIRECTORY MARKER ──────────────────────────────
-- (directories created via PHP/shell separately)

-- ── 8. SAMPLE CONSTITUENCIES ─────────────────────────────────
INSERT IGNORE INTO constituencies (id, name, state) VALUES
(1, 'Delhi North',      'Delhi'),
(2, 'Mumbai South',     'Maharashtra'),
(3, 'Bangalore East',   'Karnataka'),
(4, 'Chennai Central',  'Tamil Nadu'),
(5, 'Kolkata West',     'West Bengal');

-- ── 9. SAMPLE ELECTION ───────────────────────────────────────
INSERT IGNORE INTO elections (id, title, description, start_date, end_date, status, show_results) VALUES
(1,
 'General Assembly Election 2025',
 'Annual general assembly election for all constituencies.',
 DATE_SUB(NOW(), INTERVAL 1 DAY),
 DATE_ADD(NOW(), INTERVAL 6 DAY),
 'active',
 0
);

-- ── 10. LINK EXISTING CANDIDATES TO ELECTION & CONSTITUENCY ──
UPDATE candidates SET election_id = 1, constituency_id = 1 WHERE id = 1;
UPDATE candidates SET election_id = 1, constituency_id = 2 WHERE id = 2;
UPDATE candidates SET election_id = 1, constituency_id = 3 WHERE id = 3;
UPDATE candidates SET election_id = 1, constituency_id = 4 WHERE id = 4;
UPDATE candidates SET election_id = 1, constituency_id = 5 WHERE id = 5;

-- ── 11. SAMPLE ANNOUNCEMENT ──────────────────────────────────
INSERT IGNORE INTO announcements (title, body, type, is_active) VALUES
('General Assembly Election 2025 is Now Open',
 'Voting is now open for the General Assembly Election 2025. All registered voters can cast their vote until the election closes.',
 'success', 1);
