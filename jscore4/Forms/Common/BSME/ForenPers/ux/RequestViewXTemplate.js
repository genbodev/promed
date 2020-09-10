/**
* RequestViewXTemplate - расширение XTemplate для просомотра заявки в АРМах службы судебно биологического отделения с молекулярно-генетической лабораторией
*/
Ext.define('common.BSME.ForenPers.ux.RequestViewXTemplate',{
	extend: 'Ext.XTemplate',
	parent: null,
	callback: Ext.emptyFn,
	printDirection: function(id) {
		if (!id) {
			return false;
		}

		printBirt({
			'Report_FileName': 'CME_EvnForensicSubDopMatQuery.rptdesign',
			'Report_Params': '&paramEvnForensicSubDopMatQuery='+id,
			'Report_Format': 'pdf'
		});
	},
	deleteDirection: function(EvnForensicSubDopMatQuery_id) {
		var me = this;
		if (!EvnForensicSubDopMatQuery_id) {
			return false;
		}
		Ext.MessageBox.confirm('Сообщение', 'Вы уверены, что хотите удалить направление?', function(btn){
			if ( btn !== 'yes' ) {
				return;
			}
			var loadMask = new Ext.LoadMask(me.parent, {msg:"Пожалуйста, подождите, идёт удаление направления..."}); 
			loadMask.show();
			
			Ext.Ajax.request({
				params: {
					EvnForensicSubDopMatQuery_id: EvnForensicSubDopMatQuery_id
				},
				url: '/?c=BSME&m=deleteEvnForenSubDopMatQuery',
				callback: function(params,success,result) {

					if (result.status !== 200) {
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
						return false;
					} 
					var resp = Ext.JSON.decode(result.responseText, true);
					if (resp === null) {
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'Ошибка обработки запроса');
						return false;
					}

					loadMask.hide();
					me.callback();
				}
			});
		});
	},
	setDirectionComeDate: function(id) {
		var me = this;
		if (!id) {
			return false;
		}
		
		Ext.create('common.BSME.ForenPers.ExpertWP.tools.swFinishDopMatQueryWindow').show({
			formParams: {EvnForensicSubDopMatQuery_id: id},
			callback: function() {
				me.callback();
			}
		});
		
	},
	viewDirection: function(id) {
		var me = this;
		
		if (!id) {
			return false;
		}
		
		Ext.create('common.BSME.ForenPers.ExpertWP.tools.swCreateDopMatQueryWindow').show({
			action: 'view',
			formParams: {EvnForensicSubDopMatQuery_id: id}
		});
	},
	
	editDopDocRequest: function(id) {
		var me = this;
		if (!id) {
			return false;
		}
		Ext.create('common.BSME.ForenPers.ExpertWP.tools.swCreateDopDocQueryWindow').show({
			action: 'edit',
			formParams: {EvnForensicSubDopDocQuery_id: id},
			callback: me.callback
		});
	},
	deleteDopDocRequest: function(EvnForensicSubDopDocQuery_id) {
		var me = this;
		if (!EvnForensicSubDopDocQuery_id) {
			return false;
		}
		Ext.MessageBox.confirm('Сообщение', 'Вы уверены, что хотите удалить запрос на документы?', function(btn){
			if ( btn !== 'yes' ) {
				return;
			}
			var loadMask = new Ext.LoadMask(me.parent, {msg:"Пожалуйста, подождите, идёт удаление..."}); 
			loadMask.show();
			
			Ext.Ajax.request({
				params: {
					EvnForensicSubDopDocQuery_id: EvnForensicSubDopDocQuery_id
				},
				url: '/?c=BSME&m=deleteEvnForenSubDopDocQuery',
				callback: function(params,success,result) {

					if (result.status !== 200) {
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
						return false;
					} 
					var resp = Ext.JSON.decode(result.responseText, true);
					if (resp === null) {
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'Ошибка обработки запроса');
						return false;
					}

					loadMask.hide();
					me.callback();
				}
			});
		});
	},
	printDopDocRequest: function(id) {
		if (!id) {
			return false;
		}

		printBirt({
			'Report_FileName': 'CME_EvnForensicSubDopDocQuery.rptdesign',
			'Report_Params': '&paramEvnForensicSubDopDocQuery='+id,
			'Report_Format': 'pdf'
		});
	},
	editDopPersRequest: function(id) {
		var me = this;
		if (!id) {
			return false;
		}
		Ext.create('common.BSME.ForenPers.ExpertWP.tools.swCreateDopPersQueryWindow').show({
			action: 'edit',
			formParams: {EvnForensicSubDopPersQuery_id: id},
			callback: me.callback
		});
	},
	deleteDopPersRequest: function(EvnForensicSubDopPersQuery_id) {
		var me = this;
		if (!EvnForensicSubDopPersQuery_id) {
			return false;
		}
		Ext.MessageBox.confirm('Сообщение', 'Вы уверены, что хотите удалить запрос на участие?', function(btn){
			if ( btn !== 'yes' ) {
				return;
			}
			var loadMask = new Ext.LoadMask(me.parent, {msg:"Пожалуйста, подождите, идёт удаление..."}); 
			loadMask.show();
			
			Ext.Ajax.request({
				params: {
					EvnForensicSubDopPersQuery_id: EvnForensicSubDopPersQuery_id
				},
				url: '/?c=BSME&m=deleteEvnForenSubDopPersQuery',
				callback: function(params,success,result) {

					if (result.status !== 200) {
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
						return false;
					} 
					var resp = Ext.JSON.decode(result.responseText, true);
					if (resp === null) {
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'Ошибка обработки запроса');
						return false;
					}

					loadMask.hide();
					me.callback();
				}
			});
		});
	},
	printDopPersRequest: function(id) {
		if (!id) {
			return false;
		}

		printBirt({
			'Report_FileName': 'CME_EvnForensicSubDopPersQuery.rptdesign',
			'Report_Params': '&paramEvnForensicSubDopDocQuery='+id,
			'Report_Format': 'pdf'
		});
	},
	editCoverLetter: function(id) {
		var me = this;
		if (!id) {
			return false;
		}
		Ext.create('common.BSME.ForenPers.ExpertWP.tools.swCreateCoverLetterWindow').show({
			action: 'edit',
			formParams: {EvnForensicSubCoverLetter_id: id},
			callback: me.callback
		});
	},
	deleteCoverLetter: function(EvnForensicSubCoverLetter_id) {
		var me = this;
		if (!EvnForensicSubCoverLetter_id) {
			return false;
		}
		Ext.MessageBox.confirm('Сообщение', 'Вы уверены, что хотите удалить сопроводительное письмо?', function(btn){
			if ( btn !== 'yes' ) {
				return;
			}
			var loadMask = new Ext.LoadMask(me.parent, {msg:"Пожалуйста, подождите, идёт удаление..."}); 
			loadMask.show();
			
			Ext.Ajax.request({
				params: {
					EvnForensicSubCoverLetter_id: EvnForensicSubCoverLetter_id
				},
				url: '/?c=BSME&m=deleteEvnForenSubCoverLetter',
				callback: function(params,success,result) {

					if (result.status !== 200) {
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
						return false;
					} 
					var resp = Ext.JSON.decode(result.responseText, true);
					if (resp === null) {
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'Ошибка обработки запроса');
						return false;
					}

					loadMask.hide();
					me.callback();
				}
			});
		});
	},
	printCoverLetter: function(id) {
		if (!id) {
			return false;
		}

		printBirt({
			'Report_FileName': 'CME_EvnForensicSubCoverLetter.rptdesign',
			'Report_Params': '&paramEvnForensicSubDopPersQuery='+id,
			'Report_Format': 'pdf'
		});
	},
	handlers: [],
	constructor: function (config) {
		var me = this;
		
		me.afterlayout = function(panel,layout,eOpts) {
			var el;
			for (var i =0; !!me.handlers && i<me.handlers.length ; i++) {
				if (me.handlers[i] && (typeof me.handlers[i].id == 'string') &&  (typeof me.handlers[i].event == 'string') && (typeof me.handlers[i].handler == 'function') ) {
					el = Ext.get(me.handlers[i].id);
					if (el) {
						el.clearListeners().addListener( me.handlers[i].event, me.handlers[i].handler, me) 
					}
				}
			}
			
			//Реинициализируем массив
			me.handlers = [];
		};
		
		
		me.callParent([
			'<div class="expertisePanel">',
				'<div class="expertiseHeader">',
					'<p>Экспертиза</p>',
				'</div>',
				'<div class="expertiseBody">',
					'<tpl if="EvnDirectionForensic_id==0">',
						'<p class="expertiseFieldTitle"><span>Статус:</span></p>',
						'<p class="expertiseFieldValue">Не назначена</p>',
					'<tpl else>',
						// Временно скрыли
//						'<p class="expertiseFieldTitle"><span>Время экспертизы</span></p>',
//						'<p class="expertiseFieldValue">',
//							'<tpl if="EvnForensicSub_ExpertiseDate">',
//								'{EvnForensicSub_ExpertiseDate} {EvnForensicSub_ExpertiseTime}',
//							'</tpl>',	
//						'</p>',
						'<p class="expertiseFieldTitle"><span>Эксперт</span></p>',
						'<p class="expertiseFieldValue">{Expert_Fin}</p>',
					'</tpl>',
				'</div>',
			'</div>',
			'<div class="request-view">',
				'<h1>{ForensicSubType_Name} {EvnForensicSub_Num} <span>от {EvnForensicSub_insDT}</span></h1>',
				
				
				'<tpl if="EvnStatusHistory_Cause!=\'\'">',
					'<p><span class="label">Комментарий заведующего: </span><span class="textData">{EvnStatusHistory_Cause}</span></p>',
				'</tpl>',

				//Неотображаемые поля
				// EFS.EvnForensicSub_id as EvnForensic_id,
				// ISNULL(EFT.EvnForensicType_Name,'Не определён') as EvnForensicType_Name,
				'<p><span class="label">Подэкспертное лицо: </span><span class="textData">{Person_Fio}</span></p>',
				'<p><span class="label">Дата происшествия: </span><span class="textData"><tpl if="EvnForensicSub_AccidentDate">{EvnForensicSub_AccidentDate} {EvnForensicSub_AccidentTime}</tpl>',
				'<p><span class="label">Дата поступления экспертизы: </span><span class="textData"><tpl if="EvnForensicSub_ExpertiseComeDate">{EvnForensicSub_ExpertiseComeDate} {EvnForensicSub_ExpertiseComeTime}</tpl>',
				
				'<tpl if="ForensicSubType_id!=3">',
					'<p><span class="label">Инициатор экспертизы: </span><span class="textData">',
						'<tpl if="ForensicIniciatorPost_id">{ForensicIniciatorPost_Name}</tpl> <tpl if="Person_cid">{Iniciator_Fio}</tpl>',
					'</span></p>',
					'<p><span class="label">Дата постановления: </span><span class="textData"><tpl if="EvnForensicSub_ResDate">{EvnForensicSub_ResDate} {EvnForensicSub_ResTime}</tpl>',
					'<p><span class="label">Направившая организация: </span><span class="textData">{Org_Name}</span></p>',
				'</tpl>',
				
//				'<tpl if="EvnClass_id==123">',
//					
//					'<p><span class="label">Направившая организация: </span><span class="textData">{EvnForensicSubDir_Org_Name}</span></p>',
//				'</tpl>',	
//				'<tpl if="EvnClass_id==124">',
//					'<p class="journal-header">Журнал регистрации свидетельствуемых в судебно-медицинской амбулатории дополнительных экспертиз</p><hr/>',
//					'<p><span class="label">Исследуемое лицо: </span><span class="textData">{EvnForensicSubInsp_Person_Fio}</span></p>',
//					'<p><span class="label">Цель экспертизы: </span><span class="textData">{EvnForensicSub_Goal}</span></p>',
//					'<p><span class="label">Краткие обстоятельства дела: </span><span class="textData">{EvnForensicSub_Facts}</span></p>',
//					'<p><span class="label">Время происшествия: </span><span class="textData">{EvnForensicSub_AccidentDT} {EvnForensicSub_AccidentTime}</span></p>',
//					'<p><span class="label">Назначившее лицо: </span><span class="textData">{Person_Fio}</span></p>',
//					'<p><span class="label">Направившая организация: </span><span class="textData">{EvnForensicSubInsp_Org_Name}</span></p>',
//					'<p><span class="label">Переданные материалы: </span><span class="textData">{EvnForensicSubInsp_TransferMat}</span></p>',
//				'</tpl>',
//				'<tpl if="EvnClass_id==125">',
//					'<p class="journal-header">Журнале регистрации свидетельствуемых в судебно-медицинской амбулатории по личному заявлению</p><hr/>',
//					'<p><span class="label">Исследуемое лицо: </span><span class="textData">{EvnForensicSubOwn_Person_Fio}</span></p>',
//					'<p><span class="label">Цель экспертизы: </span><span class="textData">{EvnForensicSub_Goal}</span></p>',
//					'<p><span class="label">Краткие обстоятельства дела: </span><span class="textData">{EvnForensicSub_Facts}</span></p>',
//					'<p><span class="label">Время происшествия: </span><span class="textData">{EvnForensicSub_AccidentDT} {EvnForensicSub_AccidentTime}</span></p>',
//					'<p><span class="label">Стоимость: </span><span class="textData">{EvnForensicSubOwn_Cost}</span></p>',
//				'</tpl>',
//				'<tpl if="EvnClass_id==126">',
//					'<p class="journal-header">Журнале регистрации свидетельствуемых в судебно-медицинской амбулатории по материалам дела</p><hr/>',
//					'<p><span class="label">Исследуемое лицо: </span><span class="textData">{EvnForensicSubDoc_Person_Fio}</span></p>',
//					'<p><span class="label">Цель экспертизы: </span><span class="textData">{EvnForensicSub_Goal}</span></p>',
//					'<p><span class="label">Краткие обстоятельства дела: </span><span class="textData">{EvnForensicSub_Facts}</span></p>',
//					'<p><span class="label">Время происшествия: </span><span class="textData">{EvnForensicSub_AccidentDT} {EvnForensicSub_AccidentTime}</span></p>',		
//					'<p><span class="label">Назначившее лицо: </span><span class="textData">{Person_Fio}</span></p>',
//					'<p><span class="label">Направившая организация: </span><span class="textData">{EvnForensicSubDoc_Org_Name}</span></p>',
//					'<p><span class="label">Переданные материалы: </span><span class="textData">{EvnForensicSubDoc_TransferMat}</span></p>',
//				'</tpl>',
				'<tpl if="this.hasAttachment(attachment)">',
					'<p class="journal-header"> Прикрепленные файлы </p><hr/>',
					'<tpl for="attachment">',
					'<p><a class="savedFile" href="/?c=EvnMediaFiles&m=getFile&EvnMediaData_id={EvnMediaData_id}&fileName={EvnMediaData_FilePath}" target="_blank">{EvnMediaData_FileName}</a></p>',
					'<p>Описание: {EvnMediaData_Comment}</p>',
					'</tpl>',
				'</tpl>',
				
				
//				'<tpl if="this.hasDirections(directions)">',
//					'<p class="journal-header"> Направления на получения дополнительных материалов</p><hr/>',
//					'<table>',
//						'<tr><th>Наименование</th><th>Дата направления</th><th>Дата получения</th><th></th></tr>',
//						'<tpl for="directions">',
//							'<tr class="direction"><td>{[this.getDirectionLink(values,parent)]}</td><td>{EvnForensicSubDopMatQuery_insDT}</td><td>{EvnForensicSubDopMatQuery_ResultDT}</td><td width="80" class="tableButtons">{[this.getDirectionButtons(values)]}</td></tr>',
//						'</tpl>',
//					'</table>',
//				'</tpl>',
				
				'<tpl if="this.hasDopDocRequests(dopDocDirections)">',
					'<p class="journal-header"> Направления на получения дополнительных документов</p><hr/>',
					'<table>',
						'<tr><th>Наименование</th><!--<th>Дата направления</th><th>Дата получения</th>--><th></th></tr>',
						'<tpl for="dopDocDirections">',
							'<tr class="direction">',
								'<td>{[this.getDopDocRequestLink(values,parent)]}</td>',
								//'<td>{EvnForensicSubDopMatQuery_insDT}</td>',
								//'<td>{EvnForensicSubDopMatQuery_ResultDT}</td>',
								'<td width="80" class="tableButtons">{[this.getDopDocButtons(values)]}</td>',
							'</tr>',
						'</tpl>',
					'</table>',
				'</tpl>',
				
				'<tpl if="this.hasDopPersRequests(dopPersDirections)">',
					'<p class="journal-header"> Запросы на участие</p><hr/>',
					'<table>',
						'<tr><th>Наименование</th><!--<th>Дата направления</th><th>Дата получения</th>--><th></th></tr>',
						'<tpl for="dopPersDirections">',
							'<tr class="direction">',
								'<td>{[this.getDopPersRequestLink(values,parent)]}</td>',
								//'<td>{EvnForensicSubDopMatQuery_insDT}</td>',
								//'<td>{EvnForensicSubDopMatQuery_ResultDT}</td>',
								'<td width="80" class="tableButtons">{[this.getDopPersButtons(values)]}</td>',
							'</tr>',
						'</tpl>',
					'</table>',
				'</tpl>',
				
				
				'<tpl if="this.hasCoverLetters(coverLetters)">',
					'<p class="journal-header"> Сопроводительные письма</p><hr/>',
					'<table>',
						'<tr><th>Наименование</th><!--<th>Дата направления</th><th>Дата получения</th>--><th></th></tr>',
						'<tpl for="coverLetters">',
							'<tr class="direction">',
								'<td>{[this.getCoverLetterLink(values,parent)]}</td>',
								//'<td>{EvnForensicSubDopMatQuery_insDT}</td>',
								//'<td>{EvnForensicSubDopMatQuery_ResultDT}</td>',
								'<td width="80" class="tableButtons">{[this.getCoverLetterButtons(values)]}</td>',
							'</tr>',
						'</tpl>',
					'</table>',
				'</tpl>',
				
				'<tpl if="EvnXml_id!=0">',
				'<p class="journal-header"> Заключение </p><hr/>',
					//'{[this.getDirectionLink(values,parent)]}',
					'{EvnXmlHtml}',
				'</tpl>',
				
			'</div>',
			{	
				hasDopDocRequests: function(dopDocDirections){
					return (!!dopDocDirections && !!dopDocDirections.length);
				},
				getDopDocRequestLink: function(val,par) {
					me.handlers.push({
						id: 'dopdocquery-'+val.EvnForensicSubDopDocQuery_id,
						event: 'click',
						handler: function(){
							me.editDopDocRequest(val.EvnForensicSubDopDocQuery_id);
						}
					});
					return '<a class="pointedLink" id="dopdocquery-'+val.EvnForensicSubDopDocQuery_id+'">Направление на получение дополнительных документов '+val.EvnForensicSubDopDocQuery_Num+'</a>'
				},
				getDopDocButtons: function(val)  {
					
						me.handlers.push({
							id: 'edit-dopdoc-'+val.EvnForensicSubDopDocQuery_id,
							event: 'click',
							handler: function(){
								me.editDopDocRequest(val.EvnForensicSubDopDocQuery_id);
							}
						});
						
						me.handlers.push({
							id: 'delete-dopdoc-'+val.EvnForensicSubDopDocQuery_id,
							event: 'click',
							handler: function(){
								me.deleteDopDocRequest(val.EvnForensicSubDopDocQuery_id);
							}
						});
						
						me.handlers.push({
							id: 'print-dopdoc-'+val.EvnForensicSubDopDocQuery_id,
							event: 'click',
							handler: function(){
								me.printDopDocRequest(val.EvnForensicSubDopDocQuery_id);
							}
						});
						
						return (
							'<span role="img" id="edit-dopdoc-'+val.EvnForensicSubDopDocQuery_id+'" class="x-btn-icon-el edit16 x-btn-icon-el-additional"style=""></span>'+
							'<span role="img" id="delete-dopdoc-'+val.EvnForensicSubDopDocQuery_id+'" class="x-btn-icon-el delete16 x-btn-icon-el-additional"></span>'+
							'<span role="img" id="print-dopdoc-'+val.EvnForensicSubDopDocQuery_id+'" class="x-btn-icon-el print16 x-btn-icon-el-additional"></span>'
						);
					
				},
				hasDopPersRequests: function(dopPersDirections){
					return (!!dopPersDirections && !!dopPersDirections.length);
				},
				getDopPersRequestLink: function(val,par) {
					me.handlers.push({
						id: 'doppersquery-'+val.EvnForensicSubDopPersQuery_id,
						event: 'click',
						handler: function(){
							me.editDopPersRequest(val.EvnForensicSubDopPersQuery_id);
						}
					});
					return '<a class="pointedLink" id="doppersquery-'+val.EvnForensicSubDopPersQuery_id+'">Направление на участие '+val.EvnForensicSubDopPersQuery_Num+'</a>'
				},
				getDopPersButtons: function(val)  {
					
						me.handlers.push({
							id: 'edit-doppers-'+val.EvnForensicSubDopPersQuery_id,
							event: 'click',
							handler: function(){
								me.editDopPersRequest(val.EvnForensicSubDopPersQuery_id);
							}
						});
						
						me.handlers.push({
							id: 'delete-doppers-'+val.EvnForensicSubDopPersQuery_id,
							event: 'click',
							handler: function(){
								me.deleteDopPersRequest(val.EvnForensicSubDopPersQuery_id);
							}
						});
						
						me.handlers.push({
							id: 'print-doppers-'+val.EvnForensicSubDopPersQuery_id,
							event: 'click',
							handler: function(){
								me.printDopPersRequest(val.EvnForensicSubDopPersQuery_id);
							}
						});
						
						return (
							'<span role="img" id="edit-doppers-'+val.EvnForensicSubDopPersQuery_id+'" class="x-btn-icon-el edit16 x-btn-icon-el-additional"style=""></span>'+
							'<span role="img" id="delete-doppers-'+val.EvnForensicSubDopPersQuery_id+'" class="x-btn-icon-el delete16 x-btn-icon-el-additional"></span>'+
							'<span role="img" id="print-doppers-'+val.EvnForensicSubDopPersQuery_id+'" class="x-btn-icon-el print16 x-btn-icon-el-additional"></span>'
						);
					
				},
				hasCoverLetters: function(coverLetters){
					return (!!coverLetters && !!coverLetters.length);
				},
				getCoverLetterLink: function(val,par) {
					me.handlers.push({
						id: 'coverletter-'+val.EvnForensicSubCoverLetter_id,
						event: 'click',
						handler: function(){
							me.editCoverLetter(val.EvnForensicSubCoverLetter_id);
						}
					});
					return '<a class="pointedLink" id="coverletter-'+val.EvnForensicSubCoverLetter_id+'">Сопроводительное письмо '+val.EvnForensicSubCoverLetter_Num+'</a>'
				},
				getCoverLetterButtons: function(val)  {
					
						me.handlers.push({
							id: 'edit-coverletter-'+val.EvnForensicSubCoverLetter_id,
							event: 'click',
							handler: function(){
								me.editCoverLetter(val.EvnForensicSubCoverLetter_id);
							}
						});
						
						me.handlers.push({
							id: 'delete-coverletter-'+val.EvnForensicSubCoverLetter_id,
							event: 'click',
							handler: function(){
								me.deleteCoverLetter(val.EvnForensicSubCoverLetter_id);
							}
						});
						
						me.handlers.push({
							id: 'print-coverletter-'+val.EvnForensicSubCoverLetter_id,
							event: 'click',
							handler: function(){
								me.printCoverLetter(val.EvnForensicSubCoverLetter_id);
							}
						});
						
						return (
							'<span role="img" id="edit-coverletter-'+val.EvnForensicSubCoverLetter_id+'" class="x-btn-icon-el edit16 x-btn-icon-el-additional"style=""></span>'+
							'<span role="img" id="delete-coverletter-'+val.EvnForensicSubCoverLetter_id+'" class="x-btn-icon-el delete16 x-btn-icon-el-additional"></span>'+
							'<span role="img" id="print-coverletter-'+val.EvnForensicSubCoverLetter_id+'" class="x-btn-icon-el print16 x-btn-icon-el-additional"></span>'
						);
					
				},
				hasAttachment: function(attachment){
					return (!!attachment && !!attachment.length);
				},
				hasDirections: function(direction){
					return (!!direction && !!direction.length);
				},
				//Исключительно для того, чтобы добавить обработчик
				getDirectionLink: function(val,par) {
					me.handlers.push({
						id: 'direction-'+val.EvnForensicSubDopMatQuery_id,
						event: 'click',
						handler: function(){
							me.viewDirection(val.EvnForensicSubDopMatQuery_id);
						}
					});
					return '<a class="pointedLink" id="direction-'+val.EvnForensicSubDopMatQuery_id+'">Направление на получение дополнительных материалов '+val.EvnForensicSubDopMatQuery_Num+'</a>'
				},
				getDirectionButtons: function(val)  {
					
					if (val.EvnForensicSubDopMatQuery_ResultDT) {
						
						me.handlers.push({
							id: 'print-'+val.EvnForensicSubDopMatQuery_id,
							event: 'click',
							handler: function(){
								me.printDirection(val.EvnForensicSubDopMatQuery_id);
							}
						});
						
						return ('<span role="img" id="print-'+val.EvnForensicSubDopMatQuery_id+'" class="x-btn-icon-el print16 x-btn-icon-el-additional"></span>');
					} else {
						
						me.handlers.push({
							id: 'edit-direction-'+val.EvnForensicSubDopMatQuery_id,
							event: 'click',
							handler: function(){
								me.setDirectionComeDate(val.EvnForensicSubDopMatQuery_id);
							}
						});
						
						me.handlers.push({
							id: 'delete-direction-'+val.EvnForensicSubDopMatQuery_id,
							event: 'click',
							handler: function(){
								me.deleteDirection(val.EvnForensicSubDopMatQuery_id);
							}
						});
						
						me.handlers.push({
							id: 'print-direction-'+val.EvnForensicSubDopMatQuery_id,
							event: 'click',
							handler: function(){
								me.printDirection(val.EvnForensicSubDopMatQuery_id);
							}
						});
						
						
						return (
							'<span role="img" id="edit-direction-'+val.EvnForensicSubDopMatQuery_id+'" class="x-btn-icon-el edit16 x-btn-icon-el-additional"style=""></span>'+
							'<span role="img" id="delete-direction-'+val.EvnForensicSubDopMatQuery_id+'" class="x-btn-icon-el delete16 x-btn-icon-el-additional"></span>'+
							'<span role="img" id="print-direction-'+val.EvnForensicSubDopMatQuery_id+'" class="x-btn-icon-el print16 x-btn-icon-el-additional"></span>'
						);
					}
				}
			}
		]);
	}
})
