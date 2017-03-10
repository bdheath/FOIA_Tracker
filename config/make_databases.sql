GRANT SELECT, INSERT, UPDATE, ALTER, INDEX, CREATE, DELETE, DROP
	ON *.* TO 'nobody'@'localhost';

CREATE DATABASE IF NOT EXISTS newsnet;

CREATE TABLE IF NOT EXISTS newsnet.logs (
  id int(11) NOT NULL auto_increment,
  user varchar(50) default NULL,
  action varchar(30) default NULL,
  action_time timestamp(14) NOT NULL,
  success int(11) default NULL,
  page varchar(255) default NULL,
  session_id varchar(255) default NULL,
  PRIMARY KEY  (id),
  KEY user_idx(user),
  KEY action_idx(action)
) TYPE=MyISAM;

CREATE TABLE newsnet.sessions (
  id int(11) NOT NULL auto_increment,
  user varchar(255) default NULL,
  session_id varchar(100) default NULL,
  expires timestamp(14) NOT NULL,
  auth varchar(50) default NULL,
  usernum int(11) default NULL,
  strikes double default NULL,
  blocked int(4) default NULL,
  PRIMARY KEY  (id),
  KEY user_idx (user),
  KEY session_id_idx (session_id),
  KEY expires (expires),
  KEY usernum_idx (usernum)
) TYPE=MyISAM;

CREATE TABLE newsnet.users (
  ID int(4) NOT NULL auto_increment,
  username varchar(20) default NULL,
  password varchar(50) default NULL,
  real_name varchar(50) default NULL,
  editor_name varchar(50) default NULL,
  projects_database varchar(20) default NULL,
  last_signed_on datetime default NULL,
  signed_on_before int(4) default '0',
  write_system_messages int(4) default '0',
  administrator int(4) default '0',
  view_all_projects int(4) default '0',
  view_databases int(4) default '0',
  first_signed_on datetime default NULL,
  actionquery int(11) default NULL,
  blocked int(11) default NULL,
  group1 varchar(20) default NULL,
  group2 varchar(30) default NULL,
  editor int(11) default NULL,
  show_foia_type int(4) default NULL,
  show_foia_order int(4) default NULL,
  main_topic varchar(50) default NULL,
  main_topic_url varchar(255) default NULL,
  show_briefing int(11) default NULL,
  show_foias int(11) default NULL,
  show_projects int(11) default NULL,
  create_databases int(11) default NULL,
  PRIMARY KEY  (ID),
  UNIQUE KEY PrimaryKey (ID),
  KEY username_idx (username),
  KEY real_name_idx (real_name),
  KEY editor_idx (editor)
) TYPE=MyISAM;

INSERT INTO newsnet.users(username,password,administrator) VALUES("admin",PASSWORD("ADMIN"),1);

CREATE DATABASE IF NOT EXISTS foia;

USE foia;

CREATE TABLE IF NOT EXISTS foia.requests(
	foia_id INT NOT NULL AUTO_INCREMENT,
	owner VARCHAR(255),
	title VARCHAR(255),
	agency VARCHAR(255),
	date_filed DATE,
	date_entered DATETIME,
	date_changed TIMESTAMP,
	date_acknowledged DATE,
	date_received DATE,
	date_closed DATE,
	status_id INT,
	records_sought TEXT,
	notes TEXT,
	contact_name VARCHAR(255),
	contact_number VARCHAR(255),
	fee_initial DOUBLE,
	fee_charged DOUBLE,
	fee_paid DOUBLE,
	fee_method VARCHAR(255),
	PRIMARY KEY(foia_id),
	KEY date_filed_idx(date_filed),
	KEY date_entered_idx(date_entered),
	KEY status_id_idx(status_id),
	KEY title_idx(title),
	KEY agency_idx(agency),
	FULLTEXT KEY all_ftidx(owner,title,agency,records_sought,notes,
		contact_name, contact_number),
	FULLTEXT KEY title_ftidx(title)
);

