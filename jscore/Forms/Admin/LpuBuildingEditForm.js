/**
* swLpuBuildingEditForm - окно просмотра и редактирования подразделений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      17.07.2009
*/

sw.Promed.swLpuBuildingEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['podrazdelenie'],
	id: 'LpuBuildingEditForm',
	layout: 'form',
	maximizable: false,
	autoScroll: true,
	shim: false,
	width: 550,
	//~ autoHeight: true,
	height: 550,
	minWidth: 550,
	minHeight: 420,
	//lists: ['LpuBuildingType_id'],
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lbOk',
		tabIndex: 2762,
		iconCls: 'save16',
		handler: function()
		{
			this.ownerCt.doSave();
		}
	},{
		text:'-'
	},{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) 
		{
			ShowHelp(this.ownerCt.title);
		}
	},{
		text: BTN_FRMCANCEL,
		id: 'lbCancel',
		tabIndex: 2763,
		iconCls: 'cancel16',
		onTabAction: function()
		{
			this.ownerCt.findById('lbLpuBuilding_Code').focus();
		},
		onShiftTabAction: function()
		{
			Ext.getCmp('lbOk').focus();
		},
		handler: function()
		{
			this.ownerCt.hide();
			this.ownerCt.returnFunc(this.ownerCt.owner, -1);
		}
	}],
	returnFunc: function(owner) {},
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	show: function() 
	{
		sw.Promed.swLpuBuildingEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('LpuBuildingEditForm'), { msg: LOAD_WAIT });
		this.addressForm = getWnd('swAddressEditWindow');
		
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		else
			this.Lpu_id = null;
		if (arguments[0].LpuBuilding_id)
			this.LpuBuilding_id = arguments[0].LpuBuilding_id;
		else 
			this.LpuBuilding_id = null;

		if (!arguments[0]) {
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		var form = this;
		form.findById('LpuBuildingEditFormPanel').getForm().reset();
		switch (this.action)
			{
			case 'add':
				form.setTitle(lang['podrazdelenie_dobavlenie']);
				break;
			case 'edit':
				form.setTitle(lang['podrazdelenie_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['podrazdelenie_prosmotr']);
				break;
			}
		if (this.action!='add')
		{
			form.findById('LpuBuildingEditFormPanel').getForm().load(
			{
				url: C_LPUBUILDING_GET,
				params:
				{
					object: 'LpuBuilding',
					Lpu_id: '',
					LpuBuilding_id: form.LpuBuilding_id,
					LpuBuilding_Code: '',
					LpuBuilding_Nick: '',
					LpuBuilding_Name: '',
					LpuBuildingType_id: '',
					LpuBuilding_WorkTime: '',
					LpuBuilding_RoutePlan: ''
				},
				success: function ()
				{
					loadMask.hide();

					form.findById('lbLpuBuildingType_id').fireEvent('change', form.findById('lbLpuBuildingType_id'), form.findById('lbLpuBuildingType_id').getValue());

					if ( form.findById('lbLpuBuilding_IsExport').getValue() == 2 ) {
						form.findById('lbLpuBuilding_IsExportCheckbox').setValue(true);
					}

					form.findById('lbLpuBuilding_Code').focus(true);
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
			form.findById('lbLpu_id').setValue(this.Lpu_id);
			form.findById('lbLpuBuildingType_id').fireEvent('change', form.findById('lbLpuBuildingType_id'), form.findById('lbLpuBuildingType_id').getValue());
			form.findById('lbLpuBuilding_IsAIDSCenter').setValue(getGlobalOptions().lpu_is_secret);

			loadMask.hide();
		}

		/**
		 * Блок логики загрузки данных в поле Филиал (LpuFilial_id или lbLpuFilial_id)
		 * В режиме просмотра и добавления доступны все Филиалы МО
		 */
		// Для поля филиал в режиме edit данные нужно каждый раз загружать заново. Список доступных филиалов зависит от времени действия здания и филиалов
		form.findById('lbLpuFilial_id').getStore().baseParams = {
			Object: 'LpuFilial',
			Lpu_id: form.Lpu_id,
			LpuBuilding_id: ''
		};

		if (this.action === 'edit')
		{
			// Добавляем параметр к запросу, для того чтобы в базе фильтровать филиалы по времени действия здания
			form.findById('lbLpuFilial_id').getStore().baseParams.LpuBuilding_id = form.LpuBuilding_id;
		}

		// Перезагружаем при редактировании, один раз после него, при первом открытии и при смене МО
		if (form.findById('lbLpuFilial_id').lastLoadAction == 'edit' || this.action == 'edit' || form.findById('lbLpuFilial_id').lastLoadAction == '' || form.findById('lbLpuFilial_id').Lpu_id != form.Lpu_id)
		{
			// Загружаем данные для поля филиал
			form.findById('lbLpuFilial_id').getStore().load();
		}

		// Устанавлием значение последнего режима открытия формы, чтобы при следующем открытии решить, надо ли обновлять данные в поле Филиал
		form.findById('lbLpuFilial_id').lastLoadAction = this.action;
		// Запоминаем id МО, чтобы перезагрузить, когда будет открыто другое МО
		form.findById('lbLpuFilial_id').Lpu_id = form.Lpu_id;



		if (this.action=='view')
		{
			form.findById('lbLpuFilial_id').disable();
			form.findById('lbLpuBuilding_Code').disable();
			form.findById('lbLpuBuilding_Name').disable();
			form.findById('lbLpuBuilding_Nick').disable();
			form.findById('lbLpuBuildingType_id').disable();
			form.findById('lbLpuBuilding_CmpStationCode').disable();
			form.findById('lbLpuBuilding_CmpSubstationCode').disable();
			form.findById('lbLpuBuilding_WorkTime').disable();
			form.findById('lbLpuBuilding_RoutePlan').disable();
			form.findById('lbAddress_AddressText').disable();
			form.findById('lbPAddress_AddressText').disable();
			form.findById('lbLpuBuilding_IsExportCheckbox').disable();
			form.buttons[0].disable();
		}
		else 
		{
			form.findById('lbLpuFilial_id').enable();
			form.findById('lbLpuBuilding_Code').enable();
			form.findById('lbLpuBuilding_Name').enable();
			form.findById('lbLpuBuilding_Nick').enable();
			form.findById('lbLpuBuildingType_id').enable();
			form.findById('lbLpuBuilding_CmpStationCode').enable();
			form.findById('lbLpuBuilding_CmpSubstationCode').enable();
			form.findById('lbLpuBuilding_WorkTime').enable();
			form.findById('lbLpuBuilding_RoutePlan').enable();
			form.findById('lbAddress_AddressText').enable();
			form.findById('lbPAddress_AddressText').enable();
			form.findById('lbLpuBuilding_IsExportCheckbox').enable();
			form.buttons[0].enable();
		}

		form.findById('LpuBuildingEditFormPanel').getForm().findField('LpuBuilding_begDate').setDisabled( !isAdmin && !isLpuAdmin() );
		form.findById('LpuBuildingEditFormPanel').getForm().findField('LpuBuilding_endDate').setDisabled( !isAdmin && !isLpuAdmin() );
		form.findById('lbLpuBuilding_Code').focus(true, 50);

	},
	doSave: function() 
	{
		var form = this.findById('LpuBuildingEditFormPanel');
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
		var form = this.findById('LpuBuildingEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuBuildingEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		var window = this;
		if ( form.findById('lbLpuBuilding_IsExportCheckbox').getValue() == true ) {
			form.findById('lbLpuBuilding_IsExport').setValue(2);
		}
		else {
			form.findById('lbLpuBuilding_IsExport').setValue(1);
		}

		form.getForm().submit(
			{
				failure: function(result_form, action) 
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.Error_Code)
						{
							Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
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
						if (action.result.LpuBuilding_id)
						{
							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuBuilding_id);
						}
						else
							Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
					}
					else
						Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
				}
			});
	},
	initComponent: function() 
	{
		this.MainPanel = new sw.Promed.FormPanel({
			id:'LpuBuildingEditFormPanel',
			frame: true,
			region: 'center',
			labelWidth: 150,
			items:
			[{
				name: 'Lpu_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lbLpu_id'
			},{
				name: 'LpuBuilding_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lbLpuBuilding_id'
			},{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['period_deystviya'],
				labelWidth: 130,
				items: [
					{
						fieldLabel: lang['nachalo'],
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'LpuBuilding_begDate'
					}, {
						fieldLabel : lang['okonchanie'],
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'LpuBuilding_endDate'
					}
				]
            }, {
				allowBlank: true,
				anchor: '100%',
				disabled: false,
				fieldLabel: langs('Филиал'),
				displayField: 'LpuFilial_Name',
				codeField: 'LpuFilial_Code',
				valueField: 'LpuFilial_id',
				hiddenName: 'LpuFilial_id',
				id: 'lbLpuFilial_id',
				xtype: 'swlpufilialcombo',
				lastLoadAction: ''
			},{
				fieldLabel: lang['kod'],
				tabIndex: 2751,
				name: 'LpuBuilding_Code',
				xtype: 'numberfield',
				maxValue: (getRegionNick().inlist([ 'astra' ]) ? 99999999 : 999999),
				minValue: 1,
				autoCreate: {tag: "input", size:14, maxLength: (getRegionNick().inlist([ 'astra' ]) ? "8" : "6"), autocomplete: "off"},
				id: 'lbLpuBuilding_Code',
				allowBlank: false
			},{
				anchor: '100%',
				tabIndex: 2752,
				fieldLabel : lang['naimenovanie'],
				name: 'LpuBuilding_Name',
				xtype: 'textfield',
				id: 'lbLpuBuilding_Name',
				allowBlank:false
			},{
				anchor: '100%',
				tabIndex: 2753,
				fieldLabel : lang['sokraschenie'],
				name: 'LpuBuilding_Nick',
				xtype: 'textfield',
				id: 'lbLpuBuilding_Nick'
			},{
				allowBlank:false,
				anchor: '100%',
				comboSubject: 'LpuBuildingType',
				disabled: false,
				fieldLabel: lang['tip'],
				hiddenName: 'LpuBuildingType_id',
				id: 'lbLpuBuildingType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('LpuBuildingType_id') == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var base_form = this.MainPanel.getForm();

						if ( getRegionNick().inlist([ 'ekb', 'kareliya', 'perm', 'penza', 'pskov' ]) && typeof record == 'object' && !Ext.isEmpty(record.get('LpuBuildingType_id')) && record.get('LpuBuildingType_id') == 27 ) {
							if(!(getRegionNick() == 'penza')){
								base_form.findField('LpuBuilding_CmpStationCode').setContainerVisible(true);
							}else{
								base_form.findField('LpuBuilding_CmpStationCode').setContainerVisible(false);
							}
							base_form.findField('LpuBuilding_CmpSubstationCode').setContainerVisible(true);
						}
						else {
							base_form.findField('LpuBuilding_CmpStationCode').setContainerVisible(false);
							base_form.findField('LpuBuilding_CmpSubstationCode').setContainerVisible(false);
						}

						this.syncSize();
						this.syncShadow();
					}.createDelegate(this)
				},
				tabIndex:2754,
				xtype: 'swcommonsprcombo'
			},{
				allowBlank: true,
				anchor: '100%',
				autoCreate: {tag: "input", size:14, maxLength: "10", autocomplete: "off"},
				fieldLabel : lang['kod_stantsii'],
				id: 'lbLpuBuilding_CmpStationCode',
				name: 'LpuBuilding_CmpStationCode',
				tabIndex: 2755,
				xtype: 'textfield'
			},{
				allowBlank: true,
				anchor: '100%',
				autoCreate: {tag: "input", size:14, maxLength: "10", autocomplete: "off"},
				fieldLabel: lang['kod_podstantsii'],
				id: 'lbLpuBuilding_CmpSubstationCode',
				name: 'LpuBuilding_CmpSubstationCode',
				tabIndex: 2756,
				xtype: 'textfield'
			},{ // Далее все для адреса
				xtype: 'hidden',
				name: 'Address_Zip',
				id: 'lbAddress_Zip'
			},{
				xtype: 'hidden',
				name: 'KLCountry_id',
				id: 'lbKLCountry_id'
			},{
				xtype: 'hidden',
				name: 'KLRGN_id',
				id: 'lbKLRGN_id'
			},{
				xtype: 'hidden',
				name: 'KLSubRGN_id',
				id: 'lbKLSubRGN_id'
			},{
				xtype: 'hidden',
				name: 'KLCity_id',
				id: 'lbKLCity_id'
			},{
				xtype: 'hidden',
				name: 'KLTown_id',
				id: 'lbKLTown_id'
			},{
				xtype: 'hidden',
				name: 'KLStreet_id',
				id: 'lbKLStreet_id'
			},{
				xtype: 'hidden',
				name: 'Address_House',
				id: 'lbAddress_House'
			},{
				xtype: 'hidden',
				name: 'Address_Corpus',
				id: 'lbAddress_Corpus'
			},{
				xtype: 'hidden',
				name: 'Address_Flat',
				id: 'lbAddress_Flat'
			},{
				xtype: 'hidden',
				name: 'Address_Address',
				id: 'lbAddress_Address'
			},
			new Ext.form.TwinTriggerField (
			{
				//xtype: 'trigger',
				name: 'Address_AddressText',
				id: 'lbAddress_AddressText',
				readOnly: true,
				anchor: '100%',
				trigger1Class: 'x-form-search-trigger',
				trigger2Class: 'x-form-clear-trigger',
				//trigger3Class: 'x-form-clear-trigger',
				fieldLabel: lang['adres_zdaniya'],
				tabIndex: 2757,
				enableKeyEvents: true,
				listeners: 
				{
					'keydown': function(inp, e) 
					{
						if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) )
						{
							if ( e.F4 == e.getKey() )
								inp.onTrigger1Click();
							if ( e.F2 == e.getKey() )
								inp.onTrigger2Click();
							if ( e.DELETE == e.getKey() && e.altKey)
								inp.onTrigger3Click();
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;
							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;
							e.browserEvent.returnValue = false;
							e.returnValue = false;
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							return false;
						}
					},
					'keyup': function( inp, e )
					{
						if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) )
						{
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;
							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;
							e.browserEvent.returnValue = false;
							e.returnValue = false;
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							return false;
						}
					}
				},
				onTrigger2Click: function() 
				{
					var ownerForm = this.ownerCt.ownerCt;
					ownerForm.findById('lbAddress_Zip').setValue('');
					ownerForm.findById('lbKLCountry_id').setValue('');
					ownerForm.findById('lbKLRGN_id').setValue('');
					ownerForm.findById('lbKLSubRGN_id').setValue('');
					ownerForm.findById('lbKLCity_id').setValue('');
					ownerForm.findById('lbKLTown_id').setValue('');
					ownerForm.findById('lbKLStreet_id').setValue('');
					ownerForm.findById('lbAddress_House').setValue('');
					ownerForm.findById('lbAddress_Corpus').setValue('');
					ownerForm.findById('lbAddress_Flat').setValue('');
					ownerForm.findById('lbAddress_Address').setValue('');
					ownerForm.findById('lbAddress_AddressText').setValue('');
				},
				onTrigger1Click: function() 
				{
					//var ownerWindow = this.ownerCt.ownerCt;
					var ownerForm = this.ownerCt.ownerCt;
					ownerForm.addressForm.show({
						fields: 
						{
							Address_ZipEdit: ownerForm.findById('lbAddress_Zip').value,
							KLCountry_idEdit: ownerForm.findById('lbKLCountry_id').value,
							KLRgn_idEdit: ownerForm.findById('lbKLRGN_id').value,
							KLSubRGN_idEdit: ownerForm.findById('lbKLSubRGN_id').value,
							KLCity_idEdit: ownerForm.findById('lbKLCity_id').value,
							KLTown_idEdit: ownerForm.findById('lbKLTown_id').value,
							KLStreet_idEdit: ownerForm.findById('lbKLStreet_id').value,
							Address_HouseEdit: ownerForm.findById('lbAddress_House').value,
							Address_CorpusEdit: ownerForm.findById('lbAddress_Corpus').value,
							Address_FlatEdit: ownerForm.findById('lbAddress_Flat').value,
							Address_AddressEdit: ownerForm.findById('lbAddress_Address').value
						},
						callback: function(values) 
						{
							
							ownerForm.findById('lbAddress_Zip').setValue(values.Address_ZipEdit);
							ownerForm.findById('lbKLCountry_id').setValue(values.KLCountry_idEdit);
							ownerForm.findById('lbKLRGN_id').setValue(values.KLRgn_idEdit);
							ownerForm.findById('lbKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
							ownerForm.findById('lbKLCity_id').setValue(values.KLCity_idEdit);
							ownerForm.findById('lbKLTown_id').setValue(values.KLTown_idEdit);
							ownerForm.findById('lbKLStreet_id').setValue(values.KLStreet_idEdit);
							ownerForm.findById('lbAddress_House').setValue(values.Address_HouseEdit);
							ownerForm.findById('lbAddress_Corpus').setValue(values.Address_CorpusEdit);
							ownerForm.findById('lbAddress_Flat').setValue(values.Address_FlatEdit);
							ownerForm.findById('lbAddress_Address').setValue(values.Address_AddressEdit);
							ownerForm.findById('lbAddress_AddressText').setValue(values.Address_AddressEdit);
							ownerForm.findById('LpuBuilding_Latitude').setValue(values.lat);
							ownerForm.findById('LpuBuilding_Longitude').setValue(values.lng);
							
							//запрос не работает под проксей

							//loadMask = new Ext.LoadMask(Ext.get('LpuBuildingEditForm'),{msg: "Подождите, идет обновление геоданных...", removeMask: true});
							//loadMask.show();

							Ext.Ajax.request({
            				    
            				    url: '/?c=Yandex&m=getCoordinates',
								params: values,
            				    success: function(result_form, action) {
            				    	
									var response_obj = Ext.util.JSON.decode(result_form.responseText);
									
									if(response_obj.message != '') {

										sw.swMsg.alert(lang['oshibka'], response_obj.message);
									}

            				        values.lat = response_obj.lat;
            				        values.lng = response_obj.lng;

									ownerForm.findById('LpuBuilding_Latitude').setValue(response_obj.lat);
									ownerForm.findById('LpuBuilding_Longitude').setValue(response_obj.lng);

									//loadMask.hide();
		
									ownerForm.findById('lbAddress_AddressText').focus(true, 500);

            				    },
            				    failure: function(result_form, action){

            				        sw.swMsg.alert(lang['oshibka'], 'ошибка запроса координат');
            				    }
            				});
						},
						onClose: function() 
						{
							ownerForm.findById('lbAddress_AddressText').focus(true, 500);
						}
					})
				}
			}),
			{
				hideSeparator: true,
				fieldLabel : 'СПИД-центр',
				id: 'lbLpuBuilding_IsAIDSCenter',
				name: 'LpuBuilding_IsAIDSCenter',
				xtype: 'swcheckbox'
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: 'Координаты',
				labelWidth: 130,
				items: [
					
					{
						fieldLabel : 'Широта',
						id    	   : 'LpuBuilding_Latitude',
						xtype 	   : 'textfield',
						maskRe     : /[\d\.]/
					},{
						fieldLabel : 'Долгота',
						id         : 'LpuBuilding_Longitude',
						xtype      : 'textfield',
						maskRe     : /[\d\.]/
					},{
            			//html: "<h1>qwe</h1>",
            			id : 'lbLpuBuilding_showCoordinates',
            			xtype: "button",
            			text : "Показать координаты на карте",
            			listeners : {

            				'click' : function () {

            					var ownerForm = this.ownerCt.ownerCt;

								if ( ownerForm.findById('LpuBuilding_Latitude').getValue().length == 0 || ownerForm.findById('LpuBuilding_Longitude').getValue().length == 0 ) {
									sw.swMsg.alert(lang['oshibka'], 'Не указаны координаты');
									return false;
								}

            					yandexMap = getWnd('swYandexMap');
            					yandexMap.show({
									lat : ownerForm.findById('LpuBuilding_Latitude').getValue() || 55.75222,
									lng : ownerForm.findById('LpuBuilding_Longitude').getValue() || 37.61556
								});
            				}
            			}
            		}
				]
            }, { // Фактический адрес
				xtype: 'hidden',
				name: 'PAddress_Zip',
				id: 'lbPAddress_Zip'
			},{
				xtype: 'hidden',
				name: 'PKLCountry_id',
				id: 'lbPKLCountry_id'
			},{
				xtype: 'hidden',
				name: 'PKLRGN_id',
				id: 'lbPKLRGN_id'
			},{
				xtype: 'hidden',
				name: 'PKLSubRGN_id',
				id: 'lbPKLSubRGN_id'
			},{
				xtype: 'hidden',
				name: 'PKLCity_id',
				id: 'lbPKLCity_id'
			},{
				xtype: 'hidden',
				name: 'PKLTown_id',
				id: 'lbPKLTown_id'
			},{
				xtype: 'hidden',
				name: 'PKLStreet_id',
				id: 'lbPKLStreet_id'
			},{
				xtype: 'hidden',
				name: 'PAddress_House',
				id: 'lbPAddress_House'
			},{
				xtype: 'hidden',
				name: 'PAddress_Corpus',
				id: 'lbPAddress_Corpus'
			},{
				xtype: 'hidden',
				name: 'PAddress_Flat',
				id: 'lbPAddress_Flat'
			},{
				xtype: 'hidden',
				name: 'PAddress_Address',
				id: 'lbPAddress_Address'
			},
			new Ext.form.TwinTriggerField (
			{
				//xtype: 'trigger',
				name: 'PAddress_AddressText',
				id: 'lbPAddress_AddressText',
				readOnly: true,
				anchor: '100%',
				trigger1Class: 'x-form-search-trigger',
				trigger2Class: 'x-form-clear-trigger',
				//trigger3Class: 'x-form-clear-trigger',
				fieldLabel: lang['adres_dlya_vyidachi_lvn'],
				tabIndex: 2758,
				enableKeyEvents: true,
				listeners: 
				{
					'keydown': function(inp, e) 
					{
						if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) )
						{
							if ( e.F4 == e.getKey() )
								inp.onTrigger1Click();
							if ( e.F2 == e.getKey() )
								inp.onTrigger2Click();
							if ( e.DELETE == e.getKey() && e.altKey)
								inp.onTrigger3Click();
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;
							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;
							e.browserEvent.returnValue = false;
							e.returnValue = false;
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							return false;
						}
					},
					'keyup': function( inp, e )
					{
						if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) )
						{
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;
							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;
							e.browserEvent.returnValue = false;
							e.returnValue = false;
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							return false;
						}
					}
				},
				onTrigger2Click: function() 
				{
					var ownerForm = this.ownerCt.ownerCt;
					ownerForm.findById('lbPAddress_Zip').setValue('');
					ownerForm.findById('lbPKLCountry_id').setValue('');
					ownerForm.findById('lbPKLRGN_id').setValue('');
					ownerForm.findById('lbPKLSubRGN_id').setValue('');
					ownerForm.findById('lbPKLCity_id').setValue('');
					ownerForm.findById('lbPKLTown_id').setValue('');
					ownerForm.findById('lbPKLStreet_id').setValue('');
					ownerForm.findById('lbPAddress_House').setValue('');
					ownerForm.findById('lbPAddress_Corpus').setValue('');
					ownerForm.findById('lbPAddress_Flat').setValue('');
					ownerForm.findById('lbPAddress_Address').setValue('');
					ownerForm.findById('lbPAddress_AddressText').setValue('');
				},
				onTrigger1Click: function() 
				{
					//var ownerWindow = this.ownerCt.ownerCt;
					var ownerForm = this.ownerCt.ownerCt;
					ownerForm.addressForm.show({
						fields: 
						{
							Address_ZipEdit: ownerForm.findById('lbPAddress_Zip').value,
							KLCountry_idEdit: ownerForm.findById('lbPKLCountry_id').value,
							KLRgn_idEdit: ownerForm.findById('lbPKLRGN_id').value,
							KLSubRGN_idEdit: ownerForm.findById('lbPKLSubRGN_id').value,
							KLCity_idEdit: ownerForm.findById('lbPKLCity_id').value,
							KLTown_idEdit: ownerForm.findById('lbPKLTown_id').value,
							KLStreet_idEdit: ownerForm.findById('lbPKLStreet_id').value,
							Address_HouseEdit: ownerForm.findById('lbPAddress_House').value,
							Address_CorpusEdit: ownerForm.findById('lbPAddress_Corpus').value,
							Address_FlatEdit: ownerForm.findById('lbPAddress_Flat').value,
							Address_AddressEdit: ownerForm.findById('lbPAddress_Address').value
						},
						callback: function(values) 
						{
							ownerForm.findById('lbPAddress_Zip').setValue(values.Address_ZipEdit);
							ownerForm.findById('lbPKLCountry_id').setValue(values.KLCountry_idEdit);
							ownerForm.findById('lbPKLRGN_id').setValue(values.KLRgn_idEdit);
							ownerForm.findById('lbPKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
							ownerForm.findById('lbPKLCity_id').setValue(values.KLCity_idEdit);
							ownerForm.findById('lbPKLTown_id').setValue(values.KLTown_idEdit);
							ownerForm.findById('lbPKLStreet_id').setValue(values.KLStreet_idEdit);
							ownerForm.findById('lbPAddress_House').setValue(values.Address_HouseEdit);
							ownerForm.findById('lbPAddress_Corpus').setValue(values.Address_CorpusEdit);
							ownerForm.findById('lbPAddress_Flat').setValue(values.Address_FlatEdit);
							ownerForm.findById('lbPAddress_Address').setValue(values.Address_AddressEdit);
							ownerForm.findById('lbPAddress_AddressText').setValue(values.Address_AddressEdit);
							ownerForm.findById('lbPAddress_AddressText').focus(true, 500);
						},
						onClose: function() 
						{
							ownerForm.findById('lbPAddress_AddressText').focus(true, 500);
						}
					})
				}
			}),{
				anchor: '100%',
				tabIndex:2759,
				fieldLabel: lang['vremya_rabotyi'],
				disabled: false,
				name: 'LpuBuilding_WorkTime',
				xtype: 'textfield',
				id: 'lbLpuBuilding_WorkTime'
			},{
				anchor: '100%',
				tabIndex:2760,
				fieldLabel: lang['shema_proezda'],
				disabled: false,
				name: 'LpuBuilding_RoutePlan',
				xtype: 'textfield',
				id: 'lbLpuBuilding_RoutePlan'
			}, {
				id: 'lbLpuBuilding_IsExport',
				name: 'LpuBuilding_IsExport',
				value: 1, // По умолчанию при добавлении 
				xtype: 'hidden'
			},{
				boxLabel: lang['vyigrujat_v_pmu'],
				id: 'lbLpuBuilding_IsExportCheckbox',
				labelSeparator: '',
				name: 'LpuBuilding_IsExportCheckbox',
				tabIndex: 2761,
				xtype: 'checkbox'
			},{
				xtype: 'fieldset',
				autoHeight: true,
				title: 'Кабинет здоровья',
				labelWidth: 130,
				items: [{
					xtype: 'swphonefield',
					fieldLabel: 'Контактный номер +7',
					name: 'LpuBuildingHealth_Phone'
				}, {
					xtype: 'textfield',
					fieldLabel: 'Электронная почта',
					name: 'LpuBuildingHealth_Email',
				}]
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
				alert('success');
				}
			},
			[
				{ name: 'Lpu_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'LpuBuilding_Code' },
				{ name: 'LpuBuilding_begDate' },
				{ name: 'LpuBuilding_endDate' },
				{ name: 'LpuBuilding_Nick' },
				{ name: 'LpuBuilding_Name' },
				{ name: 'LpuBuildingType_id' },
				{ name: 'LpuBuilding_WorkTime' },
				{ name: 'LpuBuilding_RoutePlan' },
				{ name: 'Address_Zip' }, 
				{ name: 'KLCountry_id' }, 
				{ name: 'KLRGN_id' }, 
				{ name: 'KLSubRGN_id' }, 
				{ name: 'KLCity_id' }, 
				{ name: 'KLTown_id' }, 
				{ name: 'KLStreet_id' }, 
				{ name: 'Address_House' }, 
				{ name: 'Address_Corpus' }, 
				{ name: 'Address_Flat' }, 
				{ name: 'Address_Address' }, 
				{ name: 'Address_AddressText' },
				{ name: 'PAddress_Zip' }, 
				{ name: 'PKLCountry_id' }, 
				{ name: 'PKLRGN_id' }, 
				{ name: 'PKLSubRGN_id' }, 
				{ name: 'PKLCity_id' }, 
				{ name: 'PKLTown_id' }, 
				{ name: 'PKLStreet_id' }, 
				{ name: 'PAddress_House' }, 
				{ name: 'PAddress_Corpus' }, 
				{ name: 'PAddress_Flat' }, 
				{ name: 'PAddress_Address' }, 
				{ name: 'PAddress_AddressText' },
				{ name: 'LpuBuilding_IsExport' },
				{ name: 'LpuBuilding_CmpStationCode' },
				{ name: 'LpuBuilding_CmpSubstationCode' },
				{ name: 'LpuBuilding_Latitude' },
				{ name: 'LpuBuilding_Longitude' },
				{ name: 'LpuFilial_id'},
				{ name: 'LpuFilial_Name'},
				{ name: 'LpuFilial_Code'},
				{ name: 'LpuBuilding_IsAIDSCenter'},
				{ name: 'LpuBuildingHealth_Phone'},
				{ name: 'LpuBuildingHealth_Email'}
			]
			),
			url: C_LPUBUILDING_SAVE
	});
		
		Ext.apply(this, 
		{
			items: [this.MainPanel]
		});
		sw.Promed.swLpuBuildingEditForm.superclass.initComponent.apply(this, arguments);
	}
});