<ClinicalDocument xmlns="urn:hl7-org:v3" xsi:schemaLocation="urn:hl7-org:v3 CDA.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <!-- ЗАГОЛОВОК ДОКУМЕНТА "Протокол лабораторного исследования" -->
    <!---->
    <!-- R [1..] Требуемый элемент. Элемент обязан иметь непустое наполнение, nullFlavor не разрешён -->
    <!-- [1..] Обязательный элемент. Элемент обязан присутствовать, но может иметь пустое наполнение с указанием причины отсутствия информации через nullFlavor -->
    <!-- [0..] Не обязательный элемент. Элемент может отсутствовать -->
    <!---->
    <!-- R [1..1] Область применения документа (Страна) -->
    <realmCode code="RU"/>
    <!-- R [1..1] Указатель на использование CDA R2 -->
    <typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>
    <!-- R [1..1] Идентификатор Шаблона документа "Протокол лабораторного исследования. Третий уровень формализации." -->
    <!-- по справочнику "Справочник шаблонов CDA документов" (OID: 1.2.643.5.1.13.13.11.1118) -->
    <templateId root="1.2.643.5.1.13.2.7.5.1.7.3"/>
    <!-- R [1..1] Уникальный идентификатор документа -->
    <!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.51 extension = идентификатор документа -->
    <id root="{PassportToken_tid}.100.1.1.51" extension="{EvnUslugaPar_id}"/>
    <!-- R [1..1] Тип документа -->
    <code code="7" codeSystem="1.2.643.5.1.13.13.11.1115" codeSystemVersion="2.3" codeSystemName="Система электронных медицинских документов" displayName="Протокол лабораторного исследования"/>
    <!-- R [1..1] Заголовок документа -->
    <title>Протокол лабораторного исследования</title>
    <!-- R [1..1] Дата создания документа (с точностью до дня)-->
    <!-- (= дата выдачи документа = дата получения документа получателем)  -->
    <effectiveTime value="{Document_DateCreate}"/>
    <!-- R [1..1] Уровень конфиденциальности медицинского документа -->
    <confidentialityCode code="N" codeSystem="1.2.643.5.1.13.13.11.1116" codeSystemVersion="1.1" codeSystemName="Уровень конфиденциальности медицинского документа" displayName="Обычный"/>
    <!-- R [1..1] Язык документа -->
    <languageCode code="ru-RU"/>
    <!-- R [1..1] Уникальный идентификатор набора версий документа -->
    <!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.50 extension = идентификатор набора версий документа -->
    <setId root="{PassportToken_tid}.100.1.1.50" extension="{EMDVersion_id}"/>
    <!-- R [1..1] Номер версии данного документа -->
    <versionNumber value="{DocVersion}"/>
    <!-- R [1..1] ДАННЫЕ О ПАЦИЕНТЕ-->
    <recordTarget>
        <!-- R [1..1] Пациент (роль) -->
        <patientRole>
            <!-- R [1..1] Уникальный идентификатор пациента в МИС -->
            <!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.10 extension = идентификатор пациента -->
            <id root="{PassportToken_tid}.100.1.1.10" extension="{Person_id}"/>
            <!-- R [1..1] СНИЛС пациента -->
            <id root="1.2.643.100.3" extension="{Person_Snils}"/>
            <!-- [1..1] Документ, удостоверяющий личность пациента, серия, номер, кем выдан. -->
            <id nullFlavor="ASKU"/>
            <!-- [1..1] Адрес постоянной регистрации пациента -->
            <addr use="H" nullFlavor="ASKU"/>
            <!-- [1..1] Адрес фактического места жительства пациента -->
            <addr use="H" nullFlavor="ASKU"/>
            <!-- [0..1] Телефон пациента -->
            <!-- [0..*] Прочие контакты пациента (мобильный телефон) -->
            <!-- [0..*] Прочие контакты пациента (электронная почта) -->
            <!-- R [1..1] Пациент (человек)  -->
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
                <administrativeGenderCode code="{Sex_code}" codeSystem="1.2.643.5.1.13.13.11.1040" codeSystemVersion="2.1" codeSystemName="Пол пациента" displayName="{Sex_Name}"/>
                <!-- R [1..1] Дата рождения пациента -->
                <birthTime value="{Person_BirthDay}"/>
            </patient>
            <!-- R [1..1] Медицинская организация (индивидуальный предприниматель), оформившая протокол лабораторного исследования -->
			<providerOrganization>
				<!-- [1..1] Идентификатор медицинской организации ... Код по регистру МО -->
				<!-- организации - по справочнику «Реестр медицинских организаций Российской Федерации» (OID: 1.2.643.5.1.13.13.11.1461) -->
				<!-- для индивидуальных предпринимателей - указание на отсутствие кода, nullFlavor="OTH" -->
				<id root="{PassportToken_tid}"/>
				<!-- R [1..1] Наименование медицинской организации или ФИО Индивидуального предпринимателя -->
				<!-- При заполнении медицинского свидетельства о рождении в медицинской организации указывается полное наименование медицинской организации. -->
				<!-- В случае заполнения медицинского свидетельства о рождении индивидуальным предпринимателем указывается его фамилия, имя, отчество -->
				<name>{Org_Name}</name>
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
        <time value="{EvnUslugaPar_signDT}"/>
        <!-- R [1..1] АВТОР (роль) -->
        <assignedAuthor>
            <!-- R [1..1] Уникальный идентификатор автора в МИС -->
            <!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.70 extension = идентификатор персонала -->
            <id root="{PassportToken_tid}.100.1.1.70" extension="{SignMedStaffFact_id}"/>
            <!-- [0..1] СНИЛС автора -->
            <!-- R [1..1] Код должности автора-->
            <code code="{Sig_MedPost_Code}" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemVersion="2.2" codeSystemName="Должности работников организаций медицинского и фармацевтического профиля" displayName="{Sig_MedPost_Name}"/>
            <!-- [0..1] Адрес автора документа -->
            <!-- [0..1] Телефон автора -->
            <!-- [0..*] Прочие контакты автора (мобильный телефон) -->
            <!-- [0..*] Прочие контакты автора (электронная почта) -->
            <!-- R [1..1] АВТОР (человек) -->
            <assignedPerson>
                <!-- R [1..1] Фамилия, Имя, Отчество автора -->
				<name>
					<!-- R [1..1] Фамилия автора-->
					<family>{Sig_MedPersonal_SurName}</family>
					<!-- R [1..1] Имя автора-->
					<given>{Sig_MedPersonal_FirName}</given>
					<!-- [0..1] Отчество автора-->
					<?php if (!empty($Sig_MedPersonal_SecName)) { ?>
						<given>{Sig_MedPersonal_SecName}</given>
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
                <!-- организации - по справочнику «Регистр медицинских организаций Российской Федерации. Версия 2» (OID: 1.2.643.5.1.13.2.1.1.178) -->
                <!-- индивидуальные предприниматели - указание на отсутствие кода, nullFlavor="OTH" -->
                <id root="{PassportToken_tid}"/>
                <!-- R [1..1] Наименование организации-владельца документа -->
                <name>{Org_Name}</name>
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
    <!-- R [1..1] ДАННЫЕ О ПОЛУЧАТЕЛЕ ДОКУМЕНТА - ИЭМК / МЗ РФ-->
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
        <time value="{EvnUslugaPar_signDT}"/>
        <!-- R [1..1] Факт наличия подписи на документе -->
        <signatureCode code="S"/>
        <!-- R [1..1] Лицо, придавшее юридическую силу документу (роль) -->
        <assignedEntity>
            <!-- R [1..1] Уникальный идентификатор лица, придавшего юридическую силу документу в МИС -->
            <!-- по правилу: root = OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.70 extension = идентификатор персонала -->
            <id root="{PassportToken_tid}.100.1.1.70" extension="{Sig_MedPersonal_id}"/>
            <!-- [0..1] СНИЛС лица, придавшего юридическую силу документу -->
            <!-- R [1..1] Код должности лица, придавшего юридическую силу документу -->
            <code code="{Sig_MedPost_Code}" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemVersion="2.2" codeSystemName="Должности работников организаций медицинского и фармацевтического профиля" displayName="{Sig_MedPost_Name}"/>
            <!-- [0..1] Адрес лица, придавшего юридическую силу документу -->
            <!-- [0..1] Телефон лица, придавшего юридическую силу документу -->
            <!-- [0..*] Прочие контакты лица, придавшего юридическую силу документу (мобильный телефон) -->
            <!-- [0..*] Прочие контакты лица, придавшего юридическую силу документу (электронная почта) -->
            <!-- R [1..1] Лицо, придавшее юридическую силу документу (человек) -->
            <assignedPerson>
                <!-- R [1..1] Фамилия, Имя, Отчество автора -->
				<name>
					<!-- R [1..1] Фамилия автора-->
					<family>{Sig_MedPersonal_SurName}</family>
					<!-- R [1..1] Имя автора-->
					<given>{Sig_MedPersonal_FirName}</given>
					<!-- [0..1] Отчество автора-->
					<?php if (!empty($Sig_MedPersonal_SecName)) { ?>
						<given>{Sig_MedPersonal_SecName}</given>
					<?php } ?>
				</name>
            </assignedPerson>
            <!-- [0..1] Место работы лица, придавшего юридическую силу документу -->
        </assignedEntity>
    </legalAuthenticator>
    <!-- [0..1] СВЕДЕНИЯ О СТРАХОВОМ ПОЛИСЕ ОМС -->
    <!-- [0..1] СВЕДЕНИЯ О НАПРАВИВШЕМ ЛИЦЕ И ОРГАНИЗАЦИИ -->
    <!-- [0..1] СВЕДЕНИЯ О НАПРАВЛЕНИИ -->
    <!-- R [1..1] СВЕДЕНИЯ О ДОКУМЕНТИРУЕМОМ СОБЫТИИ-->
    <documentationOf>
        <!-- R [1..1] Проведённое исследование -->
        <serviceEvent>
            <!-- R [1..1] Даты исследования -->
            <effectiveTime>
                <!-- R [1..1] Дата начала исследования (доставка материала в лабораторию) -->
                <low value="{EvnLabSample_DelivDT}"/>
                <!-- R [1..1] Дата окончания исследования -->
                <high value="{EvnLabSample_StudyDT}"/>
            </effectiveTime>
            <!-- [1..*] СВЕДЕНИЯ ОБ ИСПОЛНИТЕЛЯХ ИССЛЕДОВАНИЯ -->
            <performer typeCode="PPRF">
                <assignedEntity>
                    <!-- R [1..1] Уникальный идентификатор исполнителя -->
                    <id root="{PassportToken_tid}.100.1.1.70" extension="{EvnLabSample_Doctor_id}"/>
                    <!-- [0..1] СНИЛС исполнителя -->
                    <!-- R [1..1] Должность исполнителя -->
                    <code code="{EvnLabSampleMedPost_Code}" codeSystem="1.2.643.5.1.13.13.11.1002" codeSystemVersion="2.2" codeSystemName="Должности работников организаций медицинского и фармацевтического профиля" displayName="{EvnLabSampleMedPost_Name}"/>
                    <!-- [0..1] Адрес исполнителя -->
                    <!-- [0..1] Телефон исполнителя -->
					<!-- R [1..1] Исполнитель -->
                    <assignedPerson>
                        <!-- R [1..1] Фамилия, Имя, Отчество исполнителя -->
                        <name>
                            <!-- R [1..1] Фамилия -->
                            <family>{EvnLabSample_Doctor_SurName}</family>
                            <!-- R [1..1] Имя -->
                            <given>{EvnLabSample_Doctor_FirName}</given>
							<!-- [0..1] Отчество -->
							<?php if (!empty($EvnLabSample_Doctor_SecName)) { ?>
							<given>{EvnLabSample_Doctor_SecName}</given>
							<?php } ?>
                        </name>
                    </assignedPerson>
                    <!-- R [1..1] Место работы исполнителя -->
                    <representedOrganization>
                        <!-- [1..1] Идентификатор организации исполнителя -->
                        <!-- организации - по справочнику «Регистр медицинских организаций Российской Федерации. Версия 2» (OID: 1.2.643.5.1.13.2.1.1.178) -->
                        <!-- индивидуальные предприниматели - указание на отсутствие кода, nullFlavor="NI" -->
                        <id root="{EvnLabSample_PassportToken_tid}"/>
                        <!-- R [1..1] Наименование организации исполнителя -->
                        <name>{EvnLabSampleOrg_Name}</name>
                        <!-- R [1..1] Телефон организации исполнителя -->
                        <telecom value="tel:{EvnLabSampleLpu_Phone}" use="WP"/>
                        <!-- R [1..1] Адрес организации исполнителя -->
				<addr>
					<!-- R [1..1] Адрес текстом -->
					<streetAddressLine>{EvnLabSampleLAddress_Address}</streetAddressLine>
					<!-- R [1..1] Регион РФ -->
					<state>{EvnLabSampleLKLRgn_id}</state>
				</addr>
                    </representedOrganization>
                </assignedEntity>
            </performer>
        </serviceEvent>
    </documentationOf>
    <!-- R [1..1] СВЕДЕНИЯ О СЛУЧАЕ ОКАЗАНИЯ МЕДИЦИНСКОЙ ПОМОЩИ -->
    <componentOf>
        <!-- R [1..1] Случай оказания медицинской помощи -->
        <encompassingEncounter>
            <!-- R [1..1] Уникальный идентификатор случая оказания медицинской помощи -->
            <id root="{MedHelpOID}.100.1.1.15" extension="{MedHelpId}"/>
            <!-- R [1..1] Даты начала и окончания случая -->
            <effectiveTime>
               <low value="{MedHelpStart}"/>
                <!-- R [1..1] Дата окончания исследования -->
                <high value="{MedHelpEnd}"/>
            </effectiveTime>
        </encompassingEncounter>
    </componentOf>
    <!-- ТЕЛО ДОКУМЕНТА -->
    <component>
        <!-- R [1..1] Структурированное тело, соответствующее третьему уровню CDA-->
        <structuredBody>
            <!-- R [1..1] Информация об исследованных материалах-->
            <component>
                <section>
                    <!-- R [1..1 Информация о коде секции и кодификаторе-->
                    <code code="SPECIMENS" codeSystem="1.2.643.5.1.13.13.11.1379" codeSystemVersion="1.4" codeSystemName="Секции электронных медицинских документов" displayName="Информация об исследованных материалах"/>
                    <!-- R [1..1] Заголовок секции-->
                    <title>Информация об исследованных материалах</title>
                    <!-- R [1..1] Текстовая информация об исследованных материалах-->
                    <text>Нет данных</text>
                    <!-- R [1..1] Информация об исследованных материалах -->
                    <entry>
                        <organizer classCode="CLUSTER" moodCode="EVN">
                            <statusCode code="completed"/>
                            <component>
                                <!-- R [1..1] Данные о проведенной процедуре-->
                                <procedure classCode="PROC" moodCode="EVN">
                                    <!-- R [1..1] Набор значений, Справочник, OID:1.2.643.5.1.13.13.11.1070 «Номенклатура медицинских услуг»-->
                                    <!--если есть значения, то указывается код процедуры из НМУ1664. Если нет - впечатывается nullFlavor=OTH-->
                                    <code code="{UslugaComplex_Code}" codeSystem="1.2.643.5.1.13.13.11.1070" codeSystemVersion="2.3" codeSystemName="Номенклатура медицинских услуг" displayName="{UslugaComplex_Name}"/>
                                    <!-- R [1..1] Статус выполнения процедуры-->
                                    <statusCode code="completed"/>
                                    <!-- R [1..1] Время забора материала. Если материал собирался некоторый интервал времени, то этот интервал указывается явным образом-->
                                    <!--ДОЛЖНО быть указано с точностью до дня, СЛЕДУЕТ указывать с точностью до минут. Если указано с точностью до минут, то ДОЛЖНА быть указанная временная зона. МОЖНО уточнить время до секунд.-->
                                    <effectiveTime value="{EvnLabSample_DelivDT}"/>
								<?php foreach ($uslugi as $item){ 
									$bcode=$EvnLabRequest_BarCodes[$item["EvnLabSample_id"]];?>
                                    <!-- R [1..*] информация об образце исследования -->
                                    <specimen>
                                        <specimenRole>
                                            <id root="{EvnLabSample_PassportToken_tid}.100.1.1.66" extension="<?=$bcode?>"/>
                                            <specimenPlayingEntity classCode="ENT" determinerCode="INSTANCE">
                                                <code code="{LabTestMaterial_Code}" codeSystem="1.2.643.5.1.13.13.11.1081" codeSystemVersion="1.1" codeSystemName="Федеральный справочник лабораторных исследований. Справочник материалов для проведения лабораторного исследования." displayName="{LabTestMaterial_Name}"/>
                                                <quantity value="1" unit="шт"/>
                                                <desc>Штрихкод: <?=$bcode?></desc>
                                            </specimenPlayingEntity>
                                        </specimenRole>
                                    </specimen>
								<?php } ?>
                                    <!-- [0..*] Если известны сотрудники производившие забор материала для исследования, их следует указать-->
                                    <performer>
                                        <!-- R [1..1] Сведения о человеке, осуществлявшем забор материала-->
                                        <assignedEntity>
                                            <!--R [1..1] Уникальный идентификатор назначенного лица в МИС-->
                                            <!--ДОЛЖЕН быть заполнен синтаксически корректным OID (должен соответствовать регулярному выражению ([0-2])(.([1-9][0-9]*|0))+).-->
                                            <!--ДОЛЖЕН быть сформирован по правилу: «OID_медицинской_организации.100.НомерМИС.НомерЭкзМИС.70»-->
                                            <id root="{EvnLabSample_PassportToken_tid}.100.1.1.70" extension="{EvnLabSample_Doctor_id}"/>
                                        </assignedEntity>
                                    </performer>
                                    <!-- [0..*] Описание образца, полученного в ходе обработки материала.-->
                                </procedure>
                            </component>
                        </organizer>
                    </entry>
                </section>
            </component>
            <!-- R [1..1] Информация об использованном оборудовании и расходных материалах-->
            <component>
                <section>
                    <!-- R [1..1 Информация о коде секции и кодификаторе-->
                    <code code="ANALYSERS" codeSystem="1.2.643.5.1.13.13.11.1379" codeSystemVersion="1.4" codeSystemName="Секции электронных медицинских документов" displayName="Информация об использованном оборудовании и расходных материалах"/>
                    <!-- R [1..1] Заголовок секции-->
                    <title>Оборудование и расходные материалы</title>
                    <!-- R [1..1] Текстовая информация об исследованных материалах-->
                    <text>{Analyzer_Name}</text>
                    <!--R [1..1]  Формализованное перечисление использованного оборудования и расходных материалов -->
                    <entry>
                        <organizer classCode="CLUSTER" moodCode="EVN">
                            <statusCode code="completed"/>
                            <!-- [1..*]  Устройства: анализаторы и прочее оборудование: typeCode="DEV" -->
                            <participant typeCode="DEV">
                                <participantRole classCode="ROL" nullFlavor="UNK"/>
                            </participant>
                            <!-- [1..*]  Расходные материалы: наборы для определения, тест полоски, катриджи и т.п. : typeCode="CSM" -->
                            <participant typeCode="CSM">
                                <participantRole classCode="ROL" nullFlavor="UNK"/>
                            </participant>
                        </organizer>
                    </entry>
                </section>
            </component>
            <!-- R [1..1] Результаты лабораторных исследований -->
            <component>
                <section>
                    <!-- R [1..1] код секции -->
                    <code code="RESLAB" codeSystem="1.2.643.5.1.13.13.11.1379" codeSystemVersion="1.4" codeSystemName="Секции электронных медицинских документов" displayName="Результаты лабораторных исследований"/>
                    <!-- R [1..1] заголовок секции -->
                    <title>Результаты исследования</title>
                    <!-- [1..1] наполнение секции -->
                    <text>
                        <table width="100%">
                            <col width="22%"/>
                            <col width="13%"/>
                            <col width="10%"/>
                            <col width="15%"/>
                            <col width="12%"/>
                            <col width="8%"/>
                            <col width="17%"/>
							<col width="17%"/>
                            <thead>
                                <tr>
                                    <th>Название теста</th>
                                    <th>Результат</th>
                                    <th>Единицы измерения</th>
                                    <th>Референтный диапазон</th>
                                    <th>Оборудование</th>
                                    <th>Дата</th>
                                    <th>Исполнитель</th>
									<th>Комментарий</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8">
                                        <content styleCode="Bold">{UslugaComplex_Name}</content>
                                    </td>
                                </tr>
						<?php foreach ($uslugi as $item){ ?>
                                <tr>
                                    <td><?=$item["Analize_Name"]?></td>
                                    <td><?=$item["UslugaTest_ResultValue"]?></td>
									<td><?=$item["UslugaTest_ResultUnit"]?></td>
									<td><?=$item["RefLimits"]?></td>
                                    <td><?=$item["Analyzer_Name"]?></td>
                                    <td><?=date("d.m.Y H:i", strtotime($item["UslugaTest_setDT"]))?></td>
                                    <td>{EvnLabSampleMedPost_Name} {EvnLabSample_Doctor_SurName} {EvnLabSample_Doctor_FirName} {EvnLabSample_Doctor_SecName}</td>
									<td><?=$item["UslugaTest_Comment"]?></td>
                                </tr>
						<?php } ?>
                            </tbody>
                        </table>
                    </text>
                <?php  /*   <entry>
                        <organizer classCode="CLUSTER" moodCode="EVN">
                            <statusCode code="completed"/>
                            <!-- R [1..*] Кодирование лабораторного исследования  -->
                            <component>
                                <organizer classCode="BATTERY" moodCode="EVN">
                                    <!-- R [1..1] Указание произвольной группировки исследований -->
                                    <code>
                                        <originalText>Иммуногематологическое исследование</originalText>
                                    </code>
                                    <statusCode code="completed"/>
                                    <!-- R [1..1]  Время выполнения лабораторного исследования -->
                                    <effectiveTime value="{EvnLabSample_StudyDT}"/>
                                    <!-- R [1..*] Кодирование лабораторного параметра -->
									<?php
									foreach ($uslugi as $item){ ?>
                                    <component>
                                        <observation classCode="OBS" moodCode="EVN">
                                            <!-- R [1..1] Лабораторный параметр: группа крови АВ0, качественный показатель с кодированием результатов по федеральному справочнику -->
                                            <code code="123>" codeSystem="1.2.643.5.1.13.13.11.1080" codeSystemVersion="3.6" codeSystemName="Федеральный справочник лабораторных исследований. Справочник лабораторных тестов" displayName="123"/>
                                            <!-- R [1..1] Кодирование статуса исследования параметра -->
                                            <statusCode code="completed"/>
                                            <!-- [1..1] Кодирование результата -->
                                            <value xsi:type="CD" code="103" codeSystem="1.2.643.5.1.13.13.11.1061" codeSystemVersion="1.3" codeSystemName="Группы крови для учета сигнальной информации о пациенте" displayName="B(III) без уточнения подгруппы"/>
                                            <!-- [1..1] Код интерпретации результата -->
                                            <interpretationCode nullFlavor="NA"/>
                                            <!-- R [1..1] Кодирование материала исследования -->
                                            <specimen>
                                                <specimenRole>
                                                    <!-- R [1..1] Идентификатор материала исследования -->
                                                    <!-- Пробирка для иммуногематологических исследований с ACD. Штрихкод: 1234567890 -->
                                                    <id root="{EvnLabSample_PassportToken_tid}.100.1.1.66" extension="{EvnLabRequest_BarCodes}"/>
                                                </specimenRole>
                                            </specimen>
                                            <!-- R [1..1] исполнитель (роль) -->
                                            <performer>
                                                <assignedEntity>
                                                    <!-- R [1..1] Уникальный идентификатор исполнителя - ссылка на раздел doсumentationOf заголовка -->
                                                    <!--ссылка на врача КЛД Смирнову -->
                                                    <id root="{EvnLabSample_PassportToken_tid}.100.1.1.70" extension="{EvnLabSample_Doctor_id}"/>
                                                </assignedEntity>
                                            </performer>
                                            <!-- [1..*] Информация об использованном оборудовании и расходных материалах-->
                                            <participant typeCode="CSM">
                                                <!-- для реактивов CSM-->
                                                <participantRole classCode="ROL" nullFlavor="NI"/>
                                            </participant>
                                            <!-- [0..1] Кодирование референтного интервала -->
                                        </observation>
                                    </component>
									<?php }?>
                                </organizer>
                            </component>
                            <!-- [0..1] Кодирование общего заключения по проведенным исследованиям  -->
                        </organizer>
                    </entry> */ ?>
                </section>
            </component>
            <!-- [0..1] СЕКЦИЯ: Перечень оказанных медицинских услуг-->
        </structuredBody>
    </component>
</ClinicalDocument>
