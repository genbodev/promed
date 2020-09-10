/**
* swGoodsPackCountSelectWindow - окно выбора количества товара в упаковке
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       
* @version      03.2016
*/
/*NO PARSE JSON*/

sw.Promed.swGoodsPackCountSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swGoodsPackCountSelectWindow',
	objectSrc: '/jscore/Forms/Common/swGoodsPackCountSelectWindow.js',

	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closeAction : 'hide',
	draggable: false,
	id: 'swGoodsPackCountSelectWindow',
	layout: 'form',
	modal: true,
	plain: true,
	resizable: false,
	listeners: {
		'hide': function() {
			this.onWinClose();
		}
	},
	doSave: function() {
	    
		var base_form = this.FormPanel.getForm();
		var form = this;

		
		if( !base_form.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_korrektno']);
			return false;
		}
		if(!(base_form.findField('WhsDocumentProcurementRequestSpec_Count').getValue() > 0)){
			sw.swMsg.alert('Ошибка', 'Поле Значение обязательно к заполнению');
			return false;
		}
		var values = {
			WhsDocumentProcurementRequestSpec_Count: base_form.findField('WhsDocumentProcurementRequestSpec_Count').getValue()
		};

		this.callback(values);
		this.hide();
		
	},
	
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			buttonAlign: 'left',
			frame: true,
			id: '',
			labelAlign: 'right',
			labelWidth: 115,

			items: [{
				xtype: 'hidden',
				name: 'GoodsUnit_id'
			}, {
				xtype: 'hidden',
				name: 'DrugComplexMnn_id'
			}, {
				xtype: 'hidden',
				name: 'TRADENAMES_ID'
			}, {
				xtype: 'swgoodspackcountcombo',
				anchor: '98%',
				id: 'GoodsPackCount',
				allowBlank: false,
				fieldLabel: 'Кол-во товара в уп.',
				listeners: {
					'select':function(combo,rec){
						if(rec){
							this.FormPanel.getForm().findField('WhsDocumentProcurementRequestSpec_Count').enable();
							this.FormPanel.getForm().findField('WhsDocumentProcurementRequestSpec_Count').setValue(rec.get('GoodsPackCount_Count'));
						} else {
							this.FormPanel.getForm().findField('WhsDocumentProcurementRequestSpec_Count').disable();
							this.FormPanel.getForm().findField('WhsDocumentProcurementRequestSpec_Count').setValue('');
						}
					}.createDelegate(this)
				},
				onLoadStore: function(store){
					if (store.data.length == 0) {
						sw.swMsg.alert('Предупреждение', 'В справочнике Количество товара в упаковке нет записей для выбранной единицы измерения и медикамента, добавьте новую запись',
							function(){
								var win = this;
								var bf = this.FormPanel.getForm();
								if(getRegionNick() == 'saratov'){
									Ext.Ajax.request({
										url: '/?c=UnitOfTrading&m=calcDrugUnitQuant',
										success: function(response){
											var response_obj = Ext.util.JSON.decode(response.responseText);
											var count = 0;
											if ( response_obj && response_obj[0] && response_obj[0].totalCnt && response_obj[0].totalCnt > 0 ) {
												count = response_obj[0].totalCnt;
											}
											getWnd('swGoodsUnitCountEditWindow').show({
												DrugComplexMnn_id: bf.findField('DrugComplexMnn_id').getValue(),
												TRADENAMES_ID: bf.findField('TRADENAMES_ID').getValue(),
												GoodsUnit_id: bf.findField('GoodsUnit_id').getValue(),
												action: 'add',
												count: count,
												callback: function(){
													win.findById('GoodsPackCount').getStore().load();
													if(!(win.findById('GoodsPackCount').getValue() > 0)){
														win.FormPanel.getForm().findField('WhsDocumentProcurementRequestSpec_Count').disable();
														win.FormPanel.getForm().findField('WhsDocumentProcurementRequestSpec_Count').setValue('');
													}
												}
											});
										}.createDelegate(this),
										params: {
											DrugComplexMnn_id: bf.findField('DrugComplexMnn_id').getValue(),
											GoodsUnit_id: bf.findField('GoodsUnit_id').getValue()
										}
									});
								} else {
									getWnd('swGoodsUnitCountEditWindow').show({
										DrugComplexMnn_id: bf.findField('DrugComplexMnn_id').getValue(),
										TRADENAMES_ID: bf.findField('TRADENAMES_ID').getValue(),
										GoodsUnit_id: bf.findField('GoodsUnit_id').getValue(),
										action: 'add',
										callback: function(){
											win.findById('GoodsPackCount').getStore().load();
											if(!(win.findById('GoodsPackCount').getValue() > 0)){
												win.FormPanel.getForm().findField('WhsDocumentProcurementRequestSpec_Count').disable();
												win.FormPanel.getForm().findField('WhsDocumentProcurementRequestSpec_Count').setValue('');
											}
										}
									});
								}
							}.createDelegate(this)
						);
					}
				}.createDelegate(this)
			}, {
				xtype: 'numberfield',
				disabled: true,
				anchor: '98%',
				allowBlank: false,
				name: 'WhsDocumentProcurementRequestSpec_Count',
				fieldLabel: 'Значение',
				maxLength: 8 
			}]
			
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'ok16',
				text: 'Выбрать'
			}, {
				text: '-'
			},
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swGoodsPackCountSelectWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swGoodsPackCountSelectWindow.superclass.show.apply(this, arguments);

		this.action = '';
		this.callback = Ext.emptyFn;
		this.fields = {};
		this.FormPanel.getForm().reset();
		
		this.onWinClose = Ext.emptyFn;
        var _this = this;

		var base_form = this.FormPanel.getForm();
		
		if ( arguments[0] ) {

			if ( arguments[0].action ) {
				this.action = arguments[0].action;
			}

			if ( arguments[0].callback ) {
				this.callback = arguments[0].callback;
			}

			if ( arguments[0].onClose ) {
				this.onWinClose = arguments[0].onClose;
			}

			if ( arguments[0].fields.GoodsUnit_id ) {
				this.findById('GoodsPackCount').getStore().baseParams.GoodsUnit_id = arguments[0].fields.GoodsUnit_id;
			}

			if ( arguments[0].fields.DrugComplexMnn_id ) {
				this.findById('GoodsPackCount').getStore().baseParams.DrugComplexMnn_id = arguments[0].fields.DrugComplexMnn_id;
			}

			base_form.setValues(arguments[0].fields);
		}
		
		this.findById('GoodsPackCount').getStore().load();
		if(!(this.findById('GoodsPackCount').getValue() > 0)){
			this.FormPanel.getForm().findField('WhsDocumentProcurementRequestSpec_Count').disable();
			this.FormPanel.getForm().findField('WhsDocumentProcurementRequestSpec_Count').setValue('');
		}
			
	},
	title: 'Количество товара в упаковке',
	width: 450
});