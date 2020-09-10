<?php 
	$is_allow_edit = (1 == 1);
?>

<div id="MorbusTubAdviceOperList_{MorbusTub_pid}_{pid}" class="data-table" style="display: <?php echo (empty($items))?'none':'block'; ?>;">

    <table id="MorbusTubAdviceOperTable_{MorbusTub_pid}_{pid}">
				<col class="first" />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата операции</th>
			<th>Тип операции</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
