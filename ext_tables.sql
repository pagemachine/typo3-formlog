#
# Table structure for table 'tx_formlog_entries'
#
CREATE TABLE tx_formlog_entries (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,

  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  deleted int(11) unsigned DEFAULT '0' NOT NULL,

  language int(11) unsigned DEFAULT '0' NOT NULL,
  identifier varchar(255) DEFAULT '' NOT NULL,
  data mediumtext,
  finisher_variables mediumtext,

  PRIMARY KEY (uid),
  KEY parent (pid)
);
