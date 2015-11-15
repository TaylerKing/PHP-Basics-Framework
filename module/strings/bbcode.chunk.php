<?php
namespace strings;
class BBCode {
  public static function tohtml($text,$advanced=TRUE,$charset='utf8'){
    $basic_bbcode = array(
      '[b]', '[/b]',
      '[i]', '[/i]',
      '[u]', '[/u]',
      '[s]','[/s]',
      '[ul]','[/ul]',
      '[li]', '[/li]',
      '[ol]', '[/ol]',
      '[center]', '[/center]',
      '[left]', '[/left]',
      '[right]', '[/right]',
      );
    $basic_html = array(
      '<b>', '</b>',
      '<i>', '</i>',
      '<u>', '</u>',
      '<s>', '</s>',
      '<ul>','</ul>',
      '<li>','</li>',
      '<ol>','</ol>',
      '<div style="text-align: center;">', '</div>',
      '<div style="text-align: left;">',   '</div>',
      '<div style="text-align: right;">',  '</div>',
      );
    $text = str_replace($basic_bbcode, $basic_html, $text);
    if ($advanced) {
      $advanced_bbcode = array(
       '#\[color=([a-zA-Z]*|\#?[0-9a-fA-F]{6})](.+)\[/color\]#Usi',
       '#\[size=([0-9][0-9]?)](.+)\[/size\]#Usi',
       '#\[quote](\r\n)?(.+?)\[/quote]#si',
       '#\[quote=(.*?)](\r\n)?(.+?)\[/quote]#si',
       '#\[url](.+)\[/url]#Usi',
       '#\[url=(.+)](.+)\[/url\]#Usi',
       '#\[email]([\w\.\-]+@[a-zA-Z0-9\-]+\.?[a-zA-Z0-9\-]*\.\w{1,4})\[/email]#Usi',
       '#\[email=([\w\.\-]+@[a-zA-Z0-9\-]+\.?[a-zA-Z0-9\-]*\.\w{1,4})](.+)\[/email]#Usi',
       '#\[img](.+)\[/img]#Usi',
       '#\[img=(.+)](.+)\[/img]#Usi',
       '#\[code](\r\n)?(.+?)(\r\n)?\[/code]#si',
       '#\[youtube]http://[a-z]{0,3}.youtube.com/watch\?v=([0-9a-zA-Z]{1,11})\[/youtube]#Usi',
       '#\[youtube]([0-9a-zA-Z]{1,11})\[/youtube]#Usi'
       );
      $advanced_html = array(
       '<span style="color: $1">$2</span>',
       '<span style="font-size: $1px">$2</span>',
       "<div class=\"quote\"><span class=\"quoteby\">Disse:</span>\r\n$2</div>",
       "<div class=\"quote\"><span class=\"quoteby\">Disse <b>$1</b>:</span>\r\n$3</div>",
       '<a rel="nofollow" target="_blank" href="$1">$1</a>',
       '<a rel="nofollow" target="_blank" href="$1">$2</a>',
       '<a href="mailto: $1">$1</a>',
       '<a href="mailto: $1">$2</a>',
       '<img src="$1" alt="$1" />',
       '<img src="$1" alt="$2" />',
       '<div class="codeblock"><code>$2</code></div>',
       '<object type="application/x-shockwave-flash" style="width: 450px; height: 366px;" data="http://www.youtube.com/v/$1"><param name="movie" value="http://www.youtube.com/v/$1" /><param name="wmode" value="transparent" /></object>',
       '<object type="application/x-shockwave-flash" style="width: 450px; height: 366px;" data="http://www.youtube.com/v/$1"><param name="movie" value="http://www.youtube.com/v/$1" /><param name="wmode" value="transparent" /></object>'
       );
$text = preg_replace($advanced_bbcode, $advanced_html,$text);
}
return bbcode::nl2br($text);
}
public static function remove($text) {
  return strip_tags(str_replace(array('[',']'), array('<','>'), $text));
}
public static function nl2br($var) {
  return str_replace(array('\\r\\n','\r\\n','r\\n','\r\n', '\n', '\r'), '<br />', nl2br($var));
}
}