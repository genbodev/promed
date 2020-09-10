<?php 
	$is_allow_edit = (1 == $accessType);
?>

<div id="MorbusTubConditChemList_{MorbusTub_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubConditChemList_{MorbusTub_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubConditChemList_{MorbusTub_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusTubConditChemList_{MorbusTub_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Режимы химиотерапии</span></h2>
        <div id="MorbusTubConditChemList_{MorbusTub_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusTubConditChemList_{MorbusTub_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusTubConditChemList_{MorbusTub_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusTubConditChemTable_{MorbusTub_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<!--<col />-->
				<col />
				<col />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Режим химиотерапии</th>
			<!--<th>Схема лечения</th>-->
			<th>Фаза химиотерапии</th>
			<th>Место<br>проведения</th>
			<th>Период лечения</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
