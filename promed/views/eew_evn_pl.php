<div id="EvnPL_{EvnPL_id}">

    <div id="EvnPL_data_wrap" class="frame evn_pl" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPL_{EvnPL_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPL_{EvnPL_id}_toolbar').style.display='none'">
		<?php if (!empty($AlertReg_Msg)) { ?><p style="font-weight:bold; color: #DD3333">{AlertReg_Msg}</p><?php } ?>
        {EvnPL_data}

        {EvnStick}

        {EvnMediaData}

        <div class="clear">
        </div>
    </div>

	{EvnVizitPL}

</div>
