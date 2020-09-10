<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
	$is_allow_result_edit = ($is_allow_edit && getRegionNumber() != 10);
	$fedResultDateX = (getRegionNumber() == 19 ? '2017-09-01' : '2015-01-01');
?> 
<div id="EvnPL_data_{EvnPL_id}" class="columns">
    <div class="left">
        <div id="EvnPL_data_{EvnPL_id}_content">
            <h2>Случай амбулаторно-поликлинического лечения №&nbsp;<strong>{EvnPL_NumCard}</strong><br />{EvnPL_setDate}-{EvnPL_disDate}<br />{Lpu_Nick}</h2>
            <div class="text">
				<?php
				if (in_array(getRegionNumber(), array(2,3,10,19,30,40,59,60,66,91)) && !empty($EvnPL_IsFinish) && $EvnPL_IsFinish == 2) {
					if (!empty($EvnCostPrint_setDT)) {
						$costprint = "<p>Стоимость лечения: ".$CostPrint."</p>";
						if ($EvnCostPrint_IsNoPrint == 2) {
							$costprint .= "<p>Отказ в получении справки";
						} else {
							$costprint .= "<p>Справка выдана";
						}

						$costprint .= " ".$EvnCostPrint_setDT."</p>";
						/*if (!empty($EvnCostPrint_DeliveryType) && in_array(getRegionNumber(), array(19))) {
							$costprint .= "<p>Выдана: Представитель ".$EvnCostPrint_DeliveryType."</p>";
						}*/
						echo $costprint;
					}
				}
				?>
				<p>Кем направлен: <?php 
					echo empty($PrehospDirect_Name)?$empty_str:'{PrehospDirect_Name} ';
					echo empty($LpuSectionD_Name)?(($PrehospDirect_Code==1)?$empty_str:''):'{LpuSectionD_Name}';
					echo empty($OrgD_Name)?(in_array($PrehospDirect_Code,array(2,3,4,5,6))?$empty_str:''):'{OrgD_Name}';
					echo empty($EvnDirection_Num)?'':'<br />Направление № {EvnDirection_Num} от {EvnDirection_setDate}'; 
				?>
				<br /><br />Диагноз направившего учреждения: <?php echo empty($DiagD_Name)?$empty_str:'{DiagD_Code} {DiagD_Name}'; ?>
				<?php
					if ( !empty($DiagD_Code) && in_array(substr($DiagD_Code, 0, 1), array('S', 'T')) ) {
				?>
				<div style='padding:5px 0;width: 99%' class="left">Предварительная внешняя причина: <?php
					echo '<span id="EvnPL_{EvnPL_id}_DiagPreidCode">{DiagPreid_Code}</span> ';
					$diag_str = $empty_str;
					if (!empty($DiagPreid_Name))
					{
						$diag_str = $DiagPreid_Name;
					}
					if($is_allow_edit)
					{
						echo '<span id="EvnPL_data_{EvnPL_id}_inputDiagPreid" style="color:#000;" class="link" dataid="{Diag_preid}">' . $diag_str . '</span>';
					}
					else
					{
						echo $diag_str;
					}
					?></div>
					<div id="EvnPL_data_{EvnPL_id}_inputareaDiagPreid" class="input-area" style="float:left; display: none"></div>
				<?php
					}
				?>
				<div style='padding:5px 0;width: 99%' class="left">Предварительный диагноз: <?php
					echo '<span id="EvnPL_{EvnPL_id}_DiagFCode">{DiagF_Code}</span> ';
					$diag_str = $empty_str;
					if (!empty($DiagF_Name))
					{
						$diag_str = $DiagF_Name;
					}
					if($is_allow_edit && getRegionNick() != 'ufa')
					{
						echo '<span id="EvnPL_data_{EvnPL_id}_inputDiagF" style="color:#000;" class="link" dataid="{Diag_fid}">' . $diag_str . '</span>';
					}
					else
					{
						echo $diag_str;
					}
					?></div>
				</p>
				<div id="EvnPL_data_{EvnPL_id}_inputareaDiagF" class="input-area" style="float:left; display: none"></div>
                <div style="clear: both"></div>
               <?php
			   	if(getRegionNumber()==10){
	                ?><p>Медицинская помощь: {MedicalCareKind_Name}</p><?php
                } ?>
			   <p><span id="EvnPL_data_{EvnPL_id}_showDiagList" class="link" title="Показать список уточненных диагнозов">Диагноз</span>:
				<?php 
					switch (true) {
						case (!empty($DiagFedMes_FileName) && file_exists($_SERVER['DOCUMENT_ROOT'].'/promed/views/federal_mes/'.$DiagFedMes_FileName.'.htm')):
						case ( !empty($CureStandart_Count) ):
							echo '<span id="EvnPL_{EvnPL_id}_DiagCode"><span id="EvnPL_{EvnPL_id}_showFm" class="link" title="' . (getRegionNick() == 'kz' ? 'Показать протокол лечения по этому диагнозу' : 'Показать федеральный '.getMESAlias().' по этому диагнозу') . '">{Diag_Code}</span></span> ';
							break;
						default:
							echo '<span id="EvnPL_{EvnPL_id}_DiagCode">{Diag_Code}</span> ';
							break;
					}
				?> 
				<span id="EvnPL_{EvnPL_id}_DiagText">{Diag_Name}</span>.
				<br />Характер заболевания: <span id="EvnPL_{EvnPL_id}_DeseaseTypeText"><?php echo empty($DeseaseType_Name)?$empty_str:'{DeseaseType_Name}'; ?></span></p>
                <div class="data-table"><div class="caption" style='clear:both;'><h2>Результат лечения</h2></div>
                <div style="padding:0px 5px 25px; border: 1px solid #000;">
                    <div class="data-row-container">
                        <div class="data-row"><input type="hidden" id="EvnPL_data_{EvnPL_id}_IsFinish" value="{EvnPL_IsFinish}" />Случай закончен: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputIsFinish" class="value link"<?php } else { echo ' class="value"';} ?>>{IsFinish_Name}</span></div><div id="EvnPL_{EvnPL_id}_inputareaIsFinish" class="input-area"></div>
                    </div>
					<?php if(getRegionNumber()!=66){?>
                    <div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapUKL" style="display: <?php if (2 == $EvnPL_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
                        <div class="data-row">УКЛ: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputUKL" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($EvnPL_UKL) ? $empty_str : '{EvnPL_UKL}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaUKL" class="input-area"></div>
                    </div>
					<?php }?>
					<?php if(in_array(getRegionNick(), array('karelia','astra','buryatiya'))){?>
                    <div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapIsFirstDisable">
						<?php
							if (!empty($EvnPL_setDate) && strtotime($EvnPL_setDate) > strtotime('01.11.2016')) {
						?>
                        <div class="data-row">Впервые выявленная инвалидность: <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPL_{EvnPL_id}_inputPrivilegeType" class="link"<?php } ?> dataid='{PrivilegeType_id}'><?php echo empty($PrivilegeType_Name) ? $empty_str : '{PrivilegeType_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaPrivilegeType" class="input-area"></div>
						<?php
							} else if (getRegionNick() == 'karelia') {
						?>
						<div class="data-row">Впервые выявленная инвалидность: <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPL_{EvnPL_id}_inputIsFirstDisable" class="link"<?php } ?> dataid='{EvnPL_IsFirstDisable}'><?php echo empty($EvnPL_IsFirstDisable) ? $empty_str : '{EvnPL_IsFirstDisable}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaIsFirstDisable" class="input-area"></div>
						<?php
							}
						?>
                    </div>
					<?php }?>
					<?php if (getRegionNick() != 'kz') { ?>
					<div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapIsSurveyRefuse">
						<div class="data-row">Отказ от прохождения медицинских обследований: <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPL_{EvnPL_id}_inputIsSurveyRefuse" class="link"<?php } ?> dataid='{EvnPL_IsSurveyRefuse}'><?php echo empty($IsSurveyRefuse_Name) ? $empty_str : '{IsSurveyRefuse_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaIsSurveyRefuse" class="input-area"></div>
					</div>
					<?php } ?>
                    <div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapResultClass" style="display: <?php if (2 == $EvnPL_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
                        <div class="data-row">Результат<?php echo (in_array(getRegionNumber(), array(10,60,91)) ? " обращения" : ""); ?>: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputResultClass" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{ResultClass_id}'><?php echo empty($ResultClass_Name) ? $empty_str : '{ResultClass_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaResultClass" class="input-area"></div>
                    </div>
					<?php if (getRegionNick() == 'kz') { ?>
                    <div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapMedicalStatus" style="display: <?php if (2 == $EvnPL_IsDisp) { ?>block<?php } else { ?>none<?php } ?>">
                        <div class="data-row">Состояние здоровья: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputMedicalStatus" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MedicalStatus_id}'><?php echo empty($MedicalStatus_Name) ? $empty_str : '{MedicalStatus_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaMedicalStatus" class="input-area"></div>
                    </div>
					<?php } ?>
					<?php
						if (strtotime($EvnPL_disDate) >= strtotime('01.01.2016')) {
					?>
					<div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapInterruptLeaveType" style="display: <?php if (2 == $EvnPL_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
                        <div class="data-row">Случай прерван: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputInterruptLeaveType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{InterruptLeaveType_id}'><?php echo empty($InterruptLeaveType_Name) ? $empty_str : '{InterruptLeaveType_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaInterruptLeaveType" class="input-area"></div>
                    </div>
					<?php
						}
					?>
	                <?php if (in_array(getRegionNumber(), array(3, 10, 24,/*30,*/ 35, 40, 58, 60, 66, 91, 76))) { ?>
					<div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapResultDeseaseType" style="display: <?php if (2 == $EvnPL_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
                        <div class="data-row">Исход: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputResultDeseaseType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{ResultDeseaseType_id}'><?php echo empty($ResultDeseaseType_Name) ? $empty_str : '{ResultDeseaseType_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaResultDeseaseType" class="input-area"></div>
                    </div>
					<?php } ?>
	                <div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapDirectType" style="display: <?php if (2 == $EvnPL_IsFinish && $ResultClass_SysNick != 'die') { ?>block<?php } else { ?>none<?php } ?>">
		                <div class="data-row">Направление: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputDirectType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{DirectType_id}'><?php echo empty($DirectType_Name) ? $empty_str : '{DirectType_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaDirectType" class="input-area"></div>
	                </div>
	                <div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapDirectClass" style="display: <?php if (2 == $EvnPL_IsFinish && $ResultClass_SysNick != 'die') { ?>block<?php } else { ?>none<?php } ?>">
	                    <div class="data-row">Куда направлен: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputDirectClass" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{DirectClass_id}'><?php echo empty($DirectClass_Name) ? $empty_str : '{DirectClass_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaDirectClass" class="input-area"></div>
	                </div>
	                <div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapLpuSectionO" style="display: <?php if (1 == $DirectClass_id) { ?>block<?php } else { ?>none<?php } ?>">
	                    <div class="data-row">Отделение: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputLpuSectionO" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{LpuSection_oid}'><?php echo empty($LpuSectionO_Name) ? $empty_str : '{LpuSectionO_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaLpuSectionO" class="input-area"></div>
	                </div>
	                <div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapLpuO" style="display: <?php if (2 == $DirectClass_id) { ?>block<?php } else { ?>none<?php } ?>">
	                    <div class="data-row">ЛПУ: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputLpuO" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Lpu_oid}'><?php echo empty($LpuO_Nick) ? $empty_str : '{LpuO_Nick}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaLpuO" class="input-area"></div>
	                </div>
					<div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapDiagConc" style="display: <?php if (2 == $EvnPL_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
	                    <div class="data-row">Заключ. внешняя причина: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_{EvnPL_id}_inputDiagConc" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Diag_concid}'><?php echo empty($DiagConc_Name) ? $empty_str : '{DiagConc_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaDiagConc" class="input-area"></div>
	                </div>
					<?php if (!in_array(getRegionNumber(), array(10))) { ?>
					<div style='padding:5px 0;width: 99%' class="left">Заключ. диагноз: <?php
						echo '<span id="EvnPL_{EvnPL_id}_DiagLCode">{DiagL_Code}</span> ';
						$diag_str = $empty_str;
						if (!empty($DiagL_Name))
						{
							$diag_str = $DiagL_Name;
						}
						if($is_allow_edit && getRegionNick() != 'ufa')
						{
							echo '<span id="EvnPL_data_{EvnPL_id}_inputDiagL" style="color:#000;" class="link" dataid="{Diag_lid}">' . $diag_str . '</span>';
						}
						else
						{
							echo $diag_str;
						}
						?></div>
					<?php } ?>
					<div id="EvnPL_data_{EvnPL_id}_inputareaDiagL" class="input-area" style="float:left; display: none"></div>
					<div style="clear: both"></div>
					<div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapPrehospTrauma">
						<div class="data-row">Вид травмы (внешнего воздействия): <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPL_{EvnPL_id}_inputPrehospTrauma" class="link"<?php } ?> dataid='{PrehospTrauma_id}'>
	                    <?php
						if (empty($PrehospTrauma_Name)) {
							echo '<input type="hidden" id="EvnPL_data_{EvnPL_id}_trauma" value="0" />' . $empty_str ;
						} else {
							echo '<input type="hidden" id="EvnPL_data_{EvnPL_id}_trauma" value="1" />{PrehospTrauma_Name}' ;
						}
						?></span></div><div id="EvnPL_{EvnPL_id}_inputareaPrehospTrauma" class="input-area"></div>
					</div>
					<div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapIsUnlaw" style="display: <?php if (!empty($PrehospTrauma_Name)) { ?>block<?php } else { ?>none<?php } ?>">
						<div class="data-row">Противоправная: <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPL_{EvnPL_id}_inputIsUnlaw" class="link"<?php } ?> dataid='{EvnPL_IsUnlaw}'><?php echo empty($IsUnlaw_Name) ? $empty_str : '{IsUnlaw_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaIsUnlaw" class="input-area"></div>
					</div>
					<div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapIsUnport">
						<div class="data-row">Нетранспортабельность: <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPL_{EvnPL_id}_inputIsUnport" class="link"<?php } ?> dataid='{EvnPL_IsUnport}'><?php echo empty($IsUnport_Name) ? $empty_str : '{IsUnport_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaIsUnport" class="input-area"></div>
					</div>

                    <?php if (getRegionNick() === 'astra'): ?>
                    <div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapIsMseDirected">
                        <div class="data-row">Пациент направлен на МСЭ: <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPL_{EvnPL_id}_inputIsMseDirected" class="link"<?php } ?> dataid='{EvnPL_isMseDirected}'><?php echo empty($IsMseDirected_Name) ? $empty_str : '{IsMseDirected_Name}'; ?></span></div><div id="EvnPL_{EvnPL_id}_inputareaIsMseDirected" class="input-area"></div>
                    </div>
                    <?php endif; ?>

					<?php if (!empty($isAllowFedResultFields)) { ?>
						<div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapFedLeaveType" style="display: <?php if (!empty($EvnPL_disDateYmd) && ((in_array(getRegionNumber(), [ 19 ]) && 2 == $EvnPL_IsFinish) || (getRegionNumber() != 19 && !empty($ResultClass_Code))) && $EvnPL_disDateYmd >= $fedResultDateX) { ?>block<?php } else { ?>none<?php } ?>">
							<div class="data-row">Фед. результат: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_data_{EvnPL_id}_inputFedLeaveType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{LeaveType_fedid}'><?php echo empty($FedLeaveType_Name) ? $empty_str : '{FedLeaveType_Name}'; ?></span></div><div id="EvnPL_data_{EvnPL_id}_inputareaFedLeaveType" class="input-area"></div>
						</div>
						<div class="data-row-container" id="EvnPL_{EvnPL_id}_wrapFedResultDeseaseType" style="display: <?php if (!empty($EvnPL_disDateYmd) && ((in_array(getRegionNumber(), [ 19 ]) && 2 == $EvnPL_IsFinish) || (getRegionNumber() != 19 && !empty($ResultClass_Code))) && $EvnPL_disDateYmd >= $fedResultDateX) { ?>block<?php } else { ?>none<?php } ?>">
							<div class="data-row">Фед. исход: <span<?php if ($is_allow_result_edit) { ?> id="EvnPL_data_{EvnPL_id}_inputFedResultDeseaseType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{ResultDeseaseType_fedid}'><?php echo empty($FedResultDeseaseType_Name) ? $empty_str : '{FedResultDeseaseType_Name}'; ?></span></div><div id="EvnPL_data_{EvnPL_id}_inputareaFedResultDeseaseType" class="input-area"></div>
						</div>
					<?php } ?>
                </div>
                </div>
            </div>
        </div>
    </div>
    <div class="right">
        <div id="EvnPL_{EvnPL_id}_toolbar" class="toolbar" style="display: none">
            <a id="EvnPL_data_{EvnPL_id}_editEvnPL" class="button icon icon-edit16" title="Редактировать талон АПЛ"><span></span></a>
            <a id="EvnPL_{EvnPL_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
			<?php
			if (empty($EvnPL_IsSigned)||(!empty($EvnPL_IsSigned) && $EvnPL_IsSigned == 1)){
				if (empty($_REQUEST['archiveRecord'])) {
					if ($EvnPL_IsOpenable == 2) {
						echo '<a id="EvnPL_data_{EvnPL_id}_openEvnPL" class="button icon icon-delete16" title="Отменить закрытие случая"><span></span></a>';
					} else if ($EvnPL_IsFinish!=2) {
						echo '<a id="EvnPL_data_{EvnPL_id}_closeEvnPL" class="button icon icon-ok16" title="Закрыть случай АПЛ"><span></span></a>';
					}
				}
			}
			?>
			<a id="EvnPL_{EvnPL_id}_addEvnVizitPL" class="button icon icon-add16" title="Добавить посещение в рамках данного случая"><span></span></a>
			<?php /*
			if (empty($_REQUEST['archiveRecord']) && !empty($EvnPL_IsFinish) && !empty($EvnPL_IsSigned) && !empty($isDisabledCancelSigned)) {
				if (1 == $isDisabledCancelSigned && 2 == $EvnPL_IsSigned) {
					echo '<a id="EvnPL_data_{EvnPL_id}_cancelSigned" class="button icon icon-signature16" title="Отменить подпись."><span></span></a>';
				}
				if (1 == $EvnPL_IsSigned && 2 == $EvnPL_IsFinish) {
					echo '<a id="EvnPL_data_{EvnPL_id}_signedEvnPL" class="button icon icon-signature16" title="Подписать случай АПЛ. Изменение после подписания невозможно."><span></span></a>';
				}
			}
			*/ ?>
			<div class="emd-here" data-objectname="EvnPL" data-objectid="{EvnPL_id}" data-issigned="{EvnPL_IsSigned}"></div>
        </div>
    </div>
</div>
