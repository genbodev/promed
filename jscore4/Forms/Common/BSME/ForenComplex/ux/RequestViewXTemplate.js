/**
 * RequestViewXTemplate - расширение XTemplate для просомотра заявки в АРМах службы комиссионных и комплексных экспертиз
 */
Ext.define('common.BSME.ForenComplex.ux.RequestViewXTemplate',{
	extend: 'Ext.XTemplate',
	handlers: [],
	constructor: function (config) {
		var me = this;

		/*me.afterlayout = function(panel,layout,eOpts) {
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
		};*/


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

				//Неотображаемые поля
				// EFC.EvnForensicComplex_id as EvnForensic_id,
				// ISNULL(EFT.EvnForensicType_Name,'Не определён') as EvnForensicType_Name,



				'<p class="journal-header">Журнал регистрации судебно-медицинских исследований и медицинских судебных экспертиз</p><hr/>',
				'<p><span class="label">Основание для проведения экспертизы: </span><span class="textData">{EvnForensicComplexResearch_Base}</span></p>',
				'<p><span class="label">ФИО назначившего экспертизу: </span><span class="textData">{Person_cFIO}</span></p>',
				'{[ this.getEvidences(values) ]}',


				'<tpl if="ActVersionForensic_id!=0">',
					'<p class="journal-header">Заключение (акт) эксперта </p><hr/>',
					'<p><span class="label">Номер акта: </span><span class="textData">{ActVersionForensic_Num}</span></p>',
					'{ActVersionForensic_Text}',
				'</tpl>',
				/*'<tpl if="this.hasAttachment(attachment)">',
					'<p class="journal-header"> Прикрепленные файлы </p><hr/>',
					'<tpl for="attachment">',
						'<p><a class="savedFile" href="/?c=EvnMediaFiles&m=getFile&EvnMediaData_id={EvnMediaData_id}&fileName={EvnMediaData_FilePath}" target="_blank">{EvnMediaData_FileName}</a></p>',
						'<p>Описание: {EvnMediaData_Comment}</p>',
					'</tpl>',
				'</tpl>',
				'<tpl if="this.hasDirections(directions)">',
					'<p class="journal-header"> Направления на получения дополнительных материалов</p><hr/>',
					'<table>',
						'<tr><th>Наименование</th><th>Дата направления</th><th>Дата получения</th><th></th></tr>',
						'<tpl for="directions">',
							'<tr class="direction"><td>{[this.getDirectionLink(values,parent)]}</td><td>{EvnForensicSubDopMatQuery_insDT}</td><td>{EvnForensicSubDopMatQuery_ResultDT}</td><td width="80" class="tableButtons">{[this.getDirectionButtons(values)]}</td></tr>',
						'</tpl>',
					'</table>',
				'</tpl>',*/

			'</div>',
			{
				getEvidences: function(val) {
					var count = 1;
					var html = '';
					var i,el;

					if (val.EvnForensicComplexResearch_Evidence && val.EvnForensicComplexResearch_Evidence.length) {
						for (i = 0; i < val.EvnForensicComplexResearch_Evidence.length; i++) {
							el = val.EvnForensicComplexResearch_Evidence[i];
							if (el.Evidence_Name) {
								html += '<p><span class="label">'+'Документ #'+count+'</span><span class="textData">'+el.Evidence_Name+'</span></p>'
							}
							count++;
						}
					}
					return html;
				}/*,
				createClickListener: function(val) {

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
							id: 'edit-'+val.EvnForensicSubDopMatQuery_id,
							event: 'click',
							handler: function(){
								me.setDirectionComeDate(val.EvnForensicSubDopMatQuery_id);
							}
						});

						me.handlers.push({
							id: 'delete-'+val.EvnForensicSubDopMatQuery_id,
							event: 'click',
							handler: function(){
								me.deleteDirection(val.EvnForensicSubDopMatQuery_id);
							}
						});

						me.handlers.push({
							id: 'print-'+val.EvnForensicSubDopMatQuery_id,
							event: 'click',
							handler: function(){
								me.printDirection(val.EvnForensicSubDopMatQuery_id);
							}
						});


						return (
							'<span role="img" id="edit-'+val.EvnForensicSubDopMatQuery_id+'" class="x-btn-icon-el edit16 x-btn-icon-el-additional"style=""></span>'+
								'<span role="img" id="delete-'+val.EvnForensicSubDopMatQuery_id+'" class="x-btn-icon-el delete16 x-btn-icon-el-additional"></span>'+
								'<span role="img" id="print-'+val.EvnForensicSubDopMatQuery_id+'" class="x-btn-icon-el print16 x-btn-icon-el-additional"></span>'
							);
					}
				}*/
			}
		]);
	}
})