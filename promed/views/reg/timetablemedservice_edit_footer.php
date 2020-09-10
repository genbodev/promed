<?php
// Вывод расписания службы для редактирования. Подвал таблицы со ссылками
?>
<tr class=foot>
<?php
	if (!$data['readOnly']) {
		$Lpu_Nick = str_replace('"','',$data['msData']['Lpu_Nick']);
		$LpuUnit_id = (!empty($data['msData']['LpuUnit_id']) ? $data['msData']['LpuUnit_id'] : 0);
		$LpuUnit_Name = str_replace('"','',$data['msData']['LpuUnit_Name']);
		$MedService_Name = str_replace('"','',$data['msData']['MedService_Name']);
		foreach ($data['header'] as $day => $header) {
			echo "<td class='erlink'>
				<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openFillWindow('".date("d.m.Y", DayMinuteToTime($day, 0))."'); return false;\">Заполнить расписание</a><hr>
				<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openAddDopWindow({$day}); return false;\">Добавить доп. бирку</a><hr>
				<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openEditDayCommentWindow({$day}); return false;\">Примечание</a><hr>
				<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openDayListTTMS('".date('d.m.Y', DayMinuteToTime($day, 0))."',{MedService_id: {$data['msData']['MedService_id']}, MedService_Name: '{$MedService_Name}', Lpu_id: {$data['msData']['Lpu_id']}, Lpu_Nick: '{$Lpu_Nick}', LpuUnit_id: {$LpuUnit_id}, LpuUnit_Name: '{$LpuUnit_Name}'}); return false;\">Список записанных</a><hr>";
			if ( date("Y-m-d", DayMinuteToTime($day, 0)) >= date("Y-m-d") ) {
				if ($data['occupied'][$day]) {
					echo "<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').clearFreeTTMS({$day}); return false;\">Очистить день</a>";
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