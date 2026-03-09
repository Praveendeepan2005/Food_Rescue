-- =============================================================
-- Update Schema: NGO Dashboard Support
-- =============================================================

-- 1. Updates for NGO/Volunteer Profile
ALTER TABLE users ADD org_reg_num VARCHAR2(50);

-- 2. Updates for Food Alerts (assigned_ngo_id)
-- This tracks which NGO accepted the donation
ALTER TABLE food_alerts ADD assigned_ngo_id NUMBER;
ALTER TABLE food_alerts ADD CONSTRAINT fk_alert_ngo FOREIGN KEY (assigned_ngo_id) REFERENCES users(user_id) ON DELETE SET NULL;

-- 3. Updates for Claims (to link to volunteers specifically)
-- The existing claims table already handles volunteer_id, but let's ensure it has picked_up_at
ALTER TABLE claims ADD picked_up_at DATE;
ALTER TABLE claims ADD completed_at DATE;

COMMIT;
