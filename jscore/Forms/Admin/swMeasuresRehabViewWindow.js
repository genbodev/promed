/**
 * swMeasuresRehabViewWindow - окно мероприятий реабилитации или абилитации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			09.12.2016
 */
/*NO PARSE JSON*/

sw.Promed.swMeasuresRehabViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swMeasuresRehabViewWindow',
	maximizable: false,
	maximized: true,
	layout: 'border',
	title: 'Мероприятия реабилитации или абилитации',

	openMeasuresRehabEditWindow: function(action, type) {
		if (Ext.isEmpty(action) || !action.inlist(['add','edit','view'])) {
			return false;
		}

		var wnd = this;
		var grid = this.FactGridPanel.getGrid();

		var params = {
			action: action,
			formParams: {}
		};

		params.callback = function() {
			wnd.FactGridPanel.getAction('action_refresh').execute();
		};

		if (action == 'add') {
			if (Ext.isEmpty(type)) {
				return false;
			}

			params.type = type;
			params.needMedRehab = this.needMedRehab;
			params.needReconstructSurg = this.needReconstructSurg;
			params.needOrthotics = this.needOrthotics;
			params.begDate = this.begDate;
			params.endDate = this.endDate;
			params.formParams.IPRARegistry_id = this.IPRARegistry_id;
		} else {
			var record = grid.getSelectionModel().getSelected();

			if (!record || Ext.isEmpty(record.get('MeasuresRehab_id'))) {
				return false;
			}

			params.needMedRehab = this.needMedRehab;
			params.needReconstructSurg = this.needReconstructSurg;
			params.needOrthotics = this.needOrthotics;
			params.begDate = this.begDate;
			params.endDate = this.endDate;
			params.formParams.MeasuresRehab_id = record.get('MeasuresRehab_id');
		}

		getWnd('swMeasuresRehabEditWindow').show(params);
		return true;
	},

	deleteMeasuresRehab: function() {
		var grid_panel = this.FactGridPanel;
		var grid = grid_panel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('MeasuresRehab_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {MeasuresRehab_id: record.get('MeasuresRehab_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=MeasuresRehab&m=deleteMeasuresRehab'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	show: function() {
		sw.Promed.swMeasuresRehabViewWindow.superclass.show.apply(this, arguments);

		this.IPRARegistry_id = null;
		this.needMedRehab = false;
		this.needReconstructSurg = false;
		this.needOrthotics = false;
		this.begDate = null;
		this.endDate = null;

		this.FactGridPanel.isLoaded = false;
		this.FactGridPanel.removeAll();

		if (arguments[0] && arguments[0].IPRARegistry_id) {
			this.IPRARegistry_id = arguments[0].IPRARegistry_id;
		}
		if (arguments[0] && arguments[0].needMedRehab) {
			this.needMedRehab = arguments[0].needMedRehab;
		}
		if (arguments[0] && arguments[0].needReconstructSurg) {
			this.needReconstructSurg = arguments[0].needReconstructSurg;
		}
		if (arguments[0] && arguments[0].needOrthotics) {
			this.needOrthotics = arguments[0].needOrthotics;
		}
		if (arguments[0] && arguments[0].begDate) {
			this.begDate = arguments[0].begDate;
		}
		if (arguments[0] && arguments[0].endDate) {
			this.endDate = arguments[0].endDate;
		}

		Ext.getCmp('MRVW_AddDrug').setVisible(this.needMedRehab);


		this.TabPanel.setActiveTab('Plan');
		this.TabPanel.setActiveTab('Fact');
	},

	initComponent: function() {
		var wnd = this;

		this.AddMenu = new Ext.menu.Menu({
			id: 'MRVW_AddMenu',
			items: [{
				id: 'MRVW_AddUsluga',
				text: 'Услуга',
				handler: function() {
					wnd.openMeasuresRehabEditWindow('add', 'usluga');
				}
			}, {
				id: 'MRVW_AddEvn',
				text: 'Случай лечения',
				handler: function() {
					wnd.openMeasuresRehabEditWindow('add', 'evn');
				}
			}, {
				id: 'MRVW_AddDrug',
				text: 'Медикаменты',
				handler: function() {
					wnd.openMeasuresRehabEditWindow('add', 'drug');
				}
			}, {
				id: 'MRVW_AddOther',
				text: 'Прочие мероприятия',
				handler: function() {
					wnd.openMeasuresRehabEditWindow('add', 'other');
				}
			}]
		});

		this.FactGridPanel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: Ext.emptyFn, menu: this.AddMenu},
				{name: 'action_edit', handler: function(){wnd.openMeasuresRehabEditWindow('edit')}},
				{name: 'action_view', handler: function(){wnd.openMeasuresRehabEditWindow('view')}},
				{name: 'action_delete', handler: function(){wnd.deleteMeasuresRehab()}},
			],
			border: false,
			autoLoadData: false,
			dataUrl: '/?c=MeasuresRehab&m=loadMeasuresRehabGrid',
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{name: 'MeasuresRehab_id', type: 'int', header: 'ID', key: true},
				{name: 'IPRARegistry_id', hidden: true, type: 'int'},
				{name: 'MeasuresRehabType_id', hidden: true, type: 'int'},
				{name: 'MeasuresRehabSubType_id', hidden: true, type: 'int'},
				{name: 'MeasuresRehabResult_id', hidden: true, type: 'int'},
				{name: 'MeasuresRehab_setDate', header: 'Дата', type: 'date', width: 80},
				{name: 'MeasuresRehabType_Name', header: 'Тип мероприятия', type: 'string', width: 180},
				{name: 'MeasuresRehabSubType_Name', header: 'Подтип мероприятия', type: 'string', width: 180},
				{name: 'MeasuresRehab_Code', header: 'Код', type: 'string', width: 80},
				{name: 'MeasuresRehab_Name', header: 'Наименование', type: 'string', id: 'autoexpand'},
				{name: 'MeasuresRehabResult_Name', header: 'Результат', type: 'string', width: 260}
			]
		});

		this.TabPanel = new Ext.TabPanel({
			border: true,
			activeTab: 1,
			id: 'MRVW_TabPanel',
			region: 'center',
			items: [{
				id: 'Plan',
				layout: 'fit',
				title: 'План',
				disabled: true,
				items: []
			}, {
				id: 'Fact',
				layout: 'fit',
				title: 'Факт',
				items: [this.FactGridPanel]
			}],
			listeners: {
				tabchange: function (tab, panel) {
					switch(panel.id) {
						case 'Fact':
							var grid = wnd.FactGridPanel.getGrid();

							if (!wnd.FactGridPanel.isLoaded && wnd.IPRARegistry_id) {
								grid.getStore().load({
									params: {IPRARegistry_id: wnd.IPRARegistry_id},
									success: function() {
										wnd.FactGridPanel.isLoaded = true;
									}
								});
							}
							break;
					}
				}
			}
		});

		Ext.apply(this,{
			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.TabPanel]
		});

		sw.Promed.swMeasuresRehabViewWindow.superclass.initComponent.apply(this, arguments);
	}
});