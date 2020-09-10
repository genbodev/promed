/**
 * Контрольные карты диспансерного наблюдения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */

Ext6.define('PersonDispDefaultPanel', {
	extend: 'swPanel',
	isLoaded: false,
	border: true,
	collapsible: true,
	collapsed: true,
	width: 1000,
	readOnly: false,
	onRender: function() {
		var me = this;
		me.callParent(arguments);

		if (me.plusButton) {
			me.addTool({
				type: 'plusbutton',
				itemId: 'plus',
				callback: function(panel, tool, event) {
					me.plusButton();
				}
			});
		}
	},
	checkContent: function() {
		var grid = this.queryBy(function(el) { return el.$className=='Ext.grid.Panel' ; })
		if(grid && grid[0]) {
			var always_collapsible = this.itemId ==  'PDEF_SicknessPanel';
			grid=grid[0];
			var N = grid.getStore().getCount();
			this.setTitleCounter(N);

			if(N==0) {
				if(!always_collapsible) {
					this.collapse();
					this.collapseTool.addCls('collapse-tool-hide');
				}
			} else {
				if(!always_collapsible)	this.collapseTool.removeCls('collapse-tool-hide');
				this.setTitleCounter(N);
			}
			if(N>0 && this.itemId == 'PDEF_PersonDispHistPanel') {
				this.removeCls('DispHistNull');
				this.addCls   ('DispHistNotNull');
			}
		} else this.setTitleCounter(0);
	},
	setReadOnly: function(enable) {
		this.readOnly = enable;
		if(this.readOnly) {

		}
	}
});

