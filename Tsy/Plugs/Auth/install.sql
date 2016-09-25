-- ----------------------------
-- {$PREFIX}auth_rule，规则表，
-- ID:主键，Name：规则唯一标识, Title：规则中文名称 Status 状态：为1正常，为0禁用，condition：规则表达式，为空表示存在就验证，不为空表示按照条件验证
-- ----------------------------
 DROP TABLE IF EXISTS `{$PREFIX}auth_rule`;
CREATE TABLE `{$PREFIX}auth_rule` (
    `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `Name` char(80) NOT NULL DEFAULT '',
    `Title` char(20) NOT NULL DEFAULT '',
    `Status` tinyint(1) NOT NULL DEFAULT '1',
    `Condition` char(100) NOT NULL DEFAULT '',
    PRIMARY KEY (`ID`),
    UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- ----------------------------
-- {$PREFIX}auth_group 用户组表，
-- ID：主键， Title:用户组中文名称， Rules：用户组拥有的规则ID， 多个规则","隔开，Status 状态：为1正常，为0禁用
-- ----------------------------
 DROP TABLE IF EXISTS `{$PREFIX}auth_group`;
CREATE TABLE `{$PREFIX}auth_group` (
    `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `Title` char(100) NOT NULL DEFAULT '',
    `Status` tinyint(1) NOT NULL DEFAULT '1',
    `Rules` char(80) NOT NULL DEFAULT '',
    PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- ----------------------------
-- {$PREFIX}auth_group_access 用户组明细表
-- UID:用户ID，GroupID：用户组ID
-- ----------------------------
DROP TABLE IF EXISTS `{$PREFIX}auth_group_access`;
CREATE TABLE `{$PREFIX}auth_group_access` (
    `UID` int(11) unsigned NOT NULL,
    `GroupID` int(11) unsigned NOT NULL,
    UNIQUE KEY `UIDGroupID` (`UID`,`GroupID`),
    KEY `UID` (`UID`),
    KEY `GroupID` (`GroupID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 */