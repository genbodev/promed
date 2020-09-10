<div id="EvnPS_{EvnPS_id}">

    <div id="EvnPS_data_wrap" class="frame evn_pl" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPS_{EvnPS_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPS_{EvnPS_id}_toolbar').style.display='none'">
		<?php if (!empty($AlertReg_Msg)) { ?><p style="font-weight:bold; color: #DD3333">{AlertReg_Msg}</p><?php } ?>
        {EvnPS_data}

        {EvnStick}

        {EvnMediaData}

        <div class="clear">
        </div>
    </div>

	{EvnSection}

</div>
