# 碳素云开发文档

**目录**

[TOCM]

[TOC]
# 数据库设计稿
<?php if($PDM): ?>项目名称：<?php echo ($PDM["Project"]["Name"]); ?>
项目介绍：

> <?php echo ($PDM["Project"]["Comment"]); ?>

字段定义字典：<?php echo ($line); echo ($line); ?>
|名称 | 类型 | 备注|
|---|---|---|
<?php if(is_array($PDM['Domains'])): foreach($PDM['Domains'] as $key=>$domain): ?>|<?php echo ($domain["Name"]); ?> | <?php echo ($domain["DataType"]); ?> | <?php echo ($domain["Comment"]); ?>|<?php echo ($line); endforeach; endif; ?>
<?php if(is_array($PDM['Tables'])): foreach($PDM['Tables'] as $key=>$table): echo ($line); ?>
## <?php echo ($table["Name"]); ?> (<?php echo ($table["Code"]); ?>)
> <?php echo ($table["Comment"]); ?>
<?php echo ($line); echo ($line); ?>
表字段：<?php echo ($line); echo ($line); ?>
|名称 | 代码 | 数据类型 | 逻辑代码 | 默认值 | 备注|
|---|---|---|---|---|---|
<?php if(is_array($table['Columns'])): foreach($table['Columns'] as $key=>$column): ?>|<?php echo ($column["Name"]); ?> | <?php echo ($column["Code"]); ?> | <?php echo ($column["DataType"]); ?> | <?php if($column['I'])echo 'I';if($column['M'])echo 'M';if($column['i'])echo 'P'; ?> | <?php echo ($column["DefaultValue"]); ?> | <?php echo ($column["Comment"]); ?>|<?php echo ($line); endforeach; endif; ?>
<?php echo ($line); endforeach; endif; endif; ?>
<?php echo ($line); echo ($line); ?>
# 对象列表
<?php if(is_array($Objects)): foreach($Objects as $key=>$Object): ?>## <?php echo ($Object["ObjectName"]); ?>(<?php echo ($Object["ObjectSetting"]["main"]); ?>)<?php echo ($line); ?>
主键：<?php echo ($Object["ObjectSetting"]["pk"]); echo ($line); ?>
对象结构：<?php echo ($line); ?>
```JSON
<?php echo ($Object["ObjectJSON"]); echo ($line); ?>
```
<?php echo ($line); ?>
对象字段说明：<?php echo ($line); echo ($line); ?>
|名称 | 代码 | 数据类型 | 逻辑代码 | 默认值 | 备注|
|---|---|---|---|---|---|
<?php if(is_array($Object['ObjectColumns'])): foreach($Object['ObjectColumns'] as $key=>$OC): ?>|<?php echo ($OC["Name"]); ?> | <?php echo ($key); ?> | <?php echo ($OC["DataType"]); ?> | <?php if($OC['I'])echo 'I';if($OC['M'])echo 'M';if($OC['i'])echo 'P'; ?> | <?php echo ($OC["DefaultValue"]); ?> | <?php echo ($OC["Comment"]); ?>|<?php echo ($line); endforeach; endif; endforeach; endif; ?>
<?php echo ($line); echo ($line); ?>
# 函数列表
<?php if(is_array($Functions)): foreach($Functions as $key=>$func): ?>##<?php echo ($func["zh"]); ?>(<?php echo ($func["name"]); ?>)
作者:<?php echo ($func['author']); ?>  链接:<?php echo ($func['link']); ?>
><?php echo ($func["memo"]); echo "\r\n"; ?>
参数列表：<?php echo ($line); echo ($line); ?>
<?php if($func['params']): ?>| 参数名称 |参数代码   | 数据类型 | 必填 | 默认值 | 说明 |
| --------   | :-----:  | :----:  | :----:  | :----:  | :----:  |
<?php if(is_array($func['params'])): foreach($func['params'] as $key=>$param): ?>| <?php echo ($param['zh']); ?> |<?php echo ($param['name']); ?>   | <?php echo ($param['type']); ?> | <?php echo ($param['must']); ?> | <?php echo ($param['default']); ?> | <?php echo ($param['memo']); ?> |<?php echo ($line); endforeach; endif; endif; ?>

返回值：
<?php echo ($func['return']); endforeach; endif; ?>

# 类列表
<?php if(is_array($Classes)): foreach($Classes as $key=>$cla): ?>## <?php echo ($cla['zh']); ?> (<?php echo ($cla['name']); ?>)
作者:<?php echo ($cla['author']); ?>  链接:<?php echo ($cla['link']); ?>

><?php echo ($cla['memo']); echo ($line); echo ($line); ?>

###属性
<?php if($cla['properties']): if(is_array($cla['properties'])): foreach($cla['properties'] as $key=>$property): ?>#### [<?php echo ($property['access']); ?>]<?php echo ($property['zh']); ?>(<?php echo ($property['name']); ?>)<?php endforeach; endif; endif; ?>
### 方法
<?php if($cla['methods']): if(is_array($cla['methods'])): foreach($cla['methods'] as $key=>$method): if($method['access']=='public'&&substr($method['name'],0,1)!='_'){ ?>
#### [<?php echo ($method["access"]); ?>] <?php echo ($method["zh"]); ?> (<?php echo ($method["name"]); ?>)
请求地址：<?php echo str_replace(["\\\\","\\"],['/','/'],str_replace(['Controller','Object'],'',$cla['name'])),'/',$method['name'],"\r\n"; ?>
作者:<?php echo ($method['author']); ?>  链接:<?php echo ($method['link']); ?>

参数列表：<?php echo ($line); echo ($line); ?>
<?php if($method['params']): ?>| 参数名称 |参数代码   | 数据类型 | 必填 | 默认值 | 说明 |
| --------   | :-----:  | :----: | :----:  | :----:  | :----:  |
<?php if(is_array($method['params'])): foreach($method['params'] as $key=>$param): ?>| <?php echo ($param['zh']); ?> |<?php echo ($param['name']); ?>   | <?php echo ($param['type']); ?> | <?php if($param['must']): ?>是<?php else: ?>否<?php endif; ?> | <?php echo ($param['default']); ?> | <?php echo ($param['memo']); ?> |<?php echo ($line); endforeach; endif; endif; ?>
<?php echo ($line); ?>
返回值：
<?php echo ($method['return']); ?>
<?php echo ($line); echo ($line); ?>
<?php } endforeach; endif; endif; endforeach; endif; ?>
<?php echo ($line); ?>