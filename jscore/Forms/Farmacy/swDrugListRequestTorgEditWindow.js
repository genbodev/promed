/**
* swDrugListRequestTorgEditWindow - окно редактирования конкретной строки медикамента по заявке
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      10.2012
* @comment      
*/
sw.Promed.swDrugListRequestTorgEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['stroka_medikamenta_dlya_zayavki_redaktirovanie'],
	layout: 'border',
	id: 'DrugListRequestTorgEditWindow',
	modal: true,
	shim: false,
	width: 510,
	height: 220, //270,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	onSave: Ext.emptyFn,
	getData: function() {
		var data = new Object();
		var wnd = this;
		var tn_combo = this.form.findField('TRADENAMES_id');

		data = wnd.form.getValues();

		data.TRADENAMES_Name = null;
		data.DrugListRequestTorg_IsProblem = this.form.findField('DrugListRequestTorg_IsProblem').checked ? 1 : 0;

		var idx = tn_combo.getStore().findBy(function(rec) { return rec.get('TRADENAMES_ID') == data.TRADENAMES_id; });
		if (idx >= 0) {
			data.TRADENAMES_Name = tn_combo.getStore().getAt(idx).get('NAME');
		}

		return data;
	},
	setData: function(data) {
		var wnd = this;
		wnd.form.setValues(data);
	},	
	setDisabled: function(disable) {
		var wnd = this;
		var form = wnd.form;		
		
		if (disable) {			
			form.findField('TRADENAMES_id').disable();
			form.findField('OrgFarmacyPrice_Min').disable();
			form.findField('OrgFarmacyPrice_Max').disable();
			form.findField('DrugRequestPrice_Min').disable();
			form.findField('DrugRequestPrice_Max').disable();
			form.findField('DrugRequest_Price').disable();
			wnd.buttons[0].disable();
		} else {			
			form.findField('TRADENAMES_id').enable();
			form.findField('OrgFarmacyPrice_Min').enable();
			form.findField('OrgFarmacyPrice_Max').enable();
			form.findField('DrugRequestPrice_Min').enable();
			form.findField('DrugRequestPrice_Max').enable();
			form.findField('DrugRequest_Price').enable();		
			wnd.buttons[0].enable();
		}
	},
	setJnvlpPrices: function() {
		var wnd = this;

		wnd.form.findField('Jnvlp_Price').setValue(null);
		wnd.form.findField('Jnvlp_WholesalePrice').setValue(null);

		var mnn_id = wnd.DrugComplexMnn_id ? wnd.DrugComplexMnn_id : "";
		var torg_id = wnd.tradenames_combo.getValue();

		if (torg_id > 0) {
			if (wnd.JnvlpPricesData && !Ext.isEmpty(wnd.JnvlpPricesData[mnn_id+'_'+torg_id])) {
				wnd.setJnvlpPricesFields(wnd.JnvlpPricesData[mnn_id+'_'+torg_id]);
			} else {
				Ext.Ajax.request({
					url: '/?c=DrugRequestProperty&m=getJnvlpPrices',
					params: {
						DrugComplexMnn_id: mnn_id,
						TRADENAMES_ID: torg_id
					},
					success: function(response){
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0] && result[0].MinPrice && result[0].MinPrice > 0) {
							if (!wnd.JnvlpPricesData) {
								wnd.JnvlpPricesData = new Object();
							}
							wnd.JnvlpPricesData[mnn_id+'_'+torg_id] = {
								MinPrice: result[0].MinPrice*1,
								MaxPrice: result[0].MaxPrice*1,
								Wholesale_MinPrice: result[0].Wholesale_MinPrice*1,
								Wholesale_MaxPrice: result[0].Wholesale_MaxPrice*1
							};
							wnd.setJnvlpPricesFields(wnd.JnvlpPricesData[mnn_id+'_'+torg_id]);
						}
					}
				});
			}
		}
	},
	setJnvlpPricesFields: function(data) {
		var price_str = data.MinPrice;
		var wprice_str = data.Wholesale_MinPrice;

		if (data.MaxPrice > 0 && data.MaxPrice > data.MinPrice) {
			price_str += " - " + data.MaxPrice;
		}
		if (data.Wholesale_MaxPrice > 0 && data.Wholesale_MaxPrice > data.Wholesale_MinPrice) {
			wprice_str += " - " + data.Wholesale_MaxPrice;
		}

		this.form.findField('Jnvlp_Price').setValue(price_str);
		this.form.findField('Jnvlp_WholesalePrice').setValue(wprice_str);
	},
	calcFieldValues: function() {
		var wnd = this;

		wnd.form.findField('DrugRequestPrice_Min').setValue(null);
		wnd.form.findField('DrugRequestPrice_Max').setValue(null);
		wnd.form.findField('DrugRequest_Price').setValue(null);

		var torg_id = wnd.tradenames_combo.getValue();
		if (torg_id > 0) {
			Ext.Ajax.request({
				url: '/?c=DrugRequestProperty&m=getLastYearSupplyPrices',
				params: {
					DrugRequestProperty_id: wnd.DrugRequestProperty_id,
					TRADENAMES_ID: torg_id
				},
				success: function(response){
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && result[0].avg_price && result[0].avg_price > 0) {
						wnd.form.findField('DrugRequestPrice_Min').setValue(result[0].min_price);
						wnd.form.findField('DrugRequestPrice_Max').setValue(result[0].max_price);
						wnd.form.findField('DrugRequest_Price').setValue(result[0].avg_price);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['net_dannyih_dlya_rasscheta_tsen']);
					}
				}
			});
		}
	},
	doSave:  function() {
		var wnd = this;
		if (!this.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('dlrteDrugListRequestTorgEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var data = wnd.getData();
		wnd.onSave(data);
		return true;		
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swDrugListRequestTorgEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.onSave = Ext.emptyFn;
		this.DrugRequestProperty_id = null;
		this.DrugComplexMnn_id = null;
		this.DrugListRequestTorg_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		this.action = (arguments[0].action) ? arguments[0].action : 'add';
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugRequestProperty_id ) {
			this.DrugRequestProperty_id = arguments[0].DrugRequestProperty_id;
		}
		if ( arguments[0].DrugListRequestTorg_id ) {
			this.DrugListRequestTorg_id = arguments[0].DrugListRequestTorg_id;
		}
		if ( arguments[0].DrugComplexMnn_id ) {
			this.DrugComplexMnn_id = arguments[0].DrugComplexMnn_id;
		}
		
		this.form.reset();
		this.setTitle(lang['stroka_medikamenta_dlya_zayavki']);
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();

		var torg_id = arguments[0].TRADENAMES_id;
		wnd.tradenames_combo.getStore().load({
			params: {DrugComplexMnn_id: wnd.DrugComplexMnn_id},
			callback: function() {
				if (torg_id > 0) {
					wnd.tradenames_combo.setValue(torg_id);
					wnd.setJnvlpPrices();
				}
			}
		});

		switch (this.action) {
			case 'add':
				this.setTitle(this.title + lang['_dobavlenie']);
				loadMask.hide();
			break;
			case 'view':
			case 'edit':
				this.setTitle(this.title + (this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				wnd.setData(arguments[0]);
				loadMask.hide();				
			break;	
		}
		wnd.setDisabled(wnd.action == 'view');
	},
	initComponent: function() {
		var wnd = this;

		this.tradenames_combo = new Ext.form.ComboBox({
			mode: 'local',
			store: new Ext.data.JsonStore({
				url: '/?c=DrugRequestProperty&m=loadTradenames',
				key: 'TRADENAMES_ID',
				autoLoad: false,
				fields: [
					{name: 'TRADENAMES_ID',    type:'int'},
					{name: 'NAME',  type:'string'}
				],
				sortInfo: {
					field: 'NAME'
				}
			}),
			displayField:'NAME',
			valueField: 'TRADENAMES_ID',
			hiddenName: 'TRADENAMES_id',
			fieldLabel: lang['torgovoe_naimenovanie'],
			width: 300,
			tpl: '<tpl for="."><div class="x-combo-list-item">'+
				'{NAME}'+
			'</div></tpl>',
			listeners: {
				'select': function(combo, record, index) {
					var rls_torg_id = record.get('TRADENAMES_ID');
					var rls_torg_name = record.get('NAME');
					var regexp = null;
					regexp = new RegExp('<[^>]+>','g');
					rls_torg_name = rls_torg_name.replace(regexp, '');
					regexp = new RegExp('&[^;]+;','g');
					rls_torg_name = rls_torg_name.replace(regexp, function(chr) {
						return htmlentities.decode(chr);
					});

					this.setRawValue(rls_torg_name);
					this.setValue(rls_torg_id);

					//wnd.calcFieldValues();
					wnd.setJnvlpPrices();
				}
			}
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'dlrteDrugListRequestTorgEditForm',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: false,
				labelWidth: 150,
				collapsible: true,
				items: [/*{
					xtype: 'swrlstradenamescombo',
					fieldLabel: lang['torgovoe_naimenovanie'],
					hiddenName: 'TRADENAMES_ID',
					width: 300,
					anchor: ''
				}, */
				wnd.tradenames_combo, {
					xtype: 'textfield',
					fieldLabel: lang['tsena_proizv'],
					name: 'Jnvlp_Price',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: lang['opt_tsena_bez_nds_reg'],
					name: 'Jnvlp_WholesalePrice',
					disabled: true
				}, {
					xtype: 'hidden',
					fieldLabel: lang['apt_min'],
					name: 'OrgFarmacyPrice_Min',
					allowNegative: false
				}, {
					xtype: 'hidden',
					fieldLabel: lang['apt_maks'],
					name: 'OrgFarmacyPrice_Max',
					allowNegative: false
				}, {
                    hidden: true,
                    items: [{
                        xtype: 'numberfield',
                        fieldLabel: lang['kontrakt_min'],
                        name: 'DrugRequestPrice_Min',
                        allowNegative: false
                    }, {
                        xtype: 'numberfield',
                        fieldLabel: lang['kontrakt_maks'],
                        name: 'DrugRequestPrice_Max',
                        allowNegative: false
                    }]
                }, {
					xtype: 'numberfield',
					fieldLabel: lang['tsena_v_zayavke'],
					name: 'DrugRequest_Price',
					allowNegative: false
				}, {
					fieldLabel: lang['problema_s_zakupom'],
					name: 'DrugListRequestTorg_IsProblem',
					xtype: 'checkbox'
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			bodyStyle: 'padding: 7px;',
			buttons:
			[{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
			{
                hidden: true,
				handler: function() {
					//this.ownerCt.calcFieldValues();
				},
				iconCls: 'actions16',
				text: lang['nayti_tsenyi']
			},
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swDrugListRequestTorgEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('dlrteDrugListRequestTorgEditForm').getForm();
	}	
});