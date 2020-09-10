/**
 * swHospDataExportForTfomsWindow - окно выгрузки реестров неработающих застрахованныз лиц
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Person
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.01.2014
 */

sw.Promed.swHospDataExportForTfomsWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	id: 'swHospDataExportForTfomsWindow',
	layout: 'form',
	modal: true,
	title: lang['eksport_dannyih_dlya_tfoms_i_smo'],
	width: (getRegionNick() == 'penza' ? 420 : 380),

	/* методы */
	callback: Ext.emptyFn,
	createXML: function() {
		if ( this.formMode == 'export' ) {
			return false;
		}

		this.formMode = 'export';

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					wnd.formMode = 'iddle';
					wnd.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = base_form.getValues();
		params.ARMType = this.ARMType;

		if ( getRegionNick() == 'penza' ) {
			if ( typeof base_form.findField('Period').getValue1() != 'object' || typeof base_form.findField('Period').getValue2() != 'object' ) {
				sw.swMsg.alert(lang['oshibka'], 'Неверно указан период', function() { wnd.formMode = 'iddle'; });
				return false;
			}
			else if ( base_form.findField('Period').getValue1() > base_form.findField('Period').getValue2() ) {
				sw.swMsg.alert(lang['oshibka'], 'Дата начала периода не может быть больше даты окочнания', function() { wnd.formMode = 'iddle'; });
				return false;
			}
			else if ( base_form.findField('Period').getValue1() < base_form.findField('Period').getValue2().add(Date.DAY, -31) ) {
				sw.swMsg.alert(lang['oshibka'], 'Период не может быть больше 31 дня', function() { wnd.formMode = 'iddle'; });
				return false;
			}

			params.Date = base_form.findField('Period').getValue2().format('d.m.Y');

			if ( base_form.findField('ExportLpu_id').disabled ) {
				params.ExportLpu_id = base_form.findField('ExportLpu_id').getValue();
			}
		}

		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите, идет формирование данных..." });
		loadMask.show();

		Ext.Ajax.request({
			failure: function(response, options) {
				wnd.formMode = 'iddle';
				loadMask.hide();
			},
			params: params,
			success: function(response, action) {
				wnd.formMode = 'iddle';
				loadMask.hide();

				if ( response.responseText ) {
					var answer = Ext.util.JSON.decode(response.responseText);

					if ( answer.success ) {
						wnd.TextPanel.getEl().dom.innerHTML = '<a target="_blank" href="' + answer.Link + '">Скачать и сохранить файл</a>';
						wnd.TextPanel.render();
						wnd.syncSize();
						wnd.syncShadow();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], !Ext.isEmpty(answer.Error_Msg) ? answer.Error_Msg : lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			},
			url: '/?c=EvnPS&m=exportHospDataForTfomsToXml'
		});
	},

	show: function() {
		sw.Promed.swHospDataExportForTfomsWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		this.ARMType = null;

		if ( arguments[0] && arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}

		wnd.formMode = 'iddle';

		base_form.reset();

		base_form.findField('ExportLpu_id').setContainerVisible(getRegionNick() == 'penza');
		base_form.findField('Period').setContainerVisible(getRegionNick() == 'penza');
		base_form.findField('Date').setContainerVisible(getRegionNick() != 'penza');
		base_form.findField('Period').setAllowBlank(getRegionNick() != 'penza');
		base_form.findField('Date').setAllowBlank(getRegionNick() == 'penza');

		if ( this.ARMType == 'superadmin' ) {
			base_form.findField('ExportLpu_id').enable();
		}
		else {
			base_form.findField('ExportLpu_id').disable();
			base_form.findField('ExportLpu_id').setValue(getGlobalOptions().lpu_id);
		}

		if ( getRegionNick() == 'penza' ) {
			var currentDT = new Date();

			if ( currentDT.format('H') >= 20 ) {
				base_form.findField('Period').setValue(currentDT.format('d.m.Y') + ' - ' + currentDT.format('d.m.Y'));
			}
			else {
				base_form.findField('Period').setValue(currentDT.add(Date.DAY, -1).format('d.m.Y') + ' - ' + currentDT.add(Date.DAY, -1).format('d.m.Y'));
			}
		} else {
			var currentDT = new Date();
			base_form.findField('Date').setValue(currentDT.format('d.m.Y'));
		}

		base_form.clearInvalid();

		wnd.TextPanel.getEl().dom.innerHTML = '';
		wnd.TextPanel.render();

		wnd.center();
		wnd.syncSize();
		wnd.syncShadow();
	},

	/* конструктор */
	initComponent: function() {
		var wnd = this;

		wnd.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'HDEFTW_XmlTextPanel',
			html: ''
		});

		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px;',
			border: false,
			frame: true,
			id: 'HDEFTW_FormPanel',
			labelAlign: 'right',
			labelWidth: (getRegionNick() == 'penza' ? 120 : 100),
			items: [{
				fieldLabel: lang['mo'],
				hiddenName: 'ExportLpu_id',
				listWidth: 500,
				width: 250,
				xtype: 'swlpucombo'
			}, {
				fieldLabel: 'Отчетный период',
				name: 'Period',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
				width: 170,
				xtype: 'daterangefield'
			}, {
				fieldLabel: lang['data'],
				name: 'Date',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 120,
				xtype: 'swdatefield'
			},
				wnd.TextPanel
			]
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel
			],
			buttons: [
				{
					handler: function () {
						wnd.createXML();
					}.createDelegate(this),
					iconCls: 'refresh16',
					id: 'HDEFTW_ExportButton',
					text: lang['sformirovat']
				},{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					id: 'HDEFTW_CancelButton',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swHospDataExportForTfomsWindow.superclass.initComponent.apply(this, arguments);
	}
});
