<!---/*NO PARSE JSON*/--->
<p style="text-align: center"><b>Список организаций, записанных на прием <?php echo date('j.m.Y', $data['StartDay']); ?></b></p>
<table cellpadding=0 cellspacing=0 id=timeTable>
<tr class=head>
<td width=50>Время</td>
<td width=100>Организация</td>
<td width=200>Адрес</td>
<td width=150>Телефон</td>
<td width=500>Примечание</td>
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
	
	If ($arFields["Org_id"] != "") $rec_cnt++;
	
	//Создание объекта бирки
	$Time = new TTimetableMedServiceOrg($arFields, $data['reserved'], $data );
	
	// Ячейка для редактирования
	$Time->PrintDayRowForPrint();
		
	echo "</tr>\r\n";
	$i++;
}
echo "<tr><td colspan=7>Итого {$rec_cnt} организаций</td>";
echo "</tr>";
?>
</table>