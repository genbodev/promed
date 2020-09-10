/**
 * swEvnNotifyProfEditWindow - Извещение по профзаболеванию
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Prof
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      12.2014
 */
sw.Promed.swEvnNotifyProfEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	id: 'swEvnNotifyProfEditWindow',
	layout: 'form',
	modal: true,
	width: 700,
	doSave: function()
	{
		if ( this.formStatus == 'save' || this.action != 'add' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		if (base_form.findField('MedPersonal_id').disabled) {
			params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		}

		if (base_form.findField('Diag_id').disabled) {
			params.Diag_id = base_form.findField('Diag_id').getValue();
		}
		
		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				
				showSysMsg(lang['izveschenie_sozdano']);
				win.formStatus = 'edit';
				loadMask.hide();
				var data = {};
				if (typeof action.result == 'object') {
					data = action.result;
				}
				win.callback(data);
                win.hide();
			}
		});
        return true;
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.findById('FormPanel').getForm();
		
		base_form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swEvnNotifyProfEditWindow.superclass.show.apply(this, arguments);
		
		var me = this;
		if (!arguments[0] || !arguments[0].formParams) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
                    me.hide();
				}
			});
            return false;
		}
		this.focus();
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.formMode = 'remote';
		this.formStatus = 'edit';

        this.EvnNotifyProf_id = arguments[0].EvnNotifyProf_id || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.onHide = arguments[0].onHide || Ext.emptyFn;

        var url, params = {};
		if (this.EvnNotifyProf_id) {
			this.action = 'view';
            this.setTitle(lang['izveschenie_po_profzabolevaniyu_prosmotr']);
            this.setFieldsDisabled(true);
            url = '/?c=MorbusProf&m=doLoadEditFormEvnNotifyProf';
            params.EvnNotifyProf_id = this.EvnNotifyProf_id;
		} else {
            this.action = 'add';
            this.setTitle(lang['izveschenie_po_profzabolevaniyu_dobavlenie']);
            this.setFieldsDisabled(false);
			base_form.findField('Diag_id').disable();
			base_form.findField('MedPersonal_id').disable();
			base_form.findField('HarmWorkFactorType_id').disable();
			base_form.findField('Diag_oid').disable();
            if (!arguments[0].formParams.EvnNotifyProf_setDate) {
                arguments[0].formParams.EvnNotifyProf_setDate = getGlobalOptions().date;
            }
            if (!arguments[0].formParams.EvnNotifyProf_diagDate) {
                arguments[0].formParams.EvnNotifyProf_diagDate = getGlobalOptions().date;
            }
            if (!arguments[0].formParams.MedPersonal_id) {
                arguments[0].formParams.MedPersonal_id = getGlobalOptions().medpersonal_id;
            }
            if (!arguments[0].formParams.MedPersonal_hid) {
                arguments[0].formParams.MedPersonal_hid = getGlobalOptions().medpersonal_id;
            }
            url = '/?c=MorbusProf&m=doLoadEditFormMorbusProf';
            params.Morbus_id = arguments[0].formParams.Morbus_id;
		}

		base_form.findField('Post_id').getStore().load({
			params: {
				Object:'Post',
				Post_id:'',
				Post_Name:''
			},
			callback: function() {
				if ( base_form.findField('Post_id').getValue() > 0 )
					base_form.findField('Post_id').setValue(base_form.findField('Post_id').getValue());
			}
		});

		base_form.setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		if ('add' != me.action) {
	        Ext.Ajax.request({
	            failure:function () {
	                loadMask.hide();
	                me.hide();
	                sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
	            },
	            params: params,
	            success:function (response) {
	                var result = Ext.util.JSON.decode(response.responseText);
	                if ('add' == me.action) {
	                    base_form.findField('Person_id').setValue(result[0].Person_id);
	                    if (!base_form.findField('Diag_id').getValue()) {
	                        base_form.findField('Diag_id').setValue(result[0].Diag_id || null);
	                    }
						if (!base_form.findField('Post_id').getValue()) {
							base_form.findField('Post_id').setValue(result[0].Post_id || null);
						}
						if (!base_form.findField('Org_id').getValue()) {
							base_form.findField('Org_id').setValue(result[0].Org_id || null);
						}
						if (getGlobalOptions().lpu_id) {
							base_form.findField('Lpu_did').setValue(getGlobalOptions().lpu_id);
						}
						if (!base_form.findField('MorbusProfDiag_id').getValue()) {
							base_form.findField('MorbusProfDiag_id').setValue(result[0].MorbusProfDiag_id || null);
							me.onChangeMorbusProfDiag();
						}
						// TODO заполнение остальных полей
	                } else {
	                    base_form.setValues(result[0]);
						me.onChangeMorbusProfDiag();
	                }

					if (!Ext.isEmpty(base_form.findField('Org_id').getValue())) {
						base_form.findField('Org_id').getStore().load({
							params: {
								Object: 'Org',
								Org_id: result[0].Org_id,
								Org_Name: ''
							},
							callback: function () {
								base_form.findField('Org_id').setValue(base_form.findField('Org_id').getValue());
							}
						});
					}

					base_form.findField('MorbusProfDiag_id').getStore().clearFilter();
					base_form.findField('MorbusProfDiag_id').lastQuery = '';
					if (!Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
						base_form.findField('MorbusProfDiag_id').getStore().filterBy(function(record) {
							return (!Ext.isEmpty(record.get('Diag_ids')) && record.get('Diag_ids').replace(/ /g,'').split(',').indexOf(base_form.findField('Diag_id').getValue()) > -1);
						});
					}

	                me.InformationPanel.load({
	                    Person_id: base_form.findField('Person_id').getValue()
	                });
	                if (base_form.findField('Diag_id').getValue()) {
	                    base_form.findField('Diag_id').getStore().load({
	                        params: {
	                            where: ' where Diag_id = ' + base_form.findField('Diag_id').getValue()
	                        },
	                        callback: function()
	                        {
	                            base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());
	                            base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), base_form.findField('Diag_id').getValue());
	                        }
	                    });
	                }
	                base_form.findField('MedPersonal_id').getStore().load({
	                    callback: function()
	                    {
	                        base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
	                        base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
	                    }
	                });
	                base_form.findField('MedPersonal_hid').getStore().load({
	                    callback: function()
	                    {
	                        base_form.findField('MedPersonal_hid').setValue(base_form.findField('MedPersonal_hid').getValue());
	                        base_form.findField('MedPersonal_hid').fireEvent('change', base_form.findField('MedPersonal_hid'), base_form.findField('MedPersonal_hid').getValue());
	                    }
	                });
	                loadMask.hide();
	            },
	            url: url
	        });
		} else {
			base_form.findField('MorbusProfDiag_id').getStore().clearFilter();
			base_form.findField('MorbusProfDiag_id').lastQuery = '';
			if (!Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
				base_form.findField('MorbusProfDiag_id').getStore().filterBy(function(record) {
					return (!Ext.isEmpty(record.get('Diag_ids')) && record.get('Diag_ids').replace(/ /g,'').split(',').indexOf(base_form.findField('Diag_id').getValue()) > -1);
				});
			}

            me.InformationPanel.load({
                Person_id: base_form.findField('Person_id').getValue()
            });
            if (base_form.findField('Diag_id').getValue()) {
                base_form.findField('Diag_id').getStore().load({
                    params: {
                        where: ' where Diag_id = ' + base_form.findField('Diag_id').getValue()
                    },
                    callback: function()
                    {
                        base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());
                        base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), base_form.findField('Diag_id').getValue());
                    }
                });
            }
            base_form.findField('MedPersonal_id').getStore().load({
                callback: function()
                {
                    base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
                    base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
                }
            });
            base_form.findField('MedPersonal_hid').getStore().load({
                callback: function()
                {
                    base_form.findField('MedPersonal_hid').setValue(base_form.findField('MedPersonal_hid').getValue());
                    base_form.findField('MedPersonal_hid').fireEvent('change', base_form.findField('MedPersonal_hid'), base_form.findField('MedPersonal_hid').getValue());
                }
            });
            loadMask.hide();
		}
        return true;
    },
	onChangeMorbusProfDiag: function() {
		// загрузить поля опасный фактор и внешняя причина
		var form = this.FormPanel;
		var base_form = form.getForm();

		base_form.findField('HarmWorkFactorType_id').setValue(base_form.findField('MorbusProfDiag_id').getFieldValue('HarmWorkFactorType_id'));
		base_form.findField('Diag_oid').getStore().load({
			params: {
				where: ' where Diag_id = ' + base_form.findField('MorbusProfDiag_id').getFieldValue('Diag_oid')
			},
			callback: function()
			{
				base_form.findField('Diag_oid').setValue(base_form.findField('MorbusProfDiag_id').getFieldValue('Diag_oid'));
			}
		});
	},
	initComponent: function()
	{
		var me = this;
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 220,
			autoScroll:true,
			url:'/?c=MorbusProf&m=doSaveEvnNotifyProf',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyProf_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyProf_pid',
					xtype: 'hidden'
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
                    name: 'PersonHeight_id',
                    xtype: 'hidden'
                }, {
                    name: 'PersonWeight_id',
                    xtype: 'hidden'
                }, {
                    hiddenName: 'Diag_id',
                    fieldLabel: lang['diagnoz'],
                    xtype: 'swdiagcombo',
                    anchor:'100%',
                    MorbusType_SysNick: 'prof',
                    allowBlank: false
                }, {
					fieldLabel: lang['zabolevanie'],
					hiddenName: 'MorbusProfDiag_id',
					moreFields: [
						{ name: 'Diag_ids', mapping: 'Diag_ids' },
						{ name: 'HarmWorkFactorType_id', mapping: 'HarmWorkFactorType_id' },
						{ name: 'Diag_oid', mapping: 'Diag_oid' }
					],
					listeners: {
						'change': function() {
							me.onChangeMorbusProfDiag();
						}
					},
					editable: true,
					allowBlank: false,
					anchor:'100%',
					comboSubject: 'MorbusProfDiag',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: lang['opasnyiy_proizvodstvennyiy_faktor'],
					hiddenName: 'HarmWorkFactorType_id',
					anchor:'100%',
					comboSubject: 'HarmWorkFactorType',
					xtype: 'swcommonsprcombo'
				}, {
					hiddenName: 'Diag_oid',
					fieldLabel: lang['vneshnyaya_prichina'],
					xtype: 'swdiagcombo',
					anchor:'100%'
				}, {
					fieldLabel: lang['organizatsiya'],
					allowBlank: false,
					hiddenName: 'Org_id',
					onTrigger1Click: function() {
						var combo = this;
						if (combo.disabled) {
							return false;
						}

						getWnd('swOrgSearchWindow').show({
							enableOrgType: true,
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 )
								{
									combo.getStore().load({
										params: {
											Object:'Org',
											Org_id: orgData.Org_id,
											Org_Name:''
										},
										callback: function()
										{
											combo.setValue(orgData.Org_id);
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
										}
									});
								}
								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					},
					anchor:'100%',
					xtype: 'sworgcombo'
				}, {
					name: 'EvnNotifyProf_Section',
					allowBlank: false,
					fieldLabel: lang['naimenovanie_tseha'],
					anchor:'100%',
					xtype: 'textfield'
				}, {
					xtype: 'swpostcombo',
					allowBlank: false,
					minChars: 0,
					queryDelay: 1,
					hiddenName: 'Post_id',
					anchor:'100%',
					fieldLabel: lang['professiya']
				}, {
					hiddenName: 'Lpu_did',
					allowBlank: false,
					fieldLabel: lang['mo_ustanovivshaya_diagnoz'],
					anchor:'100%',
					xtype: 'swlpucombo'
				}, {
					fieldLabel: lang['data_zapolneniya'],
					name: 'EvnNotifyProf_setDate',
					allowBlank: false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['vrach'],
					hiddenName: 'MedPersonal_id',
					listWidth: 750,
					anchor:'100%',
					xtype: 'swmedpersonalcombo',
                    allowBlank: false
                }, {
                    fieldLabel: lang['glavnyiy_vrach'],
                    hiddenName: 'MedPersonal_hid',
                    listWidth: 750,
					anchor:'100%',
                    xtype: 'swmedpersonalcombo',
                    allowBlank: false
				}]
			}]
		});
		Ext.apply(this, 
		{	
			buttons: 
			[{
				handler: function() {
                    me.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
                    me.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swEvnNotifyProfEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
