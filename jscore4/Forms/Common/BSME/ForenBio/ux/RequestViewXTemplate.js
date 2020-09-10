/**
* RequestViewXTemplate - расширение XTemplate для просомотра заявки в АРМах службы судебно биологического отделения с молекулярно-генетической лабораторией
*/
Ext.define('common.BSME.ForenBio.ux.RequestViewXTemplate',{
	extend: 'Ext.XTemplate',
	constructor: function (config) {
		this.callParent([
			'<div class="expertisePanel">',
				'<div class="expertiseHeader">',
					'<p>Экспертиза</p>',
				'</div>',
				'<div class="expertiseBody">',
					'<tpl if="EvnDirectionForensic_id==0">',
						'<p class="expertiseFieldTitle"><span>Статус:</span></p>',
						'<p class="expertiseFieldValue">Не назначена</p>',
					'<tpl else>',
						'<p class="expertiseFieldTitle"><span>Время экспертизы</span></p>',
						'<p class="expertiseFieldValue">{EvnForensic_Time}</p>',
						'<p class="expertiseFieldTitle"><span>Эксперт</span></p>',
						'<p class="expertiseFieldValue">{Expert_Fin}</p>',
						'<tpl if="ActVersionForensic_id!=0">',
							'<p class="expertiseFieldTitle"><span>Заключение</span></p>',
							'<p class="expertiseFieldValueBlue">{ActVersionForensic_Num}</p>',
						'</tpl>',
//						'<p class="expertiseFieldTitle"><span>Статус</span></p>',
//						'<p class="expertiseFieldValueBlue">{EvnForensic_Status}</p>',
					'</tpl>',
				'</div>',
			'</div>',
			'<div class="request-view">',
				'<h1>Заявка {EvnForensic_Num} <span>от {EvnForensic_insDT}</span></h1>',
				
				
				'<tpl if="EvnStatusHistory_Cause!=\'\'">',
					'<p><span class="label">Комментарий заведующего: </span><span class="textData">{EvnStatusHistory_Cause}</span></p>',
				'</tpl>',

				
				'<tpl if="EvnForensicGeneticEvid_id!=0">',
					'<p class="journal-header">Журнал регистрации вещественных доказательств и документов к ним в лаборатории</p><hr/>',
					'<p><span class="label">№ основного сопроводительного документа:</span><span class="textData">{EvnForensicGeneticEvid_AccDocNum}</span></p>',
					'<p><span class="label">Дата основного сопроводительного документа:</span><span class="textData">{EvnForensicGeneticEvid_AccDocDate}</span></p>',
					'<p><span class="label">Кол-во листов документов:</span><span class="textData">{EvnForensicGeneticEvid_AccDocNumSheets}</span></p>',
					'<p><span class="label">Учреждение направившего:</span><span class="textData">{Org_Name}</span></p>',
					'{[ this.getEvidPersons(values) ]}',
					'{[ this.getEvidences(values) ]}',
					'<p><span class="label">Краткие обстоятельства дела:</span><span class="textData">{EvnForensicGeneticEvid_Facts}</span></p>',
					'<p><span class="label">Цель экспертизы:</span><span class="textData">{EvnForensicGeneticEvid_Goal}</span></p>',
				'</tpl>',

				'<tpl if="EvnForensicGeneticSampleLive_id!=0">',
					'<p class="journal-header">Журнал регистрации биологических образцов, изъятых у живых лиц в лаборатории</p><hr/>',
					'<p><span class="label">Дата/Время изъятия образцов:</span><span class="textData">{EvnForensicGeneticSampleLive_TakeDate}</span></p>',
					'<p><span class="label">Исследуемое лицо:</span><span class="textData">{EvnForensicGeneticSampleLive_Person_FIO}</span></p>',
					'<p><span class="label">Основания для получения образцов:</span><span class="textData">{EvnForensicGeneticSampleLive_Basis}</span></p>',
					'<p><span class="label">Документ, удостоверяющий личность:</span><span class="textData">{EvnForensicGeneticSampleLive_VerifyingDoc}</span></p>',
					'<tpl if="EvnForensicGeneticSampleLive_StudyDate!=null">',
						'<p><span class="label"> Дата исследования образца </span><span class="textData"> {EvnForensicGeneticSampleLive_StudyDate}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSampleLive_MedPersonal_Fin!=null">',
						'<p><span class="label"> Изъявший биоматериалы </span><span class="textData"> {EvnForensicGeneticSampleLive_MedPersonal_Fin}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSampleLive_IsConsent!=0">',
						'<p><span class="label"> Согласие </span><span class="textData"> {EvnForensicGeneticSampleLive_IsConsent}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSampleLive_ResultDate!=null">',
						'<p><span class="label"> Дата выдачи результата	 </span><span class="textData"> {EvnForensicGeneticSampleLive_ResultDate}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSampleLive_IsIsosTestEA!=0">',
						'<p><span class="label"> Тест-эритроцит А  </span><span class="textData"> {EvnForensicGeneticSampleLive_IsIsosTestEA}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSampleLive_IsIsosTestEB!=0">',
						'<p><span class="label"> Тест-эритроцит B </span><span class="textData"> {EvnForensicGeneticSampleLive_IsIsosTestEB}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSampleLive_IsIsosCyclAntiA!=0">',
						'<p><span class="label"> Циклон Анти-А	 </span><span class="textData"> {EvnForensicGeneticSampleLive_IsIsosCyclAntiA}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSampleLive_IsIsosCyclAntiB!=0">',
						'<p><span class="label"> Циклон Анти-B	 </span><span class="textData"> {EvnForensicGeneticSampleLive_IsIsosCyclAntiB}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSampleLive_Result!=\'\'">',
						'<p><span class="label"> Результаты определения групп по исследованым системам	 </span><span class="textData"> {EvnForensicGeneticSampleLive_Result}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSampleLive_IsosOtherSystems!=\'\'">',
						'<p><span class="label"> Другие системы	(изосерология): </span><span class="textData"> {EvnForensicGeneticSampleLive_IsosOtherSystems}</span></p>',
					'</tpl>',
					'{[ this.getBioSamples(values) ]}',
					'</br>',
				'</tpl>',

				'<tpl if="EvnForensicGeneticCadBlood_id!=0">',
					'<p class="journal-header">Журнал направлений на исследование трупной крови</p><hr/>',
					'<p><span class="label">Направивший эксперт</span><span class="textData">  {EvnForensicGeneticCadBlood_MedPersonal_Fin}</span></p>',
					'<p><span class="label">Исследуемое лицо</span><span class="textData">  {EvnForensicGeneticCadBlood_Person_Fin}</span></p>',
					'<p><span class="label">Дата взятия </span><span class="textData"> {EvnForensicGeneticCadBlood_TakeDate}</span></p>',
					'<tpl if="EvnForensicGeneticCadBlood_ForDate!=null">',
						'<p><span class="label">Дата поступления </span><span class="textData"> {EvnForensicGeneticCadBlood_ForDate}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_StudyDate!=null">',
						'<p><span class="label">Дата исследования</span><span class="textData">  {EvnForensicGeneticCadBlood_StudyDate}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_Result!=null">',
						'<p><span class="label">Результат определения групп по исследованным системам </span><span class="textData"> {EvnForensicGeneticCadBlood_Result}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_IsIsosTestEA!=0">',
						'<p><span class="label">Тест-эритроцит A </span><span class="textData"> {EvnForensicGeneticCadBlood_IsIsosTestEA}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_IsIsosTestEB!=0">',
						'<p><span class="label">Тест-эритроцит B </span><span class="textData"> {EvnForensicGeneticCadBlood_IsIsosTestEB}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_IsIsosTestIsoB!=0">',
						'<p><span class="label">Изосыворотка бетта </span><span class="textData"> {EvnForensicGeneticCadBlood_IsIsosTestIsoB}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_IsIsosTestIsoA!=0">',
						'<p><span class="label">Изосыворотка альфа </span><span class="textData"> {EvnForensicGeneticCadBlood_IsIsosTestIsoA}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_IsIsosAntiA!=0">',
						'<p><span class="label">Имунная сыворотка Анти-А </span><span class="textData"> {EvnForensicGeneticCadBlood_IsIsosAntiA}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_IsIsosAntiB!=0">',
						'<p><span class="label">Имунная сыворотка Анти-B </span><span class="textData"> {EvnForensicGeneticCadBlood_IsIsosAntiB}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_IsIsosAntiH!=0">',
						'<p><span class="label">Имунная сыворотка Анти-H </span><span class="textData"> {EvnForensicGeneticCadBlood_IsIsosAntiH}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_MatCondition!=\'\'">',
						'<p><span class="label">Упаковка, состояние, количество материала: </span><span class="textData">{EvnForensicGeneticCadBlood_MatCondition}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticCadBlood_IsosOtherSystems!=\'\'">',
						'<p><span class="label">Другие системы (изосерология): </span><span class="textData">{EvnForensicGeneticCadBlood_IsosOtherSystems}</span></p>',
					'</tpl>',
				'</tpl>',

				'<tpl if="EvnForensicGeneticGenLive_id!=0">',
					'<p class="journal-header">Журнал регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования</p><hr/>',
					'<p><span class="label">Дата изъятия образцов:</span><span class="textData">{EvnForensicGeneticGenLive_TakeDate}</span></p>',
					'<tpl if="EvnForensicGeneticGenLive_MedPersonal_Fin!=null">',
						'<p><span class="label">Проводивший изъятие </span><span class="textData"> {EvnForensicGeneticGenLive_MedPersonal_Fin}</span></p>',
					'</tpl>',
					'{[ this.getGeneticGenLivePersons(values) ]}',
					'{[ this.getGeneticGenLiveBioSamples(values) ]}',
					'<p><span class="label">Краткие обстоятельства дела:</span><span class="textData">{EvnForensicGeneticGenLive_Facts}</span></p>',
				'</tpl>',
				
				'<tpl if="EvnForensicGeneticSmeSwab_id!=0">',
					'<p class="journal-header">Журнал регистрации исследований мазков и тампонов в лаборатории</p><hr/>',				
					'<p><span class="label">Исследуемое лицо:</span><span class="textData">{EvnForensicGeneticSmeSwab_Person_Fio}</span></p>',
					'<p><span class="label">Основания для получения образцов:</span><span class="textData">{EvnForensicGeneticSmeSwab_Basis}</span></p>',
					'<tpl if="EvnForensicGeneticSmeSwab_DelivDate!=null">',
						'<p><span class="label">Дата и время поступления образцов </span><span class="textData"> {EvnForensicGeneticSmeSwab_DelivDate}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSmeSwab_MedPersonal_Fin!=null">',
						'<p><span class="label">Изъявшее лицо </span><span class="textData"> {EvnForensicGeneticSmeSwab_MedPersonal_Fin}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSmeSwab_BegDate!=null">',
						'<p><span class="label">Дата начала исследования </span><span class="textData"> {EvnForensicGeneticSmeSwab_BegDate}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSmeSwab_EndDate!=null">',
						'<p><span class="label">Дата окончания исследования </span><span class="textData"> {EvnForensicGeneticSmeSwab_EndDate}</span></p>',
					'</tpl>',
					'<tpl if="EvnForensicGeneticSmeSwab_Comment!=null">',
						'<p><span class="label">Примечание </span><span class="textData"> {EvnForensicGeneticSmeSwab_Comment}</span></p>',
					'</tpl>',
					'{[ this.getGeneticSmeSwabSamples(values) ]}',
				'</tpl>',
				'<tpl if="ActVersionForensic_id!=0">',
					'<p class="journal-header">Заключение (акт) эксперта </p><hr/>',				
					'<p><span class="label">Номер акта: </span><span class="textData">{ActVersionForensic_Num}</span></p>',
					'{ActVersionForensic_Text}',
				'</tpl>',

				
				
			'</div>',
			{
				getEvidPersons: function(val) {

					var html = '';
					var i,el;
					
					if (val.EvnForensicGeneticEvidLink && val.EvnForensicGeneticEvidLink.length) {
						for (i = 0; i < val.EvnForensicGeneticEvidLink.length; i++) {
							el = val.EvnForensicGeneticEvidLink[i];
							if (el.Person_Fio && el.EvnForensicGeneticEvidLink_IsVic) {
								html += '<p><span class="label">'+((el.EvnForensicGeneticEvidLink_IsVic==2)?'Потерпервший':'Обвиняемый')+'</span><span class="textData">'+el.Person_Fio+'</span></p>'
							}

						};

					};
					return html;
				},
				getEvidences: function(val) {
					var count = 1;
					var html = '';
					var i,el;
					
					if (val.EvnForensicGeneticEvid_Evidence && val.EvnForensicGeneticEvid_Evidence.length) {
						for (i = 0; i < val.EvnForensicGeneticEvid_Evidence.length; i++) {
							el = val.EvnForensicGeneticEvid_Evidence[i];
							if (el.Evidence_Name) {
								html += '<p><span class="label">'+'Вещественное доказательство #'+count+'</span><span class="textData">'+el.Evidence_Name+'</span></p>'
							}
							count++;

						};

					};
					return html;
				},
				getBioSamples: function(val) {
					var count = 1;
					var html = '';
					var i,el;
					
					if (val.EvnForensicGeneticSampleLive_BioSample && val.EvnForensicGeneticSampleLive_BioSample.length) {
						for (i = 0; i < val.EvnForensicGeneticSampleLive_BioSample.length; i++) {
							el = val.EvnForensicGeneticSampleLive_BioSample[i];
							if (el.Evidence_Name) {
								html += '<p><span class="label">'+'Биологический образец #'+count+'</span><span class="textData">'+el.Evidence_Name+'</span></p>'
							}
							count++;
						};

					};
					return html;
				},
				getGeneticGenLivePersons: function(val) {
					var count = 1;
					var html = '';
					var i,el;
					
					if (val.EvnForensicGeneticGenLiveLink && val.EvnForensicGeneticGenLiveLink.length) {
						for (i = 0; i < val.EvnForensicGeneticGenLiveLink.length; i++) {
							el = val.EvnForensicGeneticGenLiveLink[i];
							if (el.Person_Fio) {
								html += '<p><span class="label">'+'Исследуемое лицо #'+count+'</span><span class="textData">'+el.Person_Fio+'</span></p>'
							}

						};

					};
					return html;
				},
				getGeneticGenLiveBioSamples: function(val) {
					var count = 1;
					var html = '';
					var i,el;
					
					if (val.EvnForensicGeneticGenLive_BioSample && val.EvnForensicGeneticGenLive_BioSample.length) {
						for (i = 0; i < val.EvnForensicGeneticGenLive_BioSample.length; i++) {
							el = val.EvnForensicGeneticGenLive_BioSample[i];
							if (el.Evidence_Name) {
								html += '<p><span class="label">'+'Биологический образец #'+count+'</span><span class="textData">'+el.Evidence_Name+'</span></p>'
							}
							count++;
						};

					};
					return html;
				},
				getGeneticSmeSwabSamples: function(val) {
					var count = 1;
					var html = '';
					var i,el;
					
					if (val.EvnForensicGeneticSmeSwab_Sample && val.EvnForensicGeneticSmeSwab_Sample.length) {
						for (i = 0; i < val.EvnForensicGeneticSmeSwab_Sample.length; i++) {
							el = val.EvnForensicGeneticSmeSwab_Sample[i];
							if (el.Evidence_Name) {
								html += '<p><span class="label">'+'Образец #'+count+'</span><span class="textData">'+el.Evidence_Name+'</span></p>'
							}
							count++;
						};

					};
					return html;
				},
				getCheckboxValue: function(val,name) {
					
				}
			}
		]);
	}
})