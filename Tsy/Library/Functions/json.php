<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/08/04
 * Time: 11:23
 */
/**
 * Indents a flat JSON string to make it more human-readable.
 * @param string $json The original JSON string to process.
 * @return string Indented version of the original JSON string.
 */
function json_format ($json) {
    if(!is_string($json)){
        $json = json_encode($json,JSON_UNESCAPED_UNICODE);
    }
    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = '  ';
    $newLine = "\r\n";
    $prevChar = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

// Grab the next character in the string.
        $char = substr($json, $i, 1);
// Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
// If this character is the end of an element,
// output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos+1; $j++) {
                $result .= $indentStr;
            }
        }
// Add the character to the result string.
        $result .= $char;
// If the last character was the beginning of an element,
// output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            for ($j = 0; $j < $pos+1; $j++) {
                $result .= $indentStr;
            }
        }
        $prevChar = $char;
    }

    return $result;

}

