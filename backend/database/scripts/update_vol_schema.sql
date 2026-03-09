-- =============================================================
-- Update Schema: Volunteer Dashboard Support
-- =============================================================

-- 1. Updates for Volunteer Profile
ALTER TABLE users ADD availability_status VARCHAR2(20) DEFAULT 'AVAILABLE';

-- 2. Updates for Claims Status Workflow
-- Current CHECK constraint on claims.status might need updating if it exists
-- Let's add columns for better tracking if not already there
-- We already added picked_up_at, completed_at in previous step.

-- No structural changes needed to food_alerts for now as it mostly tracks the item lifecycle.
-- The detailed tracking happens in the claims/pickup record.

COMMIT;
