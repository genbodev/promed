/**
* swRegistryDataPersonEditWindow - окно редактирования персональных данных.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      24.02.2009
*/

sw.Promed.swRegistryDataPersonEditWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'fit',
	width: 600,
	modal: true,
	resizable: false,
	draggable: false,
	autoHeight: true,
	closeAction :'hide',
	plain: true,
	id: 'RegistryDataPersonEditWindow',
	onClose: Ext.emptyFn,
	returnFunc: Ext.emptyFn,
	personId: 0,
	action: 'edit',
	title: lang['chelovek_v_reestre'],
	listeners: {
		'hide': function() {this.onClose()}
	},
	disableEdit: function(disable) {
		var form = this.findById('rd_person_edit_form');
		if ( disable === false )
		{
			form.enable();
			this.buttons[0].enable();
		}
		else
		{
			var vals = form.getForm().getValues();
			for ( value in vals )
			{
				form.getForm().findField(value).disable();
				this.buttons[0].disable();
			}
		}
	},
	doSubmit: function() {
		if ( this.readOnly )
			return;
		var window = this;
		var post = {Server_id: window.serverId};
		var form = this.findById('rd_person_edit_form').getForm();
		var evnId = null;
		if (this.findById('RDPEW_onEvn').getValue()==1)
		{
			evnId = this.Evn_id;
		}
		post['Evn_id'] = evnId;
		this.findById('RDPEW_Evn_id').setValue(evnId);
		this.findById('rd_person_edit_form').getForm().submit(
		{
			params: post,
			success: function(form, action) {
				window.hide();
			},
			failure: function (form, action)
			{
				//Ext.Msg.alert("Ошибка", action.result.msg);
			}
		});
	},
	disablePolisFields: function(disable, unclear)
	{
		if (this.readOnly)
			return;
		var form = this.findById('rd_person_edit_form');
		if ( disable == true )
		{
			form.getForm().findField('OrgSMO_id').disable();
			form.getForm().findField('Polis_Ser').disable();
			form.getForm().findField('Polis_Num').disable();
			form.getForm().findField('PolisType_id').disable();
			if (unclear != true)
			{
				form.getForm().findField('OrgSMO_id').clearValue();
				form.getForm().findField('Polis_Ser').setRawValue('');
				form.getForm().findField('Polis_Num').setRawValue('');
				form.getForm().findField('PolisType_id').clearValue();
			}
		}
		else
		{
			form.getForm().findField('OrgSMO_id').enable();
			form.getForm().findField('Polis_Ser').enable();
			form.getForm().findField('Polis_Num').enable();
			form.getForm().findField('PolisType_id').enable();
		}
	},
	
	show: function() 
	{
		var base_form = this.findById('rd_person_edit_form').getForm();
		var form = this.findById('rd_person_edit_form'),
			_this = this;

		this.personId = 0;
		this.readOnly = false;
		this.serverId = 0;

		if ( arguments[0] )
		{
			if ( arguments[0].action )
				this.action = arguments[0].action;

			if ( arguments[0].callback )
				this.returnFunc = arguments[0].callback;

			if ( arguments[0].fields )
				base_form.setValues(arguments[0].fields);

			if ( arguments[0].onClose )
				this.onClose = arguments[0].onClose;

			if ( arguments[0].Evn_id )
				this.Evn_id = arguments[0].Evn_id;
				
			if ( arguments[0].Person_id )
				this.personId = arguments[0].Person_id;
				

			if ( arguments[0].readOnly )
				this.readOnly = arguments[0].readOnly;

			if ( arguments[0].Server_id )
				this.serverId = arguments[0].Server_id;
		}

		sw.Promed.swRegistryDataPersonEditWindow.superclass.show.apply(this, arguments);

		if (!this.readOnly)
			this.disableEdit(false);
		else
			this.disableEdit(true);

		if (this.action == 'add')
			this.setTitle(lang['chelovek_v_reestre_dobavlenie']);
			
		if (this.action == 'edit')
			if (!this.readOnly)
			{
				this.setTitle(lang['chelovek_v_reestre_redaktirovanie']);
			}
			else
			{
				this.setTitle(lang['chelovek_v_reestre_prosmotr']);
			}
		this.disablePolisFields(true);
		
		if ( this.action != 'add' ) 
		{
			var Mask = new Ext.LoadMask(this.getEl(), { msg:"Пожалуйста, подождите, идет загрузка данных формы..." });
			Mask.show();
		}
		
		base_form.reset();
		if (this.Evn_id>0)
		{ 
			this.findById('RDPEW_onEvn').setValue(1);
		}
		
		if ( this.action != 'add' ) 
		{
			this.disablePolisFields(false);
			base_form.load(
			{
				failure: function() {
					Mask.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { this.hide(); }.createDelegate(this));
				}.createDelegate(this),
				params: 
				{
					Person_id: this.personId,
					Server_id: this.serverId,
					Evn_id: this.Evn_id
				},
				success: function(fm) 
				{
					if ( !form.findById('RDPEW_Person_SurName').disabled )
						form.findById('RDPEW_Person_SurName').focus(true, 300);
					if (form.findById('RDPEW_Evn_id').getValue()>0)
					{ 
						this.findById('RDPEW_onEvn').setValue(1);
					}
					else 
					{
						this.findById('RDPEW_onEvn').setValue(0);
					}
					
					Mask.hide();
					form.ownerCt.disablePolisFields(false);
					if ( base_form.findField('OMSSprTerr_id').getValue() > 0 )
					{
						var combo = base_form.findField('OMSSprTerr_id');
						var OrgSMOCombo	= base_form.findField('OrgSMO_id');
						OrgSMOCombo.lastQuery = '';
						var number = combo.getValue();
						var idx = -1;
						var findIndex = 0;
						combo.getStore().findBy(function(r) 
						{
							if ( r.data['OMSSprTerr_id'] == number )
							{
								idx = findIndex;
								return true;
							}
							findIndex++;
						});
						if ( idx >= 0 )
						{
							var code = combo.getStore().getAt(idx).data.OMSSprTerr_Code;
							if ( code <= 61 ) 
							{
								base_form.findField('Polis_Ser').disableTransPlug = true;
								base_form.findField('Polis_Ser').setAllowBlank(true);
							}
							else
							{
								base_form.findField('Polis_Ser').disableTransPlug = true;
								base_form.findField('Polis_Ser').setAllowBlank(true);
							}
							if ( code < 100 )
							{
								OrgSMOCombo.baseFilterFn = function(record) {
									if ( /.+/.test(record.get('OrgSMO_RegNomC')) )
										return true;
									else
										return false;
								}
								OrgSMOCombo.getStore().filter('OrgSMO_RegNomC', /.+/);
							}
							else
							{
								OrgSMOCombo.baseFilterFn = null;
								OrgSMOCombo.getStore().filter('OrgSMO_RegNomC', '');
							}
							OrgSMOCombo.setValue(OrgSMOCombo.getValue());
						}
						_this.filterOrgSMOCombo();
					}
					fm.clearInvalid();
					
				}.createDelegate(this),
				url: '/?c=Registry&m=getPersonEdit'
			});
		}

		if ( arguments[0].fields ) {
			this.findById('rd_person_edit_form').getForm().setValues(arguments[0].fields);
		}

		if ( this.action == 'add' ) {
			base_form.findField('Person_SurName').focus(true, 500);
		}

		base_form.clearInvalid();
		base_form.findField('Polis_Ser').disableTransPlug = true;
		base_form.findField('Polis_Ser').setAllowBlank(true);
	},
	filterOrgSMOCombo: function() {
		var base_form = this.findById('rd_person_edit_form').getForm(),
			KLRgn_id = base_form.findField('OMSSprTerr_id').getFieldValue('KLRgn_id'),
			OrgSMOCombo = base_form.findField('OrgSMO_id');

		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == KLRgn_id);
		});
		OrgSMOCombo.lastQuery = lang['stroka_kotoruyu_nikto_ne_dodumaetsya_vvodit_v_kachestve_filtra_ibo_eto_bred_iskat_smo_po_takoy_stroke'];
		OrgSMOCombo.setBaseFilter(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == KLRgn_id);
		});
	},
	initComponent: function() {
		var _this = this;
		Ext.apply(this, {
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyStyle: 'padding:2px',
				buttonAlign: 'left',
				frame: true,
				id: 'rd_person_edit_form',
				labelAlign: 'right',
				labelWidth: 125,
				url: '/?c=Registry&m=savePersonEdit',
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'Person_id' },
					{ name: 'Server_id' },
					{ name: 'Evn_id' },
					{ name: 'Person_SurName' },
					{ name: 'Person_SecName' },
					{ name: 'Person_FirName' },
					{ name: 'Person_BirthDay' },
					//{ name: 'Person_SNILS' },
					//{ name: 'PersonSex_id' },
					//{ name: 'SocStatus_id' },
					{ name: 'OMSSprTerr_id' },
					//{ name: 'PolisType_id' },
					{ name: 'Polis_Ser' },
					{ name: 'Polis_Num' },
					{ name: 'PolisType_id' },
					//{ name: 'Federal_Num' },
					{ name: 'OrgSMO_id' }
					/*,
					{ name: 'Polis_begDate' },
					{ name: 'Polis_endDate' },
					{ name: 'Document_Ser' },
					{ name: 'Document_Num' },
					{ name: 'DocumentType_id' },
					{ name: 'OrgDep_id' },
					{ name: 'Document_begDate' },
					{ name: 'Nation_id' },
					{ name: 'DouType_id' },
					{ name: 'StudyPlace_id' },
					{ name: 'WorklessType_id' },
					{ name: 'Person_Phone' },
					{ name: 'UAddress_Zip' },
					{ name: 'UKLCountry_id' },
					{ name: 'UKLRGN_id' },
					{ name: 'UKLSubRGN_id' },
					{ name: 'UKLCity_id' },
					{ name: 'UPersonSprTerrDop_id' },
					{ name: 'UKLTown_id' },
					{ name: 'UKLStreet_id' },
					{ name: 'UAddress_House' },
					{ name: 'UAddress_Corpus' },
					{ name: 'UAddress_Flat' },
					{ name: 'UAddress_Address' },
					{ name: 'UAddress_AddressText' },
					{ name: 'PAddress_Zip' },
					{ name: 'PKLCountry_id' },
					{ name: 'PKLRGN_id' },
					{ name: 'PKLSubRGN_id' },
					{ name: 'PKLCity_id' },
					{ name: 'PPersonSprTerrDop_id' },
					{ name: 'PKLTown_id' },
					{ name: 'PKLStreet_id' },
					{ name: 'PAddress_House' },
					{ name: 'PAddress_Corpus' },
					{ name: 'PAddress_Flat' },
					{ name: 'PAddress_Address' },
					{ name: 'PAddress_AddressText' },
					{ name: 'Org_id' },
					{ name: 'OrgUnion_id' },
					{ name: 'Post_id' },
					{ name: 'okved_id' },
					{ name: 'Person_Parent' },
					{ name: 'Servers_ids' },
					{ name: 'DeputyKind_id' },
					{ name: 'DeputyPerson_id' },
					{ name: 'DeputyPerson_Fio' },
					{ name: 'Diag_id' },
					{ name: 'FeedingType_id' },
					{ name: 'HealthAbnormVital_id' },
					{ name: 'HealthAbnorm_id' },
					{ name: 'HealthKind_id' },
					{ name: 'HeightAbnormType_id' },
					{ name: 'InvalidKind_id' },
					{ name: 'PersonChild_Height' },
					{ name: 'PersonChild_Weight' },
					{ name: 'PersonChild_IsBad' },
					{ name: 'PersonChild_IsYoungMother' },
					{ name: 'PersonChild_IsHeightAbnorm' },
					{ name: 'PersonChild_IsIncomplete' },
					{ name: 'PersonChild_IsInvalid' },
					{ name: 'PersonChild_IsManyChild' },
					{ name: 'PersonChild_IsMigrant' },
					{ name: 'PersonChild_IsTutor' },
					{ name: 'PersonChild_IsWeightAbnorm' },
					{ name: 'PersonChild_invDate' },
					{ name: 'ResidPlace_id' },
					{ name: 'WeightAbnormType_id'}*/
				]),
				items: [{
					xtype: 'hidden',
					name: 'Person_id'
				}, {
					xtype: 'hidden',
					name: 'Evn_id',
					id: 'RDPEW_Evn_id'
				}, 
				{
					xtype: 'checkbox',
					id: 'RDPEW_onEvn',
					labelSeparator: '',
					name: 'onEvn',
					boxLabel: lang['poseschenie']
				},
				{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 128,
						items: [{
							allowBlank: false,
							fieldLabel: lang['familiya'],
							id: 'RDPEW_Person_SurName',
							listeners: {
								'keydown': function (inp, e) {
									if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										this.findById('rd_person_edit_form').getForm().findField("Person_FirName").focus();
									}
								}.createDelegate(this)
							},
							name: 'Person_SurName',
							tabIndex: TABINDEX_PEF + 51,
							toUpperCase: true,
//							validateOnBlur: false,
//							validationEvent: false,
							width: 180,
							xtype: 'textfieldpmw'
						}, {
							allowBlank: false,
							fieldLabel: lang['imya'],
							listeners: {
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										this.findById('rd_person_edit_form').getForm().findField("Person_SurName").focus();
									}
								}.createDelegate(this)
							},
							name: 'Person_FirName',
							tabIndex: TABINDEX_PEF + 1,
							toUpperCase: true,
//							validateOnBlur: false,
//							validationEvent: false,
							width: 180,
							xtype: 'textfieldpmw'
						}, {
							xtype: 'textfieldpmw',
							fieldLabel: lang['otchestvo'],
							toUpperCase: true,
							width: 180,
							name: 'Person_SecName',
							tabIndex: TABINDEX_PEF + 2
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							allowBlank: false,
							fieldLabel: lang['data_rojdeniya'],
							format: 'd.m.Y',
							maxValue: getGlobalOptions().date,
							minValue: '01.01.1861',
							name: 'Person_BirthDay',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_PEF + 3,
//							validateOnBlur: false,
//							validationEvent: false,
							width: 95,
							xtype: 'swdatefield'
						}/*, {
							allowBlank: false,
							comboSubject: 'Sex',
							fieldLabel: lang['pol'],
							hiddenName: 'PersonSex_id',
							tabIndex: TABINDEX_PEF + 4,
							width: 130,
							xtype: 'swcommonsprcombo'
						}*/]
					}]
				},
				{
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							title: lang['polis'],
							xtype: 'fieldset',

							items: [{
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										codeField: 'PolisType_Code',
										editable: false,
										border : false,
										hiddenName: 'PolisType_id',
										allowBlank: false,
										xtype: 'swpolistypecombo',
										store: new Ext.data.JsonStore({
												url: '/?c=Registry&m=getPolisTypes',
												editable: false,
												key: 'PolisType_id',
												autoLoad: true,
												fields: [
													{name: 'PolisType_Code', type: 'int'},
													{name: 'PolisType_id', type: 'int'},
													{name: 'PolisType_Name', type: 'string'}
												],
												sortInfo: {
													field: 'PolisType_Code'
												}
										}),
										tabIndex : TABINDEX_PEF + 8
									}, {
										codeField: 'OMSSprTerr_Code',
										editable: true,
										forceSelection: true,
										hiddenName: 'OMSSprTerr_id',
										listeners: {
											'select': function(combo) {
												this.disablePolisFields(false);
											}.createDelegate(this),
											'change': function(combo) {
												if ( !combo.getValue() ) {
													this.disablePolisFields(true);
													return false;
												}

												this.disablePolisFields(false);

												var base_form = this.findById('rd_person_edit_form').getForm();

												var OrgSMOCombo = base_form.findField('OrgSMO_id');

												OrgSMOCombo.clearValue();
												OrgSMOCombo.lastQuery = '';

												// var idx = combo.getStore().findBy(function(rec) { return rec.get('OMSSprTerr_id') == combo.getValue(); });
												var number = combo.getValue();
												var idx = -1;
												var findIndex = 0;

												combo.getStore().findBy(function(r) {
													if ( r.get('OMSSprTerr_id') == number ) {
														idx = findIndex;
														return true;
													}

													findIndex++;
												});

												if ( idx >= 0 ) {
													var code = combo.getStore().getAt(idx).get('OMSSprTerr_Code');

													if ( code <= 61 )  {
														base_form.findField('Polis_Ser').disableTransPlug = true;
														base_form.findField('Polis_Ser').setAllowBlank(true);
													}
													else {
														base_form.findField('Polis_Ser').disableTransPlug = true;
														base_form.findField('Polis_Ser').setAllowBlank(true);
													}

													if ( code < 100 ) {
														OrgSMOCombo.baseFilterFn = function(record) {
															if ( /.+/.test(record.get('OrgSMO_RegNomC')) )
																return true;
															else
																return false;
														}

														OrgSMOCombo.getStore().filter('OrgSMO_RegNomC', /.+/);
													}
													else {
														OrgSMOCombo.baseFilterFn = null;
														OrgSMOCombo.getStore().filter('OrgSMO_RegNomC', '');
													}

												_this.filterOrgSMOCombo();
												}
											}.createDelegate(this)
										},
										onTrigger2Click: function() {
											this.findById('rd_person_edit_form').getForm().findField('OMSSprTerr_id').clearValue();
											this.disablePolisFields(true);
										}.createDelegate(this),
										tabIndex: TABINDEX_PEF + 9,
										width: 300,
										xtype: 'swomssprterrcombo'
									}]
								}]
							}, {
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										allowBlank: true,
										fieldLabel: lang['seriya'],
										maxLength: 10,
										name: 'Polis_Ser',
										plugins: [ new Ext.ux.translit(true, true) ],
										tabIndex: TABINDEX_PEF + 11,
										width: 100,
										xtype: 'textfield'
									}]
								}, {
									layout: 'form',
									labelWidth: 93,
									items:[{
										allowBlank: false,
										xtype: 'textfield',
										//maskRe: /\d/,
										//allowNegative: false,
										//allowDecimals: false,
										maxLength: 20,
										width: 100,
										fieldLabel: lang['nomer'],
										name: 'Polis_Num',
										tabIndex: TABINDEX_PEF + 12
									}]
								}]
							}, {
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										id: 'RDPEW_OrgSMO_id',
										tabIndex: TABINDEX_PEF + 14,
//										validateOnBlur: false,
//										validationEvent: false,
										allowBlank: false,
										xtype: 'sworgsmocombo',
										minChars: 1,
										queryDelay: 1,
										hiddenName: 'OrgSMO_id',
										lastQuery: '',
										width: 300,
										listWidth: '300',
										onTrigger2Click: function() {
											if ( this.disabled )
												return;

											var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
											var combo = this;
											var idx = ownerWindow.findById('rd_person_edit_form').getForm().findField('OMSSprTerr_id').getStore().findBy(function(rec) { return rec.get('OMSSprTerr_id') == ownerWindow.findById('rd_person_edit_form').getForm().findField('OMSSprTerr_id').getValue(); });

											if ( idx >= 0 )
												var omsterrcode = ownerWindow.findById('rd_person_edit_form').getForm().findField('OMSSprTerr_id').getStore().getAt(idx).data['OMSSprTerr_Code'];
											else
												var omsterrcode = -1;

											getWnd('swOrgSearchWindow').show({
												onSelect: function(orgData) {
													if ( orgData.Org_id > 0 )
													{
														combo.setValue(orgData.Org_id);
														combo.focus(true, 500);
														combo.fireEvent('change', combo);
													}

													getWnd('swOrgSearchWindow').hide();
												},
												onClose: function() {combo.focus(true, 200)},
												object: 'smo',
												OMSSprTerr_Code: omsterrcode
											});
										},
										enableKeyEvents: true,
										forceSelection: false,
										typeAhead: true,
										typeAheadDelay: 1,
										listeners: {
											'blur': function(combo) {
												if (combo.getRawValue()=='')
													combo.clearValue();

												if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 )
													combo.clearValue();
											},
											'keydown': function( inp, e ) {
												if ( e.F4 == e.getKey() )
												{
													if ( inp.disabled )
														return;

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

													inp.onTrigger2Click();
													inp.collapse();

													return false;
												}
											},
											'keyup': function(inp, e) {
												if ( e.F4 == e.getKey() )
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
										}
									}]
								}]
							}]
						}
				]
						
					})
					],
					keys: [{
						key: "0123456789",
						alt: true,
						fn: function(e) {Ext.getCmp("pacient_tab_panel").setActiveTab(Ext.getCmp("pacient_tab_panel").items.items[ e - 49 ]);},
						stopEvent: true
					}, {
						alt: true,
						fn: function(inp, e) {
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

							if (Ext.isIE)
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							if (e.getKey() == Ext.EventObject.J)
							{
								Ext.getCmp('RegistryDataPersonEditWindow').hide();
								return false;
							}
							if (e.getKey() == Ext.EventObject.C)
							{
								Ext.getCmp('RegistryDataPersonEditWindow').buttons[0].handler();
								return false;
							}
						},
						key: [ Ext.EventObject.C, Ext.EventObject.J, Ext.EventObject.D, Ext.EventObject.Y ],
						scope: this,
						stopEvent: false
					}],
					buttons: [
						{
							text: BTN_FRMSAVE,
							tabIndex: TABINDEX_PEF + 49,
							iconCls: 'save16',
							handler: function() {
								var form = this.findById('rd_person_edit_form');
								if ( this.readOnly )
									return;
								var oldValues = this.oldValues;
								var action = this.action;
								if ( !form.getForm().isValid() ) {
									Ext.MessageBox.show({
										title: "Проверка данных формы",
										msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.WARNING,
										fn: function() {
											form.getForm().findField('Person_SurName').focus(true, 100);
										}
									});
								} else {
									this.doSubmit();
								}
							}.createDelegate(this)
						}, {
							text: '-'
						},
							HelpButton(this, -1),
						{
							text: BTN_FRMCANCEL,
							tabIndex: TABINDEX_PEF + 50,
							iconCls: 'cancel16',
							handler: this.hide.createDelegate(this, [])
						}
					]
		});
		sw.Promed.swRegistryDataPersonEditWindow.superclass.initComponent.apply(this, arguments);
	}
});