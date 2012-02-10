

/* Create Tables */

CREATE TABLE login_account
(
	id bigint NOT NULL AUTO_INCREMENT,
	login_id varchar(64) NOT NULL UNIQUE,
	login_pw varchar(64) NOT NULL,
	name varchar(256) NOT NULL,
	role varchar(16) NOT NULL,
	pw_change_date date DEFAULT '2010-03-23' NOT NULL,
	lock_flg tinyint DEFAULT 0 NOT NULL,
	pw_fail_num tinyint DEFAULT 0 NOT NULL,
	null datetime NOT NULL,
	null timestamp NOT NULL,
	null bigint,
	null bigint,
	null tinyint DEFAULT 0 NOT NULL,
	null datetime,
	null bigint DEFAULT 1 NOT NULL,
	PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8;


CREATE TABLE meta_attribute
(
	id bigint NOT NULL AUTO_INCREMENT,
	meta_entity_id bigint NOT NULL,
	pname varchar(32) NOT NULL,
	label varchar(128) NOT NULL,
	seq tinyint NOT NULL,
	data_type tinyint NOT NULL,
	options text,
	list_flg tinyint DEFAULT 0 NOT NULL,
	require_flg tinyint DEFAULT 0 NOT NULL,
	validation text,
	validation_message text,
	ref_entity_id bigint,
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
	list_control text,
	api_flg tinyint DEFAULT 0 NOT NULL,
	create_time datetime NOT NULL,
	timestamp timestamp NOT NULL,
	created_by bigint,
	modified_by bigint,
	delete_flg tinyint DEFAULT 0 NOT NULL,
	delete_time datetime,
	version bigint DEFAULT 1 NOT NULL,
	hide_flg tinyint DEFAULT 0 NOT NULL,
	seq tinyint,
	order_by varchar(128),
	exclude_search_flg tinyint DEFAULT 0 NOT NULL,
	PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8;


CREATE TABLE meta_record
(
	id bigint NOT NULL AUTO_INCREMENT,
	meta_entity_id bigint NOT NULL,
	publish_flg tinyint DEFAULT 1 NOT NULL,
	publish_start_dt datetime,
	publish_end_dt datetime,
	seq tinyint,
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
	name varchar(128) NOT NULL,
	url varchar(128) NOT NULL UNIQUE,
	meta_entity_id bigint NOT NULL,
	template_path text NOT NULL,
	page_type tinyint NOT NULL,
	filter text,
	publish_flg tinyint DEFAULT 1 NOT NULL,
	page_limit int,
	encoding varchar(16) DEFAULT 'UTF-8',
	notify_email varchar(128),
	default_publish_flg tinyint,
	mobile_flg tinyint,
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



