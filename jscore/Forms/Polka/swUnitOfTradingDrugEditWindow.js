/**
* Форма для редактирования данных о медикаменте в списке медикаментов лота
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

sw.Promed.swUnitOfTradingDrugEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 740,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	owner: null,
	shim: false,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swUnitOfTradingDrugEditWindow',
	
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},
	
	show: function() {
		sw.Promed.swUnitOfTradingDrugEditWindow.superclass.show.apply(this, arguments);

		if( !arguments[0] || !arguments[0].action ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		if( !arguments[0].DrugRequest_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_svodnaya_zayavka']);
			this.hide();
			return false;
		}

		this.isSigned = null;
		
		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}

		if( arguments[0].isSigned ) {
			this.isSigned = arguments[0].isSigned;
		}
		
		this.action = arguments[0].action;
		
		this.setTitle('Медикамент лота: ' + this.getActionName(this.action));

		var bf = this.Form.getForm();
		bf.setValues(arguments[0].owner.getGrid().getSelectionModel().getSelected().data);

		if(!arguments[0].owner.getGrid().getSelectionModel().getSelected().data.Tradenames_Name){
			this.findById('tradename_field').hide();
		} else {
			this.findById('tradename_field').show();
		}
		if(Ext.isEmpty(bf.findField('GoodsUnit_id').getValue()) && !Ext.isEmpty(bf.findField('DrugComplexMnn_id').getValue())){
			Ext.Ajax.request({
				url: '/?c=UnitOfTrading&m=getGoodsUnitByDrugComplexMnn',
				success: function(response){
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj && response_obj[0] ) {
						if ( response_obj[0].GoodsUnit_id && bf.findField('GoodsUnit_id').getStore().getById(response_obj[0].GoodsUnit_id) ) {
							bf.findField('GoodsUnit_id').setValue(response_obj[0].GoodsUnit_id);
							if(this.action != 'view'){
								bf.findField('WhsDocumentProcurementRequestSpec_Count').enable();
							}
							if ( response_obj[0].GoodsPackCount_Count ) {
								bf.findField('WhsDocumentProcurementRequestSpec_Count').setValue(response_obj[0].GoodsPackCount_Count);
								var newGoodsCount = (bf.findField('WhsDocumentProcurementRequestSpec_Kolvo').getValue() * bf.findField('WhsDocumentProcurementRequestSpec_Count').getValue());
								bf.findField('GoodsCount').setValue(newGoodsCount);
								if(bf.findField('WhsDocumentProcurementRequestSpec_Count').getValue() > 0) {
									var priMax = bf.findField('WhsDocumentProcurementRequestSpec_PriceMax').getValue();
									priMax = priMax.replace(/ /g,'');
									var newPriceForOkei = (parseFloat(priMax) / bf.findField('WhsDocumentProcurementRequestSpec_Count').getValue());
									newPriceForOkei = newPriceForOkei.toFixed(2);
									bf.findField('PriceForOkei').setValue(newPriceForOkei);
								}
							}
						}
					}
				}.createDelegate(this),
				params: {
					DrugComplexMnn_id: bf.findField('DrugComplexMnn_id').getValue()
				}
			});
		}
		if(bf.findField('WhsDocumentProcurementRequestSpec_Kolvo').getValue()>0 && bf.findField('WhsDocumentProcurementRequestSpec_Count').getValue()>0){
			var newGoodsCount = (bf.findField('WhsDocumentProcurementRequestSpec_Kolvo').getValue() * bf.findField('WhsDocumentProcurementRequestSpec_Count').getValue());
			bf.findField('GoodsCount').setValue(newGoodsCount);
		}
		if(!arguments[0].owner.getGrid().getSelectionModel().getSelected().data.WhsDocumentProcurementRequestSpec_Name){
			bf.findField('WhsDocumentProcurementRequestSpec_Name').setValue(arguments[0].owner.getGrid().getSelectionModel().getSelected().data.Drug_Name);
		}
		var prMax = bf.findField('WhsDocumentProcurementRequestSpec_PriceMax').getValue();
		prMax = prMax.replace(/ /g,'');
		var price = bf.findField('WhsDocumentProcurementRequestSpec_Kolvo').getValue() * parseFloat(prMax);
		bf.findField('Price').setValue(price);

		this.maxKolvo = 0;
		if(arguments[0].owner.getGrid().getSelectionModel().getSelected().data.maxKolvo > 0)
			this.maxKolvo = arguments[0].owner.getGrid().getSelectionModel().getSelected().data.maxKolvo;
		
		if(this.action == 'view')
			this.disableFields(true);
		if(this.action == 'edit')
			this.enableFields();
		this.buttons[0].setDisabled( this.action == 'view' );
		if(bf.findField('GoodsUnit_id').getValue() > 0 && this.action != 'view'){
			bf.findField('WhsDocumentProcurementRequestSpec_Count').enable();
		} else {
			bf.findField('WhsDocumentProcurementRequestSpec_Count').disable();
		}

		bf.findField('WhsDocumentProcurementRequestSpec_Name').focus(true, 100);
		this.center();
	},
	
	getActionName: function(action) {
		return {
			add: lang['dobavlenie'],
			edit: lang['redaktirovanie'],
			view: lang['prosmotr']
		}[action];
	},
	
	doSave: function() {
		var bf = this.Form.getForm();
		if( !bf.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_korrektno']);
			return false;
		}
		if(bf.findField('WhsDocumentProcurementRequestSpec_Kolvo') > this.maxKolvo){
			sw.swMsg.alert(lang['oshibka'], 'Значение поля Кол-во (уп.) не может быть больше '+this.maxKolvo);
			return false;
		}

		var params = new Object();
		params.Okei_id = bf.findField('GoodsUnit_id').getStore().getById(bf.findField('GoodsUnit_id').getValue()).get('Okei_id');

		bf.submit({
			scope: this,
			params: params,
			failure: function() {
			
			},
			success: function(form, act) {
				this.owner.getAction('action_refresh').execute();
				this.hide();
			}
		});
	},
	
	disableFields: function(s) {
		this.Form.findBy(function(f) {
			if( f.xtype && f.xtype != 'hidden' ) {
				f.setDisabled(s);
			}
		});
	},

	enableFields: function() {
		var fields = ['WhsDocumentProcurementRequestSpec_Name','WhsDocumentProcurementRequestSpec_Kolvo','GoodsUnit_id'];
		for(var i = 0;i<fields.length;i++){
			this.Form.getForm().findField(fields[i]).enable();
		}
	},
	
	initComponent: function() {

		this.Form = new Ext.FormPanel({
			url: '/?c=UnitOfTrading&m=saveDrugOfUnitOfTrading',
			frame: true,
			defaults: {
				labelAlign: 'right'
			},
			layout: 'form',
			labelWidth: 150,
			items: [{
				xtype: 'hidden',
				name: 'WhsDocumentProcurementRequestSpec_id'
			}, {
				xtype: 'hidden',
				name: 'DrugComplexMnn_id'
			}, {
				xtype: 'hidden',
				name: 'TRADENAMES_ID'
			}, {
				layout: 'form',
				items: [ {
					layout: 'form',
					items: [{
						disabled: true,
						fieldLabel : 'Код',
						name: 'DrugComplexMnnCode_Code',
						anchor: '98%',
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					items: [{
						disabled: true,
						xtype: 'textfield',
						anchor: '98%',
						name: 'Drug_Name',
						fieldLabel: 'Медикамент'
					}]
				}, {
					layout: 'form',
					id: 'tradename_field',
					items: [{
						disabled: true,
						xtype: 'textfield',
						anchor: '98%',
						name: 'Tradenames_Name',
						fieldLabel: 'Торговое наименование'
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						anchor: '98%',
						allowBlank: false,
						name: 'WhsDocumentProcurementRequestSpec_Name',
						fieldLabel: 'Наименование товара'
					}]
				},
				{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'numberfield',
							width: 195,
							allowBlank: false,
							name: 'WhsDocumentProcurementRequestSpec_Kolvo',
							fieldLabel: 'Кол-во (уп.)',
							listeners: {
								'change':function(field,newval,oldval){
									var form = this.Form.getForm();
									var newGoodsCount = (newval * form.findField('WhsDocumentProcurementRequestSpec_Count').getValue());
									base_form.findField('GoodsCount').setValue(newGoodsCount);

									var packPrice = form.findField('WhsDocumentProcurementRequestSpec_PriceMax').getValue();
									packPrice = packPrice.replace(/ /g,'');
									if(packPrice)
										form.findField('Price').setValue(parseFloat(packPrice)*newval);
								}.createDelegate(this)
							}
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'textfield',
							width: 195,
							disabled: true,
							name: 'WhsDocumentProcurementRequestSpec_PriceMax',
							fieldLabel: 'Цена за упаковку'
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'swcommonsprcombo',
							editable: true,
							width: 195,
							allowBlank: false,
							moreFields: [{ name: 'Okei_id', mapping: 'Okei_id' }],
							comboSubject: 'GoodsUnit',
							fieldLabel: 'Ед. изм. товара',
							listeners: {
								'select':function(combo,newval){
									var form = this.Form.getForm();
									var val = newval.get('GoodsUnit_id');
									form.findField('GoodsUnit_id').fireEvent('change', form.findField('GoodsUnit_id'), val, 0);
								}.createDelegate(this),
								'change':function(combo,newval){
									var val = newval;
									var bf = this.Form.getForm();
									if(newval>0){
										bf.findField('WhsDocumentProcurementRequestSpec_Count').enable();
										if(!Ext.isEmpty(bf.findField('DrugComplexMnn_id').getValue())){
											Ext.Ajax.request({
												url: '/?c=UnitOfTrading&m=getGoodsUnitByDrugComplexMnn',
												success: function(response){
													var response_obj = Ext.util.JSON.decode(response.responseText);
													if ( response_obj && response_obj[0] && response_obj[0].GoodsUnit_id && bf.findField('GoodsUnit_id').getStore().getById(response_obj[0].GoodsUnit_id) ) {
														if(this.action != 'view'){
															bf.findField('WhsDocumentProcurementRequestSpec_Count').enable();
														}
														if ( response_obj[0].GoodsPackCount_Count ) {
															bf.findField('WhsDocumentProcurementRequestSpec_Count').setValue(parseInt(response_obj[0].GoodsPackCount_Count));
															var newGoodsCount = (bf.findField('WhsDocumentProcurementRequestSpec_Kolvo').getValue() * bf.findField('WhsDocumentProcurementRequestSpec_Count').getValue());
															bf.findField('GoodsCount').setValue(newGoodsCount);
															if(bf.findField('WhsDocumentProcurementRequestSpec_Count').getValue() > 0) {
																var priMax = bf.findField('WhsDocumentProcurementRequestSpec_PriceMax').getValue();
																priMax = priMax.replace(/ /g,'');
																var newPriceForOkei = (parseFloat(priMax) / bf.findField('WhsDocumentProcurementRequestSpec_Count').getValue());
																newPriceForOkei = newPriceForOkei.toFixed(2);
																bf.findField('PriceForOkei').setValue(newPriceForOkei);
															}
														}
													} else {
														bf.findField('WhsDocumentProcurementRequestSpec_Count').setValue('');
														bf.findField('GoodsCount').setValue('');
														bf.findField('PriceForOkei').setValue('');
													}
												}.createDelegate(this),
												params: {
													DrugComplexMnn_id: bf.findField('DrugComplexMnn_id').getValue(),
													GoodsUnit_id: val
												}
											});
										}
									} else {
										bf.findField('WhsDocumentProcurementRequestSpec_Count').disable();
									}
								}.createDelegate(this)
							}
						}]
					}]
				}, 
				{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [new Ext.form.TwinTriggerField({
							fieldLabel: 'Кол-во товара в уп.',
							width: 195,
							enableKeyEvents: true,
							listeners: {
								'keydown': function(inp, e) {
									if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
										if ( e.F4 == e.getKey() )
											inp.onTrigger1Click();
										if ( e.DELETE == e.getKey() && e.altKey)
											inp.onTrigger2Click();

										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.browserEvent.returnValue = false;
										e.returnValue = false;

										if ( Ext.isIE ) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
								},
								'keyup': function( inp, e ) {
									if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.browserEvent.returnValue = false;
										e.returnValue = false;

									if ( Ext.isIE ) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
								}
							},
							name: 'WhsDocumentProcurementRequestSpec_Count',
							onTrigger2Click: function() {
								var base_form = this.Form.getForm();
								base_form.findField('WhsDocumentProcurementRequestSpec_Count').setValue('');
							}.createDelegate(this),
							onTrigger1Click: function() {
								var base_form = this.Form.getForm();
								getWnd('swGoodsPackCountSelectWindow').show({
									fields: {
										WhsDocumentProcurementRequestSpec_Count: base_form.findField('WhsDocumentProcurementRequestSpec_Count').getValue(),
										GoodsUnit_id: base_form.findField('GoodsUnit_id').getValue(),
										DrugComplexMnn_id: base_form.findField('DrugComplexMnn_id').getValue(),
										TRADENAMES_ID: base_form.findField('TRADENAMES_ID').getValue()
									},
									callback: function(values) {
										base_form.findField('WhsDocumentProcurementRequestSpec_Count').setValue(values.WhsDocumentProcurementRequestSpec_Count);
										var packPrice = base_form.findField('WhsDocumentProcurementRequestSpec_PriceMax').getValue();
										packPrice = packPrice.replace(/ /g,'');
										var newPriceForOkei = (parseFloat(packPrice) / values.WhsDocumentProcurementRequestSpec_Count);
										newPriceForOkei = newPriceForOkei.toFixed(2);
										base_form.findField('PriceForOkei').setValue(newPriceForOkei);
										var newGoodsCount = (base_form.findField('WhsDocumentProcurementRequestSpec_Kolvo').getValue() * values.WhsDocumentProcurementRequestSpec_Count);
										base_form.findField('GoodsCount').setValue(newGoodsCount);
									},
									onClose: function() {

									},
									disableManualInput: true
								})
							}.createDelegate(this),
							readOnly: true,
							allowBlank: false,
							trigger1Class: 'x-form-search-trigger',
							trigger2Class: 'x-form-clear-trigger',
							width: 195
						})]
					}, {
						layout: 'form',
						items: [{
							xtype: 'numberfield',
							disabled: true,
							width: 195,
							name: 'PriceForOkei',
							fieldLabel: 'Цена за ед. товара'
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'numberfield',
							width: 195,
							disabled: true,
							name: 'GoodsCount',
							fieldLabel: 'Кол-во товара'
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'textfield',
							disabled: true,
							width: 195,
							//allowBlank: false,
							name: 'Price',
							fieldLabel: 'Стоимость'
						}]
					}]
				}]
			}]
		});
		
		Ext.apply(this, {
			items: [this.Form],
			buttons: [{
				handler: this.doSave,
				scope: this,
				iconCls: 'save16',
				text: lang['sohranit']
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
		sw.Promed.swUnitOfTradingDrugEditWindow.superclass.initComponent.apply(this, arguments);
	}
});