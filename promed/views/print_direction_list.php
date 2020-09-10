<div style="font-weight: bold; font-size: 10px;">Направления:</div>
<?php
foreach($items as $row) {
	echo '<div style="font-size: 10px;">'.$row['DirType_Name'].' (записан: '.$row['LpuSectionProfile_Name'].' '.$row['LpuUnit_Name'].' '.$row['Lpu_Name'];
	if ( !empty($row['RecDate']) )
		echo  ', '.$row['RecDate'];
	if ( !empty($row['EvnDirection_Num']) )
		echo  ', направление № '.$row['EvnDirection_Num'].' от '.$row['EvnDirection_setDate'];
	else
		echo  ', направлен '.$row['EvnDirection_setDate'];
	echo  ')</div>';
}
?>