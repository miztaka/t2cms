
CREATE TABLE login_account
(
	id bigint NOT NULL AUTO_INCREMENT,
	login_id varchar(64) NOT NULL UNIQUE,
	login_pw varchar(64) NOT NULL,
	email varchar(256) NOT NULL,
	name varchar(256) NOT NULL,
	role varchar(16) NOT NULL,
	pw_change_date date DEFAULT '2010-03-23' NOT NULL,
	lock_flg tinyint DEFAULT 0 NOT NULL,
	pw_fail_num tinyint DEFAULT 0 NOT NULL,
	create_time datetime NOT NULL,
	timestamp timestamp NOT NULL,
	delete_time datetime,
	delete_flg char(1) DEFAULT '0' NOT NULL,
	created_by bigint,
	modified_by bigint,
	PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8;

INSERT INTO login_account (
    login_id, login_pw, email, name, role, create_time
) VALUES (
    '1111','hogehoge','miztaka@gmail.com','佐藤','admin',now()
);



