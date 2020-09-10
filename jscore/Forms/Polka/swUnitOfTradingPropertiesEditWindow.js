/**
* Форма свойств лота
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Alexander Kurakin
* @copyright    Copyright (c) 2016 Swan Ltd.
* @version      2016
*/

sw.Promed.swUnitOfTradingPropertiesEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Свойства лота',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 640,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	doSave: Ext.emptyFn,
	owner: null,
	shim: false,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swUnitOfTradingPropertiesEditWindow',
	
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},
	
	show: function() {
		sw.Promed.swUnitOfTradingPropertiesEditWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] || !arguments[0].doSave ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		this.doSave = (arguments[0].doSave) ? arguments[0].doSave : Ext.emptyFn;
		
		var bf = this.Form.getForm();
		bf.findField('DrugFinance_id').setValue(arguments[0].DrugFinance_id);
		
		var Org_id = this.Form.getForm().findField('Org_aid');
		if(getGlobalOptions().orgtype == 'lpu'){
			Org_id.setFieldValue('Org_id',getGlobalOptions().org_id);
			Org_id.setDisabled(true);
		} else {
			Org_id.setFieldValue('Org_id',0);
			Org_id.setDisabled(false);
		}

		var today = new Date();
		var mm = today.getMonth()+1; //January is 0!
		var yyyy = today.getFullYear();
		if(mm>6)
			yyyy += 1;
		var setDate = new Date(yyyy,11,31);
		this.Form.getForm().findField('WhsDocumentProcurementRequest_setDate').setValue(setDate);

		this.Form.getForm().findField('WhsDocumentPurchType_id').setValue(3);

		this.center();
	},

	initComponent: function() {
		var wnd = this;

		this.Form = new Ext.FormPanel({
			frame: true,
			defaults: {
				labelAlign: 'right'
			},
			layout: 'form',
			labelWidth: 170,
			items: [{
				layout: 'form',
				items: [ {
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel : 'Организация',
						mode: 'local',
						store: new Ext.data.SimpleStore(
						{
							key: 'Org_id',
							fields:
							[
								{name: 'Org_id', type: 'int'},
								{name: 'Org_Name', type: 'string'}
							],
							data: [[0, 'Министерство здравоохранения'], [getGlobalOptions().org_id,getGlobalOptions().org_nick]]
						}),
						editable: false,
						triggerAction: 'all',
						displayField: 'Org_Name',
						valueField: 'Org_id',
						tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
						hiddenName: 'Org_aid',
						anchor: '100%',
						xtype: 'combo'
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						allowBlank: false,
						comboSubject: 'PersonRegisterType',
						fieldLabel: 'Тип регистра',
						listeners: {
							'change': function (combo,oldvalue,newvalue) {
								var form = this.ownerCt.ownerCt.ownerCt.getForm();
								var costItemType = form.findField('WhsDocumentCostItemType_id');
								costItemType.clearValue();
								costItemType.onLoadStore = function(store){
									if(store.data.length != 0)
										this.setValue(store.getAt(0).id);
									var form = this.ownerCt.ownerCt.ownerCt.getForm();
									var BudgetFormType = form.findField('BudgetFormType_id');
									BudgetFormType.clearValue();
									Ext.Ajax.request({
										url: '/?c=UnitOfTrading&m=getBudgetFormType',
										success: function(response){
											var response_obj = Ext.util.JSON.decode(response.responseText);
											if ( response_obj && !Ext.isEmpty(response_obj[0]) && !Ext.isEmpty(response_obj[0].BudgetFormType_id) ) {
												BudgetFormType.setValue(response_obj[0].BudgetFormType_id);
											}
										}.createDelegate(this),
										params: {
												WhsDocumentCostItemType_id: this.getValue(),
												DrugFinance_id: form.findField('DrugFinance_id').getValue()
										}
									});
								};
								costItemType.getStore().load({params: {where: " where PersonRegisterType_id = "+combo.getValue()}});
							}
						}
					}, {
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						disabled: true,
						//allowBlank: false,
						comboSubject: 'DrugFinance',
						fieldLabel: lang['istochnik_finansirovaniya']
					}, {
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						disabled: true,
						//allowBlank: false,
						comboSubject: 'WhsDocumentCostItemType',
						fieldLabel: lang['statya_rashoda']
					}, {
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						disabled: true,
						//allowBlank: false,
						comboSubject: 'BudgetFormType',
						fieldLabel: 'Целевая статья'
					}, {
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						allowBlank: false,
						comboSubject: 'PurchObjType',
						fieldLabel: 'Объект закупки'
					}]
				}, {
					layout: 'form',
					items: [{
						layout: 'form',
						items:[{
							xtype: 'swcommonsprcombo',
							anchor: '100%',
							allowBlank: false,
							comboSubject: 'WhsDocumentPurchType',
							fieldLabel: 'Вид закупа'
						}]
					}]
				}, {
					allowBlank: false,
					fieldLabel: 'Срок действия контракта',
					name: 'WhsDocumentProcurementRequest_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
					width: 100,
					xtype: 'swdatefield'
				}]
			}]
		});
		
		Ext.apply(this, {
			items: [this.Form],
			buttons: [{
				handler: function(button, event) {
					var bf = this.Form.getForm();
					if( !bf.isValid() ) {
						sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_korrektno']);
						return false;
					}
					var params = bf.getValues();
					if (bf.findField('Org_aid').disabled) {
						params.Org_aid = bf.findField('Org_aid').getValue();
					}
					if (bf.findField('DrugFinance_id').disabled) {
						params.DrugFinance_id = bf.findField('DrugFinance_id').getValue();
					}
					if (bf.findField('WhsDocumentCostItemType_id').disabled) {
						params.WhsDocumentCostItemType_id = bf.findField('WhsDocumentCostItemType_id').getValue();
					}
					if (bf.findField('BudgetFormType_id').disabled) {
						params.BudgetFormType_id = bf.findField('BudgetFormType_id').getValue();
					}

					params.WhsDocumentProcurementRequest_setDate = Ext.util.Format.date(bf.findField('WhsDocumentProcurementRequest_setDate').getValue(),'Y-m-d');
					
					wnd.doSave(params);
					wnd.hide();
				}.createDelegate(this),
				scope: this,
				iconCls: 'ok16',
				text: 'Выбрать'
			},
			'-',
			HelpButton(this),
			{
				text: lang['otmena'],
				tabIndex: -1,
				tooltip: lang['otmena'],
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}]
		});
		sw.Promed.swUnitOfTradingPropertiesEditWindow.superclass.initComponent.apply(this, arguments);
	}
});