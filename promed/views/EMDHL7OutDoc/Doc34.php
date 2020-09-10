<ClinicalDocument xmlns="urn:hl7-org:v3" xsi:schemaLocation="urn:hl7-org:v3 CDA.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:f88="urn:f88">

	<!-- ЗАГОЛОВОК ДОКУМЕНТА "Направление на медико-социальную экспертизу" -->
	<!-- R [1..1] Область применения документа (Страна) -->
	<realmCode code="RU"/>
	<!-- R [1..1] Указатель на использование CDA R2 -->
	<typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>
	<!-- R [1..1] Идентификатор Шаблона документа "Направление на медико-социальную экспертизу. Третий уровень формализации." -->
	<templateId root="1.2.643.5.1.13.13.14.34.3"/>
	<!-- R [1..1] Уникальный идентификатор документа -->
	<id root="{PassportToken_tid}.100.1.1.51" extension="{EvnPrescrMse_id}"/>
	<!-- R [1..1] Тип документа -->
	<!--
		Форма N 088/у
	-->
	<code code="34" codeSystem="1.2.643.5.1.13.13.99.2.195" codeSystemVersion="2.6" codeSystemName="Система электронных медицинских документов" displayName="Направление на медико-социальную экспертизу"/>
	<!-- R [1..1] Заголовок документа -->
	<title>Направление на медико-социальную экспертизу</title>
	<!-- R [1..1] Дата создания документа -->
	<effectiveTime value="{EvnPrescrMse_issueDT}"/>
	<!-- R [1..1] Уровень конфиденциальности медицинского документа -->
	<confidentialityCode code="N" codeSystem="1.2.643.5.1.13.13.99.2.285" codeSystemVersion="1.1" codeSystemName="Уровень конфиденциальности медицинского документа" displayName="Обычный"/>
	<!-- R [1..1] Язык документа -->
	<languageCode code="ru-RU"/>
	<!-- R [1..1] Уникальный идентификатор набора версий документа -->
	<setId root="{PassportToken_tid}.100.1.1.50" extension="{EMDVersion_id}"/>
	<!-- R [1..1] Номер версии данного документа -->
	<versionNumber value="{EMDDocVersion}"/>
	<!-- R [1..1] ДАННЫЕ О ПАЦИЕНТЕ -->
	<recordTarget>
		<!-- R [1..1] ПАЦИЕНТ (роль) -->
		<patientRole>
			<!-- R [1..1] Уникальный идентификатор пациента в МИС -->
			<id root="{PassportToken_tid}.100.1.1.10" extension="{Person_id}"/>
			<!-- R [1..1] СНИЛС пациента -->
			<id root="1.2.643.100.3" extension="{Person_Snils}"/>
			<!-- R [1..1] ЛОКАЛЬНЫЙ ЭЛЕМЕНТ: Документ, удостоверяющий личность пациента, серия, номер, кем выдан. -->
			<f88:IdentityDoc>
				<!-- R [1..1] Тип документа -->
				<f88:IdentityCardTypeId xsi:type="CD" code="{DocumentType_id}" codeSystem="1.2.643.5.1.13.13.99.2.48" codeSystemVersion="2.1" codeSystemName="Документы, удостоверяющие личность" displayName="{DocumentType_Name}"/>
				<!--   [1..1] Серия документа -->
				<f88:Series xsi:type="ST">{Document_Ser}</f88:Series>
				<!-- R [1..1] Номер документа -->
				<f88:Number xsi:type="ST">{Document_Num}</f88:Number>
				<!--   [1..1] Кем выдан документ -->
				<f88:IssueOrgName xsi:type="ST">нет данных</f88:IssueOrgName>
				<!--   [1..1] Кем выдан документ, код подразделения -->
				<f88:IssueOrgCode xsi:type="ST">нет данных</f88:IssueOrgCode>
				<!-- R [1..1] Дата выдачи документа -->
				<f88:IssueDate xsi:type="TS" value="{Document_begDate}"/>
			</f88:IdentityDoc>
			<!-- [0..1] Адрес постоянной регистрации пациента -->
			<?php if (!empty($UAddress_Address) && !empty($UKLRgn_id) && !empty($UKLCountry_Name) && !empty($UKLSubRgn_Name)) { ?>
			<addr use="H">
				<!-- R [1..1] государство -->
				<country>{UKLCountry_Name}</country>
				<!-- R [1..1] субъект Российской Федерации / Регион РФ -->
				<state>{UKLRgn_id}</state>
				<!--   [1..1] район -->
				<county>{UKLSubRgn_Name}</county>
				<!--   [1..1] наименование населённого пункта -->
				<?php if (!empty($UKLCity_Name)) { ?>
				<city>{UKLCity_Name}</city>
				<?php } else { ?>
				<city nullFlavor="UNK"/>
				<?php } ?>
				<!--   [1..1] почтовый индекс -->
				<?php if (!empty($UAddress_Zip)) { ?>
				<postalCode>{UAddress_Zip}</postalCode>
				<?php } else { ?>
				<postalCode nullFlavor="UNK"/>
				<?php } ?>
				<!--   [1..1] адрес текстом -->
				<streetAddressLine>{UAddress_Address}</streetAddressLine>
				<!--   [1..1] дом -->
				<?php if (!empty($UAddress_House)) { ?>
				<houseNumber>{UAddress_House}</houseNumber>
				<?php } else { ?>
				<houseNumber nullFlavor="UNK"/>
				<?php } ?>
				<!--   [1..1] улица -->
				<?php if (!empty($UKLStreet_Name)) { ?>
				<streetName>{UKLStreet_Name}</streetName>
				<?php } else { ?>
				<streetName nullFlavor="UNK"/>
				<?php } ?>
				<!--   [1..1] квартира -->
				<?php if (!empty($UAddress_Flat)) { ?>
				<unitID>{UAddress_Flat}</unitID>
				<?php } else { ?>
				<unitID nullFlavor="UNK"/>
				<?php } ?>
			</addr>
			<?php } ?>
			<!-- [0..1] Адрес фактического места жительства пациента -->
			<?php if (empty($UAddress_Address) && !empty($PAddress_Address) && !empty($PKLRgn_id) && !empty($PKLCountry_Name) && !empty($PKLSubRgn_Name)) { ?>
			<addr use="HP">
				<!-- R [1..1] государство -->
				<country>{PKLCountry_Name}</country>
				<!-- R [1..1] субъект Российской Федерации / Регион РФ -->
				<state>{PKLRgn_id}</state>
				<!--   [1..1] район -->
				<county>{PKLSubRgn_Name}</county>
				<!--   [1..1] наименование населённого пункта -->
				<?php if (!empty($PKLCity_Name)) { ?>
				<city>{PKLCity_Name}</city>
				<?php } else { ?>
				<city nullFlavor="UNK"/>
				<?php } ?>
				<!--   [1..1] почтовый индекс -->
				<?php if (!empty($PAddress_Zip)) { ?>
				<postalCode>{PAddress_Zip}</postalCode>
				<?php } else { ?>
				<postalCode nullFlavor="UNK"/>
				<?php } ?>
				<!--   [1..1] адрес текстом -->
				<streetAddressLine>{PAddress_Address}</streetAddressLine>
				<!--   [1..1] дом -->
				<?php if (!empty($PAddress_House)) { ?>
				<houseNumber>{PAddress_House}</houseNumber>
				<?php } else { ?>
				<houseNumber nullFlavor="UNK"/>
				<?php } ?>
				<!--   [1..1] улица -->
				<?php if (!empty($PKLStreet_Name)) { ?>
				<streetName>{PKLStreet_Name}</streetName>
				<?php } else { ?>
				<streetName nullFlavor="UNK"/>
				<?php } ?>
				<!--   [1..1] квартира -->
				<?php if (!empty($PAddress_Flat)) { ?>
				<unitID>{PAddress_Flat}</unitID>
				<?php } else { ?>
				<unitID nullFlavor="UNK"/>
				<?php } ?>
			</addr>
			<?php } ?>
	<!-- [0..1] Телефон пациента -->
	<?php if (!empty($Person_Phone)) { ?>
	<telecom value="tel:{Person_Phone}"/>
	<?php } else { ?>
	<telecom nullFlavor="UNK"/>
	<?php } ?>
