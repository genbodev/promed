/**
* ЛИС: форма "Выбор методики ИФА"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    All
* @access     public
* @autor      Salavat Magafurov
* @copyright  Copyright (c) 2019 EMSIS.
* @version    15.11.2019
*/

sw.Promed.swMethodsIFAAnalyzerTestWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swMethodsIFAAnalyzerTestWindow',
	title: langs('Выбор методики'),
	modal: true,
	maximized: false,
	width: 400,
	height: 120,

	show: function () {
		var params = arguments[0],
			win = this,
			baseForm = win.formPanel.getForm();
		win.action = params.action ? params.action : 'view';
		win.callback = params.callback ? params.callback : Ext.emptyFn;
		win.owner = params.owner ? params.owner : false;
		sw.Promed.swMethodsIFAAnalyzerTestWindow.superclass.show.apply(win, arguments);

		win.enableEdit(win.action.inlist(["edit", "add"]));
		//if(win.action.inlist(['edit','view']))
		//	win.loadForm({ QcControlMaterial_id: params.QcControlMaterial_id })
		baseForm.reset();
		baseForm.setValues(params);
	},

	initComponent: function () {
		var win = this;
		win.formPanel = new sw.Promed.FormPanel({
			saveUrl: '/?c=MethodsIFAAnalyzerTest&m=doSave',
			url: '/?c=MethodsIFAAnalyzerTest&m=loadEditForm',
			object: 'MethodsIFAAnalyzerTest',
			style: 'padding: 0 10px 0 10px;',
			labelWidth: 90,
			items: [
				{
					xtype: 'hidden',
					name: 'AnalyzerTest_id'
				},
				{
					xtype: 'hidden',
					name: 'MethodsIFAAnalyzerTest_id',
					allowBlank: true
				},
				{
					xtype: 'swbaselocalcombo',
					fieldLabel: langs('Производитель'),
					anchor: '100%',
					hiddenName: 'FIRMS_ID',
					valueField: 'FIRMS_id',
					displayField: 'FIRMS_Name',
					allowBlank: false,
					store: new Ext.data.JsonStore({
						autoLoad: true,
						url: '/?c=MethodsIFA&m=loadFIRMS',
						fields: [
							{ type: 'int', name: 'FIRMS_id' },
							{ type: 'string', name: 'FIRMS_Name' }
						]
					}),
					listeners: {
						change: function(combo, newValue) {
							var form = win.formPanel.getForm(),
								methodsField = form.findField('MethodsIFA_id');
							methodsField.setDisabled(!newValue);
							methodsField.clearValue();
							methodsField.store.clearFilter();
							methodsField.store.filterBy(function (rec) {
								return rec.get('FIRMS_id') == newValue;
							});
						}
					}
				},
				{ 
					xtype: 'swbaselocalcombo',
					fieldLabel: langs('Методика'),
					anchor: '100%',
					disabled: true,
					hiddenName: 'MethodsIFA_id',
					valueField: 'MethodsIFA_id',
					displayField: 'MethodsIFA_Name',
					allowBlank: false,
					store: new Ext.data.JsonStore({
						autoLoad: true,
						url: '/?c=MethodsIFA&m=loadCombo',
						fields: [
							{ type: 'int', name: 'MethodsIFA_id' },
							{ type: 'int', name: 'FIRMS_id' },
							{ type: 'string', name: 'MethodsIFA_Name' },
							{ type: 'string', name: 'MethodsIFA_Code' }
						]
					})
				}
			],
			afterSave: function (data) {
				win.hide();
				win.callback(win.owner, data.QcControlMaterial_id);
			}
		});
		Ext.apply(win, { items: win.formPanel });
		sw.Promed.swMethodsIFAAnalyzerTestWindow.superclass.initComponent.apply(this, arguments);
	}
});