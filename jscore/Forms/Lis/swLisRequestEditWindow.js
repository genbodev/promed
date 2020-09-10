/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Евгений Брагарь
* @version      ноябрь 2011
* @comment      
*
*
* @input        params (object) 
*/

sw.Promed.swLisRequestEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoHeight: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	title: lang['zayavka'],
	split: true,
	width: 900,
	layout: 'form',
	id: 'swLisRequestEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		}
		/*beforeshow: function()
		{
			var frm = this.LisRequestForm.getForm();
			frm.findField('LisRequest_isGenXml').fireEvent('check', frm.findField('LisRequest_isGenXml'), true);
		}*/
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	/* Проверка которая выполняется до сохранения данных
	*/
	doSave: function()
	{
		var win = this;
		var form = this.LisRequestForm.getForm();
		if(!form.isValid())
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya']);
			return false;
		}

		var params = {
			Id: '',
			InternalNr: form.findField('LisRequest_Num').getValue(),
			HospitalCode: getGlobalOptions().lpu_id,
			HospitalName: getGlobalOptions().lpu_nick,
			CustDepartmentCode: form.findField('LpuSection_id').getFieldValue('LpuSection_Code'),
			CustDepartmentName: form.findField('LpuSection_id').getFieldValue('LpuSection_Name'),
			DoctorCode: form.findField('MedPersonal_id').getFieldValue('MedPersonal_Code'),
			DoctorName: form.findField('MedPersonal_id').getFieldValue('MedPersonal_Name'),
			RegistrationFormCode: form.findField('RequestForm_id').getFieldValue('RequestForm_Code'),
			SamplingDate: form.findField('LisRequest_getDate').getValue(),
			SampleDeliveryDate: form.findField('LisRequest_dostDate').getValue(),
			PregnancyDuration: form.findField('LisRequest_PregSrok').getValue(),
			CyclePeriod: form.findField('CyclePeriod_id').getFieldValue('CyclePeriod_Code'),

			Priority: form.findField('Priority_id').getFieldValue('Priority_Code'),
			Code: this.findById('LREW_PersonInformationFrame').getFieldValue('Person_id'),
			//CardNr: form.findField('LisRequest_Num').getValue(),

			FirstName: this.findById('LREW_PersonInformationFrame').getFieldValue('Person_Firname'),
			MiddleName: this.findById('LREW_PersonInformationFrame').getFieldValue('Person_Secname'),
			LastName: this.findById('LREW_PersonInformationFrame').getFieldValue('Person_Surname'),

			BirthDay: Date.parseDate(this.findById('LREW_PersonInformationFrame').getFieldValue('Person_Birthday'), 'd.m.Y'),

			Sex: this.findById('LREW_PersonInformationFrame').getFieldValue('Sex_id'),
			//Country: form.findField('LisRequest_Num').getValue(),
			//City: form.findField('LisRequest_Num').getValue(),
			//Street: form.findField('LisRequest_Num').getValue(),
			//Building: form.findField('LisRequest_Num').getValue(),
			//Flat: form.findField('LisRequest_Num').getValue(),
			InsuranceCompany: this.findById('LREW_PersonInformationFrame').getFieldValue('OrgSmo_Name'),
			PolicySeries: this.findById('LREW_PersonInformationFrame').getFieldValue('Polis_Ser'),
			PolicyNumber: this.findById('LREW_PersonInformationFrame').getFieldValue('Polis_Num'),
			Biomaterial: form.findField('Biomaterial_id').getFieldValue('Biomaterial_Code'),
			InternalNrBarCode: form.findField('LisRequest_Barcode').getValue,
			Target: form.findField('Target_id').getFieldValue('Target_Code'),
			//Cancel: form.findField('LisRequest_Num').getValue(),
			ReadOnly: form.findField('LisRequest_IsReadOnly').getValue

		};
		form.submit({
			params: params,
			success: function()
			{
				/*
				var p = Ext.apply(params, form.getValues());
				p['id'] = p['code']+'_'+((p['region'])?p['region']:'default');
				win.callback(win.owner, 0, new Ext.data.Record(p), win.action);
				*/
				win.callback(win.owner, 0);
				win.hide();
			}
		});
	},

	/** Функция относительно универсальной загрузки справочников выбор в которых осуществляется при вводе букв (цифр)
	 * Пример загрузки Usluga:
	 * loadSpr('Usluga_id', { where: "where UslugaType_id = 2 and Usluga_id = " + Usluga_id });
	 */
	loadSpr: function(field_name, params, callback)
	{
		var bf = this.LisRequestForm.getForm();
		var combo = bf.findField(field_name);
		var value = combo.getValue();
		combo.getStore().removeAll();
		if (value != null && value.toString().length > 0)
		{
			combo.getStore().load(
			{
				callback: function() 
				{
					combo.getStore().each(function(record) 
					{
						if (record.data[field_name] == value)
						{
							combo.setValue(value);							
							combo.fireEvent('select', combo, record, 0);
						}
					});
					if (callback)
					{
						callback();
					}
				},
				params: params 
			});
		}
	},

	show: function() 
	{
		sw.Promed.swLisRequestEditWindow.superclass.show.apply(this, arguments);
		
		var form = this.LisRequestForm.getForm();
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

		this.findById('LREW_PersonInformationFrame').load({ Person_id: arguments[0].Person_id, Server_id: arguments[0].Server_id });

		setMedStaffFactGlobalStoreFilter({});
		
		form.findField('MedPersonal_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		//var frm = form.getForm();
		form.findField('LisRequest_Num').focus();
		
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;



		/*
		if (arguments[0].LisRequest_id) 
			form.LisRequest_id = arguments[0].LisRequest_id;
		else 
			form.LisRequest_id = null;
		*/
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
		/*else 
		{
			if ((form.LisRequest_id) && (form.LisRequest_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}*/
		//frm.reset();
		
		//frm.setValues(arguments[0]);

		//	form.getLoadMask(LOAD_WAIT).show();
		/*
		switch (form.action)
		{
			case 'add':
				form.setTitle(WND_UCTW_ADD);
				form.setFieldsDisabled(false);
				form.getDate();
				break;
			case 'edit':
				form.setTitle(WND_UCTW_EDIT);
				form.setFieldsDisabled(false);
				break;
			case 'view':
				form.setTitle(WND_UCTW_VIEW);
				form.setFieldsDisabled(true);
				break;
		}
		form.center();
		*/
		/*
		if (form.action!='add')
		{
			frm.load(
			{
				params: 
				{
					LisRequest_id: form.LisRequest_id
				},
				failure: function() 
				{
//					form.getLoadMask().hide();
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
//					form.getDate();
					// Загружаем справочники

				}
				//url: '/?c=Usluga&m=loadLisRequestView'
			});
		}
		*/
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;

		this.LisRequestForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 0px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LisRequestEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[
				new sw.Promed.PersonInformationPanel({
					button2Callback: function(callback_data) {
						var form = this.findById('swLisRequestEditWindow');
						form.getForm().findField('Server_id').setValue(callback_data.Server_id);
						this.findById('LREW_PersonInformationFrame').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
					}.createDelegate(this),
					button2OnHide: function() {
						this.findById('LREW_PersonInformationFrame').button1OnHide();
					}.createDelegate(this),
					button3OnHide: function() {
						this.findById('LREW_PersonInformationFrame').button1OnHide();
					}.createDelegate(this),
					button4OnHide: function() {
						this.findById('LREW_PersonInformationFrame').button1OnHide();
					}.createDelegate(this),
					button5OnHide: function() {
						this.findById('LREW_PersonInformationFrame').button1OnHide();
					}.createDelegate(this),
					id: 'LREW_PersonInformationFrame',
					region: 'north'
				}),
				{
					style: 'padding-bottom: 10px'
				},
				{
					layout: 'column',
					style: 'padding-left: 10px',
					items:
					[{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								xtype: 'textfield',
								fieldLabel: lang['nomer'],
								width: 120,
								name: 'LisRequest_Num',
								tabIndex: TABINDEX_LREW
							}
						]
					},
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['podrazdelenie'],
								name: 'LpuSection_id',
								hiddenName: 'LpuSection_id',
								tabIndex:  TABINDEX_LREW + 1,
								width: 300,
								listWidth: 500,
								sortField: 'LpuSection_Code',
								xtype: 'swlpusectionglobalcombo'
							}
						]
					}
					]
				},
				{
					layout: 'column',
					style: 'padding-left: 10px',
					items:
					[
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['vrach'],
								name: 'MedPersonal_id',
								hiddenName: 'MedPersonal_id',
								tabIndex:  TABINDEX_LREW + 2,
								width: 300,
								listWidth: 600,
								sortField: 'MedPersonal_Code',
								xtype: 'swmedstafffactglobalcombo'
							}
						]
					},
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['forma'],
								name: 'RequestForm_id',
								hiddenName: 'RequestForm_id',
								width: 300,
								prefix: 'lis_',
								tabIndex:  TABINDEX_LREW + 3,
								comboSubject: 'RequestForm',
								sortField: 'RequestForm_Code',
								xtype: 'swcustomobjectcombo'
							}
						]
					}
					]
				},
				{
					layout: 'column',
					style: 'padding-left: 10px',
					items:
					[
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['vzyata'],
								name: 'LisRequest_getDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								selectOnFocus: true,
								width: 120,
								tabIndex:  TABINDEX_LREW + 4,
								xtype: 'swdatefield'
							}
						]
					},
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['dostavlena'],
								name: 'LisRequest_dostDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								selectOnFocus: true,
								width: 120,
								tabIndex:  TABINDEX_LREW + 5,
								xtype: 'swdatefield'
							}
						]
					}]
				},
				{
					layout: 'column',
					style: 'padding-left: 10px',
					items:
					[
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								allowBlank: true,
								allowNegative: false,
								//enableKeyEvents: true,
								fieldLabel: lang['srok_ber-ti'],
								name: 'LisRequest_PregSrok',
								tabIndex:  TABINDEX_LREW + 6,
								width: 120,
								xtype: 'numberfield'
							}
						]
					},
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['faza_tsikla'],
								name: 'CyclePeriod_id',
								hiddenName: 'CyclePeriod_id',
								width: 300,
								prefix: 'lis_',
								tabIndex:  TABINDEX_LREW + 7,
								comboSubject: 'CyclePeriod',
								sortField: 'CyclePeriod_Code',
								xtype: 'swcustomobjectcombo'
							}
						]
					}]
				},
				{
					layout: 'column',
					style: 'padding-left: 10px',
					items:
					[
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['tolko_chtenie'],
								name: 'LisRequest_IsReadOnly',
								hiddenName: 'LisRequest_IsReadOnly',
								tabIndex:  TABINDEX_LREW + 8,
								width: 120,
								xtype: 'swyesnocombo'
							}
						]
					},
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['prioritet'],
								name: 'Priority_id',
								hiddenName: 'Priority_id',
								width: 300,
								prefix: 'lis_',
								tabIndex:  TABINDEX_LREW + 9,
								comboSubject: 'Priority',
								sortField: 'Priority_Code',
								xtype: 'swcustomobjectcombo'
							}
						]
					}]
				},
				{
					layout: 'column',
					style: 'padding-left: 10px',
					items:
					[
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['biomaterial'],
								name: 'Biomaterial_id',
								hiddenName: 'Biomaterial_id',
								width: 300,
								prefix: 'lis_',
								tabIndex:  TABINDEX_LREW + 10,
								comboSubject: 'Biomaterial',
								sortField: 'Biomaterial_Code',
								xtype: 'swcustomobjectcombo'
							}
						]
					},
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								allowBlank: true,
								allowNegative: false,
								//enableKeyEvents: true,
								fieldLabel: lang['shtrih-kod'],
								name: 'LisRequest_Barcode',
								tabIndex:  TABINDEX_LREW + 11,
								width: 180,
								xtype: 'numberfield'
							}
						]
					}]
				},
				{
					layout: 'column',
					style: 'padding-left: 10px',
					items:
					[
					{
						labelWidth: 100,
						columnWidth: 0.5,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['issledovanie'],
								name: 'Target_id',
								hiddenName: 'Target_id',
								width: 300,
								prefix: 'lis_',
								tabIndex:  TABINDEX_LREW + 12,
								comboSubject: 'Target',
								sortField: 'Target_Code',
								xtype: 'swcustomobjectcombo'
							}
						]
					}]
				}
			],
			timeout: 600,
			url: '/?c=Lis&m=saveLisRequest'
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
				tabIndex:  TABINDEX_LREW + 13
			}, 
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_LREW + 14),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: 207,
				text: BTN_FRMCANCEL,
				tabIndex:  TABINDEX_LREW + 15
			}],
			items: [/*form.PersonFrame,*/ form.LisRequestForm]
		});
		sw.Promed.swLisRequestEditWindow.superclass.initComponent.apply(this, arguments);
	}
});