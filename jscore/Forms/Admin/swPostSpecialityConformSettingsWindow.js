/**
 * swPostSpecialityConformSettingsWindow - ЕРМП. Настройка соответствия должностей и специальностей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package            Admin
 * @access            public
 * @copyright        Copyright (c) 2018 Swan Ltd
 */

sw.Promed.swPostSpecialityConformSettingsWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPostSpecialityConformSettingsWindow',
	objectName: 'swPostSpecialityConformSettingsWindow',
	objectSrc: '/jscore/Forms/Admin/swPostSpecialityConformSettingsWindow.js',
	maximized: true,
	maximazible: false,
	layout: 'border',
	title: langs('Настройка соответствия должностей и специальностей'),
	initComponent: function () {
		var win = this;

		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 130,
			region: 'north',
			items: [
				{
					autoHeight: true,
					xtype: 'fieldset',
					layout: 'column',
					items: [{
						layout: 'form',
						width: 400,
						items: [
							{
								fieldLabel: 'Должность',
								width: 250,
								xtype: 'textfield',
								id: 'Position_Input',
								valueField: 'Position_Name'
							}, {
								fieldLabel: 'Специальность',
								width: 250,
								xtype: 'textfield',
								id: 'Speciality_Input',
								valueField: 'Speciality_Name'
							}
						]
					}, {
						layout: 'form',
						items: [
							{
								tabIndex: TABINDEX_TRVVW + 5,
								xtype: 'button',
								text: langs('Найти'),
								iconCls: 'search16',
								handler: function () {
									win.doSearch();
								}
							}, {
								tabIndex: TABINDEX_TRVVW + 6,
								xtype: 'button',
								style: 'margin-top: 2px;',
								text: langs('Сброс'),
								iconCls: 'resetsearch16',
								handler: function () {
									win.doReset();
								}
							}
						]
					}]
				}]
		});

		this.PostSpecialityConformFrame = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add', handler: function () {
						win.openPostSpecEditForm('add');
					}
				},
				{
					name: 'action_edit', handler: function () {
						win.openPostSpecEditForm('edit');
					}
				},
				{
					name: 'action_view', handler: function () {
						win.openPostSpecEditForm('view');
					}
				},
				{
					name: 'action_delete', handler: function () {
						win.deletePostSpecRecord();
					}
				},
				{
					name: 'action_refresh', disabled: true, hidden: true
				},
				{
					name: 'action_print', handler: function () {
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=PostSpeciality&m=loadPostSpecialityList',
			object: 'persis.PostSpeciality',
			editformclassname: 'swPostSpecialityConformSettingsEditForm',
			id: 'PSCSW_ConformFrame',
			paging: true,
			title: langs('Соответствие должностей и специальностей ЕРМП'),
			totalProperty: 'totalCount',
			toolbar: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PostSpeciality_id', type: 'int', header: 'ID', key: true},
				{name: 'Post_id', type: 'int', hidden: true},
				{name: 'Post_Name', type: 'string', header: langs('Должность'), width: 500},
				{name: 'Speciality_id', type: 'int', hidden: true},
				{name: 'Speciality_Name', type: 'string', header: langs('Специальность'), width: 500}
			],
			onDblClick: function () {
				this.onEnter();
			},
			onEnter: function () {
				if (!win.PostSpecialityConformFrame.getGrid().getSelectionModel().getSelected()) {
					return false;
				}

				var rec = win.PostSpecialityConformFrame.getGrid().getSelectionModel().getSelected();
				if (!rec.get('PostSpeciality_id')) {
					return false;
				} else {
					win.openPostSpecEditForm('view');
				}
			}
		});

		Ext.apply(this, {
			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function () {
						win.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}],
			items: [
				this.FilterPanel,
				this.PostSpecialityConformFrame
			]
		});

		sw.Promed.swPostSpecialityConformSettingsWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function () {
		sw.Promed.swPostSpecialityConformSettingsWindow.superclass.show.apply(this, arguments);
	},

	openPostSpecEditForm: function (action) {
		var win = this,
			grid = win.PostSpecialityConformFrame.getGrid(),
			params = {
				action: action,
				grid: grid,
				caller: win,
				callback: function () {
					win.PostSpecialityConformFrame.getAction('action_refresh').execute();
				}.createDelegate(this)
			};

		getWnd('swPostSpecialityConformSettingsEditForm').show(params);
	},
	deletePostSpecRecord: function () {
		var win = this,
			grid = this.PostSpecialityConformFrame.getGrid(),
			rec = grid.getSelectionModel().getSelected().data;
		if (Ext.isEmpty(rec) || Ext.isEmpty(rec.PostSpeciality_id)) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не выбрана запись'));
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (btn) {
				if (btn == 'yes') {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg: 'Удаление соответствия должности и специальности'});
					loadMask.show();

					//запрос на удаление записи
					Ext.Ajax.request({
						url: '/?c=PostSpeciality&m=deletePostSpecialityPair',
						params: {
							PostSpeciality_id: rec.PostSpeciality_id
						},
						failure: function () {
							loadMask.hide();
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении соответствия'));
						},
						success: function (response) {
							loadMask.hide();
							var resp = Ext.util.JSON.decode(response.responseText);

							if (resp.Error_Msg) {
								sw.swMsg.alert(langs('Ошибка'), resp.Error_Msg);
							} else {
								var data = {
									start: grid.getStore().lastOptions.params.start
								};
								win.doSearch(data);
							}

							if (grid.getStore().getCount() > 0) {
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}

						}.createDelegate(this)
					});
				} else {
					if (grid.getStore().getCount() > 0) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить выбранное соответствие?'),
			title: langs('Вопрос')
		});
	},
	doSearch: function (data) {
		var form = this.FilterPanel.getForm(),
			post = form.findField('Position_Input').getValue(),
			speciality = form.findField('Speciality_Input').getValue(),
			grid = this.PostSpecialityConformFrame.getGrid().getStore(),
			params = {
				'Post_Name': post,
				'Speciality_Name': speciality,
				'object': grid.baseParams.object,
				'start': 0,
				'limit': 100
			};
		if (!Ext.isEmpty(data))
			params.start = data.start;

		grid.baseParams = params;
		grid.removeAll();
		grid.load(
			{
				params: params,
				globalFilters: params
			}
		);
	},
	doReset: function () {
		var grid = this.PostSpecialityConformFrame;
		this.FilterPanel.getForm().reset();
		grid.getGrid().getStore().removeAll();
		grid.getGrid().getView().refresh();
	}
});