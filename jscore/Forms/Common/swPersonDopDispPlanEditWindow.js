/**
* swPersonDopDispPlanEditWindow - окно редактирования плана профилактических мероприятий
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

/*NO PARSE JSON*/
sw.Promed.swPersonDopDispPlanEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'PersonDopDispPlanEditWindow',
	layout: 'border',
	maximizable: true,
	maximized: true,
	width: 470,
	height: 300,
	modal: true,
	codeRefresh: true,
	objectName: 'swPersonDopDispPlanEditWindow',
	objectSrc: '/jscore/Forms/Common/swPersonDopDispPlanEditWindow.js',	
	returnFunc: function(owner) {},
	action: 'add',
	listeners: {
		'hide': function(win) {
			if (win.isAutosaved) {
				win.undoSave();
			}
			else{
				win.hide();
			}
		}
	},
	show: function() {		
		sw.Promed.swPersonDopDispPlanEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var form = this.findById('PersonDopDispPlanEditForm').getForm();
		form.reset();
		this.GridPanel.getGrid().getStore().removeAll();
		this.isAutosaved = false;
		this.data = [];

		if (arguments[0]['action']) {
			this.action = arguments[0]['action'];
		}

		if (arguments[0]['callback']) {
			this.returnFunc = arguments[0]['callback'];
		}
		
		if (arguments[0]['PersonDopDispPlan_id']) {
			this.PersonDopDispPlan_id = arguments[0]['PersonDopDispPlan_id'];
		} else {
			this.PersonDopDispPlan_id = null;
		}
		
		if (arguments[0]['DispClass_id']) {
			this.DispClass_id = arguments[0]['DispClass_id'];
		} else {
			this.DispClass_id = null;
		}

		if ( !this.GridPanel.getAction('action_retry') ) {
			this.GridPanel.addActions({
				handler: function () {
					this.retryInclude();
				}.createDelegate(this),
				iconCls: '',
				name: 'action_retry',
				text: 'Повторно включить в план',
				tooltip: 'Повторно включить в план'
			});
		}
		
		switch (this.action){
			case 'add':
				this.setTitle('План профилактического мероприятия: Добавление');
				break;
			case 'edit':
				this.setTitle('План профилактического мероприятия: Редактирование');
				break;
			case 'view':
				this.setTitle('План профилактического мероприятия: Просмотр');
				break;
		}
		
		if (getRegionNick() == 'vologda') {
			var dc_combo = form.findField('DispClass_id'),
				dc_store = dc_combo.getStore();
			dc_combo.lastQuery = '';
			dc_store.clearFilter();
			if (this.action == 'add') {
				dc_store.filterBy(function(rec) {
					return (rec.get('DispClass_id').inlist([1,5,28]));
				});
			}
		}
		
		if (this.action != 'add') {
			var loadMask = new Ext.LoadMask(Ext.get('PersonDopDispPlanEditForm'), { msg: "Подождите, идет сохранение..." });
			this.findById('PersonDopDispPlanEditForm').getForm().load({
				url: '/?c=PersonDopDispPlan&m=load',
				params: {
					PersonDopDispPlan_id: win.PersonDopDispPlan_id
				},
				success: function (form, action) {
					loadMask.hide();
					form.findField('DispCheckPeriod_id').getStore().baseParams = {
						PersonDopDispPlan_id: win.PersonDopDispPlan_id,
						DispClass_id: form.findField('DispClass_id').getValue()
					};
					form.findField('DispCheckPeriod_id').getStore().load({
						callback: function() {
							var dcp_combo = form.findField('DispCheckPeriod_id');
							var index = dcp_combo.getStore().indexOfId(dcp_combo.getValue());
							var record = dcp_combo.getStore().getAt(index);
							dcp_combo.setValue(dcp_combo.getValue());
							dcp_combo.fireEvent('select', dcp_combo, record, index);
						}
					});
					win.doReset();
				},
				failure: function (form, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
						this.hide();
					}
				},
				scope: this
			});
		} else {
			form.findField('DispClass_id').focus();		
			form.findField('DispCheckPeriod_id').getStore().baseParams = {};
			if (this.DispClass_id) {
				form.findField('DispClass_id').setValue(this.DispClass_id);
				form.findField('DispClass_id').fireEvent('change', form.findField('DispClass_id'), form.findField('DispClass_id').getValue());
			}
		}
		
		if (this.action=='view') {
			form.findField('DispClass_id').disable();
			form.findField('DispCheckPeriod_id').disable();
			this.GridPanel.setReadOnly(true);
			this.GridPanel.getAction('action_refresh').setDisabled(true);
			this.buttons[0].disable();
		} else {
			form.findField('DispClass_id').setDisabled(this.action!='add');
			form.findField('DispCheckPeriod_id').enable();
			this.GridPanel.setReadOnly(false);
			this.GridPanel.getAction('action_refresh').setDisabled(false);
			this.buttons[0].enable();
		}
		
	},
	doSearch: function() {
		var grid = this.GridPanel.getGrid();
		var form = this.FormPanel.getForm();
		var win = this;
		var params = form.getValues();
		params.start = 0;
		params.limit = 5000000;
		
		// 1. грузим всё в массив
		win.data = [];
		win.getLoadMask(lang['zagruzka_dannyih']).show();
		grid.getStore().removeAll();
		Ext.Ajax.request({
			url: '/?c=PersonDopDispPlan&m=loadPlanPersonList',
			params: params,
			callback: function(opt, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					win.data = Ext.util.JSON.decode(response.responseText);
					win.data = win.data.data;
					// 2. грид используем с временным хранилищем (грузим первые сто записей)
					var response_obj = new Object();
					response_obj.totalCount = win.data.length;
					response_obj.data = win.data.slice(0, 100);
					grid.getStore().loadData(response_obj);
					win.refreshCheckAll();
				}
			}
		});
	},
	doReset: function() {
		var form = this.FormPanel.getForm();
		form.findField('Person_FIO').setValue('');
		form.findField('Person_Birthday').setValue('');
		form.findField('PersonAge_Min').setValue('');
		form.findField('PersonAge_Max').setValue('');
		form.findField('Fact_id').clearValue();
		form.findField('Sex_id').clearValue();
		form.findField('PlanPersonListStatusType_id').clearValue();
		form.findField('PacketNumber').setValue('');
		this.doSearch();
	},
	doSave: function(options) 
	{
		var win = this;
		var form = this.findById('PersonDopDispPlanEditForm').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('PersonDopDispPlanEditForm'), { msg: "Подождите, идет сохранение..." });
		var params = {};
		
		if (!form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('PersonDopDispPlanEditForm').getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}	

		params.PersonDopDispPlan_Year = form.findField('DispCheckPeriod_id').getFieldValue('DispCheckPeriod_Year');
		if (form.findField('DispClass_id').disabled) {
			params.DispClass_id = form.findField('DispClass_id').getValue();
		}

		loadMask.show();
		form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result && action.result.PersonDopDispPlan_id) {
						if (options && options.auto) {
							win.returnFunc();
							win.PersonDopDispPlan_id = action.result.PersonDopDispPlan_id;
							form.findField('PersonDopDispPlan_id').setValue(action.result.PersonDopDispPlan_id);
							win.GridPanel.getGrid().getStore().baseParams = {PersonDopDispPlan_id: action.result.PersonDopDispPlan_id, start: 0, limit: 100};
							win.isAutosaved = true;
							if (typeof options.auto == 'function') {
								// релоад для ограничения списка
								form.findField('DispCheckPeriod_id').getStore().baseParams = {
									PersonDopDispPlan_id: win.PersonDopDispPlan_id,
									DispClass_id: form.findField('DispClass_id').getValue()
								};
								form.findField('DispCheckPeriod_id').getStore().load({
									callback: function() {
										var dcp_combo = form.findField('DispCheckPeriod_id');
										var index = dcp_combo.getStore().indexOfId(dcp_combo.getValue());
										var record = dcp_combo.getStore().getAt(index);
										dcp_combo.setValue(dcp_combo.getValue());
										dcp_combo.fireEvent('select', dcp_combo, record, index);
									}
								});
								options.auto();
							}
						} 
						else {
							win.isAutosaved = false;
							win.hide();
							win.returnFunc();
						}
					}	
				}
				else {
					Ext.Msg.alert(lang['oshibka'], 'При сохранении плана возникли ошибки');
				}
							
			}.createDelegate(this)
		});
	},
	autoCreatePlan: function(params) {

		var win = this,
			loadMask = new Ext.LoadMask(Ext.get('PersonDopDispPlanEditWindow'), { msg: "Запуск формирования плана..." });
		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=PersonDopDispPlan&m=autoCreatePlan',
			params: {
				PersonDopDispPlan_id: params.PersonDopDispPlan_id,
				DispCheckPeriod_id: params.DispCheckPeriod_id,
				DispCheckPeriod_begDate: params.DispCheckPeriod_begDate,
				DispCheckPeriod_endDate: params.DispCheckPeriod_endDate
			},
			callback: function(options, success, response) {
				if (success) {
					loadMask.hide();
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.background) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: 'Формирование плана будет продолжено в фоновом режиме',
							fn: function () {
								win.isAutosaved = false;
								win.hide();
							}
						});
					}
				}
			}
		});
	},
	addPlanPerson: function() {
		if (getWnd('swPersonDopDispPlanPersonSearchWindow').isVisible()) {
			return false;
		}
		
		var win = this;
		var form = this.findById('PersonDopDispPlanEditForm').getForm();
		var params = {};
		params.PersonDopDispPlan_id = form.findField('PersonDopDispPlan_id').getValue();
		params.DispClass_id = form.findField('DispClass_id').getValue();
		params.DispCheckPeriod_id = form.findField('DispCheckPeriod_id').getValue();
		params.DispCheckPeriod_begDate = Ext.util.Format.date(form.findField('DispCheckPeriod_id').getFieldValue('DispCheckPeriod_begDate'), 'd.m.Y');
		params.DispCheckPeriod_endDate = Ext.util.Format.date(form.findField('DispCheckPeriod_id').getFieldValue('DispCheckPeriod_endDate'), 'd.m.Y');
		params.PeriodCap_id = form.findField('DispCheckPeriod_id').getFieldValue('PeriodCap_id');
		params.DispCheckPeriod_Year = form.findField('DispCheckPeriod_id').getFieldValue('DispCheckPeriod_Year');
		params.callback = function() {
			win.doSearch();
			win.returnFunc();
		}
		
		var addAction = function (params) {
			getWnd('swPersonDopDispPlanPersonSearchWindow').show(params);
		};

		if (getRegionNick() == 'vologda' && params.DispClass_id == 28) {
			addAction = function (params) {
				win.autoCreatePlan(params);
			};
		}
		
		if (form.findField('PersonDopDispPlan_id').getValue() > 0) {
			addAction(params);
		}
		else {
			this.doSave({
				auto: function () {
					params.PersonDopDispPlan_id = form.findField('PersonDopDispPlan_id').getValue();
					addAction(params);
				}
			});
		}
	},
	deletePlanPerson: function() {
		var win = this;
		var grid = this.GridPanel.getGrid();
		var r_array = [];
		
		for (var i = 0; i < win.data.length; i++) {
			if (win.data[i].IsChecked == 1 && win.data[i].PlanPersonListStatusType_id == 1) { // Только Новые
				r_array.push(win.data[i].PlanPersonList_id);
			}
		}
		
		if (!r_array.length) {
			return false;
		}
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' ) {
					
					var loadMask = new Ext.LoadMask(Ext.get('PersonDopDispPlanEditWindow'), { msg: "Подождите, идет удаление..." });
					loadMask.show();
					Ext.Ajax.request({
						url: '/?c=PersonDopDispPlan&m=deletePlanPersonList',
						params: {
							PlanPersonList_ids: Ext.util.JSON.encode(r_array)
						},
						callback: function(options, success, response) {
							if (success) {
								loadMask.hide();
								win.doSearch();
								win.returnFunc();
							}
						}
					});
				}
			},
			msg: 'Удалить выбранные записи?',
			title: 'Вопрос'
		});
	},
	transferPlanPerson: function() {
		if (getWnd('swPersonDopDispPlanTransferParamsWindow').isVisible()) {
			return false;
		}
		
		var win = this;
		var grid = this.GridPanel.getGrid();
		var form = this.findById('PersonDopDispPlanEditForm').getForm();
		var r_array = [];
		
		for (var i = 0; i < win.data.length; i++) {
			if (win.data[i].IsChecked == 1 && win.data[i].PlanPersonListStatusType_id == 1) { // Только Новые
				r_array.push(win.data[i].PlanPersonList_id);
			}
		}
		
		if (!r_array.length) {
			return false;
		}
		
		var params = form.getValues();
		params.DispClass_id = form.findField('DispClass_id').getValue();
		params.PlanPersonList_ids = r_array;
		params.callback = function() {
			win.doSearch();
			win.returnFunc();
		}
		
		getWnd('swPersonDopDispPlanTransferParamsWindow').show(params);
	},
	retryInclude: function() {
		if (getWnd('swPersonDopDispPlanRetryIncludeParamsWindow').isVisible()) {
			return false;
		}

		var win = this;
		var grid = this.GridPanel.getGrid();
		var form = this.findById('PersonDopDispPlanEditForm').getForm();
		var r_array = [];

		for (var i = 0; i < win.data.length; i++) {
			if (win.data[i].IsChecked == 1 && win.data[i].PlanPersonListStatusType_id == 4) { // Только Ошибки
				r_array.push(win.data[i].PlanPersonList_id);
			}
		}

		if (!r_array.length) {
			return false;
		}

		var params = form.getValues();
		params.DispClass_id = form.findField('DispClass_id').getValue();
		params.PlanPersonList_ids = r_array;
		params.callback = function() {
			win.doSearch();
			win.returnFunc();
		}

		getWnd('swPersonDopDispPlanRetryIncludeParamsWindow').show(params);
	},
	undoSave: function() {
		var win = this;
		var loadMask = new Ext.LoadMask(Ext.get('PersonDopDispPlanEditWindow'), { msg: "Отмена изменений..." });
		loadMask.show();
		Ext.Ajax.request({
			params: {PersonDopDispPlan_id: win.PersonDopDispPlan_id},
			url: '/?c=PersonDopDispPlan&m=delete',
			callback: function(opt, success, resp)  {
				loadMask.hide();
				if (success) {
					win.hide();
					win.returnFunc();
				}
			}
		});
	},
	loadRecords: function(params) {
		var win = this;

		win.GridPanel.getGrid().getStore().baseParams.start = params.start;
		win.GridPanel.getGrid().getStore().baseParams.limit = params.limit;

		var response_obj = new Object();

		response_obj.totalCount = win.data.length;
		response_obj.data = win.data.slice(params.start, params.start+params.limit);
		win.GridPanel.getGrid().getStore().loadData(response_obj);
		this.refreshCheckAll();
	},
	checkAllCheckbox: function()
	{
		var win = this;
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Обработка..."});
		loadMask.show();
		
		setTimeout(function() {
			win.data.forEach(function(rec) {
				rec.IsChecked = 1;
				rec.RecordCheck = 'on';
				win.GridPanel.getGrid().getStore().each(function(record){
					if ((rec.PlanPersonList_id) == record.get('PlanPersonList_id')) {
						record.set('IsChecked', 1);
						record.set('RecordCheck', 'on');
						record.commit();
					}
				});
				win.setGridActions();
			});
			win.refreshCheckAll();
			loadMask.hide();
		}, 10);
	},
	resetCheckbox: function()
	{
		var win = this;
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Обработка..."});
		loadMask.show();
		
		setTimeout(function() {
			win.data.forEach(function(rec) {
				rec.IsChecked = 2;
				rec.RecordCheck = false;
				win.GridPanel.getGrid().getStore().each(function(record){
					if ((rec.PlanPersonList_id) == record.get('PlanPersonList_id')) {
						record.set('IsChecked', 2);
						record.set('RecordCheck', false);
						record.commit();
					}
				});
				win.setGridActions();
			});
			win.refreshCheckAll();
			loadMask.hide();
		}, 10);
	},
	checkPageAll: function(check)
	{
		var win = this;
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Обработка..."});
		loadMask.show();
		
		setTimeout(function() {
			win.GridPanel.getGrid().getStore().each(function(record){
				record.set('IsChecked', (check ? 1 : 2));
				record.set('RecordCheck', (check ? 'on' : false));
				record.commit();
				win.data.forEach(function(rec) {
					if ((rec.PlanPersonList_id) == record.get('PlanPersonList_id')) {
						rec.IsChecked = (check ? 1 : 2);
						rec.RecordCheck = (check ? 'on' : false);
					}
				});
				win.setGridActions();
			});
			loadMask.hide();
		}, 10);
	},
	refreshCheckAll: function() {
		var grid = this.GridPanel.getGrid();
		var is_all_checked = true;

		if (grid.getStore().getCount() > 0) {
			grid.getStore().each(function(record){
				if (record.get('IsChecked')	== 2) {
					is_all_checked = false;
					return false;
				}
			});
		} else {
			is_all_checked = false;
		}

		Ext.get('PDDPEW_checkPageAll').dom.checked = is_all_checked;
	},
	checkRenderer: function(v, p, record) {
		var id = record.get('PlanPersonList_id');
		var value = 'value="'+id+'"';
		var checked = record.get('IsChecked')==1 ? ' checked="checked"' : '';
		var onclick = 'onClick="getWnd(\'swPersonDopDispPlanEditWindow\').checkOne(this.value);"';

		return '<input type="checkbox" '+value+' '+checked+' '+onclick+'>';
	},
	checkOne: function(id) {
		var win = this;
		var grid = this.GridPanel.getGrid();

		var record = grid.getStore().getAt(grid.getStore().findBy(function(rec) { return rec.get('PlanPersonList_id') == id; }));
		if (record) {
			var newVal = (record.get('IsChecked')==2 || Ext.isEmpty(record.get('IsChecked')))?1:2;
			record.set('IsChecked', newVal);
			record.commit();

			// также ищем и обновляем запись в хранилище
			win.data.forEach(function(rec) {
				if ((rec.PlanPersonList_id) == record.get('PlanPersonList_id')) {
					rec.IsChecked = newVal;
				}
			});
		}
		this.refreshCheckAll();
		this.setGridActions();
	},
	setGridActions: function(id) {
		
		var win = this,
			form = this.findById('PersonDopDispPlanEditForm').getForm(),
			grid = this.GridPanel;
		
		grid.setActionDisabled('action_delete', true);
		grid.setActionDisabled('action_refresh', true);
		grid.setActionDisabled('action_retry', true);
		
		if (win.action == 'view') {
			return false;
		}

		if (getRegionNick() == 'vologda' && form.findField('DispClass_id').getValue() == 28) {
			return false;
		}
		
		for (var i = 0; i < win.data.length; i++) {
			if (win.data[i].IsChecked == 1 && win.data[i].PlanPersonListStatusType_id == 1) {
				grid.setActionDisabled('action_delete', false);
				grid.setActionDisabled('action_refresh', false);
			}
			if (win.data[i].IsChecked == 1 && win.data[i].PlanPersonListStatusType_id == 4) {
				grid.setActionDisabled('action_retry', false);
			}
		}
	},
	initComponent: function() {
	
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			id:'PersonDopDispPlanEditForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'north',
			labelAlign: 'right',
			labelWidth: 112,
			height: 270,
			items:
			[{
				name: 'PersonDopDispPlan_id',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['tip'],
				width: 350,
				listWidth: 450,
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				lastQuery: '',
				typeCode: 'int',
				xtype: 'swcommonsprcombo',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var form = this.findById('PersonDopDispPlanEditForm').getForm();
						form.findField('DispCheckPeriod_id').getStore().baseParams.DispClass_id = newValue;
						form.findField('DispCheckPeriod_id').getStore().load();
					}.createDelegate(this)
				},
				onLoadStore: function(store) {
					this.lastQuery = '';
					store.clearFilter();
					var allowedDispClassList = [1,3,5,6,7,9,10];
					if (getRegionNick() == 'kareliya') {
						allowedDispClassList.push(2);
						allowedDispClassList.push(12);
					}
					store.filterBy(function(rec) {
						return (rec.get('DispClass_id').inlist(allowedDispClassList));
					});
				}
			}, {
				allowBlank: false,
				width: 350,
				editable: false,
				hiddenName: 'DispCheckPeriod_id',
				fieldLabel: 'Период',
				lastQuery: '',
				typeCode: 'int',
				xtype: 'swbaselocalcombo',
				store: new Ext.data.JsonStore({
					key: 'DispCheckPeriod_id',
					autoLoad: false,
					fields: [
						{name:'DispCheckPeriod_id',type: 'int'},
						{name:'PeriodCap_id', type: 'int'},
						{name:'DispCheckPeriod_Year', type: 'int'},
						{name:'DispCheckPeriod_Name', type: 'string'},
						{name:'DispCheckPeriod_begDate', type: 'date'},
						{name:'DispCheckPeriod_endDate', type: 'date'}
					],
					url: '/?c=PersonDopDispPlan&m=getDispCheckPeriod'
				}),
				valueField: 'DispCheckPeriod_id',
				displayField: 'DispCheckPeriod_Name',
				listeners: {
					'change': function() {
						
					}.createDelegate(this)
				}
			}, {
				xtype: 'fieldset',
				style:'padding: 0px 3px 3px 6px;',
				autoHeight: true,
				listeners: {
					expand: function() {
						win.FormPanel.setHeight(200);
						this.ownerCt.doLayout();
						win.syncSize();
					},
					collapse: function() {
						win.FormPanel.setHeight(90);
						win.syncSize();
					}
				},
				collapsible: true,
				collapsed: false,
				title: lang['filtr'],
				bodyStyle: 'background: #DFE8F6; padding: 5px;',
				labelWidth: 100,
				items: [{
					fieldLabel: 'ФИО',
					name: 'Person_FIO',
					xtype: 'textfieldpmw',
					width: 350
				}, {
					layout: 'column',
					items: [{
						layout:'form',
						bodyStyle:'background: #DFE8F6;',
						labelWidth: 100,
						border: false,
						items: [{
							fieldLabel: 'Дата рождения',
							name: 'Person_Birthday',
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 140
						}]
					}, {
						layout: 'form',
						labelWidth: 65,
						items: [{
							fieldLabel: lang['pol'],
							xtype: 'swpersonsexcombo',
							hiddenName: 'Sex_id',
							width: 140
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowNegative: false,
							allowDecimals: false,
							fieldLabel: lang['vozrast_s'],
							name: 'PersonAge_Min',
							width: 140,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 65,
						layout: 'form',
						items: [{
							allowNegative: false,
							allowDecimals: false,
							fieldLabel: lang['po'],
							name: 'PersonAge_Max',
							width: 140,
							xtype: 'numberfield'
						}]
					}]
				}, {
					xtype: 'swbaselocalcombo',
					fieldLabel: 'Факт',
					triggerAction: 'all',
					hiddenName: 'Fact_id',
					width: 350,
					store: [
						[0, 'Все'],
						[1, 'Прошел'],
						[2, 'Не прошел']
					],
					listeners: {
						'change': function() {
							
						}.createDelegate(this)
					}
				}, {
					xtype: 'swcommonsprcombo',
					comboSubject: 'PlanPersonListStatusType',
					fieldLabel: 'Статус записи',
					hiddenName: 'PlanPersonListStatusType_id',
					width: 350
				}, {
					width: 140,
					name: 'PacketNumber',
					fieldLabel: 'Номер пакета',
					autoCreate: {tag: "input", maxLength: 1, autocomplete: "off"},
					allowDecimals: false,
					allowNegative: false,
					xtype: 'numberfield'
				}, {
					border: false,
					style: 'margin-top: 5px;',
					layout: 'column',
					items: [{
						border: false,
						style: 'margin-left: 315px;',
						layout: 'form',
						items: [{
							xtype: 'button',
							handler: function()
							{
								win.doSearch();
							},
							iconCls: 'search16',
							text: BTN_FRMSEARCH
						}]
					}, {
						border: false,
						style: 'margin-left: 5px;',
						layout: 'form',
						items: [{
							xtype: 'button',
							handler: function()
							{
								win.doReset();
							},
							iconCls: 'resetsearch16',
							text: BTN_FRMRESET
						}]
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({},
			[
				{ name: 'PersonDopDispPlan_id' },
				{ name: 'DispClass_id' },
				{ name: 'DispCheckPeriod_id' }
			]
			),
			url: '/?c=PersonDopDispPlan&m=save'
		});

		var packageResultRenderer = function(value, meta) {
			if (Ext.isEmpty(value)) return '';
			meta.attr += ' data-qtip="' + value + '"';
			return value;
		};

		var packageDataShowTpl = new Ext.XTemplate([
			'<span class="fake-link" onclick="Ext.Msg.alert(\'Данные\', \'{data}\');">Показать</span>'
		]);

		var packageDataRenderer = function(value) {
			if (Ext.isEmpty(value)) return '';
			return packageDataShowTpl.apply({data: value});
		};
		
		this.GridPanel = new sw.Promed.ViewFrame({
			focusOnFirstLoad: false, 
			region: 'center',
			dataUrl: '/?c=PersonDopDispPlan&m=loadPlanPersonList',
			toolbar: true,
			useEmptyRecord: false,
			autoLoadData: false,
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			autoExpandColumn: 'autoexpand',
			id: win.id + 'ViewFrame',
			onRowSelect: function(sm,rowIdx,record) {
			},
			onLoadData: function(data) {
				win.GridPanel.getAction('action_refresh').setDisabled(true);
				win.GridPanel.getAction('action_retry').setDisabled(true);
			},
			onDblClick: function(grid, idx) {
				var record = grid.getStore().getAt(idx);
				if (record && record.get('Person_id')) {
					getWnd('swPersonEditWindow').show({
						action: 'edit',
						Person_id: record.get('Person_id')
					});
				}
			},
			stringfields:
			[
				{name: 'check', sortable: false, header: '<input type="checkbox" id="PDDPEW_checkPageAll" onClick="getWnd(\'swPersonDopDispPlanEditWindow\').checkPageAll(this.checked);">', width: 40, renderer: this.checkRenderer},
				{name: 'IsChecked', type: 'int', hidden:true},
				{name: 'PlanPersonList_id', type: 'int', key: true, hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Person_FIO', header: 'ФИО', width: 300, id:'autoexpand'},
				{name: 'Person_Birthday', header: 'Дата рождения', width: 150},
				{name: 'Person_Age', header: 'Возраст', width: 150},
				{name: 'PlanPersonListStatusType_id', header: 'Статус', type: 'int', hidden: true},
				{name: 'PlanPersonListStatusType_Name', header: 'Статус', type: 'string', width: 150},
				{name: 'PlanPersonListStatus_setDate', header: 'Дата установки статуса', type: 'date', width: 150},
				{name: 'ExportErrorPlanDDType_Code', header: 'Ошибки экспорта', type: 'string', width: 100, hidden: getRegionNick() == 'ekb'},
				{name: 'ExportErrorPlanDD_Description', header: 'Ошибки экспорта', type: 'string', width: 150, hidden: getRegionNick() != 'ekb'},
				{name: 'ExportResult', header: 'Результат отправки', type: 'string', hidden: getRegionNick().inlist(['vologda']), width: 250, renderer: packageResultRenderer},
				{name: 'ExportData', header: 'Данные пакета', type: 'string', hidden: getRegionNick().inlist(['vologda']), width: 150, renderer: packageDataRenderer},
				{name: 'ImportData', header: 'Ответ ТФОМС', type: 'string', hidden: getRegionNick().inlist(['vologda']), width: 150, renderer: packageDataRenderer},
				{name: 'IsDisp', header: 'Факт прохождения осмотра', type: 'checkbox', width: 150}
			],
			actions:
			[
				{name:'action_add', hidden: false, disabled: (win.action == 'view'), handler: function(){win.addPlanPerson()}},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: false, disabled: (win.action == 'view'), handler: function(){win.deletePlanPerson()}},
				{name:'action_refresh', hidden: false, disabled: (win.action == 'view'), text: 'Перенести', tooltip: 'Перенести', icon: 'img/icons/redo.png', handler: function(){win.transferPlanPerson()}},
				{name:'action_print', hidden: true, disabled: false}
			]
		});

		this.GridPanel.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				var status = Number(row.get('PlanPersonListStatusType_id'));
				
				if (status.inlist([4,5])) {
					cls = cls + 'x-grid-rowred ';
				}

				if (cls.length == 0) {
					cls = 'x-grid-panel';
				}

				return cls;
			}
		});
		
		this.GridPanel.getGrid().getStore().addListener('beforeload', function(store, options) {
			win.loadRecords(options.params);
			return false;
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [
				this.FormPanel,
				this.GridPanel
			],
			buttons:
			[{
				text: lang['sohranit'],
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this)
			}, {
				handler: function() {
					this.checkAllCheckbox();
				}.createDelegate(this),
				text: lang['otmetit_vse']
			}, {
				handler: function() {
					this.resetCheckbox();
				}.createDelegate(this),
				text: lang['sbrosit_vse']
			},
			{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			},
			{
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function()
				{
					this.hide();
				}.createDelegate(this)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.doSave();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swPersonDopDispPlanEditWindow.superclass.initComponent.apply(this, arguments);
	}
});