<div id="CmpCloseCard_{CmpCloseCard_id}" class="frame" 	 
	 onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('CmpCloseCard_{CmpCloseCard_id}_toolbar').style.visibility='visible'" 
	 onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('CmpCloseCard_{CmpCloseCard_id}_toolbar').style.visibility='hidden'">

        <div style="float: right">
            <div id="CmpCloseCard_{CmpCloseCard_id}_toolbar" class="toolbar" style="visibility: hidden">
				<?php if (!$isMseDepers) { ?>
            	<a id="CmpCloseCard_{CmpCloseCard_id}_viewCmpCloseCard" class="button icon icon-edit16" title="Просмотр карты вызова"><span></span></a>
				<a id="CmpCloseCard_{CmpCloseCard_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
				<?php }?>
            </div>
        </div>

		<div id="CmpCloseCard_{CmpCloseCard_id}_content">
		
			<div style="text-align: left" class="data-table">
				<p><strong>
						Карта вызова СМП № {Day_num} <?php echo isset($CmpCloseCard_DayNumPr) ? $CmpCloseCard_DayNumPr : '' ?>
						/
						{Year_num} <?php echo isset($CmpCloseCard_YearNumPr) ? $CmpCloseCard_YearNumPr : '' ?>
					</strong></p>
				<p>ФИО: <strong><?php if ($isMseDepers) { ?>***<?php } else { ?>{Fam} {Name} {Middle}<?php }?></strong></p>
				<div style="text-align: left">
					<?php
					if (in_array(getRegionNumber(), array(2,3,10,19,30,59,60,66))) {
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
					<p>Диагноз: <strong>{Diag}</strong></p>
					<?php
					if (!in_array(getRegionNumber(), array(58))) {
					?>
						<p>Фельдшер принявший вызов: <strong>{FeldsherAcceptName}</strong></p>
					<?php
					}
					?>
					<p>Врач: <strong>{EmergencyTeam_HeadShift_Name}</strong></p>
					<?php
					if (!in_array(getRegionNumber(), array(101))) {
						?>
						<p>Результат выезда: <strong>{ResultUfa_id}</strong></p>
						<?php
					}
					?>
				</div>

			</div>
		</div>

</div>

