-- =============================================================
-- link_volunteers_to_ngo.sql
-- Link volunteers to specific NGOs
-- =============================================================

-- Add ngo_id to users table to link volunteers to their respective NGO
ALTER TABLE users ADD belongs_to_ngo_id NUMBER;
ALTER TABLE users ADD CONSTRAINT fk_vol_ngo FOREIGN KEY (belongs_to_ngo_id) REFERENCES users(user_id) ON DELETE SET NULL;

-- Also add specialized status for volunteers like 'AVAILABLE' or 'ON_DELIVERY'
-- But we can use the existing status column or just check active claims.

COMMIT;
