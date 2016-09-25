<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/6/29
 * Time: 20:05
 */
function template_content_replace($content) {
    // 系统默认的特殊变量替换
    $replace =  array(
        '__ROOT__'      =>  '',       // 当前网站地址
        '__APP__'       =>  current_MCA('M').'/'.current_MCA('C').'/'.current_MCA('A'),        // 当前应用地址
        '__MODULE__'    =>  current_MCA('M'),
        '__ACTION__'    =>  current_MCA('A'),     // 当前操作地址
//        '__SELF__'      =>  htmlentities(__SELF__),       // 当前页面地址
        '__CONTROLLER__'=>  current_MCA('C'),
        '__URL__'       =>  current_MCA('C'),
        '__PUBLIC__'    =>  '/Public',// 站点公共目录
    );
    // 允许用户自定义模板的字符串替换
    if(is_array(C('TMPL_PARSE_STRING')) )
        $replace =  array_merge($replace,C('TMPL_PARSE_STRING'));
    $content = str_replace(array_keys($replace),array_values($replace),$content);
    return $content;
}

/**
 * 去除代码中的空白和注释
 * @param string $content 代码内容
 * @return string
 */
function strip_whitespace($content) {
    $stripStr   = '';
    //分析php源码
    $tokens     = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr  .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr  .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<THINK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "THINK;\n";
                    for($k = $i+1; $k < $j; $k++) {
                        if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } elseif($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr  .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}