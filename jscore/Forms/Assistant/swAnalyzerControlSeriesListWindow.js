/**
 * swAnalyzerControlSeriesListWindow - Контроль качества
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @comment
 */
sw.Promed.swAnalyzerControlSeriesListWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	title: langs('Контроль качества'),
	id: 'AnalyzerControlSeriesListWindow',
	modal: false,
	width: 900,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	layout: 'form',
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	show: function() {
		sw.Promed.swAnalyzerControlSeriesListWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = win.FormPanel.getForm();
		
		this.MedService_id = arguments[0].MedService_id || null;
		this.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick || null;
		
		var ms_combo = base_form.findField('MedService_id');
		
		base_form.reset();
		win.AnalyzerTestGrid.getGrid().getStore().removeAll();
		win.AnalyzerControlSeriesGrid.getGrid().getStore().removeAll();
		
		if (this.MedServiceType_SysNick == 'lab') {
			ms_combo.hideContainer();
		} else {
			ms_combo.showContainer();
		}
		
		this.syncShadow();
		
		ms_combo.getStore().removeAll();
		ms_combo.getStore().load({
			params: {
				ARMType: win.MedServiceType_SysNick,
				MedService_id: win.MedService_id
			},
			callback: function() {
				if (ms_combo.getStore().getCount()) {
					ms_combo.setValue(ms_combo.getStore().getAt(0).get('MedService_id'));
					ms_combo.fireEvent('change', ms_combo, ms_combo.getValue());
				}
			}
		});
	},
	loadAnalyzerControlSeriesGrid: function() {
		var win = this;
		var grid = win.AnalyzerControlSeriesGrid.getGrid();
		var regDTRange = Ext.getCmp(win.id + 'AnalyzerControlSeries_regDTRange').getRawValue();
		grid.getStore().load({
			params: {
				AnalyzerTest_id: win.AnalyzerControlSeriesGrid.params.AnalyzerTest_id,
				AnalyzerControlSeries_regDateRange: regDTRange
			}
		});
	},
	initComponent: function() {
		var win = this;
	
		win.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			autoHeight: true,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				layout: 'form',
				id: 'AnalyzerControlSeriesListForm',
				bodyStyle:'background:#DFE8F6;',
				border: true,
				labelWidth: 100,
				collapsible: true,
				region: 'north',
				items: [{
					fieldLabel: langs('Лаборатория'),
					hiddenName: 'MedService_id',
					allowBlank: false,
					xtype: 'swmedserviceglobalcombo',
					width: 300,
					listeners: {
						'change': function (combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});
							combo.fireEvent('select', combo, combo.getStore().getAt(index));
						}.createDelegate(this),
						'select': function (combo, record) {
							var base_form = win.FormPanel.getForm();
							var a_combo = base_form.findField('Analyzer_id');
							a_combo.getStore().removeAll();
							if (record && record.get('MedService_id')) {
								a_combo.getStore().load({
									params: {
										MedService_id: record.get('MedService_id')
									},
									callback: function() {
										if (a_combo.getStore().getCount()) {
											a_combo.setValue(a_combo.getStore().getAt(0).get('Analyzer_id'));
											a_combo.fireEvent('select', a_combo, a_combo.getStore().getAt(0), 0);
										}
									}
								});
							}
						}
					}
				}, {
					fieldLabel: langs('Анализатор'),
					hiddenName: 'Analyzer_id',
					allowBlank: false,
					anchor: '',
					xtype: 'swanalyzercombo',
					width: 300,
					listeners: {
						'select': function (c, r) {
							win.AnalyzerControlSeriesGrid.getGrid().getStore().removeAll();
							var grid = win.AnalyzerTestGrid.getGrid();
							grid.getStore().removeAll();
							if (r && r.get('Analyzer_id')) {
								grid.getStore().load({
									params: {
										Analyzer_id: r.get('Analyzer_id')
									}
								});
							}
						}
					}
				}]
			}]
		});
		
		this.AnalyzerTestGrid = new sw.Promed.ViewFrame({
			useEmptyRecord: false,
			id: win.id + 'swAnalyzerTestGrid',
			autoLoadData: false,
			border: true,
			object: 'AnalyzerTest',
			dataUrl: '/?c=AnalyzerTest&m=loadAnalyzerTestGrid',
			region: 'west',
			root: 'data',
			width: 340,
			title: 'Методики',
			toolbar: false,
			actions:[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true},
				{name:'action_print'},
				{name:'action_refresh'}
			],
			stringfields: [
				{name:'AnalyzerTest_id', type:'int', header:'ID', key:true},
				{name:'AnalyzerTest_Name', type:'string', header:langs('Методика'), width: 180},
				{name:'AnalyzerTest_Code', type:'string', header:langs('Код теста'), width: 120},
			],
			onRowSelect: function(sm,index,record){
				var base_form = win.FormPanel.getForm();
				var a_combo = base_form.findField('Analyzer_id');
				var grid = win.AnalyzerControlSeriesGrid.getGrid();
				grid.getStore().removeAll();
				if (record && record.get('AnalyzerTest_id')) {
					win.AnalyzerControlSeriesGrid.params = {
						AnalyzerTest_id: record.get('AnalyzerTest_id'),
						Analyzer_begDT: a_combo.getFieldValue('Analyzer_begDT'),
						MedService_id: win.MedService_id,
					};
					win.loadAnalyzerControlSeriesGrid();
				}
			}
		});
		
		this.AnalyzerControlSeriesGrid = new sw.Promed.ViewFrame({
			useEmptyRecord: false,
			id: win.id + 'swAnalyzerControlSeriesGrid',
			autoLoadData: false,
			editformclassname: 'swAnalyzerControlSeriesEditWindow',
			border: false,
			object: 'AnalyzerControlSeries',
			dataUrl: '/?c=AnalyzerControlSeries&m=loadList',
			region: 'center',
			width: 'auto',
			toolbar: false,
            actions:[
                {name:'action_add'},
                {name:'action_edit'},
                {name:'action_view', disabled: true, hidden: true},
                {name:'action_delete'},
                {name:'action_print', disabled: true, hidden: true},
                {name:'action_refresh'}
            ],
			stringfields: [
				{name:'AnalyzerControlSeries_id', type:'int', header:'ID', key:true},
				{name:'AnalyzerTest_id', type:'int', hidden: true},
				{name:'AnalyzerControlSeries_regDT', type:'string', header:langs('Дата'), width: 80},
				{name:'AnalyzerControlSeries_Value', type:'string', header:langs('Результат'), width: 120},
				{name:'AnalyzerControlSeries_IsControlPassed', type:'string', header:langs('Контроль пройден'), width: 80},
				{name:'AnalyzerControlSeries_Comment', type:'string', header:langs('Примечание'), width: 120},
				{name:'MedPersonal_Fio', type:'string', header:langs('Сотрудник'), width: 120},
			],
			deleteRecord: function() {
				var view_frame = this;
				var record = view_frame.getGrid().getSelectionModel().getSelected();
                if (!record) return false;
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							Ext.Ajax.request({
								url: '/?c=AnalyzerControlSeries&m=delete',
								params: {
									AnalyzerControlSeries_id: record.get('AnalyzerControlSeries_id')
								},
								callback: function(o, s, r) {
									win.AnalyzerControlSeriesGrid.loadData();
								}
							});
						}
					},
					msg: 'Результаты измерения контрольного материала будут удалены. Продолжить?',
					title: 'Удаление контрольной серии от ' + record.get('AnalyzerControlSeries_regDT')
				});
			},
			refreshRecords: function() {
				win.AnalyzerControlSeriesGrid.loadData();
			}
		});
		
		this.TopToolbar = new Ext.Toolbar({
			id : this.id+'TopToolbar',
			items:[
				new Ext.Action({name:'action_add', text: langs('Добавить'), tooltip: langs('Добавить'), iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function(){win.AnalyzerControlSeriesGrid.editRecord('add')}}),
				new Ext.Action({name:'action_edit', text: langs('Изменить'), tooltip: langs('Открыть'), iconCls : 'x-btn-text', icon: 'img/icons/edit16.png', handler: function(){win.AnalyzerControlSeriesGrid.editRecord('edit')}}),
				new Ext.Action({name:'action_delete', text: langs('Удалить'), tooltip: langs('Удалить'), iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function(){win.AnalyzerControlSeriesGrid.deleteRecord()}}),
				{xtype: 'tbfill'},
				{
					fieldLabel: langs('Период'),
					id: win.id + 'AnalyzerControlSeries_regDTRange',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield',
					listeners: {
						'keydown': function (inp, e) {
							if (e.getKey() == Ext.EventObject.ENTER) {
								win.loadAnalyzerControlSeriesGrid();
							}
						}.createDelegate(this),
						'select': function () {
							win.loadAnalyzerControlSeriesGrid();
						}
					}
				}
			]
		});
		
		this.AnalyzerControlSeriesPanel = new Ext.Panel({
			region: 'center',
			border: true,
			tbar: this.TopToolbar,
			title: 'Контрольные серии',
			items: [
				this.AnalyzerControlSeriesGrid
			]
		});
		
		Ext.apply(this, {
			modal: true,
			height: 230,
			buttons:[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function()
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				win.FormPanel, {
					layout: 'border',
					//autoHeight: true,
					height: 400,
					items: [
						win.AnalyzerTestGrid,
						win.AnalyzerControlSeriesPanel
					]
				}
			]
		});
		sw.Promed.swAnalyzerControlSeriesListWindow.superclass.initComponent.apply(this, arguments);
	}
});