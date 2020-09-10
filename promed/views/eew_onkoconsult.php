<?php
/**
 * @package      MorbusOnko
 */
?>
<div id="OnkoConsultList_{MorbusOnko_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('OnkoConsultList_{MorbusOnko_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('OnkoConsultList_{MorbusOnko_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="OnkoConsultList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Сведения о проведении консилиума</span></h2>
        <div id="OnkoConsultList_{MorbusOnko_pid}_{pid}_toolbar" class="toolbar">
            <a id="OnkoConsultList_{MorbusOnko_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
			<a id="OnkoConsultList_{MorbusOnko_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="OnkoConsultTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">			
        <col class="first" />
        <col />
		<col class="last" />
        <col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата проведения</th>
			<th>Тип лечения</th>
			<th>Результат проведения</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>
</div>