<!-- [0..*] Прочие контакты пациента (электронная почта) -->
<?php if (!empty($PersonInfo_Email)) { ?>
<telecom value="mailto:{PersonInfo_Email}"/>
<?php } ?>
<!-- [0..*] Прочие контакты пациента (мобильный телефон) -->
<!-- [0..*] Прочие контакты пациента (электронная почта) -->
<!-- R [1..1] ПАЦИЕНТ (человек) -->
<!--
6. Фамилия,    имя,   отчество
7. Дата рождения
8. Пол
-->
<patient>
	<!-- R [1..1] ФИО пациента -->
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
	<!-- R [1..1] Пол пациента -->
	<administrativeGenderCode code="{Sex_Code}" codeSystem="1.2.643.5.1.13.13.11.1040" codeSystemVersion="2.1" codeSystemName="Классификатор половой принадлежности" displayName="{Sex_Name}"/>
	<!-- R [1..1] Дата рождения -->
	<birthTime value="{Person_BirthDay}"/>
	<!-- [0..1] Законный (уполномоченный) представитель -->
</patient>
<!-- R [1..1] Организация (ЛПУ или его филиал), направившая на медико-социальную экспертизу -->
<!--
	наименование медицинской организации, адрес медицинской организации, ОГРН медицинской организации
-->
<providerOrganization>
	<!-- R [1..1] Идентификатор организации -->
	<id root="{PassportToken_tid}"/>
	<!-- R [1..1] ОГРН -->
	<f88:Ogrn xsi:type="ST">{Lpu_OGRN}</f88:Ogrn>
	<!-- R [1..1] Наименование организации -->
	<name><![CDATA[{Lpu_Name}]]></name>
	<!-- [1..1] Телефон организации -->
	<?php if (!empty($Lpu_Phone)) { ?>
	<telecom value="tel:{Lpu_Phone}" use="WP"/>
	<?php } else { ?>
	<telecom nullFlavor="NI"/>
<?php } ?>

<!-- [0..*] Прочие контакты организации -->
<!-- R [1..1] Адрес организации -->
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
<!-- R [1..1] ДАННЫЕ ОБ АВТОРЕ ДОКУМЕНТА -->
<author>
	<!-- [1..1] Дата подписи документа автором -->
	<time value="{EvnPrescrMse_setDT}"/>
	<!-- R [1..1] АВТОР (роль) -->
	<assignedAuthor>
		<!-- R [1..1] Уникальный идентификатор автора в МИС -->
		<id root="{PassportToken_tid}.100.1.1.70" extension="{MedStaffFact_id}"/>
		<!-- [0..1] СНИЛС автора -->
		<!-- R [1..1] Код должности автора -->
		<code code="{MedPost_Code}" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemVersion="4.2" codeSystemName="Должности работников организаций медицинского и фармацевтического профиля" displayName="{MedPost_Name}"/>
		<!-- [0..1] Адрес автора документа -->
		<!-- [0..1] Телефон автора -->
		<!-- [0..*] Прочие контакты автора (мобильный телефон) -->
		<!-- [0..*] Прочие контакты автора (электронная почта) -->
		<!-- [0..*] Прочие контакты автора (факс) -->
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
			<!-- R [1..1] Наименование организации -->
			<name><![CDATA[{Lpu_Name}]]></name>
			<!-- [1..1] Телефон организации -->
			<telecom nullFlavor="NI"/>
			<!-- R [1..1] Адрес организации -->
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
<!-- R [1..1] ДАННЫЕ О ЛИЦЕ, ПРИДАВШЕМ ЮРИДИЧЕСКУЮ СИЛУ ДОКУМЕНТУ -->
<legalAuthenticator>
	<!-- R [1..1] Дата подписи документа лицом, придавшем юридическую силу документу -->
	<time value="{assignedTime}"/>
	<!-- R [1..1] Факт наличия подписи на документе -->
	<signatureCode code="{isAssigned}"/>
	<!-- R [1..1] Лицо, придавшен юридическую силу документу (роль) -->
	<assignedEntity>
		<!-- R [1..1] Уникальный идентификатор лица, придавшего юридическую силу документу -->
		<id root="{PassportToken_tid}.100.1.1.70" extension="{sign_MedStaffFact_id}"/>
		<!-- [0..1] СНИЛС лица, придавшего юридическую силу документу -->
		<!-- R [1..1] Код должности лица, придавшего юридическую силу документу -->
		<code code="{sign_MedPost_Code}" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemVersion="4.2" codeSystemName="Должности работников организаций медицинского и фармацевтического профиля" displayName="{sign_MedPost_Name}"/>
		<!-- [0..1] Адрес лица, придавшего юридическую силу документу -->
		<!-- [0..1] Телефон лица, придавшего юридическую силу документу -->
		<!-- [0..*] Прочие контакты лица, придавшего юридическую силу документу (мобильный телефон) -->
		<!-- [0..*] Прочие контакты лица, придавшего юридическую силу документу (электронная почта) -->
		<!-- [0..*] Прочие контакты лица, придавшего юридическую силу документу (факс) -->
		<!-- R [1..1] Лицо, придавшен юридическую силу документу (человек) -->
		<assignedPerson>
			<!-- R [1..1] Фамилия, Имя, Отчество лица, придавшего юридическую силу документу -->
			<name>
				<!-- R [1..1] Фамилия -->
				<family>{sign_MedPersonal_SurName}</family>
				<!-- R [1..1] Имя -->
				<given>{sign_MedPersonal_FirName}</given>
				<!-- [0..1] Отчество -->
				<?php if (!empty($sign_MedPersonal_SecName)) { ?>
				<given>{sign_MedPersonal_SecName}</given>
			<?php } ?>
			</name>
		</assignedPerson>
		<!-- [0..1] Место работы лица, придавшего юридическую силу документу -->
	</assignedEntity>
</legalAuthenticator>
<!-- [0..1] СВЕДЕНИЯ О СТРАХОВОЙ КОМПАНИИ ОМС -->
<!--
13. Гражданин находится
-->
<!-- R [1..1] Местонахождение гражданина -->
<participant typeCode="LOC">
	<!-- R [1..1] Местонахождение гражданина (роль) -->
	<associatedEntity classCode="SDLOC">
		<!-- R [1..1] Тип местонахождения гражданина -->
		<code code="2" codeSystem="1.2.643.5.1.13.13.11.1008" codeSystemVersion="2.2" codeSystemName="Место оказания медицинской помощи" displayName="На дому"/>
		<!-- [1..1] Организация или учреждение -->
		<scopingOrganization nullFlavor="NA"/>
	</associatedEntity>
</participant>
<!-- R [1..1] СВЕДЕНИЯ О ДОКУМЕНТИРУЕМОМ СОБЫТИИ -->
<!--
	 Председатель врачебной комиссии:
	 Члены врачебной комиссии:
