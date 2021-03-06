<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
date_default_timezone_set('Europe/London');

function plural($num) {
	if ($num != 1)
		return "s";
}

function getRelativeTime($date) {
  $time = @strtotime($date);
	$diff = time() - $time;
	if ($diff<60)
		return $diff . " second" . plural($diff) . " ago";
	$diff = round($diff/60);
	if ($diff<60)
		return $diff . " minute" . plural($diff) . " ago";
	$diff = round($diff/60);
	if ($diff<24)
		return $diff . " hour" . plural($diff) . " ago";
	$diff = round($diff/24);
	if ($diff<7)
		return $diff . " day" . plural($diff) . " ago";
	$diff = round($diff/7);
	if ($diff<4)
		return $diff . " week" . plural($diff) . " ago";
  if (date('Y', $time) != date('Y', time())) 
    return date("j-M Y", $time);
	return date("j-M", $time);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset=utf-8 />
<title><?php echo $name ?>'s bins</title>
<style>
/* Font via http://robey.lag.net/2010/06/21/mensch-font.html */
@font-face {
  font-family: 'MenschRegular';
  src: url('/font/mensch-webfont.eot');
  src: url('/font/mensch-webfont.eot?#iefix') format('eot'),
       url('/font/mensch-webfont.woff') format('woff'),
       url('/font/mensch-webfont.ttf') format('truetype'),
       url('/font/mensch-webfont.svg#webfont0UwCC656') format('svg');
  font-weight: normal;
  font-style: normal;
}

body {
  font-family: MenschRegular, Menlo, Monaco, consolas, monospace;
  padding: 0;
  margin: 0;
}
.thumb {
  border: 1px solid #ccc;
  overflow: hidden;
  height: 145px;
  width: 193px;
  margin: 10px 0;
}
iframe {
  -moz-transform:    scale(0.8);
  -o-transform:      scale(0.8);
  -webkit-transform: scale(0.8);
  transform:         scale(0.8);
  /* IE8+ - must be on one line, unfortunately */ 
  -ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.8, M12=0, M21=0, M22=0.8, SizingMethod='auto expand')";
  
  /* IE6 and 7 */ 
  filter: progid:DXImageTransform.Microsoft.Matrix(
           M11=0.8,
           M12=0,
           M21=0,
           M22=0.8,
           SizingMethod='auto expand');
  
  -webkit-transform-origin: 0 0;
  
  width: 100%;
  height: 100%;
}
#bins {
  width: 70%;
  font-size: 13px;
  padding: 10px 0;
  position: relative;
}
#preview {
  border-left: 1px solid #ccc;
  position: fixed;
  top: 0;
  width: 30%;
  right: 0;
  height: 100%;
  padding-top: 10px;
  background: #fff;
}
h2 {
  margin: 0;
  font-size: 14px;
  font-family: "Helvetica Neue", Helvetica, Arial;
  font-size: 13px;
  padding: 0 20px;
}
#bins h2 {
  margin-bottom: 10px;
}

table {
  border-collapse: collapse;
  table-layout: fixed;
  width: 100%;
  position: relative;
}

td {
  margin: 0;
  padding: 3px 0;
}

.url {
  padding-left: 20px;
  text-align: right;
  padding-right: 20px;
  color: #0097fe;
  width: 25%;
}

.url span {
  color: #000;
  visibility: hidden;
}

.url span.first {
  visibility: visible;
}

.created {
  color: #ccc;
  width: 25%;
}

.title {
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
}

tr:hover *,
tr.hover * {
  background: #0097fe;
  color: #fff;
  cursor: pointer;
}

tr[data-type=spacer]:hover * {
  background: #fff;
  cursor: default;
}

iframe {
  border: 0;
  display: block;
  margin: 0 auto;
  width: 90%;
}

#viewing {
  font-size: 10px;
  margin-left: 20px;
}
</style>
</head>
<body>
<div id="bins">
<h2>Open</h2>
<table>
<tbody>
<?php 
$last = null;
$bins = array();
while ($bin = mysql_fetch_array($result)) {
    $url = $name . formatURL($bin['url'], $bin['revision']);
    preg_match('/<title>(.*?)<\/title>/', $bin['html'], $match);
    preg_match('/<body>(.*)/s', $bin['html'], $body);
    if (count($body)) {
      $title = strip_tags($body[1]);
    }
    if ($bin['javascript']) {
      $title = preg_replace('/\s+/', ' ', $bin['javascript']);
    }

    if (get_magic_quotes_gpc() && $title) {
      $title = stripslashes($title);
    }
    if (!$title && count($match)) {
      $title = get_magic_quotes_gpc() ? stripslashes($match[1]) : $match[1];
    }

    $firstTime = $bin['url'] != $last;

    if ($firstTime && $last !== null) : ?>
<tr data-type="spacer"><td colspan=3></td></tr>
    <?php endif ?>
<tr data-url="<?=$url?>">
  <td class="url"><span<?=($firstTime ? ' class="first"' : '') . '>' . $bin['url']?>/</span><?=$bin['revision']?>/</td>
  <td class="created"><?=getRelativeTime($bin['created'])?></td>
  <td class="title"><?=$title?></td>
</tr>
<?php
    $last = $bin['url'];
/*<a href="/<?php echo $url ?>"><?php echo $title ?></a> [<a href="<?php echo HOST . $url . 'edit' ?>">edit</a>]<br>
    URL: <?php echo HOST . $url ?><br>
    Saved: <time title="<?php echo $bin['created']?>" datetime="<?php echo $bin['created'] ?>"><?php echo getRelativeTime($bin['created']) ?></time><br>
    JavaScript: <?php echo strlen($bin['javascript']) ? 'yes' : 'no' ?><br>
    HTML: <?php echo strlen($bin['html']) ? 'yes' : 'no' ?>
    <div class="thumb"><iframe src="<?php echo $url ?>quiet" frameborder="0"></iframe></div></li>
*/ ?>
<?php } ?>
</tbody>
</table>
</div>
<div id="preview">
<h2>Preview</h2>
<p id="viewing"></p>
<iframe id="iframe" hidden></iframe>
</div>
<script>
function render(url) {
  iframe.src = url + 'quiet';
  iframe.removeAttribute('hidden');
  viewing.innerHTML = 'http://jsbin.com/' + url;
}

function matchNode(el, nodeName) {
  if (el.nodeName == nodeName) {
    return el;
  } else if (el.nodeName == 'BODY') {
    return false;
  } else {
    return matchNode(el.parentNode, nodeName);
  }
}

function removeHighlight() {
  var i = trs.length;
  while (i--) {
    trs[i].className = '';
  }
}

function visit() {
  window.location = this.getAttribute('data-url') + 'edit';
}

var preview = document.getElementById('preview'),
    iframe = document.getElementById('iframe');
    bins = document.getElementById('bins'),
    trs = document.getElementsByTagName('tr'),
    current = null,
    viewing = document.getElementById('viewing'),
    hoverTimer = null;

// this is some nasty code - just because I couldn't be
// bothered to bring jQuery to the party.
bins.onmouseover = function (event) {
  clearTimeout(hoverTimer);
  event = event || window.event;
  var url, target = event.target || event.srcElement;
  if (target = matchNode(event.target, 'TR')) {
    removeHighlight();
    if (target.getAttribute('data-type') !== 'spacer') {
      target.className = 'hover';
      target.onclick = visit;
      url = target.getAttribute('data-url');
      if (current !== url) {
        hoverTimer = setTimeout(function () {
          current = url;
          render(url);
        }, 200);
      }
    }
  }
};
</script>
</body>
</html>
