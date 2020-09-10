/**
* swExportPersonDispForPeriodWindow - окно выгрузки карт диспансерного наблюдения за период
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 - 2016 Swan Ltd.
* @author
* @version      27.07.2016
* @comment      Префикс для id компонентов epdfpw (swExportPersonDispForPeriodWindow)
*/

sw.Promed.swExportPersonDispForPeriodWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'ExportPersonDispForPeriodWindow',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	plain: true,
	resizable: false,
	title: langs('Экспорт карт диспансерного наблюдения за период'),
	width: getRegionNick() == 'astra' ? 600 : 400,

	/* методы */
	createExportFile: function() {
		var
			win = this,
			base_form = win.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( parseInt(base_form.findField('PackageNum').getValue()) == 0 ) {
			base_form.findField('PackageNum').focus(250, true);
			win.TextPanel.getEl().dom.innerHTML = 'Значение поля "Порядковый номер пакета" должно быть больше 0';
			win.TextPanel.render();
			win.syncSize();
			win.syncShadow();
			return false;
		}

		var params = {
			ExportDateRange: base_form.findField('ExportDateRange').getRawValue(),
			PackageNum: base_form.findField('PackageNum').getValue()
		}

		switch ( getRegionNick() ) {
			case 'astra':
				params.OrgSMO_id = base_form.findField('OrgSMO_id').getValue();
				break;
			case 'ekb':
			case 'perm':
				if ( base_form.findField('ExportDateRange').getValue2() > base_form.findField('ReportDate').getValue() ) {
					base_form.findField('ReportDate').focus(250, true);
					win.TextPanel.getEl().dom.innerHTML = 'Значение поля "Отчетная дата" должно быть не более текущей и более даты окончания периода выгрузки';
					win.TextPanel.render();
					win.syncShadow();
					return false;
				}

				params.FileCreationDate = base_form.findField('FileCreationDate').getValue().format('d.m.Y');
				params.ReportDate = base_form.findField('ReportDate').getValue().format('d.m.Y');
				params.TypeFilterLpuAttach_id = base_form.findField('TypeFilterLpuAttach_id').getValue();
				params.TypeFilterLpuCard_id = base_form.findField('TypeFilterLpuCard_id').getValue();
				break;
		}

		win.getLoadMask().show();
		win.buttons[0].disable();

		Ext.Ajax.request({
			failure: function(response, options) {
				win.getLoadMask().hide();
				win.buttons[0].enable();

				win.TextPanel.getEl().dom.innerHTML = response.statusText;

				win.TextPanel.render();
				win.syncSize();
				win.syncShadow();
			},
			params: params,
			timeout: 1800000,
			url: '/?c=PersonDisp&m=exportPersonDispForPeriod',
			success: function(response, options) {
				win.getLoadMask().hide();
				win.buttons[0].enable();

				var
					response_obj = Ext.util.JSON.decode(response.responseText),
					text = '';

				if ( response_obj.success == false ) {
					text = (response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при выгрузке файла');
				}
				else {
					if ( response_obj.Count ) {
						text = text + '<div>Выгружено записей: ' + response_obj.Count + '</div>';
					}

					if ( response_obj.Link ) {
						text = text + '<div><a target="_blank" href="' + response_obj.Link + '">Скачать и сохранить список</a></div>';
					}

					if ( response_obj.success === false ) {
						text = text + response_obj.Error_Msg;
					}
				}

				win.TextPanel.getEl().dom.innerHTML = text;
				win.TextPanel.render();
				win.syncSize();
				win.syncShadow();
			}
		});
	},
	filterOrgSMOCombo: function() {
		var OrgSMOCombo = this.FormPanel.getForm().findField('OrgSMO_id');
		
		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == getRegionNumber());
		});
		OrgSMOCombo.lastQuery = langs('Строка, которую никто не додумается вводить в качестве фильтра, ибо это бред искать СМО по такой строке');
		OrgSMOCombo.setBaseFilter(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == getRegionNumber());
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: langs('Подождите. Идет формирование.') });
		}
		return this.loadMask;
	},
	onHide: Ext.emptyFn,
	show: function() {
		sw.Promed.swExportPersonDispForPeriodWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.FormPanel.getForm(),
			dt = getValidDT(getGlobalOptions().date, ''),
			initialPeriod,
			win = this;

		if ( !isSuperAdmin() && !isLpuAdmin(getGlobalOptions().lpu_id) ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Функционал недоступен'), function() { win.hide(); });
			return false;
		}

		win.onHide = Ext.emptyFn;

		win.buttons[0].enable();

		base_form.reset();

		if ( typeof dt != 'object' ) {
			dt = new Date();
		}

		switch ( getRegionNick() ) {
			case 'astra':
				win.filterOrgSMOCombo();
				break;
			case 'ekb':
			case 'perm':
				base_form.findField('TypeFilterLpuAttach_id').fireEvent('change', base_form.findField('TypeFilterLpuAttach_id'), base_form.findField('TypeFilterLpuAttach_id').getValue());
				base_form.findField('FileCreationDate').setMaxValue(getGlobalOptions().date);
				base_form.findField('FileCreationDate').setValue(getGlobalOptions().date);
				base_form.findField('ReportDate').setMaxValue(getGlobalOptions().date);
				base_form.findField('ReportDate').setMinValue(undefined);
				base_form.findField('ReportDate').setValue(dt.getFirstDateOfMonth().format('d.m.Y')); // 1 число текущего месяца
				break;
		}

		dt = dt.add(Date.MONTH, -1);
		initialPeriod = dt.getFirstDateOfMonth().format('d.m.Y') + ' - ' + dt.getLastDateOfMonth().format('d.m.Y');

		base_form.findField('ExportDateRange').setValue(initialPeriod);
		base_form.findField('PackageNum').setValue(1);

		win.TextPanel.getEl().dom.innerHTML = langs('Выгрузка списка карт диспансерного наблюдения в формате XML');
		win.TextPanel.render();

		win.syncSize();
		win.syncShadow();
	},

	/* конструктор */
	initComponent: function() {
		var win = this;

		win.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'ExportPersonDispForPeriodTextPanel',
			html: langs('Выгрузка списка карт диспансерного наблюдения в формате XML')
		});

		var items = new Array();

		items.push({
			allowBlank: false,
			fieldLabel: 'Период выгрузки',
			name: 'ExportDateRange',
			plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
			width: 180,
			xtype: 'daterangefield'
		});

		switch ( getRegionNick() ) {
			case 'astra':
				items.push({
					fieldLabel: langs('СМО'),
					hiddenName: 'OrgSMO_id',
					listWidth: 500,
					onTrigger2Click: function() {
						if ( this.disabled ) {
							return false;
						}

						var combo = this;

						getWnd('swOrgSearchWindow').show({
							KLRgn_id: getGlobalOptions().region.number,
							object: 'smo',
							onClose: function() {
								combo.focus(true, 200);
							},
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 )
								{
									combo.setValue(orgData.Org_id);
									combo.focus(true, 250);
									combo.fireEvent('change', combo);
								}
								getWnd('swOrgSearchWindow').hide();
							}
						});
					},
					width: 380,
					xtype: 'sworgsmocombo'
				});

				items.push({
					allowBlank: false,
					autoCreate: {tag: "input", maxLength: "4", autocomplete: "off"},
					fieldLabel: 'Порядковый номер пакета',
					maskRe: /[0-9]/,
					name: 'PackageNum',
					width: 50,
					xtype: 'textfield'
				});
				break;
			case 'ekb':
			case 'perm':
				items.push({
					allowBlank: false,
					fieldLabel: 'Дата формирования файла',
					name: 'FileCreationDate',
					width: 100,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				});

				items.push({
					allowBlank: false,
					fieldLabel: 'Отчетная дата',
					name: 'ReportDate',
					width: 100,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				});

				items.push({
					allowBlank: false,
					autoCreate: {tag: "input", maxLength: "3", autocomplete: "off"},
					fieldLabel: 'Порядковый номер пакета',
					maskRe: /[0-9]/,
					name: 'PackageNum',
					width: 100,
					xtype: 'textfield'
				});

				items.push({
					allowBlank: false,
					hiddenName: 'TypeFilterLpuAttach_id',
					value: 2, // Значение по умолчанию «Своя МО».
					valueField: 'TypeFilterLpu_id',
					displayField: 'TypeFilterLpu_Name',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = win.FormPanel.getForm();
							base_form.findField('TypeFilterLpuCard_id').getStore().clearFilter();
							if (newValue != 2) {
								base_form.findField('TypeFilterLpuCard_id').getStore().filterBy(function(rec) {
									return (rec.get('TypeFilterLpu_id') == 2); // только "Своя МО"
								});
								if (base_form.findField('TypeFilterLpuCard_id').getValue() != 2) {
									base_form.findField('TypeFilterLpuCard_id').clearValue();
								}
							}
							base_form.findField('TypeFilterLpuCard_id').lastQuery = '';
						}
					},
					store: new Ext.data.SimpleStore({
						autoLoad: true,
						data: [
							[ 1, 'Все' ],
							[ 2, 'Своя МО' ],
							[ 3, 'Все кроме своей МО' ]
						],
						fields: [
							{ name: 'TypeFilterLpu_id', type: 'int'},
							{ name: 'TypeFilterLpu_Name', type: 'string'}
						],
						key: 'TypeFilterLpu_id',
						sortInfo: { field: 'TypeFilterLpu_id' }
					}),
					fieldLabel: 'МО прикрепления',
					width: 180,
					xtype: 'swbaselocalcombo'
				});

				items.push({
					allowBlank: false,
					hiddenName: 'TypeFilterLpuCard_id',
					value: 2, // Значение по умолчанию «Своя МО».
					valueField: 'TypeFilterLpu_id',
					displayField: 'TypeFilterLpu_Name',
					store: new Ext.data.SimpleStore({
						autoLoad: true,
						data: [
							[ 1, 'Все' ],
							[ 2, 'Своя МО' ],
							[ 3, 'Все кроме своей МО' ]
						],
						fields: [
							{ name: 'TypeFilterLpu_id', type: 'int'},
							{ name: 'TypeFilterLpu_Name', type: 'string'}
						],
						key: 'TypeFilterLpu_id',
						sortInfo: { field: 'TypeFilterLpu_id' }
					}),
					fieldLabel: 'МО карты дисп. наблюдения',
					width: 180,
					xtype: 'swbaselocalcombo'
				});
				break;
		}

		items.push(win.TextPanel);

		win.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'ExportPersonDispForPeriodPanel',
			labelAlign: 'right',
			labelWidth: 180,
			items: items
		});

		Ext.apply(win, {
			autoHeight: true,
			buttons: [{
				id: win.id + 'OkButton',
				handler: function() {
					win.createExportFile();
				},
				iconCls: 'refresh16',
				text: langs('Сформировать')
			}, {
				text: '-'
			},
			HelpButton(win),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				onTabElement: win.id + 'OkButton',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.FormPanel
			]
		});

		sw.Promed.swExportPersonDispForPeriodWindow.superclass.initComponent.apply(win, arguments);
	}
});