-->
<documentationOf>
	<!-- R [1..1] Проведённая врачебная комиссия -->
	<serviceEvent>
		<!-- R [1..1] Даты проведения комиссии -->
		<effectiveTime>
			<!-- [1..1] Дата начала -->
			<low nullFlavor="NI"/>
		</effectiveTime>
		<!-- [1..*] СВЕДЕНИЯ ОБ УЧАСТНИКАХ КОМИССИИ -->
		<?php foreach($performers as $performer) { ?>
		<performer typeCode="<?php echo $performer['prf_typeCode']; ?>">
			<assignedEntity>
				<!-- R [1..1] Уникальный идентификатор члена врачебной комиссии -->
				<id root="{PassportToken_tid}.100.1.1.70" extension="<?php echo $performer['prf_MedStaffFact_id']; ?>"/>
				<!-- [0..1] СНИЛС члена врачебной комиссии -->
				<?php if (!empty($performer['prf_MedPersonal_Snils'])) { ?>
				<id root="1.2.643.100.3" extension="<?php echo $performer['prf_MedPersonal_Snils']; ?>"/>
				<?php } ?>
				<!-- R [1..1] Код должности члена врачебной комиссии -->
				<code code="<?php echo $performer['prf_MedPost_Code']; ?>" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemVersion="2.2" codeSystemName="Должности работников организаций медицинского и фармацевтического профиля" displayName="<?php echo $performer['prf_MedPost_Name']; ?>"/>
				<!-- [0..1] Адрес члена врачебной комиссии документа -->
				<!-- [0..1] Телефон члена врачебной комиссии -->
				<!-- [0..*] Прочие контакты члена врачебной комиссии (мобильный телефон) -->
				<!-- [0..*] Прочие контакты члена врачебной комиссии (электронная почта) -->
				<!-- [0..*] Прочие контакты члена врачебной комиссии (факс) -->
				<!-- R [1..1] Член врачебной комиссии (человек) -->
				<assignedPerson>
					<!-- R [1..1] Фамилия, Имя, Отчество члена врачебной комиссии -->
					<name>
						<!-- R [1..1] Фамилия -->
						<family><?php echo $performer['prf_MedPersonal_SurName']; ?></family>
						<!-- R [1..1] Имя -->
						<given><?php echo $performer['prf_MedPersonal_FirName']; ?></given>
						<!-- [1..1] Отчество -->
						<given><?php echo $performer['prf_MedPersonal_SecName']; ?></given>
					</name>
				</assignedPerson>
				<!-- [0..1] Место работы члена врачебной комиссии  -->
			</assignedEntity>
		</performer>
		<?php } ?>
	</serviceEvent>
</documentationOf>
<!-- R [1..1] ТЕЛО ДОКУМЕНТА -->
<component>
	<!-- R [1..1] Структурированное тело документа -->
	<structuredBody>
		<!-- R [1..1] СЕКЦИЯ: НАПРАВЛЕН  -->
		<component>
			<section>
				<!-- R [1..1] код секции -->
				<code code="SCOPORG" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5"  codeSystemName="Секции CDA документов" displayName="Цель направления и медицинская организация, куда направлен"/>
				<!-- R [1..1] заголовок секции -->
				<title>НАПРАВЛЕНИЕ</title>
				<!-- R [1..1] наполнение секции -->
				<text>
					<paragraph>
						<caption>Гражданин направляется на медико-социальную экспертизу</caption>
						<content>"{isFirstTime}"</content>
					</paragraph>
					<paragraph>
						<caption>Цель направления</caption>
						<content>"{MseDirectionAimType_Name}"</content>
					</paragraph>
					<br/>
					<paragraph>
						<caption>Протокол врачебной комиссии медицинской организации, содержащий решение о направлении гражданина на медико-социальную экспертизу</caption>
						<content>№ "{EvnVK_NumProtocol}" от {EvnVK_setDTFormatted}.</content>
					</paragraph>
					<br/>
					<paragraph>
						<caption>Дата выдачи гражданину направления на медико-социальную экспертизу медицинской организацией</caption>
						<content>"{EvnPrescrMse_setDTFormatted}"</content>
					</paragraph>
					<br/>
					<paragraph>
						<caption>Гражданство</caption>
						<content>"{personNationName}"</content>
					</paragraph>

					<?php if (!empty($MilitaryKind_id)) { ?>
					<br/>
					<paragraph>
						<caption>Отношения к воинской обязанности</caption>
						<content>"{MilitaryKind_FullName}"</content>
					</paragraph>
				<?php } ?>
				</text>
				<!-- R [1..1] Кодирование цели направления и медицинской организации, куда направлен пациент -->
				<entry>
					<act classCode="ACT" moodCode="RQO">
						<!-- R [1..1] Тип направления -->
						<code code="9" codeSystem="1.2.643.5.1.13.13.11.1009" codeSystemVersion="1.2" codeSystemName="Виды медицинских направлений" displayName="На медико-социальную экспертизу"/>
						<statusCode code="active"/>
						<!-- R [1..1] Кодирование организации, куда направлен пациент -->
						<performer>
							<assignedEntity>
								<!-- [1..1] Уникальный идентификатор человека-исполнителя, как правило он неизвестен - следует использовать nullFlavor -->
								<id nullFlavor="NI"/>
								<representedOrganization>
									<!-- R [1..1] Идентификатор организации исполнителя -->
									<id root="{MPassportToken_tid}"/>
									<!-- R [1..1] Наименование организации исполнителя -->
									<name><![CDATA[{MLpu_Name}]]></name>
									<!-- [1..1] Телефон организации исполнителя -->
									<telecom nullFlavor="NI"/>
									<!-- R [1..1] Адрес организации исполнителя -->
									<?php if (!empty($MLUAddress_Address) || !empty($MLPAddress_Address)) { ?>
									<addr>
										<!-- R [1..1] адрес текстом -->
										<?php if (!empty($MLUAddress_id)) { ?>
										<?php if (!empty($MLUAddress_Address)) { ?>
										<streetAddressLine>{MLUAddress_Address}</streetAddressLine>
										<?php } else { ?>
										<streetAddressLine>
											<?php if (!empty($MLUAddress_Zip)) { ?>
											{MLUAddress_Zip},
										<?php } ?>
										<?php if (!empty($MLUKLSubRgn_Name)) { ?>
										{MLUKLSubRgn_Name},
									<?php } ?>
									<?php if (!empty($MLUKLTown_Name)) { ?>
									{MLUKLTown_Name},
								<?php } ?>
								<?php if (!empty($MLUKLCity_Name)) { ?>
								г. {MLUKLCity_Name},
							<?php } ?>
							<?php if (!empty($MLUKLStreet_Name)) { ?>
							ул. {MLUKLStreet_Name},
						<?php } ?>
						<?php if (!empty($MLUAddress_Corpus)) { ?>
						корп. {MLUAddress_Corpus},
					<?php } ?>
					<?php if (!empty($MLUAddress_House)) { ?>
					д. {MLUAddress_House},
				<?php } ?>
				</streetAddressLine>
			<?php } ?>
			<!-- R [1..1] Регион РФ -->
			<state>{MLUKLRgn_id}</state>
			<?php } else { ?>
			<?php if (!empty($MLPAddress_Address)) { ?>
			<streetAddressLine>{MLPAddress_Address}</streetAddressLine>
			<?php } else { ?>
			<streetAddressLine>
				<?php if (!empty($MLPAddress_Zip)) { ?>
				{MLPAddress_Zip},
			<?php } ?>
			<?php if (!empty($MLPKLSubRgn_Name)) { ?>
			{MLPKLSubRgn_Name},
		<?php } ?>
		<?php if (!empty($MLPKLTown_Name)) { ?>
		{MLPKLTown_Name},
	<?php } ?>
	<?php if (!empty($MLPKLCity_Name)) { ?>
	г. {MLPKLCity_Name},
<?php } ?>
<?php if (!empty($MLPKLStreet_Name)) { ?>
ул. {MLPKLStreet_Name},
<?php } ?>
<?php if (!empty($MLPAddress_Corpus)) { ?>
корп. {MLPAddress_Corpus},
<?php } ?>
<?php if (!empty($MLUAddress_House)) { ?>
д. {MLPAddress_House},
<?php } ?>
</streetAddressLine>
<?php } ?>
<?php } ?>
</addr>
<?php } else { ?>
<addr nullFlavor="NI"/>
<?php } ?>
</representedOrganization>
</assignedEntity>
</performer>
<!-- R [1..1] Кодирование цели направления -->
<entryRelationship typeCode="SUBJ" inversionInd="true">
	<observation classCode="OBS" moodCode="EVN">
		<!-- R [1..1] Цель направления -->
		<code code="{MseDirectionAimType_Code}" displayName="{MseDirectionAimType_Name}" codeSystem="1.2.643.5.1.13.13.99.2.147" codeSystemVersion="1.2" codeSystemName="Цель направления на медико-социальную экспертизу"/>
	</observation>
