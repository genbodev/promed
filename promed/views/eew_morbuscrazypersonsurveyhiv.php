<?php 
	$accessMorbusCrazyPersonSurveyHIV = (isset($accessMorbusCrazyPersonSurveyHIV))?$accessMorbusCrazyPersonSurveyHIV:1;
	$is_allow_edit = (($accessType == 1) && ($accessMorbusCrazyPersonSurveyHIV == 1));
?>

<div id="MorbusCrazyPersonSurveyHIVList_{MorbusCrazy_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonSurveyHIVList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonSurveyHIVList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusCrazyPersonSurveyHIVList_{MorbusCrazy_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Обследование на ВИЧ</span></h2>
        <div id="MorbusCrazyPersonSurveyHIVList_{MorbusCrazy_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusCrazyPersonSurveyHIVList_{MorbusCrazy_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusCrazyPersonSurveyHIVList_{MorbusCrazy_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusCrazyPersonSurveyHIVTable_{MorbusCrazy_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата</th>
			<th>Результат</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
