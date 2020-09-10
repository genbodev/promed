/**
* форма добавление/редактир-я лота
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      03.2016
*/

sw.Promed.swUnitOfTradingEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 640,
	callback: Ext.emptyFn,
	owner: null,
	shim: false,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swUnitOfTradingEditWindow',
	
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},
	onHide: function() {
		if(this.Form.getForm().findField('WhsDocumentUc_id').getValue() > 0) {
			var bf = this.Form.getForm();
			Ext.Ajax.request({
				url: '/?c=UnitOfTrading&m=loadDrugListOnUnitOfTrading',
				success: function(response){
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj && response_obj['totalCount'] == 0 ) {
						Ext.Ajax.request({
							url: '/?c=UnitOfTrading&m=deleteUnitOfTrading',
							success: function(response){
								this.hide();
							}.createDelegate(this),
							params: {
								WhsDocumentProcurementRequest_id: bf.findField('WhsDocumentUc_id').getValue()
							}
						});
					} else {
						this.hide();
					}
				}.createDelegate(this),
				params: {
					WhsDocumentUc_id: bf.findField('WhsDocumentUc_id').getValue()
				}
			});
		} else {
			this.hide();
		}
	},
	
	show: function() {
		var wnd = this;

		sw.Promed.swUnitOfTradingEditWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] || !arguments[0].action ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}

		if( arguments[0].WhsDocumentStatusType_id && arguments[0].WhsDocumentStatusType_id == 2 && arguments[0].action == 'edit') {
			sw.swMsg.alert(lang['oshibka'], 'Лот подписан и не может быть изменен');
			this.hide();
			return false;
		}
		
		if( !arguments[0].DrugRequest_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_svodnaya_zayavka']);
			this.hide();
			return false;
		} else {
			this.DrugRequest_id = arguments[0].DrugRequest_id;
		}

		this.action = arguments[0].action;

		if( arguments[0].copy ) {
			this.copy = arguments[0].copy;
		}

		if( arguments[0].onCopy ) {
			this.onCopy = arguments[0].onCopy;
		}

		if( arguments[0].copyWhsDocumentUc_id ) {
			this.copyWhsDocumentUc_id = arguments[0].copyWhsDocumentUc_id;
		}

		this.org_combo.fullReset();
		if (!Ext.isEmpty(getGlobalOptions().org_id)) {
            this.org_combo.getStore().baseParams.UserOrg_id = getGlobalOptions().org_id;
		}

		wnd.org_combo.onSetValue = function() {
			if(wnd.action == 'add'){
				Ext.Ajax.request({
					url: '/?c=UnitOfTrading&m=loadDrugList',
					success: function(response){
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if ( response_obj && response_obj['totalCount'] == 0 ) {
							sw.swMsg.alert(lang['soobschenie'], lang['net_medikamentov_dlya_dobavleniya_vse_medikamentyi_iz_zayavki_dobavlenyi_v_lotyi']);
							this.hide();
						} else {
							var bf = this.Form.getForm();
							if(this.copy){
								bf.setValues(arguments[0]);
							}
							while( !bf.isValid() ) {
								this.Form.getFirstInvalidEl().setValue(1);
							}
							var params = new Object();

							if (bf.findField('WhsDocumentUc_Name').disabled) {
								params.WhsDocumentUc_Name = bf.findField('WhsDocumentUc_Name').getValue();
							}
							if (bf.findField('Org_aid').disabled) {
								params.Org_aid = wnd.org_combo.getValue();
							}
							if (bf.findField('DrugFinance_id').disabled) {
								params.DrugFinance_id = bf.findField('DrugFinance_id').getValue();
							}

							bf.submit({
								scope: wnd,
								params: params,
								failure: function() {

								},
								success: function(form, act) {
									var response_obj = Ext.util.JSON.decode(act.response.responseText);
									if ( response_obj && !Ext.isEmpty(response_obj.WhsDocumentUc_id) ) {
										this.findById('WhsDocumentUc_id').setValue(response_obj.WhsDocumentUc_id);
										if(this.copy)
											this.onCopy(this.copyWhsDocumentUc_id);
										var win = this;
										var newparams = {
											DrugRequest_id: win.DrugRequest_id,
											WhsDocumentUc_id: response_obj.WhsDocumentUc_id,
											onCancel: function(){
												win.onHide();
											},
											callback: function(){
												var grid_p = Ext.getCmp('swUnitOfTradingViewWindow').GridPanel2;
												grid_p.setParam('WhsDocumentUc_id', response_obj.WhsDocumentUc_id, true);
												grid_p.loadData();
											}
										};
										getWnd('swUnitOfTradingRowWindow').show(newparams);
									}
								}.createDelegate(wnd)
							});
						}
					}.createDelegate(wnd),
					params: {
						start: 0,
						DrugRequest_id: wnd.DrugRequest_id
					}
				});
			}
			wnd.org_combo.onSetValue = Ext.emptyFn;
		};

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

		this.setTitle('Лот: ' + this.getActionName(this.action));
		
		var bf = this.Form.getForm();
		if( this.action !== 'add' ) {
			bf.setValues(arguments[0].owner.getGrid().getSelectionModel().getSelected().data);
		} else {
			bf.setValues(arguments[0]);
		}
		
		this.disableFields( this.action == 'view' );
		this.buttons[0].setDisabled( this.action == 'view' );

		if (this.action == 'edit' && this.isSigned == 'true') {
			bf.findField('WhsDocumentUc_Num').setDisabled(false);
			bf.findField('WhsDocumentUc_Name').setDisabled(true);
		}

		var selected_org_aid = arguments[0].owner.getGrid().getSelectionModel().getSelected().data.Org_aid;
		if (this.action.inlist(['edit', 'view']) && !Ext.isEmpty(selected_org_aid)) { //в режиме редактирования если есть сохраненное значение, грузим его
            wnd.org_combo.setDisabled(true);
            wnd.org_combo.setValueById(selected_org_aid);
		} else { //иначе ставим значение по умолчанию
            if(getGlobalOptions().orgtype == 'lpu' && !Ext.isEmpty(getGlobalOptions().org_id)) {
                wnd.org_combo.setDisabled(true);
                wnd.org_combo.setValueById(getGlobalOptions().org_id);
            } else if (!Ext.isEmpty(getGlobalOptions().minzdrav_org_id)) {
                wnd.org_combo.setDisabled(false);
                wnd.org_combo.setValueById(getGlobalOptions().minzdrav_org_id);
            }
		}

		if(this.action == 'add'){
			if(!this.copy){
				var today = new Date();
				var mm = today.getMonth()+1; //January is 0!
				var yyyy = today.getFullYear();
				if(mm>6)
					yyyy += 1;
				var setDate = new Date(yyyy,11,31);
				bf.findField('WhsDocumentProcurementRequest_setDate').setValue(setDate);

				if(arguments[0].DrugFinance_id)
					bf.findField('DrugFinance_id').setValue(arguments[0].DrugFinance_id);
				if(arguments[0].WhsDocumentCostItemType_id)
					bf.findField('WhsDocumentCostItemType_id').setValue(arguments[0].WhsDocumentCostItemType_id);
				Ext.Ajax.request({
					url: '/?c=UnitOfTrading&m=getBudgetFormType',
					success: function(response){
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if ( response_obj && !Ext.isEmpty(response_obj[0]) && !Ext.isEmpty(response_obj[0].BudgetFormType_id) ) {
							bf.findField('BudgetFormType_id').setValue(response_obj[0].BudgetFormType_id);
						}
					}.createDelegate(this),
					params: {
						WhsDocumentCostItemType_id: bf.findField('WhsDocumentCostItemType_id').getValue(),
						DrugFinance_id: bf.findField('DrugFinance_id').getValue()
					}
				});
				bf.findField('WhsDocumentPurchType_id').setValue(3);
			}
		}

		

		bf.findField('WhsDocumentUc_Num').focus(true, 100);
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

		var params = new Object();
		if (bf.findField('WhsDocumentUc_Name').disabled) {
			params.WhsDocumentUc_Name = bf.findField('WhsDocumentUc_Name').getValue();
		}
		if (this.org_combo.disabled) {
			params.Org_aid = this.org_combo.getValue();
		}
		if (bf.findField('DrugFinance_id').disabled) {
			params.DrugFinance_id = bf.findField('DrugFinance_id').getValue();
		}

		bf.submit({
			scope: this,
			params: params,
			failure: function() {
			
			},
			success: function(form, act) {
				this.callback.call(this.owner, this.owner, 0);
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
	
	initComponent: function() {
		var wnd = this;

        this.org_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Организация'),
            hiddenName: 'Org_aid',
            displayField: 'Org_Name',
            valueField: 'Org_id',
            editable: true,
            allowBlank: false,
            anchor: '100%',
            listWidth: 400,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{Org_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'Org_id'
                }, [
                    {name: 'Org_id', mapping: 'Org_id'},
                    {name: 'Org_Name', mapping: 'Org_Name'}
                ]),
                url: '/?c=UnitOfTrading&m=loadOrgCombo'
            })
        });

		this.Form = new Ext.FormPanel({
			url: '/?c=UnitOfTrading&m=saveUnitOfTrading',
			frame: true,
			defaults: {
				labelAlign: 'right'
			},
			layout: 'form',
			labelWidth: 170,
			items: [{
				layout: 'form',
				items: [{
					layout: 'form',
					items: [
						wnd.org_combo
					]
				}, {
					layout: 'form',
					items: [{
						xtype: 'hidden',
						id: 'WhsDocumentUc_id',
						name: 'WhsDocumentUc_id'
					}, {
						xtype: 'hidden',
						name: 'WhsDocumentStatusType_id'
					}, {
						xtype: 'hidden',
						name: 'WhsDocumentUcStatusType_id'
					}, {
						xtype: 'textfield',
						anchor: '100%',
						allowBlank: false,
						name: 'WhsDocumentUc_Num',
						fieldLabel: lang['№_lota'],
						maxLength: 50
					}]
				}, {
					allowBlank: false,
					fieldLabel: 'Срок действия контракта',
					name: 'WhsDocumentProcurementRequest_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
					width: 100,
					xtype: 'swdatefield'
				}, {
					layout: 'form',
					items: [{
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						allowBlank: false,
						comboSubject: 'PurchObjType',
						fieldLabel: 'Объект закупки'
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						anchor: '100%',
						allowBlank: false,
						name: 'WhsDocumentUc_Name',
						fieldLabel: lang['naimenovanie'],
						maxLength: 100
					}]
				}]
			}, {
				layout: 'form',
				items: [{
					layout: 'form',
					items:[{
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						allowBlank: false,
						comboSubject: 'DrugFinance',
						fieldLabel: lang['istochnik_finansirovaniya']
					}, {
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						allowBlank: false,
						comboSubject: 'WhsDocumentCostItemType',
						fieldLabel: lang['statya_rashoda']
					}, {
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						allowBlank: false,
						comboSubject: 'BudgetFormType',
						fieldLabel: 'Целевая статья'
					}, {
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						allowBlank: false,
						comboSubject: 'WhsDocumentPurchType',
						fieldLabel: 'Вид закупа'
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
		sw.Promed.swUnitOfTradingEditWindow.superclass.initComponent.apply(this, arguments);
	}
});