</entryRelationship>
<!-- R [1..1] Кодирование порядка обращения -->
<entryRelationship typeCode="SUBJ" inversionInd="true">
	<observation classCode="OBS" moodCode="EVN">
		<!-- R [1..1] Порядок обращения -->
		<code code="{isFirstTimeCode}" codeSystem="1.2.643.5.1.13.13.11.1007" codeSystemVersion="2.1" codeSystemName="Порядок случаев госпитализации или обращения" displayName="{isFirstTime}"/>
	</observation>
</entryRelationship>
<!-- R [1..1] Кодирование даты и номера протокол врачебной комиссии -->
<entryRelationship typeCode="SUBJ" inversionInd="true">
	<observation classCode="OBS" moodCode="EVN">
		<code code="4059" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.10" codeSystemName="Кодируемые поля CDA документов" displayName="Протокол врачебной комиссии"/>
		<!-- R [1..1] Дата протокола врачебной комиссии -->
		<effectiveTime>
			<!-- R [1..1] Дата -->
			<high value="{EvnVK_setDT}"/>
		</effectiveTime>
		<!-- R [1..1] Номер протокола врачебной комиссии -->
		<value xsi:type="ST">{EvnVK_NumProtocol}</value>
	</observation>
</entryRelationship>
<!-- [0..1] Место проведения медико-социальной экспертизы -->
<!-- R [1..1] Кодирование нуждаемости в оказании паллиативной медицинской помощи -->
<entryRelationship typeCode="SUBJ" inversionInd="true">
	<observation classCode="OBS" moodCode="EVN">
		<code code="4061" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.10" codeSystemName="Кодируемые поля CDA документов" displayName="Нуждаемость в оказании паллиативной медицинской помощи"/>
		<value xsi:type="BL" value="{EvnPrescrMse_IsPalliative}"/>
	</observation>
</entryRelationship>
<!-- R [1..1] Кодирование даты выдачи направления -->
<entryRelationship typeCode="SUBJ" inversionInd="true">
	<observation classCode="OBS" moodCode="EVN">
		<code code="4062" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.10" codeSystemName="Кодируемые поля CDA документов" displayName="Дата выдачи направления на МСЭ"/>
		<value xsi:type="TS" value="{EvnPrescrMse_setDT}"/>
	</observation>
</entryRelationship>
</act>
</entry>
<!-- R [1..1] кодирование гражданства -->
<entry>
	<observation classCode="OBS" moodCode="EVN">
		<code code="{personNationCode}" codeSystem="1.2.643.5.1.13.13.99.2.315" codeSystemVersion="1.1"  codeSystemName="Категории гражданства" displayName="{personNationName}"/>
	</observation>
</entry>
<!-- R [1..1] кодирование отношения к воинской обязанности -->
<?php if (!empty($MilitaryKind_id)) { ?>
<entry>
	<observation classCode="OBS" moodCode="EVN">
		<code code="{MilitaryKind_Code}" codeSystem="1.2.643.5.1.13.13.99.2.314" codeSystemVersion="1.1"  codeSystemName="Отношение к воинской обязанности" displayName="{MilitaryKind_FullName}"/>
	</observation>
</entry>
<?php } ?>
</section>
</component>
<!-- [1..1] СЕКЦИЯ: Место работы, должность  -->
<component>
	<section>
		<!-- R [1..1] Код секции -->
		<code code="WORK" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5"  codeSystemName="Секции CDA документов" displayName="Место работы и должность, условия труда"/>
		<!-- R [1..1] Заголовок секции -->
		<title>ТРУДОВАЯ ДЕЯТЕЛЬНОСТЬ</title>
		<!-- [1..1] Наполнение секции -->
		<?php if ($EvnPrescrMse_IsWork == 2) { ?>
		<text>
			<paragraph><caption>Сведения о трудовой деятельности</caption>"{Post_Name}" в "{Org_Name}"</paragraph>
		</text>
		<?php } else { ?>
		<text>
			<paragraph><caption>Сведения о трудовой деятельности</caption>нет данных.</paragraph>
		</text>
	<?php } ?>

	</section>
</component>
<!-- [1..1] СЕКЦИЯ: Образование  -->
<component>
	<section>
		<!-- R [1..1] Код секции -->
		<code code="EDU" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5"  codeSystemName="Секции CDA документов" displayName="Образование"/>
		<!-- R [1..1] Заголовок секции -->
		<title>ОБРАЗОВАНИЕ</title>
		<!-- [1..1] Наполнение секции -->
		<text>
			<paragraph><caption>Сведения о получении образования</caption>
				{code4100}
			</paragraph>
		</text>
		<!-- [1..1] Кодирование ... Сведения о получении образования -->
		<entry>
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] -->
				<code code="4100" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Сведения о получении образования"/>
				<!-- R [1..1] -->
				<value xsi:type="ST">
					{code4100}
				</value>
			</observation>
		</entry>
	</section>
