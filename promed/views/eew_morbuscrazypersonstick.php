<?php 
	$is_allow_edit = (1 == $accessType);
?>

<div id="MorbusCrazyPersonStickList_{MorbusCrazy_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonStickList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonStickList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusCrazyPersonStickList_{MorbusCrazy_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Временная нетрудоспособность</span></h2>
        <div id="MorbusCrazyPersonStickList_{MorbusCrazy_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusCrazyPersonStickList_{MorbusCrazy_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusCrazyPersonStickList_{MorbusCrazy_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusCrazyPersonStickTable_{MorbusCrazy_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<col />
				<col />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата открытия БЛ </th>
			<th>Дата закрытия БЛ</th>
			<th>Число дней ВН</th>
			<th>Диагноз</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
