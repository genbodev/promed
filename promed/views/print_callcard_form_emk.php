<div id="CmpCallCard_{CmpCallCard_id}" class="frame" 	 
	 onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('CmpCallCard_{CmpCallCard_id}_toolbar').style.visibility='visible'" 
	 onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('CmpCallCard_{CmpCallCard_id}_toolbar').style.visibility='hidden'">

        <div style="float: right">
            <div id="CmpCallCard_{CmpCallCard_id}_toolbar" class="toolbar" style="visibility: hidden">
				<?php if (!$isMseDepers) { ?>
				<a id="CmpCallCard_{CmpCallCard_id}_viewCmpCallCard" class="button icon icon-edit16" title="Просмотр карты вызова"><span></span></a>
				<a id="CmpCallCard_{CmpCallCard_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
				<?php }?>
            </div>
        </div>

		<div id="CmpCallCard_{CmpCallCard_id}_content">
			<div style="text-align: left" class="data-table">
				<p>
					<strong>
						Карта вызова СМП № {Day_num} <?php echo isset($CmpCallCard_NumvPr) ? $CmpCallCard_NumvPr : '' ?>
						/
						{Year_num} <?php echo isset($CmpCallCard_NgodPr) ? $CmpCallCard_NgodPr : '' ?>
					</strong>
				</p>
				<p>ФИО: <strong><?php if ($isMseDepers) { ?>***<?php } else { ?>{Fam} {Name} {Middle}<?php }?></strong></p>
				<div style="text-align: left">
					<?php
					if (in_array(getRegionNumber(), array(2,10,19,59,66))) {
						if (!empty($CmpCallCardCostPrint_setDT)) {
							$costprint = "<p>Стоимость лечения: ".$CostPrint."</p>";
							if ($CmpCallCardCostPrint_IsNoPrint == 2) {
								$costprint .= "<p>Отказ в получении справки";
							} else {
								$costprint .= "<p>Справка выдана";
							}

							$costprint .= " ".$CmpCallCardCostPrint_setDT."</p>";
							echo $costprint;
						}
					}
					?>
					<p>Дата и время вызова: <strong>{AcceptDateTime}</strong></p>
					<p>Место вызова: <strong>{CmpCallPlace}</strong></p>
					<p>Причина вызова: <strong>{CmpReason_Name}</strong></p>
					<p>Основной диагноз: <strong>{Diag}</strong></p>
					<p>Диагноз (Адис): <strong>{CmpDiag_Name}</strong></p>
					<p>Результат: <strong>{CmpResult_Name}</strong></p>
					<p>Исход: <strong>{ResultDeseaseType_Name}</strong></p>
					<p>Врач: <strong>{MedPersonal_Name}</strong></p>
				</div>

			</div>
		</div>

</div>

