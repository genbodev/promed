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
				<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openDayListTTMSO('".date('d.m.Y', DayMinuteToTime($day, 0))."', {$data['msData']['MedService_id']}); return false;\">Список записанных</a><hr>";
			if ($data['occupied'][$day]) {
				echo "<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').clearFreeTTMSO({$day}); return false;\">Очистить день</a>";
			} else {
				echo "<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').clearDay({$day}); return false;\">Очистить день</a>";
			}

			echo "</td>";
		}
	}
?>
</tr>
</table>