/**
* ЛИС: форма "Контроль качества"
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

sw.Promed.swQcControlMaterialWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swQcControlMaterialWindow',
	titleString: langs('Контрольные материалы'),
	modal: true,
	maximized: false,
	width: 400,
	height: 220,

	show: function() {
		var params = arguments[0],
			win = this,
			baseForm = win.formPanel.getForm();
		win.action = params.action ? params.action : 'view';
		win.callback = params.callback ? params.callback : Ext.emptyFn;
		win.owner = params.owner ? params.owner : false;
		sw.Promed.swQcControlMaterialWindow.superclass.show.apply(win, arguments);

		switch (win.action) {
			case "edit":
				win.setTitle(win.titleString + ': Редактирование');
				break;
			case "add":
				win.setTitle(win.titleString + ": Добавление");
				break;
			default:
				win.setTitle(win.titleString + ': Просмотр');
		}

		win.enableEdit( win.action.inlist(["edit","add"]));
		//if(win.action.inlist(['edit','view']))
		//	win.loadForm({ QcControlMaterial_id: params.QcControlMaterial_id })
		baseForm.reset();
		baseForm.setValues(params);
	},

	initComponent: function() {
		var win = this;
		win.formPanel = new sw.Promed.FormPanel({
			saveUrl: '/?c=QcControlMaterial&m=doSave',
			url: '/?c=QcControlMaterial&m=loadEditForm',
			object: 'QcControlMaterial',
			identField: 'QcControlMaterial_id',
			labelWidth: 150,
			items: [
				{
					xtype: 'hidden',
					name: 'QcControlMaterial_id',
					allowBlank: true
				},
				{
					xtype: 'textfield',
					fieldLabel: langs('Наименование'),
					name: 'QcControlMaterial_Name',
					allowBlank: false
				},
				{
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Вид материала'),
					hiddenName: 'QcControlMaterialType_id',
					comboSubject: 'QcControlMaterialType',
					allowBlank: false
				},
				{
					xtype: 'checkbox',
					fieldLabel: langs('Аттестованн'),
					name: 'QcControlMaterial_IsAttested',
					allowBlank: false
				},
				{
					xtype: 'textfield',
					fieldLabel: langs('Лот'),
					name: 'QcControlMaterial_LotNum'
				},
				{
					xtype: 'textfield',
					fieldLabel: langs('Каталожный номер'),
					name: 'QcControlMaterial_CatalogNum'
				},
				{
					xtype: 'swdatefield',
					fieldLabel: langs('Срок годности'),
					name: 'QcControlMaterial_ExpDate'
				}
			],
			reader: new Ext.data.JsonReader({
					success: function() {
						
					}
				},
				[
					'QcControlMaterial_id',
					'QcControlMaterial_Name',
					'QcControlMaterialType_id',
					'QcControlMaterial_LotNum',
					'QcControlMaterial_CatalogNum',
					'QcControlMaterial_ExpDate',
					'QcControlMaterial_IsAttested',
					'QcControlMaterial_begDT',
					'QcControlMaterial_endDT'
				]
			),
			afterSave: function(data) {
				win.hide();
				win.callback(win.owner,data.QcControlMaterial_id);
			}
		})
		Ext.apply(win, { items: win.formPanel });
		sw.Promed.swQcControlMaterialWindow.superclass.initComponent.apply(this, arguments);
	}
});