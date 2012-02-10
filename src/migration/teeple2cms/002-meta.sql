CREATE TABLE meta_attribute
(
    id bigint NOT NULL AUTO_INCREMENT,
    meta_entity_id bigint NOT NULL,
    pname varchar(32) NOT NULL,
    label varchar(128) NOT NULL,
    seq tinyint NOT NULL,
    data_type tinyint NOT NULL,
    options text,
    create_time datetime NOT NULL,
    timestamp timestamp NOT NULL,
    created_by bigint,
    modified_by bigint,
    delete_flg tinyint DEFAULT 0 NOT NULL,
    delete_time datetime,
    version bigint DEFAULT 1 NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (meta_entity_id, pname)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8;


CREATE TABLE meta_entity
(
    id bigint NOT NULL AUTO_INCREMENT,
    pname varchar(32) NOT NULL UNIQUE,
    label varchar(128) NOT NULL,
    create_time datetime NOT NULL,
    timestamp timestamp NOT NULL,
    created_by bigint,
    modified_by bigint,
    delete_flg tinyint DEFAULT 0 NOT NULL,
    delete_time datetime,
    version bigint DEFAULT 1 NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8;


CREATE TABLE meta_record
(
    id bigint NOT NULL AUTO_INCREMENT,
    meta_entity_id bigint NOT NULL,
    publish_flg tinyint DEFAULT 1 NOT NULL,
    publish_start_dt datetime,
    publish_end_dt datetime,
    create_time datetime NOT NULL,
    timestamp timestamp NOT NULL,
    created_by bigint,
    modified_by bigint,
    delete_flg tinyint DEFAULT 0 NOT NULL,
    delete_time datetime,
    version bigint DEFAULT 1 NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8;


CREATE TABLE meta_value
(
    id bigint NOT NULL AUTO_INCREMENT,
    meta_record_id bigint NOT NULL,
    meta_attribute_id bigint NOT NULL,
    value text,
    create_time datetime NOT NULL,
    timestamp timestamp NOT NULL,
    created_by bigint,
    modified_by bigint,
    delete_flg tinyint DEFAULT 0 NOT NULL,
    delete_time datetime,
    version bigint DEFAULT 1 NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (meta_record_id, meta_attribute_id)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8;


CREATE TABLE page
(
    id bigint NOT NULL AUTO_INCREMENT,
    url varchar(128) NOT NULL UNIQUE,
    meta_entity_id bigint NOT NULL,
    template_path text NOT NULL,
    page_type tinyint NOT NULL,
    create_time datetime NOT NULL,
    timestamp timestamp NOT NULL,
    created_by bigint,
    modified_by bigint,
    delete_flg tinyint DEFAULT 0 NOT NULL,
    delete_time datetime,
    version bigint DEFAULT 1 NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8;



/* Create Foreign Keys */

ALTER TABLE meta_value
    ADD FOREIGN KEY (meta_attribute_id)
    REFERENCES meta_attribute (id)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
;


ALTER TABLE meta_attribute
    ADD FOREIGN KEY (meta_entity_id)
    REFERENCES meta_entity (id)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
;


ALTER TABLE meta_record
    ADD FOREIGN KEY (meta_entity_id)
    REFERENCES meta_entity (id)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
;


ALTER TABLE page
    ADD FOREIGN KEY (meta_entity_id)
    REFERENCES meta_entity (id)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
;


ALTER TABLE meta_value
    ADD FOREIGN KEY (meta_record_id)
    REFERENCES meta_record (id)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
;
