/**
 * swElectronicQueueListWindow - электронная очередь
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2015 Swan Ltd.
 */
/*NO PARSE JSON*/
sw.Promed.swElectronicQueueListWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: false,
	maximized: true,
	height: 600,
	width: 900,
	id: 'swElectronicQueueListWindow',
	title: 'Справочник электронных очередей',
	layout: 'border',
	resizable: true,
	deleteElectronicQueue: function() {
		var win = this,
			grid = this.ElectronicQueueGrid.getGrid();
		
		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ElectronicQueueInfo_id')) {
			return false;
		}
		
		var electronicqueueinfo_id = grid.getSelectionModel().getSelected().get('ElectronicQueueInfo_id');
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							win.doSearch();
						},
						params: {ElectronicQueueInfo_id: electronicqueueinfo_id},
						url: '/?c=ElectronicQueueInfo&m=delete'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_vyibrannuyu_zapis'],
			title: lang['vopros']
		});

	},
	openElectronicQueueEditWindow: function(action) {
		var win = this,
			grid = this.ElectronicQueueGrid.getGrid();

		var params = new Object();
		params.action = action;

		if (action != 'add') {
			if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ElectronicQueueInfo_id')) {
				return false;
			}
			
			params.ElectronicQueueInfo_id = grid.getSelectionModel().getSelected().get('ElectronicQueueInfo_id');
		}

		params.callback = function() {
			win.doSearch();
		};

		getWnd('swElectronicQueueEditWindow').show(params);
	},

	doSearch: function() {

		var wnd = this,
			filterForm = wnd.FilterPanel.getForm();

		var params = filterForm.getValues();

		params.start = 0;
		params.limit = 100;

		// т.к. поле c ЛПУ дизаблится, lpu_id в параметры передаем принудительно
		if (isLpuAdmin() && !isSuperAdmin()) {

			params.f_Lpu_id = getGlobalOptions().lpu_id;

			var lpuCombo = this.FilterPanel.getForm().findField('f_Lpu_id');
			var buildingCombo = this.FilterPanel.getForm().findField('LpuBuilding_id');
			var medserviceCombo = this.FilterPanel.getForm().findField('MedService_id');

			if (buildingCombo.getValue() > 0 || medserviceCombo.getValue() > 0)
				lpuCombo.fireEvent('change', lpuCombo, params.f_Lpu_id, params.f_Lpu_id);
			else {
				lpuCombo.fireEvent('change', lpuCombo, params.f_Lpu_id);
			}
		} else if (isSuperAdmin()) {
			params.f_Lpu_id = this.FilterPanel.getForm().findField('f_Lpu_id').getValue();
		}

		// Ставим заголовок фильтра
		this.setTitleFieldSet();
		wnd.ElectronicQueueGrid.loadData({globalFilters: params});
	},

	doReset: function() {

		var form = this;
		this.FilterPanel.getForm().reset();

		var lpuCombo = this.FilterPanel.getForm().findField('f_Lpu_id');
		var buildingCombo = this.FilterPanel.getForm().findField('LpuBuilding_id');
		var medserviceCombo = this.FilterPanel.getForm().findField('MedService_id');

		buildingCombo.getStore().removeAll();
		medserviceCombo.getStore().removeAll();

		lpuCombo.getStore().load(
			{
			callback: function () {
				if (isLpuAdmin() && !isSuperAdmin()) {
					lpuCombo.setValue(getGlobalOptions().lpu_id);
					lpuCombo.setDisabled(true);
				} else {
					lpuCombo.setValue(getGlobalOptions().lpu_id);
				}

				form.doSearch();
			}
		});
	},

	setTitleFieldSet: function() {
		var fieldSet = this.FilterPanel.find('xtype', 'fieldset')[0],
			enableFilter = false,
			title = lang['poisk_filtr'];

		fieldSet.findBy(function(f) {
			if( f.xtype && f.xtype.inlist(['textfield', 'swlpusearchcombo', 'swlpubuildingcombo', 'daterangefield']) ) {
				if( f.getValue() != '' && f.getValue() != null ) {
					enableFilter = true;
				}
			}
		});

		fieldSet.setTitle( title + ( enableFilter == true ? '' : 'не ' ) + 'установлен' );
	},

	initComponent: function()
	{
		var win = this;

		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			region: 'north',
			frame: true,
			items: [
				{
					layout: 'form',
					xtype: 'fieldset',
					autoHeight: true,
					collapsible: true,
					listeners: {
						collapse: function() {
							this.FilterPanel.doLayout();
							this.doLayout();
						}.createDelegate(this),
						expand: function() {
							this.FilterPanel.doLayout();
							this.doLayout();
						}.createDelegate(this)
					},
					labelAlign: 'right',
					title: lang['poisk_filtr_ne_ustanovlen'],
					items: [
						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									defaults: {
										anchor: '100%'
									},
									labelWidth: 100,
									width: 250,
									items: [
										{
											xtype: 'textfield',
											name: 'ElectronicQueueInfo_Code',
											fieldLabel: 'Код'
										}, {
											xtype: 'textfield',
											name: 'ElectronicQueueInfo_Name',
											fieldLabel: 'Наименование'
										},
										{
											xtype: 'textfield',
											name: 'ElectronicQueueInfo_Nick',
											fieldLabel: 'Краткое наименование'
										},
									]
								}, {
									layout: 'form',
									width: 320,
									defaults: {
										anchor: '100%'
									},
									items: [
										new sw.Promed.SwBaseLocalCombo ({
											hiddenName: 'f_Lpu_id',
											listWidth: 320,
											width: 320,
											displayField: 'Lpu_Nick',
											valueField: 'Lpu_id',
											editable: true,
											fieldLabel: lang['mo'],
											tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'{Lpu_Nick}&nbsp;',
												'</div></tpl>'
											),
											store: new Ext.data.SimpleStore({
												autoLoad: false,
												fields: [
													{name: 'Lpu_id', mapping: 'Lpu_id'},
													{name: 'Lpu_Name', mapping: 'Lpu_Name'},
													{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
												],
												key: 'Lpu_id',
												sortInfo: { field: 'Lpu_Nick' },
												url:'/?c=ElectronicQueueInfo&m=loadAllRelatedLpu'
											}),
											listeners: {
												'change': function (combo, newValue, oldValue) {

													var buildingCombo = win.FilterPanel.getForm().findField('LpuBuilding_id');
													var medserviceCombo = win.FilterPanel.getForm().findField('MedService_id');

													if (!newValue) {
														buildingCombo.clearValue();
														buildingCombo.getStore().removeAll();

														medserviceCombo.clearValue();
														medserviceCombo.getStore().removeAll();

													} else if (newValue != oldValue) {

														buildingCombo.clearValue();
														buildingCombo.getStore().baseParams.Lpu_id = newValue;
														buildingCombo.getStore().load();

														medserviceCombo.clearValue();
														medserviceCombo.getStore().baseParams.Lpu_id = newValue;
														medserviceCombo.getStore().load();
														//buildingCombo.getStore().load({
														//    params: {Lpu_id:newValue}
														//});

													} else {
														return false;
													}
												}
											}

										}),
										{
											xtype: 'swlpubuildingcombo',
											fieldLabel: 'Подразделение',
											hiddenName: 'LpuBuilding_id',
											listWidth: 320,
											width: 320
										},
										{
											xtype: 'swmedserviceglobalcombo',
											fieldLabel: 'Служба',
											hiddenName: 'MedService_id',
											listWidth: 320,
											width: 320,
										}
									]
								}, {
									layout: 'form',
									width: 300,
									labelWidth: 100,
									defaults: {
										anchor: '100%'
									},
									items: [
										{
											name: 'ElectronicQueueInfo_WorkRange',
											fieldLabel: 'Период работы',
											xtype: 'daterangefield',
											plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
										}
									]
								}
							]
						}, {
							layout: 'column',
							style: 'padding: 3px;',
							items: [
								{
									layout: 'form',
									items: [
										{
											handler: function() {
												this.doSearch();
											}.createDelegate(this),
											xtype: 'button',
											iconCls: 'search16',
											text: BTN_FRMSEARCH
										}
									]
								}, {
									layout: 'form',
									style: 'margin-left: 5px;',
									items: [
										{
											handler: function() {
												this.doReset();
											}.createDelegate(this),
											xtype: 'button',
											iconCls: 'resetsearch16',
											text: lang['sbros']
										}
									]
								}
							]
						}
					]
				}
			],
			keys: [{
				fn: function(inp, e) {
					this.doSearch();
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}]
		});

		this.ElectronicQueueGrid = new sw.Promed.ViewFrame({
			id: win.id+'ElectronicQueueGrid',
			title:'',
			object: 'ElectronicQueueInfo',
			dataUrl: '/?c=ElectronicQueueInfo&m=loadList',
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			toolbar: true,
			useEmptyRecord: false,
			onRowSelect: function (sm,index,record) {
				if (record.get('ElectronicQueueInfo_IsOn') == 'true') {
					this.setActionDisabled('action_enable', true);
					this.setActionDisabled('action_disable', false);
				} else {
					this.setActionDisabled('action_enable', false);
					this.setActionDisabled('action_disable', true);
				}
			},
			stringfields: [
				{name: 'ElectronicQueueInfo_id', type: 'int', header: 'ID', key: true, hidden: false, width: 50},
				{name: 'ElectronicQueueInfo_Code', header: 'Код', width: 50},
				{name: 'ElectronicQueueInfo_Name', header: 'Наименование', width: 200, id: 'autoexpand'},
				{name: 'ElectronicQueueInfo_Nick', header: 'Краткое наименование', width: 150, hidden: false},
				{name: 'Lpu_Nick', header: 'МО', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 150},
				{name: 'MedService_Nick', header: 'Служба', width: 150},
				{name: 'ElectronicQueueInfo_begDate', header: 'Дата начала', width: 100},
				{name: 'ElectronicQueueInfo_endDate', header: 'Дата окончания', width: 100},
				{name: 'ElectronicQueueInfo_IsOn', type: 'checkbox', header: 'Вкл.', width: 50}
			],
			actions: [
				{name:'action_add', handler: function() { win.openElectronicQueueEditWindow('add'); }},
				{name:'action_edit', handler: function() { win.openElectronicQueueEditWindow('edit'); }},
				{name:'action_view', handler: function() { win.openElectronicQueueEditWindow('view'); }},
				{name:'action_delete', handler: function() { win.deleteElectronicQueue(); }},
				{name:'action_print', disabled: true, hidden: true}
			]
		});

		Ext.apply(this, {
		items: [
			win.FilterPanel,
			win.ElectronicQueueGrid
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

		sw.Promed.swElectronicQueueListWindow.superclass.initComponent.apply(this, arguments);
	},
	setElectronicQueueInfoIsOff: function(options) {
		var win = this,
			grid = this.ElectronicQueueGrid.getGrid();

		var record = grid.getSelectionModel().getSelected();
		if (!record || !record.get('ElectronicQueueInfo_id')) {
			return false;
		}

		if (!options.ignoreOffQuestion) {
			var code = record.get('ElectronicQueueInfo_Code');
			var nick = record.get('ElectronicQueueInfo_Nick');
			if (options.ElectronicQueueInfo_IsOff == 1) {
				// Если у ЭО признак «Очередь выключена» изменилось на false
				sw.swMsg.alert('Внимание', 'Работа ЭО ' + code + ' ' + nick + ' возобновлена.');
			} else {
				// Если у ЭО признак «Очередь выключена» изменилось на true
				sw.swMsg.show({
					buttons: {yes: 'Продолжить', no: 'Отмена'},
					fn: function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							options.ignoreOffQuestion = 1;
							win.setElectronicQueueInfoIsOff(options);
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: 'ЭО ' + code + ' ' + nick + ' будет отключена. При отключении ЭО прием пациентов в автоматическом режиме недоступен.',
					title: 'Внимание'
				});
				return false;
			}
		}

		var ElectronicQueueInfo_id = record.get('ElectronicQueueInfo_id');

		win.getLoadMask('Пожалуйста, подождите...').show();
		Ext.Ajax.request({
			callback: function(opt, scs, response) {
				win.getLoadMask().hide();
				win.doSearch();
			},
			params: {
				ElectronicQueueInfo_id: ElectronicQueueInfo_id,
				ElectronicQueueInfo_IsOff: options.ElectronicQueueInfo_IsOff
			},
			url: '/?c=ElectronicQueueInfo&m=setElectronicQueueInfoIsOff'
		});
	},
	show: function() {
		sw.Promed.swElectronicQueueListWindow.superclass.show.apply(this, arguments);

		var win = this;
		this.ElectronicQueueGrid.addActions({
			name:'action_enable',
			text: 'Запустить ЭО',
			handler: function() {
				win.setElectronicQueueInfoIsOff({
					ElectronicQueueInfo_IsOff: 1
				});
			}
		});

		this.ElectronicQueueGrid.addActions({
			name:'action_disable',
			text: 'Отключить ЭО',
			handler: function() {
				win.setElectronicQueueInfoIsOff({
					ElectronicQueueInfo_IsOff: 2
				});
			}
		});


		var lpuCombo = this.FilterPanel.getForm().findField('f_Lpu_id');
			lpuCombo.setValue(getGlobalOptions().lpu_id);

		this.doReset();
	}
});