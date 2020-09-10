<ClinicalDocument xmlns="urn:hl7-org:v3" xsi:schemaLocation="urn:hl7-org:v3 CDA.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<!-- ЗАГОЛОВОК ДОКУМЕНТА "Медицинское свидетельство о смерти" -->
	<!-- R [1..1] Область применения документа (Страна) -->
	<realmCode code="RU"/>
	<!-- R [1..1] Указатель на использование CDA R2 -->
	<typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>
	<!-- R [1..1] Идентификатор Шаблона документа "Медицинское свидетельство о смерти третий уровень формализации" -->
	<templateId root="1.2.643.5.1.13.2.7.5.1.13.3"/>
	<!-- R [1..1] Уникальный идентификатор документа -->
	<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.51 extension = идентификатор документа -->
	<id root="{PassportToken_tid}.100.1.1.51" extension="123789"/>
	<!-- R [1..1] Тип документа -->
	<code code="13" codeSystem="1.2.643.5.1.13.13.11.1115" codeSystemVersion="2.4" codeSystemName="Система электронных медицинских документов" displayName="Медицинское свидетельство о смерти"/>
	<!-- R [1..1] Заголовок документа -->
	<title>МЕДИЦИНСКОЕ СВИДЕТЕЛЬСТВО О СМЕРТИ</title>
	<!-- R [1..1] Дата создания документа (с точностью до дня)-->
	<effectiveTime value="{DeathSvid_GiveDate}"/>
	<!-- R [1..1] Уровень конфиденциальности документа -->
	<confidentialityCode code="N" codeSystem="1.2.643.5.1.13.13.11.1116" codeSystemVersion="1.1" codeSystemName="Уровень конфиденциальности документа" displayName="Обычный"/>
	<!-- R [1..1] Язык документа -->
	<languageCode code="ru-RU"/>
	<!-- R [1..1] Уникальный идентификатор набора версий документа -->
	<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.50 extension = идентификатор набора версий документа -->
	<setId root="{PassportToken_tid}.100.1.1.50" extension="{DeathSvid_id}"/>
	<!-- R [1..1] Номер версии данного документа -->
	<versionNumber value="2"/>
	<!-- R [1..1] ДАННЫЕ ОБ УМЕРШЕМ -->
	<recordTarget>
		<!-- R [1..1] УМЕРШИЙ (роль) -->
		<patientRole>
			<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.10 extension = идентификатор пациента -->
			<id root="{PassportToken_tid}.100.1.1.10" extension="{Person_id}"/>
			<!-- [1..1] СНИЛС умершего -->
			<id root="1.2.643.100.3" extension="{Person_Snils}"/>
			<!-- [1..1] Адрес постоянной регистрации умершего -->
			<addr use="H">
				<!-- [1..1] адрес текстом -->
				<streetAddressLine>{Address_Address}</streetAddressLine>
				<!--[1..1] Регион РФ (республика, край, область) -->
				<state>{KLRgn_id}</state>
				<!--[0..1] Район -->
				<precinct>{KLSubRgn_Name}</precinct>
				<!-- [0..1] Город \ Село -->
				<city>{KLCity_Name}</city>
				<!-- [0..1] Улица -->
				<streetName>{KLStreet_Name}</streetName>
				<!-- [0..1] Дом -->
				<houseNumber>{Address_House}</houseNumber>
				<!-- [0..1] Квартира -->
				<unitID>{Address_Flat}</unitID>
			</addr>
			<!-- R [1..1] УМЕРШИЙ (человек) -->
			<patient>
				<!-- [0..1] ФИО умершего -->
				<name>
					<!-- R [1..1] Фамилия -->
					<family>{Person_SurName}</family>
					<!-- [1..1] Имя -->
					<given>{Person_FirName}</given>
					<!-- [1..1] Отчество -->
					<given>{Person_SecName}</given>
				</name>
				<!-- [1..1] Пол умершего -->
				<!--Пол: Женский-->
				<administrativeGenderCode code="{Sex_Code}" codeSystem="1.2.643.5.1.13.13.11.1040" codeSystemVersion="2.1" codeSystemName="Пол пациента" displayName="{Sex_Name}"/>
				<!-- [1..1] Дата рождения умершего-->
				<birthTime value="{Person_BirthDay}"/>
			</patient>
			<!--  R [1..1] Организация (ЛПУ или его филиал, индивидуальный предприниматель, осуществляющий медицинскую деятельность), оказавшая медицинскую помощь -->
			<providerOrganization>
				<!-- [1..1] Идентификатор организации -->
				<!-- организации - по справочнику «Реестр медицинских организаций Российской Федерации» (OID: 1.2.643.5.1.13.13.11.1461) -->
				<!-- индивидуальные предприниматели - указание на отсутствие кода, nullFlavor="OTH" -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование организации \ ФИО Индивидуального предпринимателя -->
				<name>{Lpu_Nick}</name>
				<!-- [0..1] Телефон организации -->
				<telecom value="tel:{Lpu_Phone}" use="WP"/>
				<!-- [0..*] Прочие контакты организации (веб-сайт) -->
				<telecom value="{Lpu_Www}"/>
				<!-- R [1..1] Адрес организации\Индивидуального предпринимателя-->
				<addr>
					<!-- R [1..1] адрес текстом -->
					<streetAddressLine>{LAddress_Address}</streetAddressLine>
					<!-- R [1..1] Регион РФ -->
					<state>{LKLRgn_id}</state>
				</addr>
			</providerOrganization>
		</patientRole>
	</recordTarget>
	<!-- R [1..1] ДАННЫЕ ОБ АВТОРЕ ДОКУМЕНТА -->
	<author>
		<!-- R [1..1] Дата подписи документа автором-->
		<time value="{DeathSvid_GiveDate}"/>
		<!-- R [1..1] АВТОР (роль) -->
		<assignedAuthor>
			<!-- R [1..1] Уникальный идентификатор автора в МИС -->
			<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.70 extension = идентификатор персонала -->
			<id root="{PassportToken_tid}.100.1.1.70" extension="{MedPersonal_id}"/>
			<!-- [0..1] СНИЛС автора -->
			<id root="1.2.643.100.3" extension="{MedPersonal_Snils}"/>
			<!-- R [1..1] Код должности автора-->
			<code code="{MedPost_Code}" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemName="Должности работников организаций медицинского и фармацевтического профиля" displayName="{MedPost_Name}"/>
			<!-- [0..1] Адрес автора документа -->
			<addr>
				<!-- R [1..1] адрес текстом -->
				<streetAddressLine>{MAddress_Address}</streetAddressLine>
				<!-- R [1..1] Регион РФ -->
				<state>{MKLRgn_id}</state>
			</addr>
			<!-- [0..1] Телефон автора -->
			<telecom value="tel:{MedPersonal_Phone}"/>
			<!-- R [1..1] АВТОР (человек) -->
			<assignedPerson>
				<!-- R [1..1] Фамилия, Имя, Отчество автора -->
				<name>
					<!-- R [1..1] Фамилия автора-->
					<family>{MedPersonal_SurName}</family>
					<!-- R [1..1] Имя автора-->
					<given>{MedPersonal_FirName}</given>
					<!-- [1..1] Отчество автора-->
					<given>{MedPersonal_SecName}</given>
				</name>
			</assignedPerson>
			<!-- [0..1] Место работы автора  -->
			<representedOrganization>
				<!-- R [1..1] Идентификатор организации -->
				<!-- организации - по справочнику «Реестр медицинских организаций Российской Федерации» (OID: 1.2.643.5.1.13.13.11.1461) -->
				<!-- индивидуальные предприниматели - указание на отсутствие кода, nullFlavor="OTH" -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование организации \ ФИО Индивидуального предпринимателя -->
				<name>{Lpu_Nick}</name>
				<!-- [0..1] Телефон организации -->
				<telecom value="tel:{Lpu_Phone}" use="WP"/>
				<!-- [0..*] Прочие контакты организации (веб-сайт) -->
				<telecom value="{Lpu_Www}"/>
				<!-- [1..1] Адрес организации\Индивидуального предпринимателя-->
				<addr>
					<!-- R [1..1] адрес текстом -->
					<streetAddressLine>{LAddress_Address}</streetAddressLine>
					<!-- R [1..1] Регион РФ -->
					<state>{LKLRgn_id}</state>
				</addr>
			</representedOrganization>
		</assignedAuthor>
	</author>
	<!-- R [1..1] ДАННЫЕ ОБ ОРГАНИЗАЦИИ-ВЛАДЕЛЬЦЕ ДОКУМЕНТА -->
	<custodian>
		<!-- R [1..1] Организация-владелец документа (роль) -->
		<assignedCustodian>
			<!-- R [1..1] Организация-владелец документа (организация) -->
			<representedCustodianOrganization>
				<!-- [1..1] Идентификатор организации -->
				<!-- организации - по справочнику «Реестр медицинских организаций Российской Федерации» (OID: 1.2.643.5.1.13.13.11.1461) -->
				<!-- индивидуальные предприниматели - указание на отсутствие кода, nullFlavor="OTH" -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование организации \ ФИО Индивидуального предпринимателя -->
				<name>{Lpu_Nick}</name>
				<!-- [0..1] Телефон организации -->
				<telecom value="tel:{Lpu_Phone}" use="WP"/>
				<!-- [1..1] Адрес организации\Индивидуального предпринимателя-->
				<addr>
					<!-- R [1..1] адрес текстом -->
					<streetAddressLine>{LAddress_Address}</streetAddressLine>
					<!-- R [1..1] Регион РФ -->
					<state>{LKLRgn_id}</state>
				</addr>
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
	<!-- R [1..1] ДАННЫЕ О ЛИЦЕ, ПРИДАВШЕМ ЮРИДИЧЕСКУЮ СИЛУ ДОКУМЕНТУ (Руководитель медицинской организации или Индивидуальный предприниматель, осуществляющий медицинскую деятельность) -->
	<legalAuthenticator>
		<!-- R [1..1] Дата подписи документа лицом, придавшем юридическую силу документу -->
		<time value="{DeathSvid_GiveDate}"/>
		<!-- R [1..1] Факт наличия подписи на документе -->
		<signatureCode code="S"/>
		<!-- R [1..1] Лицо, придавшен юридическую силу документу (роль) -->
		<assignedEntity>
			<!-- R [1..1] Уникальный идентификатор лица, придавшего юридическую силу документу -->
			<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.70 extension = идентификатор персонала -->
			<id root="{PassportToken_tid}.100.1.1.70" extension="{MedPersonal_id}"/>
			<!-- [0..1] СНИЛС лица, придавшего юридическую силу документу -->
			<id root="1.2.643.100.3" extension="{MedPersonal_Snils}"/>
			<!-- R [1..1] Код должности лица, придавшего юридическую силу документу -->
			<code code="{MedPost_Code}" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemName="Должности работников организаций медицинского и фармацевтического профиля" displayName="{MedPost_Name}"/>
			<!-- [0..1] Адрес лица, придавшего юридическую силу документу -->
			<addr>
				<!-- R [1..1] адрес текстом -->
				<streetAddressLine>{MAddress_Address}</streetAddressLine>
				<!-- R [1..1] Регион РФ -->
				<state>{MKLRgn_id}</state>
			</addr>
			<!-- [0..1] Телефон лица, придавшего юридическую силу документу -->
			<telecom value="tel:{MedPersonal_Phone}"/>
			<!-- R [1..1] Лицо, придавшен юридическую силу документу (человек) -->
			<assignedPerson>
				<!-- R [1..1] Фамилия, Имя, Отчество лица, придавшего юридическую силу документу -->
				<name>
					<!-- R [1..1] Фамилия -->
					<family>{MedPersonal_SurName}</family>
					<!-- R [1..1] Имя -->
					<given>{MedPersonal_FirName}</given>
					<!-- [1..1] Отчество -->
					<given>{MedPersonal_SecName}</given>
				</name>
			</assignedPerson>
			<!-- [0..1] Место работы лица, придавшего юридическую силу документу -->
			<representedOrganization>
				<!-- R [1..1] Идентификатор организации -->
				<!-- организации - по справочнику «Реестр медицинских организаций Российской Федерации» (OID: 1.2.643.5.1.13.13.11.1461) -->
				<!-- индивидуальные предприниматели - указание на отсутствие кода, nullFlavor="OTH" -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование организации \ ФИО Индивидуального предпринимателя -->
				<name>{Lpu_Nick}</name>
				<!-- [0..1] Телефон организации -->
				<telecom value="tel:{Lpu_Phone}" use="WP"/>
				<!-- [0..*] Прочие контакты организации (веб-сайт) -->
				<telecom value="{Lpu_Www}"/>
				<!-- [1..1] Адрес организации\Индивидуального предпринимателя-->
				<addr>
					<!-- R [1..1] адрес текстом -->
					<streetAddressLine>{LAddress_Address}</streetAddressLine>
					<!-- R [1..1] Регион РФ -->
					<state>{LKLRgn_id}</state>
				</addr>
			</representedOrganization>
		</assignedEntity>
	</legalAuthenticator>
	<!-- [0..1] Информация о враче, ответственном за проверку правильности заполнения медицинских свидетельств -->
	<authenticator>
		<!-- R [1..1] Дата подписи документа врачом, ответственного за правильность заполнения медицинских свидетельств -->
		<time value="{DeathSvid_GiveDate}"/>
		<!-- R [1..1] Факт наличия подписи на документе -->
		<signatureCode code="S"/>
		<!-- R [1..1] Врач, ответственный за правильность заполнения медицинских свидетельств (роль) -->
		<assignedEntity>
			<!-- R [1..1] Уникальный идентификатор врача, ответственного за правильность заполнения медицинских свидетельств -->
			<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.70 extension = идентификатор персонала -->
			<id root="{PassportToken_tid}.100.1.1.70" extension="{MedPersonal_hid}"/>
			<!-- [0..1] СНИЛС врача, ответственного за правильность заполнения медицинских свидетельств -->
			<id root="1.2.643.100.3" extension="{MedPersonal_hSnils}"/>
			<!-- R [1..1] Код должности  врача, ответственного за правильность заполнения медицинских свидетельств -->
			<code code="{MedPost_hCode}" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemName="Должности работников организаций медицинского и фармацевтического профиля" displayName="{MedPost_hName}"/>
			<!-- [0..1] Адрес  врача, ответственного за правильность заполнения медицинских свидетельств -->
			<addr>
				<!-- R [1..1] адрес текстом -->
				<streetAddressLine>{MHAddress_Address}</streetAddressLine>
				<!-- R [1..1] Регион РФ -->
				<state>{MHKLRgn_id}</state>
			</addr>
			<!-- [0..1] Телефон врача, ответственного за правильность заполнения медицинских свидетельств -->
			<telecom value="tel:{MedPersonal_hPhone}"/>
			<!-- R [1..1] Врач, ответственный за правильность заполнения медицинских свидетельств (человек) -->
			<assignedPerson>
				<!-- R [1..1] Фамилия, Имя, Отчество врача, ответственного за правильность заполнения медицинских свидетельств -->
				<name>
					<!-- R [1..1] Фамилия -->
					<family>{MedPersonal_hSurName}</family>
					<!-- R [1..1] Имя -->
					<given>{MedPersonal_hFirName}</given>
					<!-- [1..1] Отчество -->
					<given>{MedPersonal_hSecName}</given>
				</name>
			</assignedPerson>
			<!-- [0..1] Место работы врача, ответственного за правильность заполнения медицинских свидетельств -->
			<representedOrganization>
				<!-- R [1..1] Идентификатор организации -->
				<!-- организации - по справочнику «Реестр медицинских организаций Российской Федерации» (OID: 1.2.643.5.1.13.13.11.1461) -->
				<!-- индивидуальные предприниматели - указание на отсутствие кода, nullFlavor="OTH" -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование организации \ ФИО Индивидуального предпринимателя -->
				<name>{Lpu_Nick}</name>
				<!-- [0..1] Телефон организации -->
				<telecom value="tel:{Lpu_Phone}" use="WP"/>
				<!-- [0..*] Прочие контакты организации (веб-сайт) -->
				<telecom value="{Lpu_Www}"/>
				<!-- [1..1] Адрес организации\Индивидуального предпринимателя-->
				<addr>
					<!-- R [1..1] адрес текстом -->
					<streetAddressLine>{LAddress_Address}</streetAddressLine>
					<!-- R [1..1] Регион РФ -->
					<state>{LKLRgn_id}</state>
				</addr>
			</representedOrganization>
		</assignedEntity>
	</authenticator>
	<!-- R [1..1] ДАННЫЕ О ПОЛУЧАТЕЛЕ ДОКУМЕНТА - родственник или представитель умершего -->
	<!-- В случае родственника указывать classCode="PRS"-->
	<!-- В случае уполномоченного лица указывать classCode="AGNT"-->
	<participant typeCode="IRCP">
		<!-- R [1..1] Дата получения медицинского свидетельства о смерти (с точностью до дня) -->
		<!-- В пункте 14 корешка делается запись о получателе медицинского свидетельства о смерти, указывается дата получения. -->
		<time value="{DeathSvid_RcpDate}"/>
		<!-- R [1..1] Получатель документа (роль) -->
		<associatedEntity classCode="{DeathSvidRelation_FirstCode}">
			<!-- <associatedEntity classCode="AGNT"> -->
			<!-- R [1..1] Документ, удостоверяющий личность получателя, серия, номер, кем выдан. -->
			<!-- В пункте 14 корешка делается запись о получателе медицинского свидетельства о смерти, указываются данные о документе, удостоверяющем личность получателя медицинского свидетельства о смерти (серия, номер, кем выдан). -->
			<id root="1.2.643.5.1.13.13.99.2.48.{Frmr_id}" extension="{Document_Ser} {Document_Num}" assigningAuthorityName='{DocOrg_Name}. Дата выдачи: {Document_begDate}'/>
			<!-- [0..1] СНИЛС получателя документа (родственник или представитель умершего) -->
			<id root="1.2.643.100.3" extension="{RPerson_Snils}"/>
			<!-- R [1..1] Получатель документа (родственник или представитель умершего) -->
			<associatedPerson>
				<!-- R [1..1] Фамилия, Имя, Отчество получателя-->
				<!-- В пункте 14 корешка делается запись о фамилии, имени, отчестве получателя медицинского свидетельства о смерти.-->
				<name>
					<!-- R [1..1] Фамилия получателя-->
					<family>{RPerson_SurName}</family>
					<!-- R [1..1] Имя получателя-->
					<given>{RPerson_FirName}</given>
					<!-- [1..1] Отчество получателя-->
					<given>{RPerson_SecName}</given>
				</name>
			</associatedPerson>
		</associatedEntity>
	</participant>

	<!-- R [1..1] ТЕЛО ДОКУМЕНТА -->
	<component>
		<!-- R [1..1] Структурированное тело документа -->
		<structuredBody>
			<!-- R [1..1] СЕКЦИЯ: Сведения о документе-->
			<component>
				<section>
					<!-- R [1..1] код секции -->
					<code code="DOCINFO" codeSystem="1.2.643.5.1.13.13.11.1379" codeSystemVersion="1.4" codeSystemName="Секции электронных медицинских документов" displayName="Сведения о документе"/>
					<!-- R [1..1] заголовок секции -->
					<title>МЕДИЦИНСКОЕ СВИДЕТЕЛЬСТВО О СМЕРТИ</title>
					<!-- R [1..1] наполнение секции -->
					<text>
						<content ID="hd1">СЕРИЯ {DeathSvid_Ser} N {DeathSvid_Num}</content><br/>
						<content>Дата выдачи {DeathSvid_GiveDateFormatted}</content><br/>
						<content ID="hd2">{DeathSvidType_Name} </content><content ID="hd3">серия {DeathSvid_OldSer}  № {DeathSvid_OldNum} </content><content ID="hd4">от {DeathSvid_OldGiveDateFormatted}.</content><br/>
					</text>
					<!-- R [1..1] кодирование ... Серия и номер медицинского свидетельства о смерти-->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="101" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Серия и номер медицинского свидетельства о смерти"/>
							<text>
								<reference  value="#hd1"/>
							</text>
							<value xsi:type="ST">{DeathSvid_Ser}{DeathSvid_Num}</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Вид медицинского свидетельства о смерти -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="511" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Вид медицинского свидетельства о смерти"/>
							<text>
								<reference  value="#hd2"/>
							</text>
							<value xsi:type="CD" code="{DeathSvidType_Code}" codeSystem="1.2.643.5.1.13.13.99.2.19" codeSystemName="Вид медицинского свидетельства о смерти" displayName="{DeathSvidType_Name}"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Серия и номер предшествующего медицинского свидетельства о смерти, если неприменимо - ставим nullFlavor=NA -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="102" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Серия и номер предшествующего медицинского свидетельства о смерти"/>
							<text>
								<reference   value="#hd3"/>
							</text>
							<?php if (!empty($DeathSvid_OldNum)) { ?>
							<value xsi:type="ST">{DeathSvid_OldSer}{DeathSvid_OldNum}</value>
							<?php } else { ?>
							<value xsi:type="ST" nullFlavor="NA"/>
							<?php } ?>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Дата выдачи предшествующего медицинского свидетельства о смерти, если неприменимо - ставим nullFlavor=NA -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="112" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Дата выдачи предшествующего медицинского свидетельства о смерти"/>
							<text>
								<reference   value="#hd4"/>
							</text>
							<?php if (!empty($DeathSvid_OldGiveDate)) { ?>
							<value xsi:type="TS" value="{DeathSvid_OldGiveDate}"/>
							<?php } else { ?>
							<value xsi:type="TS" nullFlavor="NA"/>
							<?php } ?>
						</observation>
					</entry>
				</section>
			</component>
			<!-- R [1..1] СЕКЦИЯ: Информация об умершем-->
			<component>
				<section>
					<!-- R [1..1] код секции -->
					<code code="DEADPATINFO" codeSystem="1.2.643.5.1.13.13.11.1379" codeSystemVersion="1.4" codeSystemName="Секции электронных медицинских документов" displayName="Информация  об умершем"/>
					<!-- R [1..1] заголовок секции -->
					<title>Информация об умершем</title>
					<!-- R [1..1] наполнение секции -->
					<text>
						<content>Фамилия, имя, отчество умершего: {Person_SurName} {Person_FirName} {Person_SecName}.</content><br/>
						<content>Пол: {Sex_Name}.</content><br/>
						<content>Дата рождения: {Person_BirthDayFormatted}.</content><br/>
						<?php if (!empty($DeathSvid_DeathDateFormatted)) { ?>
						<content ID="p4">Дата смерти: {DeathSvid_DeathDateFormatted}, время {DeathSvid_DeathTime}.</content><br/>
						<?php } else { ?>
						<content ID="p4">Дата смерти неизвестна.</content><br/>
						<?php } ?>
						<content>Место постоянного жительства (регистрации): {Address_Address}.</content><br/>
						<content ID="p6">Местность: {KLAreaType_Name}.</content><br/>
						<content ID="p7">Место смерти: {DAddress_Address}</content><br/>
						<content ID="p8">Местность: {DKLAreaType_Name}.</content><br/>
						<content ID="p9">Смерть наступила: {DeathPlace_Name}.</content><br/>
						<content ID="p10">Для детей, умерших  в возрасте от 168 час. до 1 месяца: {ChildTermType_Name}</content><br/>
						<content ID="p11">Для детей, умерших  в возрасте от 168 час. до 1 года: {DeathSvid_Mass} {DeathSvid_ChildCount}</content><br/>
						<content ID="p12">Семейное положение: {DeathFamilyStatus_Name}</content><br/>
						<content ID="p13">Образование: {DeathEducation_Name}</content><br/>
						<content ID="p14">Занятость: {DeathEmployment_Name}</content>
					</text>
					<!-- R [1..1] кодирование ... Дата и время смерти -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="521" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Дата и время смерти"/>
							<text>
								<reference value="#p4"/>
							</text>
							<value xsi:type="TS" value="{DeathSvid_DeathDate}"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Местность регистрации умершего-->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="250" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Тип местности постоянного проживания (регистрации)"/>
							<text>
								<reference value="#p6"/>
							</text>
							<value xsi:type="CD" code="{KLAreaType_Code}" codeSystem="1.2.643.5.1.13.13.11.1042" codeSystemName="Признак жителя города или села" displayName="{KLAreaType_Name}"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Место смерти -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="531" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Место смерти"/>
							<text>
								<reference value="#p7"/>
							</text>
							<value xsi:type="AD">
								<!-- [1..1] адрес текстом -->
								<streetAddressLine>{DAddress_Address}</streetAddressLine>
								<!--[1..1] Регион РФ (республика, край, область) -->
								<state>{DKLRgn_id}</state>
								<!-- [0..1] Город \ Село -->
								<city>{DKLCity_Name}</city>
								<!-- [0..1] Район -->
								<precinct>{DKLSubRgn_Name}</precinct>
								<!-- [0..1] Улица -->
								<streetName>{DKLStreet_Name}</streetName>
								<!-- [0..1] Дом -->
								<houseNumber>{DAddress_House}</houseNumber>
								<!-- [0..1] Квартира -->
								<unitID>{DAddress_Flat}</unitID>
							</value>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Местность смерти -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="252" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Тип местности смерти"/>
							<text>
								<reference value="#p8"/>
							</text>
							<value xsi:type="CD" code="{DKLAreaType_Code}" codeSystem="1.2.643.5.1.13.13.11.1042" codeSystemName="Признак жителя города или села" displayName="{DKLAreaType_Name}"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Тип места смерти-->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="541" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Типы мест наступления смерти"/>
							<text>
								<reference value="#p9"/>
							</text>
							<value xsi:type="CD" code="{DeathPlace_Code}" codeSystem="1.2.643.5.1.13.13.99.2.20" codeSystemName="Типы мест наступления смерти" displayName="{DeathPlace_Name}"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Доношенность детей, умерших в возрасте от 168 часов до 1 месяца, если неприменимо - ставим nullFlavor=NA-->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="612" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Доношенность умершего ребенка возрастом от 168 часов до 1 месяца жизни"/>
							<text>
								<reference  value="#p10"/>
							</text>
							<value xsi:type="CD" codeSystem="1.2.643.5.1.13.13.99.2.18" codeSystemVersion="1.1" codeSystemName="Доношенность новорожденного" nullFlavor="NA"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Масса тела ребёнка при рождении (в граммах)-->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="410" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Масса тела ребёнка при рождении (в граммах)"/>
							<text>
								<reference  value="#p11"/>
							</text>
							<?php if (!empty($DeathSvid_Mass)) { ?>
							<value xsi:type="PQ" unit="г" value="{DeathSvid_Mass}"/>
							<?php } else { ?>
							<value xsi:type="PQ" unit="г" nullFlavor="NA"/>
							<?php } ?>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Которым по счёту ребёнок был рождён у матери-->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="370" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Которым по счёту ребёнок был рождён у матери"/>
							<text>
								<reference  value="#p11"/>
							</text>
							<?php if (!empty($DeathSvid_Mass)) { ?>
							<value xsi:type="INT" value="{DeathSvid_ChildCount}"/>
							<?php } else { ?>
							<value xsi:type="INT" nullFlavor="NA"/>
							<?php } ?>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Семейное положение-->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="260" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Семейное положение"/>
							<text>
								<reference  value="#p12"/>
							</text>
							<value xsi:type="CD" code="{DeathFamilyStatus_Code}" codeSystem="1.2.643.5.1.13.13.99.2.15" codeSystemName="Семейное положение" displayName="{DeathFamilyStatus_Name}"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Образование-->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="270" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Образование"/>
							<text>
								<reference  value="#p13"/>
							</text>
							<value xsi:type="CD" code="{DeathEducation_Code}" codeSystem="1.2.643.5.1.13.13.99.2.16" codeSystemName="Классификатор образования для медицинского свидетельства о смерти" displayName="{DeathEducation_Name}"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Занятость-->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="280" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Занятость"/>
							<text>
								<reference  value="#p14"/>
							</text>
							<?php if (!empty($DeathEmployment_Code)) { ?>
							<value xsi:type="CD" code="{DeathEmployment_Code}" codeSystem="1.2.643.5.1.13.13.99.2.17" codeSystemName="Занятость" displayName="{DeathEmployment_Name}"/>
							<?php } else { ?>
							<value xsi:type="CD" codeSystem="1.2.643.5.1.13.13.99.2.17" codeSystemName="Занятость" nullFlavor="UNK"/>
							<?php } ?>
						</observation>
					</entry>
				</section>
			</component>
			<!-- R [1..1] СЕКЦИЯ: Характеристика причины смерти-->
			<component>
				<section>
					<!-- R [1..1] код секции -->
					<code code="ABOUTDEATH" codeSystem="1.2.643.5.1.13.13.11.1379" codeSystemVersion="1.4" codeSystemName="Секции электронных медицинских документов" displayName="Характеристика причины смерти"/>
					<!-- R [1..1] заголовок секции -->
					<title>Характеристика причины смерти</title>
					<!-- R [1..1] наполнение секции -->
					<text>
						<content ID="p15">Смерть произошла: {DeathCause_Name}.</content> <br/>
						<content ID="p16">Для смерти от несчастного случая, установленная дата травмы (отравления): {DeathSvid_TraumaDateFormatted}.</content> <br/>
						<content ID="p17">Тип медицинского работника, установившего причины смерти: {DeathSetType_Name}.</content><br/>
						<content ID="p18">Основание для определения причины смерти: {DeathSetCause_Name}.</content><br/><br/>
						Причины смерти
						<table>
							<tbody>
							<tr>
								<th>I. Причины смерти:</th>
								<th>Период времени</th>
								<th>Код МКБ10</th>
							</tr>

							<tr ID="p191">
								<td><sub>а) Болезнь или состояние, напосредственно приведшее к смерти:</sub><br/>{Diag_iName}</td>
								<td>{DeathSvid_TimePeriod}</td>
								<td>{Diag_iCode}</td>
							</tr>

							<tr ID="p192">
								<td><sub>б) патологическое состояние, которое привело к возникновению вышеуказанной причины:</sub><br/>{Diag_tName}</td>
								<td>{DeathSvid_TimePeriodPat}</td>
								<td>{Diag_tCode}</td>
							</tr>

							<tr ID="p193">
								<td><sub>в) первоначальная причина смерти:</sub><br/>{Diag_mName}</td>
								<td>{DeathSvid_TimePeriodDom}</td>
								<td>{Diag_mCode}</td>
							</tr>

							<tr ID="p194">
								<td><sub>г) внешняя причина при травмах и отравлениях:</sub><br/>{Diag_eName}</td>
								<td>{DeathSvid_TimePeriodExt}</td>
								<td>{Diag_eCode}</td>
							</tr>

							<tr>
								<th>II. Прочие важные состояния, способствовавшие смерти, но не связанные<br/> с болезнью или патологическим состоянием, приведшим к ней</th>
								<th>Период времени</th>
								<th>Код МКБ10</th>
							</tr>

							<tr ID="p1951">
								<td>{Diag_oName}</td>
								<td>{DeathSvid_TimePeriodImp}</td>
								<td>{Diag_oCode}</td>
							</tr>

							</tbody>
						</table><br/><br/>
						<content ID="p20">В случае смерти в результате ДТП: {DtpDeathTime_Name}</content><br/>
						<content ID="p21">Связь смерти с беременностью: {DeathWomanType_Name}</content>
					</text>
					<!-- R [1..1] кодирование ... Род смерти -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="551" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Род причины смерти"/>
							<text>
								<reference value="#p15"/>
							</text>
							<value xsi:type="CD" code="{DeathCause_Code}" codeSystem="1.2.643.5.1.13.13.99.2.21" codeSystemName="Род причины смерти" displayName="{DeathCause_Name}"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Дата и время проишествия для смерти от внешних причин -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="561" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Дата и время проишествия для смерти от внешних причин"/>
							<text>
								<reference value="#p16"/>
							</text>
							<?php if (!empty($DeathSvid_TraumaDate)) { ?>
							<value xsi:type="TS" value="{DeathSvid_TraumaDate}"/>
							<?php } else { ?>
							<value xsi:type="TS" nullFlavor="NA"/>
							<?php } ?>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Тип медицинского работника, установившего причины смерти -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="571" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Тип медицинского работника, установившего причины смерти"/>
							<text>
								<reference value="#p17"/>
							</text>
							<value xsi:type="CD" code="{DeathSetType_Code}" codeSystem="1.2.643.5.1.13.13.99.2.22" codeSystemName="Тип медицинского работника, установившего причины смерти" displayName="{DeathSetType_Name}"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Основание для определения причины смерти -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="581" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Основание для определения причины смерти"/>
							<text>
								<reference value="#p18"/>
							</text>
							<value xsi:type="CD" code="{DeathSetCause_Code}" codeSystem="1.2.643.5.1.13.13.99.2.23" codeSystemName="Основания для определения причины смерти" displayName="{DeathSetCause_Name}"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Болезнь или состояние, напосредственно приведшее к смерти -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="4030" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" displayName="Болезнь или состояние, непосредственно приведшее к смерти" codeSystemName="Кодируемые поля CDA документов"/>
							<text>
								<reference value="#p191"/>
							</text>
							<!-- [1..1] время начала патологического процесса, использованное для расчета приблизительного периода между началом патологического процесса и смертью-->
							<?php if (!empty($DeathSvid_TimePeriod)) { ?>
							<effectiveTime value="{DeathSvid_TimePeriod}"/>
							<?php } else { ?>
							<effectiveTime nullFlavor="NA"/>
							<?php } ?>
							<value xsi:type="CD" code="{Diag_iCode}" codeSystem="1.2.643.5.1.13.13.11.1005" codeSystemName="Международная классификация болезней и состояний, связанных со здоровьем 10 пересмотра. Версия 4" displayName="{Diag_iName}"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... патологическое состояние, которое привело к возникновению непосредственной причины смерти -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="4035" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" displayName="Патологическое состояние, которое привело к возникновению непосредственной причины смерти" codeSystemName="Кодируемые поля CDA документов"/>
							<text>
								<reference value="#p192"/>
							</text>
							<?php if (!empty($DeathSvid_TimePeriodPat)) { ?>
							<effectiveTime value="{DeathSvid_TimePeriodPat}"/>
							<?php } else { ?>
							<effectiveTime nullFlavor="NA"/>
							<?php } ?>
							<value xsi:type="CD" code="{Diag_tCode}" codeSystem="1.2.643.5.1.13.13.11.1005" codeSystemName="Международная классификация болезней и состояний, связанных со здоровьем 10 пересмотра. Версия 4" displayName="{Diag_tName}"/>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... первоначальная причина смерти -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="4040" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" displayName="Первоначальная причина смерти" codeSystemName="Кодируемые поля CDA документов"/>
							<text>
								<reference value="#p193"/>
							</text>
							<?php if (!empty($DeathSvid_TimePeriodDom)) { ?>
							<effectiveTime value="{DeathSvid_TimePeriodDom}"/>
							<?php } else { ?>
							<effectiveTime nullFlavor="NA"/>
							<?php } ?>
							<value xsi:type="CD" code="{Diag_mCode}" codeSystem="1.2.643.5.1.13.13.11.1005" codeSystemName="Международная классификация болезней и состояний, связанных со здоровьем 10 пересмотра. Версия 4" displayName="{Diag_mName}"/>
						</observation>
					</entry>
					<!--  [1..1] кодирование ... внешняя причина смерти при травмах и отравлениях -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="4045" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" displayName="Внешняя причина смерти при травмах и отравлениях" codeSystemName="Кодируемые поля CDA документов"/>
							<text>
								<reference value="#p194"/>
							</text>
							<?php if (!empty($DeathSvid_TimePeriodExt)) { ?>
							<effectiveTime value="{DeathSvid_TimePeriodExt}"/>
							<?php } else { ?>
							<effectiveTime nullFlavor="NA"/>
							<?php } ?>
							<value xsi:type="CD" code="{Diag_eCode}" codeSystem="1.2.643.5.1.13.13.11.1005" codeSystemName="Международная классификация болезней и состояний, связанных со здоровьем 10 пересмотра. Версия 4" displayName="{Diag_eName}"/>
						</observation>
					</entry>
					<!--  [0..*] кодирование ... Прочие важные состояния, способствовавшие смерти, но не связанные с болезнью или патологическим состоянием, приведшим к ней -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="4050" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" displayName="Прочие важные состояния, способствовавшие смерти" codeSystemName="Кодируемые поля CDA документов"/>
							<text>
								<reference value="#p1951"/>
							</text>
							<?php if (!empty($DeathSvid_TimePeriodImp)) { ?>
							<effectiveTime value="{DeathSvid_TimePeriodImp}"/>
							<?php } else { ?>
							<effectiveTime nullFlavor="NA"/>
							<?php } ?>
							<value xsi:type="CD" code="{Diag_oCode}" codeSystem="1.2.643.5.1.13.13.11.1005" codeSystemName="Международная классификация болезней и состояний, связанных со здоровьем 10 пересмотра. Версия 4" displayName="{Diag_oName}"/>
						</observation>
					</entry>
					<!--  [1..1] кодирование ... Связь смерти с ДТП, если неприменимо - ставим nullFlavor=NA -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="601" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Связь смерти с ДТП"/>
							<text>
								<reference value="#p20"/>
							</text>
							<?php if (!empty($DtpDeathTime_Code)) { ?>
							<value xsi:type="CD" code="{DtpDeathTime_Code}" codeSystem="1.2.643.5.1.13.13.99.2.24" codeSystemName="Связь смерти с ДТП" displayName="{DtpDeathTime_Name}"/>
							<?php } else { ?>
							<value xsi:type="CD" codeSystem="1.2.643.5.1.13.13.99.2.24" codeSystemName="Связь смерти с ДТП" nullFlavor="NA"/>
							<?php } ?>
						</observation>
					</entry>
					<!-- [1..1] кодирование ... Связь смерти с беременностью, если неприменимо - ставим nullFlavor=NA -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="591" codeSystem="1.2.643.5.1.13.13.11.1380" codeSystemVersion="1.4" codeSystemName="Кодируемые поля CDA документов"  displayName="Связь смерти с беременностью"/>
							<text>
								<reference value="#p21"/>
							</text>
							<?php if (!empty($DeathWomanType_Code)) { ?>
							<value xsi:type="CD" code="{DeathWomanType_Code}" codeSystem="1.2.643.5.1.13.13.99.2.25" codeSystemName="Связь смерти с беременностью" displayName="{DeathWomanType_Name}"/>
							<?php } else { ?>
							<value xsi:type="CD" codeSystem="1.2.643.5.1.13.13.99.2.25" codeSystemName="Связь смерти с беременностью" nullFlavor="NA"/>
							<?php } ?>
						</observation>
					</entry>
				</section>
			</component>
		</structuredBody>
	</component>
</ClinicalDocument>