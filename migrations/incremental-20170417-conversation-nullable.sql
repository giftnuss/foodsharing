ALTER TABLE fs_conversation
  MODIFY COLUMN last_message_id INTEGER UNSIGNED NULL;
UPDATE fs_conversation
SET last_message_id = NULL
WHERE last_message_id = 0;
