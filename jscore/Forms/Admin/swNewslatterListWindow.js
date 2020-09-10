/**
 * swNewslatterListWindow - список рассылок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2015 Swan Ltd.
 * @author		Aleksandr Chebukin
 * @version     18.12.2015
 */
/*NO PARSE JSON*/
sw.Promed.swNewslatterListWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: false,
	maximized: true,
	height: 600,
	width: 900,
	id: 'swNewslatterListWindow',
	title: 'Журнал рассылок',
	layout: 'border',
	resizable: true,
	deleteNewslatter: function() {
		var win = this;
		var records = [];
		this.NewslatterGrid.getMultiSelections().forEach(function (el){
			if (!Ext.isEmpty(el.get('Newslatter_id'))) {
				records.push(el.get('Newslatter_id'));
			}
		});
		
		if (!records.length) {
			return false;
		}
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							win.PersonNewslatterGrid.removeAll({ clearAll: true });
							win.NewslatterGrid.loadData();
						},
						params: {Newslatter_ids: Ext.util.JSON.encode(records)},
						url: '/?c=Newslatter&m=delete'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить выбранные рассылки?',
			title: 'Вопрос'
		});

	},
	cancelNewslatter: function() {
		var win = this;
		var records = [];
		this.NewslatterGrid.getMultiSelections().forEach(function (el){
			if (!Ext.isEmpty(el.get('Newslatter_id')) && el.get('Newslatter_IsActive') == 'true') {
				records.push(el.get('Newslatter_id'));
			}
		});
		
		if (!records.length) {
			return false;
		}
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							win.PersonNewslatterGrid.removeAll({ clearAll: true });
							win.NewslatterGrid.loadData();
						},
						params: {Newslatter_ids: Ext.util.JSON.encode(records)},
						url: '/?c=Newslatter&m=cancel'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Отменить выбранные рассылки?',
			title: 'Вопрос'
		});
		
	},
	activateNewslatter: function() {
		var win = this;
		var records = [];
		this.NewslatterGrid.getMultiSelections().forEach(function (el){
			if (!Ext.isEmpty(el.get('Newslatter_id')) && el.get('Newslatter_IsActive') == 'false') {
				records.push(el.get('Newslatter_id'));
			}
		});
		
		if (!records.length) {
			return false;
		}
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							win.PersonNewslatterGrid.removeAll({ clearAll: true });
							win.NewslatterGrid.loadData();
						},
						params: {Newslatter_ids: Ext.util.JSON.encode(records)},
						url: '/?c=Newslatter&m=activate'
					});	
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Активировать выбранные рассылки?',
			title: 'Вопрос'
		});
		
	},
	openNewslatterEditWindow: function(action) {
		var grid = this.NewslatterGrid.getGrid();
		var record;

		var params = new Object();
		params.action = action;
		params.formParams = new Object();

		if (action != 'add') {
			var records = this.NewslatterGrid.getMultiSelections();
			if (records.length == 1) {
				record = records[0];
			} else {
				record = grid.getSelectionModel().getSelected();
			}
			if (!record.get('Newslatter_id')) { return false; }
			params.Newslatter_id = record.get('Newslatter_id');
			params.NewslatterGroupType_id = record.get('NewslatterGroupType_id');
		}

		params.callback = function(){
			this.NewslatterGrid.getAction('action_refresh').execute();
		}.createDelegate(this);
		params.currentForm = "swNewslatterListWindow";
		if(action != 'view') params.action = (params.Newslatter_id) ? 'edit' : 'add';
		getWnd('swNewslatterEditWindow').show(params);
	},
	doResetFilters: function() {
	
		var base_form = this.filtersPanel.getForm();
		base_form.reset();
	},
	reloadPersonNewslatterGrid: function(Newslatter_id) {
	
		if (!Newslatter_id) {
			return false;
		}
		
		this.PersonNewslatterGrid.removeAll({ clearAll: true });
		this.PersonNewslatterGrid.loadData({ globalFilters: {Newslatter_id : Newslatter_id} });
	},
	doFilter: function() {
	
		var base_form = this.filtersPanel.getForm();
		var filters = base_form.getValues();
		filters.start = 0;
		filters.limit = 100;

		this.NewslatterGrid.removeAll({ clearAll: true });
		this.PersonNewslatterGrid.removeAll({ clearAll: true });

		this.NewslatterGrid.loadData({ globalFilters: filters });
	},
	initComponent: function()
	{
		var win = this;

		this.filtersPanel = new Ext.FormPanel({
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 100,
			frame: true,
			border: false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e)
				{
					win.doFilter();
				},
				stopEvent: true
			}],
			items: [{
				buttons: [{
					text: BTN_FIND,
					tabIndex: TABINDEX_RRLW + 10,
					handler: function() {
						win.doFilter();
					},
					iconCls: 'search16'
				}, {
					text: BTN_RESETFILTER,
					tabIndex: TABINDEX_RRLW + 11,
					handler: function() {
						win.doResetFilters();
						win.doFilter();
					},
					iconCls: 'resetsearch16'
				}, '-'],
				xtype: 'fieldset',
				autoHeight: true,
				collapsible: true,
				listeners: {
					collapse: function(p) {
						win.doLayout();
					},
					expand: function(p) {
						win.doLayout();
					}
				},
				title: 'Фильтр',
				items: [{
					border: false,
					layout: 'column',
					labelWidth: 120,
					anchor: '-10',
					items: [{
						layout: 'form',
						columnWidth: .30,
						border: false,
						items: [{
							name: 'Newslatter_insDT',
							fieldLabel: 'Дата создания',
							xtype: 'daterangefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							width: 180
						}, {
							name: 'Newslatter_Date',
							fieldLabel: 'Период рассылки',
							xtype: 'daterangefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							width: 180
						}, {
							hiddenName: 'NewslatterType_id',
							fieldLabel: 'Тип рассылки',
							codeField: 'NewslatterType_Code',
							displayField: 'NewslatterType_Name',
							valueField: 'NewslatterType_id',
							editable: false,
							store: new Ext.data.Store({
								autoLoad: true,
								baseParams: {Object: 'NewslatterType', NewslatterType_id: '', NewslatterType_Name: ''},
								reader: new Ext.data.JsonReader({
									id: 'NewslatterType_id'
								}, [
									{ name: 'NewslatterType_id', mapping: 'NewslatterType_id' },
									{ name: 'NewslatterType_Code', mapping: 'NewslatterType_Code' },
									{ name: 'NewslatterType_Name', mapping: 'NewslatterType_Name' }
								]),
								url: C_GETOBJECTLIST
							}),
							xtype: 'swbaselocalcombo',
							width: 180
						}]
					}, {
						layout: 'form',
						columnWidth: .40,
						border: false,
						items: [{
							allowBlank: false,
							hiddenName: 'Newslatter_IsActive',
							fieldLabel: 'Активность',
							xtype: 'swbaselocalcombo',
							displayField: 'name',
							valueField: 'code',
							editable: false,
							store: new Ext.data.SimpleStore({
								id: 0,
								fields: ['code','name'],
								data: [
									[0, 'Все'],
									[2, 'Активные'],
									[1, 'Неактивные']
								]
							}),
							value: 0,
							width: 150
						}, {
							name: 'Person_Fio',
							fieldLabel: 'ФИО',
							xtype: 'textfieldpmw',
							width: 300
						}, {
							name: 'Newslatter_Text',
							fieldLabel: 'Текст сообщения',
							xtype: 'textfield',
							width: 300
						}]
					}]
				}]
			}]
		});

		this.NewslatterGrid = new sw.Promed.ViewFrame({
			id: win.id+'NewslatterGrid',
			selectionModel: 'multiselect2',
			title:'',
			object: 'Newslatter',
			dataUrl: '/?c=Newslatter&m=loadList',
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			toolbar: true,
			useEmptyRecord: false,
			noSelectFirstRowOnFocus: true,
			stringfields: [
				{name: 'Newslatter_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'NewslatterGroupType_id', type: 'int', hidden: true},
				{name: 'Newslatter_insDT', header: 'Дата создания', width: 100},
				{name: 'Newslatter_Date', header: 'Период рассылки', width: 150},
				{name: 'Newslatter_Time', header: 'Время рассылки', width: 150},
				{name: 'PMUser_Name', header: 'Пользователь', width: 150},
				{name: 'Newslatter_Text', header: 'Текст', id: 'autoexpand', width: 80},
				{name: 'Newslatter_IsActive', header: 'Активность', type: 'checkbox', width: 80}
			],
			onRowSelect: function(sm,index,record) {
				win.PersonNewslatterGrid.removeAll({ clearAll: true });
				if (record && record.get('Newslatter_id')) {
					win.reloadPersonNewslatterGrid(record.get('Newslatter_id'));
				}			
			},
			onRowDeSelect: function() {
				this.onRowSelect();
			},
			actions: [
				{name:'action_add', disabled: false, handler: function() { win.openNewslatterEditWindow('add'); }},
				{name:'action_edit', handler: function() { win.openNewslatterEditWindow('edit'); }},
				{name:'action_view', handler: function() { win.openNewslatterEditWindow('view'); }},
				{name:'action_delete', handler: function() { win.deleteNewslatter(); }},
				{name:'action_print', disabled: true, hidden: true}
			]
		});

		this.PersonNewslatterGrid = new sw.Promed.ViewFrame({
			id: win.id+'PersonNewslatterGrid',
			title: 'Список пациентов',
			object: 'PersonNewslatter',
			dataUrl: '/?c=Newslatter&m=loadPersonNewslatterList',
			autoLoadData: false,
			toolbar: false,
			region: 'south',
			useEmptyRecord: false,
			stringfields: [
				{name: 'PersonNewslatter_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'NewslatterAccept_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Person_Fio', header: 'ФИО', width: 400},
				{name: 'Person_Birthday', header: 'Дата рождения', width: 200}
			]
		});

		this.formPanel = new Ext.Panel({
			region: 'center',
			labelAlign: 'right',
			layout: 'border',
			labelWidth: 50,
			border: false,
			items: [
				this.filtersPanel,
				this.NewslatterGrid,
				this.PersonNewslatterGrid
			]
		});

		Ext.apply(this, {
		items: [
			win.formPanel
		],
		buttons: [{
				text: '-'
			},
			HelpButton(this, TABINDEX_RRLW + 13),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RRLW + 14,
				handler: function() {
					win.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swNewslatterListWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swNewslatterListWindow.superclass.show.apply(this, arguments);
		
		this.NewslatterGrid.addActions({name:'action_cancel_nl', text: 'Отменить', iconCls: 'delete16', handler: function() { this.cancelNewslatter(); }.createDelegate(this)}, 5);
		this.NewslatterGrid.addActions({name:'action_activate', text: 'Активировать', iconCls: 'ok16', handler: function() { this.activateNewslatter(); }.createDelegate(this)}, 6);

		this.doResetFilters();
		this.doFilter();
	}
});