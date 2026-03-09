-- =============================================================
-- Update Schema: Volunteer Rewards and Points System
-- =============================================================

-- 1. Add points and impact tracking to users
ALTER TABLE users ADD total_points NUMBER DEFAULT 0;
ALTER TABLE users ADD current_streak NUMBER DEFAULT 0;
ALTER TABLE users ADD last_completion_date DATE;

-- 2. Table for tracking individual reward transactions (Points History)
CREATE TABLE reward_history (
    reward_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id NUMBER NOT NULL,
    points_awarded NUMBER NOT NULL,
    reason VARCHAR2(100),
    awarded_at DATE DEFAULT SYSDATE,
    CONSTRAINT fk_reward_user FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- 3. Table for Daily Goals / Completion Status
CREATE TABLE daily_goals (
    goal_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id NUMBER NOT NULL,
    goal_date DATE DEFAULT TRUNC(SYSDATE),
    rescues_completed NUMBER DEFAULT 0,
    daily_target NUMBER DEFAULT 3, -- Default goal of 3 rescues per day
    CONSTRAINT fk_goal_user FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT uk_user_date UNIQUE (user_id, goal_date)
);

COMMIT;
