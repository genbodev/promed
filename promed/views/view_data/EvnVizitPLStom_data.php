<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
	$is_anonym = (!empty($Person_IsAnonym) && $Person_IsAnonym == 2);
	$is_morbus = (strtotime($EvnPLStom_setDate) >= getEvnPLStomNewBegDate());
	$xdate = strtotime('01.01.2016'); // для Перми поле появляется с 01.01.2016
	if (getRegionNick() != 'perm') {
		$xdate = getEvnPLStomNewBegDate(); // для остальных зависит от даты нового стомат.тап
	}
?>
<div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}" class="columns">
    <div class="left">
        <div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_content">
            <div class="text">
               <p>Дата: {EvnVizitPLStom_setDate} {EvnVizitPLStom_setTime}
			   <br />Отделение: {LpuSection_Name}</p>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Врач: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputMedStaffFact" style='color:#000;' class="link" dataid='{MedStaffFact_id}'><?php } echo empty($MedPersonal_Fin)?$empty_str:'{MedPersonal_Fin}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaMedStaffFact" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php
				$fieldHtml = "<div style='clear:both;'>
					<div style='float:left;padding:5px 0;'>Профиль: ";
				if ($is_allow_edit && !in_array(getRegionNumber(), array(2, 66))) {
					$fieldHtml .= "<span id='EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputLpuSectionProfile' style='color:#000;' class='link' dataid='{LpuSectionProfile_id}'>";
				}
				if (empty($LpuSectionProfile_Name)) {
					$fieldHtml .= $empty_str;
				} else {
					$fieldHtml .= '{LpuSectionProfile_Code}. {LpuSectionProfile_Name}';
				}
				if ($is_allow_edit && !in_array(getRegionNumber(), array(2, 66))) {
					$fieldHtml .= "</span>";
				}
				$fieldHtml .= '</div>
					<div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaLpuSectionProfile" class="input-area" style="float:left; margin-left:5px; display: none"></div>
				</div>';
				echo $fieldHtml;
	            ?>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Сред. м./персонал: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputSMedPersonal" style='color:#000;' class="link" dataid='{MedPersonal_sid}'><?php } echo empty($MedPersonal_sFin)?$empty_str:'{MedPersonal_sFin}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaSMedPersonal" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
	            <?php if (strtotime($EvnPLStom_setDate) >= $xdate && getRegionNumber() != 10) { ?>
					<div style='clear:both;'><div style='float:left;padding:5px 0;'><?php echo (getRegionNumber() == 101)?'Повод':'Вид'; ?> обращения: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputTreatmentClass" style='color:#000;' class="link" dataid='{TreatmentClass_id}'><?php } echo empty($TreatmentClass_Name)?$empty_str:'{TreatmentClass_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaTreatmentClass" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php } ?>
				<?php if (getRegionNick() == 'kz'){ ?>
					<div style='clear:both; display: <?php echo ($TreatmentClass_id == 22 || $TreatmentClass_id == 30)?'block':'none' ?>' id='blockVizitActiveType'>
						<div style='float:left;padding:5px 0;'>
							Вид активного посещения: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputVizitActiveType" style='color:#000;' class="link" dataid='{VizitActiveType_id}'><?php } echo empty($VizitActiveType_Name)?$empty_str:'{VizitActiveType_Name}'; if($is_allow_edit) { ?></span><?php } ?>
						</div>
						<div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaVizitActiveType" class="input-area" style="float:left; margin-left:5px; display: none">
						</div>
					</div>
				<?php } ?>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Место: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputServiceType" style='color:#000;' class="link" dataid='{ServiceType_id}'><?php } echo empty($ServiceType_Name)?$empty_str:'{ServiceType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaServiceType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <div style='clear:both;'><div style='float:left;padding:5px 0;'>Прием: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputVizitClass" style='color:#000;' class="link" dataid='{VizitClass_id}'><?php } echo empty($VizitClass_Name)?$empty_str:'{VizitClass_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaVizitClass" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <div class="data-row-container" id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_wrapIsPrimaryVizit">
                    <div class="data-row">Первично в текущем году: <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputIsPrimaryVizit" class="link"<?php } ?> dataid='{EvnVizitPLStom_IsPrimaryVizit}'><?php echo empty($IsPrimaryVizit_Name) ? $empty_str : '{IsPrimaryVizit_Name}'; ?></span></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaIsPrimaryVizit" class="input-area"></div>
                </div>
                <div style='clear:both;'>
                    <div style='float:left;padding:5px 0;'>Цель посещения: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputVizitType" style='color:#000;' class="link" dataid='{VizitType_id}'><?php } echo empty($VizitType_Name)?$empty_str:'{VizitType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div>
                    <div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaVizitType" class="input-area" style="float:left; margin-left:5px; display: none"></div>
                    <div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_wrapProfGoal" style='margin-left: 5px; float:left; padding:5px 0; display: <?php
		            if ($VizitType_SysNick == 'prof') {
			            ?>block<?php
		            } else {
			            ?>none<?php
		            } ?>;'><?php
			            if($is_allow_edit) {
				            ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputProfGoal" style='color:#000;' class="link" dataid='{ProfGoal_id}'><?php
			            }
			            echo empty($ProfGoal_Name)?$empty_str:'{ProfGoal_Name}';
			            if($is_allow_edit) {
				            ?></span><?php
			            }
			            ?></div>
                    <div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaProfGoal" class="input-area" style="float:left; margin-left:5px; display: none"></div>
					<div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_wrapDispProfGoalType" style='margin-left: 5px; float:left; padding:5px 0; display: <?php
                        if ($VizitType_SysNick == 'disp') {
	                        ?>block<?php
                        } else {
	                        ?>none<?php
                        } ?>;'><?php
		                if($is_allow_edit) {
			                ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputDispProfGoalType" style='color:#000;' class="link" dataid='{DispProfGoalType_id}'><?php
		                }
		                echo empty($DispProfGoalType_Name)?$empty_str:'{DispProfGoalType_Name}';
		                if($is_allow_edit) {
			                ?></span><?php
		                }
		                ?></div>
                    <div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaDispProfGoalType" class="input-area" style="float:left; margin-left:5px; display: none"></div>
                    <div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaVizitType" class="input-area" style="float:left; margin-left:5px; display: none"></div>
                </div><?php
	            if (in_array(getRegionNumber(), array(66)))
	            {
		            $fieldHtml = "<div style='clear:both;'>
		                <div style='float:left;padding:5px 0;'>МЭС: ";
		            if ($is_allow_edit && $EvnVizitPLStom_Index <= 0) {
			            $fieldHtml .= "<span id='EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputMesOldVizit' style='color:#000;' class='link' dataid='{Mes_id}'>";
		            }
		            if (empty($Mes_Code)) {
			            $fieldHtml .= $empty_str;
		            } else {
			            $fieldHtml .= '{Mes_Code}. {Mes_Name}';
		            }
		            if ($is_allow_edit && $EvnVizitPLStom_Index <= 0) {
			            $fieldHtml .= "</span>";
		            }
		            $fieldHtml .= '</div>
		                <div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaMesOldVizit" class="input-area" style="float:left; margin-left:5px; display: none"></div>
		            </div>';
		            echo $fieldHtml;
	            }
				if (in_array(getRegionNumber(), array(2, 60, 66, 101)) || (getRegionNumber() == 59 && $is_morbus === true))
	            {
		            $fieldUslugaComplex = "<div style='clear:both;'><div style='float:left;padding:5px 0;'>Код" . (getRegionNumber() == 101 ? ' услуги ' : ' ') . "посещения: ";
		            if ($is_allow_edit) {
			            $fieldUslugaComplex .= "<span id='EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputUslugaComplex' style='color:#000;' class='link' dataid='{UslugaComplex_uid}'>";
		            }
		            if (empty($UslugaComplex_Name)) {
			            $fieldUslugaComplex .= $empty_str;
		            } else {
			            $fieldUslugaComplex .= '{UslugaComplex_Code}. {UslugaComplex_Name}';
		            }
		            if ($is_allow_edit) {
			            $fieldUslugaComplex .= "</span>";
		            }
		            $fieldUslugaComplex .= '</div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaUslugaComplex" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>';
		            echo $fieldUslugaComplex;
	            }
                if (getRegionNick() === 'kz') {
                    $fieldUslugaMedType = "<div style='clear:both;'><div style='float:left;padding:5px 0;'>Вид услуги: ";
                    if ($is_allow_edit) {
                        $fieldUslugaMedType .= "<span id='EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputUslugaMedType' style='color:#000;' class='link' dataid='{UslugaMedType_id}'>";
                    }
                    if (empty($UslugaMedType_Name)) {
                        $fieldUslugaMedType .= $empty_str;
                    } else {
                        $fieldUslugaMedType .= '{UslugaMedType_Code}. {UslugaMedType_Name}';
                    }
                    if ($is_allow_edit) {
                        $fieldUslugaMedType .= "</span>";
                    }
                    $fieldUslugaMedType .= '</div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaUslugaMedType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>';
                    echo $fieldUslugaMedType;
                }
				if (getRegionNumber() == 59 && strtotime($LastEvnVizitPLStom_setDate) >= strtotime('01.11.2015'))
	            {
		            $fieldHtml = "<div style='clear:both;'><div style='float:left;padding:5px 0;'>Тариф: ";
		            if ($is_allow_edit) {
			            $fieldHtml .= "<span id='EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputUslugaComplexTariff' style='color:#000;' class='link' dataid='{UslugaComplexTariff_id}'>";
		            }
		            if (empty($UslugaComplexTariff_Name)) {
			            $fieldHtml .= $empty_str;
		            } else {
			            $fieldHtml .= '{UslugaComplexTariff_Name}';
		            }
		            if ($is_allow_edit) {
			            $fieldHtml .= "</span>";
		            }
		            $fieldHtml .= '</div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaUslugaComplexTariff" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>';
		            echo $fieldHtml;
				}
				if (
					(getRegionNumber() == 59 && strtotime($LastEvnVizitPLStom_setDate) >= strtotime('01.11.2015'))
					||
					(getRegionNumber() == 60 && strtotime($LastEvnVizitPLStom_setDate) >= strtotime('01.01.2018'))
				) {
		            $fieldHtml = "<div style='clear:both;'><div style='float:left;padding:5px 0;'>УЕТ врача: ";
		            if ($is_allow_edit) {
			            $fieldHtml .= "<span id='EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputEvnUslugaStom_UED' style='color:#000;' dataid='{EvnUslugaStom_UED}'>";
		            }
		            if (empty($EvnUslugaStom_UED)) {
			            $fieldHtml .= $empty_str;
		            } else {
			            $fieldHtml .= '{EvnUslugaStom_UED}';
		            }
		            if ($is_allow_edit) {
			            $fieldHtml .= "</span>";
		            }
		            $fieldHtml .= '</div></div>';
		            echo $fieldHtml;
				}
	            ?>

				<?php if(getRegionNumber() == 101) { ?>
                    <div style='float:left;padding:5px 0;clear:both;'>
                        <label>Платное посещение</label>
                        <input type="checkbox" <?php echo $PayType_id == 153 ? 'checked' : '' ?> id="isPaidVisit" style='vertical-align: middle;' onclick='Ext.getCmp("PersonEmkForm").kzCheckbox("EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputPayType","{EvnVizitPLStom_id}",true);'/>
                    </div>
				<?php } ?>

                <div style='clear:both;'><div style='float:left;padding:5px 0;'><?php echo (getRegionNumber() == 101)?'Источник финансирования':'Вид оплаты'?>: <?php if($is_allow_edit && !$is_anonym) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputPayType" style='color:#000;' class="<?php echo (getRegionNumber() == 101)?'':'link'?>" dataid='{PayType_id}'><?php } echo empty($PayType_Name)?$empty_str:'{PayType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaPayType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <?php if(getRegionNumber() == 101) { ?>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Тип оплаты: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputPayTypeKAZ" style='color:#000;' class="link" dataid='{PayTypeKAZ_id}'><?php } echo empty($PayTypeKAZ_Name)?$empty_str:'{PayTypeKAZ_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaPayTypeKAZ" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php } ?>
                <div style='clear:both;'><div style='float:left;padding:5px 0;'>Карта дис.учета: <?php if($is_allow_edit && !$is_anonym) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputPersonDisp" style='color:#000;' class="link" dataid='{PersonDisp_id}'><?php } echo empty($PersonDisp_Name)?$empty_str:'{PersonDisp_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaPersonDisp" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <?php
				if (getRegionNick() != 'kz' && strtotime($EvnPLStom_setDate) >= $xdate) {
					$fieldMedicalCareKind = "<div style='clear:both;'><div style='float:left;padding:5px 0;'>Вид мед. помощи: ";
					if ( !in_array(getRegionNick(), array('ekb', 'kareliya', 'ufa')) ) {
						$fieldMedicalCareKind .= "<span id='EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputMedicalCareKind' style='color:#000;' class='link' dataid='{MedicalCareKind_id}'>";
					}
					if (empty($MedicalCareKind_Name)) {
						$fieldMedicalCareKind .= $empty_str;
					} else {
						$fieldMedicalCareKind .= '{MedicalCareKind_Name}';
					}
					if ( !in_array(getRegionNick(), array('ekb', 'kareliya', 'ufa')) ) {
						$fieldMedicalCareKind .= "</span>";
					}
					$fieldMedicalCareKind .= '</div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaMedicalCareKind" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>';
					echo $fieldMedicalCareKind;
				}

				?>
				<?php
				if ( $is_morbus === false ) {
				?>
                <div style='clear:both;'><div style='float:left;padding:5px 0;'>Основной диагноз:
				<?php 
					switch (true) {
						case (!empty($DiagFedMes_FileName) && file_exists($_SERVER['DOCUMENT_ROOT'].'/promed/views/federal_mes/'.$DiagFedMes_FileName.'.htm')):
						case ( !empty($CureStandart_Count) ):
							echo '<span id="EvnVizitPLStom_{EvnVizitPLStom_id}_DiagCode"><span id="EvnVizitPLStom_{EvnVizitPLStom_id}_showFm" class="link" title="' . (getRegionNick() == 'kz' ? 'Показать протокол лечения по этому диагнозу' : 'Показать федеральный '.getMESAlias().' по этому диагнозу') . '">{Diag_Code}</span></span> ';
							break;
						default:
							echo '<span id="EvnVizitPLStom_{EvnVizitPLStom_id}_DiagCode">{Diag_Code}</span> ';
							break;
					}
					$diag_str = $empty_str;
					if (!empty($Diag_Name))
					{
						$diag_str = '{Diag_Name}.';
					}
					if($is_allow_edit)
					{
						echo '<span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputDiag" style="color:#000;" class="link" dataid="{Diag_id}">' . $diag_str . '</span>';
					}
					else
					{
						echo $diag_str;
					}
				?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaDiag" class="input-area" style="float:left; margin-left:5px; display: none"></div>
				<!--div><a id="EvnVizitPLStom_{EvnVizitPLStom_id}_addEvnInfectNotify" class="button icon icon-add16" style="float:left; margin-left:10px; display: none" title="Добавить экстренное извещение об инфекционном заболевании, отравлении"><span></span></a></div-->
				<div id="EvnVizitPLStom_{EvnVizitPLStom_id}_addEvnInfectNotifyTools" class="toolbar right" style="width:1%;display: <?php if (empty($isDisabledAddEvnInfectNotify)) { ?>block<?php } else { ?>none<?php } ?>;">
					<a id="EvnVizitPLStom_{EvnVizitPLStom_id}_addEvnInfectNotify" class="button icon icon-add16" title="Добавить экстренное извещение об инфекционном заболевании, отравлении"><span></span></a>
				</div>
				<div id="EvnVizitPLStom_{EvnVizitPLStom_id}_addEvnNotifyVenerTools" class="toolbar right" style="width:1%;display: <?php if (empty($isDisabledAddEvnNotifyVener)) { ?>block<?php } else { ?>none<?php } ?>;">
					<a id="EvnVizitPLStom_{EvnVizitPLStom_id}_addEvnNotifyVener" class="button icon icon-add16" title="Создать Извещение о больном венерическим заболеванием"><span></span></a>
				</div>
				</div>
                <div style='clear:both;'><div style='float:left;padding:5px 0;'>Характер заболевания: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputDeseaseType" style='color:#000;' class="link" dataid='{DeseaseType_id}'><?php } echo empty($DeseaseType_Name)?$empty_str:'{DeseaseType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaDeseaseType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <div style='clear:both;'><div style='float:left;padding:5px 0;'>Зуб: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputTooth" style='color:#000;' class="link" dataid='{Tooth_id}'><?php } echo empty($Tooth_Code)?$empty_str:'{Tooth_Code}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaTooth" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_wrapToothSurface" style='clear:both; display: <?php echo empty($Tooth_Code)?'none':'block'; ?>;'>
	                <div style='float:left;padding:5px 0;'>Поверхности: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputToothSurface" style='color:#000;' class="link"><?php } echo empty($ToothSurfaceType_list)?$empty_str:'{ToothSurfaceType_list}'; if($is_allow_edit) { ?></span><?php } ?></div>
	                <div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaToothSurface" class="input-area" style="float:left; margin-left:5px; display: none"></div>
                </div>
	            <?php
	            if (in_array(getRegionNumber(), array(59)))
	            {
		            ?><div style='clear:both;'><div style='float:left;padding:5px 0;'>МЭС: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputMes" style='color:#000;' class="link" dataid='{Mes_id}'><?php } echo empty($Mes_Code)?$empty_str:'{Mes_Code}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaMes" class="input-area" style="float:left; margin-left:5px; display: none"></div></div><?php
	            }
	            ?>
                <div style='clear:both;'><div style='float:left;padding:5px 0;'>УЕТ (факт): <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputUet" style='color:#000;' class="link"><?php } echo empty($EvnVizitPLStom_Uet)?$empty_str:'{EvnVizitPLStom_Uet}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaUet" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <div style='clear:both;'><div style='float:left;padding:5px 0;'>УЕТ (факт по ОМС): <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputUetOMS" style='color:#000;' class="link"><?php } echo empty($EvnVizitPLStom_UetOMS)?$empty_str:'{EvnVizitPLStom_UetOMS}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaUetOMS" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
	            <?php
	            if (in_array(getRegionNumber(), array(59)))
	            {
		            ?><div style='clear:both;'><div style='float:left;padding:5px 0;'>УЕТ (норматив по МЭС): <span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_MesUet"><?php echo (empty($EvnVizitPLStom_MesUet))?$empty_str:'{EvnVizitPLStom_MesUet}'; ?></span></div></div><?php
	            }
	            ?>
				<?php
				} else {
					// для заболеваний нужно поле диагноз (комбобокс) refs #49932
				?>
					<div style='clear:both;'><div style='float:left;padding:5px 0;'>Основной диагноз: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputDiagnew" style='color:#000;' class="link" dataid='{Diag_id}'><?php } echo empty($Diag_Code)?$empty_str:'{Diag_Code}. {Diag_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaDiagnew" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <?php if(getRegionNumber() == 2) { ?>
					<div style='clear:both;'><div style='float:left;padding:5px 0px;'>Группа здоровья: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputHealthKind" style='color:#000;' class="link" dataid='{HealthKind_id}'><?php } echo empty($HealthKind_Name)?$empty_str:'{HealthKind_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaHealthKind" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <?php } ?>
				<?php
				}
				?>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Прикус: <?php if($is_allow_edit) { ?><span id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputBitePersonType" style='color:#000;' class="link" dataid='{BitePersonType_id}'><?php } echo empty($BitePersonType_Name)?$empty_str:'{BitePersonType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_inputareaBitePersonType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
            </div>
        </div>
    </div>
    <div class="right">
        <div id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_toolbar" class="toolbar" style="display: none">
            <a id="EvnVizitPLStom_data_{EvnVizitPLStom_id}_editEvnVizitPLStom" class="button icon icon-edit16" title="Редактировать стат.данные посещения"><span></span></a>
        </div>
    </div>
</div>
