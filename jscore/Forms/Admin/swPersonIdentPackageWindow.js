/**
 * swPersonIdentPackageWindow - окно для пкатной идентификации людей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.04.2017
 */
/*NO PARSE JSON*/

sw.Promed.swPersonIdentPackageWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonIdentPackageWindow',
	title: 'Пакетная идентификация ТФОМС',
	maximizable: false,
	maximized: true,
	layout: 'border',

	doPackageFilter: function(reset) {
		var form_panel = this.PackageFilterPanel;
		var base_form = this.PackageFilterPanel.getForm();
		var package_grid = this.PackageGridPanel.getGrid();

		this.PackageGridPanel.removeAll();

		if (reset) base_form.reset();

		var params = getAllFormFieldValues(form_panel);

		params.start = 0;
		params.limit = 100;

		package_grid.getStore().load({
			params: params,
			callback: function() {
				this.doPackagePosFilter();
			}.createDelegate(this)
		});
	},

	doPackagePosFilter: function(reset) {
		var form_panel = this.PackagePosFilterPanel;
		var base_form = this.PackagePosFilterPanel.getForm();
		var package_grid = this.PackageGridPanel.getGrid();
		var package_pos_grid = this.PackagePosGridPanel.getGrid();

		this.PackagePosGridPanel.removeAll();

		if (reset) base_form.reset();

		var package_record = package_grid.getSelectionModel().getSelected();
		if (!package_record || Ext.isEmpty(package_record.get('PersonIdentPackage_id'))) {
			return;
		}

		var params = getAllFormFieldValues(form_panel);

		params.PersonIdentPackage_id = package_record.get('PersonIdentPackage_id');
		params.start = 0;
		params.limit = 100;

		package_pos_grid.getStore().load({
			params: params
		});
	},

	addPersonIdentPackage: function () {
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование пакетов..."});
		loadMask.show();

		var result_tpl = new Ext.Template('Пакеты идентификации успешно добавлены. Всего {PackageCount} пакетов.');

		Ext.Ajax.request({
			url: '/?c=PersonIdentPackage&m=addPersonIdentPackage',
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.success) {
					Ext.Msg.alert(lang['soobschenie'], result_tpl.apply(response_obj));
					this.doPackageFilter();
				}
			}.createDelegate(this),
			failure: function(response) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	deletePersonIdentPackage: function() {
		var package_grid = this.PackageGridPanel.getGrid();
		var record = package_grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('PersonIdentPackage_id'))) {
			return;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет удаление пакета..."});
					loadMask.show();

					Ext.Ajax.request({
						url: '/?c=PersonIdentPackage&m=deletePersonIdentPackage',
						params: {PersonIdentPackage_id: record.get('PersonIdentPackage_id')},
						success: function(response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.success) {
								this.doPackageFilter();
							}
						}.createDelegate(this),
						failure: function(response) {
							loadMask.hide();
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_hotite_udalit_zapis'],
			title: lang['podtverjdenie']
		});
	},

	importPersonIdentPackageResponse: function() {
		var wnd = this;
		var params = {};
		params.callback = function() {
			wnd.doPackageFilter();
		};

		getWnd('swPersonIdentPackageResponseImportWindow').show(params);
	},

	openEvnWindow: function(Person_id, Evn_id) {
		var package_pos_grid = this.PackagePosGridPanel.getGrid();
		var record = package_pos_grid.getStore().getById(Person_id);
		if (!record || Ext.isEmpty(record.get('Person_id')) || Ext.isEmpty(record.get('EvnList'))) {
			return false;
		}

		var evnList = Ext.util.JSON.decode(record.get('EvnList'));
		if (!Ext.isArray(evnList)) {
			return false;
		}

		var evn = evnList.find(function(evn){return evn.Evn_id == Evn_id});

		log(evn.EvnClass_SysNick+': '+evn.Evn_id);

		var wnd = null;
		var params = {};
		params[evn.EvnClass_SysNick+'_id'] = evn.Evn_id;
		params.action = 'view';
		params.Person_id = evn.Person_id;
		params.PersonEvn_id = evn.PersonEvn_id;
		params.Server_id = evn.Server_id;

		switch(evn.EvnClass_SysNick){
			case 'EvnPL':
				wnd = 'swEvnPLEditWindow';
				break;
			case 'EvnPLStom':
				wnd = 'swEvnPLStomEditWindow';
				break;
			case 'EvnPS':
				wnd = 'swEvnPSEditWindow';
				break;
			case 'EvnSection':
				wnd = 'swEvnSectionEditWindow';
				break;
			case 'EvnPLDispDop':
				wnd = 'swEvnPLDispDopEditWindow';
				break;
			case 'EvnPLDispOrp':
				switch(Number(evn.DispClass_id)) {
					case 3:
					case 7:
						wnd = 'swEvnPLDispOrp13EditWindow';
						break;
					case 4:
					case 8:
						wnd = 'swEvnPLDispOrp13SecEditWindow';
						break;
				}
				break;
			case 'EvnPLDispTeenInspection':
				switch(Number(evn.DispClass_id)) {
					case 6:
						wnd = 'swEvnPLDispTeenInspectionEditWindow';
						break;
					case 9:
						wnd = 'swEvnPLDispTeenInspectionPredEditWindow';
						break;
					case 10:
						wnd = 'swEvnPLDispTeenInspectionProfEditWindow';
						break;
					case 11:
						wnd = 'swEvnPLDispTeenInspectionPredSecEditWindow';
						break;
					case 12:
						wnd = 'swEvnPLDispTeenInspectionProfSecEditWindow';
						break;
				}
				break;
		}
		if (Ext.isEmpty(wnd)) {
			return false;
		}

		getWnd(wnd).show(params);
		return true;
	},

	show: function() {
		sw.Promed.swPersonIdentPackageWindow.superclass.show.apply(this, arguments);

		var wnd = this;

		if (!this.PackageGridPanel.getAction('action_import')) {
			this.PackageGridPanel.addActions({
				name: 'action_import',
				text: 'Загрузить',
				tooltip: 'Загрузить ответ от ТФОМС',
				iconCls: 'archive16',
				handler: function() {
					wnd.importPersonIdentPackageResponse();
				}
			});
		}

		this.doPackageFilter(true);
	},

	initComponent: function() {
		var wnd = this;

		this.fileLinkTpl = new Ext.Template('<a href="{url}" target="_blank">{name}</a>');

		this.evnLinkStyle = 'margin-right: 10px;font-size: 12px;';
		this.evnLinkTpl = new Ext.Template('<p class="fake-link" style="{style}" onClick="getWnd(\'swPersonIdentPackageWindow\').openEvnWindow(\'{Person_id}\', \'{Evn_id}\')">{name}</p>');

		this.getEvnLink = function(evn) {
			return wnd.evnLinkTpl.apply({
				style: wnd.evnLinkStyle,
				Person_id: evn.Person_id,
				Evn_id: evn.Evn_id,
				name: evn.Evn_id
			});
		};

		this.PackageFilterPanel = new Ext.FormPanel({
			frame: true,
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			bodyStyle: 'margin-top: 5px;',
			keys: [{
				fn: function() {
					this.doPackageFilter();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 80,
					items: [{
						xtype: 'daterangefield',
						name: 'PersonIdentPackage_DateRange',
						fieldLabel: 'Дата пакета',
						width: 200
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 120,
					items: [{
						xtype: 'swyesnocombo',
						hiddenName: 'PersonIdentPackage_IsResponseRetrieved',
						fieldLabel: 'Ответ загружен',
						width: 100
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 20px;',
					items: [{
						xtype: 'button',
						text: lang['nayti'],
						iconCls: 'search16',
						handler: function() {
							this.doPackageFilter();
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 10px;',
					items: [{
						xtype: 'button',
						text: lang['sbros'],
						iconCls: 'reset16',
						handler: function() {
							this.doPackageFilter(true);
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.PackageGridPanel = new sw.Promed.ViewFrame({
			id: 'PIPW_PackageGridPanel',
			dataUrl: '/?c=PersonIdentPackage&m=loadPersonIdentPackageGrid',
			border: true,
			autoLoadData: false,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			paging: true,
			stringfields: [
				{name: 'PersonIdentPackage_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonIdentPackage_begDate', header: 'Дата пакета', type: 'date', width: 120},
				{name: 'PersonIdentPackage_IsResponseRetrieved', header: 'Ответ загружен', width: 100, renderer: function(value){
					if (Ext.isEmpty(value)) return '';
					return value==2?'Да':'Нет'
				}},
				{name: 'PersonIdentPackage_File', header: 'Файл', id: 'autoexpand', renderer: function(value) {
					if (/\d+\.SCD$/.test(value) == false) return '';
					return wnd.fileLinkTpl.apply({url: value, name: value});
				}},
				{name: 'PersonIdentPackage_ActualCount', header: 'Актуальных записей', type: 'int', width: 140},
				{name: 'PersonIdentPackage_ErrorCount', header: 'Записей с ошибками', type: 'int', width: 140}
			],
			actions: [
				{name:'action_add', handler: function(){this.addPersonIdentPackage()}.createDelegate(this)},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', handler: function(){this.deletePersonIdentPackage()}.createDelegate(this)},
				{name:'action_refresh'}
			],
			onRowSelect: function(sm, index, record) {
				this.doPackagePosFilter(true);
			}.createDelegate(this)
		});

		this.PackagePosFilterPanel = new Ext.FormPanel({
			frame: true,
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			bodyStyle: 'margin-top: 5px;',
			keys: [{
				fn: function() {
					this.doPackagePosFilter();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 80,
					items: [{
						xtype: 'textfield',
						name: 'Person_FIO',
						fieldLabel: 'ФИО',
						width: 200
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						xtype: 'textfield',
						maskRe: /\d/,
						name: 'PersonIdentPackagePosErrorType_Code',
						fieldLabel: 'Код ошибки',
						width: 200
					}]
				}]
			}, {
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 80,
					items: [{
						xtype: 'textfield',
						maskRe: /\d/,
						name: 'Evn_id',
						fieldLabel: 'ИД случая',
						width: 200
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						xtype: 'swcommonsprcombo',
						comboSubject: 'PersonIdentState',
						hiddenName: 'PersonIdentState_id',
						fieldLabel: 'Статус',
						width: 200
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 20px;',
					items: [{
						xtype: 'button',
						text: lang['nayti'],
						iconCls: 'search16',
						handler: function() {
							this.doPackagePosFilter();
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 10px;',
					items: [{
						xtype: 'button',
						text: lang['sbros'],
						iconCls: 'reset16',
						handler: function() {
							this.doPackagePosFilter(true);
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.PackagePosGridPanel = new sw.Promed.ViewFrame({
			id: 'PIPW_PackagePosGridPanel',
			dataUrl: '/?c=PersonIdentPackage&m=loadPersonIdentPackagePosGrid',
			toolbar: false,
			border: true,
			autoLoadData: false,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			paging: true,
			stringfields: [
				{name: 'Person_id', header: 'Person_id', type: 'int', key: true, hidden: false, width: 100},
				{name: 'PersonIdentPackage_identDT', type: 'date', hidden: true},
				{name: 'PersonIdentPackage_identDT2', type: 'date', hidden: true},
				{name: 'PersonIdentPackagePos_recDate', type: 'date', hidden: true},
				{name: 'PersonIdentPackagePos_insurEndDate', type: 'date', hidden: true},
				{name: 'PersonIdentAlgorithm_Value', type: 'string', hidden: true},
				{name: 'Person_FIO', header: 'ФИО', type: 'string', width: 260},
				{name: 'PersonIdentPackagePosErrorType_Code', header: 'Код ошибки', type: 'string', width: 120},
				{name: 'PersonIdentPackagePosErrorType_Name', header: 'Описание ошибки', type: 'string', id: 'autoexpand'},
				{name: 'PersonIdentState_Name', header: 'Статус', type: 'string', width: 220},
				{name: 'PersonIdentPackage_identPeriod', header: 'Период', width: 140, renderer: function(value, meta, record) {
					var date1 = !Ext.isEmpty(record.get('PersonIdentPackage_identDT'))
						?record.get('PersonIdentPackage_identDT').format('d.m.Y'):'';
					var date2 = !Ext.isEmpty(record.get('PersonIdentPackage_identDT2'))
						?record.get('PersonIdentPackage_identDT2').format('d.m.Y'):'';
					if (Ext.isEmpty(date2)) return date1;
					return date1+' - '+date2;
				}},
				{name: 'EvnList', header: 'События', width: 120, renderer: function(value) {
					if (!Ext.isEmpty(value)) {
						var evnList = Ext.util.JSON.decode(value);
						if (!Ext.isArray(evnList)) return '';
						return evnList.map(wnd.getEvnLink).join('');
					} else {
						return '';
					}
				}},
				{name: 'PersonIdentAlgorithm_Code', header: 'Алгоритм идентификации', renderer: function(value, meta, record) {
					if(meta) meta.attr = 'ext:qtip="' + record.get('PersonIdentAlgorithm_Value') + '"';
					return value;
				}},
				{name: 'PersonIdentPackagePos_PolisNum', header: 'Номер полиса', type: 'string', width: 120},
				{name: 'OrgSMO_Nick', header: 'СМО', type: 'string', width: 240},
				{name: 'PolisType_Name', header: 'Тип полиса', type: 'string', width: 120},
				{name: 'PersonIdentPackagePos_insurPeriod', header: 'Период действия СП', width: 140, renderer: function(value, meta, record) {
					var date1 = !Ext.isEmpty(record.get('PersonIdentPackagePos_recDate'))
						?record.get('PersonIdentPackagePos_recDate').format('d.m.Y'):'';
					var date2 = !Ext.isEmpty(record.get('PersonIdentPackagePos_insurEndDate'))
						?record.get('PersonIdentPackagePos_insurEndDate').format('d.m.Y'):'...';
					if (Ext.isEmpty(date2)) return date1;
					return date1+' - '+date2;
				}},
				{name: 'PersonIdentPackagePos_Snils', header: 'СИНЛС', type: 'string', width: 120},
				{name: 'PersonIdentPackagePos_BirthDay', header: 'Дата рождения', type: 'date', width: 120},
				{name: 'Sex_Name', header: 'Пол', type: 'string', width: 120}
			]
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
			items: [{
				layout: 'border',
				region: 'north',
				border: false,
				height: 350,
				items: [
					this.PackageFilterPanel,
					this.PackageGridPanel
				]
			}, {
				layout: 'border',
				region: 'center',
				border: false,
				title: 'Итоги проверки ТФОМС',
				items: [
					this.PackagePosFilterPanel,
					this.PackagePosGridPanel
				]
			}]
		});

		sw.Promed.swPersonIdentPackageWindow.superclass.initComponent.apply(this, arguments);
	}
});