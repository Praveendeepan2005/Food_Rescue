-- Add vehicle_type to users table
ALTER TABLE users ADD vehicle_type VARCHAR2(100);
COMMIT;
EXIT;
