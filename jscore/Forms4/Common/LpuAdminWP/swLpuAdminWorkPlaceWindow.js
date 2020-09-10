/**
 * swLpuAdminWorkPlaceWindow - АРМ администратора МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.LpuAdminWP.swLpuAdminWorkPlaceWindow', {
	noCloseOnTaskBar: true, // без кнопки закрытия на таксбаре
	extend: 'base.BaseForm',
	alias: 'widget.swLpuAdminWorkPlaceWindow',
	autoShow: false,
	maximized: true,
	width: 1000,
	refId: 'polkawp',
	findWindow: false,
	closable: false,
	frame: false,
	cls: 'arm-window-new PolkaWP',
	title: 'АРМ администратора МО',
	header: true,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	onDblClick: function() {
		var win = this;
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (record.get('pmUser_id')) {
				if (!win.mainGrid.down('#action_edit').isDisabled()) {
					win.openUserEditWindow('edit');
				}
			}
		}
	},
	onRecordSelect: function() {
		var win = this;

		win.mainGrid.down('#action_edit').disable();
		win.mainGrid.down('#action_delete').disable();

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (record.get('pmUser_id')) {
				win.mainGrid.down('#action_edit').enable();
				win.mainGrid.down('#action_delete').enable();
			}
		}
	},
	getGrid: function ()
	{
		return this.mainGrid;
	},
	getSelectedRecord: function() {
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];
			if (record && record.get('pmUser_id')) {
				return record;
			}
		}
		return false;
	},
	show: function() {
		this.callParent(arguments);
		var win = this;

		sw.Promed.MedStaffFactByUser.setMenuTitle(win, arguments[0]);

		win.doReset();
	},
	doSearch: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var base_form = this.filterPanel.getForm();
		var extraParams = base_form.getValues();

		extraParams.allowOverLimit = 1;
		extraParams.Org_id = getGlobalOptions().org_id;
		win.mainGrid.getStore().proxy.extraParams = extraParams;
		win.mainGrid.getStore().currentPage = 1;

		win.mainGrid.getStore().load({
			params: {
				start: 0,
				limit: 100
			},
			callback: function () {
				if (options.callback && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
	doReset: function () {
		var base_form = this.filterPanel.getForm();
		base_form.reset();
		this.mainGrid.getStore().removeAll();
		this.onRecordSelect();
		base_form.findField('Person_SurName').focus(true, 100);
		this.doSearch();
	},
	openUserEditWindow: function(action) {
		var win = this;
		var record = this.getSelectedRecord();

		if (!record && action !== 'add') return false;

		var user_login = (action !== 'add') ? record.get('login') : 0;

		var params = {
			action: action,
			fields: {
				action: action,
				org_id: getGlobalOptions().org_id,
				user_login: user_login
			},
			owner: this,
			callback: function(owner) {
				win.mainGrid.getStore().reload();
			},
			onClose: function() {
				//
			}
		}

		getWnd('swUserEditWindow').show(params);
	},
	deleteUser: function() {
		var win = this;
		var record = this.getSelectedRecord();

		if (!record) return false;

		Ext6.Msg.show({
			title: langs('Подтверждение удаления'),
			msg: langs('Вы действительно желаете удалить этого пользователя?'),
			buttons: Ext6.Msg.YESNO,
			fn: function(buttonId) {
				if (buttonId == 'yes') {
					win.mask('Удаление пользователя');
					Ext6.Ajax.request({
						url: C_USER_DROP,
						params: {
							user_login: record.get('login')
						},
						callback: function(o, s, r) {
							win.unmask();
							if(s) {
								win.mainGrid.getStore().reload();
							}
						}
					});
				}
			}
		});
	},
	initComponent: function() {
		var win = this;

		win.mainGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			tbar: {
				xtype: 'toolbar',
				defaults: {
					margin: '0 4 0 0',
					padding: '4 10'
				},
				height: 40,
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [{
					margin: '0 0 0 6',
					text: 'Обновить',
					itemId: 'action_refresh',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_refresh',
					handler: function() {
						win.doSearch();
					}
				}, {
					text: 'Добавить',
					itemId: 'action_add',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_add',
					handler: function() {
						win.openUserEditWindow('add');
					}
				}, {
					text: 'Изменить',
					itemId: 'action_edit',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_edit',
					handler: function() {
						win.openUserEditWindow('edit');
					}
				}, {
					text: 'Удалить',
					itemId: 'action_delete',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_delete',
					handler: function() {
						win.deleteUser();
					}
				}, {
					text: 'Печать',
					itemId: 'action_print',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_print',
					menu: new Ext6.menu.Menu({
						userCls: 'menuWithoutIcons',
						items: [{
							text: 'Печать списка',
							handler: function() {
								Ext6.ux.GridPrinter.print(win.mainGrid);
							}
						}]
					})
				}]
			},
			bbar: {
				xtype: 'pagingtoolbar',
				displayInfo: true
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						win.onRecordSelect();
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					win.onDblClick();
				}
			},
			store: {
				fields: [
					{name: 'pmUser_id', type: 'int'},
					{name: 'login'},
					{name: 'surname'},
					{name: 'name'},
					{name: 'secname'},
					{name: 'groups'},
					{name: 'desc'},
					{name: 'IsMedPersonal', type: 'int'}
				],
				pageSize: 100,
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=User&m=getUsersListOfCache',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'login'
				],
				listeners: {
					load: function() {
						win.onRecordSelect();
					}
				}
			},
			columns: [
				{text: 'Логин', tdCls: 'padLeft', width: 150, dataIndex: 'login'},
				{text: 'Фамилия', width: 150, dataIndex: 'surname'},
				{text: 'Имя', width: 150, dataIndex: 'name'},
				{text: 'Отчество', width: 150, dataIndex: 'secname'},
				{
					text: 'Группы',
					dataIndex: 'groups',
					renderer: function(value, cellEl, rec) {
						var result = '';
						if (!Ext6.isEmpty(value)) {
							// разджейсониваем
							var groups = Ext6.JSON.decode(value);
							for (var k in groups) {
								// Костыль для Chrome - берем только элементы с числовыми идентификаторами
								// @task https://redmine.swan.perm.ru/issues/102122
								if (parseInt(k) == k && !Ext6.isEmpty(groups[k].name)) {
									if (!Ext6.isEmpty(result)) {
										result += ', ';
									}
									result += groups[k].name;
								}
							}
						}
						return result;
					},
					width: 300
				},
				{text: 'Описание', dataIndex: 'desc', flex: 1},
				{text: 'Врач', dataIndex: 'IsMedPersonal', renderer: function(val, metaData, record) {
					var s = '';
					if (record.get('IsMedPersonal') && record.get('IsMedPersonal') == 1) {
						s += "&#10004;";
					}
					return s;
				}}
			]
		});

		win.leftMenu = new Ext6.menu.Menu({
			xtype: 'menu',
			floating: false,
			dock: 'left',
			cls: 'leftPanelWP',
			border: false,
			padding: 0,
			defaults: {
				margin: 0
			},
			mouseLeaveDelay: 100,
			collapsedWidth: 30,
			collapseMenu: function() {
				if (!win.leftMenu.activeChild || win.leftMenu.activeChild.hidden) {
					clearInterval(win.leftMenu.collapseInterval); // сбрасывем
					win.leftMenu.getEl().setWidth(win.leftMenu.collapsedWidth); // сужаем
					win.leftMenu.body.setWidth(win.leftMenu.collapsedWidth - 1); // сужаем
					win.leftMenu.deactivateActiveItem();
				}
			},
			listeners: {
				mouseover: function() {
					clearInterval(win.leftMenu.collapseInterval); // сбрасывем
					win.leftMenu.getEl().setWidth(win.leftMenu.items.items[0].getWidth());
					win.leftMenu.body.setWidth(win.leftMenu.items.items[0].getWidth() - 1);
				},
				afterrender : function(scope) {
					win.leftMenu.setWidth(win.leftMenu.collapsedWidth); // сразу сужаем
					win.leftMenu.setZIndex(10); // fix zIndex чтобы панель не уезжала под грид

					this.el.on('mouseout', function() {
						// сужаем, если нет подменю
						clearInterval(win.leftMenu.collapseInterval); // сбрасывем
						win.leftMenu.collapseInterval = setInterval(win.leftMenu.collapseMenu, 100);
					});
				}
			},
			items: [{
				iconCls: 'lpupassport16-2017',
				handler: function() {
					getWnd('swLpuPassportEditWindow').show({
						action: 'edit',
						Lpu_id: getGlobalOptions().lpu_id
					});
				},
				text: 'Паспорт МО'
			}, {
				iconCls: 'structure16-2017',
				handler: function() {
					getWnd('swLpuStructureViewForm').show();
				},
				text: 'Структура МО'
			}, {
				iconCls: 'pcard16-2017',
				handler: function() {
					getWnd('swPersonSearchPersonCardAutoWindow').show();
				},
				hidden:  getGlobalOptions().lpu_isLab == 2,
				text: 'Групповое прикрепление'
			}, {
				iconCls: 'workgraph16-2017',
				handler: function() {
					getWnd('swWorkGraphSearchWindow').show();
				},
				hidden: !(isUserGroup('WorkGraph') || isLpuAdmin()),
				text: 'Графики дежурств'
			}, {
				iconCls: 'org16-2017',
				handler: function() {
					getWnd('swOrgViewForm').show();
				},
				hidden: getGlobalOptions().lpu_isLab == 2,
				text: 'Все организации'
			}, {
				iconCls: 'product16-2017',
				handler: function() {
					getWnd('swInvoiceViewWindow').show();
				},
				hidden: getGlobalOptions().lpu_isLab == 2,
				text: 'Учет ТМЦ'
			}, {
				iconCls: 'tools16-2017',
				menu: [{
					hidden: getRegionNick().inlist(['by']),
					text: langs('Глоссарий'),
					handler: function() {
						getWnd('swGlossarySearchWindow').show();
					}
				}, {
					text: langs('Шаблоны документов'),
					handler: function() {
						getWnd('swTemplSearchWindow').show();
					}
				}, {
					text: 'Выгрузка списка прикреплённого населения',
					hidden: (!(isLpuAdmin(getGlobalOptions().lpu_id) && isExpPop()) || (getGlobalOptions().region.nick == 'saratov')),
					handler: function() {
						getWnd('swPersonXmlWindow').show();
					}
				}, {
					text: 'Выгрузка списка прикреплённого населения',
					hidden: (!(isLpuAdmin(getGlobalOptions().lpu_id) && isExpPop()) || (getGlobalOptions().region.nick == 'saratov')),
					handler: function() {
						getWnd('swPersonXmlWindow').show();
					}
				}, {
					text: langs('Выгрузка списка прикрепленного населения в CSV'),
					hidden: (getRegionNick() != 'pskov'),
					handler: function() {
						getWnd('swPersonCSVWindow').show();
					}
				}, {
					hidden: getRegionNick().inlist(['by']),
					text: langs('Выгрузка ЗП в DBF'),

					handler: function() {
						getWnd('swExportDBFWindow').show();
					}
				}, {
					text: langs('Реестры неработающих застрахованных лиц'),
					hidden: !getRegionNick().inlist(['buryatiya']),
					disabled: false,
					handler: function() {
						getWnd('swPersonPolisExportWindow').show({
							AttachLpu_id: getGlobalOptions().lpu_id
						});
					}
				}, {
					text: langs('Экспорт данных для ТФОМС и СМО'),

					hidden: !getRegionNick().inlist(['astra', 'kareliya', 'ekb', 'khak', 'penza']),
					handler: function() {
						getWnd('swHospDataExportForTfomsWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					text: langs('Импорт данных для ТФОМС и СМО'),

					hidden: getRegionNick() != 'astra',
					handler: function() {
						getWnd('swHospDataImportFromTfomsWindow').show();
					}.createDelegate(this)
				}, {
					text: 'Импорт ответа по прикрепленному населению от СМО',
					hidden: getRegionNick() != 'astra',
					handler: function() {
						getWnd('swImportAnswerFromSMO').show();
					}.createDelegate(this)
				}, {
					text: langs('Склад МО'),
					handler: function() {
						getWnd('swSkladMO').show();
					}
				}, {
					text: langs('Импорт карт СМП'),
					hidden: (!getGlobalOptions().region || getGlobalOptions().region.nick != 'ekb'),
					handler: function() {
						getWnd('swAmbulanceCardImportDbfWindow').show();
					}
				}, {
					name: 'export_direction_htm',
					text: 'Выгрузка направлений на ВМП',
					hidden: getRegionNick() != 'kareliya',
					handler: function() {
						getWnd('swDirectionHTMExportWindow').show();
					}.createDelegate(this)
				}, {
					text: langs('Экспорт прикрепленного населения за период'),
					handler: function() {
						getWnd('swExportPersonCardForPeriodWindow').show();
					},
					hidden: (getRegionNick() != 'perm')
				}, {
					text: langs('Экспорт карт диспансерного наблюдения за период'),
					handler: function() {
						getWnd('swExportPersonDispForPeriodWindow').show();
					},
					hidden: !getRegionNick().inlist(['astra', 'perm'])
				}, {
					text: 'Выгрузка карт диспансерного наблюдения',
					handler: function() {
						getWnd('swExportPersonDispCardWindow').show();
					},
					hidden: (getRegionNick() != 'kareliya')
				}, {
					name: 'ExportPersonProf',
					handler: function() {
						getWnd('swPersonProfExportWindow').show();
					},
					hidden: (getRegionNick() != 'kareliya'),
					text: 'Выгрузка данных по профилактическим мероприятиям'
				}, {
					name: langs('Выгрузка регистра медработников для ФРМП новый'),
					text: langs('Выгрузка регистра медработников для ФРМП новый'),
					hidden: false,
					handler: function() {
						getWnd('swExportMedPersonalToXMLFRMPWindow').show();
					}.createDelegate(this)
				}, {
					name: langs('Выгрузка штатного расписания для ФРМП новый'),
					text: langs('Выгрузка штатного расписания для ФРМП новый'),
					hidden: false,
					handler: function() {
						getWnd('swExportMedPersonalToXMLFRMPStaffWindow').show();
					}.createDelegate(this)
				}, {
					text: 'Пакетная идентификация ТФОМС',
					handler: function() {
						getWnd('swPersonIdentPackageWindow').show({ARMType: this.ARMType});
					}.createDelegate(this),
					hidden: !getRegionNick().inlist(['ekb', 'krym'])
				}, {
					text: 'Передача данных в сервис ФРМО',
					handler: function() {
						//getWnd('swExportToFRMOWindow').show();
						//todo: запустить выгрузку по текущему МО пользователя
						sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
					}.createDelegate(this)
				}, {
					text: langs('Журнал работы сервисов'),
					handler: function() {
						getWnd('swServiceListLogWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					nn: 'action_ExportEvnPrescrMse',
					text: 'Экспорт направлений на МСЭ',
					hidden: getRegionNick() == 'kz',
					handler: function() {
						getWnd('swEvnPrescrMseExportWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				},

				// #175117
				// Кнопка для открытия формы "Журнал учета рабочего времени сотрудников":
				{
					text: langs('Журнал учета рабочего времени сотрудников'),

					handler: function()
					{
						var cur = sw.Promed.MedStaffFactByUser.current;

						getWnd('swTimeJournalWindow').show(
							{
								ARMType: (cur ? cur.ARMType : undefined),
								MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined),
								Lpu_id: (cur ? cur.Lpu_id : undefined)
							});
					}
				}, {
					text: 'Отчеты в DBF формате',
					hidden: getRegionNick() !== 'vologda',
					handler: function() {
						getWnd('swReportsInDBFFormat').show();
					}.createDelegate(this)
				}, {
					text: langs('Удаленные данные'),
					hidden: getRegionNick() !== 'vologda',
					handler: function() {
						var cur = sw.Promed.MedStaffFactByUser.current;
						getWnd('delDocsSearchWindow').show({MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined)});
					}
				},

				// #175117
				// Кнопка для открытия формы "Журнал учета рабочего времени сотрудников":
				{
					text: langs('Журнал учета рабочего времени сотрудников'),
					handler: function()
					{
						var cur = sw.Promed.MedStaffFactByUser.current;

						getWnd('swTimeJournalWindow').show(
							{
								ARMType: (cur ? cur.ARMType : undefined),
								MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined),
								Lpu_id: (cur ? cur.Lpu_id : undefined)
							});
					}
				}
				],
				hidden: ((getRegionNick() == 'saratov') || (getGlobalOptions().lpu_isLab == 2)),
				text: 'Инструментарий'
			}, {
				iconCls: 'spr16-2017',
				menu: [{
					text: langs('Тарифы и объемы'),
					handler: function () {
						var params = {};
						params.readOnly = (getRegionNick() == 'vologda') ? false : true;
						getWnd('swTariffVolumesViewWindow').show(params);
					}
				}, {
					text: langs('Тарифы ТФОМС'),
					handler: function() {
						getWnd('swTariffValueListWindow').show({
							ARMType: this.ARMType
						});
					}.createDelegate(this),
					hidden: !getRegionNick().inlist([ 'penza' ])
				}, {
					text: langs('Комплексные услуги'),
					handler: function() {
						getWnd('swUslugaComplexViewWindow').show();
					},
					hidden: true
				}, {
					text: langs('Справочник услуг'),
					handler: function() {
						getWnd('swUslugaTreeWindow').show();
					},
					hidden: !isAdmin && !isLpuAdmin()
				}, {
					text: langs('МКБ-10'),
					handler: function()
					{
						sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
					},
					hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
				}, {
					text: langs('Новые ') + getMESAlias(),
					handler: function()
					{
						getWnd('swMesSearchWindow').show();
					},
					hidden: (!isAdmin || (getRegionNick().inlist(['by'])))
				}, {
					text: getMESAlias(),
					handler: function()
					{
						getWnd('swMesOldSearchWindow').show();
					},
					hidden: getRegionNick().inlist(['by']) // TODO: После тестирования доступ должен быть для всех
				}, {
					text: langs('Наименования мест хранения'),
					handler: function()
					{
						getWnd('swGoodsStorageViewWindow').show();
					}
				}, {
					text: 'Справочники системы учета медикаментов',
					handler: function()
					{
						getWnd('swDrugDocumentSprWindow').show();
					}
				}, {
					text: 'Перечни медикаментов',
					handler: function()
					{
						getWnd('swDrugListSprWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					text: langs('Номенклатурный справочник'),
					handler: function()
					{
						getWnd('swDrugNomenSprWindow').show({readOnly: false});
					}
				}, {
					text: langs('Лекарственные средства'),
					menu: [{
						text: langs('Экстемпоральные рецептуры'),
						handler: function() {
							getWnd('swExtemporalViewWindow').show();
						}
					}, {
						hidden: getRegionNick().inlist(['by','kz']),
						text: langs('Предельные надбавки на ЖНВЛП'),
						handler: function() {
							getWnd('swDrugMarkupViewWindow').show();
						}
					}, {
						hidden: getRegionNick().inlist(['by','kz']),
						text: langs('Цены на ЖНВЛП'),
						handler: function() {
							getWnd('swJNVLPPriceViewWindow').show();
						}
					}, {
						text: langs('Справочник фальсификатов и забракованных серий ЛС'),
						handler: function()
						{
							getWnd('swPrepBlockViewWindow').show();
						}
					}, {
						text: getRLSTitle(),
						handler: function()
						{
							getWnd('swRlsViewForm').show();
						},
						hidden: false
					}]
				}, {
					text: langs('ЕРМП'),
					menu: [{
						text: langs('Должности'),
						handler: function()
						{
							window.gwtBridge.runDictionary(getPromedUserInfo(), 'Post', main_center_panel);
						}
					},  {
						text: langs('Причины невыплат'),
						handler: function()
						{
							window.gwtBridge.runDictionary(getPromedUserInfo(), 'SkipPaymentReason', main_center_panel);
						}
					}, {
						text: langs('Режимы работы'),
						handler: function()
						{
							window.gwtBridge.runDictionary(getPromedUserInfo(), 'WorkMode', main_center_panel);
						}
					}, {
						text: langs('Специальности'),
						handler: function()
						{
							window.gwtBridge.runDictionary(getPromedUserInfo(), 'Speciality', main_center_panel);
						}
					}, {
						text: langs('Дипломные специальности'),
						handler: function()
						{
							window.gwtBridge.runDictionary(getPromedUserInfo(), 'DiplomaSpeciality', main_center_panel);
						}
					}, {
						text: langs('Тип записи окончания работы'),
						handler: function()
						{
							window.gwtBridge.runDictionary(getPromedUserInfo(), 'LeaveRecordType', main_center_panel);
						}
					}, {
						text: langs('Тип образования'),
						handler: function()
						{
							window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationType', main_center_panel);
						}

					}, {
						text: langs('Учебное учреждение'),
						handler: function()
						{
							window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationInstitution', main_center_panel);
						}
					}]
				}, {
					text: 'Справочник связи МО с бюро МСЭ',
					handler: function() {
						getWnd('swLpuMseLinkViewWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}],
				text: 'Справочники'
			}, {
				nn: 'action_LpuVIPPerson',
				text: langs('VIP Пациенты'),
				tooltip: langs('VIP Пациенты'),
				iconCls: 'vip-client',
				hidden: (getRegionNick().inlist(['kz']) || !isSuperAdmin() && !(isUserGroup('VIPRegistry')&& isLpuAdmin())),
				handler: function () {
					getWnd('AdminVIPPersonWindow').show();
				}
			}, {
				iconCls: 'contragent16-2017',
				handler: function() {
					getWnd('swContragentViewWindow').show();
				},
				hidden: getGlobalOptions().lpu_isLab == 2,
				text: 'Контрагенты'
			}, {
				iconCls: 'stick16-2017',
				menu: [{
					text: langs('Реестры ЛВН'),
					handler: function(){
						getWnd('swRegistryESViewWindow').show();
					}
				}, {
					text: 'Запросы в ФСС',
					handler: function(){
						getWnd('swStickFSSDataViewWindow').show();
					}
				}, {
					text: 'Номера ЭЛН',
					handler: function(){
						getWnd('swRegistryESStorageViewWindow').show();
					}
				}],
				hidden: (getRegionNick().inlist(['kz']) || (getGlobalOptions().lpu_isLab == 2)),
				text: 'ЭЛН'
			}, {
				iconCls: 'registers16-2017',
				hidden: !getRegionNick().inlist(['astra', 'ufa', 'kareliya', 'krym', 'perm', 'pskov']),
				handler: function() {
					getWnd('swPlanVolumeViewWindow').show();
				},
				text: 'Планирование объёмов мед. помощи (бюджет)'
			}, {
				iconCls: 'reports16-2017',
				handler: function() {
					if (sw.codeInfo.loadEngineReports) {
						getWnd('swReportEndUserWindow').show();
					} else {
						getWnd('reports').load({
							callback: function (success) {
								sw.codeInfo.loadEngineReports = success;
								getWnd('swReportEndUserWindow').show();
							}
						});
					}
				},
				text: 'Отчеты'
			}, {
				iconCls: 'numerator16-2017',
				handler: function() {
					getWnd('swNumeratorListWindow').show();
				},
				text: 'Нумераторы'
			}, {
				iconCls: 'doubleshistory16-2017',
				handler: function() {
					getWnd('swPersonUnionHistoryWindow').show();
				},
				hidden: getGlobalOptions().lpu_isLab == 2,
				text: 'История модерации двойников'
			}, {
				iconCls: 'disp16-2017',
				handler: function() {
					getWnd('swPersonDopDispPlanListWindow').show();
				},
				text: 'Диспансеризация и профосмотры'
			}, {
				iconCls: 'storage16-2017',
				handler: function() {
					getWnd('swStorageZoneViewWindow').show();
				},
				text: 'Размещение на складах'
			}, {
				iconCls: 'eq16-2017',
				menu: [{
					text: 'Справочник электронных очередей', //'Электронная очередь',
					handler: function() {
						if (!getWnd('swElectronicQueueListWindow').isVisible())
							getWnd('swElectronicQueueListWindow').show({
								mode: 'LpuAdmin'
							});
					}
				}, {
					text: 'Справочник электронных табло', //'Электронное табло',
					handler: function() {
						if (!getWnd('swElectronicScoreboardListWindow').isVisible())
							getWnd('swElectronicScoreboardListWindow').show({
								mode: 'LpuAdmin'
							});
					}
				}, {
					text: 'Справочник инфоматов', //'Инфомат',
					handler: function() {
						if (!getWnd('swElectronicInfomatListWindow').isVisible())
							getWnd('swElectronicInfomatListWindow').show({
								mode: 'LpuAdmin'
							});
					}
				}, {
					text: 'Справочник поводов обращений',
					handler: function() {
						if (!getWnd('swElectronicTreatmentListWindow').isVisible())
							getWnd('swElectronicTreatmentListWindow').show({
								mode: 'LpuAdmin'
							});
					}
				}],
				text: 'Электронная очередь'
			}, {
				iconCls: 'replacement16-2017',
				handler: function() {
					getWnd('swMedStaffFactReplaceViewWindow').show();
				},
				text: 'График замещений'
			}, {
				iconCls: 'cabinet16-2017',
				menu: [{
					text: 'Справочник кабинетов',
					handler: function() {
						getWnd('swLpuBuildingOfficeListWindow').show();
					}
				}, {
					text: 'Расписание работы врачей',
					handler: function() {
						getWnd('swLpuBuildingScheduleWorkDoctorWindow').show();
					}
				}],
				text: 'Справочник кабинетов'
			}, {
				hidden: getRegionNick() == 'kz',
				iconCls: 'remd16-2017',
				menu: [{
					text: 'Подписание медицинской документации',
					handler: function() {
						getWnd('swEMDSearchUnsignedWindow').show();
					}
				}, {
					text: 'Региональный РЭМД',
					handler: function() {
						getWnd('swEMDSearchWindow').show({ArmType: 'lpuadmin'});
					}
				}, {
					text: 'Настройки правил подписания документов',
					handler: function() {
						getWnd('swEMDDocumentSignRulesWindow').show({
							ArmType: 'lpuadmin'
						});
					}
				}],
				text: 'Региональный РЭМД'
			}, {
				hidden: getRegionNick() == 'kz',
				iconCls: 'remd-egisz16-2017',
				handler: function() {
					getWnd('swEMDJournalQueryWindow').show();
				},
				text: 'РЭМД ЕГИСЗ'
			}, {
				iconCls: 'registers16-2017',
				menu: [{
					text: 'Планирование вакцинации',
					handler: function() {
						getWnd('amm_StartVacPlanForm').show();
					}
				}, {
					text: 'Открыть список заданий на планирование вакцинации',
					handler: function() {
						getWnd('amm_ListTaskForm').show();
					}
				}],
				text: 'Иммунопрофилактика'
			},
				{
					iconCls: 'timetable16-2017',
					handler: function() {
						getWnd('swEvnQueueWaitingListJournal').show();
					},
					text: 'Листы ожидания'
				}
			, {
				//iconCls: 'registers16-2017',
				menu: [{
					text: 'Журнал Родовых сертификатов',
					handler: function () {
						getWnd('swEvnErsJournalWindow').show();
					}
				}, {
					text: 'Журнал Талонов',
					handler: function () {
						getWnd('swEvnErsTicketJournalWindow').show();
					}
				}, {
					text: 'Журнал учета детей',
					handler: function () {
						getWnd('swEvnErsChildJournalWindow').show();
					}
				}, {
					text: 'Реестры талонов и счета на оплату',
					handler: function () {
						getWnd('swErsRegistryJournalWindow').show();
					}
				}],
				text: 'ЭРС'
			}]
		});

		win.filterPanel = Ext6.create('Ext6.form.FormPanel', {
			autoScroll: true,
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 20px 30px 0px 0px;',
			cls: 'person-search-input-panel',
			region: 'north',
			items: [{
				border: false,
				layout: 'column',
				padding: '0 0 0 28',
				items: [{
					border: false,
					layout: 'anchor',
					defaults: {
						anchor: '100%',
						labelWidth: 65,
						width: 250,
						listeners: {
							specialkey: function (field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									win.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Фамилия',
						name: 'Person_SurName'
					}, {
						xtype: 'textfield',
						fieldLabel: 'Логин',
						name: 'login'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 27',
					defaults: {
						anchor: '100%',
						labelWidth: 65,
						width: 250,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									win.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Имя',
						name: 'Person_FirName'
					}, {
						xtype: 'textfield',
						fieldLabel: 'Группа',
						name: 'group'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%',
						labelWidth: 75,
						width: 250,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									win.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Отчество',
						name: 'Person_SecName'
					}, {
						xtype: 'textfield',
						fieldLabel: 'Описание',
						name: 'desc'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%'
					},
					items: [{
						border: false,
						style: 'margin-top: 3px;',
						items: [{
							width: 100,
							cls: 'button-secondary',
							text: 'Очистить',
							xtype: 'button',
							cls: 'button-secondary',
							handler: function() {
								win.doReset();
							}
						}]
					}, {
						border: false,
						style: 'margin-top: 8px;',
						items: [{
							width: 100,
							cls: 'button-primary',
							text: 'Найти',
							xtype: 'button',
							handler: function() {
								win.doSearch();
							}
						}]
					}]
				}]
			}]
		});

		win.cardPanel = new Ext6.Panel({
			dockedItems: [ win.leftMenu ],
			animCollapse: false,
			floatable: false,
			collapsible: false,
			flex: 100,
			region: 'center',
			layout: 'border',
			activeItem: 0,
			border: false,
			items: [
				win.filterPanel,
				win.mainGrid
			]
		});

		win.mainPanel = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			border: false,
			items: [ win.cardPanel ]
		});

		Ext6.apply(win, {
			referenceHolder: true, // чтобы ЛУКап заработал по референсу
			reference: 'swLpuAdminWorkPlaceWindowLayout_' + win.id,
			items: [win.mainPanel, win.FormPanel],
			/*buttons: [
				{
					handler: function() {ShowHelp(this.up('window').title)},
					iconCls: 'help16',
					text: BTN_FRMHELP
				},
				{
					handler: function() {win.hide()},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			]*/
		});

		this.callParent(arguments);
	}
});
