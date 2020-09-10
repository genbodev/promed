<?php 
	$is_allow_edit = (1 == $accessType);
?>

<div id="MorbusCrazyPersonSocDangerActList_{MorbusCrazy_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonSocDangerActList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonSocDangerActList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusCrazyPersonSocDangerActList_{MorbusCrazy_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Общественно-опасные действия</span></h2>
        <div id="MorbusCrazyPersonSocDangerActList_{MorbusCrazy_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusCrazyPersonSocDangerActList_{MorbusCrazy_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusCrazyPersonSocDangerActList_{MorbusCrazy_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusCrazyPersonSocDangerActTable_{MorbusCrazy_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата совершения ООД</th>
			<th>Статья УК</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
