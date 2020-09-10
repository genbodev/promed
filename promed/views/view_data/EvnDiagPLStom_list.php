<?php
	$is_morbus = (strtotime($EvnPLStom_setDate) >= getEvnPLStomNewBegDate());

	if ( $is_morbus === true ) {
?>
<div id="EvnDiagPLStomList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagPLStomList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagPLStomList_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="EvnDiagPLStomList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Заболевания</span></h2>
        <div id="EvnDiagPLStomList_{pid}_toolbar" class="toolbar">
            <a id="EvnDiagPLStomList_{pid}_add" class="button icon icon-add16" title="Добавить заболевание"><span></span></a>
        </div>
    </div>

    <table id="EvnDiagPLStomTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

        <col class="first last" />
        <col class="toolbar"/>

    	{items}

    </table>

</div>
<?php
	}
?>