<?php
	$empty_str = '<span style="color: #666;">Не указано</span>';
?>
<div id="CmpCallCardCostPrint_{CmpCallCardCostPrint_id}" class="frame"
	 onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('CmpCallCardCostPrint_{CmpCallCardCostPrint_id}_toolbar').style.visibility='visible'"
	 onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('CmpCallCardCostPrint_{CmpCallCardCostPrint_id}_toolbar').style.visibility='hidden'">

        <div style="float: right">
            <div id="CmpCallCardCostPrint_{CmpCallCardCostPrint_id}_toolbar" class="toolbar" style="visibility: hidden">
            </div>
        </div>

		<div id="CmpCallCardCostPrint_{CmpCallCardCostPrint_id}_content">
		
			<div style="text-align: left" class="data-table">
				<p><strong>Справка о стоимости лечения</strong></p>
				<div style="text-align: left">
					<div style='clear:both;'><div style='float:left;padding:5px 0;'>Дата выдачи: <span id='CmpCallCardCostPrint_{CmpCallCardCostPrint_id}_inputSetDate' style='color:#000;' class='link' dataid='{CmpCallCardCostPrint_setDate}'><?php if (empty($CmpCallCardCostPrint_setDate)) { echo $empty_str; } else { echo $CmpCallCardCostPrint_setDate; }?></span></div><div id="CmpCallCardCostPrint_{CmpCallCardCostPrint_id}_inputareaSetDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div style='clear:both;'><div style='float:left;padding:5px 0;'>Отказ: <span id='CmpCallCardCostPrint_{CmpCallCardCostPrint_id}_inputIsNoPrint' style='color:#000;' class='link' dataid='{CmpCallCardCostPrint_IsNoPrint}'><?php if (empty($CmpCallCardCostPrint_IsNoPrintText)) { echo $empty_str; } else { echo $CmpCallCardCostPrint_IsNoPrintText; }?></span></div><div id="CmpCallCardCostPrint_{CmpCallCardCostPrint_id}_inputareaIsNoPrint" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div style='clear:both;'><div style='float:left;padding:5px 0;'>Стоимость лечения: {CmpCallCardCostPrint_Cost}</div></div>
					<br>
				</div>

			</div>
		</div>

</div>

