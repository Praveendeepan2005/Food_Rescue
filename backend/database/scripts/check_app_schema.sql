SET LINESIZE 200
SELECT table_name FROM user_tables;
SELECT count(*) FROM food_alerts;
SELECT count(*) FROM claims;
SELECT DISTINCT status FROM food_alerts;
SELECT DISTINCT status FROM claims;
EXIT;
