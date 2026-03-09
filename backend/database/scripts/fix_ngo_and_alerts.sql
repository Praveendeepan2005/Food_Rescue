-- 1. Fix NGO City
UPDATE users SET city = 'Coimbatore' WHERE user_id = 6;

-- 2. Link PENDING Coimbatore donations to NGO 6
UPDATE food_alerts 
SET status = 'NGO_ASSIGNED', assigned_ngo_id = 6 
WHERE status = 'PENDING' AND LOWER(city) = 'coimbatore';

COMMIT;
EXIT;
