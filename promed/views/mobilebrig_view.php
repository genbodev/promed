<!DOCTYPE html>
<head>
		<meta name="viewport" content="width=device-width, user-scalable=no" />
		<meta charset="<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">	
		<meta name="HandheldFriendly" content="true">
		
		<?php
		foreach ($css_files as $css) {
			show_stamped_CSS($css);
		}
		foreach ($js_files as $js) {
			show_stamped_JS($js);
		}
		
		$htmlStatusList= '';
		$firstStatus = '';
		foreach ($stats as $value) {
			if ($value['EmergencyTeamStatus_Id'] == $EmergencyTeamStatus_id) {
				$htmlStatusList .= "<a data-role='button' id='".$value['EmergencyTeamStatus_Id']."' class='ui-btn-active statusButton'>{$value['EmergencyTeamStatus_Name']}</a>";
				$firstStatus = $value['EmergencyTeamStatus_Name'];
			} else {
				$htmlStatusList .= "<a data-role='button' id='".$value['EmergencyTeamStatus_Id']."' class='statusButton' >{$value['EmergencyTeamStatus_Name']}</a>";
			}
		}
					
		?>
		<title>АРМ Старшего бригады</title>
	</head>

	<body>
		<audio id="audioNotification" preload="metadata">
			<source src="audio/mobile/Whistle.mp3" type="audio/mpeg; codecs='mp3'">
			<source src="audio/mobile/Whistle.ogg" type="audio/ogg">
		</audio>
		<!--<audio id="audioNotification" src="audio/mobile/Whistle.ogg" preload="auto"></audio>-->
		<header>
			<p><img src="img/Mobile/logo.png" alt="ProMed"/><span>Бриг.</span> <?php echo $EmergencyTeam_Num;?> <span>|</span> <?php echo $HeadBrig_ShortFIO;?> <span class="stat" id="statusName"><?php echo $firstStatus; ?></span> <button onclick="logout();">Выход</button><span class="time" id="time"></span></p>
		</header>
		<menu>
		<ul id='menu_ul'>
			<li class="call active menu_item" id="menu_call"><a href="" rel="external" onClick="menu_click('call');"><span></span>Вызов</a></li>
			<li class="patient menu_item" id="menu_patient"><a href="" rel="external" onClick="menu_click('patient');"><span></span>Пациент</a></li>
			<li class="brig menu_item" id="menu_brig"><a href="" rel="external" onClick="menu_click('brig');"><span></span>Бригада</a></li>
			<li class="stac menu_item" id="menu_stac"><a href="" rel="external" onClick="menu_click('stac');"><span></span>Стационар</a></li>
		</ul>
		</menu>
		
		<section class="content" id="content">
			<div data-role="page">
				<div data-role="content" >
					<div class="content_div" id="content_call">
						<p style="display: none;" id="EmergencyTeam_Num"><?php echo $EmergencyTeam_Num;?></p>
						<div class="header"><span>Новые вызовы(<b id="unclosedCardsCount"><?php	echo $unclosedCallCardsCount;?></b>)</span></div>
						<div id="UnclosedCards">
							<div class="callcard unclosed" id="unclosedTemplate" style="display:none">
								<h3 class="unclosedPersonName"></h3>
								<p class="unclosedPersonId" style="display:none;"></p>
								<p class="unclosedCallerInfo" style="display:none;"></p>
								<p class="unclosedCallType" style="display:none;"></p>
								<p class="unclosedPersonFir" style="display:none;"></p>
								<p class="unclosedPersonSec" style="display:none;"></p>
								<p class="unclosedPersonSur" style="display:none;"></p>
								<p class="unclosedPersonSex" style="display:none;"></p>
								<p class="unclosedPersonAgeTypeVal" style="display:none;"></p>
								<p class="unclosedPersonAge" style="display:none;"></p>
								<p class="unclosedBirthDayAndReason"><em class="bd"></em> <span class="ds"></span></p>
								<p class="unclosedAddr"></p>
								<p class="approve"><span class="timestamp"> <em>|</em> </span><button>Принять</button></p>
							</div>
							<?php
								echo $unclosedCallCards;
							?>
						</div>
						<div class="header"><span>Закрытые вызовы за смену (<b id="closedCardsCount">0</b>)</span></div>
						
						<div class="callcard closed" id="closedTemplate" style="display:none">
							<h3 class="closedPersonName"></h3>
							<p class="closedBirthDayAndReason"><em class="bd"></em> <span class="ds"></span></p>
							<p class="closedAddr"></p>
							<p class="approve"><span class="timestamp"><em>|</em></span><button>+</button></p>
						</div>
						
						<div id='ClosedCards'>
							<?php
								echo $closedCallCards;
							?>
						</div>
					</div>
					<div class="content_div" id="content_brig" style="display:none;">
						<div class="header"><span>Состав бригады</span><strong></strong></div>
						<p id="EmergencyTeam_id" style="display:none;"><?php echo $EmergencyTeam_id;?></p>
						<p><label class="classic">Номер</label><strong><?php echo $EmergencyTeam_Num;?></strong></p>
						<p><label class="classic">Старший бригады</label><strong><?php echo $HeadBrig_FIO;?></strong></p>
						<p><label class="classic">Фельдшер 1</label><strong><?php echo $Assistant1_FIO;?></strong></p>
						<p><label class="classic">Фельдшер 2</label><strong><?php echo $Assistant2_FIO;?></strong></p>
						<p><label class="classic">Водитель</label><strong><?php echo $Driver_FIO;?></strong></p>

						<div class="header"><span>Статус бригады</span><strong></strong></div>
						<?php echo $htmlStatusList;	?>						
					</div>
					<div class="content_div" id="content_patient" style="display:none;">
						<h3>Информация о пациенте</h3>
						<div class="header"><span>Сигнальная информация</span></div>
						<div id="signalInformation">
							<div id="personData" class="patientSignalInfo">
							</div>
							<div data-role="collapsible-set">
								<div data-role="collapsible" data-collapsed="true" id="PersonMedHistoryDiv">
									<h3>Анамнез жизни</h3>
									<div id="PersonMedHistory" class="patientSignalInfo">
									</div>
								</div>
								<div data-role="collapsible" data-collapsed="true">
									<h3>Антропометрия</h3>
									<div id="Anthropometry" class="patientSignalInfo">
									</div>
								</div>
								<div data-role="collapsible" data-collapsed="true">
									<h3>Группа крови и Rh-фактор</h3>
									<div id="BloodData" class="patientSignalInfo">
									</div>
								</div>
								<div data-role="collapsible" data-collapsed="true">
									<h3>Аллергологический анамнез</h3>
									<div id="AllergHistory" class="patientSignalInfo">
									</div>
								</div>
								<div data-role="collapsible" data-collapsed="true">
									<h3>Экспертный анамнез и льготы</h3>
									<div id="ExpertHistory" class="patientSignalInfo">
									</div>
								</div>
								<div data-role="collapsible" data-collapsed="true">
									<h3>Диспансерный учет</h3>
									<div id="PersonDispInfo" class="patientSignalInfo">
									</div>
								</div>
								<div data-role="collapsible" data-collapsed="true">
									<h3>Список уточненных диагнозов</h3>
									<div id="DiagList" class="patientSignalInfo">
									</div>
								</div>
								<div data-role="collapsible" data-collapsed="true">
									<h3>Список оперативных вмешательств</h3>
									<div id="SurgicalList" class="patientSignalInfo">
									</div>
								</div>
								
							</div>
						</div>
						<div class="header"><span>Последние посещения с диагнозами (за 2 года)</span></div>
						<div data-role="collapsible-set" id="eventSet">
						</div>
					</div>
					<div class="content_div" id="content_stac"  style="display:none;">
						<div class="header"><span>Выберите профиль стационара</span></div>
						<select id="LpuSectionProfile_select">
							<?php foreach ($SectionProfiles as $v) {
								echo "<option value={$v['LpuSectionProfile_OMSCode']}>{$v['LpuSectionProfile_Name']}</option>";
							} ?>
						</select>
						<p><em>Найдено <strong class="red" id="totalBedCount">15</strong> мест, <strong class="red" id="totalLpuCount">4</strong> учреждения</em></p>
						<div class="header"><span>Выбор стационара</span></div>
						<div data-role="collapsible-set" id="bookingOptions">
							<div data-role="collapsible" data-collapsed="true">
								<h3>Городская клиническая больница № 3 <span>(<strong class="red">6</strong> мест)</span><br/>
									<small>Серпуховская, 11а тел. приемн. отд 294-12-52</small>
								</h3>
								<div class="paddding30">
									<p><a href="#" data-role="button" data-inline="true">Терапевтическое отделение <strong class="red">3</strong></a></p>
									<p><a href="#" data-role="button" data-inline="true">Терапевтическое отделение <strong class="red">2</strong></a></p>
									<p><a href="#" data-role="button" data-inline="true">Терапевтическое отделение <strong class="red">1</strong></a></p>
								</div>
							</div>
						</div>
					</div>
					<div class="content_div" id="content_closecard" style="display:none;">
						<div class="header"><span>Время</span></div>
						
						<div data-role="fieldcontain">
							<p><label class='width30'>Выезд на вызов </label><input style="border-color: red;" class="timeInput" name="GoTime" type="text"/><a href="#" onclick="SetNowTimeForTimeline('GoTime');getSpendedTime();" data-role="button" data-inline="true" data-icon="gear" data-iconpos="notext" data-theme="a">Установить текущее время</a><p/>
							<p><label class='width30'>Прибытие на место вызова </label><input style="border-color: red;" class="timeInput" name="ArriveTime" type="text"/><a href="#" onclick="SetNowTimeForTimeline('ArriveTime');" data-role="button" data-inline="true" data-icon="gear" data-iconpos="notext" data-theme="a">Установить текущее время</a><p/>
							<p><label class='width30'>Начало транспортировки больного </label><input class="timeInput" name="TransportTime" type="text"/><a href="#" onclick="SetNowTimeForTimeline('TransportTime');" data-role="button" data-inline="true" data-icon="gear" data-iconpos="notext" data-theme="a">Установить текущее время</a><p/>
							<p><label class='width30'>Прибытие в медицинскую организацию </label><input class="timeInput" name="ToHospitalTime" type="text"/><a href="#" onclick="SetNowTimeForTimeline('ToHospitalTime');" data-role="button" data-inline="true" data-icon="gear" data-iconpos="notext" data-theme="a">Установить текущее время</a><p/>
							<p><label class='width30'>Окончание вызова</label><input style="border-color: red;" class="timeInput" name="EndTime" type="text"/><a href="#" onclick="SetNowTimeForTimeline('EndTime');getSpendedTime();" data-role="button" data-inline="true" data-icon="gear" data-iconpos="notext" data-theme="a">Установить текущее время</a></p>
							<p><label class='width30'>Возвращение на подстанцию</label><input class="timeInput" name="BackTime" type="text"/><a href="#" onclick="SetNowTimeForTimeline('BackTime');" data-role="button" data-inline="true" data-icon="gear" data-iconpos="notext" data-theme="a">Установить текущее время</a></p>
							<p><label class='width30'>Время, затраченное на вызов</label><input class="" name="SummTime" type="text"/></p>
						</div>
						
						<div class="header"><span>Адрес вызова</span></div>
						<div data-role="fieldcontain" id='callAddr'>
						</div>
						
						<div class="header"><span>Сведения о больном</span></div>
						<div data-role="fieldcontain" id='patientInfo'>
							<p><label class='width30'>Фамилия </label><input name="PersonSurName" type="text"/></p>
							<p><label class='width30'>Имя </label><input name="PersonFirName" type="text"/><p/>
							<p><label class='width30'>Отчество </label><input name="PersonSecName" type="text"/></p>
							<p><label class='width30'>Возраст </label><input name="PersonAge" type="text"/></p>
							<?php echo $combo['AgeType_id'];?>
							<p id="closeCardPersonBirthday"></p>
							</br>
							<label> Пол </label><select name="PersonSex_id" >
										<option selected="selected" value="1"> Мужской </option>
										<option value="2"> Женский </option>
										<option value="3"> Не определён </option>
							</select><br/>
							
							<p><label class='width30'>Место работы </label><input name="PersonJobPlace" type="text"/></p>
							<p><label class="width30">Серия и номер документа, удостоверяющего личность (при наличии)</label><input name="DocumentNum" type="text"/></p>
						</div>
						<div data-role="fieldcontain">
							<?php echo $combo['PersonRegistry_id'];?>
							<?php echo $combo['PersonSocial_id'];?>
						</div>
						
						<div class="header" ><span>Сведения о вызывающем</span></div>
						<div data-role="fieldcontain" id='callerInfo'>
						</div>

						<div class="header"><span>Повод к вызову</span></div>
						<div data-role="fieldcontain">
							<!--TODO: Вывести справочники промеда-->
							<p id="CloseCardCallType"></p>
							<p id="CloseCardReasonName"></p>
						</div>						
						<div class="header"><span>Причины выезда с опозданием</span></div>
						<div data-role="fieldcontain">
							<?php echo $combo['Delay_id'];?>
						</div>
						<div class="header"><span>Причина несчастного случая</span></div>
						<div data-role="fieldcontain">
							<?php echo $combo['AccidentReason_id'].'</br>'.$combo['Trauma_id'];?>
						</div>
						<div class="header"><span>Наличие клиники опьянения</span></div>
						<div data-role="fieldcontain">
							<fieldset data-role="controlgroup" data-type="vertical" data-mini="true">
								<label> Клиника опъянения </label> <select name="DruncClinic">
										<option value="2"> Есть </option>
										<option selected="selected" value="1"> Нет </option>
								</select>
							</fieldset>
						</div>
						<div class="header"><span>Жалобы</span></div>
						<div data-role="fieldcontain">							
							<label for="Complains">
								Жалобы
							</label>
							<textarea name="Complains" id="Complains" placeholder="" data-mini="true">
							</textarea>
						</div>
						<div class="header"><span>Анамнез</span></div>
						<div data-role="fieldcontain">
							<label for="Anamnez">
								Анамнез
							</label>
							<textarea name="Anamnez" id="Anamnez" placeholder="" data-mini="true">
							</textarea>
						</div>
						<div class="header"><span>Объективные данные</span></div>
						<div data-role="fieldcontain">
							<?php echo $combo['Condition_id'].
									'</br>'.
									$combo['Behavior_id'].
									'</br>'.
									$combo['Cons_id'].
									'<label> Менингеальные знаки </label> <select name="MeningSigns">
										<option value="2"> Есть </option>
										<option selected="selected" value="1"> Нет </option>
									</select>'.
									$combo['Pupil_id'].
									'</br>'.
									'<label> Анизокория </label> <select name="aniziocory">
										<option value="2"> Есть </option>
										<option selected="selected" value="1"> Нет </option>
									</select>'.
									'</br>'.
									'<label> Нистагм </label> <select name="nistagm">
										<option value="2"> Есть </option>
										<option selected="selected" value="1"> Нет </option>
									</select>'.
									'</br>'.
									'<label> Реакция на свет </label> <select name="reactOnLight">
										<option value="2"> Есть </option>
										<option selected="selected" value="1"> Нет </option>
									</select>'.
									'</br>'.
									$combo['Kozha_id'].
									'</br>'.
									'<label> Акроцианоз </label> <select name="acrocianoz">
										<option value="2"> Есть </option>
										<option selected="selected" value="1"> Нет </option>
									</select>'.
									'</br>'.
									'<label> Мраморность </label> <select name="mramornost">
										<option value="2"> Есть </option>
										<option selected="selected" value="1"> Нет </option>
									</select>'.
									'</br>'.
									$combo['Hypostas_id'].
									'</br>'.
									$combo['Crop_id'].
									'</br>'.
									$combo['Hale_id'].
									'</br>'.
									$combo['Rattle_id'].
									'</br>'.
									$combo['Shortwind_id'].
									'</br>'.
									'<p>Органы системы кровообращения</p>'.
									'</br>'.
									$combo['Heart_id'].
									'</br>'.
									$combo['Noise_id'].
									'</br>'.
									$combo['Pulse_id'].
									'</br>'.
									'<p>Органы пищеварения</p>'.
									'</br>'.
									$combo['Lang_id'].
									'</br>'.
									$combo['Gaste_id'].
									'</br>'.
									
									'<label> Участвует в акте дыхания </label> <select name="involvedInBreatheAct">
										<option selected="selected" value="2"> Да </option>
										<option value="1"> Нет </option>
									</select>'.
									'</br>'.
									'<label> Симптомы раздражения брюшины </label> <select name="stomachDisturbSymptoms">
										<option selected="selected" value="2"> Да </option>
										<option value="1"> Нет </option>
									</select>'.
									'</br>'.
									$combo['Liver_id'].
									'<p><label class="width30"> Мочеиспускание </label><input id="Urinate" name="Urinate" type="text"/></p>'.
									'</br>'.
									'<p><label class="width30"> Стул </label><input id="Chair" name="Chair" type="text"/></p>'.
									'</br>'.
									'<label for="otherSymptoms">
										Другие симптомы
									</label>
									<textarea name="otherSymptoms" id="otherSymptoms" placeholder="" data-mini="true"></textarea>'.
									'</br>'.
									'<table width="100%">
										<thead>
											<tr>
												<th></th>
												<th>До оказания помощи</th>
												<th>После оказания помощи</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>АД</td>
												<td><input class="numbersOnly" id="AD" name="AD" type="text"/> мм. рт.ст.</td>
												<td><input class="numbersOnly" id="EffAD" name="EffAD" type="text"/> мм. рт.ст.</td>
											</tr>
											<tr>
												<td>ЧД</td>
												<td><input class="numbersOnly" id="CHD" name="CHD" type="text"/> в минуту</td>
												<td><input class="numbersOnly" id=EffCHD" name="EffCHD" type="text"/> в минуту</td>
											</tr>
											<tr>
												<td>Пульс</td>
												<td><input class="numbersOnly" id="Pulse" name="Pulse" type="text"/> ударов в минуту</td>
												<td><input class="numbersOnly" id="EffPulse" name="EffPulse" type="text"/> ударов в минуту</td>
											</tr>
											<tr>
												<td>ЧСС</td>
												<td><input class="numbersOnly" id="CHSS" name="CHSS" type="text"/> в минуту</td>
												<td><input class="numbersOnly" id="EffCHSS" name="EffCHSS" type="text"/> в минуту</td>
											</tr>
											<tr>
												<td>Т</td>
												<td><input class="numbersOnly" id="Temper" name="Temper" type="text"/> &degС</td>
												<td><input class="numbersOnly" id="EffTemper" name="EffTemper" type="text"/> &degС</td>
											</tr>
											<tr>
												<td>Пульсоксиметрия</td>
												<td><input id="Pulsoxymetry" name="Pulsoxymetry" type="text"/></td>
												<td><input id="EffPulsoxymetry" name="EffPulsoxymetry" type="text"/></td>
											</tr>
											<tr>
												<td>Глюкометрия</td>
												<td><input id="Glucometry" name="Glucometry" type="text"/></td>
												<td><input id="EffGlucometry" name="EffGlucometry" type="text"/></td>
											</tr>
											<tr>
												<td>Рабочее АД</td>
												<td><input class="numbersOnly" id="workAD" name="workAD" type="text"/> мм. рт.ст.</td>
												<td></td>
											</tr>
										</tbody>
									</table>
									'.
									'</br>'.
									'<label for="AdditionalData">
										Дополнительные объективные данные. Локальный статус.
									</label>
									<textarea name="AdditionalData" id="AdditionalData" placeholder="" data-mini="true"></textarea>
									</br>
									<p>Электрокардиограмма (ЭКГ)</p>
									</br>
									<label for="EKGBefore">
										ЭКГ до оказания медицинской помощи
									</label>
									<textarea name="EKGBefore" id="EKGBefore" placeholder="" data-mini="true"></textarea>
									<p><label class="width30">Время проведения</label><input name="EKGBeforeTime" class="timeInput" type="text"/></p>
									</br>
									<label for="EKGAfter">
										ЭКГ после оказания медицинской помощи
									</label>
									<textarea name="EKGAfter" id="EKGAfter" placeholder="" data-mini="true"></textarea>
									<p><label class="width30">Время проведения</label><input name="EKGAfterTime" class="timeInput" type="text"/></p>'
							;?>
						</div>
						<div class="header"><span>Диагноз</span></div>
						<div data-role="fieldcontain">
							<p><label class="width30">Диагноз</label><input style="border-color: red;" name="CloseCardDiag" type="text"></p>
							<p id="CloseCardDiag_id" style="display: none"></p>
						</div>
						<div class="header"><span>Осложнения</span></div>
						<div data-role="fieldcontain">
							<?php echo $combo['Complicat_id'];?>
						</div>
						<div data-role="fieldcontain" id="ComplicatEffDiv">
							<?php echo $combo['ComplicatEf_id'];?>							
						</div>
						<div class="header"><span>Оказанная помощь</span></div>
						<div data-role="fieldcontain">
							<!--<table width="100%">
								<thead>
									<tr>
										<th>Код</th>
										<th>Наименование манипуляции</th>
										<th>Кол-во</th>
									</tr>
								</thead>
								<tbody id="ManipulationTable">
									<tr id='Manipulation1'>
										<td>-</td>
										<td class="ManipulationName"><input id="ManipulationName1" name="ManipulationName1" type="text"/><button data-inline="true" onclick="ManipulationButtonClick(1)"id="ManipulationButton1">+</button></td>
										<td class="ManipulationCount"><input id="ManipulationQuantity1" name="ManipulationQuantity1" type="text"/></td>
									</tr>
								</tbody>
							</table>-->
							<label for="HelpPlace">
								Оказанная помощь на месте вызова (проведенные манипуляции и мероприятия):
							</label>
							<textarea name="HelpPlace" id="HelpPlace" placeholder="" data-mini="true">
							</textarea>
							<label for="HelpAuto">
								Оказанная помощь в автомобиле скорой медицинской помощи (проведенные манипуляции и мероприятия):
							</label>
							<textarea name="HelpAuto" id="HelpAuto" placeholder="" data-mini="true">
							</textarea>
						</div>
						<!--<div class="header"><span>Оказанная помощь в автомобиле СМП</span></div>
						<div data-role="fieldcontain">
							
						</div>-->
						<div class="header"><span>Результат оказания скорой медицинской помощи</span></div>
						<div data-role="fieldcontain">
							<?php echo $combo['Result_id'];?>
						</div>
						<div class="header"><span>Больной</span></div>
						<div data-role="fieldcontain">
							<?php echo $combo['Patient_id'];?>
						</div>
						<div class="header"><span>Способ доставки больного в автомобиль СМП</span></div>
						<div data-role="fieldcontain">
							<?php echo $combo['TransToAuto_id'];?>
						</div>
						<div class="header"><span>Действия пациента</span></div>
						<div data-role="fieldcontain">
							<fieldset data-role="controlgroup" data-type="vertical" data-mini="true">
								<input id="PatientAction1" checked="checked" name="PatientAction" value="1" type="radio" />
								<label for="PatientAction1">
									Согласие на медицинское вмешательство
								</label>
								<input id="PatientAction2" name="PatientAction" value="2" type="radio" />
								<label for="PatientAction2">
									Отказ от медицинского вмешательства
								</label>
							</fieldset>
						</div>
						<div class="header"><span>Результат выезда</span></div>
						<div data-role="fieldcontain">
