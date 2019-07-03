INSERT INTO fs_mailbox (id, name) SELECT mailbox_id, CONCAT("region-", id) FROM fs_bezirk WHERE mailbox_id <> 0;
