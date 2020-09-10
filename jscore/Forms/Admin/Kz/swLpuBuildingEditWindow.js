/**
* swLpuBuildingEditWindow - окно редактирования/добавления зданий МО.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      05.10.2011
*/

sw.Promed.swLpuBuildingEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoScroll: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	layout: 'form',
	id: 'LpuBuildingEditWindow',
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
	maximized: true,
	doSave: function()
	{
		var form = this.findById('LpuBuildingEditForm'),
		    base_form = form.getForm(),
            curYear = new Date();

		if ( !base_form.isValid() )
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

		var YearBuilt = form.getForm().findField('LpuBuildingPass_YearBuilt').getValue(),
			YearProjDoc = form.getForm().findField('LpuBuildingPass_YearProjDoc').getValue(),
			YearRepair = form.getForm().findField('LpuBuildingPass_YearRepair').getValue(),
			TotalArea = form.getForm().findField('LpuBuildingPass_TotalArea').getValue(),
			EffBuildVol = form.getForm().findField('LpuBuildingPass_EffBuildVol').getValue(),
			WorkArea = form.getForm().findField('LpuBuildingPass_WorkArea').getValue(),
			focusField = '',
			msg = '';

		switch (true){
			case (!Ext.isEmpty(YearBuilt) && YearBuilt.getFullYear() > curYear.getFullYear()):
				msg = lang['god_postroyki_ne_mojet_byit_bolshe_tekuschego_goda'];
				focusField = 'LpuBuildingPass_YearBuilt';
				break;
			case (!Ext.isEmpty(YearProjDoc) && YearProjDoc.getFullYear() > curYear.getFullYear()):
				msg = lang['god_razrabotki_ne_mojet_byit_bolshe_tekuschego_goda'];
				focusField = 'LpuBuildingPass_YearProjDoc';
				break;
			case (!Ext.isEmpty(YearRepair) && YearRepair.getFullYear() > curYear.getFullYear()):
				msg = lang['god_posledney_rekonstruktsii_ne_mojet_byit_bolshe_tekuschego_goda'];
				focusField = 'LpuBuildingPass_YearRepair';
				break;
			case (!Ext.isEmpty(EffBuildVol) && !Ext.isEmpty(TotalArea) && EffBuildVol > TotalArea):
				msg = lang['obschaya_ploschad_zdaniya_ne_mojet_byit_menshe_chem_poleznaya'];
				focusField = 'LpuBuildingPass_TotalArea';
				break;
			case (!Ext.isEmpty(WorkArea) && !Ext.isEmpty(TotalArea) && WorkArea > TotalArea):
				msg = lang['obschaya_ploschad_zdaniya_ne_mojet_byit_menshe_chem_rabochaya'];
				focusField = 'LpuBuildingPass_TotalArea';
				break;
		}

		if (!Ext.isEmpty(msg)){
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						base_form.findField(focusField).focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: msg,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}

		this.submit();
		return true;
	},
	submit: function()
	{
		var form = this.findById('LpuBuildingEditForm'),
            base_form = form.getForm(),
		    current_window = this,
		    loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});

		//loadMask.show();
		base_form.submit(
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
						Ext.Msg.alert(lang['oshibka']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
				if (action.result)
				{
					if (action.result.LpuBuildingPass_id)
					{
						current_window.hide();
						Ext.getCmp('LpuPassportEditWindow').findById('LPEW_LpuBuilding').loadData();
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
							msg: 'При выполнении операции сохранения произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
    enableEdit: function(enable) {

        var form = this.LpuBuildingEditForm.getForm();
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
	formatNumber: function(v) {
		return (v) ? Number(v.slice(0,-2)) : null;
	},
	show: function()
	{
		sw.Promed.swLpuBuildingEditWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		if (!arguments[0])
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы.<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('LpuBuildingEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].LpuBuildingPass_id)
			this.LpuBuildingPass_id = arguments[0].LpuBuildingPass_id;
		else
			this.LpuBuildingPass_id = null;

		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		else
			this.Lpu_id = null;

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
			if ( ( this.LpuBuildingPass_id ) && ( this.LpuBuildingPass_id > 0 ) )
				this.action = "edit";
			else
				this.action = "add";
		}

		var form = this.findById('LpuBuildingEditForm'),
            base_form = form.getForm();

        /*form.findById('LPEW_MOArea_id').getStore().loadData({callback: function(){
            base_form.setValues(arguments[0]);
        }});*/

		base_form.setValues(arguments[0]);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['zdaniya_mo_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				base_form.clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['zdaniya_mo_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['zdaniya_mo_prosmotr']);
				this.enableEdit(false);
				break;
		}

		if (this.action != 'add')
		{
			base_form.load(
			{
				params:
				{
					LpuBuildingPass_id: current_window.LpuBuildingPass_id,
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
						msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
						title: lang['oshibka']
					});
				},
				success: function(result, request)
				{
					loadMask.hide();
					current_window.findById('LPEW_Lpu_id').setValue(current_window.Lpu_id);

                    base_form.findField('LpuBuildingPass_EffBuildVol').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_EffBuildVol').getValue()));
                    base_form.findField('LpuBuildingPass_TotalArea').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_TotalArea').getValue()));
                    base_form.findField('LpuBuildingPass_BuildVol').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_BuildVol').getValue()));
                    base_form.findField('LpuBuildingPass_FSDis').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_FSDis').getValue()));
                    base_form.findField('LpuBuildingPass_WorkAreaWard').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_WorkAreaWard').getValue()));
                    base_form.findField('LpuBuildingPass_OfficeArea').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_OfficeArea').getValue()));
                    base_form.findField('LpuBuildingPass_WorkAreaWardSect').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_WorkAreaWardSect').getValue()));
                    base_form.findField('LpuBuildingPass_WorkArea').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_WorkArea').getValue()));

                    /*var MOArea_id = base_form.findField('MOArea_id').getValue();
                    base_form.findField('MOArea_id').getStore().load({
                        callback: function() {
                            var index = base_form.findField('MOArea_id').getStore().findBy(function(rec) {
                                return (rec.get('MOArea_id') == MOArea_id);
                            });
            
                            if ( index >= 0 ) {
                                base_form.findField('MOArea_id').setValue(MOArea_id);
                            }
                            else {
                                base_form.findField('MOArea_id').clearValue();
                            }
                        },
                        params: {
                            Lpu_id: current_window.Lpu_id
                        }
                    });*/

				},
				url: '/?c=LpuPassport&m=loadLpuBuilding'
			});
		}
		if ( this.action != 'view' )
			Ext.getCmp('LPEW_LpuBuildingPass_Name').focus(true, 100);
		else
			this.buttons[3].focus();
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.LpuBuildingEditForm = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			//layout: 'column',
			layout: 'fit',
			region: 'north',
			id: 'LpuBuildingEditForm',
			bodyStyle: 'padding: 5px',
			//autoHeight: false,
			items: 
			[
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_data',
                    layout: 'form',
                    title: '1. Общие данные',
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 300,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
                                id: 'LPEW_Lpu_id',
                                name: 'Lpu_id',
                                value: 0,
                                xtype: 'hidden'
                            },{
                                id: 'LPEW_LpuBuildingPass_id',
                                name: 'LpuBuildingPass_id',
                                value: 0,
                                xtype: 'hidden'
                            },{
                                xtype: 'textfield',
                                allowBlank: false,
                                id: 'LPEW_LpuBuildingPass_Name',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['naimenovanie'],
                                name: 'LpuBuildingPass_Name'
                            },/*{
                                displayField: 'MOArea_Name',
                                fieldLabel: lang['naimenovanie_ploschadki'],
                                codeField: 'MOArea_id',
                                hiddenName: 'MOArea_id',
                                id: 'LPEW_MOArea_id',
                                width: 200,
                                editable: false,
                                mode: 'local',
                                resizable: true,
                                store: new Ext.data.Store({
                                    autoLoad: false,
                                    reader: new Ext.data.JsonReader({
                                        id: 'MOArea_id'
                                    }, [
                                        { name: 'MOArea_id', mapping: 'MOArea_id' },
                                        { name: 'MOArea_Name', mapping: 'MOArea_Name' }
                                    ]),
                                    url:'/?c=LpuPassport&m=loadMOArea'
                                }),
                                tpl: new Ext.XTemplate(
                                    '<tpl for="."><div class="x-combo-list-item">',
                                    '<font color="red">{MOArea_id}</font>&nbsp; {MOArea_Name}',
                                    '</div></tpl>'
                                ),
                                triggerAction: 'all',
                                valueField: 'MOArea_id',
                                tabIndex: TABINDEX_LPEEW + 2,
                                xtype: 'swbaselocalcombo'
                            },{
                                xtype: 'textfield',
                                //width: 164,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['identifikator_zdaniya'],
                                allowBlank: false,
                                name: 'LpuBuilding_Name' //toFix
                            },*//*{
                                xtype: 'swcommonsprcombo',
                                //width: 164,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['tip'],
                                allowBlank: false,
                                comboSubject: 'LpuBuildingType',
                                name: 'LpuBuildingType_id'
                            },*/
							{
								xtype: 'textfield',
								//width: 164,
								tabIndex: TABINDEX_LPBEW + 9,
								fieldLabel: lang['identifikator_zdaniya'],
								allowBlank: false,
								name: 'LpuBuildingPass_BuildingIdent'
							},{
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['naznachenie'],
                                allowBlank: false,
                                prefix:'passport101_',
                                comboSubject: 'BuildingUse',
                                name: 'BuildingUse_id',
								id: 'BuildingUse_id_',
                            },{
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['forma_vladeniya'],
                                comboSubject: 'PropertyClass',
                                prefix: 'passport101_',
                                name: 'PropertyClass_id'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['otdelno_stoyaschee_zdanie'],
                                hiddenName: 'LpuBuildingPass_IsDetached'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['moschnost_po_proektu_chislo_koek'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_PowerProjBed'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['obschaya_ploschad_zdaniya_kv_m'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_TotalArea'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['poleznaya_ploschad_zdaniya_kv_m'],
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_EffBuildVol'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['rabochaya_ploschad_kv_m'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_WorkArea'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ploschad_platnyih_otdeleniy_kv_m'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_WorkAreaWardSect'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ploschad_kabinetov_vrachebnogo_priema_kv_m'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_OfficeArea'
                            },/*{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ploschad_koechnyih_otdeleniy_kv_m'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_HZ1'//toFix
                            },*/{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['v_t_ch_palat_kv_m'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_WorkAreaWard'
                            },/*{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_kabinetov_vrachebnogo_priema'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_HZ2'
                            },*/{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['moschnost_po_proektu_chislo_posescheniy'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_PowerProjViz'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['statsionarnyie_mesta'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_StatPlace'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ambulatornyie_mesta'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_AmbPlace'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['obyem_zdaniya'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_BuildVol'
                            },{
                                fieldLabel: lang['na_balanse'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_IsBalance',
                                tabIndex: TABINDEX_LPEEW + 1
                            }
                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_Constructions',
                    layout: 'form',
                    title: '2. Конструкции',
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 300,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['tip_proekta_zdaniya'],
                                comboSubject: 'BuildingType',
                                name: 'BuildingType_id'
                            },{
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['tip_zdaniya'],
                                comboSubject: 'BuildingClass',
                                prefix: 'passport101_',
                                name: 'BuildingClass_id'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['nomer_proekta'],
                                //autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                //maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_NumProj'
                            },{
                                xtype: 'swdatefield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['god_razrabotki_proektnoy_dokumentatsii'],
                                autoCreate: {tag: "input", maxLength: "4", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_YearProjDoc',
                                minValue: '01.01.1800'
                            },{
                                xtype: 'swdatefield',
                                allowBlank: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['god_postroyki'],
                                autoCreate: {tag: "input", maxLength: "4", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_YearBuilt',
                                minValue: '01.01.1800'
                            },{
                                xtype: 'swdatefield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['god_posledney_rekonstruktsii_kapitalnogo_remonta'],
                                autoCreate: {tag: "input", maxLength: "4", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_YearRepair',
                                minValue: '01.01.1800'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ventilyatsiya'],
                                hiddenName: 'LpuBuildingPass_IsVentil'
                            },{
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['nesuschie_konstruktsii'],
                                comboSubject: 'BuildingHoldConstrType',
                                name: 'BuildingHoldConstrType_id'
                            },{
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['perekryitiya'],
                                comboSubject: 'BuildingOverlapType',
                                name: 'BuildingOverlapType_id'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['etajnost'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_Floors'
                            },{
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['tekuschee_sostoyanie_zdaniya'],
                                comboSubject: 'BuildingState',
                                prefix: 'passport101_',
                                name: 'BuildingState_id'
                            }
                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_Communications',
                    layout: 'form',
                    title: '3. Коммуникации',
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 300,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['konditsionirovanie'],
                                hiddenName: 'LpuBuildingPass_IsAirCond'
                            },{
                                comboSubject: 'ElectricType',
                                hiddenName: 'ElectricType_id',
                                prefix:'passport101_',
                                fieldLabel: lang['elektrosnabjenie'],
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9
                            },{
                                comboSubject: 'GasType',
                                hiddenName: 'GasType_id',
                                prefix:'passport101_',
                                xtype: 'swcommonsprcombo',
                                fieldLabel: lang['gazosnabjenie'],
                                tabIndex: TABINDEX_LPBEW + 9
                            },/*{
                                fieldLabel: lang['nalichie_nezavisimyih_istochnikov_energosnabjeniya'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_HZ3',//toFix
                                tabIndex: TABINDEX_LPEEW + 1
                            },*/{
                                comboSubject: 'ColdWaterType',
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['holodnoe_vodosnabjenie'],
                                prefix:'passport101_',
                                hiddenName: 'ColdWaterType_id'
                            },{
                                comboSubject: 'DHotWater',
                                hiddenName: 'DHotWater_id',
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['goryachee_vodosnabjenie']
                                //hiddenName: 'LpuBuildingPass_IsHotWater'
                            },{
                                comboSubject: 'VentilationType',
                                hiddenName: 'VentilationType_id',
                                xtype: 'swcommonsprcombo',
                                prefix:'passport101_',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ventilyatsiya']
                                //hiddenName: 'LpuBuildingPass_IsHotWater'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['priboryi_ucheta_vodosnabjeniya'],
                                hiddenName: 'LpuBuildingPass_IsWaterMeters'
                            },{
                                xtype: 'swcommonsprcombo',
                                comboSubject: 'HeatingType',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['otoplenie'],
                                prefix:'passport101_',
                                hiddenName: 'HeatingType_id'
                                //hiddenName: 'LpuBuildingPass_IsHeat'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['priboryi_ucheta_tepla'],
                                hiddenName: 'LpuBuildingPass_IsHeatMeters'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['nalichie_utepleniya_fasada'],
                                hiddenName: 'LpuBuildingPass_IsInsulFacade'
                            },{
                                xtype: 'swcommonsprcombo',
                                comboSubject: 'DCanalization',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['kanalizatsiya'],
                                hiddenName: 'DCanalization_id'
                                //hiddenName: 'LpuBuildingPass_IsSewerage'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['lechebnoe_gazosnabjenie'],
                                hiddenName: 'LpuBuildingPass_IsMedGas'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['byitovoe_gazosnabjenie'],
                                hiddenName: 'LpuBuildingPass_IsDomesticGas'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['telefonizatsiya'],
                                hiddenName: 'LpuBuildingPass_IsPhone'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_passajirskih_liftov'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_PassLift'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_passajirskih_liftov_trebuyuschih_zamenyi'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_PassLiftReplace'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_meditsinskih_liftov'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_HostLift'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_meditsinskih_liftov_trebuyuschih_zamenyi'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_HostLiftReplace'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_tehnologicheskih_podyemnikov'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_TechLift'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_tehnologicheskih_podyemnikov_trebuyuschih_zamenyi'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_TechLiftReplace'
                            },{
                                xtype: 'swcommonsprcombo',
                                comboSubject: 'DLink',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['kanal_svyazi'],
                                hiddenName: 'DLink_id'
                                //hiddenName: 'LpuBuildingPass_IsSewerage'
                            }
                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_Prices',
                    layout: 'form',
                    title: '4. Оценки стоимости',
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 300,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['pervonachalnaya_stoimost'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_PurchaseCost'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['fakticheskaya_stoimost'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_FactVal'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['stoimost_iznosa_tyis_tenge'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'BuildingLpu_DeprecCost'
                            },{
                                xtype: 'swdatefield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['data_otsenki_stoimosti'],
                                name: 'LpuBuildingPass_ValDT'
                            },{
                                xtype: 'swdatefield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['nachalo_tekuschego_remonta'],
                                name: 'BuildingLpu_RepBegDate'
                            },{
                                xtype: 'swdatefield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['okonchanie_tekuschego_remonta'],
                                name: 'BuildingLpu_RepEndDate'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['stoimost_tekuschego_remonta_tyis_tenge'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'BuildingLpu_RepCost'
                            },{
                                xtype: 'swdatefield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['nachalo_kapitalnogo_remonta'],
                                name: 'BuildingLpu_RepCapBegDate'
                            },{
                                xtype: 'swdatefield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['okonchanie_kapitalnogo_remonta'],
                                name: 'BuildingLpu_RepCapEndDate'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['stoimost_kapitalnogo_remonta_tyis_tenge'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'BuildingLpu_RepCapCost'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ostatochnaya_stoimost'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_ResidualCost'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['iznos_%'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_WearPersent'
                            }

                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_FireBeware',
                    layout: 'form',
                    title: '5. Пожарная безопасность',
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 300,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
                                fieldLabel: lang['avtomaticheskaya_pojarnaya_signalizatsiya_v_zdanii'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_IsAutoFFSig',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['ohrannaya_signalizatsiya_v_zdanii'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_IsSecurAlarm',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['knopka_brelok_ekstrennogo_vyizova_militsii_v_zdanii'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_IsCallButton',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['sistema_opovescheniya_i_upravleniya_evakuatsiey_lyudey_pri_pojare_v_zdanii'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_IsWarningSys',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['protivopojarnoe_vodosnabjenie_zdaniya'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_IsFFWater',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['vyivod_signala_o_srabatyivanii_sistem_protivopojarnoy_zaschityi_v_podrazdelenii_pojarnoy_ohranyi_v_zdanii'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_IsFFOutSignal',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['pryamaya_telefonnaya_svyaz_s_podrazdeleniem_pojarnoy_ohranyi_dlya_zdaniya'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_IsConnectFSecure',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['kolichestvo_narusheniy_trebovaniy_pojarnoy_bezopasnosti'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_CountDist'
                            },{
                                fieldLabel: lang['nalichie_evakuatsionnyih_putey_i_vyihodov_v_zdanii'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_IsEmergExit',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['obespechennost_personala_zdaniya_uchrejdeniya_sredstvami_individualnoy_zaschityi_organov_dyihaniya'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_RespProtect',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['obespechennost_personala_zdaniya_uchrejdeniya_nosilkami_dlya_evakuatsii_malomobilnyih_patsientov'],
                                xtype: 'checkbox',
                                inputValue: '2',
                                uncheckedValue: '1',
                                name: 'LpuBuildingPass_StretProtect',//хз
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['udalenie_ot_blijayshego_pojarnogo_podrazdeleniya'],
                                //autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_FSDis'//хз
                            }
                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_TechState',
                    layout: 'form',
                    title: '6. Техническое состояние',
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 300,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['trebuet_blagoustroystva'],
                                hiddenName: 'LpuBuildingPass_IsRequirImprovement'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['nahoditsya_v_avariynom_sostoyanii'],
                                hiddenName: 'LpuBuildingPass_IsBuildEmerg'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['trebuet_rekonstruktsii'],
                                hiddenName: 'LpuBuildingPass_IsNeedRec'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['trebuet_kapitalnogo_remonta'],
                                hiddenName: 'LpuBuildingPass_IsNeedCap'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['trebuet_snosa'],
                                hiddenName: 'LpuBuildingPass_IsNeedDem'
                            }
                        ]
                    }]
                })
					/*,
					{
						xtype: 'textfield',
						tabIndex: TABINDEX_LPBEW + 9,
						fieldLabel: lang['nomer'],
						maskRe: /[0-9]/,
						autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
						name: 'LpuBuildingPass_Number'
					},
					{
						xtype: 'textfield',
						tabIndex: TABINDEX_LPBEW + 9,
						fieldLabel: lang['postroeno_po_proektu'],
						name: 'LpuBuildingPass_Project'
					},
					{
						xtype: 'textfield',
						tabIndex: TABINDEX_LPBEW + 9,
						fieldLabel: lang['ploschad_uchastka'],
						autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
						maskRe: /[0-9]/,
						name: 'LpuBuildingPass_RegionArea'
					},
					{
						xtype: 'textfield',
						tabIndex: TABINDEX_LPBEW + 9,
						fieldLabel: lang['kol-vo_kabinetov_vrachebnogo_priema'],
						autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
						maskRe: /[0-9]/,
						name: 'LpuBuildingPass_OfficeCount'
					},
					{
						xtype: 'swyesnocombo',
						tabIndex: TABINDEX_LPBEW + 9,
						fieldLabel: lang['nalichie_ohranno-pojarnoy_signalizatsii'],
						hiddenName: 'LpuBuildingPass_IsFireAlarm'
					},*/

			],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'Lpu_id' },
				{ name: 'LpuBuildingPass_id' },
				{ name: 'LpuBuildingPass_Name' },
				//{ name: 'LpuBuildingType_id' },
				{ name: 'BuildingClass_id' },
				//{ name: 'LpuBuildingPass_Number' },
				{ name: 'BuildingAppointmentType_id' },
				//{ name: 'MOArea_id' },
				{ name: 'LpuBuildingPass_YearBuilt' },
				{ name: 'LpuBuildingPass_YearRepair' },
				{ name: 'LpuBuildingPass_PurchaseCost' },
				{ name: 'LpuBuildingPass_ResidualCost' },
				{ name: 'LpuBuildingPass_Floors' },
				{ name: 'LpuBuildingPass_EffBuildVol' },
				{ name: 'LpuBuildingPass_TotalArea' },
				{ name: 'LpuBuildingPass_WorkArea' },
				{ name: 'LpuBuildingPass_AmbPlace' },
				{ name: 'BuildingCurrentState_id' },
				{ name: 'DHotWater_id' },
				{ name: 'LpuBuildingPass_IsWarningSys' },
				{ name: 'LpuBuildingPass_IsCallButton' },
				{ name: 'LpuBuildingPass_IsAutoFFSig' },
				{ name: 'DHeating_id' },
				{ name: 'LpuBuildingPass_FSDis' },
				{ name: 'LpuBuildingPass_ValDT' },
				{ name: 'LpuBuildingPass_StretProtect' },
				{ name: 'DCanalization_id' },
				{ name: 'LpuBuildingPass_BuildVol' },
				{ name: 'LpuBuildingPass_IsBalance' },
				{ name: 'LpuBuildingPass_StatPlace' },
				//{ name: 'LpuBuildingPass_RegionArea' },
				{ name: 'LpuBuildingPass_WorkAreaWardSect' },
				{ name: 'LpuBuildingPass_WorkAreaWard' },
				{ name: 'LpuBuildingPass_PowerProjBed' },
				{ name: 'LpuBuildingPass_PowerProjViz' },
				//{ name: 'LpuBuildingPass_OfficeCount' },
				{ name: 'LpuBuildingPass_OfficeArea' },
				{ name: 'LpuBuildingPass_NumProj' },
				{ name: 'BuildingHoldConstrType_id' },
				{ name: 'BuildingOverlapType_id' },
				{ name: 'LpuBuildingPass_IsAirCond' },
				{ name: 'LpuBuildingPass_IsVentil' },
				{ name: 'LpuBuildingPass_IsElectric' },
				{ name: 'LpuBuildingPass_IsPhone' },
				{ name: 'LpuBuildingPass_IsColdWater' },
				{ name: 'LpuBuildingPass_IsDomesticGas' },
				{ name: 'LpuBuildingPass_IsMedGas' },
				{ name: 'LpuBuildingPass_HostLift' },
				{ name: 'LpuBuildingPass_HostLiftReplace' },
				{ name: 'LpuBuildingPass_PassLift' },
				{ name: 'LpuBuildingPass_PassLiftReplace' },
				{ name: 'LpuBuildingPass_TechLift' },
				{ name: 'LpuBuildingPass_TechLiftReplace' },
				{ name: 'LpuBuildingPass_WearPersent' },
				{ name: 'LpuBuildingPass_IsInsulFacade' },
				//{ name: 'LpuBuildingPass_IsFireAlarm' },
				{ name: 'LpuBuildingPass_IsHeatMeters' },
				{ name: 'LpuBuildingPass_IsWaterMeters' },
				{ name: 'LpuBuildingPass_IsRequirImprovement' },
				{ name: 'LpuBuildingPass_YearProjDoc' },
				{ name: 'DLink_id' },
				{ name: 'LpuBuildingPass_FactVal' },
				{ name: 'LpuBuildingPass_ValDT' },
				{ name: 'LpuBuildingPass_IsSecurAlarm' },
				{ name: 'LpuBuildingPass_IsWarningSys' },
				{ name: 'LpuBuildingPass_IsFFWater' },
				{ name: 'LpuBuildingPass_IsFFOutSignal' },
				{ name: 'LpuBuildingPass_IsConnectFSecure' },
				{ name: 'LpuBuildingPass_CountDist' },
				{ name: 'LpuBuildingPass_IsEmergExit' },
				{ name: 'LpuBuildingPass_RespProtect' },
				{ name: 'LpuBuildingPass_IsBuildEmerg' },
				{ name: 'LpuBuildingPass_IsNeedRec' },
				{ name: 'LpuBuildingPass_IsNeedCap' },
				{ name: 'LpuBuildingPass_IsNeedDem' },
				{ name: 'LpuBuildingPass_IsDetached' },
                { name: 'DHotWater_id' },
                { name: 'DCanalization_id' },
                { name: 'DHeating_id' },
                //kz
				{ name: 'BuildingUse_id' },
				{ name: 'PropertyClass_id' },
				{ name: 'BuildingType_id' },
				{ name: 'BuildingState_id' },
				{ name: 'HeatingType_id' },
				{ name: 'ColdWaterType_id' },
				{ name: 'VentilationType_id' },
				{ name: 'ElectricType_id' },
				{ name: 'GasType_id' },
				{ name: 'BuildingLpu_DeprecCost' },
				{ name: 'BuildingLpu_RepCapCost' },
				{ name: 'BuildingLpu_RepCost' },
				{ name: 'BuildingLpu_RepBegDate' },
				{ name: 'BuildingLpu_RepEndDate' },
				{ name: 'BuildingLpu_RepCapBegDate' },
				{ name: 'BuildingLpu_RepCapEndDate' },
				{ name: 'LpuBuildingPass_BuildingIdent' },
			]),
			url: '/?c=LpuPassport&m=saveLpuBuilding'
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
				tabIndex: TABINDEX_LPBEW + 9,
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
				tabIndex: TABINDEX_LPBEW + 9,
				text: BTN_FRMCANCEL
			}],
			items: [this.LpuBuildingEditForm]
		});
		sw.Promed.swLpuBuildingEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});