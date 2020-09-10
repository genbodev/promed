/**
 * swMOAreaEditWindow - окно редактирования/добавления площадки, занимаемой организацией.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @version      05.10.2011
 */

sw.Promed.swMOAreaEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        action: null,
        autoHeight: true,
        buttonAlign: 'left',
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        split: true,
        width: 600,
        layout: 'form',
        id: 'MOAreaEditWindow',
        listeners:
        {
            hide: function()
            {
                this.onHide();
            }
        },
        modal: true,
        onHide: Ext.emptyFn,
        plain: true,
        resizable: false,
        doSave: function()
        {
            var form = this.findById('MOAreaEditForm');
            if ( !form.getForm().isValid() )
            {
                sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        fn: function()
                        {
                            form.getFirstInvalidEl().focus(true);
                        },
                        icon: Ext.Msg.WARNING,
                        msg: ERR_INVFIELDS_MSG,
                        title: ERR_INVFIELDS_TIT
                    });
                return false;
            }
            this.submit();
            return true;
        },
		loadOkatoField: function() {
			// расчёт поля ОКАТО
			var win = this;
			var base_form = win.MOAreaEditForm.getForm();
			
			var KLRGN_id = base_form.findField('KLRGN_id').getValue();
			var KLSubRGN_id = base_form.findField('KLSubRGN_id').getValue();
			var KLCity_id = base_form.findField('KLCity_id').getValue();
			var KLTown_id = base_form.findField('KLTown_id').getValue();
			var KLStreet_id = base_form.findField('KLStreet_id').getValue();
	
			if (Ext.isEmpty(KLRGN_id) && Ext.isEmpty(KLSubRGN_id) && Ext.isEmpty(KLCity_id) && Ext.isEmpty(KLTown_id) && Ext.isEmpty(KLStreet_id))
			{
				sw.swMsg.alert(lang['vnimanie'], lang['dlya_polucheniya'] + (getRegionNick() == 'by' ? lang['soato'] : lang['okato']) + lang['po_adresu_neobhodimo_vvesti_adres']);
				return false;
			}
	
			win.getLoadMask(lang['poluchenie'] + (getRegionNick() == 'by' ? lang['soato'] : lang['okato']) + lang['po_adresu']).show();
			Ext.Ajax.request({
				callback: function(options, success, response) {
					win.getLoadMask().hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.KLAdr_Ocatd ) {
							base_form.findField('MoArea_OKATO').setValue(response_obj.KLAdr_Ocatd);
						}
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_polucheniya'] + (getRegionNick() == 'by' ? lang['soato'] : lang['okato']) + lang['po_adresu']);
					}
				},
				params: {
					KLRGN_id: KLRGN_id,
					KLSubRGN_id: KLSubRGN_id,
					KLCity_id: KLCity_id,
					KLTown_id: KLTown_id,
					KLStreet_id: KLStreet_id
				},
				url: '/?c=Address&m=loadOkatoField'
			});
		},
        submit: function()
        {
            var form = this.findById('MOAreaEditForm');
            var current_window = this;
            var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
            loadMask.show();
            form.getForm().submit(
                {
                    params:
                    {
                        action: current_window.action
                    },
                    failure: function(result_form, action)
                    {
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
                        loadMask.hide();
                        if (action.result)
                        {
                            if (action.result.MOArea_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_MOAreaGrid').loadData();
                            }
                            else
                            {
                                sw.swMsg.show(
                                    {
                                        buttons: Ext.Msg.OK,
                                        fn: function()
                                        {
                                            form.hide();
                                        },
                                        icon: Ext.Msg.ERROR,
                                        msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
                                        title: lang['oshibka']
                                    });
                            }
                        }
                    }
                });
        },
        show: function()
        {
            sw.Promed.swMOAreaEditWindow.superclass.show.apply(this, arguments);
            var current_window = this;
            if (!arguments[0])
            {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.ERROR,
                    msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
                    title: lang['oshibka'],
                    fn: function() {
                        this.hide();
                    }
                });
            }

            this.focus();
            this.findById('MOAreaEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;



            if (arguments[0].MOArea_id)
                this.MOArea_id = arguments[0].MOArea_id;
            else
                this.MOArea_id = null;

            if (arguments[0].Lpu_id)
                this.Lpu_id = arguments[0].Lpu_id;
            else
                this.Lpu_id = null;

            if (arguments[0].MOArea_Name)
                this.MOArea_Name = arguments[0].MOArea_Name;
            else
                this.MOArea_Name = null;

            if (arguments[0].MOArea_Member)
                this.MOArea_Member = arguments[0].MOArea_Member;
            else
                this.MOArea_Member = null;

            if (arguments[0].MoArea_Right)
                this.MoArea_Right = arguments[0].MoArea_Right;
            else
                this.MoArea_Right = null;

            if (arguments[0].MoArea_Space)
                this.MoArea_Space = arguments[0].MoArea_Space;
            else
                this.MoArea_Space = null;

            if (arguments[0].MoArea_KodTer)
                this.MoArea_KodTer = arguments[0].MoArea_KodTer;
            else
                this.MoArea_KodTer = null;

            if (arguments[0].MoArea_OrgDT)
                this.MoArea_OrgDT = arguments[0].MoArea_OrgDT;
            else
                this.MoArea_OrgDT = null;

            if (arguments[0].MoArea_AreaSite)
                this.MoArea_AreaSite = arguments[0].MoArea_AreaSite;
            else
                this.MoArea_AreaSite = null;


            if (arguments[0].callback)
            {
                this.callback = arguments[0].callback;
            }
            if (arguments[0].owner)
            {
                this.owner = arguments[0].owner;
            }
            if (arguments[0].onHide)
            {
                this.onHide = arguments[0].onHide;
            }
            if (arguments[0].action)
            {
                this.action = arguments[0].action;
            }
            else
            {
                if ( ( this.MOArea_id ) && ( this.MOArea_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('MOAreaEditForm');
			form.getForm().findField('OKATO_id').setContainerVisible(!getRegionNick().inlist(['by', 'kz']));
			this.syncShadow();
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['ploschadka_zanimaemaya_organizatsiey_dobavlenie']);
                    this.enableEdit(true);
					if (!getRegionNick().inlist(['by', 'kz'])){
						//form.getForm().findField('OKATO_id').getStore().load();
					}
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    form.getForm().findField('MoArea_OrgDT').setValue(new Date());
                    break;
                case 'edit':
                    this.setTitle(lang['ploschadka_zanimaemaya_organizatsiey_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['ploschadka_zanimaemaya_organizatsiey_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                    params:
                    {
                        MOArea_Name: current_window.MOArea_Name,
                        MOArea_Member: current_window.MOArea_Member,
                        MoArea_Right: current_window.MoArea_Right,
                        MoArea_Space: current_window.MoArea_Space,
                        MoArea_KodTer: current_window.MoArea_KodTer,
                        MoArea_OrgDT: current_window.MoArea_OrgDT,
                        MoArea_AreaSite: current_window.MoArea_AreaSite,
                        MOArea_id: current_window.MOArea_id,
                        Lpu_id: current_window.Lpu_id
                    },
                    failure: function(f, o, a)
                    {
                        loadMask.hide();
                        sw.swMsg.show(
                            {
                                buttons: Ext.Msg.OK,
                                fn: function()
                                {
                                    current_window.hide();
                                },
                                icon: Ext.Msg.ERROR,
                                msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
                                title: lang['oshibka']
                            });
                    },
                    success: function()
                    {
						if (!getRegionNick().inlist(['by', 'kz'])){
							var okatoField = form.getForm().findField('OKATO_id');
							if (!Ext.isEmpty(okatoField.getValue())){
								okatoField.getStore().load({
									params: {OKATO_id:okatoField.getValue()},
									callback: function(){
										okatoField.setValue(okatoField.getValue());
									}
								});
							} else {
								form.getForm().findField('OKATO_id').getStore().load();
							}
						}

                        loadMask.hide();
                        current_window.findById('LPEW_Lpu_id').setValue(current_window.Lpu_id);
                        //Оставляем последние 3 знака
						var MoArea_Space = form.getForm().findField('MoArea_Space').getValue();
                        form.getForm().findField('MoArea_Space').setValue(MoArea_Space?Number(MoArea_Space).toFixed(2):'');
						var MoArea_AreaSite = form.getForm().findField('MoArea_AreaSite').getValue();
                        form.getForm().findField('MoArea_AreaSite').setValue(MoArea_AreaSite?Number(MoArea_AreaSite).toFixed(2):'');

                    },
                    url: '/?c=LpuPassport&m=loadMOArea'
                });
            }
            if ( this.action != 'view' )
                Ext.getCmp('LPEW_MOArea_Name').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        initComponent: function()
        {
			var win = this;
            // Форма с полями 
            this.MOAreaEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'MOAreaEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            name: 'MOArea_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            name: 'Address_Zip',
                            xtype: 'hidden'
                        },{
                            name: 'KLCountry_id',
                            xtype: 'hidden'
                        },{
                            name: 'KLRGN_id',
                            xtype: 'hidden'
                        },{
                            name: 'KLSubRGN_id',
                            xtype: 'hidden'
                        },{
                            name: 'KLCity_id',
                            xtype: 'hidden'
                        },{
                            name: 'KLTown_id',
                            xtype: 'hidden'
                        },{
                            name: 'KLStreet_id',
                            xtype: 'hidden'
                        },{
                            name: 'Address_House',
                            xtype: 'hidden'
                        },{
                            name: 'Address_Corpus',
                            xtype: 'hidden'
                        },{
                            name: 'Address_Flat',
                            xtype: 'hidden'
                        },{
                            name: 'Address_Address',
                            xtype: 'hidden'
                        },{
                            name: 'Address_id',
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['naimenovanie_ploschadki'],
                            allowBlank: false,
                            xtype: 'textfield',
                            id: 'LPEW_MOArea_Name',
                            anchor: '99%',
                            name: 'MOArea_Name',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['identifikator_uchastka'],
                            allowBlank: false,
							maskRe: /[0-9:]/,
							regex: /^\d{2}:\d{2}:\d{2}\d{0,5}:\d{0,4}$/,
							regexText: lang['format_99_99_9900000_0000'],
                            xtype: 'textfield',
                            anchor: '99%',
                            name: 'MOArea_Member',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['pravo_na_zemelnyiy_uchastok'],
                            xtype: 'textfield',
                            anchor: '99%',
                            name: 'MoArea_Right',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
							allowDecimals: true,
							allowNegative: false,
                            fieldLabel: lang['ploschad_uchastka_ga'],
                            xtype: 'numberfield',
                            anchor: '99%',
                            name: 'MoArea_Space',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['kod_territorii'],
                            allowBlank: false,
                            xtype: 'textfield',
                            anchor: '99%',
                            name: 'MoArea_KodTer',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['data_organizatsii'],
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            name: 'MoArea_OrgDT',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
							allowDecimals: true,
							allowNegative: false,
							fieldLabel: lang['ploschad_ploschadki_ga'],
							xtype: 'numberfield',
							anchor: '99%',
							name: 'MoArea_AreaSite',
							tabIndex: TABINDEX_LPEEW + 3
						}, {
							codeField: 'OKATO_Code',
							displayField: 'OKATO_Name',
							fieldLabel: lang['kod_po_okato'],
							hiddenName: 'OKATO_id',
							valueField: 'OKATO_id',
							allowBlank: true,
							editable: true,
							lastQuery: '',
							triggerAction: 'all',
							validateOnBlur: true,
							anchor: '99%',
                            minChars: 3,
							onTrigger1Click: function(){
								var combo = win.MOAreaEditForm.getForm().findField('OKATO_id');
								getWnd('swOKATOSearchWindow').show({
									fields: {
										OKATO_id: combo.getValue(),
										OKATO_Code: combo.getFieldValue('OKATO_Code'),
										OKATO_Name: combo.getFieldValue('OKATO_Name')
									},
									callback: function(values) {
                                        combo.getStore().load({
                                            params: {OKATO_id:values.OKATO_id},
                                            callback: function() {
                                                combo.setValue(values.OKATO_id);
                                            }
                                        });
									},
									onClose: function() {
										//
									}
								});
							},
							trigger1Class: 'x-form-search-trigger',
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{name: 'OKATO_id', type: 'int'},
									{name: 'OKATO_Name', type: 'string'},
									{name: 'OKATO_Code', type: 'string'}
								],
								key: 'OKATO_id',
								sortInfo: {
									field: 'OKATO_Code'
								},
								url: C_OKATO_LIST
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{OKATO_Code}</font>&nbsp;{OKATO_Name}',
								'</div></tpl>'
							),
							xtype: 'swbaseremotecombo'
						}, {
							border: false,
							layout: 'column',
							hidden: !getRegionNick().inlist(['by','kz']),
							items: [{
								border: false,
								columnWidth: .85,
								layout: 'form',
								items: [{
									fieldLabel: (getRegionNick() == 'by' ? lang['kod_po_soato'] : lang['kod_po_okato']),
									xtype: 'numberfield',
									anchor: '-10',
									name: 'MoArea_OKATO',
									tabIndex: TABINDEX_LPEEW + 3
								}]
							}, {
								border: false,
								columnWidth: .15,
								layout: 'form',
								items: [{
									handler: function() {
										win.loadOkatoField();
									},
									text: lang['po_adresu'],
									tooltip: lang['raschet'] + (getRegionNick() == 'by' ? lang['soato'] : lang['okato']) + lang['po_adresu'],
									xtype: 'button'
								}]
							}]
						}, new Ext.form.TwinTriggerField({
							enableKeyEvents: true,
							fieldLabel: lang['adres'],
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
							name: 'Address_AddressText',
							onTrigger2Click: function() {
								var base_form = win.MOAreaEditForm.getForm();
								base_form.findField('Address_Zip').setValue('');
								base_form.findField('KLCountry_id').setValue('');
								base_form.findField('KLRGN_id').setValue('');
								base_form.findField('KLSubRGN_id').setValue('');
								base_form.findField('KLCity_id').setValue('');
								base_form.findField('KLTown_id').setValue('');
								base_form.findField('KLStreet_id').setValue('');
								base_form.findField('Address_House').setValue('');
								base_form.findField('Address_Corpus').setValue('');
								base_form.findField('Address_Flat').setValue('');
								base_form.findField('Address_Address').setValue('');
								base_form.findField('Address_AddressText').setValue('');
							},
							onTrigger1Click: function() {
								var base_form = win.MOAreaEditForm.getForm();
								getWnd('swAddressEditWindow').show({
									fields: {
										Address_ZipEdit: base_form.findField('Address_Zip').value,
										KLCountry_idEdit: base_form.findField('KLCountry_id').value,
										KLRgn_idEdit: base_form.findField('KLRGN_id').value,
										KLSubRGN_idEdit: base_form.findField('KLSubRGN_id').value,
										KLCity_idEdit: base_form.findField('KLCity_id').value,
										KLTown_idEdit: base_form.findField('KLTown_id').value,
										KLStreet_idEdit: base_form.findField('KLStreet_id').value,
										Address_HouseEdit: base_form.findField('Address_House').value,
										Address_CorpusEdit: base_form.findField('Address_Corpus').value,
										Address_FlatEdit: base_form.findField('Address_Flat').value,
										Address_AddressEdit: base_form.findField('Address_Address').value
									},
									callback: function(values) {
										base_form.findField('Address_Zip').setValue(values.Address_ZipEdit);
										base_form.findField('KLCountry_id').setValue(values.KLCountry_idEdit);
										base_form.findField('KLRGN_id').setValue(values.KLRgn_idEdit);
										base_form.findField('KLSubRGN_id').setValue(values.KLSubRGN_idEdit);
										base_form.findField('KLCity_id').setValue(values.KLCity_idEdit);
										base_form.findField('KLTown_id').setValue(values.KLTown_idEdit);
										base_form.findField('KLStreet_id').setValue(values.KLStreet_idEdit);
										base_form.findField('Address_House').setValue(values.Address_HouseEdit);
										base_form.findField('Address_Corpus').setValue(values.Address_CorpusEdit);
										base_form.findField('Address_Flat').setValue(values.Address_FlatEdit);
										base_form.findField('Address_Address').setValue(values.Address_AddressEdit);
										base_form.findField('Address_AddressText').setValue(values.Address_AddressEdit);
										base_form.findField('Address_AddressText').focus(true, 500);
									},
									onClose: function() {
										base_form.findField('Address_AddressText').focus(true, 500);
									},
									disableManualInput: true
								})
							},
							readOnly: true,
							tabIndex: TABINDEX_LPEEW + 3,
							trigger1Class: 'x-form-search-trigger',
							trigger2Class: 'x-form-clear-trigger',
							anchor: '99%'
						})
					],
                //},
                    reader: new Ext.data.JsonReader(
                        {
                            success: function()
                            {
                                //
                            }
                        },
                        [
                            {name: 'Lpu_id'},
                            {name: 'MOArea_id'},
                            {name: 'MOArea_Name'},
                            {name: 'MOArea_Member'},
                            {name: 'MoArea_Right'},
                            {name: 'MoArea_Space'},
                            {name: 'MoArea_KodTer'},
                            {name: 'MoArea_OrgDT'},
                            {name: 'MoArea_AreaSite'},
                            {name: 'MoArea_OKATO'},
                            {name: 'OKATO_id'},
							{name: 'Address_id'},
							{name: 'Address_Zip'},
							{name: 'KLCountry_id'},
							{name: 'KLRGN_id'},
							{name: 'KLSubRGN_id'},
							{name: 'KLCity_id'},
							{name: 'KLTown_id'},
							{name: 'KLStreet_id'},
							{name: 'Address_House'},
							{name: 'Address_Corpus'},
							{name: 'Address_Flat'},
							{name: 'Address_Address'},
							{name: 'Address_AddressText'}
                        ]),
                    url: '/?c=LpuPassport&m=saveMOArea'
                });
            Ext.apply(this,
                {
                    buttons:
                        [{
                            handler: function()
                            {
                                this.ownerCt.doSave();
                            },
                            iconCls: 'save16',
                            tabIndex: TABINDEX_LPEEW + 16,
                            text: BTN_FRMSAVE
                        },
                        {
                            text: '-'
                        },
                        HelpButton(this),
                        {
                            handler: function()
                            {
                                this.ownerCt.hide();
                            },
                            iconCls: 'cancel16',
                            tabIndex: TABINDEX_LPEEW + 17,
                            text: BTN_FRMCANCEL
                        }],
                    items: [this.MOAreaEditForm]
                });
            sw.Promed.swMOAreaEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });