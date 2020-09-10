<?php 
	$is_allow_edit = (1 == $accessType);
?>

<div id="MorbusTubPrescrList_{MorbusTub_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubPrescrList_{MorbusTub_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubPrescrList_{MorbusTub_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusTubPrescrList_{MorbusTub_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Лекарственные назначения</span></h2>
        <div id="MorbusTubPrescrList_{MorbusTub_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusTubPrescrList_{MorbusTub_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusTubPrescrList_{MorbusTub_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusTubPrescrTable_{MorbusTub_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<col />
		        <col />
		        <col />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Фаза химиотерапии</th>
			<th>Дата назначения / Дата отмены</th>
			<th>Противотуберкулезный препарат</th>
            <th>Суточная доза</th>
            <th>Общее кол-во доз</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
