/**
* Форма редактирования данных для формирования аукционной документации по лоту
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

sw.Promed.swUnitOfTradingDocsDataEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 640,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	owner: null,
	shim: false,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swUnitOfTradingDocsDataEditWindow',
	
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},
	
	show: function() {
		sw.Promed.swUnitOfTradingDocsDataEditWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		if( !arguments[0].WhsDocumentUc_id ) {
			sw.swMsg.alert(lang['oshibka'], 'Не передан идентификатор лота');
			this.hide();
			return false;
		}

		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}

		this.action = arguments[0].action;

		this.setTitle('Данные для документации: ' + this.getActionName(this.action));
		
		var bf = this.Form.getForm();

		bf.setValues(arguments[0]);
        if (arguments[0].Okpd_id) {
            this.okpd_combo.setValueById(arguments[0].Okpd_id);
        }
		
		this.disableFields( this.action == 'view' );
		this.buttons[0].setDisabled( this.action == 'view' );

		bf.findField('Okved_id').focus(true, 100);

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

		bf.submit({
			scope: this,
			params: params,
			failure: function() {
			
			},
			success: function(form, act) {
				this.callback(form);
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
        this.okpd_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel : 'Код ОКПД',
            hiddenName: 'Okpd_id',
            displayField: 'Okpd_Code',
            valueField: 'Okpd_id',
            allowBlank: false,
            editable: true,
            anchor: '100%',
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<font color="red">{Okpd_Code}</font>&nbsp;{Okpd_Name}',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'Okpd_id', mapping: 'Okpd_id' },
                    { name: 'Okpd_Name', mapping: 'Okpd_Name' },
                    { name: 'Okpd_Code', mapping: 'Okpd_Code' }
                ],
                key: 'Okpd_id',
                sortInfo: { field: 'Okpd_Name' },
                url:'/?c=UnitOfTrading&m=loadOkpdCombo'
            }),
            onTrigger2Click: function() {
                var combo = this;

                if (combo.disabled) {
                    return false;
                }

                combo.clearValue();
                delete combo.lastQuery;
                combo.getStore().removeAll();
                combo.getStore().baseParams.query = '';
                combo.fireEvent('change', combo, null);
            },
            setValueById: function(id) {
                var combo = this;
                combo.getStore().baseParams.Okpd_id = id;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(id);
                        combo.store.baseParams.Okpd_id = null;
                    }
                });
            }
        });

		this.Form = new Ext.FormPanel({
			url: '/?c=UnitOfTrading&m=saveUnitOfTradingDocsData',
			frame: true,
			defaults: {
				labelAlign: 'right'
			},
			layout: 'form',
			labelWidth: 170,
			items: [{
				layout: 'form',
				items: [{
					xtype: 'hidden',
					name: 'WhsDocumentUc_id'
				}, {
					xtype: 'hidden',
					name: 'WhsDocumentProcurementRequestSpecDop_id'
				}, {
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel : 'Код ОКВЭД',
						comboSubject: 'Okved',
						anchor: '100%',
						xtype: 'swcommonsprcombo'
					}]
				}, {
					layout: 'form',
					items: [
                        this.okpd_combo
                    ]
				}, {
					layout: 'form',
					items: [{
						allowBlank: false,
						xtype: 'numberfield',
						allowDecimals: false,
						anchor: '100%',
						name: 'WhsDocumentProcurementRequestSpecDop_CodeKOSGU',
						fieldLabel: 'Код КОСГУ'
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'numberfield',
						allowDecimals: false,
						maxLength:2,
						anchor: '100%',
						allowBlank: false,
						name: 'WhsDocumentProcurementRequestSpecDop_Count',
						fieldLabel: 'Количество разнарядок'
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						allowBlank: false,
						comboSubject: 'SupplyPlaceType',
						fieldLabel: 'Место поставки товара'
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swcommonsprcombo',
						anchor: '100%',
						allowBlank: false,
						comboSubject: 'ProvSizeType',
						fieldLabel: 'Размер обеспечения'
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
		sw.Promed.swUnitOfTradingDocsDataEditWindow.superclass.initComponent.apply(this, arguments);
	}
});