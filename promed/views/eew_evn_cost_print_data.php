<?php
	$empty_str = '<span style="color: #666;">Не указано</span>';
?>
<div id="EvnCostPrint_{EvnCostPrint_id}" class="frame"
	 onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnCostPrint_{EvnCostPrint_id}_toolbar').style.visibility='visible'"
	 onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnCostPrint_{EvnCostPrint_id}_toolbar').style.visibility='hidden'">

        <div style="float: right">
            <div id="EvnCostPrint_{EvnCostPrint_id}_toolbar" class="toolbar" style="visibility: hidden">
            </div>
        </div>

		<div id="EvnCostPrint_{EvnCostPrint_id}_content">

			<div style="text-align: left" class="data-table">
				<p><strong>Справка о стоимости лечения</strong></p>
				<div style="text-align: left">
					<div style='clear:both;'><div style='float:left;padding:5px 0;'>Дата выдачи: <span id='EvnCostPrint_{EvnCostPrint_id}_inputSetDate' style='color:#000;' class='link' dataid='{EvnCostPrint_setDate}'>{EvnCostPrint_setDate}</span></div><div id="EvnCostPrint_{EvnCostPrint_id}_inputareaSetDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<?php if(getRegionNumber()=='19'){?>
					<div style='clear:both;'><div style='float:left;padding:5px 0;'>Номер справки: {EvnCostPrint_Number}</div></div>
					<?php }?>
					<div style='clear:both;'><div style='float:left;padding:5px 0;'>Отказ: <span id='EvnCostPrint_{EvnCostPrint_id}_inputIsNoPrint' style='color:#000;' class='link' dataid='{EvnCostPrint_IsNoPrint}'>{EvnCostPrint_IsNoPrintText}</span></div><div id="EvnCostPrint_{EvnCostPrint_id}_inputareaIsNoPrint" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div style='clear:both;'><div style='float:left;padding:5px 0;'>Стоимость лечения: {EvnCostPrint_Cost}</div></div>
					<div style='clear:both;'><div style='float:left;padding:5px 0;'>Выдана: {EvnCostPrint_DeliveryType}</div></div>
					<br>
				</div>

			</div>
		</div>

</div>

