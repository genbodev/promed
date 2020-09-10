<div style="font-weight: bold; font-size: 10px;">Манипуляции и процедуры:</div>
<?php
foreach($items as $row) {
	echo '<div style="font-size: 10px;">'.$row['Usluga_List'].'&nbsp;'.$row['EvnPrescr_setDate'];
	if ( !empty($row['CountInDay']) )
		echo  ' '.$row['CountInDay'].' '.(in_array($row['CountInDay'],array(2,3,4))?'раза':'раз').' в сутки';
	if ( !empty($row['ContReception']))
		echo  ', повторять непрерывно '.$row['ContReception'].' '.$row['DurationTypeN_Nick'];
	if ( !empty($row['Interval']))
		echo  ', перерыв '.$row['Interval'].' '.$row['DurationTypeI_Nick'];
	if ( !empty($row['CourseDuration']) && $row['CourseDuration'] != $row['ContReception'] )
		echo  ', всего '.$row['CourseDuration'].' '.$row['DurationTypeP_Nick'];
	echo  '.';
	if ( $row['IsCito'] == 2 )
		echo  '&nbsp;<span style="color: red">Cito!</span>';
	if ( !empty($row['Descr']) )
		echo  ' '.$row['Descr'];
	echo  '</div>';
}
?>