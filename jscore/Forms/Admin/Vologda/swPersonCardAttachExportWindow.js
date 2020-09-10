/**
 * swPersonCardAttachExportWindow - окно экспорта заявлений о прикреплении
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan-it.ru)
 * @version			11.04.2019
 */
/*NO PARSE JSON*/

sw.Promed.swPersonCardAttachExportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonCardAttachExportWindow',
	maximizable: false,
	resizable: false,
	modal: true,
	autoHeight: true,
	width: 390,
	layout: 'form',
	title: 'Экспорт заявлений о прикреплении',
	daysSinceMinDate: function (date) {
		return date/1000/3600/24;
	},
	doExport: function() {
		var wnd = this;
		var baseForm = wnd.FormPanel.getForm();
		var exportButton = Ext.getCmp('PCAEW_ExportButton');

		exportButton.disable();

		if (!baseForm.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					exportButton.enable();
					wnd.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return;
		}

		var begDate = baseForm.findField('dateRange').getValue1();
		var endDate = baseForm.findField('dateRange').getValue2();

		if(this.daysSinceMinDate(endDate) - this.daysSinceMinDate(begDate) > 31)
		{
			wnd.getLoadMask().hide();
			exportButton.enable();
			sw.swMsg.alert(langs('Ошибка'), langs('Период не должен превышать 31 день'));
			return;
		}

		var params = {
			Lpu_aid: baseForm.findField('Lpu_aid').getValue(),
			OrgSMO_id: baseForm.findField('OrgSMO_id').getValue(),
			year: endDate.getFullYear(),
			month: endDate.getMonth() + 1,
			begDate: Ext.util.Format.date(begDate,'d.m.Y'),
			endDate: Ext.util.Format.date(endDate,'d.m.Y'),
			packageNumber: baseForm.findField('packageNumber').getValue()
		};

		wnd.ResultPanel.setData();
		wnd.getLoadMask('Формирование данных...').show();

		Ext.Ajax.request({
			url: '/?c=PersonCard&m=exportPersonCardAttach',
			params: params,
			success: function (response) {
				wnd.getLoadMask().hide();
				exportButton.enable();
				var responseObj = Ext.util.JSON.decode(response.responseText);

				if (responseObj.xmllink || responseObj.loglink) {
					wnd.ResultPanel.setData(responseObj);
					wnd.callback();
				}
			},
			failure: function (response) {
				sw.swMsg.alert(langs('Ошибка'), response.responseText);
				wnd.getLoadMask().hide();
				exportButton.enable();
			}
		});
	},

	show: function() {
		var wnd = this;
		var baseForm = wnd.FormPanel.getForm();
		var regionNumber = getRegionNumber();

		sw.Promed.swPersonCardAttachExportWindow.superclass.show.apply(wnd, arguments);

		this.ARMType = arguments[0] && arguments[0].ARMType ? arguments[0].ARMType : null;
		this.callback = arguments[0] && arguments[0].callback ? arguments[0].callback : Ext.emptyFn;

		baseForm.reset();
		wnd.ResultPanel.setData();

		var lpuCombo = baseForm.findField('Lpu_aid');
		var smoCombo = baseForm.findField('OrgSMO_id');
		var dateRangeField = baseForm.findField('dateRange');

		lpuCombo.setDisabled(this.ARMType != 'superadmin');
		if (this.ARMType != 'superadmin') {
			lpuCombo.setValue(getGlobalOptions().lpu_id);
		}

		smoCombo.disable();
		smoCombo.setBaseFilter(function(record) {
			return record.get('KLRgn_id') == regionNumber;
		});

		var smoIndex = smoCombo.getStore().find('Orgsmo_f002smocod', '35003');
		var smoRecord = smoCombo.getStore().getAt(smoIndex);
		if (smoRecord) smoCombo.setValue(smoRecord.get('OrgSMO_id'));

		var begOfMonth = getValidDT(getGlobalOptions().date,'');
		begOfMonth.setDate(1);
		begOfMonth = Ext.util.Format.date(begOfMonth, 'd.m.Y');

		var endOfMonth = getValidDT(getGlobalOptions().date,'');
		endOfMonth.addMonths(1);
		endOfMonth.setDate(0);
		endOfMonth = Ext.util.Format.date(endOfMonth, 'd.m.Y');

		dateRangeField.setValue(begOfMonth + ' - ' + endOfMonth);
	},

	initComponent: function() {
		var wnd = this;

		wnd.FormPanel = new Ext.form.FormPanel({
			id: 'PCAEW_FormPanel',
			labelAlign: 'right',
			labelWidth: 90,
			items: [{
				allowBlank: false,
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_aid',
				fieldLabel: langs('МО'),
				width: 250,
				listWidth: 500
			}, {
				allowBlank: false,
				xtype: 'sworgsmocombo',
				hiddenName: 'OrgSMO_id',
				fieldLabel: langs('СМО'),
				withoutTrigger: true,
				width: 250,
				listWidth: 500
			}, {
				allowBlank: false,
				xtype: 'daterangefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				name: 'dateRange',
				fieldLabel: langs('Отчетный период'),
				width: 250
			}, /*{
				xtype: 'fieldset',
				title: 'Отчетный период',
				autoHeight: true,
				labelWidth: 80,
				items: [
					this.yearCombo,
					this.monthCombo
				]
			},*/ {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield',
				name: 'packageNumber',
				fieldLabel: langs('Номер пакета'),
				width: 250
			}]
		});

		var getFileNameByLink = function(link) {
			return link?link.split('/').pop():'';
		};

		wnd.ResultPanel = new Ext.Panel({
			id: 'PCAEW_ResultPanel',
			tpl: new Ext.XTemplate(
				'<tpl if="xmllink">',
				'<p style="{style}"><a target="_blank" download href="{xmllink}">Скачать файл {xmlname}</a></p>',
				'</tpl>',
				'<tpl if="loglink">',
				'<p style="{style}"><a target="_blank" href="{loglink}">Скачать файл {logname}</a></p>',
				'</tpl>'
			),
			defaultData: {
				style: 'margin-left: 7px; font-size: 12px;',
				xmllink: '',
				loglink: '',
				xmlname: '',
				logname: ''
			},
			setData: function(_data) {
				var me = wnd.ResultPanel;
				var data = Ext.apply({}, _data, me.defaultData);
				data.xmlname = getFileNameByLink(data.xmllink);
				data.logname = getFileNameByLink(data.loglink);
				me.tpl.overwrite(me.body, data);
				wnd.syncShadow();
			}
		});

		wnd.MainPanel = new Ext.Panel({
			layout: 'form',
			frame: true,
			items: [
				wnd.FormPanel,
				wnd.ResultPanel
			]
		});

		Ext.apply(this, {
			items: [
				wnd.MainPanel
			],
			buttons: [{
				handler: function () {
					wnd.doExport();
				},
				iconCls: 'database-export16',
				id: 'PCAEW_ExportButton',
				text: langs('Экспорт')
			}, {
				text: '-'
			},
				HelpButton(wnd, 1),
				{
					handler: function () {
						wnd.hide();
					},
					iconCls: 'close16',
					id: 'PCAEW_CancelButton',
					text: langs('Закрыть')
				}]
		});

		sw.Promed.swPersonCardAttachExportWindow.superclass.initComponent.apply(wnd, arguments);
	}
});