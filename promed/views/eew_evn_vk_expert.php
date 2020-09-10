<div id="EvnVKExpertList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnVKExpertList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnVKExpertList_{pid}_toolbar').style.display='none'">
	<div class="caption">
		<h2><span id="EvnVKExpertList_{pid}_toggleDisplayExpertList" class="<?php if (!empty($items)) { ?>collapsible<?php } ?> ">
			Состав экспертов
		</span></h2>
		<div id="EvnVKExpertList_{pid}_toolbar" class="toolbar">
			<?php if(getRegionNick() != 'vologda' || $EvnVK_isInternal == 2) { ?> 
			<a id="EvnVKExpertList_{pid}_addExpert" class="button icon icon-add16" title="Добавить"><span></span></a>
			<?php } ?>
		</div>
	</div>
	<table id="EvnVKExpertTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col style="width: 72%" class="first" />
		<?php  if(getRegionNick() == 'vologda') { ?> 
		<col />
		<col />
		<?php } ?>
		<col class="last" />
		<col class="toolbar" />

		<thead>
		<tr>
			<th>Врач ВК</th>
			<th>Эксперт</th>
			<?php  if(getRegionNick() == 'vologda') { ?> 
			<th>Решение эксперта</th>
			<th>Комментарий</th>
			<?php } ?>
			<th class="toolbar">
		</tr>
		</thead>

		<tbody id="EvnVKExpertList_{pid}">

		{items}

		</tbody>

	</table>

</div>