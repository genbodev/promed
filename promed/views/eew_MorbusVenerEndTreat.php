<?php 
	$is_allow_edit = (1 == $accessType);
?>

<div id="MorbusVenerEndTreatList_{MorbusVener_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusVenerEndTreatList_{MorbusVener_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusVenerEndTreatList_{MorbusVener_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusVenerEndTreatList_{MorbusVener_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Контроль по окончании лечения</span></h2>
        <div id="MorbusVenerEndTreatList_{MorbusVener_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusVenerEndTreatList_{MorbusVener_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusVenerEndTreatList_{MorbusVener_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusVenerEndTreatTable_{MorbusVener_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<col />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата назначенной явки</th>
			<th>Дата вызова</th>
			<th>Дата фактической явки</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
