/**
 * swExportToFRMRWindow - окно экспорта данных в сервис ФРМР
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			08.2017
 */
/*NO PARSE JSON*/

sw.Promed.swExportToFRMRWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swExportToFRMRWindow',
	width: 400,
	autoHeight: true,
	modal: true,
	maximizable: false,
	title: 'Передача данных в сервис ФРМР',

	doExport: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();

		var params = {
			Lpu_id: base_form.findField('Lpu_id').getValue()
		};

		win.getLoadMask('Запуск передачи данных в сервис ФРМР').show();
		Ext.Ajax.request({
			url: '/?c=ServiceFRMR&m=runExport',
			params: params,
			callback: function() {
				win.getLoadMask().hide();
				win.hide();
			}
		});
	},

	show: function() {
		sw.Promed.swExportToFRMRWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		if (getGlobalOptions().lpu_id) {
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
		}
	},

	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoWidth: false,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 60,
			items: [{
				xtype: 'swlpucombo',
				anchor: '100%',
				fieldLabel: langs('МО'),
				name: 'Lpu_id'
			}]
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doExport();
					}.createDelegate(this),
					//iconCls: 'save16',
					text: 'Запуск'
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
			layout: 'form',
			items: [this.FormPanel]
		});

		sw.Promed.swExportToFRMRWindow.superclass.initComponent.apply(this, arguments);
	}
});