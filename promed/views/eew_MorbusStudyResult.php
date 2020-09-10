<?php 
	$is_allow_edit = (1 == $accessType);
?>

<div id="MorbusStudyResultList_{MorbusTub_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusStudyResultList_{MorbusTub_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusStudyResultList_{MorbusTub_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusStudyResultList_{MorbusTub_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Направление на проведение микроскопических исследований на туберкулез</span></h2>
        <div id="MorbusStudyResultList_{MorbusTub_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusStudyResultList_{MorbusTub_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusStudyResultList_{MorbusTub_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusStudyResultTable_{MorbusTub_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<col />
				<col />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Фаза химиотерапии</th>
			<th>Дата регистрации результата</th>
			<th>Тип исследования</th>
			<th>Результат исследования</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
