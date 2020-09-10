<?php
// Вывод расписания параклиники. Часть заголовков, включая комментарии на день
?>
<!---/*NO PARSE JSON*/--->
<table cellpadding=0 cellspacing=0 id=timeTable>
	<tr class=head>
	<?php
	foreach ($data['header'] as $day) {
		echo $day;
	}
	?>
	</tr>
	<tr class='head'>
	<?php
	foreach ($data['descr'] as $day) {
		if ( isset($day['UslugaComplexDay_Descr']) ) {
			echo "<td class='comments'><img border=0 valign=center ext:qtip=\"<font class=\'smallfont\'>{$day['pmUser_Name']}, {$day['UslugaComplexDay_updDT']}</font>\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> ".nl2br($day['UslugaComplexDay_Descr'])."</td>";
		} else {
			echo "<td class='comments'></td>";
		}
	}
	?>
	</tr>