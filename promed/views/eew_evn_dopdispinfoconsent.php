<div id="DopDispInfoConsentList_{pid}" parent_object="{Object}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DopDispInfoConsentList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DopDispInfoConsentList_{pid}_toolbar').style.display='none'">
	<div class="caption">
		<h2><span id="DopDispInfoConsentList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			Информированное добровольное согласие
		</span></h2>
		<div id="DopDispInfoConsentList_{pid}_toolbar" class="toolbar">
			<a id="DopDispInfoConsentList_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
		</div>
	</div>
	<table id="DopDispInfoConsentTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="first" />
		<col />
		<col class="last" />
		<thead>
		<tr>
			<th>Осмотр, исследование</th>
			<th>Пройдено ранее</th>
			<th>Согласие гражданина</th>
		</tr>
		</thead>

		<tbody id="DopDispInfoConsentList_{pid}">

		{items}

		</tbody>

	</table>
	<?php
		if (!empty($DispClass_id) && in_array($DispClass_id, array('1', '2', '3', '7', '6', '9', '10'))) {
			if ($DispClass_id != 10 || havingGroup('ProfReg')) {
				echo '<br><a id="DopDispInfoConsentList_{pid}_save" class="button" title=""><span>Сохранить согласие</span></a>';
			}
		}
	?>

</div>