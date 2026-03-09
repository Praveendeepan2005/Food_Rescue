ALTER TABLE reward_history ADD claim_id NUMBER;
ALTER TABLE reward_history ADD CONSTRAINT fk_reward_claim FOREIGN KEY (claim_id) REFERENCES claims(claim_id);
COMMIT;
EXIT;
