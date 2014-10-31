CREATE TABLE IF NOT EXISTS `#__ijoomeradv_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `options` text,
  `type` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `server` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

INSERT INTO `#__ijoomeradv_config` (`id`, `caption`, `description`, `name`, `value`, `options`, `type`, `group`, `server`) VALUES
(1, 'COM_IJOOMERADV_GC_LOGIN_REQUIRED', 'COM_IJOOMERADV_GC_LOGIN_REQUIRED_EXPLAIN', 'IJOOMER_GC_LOGIN_REQUIRED', '0', '0::No;;1::Yes', 'select', 'global', 0),
(2, 'COM_IJOOMERADV_GC_REGISTRATION', 'COM_IJOOMERADV_GC_REGISTRATION_EXPLAIN', 'IJOOMER_GC_REGISTRATION', 'jomsocial', 'none::None;;joomla::Joomla', 'select', 'global', 0),
(3, 'COM_IJOOMERADV_THM_ENABLE_THEME', 'COM_IJOOMERADV_THM_ENABLE_THEME_EXPLAIN', 'IJOOMER_THM_ENABLE_THEME', '0', '0::No;;1::Yes', 'select', 'theme', 0),
(4, 'COM_IJOOMERADV_THM_SELECTED_THEME', 'COM_IJOOMERADV_THM_SELECTED_THEME_EXPLAIN', 'IJOOMER_THM_SELECTED_THEME', 'leather', 'leather::leather', 'select', 'theme', 0),
(5, 'COM_IJOOMERADV_PUSH_ENABLE', 'COM_IJOOMERADV_PUSH_ENABLE_EXPLAIN', 'IJOOMER_PUSH_ENABLE_IPHONE', '0', '0::No;;1::Yes', 'select', 'push>>iphone', 1),
(6, 'COM_IJOOMERADV_PUSH_DEPLOYMENT_MODE', 'COM_IJOOMERADV_PUSH_DEPLOYMENT_MODE_EXPLAIN', 'IJOOMER_PUSH_DEPLOYMENT_IPHONE', '0', '0::Sandbox;;1::Live', 'select', 'push>>iphone', 1),
(7, 'COM_IJOOMERADV_PUSH_ENABLE_SOUND', 'COM_IJOOMERADV_PUSH_ENABLE_SOUND_EXPLAIN', 'IJOOMER_PUSH_ENABLE_SOUND_IPHONE', '0', '0::No;;1::Yes', 'select', 'push>>iphone', 1),
(8, 'COM_IJOOMERADV_PUSH_ENABLE', 'COM_IJOOMERADV_PUSH_ENABLE_EXPLAIN', 'IJOOMER_PUSH_ENABLE_ANDROID', '1', '0::No;;1::Yes', 'select', 'push>>android', 1),
(9, 'COM_IJOOMERADV_PUSH_API_KEY', 'COM_IJOOMERADV_PUSH_API_KEY_EXPLAIN', 'IJOOMER_PUSH_API_KEY_ANDROID', '', NULL, 'text', 'push>>android', 1),
(10, 'COM_IJOOMERADV_ENC_REQUIRED', 'COM_IJOOMERADV_ENC_REQUIRED_EXPLAIN', 'IJOOMER_ENC_REQUIRED', '0', '0::No;;1::Yes', 'select', 'encryption', 0),
(11, 'COM_IJOOMERADV_ENC_GENRATE_KEY', 'COM_IJOOMERADV_ENC_GENRATE_KEY_EXPLAIN', 'IJOOMER_ENC_GENRATE_KEY', '', NULL, 'button', 'encryption1', 1),
(12, 'COM_IJOOMERADV_ENC_KEY', 'COM_IJOOMERADV_ENC_KEY_EXPLAIN', 'IJOOMER_ENC_KEY', '', NULL, 'text', 'encryption', 1);


CREATE TABLE IF NOT EXISTS `#__ijoomeradv_extensions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `classname` varchar(255) NOT NULL,
  `option` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `#__ijoomeradv_extensions` (`id`, `name`, `classname`, `option`, `published`) VALUES
(1, 'ICMS', 'icms', 'com_content', 1);

CREATE TABLE IF NOT EXISTS `#__ijoomeradv_icms_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `options` text,
  `type` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `server` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ;


INSERT INTO `#__ijoomeradv_icms_config` (`id`, `caption`, `description`, `name`, `value`, `options`, `type`, `group`, `server`) VALUES
(1, 'COM_IJOOMERADV_ICMS_ARTICLE_LIMIT', 'COM_IJOOMERADV_ICMS_ARTICLE_LIMIT_EXPLAIN', 'ICMS_ARTICLE_LIMIT', '10', '', 'text', 'pagination', 0),
(2, 'COM_IJOOMERADV_ICMS_CATEGORY_LIMIT', 'COM_IJOOMERADV_ICMS_CATEGORY_LIMIT_EXPLAIN', 'ICMS_CATEGORY_LIMIT', '10', '', 'text', 'pagination', 0);


CREATE TABLE IF NOT EXISTS `#__ijoomeradv_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT 'The display title of the menu item.',
  `menutype` int(11) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(16) NOT NULL COMMENT 'The type of link: Component, URL, Alias, Separator',
  `menudevice` int(1) NOT NULL DEFAULT '1' COMMENT 'Default:1,Android:2,Iphone:3,Both:4',
  `published` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'The published state of the menu link.',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The access level required to view the menu item.',
  `views` varchar(255) NOT NULL,
  `home` tinyint(3) NOT NULL,
  `ordering` int(11) NOT NULL,
  `requiredField` tinyint(4) NOT NULL,
  `menuoptions` text,
  `itemimage` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_componentid` (`published`,`access`)
);

CREATE TABLE IF NOT EXISTS `#__ijoomeradv_menu_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menutype` varchar(24) NOT NULL,
  `title` varchar(48) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL COMMENT '1:Home Screen, 2:Slide Menu, 3:Bottom Menu',
  `menudevice` int(1) NOT NULL DEFAULT '1' COMMENT 'Both:1,Android:2,Iphone:3',
  `screen` text NOT NULL,
  `menuitem` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);


CREATE TABLE IF NOT EXISTS `#__ijoomeradv_users` (
  `userid` int(11) NOT NULL,
  `jomsocial_params` text NOT NULL,
  `device_token` varchar(255) DEFAULT NULL,
  `device_type` varchar(255) DEFAULT NULL,
  `coverpic` int(11) DEFAULT NULL,
  PRIMARY KEY (`userid`)
);

CREATE TABLE IF NOT EXISTS `#__ijoomeradv_push_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_type` text NOT NULL,
  `to_user` text NOT NULL,
  `to_all` tinyint(1) NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `#__ijoomeradv_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `status` int(2) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `#__ijoomeradv_push_notification_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `detail` blob NOT NULL,
  `tocount` int(3) NOT NULL,
  `readcount` int(3) NOT NULL,
  PRIMARY KEY (`id`)
);
