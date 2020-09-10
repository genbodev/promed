/**
* swTemplateRefValuesEditWindow - окно редактирования/добавления реестра (счета).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      18.11.2009
* @comment      Префикс для id компонентов trvew (RefValuesEditForm)
*               tabIndex: TABINDEX_TRVEW
*
*
* @input data: action - действие (add, edit, view)
*              RefValues_id - ID записи референтного значения
*/

/*NO PARSE JSON*/

sw.Promed.swTemplateRefValuesEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swTemplateRefValuesEditWindow',
	objectSrc: '/jscore/Forms/Common/swTemplateRefValuesEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	title: WND_TRVEW,
	draggable: true,
	split: true,
	width: 700,
	layout: 'form',
	id: 'TemplateRefValuesEditWindow',
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
	getLoadMask: function(MSG)
	{
		if (MSG) 
		{
			delete(this.loadMask);
		}
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
		}
		return this.loadMask;
	},
	/* Проверка которая выполняется до сохранения данных
	*/
	doSave: function() 
	{
		var form = this.RefValuesForm;
		if (!form.getForm().isValid()) 
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
		frm = form.getForm();
		if ((frm.findField('RefValuesType_id').getValue()==3) && (frm.findField('RefValues_LowerLimit').getValue()>frm.findField('RefValues_UpperLimit').getValue()))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					frm.findField('RefValues_LowerLimit').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['nijniy_limit_ne_mojet_byit_bolshe_verhnego'],
				title: lang['oshibka_sohraneniya']
			});
			return false;
		};
		if ((frm.findField('RefValuesGroup_id').getValue()>0) && (frm.findField('RefValues_LowerAge').getValue()>frm.findField('RefValues_UpperAge').getValue()))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					frm.findField('RefValues_LowerAge').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['minimalnyiy_vozrast_ne_mojet_byit_bolshe_maksimalnogo'],
				title: lang['oshibka_sohraneniya']
			});
			return false;
		};
		form.ownerCt.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.RefValuesForm;
		form.ownerCt.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.getForm().submit(
		{
			//params: {},
			failure: function(result_form, action) 
			{
				form.ownerCt.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				form.ownerCt.getLoadMask().hide();
				if (action.result) 
				{
					if (action.result.RefValues_id)
					{
						var records = form.getForm().getValues();
						records['RefValues_id'] = action.result.RefValues_id;
						form.ownerCt.callback(form.ownerCt.owner, action.result.RefValues_id, records, (form.ownerCt.action=='add')) //, form.getForm().getValues(), (form.ownerCt.action=='add'));
						form.ownerCt.hide();
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
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.RefValuesForm.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset'))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	setPanelVisible: function(panel,flag)
	{
		this.findById(panel).setVisible(flag);
		this.syncSize();
	},
	show: function() 
	{
		sw.Promed.swTemplateRefValuesEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		form.focus();
		form.findById('RefValuesEditForm').getForm().reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].RefValues_id) 
			form.RefValues_id = arguments[0].RefValues_id;
		else 
			form.RefValues_id = null;
			
		if (arguments[0].callback) 
		{
			form.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			form.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			form.action = arguments[0].action;
		}
		else 
		{
			if ((form.RefValues_id) && (form.RefValues_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}
		var frm = form.RefValuesForm.getForm();
		frm.reset();
		frm.setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (form.action) 
		{
			case 'add':
				form.setTitle(WND_TRVEW_ADD);
				form.setFieldsDisabled(false);
				loadMask.hide();
				//frm.findField('RefValues_OPMUCode').focus(true, 50);
				form.setPanelVisible('trvvwRefValuesLimit',false);
				form.setPanelVisible('trvvwRefValuesAge',false);
				break;
			case 'edit':
				form.setTitle(WND_TRVEW_EDIT);
				form.setFieldsDisabled(false);
				break;
			case 'view':
				form.setTitle(WND_TRVEW_VIEW);
				form.setFieldsDisabled(true);
				break;
		}
		
		if (form.action!='add')
		{
			frm.load(
			{
				params: 
				{
					RefValues_id: form.RefValues_id
				},
				failure: function() 
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['pri_poluchenii_dannyih_server_vernul_oshibku_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() 
				{
					loadMask.hide();
					form.setPanelVisible('trvvwRefValuesLimit',(frm.findField('RefValuesType_id').getValue()==3));
					form.setPanelVisible('trvvwRefValuesAge',(frm.findField('RefValuesGroup_id').getValue()>0));
					if (form.action=='edit')
						form.findById('trvewRefValues_Name').focus(true, 50);
					else 
						form.focus();
				},
				url: '/?c=Template&m=editRefValues'
			});
		}
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		
		this.RefValuesForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'RefValuesEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				id: 'trvewRefValues_id',
				name: 'RefValues_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'trvewLpu_id',
				name: 'Lpu_id',
				value: null,
				xtype: 'hidden'
			}, 
			/*{
				// Панель для всех трех кодов
				autoHeight: true,
				style: 'padding: 2px 2px 0px 10px;',
				title: lang['kodyi'],
				xtype: 'fieldset',
				layout: 'column',
				items: 
				[{
					columnWidth: .333,
					labelAlign: 'top',
					layout: 'form',
					labelWidth: 100,
					items: 
					[{
						allowBlank: true,
						fieldLabel: lang['okpmu'],
						autoCreate: {tag: "input", size:20, autocomplete: "off"},
						id: 'trvewRefValues_OPMUCode',
						name: 'RefValues_OPMUCode',
						tabIndex: TABINDEX_TRVEW + 1,
						anchor:'95%',
						xtype: 'textfield'
					}]
				},
				{
					columnWidth: .333,
					labelAlign: 'top',
					layout: 'form',
					labelWidth: 100,
					items: 
					[{
						allowBlank: true,
						fieldLabel: lang['foms'],
						autoCreate: {tag: "input", size:20, autocomplete: "off"},
						id: 'trvewRefValues_LocalCode',
						name: 'RefValues_LocalCode',
						tabIndex: TABINDEX_TRVEW + 1,
						anchor:'95%',
						xtype: 'textfield'
					}]
				},
				{
					columnWidth: .333,
					labelAlign: 'top',
					layout: 'form',
					labelWidth: 100,
					items: 
					[{
						allowBlank: true,
						fieldLabel: 'HL7',
						autoCreate: {tag: "input", size:20, autocomplete: "off"},
						id: 'trvewRefValues_Code',
						name: 'RefValues_Code',
						tabIndex: TABINDEX_TRVEW + 1,
						anchor:'95%',
						xtype: 'textfield'
					}]
				}]
			},*/
			{
				allowBlank: false,
				fieldLabel: lang['naimenovanie'],
				id: 'trvewRefValues_Name',
				name: 'RefValues_Name',
				tabIndex: TABINDEX_TRVEW + 2,
				anchor:'100%',
				xtype: 'textfield'
			}, 
			{
				allowBlank: false,
				fieldLabel: lang['klinicheskoe_nazvanie'],
				id: 'trvewRefValues_Nick',
				autoCreate: {tag: "input", size:50, autocomplete: "off"},
				name: 'RefValues_Nick',
				tabIndex: TABINDEX_TRVEW + 3,
				anchor:'100%',
				xtype: 'textfield'
			}, 
			{
				allowBlank: false,
				disabled: false,
				fieldLabel: lang['tip'],
				tabIndex: TABINDEX_TRVEW + 4,
				comboSubject: 'RefValuesType',
				sortField: 'RefValuesType_id',
				id: 'trvvwRefValuesType_id',
				anchor:'100%',
				xtype: 'swcustomobjectcombo',
				listeners: 
				{
					select: function(combo, record, index) 
					{
						this.setPanelVisible('trvvwRefValuesLimit',(combo.getValue()==3))
					}.createDelegate(this)
				}
			}, 
			{
				// Панель для всех двух границ
				autoHeight: true,
				style: 'padding: 2px 2px 0px 10px;',
				id: 'trvvwRefValuesLimit',
				title: lang['granitsyi_znacheniy_normyi'],
				xtype: 'fieldset',
				layout: 'column',
				items: 
				[{
					columnWidth: .50,
					labelAlign: 'left',
					layout: 'form',
					labelWidth: 140,
					items: 
					[{
						xtype: 'numberfield',
						tabIndex: TABINDEX_TRVEW + 5,
						name: 'RefValues_LowerLimit',
						id:  'trvvwRefValues_LowerLimit',
						maxValue: 9999999,
						minValue: 0,
						decimalPrecision: 100,
						autoCreate: {tag: "input", size:14, autocomplete: "off"},
						allowBlank: true,
						anchor:'100%',
						fieldLabel: lang['nijnyaya']
					}]
				},
				{
					columnWidth: .50,
					labelAlign: 'left',
					layout: 'form',
					labelWidth: 140,
					items: 
					[{
						xtype: 'numberfield',
						tabIndex: TABINDEX_TRVEW + 6,
						name: 'RefValues_UpperLimit',
						id:  'trvvwRefValues_UpperLimit',
						maxValue: 9999999,
						minValue: 0,
						decimalPrecision: 100,
						autoCreate: {tag: "input", size:14, autocomplete: "off"},
						allowBlank: true,
						anchor:'100%',
						fieldLabel: lang['verhnyaya']
					}]
				}]
			},
			{
				allowBlank: false,
				disabled: false,
				fieldLabel: lang['ed_izmereniya'],
				tabIndex: TABINDEX_TRVEW + 7,
				comboSubject: 'RefValuesUnit',
				sortField: 'RefValuesUnit_id',
				id: 'trvvwRefValuesUnit_id',
				anchor:'100%',
				xtype: 'swcustomobjectcombo'
			}, 
			
			{
				allowBlank: true,
				disabled: false,
				fieldLabel: lang['aktualnaya_gruppa'],
				tabIndex: TABINDEX_TRVEW + 8,
				comboSubject: 'RefValuesGroup',
				sortField: 'RefValuesGroup_id',
				id: 'trvvwRefValuesGroup_id',
				anchor:'100%',
				xtype: 'swcustomobjectcombo',
				listeners: 
				{
					select: function(combo, record, index) 
					{
						this.setPanelVisible('trvvwRefValuesAge',(combo.getValue()>0))
					}.createDelegate(this)
				}
			}, 
			{
				// Панель для всех трех кодов
				autoHeight: true,
				style: 'padding: 2px 2px 0px 10px;',
				title: lang['granitsyi_vozrasta'],
				id: 'trvvwRefValuesAge',
				value: null,
				xtype: 'fieldset',
				layout: 'column',
				items: 
				[{
					columnWidth: .50,
					labelAlign: 'left',
					layout: 'form',
					labelWidth: 140,
					items: 
					[{
						xtype: 'numberfield',
						tabIndex: TABINDEX_TRVEW + 9,
						name: 'RefValues_LowerAge',
						id:  'trvvwRefValues_LowerAge',
						maxValue: 9999999,
						minValue: 0,
						autoCreate: {tag: "input", size:14, autocomplete: "off"},
						anchor:'100%',
						allowBlank: true,
						fieldLabel: lang['minimalnyiy']
					},
					{
						allowBlank: true,
						disabled: false,
						fieldLabel: lang['ed_vozrasta'],
						value: null,
						tabIndex: TABINDEX_TRVEW + 11,
						comboSubject: 'AgeUnit',
						sortField: 'AgeUnit_id',
						id: 'trvvwAgeUnit_id',
						anchor:'100%',
						xtype: 'swcustomobjectcombo'
					} 
					]
				},
				{
					columnWidth: .50,
					labelAlign: 'left',
					layout: 'form',
					labelWidth: 140,
					items: 
					[{
						xtype: 'numberfield',
						tabIndex: TABINDEX_TRVEW + 10,
						name: 'RefValues_UpperAge',
						id:  'trvvwRefValues_UpperAge',
						maxValue: 9999999,
						minValue: 0,
						autoCreate: {tag: "input", size:14, autocomplete: "off"},
						anchor:'100%',
						allowBlank: true,
						fieldLabel: lang['maksimalnyiy']
					}]
				}]
			},
			{
				allowBlank: true,
				disabled: false,
				fieldLabel: lang['kategoriya'],
				value: null,
				tabIndex: TABINDEX_TRVEW + 12,
				comboSubject: 'RefCategory',
				sortField: 'RefCategory_id',
				id: 'trvvwRefCategory_id',
				anchor:'100%',
				xtype: 'swcustomobjectcombo'
			}, 
			{
				allowBlank: true,
				disabled: false,
				fieldLabel: lang['kategoriya'],
				value: null,
				tabIndex: TABINDEX_TRVEW + 13,
				comboSubject: 'HormonalPhaseType',
				sortField: 'HormonalPhaseType_id',
				id: 'trvvwHormonalPhaseType_id',
				anchor:'100%',
				xtype: 'swcustomobjectcombo'
			}, 
			{
				allowBlank: true,
				disabled: false,
				fieldLabel: lang['gormonalnaya_faza'],
				value: null,
				tabIndex: TABINDEX_TRVEW + 14,
				comboSubject: 'HormonalPhaseType',
				sortField: 'HormonalPhaseType_Code',
				id: 'trvvwHormonalPhaseType_id',
				anchor:'100%',
				xtype: 'swcustomobjectcombo'
			}, 
			{
				allowBlank: true,
				disabled: false,
				fieldLabel: lang['vremya_sutok'],
				value: null,
				tabIndex: TABINDEX_TRVEW + 15,
				comboSubject: 'TimeOfDay',
				sortField: 'TimeOfDay_Code',
				id: 'trvvwTimeOfDay_id',
				anchor:'100%',
				xtype: 'swcustomobjectcombo'
			}, 
			{
				allowBlank: true,
				disabled: false,
				fieldLabel: lang['material'],
				value: null,
				tabIndex: TABINDEX_TRVEW + 16,
				comboSubject: 'RefMaterial',
				sortField: 'RefMaterial_Code',
				id: 'trvvwRefMaterial_id',
				anchor:'100%',
				xtype: 'swcustomobjectcombo'
			}, 
			/*{
				xtype: 'numberfield',
				tabIndex: TABINDEX_TRVEW + 17,
				name: 'RefValues_Cost',
				id:  'trvvwRefValues_Cost',
				maxValue: 9999999,
				minValue: 0,
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				allowBlank: true,
				fieldLabel: lang['stoimost_issledovaniya']
			},
			{
				xtype: 'numberfield',
				tabIndex: TABINDEX_TRVEW + 18,
				name: 'RefValues_UET',
				id:  'trvvwRefValues_UET',
				maxValue: 9999999,
				minValue: 0,
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				allowBlank: true,
				allowDecimals: true,
				allowNegative: false,
				fieldLabel: lang['uet_issledovaniya']
			},*/
			{
				allowBlank: true,
				fieldLabel: lang['naimenovanie_metoda'],
				id: 'trvewRefValues_Method',
				name: 'RefValues_Method',
				tabIndex: TABINDEX_TRVEW + 19,
				anchor:'100%',
				xtype: 'textfield'
			}, 
			{
				allowBlank: true,
				fieldLabel: lang['opisanie'],
				id: 'trvewRefValues_Description',
				name: 'RefValues_Description',
				tabIndex: TABINDEX_TRVEW + 20,
				anchor:'100%',
				xtype: 'textarea'
			}],
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
								this.doSave(false);
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
				{ name: 'RefValues_id' },
				{ name: 'Lpu_id' },
				{ name: 'RefValues_Code' },
				{ name: 'RefValues_OPMUCode' },
				{ name: 'RefValues_LocalCode' },
				{ name: 'RefValues_Name' },
				{ name: 'RefValues_Nick' },
				{ name: 'RefValuesType_id' },
				{ name: 'RefValuesUnit_id' },
				{ name: 'RefValues_LowerLimit' },
				{ name: 'RefValues_UpperLimit' },
				{ name: 'RefValuesGroup_id' },
				{ name: 'RefValues_LowerAge' },
				{ name: 'RefValues_UpperAge' },
				{ name: 'AgeUnit_id' },
				{ name: 'RefCategory_id' },
				{ name: 'HormonalPhaseType_id' },
				{ name: 'TimeOfDay_id' },
				{ name: 'RefMaterial_id' },
				{ name: 'RefValues_Cost' },
				{ name: 'RefValues_UET' },
				{ name: 'RefValues_Method' },
				{ name: 'RefValues_Description' }
			]),
			timeout: 600,
			url: '/?c=Template&m=saveRefValues'
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
				text: BTN_FRMSAVE,
				tabIndex: TABINDEX_TRVEW + 91
			}, 
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_TRVEW + 92),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: 207,
				text: BTN_FRMCANCEL,
				tabIndex: TABINDEX_TRVEW + 93
			}],
			items: [form.RefValuesForm]
		});
		sw.Promed.swTemplateRefValuesEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});