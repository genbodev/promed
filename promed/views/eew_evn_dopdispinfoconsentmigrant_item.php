<?php
$is_allow_edit_head = true;
if (!havingGroup('DrivingCommissionReg') && isset($DispClass_id) && $DispClass_id == 26 && getRegionNick() == 'perm') {
	// только регистратор может редактировать заголовок
	$is_allow_edit_head = false;
}
?>
<tr id="DopDispInfoConsent_{DopDispInfoConsent_id}" class="list-item" ddic_id="{DopDispInfoConsent_id}" stl_id="{SurveyTypeLink_id}">
	<td>{SurveyType_Name}</td>
	<td <?php if (getRegionNumber() != 59) { ?>style="display: none;"<?php } ?>>
		<input class='ddic_isearlier' id="DopDispInfoConsent_{DopDispInfoConsent_id}_inputIsEarliersave" type="checkbox" <?php if(!$is_allow_edit_head) { echo 'disabled'; } ?> {DopDispInfoConsent_IsEarlierChecked} <?php if ($SurveyType_Code == 158) { ?>style="visibility: hidden;"<?php } ?> />
	</td>
	<td><input class='ddic_isagree' id="DopDispInfoConsent_{DopDispInfoConsent_id}_inputsave" type="checkbox" <?php if(!$is_allow_edit_head) { echo 'disabled'; } ?> {DopDispInfoConsent_IsAgreeChecked} /></td>
</tr>