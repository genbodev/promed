<?php
	$is_allow_edit_head = 'edit';
	if (!empty($DispClass_id) && $DispClass_id == 26 && !havingGroup('DrivingCommissionReg') && getRegionNick() == 'perm') {
		// только регистратор может редактировать заголовок
		$is_allow_edit_head = 'view';
	}
?>
<tr id="DopDispInfoConsent_{DopDispInfoConsent_id}" class="list-item" ddic_id="{DopDispInfoConsent_id}" stl_id="{SurveyTypeLink_id}">
	<td>{SurveyType_Name}</td>
	<td><input class='ddic_isearlier' type="checkbox" <?php if(!$is_allow_edit_head) { echo 'disabled'; } ?> {DopDispInfoConsent_IsEarlierChecked} /></td>
	<td><input class='ddic_isagree' type="checkbox" <?php if(!$is_allow_edit_head) { echo 'disabled'; } ?> {DopDispInfoConsent_IsAgreeChecked} /></td>
</tr>