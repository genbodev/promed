<div id="EvnPS_data_{EvnPS_id}" class="columns">
    <div class="left">
        <div id="EvnPS_data_{EvnPS_id}_content">
			<h2>Случай стационарного лечения №&nbsp;<strong>{EvnPS_NumCard}</strong>, {EvnPS_setDate}, {EvnPS_setTime} - {EvnPS_disDate}, {EvnPS_disTime}</h2>
			<div class="text">
				<?php
				if (in_array(getRegionNumber(), array(2,3,10,19,30,40,59,60,66)) && !empty($LeaveType_Code) && (getRegionNumber() == 59 || !in_array($LeaveType_Code, array(5,104,204)))) {
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
                <p><span id="EvnPS_data_{EvnPS_id}_showDiagList" class="link" title="Показать список уточненных диагнозов">Диагноз</span>: 
				<?php 
					switch (true) {
						case (!empty($DiagFedMes_FileName) && file_exists($_SERVER['DOCUMENT_ROOT'].'/promed/views/federal_mes/'.$DiagFedMes_FileName.'.htm')):
						case ( !empty($CureStandart_Count) ):
							echo '<span id="EvnPS_{EvnPS_id}_showFm" class="link" title="' . (getRegionNick() == 'kz' ? 'Показать протокол лечения по этому диагнозу' : 'Показать федеральный '.getMESAlias().' по этому диагнозу') . '">{Diag_Code}</span>';
							break;
						default:
							echo '{Diag_Code}';
							break;
					}
				?> 
				{Diag_Name}.</p>
				<p>Исход госпитализации: <strong>{LeaveType_Name}</strong></p>
				<p>Тип госпитализации: <strong>{PrehospType_Name}</strong></p>
				<?php if (getRegionNick() == 'penza') { ?>
				<p>Форма помощи: <strong>{MedicalCareFormType_Name}</strong></p>
				<?php } ?>
				<p>Доставлен: {PrehospArrive_Name} <?php echo (($PrehospArrive_id != 2)?'':', Код: {EvnPS_CodeConv} Номер наряда: {EvnPS_NumConv}');?></p>
				<?php echo (empty($EvnPS_IsCont)?'':'<p>Переведен из: {Lpu_p_Name}</p>');?>
				<p>Направление: <?php echo (empty($EvnDirection_Num)?'нет':'{EvnDirection_Num} от');?> {EvnDirection_setDate}</p>
				<p>Кем выдано: {Lpu_d_Name}</p>
				<p>Диагноз направившего учреждения: <strong>{Diag_d_Code}. {Diag_d_Name}</strong></p>
				<p>
					Сопутствующие диагнозы направившего учреждения:<br />
					{EvnDiagDirectPS}
				</p>
				<p>
					<?php echo ((2 == $EvnPS_IsImperHosp || 2 == $EvnPS_IsShortVolume || 2 == $EvnPS_IsWrongCure || 2 == $EvnPS_IsDiagMismatch)?'Дефекты догоспитального этапа:<br />':'');?>
					<?php echo ((2 == $EvnPS_IsImperHosp)?'<strong>Несвоевременность госпитализации</strong><br />':'');?>
					<?php echo ((2 == $EvnPS_IsShortVolume)?'<strong>Недостаточный объем клинико-диагностического обследования</strong><br />':'');?>
					<?php echo ((2 == $EvnPS_IsWrongCure)?'<strong>Неправильная тактика лечения</strong><br />':'');?>
					<?php echo ((2 == $EvnPS_IsDiagMismatch)?'<strong>Несовпадение диагноза</strong><br />':'');?>
				</p>
				<br />
			</div>
        </div>
    </div>
    <div class="right">
        <div id="EvnPS_{EvnPS_id}_toolbar" class="toolbar" style="display: none">
            <a id="EvnPS_data_{EvnPS_id}_editEvnPS" class="button icon icon-edit16" title="Редактировать КВС"><span></span></a>
			<a id="EvnPS_data_{EvnPS_id}_openEvnPSLocat" class="button icon icon-archiveEmk16" title="Оригинал ИБ"><span></span></a>
            <a id="EvnPS_{EvnPS_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
			<div class="emd-here" data-objectname="EvnPS" data-objectid="{EvnPS_id}" data-issigned="{EvnPS_IsSigned}" data-minsigncount="{EvnPS_MinSignCount}" data-signcount="{EvnPS_SignCount}"></div>
        </div>
    </div>
</div>
