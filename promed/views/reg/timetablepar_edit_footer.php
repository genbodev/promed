<?php
// Вывод расписания для редактирования. Подвал таблицы со ссылками
?>
<tr class=foot>
<?php
	foreach ($data['header'] as $day => $header) {
		echo "<td class='erlink'>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openFillWindow('".date("d.m.Y", DayMinuteToTime($day, 0))."'); return false;\">Заполнить расписание</a><hr>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openAddDopWindow({$day}); return false;\">Добавить доп. бирку</a><hr>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openEditDayCommentWindow({$day}); return false;\">Примечание</a><hr>";
		if ($data['occupied'][$day]) {
			echo "<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').clearFreeTTP({$day}); return false;\">Очистить день</a>";
		} else {
			echo "<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').clearDay({$day}); return false;\">Очистить день</a>";
		}
		
		echo "</td>";
	}
?>
</tr>
</table>