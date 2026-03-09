-- Link volunteers 4 and 7 to NGO 6
UPDATE users SET belongs_to_ngo_id = 6 WHERE user_id IN (4, 7);
COMMIT;
EXIT;
