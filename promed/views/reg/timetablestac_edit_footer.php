<?php
// Вывод расписания стационара для редактирования. Подвал таблицы со ссылками
?>
<tr class=foot>
<?php
	$Lpu_Nick = str_replace('"','',$data['lsData']['Lpu_Nick']);
	$LpuUnit_Name = str_replace('"','',$data['lsData']['LpuUnit_Name']);
	$LpuSection_Name = str_replace('"','',$data['lsData']['LpuSection_Name']);
	foreach ($data['header'] as $day => $header) {
		echo "<td class='erlink'>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openFillWindow('".date("d.m.Y", DayMinuteToTime($day, 0))."'); return false;\">Заполнить расписание</a><hr>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openEditDayCommentWindow({$day}); return false;\">Примечание</a><hr>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openDayListTTS('".date('d.m.Y', DayMinuteToTime($day, 0))."',{LpuUnit_id: {$data['lsData']['LpuUnit_id']}, LpuUnit_Name: '{$LpuUnit_Name}', Lpu_id: {$data['lsData']['Lpu_id']}, Lpu_Nick: '{$Lpu_Nick}', LpuSectionProfile_id: '{$data['lsData']['LpuSectionProfile_id']}', LpuSectionProfile_Name: '{$data['lsData']['LpuSectionProfile_Name']}', LpuSection_id: {$data['lsData']['LpuSection_id']}, LpuSection_Name: '{$LpuSection_Name}' }); return false;\">Список записанных</a><hr>";
		if ( date("Y-m-d", DayMinuteToTime($day, 0)) >= date("Y-m-d") ) {
			if ($data['occupied'][$day]) {
				echo "<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').clearFreeTTS({$day}); return false;\">Очистить день</a>";
			} else {
				echo "<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').clearDay({$day}); return false;\">Очистить день</a>";
			}
		}
		
		echo "</td>";
	}
?>
</tr>
</table>