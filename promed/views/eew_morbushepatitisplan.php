<?php 
	$is_allow_edit = empty($hasOpenPlan) && (empty($AccessType) || 'edit' == $AccessType);
?>
<div id="MorbusHepatitisPlanList_{MorbusHepatitis_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisPlanList_{MorbusHepatitis_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisPlanList_{MorbusHepatitis_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusHepatitisPlanList_{MorbusHepatitis_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">План лечения Гепатита C</span></h2>
        <div id="MorbusHepatitisPlanList_{MorbusHepatitis_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusHepatitisPlanList_{MorbusHepatitis_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusHepatitisPlanList_{MorbusHepatitis_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusHepatitisPlanTable_{MorbusHepatitis_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
        <col class="first" />
        <col />
        <col />
        <col />
        <col class="last" />
        <col class="toolbar"/>
		<thead>
 		<tr>
			<th>Год лечения</th>
			<th>Месяц лечения</th>
			<th>Условия оказания МП</th>
			<th>МО планируемого лечения</th>
			<th>Лечение проведено</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>
		
</div>