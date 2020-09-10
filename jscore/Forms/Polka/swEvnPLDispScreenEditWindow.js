/**
* swEvnPLDispScreenEditWindow - окно редактирования/добавления скринингового исследования
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2015 Swan Ltd.
* @author		Dmitry Vlasenko
* @comment		Префикс для id компонентов EPLDSEF (EvnPLDispScreenEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispScreenEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispScreenEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispScreenEditWindow.js',
	draggable: true,
	getEvnUslugaDispDopMinMaxDates: function()
	{
		var response = new Object();
		var EvnUslugaDispDop_minDate, EvnUslugaDispDop_maxDate;
		this.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
			if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_setDate')) ) {
				if ( Ext.isEmpty(EvnUslugaDispDop_minDate) || EvnUslugaDispDop_minDate > rec.get('EvnUslugaDispDop_setDate') ) {
					EvnUslugaDispDop_minDate = rec.get('EvnUslugaDispDop_setDate');
				}
				
				if ( Ext.isEmpty(EvnUslugaDispDop_maxDate) || EvnUslugaDispDop_maxDate < rec.get('EvnUslugaDispDop_setDate') ) {
					EvnUslugaDispDop_maxDate = rec.get('EvnUslugaDispDop_setDate');
				}
			}
		});
		
		response.minDate = EvnUslugaDispDop_minDate;
		response.maxDate = EvnUslugaDispDop_maxDate;
		return response;
	},
	getDataForCallBack: function()
	{
		var win = this;
		var base_form = win.EvnPLDispScreenFormPanel.getForm();
		var personinfo = win.PersonInfoPanel;
		var EvnUslugaDispDopDate = win.getEvnUslugaDispDopMinMaxDates();
		var response = new Object();

		response.EvnPLDispScreen_id = base_form.findField('EvnPLDispScreen_id').getValue();
		response.Person_id = base_form.findField('Person_id').getValue();
		response.Server_id = base_form.findField('Server_id').getValue();
		response.Person_Surname = personinfo.getFieldValue('Person_Surname');
		response.Person_Firname = personinfo.getFieldValue('Person_Firname');
		response.Person_Secname = personinfo.getFieldValue('Person_Secname');
		response.Person_Birthday = personinfo.getFieldValue('Person_Birthday');
		response.Sex_Name = personinfo.getFieldValue('Sex_Name');
		response.AgeGroupDisp_Name = base_form.findField('AgeGroupDisp_id').getFieldValue('AgeGroupDisp_Name');
		response.EvnPLDispScreen_setDate = typeof EvnUslugaDispDopDate.minDate == 'object' ? EvnUslugaDispDopDate.minDate : base_form.findField('EvnPLDispScreen_setDate').getValue();
		response.EvnPLDispScreen_disDate = typeof EvnUslugaDispDopDate.maxDate == 'object' && base_form.findField('EvnPLDispScreen_IsEndStage').getValue() == 2 ? EvnUslugaDispDopDate.maxDate : null;
		response.EvnPLDispScreen_IsEndStage = (base_form.findField('EvnPLDispScreen_IsEndStage').getValue() == 2) ? lang['da']:lang['net'];

		log(response);
		return response;
	},
	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;
		var base_form = win.EvnPLDispScreenFormPanel.getForm();
		var autoSave = options.autoSave == undefined ? false : true;


		if (!(win.action == 'add' && win.withoutAgeGroups) && !base_form.isValid() )
		{
			if (autoSave == false)
			{
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						win.EvnPLDispScreenFormPanel.getFirstInvalidEl().focus(false);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var params = new Object();

		if (base_form.findField('AgeGroupDisp_id').disabled) {
			params.AgeGroupDisp_id = base_form.findField('AgeGroupDisp_id').getValue();
		}

		if (base_form.findField('EvnPLDispScreen_QueteletIndex').disabled) {
			params.EvnPLDispScreen_QueteletIndex = base_form.findField('EvnPLDispScreen_QueteletIndex').getValue();
		}

		params.withoutAgeGroups = win.withoutAgeGroups;

		var queryParams = {
			clientValidation: false,
			failure: function(result_form, action) {
				win.getLoadMask().hide()
			},
			params: params,
			success: function(result_form, action) {
				
				if (action.result || (win.action == 'add' && win.withoutAgeGroups)) {
					
					var resultSave = Ext.util.JSON.decode(action.response.responseText);
					
					Ext.Ajax.request({
						params: {
							EvnPLDispScreen_id: resultSave.EvnPLDispScreen_id,
							EvnPLDispScreen_IsEndStage: base_form.findField('EvnPLDispScreen_IsEndStage').getValue()
						},
						url: '/?c=ExchangeBL&m=sendEvnPLDispScreenAPP',
						success: function (response) {
							win.getLoadMask().hide();
							
							var resultAPP = Ext.util.JSON.decode(response.responseText);
							
							if (resultAPP.success) {
								if (!resultAPP.isAlreadySendedToApp) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function (buttonId) {
											base_form.findField('EvnPLDispScreen_id').setValue(action.result.EvnPLDispScreen_id);
											win.callback({evnPLDispScreenData: win.getDataForCallBack()});
											
											if (options.callback) {
												options.callback();
											} else {
												win.hide();
											}
										},
										icon: Ext.Msg.INFO,
										msg: 'Скрининговое исследование добавлено',
										title: 'Сообщение'
									});
								} else {
									base_form.findField('EvnPLDispScreen_id').setValue(action.result.EvnPLDispScreen_id);
									win.callback({evnPLDispScreenData: win.getDataForCallBack()});
									
									if (options.callback) {
										options.callback();
									} else {
										win.hide();
									}
								}
							} else {
								if (!resultAPP.info) resultAPP.info = resultAPP.Error_Msg;
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.WARNING,
									title: "Ошибка отправки в сервис.",
									msg: resultAPP.info
								});
							}
						},
						failure: function (response) {
							win.getLoadMask().hide();
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								title: "Ошибка отправки в сервис.",
								msg: 'Ошибка отправки в сервис.'
							});
						}
					});
				} else {
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		};

		win.getLoadMask("Подождите, идет сохранение...").show();
		if(win.action == 'add' && win.withoutAgeGroups) {
			var data = [];
			var values = win.EvnPLDispScreenFormPanel.getForm().getValues();
			win.evnUslugaRecommendDispDopGrid.getGrid().getStore().each(function (rec) {
				var flag = rec.json;
				flag.checked = rec.get('SurveyTypeLink_IsEarlier');
				if(flag.checked === true) {
					flag.checked = '2';
				}else {
					flag.checked = '1';
				}
				data.push(flag);
			});
			queryParams.params.data = JSON.stringify(data);
			queryParams.params = Object.assign(queryParams.params, values);
		}
		
		var EvnUslugaDispDopDate = win.getEvnUslugaDispDopMinMaxDates();
		
		if(!Ext.isEmpty(EvnUslugaDispDopDate.minDate)){
			base_form.findField('EvnPLDispScreen_setDate').setValue(EvnUslugaDispDopDate.minDate);
		}
		base_form.submit(queryParams);
	},
	height: 570,
	Year: 2015,
	id: 'EvnPLDispScreenEditWindow',
	
	showEvnDirectionEditWindow: function (action) {
		
		var base_form = this.EvnPLDispScreenFormPanel.getForm();
		var grid = this.evnUslugaDispDopGrid.getGrid();
		var win = this;
		
		var record = grid.getSelectionModel().getSelected();
		
		if ( typeof record != 'object' ) {
			return false;
		}
		
		//обновить грид услуг
		var reloadGrid  = function () {
			win.evnUslugaDispDopGrid.loadData({
				params: {
					EvnPLDispScreen_id: base_form.findField('EvnPLDispScreen_id').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue()
				},
				globalFilters: {
					withoutAgeGroups: win.withoutAgeGroups,
					EvnPLDispScreen_id: base_form.findField('EvnPLDispScreen_id').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue()
				}, noFocusOnLoad: true
			});
		}
		
		if (Ext.isEmpty(base_form.findField('EvnPLDispScreen_id').getValue())){
			win.doSave({
				callback: function() {
					win.showEvnDirectionEditWindow(action);
				},
				autoSave: true
			});
			return false;
		}
		
		if (action == 'add') {
			getWnd('swEvnDirectionEditWindow').show({
				action: 'add',
				Person_id: base_form.findField('Person_id').getValue(),
				kzScreening: 1,
				callback: function (data) {
					Ext.Ajax.request({
						url: '/?c=EvnPLDispScreen&m=saveEvnUslugaDispDop',
						params: {
							'MedPersonal_id': record.get('MedPersonal_id'),
							'MedStaffFact_id': record.get('MedStaffFact_id'),
							'LpuSection_id': record.get('LpuSection_id'),
							'Diag_id': record.get('Diag_id'),
							'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id'),
							'EvnUslugaDispDop_pid': base_form.findField('EvnPLDispScreen_id').getValue(),
							'EvnDirection_id': data.evnDirectionData.EvnDirection_id,
							'SurveyType_id': record.get('SurveyType_id'),
							'EvnUslugaDispDop_setDate': Ext.isEmpty(record.get('EvnUslugaDispDop_setDate'))?Ext.util.Format.date(data.evnDirectionData.EvnDirection_setDate, 'd.m.Y'):Ext.util.Format.date(record.get('EvnUslugaDispDop_setDate'), 'd.m.Y'),
							'UslugaComplex_id': record.get('UslugaComplex_id'),
							'Person_id': base_form.findField('Person_id').getValue(),
							'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue()
						},
						success: function (response, action) {
							if (response && response.responseText) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (answer.success) {
									reloadGrid();
								} else if (answer.Error_Msg) {
									//Ext.Msg.alert('Ошибка', answer.Error_Msg);
								}
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['Ошибка при сохранении исследования']);
							}
						}
					});
				},
				formParams: {
					'DirType_id': 10,
					'UslugaComplex_did': record.get('UslugaComplex_id')
				}
			});
		} else if (action == 'view') {
			getWnd('swEvnDirectionEditWindow').show({
				action: 'view',
				Person_id: base_form.findField('Person_id').getValue(),
				EvnDirection_id: record.get('EvnDirection_id'),
				formParams: {}
			});
		} else if (action == 'cancel') {
			var params = {
				EvnDirection_id: record.get('EvnDirection_id'),
				cancelType: 'cancel',
				ownerWindow: win,
				callback: function (data) {
					/*Ext.Ajax.request({
						url: '/?c=EvnPLDispScreen&m=deleteEvnUslugaDispDop',
						params: {
							'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id')
						},
						success: function (response, action) {
							if (response && response.responseText) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (answer.success) {
									reloadGrid();
								} else if (answer.Error_Msg) {
									//Ext.Msg.alert('Ошибка', answer.Error_Msg);
								}
							} else {
								Ext.Msg.alert(lang['oshibka'], 'Ошибка при удалении исследования');
							}
						}
					});*/
					Ext.Ajax.request({
						url: '/?c=EvnPLDispScreenChild&m=saveEvnUslugaDispDop',
						params: {
							'MedPersonal_id': record.get('MedPersonal_id'),
							'MedStaffFact_id': record.get('MedStaffFact_id'),
							'LpuSection_id': record.get('LpuSection_id'),
							'Diag_id': record.get('Diag_id'),
							'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id'),
							'EvnUslugaDispDop_pid': base_form.findField('EvnPLDispScreen_id').getValue(),
							//'EvnDirection_id': data.evnDirectionData.EvnDirection_id,
							'SurveyType_id': record.get('SurveyType_id'),
							'EvnUslugaDispDop_setDate': Ext.util.Format.date(record.get('EvnUslugaDispDop_setDate'), 'd.m.Y'),
							'UslugaComplex_id': record.get('UslugaComplex_id'),
							'Person_id': base_form.findField('Person_id').getValue(),
							'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue()
						},
						success: function (response, action) {
							if (response && response.responseText) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (answer.success) {
									reloadGrid();
								} else if (answer.Error_Msg) {
									//Ext.Msg.alert('Ошибка', answer.Error_Msg);
								}
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['Ошибка при сохранении исследования']);
							}
						}
					});
				}
			}
			
			sw.Promed.Direction.cancel(params);
		}
	},
		
	showEvnUslugaDispScreenEditWindow: function(action) {
		var base_form = this.EvnPLDispScreenFormPanel.getForm();
		var grid = this.evnUslugaDispDopGrid.getGrid();
		var win = this;

		var record = grid.getSelectionModel().getSelected();

		if ( typeof record != 'object' ) {
			return false;
		}

		var personinfo = win.PersonInfoPanel;

		if (Ext.isEmpty(base_form.findField('EvnPLDispScreen_id').getValue())){
			win.doSave({
				callback: function() {
					win.showEvnUslugaDispScreenEditWindow(action);
				},
				autoSave: true
			});
			return false;
		}

		getWnd('swEvnUslugaDispScreenEditWindow').show({
			EvnDirection_id: record.get('EvnDirection_id'),
			AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
			ScreenType_id: base_form.findField('ScreenType_id').getValue(),
			archiveRecord: this.archiveRecord,
			action: action,
			object: 'EvnPLDispScreen',
			minDate: '01.01.'+ win.Year,
			maxDate: '31.12.'+ win.Year,
			DispClass_id: base_form.findField('DispClass_id').getValue(),
			OmsSprTerr_Code: personinfo.getFieldValue('OmsSprTerr_Code'),
			Person_id: personinfo.getFieldValue('Person_id'),
			Person_Birthday: personinfo.getFieldValue('Person_Birthday'),
			Person_Firname: personinfo.getFieldValue('Person_Firname'),
			Person_Secname: personinfo.getFieldValue('Person_Secname'),
			Person_Surname: personinfo.getFieldValue('Person_Surname'),
			Sex_id: personinfo.getFieldValue('Sex_id'),
			Sex_Code: personinfo.getFieldValue('Sex_Code'),
			Person_Age: personinfo.getFieldValue('Person_Age'),
			UserLpuSection_id: win.UserLpuSection_id,
			UserMedStaffFact_id: win.UserMedStaffFact_id,
			formParams: {
				EvnUslugaDispDop_pid: base_form.findField('EvnPLDispScreen_id').getValue(),
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue(),
				EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id'),
				SurveyType_id: record.get('SurveyType_id')
			},
			SurveyTypeLink_id: record.get('SurveyTypeLink_id'),
			SurveyType_Code: record.get('SurveyType_Code'),
			SurveyType_Name: record.get('SurveyType_Name'),
			ShowDeseaseStageCombo: getRegionNick().inlist(['perm','buryatiya','kareliya'])?true:false,
			onHide: Ext.emptyFn,
			callback: function(data) {
				// обновить грид услуг
				win.evnUslugaDispDopGrid.loadData({
					params: {
						EvnPLDispScreen_id: base_form.findField('EvnPLDispScreen_id').getValue(),
						Person_id: base_form.findField('Person_id').getValue(),
						AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue()
					},
					globalFilters: {
						withoutAgeGroups: win.withoutAgeGroups,
						EvnPLDispScreen_id: base_form.findField('EvnPLDispScreen_id').getValue(),
						Person_id: base_form.findField('Person_id').getValue(),
						AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue()
					}, noFocusOnLoad: true
				});
			}

		});
	},
	recountKetle: function() {
		var win = this;
		var base_form = win.EvnPLDispScreenFormPanel.getForm();
		var person_height = base_form.findField('PersonHeight_Height').getValue() / 100;
		var person_weight = base_form.findField('PersonWeight_Weight').getValue();

		base_form.findField('EvnPLDispScreen_QueteletIndex').setValue(null);
		if ( !Ext.isEmpty(person_height) && !Ext.isEmpty(person_weight) ) {
			var body_mass_index = person_weight / (person_height * person_height);
			base_form.findField('EvnPLDispScreen_QueteletIndex').setValue(body_mass_index.toFixed(1));
		}
	},
	initComponent: function() {
		var win = this;

		this.evnUslugaDispDopGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() { win.showEvnUslugaDispScreenEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { win.showEvnUslugaDispScreenEditWindow('view'); } },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			onRowSelect: function(sm, index, record) {
				this.setActionDisabled('action_edit', Ext.isEmpty(record.get('EvnUslugaDispDop_setDate')) && record.get('noIndication'));
				this.setActionDisabled('action_view', Ext.isEmpty(record.get('EvnUslugaDispDop_setDate')));
				
				if (record.get('EvnDirection_id')) {
					this.setActionDisabled('action_showDirection',false);
					this.setActionDisabled('action_cancelDirection',false);
					this.setActionDisabled('action_addDirection',true);
				} else {
					this.setActionDisabled('action_showDirection',true);
					this.setActionDisabled('action_cancelDirection',true);
					this.setActionDisabled('action_addDirection',false);
				}
			},
			id: 'EPLDSEF_evnUslugaDispDopGrid',
			dataUrl: '/?c=EvnPLDispScreen&m=loadEvnUslugaDispDopGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'SurveyType_id', type: 'int', hidden: true },
				{ name: 'ScreenType_id', type: 'int', hidden: true },
				{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'EvnUslugaDispDop_id', type: 'int', hidden: true },
				{ name: 'noIndication', type: 'int', hidden: true },
				{ name: 'UslugaComplex_id', type: 'string', hidden: true, header: 'USLUGA' },
				{ name: 'EvnDirection_id', type: 'string', hidden: true, header: 'DIRECTION' },
				{ name: 'MedPersonal_id', type: 'string', hidden: true, header: 'MedPersonal_id' },
				{ name: 'MedStaffFact_id', type: 'string', hidden: true, header: 'MedStaffFact_id' },
				{ name: 'LpuSection_id', type: 'string', hidden: true, header: 'LpuSection_id' },
				{ name: 'Diag_id', type: 'string', hidden: true, header: 'Diag_id' },
				{ name: 'SurveyType_Name', type: 'string', header: langs('Наименование осмотра (исследования)'), id: 'autoexpand' },
				{ name: 'EvnUslugaDispDop_setDate', type: 'date', header: langs('Дата выполнения'), width: 150 }
			]
		});

		this.evnUslugaRecommendDispDopGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			actions: [
				{ name: 'action_add', disabled: true, hidden: true},
				{ name: 'action_edit', handler: function() { win.showEvnUslugaDispScreenEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { win.showEvnUslugaDispScreenEditWindow('view'); } },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			onRowSelect: function(sm, index, record) {
				this.setActionDisabled('action_edit', Ext.isEmpty(record.get('EvnUslugaDispDop_setDate')) && record.get('noIndication'));
				this.setActionDisabled('action_view', Ext.isEmpty(record.get('EvnUslugaDispDop_setDate')));
			},
			onAfterEdit: function() {
				var saveDisabled = true;
				win.evnUslugaRecommendDispDopGrid.getGrid().getStore().each(function (rec) {
					flag = rec.get('SurveyTypeLink_IsEarlier');
					if(flag !== true && flag !== undefined) {
						saveDisabled = false;
					}
				});
				if(saveDisabled) {
					Ext.getCmp('EPLDSEF_SaveButton').disable();
				}else {
					Ext.getCmp('EPLDSEF_SaveButton').enable();
				}
			},
			id: 'EPLDSEF_evnUslugaRecommendDispDopGrid',
			dataUrl: '/?c=EvnPLDispScreen&m=loadEvnUslugaDispDopGrid',
			region: 'center',
			height: 500,
			title: '',
			saveAtOnce: false,
			toolbar: false,
			stringfields: [
				{ name: 'SurveyType_id', type: 'int', hidden: true },
				//{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'EvnUslugaDispDop_id', type: 'int', hidden: true },
				{ name: 'noIndication', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', header: langs('Наименование осмотра (исследования)'), id: 'autoexpand' },
				{ 
					name: 'SurveyTypeLink_IsEarlier', 
					type: 'checkcolumnedit',
					header: langs('Пройдено ранее'),
					width: 150,

				}
			]
			

		});

		this.evnUslugaDispDopGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function(row, index) {
				var cls = '';
				if (row.get('noIndication') > 0)
					cls = cls + 'x-grid-rowbackgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});

		this.evnUslugaRecommendDispDopGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function(row, index) {
				var cls = '';
				if (row.get('noIndication') > 0)
					cls = cls + 'x-grid-rowbackgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});

		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			button2Callback: function(callback_data) {
				var base_form = win.EvnPLDispScreenFormPanel.getForm();

				base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				base_form.findField('Server_id').setValue(callback_data.Server_id);

				win.PersonInfoPanel.load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
			},
			region: 'north'
		});

		this.EvnUslugaDispDopPanel = new sw.Promed.Panel({
			items: [
				win.evnUslugaDispDopGrid
			],
			animCollapse: true,
			layout: 'form',
			id: 'EvnUslugaDispDopPanel',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: lang['marshrutnaya_karta']
		});

		this.EvnUslugaRecommendDispDopPanel = new sw.Promed.Panel({
			items: [
				win.evnUslugaRecommendDispDopGrid
			],
			animCollapse: true,
			layout: 'form',
			border: false,
			id: 'EvnUslugaRecommendDispDopPanel',
			autoHeight: true,
			collapsible: true,
			title: langs('Рекомендуемые скрининговые осмотры')
		});

		this.DopDispQuestionPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: lang['opros_po_skrining-testu'],
			id: 'DopDispQuestionPanel',
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			layout: 'form',
			labelAlign: 'right',
			bodyStyle: 'padding: 5px;',
			labelWidth: 300,
			items: [{
				fieldLabel: langs('Курение (хотя бы одну сигарету в день)'),
				hiddenName: 'EvnPLDispScreen_IsSmoking',
				xtype: 'swyesnocombo',
				allowBlank: false
			}, {
				fieldLabel: lang['upotreblyaete_li_vyi_alkogolnyie_napitki'],
				hiddenName: 'EvnPLDispScreen_IsAlco',
				labelSeparator: '',
				listeners: {
					'change': function(combo, newValue) {
						var base_form = win.EvnPLDispScreenFormPanel.getForm();

						win.filterAlcoCombos();

						if (newValue == 2) {
							base_form.findField('AlcoholIngestType_bid').enable();
							base_form.findField('AlcoholIngestType_bid').setAllowBlank(false);
							base_form.findField('AlcoholIngestType_wid').enable();
							base_form.findField('AlcoholIngestType_wid').setAllowBlank(false);
							base_form.findField('AlcoholIngestType_vid').enable();
							base_form.findField('AlcoholIngestType_vid').setAllowBlank(false);

						} else {
							base_form.findField('AlcoholIngestType_bid').clearValue();
							base_form.findField('AlcoholIngestType_bid').setAllowBlank(true);
							base_form.findField('AlcoholIngestType_bid').disable();
							base_form.findField('AlcoholIngestType_wid').clearValue();
							base_form.findField('AlcoholIngestType_wid').setAllowBlank(true);
							base_form.findField('AlcoholIngestType_wid').disable();
							base_form.findField('AlcoholIngestType_vid').clearValue();
							base_form.findField('AlcoholIngestType_vid').setAllowBlank(true);
							base_form.findField('AlcoholIngestType_vid').disable();
						}
					}
				},
				xtype: 'swyesnocombo'
			}, {
				title: lang['variant_ejenedelnogo_potrebleniya'],
				autoHeight: true,
				labelWidth: 290,
				xtype: 'fieldset',
				items: [{
					fieldLabel: lang['pivo'],
					comboSubject: 'AlcoholIngestType',
					codeField: 'AlcoholIngestType_Code',
					hiddenName: 'AlcoholIngestType_bid',
					lastQuery: '',
					disabled: true,
					autoLoad: true,
					allowBlank: false,
					ids: {
						male: ['75','76'],
						female: ['77','78']
					},
					moreFields: [
						{ name: 'DrinkType_id', mapping: 'DrinkType_id' },
						{ name: 'Sex_id', mapping: 'Sex_id' },
						{ name: 'AlcoholIngestType_From', mapping: 'AlcoholIngestType_From' },
						{ name: 'AlcoholIngestType_To', mapping: 'AlcoholIngestType_To' }
					],
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: lang['vino'],
					comboSubject: 'AlcoholIngestType',
					codeField: 'AlcoholIngestType_Code',
					hiddenName: 'AlcoholIngestType_wid',
					lastQuery: '',
					autoLoad: true,
					disabled: true,
					allowBlank: false,
					ids: {
						male: ['79','80'],
						female: ['81','82']
					},
					moreFields: [
						{ name: 'DrinkType_id', mapping: 'DrinkType_id' },
						{ name: 'Sex_id', mapping: 'Sex_id' },
						{ name: 'AlcoholIngestType_From', mapping: 'AlcoholIngestType_From' },
						{ name: 'AlcoholIngestType_To', mapping: 'AlcoholIngestType_To' }
					],
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: lang['vodka_i_drugie_krepkie_napitki'],
					comboSubject: 'AlcoholIngestType',
					codeField: 'AlcoholIngestType_Code',
					lastQuery: '',
					autoLoad: true,
					disabled: true,
					allowBlank: false,
					ids: {
						male: ['83','84'],
						female: ['85','86']
					},
					hiddenName: 'AlcoholIngestType_vid',
					moreFields: [
						{ name: 'DrinkType_id', mapping: 'DrinkType_id' },
						{ name: 'Sex_id', mapping: 'Sex_id' },
						{ name: 'AlcoholIngestType_From', mapping: 'AlcoholIngestType_From' },
						{ name: 'AlcoholIngestType_To', mapping: 'AlcoholIngestType_To' }
					],
					xtype: 'swcommonsprcombo'
				}]
			}, {
				fieldLabel: langs('Физическая активность, ежедневная физическая нагрузка (ходьба, упражнения и т.д.) не менее 30 минут'),
				hiddenName: 'EvnPLDispScreen_IsDailyPhysAct',
				xtype: 'swyesnocombo',
				allowBlank: false
			}, {
				fieldLabel: langs('Имеются ли у родителей болезни сердца (гипертония, ИБС)?'),
				hiddenName: 'EvnPLDispScreen_IsParCoronary',
				labelSeparator: '',
				xtype: 'swyesnocombo'
			}, {
				fieldLabel: langs('Имеются ли у Вас болезни сердца (гипертония, ишемическая болезнь сердца)?'),
				hiddenName: 'EvnPLDispScreen_IsCoronary',
				labelSeparator: '',
				xtype: 'swyesnocombo'
			},
				{
				fieldLabel: lang['poyavlyaetsya_li_u_vas_bol_ili_drugie_nepriyatnyie_oschuscheniya_za_grudinoy_v_pokoe_ili_pri_nagruzke_psihoemotsionalnaya_fizicheskaya_prohodyaschie_pri_ee_otmene_v_techenie_do_10_minut_ili_pereboi_v_ritme_serdtsa'],
				hiddenName: 'EvnPLDispScreen_IsHeartache',
				labelSeparator: '',
				xtype: 'swyesnocombo'
			}, {
				fieldLabel: lang['otmechayutsya_li_u_vas_golovnyie_boli'],
				hiddenName: 'EvnPLDispScreen_IsHeadache',
				labelSeparator: '',
				xtype: 'swyesnocombo'
			}, {
				fieldLabel: lang['otmechaetsya_li_u_vas_povyishenie_arterialnogo_davleniya'],
				hiddenName: 'EvnPLDispScreen_IsHighPressure',
				labelSeparator: '',
				xtype: 'swyesnocombo'
			}, {
				fieldLabel: langs('Наблюдается ли у Вас снижение остроты зрения?'),
				hiddenName: 'EvnPLDispScreen_IsVisImpair',
				labelSeparator: '',
				xtype: 'swyesnocombo'
			},{
				fieldLabel: langs('Имеются ли у Вас жалобы на «пелену» перед глазами?'),
				hiddenName: 'EvnPLDispScreen_IsBlurVision',
				labelSeparator: '',
				xtype: 'swyesnocombo'
			},{
				fieldLabel: langs('Имеется (имелась) ли у Вас или родителей глаукома?'),
				hiddenName: 'EvnPLDispScreen_IsGlaucoma',
				labelSeparator: '',
				xtype: 'swyesnocombo'
			},{
				fieldLabel: langs('Есть ли у Вас близорукость, превышающая 4 диоптрии?'),
				hiddenName: 'EvnPLDispScreen_IsHighMyopia',
				labelSeparator: '',
				xtype: 'swyesnocombo'
			}, {
				fieldLabel: langs('Отмечаются ли у Вас в течение последнего года патологические примеси в кале?'),
				comboSubject: 'FecalCasts',
				showCodefield: false,
				hiddenName: 'FecalCasts_id',
				labelSeparator: '',
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: langs('Бывают ли у Вас контактные кровотечения?'),
				hiddenName: 'EvnPLDispScreen_IsBleeding',
				labelSeparator: '',
				xtype: 'swyesnocombo'
			}]
		});

		this.EvnPLDispScreenMainResultsPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: lang['osnovnyie_rezultatyi'],
			id: 'EvnPLDispScreenMainResultsPanel',
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			layout: 'form',
			labelAlign: 'right',
			bodyStyle: 'padding: 5px;',
			labelWidth: 300,
			items: [{
					name: 'EvnPLDispScreen_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'accessType',
					xtype: 'hidden'
				}, {
					name: 'Lpu_id',
					xtype: 'hidden'
				}, {
					name: 'DispClass_id',
					value: 13,
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				}, {
					fieldLabel: lang['rost_sm'],
					listeners: {
						'change': function() {
							win.recountKetle();
						}
					},
					name: 'PersonHeight_Height',
					allowDecimal: false,
					allowNegative: false,
					allowBlank: false,
					xtype: 'numberfield'
				}, {
					fieldLabel: lang['ves_kg'],
					listeners: {
						'change': function() {
							win.recountKetle();
						}
					},
					name: 'PersonWeight_Weight',
					allowDecimal: true,
					allowNegative: false,
					allowBlank: false,
					xtype: 'numberfield'
				}, {
					fieldLabel: lang['okrujnost_talii_sm'],
					name: 'EvnPLDispScreen_PersonWaist',
					allowDecimal: false,
					allowNegative: false,
					allowBlank: false,
					xtype: 'numberfield'
				}, {
					fieldLabel: lang['indeks_ketle'],
					name: 'EvnPLDispScreen_QueteletIndex',
					readOnly: true,
					xtype: 'textfield'
				}, /*{
					fieldLabel: langs('Окружность талии (см)'),
					comboSubject: 'WaistCircumference',
					showCodefield: false,
					hiddenName: 'WaistCircumference_id',
					allowBlank: false,
					xtype: 'swcommonsprcombo',
					width: 145
				},*/ {
					fieldLabel: langs('Артериальное давление (систолическое)'),
					name: 'EvnPLDispScreen_ArteriaSistolPress',
					allowDecimal: false,
					allowNegative: false,
					xtype: 'numberfield'
				}, {
					fieldLabel: lang['arterialnoe_davlenie_diastolicheskoe'],
					name: 'EvnPLDispScreen_ArteriaDiastolPress',
					allowDecimal: false,
					allowNegative: false,
					xtype: 'numberfield'
				}, {
					fieldLabel: langs('Здоров'),
					hiddenName: 'EvnPLDispScreen_IsHealthy',
					xtype: 'swyesnocombo',
					allowBlank: false,
					width: 145
				},  {
					title: langs('Выявлены поведенческие факторы риска'),
					autoHeight: true,
					labelWidth: 290,
					id: 'behaviour_risk_factors',
					xtype: 'fieldset',
					items: [
						{
						xtype: 'swcheckbox',
						name: 'EvnPLDispScreen_IsAlcoholAbuse',
						boxLabel: 'Злоупотребление алкоголем',
						hideLabel: true
						}, {
						xtype: 'swcheckbox',
						name: 'EvnPLDispScreen_IsOverweight',
						boxLabel: 'Избыточная масса тела',
						hideLabel: true
						}, {
						xtype: 'swcheckbox',
						name: 'EvnPLDispScreen_IsLowPhysAct',
						boxLabel: 'Низкая физическая активность',
						hideLabel: true
						}
					]
				}, {
					title: langs('Выявлены биологические факторы риска'),
					autoHeight: true,
					labelWidth: 290,
					id: 'biology_risk_factors',
					xtype: 'fieldset',
					items: [
						{
							xtype: 'swcheckbox',
							name: 'EvnPLDispScreen_IsGenPredisposed',
							boxLabel: 'Наследственная предрасположенность',
							hideLabel: true
						}, {
							xtype: 'swcheckbox',
							name: 'EvnPLDispScreen_IsHypertension',
							boxLabel: 'Гипертензия',
							hideLabel: true
						}, {
							xtype: 'swcheckbox',
							name: 'EvnPLDispScreen_IsHyperlipidemia',
							boxLabel: 'Гиперлипидемия',
							hideLabel: true
						}, {
							xtype: 'swcheckbox',
							name: 'EvnPLDispScreen_IsHyperglycaemia',
							boxLabel: 'Гипергликемия',
							hideLabel: true
						}
					]
				}, {
					fieldLabel: langs('Группа диспансерного наблюдения'),
					hiddenName: 'HealthKind_id',
					id: 'EPLDSEW_HealthKind_id',
					loadParams: {params: {where: ' where HealthKind_Code in (8,9,10,11)'}},
					xtype: 'swhealthkindcombo'
				}, {
					fieldLabel: langs('Направлен к врачу ПМСП'),
					hiddenName: 'EvnPLDispScreen_IsDirectedPMSP',
					id: 'EPLDSEW_EvnPLDispScreen_IsDirectedPMSP',
					xtype: 'swyesnocombo'
				}, {
					fieldLabel: langs('Случай закончен'),
					allowBlank: false,
					value: 1,
					hiddenName: 'EvnPLDispScreen_IsEndStage',
					listeners: {
						'change': function(combo, newValue) {
							var base_form = win.EvnPLDispScreenFormPanel.getForm();
							
							var pmsp = Ext.getCmp('EPLDSEW_EvnPLDispScreen_IsDirectedPMSP'),
								hk = Ext.getCmp('EPLDSEW_HealthKind_id');

							if (newValue == 2) {
								if (pmsp.isVisible())
								{
									pmsp.setAllowBlank(false);
								}

								hk.setAllowBlank(false);

								win.buttons[1].show();

								base_form.findField('ScreenEndCause_id').showContainer();
								base_form.findField('ScreenEndCause_id').setAllowBlank(false);
							} else {
								pmsp.setAllowBlank(true);
								win.buttons[1].hide();
								
								base_form.findField('ScreenEndCause_id').hideContainer();
								base_form.findField('ScreenEndCause_id').setAllowBlank(true);
							}
						}
					},
					xtype: 'swyesnocombo'
				}, {
					border: false,
					layout: 'form',
					hidden: getRegionNick()!='kz',
					items: [{
						comboSubject: 'ScreenEndCause',
						fieldLabel: 'Причина завершения',
						prefix: 'r101_',
						xtype: 'swcommonsprcombo'
					}]
				}, {
					xtype: 'swcheckbox',
					name: 'EvnPLDispScreen_IsDisability',
					fieldLabel: langs('Установлена инвалидность'),
					//hideLabel: true,
					listeners: {
						change: function (el, newValue, oldValue) {
							win.handleDisabilityFields();
						}
					}
				}, {
					fieldLabel: langs('Год установления инвалидности'),
					xtype: 'swnumcounterfield',
					name: 'EvnPLDispScreen_DisabilityYear',
					minYear: 0,
					enableKeyEvents: true,
					allowBlank: false,
					editable: false,
					defaultValue: Ext.util.Format.date(new Date, 'Y'), // текущий год
					beforeTriggerCheck: function (newValue, currValue, action)
					{
						if (newValue < this.minYear || newValue > this.defaultValue)
						{
							return false;
						}
						return true;
					}

				}, {
					xtype: 'numberfield',
					maxValue: 16,
					minValue: 1,
					allowDecimals: false,
					name: 'EvnPLDispScreen_DisabilityPeriod',
					fieldLabel: langs('На какой срок установлена инвалидность'),
					allowBlank: false
				}, {
					fieldLabel: langs('Диагноз по инвалидности'),
					xtype: 'swdiagcombo',
					width: 450,
					allowBlank: false,
					hiddenName: 'Diag_disid'
				}
			],
			region: 'center'
		});

		this.EvnPLDispScreenFormPanel = new Ext.form.FormPanel({
			border: false,
			layout: 'form',
			region: 'center',
			autoScroll: true,
			items: [{
				border: false,
				labelWidth: 200,
				layout: 'form',
				style: 'padding: 5px;',
				items: [{
					fieldLabel: langs('Дата начала'),
					format: 'd.m.Y',
					name: 'EvnPLDispScreen_setDate',
					value: new Date(),
					id: 'EvnPLDispScreen_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					selectOnFocus: true,
					width: 100,
					allowBlank: false,
					hidden: false,
					xtype: 'swdatefield',
					listeners: {
						change: function (combo, newValue, oldValue)
						{
							if (newValue == oldValue)
							{
								return true;
							}

							var base_form = win.EvnPLDispScreenFormPanel.getForm(),
								dateX = new Date(2018,3,30);


							if ( ! ((oldValue > dateX && newValue > dateX) || (oldValue < dateX && newValue < dateX)) )
							{
								win.handleAfterFirstMayFields();
								win.filterAlcoCombos();
								win.loadAgeDispGroupCombo(newValue);
								win.setAgeGroupDispCombo();

								win.evnUslugaDispDopGrid.loadData({
									params: {
										withoutAgeGroups: win.withoutAgeGroups,
										EvnPLDispScreen_id: base_form.findField('EvnPLDispScreen_id').getValue(),
										Person_id: base_form.findField('Person_id').getValue(),
										AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
										EvnPLDispScreen_setDate: Ext.util.Format.date(newValue, 'd.m.Y')
									},
									globalFilters: {
										withoutAgeGroups: win.withoutAgeGroups,
										EvnPLDispScreen_id: base_form.findField('EvnPLDispScreen_id').getValue(),
										Person_id: base_form.findField('Person_id').getValue(),
										AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
										EvnPLDispScreen_setDate: Ext.util.Format.date(newValue, 'd.m.Y')
									}, noFocusOnLoad: true
								});
							}
						}
					}
				}, {
					border: false,
					layout: 'form',
					hidden: getRegionNick()!='kz',
					items: [{
						comboSubject: 'ScreenType',
						fieldLabel: 'Целевая категория',
						prefix: 'r101_',
						allowBlank: getRegionNick()!='kz',
						listeners: {
							change: function (combo, newValue, oldValue) {
								if (newValue == oldValue) return true;
								
								var base_form = win.EvnPLDispScreenFormPanel.getForm();
								
								win.evnUslugaDispDopGrid.loadData({
									params: {
										EvnPLDispScreen_id: base_form.findField('EvnPLDispScreen_id').getValue(),
										Person_id: base_form.findField('Person_id').getValue(),
										AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue()
									},
									globalFilters: {
										withoutAgeGroups: win.withoutAgeGroups,
										EvnPLDispScreen_id: base_form.findField('EvnPLDispScreen_id').getValue(),
										Person_id: base_form.findField('Person_id').getValue(),
										AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
										ScreenType_id: newValue,
										EvnPLDispScreen_setDate: Ext.util.Format.date(base_form.findField('EvnPLDispScreen_setDate').getValue(), 'd.m.Y')
									}, noFocusOnLoad: true
								});
							}
						},
						width: 600,
						xtype: 'swcommonsprcombo'
					}]
				}, {
					border: false,
					layout: 'form',
					hidden: getRegionNick() == 'kz',
					items: [{
						allowBlank: false,
						comboSubject: 'AgeGroupDisp',
						fieldLabel: lang['vozrastnaya_gruppa'],
						id: 'AgeGroupCombo',
						disabled: getRegionNick() == 'kz',
						hiddenName: 'AgeGroupDisp_id',
						moreFields: [
							{ name: 'AgeGroupDisp_From', mapping: 'AgeGroupDisp_From' },
							{ name: 'AgeGroupDisp_To', mapping: 'AgeGroupDisp_To' },
							{ name: 'AgeGroupDisp_monthFrom', mapping: 'AgeGroupDisp_monthFrom' },
							{ name: 'AgeGroupDisp_monthTo', mapping: 'AgeGroupDisp_monthTo' }
						],
						lastQuery: '',
						width: 300,
						xtype: 'swcommonsprcombo'
					}]
				}]
			},
				//Рекомендованые скрининговые осмотры
				win.EvnUslugaRecommendDispDopPanel,
				// маршрутная карта
				win.EvnUslugaDispDopPanel,
				// опрос по скрининг-тесту
				win.DopDispQuestionPanel,
				// основные результаты диспансеризации
				win.EvnPLDispScreenMainResultsPanel
			],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey())
					{
						case Ext.EventObject.C:
							if (this.action != 'view')
							{
								this.doSave(false);
							}
							break;

						case Ext.EventObject.G:
							this.printEvnPLDispScreen();
							break;

						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.G, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'EvnPLDispScreen_id' },
				{ name: 'accessType' },
				{ name: 'EvnPLDispScreen_setDate' },
				{ name: 'DispClass_id' },
				{ name: 'Lpu_id' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'PersonHeight_Height' },
				{ name: 'PersonWeight_Weight' },
				{ name: 'EvnPLDispScreen_PersonWaist' },
				{ name: 'EvnPLDispScreen_QueteletIndex' },
				{ name: 'EvnPLDispScreen_ArteriaSistolPress' },
				{ name: 'EvnPLDispScreen_ArteriaDiastolPress' },
				{ name: 'AlcoholIngestType_bid' },
				{ name: 'AlcoholIngestType_vid' },
				{ name: 'AlcoholIngestType_wid' },
				{ name: 'EvnPLDispScreen_IsAlco' },
				{ name: 'EvnPLDispScreen_IsBleeding' },
				{ name: 'EvnPLDispScreen_IsCoronary' },
				{ name: 'EvnPLDispScreen_IsHeadache' },
				{ name: 'EvnPLDispScreen_IsHeartache' },
				{ name: 'EvnPLDispScreen_IsHighPressure' },
				{ name: 'EvnPLDispScreen_IsParCoronary' },
				{ name: 'EvnPLDispScreen_IsSmoking' },
				{ name: 'HealthKind_id' },
				{ name: 'AgeGroupDisp_id' },
				{ name: 'ScreenType_id' },
				{ name: 'ScreenEndCause_id' },
				{ name: 'EvnPLDispScreen_IsEndStage' },
				//{ name: 'WaistCircumference_id' },
				{ name: 'EvnPLDispScreen_IsBlurVision' },
				{ name: 'EvnPLDispScreen_IsDailyPhysAct' },
				{ name: 'EvnPLDispScreen_IsDirectedPMSP' },
				{ name: 'EvnPLDispScreen_IsGenPredisposed' },
				{ name: 'EvnPLDispScreen_IsGlaucoma' },
				{ name: 'EvnPLDispScreen_IsHealthy' },
				{ name: 'EvnPLDispScreen_IsHighMyopia' },
				{ name: 'EvnPLDispScreen_IsHyperglycaemia' },
				{ name: 'EvnPLDispScreen_IsHyperlipidemia' },
				{ name: 'EvnPLDispScreen_IsHypertension' },
				{ name: 'EvnPLDispScreen_IsLowPhysAct' },
				{ name: 'EvnPLDispScreen_IsOverweight' },
				{ name: 'EvnPLDispScreen_IsVisImpair' },
				{ name: 'FecalCasts_id' },
				{ name: 'EvnPLDispScreen_IsAlcoholAbuse' },
				{ name: 'EvnPLDispScreen_IsDisability' },
				{ name: 'EvnPLDispScreen_DisabilityYear' },
				{ name: 'EvnPLDispScreen_DisabilityPeriod' },
				{ name: 'Diag_disid' }
			]),
			url: '/?c=EvnPLDispScreen&m=saveEvnPLDispScreen'
		});

		Ext.apply(this, {
			items: [
				// паспортная часть человека
				win.PersonInfoPanel,
				win.EvnPLDispScreenFormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EPLDSEF_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDSEF_PrintButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EPLDSEF_IsFinishCombo').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			}, {
				hidden: true,
				handler: function() {
					this.printEvnPLDispScreen();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDSEF_PrintButton',
				tabIndex: 2407,
				text: lang['pechat_statisticheskaya_karta_forma_025-08_u']
			}, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDSEF_CancelButton',
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispScreenEditWindow.superclass.initComponent.apply(this, arguments);
	},
	loadScoreField: function() {
		// расчёт поля SCORE
		var win = this;
		var base_form = this.EvnPLDispScreenFormPanel.getForm();

		win.getLoadMask('Расчёт суммарного сердечно-сосудистого риска').show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.SCORE ) {
						base_form.findField('EvnPLDispScreen_SumRick').setValue(response_obj.SCORE);
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка расчёта суммарного сердечно-сосудистого риска');
				}
			},
			params: {
				EvnPLDisp_id: base_form.findField('EvnPLDispScreen_id').getValue()
			},
			url: '/?c=EvnUslugaDispDop&m=loadScoreField'
		});
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPLDispScreenEditWindow');
			var tabbar = win.findById('EPLDSEF_EvnPLTabbar');

			switch (e.getKey())
			{
				case Ext.EventObject.C:
					win.doSave();
					break;

				case Ext.EventObject.J:
					win.hide();
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 570,
	minWidth: 800,
	modal: true,
	onHide: Ext.emptyFn,
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null
	},

	plain: true,
	printEvnPLDispScreen: function(print_blank) {
		var win = this;
		var base_form = this.EvnPLDispScreenFormPanel.getForm();
		win.doSave({
			callback: function() {
				var evn_pl_id = base_form.findField('EvnPLDispScreen_id').getValue();

				var template = 'f025_08u.rptdesign';
				printBirt({
					'Report_FileName': template,
					'Report_Params': '&paramEvnPLDispScreen_id=' + evn_pl_id,
					'Report_Format': 'pdf'
				});
			}
		});
	},
	resizable: true,
	setAgeGroupDispCombo: function() {
		var win = this;
		var base_form = this.EvnPLDispScreenFormPanel.getForm();
		var age = -1;
		if ( !Ext.isEmpty(win.Year) ) {
			var year = win.Year;
			var endYearDate = new Date(year, 11, 31);
			age = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), endYearDate);
		}

		var agegroupcombo = base_form.findField('AgeGroupDisp_id');
		agegroupcombo.getStore().clearFilter();
		var index = agegroupcombo.getStore().findBy(function(record) {
			if (record.get('AgeGroupDisp_From') <= age && record.get('AgeGroupDisp_To') >= age) {
				return true;
			}
			else {
				return false;
			}
		});
		if (index >= 0) {
			agegroupcombo.setValue(agegroupcombo.getStore().getAt(index).get('AgeGroupDisp_id'));
		}
	},
	show: function() {
		sw.Promed.swEvnPLDispScreenEditWindow.superclass.show.apply(this, arguments);
		this.evnUslugaDispDopGrid.getGrid().getStore().removeAll();

		if (!arguments[0])
		{
			Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		var win = this;
		win.getLoadMask(LOAD_WAIT).show();

		this.restore();
		this.center();
		this.maximize();

		var form = this.EvnPLDispScreenFormPanel;
		form.getForm().reset();
		
		if (getRegionNick() == 'kz') {
			
			if (!this.evnUslugaDispDopGrid.getAction('action_cancelDirection')) {
				this.evnUslugaDispDopGrid.addActions({
					handler: function () {
						this.showEvnDirectionEditWindow('cancel');
					}.createDelegate(this),
					name: 'action_cancelDirection',
					text: 'Отменить направление',
					disabled: true
				});
			}
			
			if (!this.evnUslugaDispDopGrid.getAction('action_showDirection')) {
				this.evnUslugaDispDopGrid.addActions({
					handler: function () {
						this.showEvnDirectionEditWindow('view');
					}.createDelegate(this),
					name: 'action_showDirection',
					text: 'Просмотр направления',
					disabled: true
				});
			}
			
			if (!this.evnUslugaDispDopGrid.getAction('action_addDirection')) {
				this.evnUslugaDispDopGrid.addActions({
					handler: function () {
						this.showEvnDirectionEditWindow('add');
					}.createDelegate(this),
					name: 'action_addDirection',
					text: 'Добавить направление',
					disabled: true
				});
			}
		}

		if (getRegionNick() == 'kz') {
			
			if (!this.evnUslugaDispDopGrid.getAction('action_cancelDirection')) {
				this.evnUslugaDispDopGrid.addActions({
					handler: function () {
						this.showEvnDirectionEditWindow('cancel');
					}.createDelegate(this),
					name: 'action_cancelDirection',
					text: 'Отменить направление',
					disabled: true
				});
			}
			
			if (!this.evnUslugaDispDopGrid.getAction('action_showDirection')) {
				this.evnUslugaDispDopGrid.addActions({
					handler: function () {
						this.showEvnDirectionEditWindow('view');
					}.createDelegate(this),
					name: 'action_showDirection',
					text: 'Просмотр направления',
					disabled: true
				});
			}
			
			if (!this.evnUslugaDispDopGrid.getAction('action_addDirection')) {
				this.evnUslugaDispDopGrid.addActions({
					handler: function () {
						this.showEvnDirectionEditWindow('add');
					}.createDelegate(this),
					name: 'action_addDirection',
					text: 'Добавить направление',
					disabled: true
				});
			}
		}

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		var setDate = form.getForm().findField('EvnPLDispScreen_setDate');


		form.getForm().setValues(arguments[0]);

		if (arguments[0].action)
		{
			this.action = arguments[0].action;
		}

		if (arguments[0].Year)
		{
			this.Year = arguments[0].Year;
		}
		else
		{
			this.Year = null;
		}

		if (arguments[0].callback)
		{
			this.callback = arguments[0].callback;
		}

		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}

		// определенный медстафффакт
		if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 )
		{
			this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}
		else
		{
			this.UserMedStaffFact_id = null;
			// если в настройках есть medstafffact, то имеем список мест работы
			if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 )
			{
				this.UserMedStaffFacts = Ext.globalOptions.globals['medstafffact'];
				this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
			}
			else
			{
				// свободный выбор врача и отделения
				this.UserMedStaffFacts = null;
				this.UserLpuSections = null;
			}
		}

		// определенный LpuSection
		if ( arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0 )
		{
			this.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}
		else
		{
			this.UserLpuSection_id = null;
			// если в настройках есть lpusection, то имеем список мест работы
			if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 )
			{
				this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
			}
			else
			{
				// свободный выбор врача и отделения
				this.UserLpuSectons = null;
			}
		}

		var base_form = this.EvnPLDispScreenFormPanel.getForm();
		var EvnPLDispScreen_id = base_form.findField('EvnPLDispScreen_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();

		switch (win.action) {
			case 'add':
				win.setTitle(WND_POL_EPLDSADD);
				break;
			case 'edit':
				win.setTitle(WND_POL_EPLDSEDIT);
				break;
			case 'view':
				win.setTitle(WND_POL_EPLDSVIEW);
				break;
		}

		this.PersonInfoPanel.load({
			Person_id: person_id,
			Server_id: server_id,
			callback: function() {
				win.getLoadMask().hide();

				var minYear = Ext.util.Format.date(win.PersonInfoPanel.getFieldValue('Person_Birthday'), 'Y');
				base_form.findField('Server_id').setValue(win.PersonInfoPanel.getFieldValue('Server_id'));
				base_form.findField('PersonEvn_id').setValue(win.PersonInfoPanel.getFieldValue('PersonEvn_id'));
				base_form.findField('EvnPLDispScreen_DisabilityYear').minYear = minYear;

				if (win.action != 'view') {
					win.enableEdit(true);
					win.evnUslugaDispDopGrid.setReadOnly(false);
				} else {
					win.enableEdit(false);
					win.evnUslugaDispDopGrid.setReadOnly(true);
				}

				if (!Ext.isEmpty(EvnPLDispScreen_id)) {
					win.loadForm(EvnPLDispScreen_id);
				} else {
					setDate.setMinValue('01.05.2018');
					win.onLoadForm();
					win.handleAfterFirstMayFields();
					win.filterAlcoCombos();
					win.loadAgeDispGroupCombo(setDate.getValue());
					win.setAgeGroupDispCombo();
					win.handleDisabilityFields();
				}

				win.buttons[0].focus();
			} 
		});

		form.getForm().clearInvalid();
		this.doLayout();
	},
	loadForm: function(EvnPLDispScreen_id) {
		var win = this;
		var base_form = this.EvnPLDispScreenFormPanel.getForm(),
			setDate = base_form.findField('EvnPLDispScreen_setDate');
		win.getLoadMask(LOAD_WAIT).show();

		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				swEvnPLDispScreenEditWindow.hide();
			},
			params: {
				EvnPLDispScreen_id: EvnPLDispScreen_id,
				archiveRecord: win.archiveRecord
			},
			success: function() {
				win.getLoadMask().hide();
				win.Year = Ext.util.Format.date(Date.parseDate(setDate.getValue(), 'd.m.Y'), 'Y');

				if ( base_form.findField('accessType').getValue() == 'view' ) {
					win.action = 'view';
					win.enableEdit(false);
				}

				// для случаев до 1 мая скрываем поле даты
				if (setDate.getValue() && ! (setDate.getValue() > new Date(2018,3,30)))
				{
					setDate.hideContainer();
				} else
				{
					setDate.setMinValue('01.05.2018');
				}

				win.onLoadForm();
			},
			url: '/?c=EvnPLDispScreen&m=loadEvnPLDispScreenEditForm'
		});
	},
	onLoadForm: function() {
		var win = this;
		var base_form = win.EvnPLDispScreenFormPanel.getForm(),
			setDate = base_form.findField('EvnPLDispScreen_setDate');

		var beginDate = (Ext.query('#EvnPLDispScreen_setDate')[0]).value.split('.');
		beginDate = new Date(beginDate[2], beginDate[1] - 1, beginDate[0]);

		var withoutAgeGroupsDate = Date.parse('2018-08-01');	//c этой даты больше не учитываем возрастные группы #138439
		
		win.withoutAgeGroups = beginDate > withoutAgeGroupsDate;
		if ( getRegionNick() === 'kz' ) {
			win.withoutAgeGroups = false;
		}

		
		var questionPanel = Ext.getCmp('DopDispQuestionPanel'), 				//Опрос по скрининг-тесту
			ageGroupCombo = Ext.getCmp('AgeGroupCombo'), 						//возрастная группа
			recommendDispDop = Ext.getCmp('EvnUslugaRecommendDispDopPanel'), 	//Рекмендованные скрининговые исследования
			dispDopPanel = Ext.getCmp('EvnUslugaDispDopPanel'),					//Маршрутная карта
			mainResultPanel = Ext.getCmp('EvnPLDispScreenMainResultsPanel');	//Основные результаты

		
		if(win.withoutAgeGroups) {
			ageGroupCombo.hideContainer();
		}else {
			ageGroupCombo.showContainer();
		}

		if(win.action == 'add' && win.withoutAgeGroups) {
			recommendDispDop.show();
			dispDopPanel.hide();
			mainResultPanel.hide();
			questionPanel.hide();
			
		} else {
			recommendDispDop.hide();
			dispDopPanel.show();
			mainResultPanel.show();
			questionPanel.show();
		}		

		win.handleAfterFirstMayFields();
		win.filterAlcoCombos();
		win.loadAgeDispGroupCombo(setDate.getValue());
		win.setAgeGroupDispCombo();
		win.handleDisabilityFields();

		var params = {
			params: {
				EvnPLDispScreen_id: base_form.findField('EvnPLDispScreen_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
				EvnPLDispScreen_setDate: Ext.util.Format.date(base_form.findField('EvnPLDispScreen_setDate').getValue(), 'd.m.Y')
			},
			globalFilters: {
				withoutAgeGroups: win.withoutAgeGroups,
				EvnPLDispScreen_id: base_form.findField('EvnPLDispScreen_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
				EvnPLDispScreen_setDate: Ext.util.Format.date(base_form.findField('EvnPLDispScreen_setDate').getValue(), 'd.m.Y'),
				ScreenType_id: Ext.isEmpty(base_form.findField('ScreenType_id').getValue())?'':base_form.findField('ScreenType_id').getValue()
			}, noFocusOnLoad: true
		}

		if(win.action == 'add') {
			win.evnUslugaRecommendDispDopGrid.loadData(params);
		} else {
			win.evnUslugaDispDopGrid.loadData(params);
		}

		base_form.findField('EvnPLDispScreen_IsAlco').fireEvent('change', base_form.findField('EvnPLDispScreen_IsAlco'), base_form.findField('EvnPLDispScreen_IsAlco').getValue());
		base_form.findField('EvnPLDispScreen_IsEndStage').fireEvent('change', base_form.findField('EvnPLDispScreen_IsEndStage'), base_form.findField('EvnPLDispScreen_IsEndStage').getValue());
	},
	title: WND_POL_EPLDSADD,
	width: 800,
	handleDisabilityFields: function ()
	{
		var disabilityFields = ['EvnPLDispScreen_DisabilityYear','EvnPLDispScreen_DisabilityPeriod','Diag_disid'];
		this.checkVisibilityFields(disabilityFields);
	},
	handleAfterFirstMayFields: function()
	{
		// EvnPLDispScreen_setDate
		var fields = ['EvnPLDispScreen_IsDailyPhysAct','EvnPLDispScreen_IsBlurVision','EvnPLDispScreen_IsVisImpair','EvnPLDispScreen_IsGlaucoma','EvnPLDispScreen_IsHighMyopia',
			'FecalCasts_id','EvnPLDispScreen_IsBleeding','EvnPLDispScreen_IsHealthy','EvnPLDispScreen_IsDirectedPMSP',
			'biology_risk_factors','behaviour_risk_factors','EvnPLDispScreen_IsSmoking','EvnPLDispScreen_IsAlco','EvnPLDispScreen_IsCoronary'];


		this.checkVisibilityFields(fields);

	},
	checkVisibilityFields: function (fields)
	{
		var base_form = this.EvnPLDispScreenFormPanel.getForm(),
			sex_id = this.PersonInfoPanel.getFieldValue('Sex_id');
			var dateToday = base_form.findField('EvnPLDispScreen_setDate').getValue(),
				isDisability = base_form.findField('EvnPLDispScreen_IsDisability').getValue(), // true false
				realDate = new Date(),
				dateX = new Date(2018,3,30),
				age = this.PersonInfoPanel.getFieldValue('Person_Age'),
				allowBlank, visible;

		Ext.each(fields, function (field) {
			allowBlank = null;

			switch (field)
			{
				case 'EvnPLDispScreen_IsCoronary':
					visible = ! (dateToday > dateX);
					break;
				case 'EvnPLDispScreen_IsBleeding':
					visible = sex_id == 2;
					break;
				case 'EvnPLDispScreen_IsSmoking':
					allowBlank = ! visible;
					visible = true;
					break;
				case 'EvnPLDispScreen_IsAlco':
					allowBlank = ! visible;
					visible = true;
					break;
				case 'EvnPLDispScreen_DisabilityYear':
				case 'Diag_disid':
				case 'EvnPLDispScreen_DisabilityPeriod':
					visible = isDisability;
					break;
				case 'FecalCasts_id':
					visible = (dateToday > dateX) && (age > 50);
					break;
				default:
					visible = dateToday > dateX;
					break;
			}
			var combo = base_form.findField(field);

			if (combo == undefined)
			{
				combo = Ext.getCmp(field);
			}

			if (visible)
			{
				if (allowBlank === null)
				{
					allowBlank = combo.initialConfig.allowBlank == undefined;
				}
				if (typeof combo.showContainer == 'function')
				{
					combo.showContainer();
				} else
				{
					combo.show();
				}
				if (typeof combo.setAllowBlank == 'function')
				{
					combo.setAllowBlank(allowBlank);
				}
				if (! Ext.isEmpty(combo.defaultValue) && typeof combo.setValue == 'function')
				{
					combo.setValue(combo.defaultValue);
				}
			} else
			{
				if (typeof combo.hideContainer == 'function')
				{
					combo.hideContainer();
				} else
				{
					combo.hide();
				}
				if (typeof combo.setAllowBlank == 'function')
				{
					combo.setAllowBlank(true);
				}
				if (typeof combo.setValue == 'function')
				{
					combo.setValue(null);
				}
			}
		});

	},
	filterAlcoStoreBeforeFirstMay: function(age, sex_id)
	{
		var base_form = this.EvnPLDispScreenFormPanel.getForm();

		base_form.findField('AlcoholIngestType_bid').getStore().clearFilter();
		base_form.findField('AlcoholIngestType_bid').lastQuery = '';
		base_form.findField('AlcoholIngestType_bid').getStore().filterBy(function (rec) {
			if (rec.get('DrinkType_id') == 1 && rec.get('Sex_id') == sex_id && rec.get('AlcoholIngestType_From') <= age && (Ext.isEmpty(rec.get('AlcoholIngestType_To')) || rec.get('AlcoholIngestType_To') >= age)) {
				return true;
			}
			if (rec.get('AlcoholIngestType_id') == 74)
			{
				return true;
			}
			return false;
		});

		base_form.findField('AlcoholIngestType_wid').getStore().clearFilter();
		base_form.findField('AlcoholIngestType_wid').lastQuery = '';
		base_form.findField('AlcoholIngestType_wid').getStore().filterBy(function (rec) {
			if (rec.get('DrinkType_id') == 2 && rec.get('Sex_id') == sex_id && rec.get('AlcoholIngestType_From') <= age && (Ext.isEmpty(rec.get('AlcoholIngestType_To')) || rec.get('AlcoholIngestType_To') >= age)) {
				return true;
			}
			if (rec.get('AlcoholIngestType_id') == 74)
			{
				return true;
			}
			return false;
		});

		base_form.findField('AlcoholIngestType_vid').getStore().clearFilter();
		base_form.findField('AlcoholIngestType_vid').lastQuery = '';
		base_form.findField('AlcoholIngestType_vid').getStore().filterBy(function (rec) {
			if (rec.get('DrinkType_id') == 3 && rec.get('Sex_id') == sex_id && rec.get('AlcoholIngestType_From') <= age && (Ext.isEmpty(rec.get('AlcoholIngestType_To')) || rec.get('AlcoholIngestType_To') >= age)) {
				return true;
			}
			if (rec.get('AlcoholIngestType_id') == 74)
			{
				return true;
			}
			return false;
		});
	},
	filterAlcoStoreAfterFirstMay: function (age, sex_id)
	{
		var alco = ['AlcoholIngestType_bid','AlcoholIngestType_wid','AlcoholIngestType_vid'],
			base_form = this.EvnPLDispScreenFormPanel.getForm(),
			sexIndex = ['zero', 'male', 'female'];

		Ext.each(alco, function (field) {

			var combo = base_form.findField(field),
				ids = combo.ids;

			if (age <= 34 || age >= 66)
			{
				ids = ids['female'];
			} else if (age <= 65)
			{
				ids = ids[sexIndex[sex_id]];
			} else
			{
				ids = [];
			}

			combo.getStore().clearFilter();
			combo.lastQuery = '';

			combo.getStore().filterBy(function(rec) {
				if (rec.get('AlcoholIngestType_id') == 74 || rec.get('AlcoholIngestType_id').inlist(ids))
				{
					return true;
				}
				return false;
			});


			if (combo.getStore().find('AlcoholIngestType_id', combo.getValue()) == -1)
			{
				combo.clearValue();
			}

			combo = null;

		});
	},
	loadAgeDispGroupCombo: function (date)
	{
		var base_form = this.EvnPLDispScreenFormPanel.getForm(),
			ageGroup = base_form.findField('AgeGroupDisp_id');

		if (date > new Date(2018,3,30))
		{
			ageGroup.getStore().baseParams.where = 'where DispType_id = 5 and AgeGroupDisp_endDate is null';
		} else
		{
			ageGroup.getStore().baseParams.where = 'where DispType_id = 5';
		}

		ageGroup.getStore().load();
	},
	filterAlcoCombos: function ()
	{
		var base_form = this.EvnPLDispScreenFormPanel.getForm();
		var sex_id = this.PersonInfoPanel.getFieldValue('Sex_id');
		var age = this.PersonInfoPanel.getFieldValue('Person_Age');
		var dateToday = base_form.findField('EvnPLDispScreen_setDate').getValue(),
			dateX = new Date(2018,3,30);

		if (dateToday > dateX)
		{
			this.filterAlcoStoreAfterFirstMay(age, sex_id);
		} else
		{
			this.filterAlcoStoreBeforeFirstMay(age, sex_id);
		}
	}
});
