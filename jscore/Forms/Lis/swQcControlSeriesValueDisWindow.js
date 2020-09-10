/**
* ЛИС: форма "Причина исключения результата из серии"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    All
* @access     public
* @autor      Salavat Magafurov
* @copyright  Copyright (c) 2019 EMSIS.
* @version    01.07.2019
*/

sw.Promed.swQcControlSeriesValueDisWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swQcControlSeriesValueDisWindow',
	title: langs('Причина исключения результата из серии'),
	modal: true,
	maximized: false,
	width: 400,
	height: 160,

	show: function() {
		var params = arguments[0],
			win = this,
			baseForm = win.formPanel.getForm();

		//win.action = params.action ? params.action : 'view';
		win.callback = params.callback ? params.callback : Ext.emptyFn;
		win.owner = params.owner ? params.owner : false;
		sw.Promed.swQcControlSeriesValueDisWindow.superclass.show.apply(win, arguments);

		baseForm.reset();
		baseForm.setValues(params);
	},

	initComponent: function() {
		var win = this;
		win.formPanel = new sw.Promed.FormPanel({
			labelWidth: 150,
			labelAlign: 'top',
			saveUrl: '/?c=QcControlSeriesValue&m=doDisable',
			bodyStyle: 'padding: 5px;background:#DFE8F6;',
			items: [
				{
					xtype: 'hidden',
					name: 'QcControlSeriesValue_id'
				},
				{
					xtype: 'hidden',
					name: 'QcControlSeriesValue_isDisabled',
					value: 2
				},
				{
					xtype: 'textarea',
					fieldLabel: langs('Причина исключения'),
					name: 'QcControlSeriesValue_Comment',
					allowBlank: false,
					anchor: '100%'
				}
			],
			afterSave: function() {
				win.callback();
				win.hide();
			}
		})
		Ext.apply(win, { items: win.formPanel });
		sw.Promed.swQcControlSeriesValueDisWindow.superclass.initComponent.apply(this, arguments);
	}
});