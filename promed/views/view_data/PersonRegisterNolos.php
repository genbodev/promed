<?php
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = (1 == $accessType);
?>
<div id="PersonRegisterNolos_{PersonRegister_id}">
	<div class="frame signal-info" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonRegisterNolos_{PersonRegister_id}_toolbarPers').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonRegisterNolos_{PersonRegister_id}_toolbarPers').style.display='none'">

		<div id="PersonRegisterNolos_{PersonRegister_id}" class="person-info"  onmouseover="if (isMouseLeaveOrEnter(event, this)) {
		document.getElementById('PersonRegisterNolos_{PersonRegister_id}_toolbarAttach').style.display='block';
		if (document.getElementById('PersonCard_begDate_{PersonRegister_id}').innerHTML == '') {
			document.getElementById('PersonRegisterNolos_{PersonRegister_id}_printMedCard').style.display='none';
		}
	}" onmouseout="if (isMouseLeaveOrEnter(event, this)) {
		document.getElementById('PersonRegisterNolos_{PersonRegister_id}_toolbarAttach').style.display='none';
	} ">

		<div class="columns">
			<div class="left">
				<div id="PersonRegisterNolos_{PersonRegister_id}_editPhoto" class="photo" title="кликните для загрузки другой фотографии пациента">
					<img src="{PersonPhotoThumbName}" width="68" height="106" alt="фото" name="photo_person_{Person_id}"/>
				</div>
				<div class="data">
					<h1><strong>{Person_Surname} {Person_Firname} {Person_Secname}</strong>, Д/р: <strong>{Person_Birthday}</strong></h1>
					<p>Пол: <strong>{Sex_Name}</strong></p>
					<p>Соц. статус: <strong>{SocStatus_Name}</strong>, СНИЛС: <strong>{Person_Snils}</strong></p>
					<p>Регистрация: <strong>{Person_RAddress}</strong></p>
					<p>Проживает: <strong>{Person_PAddress}</strong></p>
					<p>Полис: <strong>{Polis_Ser} {Polis_Num}</strong>, Выдан: <strong>{Polis_begDate}</strong>, <strong>{OrgSmo_Name}</strong>, Закрыт: <strong>{Polis_endDate}</strong></p>
					<p>Документ: <strong>{Document_Ser} {Document_Num}</strong>, Выдан: <strong>{Document_begDate}, {OrgDep_Name}</strong></p>
					<p>Работа: <strong>{Person_Job}</strong></p>
					<div class="data-row-container"><div class="data-row" style="padding: 0">Декретированная группа населения: <span<?php if ($is_allow_edit) { ?> id="PersonRegisterNolos_{PersonRegister_id}_inputPersonDecreedGroup" class="value link" dataid="{PersonDecreedGroup_id}"<?php } else { echo ' class="value"';} ?>><?php echo empty($PersonDecreedGroup_id) ? $empty_str : '{PersonDecreedGroup_Name}'; ?></span></div><div id="PersonRegisterNolos_{PersonRegister_id}_inputareaPersonDecreedGroup" class="input-area"></div></div>
					<div style="clear: both;"></div>
					<p>Должность: <strong>{Person_Post}</strong></p>
					<p><strong>Прикрепление.</strong> ЛПУ: <strong>{LpuAttach_Nick}</strong>, Участок: <strong>{LpuRegion_Name}</strong>, Дата прикрепления: <strong id="PersonCard_begDate_{PersonRegister_id}">{PersonCard_begDate}</strong></p>
					<div class="toolbar" id="PersonRegisterNolos_{PersonRegister_id}_toolbarAttach" style="display: none">
						<a id="PersonRegisterNolos_{PersonRegister_id}_editAttach" class="button icon text icon-hospital16" title="Открыть историю прикрепления"><span>Прикрепления</span></a>
						<a id="PersonRegisterNolos_{PersonRegister_id}_printMedCard" class="button disable icon text icon-print16" title="Печать медицинской карты"><span>Печать мед. карты</span></a>
					</div>
				</div>
			</div>
			<div class="right">
				<div id="PersonRegisterNolos_{PersonRegister_id}_toolbarPers" class="toolbar" style="display: none">
					<?php
					if ($is_allow_edit) {
						?><a id="PersonRegisterNolos_{PersonRegister_id}_editPers" class="button icon icon-edit16" title="Редактирование/Просмотр персональных данных пациента"><span></span></a><?php
					} else {
						?><a id="PersonRegisterNolos_{PersonRegister_id}_editPers" class="button icon icon-view16" title="Просмотр персональных данных пациента"><span></span></a><?php
					} 
					?>
					<a id="PersonRegisterNolos_{PersonRegister_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
				</div>
			</div>
		</div>
	</div>

    <div class="clear"><br /></div>

	<div class="specifics">
		<div class="data-row-container"><div class="data-row">Диагноз заболевания: <span<?php
				if ($is_allow_edit) {
					?> id="PersonRegisterNolos_{PersonRegister_id}_inputDiag" class="value link"<?php
				} else {
					echo ' class="value"';
				} ?>><?php echo empty($Diag_Name) ? $empty_str : '{Diag_Name}'; ?></span></div>
			<div id="PersonRegisterNolos_{PersonRegister_id}_inputareaDiag" class="input-area"></div>
		</div>
		<!-- Региональная льгота. Список -->
		{PersonPrivilegeRegAll}
		<!-- Федеральная льгота. Список -->
		{PersonPrivilegeFedAll}
		<!-- Лекарственные препараты. Список DrugOrphan-->
		{PersonDrug}
		<div class="data-row-container"><div class="data-row">Резистентность к проводимой терапии: <span<?php
				if ($is_allow_edit) {
					?> id="PersonRegisterNolos_{PersonRegister_id}_inputResist" class="value link"<?php
				} else {
					echo ' class="value"';
				} ?>><?php if(empty($PersonRegister_IsResist)){ echo 'Нет'; } else { echo 'Да'; } ?></span></div>
				<div id="PersonRegisterNolos_{PersonRegister_id}_inputareaResist" class="input-area"></div>
		</div>
		<div class="data-row-container"><div class="data-row">Дата включения в федеральный регистр: <span class="value"><?php echo empty($PersonRegister_setDate) ? $empty_str : '{PersonRegister_setDate}'; ?></span></div></div>
		<div class="data-row-container"><div class="data-row">Дата исключения из федерального регистра: <span class="value"><?php echo empty($PersonRegister_disDate) ? $empty_str : '{PersonRegister_disDate}'; ?></span></div></div>
		<div class="data-row-container"><div class="data-row">Причина исключения из регистра: <span class="value"><?php echo empty($PersonRegisterOutCause_Name) ? $empty_str : '{PersonRegisterOutCause_Name}'; ?></span></div></div>
		<div class="data-row-container"><div class="data-row"><span style="font-weight: bold">Номер регистровой записи:</span> <span<?php
				if ($is_allow_edit) {
					?> id="PersonRegisterNolos_{PersonRegister_id}_inputCode" class="value link"<?php
				} else {
					echo ' class="value"';
				} ?>><?php echo empty($PersonRegister_Code) ? $empty_str : '{PersonRegister_Code}'; ?></span></div>
			<div id="PersonRegisterNolos_{PersonRegister_id}_inputareaCode" class="input-area"></div>
		</div>
		<!-- Выгрузка в федеральный регистр. Список -->
		{PersonRegisterExport}
		<div class="clear"><br></div>
	</div>
		
</div>