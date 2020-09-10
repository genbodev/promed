/**
* swTreatmentFeedbackWindow - окно добавления, редактирования обращения.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swTreatmentFeedbackWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      4.08.2010
* @comment      Префикс для id компонентов TEW (swTreatmentFeedbackWindow). TABINDEX_TEW
*/

sw.Promed.swTreatmentFeedbackWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 600,
	title: langs('Изменение статуса: Обращение'),
	modal: true,
	id: 'swTreatmentFeedbackWindow',
	draggable: false,
	resizable: false,
	closable: true,
	border : false,
	closeAction: 'hide',
	layout: 'form',
	autoHeight: true,
	onsave: Ext.emptyFn,
	initComponent: function(){

		var wind = this;

		Ext.apply(wind, {
			items: [
				new Ext.form.FormPanel({
					layout: 'form',
					labelAlign: 'right',
					autoHeight: true,
					labelWidth: 180,
					bodyStyle: 'padding:5px;',
					items: [
						{
							border: false,
							layout: 'form',
							items: [
								{
									name: 'Treatment_id',
									xtype: 'hidden'
								},
								{
									fieldLabel: langs('Статус обращения'),
									allowBlank: false,
									disabled: false,
									comboSubject: 'TreatmentReview',
									tabIndex: TABINDEX_ETSF + 14,
									width: 180,
									value: '', //default value
									id: 'ETSF_TreatmentReview_id',
									xtype: 'swcommonsprcombo',
									listeners: {
										'beforeselect': function(combo, record){
											wind.setVisibleFields(record.get('TreatmentReview_Code') == '3');
										}
									}
								}
							]
						},
						{
							xtype: 'fieldset',
							id: 'feedbackFS',
							autoHeight: true,
							title: 'Ответ на обращение',
							items: [
								{
									fieldLabel: langs('Ответ предоставлен'),
									comboSubject: 'TreatmentMethodDispatch',
									id: 'ETSF_TreatmentMethodDispatch_id',
									allowBlank: false,
									tabIndex: TABINDEX_ETSF + 10,
									width: 250,
									xtype: 'swcommonsprcombo'
								},
								{
									fieldLabel: langs('Текст ответа'),
									allowBlank: true,
									maxLength: 8000,
									maxLengthText: langs('Вы превысили максимальный размер сообщения 8000 символов'),
									name: 'TreatmentFeedback_Message',
									value: '',
									height: 80,
									width: 350,
									xtype: 'textarea'
								},
								{
									fieldLabel: 'Примечание',
									allowBlank: true,
									disabled: false,
									maxLength: 8000,
									maxLengthText: langs('Вы превысили максимальный размер сообщения 8000 символов'),
									name: 'TreatmentFeedback_Note',
									value: '',
									height: 80,
									width: 350,
									xtype: 'textarea'
								},
								{
									border: false,
									layout : 'column',
									id: 'Request_FilesPanel_' + wind.id,
									items : [
										{
											border: false,
											layout : 'form',
											width : 200,
											items : [
												{
													id: 'TreatmentFeedback_Document',
													name: 'TreatmentFeedback_Document',
													xtype: 'hidden'
												},
												{
													text : langs('Прикрепленные документы'),
													id: 'TreatmentFeedback_FilesLabel',
													width : 180,
													style : 'font-size: 12px; text-align: right; padding: 5px 5px 0',
													xtype: 'label'
												}
											]
										},
										{
											border: false,
											layout : 'form',
											width : 210,
											items : [
												new Ext.Panel({
													fieldLabel: langs('Прикреп. документы'),
													autoHeight: true,
													id : 'TreatmentFeedback_FilesPanel',
													split : true,
													hidden: false,
													html : '',
													border : false
												})
											]
										},
										{
											border: false,
											layout : 'form',
											items : [
												{
													xtype : 'button',
													text : langs('Прикрепить'),
													tabIndex : TABINDEX_TEW + 19,
													style : 'margin: 0px 2px 0px 3px;',
													iconCls : 'add16',
													id : 'TreatmentFeedback_FileUploadButton',
													handler : function() {
														var params = new Object();
														//params.action = action;
														params.FilesData = Ext.getCmp('TreatmentFeedback_Document').getValue();
														params.callback = function(data) {
															if ( !data )
															{
																return false;
															}
															Ext.getCmp('TreatmentFeedback_Document').setValue(data);
															Ext.getCmp('TreatmentFeedback_FilesPanel').removeAll();
															var response_obj = Ext.util.JSON.decode(data);

															var form = wind.getFormPanel()[0].getForm();

															//@todo не понятно что это
															var id = form.findField('Treatment_id').getValue();
															if ( ! id ) id = 0;
															wind.createFilesLinks( response_obj, id );

														};
														getWnd('swFileUploadWindow').show(params);
													}
												}
											]
										}
									]
								}
							]
						}
					]
				})
			],

			buttons : [
				{
					iconCls: 'save16',
					text: 'Сохранить',
					handler: function() {
						wind.doSave();
					}
				},
				'-',
				{
					iconCls: 'cancel16',
					text: 'Отмена',
					handler: function() {
						wind.hide();
					}
				}
			]
		});

		sw.Promed.swTreatmentFeedbackWindow.superclass.initComponent.apply(wind, arguments);
	},
	show: function(){
		sw.Promed.swTreatmentFeedbackWindow.superclass.show.apply(this, arguments);

		var form = this.getFormPanel()[0].getForm();

		form.reset();

		Ext.getCmp('TreatmentFeedback_FilesPanel').removeAll();
		Ext.getCmp('TreatmentFeedback_Document').setValue('');

		if(arguments && arguments[0] && arguments[0].Treatment_id) {

			var args = arguments[0];

			if(args.onsave){
				this.onsave = args.onsave;
			}

			form.findField('Treatment_id').setValue(args.Treatment_id);
			form.findField('TreatmentReview_id').setValue(args.TreatmentReview_id);
			this.setVisibleFields(args.TreatmentReview_id == 2);

		}else{
			sw.swMsg.alert(langs('Ошибка'), langs('Не указан параметр обращения'));
		}

	},

	doSave: function(){
		var wind = this,
			form = wind.getFormPanel()[0].getForm(),
			params = form.getValues();

		//Сохранение статуса обращения
		Ext.Ajax.request({
			url: '/?c=Treatment&m=setStatusTreatment',
			params: params,
			callback: function (options, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if(params.TreatmentReview_id == 2){
						//Сохранение ответа на обращение
						Ext.Ajax.request({
							url: '/?c=Treatment&m=saveTreatmentFeedback',
							params: params,
							callback: function (options, success, response) {
								if ( success && response.responseText != '' ) {
									var response_obj = Ext.util.JSON.decode(response.responseText);

									wind.onsave('feedback');
								}
							}
						});
					}else{
						wind.onsave('status');
					}

				}
			}
		});
	},

	setVisibleFields: function(show){
		var form = this.getFormPanel()[0].getForm();

		if(show){
			Ext.getCmp('feedbackFS').show();
		}
		else{
			Ext.getCmp('feedbackFS').hide();
		}

		//тень не успевает
		this.syncShadow();
	},
	createFilesLinks: function(files_data, treatment_id) {
		var files_panel = Ext.getCmp('TreatmentFeedback_FilesPanel');
		for(i in files_data)
		{
			if ( ! files_data[i].file_name ) continue;
			files_panel.add(new Ext.Panel({
				id: 'TreatmentFeedback_' + files_data[i].file_name,
				border : false,
				html   : '<a href="/uploads/' + files_data[i].file_name + '" target="_blank">' + files_data[i].orig_name + '</a> <span onclick="Ext.getCmp(\'swTreatmentFeedbackWindow\').swDeleteFile(' + i + ', ' + treatment_id + ');" style="color: red; cursor: pointer; font-weight: bold; " title="удалить"> X </span> ' // + files_data[i].file_descr
			}));
			files_panel.doLayout();
		}
	},
	swDeleteFile: function(index, Treatment_id) {
		if(this.action == 'view') {
			return false;
		}
		var files_str = Ext.getCmp('TreatmentFeedback_Document').getValue();
		var files_obj = Ext.util.JSON.decode( files_str );
		sw.swMsg.show(
			{
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Вы действительно хотите удалить файл ') + files_obj[index].orig_name + '?',
				title: langs('Вопрос'),
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						var Mask = new Ext.LoadMask(this.getEl(), { msg: "Пожалуйста, подождите, идет обработка запроса на удаление файла " + files_obj[index].orig_name + " ..." });
						Mask.show();
						Ext.Ajax.request({
							callback: function(opt, success, resp) {
								Mask.hide();
								var response_obj = Ext.util.JSON.decode(resp.responseText);

								if ( response_obj.Error_Msg )
									sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
								if (!response_obj.data)
									return false; //To-do нужна обработка ситуации, когда файл был загружен, но документ не был сохранен.

								// Обновление данных о прикрепленных файлах
								Ext.getCmp('TreatmentFeedback_Document').setValue(response_obj.data);
								// Удаление ссылки на файл
								var files_panel = Ext.getCmp('TreatmentFeedback_FilesPanel');
								files_panel.remove( 'TreatmentFeedback_' + files_obj[index].file_name );
								files_panel.doLayout();
								this.doSave({isEditFile: true});
								//sw.swMsg.alert( this.title, 'Файл ' + files_obj[index].orig_name + ' удален.');
							}.createDelegate(this),
							params: {
								file: files_obj[index].file_name,
								data: files_str
							},
							url: '/?c=Treatment&m=deleteFile'
						});
					}
					else
					{
						return false;
					}
				}.createDelegate(this)
			});
	},
});