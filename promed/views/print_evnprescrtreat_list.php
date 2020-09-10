<div style="font-weight: bold; font-size: 10px;">Лекарственное лечение:</div>
<?php

function duration($duration, $type) {
	switch($type) {
		case 'дн': $type = 'дней';break;
		case 'нед': $type = 'недель';break;
		case 'мес': $type = 'месяцев';break;
	}
	return $duration.' '.$type;
}
function countInDay($count) {
	if (in_array($count % 10, array(2, 3, 4))) {
		return $count.' раза в день';
	}
	return $count.' раз в день';
}

if (empty($client) || $client != 'ext6') {
	foreach($items as $index => $row) {
		echo '<div style="font-size: 10px;">'.($index+1).'.&nbsp;'.$row['Drug_Info'].'&nbsp;'.$row['EvnPrescr_setDate'];
		if ( !empty($row['EvnPrescrTreatDrug_KolvoEd']) )
			echo  ' По '.$row['EvnPrescrTreatDrug_KolvoEd'].' '.(empty($row['DrugForm_Nick'])?'ед.дозировки':$row['DrugForm_Nick']).' ';
		if ( !empty($row['EvnPrescrTreatDrug_Kolvo']) && empty($row['EvnPrescrTreatDrug_KolvoEd']))
			echo $row['EvnPrescrTreatDrug_Kolvo'].' ';
		if ( !empty($row['Okei_NationSymbol']) && empty($row['EvnPrescrTreatDrug_KolvoEd']))
			echo $row['Okei_NationSymbol'].' ';
		if ( !empty($row['CountInDay']) )
			echo  ' '.$row['CountInDay'].' '.(in_array($row['CountInDay'],array(2,3,4))?'раза':'раз').' в сутки';
		if ( !empty($row['ContReception']))
			echo  ', повторять непрерывно '.$row['ContReception'].' '.$row['DurationTypeN_Nick'];
		if ( !empty($row['Interval']))
			echo  ', перерыв '.$row['Interval'].' '.$row['DurationTypeI_Nick'];
		if ( !empty($row['CourseDuration']) && $row['CourseDuration'] != $row['ContReception'] )
			echo  ', в течение '.$row['CourseDuration'].' '.$row['DurationTypeP_Nick'];
		echo  '.';
		/*
		if ( !empty($row['PrescriptionIntroType_Name']))
			echo  '<br />Метод введения: '.$row['PrescriptionIntroType_Name'];
		if ( !empty($row['PerformanceType_Name']))
			echo  '<br />Исполнение: '.$row['PerformanceType_Name'];
		if ( $row['IsCito'] == 2 )
			echo  '&nbsp;<span style="color: red">Cito!</span>';
		*/
		if ( !empty($row['Descr']) )
			echo  ' '.$row['Descr'];
		echo  '</div>';
	}
} else {
	foreach($items as $index => $row) {
		echo '<div style="font-size: 10px;">'.($index+1).'.&nbsp;'.$row['Drug_Info'].'.&nbsp; Начать с '.$row['EvnPrescr_setDate'];
		if ( !empty($row['PrescriptionIntroType_Name']) ) {
			echo " {$row['PrescriptionIntroType_Name']}";
		}
		if ( !empty($row['CourseDuration']) ) {
			echo " в течение ".duration($row['CourseDuration'], $row['DurationTypeP_Nick']);
		}
		if ( !empty($row['CountInDay']) ) {
			echo " ".countInDay($row['CountInDay']);
		}
		if ( !empty($row['EvnPrescrTreatDrug_KolvoEd']) ) {
			echo " по {$row['EvnPrescrTreatDrug_KolvoEd']} {$row['GoodsUnitS_Name']} за прием";
		}
		echo '.';
		if ( !empty($row['EvnReceptGeneral_Num']) ) {
			$type = mb_strtolower($row['ReceptType_Name']);
			echo " Рецепт {$type} № {$row['EvnReceptGeneral_Ser']} {$row['EvnReceptGeneral_Num']} от {$row['EvnReceptGeneral_begDate']}.";
		}
		if ( !empty($row['Descr']) )
			echo  ' '.$row['Descr'];
		echo  '</div>';
	}
}
?>