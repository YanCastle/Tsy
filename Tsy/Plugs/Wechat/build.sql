drop table if exists {$PREFIX}wechat_log;
drop table if exists {$PREFIX}wechat_match;
drop table if exists {$PREFIX}wechat_member;
drop table if exists {$PREFIX}wechat_msg_type_dic;
drop table if exists {$PREFIX}wechat_reply;
drop table if exists {$PREFIX}wechat_reply_log;
/*==============================================================*/
/* Table: {$PREFIX}wechat_log                                            */
/*==============================================================*/
CREATE TABLE `{$PREFIX}wechat_log` (
  `LID` int(11) NOT NULL AUTO_INCREMENT,
  `To` char(50) DEFAULT NULL,
  `From` char(50) DEFAULT NULL,
  `Time` int(10) DEFAULT NULL,
  `MsgTypeID` int(11) DEFAULT NULL,
  `Content` text,
  `MsgID` char(50) DEFAULT NULL,
  `MatchRuleID` int(11) unsigned DEFAULT NULL,
  `Match` char(255) DEFAULT NULL,
  PRIMARY KEY (`LID`),
  KEY `from` (`From`) USING BTREE,
  KEY `FK_Reference_2` (`MsgTypeID`) USING BTREE,
  CONSTRAINT `{$PREFIX}wechat_log_ibfk_1` FOREIGN KEY (`MsgTypeID`) REFERENCES `{$PREFIX}wechat_msg_type_dic` (`MsgTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*==============================================================*/
/* Index: `from`                                                */
/*==============================================================*/
create index `from` on {$PREFIX}wechat_log
(
   `From`
);
/*==============================================================*/
/* Table: {$PREFIX}wechat_match                                          */
/*==============================================================*/
create table {$PREFIX}wechat_match
(
   ConfigID             int(11) not null auto_increment,
   Rule                 text,
   MsgTypeID            int(11) not null,
   Method               char(50),
   `Order`              int(11) comment '查询时按Order的大小查询',
   Success              char(250),
   ReplyID              int(11) not null,
   StartTime            int(10),
   EndTime              int(10),
   MatchTimes           int(11),
   `Name`                 char(250),
   `Open`                 tinyint(1) default 1,
   primary key (ConfigID)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*==============================================================*/
/* Index: unique_match_rule_name                                */
/*==============================================================*/
create unique index unique_match_rule_name on {$PREFIX}wechat_match
(
   `Name`
);
/*==============================================================*/
/* Index: start                                                 */
/*==============================================================*/
create index start on {$PREFIX}wechat_match
(
   StartTime
);
/*==============================================================*/
/* Index: end                                                   */
/*==============================================================*/
create index end on {$PREFIX}wechat_match
(
   EndTime
);
/*==============================================================*/
/* Index: times                                                 */
/*==============================================================*/
create index times on {$PREFIX}wechat_match
(
   MatchTimes
);
/*==============================================================*/
/* Index: `order`                                               */
/*==============================================================*/
create index `order` on {$PREFIX}wechat_match
(
   `Order`
);
/*==============================================================*/
/* Table: {$PREFIX}wechat_member                                         */
/*==============================================================*/
create table {$PREFIX}wechat_member
(
   MemberID             int(11) not null auto_increment,
   OpenID               char(250),
   SubscribeTime        int(10),
   NickName             char(250),
   Sex                  tinyint(1),
   `Language`             char(50),
   City                 char(50),
   Province             char(50),
   Country              char(50),
   HeadImgUrl           char(250),
   Subscribe            tinyint(1),
   Unionid              char(250),
   Remark               char(250),
   GroupID              int(11),
   primary key (MemberID)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*==============================================================*/
/* Table: {$PREFIX}wechat_msg_type_dic                                   */
/*==============================================================*/
create table {$PREFIX}wechat_msg_type_dic
(
   MsgTypeID            int(11) not null auto_increment,
   MsgType              char(50),
   `Name`                 char(50),
   Method               char(20) not null,
   ReplyMethod          char(50),
   primary key (MsgTypeID)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*==============================================================*/
/* Index: msg_type_value                                        */
/*==============================================================*/
create unique index msg_type_value on {$PREFIX}wechat_msg_type_dic
(
   MsgType,
   Method
);
/*==============================================================*/
/* Table: {$PREFIX}wechat_reply                                          */
/*==============================================================*/
create table {$PREFIX}wechat_reply
(
   ReplyID              int(11) not null auto_increment,
   `Name`                 char(250),
   MsgTypeID            int(11) not null,
   Config               text,
   Method               char(50) comment 'Func:函数回调
            Assign:模板渲染
            TEXT:文本',
   primary key (ReplyID)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*==============================================================*/
/* Index: name                                                  */
/*==============================================================*/
create unique index name on {$PREFIX}wechat_reply
(
   `Name`
);
/*==============================================================*/
/* Table: {$PREFIX}wechat_reply_log                                      */
/*==============================================================*/
create table {$PREFIX}wechat_reply_log
(
   RLID                 int(11) not null auto_increment,
   LID                  int(11),
   `To`                   char(250),
   MsgTypeID            int(11) not null,
   Content              text,
   ReplyID              int(11),
   Time                 int(10),
   MatchID              int(11),
   primary key (RLID)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*==============================================================*/
/* Index: to                                                    */
/*==============================================================*/
create index `to` on {$PREFIX}wechat_reply_log
(
   `To`
);
/*==============================================================*/
/* Index: time                                                  */
/*==============================================================*/
create index time on {$PREFIX}wechat_reply_log
(
   Time
);

alter table {$PREFIX}wechat_log add constraint FK_Reference_2 foreign key (MsgTypeID)
      references {$PREFIX}wechat_msg_type_dic (MsgTypeID) on delete restrict on update restrict;
alter table {$PREFIX}wechat_match add constraint FK_Reference_4 foreign key (ReplyID)
      references {$PREFIX}wechat_reply (ReplyID) on delete restrict on update restrict;
alter table {$PREFIX}wechat_match add constraint FK_Reference_5 foreign key (MsgTypeID)
      references {$PREFIX}wechat_msg_type_dic (MsgTypeID) on delete restrict on update restrict;
alter table {$PREFIX}wechat_reply add constraint FK_Reference_1 foreign key (MsgTypeID)
      references {$PREFIX}wechat_msg_type_dic (MsgTypeID) on delete restrict on update restrict;
alter table {$PREFIX}wechat_reply_log add constraint FK_Reference_3 foreign key (MsgTypeID)
      references {$PREFIX}wechat_msg_type_dic (MsgTypeID) on delete restrict on update restrict;