/**
* swPersonDopDispPlanPersonSearchWindow - поиск людей для профилактических мероприятий
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
sw.Promed.swPersonDopDispPlanPersonSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Список лиц для проведения диспансеризации/профилактических осмотров: Поиск',
	id: 'PersonDopDispPlanPersonSearchWindow',
	layout: 'border',
	maximizable: true,
	maximized: true,
	width: 470,
	height: 300,
	modal: true,
	codeRefresh: true,
	objectName: 'swPersonDopDispPlanPersonSearchWindow',
	objectSrc: '/jscore/Forms/Common/swPersonDopDispPlanPersonSearchWindow.js',	
	returnFunc: function(owner) {},
	PersonDopDispPlan_id: null,
	action: 'add',
	show: function() {		
		sw.Promed.swPersonDopDispPlanPersonSearchWindow.superclass.show.apply(this, arguments);

		this.data = [];
		
		var base_form = this.FiltersFrame.getForm();
		base_form.reset();
		this.GridPanel.getGrid().getStore().removeAll();
		this.findById('PDDPPSW_DVNPanel').hide();
		this.findById('PDDPPSW_POVNPanel').hide();
		
		if (arguments[0]['DispCheckPeriod_Year']) {
			this.findById('PDDPPSW_PersonDopDisp_Year').setValue(arguments[0]['DispCheckPeriod_Year']);
		}
		else {
			this.findById('PDDPPSW_PersonDopDisp_Year').setValue(getGlobalOptions().date.substr(6, 4));
		}
		
		base_form.findField('PersonPeriodicType_id').hideContainer();
		this.findById('PDDPPSW_SearchFilterTabbar').hideTabStripItem('PDDPPSW_SearchFilterTabbarfilterUser');
		this.findById('PDDPPSW_SearchFilterTabbar').setActiveTab(1);
		this.findById('PDDPPSW_SearchFilterTabbar').setActiveTab(0);
		this.findById('PDDPPSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('PDDPPSW_SearchFilterTabbar').getActiveTab());

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
		
		if (arguments[0]['DispCheckPeriod_begDate']) {
			this.DispCheckPeriod_begDate = arguments[0]['DispCheckPeriod_begDate'];
		} else {
			this.DispCheckPeriod_begDate = null;
		}
		
		if (arguments[0]['PeriodCap_id']) {
			this.PeriodCap_id = arguments[0]['PeriodCap_id'];
		} else {
			this.PeriodCap_id = null;
		}
		
		if (this.DispClass_id == 1) {
			this.findById('PDDPPSW_DVNPanel').show();
		}
		
		if (this.DispClass_id == 5) {
			this.findById('PDDPPSW_POVNPanel').show();
		}
		
		var att_lpu_field = base_form.findField('AttachLpu_id');
		att_lpu_field.setValue(getGlobalOptions().lpu_id);
		att_lpu_field.setDisabled(!isSuperAdmin());
		
		if (this.DispClass_id.inlist([1,5])) {
			base_form.findField('LpuAttachType_id').setValue(1);
			base_form.findField('LpuAttachType_id').setDisabled(true);
			base_form.findField('PersonCardStateType_id').setValue(1);
			base_form.findField('PersonCardStateType_id').setDisabled(true);
		} else {
			base_form.findField('LpuAttachType_id').setValue(null);
			base_form.findField('LpuAttachType_id').setDisabled(false);
			base_form.findField('PersonCardStateType_id').setDisabled(false);
		}
		
		base_form.findField('Person_isNotDispDopOnTime').hide();
		if (['kz', 'kareliya', 'khak', 'buryatiya', 'ufa'].indexOf(getRegionNick()) === -1) {
			base_form.findField('Person_isNotDispDopOnTime').show();
		}
	},
	doSave: function() 
	{
		var win = this;
		var form = this.FiltersFrame.getForm();
		var grid = this.GridPanel.getGrid();
		var r_array = [];
		
		for (var i = 0; i < win.data.length; i++) {
			if (win.data[i].IsChecked == 1) {
				r_array.push(win.data[i].Person_id);
			}
		}
		
		if (!r_array.length) {
			this.returnFunc();
			this.hide();
			return false;
		}
		
		var loadMask = new Ext.LoadMask(Ext.get('PersonDopDispPlanPersonSearchWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=PersonDopDispPlan&m=savePlanPersonList',
			params: {
				PersonDopDispPlan_id: win.PersonDopDispPlan_id,
				Person_ids: Ext.util.JSON.encode(r_array)
			},
			callback: function(options, success, response) {
				if (success) {
					loadMask.hide();
					win.returnFunc();
					win.hide();
				}
			}
		});
		return true;
	},
	doSearch: function() {
		var grid = this.GridPanel.getGrid();
		var form = this.FiltersFrame.getForm();
		var win = this;
		var params = form.getValues();
		params.start = 0;
		params.limit = 5000000;
		params.PersonDopDisp_Year = this.findById('PDDPPSW_PersonDopDisp_Year').getValue();
		params.AttachLpu_id = form.findField('AttachLpu_id').getValue();
		params.DispClass_id = this.DispClass_id;
		params.DispCheckPeriod_begDate = this.DispCheckPeriod_begDate;
		params.PeriodCap_id = this.PeriodCap_id;
		params.LpuAttachType_id = form.findField('LpuAttachType_id').getValue();
		params.PersonCardStateType_id = form.findField('PersonCardStateType_id').getValue();
		
		// 1. грузим всё в массив
		win.data = [];
		win.getLoadMask(lang['zagruzka_dannyih']).show();
		grid.getStore().removeAll();
		Ext.Ajax.request({
			url: C_SEARCH,
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
					win.GridPanel.getGrid().getStore().loadData(response_obj);
					win.refreshCheckAll();
				}
			}
		});
	},
	doReset: function() {
		var base_form = this.FiltersFrame.getForm();
		base_form.reset();
		
		var att_lpu_field = base_form.findField('AttachLpu_id');
		att_lpu_field.setValue(getGlobalOptions().lpu_id);
		
		this.GridPanel.getGrid().getStore().removeAll();
		this.data = [];
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
					if ((rec.Person_id) == record.get('Person_id')) {
						record.set('IsChecked', 1);
						record.set('RecordCheck', 'on');
						record.commit();
					}
				});
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
					if ((rec.Person_id) == record.get('Person_id')) {
						record.set('IsChecked', 2);
						record.set('RecordCheck', false);
						record.commit();
					}
				});
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
					if ((rec.Person_id) == record.get('Person_id')) {
						rec.IsChecked = (check ? 1 : 2);
						rec.RecordCheck = (check ? 'on' : false);
					}
				});
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

		Ext.get('PDDPPSW_checkPageAll').dom.checked = is_all_checked;
	},
	checkRenderer: function(v, p, record) {
		var id = record.get('Person_id');
		var value = 'value="'+id+'"';
		var checked = record.get('IsChecked')==1 ? ' checked="checked"' : '';
		var onclick = 'onClick="getWnd(\'swPersonDopDispPlanPersonSearchWindow\').checkOne(this.value);"';

		return '<input type="checkbox" '+value+' '+checked+' '+onclick+'>';
	},
	checkOne: function(id) {
		var win = this;
		var grid = this.GridPanel.getGrid();

		var record = grid.getStore().getAt(grid.getStore().findBy(function(rec) { return rec.get('Person_id') == id; }));
		if (record) {
			var newVal = (record.get('IsChecked')==2 || Ext.isEmpty(record.get('IsChecked')))?1:2;
			record.set('IsChecked', newVal);
			record.commit();

			// также ищем и обновляем запись в хранилище
			win.data.forEach(function(rec) {
				if ((rec.Person_id) == record.get('Person_id')) {
					rec.IsChecked = newVal;
				}
			});
		}
		this.refreshCheckAll();
	},
	initComponent: function() {
	
		var win = this;
		var year_store = [];
		for ( var i = 2017; i <= 2099; i++ ) {
			year_store.push([i, String(i)]);
		}
			
		this.FiltersFrame = getBaseSearchFiltersFrame({
			ownerWindow: this,
			searchFormType: 'PersonDopDispPlan',
			tabPanelId: 'PDDPPSW_SearchFilterTabbar',
			allowPersonPeriodicSelect: false,
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				style: 'padding: 5px 10px;',
				labelWidth: 180,
				border: false,
				layout: 'form',
				title: '6. Профилактические мероприятия',
				items: [{
					border: false,
					layout: 'column',
					items: [{
						layout:'form',
						labelWidth: 100,
						border: false,
						items:[{
							boxLabel: 'Учесть открытые/закрытые карты в плановом году',
							hideLabel: true,
							name: 'Person_isDopDispPassed',
							xtype: 'checkbox',
							listeners: {
								'check': function(checkbox, checked) {
									var base_form = this.FiltersFrame.getForm();
									if ( checked ) {
										base_form.findField('EvnPLDisp_setDate_Range').enable();
										base_form.findField('EvnPLDisp_disDate_Range').enable();
									}
									else {
										base_form.findField('EvnPLDisp_setDate_Range').disable();
										base_form.findField('EvnPLDisp_disDate_Range').disable();
									}
								}.createDelegate(this)
							}
						}, {
							boxLabel: 'Часто обращающиеся за МП',
							hideLabel: true,
							name: 'Person_isOftenApplying',
							xtype: 'checkbox'
						}, {
							boxLabel: 'Не обращавшиеся за МП в прошлом году',
							hideLabel: true,
							name: 'Person_isNotApplyingLastYear',
							xtype: 'checkbox'
						}]
					}, {
						layout:'form',
						labelWidth: 200,
						border: false,
						items:[{
							fieldLabel: 'Дата начала мероприятия',
							name: 'EvnPLDisp_setDate_Range',
							disabled: true,
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							width: 170,
							xtype: 'daterangefield'
						}, {
							fieldLabel: 'Дата окончания мероприятия',
							name: 'EvnPLDisp_disDate_Range',
							disabled: true,
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							width: 170,
							xtype: 'daterangefield'
						}]
					}]
				}, {
					autoHeight: true,
					labelWidth: 250,
					style: 'padding: 5px 10px;',
					title: 'ДВН',
					id: 'PDDPPSW_DVNPanel',
					width: 555,
					xtype: 'fieldset',
					items: [{
						boxLabel: 'Не проходили ПОВН в прошлом году',
						hideLabel: true,
						name: 'Person_isNotDispProf',
						xtype: 'checkbox'
					}, {
						boxLabel: 'Подлежащие ежегодному прохождению ДВН',
						hideLabel: true,
						name: 'Person_isYearlyDispDop',
						xtype: 'checkbox'
					}, {
						boxLabel: 'Не проходившие в установленные сроки',
						hideLabel: true,
						name: 'Person_isNotDispDopOnTime',
						xtype: 'checkbox'
					}]
				}, {
					autoHeight: true,
					labelWidth: 150,
					style: 'padding: 5px 10px;',
					title: 'ПОВН',
					id: 'PDDPPSW_POVNPanel',
					width: 555,
					xtype: 'fieldset',
					items: [{
						boxLabel: 'Не проходили ДВН в прошлом году',
						hideLabel: true,
						name: 'Person_isNotDispDop',
						xtype: 'checkbox'
					}]
				}]
			}]
		}); 
		
		this.FormPanel = new Ext.Panel({
			autoHeight: true,
			id: 'PDDPPSW_FormPanel',
			region: 'north',
			layout: 'form',
			border: false,
			labelWidth: 120,
			items: 
			[{
				bodyStyle:'padding:3px; padding-left:5px;',
				layout: 'form',
				xtype: 'panel',
				border: false,
				labelWidth: 125,
				items:
				[{
					allowBlank: false,
					xtype: 'swbaselocalcombo',
					fieldLabel: lang['god'],
					triggerAction: 'all',
					hiddenName: 'PersonDopDisp_Year',
					id: 'PDDPPSW_PersonDopDisp_Year',
					disabled: true,
					width: 60,
					store: year_store,
					listeners: {
						'change': function() {
							
						}.createDelegate(this)
					}
				}]
			},
			this.FiltersFrame, 
			{
				style: 'margin: -1px -1px 0;',
				bodyStyle: 'padding: 10px 0 10px 5px;',
				layout: 'form',
				xtype: 'panel',
				frame: false,
				border: true,
				labelWidth: 125,
				items:
				[{
					layout: 'column',
					border: false,
					items: 
					[{
						layout: 'form',
						labelWidth: 55,
						border: false,
						items:
						[{
							style: "padding-left: 10px",
							xtype: 'button',
							text: lang['nayti'],
							iconCls: 'search16',
							style: 'margin: 0 0 0 10px',
							handler: function() {
								this.doSearch();
							}.createDelegate(this)
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						border: false,
						items:[{
							style: "padding-left: 10px",
							xtype: 'button',
							text: lang['sbros'],
							iconCls: 'resetsearch16',
							style: 'margin: 0 0 0 10px',
							handler: function() {
								this.doReset();
							}.createDelegate(this)
						}]
					}]
				}]
			}]
		});
		
		this.GridPanel = new sw.Promed.ViewFrame({
			focusOnFirstLoad: false, 
			region: 'center',
			dataUrl: C_SEARCH,
			toolbar: false,
			useEmptyRecord: false,
			autoLoadData: false,
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			id: win.id + 'ViewFrame',
			autoExpandColumn: 'autoexpand',
			onRowSelect: function(sm,rowIdx,record) {
				
			},
			stringfields:
			[
				{name: 'check', sortable: false, header: '<input type="checkbox" id="PDDPPSW_checkPageAll" onClick="getWnd(\'swPersonDopDispPlanPersonSearchWindow\').checkPageAll(this.checked);">', width: 40, renderer: this.checkRenderer},
				{name: 'IsChecked', type: 'int', hidden:true},
				{name: 'Person_id', type: 'int', key: true, hidden: true},
				{name: 'Person_FIO', header: 'ФИО', width: 300, id:'autoexpand'},
				{name: 'Person_Birthday', header: 'Дата рождения', width: 150},
				{name: 'Person_Sex', header: 'Пол', width: 150},
				{name: 'EvnPLDisp_setDate', header: 'Дата начала мероприятия', width: 150},
				{name: 'EvnPLDisp_disDate', header: 'Дата окончания мероприятия', width: 150}
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true}
			]
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
					ShowHelp(this.ownerCt.title);
				}
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
		sw.Promed.swPersonDopDispPlanPersonSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});