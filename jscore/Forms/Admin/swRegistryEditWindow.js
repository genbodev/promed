/**
* swRegistryEditWindow - окно редактирования/добавления реестра (счета).
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
* @comment      Префикс для id компонентов rege (RegistryEditForm)
*               tabIndex (firstTabIndex): 15100+1 .. 15200
*
*
* @input data: action - действие (add, edit, view)
*              Registry_id - ID реестра
*/
/*NO PARSE JSON*/
sw.Promed.swRegistryEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 700,
	layout: 'form',
	firstTabIndex: 15100,
	id: 'RegistryEditWindow',
	codeRefresh: true,
	objectName: 'swRegistryEditWindow',
	objectSrc: '/jscore/Forms/Admin/swRegistryEditWindow.js',
	listeners: 
	{
		hide: function() 
		{
			swLpuBuildingGlobalStore.clearFilter();
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	
	doSave: function() 
	{
		var form = this.RegistryForm;
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
		var begDate = form.findById('regeRegistry_begDate').getValue();
		var endDate = form.findById('regeRegistry_endDate').getValue();
		if ((begDate) && (endDate) && (begDate>endDate))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('regeRegistry_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		// а дату-то надо всетаки передать, понадобится при редактировании
		this.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.RegistryForm;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."});
		loadMask.show();
		var registry_acc_date = form.findById('regeRegistry_accDate').getValue().dateFormat('d.m.Y');
		form.getForm().submit(
		{
			params: 
			{
				RegistryType_id: form.findById('regeRegistryType_id').getValue(),
				Registry_accDate: registry_acc_date
			},
			failure: function(result_form, action) 
			{
				loadMask.hide();
				/*
				Тут стандартный акшен на ошибку отрабатывает, если ошибка - поэтому не надо 
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
				*/
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					//if (action.result.Registry_id)
					if (action.result.RegistryQueue_id)
					{
						//log(form.getForm().getValues());
						var records = {RegistryQueue_id:action.result.RegistryQueue_id, RegistryQueue_Position:action.result.RegistryQueue_Position}
						form.ownerCt.callback(form.ownerCt.owner, action.result.RegistryQueue_id, records) //, form.getForm().getValues(), (form.ownerCt.action=='add'));
						
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
		sw.Promed.swRegistryEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		if (!arguments[0] || !arguments[0].RegistryType_id) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi']+form.id+lang['ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka']
			});
		}
		form.focus();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].Registry_id) 
			form.Registry_id = arguments[0].Registry_id;
		else 
			form.Registry_id = null;
			
		if (arguments[0].RegistryStatus_id) 
			form.RegistryStatus_id = arguments[0].RegistryStatus_id;
		else 
			form.RegistryStatus_id = null;
		if (arguments[0].RegistryType_id) 
			form.RegistryType_id = arguments[0].RegistryType_id;
			
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
			if ((form.Registry_id) && (form.Registry_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}
		
		var base_form = form.findById('RegistryEditForm').getForm();
		base_form.reset();

		base_form.setValues(arguments[0]);

		if ( 'add' == form.action ) {
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
			base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
		} 

		base_form.findField('RegistryStacType_id').setContainerVisible(form.RegistryType_id == 1 || form.RegistryType_id == 14);
		// Показываем подразделение только для полки и стаца / смп / параклиники
		base_form.findField('LpuBuilding_id').setContainerVisible(form.RegistryType_id.inlist([1,2,6,14,15]));
		
		if (form.RegistryType_id >= 9 && form.RegistryType_id <= 10) {
			// Обеспечить контроль на дату начала реестра, значение не должно быть меньше 01.03.13 #21064
			form.findById('regeRegistry_begDate').setMinValue('01.03.2013');
		} else {
			form.findById('regeRegistry_begDate').setMinValue(null);
		}

		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));

		if ( form.RegistryType_id.toString().inlist([ '7', '9', '12' ]) ) {
			var dispClassList = [];

			switch ( form.RegistryType_id ) {
				case 7: // Дисп-ция взр. населения с 2013 года
					dispClassList = [ '1', '2' ];
				break;

				case 9: // Дисп-ция детей-сирот с 2013 года
					dispClassList = [ '3', '7' ];
				break;

				case 12: // Медосмотры несовершеннолетних
					dispClassList = [ '6', '9', '10' ];
				break;
			}

			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
		}

		if ((form.RegistryType_id == 2) || (form.RegistryType_id == 1) || (form.RegistryType_id == 14) || (form.RegistryType_id == 15)) {
			// Читаем список подразделений
			/*
			var filter = {};
			swLpuBuildingGlobalStore.filterBy(function(record) {
				var filter_flag = false;
				// Stac
				if ( record.get('LpuUnitType_id').inlist([1,6,7,9]) && (form.RegistryType_id == 1)) {
					filter_flag = true;
				}
				// Polka
				if ((record.get('LpuUnitType_id')==2) && (form.RegistryType_id == 2)) {
					filter_flag = true;
				}
				return filter_flag;
			});
			*/
			base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
		}

		if ( form.action == 'edit' )
			this.buttons[0].setText(lang['pereformirovat']);
		else
			this.buttons[0].setText(lang['sohranit']);
		
		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if (form.RegistryStatus_id==4)
		{
			form.action = "view";
		}

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (form.action) 
		{
			case 'add':
				form.setTitle(WND_ADMIN_REGISTRYADD);
				form.enableEdit(true);
				loadMask.hide();
				//form.getForm().clearInvalid();
				base_form.findField('Registry_IsRepeated').setValue(1);
				form.findById('regeKatNasel_id').fireEvent('change', null, 0);
				form.findById('regeRegistry_begDate').focus(true, 50);
				break;
			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				break;
			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
				break;
		}

		form.findById('REW_OrgRSchet_Combo').getStore().clearFilter();

		if (form.action!='add')
		{
			form.findById('RegistryEditForm').getForm().load(
			{
				params: 
				{
					Registry_id: form.Registry_id
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
					loadMask.hide();

					var
						Org_mid = base_form.findField('Org_mid').getValue(),
						OrgRSchet_id = base_form.findField('OrgRSchet_id').getValue();
						OrgRSchet_mid = base_form.findField('OrgRSchet_mid').getValue();

					base_form.findField('Org_mid').clearValue(),
					base_form.findField('OrgRSchet_mid').clearValue();

					base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());

					if ( form.findById('REW_OrgRSchet_Combo').getStore().getCount() == 0 ) {
						form.findById('REW_OrgRSchet_Combo').getStore().load({
							callback: function() {
								form.filterOrgRSchetCombo(OrgRSchet_id);
							},
							params: {
								object: 'OrgRSchet',
								OrgRSchet_id: '',
								OrgRSchet_Name: '',
								OrgRSchet_begDate: '',
								OrgRSchet_endDate: ''
							}
						});
					}
					else {
						form.filterOrgRSchetCombo(OrgRSchet_id);
					}

					if ( !Ext.isEmpty(Org_mid) ) {
						base_form.findField('Org_mid').getStore().load({
							callback: function() {
								var index = base_form.findField('Org_mid').getStore().findBy(function(rec) {
									return (rec.get('Org_id') == Org_mid);
								});

								if ( index >= 0 ) {
									base_form.findField('Org_mid').setValue(Org_mid);

									if ( !Ext.isEmpty(OrgRSchet_mid) ) {
										base_form.findField('OrgRSchet_mid').getStore().load({
											callback: function() {
												index = base_form.findField('OrgRSchet_mid').getStore().findBy(function(rec) {
													return (rec.get('OrgRSchet_id') == OrgRSchet_mid);
												});

												if ( index >= 0 ) {
													base_form.findField('OrgRSchet_mid').setValue(OrgRSchet_mid);
												}
											},
											params: {
												Org_id: Org_mid
											}
										});
									}
								}
							},
							params: {
								Org_id: Org_mid
							}
						});
					}

					form.findById('regeKatNasel_id').fireEvent('change', form.findById('regeKatNasel_id').getValue(), 0);
					if (form.action=='edit')
						form.findById('regeRegistry_begDate').focus(true, 50);
					else 
						form.focus();

					form.syncSize();
					form.syncShadow();
				},
				url: '/?c=Registry&m=loadRegistry'
			});
		}
		else {
			// устанавливаем дату счета
			form.findById('regeRegistry_accDate').setValue(getGlobalOptions().date);

			if ( form.findById('REW_OrgRSchet_Combo').getStore().getCount() == 0 ) {
				form.findById('REW_OrgRSchet_Combo').getStore().load({
					callback: function() {
						form.filterOrgRSchetCombo();
					},
					params: {
						object: 'OrgRSchet',
						OrgRSchet_id: '',
						OrgRSchet_Name: '',
						OrgRSchet_begDate: '',
						OrgRSchet_endDate: ''
					}
				});
			}
			else {
				form.filterOrgRSchetCombo();
			}

			form.syncSize();
			form.syncShadow();
		}
		
		// запрещаем редактировать дату счета
		form.findById('regeRegistry_accDate').disable();
	},

	filterOrgRSchetCombo: function(OrgRSchet_id) {
		var combo = this.findById('REW_OrgRSchet_Combo');
		var date = this.findById('regeRegistry_accDate').getValue();

		combo.getStore().clearFilter();
		combo.lastQuery = '';

		if ( !Ext.isEmpty(date) && typeof date == 'object' ) {
			combo.getStore().filterBy(function(rec) {
				if ( Ext.isEmpty(rec.get('OrgRSchet_begDate')) && Ext.isEmpty(rec.get('OrgRSchet_endDate')) ) {
					return true;
				}

				return (
					(Ext.isEmpty(rec.get('OrgRSchet_begDate')) || typeof rec.get('OrgRSchet_begDate') != 'object' || rec.get('OrgRSchet_begDate') <= date)
					&& (Ext.isEmpty(rec.get('OrgRSchet_endDate')) || typeof rec.get('OrgRSchet_endDate') != 'object' || rec.get('OrgRSchet_endDate') >= date)
				);
			});

			if ( !Ext.isEmpty(OrgRSchet_id) ) {
				var index = combo.getStore().findBy(function(rec) {
					return (rec.get('OrgRSchet_id') == OrgRSchet_id);
				});

				if ( index >= 0 ) {
					combo.setValue(OrgRSchet_id);
				}
				else {
					combo.clearValue();
				}
			}
		}
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		
		this.RegistryForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'RegistryEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			items: 
			[{
				id: 'regeRegistry_id',
				name: 'Registry_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				id: 'regeLpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				xtype: 'hidden',
				name: 'RegistryStatus_id',
				id: 'regeRegistryStatus_id',
				value: 3 // По умолчанию при добавлении 
			}, 
			{
				xtype: 'hidden',
				name: 'Registry_IsActive',
				id: 'regeRegistry_IsActive',
				value: 2 // По умолчанию при добавлении 
			}, 
			{
				anchor: '100%',
				disabled: true,
				name: 'RegistryType_id',
				xtype: 'swregistrytypecombo',
				id: 'regeRegistryType_id',
				tabIndex: form.firstTabIndex + 1
			}, 
			{
				allowBlank: false,
				fieldLabel: lang['nachalo_perioda'],
				id: 'regeRegistry_begDate',
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex + 2,
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: lang['okonchanie_perioda'],
				id: 'regeRegistry_endDate',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex + 3,
				width: 100,
				xtype: 'swdatefield'
			},
			{
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: lang['tip_disp-tsii_medosmotra'],
				id: 'regeDispClass_id',
				lastQuery: '',
				tabIndex: form.firstTabIndex + 4,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			},
			{
				anchor: '100%',
				hiddenName: 'LpuBuilding_id',
				fieldLabel: lang['podrazdelenie'],
				id: 'regeLpuBuilding_id',
				linkedElements: [],
				tabIndex: form.firstTabIndex + 5,
				xtype: 'swlpubuildingglobalcombo'
			},
			{
				allowBlank: false,
				anchor: '100%',
				listeners: {
					'change': function(combo, nv, ov) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('PayType_id') == nv);
						});

						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					}.createDelegate(this),
					'select': function(combo, record, idx) {
						var base_form  = this.RegistryForm.getForm();

						if ( typeof record == 'object' && record.get('PayType_SysNick') == 'ovd' ) {
							base_form.findField('KatNasel_id').setContainerVisible(false);
							base_form.findField('KatNasel_id').clearValue();
							base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
							base_form.findField('KatNasel_id').setAllowBlank(true);
							base_form.findField('Org_mid').setContainerVisible(true);
							base_form.findField('Org_mid').setAllowBlank(false);
							base_form.findField('OrgRSchet_mid').setContainerVisible(true);
							base_form.findField('OrgRSchet_mid').setAllowBlank(false);
							base_form.findField('Registry_begDate').setMinValue('01.01.2012');
							base_form.findField('Registry_endDate').setMinValue('01.01.2012');

							if ( Ext.isEmpty(base_form.findField('Org_mid').getValue()) && !Ext.isEmpty(getGlobalOptions().mvd_org)) {
								base_form.findField('Org_mid').getStore().load({
									callback: function() {
										if ( base_form.findField('Org_mid').getStore().getCount() == 1 ) {
											base_form.findField('Org_mid').setValue(getGlobalOptions().mvd_org);

											if ( !Ext.isEmpty(getGlobalOptions().mvd_org_schet) ) {
												base_form.findField('OrgRSchet_mid').setValue(getGlobalOptions().mvd_org_schet);
											}

											base_form.findField('Org_mid').fireEvent('change', base_form.findField('Org_mid'), getGlobalOptions().mvd_org);
										}
									},
									params: {
										Org_id: getGlobalOptions().mvd_org
									}
								});
							}
						}
						else {
							// Если не полка/стац/смп, то категорию населения убираем 
							base_form.findField('KatNasel_id').setAllowBlank(!this.RegistryType_id.inlist([1,2,6,7,11,12,14,15]));
							base_form.findField('KatNasel_id').setContainerVisible(this.RegistryType_id.inlist([1,2,6,7,11,12,14,15]));
							base_form.findField('Registry_begDate').setMinValue(undefined);
							base_form.findField('Registry_endDate').setMinValue(undefined);
							base_form.findField('Org_mid').clearValue();
							base_form.findField('Org_mid').setContainerVisible(false);
							base_form.findField('Org_mid').setAllowBlank(true);
							base_form.findField('Org_mid').getStore().removeAll();
							base_form.findField('OrgRSchet_mid').clearValue();
							base_form.findField('OrgRSchet_mid').setContainerVisible(false);
							base_form.findField('OrgRSchet_mid').setAllowBlank(true);
							base_form.findField('OrgRSchet_mid').getStore().removeAll();
						}

						base_form.findField('Registry_begDate').validate();
						base_form.findField('Registry_endDate').validate();
						base_form.findField('KatNasel_id').validate();

						this.syncSize();
						this.syncShadow();
					}.createDelegate(this)
				},
				name: 'PayType_id',
				xtype: 'swpaytypecombo',
				tabIndex: form.firstTabIndex + 6
			}, 
			{
				anchor: '100%',
				xtype: 'swkatnaselcombo',
				id: 'regeKatNasel_id',
				tabIndex: form.firstTabIndex + 7,
				listeners: {
					'change': function(combo, nv, ov) {
						var bf  = this.RegistryForm.getForm();

						if ( bf.findField('RegistryType_id').getValue() == 1 || bf.findField('RegistryType_id').getValue() == 14 ) {
							bf.findField('RegistryStacType_id').setContainerVisible(nv == 2);
							bf.findField('RegistryStacType_id').setDisabled(nv != 2);

							if ( nv != 2 ) {
								bf.findField('RegistryStacType_id').clearValue();
							}
						}
						else {
							bf.findField('RegistryStacType_id').setContainerVisible(false);
						}
					}.createDelegate(this)
				}
			}, 
			{
				anchor: '100%',
				allowBlank: true,
				value: null,
				fieldLabel: lang['tip_reestra_stats'],
				comboSubject: 'RegistryStacType',
				name: 'RegistryStacType_id',
				xtype: 'swcustomobjectcombo',
				id: 'regeRegistryStacType_id',
				tabIndex: form.firstTabIndex + 8
			},
			{
				allowBlank: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "10",
					autocomplete: "off"
				},
				fieldLabel: lang['nomer_scheta'],
				id: 'regeRegistry_Num',
				name: 'Registry_Num',
				tabIndex: form.firstTabIndex + 9,
				width: 100,
				xtype: 'textfield'
			},
			{
				allowBlank: false,
				anchor: '100%',
				hiddenName: 'OrgRSchet_id',
				id: 'REW_OrgRSchet_Combo',
				tabIndex: form.firstTabIndex + 10,
				xtype: 'sworgrschetcombo'
			}, 
			{
				anchor: '100%',
				displayField: 'Org_Name',
				enableKeyEvents: true,
				fieldLabel: lang['organizatsiya_mvd'],
				hiddenName: 'Org_mid',
				id: 'REW_Org_Combo',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form  = this.RegistryForm.getForm();

						var OrgRSchet_mid = base_form.findField('OrgRSchet_mid').getValue();

						base_form.findField('OrgRSchet_mid').clearValue();
						base_form.findField('OrgRSchet_mid').getStore().removeAll();

						if ( !Ext.isEmpty(newValue) ) {
							base_form.findField('OrgRSchet_mid').getStore().load({
								callback: function() {
									var index = base_form.findField('OrgRSchet_mid').getStore().findBy(function(rec) {
										return (rec.get('OrgRSchet_id') == OrgRSchet_mid);
									});

									if ( index >= 0 ) {
										base_form.findField('OrgRSchet_mid').setValue(OrgRSchet_mid);
									}
								},
								params: {
									Org_id: newValue
								}
							});
						}
					}.createDelegate(this)
				},
				onTrigger1Click: function() {
					var base_form = this.RegistryForm.getForm();
					var combo = base_form.findField('Org_mid');

					if ( combo.disabled ) {
						return false;
					}

					getWnd('swOrgSearchWindow').show({
						object: 'org',
						onClose: function() {
							combo.focus(true, 200)
						},
						onSelect: function(org_data) {
							if ( !Ext.isEmpty(org_data.Org_id) ) {
								combo.getStore().loadData([{
									Org_id: org_data.Org_id,
									Org_Name: org_data.Org_Name
								}]);
								combo.setValue(org_data.Org_id);
								combo.fireEvent('change', combo, org_data.Org_id);
								getWnd('swOrgSearchWindow').hide();
								combo.collapse();
							}
						}
					});
				}.createDelegate(this),
				tabIndex: form.firstTabIndex + 11,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{Org_Name}',
					'</div></tpl>'
				),
				valueField: 'Org_id',
				xtype: 'sworgcombo'
			},
			{
				allowBlank: false,
				anchor: '100%',
				displayField: 'OrgRSchet_Name',
				fieldLabel: lang['r_schet_organizatsii_mvd'],
				hiddenName: 'OrgRSchet_mid',
				id: 'REW_OrgRSchetM_Combo',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'OrgRSchet_id', type: 'int' },
						{ name: 'OrgRSchet_Name', type: 'string' },
						{ name: 'OrgRSchet_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'OrgRSchet_endDate', type: 'date', dateFormat: 'd.m.Y' }
					],
					key: 'OrgRSchet_id',
					sortInfo: {
						field: 'OrgRSchet_Name'
					},
					url: '/?c=Org&m=loadOrgRSchetList'
				}),
				tabIndex: form.firstTabIndex + 12,
				valueField: 'OrgRSchet_id',
				xtype: 'swbaselocalcombo'
			},
			{
				allowBlank: false,
				fieldLabel: lang['data_scheta'],
				format: 'd.m.Y',
				id: 'regeRegistry_accDate',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex + 13,
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['povtornaya_podacha'],
				hiddenName: 'Registry_IsRepeated',
				tabIndex: form.firstTabIndex + 14,
				width: 100,
				xtype: 'swyesnocombo'
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
				{ name: 'KatNasel_id' },
				{ name: 'DispClass_id' },
				{ name: 'PayType_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryType_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'RegistryStacType_id' },
				{ name: 'RegistryStatus_id' },
				{ name: 'Org_mid' },
				{ name: 'OrgRSchet_id' },
				{ name: 'OrgRSchet_mid' },
				{ name: 'Registry_IsActive' },
				{ name: 'Lpu_id' },
				{ name: 'Registry_IsRepeated' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveRegistry'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE,
				tabIndex: form.firstTabIndex + 15
			}, 
			{
				text: '-'
			},
			HelpButton(this, form.firstTabIndex + 16),
			{
				handler: function() 
				{
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				// tabIndex: 207,
				text: BTN_FRMCANCEL,
				tabIndex: form.firstTabIndex + 17
			}],
			items: [form.RegistryForm]
		});
		sw.Promed.swRegistryEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('REW_OrgRSchet_Combo').setBaseFilter(function(rec) {
			var date = this.findById('regeRegistry_accDate').getValue();

			if ( !Ext.isEmpty(date) && typeof date == 'object' ) {
				if ( Ext.isEmpty(rec.get('OrgRSchet_begDate')) && Ext.isEmpty(rec.get('OrgRSchet_endDate')) ) {
					return true;
				}

				return (
					(Ext.isEmpty(rec.get('OrgRSchet_begDate')) || typeof rec.get('OrgRSchet_begDate') != 'object' || rec.get('OrgRSchet_begDate') <= date)
					&& (Ext.isEmpty(rec.get('OrgRSchet_endDate')) || typeof rec.get('OrgRSchet_endDate') != 'object' || rec.get('OrgRSchet_endDate') >= date)
				);
			}

			return true;
		}.createDelegate(this));
	}
});