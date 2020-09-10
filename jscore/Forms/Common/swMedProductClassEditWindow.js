/**
 * swMedProductClassEditWindow - окно редактирования/добавления класса медицинского изделия.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Samir Abakhri
 * @version      05.08.2014
 */

sw.Promed.swMedProductClassEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        action: null,
        autoHeight: true,
        buttonAlign: 'left',
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        split: true,
        width: 800,
        layout: 'form',
        id: 'MedProductClassEditWindow',
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
            var form = this.findById('MedProductClassEditForm');
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
        submit: function()
        {
            var form = this.findById('MedProductClassEditForm'),
            	_this = this,
				MedProductClass_Name = form.getForm().findField('MedProductClass_Name').getValue();
            	loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
            loadMask.show();
            form.getForm().submit(
                {
                    params:
                    {
                        action: _this.action
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
                            if (action.result.MedProductClass_id)
                            {
                                _this.callback({MedProductClass_Name: MedProductClass_Name, MedProductClass_id: action.result.MedProductClass_id});
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
        enableEdit: function(enable)
        {
            var form = this.MedProductClassEditForm.getForm();
            this.lists = [];
            this.editFields = [];

            this.getFieldsLists(form, {
                needConstructComboLists: true,
                needConstructEditFields: true
            });

            if (enable)
            {
                (this.editFields).forEach(function(rec){
                    rec.enable();
                });

                this.buttons[0].enable();
            } else {
                (this.editFields).forEach(function(rec){
                    rec.disable();
                });
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swMedProductClassEditWindow.superclass.show.apply(this, arguments);
            var _this = this;
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
            this.findById('MedProductClassEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].MedProductClass_id)
                this.MedProductClass_id = arguments[0].MedProductClass_id;
            else
                this.MedProductClass_id = null;

            if (arguments[0].Lpu_id)
                this.Lpu_id = arguments[0].Lpu_id;
            else
                this.Lpu_id = getGlobalOptions().lpu_id;

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
                if ( ( this.MedProductClass_id ) && ( this.MedProductClass_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('MedProductClassEditForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['klass_meditsinskogo_izdeliya_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['klass_meditsinskogo_izdeliya_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['klass_meditsinskogo_izdeliya_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add') {
                if (!Ext.isEmpty(_this.MedProductClass_id)) {
                    form.getForm().load({
                        params: {
                            MedProductClass_id: _this.MedProductClass_id,
                            Lpu_id: _this.Lpu_id
                        },
                        failure: function(f, o, a) {
                            loadMask.hide();
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: function()
                                {
                                    _this.hide();
                                },
                                icon: Ext.Msg.ERROR,
                                msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
                                title: lang['oshibka']
                            });
                        },
                        success: function(cmp, frm) {
							var result = frm.result.data;

							cmp.findField('MedProductClass_IsAmbulNovor').setValue(result.MedProductClass_IsAmbulNovor==2);
							cmp.findField('MedProductClass_IsAmbulTerr').setValue(result.MedProductClass_IsAmbulTerr==2);
                            _this.findById('MPCEW_Lpu_id').setValue(_this.Lpu_id);
                            loadMask.hide();
                        },
                        url: '/?c=LpuPassport&m=getMedProductClassList'
                    });
                } else {
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function()
                        {
                            form.getFirstInvalidEl().focus(true);
                        },
                        icon: Ext.Msg.WARNING,
                        msg: lang['peredan_pustoy_identifikator_klassa_mi'],
                        title: lang['oshibka']
                    });
                    return false;
                }
            }

            if ( this.action != 'view' )
                _this.MedProductClassEditForm.findById('MedProductClass_Name').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями
            var _this = this;

			_this.store_CardType = new Ext.data.JsonStore({
				url: '/?c=LpuPassport&m=loadCardTypeList',
				key: 'CardType_id',
				autoLoad: false,
				fields: [
					{name: 'CardType_id',    type:'int'},
					{name: 'CardType_pid',  type:'int'}
				]
			});

			_this.store_CardType.load();

			
            this.MedProductClassEditForm = new Ext.form.FormPanel({
				autoHeight: true,
				buttonAlign: 'left',
				frame: true,
				id: 'MedProductClassEditForm',
				labelAlign: 'right',
				region: 'north',
                labelWidth: 250,
				items: [{
                    id: 'MPCEW_Lpu_id',
                    name: 'Lpu_id',
                    xtype: 'hidden'
                }, 
				{
                    id: 'MPCEW_MedProductClass_id',
                    name: 'MedProductClass_id',
                    xtype: 'hidden'
                }, 
				{
                    border: false,
                    layout: 'column',
                    labelWidth: 250,
                    items: [{
                        border: false,
                        columnWidth: .85,
                        layout: 'form',
                        items: [{
                            allowBlank: false,
                            enableKeyEvents: true,
                            fieldLabel: lang['naimenovanie_mi'],
                            id: 'MedProductClass_Name',
                            hiddenName: 'MedProductClass_Name',
                            tabIndex: TABINDEX_MPCEW + 10,
                            width: 400,
                            xtype: 'textfield'
                        }]
                    },{
                        border: false,
                        columnWidth: .10,
                        layout: 'form',
                        tabIndex: TABINDEX_MPCEW + 15,
                        labelWidth: 10,
                        items: [{
                            text:'=',
                            tooltip:lang['skopirovat_naimenovanie_vida'],
                            handler:function () {
                                var base_form = _this.MedProductClassEditForm,
                                    type_value = base_form.findById('MPCEW_MedProductType_id').getFieldValue('MedProductType_Name');

                                if (!Ext.isEmpty(type_value)) {
                                    base_form.findById('MedProductClass_Name').setValue(type_value);
                                }

                            },
                            id:_this.id + '_copyBtn',
                            xtype:'button'
                        }]
                    }]
                },{
                    allowBlank: false,
                    enableKeyEvents: true,
                    fieldLabel: lang['model'],
                    id: 'MPCEW_MedProductClass_Model',
                    name: 'MedProductClass_Model',
					maxLength: 32,
                    tabIndex: TABINDEX_MPCEW + 20,
                    width: 400,
                    xtype: 'textfield'
                },{
                    comboSubject: 'MedProductType',
                    editable: true,
                    fieldLabel: lang['vid_mi'],
                    hiddenName: 'MedProductType_id',
                    id: 'MPCEW_MedProductType_id',
                    width: 400,
                    tabIndex: TABINDEX_MPCEW + 30,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    allowBlank: false,
                    editable: true,
                    comboSubject: 'CardType',
                    fieldLabel: lang['tip_mi'],
                    hiddenName: 'CardType_id',
                    id: 'MPCEW_CardType_id',
                    name: 'MPCEW_CardType_Name',
                    width: 400,
                    tabIndex: TABINDEX_MPCEW + 40,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo',
                    listeners: {
                        change: function(value) {
							//#PROMEDWEB-15685
							var base_form = _this.MedProductClassEditForm;
							var FRMOEquipment = base_form.findById('MPCEW_FRMOEquipment_id');
							var CardType = _this.store_CardType;
							var CardType_pid = CardType.getAt(CardType.find('CardType_id', value.getValue())).get('CardType_pid');
							if(!Ext.isEmpty(CardType_pid) && CardType_pid == 503){
                                FRMOEquipment.setAllowBlank(true);
							}
							else {
                                FRMOEquipment.setAllowBlank(false);
                            }
                        }
                    },
                },{
                    comboSubject: 'FRMOEquipment',
                    id: 'MPCEW_FRMOEquipment_id',
                    name: 'MPCEW_FRMOEquipment_Name',
					hiddenName: 'FRMOEquipment_id',
                    editable: true,
                    fieldLabel: lang['FRMO_Perecheni_apparatov_i_oborudovania_otdelenei_mo'],
                    prefix: 'passport_',
                    tabIndex: TABINDEX_SPEF + 31,
                    width: 400,
                    xtype: 'swcommonsprcombo',
						listeners: {
							change: function(value) {
								//#PROMEDWEB-15685
								var base_form = _this.MedProductClassEditForm;
								var MPCEWFZ30Type = base_form.findById('MPCEW_FZ30Type_id');
								if(Ext.isEmpty(value.getValue())){
									MPCEWFZ30Type.setAllowBlank(true);
								}
								else {
									MPCEWFZ30Type.setAllowBlank(false);
								}
							}
						},
                },{
                    allowBlank: false,
                    editable: true,
                    comboSubject: 'ClassRiskType',
                    fieldLabel: lang['klass_potentsialnogo_riska_primeneniya'],
                    hiddenName: 'ClassRiskType_id',
                    id: 'MPCEW_ClassRiskType_id',
                    width: 400,
                    prefix: 'passport_',
                    tabIndex: TABINDEX_MPCEW + 50,
                    xtype: 'swcommonsprcombo'
                },{
                    allowBlank: false,
                    editable: true,
                    comboSubject: 'FuncPurpType',
                    fieldLabel: lang['funktsionalnoe_naznachenie'],
                    hiddenName: 'FuncPurpType_id',
                    id: 'MPCEW_FuncPurpType_id',
                    width: 400,
                    tabIndex: TABINDEX_MPCEW + 60,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    allowBlank: false,
                    editable: true,
                    comboSubject: 'FuncPurpType',
                    fieldLabel: lang['funktsionalnoe_naznachenie'],
                    hiddenName: 'FuncPurpType_id',
                    id: 'MPCEW_FuncPurpType_id',
                    width: 400,
                    tabIndex: TABINDEX_MPCEW + 60,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    allowBlank: false,
                    editable: true,
                    comboSubject: 'UseAreaType',
                    fieldLabel: lang['oblast_primeneniya'],
                    hiddenName: 'UseAreaType_id',
                    id: 'MPCEW_UseAreaType_id',
                    width: 400,
                    tabIndex: TABINDEX_MPCEW + 70,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    allowBlank: false,
                    editable: true,
                    comboSubject: 'UseSphereType',
                    fieldLabel: lang['sfera_primeneniya'],
                    hiddenName: 'UseSphereType_id',
                    id: 'MPCEW_UseSphereType_id',
                    width: 400,
                    tabIndex: TABINDEX_MPCEW + 80,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    editable: true,
                    comboSubject: 'FZ30Type',
                    fieldLabel: lang['30y_fz'],
                    hiddenName: 'FZ30Type_id',
                    id: 'MPCEW_FZ30Type_id',
                    tabIndex: TABINDEX_MPCEW + 90,
                    width: 400,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    editable: true,
                    comboSubject: 'TNDEDType',
                    fieldLabel: lang['tn_ved'],
                    hiddenName: 'TNDEDType_id',
                    id: 'MPCEW_TNDEDType_id',
                    tabIndex: TABINDEX_MPCEW + 100,
                    width: 400,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    editable: true,
                    comboSubject: 'GMDNType',
                    fieldLabel: 'GMDN',
                    hiddenName: 'GMDNType_id',
                    id: 'MPCEW_GMDNType_id',
                    tabIndex: TABINDEX_MPCEW + 110,
                    width: 400,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    editable: true,
                    comboSubject: 'MT97Type',
                    fieldLabel: lang['mt_po_97pr'],
                    hiddenName: 'MT97Type_id',
                    id: 'MPCEW_MT97Type_id',
                    tabIndex: TABINDEX_MPCEW + 120,
                    width: 400,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    editable: true,
                    comboSubject: 'OKOFType',
                    fieldLabel: lang['okof'],
                    hiddenName: 'OKOFType_id',
                    id: 'MPCEW_OKOFType_id',
                    tabIndex: TABINDEX_MPCEW + 130,
                    width: 400,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    editable: true,
                    comboSubject: 'OKPType',
                    fieldLabel: lang['okp'],
                    hiddenName: 'OKPType_id',
                    id: 'MPCEW_OKPType_id',
                    tabIndex: TABINDEX_MPCEW + 140,
                    width: 400,
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },{
                    editable: true,
                    comboSubject: 'OKPDType',
                    fieldLabel: lang['okpd'],
                    hiddenName: 'OKPDType_id',
                    id: 'MPCEW_OKPDType_id',
                    tabIndex: TABINDEX_MPCEW + 150,
                    width: 400,
				    onTabElement: 'MedProductClass_Name',
                    prefix: 'passport_',
                    xtype: 'swcommonsprcombo'
                },
				{
					xtype: 'checkbox',
					fieldLabel: lang['reanimobil_dlya_novorojdennyih_i_detey_rannego_vozrasta'],
					name: 'MedProductClass_IsAmbulNovor',
					tabIndex: TABINDEX_MPCEW + 160
				},
				{
					xtype: 'checkbox',
					fieldLabel: lang['reanimobil_povyishennoy_prohodimosti'],
					name: 'MedProductClass_IsAmbulTerr',
					tabIndex: TABINDEX_MPCEW + 160
				}
				
				],
                reader: new Ext.data.JsonReader(
                    {
                        success: function()
                        {
                        //alert('success');
                        }
                    },
                    [
                        { name: 'Lpu_id' },
                        { name: 'MedProductClass_id' },
                        { name: 'MedProductClass_Name' },
                        { name: 'MedProductClass_Model' },
                        { name: 'CardType_id' },
                        { name: 'MedProductType_id' },
                        { name: 'ClassRiskType_id' },
                        { name: 'FuncPurpType_id' },
                        { name: 'FZ30Type_id' },
                        { name: 'GMDNType_id' },
                        { name: 'MT97Type_id' },
                        { name: 'OKOFType_id' },
                        { name: 'OKPType_id' },
                        { name: 'OKPDType_id' },
                        { name: 'TNDEDType_id' },
                        { name: 'UseAreaType_id' },
                        { name: 'UseSphereType_id' },
                        { name: 'MedProductClass_IsAmbulNovor' },
                        { name: 'MedProductClass_IsAmbulTerr' },
                        { name: 'FRMOEquipment_id' }
                    ]
                ),
                url:'/?c=LpuPassport&m=saveMedProductClass'
			});
            Ext.apply(this,
                {
                    buttons:
                        [{
                            handler: function()
                            {
                                _this.doSave();
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
                                    _this.hide();
                                },
                                iconCls: 'cancel16',
                                tabIndex: TABINDEX_LPEEW + 17,
                                text: BTN_FRMCANCEL
                            }],
                    items: [this.MedProductClassEditForm]
                });
            sw.Promed.swMedProductClassEditWindow.superclass.initComponent.apply(this, arguments);
        },
		
    });