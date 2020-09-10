<?php
// Вывод расписания услуги для редактирования. Подвал таблицы со ссылками
?>
<tr class=foot>
<?php
	if (!$data['readOnly']) {
		$data['msData']['Lpu_Nick'] = htmlspecialchars($data['msData']['Lpu_Nick']);
		foreach ($data['header'] as $day => $header) {
			echo "<td class='erlink'>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openFillWindow('".date("d.m.Y", DayMinuteToTime($day, 0))."'); return false;\">Заполнить расписание</a><hr>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openAddDopWindow({$day}); return false;\">Добавить доп. бирку</a><hr>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openEditDayCommentWindow('".date("d.m.Y", DayMinuteToTime($day, 0))."'); return false;\">Создать примечание</a><hr>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openDayListTTR('".date('d.m.Y', DayMinuteToTime($day, 0))."',{Resource_id: {$data['msData']['Resource_id']}, Resource_Name: '{$data['msData']['Resource_Name']}', MedService_id: {$data['msData']['MedService_id']}, MedService_Name: '{$data['msData']['MedService_Name']}', Lpu_id: {$data['msData']['Lpu_id']}, Lpu_Nick: '{$data['msData']['Lpu_Nick']}', LpuUnit_id: {$data['msData']['LpuUnit_id']}, LpuUnit_Name: '{$data['msData']['LpuUnit_Name']}'}); return false;\">Список записанных</a><hr>";
			if ( date("Y-m-d", DayMinuteToTime($day, 0)) >= date("Y-m-d") ) {
				if ($data['occupied'][$day]) {
					echo "<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').clearFreeTTR({$day}); return false;\">Очистить день</a>";
				} else {
					echo "<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').clearDay({$day}); return false;\">Очистить день</a>";
				}
			}

			echo "</td>";
		}
	}
?>
</tr>
</table>