<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);

	$diag_code_full = !empty($Diag_Code)?substr($Diag_Code, 0, 3):'';

	$showRankinScaleEsDiv = (
		(in_array(getRegionNumber(), array(1, 2, 59, 66, 91)) && $DiagFinance_IsRankin == 2 && $EvnSection_IsPriem != 2) ||
		(in_array(getRegionNumber(), array(58)) && $LpuSectionProfile_Code == 158 && $EvnSection_IsPriem != 2)
	);
	$labelRankinScaleEsDiv = in_array(getRegionNumber(), array(58))?'Значение по шкале Рэнкина':'Значение по шкале Рэнкина при поступлении';
?>
<div id="EvnSection_data_{EvnSection_id}" class="columns">
    <div class="left">
        <div id="EvnSection_data_{EvnSection_id}_content">
			<h2>Движение пациента</h2>
			<div class="text">
				<p>{LpuSection_Name} - {EvnSection_setDate} {EvnSection_setTime}<?php echo ((empty($EvnSection_disDate))?'':' - {EvnSection_disDate} {EvnSection_disTime}'); ?> Вид оплаты: {PayType_Name}<?php echo ((empty($TariffClass_Name))?'':', Вид тарифа: {TariffClass_Name}'); ?></p>
				<div class="columns" onmouseover="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnSection_data_{EvnSection_id}_editMedPersonal').style.display='block'; document.getElementById('EvnSection_data_{EvnSection_id}_viewMedPersonal').style.display='block';} " onmouseout="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnSection_data_{EvnSection_id}_editMedPersonal').style.display='none'; document.getElementById('EvnSection_data_{EvnSection_id}_viewMedPersonal').style.display='none';}">
                    <?php if(getRegionNumber()==60){echo'<div class="left"><p>Вид транспортировки: {LpuSectionTransType_id}</p></div>';}?>
						<div class="left"><p>Врач: {MedPersonal_Fio}<?php  
						if(empty($MedPersonal_Fio))
						{
							$title = 'Назначить лечащего врача';
							$ico = 'icon-add16';
						}
						else
						{
							$title = 'Изменить лечащего врача';
							$ico = 'icon-edit16';
						}
					?></p></div>
					<div class="toolbar right">
						<?php if ( getRegionNick() == 'vologda' ) { ?>
							<a id="EvnSection_data_{EvnSection_id}_viewMedPersonal" class="button icon icon-view16" title="Просмотр" style="display: none"><span></span></a>
						<?php } ?>
						<a id="EvnSection_data_{EvnSection_id}_editMedPersonal" class="button icon <?php echo $ico;?>" title="<?php echo $title;?>" style="display: none"><span></span></a>
						
					</div>
				</div>
	            <?php
	            if (in_array(getRegionNumber(), array(66)))
	            {
					// Если в поле “Вид оплаты” выбрано “Местный бюджет” или указан метод ВМП, то поле КСГ делать не доступным
					$is_allow_edit_ksg = ($is_allow_edit && !empty($PayType_id) && $PayType_id != 112 && empty($HTMedicalCareClass_id));
		            $fieldHtml = "<div style='clear:both;'>
		                <div style='float:left;padding:5px 0;'>КСГ: ";
		            if ($is_allow_edit_ksg) {
			            $fieldHtml .= "<span id='EvnSection_data_{EvnSection_id}_inputKsg' style='color:#000;' class='link' dataid='{Mes_sid}'>";
		            }
		            if (empty($UslugaComplex_Name)) {
			            $fieldHtml .= $empty_str;
		            } else {
			            $fieldHtml .= '{UslugaComplex_Code}. {UslugaComplex_Name}';
		            }
		            if ($is_allow_edit_ksg) {
			            $fieldHtml .= "</span>";
		            }
		            $fieldHtml .= '</div>
		                <div id="EvnSection_data_{EvnSection_id}_inputareaKsg" class="input-area" style="float:left; margin-left:5px; display: none"></div>
		            </div>';
					$fieldHtml .= "<div style='clear:both;'></div>";
		            echo $fieldHtml;
					
					$fieldHtml = "<div style='clear:both;'>
		                <div style='float:left;padding:5px 0;'>Профиль: ";
		            if ($is_allow_edit) {
			            $fieldHtml .= "<span id='EvnSection_data_{EvnSection_id}_inputLpuSectionProfile' style='color:#000;' class='link' dataid='{es_LpuSectionProfile_id}'>";
		            }
		            if (empty($LpuSectionProfile_Name)) {
			            $fieldHtml .= $empty_str;
		            } else {
			            $fieldHtml .= '{LpuSectionProfile_Code}. {LpuSectionProfile_Name}';
		            }
		            if ($is_allow_edit) {
			            $fieldHtml .= "</span>";
		            }
		            $fieldHtml .= '</div>
		                <div id="EvnSection_data_{EvnSection_id}_inputareaLpuSectionProfile" class="input-area" style="float:left; margin-left:5px; display: none"></div>
		            </div>';
					$fieldHtml .= "<div style='clear:both;'></div>";
		            echo $fieldHtml;
	            }
				if (in_array(getRegionNumber(), array(30,101))) {
					$fieldHtml ='';
					if (FALSE === \stripos($LowLpuSection_Name, 'приемное отделение')) {
						$fieldHtml = "<div style='clear:both;'>
		                <div style='float:left;padding:5px 0;'>Профиль: ";
					if ($is_allow_edit) {
						$fieldHtml .= "<span id='EvnSection_data_{EvnSection_id}_inputLpuSectionProfile' style='color:#000;' class='link' dataid='{es_LpuSectionProfile_id}'>";
					}
					if (empty($LpuSectionProfile_Name)) {
						$fieldHtml .= $empty_str;
					} else {
						$fieldHtml .= '{LpuSectionProfile_Code}. {LpuSectionProfile_Name}';
					}
					if ($is_allow_edit) {
						$fieldHtml .= "</span>";
					}
					$fieldHtml .= '</div>
		                <div id="EvnSection_data_{EvnSection_id}_inputareaLpuSectionProfile" class="input-area" style="float:left; margin-left:5px; display: none"></div>
		            </div>';
					$fieldHtml .= "<div style='clear:both;'></div>";
					echo $fieldHtml;
					}
					
				}
	            if (false == empty($EvnSection_IsPriem) && $EvnSection_IsPriem != 2 && in_array(getRegionNumber(), array(3))
					|| ($EvnSection_IsPriem != 2 && getRegionNumber() == 60 && (empty($LpuUnitType_SysNick) || $LpuUnitType_SysNick != 'stac'))
				)
	            {
		            $fieldUslugaComplex = "<div style='clear:both;'><div style='float:left;padding:5px 0;'>" . (getRegionNumber() == 3 ? 'Профильная услуга' : 'Услуга лечения') . ": ";
		            if ($is_allow_edit && (getRegionNumber() != 60 || empty($HTMedicalCareClass_id))) {
			            $fieldUslugaComplex .= "<span id='EvnSection_data_{EvnSection_id}_inputUslugaComplex' style='color:#000;' class='link' dataid='{UslugaComplex_id}'>";
		            }
		            if (empty($UslugaComplex_Name)) {
			            $fieldUslugaComplex .= $empty_str;
		            } else {
			            $fieldUslugaComplex .= '{UslugaComplex_Code}. {UslugaComplex_Name}';
		            }
		            if ($is_allow_edit && (getRegionNumber() != 60 || empty($HTMedicalCareClass_id))) {
			            $fieldUslugaComplex .= "</span>";
		            }
		            $fieldUslugaComplex .= '</div><div id="EvnSection_data_{EvnSection_id}_inputareaUslugaComplex" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>';
		            echo $fieldUslugaComplex;
	            }
	            ?>
				<?php if ( false == empty($EvnSection_IsPriem) && $EvnSection_IsPriem != 2 && (getRegionNumber() == 63) ) { ?>
				<div class="columns" onmouseover="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnSection_data_{EvnSection_id}_editLpuSectionBedProfile').style.display='block'; } " onmouseout="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnSection_data_{EvnSection_id}_editLpuSectionBedProfile').style.display='none'; }">
					<div class="left"><p>Профиль коек: {LpuSectionBedProfile_Name}</p></div>
					<div class="toolbar right">
						<a id="EvnSection_data_{EvnSection_id}_editLpuSectionBedProfile" style="display: none"<?php  
						if(empty($LpuSectionBedProfile_Name))
						{
							?> class="button icon icon-add16" title="Указать профиль коек"<?php
						}
						else
						{
							?> class="button icon icon-edit16" title="Изменить профиль коек"<?php
						}
					?>><span></span></a>
					</div>
				</div>
				<?php } ?>
				<?php if ( getRegionNick() != 'kz' ) { ?>
					<?php if ( getRegionNick() == 'vologda' ) { ?>
						<div class="LpuSectionWardHistoryTable" style="display:block;width:100%;">
							<div id="LpuSectionWardHistory_{EvnSection_id}" style="display: none;">
								<table class="LpuSectionWardHistoryTable_{EvnSection_id}"></table>
							</div>
						</div>
					<?php } ?>
				<div class="columns" onmouseover="
					if (isMouseLeaveOrEnter(event, this)) { 
						document.getElementById('EvnSection_data_{EvnSection_id}_editLpuSectionWard').style.display='block'; 
						document.getElementById('EvnSection_data_{EvnSection_id}_openLpuSectionWardHistory').style.display='block';
					} " onmouseout="if (isMouseLeaveOrEnter(event, this)) { 
						document.getElementById('EvnSection_data_{EvnSection_id}_editLpuSectionWard').style.display='none'; 
						document.getElementById('EvnSection_data_{EvnSection_id}_openLpuSectionWardHistory').style.display='none';
					}">
					<div class="left"><p>Палата: {LpuSectionWard_Name}<?php  
						if(empty($LpuSectionWard_Name))
						{
							$title = 'Указать палату';
							$ico = 'icon-add16';
						}
						else
						{
							$title = 'Изменить палату';
							$ico = 'icon-edit16';
						}
					?></p></div>
					<div class="toolbar right">
						<a id="EvnSection_data_{EvnSection_id}_editLpuSectionWard" class="button icon <?php echo $ico;?>" title="<?php echo $title;?>" style="display: none"><span></span></a>
						<?php if ( getRegionNick() == 'vologda' ) { ?>
						<a id="EvnSection_data_{EvnSection_id}_openLpuSectionWardHistory" class="button icon icon-view16" title="История изменений" style="display: none"><span></span></a>
						<?php } ?>
					</div>
				</div>
				<?php } else { ?>
				<div class="columns">
					<?php if($EvnSection_IsPriem == 2) { ?>
					<div class="left"><p>Профиль койки: {BedProfileRuFull}</p></div>
					<?php } else { ?>
					<div class="left"><p>Палата: {GetRoom_Name}</p></div>
					<div class="left"><p>Койка: {GetBed_Name}</p></div>
					<?php }  ?>
				</div>
				<?php } ?>
				<div class="columns">
					<div class="left"><p>Внутр. № карты: {EvnSection_insideNumCard}</p></div>
				</div>
				<div class="columns" onmouseover="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnSectionLeave_{EvnSection_id}_toolbar').style.display='block'; } " onmouseout="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnSectionLeave_{EvnSection_id}_toolbar').style.display='none'; }">
					<div class="left">
					<p><strong>Исход <?php echo (in_array($LeaveType_Code, array(-1, -2, -3, -4)) && getRegionNumber() == 60 ? 'пребывания' : 'госпитализации'); ?>:</strong>
					<?php
					$evnsectionleave_btns = '<a id="EvnSection_data_{EvnSection_id}_editLeave" class="button icon icon-edit16" title="Редактировать исход"'.(($IsSigned == 2)?' style="display:none"':'').'><span></span></a>
								<a id="EvnSection_data_{EvnSection_id}_delLeave" class="button icon icon-delete16" title="Удалить исход"'.(($IsSigned == 2)?' style="display:none"':'').'><span></span></a>';
					if (empty($_REQUEST['archiveRecord'])) {
						if ($IsSigned == 2) {
							$evnsectionleave_btns .= '<a id="EvnSection_data_{EvnSection_id}_signLeave" class="button icon icon-signature16" title="Отменить подпись"><span></span></a>';
						} else {
							$evnsectionleave_btns .= '<a id="EvnSection_data_{EvnSection_id}_signLeave" class="button icon icon-signature16" title="Подписать исход"><span></span></a>';
						}
					}
					$fed_result_fields = '';
					if (!empty($isAllowFedResultFields) && !empty($EvnSection_setDateYmd) && $EvnSection_setDateYmd >= '2015-01-01') {
						if (in_array(getRegionNumber(), array(59)))
						{
							$fieldHtml = "<div style='clear:both;'>
								<div style='float:left;padding:5px 0;'>Фед. результат: ";
							if ($is_allow_edit) {
								$fieldHtml .= "<span id='EvnSection_data_{EvnSection_id}_inputFedLeaveType' style='color:#000;' class='link' dataid='{LeaveType_fedid}'>";
							}
							if (empty($FedLeaveType_Name)) {
								$fieldHtml .= $empty_str;
							} else {
								$fieldHtml .= '{FedLeaveType_Code}. {FedLeaveType_Name}';
							}
							if ($is_allow_edit) {
								$fieldHtml .= "</span>";
							}
							$fieldHtml .= '</div>
								<div id="EvnSection_data_{EvnSection_id}_inputareaFedLeaveType" class="input-area" style="float:left; margin-left:5px; display: none"></div>
							</div>';
							$fieldHtml .= "<div style='clear:both;'></div>";
							//echo $fieldHtml;
							/*-----------------------*/
							$fieldHtml .= "<div style='clear:both;'>
								<div style='float:left;padding:5px 0;'>Фед. исход: ";
							if ($is_allow_edit) {
								$fieldHtml .= "<span id='EvnSection_data_{EvnSection_id}_inputFedResultDeseaseType' style='color:#000;' class='link' dataid='{ResultDeseaseType_fedid}'>";
							}
							if (empty($FedResultDeseaseType_Name)) {
								$fieldHtml .= $empty_str;
							} else {
								$fieldHtml .= '{FedResultDeseaseType_Code}. {FedResultDeseaseType_Name}';
							}
							if ($is_allow_edit) {
								$fieldHtml .= "</span>";
							}
							$fieldHtml .= '</div>
								<div id="EvnSection_data_{EvnSection_id}_inputareaFedResultDeseaseType" class="input-area" style="float:left; margin-left:5px; display: none"></div>
							</div>';
							$fieldHtml .= "<div style='clear:both;'></div>";
							//echo $fieldHtml;
						}
						$fed_result_fields = $fieldHtml;
						/*$fed_result_fields = '<p>Фед. результат: {FedLeaveType_Code}&nbsp;{FedLeaveType_Name}</p>
						<p>Фед. исход: {FedResultDeseaseType_Code}&nbsp;{FedResultDeseaseType_Name}</p>';*/
					}
					switch($LeaveType_Code)
					{
						case -3://Нет ни отказа, ни госпитализации в отделение
							$evnsectionleave_btns = '<a id="EvnSection_data_{EvnSection_id}_editLeavePriem" class="button icon icon-add16" title="Указать исход из приёмного отделения"><span></span></a>';
						?>
							</p></div>
						<?php
						break;
						case -4://603 == $PrmLeaveType_Code
							//Бурятия: Осмотрен в приемном отделении 
							//Псков: Неотложная помощь в приемном отделении
							if ( getRegionNumber() == 3 ) { $evnsectionleave_text = 'Осмотрен в приемном отделении'; }
							else if ( getRegionNumber() == 60 ) { $evnsectionleave_text = 'Неотложная помощь в приемном отделении'; }
							else { $evnsectionleave_text = '&nbsp;'; }
							$evnsectionleave_btns = '<a id="EvnSection_data_{EvnSection_id}_editLeavePriem" class="button icon icon-edit16" title="Редактировать исход из приёмного отделения"><span></span></a>';
						?>
							{EvnSection_leaveDate} {EvnSection_leaveTime} <strong><?php echo $evnsectionleave_text; ?></strong></p>
						<?php
							if ( getRegionNumber() == 60 ) {
								if ( !empty($PrehospWaifRefuseCause_Name) ) {
									echo '<p>Причина отказа: {PrehospWaifRefuseCause_Name}</p>';
								}

								echo '<p>Результат обращения: {ResultClass_Name}</p>';
								echo '<p>Исход: {ResultDeseaseType_Name}</p>';
							}
							if ( getRegionNumber() != 60 ) {
						?>
							<p>Код посещения: {UslugaComplex_Code}. {UslugaComplex_Name}</p>
						<?php
							}
						?>
							<?php echo $fed_result_fields; ?>
							</div>
						<?php
						break;
						case -2://Госпитализация в отделение
							$evnsectionleave_btns = '<a id="EvnSection_data_{EvnSection_id}_editLeavePriem" class="button icon icon-edit16" title="Редактировать исход из приёмного отделения"><span></span></a>';
						?>
							{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Госпитализация в отделение: {LpuSection_o_Name}</strong></p></div>
						<?php
						break;
						case -1://Отказ 
							if (empty($PrehospWaifRefuseCause_Name))
							{
								echo '</p></div>';
								$evnsectionleave_btns = '';
							}
							else
							{
								echo '<strong>Отказ</strong></p><p>Причина отказа: {PrehospWaifRefuseCause_Name}</p>';
								echo $fed_result_fields;
								echo '</div>';
								$evnsectionleave_btns = '<a id="EvnSection_data_{EvnSection_id}_editLeavePriem" class="button icon icon-edit16" title="Редактировать исход из приёмного отделения"><span></span></a>';
							}
						break;
						// https://redmine.swan.perm.ru/issues/30661
						// 107. Лечение прервано по инициативе пациента
						// 108. Лечение прервано по инициативе ЛПУ
						// 110. Самовольно прерванное лечение
						case 107:
						case 108:
						case 110:
						case 207:
						case 208:
						?>
							{EvnSection_leaveDate} {EvnSection_leaveTime} <strong><?php echo $LeaveType_Name; ?></strong><?php echo ((empty($EvnLeave_UKL))?'':' УКЛ {EvnLeave_UKL}'); ?></p>
							<?php echo ((empty($ResultDesease_Name))?'':'<p>Исход госпитализации: {ResultDesease_Name}</p>'); ?>
							<!--p>Отделение: {LpuSection_o_Name}</p-->
							<?php echo $fed_result_fields; ?>
							</div>
						<?php
						break;
						default:
							switch ($Leave_EvnClass_SysNick) {
								// Выписка
								case 'EvnLeave':
								?>
									{EvnSection_leaveDate} {EvnSection_leaveTime} <strong><?php echo $LeaveType_Name; ?></strong> УКЛ {EvnLeave_UKL} <?php if ( getRegionNumber() != 2 ) { echo (empty($EvnSection_KSG)?'':'КСГ {EvnSection_KSG}'), (empty($EvnSection_KPG)?'':'КПГ {EvnSection_KPG}'); } ?></p>
									<p>Причина выписки: {LeaveCause_Name}		Исход заболевания: {ResultDesease_Name}</p>
									<p>Направлен на амб.лечение <?php echo ((empty($EvnLeave_IsAmbul))?'':'{EvnLeave_IsAmbul}'); ?></p>
									<?php echo $fed_result_fields; ?>
									</div>
								<?php
								break;
								// Перевод в другое ЛПУ
								case 'EvnOtherLpu':
								?>
									{EvnSection_leaveDate} {EvnSection_leaveTime} <strong><?php echo $LeaveType_Name; ?>: {Lpu_l_Name}</strong> УКЛ {EvnLeave_UKL}</p>
									<p>Причина перевода: {LeaveCause_Name} 	Исход госпитализации: {ResultDesease_Name}</p>
									<?php echo $fed_result_fields; ?>
									</div>
								<?php
								break;
								// Смерть
								case 'EvnDie':
								?>
									{EvnSection_leaveDate} {EvnSection_leaveTime} <strong><?php echo $LeaveType_Name; ?></strong> УКЛ {EvnLeave_UKL}</p>
									<?php echo ((empty($ResultDesease_Name))?'':'<p>Результат госпитализации: {ResultDesease_Name}</p>'); ?>
									<p>Врач, установивший смерть: {MedPersonal_d_Fin}</p>
									<p>Необходимость экспертизы: <?php echo ((empty($EvnDie_IsAnatom))?'':'{EvnDie_IsAnatom}'); ?></p>
									<p><strong><i>Патологоанатомическая экспертиза:</i></strong></p>
									<p>{EvnDie_expDate} {EvnDie_expTime}  Место проведения:  {EvnDie_locName}</p>
									<p>Врач:{MedPersonal_a_Fin}</p>
									<p>Основной патологоанатомический диагноз: {Diag_a_Code}.{Diag_a_Name}</p>
									<?php echo $fed_result_fields; ?>
									</div>
								<?php
								break;
								// Перевод в стационар другого типа
								case 'EvnOtherStac':
								?>
									{EvnSection_leaveDate} {EvnSection_leaveTime} <strong><?php echo $LeaveType_Name; ?></strong> УКЛ {EvnLeave_UKL}</p>
									<p>Причина перевода: {LeaveCause_Name} 	Исход госпитализации: {ResultDesease_Name}</p>
									<p>Тип стационара: {LpuUnitType_o_Name}	Отделение: {LpuSection_o_Name}</p>
									<?php echo $fed_result_fields; ?>
									</div>
								<?php
								break;
								//Перевод в другое отделение
								case 'EvnOtherSection':
								?>
									{EvnSection_leaveDate} {EvnSection_leaveTime} <strong><?php echo $LeaveType_Name; ?>: {LpuSection_o_Name}</strong><?php echo ((empty($EvnLeave_UKL))?'':' УКЛ {EvnLeave_UKL}'); ?></p>
									<?php echo ((empty($LeaveCause_Name))?'':'<p>Причина перевода: {LeaveCause_Name} 	Исход госпитализации: {ResultDesease_Name}</p>'); ?>
									<!--p>Отделение: {LpuSection_o_Name}</p-->
									<?php echo $fed_result_fields; ?>
									</div>
								<?php
								break;
								// Перевод на другой профиль коек
								case 'EvnOtherSectionBedProfile':
								?>
									{EvnSection_leaveDate} {EvnSection_leaveTime} <strong><?php echo $LeaveType_Name; ?>: {LpuSection_o_Name}</strong><?php echo ((empty($EvnLeave_UKL))?'':' УКЛ {EvnLeave_UKL}'); ?></p>
									<?php echo ((empty($LeaveCause_Name))?'':'<p>Причина перевода: {LeaveCause_Name} 	Исход госпитализации: {ResultDesease_Name}</p>'); ?>
									<!--p>Отделение: {LpuSection_o_Name}</p-->
									<?php echo $fed_result_fields; ?>
									</div>
								<?php
								break;
								default:
									$evnsectionleave_btns = '<a id="EvnSection_data_{EvnSection_id}_addLeave" class="button icon icon-add16" title="Указать исход"><span></span></a>';
								?>
									</p></div>
								<?php
								break;
							}
						break;
					}
					?>
					<div id="EvnSectionLeave_{EvnSection_id}_toolbar" class="toolbar right" style="display: none">
						<?php echo $evnsectionleave_btns; ?>
					</div>
				</div>
				<div class="clear"></div>

				<div class="columns" onmouseover="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnSection_{EvnSection_id}_addEvnInfectNotify').style.display='block'; } " onmouseout="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnSection_{EvnSection_id}_addEvnInfectNotify').style.display='none'; }">
					<div style="width:99%;" class="left">
						<p><strong>Основной диагноз:</strong> <!--span id="EvnSection_{EvnSection_id}_showDiagList" class="link" title="Показать список уточненных диагнозов">Диагноз</span-->
						<?php
							switch (true) {
								case (!empty($DiagFedMes_FileName) && file_exists($_SERVER['DOCUMENT_ROOT'].'/promed/views/federal_mes/'.$DiagFedMes_FileName.'.htm')):
								case (!empty($CureStandart_Count) && getRegionNumber() != 63): // кроме Самары
									echo '<span id="EvnSection_{EvnSection_id}_showFm" class="link" title="' . (getRegionNick() == 'kz' ? 'Показать протокол лечения по этому диагнозу' : 'Показать федеральный '.getMESAlias().' по этому диагнозу') . '">{Diag_Code}</span>';
									break;
								default:
									echo '{Diag_Code}';
									break;
							}
						?>
						{Diag_Name}</p>
						<?php
						if (getRegionNick() == 'kareliya' && (($diag_code_full >= 'C00' && $diag_code_full <= 'C97') || ($diag_code_full >= 'D00' && $diag_code_full <= 'D09'))) { ?>
						<p><strong>Характер:</strong> {DeseaseType_Name}</p>
						<?php } ?>
						<?php if (
							(in_array(getRegionNick(), array('kareliya','ekb')) && (($diag_code_full >= 'C00' && $diag_code_full <= 'C97') || ($diag_code_full >= 'D00' && $diag_code_full <= 'D09'))) ||
							(in_array(getRegionNick(), array('penza')) && ($diag_code_full >= 'C00' && $diag_code_full <= 'C97'))
						) { ?>
						<p><strong>Стадия выявленного ЗНО:</strong> {TumorStage_Name}</p>
						<?php } ?>
						<?php if (in_array(getRegionNick(), array('penza')) && ($diag_code_full >= 'C00' && $diag_code_full <= 'C97')) { ?>
						<p><strong>Интенсивность боли:</strong> {PainIntensity_Name}</p>
						<?php } ?>
					</div>

                    <div style='clear:both;display: <?php echo $showRS?"block":"none"; ?>;'>
                        Оценка состояния по ШРМ: <?php if($is_allow_edit) { ?>
                        <span id="EvnSection_data_{EvnSection_id}_inputRehabScale" style='color:#000;' class="link" dataid='{RehabScale_id}'>
                                <?php } echo empty($RehabScale_id)?$empty_str:'{RehabScale_Name}';
								if($is_allow_edit) { ?></span><?php } ?>
                        <div id="EvnSection_data_{EvnSection_id}_inputareaRehabScale" class="input-area" style="display: none"></div>
                    </div>

                    <?php if($showRSOut): ?>
                    <div style='clear:both;'>
                        Оценка состояния по ШРМ при выписке: <?php if($is_allow_edit) { ?>
                        <span id="EvnSection_data_{EvnSection_id}_inputRehabScaleVid" style='color:#000;' class="link" dataid='{RehabScale_vid}'>
                                <?php } echo empty($RehabScale_vid)?$empty_str:'{RehabScaleOut_Name}';
								if($is_allow_edit) { ?></span><?php } ?>
                        <div id="EvnSection_data_{EvnSection_id}_inputareaRehabScaleVid" class="input-area" style="display: none"></div>
                    </div>
                    <?php endif;?>

                    <div style='clear:both;'><div id="EvnSection_data_{EvnSection_id}_inputRankinScaleEsDiv" style='display:<?php echo $showRankinScaleEsDiv?"block":"none"; ?>;float:left;padding:5px 0px;'><?php echo $labelRankinScaleEsDiv; ?>: <?php if($is_allow_edit) { ?><span id="EvnSection_data_{EvnSection_id}_inputRankinScaleEs" style='color:#000;' class="link" dataid='{RankinScale_id}'><?php } echo empty($RankinScale_Name)?$empty_str:'{RankinScale_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnSection_data_{EvnSection_id}_inputareaRankinScaleEs" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div style='clear:both;'><div id="EvnSection_data_{EvnSection_id}_inputEsInsultScaleDiv" style='display:<?php if (in_array(getRegionNumber(), array(2, 59, 91)) && $DiagFinance_IsRankin == 2 && $EvnSection_IsPriem != 2) { echo "block"; } else { echo "none"; } ?>;float:left;padding:5px 0px;'>Значение шкалы инсульта Национального института здоровья: <?php if($is_allow_edit) { ?><span id="EvnSection_data_{EvnSection_id}_inputEsInsultScale" style='color:#000;' class="link" dataid='{EvnSection_InsultScale}'><?php } echo !is_null($EvnSection_InsultScale)?'{EvnSection_InsultScale}':$empty_str; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnSection_data_{EvnSection_id}_inputareaEsInsultScale" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div style='clear:both;'><div id="EvnSection_data_{EvnSection_id}_inputRankinScaleEsSidDiv" style='display:<?php if (in_array(getRegionNumber(), array(1, 2, 59, 66, 91)) && $DiagFinance_IsRankin == 2 && $EvnSection_IsPriem != 2 && !empty($EvnSection_disDate)) { echo "block"; } else { echo "none"; } ?>;float:left;padding:5px 0px;'>Значение по шкале Рэнкина при выписке: <?php if($is_allow_edit) { ?><span id="EvnSection_data_{EvnSection_id}_inputRankinScaleEsSid" style='color:#000;' class="link" dataid='{RankinScale_sid}'><?php } echo empty($RankinScale_sName)?$empty_str:'{RankinScale_sName}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnSection_data_{EvnSection_id}_inputareaRankinScaleEsSid" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div id="EvnSection_{EvnSection_id}_addEvnInfectNotifyTools" class="toolbar right" style="width:1%;display: <?php if (empty($isDisabledAddEvnInfectNotify)) { ?>block<?php } else { ?>none<?php } ?>;">
						<a id="EvnSection_{EvnSection_id}_addEvnInfectNotify" class="button icon icon-add16" title="Добавить экстренное извещение об инфекционном заболевании, отравлении"><span></span></a>
					</div>
					<div id="EvnSection_{EvnSection_id}_addEvnNotifyVenerTools" class="toolbar right" style="width:1%;display: <?php if (empty($isDisabledAddEvnNotifyVener)) { ?>block<?php } else { ?>none<?php } ?>;">
						<a id="EvnSection_{EvnSection_id}_addEvnNotifyVener" class="button icon icon-add16" title="Создать Извещение о больном венерическим заболеванием"><span></span></a>
					</div>
					<div id="EvnSection_{EvnSection_id}_addEvnNotifyRegisterIncludeNolosTools" class="toolbar right" style="width: 1%;display: none;">
						<a id="EvnSection_{EvnSection_id}_addEvnNotifyRegisterIncludeNolos" class="button icon icon-add16" title="Создать Направление на включение в регистр по ВЗН"><span></span></a>
					</div>
					<div id="EvnSection_{EvnSection_id}_addEvnNotifyRegisterIncludeOrphanTools" class="toolbar right" style="width: 1%;display: none;">
						<a id="EvnSection_{EvnSection_id}_addEvnNotifyRegisterIncludeOrphan" class="button icon icon-add16" title="Создать Направление на включение в регистр по орфанным заболеваниям"><span></span></a>
					</div>
				</div>
				<?php
					if( in_array(getRegionNumber(), array(30)) && !empty($CureResult_id) ) {
						// поле "Законченный случай"
						$fieldHtml = "<div style='clear:both;'>
		                	<div style='float:left;padding:5px 0;'>Итог лечения: ";
						if ($is_allow_edit) {
							$fieldHtml .= "<span id='EvnSection_data_{EvnSection_id}_inputIsFinish' style='color:#000;' class='link' dataid='{CureResult_id}'>";
						}
						if (empty($CureResult_id)) {
							$fieldHtml .= $empty_str;
						} else {
							$fieldHtml .= '{CureResult_Name}';
						}
						if ($is_allow_edit) {
							$fieldHtml .= "</span>";
						}
						$fieldHtml .= '</div>
							<div id="EvnSection_data_{EvnSection_id}_inputareaIsFinish" class="input-area" style="float:left; margin-left:5px; display: none"></div>
						</div>';
						$fieldHtml .= "<div style='clear:both;'></div>";
						echo $fieldHtml;
					}
				?>
				{EvnDiagPS}
				<?php if ($Sex_SysNick == 'woman') { // Отображаем карты наблюдения за кровотечением только для женщин?>
					{BleedingCard}
				<?php } ?>
				<?php
				echo !empty($DrugTherapyScheme_Code)?'<p>Схема лекарственной терапии: {DrugTherapyScheme_Code}. {DrugTherapyScheme_Name}</p>':'';
				echo !empty($EvnSection_SofaScalePoints)?'<p>Оценка по шкале органной недостаточности (SOFA, pSOFA): {EvnSection_SofaScalePoints}</p>':'';
				echo !empty($EvnSection_BarthelIdx)?'<p>Индекс Бартел: {EvnSection_BarthelIdx}</p>':'';

				if(75 == $LpuSectionProfile_id)
				{
					echo ((empty($EvnPS_HospCount))?'':'<p>Количество госпитализаций: {EvnPS_HospCount}</p>');
					echo ((empty($EvnPS_TimeDesease))?'':'<p>Время с начала заболевания: {EvnPS_TimeDesease}</p>');
					echo ((empty($EvnPS_IsNeglectedCase))?'':'<p>Случай запущен: {EvnPS_IsNeglectedCase}</p>');
					echo ((empty($PrehospToxic_Name))?'':'<p>Состояние опьянения: {PrehospToxic_Name}</p>');
					echo ((empty($PrehospTrauma_Name))?'':'<p>Вид травмы (внешнего воздействия) : {PrehospTrauma_Name}');
					echo ((empty($EvnPS_IsUnlaw))?'</p>':' Противоправная: {EvnPS_IsUnlaw}</p>');
					echo ((empty($EvnPS_IsUnport))?'':'<p>Нетранспортабельность: {EvnPS_IsUnport}</p>');
				}
				else if( !in_array(getRegionNumber(), array(1, 3, 63)) ) // отображаем в движении для всех кроме Адыгеи, Самары и Бурятии
				{
                    //echo $Duration;
                    if(isset($Duration)&&$Duration>0){
                        $Interval = ((int)$Duration-(int)$EvnSection_KoikoDni);//*-1;
                        //if($Interval>0)$Interval='+'.$Interval;
                        echo"<p>Срок госпитализации: ".$Interval."</p>";
                    }
					if (empty($Mes_Name)) {
						echo '<p>'.getMESAlias().': не выбран</p>';
					} else {
						echo '<p>'.getMESAlias().': <u>{Mes_Code}.{Mes_Name}</u></p>
						<p>Норматив койкодней: {Mes_KoikoDni}, фактические койкодни: {EvnSection_KoikoDni},	выполнение '.getMESAlias().': {Procent_KoikoDni}%'.((getRegionNumber()==60)?'</p>':'</p>').'
						<!--table style="text-align: left; font-family: tahoma,arial,helvetica,sans-serif; font-size: 13px;" cellspacing="0" height="30" width="400"><tbody>
						<tr valign="bottom"><td rowspan="2" valign="bottom" width="80">Выполнение:&nbsp;</td><td style="font-size: 8pt;" width="55"><div style="float: left; margin-left: -1px;">0%<br>|</div></td><td style="font-size: 8pt;" width="55"><div style="float: left;">25%<br>|</div></td><td style="font-size: 8pt;" width="55"><div style="float: left;">50%<br>|</div></td><td style="font-size: 8pt;" width="55"><div style="float: left;">75%<br>|</div></td><td rowspan="2" style="font-size: 8pt;" valign="top" width="30"><div style="float: left; margin-left: -2px;">100%<br>|</div></td><td rowspan="2" valign="bottom">({Procent_KoikoDni}%)</td></tr>
						<tr height="6"><td colspan="4"><table style="border: 1px solid rgb(0, 0, 0);" cellspacing="0" height="6" width="100%"><tbody><tr><td style="background: green none repeat scroll 0% 0%; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous;" width="{Procent_KoikoDni}%"></td><td width="0%"></td></tr></tbody></table></td></tr>
						</tbody></table-->';
					}
				}
				?>
				<?php
					if(in_array(getRegionNumber(), array(59,40,35))) {
						echo empty($EvnSection_KSG)?'<p>КСГ: </p>':'<p>КСГ: {EvnSection_KSGName}</p>';
						echo empty($EvnSection_KPG)?'<p>КПГ: </p>':'<p>КПГ: {EvnSection_KPG}</p>';
						echo empty($EvnSection_KSGCoeff)?'<p>Коэффициент КСГ/КПГ: </p>':'<p>Коэффициент КСГ/КПГ: {EvnSection_KSGCoeff}</p>';
					} elseif(in_array(getRegionNumber(), array(91))) {
						// echo empty($EvnSection_KPG)?'<p>КПГ: </p>':'<p>КПГ: {EvnSection_KPG}</p>'; // убрали поле #113905
					} elseif(in_array(getRegionNumber(), array(2,10,3))) {
						echo empty($EvnSection_KSG)?"<p id='EvnSection_data_{EvnSection_id}_KSG'>КСГ: </p>":"<p id='EvnSection_data_{EvnSection_id}_KSG'>КСГ: {EvnSection_KSG}</p>";
						echo empty($EvnSection_KPG)?"<p id='EvnSection_data_{EvnSection_id}_KPG'>КПГ: </p>":"<p id='EvnSection_data_{EvnSection_id}_KPG'>КПГ: {EvnSection_KPG}</p>";
					} elseif(in_array(getRegionNumber(), array(24))) {
						echo empty($EvnSection_KSG)?"<p id='EvnSection_data_{EvnSection_id}_KSG'>КСГ: </p>":"<p id='EvnSection_data_{EvnSection_id}_KSG'>КСГ: {EvnSection_KSG}</p>";
					} elseif(in_array(getRegionNumber(), array(1))) {
						echo empty($EvnSection_KSG)?"<p id='EvnSection_data_{EvnSection_id}_KSG'>КСГ: </p>":"<p id='EvnSection_data_{EvnSection_id}_KSG'>КСГ: {EvnSection_KSGName}</p>";
					} elseif(getRegionNumber() == 60) {
						echo empty($EvnSection_KSG)?'<p>КСГ: </p>':'<p>КСГ: {EvnSection_KSG} &nbsp;&nbsp; {EvnSection_KSGUslugaNumber}</p>';
						echo empty($EvnSection_KSGCoeff)?'<p>Коэффициент КСГ: </p>':'<p>Коэффициент КСГ: {EvnSection_KSGCoeff}</p>';
					} elseif(getRegionNumber() == 101) {
						echo empty($EvnSection_KSG)?'':'<p>КЗГ: {EvnSection_KSG}</p>';
						echo empty($EvnSection_KSGCoeff)?'<p>Коэффициент КЗГ: </p>':'<p>Коэффициент КЗГ: {EvnSection_KSGCoeff}</p>';
					} elseif(getRegionNumber() == 30) {
						echo empty($EvnSection_KSG)?'<p>КСГ: </p>':'<p>КСГ: {EvnSection_KSG}</p>';
						echo empty($EvnSection_KPG)?"<p id='EvnSection_data_{EvnSection_id}_KPG'></p>":"<p id='EvnSection_data_{EvnSection_id}_KPG'>КПГ: {EvnSection_KPG}</p>";
						// поле "КСГ/КПГ для расчёта"
						$fieldHtml = "<div style='clear:both;'>
		                	<div style='float:left;padding:5px 0;'>КСГ/КПГ для расчёта: ";
						if ($is_allow_edit) {
							$fieldHtml .= "<span id='EvnSection_data_{EvnSection_id}_inputMesRid' style='color:#000;' class='link' dataid='{Mes_rid}'>";
						}
						if (empty($MesRid_Code)) {
							$fieldHtml .= $empty_str;
						} else {
							$fieldHtml .= '{MesRid_Code}';
						}
						if ($is_allow_edit) {
							$fieldHtml .= "</span>";
						}
						$fieldHtml .= '</div>
							<div id="EvnSection_data_{EvnSection_id}_inputareaMesRid" class="input-area" style="float:left; margin-left:5px; display: none"></div>
						</div>';
						$fieldHtml .= "<div style='clear:both;'></div>";
						echo $fieldHtml;
						echo "<span id='EvnSection_data_{EvnSection_id}_inputMesTariff'>";
						echo empty($EvnSection_KSGCoeff)?"<p id='EvnSection_data_{EvnSection_id}_KSGCoeff'></p>":"<p id='EvnSection_data_{EvnSection_id}_KSGCoeff'>Коэффициент КСГ/КПГ: {EvnSection_KSGCoeff}</p>";
						echo "</span>";
					}
				?>
				<?php if ($Sex_SysNick == 'woman' && $Person_Age >= 15 && $Person_Age <= 50) { ?>
					<div style='clear:both;'>
						<div style="float:left;">Срок беременности, недель:&nbsp;</div>
						<div id="EvnSection_data_{EvnSection_id}_inputPregnancyEvnPSPeriod" style="color:#000;" class="link" style="float:left; margin-left:5px;" dataid="{PregnancyEvnPS_Period}">
							<?php echo !empty($PregnancyEvnPS_Period) ? $PregnancyEvnPS_Period : $empty_str ?>
						</div>
						<div id="EvnSection_data_{EvnSection_id}_inputareaPregnancyEvnPSPeriod" class="input-area" style="float:left; margin-left:5px; display: none"></div>
					</div>
				<?php } ?>
                <?php
					if (!empty($Diag_Code) && (in_array(substr($Diag_Code, 0, 3), array('I21', 'I22', 'I24')) || $Diag_Code == 'I20.0' ) ) {
						echo "<p id='EvnSection_data_{EvnSection_id}_IsCardShock'>Осложнен кардиогенным шоком: {EvnSection_IsCardShock}</p>";
						if (getRegionNumber()==59) echo "<p id='EvnSection_data_{EvnSection_id}_StartPain'>Время от начала боли: {EvnSection_StartPainHour} ч, {EvnSection_StartPainMin} мин</p>";
						if (getRegionNumber()==59) echo "<p id='EvnSection_data_{EvnSection_id}_GraceScalePoints'>Кол-во баллов по шкале GRACE: {EvnSection_GraceScalePoints}</p>";
					}
				?>
                <?php
					$isAllowViewPrescr = true;
                    if(getRegionNumber() == 63) {
                        if (FALSE === \stripos($LpuSection_Name, 'Приемное отделение')) {
	                        $isAllowViewPrescr = true;
                        } else {
	                        $isAllowViewPrescr = false;
                        }
                    }
					if ($isAllowViewPrescr) {
						?>
						<div style="display: {displayEvnObservGraphs}" id="EvnSection_data_{EvnSection_id}_EvnObservGraphs" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnSection_data_{EvnSection_id}_toolbarEvnObservGraphs').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnSection_data_{EvnSection_id}_toolbarEvnObservGraphs').style.display='none'">
	                        <div class="clear">
	                            <div class="data-table">
	                                <div class="caption">
	                                    <h2><span id="EvnSection_data_{EvnSection_id}_toggleDisplayEvnObservGraphs" class="collapsible">Температурный лист</span></h2>
	                                    <div id="EvnSection_data_{EvnSection_id}_toolbarEvnObservGraphs" class="toolbar">
	                                        <a id="EvnSection_data_{EvnSection_id}_printEvnObservGraphs" class="button icon icon-print16" title="Печать"><span></span></a>
	                                    </div>
	                                </div>
	                                <div id="EvnSection_data_{EvnSection_id}_wrapEvnObservGraphs" class="canvasHidden"></div>
	                            </div>
	                        </div>
	                    </div>
	                    {EvnPrescrPlan}
	                    {EvnDirectionStac}
						<?php
                    }

                ?>
			</div>
        </div>
    </div>
    <div class="right">
        <div id="EvnSection_data_{EvnSection_id}_toolbar" class="toolbar" style="display: none">
            <a id="EvnSection_data_{EvnSection_id}_editEvnSection" class="button icon icon-edit16" title="Редактировать"><span></span></a>
			<a id="EvnSection_data_{EvnSection_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>
</div>
