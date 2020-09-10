<tr id="EvnUslugaDispDop_{DopDispInfoConsent_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaDispDop_{DopDispInfoConsent_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaDispDop_{DopDispInfoConsent_id}_toolbar').style.display='none'">
	<?php

		if (!empty($DispClass_id) && $DispClass_id == 10) {

			switch($SurveyType_Code) {
				case '11':
					if (!havingGroup('ProfUrine') && !havingGroup('ProfPed')) {
						$accessType = 'view';
					}
					break;
				case '17':
					if (!havingGroup('ProfElectro') && !havingGroup('ProfPed')) {
						$accessType = 'view';
					}
					break;
				case '18':
					if (!havingGroup('ProfNeur')) {
						$accessType = 'view';
					}
					break;
				case '29':
					if (!havingGroup('ProfSurg')) {
						$accessType = 'view';
					}
					break;
				case '32':
					if (!havingGroup('ProfTrauma')) {
						$accessType = 'view';
					}
					break;
				case '102':
					if (!havingGroup('ProfOto')) { $accessType = 'view'; }
					break;
				case '9':
				case '127':
					if (!havingGroup('ProfBlood') && !havingGroup('ProfPed')) {
						$accessType = 'view';
					}
					break;
				case '27':
					if (!havingGroup('ProfPed')) {
						$accessType = 'view';
					}
					break;
				case '28':
					if (!havingGroup('ProfOphth')) {
						$accessType = 'view';
					}
					break;
			}
		}

		$linkName = 'edit';
		if (!empty($accessType)) { $linkName = $accessType; }
	?>
	<td>
		<?php if (empty($EvnUslugaDispDop_id) && !empty($accessType) && $accessType != 'edit') { echo $SurveyType_Name; } else { ?>
			<a id="EvnUslugaDispDop_{DopDispInfoConsent_id}_<?php echo $linkName; ?>"
			   class="" title="<?php echo empty($accessType) || $accessType == 'edit' ? "Редактировать" : "Просмотреть"; ?>">
				{SurveyType_Name}
			</a>
		<?php } ?>
	</td>
	<td>{EvnUslugaDispDop_didDate}</td>
	<?php if (!empty($EvnPrescr_id)) { ?>
	<td><div class="EvnUslugaDispDopDirInfo"><div class='dirinfoinner'><img src='/img/icons/place_icon.png' />&nbsp;{RecTo}
		<?php if (isset($EvnXml_id) && !empty($EvnXml_id)) { ?> 
		<span class="collapsible" id="EvnUslugaDispDop_{DopDispInfoConsent_id}_xml">Результаты...</span>
		<?php } ?>
	</div>
	<div class='dirinfoinner'><img src='/img/icons/napr_icon.png' />&nbsp;<span id='EvnUslugaDispDop_{DopDispInfoConsent_id}_viewdir' class='link' title='Просмотр направления'>Направление {EvnDirection_Num}</span></div><div class='dirinfoinner'><img src='/img/icons/time_icon.png' />&nbsp;{RecDate}</div></div></td>
	<?php } elseif (!empty($EvnDirection_id)) { ?>
	<td><div class="EvnUslugaDispDopDirInfo"><div class='dirinfoinner'>{RecTo} / <span id='EvnUslugaDispDop_{DopDispInfoConsent_id}_viewdir' class='link' title='Просмотр направления'>Направление {EvnDirection_Num}</span> /&nbsp;{RecDate}</div></td>
	<?php } else { ?>
	<td></td>
	<?php } ?>
	<td class="toolbar">
		<div id="EvnUslugaDispDop_{DopDispInfoConsent_id}_toolbar" class="toolbar">
			<!-- <a id="EvnUslugaDispDop_{DopDispInfoConsent_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a> -->
<?php
		if (!empty($accessType) && $accessType == 'edit') {
			if (!empty($EvnDirection_id)) {
?>
			<a id="EvnUslugaDispDop_{DopDispInfoConsent_id}_openDirActionMenu" class="button prescrMenuButton"><span></span></a>
<?php
			} else {
?>
			<a id="EvnUslugaDispDop_{DopDispInfoConsent_id}_add" class="button icon icon-add16" title="Создать назначение"><span></span></a>
<?php
			}
		}
?>
		</div>
	</td>
</tr>