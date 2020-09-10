<?php
	$is_morbus = (strtotime($EvnPLStom_setDate) >= getEvnPLStomNewBegDate());
?>
<div id="EvnUslugaStomList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaStomList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaStomList_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="EvnUslugaStomList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Услуги</span></h2>
        <div id="EvnUslugaStomList_{pid}_toolbar" class="toolbar">
<?php
	if ( $is_morbus === false ) {
?>
            <a id="EvnUslugaStomList_{pid}_add" class="button icon icon-add16" title="Оформить оказание услуги"><span></span></a>
            <a id="EvnUslugaStomList_{pid}_addByMes" class="button icon" title="Добавить все услуги по МЭС"><span style="padding-left: 2px;">Добавить все услуги по МЭС</span></a>
<?php
	}
?>
        </div>
    </div>

    <table id="EvnUslugaStomTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

        <col class="first last" />
        <col class="toolbar"/>

    	{items}

    </table>

</div>
