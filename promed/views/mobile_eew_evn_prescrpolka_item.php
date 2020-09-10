<?php 
	if(empty($EvnPrescrGroup_Title))
	{
?>
<tr >
    <td>
		<p><?php 
			switch($PrescriptionType_id){
				case 1://<период действия>  <тип режима> <комментарий> 
					$EvnPrescrItem_id = $EvnPrescrRegime_id;
					$prescriptionRegimeTypeImage = '';
					switch ( $PrescriptionRegimeType_id ) {
						case 1: $prescriptionRegimeTypeImage = 'treatment-common16.png'; break;
						case 2: $prescriptionRegimeTypeImage = 'treatment-semi-bed16.png'; break;
						case 3: $prescriptionRegimeTypeImage = 'treatment-bed16.png'; break;
						case 4: $prescriptionRegimeTypeImage = 'treatment-bed-strict16.png'; break;
					}
					echo '<b>{EvnPrescr_setDate}</b>'.((empty($EvnPrescr_setTime))?'':'&nbsp;<b>{EvnPrescr_setTime}</b>');
					echo '&nbsp;<img src="/img/icons/'. $prescriptionRegimeTypeImage .'" title="Тип режима: '.  htmlspecialchars($PrescriptionRegimeType_Name) .'" />';
					echo (!empty($EvnPrescrRegime_hint) ? '&nbsp;<img src="/img/icons/comment16.png" title="' . htmlspecialchars($EvnPrescrRegime_hint) . '" />':'');
					break;
				case 2://<период действия>  <тип диеты> <комментарий> 
					$EvnPrescrItem_id = $EvnPrescrDiet_id;
					$prescriptionDietTypeImage = 'dietXn16.png';
					$prescriptionDietTypeImage = str_replace('X', $PrescriptionDietType_Code, $prescriptionDietTypeImage);
					$prescriptionDietTypeImage = str_replace('б', 'b', $prescriptionDietTypeImage);
					$prescriptionDietTypeImage = str_replace('c', 's', $prescriptionDietTypeImage);
					$prescriptionDietTypeImage = str_replace('с', 's', $prescriptionDietTypeImage);

					echo '<b>{EvnPrescr_setDate}</b>'.((empty($EvnPrescr_setTime))?'':'&nbsp;<b>{EvnPrescr_setTime}</b>');
					echo '&nbsp;<img src="/img/icons/'. $prescriptionDietTypeImage . '" title="Тип диеты: ' .  htmlspecialchars($PrescriptionDietType_Name) .'" />';
					echo (!empty($EvnPrescrDiet_hint) ? '&nbsp;<img src="/img/icons/comment16.png" title="' . htmlspecialchars($EvnPrescrDiet_hint) . '" />':'');
					break;
				case 5://
					echo '{Drug_Info}&nbsp;{EvnPrescr_setDate} ';
					if ( !empty($EvnPrescrTreatDrug_KolvoEd))
						echo  ' По {EvnPrescrTreatDrug_KolvoEd} '.(empty($DrugForm_Nick)?'ед.дозировки':$DrugForm_Nick).' ';
					if ( !empty($EvnPrescrTreatDrug_Kolvo) && empty($EvnPrescrTreatDrug_KolvoEd) )
						echo  '{EvnPrescrTreatDrug_Kolvo} ';
					if ( !empty($Okei_NationSymbol) && empty($EvnPrescrTreatDrug_KolvoEd) )
						echo  '{Okei_NationSymbol} ';
					if ( !empty($CountInDay))
						echo  '{CountInDay}&nbsp;'.(in_array($CountInDay,array(2,3,4))?'раза':'раз').' в сутки';
					if ( !empty($ContReception))
						echo  ', принимать {ContReception} {DurationTypeN_Nick}';
					if ( !empty($Interval))
						echo  ', перерыв {Interval} {DurationTypeI_Nick}';
					if ( !empty($CourseDuration) && $CourseDuration != $ContReception )
						echo  ', в течение {CourseDuration} {DurationTypeP_Nick}';
					echo  '.';
					if ( !empty($PrescriptionIntroType_Name))
						echo  '<br />Метод введения: {PrescriptionIntroType_Name}';
					if ( !empty($PerformanceType_Name))
						echo  '<br />Исполнение: {PerformanceType_Name}';
					//echo '&nbsp;<span style="text-decoration: underline;">{DrugTorg_Name}</span>&nbsp;<img src="/img/icons/dlo16.png" title="{Drug_Name}"/>';
					if ( $IsCito_Code == 1 )
						echo  '&nbsp;<span style="color: red">Cito!</span>';
					break;
				case 6://
					echo '{Usluga_List}&nbsp;{EvnPrescr_setDate}';
					if ( !empty($CountInDay))
						echo  ' {CountInDay} '.(in_array($CountInDay,array(2,3,4))?'раза':'раз').' в сутки';
					if ( !empty($ContReception))
						echo  ', повторять непрерывно {ContReception} {DurationTypeN_Nick}';
					if ( !empty($Interval))
						echo  ', перерыв {Interval} {DurationTypeI_Nick}';
					if ( !empty($CourseDuration) && $CourseDuration != $ContReception )
						echo  ', всего {CourseDuration} {DurationTypeP_Nick}';
					echo  '.';
					if ( $IsCito_Code == 1 )
						echo  '&nbsp;<span style="color: red">Cito!</span>';
					break;
				case 7://также как 12
				case 11://также как 12
				case 12:
					echo '{Usluga_List}&nbsp;{EvnPrescr_setDate}';
					if ( $IsCito_Code == 1 )
						echo  '&nbsp;<span style="color: red">Cito!</span>';
					break;
			}
			echo (!empty($EvnPrescr_hint) ? '&nbsp;<img src="/img/icons/comment16.png" title="' . htmlspecialchars($EvnPrescr_hint) . '" />':'');
		?></p>
    </td>
</tr>
<?php 
	}
	else
	{
?>
<tr >
    <td><span style="color: green">{EvnPrescrGroup_Title}</span></td>
</tr>
<?php 
	}
?>
