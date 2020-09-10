<!---/*NO PARSE JSON*/--->
<table cellpadding=0 cellspacing=0 id=timeTable>
	<tr class=head>
		<td width=250>Пациент</td>
		<td width=60>Дата рождения</td>
		<td width=400>Адрес</td>
		<td>Место работы/учебы</td>
		<td width=100>Телефон</td>
		<td>Прикреплен</td>
	</tr>
	<?php
	// Вывод расписания для записи. Основная часть с бирками
	$i = 0;
	$rec_cnt = 0;
	while( !EmptyArrays( $data['data'] ) && $i <= 500 ) { // $i <= 500 - защита от зацикливания
		echo "<tr class='time' onmouseover=\"this.style.backgroundColor = '#aaaaff';\" onmouseout=\"this.style.backgroundColor = '';\">\n";
		$nTime = $data['StartDay'];

		$nDay = TimeToDay( $nTime );

		$sClass = "work";
		$sText = "&nbsp;";
		$sEvents  = "";

		$arFields = array_shift( $data['data'] );

		If ($arFields["Person_id"] != "") $rec_cnt++;

		//Создание объекта бирки
		$Time = new TTimetableResource($arFields, $data['reserved'], $data );

		// Если только просмотр
		if (!empty($data['readOnly'])) {
			$Time->readOnly = true;
		}

		// Ячейка для редактирования
		$Time->PrintDayRow();

		echo "</tr>\r\n";
		$i++;
	}
	echo "<tr><td colspan=7>Итого {$rec_cnt} человек</td>";
	echo "</tr>";
	?>
</table>