-- =============================================================
-- FOOD RESCUE SYSTEM - Oracle XE 21c Database Setup Script
-- Run this script in SQL*Plus or Oracle SQL Developer
-- =============================================================

-- Drop tables if they already exist (for clean re-run)
BEGIN
  EXECUTE IMMEDIATE 'DROP TABLE claims CASCADE CONSTRAINTS';
  EXCEPTION WHEN OTHERS THEN NULL;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'DROP TABLE food_alerts CASCADE CONSTRAINTS';
  EXCEPTION WHEN OTHERS THEN NULL;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'DROP TABLE users CASCADE CONSTRAINTS';
  EXCEPTION WHEN OTHERS THEN NULL;
END;
/

-- =============================================================
-- 1. USERS TABLE
-- =============================================================
CREATE TABLE users (
    user_id      NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name         VARCHAR2(100)  NOT NULL,
    email        VARCHAR2(100)  NOT NULL UNIQUE,
    password     VARCHAR2(255)  NOT NULL,
    role         VARCHAR2(20)   NOT NULL CHECK (role IN ('DONOR', 'NGO', 'VOLUNTEER', 'ADMIN')),
    phone        VARCHAR2(15),
    latitude     NUMBER(10, 7),
    longitude    NUMBER(10, 7),
    device_token VARCHAR2(255),
    status       VARCHAR2(20)   DEFAULT 'ACTIVE' NOT NULL CHECK (status IN ('ACTIVE', 'SUSPENDED')),
    created_at   DATE DEFAULT SYSDATE
);

-- Index on email for fast login lookups
CREATE INDEX idx_users_email ON users(email);
-- Index on role for role-based filtering
CREATE INDEX idx_users_role  ON users(role);

-- =============================================================
-- 2. FOOD_ALERTS TABLE
-- =============================================================
CREATE TABLE food_alerts (
    alert_id    NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    donor_id    NUMBER         NOT NULL,
    food_type   VARCHAR2(100)  NOT NULL,
    quantity    VARCHAR2(50)   NOT NULL,
    expiry_time DATE           NOT NULL,
    latitude    NUMBER(10, 7)  NOT NULL,
    longitude   NUMBER(10, 7)  NOT NULL,
    status      VARCHAR2(20)   DEFAULT 'AVAILABLE' NOT NULL
                    CHECK (status IN ('AVAILABLE', 'CLAIMED', 'COMPLETED', 'EXPIRED')),
    created_at  DATE DEFAULT SYSDATE,
    -- Foreign Key
    CONSTRAINT fk_alert_donor FOREIGN KEY (donor_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Index for nearby alert queries (lat/lon)
CREATE INDEX idx_food_alerts_location ON food_alerts(latitude, longitude);
-- Index for status filtering
CREATE INDEX idx_food_alerts_status   ON food_alerts(status);
-- Index for expiry time (for auto-expire jobs)
CREATE INDEX idx_food_alerts_expiry   ON food_alerts(expiry_time);

-- =============================================================
-- 3. CLAIMS TABLE
-- =============================================================
CREATE TABLE claims (
    claim_id     NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    alert_id     NUMBER       NOT NULL,
    volunteer_id NUMBER       NOT NULL,
    claimed_at   DATE DEFAULT SYSDATE,
    status       VARCHAR2(20) DEFAULT 'ACTIVE' NOT NULL
                     CHECK (status IN ('ACTIVE', 'COMPLETED', 'CANCELLED')),
    -- Foreign Keys
    CONSTRAINT fk_claim_alert     FOREIGN KEY (alert_id)     REFERENCES food_alerts(alert_id) ON DELETE CASCADE,
    CONSTRAINT fk_claim_volunteer FOREIGN KEY (volunteer_id) REFERENCES users(user_id)         ON DELETE CASCADE,
    -- Prevent double-claiming the same alert by the same user
    CONSTRAINT uq_claim_alert_volunteer UNIQUE (alert_id, volunteer_id)
);

-- Index for fast lookup
CREATE INDEX idx_claims_alert_id     ON claims(alert_id);
CREATE INDEX idx_claims_volunteer_id ON claims(volunteer_id);

-- =============================================================
-- SAMPLE DATA (Optional - for testing)
-- =============================================================
-- Password for all test users is: Test@1234
-- BCrypt hash of "Test@1234" (generated via PHP password_hash)
-- NOTE: Replace hashes below with real PHP-generated hashes if needed

INSERT INTO users (name, email, password, role, phone)
VALUES ('System Admin', 'admin@test.com',
        '$2y$10$aHogUd4Avjf4F/l5G40CcOQv3d9ySfT8GtzO8X4Pj5CU3168t0Ewi',
        'ADMIN', '0000000000');

INSERT INTO users (name, email, password, role, phone, latitude, longitude)
VALUES ('John Donor', 'donor@test.com',
        '$2y$10$aHogUd4Avjf4F/l5G40CcOQv3d9ySfT8GtzO8X4Pj5CU3168t0Ewi',
        'DONOR', '9876543210', 12.971599, 77.594566);

INSERT INTO users (name, email, password, role, phone, latitude, longitude)
VALUES ('Help NGO', 'ngo@test.com',
        '$2y$10$aHogUd4Avjf4F/l5G40CcOQv3d9ySfT8GtzO8X4Pj5CU3168t0Ewi',
        'NGO', '9123456780', 12.972442, 77.580643);

INSERT INTO users (name, email, password, role, phone, latitude, longitude)
VALUES ('Alice Volunteer', 'volunteer@test.com',
        '$2y$10$aHogUd4Avjf4F/l5G40CcOQv3d9ySfT8GtzO8X4Pj5CU3168t0Ewi',
        'VOLUNTEER', '9988776655', 12.968512, 77.601000);

INSERT INTO food_alerts (donor_id, food_type, quantity, expiry_time, latitude, longitude, status)
VALUES (1, 'Rice and Dal', '10 kg', SYSDATE + 1/24, 12.971599, 77.594566, 'AVAILABLE');

INSERT INTO food_alerts (donor_id, food_type, quantity, expiry_time, latitude, longitude, status)
VALUES (1, 'Bread Loaves', '20 pieces', SYSDATE + 2/24, 12.971599, 77.594566, 'AVAILABLE');

COMMIT;

-- =============================================================
-- AUTO-EXPIRE ALERTS: Database Scheduled Job (Optional)
-- Marks alerts as EXPIRED if expiry_time has passed
-- Run this to create an Oracle scheduler job
-- =============================================================
BEGIN
    DBMS_SCHEDULER.CREATE_JOB(
        job_name        => 'EXPIRE_FOOD_ALERTS',
        job_type        => 'PLSQL_BLOCK',
        job_action      => 'BEGIN
                               UPDATE food_alerts
                               SET status = ''EXPIRED''
                               WHERE status = ''AVAILABLE''
                                 AND expiry_time < SYSDATE;
                               COMMIT;
                            END;',
        start_date      => SYSTIMESTAMP,
        repeat_interval => 'FREQ=MINUTELY;INTERVAL=5',
        enabled         => TRUE,
        comments        => 'Auto-expire food alerts past their expiry time'
    );
END;
/

-- =============================================================
-- Verify Tables
-- =============================================================
SELECT table_name FROM user_tables WHERE table_name IN ('USERS','FOOD_ALERTS','CLAIMS');
SELECT * FROM users;
SELECT * FROM food_alerts;