<!--							<fieldset data-role="controlgroup" data-type="vertical" data-mini="true">
								<legend>
									Результат вызова:
								</legend>
								<input id="radioCallResult1" checked="checked" name="CallResultView" value="1" type="radio" checked="checked" />
								<label for="radioCallResult1">
									Выполненный вызов
								</label>
								<input id="radioCallResult2" name="CallResultView" value="2" type="radio" />
								<label for="radioCallResult2">
									Безрезультатный вызов
								</label>
							</fieldset>-->
							
							<!-- <p> Выполненный выезд: </p> -->
							<div id="ResultUfaDiv">
								<?php echo $combo['ResultUfa_id'];?>
							</div>
							<!-- <p> Выполненный выезд: </p> -->
<!--							<div id="DeportCloseDiv">
								<?php echo $combo['DeportClose_id'];?>
							</div>
							 <p> Безрезульатный выезд: </p> 
							<div id="DeportFailDiv" style="display:none; ">
								<?php echo $combo['DeportFail_id'];?>
							</div>-->
							
							
							
						</div>
						<div class="header"><span>Километраж</span></div>
						<div data-role="fieldcontain">
							<p><label class="width30">Километраж вызова: </label><input name="Kilo" type="text"/> км</p>
						</div>
						<div class="header"><span>Примечания</span></div>
						<div data-role="fieldcontain">
							<label for="Complains">
								Примечания
							</label>
							<textarea name="DescText" id="DescText" placeholder="" data-mini="true">
							</textarea>
						</div>
					</div>
				</div>
			</div>
		</section>
		<footer>
			<nav>
				
			</nav>
			<p align="center"><button id="closeCallCard" class="footer_button" style="display: none;">Заполнить форму закрытия карты вызова</button></p>
			<p align="center"><button id="closeCall" class="footer_button" onclick="closeCard();" style="display: none;" >Закрыть форму</button></p>
		</footer>
	</body>
</html>