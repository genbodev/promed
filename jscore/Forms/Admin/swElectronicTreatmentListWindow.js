/**
 * swElectronicTreatmentListWindow - список поводов обращений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 */
sw.Promed.swElectronicTreatmentListWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	height: 600,
	id: 'swElectronicTreatmentListWindow',
	layout: 'border',
	maximizable: false,
	maximized: true,
	resizable: true,
	title: 'Справочник поводов обращений',
	width: 900,

	/* методы */
	deleteElectronicTreatment: function() {
		var wnd = this,
			grid = this.ElectronicTreatmentGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected()
			|| !grid.getSelectionModel().getSelected().get('ElectronicTreatment_id')
		) {
			return false;
		}

		var
			record = grid.getSelectionModel().getSelected(),
			ElectronicTreatment_id = record.get('ElectronicTreatment_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							grid.getStore().remove(record);
						},
						params: {
							ElectronicTreatment_id: ElectronicTreatment_id
						},
						url: '/?c=ElectronicTreatment&m=delete'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_vyibrannuyu_zapis'],
			title: lang['vopros']
		});
	},
	deleteElectronicTreatmentGroup: function() {
		var wnd = this,
			grid = this.ElectronicTreatmentGroupGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected()
			|| !grid.getSelectionModel().getSelected().get('ElectronicTreatment_id')
		) {
			return false;
		}

		var ElectronicTreatment_id = grid.getSelectionModel().getSelected().get('ElectronicTreatment_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							wnd.doSearch();
						},
						params: {
							ElectronicTreatment_id: ElectronicTreatment_id
						},
						url: '/?c=ElectronicTreatment&m=delete'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_vyibrannuyu_zapis'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var
			base_form = this.FilterPanel.getForm(),
			wnd = this;

		base_form.reset();
		this.ElectronicTreatmentGroupGrid.removeAll();
		this.ElectronicTreatmentGrid.removeAll();

		if ( wnd.mode != 'SuperAdmin' ) {
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
		}
	},
	doSearch: function() {
		var
			wnd = this,
			params = {};


		params.Lpu_id = this.FilterPanel.getForm().findField('Lpu_id').getValue();
		params.limit = 100;
		params.start = 0;

		wnd.ElectronicTreatmentGroupGrid.loadData({
			globalFilters: params
		});
	},
	openElectronicTreatmentEditWindow: function(action) {
		var
			grid = this.ElectronicTreatmentGrid.getGrid(),
			params = new Object(),
			parentGrid = this.ElectronicTreatmentGroupGrid.getGrid(),
			wnd = this;

		params.action = action;

		if (action != 'add') {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ElectronicTreatment_id') ) {
				return false;
			}

			params.formParams = grid.getSelectionModel().getSelected().data;
			params.formParams.LpuBuilding_id = parentGrid.getSelectionModel().getSelected().get('LpuBuilding_id');

		} else {

			if (!parentGrid.getSelectionModel().getSelected()
				|| !parentGrid.getSelectionModel().getSelected().get('ElectronicTreatment_id')
			) {
				return false;
			}

			params.formParams = {
				ElectronicTreatment_pid: parentGrid.getSelectionModel().getSelected().get('ElectronicTreatment_id'),
				LpuBuilding_id: parentGrid.getSelectionModel().getSelected().get('LpuBuilding_id'),
				Lpu_id: parentGrid.getSelectionModel().getSelected().get('Lpu_id')
			};
		}

		params.callback = function() {
			grid.getStore().reload();
		};
		params.mode = wnd.mode;

		log('sel', parentGrid.getSelectionModel().getSelected());
		getWnd('swElectronicTreatmentEditWindow').show(params);
	},
	openElectronicInfomatTreatmentLinkEditWindow: function(action) {

		var grid = this.ElectronicInfomatTreatmentLinkGrid.getGrid(),
			parentGrid = this.ElectronicTreatmentGroupGrid.getGrid(),
			selectedTreatment = parentGrid.getSelectionModel().getSelected(),
			wnd = this;

		var params = new Object();
		params.action = action;

		if ( action == 'add' ) {

			if ( !selectedTreatment || !selectedTreatment.get('ElectronicTreatment_id')
			) { return false; }

			params.ElectronicTreatment_id = selectedTreatment.get('ElectronicTreatment_id');
			params.Lpu_id = selectedTreatment.get('Lpu_id');

			params.callback = function() { grid.getStore().reload(); };
			getWnd('swElectronicInfomatTreatmentLinkEditWindow').show(params);
		}
	},
	openElectronicTreatmentGroupEditWindow: function(action) {
		var
			grid = this.ElectronicTreatmentGroupGrid.getGrid(),
			params = new Object(),
			wnd = this;

		params.action = action;

		if ( action != 'add' ) {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ElectronicTreatment_id') ) {
				return false;
			}

			params.formParams = grid.getSelectionModel().getSelected().data;
		}
		else {
			params.formParams = {
				ElectronicTreatment_id: null
			};
		}

		params.callback = function() {
			wnd.doSearch();
		};
		params.mode = wnd.mode;

		getWnd('swElectronicTreatmentGroupEditWindow').show(params);
	},
	show: function() {

		sw.Promed.swElectronicTreatmentListWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		this.mode = 'SuperAdmin';

		if ( arguments && arguments[0] && arguments[0].mode ) {
			this.mode = arguments[0].mode;
		}

		if ( wnd.mode == 'SuperAdmin' ) { this.FilterPanel.getForm().findField('Lpu_id').enable(); }
		else { this.FilterPanel.getForm().findField('Lpu_id').disable(); }

		this.doReset();
		this.doSearch();
	},

	/* конструктор */
	initComponent: function() {
		var wnd = this;

		this.FilterPanel = new Ext.FormPanel({
			autoHeight: true,
			frame: true,
			id: wnd.id + 'FilterPanel',
			labelAlign: 'right',
			labelWidth: 100,
			region: 'north',

			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						fieldLabel: lang['mo'],
						hiddenName: 'Lpu_id',
						width: 300,
						xtype: 'swlpucombo'
					}]
				}, {
					layout: 'form',
					items: [{
						handler: function() {
							this.doSearch();
						}.createDelegate(this),
						iconCls: 'search16',
						id: wnd.id + 'SearchButton',
						style: 'padding-left: 20px;',
						text: BTN_FRMSEARCH,
						xtype: 'button'
					}]
				}, {
					layout: 'form',
					items: [{
						handler: function() {
							this.doReset();
						}.createDelegate(this),
						iconCls: 'resetsearch16',
						id: wnd.id + 'ResetButton',
						style: 'padding-left: 5px;',
						text: BTN_FRMRESET,
						xtype: 'button'
					}]
				}]
			}],
			keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.ElectronicTreatmentGroupGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add', handler: function() { wnd.openElectronicTreatmentGroupEditWindow('add'); }},
				{name:'action_edit', handler: function() { wnd.openElectronicTreatmentGroupEditWindow('edit'); }},
				{name:'action_view', handler: function() { wnd.openElectronicTreatmentGroupEditWindow('view'); }},
				{name:'action_delete', handler: function() { wnd.deleteElectronicTreatmentGroup(); }},
				{name:'action_refresh', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true}
			],
			autoLoadData: false,
			dataUrl: '/?c=ElectronicTreatment&m=loadList',
			id: wnd.id + 'ElectronicTreatmentGroupGrid',
			object: 'ElectronicTreatment',
			onRowSelect: function (sm,index,record) {
				wnd.ElectronicTreatmentGrid.loadData({
					globalFilters: {
						ElectronicTreatment_pid: record.get('ElectronicTreatment_id'),
						limit: 100,
						start: 0
					}
				});

				//wnd.ElectronicInfomatTreatmentLinkGrid.loadData({
				//	globalFilters: {
				//		ElectronicTreatment_id: record.get('ElectronicTreatment_id'),
				//		limit: 100,
				//		start: 0
				//	}
				//});
			},
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'ElectronicTreatment_id', type: 'int', key: true, hidden: true},
				{name: 'LpuBuilding_id', type: 'int', hidden: true},
				{name: 'Lpu_Nick', header: 'МО', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 200},
				{name: 'ElectronicTreatment_Code', header: 'Код', width: 100},
				{name: 'ElectronicTreatment_Name', header: 'Наименование', width: 200, id: 'autoexpand'},
				{name: 'ElectronicTreatment_begDate', header: 'Дата начала', width: 100, type: 'date'},
				{name: 'ElectronicTreatment_endDate', header: 'Дата окончания', width: 100, type: 'date'},
				{name: 'Lpu_id', header: 'Идентификатор МО', hidden: true, type: 'int'},
				{name: 'ElectronicTreatmentLevel_id', header: 'Уровень', hidden: true, type: 'int'},
				{name: 'ElectronicTreatment_Descr', header: 'Примечание', hidden: true, type: 'string'}
			],
			title: 'Группа повода обращения',
			toolbar: true,
			totalProperty: 'totalCount',
			useEmptyRecord: false
		});

		//this.ElectronicInfomatTreatmentLinkGrid = new sw.Promed.ViewFrame({
		//	autoLoadData: false,
		//	dataUrl: '/?c=ElectronicTreatment&m=loadElectronicInfomatTreatmentLink',
		//	height: 250,
		//	id: wnd.id + 'ElectronicInfomatTreatmentLink',
		//	object: 'ElectronicInfomatTreatmentLink',
		//	paging: true,
		//	region: 'north',
		//	root: 'data',
		//	title: 'Связанные инфоматы',
		//	toolbar: true,
		//	totalProperty: 'totalCount',
		//	useEmptyRecord: false,
        //
		//	onRowSelect: function (sm,index,record) {},
		//	stringfields: [
		//		{name: 'ElectronicInfomatTreatmentLink_id', type: 'int', header: 'Номер', key: true, hidden: true},
		//		{name: 'ElectronicInfomat_id',header: 'ID', type: 'int'},
		//		{name: 'ElectronicTreatment_id', type: 'int', hidden: true},
		//		{name: 'LpuBuilding_id', type: 'int', hidden: true},
		//		{name: 'ElectronicInfomat_Name', header: 'Наименование', type: 'string', width: 200},
		//		{name: 'LpuBuilding_Name', header: 'Подразделение', type: 'string', width: 200},
		//		{name: 'LpuBuilding_Address', header: 'Адрес', type: 'string', id: 'autoexpand'}
		//	],
		//	actions: [
		//		{name:'action_add', handler: function() { wnd.openElectronicInfomatTreatmentLinkEditWindow('add'); }},
		//		{name:'action_edit', disabled: true, hidden: true},
		//		{name:'action_view', disabled: true, hidden: true},
		//		{name:'action_delete'},
		//		{name:'action_refresh', disabled: true, hidden: true},
		//		{name:'action_print', disabled: true, hidden: true}
		//	]
		//});

		this.ElectronicTreatmentGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			dataUrl: '/?c=ElectronicTreatment&m=loadList',
			id: wnd.id + 'ElectronicTreatmentGrid',
			object: 'ElectronicTreatment',
			paging: true,
			region: 'south',
			root: 'data',
			height: 300,
			title: 'Повод обращения',
			toolbar: true,
			totalProperty: 'totalCount',
			useEmptyRecord: false,

			onRowSelect: function (sm,index,record) {},
			stringfields: [
				{name: 'ElectronicTreatment_id', type: 'int', header: 'Номер', key: true, hidden: true},
				{name: 'ElectronicTreatment_pid', type: 'int', hidden: true},
				{name: 'ElectronicTreatmentLevel_id', type: 'int', hidden: true},
				{name: 'ElectronicTreatment_isConfirmPage', type: 'int', hidden: true},
				{name: 'ElectronicTreatment_isFIOShown', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'ElectronicTreatment_Descr', type: 'string', hidden: true},
				{name: 'ElectronicTreatment_Code', header: 'Код', width: 100},
				{name: 'ElectronicTreatment_Name', header: 'Наименование', width: 200, id: 'autoexpand'},
				{name: 'Lpu_Nick', header: 'МО', width: 200},
				{name: 'ElectronicQueues', header: 'Код ЭО', width: 200},
				{name: 'ElectronicTreatment_begDate', header: 'Дата начала', width: 100},
				{name: 'ElectronicTreatment_endDate', header: 'Дата окончания', width: 100}
			],
			actions: [
				{name:'action_add', handler: function() { wnd.openElectronicTreatmentEditWindow('add'); }},
				{name:'action_edit', handler: function() { wnd.openElectronicTreatmentEditWindow('edit'); }},
				{name:'action_view', handler: function() { wnd.openElectronicTreatmentEditWindow('view'); }},
				{name:'action_delete', handler: function() { wnd.deleteElectronicTreatment(); }},
				{name:'action_refresh', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true}
			]
		});


		//this.DependentPanel = new Ext.FormPanel({
		//	layout: 'border',
		//	height: 500,
		//	region: 'south',
		//	items: [
		//		this.ElectronicInfomatTreatmentLinkGrid,
		//		this.ElectronicTreatmentGrid
		//	]
		//});

		Ext.apply(this, {
			items: [
				wnd.FilterPanel,
				wnd.ElectronicTreatmentGroupGrid,
				wnd.ElectronicTreatmentGrid
			],
			buttons: [{text: '-'},
			HelpButton(this), {
				handler: function() {wnd.hide();},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swElectronicTreatmentListWindow.superclass.initComponent.apply(this, arguments);
	}
});