DROP TABLE IF EXISTS foia.status;
CREATE TABLE IF NOT EXISTS foia.status(
	status_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	status VARCHAR(255),
	KEY status_idx(status)
);

CREATE TABLE IF NOT EXISTS foia.reminders(
	reminder_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	foia_id INT,
	reminder_date DATETIME,
	reminder_text VARCHAR(255),
	KEY foia_id_idx(foia_id),
	KEY reminder_date_idx(reminder_date),
	KEY reminder_text_idx(reminder_text)
);

CREATE TABLE IF NOT EXISTS foia.buckets(
	bucket_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	bucket_name VARCHAR(255),
	bucket_owner VARCHAR(255),
	KEY bucket_owner_idx(bucket_owner)
);

CREATE TABLE IF NOT EXISTS foia.buckets_contents(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	bucket_id INT,
	KEY bucket_id_idx(bucket_id),
	foia_id INT,
	KEY foia_id_idx(foia_id)
);

INSERT INTO foia.status(status)
	VALUES("Pending"),
		("Pending - Awaiting Appeal"),
		("Pending - Possible Litigation"),
		("Pending - In Litigation"),
		("Closed - Approved"),
		("Closed - Denied"),
		("Closed - Partially Approved"),
		("Abandoned"),
		("Other")
;

CREATE TABLE IF NOT EXISTS foia.alerts(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	foia_id INT,
	alert_owner VARCHAR(255),
	alert_date DATE,
	alert_text TEXT,
	alert_cleared INT,
	date_cleared DATE,
	date_created DATE,
	KEY foia_id_idx(foia_id),
	KEY alert_owner_idx(alert_owner),
	KEY alert_date_idx(alert_date)
);

ALTER TABLE foia.requests
	ADD deleted INT,
	ADD KEY deleted_idx(deleted)
;

CREATE TABLE foia.requests_versions
	SELECT *
	FROM foia.requests
	LIMIT 0
;

ALTER TABLE foia.requests_versions
	ADD version_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	ADD KEY foia_id_idx(foia_id)
;

CREATE TABLE IF NOT EXISTS foia.user_prefs(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	user VARCHAR(255),
	KEY user_idx(user),
	email VARCHAR(255),
	name VARCHAR(255),
	address VARCHAR(255),
	city VARCHAR(255),
	state VARCHAR(255),
	zip VARCHAR(255),
	phone VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS foia.prefs(
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	version VARCHAR(25),
	urlstem VARCHAR(255)
);

INSERT INTO foia.prefs(version,urlstem)
	VALUES("1.0.1","");

CREATE TABLE IF NOT EXISTS foia.templates(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255),
	filename VARCHAR(255),
	KEY name_idx(name)
);
INSERT INTO foia.templates(name, filename) VALUES("Federal Agencies", "federal.txt");

CREATE TABLE IF NOT EXISTS foia.letters(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	foia_id INT,
	KEY foia_id_idx(foia_id),
	owner VARCHAR(255),
	KEY owner_idx(owner),
	address TEXT,
	greeting VARCHAR(255),
	records TEXT,
	letter TEXT,
	FULLTEXT KEY to_idx(address),
	KEY greeting_idx(greeting),
	FULLTEXT KEY letter_idx(letter),
	FULLTEXT KEY all_ftidx(address, greeting, records, letter),
	template_id INT
);


CREATE TABLE foia.documents_pages(
	pid INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	document_id INT,
	page_id INT,
	item LONGTEXT,
	KEY document_id_idx(document_id),
	KEY page_id_idx(page_id),
	FULLTEXT KEY item_ftidx(item)
) ENGINE=MyISAM, CHARACTER SET=latin1, COLLATE=latin1_general_ci
;

CREATE TABLE IF NOT EXISTS foia.notes(
	note_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	added DATETIME,
	modified TIMESTAMP,
	request_id INT NOT NULL,
	KEY request_id_idx(request_id),
	owner VARCHAR(255),
	note LONGTEXT,
	KEY all_ftidx(note,owner)
) ENGINE=MyISAM, CHARACTER SET=latin1, COLLATE=latin1_general_ci
;