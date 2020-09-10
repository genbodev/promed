/**
* swXmlTemplateCatEditWindow - Форма редактирования свойств папки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Aleksandr Permyakov (alexpm)
* @version      24.02.2012
* @comment      tabIndex: TABINDEX_XTCEW + (от 21 до 50)
*/

/*NO PARSE JSON*/
sw.Promed.swXmlTemplateCatEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swXmlTemplateCatEditWindow',
	objectSrc: '/jscore/Forms/Common/swXmlTemplateCatEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['svoystva_papki_redaktirovanie'],
	draggable: true,
	id: 'swXmlTemplateCatEditWindow',
	width: 700,
	height: 206,
    modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	loadXmlTemplateCatCombo: function(params) {
        var me = this;
        var form = this.formPanel.getForm();
		var combo = form.findField('XmlTemplateCat_pid');
		var XmlTemplateCat_pid = combo.getValue();
		if (true || sw.Promed.XmlTemplateCatDefault.isAllowRootFolder()) {
			combo.setAllowBlank(true);
			combo.emptyText = lang['kornevaya_papka'];
		} else {
			combo.setAllowBlank(false);
			combo.emptyText = null;
		}

		if (!params) {
			params = {};
		}
        combo.getStore().baseParams = {
            XmlTemplateCat_id: form.findField('XmlTemplateCat_id').getValue() || null,
            LpuSection_id: getGlobalOptions().CurLpuSection_id || null
        };
        //params.XmlTemplateCat_pid = XmlTemplateCat_pid || null;
        params.needMaxLevelFilter = 1;

		combo.lastQuery = '';
		combo.getStore().removeAll();
		combo.getStore().load({
			params: params,
			callback: function(r,o,s){
                if (combo.getStore().getCount() == 0) {
                    // нет ни одной папки доступной для редактирования
					/*
                    sw.Promed.XmlTemplateCatDefault.create({
                        MedStaffFact_id: form.findField('MedStaffFact_id').getValue() || sw.Promed.MedStaffFactByUser.last.MedStaffFact_id,
                        EvnClass_id: form.findField('EvnClass_id').getValue(),
                        XmlType_id: form.findField('XmlType_id').getValue()
                    }, function(success, result, records){
                        if (success && result.success) {
                            combo.setValue(result['XmlTemplateCat_id']);
                            me.loadXmlTemplateCatCombo();
                        } else {
                            sw.swMsg.alert(lang['oshibka'], result['Error_Msg']);
                        }
                    }, me);
					*/
					// сохраняем в корневую папку
                    return true;
                }
				var index = combo.getStore().findBy(function(rec) {
					return (rec.get('XmlTemplateCat_id') == XmlTemplateCat_pid);
				});
				if ( index >= 0 ) {
					combo.setValue(XmlTemplateCat_pid);
				} else {
                    combo.clearValue();
                }
                /*if (XmlTemplateCat_pid != combo.getValue() || (!combo.getValue() && false == isSuperAdmin())) {
                    var msg = lang['vasha_papka_ne_mojet_byit_sohranena_v_tekuschey_papke_vyiberite_druguyu_papku'];
                    if (form.findField('XmlTemplateCat_id').getValue()) {
                        msg = lang['vasha_papka_ne_mojet_byit_sohranena_v_toy_je_papke_v_kotoroy_byila_vyiberite_druguyu_papku'];
                    }
                    sw.swMsg.alert(lang['soobschenie'], msg, function() { combo.focus(false, 250); });
                }*/
			}
		});
	},
	submit: function() {
		var win = this,
			form = this.formPanel.getForm(),
			params = {};
		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		
		win.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
                var result = Ext.util.JSON.decode(action.response.responseText);
                if ( result && result['XmlTemplateCat_id'] && result['success'] && result['success'] === true )
                {
                    win.hide();
                    params.XmlTemplateCat_id = result['XmlTemplateCat_id'];
                    win.callback(params);
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                }
			}
		});
	},

	initComponent: function() {
		var win = this;

        this.accessRightsPanel = new sw.Promed.XmlTemplateScopePanel({
            object: 'XmlTemplateCat',
			labelWidth: 180,
            tabIndexStart: TABINDEX_XTCEW+25
        });
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'left',
			labelWidth: 185,
			region: 'center',
			items: [{
				name: 'XmlTemplateCat_id',
				xtype: 'hidden'
			}, {
                name: 'XmlType_id',
                xtype: 'hidden'
            }, {
                name: 'EvnClass_id',
                xtype: 'hidden'
            }, {
                name: 'MedStaffFact_id',
                xtype: 'hidden'
            }, {
                name: 'MedPersonal_id',
                xtype: 'hidden'
            }, {
                name: 'MedService_id',
                xtype: 'hidden'
            }, {
				fieldLabel: lang['papka_verhnego_urovnya'],
				anchor: '99%',
				hiddenName: 'XmlTemplateCat_pid',
				tabIndex: TABINDEX_XTCEW+23,
				xtype: 'swxmltemplatecatcombo',
				editable: true,
				mode: 'remote'
			},{
				allowBlank: false,
				id: 'XTCEW_XmlTemplateCat_Name',
				fieldLabel: lang['naimenovanie'],
				name: 'XmlTemplateCat_Name',
				value: lang['novaya_papka'],
				width: '97%',
				maxLength: 100,
				tabIndex: TABINDEX_XTCEW + 24,
				xtype: 'textfield'
			},
                this.accessRightsPanel
            ],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.submit();
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'XmlTemplateCat_id' },
				{ name: 'XmlTemplateCat_pid' },
				{ name: 'XmlTemplateCat_Name' },
				{ name: 'XmlTemplateScope_id' },
				{ name: 'XmlTemplateScope_eid' },
				{ name: 'LpuSection_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuSection_Name' },
				{ name: 'Lpu_Name' },
				{ name: 'PMUser_Name' }
			]),
			timeout: 600,
			url: '/?c=XmlTemplateCat&m=save'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_XTCEW + 48,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			//HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				onTabElement: 'XTCEW_XmlTemplateCat_Name',
				tabIndex: TABINDEX_XTCEW + 49,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swXmlTemplateCatEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swXmlTemplateCatEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;

		this.doReset();
		this.center();

		var win = this,
			form = this.formPanel.getForm();

		if (arguments[0].formParams) {
            form.setValues(arguments[0].formParams);
            win.formParams = arguments[0].formParams;
        }
		this.action = (form.findField('XmlTemplateCat_id').getValue() > 0)?'edit':'add';
		if(this.action == 'add'){
			this.setTitle(lang['papka_dobavlenie']);
            win.accessRightsPanel.onLoadForm(form, win.action);
			this.loadXmlTemplateCatCombo();
			form.findField('XmlTemplateCat_Name').focus(true,250);
		}
		if(this.action == 'edit'){
			this.setTitle(lang['papka_redaktirovanie']);
			this.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
				},
				params: {
					XmlTemplateCat_id: form.findField('XmlTemplateCat_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
                    win.accessRightsPanel.onLoadForm(form, win.action);
                    form.findField('XmlType_id').setValue(win.formParams.XmlType_id);
                    form.findField('EvnClass_id').setValue(win.formParams.EvnClass_id);
                    form.findField('MedStaffFact_id').setValue(win.formParams.MedStaffFact_id);
                    form.findField('MedPersonal_id').setValue(win.formParams.MedPersonal_id);
                    form.findField('MedService_id').setValue(win.formParams.MedService_id);
                    win.loadXmlTemplateCatCombo();
				},
				url: '/?c=XmlTemplateCat&m=loadForm'
			});
		}
	}
});
