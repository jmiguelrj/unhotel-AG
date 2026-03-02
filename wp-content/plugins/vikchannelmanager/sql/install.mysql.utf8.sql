CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_roomsxref` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idroomvb` int(10) NOT NULL,
  `idroomota` varchar(64) NOT NULL DEFAULT '0',
  `idchannel` int(10) NOT NULL DEFAULT 0,
  `channel` varchar(64) DEFAULT 'expedia',
  `otaroomname` varchar(128) DEFAULT NULL,
  `otapricing` text DEFAULT NULL,
  `prop_name` varchar(128) NOT NULL DEFAULT '',
  `prop_params` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_balancer_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `mod_ts` int(11) NOT NULL,
  `type` varchar(8) DEFAULT 'av',
  `from_ts` int(11) NOT NULL,
  `to_ts` int(11) NOT NULL,
  `rule` text DEFAULT NULL,
  `logs` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_balancer_rooms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `room_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_balancer_ratelogs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(11) NOT NULL,
  `rule_id` int(10) NOT NULL,
  `day` DATE DEFAULT NULL,
  `room_ids` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_balancer_bookings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL,
  `ts` int(11) NOT NULL,
  `rule_id` int(10) NOT NULL,
  `rule_name` varchar(64) DEFAULT NULL,
  `saveamount` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `param` varchar(128) NOT NULL,
  `setting` mediumtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `param` (`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_opportunities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL,
  `prop_first_param` varchar(128) DEFAULT NULL,
  `prop_name` varchar(128) DEFAULT NULL,
  `identifier` varchar(128) DEFAULT NULL,
  `channel` varchar(64) DEFAULT 'vikbooking',
  `title` varchar(256) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `action` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idordervb` int(10) NOT NULL,
  `ts` int(11) DEFAULT NULL,
  `orderdata` text DEFAULT NULL,
  `channel` varchar(64) DEFAULT 'expedia',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 1,
  `from` varchar(64) DEFAULT 'e4jconnect',
  `cont` text,
  `idordervb` int(10) DEFAULT NULL,
  `read` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_notification_child` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int(10) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 1,
  `cont` text,
  `channel` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idordervb` int(10) NOT NULL DEFAULT 0,
  `key` int(10) NOT NULL DEFAULT '1717',
  `id_notification` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_channel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `params` text,
  `uniquekey` varchar(16) NOT NULL DEFAULT '0xFF',
  `av_enabled` tinyint(1) DEFAULT 0,
  `settings` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_hotel_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL DEFAULT 'false',
  `value` text,
  `required` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `param` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_hotel_multi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hname` varchar(64) NOT NULL,
  `hdata` text,
  `channel` int(10) DEFAULT 0,
  `account_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`hname`, `channel`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_tac_rooms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `desc` varchar(1000) NOT NULL DEFAULT '',
  `img` varchar(256) DEFAULT '',
  `url` varchar(512) NOT NULL DEFAULT '',
  `cost` decimal(6, 2) DEFAULT 0.0,
  `amenities` text,
  `codes` varchar(32) DEFAULT '',
  `id_vb_room` int(10) NOT NULL,
  `account_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_tri_rooms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `desc` varchar(1000) NOT NULL DEFAULT '',
  `img` varchar(256) DEFAULT '',
  `url` varchar(512) NOT NULL DEFAULT '',
  `cost` decimal(6, 2) DEFAULT 0.0,
  `codes` varchar(32) DEFAULT '',
  `id_vb_room` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_listings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `retrieval_url` varchar(256) NOT NULL, 
  `id_vb_room` int(10) NOT NULL,
  `channel` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_ical_channels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `logo` varchar(256) DEFAULT NULL,
  `rules` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_ical_bookings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(10) NOT NULL,
  `rid` int(10) NOT NULL,
  `uniquekey` varchar(16) NOT NULL DEFAULT '0xFF',
  `ota_bid` varchar(256) DEFAULT NULL,
  `signature` varchar(128) DEFAULT NULL,
  `created_on` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_call_stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `channel` varchar(16) NOT NULL,
  `call` varchar(64) NOT NULL,
  `min_exec_time` decimal(12, 4) DEFAULT 999999,
  `max_exec_time` decimal(12, 4) DEFAULT 0,
  `last_exec_time` decimal(12, 4) DEFAULT 0,
  `last_visit` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_rar_updates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `channel` varchar(16) NOT NULL,
  `date` varchar(32) NOT NULL,
  `room_type_id` varchar(32) NOT NULL,
  `data` text DEFAULT NULL,
  `last_update` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_reslogs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idorder` int(10) DEFAULT NULL,
  `idorderota` varchar(64) DEFAULT NULL,
  `idchannel` int(10) DEFAULT NULL,
  `idroomvb` int(10) DEFAULT NULL,
  `idroomota` varchar(32) NOT NULL DEFAULT '0',
  `dt` datetime NOT NULL,
  `day` date NOT NULL,
  `type` char(3) NOT NULL DEFAULT 'NBW',
  `descr` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_rqschedules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL,
  `vbo_order_id` int(11) NOT NULL DEFAULT 0,
  `payload` text DEFAULT NULL,
  `request` varchar(8) DEFAULT 'a',
  `channels` varchar(128) DEFAULT NULL,
  `errno` tinyint(1) NOT NULL DEFAULT 0,
  `errmsg` varchar(1024) DEFAULT NULL,
  `extra` varchar(1024) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `last_retry` datetime DEFAULT NULL,
  `retries` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_pendinglocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vbo_order_id` int(11) NOT NULL DEFAULT 0,
  `until` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_otareviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `review_id` varchar(64) DEFAULT '0',
  `prop_first_param` varchar(128) DEFAULT NULL,
  `prop_name` varchar(128) DEFAULT NULL,
  `channel` varchar(64) DEFAULT NULL,
  `uniquekey` varchar(16) NOT NULL DEFAULT '0xFF',
  `idorder` int(10) DEFAULT NULL,
  `dt` datetime NOT NULL,
  `customer_name` varchar(128) DEFAULT NULL,
  `lang` varchar(16) DEFAULT NULL,
  `score` decimal(5, 2) DEFAULT 0,
  `country` char(3) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_otascores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prop_first_param` varchar(128) DEFAULT NULL,
  `prop_name` varchar(128) DEFAULT NULL,
  `channel` varchar(64) DEFAULT NULL,
  `uniquekey` varchar(16) NOT NULL DEFAULT '0xFF',
  `last_updated` datetime NOT NULL,
  `score` decimal(5, 2) DEFAULT 0,
  `content` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_threads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idorder` int(10) DEFAULT NULL,
  `idorderota` varchar(64) DEFAULT NULL,
  `channel` varchar(64) DEFAULT NULL,
  `ota_thread_id` varchar(64) DEFAULT NULL,
  `subject` varchar(128) DEFAULT NULL,
  `topic` varchar(128) DEFAULT NULL,
  `type` varchar(64) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `no_reply_needed` tinyint(1) NOT NULL DEFAULT 0,
  `ai_processed` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'whether the cron already processed this thread to extract the topics',
  `ai_stopped` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'whether the auto-responder should ignore this thread',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_threads_cohosts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ota_cohost_id` varchar(64) NOT NULL DEFAULT '0',
  `channel` varchar(64) DEFAULT NULL,
  `nominative` varchar(256) DEFAULT NULL,
  `pic` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_threads_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idthread` int(10) NOT NULL,
  `ota_message_id` varchar(64) DEFAULT NULL,
  `in_reply_to` varchar(64) DEFAULT NULL,
  `sender_id` varchar(64) DEFAULT NULL,
  `sender_name` varchar(128) DEFAULT NULL,
  `sender_type` varchar(32) DEFAULT NULL,
  `recip_id` varchar(64) DEFAULT NULL,
  `recip_name` varchar(128) DEFAULT NULL,
  `recip_type` varchar(32) DEFAULT NULL,
  `dt` datetime DEFAULT NULL,
  `content` text DEFAULT NULL,
  `translation` text DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `read_dt` datetime DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `device` varchar(32) DEFAULT NULL,
  `needs_reply` tinyint(1) NOT NULL DEFAULT 1,
  `cohost_id` int(10) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `replied` tinyint(1) NOT NULL DEFAULT 0,
  `ai_replied` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'whether the AI auto-responder already processed this message',
  `lang` char(8) DEFAULT NULL,
  `suspicious` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  FULLTEXT KEY (`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_threads_messages_reactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idthread` int(10) NOT NULL,
  `idmessage` int(10) NOT NULL,
  `ota_message_id` varchar(64) DEFAULT NULL,
  `emoji` varchar(16) NOT NULL,
  `user` varchar(128) DEFAULT NULL,
  `iduser` varchar(64) DEFAULT NULL,
  `dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_threads_schedules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idthread` int(10) NOT NULL,
  `responder_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_threads_drafts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idthread` int(10) NOT NULL,
  `dt` datetime DEFAULT NULL,
  `content` text DEFAULT NULL,
  `attachments` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_threads_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ota_thread_id` varchar(64) DEFAULT NULL,
  `ota_user_id` varchar(64) DEFAULT NULL,
  `channel` varchar(64) DEFAULT NULL,
  `type` varchar(32) NOT NULL DEFAULT 'user',
  `nominative` varchar(256) DEFAULT NULL,
  `pic` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_messaging_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic` varchar(128) NOT NULL,
  `hits` int(10) DEFAULT 1,
  `idthread` int(10) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_order_messaging_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idorder` int(10) DEFAULT NULL,
  `idorderota` varchar(64) DEFAULT NULL,
  `last_check` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_messaging_users_pings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idorder` int(10) NOT NULL,
  `client` tinyint(1) DEFAULT 0 COMMENT '1: admin, 0: site',
  `ping` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_otarooms_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idchannel` int(5) NOT NULL DEFAULT 0,
  `account_key` varchar(64) NOT NULL DEFAULT '',
  `idroomota` varchar(64) DEFAULT NULL,
  `param` varchar(64) NOT NULL,
  `setting` mediumtext,
  `last_updated` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_otapromotions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vbo_promo_id` int(5) NOT NULL DEFAULT 0,
  `ota_promo_id` varchar(128) NOT NULL DEFAULT '',
  `channel` varchar(64) DEFAULT NULL,
  `method` varchar(32) NOT NULL DEFAULT 'new',
  `data` text DEFAULT NULL,
  `dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_rates_flow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `day_from` date DEFAULT NULL,
  `day_to` date DEFAULT NULL,
  `channel_id` int(5) NOT NULL DEFAULT -1,
  `ota_room_id` varchar(64) DEFAULT NULL,
  `vbo_room_id` int(10) NOT NULL,
  `vbo_price_id` int(10) NOT NULL,
  `base_fee` decimal(12,2) DEFAULT 0.00,
  `nightly_fee` decimal(12,2) DEFAULT NULL,
  `channel_alter` varchar(16) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `created_by` varchar(128) DEFAULT NULL,
  `created_on` timestamp DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_channel_id` (`channel_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikchannelmanager_chat_async_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_thread` int(10) NOT NULL,
  `id_message` int(10) NOT NULL,
  `instance` blob DEFAULT NULL COMMENT 'serialized VCMChatAsyncJob object',
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('dateformat', '');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('currencysymb', '');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('currencyname', '');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('defaultpayment', '-1');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('vikbookingsynch', '1');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('emailadmin', '');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('apikey', '');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('moduleactive', '');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('account_status', '0');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('version', '1.0');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('to_update', '0');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('block_program', '0');

INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('tri_partner_id', '');

INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('tac_partner_id', '');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('tac_account_id', '');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('tac_api_key', '');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('pro_level', '15');
INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('av_window', '3');

INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('name', '', 1);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('street', '', 1);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('city', '', 1);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('zip', '', 0);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('state', '', 0);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('country', '', 1);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('latitude', '', 0);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('longitude', '', 0);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('description', '', 0);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('amenities', '', 0);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('url', '', 1);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('email', '', 0);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('phone', '', 0);
INSERT INTO `#__vikchannelmanager_hotel_details` (`key`,`value`,`required`) VALUES('fax', '', 0);