<?php
// Вывод расписания службы для записи. Основная часть с бирками
$i = 0;
while( !EmptyArrays( $data['data'] ) && $i <= 500 ) { // $i <= 500 - защита от зацикливания
	echo "<tr class='time'>\n";
	$nTime = $data['StartDay'];
	for( $nCol = 0; $nCol < 14; $nCol++ ) {
		$nDay = TimeToDay( $nTime );

		$sClass = "work";
		$sText = "&nbsp;";
		$sEvents  = "";
		
		if ( count( $data['data'][$nDay] ) > 0 ) {
			$arFields = array_shift( $data['data'][$nDay] );
			
			//Создание объекта бирки
			$Time = new TTimetableMedServiceOrg($arFields, $data['reserved'], $data );
			
			// Ячейка для записи
			$Time->PrintCell();
			
		} else { //вывод пустой ячейки
			echo "<td class=''></td>";
		}

		$nTime = strtotime( "+1 day", $nTime );
	}
	echo "</tr>\r\n";
	$i++;
}
?>