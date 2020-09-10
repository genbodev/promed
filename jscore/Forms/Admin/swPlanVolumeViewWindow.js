/**
* swPlanVolumeViewWindow - окно просмотра и редактирования плановых объёмов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      21.11.2018
*/

/*NO PARSE JSON*/
sw.Promed.swPlanVolumeViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	firstRun: true,
	height: 500,
	width: 800,
	title: langs('Планирование объёмов мед. помощи'),
	layout: 'border',
	//maximizable: true,
	maximized: true,
	modal: false,
	//plain: true,
	resizable: false,
	setPlanVolumeMode: function(mode) {
		if (mode) {
			this.planVolumeMode = mode;
			if (this.planVolumeMode == 'incoming') {
				this.PlanVolumeRequestTabPanel.setActiveTab(1);
			} else {
				this.PlanVolumeRequestTabPanel.setActiveTab(0);
			}
		}

		if (this.planVolumeMode == 'incoming') {
			this.PlanVolumeRequestTabPanel.hideTabStripItem('tab_PlanVolumeRequest_New');
		} else {
			this.PlanVolumeRequestTabPanel.unhideTabStripItem('tab_PlanVolumeRequest_New');
		}

		var panelId = this.PlanVolumeRequestTabPanel.getActiveTab().id;

		this.PlanVolumeRequestGrid.setActionHidden('action_accept', panelId != 'tab_PlanVolumeRequest_Queue' || this.planVolumeMode != 'incoming');
		this.PlanVolumeRequestGrid.setActionHidden('action_reject', panelId != 'tab_PlanVolumeRequest_Queue' || this.planVolumeMode != 'incoming');

		this.PlanVolumeRequestGrid.setActionHidden('action_add', panelId != 'tab_PlanVolumeRequest_New' || this.planVolumeMode != 'outcoming');
		this.PlanVolumeRequestGrid.setActionHidden('action_edit', panelId != 'tab_PlanVolumeRequest_New' || this.planVolumeMode != 'outcoming');
		this.PlanVolumeRequestGrid.setActionHidden('action_towork', panelId != 'tab_PlanVolumeRequest_New' || this.planVolumeMode != 'outcoming');
		this.PlanVolumeRequestGrid.setActionHidden('action_tonew', panelId != 'tab_PlanVolumeRequest_Queue' || this.planVolumeMode != 'outcoming');
		this.PlanVolumeRequestGrid.setActionHidden('action_editwork', panelId != 'tab_PlanVolumeRequest_Rejected' || this.planVolumeMode != 'outcoming');
		this.PlanVolumeRequestGrid.setActionHidden('action_delete', panelId != 'tab_PlanVolumeRequest_New' || this.planVolumeMode != 'outcoming');
	},
	show: function()
	{
		sw.Promed.swPlanVolumeViewWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.maximize();

		this.PlanVolumeFilterPanel.getForm().findField('Year').setValue(new Date().getFullYear());

		if (!haveArmType('spec_mz')) {
			this.PlanVolumeFilterPanel.getForm().findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			this.PlanVolumeFilterPanel.getForm().findField('Lpu_id').disable();
		}

		this.PlanVolumeGrid.setActionHidden('action_delete', !haveArmType('spec_mz') || getGlobalOptions().registry_mz_approve_lpu);

		this.PlanVolumeGrid.removeAll();
		this.loadPlanVolumeGrid();
	},
	deletePlanVolumeRequest: function(viewframe, doDelete) {
		if ( !haveArmType('spec_mz') || getGlobalOptions().registry_mz_approve_lpu ) {
			return false;
		}

		var
			win = this,
			grid = viewframe.getGrid(),
			record = grid.getSelectionModel().getSelected();

		if ( !record || !record.get('PlanVolumeRequest_id') ) {
			sw.swMsg.alert('Ошибка', 'Не выбран плановый объём');
			return false;
		}

		if ( !doDelete ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						win.deletePlanVolumeRequest(viewframe, true);
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: langs('Рекомендуется вносить изменения в существующий объём, а не удалять его, чтобы избежать ошибок в работе системы. Вы уверены, что необходимо удалить этот объём?'),
				title: langs('Внимание')
			});
			return true;
		}

		win.getLoadMask(LOAD_WAIT).show();

		Ext.Ajax.request({
			url: '/?c=PlanVolume&m=deletePlanVolumeRequest',
			params: {
				id: record.get('PlanVolumeRequest_id')
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					if ( result.success ) {
						win.loadPlanVolumeGrid();
					}
				}
			}
		});
	},
	openPlanVolumeRequestEditWindow: function(action, viewframe) {
		var win = this;

		var grid = viewframe.getGrid();
		var params = {
			action: action,
			callback: function() {
				grid.getStore().reload();
			}
		};

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record || !record.get('PlanVolumeRequest_id'))
			{
				sw.swMsg.alert('Ошибка', 'Не выбран плановый объём');
				return false;
			}

			params.PlanVolume_id = record.get('PlanVolume_id');
			params.PlanVolumeRequest_id = record.get('PlanVolumeRequest_id');
		}

		getWnd('swPlanVolumeRequestEditWindow').show(params);
	},
	setPlanVolumeRequestStatus: function(PlanVolumeRequestStatus_id) {
		var win = this;

		var record = this.PlanVolumeRequestGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('PlanVolumeRequest_id'))
		{
			sw.swMsg.alert('Ошибка', 'Не выбрана заявка');
			return false;
		}

		var msg = '';
		switch(PlanVolumeRequestStatus_id) {
			case 1:
				msg = 'Отозвать заявку?';
				break;
			case 2:
				msg = 'Отправить заявку на рассмотрение?';
				break;
			case 3:
				msg = 'Утвердить плановый объём?';
				break;
			case 4:
				msg = 'Отклонить заявку?';
				break;
		}

		var PlanVolumeRequest_id = record.get('PlanVolumeRequest_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					win.getLoadMask(LOAD_WAIT).show();
					Ext.Ajax.request({
						url: '/?c=PlanVolume&m=setPlanVolumeRequestStatus',
						params: {
							PlanVolumeRequest_id: PlanVolumeRequest_id,
							PlanVolumeRequestStatus_id: PlanVolumeRequestStatus_id
						},
						callback: function(options, success, response) {
							win.getLoadMask().hide();
							if (success) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (result.success) {
									win.loadPlanVolumeRequestGrid();
								}
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: msg,
			title: langs('Внимание')
		});
	},
	loadPlanVolumeGrid: function() {
		var form = this;
		var filtersForm = form.PlanVolumeFilterPanel.getForm();
		var params = filtersForm.getValues();
		params.start = 0;
		params.limit = 100;
		if (filtersForm.findField('Lpu_id').disabled) {
			params.Lpu_id = filtersForm.findField('Lpu_id').getValue();
		}

		if (haveArmType('spec_mz')) {
			params.Lpu_ids = params.Lpu_id;
			params.Lpu_id = '';
		}

		form.PlanVolumeGrid.loadData({
			globalFilters: params
		});
	},
	loadPlanVolumeRequestGrid: function() {
		var form = this;
		var filtersForm = form.PlanVolumeRequestFilterPanel.getForm();
		var params = filtersForm.getValues();
		params.start = 0;
		params.limit = 100;
		if (filtersForm.findField('Lpu_id').disabled) {
			params.Lpu_id = filtersForm.findField('Lpu_id').getValue();
		}

		if (haveArmType('spec_mz')) {
			params.Lpu_ids = params.Lpu_id;
			params.Lpu_id = '';
		}

		switch (form.PlanVolumeRequestTabPanel.getActiveTab().id) {
			case 'tab_PlanVolumeRequest_New':
				params.PlanVolumeRequestStatus_id = 1;
				break;
			case 'tab_PlanVolumeRequest_Queue':
				params.PlanVolumeRequestStatus_id = 2;
				break;
			case 'tab_PlanVolumeRequest_Accepted':
				params.PlanVolumeRequestStatus_id = 3;
				break;
			case 'tab_PlanVolumeRequest_Rejected':
				params.PlanVolumeRequestStatus_id = 4;
				break;
		}

		switch(form.planVolumeMode) {
			case 'incoming':
				if (haveArmType('spec_mz')) {
					params.PlanVolumeRequestSourceType_id = 1; // МО
				} else {
					params.PlanVolumeRequestSourceType_id = 2; // Минздрав
				}
				break;
			case 'outcoming':
				if (haveArmType('spec_mz')) {
					params.PlanVolumeRequestSourceType_id = 2; // Минздрав
				} else {
					params.PlanVolumeRequestSourceType_id = 1; // МО
				}
				break;
		}

		form.PlanVolumeRequestGrid.loadData({
			globalFilters: params
		});
	},
	addCloseFilterMenu: function(gridCmp){
		var form = this;
		var grid = gridCmp;

		if ( !grid.getAction('action_isclosefilter_'+grid.id) ) {
			var menuIsCloseFilter = new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: langs('Все'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = null;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Все</b>'));
							grid.getGrid().getStore().baseParams.isClose = null;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: langs('Открытые'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 1;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Открытые</b>'));
							grid.getGrid().getStore().baseParams.isClose = 1;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: langs('Закрытые'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 2;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Закрытые</b>'));
							grid.getGrid().getStore().baseParams.isClose = 2;
							grid.getGrid().getStore().reload();
						}
					})
				]
			});

			grid.addActions({
				isClose: null,
				name: 'action_isclosefilter_'+grid.id,
				text: langs('Показывать: <b>Все</b>'),
				menu: menuIsCloseFilter
			});
			grid.getGrid().getStore().baseParams.isClose = null;
		}

		return true;
	},
	planVolumeMode: 'incoming',
	initComponent: function()
	{
		var form = this;

		this.PlanVolumeRequestFilterPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 60,
			bodyStyle: 'background: transparent; padding: 5px;',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.loadPlanVolumeRequestGrid();
				},
				stopEvent: true
			}],
			listeners: {
				'render': function() {
					if (!haveArmType('spec_mz') && !getGlobalOptions().registry_mz_approve_lpu) {
						form.setPlanVolumeMode('outcoming');
					} else {
						form.setPlanVolumeMode('incoming');
					}
					form.PlanVolumeRequestFilterPanel.getForm().findField('Year').setValue(new Date().getFullYear());
					if (!haveArmType('spec_mz')) {
						form.PlanVolumeRequestFilterPanel.getForm().findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
						form.PlanVolumeRequestFilterPanel.getForm().findField('Lpu_id').disable();
					}
				}
			},
			items: [{
				layout: 'column',
				border: false,
				bodyStyle: 'background: transparent;',
				defaults: {
					labelAlign: 'right',
					bodyStyle: 'background: transparent; padding-left: 10px;'
				},
				items: [{
					layout: 'form',
					border: false,
					width: 400,
					labelWidth: 120,
					items: [{
						anchor: '100%',
						comboSubject: 'MedicalCareBudgType',
						ctxSerach: true,
						editable: true,
						enableKeyEvents: true,
						fieldLabel: 'Тип мед. помощи',
						hiddenName: 'MedicalCareBudgType_id',
						xtype: 'swcommonsprcombo'
					}, {
						anchor: '100%',
						fieldLabel: 'Вид оплаты',
						hiddenName: 'PayType_id',
						loadParams: {
							params: {where: getRegionNick() == 'kareliya' ? " where PayType_SysNick in ('bud', 'fbud', 'subrf')" : " where PayType_SysNick in ('bud', 'fbud')"}
						},
						xtype: 'swpaytypecombo'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 400,
					labelWidth: 130,
					items: [new Ext.ux.Andrie.Select({
						multiSelect: true,
						mode: 'local',
						allowBlank: true,
						fieldLabel: langs('МО'),
						hiddenName: 'Lpu_id',
						displayField: 'Lpu_Nick',
						valueField: 'Lpu_id',
						xtype:'swlpucombo',
						ctxSerach: true,
						enableKeyEvents: true,
						name: 'Lpu_id',
						anchor: '100%',
						store: new Ext.db.AdapterStore({

							dbFile: 'Promed.db',
							tableName: 'LpuSearch',
							key: 'Lpu_id',
							sortInfo: {field: 'Lpu_Nick'},
							autoLoad: false,
							fields: [
								{name: 'Lpu_id', mapping: 'Lpu_id'},
								{name: 'Lpu_IsOblast', mapping: 'Lpu_IsOblast'},
								{name: 'Lpu_Name', mapping: 'Lpu_Name'},
								{name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string'},
								{name: 'Lpu_Ouz', mapping: 'Lpu_Ouz'},
								{name: 'Lpu_RegNomC', mapping: 'Lpu_RegNomC'},
								{name: 'Lpu_RegNomC2', mapping: 'Lpu_RegNomC2'},
								{name: 'Lpu_RegNomN2', mapping: 'Lpu_RegNomN2'},
								{name: 'Lpu_DloBegDate', mapping: 'Lpu_DloBegDate'},
								{name: 'Lpu_DloEndDate', mapping: 'Lpu_DloEndDate'},
								{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
								{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate', type: 'string'},
								{name: 'Lpu_IsAccess', mapping: 'Lpu_IsAccess'}
							],
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{[(values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыта " + values.Lpu_EndDate + ")" : values.Lpu_Nick ]}&nbsp;',
							'</div></tpl>'
						)
					}), {
						anchor: '100%',
						comboSubject: 'QuoteUnitType',
						fieldLabel: 'Единица измерения',
						hiddenName: 'QuoteUnitType_id',
						loadParams: {
							params: {where: " where QuoteUnitType_Code in (1, 2, 3)"}
						},
						xtype: 'swcommonsprcombo'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 400,
					labelWidth: 30,
					items: [{
						fieldLabel: 'Год',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						plugins: [new Ext.ux.InputTextMask('9999', false)],
						minLength: 4,
						width: 70,
						name: 'Year',
						xtype: 'numberfield'
					}, {
						layout: 'column',
						border: false,
						bodyStyle: 'background: transparent;',
						defaults: {
							labelAlign: 'right',
							bodyStyle: 'background: transparent; padding-left: 10px;'
						},
						items: [{
							layout: 'form',
							border: false,
							items: [{
								tooltip: BTN_FRMSEARCH_TIP,
								xtype: 'button',
								text: BTN_FRMSEARCH,
								icon: 'img/icons/search16.png',
								iconCls: 'x-btn-text',
								handler: function() {
									form.loadPlanVolumeRequestGrid();
								}
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								xtype: 'button',
								text: BTN_FRMRESET,
								icon: 'img/icons/reset16.png',
								iconCls: 'x-btn-text',
								handler: function() {
									var filtersForm = form.PlanVolumeRequestFilterPanel.getForm();
									filtersForm.reset();
									filtersForm.findField('Year').setValue(new Date().getFullYear());
									if (!haveArmType('spec_mz')) {
										filtersForm.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
									}
									form.PlanVolumeRequestGrid.removeAll(true);
									form.loadPlanVolumeRequestGrid();
								}
							}]
						}]
					}]
				}]
			}]
		});

		this.PlanVolumeRequestGrid = new sw.Promed.ViewFrame({
			uniqueId: true,
			region: 'center',
			title: '',
			object: 'PlanVolumeRequest',
			editformclassname: 'swPlanVolumeRequestEditWindow',
			dataUrl: '/?c=PlanVolume&m=loadPlanVolumeRequestGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			onRowSelect: function(sm, rowIdx, record) {

			},
			stringfields: [
				{name: 'PlanVolumeRequest_id', type: 'int', header: 'PlanVolumeRequest_id', key: true},
				{name: 'Lpu_Nick', type: 'string', header: langs('МО'), width: 150, hidden: !haveArmType('spec_mz')},
				{name: 'PlanVolumeRequest_Num', type: 'string', header: langs('Номер заявки'), width: 150},
				{name: 'MedicalCareBudgType_Name', type: 'string', header: langs('Тип мед. помощи'), width: 300},
				{name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 120},
				{name: 'QuoteUnitType_Name', type: 'string', header: langs('Единица измерения'), width: 120},
				{name: 'PlanVolumeRequest_Value', type: 'float', header: langs('Значение'), width: 180},
				{name: 'PlanVolumeRequest_begDT', type: 'date', header: langs('Дата начала'), width: 120},
				{name: 'PlanVolumeRequest_endDT', type: 'date', header: langs('Дата окончания'), width: 120},
				{name: 'PlanVolume_Num', type: 'string', header: langs('Заменяет плановый объём'), width: 150}
			],
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=PlanVolume&m=deletePlanVolumeRequest'}
			]
		});

		this.PlanVolumeFilterPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 60,
			bodyStyle: 'background: transparent; padding: 5px;',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.loadPlanVolumeGrid();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				bodyStyle: 'background: transparent;',
				defaults: {
					labelAlign: 'right',
					bodyStyle: 'background: transparent; padding-left: 10px;'
				},
				items: [{
					layout: 'form',
					border: false,
					width: 400,
					labelWidth: 120,
					items: [{
						anchor: '100%',
						comboSubject: 'MedicalCareBudgType',
						ctxSerach: true,
						editable: true,
						enableKeyEvents: true,
						fieldLabel: 'Тип мед. помощи',
						hiddenName: 'MedicalCareBudgType_id',
						xtype: 'swcommonsprcombo'
					}, {
						anchor: '100%',
						fieldLabel: 'Вид оплаты',
						hiddenName: 'PayType_id',
						loadParams: {
							params: {where: getRegionNick() == 'kareliya' ? " where PayType_SysNick in ('bud', 'fbud', 'subrf')" : " where PayType_SysNick in ('bud', 'fbud')"}
						},
						xtype: 'swpaytypecombo'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 400,
					labelWidth: 130,
					items: [new Ext.ux.Andrie.Select({
						multiSelect: true,
						mode: 'local',
						allowBlank: true,
						fieldLabel: langs('МО'),
						hiddenName: 'Lpu_id',
						displayField: 'Lpu_Nick',
						valueField: 'Lpu_id',
                        anyMatch: true,
						xtype:'swlpucombo',
                        anyMatch: true,
						ctxSerach: true,
						enableKeyEvents: true,
						readOnly: false,
						name: 'Lpu_id',
						anchor: '100%',
						store: new Ext.db.AdapterStore({

							dbFile: 'Promed.db',
							tableName: 'LpuSearch',
							key: 'Lpu_id',
							sortInfo: {field: 'Lpu_Nick'},
							autoLoad: false,
							fields: [
								{name: 'Lpu_id', mapping: 'Lpu_id'},
								{name: 'Lpu_IsOblast', mapping: 'Lpu_IsOblast'},
								{name: 'Lpu_Name', mapping: 'Lpu_Name'},
								{name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string'},
								{name: 'Lpu_Ouz', mapping: 'Lpu_Ouz'},
								{name: 'Lpu_RegNomC', mapping: 'Lpu_RegNomC'},
								{name: 'Lpu_RegNomC2', mapping: 'Lpu_RegNomC2'},
								{name: 'Lpu_RegNomN2', mapping: 'Lpu_RegNomN2'},
								{name: 'Lpu_DloBegDate', mapping: 'Lpu_DloBegDate'},
								{name: 'Lpu_DloEndDate', mapping: 'Lpu_DloEndDate'},
								{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
								{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate', type: 'string'},
								{name: 'Lpu_IsAccess', mapping: 'Lpu_IsAccess'}
							],
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{[(values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыта " + values.Lpu_EndDate + ")" : values.Lpu_Nick ]}&nbsp;',
							'</div></tpl>'
						)
					}), {
						anchor: '100%',
						comboSubject: 'QuoteUnitType',
						fieldLabel: 'Единица измерения',
						hiddenName: 'QuoteUnitType_id',
						loadParams: {
							params: {where: " where QuoteUnitType_Code in (1, 2, 3)"}
						},
						xtype: 'swcommonsprcombo'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 140,
					labelWidth: 50,
					items: [{
						allowBlank: true,
						anchor: '100%',
						fieldLabel: 'Номер',
						name: 'PlanVolume_Num',
						xtype: 'numberfield'
					}, {
						fieldLabel: 'Год',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						plugins: [new Ext.ux.InputTextMask('9999', false)],
						minLength: 4,
						width: 70,
						name: 'Year',
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 100,
					labelWidth: 30,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						handler: function() {
							form.loadPlanVolumeGrid();
						}
					}, {
						xtype: 'button',
						text: BTN_FRMRESET,
						icon: 'img/icons/reset16.png',
						iconCls: 'x-btn-text',
						handler: function() {
							var filtersForm = form.PlanVolumeFilterPanel.getForm();
							filtersForm.reset();
							filtersForm.findField('Year').setValue(new Date().getFullYear());
							if (!haveArmType('spec_mz')) {
								filtersForm.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
							}
							form.PlanVolumeGrid.removeAll(true);
							form.loadPlanVolumeGrid();
						}
					}]
				}]
			}]
		});

		this.PlanVolumeGrid = new sw.Promed.ViewFrame({
			uniqueId: true,
			region: 'center',
			title: '',
			object: 'PlanVolume',
			dataUrl: '/?c=PlanVolume&m=loadPlanVolumeGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			onRowSelect: function(sm, rowIdx, record) {
				form.PlanVolumeGrid.setActionDisabled('action_delete', !haveArmType('spec_mz') || getGlobalOptions().registry_mz_approve_lpu || !record || record.get('PlanVolumeRequestSourceType_id') != 2);
			},
			stringfields: [
				{name: 'PlanVolume_id', type: 'int', header: 'PlanVolume_id', key: true},
				{name: 'PlanVolumeRequest_id', type: 'int', header: 'PlanVolumeRequest_id', hidden: true},
				{name: 'PlanVolumeRequestSourceType_id', type: 'int', header: 'PlanVolumeRequestSourceType_id', hidden: true},
				{name: 'Lpu_Nick', type: 'string', header: langs('МО'), width: 150, hidden: !haveArmType('spec_mz')},
				{name: 'PlanVolume_Num', type: 'string', header: langs('Номер объёма'), width: 150},
				{name: 'MedicalCareBudgType_Name', type: 'string', header: langs('Тип мед. помощи'), width: 300},
				{name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 120},
				{name: 'QuoteUnitType_Name', type: 'string', header: langs('Единица измерения'), width: 120},
				{name: 'PlanVolumeRequest_Value', type: 'float', header: langs('Значение'), width: 180},
				{name: 'PlanVolumeRequest_begDT', type: 'date', header: langs('Дата начала'), width: 120},
				{name: 'PlanVolumeRequest_endDT', type: 'date', header: langs('Дата окончания'), width: 120},
				{name: 'PlanVolumeRequest_Num', type: 'string', header: langs('Исходная заявка'), width: 150},
				{name: 'NextPlanVolumeRequest_Num', type: 'string', header: langs('Заявка на изменение'), width: 150}
			],
			actions: [
				{name: 'action_add', text: langs('Добавить объём'), disabled: !haveArmType('spec_mz'), hidden: !haveArmType('spec_mz'), handler: function() {
					form.openPlanVolumeRequestEditWindow('add', form.PlanVolumeGrid);
				}},
				{name: 'action_edit', text: langs('Изменить объём'), handler: function() {
					if (haveArmType('spec_mz') && !getGlobalOptions().registry_mz_approve_lpu) {
						form.openPlanVolumeRequestEditWindow('edit', form.PlanVolumeGrid);
					} else {
						form.openPlanVolumeRequestEditWindow('editvolume', form.PlanVolumeGrid);
					}
				}},
				{name: 'action_view', handler: function() {
					form.openPlanVolumeRequestEditWindow('view', form.PlanVolumeGrid);
				}},
				{name: 'action_delete', handler: function() {
					form.deletePlanVolumeRequest(form.PlanVolumeGrid);
				}, text: langs('Удалить объём')}
			]
		});

		this.PlanVolumeGrid.ViewToolbar.on('render', function(vt) {
			return this.addCloseFilterMenu(this.PlanVolumeGrid);
		}.createDelegate(this));

		this.PlanVolumeRequestGrid.ViewToolbar.on('render', function(vt) {
			if (!form.PlanVolumeRequestGrid.getAction('action_towork')) {
				form.PlanVolumeRequestGrid.addActions({
					name: 'action_towork',
					text: langs('Отправить на рассмотрение'),
					handler: function() {
						form.setPlanVolumeRequestStatus(2);
					}
				}, 3);
			}

			if (!form.PlanVolumeRequestGrid.getAction('action_tonew')) {
				form.PlanVolumeRequestGrid.addActions({
					name: 'action_tonew',
					text: langs('Отозвать'),
					handler: function() {
						form.setPlanVolumeRequestStatus(1);
					}
				}, 4);
			}

			if (!form.PlanVolumeRequestGrid.getAction('action_editwork')) {
				form.PlanVolumeRequestGrid.addActions({
					name: 'action_editwork',
					text: langs('Изменить и отправить повторно'),
					handler: function() {
						form.openPlanVolumeRequestEditWindow('editrequest', form.PlanVolumeRequestGrid);
					}
				}, 5);
			}

			if (!form.PlanVolumeRequestGrid.getAction('action_accept')) {
				form.PlanVolumeRequestGrid.addActions({
					name: 'action_accept',
					text: langs('Утвердить'),
					handler: function() {
						form.setPlanVolumeRequestStatus(3);
					}
				}, 6);
			}

			if (!form.PlanVolumeRequestGrid.getAction('action_reject')) {
				form.PlanVolumeRequestGrid.addActions({
					name: 'action_reject',
					text: langs('Отклонить'),
					handler: function() {
						form.openPlanVolumeRequestEditWindow('decline', form.PlanVolumeRequestGrid);
					}
				}, 7);
			}

			form.setPlanVolumeMode();

			return this.addCloseFilterMenu(this.PlanVolumeRequestGrid);
		}.createDelegate(this));

		this.PlanVolumeRequestTabPanel = new Ext.TabPanel({
			border: false,
			region: 'north',
			activeTab: 0,
			enableTabScroll: true,
			autoScroll: true,
			defaults: {bodyStyle: 'width:100%;'},
			layoutOnTabChange: true,
			height: 27,
			listeners: {
				tabchange: function(tab, panel) {
					if (form.PlanVolumeRequestGrid.rendered) {
						form.PlanVolumeRequestGrid.removeAll();
						form.loadPlanVolumeRequestGrid();
					}

					form.setPlanVolumeMode();
				}
			},
			items: [{
				title: 'Новые',
				id: 'tab_PlanVolumeRequest_New',
				border: false,
				items: []
			}, {
				title: 'Ожидают рассмотрения',
				id: 'tab_PlanVolumeRequest_Queue',
				border: false,
				items: []
			}, {
				title: 'Утверждённые',
				id: 'tab_PlanVolumeRequest_Accepted',
				border: false,
				items: []
			}, {
				title: 'Отклонённые',
				id: 'tab_PlanVolumeRequest_Rejected',
				border: false,
				items: []
			}]
		});

		this.DataTab = new Ext.TabPanel({
			border: false,
			region: 'center',
			activeTab: 0,
			enableTabScroll: true,
			autoScroll: true,
			defaults: {bodyStyle: 'width:100%;'},
			layoutOnTabChange: true,
			listeners: {
				tabchange: function(tab, panel) {
					switch(panel.id) {
						case 'tab_PlanVolume':
							form.PlanVolumeGrid.removeAll();
							form.loadPlanVolumeGrid();
							break;
						case 'tab_PlanVolumeRequest':
							form.PlanVolumeRequestGrid.removeAll();
							form.loadPlanVolumeRequestGrid();
							break;
					}
				}
			},
			items: [{
				title: 'Плановые объёмы',
				layout: 'border',
				id: 'tab_PlanVolume',
				border: false,
				frame: true,
				items: [
					this.PlanVolumeFilterPanel,
					this.PlanVolumeGrid
				]
			}, {
				title: 'Заявки',
				layout: 'border',
				id: 'tab_PlanVolumeRequest',
				border: false,
				frame: true,
				tbar: new Ext.Toolbar({
					items: [{
						text: langs('Входящие'),
						minWidth: 150,
						xtype: 'button',
						toggleGroup: 'planVolumeModeToggle',
						iconCls: '',
						pressed: haveArmType('spec_mz') || getGlobalOptions().registry_mz_approve_lpu,
						disabled: !haveArmType('spec_mz') && !getGlobalOptions().registry_mz_approve_lpu,
						handler: function()
						{
							form.setPlanVolumeMode('incoming');
							form.PlanVolumeRequestGrid.removeAll();
							form.loadPlanVolumeRequestGrid();
							this.toggle(true);
						}
					}, {
						text: langs('Исходящие'),
						minWidth: 150,
						xtype: 'button',
						toggleGroup: 'planVolumeModeToggle',
						iconCls: '',
						pressed: !haveArmType('spec_mz') && !getGlobalOptions().registry_mz_approve_lpu,
						disabled: haveArmType('spec_mz') && !getGlobalOptions().registry_mz_approve_lpu,
						handler: function()
						{
							form.setPlanVolumeMode('outcoming');
							form.PlanVolumeRequestGrid.removeAll();
							form.loadPlanVolumeRequestGrid();
							this.toggle(true);
						}
					}]
				}),
				items: [
					this.PlanVolumeRequestTabPanel, {
						layout: 'border',
						region: 'center',
						items: [
							this.PlanVolumeRequestFilterPanel,
							this.PlanVolumeRequestGrid
						]
					}
				]
			}]
		});

		Ext.apply(this,
		{
			layout:'border',
			defaults: {split: true},
			buttons:
			[{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items:
			[
				form.DataTab
			]
		});
		sw.Promed.swPlanVolumeViewWindow.superclass.initComponent.apply(this, arguments);
	}
});