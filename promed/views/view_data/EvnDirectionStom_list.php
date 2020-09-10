<div id="EvnDirectionStomList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDirectionStomList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDirectionStomList_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="EvnDirectionStomList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Направления</span></h2>
        <div id="EvnDirectionStomList_{pid}_toolbar" class="toolbar">
            <a id="EvnDirectionStomList_{pid}_add" class="button icon icon-add16" title="Создать направление"><span></span></a>
			<a id="EvnDirectionStomList_{pid}_addtome" class="button icon" title="Записать к себе"><span style="padding-left: 2px;">Записать к себе</span></a>
        </div>
    </div>

    <table id="EvnDirectionStomTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

        <col class="first last" />
        <col class="toolbar"/>

        {items}

        {item_arr}
    	{/item_arr}

    </table>

</div>
