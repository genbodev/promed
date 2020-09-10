/**
* swMinzdravDLODocumentsDrugEditWindow - окно редактирования "Медикамент"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Alexandr Chebukin
* @version      23.12.2012
* @comment      
*/
sw.Promed.swMinzdravDLODocumentsDrugEditWindow = Ext.extend(sw.Promed.BaseForm, { //getWnd('swMinzdravDLODocumentsDrugEditWindow').show();
	autoHeight: false,
	title: lang['medikament'],
	layout: 'border',
	id: 'MinzdravDLODocumentsDrugEditWindow',
	modal: true,
	//shim: false,
	height: 260,
	width: 700,
	resizable: false,
	firstTabIndex: 10000,
	plain: true,
	debug_mode: false, //только для разработки
	demand: null,
	delivery_graph: null,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	getValues:  function() {
		var res = new Object();
		var document_combo = this.form.findField('WhsDocumentSupply_id');
		var drug_combo = this.form.findField('Drug_id');
		Ext.apply(res, this.form.getValues());

		drug_combo.getStore().each(function(record) {
            if (record.get('Drug_id') == drug_combo.getValue()) {
                res.Tradenames_Name = record.get('Tradenames_Name');
                res.DrugForm_Name = record.get('DrugForm_Name');
                res.Drug_Dose = record.get('Drug_Dose');
                res.Drug_Fas = record.get('Drug_Fas');
                res.Reg_Num = record.get('Reg_Num');
                res.Reg_Firm = record.get('Reg_Firm');
                res.Reg_Country = record.get('Reg_Country');
                res.Reg_Period = record.get('Reg_Period');
                res.Reg_ReRegDate = record.get('Reg_ReRegDate');
            }
        });

		document_combo.getStore().each(function(record) {
            if (record.get('WhsDocumentUc_id') == document_combo.getValue()) {
                res.WhsDocumentUc_pid = record.get('WhsDocumentUc_id');
                res.WhsDocumentUc_Name = record.get('WhsDocumentSupply_Name');
                res.WhsDocumentUc_Num = record.get('WhsDocumentUc_Num');
                res.WhsDocumentSupply_Year = record.get('WhsDocumentSupply_Year');
            }
        });

		res.WhsDocumentUc_Sum = this.form.findField('WhsDocumentUc_Sum').getValue();
		res.WhsDocumentOrderReserveDrug_PriceNDS = this.form.findField('WhsDocumentOrderReserveDrug_PriceNDS').getValue();
		res.DrugOstatRegistry_Kolvo = this.form.findField('DrugOstatRegistry_Kolvo').getValue();

		return res;
	},
	doSave:  function() {
		var wnd = this;

		if ( !this.form.isValid() ) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('MinzdravDLODocumentsDrugEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        var data = this.getValues();

        if (this.DuplicateRecordChecking && !Ext.isEmpty(this.owner)) {
            var dbl_idx = this.owner.findDuplicate(data);
            if (dbl_idx >= 0) {
                sw.swMsg.show({
                    icon: Ext.MessageBox.QUESTION,
                    msg: 'Указанный медикамент в списке уже присутствует. Заменить данные?',
                    title: lang['vnimanie'],
                    buttons: Ext.Msg.YESNO,
                    fn: function(buttonId, text, obj){
                        if ('yes' == buttonId){
                            wnd.owner.deleteRecord(dbl_idx);
                            wnd.callback(data);
                            wnd.hide();
                        }
                    }
                });
            } else {
                this.callback(data);
                this.hide();
            }
        } else {
            this.callback(data);
            this.hide();
        }

        return true;
	},
	setSum: function() {
		var form = this.form;
		var percent = this.form.findField('WhsDocumentOrderReserve_Percent').getValue();
		if (this.WhsDocumentType_id == 12) {
			var kolvo = Math.floor(percent * this.form.findField('DrugOstatRegistry_Kolvo').getValue() * 0.01);
		} else {
			var kolvo = this.form.findField('DrugOstatRegistry_Kolvo').getValue();
		}
		var sum = kolvo * this.form.findField('WhsDocumentOrderReserveDrug_PriceNDS').getValue();
		this.form.findField('WhsDocumentOrderReserveDrug_Kolvo').setValue(kolvo);
		this.form.findField('WhsDocumentUc_Sum').setValue(sum.toFixed(2));
		
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = this.form;
		var field_arr = [
			'WhsDocumentSupply_id',
			'Drug_id',
			'WhsDocumentOrderReserve_Percent',
			'WhsDocumentOrderReserveDrug_Kolvo'
		];
		
		for (var i in field_arr) if (form.findField(field_arr[i])) {
			if (disable || form.findField(field_arr[i]).enable_blocked) {
                form.findField(field_arr[i]).disable();
            } else {
                form.findField(field_arr[i]).enable();
            }
		}
		
		if (disable) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}
	},
	show: function() {
        var that = this;		
		
		sw.Promed.swMinzdravDLODocumentsDrugEditWindow.superclass.show.apply(this, arguments);
		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.owner = null;
        this.DuplicateRecordChecking = false;
		this.WhsDocumentOrderReserveDrug_id = null;
		this.WhsDocumentType_id = null;
		this.Org_id = null;
		this.DrugRequest_id = null;
		this.WhsDocumentSupply_id = null;
		this.DrugFinance_id = null;
		this.WhsDocumentCostItemType_id = null;
		this.WhsDocumentUc_pid = null;
		this.WhsDocumentOrderReserveDrug_Kolvo = null;

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
        if ( arguments[0].DuplicateRecordChecking ) {
            this.DuplicateRecordChecking = arguments[0].DuplicateRecordChecking;
        }
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].WhsDocumentOrderReserveDrug_id ) {
			this.WhsDocumentOrderReserveDrug_id = arguments[0].WhsDocumentOrderReserveDrug_id;
		}
		if ( arguments[0].params.WhsDocumentType_id ) {
			this.WhsDocumentType_id = arguments[0].params.WhsDocumentType_id;
		}
		if ( arguments[0].params.Org_id ) {
			this.Org_id = arguments[0].params.Org_id;
		}
		if ( arguments[0].params.DrugRequest_id ) {
			this.DrugRequest_id = arguments[0].params.DrugRequest_id;
		}
		if ( arguments[0].params.WhsDocumentSupply_id ) {
			this.WhsDocumentSupply_id = arguments[0].params.WhsDocumentSupply_id;
		}
		if ( arguments[0].params.DrugFinance_id ) {
			this.DrugFinance_id = arguments[0].params.DrugFinance_id;
		}
		if ( arguments[0].params.WhsDocumentCostItemType_id ) {
			this.WhsDocumentCostItemType_id = arguments[0].params.WhsDocumentCostItemType_id;
		}
		if ( arguments[0].params.WhsDocumentUc_pid ) {
			this.WhsDocumentUc_pid = arguments[0].params.WhsDocumentUc_pid;
		}
		if ( arguments[0].params.WhsDocumentOrderReserveDrug_Kolvo ) {
			this.WhsDocumentOrderReserveDrug_Kolvo = arguments[0].params.WhsDocumentOrderReserveDrug_Kolvo;
		}
		
		this.form.reset();
		this.supply_combo.getStore().baseParams = new Object();;

		this.setTitle(lang['medikament']);

        if (arguments[0].params) {
            this.form.setValues(arguments[0].params);
        }

        this.form.findField('WhsDocumentOrderReserve_Percent').setValue(arguments[0].params.WhsDocumentOrderReserve_Percent);

        var drug_combo = that.form.findField('Drug_id');
        if (this.WhsDocumentSupply_id > 0 && Ext.isEmpty(this.WhsDocumentUc_pid)) {
            this.supply_combo.setValueById(this.WhsDocumentSupply_id);
            this.supply_combo.enable_blocked = true;

            drug_combo.store.load({
                params: {
                    WhsDocumentSupply_id: that.WhsDocumentSupply_id,
                    WhsDocumentType_id: that.WhsDocumentType_id
                },
                callback: function(){
                    drug_combo.setValue(drug_combo.getValue());
                    drug_combo.fireEvent('change', drug_combo, drug_combo.getValue());
                    that.form.findField('WhsDocumentOrderReserveDrug_Kolvo').setValue(that.WhsDocumentOrderReserveDrug_Kolvo);
                    that.form.findField('WhsDocumentOrderReserveDrug_Kolvo').fireEvent('change', that.form.findField('WhsDocumentOrderReserveDrug_Kolvo'), that.form.findField('WhsDocumentOrderReserveDrug_Kolvo').getValue());
                }
            });
        } else {
            this.supply_combo.enable_blocked = (this.WhsDocumentSupply_id > 0);

            if (this.action == 'view') {
                if (this.WhsDocumentUc_pid > 0) {
                    this.supply_combo.setValueByFieldId('WhsDocumentUc_id', this.WhsDocumentUc_pid);
                }
                if (arguments[0].params && !Ext.isEmpty(arguments[0].params.Drug_Name)) {
                    drug_combo.setRawValue(arguments[0].params.Drug_Name);
                }
            } else {
                this.supply_combo.getStore().baseParams.DrugRequest_id = this.DrugRequest_id;
                this.supply_combo.getStore().baseParams.DrugFinance_id = this.DrugFinance_id;
                this.supply_combo.getStore().baseParams.WhsDocumentCostItemType_id = this.WhsDocumentCostItemType_id;
                this.supply_combo.getStore().baseParams.Org_cid = this.Org_id; //Org_cid - заказчик
                this.supply_combo.getStore().baseParams.SubAccountType_SysNick = this.WhsDocumentType_id == 12 ? 'available' : 'reserve'; //12 - Распоряжение на включение в резерв
                this.supply_combo.getStore().baseParams.DrugOstatRegistry_Org_id = this.Org_id; //Org_cid - заказчик

                this.supply_combo.getStore().load({
                    callback: function(){
                        if (that.WhsDocumentUc_pid > 0) {
                            that.supply_combo.getStore().findBy(function(record) {
                                if (record.get('WhsDocumentUc_id') == that.WhsDocumentUc_pid) {
                                    that.supply_combo.setValue(record.get('WhsDocumentSupply_id'));
                                    drug_combo.store.load({
                                        params: {
                                            WhsDocumentSupply_id: record.get('WhsDocumentSupply_id'),
                                            WhsDocumentType_id: that.WhsDocumentType_id
                                        },
                                        callback: function(){
                                            drug_combo.setValue(drug_combo.getValue());
                                            drug_combo.fireEvent('change', drug_combo, drug_combo.getValue());
                                            that.form.findField('WhsDocumentOrderReserveDrug_Kolvo').setValue(that.WhsDocumentOrderReserveDrug_Kolvo);
                                            that.form.findField('WhsDocumentOrderReserveDrug_Kolvo').fireEvent('change', that.form.findField('WhsDocumentOrderReserveDrug_Kolvo'), that.form.findField('WhsDocumentOrderReserveDrug_Kolvo').getValue());
                                        }
                                    });
                                }
                            });
                        }
                    }
                });
            }
        }

		switch (this.action) {
			case 'add':
				this.setTitle(this.title + lang['_dobavlenie']);
				this.setDisabled(false);
			break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + ': ' + (this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				var nds = 10;
				var price = this.form.findField('WhsDocumentOrderReserveDrug_PriceNDS').getValue() / ( 1 + nds / 100 );
				that.form.findField('WhsDocumentOrderReserveDrug_Price').setValue(price.toFixed(2));				
				this.setDisabled(this.action == 'view');
			break;	
		}
	},
	initComponent: function() {
		var current_window = this;

        this.supply_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['kontrakt'],
            hiddenName: 'WhsDocumentSupply_id',
            displayField: 'WhsDocumentSupply_Name',
            valueField: 'WhsDocumentSupply_id',
            allowBlank: true,
            editable: true,
            width: 450,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
                '<td style="padding: 2px; width: 20%;">'+lang['№']+'</td>',
                '<td style="padding: 2px; width: 40%;">'+lang['naimenovanie']+'</td>',
                '<td style="padding: 2px; width: 40%;">'+lang['postavschik']+'</td>',
                '<tpl for="."><tr class="x-combo-list-item" style="font-family: tahoma; font-size: 8pt;">',
                '<td style="padding: 2px;">{WhsDocumentUc_Num}&nbsp;</td>',
                '<td style="padding: 2px;">{WhsDocumentSupply_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{Supplier_Name}&nbsp;</td>',
                '</tr></tpl>',
                '</table>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'WhsDocumentUc_id', mapping: 'WhsDocumentUc_id' },
                    { name: 'WhsDocumentSupply_id', mapping: 'WhsDocumentSupply_id' },
                    { name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id' },
                    { name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
                    { name: 'WhsDocumentSupply_Name', mapping: 'WhsDocumentSupply_Name' },
                    { name: 'Supplier_Name', mapping: 'Supplier_Name' },
                    { name: 'WhsDocumentSupply_Year', mapping: 'WhsDocumentSupply_Year' },
                    { name: 'WhsDocumentUc_Num', mapping: 'WhsDocumentUc_Num' }
                ],
                key: 'WhsDocumentSupply_id',
                sortInfo: { field: 'WhsDocumentSupply_Name' },
                url:'/?c=WhsDocumentOrderReserve&m=loadWhsDocumentSupplyCombo'
            }),
            listeners: {
                'change': function(combo, newValue) {
                    if (newValue > 0) {
                        current_window.form.findField('Drug_id').getStore().load({
                            params: {
                                WhsDocumentSupply_id: newValue,
                                WhsDocumentType_id: current_window.WhsDocumentType_id
                            }
                        });
                    }
                }
            },
            onTrigger2Click: function() {
                var combo = this;

                if (combo.disabled) {
                    return false;
                }

                combo.clearValue();
                combo.lastQuery = '';
                combo.getStore().removeAll();
                combo.getStore().baseParams.query = '';
                current_window.form.findField('Drug_id').getStore().removeAll();
            },
            setValueById: function(id) {
                var combo = this;
                combo.store.baseParams.WhsDocumentSupply_id = id;
                combo.store.load({
                    callback: function(){
                        combo.setValue(id);
                        combo.store.baseParams.WhsDocumentSupply_id = null;
                    }
                });
            },
            setValueByFieldId: function(field, id) {
                var combo = this;
                combo.store.baseParams[field] = id;
                combo.store.load({
                    callback: function(){
                        combo.setValue(id);
                        combo.store.baseParams[field] = null;
                    }
                });
            },
            loadData: function() {
                var combo = this;
                combo.store.load({
                    callback: function(){
                        combo.setValue(null);
                    }
                });
            }
        });
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'background:#DFE8F6; padding: 0;',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'MinzdravDLODocumentsDrugEditForm',
				style: '',
				bodyStyle:'background:#DFE8F6; padding: 5px;',
				border: false,
				labelWidth: 180,
				labelAlign: 'right',
				collapsible: true,
				region: 'north',
				url:'/?c=WhsDocumentSupply&m=save',
				layout: 'form',
				items: [{
					name: 'WhsDocumentOrderReserveDrug_id',
					value: null,
					xtype: 'hidden'
				},
                this.supply_combo,
                new sw.Promed.SwBaseLocalCombo({
					allowBlank: false,
					displayField: 'Drug_Name',
					editable: false,
					fieldLabel: lang['naimenovanie'],
					hiddenName: 'Drug_id',
					store: new Ext.data.JsonStore({
						url: '/?c=WhsDocumentOrderReserveDrug&m=loadWhsDocumentOrderReserveDrugList',
						key: 'Drug_id',
						autoLoad: false,
						fields: [
							{name: 'Drug_id', type:'int'},
							{name: 'Okei_id', type:'int'},
							{name: 'Drug_Code', type:'string'},
							{name: 'Drug_Name',  type:'string'},
							{name: 'DrugOstatRegistry_Kolvo',  type:'string'},
							{name: 'DrugComplexMnn_RusName',  type:'string'},
                            {name: 'Tradenames_Name',  type:'string'},
                            {name: 'DrugForm_Name',  type:'string'},
                            {name: 'Drug_Dose',  type:'string'},
                            {name: 'Drug_Fas',  type:'string'},
                            {name: 'Reg_Num',  type:'string'},
							{name: 'WhsDocumentSupplySpec_Price',  type:'string'},
							{name: 'WhsDocumentSupplySpec_PriceNDS',  type:'string'},
                            {name: 'Reg_Firm',  type:'string'},
                            {name: 'Reg_Country',  type:'string'},
                            {name: 'Reg_Period',  type:'string'},
                            {name: 'Reg_ReRegDate',  type:'string'}
						],
						sortInfo: {
							field: 'Drug_Code'
						}
					}),
					listeners:
					{
						change:  function(combo, newValue, oldValue)
						{
							if (newValue > 0) {
								var idx = combo.getStore().findBy(function(rec) {
									if (rec.get('Drug_id') == newValue) {
										return true;
									} else {
										return false;
									}
								}.createDelegate(this));
								var record = combo.getStore().getAt(idx);
								if (record) {
									this.form.findField('Okei_id').setValue(record.get('Okei_id'));
									this.form.findField('DrugOstatRegistry_Kolvo').setValue(record.get('DrugOstatRegistry_Kolvo'));
									this.form.findField('WhsDocumentOrderReserveDrug_Price').setValue(record.get('WhsDocumentSupplySpec_Price'));
									this.form.findField('WhsDocumentOrderReserveDrug_PriceNDS').setValue(record.get('WhsDocumentSupplySpec_PriceNDS'));
									this.setSum();
								}								
							}
						}.createDelegate(this)
					},
					tpl: '<tpl for="."><div class="x-combo-list-item">{Drug_Name}&nbsp;</div></tpl>',
					valueField: 'Drug_id',
					width: 450
				}), {
					name: 'Okei_id',
					value: null,
					xtype: 'hidden'
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 260,
						items: [{
							xtype: 'textfield',
							fieldLabel : lang['dostupno'],
							width: 70,
							tabIndex: current_window.firstTabIndex + 10,
							name: 'DrugOstatRegistry_Kolvo',
							disabled: true,
							value: '',
							allowBlank:false
						}]						
					}, {
						layout: 'form',
						border: false,
						labelWidth: 80,
						width: 200,
						items: [{
							xtype: 'numberfield',
							fieldLabel : lang['rezerv_%'],
							width: 60,
							tabIndex: current_window.firstTabIndex + 10,
							name: 'WhsDocumentOrderReserve_Percent',
							maxValue: 100,
							minValue: 0.01,
							value: '',
							listeners:
							{
								change:  function(field, newValue, oldValue)
								{
									if (newValue > 0) {
										this.setSum();
									}
								}.createDelegate(this)
							},
							allowBlank:false
						}]						
					}]
				}, {
					xtype: 'textfield',
					fieldLabel : lang['kol-vo'],
					tabIndex: current_window.firstTabIndex + 10,
					width: 70,
					name: 'WhsDocumentOrderReserveDrug_Kolvo',			
					allowBlank:false,
					listeners: {
						change:  function(field, newValue, oldValue) {
							if (newValue > 0) {
								var sum = newValue * this.form.findField('WhsDocumentOrderReserveDrug_PriceNDS').getValue();
								this.form.findField('WhsDocumentUc_Sum').setValue(sum.toFixed(2));
							}
						}.createDelegate(this)
					}
				}, {
					xtype: 'textfield',
					fieldLabel : lang['tsena_bez_nds_rub'],
					tabIndex: current_window.firstTabIndex + 10,
					width: 70,
					name: 'WhsDocumentOrderReserveDrug_Price',				
					allowBlank:false,
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel : lang['tsena_s_nds_rub'],
					tabIndex: current_window.firstTabIndex + 10,
					width: 70,
					name: 'WhsDocumentOrderReserveDrug_PriceNDS',
					allowBlank:false,
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel : lang['summa_optovaya_s_nds_rub'],
					tabIndex: current_window.firstTabIndex + 10,
					width: 70,
					name: 'WhsDocumentUc_Sum',
					allowBlank:false,
					disabled: true
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{				
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swMinzdravDLODocumentsDrugEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('MinzdravDLODocumentsDrugEditForm').getForm();
	}	
});