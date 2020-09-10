/**
* swEvnPLWOWEditWindow - форма ввода талона углубленного обследования ВОВ.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      14.03.2010
* @comment      Префикс для id компонентов ppwows (PersonPrivilegeWOWSearchWindow)
                TABINDEX_EPWEW: 8300 
*
* @input data: 
               
               
*/

sw.Promed.EvnPLWOWEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['uglublennoe_obsledovanie_vov'],
	layout: 'border',
	id: 'EvnPLWOWEditWindow',
	maximized: true,
	maximizable: false,
	shim: false,
	buttons:
	[
		{
			text: BTN_FRMSAVE,
			id: 'eplwoweButtonSave',
			tabIndex: TABINDEX_EPWEW+84,
			onTabElement: 'eplwoweButtonCancel',
			onShiftTabElement: 'eplwoweEvnPLWOW_UKL',
			tooltip: lang['sohranit'],
			iconCls: 'save16',
			handler: function()
			{
				this.ownerCt.doSave(true);
			}
		},
		{
			text: '-'
		},
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text: BTN_FRMCLOSE,
			id: 'eplwoweButtonCancel',
			tabIndex: TABINDEX_EPWEW+80,
			tooltip: lang['zakryit'],
			iconCls: 'cancel16',
			handler: function()
			{
				this.ownerCt.returnFunc(this.ownerCt.owner, -1);
			}
		}
	],
	returnFunc: function(owner) {},
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	/* +lang['podgruzka']+ +lang['spravochnikov']+ */
	loadSprData: function()
	{
		frm = this;
	},
	setMode: function()
	{
		if (this.action=='view')
		{
			this.findById('eplwoweEvnPLWOW_IsFinish').disable();
			this.findById('eplwoweResultClass_id').disable();
			this.findById('eplwoweEvnPLWOW_UKL').disable();
			this.VizitGrid.setReadOnly(true);
			this.UslugaGrid.setReadOnly(true);
		}
		else 
		{
			this.findById('eplwoweEvnPLWOW_IsFinish').enable();
			this.findById('eplwoweResultClass_id').enable();
			this.findById('eplwoweEvnPLWOW_UKL').enable();
			this.VizitGrid.setReadOnly(false);
			this.UslugaGrid.setReadOnly(false);
		}
		
		// Заголовок формы
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['uglublennoe_obsledovanie_vov_dobavlenie']);
				break;
			case 'edit':
				this.setTitle(lang['uglublennoe_obsledovanie_vov_redaktirovanie']);
				break;
			case 'view':
				this.setTitle(lang['uglublennoe_obsledovanie_vov_prosmotr']);
				break;
		}
	},
	getValidDispWowSpec: function (gridpanel)
	{
		if (gridpanel.getCount()>0)
		{
			var mass = new Object();
			mass = {'1':1}; // Тут можно наименования осмотров
			gridpanel.getGrid().getStore().each(function(record)
			{
				if (record.get('DispWOWSpec_id').inlist(mass))
				{
					delete mass[record.get('DispWOWSpec_id').toString()];
				}
			});
			return (count(mass)==0);
		}
		else 
		{
			return false;
		}
	},
	getValidUslugaType: function (gridpanel)
	{
		if (gridpanel.getCount()>0)
		{
			var mass = new Object();
			mass = {'1':1,'2':2,'13':13};

			gridpanel.getGrid().getStore().each(function(record) 
			{
				if (record.get('DispWowUslugaType_id').inlist(mass))
				{
					delete mass[record.get('DispWowUslugaType_id').toString()];
				}
			});

			return (count(mass)==0);
		}
		else 
		{
			return false;
		}
	},
	doSave: function(flag) 
	{
		var form = this.FieldsPanel;
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

		// Если случай закончен
		if (form.findById('eplwoweEvnPLWOW_IsFinish').getValue() == 2)
		{
			// Проверка на заполненность осмотров
			if (!this.getValidDispWowSpec(this.VizitGrid))
			{
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function() 
					{
						form.findById('eplwoweEvnPLWOW_IsFinish').setValue(1);
						form.findById('eplwoweEvnPLWOW_IsFinish').fireEvent('change', form.findById('eplwoweEvnPLWOW_IsFinish'), 1, 2);
						form.ownerCt.VizitGrid.focus(true);
					},
					icon: Ext.Msg.ERROR,
					title: lang['oshibka'],
					msg: lang['ne_vvedenyi_odin_ili_bolee_obyazatelnyih_osmotrov_sluchay_ne_mojet_byit_zakonchen']
				});
				return false;
			}
			// Проверка на заполненность заполненности набора исследований  
			if (!this.getValidUslugaType(this.UslugaGrid))
			{
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function() 
					{
						form.findById('eplwoweEvnPLWOW_IsFinish').setValue(1);
						form.findById('eplwoweEvnPLWOW_IsFinish').fireEvent('change', form.findById('eplwoweEvnPLWOW_IsFinish'), 1, 2);
						form.ownerCt.UslugaGrid.focus(true);
					},
					icon: Ext.Msg.ERROR,
					title: lang['oshibka'],
					msg: lang['ne_vvedenyi_odno_ili_bolee_issledovaniy_sluchay_ne_mojet_byit_zakonchen']
				});
				return false;
			}
					//В соответствие с задачей 10729 необходимо проверить, заведен ли осмотр необходимых врачей при наличии определенных обследований
					var grid_Vizit= this.VizitGrid;
					var grid_Usluga = this.UslugaGrid;
					var mass_Usluga = new Object();
					var mass_Vizit = new Object();
					mass_Usluga = {'15':15,'16':16};
					mass_Vizit = {'5':5};

					grid_Usluga.getGrid().getStore().each(function(record)
					{
						if (record.get('DispWowUslugaType_id').inlist(mass_Usluga))
						{
							delete mass_Usluga[record.get('DispWowUslugaType_id').toString()];
						}
					});
					if (count(mass_Usluga) < 2)
					{
						grid_Vizit.getGrid().getStore().each(function(record)
						{
							if ((typeof record == 'object') && (record.get('DispWOWSpec_id').inlist(mass_Vizit)))
							{
								delete mass_Vizit[record.get('DispWOWSpec_id').toString()];
							}
						});
						if (count(mass_Vizit)>0)
						{
							alert(lang['pri_vvode_opredeleniya_ostrotyi_zreniya_02270401_ili_oftalmoskopii_glaznogo_dna_02270302_neobhodim_osmotr_oftalmologa']);
							return false;
						}
					}

			if (flag==true) 
				form.ownerCt.submit();
		}
		else 
		{
			if ((this.getValidDispWowSpec(this.VizitGrid)) && (this.getValidUslugaType(this.UslugaGrid)))
			{
				sw.swMsg.show(
				{
					buttons: Ext.Msg.YESNO,
					fn: function(btn) 
					{
						if (btn == 'yes')
						{
							form.findById('eplwoweEvnPLWOW_IsFinish').setValue(2);
							if ((form.findById('eplwoweEvnPLWOW_UKL').getValue()>0) && (form.findById('eplwoweResultClass_id').getValue()>0))
							{
								if (flag==true) 
									form.ownerCt.submit();
								return true;
							}
							else 
							{
								sw.swMsg.show(
								{
									buttons: Ext.Msg.OK,
									fn: function() 
									{
										form.findById('eplwoweResultClass_id').focus();
									},
									icon: Ext.Msg.WARNING,
									title: lang['vnimanie'],
									msg: lang['zapolnite_obyazatelnyie_polya_zakonchennosti_sluchaya']
								});
								return false;
							}
						}
						else 
						{
							if (flag==true)
								form.ownerCt.submit();
						}
					},
					icon: Ext.Msg.QUESTION,
					title: lang['vopros'],
					msg: lang['v_karte_zapolnenyi_vse_neobhodimyie_dannyie_prostavit_priznak_zakonchennosti_sluchaya']
				});
			}
			else 
			{
				if (flag==true) 
					form.ownerCt.submit();
			}
		}
		return true;
	},
	submit: function(mode, type, onlySave) 
	{
		var form = this.FieldsPanel;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit(
		{
			params: 
			{
				save:onlySave
			},
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Message)
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
					if (action.result.EvnPLWOW_id)
					{
						if (!onlySave || (onlySave!==1))
						{
							//log('Вызов returnFunc');
							//log(win.owner);
							win.returnFunc(win.owner, action.result.EvnPLWOW_id);
						}
						else
						{
							new Ext.ux.window.MessageWindow(
							{
								title: lang['sohranenie'],
								autoHeight: true,
								help: false,
								bodyStyle: 'text-align:center',
								closable: true,
								hideFx:
								{
									delay: 3000,
									mode: 'standard',
									useProxy: false
								},
								html: lang['obratite_vnimanie_talon_uglublennogo_obsledovaniya_sohranen'],
								iconCls: 'info16',
								width: 250
							}).show(Ext.getDoc());
							
							if (!form.findById('eplwoweEvnPLWOW_id').getValue())
							{
								form.findById('eplwoweEvnPLWOW_id').setValue(action.result.EvnPLWOW_id);
								//log(action.result.EvnPLWOW_id);
								win['VizitGrid'].setParam('EvnPLWOW_id', form.findById('eplwoweEvnPLWOW_id').getValue(), false);
								win['VizitGrid'].setParam('EvnPLWOW_id', form.findById('eplwoweEvnPLWOW_id').getValue());
								win['UslugaGrid'].setParam('EvnPLWOW_id', form.findById('eplwoweEvnPLWOW_id').getValue(), false);
								win['UslugaGrid'].setParam('EvnPLWOW_id', form.findById('eplwoweEvnPLWOW_id').getValue());
								if (mode=='add')
								{
									win[type+'Grid'].run_function_add = false;
									win[type+'Grid'].runAction('action_add');
								}
							}
							else
							{
								if (mode=='add')
								{
									win[type+'Grid'].run_function_add = false;
									win[type+'Grid'].runAction('action_add');
								}
								else if (mode=='edit')
								{
									win[type+'Grid'].run_function_edit = false;
									win[type+'Grid'].runAction('action_edit');
								}
							}
						}
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
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_pozdnee'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	clearValues: function ()
	{
		// Обнуление данных
		/*
		this.findById('eplwoweEvnPLWOW_id').setValue('');
		this.findById('eplwowePersonEvn_id').setValue('');
		this.findById('eplwoweServer_id').setValue('');
		this.findById('eplwowePerson_id').setValue('');
		*/
		this.FieldsPanel.getForm().reset();
		this.findById('eplwoweEvnPLWOW_IsFinish').setValue(1);
		this.findById('eplwoweResultClass_id').setAllowBlank(true);
		this.findById('eplwoweEvnPLWOW_UKL').setAllowBlank(true);
	},
	callbackEvnVizit: function(data)
	{
		if ( !data || !data.evnVizitPLData ) 
		{
			return false;
		}
		var dt = new Object();
		var grid = this.VizitGrid.getGrid();
		dt.EvnVizitPLWOW_id = data.evnVizitPLData[0].EvnVizitPL_id;
		dt.EvnVizitPLWOW_pid = data.evnVizitPLData[0].EvnPL_id;
		/*
		dt.Person_id = data.evnVizitPLData[0].Person_id;
		dt.PersonEvn_id = data.evnVizitPLData[0].PersonEvn_id;
		dt.Server_id = data.evnVizitPLData[0].Server_id;
		*/
		dt.Diag_id = data.evnVizitPLData[0].Diag_id;
		dt.Diag_Code = data.evnVizitPLData[0].Diag_Code;
		dt.LpuSection_id = data.evnVizitPLData[0].LpuSection_id;
		dt.LpuSection_Name = data.evnVizitPLData[0].LpuSection_Name;
		dt.MedPersonal_id = data.evnVizitPLData[0].MedPersonal_id;
		dt.MedPersonal_FIO = data.evnVizitPLData[0].MedPersonal_Fio;
		dt.EvnVizitPLWOW_setDate = data.evnVizitPLData[0].EvnVizitPL_setDate;
		dt.DispWOWSpec_id = data.evnVizitPLData[0].DispWOWSpec_id;
		dt.DispWOWSpec_Name = data.evnVizitPLData[0].DispWOWSpec_Name;
		var record = grid.getStore().getById(dt.EvnVizitPLWOW_id);
		if ( !record ) 
		{
			if (this.VizitGrid.getCount()==0) 
			{
				grid.getStore().removeAll();
			}
			setGridRecord(grid,dt,'add');
		}
		else 
		{
			var evn_vizit_fields = new Array();
			grid.getStore().fields.eachKey(function(key, item) 
			{
				evn_vizit_fields.push(key);
			});

			for ( i = 0; i < evn_vizit_fields.length; i++ ) 
			{
				record.set(evn_vizit_fields[i], dt[evn_vizit_fields[i]]);
			}
			record.commit();
		}
	},
	loadRecord: function ()
	{
		var form = this;
		if (form.action!='add')
		{
			form.findById('EvnPLWOWFieldsPanel').getForm().load(
			{
				params: 
				{
					EvnPLWOW_id: form.EvnPLWOW_id
				},
				failure: function() 
				{
					form.loadMask.hide();
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
					form.loadMask.hide();
					// Подгружаем данные, если они есть 
					// Если читаем, то сервер_ид подгружаем 
					form.VizitGrid.setParam('EvnPLWOW_id', form.EvnPLWOW_id);
					form.VizitGrid.setParam('EvnPLWOW_id', form.EvnPLWOW_id, false);
					form.VizitGrid.setParam('Server_id', form.Server_id, false);
					form.VizitGrid.setParam('FormType', 'EvnVizitPLWow', false);
					form.VizitGrid.loadData();
					form.UslugaGrid.setParam('EvnPLWOW_id', form.EvnPLWOW_id);
					form.UslugaGrid.setParam('Server_id', form.Server_id, false);
					form.UslugaGrid.setParam('EvnPLWOW_id', form.EvnPLWOW_id, false);
					form.UslugaGrid.loadData({noFocusOnLoad:true});
					
				},
				url: '/?c=EvnPLWOW&m=loadEvnPLWOWEditForm'
			});
		}
		else 
		{
			form.VizitGrid.focus();
			form.loadMask.hide();
		}
	},
	show: function()
	{
		sw.Promed.EvnPLWOWEditWindow.superclass.show.apply(this, arguments);
		
		var form = this;
		this.loadMask = new Ext.LoadMask(Ext.get('EvnPLWOWEditWindow'), {msg: LOAD_WAIT});
		this.loadMask.show();
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		
		if (arguments[0].Person_id)
			this.Person_id = arguments[0].Person_id;
		else
			this.Person_id = null;
		
		if (arguments[0].EvnPLWOW_id)
			this.EvnPLWOW_id = arguments[0].EvnPLWOW_id;
		else
			this.EvnPLWOW_id = null;
		
		if (arguments[0].PersonEvn_id)
			this.PersonEvn_id = arguments[0].PersonEvn_id;
		else
			this.PersonEvn_id = null;
		if (arguments[0].Server_id != undefined)
			this.Server_id = arguments[0].Server_id;
		else
			this.Server_id = null;
		
		if (!this.action || (this.action=='')) 
			this.action = 'add';

		//Проверяем возможность редактирования документа
		if (this.action === 'edit' && getRegionNick() === 'kareliya' && this.EvnPLWOW_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: this.EvnPLWOW_id,
					MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
					ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
				},
				success: function (response, options) {
					if (!Ext.isEmpty(response.responseText)) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if (response_obj.success == false) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_zagruzke_dannyih_formyi']);
							this.action = 'view';
						}
					}

					//вынес продолжение show в отдельную функцию, т.к. иногда callback приходит после выполнения логики
					this.onShow();
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		} else {
			this.onShow();
		}	
	},
	
	onShow: function(){
		var form = this;
		// Очистка всех полей 
		form.clearValues();
		form.VizitGrid.removeAll({clearAll:true});
		form.UslugaGrid.removeAll({clearAll:true});
		form.FieldsPanel.getForm().setValues(arguments[0]);
		form.VizitGrid.setParam('FormType', 'EvnVizitPLWow', false);
		form.PersonFrame.load(
		{
			Person_id: form.Person_id, 
			Server_id: form.Server_id, 
			callback: function() 
			{
				form.VizitGrid.setParam('Person_id', form.PersonFrame.getFieldValue('Person_id'), false);
				form.VizitGrid.setParam('Person_Birthday', form.PersonFrame.getFieldValue('Person_Birthday'), false);
				form.VizitGrid.setParam('Person_Firname', form.PersonFrame.getFieldValue('Person_Firname'), false);
				form.VizitGrid.setParam('Person_Secname', form.PersonFrame.getFieldValue('Person_Secname'), false);
				form.VizitGrid.setParam('Person_Surname', form.PersonFrame.getFieldValue('Person_Surname'), false);
				form.VizitGrid.setParam('Person_id', form.Person_id, false);
				form.VizitGrid.setParam('PersonEvn_id', form.PersonFrame.getFieldValue('PersonEvn_id'), false);
				form.VizitGrid.setParam('Server_id', form.Server_id, false);
				form.VizitGrid.setParam('callback', function (data) {form.callbackEvnVizit(data);}.createDelegate(this), false);
				form.VizitGrid.setParam('onHide', function (data) {form.VizitGrid.focus();}.createDelegate(this), false);
				form.VizitGrid.setParam('Sex_id', form.PersonFrame.getFieldValue('Sex_id'), false);
				
				form.UslugaGrid.setParam('Person_id', form.PersonFrame.getFieldValue('Person_id'), false);
				form.UslugaGrid.setParam('Person_Birthday', form.PersonFrame.getFieldValue('Person_Birthday'), false);
				form.UslugaGrid.setParam('Person_Firname', form.PersonFrame.getFieldValue('Person_Firname'), false);
				form.UslugaGrid.setParam('Person_Secname', form.PersonFrame.getFieldValue('Person_Secname'), false);
				form.UslugaGrid.setParam('Person_Surname', form.PersonFrame.getFieldValue('Person_Surname'), false);
				form.UslugaGrid.setParam('Sex_id', form.PersonFrame.getFieldValue('Sex_id'), false);
				form.UslugaGrid.setParam('Person_id', form.Person_id, false);
				form.UslugaGrid.setParam('PersonEvn_id', form.PersonFrame.getFieldValue('PersonEvn_id'), false);
				form.UslugaGrid.setParam('Server_id', form.Server_id, false);
				// Включаем или выключаем доступ к редактированию
				form.setMode();
				// Читаем все пришедшие поля
				// Читаем данные
				form.loadRecord();
			}.createDelegate(this)
		});
	},
	
	beforeshow: function(){
		console.log('beforeshow');
		console.log(arguments[0]);
	},

	initComponent: function()
	{
		var form = this;
		
		this.PersonFrame = new sw.Promed.PersonInformationPanel(
		{
			button2Callback: function(callback_data) 
			{
				var win = Ext.getCmp('EvnPLWOWEditWindow');
				win.findById('eplwowePersonEvn_id').setValue(callback_data.PersonEvn_id);
				win.findById('eplwoweServer_id').setValue(callback_data.Server_id);
				win.findById('EvnPLWOWPersonInformationFrame').load({Person_id: callback_data.Person_id, Server_id: callback_data.Server_id});
			},
			button2OnHide: function() 
			{
				var win = Ext.getCmp('EvnPLWOWEditWindow');
				win.VizitGrid.focus();
			},
			button3OnHide: function() 
			{
				var win = Ext.getCmp('EvnPLWOWEditWindow');
				win.VizitGrid.focus();
			},
			id: 'EvnPLWOWPersonInformationFrame',
			region: 'north'
		})

		this.VizitRecordAdd = function()
		{
			if (this.findById('eplwoweEvnPLWOW_id').getValue()==0)
			{
				if (this.doSave())
				{
					this.submit('add', 'Vizit', 1);
				}
				return false;
			}
			else 
			{
				this.VizitGrid.run_function_add = false;
				this.VizitGrid.runAction('action_add');
			}
		}
		
		// Посещения 
		this.VizitGrid = new sw.Promed.ViewFrame(
		{
			title:lang['osmotr_vracha-spetsialista'],
			id: 'EvnPLWOWVizitGrid',
			region: 'north',
			height: 250,
			minSize: 200,
			object: 'EvnVizitPLWOW',
			editformclassname: 'swEvnVizitPLEditWindow',
			dataUrl: '/?c=EvnPLWOW&m=loadEvnVizitPLWOW',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'EvnVizitPLWOW_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnVizitPLWOW_pid', hidden: true, isparams: true},
				{name: 'EvnVizitPLWOW_setDate', type: 'date', header: lang['data_osmotra'], width: 80},
				{name: 'DispWOWSpec_id', hidden: true, isparams: true},
				{id: 'autoexpand', name: 'DispWOWSpec_Name', width: 140, header: lang['spetsialnost']},
				{name: 'LpuSection_id', hidden: true, isparams: true},
				{name: 'LpuSection_Name', width: 200, header: lang['otdelenie']},
				{name: 'MedPersonal_id', hidden: true, isparams: true},
				{name: 'MedPersonal_FIO', width: 240, header: lang['vrach']},
				{name: 'Diag_id', hidden: true, isparams: true},
				{name: 'Diag_Code', width: 80, header: lang['diagnoz']}
			],
			actions:
			[
				{name:'action_add', func: form.VizitRecordAdd.createDelegate(this)}, 
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'}
			],
			onLoadData: function()
			{
				var win = Ext.getCmp('EvnPLWOWEditWindow');
			},
			onRowSelect: function (sm,index,record)
			{
				var win = Ext.getCmp('EvnPLWOWEditWindow');
			},
			focusOn: {name:'EvnUslugaWOWGrid',type:'grid'},
			focusPrev: {name:'eplwoweButtonCancel',type:'field'}
		});
		
		this.UslugaRecordAdd = function()
		{
			if (this.findById('eplwoweEvnPLWOW_id').getValue()==0)
			{
				if (this.doSave())
				{
					this.submit('add', 'Usluga', 1);
				}
				return false;
			}
			else 
			{
				this.UslugaGrid.run_function_add = false;
				this.UslugaGrid.runAction('action_add');
			}
		}
		
		// Обследования 
		this.UslugaGrid = new sw.Promed.ViewFrame(
		{
			title:lang['laboratornyie_issledovaniya'],
			id: 'EvnUslugaWOWGrid',
			region: 'center',
			height: 200,
			minSize: 200,
			object: 'EvnUslugaWOW',
			editformclassname: 'swEvnUslugaWOWEditWindow',
			dataUrl: '/?c=EvnPLWOW&m=loadEvnUslugaWOW',
			toolbar: true,
			autoLoadData: false,
			/*
			saveAtOnce: false,
			saveAllParams: true,
			*/
			stringfields:
			[
				{name: 'EvnUslugaWOW_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnUslugaWOW_pid', hidden: true, isparams: true},
				{name: 'EvnUslugaWOW_setDate', type: 'date', header: lang['rezultat'], width: 80},
				{name: 'DispWowUslugaType_id', hidden: true, isparams: true},
				{id: 'autoexpand', name: 'DispWowUslugaType_Name', width: 140, header: lang['vid_obsledovaniya']},
				{name: 'LpuSection_id', hidden: true, isparams: true},
				{name: 'LpuSection_Name', width: 200, header: lang['otdelenie']},
				{name: 'MedPersonal_id', hidden: true, isparams: true},
				{name: 'MedPersonal_Fio', width: 220, header: lang['vrach']},
				{name: 'Usluga_id', hidden: true, isparams: true},
				{name: 'Usluga_Code', width: 80, header: lang['kod']},
				{name: 'Usluga_Name', width: 260, header: lang['obsledovanie']}
			],
			actions:
			[
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'}
			],
			focusPrev: {name:'EvnPLWOWVizitGrid',type:'grid'},
			focusOn: {name:'eplwoweEvnPLWOW_IsFinish',type:'field'},
			onLoadData: function (result)
			{
				var win = Ext.getCmp('EvnPLWOWEditWindow');
			},
			onRowSelect: function (sm,index,record)
			{
				var win = Ext.getCmp('EvnPLWOWEditWindow');
			}
		});
		
		// Панелька филдов
		this.FieldsPanel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			region: 'south',
			border: true,
			buttonAlign: 'left',
			frame: true,
			id: 'EvnPLWOWFieldsPanel',
			labelAlign: 'right',
			labelWidth: 130,
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			items: 
			[{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: 0.9,
				labelWidth: 130,
				items: 
				[{
					id: 'eplwoweEvnPLWOW_id',
					name: 'EvnPLWOW_id',
					xtype: 'hidden',
					value: null
				},
				{
					id: 'eplwowePersonEvn_id',
					name: 'PersonEvn_id',
					xtype: 'hidden',
					value: null
				},
				{
					id: 'eplwoweServer_id',
					name: 'Server_id',
					xtype: 'hidden',
					value: null
				},
				{
					id: 'eplwowePerson_id',
					name: 'Person_id',
					xtype: 'hidden',
					value: null
				},
				{
					allowBlank: false,
					enableKeyEvents: true,
					fieldLabel: lang['sluchay_zakonchen'],
					id: 'eplwoweEvnPLWOW_IsFinish',
					hiddenName: 'EvnPLWOW_IsFinish',
					listeners: 
					{
						'change': function(combo, newValue, oldValue) 
						{
							if (newValue == 2)
							{
								combo.ownerCt.findById('eplwoweResultClass_id').setAllowBlank(false);
								combo.ownerCt.findById('eplwoweEvnPLWOW_UKL').setAllowBlank(false);
								if (combo.ownerCt.findById('eplwoweEvnPLWOW_UKL').getValue()=='')
									combo.ownerCt.findById('eplwoweEvnPLWOW_UKL').setValue(1);
								if (combo.ownerCt.findById('eplwoweResultClass_id').getValue()=='')
									combo.ownerCt.findById('eplwoweResultClass_id').setValue(3);
								//combo.ownerCt.findById('eplwoweResultClass_id').setDisabled(false);
								//combo.ownerCt.findById('eplwoweEvnPLWOW_UKL').setDisabled(false);
							}
							else
							{
								combo.ownerCt.findById('eplwoweResultClass_id').setAllowBlank(true);
								combo.ownerCt.findById('eplwoweEvnPLWOW_UKL').setAllowBlank(true);
								/*
								combo.ownerCt.findById('eplwoweResultClass_id').setValue('');
								combo.ownerCt.findById('eplwoweEvnPLWOW_UKL').setValue('');
								*/
								//combo.ownerCt.findById('eplwoweResultClass_id').setDisabled(true);
								//combo.ownerCt.findById('eplwoweEvnPLWOW_UKL').setDisabled(true);
							}
						}
					},
					tabIndex: this.TABINDEX_EPWEW+81,
					width: 70,
					xtype: 'swyesnocombo'
				},
				{
					hiddenName: 'ResultClass_id',
					id: 'eplwoweResultClass_id',
					tabIndex: this.TABINDEX_EPWEW+82,
					width: 300,
					xtype: 'swresultclasscombo'
				},
				{
					allowDecimals: true,
					allowNegative: false,
					fieldLabel: lang['ukl'],
					minValue: 0.1,
					maxValue: 1,
					id: 'eplwoweEvnPLWOW_UKL',
					name: 'EvnPLWOW_UKL',
					tabIndex: this.TABINDEX_EPWEW+83,
					width: 70,
					xtype: 'numberfield'
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .1,
				labelWidth: 10
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
								this.doSave(true);
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
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'Person_id' },
				{ name: 'EvnPLWOW_id' },
				{ name: 'EvnPLWOW_IsFinish' },
				{ name: 'ResultClass_id' },
				{ name: 'EvnPLWOW_UKL' },
				{ name: 'Lpu_id' }
			]),
			url: '/?c=EvnPLWOW&m=save&method=saveEvnPLWOW'
		});
		
		
		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			items:
			[	this.PersonFrame, 
				{
					border: false,
					layout:'border',
					region: 'center',
					defaults: {split: true},
					items: [form.VizitGrid, form.UslugaGrid]
				},
				form.FieldsPanel
			]
		});
		sw.Promed.EvnPLWOWEditWindow.superclass.initComponent.apply(this, arguments);
		this.VizitGrid.addListenersFocusOnFields();
		this.UslugaGrid.addListenersFocusOnFields();
	}

});
