<?php
/**
 * @package      MorbusOnko
 * @author       Куракин Александр
 * @version      01.2017
 */
$is_allow_edit = (empty($accessType) || 'edit' == $accessType);
?>
<div id="MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this) && window.getComputedStyle(document.getElementById('MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}_addEvnOnkoNotifyContainer')).display == 'none') document.getElementById('MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}_toolbar').style.display='none'">
	<div class="caption">
		<h2><span id="MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Извещения</span></h2>
		<div id="MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}_addEvnOnkoNotifyContainer" style="float:right; display: <?php if (empty($isDisabledAddEvnOnkoNotify)) { ?>block<?php } else { ?>none<?php } ?>;">
            <a id="MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}_addEvnOnkoNotify" sopdiagid="{EvnDiagPLSop_id}" class="button icon icon-add16" title="Создать Извещение о больном с впервые в жизни установленным диагнозом злокачественного новообразования"><span></span></a>
        </div>
        <div style="display: none;" id="MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}_toolbar" class="toolbar" >
        	<a id="MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
	</div>

	<table id="MorbusOnkoEvnNotifyTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
		<col class="first" />
		<col />
		<col />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Дата</th>
			<th>Статус</th>
			<th>Причина отклонения</th>
			<th>Комментарий</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
		{items}
		</tbody>
	</table>
</div>
