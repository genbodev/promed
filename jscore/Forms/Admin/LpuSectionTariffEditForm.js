/**
* swLpuSectionTariffEditForm - окно просмотра и редактирования участков
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      07.07.2009
*/

sw.Promed.swLpuSectionTariffEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['tarif_otdeleniya'],
	id: 'LpuSectionTariffEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 500,
	height: 265,
	modal: true,
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	returnFunc: function(owner, kid) {},
	show: function()
	{
		sw.Promed.swLpuSectionTariffEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionTariffEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].LpuSectionTariff_id)
			this.LpuSectionTariff_id = arguments[0].LpuSectionTariff_id;
		else 
			this.LpuSectionTariff_id = null;
		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;
		else 
			this.LpuSection_id = null;
		if (arguments[0].LpuSection_Name)
			this.LpuSection_Name = arguments[0].LpuSection_Name;
		else 
			this.LpuSection_Name = null;
		if (arguments[0].LpuUnitType_id)
			this.LpuUnitType_id = arguments[0].LpuUnitType_id;
		else 
			this.LpuUnitType_id = null;
		
		if (!arguments[0])
			{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
			}
		var form = this;
		form.findById('LpuSectionTariffEditFormPanel').getForm().reset();
		
		form.findById('lstTariffClass_id').lastQuery = '';
		form.findById('lstTariffClass_id').getStore().clearFilter();
		form.findById('lstTariffClass_id').getStore().filterBy(function(rec) {
			if (rec.get('TariffClass_IsMap') && rec.get('TariffClass_IsMap') == 2) {
				return false;
			}
		});

		form.allowedTariffClassSysNicks = new Array();
		form.disallowedTariffClassSysNicks = new Array();

		if ( !getRegionNick().inlist([ 'buryatiya', 'ufa', 'kareliya', 'astra' ]) ) { // фильтрация для всех, кроме Уфы, Карелии, Астрахани, Бурятии
			form.findById('lstTariffClass_id').getStore().filterBy(function(rec) {
				return false;
			});

			if ( form.LpuUnitType_id.inlist([ 2, 10, 11, 12 ]) ) {
				form.allowedTariffClassSysNicks = [ 'polkray', 'polinterr', 'polinog', 'bmpolkray', 'bmpolinog', 'disponebase', 'disptwobase',
					'dispchildonebase', 'dispchildtwobase', 'profbase', 'dispchildoneopekbase', 'dispchildtwoopekbase', 'ProfDetBase',
					'PredDetDosh', 'PredDetObch', 'PredDetObr', 'PeriodDetDosh', 'PeriodDetObch', 'PeriodDetObr', 'mvdvoen',
					'disponezlter', 'disptwozlter', 'profzlter', 'disponezlinter', 'disptwozlinter', 'profzlinter', 
					'ProfDetBaseInter','PredDetDoshInter','PredDetObchInter','PredDetObrInter','PeriodDetDoshInter','PeriodDetObchInter','PeriodDetObrInter',
					'PredDetDoshH', 'PredDetObchH', 'PredDetObrH', 'PredDetDoshG', 'PredDetObchG', 'PredDetObrG', 'PredDetDoshH_in', 'PredDetObchH_in',
					'PredDetObrH_in', 'PredDetDoshG_in', 'PredDetObchG_in', 'PredDetObrG_in', 'uetstom', 'uetuslortodont',
					'polvzrzab', 'polvzrpos', 'polvzrnmp', 'poldetzab', 'poldetpos', 'poldetnmp'
				];
			}
			else if ( form.LpuUnitType_id.inlist([ 7 ]) ) {
				form.allowedTariffClassSysNicks = [ 'smedkray', 'bmedkray', 'inogsmed', 'inogbmed', 'mvdvoen' ];
			}
			else if ( form.LpuUnitType_id.inlist([ 1, 6, 9 ]) ) {
				form.allowedTariffClassSysNicks = [ 'stackray', 'stacinog', 'bmstackray', 'bmstacinog', 'mvdvoen' ];
			}
			else {
				form.allowedTariffClassSysNicks = [ 'mvdvoen' ];
			}

			if ( form.LpuUnitType_id.inlist([ 1 ]) ) {
				form.allowedTariffClassSysNicks.push('SBPOMS', 'baset');
			}
		}
		// https://redmine.swan.perm.ru/issues/36870
		else if ( getRegionNick() == 'kareliya' ) {
			form.findById('lstTariffClass_id').getStore().filterBy(function(rec) {
				return false;
			});

			form.disallowedTariffClassSysNicks = [ 'disponebase', 'dispchildonebase', 'prof', 'profchildone', 'Periodchild', 'PredDetDosh', 'PredDetObch', 'PredDetObr' ];
		}

		if (getRegionNick() == 'ufa' && this.LpuUnitType_id.inlist([1,2,6,7,9])) {
			form.findById('lstLpuSectionTariff_TotalFactor').allowBlank = false;
		} else {
			form.findById('lstLpuSectionTariff_TotalFactor').allowBlank = true;
		}
		
		switch (this.action)
		{
			case 'add':
				form.setTitle(lang['tarif_otdeleniya_dobavlenie']);
				break;
			case 'edit':
				form.setTitle(lang['tarif_otdeleniya_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['tarif_otdeleniya_prosmotr']);
				break;
		}
		
		if (this.action=='view')
		{
			form.findById('lstLpuSectionTariff_Tariff').disable();
			form.findById('lstLpuSectionTariff_setDate').disable();
			form.findById('lstLpuSectionTariff_disDate').disable();
			form.findById('lstLpuSectionTariff_TotalFactor').disable();
			form.findById('lstTariffClass_id').disable();
			form.findById('lstLpuSection_id').disable();
			form.findById('lstLpuSectionTariff_id').disable();
			form.buttons[0].disable();
		}
		else
		{
			form.findById('lstLpuSectionTariff_Tariff').enable();
			form.findById('lstLpuSectionTariff_setDate').enable();
			form.findById('lstLpuSectionTariff_disDate').enable();
			form.findById('lstLpuSectionTariff_TotalFactor').enable();
			form.findById('lstTariffClass_id').enable();
			form.findById('lstLpuSection_id').enable();
			form.findById('lstLpuSectionTariff_id').enable();
			form.buttons[0].enable();
		}
		form.findById('lstLpuSection_id').setValue(this.LpuSection_id);
		form.findById('lstLpuSection_Name').setValue(this.LpuSection_Name);
		if (this.action!='add')
		{
			
			form.findById('LpuSectionTariffEditFormPanel').getForm().load(
			{
				url: C_LPUSECTIONTARIFF_GET,
				params:
				{
					object: 'LpuSectionTariff',
					LpuSectionTariff_id: this.LpuSectionTariff_id,
					LpuSectionTariff_Tariff: '',
					lstLpuSectionTariff_TotalFactor: '',
					LpuSectionTariff_setDate: '',
					LpuSectionTariff_disDate: '',
					TariffClass_id: '',
					LpuSection_id: ''
				},
				success: function ()
				{
					form.findById('lstLpuSectionTariff_setDate').fireEvent('change', form.findById('lstLpuSectionTariff_setDate'), form.findById('lstLpuSectionTariff_setDate').getValue());

					if (form.action!='view') {
						form.findById('lstLpuSectionTariff_setDate').focus(true, 100);
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}

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
		//form.findById('lstLpuUnit_Name').setValue(this.LpuUnit_Name);
		form.findById('lstLpuSectionTariff_setDate').focus(true, 100);
		loadMask.hide();
		}
	},
	checkDataExits: function () 
	{
		// Проверка на ранее введенные данные 
		var form = this.findById('LpuSectionTariffEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionTariffEditForm'), { msg: "Подождите, идет проверка..." });
		loadMask.show();
		var params = 
		{
			LpuSectionTariff_id: form.findById('lstLpuSectionTariff_id').getValue(), 
			LpuSection_id: form.findById('lstLpuSection_id').getValue(), 
			TariffClass_id: form.findById('lstTariffClass_id').getValue(), 
			LpuSectionTariff_setDate: Ext.util.Format.date(form.findById('lstLpuSectionTariff_setDate').getValue(),'d.m.Y')
		}
		Ext.Ajax.request(
		{
			url: C_LPUSECTIONTARIFF_CHECK,
			params: params,
			callback: function(options, success, response) 
			{
				loadMask.hide();
				if (success)
				{
					if ( response.responseText.length > 0 )
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result.success)
						{
							sw.swMsg.show(
							{
								buttons: Ext.Msg.OK,
								fn: function() 
								{
									form.findById('lstLpuSectionTariff_setDate').focus(false)
								},
								icon: Ext.Msg.ERROR,
								msg: result.ErrorMessage,
								title: ERR_INVFIELDS_TIT
							});
						}
						else 
						{
							form.ownerCt.submit();
						}
					}
				}
			}
		});
	},
	doSave: function() 
	{
		var form = this.findById('LpuSectionTariffEditFormPanel');
        var begDate = form.findById('lstLpuSectionTariff_setDate').getValue();
        var endDate = form.findById('lstLpuSectionTariff_disDate').getValue();
        var deadlineDate = new Date(2013, 9, 1);

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

		if ((begDate) && (endDate) && (begDate>endDate))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('lstLpuSectionTariff_setDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( getGlobalOptions().region.nick == 'perm' && !Ext.isEmpty(form.findById('lstTariffClass_id').getFieldValue('TariffClass_Code')) ) {
			if ( form.findById('lstTariffClass_id').getFieldValue('TariffClass_Code').toString().inlist([ '17', '18', '21' ]) && (begDate >= deadlineDate) )
			{
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
					},
					icon: Ext.Msg.ERROR,
					msg: lang['dlya_tarifov_s_kodami_17_18_i_21_data_nachala_doljna_byit_menshe_01_10_2013'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( form.findById('lstTariffClass_id').getFieldValue('TariffClass_Code').toString().inlist([ '40', '41', '42', '43', '44', '45' ]) && (begDate < deadlineDate) )
			{
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
					},
					icon: Ext.Msg.ERROR,
					msg: lang['dlya_tarifov_s_kodami_40_41_42_43_44_i_45_data_nachala_doljna_byit_bolshe_01_10_2013'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		form.ownerCt.checkDataExits();
	},
	submit: function()
	{
		var form = this.findById('LpuSectionTariffEditFormPanel');
        var begDate = form.findById('lstLpuSectionTariff_setDate').getValue();
        var deadlineDate = new Date(2013, 10, 1);
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionTariffEditForm'), { msg: "Подождите, идет сохранение..." });

		if ( getGlobalOptions().region.nick == 'perm' && !Ext.isEmpty(form.findById('lstTariffClass_id').getFieldValue('TariffClass_Code')) ) {
			if ( form.findById('lstTariffClass_id').getFieldValue('TariffClass_Code').toString().inlist([ '17', '18', '21' ]) && (begDate >= deadlineDate) ) {
				form.findById('lstLpuSectionTariff_disDate').setValue(new Date(2013, 8, 30));
			}
		}

		loadMask.show();
		form.getForm().submit(
			{
				failure: function(result_form, action)
				{
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
						if (action.result.LpuSectionTariff_id)
						{
							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuSectionTariff_id);
						}
						else
							Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
					}
					else
						Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
				}
			});
	},
	tabIndexBase: 1250,
	initComponent: function()
	{
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');

		this.MainPanel = new sw.Promed.FormPanel({
			id:'LpuSectionTariffEditFormPanel',
			height:this.height,
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			labelWidth: 100,
			region: 'center',
			items: [{
				name: 'LpuSectionTariff_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lstLpuSectionTariff_id'
			}, {
				name: 'LpuSection_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lstLpuSection_id'
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['period_deystviya'],
				labelWidth: 100,
				style: 'padding: 0px;',
				items: [{
					allowBlank: false,
					fieldLabel : lang['nachalo'],
					format: 'd.m.Y',
					id: 'lstLpuSectionTariff_setDate',
					listeners:{
						'change':function (combo, newValue, oldValue) {
							var form = this.MainPanel.getForm();
							form.findField('LpuSectionTariff_disDate').fireEvent('change', form.findField('LpuSectionTariff_disDate'), form.findField('LpuSectionTariff_disDate').getValue());
						}.createDelegate(this),
						'keydown': function (inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					name: 'LpuSectionTariff_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: this.tabIndexBase + 1,
					xtype: 'swdatefield'
				}, {
					fieldLabel : lang['okonchanie'],
					tabIndex: 1224,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionTariff_disDate',
					tabIndex: this.tabIndexBase + 2,
					id: 'lstLpuSectionTariff_disDate',
					listeners: {
						'change':function (field, newValue, oldValue) {
							var base_form = this.MainPanel.getForm();
							var form = this;

							var
								index,
								TariffClass_id = base_form.findField('TariffClass_id').getValue(),
								setDate = base_form.findField('LpuSectionTariff_setDate').getValue();

							// Фильтруем список видов тарифов
							base_form.findField('TariffClass_id').clearValue();
							base_form.findField('TariffClass_id').getStore().clearFilter();
							base_form.findField('TariffClass_id').lastQuery = '';

							if ( !Ext.isEmpty(setDate) || !Ext.isEmpty(newValue) ) {
								base_form.findField('TariffClass_id').getStore().filterBy(function(rec) {
									if (rec.get('TariffClass_IsMap') && rec.get('TariffClass_IsMap') == 2) {
										return false;
									}

									if (
										(form.allowedTariffClassSysNicks.length == 0 || rec.get('TariffClass_SysNick').inlist(form.allowedTariffClassSysNicks))
										&& (form.disallowedTariffClassSysNicks.length == 0 || !rec.get('TariffClass_SysNick').inlist(form.disallowedTariffClassSysNicks))
									) {
										if ( Ext.isEmpty(rec.get('TariffClass_begDT')) && Ext.isEmpty(rec.get('TariffClass_endDT')) ) {
											return true;
										}

										if ( !Ext.isEmpty(setDate) && Ext.isEmpty(newValue) ) {
											return (
												(Ext.isEmpty(rec.get('TariffClass_begDT')) || typeof rec.get('TariffClass_begDT') != 'object' || rec.get('TariffClass_begDT') <= setDate)
												&& (Ext.isEmpty(rec.get('TariffClass_endDT')) || typeof rec.get('TariffClass_endDT') != 'object' || rec.get('TariffClass_endDT') >= setDate)
											);
										}
										else if ( Ext.isEmpty(setDate) && !Ext.isEmpty(newValue) ) {
											return (
												(Ext.isEmpty(rec.get('TariffClass_begDT')) || typeof rec.get('TariffClass_begDT') != 'object' || rec.get('TariffClass_begDT') <= newValue)
												&& (Ext.isEmpty(rec.get('TariffClass_endDT')) || typeof rec.get('TariffClass_endDT') != 'object' || rec.get('TariffClass_endDT') >= newValue)
											);
										}
										else {
											return (
												(Ext.isEmpty(rec.get('TariffClass_begDT')) || typeof rec.get('TariffClass_begDT') != 'object' || (rec.get('TariffClass_begDT') <= newValue && rec.get('TariffClass_begDT') <= setDate))
												&& (Ext.isEmpty(rec.get('TariffClass_endDT')) || typeof rec.get('TariffClass_endDT') != 'object' || (rec.get('TariffClass_endDT') >= newValue && rec.get('TariffClass_endDT') >= setDate))
											);
										}
									}
									else {
										return false;
									}
								});
							}

							index = base_form.findField('TariffClass_id').getStore().findBy(function(rec) {
								return (rec.get('TariffClass_id') == TariffClass_id);
							});
							
							if ( index >= 0 ) {
								base_form.findField('TariffClass_id').setValue(TariffClass_id);
							}
						}.createDelegate(this)
					}
				}]
			}, {
				name: 'LpuSection_Name',
				disabled: true,
				fieldLabel: lang['otdelenie'],
				tabIndex: this.tabIndexBase + 3,
				xtype: 'descfield',
				id: 'lstLpuSection_Name'
			}, {
				anchor: '100%',
				disabled: false,
				name: 'TariffClass_id',
				xtype: 'swtariffclasscombo',
				id: 'lstTariffClass_id',
				lastQuery: '',
				allowBlank:false,
				listWidth: 500,
				enableKeyEvents: true,
				tabIndex: this.tabIndexBase + 4
			},
			{
				xtype: 'numberfield',
				tabIndex: this.tabIndexBase + 5,
				name: 'LpuSectionTariff_Tariff',
				id:  'lstLpuSectionTariff_Tariff',
				maxValue: 999999,
				minValue: 0,
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				allowBlank: false,
				fieldLabel: lang['tarif']
			}, {
				fieldLabel: lang['itogovyiy_koeffitsient'],
				tabIndex: this.tabIndexBase + 6,
				decimalPrecision: 9,
				hidden: !getRegionNick().inlist(['ufa']),
				hideLabel: !getRegionNick().inlist(['ufa']),
				xtype: 'numberfield',
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				name: 'LpuSectionTariff_TotalFactor',
				id: 'lstLpuSectionTariff_TotalFactor'
			}],
			reader: new Ext.data.JsonReader({
				success: function() {
				//alert('success');
				}
			}, [
				{ name: 'LpuSectionTariff_id' },
				{ name: 'LpuSection_id' },
				{ name: 'TariffClass_id' },
				{ name: 'LpuSectionTariff_Tariff' },
				{ name: 'LpuSectionTariff_TotalFactor'},
				{ name: 'LpuSectionTariff_setDate' },
				{ name: 'LpuSectionTariff_disDate' }
			]),
			url: C_LPUSECTIONTARIFF_SAVE
		});

		Ext.apply(this, {
			xtype: 'panel',
			border: false,
			buttons: [{
				text: BTN_FRMSAVE,
				id: 'lstOk',
				tabIndex: this.tabIndexBase + 7,
				iconCls: 'save16',
				handler: function() {
					this.doSave();
				}.createDelegate(this)
			}, {
				text:'-'
			},  {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				tabIndex: this.tabIndexBase + 8,
				handler: function(button, event) {
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCANCEL,
				id: 'lstCancel',
				tabIndex: this.tabIndexBase + 9,
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					var base_form = this.MainPanel.getForm();

					if ( !base_form.findField('LpuSectionTariff_setDate').disabled ) {
						base_form.findField('LpuSectionTariff_setDate').focus(true);
					}
				}.createDelegate(this),
				handler: function() {
					this.hide();
					this.returnFunc(this.owner, -1);
				}.createDelegate(this)
			}],
			items: [
				this.MainPanel
			]
		});

		sw.Promed.swLpuSectionTariffEditForm.superclass.initComponent.apply(this, arguments);
	}
});