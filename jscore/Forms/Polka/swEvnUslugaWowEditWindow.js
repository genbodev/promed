/**
* swEvnUslugaWOWEditWindow - окно редактирования/добавления исследований.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      22.03.2009
* @comment      Префикс для id компонентов euwew (EvnUslugaWOWEditForm)
*               tabIndex (firstTabIndex): 18600+1 .. 18700
*
*
* @input data: action - действие (add, edit, view)
*              EvnUslugaWOW_id - ID реестра
*/

sw.Promed.swEvnUslugaWOWEditWindow = Ext.extend(sw.Promed.BaseForm, {
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
	firstTabIndex: 18600,
	id: 'EvnUslugaWOWEditWindow',
	title: lang['laboratornyie_issledovaniya'],
	listeners: 
	{
		hide: function() 
		{
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	plain: true,
	resizable: false,
	doSave: function() 
	{
		var form = this.EditForm;
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
		var setDate = form.findById('euwewEvnUslugaWOW_setDate').getValue();
		var didDate = form.findById('euwewEvnUslugaWOW_didDate').getValue();
		if ((setDate) && (didDate) && (setDate>didDate))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('euwewEvnUslugaWOW_setDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_rezultata_ne_mojet_byit_menshe_datyi_issledovaniya'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.ownerCt.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.EditForm;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."});
		loadMask.show();
		var mp_fio = '';
		var mp_id = null;
		var msf_id = form.getForm().findField('MedStaffFact_id').getValue();
		var record = null;
		record = form.getForm().findField('MedStaffFact_id').getStore().getById(msf_id);
		if ( record ) 
		{
			mp_fio = record.get('MedPersonal_Fio');
			mp_id = record.get('MedPersonal_id');
		}
		form.getForm().findField('MedPersonal_id').setValue(mp_id);
		
		form.getForm().submit(
		{
			params: 
			{
				//
			},
			failure: function(result_form, action) 
			{
				loadMask.hide();
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					//if (action.result.EvnUslugaWOW_id)
					if (action.result.EvnUslugaWOW_id)
					{
						//log(form.getForm().getValues());
						//var records = {EvnUslugaWOW_id:action.result.EvnUslugaWOW_id}
						form.ownerCt.callback(form.ownerCt.owner, action.result.EvnUslugaWOW_id) 
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
	setModeForm: function() 
	{
		var form = this;
		var enable = true;
		if (form.action == 'view')
		{
			enable = false;
		}
		form.findById('euwewEvnUslugaWOW_setDate').setDisabled(!enable);
		form.findById('euwewEvnUslugaWOW_didDate').setDisabled(!enable);
		form.findById('euwewDispWowUslugaType_id').setDisabled(!enable);
		form.findById('euwewLpuSection_id').setDisabled(!enable);
		form.findById('euwewMedPersonal_id').setDisabled(!enable);
		form.findById('euwewUsluga_id').setDisabled(!enable);
		form.buttons[0].setDisabled(!enable);
		
		switch (form.action) 
		{
			case 'add':
				form.setTitle(lang['laboratornoe_issledovanie_dobavlenie']);
				form.findById('euwewEvnUslugaWOW_setDate').focus(true, 50);
				break;
			case 'edit':
				form.setTitle(lang['laboratornoe_issledovanie_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['laboratornoe_issledovanie_prosmotr']);
				break;
		}
	},
	// получаем список доступных типов услуг + та которая может быть указана
	getListDispWowUslugaType: function (gridpanel, value)
	{
		var list = Array();
		if (gridpanel.getCount()>0)
		{
			gridpanel.getGrid().getStore().each(function(rec) 
			{
				if ((rec.data.DispWowUslugaType_id!=value) && (rec.get('DispWOWSpec_id')!=17))
				{
					list.push(rec.data.DispWowUslugaType_id);
				}
			});
		}
		return list;
	},
	loadDataForm: function () 
	{
		
		var bf = this.EditForm.getForm();
		var ls_id = bf.findField('LpuSection_id').getValue();
		var mp_id = bf.findField('MedPersonal_id').getValue(); 

		setCurrentDateTime(
		{
			callback: function() 
			{
				bf.findField('EvnUslugaWOW_setDate').fireEvent('change', bf.findField('EvnUslugaWOW_setDate'), bf.findField('EvnUslugaWOW_setDate').getValue());
				var index = bf.findField('MedStaffFact_id').getStore().findBy(function(record, id) 
				{
					if ( record.get('LpuSection_id') == ls_id && record.get('MedPersonal_id') == mp_id )
						return true;
					else
						return false;
				});

				if ( index >= 0 ) 
				{
					bf.findField('MedStaffFact_id').setValue(bf.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
				}

				bf.findField('EvnUslugaWOW_setDate').focus(true, 0);
			},
			dateField: bf.findField('EvnUslugaWOW_setDate'),
			loadMask: false,
			setDate: false,
			setDateMaxValue: true,
			setDateMinValue: false,
			setTime: false,
			//timeField: bf.findField('EvnUslugaWOW_setDate'),
			windowId: 'EvnUslugaWOWEditWindow'
		});
	},
	/* Устанавливает фильтр выбора DispWowUslugaType, в качестве значения принимает значение этого поля */
	setFilter: function(value)
	{
		form = this;
		var mass = form.getListDispWowUslugaType(form.owner,value);
		var combo = form.findById('euwewDispWowUslugaType_id');
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		combo.getStore().filterBy(function(record) 
		{
			if (value==record.get('DispWowUslugaType_id'))
			{
				combo.fireEvent('select', combo, record, 0);
				combo.fireEvent('change', combo, value, '');
				//combo.fireEvent('select', combo, record, 0);
			}
			return (!(record.get('DispWowUslugaType_id').inlist(mass))) && (!(((form.Sex_id == 2) && (record.get('DispWowUslugaType_id').inlist([7]))) || ((form.Sex_id == 1) && (record.get('DispWowUslugaType_id').inlist([4,5,6,9,11])))));
		});
		if (value==0)
		{
			combo.fireEvent('change', combo, '', '');
		}
	},
	setUsluga: function()
	{
		form = this;
		var usluga_combo = form.findById('euwewUsluga_id');
		var usluga_id = usluga_combo.getValue();
		if (usluga_id != null && usluga_id.toString().length > 0)
		{
			usluga_combo.getStore().load(
			{
				callback: function() 
				{
					usluga_combo.getStore().each(function(record) 
					{
						if (record.data.Usluga_id == usluga_id)
						{
							usluga_combo.fireEvent('select', usluga_combo, record, 0);
						}
					});
				},
				params: { where: "where UslugaType_id = 2 and Usluga_id = " + usluga_id }
			});
		}
	},
	show: function() 
	{
		sw.Promed.swEvnUslugaWOWEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		var base_form = form.findById('EvnUslugaWOWEditForm').getForm();
		
		if (!arguments[0] || !arguments[0].EvnPLWOW_id) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы <b>'+form.title+'</b>.<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
			this.hide();
			return false;
		}
		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		form.focus();
		form.findById('EvnUslugaWOWEditForm').getForm().reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].EvnUslugaWOW_id) 
			form.EvnUslugaWOW_id = arguments[0].EvnUslugaWOW_id;
		else 
			form.EvnUslugaWOW_id = null;
			
		if (arguments[0].EvnPLWOW_id) 
			form.EvnPLWOW_id = arguments[0].EvnPLWOW_id;
		
		if (arguments[0].PersonEvn_id) 
			form.PersonEvn_id = arguments[0].PersonEvn_id;
		if (arguments[0].Sex_id) 
			form.Sex_id = arguments[0].Sex_id;
		else
			form.Sex_id = null;
		if (arguments[0].Server_id) 
			form.Server_id = arguments[0].Server_id;
			
		if (arguments[0].callback) 
		{
			form.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			form.owner = arguments[0].owner;
		}
		else 
		{
			form.owner = null;
		}
		/*
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		*/
		if (arguments[0].action) 
		{
			form.action = arguments[0].action;
		}
		else 
		{
			if ((form.EvnUslugaWOW_id) && (form.EvnUslugaWOW_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}
		
		this.PersonFrame.load(
		{
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaWOW_setDate');
				clearDateAfterPersonDeath('personpanelid', 'euwewPersonInformationFrame', field);
			}
		});
		
		form.setModeForm();
		form.findById('EvnUslugaWOWEditForm').getForm().setValues(arguments[0]);
		
		if (form.action!='add')
		{
			form.findById('EvnUslugaWOWEditForm').getForm().load(
			{
				params: 
				{
					EvnUslugaWOW_id: form.EvnUslugaWOW_id,
					EvnPLWOW_id: form.EvnPLWOW_id
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
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() 
				{
					form.loadDataForm();
					form.setFilter(form.findById('euwewDispWowUslugaType_id').getValue());
					form.setUsluga();
					loadMask.hide();
					if (form.action=='edit')
						form.findById('euwewEvnUslugaWOW_setDate').focus(true, 50);
					else 
						Ext.getCmp('euwewButtonCancel').focus();
				},
				url: '/?c=EvnPLWOW&m=loadEvnUslugaWOW'
			});
		}
		else 
		{
			form.loadDataForm();
			form.setFilter(0);
			loadMask.hide();
		}
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		this.PersonFrame  = new sw.Promed.PersonInformationPanelShort(
		{
			id: 'euwewPersonInformationFrame',
			region: 'north'
		});
		this.EditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'center',
			buttonAlign: 'left',
			frame: true,
			id: 'EvnUslugaWOWEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				id: 'euwewEvnUslugaWOW_id',
				name: 'EvnUslugaWOW_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				id: 'euwewEvnPLWOW_id',
				name: 'EvnPLWOW_id',
				xtype: 'hidden'
			}, 
			{
				id: 'euwewPersonEvn_id',
				name: 'PersonEvn_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				id: 'euwewServer_id',
				name: 'Server_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				allowBlank: false,
				fieldLabel: lang['data_issledovaniya'],
				format: 'd.m.Y',
				id: 'euwewEvnUslugaWOW_setDate',
				name: 'EvnUslugaWOW_setDate',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: this.firstTabIndex+1,
				width: 100,
				xtype: 'swdatefield',
				listeners: 
				{
					change: 
						function(field, newValue, oldValue) 
						{
							if (blockedDateAfterPersonDeath('personpanelid', 'euwewPersonInformationFrame', field, newValue, oldValue)) return;
						
							var bf = this.findById('EvnUslugaWOWEditForm').getForm();
							var ls_id = bf.findField('LpuSection_id').getValue();
							var msf_id = bf.findField('MedStaffFact_id').getValue();

							bf.findField('LpuSection_id').clearValue();
							bf.findField('MedStaffFact_id').clearValue();

							if ( !newValue ) 
							{
								setLpuSectionGlobalStoreFilter(
								{
									//isPolka: true
								});
								setMedStaffFactGlobalStoreFilter(
								{
									//isPolka: true
								});
							}
							else 
							{
								setLpuSectionGlobalStoreFilter(
								{
									//isPolka: true,
									onDate: Ext.util.Format.date(newValue, 'd.m.Y')
								});

								setMedStaffFactGlobalStoreFilter(
								{
									//isPolka: true,
									onDate: Ext.util.Format.date(newValue, 'd.m.Y')
								});
								if (this.action == 'add')
									bf.findField('Usluga_id').setFilterActualUsluga(newValue, null);
							}

							bf.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							bf.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
							
							if ( bf.findField('LpuSection_id').getStore().getById(ls_id) ) 
							{
								bf.findField('LpuSection_id').setValue(ls_id);
							}

							if ( bf.findField('MedStaffFact_id').getStore().getById(msf_id) ) 
							{
								bf.findField('MedStaffFact_id').setValue(msf_id);
							}
						}.createDelegate(this),
					keydown: 
						function(inp, e) 
						{
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) 
							{
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
				}
			},
			{
				allowBlank: false,
				fieldLabel: lang['data_rezultata'],
				format: 'd.m.Y',
				id: 'euwewEvnUslugaWOW_didDate',
				name: 'EvnUslugaWOW_didDate',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: this.firstTabIndex+2,
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				anchor: '100%',
				enableKeyEvents: true,
				fieldLabel: lang['vid'],
				lastQuery: '',
				id: 'euwewDispWowUslugaType_id',
				name: 'DispWowUslugaType_id',
				tabIndex: this.firstTabIndex+3,
				validateOnBlur: false,
				xtype: 'swdispwowuslugatypecombo',
				listeners: 
				{
					change: 
						function(field, newValue, oldValue) 
						{
							var fin = '';
							switch (newValue.toString())
							{
								case '1': fin = "Usluga_Code in ('02000100')"; break; // a)	развернутый клинический анализ крови (02000100); 
								case '2': fin = "Usluga_Code in ('02000130')"; break; // b)	общий анализ мочи (02000130);
								case '3': fin = "Usluga_Code in ('02000400')"; break; // c)	биохимический анализ крови (02000400)
								case '4': fin = "Usluga_Code in ('02000596')"; break;  // d)	Онкомаркёр альфафетопротеин<только для женщин>, (02000596)
								case '5': fin = "Usluga_Code in ('02000594')"; break; // e)	Онкомаркёр РЭА <только для женщин>, (02000594)
								case '6': fin = "Usluga_Code in ('02000592')"; break; // f)	Онкомаркёр СА-125 <только для женщин>, (02000592)
								case '7': fin = "Usluga_Code in ('02000593')"; break; // g)	Онкомаркёр ПСА общий <только для мужчин>, (02000593)
								case '8': fin = "Usluga_Code in ('02000595')"; break; // h)	Онкомаркёр СА-19-9; (02000595)
								case '9': fin = "Usluga_Code in ('02000238')"; break; // i)	мазок по Папаниколау (Цитологическое исследование соскобов шейки матки и цервикального канала (02000238)) <только для женщин>, <***>.
								case '10': fin = "Usluga_Code in ('02002201')"; break; // j)	рентгенография органов грудной клетки  (02002201)<***>;
								case '11': fin = "Usluga_Code in ('02002230', '02001309')"; break; // k)	маммография/УЗИ молочных желез (02002230 маммография или 02001309- Узи молочных желез) <только для женщин>, <***>;
								case '12': fin = "Usluga_Code in ('02001348')"; break; // l)	ультразвуковое исследование органов брюшной полости и органов малого таза (02001348)<***>;
								case '13': fin = "Usluga_Code in ('02001101')"; break; // m)	электрокардиографическое исследование (02001101) <***>;
								case '14': fin = "Usluga_Code in ('02270408')"; break; // n)	измерение внутриглазного давления (02270408)<***>;
								case '15': fin = "Usluga_Code in ('02270401')"; break; // o)	определение остроты зрения (02270401);
								case '16': fin = "Usluga_Code in ('02270302')"; break; // p)	офтальмоскопия глазного дна  (02270302)<***>.
								case '17': fin = "(1!=1)"; break; // q)	дополнительное обследование
								default: fin = "Usluga_Code in ('02000100', '02000130', '02000400', '02000596', '02000594', '02000592', '02000593','02000595','02000238','02002201','02002230', '02001309','02001348','02001101','02270408','02270401','02270302')"; break; 
								// <***> Обследование производится непосредственно в лечебно-профилактическом учреждении.
							}
							var combo = field.ownerCt.findById('euwewUsluga_id');
							var usluga_id = combo.getValue();
							//combo.setLoadQuery(newValue==17);
							combo.setLoadQuery(true);
							//log(fin);
							combo.getStore().load(
							{
								callback: function() 
								{
									var fs = false;
									combo.getStore().each(function(record) 
									{
										if ((combo.getStore().getCount()==1) && (usluga_id==''))
										{
											usluga_id = record.get('Usluga_id');
											//log('1 '+usluga_id);
											combo.setValue(usluga_id);
										}
										//log(record.get('Usluga_id')+' = '+usluga_id);
										if (record.get('Usluga_id') == usluga_id)
										{
											combo.fireEvent('select', combo, record, 0);
											fs = true;
										}
									});
									if (!fs) 
									{
										combo.setValue('');
										combo.clearInvalid();
									}
								},
								params: { where: "where UslugaType_id = 2 and "+fin+" " }
							});
						}
				}
			}, 
			{
				allowBlank: true,
				anchor: '100%',
				name: 'LpuSection_id',
				hiddenName: 'LpuSection_id',
				id: 'euwewLpuSection_id',
				lastQuery: '',
				linkedElements: 
				[
					'euwewMedStaffFact_id'
				],
				tabIndex: this.firstTabIndex+4,
				xtype: 'swlpusectionglobalcombo'
			},
			{
				name: 'MedPersonal_id',
				id: 'euwewMedPersonal_id',
				xtype: 'hidden'
			},
			{
				allowBlank: true,
				anchor: '100%',
				hiddenName: 'MedStaffFact_id',
				id: 'euwewMedStaffFact_id',
				parentElementId: 'euwewLpuSection_id',
				tabIndex: this.firstTabIndex+5,
				xtype: 'swmedstafffactglobalcombo'
			},
			{
				allowBlank: false,
				anchor: '100%',
				lastQuery: '',
				id: 'euwewUsluga_id',
				name: 'Usluga_id',
				tabIndex: this.firstTabIndex+6,
				loadQuery: true,
				/*listeners: {
					'beforequery': function(event) {
						var usluga_date_field = this.findById('EvnUslugaWOWEditForm').getForm().findField('EvnUslugaWOW_setDate');
						var usluga_date = usluga_date_field.getValue(); 
						if (!usluga_date)
						{
							sw.swMsg.alert(lang['oshibka'], lang['vyi_ne_ukazali_datu_vyipolneniya_uslugi'], function() { usluga_date_field.focus(); } );
							return false;
						}
						if (event.combo.Usluga_date != Ext.util.Format.date(usluga_date, 'd.m.Y'))
							event.combo.setFilterActualUsluga(usluga_date, null);
						return true;
					}.createDelegate(this)
				},*/
				xtype: 'swuslugacombo'
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
				{ name: 'EvnUslugaWOW_id' },
				{ name: 'EvnPLWOW_id' },
				{ name: 'EvnUslugaWOW_setDate' },
				{ name: 'EvnUslugaWOW_didDate' },
				{ name: 'DispWowUslugaType_id' },
				{ name: 'LpuSection_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'Usluga_id' }
			]),
			timeout: 600,
			url: '/?c=EvnPLWOW&m=save&method=saveEvnUslugaWOW'
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
				id: 'euwewButtonSave',
				tabIndex: this.firstTabIndex+7,
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
				id: 'euwewButtonCancel',
				tabIndex: this.firstTabIndex+8,
				text: BTN_FRMCANCEL
			}],
			items: [form.PersonFrame, form.EditForm]
		});
		sw.Promed.swEvnUslugaWOWEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});