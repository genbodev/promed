<div id="person_data_{Person_id}" class="person-info" 
<?php if (empty($PersonEncrypHIV_Encryp) && empty($isMseDepers)) { ?> onmouseover="if (isMouseLeaveOrEnter(event, this)){
	document.getElementById('person_data_{Person_id}_toolbar').style.opacity=1;
	if (document.getElementById('PersonCard_begDate').innerHTML == '') {
		document.getElementById('person_data_{Person_id}_printMedCard').style.display='none';
	}
}" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('person_data_{Person_id}_toolbar').style.opacity=0"
<?php } ?>>

    <div class="columns">

        <div class="left">
			<?php if (empty($PersonEncrypHIV_Encryp)) { ?>
            <div id="person_data_{Person_id}_editPhoto" class="photo" title="кликните для загрузки другой фотографии пациента">
                <img src="{PersonPhotoThumbName}" width="68" height="106" alt="фото" name="photo_person_{Person_id}"/>
            </div>
			<?php } else { ?>
            <div class="photo">
                <img src="{PersonPhotoThumbName}" width="68" height="106" alt="фото" name="photo_person_{Person_id}"/>
            </div>
			<?php } ?>

            <div class="data">
			<?php if (isset($isMseDepers) && $isMseDepers) { ?>
                <h1><strong>ИД пациента: {Person_id}</strong></h1>
                <p>Пол: <strong>{Sex_Name}</strong></p>
                <p>Соц. статус: <strong>{SocStatus_Name}</strong></p>
                <p>Работа: <strong>{Person_Job}</strong></p>
                <p>Должность: <strong>{Person_Post}</strong></p>
                <p><strong>Прикрепление.</strong> МО: <strong>{Lpu_Nick}</strong>, Участок: <strong>{LpuRegion_Name}</strong>, Дата прикрепления: <strong id="PersonCard_begDate">{PersonCard_begDate}</strong></p>
			<?php } elseif (!empty($PersonEncrypHIV_Encryp)) { ?>
				<h1><strong>{PersonEncrypHIV_Encryp}</strong></h1>
			<?php } else { ?>
                <h1><strong>{Person_Surname} {Person_Firname} {Person_Secname}</strong>, Д/р: <strong>{Person_Birthday}</strong></h1>
                <p>Пол: <strong>{Sex_Name}</strong></p>
                <p>Соц. статус: <strong>{SocStatus_Name}</strong>, <?php echo (getRegionNumber() == '101')? '' : ' СНИЛС: <strong>{Person_Snils}</strong>'; ?></p>
                <p>Регистрация: <strong>{Person_RAddress}</strong></p>
                <p>Проживает: <strong>{Person_PAddress}</strong></p>
                <p>
					<?php if (getRegionNick() == 'kz') {?>
						ИИН:
					<?php } else {?>
						ИНН:
					<?php }?>
					<strong> {Person_Inn}</strong></p>
                <p>Полис: <strong>{Polis_Ser} {Polis_Num}</strong>, Выдан: <strong>{Polis_begDate}</strong>, <strong>{OrgSmo_Name}</strong>, Закрыт: <strong>{Polis_endDate}</strong></p>
                <p>Документ: <strong>{Document_Ser} {Document_Num}</strong>, Выдан: <strong>{Document_begDate}, {OrgDep_Name}</strong></p>
                <p>Работа: <strong>{Person_Job}</strong></p>
                <p>Должность: <strong>{Person_Post}</strong></p>
                <p>
				<strong>Прикрепление.</strong> МО: <strong>{Lpu_Nick}</strong>, Участок: <strong>{LpuRegion_Name}</strong>, Дата прикрепления: <strong id="PersonCard_begDate">{PersonCard_begDate}</strong>
				</p>
                <p>Семейное положение: <strong>{FamilyStatus_Name}</strong></p>
				<?php if ($Person_Age <= 5) {?>
                <p>Способ вскармливания: <strong>{FeedingType_Name}</strong></p>
				<?php } ?>
				<?php if(getRegionNick()=='vologda') { ?>
					<p>Дистанционный мониторинг: 
					<?php if(empty($MonitorTemperatureStartDate)) { ?>
						<a id="person_data_{Person_id}_monitor_temperature" action='add' enable='{MonitorTemperatureLpuEnable}' href="#" onClick=" if(!this.getAttribute('enable')) { Ext.Msg.alert('Сообщение','Пациент не прикреплен к данной МО'); return;} if(Ext.get('person_data_{Person_id}_monitor_temperature').getAttribute('action')=='add') { getWnd('swRemoteMonitoringConsentWindow').show({ Person_id: {Person_id}, Label_id: '7', action: 'add', Person_Birthday: '{Person_Birthday}', DateFormat: 'd.m.Y', callback: function(data) { var el = Ext.get('person_data_{Person_id}_monitor_temperature'); el.dom.innerHTML=data.dateConsent.dateFormat('d.m.Y'); el.setAttribute('action','view'); } }); } else {getWnd('swRemoteMonitoringWindow').show({ Person_id: {Person_id}, Label_id: '7' });}">Добавить в дистанционный мониторинг температуры
					<?php } else { ?>
						<a href="#" enable='{MonitorTemperatureLpuEnable}' onClick="if(this.getAttribute('enable')) {getWnd('swRemoteMonitoringWindow').show({ Person_id: {Person_id}, Label_id: '7' });} else Ext.Msg.alert('Сообщение','Пациент не прикреплен к данной МО');">температура с {MonitorTemperatureStartDate}</a>
					<?php } ?>
					</a>
					</p>
				<?php } ?>
				<p>
				<div class="toolbar" id="person_data_{Person_id}_toolbar" style="opacity:0">
                    <a id="person_data_{Person_id}_editAttach" class="button icon text icon-hospital16" title="Открыть историю прикрепления"><span>Прикрепления</span></a>
                    <a id="person_data_{Person_id}_printMedCard" class="button disable icon text icon-print16" title="Печать медицинской карты"><span>Печать мед. карты</span></a>
                </div>
				</p>
			<?php } ?>
            </div>

        </div>

        <div class="right">

            <div id="{ParentSection}_{Person_id}_toolbar" class="toolbar" style="display: none">
			<?php if (empty($PersonEncrypHIV_Encryp)) { ?>
                <a id="person_data_{Person_id}_editPers" class="button icon icon-edit16" title="Редактировать персональные данные пациента"><span></span></a>
			<?php } ?>
                <a id="{ParentSection}_{Person_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
            </div>

        </div>

    </div>

    <!--
        <div class="columns">
            <div class="left">
                <div>
                    <p><strong>Прикрепление.</strong> ЛПУ: <strong>{Lpu_Nick}</strong>, Участок: <strong>{LpuRegion_Name}</strong>, Дата прикрепления: <strong>{PersonCard_begDate}</strong></p>
                </div>
            </div>

            <div class="right">
                <div class="toolbar">
                    <a id="person_data_{Person_id}_editAttach" class="button icon text icon-hospital16" title="Открыть историю прикрепления"><span>Прикрепления</span></a>
                    <a id="person_data_217957_addAttach" class="button icon text icon-hospital16" title=""><span></span></a>
                </div>
            </div>
        </div>
    -->

</div>
