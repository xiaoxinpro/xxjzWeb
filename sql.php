<?php
// COLUMN_NAME：表名
// COLUMN_TYPE：类型（varchar(64)）
// IS_NULLABLE：是否允许为空（NO、YES）
// COLUMN_DEFAULT：默认值（NULL、值）
// EXTRA：自增（auto_increment）
// COLUMN_KEY：主键（PRI）
// ORDINAL_POSITION：顺序值（从1起始递增）
return array(
	'account' => array(
		'tableName'  => "account",
		'tableField' => array(
			"acid" => array('COLUMN_NAME'=>"acid",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"NOT NULL",'EXTRA'=>"AUTO_INCREMENT"),
			"acmoney" => array('COLUMN_NAME'=>"acmoney",'COLUMN_TYPE'=>"double(9,2) unsigned",'IS_NULLABLE'=>"NOT NULL"),
			"acclassid" => array('COLUMN_NAME'=>"acclassid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
			"actime" => array('COLUMN_NAME'=>"actime",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
			"acremark" => array('COLUMN_NAME'=>"acremark",'COLUMN_TYPE'=>"varchar(255)",'IS_NULLABLE'=>"NOT NULL"),
			"jiid" => array('COLUMN_NAME'=>"jiid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
			"zhifu" => array('COLUMN_NAME'=>"zhifu",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
			"fid" => array('COLUMN_NAME'=>"fid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL",'COLUMN_DEFAULT'=>"DEFAULT '-1'"),
		),
		'tableKey' => "acid",
		'tableOther' => "ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;"
	),

	'account_class' => array(
		'tableName'  => "account_class",
		'tableField' => array(
			"classid" => array('COLUMN_NAME'=>"classid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL",'EXTRA'=>"AUTO_INCREMENT"),
			"classname" => array('COLUMN_NAME'=>"classname",'COLUMN_TYPE'=>"varchar(255)",'IS_NULLABLE'=>"NOT NULL"),
			"classtype" => array('COLUMN_NAME'=>"classtype",'COLUMN_TYPE'=>"int(1)",'IS_NULLABLE'=>"NOT NULL"),
			"ufid" => array('COLUMN_NAME'=>"ufid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
			"sort" => array('COLUMN_NAME'=>"sort",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL",'COLUMN_DEFAULT'=>"DEFAULT '255'"),
		),
		'tableKey' => "classid",
		'tableOther' => "ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;"
	),

	'account_funds' => array(
		'tableName'  => "account_funds",
		'tableField' => array(
			"fundsid" => array('COLUMN_NAME'=>"fundsid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL",'EXTRA'=>"AUTO_INCREMENT"),
			"fundsname" => array('COLUMN_NAME'=>"fundsname",'COLUMN_TYPE'=>"varchar(255)",'IS_NULLABLE'=>"NOT NULL"),
			"uid" => array('COLUMN_NAME'=>"uid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
			"sort" => array('COLUMN_NAME'=>"sort",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL",'COLUMN_DEFAULT'=>"DEFAULT '255'"),
		),
		'tableKey' => "fundsid",
		'tableOther' => "ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;"
	),

	'account_image' => array(
		'tableName'  => "account_image",
		'tableField' => array(
			"id" => array('COLUMN_NAME'=>"id",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"NOT NULL",'EXTRA'=>"AUTO_INCREMENT"),
			"uid" => array('COLUMN_NAME'=>"uid",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"NOT NULL"),
			"acid" => array('COLUMN_NAME'=>"acid",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"DEFAULT NULL"),
			"name" => array('COLUMN_NAME'=>"name",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"type" => array('COLUMN_NAME'=>"type",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"size" => array('COLUMN_NAME'=>"size",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"NOT NULL"),
			"ext" => array('COLUMN_NAME'=>"ext",'COLUMN_TYPE'=>"varchar(8)",'IS_NULLABLE'=>"NOT NULL"),
			"md5" => array('COLUMN_NAME'=>"md5",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"savepath" => array('COLUMN_NAME'=>"savepath",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"savename" => array('COLUMN_NAME'=>"savename",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"time" => array('COLUMN_NAME'=>"time",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"NOT NULL"),
		),
		'tableKey' => "id",
		'tableOther' => "ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;"
	),

	'account_transfer' => array(
		'tableName'  => "account_transfer",
		'tableField' => array(
			"tid" => array('COLUMN_NAME'=>"tid",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"NOT NULL",'EXTRA'=>"AUTO_INCREMENT"),
			"uid" => array('COLUMN_NAME'=>"uid",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"NOT NULL"),
			"money" => array('COLUMN_NAME'=>"money",'COLUMN_TYPE'=>"double(9,2) unsigned",'IS_NULLABLE'=>"NOT NULL"),
			"source_fid" => array('COLUMN_NAME'=>"source_fid",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"NOT NULL"),
			"target_fid" => array('COLUMN_NAME'=>"target_fid",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"NOT NULL"),
			"time" => array('COLUMN_NAME'=>"time",'COLUMN_TYPE'=>"int(11) unsigned",'IS_NULLABLE'=>"NOT NULL"),
			"mark" => array('COLUMN_NAME'=>"mark",'COLUMN_TYPE'=>"varchar(255)",'IS_NULLABLE'=>"NOT NULL"),
		),
		'tableKey' => "tid",
		'tableOther' => "ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;"
	),

	'user' => array(
		'tableName'  => "user",
		'tableField' => array(
			"uid" => array('COLUMN_NAME'=>"uid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL",'EXTRA'=>"AUTO_INCREMENT"),
			"username" => array('COLUMN_NAME'=>"username",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"password" => array('COLUMN_NAME'=>"password",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"email" => array('COLUMN_NAME'=>"email",'COLUMN_TYPE'=>"varchar(255)",'IS_NULLABLE'=>"NOT NULL"),
			"utime" => array('COLUMN_NAME'=>"utime",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
		),
		'tableKey' => "uid",
		'tableOther' => "ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;"
	),

	'user_config' => array(
		'tableName'  => "user_config",
		'tableField' => array(
			"cid" => array('COLUMN_NAME'=>"cid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL",'EXTRA'=>"AUTO_INCREMENT"),
			"uid" => array('COLUMN_NAME'=>"uid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
			"config_name" => array('COLUMN_NAME'=>"config_name",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"config_key" => array('COLUMN_NAME'=>"config_key",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"config_value" => array('COLUMN_NAME'=>"config_value",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"time" => array('COLUMN_NAME'=>"time",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
		),
		'tableKey' => "cid",
		'tableOther' => "ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;"
	),

	'user_login' => array(
		'tableName'  => "user_login",
		'tableField' => array(
			"lid" => array('COLUMN_NAME'=>"lid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL",'EXTRA'=>"AUTO_INCREMENT"),
			"uid" => array('COLUMN_NAME'=>"uid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
			"login_name" => array('COLUMN_NAME'=>"login_name",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"login_id" => array('COLUMN_NAME'=>"login_id",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"login_key" => array('COLUMN_NAME'=>"login_key",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
			"login_token" => array('COLUMN_NAME'=>"login_token",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL"),
		),
		'tableKey' => "lid",
		'tableOther' => "ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;"
	),

	'user_push' => array(
		'tableName'  => "user_push",
		'tableField' => array(
			"pid" => array('COLUMN_NAME'=>"pid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL",'EXTRA'=>"AUTO_INCREMENT"),
			"uid" => array('COLUMN_NAME'=>"uid",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
			"push_name" => array('COLUMN_NAME'=>"push_name",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"NOT NULL",'COLUMN_DEFAULT'=>"DEFAULT 'Weixin'"),
			"push_id" => array('COLUMN_NAME'=>"push_id",'COLUMN_TYPE'=>"varchar(64)",'IS_NULLABLE'=>"NOT NULL"),
			"push_mark" => array('COLUMN_NAME'=>"push_mark",'COLUMN_TYPE'=>"varchar(32)",'IS_NULLABLE'=>"DEFAULT NULL"),
			"time" => array('COLUMN_NAME'=>"time",'COLUMN_TYPE'=>"int(11)",'IS_NULLABLE'=>"NOT NULL"),
		),
		'tableKey' => "pid",
		'tableOther' => "ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;"
	),

);