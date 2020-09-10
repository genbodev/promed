<ClinicalDocument xmlns="urn:hl7-org:v3" xsi:schemaLocation="urn:hl7-org:v3 http://tech-iemc-test.rosminzdrav.ru/tech/download/infrastructure/8/CDA.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

	<!-- ЗАГОЛОВОК ДОКУМЕНТА "Медицинская справка о допуске к управлению транспортным средством" -->
	<!-- R [1..1] Область применения документа (Страна) -->
	<realmCode code="RU"/>
	<!-- R [1..1] Указатель на использование CDA R2 -->
	<typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>
	<!-- R [1..1] Идентификатор Шаблона документа "Медицинская справка о допуске к управлению транспортным средством третий уровень формализации" -->
	<templateId root="1.2.643.5.1.13.2.7.5.1.8.3"/>
	<!-- R [1..1] Уникальный идентификатор документа -->
	<id root="{PassportToken_tid}.100.1.1.51" extension="{EvnPLDispDriver_id}"/>
	<!-- R [1..1] Тип документа -->
	<!--
		Форма N 003–В/у
	-->
	<code code="8" codeSystem="1.2.643.5.1.13.2.1.1.646" codeSystemName="Система электронных медицинских документов" displayName="Медицинская справка о допуске к управлению транспортным средством"/>
	<!-- R [1..1] Заголовок документа -->
	<title>Медицинская справка о допуске к управлению транспортным средством</title>
	<!-- R [1..1] Дата создания документа (с точностью до дня)-->
	<!--
		Дата выдачи "__" _______ 20__ г. <*>
	-->
	<effectiveTime value="   "/>
	<!-- R [1..1] Уровень конфиденциальности документа -->
	<confidentialityCode code="N" codeSystem="1.2.643.5.1.13.2.1.1.1504.9" codeSystemName="Уровень конфиденциальности документа" displayName="Обычный"/>
	<!-- R [1..1] Язык документа -->
	<languageCode code="ru-RU"/>
	<!-- R [1..1] Уникальный идентификатор набора версий документа -->
	<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.50 extension = идентификатор набора версий документа -->
	<setId root="{PassportToken_tid}.100.1.1.50" extension="{EvnPLDispDriver_id}"/>
	<!-- R [1..1] Номер версии данного документа -->
	<versionNumber value="1"/>
	<!-- R [1..1] ДАННЫЕ О ВОДИТЕЛЕ (КАНДИДАТЕ В ВОДИТЕЛИ) (Далее "ВОДИТЕЛЬ")-->
	<recordTarget>
		<!-- R [1..1] ВОДИТЕЛЬ (роль) -->
		<patientRole>
			<!-- R [1..1] Уникальный идентификатор водителя в МИС -->
			<id root="{PassportToken_tid}.100.1.1.10" extension="{Person_id}"/>
			<!-- [1..1] СНИЛС водителя -->
			<id root="1.2.643.100.3" extension="{Person_Snils}"/>
			<!-- R [1..1] Документ, удостоверяющий личность водителя, серия, номер, кем выдан. -->
			<id root="1.2.643.5.1.13.13.11.1011.1" extension="{Document_Ser} {Document_Num}" assigningAuthorityName='{DocOrg_Name}. Дата выдачи: {Document_begDate}'/>
			<!-- R [1..1] Адрес постоянной регистрации водителя -->
			<?php if (!empty($UAddress_Address)) { ?>
			<addr use="H">
				<!-- R [1..1] Адрес текстом -->
				<streetAddressLine>{UAddress_Address}</streetAddressLine>
				<!-- R [1..1] Регион РФ (республика, край, область)-->
				<state>{UKLRgn_id}</state>
			</addr>
			<?php } else { ?>
				<addr use="H" nullFlavor="ASKU"/>
			<?php } ?>
			<!-- R [1..1] Адрес фактического места жительства водителя -->
			<?php if (!empty($PAddress_Address)) { ?>
			<addr use="HP">
				<!-- R [1..1] адрес текстом -->
				<streetAddressLine>{PAddress_Address}</streetAddressLine>
				<!-- R [1..1] Регион РФ -->
				<state>{PKLRgn_id}</state>
			</addr>
			<?php } else { ?>
				<addr use="HP" nullFlavor="ASKU"/>
			<?php } ?>
			<!-- [0..1] Телефон водителя -->
			<?php if (!empty($Person_Phone)) { ?>
				<telecom value="tel:{Person_Phone}"/>
			<?php } ?>
			<?php if (!empty($PersonInfo_Email)) { ?>
				<telecom value="mailto:{PersonInfo_Email}"/>
			<?php } ?>
			<patient>
				<!-- R [1..1] ФИО водителя -->
				<name>
					<!-- R [1..1] Фамилия -->
					<family>{Person_SurName}</family>
					<!-- R [1..1] Имя -->
					<given>{Person_FirName}</given>
					<!-- [0..1] Отчество -->
					<?php if (!empty($Person_SecName)) { ?>
						<given>{Person_SecName}</given>
					<?php } ?>
				</name>
				<!-- R [1..1] Пол водителя -->
				<administrativeGenderCode code="{Sex_Code}" codeSystem="1.2.643.5.1.13.2.1.1.156" codeSystemName="Классификатор половой принадлежности" displayName="{Sex_Name}"/>
				<!-- R [1..1] Дата рождения водителя -->
				<birthTime value="{Person_BirthDay}"/>
			</patient>
			<!-- R [1..1] Организация, оформившая медицинскую справку о допуске к управлению ТС -->
			<providerOrganization>
				<!-- R [1..1] Идентификатор организации -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование организации -->
				<name>{Lpu_Name}</name>
				<!-- [0..1] Телефон организации -->
				<?php if (!empty($Lpu_Phone)) { ?>
					<telecom value="tel:{Lpu_Phone}" use="WP"/>
				<?php } else { ?>
					<telecom nullFlavor="NI"/>
				<?php } ?>
				<!-- R [1..1] Адрес организации-->
				<?php if (!empty($LUAddress_Address) || !empty($LPAddress_Address)) { ?>
					<addr>
						<!-- R [1..1] адрес текстом -->
						<?php if (!empty($LUAddress_id)) { ?>
							<?php if (!empty($LUAddress_Address)) { ?>
								<streetAddressLine>{LUAddress_Address}</streetAddressLine>
							<?php } else { ?>
								<streetAddressLine>
									<?php if (!empty($LUAddress_Zip)) { ?>
										{LUAddress_Zip},
									<?php } ?>
									<?php if (!empty($LUKLSubRgn_Name)) { ?>
										{LUKLSubRgn_Name},
									<?php } ?>
									<?php if (!empty($LUKLTown_Name)) { ?>
										{LUKLTown_Name},
									<?php } ?>
									<?php if (!empty($LUKLCity_Name)) { ?>
										г. {LUKLCity_Name},
									<?php } ?>
									<?php if (!empty($LUKLStreet_Name)) { ?>
										ул. {LUKLStreet_Name},
									<?php } ?>
									<?php if (!empty($LUAddress_Corpus)) { ?>
										корп. {LUAddress_Corpus},
									<?php } ?>
									<?php if (!empty($LUAddress_House)) { ?>
										д. {LUAddress_House},
									<?php } ?>
								</streetAddressLine>
							<?php } ?>
							<!-- R [1..1] Регион РФ -->
							<state>{LUKLRgn_id}</state>
						<?php } else { ?>
							<?php if (!empty($LPAddress_Address)) { ?>
								<streetAddressLine>{LPAddress_Address}</streetAddressLine>
							<?php } else { ?>
								<streetAddressLine>
									<?php if (!empty($LPAddress_Zip)) { ?>
										{LPAddress_Zip},
									<?php } ?>
									<?php if (!empty($LPKLSubRgn_Name)) { ?>
										{LPKLSubRgn_Name},
									<?php } ?>
									<?php if (!empty($LPKLTown_Name)) { ?>
										{LPKLTown_Name},
									<?php } ?>
									<?php if (!empty($LPKLCity_Name)) { ?>
										г. {LPKLCity_Name},
									<?php } ?>
									<?php if (!empty($LPKLStreet_Name)) { ?>
										ул. {LPKLStreet_Name},
									<?php } ?>
									<?php if (!empty($LPAddress_Corpus)) { ?>
										корп. {LPAddress_Corpus},
									<?php } ?>
									<?php if (!empty($LPAddress_House)) { ?>
										д. {LPAddress_House},
									<?php } ?>
								</streetAddressLine>
							<?php } ?>
						<?php } ?>
					</addr>
				<?php } else { ?>
					<addr nullFlavor="NI"/>
				<?php } ?>
			</providerOrganization>
		</patientRole>
	</recordTarget>
	<!-- R [1..1] ДАННЫЕ ОБ АВТОРЕ ДОКУМЕНТА - ВРАЧ-ТЕРАПЕВТ -->
	<author>
		<!-- R [1..1] Дата подписи документа автором – врач-терапевт -->
		<time value="{EvnPLDispDriver_setDT}"/>
		<!-- R [1..1] АВТОР (роль) -->
		<assignedAuthor>
			<!-- R [1..1] Уникальный идентификатор автора в МИС -->
			<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.70 extension = идентификатор персонала -->
			<id root="{PassportToken_tid}.100.1.1.70" extension="{MedPersonal_id}"/>
			<!-- [0..1] СНИЛС автора -->
			<id root="1.2.643.100.3" extension="524-153-723 12"/>
			<!-- R [1..1] Код должности автора-->
			<code code="109" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemName="Номенклатура должностей медицинских работников и фармацевтических работников" displayName="Врач-терапевт"/>
			<!-- [0..1] Адрес автора документа -->
			<!-- [0..1] Телефон автора -->
			<!-- [0..*] Прочие контакты автора (мобильный телефон) -->
			<!-- [0..*] Прочие контакты автора (электронная почта) -->
			<!-- R [1..1] АВТОР (человек) -->
			<assignedPerson>
				<!-- R [1..1] Фамилия, Имя, Отчество автора -->
				<name>
					<!-- R [1..1] Фамилия -->
					<family>{MedPersonal_SurName}</family>
					<!-- R [1..1] Имя -->
					<given>{MedPersonal_FirName}</given>
					<!-- [0..1] Отчество -->
					<?php if (!empty($MedPersonal_SecName)) { ?>
						<given>{MedPersonal_SecName}</given>
					<?php } ?>
				</name>
			</assignedPerson>
			<!-- [0..1] Место работы автора  -->
		</assignedAuthor>
	</author>
	<!-- R [1..1] ДАННЫЕ ОБ ОРГАНИЗАЦИИ-ВЛАДЕЛЬЦЕ ДОКУМЕНТА -->
	<custodian>
		<!-- R [1..1] Организация-владелец документа (роль) -->
		<assignedCustodian>
			<!-- R [1..1] Организация-владелец документа (организация) -->
			<representedCustodianOrganization>
				<!-- R [1..1] Идентификатор организации -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование организации-владельца документа -->
				<name>{Lpu_Name}</name>
				<!-- [0..1] Телефон организации -->
				<telecom nullFlavor="NI"/>
				<!-- [1..1] Адрес организации-->
				<addr nullFlavor="UNK"/>
			</representedCustodianOrganization>
		</assignedCustodian>
	</custodian>
	<!-- R [1..1] ДАННЫЕ О ПОЛУЧАТЕЛЕ ДОКУМЕНТА - ИЭМК \ МЗ РФ-->
	<informationRecipient>
		<!-- R [1..1] Получатель документа (роль) -->
		<intendedRecipient>
			<!-- R [1..1] Получатель документа (организация) -->
			<receivedOrganization>
				<!-- R [1..1] Идентификатор получающей организации-->
				<id root="1.2.643.5.1.13"/>
				<!-- R [1..1] Наименование получающей организации-->
				<name>Министерство здравоохранения Российской Федерации (ИЭМК)</name>
			</receivedOrganization>
		</intendedRecipient>
	</informationRecipient>
	<!-- R [1..1] ДАННЫЕ О ЛИЦЕ, ПРИДАВШЕМ ЮРИДИЧЕСКУЮ СИЛУ ДОКУМЕНТУ – ВРАЧ-ТЕРАПЕВТ -->
	<legalAuthenticator>
		<!-- R [1..1] Дата подписи документа лицом, придавшем юридическую силу документу -->
		<time value="{assignedTime}"/>
		<!-- R [1..1] Факт наличия подписи на документе -->
		<signatureCode code="{isAssigned}"/>
		<!-- R [1..1] Лицо, придавшее юридическую силу документу (роль) -->
		<assignedEntity>
			<!-- R [1..1] Уникальный идентификатор лица, придавшего юридическую силу документу в МИС -->
			<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.70 extension = идентификатор персонала -->
			<id root="{PassportToken_tid}.100.1.1.70" extension="{MedPersonal_id}"/>
			<!-- [0..1] СНИЛС лица, придавшего юридическую силу документу -->
			<id root="1.2.643.100.3" extension="524-153-723 12"/>
			<!-- [0..1] Идентификатор лица, придавшего юридическую силу документу, по Федеральному Регистру Медицинских Работников -->
			<id root="1.2.643.5.1.13.2.1.1.1504.100" extension="485148"/>
			<!-- R [1..1] Код должности лица, придавшего юридическую силу документу -->
			<code code="109" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemName="Номенклатура должностей медицинских работников и фармацевтических работников" displayName="Врач-терапевт"/>
			<!-- [0..1] Адрес лица, придавшего юридическую силу документу -->
			<!-- [0..1] Телефон лица, придавшего юридическую силу документу -->
			<!-- [0..*] Прочие контакты лица, придавшего юридическую силу документу (мобильный телефон) -->
			<!-- [0..*] Прочие контакты лица, придавшего юридическую силу документу (электронная почта) -->
			<!-- R [1..1] Лицо, придавшее юридическую силу документу (человек) -->
			<assignedPerson>
				<!-- R [1..1] Фамилия, Имя, Отчество автора -->
				<name>
					<!-- R [1..1] Фамилия -->
					<family>{MedPersonal_SurName}</family>
					<!-- R [1..1] Имя -->
					<given>{MedPersonal_FirName}</given>
					<!-- [0..1] Отчество -->
					<?php if (!empty($MedPersonal_SecName)) { ?>
						<given>{MedPersonal_SecName}</given>
					<?php } ?>
				</name>
			</assignedPerson>
			<!-- [0..1] Место работы лица, придавшего юридическую силу документу -->
		</assignedEntity>
	</legalAuthenticator>
	<!-- ТЕЛО ДОКУМЕНТА -->
	<component>
		<!-- R [1..1] Структурированное тело документа -->
		<structuredBody>
			<!-- R [1..1] СЕКЦИЯ: Документ-->
			<component>
				<section>
					<!-- R [1..1] код секции -->
					<code code="DOCINFO" codeSystem="1.2.643.5.1.13.2.1.1.1504.23" codeSystemName="Справочник секций документов" displayName="Сведения о документе"/>
					<!-- R [1..1] заголовок секции -->
					<title>Информация о документе</title>
					<!-- R [1..1] наполнение секции -->
					<text>
						<table>
							<tbody>
								<tr>
									<td>
										<content>Серия и номер документа</content>
									</td>
									<td>
										<content>{EvnPLDispDriver_MedSer} {EvnPLDispDriver_MedNum}</content>
									</td>
								</tr>
								<tr>
									<td>
										<content>Дата выдачи</content>
									</td>
									<td>
										<content>{EvnPLDispDriver_setDT}</content>
									</td>
								</tr>
							</tbody>
						</table>
					</text>
					<!-- R [1..1] кодирование ... Серия и номер медицинской справки о допуске к управлению транспортным средством-->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="103" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Серия и номер медицинской справки о допуске к управлению транспортным средством">
							</code>
							<value xsi:type="II" root="1.2.643.5.1.13.2.1.1.1504.144.3" extension="{EvnPLDispDriver_MedSer} {EvnPLDispDriver_MedNum}"/>
						</observation>
					</entry>
				</section>
			</component>
			<!-- R [1..1] СЕКЦИЯ: Категории и подкатегории ТС, на которые предоставляется право управления ТС -->
			<component>
				<section>
					<!-- R [1..1] код секции -->
					<code code="CATINFO" codeSystem="1.2.643.5.1.13.2.1.1.1504.23" codeSystemName="Справочник секций документов" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС врачей-специалистов"/>
					<!-- R [1..1] заголовок секции -->
					<title>Категории и подкатегории ТС, на которые предоставляется право управления ТС</title>
					<!-- R [1..1] наполнение секции -->
					<text>
						<table>
							<tbody>
								<tr>
									<th>
										<content>"A"</content>
									</th>
									<th>
										<content>"B"</content>
									</th>
									<th>
										<content>"C"</content>
									</th>
									<th>
										<content>"D"</content>
									</th>
									<th>
										<content>"BE"</content>
									</th>
									<th>
										<content>"CE"</content>
									</th>
									<th>
										<content>"DE"</content>
									</th>
									<th>
										<content>"Tm"</content>
									</th>
									<th>
										<content>"Tb"</content>
									</th>
									<th>
										<content>"M"</content>
									</th>
									<th>
										<content>"A1"</content>
									</th>
									<th>
										<content>"B1"</content>
									</th>
									<th>
										<content>"C1"</content>
									</th>
									<th>
										<content>"D1"</content>
									</th>
									<th>
										<content>"C1E"</content>
									</th>
									<th>
										<content>"D1E"</content>
									</th>
								</tr>
								<tr>
									<td>
										<content ID="CAT_1"><?php echo (in_array('A', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_2"><?php echo (in_array('B', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_3"><?php echo (in_array('C', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_4"><?php echo (in_array('D', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_5"><?php echo (in_array('BE', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_6"><?php echo (in_array('CE', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_7"><?php echo (in_array('DE', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_8"><?php echo (in_array('Tm', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_9"><?php echo (in_array('Tb', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_10"><?php echo (in_array('M', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
									    <content ID="CAT_11"><?php echo (in_array('A1', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_12"><?php echo (in_array('B1', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
										<td>
										<content ID="CAT_13"><?php echo (in_array('C1', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_14"><?php echo (in_array('D1', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_15"><?php echo (in_array('C1E', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
									<td>
										<content ID="CAT_16"><?php echo (in_array('D1E', $DriverCategorys)) ? 'V' : 'Z'; ?></content>
									</td>
								</tr>
							</tbody>
						</table>
					</text>
					<!-- R [1..1] кодирование ... Категория "A" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="1" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="A">
								<originalText>
									<reference value="#CAT_1"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "B" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="2" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="B">
								<originalText>
									<reference value="#CAT_2"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "C" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="3" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="C">
								<originalText>
									<reference value="#CAT_3"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "D" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="4" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="D">
								<originalText>
									<reference value="#CAT_4"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "BE" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="5" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="BE">
								<originalText>
									<reference value="#CAT_5"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "CE" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="6" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="CE">
								<originalText>
									<reference value="#CAT_6"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "DE" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="7" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="DE">
								<originalText>
									<reference value="#CAT_7"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "Tm" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="8" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="Tm">
								<originalText>
									<reference value="#CAT_8"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "Tb" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="9" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="Tb">
								<originalText>
									<reference value="#CAT_9"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "M" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="10" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="M">
								<originalText>
									<reference value="#CAT_10"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "A1" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="11" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="A1">
								<originalText>
									<reference value="#CAT_11"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "B1" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="12" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="B1">
								<originalText>
									<reference value="#CAT_12"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "C1" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="13" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="C1">
								<originalText>
									<reference value="#CAT_13"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "D1" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="14" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="D1">
								<originalText>
									<reference value="#CAT_14"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "C1E" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="15" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="C1E">
								<originalText>
									<reference value="#CAT_15"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Категория "D1E" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="701" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Категории и подкатегории ТС, на которые предоставляется право управления ТС">
							</code>
							<value xsi:type="CD" code="16" codeSystem="1.2.643.5.1.13.2.1.1.1504.74" codeSystemName="Справочник категорий и подкатегорий ТС" displayName="D1E">
								<originalText>
									<reference value="#CAT_16"/>
								</originalText>
							</value>
						</observation>
					</entry>
				</section>
			</component>
			<!-- R [1..1] СЕКЦИЯ: Заключение врача-терапевта-->
			<component>
				<section>
					<!-- R [1..1] код секции -->
					<code code="RESINFO" codeSystem="1.2.643.5.1.13.2.1.1.1504.23" codeSystemName="Справочник секций документов" displayName="Заключение врача-терапевта"/>
					<!-- R [1..1] заголовок секции -->
					<title>Заключение</title>
					<!-- R [1..1] наполнение секции -->
					<text>
	                    Медицинские ограничения к управлению транспортными средствами
						<table>
							<tbody>
								<tr>
									<th>
										<content>Медицинские ограничения</content>
									</th>
									<th>
										<content>Наличие</content>
									</th>
								</tr>
								<tr>
									<td>
										<content>1) Категории "A" или "M", подкатегории "A1" или "B1" с мотоциклетной посадкой или рулем мотоциклетного типа</content>
									</td>
									<td>
										<content ID="RES_1_1"><?php echo (in_array('1', $DriverMedicalCloses)) ? 'V' : 'Z'; ?></content>
									</td>
								</tr>
								<tr>
									<td>
										<content>2) Категории "B" "BE"; подкатегории "B1" (кроме транспортных средств с мотоциклетной посадкой или рулем мотоциклетного типа)</content>
									</td>
									<td>
										<content ID="RES_1_2"><?php echo (in_array('2', $DriverMedicalCloses)) ? 'V' : 'Z'; ?></content>
									</td>
								</tr>
								<tr>
									<td>
										<content>3) Категории "C", "CE", "D", "DE", "Tm" или "Tb"; подкатегории "C1", "D1", "C1E" или "D1E"</content>
									</td>
									<td>
										<content ID="RES_1_3"><?php echo (in_array('3', $DriverMedicalCloses)) ? 'V' : 'Z'; ?></content>
									</td>
								</tr>
							</tbody>
						</table>
						<br/>
						Медицинские показания к управлению транспортными средствами
						<table>
							<tbody>
								<tr>
									<th>
										<content>Медицинские показания</content>
									</th>
									<th>
										<content>Наличие</content>
									</th>
								</tr>
								<tr>
									<td>
										<content>С ручным управлением</content>
									</td>
									<td>
										<content ID="RES_2_1"><?php echo (in_array('1', $DriverMedicalIndications)) ? 'V' : 'Z'; ?></content>
									</td>
								</tr>
								<tr>
									<td>
										<content>С автоматической трансмиссией</content>
									</td>
									<td>
										<content ID="RES_2_2"><?php echo (in_array('2', $DriverMedicalIndications)) ? 'V' : 'Z'; ?></content>
									</td>
								</tr>
								<tr>
									<td>
										<content>Оборудованным акустической парковочной системой</content>
									</td>
									<td>
										<content ID="RES_2_3"><?php echo (in_array('3', $DriverMedicalIndications)) ? 'V' : 'Z'; ?></content>
									</td>
								</tr>
								<tr>
									<td>
										<content>С использованием водителем транспортного средства медицинских изделий для коррекции зрения</content>
									</td>
									<td>
										<content ID="RES_2_4"><?php echo (in_array('4', $DriverMedicalIndications)) ? 'V' : 'Z'; ?></content>
									</td>
								</tr>
								<tr>
									<td>
										<content>С использованием водителем транспортного средства медицинских изделий для компенсации потери слуха</content>
									</td>
									<td>
										<content ID="RES_2_5"><?php echo (in_array('5', $DriverMedicalIndications)) ? 'V' : 'Z'; ?></content>
									</td>
								</tr>
							</tbody>
						</table>
						<br/>
						Медицинское заключение:
						<content ID="RES_3_1">противопоказания к управлению ТС <?php echo ($ResultDispDriver_id == 2) ? 'имеются' : 'отсутствуют'; ?></content>, <content ID="RES_3_2">показания к управлению ТС <?php echo (!empty($DriverMedicalIndications)) ? 'имеются' : 'отсутствуют'; ?></content>, <content ID="RES_3_3">ограничения к управлению ТС <?php echo (!empty($DriverMedicalCloses)) ? 'имеются' : 'отсутствуют'; ?></content>.
					</text>
					<!-- R [1..1] кодирование ... Медицинские ограничения к управлению ТС -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="702" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинские ограничения к управлению ТС">
							</code>
							<value xsi:type="CD" code="1" codeSystem="1.2.643.5.1.13.2.1.1.1504.71" codeSystemName="Справочник медицинских ограничений к управлению ТС" displayName="Категории A или M, подкатегории A1 или B1 с мотоциклетной посадкой или рулем мотоциклетного типа">
								<originalText>
									<reference value="#RES_1_1"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Медицинские ограничения к управлению ТС -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="702" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинские ограничения к управлению ТС">
							</code>
							<value xsi:type="CD" code="2" codeSystem="1.2.643.5.1.13.2.1.1.1504.71" codeSystemName="Справочник медицинских ограничений к управлению ТС" displayName="Категории B или BE; подкатегории B1 (кроме транспортных средств с мотоциклетной посадкой или рулем мотоциклетного типа)">
								<originalText>
									<reference value="#RES_1_2"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Медицинские ограничения к управлению ТС -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="702" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинские ограничения к управлению ТС">
							</code>
							<value xsi:type="CD" code="3" codeSystem="1.2.643.5.1.13.2.1.1.1504.71" codeSystemName="Справочник медицинских ограничений к управлению ТС" displayName="Категории C, CE, D, DE, Tm или Tb; подкатегории C1, D1, C1E или D1E">
								<originalText>
									<reference value="#RES_1_3"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Медицинские показания к управлению ТС -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="703" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинские показания к управлению ТС">
							</code>
							<value xsi:type="CD" code="1" codeSystem="1.2.643.5.1.13.2.1.1.1504.72" codeSystemName="Справочник медицинских показаний к управлению ТС" displayName="С ручным управлением">
								<originalText>
									<reference value="#RES_2_1"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Медицинские показания к управлению ТС -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="703" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинские показания к управлению ТС">
							</code>
							<value xsi:type="CD" code="2" codeSystem="1.2.643.5.1.13.2.1.1.1504.72" codeSystemName="Справочник медицинских показаний к управлению ТС" displayName="С автоматической трансмиссией">
								<originalText>
									<reference value="#RES_2_2"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Медицинские показания к управлению ТС -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="703" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинские показания к управлению ТС">
							</code>
							<value xsi:type="CD" code="3" codeSystem="1.2.643.5.1.13.2.1.1.1504.72" codeSystemName="Справочник медицинских показаний к управлению ТС" displayName="Оборудованным акустической парковочной системой">
								<originalText>
									<reference value="#RES_2_3"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Медицинские показания к управлению ТС -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="703" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинские показания к управлению ТС">
							</code>
							<value xsi:type="CD" code="4" codeSystem="1.2.643.5.1.13.2.1.1.1504.72" codeSystemName="Справочник медицинских показаний к управлению ТС" displayName="С использованием водителем транспортного средства медицинских изделий для коррекции зрения">
								<originalText>
									<reference value="#RES_2_4"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Медицинские показания к управлению ТС -->
					<entry>
						<observation classCode="OBS" moodCode="EVN" negationInd="true">
							<code code="703" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинские показания к управлению ТС">
							</code>
							<value xsi:type="CD" code="5" codeSystem="1.2.643.5.1.13.2.1.1.1504.72" codeSystemName="Справочник медицинских показаний к управлению ТС" displayName="С использованием водителем транспортного средства медицинских изделий для компенсации потери слуха">
								<originalText>
									<reference value="#RES_2_5"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Медицинское заключение - наличие противопокзаний -->
					<!-- Необходимо указывать только одно значение из пары значений справочника: либо "противопоказания имеются" либо "противопоказания отсутствуют" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="704" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинское заключение">
							</code>
							<value xsi:type="CD" code="1" codeSystem="1.2.643.5.1.13.2.1.1.1504.73" codeSystemName="Справочник наличия медицинских показаний, ограничений и противопоказаний к управлению ТС" displayName="противопоказаний отсутствуют">
								<originalText>
									<reference value="#RES_3_1"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Медицинское заключение - наличие ограничений -->
					<!-- Необходимо указывать только одно значение из пары значений справочника: либо "ограничения имеются" либо "ограничения отсутствуют" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="704" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинское заключение">
							</code>
							<value xsi:type="CD" code="4" codeSystem="1.2.643.5.1.13.2.1.1.1504.73" codeSystemName="Справочник наличия медицинских показаний, ограничений и противопоказаний к управлению ТС" displayName="ограничения имеются">
								<originalText>
									<reference value="#RES_3_2"/>
								</originalText>
							</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Медицинское заключение - наличие показания -->
					<!-- Необходимо указывать только одно значение из пары значений справочника: либо "показания имеются" либо "показания отсутствуют" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="704" codeSystem="1.2.643.5.1.13.2.1.1.1504.41" codeSystemName="Справочник кодируемых полей" displayName="Медицинское заключение">
							</code>
							<value xsi:type="CD" code="6" codeSystem="1.2.643.5.1.13.2.1.1.1504.73" codeSystemName="Справочник наличия медицинских показаний, ограничений и противопоказаний к управлению ТС" displayName="показания имеются">
								<originalText>
									<reference value="#RES_3_3"/>
								</originalText>
							</value>
						</observation>
					</entry>
				</section>
			</component>
		</structuredBody>
	</component>
</ClinicalDocument>