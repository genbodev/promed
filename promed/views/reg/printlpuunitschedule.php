<?php
if(gettype($data) == 'array' && count($data) == 0){
	echo '<p>На ', $Date, ' в «', $LpuUnit_Name,'» нет записанных пациентов.</p>';
}
foreach($data as $msf) {
?>
Расписание приема<br/>
Врач: <?php echo $msf['MedPersonal_FIO']?><br/>
Отделение:  <?php 
	echo $msf['LpuSectionProfile_Name'].", ".$msf['LpuSection_Name'];
?><br/>
Дата: <?php echo $Date?><br/>
<br/>
	
<!---/*NO PARSE JSON*/--->
<table cellpadding=0 cellspacing=0 id=timeTable>
<tr class=head>
<td width=50>Время</td>
<td width=250>Пациент</td>
<td width=60>Дата рождения</td>
<td width=400>Адрес</td>
</tr>
<?php
// Вывод расписания для записи. Основная часть с бирками
$i = 0;
$rec_cnt = 0;
foreach( $msf['schedule'] as $arFields ) {
	echo "<tr class='time' style='text-align: left;'>\r\n";

	$sClass = "work";
	$sText = "&nbsp;";
	$sEvents  = "";
	
	If ($arFields["Person_id"] != "") $rec_cnt++;
	
	echo "<td style='text-align: left;'>{$arFields["TimetableGraf_begTime"]}</td>";
	echo "<td style='text-align: left;'>{$arFields["Person_FIO"]}</td>";
	echo "<td style='text-align: left;'>{$arFields["Person_BirthDay"]}</td>";
	echo "<td style='text-align: left;'>{$arFields["Person_Address"]}</td>";

	echo "</tr>\r\n";
	$i++;
}
echo "<tr><td colspan=4>Итого {$rec_cnt} ".plural($rec_cnt, 'человек', 'человека', 'людей')."</td></tr>";
?>
</table>

<?php
echo "</br></br>";
}
?>