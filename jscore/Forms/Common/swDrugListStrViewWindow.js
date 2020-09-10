/**
 * swDrugListStrViewWindow - окно просмотра списка медикаментов перечня
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			23.10.2017
 */
/*NO PARSE JSON*/

sw.Promed.swDrugListStrViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugListStrViewWindow',
	layout: 'border',
	title: 'Медикаменты перечня',
	maximizable: false,
	maximized: true,

	doSearch: function(reset) {
		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();
		var base_form = this.FilterPanel.getForm();

		if (reset) {
			base_form.reset();
		}

		var params = base_form.getValues();
		params.DrugList_id = this.DrugList_id;
		params.start = 0;
		params.limit = 100;

		grid_panel.loadData({params: params, globalFilters: params});
	},

	openDrugListStrEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return;
		}

		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();

		var params = {};
		params.action = action;
		params.DrugListType_Code = this.DrugListType_Code;
		params.formParams = {};
		params.callback = function() {
			grid_panel.getAction('action_refresh').execute();
		}.createDelegate(this);

		if (action == 'add') {
			params.formParams.DrugList_id = this.DrugList_id;
		} else  {
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('DrugListStr_id'))) {
				return;
			}
			params.formParams.DrugListStr_id = record.get('DrugListStr_id');
		}

		getWnd('swDrugListStrEditWindow').show(params);
	},

	deleteDrugListStr: function() {
		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('DrugListStr_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {DrugListStr_id: record.get('DrugListStr_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=DrugList&m=deleteDrugListStr'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	show: function() {
		sw.Promed.swDrugListStrViewWindow.superclass.show.apply(this, arguments);

		this.DrugList_id = null;
		this.DrugListType_Code = null;

		if (arguments[0] && arguments[0].DrugList_id) {
			this.DrugList_id = arguments[0].DrugList_id;
		}
		if (arguments[0] && arguments[0].DrugListType_Code) {
			this.DrugListType_Code = arguments[0].DrugListType_Code;
		}

		this.doSearch(true);
	},

	initComponent: function() {
		this.FilterPanel = new Ext.FormPanel({
			frame: false,
			id: 'DLSVW_FilterPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			layout: 'form',
			bodyStyle: 'padding-top: 5px; padding-bottom: 5px;',
			items: [{
				xtype: 'swcommonsprcombo',
				comboSubject: 'DrugListGroup',
				hiddenName: 'DrugListGroup_id',
				fieldLabel: 'Группа',
				width: 400
			}, {
				xtype: 'swrlsclsatccombo',
				hiddenName: 'ClsATC_id',
				fieldLabel: 'Класс АТХ',
				ctxSerach: true,
				width: 400
			}, {
				xtype: 'textfield',
				name: 'DrugListStr_Name',
				fieldLabel: 'Наименование',
				width: 400
			}, {
				layout: 'column',
				border: false,
				items: [{
					layout:'form',
					border: false,
					items: [{
						style: 'margin-left: 10px;',
						xtype: 'button',
						id: 'DLSVW_BtnSearch',
						text: lang['nayti'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							this.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					border: false,
					items: [{
						style: 'margin-left: 10px;',
						xtype: 'button',
						id: 'DLSVW_BtnReset',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							this.doSearch(true);
						}.createDelegate(this)
					}]
				}]
			}],
			keys: [{
				fn: function() {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'DLSVW_GridPanel',
			dataUrl: '/?c=DrugList&m=loadDrugListStrGrid',
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'DrugListStr_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugList_id', type: 'int', hidden: true},
				{name: 'DrugListGroup_id', type: 'int', hidden: true},
				{name: 'DrugListStr_Name', type: 'string', header: 'Наименование', id: 'autoexpand'},
				{name: 'Clsdrugforms_Name', type: 'string', header: 'Лекарственная форма', width: 140},
				{name: 'DrugListStr_Dose', type: 'string', header: 'Дозировка', width: 140},
				{name: 'ClsATC_Name', type: 'string', header: 'Класс АТХ', width: 260},
				{name: 'DrugListGroup_Name', type: 'string', header: 'Группа', width: 300},
				{name: 'DrugListStr_Num', type: 'string', header: 'Норматив', width: 140}
			],
			actions: [
				{name:'action_add', handler: function(){this.openDrugListStrEditWindow('add')}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openDrugListStrEditWindow('edit')}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openDrugListStrEditWindow('view')}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteDrugListStr()}.createDelegate(this)}
			]
		});

		Ext.apply(this, {
			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [
				this.FilterPanel,
				this.GridPanel
			]
		});

		sw.Promed.swDrugListStrViewWindow.superclass.initComponent.apply(this, arguments);
	}
});