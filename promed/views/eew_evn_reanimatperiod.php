<!--<div id="EvnReanimatPeriodList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReanimatPeriodList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReanimatPeriodList_{pid}_toolbar').style.display='none'">-->
<div id="EvnReanimatPeriodList_{pid}" class="data-table" <?php if (empty($items)){ ?> style="display:none " <?php } ?>    >
    <div class="caption">
        <h2>Реанимация</h2>
        
        
<!--        <div id="EvnReanimatPeriodList_{pid}_toolbar" class="toolbar">
            <a id="EvnReanimatPeriodList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
        </div>-->
    </div>
    
    <table>
        <col class="first last" />
        <col class="toolbar"/>

    	{items}
<!--		{EvnReanimatPeriod_data}-->
    </table>

</div>