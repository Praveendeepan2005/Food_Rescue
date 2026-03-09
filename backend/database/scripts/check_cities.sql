SET LINESIZE 200
COL name FOR A20
COL city FOR A20
SELECT user_id, name, city, role FROM users WHERE user_id = 6;
SELECT alert_id, food_type, city, status FROM food_alerts;
EXIT;
