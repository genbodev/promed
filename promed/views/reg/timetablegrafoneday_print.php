<!---/*NO PARSE JSON*/--->
<table cellpadding=0 cellspacing=0 id=timeTable>
<tr class=head>
<td width=250>Пациент</td>
<td width=100>Номер амб. карты</td>
<td width=60>Дата рождения</td>
<td width=400>Адрес</td>
<?php
//Ufa, gaf #116387, для ГАУЗ РВФД
if ((isSuperadmin() || $data['lpu_id'] == 81) && $regionNick == "ufa"){
	echo "<td>Вид спорта</td>";
}else{
	echo "<td>Место работы/учебы</td>";
}
?>
<?php
//Ufa, gaf #116387, для ГАУЗ РВФД
if (!((isSuperadmin() || $data['lpu_id'] == 81) && $regionNick == "ufa")){
	echo "<td>Участок</td>";
}
?>
<td width=100>Телефон</td>
<?php
//Ufa, gaf #116387, для ГАУЗ РВФД
if (!((!isSuperadmin() || $data['lpu_id'] == 81) && $regionNick == "ufa")){
	echo "<td>Прикреплен</td><td>Прием</td>";
}
?>
<?php
// Для Астрахани выводится информация о полисе
if ($regionNick == "astra") {
	$footerColspan = 11;
	echo "<td>Полис</td>";
} else {
	$footerColspan = 10;
}
?>
<td>Оператор</td>
</tr>
<?php
// Вывод расписания для записи. Основная часть с бирками
$i = 0;
$rec_cnt = 0;
while( !EmptyArrays( $data['data'] ) && $i <= 500 ) { // $i <= 500 - защита от зацикливания
	echo "<tr class='time'\">\n";
	$nTime = $data['StartDay'];

	$nDay = TimeToDay( $nTime );

	$sClass = "work";
	$sText = "&nbsp;";
	$sEvents  = "";

	$arFields = array_shift( $data['data'] );
	
	If ($arFields["Person_id"] != "") $rec_cnt++;
	
	//Создание объекта бирки
	$Time = new TTimetableGraf($arFields, $data['reserved'], $data );
	
	// Ячейка для редактирования
	$Time->PrintDayRowForPrint();
		
	echo "</tr>\r\n";
	$i++;
}
echo "<tr><td colspan=", $footerColspan, ">Итого {$rec_cnt} человек</td>";
echo "</tr>";
?>
</table>