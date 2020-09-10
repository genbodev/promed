<?php 
	$accessMorbusCrazyBasePS = (isset($accessMorbusCrazyBasePS))?$accessMorbusCrazyBasePS:1;
	$is_allow_edit = (($accessType == 1) && ($accessMorbusCrazyBasePS == 1));
	
?>

<div id="MorbusCrazyBasePSList_{MorbusCrazy_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyBasePSList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyBasePSList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusCrazyBasePSList_{MorbusCrazy_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Сведения о госпитализациях</span></h2>
        <div id="MorbusCrazyBasePSList_{MorbusCrazy_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusCrazyBasePSList_{MorbusCrazy_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusCrazyBasePSList_{MorbusCrazy_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusCrazyBasePSTable_{MorbusCrazy_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<col />
				<col />
				<col />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>МО</th>
			<th>Цель госпитализации</th> 
			<th>Период лечения</th>
			<th>Диагноз в соответствии с V классом МКБ-10 (*)</th>
			<th>Диагноз основного заболевания (+)</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
