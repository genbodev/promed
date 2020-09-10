/**
* swPersonDopDispPlanEditForm - окно просмотра и редактирования плана диспансеризации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009-2013 Swan Ltd.
* @author       
* @version      19.05.2013
* @comment      префикс PDDPEF
*/
/*NO PARSE JSON*/

sw.Promed.swPersonDopDispPlanEditForm = Ext.extend(sw.Promed.BaseForm, {
	callback: Ext.emptyFn,
	layout: 'form',
	title: lang['plan_dispanserizatsii'],
	id: 'PersonDopDispPlanEditForm',
	width: 600,
	autoHeight: true,
	modal: true,
    resizable: false,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'PDDPEFOk',
		tabIndex: TABINDEX_PDDPEF + 91,
		iconCls: 'save16',
		handler: function() {
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	}, 
	{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		tabIndex: TABINDEX_PDDPEF + 92,
		handler: function(button, event) 
		{
			ShowHelp(this.ownerCt.title);
		}
	},
	{
		text: BTN_FRMCANCEL,
		tabIndex: TABINDEX_PDDPEF + 93,
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
			this.ownerCt.returnFunc(this.ownerCt.owner, -1);
		}
	}
	],
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	doSave: function()  {
		var form = this.findById('PersonDopDispPlanEditFormPanel');
		if (!form.getForm().isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		form.ownerCt.submit();
	},
	returnFunc: function(owner, kid) {},
	show: function() {
        sw.Promed.swPersonDopDispPlanEditForm.superclass.show.apply(this, arguments);
		
		if( !arguments[0] || !arguments[0].DispDopClass_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], this.hide.createDelegate(this));
			return false;
		}
		
		this.DispDopClass_id = arguments[0].DispDopClass_id;
		
        var loadMask = new Ext.LoadMask(Ext.get('PersonDopDispPlanEditForm'), { msg: "Подождите, идет загрузка..." });
        loadMask.show();
        if (arguments[0].callback)
            this.returnFunc = arguments[0].callback;
        if (arguments[0].owner)
            this.owner = arguments[0].owner;
        if (arguments[0].action)
            this.action = arguments[0].action;

        if (arguments[0].PersonDopDispPlan_id)
            this.PersonDopDispPlan_id = arguments[0].PersonDopDispPlan_id;
        else
            this.PersonDopDispPlan_id = null;

        if (arguments[0].LpuRegionType_ids) {
            this.LpuRegionType_ids = arguments[0].LpuRegionType_ids;
        } else {
            this.LpuRegionType_ids = null;
        }

        if (arguments[0].Lpu_id)
            this.Lpu_id = arguments[0].Lpu_id;
        else
            this.Lpu_id = null;

        if (!arguments[0])
        {
            Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
            this.hide();
            return false;
        }
        var form = this;
        var bf = form.findById('PersonDopDispPlanEditFormPanel').getForm();
        bf.reset();

        var field = bf.findField('EducationInstitutionType_id');
        if(this.DispDopClass_id.inlist([4,5])){
            field.getEl().up('.x-form-item').setDisplayed(true);
        }
        else{
            field.getEl().up('.x-form-item').setDisplayed(false);
            this.syncShadow();
        }

        // поле месяц необязательно для ввода для периодических осмоторов (refs #21304)
        if (this.DispDopClass_id == 4) {
            bf.findField('PersonDopDispPlan_Month').setAllowBlank(true);
        } else {
            bf.findField('PersonDopDispPlan_Month').setAllowBlank(false);
        }

        switch (this.action)
        {
            case 'add':
                this.enableEdit(true);
                form.setTitle(lang['plan_dispanserizatsii_dobavlenie']);
                break;
            case 'edit':
                this.enableEdit(true);
                form.setTitle(lang['plan_dispanserizatsii_redaktirovanie']);
                break;
            case 'view':
                this.enableEdit(false);
                form.setTitle(lang['plan_dispanserizatsii_prosmotr']);
                break;
        }

        bf.findField('Lpu_id').setValue(this.Lpu_id);
        bf.findField('DispDopClass_id').setValue(this.DispDopClass_id);
        var lpuregion_params = {Lpu_id: this.Lpu_id};
        if (!Ext.isEmpty(this.LpuRegionType_ids)) {
            lpuregion_params.LpuRegionType_ids = Ext.util.JSON.encode(this.LpuRegionType_ids);
        }
        bf.findField('LpuRegion_id').getStore().load(
        {
            params: lpuregion_params
        });

        if (this.action!='add')
        {
            bf.load(
            {
                url: C_PERSONDOPDISPPLAN_GET,
                params:
                {
                    object: 'PersonDopDispPlan',
                    PersonDopDispPlan_id: this.PersonDopDispPlan_id
                },
                success: function ()
                {
                    bf.findField('PersonDopDispPlan_Year').focus(true, 100);
                    loadMask.hide();
                },
                failure: function ()
                {
                    loadMask.hide();
                    Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
                }
            });
        }
        else
        {
            bf.findField('PersonDopDispPlan_Year').focus(true, 100);
            loadMask.hide();
        }
	},
	submit: function()
	{
		var form = this.findById('PersonDopDispPlanEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('PersonDopDispPlanEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		form.getForm().submit(
		{
			failure: function(result_form, action) 
			{
				loadMask.hide();
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.PersonDopDispPlan_id) 
					{
						form.ownerCt.hide();
						form.ownerCt.returnFunc(form.ownerCt.owner, action.result.PersonDopDispPlan_id);
					}
					else
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
				}
				else
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
			}
		});
	},
	initComponent: function() 
	{
		this.MainPanel = new sw.Promed.FormPanel(
		{
			autoHeight: true,
			bodyStyle:'background:#DFE8F6;padding:5px;',
			id:'PersonDopDispPlanEditFormPanel',
			layout: 'form',
			frame: true,
			autoWidth: false,
			region: 'center',
			labelWidth: 230,
			items:
			[
			{
				name: 'PersonDopDispPlan_id',
				tabIndex: -1,
				xtype: 'hidden'
			},
			{
				name: 'Lpu_id',
				tabIndex: -1,
				xtype: 'hidden'
			},
			{
				name: 'DispDopClass_id',
				tabIndex: -1,
				xtype: 'hidden'
			},
			{
				xtype: 'numberfield',
				tabIndex: TABINDEX_PDDPEF + 1,
				name: 'PersonDopDispPlan_Year',
				maxValue: 2030,
				minValue: 2011,
				autoCreate: {tag: "input", size:14, maxLength: "4", autocomplete: "off"},
				allowBlank: false,
				fieldLabel: lang['god'],
				value: new Date().getFullYear()
			},
			{
				xtype: 'combo',
				tabIndex: TABINDEX_PDDPEF + 2,
				hiddenName: 'PersonDopDispPlan_Month',
				allowBlank: false,
				fieldLabel: lang['mesyats'],
				width: 184,
				triggerAction: 'all',
				store: [
					[1, lang['yanvar']],
					[2, lang['fevral']],
					[3, lang['mart']],
					[4, lang['aprel']],
					[5, lang['may']],
					[6, lang['iyun']],
					[7, lang['iyul']],
					[8, lang['avgust']],
					[9, lang['sentyabr']],
					[10, lang['oktyabr']],
					[11, lang['noyabr']],
					[12, lang['dekabr']]
				]
			},
            {
                comboSubject: 'EducationInstitutionType',
                fieldLabel: lang['tip_obrazovatelnogo_uchrejdeniya'],
                hiddenName: 'EducationInstitutionType_id',

                listeners: {
                    'change': function(combo, newValue) {
                        var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

                        var EducationInstitutionClass_id = base_form.findField('EducationInstitutionClass_id').getValue();
                        var hasOldValue = false;
                        base_form.findField('EducationInstitutionClass_id').clearValue();
                        base_form.findField('EducationInstitutionClass_id').getStore().clearFilter();

                        base_form.findField('EducationInstitutionClass_id').getStore().filterBy(function(rec) {
                            if (rec.get('EducationInstitutionType_id') == newValue) {
                                if (rec.get('EducationInstitutionClass_id') == EducationInstitutionClass_id) {
                                    hasOldValue = true;
                                }
                                return true;
                            } else {
                                return false;
                            }
                        });

                        if (hasOldValue) {
                            base_form.findField('EducationInstitutionClass_id').setValue(EducationInstitutionClass_id);
                        }
                    }
                },
                lastQuery: '',
                width: 270,
                listWidth: 400,
                xtype: 'swcommonsprcombo',
                allowBlank: true,
                tabIndex: TABINDEX_PDDPEF + 3
            },
			{
				xtype: 'swlpuregioncombo',
				fieldLabel: lang['uchastok'],
				allowBlank: isSuperAdmin(),
				hiddenName: 'LpuRegion_id',
				tabIndex: TABINDEX_PDDPEF + 4
			},
			{
				xtype: 'numberfield',
				fieldLabel : lang['plan'],
				tabIndex: TABINDEX_PDDPEF + 5,
				name: 'PersonDopDispPlan_Plan',
				maxValue: 999999,
				minValue: 0,
				allowBlank: false,
				decimalPrecision: 0,
				autoCreate: {tag: "input", type: "text", size:"14", maxLength: "9", autocomplete: "off"}
				
			}, {
				allowBlank: false,
				hiddenName:'QuoteUnitType_id',
				tabIndex: TABINDEX_PDDPEF + 6,
				xtype: 'swquoteunittypecombo'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//alert('success');
				}
			},
			[
				{ name: 'PersonDopDispPlan_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuRegion_id' },
				{ name: 'PersonDopDispPlan_Year' },
				{ name: 'PersonDopDispPlan_Month' },
                { name: 'EducationInstitutionType_id' },
				{ name: 'DispDopClass_id' },
				{ name: 'PersonDopDispPlan_Plan' },
				{ name: 'QuoteUnitType_id' }
			]
			),
			url: C_PERSONDOPDISPPLAN_SAVE
		});

		Ext.apply(this,
		{
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swPersonDopDispPlanEditForm.superclass.initComponent.apply(this, arguments);
	}
});