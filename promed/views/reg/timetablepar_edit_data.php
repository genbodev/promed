<?php
// Вывод расписания параклиники для редактирования. Основная часть с бирками
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
			$Time = new TTimetablePar($arFields, $data['reserved'], NULL );
			
			// Ячейка для редактирования
			$Time->PrintCellForEdit();
			
		} else { //вывод пустой ячейки
			echo "<td class=''></td>";
		}

		$nTime = strtotime( "+1 day", $nTime );
	}
	echo "</tr>\r\n";
	$i++;
}
?>