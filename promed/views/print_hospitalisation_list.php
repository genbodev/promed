<div style="font-weight: bold; font-size: 10px;">Госпитализация:</div>
<?php
foreach($items as $row) {
	echo '<div style="font-size: 10px;">'.$row['DirType_Name'].' по профилю - '.$row['LpuSectionProfile_Name'].' (направлен: '.$row['Lpu_Name'];
	if ( !empty($row['LpuSection_Name']) )
		echo  ', отделение: '.$row['LpuSection_Name'];
	if ( !empty($row['EvnDirection_Num']) )
		echo  ', направление № '.$row['EvnDirection_Num'].' от '.$row['EvnDirection_setDate'];
	if ( !empty($row['RecDate']) )
		echo  '; записан: на '.$row['RecDate'];
	echo  ')</div>';
}
?>