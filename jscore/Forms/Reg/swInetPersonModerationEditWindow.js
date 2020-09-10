/**
* swInetPersonModerationEditWindow - окно редактирования расписания врача поликлиники
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      09.04.2013
*/

sw.Promed.swInetPersonModerationEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: false,
	autoHeight: true,
	layout: 'form',
	withoutAttr:true,
	width: 700,
	id: 'InetPersonModerationEditWindow',
	title: WND_REG_INETPERSONMODERATIONEDITWINDOW,
    initComponent: function() {
		var wnd = this;
		
		this.formPanel = new Ext.FormPanel(
		{
			region: 'center',
			labelAlign: 'right',
			autoHeight: true,
			bodyStyle: 'padding:10px;',
			layout: 'form',
			labelWidth: 130,
			border: false,
			items:
			[{
				editable: false,
				allowBlank: true,
				hiddenName: 'Person_mainId',
				width: 450,
				tabIndex: TABINDEX_IPMEW + 0,
				fieldLabel: lang['chelovek_v_baze'],
				xtype: 'swpersoncombo',
				onTrigger1Click: function() {
					var combo = this;
					var base_form = wnd.formPanel.getForm();
					getWnd('swPersonSearchWindow').show({
						personFirname: base_form.findField('Person_Firname').getValue(),
						personSecname: base_form.findField('Person_Secname').getValue(),
						personSurname: base_form.findField('Person_Surname').getValue(),
						PersonBirthDay_BirthDay: Ext.util.Format.date(base_form.findField('Person_BirthDate').getValue(), 'd.m.Y'),
						//Polis_Ser: base_form.findField('Polis_Ser').getValue(),
						//Polis_Num: base_form.findField('Polis_Num').getValue(),
						onSelect: function(personData) {
							if ( personData.Person_id > 0 )
							{
								PersonSurName_SurName = Ext.isEmpty(personData.PersonSurName_SurName)?'':personData.PersonSurName_SurName;
								PersonFirName_FirName = Ext.isEmpty(personData.PersonFirName_FirName)?'':personData.PersonFirName_FirName;
								PersonSecName_SecName = Ext.isEmpty(personData.PersonSecName_SecName)?'':personData.PersonSecName_SecName;
								
								combo.getStore().loadData([{
									Person_id: personData.Person_id,
									Person_Fio: PersonSurName_SurName + ' ' + PersonFirName_FirName + ' ' + PersonSecName_SecName
								}]);
								combo.setValue(personData.Person_id);
								combo.collapse();
								combo.focus(true, 500);
								combo.fireEvent('change', combo);
							}
							getWnd('swPersonSearchWindow').hide();
						},
						onClose: function() {combo.focus(true, 500)}
					});
				},
				enableKeyEvents: true,
				listeners: {
					'change': function(combo) {
					},
					'keydown': function( inp, e ) {
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
							inp.onTrigger1Click();
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
			}, {
				name: 'Person_Surname',
				tabIndex: TABINDEX_IPMEW + 1,
				fieldLabel: lang['familiya'],
				allowBlank: false,
				xtype: 'textfield'
			}, {
				name: 'Person_Firname',
				tabIndex: TABINDEX_IPMEW + 2,
				fieldLabel: lang['imya'],
				allowBlank: false,
				xtype: 'textfield'
			}, {
				name: 'Person_Secname',
				tabIndex: TABINDEX_IPMEW + 3,
				fieldLabel: lang['otchestvo'],
				xtype: 'textfield'
			}, {
				hiddenName: 'PersonSex_id',
				tabIndex: TABINDEX_IPMEW + 4,
				fieldLabel: lang['pol'],
				allowBlank: false,
				xtype: 'swpersonsexcombo'
			}, {
				name: 'Person_Phone',
				tabIndex: TABINDEX_IPMEW + 5,
				fieldLabel: lang['telefon'],
				xtype: 'textfield'
			}, {
				fieldLabel: lang['data_rojdeniya'],
				tabIndex: TABINDEX_IPMEW + 6,
				format: 'd.m.Y',
				name: 'Person_BirthDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				allowBlank: false,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['territoriya_strahovaniya'],
				tabIndex: TABINDEX_IPMEW + 7,
				codeField: 'OMSSprTerr_Code',
				editable: true,
				forceSelection: true,
				hiddenName: 'OMSSprTerr_id',
				onTrigger2Click: function() {
					this.clearValue();
				}.createDelegate(this),
				width: 300,
				xtype: 'swomssprterrcombo'
			}, {
				name: 'Polis_Ser',
				tabIndex: TABINDEX_IPMEW + 8,
				fieldLabel: lang['seriya_polisa'],
				xtype: 'textfield'
			}, {
				name: 'Polis_Num',
				tabIndex: TABINDEX_IPMEW + 9,
				fieldLabel: lang['nomer_polisa'],
				xtype: 'textfield'
			}, {
				xtype: 'sworgsmocombo',
				tabIndex: TABINDEX_IPMEW + 10,
				minChars: 1,
				queryDelay: 1,
				hiddenName: 'OrgSMO_id',
				lastQuery: '',
				width: 300,
				listWidth: 300,
				onTrigger2Click: function() {
					if ( this.disabled )
						return;

					var combo = this;

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
						object: 'smo'
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
				},
				fieldLabel: lang['strahovaya_kompaniya']
			}, new Ext.form.TwinTriggerField ({
				enableKeyEvents: true,
				fieldLabel: lang['adres'],
				name: 'Address_Address',
				readOnly: true,
				tabIndex: TABINDEX_IPMEW + 11,
				trigger1Class: 'x-form-search-trigger',
				trigger2Class: 'x-form-clear-trigger',
				width: 450,

				listeners: {
					'keydown': function(inp, e) {
						if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
							if ( e.F4 == e.getKey() )
								inp.onTrigger1Click();

							if ( e.DELETE == e.getKey() && e.altKey)
								inp.onTrigger2Click();

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

							if ( Ext.isIE ) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							return false;
						}
					},
					'keyup': function( inp, e ) {
						if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
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

							if ( Ext.isIE ) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							return false;
						}
					}
				},
				onTrigger2Click: function() {
					var base_form = wnd.formPanel.getForm();
					if (!base_form.findField('Address_Address').disabled)
					{
						base_form.findField('KLCountry_id').setValue('');
						base_form.findField('KLRgn_id').setValue('');
						base_form.findField('KLSubRgn_id').setValue('');
						base_form.findField('KLCity_id').setValue('');
						base_form.findField('KLTown_id').setValue('');
						base_form.findField('KLStreet_id').setValue('');
						base_form.findField('Address_House').setValue('');
						base_form.findField('Address_Corpus').setValue('');
						base_form.findField('Address_Flat').setValue('');
						base_form.findField('Address_Address').setValue('');
					}
				},
				onTrigger1Click: function() {
					var base_form = wnd.formPanel.getForm();
					if (!base_form.findField('Address_Address').disabled)
					{
						getWnd('swAddressEditWindow').show({
							fields: {
								KLCountry_idEdit: (base_form.findField('KLCountry_id').getValue() > 0)?base_form.findField('KLCountry_id').getValue():null,
								KLRgn_idEdit: (base_form.findField('KLRgn_id').getValue() > 0)?base_form.findField('KLRgn_id').getValue():null,
								KLSubRGN_idEdit: (base_form.findField('KLSubRgn_id').getValue() > 0)?base_form.findField('KLSubRgn_id').getValue():null,
								KLCity_idEdit: (base_form.findField('KLCity_id').getValue() > 0)?base_form.findField('KLCity_id').getValue():null,
								KLTown_idEdit: (base_form.findField('KLTown_id').getValue() > 0)?base_form.findField('KLTown_id').getValue():null,
								KLStreet_idEdit: (base_form.findField('KLStreet_id').getValue() > 0)?base_form.findField('KLStreet_id').getValue():null,
								Address_HouseEdit: base_form.findField('Address_House').getValue(),
								Address_CorpusEdit: base_form.findField('Address_Corpus').getValue(),
								Address_FlatEdit: base_form.findField('Address_Flat').getValue(),
								Address_AddressEdit: base_form.findField('Address_Address').getValue(),
								addressType: 0,
								showDate: true
							},
							callback: function(values) {
								base_form.findField('KLCountry_id').setValue(values.KLCountry_idEdit);
								base_form.findField('KLRgn_id').setValue(values.KLRgn_idEdit);
								base_form.findField('KLSubRgn_id').setValue(values.KLSubRGN_idEdit);
								base_form.findField('KLCity_id').setValue(values.KLCity_idEdit);
								base_form.findField('KLTown_id').setValue(values.KLTown_idEdit);
								base_form.findField('KLStreet_id').setValue(values.KLStreet_idEdit);
								base_form.findField('Address_House').setValue(values.Address_HouseEdit);
								base_form.findField('Address_Corpus').setValue(values.Address_CorpusEdit);
								base_form.findField('Address_Flat').setValue(values.Address_FlatEdit);
								base_form.findField('Address_Address').setValue(values.Address_AddressEdit);
								base_form.findField('Address_Address').focus(true, 500);
							},
							onClose: function() {
								base_form.findField('Address_Address').focus(true, 500);
							}
						})
					}
				}
			}),
			{
				xtype: 'hidden',
				name: 'KLCountry_id'
			},
			{
				xtype: 'hidden',
				name: 'KLRgn_id'
			},
			{
				xtype: 'hidden',
				name: 'KLSubRgn_id'
			},
			{
				xtype: 'hidden',
				name: 'KLCity_id'
			},
			{
				xtype: 'hidden',
				name: 'KLTown_id'
			},
			{
				xtype: 'hidden',
				name: 'KLStreet_id'
			},
			{
				xtype: 'hidden',
				name: 'Address_House'
			},
			{
				xtype: 'hidden',
				name: 'Address_Corpus'
			},
			{
				xtype: 'hidden',
				name: 'Address_Flat'
			}],
			url: '/?c=InetPerson&m=confirmInetPersonModeration',
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'Person_id' },
				{ name: 'Person_mainId' },
				{ name: 'Person_Surname' },
				{ name: 'Person_Firname' },
				{ name: 'Person_Secname' },
				{ name: 'PersonSex_id' },
				{ name: 'Person_Phone' },
				{ name: 'Person_BirthDate' },
				{ name: 'Polis_Ser' },
				{ name: 'Polis_Num' },
				{ name: 'KLCountry_id' },
				{ name: 'KLRgn_id' },
				{ name: 'KLSubRgn_id' },
				{ name: 'KLCity_id' },
				{ name: 'KLTown_id' },
				{ name: 'KLStreet_id' },
				{ name: 'Address_House' },
				{ name: 'Address_Corpus' },
				{ name: 'Address_Flat' },
				{ name: 'Address_Address' },
				{ name: 'OMSSprTerr_id' }
			])
		});

	    Ext.apply(this, {
			border: false,
			items: [
				wnd.formPanel
			],
			buttons: [{
				handler: function() {
					wnd.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_IPMEW + 90,
				text: lang['sohranit_i_podtverdit']
			}, {
				handler: function() {
					wnd.doCancel();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_IPMEW + 91,
				text: lang['otkazat_v_dobavlenii']
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_IPMEW + 92),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_IPMEW + 93,
				onTabAction: function()
				{
					wnd.filtersPanel.getForm().findField('Person_id').focus();
				},
				handler: function() {
					wnd.hide();
				},
				text: BTN_FRMCLOSE
			}]
	    });
	    sw.Promed.swInetPersonModerationEditWindow.superclass.initComponent.apply(this, arguments);
    },
	doSave: function(options) {
		// отправляем запрос на подтверждение модерации, поле mainId должно быть заполнено
		var wnd = this;
		var form = this.formPanel;
		var base_form = form.getForm();
		
		if (!base_form.isValid())
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
		
		var params = new Object();
		params.Person_id = wnd.Person_id;
		
		wnd.getLoadMask().show(lang['podtverjdenie_moderatsii']);
		
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.success == true)
				{
					wnd.hide();
				}
			}
		});
	},
	doCancel: function() {
		var wnd = this;
		
		Ext.Msg.prompt(lang['prichina_otkaza'], lang['vvedite_prichinu'], function(btn, txt) {
			if( btn == 'ok' ) {
				if( txt == '' ) {
					return sw.swMsg.alert(lang['oshibka'], lang['vyi_doljnyi_vvesti_prichinu_otkaza']);
				}
				
				wnd.getLoadMask().show(lang['otkaz_v_moderatsii']);				
				Ext.Ajax.request({
					url: '/?c=InetPerson&m=cancelInetPersonModeration',
					params: {
						Person_id: wnd.Person_id,
						PersonModeration_FailComment: txt
					},
					callback: function(options, success, response) {
						wnd.getLoadMask().hide();
						
						if (success && response.responseText.length > 0 ) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.success) {
								wnd.hide();
							}
						}
					}.createDelegate(this)
				});
			}
		}, '', 60);
	},
	onHide: Ext.emptyFn,
	listeners: 
	{
		hide: function()
		{
			this.onHide();
		}
	},
    show: function () {
    	sw.Promed.swInetPersonModerationEditWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] || !arguments[0].Person_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		this.Person_id = arguments[0].Person_id;
		this.onHide =  (typeof arguments[0].onHide == 'function') ? arguments[0].onHide : Ext.emptyFn;
		
		var wnd = this;
		var base_form = this.formPanel.getForm();
		base_form.reset();
		
		wnd.getLoadMask(LOAD_WAIT).show();

		base_form.load({
			failure: function() {
				wnd.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
			}.createDelegate(this),
			params: {
				'Person_id': wnd.Person_id
			},
			success: function() {
				wnd.getLoadMask().hide();
				
				base_form.findField('Person_mainId').focus();
			}.createDelegate(this),
			url: '/?c=InetPerson&m=loadInetPersonModerationEditWindow'
		});
    }
});
