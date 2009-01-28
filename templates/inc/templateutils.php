<?php
function offlinehtml_breadcrumbs($crumbs, $sep='&raquo;') {
	global $conf;
	global $lang;
	if(!$conf['breadcrumbs']) return false;
	echo $lang['breadcrumb'] . ':';
	if ($lang['direction'] == 'rtl') array_reverse($breadcrumbs, true);
	$last = count($crumbs);
	$i = 0;
	foreach($crumbs as $id => $href) {
		$i++;
		echo ' <span class="bcsep">' . $sep . '</span> ';
		if ($i == $last) echo '<span class="curid">';
		echo "<a href=\"$href\">$id</a>";
		if ($i == $last) echo '</span>';
	}
	return true;
}
?>