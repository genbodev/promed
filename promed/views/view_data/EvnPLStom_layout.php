<div id="EvnPLStom_{EvnPLStom_id}">

    <div id="EvnPLStom_data_wrap" class="frame evn_pl" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLStom_{EvnPLStom_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLStom_{EvnPLStom_id}_toolbar').style.display='none'">
		<?php if (!empty($AlertReg_Msg)) { ?><p style="font-weight:bold; color: #DD3333">{AlertReg_Msg}</p><?php } ?>
        {EvnPLStom_data}

        {EvnStick}

        {EvnMediaData}

        <div class="clear">
        </div>
    </div>

	{EvnVizitPLStom}

</div>
