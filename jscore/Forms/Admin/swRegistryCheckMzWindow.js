/**
* swRegistryCheckMzWindow - окно проверки реестра
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      16.11.2018
*/

/*NO PARSE JSON*/
sw.Promed.swRegistryCheckMzWindow = Ext.extend(sw.Promed.BaseForm,
{
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	firstRun: true,
	height: 500,
	width: 800,
	title: langs('Проверка реестра'),
	layout: 'border',
	//maximizable: true,
	maximized: true,
	modal: false,
	//plain: true,
	resizable: false,
	processRegistryData: function(action, options) {
		if (!options) {
			options = {};
		}

		var win = this;

		var selections = this.RegistryDataGrid.getMultiSelections();
		if (selections.length == 0) {
			sw.swMsg.alert('Ошибка', 'Не выбрана ни одна запись');
			return false;
		}

		var Evn_ids = [];
		for (var k in selections) {
			if (typeof selections[k].get == 'function') {
				Evn_ids.push(selections[k].get('Evn_id'));
			}
		}

		if (action == 'reject' && !options.ignoreRegistryHealDepErrorTypeSelect) {
			getWnd('swRegistryHealDepErrorTypeSelectWindow').show({
				callback: function(data) {
					options.RegistryHealDepErrorType_id = data.RegistryHealDepErrorType_id;
					options.ignoreRegistryHealDepErrorTypeSelect = true;
					win.processRegistryData(action, options);
				}
			});

			return false;
		}

		win.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request({
			url: '/?c=Registry&m=processRegistryDataMz',
			params: {
				Registry_id: win.Registry_id,
				Evn_ids: Ext.util.JSON.encode(Evn_ids),
				RegistryHealDepErrorType_id: options.RegistryHealDepErrorType_id,
				action: action
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						win.RegistryDataGrid.getGrid().getStore().reload();
						win.getRegistryDataMzGridCounters();
					}
				}
			}
		});
	},
	selectAllOnPage: function(RegistryHealDepResType_id) {
		var win = this;
		win.RegistryDataGrid.clearMultiSelections();

		win.RegistryDataGrid.getGrid().getStore().each(function (record) {
			var check = false;
			switch(RegistryHealDepResType_id) {
				case 1:
				case 2:
					check = record.get('RegistryHealDepResType_id') && parseInt(record.get('RegistryHealDepResType_id')) == RegistryHealDepResType_id;
					break;

				case -1:
					check = Ext.isEmpty(record.get('RegistryHealDepResType_id'));
					break;

				default:
					check = true;
					break;
			}

			if (check) {
				win.RegistryDataGrid.checkedArray.push(record);
				record.set('MultiSelectValue', true);
				record.commit();
			}
		});

		win.RegistryDataGrid.syncMultiSelectHeader();
		win.onMultiSelectChange();
	},
	selectAll: function(RegistryHealDepResType_id) {
		var win = this;

		// надо все идешники со всех страниц подгрузить %)
		var filtersForm = win.RegistryDataFilterPanel.getForm();
		var params = filtersForm.getValues();
		params.Registry_id = win.Registry_id;
		params.getIdsOnly = 1;
		params.RegistryHealDepResType_id = RegistryHealDepResType_id;

		win.RegistryDataGrid.clearMultiSelections();

		win.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request({
			url: '/?c=Registry&m=loadRegistryDataMzGrid',
			params: params,
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						win.RegistryDataGrid.checkedArray = [];
						for(var k in result.ids) {
							var Evn_id = parseInt(result.ids[k]);
							if (Evn_id) {
								var record = new Ext.data.Record({
									Evn_id: Evn_id
								});
								win.RegistryDataGrid.checkedArray.push(record);

								win.RegistryDataGrid.getGrid().getStore().each(function (record) {
									if (record.get('Evn_id') == Evn_id) {
										record.set('MultiSelectValue', true);
										record.commit();
									}
								});
							}
						}

						win.RegistryDataGrid.syncMultiSelectHeader();
						win.onMultiSelectChange();
					}
				}
			}
		});
	},
	show: function()
	{
		sw.Promed.swRegistryCheckMzWindow.superclass.show.apply(this, arguments);

		var win = this;

		if (!arguments[0] || !arguments[0].Registry_id) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы') + form.id + langs('.<br/>Не указаны необходимые входные параметры.'),
				title: langs('Ошибка')
			});
			this.hide();

			return false;
		}

		win.Registry_id = arguments[0].Registry_id;
		win.RegistryType_id = arguments[0].RegistryType_id;

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		} else {
			this.onHide = Ext.emptyFn;
		}

		this.maximize();
		this.loadRegistryDataGrid();

		if (!win.RegistryDataGrid.getAction('action_select')) {
			win.RegistryDataGrid.addActions({
				name: 'action_select',
				text: langs('Выделить случаи'),
				menu: [{
					text: langs('Выделить случаи на странице'),
					menu: [{
						text: langs('Непроверенные'),
						handler: function() {
							win.selectAllOnPage(-1);
						}
					}, {
						text: langs('Принятые'),
						handler: function() {
							win.selectAllOnPage(1);
						}
					}, {
						text: langs('Отклонённые'),
						handler: function() {
							win.selectAllOnPage(2);
						}
					}, {
						text: langs('Все на странице'),
						handler: function() {
							win.selectAllOnPage(null);
						}
					}],
				}, {
					text: langs('Выделить все случаи'),
					menu: [{
						text: langs('Непроверенные'),
						handler: function() {
							win.selectAll(-1);
						}
					}, {
						text: langs('Принятые'),
						handler: function() {
							win.selectAll(1);
						}
					}, {
						text: langs('Отклонённые'),
						handler: function() {
							win.selectAll(2);
						}
					}, {
						text: langs('Все на странице'),
						handler: function() {
							win.selectAll(null);
						}
					}]
				}]
			}, 0);
		}

		if (!win.RegistryDataGrid.getAction('action_deselect')) {
			win.RegistryDataGrid.addActions({
				name: 'action_deselect',
				text: langs('Снять выделение'),
				handler: function() {
					win.RegistryDataGrid.clearMultiSelections();
				}
			}, 1);
		}

		if (!win.RegistryDataGrid.getAction('action_accept')) {
			win.RegistryDataGrid.addActions({
				name: 'action_accept',
				text: langs('Принять'),
				handler: function() {
					win.processRegistryData('accept');
				}
			}, 2);
		}

		if (!win.RegistryDataGrid.getAction('action_reject')) {
			win.RegistryDataGrid.addActions({
				name: 'action_reject',
				text: langs('Отклонить'),
				handler: function() {
					win.processRegistryData('reject');
				}
			}, 3);
		}

		if (!win.RegistryDataGrid.getAction('action_reset')) {
			win.RegistryDataGrid.addActions({
				name: 'action_reset',
				text: langs('Сбросить результат проверки'),
				handler: function() {
					win.processRegistryData('reset');
				}
			}, 4);
		}

		if (!win.RegistryDataGrid.getAction('action_evn')) {
			win.RegistryDataGrid.addActions({
				name: 'action_evn',
				text: langs('Открыть случай лечения'),
				handler: function() {
					win.openForm('OpenEvn');
				}
			}, 5);
		}

		if (!win.RegistryDataGrid.getAction('action_person')) {
			win.RegistryDataGrid.addActions({
				name: 'action_person',
				text: langs('Открыть данные человека'),
				handler: function() {
					win.openForm('OpenPerson');
				}
			}, 6);
		}

		var filtersForm = win.RegistryDataFilterPanel.getForm();
		if (filtersForm.findField('RegistryHealDepErrorType_id').getStore().getCount() == 0) {
			filtersForm.findField('RegistryHealDepErrorType_id').getStore().load();
		}
		filtersForm.findField('Evn_id').setContainerVisible(isSuperAdmin());
	},
	loadRegistryDataGrid: function() {
		var form = this;

		var filtersForm = form.RegistryDataFilterPanel.getForm();
		var params = filtersForm.getValues();
		params.start = 0;
		params.limit = 100;
		params.Registry_id = form.Registry_id;

		form.RegistryDataGrid.loadData({
			globalFilters: params
		});

		form.getRegistryDataMzGridCounters();
	},
	openForm: function (frm)
	{
		var form = this;
		var record = form.RegistryDataGrid.getGrid().getSelectionModel().getSelected();
		if ( typeof record != 'object' || Ext.isEmpty(record.get('Evn_id')) )
		{
			sw.swMsg.alert('Ошибка', 'Ошибка выбора записи!');
			return false;
		}


		var id = record.get('Evn_rid') ||  record.get('Evn_id'); // Вызываем родителя , а если родитель пустой то основное
		var Person_id = record.get('Person_id');
		var Server_id = record.get('Server_id');
		var PersonEvn_id = null;
		var usePersonEvn = null;
		var key;
		if (record.get('PersonEvn_id'))
		{
			PersonEvn_id = record.get('PersonEvn_id');
			usePersonEvn = true;
		}

		var params = {
			action: 'view',
			Person_id: Person_id,
			Server_id: Server_id,
			PersonEvn_id: PersonEvn_id,
			usePersonEvn: usePersonEvn
		};
		params = Ext.apply(params || {});

		if (frm == 'OpenPerson') {
			if (Ext.isEmpty(record.get('Person_id')) ) {
				return false;
			}

			open_form = 'swPersonEditWindow';
			key = 'Person_id';
			id = record.get('Person_id');
		} else {
			if ( form.RegistryType_id == 6 ) {
				if ( !Ext.isEmpty(record.get('CmpCloseCard_id')) ) {
					id = record.get('CmpCloseCard_id');
					open_form = 'swCmpCallCardNewCloseCardWindow';
					key = 'CmpCloseCard_id';
				}
				else {
					open_form = 'swCmpCallCardEditWindow';
					key = 'CmpCallCard_id';

					if ( !Ext.isEmpty(record.get('CmpCallCardInputType_id')) ) {
						params.CmpCallCard_isShortEditVersion = record.get('CmpCallCardInputType_id');
					}
				}
			}
			else {
				var config = getEditFormForEvnClass({
					EvnClass_id: record.get('EvnClass_id'),
					DispClass_id: record.get('DispClass_id')
				});

				open_form = config.open_form;
				key = config.key;
			}
		}

		if (id) {
			params[key] = id;
		}

		if (open_form.inlist([ 'swCmpCallCardEditWindow', 'swCmpCallCardNewCloseCardWindow', 'swEvnUslugaTelemedEditWindow' ])) { // карты вызова, телемедицинские услуги
			params.formParams = Ext.apply(params);
		}

		getWnd(open_form).show(params);
	},
	getRegistryDataMzGridCounters: function() {
		var win = this;

		win.uncCounter.setValue('...');
		win.accCounter.setValue('...');
		win.decCounter.setValue('...');
		Ext.Ajax.request({
			url: '/?c=Registry&m=getRegistryDataMzGridCounters',
			params: {
				Registry_id: win.Registry_id
			},
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						win.uncCounter.setValue(result.RegistryHealDepCheckJournal_UncRecCount);
						win.accCounter.setValue(result.RegistryHealDepCheckJournal_AccRecCount);
						win.decCounter.setValue(result.RegistryHealDepCheckJournal_DecRecCount);
					}
				}
			}
		});
	},
	onRowSelect: function() {
		var record = this.RegistryDataGrid.getGrid().getSelectionModel().getSelected();
		if (record && record.get('Evn_id')) {
			this.RegistryDataGrid.setActionDisabled('action_evn', false);
			this.RegistryDataGrid.setActionDisabled('action_person', false);
		} else {
			this.RegistryDataGrid.setActionDisabled('action_evn', true);
			this.RegistryDataGrid.setActionDisabled('action_person', true);
		}
	},
	onMultiSelectChange: function() {
		var selections = this.RegistryDataGrid.getMultiSelections();

		this.RegistryDataGrid.setActionDisabled('action_deselect', selections.length == 0);
		this.RegistryDataGrid.setActionDisabled('action_accept', selections.length == 0);
		this.RegistryDataGrid.setActionDisabled('action_reject', selections.length == 0);
		this.RegistryDataGrid.setActionDisabled('action_reset', selections.length == 0);
	},
	onHide: Ext.emptyFn,
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	initComponent: function()
	{
		var form = this;

		this.RegistryDataFilterPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 90,
			bodyStyle: 'background: transparent; padding: 5px;',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.loadRegistryDataGrid();
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
					width: 300,
					labelWidth: 100,
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						name: 'Person_SurName',
						xtype: 'textfield'
					}, {
						anchor: '100%',
						fieldLabel: 'Имя',
						name: 'Person_FirName',
						xtype: 'textfield'
					}, {
						anchor: '100%',
						fieldLabel: 'Отчество',
						name: 'Person_SecName',
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 390,
					labelWidth: 190,
					items: [{
						anchor: '100%',
						fieldLabel: 'Результат проверки',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[-1, 'Не проверен'],
							[1, 'Принят'],
							[2, 'Отклонён']
						],
						hiddenName: 'RegistryHealDepResType_id',
						xtype: 'combo'
					}, {
						anchor: '100%',
						fieldLabel: 'Ошибка',
						hiddenName: 'RegistryHealDepErrorType_id',
						xtype: 'swregistryhealdeperrortypecombo'
					}, {
						anchor: '100%',
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						xtype: 'numberfield'
					},{
						anchor: '100%',
						editable: true,
						xtype: 'swmedicalcarebudgtypecombo',
						hiddenName: 'MedicalCareBudgType_id',
						fieldLabel: 'Тип мед. помощи'
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						handler: function() {
							form.loadRegistryDataGrid();
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
							var filtersForm = form.RegistryDataFilterPanel.getForm();
							filtersForm.reset();
							form.RegistryDataGrid.removeAll(true);
							form.loadRegistryDataGrid();
						}
					}]
				}]
			}]
		});

		this.RegistryDataGrid = new sw.Promed.ViewFrame({
			region: 'center',
			title: '',
			object: 'RegistryData',
			dataUrl: '/?c=Registry&m=loadRegistryDataMzGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			selectionModel: 'multiselect2',
			id: form.id + 'RegistryDataGrid',
			onMultiSelectChange: function() {
				form.onMultiSelectChange();
			},
			onLoadData: function() {
				form.onMultiSelectChange();
				form.onRowSelect();
			},
			onRowSelect: function() {
				form.onRowSelect();
			},
			onDblClick: function () {
				form.openForm('OpenEvn');
			},
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: !isSuperAdmin()},
				{name: 'RegistryHealDepResType_id',  header: 'Результат проверки', renderer: function(val) {
					if (val) {
						switch (parseInt(val)) {
							case 2: // Отклонён
								return "<img src='/img/icons/minus16.png' />";
								break;
							case 1: // Принят
								return "<img src='/img/icons/plus16.png' />";
								break;
						}
					}

					return '';
				}, width: 120},
				{name: 'RegistryHealDepErrorType_Name', type: 'string', header: 'Ошибка', width: 90},
				{name: 'Person_FIO', id: 'autoexpand', sort: true, header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 80},
				{name: 'Evn_setDate', type: 'date', header: 'Дата начала лечения', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Дата окончания лечения', width: 80},
				{name: 'RegistryData_ItogSum', type: 'money', header: 'Сумма к оплате', width: 90},
				{name: 'MedicalCareType_Name', type: 'string', header: 'Тип МП', width: 90},
				{name: 'Evn_rid', type: 'int', header: 'Evn_rid', hidden: true},
				{name: 'PersonEvn_id', type: 'int', header: 'PersonEvn_id', hidden: true},
				{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
				{name: 'EvnClass_id', type: 'int', header: 'EvnClass_id', hidden: true},
				{name: 'DispClass_id', type: 'int', header: 'DispClass_id', hidden: true},
				{name: 'CmpCloseCard_id', type: 'int', hidden: true},
				{name: 'CmpCallCardInputType_id', type: 'int', hidden: true}
			],
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true}
			]
		});

		this.uncCounter = new Ext.form.TextField({
			width: 30,
			readOnly: true
		});
		this.accCounter = new Ext.form.TextField({
			width: 30,
			readOnly: true
		});
		this.decCounter = new Ext.form.TextField({
			width: 30,
			readOnly: true
		});

		this.RegistryDataGrid.ViewToolbar.on('render', function(vt) {
			// добавляем
			vt.add('-');
			vt.add({
				text: 'Осталось проверить:',
				xtype: 'label'
			});
			vt.add(form.uncCounter);
			vt.add({
				style: 'margin-left: 4px;',
				text: 'Принято:',
				xtype: 'label'
			});
			vt.add(form.accCounter);
			vt.add({
				style: 'margin-left: 4px;',
				text: 'Отклонено:',
				xtype: 'label'
			});
			vt.add(form.decCounter);
		});

		this.RegistryDataPanel = new Ext.Panel({
			border: false,
			layout: 'border',
			region: 'center',
			height: 280,
			items: [
				this.RegistryDataFilterPanel,
				this.RegistryDataGrid
			]
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
				form.RegistryDataPanel
			]
		});
		sw.Promed.swRegistryCheckMzWindow.superclass.initComponent.apply(this, arguments);
	}
});