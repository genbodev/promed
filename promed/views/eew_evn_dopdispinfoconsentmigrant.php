<?php
$is_allow_edit_head = true;
if (!havingGroup('DrivingCommissionReg') && isset($DispClass_id) && $DispClass_id == 26 && getRegionNick() == 'perm') {
	// только регистратор может редактировать заголовок
	$is_allow_edit_head = false;
}
?>
<div class="data-table">

	<table id="DopDispInfoConsentTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="first" />
		<?php if (getRegionNumber() == 59) { ?> <col /><?php } ?>
		<col class="last" />
		<thead>
		<tr>
			<th>Наименование осмотра (исследования)</th>
			<?php if (getRegionNumber() == 59) { ?> <th>Пройдено ранее</th> <?php } ?>
			<th>Согласие на проведение</th>
		</tr>
		</thead>

		<tbody id="DopDispInfoConsentList_{pid}" parent_object="{Object}">

		{items}

		</tbody>

	</table>
	
	<br>
	
	<?php
		if (!empty($DispClass_id) && in_array($DispClass_id, array('26'))) {
			if ($is_allow_edit_head) {
				echo '<a id="DopDispInfoConsentList_{pid}_save" class="button icon icon-save16" title=""><span>&nbsp;Сохранить&nbsp;</span></a>';
			} else {
				echo '<a class="button icon icon-save16 disabled" title=""><span>&nbsp;Сохранить&nbsp;</span></a>';
			}
		}
	?>
	<?php if ($DispClass_id != 26 || getRegionNick() == 'perm') { ?>
	<a id="DopDispInfoConsentList_{pid}_printmigr" class="button icon icon-print16" title="Печать"><span>&nbsp;Печать&nbsp;</span></a>
	 <?php } ?>
</div>