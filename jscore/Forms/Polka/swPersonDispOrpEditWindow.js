/**
* swPersonDispOrpEditWindow - окно "Регистр детей-сирот (стационарных): Добавление / Редактирование"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      21.05.2013
*/

sw.Promed.swPersonDispOrpEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function(options) {
		if ( this.action == 'view' ) {
			return false;
		}

		var win = this,
			base_form = this.FormPanel.getForm(),
			org_field = base_form.findField('Org_id'),
			Org_EndDT = win.FormPanel.getForm().findField('Org_id').getFieldValue('Org_endDate');

		var orpInCorrectOrgType = false;
		if(options && options.orpInCorrectOrgType){
			orpInCorrectOrgType = true;
		}

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (Ext.isEmpty(win.FormPanel.getForm().findField('Org_id').getFieldValue('OrgStac_Code')) && this.CategoryChildType === 'orp' && getRegionNick() !== 'ufa') {
			sw.swMsg.show({
				icon: Ext.Msg.ERROR,
				msg: lang['u_vyibrannogo_obrazovatelnogo_uchrejdeniya_otsutstvuet_federalnyiy_kod_sohranenie_nevozmojno'],
				title: lang['oshibka'],
				buttons: Ext.Msg.OK,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						win.doSave();
					}
				}
			});
			return false;
		}


		if (win.CategoryChildType == 'orpadopted') {
			if (
				!Ext.isEmpty(org_field.getValue())
				&& this.orpAdoptedMOEmptyCode
				&& Ext.isEmpty(org_field.getFieldValue('OrgStac_Code'))
				&& (Ext.isEmpty(org_field.getFieldValue('Org_Name')) || Ext.isEmpty(org_field.getFieldValue('OrgType_Name')) || Ext.isEmpty(org_field.getFieldValue('Org_Address')))
			) {

				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: lang['u_vyibrannogo_obrazovatelnogo_uchrejdeniya_otsutstvuet_federalnyiy_kod_ili_ne_zapolnenyi_vse_perechislennyie_atributyi_naimenovanie_yuridicheskiy_adres_tip_organizatsii'],
					title: lang['preduprejdenie'],
					buttons: {yes: lang['sohranit'], no: lang['otmena']},
					fn: function (buttonId, text, obj) {
						if ('yes' == buttonId) {
							win.orpAdoptedMOEmptyCode = false;
							win.doSave();
						}
					}
				});
				return false;
			}
		}

		if (!Ext.isEmpty(Org_EndDT) && (this.orpAdoptedMODateincorrect || this.CategoryChildType === 'orp') && getRegionNick() !== 'ufa') {
			var rOrg_EndDT = Org_EndDT.split('.').reverse().join('.'),
				curDate = new Date(),
				CurYear = curDate.getFullYear(),
				firstDayOfYear = ('01.01.'+CurYear.toString()).split('.').reverse().join('.');

			if ( !Ext.isEmpty(rOrg_EndDT) && rOrg_EndDT < firstDayOfYear) {
				var msg = 'У выбранного образовательного учреждения указана дата закрытия '+ Org_EndDT +'. Сохранить?',
					icon = Ext.MessageBox.QUESTION,
					title = 'Вщпрос',
					buttons = Ext.Msg.YESNO;

				if (this.CategoryChildType === 'orp'){
					msg = lang['u_vyibrannogo_obrazovatelnogo_uchrejdeniya_ukazana_data_zakryitiya_menshe_pervogo_yanvarya_tekuschego_goda_sohranenie_nevozmojno'];
					icon = Ext.MessageBox.ERROR;
					title = lang['oshibka'];
					buttons = Ext.Msg.OK;
				}

				sw.swMsg.show({
					icon: icon,
					msg: msg,
					title: title,
					buttons: buttons,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							win.orpAdoptedMODateincorrect = false;
							win.doSave();
						}
					}
				});
				return false;
			}
		}

		if(!orpInCorrectOrgType && !Ext.isEmpty(org_field.getFieldValue('OrgType_SysNick')) && !org_field.getFieldValue('OrgType_SysNick').inlist(['preschool','secschool','proschool','highschool'])){
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: 'У выбранного образовательного учреждения указан неверный тип организации.',
				title: lang['preduprejdenie'],
				buttons: {yes: lang['sohranit'], no: lang['otmena']},
				fn: function (buttonId, text, obj) {
					if ('yes' == buttonId) {
						var options = {orpInCorrectOrgType:true};
						win.doSave(options);
					}
				}
			});
			return false;
		}

		var params = {};
		
		win.getLoadMask("Подождите, идет сохранение...").show();
		base_form.submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide();
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				win.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.PersonDispOrp_id ) {
						if (win.action == 'add') {
							sw.swMsg.alert(lang['soobschenie'], lang['patsient_uspeshno_dobavlen_v_registr']);
						}
						win.callback();
						win.hide();
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	height: 400,
	id: 'PersonDispOrpEditWindow',
	initComponent: function() {
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'PersonDispOrpEditForm',
			labelAlign: 'right',
			labelWidth: 250,
			reader: new Ext.data.JsonReader({
				success: function() {
				}
			}, [
				{ name: 'PersonDispOrp_id' },
				{ name: 'Person_id' },
				{ name: 'Server_id' },
				{ name: 'CategoryChildType_id' },
				{ name: 'Org_id' },
				//{ name: 'OrgExist' },
				{ name: 'PersonDispOrp_Year' },
				{ name: 'PersonDispOrp_setDate' },
				{ name: 'DisposalCause_id' },
				{ name: 'PersonDispOrp_DisposDate' }
			]),
			region: 'center',
			url: '/?c=PersonDispOrp13&m=savePersonDispOrp',
			items: [
				{
					name: 'PersonDispOrp_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'PersonDispOrp_Year',
					xtype: 'hidden'				
				},
				{
					allowBlank: false,
					comboSubject: 'CategoryChildType',
					fieldLabel: lang['kategoriya_ucheta_nesovershennoletnego'],
					hiddenName: 'CategoryChildType_id',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				}, /*{
					fieldLabel: lang['obuchayuschiysya'],
					hiddenName: 'OrgExist',
					allowBlank: false,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = win.FormPanel.getForm();
							
							if (newValue == 2) {
								base_form.findField('Org_id').setAllowBlank(false);
								base_form.findField('Org_id').enable();
							} else {
								base_form.findField('Org_id').setAllowBlank(true);
								base_form.findField('Org_id').clearValue();
								base_form.findField('Org_id').disable();
							}
						}
					},
					xtype: 'swyesnocombo'
				},*/ {
					editable: false,
					allowBlank: true,
					enableKeyEvents: true,
					fieldLabel: lang['statsionarnoe_uchrejdenie'],
					hiddenName: 'Org_id',
					triggerAction: 'none',
					needOrgType: true,
					width: 300,
					xtype: 'sworgcombo',
					onTrigger1Click: function() {
						var combo = this;
						if (combo.disabled) {
							return false;
						}
						getWnd('swOrgSearchWindow').show({
							object: 'org', //(win.CategoryChildType == 'orpadopted' ? 'orgstaceducation' : 'orgstac'),
							showOrgStacFilters : true,
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 )
								{
									combo.getStore().load({
										params: {
											OrgType: 'org', //(win.CategoryChildType == 'orpadopted' ? 'orgstaceducation' : 'orgstac'),
											Org_id: orgData.Org_id,
											Org_Name:''
										},
										callback: function()
										{
											combo.setValue(orgData.Org_id);
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
										}
									});
								}
								Ext.get('swOrgSearchWindow').hide();
								//getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					}
				}, {
					fieldLabel: lang['data_postupleniya'],
					format: 'd.m.Y',
					name: 'PersonDispOrp_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}, {
					allowBlank: true,
					comboSubject: 'DisposalCause',
					fieldLabel: lang['prichina_vyibyitiya'],
					hiddenName: 'DisposalCause_id',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();
							if (newValue > 0) {
								base_form.findField('PersonDispOrp_DisposDate').enable();
								base_form.findField('PersonDispOrp_DisposDate').setAllowBlank(false);
							} else {
								base_form.findField('PersonDispOrp_DisposDate').setValue('');
								base_form.findField('PersonDispOrp_DisposDate').disable();
								base_form.findField('PersonDispOrp_DisposDate').setAllowBlank(true);
							}
						}.createDelegate(this),
						'select': function (combo, record) {
							var base_form = this.FormPanel.getForm();
							if (combo.getValue() > 0) {
								base_form.findField('PersonDispOrp_DisposDate').enable();
								base_form.findField('PersonDispOrp_DisposDate').setAllowBlank(false);
							} else {
								base_form.findField('PersonDispOrp_DisposDate').setValue('');
								base_form.findField('PersonDispOrp_DisposDate').disable();
								base_form.findField('PersonDispOrp_DisposDate').setAllowBlank(true);
							}
						}.createDelegate(this)
					}
				}, {
					fieldLabel: lang['data_vyibyitiya'],
					format: 'd.m.Y',
					name: 'PersonDispOrp_DisposDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield',
					disabled: true
				}
			]
		});
		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				if (this.action == 'view') {
					this.buttons[this.buttons.length - 1].focus();
				} else {
					this.FormPanel.getForm().findField('CategoryChildType_id').focus(true);
				}
			}.createDelegate(this),
			button2Callback: function(callback_data) {
				this.FormPanel.getForm().findField('Server_id').setValue(callback_data.Server_id);
				this.PersonInfo.load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
			}.createDelegate(this),
			button2OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button3OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button4OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button5OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			collapsible: true,
			collapsed: false,
			floatable: false,
			id: 'PDEF_PersonInformationFrame',
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north',
			title: lang['zagruzka'],
			titleCollapse: true
		});
		
		Ext.apply(this, {
			buttons: [
				{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					onShiftTabAction: function () {
						var base_form = this.FormPanel.getForm();
					}.createDelegate(this),
					onTabAction: function () {
						this.buttons[this.buttons.length - 1].focus(true);
					}.createDelegate(this),
					tabIndex: 12613,
					text: BTN_FRMSAVE
				},
				'-',
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						// this.buttons[1].focus(true);
						this.buttons[0].focus(true);
					}.createDelegate(this),
					onTabAction: function () {
						if (this.action != 'view') {
							this.FormPanel.getForm().findField('CategoryChildType_id').focus(true);
						} else {
							this.buttons[1].focus(true);
						}
					}.createDelegate(this),
					tabIndex: 12615,//todo
					text: BTN_FRMCANCEL
				}
			],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'border'
		});
		sw.Promed.swPersonDispOrpEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners:	{
		'hide':	function() {
			this.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	params: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPersonDispOrpEditWindow.superclass.show.apply(this, arguments);
		this.restore();
		this.center();
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.PersonInfo.setTitle('...');
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}
		
		// фильтруем поле "Категория учета несовершеннолетнего"
		var combo = base_form.findField('CategoryChildType_id');
		if (arguments[0] && arguments[0].CategoryChildType) {
			this.CategoryChildType = arguments[0].CategoryChildType;
		} else {
			this.CategoryChildType = 'orp';
		}
		
		if (this.CategoryChildType == 'orpadopted') {
			this.orpAdoptedMOEmptyCode = true;
			this.orpAdoptedMODateincorrect = true;
			this.wintitle = lang['registr_detey-sirot_usyinovlennyih_opekaemyih'];
			
			combo.getStore().clearFilter();
			combo.lastQuery = '';
			combo.getStore().filterBy(function(record) {
				return (record.get('CategoryChildType_id').inlist([5,6,7]));
			});
			/*base_form.findField('OrgExist').showContainer();
			base_form.findField('OrgExist').setValue(2);
			base_form.findField('OrgExist').setAllowBlank(false);*/
			base_form.findField('Org_id').setAllowBlank(true);
			base_form.findField('Org_id').setFieldLabel(lang['obrazovatelnoe_uchrejdenie']);
			base_form.findField('PersonDispOrp_setDate').setAllowBlank(true);
			base_form.findField('DisposalCause_id').setContainerVisible(false);
			base_form.findField('PersonDispOrp_DisposDate').setContainerVisible(false);
		} else {
			this.wintitle = lang['registr_detey-sirot_statsionarnyih'];

			combo.getStore().clearFilter();
			combo.lastQuery = '';
			combo.getStore().filterBy(function(record) 
			{
				return (record.get('CategoryChildType_id').inlist([1,2,3,4]));
			});
			/*base_form.findField('OrgExist').hideContainer();
			base_form.findField('OrgExist').setAllowBlank(true);*/
			base_form.findField('Org_id').setAllowBlank(getRegionNick().inlist(['ekb']));
			base_form.findField('Org_id').setFieldLabel(lang['statsionarnoe_uchrejdenie']);
			base_form.findField('PersonDispOrp_setDate').setAllowBlank(getRegionNick().inlist(['ekb']));
			base_form.findField('DisposalCause_id').setContainerVisible(true);
			base_form.findField('PersonDispOrp_DisposDate').setContainerVisible(true);
		}
		
		base_form.setValues(arguments[0].formParams);

		base_form.findField('PersonDispOrp_setDate').setMaxValue(undefined);
		
		this.PersonInfo.load({
			callback: function(params) {
				this.PersonInfo.setPersonTitle();
			}.createDelegate(this),
			Person_id: base_form.findField('Person_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue()
		});
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		var _this = this;
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.wintitle + lang['_dobavlenie']);
				this.enableEdit(true);

				/*if (this.CategoryChildType == 'orpadopted') {
					base_form.findField('OrgExist').fireEvent('change', base_form.findField('OrgExist'), base_form.findField('OrgExist').getValue());
				}*/

				if ( !Ext.isEmpty(base_form.findField('PersonDispOrp_Year').getValue()) ) {
					base_form.findField('PersonDispOrp_setDate').setMaxValue('31.12.' + base_form.findField('PersonDispOrp_Year').getValue());
				}

				Ext.Ajax.request({
					failure: function(response, options) {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
					},
					params: {
						 CategoryChildType: this.CategoryChildType
						,Person_id: base_form.findField('Person_id').getValue()
						,PersonDispOrp_Year: base_form.findField('PersonDispOrp_Year').getValue()
					},
					success: function(response, options) {
						loadMask.hide();

						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( typeof response_obj == 'object' && response_obj.length == 1 ) {
							if ( !Ext.isEmpty(response_obj[0].PersonDispOrp_setDate) ) {
								base_form.findField('PersonDispOrp_setDate').setValue(response_obj[0].PersonDispOrp_setDate);
							}

							if ( !Ext.isEmpty(response_obj[0].Org_id) ) {
								var orgcombo = base_form.findField('Org_id');

								orgcombo.getStore().load({
									params: {
										OrgType: 'org', //(this.CategoryChildType == 'orpadopted' ? 'orgstaceducation' : 'orgstac'),
										Org_id: response_obj[0].Org_id
									},
									callback: function() {
										orgcombo.setValue(response_obj[0].Org_id);
										orgcombo.fireEvent('change', orgcombo, response_obj[0].Org_id);
									}
								});
							}
						}

						base_form.findField('DisposalCause_id').fireEvent('change', base_form.findField('DisposalCause_id'), base_form.findField('DisposalCause_id').getValue());

						base_form.markInvalid();

						base_form.findField('CategoryChildType_id').focus(true, 250);
					}.createDelegate(this),
					url: '/?c=PersonDispOrp13&m=getPersonDispOrpLastYearData'
				});
			break;

			case 'edit':
			case 'view':
				var person_disp_orp_id = base_form.findField('PersonDispOrp_id').getValue();
				if (!person_disp_orp_id) {
					loadMask.hide();
					this.hide();
					return false;
				}
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {
							this.hide();
						}.createDelegate(this));
					}.createDelegate(this),
					params: {
						'PersonDispOrp_id': person_disp_orp_id
					},
					success: function() {
						loadMask.hide();
						if (this.action == 'edit') {
							this.setTitle(this.wintitle + lang['_redaktirovanie']);
							this.enableEdit(true);
						} else {
							this.setTitle(this.wintitle + lang['_prosmotr']);
							this.enableEdit(false);
						}

						if ( !Ext.isEmpty(base_form.findField('PersonDispOrp_Year').getValue()) ) {
							base_form.findField('PersonDispOrp_setDate').setMaxValue('31.12.' + base_form.findField('PersonDispOrp_Year').getValue());
						}

						/*if (this.CategoryChildType == 'orpadopted') {
							base_form.findField('OrgExist').fireEvent('change', base_form.findField('OrgExist'), base_form.findField('OrgExist').getValue());
						}*/
						
						var orgcombo = base_form.findField('Org_id');
						if (!Ext.isEmpty(orgcombo.getValue())) {
							orgcombo.getStore().load({
								params: {
									OrgType: 'org', //(this.CategoryChildType == 'orpadopted' ? 'orgstaceducation' : 'orgstac'),
									Org_id: orgcombo.getValue(),
									Org_Name:''
								},
								callback: function()
								{
									orgcombo.setValue(orgcombo.getValue());
									orgcombo.fireEvent('change', orgcombo);
								}
							});
						}
						
						var disposalcombo = base_form.findField('DisposalCause_id');
						disposalcombo.fireEvent('change', disposalcombo, disposalcombo.getValue());
									
						base_form.markInvalid();
						
						if (this.action == 'edit') {
							base_form.findField('CategoryChildType_id').focus(true, 250);
						} else {
							this.buttons[this.buttons.length - 1].focus();
						}
						_this.PersonInfo.setReadOnly(_this.action == 'view');
					}.createDelegate(this),
					url: '/?c=PersonDispOrp13&m=loadPersonDispOrpEditForm'
				});
				break;
			default:
				loadMask.hide();
				this.hide();
				break;
		}
	},
	width: 600
});