</component>
<!-- R [1..1] СЕКЦИЯ: АНАМНЕЗ  -->
<component>
	<section>
		<!-- R [1..1] код секции -->
		<code code="SOCANAM" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5" codeSystemName="Секции CDA документов" displayName="Социальный анамнез"/>
		<!-- R [1..1] заголовок секции -->
		<title>АНАМНЕЗ</title>
		<!-- R [1..1] наполнение секции -->
		<text>

			<paragraph><caption>Наблюдается в организациях, оказывающих лечебно-профилактическую помощь</caption>
				<content>с {EvnPrescrMse_OrgMedDateYear} года.</content></paragraph>
			<br/>

			<paragraph><caption>Анамнез заболевания</caption>
				<content><![CDATA[{EvnPrescrMse_DiseaseHist}]]></content></paragraph>
			<br/>

			<paragraph><caption>Анамнез жизни</caption>
				<content><![CDATA[{EvnPrescrMse_LifeHist}]]></content></paragraph>
			<br/>

		</text>
		<!-- [0..1] Кодирование ... Год, с которого наблюдается в медицинской организации -->
		<?php if (!empty($EvnPrescrMse_OrgMedDate)) { ?>
		<entry>                    
			<observation classCode="OBS" moodCode="EVN">
				<code code="4101" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Год, с которого наблюдается в медицинской организации"/>
				<value xsi:type="ST">{EvnPrescrMse_OrgMedDate}</value>
			</observation>
		</entry>

		<?php } ?>
		<!-- [1..1] Кодирование ... Анамнез заболевания -->
		<entry>
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] -->
				<code code="4102" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Анамнез заболевания"/>
				<!-- R [1..1] -->
				<value xsi:type="ST"><![CDATA[{EvnPrescrMse_DiseaseHist}]]></value>
			</observation>
		</entry>
		<!-- [1..1] Кодирование ... Анамнез жизни -->
		<entry>
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] -->
				<code code="4103" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Анамнез жизни"/>
				<!-- R [1..1] -->
				<value xsi:type="ST"><![CDATA[{EvnPrescrMse_LifeHist}]]></value>
			</observation>
		</entry>
		<!-- [0..1] Кодирование ... Физическое развитие (в отношении детей в возрасте до 3 лет) -->
		<!-- [0..1] Кодирование Временная нетрудоспособность -->
		<!-- [0..1] Кодирование ... Инвалидность -->
		<?php if (!empty($InvalidGroup_Code)) { ?>
		<entry>
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] Инвалидность -->
				<code code="{InvalidGroup_Code}" codeSystem="1.2.643.5.1.13.13.11.1053" codeSystemVersion="2.2" codeSystemName="Группы инвалидности" displayName="{InvalidGroup_Name}">
					<!-- [0..1] Уточнение порядка установления инвалидности -->
					<qualifier>
						<!-- R [1..1] Порядок установления инвалидности -->
						<value code="2" codeSystem="1.2.643.5.1.13.13.11.1041" codeSystemVersion="1.1" codeSystemName="Порядок установления инвалидности" displayName="Повторно"/>
					</qualifier>
				</code>
				<!-- R [1..1] Дата\время установления инвалидности -->
				<effectiveTime>
					<!-- [1..1] Дата\время установления инвалидности -->
					<?php if (!empty($EvnMse_SendStickDate)) { ?>
						<low value="{EvnPrescrMse_InvalidDate}"/>
					<?php } else { ?>
						<low nullFlavor="NI"/>
					<?php } ?>
					<!-- [0..1] Дата\время, до которой установлена инвалидность -->
					<?php if (!empty($InvalidGroupType_id) && $InvalidGroupType_id>1 && !empty($EvnPrescrMse_InvalidEndDate)) { ?>
						<high value="{EvnPrescrMse_InvalidEndDate}"/>
					<?php } ?>
				</effectiveTime>
				<!-- R [1..1] Кодирование ... Срок, на который установлена инвалидность -->
				<entryRelationship typeCode="COMP">
					<observation classCode="OBS" moodCode="EVN">
						<code code="4115" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Срок, на который установлена инвалидность"/>
						<!-- R [1..1] Срок, на который установлена степень утраты профессиональной трудоспособности -->
						<value xsi:type="CD" code="{InvalidPeriodType_Code}" codeSystem="1.2.643.5.1.13.13.99.2.358" codeSystemVersion="1.1" codeSystemName="Срок, на который установлена инвалидность" displayName="{InvalidPeriodType_Name}"/>
					</observation>
				</entryRelationship>
				<!-- [0..*] Кодирование ... Степень утраты профессиональной трудоспособности (%)-->
				<?php if (!empty($ProfDisabilityPeriod_Code) && !($InvalidCouseType_Code==4 || $InvalidCouseType_Code==5)) { ?>
				<entryRelationship typeCode="COMP">
					<observation classCode="OBS" moodCode="EVN">
						<code code="4058" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Степень утраты профессиональной трудоспособности (%)"/>
						<!-- R [1..1] Дата, до которой установлена степень утраты профессиональной трудоспособности -->
						<effectiveTime>
							<?php if (!empty($EvnMse_ProfDisabilityEndDate)) { ?>
								<high value="{EvnMse_ProfDisabilityEndDate}"/>
							<?php } else { ?>
								<high nullFlavor="NI"/>
							<?php } ?>
						</effectiveTime>
						<value xsi:type="INT" value="60"/>
						<!-- R [1..1] Кодирование ... Срок, на который установлена степень утраты профессиональной трудоспособности -->
						<entryRelationship typeCode="COMP">
							<observation classCode="OBS" moodCode="EVN">
								<code code="4083" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Срок, на который установлена степень утраты профессиональной трудоспособности"/>
								<!-- R [1..1] Срок, на который установлена степень утраты профессиональной трудоспособности -->
								<value xsi:type="CD" code="{ProfDisabilityPeriod_Code}" codeSystem="1.2.643.5.1.13.13.99.2.325" codeSystemVersion="1.1" codeSystemName="Срок, на который установлена степень утраты профессиональной трудоспособности" displayName="{ProfDisabilityPeriod_Name}"/>
							</observation>
						</entryRelationship>
					</observation>
				</entryRelationship>
				<?php } ?>
				<!-- [0..1] Кодирование ... Причина инвалидности -->
				<entryRelationship typeCode="COMP">
					<act classCode="ACT" moodCode="EVN">
						<!-- R [1..1] Причина инвалидности -->
						<code code="{InvalidCouseType_Code}" codeSystem="1.2.643.5.1.13.13.11.1474" codeSystemVersion="1.2" codeSystemName="Причины инвалидности" displayName="{InvalidCouseType_Name}"/>
					</act>
				</entryRelationship>
				<entryRelationship typeCode="COMP">
					<observation classCode="OBS" moodCode="EVN">
						<code code="4169" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.15" codeSystemName="Кодируемые поля CDA документов" displayName="Период, в течение которого гражданин находился на инвалидности на момент направления на медико-социальную экспертизу"/>
						<!-- R [1..1] Срок, на который установлена степень утраты профессиональной трудоспособности -->
						<value xsi:type="CD" code="{ProfDisabilityPeriodBeforeMSE_Code}" codeSystem="1.2.643.5.1.13.13.11.1490" codeSystemVersion="1.1" codeSystemName="Период, в течение которого гражданин находился на инвалидности на момент направления на медико-социальную экспертизу" displayName="{ProfDisabilityPeriodBeforeMSE_Name}"/>
					</observation>
				</entryRelationship>

			</observation>
		</entry>
		<?php } ?>
		<!-- [0..1] Кодирование ... Номер ИПРА -->
		<?php if (!empty($IPRARegistry_Number)) { ?>
		<entry>
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] -->
				<code code="4104" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Номер ИПРА"/>
				<!-- R [1..1] -->
				<value xsi:type="ST">{IPRARegistry_Number}</value>
			</observation>
		</entry>
		<?php } ?>		
		<!-- [0..1] Кодирование ... Номер протокола проведения медико-социальной экспертизы -->
		<?php if (!empty($PRARegistry_Protocol)) { ?>
		<entry>
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] -->
				<code code="4105" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Номер протокола проведения медико-социальной экспертизы"/>
				<!-- R [1..1] -->
				<value xsi:type="ST">{PRARegistry_Protocol}</value>
			</observation>
		</entry>
		<?php } ?>
		<!-- [0..1] Кодирование ... Дата протокола проведения медико-социальной экспертизы -->
		<?php if (!empty($IPRARegistry_ProtocolDate)) { ?>
		<entry>
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] -->
				<code code="4106" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Дата протокола проведения медико-социальной экспертизы"/>
				<!-- R [1..1] -->
				<value xsi:type="TS" value="{IPRARegistry_ProtocolDate}"/>
			</observation>
		</entry>
		<?php } ?>
		<!-- [0..1] Кодирование ... Результаты и эффективность проведенных мероприятий медицинской реабилитации, рекомендованных индивидуальной программой реабилитации или абилитации инвалида (ребенка-инвалида) (ИПРА) (текстовое описание) -->
		<?php if (!empty($MeasuresRehabEffect_Comment)) { ?>		
		<entry>
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] -->
				<code code="4107" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Результаты и эффективность проведенных мероприятий медицинской реабилитации, рекомендованных индивидуальной программой реабилитации или абилитации инвалида (ребенка-инвалида) (ИПРА) (текстовое описание)"/>
				<!-- R [1..1] -->
				<value xsi:type="ST">{MeasuresRehabEffect_Comment}</value>
			</observation>
		</entry>
		<?php } ?>
		<!-- [0..1] кодирование ... Результаты и эффективность проведенных мероприятий медицинской реабилитации -->
	</section>
