/**
 * swMeasuresRehabExportWindow - окно экспорта мероприятий реабилитации или абилитации
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

sw.Promed.swMeasuresRehabExportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swMeasuresRehabExportWindow',
	maximizable: false,
	maximized: true,
	layout: 'border',
	title: 'Мероприятия реабилитации или абилитации',

	doSearch: function(reset) {
		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();
		var base_form = this.FilterPanel.getForm();

		if (reset) {
			base_form.reset();
			grid_panel.removeAll();
			return;
		}

		var params = base_form.getValues();

		grid.getStore().load({params: params});
	},

	doExport: function() {
		var grid = this.GridPanel.getGrid();

		var MeasuresRehab_ids = [];

		grid.getStore().each(function(rec){
			MeasuresRehab_ids.push(rec.get('MeasuresRehab_id'));
		});

		var params = {
			MeasuresRehab_ids: Ext.util.JSON.encode(MeasuresRehab_ids)
		};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет экспорт данных..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=MeasuresRehab&m=exportMeasuresRehab',
			params: params,
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.success) {
					if (!Ext.isEmpty(response_obj.link)) {
						var msg = 'Сформирован скрипт для экспорта данных в "витрину" МСЭ:</br>';
						msg += '<a target="_blank" href="'+response_obj.link+'">Скачать файл</a>';

						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: msg,
							title: 'Результат экспорта'
						});
					}
				}
			},
			failures: function() {
				loadMask.hide();
			}
		});
	},

	openMeasuresRehabEditWindow: function(action, type) {
		if (Ext.isEmpty(action) || !action.inlist(['add','edit','view'])) {
			return false;
		}

		var wnd = this;
		var grid = this.GridPanel.getGrid();

		var params = {
			action: action,
			formParams: {}
		};

		params.callback = function() {
			wnd.GridPanel.getAction('action_refresh').execute();
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
		var grid_panel = this.GridPanel;
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
		sw.Promed.swMeasuresRehabExportWindow.superclass.show.apply(this, arguments);

		this.GridPanel.addActions({
			name: 'person_register_export',
			text: 'Экспорт',
			tooltip: 'Экспорт',
			iconCls: 'database-export16',
			handler: function () {
				this.doExport();
			}.createDelegate(this)
		});

		this.doSearch(true);
	},

	initComponent: function() {
		var wnd = this;

		this.FilterPanel = new Ext.FormPanel({
			frame: true,
			id: 'MREW_FilterPanel',
			autoHeight: true,
			labelAlign: 'right',
			region: 'north',
			items: [{
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 100,
					items: [{
						xtype: 'swdatefield',
						name: 'MeasuresRehab_begRange',
						fieldLabel: 'Начало периода',
						width: 100
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 130,
					items: [{
						xtype: 'swdatefield',
						name: 'MeasuresRehab_endRange',
						fieldLabel: 'Окончание периода',
						width: 100
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 120,
					items: [{
						xtype: 'swlpucombo',
						hiddenName: 'LpuAttach_id',
						fieldLabel: 'МО прикрепления',
						width: 250
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 160,
					items: [{
						xtype: 'swyesnocombo',
						hiddenName: 'MeasuresRehab_IsExport',
						fieldLabel: 'Мероприятие передано',
						width: 80
					}]
				}],
			}],
			keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', handler: function(){wnd.openMeasuresRehabEditWindow('view')}},
				{name: 'action_delete', handler: function(){wnd.deleteMeasuresRehab()}},
			],
			border: false,
			autoLoadData: false,
			dataUrl: '/?c=MeasuresRehab&m=loadMeasuresRehabExportGrid',
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{name: 'MeasuresRehab_id', type: 'int', header: 'ID', key: true},
				{name: 'IPRARegistry_id', hidden: true, type: 'int'},
				{name: 'MeasuresRehabType_id', hidden: true, type: 'int'},
				{name: 'MeasuresRehabSubType_id', hidden: true, type: 'int'},
				{name: 'MeasuresRehabResult_id', hidden: true, type: 'int'},
				{name: 'IPRARegistry_Number', header: '№ ИПРА', type: 'string', width: 80},
				{name: 'Person_Fio', header: 'ФИО', type: 'string', width: 200},
				{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 80},
				{name: 'LpuAttach_Nick', header: 'МО прикрепления', type: 'string', width: 200},
				{name: 'MeasuresRehab_setDate', header: 'Дата мероприятия', type: 'date', width: 80},
				{name: 'MeasuresRehabType_Name', header: 'Тип мероприятия', type: 'string', width: 180},
				{name: 'MeasuresRehabSubType_Name', header: 'Подтип мероприятия', type: 'string', width: 180},
				{name: 'MeasuresRehab_Code', header: 'Код', type: 'string', width: 80},
				{name: 'MeasuresRehab_Name', header: 'Наименование', type: 'string', id: 'autoexpand'},
				{name: 'MeasuresRehabResult_Name', header: 'Результат', type: 'string', width: 260}
			],
			onDblClick: function(grid, number, object){
				wnd.openMeasuresRehabEditWindow('view');
			}
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function() {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					id: 'MREW_SearchButton',
					text: BTN_FRMSEARCH
				},
				{
					handler: function() {
						this.doSearch(true);
					}.createDelegate(this),
					iconCls: 'resetsearch16',
					id: 'MREW_ResetButton',
					text: BTN_FRMRESET
				},
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
			items: [this.FilterPanel, this.GridPanel]
		});

		sw.Promed.swMeasuresRehabExportWindow.superclass.initComponent.apply(this, arguments);
	}
});