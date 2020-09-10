/**
* swMedPersonalEditWindow - окно редактирования/добавления медперсонала.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      09.03.2010
* @comment      Префикс для id компонентов MPEW (MedPersonalEditWindow)
*/

sw.Promed.swMedPersonalEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	checkPersonSnils: function()
	{
		var snils = String(this.findById('MedPersonalEditForm').getForm().findField('Person_Snils').getValue());
		if (snils == '')
			return true;
		else
		{
			var reg = /^\d{11}$/;
			if ( !reg.test(snils) )
				return false;
			var psk = snils.substring(9);
			var ps = snils.substring(0, 9);
			var arr = new Array();
			var z = 9;
			var sum = 0;
			for (i = 0; i < 9; i++)
			{
				arr[i] = ps.substr(i, 1);
				sum += arr[i]*z;
				z--;
			}
			while (sum > 101)
			{
				sum = sum % 101;
			}
			if (((sum < 100) && (sum != psk)) || (((sum == 100) || (sum == 101)) && (psk != '00')))
			{
				return false;
			}
			return true;
		}
	},
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	id: 'MedPersonalEditWindow',
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
	checkDoctorDouble: function(form)
	{
		var current_window = this;
		var post = {};
		post.Person_SurName = form.findField('Person_SurName').getValue();
		post.Person_FirName = form.findField('Person_FirName').getValue();
		post.Person_SecName = form.findField('Person_SecName').getValue();
		post.Person_BirthDay = form.findField('Person_BirthDay').getValue().format('d.m.Y');
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет поиск двойников..." });
		loadMask.show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.found)
					{
						sw.swMsg.alert(
							lang['kontrol_na_vvod_vrachey-dvoynikov'],
							lang['v_baze_dannyih_nayden_vrach_s_takimi_je_fio_i_datoy_rojdeniya_identifikator_vracha'] + response_obj.MedPersonal_id,
							function() {
								form.findField('Person_SurName').focus(true);
							}
						);
						return;
					}
					else
					{
						current_window.submit();
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_poiske_dvoynikov_proizoshli_oshibki']);
				}
			},
			params: post,
			url: '/?c=MedPersonal&m=searchDoctorByFioBirthday'
		});
	},
	doSave: function() 
	{
		var form = this.findById('MedPersonalEditForm');
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
		if (!this.checkPersonSnils())
		{
			sw.swMsg.alert(
				lang['proverka_polya_snils'],
				lang['snils_vveden_neverno_ne_udovletvoryaet_pravilam_formirovaniya_snils'],
				function() {
					form.getForm().findField('Person_Snils').focus(true);
				}
			);
			return;
		}
		if ( (form.getForm().findField('WorkData_endDate').getValue() != '') && (form.getForm().findField('WorkData_endDate').getValue() < form.getForm().findField('WorkData_begDate').getValue()) )
		{
			sw.swMsg.alert(lang['oshibka'], lang['data_okonchaniya_rabotyi_doljna_byit_ne_pozje_datyi_nachala_rabotyi'], function() {
				form.getForm().findField('WorkData_endDate').focus();
			});
			return;
		}
		if ( form.getForm().findField('WorkData_begDate').getValue() < form.getForm().findField('Person_BirthDay').getValue() )
		{
			sw.swMsg.alert(lang['oshibka'], lang['data_rojdeniya_doljna_byit_ne_pozje_datyi_nachala_rabotyi'], function() {
				form.getForm().findField('Person_BirthDay').focus();
			});
			return;
		}
		/* по ФИО и дате рождения */
		if (this.action == 'add')
		{
			this.checkDoctorDouble(form.getForm());
		}
		else
		{
			this.submit();
		}
		return true;
	},
	submit: function() 
	{
		var form = this.findById('MedPersonalEditForm');
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
					if (action.result.MedPersonal_id)
					{
						current_window.hide();
						Ext.getCmp('MedPersonalEditWindow').callback({
							MedPersonal_id: action.result.MedPersonal_id,
							MedPersonal_Code: form.getForm().findField('MedPersonal_Code').getValue(),
							MedPersonal_TabCode: form.getForm().findField('MedPersonal_TabCode').getValue(),
							Person_SurName: form.getForm().findField('Person_SurName').getValue(),
							Person_FirName: form.getForm().findField('Person_FirName').getValue(),
							Person_SecName: form.getForm().findField('Person_SecName').getValue(),
							Person_BirthDay: form.getForm().findField('Person_BirthDay').getValue(),
							WorkData_begDate: form.getForm().findField('WorkData_begDate').getValue(),
							WorkData_endDate: form.getForm().findField('WorkData_endDate').getValue(),
							WorkData_IsDlo: (form.getForm().findField('WorkData_IsDlo').getValue() == 2 ) ? 'true' : 'false',
							Person_Snils: form.getForm().findField('Person_Snils').getValue()
						});
						
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								current_window.hide();
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
		var form = this;
		if (enable) 
		{
			var form = this.findById('MedPersonalEditForm');
			form.getForm().findField('MedPersonal_Code').enable();
			form.getForm().findField('MedPersonal_TabCode').enable();
			form.getForm().findField('Person_SurName').enable();
			form.getForm().findField('Person_FirName').enable();
			form.getForm().findField('Person_SecName').enable();
			form.getForm().findField('Person_BirthDay').enable();
			form.getForm().findField('WorkData_begDate').enable();
			form.getForm().findField('WorkData_endDate').enable();
			form.getForm().findField('WorkData_IsDlo').enable();
			form.getForm().findField('Person_Snils').enable();			
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById('MedPersonalEditForm');
			form.getForm().findField('MedPersonal_Code').disable();
			form.getForm().findField('MedPersonal_TabCode').disable();
			form.getForm().findField('Person_SurName').disable();
			form.getForm().findField('Person_FirName').disable();
			form.getForm().findField('Person_SecName').disable();
			form.getForm().findField('Person_BirthDay').disable();
			form.getForm().findField('WorkData_begDate').disable();
			form.getForm().findField('WorkData_endDate').disable();
			form.getForm().findField('WorkData_IsDlo').disable();
			form.getForm().findField('Person_Snils').disable();			
			this.buttons[0].disable();			
		}
	},
	show: function() 
	{
		sw.Promed.swMedPersonalEditWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					current_window.hide();
				}
			});
		}
		this.focus();
		this.findById('MedPersonalEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].MedPersonal_id) 
			this.MedPersonal_id = arguments[0].MedPersonal_id;
		else 
			this.MedPersonal_id = null;
			
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
			if ( ( this.MedPersonal_id ) && ( this.MedPersonal_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('MedPersonalEditForm');
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(WND_ADMIN_MPADD);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(WND_ADMIN_MPEDIT);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(WND_ADMIN_MPVIEW);
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					MedPersonal_id: current_window.MedPersonal_id
				},
				failure: function() 
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
					loadMask.hide();
					if (current_window.action=='edit')
					{
						//current_window.findById('regeRegistry_begDate').focus(true, 50);
					}
					else 
						current_window.buttons[3].focus();
				},
				url: '/?c=MedPersonal&m=loadMedPersonal'
			});
		}
		if ( this.action != 'view' )
			form.getForm().findField('MedPersonal_Code').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.medPersonalEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'MedPersonalEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				id: 'MPEW_MedPersonal_id',
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				enableKeyEvents: true,
				xtype: 'textfield',
				maskRe: /\d/,
				fieldLabel: lang['kod_vracha'],
				autoCreate: {tag: "input", type: "text", size: "7", maxLength: "7", autocomplete: "off"},
				width: 180,
				name: 'MedPersonal_Code',
				tabIndex: TABINDEX_MPEW + 12,
				id: 'MPEW_MedPersonal_Code',
				listeners: {
					'keydown': function (inp, e) {
						if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
						{
							e.stopEvent();
							Ext.getCmp('MPEW_MedPersonal_TabCode').focus();
						}
					}
				}
			}, {
				enableKeyEvents: true,
				xtype: 'textfield',
				maskRe: /\d/,
				fieldLabel: lang['tabelnyiy_kod_vracha'],
				autoCreate: {tag: "input", type: "text", size: "7", maxLength: "7", autocomplete: "off"},
				width: 180,
				name: 'MedPersonal_TabCode',
				tabIndex: TABINDEX_MPEW + 1,
				id: 'MPEW_MedPersonal_TabCode',
				listeners: {
					'keydown': function (inp, e) {
						if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
						{
							e.stopEvent();
							Ext.getCmp('MPEW_MedPersonal_Code').focus();
						}
					}
				}
			}, {
				allowBlank: false,
				xtype: 'textfieldpmw',
				fieldLabel: lang['familiya'],
				toUpperCase: true,
				width: 180,
				id: 'MPEW_Person_SurName',
				name: 'Person_SurName',
				tabIndex: TABINDEX_MPEW + 2
			}, {
				allowBlank: false,
				xtype: 'textfieldpmw',
				fieldLabel: lang['imya'],
				toUpperCase: true,
				width: 180,
				name: 'Person_FirName',
				id: 'MPSW_Person_FirName',
				tabIndex: TABINDEX_MPEW + 3
			}, {
				xtype: 'textfieldpmw',
				fieldLabel: lang['otchestvo'],
				toUpperCase: true,
				width: 180,
				name: 'Person_SecName',
				tabIndex: TABINDEX_MPEW + 4
			}, {
				allowBlank: false,
				fieldLabel: lang['data_rojdeniya'],
				format: 'd.m.Y',
				id: 'MPEW_Person_BirthDay',
				name: 'Person_BirthDay',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield',
				tabIndex: TABINDEX_MPEW + 5
			}, {
				allowBlank: false,
				fieldLabel: lang['nachalo_rabotyi'],
				format: 'd.m.Y',
				id: 'MPEW_WorkData_begDate',
				name: 'WorkData_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield',
				tabIndex: TABINDEX_MPEW + 6
			}, {
				fieldLabel: lang['okonchanie_rabotyi'],
				format: 'd.m.Y',
				id: 'MPEW_WorkData_endDate',
				name: 'WorkData_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield',
				tabIndex: TABINDEX_MPEW + 7
			}, {
				fieldLabel: lang['vrach_llo'],
				hiddenName: 'WorkData_IsDlo',
				id: 'MPEW_WorkData_IsDlo',
				width: 100,
				xtype: 'swyesnocombo',
				tabIndex: TABINDEX_MPEW + 8
			}, {
				xtype: 'textfield',
				maskRe: /\d/,
				fieldLabel: lang['snils'],
				maxLength: 11,
				minLength: 11,
				autoCreate: {tag: "input", type: "text", size: "11", maxLength: "11", autocomplete: "off"},
				width: 180,
				name: 'Person_Snils',
				tabIndex: TABINDEX_MPEW + 9
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
				{ name: 'MedPersonal_id' },
				{ name: 'MedPersonal_Code' },
				{ name: 'MedPersonal_TabCode' },
				{ name: 'Person_SurName' },
				{ name: 'Person_FirName' },
				{ name: 'Person_SecName' },
				{ name: 'Person_BirthDay' },
				{ name: 'WorkData_begDate' },
				{ name: 'WorkData_endDate' },
				{ name: 'WorkData_IsDlo' },
				{ name: 'Person_Snils' }
			]),
			url: '/?c=MedPersonal&m=saveMedPersonal'
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
				tabIndex: TABINDEX_MPEW + 10,
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
				tabIndex: TABINDEX_MPEW + 11,
				text: BTN_FRMCANCEL
			}],
			items: [this.medPersonalEditForm]
		});
		sw.Promed.swMedPersonalEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});