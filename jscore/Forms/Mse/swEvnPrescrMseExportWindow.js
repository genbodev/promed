/**
 * swEvnPrescrMseExportWindow - окно экспорта направлений на МСЭ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Mse
 * @access       	public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			02.02.2018
 */
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrMseExportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swEvnPrescrMseExportWindow',
	width: 470,
	autoHeight: true,
	modal: true,
	title: langs('Экспорт направлений на МСЭ'),

	doExport: function() {
		var wnd = this;

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {};

		params.ARMType = this.ARMType;
		params.MedService_id = this.MedService_id;

		if (base_form.findField('Lpu_oid').disabled) {
			params.Lpu_oid = base_form.findField('Lpu_oid').getValue();
		}
		if (base_form.findField('EvnStatus_id').disabled) {
			params.EvnStatus_id = base_form.findField('EvnStatus_id').getValue();
		}

		this.LinkPanel.setLink(null, null, null);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if (action.result && typeof action.result.link !== "undefined") {
					this.LinkPanel.setLink(action.result.link, action.result.errorlink, action.result.commentlink);
				}
			}.createDelegate(this),
			failure: function(response) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	resetFormParams: function() {
		var base_form = this.FormPanel.getForm();

		base_form.reset();

		this.LinkPanel.setLink(null, null, null);

		var LpuCombo = base_form.findField('Lpu_oid');
		var EvnStatusCombo = base_form.findField('EvnStatus_id');
		var ExportDateRange = base_form.findField('ExportDateRange');

		LpuCombo.enable();
		LpuCombo.clearBaseFilter();

		EvnStatusCombo.enable();
		EvnStatusCombo.setValue(28);
		EvnStatusCombo.lastQuery = '';
		EvnStatusCombo.setBaseFilter(function(record) {
			return record.get('EvnStatus_id').inlist([28,29,31]);
		});

		var date = new Date().format('d.m.Y');
		ExportDateRange.setValue(date+' - '+date);

		switch(this.ARMType) {
			case 'spec_mz':
				LpuCombo.setBaseFilter(function(record){
					return record.get('Org_tid') == getGlobalOptions().org_id;
				});
				break;

			case 'superadmin':
				break;

			case 'lpuadmin':
				LpuCombo.disable();
				LpuCombo.setValue(getGlobalOptions().lpu_id);
				LpuCombo.setBaseFilter(function(record){
					return record.get('Lpu_id') == getGlobalOptions().lpu_id
				});
				break;

			case 'vk':
				LpuCombo.disable();
				LpuCombo.setValue(getGlobalOptions().lpu_id);
				LpuCombo.setBaseFilter(function(record){
					return record.get('Lpu_id') == getGlobalOptions().lpu_id
				});

				var allowedStatusList = [28];
				if (getRegionNick() == 'kareliya') {
					allowedStatusList = [28, 29, 31]
				} else {
					EvnStatusCombo.disable();
				}

				EvnStatusCombo.setBaseFilter(function(record) {
					return record.get('EvnStatus_id').inlist(allowedStatusList);
				});
				break;

			case 'mse':
				LpuCombo.disable();
				LpuCombo.setValue(getGlobalOptions().lpu_id);
				LpuCombo.setBaseFilter(function(record){
					return record.get('Lpu_id') == getGlobalOptions().lpu_id
				});

				var allowedStatusList = [29, 31];
				if (getRegionNick() == 'kareliya') {
					allowedStatusList = [28, 29, 31]
				}

				EvnStatusCombo.setValue(29);
				EvnStatusCombo.setBaseFilter(function(record) {
					return record.get('EvnStatus_id').inlist(allowedStatusList);
				});
				break;
		}
	},

	show: function() {
		sw.Promed.swEvnPrescrMseExportWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;
		this.MedService_id = null;

		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		if (arguments[0].MedService_id) {
			this.MedService_id = arguments[0].MedService_id;
		}

		this.resetFormParams();
	},

	initComponent: function() {
		this.LinkPanel = new Ext.Panel({
			id: 'EPMEW_TextPanel',
			border: false,
			style: 'margin: 5px 0 10px 125px; font-size: 12px;',
			tpl: new Ext.XTemplate([
				'{[(values.link && values.link != "") ? "<a target=\'_blank\' href=\'" + values.link + "\'>Ссылка на скачивание файла</a>" : "" ]}',
				'{[(values.errorlink && values.errorlink != "") ? "<br><a target=\'_blank\' href=\'" + values.commentlink + "\'>Ошибки экспорта</a> (<a target=\'_blank\' href=\'" + values.errorlink + "\'>файл</a>)" : "" ]}'
			]),
			setLink: function(link, errorlink, commentlink) {
				var panel = this.LinkPanel;
				if (link === null) {
					panel.body.dom.innerHTML = '';
				} else {
					panel.tpl.overwrite(panel.body, {link: link, errorlink: errorlink, commentlink: commentlink});
				}
				this.syncShadow();
			}.createDelegate(this)
		});

		this.FormPanel = new Ext.FormPanel({
			id: 'EPMEW_FormPanel',
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			bodyStyle: 'margin-top: 10px;',
			url: '/?c=Mse&m=exportEvnPrescrMse',
			items: [{
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_oid',
				fieldLabel: 'МО',
				width: 280
			}, {
				allowBlank: false,
				editable: false,
				xtype: 'swevnstatuscombo',
				hiddenName: 'EvnStatus_id',
				fieldLabel: 'Статус направления',
				width: 280
			}, {
				allowBlank: false,
				xtype: 'daterangefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				name: 'ExportDateRange',
				fieldLabel: 'Период',
				width: 280
			}, {
				layout: 'form',
				border: false,
				style: 'margin-left: 125px;',
				items: [{
					xtype: 'checkbox',
					name: 'ExportAllRecords',
					boxLabel: 'Учитывать направления, выгруженные ранее',
					hideLabel: true
				}]
			}, this.LinkPanel]
		});

		Ext.apply(this,
			{
				buttons: [
					{
						handler: function () {
							this.doExport();
						}.createDelegate(this),
						iconCls: 'database-export16',
						id: 'EPMEW_ExportButton',
						text: langs('Сформировать')
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
				items: [
					this.FormPanel
				]
			});

		sw.Promed.swEvnPrescrMseExportWindow.superclass.initComponent.apply(this, arguments);
	}
});