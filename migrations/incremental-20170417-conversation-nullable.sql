ALTER TABLE fs_conversation MODIFY last_message_id UNSIGNED INT NULL;
UPDATE fs_conversation SET last_message_id = NULL WHERE last_message_id = 0;
