SET LINESIZE 200
COLUMN column_name FORMAT A30
SELECT column_name, data_type FROM user_tab_columns WHERE table_name = 'USERS' ORDER BY column_id;
EXIT;
