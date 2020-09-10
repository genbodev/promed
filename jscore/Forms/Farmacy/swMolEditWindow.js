/**
* swMolEditWindow - окно просмотра и редактирования участков
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      18.06.2009
* @comment      Префикс для id компонентов mfef (swMolEditForm)
*               tabIndex (firstTabIndex): 15300+1 .. 15400

*/
/*NO PARSE JSON*/
sw.Promed.swMolEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMolEditWindow',
	objectSrc: '/jscore/Forms/Farmacy/swMolEditWindow.js',
	title:lang['otdelenie'],
	id: 'swMolEditForm',
	layout: 'border',
	maximizable: false,
	resizable: false,
	shim: false,
	firstTabIndex: 15300,
	width: 600,
	height: 230,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'mfefOk',
		tabIndex: this.firstTabIndex + 91,
		iconCls: 'save16',
		handler: function()
		{
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	},
	{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		id: 'mfefHelp',
		handler: function() {
			ShowHelp('Материально-ответственное лицо');
		}
	},
	{
		text: BTN_FRMCANCEL,
		id: 'mfefCancel',
		tabIndex: this.firstTabIndex + 92,
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
			this.ownerCt.returnFunc(this.ownerCt.owner, -1);
		}
	}
	],
	listeners: {
		hide: function() {
			this.returnFunc(this.owner, -1);
		}
	},
	returnFunc: function(owner, kid) {},
	setFilterMP: function()
	{
		var form = this;
		var bf = form.MainPanel.getForm();
		var med_staff_fact_id = bf.findField('MedStaffFact_id').getValue();
		var dateFrom = form.findById('mfefMol_begDT').getValue();
		var dateTo = form.findById('mfefMol_endDT').getValue();
		var params = new Object();
		//params.isStac = true;
		if (!getGlobalOptions().OrgFarmacy_id) 		
			params.LpuSection_id = this.LpuSection_id;
		
		if (!Ext.isEmpty(dateFrom)) 
			params.dateFrom = Ext.util.Format.date(dateFrom, 'd.m.Y');
			
		if (!Ext.isEmpty(dateTo)) 
			params.dateTo = Ext.util.Format.date(dateTo, 'd.m.Y');
			
		if (Ext.isEmpty(dateFrom) && Ext.isEmpty(dateTo)) 
			params.onDate = getGlobalOptions().date;
		
		bf.findField('MedStaffFact_id').getStore().removeAll();		
		if (!Ext.isEmpty(med_staff_fact_id)) {
			setMedStaffFactGlobalStoreFilter({id: med_staff_fact_id});
			bf.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));	
		}
		setMedStaffFactGlobalStoreFilter(params);
		bf.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore), true);
		if ( bf.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
			bf.findField('MedStaffFact_id').setValue(med_staff_fact_id);
		}
	},
	show: function()
	{
		sw.Promed.swMolEditWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('swMolEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].Mol_id)
			this.Mol_id = arguments[0].Mol_id;
		else 
			this.Mol_id = null;
		if (arguments[0].MedPersonal_id)
			this.MedPersonal_id = arguments[0].MedPersonal_id;
		else 
			this.MedPersonal_id = null;
		if (arguments[0].Person_id)
			this.Person_id = arguments[0].Person_id;
		else 
			this.Person_id = null;
		if (arguments[0].Person_FIO)
			this.Person_FIO = arguments[0].Person_FIO;
		else 
			this.Person_FIO = null;
		
		if (arguments[0].Contragent_id)
			this.Contragent_id = arguments[0].Contragent_id;
		else 
			this.Contragent_id = null;
		if (arguments[0].Contragent_Name)
			this.Contragent_Name = arguments[0].Contragent_Name;
		else 
			this.Contragent_Name = null;
		if (arguments[0].ContragentType_id)
			this.ContragentType_id = arguments[0].ContragentType_id;
		else 
			this.ContragentType_id = null;
			
		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;
		else 
			this.LpuSection_id = null;
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		var form = this;
		form.findById('swMolEditFormPanel').getForm().reset();
		
		if (!this.ContragentType_id.inlist([2,3,5,6])) {
			loadMask.hide();
			Ext.Msg.alert(lang['oshibka'], lang['dannyiy_tip_kontragentov_ne_predusmatrivaet_nalichie_mol']);
			return false;
		}
		
		this.setFormFields();
		
		switch (this.action)
			{
			case 'add':
				form.setTitle(lang['materialno-otvetstvennoe_litso_dobavlenie']);
				break;
			case 'edit':
				form.setTitle(lang['materialno-otvetstvennoe_litso_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['materialno-otvetstvennoe_litso_prosmotr']);
				break;
			}
		
		form.findById('mfefContragent_id').setValue(form.Contragent_id);
		form.findById('mfefContragent_Name').setValue(form.Contragent_Name);
		form.findById('mfefPerson_FIO').setValue(form.Person_FIO);
		form.findById('mfefPerson_id').setValue(form.Person_id);
		if (this.action=='view')
		{
			form.findById('mfefMol_Code').disable();
			form.findById('mfefMedStaffFact_id').disable();
			form.findById('mfefMol_begDT').disable();
			form.findById('mfefMol_endDT').disable();
			form.buttons[0].disable();
		}
		else
		{
			form.findById('mfefMol_Code').enable();
			form.findById('mfefMedStaffFact_id').enable();
			form.findById('mfefMol_begDT').enable();
			form.findById('mfefMol_endDT').enable();
			form.buttons[0].enable();
		}
		/*
		form.findById('mfefMedPersonal_id').getStore().load(
		{
			callback: function(r,o,s) 
			{
				form.findById('mfefMedPersonal_id').setValue(form.findById('mfefMedPersonal_id').getValue());
				form.findById('mfefMedPersonal_id').focus(true, 400);
			}
		});
		*/
		
		if (this.action!='add')
		{
			form.findById('swMolEditFormPanel').getForm().load(
			{
				url: '?c=Farmacy&m=loadMolView',
				params:
				{
					Mol_id: this.Mol_id
				},
				success: function ()
				{
					form.setFilterMP();
					if (form.action!='view')
					{
						form.findById('mfefMol_Code').focus(true, 100);
					}
					loadMask.hide();
					//form.findById('mfefMedStaffFact_id').setValue();
					if (Ext.isEmpty(form.findById('mfefMedStaffFact_id').getValue())) {					
						var mp_id = form.findById('mfefMedPersonal_id').getValue();					
						if (mp_id) {
							index = -1;
							index = form.findById('mfefMedStaffFact_id').getStore().findBy(function(rec) {
								return (rec.get('MedPersonal_id') == mp_id);
							});			
							if ( index >= 0) {
								form.findById('mfefMedStaffFact_id').setValue(form.findById('mfefMedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}
					}
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
			loadMask.hide();
			form.setFilterMP();
			form.findById('mfefMol_Code').focus(true, 250);
		}
		
		
		////////
		var bf = this.MainPanel.getForm();
		bf.findField('MedService_id').getStore().load({
			params: {
				Contragent_id: bf.findField('Contragent_id').getValue(),
				MedServiceType_id: 22 // Склад
			},
			scope: bf.findField('MedService_id'),
			callback: function() {
				//
			}
		});
		
		
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},
	setCode: function()
	{
		var form = this;
		// Запрос к серверу для получения нового кода
		form.getLoadMask().show();
		Ext.Ajax.request(
		{
			url: '/?c=Farmacy&m=generateMolCode',
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					form.findById('mfefMol_Code').setValue(result[0].Mol_Code);
					form.getLoadMask().hide();
				}
				else 
				{
					form.getLoadMask().hide();
				}
			}
		});
	},
	doSave: function() 
	{
		var form = this.findById('swMolEditFormPanel');
		if (!form.getForm().isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
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
	submit: function()
	{
		var form = this.findById('swMolEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('swMolEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		var mp_id = null;
		var record = form.findById('mfefMedStaffFact_id').getStore().getById(form.findById('mfefMedStaffFact_id').getValue());
		if ( record ) 
		{
			var mp_id = record.get('MedPersonal_id');
		}
		form.findById('mfefMedPersonal_id').setValue(mp_id);
		form.getForm().submit(
			{
				params: 
				{
					Mol_id: form.findById('mfefMol_id').getValue(),
					Person_id: form.findById('mfefPerson_id').getValue()
				},
				failure: function(result_form, action)
				{
					if (action.result)
					{
						if (action.result.Error_Code)
						{
							Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
						}
						else
						{
							//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
						}
					}
					loadMask.hide();
				},
				success: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.Mol_id)
						{
							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, action.result.Mol_id);
						}
						else
							Ext.Msg.alert(lang['oshibka_#100004'], lang['pri_sohranenii_proizoshla_oshibka']);
					}
					else
						Ext.Msg.alert(lang['oshibka_#100005'], lang['pri_sohranenii_proizoshla_oshibka']);
				}
			});
	},
	initComponent: function()
	{
		var form = this;
		this.MainPanel = new sw.Promed.FormPanel(
		{
			id:'swMolEditFormPanel',
			//height:this.height,
			//width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: true,
			region: 'center',
			items:
			[
			{
				name: 'Mol_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'mfefMol_id'
			},
			{
				name: 'MedPersonal_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'mfefMedPersonal_id'
			},
			{
				name: 'Person_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'mfefPerson_id'
			},
			{
				name: 'Contragent_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'mfefContragent_id'
			},
			{
				name: 'Contragent_Name',
				fieldLabel: lang['kontragent'],
				tabIndex: -1,
				xtype: 'descfield',
				id: 'mfefContragent_Name'
			},
			{
				fieldLabel: lang['sklad'],
				xtype: 'swmedserviceglobalcombo'
			},
			{
				tabIndex: form.firstTabIndex + 1,
				fieldLabel : lang['kod'],
				name: 'Mol_Code',
				xtype: 'trigger',
				maxValue: 999999,
				minValue: 0,
				maskRe: /\d/,
				autoCreate: {tag: "input", size:14, maxLength: "6", autocomplete: "off"},
				id: 'mfefMol_Code',
				allowBlank:false,
				triggerAction: 'all',
				triggerClass: 'x-form-plus-trigger',
				onTriggerClick: function() 
				{
					Ext.getCmp('swMolEditForm').setCode(this);
				},
				enableKeyEvents:true,
				listeners:
				{
					keydown: function(inp, e) 
					{
						if (e.getKey() == e.F2)
						{
							this.onTriggerClick();
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							e.stopEvent(); 
						}
					}
				}
			}, {
				tabIndex: form.firstTabIndex + 2,
				fieldLabel : lang['data_nachala'],
				allowBlank: true,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'Mol_begDT',
				id: 'mfefMol_begDT',
				listeners: {
					'change': function(combo, newValue, oldValue)  {
						form.setFilterMP();
					}
				}
			}, {
				tabIndex: form.firstTabIndex + 3,
				fieldLabel : lang['data_okonchaniya'],
				allowBlank: true,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'Mol_endDT',
				id: 'mfefMol_endDT',
				listeners: {
					'change': function(combo, newValue, oldValue)  {
						form.setFilterMP();
					}
				}
			},	{
				xtype: 'panel',
				id: 'mfefMedPersonalPanel',
				layout: 'form',
				border: false,
				bodyStyle:'background:transparent;width:100%;',
				labelWidth: 110,
				items:
				[{
					allowBlank: false,
					width: 455,
					hiddenName: 'MedStaffFact_id',
					id: 'mfefMedStaffFact_id',
					lastQuery: '',
					tabIndex: form.firstTabIndex + 2,
					xtype: 'swmedstafffactglobalcombo'
				}]
			},
			{
				xtype: 'panel',
				id: 'mfefPersonPanel',
				layout: 'form',
				border: false,
				bodyStyle:'background:transparent;width:100%;',
				labelWidth: 110,
				items:
				[{
					width: 455,
					name: 'Person_FIO',
					fieldLabel: lang['fio'],
					tabIndex: form.firstTabIndex + 2,
					xtype: 'textfield',
					id: 'mfefPerson_FIO',
					disabled: true
				}]
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
				}
			},
			[
				{ name: 'Mol_id' },
				{ name: 'Contragent_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedService_id' },
				{ name: 'Person_id' },
				{ name: 'Mol_Code' },
				{ name: 'Person_FIO' },
				{ name: 'Mol_begDT' },
				{ name: 'Mol_endDT' }
			]
			),
			url: '/?c=Farmacy&m=save&method=saveMol'
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swMolEditWindow.superclass.initComponent.apply(this, arguments);
	},
	setFormFields: function() { //установка набора текущих полей
		if ((this.ContragentType_id != 2) && (this.ContragentType_id != 5)) {//если контрагент не отделение и не Аптека МУ
			// Если аптека, то Person_id выбран 
			this.findById('mfefMedPersonalPanel').setVisible(false);
			this.findById('mfefPersonPanel').setVisible(true);
			this.findById('mfefPerson_FIO').setAllowBlank(false);
			this.findById('mfefMedStaffFact_id').setAllowBlank(true);
		} else  {
			// Медперсонал должен быть доступен для заполнения в зависимости от указанного LpuSection
			this.findById('mfefMedPersonalPanel').setVisible(true);
			this.findById('mfefPersonPanel').setVisible(false);
			this.findById('mfefPerson_FIO').setAllowBlank(true);
			this.findById('mfefMedStaffFact_id').setAllowBlank(false);
		}
	}
});