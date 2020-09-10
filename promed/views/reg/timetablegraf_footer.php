<?php
// Вывод расписания для записи в поликлинику. Подвал таблицы со ссылками
if (!havingGroup('PM')) {
?>
<tr class=foot>
<?php
	foreach ($data['header'] as $day => $header) {
		echo "<td class='erlink'>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').getOwner().openDayListTTG('".date('d.m.Y', DayMinuteToTime($day, 0))."'); return false;\">Список записанных</a>";
		echo "</td>";
	}
?>
</tr>
<?php
}
?>
</table>
<script>
Ext.onReady(function (){
	<?="Ext.getCmp('{$data['PanelID']}').btnQueuePerson.setDisabled({$data['checkQueue']});"?>
});
</script>
