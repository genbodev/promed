/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 25.03.16
 * Time: 12:21
 * To change this template use File | Settings | File Templates.
 */

sw.Promed.swWorkGraphSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	deleteWorkGraph: function() {
		var grid = this.SearchGrid.getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['При удаленни строки графика дежурств возникли ошибки']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'],'Не выбрана строка графика дежурств');
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var id = selected_record.get('WorkGraph_id');

		if ( !id ) {
			return false;
		}

		var error = '';
		var params = new Object();
		params.WorkGraph_id = id;
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();

							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}
							}
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}.createDelegate(this),
						url: C_WORKGRAPH_DEL
					});
				}
				else {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: 'Вы действительно хотите удалить эту запись?',
			title: lang['vopros']
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['podojdite'] });
		}

		return this.loadMask;
	},
	height: 500,
	id: 'WorkGraphSearchWindow',
	initComponent: function() {
		var win = this;
		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'left',
			//labelWidth: 210,
			id: 'EvnStickFilterForm',
			region: 'north',

			items: [{
				layout: 'column',
				border: false,
				items: [{
					//bodyStyle: 'padding-right: 5px;',
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: lang['sotrudnik'],
						hiddenName: 'MedStaffFact_id',
						id: 'EStWREF_MedPersonalCombo',
						lastQuery: '',
						listWidth: 500,
						tabIndex: TABINDEX_ESTWREF + 4,
						width: 500,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						fieldLabel: 'Дата дежурства',
						format: 'd.m.Y',
						name: 'WorkGraph_Date',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}, {
						listWidth: 500,
						hiddenName:'LpuBuilding_id',
						id: 'WGSW_LpuBuildingCombo',
						fieldLabel: 'Подразделение',
						width: 500,
						xtype: 'swlpubuildingglobalcombo',
						linkedElements: [
							'WGSW_LpuSectionCombo'
						]
					},
					{
						listWidth: 500,
						xtype: 'swlpusectionglobalcombo',
						width: 500,
						hiddenName: 'LpuSection_id',
						id: 'WGSW_LpuSectionCombo',
						parentElementId: 'WGSW_LpuBuildingCombo',
						allowBlank: true
					}]
				}]
			}], keys: [{
				fn: function() {
					win.loadGridWithFilter();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		var base_form = this.FilterPanel.getForm();

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openWorkGraphEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openWorkGraphEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openWorkGraphEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteWorkGraph(); }.createDelegate(this), hidden: false, disabled: true }
			],
			autoLoadData: false,
			dataUrl: '/?c=Common&m=loadWorkGraphGrid',
			height: 203,
			id: this.id + 'SearchGrid',
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
				else {
					this.ViewActions.action_view.execute();
				}
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				var options = getGlobalOptions();
				var date = Date.parseDate(options['date'], 'd.m.Y').format('Y-m-d');
				if(!Ext.isEmpty(record) && !Ext.isEmpty(record.get('WorkGraph_id')))
				{
					var beg_date = record.get('WorkGraph_begDate').format('Y-m-d');
					var end_date = record.get('WorkGraph_endDate').format('Y-m-d');
					if(date < beg_date){
						win.SearchGrid.getAction('action_delete').setDisabled(false);
						win.SearchGrid.getAction('action_edit').setDisabled(false);
					}
					else{
						if(date <= end_date){
							win.SearchGrid.getAction('action_edit').setDisabled(false);
							win.SearchGrid.getAction('action_delete').setDisabled(true);
						}
						else
						{
							win.SearchGrid.getAction('action_edit').setDisabled(true);
							win.SearchGrid.getAction('action_delete').setDisabled(true);
						}
					}
				}
			},
			paging: true,
			pageSize: 100,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'WorkGraph_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_SurName', type: 'string', header: lang['familiya'], width: 100 },
				{ name: 'Person_FirName', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'Person_SecName', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'WorkGraph_begDate', type: 'date', format: 'd.m.Y', header: 'Дата начала дежурства', width: 100 },
				{ name: 'WorkGraph_endDate', type: 'date', format: 'd.m.Y', header: 'Дата окончания дежурства', width: 100 },
				{ name: 'PMUser_Name', type: 'string', header: 'Пользователь, добавивший запись', width: 200 },
				{ name: 'WorkGraph_Sections', type: 'string', header: 'Отделения', width: 600 }
			],
			title: 'График дежурств: Поиск',
			totalProperty: 'totalCount'
		});

		this.CancelButton = new Ext.Button({
			handler: function()  {
				this.hide()
			}.createDelegate(this),
			id: 'ESVW_CancelButton',
			iconCls: 'close16',
			text: BTN_FRMCLOSE
		});

		this.SearchGrid.focusPrev = this.CancelButton;
		this.SearchGrid.focusPrev.type = 'button';
		this.SearchGrid.focusPrev.name = this.SearchGrid.focusPrev.id;
		this.SearchGrid.focusOn = this.CancelButton;
		this.SearchGrid.focusOn.type = 'button';
		this.SearchGrid.focusOn.name = this.SearchGrid.focusOn.id;

		Ext.apply(this, {
			buttons: [
				{
					text: BTN_FRMSEARCH,
					handler: function(){
						this.loadGridWithFilter();
					}.createDelegate(this),
					iconCls: 'search16'
				},
				{
					text: lang['sbros'],
					handler: function(){
						this.loadGridWithFilter(true);
					}.createDelegate(this),
					iconCls: 'resetsearch16'
				},
				{
					text: '-'
				},
				HelpButton(this),
				this.CancelButton],
			defaults: {
				split: true
			},
			layout: 'border',
			items: [ this.FilterPanel,
				{
					border: false,
					layout: 'border',
					region: 'center',
					xtype: 'panel',

					items: [
						this.SearchGrid
					]
				}]
		});

		sw.Promed.swWorkGraphSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'beforeshow': function() {
			//
		}
	},
	loadGridWithFilter: function(clear) {
		var base_form = this.FilterPanel.getForm();

		this.SearchGrid.getGrid().getStore().removeAll();


		if ( clear ) {
			base_form.reset();
		}
			var params = base_form.getValues();

			params.limit = 100;
			params.start = 0;
			this.SearchGrid.getGrid().getStore().removeAll();
			this.SearchGrid.getGrid().getStore().load({
				params: params
			});
	},
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	openWorkGraphEditWindow: function(action) {
		var base_form = this.FilterPanel.getForm();
		var grid = this.SearchGrid.getGrid();
		var formParams = new Object();
		var params = new Object();
		params.WorkGraph_id = null;
		if(action!='add')
		{
			var selected_record = grid.getSelectionModel().getSelected();
				if ( !selected_record || !selected_record.get('WorkGraph_id') ) {
					return false;
				}
			params.WorkGraph_id = selected_record.get('WorkGraph_id');
		}
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		}.createDelegate(this);
		getWnd('swWorkGraphEditWindow').show(params);
	},
	resizable: false,
	show: function() {
		sw.Promed.swWorkGraphSearchWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();
		var base_form = this.FilterPanel.getForm();
		this.center();
		this.maximize();
		this.loadGridWithFilter();

		this.CurLpuSection_id = 0;
		this.CurLpuUnit_id = 0;
		this.CurLpuBuilding_id = 0;
		this.StickReg = 0;
		if(arguments[0])
		{
			if(arguments[0].CurLpuSection_id)
				this.CurLpuSection_id = arguments[0].CurLpuSection_id;
			if(arguments[0].CurLpuUnit_id)
				this.CurLpuUnit_id = arguments[0].CurLpuUnit_id;
			if(arguments[0].CurLpuBuilding_id)
				this.CurLpuBuilding_id = arguments[0].CurLpuBuilding_id;
			if(arguments[0].StickReg)
				this.StickReg = arguments[0].StickReg;
		}
		setMedStaffFactGlobalStoreFilter({
			isOnlyStac: true
		});
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		//base_form.findField('MedStaffFact_id').getStore().loadData();
		this.getLoadMask().hide();


		base_form.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
		base_form.findField('LpuBuilding_id').getStore().load();
		setLpuSectionGlobalStoreFilter({
			isOnlyStac: true
		});
		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
	},
	title: 'Графики дежурств',
	width: 800
});