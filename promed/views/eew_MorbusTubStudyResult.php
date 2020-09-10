<?php 
	$is_allow_edit = (1 == $accessType);
?>

<div id="MorbusTubStudyResultList_{MorbusTub_pid}_{pid}">
<div class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubStudyResultList_{MorbusTub_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubStudyResultList_{MorbusTub_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusTubStudyResultList_{MorbusTub_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Результаты исследований</span></h2>
        <div id="MorbusTubStudyResultList_{MorbusTub_pid}_{pid}_toolbar" class="toolbar">
            <!--a id="MorbusTubStudyResultList_{MorbusTub_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a-->
			<a id="MorbusTubStudyResultList_{MorbusTub_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusTubStudyResultTable_{MorbusTub_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<col />
				<col />
				<col />
				<col />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Месяц/фаза лечения</th>
			<th>Микроскопия</th>
			<th>Посев</th>
			<th>Гистология</th>
			<th>Рентген</th>
			<th>Вес</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
</div>
