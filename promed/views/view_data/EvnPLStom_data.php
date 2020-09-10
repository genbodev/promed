<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
	$is_allow_result_edit = ($is_allow_edit && getRegionNumber() != 10);
	$fedResultDateX = (getRegionNumber() == 19 ? '2017-09-01' : '2015-01-01');
?> 
<div id="EvnPLStom_data_{EvnPLStom_id}" class="columns">
    <div class="left">
        <div id="EvnPLStom_data_{EvnPLStom_id}_content">
            <h2>Случай стоматологического лечения №&nbsp;<strong>{EvnPLStom_NumCard}</strong><br />{EvnPLStom_setDate}-{EvnPLStom_disDate}<br />{Lpu_Nick}</h2>
            <div class="text">
				<?php
				if (in_array(getRegionNumber(), array(2,3,10,19,30,59,60,66)) && !empty($EvnPLStom_IsFinish) && $EvnPLStom_IsFinish == 2) {
					if (!empty($EvnCostPrint_setDT)) {
						$costprint = "<p>Стоимость лечения: ".$CostPrint."</p>";
						if ($EvnCostPrint_IsNoPrint == 2) {
							$costprint .= "<p>Отказ в получении справки";
						} else {
							$costprint .= "<p>Справка выдана";
						}

						$costprint .= " ".$EvnCostPrint_setDT."</p>";
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
					echo '<span id="EvnPLStom_{EvnPLStom_id}_DiagPreidCode">{DiagPreid_Code}</span> ';
					$diag_str = $empty_str;
					if (!empty($DiagPreid_Name))
					{
						$diag_str = $DiagPreid_Name;
					}
					if($is_allow_edit)
					{
						echo '<span id="EvnPLStom_data_{EvnPLStom_id}_inputDiagPreid" style="color:#000;" class="link" dataid="{Diag_preid}">' . $diag_str . '</span>';
					}
					else
					{
						echo $diag_str;
					}
					?></div>
					<div id="EvnPLStom_data_{EvnPLStom_id}_inputareaDiagPreid" class="input-area" style="float:left; display: none"></div>
				<?php
					}
				?>
				<div style='padding:5px 0;width: 99%' class="left">Предварительный диагноз: <?php
					echo '<span id="EvnPLStom_{EvnPLStom_id}_DiagFCode">{DiagF_Code}</span> ';
					$diag_str = $empty_str;
					if (!empty($DiagF_Name))
					{
						$diag_str = $DiagF_Name;
					}
					if($is_allow_edit && getRegionNick() != 'ufa')
					{
						echo '<span id="EvnPLStom_data_{EvnPLStom_id}_inputDiagF" style="color:#000;" class="link" dataid="{Diag_fid}">' . $diag_str . '</span>';
					}
					else
					{
						echo $diag_str;
					}
					?></div>
				<div id="EvnPLStom_data_{EvnPLStom_id}_inputareaDiagF" class="input-area" style="float:left; display: none"></div>
				<div style="clear: both"></div>
	            <?php
	            if(!empty($MedicalCareKind_Name)) {
		            ?><p>Медицинская помощь: {MedicalCareKind_Name}</p><?php
	            } ?>
			   <p><span id="EvnPLStom_data_{EvnPLStom_id}_showDiagList" class="link" title="Показать список уточненных диагнозов">Диагноз</span>:
				<?php 
					switch (true) {
						case (!empty($DiagFedMes_FileName) && file_exists($_SERVER['DOCUMENT_ROOT'].'/promed/views/federal_mes/'.$DiagFedMes_FileName.'.htm')):
						case ( !empty($CureStandart_Count) ):
							echo '<span id="EvnPLStom_{EvnPLStom_id}_DiagCode"><span id="EvnPLStom_{EvnPLStom_id}_showFm" class="link" title="' . (getRegionNick() == 'kz' ? 'Показать протокол лечения по этому диагнозу' : 'Показать федеральный '.getMESAlias().' по этому диагнозу') . '">{Diag_Code}</span></span> ';
							break;
						default:
							echo '<span id="EvnPLStom_{EvnPLStom_id}_DiagCode">{Diag_Code}</span> ';
							break;
					}
				?> 
				<span id="EvnPLStom_{EvnPLStom_id}_DiagText">{Diag_Name}</span>.
				<br />Характер заболевания: <span id="EvnPLStom_{EvnPLStom_id}_DeseaseTypeText"><?php echo empty($DeseaseType_Name)?$empty_str:'{DeseaseType_Name}'; ?></span></p>
                <div class="data-table"><div class="caption" style='clear:both;'><h2>Результат лечения</h2></div>
                    <div style="padding:0px 5px 25px; border: 1px solid #000;">
                        <div class="data-row-container">
                            <div class="data-row">Случай закончен: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputIsFinish" class="value link"<?php } else { echo ' class="value"';} ?>>{IsFinish_Name}</span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaIsFinish" class="input-area"></div>
                        </div>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapIsSan" style="display: <?php if (2 == $EvnPLStom_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">Санирован: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputIsSan" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnPLStom_IsSan}'><?php echo empty($IsSan_Name) ? $empty_str : '{IsSan_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaIsSan" class="input-area"></div>
                        </div>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapSanationStatus" style="display: <?php if (2 == $EvnPLStom_IsSan) { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">Санация: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputSanationStatus" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{SanationStatus_id}'><?php echo empty($SanationStatus_Name) ? $empty_str : '{SanationStatus_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaSanationStatus" class="input-area"></div>
                        </div>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapUKL" style="display: <?php if (2 == $EvnPLStom_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">УКЛ: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputUKL" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($EvnPLStom_UKL) ? $empty_str : '{EvnPLStom_UKL}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaUKL" class="input-area"></div>
                        </div>
						<?php if (getRegionNick() != 'kz') { ?>
							<div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapIsSurveyRefuse">
								<div class="data-row">Отказ от прохождения медицинских обследований: <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputIsSurveyRefuse" class="link"<?php } ?> dataid='{EvnPLStom_IsSurveyRefuse}'><?php echo empty($IsSurveyRefuse_Name) ? $empty_str : '{IsSurveyRefuse_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaIsSurveyRefuse" class="input-area"></div>
							</div>
						<?php } ?>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapResultClass" style="display: <?php if (2 == $EvnPLStom_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">Результат<?php echo (in_array(getRegionNumber(), array(10,60,91)) ? " обращения" : ""); ?>: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputResultClass" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{ResultClass_id}'><?php echo empty($ResultClass_Name) ? $empty_str : '{ResultClass_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaResultClass" class="input-area"></div>
                        </div>
	                    <?php if (getRegionNick() == 'kz') { ?>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapMedicalStatus" style="display: <?php if (2 == $EvnPLStom_IsDisp) { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">Состояние здоровья: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputMedicalStatus" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MedicalStatus_id}'><?php echo empty($MedicalStatus_Name) ? $empty_str : '{MedicalStatus_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaMedicalStatus" class="input-area"></div>
                        </div>
						<?php } ?>
						<?php
							if (strtotime($EvnPLStom_disDate) >= strtotime('01.01.2016')) {
						?>
						<div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapInterruptLeaveType" style="display: <?php if (2 == $EvnPLStom_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
							<div class="data-row">Случай прерван: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputInterruptLeaveType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{InterruptLeaveType_id}'><?php echo empty($InterruptLeaveType_Name) ? $empty_str : '{InterruptLeaveType_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaInterruptLeaveType" class="input-area"></div>
						</div>
						<?php
							}
						?>
	                    <?php if (in_array(getRegionNumber(), array(3,10,24,35,40,58,60,66,91,76))) { ?>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapResultDeseaseType" style="display: <?php if (2 == $EvnPLStom_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">Исход: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputResultDeseaseType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{ResultDeseaseType_id}'><?php echo empty($ResultDeseaseType_Name) ? $empty_str : '{ResultDeseaseType_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaResultDeseaseType" class="input-area"></div>
                        </div>
	                    <?php } ?>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapDirectType" style="display: <?php if (2 == $EvnPLStom_IsFinish && $ResultClass_SysNick != 'die') { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">Направление: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputDirectType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{DirectType_id}'><?php echo empty($DirectType_Name) ? $empty_str : '{DirectType_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaDirectType" class="input-area"></div>
                        </div>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapDirectClass" style="display: <?php if (2 == $EvnPLStom_IsFinish && $ResultClass_SysNick != 'die') { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">Куда направлен: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputDirectClass" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{DirectClass_id}'><?php echo empty($DirectClass_Name) ? $empty_str : '{DirectClass_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaDirectClass" class="input-area"></div>
                        </div>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapLpuSectionO" style="display: <?php if (1 == $DirectClass_id) { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">Отделение: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputLpuSectionO" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{LpuSection_oid}'><?php echo empty($LpuSectionO_Name) ? $empty_str : '{LpuSectionO_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaLpuSectionO" class="input-area"></div>
                        </div>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapLpuO" style="display: <?php if (2 == $DirectClass_id) { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">ЛПУ: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputLpuO" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Lpu_oid}'><?php echo empty($LpuO_Nick) ? $empty_str : '{LpuO_Nick}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaLpuO" class="input-area"></div>
                        </div>
						<div style='padding:5px 0;width: 99%' class="left">Заключ. диагноз: <?php
							echo '<span id="EvnPLStom_{EvnPLStom_id}_DiagLCode">{DiagL_Code}</span> ';
							$diag_str = $empty_str;
							if (!empty($DiagL_Name))
							{
								$diag_str = $DiagL_Name;
							}
							if($is_allow_edit && getRegionNick() != 'ufa')
							{
								echo '<span id="EvnPLStom_data_{EvnPLStom_id}_inputDiagL" style="color:#000;" class="link" dataid="{Diag_lid}">' . $diag_str . '</span>';
							}
							else
							{
								echo $diag_str;
							}
							?></div>
						<div id="EvnPLStom_data_{EvnPLStom_id}_inputareaDiagL" class="input-area" style="float:left; display: none"></div>
						<div style="clear: both"></div>
                        <div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapDiagConc" style="display: <?php if (2 == $EvnPLStom_IsFinish) { ?>block<?php } else { ?>none<?php } ?>">
                            <div class="data-row">Заключ. внешняя причина: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputDiagConc" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Diag_concid}'><?php echo empty($DiagConc_Name) ? $empty_str : '{DiagConc_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaDiagConc" class="input-area"></div>
                        </div>
						<div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapPrehospTrauma">
							<div class="data-row">Вид травмы (внешнего воздействия): <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputPrehospTrauma" class="link"<?php } ?> dataid='{PrehospTrauma_id}'>
	                    <?php
						if (empty($PrehospTrauma_Name)) {
							echo '<input type="hidden" id="EvnPLStom_data_{EvnPLStom_id}_trauma" value="0" />' . $empty_str ;
						} else {
							echo '<input type="hidden" id="EvnPLStom_data_{EvnPLStom_id}_trauma" value="1" />{PrehospTrauma_Name}' ;
						}
						?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaPrehospTrauma" class="input-area"></div>
						</div>
						<div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapIsUnlaw" style="display: <?php if (!empty($PrehospTrauma_Name)) { ?>block<?php } else { ?>none<?php } ?>">
							<div class="data-row">Противоправная: <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputIsUnlaw" class="link"<?php } ?> dataid='{EvnPLStom_IsUnlaw}'><?php echo empty($IsUnlaw_Name) ? $empty_str : '{IsUnlaw_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaIsUnlaw" class="input-area"></div>
						</div>
						<div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapIsUnport">
							<div class="data-row">Нетранспортабельность: <span style="color: #000; display: inline;"<?php if ($is_allow_edit) { ?> id="EvnPLStom_{EvnPLStom_id}_inputIsUnport" class="link"<?php } ?> dataid='{EvnPLStom_IsUnport}'><?php echo empty($IsUnport_Name) ? $empty_str : '{IsUnport_Name}'; ?></span></div><div id="EvnPLStom_{EvnPLStom_id}_inputareaIsUnport" class="input-area"></div>
						</div>
						<?php if (!empty($isAllowFedResultFields)) { ?>
							<div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapFedLeaveType" style="display: <?php if (!empty($EvnPLStom_disDateYmd) && ((getRegionNumber() == 19 && 2 == $EvnPLStom_IsFinish) || (getRegionNumber() != 19 && !empty($ResultClass_Code))) && $EvnPLStom_disDateYmd >= $fedResultDateX) { ?>block<?php } else { ?>none<?php } ?>">
								<div class="data-row">Фед. результат: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_data_{EvnPLStom_id}_inputFedLeaveType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{LeaveType_fedid}'><?php echo empty($FedLeaveType_Name) ? $empty_str : '{FedLeaveType_Name}'; ?></span></div><div id="EvnPLStom_data_{EvnPLStom_id}_inputareaFedLeaveType" class="input-area"></div>
							</div>
							<div class="data-row-container" id="EvnPLStom_{EvnPLStom_id}_wrapFedResultDeseaseType" style="display: <?php if (!empty($EvnPLStom_disDateYmd) && ((getRegionNumber() == 19 && 2 == $EvnPLStom_IsFinish) || (getRegionNumber() != 19 && !empty($ResultClass_Code))) && $EvnPLStom_disDateYmd >= $fedResultDateX) { ?>block<?php } else { ?>none<?php } ?>">
								<div class="data-row">Фед. исход: <span<?php if ($is_allow_result_edit) { ?> id="EvnPLStom_data_{EvnPLStom_id}_inputFedResultDeseaseType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{ResultDeseaseType_fedid}'><?php echo empty($FedResultDeseaseType_Name) ? $empty_str : '{FedResultDeseaseType_Name}'; ?></span></div><div id="EvnPLStom_data_{EvnPLStom_id}_inputareaFedResultDeseaseType" class="input-area"></div>
							</div>
						<?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="right">
        <div id="EvnPLStom_{EvnPLStom_id}_toolbar" class="toolbar" style="display: none">
            <a id="EvnPLStom_data_{EvnPLStom_id}_editEvnPLStom" class="button icon icon-edit16" title="Редактировать талон АПЛ"><span></span></a>
            <a id="EvnPLStom_{EvnPLStom_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
            <a id="EvnPLStom_{EvnPLStom_id}_addEvnVizitPLStom" class="button icon icon-add16" title="Добавить посещение в рамках данного случая"><span></span></a>
            <?php /*
			if (empty($_REQUEST['archiveRecord']) && !empty($EvnPLStom_IsFinish) && !empty($EvnPLStom_IsSigned) && !empty($isDisabledCancelSigned)) {
				if (1 == $isDisabledCancelSigned && 2 == $EvnPLStom_IsSigned) {
					echo '<a id="EvnPLStom_data_{EvnPLStom_id}_cancelSigned" class="button icon icon-signature16" title="Отменить подпись."><span></span></a>';
				}
				if (1 == $EvnPLStom_IsSigned && 2 == $EvnPLStom_IsFinish) {
					echo '<a id="EvnPLStom_data_{EvnPLStom_id}_signedEvnPLStom" class="button icon icon-signature16" title="Подписать случай АПЛ. Изменение после подписания невозможно."><span></span></a>';
				}
			}
			*/ ?>
			<div class="emd-here" data-objectname="EvnPLStom" data-objectid="{EvnPLStom_id}" data-issigned="{EvnPLStom_IsSigned}"></div>
        </div>
    </div>
</div>