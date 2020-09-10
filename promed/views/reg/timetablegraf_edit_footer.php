<?php
// Вывод расписания для редактирования. Подвал таблицы со ссылками
?>
<tr class=foot>
<?php
	if (!$data['readOnly']) {
		$Lpu_Nick = str_replace('"','',$data['mpdata']['Lpu_Nick']);
		$LpuUnit_Name = str_replace('"','',$data['mpdata']['LpuUnit_Name']);
		foreach ($data['header'] as $day => $header) {
			echo "<td class='erlink'>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openFillWindow('".date("d.m.Y", DayMinuteToTime($day, 0))."'); return false;\">Заполнить расписание</a><hr>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openAddDopWindow({$day}); return false;\">Добавить доп. бирку</a><hr>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openEditDayCommentWindow('".date("d.m.Y", DayMinuteToTime($day, 0))."'); return false;\">Создать примечание</a><hr>
			<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').openDayListTTG('".date('d.m.Y', DayMinuteToTime($day, 0))."',{MedStaffFact_id: {$data['mpdata']['MedstaffFact_id']}, MedPersonal_FIO: '{$data['mpdata']['MedPersonal_FIO']}', LpuUnit_id: {$data['mpdata']['LpuUnit_id']}, LpuUnit_Name: '{$LpuUnit_Name}', Lpu_id: '{$data['mpdata']['Lpu_id']}', Lpu_Nick: '{$Lpu_Nick}', LpuSectionProfile_id: '{$data['mpdata']['LpuSectionProfile_id']}', LpuSectionProfile_Name: '{$data['mpdata']['LpuSectionProfile_Name']}', LpuSection_id: '', LpuSection_Name: '' }); return false;\">Список записанных</a><hr>";
			if ( date("Y-m-d", DayMinuteToTime($day, 0)) >= date("Y-m-d") ) {
				if ($data['occupied'][$day]) {
					echo "<a href=# onclick=\"Ext.getCmp('{$data['PanelID']}').clearFreeTTG({$day}); return false;\">Очистить день</a>";
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