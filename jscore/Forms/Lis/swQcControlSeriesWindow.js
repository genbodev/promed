/**
* ЛИС: форма "Контрольная серия"
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

sw.Promed.swQcControlSeriesWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swQcControlSeriesWindow',
	title: langs('Контрольная серия: Добавление'),
	modal: true,
	maximized: false,
	width: 400,
	height: 100,
	show: function() {
		var params = arguments[0],
			win = this,
			baseForm = win.formPanel.getForm();

		win.action = params.action ? params.action : 'view';
		win.callback = params.callback ? params.callback : Ext.emptyFn;
		win.owner = params.owner ? params.owner : false;
		sw.Promed.swQcControlSeriesWindow.superclass.show.apply(win, arguments);

		baseForm.reset();
		baseForm.setValues(params);

	},

	initComponent: function() {
		var win = this;
		win.formPanel = new sw.Promed.FormPanel({
			saveUrl: '/?c=QcControlSeries&m=doSave',
			url: '/?c=QcControlSeries&m=loadEditForm',
			labelWidth: 150,
			allowBlank: false,
			items: [
				{
					xtype: 'hidden',
					name: 'QcControlSeries_id'
				},
				{
					xtype: 'hidden',
					name: 'QcControlMaterialValue_id'
				},
				{
					xtype: 'hidden',
					name: 'NextStage_id',
					value: 1
				},
				{
					xtype: 'hidden',
					name: 'Analyzer_id'
				},
				{
					fieldLabel: langs('Наименование серии'),
					xtype: 'textfield',
					name: 'QcControlSeries_Name',
					maskRe: /[а-яА-Я\w\d]/
				}
			],
			reader: new Ext.data.JsonReader({
					success: function() {
						
					}
				},
				[
					'QcControlSeries_id',
					'Analyzer_id',
					'QcControlMaterial_id',
					'QcControlSeries_Name'
				]
			),
			afterSave: function(data) {
				win.hide();
				win.callback(win.owner,data.QcControlSeries_id);
			}
		})
		Ext.apply(win, { items: win.formPanel });
		sw.Promed.swQcControlSeriesWindow.superclass.initComponent.apply(this, arguments);
	}
});