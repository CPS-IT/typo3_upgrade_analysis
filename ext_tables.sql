#
# Table structure for table 'tx_typo3upgradeanalysis_domain_model_analysis'
#
CREATE TABLE tx_typo3upgradeanalysis_domain_model_analysis (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	lines_of_code int(11) unsigned DEFAULT '0' NOT NULL,
	php_warnings int(11) unsigned DEFAULT '0' NOT NULL,
	php_errors int(11) unsigned DEFAULT '0' NOT NULL,
	extension_scan_strong_braking int(11) unsigned DEFAULT '0' NOT NULL,
	extension_scan_weak_braking int(11) unsigned DEFAULT '0' NOT NULL,
	extension_scan_strong_deprecated int(11) unsigned DEFAULT '0' NOT NULL,
	extension_scan_weak_deprecated int(11) unsigned DEFAULT '0' NOT NULL,
	category int(11) unsigned DEFAULT '0' NOT NULL,
	compatible_version int(11) unsigned DEFAULT '0' NOT NULL,
	ext_key varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)
);