<?php
// Вывод расписания службы для записи. Подвал таблицы со ссылками
if (!havingGroup('PM')) {
?>
<tr class=foot>
<?php
	foreach ($data['header'] as $day => $header) {
		echo "<td class='erlink'>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').getOwner().openDayListTTMSO('".date('d.m.Y', DayMinuteToTime($day, 0))."'); return false;\">Список записанных</a>";
		echo "</td>";
	}
?>
</tr>
<?php
}
?>
</table>