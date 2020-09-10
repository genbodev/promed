<?php
// Вывод расписания поликлиники. Часть заголовков, включая комментарии на день
$tdw = round(100 / count($data['header']), 1); // ширина колонки
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
		if ( count($day) ) {
			echo "<td class='comments' style='width: {$tdw}%'>";
			foreach ($day as $comment) {			
				echo "<img border=0 valign=center ext:qtip=\"<font class=\'smallfont\'>{$comment['pmUser_Name']}, {$comment['Annotation_updDT']}</font>\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\">&nbsp;".nl2br($comment['Annotation_Comment'])."<br>";
			}
			echo "</td>";
		} else {
			echo "<td class='comments' style='width: {$tdw}%'></td>";
		}
	}
	?>
	</tr>