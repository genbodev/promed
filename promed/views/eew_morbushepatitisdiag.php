<?php 
	$is_allow_edit = ('edit' == $AccessType);
?>
<div id="MorbusHepatitisDiagList_{MorbusHepatitis_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisDiagList_{MorbusHepatitis_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisDiagList_{MorbusHepatitis_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusHepatitisDiagList_{MorbusHepatitis_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Диагноз</span></h2>
        <div id="MorbusHepatitisDiagList_{MorbusHepatitis_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusHepatitisDiagList_{MorbusHepatitis_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusHepatitisDiagList_{MorbusHepatitis_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusHepatitisDiagTable_{MorbusHepatitis_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
        <col class="first" />
        <col />
		<col />
		<col />
		<col />
		<col />
        <col class="last" />
        <col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата</th>
			<th>ЛПУ</th>
			<th>Профиль/Врач</th>
			<th>Диагноз</th>
			<th>Дата подтверждения</th>
			<th>Активность</th>
			<th>Фиброз</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
