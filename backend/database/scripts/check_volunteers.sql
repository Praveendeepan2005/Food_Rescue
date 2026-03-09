SET LINESIZE 200
COL name FOR A20
COL email FOR A30
COL role FOR A10
DESC users;
SELECT user_id, name, email, role, belongs_to_ngo_id, status FROM users WHERE role = 'VOLUNTEER';
SELECT user_id, name, email, role, belongs_to_ngo_id, status FROM users WHERE belongs_to_ngo_id = 6;
EXIT;
