ALTER TABLE meta_entity ADD single_page_flg tinyint;


CREATE TABLE record_url
(
    meta_record_id bigint NOT NULL,
    url varchar(128) NOT NULL,
    page_id bigint NOT NULL,
    create_time datetime NOT NULL,
    timestamp timestamp NOT NULL,
    created_by bigint,
    modified_by bigint,
    delete_flg tinyint DEFAULT 0 NOT NULL,
    delete_time datetime,
    version bigint DEFAULT 1 NOT NULL,
    PRIMARY KEY (meta_record_id)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8;


ALTER TABLE record_url
    ADD FOREIGN KEY (meta_record_id)
    REFERENCES meta_record (id)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
;


ALTER TABLE record_url
    ADD FOREIGN KEY (page_id)
    REFERENCES page (id)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
;

