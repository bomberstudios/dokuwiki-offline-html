<?php

require_once('inc/templateutils.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']; ?>"
 lang="<?php echo $conf['lang']; ?>" dir="<?php echo $lang['direction']; ?>">
<head>
	<title><?php echo strip_tags($conf['title']) ?> [<?php echo $ID ?>]</title>
	<link rel="stylesheet" media="screen" type="text/css" href="style.css" />
	<link rel="stylesheet" media="print" type="text/css" href="print.css" />
	<script type="text/javascript" charset="utf-8" src="script.js" ></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<div class="dokuwiki">
	<div class="stylehead">
		<div class="header">
			<div class="pagename">
				[[<a name="dokuwiki__dummy"><?php echo $ID ?></a>]]
			</div>
			<div class="logo">
				<a href="index.html" name="dokuwiki__top" id="dokuwiki__top" accesskey="h" title="[ALT+H]"><?php echo strip_tags($conf['title']) ?></a>
			</div>
			<div class="clearer"></div>
		</div>
	</div>

    <div class="bar" id="bar__top">
		<div class="bar-left" id="bar__topleft">
			&nbsp;
		</div>
		<div class="bar-right" id="bar__topright">
			&nbsp;
		</div>
		<div class="clearer"></div>
    </div>
	
	<div class="breadcrumbs">
		<?php offlinehtml_breadcrumbs($breadcrumbs); ?>
	</div>

	<div class="page">
		<!-- wikipage start -->
		<?php echo $content ?>
		<!-- wikipage stop -->
	</div>

	<div class="clearer">&nbsp;</div>

	<div class="stylefoot">
		<div class="meta">
			<div class="user">
				&nbsp;
			</div>
			<div class="doc">
				&nbsp;
			</div>
		</div>
	</div>

    <div class="bar" id="bar__bottom">
		<div class="bar-left" id="bar__bottomleft">
			&nbsp;
		</div>
		<div class="bar-right" id="bar__bottomright">
			&nbsp;
		</div>
		<div class="clearer"></div>
    </div>
</div>
</body>
</html>
