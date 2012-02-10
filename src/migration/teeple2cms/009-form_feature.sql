ALTER TABLE meta_attribute
    ADD require_flg tinyint DEFAULT 0 NOT NULL,
    ADD validation text,
    ADD validation_message text
    ;
ALTER TABLE meta_entity
    ADD api_flg tinyint DEFAULT 0 NOT NULL;
