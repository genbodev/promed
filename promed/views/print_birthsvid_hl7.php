<ClinicalDocument xmlns="urn:hl7-org:v3" xsi:schemaLocation="urn:hl7-org:v3 CDA.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:f103="urn:f103" xmlns:fias="urn:fias">
	<!-- ЗАГОЛОВОК ДОКУМЕНТА "Медицинское свидетельство о рождении" -->
	<!-- R [1..1] Область применения документа (Страна) -->
	<realmCode code="RU"/>
	<!-- R [1..1] Указатель на использование CDA R2 -->
	<typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>
	<!-- R [1..1] Идентификатор Шаблона документа "Медицинское свидетельство о рождении. Третий уровень формализации." -->
	<templateId root="1.2.643.5.1.13.2.7.5.1.33.3"/>
	<!-- R [1..1] Уникальный идентификатор документа -->
	<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.51 extension = идентификатор документа -->
	<id root="{PassportToken_tid}.100.1.1.51" extension="{BirthSvid_id}"/>
	<!-- R [1..1] Тип документа -->
	<code code="33" codeSystem="1.2.643.5.1.13.13.11.1115" codeSystemVersion="2.4" codeSystemName="Система электронных медицинских документов" displayName="Медицинское свидетельство о рождении"/>
	<!-- R [1..1] Заголовок документа -->
	<title>Медицинское свидетельство о рождении</title>
	<!-- R [1..1] Дата выдачи медицинского свидетельства о рождении (с точностью до дня) -->
	<effectiveTime value="{BirthSvid_GiveDate}"/>
	<!-- R [1..1] Уровень конфиденциальности документа -->
	<confidentialityCode code="N" codeSystem="1.2.643.5.1.13.13.11.1116" codeSystemVersion="1.1" codeSystemName="Уровень конфиденциальности документа" displayName="Обычный"/>
	<!-- R [1..1] Язык документа -->
	<languageCode code="ru-RU"/>
	<!-- R [1..1] Уникальный идентификатор набора версий документа -->
	<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.50 extension = идентификатор набора версий документа -->
	<setId root="{PassportToken_tid}.100.1.1.50" extension="{BirthSvid_id}"/>
	<!-- R [1..1] Номер версии данного документа -->
	<!-- Внесение более двух исправлений в медицинское свидетельство о рождении не допускается. -->
	<!-- максимальное значение - value="3" -->
	<versionNumber value="1"/>
	<!-- R [1..1] ДАННЫЕ О НОВОРОЖДЕННОМ -->
	<recordTarget>
		<!-- R [1..1] НОВОРОЖДЕННЫЙ (роль) -->
		<patientRole>
			<!-- R [1..1] Уникальный идентификатор новорожденного в МИС-->
			<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.10 extension = идентификатор пациента -->
			<id root="{PassportToken_tid}.100.1.1.10" extension="{Person_id}"/>
			<!-- R [1..1] НОВОРОЖДЕННЫЙ (человек) -->
			<patient>
				<!-- [1..1] ФИО новорожденного -->
				<!-- В пункте 11 "Фамилия ребенка", заполняемом по желанию родителей, фамилия ребенка указывается только в случае, если родители имеют одинаковую фамилию. -->
				<!-- "неизвестно" указывается с использованием nullFlavor="ASKU" или "NA"-->
				<name>
					<!-- R [1..1] Фамилия новорожденного -->
					<family>{BirthSvid_ChildFamil}</family>
				</name>
				<!-- R [1..1] Пол новорожденного -->
				<!-- В пункте 15 "Пол" делается отметка о поле ребенка (мальчик или девочка). В случае невозможности визуального определения пола ребенка его записывают по желанию матери -->
				<administrativeGenderCode code="{Sex_Code}" codeSystem="1.2.643.5.1.13.13.11.1040" codeSystemName="Пол пациента" displayName="{Sex_Name}">
					<originalText>
						<reference value="#N15"/>
					</originalText>
				</administrativeGenderCode>
				<!-- R [1..1] Дата рождения новорожденного (с точностью до минут) -->
				<!-- В пункте 1 "Ребенок родился" указывается дата рождения (число, месяц, год), а также время (часы, минуты). Сведения берут из истории родов, истории развития новорожденного и иных документов. -->
				<birthTime value="{BirthSvid_BirthDT_Format}"/>
				<!-- [1..1] Место рождения новорожденного -->
				<!-- В пункте 12 "Место рождения" указывается республика (край, область), район, город (село), где произошло рождение ребенка. При отсутствии таких сведений делается запись "неизвестно". -->
				<!-- "неизвестно" указывается с использованием nullFlavor="UNK" -->
				<birthplace>
					<place>
						<addr>
							<!-- [1..1] Глобальный уникальный идентификатор ФИАС -->
							<fias:GUID<?=(empty($KLAreaGUID)) ? ' nullFlavor="UNK"' : ''?>>{KLAreaGUID}</fias:GUID>
							<!-- R [1..1] Регион РФ (республика, край, область) -->
							<state>{KLRgn_id}</state>
							<!-- R [1..1] Район -->
							<precinct>{KLSubRgn_Name}</precinct>
							<!-- R [1..1] Город \ Село -->
							<city>{KLCity_Name}</city>
						</addr>
					</place>
				</birthplace>
			</patient>
			<!-- R [1..1] Медицинская организация или индивидуальный предприниматель, осуществляющий медицинскую деятельность -->
			<providerOrganization>
				<!-- [1..1] Идентификатор медицинской организации ... Код по регистру МО -->
				<!-- организации - по справочнику «Реестр медицинских организаций Российской Федерации» (OID: 1.2.643.5.1.13.13.11.1461) -->
				<!-- для индивидуальных предпринимателей - указание на отсутствие кода, nullFlavor="OTH" -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование медицинской организации или ФИО Индивидуального предпринимателя -->
				<!-- При заполнении медицинского свидетельства о рождении в медицинской организации указывается полное наименование медицинской организации. -->
				<!-- В случае заполнения медицинского свидетельства о рождении индивидуальным предпринимателем указывается его фамилия, имя, отчество -->
				<name>{Lpu_Nick}</name>
				<!-- [0..1] Телефон медицинской организации или индивидуального предпринимателя -->
				<?php if ($Lpu_Phone) {?>
				<telecom value="tel:{Lpu_Phone}" use="WP"/>
				<?php }?>
				<!-- [0..*] Прочие контакты медицинской организации или индивидуального предпринимателя (веб-сайт) -->
				<?php if ($Lpu_Www) {?>
				<telecom value="{Lpu_Www}"/>
				<?php }?>
				<!-- R [1..1] Адрес медицинской организации или индивидуального предпринимателя-->
				<!-- При заполнении медицинского свидетельства о рождении в медицинской организации или индивидуальным предпринимателем указывается адрес. -->
				<addr>
					<!-- R [1..1] Адрес текстом -->
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
		<time value="{BirthSvid_GiveDate}"/>
		<!-- R [1..1] АВТОР (роль) -->
		<assignedAuthor>
			<!-- R [1..1] Уникальный идентификатор автора в МИС -->
			<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.70 extension = идентификатор персонала -->
			<id root="{PassportToken_tid}.100.1.1.70" extension="{MedPersonal_id}"/>
			<!-- [0..1] СНИЛС автора -->
			<id root="1.2.643.100.3" extension="{MedPersonal_Snils}"/>
			<!-- R [1..1] Код должности автора-->
			<!-- В пункте 8 корешка указывается должность врача (фельдшера, акушерки), заполнившего медицинское свидетельство о рождении. -->
			<!-- В пункте 20 указываются сведения о лице, заполнившем медицинское свидетельство о рождении: должность врача (фельдшера, акушерки). -->
			<code code="{MedPost_Code}" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemName="Должности работников организаций медицинского и фармацевтического профиля" displayName="{MedPost_Name}"/>
			<!-- [0..1] Адрес автора документа -->
			<addr>
				<!-- R [1..1] Адрес текстом -->
				<streetAddressLine>{MAddress_Address}</streetAddressLine>
				<!-- R [1..1] Регион РФ -->
				<state>{MKLRgn_id}</state>
			</addr>
			<!-- [0..1] Телефон автора -->
			<telecom value="tel:{MedPersonal_Phone}"/>
			<!-- R [1..1] АВТОР (человек) -->
			<assignedPerson>
				<!-- R [1..1] Фамилия, Имя, Отчество автора -->
				<!-- В пункте 8 корешка указывается фамилия, имя, отчество врача (фельдшера, акушерки), заполнившего медицинское свидетельство о рождении. -->
				<!-- В пункте 20 указываются сведения о лице, заполнившем медицинское свидетельство о рождении: фамилия, имя, отчество врача (фельдшера, акушерки). -->
				<name>
					<!-- R [1..1] Фамилия автора-->
					<family>{MedPersonal_SurName}</family>
					<!-- R [1..1] Имя автора-->
					<given>{MedPersonal_FirName}</given>
					<!-- [0..1] Отчество автора-->
					<?php if (!empty($MedPersonal_SecName)) { ?>
						<given>{MedPersonal_SecName}</given>
					<?php } ?>
				</name>
			</assignedPerson>
			<!-- [0..1] Место работы автора  -->
			<representedOrganization>
				<!-- [1..1] Идентификатор медицинской организации ... Код по регистру МО -->
				<!-- организации - по справочнику «Реестр медицинских организаций Российской Федерации» (OID: 1.2.643.5.1.13.13.11.1461) -->
				<!-- для индивидуальных предпринимателей - указание на отсутствие кода, nullFlavor="OTH" -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование медицинской организации -->
				<name>{Lpu_Nick}</name>
				<!-- [0..1] Телефон медицинской организации -->
				<telecom value="tel:{Lpu_Phone}" use="WP"/>
				<!-- [0..*] Прочие контакты медицинской организации (веб-сайт) -->
				<telecom value="{Lpu_Www}"/>
				<!-- [1..1] Адрес организации -->
				<addr>
					<!-- R [1..1] Адрес текстом -->
					<streetAddressLine>{LAddress_Address}</streetAddressLine>
					<!-- R [1..1] Регион РФ -->
					<state>{LKLRgn_id}</state>
				</addr>
			</representedOrganization>
		</assignedAuthor>
	</author>
	<!-- R [1..1] ДАННЫЕ ОБ ОРГАНИЗАЦИИ-ВЛАДЕЛЬЦЕ ДОКУМЕНТА -->
	<!-- Бланки медицинских свидетельств о рождении хранятся у руководителя медицинской организации или у индивидуального предпринимателя. -->
	<custodian>
		<!-- R [1..1] Организация-владелец документа (роль) -->
		<assignedCustodian>
			<!-- R [1..1] Организация-владелец документа (организация или индивидуальный предприниматель) -->
			<representedCustodianOrganization>
				<!-- [1..1] Идентификатор медицинской организации ... Код по регистру МО -->
				<!-- организации - по справочнику «Реестр медицинских организаций Российской Федерации» (OID: 1.2.643.5.1.13.13.11.1461) -->
				<!-- для индивидуальных предпринимателей - указание на отсутствие кода, nullFlavor="OTH" -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование медицинской организации или ФИО Индивидуального предпринимателя-->
				<name>{Lpu_Nick}</name>
				<!-- [1..1] Телефон медицинской организации или индивидуального предпринимателя-->
				<telecom value="tel:{Lpu_Phone}" use="WP"/>
				<!-- [1..1] Адрес организации или индивидуального предпринимателя -->
				<addr>
					<!-- R [1..1] Адрес текстом -->
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
	<!-- R [1..1] ДАННЫЕ О ЛИЦЕ, ПРИДАВШЕМ ЮРИДИЧЕСКУЮ СИЛУ ДОКУМЕНТУ -->
	<!-- Медицинское свидетельство о рождении подписывается руководителем медицинской организации (или уполномоченным лицом) или индивидуальным предпринимателем -->
	<legalAuthenticator>
		<!-- R [1..1] Дата подписи документа лицом, придавшем юридическую силу документу -->
		<time value="{BirthSvid_GiveDate}"/>
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
				<!-- R [1..1] Адрес текстом -->
				<streetAddressLine>{MAddress_Address}</streetAddressLine>
				<!-- R [1..1] Регион РФ -->
				<state>{MKLRgn_id}</state>
			</addr>
			<!-- [0..1] Телефон лица, придавшего юридическую силу документу -->
			<telecom value="tel:{MedPersonal_Phone}"/>
			<!-- R [1..1] Лицо, придавшен юридическую силу документу (человек) -->
			<assignedPerson>
				<!-- R [1..1] Фамилия, Имя, Отчество лица, придавшего юридическую силу документу -->
				<!-- Медицинское свидетельство о рождении подписывается руководителем медицинской организации (или уполномоченным лицом) или индивидуальным предпринимателем с указанием фамилии, имени, отчества. -->
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
			<representedOrganization>
				<!-- [1..1] Идентификатор медицинской организации ... Код по регистру МО -->
				<!-- организации - по справочнику «Реестр медицинских организаций Российской Федерации» (OID: 1.2.643.5.1.13.13.11.1461) -->
				<!-- для индивидуальных предпринимателей - указание на отсутствие кода, nullFlavor="OTH" -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование медицинской организации -->
				<name>{Lpu_Nick}</name>
				<!-- [0..1] Телефон медицинской организации -->
				<telecom value="tel:{Lpu_Phone}" use="WP"/>
				<!-- [0..*] Прочие контакты медицинской организации (веб-сайт) -->
				<telecom value="{Lpu_Www}"/>
				<!-- [1..1] Адрес организации -->
				<addr>
					<!-- R [1..1] Адрес текстом -->
					<streetAddressLine>{LAddress_Address}</streetAddressLine>
					<!-- R [1..1] Регион РФ -->
					<state>{LKLRgn_id}</state>
				</addr>
			</representedOrganization>
		</assignedEntity>
	</legalAuthenticator>
	<!-- R [1..1] ДАННЫЕ О ПОЛУЧАТЕЛЕ ДОКУМЕНТА - родственник или представитель новорожденного-->
	<!-- Медицинское свидетельство о рождении выдается одному из родителей, родственнику одного из родителей или иному уполномоченному родителями лицу либо должностному лицу медицинской организации или должностному лицу иной организации, в которой находилась мать во время родов или находится ребенок, при предъявлении документов, удостоверяющих личность одного из родителей или личность заявителя и подтверждающих его полномочия. -->
	<!-- В случае родственника указывать classCode="PRS"-->
	<!-- В случае уполномоченного лица указывать classCode="AGNT"-->
	<participant typeCode="IRCP">
		<!-- R [1..1] Дата получения медицинского свидетельства о рождении (с точностью до дня) -->
		<!-- В пункте 9 корешка делается запись о получателе медицинского свидетельства о рождении, указывается дата получения. -->
		<time value="{BirthSvid_RcpDate}"/>
		<!-- R [1..1] Получатель документа (роль) -->
		<associatedEntity classCode="{DeputyKind_FirstCode}">
			<!-- <associatedEntity classCode="AGNT"> -->
			<!-- R [1..1] Документ, удостоверяющий личность получателя, серия, номер, кем выдан. -->
			<!-- В пункте 9 корешка делается запись о получателе медицинского свидетельства о рождении, указываются данные о документе, удостоверяющем личность получателя медицинского свидетельства о рождении (серия, номер, кем выдан). -->
			<!-- R [1..1] СНИЛС получателя -->
			<id <?=($RPerson_Snils) ? "root=\"1.2.643.100.3\" extension=\"$RPerson_Snils\"" : 'nullFlavor="UNK"'?>/>
			<!-- [1..1] ЛОКАЛЬНЫЙ ЭЛЕМЕНТ: Документ, удостоверяющий личность матери, серия, номер, кем выдан. -->
			<!-- R [1..1] Тип документа --> 
			<f103:IdentityDoc<?=(!$DocumentType_Name) ? ' nullFlavor="UNK"' :'' ?>>
				<?php if ($DocumentType_Name){ ?>
				<f103:IdentityCardTypeId xsi:type="CD" code="{DocumentType_Code}" codeSystem="1.2.643.5.1.13.13.99.2.48" codeSystemVersion="2.1" codeSystemName="Документы, удостоверяющие личность" displayName="{DocumentType_Name}"/> 
				<!-- [1..1] Серия документа -->
				<f103:Series<?=(!$Document_Ser) ? ' nullFlavor="UNK"' :'' ?>>{Document_Ser}</f103:Series>
				<!-- R [1..1] Номер документа -->
				<f103:Number>{Document_Num}</f103:Number>
				<!-- [1..1] Кем выдан документ -->
				<f103:IssueOrgName<?=(!$DocOrg_Name) ? ' nullFlavor="UNK"' :'' ?>>{DocOrg_Name}</f103:IssueOrgName>
				<!-- [1..1] Кем выдан документ, код подразделения -->
				<f103:IssueOrgCode<?=(!$DocOrg_Code) ? ' nullFlavor="UNK"' :'' ?>>{DocOrg_Code}</f103:IssueOrgCode>
				<!-- R [1..1] Дата выдачи документа -->
				<f103:IssueDate xsi:type="TS" value="{Document_begDate}"/>
				<?php } ?>
			</f103:IdentityDoc>
			<!-- В пункте 9 корешка делается запись о получателt медицинского свидетельства о рождении и его отношение к ребенку. -->
			<code code="{DeputyKind_SecCode}" codeSystem="1.2.643.5.1.13.13.99.2.14" codeSystemName="Родственные и иные связи" displayName="{DeputyKind_SecName}"/>
			<!-- R [1..1] Получатель документа (родственник или представитель новорожденного) -->
			<associatedPerson>
				<!-- R [1..1] Фамилия, Имя, Отчество получателя-->
				<!-- В пункте 9 корешка делается запись о фамилии, имени, отчестве получателя медицинского свидетельства о рождении.-->
				<name>
					<!-- R [1..1] Фамилия получателя-->
					<family>{RPerson_SurName}</family>
					<!-- R [1..1] Имя получателя-->
					<given>{RPerson_FirName}</given>
					<!-- [0..1] Отчество получателя-->
					<?php if (!empty($Person_SecName)) { ?>
						<given>{RPerson_SecName}</given>
					<?php } ?>
				</name>
			</associatedPerson>
		</associatedEntity>
	</participant>
	<!-- R [1..1] ТЕЛО ДОКУМЕНТА -->
	<component>
		<!-- R [1..1] Структурированное тело документа -->
		<structuredBody>
			<!-- R [1..1] СЕКЦИЯ: Документ-->
			<component>
				<section>
					<!-- R [1..1] код секции -->
					<code code="DOCINFO" codeSystem="1.2.643.5.1.13.13.11.1379" codeSystemVersion="1.4" codeSystemName="Секции электронных медицинских документов" displayName="Сведения о документе"/>
					<!-- R [1..1] заголовок секции -->
					<title>МЕДИЦИНСКОЕ СВИДЕТЕЛЬСТВО О РОЖДЕНИИ</title>
					<!-- R [1..1] наполнение секции -->
					<text>
						<content ID="d1">СЕРИЯ {BirthSvid_Ser} </content>
						<content ID="d2">N {BirthSvid_Num}</content><br/>
						<content>Дата выдачи {BirthSvid_GiveDateFormatted}</content>
					</text>
					<!-- R [1..1] кодирование ... Серия и номер медицинского свидетельства о рождении. -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="4120" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Серия медицинского свидетельства о рождении"/>
							<text>
								<reference value="d1"/>
							</text>
							<value xsi:type="ST">{BirthSvid_Ser}</value>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Номер медицинского свидетельства о рождении. --> 
					<entry>
						<observation classCode="OBS" moodCode="EVN"> 
							<code code="4121" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов" displayName="Номер медицинского свидетельства о рождении"/> 
							<text>
								<reference value="d2"/> 
							</text> 
							<value xsi:type="ST">{BirthSvid_Num}</value>
						</observation> 
					</entry>
				</section>
			</component>
			<!-- R [1..1] СЕКЦИЯ: Информация о матери-->
			<component>
				<section>
					<!-- R [1..1] код секции -->
					<code code="MOTHINFO" codeSystem="1.2.643.5.1.13.13.11.1379" codeSystemVersion="1.4" codeSystemName="Секции электронных медицинских документов" displayName="Информация о матери"/>
					<!-- R [1..1] заголовок секции -->
					<title>Мать</title>
					<!-- R [1..1] наполнение секции -->
					<text>
						<content>Фамилия, Имя, Отчество: {Person_SurName} {Person_FirName} {Person_SecName}</content><br/>
						<content>Дата рождения: {Person_BirthDayFormatted}</content><br/>
						<content ID="m9">Полис ОМС: {Polis_Num}</content><br/>
						<content>Адрес регистрации: {Address_Address}</content><br/>
						<content ID="m5">Местность: {KLAreaType_Name}</content><br/>
						<content ID="m6">Семейное положение матери: {FamilyStatus_Name}</content><br/>
						<content ID="m7">Образование матери: {BirthEducation_Name}</content><br/>
						<content ID="m8">Занятость матери: {BirthEmployment_Name}</content><br/>
					</text>
							<!-- R [1..1] Переключение контекста на описание информации о матери. -->
					<subject>
						<relatedSubject classCode="PRS">
							<!-- [1..1] СНИЛС матери --> 
							<f103:id <?=($Person_Snils) ? "root=\"1.2.643.100.3\" extension=\"$Person_Snils\"" : 'nullFlavor="UNK"'?>/>
							<!-- [1..1] ЛОКАЛЬНЫЙ ЭЛЕМЕНТ: Документ, удостоверяющий личность матери, серия, номер, кем выдан. --> 
							<!-- R [1..1] Тип документа -->
							<f103:IdentityDoc<?=(!$DocumentType_Name) ? ' nullFlavor="UNK"' :'' ?>>
								<?php if ($DocumentType_Name) { ?>
								<f103:IdentityCardTypeId xsi:type="CD" code="{DocumentType_Code}" codeSystem="1.2.643.5.1.13.13.99.2.48" codeSystemVersion="2.1" codeSystemName="Документы, удостоверяющие личность" displayName="{DocumentType_Name}"/> 
									<!-- [1..1] Серия документа -->
									<f103:Series<?=(!$Document_Ser) ? ' nullFlavor="UNK"' :'' ?>>{Document_Ser}</f103:Series>
									<!-- R [1..1] Номер документа -->
									<f103:Number>{Document_Num}</f103:Number>
									<!-- [1..1] Кем выдан документ -->
									<f103:IssueOrgName<?=(!$DocOrg_Name) ? ' nullFlavor="UNK"' :'' ?>>{DocOrg_Name}</f103:IssueOrgName>
									<!-- [1..1] Кем выдан документ, код подразделения -->
									<f103:IssueOrgCode<?=(!$DocOrg_Code) ? ' nullFlavor="UNK"' :'' ?>>{DocOrg_Code}</f103:IssueOrgCode>
									<!-- R [1..1] Дата выдачи документа -->
									<f103:IssueDate xsi:type="TS" value="{Document_begDate}"/>
								<?php } ?>
							</f103:IdentityDoc>
							<!-- R [1..1] Тип родственной связи -->
							<code code="1" codeSystem="1.2.643.5.1.13.13.99.2.14" codeSystemName="Родственные и иные связи" displayName="Мать"/>
							<!-- [1..1] Адрес постоянного жительства (регистрации) матери новорождённого -->
							<!-- В пункт 4 "Место постоянного жительства (регистрации)" вносятся сведения в соответствии с отметкой о регистрации по месту жительства, сделанной в документе, удостоверяющем личность матери. В случае отсутствия у матери документа, удостоверяющего ее личность пункты 2 - 4 и 6 медицинского свидетельства о рождении и 2 - 4 корешка медицинского свидетельства о рождении заполняются со слов матери. При отсутствии документа, удостоверяющего личность матери, делается запись "неизвестно". -->
							<!-- "неизвестно" указывается с использованием nullFlavor="UNK" -->
							<addr use="HP">
								<!-- [1..1] Глобальный уникальный идентификатор ФИАС --> 
								<fias:GUID<?=(empty($KLAreaGUID)) ? ' nullFlavor="UNK"' : ''?>>{KLAreaGUID}</fias:GUID>
								<!-- R [1..1] Регион РФ (республика, край, область)  -->
								<state>{KLRgn_id}</state>
								<!-- R [1..1] Район -->
								<precinct>{KLSubRgn_Name}</precinct>
								<!-- R [1..1] Город \ Село -->
								<city>{KLCity_Name}</city>
								<!-- R [1..1] Улица -->
								<streetName>{KLStreet_Name}</streetName>
								<!-- R [1..1] Дом -->
								<houseNumber>{Address_House}</houseNumber>
								<!-- R [1..1] Квартира -->
								<unitID>{Address_Flat}</unitID>
							</addr>
							<subject>
								<!-- [1..1] Фамилия, Имя, Отчество матери -->
								<!-- Пункт 2 "Фамилия, имя и отчество" заполняется полностью по данным документа, удостоверяющего личность матери, а фамилия, имя, отчество (при наличии) несовершеннолетней матери, не достигшей возраста четырнадцати лет, - на основании свидетельства о ее рождении. В случае отсутствия у матери документа, удостоверяющего ее личность пункты 2 - 4 и 6 медицинского свидетельства о рождении и 2 - 4 корешка медицинского свидетельства о рождении заполняются со слов матери. При отсутствии таких сведений делается запись "неизвестно". -->
								<!-- "неизвестно" указывается с использованием nullFlavor="UNK" -->
								<name>
									<!-- R [1..1] Фамилия матери -->
									<family>{Person_SurName}</family>
									<!-- R [1..1] Имя матери-->
									<given>{Person_FirName}</given>
									<!-- [0..1] Отчество матери -->
									<given>{Person_SecName}</given>
								</name>
								<!-- [1..1] Дата рождения матери -->
								<!-- В пункт 3 "Дата рождения" вносится число, месяц, год рождения - на основании данных, содержащихся в документе, удостоверяющем личность матери (у несовершеннолетней матери, не достигшей возраста четырнадцати лет, - в свидетельстве о рождении). В случае отсутствия у матери документа, удостоверяющего ее личность пункты 2 - 4 и 6 медицинского свидетельства о рождении и 2 - 4 корешка медицинского свидетельства о рождении заполняются со слов матери. В случае, если дата рождения матери неизвестна, во всех ячейках пункта 3 ставятся прочерки. Если известен только год рождения (определен судебно-медицинским экспертом), его указывают в соответствующих ячейках, в остальных ячейках ставятся прочерки. -->
								<!-- "неизвестно" указывается с использованием nullFlavor="UNK" -->
								<birthTime value="{Person_BirthDay}"/>
							</subject>
						</relatedSubject>
					</subject>
					<!-- R [1..1] кодирование ... Местность регистрации матери-->
					<!-- В пункте 5 "Местность" указывается принадлежность населенного пункта, являющегося местом жительства (регистрации) матери, к городской или сельской местности; -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="250" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Местность регистрации матери"/>
							<text>
								<reference value="#m5"/>
							</text>
							<value xsi:type="CD" code="{KLAreaType_Code}" codeSystem="1.2.643.5.1.13.13.11.1042" codeSystemName="Признак жителя города или села" displayName="{KLAreaType_Name}"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Семейное положение матери -->
					<!-- В пункте 6 "Семейное положение матери" указывается, состоит женщина или не состоит в зарегистрированном браке. В случае отсутствия у матери документа, удостоверяющего ее личность пункты 2 - 4 и 6 медицинского свидетельства о рождении и 2 - 4 корешка медицинского свидетельства о рождении заполняются со слов матери. При отсутствии таких сведений делается запись "неизвестно". -->
					<!-- "неизвестно" указывается с использованием nullFlavor="UNK" -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="260" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Семейное положение матери"/>
							<text>
								<reference value="#m6"/>
							</text>
							<value xsi:type="CD" code="{FamilyStatus_Code}" codeSystem="1.2.643.5.1.13.13.99.2.15" codeSystemName="Семейное положение матери" displayName="{FamilyStatus_Name}"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Образование матери -->
					<!-- В пункте 7 "Образование матери", заполняемом со слов матери, делается отметка об образовании. -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="270" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Образование матери"/>
							<text>
								<reference value="#m7"/>
							</text>
							<value xsi:type="CD" code="{BirthEducation_Code}" codeSystem="1.2.643.5.1.13.13.99.2.16" codeSystemName="Классификатор образования для медицинских свидетельств" displayName="{BirthEducation_Name}"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Занятость матери-->
					<!-- Пункт 8 "Занятость матери" заполняется со слов матери -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="280" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Занятость матери"/>
							<text>
								<reference value="#m8"/>
							</text>
							<value xsi:type="CD" code="{BirthEmployment_Code}" codeSystem="1.2.643.5.1.13.13.99.2.17" codeSystemName="Занятость матери" displayName="{BirthEmployment_Name}"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Серия и номер полиса ОМС матери -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="5010" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.17" codeSystemName="Кодируемые поля CDA документов"  displayName="Серия и номер полиса ОМС"/>
							<text>
								<reference value="#m9"/>
							</text>
							<!-- R [1..1] Серия и номер полиса ОМС матери -->
							<value xsi:type="ST">{Polis_Num}</value>
						</observation>
					</entry>

				</section>
			</component>
			<!-- R [1..1] СЕКЦИЯ: Беременность и роды-->
			<component>
				<section>
					<!-- R [1..1] код секции -->
					<code code="LABODELI" codeSystem="1.2.643.5.1.13.13.11.1379" codeSystemVersion="1.4" codeSystemName="Секции электронных медицинских документов" displayName="Беременность и роды"/>
					<!-- R [1..1] заголовок секции -->
					<title>Беременность и роды</title>
					<!-- R [1..1] наполнение секции -->
					<text>
						<content ID="L9">Срок первой явки к врачу\фельдшеру\акушерке (в неделях): {BirthSvid_Week}</content><br/>
						<content ID="L14">Роды произошли: {BirthPlace_Name}</content><br/>
						<content ID="L19">Лицо, принимавшее роды: {BirthSpecialist_Name}</content><br/>
						<content ID="L181">Тип родов: {BirthSvid_IsMnogoplodName}</content><br/>
						<content ID="L182">Число родившихся детей: {BirthSvid_PlodCount}</content><br/>
					</text>
					<!-- R [1..1] кодирование ... Срок первой явки к врачу\фельдшеру\акушерке (в неделях) -->
					<!-- Пункт 9 "Срок первой явки к врачу (фельдшеру, акушерке)" заполняется на основании сведений из индивидуальной карты беременной и родильницы (выписного эпикриза), срок первой явки к врачу (фельдшеру, акушерке) указывается в неделях. -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="290" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Срок первой явки к врачу\фельдшеру\акушерке (в неделях)"/>
							<text>
								<reference value="#L9"/>
							</text>
							<value xsi:type="PQ" value="{BirthSvid_Week}" unit="нед"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Информация о родах-->
					<entry>
						<act classCode="ACT" moodCode="EVN">
							<code code="B01.001.009" codeSystem="1.2.643.5.1.13.13.11.1070" codeSystemName="Номенклатура медицинских услуг" displayName="Ведение физиологических родов врачом-акушером-гинекологом" />
							<!-- R [1..1] кодирование ... Лицо, принимавшее роды -->
							<performer>
								<!-- R [1..1] кодирование ... Лицо, принимавшее роды (роль) -->
								<assignedEntity>
									<!-- R [1..1] Уникальный идентификатор лица, принимавшего роды -->
									<!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.70 extension = идентификатор персонала -->
									<id root="{PassportToken_tid}.100.1.1.70" extension="{MedPersonal_id}"/>
									<!-- R [1..1] Тип лица, принимавшего роды-->
									<!-- В пункте 19 "Лицо, принимавшее роды" указывается лицо, принявшее роды: врач, фельдшер (акушерка) или другое лицо -->
									<code  code="{BirthSpecialist_Code}" codeSystem="1.2.643.5.1.13.13.99.2.32" codeSystemName="Тип лица, принимавшего роды" displayName="{BirthSpecialist_Name}">
										<originalText>
											<reference value="#L19"/>
										</originalText>
									</code>
									<!-- R [1..1] кодирование ... Лицо, принимавшее роды (человек) -->
									<assignedPerson>
										<!-- R [1..1] Фамилия, Имя, Отчество автора -->
										<name>
											<!-- R [1..1] Фамилия автора-->
											<family>{MedPersonal_SurName}</family>
											<!-- R [1..1] Имя автора-->
											<given>{MedPersonal_FirName}</given>
											<!-- [0..1] Отчество автора-->
											<?php if (!empty($MedPersonal_SecName)) { ?>
												<given>{MedPersonal_SecName}</given>
											<?php } ?>
										</name>
									</assignedPerson>
								</assignedEntity>
							</performer>
							<!-- R [1..1] кодирование ... Роды произошли-->
							<!-- В пункте 14 "Роды произошли" делается отметка о том, где произошли роды: в стационаре, дома, в другом месте или неизвестно. -->
							<participant typeCode="LOC">
								<participantRole>
									<code code="{BirthPlace_Code}" codeSystem="1.2.643.5.1.13.13.99.2.30" codeSystemName="Тип места рождения ребёнка" displayName="{BirthPlace_Name}">
										<originalText>
											<reference value="#L14"/>
										</originalText>
									</code>
								</participantRole>
							</participant>
							<!-- R [1..1] кодирование ... Тип родов-->
							<!-- В пункте 18 "Ребенок родился" отмечаются позиции:
								а) "при одноплодных родах", если роды одноплодные
								б) "при многоплодных родах", если роды многоплодные. -->
							<entryRelationship typeCode="COMP">
								<observation classCode="OBS" moodCode="EVN">
									<code code="300" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Тип родов (плодность)"/>
									<text>
										<reference value="#L181"/>
									</text>
									<value xsi:type="CD" code="{BirthSvid_IsMnogoplodCode}" codeSystem="1.2.643.5.1.13.13.99.2.31" codeSystemName="Тип родов (плодность)" displayName="{BirthSvid_IsMnogoplodName}"/>
								</observation>
							</entryRelationship>
							<!-- R [1..1] кодирование ... Число родившихся детей-->
							<!-- В пункте 18 "Ребенок родился" отмечаются позиции:
								б) "при многоплодных родах", если роды многоплодные:
								в "число родившихся" указывается число родившихся при многоплодных родах. -->
							<entryRelationship typeCode="COMP">
								<observation classCode="OBS" moodCode="EVN">
									<code code="310" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Число родившихся детей"/>
									<text>
										<reference value="#L182"/>
									</text>
									<value xsi:type="INT" value="{BirthSvid_PlodCount}"/>
								</observation>
							</entryRelationship>
						</act>
					</entry>
				</section>
			</component>
			<!-- R [1..1] СЕКЦИЯ: Ребёнок-->
			<component>
				<section>
					<!-- R [1..1] код секции -->
					<code code="NBINFO" codeSystem="1.2.643.5.1.13.13.11.1379" codeSystemVersion="1.4" codeSystemName="Секции электронных медицинских документов" displayName="Информация о новорождённом"/>
					<!-- R [1..1] заголовок секции -->
					<title>Ребёнок</title>
					<!-- R [1..1] наполнение секции -->
					<text>
						<content>Ребёнок родился: {BirthSvid_GiveDateFormatted}</content><br/>
						<content>Фамилия ребёнка: {BirthSvid_ChildFamil}</content><br/>
						<content ID="N15">Пол ребёнка: {Sex_Name}</content><br/>
						<content ID="N16">Масса тела ребёнка при рождении (в граммах): {BirthSvid_Mass}</content><br/>
						<content ID="N17">Длина тела ребёнка при рождении (в сантиметрах): {BirthSvid_Height}</content><br/>
						<content ID="N10">Которым по счёту ребёнок был рождён у матери: {BirthSvid_ChildCount}</content><br/>
						<content ID="N18">Которым по счёту ребёнок был рождён в данных родах: {BirthSvid_PlodIndex}</content><br/>
						<content>Место рождения ребёнка: {LAddress_Address}</content><br/>
						<content ID="N13">Местность: {KLAreaType_Name}</content>
					</text>
					<!-- R [1..1] кодирование ... Которым по счёту ребёнок был рождён у матери-->
					<!-- Пункт 10 "Которым по счету ребенок был рожден у матери" заполняется с учетом умерших и без учета мертворожденных детей при предыдущих родах. -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="370" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Которым по счёту ребёнок был рождён у матери"/>
							<text>
								<reference value="#N9"/>
							</text>
							<value xsi:type="INT" value="{BirthSvid_ChildCount}"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Которым по счёту ребёнок был рождён в данных родах-->
					<!-- В пункте 18 "Ребенок родился" отмечаются позиции:
						б) "при многоплодных родах", если роды многоплодные:
						в "которым по счету" указывается очередность рождения ребенка при многоплодных родах. -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="380" codeSystem="1.2.643.5.1.13.13.99.2.166" codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Которым по счёту ребёнок был рождён в данных родах"/>
							<text>
								<reference value="#N18"/>
							</text>
							<value xsi:type="INT" value="{BirthSvid_PlodIndex}"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Местность рождения ребёнка-->
					<!-- В пункте 13 "Местность" указывается принадлежность населенного пункта к городской или сельской местности. -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="350" codeSystem="1.2.643.5.1.13.13.99.2.166"  codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Местность рождения ребёнка"/>
							<text>
								<reference value="#N13"/>
							</text>
							<value xsi:type="CD" code="{KLAreaType_Code}" codeSystem="1.2.643.5.1.13.13.11.1042" codeSystemName="Признак жителя города или села" displayName="{KLAreaType_Name}"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Масса тела ребёнка при рождении (в граммах) -->
					<!-- В пункте 16 "Масса тела ребенка при рождении" указывается масса тела ребенка в граммах, установленная в результате первого взвешивания, произведенного в течение первого часа его жизни -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="410" codeSystem="1.2.643.5.1.13.13.99.2.166"  codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Масса тела ребёнка при рождении (в граммах)"/>
							<text>
								<reference value="#N16"/>
							</text>
							<value xsi:type="PQ" value="{BirthSvid_Mass}" unit="г"/>
						</observation>
					</entry>
					<!-- R [1..1] кодирование ... Длина тела ребёнка при рождении (в сантиметрах)-->
					<!-- В пункте 17 "Длина тела ребенка при рождении" указывается длина тела ребенка от верхушки темени до пяток, измеренная в сантиметрах. -->
					<entry>
						<observation classCode="OBS" moodCode="EVN">
							<code code="420" codeSystem="1.2.643.5.1.13.13.99.2.166"  codeSystemVersion="1.13" codeSystemName="Кодируемые поля CDA документов"  displayName="Длина тела ребёнка при рождении (в сантиметрах)"/>
							<text>
								<reference value="#N17"/>
							</text>
							<value xsi:type="PQ" value="{BirthSvid_Height}" unit="см"/>
						</observation>
					</entry>
				</section>
			</component>
		</structuredBody>
	</component>
</ClinicalDocument>