Ext6.define('common.EMK.PersonDispEditWindow', {
	layout: 'border',
	requires: [
		'common.EMK.PersonInfoPanel'
		,'common.EvnXml.ItemsPanel'
	],
	addHelpButton: Ext6.emptyFn,
	addCodeRefresh: Ext6.emptyFn,
	maximized: true,
	header: false,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	width: 1000,
	cls: 'arm-window-new emk-forms-window dispcard',
	extend: 'base.BaseForm',
	renderTo: main_center_panel.body.dom,
	title: 'Контрольная карта диспансерного наблюдения',
	constrain: true,
	userMedStaffFact: null,
	itemId: 'swPersonDispEditWindowExt6',

	loadGridData: function(prms, grid) {
		params = null;
		gFilters = null;
		noFocusOnLoad = false;

		if (prms) {
			if (prms.params) params = prms.params;
			if (prms.globalFilters) gFilters = prms.globalFilters;
			if (prms.noFocusOnLoad) noFocusOnLoad = true;
			if (prms.url) grid.getStore().proxy = new Ext6.data.HttpProxy({	url: prms.url });
		}

		grid.getStore().removeAll();
		grid.getStore().load(
			{
				params: gFilters,
				callback: (prms && prms.callback)?prms.callback:null || null
			}
		);
	},
	enablePrint030: function() {
		var base_form = this.FormPanel.getForm();
		var show_print030 = false;
		var diag = base_form.findField('Diag_id').getValue();
		if(diag && base_form.findField('Diag_id').getStore().getById(diag)){
			var diag_code = base_form.findField('Diag_id').getStore().getById(diag).get('Diag_Code');
			if(diag_code && diag_code.substr(0,3) >= 'A15' && diag_code.substr(0,3) <= 'A19'){
				show_print030 = true;
			}
		}
		this.queryById('print030').setDisabled(!show_print030);
	},
	print030: function(personDisp){
		if (!personDisp){
			Ext6.Msg.alert(langs('Ошибка'), 'Не передан идентификатор для печати');
			return;
		}
		printBirt({
			'Report_FileName': 'f030_4u.rptdesign',
			'Report_Params': '&paramPersonDisp=' + personDisp,
			'Report_Format': 'pdf'
		});
	},
	showSicknessPanels: function(visible) {
		if(visible) {
			this.SicknessPanel.show();
		} else {
			this.SicknessPanel.hide();
		}
	},
	showNephroPanels: function(visible) {
		this.MorbusNephroPanel.doShow(visible);
		if(visible) {
			this.MorbusNephroLabPanel.show();
		} else {
			this.MorbusNephroLabPanel.hide();
		}
	},
	showPregnancyPanels: function(visible=true) {
		var win = this;
		if(visible) {
			win.PregnancyHistoryGrid.load();
			win.PregnancyPanel.show();
			win.PregnancyLabPanel.show();
			win.PregnancySpecComplicationPanel.show();
			win.PregnancySpecExtragenitalDiseasePanel.show();
			win.ChildPanel.show();
			win.DeadChildPanel.show();
		} else {
			win.PregnancyPanel.hide();
			win.PregnancyLabPanel.hide();
			win.PregnancySpecComplicationPanel.hide();
			win.PregnancySpecExtragenitalDiseasePanel.hide();
			win.ChildPanel.hide();
			win.DeadChildPanel.hide();
		}
	},
	openPersonDispHistEditWindow: function(action) {
		var win = this;
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		if (getWnd('swPersonDispHistEditWindowExt6').isVisible()) {
			Ext6.Msg.alert('Сообщение', 'Окно редактирования ответственного врача уже открыто');
			return false;
		}
		if (win.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		
		var base_form = win.FormPanel.getForm();
		var grid = win.PersonDispHistGrid;
		var params = {
			action: action,
			callback: function(data) {
				grid.load();
			},
			PersonDisp_begDate: win.FormPanel.getForm().findField('PersonDisp_begDate').getValue(),
			PersonDisp_endDate: win.FormPanel.getForm().findField('PersonDisp_endDate').getValue()
		};
		if (action != 'add') {
			var selected_record = grid.getStore().getAt(grid.recordMenu.rowIndex);
			if (!selected_record || !selected_record.get('PersonDispHist_id') || selected_record.get('PersonDispHist_id') === 0) {
				Ext6.Msg.alert('Сообщение', 'Редактирование/просмотр доступны только после сохранения карты.');
				return false;
			}
			if (base_form.findField('PersonDisp_id').getValue() != '0') {
				params.PersonDispHist_id = selected_record.get('PersonDispHist_id');
				getWnd('swPersonDispHistEditWindowExt6').show(params);
			} else {
				win.doSave({
					doNotHide:true,
					callback: function(data){
						grid.getStore().load({
							params: {PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()},
							callback: function(){
								if(grid.getStore().data.length > 0){
									params.PersonDispHist_id = grid.getStore().getAt(0).get('PersonDispHist_id');
									getWnd('swPersonDispHistEditWindowExt6').show(params);
								}
							}
						});
					}
				});
			}
		} else {
			if (base_form.findField('PersonDisp_id').getValue() != '0') {
				params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
				getWnd('swPersonDispHistEditWindowExt6').show(params);
			} else {
				win.doSave({
					doNotHide:true,
					callback: function(data){
						params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
						getWnd('swPersonDispHistEditWindowExt6').show(params);
					}
				});
			}
		}
	},
	deletePersonDispHist: function() {
		var win = this;
		if (win.action == 'view') {
			return false;
		}
		var grid = win.PersonDispHistGrid;
		var row = grid.getStore().getAt(grid.recordMenu.rowIndex);
		if (!row || row.get('PersonDispHist_id') === 0) {
			return false;
		}
		Ext6.Msg.show({
			buttons: Ext6.Msg.YESNO,
			fn: function (buttonId) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function() {
							grid.getStore().remove(row);
						},
						params: {
							PersonDispHist_id: row.get('PersonDispHist_id')
						},
						url: '/?c=PersonDisp&m=deletePersonDispHist'
					});
				}
			},
			msg: 'Вы действительно желаете удалить эту запись?',
			title: 'Подтверждение удаления'
		});
	},
	disablePersonDispHistActions: function(disable){
		var win = this;
		if(win.action == 'view' && !isUserGroup('PersonDispHistEdit')){
			disable = true;
		}
		var grid = win.PersonDispHistGrid;
		var panel = win.PersonDispHistPanel;
		panel.tools.find(tool => tool.itemId === 'plus' ? tool.setVisible(!disable) : null); //panel.queryById('plus').setVisible(!disable); кнопка plus не всегда рендерится изза юзер групп, поэтому ошибка была
		grid.recordMenu.queryById('HistEdit').setDisabled(disable);
		grid.recordMenu.queryById('HistDelete').setDisabled(disable);
	},
	openBirthSpecChildDeathEditWindow:function (action) {
		//Беременность и роды - Мертворожденные - Просмотр
		var params = new Object();
		var grid = this.DeadChildGrid;
		params.action = action;

		if (!grid.getSelectionModel().getSelected()) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		params.formParams = selected_record.data;
		params.onHide = function () {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			grid.getSelectionModel().selectFirstRow();
		};

		getWnd('swBirthSpecStacChildDeathEditWindow').show(params);
	},
	//TAG: Беременность. Actions
	//Осложнение беременности
	openPregnancySpecComplicationEditWindow: function (action) {
		var params = new Object();
		var win = this;
		var grid = win.PregnancySpecComplicationGrid;
		params.formParams = {};
		params.gridRecords = sw4.getStoreRecords(grid.getStore(), {
			convertDateFields: true,
			exceptionFields: [
				 'Diag_Name'
			]
		});
		params.callback = function(data) {
			if (!data || !data.ComplicationData) {
				return false;
			}
			data.ComplicationData.RecordStatus_Code = 0;
			// Обновить запись в grid

			var record = grid.getStore().getAt(grid.recordMenu.rowIndex);
			if (record && action!='add') {
				if (record.get('RecordStatus_Code') == 1) {
					data.ComplicationData.RecordStatus_Code = 2;
				}

				record.set('PregnancySpecComplication_id', data.ComplicationData['PregnancySpecComplication_id']);
				record.set('PregnancySpec_id', data.ComplicationData['PregnancySpec_id']);
				record.set('PSC_setDT', data.ComplicationData['PSC_setDT']);
				record.set('Diag_id', data.ComplicationData['Diag_id']);
				record.set('Diag_Name', data.ComplicationData['Diag_Name']);

				//~ var grid_fields = new Array();
				//~ grid.getStore().fields.eachKey(function(key, item) {
					//~ grid_fields.push(key);
				//~ });
				//~ for (i = 0; i < grid_fields.length; i++) {
					//~ record.set(grid_fields[i], data.ComplicationData[grid_fields[i]]);
				//~ }
				record.commit();
			} else {
				//~ if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PregnancySpecComplication_id')) {
					//~ grid.getStore().removeAll();
				//~ }
				data.ComplicationData.PregnancySpecComplication_id = -Math.floor(Math.random() * 1000000);
				grid.getStore().loadData([ data.ComplicationData ], true);
			}
			win.PregnancySpecComplicationPanel.checkContent();
		}
		params.action = action;
		if (action.inlist(['view','edit'])) {
			//~ if (!grid.getSelectionModel().getSelected()) {
				//~ return false;
			//~ }
			var selected_record = grid.getStore().getAt(grid.recordMenu.rowIndex);
			params.formParams = selected_record.data;
			//~ params.onHide = function() {
				//~ grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			//~ }
		} else {
			//~ params.onHide = function() {
				//~ grid.getView().focusRow(0);
			//~ };
		}
		getWnd('swPregnancySpecComplicationEditWindowExt6').show(params);
	},
	openPregnancySpecExtragenitalDiseaseEditWindow: function(action) {
		var params = new Object(),
			win = this,
			grid = win.PregnancySpecExtragenitalDiseaseGrid;
		params.formParams = {};
		params.gridRecords = sw4.getStoreRecords(grid.getStore(), {
			convertDateFields: true,
			exceptionFields: [
				 'Diag_Name'
			]
		});
		params.callback = function(data) {
			if (!data || !data.ExtragenitalDiseaseData) {
				return false;
			}
			data.ExtragenitalDiseaseData.RecordStatus_Code = 0;
			// Обновить запись в grid
			var record = grid.getStore().getAt(grid.recordMenu.rowIndex);
			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.ExtragenitalDiseaseData.RecordStatus_Code = 2;
				}

				record.set('PSED_id', data.ExtragenitalDiseaseData['PSED_id']);
				record.set('PregnancySpec_id', data.ExtragenitalDiseaseData['PregnancySpec_id']);
				record.set('PSED_setDT', data.ExtragenitalDiseaseData['PSED_setDT']);
				record.set('Diag_id', data.ExtragenitalDiseaseData['Diag_id']);
				record.set('Diag_Name', data.ExtragenitalDiseaseData['Diag_Name']);

				record.commit();
			} else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PSED_id')) {
					grid.getStore().removeAll();
				}
				data.ExtragenitalDiseaseData.PSED_id = -Math.floor(Math.random() * 1000000);
				grid.getStore().loadData([ data.ExtragenitalDiseaseData ], true);
			}
			win.PregnancySpecExtragenitalDiseasePanel.checkContent();
		}
		params.action = action;
		if (action.inlist(['view','edit'])) {
			var selected_record = grid.getStore().getAt(grid.recordMenu.rowIndex);
			params.formParams = selected_record.data;
		}

		getWnd('swPregnancySpecExtragenitalDiseaseEditWindowExt6').show(params);
	},
	//Беременность. Удаление Лабораторного обследования
	deletePregnancyLab: function() {
		var win = this;
		if (win.action == 'view') {
			return false;
		}
		var error = 'При удалении лабораторного обследования возникли ошибки';
		var grid = win.PregnancyLabGrid;

		var question = 'Удалить лабораторное обследование?';
		var url = '/?c=EvnUsluga&m=deleteEvnUsluga';
		var params = new Object();
		if (!grid || win.PregnancyLabGrid.recordMenu.rowIndex<0) {
			return false;
		}
		var selected_record = win.PregnancyLabGrid.getStore().getAt(win.PregnancyLabGrid.recordMenu.rowIndex);
		params['class'] = 'EvnUslugaPregnancySpec';
		params['id'] = selected_record.get('EvnUslugaPregnancySpec_id');
		Ext6.Msg.show({
			buttons: Ext6.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					//var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
					//loadMask.show();
					Ext6.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							Ext6.Msg.alert('Ошибка', error);
						},
						params: params,
						success: function(response, options) {
							//loadMask.hide();
							var response_obj = Ext6.util.JSON.decode(response.responseText);
							if (response_obj.success == false) {
								Ext6.Msg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
							} else {
								grid.getStore().remove(selected_record);
								win.PregnancyLabPanel.checkContent();
							}
						},
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext6.MessageBox.QUESTION,
			msg: question,
			title: 'Вопрос'
		});
	},
	//Беременность. Редактирование Лабораторного обследования
	openEvnUslugaEditWindow: function(action) {
		if (action != 'add' && action != 'edit' && action != 'view') {
			return false;
		}
		var win=this;
		var grid = win.PregnancyLabGrid;
		var row = grid.getStore().getAt(grid.recordMenu.rowIndex);
		if (win.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		var params = new Object();
		params.action = action;
		params.callback = function(data) {
			if (!data || !data.evnUslugaData) {
				grid.getStore().load({
					params: {
						PregnancySpec_id: win.FormPanel.getForm().findField('PregnancySpec_id').getValue()
					}
				});
				return false;
			}
			var record = grid.getStore().getById(data.evnUslugaData.EvnUsluga_id);
			data.evnUslugaData.EUPS_setDate = data.evnUslugaData['EvnUsluga_setDate'];
			data.evnUslugaData.lpu_name = data.evnUslugaData['lpu_name'];
			data.evnUslugaData.EvnUslugaPregnancySpec_id = data.evnUslugaData['EvnUsluga_id'];
			if (!record) {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnUslugaPregnancySpec_id')) {
					grid.getStore().removeAll();
				}
				grid.getStore().loadData([ data.evnUslugaData ], true);
			} else {
				record.set('EUPS_setDate', data.evnUslugaData['EUPS_setDate']);
				record.set('Usluga_Code', data.evnUslugaData['Usluga_Code']);
				record.set('Usluga_Name', data.evnUslugaData['Usluga_Name']);
				record.set('lpu_name', data.evnUslugaData['lpu_name']);
				//устанавливаю значения полей, которые не установились по каким-либо причинам в цикле
				record.commit();

			}
			win.calcPregPeriod();
			win.PregnancyLabPanel.checkContent();
		}.createDelegate(win);
		params.parentClass = 'PersonDisp';//'EvnPS';
		params.Person_id = win.PersonInfoPanel.getFieldValue('Person_id');
		params.Person_Birthday = win.PersonInfoPanel.getFieldValue('Person_Birthday');
		params.Person_Firname = win.PersonInfoPanel.getFieldValue('Person_Firname');
		params.Person_Secname = win.PersonInfoPanel.getFieldValue('Person_Secname');
		params.Person_Surname = win.PersonInfoPanel.getFieldValue('Person_Surname');
		var parent_evn_combo_data = new Array();
		params.formParams = {
			Person_id: win.PersonInfoPanel.getFieldValue('Person_id'),
			PersonEvn_id: win.PersonInfoPanel.getFieldValue('PersonEvn_id'),
			Server_id: win.PersonInfoPanel.getFieldValue('Server_id')
		}

		params.parentEvnComboData = [{ Evn_id: 0 }];
		params.doSave = function(options) {
			//log('->doSave');
			// options @Object
			// options.openChildWindow @Function Открыть доченрее окно после сохранения
			var EvnUslugaCommonEditWindow = getWnd('EvnUslugaCommonEditWindow');
			//~ if (EvnUslugaCommonEditWindow.formStatus == 'save' || EvnUslugaCommonEditWindow.action == 'view') {
				//~ return false;
			//~ }
			//~ EvnUslugaCommonEditWindow.formStatus = 'save';
			var base_form = EvnUslugaCommonEditWindow.down('form').getForm();
			if (!base_form.isValid()) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						EvnUslugaCommonEditWindow.formStatus = 'edit';
						base_form.findField('EvnUslugaCommon_setDate').focus(true, 100);
					}.createDelegate(EvnUslugaCommonEditWindow),
					icon: Ext6.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			var evn_usluga_set_time = base_form.findField('EvnUslugaCommon_setTime').getValue();
			var evn_usluga_common_pid = base_form.findField('EvnUslugaCommon_pid').getValue();
			if ((EvnUslugaCommonEditWindow.parentClass == 'EvnVizit' || EvnUslugaCommonEditWindow.parentClass == 'EvnPS') && !evn_usluga_common_pid) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						//~ EvnUslugaCommonEditWindow.formStatus = 'edit';
						base_form.findField('EvnUslugaCommon_pid').focus(true);
					}.createDelegate(EvnUslugaCommonEditWindow),
					icon: Ext6.Msg.WARNING,
					msg: 'Не выбрано отделение (посещение)',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			var med_personal_id = null;
			var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
			var params = new Object();
			var record = null;
			var usluga_code = '';
			var usluga_id = 0;
			var usluga_name = '';
			var usluga_place_code = 0;
			// MedPersonal_id
			index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == med_staff_fact_id);
			});

			if ( index >= 0 ) {
				base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));
				med_personal_id = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id');
			}
			record = base_form.findField('UslugaPlace_id').getStore().getById(base_form.findField('UslugaPlace_id').getValue());
			var lpu_name = '';
			if (record) {
				usluga_place_code = Number(record.get('UslugaPlace_Code'));
				record = base_form.findField('UslugaComplex_id').getStore().getById(base_form.findField('UslugaComplex_id').getValue());
				if (record) {
					usluga_code = record.get('UslugaComplex_Code');
					usluga_id = record.get('UslugaComplex_id');
					usluga_name = record.get('UslugaComplex_Name');
				}

				switch (usluga_place_code) {
					case 1:
						//1 Отделение ЛПУ
						lpu_name = getGlobalOptions().lpu_nick; //Ext.globalOptions.globals.lpu_nick;
						break;
					case 2:
						//2 Другое ЛПУ
						lpu_name = base_form.findField('Lpu_uid').getRawValue();
						break;
					case 3:
						//3 Другая организация
						lpu_name = base_form.findField('Org_uid').getRawValue();
						break;
				}
			}
			if (!usluga_id) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						EvnUslugaCommonEditWindow.formStatus = 'edit';
						EvnUslugaCommonEditWindow.buttons[0].focus();
					}.createDelegate(EvnUslugaCommonEditWindow),
					icon: Ext6.Msg.WARNING,
					msg: 'Не выбрана оказываемая услуга',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			base_form.findField('UslugaComplex_id').setValue(usluga_id);
			var loadMask = new Ext6.LoadMask(EvnUslugaCommonEditWindow, { msg: LOAD_WAIT_SAVE });
			loadMask.show();
			params.EvnUslugaPregnancySpec_id = base_form.findField('EvnUslugaCommon_id').getValue();
			if(!params.EvnUslugaPregnancySpec_id) params.EvnUslugaPregnancySpec_id=0;
			params.EvnUslugaPregnancySpec_rid = 0;
			params.PregnancySpec_id = win.PregnancyPanel.formFields.PregnancySpec_id.getValue();
			base_form.findField('MedPersonal_id').setValue(med_personal_id)
			params.MedPersonal_id = med_personal_id;
			params.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
			if (base_form.findField('EvnUslugaCommon_pid').disabled) {
				params.EvnUslugaCommon_pid = evn_usluga_common_pid;
			}

			var wnd = EvnUslugaCommonEditWindow.getController().getView(),
				wndVm = EvnUslugaCommonEditWindow.getController().getViewModel(),
				formPanel = wnd.down('UslugaEditForm'),
				form = formPanel.getForm(),
				vm = formPanel.getViewModel(),
				tabPanel = wnd.down('tabpanel'),
				EvnUslugaCommon_pid = vm.get('EvnUslugaCommon_pid'),
				parentClass = vm.get('parentClass'),
				action = vm.get('action');

			form.submit({
				url: wnd.submitUrl,
				params: params,
				failure: function(result_form, action) {
					EvnUslugaCommonEditWindow.formStatus = 'edit';
					loadMask.hide();
					if (action.result) {
						if (action.result.Error_Msg) {
							Ext6.Msg.alert('Ошибка', action.result.Error_Msg);
						} else {
							Ext6.Msg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
						}
					}
				}.createDelegate(EvnUslugaCommonEditWindow),
				success: function(result_form, action) {
					EvnUslugaCommonEditWindow.formStatus = 'edit';
					loadMask.hide();
					if (action.result && action.result.EvnUslugaCommon_id > 0) {
						base_form.findField('EvnUslugaCommon_id').setValue(action.result.EvnUslugaCommon_id);
						if (options && typeof options.openChildWindow == 'function' && EvnUslugaCommonEditWindow.action == 'add') {
							options.openChildWindow();
						} else {
							var data = new Object();
							var set_time = base_form.findField('EvnUslugaCommon_setTime').getValue();
							if (!set_time || set_time.length == 0) {
								set_time = '00:00';
							}
							data.evnUslugaData = {
								'accessType': 'edit',
								'EvnClass_SysNick': 'EvnUslugaCommon',
								'EvnUsluga_Kolvo': base_form.findField('EvnUslugaCommon_Kolvo').getValue(),
								'EvnUsluga_id': base_form.findField('EvnUslugaCommon_id').getValue(),
								'EvnUsluga_setDate': base_form.findField('EvnUslugaCommon_setDate').getValue(),
								'EvnUsluga_setTime': set_time,
								'Usluga_Code': usluga_code,
								'Usluga_Name': usluga_name,
								'lpu_name': lpu_name
							};
							EvnUslugaCommonEditWindow.callback(data);
							EvnUslugaCommonEditWindow.hide();
						}
					} else {
						Ext6.Msg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
					}
				}.createDelegate(getWnd('EvnUslugaCommonEditWindow')) //}.createDelegate(getWnd('swEvnUslugaEditWindow'))
			});
			//log('<-doSave');
		};
		params.formUrl = '/?c=PregnancySpec&m=saveEvnUslugaPregnancySpec';
		params.formLoadUrl = '/?c=PregnancySpec&m=loadEvnUslugaPregnancySpecForm';
		switch (action) {
			case 'add':
				if (win.FormPanel.getForm().findField('PersonDisp_id').getValue() != '0') {
					if (!win.PregnancyPanel.formFields.PregnancySpec_id.getValue()) {
						win.PregnancyPanel.save(function (){
							getWnd('EvnUslugaCommonEditWindow').show(params);//getWnd('swEvnUslugaEditWindow').show(params);
						});
					} else {
						getWnd('EvnUslugaCommonEditWindow').show(params);//getWnd('swEvnUslugaEditWindow').show(params);
					}
				} else {
					//log('starting saving followed by adding');
					win.doSave({
						doNotHide:true,
						callback: function(){
							getWnd('EvnUslugaCommonEditWindow').show(params);//getWnd('swEvnUslugaEditWindow').show(params);
						}
					});
				}
				break;
			case 'edit':
			case 'view':
				var row = win.PregnancyLabGrid.getStore().getAt(win.PregnancyLabGrid.recordMenu.rowIndex);
				if (!row || !row.get('EvnUslugaPregnancySpec_id')) {
					return false;
				}
				var evn_usluga_id = row.get('EvnUslugaPregnancySpec_id');

				params.formParams.MedPersonal_id = row.get('MedPersonal_id');
				params.formParams.EvnUslugaCommon_id = evn_usluga_id;
				if (!win.PregnancyPanel.formFields.PregnancySpec_id.getValue()) {
					win.PregnancyPanel.save(function (){
						getWnd('EvnUslugaCommonEditWindow').show(params); //getWnd('swEvnUslugaEditWindow').show(params);
					});
				} else {
					getWnd('EvnUslugaCommonEditWindow').show(params); //getWnd('swEvnUslugaEditWindow').show(params);
				}
		}
	},
	//Беременность. Редактирование истории диагнозов
	openPregnancyDiagEditWindow: function(action) {
		var win = this;
		var bf = win.FormPanel.getForm();
		var params = {
			action: action,
			callback: function() {

					win.PregnancyHistoryGrid.load();
				},
			onClose: function() {}
		};

		if(action=='view' || action=='edit') {
			params.DiagDispCard_id = win.PregnancyHistoryGrid.recordMenu.data_id;
		}
		getWnd('swPersonDispDiagHistoryEditWindowExt6').show(params);
	},
	//регистр заболеваний. редактирование
	openPersonDispMedicamentEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		if (getWnd('swPersonDispDrugAddWindow').isVisible()) {
			Ext6.Msg.alert('Сообщение', 'Окно редактирования назначаемого медикамента уже открыто');
			return false;
		}
		var win = this;
		if (win.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		var base_form = win.FormPanel.getForm();
		var grid = win.PersonDispMedicamentGrid;
		var params = new Object();
		var idx;
		var person_disp_beg_date = base_form.findField('PersonDisp_begDate').getValue();
		var person_disp_id = base_form.findField('PersonDisp_id').getValue();
		var privilege_type_id;
		var record;
		var sickness_id = base_form.findField('Sickness_id').getValue();
		if (!sickness_id) {
			return false;
		}
		idx = base_form.findField('Sickness_id').getStore().findBy(function(rec) {
			if (rec.get('Sickness_id') == sickness_id) {
				return true;
			} else {
				return false;
			}
		});
		if (idx >= 0) {
			privilege_type_id = base_form.findField('Sickness_id').getStore().getAt(idx).get('PrivilegeType_id');
		}
		params.callback = function(data) {
			// если медикамент был сохранен формой редактирования медикамента, то просто обновляем грид
			if (data.medicamentWasSaved) {
				grid.load();
				/*grid.getStore().load({
					params: {
						PersonDisp_id: person_disp_id
					}
				});*/
				return;
			}
			if (grid.getStore().getCount() == 1) {
				var row = grid.getStore().getAt(0);
				if (!row.get('Drug_id')) {
					grid.getStore().removeAll();
				}
			}
			idx = grid.getStore().findBy(function(rec) {
				if (rec.get('PersonDispMedicament_id') == data.PersonDispMedicament_id) {
					return true;
				} else {
					return false;
				}
			});
			if (idx >= 0) {
				record = grid.getStore().getAt(idx);
				record.set('PersonDispMedicament_id', data.PersonDispMedicament_id);
				record.set('PersonDisp_id', 0);
				record.set('DrugMnn_id', data.DrugMnn_id);
				record.set('Drug_id', data.Drug_id);
				record.set('Drug_Name', data.Drug_Name);
				record.set('Drug_Price', data.DrugPrice);
				record.set('Drug_Count', data.Course);
				record.set('PersonDispMedicament_begDate', data.Course_begDate);
				record.set('PersonDispMedicament_endDate', data.Course_endDate);
				record.commit();
			}
		};
		params.params = {
			action: action,
			PrivilegeType_id: privilege_type_id,
			PersonDisp_id: person_disp_id,
			PersonDisp_begDate: person_disp_beg_date
		};
		if (action == 'add') {
		} else {
			var selected_record = grid.getStore().getAt(grid.recordMenu.rowIndex);
			if (!selected_record || !selected_record.get('Drug_id')) {
				return false;
			}
			params.params.medicament_data = selected_record.data;
		}
		getWnd('swPersonDispDrugAddWindow').show(params);
	},
	deletePersonDispMedicament: function() {
		if (this.action == 'view') {
			return false;
		}
		var grid = this.PersonDispMedicamentGrid;
		var row = grid.getStore().getAt(grid.recordMenu.rowIndex);
		if (!row) {
			return false;
		}
		Ext6.Msg.show({
			buttons: Ext6.Msg.YESNO,
			fn: function (buttonId) {
				if (buttonId == 'yes') {
					if (row.get('PersonDispMedicament_id') <= 0) {
						grid.getStore().remove(row);
						if (grid.getStore().getCount() == 0) {
							grid.checkContent();
						}
					} else {
						Ext6.Ajax.request({
							callback: function() {
								grid.getStore().remove(row);
								if (grid.getStore().getCount() == 0) {
									grid.checkContent();
								}
							},
							params: {
								PersonDispMedicament_id: row.get('PersonDispMedicament_id')
							},
							url: '/?c=PersonDisp&m=deletePersonDispMedicament'
						});
					}
				}
			},
			msg: 'Вы действительно желаете удалить эту запись?',
			title: 'Подтверждение удаления'
		});
	},
	//TAG: ЦЕЛЕВЫЕ ПОКАЗАТЕЛИ редактирование
	openPersonDispTargetRateEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		if (isVisibleWnd('swPersonDispTargetRateEditWindowExt6')) {
			Ext6.Msg.alert('Сообщение', 'Окно редактирования целевых показателей уже открыто');
			return false;
		}
		var win = this,
			base_form = win.FormPanel.getForm(),
			grid = win.PersonDispTargetRateGrid;
		if (!grid.recordMenu.data_id) {
			return false;
		}
		var params = {
			action: action,
			PersonDisp_id: base_form.findField('PersonDisp_id').getValue(),
			callback: function(data) {
				grid.getStore().reload();
			}
		};

		params.RateType_id = grid.recordMenu.data_id;
		getWnd('swPersonDispTargetRateEditWindowExt6').show(params);
	},
	deletePersonDispRate: function(id) {
		if(id>0) {
			var win = this,
				grid = win.PersonDispTargetRateGrid,
				pdfr_id = win.PersonDispTargetRateGrid.getStore().getById(id).data.PersonDispFactRate_id,
				rate_id = win.PersonDispTargetRateGrid.getStore().getById(id).data.Rate_id;
			if(pdfr_id>0) {
				Ext6.Ajax.request({
					callback: function() {
						grid.load();
					},
					params: {
						PersonDispFactRate_id: pdfr_id,
						Rate_id: rate_id
					},
					url: '/?c=PersonDisp&m=deletePersonDispFactRate'
				});
			}
		}
	},
	openPersonDispSopDiagEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		//~ if (getWnd('swPersonDispSopDiagEditWindowExt6').isVisible()) {
		if( isVisibleWnd('swPersonDispSopDiagEditWindowExt6') ) {
			Ext6.Msg.alert('Сообщение', 'Окно редактирования сопутствующих диагнозов уже открыто');
			return false;
		}
		var win = this;
		if (win.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		
		var base_form = win.FormPanel.getForm();
		var grid = win.PersonDispSopDiagGrid;
		var params = {
			action: action,
			callback: function(data) {
				grid.getStore().load({
					params: {PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()}
				});
			}
		};
		if (action != 'add') {
			if (!grid.recordMenu.data_id) {
				return false;
			}
			params.PersonDispSopDiag_id = grid.recordMenu.data_id;
			getWnd('swPersonDispSopDiagEditWindowExt6').show(params);
		} else {
			if (base_form.findField('PersonDisp_id').getValue() != '0') {
				params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
				getWnd('swPersonDispSopDiagEditWindowExt6').show(params);
			} else {
				win.doSave({
					doNotHide:true,
					callback: function(data){
						params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
						getWnd('swPersonDispSopDiagEditWindowExt6').show(params);
					}
				});
			}
		}
	},
	deletePersonDispSopDiag: function() {
		if (this.action == 'view') {
			return false;
		}
		var grid = this.PersonDispSopDiagGrid;
		var row = grid.recordMenu.data_id;
		if (!row) {
			return false;
		}
		Ext6.Msg.show({
			buttons: Ext6.Msg.YESNO,
			icon: Ext6.Msg.QUESTION,
			fn: function (btn) {
				if (btn == 'yes') {
					Ext6.Ajax.request({
						callback: function() {
							grid.load();
						},
						params: {
							PersonDispSopDiag_id: grid.recordMenu.data_id
						},
						url: '/?c=PersonDisp&m=delPersonDispSopDiag'
					});
				}
			},
			msg: 'Вы действительно желаете удалить эту запись?',
			title: 'Подтверждение удаления'
		});
	},

	openNephroLabEditWindow: function(action) {
		var win = this;
		var windowname = 'swMorbusNephroLabWindowExt6';
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}
		if (action == 'add' &&  isVisibleWnd(windowname)) {
			Ext6.Msg.alert('Сообщение', 'Окно редактирования уже открыто');
			return false;
		}
		var grid = win.MorbusNephroLabGrid;
		var params = {};

		params.action = action;
		params.callback = function(data) {

			if (!data || !data.BaseData) {
				return false;
			}
			//~ if ('MorbusNephroLab' == gridName && action.toString().inlist(['add', 'edit'])) {
				//~ var actionSetOnlyLast = viewFrame.getAction('action_setOnlyLast');
				//~ actionSetOnlyLast.setDisabled(true);
			//~ }
			data.BaseData.RecordStatus_Code = 0;
			if(action=='add') {
				grid.getStore().add({
					MorbusNephroLab_id: data.BaseData.MorbusNephroLab_id,
					RecordStatus_Code: data.BaseData.RecordStatus_Code,
					MorbusNephroLab_Date: data.BaseData.MorbusNephroLab_Date,
					MorbusNephro_id: data.BaseData.MorbusNephro_id,
					Rate_id: data.BaseData.Rate_id,
					RateType_id: data.BaseData.RateType_id,
					RateType_Name: data.BaseData.RateType_Name,
					Rate_ValueStr: data.BaseData.Rate_ValueStr
				});
			} else {
				// Обновить запись в grid
				var record = grid.getStore().getById(data.BaseData['MorbusNephroLab_id']);

				if (record) {
					if (record.get('RecordStatus_Code') == 1) {
						data.BaseData.RecordStatus_Code = 2;
					}

					record.set('MorbusNephroLab_id', data.BaseData.MorbusNephroLab_id);
					record.set('RecordStatus_Code', data.BaseData.RecordStatus_Code);
					record.set('MorbusNephroLab_Date', data.BaseData.MorbusNephroLab_Date);
					record.set('MorbusNephro_id', data.BaseData.MorbusNephro_id);
					record.set('Rate_id', data.BaseData.Rate_id);

					record.set('RateType_id', data.BaseData.RateType_id);
					record.set('RateType_Name', data.BaseData.RateType_Name);
					record.set('Rate_ValueStr', data.BaseData.Rate_ValueStr);

					record.commit();
				} else {
					if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('MorbusNephroLab_id')) {
						grid.getStore().removeAll();
					}

					data.BaseData['MorbusNephroLab_id'] = -Math.floor(Math.random() * 1000000);

					grid.getStore().loadData([ data.BaseData ], true);
				}
			}
			win.MorbusNephroLabPanel.checkContent();
		};
		params.formMode = 'local';
		params.formParams = new Object();

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getStore().getAt(grid.recordMenu.data_id);

			params.formParams = selected_record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd(windowname).show(params);
	},
	deleteNephroLab: function() {
		var win = this;
		var grid = win.MorbusNephroLabGrid;
		var id = grid.recordMenu.data_id;
		var record = grid.getStore().getAt(id);

		Ext6.Msg.show({
			buttons: Ext6.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					if (!grid || !record) {
						return false;
					}
					switch (Number(record.get('RecordStatus_Code'))) {
						case 0:
						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();
							grid.getStore().filterBy(function(rec) {
								if (Number(rec.get('RecordStatus_Code')) == 3) {
									return false;
								} else {
									return true;
								}
							});
							break;
					}
				}
				win.MorbusNephroLabPanel.checkContent();
			}.createDelegate(this),
			icon: Ext6.MessageBox.QUESTION,
			msg: 'Вы действительно хотите удалить эту запись?',
			title: 'Вопрос'
		});
	},
	//TAG: ЛЬГОТЫ методы
	openPersonPrivilegeEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		if (isVisibleWnd('swPrivilegeEditWindowExt6')) {
			Ext6.alert('Сообщение', 'Окно редактирования льготы уже открыто');
			return false;
		}
		var win = this;
		if (win.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		var base_form = win.FormPanel.getForm();
		var grid = win.PersonPrivilegeGrid;
		var indx = grid.getStore().findBy(function(rec) { if(rec.data.PersonPrivilege_id==grid.recordMenu.data_id) return rec;});

		var params = new Object();
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		params.Person_Birthday = win.PersonInfoPanel.getFieldValue('Person_Birthday');
		params.Person_deadDT = win.PersonInfoPanel.getFieldValue('Person_deadDT');
		if (action == 'add') {
			params.Person_id = base_form.findField('Person_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
			getWnd('swPrivilegeEditWindowExt6').show(params);
		} else {
			var selected_record = grid.getStore().getAt(indx).data;
			if (!selected_record || !selected_record.PersonPrivilege_id) {
				return false;
			}
			var lpu_id = selected_record.Lpu_id;
			var person_id = selected_record.Person_id;
			var person_privilege_id = selected_record.PersonPrivilege_id;
			var privilege_end_date = selected_record.Privilege_endDate;
			var privilege_type_code = selected_record.PrivilegeType_Code;
			var server_id = selected_record.Server_id;
			if (action == 'edit' && privilege_type_code <= 249 || lpu_id != Ext.globalOptions.globals.lpu_id) {
				params.action = 'view';
			}
			if (person_id && person_privilege_id && server_id >= 0) {
				params.Person_id = person_id;
				params.PersonPrivilege_id = person_privilege_id;
				params.Server_id = server_id;
				getWnd('swPrivilegeEditWindowExt6').show(params);
			}
		}
	},
	deletePersonPrivilege: function() {
		var grid = this.PersonPrivilegeGrid;
		if (!grid || !grid.recordMenu.data_id) {
			return false;
		}
		//~ var selected_record = grid.getSelectionModel().getSelected();
		var indx = grid.getStore().findBy(function(rec) { if(rec.data.PersonPrivilege_id==grid.recordMenu.data_id) return rec;});
		var selected_record = grid.getStore().getAt(indx).data;

		var lpu_id = selected_record.Lpu_id;
		var person_privilege_id = selected_record.PersonPrivilege_id;
		// Вообще проверка по ID - Узкое место, по Code - нельзя потому что в других регионах может быть
		// по другому.
		// В данном случае надо тянуть в грид ReceptFinance_id и по нему проверять
		if ((Number(selected_record.PrivilegeType_Code) <= 249 || lpu_id != Ext.globalOptions.globals.lpu_id) && !isSuperAdmin() /* суперадминистратор может удалить любую льготу */) {
			return false;
		}
		Ext6.Msg.show({
			buttons: Ext6.Msg.YESNO,
			icon: Ext6.Msg.QUESTION,
			fn: function(btn, text, obj) {
				if (btn == 'yes') {
					var loadMask = new Ext6.LoadMask(grid, { msg: "Подождите, идет удаление..." });
					loadMask.show();
					Ext6.Ajax.request({
						callback: function(options, success/*, response*/) {
							loadMask.hide();
							if (success) {
								grid.load();
							} else {
								Ext6.Msg.alert('Ошибка', 'При удалении льготы возникли ошибки');
							}
						},
						params: {
							PersonPrivilege_id: person_privilege_id
						},
						url: C_PERS_PRIV_DEL
					});
				}
			},
			icon: Ext6.MessageBox.QUESTION,
			msg: 'Удалить льготу?',
			title: 'Вопрос'
		});
	},
	//TAG: КОНТРОЛЬ ПОСЕЩЕНИЙ методы
	openPersonDispVizitEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		if (isVisibleWnd('swPersonDispVizitEditWindowExt6')) {
			Ext6.Msg.alert('Сообщение', 'Окно редактирования контроля посещений уже открыто');
			return false;
		}
		var win = this;
		if (win.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		var base_form = win.FormPanel.getForm();
		var grid = win.PersonDispVizitGrid;
		var params = {
			action: action,
			callback: function(data) {
				grid.getStore().load({
					params: {PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()}
				});
			}
		};
		if (action != 'add') {
			var indx = grid.getStore().findBy(function(rec) { if(rec.data.PersonDispVizit_id==grid.recordMenu.data_id) return rec;});
			var selected_record = grid.getStore().getAt(indx).data;

			if (!selected_record || !selected_record.PersonDispVizit_id) {
				return false;
			}
			params.PersonDispVizit_id = selected_record.PersonDispVizit_id;
			getWnd('swPersonDispVizitEditWindowExt6').show(params);
		} else {
			if (base_form.findField('PersonDisp_id').getValue() != '0') {
				params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
				getWnd('swPersonDispVizitEditWindowExt6').show(params);
			} else {
				win.doSave({
					doNotHide:true,
					callback: function(data){
						params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
						getWnd('swPersonDispVizitEditWindowExt6').show(params);
					}
				});
			}
		}
	},
	deletePersonDispVizit: function() {
		if (this.action == 'view') {
			return false;
		}
		var grid = this.PersonDispVizitGrid;
		var row = grid.getSelectionModel().getSelected();
		var indx = grid.getStore().findBy(function(rec) { if(rec.data.PersonDispVizit_id==grid.recordMenu.data_id) return rec;});
		if (!indx && indx != 0) {
			return false;
		}
		var selected_record = grid.getStore().getAt(indx).data;

		Ext6.Msg.show({
			buttons: Ext6.Msg.YESNO,
			icon: Ext6.Msg.QUESTION,
			fn: function (btn) {
				if ( btn === 'yes' ) {
					Ext6.Ajax.request({
						callback: function() {
							grid.load();
						},
						params: {
							PersonDispVizit_id: selected_record.PersonDispVizit_id
						},
						url: '/?c=PersonDisp&m=delPersonDispVizit'
					});
				}
			},
			msg: 'Вы действительно желаете удалить эту запись?',
			title: 'Подтверждение удаления'
		});
	},
	//TAG: ПРОФИЛАКТИЧЕСКИЕ ОСМОТРЫ методы
	openEvnPLDispProfEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}

		if (isVisibleWnd('swEvnPLDispProfEditWindow'))
		{
			Ex6t.Msg.alert(langs('Сообщение'), langs('Окно редактирования профосмотра уже открыто'));
			return false;
		}

		var win = this;
		var grid = win.EvnPLDispProfGrid;

		var base_form = win.FormPanel.getForm();

		var Person_id = base_form.findField('Person_id').getValue();
		var Server_id = base_form.findField('Server_id').getValue();

		if (action != 'add') {
			var indx = grid.getStore().findBy(function(rec) { if(rec.data.EvnPLDispProf_id==grid.recordMenu.data_id) return rec;});
			var selected_record = grid.getStore().getAt(indx).data;

			if (!selected_record || !selected_record.EvnPLDispProf_id) {
				return false;
			}

			var params = {
				action: action,
				DispClass_id: 5,
				EvnPLDispProf_id: selected_record.EvnPLDispProf_id,
				onHide: Ext.emptyFn,
				callback: function() {
					grid.getStore().reload();
				},
				Person_id: Person_id,
				Server_id: Server_id
			};
			getWnd('swEvnPLDispProfEditWindow').show(params);
		} else {
			win.getLoadMask('Проверка наличия у пациента случая ДВН или ПОВН в текущем году').show();
			Ext6.Ajax.request({
				url: '/?c=EvnPLDispProf&m=checkBeforeAddEvnPLDisp',
				params: {
					Person_id: Person_id
				},
				callback: function(options, success, response) {
					win.getLoadMask().hide();

					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.Error_Msg) {
							return false;
						}

						getWnd('swEvnPLDispProfEditWindow').show({
							action: 'add',
							DispClass_id: 5,
							EvnPLDispProf_id: null,
							onHide: Ext.emptyFn,
							callback: function() {
								grid.getStore().reload();
							},
							Person_id: Person_id,
							Server_id: Server_id
						});
					}
				}
			});
		}
	},
	doDeleteEvnPLDD: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var grid = win.EvnPLDispProfGrid;

		var indx = grid.getStore().findBy(function(rec) { if(rec.data.EvnPLDispProf_id==grid.recordMenu.data_id) return rec;});
		var selected_record = grid.getStore().getAt(indx).data;

		if ( !grid || !selected_record || !selected_record.EvnPLDispProf_id) {
			return false;
		}

		var params = {
			EvnPLDispProf_id: selected_record.EvnPLDispProf_id
		};

		if (options.ignoreCheckRegistry) {
			params.ignoreCheckRegistry = 1;
		}

		win.getLoadMask('Удаление случая ПОВН').show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						Ext6.Msg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при удалении случая ПОВН'));
					}
					else if (response_obj.Alert_Msg) {
						Ext6.Msg.show({
							icon: Ext6.MessageBox.QUESTION,
							msg: response_obj.Alert_Msg + ' Продолжить?',
							title: langs('Подтверждение'),
							buttons: Ext6.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ('yes' == buttonId) {
									options.ignoreCheckRegistry = true;
									win.doDeleteEvnPLDD(options);
								}
							}
						});
					}
					else {
						grid.getStore().remove(selected_record);

						if ( grid.getStore().getCount() == 0 ) {
							LoadEmptyRow(grid);
						}
					}

					grid.getStore().reload();
				}
				else {
					Ext6.Msg.alert(langs('Ошибка'), langs('При удалении случая ПОВН возникли ошибки'));
				}
			},
			params: params,
			url: '/?c=EvnPLDispProf&m=deleteEvnPLDispProf'
		});
	},
	deleteEvnPLDispProf: function() {
		var win = this;
		Ext6.Msg.show({
			buttons: Ext6.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.doDeleteEvnPLDD();
				}
			},
			icon: Ext6.MessageBox.QUESTION,
			msg: langs('Удалить случай ПОВН?'),
			title: langs('Вопрос')
		});
	},
	enableButtonsForMedStaff: function (index) {
		var win = this;
		if(getGlobalOptions().CurMedPersonal_id != win.PersonDispHistGrid.getStore().getAt(index).get('MedPersonal_id')) {
			win.EvnPLDispProfGrid.recordMenu.queryById('EvnPLDispProf_EditOption').disable();
			win.EvnPLDispProfGrid.recordMenu.queryById('EvnPLDispProf_DeleteOption').disable();
			win.EvnPLDispProfPanel.queryById('plus').hide()
		} else {
			win.EvnPLDispProfGrid.recordMenu.queryById('EvnPLDispProf_EditOption').enable();
			win.EvnPLDispProfGrid.recordMenu.queryById('EvnPLDispProf_DeleteOption').enable();
			win.EvnPLDispProfPanel.queryById('plus').show()
		}
	},

	doSave: function(options) {
		// options @Object
		// options.print @Boolean Вызывать печать карты дисп. учета, если true
		var win = this;
		if (win.formStatus == 'save' || win.action == 'view') {
			return false;
		}
		win.formStatus = 'save';
		var base_form = win.FormPanel.getForm();
		var form = win.FormPanel;
		

		if (base_form.findField('PersonDisp_begDate').getValue() != '' && new Date() < base_form.findField('PersonDisp_begDate').getValue()) {
			Ext6.Msg.alert('Ошибка', 'Дата приема на учет должна быть не позже текущей даты.', function() {
				win.formStatus = 'edit';
				base_form.findField('PersonDisp_begDate').focus(true);
			}.createDelegate(win));
			return false;
		}

		if (!Ext6.isEmpty(base_form.findField('PersonDisp_DiagDate').getRawValue()) && new Date() < base_form.findField('PersonDisp_DiagDate').getValue()) {
			Ext6.Msg.alert('Ошибка', 'Дата установления диагноза должна быть не позже текущей даты.', function() {
				win.formStatus = 'edit';
				base_form.findField('PersonDisp_DiagDate').focus(true);
			}.createDelegate(win));
			return false;
		}
		if (!Ext6.isEmpty(base_form.findField('PersonDisp_endDate').getRawValue()) && new Date() < base_form.findField('PersonDisp_endDate').getValue()) {
			Ext6.Msg.alert('Ошибка', 'Дата снятия должна быть не позже текущей даты.', function() {
				win.formStatus = 'edit';
				base_form.findField('PersonDisp_endDate').focus(true);
			}.createDelegate(win));
			return false;
		}
		if(!Ext6.isEmpty(base_form.findField('PersonDisp_endDate').getRawValue()) && base_form.findField('PersonDisp_endDate').getValue() < base_form.findField('PersonDisp_begDate').getValue()) {
			Ext6.Msg.alert('Ошибка', 'Дата снятия должна быть не раньше даты приема.', function() {
				win.formStatus = 'edit';
				base_form.findField('PersonDisp_endDate').focus(true);
			}.createDelegate(win));
			return false;
		}
		if (!Ext6.isEmpty(base_form.findField('PersonDisp_endDate').getRawValue()) && Ext6.isEmpty(base_form.findField('DispOutType_id').getValue())) {
			Ext6.Msg.alert('Ошибка', 'При снятии пациента с учета должна быть указана причина снятия.', function() {
				win.formStatus = 'edit';
				base_form.findField('DispOutType_id').focus(true);
			}.createDelegate(win));
			return false;
		}

		if (!Ext6.isEmpty(base_form.findField('PersonDisp_endDate').getRawValue())) {
			var endDate = base_form.findField('PersonDisp_endDate').getValue();
			var histBegDate = false;
			var histEndDate = false;
			win.PersonDispHistGrid.getStore().each(function(rec){
				if(rec.get('PersonDispHist_id') > 0 && (!histBegDate || histBegDate < rec.get('PersonDispHist_begDate'))){
					histBegDate = rec.get('PersonDispHist_begDate');
					histEndDate = rec.get('PersonDispHist_endDate');
				}
			});
			if((!Ext6.isEmpty(histEndDate) && histEndDate !== false) && Ext6.util.Format.date(histEndDate, 'd.m.Y') != Ext6.util.Format.date(endDate, 'd.m.Y')){
				Ext6.Msg.alert('Ошибка', 'Дата снятия с диспансерного наблюдения должна соответствовать дате окончания периода ответственности последнего ответственного врача.', function() {
					win.formStatus = 'edit';
					base_form.findField('PersonDisp_endDate').setValue('');
					base_form.findField('PersonDisp_endDate').fireEvent('change',base_form.findField('PersonDisp_endDate'),null);
					base_form.findField('PersonDisp_endDate').focus(true);
				}.createDelegate(win));
				return false;
			}
		}

		if (!base_form.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(win),
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!Ext6.isEmpty(base_form.findField('PregnancySpec_Count').getValue()) && !Ext6.isEmpty(base_form.findField('PregnancySpec_CountBirth').getValue()) && base_form.findField('PregnancySpec_Count').getValue() < base_form.findField('PregnancySpec_CountBirth').getValue()) {
			Ext6.Msg.alert('Ошибка', 'Количество родов не может быть больше количества беременностей', function() {
				win.formStatus = 'edit';
				base_form.findField('PregnancySpec_CountBirth').focus(true);
			}.createDelegate(win));
			return false;
		}

		if (!Ext6.isEmpty(base_form.findField('PregnancySpec_Count').getValue()) && !Ext6.isEmpty(base_form.findField('PregnancySpec_CountAbort').getValue()) && base_form.findField('PregnancySpec_Count').getValue() < base_form.findField('PregnancySpec_CountAbort').getValue()) {
			Ext6.Msg.alert('Ошибка', 'Количество абортов не может быть больше количества беременностей', function() {
				win.formStatus = 'edit';
				base_form.findField('PregnancySpec_CountAbort').focus(true);
			}.createDelegate(win));
			return false;
		}

		if (!Ext6.isEmpty(base_form.findField('PersonDisp_endDate').getRawValue()) && base_form.findField('PersonDisp_endDate').getValue() < base_form.findField('PersonDisp_begDate').getValue()) {
			Ext6.Msg.alert('Ошибка', 'Дата приема на учет должна быть не позже даты снятия с учета.', function() {
				win.formStatus = 'edit';
				base_form.findField('PersonDisp_endDate').focus(true);
			}.createDelegate(win));
			return false;
		}
		var med_personal_code;
		var med_personal_fio = '';
		var med_personal_id;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var record;
		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if (record) {
			med_personal_code = record.get('MedPersonal_TabCode');
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
			base_form.findField('MedPersonal_id').setValue(med_personal_id);
		}
		var idx;
		var medicaments_array = new Array();
		var medicaments_grid = win.PersonDispMedicamentGrid;
		var params = new Object();
		var privilege_type_id;
		var sickness_id = base_form.findField('Sickness_id').getValue();
		medicaments_grid.getStore().each(function(record) {
			if (!record.get('Drug_id')) {
				medicaments_grid.getStore().remove(record);
			}
		});
		if (win.action == 'add' && medicaments_grid.getStore().getCount() > 0 && medicaments_grid.getStore().getAt(0).get('Drug_id') > 0) {
			medicaments_array = sw4.getStoreRecords(medicaments_grid.getStore(), { convertDateFields: true, exceptionFields: [ 'PersonDispMedicament_id', 'PersonDisp_id', 'Drug_Name', 'Drug_Price' ]});
		}
		idx = base_form.findField('Sickness_id').getStore().findBy(function(rec) {
			return rec.get('Sickness_id') == sickness_id;
		});
		if (idx >= 0) {
			privilege_type_id = base_form.findField('Sickness_id').getStore().getAt(idx).get('PrivilegeType_id');
		}
		params.action = win.action;
		params.PrivilegeType_id = privilege_type_id;
		params.Sickness_id = sickness_id;
		if (params.Sickness_id == 100500){
			params.Sickness_id = null;
		}
		if (base_form.findField('PersonDisp_NumCard').disabled) {
			params.PersonDisp_NumCard = base_form.findField('PersonDisp_NumCard').getValue();
		}
		if (base_form.findField('LpuSection_id').disabled) {
			params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}
		if (base_form.findField('Diag_id').disabled) {
			params.Diag_id = base_form.findField('Diag_id').getValue();
		}
		if (options && options.ignoreExistsPersonDisp) {
			params.ignoreExistsPersonDisp = 1;
		}
		base_form.findField('MorbusNephro_IsHyperten').setValue(base_form.findField('MorbusNephro_IsHyperten').getValue() ? '2':'1');
		var loadMask = new Ext6.LoadMask(win, { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		base_form.submit({
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Msg) {
						Ext6.Msg.alert('Ошибка', action.result.Error_Msg, function() {
							base_form.findField('PersonDisp_NumCard').focus(true);
						});
					} else {
						Ext6.Msg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]', function() {
							base_form.findField('PersonDisp_NumCard').focus(true);
						});
					}
				}
			}.createDelegate(win),
			params: params,
			success: function(result_form, action) {
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result) {
					if (action.result.existsPersonDisp) {
						var diag_full_name = action.result.existsPersonDisp.Diag_FullName;
						Ext6.Msg.show({
							buttons: Ext6.Msg.YESNO,
							buttonText: {
								yes: 'Создать',
								no: 'Отмена'
							},
							fn:function (buttonId, text, obj) {
								if (buttonId == 'yes') {
									options = options || {};
									options.ignoreExistsPersonDisp = true;
									win.doSave(options);
								}
							}.createDelegate(win),
							icon:Ext6.MessageBox.QUESTION,
							msg:'Выбранный пациент уже состоит на диспансерном учете с диагнозом "'+diag_full_name+'"',
							title:'Подтверждение'
						});
					} else if (action.result.PersonDisp_id) {
						var data = new Object(),
							diag_code,
							lpu_section_code,
							person_disp_id = action.result.PersonDisp_id;
						base_form.findField('PersonDisp_id').setValue(person_disp_id);
						record = base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue());
						if (record) {
							diag_code = record.get('Diag_Code');
						}
						record = base_form.findField('LpuSection_id').getStore().getById(base_form.findField('LpuSection_id').getValue());
						if (record) {
							lpu_section_code = record.get('LpuSection_Code');
						}
						data.PersonDispData = {
							'accessType': 'edit',
							'PersonDisp_id': person_disp_id,
							'Person_id': base_form.findField('Person_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue(),
							'Diag_Code': diag_code,
							'LpuSection_Code': lpu_section_code,
							'MedPersonal_Code': med_personal_code,
							'MedPersonal_Fio': med_personal_fio,
							'Person_Surname': win.PersonInfoPanel.getFieldValue('Person_Surname'),
							'Person_Firname': win.PersonInfoPanel.getFieldValue('Person_Firname'),
							'Person_Secname': win.PersonInfoPanel.getFieldValue('Person_Secname'),
							'Person_Birthday': win.PersonInfoPanel.getFieldValue('Person_Birthday'),
							'PersonDisp_begDate': base_form.findField('PersonDisp_begDate').getValue(),
							'PersonDisp_endDate': base_form.findField('PersonDisp_endDate').getValue()
						};
						
						win.callback(data);
						//сохраняю специфику по беременности
						//log('this.specifics.panel.visible', this.specifics.panel.hidden);
						var afterSave = function (){
							 if (options && options.printPersonDispCard){
								var paramPersonDisp = person_disp_id;
								printBirt({
									'Report_FileName': 'PersonDispCard.rptdesign',
									'Report_Params': '&paramPersonDisp=' + paramPersonDisp,
									'Report_Format': 'pdf'
								});
							}
							if (options && options.print030Card){
								var paramPersonDisp = person_disp_id;
								win.print030(paramPersonDisp);
							}
							if (options && options.printParturientCard) {
								//window.open('/?c=PersonDisp&m=printPersonDisp&PersonDisp_id=' + person_disp_id, '_blank');
								var paramPersonDispBirth = person_disp_id;
								printBirt({
									'Report_FileName': 'han_ParturientCard.rptdesign',
									'Report_Params': '&paramPersonDispBirth=' + paramPersonDispBirth,
									'Report_Format': 'pdf'
								});
							} else {
								if ((options && !options.doNotHide) || !options){
									win.hide();
								}
								if (options && options.callback){
									options.callback();
								}
							}
						}.createDelegate(win);
						var lastHist = 0;
						if (
							!Ext6.isEmpty(base_form.findField('PersonDisp_endDate').getRawValue())
							|| !Ext6.isEmpty(win.PersonDispCloseDate) // Дата закрытия карты сейчас пуста, но не была пуста при открытии формы - значит восстановление наблюдения
						) {
							var endDate = base_form.findField('PersonDisp_endDate').getValue();
							var histBegDate = false;
							var histEndDate = false;
							var histId = 0;
							win.PersonDispHistGrid.getStore().each(function(rec){
								if(!histBegDate || histBegDate < rec.get('PersonDispHist_begDate')){
									histBegDate = rec.get('PersonDispHist_begDate');
									histEndDate = rec.get('PersonDispHist_endDate');
									histId = rec.get('PersonDispHist_id');
								}
							});
							if(histEndDate != endDate){
								lastHist = histId;
							}
						}
						if (
							(
								win.PersonDispHistGrid.getStore().data.length == 1
								&& win.PersonDispHistGrid.getStore().getAt(0).get('PersonDispHist_id') == 0
								&& !win.PersonDispHistSaved
							)
							||
							(lastHist>0)
						) {
							if(!(lastHist>0)){
								var paramsHist = {
									PersonDispHist_id:null,
									PersonDisp_id:person_disp_id,
									MedPersonal_id:med_personal_id,
									LpuSection_id:base_form.findField('LpuSection_id').getValue(),
									PersonDispHist_begDate:Ext6.util.Format.date(base_form.findField('PersonDisp_begDate').getValue(),'Y-m-d'),
									PersonDispHist_endDate:Ext6.util.Format.date(base_form.findField('PersonDisp_endDate').getValue(),'Y-m-d')
								};
							} else {
								var recordHist = win.PersonDispHistGrid.getStore().getById(lastHist);
								var paramsHist = {
									PersonDispHist_id:recordHist.get('PersonDispHist_id'),
									PersonDisp_id:person_disp_id,
									MedPersonal_id:recordHist.get('MedPersonal_id'),
									LpuSection_id:recordHist.get('LpuSection_id'),
									PersonDispHist_begDate:Ext6.util.Format.date(recordHist.get('PersonDispHist_begDate'),'Y-m-d'),
									PersonDispHist_endDate:Ext6.util.Format.date(base_form.findField('PersonDisp_endDate').getValue(),'Y-m-d')
								};
							}

							Ext6.Ajax.request({
								url: '/?c=PersonDisp&m=savePersonDispHist',
								params: paramsHist,
								callback: function(options, success, response) {
									if ( success ) {
										var result = Ext6.util.JSON.decode(response.responseText);
										win.PersonDispHistSaved = true;
									}
									if (!win.PregnancyPanel.hidden) {
										if (win.FormPanel.getForm().findField('PregnancySpec_id')) {
											win.PregnancyPanel.save(afterSave);
										} else {
											afterSave();
										}
									} else if (!win.MorbusNephroPanel.hidden) {
										win.MorbusNephroPanel.save(afterSave);
									} else {
										afterSave();
									}
								}.createDelegate(win)
							});
						} else {
							if (!win.PregnancyPanel.hidden) {
								if (win.FormPanel.getForm().findField('PregnancySpec_id')) {
									win.PregnancyPanel.save(afterSave);
								} else {
									afterSave();
								}
							} else if (!win.MorbusNephroPanel.hidden) {
								win.MorbusNephroPanel.save(afterSave);
							} else {
								afterSave();
							}
						}
					} else {
						if (action.result.Error_Msg) {
							Ext6.Msg.alert('Ошибка', action.result.Error_Msg);
						} else {
							Ext6.Msg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
						}
					}
				} else {
					Ext6.Msg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}.createDelegate(win)
		});
	},
	enableEdit: function(enable) {
		var win = this;
		if (win.MorbusNephroLabPanel) {
			win.MorbusNephroLabPanel.setReadOnly(!enable);
		}
		if (win.PregnancySpecComplicationPanel) {
			win.PregnancySpecComplicationPanel.setReadOnly(!enable);
		}
		if (win.PregnancySpecExtragenitalDiseasePanel) {
			win.PregnancySpecExtragenitalDiseasePanel.setReadOnly(!enable);
		}
		if (win.ChildPanel) {
			win.ChildPanel.setReadOnly(!enable);
		}
		if (win.DeadChildPanel) {
			win.DeadChildPanel.setReadOnly(!enable);
		}
		if (win.PersonPrivilegePanel) {
			win.PersonPrivilegePanel.setReadOnly(!enable);
		}

		var base_form = win.FormPanel.getForm();
		var form_fields = new Array('Diag_id', 'LpuSection_id', 'MedStaffFact_id', 'PersonDisp_NumCard','PersonDisp_begDate', 'PersonDisp_endDate', 'DispOutType_id');
		var i = 0;
		for (i = 0; i < form_fields.length; i++) {
			if (enable) {
				base_form.findField(form_fields[i]).enable();
			} else {
				base_form.findField(form_fields[i]).disable();
			}
		}
		if(this.action != "add") {
			base_form.findField('PersonDisp_NumCard').disable();
		}
	},
	show: function() {//TAG: show формы
		var win = this;
		win.callParent(arguments);
		
		if(!win.tip)
			win.tip = Ext6.create('Ext6.tip.ToolTip', {
				userCls: 'disptip',
				target: win.titlePanel.items.items[1].el,
				trackMouse: false,
				anchorToTarget: true,
				targetOffset: [-70, 0],
				renderTo: main_center_panel.body.dom,
				constrainPosition :false,
				listeners: {
					beforeshow: function updateTipBody(tip) {
						tip.update('Печать');
					}
				}
			});

		win.queryBy(function(r){
			if(r.plusButton) {
				var x = r.queryById('plus');
				if(!x.tip)
					x.tip = Ext6.create('Ext6.tip.ToolTip', {
						userCls: 'disptip',
						target: x.el,
						trackMouse: false,
						//~ anchorToTarget: true,
						//~ anchor: 'right',
						targetOffset: [-90, 0],
						renderTo: main_center_panel.body.dom,
						constrainPosition :false,
						listeners: {
							beforeshow: function updateTipBody(tip) {
								tip.update('Добавить');
							}
						}
					});
			}
		});

		win.queryById('PDEF_PersonDispHistPanel').setUserCls('DispHistNull')

		win.PersonDispPanel.expand();
		win.PersonPrivilegePanel.collapse();

		win.PersonDispTargetRatePanel.collapse();
		win.EvnPLDispProfPanel.collapse();
		win.PersonDispVizitPanel.collapse();
		win.DiagPanel.collapse();
		win.PersonDispHistGrid.getStore().removeAll();
		win.PersonDispHistPanel.collapse();

		win.PregnancyPanel.collapse();
		win.PregnancyHistoryPanel.collapse();
		win.PregnancyLabPanel.collapse();
		win.PregnancySpecComplicationPanel.collapse();
		win.PregnancySpecExtragenitalDiseasePanel.collapse();
		win.ChildPanel.collapse();
		win.DeadChildPanel.collapse();

		win.SicknessPanel.collapse();

		win.PersonPrivilegePanel.isLoaded = false;
		win.PersonDispTargetRatePanel.isLoaded = false;
		win.EvnPLDispProfPanel.isLoaded = false;
		win.PersonDispVizitPanel.isLoaded = false;
		win.DiagPanel.isLoaded = false;
		win.PersonDispHistPanel.isLoaded = false;
		var base_form = win.FormPanel.getForm();

		base_form.reset();

		//base_form.findField('Diag_id').filterDate = null;
		win.DiagCombo.filterDate = null;
		win.action = null;
		win.callback = Ext6.emptyFn;
		win.Diag_id = null;
		win.DiagFilter_id = null;
		win.DiagLevelFilter_id = null;
		win.formStatus = 'edit';
		win.isDopDisp = false;
	//	win.onHide = Ext6.emptyFn;
		
		win.Sickness_id = null;
		win.UserLpuSection_id = null;
		win.UserLpuSectionList = new Array();
		win.UserMedStaffFact_id = null;
		win.MedPersonal_id = null;
		win.UserMedStaffFactList = new Array();
		win.PersonDispHistSaved = false;
		win.PersonDispCloseDate = null;

		if (!arguments[0] || !arguments[0].formParams) {
			Ext6.Msg.alert('Сообщение', 'Неверные параметры', function() {
				win.hide();
			}.createDelegate(win));
			return false;
		}

		if(arguments[0].userMedStaffFact) win.userMedStaffFact = arguments[0].userMedStaffFact;
		if(arguments[0].PersonEvn_id) win.PersonEvn_id = arguments[0].PersonEvn_id;

		// определенный медстафффакт
		if (arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0) {
			win.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}
		else if (arguments[0].formParams && arguments[0].formParams.MedPersonal_id && arguments[0].formParams.MedPersonal_id > 0) {
			win.MedPersonal_id = arguments[0].formParams.MedPersonal_id;
		}
		// если в настройках есть medstafffact, то имеем список мест работы
		else if (Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0) {
			win.UserMedStaffFactList = Ext.globalOptions.globals['medstafffact'];
		}
		// определенный LpuSection
		if (arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0) {
			win.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}
		// если в настройках есть lpusection, то имеем список мест работы
		else if (Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0) {
			win.UserLpuSectionList = Ext.globalOptions.globals['lpusection'];
		}
		base_form.setValues(arguments[0].formParams);

		win.EvnPLDispProfPanel.hide();

		win.PersonInfoPanel.load({
			Person_id: base_form.findField('Person_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue(),
		//	userMedStaffFact: win.userMedStaffFact,
			PersonEvn_id: win.PersonEvn_id,
			noToolbar: true,
			callback: function () {
				var piPanel = win.PersonInfoPanel;
				if (piPanel.getFieldValue('Person_Surname')) {
					winTitle = piPanel.getFieldValue('Person_Surname');

					if (piPanel.getFieldValue('Person_Firname')) {
						winTitle = winTitle + ' ' + piPanel.getFieldValue('Person_Firname').substring(0, 1) + '.';
					}

					if (piPanel.getFieldValue('Person_Secname')) {
						winTitle = winTitle + ' ' + piPanel.getFieldValue('Person_Secname').substring(0, 1) + '.';
					}
				}
				//Не отображаем "Профилактические осмотры", в случаях если человеку 18 и больше лет или регион Казахстан
				if(win.PersonInfoPanel.getFieldValue('Person_Age') >= 18 && getRegionNick() != 'kz') {
					win.EvnPLDispProfPanel.show();
				}
				win.PersonInfoPanel.PToolbar.hide();
			}
		});

		if (arguments[0].action) {
			win.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			win.callback = arguments[0].callback;
		}
		if (arguments[0].Diag_id) {
			win.Diag_id = arguments[0].Diag_id;
		}

		// Диагноз или группа диагнозов, которыми ограничивать список выбора диагноза
		if (arguments[0].DiagFilter_id && arguments[0].DiagLevelFilter_id) {
			win.DiagFilter_id = arguments[0].DiagFilter_id;
			win.DiagLevelFilter_id = arguments[0].DiagLevelFilter_id;
		}
		if (arguments[0].isDopDisp) {
			win.isDopDisp = arguments[0].isDopDisp;
		}

		// Тип заболевания
		if (arguments[0].Sickness_id && arguments[0].Sickness_id > 0) {
			win.Sickness_id = arguments[0].Sickness_id;
		}

		//~ win.PersonDispMedicamentGrid.getStore().removeAll(true);

		//TAG: в show загрузка данных
		win.PersonDispHistPanel.checkContent();//загружать на кнопке
		win.PersonDispSopDiagGrid.load();
		win.PersonPrivilegeGrid.load();
		win.PersonDispVizitGrid.load();
		win.EvnPLDispProfGrid.load();

		var diag_combo = base_form.findField('Diag_id');
		var diag_id;
		var idx;
		diag_combo.additQueryFilter = '';
		diag_combo.allQueryFilter = '';
		diag_combo.DiagFilter_id = null;
		diag_combo.DiagLevelFilter_id = null;

		win.MorbusNephroPanel.onShowWindow();

		win.showNephroPanels(false);
		win.showPregnancyPanels(false);
		win.showSicknessPanels(false);

		var loadMask = new Ext6.LoadMask(win, { msg: LOAD_WAIT });
		loadMask.show();

		win.PersonDispTargetRatePanel.setReadOnly(!win.action.inlist([ 'add', 'edit' ]));

		switch (win.action) {
			case 'add':
				win.setTitle(langs("Контрольная карта диспансерного наблюдения"));
				win.enableEdit(true);
				base_form.findField('PersonDisp_begDate').setValue(new Date());

				base_form.findField('PersonDisp_begDate').setValue("");

				base_form.findField('PersonDisp_begDate').fireEvent('change', base_form.findField('PersonDisp_begDate'), base_form.findField('PersonDisp_begDate').getValue());

				base_form.findField('PersonDisp_endDate').fireEvent('change', base_form.findField('PersonDisp_endDate'), base_form.findField('PersonDisp_endDate').getValue());

				base_form.findField('PersonDisp_NumCard').enable();

				win.PersonDisp_NumCardAddBtn.show();

				// win.loadListsAndFormData();

				// Дополнительные фильтры для диагноза
				if (win.DiagFilter_id > 0 && win.DiagLevelFilter_id > 0) {
					diag_combo.DiagFilter_id = win.DiagFilter_id;
					diag_combo.DiagLevelFilter_id = win.DiagLevelFilter_id;
				}

				// Типы заболеваний
				if (win.Sickness_id && win.Sickness_id > 0) {
					base_form.findField('Sickness_id').setValue(win.Sickness_id);
					if (base_form.findField('Sickness_id').getValue()) {
						var sickness_id = win.Sickness_id;
						var ids = [ 0 ];
						var sickness_diag_store = win.sicknessDiagStore;
						sickness_diag_store.load({
							params: sickness_diag_store.baseParams,
							callback: function() {
								idx = sickness_diag_store.findBy(function(record) {
									if (record.get('Sickness_id') == sickness_id || sickness_id == 100500) {
										ids.push(record.get('Diag_id'));
									}
								});
								if (sickness_id == 100500) {
									diag_combo.additQueryFilter = "Diag_id not in (" + ids.join(', ') + ")";
									diag_combo.additClauseFilter = '!(record["Diag_id"].inlist([' + ids.join(', ') + ']))';
								} else {
									diag_combo.additQueryFilter = "Diag_id in (" + ids.join(', ') + ")";
									diag_combo.additClauseFilter = '(record["Diag_id"].inlist([' + ids.join(', ') +']))';
								}
							}.createDelegate(win)
						})

					}
				} else {
					win.sicknessDiagStore.load(); //{params:win.sicknessDiagStore.baseParams}
				}
				// если переданы диагнозы, то загружаем диагноз, так же с прочими полями (это в случае новой карты, создаваемой из старой)
				if (win.Diag_id) {
					diag_id = win.Diag_id;
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.setValue(diag_id);
							diag_combo.getStore().each(function(record) {
								if (record.get('Diag_id') == diag_id) {
									diag_combo.fireEvent('select', diag_combo, record, 0);
									diag_combo.fireEvent('change', diag_combo, diag_id, 0);
								}
							});
						}.createDelegate(win),
						params: {
							where: "where Diag_id = " + diag_id
						}
					});
				}
				win.queryById('Diag_id_TFOMS_msg').hide();
				var yesno_id = 1;
				if (win.isDopDisp) {
					yesno_id = 2;
				}
				loadMask.hide();
				base_form.clearInvalid();
				base_form.findField('PersonDisp_NumCard').focus(true, 250);
				win.PregnancyPanel.init();
				break;
			case 'edit':
			case 'view':
				var person_disp_id = base_form.findField('PersonDisp_id').getValue();

				base_form.findField('PersonDisp_NumCard').disable();

				win.PersonDisp_NumCardAddBtn.hide();

				if (!person_disp_id) {
					loadMask.hide();
					win.hide();
					return false;
				}
				base_form.load({
					failure: function() {
						loadMask.hide();
						Ext6.Msg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {
							win.hide();
						}.createDelegate(win));
					}.createDelegate(win),
					params: {
						'PersonDisp_id': person_disp_id
					},
					success: function(form, action) {
						var data = Ext6.JSON.decode(action.response.responseText);
						if (data[0]) {
							base_form.findField('LpuSection_id').setValue(parseInt(data[0].LpuSection_id));
							base_form.findField('Diag_id').setValue(parseInt(data[0].Diag_id));
							//~ win.DiagCombo.setValue(parseInt(data[0].Diag_id));
							//~ win.DiagCombo.setValue(win.DiagCombo.getValue());
						}
						if(base_form.findField('Label_id').getValue()=='1') {
							var ids = [5378,5379,5380,5381,5382,5383,5384,5385,5386,5387,5388,5389,5390,11742,11742];
							diag_combo.additQueryFilter = "Diag_id in (" + ids.join(', ') + ")";
							diag_combo.additClauseFilter = '(record["Diag_id"].inlist([' + ids.join(', ') +']))';
						}
						if (base_form.findField('accessType').getValue() == 'view'
							&& !( haveArmType('mstat')
								|| isSuperAdmin()
								|| (getGlobalOptions().medpersonal_id == null && base_form.findField('Lpu_id').getValue() > 0 && getGlobalOptions().lpu_id == base_form.findField('Lpu_id').getValue())
								)
							) {
							win.action = 'view';
						}
						if (win.action == 'edit') {
							//win.setTitle(langs("Контрольная карта диспансерного наблюдения: редактирование"));
							win.enableEdit(true);
						} else {
							//win.setTitle(langs("Контрольная карта диспансерного наблюдения: просмотр"));
							win.enableEdit(false);
							if(win.queryById('PDEF_EvnUslugaPregnancySpecGrid'))
								win.queryById('PDEF_EvnUslugaPregnancySpecGrid').getTopToolbar().items.items[0].disable();
						}
						if (win.action == 'edit') {
							setCurrentDateTime({
								dateField: base_form.findField('PersonDisp_begDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: win.id
							});
						}
						var diag_id = diag_combo.getValue();
						var index;
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var med_personal_id = base_form.findField('MedPersonal_id').getValue();
						var record;
						base_form.findField('PersonDisp_endDate').fireEvent('change', base_form.findField('PersonDisp_endDate'), base_form.findField('PersonDisp_endDate').getValue());
						win.PersonDispCloseDate = base_form.findField('PersonDisp_endDate').getValue();
						if (win.action == 'edit') {
							base_form.findField('DispOutType_id').fireEvent('change', base_form.findField('DispOutType_id'), base_form.findField('DispOutType_id').getValue());
							base_form.findField('PersonDisp_begDate').fireEvent('change', base_form.findField('PersonDisp_begDate'), base_form.findField('PersonDisp_begDate').getValue());
							base_form.findField('Diag_id').filterDate = Ext6.util.Format.date(base_form.findField('PersonDisp_begDate').getValue(), 'd.m.Y');
							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
								if (record.get('LpuSection_id') == lpu_section_id && record.get('MedPersonal_id') == med_personal_id) {
									return true;
								} else {
									return false;
								}
							});
							if (index >= 0) {
								base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						} else {
							base_form.findField('LpuSection_id').getStore().load({
								callback: function() {
									index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
										if (rec.get('LpuSection_id') == lpu_section_id) {
											return true;
										} else {
											return false;
										}
									});
									if (index >= 0) {
										base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
										base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
									}
								}.createDelegate(win),
								params: {
									LpuSection_id: lpu_section_id,
									mode: 'combo'
								}
							});
							base_form.findField('MedStaffFact_id').getStore().load({
								callback: function() {
									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										if (rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id) {
											return true;
										} else {
											return false;
										}
									});
									if (index >= 0) {
										base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
									}
								}.createDelegate(win),
								params: {
									LpuSection_id: lpu_section_id,
									MedPersonal_id: med_personal_id
								}
							});
						}
						if (diag_id) {
							diag_combo.getStore().load({
								callback: function(records, operation, success) {
									diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
									base_form.findField('Diag_id').setValue(diag_id); //иначе комбик не заполняется (хотя численное значение имеет)
								}.createDelegate(win),
								params: {
									where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
								}
							});
						}
						base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), diag_id);

						if (!win.queryById('PDEF_SicknessPanel').hidden) {
							win.PersonDispMedicamentGrid.load();
						}
						var tfoms = base_form.findField('PersonDisp_IsTFOMS').getValue();
						if (tfoms == 2 && win.action == 'edit') {
							base_form.findField('Diag_id').disable();
							win.queryById('Diag_id_TFOMS_msg').show();
						} else {
							win.queryById('Diag_id_TFOMS_msg').hide();
							if(win.action == 'edit')
								base_form.findField('Diag_id').enable();
						}
						loadMask.hide();
						base_form.clearInvalid();
						if (win.action == 'edit') {
							base_form.findField('PersonDisp_NumCard').focus(true, 250);
						} else {
							win.buttons[win.buttons.length - 1].focus();
						}

						//~ if (!win.PregnancyPanel.hidden){
							win.PregnancyPanel.init();
							win.PregnancyPanel.doShow(true);
						//~ }
						win.PersonDispHistGrid.load();
						win.enablePrint030();
						//PROMEDWEB-10455
						if (!base_form.findField('PersonDisp_NumCard').getValue()) {
							base_form.findField('PersonDisp_NumCard').enable()
						}
					}.createDelegate(win),
					url: '/?c=PersonDisp&m=loadPersonDispEditForm'
				});
				break;
			default:
				loadMask.hide();
				win.hide();
				break;
		}
		var today = new Date();
		base_form.findField('PersonDisp_begDate').maxValue = today;
		base_form.findField('PersonDisp_DiagDate').maxValue = today;
		base_form.findField('PersonDisp_endDate').maxValue = today;
		if(!Ext6.isEmpty(base_form.findField('PersonDisp_begDate').getRawValue())){
			base_form.findField('PersonDisp_endDate').setMinValue(base_form.findField('PersonDisp_begDate').getValue());
		} else {
			base_form.findField('PersonDisp_endDate').setMinValue(null);
		}
	},
	getPersonDispNumber: function() {
		var base_form = this.FormPanel.getForm();
		var persondisp_num_field = base_form.findField('PersonDisp_NumCard');
		Ext.Ajax.request({
			callback: function (options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj) {
						persondisp_num_field.setValue(response_obj.PersonDisp_NumCard);
						persondisp_num_field.focus(true);
					} else {
						sw.swMsg.alert(langs('Ошибка'), langs('Номер не был получен'));
					}
				} else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении номера талона'));
				}
			},
			url: '/?c=PersonDisp&m=getPersonDispNumber'
		});
	},
	//TAG: initComp
	initComponent: function() {
		var win = this;

		win.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanel', {
			region: 'north',
			border: false,
			userMedStaffFact: win.userMedStaffFact,
			ownerWin: win
		});

		win.sicknessDiagStore = new Ext.db.AdapterStore({
			autoLoad: false,
			dbFile: 'Promed.db',
			fields: [
				{ name: 'SicknessDiag_id', type: 'int' },
				{ name: 'Sickness_id', type: 'int' },
				{ name: 'Sickness_Code', type: 'int' },
				{ name: 'PrivilegeType_id', type: 'int' },
				{ name: 'Sickness_Name', type: 'string' },
				{ name: 'Diag_id', type: 'int' },
				{ name: 'SicknessDiag_begDT', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'SicknessDiag_endDT', type: 'date', dateFormat: 'd.m.Y' }
			],
			key: 'SicknessDiag_id',
			sortInfo: {
				field: 'Diag_id'
			},
			tableName: 'SicknessDiag'
		});


		var deleteGridSelectedRecord = function(gridId, idField) { //TODO: Удалить?
			var grid = win.queryById(gridId).getGrid();
			var record = grid.getSelectionModel().getSelected();
			Ext6.Msg.show({
				buttons: Ext6.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if (buttonId == 'yes') {
						if (!grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField)) {
							return false;
						}
						switch (Number(record.get('RecordStatus_Code'))) {
							case 0:
								grid.getStore().remove(record);
								break;
							case 1:
							case 2:
								record.set('RecordStatus_Code', 3);
								record.commit();
								grid.getStore().filterBy(function(rec) {
									if (Number(rec.get('RecordStatus_Code')) == 3) {
										return false;
									} else {
										return true;
									}
								});
								break;
						}
					}
					if (grid.getStore().getCount() > 0) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(win),
				icon: Ext6.MessageBox.QUESTION,
				msg: 'Вы действительно хотите удалить эту запись?',
				title: 'Вопрос'
			});
		}

		var fillFromAnotherPersonDisp = function () {
			var win = this,
				form = win.FormPanel.getForm(),
				field = win.FormPanel.getForm().findField('LpuAnother_id');
			var pregnancySpec_id = field.getStore().getById(field.getValue()).data['PregnancySpec_id'];
			form.findField('Lpu_aid').setValue(pregnancySpec_id);
			loadPregnancySpecIntoForm(pregnancySpec_id);
		}
		var loadPregnancySpecIntoForm = function(pregnancySpec_id) {

			var form = win.FormPanel.getForm();
			var params = new Object();
			params.PregnancySpec_id = pregnancySpec_id;
			var formFields = win.PregnancyPanel.formFields;
			win.ChildGrid.getStore().removeAll();
			win.DeadChildGrid.getStore().removeAll();
			Ext6.Ajax.request({
				method: 'post',
				callback: function(options, success, response) {
					var resp = Ext6.util.JSON.decode(response.responseText);
					formFields.Lpu_aid.setValue(resp[0].Lpu_aid);
					formFields.PregnancySpec_Period.setValue(resp[0].PregnancySpec_Period);
					formFields.PregnancySpec_Count.setValue(resp[0].PregnancySpec_Count);
					formFields.PregnancySpec_CountBirth.setValue(resp[0].PregnancySpec_CountBirth);
					formFields.PregnancySpec_CountAbort.setValue(resp[0].PregnancySpec_CountAbort);
					formFields.PregnancySpec_BirthDT.setValue(resp[0].PregnancySpec_BirthDT);
					setTimeout(function (){formFields.BirthResult_id.setValue(resp[0].BirthResult_id)},200);
					formFields.PregnancySpec_OutcomPeriod.setValue(resp[0].PregnancySpec_OutcomPeriod);
					formFields.PregnancySpec_OutcomDT.setValue(resp[0].PregnancySpec_OutcomDT);
					setTimeout(function (){formFields.BirthSpec_id.setValue(resp[0].BirthSpec_id)},200);
					formFields.PregnancySpec_IsHIVtest.setValue(resp[0].PregnancySpec_IsHIVtest);
					formFields.PregnancySpec_IsHIV.setValue(resp[0].PregnancySpec_IsHIV);
					if (resp[0].EvnSection_id) {
						formFields.EvnSection_id.setValue(resp[0].EvnSection_id);
						formFields.EvnSection_pid.setValue(resp[0].EvnSection_pid);
						//загружаю грид с детьми и мертворожденными
						win.ChildGrid.getStore().load({
							params: {
								EvnSection_pid: formFields.EvnSection_pid.getValue(),
								Person_id:form.findField('Person_id').getValue()
							}
						});
						win.DeadChildGrid.getStore().load({
							params: {
								EvnSection_id: formFields.EvnSection_id.getValue()
							}
						});
					}

					win.ChildPanel.checkContent();
					win.DeadChildPanel.checkContent();
					//загружаю лаб обследования
					win.PregnancyLabGrid.getStore().load({
						params: {
							PregnancySpec_id: pregnancySpec_id
						},
						noFocusOnLoad: true
					});
					win.PregnancyLabPanel.checkContent();
					//загружаю грид с осложнениями
					win.PregnancySpecComplicationGrid.getStore().load({
						params: {
							PregnancySpec_id: pregnancySpec_id
						}
					});
					win.PregnancySpecExtragenitalDiseaseGrid.getStore().load({
						params: {
							PregnancySpec_id: pregnancySpec_id
						}
					});
					win.PregnancySpecExtragenitalDiseasePanel.checkContent();
				},
				params: params,
				url: '/?c=PregnancySpec&m=load'
			});
		}

		//TAG: Грид История врачей
		win.PersonDispHistGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					itemId: 'HistEdit',
					handler: function() {
						win.openPersonDispHistEditWindow('edit');
					}
				}, {
					text: 'Просмотр',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPersonDispHistEditWindow('view');
					}
				}, {
					text: 'Удалить',
					itemId: 'HistDelete',
					iconCls:'menu_dispdel',
					handler: function() {
						win.deletePersonDispHist();
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [
				{ dataIndex: 'PersonDispHist_id', type: 'int', header: 'ID', key: true, hidden: true },
				{ dataIndex: 'MedPersonal_id', hidden: true},
				{ dataIndex: 'LpuSection_id', hidden: true},
				{ dataIndex: 'MedPersonal_Fio', type: 'string', header: langs('Врач'), width: 270 },
				{ dataIndex: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 270 },
				{ dataIndex: 'PersonDispHist_begDate', type: 'date', formatter: 'date("d.m.Y")', header: langs('Начало') },
				{ dataIndex: 'PersonDispHist_endDate', type: 'date', formatter: 'date("d.m.Y")', header: langs('Окончание'), flex: 1 },
				{
					width: 40,
					dataIndex: 'PersonDispHist_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.PersonDispHistGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
					},
					hidden: !isUserGroup('PersonDispHistEdit')
				}
			],
			load: function() {
				this.getStore().load({
					params: {
						PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
					}
				});
			},
			store: Ext6.create('Ext6.data.Store', {
				autoLoad: false,
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.PersonDispHist_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{ name: 'PersonDispHist_id', type: 'int', header: 'ID', key: true, hidden: true },
					{ name: 'MedPersonal_id', hidden: true},
					{ name: 'LpuSection_id', hidden: true},
					{ name: 'MedPersonal_Fio', type: 'string', header: langs('ФИО врача')},
					{ name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 180 },
					{ name: 'PersonDispHist_begDate', type: 'date', dateFormat: 'd.m.Y', header: langs('Начало'), width: 100},
					{ name: 'PersonDispHist_endDate', type: 'date', dateFormat: 'd.m.Y', header: langs('Окончание'), width: 100}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonDisp&m=loadPersonDispHistlist',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.PersonDispHistPanel.checkContent();
						 if(records.length){
							var today = new Date();
							today.setHours(0,0,0,0);
							var index = win.PersonDispHistGrid.getStore().findBy(function(rec){
								return(
									(rec.get('PersonDispHist_begDate') <= today && Ext6.isEmpty(rec.get('PersonDispHist_endDate')))
									|| (rec.get('PersonDispHist_begDate') <= today && rec.get('PersonDispHist_endDate') >= today)
								);
							});
							if(index > -1){
								win.enableButtonsForMedStaff(index);
								var mp =  win.PersonDispHistGrid.getStore().getAt(index).get('MedPersonal_Fio');
								win.FormPanel.getForm().findField('PersonDispHist_MedPersonalFio').setValue(mp);
							}
							var base_form = win.FormPanel.getForm();
							var histBegDate = false;
							var histEndDate = false;
							win.PersonDispHistGrid.getStore().each(function(rec){
								if(!histBegDate || histBegDate < rec.get('PersonDispHist_begDate')){
									histBegDate = rec.get('PersonDispHist_begDate');
									histEndDate = rec.get('PersonDispHist_endDate');
								}
							});
							if(base_form.findField('PersonDisp_endDate').minValue < histBegDate){
								base_form.findField('PersonDisp_endDate').minValue = histBegDate;
							}
						}
							var disableActions = false;
							if(!Ext6.isEmpty(win.PersonDispCloseDate) && !Ext6.isEmpty(win.FormPanel.getForm().findField('PersonDisp_endDate').getRawValue())){
								disableActions = true;
							}
							win.disablePersonDispHistActions(disableActions);

					}
				},
				sorters: [
					'PersonDispHist_id'
				]
			})
		});
		//TAG: Грид Сопутствующие диагнозы
		win.PersonDispSopDiagGrid = new Ext6.grid.Panel({
			autoLoad: false,
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPersonDispSopDiagEditWindow('edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						win.deletePersonDispSopDiag();
					}
				}]
			}),
			showRecordMenu: function(el, PersonDispSopDiag_id) {
				this.recordMenu.data_id = PersonDispSopDiag_id;
				this.recordMenu.showBy(el);
			},
			columns: [
				{ dataIndex: 'PersonDispSopDiag_id', type: 'int', header: 'ID', key: true, hidden: true },
				{ dataIndex: 'Diag_Code', type: 'string', header: 'Код'},
				{ dataIndex: 'Diag_Name', type: 'string', header: langs('Наименование'), flex: 1 },
				{ dataIndex: 'DopDispDiagType_Name', type: 'string', header: langs('Характер заболевания'), width: 300},
				{
					width: 40,
					dataIndex: 'PersonDispSopDiag_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.PersonDispSopDiagGrid.id + "\").showRecordMenu(this, " + record.get('PersonDispSopDiag_id') + ");'></div>";
					}
				}
			],
			load: function() {
				this.getStore().load({
					params: {
						PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
					}
				});
			},

			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.PersonDispSopDiag_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{ name: 'PersonDispSopDiag_id', type: 'int', header: 'ID', key: true, hidden: true },
					{ name: 'Diag_Code', type: 'string', header: 'Код'},
					{ name: 'Diag_Name', type: 'string', header: 'Наименование' },
					{ name: 'DopDispDiagType_Name', type: 'string', header: 'Характер заболевания', width: 300}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonDisp&m=loadPersonDispSopDiaglist',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.DiagPanel.checkContent();
					}
				},
				sorters: [
					'PersonDispSopDiag_id'
				]
			})
		});
		//TAG: Грид Льготы
		win.PersonPrivilegeGrid = new Ext6.grid.Panel({
			autoLoad: false,
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPersonPrivilegeEditWindow('edit');
					}
				}, {
					text: 'Просмотр',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPersonPrivilegeEditWindow('view');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						win.deletePersonPrivilege();
					}
				}]
			}),
			showRecordMenu: function(el, id) {
				this.recordMenu.data_id = id;
				this.recordMenu.showBy(el);
			},
			columns: [
				{ dataIndex: 'PersonPrivilege_id', type: 'int', header: 'ID', key: true, hidden: true },
				{ dataIndex: 'Lpu_id', type: 'int', hidden: true },
				{ dataIndex: 'Person_id', type: 'int', hidden: true },
				{ dataIndex: 'PersonPrivilege_id', type: 'int', hidden: true },
				{ dataIndex: 'Privilege_Refuse', type: 'string', hidden: true },
				{ dataIndex: 'Privilege_RefuseNextYear', type: 'string', hidden: true },
				{ dataIndex: 'PrivilegeType_id', type: 'int', hidden: true },
				{ dataIndex: 'Server_id', type: 'int', hidden: true },
				{ dataIndex: 'PrivilegeType_Code', type: 'int', header: langs('Код') },
				{ dataIndex: 'PrivilegeType_Name', type: 'string', header: langs('Категория'), flex: 1 },
				{ dataIndex: 'Privilege_begDate', type: 'date', formatter: 'date("d.m.Y")', header: langs('Начало') },
				{ dataIndex: 'Privilege_endDate', type: 'date', formatter: 'date("d.m.Y")', header: langs('Окончание') },
				{ dataIndex: 'Lpu_Name', type: 'string', header: langs('ЛПУ'), width: 250 },
				{
					width: 40,
					dataIndex: 'PersonPrivilege_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.PersonPrivilegeGrid.id + "\").showRecordMenu(this, " + record.get('PersonPrivilege_id') + ");'></div>";
					}
				}
			],
			load: function() {
				this.getStore().load({
					params: {
						Person_id: win.FormPanel.getForm().findField('Person_id').getValue(),
						Server_id: win.FormPanel.getForm().findField('Server_id').getValue()
					}
				});
			},

			store: Ext6.create('Ext6.data.Store', {
				autoLoad: false,
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.PersonPrivilege_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{ name: 'PersonPrivilege_id', type: 'int', header: 'ID', key: true, hidden: true },
					{ name: 'Lpu_id', type: 'int', hidden: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'PersonPrivilege_id', type: 'int', hidden: true },
					{ name: 'Privilege_Refuse', type: 'string', hidden: true },
					{ name: 'Privilege_RefuseNextYear', type: 'string', hidden: true },
					{ name: 'PrivilegeType_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'PrivilegeType_Code', type: 'int', header: 'Код' },
					{ name: 'PrivilegeType_Name', type: 'string', header: 'Категория' },
					{ name: 'Privilege_begDate', type: 'date', dateFormat: 'd.m.Y', header: 'Начало' },
					{ name: 'Privilege_endDate', type: 'date', dateFormat: 'd.m.Y', header: 'Окончание' },
					{ name: 'Lpu_Name', type: 'string', header: 'ЛПУ', width: 250 }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: C_PRIV_LOAD_LIST,
					reader: {
						type: 'json',
					//	rootProperty: 'data',
					//	totalProperty: 'totalCount'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.PersonPrivilegePanel.checkContent();
					}
				},
				sorters: [
					'PersonPrivilege_id'
				]
			})
		});
		//TAG: Грид Контроль посещений
		win.PersonDispVizitGrid = new Ext6.grid.Panel({
			autoLoad: false,
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				//~ userCls: 'person-disp-vizit-grid-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPersonDispVizitEditWindow('edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						win.deletePersonDispVizit();
					}
				}]
			}),
			showRecordMenu: function(el, id) {
				this.recordMenu.data_id = id;
				this.recordMenu.showBy(el);
			},
			columns: [
				{ dataIndex: 'PersonDispVizit_id', type: 'int', header: 'ID', key: true, hidden: true },
				{ dataIndex: 'PersonDispVizit_NextDate', type: 'date', formatter: 'date("d.m.Y")', header: 'Назначено явиться', width: 200 },
				{ dataIndex: 'PersonDispVizit_NextFactDate', type: 'date', formatter: 'date("d.m.Y")', header: 'Явился', flex: 1 },
				{
					width: 40,
					dataIndex: 'PersonDispVizit_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.PersonDispVizitGrid.id + "\").showRecordMenu(this, " + record.get('PersonDispVizit_id') + ");'></div>";
					}
				}
			],
			load: function() {
				this.getStore().load({
					params: {
						PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
					}
				});
			},
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.PersonDispVizit_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{ name: 'PersonDispVizit_id', type: 'int', header: 'ID', key: true, hidden: true },
					{ name: 'PersonDispVizit_NextDate', type: 'date', dateFormat: 'd.m.Y', header: 'Назначено явиться', width: 200 },
					{ name: 'PersonDispVizit_NextFactDate', type: 'date', dateFormat: 'd.m.Y', header: 'Явился', width: 200 }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonDisp&m=loadPersonDispVizitList',
					reader: {
						type: 'json'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.PersonDispVizitPanel.checkContent();
					}
				},
				sorters: [
					'PersonDispVizit_id'
				]
			})
		});

		//TAG: Грид Профилактические осмотры
		win.EvnPLDispProfGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 780,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					itemId: 'EvnPLDispProf_EditOption',
					handler: function() {
						win.openEvnPLDispProfEditWindow('edit');
					}
				}, {
					text: 'Просмотр',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openEvnPLDispProfEditWindow('view');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					itemId: 'EvnPLDispProf_DeleteOption',
					handler: function() {
						win.deleteEvnPLDispProf();
					}
				}]
			}),
			showRecordMenu: function(el, id) {
				this.recordMenu.data_id = id;
				this.recordMenu.showBy(el);
			},
			columns: [
				{ dataIndex: 'EvnPLDispProf_id', type: 'int', header: 'ID', key: true, hidden: true },
				{ dataIndex: 'EvnPLDispProf_Year', type: 'int', header: 'Год', width: 60},
				{ dataIndex: 'EvnPLDispProf_setDate', type: 'date', formatter: 'date("d.m.Y")', header: langs('Дата начала'), width: 120 },
				{ dataIndex: 'EvnPLDispProf_disDate', type: 'date', formatter: 'date("d.m.Y")', header: langs('Дата окончания'), width: 120 },
				{ dataIndex: 'EvnPLDispProf_IsEndStage', renderer: function(value, p, rec) {
						if (rec.get('EvnPLDispProf_id')) {
							if (value == "2") {
								return "Да";
							} else {
								return "Нет";
							}
						} else {
							return '';
						}
					}, header: langs('Профосмотр закончен'), width: 120},
				{ dataIndex: 'accessType', type: 'string', hidden: true},
				{ dataIndex: 'Person_id', type: 'int', hidden: true},
				{ dataIndex: 'Server_id', type: 'int', hidden: true},
				{ dataIndex: 'Lpu_Nick', type: 'string', header: 'МО', flex: 1},
				{
					width: 40,
					dataIndex: 'EvnPLDispProf_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.EvnPLDispProfGrid.id + "\").showRecordMenu(this, " + record.get('EvnPLDispProf_id') + ");'></div>";
					}
				}
			],
			load: function() {
				this.getStore().load({
					params: {
						Person_id: win.FormPanel.getForm().findField('Person_id').getValue(),
						Server_id: win.FormPanel.getForm().findField('Server_id').getValue(),
						PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
					}
				});
			},
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.EvnPLDispProf_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{ dataIndex: 'EvnPLDispProf_id', type: 'int', header: 'ID', key: true, hidden: true },
					{ dataIndex: 'EvnPLDispProf_Year', type: 'int', header: 'Год', width: 60},
					{ dataIndex: 'EvnPLDispProf_setDate', type: 'date', formatter: 'date("d.m.Y")', header: langs('Дата начала'), width: 120 },
					{ dataIndex: 'EvnPLDispProf_disDate', type: 'date', formatter: 'date("d.m.Y")', header: langs('Дата окончания'), width: 120 },
					{ dataIndex: 'EvnPLDispProf_IsEndStage',type: 'string', header: langs('Профосмотр закончен'), width: 50},
					{ dataIndex: 'accessType', type: 'string', hidden: true},
					{ dataIndex: 'Person_id', type: 'int', hidden: true},
					{ dataIndex: 'Server_id', type: 'int', hidden: true},
					{ dataIndex: 'Lpu_Nick', type: 'string', header: 'МО', width: 300}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnPLDispProf&m=loadEvnPLDispProfList',
					reader: {
						type: 'json'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.EvnPLDispProfPanel.checkContent();
					}
				},
				sorters: [
					'EvnPLDispProf_id'
				]
			})
		});

		//TAG: Грид Целевые показатели
		win.PersonDispTargetRateGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			isLoaded: false,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [/*{
					text: 'Добавить',
					iconCls:'menu_dispadd',
					handler: function() {
						win.openPersonDispTargetRateEditWindow('add');
					}
				}, */{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPersonDispTargetRateEditWindow('edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						win.deletePersonDispRate(win.PersonDispTargetRateGrid.recordMenu.data_id);
					}
				}]
			}),
			showRecordMenu: function(el, id) {
				this.recordMenu.data_id = id;
				this.recordMenu.showBy(el);
			},
			columns: [
				{ dataIndex: 'RateType_id', type: 'int', header: 'ID', key: true, hidden: true },
				{ dataIndex: 'PersonDispFactRate_id', type: 'int', header: 'FactRate_id', hidden: true },
				{ dataIndex: 'Rate_id', type: 'int', header: 'Rate_id', hidden: true },
				{ dataIndex: 'RateType_Name', type: 'string', header: 'Показатель', width: 300 },
				{ dataIndex: 'TargetRate_Value', type: 'float', header: 'Целевое значение', width: 140 },
				{ dataIndex: 'FactRate_Value', type: 'float', header: 'Фактическое значение', width: 140,
					renderer: function (value, metaData, record) {
						if(value==0) return "";
						else return value;
					}
				},
				{ dataIndex: 'FactRate_setDT', type: 'date', formatter: 'date("d.m.Y")', header: 'Дата результата', flex: 1 },
				{
					width: 40,
					dataIndex: 'RateType_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.PersonDispTargetRateGrid.id + "\").showRecordMenu(this, " + record.get('RateType_id') + ");'></div>";
					}
				}
			],
			load: function() {
				this.getStore().load({
					params: {
						PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
					}
				});
			},
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.RateType_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{ name: 'RateType_id', type: 'int', header: 'ID', key: true },
					{ name: 'PersonDispFactRate_id', type: 'int', header: 'FactRate_id', width: 300 },
					{ name: 'RateType_Name', type: 'string', header: 'Показатель', width: 300 },
					{ name: 'TargetRate_Value', type: 'float', header: 'Целевое значение', width: 200 },
					{ name: 'FactRate_Value', type: 'float', header: 'Фактическое значение', width: 200 },
					{ name: 'FactRate_setDT', type: 'date', dateFormat: 'd.m.Y', header: 'Дата результата', flex: 1 }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonDisp&m=loadPersonDispTargetRateList',
					reader: {
						type: 'json'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.PersonDispTargetRatePanel.checkContent();
					}
				},
				sorters: [
					'RateType_id'
				]
			})
		});


		//TAG: БЕРЕМ. Лабораторные обследования PregnancyLabGrid
		win.PregnancyLabGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			isLoaded: false,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					itemId: 'PregnancyLabEdit',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openEvnUslugaEditWindow('edit');
					}
				}, {
					text: 'Просмотр',
					itemId: 'PregnancyLabView',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openEvnUslugaEditWindow('view');
					}
				}, {
					text: 'Удалить',
					itemId: 'PregnancyLabDelete',
					iconCls:'menu_dispdel',
					handler: function() {
						win.deletePregnancyLab(win.PregnancyLabGrid.recordMenu.data_id);
					}
				}]
			}),
			showRecordMenu: function(el, evn_usluga_id, rowIndex) {
				this.recordMenu.data_id = evn_usluga_id;
				this.recordMenu.rowIndex = rowIndex;

				var readOnly = ((win.action != 'edit') && (win.action != 'add'));
				if (evn_usluga_id) {
					if (readOnly) {
						this.recordMenu.queryById('PregnancyLabEdit').hide();
						this.recordMenu.queryById('PregnancyLabView').show();
						this.recordMenu.queryById('PregnancyLabDelete').hide();
					} else {
						this.recordMenu.queryById('PregnancyLabEdit').show();
						this.recordMenu.queryById('PregnancyLabView').show();
						this.recordMenu.queryById('PregnancyLabDelete').show();
					}
					this.recordMenu.showBy(el);
				}
			},
			columns: [
				{
					dataIndex: 'EUPS_setDate',
					header: 'Дата',
					hidden: false,
					renderer: Ext6.util.Format.dateRenderer('d.m.Y'),
					resizable: false,
					sortable: true,
					width: 100
				},
				{
					dataIndex: 'Usluga_Code',
					header: 'Код',
					hidden: false,
					resizable: false,
					sortable: true,
					width: 100
				},
				{
					dataIndex: 'Usluga_Name',
					header: 'Наименование',
					hidden: false,
					resizable: true,
					sortable: true,
					flex: 1
				},
				{
					dataIndex: 'lpu_name',
					header: 'Место выполнения',
					hidden: false,
					resizable: true,
					sortable: true,
					width: 200
				},
				{
					dataIndex: 'pregPeriod',
					header: 'Срок беременности',
					hidden: false,
					resizable: true,
					sortable: true,
					width: 200
				}, {
					width: 40,
					dataIndex: 'PregnancyLab_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.PregnancyLabGrid.id + "\").showRecordMenu(this, " + record.get('EvnUslugaPregnancySpec_id') +","+metaData.rowIndex+");'></div>";
					}
				}
			],
			load: function() {
				this.getStore().load({
					params: {
						PregnancySpec_id: win.FormPanel.getForm().findField('PregnancySpec_id').getValue()
					},
					noFocusOnLoad: true,
					callback: function() {
						win.calcPregPeriod();
					}
				});
			},
			store: new Ext6.data.Store({
				autoLoad: false,
				baseParams: {
					'parent': 'EvnPS'
				},
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.EvnUslugaPregnancySpec_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{
						mapping: 'EvnUslugaPregnancySpec_id',
						name: 'EvnUslugaPregnancySpec_id',
						type: 'int'
					},
					{
						mapping: 'EvnClass_SysNick',
						name: 'EvnClass_SysNick',
						type: 'string'
					},
					{
						mapping: 'EvnUsluga_setTime',
						name: 'EvnUsluga_setTime',
						type: 'string'
					},
					{
						dateFormat: 'd.m.Y',
						mapping: 'EUPS_setDate',
						name: 'EUPS_setDate',
						type: 'date'
					},
					{
						mapping: 'Usluga_Code',
						name: 'Usluga_Code',
						type: 'string'
					},
					{
						mapping: 'Usluga_Name',
						name: 'Usluga_Name',
						type: 'string'
					},
					{
						mapping: 'lpu_name',
						name: 'lpu_name',
						type: 'string'
					},
					{
						mapping: 'EUPS_Kolvo',
						name: 'EUPS_Kolvo',
						type: 'float'
					}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PregnancySpec&m=loadEvnUslugaPregnancySpecGrid',
					reader: {
						type: 'json'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.PregnancyLabPanel.checkContent();
						win.calcPregPeriod();
					}
				},
				sorters: [
					'EUPS_setDate'
				]
			}),
		});

		//TAG: БЕРЕМ. осложнения грид
		win.PregnancySpecComplicationGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			isLoaded: false,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					itemId: 'PregnancySpecComplicationEdit',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPregnancySpecComplicationEditWindow('edit');
					}
				}, /*{ //всегда доступно на редактирование?
					text: 'Просмотр',
					itemId: 'PregnancySpecComplicationView',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPregnancySpecComplicationEditWindow('view');
					}
				}, */{
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						//TODO: удаление как в нефрологии
						//~ win.deletePregnancyDiag(win.PregnancyHistoryGrid.recordMenu.data_id);
					}
				}]
			}),
			showRecordMenu: function(el, id, rowIndex) {
				this.recordMenu.data_id = id;
				this.recordMenu.rowIndex = rowIndex;

				this.recordMenu.showBy(el);
			},
			columns: [
				{ dataIndex: 'PregnancySpecComplication_id', type: 'int', header: 'ID', hidden: true},
				{ dataIndex: 'PregnancySpec_id', type: 'int', hidden: true},
				//– Дата/время установки; обязательное
				{ dataIndex: 'PSC_setDT', type: 'date', formatter: 'date("d.m.Y")', header: 'Дата', hidden: false, width: 100},
				//– Диагноз. обязательное
				{ dataIndex: 'Diag_Name', type: 'string', header: 'Диагноз', hidden: false, flex:1 },
				{ dataIndex: 'Diag_id', type: 'int', hidden: true},
				{ dataIndex: 'RecordStatus_Code', type: 'int', hidden: true},
				{
					width: 40,
					dataIndex: 'PregnancySpecComplication_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.PregnancySpecComplicationGrid.id + "\").showRecordMenu(this, " + record.get('PregnancySpecComplication_id') + ", "+ metaData.rowIndex + ");'></div>";
					}
				}
			],
			load: function() {
				this.getStore().load({
					params: {
						PregnancySpec_id: win.FormPanel.getForm().findField('PregnancySpec_id').getValue()
					}
				});
			},
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.PregnancySpecComplication_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{ name: 'PregnancySpecComplication_id', type: 'int', header: 'ID', key: true, hidden: true},
					{ name: 'PregnancySpec_id', type: 'int', hidden: true},
					//– Дата/время установки; обязательное
					{ name: 'PSC_setDT', type: 'date', dateFormat: 'd.m.Y', header: 'Дата', hidden: false, width: 100},
					//– Диагноз. обязательное
					{ name: 'Diag_Name', type: 'string', header: 'Диагноз', hidden: false, width: 200},
					{ name: 'Diag_id', type: 'int', hidden: true},
					{ name: 'RecordStatus_Code', type: 'int', hidden: true}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PregnancySpec&m=loadPregnancySpecComplication',
					reader: {
						type: 'json'
					}
				},
				listeners: {
					'load': function(store, records) {

						win.PregnancySpecComplicationPanel.checkContent();
					}
				},
				sorters: [
					'PSC_setDT' // 'PregnancySpec_id'
				]
			})
		});
		//TAG: БЕРЕМ. экстра заболевания
		win.PregnancySpecExtragenitalDiseaseGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			isLoaded: false,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					itemId: 'PregnancySpecExtragenitalDiseaseEdit',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPregnancySpecExtragenitalDiseaseEditWindow('edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						//TODO: удаление как в нефрологии
						//~ win.deletePregnancyDiag(win.PregnancyHistoryGrid.recordMenu.data_id);
					}
				}]
			}),
			showRecordMenu: function(el, id, rowIndex) {
				this.recordMenu.data_id = id;
				this.recordMenu.rowIndex = rowIndex;

				this.recordMenu.showBy(el);
			},
			columns: [
				{ dataIndex: 'PSED_id', type: 'int', header: 'ID', key: true, hidden: true},
				{ dataIndex: 'PregnancySpec_id', type: 'int', hidden: true},
				//– Дата/время установки; обязательное
				{ dataIndex: 'PSED_setDT', type: 'date', formatter: 'date("d.m.Y")', header: 'Дата', hidden: false, width: 100},
				//– Диагноз. обязательное
				{ dataIndex: 'Diag_Name', type: 'string', header: 'Диагноз', hidden: false, flex:1 },
				{ dataIndex: 'Diag_id', type: 'int', hidden: true},
				{ dataIndex: 'RecordStatus_Code', type: 'int', hidden: true},
				{
					width: 40,
					dataIndex: 'PregnancySpecExtragenitalDisease_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.PregnancySpecExtragenitalDiseaseGrid.id + "\").showRecordMenu(this, " + record.get('PSED_id') + ", "+ metaData.rowIndex + ");'></div>";
					}
				}
			],
			load: function() {
				this.getStore().load({
					params: {
						PregnancySpec_id: win.FormPanel.getForm().findField('PregnancySpec_id').getValue()
					}
				});
			},
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.PSED_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{ name: 'PSED_id', type: 'int', header: 'ID', key: true, hidden: true},
					{ name: 'PregnancySpec_id', type: 'int', hidden: true},
					//– Дата/время установки; обязательное
					{ name: 'PSED_setDT', type: 'date', dateFormat: 'd.m.Y', header: 'Дата', hidden: false, width: 100},
					//– Диагноз. обязательное
					{ name: 'Diag_Name', type: 'string', header: 'Диагноз', hidden: false, width: 200},
					{ name: 'Diag_id', type: 'int', hidden: true},
					{ name: 'RecordStatus_Code', type: 'int', hidden: true}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PregnancySpec&m=loadPregnancySpecExtragenitalDisease',
					reader: {
						type: 'json'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.PregnancySpecExtragenitalDiseasePanel.checkContent();
					}
				},
				sorters: [
					'PregnancySpec_id'
				]
			})
		});

		//TAG: БЕРЕМ. история диагнозов
		win.PregnancyHistoryGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			isLoaded: false,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					itemId: 'PregnancyHistoryEdit',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPregnancyDiagEditWindow('edit');
					}
				}, {
					text: 'Просмотр',
					itemId: 'PregnancyHistoryView',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPregnancyDiagEditWindow('view');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						win.deletePregnancyDiag(win.PregnancyHistoryGrid.recordMenu.data_id);
					}
				}]
			}),
			showRecordMenu: function(el, id, rowIndex) {
				this.recordMenu.data_id = id;
				this.recordMenu.rowIndex = rowIndex;
				if(rowIndex==0) {
					this.recordMenu.queryById('PregnancyHistoryEdit').hide();
					this.recordMenu.queryById('PregnancyHistoryView').show();
				} else {
					this.recordMenu.queryById('PregnancyHistoryEdit').show();
					this.recordMenu.queryById('PregnancyHistoryView').hide();
				}
				this.recordMenu.showBy(el);
			},
			columns: [
				{dataIndex: 'DiagDispCard_id', type: 'int', hidden: true, key:true},
				{dataIndex: 'DiagDispCard_Date',  type: 'string', header: langs('Дата установки'), width: 150},
				{dataIndex: 'Diag_FullName',  type: 'string', header: langs('Диагноз'), flex:1 },
				{dataIndex: 'Diag_id',  type: 'int', hidden: true, width: 436},
				{
					width: 40,
					dataIndex: 'DiagDispCard_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.PregnancyHistoryGrid.id + "\").showRecordMenu(this, " + record.get('DiagDispCard_id') + ", "+ metaData.rowIndex + ");'></div>";
					}
				}
			],
			load: function() {
				this.getStore().load({
					params: {
						PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
					}
				});
			},
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.DiagDispCard_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{name: 'DiagDispCard_id', type: 'int', hidden: true, key:true},
					{name: 'DiagDispCard_Date',  type: 'string', header: langs('Дата установки'), width: 150},
					{name: 'Diag_FullName',  type: 'string', header: langs('Диагноз'), width: 436},
					{name: 'Diag_id',  type: 'int', hidden: true, width: 436}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '?c=PersonDisp&m=loadDiagDispCardHistory',
					reader: {
						type: 'json'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.PregnancyHistoryPanel.checkContent();
					}
				},
				sorters: [
					'DiagDispCard_id'
				]
			}),
		});

		//TAG: ДЕТИ грид
		win.ChildGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: '100%',
			isLoaded: false,
			disableSelection: true,
			columns: [
				{dataIndex:'ChildEvnPS_id', type:'int', header:'ID', key:true, hidden: true},
				{dataIndex:'Person_F', type:'string', hidden:false, header:'ФИО', flex:1,
					renderer: function (value, metaData, record) {
						var s = '';
						s+= record.get('Sex_name')=='Женский' ?
									'<b class="child-female child-sex"></b>'
								:	'<b class="child-male child-sex"></b>';
						s+= '<span class="child-name">'+record.get('Person_F')+' '+record.get('Person_I')+' '+record.get('Person_O')+'</span>'
						return s;
					}
				},
				{dataIndex:'Person_F', type:'string', hidden:true, header:'Фамилия'},
				{dataIndex:'Person_I', type:'string', hidden:true, header:'Имя'},
				{dataIndex:'Person_O', type:'string', hidden:true, header:'Отчество'},
				{dataIndex:'Person_Bday', type:'date', formatter: 'date("d.m.Y")', hidden:false, header:'Д/Р', width:110},
				{dataIndex:'Sex_name', type:'string', hidden:true, header:'Пол', width:80},
				{dataIndex:'Person_Weight', type:'float', hidden:true, header:'Масса при рождении', width:120},
				{dataIndex:'Okei_id', type:'int', hidden:true},
				{dataIndex:'PersonWeight_text', type:'string', hidden:false, header:'Масса', width:120},
				{dataIndex:'Person_Height', type:'float', hidden:false, header:'Рост', width:110},
				{dataIndex:'BirthSvid_id', type:'int', hidden:true},
				{dataIndex:'BirthSvid_Num', type:'string', hidden:false, header:'Св-во о рождении', width:150},
				{dataIndex:'BirthResult', type:'string', hidden:false, header:'Результат', width:100},
				{dataIndex:'CountChild', type:'float', hidden:false, header:'№ по счету', width:110},
				{dataIndex:'RecordStatus_Code', type:'int', hidden:true},
				{dataIndex:'EvnLink_id', type:'int', hidden:true},
				{dataIndex:'Person_id', type:'int', hidden:true},
				{dataIndex:'Person_cid', type:'int', hidden:true},
				{dataIndex:'Server_id', type:'int', hidden:true}
			],
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.ChildEvnPS_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{name:'ChildEvnPS_id', type:'int', header:'ID', key:true},
					{name:'Person_F', type:'string', hidden:false, header:'Фамилия'},
					{name:'Person_I', type:'string', hidden:false, header:'Имя'},
					{name:'Person_O', type:'string', hidden:false, header:'Отчество'},
					{name:'Person_Bday', type:'date', hidden:false, dateFormat:'d.m.Y', header:'Дата рождения', width:110},
					{name:'Sex_name', type:'string', hidden:false, header:'Пол', width:80},
					{name:'Person_Weight', type:'float', hidden:true, header:'Масса при рождении', width:120},
					{name:'Okei_id', type:'int', hidden:true},
					{name:'PersonWeight_text', type:'string', hidden:false, header:'Масса при рождении', width:120},
					{name:'Person_Height', type:'float', hidden:false, header:'Рост при рождении (см)', width:110},
					{name:'BirthSvid_id', type:'int', hidden:true},
					{name:'BirthSvid_Num', type:'string', hidden:false, header:'Св-во о рождении', width:110},
					{name:'BirthResult', type:'string', hidden:false, header:'Результат родов', width:100},
					{name:'CountChild', type:'float', hidden:false, header:'Который по счету', width:110},
					{name:'RecordStatus_Code', type:'int', hidden:true},
					{name:'EvnLink_id', type:'int', hidden:true},
					{name:'Person_id', type:'int', hidden:true},
					{name:'Person_cid', type:'int', hidden:true},
					{name:'Server_id', type:'int', hidden:true}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=BirthSpecStac&m=loadChildGridData',
					reader: {
						type: 'json'
					}
				},
				listeners: {
					'load': function(store, records) {

						win.ChildPanel.checkContent();
					}
				},
				sorters: [
					'ChildEvnPS_id'
				]
			})
		});

		win.DeadChildGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: '100%',
			isLoaded: false,
			disableSelection: true,
			columns: [
				//– Врач;
				{ dataIndex: 'MedStaffFact_Name', type: 'string', header: 'Врач', hidden: false, width: 205 },
				//– Диагноз;
				{ dataIndex: 'Diag_Name', type: 'string', header: 'Диагноз', hidden: false, flex:1 },
				//– Пол;
				{ dataIndex: 'Sex_Name', type: 'string', header: 'Пол', hidden: false, width: 55,
					renderer: function (value, metaData, record) {
						return value=='Женский' ?
								'<div class="child-female">&nbsp;</div>'
							: 	'<div class="child-male">&nbsp;</div>' ;
					}
				},
				//– Масса;
				{ dataIndex: 'ChildDeath_Weight_text', type: 'string', header: 'Масса', width: 60 },
				//– Рост;
				{ dataIndex: 'ChildDeath_Height', type: 'float', header: 'Рост', width: 60 },
				//– Наступление смерти;
				{ dataIndex: 'PntDeathTime_Name', type: 'string', header: 'Наст. смерти', hidden: false },
				//– Доношенный;
				{ dataIndex: 'ChildTermType_Name', type: 'string', header: 'Доношенный', hidden: false },
				//– Который по счету.
				{ dataIndex: 'ChildDeath_Weight', type: 'float', hidden: true, width: 60 },
				{ dataIndex: 'Okei_wid', type: 'int', hidden: true, header: 'Масса', width: 60 },
				{ dataIndex: 'ChildDeath_Count', type: 'int', header: '№ по счету', width: 110 },
				{ dataIndex: 'ChildDeath_id', type: 'int', header: 'ID', hidden:true },
				{ dataIndex: 'LpuSection_id', type: 'int', hidden: true },
				{ dataIndex: 'MedStaffFact_id', type: 'int', hidden: true },
				{ dataIndex: 'Diag_id', type: 'int', hidden: true},
				{ dataIndex: 'Sex_id', type: 'int', hidden: true },
				{ dataIndex: 'PntDeathTime_id', type: 'int', hidden: true },
				{ dataIndex: 'ChildTermType_id', type: 'int', hidden: true },
				{ dataIndex: 'PntDeathSvid_id', type: 'int', hidden: true},
				{ dataIndex: 'PntDeathSvid_Num', type: 'string', header: 'Св-во о смерти', width: 160, hidden: false},
				{ dataIndex: 'RecordStatus_Code', type: 'int', hidden: true }
			],
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.ChildDeath_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					//– Врач;
					{ name: 'MedStaffFact_Name', type: 'string', header: 'Врач', hidden: false, width: 180 },
					//– Диагноз;
					{ name: 'Diag_Name', type: 'string', header: 'Диагноз', hidden: false, width: 120},
					//– Пол;
					{ name: 'Sex_Name', type: 'string', header: 'Пол', hidden: false, width: 40},
					//– Масса;
					{ name: 'ChildDeath_Weight_text', type: 'string', header: 'Масса', width: 60 },
					//– Рост;
					{ name: 'ChildDeath_Height', type: 'float', header: 'Рост (см)', width: 60 },
					//– Наступление смерти;
					{ name: 'PntDeathTime_Name', type: 'string', header: 'Наступление смерти', hidden: false },
					//– Доношенный;
					{ name: 'ChildTermType_Name', type: 'string', header: 'Доношенный', hidden: false },
					//– Который по счету.
					{ name: 'ChildDeath_Weight', type: 'float', hidden: true, width: 60 },
					{ name: 'Okei_wid', type: 'int', hidden: true, header: 'Масса', width: 60 },
					{ name: 'ChildDeath_Count', type: 'int', header: 'Который по счету', width: 105 },
					{ name: 'ChildDeath_id', type: 'int', header: 'ID', key: true, hidden: true },
					{ name: 'LpuSection_id', type: 'int', hidden: true },
					{ name: 'MedStaffFact_id', type: 'int', hidden: true },
					{ name: 'Diag_id', type: 'int', hidden: true},
					{ name: 'Sex_id', type: 'int', hidden: true },
					{ name: 'PntDeathTime_id', type: 'int', hidden: true },
					{ name: 'ChildTermType_id', type: 'int', hidden: true },
					{ name: 'PntDeathSvid_id', type: 'int', hidden: true},
					{ name: 'PntDeathSvid_Num', type: 'string', header: 'Свид-во о смерти', width: 110, hidden: false},
					{ name: 'RecordStatus_Code', type: 'int', hidden: true }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=BirthSpecStac&m=loadChildDeathGridData',
					reader: {
						type: 'json'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.DeadChildPanel.checkContent();
					}
				},
				sorters: [
					'ChildDeath_id'
				]
			})
		});

		//TAG: НЕФРОЛОГИЯ грид
		win.MorbusNephroLabGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			isLoaded: false,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openNephroLabEditWindow('edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						win.deleteNephroLab();
					}
				}]
			}),
			showRecordMenu: function(el, id) {
				this.recordMenu.data_id = id;
				this.recordMenu.showBy(el);
			},
			columns: [
				{dataIndex: 'MorbusNephroLab_id', type: 'int', header: 'ID', key: true, hidden: true},
				{dataIndex: 'RecordStatus_Code', type: 'int', hidden: true},
				{dataIndex: 'MorbusNephroLab_Date', type: 'date', formatter: 'date("d.m.Y")', header: 'Дата', width: 120},
				{dataIndex: 'MorbusNephro_id', type: 'int', hidden: true},
				{dataIndex: 'Rate_id', type: 'int', hidden: true},
				{dataIndex: 'RateType_id', type: 'int', hidden: true},
				{dataIndex: 'RateType_Name', type: 'string', header: 'Показатель', width: 200},
				{dataIndex: 'Rate_ValueStr', type: 'string', header: 'Значение', flex: 1},
				{
					width: 40,
					dataIndex: 'MorbusNephroLab_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.MorbusNephroLabGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
					}
				}
			],
			load: function() {
				this.getStore().load({
					params: this.getStore().baseParams
				});
			},
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.MorbusNephroLab_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{name: 'MorbusNephroLab_id', type: 'int', header: 'ID', key: true},
					{name: 'RecordStatus_Code', type: 'int', hidden: true},
					{name: 'MorbusNephroLab_Date', type: 'date', dateFormat: 'd.m.Y', header: 'Дата', width: 120},
					{name: 'MorbusNephro_id', type: 'int', hidden: true},
					{name: 'Rate_id', type: 'int', hidden: true},
					{name: 'RateType_id', type: 'int', hidden: true},
					{name: 'RateType_Name', type: 'string', header: 'Показатель', width: 200},
					{name: 'Rate_ValueStr', type: 'string', header: 'Значение', width: 240}
				],
				baseParams: {
					'isOnlyLast': 0
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=MorbusNephro&m=doLoadGridLab',
					reader: {
						type: 'json'
					}
				},
				listeners: {
					'load': function(store, records) {
						win.MorbusNephroLabPanel.checkContent();
					}
				},
				sorters: [
					'MorbusNephroLab_Date' //'MorbusNephroLab_id'
				]
			})
		});

		//TAG: РЕГИСТР Грид
		win.PersonDispMedicamentGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 800,
			padding: '0 0 15px 0',
			isLoaded: false,
			disableSelection: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openPersonDispMedicamentEditWindow('edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						win.deletePersonDispMedicament();
					}
				}]
			}),
			showRecordMenu: function(el, id) {
				this.recordMenu.rowIndex = id;
				this.recordMenu.showBy(el);
			},
			columns: [
				{ dataIndex: 'PersonDispMedicament_id', type: 'int', header: 'ID', key: true, hidden: true },
				{ dataIndex: 'PersonDisp_id', type: 'int', hidden: true },
				{ dataIndex: 'Drug_id', type: 'int', hidden: true },
				{ dataIndex: 'DrugMnn_id', type: 'int', hidden: true },
				{ dataIndex: 'Drug_Name', type: 'string', header: 'Назначенный медикамент', flex: 1 },
				{ dataIndex: 'Drug_Price', type: 'string', header: 'Цена', width: 85 },
				{ dataIndex: 'Drug_Count', type: 'string', header: 'Мес. курс', width: 85 },
				{ dataIndex: 'PersonDispMedicament_begDate', type: 'date', formatter: 'date("d.m.Y")', header: 'Дата начала', width: 125 },
				{ dataIndex: 'PersonDispMedicament_endDate', type: 'date', formatter: 'date("d.m.Y")', header: 'Дата окончания', width: 125 },
				{
					width: 40,
					dataIndex: 'PersonDispMedicament_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.PersonDispMedicamentGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
					}
				}
			],
			load: function() {
				var me = this;
				me.getStore().load({
					params: {
						PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
					},
					callback: function() {
						hits = {};
						me.getStore().clearFilter();
						me.getStore().filterBy(function(rec) {
							id = rec.get('PersonDispMedicament_id');
							if (hits[id]) {
								return false;
							} else {
								hits[id] = true;
								return true;
							}
						});
						win.SicknessPanel.checkContent();
					}
				});


			},
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.PersonDispMedicament_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{ name: 'PersonDispMedicament_id', type: 'int', header: 'ID', key: true },
					{ name: 'PersonDisp_id', type: 'int', hidden: true },
					{ name: 'Drug_id', type: 'int', hidden: true },
					{ name: 'DrugMnn_id', type: 'int', hidden: true },
					{ name: 'Drug_Name', type: 'string', header: 'Медикамент' },
					{ name: 'Drug_Price', type: 'string', header: 'Цена' },
					{ name: 'Drug_Count', type: 'string', header: 'Мес. курс' },
					{ name: 'PersonDispMedicament_begDate', type: 'date', dateFormat: 'd.m.Y', header: 'Дата начала' },
					{ name: 'PersonDispMedicament_endDate', type: 'date', dateFormat: 'd.m.Y', header: 'Дата оконч' }
				],
				baseParams: {
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonDisp&m=getPersonDispMedicamentList',
					reader: {
						type: 'json'

					}
				},
				listeners: {
					'load': function(store, records) {
						/*hits = {};
						store.filterBy(record => {
							const name = record.get('PersonDispMedicament_id')
							if (hits[name]) {
								return false
							} else {
								hits[name] = true
								return true
							}
						});*/
						win.SicknessPanel.checkContent();
					}
				},
				sorters: [
					'PersonDispMedicament_id'
				]
			})
		});

		win.calcPregPeriod = function () {
			//заполняю поле "срок беременности"
			var s = win.PregnancyLabGrid.getStore();
			var pdBeginDt = win.FormPanel.getForm().findField('PersonDisp_begDate').getValue();
			var period = win.FormPanel.getForm().findField('PregnancySpec_Period').getValue();
			var cnt = s.getCount();
			if (pdBeginDt && period && cnt) {
				for (var i = 0; i < cnt; i++) {
					var r = s.getAt(i);
					if (r.get('EUPS_setDate')) {
						var pregPeriod = period + Math.floor((pdBeginDt.getElapsed(r.get('EUPS_setDate')) / 1000 / 60 / 60 / 24) / 7);
						r.set('pregPeriod', pregPeriod);
						r.commit();
					}
				}
			}
		};

		win.TitleToolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			height: 40,
			border: false,
			noWrap: true,
			right: 0,
			style: 'background: transparent;',
			items: [{
				xtype: 'tbspacer',
				width: 10
			}, {
				userCls: 'button-without-frame',
				iconCls: 'panicon-print',
				itemId: 'printbutton',
				menu: Ext6.create('Ext6.menu.Menu', {
					itemId: 'printmenu',
					plain: true,
					showBy: function(cmp, pos, off) {
						var me = this;
						me._lastAlignTarget = win.queryById('printbutton');
						me._lastAlignToPos = 'tr-b';
						//~ me._lastAlignToOffsets = me.alignOffset; //для еще более точного позиционирования меню
						me.show();
						return me;
					},
					defaults: {
						padding: '0px 20px 0px 20px'
					},
					items: [{
						userCls: 'dispcard',
						text: 'Печать контрольной карты дисп.наблюдения',
						itemId: 'printDispCard',
						handler: function() {
							if(win.action == 'view'){
								var paramPersonDisp = win.FormPanel.getForm().findField('PersonDisp_id').getValue();
								printBirt({
									'Report_FileName': 'PersonDispCard.rptdesign',
									'Report_Params': '&paramPersonDisp=' + paramPersonDisp,
									'Report_Format': 'pdf'
								});
							}
							else{
								win.doSave({
									printPersonDispCard: true
								});
							}
						}
					}, {
						userCls: 'dispcard',
						text: 'Печать формы №030-4/у',
						disabled: true,
						itemId: 'print030',
						handler: function() {
							if(win.action == 'view'){
								var paramPersonDisp = win.FormPanel.getForm().findField('PersonDisp_id').getValue();
								win.print030(paramPersonDisp);
							}
							else{
								win.doSave({
									print030Card: true
								});
							}
						}
					}, {
						userCls: 'dispcard',
						itemId: 'print111',
						handler: function() {
							if(win.action=='view'){ //Если открыли на просмотр, то печатаем карту
								var paramPersonDispBirth = win.FormPanel.getForm().findField('PersonDisp_id').getValue();
								printBirt({
									'Report_FileName': 'han_ParturientCard.rptdesign',
									'Report_Params': '&paramPersonDispBirth=' + paramPersonDispBirth,
									'Report_Format': 'pdf'
								});
							}
							else{ //Иначе (если добавление/изменение) - сохраняем карту с параметром printParturientCard
								win.doSave({
									printParturientCard: true
								});
							}
						},
						hidden: true,
						disabled: true,
						text: 'Печать формы 111/у Индивидуальная карта беременной и родильницы'
					}]
				})
			}]
		});

		win.titlePanel = Ext6.create('Ext6.Panel', {
			userCls: 'DispTitle',
			region: 'north',
			layout: 'border',
			border: false,
			height: 40,
			bodyStyle: 'background-color: #EEEEEE;',
			items: [{
				region: 'center',
				border: false,
				bodyStyle: 'background-color: #EEEEEE;',
				height: 40,
				bodyPadding: '10 10 10 4',
				items: [
					Ext6.create('Ext6.form.Label', {
						xtype: 'label',
						cls: 'no-wrap-ellipsis dispWindowTitle',
						style: 'font-size: 16px; padding: 3px 10px;',
						html: 'Контрольная карта диспансерного наблюдения'
					})
				]
			}, win.TitleToolPanel
			],
			xtype: 'panel'
		});

		//Кнопка для генерации номера карты
		win.PersonDisp_NumCardAddBtn = new Ext6.create('Ext6.button.Button', {
			refId: 'PersonDisp_NumCardAddBtn',
			userCls: 'button-without-frame',
			iconCls: 'menu_dispadd',
			handler: function () {
				win.getPersonDispNumber();
			}.createDelegate(win)});

		//TAG: Основная панель
		win.PersonDispPanel = new Ext6.create('swPanel', {
			bodyPadding: '20 20 20 31',
			autoHeight: true,
			width: '100%',
			border: false,
			itemId: 'PDEF_PersonDispPanel',
			userCls: 'PersonDispPanel',
			title: null,
			collapsible: false,
			collapsed: false,
			defaults: {
				labelWidth: 190,
				width: 700,
				msgTarget: 'side'
			},
			items: [
				{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				},
				{
					name: 'Lpu_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'PersonDisp_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'MedPersonal_id',
					value: null,
					xtype: 'hidden'
				},
				{
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Label_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					xtype: 'hidden',
					name: 'Server_id',
					value: -1
				},
				{
					xtype: 'hidden',
					name: 'PersonDisp_IsTFOMS',
					value: 1
				},
				{
					xtype: 'hidden',
					name: 'PregnancySpec_id',
					value: null
				},
				{
					xtype: 'hidden',
					name: 'EvnSection_id',//идентификатор Движения в КВС, если эта ДУ вообще связана с какой-либо спецификой беременности КВС
					value: -1
				},
				{
					xtype: 'hidden',
					name: 'EvnSection_pid',//идентификатор КВС, если эта ДУ вообще связана с какой-либо спецификой беременности КВС
					value: -1
				},
				{
					name: 'PersonRegister_id',
					xtype: 'hidden'
				}, {
					layout: 'column',
					border: false,
					margin: '0 0 5 0',
					referenceHandler: true,
					reference: 'PersonDisp_NC',
					items: [{
						allowBlank: false,
						fieldLabel: langs('Номер карты'),
						labelPad: 95,
						maskRe: /\d/,
						name: 'PersonDisp_NumCard',
						width: 350,
						emptyText: 'введите номер карты',
						tabIndex: 2601,
						xtype: 'textfield',
						enableKeyEvents: true,
						listeners: {
							'keydown': function (inp, e) {
								switch (e.getKey()) {
									case Ext6.EventObject.F2:
										e.stopEvent();
										this.getPersonDispNumber();
										break;

									case Ext6.EventObject.TAB:
										if (e.shiftKey == true) {
											e.stopEvent();
											this.buttons[this.buttons.length - 1].focus();
										}
										break;

								}
							}.createDelegate(this),
							render: function(field, p, rec) {
								var afterNumCardDiv = document.createElement('div');
								afterNumCardDiv.id = 'afterNumCardDiv';
								afterNumCardDiv.innerText = '* Номер может состоять только из цифр от 0 до 999999999';
								afterNumCardDiv.setAttribute("style", "position: absolute;font-size: 0.8em;top: 3px;left: 380px;color: red; display:none;");
								field.container.dom.appendChild(afterNumCardDiv);
							},
							focus: function(field){
								var afterNumCardDiv = document.getElementById('afterNumCardDiv');
								if(afterNumCardDiv) afterNumCardDiv.style.display = 'block';
							},
							blur: function(field){
								var afterNumCardDiv = document.getElementById('afterNumCardDiv');
								if(afterNumCardDiv) afterNumCardDiv.style.display = 'none';
							}
						}
					}, win.PersonDisp_NumCardAddBtn]
				}, {
					allowBlank: false,
					fieldLabel: 'Взят',//TAG: поле "Взят"
					format: 'd.m.Y',
					invalidText: 'Неправильная дата',
					name: 'PersonDisp_begDate',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ],
					formatText: null,
					tabIndex: 2602,
					width: 330,
					userCls: 'DateInputMin',
					xtype: 'datefield',
					listeners: {
						'focus': function(field, event, eOpts) {
							setTimeout(function() {
								var pos=0;
								var s=field.getValue();
								/*if(s && s.length) {
									pos=s.indexOf('_');
									if(pos<0) pos=s.length;
								}*/
								document.getElementById(field.getInputId()).selectionStart = pos;
								document.getElementById(field.getInputId()).selectionEnd = pos;
							}, 10);
						},
						'change': function(field, newValue, oldValue) {
							if(!newValue) newValue="";
							var base_form = win.FormPanel.getForm();
							var lpu_section_id = base_form.findField('LpuSection_id').getValue();
							var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
							if (typeof newValue != 'object') {
								base_form.findField('LpuSection_id').disable();
								base_form.findField('MedStaffFact_id').disable();
								return false;
							}

							base_form.findField('LpuSection_id').clearValue();
							base_form.findField('MedStaffFact_id').clearValue();
							var Diag_id = base_form.findField('Diag_id').getValue();
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').setFilterByDate(newValue);

								},
								params: { object: "Diag", where: "where DiagLevel_id = 4 and Diag_id = " + Diag_id },
							});

							var lpu_section_filter_params = {
								allowLowLevel: 'yes',
								isPolka: true,
								regionCode: getGlobalOptions().region.number
							};
							var medstafffact_filter_params = {
								allowLowLevel: 'yes',
								isPolka: true,
								regionCode: getGlobalOptions().region.number
							};

							if (getGlobalOptions().allowed_disp_med_staff_fact_group == 2) {
								medstafffact_filter_params.isDoctorOrMidMedPersonal = true;
							}

							medstafffact_filter_params.onEndDate = Ext6.util.Format.date(newValue, 'd.m.Y');
							if (win.action == 'add') {
								// фильтр или на конкретное место работы или на список мест работы
								if (win.UserLpuSection_id && win.UserMedStaffFact_id) {
									lpu_section_filter_params.id = win.UserLpuSection_id;
									medstafffact_filter_params.id = win.UserMedStaffFact_id;
								} else if (typeof win.UserLpuSectionList == 'object' && win.UserLpuSectionList.length > 0 && typeof win.UserMedStaffFactList == 'object' && win.UserMedStaffFactList.length > 0) {
									lpu_section_filter_params.ids = win.UserLpuSectionList;
									medstafffact_filter_params.ids = win.UserMedStaffFactList;
								}
							}
							lpu_section_filter_params.isDisp = true;
							medstafffact_filter_params.isDisp = true;
							if (win.action != 'view') {
								base_form.findField('LpuSection_id').enable();
								if (win.action != 'edit') {
									base_form.findField('MedStaffFact_id').enable();
								}
							}

							base_form.findField('LpuSection_id').getStore().removeAll();
							base_form.findField('MedStaffFact_id').getStore().removeAll();

							// загружаем локальные списки отделений и мест работы
							setLpuSectionGlobalStoreFilter(lpu_section_filter_params, sw4.swLpuSectionGlobalStore);
							setMedStaffFactGlobalStoreFilter(medstafffact_filter_params, sw4.swMedStaffFactGlobalStore);
							base_form.findField('LpuSection_id').getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));
							base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));

							base_form.findField('LpuSection_id').getStore().findBy(function(rec){ if(rec.data.LpuSection_id == lpu_section_id) return rec;});

							if( base_form.findField('LpuSection_id').getStore().findBy(function(rec){ if(rec.data.LpuSection_id == lpu_section_id) return rec;}) ){
								base_form.findField('LpuSection_id').setValue(lpu_section_id);
							}
							if (base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id)) {
								base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
							}
							var that = this;
							if(that.MedPersonal_id){
								var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
									if (record.get('MedPersonal_id') == that.MedPersonal_id) {
										return true;
									} else {
										return false;
									}
								});
								if (index >= 0) {
									base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
								}
							}
							//base_form.findField('MedPersonal_id').setValue(MedPersonal_id);
							/*
							 если форма открыта на редактирование и задано отделение и
							 место работы или задан список мест работы, то не даем редактировать вообще
							 */
							if (win.action == 'edit' && ((win.UserLpuSection_id && win.UserMedStaffFact_id) || (typeof win.UserLpuSectionList == 'object' && win.UserLpuSectionList.length > 0 && typeof win.UserMedStaffFactList == 'object' && win.UserMedStaffFactList.length > 0))) {
								base_form.findField('LpuSection_id').disable();
								base_form.findField('MedStaffFact_id').disable();
							}
							// Если форма открыта на добавление...
							if (win.action == 'add') {
								// ... и задано отделение и место работы...
								if (win.UserLpuSection_id && win.UserMedStaffFact_id) {
									// ... то устанавливаем их и не даем редактировать поля
									base_form.findField('LpuSection_id').disable();
									base_form.findField('MedStaffFact_id').disable();
									base_form.findField('LpuSection_id').setValue(win.UserLpuSection_id);
									base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), win.UserLpuSection_id);
									base_form.findField('MedStaffFact_id').setValue(win.UserMedStaffFact_id);
								}
								// или задан список отделений и мест работы...
								else if (base_form.findField('MedStaffFact_id').getStore().getCount() > 0 && typeof win.UserLpuSectionList == 'object' && win.UserLpuSectionList.length > 0 && typeof win.UserMedStaffFactList == 'object' && win.UserMedStaffFactList.length > 0) {
									// ... выбираем первое место работы
									med_staff_fact_id = base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');
									base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
									base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), med_staff_fact_id);
									// Если в списке мест работы всего одна запись...
									if (win.UserMedStaffFactList.length == 1) {
										// ... закрываем поля для редактирования
										base_form.findField('LpuSection_id').disable();
										base_form.findField('MedStaffFact_id').disable();
									}
								}
								if(win.PersonDispHistGrid.getStore().data.length == 0 && base_form.findField('PersonDisp_id').getValue() == 0){
									var rec = {PersonDispHist_id:0,MedPersonal_Fio:'',LpuSection_Name:'',PersonDispHist_begDate:newValue,PersonDispHist_endDate:''};
									var msf = base_form.findField('MedStaffFact_id').getValue();
									var indxMsf = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec){ if(rec.data.MedStaffFact_id == msf) return rec;});

									if(msf && indxMsf>=0){
										//rec.MedPersonal_Fio = base_form.findField('MedStaffFact_id').getStore().getById(msf).get('MedPersonal_Fio');
										//rec.LpuSection_Name = base_form.findField('MedStaffFact_id').getStore().getById(msf).get('LpuSection_Name');


										rec.MedPersonal_Fio = base_form.findField('MedStaffFact_id').getStore().getAt(indxMsf).get('MedPersonal_Fio');
										rec.LpuSection_Name = base_form.findField('MedStaffFact_id').getStore().getAt(indxMsf).get('LpuSection_Name');

									}
									win.PersonDispHistGrid.getStore().loadData([rec]);
									//~ win.PersonDispHistGrid.getStore().each(function(el) {el.commit()});
									win.PersonDispHistPanel.checkContent();
									win.queryById('PDEF_PersonDispHistPanel').isLoaded = true;

									base_form.findField('PersonDispHist_MedPersonalFio').setValue(rec.MedPersonal_Fio);
									win.disablePersonDispHistActions(true);
								}
							}
							if(base_form.findField('PersonDisp_endDate').getValue()<newValue){
								base_form.findField('PersonDisp_endDate').setValue('');
							}
							base_form.findField('PersonDisp_endDate').setMinValue(newValue);

						}.createDelegate(win)
					}
				},
				{
					allowBlank: false,
					fieldLabel: 'Отделение',
					name: 'LpuSection_id',
					itemId: 'LpuSectionCombo',
					listWidth: 650,
					tabIndex: 2603,
					xtype: 'SwLpuSectionGlobalCombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = win.FormPanel.getForm();
							var MedStaffFactFilterParams = {
								allowLowLevel: 'yes',
								//onDate:
							};
							MedStaffFactFilterParams.LpuSection_id = newValue;
							setMedStaffFactGlobalStoreFilter(MedStaffFactFilterParams, sw4.swMedStaffFactGlobalStore);
							base_form.findField('MedStaffFact_id').setValue('');
							base_form.findField('MedStaffFact_id').getStore().removeAll();
							base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
						}
					}
				},
				{
					allowBlank: false,
					fieldLabel: langs('Поставивший врач'),
					hiddenName: 'MedStaffFact_id',
					name: 'MedStaffFact_id',
					itemId: 'PDEF_MedStaffactCombo',
					xtype: 'swMedStaffFactCombo' //'SwMedStaffFactGlobalCombo'   // 'swMedStaffFactCombo'
				},
				{
					xtype: 'textfield',
					name: 'PersonDispHist_MedPersonalFio',
					fieldLabel: langs('Ответственный врач'),
					readOnly: true
				},
				{
					layout: 'column',
					border: false,
					width: '100%',
					style: 'padding-bottom: 6px;',
					items: [
						win.DiagCombo = Ext6.create('swDiagCombo',{
							allowBlank: false,
							labelWidth: 190,
							width: 700,
							hiddenName: 'Diag_id',
							name: 'Diag_id',//TAG:комбо Диагноз
							userCls:'diagnoz',
							valueField: 'Diag_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var action = win.action;
									var do_some_things = function (combo, newValue, oldValue, load_started){
										if (!load_started) {
											load_started = false;
										}
										if (0==win.sicknessDiagStore.data.length) {
											//log('store not loaded, shedulled');
											if (!win.sicknessDiagStore.autoLoad && !load_started) {
												win.sicknessDiagStore.load();
												load_started = true;
											}
											setTimeout(function (){
												do_some_things(combo, newValue, oldValue, load_started);
											}, 100);
											return;
										}
										var base_form = win.FormPanel.getForm();
										var sickness_diag_store = win.sicknessDiagStore;
										var sickness_id = null;
										var needToShowSicknessPanel = false;
										var needToShowPregnantPanel =false;
										var idx = -1;
										//определяю необходимость показа панели регистр
										if (newValue != '') {
											//находим диагноз
											idx = sickness_diag_store.findBy(function(record) {
												if (record.get('Diag_id') == newValue) {
													//заодно определяем заболевание
													sickness_id = record.get('Sickness_id');
													return true;
												}
											});
										}
										if ((idx>=0) && (sickness_id != null)) {
											//запись найдена
											if (Ext6.isEmpty(base_form.findField('Sickness_id').getValue())) {
												base_form.findField('Sickness_id').setValue(sickness_diag_store.getAt(idx).get('Sickness_id'));
											}

											switch (sickness_id.toString()){
												//Регистр надо показать для
												case '1'://1 ГЕМОФИЛИЯ
												case '2'://2 ОНКОГЕМАТОЛОГИЯ
												case '3'://3 РАССЕЯННЫЙ СКЛЕРОЗ
												case '4'://4 МУКОВИСЦИДОЗ
												case '5'://5 ГИПОФИЗАРНЫЙ НАНИЗМ
												case '6'://6 БОЛЕЗНЬ ГОШЕ
												case '7'://7 МИЕЛОЛЕЙКОЗ
												case '8'://8 ТРАНСПЛАНТАЦИИ ОРГАНОВ (ТКАНЕЙ)
													win.PregnancyHistoryPanel.hide();
													needToShowSicknessPanel = true;
													break;
												// беременность надо показать для
												case '9'://9 БЕРЕМЕННОСТЬ И РОДЫ
													if(action == 'edit'){
														win.PregnancyHistoryPanel.show();
													}
													needToShowPregnantPanel = true;
													break;
												default:
													break;
											}
										} else {
											win.PregnancyHistoryPanel.hide();
											base_form.findField('Sickness_id').clearValue();
										}

										if (needToShowSicknessPanel) {
											win.queryById('PDEF_SicknessPanel').show();
											win.PersonDispMedicamentGrid.load();
											/*win.PersonDispMedicamentGrid.getStore().load({
												params: {
													PersonDisp_id: base_form.findField('PersonDisp_id').getValue()
												}
											});*/
											win.SicknessPanel.checkContent();
											win.queryById('PDEF_SicknessPanel').updateLayout();
										} else {
											win.queryById('PDEF_SicknessPanel').hide();
											//~ win.PersonDispMedicamentGrid.getStore().removeAll(true);
										}
										if (needToShowPregnantPanel) {
											win.showPregnancyPanels(true);
										} else {
											win.showPregnancyPanels(false);
										}
										//log('needToShowPregnantPanel', needToShowPregnantPanel);
										//log('<-change');
									};
									do_some_things(combo, newValue, oldValue);
									win.MorbusNephroPanel.onChangeDiag(combo, newValue);
									win.enablePrint030();
									if(win.FormPanel.getForm().findField('Sickness_id').getValue() == 9) {
										win.queryById('print111').enable();
										win.queryById('print111').show();
									} else {
										win.queryById('print111').disable();
										win.queryById('print111').hide();
									}
								}.createDelegate(win)
							},
						//	listWidth: 600,
							tabIndex: 2605
						}),
						{
							html: '<div style="text-align:left;padding-left:30px;">Данные пациента уже отправлены в ТФОМС, редактирование недоступно</div>',
							width: 310,
							xtype: 'label',
							itemId: 'Diag_id_TFOMS_msg',
							hidden: true
						}
					]
				},
				{
					fieldLabel: 'Дата установления диагноза',
					format: 'd.m.Y',
					allowBlank: false,
					name: 'PersonDisp_DiagDate',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ],
					tabIndex: 2606,
					//~ labelWidth: 190,
					width: 330,//=190label+ 110
					xtype: 'datefield',
					userCls: 'DateInputMin',
					formatText: null,
					invalidText: 'Неправильная дата',
					listeners: {
						'focus': function(field, event, eOpts) {
							setTimeout(function() {
								var pos=0;
								var s=field.getValue();
								/*if(s && s.length>0) {
									pos=s.indexOf('_');
									if(pos<0) pos=s.length;
								}*/
								document.getElementById(field.getInputId()).selectionStart = pos;
								document.getElementById(field.getInputId()).selectionEnd = pos;
							}, 10);
						}
					}
				},/*{
					layout: 'column',
					border: false,
					style: 'padding-bottom: 6px;',
					items: [
						{
							fieldLabel: 'Дата установления диагноза',
							format: 'd.m.Y',
							allowBlank: false,
							name: 'PersonDisp_DiagDate',
							plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
							tabIndex: 2606,
							labelWidth: 190,
							width: 330,
							xtype: 'datefield',
							userCls: 'DateInputMin',
							formatText: null,
							invalidText: 'Неправильная дата',
							listeners: {
								'focus': function (field, event, eOpts) {
									setTimeout(function () {
										var pos = 0;
										var s = field.getValue();
										if (s && s.length>0) {
											pos = s.indexOf('_');
											if (pos < 0) pos = s.length;
										}
										document.getElementById(field.getInputId()).selectionStart = pos;
										document.getElementById(field.getInputId()).selectionEnd = pos;
									}, 10);
								},
							}
						}, {
							comboSubject: 'DeseaseDispType',
							labelWidth: 130,
							width: 370,
							style: 'padding-left: 20px;',
							fieldLabel: 'Диагноз установлен',
							hiddenName: 'DeseaseDispType_id',
							name: 'DeseaseDispType_id',
							allowBlank: true,
							//~ labelWidth: 140,
							//~ width: 395,
							xtype: 'commonSprCombo'
						}]
				},*/ {
					comboSubject: 'DeseaseDispType',
					/*labelWidth: 130,
					width: 370,*/
					fieldLabel: 'Диагноз установлен',
					hiddenName: 'DeseaseDispType_id',
					name: 'DeseaseDispType_id',
					allowBlank: true,
					//~ labelWidth: 140,
					//~ width: 395,
					xtype: 'commonSprCombo'
				}, {
							comboSubject: 'DiagDetectType',
							fieldLabel: 'Заболевание выявлено',
							hiddenName: 'DiagDetectType_id',
							name: 'DiagDetectType_id',
							allowBlank: getRegionNick() != 'kz',
							xtype: 'commonSprCombo'
						},{
							comboSubject: 'DispGroup',
							fieldLabel: 'Диспансерная группа',
							hiddenName: 'DispGroup_id',
							name: 'DispGroup_id',
							hidden: getRegionNick() != 'kz',
							hideLabel: getRegionNick() != 'kz',
							allowBlank: getRegionNick() != 'kz',
							xtype: 'commonSprCombo'
						},{
					layout: 'column',
					border: false,
					items: [
					{
						enableKeyEvents: true,
						fieldLabel: 'Снят',
						format: 'd.m.Y',
						msgTarget: 'side',
						listeners: {
							'focus': function(field, event, eOpts) {
								setTimeout(function() {
									var pos=0;
									var s=field.getValue();
									/*if(s && s.length>0) {

										pos=s.indexOf('_');
										if(pos<0) pos=s.length;
									}*/
									document.getElementById(field.getInputId()).selectionStart = pos;
									document.getElementById(field.getInputId()).selectionEnd = pos;
								}, 10);
							},
							'change': function(inp, newValue, oldValue) {
								var base_form = win.FormPanel.getForm();
								var dispOutTypeCombo=base_form.findField('DispOutType_id');
								if (typeof newValue == 'object' && newValue !== null) {
									if (win.action != 'view') {
										dispOutTypeCombo.enable();
										dispOutTypeCombo.setAllowBlank(false);
										dispOutTypeCombo.getStore().filter(
											function(record){
											  return record.get("DispOutType_id")!=8;
											}
										);
									}
									win.disablePersonDispHistActions(true);
								} else {
									dispOutTypeCombo.clearValue();
									dispOutTypeCombo.disable();
									dispOutTypeCombo.setAllowBlank(true);
									win.disablePersonDispHistActions(false);
								}
								if(win.action == 'add' && win.PersonDispHistGrid.getStore().data.length == 1 && base_form.findField('PersonDisp_id').getValue() == 0){
									var rec = win.PersonDispHistGrid.getStore().getAt(0);
									rec.set('PersonDispHist_endDate',newValue);
									rec.commit();
									win.disablePersonDispHistActions(true);
								}
							}.createDelegate(win)
						},
						name: 'PersonDisp_endDate',
						plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ],
						tabIndex: 2607,
						labelWidth: 190,
						width: 330,
						xtype: 'datefield',
						userCls: 'DateInputMin',
						invalidText: 'Неправильная дата'
					},
					{
						labelWidth: 115,
						width: 370,
						style: 'padding-left: 20px;',
						comboSubject: 'DispOutType',
						fieldLabel: 'Причина снятия',
						//labelWidth: 180,
						hiddenName: 'DispOutType_id',
						name: 'DispOutType_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = win.FormPanel.getForm();
								if (win.action == 'view') {
									return false;
								}
							}.createDelegate(win)
						},
						tabIndex: 2608,
						xtype: 'commonSprCombo'
					}]
				}
			]
		});

		//Сборка гармошки
		win.FormPanel = new Ext6.form.FormPanel({
			layout: 'vbox',
			userCls: 'emk_forms',
			scrollable: true,
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: false,
			itemId: 'PersonDispEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			items: [
				win.PersonDispPanel,
				{
					layout: {
						type: 'accordion',
						titleCollapse: false,
						animate: true,
						multi: true,
						activeOnTop: false,
						border: true
					},
					defaults: {
						margin: "0px 0px 2px 0px",
						bodyPadding: '20 20 20 20',
						width: '100%',
						border: false
					},
					scrollable: true,
					width: '100%',
					border: false,
					items: [

						//TAG: Панель История врачей
						win.PersonDispHistPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'ИСТОРИЯ ВРАЧЕЙ, ОТВЕТСТВЕННЫХ ЗА НАБЛЮДЕНИЕ',
							itemId: 'PDEF_PersonDispHistPanel',
							cls: 'DispHistNull',
							plusButton: isUserGroup('PersonDispHistEdit')
								? function() { win.openPersonDispHistEditWindow('add'); } : null,
							items: [ win.PersonDispHistGrid
							]
						}) ,
						//TAG: Панель сопут.диагнозов
						win.DiagPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'СОПУТСТВУЮЩИЕ ДИАГНОЗЫ',
							itemId: 'PDEF_DiagPanel',
							plusButton: function() { win.openPersonDispSopDiagEditWindow('add')},
							//~ plusTooltip: 'Добавить',
							items: [
								win.PersonDispSopDiagGrid
							]
						}),
						//TAG: Панель Льгот
						win.PersonPrivilegePanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'ЛЬГОТЫ',
							itemId: 'PDEF_PersonPrivilegePanel',
							plusButton: function() { win.openPersonPrivilegeEditWindow('add')},
							//~ plusTooltip: 'Добавить',
							items: [win.PersonPrivilegeGrid
							]
						}),
						//TAG: Панель Контроль посещений
						win.PersonDispVizitPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'КОНТРОЛЬ ПОСЕЩЕНИЙ',
							itemId: 'PDEF_PersonDispVizitPanel',
							plusButton: function() {win.openPersonDispVizitEditWindow('add')},
							//~ plusTooltip: 'Добавить',
							items: [win.PersonDispVizitGrid
							]
						}),
						win.EvnPLDispProfPanel = new Ext6.create('PersonDispDefaultPanel',{
							title: 'ПРОФИЛАКТИЧЕСКИЕ ОСМОТРЫ',
							itemId: 'PDEF_EvnPLDispProfPanel',
							plusButton: function() {win.openEvnPLDispProfEditWindow('add')},
							items: [win.EvnPLDispProfGrid
							]
						}),
						win.PersonDispTargetRatePanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'ЦЕЛЕВЫЕ ПОКАЗАТЕЛИ',
							itemId: 'PDEF_PersonDispTargetRatePanel',
							listeners: {
								'expand': function(panel) {
									var base_form = win.FormPanel.getForm();
									if (panel.isLoaded === false) {
										if (base_form.findField('PersonDisp_id').getValue() != '0') {
											panel.isLoaded = true;
											win.PersonDispTargetRateGrid.getStore().load({
												params: {
													PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
												}
											});
										} else {
											panel.collapse();
											win.doSave({
												doNotHide:true,
												callback: function(data){
													panel.expand();
													panel.isLoaded = true;
													win.PersonDispTargetRateGrid.getStore().load({
														params: {
															PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
														}
													});
												}
											});
										}

									}
									panel.updateLayout();
								}.createDelegate(win)
							},
							items: [
								win.PersonDispTargetRateGrid
							]
						}),
						//TAG: РЕГИСТР по заболеваниям
						//win.SicknessPanel = new Ext6.create('PersonDispDefaultPanel', {
						win.SicknessPanel = new Ext6.create('swPanel', {
							title: 'РЕГИСТР ПО ЗАБОЛЕВАНИЯМ',
							itemId: 'PDEF_SicknessPanel',
							hidden: true,
							collapsed: true,
							checkContent: function() {
								if(win.PersonDispMedicamentGrid.getStore().getCount()>0) {
									win.PersonDispMedicamentGrid.show();
								} else win.PersonDispMedicamentGrid.hide();
							},
							items: [
								{
									codeField: 'Sickness_Code',
									displayField: 'Sickness_Name',
									padding: '0 0 15px 11px',
									disabled: true,
									editable: true,
									fieldLabel: 'Заболевание',
									name: 'Sickness_id',
									comboSubject: 'Sickness',
									fields: [
											{ name: 'Sickness_id', type: 'int' },
											{ name: 'PrivilegeType_id', type: 'int' },
											{ name: 'Sickness_Code', type: 'int' },
											{ name: 'Sickness_Name', type: 'string' }
										],
									tabIndex: 2612,
									tpl: '<tpl for="."><div class="x-combo-list-item">' + '<font color="red">{Sickness_Code}</font>&nbsp;{Sickness_Name}' + '</div></tpl>',
									valueField: 'Sickness_id',
									width: 500,
									xtype: 'commonSprCombo'
								},
								win.PersonDispMedicamentGrid
								,{
									xtype: 'label',
									padding: '0 0 0 11px',
									html: "<a href='#' onclick='Ext6.getCmp(\""+win.id+"\").openPersonDispMedicamentEditWindow(\"add\");'>Добавить медикамент</a> "
								}
							]
						}),
						//TAG: НЕФРОЛОГИЯ
						win.MorbusNephroPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'СПЕЦИФИКА (НЕФРОЛОГИЯ)',
							itemId: 'PDEF_MorbusNephroPanel',
							userCls: 'morbus-nephro-panel-accordion',
							bodyPadding: '20 20 20 37',
							hidden: true,
							defaults: {
								labelWidth: 340,
								width: 700,
								defaults: {
									labelWidth: 340
								}
							},
							items: [
							{
								name: 'MorbusNephro_id',
								xtype: 'hidden'
							}, {
								name: 'PersonHeight_id',
								xtype: 'hidden'
							}, {
								name: 'PersonWeight_id',
								xtype: 'hidden'
							}, {
								fieldLabel: 'Давность заболевания до установления диагноза',
								width: 140+340,
								name: 'MorbusNephro_firstDate',
								xtype: 'datefield',
								formatText: null,
								invalidText: 'Неправильная дата',
								plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
								listeners: {
									'focus': function(field, event, eOpts) {
										setTimeout(function() {
											var pos=0;
											var s=field.getValue();
											/*if(s && s.length>0) {
												pos=s.indexOf('_');
												if(pos<0) pos=s.length;
											}*/
											document.getElementById(field.getInputId()).selectionStart = pos;
											document.getElementById(field.getInputId()).selectionEnd = pos;
										}, 10);
									}
								}
							}, {
								fieldLabel: 'Способ установления диагноза',
								anchor:'100%',
								name: 'NephroDiagConfType_id',
								xtype: 'commonSprCombo',
								sortField:'NephroDiagConfType_Code',
								comboSubject: 'NephroDiagConfType'
							}, {
								fieldLabel: 'Стадия ХБП',
								anchor:'100%',
								name: 'NephroCRIType_id',
								xtype: 'commonSprCombo',
								sortField:'NephroCRIType_Code',
								comboSubject: 'NephroCRIType'
							}, {
								boxLabel: 'Артериальная гипертензия',
								padding: '0px 0px 0px 345px',
								width: 300,
								xtype: 'checkbox',
								name: 'MorbusNephro_IsHyperten',
								itemId: 'MorbusNephro_IsHyperten',
								inputValue: '2',
								uncheckedValue: '1'
							}, {
								layout: 'column',
								border: false,
								width: 1000,
								labelWidth: 0,
								padding: '0px 0px 5px 0px',
								items: [{
									fieldLabel: 'Рост',
									name: 'PersonHeight_Height',
									width: 70+340+5,
									xtype: 'numberfield',
									allowNegative: false,
									allowDecimals: false,
									decimalPrecision: 0,
									regex:new RegExp('(^[0-9]{0,3})$'),
									maxValue: 999,
									maxLength: 3,
									maxLengthText: 'Максимальная длина этого поля 3 символа'
								}, {
									xtype: 'label',
									padding: '3 20 0 8',
									html: 'см'
								}, {
									fieldLabel: 'Вес',
									labelAlign: 'right',
									name: 'PersonWeight_Weight',
									labelWidth: 50,
									width: 70+50+5,
									xtype: 'numberfield',
									allowNegative: false,
									allowDecimals: false,
									decimalPrecision: 0,
									regex:new RegExp('(^[0-9]{0,3})$'),
									maxValue: 999,
									maxLength: 3,
									maxLengthText: 'Максимальная длина этого поля 3 символа'
								}, {
									xtype: 'label',
									padding: '3 0 0 8',
									html: 'кг'
								}]
							}, {
								fieldLabel: 'Назначенное лечение (диета, препараты)',
								name: 'MorbusNephro_Treatment',
								anchor:'100%',
								maxLength: 100,
								maxLengthText: 'Максимальная длина этого поля 100 символов',
								xtype: 'textfield'
							}, {
								xtype: 'label',
								id: 'PDEF_NotifyNephroButton',
								padding: '8px 0px 0px 345px',
								html: "<a href='#' onclick='Ext6.getCmp(\"PDEF_NotifyNephroButton\").AddEvnNotifyNephro(this);'>Создать извещение по нефрологии</a> ",
								AddEvnNotifyNephro: function() {
									var sp = win.MorbusNephroPanel;
									var PersonDisp_id = win.FormPanel.getForm().findField('PersonDisp_id').getValue();
									if(PersonDisp_id > 0){
										var params = {
													EvnNotifyNephro_id: null
													,formParams: {
														EvnNotifyNephro_id: null
														,EvnNotifyNephro_pid: null
														,Morbus_id: sp.fieldMorbusId.getValue()
														,Server_id: sp.personInfoPanel.getFieldValue('Server_id')
														,PersonEvn_id: sp.personInfoPanel.getFieldValue('PersonEvn_id')
														,Person_id: sp.personInfoPanel.getFieldValue('Person_id')
														,Diag_Name: sp.fieldDiagId.getRawValue()
														,EvnNotifyNephro_setDate: getGlobalOptions().date
														,MedPersonal_id: getGlobalOptions().medpersonal_id
														,fromDispCard: true
													}
													,callback: function (data) {
														win.queryById('PDEF_NotifyNephroButton').hide();
														win.queryById('PDEF_NotifyNephroButtonView').load();
													}
												};
										getWnd('swEvnNotifyNephroEditWindowExt6').show(params);
									} else
									{
										win.doSave({
											doNotHide:true,
											callback:function(){
												Ext6.getCmp('PDEF_NotifyNephroButton').AddEvnNotifyNephro();
											}
										});
									}
								}
							}, {
								xtype: 'label',
								id: 'PDEF_NotifyNephroButtonView',
								padding: '8px 0px 0px 345px',
								tpl: new Ext6.XTemplate(
									'{dateNotify} ',
									'<a href="#" onclick="Ext6.getCmp(\'PDEF_NotifyNephroButtonView\').ViewEvnNotifyNephro(this);">Извещение по нефрологии</a>',
									' / {[this.formatFio(values.MedPersonalFio)]} / {[this.formatFio(values.MedPersonalZavFio)]}',
									{
										formatFio: function(fio) {
											var f_io = fio.split(' ');
											var surname = '', io = '';
											if(f_io.length == 2) {
												surname = f_io[0];
												surname = surname.slice(0,1).toUpperCase() + surname.slice(1).toLowerCase();
												io = f_io[1];
												io=io.split('').join('.')+'.';
											}
											return surname+' '+io;
										}
									}
								),
								load: function() {
									var np = win.MorbusNephroPanel;
									Ext6.Ajax.request({
										callback: function(options, success, response) {
											if (success){
												var response_obj = Ext6.util.JSON.decode(response.responseText);
												if (response_obj.MorbusNephro_id){
													if(response_obj.EvnNotifyNephro_id || response_obj.PersonRegister_id) {
														win.EvnNotifyNephro_id = response_obj.EvnNotifyNephro_id;
														np.queryById('PDEF_NotifyNephroButton').hide();
														if(response_obj.dateNotify && response_obj.MedPersonalFio && response_obj.MedPersonalZavFio) {
															var html = np.queryById('PDEF_NotifyNephroButtonView').tpl.apply(response_obj);
															np.queryById('PDEF_NotifyNephroButtonView').setHtml(html);

															np.queryById('PDEF_NotifyNephroButtonView').show();
														}
													} else {
														np.queryById('PDEF_NotifyNephroButton').show();
														np.queryById('PDEF_NotifyNephroButtonView').hide();
													}
												}
											}
										},
										params: {
											Diag_id: win.FormPanel.getForm().findField('Diag_id').getValue(),
											Person_id: np.fieldPersonId.getValue()
										},
										url: '/?c=MorbusNephro&m=checkByPersonDispForm'
									});
								},
								ViewEvnNotifyNephro: function() {
									getWnd('swEvnNotifyNephroEditWindowExt6').show({
										action: 'view',
										EvnNotifyNephro_id: win.EvnNotifyNephro_id,
										formParams: {
											EvnNotifyNephro_id: win.EvnNotifyNephro_id
										}
									});
								}
							}
							],
							//TAG: НЕФРОЛОГИЯ настройки
							loaded: false,
							inited: false,
							onShowWindow: function() {
								//~ win.showNephroPanels(false);
								var np = win.MorbusNephroPanel;
								np.loaded = false;
								np.inited = false;

								np.personInfoPanel = win.PersonInfoPanel;
								var base_form = win.FormPanel.getForm();
								np.baseForm = base_form;
								np.fieldDiagId = base_form.findField('Diag_id');
								np.fieldPersonId = base_form.findField('Person_id');
								np.fieldMorbusNephroId = base_form.findField('MorbusNephro_id');
								np.fieldMorbusId = base_form.findField('Morbus_id');
								np.fieldFirstDate = base_form.findField('MorbusNephro_firstDate');
								np.comboNephroDiagConfType = base_form.findField('NephroDiagConfType_id');
								np.comboNephroCRIType = base_form.findField('NephroCRIType_id');
								np.comboIsHyperten = base_form.findField('MorbusNephro_IsHyperten');
								np.fieldPersonHeight = base_form.findField('PersonHeight_Height');
								np.fieldPersonWeight = base_form.findField('PersonWeight_Weight');
								np.fieldPersonHeightId = base_form.findField('PersonHeight_id');
								np.fieldPersonWeightId = base_form.findField('PersonWeight_id');
								np.fieldTreatment = base_form.findField('MorbusNephro_Treatment');
								win.MorbusNephroLabGrid.getStore().baseParams.isOnlyLast = 0;
								np.inited = true;
								np.accessType = 'view';
								win.MorbusNephroLabGrid.getStore().removeAll();
							},
							onChangeDiag: function(combo, newValue) {
								if(!newValue) return;
								var sp = this;
								var np = win.MorbusNephroPanel;
								np.fieldMorbusId.setValue(null);
								if (sp.inited && getRegionNick().inlist([ 'perm', 'ufa' ])) {
									Ext6.Ajax.request({
										callback: function(options, success, response) {
											if (success){
												var response_obj = Ext6.util.JSON.decode(response.responseText);
												if (response_obj.Morbus_id){
													np.fieldMorbusId.setValue(response_obj.Morbus_id);
												}
												if (response_obj.MorbusNephro_id){
													np.fieldMorbusNephroId.setValue(response_obj.MorbusNephro_id);
													win.showNephroPanels(true);
													if(response_obj.EvnNotifyNephro_id || response_obj.PersonRegister_id) {
														win.EvnNotifyNephro_id = response_obj.EvnNotifyNephro_id;
														np.queryById('PDEF_NotifyNephroButton').hide();
														if(response_obj.dateNotify && response_obj.MedPersonalFio && response_obj.MedPersonalZavFio) {
															var html = np.queryById('PDEF_NotifyNephroButtonView').tpl.apply(response_obj);
															np.queryById('PDEF_NotifyNephroButtonView').setHtml(html);

															np.queryById('PDEF_NotifyNephroButtonView').show();
														}
													} else {
														np.queryById('PDEF_NotifyNephroButton').show();
														np.queryById('PDEF_NotifyNephroButtonView').hide();
													}
												} else {
													win.showNephroPanels(false);
													np.fieldMorbusNephroId.setValue(null);
												}
											}
										},
										params: {
											Diag_id: newValue,
											Person_id: np.fieldPersonId.getValue()
										},
										url: '/?c=MorbusNephro&m=checkByPersonDispForm'
									});
								}
							},
							save: function (callback) {
								var np = win.MorbusNephroPanel;
								var dataToSave = {
									Diag_id: np.fieldDiagId.getValue(),
									Person_id: np.fieldPersonId.getValue(),
									MorbusNephro_id: np.fieldMorbusNephroId.getValue() || null,
									Morbus_id: np.fieldMorbusId.getValue() || null,
									MorbusNephro_firstDate: np.fieldFirstDate.getRawValue() || null,
									NephroDiagConfType_id: np.comboNephroDiagConfType.getValue() || null,
									NephroCRIType_id: np.comboNephroCRIType.getValue() || null,
									MorbusNephro_IsHyperten: np.comboIsHyperten.getValue() ? '2' : '1',
									PersonHeight_Height: np.fieldPersonHeight.getValue() || null,
									PersonWeight_Weight: np.fieldPersonWeight.getValue() || null,
									PersonHeight_id: np.fieldPersonHeightId.getValue() || null,
									PersonWeight_id: np.fieldPersonWeightId.getValue() || null,
									MorbusNephro_Treatment: np.fieldTreatment.getValue() || null
								};
								var lab_grid = win.MorbusNephroLabGrid;
								lab_grid.getStore().clearFilter();
								if (lab_grid.getStore().getCount() > 0) {
									var lab_data = sw4.getStoreRecords(lab_grid.getStore(), { convertDateFields: true });
									lab_grid.getStore().filterBy(function(rec) {
										return Number(rec.get('RecordStatus_Code')) != 3;
									});
									dataToSave.MorbusNephroLabList = Ext6.util.JSON.encode(lab_data);
								}
								Ext6.Ajax.request({
									callback: function(options, success, response) {
										if (success){
											var response_obj = Ext.util.JSON.decode(response.responseText);
											if (response_obj.success){
												np.fieldMorbusId.setValue(response_obj.Morbus_id);
												np.fieldMorbusNephroId.setValue(response_obj.MorbusNephro_id);
												callback();
												//~ var actionSetOnlyLast = np.viewFrameMorbusNephroLab.getAction('action_setOnlyLast');
												//~ actionSetOnlyLast.setDisabled(false);
											} else {
												Ext6.Msg.alert('Ошибка', response_obj.Error_Msg || 'Ошибка сохранения');
												callback();
											}
										} else {
											Ext6.Msg.alert('Ошибка', 'Ошибка вызова сохранения');
											callback();
										}
									},
									params: dataToSave,
									url: '/?c=MorbusNephro&m=doSavePersonDispForm'
								});
							},
							setEnabled: function(enable){
								var np = win.MorbusNephroPanel;
								if (np.inited) {
									np.fieldFirstDate.setDisabled(!enable);
									np.comboNephroDiagConfType.setDisabled(!enable);
									np.comboNephroCRIType.setDisabled(!enable);
									np.comboIsHyperten.setDisabled(!enable);
									np.fieldPersonHeight.setDisabled(!enable);
									np.fieldPersonWeight.setDisabled(!enable);
									np.fieldTreatment.setDisabled(!enable);
									//~ np.viewFrameMorbusNephroLab.setReadOnly(!enable);
								}
							},
							load: function() {
								var sp = win.MorbusNephroPanel;
								sp.collapse();
								win.MorbusNephroLabPanel.collapse();
								Ext6.Ajax.request({
									method: 'post',
									callback: function(options, success, response) {
										var resp = Ext.util.JSON.decode(response.responseText);
										if (resp[0] && resp[0].Morbus_id) {
											sp.fieldFirstDate.setValue(resp[0].MorbusNephro_firstDate);
											sp.comboNephroDiagConfType.setValue(resp[0].NephroDiagConfType_id);
											sp.comboNephroCRIType.setValue(resp[0].NephroCRIType_id);
											sp.comboIsHyperten.setValue(resp[0].MorbusNephro_IsHyperten == '2');
											sp.fieldPersonHeight.setValue(resp[0].PersonHeight_Height);
											sp.fieldPersonWeight.setValue(resp[0].PersonWeight_Weight);
											sp.fieldPersonHeightId.setValue(resp[0].PersonHeight_id);
											sp.fieldPersonWeightId.setValue(resp[0].PersonWeight_id);
											sp.fieldTreatment.setValue(resp[0].MorbusNephro_Treatment);
											win.MorbusNephroLabGrid.getStore().removeAll();
											win.loadGridData({
												globalFilters: {
													MorbusNephro_id: sp.fieldMorbusNephroId.getValue()
												},
												noFocusOnLoad: true
											}, win.MorbusNephroLabGrid);
											sp.accessType = resp[0].accessType;
											//~ sp.setEnabled(win.action != 'view' && sp.accessType != 0);
											//~ sp.panel.expand();
											sp.loaded = true;
										} else {
											sp.hide();
										}
									},
									params: {
										Morbus_id: sp.fieldMorbusId.getValue()
									},
									url: '/?c=MorbusNephro&m=doLoadEditFormMorbusNephro'
								});
							},
							doShow: function(visible) {
								if(visible) {
									if (!this.loaded) {
										this.load();
									} else {
										this.setEnabled(win.action != 'view' && this.accessType != 0);
									}
									win.MorbusNephroPanel.show();
								}
								else win.MorbusNephroPanel.hide();
							}
						}),
						win.MorbusNephroLabPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'ЛАБОРАТОРНЫЕ ИССЛЕДОВАНИЯ',
							itemId: 'PDEF_MorbusNephroLabPanel',
							plusButton: function() {win.openNephroLabEditWindow('add')},
							hidden: true,
							items: [
								win.MorbusNephroLabGrid
							]
						}),
						//TAG: БЕРЕМЕННОСТЬ панель
						win.PregnancyPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'БЕРЕМЕННОСТЬ И РОДЫ',
							itemId: 'PDEF_PregnancyPanel',
							hidden: true,
							bodyPadding: '20 20 20 37',
							defaults: {
								border: false,
								labelWidth: 255,

								//width: 700
							},
							items: [
								{
									name: 'PregnancySpec_id',
									value: 0,
									xtype: 'hidden'
								},
								//Lpu_aid – Наблюдалась в другом ЛПУ;   выбор из списка карт ДУ с диагнозами из группы «Беременность и роды»; отображаемые данные в списке карт: диагноз (код, наименование), ЛПУ (где стоит/состояла на учете), Дата постановки - Дата снятия (если есть); при выборе заполнить данные по специфике из выбранной карты ДУ, включая посещения, исследования, диагнозы.
								{
									name: 'Lpu_aid',
									xtype: 'hidden',
									value: null
								},
								//PregnancySpec_Period – Срок беременности при взятии на учет (нед); целое, при переносе из пред-й карты ДУ пересчитать по новой дате постановки на учет, обязательное
								{
									layout: 'column',
									items: [{
										allowDecimals: false,
										allowNegative: false,
										fieldLabel: 'Срок беременности при взятии на учет',
										maxValue: 50,
										minValue: 0,
										name: 'PregnancySpec_Period',
										width: 330,
										labelWidth: 255,
										xtype: 'numberfield'
									},
									{
										xtype: 'label',
										html: ' недель',
										style: 'padding: 8px 15px;',
									}
									]
								},
								//PregnancySpec_Count – Которая беременность; целое, обязательное
								{
									layout: 'column',
									margin: '4 0',
									items: [{
										allowDecimals: false,
										allowNegative: false,
										fieldLabel: 'Номер беременности',
										maxValue: 99,
										minValue: 1,
										name: 'PregnancySpec_Count',
										width: 330,
										labelWidth: 255,
										xtype: 'numberfield'
									}, {//PregnancySpec_CountBirth – Из них закончились родами; целое, обязательное
										allowDecimals: false,
										allowNegative: false,
										fieldLabel: 'Из них родов',
										maxValue: 10,
										minValue: 0,
										name: 'PregnancySpec_CountBirth',
										width: 165,
										margin: '0 0 0 15',
										xtype: 'numberfield'
									}, {//PregnancySpec_CountAbort – Из них закончились абортами; целое, обязательное
										allowDecimals: false,
										allowNegative: false,
										fieldLabel: 'Из них абортов',
										maxValue: 10,
										minValue: 0,
										name: 'PregnancySpec_CountAbort',
										width: 175,
										margin: '0 0 0 15',
										xtype: 'numberfield'
									}]
								},
								{
									layout: 'column',
									margin: '4 0',
									items: [{//PregnancySpec_BirthDT – Предполагаемая дата; обязательное
										fieldLabel: 'Предполагаемая дата',
										format: 'd.m.Y',
										name: 'PregnancySpec_BirthDT',
										plugins: [
											new Ext6.ux.InputTextMask('99.99.9999', false)
										],
										width: 370,
										labelWidth: 255,
										xtype: 'datefield',
										formatText: null,
										invalidText: 'Неправильная дата',
										listeners: {
											'focus': function(field, event, eOpts) {
												setTimeout(function() {
													var pos=0;
													var s=field.getValue();
													/*if(s && s.length) {
														pos=s.indexOf('_');
														if(pos<0) pos=s.length;
													}*/
													document.getElementById(field.getInputId()).selectionStart = pos;
													document.getElementById(field.getInputId()).selectionEnd = pos;
												}, 10);
											}
										}
									}, {//BirthResult_id – Исход беременности; заполняется автоматически из Характера родов в «КВС.Специфика. Сведения о родах», привязаной к карте ДУ, и не доступно
										comboSubject: 'BirthResult',
										fieldLabel: 'Исход беременности',
										hiddenName: 'BirthResult_id',
										name: 'BirthResult_id',
										listWidth: 300,
										//disabled: true,
										width: 310,
										labelWidth: 135,
										margin: '0 0 0 20',
										xtype: 'commonSprCombo'
									}
									]
								},
								{
									layout: 'column',
									margin: '4 0',
									items: [
										{//PregnancySpec_OutcomDT – Дата исхода; автоматически по дате родов из «КВС.Специфика. Сведения о родах» и не доступно
											format: 'd.m.Y',
											fieldLabel: 'Дата исхода',
											//disabled: true,
											name: 'PregnancySpec_OutcomDT',
											plugins: [
												new Ext6.ux.InputTextMask('99.99.9999', false)
											],
											//width: 100,
											xtype: 'datefield',
											formatText: null,
											invalidText: 'Неправильная дата',
											labelWidth: 255,
											width: 370,
											listeners: {
												'focus': function(field, event, eOpts) {
													setTimeout(function() {
														var pos=0;
														/*var s=field.getValue();
														if(s && s.length) {
															pos=s.indexOf('_');
															if(pos<0) pos=s.length;
														}*/
														document.getElementById(field.getInputId()).selectionStart = pos;
														document.getElementById(field.getInputId()).selectionEnd = pos;
													}, 10);
												}
											}
										},
										{//PregnancySpec_OutcomPeriod – Срок исхода (нед); целое, рассчитывается автоматически по дате родов из «КВС.Специфика. Сведения о родах»,  дате постановки на ДУ и Срока беременности при взятии на учет (нед) и не доступно
											llowDecimals: false,
											allowNegative: false,
											fieldLabel: 'Срок исхода',
											maxValue: 90,
											//disabled: true,
											minValue: 0,
											name: 'PregnancySpec_OutcomPeriod',
											//width: 100,
											xtype: 'numberfield',
											labelWidth: 85,
											width: 155,
											margin: '0 0 0 20'
										}, {
											xtype: 'label',
											html: ' недель',
											style: 'padding: 8px 15px;',
										}
									]
								},
								{//BirthSpec_id – Особенности родов;, заполняется автоматически из «КВС.Специфика. Сведения о родах», привязаной к карте ДУ, и не доступно
									comboSubject: 'BirthSpec',
									fieldLabel: 'Особенности родов',
									hiddenName: 'BirthSpec_id',
									name: 'BirthSpec_id',
									//disabled: true,
									//width: 300,
									width: 420,
									xtype: 'commonSprCombo'
								},
								//PregnancySpec_IsHIVtest – Обследована на ВИЧ; (Да/Нет);
								{
									//width: 100,
									boxLabel: langs('Обследована на ВИЧ'),
									name: 'PregnancySpec_IsHIVtest',
									xtype: 'checkbox',
									//~ inputValue: '1',
									//~ uncheckedValue: '0',
									uncheckedValue: 0,
									value: 1,
									getValue: function(){
										return this.value ? 1 : 0;
									},
									style: 'padding-left: 265px;'
								},
								//PregnancySpec_IsHIV – Наличие ВИЧ-инфекции
								{
									//width: 100,
									boxLabel: langs('Наличие ВИЧ-инфекции'),
									name: 'PregnancySpec_IsHIV',
									xtype: 'checkbox',
									//~ inputValue: '1',
									//~ uncheckedValue: '0',

									uncheckedValue: 0,
									value: 1,
									getValue: function(){
										return this.value ? 1 : 0;
									},
									style: 'padding-left: 265px;'
								}
							],
							//TAG: БЕРЕМЕННОСТЬ настройки
							loaded: false,
							inited: false,
							formFields: {
								Lpu_aid: null,
								PregnancySpec_Period: null,
								PregnancySpec_Count: null,
								PregnancySpec_CountBirth: null,
								PregnancySpec_CountAbort: null,
								PregnancySpec_BirthDT: null,
								BirthResult_id: null,
								PregnancySpec_OutcomPeriod: null,
								PregnancySpec_OutcomDT: null,
								BirthSpec_id: null,
								PregnancySpec_IsHIVtest: null,
								PregnancySpec_IsHIV: null,
								PregnancySpec_id: null,
								EvnSection_id: null,
								EvnSection_pid: null
							},
							load: function () {
								var pregnancySpec_id = this.formFields.PregnancySpec_id.getValue();
								if (pregnancySpec_id == '') {
									//специфика о беременности создается
								} else {
									//специфика о беременности редактируется
									loadPregnancySpecIntoForm(pregnancySpec_id);
								}
								//~ //избавиться от костыля для нормальной отрисовки гридов во время первой загрузки формы
								//~ setTimeout(function (){
									//~ Ext.getCmp("PDEF_PregnantPanel").updateLayout();
								//~ }, 100);
							},
							reset: function(){
								win.PregnancyLabGrid.getStore().removeAll();
								win.PregnancySpecComplicationGrid.getStore().removeAll();
								win.PregnancySpecExtragenitalDiseaseGrid.getStore().removeAll();
								win.ChildGrid.getStore().removeAll();
								win.DeadChildGrid.getStore().removeAll();
							},
							setValidation: function (allowBlank){
								//включить/выключить проверку
								this.formFields.PregnancySpec_Period.allowBlank = allowBlank;
								this.formFields.PregnancySpec_Count.allowBlank = allowBlank;
								this.formFields.PregnancySpec_CountBirth.allowBlank = allowBlank;
								this.formFields.PregnancySpec_CountAbort.allowBlank = allowBlank;
								this.formFields.PregnancySpec_BirthDT.allowBlank = allowBlank;
							},
							save: function (callAfterSpecificsSave) {
								//log('->specifics.save');

								var personDisp_id = win.FormPanel.getForm().findField('PersonDisp_id').getValue();
								var dataToSave = new Object();
								dataToSave.PregnancySpec_id = this.formFields.PregnancySpec_id.getValue();
								if (!dataToSave.PregnancySpec_id) {
									dataToSave.PregnancySpec_id = 0;
								}
								this.setValidation(false);
								if (!win.FormPanel.getForm().isValid()) {
									Ext6.Msg.show({
										buttons: Ext6.Msg.OK,
										fn: function() {
											win.formStatus = 'edit';
											win.FormPanel.getFirstInvalidEl().focus(false);
										}.createDelegate(this),
										icon: Ext6.Msg.WARNING,
										msg: ERR_INVFIELDS_MSG,
										title: ERR_INVFIELDS_TIT
									});
								} else {
									dataToSave.Lpu_aid = this.formFields.Lpu_aid.getValue();
									dataToSave.PregnancySpec_Period = this.formFields.PregnancySpec_Period.getValue();
									dataToSave.PregnancySpec_Count = this.formFields.PregnancySpec_Count.getValue();
									dataToSave.PregnancySpec_CountBirth = this.formFields.PregnancySpec_CountBirth.getValue();
									dataToSave.PregnancySpec_CountAbort = this.formFields.PregnancySpec_CountAbort.getValue();
									dataToSave.PregnancySpec_BirthDT = this.formFields.PregnancySpec_BirthDT.getValue().format('d.m.Y');
									//Эти поля не надо передавать на сохранение, они берутся из КВС. UPD: позволить редактировать здесь
									dataToSave.BirthResult_id             = this.formFields.BirthResult_id            .getValue();
									dataToSave.PregnancySpec_OutcomPeriod = this.formFields.PregnancySpec_OutcomPeriod.getValue();
									if (this.formFields.PregnancySpec_OutcomDT.getValue()) {
										dataToSave.PregnancySpec_OutcomDT     = this.formFields.PregnancySpec_OutcomDT    .getValue().format('d.m.Y');
									} else {
										dataToSave.PregnancySpec_OutcomDT = null;
									}
									dataToSave.BirthSpec_id               = this.formFields.BirthSpec_id              .getValue();
									dataToSave.PregnancySpec_IsHIVtest = this.formFields.PregnancySpec_IsHIVtest.getValue();
									dataToSave.PregnancySpec_IsHIV = this.formFields.PregnancySpec_IsHIV.getValue();
									dataToSave.PersonDisp_id = personDisp_id;

									//~ if (win.queryById('PDEF_PregnancySpecComplication')) {
										var person_height_grid = win.PregnancySpecComplicationGrid;
										person_height_grid.getStore().clearFilter();
										if (person_height_grid.getStore().getCount() > 0) {
											var person_height_data = sw4.getStoreRecords(person_height_grid.getStore(), { convertDateFields: true });
											person_height_grid.getStore().filterBy(function(rec) {
												return Number(rec.get('RecordStatus_Code')) != 3;
											});
											dataToSave.PregnancySpecComplication = Ext6.util.JSON.encode(person_height_data);
										}
									//~ }
									//~ if (win.queryById('PDEF_PregnancySpecExtragenitalDisease')) {
										person_height_grid = win.PregnancySpecExtragenitalDiseaseGrid;
										person_height_grid.getStore().clearFilter();
										if (person_height_grid.getStore().getCount() > 0) {
											person_height_data = sw4.getStoreRecords(person_height_grid.getStore(), { convertDateFields: true });
											person_height_grid.getStore().filterBy(function(rec) {
												return Number(rec.get('RecordStatus_Code')) != 3;
											});
											dataToSave.PregnancySpecExtragenitalDisease = Ext6.util.JSON.encode(person_height_data);
										}
									//~ }
									Ext6.Ajax.request({
										method: 'post',
										callback: function(options, success, response) {
											if (success){
												var response_obj = Ext6.util.JSON.decode(response.responseText);
												if (response_obj.success){
													win.FormPanel.getForm().findField('PregnancySpec_id').setValue(response_obj.PregnancySpec_id);
													if (callAfterSpecificsSave) {
														var arg = null;
														if (arguments) {
															var arg = arguments;
														}
														callAfterSpecificsSave(arg);
													}
												} else {

												}
											} else {

											}

										},
										params: dataToSave,
										url: '/?c=PregnancySpec&m=save'
									});
								}
								//log('<-specifics.save');
							},
							//отображает панель
							doShow: function (visible) {
								if(visible) {
									if (!this.loaded) {
										this.init();
										this.load();
									} else {
										this.setEnabled(win.action != 'view' && this.accessType != 0);
									}
								}
								win.showPregnancyPanels(visible);

								//~ if (!this.loaded) {
									//~ this.init();
									//~ this.load();
								//~ }
								//~ this.setEnabled((win.action == 'edit')||(win.action == 'add'));
								//~ win.showPregnancyPanels(visible);
							},
							setEnabled: function(enable){
								//log('->setEnabled', enable);
								var ff = this.formFields;
								if (enable) {
									ff.PregnancySpec_Period.enable();
									ff.PregnancySpec_Count.enable();
									ff.PregnancySpec_CountBirth.enable();
									ff.PregnancySpec_CountAbort.enable();
									ff.PregnancySpec_BirthDT.enable();
									ff.BirthResult_id.enable();
									ff.PregnancySpec_OutcomPeriod.enable();
									ff.PregnancySpec_OutcomDT.enable();
									ff.BirthSpec_id.enable();
									ff.PregnancySpec_IsHIVtest.enable();
									ff.PregnancySpec_IsHIV.enable();
									ff.PregnancySpec_id.enable();
									ff.EvnSection_id.enable();
									ff.EvnSection_pid.enable();
								} else {
									ff.PregnancySpec_Period.disable();
									ff.PregnancySpec_Count.disable();
									ff.PregnancySpec_CountBirth.disable();
									ff.PregnancySpec_CountAbort.disable();
									ff.PregnancySpec_BirthDT.disable();
									ff.BirthResult_id.disable();
									ff.PregnancySpec_OutcomPeriod.disable();
									ff.PregnancySpec_OutcomDT.disable();
									ff.BirthSpec_id.disable();
									ff.PregnancySpec_IsHIVtest.disable();
									ff.PregnancySpec_IsHIV.disable();
									ff.PregnancySpec_id.disable();
									ff.EvnSection_id.disable();
									ff.EvnSection_pid.disable();
								}
								//TODO: Доступность событий в менюшках, переделать:
								//~ win.setReadOnly(!enable, win.PregnancySpecComplicationPanel);
								//~ win.setReadOnly(!enable, win.PregnancySpecExtragenitalDiseasePanel);
							},
							//начальная инициализация
							init: function () {
								if (!this.inited) {
									var f = win.FormPanel.getForm();
									var ff = this.formFields;
									ff.Lpu_aid = f.findField('Lpu_aid');
									ff.PregnancySpec_Period = f.findField('PregnancySpec_Period');
									ff.PregnancySpec_Count = f.findField('PregnancySpec_Count');
									ff.PregnancySpec_CountBirth = f.findField('PregnancySpec_CountBirth');
									ff.PregnancySpec_CountAbort = f.findField('PregnancySpec_CountAbort');
									ff.PregnancySpec_BirthDT = f.findField('PregnancySpec_BirthDT');
									ff.BirthResult_id = f.findField('BirthResult_id');
									ff.PregnancySpec_OutcomPeriod = f.findField('PregnancySpec_OutcomPeriod');
									ff.PregnancySpec_OutcomDT = f.findField('PregnancySpec_OutcomDT');
									ff.BirthSpec_id = f.findField('BirthSpec_id');
									ff.PregnancySpec_IsHIVtest = f.findField('PregnancySpec_IsHIVtest');
									ff.PregnancySpec_IsHIV = f.findField('PregnancySpec_IsHIV');
									ff.PregnancySpec_id = f.findField('PregnancySpec_id');
									ff.EvnSection_id = f.findField('EvnSection_id');
									ff.EvnSection_pid = f.findField('EvnSection_pid');
									this.inited = true;
								} else {
									this.reset();
								}
								this.setValidation(true);
								this.loaded = false;
							}
						}),
						win.PregnancyHistoryPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'ИСТОРИЯ ДИАГНОЗОВ',
							hidden: true,
							items: [
								win.PregnancyHistoryGrid
							]
						}),
						win.PregnancyLabPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'ЛАБОРАТОРНЫЕ ОБСЛЕДОВАНИЯ',
							itemId: 'PDEF_EvnUslugaPregnancySpecGridPanel',
							hidden: true,
							plusButton: function() {win.openEvnUslugaEditWindow('add')},
							items: [
								win.PregnancyLabGrid
							]
						}),
						win.PregnancySpecComplicationPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'ОСЛОЖНЕНИЕ БЕРЕМЕННОСТИ',
							plusButton: function() {win.openPregnancySpecComplicationEditWindow('add')},
							hidden: true,
							items: [
								win.PregnancySpecComplicationGrid
							]
						}),
						win.PregnancySpecExtragenitalDiseasePanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'ЭКСТРАГЕНИТАЛЬНЫЕ ЗАБОЛЕВАНИЯ',
							plusButton: function() {win.openPregnancySpecExtragenitalDiseaseEditWindow('add')},
							hidden: true,
							items: [
								win.PregnancySpecExtragenitalDiseaseGrid
							]
						}),
						win.ChildPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'ДЕТИ',
							hidden: true,
							items: [ win.ChildGrid
							]
						}),
						win.DeadChildPanel = new Ext6.create('PersonDispDefaultPanel', {
							title: 'МЕРТВОРОЖДЕННЫЕ',
							hidden: true,
							items: [ win.DeadChildGrid
							]
						})
					]
				}, {
					layout: 'column',
					userCls: 'buttonFooterGroup',
					border: false,
					margin: '20 10 30 27',
					items: [
						{
							text: 'Сохранить',
							xtype: 'button',
							cls: 'button-primary',
							//style: 'margin-left: 30px;',
							margin: 5,
							handler: function() {
								win.doSave();
							}
						}, {
							text: 'Отмена',
							xtype: 'button',
							cls: 'button-secondary',
							//~ style: 'margin-left: 10px;',
							margin: 5,
							handler: function() {
								win.hide();
							}
						}
					]
				}
			],

			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{ name: 'accessType' },
						{ name: 'Diag_id' },
						{ name: 'DispOutType_id' },
						{ name: 'LpuSection_id' },
						{ name: 'Lpu_id' },
						{ name: 'MedPersonal_id' },
						{ name: 'Person_id' },
						{ name: 'PersonDisp_NumCard' },
						{ name: 'PersonDisp_begDate' },
						{ name: 'PersonDisp_endDate' },
						{ name: 'Server_id' },
						{ name: 'Sickness_id' },
						{ name: 'PregnancySpec_id' },
						{ name: 'Morbus_id' },
						{ name: 'EvnSection_id' },
						{ name: 'EvnSection_pid' },
						{ name: 'PersonDisp_DiagDate' },
						{ name: 'DiagDetectType_id' },
						{ name: 'DispGroup_id' },
						{ name: 'DeseaseDispType_id' },
						{ name: 'PersonDisp_IsTFOMS' },
						{ name: 'Label_id' }
					]
				})
			}),
			region: 'center',
			url: '/?c=PersonDisp&m=savePersonDisp'
		});

		Ext6.apply(win, {
			items: [
				{
					region: 'north',
					border: false,
					userCls: 'dispcardTitleShadow',
					items: [
						win.PersonInfoPanel,
						win.titlePanel
					]
				}, {
					region: 'center',
					border: false,
					scrollable: true,
					userCls: 'dispcard_center',
					items: [
						win.FormPanel
					]
				}
			]

		});

		win.callParent(arguments);

	/*	if (win.PersonDispPanel.printMenu) {
			win.PersonDispPanel.addTool({
				type: 'printmenu',
				callback: function(panel, tool, event) {
					win.PersonDispPanel.printMenu.showBy(tool);
				}
			});
		}*/
	}
});
