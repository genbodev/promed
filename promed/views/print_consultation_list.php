<div style="font-weight: bold; font-size: 10px;">Консультации:</div>
<?php
foreach($items as $row) {
	echo '<div style="font-size: 10px;">по профилю - '.$row['LpuSectionProfile_Name'].' (записан: врач '.$row['MedPersonal_Name'].' '.$row['RecDate'];
	if ( !empty($row['EvnDirection_Num']) )
		echo  '; направление № '.$row['EvnDirection_Num'].' от '.$row['EvnDirection_setDate'];
	else
		echo  '; направлен '.$row['EvnDirection_setDate'];
	echo  ')</div>';
}
?>