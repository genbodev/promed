<?php 
	$accessMorbusCrazyDrugVolume = (isset($accessMorbusCrazyDrugVolume))?$accessMorbusCrazyDrugVolume:1;
	$is_allow_edit = (($accessType == 1) && ($accessMorbusCrazyDrugVolume == 1));
?>
<div id="MorbusCrazyDrugVolumeList_{MorbusCrazy_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDrugVolumeList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDrugVolumeList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusCrazyDrugVolumeList_{MorbusCrazy_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Полученный объем наркологической помощи</span></h2>
        <div id="MorbusCrazyDrugVolumeList_{MorbusCrazy_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusCrazyDrugVolumeList_{MorbusCrazy_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusCrazyDrugVolumeList_{MorbusCrazy_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusCrazyDrugVolumeTable_{MorbusCrazy_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<col />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>МО</th>
			<th>Дата оказания помощи</th>
			<th>Тип объема наркологической помощи</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
