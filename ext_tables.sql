CREATE TABLE tx_formlog_entries (
	page int(11) unsigned DEFAULT '0' NOT NULL,
	language int(11) unsigned DEFAULT '0' NOT NULL,
	identifier varchar(255) DEFAULT '' NOT NULL,
	data mediumtext,
	finisher_variables mediumtext,
);
