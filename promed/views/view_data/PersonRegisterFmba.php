<?php
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = (1 == $accessType);
?>
<div id="PersonRegisterFmba_{PersonRegister_id}">
	<div class="frame signal-info">

		<div id="PersonRegisterFmba_{PersonRegister_id}" class="person-info">

		<div class="columns">
			<div class="left">
				<div id="PersonRegisterFmba_{PersonRegister_id}_editPhoto" class="photo" title="кликните для загрузки другой фотографии пациента">
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
					<p>Должность: <strong>{Person_Post}</strong></p>
					<p><strong>Прикрепление.</strong> ЛПУ: <strong>{LpuAttach_Nick}</strong>, Участок: <strong>{LpuRegion_Name}</strong>, Дата прикрепления: <strong id="PersonCard_begDate_{PersonRegister_id}">{PersonCard_begDate}</strong></p>
				</div>
			</div>
		</div>
	</div>

    <div class="clear"><br /></div>

	<div class="specifics">
		<div class="data-row-container"><div class="data-row">Диагноз заболевания: <span<?php
				if ($is_allow_edit) {
					?> id="PersonRegisterFmba_{PersonRegister_id}_inputDiag" class="value link"<?php
				} else {
					echo ' class="value"';
				} ?>><?php echo empty($Diag_Name) ? $empty_str : '{Diag_Name}'; ?></span></div>
			<div id="PersonRegisterFmba_{PersonRegister_id}_inputareaDiag" class="input-area"></div>
		</div>
		<!-- Региональная льгота. Список -->
		{PersonPrivilegeRegAll}
		<!-- Федеральная льгота. Список -->
		{PersonPrivilegeFedAll}
		<!-- Лекарственные препараты. Список DrugOrphan-->
		{PersonDrug}
		<div class="data-row-container"><div class="data-row">Дата включения в федеральный регистр: <span class="value"><?php echo empty($PersonRegister_setDate) ? $empty_str : '{PersonRegister_setDate}'; ?></span></div></div>
		<div class="data-row-container"><div class="data-row">Дата исключения из федерального регистра: <span class="value"><?php echo empty($PersonRegister_disDate) ? $empty_str : '{PersonRegister_disDate}'; ?></span></div></div>
		<div class="data-row-container"><div class="data-row">Причина исключения из регистра: <span class="value"><?php echo empty($PersonRegisterOutCause_Name) ? $empty_str : '{PersonRegisterOutCause_Name}'; ?></span></div></div>
		<div class="clear"><br></div>
	</div>
		
</div>