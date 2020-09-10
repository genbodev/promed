/**
* swPersonDispOrpPeriodEditWindow - окно "Регистр детей-сирот (с 2013г.): Добавление / Редактирование"
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

sw.Promed.swPersonDispOrpPeriodEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	getDataForCallBack: function()
	{
		var win = this;
		var base_form = win.FormPanel.getForm();
		var personinfo = win.PersonInfo;
		
		var response = new Object();

		response.PersonDispOrp_id = base_form.findField('PersonDispOrp_id').getValue();
		response.Person_id = base_form.findField('Person_id').getValue();
		response.Server_id = base_form.findField('Server_id').getValue();
		response.PersonEvn_id = personinfo.getFieldValue('PersonEvn_id');
		response.Person_Surname = personinfo.getFieldValue('Person_Surname');
		response.Person_Firname = personinfo.getFieldValue('Person_Firname');
		response.Person_Secname = personinfo.getFieldValue('Person_Secname');
		response.Person_Birthday = personinfo.getFieldValue('Person_Birthday');
		response.Sex_Name = personinfo.getFieldValue('Sex_Name');
		response.ua_name = personinfo.getFieldValue('Person_RAddress');
		response.pa_name = personinfo.getFieldValue('Person_PAddress');
		response.Lpu_Nick = personinfo.getFieldValue('Lpu_Nick');
		response.EducationInstitutionType_Name = base_form.findField('EducationInstitutionType_id').getFieldValue('EducationInstitutionType_Name');
		response.ExistsDirection = null;
		response.ExistsDOPL = null;
		response.EducationInstitutionType_id = base_form.findField('EducationInstitutionType_id').getValue();
		response.Org_id = base_form.findField('Org_id').getValue();
		response.EvnPLDispTeenInspection_id = null;
				
		return response;
	},
	doSave: function(options) {
		// options @Object
		if ( this.action == 'view' ) {
			return false;
		}

		var win = this;
		var base_form = this.FormPanel.getForm();
		var org_field = base_form.findField('Org_id');

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

		if (
			this.orpAdoptedMOEmptyCode
			&& Ext.isEmpty(org_field.getFieldValue('OrgStac_Code'))
			&& (Ext.isEmpty(org_field.getFieldValue('Org_Name')) || Ext.isEmpty(org_field.getFieldValue('OrgType_Name')) || Ext.isEmpty(org_field.getFieldValue('Org_Address')))
		) {

			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: lang['u_vyibrannogo_obrazovatelnogo_uchrejdeniya_otsutstvuet_federalnyiy_kod_ili_ne_zapolnenyi_vse_perechislennyie_atributyi_naimenovanie_yuridicheskiy_adres_tip_organizatsii'],
				title: lang['preduprejdenie'],
				buttons: {yes: lang['sohranit'], no: lang['otmena']},
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						win.orpAdoptedMOEmptyCode = false;
						win.doSave();
					}
				}
			});
			return false;
		}

		var Org_EndDT = win.FormPanel.getForm().findField('Org_id').getFieldValue('Org_endDate');

		if (!Ext.isEmpty(Org_EndDT)) {
			var rOrg_EndDT = Org_EndDT.split('.').reverse().join('.'),
				curDate = new Date(),
				CurYear = curDate.getFullYear(),
				firstDayOfYear = ('01.01.'+CurYear.toString()).split('.').reverse().join('.');

				if ( !Ext.isEmpty(rOrg_EndDT) && rOrg_EndDT < firstDayOfYear && this.orpAdoptedMODateincorrect) {
				var msg = 'У выбранного образовательного учреждения указана дата закрытия '+ Org_EndDT +'. Сохранить?';

				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: msg,
					title: lang['vopros'],
					buttons: Ext.Msg.YESNO,
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
						base_form.findField('PersonDispOrp_id').setValue(action.result.PersonDispOrp_id);
						win.callback({personDispOrpData: win.getDataForCallBack()});
						if (win.action == 'add') {
							sw.swMsg.alert(lang['soobschenie'], lang['patsient_uspeshno_dobavlen_v_registr']);
						}
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
	height: 310,
	id: 'PersonDispOrpPeriodEditWindow',
	initComponent: function() {
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'PersonDispOrpPeriodEditForm',
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
				{ name: 'EducationInstitutionType_id' },
				{ name: 'Org_id' },
				{ name: 'PersonDispOrp_Year' }
			]),
			region: 'center',
			url: '/?c=PersonDispOrp13&m=savePersonDispOrp',
			items: [
				{
					name: 'PersonDispOrp_id',
					xtype: 'hidden'
				},
				{
					name: 'Person_id',
					xtype: 'hidden'
				},
				{
					name: 'Server_id',
					xtype: 'hidden'
				},
				{
					name: 'PersonDispOrp_Year',
					xtype: 'hidden'
				},
				{
					name: 'CategoryChildType_id',
					value: 8,
					xtype: 'hidden'
				},
				{
					allowBlank: false,
					comboSubject: 'EducationInstitutionType',
					fieldLabel: lang['tip_obrazovatelnogo_uchrejdeniya'],
					hiddenName: 'EducationInstitutionType_id',
					lastQuery: '',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = win.FormPanel.getForm();
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});

							base_form.findField('Org_id').clearValue();

							if ( index >= 0 ) {
								base_form.findField('Org_id').enable();
							}
							else {
								base_form.findField('Org_id').disable();
							}
						}
					},
					width: 300,
					xtype: 'swcommonsprcombo'
				},
				{
					editable: false,
					allowBlank: getRegionNick().inlist(['ekb']),
					enableKeyEvents: true,
					fieldLabel: lang['obrazovatelnoe_uchrejdenie'],
					hiddenName: 'Org_id',
					triggerAction: 'none',
					needOrgType: true,
					width: 300,
					xtype: 'sworgcombo',
					onTrigger1Click: function() {
						var base_form = win.FormPanel.getForm();
						var combo = this;

						if (combo.disabled) {
							return false;
						}
						var OrgType_id;

						if ( !Ext.isEmpty(base_form.findField('EducationInstitutionType_id').getValue()) ) {
							switch ( Number(base_form.findField('EducationInstitutionType_id').getFieldValue('EducationInstitutionType_Code')) ) {
								case 1:
									OrgType_id = 7;
								break;

								case 2:
									OrgType_id = 8;
								break;

								case 3:
									OrgType_id = 9;
								break;
							}
						}
						getWnd('swOrgSearchWindow').show({
							enableOrgType: true,
							OrgType_id: OrgType_id,
							showOrgStacFilters : true,
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 )
								{
									combo.getStore().load({
										params: {
											Object:'Org',
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
								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					}
				}
			]
		});
		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				if (this.action == 'view') {
					this.buttons[this.buttons.length - 1].focus();
				} else {
					this.FormPanel.getForm().findField('Org_id').focus(true);
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
			id: 'PDOPEREF_PersonInformationFrame',
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
							this.FormPanel.getForm().findField('Org_id').focus(true);
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
		sw.Promed.swPersonDispOrpPeriodEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners:	{
		'hide':	function() {
			this.onHide();
		}
	},
	maximizable: true,
	modal: true,
	onHide: Ext.emptyFn,
	params: null,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swPersonDispOrpPeriodEditWindow.superclass.show.apply(this, arguments);
		this.restore();
		this.center();
		this.orpAdoptedMOEmptyCode = true;
		this.orpAdoptedMODateincorrect = true;
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
		
		this.wintitle = lang['registr_periodicheskih_osmotrov_nesovershennoletnih'];
		base_form.setValues(arguments[0].formParams);
		
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
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		var _this = this;
		switch (this.action) {
			case 'add':
				this.setTitle(this.wintitle + lang['_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
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

						var orgcombo = base_form.findField('Org_id');
						if (!Ext.isEmpty(orgcombo.getValue())) {
							orgcombo.getStore().load({
								params: {
									Object:'Org',
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

						base_form.clearInvalid();
						
						if (this.action == 'edit') {
							base_form.findField('EducationInstitutionType_id').focus(true, 250);
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