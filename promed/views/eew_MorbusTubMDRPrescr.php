<?php 
	$is_allow_edit = (1 == $accessType);
?>

<div id="MorbusTubMDRPrescrList_{MorbusTub_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubMDRPrescrList_{MorbusTub_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubMDRPrescrList_{MorbusTub_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusTubMDRPrescrList_{MorbusTub_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Лечебные мероприятия</span></h2>
        <div id="MorbusTubMDRPrescrList_{MorbusTub_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusTubMDRPrescrList_{MorbusTub_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusTubMDRPrescrList_{MorbusTub_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusTubMDRPrescrTable_{MorbusTub_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
		        <col />
		        <col />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата начала / Дата окончания</th>
			<th>Препарат</th>
            <th>Назначено дней лечения</th>
            <th>Пропущено дней лечения</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
