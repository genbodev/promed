/**
* ЛИС: форма "Брак планшета"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    All
* @access     public
* @autor      Salavat Magafurov
* @copyright  Copyright (c) 2019 EMSIS.
* @version    21.11.2019
*/

sw.Promed.swTabletDefectWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swTabletDefectWindow',
	title: 'Брак планшета',
	modal: true,
	maximized: false,
	width: 400,
	height: 120,

	show: function () {
		var params = arguments[0],
			win = this,
			baseForm = win.formPanel.getForm();

		win.dbObject = params.dbObject ? params.dbObject : 'Tablet';
		win.isHoleDefect = win.dbObject === 'Hole';
		win.setTitle(win.isHoleDefect ? 'Брак лунки' : 'Брак планшета');

		baseForm.saveUrl = '/?c=' + win.dbObject + '&m=setDefect';
		win.callback = params.callback ? params.callback : Ext.emptyFn;
		win.owner = params.owner ? params.owner : false;
		sw.Promed.swTabletDefectWindow.superclass.show.apply(win, arguments);

		baseForm.reset();
		baseForm.setValues(params);
	},

	initComponent: function () {
		var win = this;
		win.formPanel = new sw.Promed.FormPanel({
			labelWidth: 100,
			bodyStyle: 'padding: 5px;background:#DFE8F6;',
			items: [
				{
					xtype: 'hidden',
					name: '_id'
				},
				{
					xtype: 'swcommonsprcombo',
					fieldLabel: lang['prichina'],
					comboSubject: 'DefectCauseType',
					prefix: 'lis_',
					allowBlank: false,
					anchor: "100%"
				},
				{
					xtype: 'hidden',
					name: '_defectDT',
					value: getGlobalOptions().date
				},
				{
					xtype: 'textfield',
					fieldLabel: 'Комментарий',
					name: '_Comment',
					allowBlank: false,
					anchor: '100%',
					maxLength: 20
				}
			],
			beforeSave: function(params) {
				let object = win.dbObject;
				params[ object + '_id' ] = params['_id'];
				params[ object + '_Comment'] = params['_Comment'];
				params[ object + '_defectDT' ] = params['_defectDT'];

				delete params['_id'];
				delete params['_Comment'];
				delete params['_defectDT'];
			},
			afterSave: function () {
				win.callback();
				win.hide();
			}
		});
		Ext.apply(win, { items: win.formPanel });
		sw.Promed.swTabletDefectWindow.superclass.initComponent.apply(this, arguments);
	}
});