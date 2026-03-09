SET LONG 2000;
SELECT search_condition FROM user_constraints WHERE table_name = 'FOOD_ALERTS' AND search_condition LIKE '%status%';
EXIT;
