#碳素云微信组建描述

使用步骤：拷贝目录，运行wechat_build.sql文件创建数据库，使用的数据库前缀与config.php中的一致

消息类型字典表数据
MsgTypeID 微信名称 中文名称
1	text	文本消息	TYPE	replyText
2	image	图片消息	TYPE	replyImage
3	voice	音频消息	TYPE	replyVoice
4	video	视频消息	TYPE	replyVideo
5	shortvideo	短视频消息	TYPE
6	location	位置消息	TYPE
7	link	连接消息	TYPE
8	music	音乐消息	TYPE	replyMusic
9	news	图文消息	TYPE	replyNews
10	event	事件消息	TYPE
11	transfer_customer_service	多客服转发	RETURN
12	subscribe	订阅	EVENT
13	unsubscribe	取消订阅	EVENT
14	SCAN	二维码扫码	EVENT
16	CLICK	菜单点击	EVENT
17	VIEW		EVENT
20	LOCATION	报告位置	EVENT
21	DEFAULT	默认处理方案	TYPE
22	NewsOnce	一次回复	RETURN	replyNewsOnce

#配置说明：
##1：匹配规则配置
匹配规则表：wechat_match
字段说明：
ConfigID：配置编号，不允许手动填写
Rule：匹配规则，与Method有关
MsgTypeID：参照MsgTypeDic表中的MsgTypeID字段，表示匹配该类型的消息
Method：匹配方式，有四种 EQ(全等),FUNC（函数回调）,PREG(正则匹配),EVENT(匹配事件)，当值为FUNC时需要在Rule中写回调参数 如：\Home\Service\WechatService::func，仅支持静态方法或全局函数，其参数为微信推送过来的所有数据
Order：同一个匹配方式中的匹配顺序
Success：匹配成功回调
ReplyID：wechat_reply表中的回复配置编号
StartTime：该规则的生效时间,该值为十位时间戳
EndTime：该规则的失效时间，该值为十位时间戳
Open：是否启用该规则


##2：回复规则
规则配置表：wechat_reply
字段说明：
ReplyID：自动生成
Name：名称标识，用于人工识别
MsgTypeID：消息回复类型
Config:回复配置，根据MsgTypeID不同其配置方式不同
Method：该值支持三个方式：TEXT，TEMPLATE，FUNC，分别表示 文本配置，直接返回文本内容；模板渲染，参见ThinkPHP的模板引擎；回调函数，回调函数允许重定义MsgTypeID