</component>
<!-- R [1..1] СЕКЦИЯ: ВИТАЛЬНЫЕ ПАРАМЕТРЫ  -->
<component>
	<section>
		<!-- R [1..1] код секции -->
		<code code="VITALPARAM" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5" codeSystemName="Секции CDA документов" displayName="Витальные параметры"/>
		<!-- R [1..1] заголовок секции -->
		<title>АНТРОПОМЕТРИЧЕСКИЕ ДАННЫЕ И ФИЗИОЛОГИЧЕСКИЕ ПАРАМЕТРЫ</title>
		<text>
			<table width="100%">
				<col width="30%"/>
				<col width="70%"/>
				<tbody>
				<?php if (!empty($PersonWeight_Weight)) { ?>
				<tr>
					<td>Масса тела</td>
					<td><content ID="vv1_1">"{PersonWeight_Weight}" кг</content></td>
				</tr>
				<?php } ?>
				<?php if (!empty($PersonHeight_HeightM)) { ?>
				<tr>
					<td>Рост</td>
					<td><content ID="vv1_2">"{PersonHeight_HeightM}" м</content></td>
				</tr>
				<?php } ?>
				<?php if (!empty($Person_IMT)) { ?>
				<tr>
					<td>ИМТ</td>
					<td><content ID="vv1_3">{Person_IMT}</content></td>
				</tr>
				<?php } ?>
				<?php if (!empty($PhysiqueType_Name)) { ?>
				<tr>
					<td>Телосложение</td>
					<td><content ID="vv1_4">{PhysiqueType_Name}</content></td>
				</tr>
				<?php } ?>
				<?php if (!empty($EvnPrescrMse_DailyPhysicDepartures)) { ?>
				<tr>
					<td>Суточный объём физиологических отправлений</td>
					<td><content ID="vv1_5">{EvnPrescrMse_DailyPhysicDepartures} мл</content></td>
				</tr>
				<?php } ?>
				<?php if (!empty($EvnPrescrMse_Waist) && !empty($EvnPrescrMse_Hips)) { ?>
				<tr>
					<td>Объём талии/бёдер</td>
					<td><content ID="vv1_6">"{EvnPrescrMse_Waist}" см</content>/<content ID="vv1_7">"{EvnPrescrMse_Hips}" см</content></td>
				</tr>
				<?php } ?>
</tbody>
</table>
</text>
<!-- R [3..*] Кодирование витальных параметров -->
<?php if (!empty($PersonWeight_WeightGr)) { ?>
<entry>
	<organizer classCode="CLUSTER" moodCode="EVN">
		<!-- R [1..1] Статус измерения - выполнено -->
		<statusCode code="completed"/>
		<!-- R [1..1] Дата измерения -->
		<effectiveTime value="{EvnPrescrMse_setDT}"/>
		<!-- R [1..2] Кодирование витального параметра -->
		<component typeCode="COMP">
			<!-- R [1..1] Кодирование массы тела -->
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] Тип витального параметра -->
				<code code="50" codeSystem="1.2.643.5.1.13.13.99.2.262" codeSystemVersion="1.3" codeSystemName="Витальные параметры" displayName="Масса тела">
					<!-- [0..1] Ссылка на фрагмент текстовой части секции -->
					<originalText><reference value="#vv1_1"/></originalText>
				</code>
				<!-- R [1..1] Масса тела -->
				<value xsi:type="PQ" value="{PersonWeight_WeightGr}" unit="гр."/>
			</observation>
		</component>
	</organizer>
</entry>
<?php } ?>
<?php if (!empty($PersonHeight_Height)) { ?>
<entry>
	<organizer classCode="CLUSTER" moodCode="EVN">
		<!-- R [1..1] Статус измерения - выполнено -->
		<statusCode code="completed"/>
		<!-- R [1..1] Дата измерения -->
		<effectiveTime value="{EvnPrescrMse_setDT}"/>
		<!-- R [1..2] Кодирование витального параметра -->
		<component typeCode="COMP">
			<!-- R [1..1] Кодирование длины тела -->
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] Тип витального параметра -->
				<code code="51" codeSystem="1.2.643.5.1.13.13.99.2.262" codeSystemVersion="1.3" codeSystemName="Витальные параметры" displayName="Длина тела">
					<!-- [0..1] Ссылка на фрагмент текстовой части секции -->
					<originalText><reference value="#vv1_2"/></originalText>
				</code>
				<!-- R [1..1] Длина тела -->
				<value xsi:type="PQ" value="{PersonHeight_Height}" unit="см"/>
			</observation>
		</component>
	</organizer>
</entry>
<?php } ?>
<?php if (!empty($Person_IMT)) { ?>
<entry>
	<organizer classCode="CLUSTER" moodCode="EVN">
		<!-- R [1..1] Статус измерения - выполнено -->
		<statusCode code="completed"/>
		<!-- R [1..1] Дата измерения -->
		<effectiveTime value="{EvnPrescrMse_setDT}"/>
		<!-- R [1..2] Кодирование витального параметра -->
		<component typeCode="COMP">
			<!-- R [1..1] Кодирование индекса массы тела -->
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] Тип витального параметра -->
				<code code="10" codeSystem="1.2.643.5.1.13.13.99.2.262" codeSystemVersion="1.3" codeSystemName="Витальные параметры" displayName="Индекс массы тела">
					<!-- [0..1] Ссылка на фрагмент текстовой части секции -->
					<originalText><reference value="#vv1_3"/></originalText>
				</code>
				<!-- R [1..1] Индекс массы тела -->
				<value xsi:type="REAL" value="{Person_IMT}"/>
			</observation>
		</component>
	</organizer>
</entry>
<?php } ?>
<?php if (!empty($PhysiqueType_Name)) { ?>
<!-- [1..1] Кодирование ... Телосложение -->
<entry>
	<observation classCode="OBS" moodCode="EVN">
		<!-- R [1..1] -->
		<code code="4108" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Телосложение"/>
		<!-- R [1..1] -->
		<value xsi:type="CD" code="{PhysiqueType_Code}" codeSystem="1.2.643.5.1.13.13.11.1492"  codeSystemVersion="1.1"  codeSystemName="Типы телосложения" displayName="{PhysiqueType_Name}"/>
	</observation>
</entry>
<?php } ?>
<?php if (!empty($EvnPrescrMse_DailyPhysicDepartures)) { ?>
<entry>
	<organizer classCode="CLUSTER" moodCode="EVN">
		<!-- R [1..1] Статус измерения - выполнено -->
		<statusCode code="completed"/>
		<!-- R [1..1] Дата измерения -->
		<effectiveTime value="{EvnPrescrMse_setDT}"/>
		<!-- R [1..2] Кодирование витального параметра -->
		<component typeCode="COMP">
			<!-- R [1..1] Кодирование суточного объёма физиологических отправлений -->
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] Тип витального параметра -->
				<code code="56" codeSystem="1.2.643.5.1.13.13.99.2.262" codeSystemVersion="1.3" codeSystemName="Витальные параметры" displayName="Суточный объём физиологических отправлений">
					<!-- [0..1] Ссылка на фрагмент текстовой части секции -->
					<originalText><reference value="#vv1_5"/></originalText>
				</code>
				<!-- R [1..1] Длина тела -->
				<value xsi:type="PQ" value="{EvnPrescrMse_DailyPhysicDepartures}" unit="мл"/>
			</observation>
		</component>
	</organizer>
</entry>
<?php } ?>
<?php if (!empty($EvnPrescrMse_Waist)) { ?>
<entry>
	<organizer classCode="CLUSTER" moodCode="EVN">
		<!-- R [1..1] Статус измерения - выполнено -->
		<statusCode code="completed"/>
		<!-- R [1..1] Дата измерения -->
		<effectiveTime value="{EvnPrescrMse_setDT}"/>
		<!-- R [1..2] Кодирование витального параметра -->
		<component typeCode="COMP">
			<!-- R [1..1] Кодирование окружности талии -->
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] Тип витального параметра -->
				<code code="54" codeSystem="1.2.643.5.1.13.13.99.2.262" codeSystemVersion="1.3" codeSystemName="Витальные параметры" displayName="Окружность талии">
					<!-- [0..1] Ссылка на фрагмент текстовой части секции -->
					<originalText><reference value="#vv1_6"/></originalText>
				</code>
				<!-- R [1..1] Длина тела -->
				<value xsi:type="PQ" value="{EvnPrescrMse_Waist}" unit="см"/>
			</observation>
		</component>
	</organizer>
