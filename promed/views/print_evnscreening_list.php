<div style="font-weight: bold; font-size: 10px;">Исследования:</div>
<table><body>
<?php
foreach($items as $row) {
	echo '<tr><td style="font-size: 10px; width: 20%;" nowrap>'.$row['UslugaComplex_Name_List'].'</td><td style="font-size: 10px; padding-left: 5px;">';
	if ( !empty($row['RecDate']) || !empty($row['EvnDirection_setDate']) )
	{
		echo  '(';
		if ( !empty($row['RecDate']) )
			echo  'запись: '.$row['RecDate'];
		if ( !empty($row['EvnDirection_Num']) )
			echo  ', направление № '.$row['EvnDirection_Num'].' от '.$row['EvnDirection_setDate'];
		else
			echo  ', направлен '.$row['EvnDirection_setDate'];
		echo  ')';
	}
	echo  '</td></tr>';
}
?>
</body></table>