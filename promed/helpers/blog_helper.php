<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Заменяет в тексте сообщения маркеры на теги, URLы на ссылки и т.п.
 */
function parseMessage($text)
{
    $patterns = array (
    '#\[b\](.*?)\[/b\]#is'=>'<span style="font-weight: bolder;">$1</span>',
    '#\[i\](.*?)\[/i\]#is'=>'<span style="font-style: italic;">$1</span>',
    '#\[u\](.*?)\[/u\]#is'=>'<span style="text-decoration: underline;">$1</span>',
    '#\[color=\#?([A-F0-9]{3}|[A-F0-9]{6})\](.*?)\[/color\]#is'=>'<span style="color: #$1;">$2</span>',
    '#\[url=(.*?)\](.*?)\[/url\]#i'=>'<a href="$1">$2</a>',
    '#\[url\](.*?)\[/url\]#i'=>'<a href="$1">$1</a>',
    '#\[img\](.*?)\[/img\]#i'=>'<img src="$1" alt="$1" />',
    '#\n#'=>'<br />',
    '#\[cut\]#is'=>'<br />',
    //    '#http://([a-zA-Z0-9-\./]+)#'=>'<a href="http://$1">http://$1</a>',
    //    '#https://([a-zA-Z0-9-\./]+)#'=>'<a href="https://$1">http://$1</a>',
    //    '#ftp://([a-zA-Z0-9-\./]+)#'=>'<a href="http://$1">ftp://$1</a>',
    '#(([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6})#'=>'<a href="mailto:$1">$1</a>',
    '#\(c\)#'=>'&copy;',
    '#\(с\)#'=>'&copy;' // здесь русская "с" :)
    );
    $text = preg_replace(array_keys($patterns), array_values($patterns), $text);
    return $text;
}
/**
 * Возвращает переменные объекта в строку через ','
 */
function str_object_keys($object)
{
    return implode(",", array_keys(get_object_vars($object)));
}
/**
 * Фильтрует и возвращает переменные объекта в строку через ','
 */
function str_object_values_escaped($object)
{
    $object_array = array_values(get_object_vars($object));
    array_walk($object_array, 'str_escape');
    return implode("','", $object_array);
}
/**
 *  Возвращает строку key1='value1', key2='value2',... из переменных объекта
 */
function str_object_key_value($object)
{
    $keys_array = array_keys(get_object_vars($object));
    $values_array = array_values(get_object_vars($object));
    $vars = (get_object_vars($object));
    array_walk($vars, 'str_escape');
    $str = '';
    for ($i = 0; $i < count($vars)-1; $i++)
    {
        if (key($vars) <> 'id')
        {
            $str .= key($vars).' = \''.current($vars).'\', ';
        }
        next($vars);
    }
    $str .= key($vars).' = \''.current($vars).'\'';
    return $str;
}

function str_escape($str)
{
    if (is_string($str))
    {
        $str = addslashes($str);
    }
    elseif (is_bool($str))
    {
        $str = ($str === FALSE)?0:1;
    }
    elseif (is_null($str))
    {
        $str = 'NULL';
    }
    return $str;
}
?>
