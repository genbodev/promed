<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
	$isMorbusOnkoExists = (2 == $isMorbusOnkoExists);
	$is_anonym = (!empty($Person_IsAnonym) && $Person_IsAnonym == 2);
	$dateXAdult = '2017-07-21';
	$dateXChild = '2017-07-24';
	$dateX20170901 = '2017-09-01';
	$dateX20180601 = '2018-06-01';
	$dateX20181101 = '2018-11-01';
?> 
<div id="EvnVizitPL_data_{EvnVizitPL_id}" class="columns">
    <div class="left">
        <div id="EvnVizitPL_data_{EvnVizitPL_id}_content">
            <div class="text">
               <p>Дата: {EvnVizitPL_setDate} {EvnVizitPL_setTime}
			   <br />Отделение: {LpuSection_Name}</p>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Врач: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputMedStaffFact" style='color:#000;' class="link" dataid='{MedStaffFact_id}'><?php } echo empty($MedPersonal_Fin)?$empty_str:'{MedPersonal_Fin}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaMedStaffFact" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Сред. м./персонал: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputSMedPersonal" style='color:#000;' class="link" dataid='{MedPersonal_sid}'><?php } echo empty($MedPersonal_sFin)?$empty_str:'{MedPersonal_sFin}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaSMedPersonal" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <?php if (strtotime($LastEvnVizitPL_setDate) >= strtotime('01.01.2016') && getRegionNumber() != 10) { ?>
					<div style='clear:both;'><div style='float:left;padding:5px 0;'><?php echo (getRegionNumber() == 101)?'Повод':'Вид'; ?> обращения:<?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputTreatmentClass" style='color:#000;' class="link" dataid='{TreatmentClass_id}'><?php } echo empty($TreatmentClass_Name)?$empty_str:'{TreatmentClass_Name}';if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaTreatmentClass" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php } ?>
                <?php if(getRegionNumber() == 101) { ?>
				<div style='clear:both; display: <?php echo $TreatmentClass_id == 29 ? 'block' : 'none' ?>'>
					<div style='float:left;padding:5px 0;'>Вид скрининга: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputScreenType" style='color:#000;' class="link" dataid='{ScreenType_id}'><?php } echo empty($ScreenType_Name)?$empty_str:'{ScreenType_Name}';if($is_allow_edit) { ?></span><?php } ?></div>
					<div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaScreenType" class="input-area" style="float:left; margin-left:5px; display: none"></div>
				</div>
				<?php } ?>
				<?php if (getRegionNick() == 'kz'){ ?>
					<div style='clear:both; display: <?php echo ($TreatmentClass_id == 22 || $TreatmentClass_id == 30)?'block':'none' ?>' id='blockVizitActiveType'>
						<div style='float:left;padding:5px 0'>
							Вид активного посещения: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputVizitActiveType" style='color:#000;' class="link" dataid='{VizitActiveType_id}'><?php } echo empty($VizitActiveType_Name)?$empty_str:'{VizitActiveType_Name}'; if($is_allow_edit) { ?></span><?php } ?>
						</div>
						<div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaVizitActiveType" class="input-area" style="float:left; margin-left:5px; display: none">
						</div>
					</div>
                <?php } ?>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Место: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputServiceType" style='color:#000;' class="link" dataid='{ServiceType_id}'><?php } echo empty($ServiceType_Name)?$empty_str:'{ServiceType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaServiceType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <div style='clear:both;'><div style='float:left;padding:5px 0;'>Прием: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputVizitClass" style='color:#000;' class="link" dataid='{VizitClass_id}'><?php } echo empty($VizitClass_Name)?$empty_str:'{VizitClass_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaVizitClass" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <div style='clear:both;'>
	                <div style='float:left;padding:5px 0;'>Цель посещения: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputVizitType" style='color:#000;' class="link" dataid='{VizitType_id}'><?php } echo empty($VizitType_Name)?$empty_str:'{VizitType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div>
	                <div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaVizitType" class="input-area" style="float:left; margin-left:5px; display: none"></div>
                    <div id="EvnVizitPL_data_{EvnVizitPL_id}_wrapProfGoal" style='margin-left: 5px; float:left; padding:5px 0; display: <?php
                        if ($VizitType_SysNick == 'prof') {
	                        ?>block<?php
                        } else {
	                        ?>none<?php
                        } ?>;'><?php
		                if($is_allow_edit) {
			                ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputProfGoal" style='color:#000;' class="link" dataid='{ProfGoal_id}'><?php
		                }
		                echo empty($ProfGoal_Name)?$empty_str:'{ProfGoal_Name}';
		                if($is_allow_edit) {
			                ?></span><?php
		                }
		                ?></div>
                    <div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaProfGoal" class="input-area" style="float:left; margin-left:5px; display: none"></div>
	                <div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaVizitType" class="input-area" style="float:left; margin-left:5px; display: none"></div>

                </div>
	            <?php
				if (in_array(getRegionNumber(), array(66)))
	            {
					$fieldHtml = "<div style='clear:both;'>
                        <div style='float:left;padding:5px 0;'>МЭС: ";
		            if ($is_allow_edit && $EvnVizitPL_Index <= 0) {
			            $fieldHtml .= "<span id='EvnVizitPL_data_{EvnVizitPL_id}_inputMesOldVizit' style='color:#000;' class='link' dataid='{Mes_id}'>";
		            }
		            if (empty($Mes_Code)) {
			            $fieldHtml .= $empty_str;
		            } else {
			            $fieldHtml .= '{Mes_Code}. {Mes_Name}';
		            }
		            if ($is_allow_edit && $EvnVizitPL_Index <= 0) {
			            $fieldHtml .= "</span>";
		            }
		            $fieldHtml .= '</div>
		                <div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaMesOldVizit" class="input-area" style="float:left; margin-left:5px; display: none"></div>
		            </div>';
		            echo $fieldHtml;
	            }

				if (getRegionNick() != 'kz' && strtotime($LastEvnVizitPL_setDate) >= strtotime('01.01.2016')) {
					$fieldMedicalCareKind = "<div style='clear:both;'><div style='float:left;padding:5px 0;'>Вид мед. помощи: ";
					if (!in_array(getRegionNumber(), array(2,10,66))) $fieldMedicalCareKind .= "<span id='EvnVizitPL_data_{EvnVizitPL_id}_inputMedicalCareKind' style='color:#000;' class='link' dataid='{MedicalCareKind_id}'>";
					if (empty($MedicalCareKind_Name)) {
						$fieldMedicalCareKind .= $empty_str;
					} else {
						$fieldMedicalCareKind .= '{MedicalCareKind_Name}';
					}
					if (!in_array(getRegionNumber(), array(2,10,66))) $fieldMedicalCareKind .= "</span>";
					$fieldMedicalCareKind .= '</div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaMedicalCareKind" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>';
					echo $fieldMedicalCareKind;
				}

	            if (in_array(getRegionNumber(), array(2, 35, 60, 66, 3, 59, 101)))
	            {
		            $fieldUslugaComplex = "<div style='clear:both;'><div style='float:left;padding:5px 0;'>Код" . (getRegionNumber() == 101 ? ' услуги ' : ' ') . "посещения: ";
		            if ($is_allow_edit && (getRegionNumber() != 60 || $EvnVizitPL_Index <= 0)) {
			            $fieldUslugaComplex .= "<span id='EvnVizitPL_data_{EvnVizitPL_id}_inputUslugaComplex' style='color:#000;' class='link' dataid='{UslugaComplex_uid}'>";
		            }
		            if (empty($UslugaComplex_Name)) {
			            $fieldUslugaComplex .= $empty_str;
		            } else {
			            $fieldUslugaComplex .= '{UslugaComplex_Code}. {UslugaComplex_Name}';
		            }
					if ($is_allow_edit && (getRegionNumber() != 60 || $EvnVizitPL_Index <= 0)) {
			            $fieldUslugaComplex .= "</span>";
		            }
		            $fieldUslugaComplex .= '</div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaUslugaComplex" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>';
		            echo $fieldUslugaComplex;
	            }

	            if (getRegionNick() === 'kz') {
                    $fieldUslugaMedType = "<div style='clear:both;'><div style='float:left;padding:5px 0;'>Вид услуги: ";
                    if ($is_allow_edit) {
                        $fieldUslugaMedType .= "<span id='EvnVizitPL_data_{EvnVizitPL_id}_inputUslugaMedType' style='color:#000;' class='link' dataid='{UslugaMedType_id}'>";
                    }
                    if (empty($UslugaMedType_Name)) {
                        $fieldUslugaMedType .= $empty_str;
                    } else {
                        $fieldUslugaMedType .= '{UslugaMedType_Code}. {UslugaMedType_Name}';
                    }
                    if ($is_allow_edit) {
                        $fieldUslugaMedType .= "</span>";
                    }
                    $fieldUslugaMedType .= '</div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaUslugaMedType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>';
                    echo $fieldUslugaMedType;
                }

	            if (in_array(getRegionNumber(), array(2)))
	            {
		            $fieldDispProfGoalType = "<div style='clear:both;'><div style='float:left;padding:5px 0;'>В рамках дисп./мед.осмотра: ";
		            if ($is_allow_edit) {
			            $fieldDispProfGoalType .= "<span id='EvnVizitPL_data_{EvnVizitPL_id}_inputDispProfGoalType' style='color:#000;' class='link' dataid='{DispProfGoalType_id}'>";
		            }
		            if (empty($DispProfGoalType_Name)) {
			            $fieldDispProfGoalType .= $empty_str;
		            } else {
			            $fieldDispProfGoalType .= '{DispProfGoalType_Name}';
		            }
					if ($is_allow_edit) {
			            $fieldDispProfGoalType .= "</span>";
		            }
		            $fieldDispProfGoalType .= '</div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaDispProfGoalType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>';
		            echo $fieldDispProfGoalType;
	            }
	            /*if (in_array(getRegionNumber(), array(66)))
	            {
					$fieldHtml = "<div style='clear:both;'>
		                <div style='float:left;padding:5px 0;'>Профиль: ";
		            if ($is_allow_edit) {
			            $fieldHtml .= "<span id='EvnVizitPL_data_{EvnVizitPL_id}_inputLpuSectionProfile' style='color:#000;' class='link' dataid='{LpuSectionProfile_id}'>";
		            }
		            if (empty($LpuSectionProfile_Name)) {
			            $fieldHtml .= $empty_str;
		            } else {
			            $fieldHtml .= '{LpuSectionProfile_Code}. {LpuSectionProfile_Name}';
		            }
		            if ($is_allow_edit) {
			            $fieldHtml .= "</span>";
		            }
		            $fieldHtml .= '</div>
		                <div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaLpuSectionProfile" class="input-area" style="float:left; margin-left:5px; display: none"></div>
		            </div>';
		            echo $fieldHtml;
	            }*/
				
				$fieldHtml = "<div style='clear:both;'>
					<div style='float:left;padding:5px 0;'>Профиль: ";
				if ($is_allow_edit && !in_array(getRegionNumber(), array(2, 66))) {
					$fieldHtml .= "<span id='EvnVizitPL_data_{EvnVizitPL_id}_inputLpuSectionProfile' style='color:#000;' class='link' dataid='{LpuSectionProfile_id}'>";
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
					<div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaLpuSectionProfile" class="input-area" style="float:left; margin-left:5px; display: none"></div>
				</div>';
				echo $fieldHtml;
				
	            ?>
				<div style='clear:both; display: <?php echo (getRegionNumber() == 30 && $VizitType_SysNick == 'cz' && !($Person_Age_On_Vizit_Date >= 18 && $EvnVizitPL_setDate120 >= $dateXAdult) && !($Person_Age_On_Vizit_Date < 18 && $EvnVizitPL_setDate120 >= $dateXChild))?'block':'none'?>;' id="EvnVizitPL_data_{EvnVizitPL_id}_wrapRiskLevel"><div style='float:left;padding:5px 0px;'>Фактор риска: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputRiskLevel" style='color:#000;' class="link" dataid='{RiskLevel_id}'><?php } echo empty($RiskLevel_Name)?$empty_str:'{RiskLevel_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaRiskLevel" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div style='clear:both; display: <?php echo (getRegionNumber() == 30 && $VizitType_SysNick == 'cz' && $Person_Age_On_Vizit_Date >= 2 && $Person_Age_On_Vizit_Date < 18 && $EvnVizitPL_setDate120 >= $dateXChild)?'block':'none'?>;' id="EvnVizitPL_data_{EvnVizitPL_id}_wrapWellnessCenterAgeGroups"><div style='float:left;padding:5px 0px;'>Группа ЦЗ: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputWellnessCenterAgeGroups" style='color:#000;' class="link" dataid='{WellnessCenterAgeGroups_id}'><?php } echo empty($WellnessCenterAgeGroups_Name)?$empty_str:'{WellnessCenterAgeGroups_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaWellnessCenterAgeGroups" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>

				<?php if(getRegionNumber() == 101) { ?>
                <div style='float:left;padding:5px 0;clear:both;'>
                    <label>Платное посещение</label>
                    <input type="checkbox" <?php echo $PayType_id == 153 ? 'checked' : '' ?> id="isPaidVisit" style='vertical-align: middle;' onclick='Ext.getCmp("PersonEmkForm").kzCheckbox("EvnVizitPL_data_{EvnVizitPL_id}_inputPayType","{EvnVizitPL_id}",true);'/>
                </div>
				<?php } ?>

                <div style='clear:both;'><div style='float:left;padding:5px 0;'><?php echo (getRegionNumber() == 101)?'Источник финансирования':'Вид оплаты'?>: <?php if($is_allow_edit && !$is_anonym) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputPayType" style='color:#000;' class="<?php echo (getRegionNumber() == 101)?'':'link'?>" dataid='{PayType_id}'><?php } echo empty($PayType_Name)?$empty_str:'{PayType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaPayType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>

                <?php if(getRegionNumber() == 101) { ?>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Тип оплаты: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputPayTypeKAZ" style='color:#000;' class="link" dataid='{PayTypeKAZ_id}'><?php } echo empty($PayTypeKAZ_Name)?$empty_str:'{PayTypeKAZ_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaPayTypeKAZ" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php } ?>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Карта дис.учета: <?php if($is_allow_edit && !$is_anonym) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputPersonDisp" style='color:#000;' class="link" dataid='{PersonDisp_id}'><?php } echo empty($PersonDisp_Name)?$empty_str:'{PersonDisp_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaPersonDisp" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div style='clear:both;'></div>
				<div class="columns" onmouseover="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnVizitPL_{EvnVizitPL_id}_addEvnInfectNotify').style.display='block'; } " onmouseout="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnVizitPL_{EvnVizitPL_id}_addEvnInfectNotify').style.display='none'; }">
					<div style='padding:5px 0;width: 99%' class="left">Основной диагноз: <?php
						switch (true) {
							case (!empty($DiagFedMes_FileName) && file_exists($_SERVER['DOCUMENT_ROOT'].'/promed/views/federal_mes/'.$DiagFedMes_FileName.'.htm')):
							case ( !empty($CureStandart_Count) ):
								echo '<span id="EvnVizitPL_{EvnVizitPL_id}_DiagCode"><span id="EvnVizitPL_{EvnVizitPL_id}_showFm" class="link" title="' . (getRegionNick() == 'kz' ? 'Показать протокол лечения по этому диагнозу' : 'Показать федеральный '.getMESAlias().' по этому диагнозу') . '">{Diag_Code}</span></span> ';
								break;
							default:
								echo '<span id="EvnVizitPL_{EvnVizitPL_id}_DiagCode">{Diag_Code}</span> ';
								break;
						}
						$diag_str = $empty_str;
						if (!empty($Diag_Name))
						{
							$diag_str = $Diag_Name;
						}
						if($is_allow_edit)
						{
							echo '<span id="EvnVizitPL_data_{EvnVizitPL_id}_inputDiag" style="color:#000;" class="link" dataid="{Diag_id}">' . $diag_str . '</span>';
						}
						else
						{
							echo $diag_str;
						}
						echo ' <span id="EvnVizitPL_data_{EvnVizitPL_id}_editPersonPregnancy" class="link" style="display: '.($isPregDiag?'':'none').';">Сведения о беременности</span>';
					?></div>
					<div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaDiag" class="input-area" style="float:left; /*margin-left:5px;*/ display: none"></div>
					<div id="EvnVizitPL_{EvnVizitPL_id}_addEvnInfectNotifyTools" class="toolbar right" style="width: 1%;display: <?php if (empty($isDisabledAddEvnInfectNotify)) { ?>block<?php } else { ?>none<?php } ?>;">
						<a id="EvnVizitPL_{EvnVizitPL_id}_addEvnInfectNotify" class="button icon icon-add16" title="Добавить экстренное извещение об инфекционном заболевании, отравлении"><span></span></a>
					</div>
					<div id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyVenerTools" class="toolbar right" style="width:1%;display: <?php if (empty($isDisabledAddEvnNotifyVener)) { ?>block<?php } else { ?>none<?php } ?>;">
						<a id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyVener" class="button icon icon-add16" title="Создать Извещение о больном венерическим заболеванием"><span></span></a>
					</div>
					<div id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyRegisterIncludeNolosTools" class="toolbar right" style="width: 1%;display: none;">
						<a id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyRegisterIncludeNolos" class="button icon icon-add16" title="Создать Направление на включение в регистр по ВЗН"><span></span></a>
					</div>
					<div id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyRegisterIncludeOrphanTools" class="toolbar right" style="width: 1%;display: none;">
						<a id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyRegisterIncludeOrphan" class="button icon icon-add16" title="Создать Направление на включение в регистр по орфанным заболеваниям"><span></span></a>
					</div>
				</div>
				<div style='clear:both;'><div style='float:left;padding:5px 0px;'>Характер заболевания: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputDeseaseType" style='color:#000;' class="link" dataid='{DeseaseType_id}'><?php } echo empty($DeseaseType_Name)?$empty_str:'{DeseaseType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaDeseaseType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php if ($Sex_SysNick == 'woman' && $Person_Age_On_Vizit_Date >= 15 && $Person_Age_On_Vizit_Date <= 50) { ?>
					<div style='clear:both;'><div style='float:left;padding:5px 0px;'>Срок беременности, недель: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputPregnancyEvnVizitPLPeriod" style='color:#000;' class="link" dataid='{PregnancyEvnVizitPL_Period}'><?php } echo empty($PregnancyEvnVizitPL_Period)?$empty_str:'{PregnancyEvnVizitPL_Period}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaPregnancyEvnVizitPLPeriod" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php } ?>
				<?php if ((in_array(getRegionNick(), array('ekb')) && $EvnVizitPL_setDate120 < $dateX20180601) || (in_array(getRegionNick(), array('kareliya')) && $EvnVizitPL_setDate120 >= $dateX20170901)) { ?>
				<div style='clear:both;'><div style='float:left;padding:5px 0px;' id="EvnVizitPL_data_{EvnVizitPL_id}_wrapTumorStage">Стадия выявленного ЗНО: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputTumorStage" style='color:#000;' class="link" dataid='{TumorStage_id}'><?php } echo empty($TumorStage_Name)?$empty_str:'{TumorStage_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaTumorStage" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php } ?>
				<?php if (in_array(getRegionNick(), array('penza'))) { ?>
				<div style='clear:both;' style="display: <?php echo $EvnVizitPL_setDate120 >= $dateX20181101 && ((substr($Diag_Code, 0, 3) >= 'C00' && substr($Diag_Code, 0, 3) <= 'C97') || (substr($Diag_Code, 0, 3) >= 'D00' && substr($Diag_Code, 0, 3) <= 'D09')) ? 'block' : 'none'; ?>;"><div style='float:left;padding:5px 0px;' id="EvnVizitPL_data_{EvnVizitPL_id}_wrapPainIntensity">Интенсивность боли: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputPainIntensity" style='color:#000;' class="link" dataid='{PainIntensity_id}'><?php } echo empty($PainIntensity_Name)?$empty_str:'{PainIntensity_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaPainIntensity" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php } ?>
				<?php if (!in_array(getRegionNick(), array('kz'))) { ?>
				<div style='clear:both;'><div style='float:left;padding:5px 0px;' id="EvnVizitPL_data_{EvnVizitPL_id}_wrapIsZNO">Подозрение на ЗНО: <?php if($is_allow_edit && !$isMorbusOnkoExists) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputIsZNO" style='color:#000;' class="link" dataid='{EvnVizitPL_IsZNO}'><?php } echo empty($IsZNO_Name)?$empty_str:'{IsZNO_Name}'; if($is_allow_edit && !$isMorbusOnkoExists) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaIsZNO" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div id="EvnVizitPL_{EvnVizitPL_id}_DiagSpidField" style='clear:both;' style="display: <?php echo (!empty($EvnVizitPL_IsZNO) && $EvnVizitPL_IsZNO == 2)?'block':'none'; ?>;"><div style='float:left;padding:5px 0px;' id="EvnVizitPL_data_{EvnVizitPL_id}_wrapDiagSpid">Подозрение на диагноз: <span id="EvnVizitPL_{EvnVizitPL_id}_DiagSpidCode">{DiagSpid_Code}</span> <?php if($is_allow_edit && !$isMorbusOnkoExists) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputDiagSpid" style='color:#000;' class="link" dataid='{Diag_spid}'><?php } echo empty($DiagSpid_Name)?$empty_str:'{DiagSpid_Name}'; if($is_allow_edit && !$isMorbusOnkoExists) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaDiagSpid" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php if (getRegionNick()=='ekb') { ?>
					<div style='clear:both;' id='EvnVizitPL_{EvnVizitPL_id}_BiopsyDateField'><div style='float:left;padding:5px 0;'>Дата взятия биопсии: <?php if($is_allow_edit) { ?><span id='EvnVizitPL_data_{EvnVizitPL_id}_inputBiopsyDate' style='color:#000;' class='link' dataid='{EvnVizitPL_BiopsyDate}'>
						<?php } echo empty($EvnVizitPL_BiopsyDate)?$empty_str:'{EvnVizitPL_BiopsyDate}'; if($is_allow_edit) { ?>
					</span><?php } ?>
					</div>
					<?php } ?>
					<div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaBiopsyDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<?php } ?>
                <?php if(getRegionNumber() == 2) { ?>
					<div style='clear:both;'><div style='float:left;padding:5px 0px;'>Группа здоровья: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputHealthKind" style='color:#000;' class="link" dataid='{HealthKind_id}'><?php } echo empty($HealthKind_Name)?$empty_str:'{HealthKind_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaHealthKind" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
                <?php } ?>
                <div style='clear:both;'><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputRankinScaleDiv" style='display:<?php if (in_array(getRegionNumber(), array(66)) && $DiagFinance_IsRankin == 2) { echo "block"; } else { echo "none"; } ?>;float:left;padding:5px 0px;'>Значение по шкале Рэнкина: <?php if($is_allow_edit) { ?><span id="EvnVizitPL_data_{EvnVizitPL_id}_inputRankinScale" style='color:#000;' class="link" dataid='{RankinScale_id}'><?php } echo empty($RankinScale_Name)?$empty_str:'{RankinScale_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnVizitPL_data_{EvnVizitPL_id}_inputareaRankinScale" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
            </div>
        </div>
    </div>
    <div class="right">
        <div id="EvnVizitPL_data_{EvnVizitPL_id}_toolbar" class="toolbar" style="display: none">
            <?php
                if(getRegionNumber()=='101')
                    echo '<a id="EvnVizitPL_data_{EvnVizitPL_id}_printEvnVizitPL" class="button icon icon-print16" title="Печать стат.данных посещения"><span></span></a>';
            ?>
            <a id="EvnVizitPL_data_{EvnVizitPL_id}_editEvnVizitPL" class="button icon icon-edit16" title="Редактировать стат.данные посещения"><span></span></a>
        </div>
    </div>
</div>