</entry>
<?php } ?>
<?php if (!empty($EvnPrescrMse_Hips)) { ?>
<entry>
	<organizer classCode="CLUSTER" moodCode="EVN">
		<!-- R [1..1] Статус измерения - выполнено -->
		<statusCode code="completed"/>
		<!-- R [1..1] Дата измерения -->
		<effectiveTime value="{EvnPrescrMse_setDT}"/>
		<!-- R [1..2] Кодирование витального параметра -->
		<component typeCode="COMP">
			<!-- R [1..1] Кодирование окружности бёдер -->
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] Тип витального параметра -->
				<code code="55" codeSystem="1.2.643.5.1.13.13.99.2.262" codeSystemVersion="1.3" codeSystemName="Витальные параметры" displayName="Окружность бёдер">
					<!-- [0..1] Ссылка на фрагмент текстовой части секции -->
					<originalText><reference value="#vv1_7"/></originalText>
				</code>
				<!-- R [1..1] Длина тела -->
				<value xsi:type="PQ" value="{EvnPrescrMse_Hips}" unit="см"/>
			</observation>
		</component>
	</organizer>
</entry>
<?php } ?>
</section>
</component>
<!-- R [1..1] СЕКЦИЯ: СОСТОЯНИЕ ПРИ НАПРАВЛЕНИИ  -->
<?php if (!empty($EvnPrescrMse_State)) { ?>
<component>
	<section>
		<!-- R [1..1] код секции -->
		<code code="STATECUR" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5" codeSystemName="Секции CDA документов" displayName="Текущее состояние"/>
		<!-- R [1..1] заголовок секции -->
		<title>СОСТОЯНИЕ ПРИ НАПРАВЛЕНИИ</title>
		<!-- R [1..1] наполнение секции -->
		<text>
			<paragraph>
				<caption>Состояние здоровья гражданина при направлении на медико-социальную экспертизу</caption>
				<content><![CDATA[{EvnPrescrMse_State}]]></content>
			</paragraph>
		</text>
		<!-- [1..1] Кодирование ... Состояние здоровья гражданина при направлении на медико-социальную экспертизу -->
		<entry>
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] -->
				<code code="4109" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Состояние здоровья гражданина при направлении на медико-социальную экспертизу"/>
				<!-- R [1..1] -->
				<value xsi:type="ST"><![CDATA[{EvnPrescrMse_State}]]></value>
			</observation>
		</entry>
	</section>
</component>
<?php } ?>
<!-- [1..1] СЕКЦИЯ: ДИАГНОСТИЧЕСКИЕ ИССЛЕДОВАНИЯ И КОНСУЛЬТАЦИИ  -->
<component>
	<section>
		<!-- R [1..1] код секции -->
		<code code="PROC" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5" codeSystemName="Секции CDA документов" displayName="Исследования и процедуры"/>
		<!-- R [1..1] заголовок секции -->
		<title>МЕДИЦИНСКИЕ ОБСЛЕДОВАНИЯ</title>
		<text>
			<paragraph>
				<content>нет данных.</content>
			</paragraph>
		</text>
		<!-- [1..1] Кодирование ... Сведения о медицинских обследованиях, необходимых для получения клинико-функциональных данных в зависимости от заболевания при проведении медико-социальной экспертизы -->
		<entry>
			<observation classCode="OBS" moodCode="EVN">
				<!-- R [1..1] -->
				<code code="4110" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Сведения о медицинских обследованиях, необходимых для получения клинико-функциональных данных в зависимости от заболевания при проведении медико-социальной экспертизы"/>
				<!-- R [1..1] -->
				<value xsi:type="ST">{UslugaComplex_list}</value>
			</observation>
		</entry>
		<!-- [0..1] СЕКЦИЯ: Результаты  инструментальных исследований  -->
		<!-- [0..1] СЕКЦИЯ: Результаты лабораторных исследований  -->
		<!-- [0..1] СЕКЦИЯ: Консультации врачей специалистов  -->
	</section>
</component>
<!-- R [1..1] СЕКЦИЯ: ДИАГНОЗЫ  -->
<component>
	<section>
		<!-- R [1..1] Код секции -->
		<code code="DGN" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5"  codeSystemName="Секции CDA документов" displayName="Диагнозы"/>
		<!-- R [1..1] Заголовок секции -->
		<title>ДИАГНОЗЫ</title>
		<!-- R [1..1] наполнение секции -->
		<text>
			<table>
				<caption>Диагноз при направлении на медико-социальную экспертизу:</caption>
				<tbody>
				<tr>
					<th>Шифр</th>
					<th>Тип</th>
					<th>Текст</th>
				</tr>
				<tr>
					<td>"{Diag_Code}"</td>
					<td>Основное заболевание</td>
					<td>"{Diag_Name}"</td>
				</tr>
				</tbody>
			</table>
		</text>
		<!-- R [1..1] Кодирование диагноза при направлении на медико-социальную экспертизу -->
		<entry>
			<act classCode="ACT" moodCode="EVN">
				<!-- R [1..1] Степень обоснованности диагноза -->
				<code code="3" codeSystem="1.2.643.5.1.13.13.11.1076" codeSystemVersion="1.2" codeSystemName="Степень обоснованности диагноза" displayName="Заключительный клинический диагноз"/>
				<!-- R [1..*] Кодирование основного заключительного диагноза -->
				<entryRelationship typeCode="COMP">
					<observation classCode="OBS" moodCode="EVN">
						<!-- R [1..1] Кодирование вида нозологической единицы диагноза -->
						<code code="1" codeSystem="1.2.643.5.1.13.13.11.1077" codeSystemVersion="1.2" displayName="Основное заболевание" codeSystemName="Виды нозологических единиц диагноза"/>
						<!-- [0..1] Врачебное описание нозологической единицы -->
						<text>"{Diag_Name}"</text>
						<statusCode code="completed"/>
						<!-- R [1..1] Даты диагноза -->
						<effectiveTime>
							<!-- [1..1] Дата выявления диагноза -->
							<low value="{EvnPrescrMse_setDT}"/>
							<!-- [0..1] Дата закрытия диагноза -->
						</effectiveTime>
						<!-- R [1..1] Основное заболевание -->
						<value xsi:type="CD" code="{Diag_Code}" codeSystem="1.2.643.5.1.13.13.11.1005"  codeSystemVersion="2.5"  codeSystemName="Международная классификация болезней и состояний, связанных со здоровьем 10 пересмотра. Версия 4" displayName="{Diag_Name}"/>
					</observation>
				</entryRelationship>
				<!-- [0..*] Кодирование осложнения заключительного диагноза -->
				<?php foreach($concomitant_and_complications['complicationsFinalDisease'] as $line) { ?>
				<entryRelationship typeCode="COMP">
					<observation classCode="OBS" moodCode="EVN">
						<!-- R [1..1] Кодирование вида нозологической единицы диагноза -->
						<code code="2" codeSystem="1.2.643.5.1.13.13.11.1077" codeSystemVersion="1.3" displayName="Осложнение основного заболевания" codeSystemName="Виды нозологических единиц диагноза"/>
						<!-- [0..1] Врачебное описание нозологической единицы -->
						<text><?php echo $line['Diag_Name']; ?></text>
						<statusCode code="completed"/>
						<!-- R [1..1] Сопутствующая патология -->
						<value xsi:type="CD" code="<?php echo $line['Diag_Code']; ?>" codeSystem="1.2.643.5.1.13.13.11.1005"  codeSystemVersion="2.5"  codeSystemName="Международная классификация болезней и состояний, связанных со здоровьем 10 пересмотра. Версия 4" displayName="<?php echo $line['Diag_Name']; ?>"/>
					</observation>
				</entryRelationship>
				<?php } ?>
				<!-- [0..*] Кодирование сопутствующей патологии заключительного диагноза -->
				<?php foreach($concomitant_and_complications['accompanyingIllnesses'] as $line) { ?>
				<entryRelationship typeCode="COMP">
					<observation classCode="OBS" moodCode="EVN">
						<!-- R [1..1] Кодирование вида нозологической единицы диагноза -->
						<code code="3" codeSystem="1.2.643.5.1.13.13.11.1077" codeSystemVersion="1.3" displayName="Сопутствующее заболевание" codeSystemName="Виды нозологических единиц диагноза"/>
						<!-- [0..1] Врачебное описание нозологической единицы -->
						<text><?php echo $line['Diag_Name']; ?></text>
						<statusCode code="completed"/>
						<!-- R [1..1] Сопутствующая патология -->
						<value xsi:type="CD" code="<?php echo $line['Diag_Code']; ?>" codeSystem="1.2.643.5.1.13.13.11.1005"  codeSystemVersion="2.5"  codeSystemName="Международная классификация болезней и состояний, связанных со здоровьем 10 пересмотра. Версия 4" displayName="<?php echo $line['Diag_Name']; ?>"/>
					</observation>
				</entryRelationship>
					<?php if(empty($line['complicationsOfConcomitantDisease'])) $line['complicationsOfConcomitantDisease'] = []; ?>
					<!-- [0..*] Кодирование осложнения сопутствующих заболеваний -->
					<?php foreach($line['complicationsOfConcomitantDisease'] as $value) { ?>
					<entryRelationship typeCode="COMP">
						<observation classCode="OBS" moodCode="EVN">
							<!-- R [1..1] Кодирование вида нозологической единицы диагноза -->
							<code code="7" codeSystem="1.2.643.5.1.13.13.11.1077" codeSystemVersion="1.3" displayName="Осложнение сопутствующего заболевания" codeSystemName="Виды нозологических единиц диагноза"/>
							<!-- [0..1] Врачебное описание нозологической единицы -->
							<text><?php echo $value['Diag_Name']; ?></text>
							<statusCode code="completed"/>
							<!-- R [1..1] Сопутствующая патология -->
							<value xsi:type="CD" code="<?php echo $value['Diag_Code']; ?>" codeSystem="1.2.643.5.1.13.13.11.1005"  codeSystemVersion="2.5"  codeSystemName="Международная классификация болезней и состояний, связанных со здоровьем 10 пересмотра. Версия 4" displayName="<?php echo $value['Diag_Name']; ?>"/>
						</observation>
					</entryRelationship>
				<?php }} ?>
			</act>
		</entry>
	</section>
</component>
<!-- [1..1] СЕКЦИЯ: ОБЪЕКТИВИЗИРОВАННАЯ ОЦЕНКА СОСТОЯНИЯ  -->
<component>
	<section>
		<!-- R [1..1] код секции -->
		<code code="SCORES" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5" codeSystemName="Секции CDA документов" displayName="Объективизированная оценка состояния больного"/>
		<!-- R [1..1] заголовок секции -->
		<title>ОБЪЕКТИВИЗИРОВАННАЯ ОЦЕНКА СОСТОЯНИЯ</title>
		<!-- [1..1] наполнение секции -->
		<text>
			<table>
				<thead>
				<tr>
					<th>Тип оценки</th>
					<th>Результат</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>Клинический прогноз</td>
					<td><content ID="ob_1">{ClinicalForecastType_Name}</content></td>
				</tr>
				<tr>
					<td>Реабилитационный потенциал</td>
					<td><content ID="ob_2">{ClinicalPotentialType_Name}</content></td>
				</tr>
				<tr>
					<td>Реабилитационный прогноз</td>
					<td><content ID="ob_3">{ClinicalForecastType_dName}</content></td>
				</tr>
				</tbody>
			</table>
		</text>
		<!-- [0..*] кодирование ... Объективизированная оценка -->
	</section>
</component>
<!-- [1..1] СЕКЦИЯ: РЕКОМЕНДАЦИИ  -->
<component>
	<section>
		<!-- R [1..1] код секции -->
		<code code="REGIME" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5" codeSystemName="Секции CDA документов" displayName="Режим и рекомендации"/>
		<!-- R [1..1] заголовок секции -->
		<title>РЕКОМЕНДАЦИИ</title>
		<!-- R [1..1] СЕКЦИЯ: Рекомендованное лечение   -->
		<component>
			<section>
				<!-- R [1..1] код секции -->
				<code code="RECTREAT" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5"  codeSystemName="Секции электронных медицинских документов" displayName="Рекомендованное лечение"/>
				<!-- R [1..1] заголовок секции -->
				<title>Рекомендованное лечение</title>
				<!-- [1..1] наполнение секции -->
				<text>
					<paragraph><caption>Рекомендуемые мероприятия по реконструктивной хирургии</caption>{EvnPrescrMse_MeasureSurgery}</paragraph>
					<paragraph><caption>Рекомендуемые мероприятия по протезированию и ортезированию</caption>{EvnPrescrMse_MeasureProstheticsOrthotics}</paragraph>
					<paragraph><caption>Санаторно-курортное лечение</caption>{EvnPrescrMse_HealthResortTreatment}</paragraph>
				</text>
				<!-- [1..1] Рекомендуемые мероприятия по реконструктивной хирургии -->
				<entry>
					<observation classCode="OBS" moodCode="EVN">
						<!-- R [1..1] -->
						<code code="4111" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Рекомендуемые мероприятия по реконструктивной хирургии"/>
						<!-- R [1..1] -->
						<value xsi:type="ST">{EvnPrescrMse_MeasureSurgery}</value>
					</observation>
				</entry>
				<!-- [1..1] Рекомендуемые мероприятия по протезированию и ортезированию -->
				<entry>
					<observation classCode="OBS" moodCode="EVN">
						<!-- R [1..1] -->
						<code code="4112" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Рекомендуемые мероприятия по протезированию и ортезированию"/>
						<!-- R [1..1] -->
						<value xsi:type="ST">{EvnPrescrMse_MeasureProstheticsOrthotics}</value>
					</observation>
				</entry>
				<!-- [1..1] Санаторно-курортное лечение -->
				<entry>
					<observation classCode="OBS" moodCode="EVN">
						<!-- R [1..1] -->
						<code code="4113" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Санаторно-курортное лечение"/>
						<!-- R [1..1] -->
						<value xsi:type="ST">{EvnPrescrMse_HealthResortTreatment}</value>
					</observation>
				</entry>
			</section>
		</component>
		<!-- [0..1] СЕКЦИЯ: Прочие рекомендации  -->
		<component>
			<section>
				<!-- R [1..1] код секции -->
				<code code="RECOTHER" codeSystem="1.2.643.5.1.13.13.99.2.197" codeSystemVersion="1.5" codeSystemName="Секции CDA документов" displayName="Прочие рекомендации"/>
				<!-- R [1..1] заголовок секции -->
				<title>Прочие рекомендации</title>
				<!-- [1..1] наполнение секции -->
				<text>
				<paragraph><caption>Рекомендуемые мероприятия по медицинской реабилитации</caption>{EvnPrescrMse_Recomm}</paragraph>
				</text>
				<!-- [1..1] Рекомендуемые мероприятия по медицинской реабилитации -->
				<entry>
					<observation classCode="OBS" moodCode="EVN">
						<!-- R [1..1] -->
						<code code="4114" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.12" codeSystemName="Кодируемые поля CDA документов" displayName="Рекомендуемые мероприятия по медицинской реабилитации"/>
						<!-- R [1..1] -->
						<value xsi:type="ST">{EvnPrescrMse_Recomm}</value>
					</observation>
				</entry>
			</section>
		</component>
	</section>
</component>
</structuredBody>
</component>
</ClinicalDocument>