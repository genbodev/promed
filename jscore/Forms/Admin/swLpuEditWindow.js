/**
* swLpuEditWindow - окно просмотра, добавления и редактирования ЛПУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      16.03.2010
* @comment      Префикс для id компонентов LEW (LpuEditWindow)
*/

sw.Promed.swLpuEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	bodyStyle: 'padding: 2px',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closeAction: 'hide',
	draggable: false,
	enableEdit: function( enable ) {
		var form = this.findById('LpuEditForm');
		if ( enable === false )
		{
			form.getForm().findField('Org_Code').disable();
			form.getForm().findField('Org_Name').disable();
			form.getForm().findField('Org_Nick').disable();
			form.getForm().findField('UAddress_AddressText').disable();
			form.getForm().findField('PAddress_AddressText').disable();
			form.getForm().findField('LpuType_id').disable();
			form.getForm().findField('Lpu_RegNomC').disable();
			form.getForm().findField('Lpu_RegNomN').disable();
			form.getForm().findField('Lpu_IsOMS').disable();
			form.getForm().findField('Org_INN').disable();
			form.getForm().findField('Org_OGRN').disable();
			form.getForm().findField('Okved_id').disable();
			form.getForm().findField('Org_Email').disable();
			form.getForm().findField('Org_Phone').disable();
			form.getForm().findField('Org_OKPO').disable();
			form.getForm().findField('Org_OKATO').disable();
			form.getForm().findField('Okonh_id').disable();
			form.getForm().findField('Okogu_id').disable();
			form.getForm().findField('Okopf_id').disable();
			form.getForm().findField('Okfs_id').disable();			
			this.buttons[0].disable();
		}
		else
		{
			form.getForm().findField('Org_Code').enable();
			form.getForm().findField('Org_Name').enable();
			form.getForm().findField('Org_Nick').enable();
			form.getForm().findField('UAddress_AddressText').enable();
			form.getForm().findField('PAddress_AddressText').enable();
			form.getForm().findField('LpuType_id').enable();
			form.getForm().findField('Lpu_RegNomC').enable();
			form.getForm().findField('Lpu_RegNomN').enable();
			form.getForm().findField('Lpu_IsOMS').enable();
			form.getForm().findField('Org_INN').enable();
			form.getForm().findField('Org_Email').enable();
			form.getForm().findField('Org_Phone').enable();
			form.getForm().findField('Org_OGRN').disable();
			form.getForm().findField('Okved_id').disable();
			form.getForm().findField('Org_OKPO').disable();
			form.getForm().findField('Org_OKATO').disable();
			form.getForm().findField('Okonh_id').disable();
			form.getForm().findField('Okogu_id').disable();
			form.getForm().findField('Okopf_id').disable();
			form.getForm().findField('Okfs_id').disable();
			this.buttons[0].enable();
		}
	},
	id: 'lpu_edit_window',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				id: 'LEW_SaveButton',
				tabIndex: TABINDEX_LEW + 11,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				id: 'LEW_CancelButton',
				handler: function() {
					this.ownerCt.hide();
				},
				tabIndex: TABINDEX_LEW + 12,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyStyle: 'padding: 5px',
				frame: true,
				id: 'LpuEditForm',
				labelAlign: 'right',
				labelWidth: 145,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'Org_id' },
					{ name: 'Org_Code' },
					{ name: 'Org_Nick' },
					{ name: 'Org_Name' },
					{ name: 'UAddress_id' },
					{ name: 'UAddress_Zip' },
					{ name: 'UKLCountry_id' },
					{ name: 'UKLRGN_id' },
					{ name: 'UKLSubRGN_id' },
					{ name: 'UKLCity_id' },
					{ name: 'UKLTown_id' },
					{ name: 'UKLStreet_id' },
					{ name: 'UAddress_House' },
					{ name: 'UAddress_Corpus' },
					{ name: 'UAddress_Flat' },
					{ name: 'UAddress_Address' },
					{ name: 'UAddress_AddressText' },
					{ name: 'PAddress_id' },
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
					{ name: 'LpuType_id' },
					{ name: 'Lpu_RegNomC' },
					{ name: 'Lpu_RegNomN' },
					{ name: 'Lpu_Ouz' },
					{ name: 'Lpu_IsOMS' },
					{ name: 'Org_INN' },
					{ name: 'Org_OGRN' },
					{ name: 'Org_Email' },
					{ name: 'Org_Phone' },
					{ name: 'Okved_id' },
					{ name: 'Org_OKPO' },
					{ name: 'Okonh_id' },
					{ name: 'Org_OKATO' },
					{ name: 'Okogu_id' },
					{ name: 'Okopf_id' },
					{ name: 'Okfs_id' }
				]),
				url: '/?c=Org&m=saveLpu',

				items: [{
					id: 'LEW_Org_id',
					name: 'Org_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_PAddress_id',
					name: 'PAddress_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_PAddress_Zip',
					name: 'PAddress_Zip',
					xtype: 'hidden'
				}, {
					id: 'LEW_PKLCountry_id',
					name: 'PKLCountry_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_PKLRGN_id',
					name: 'PKLRGN_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_PKLSubRGN_id',
					name: 'PKLSubRGN_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_PKLCity_id',
					name: 'PKLCity_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_PKLTown_id',
					name: 'PKLTown_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_PKLStreet_id',
					name: 'PKLStreet_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_PAddress_House',
					name: 'PAddress_House',
					xtype: 'hidden'
				}, {
					id: 'LEW_PAddress_Corpus',
					name: 'PAddress_Corpus',
					xtype: 'hidden'
				}, {
					id: 'LEW_PAddress_Flat',
					name: 'PAddress_Flat',
					xtype: 'hidden'
				}, {
					id: 'LEW_PAddress_Address',
					name: 'PAddress_Address',
					xtype: 'hidden'
				}, {
					id: 'LEW_UAddress_id',
					name: 'UAddress_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_UAddress_Zip',
					name: 'UAddress_Zip',
					xtype: 'hidden'
				}, {
					id: 'LEW_UKLCountry_id',
					name: 'UKLCountry_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_UKLRGN_id',
					name: 'UKLRGN_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_UKLSubRGN_id',
					name: 'UKLSubRGN_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_UKLCity_id',
					name: 'UKLCity_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_UKLTown_id',
					name: 'UKLTown_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_UKLStreet_id',
					name: 'UKLStreet_id',
					xtype: 'hidden'
				}, {
					id: 'LEW_UAddress_House',
					name: 'UAddress_House',
					xtype: 'hidden'
				}, {
					id: 'LEW_UAddress_Corpus',
					name: 'UAddress_Corpus',
					xtype: 'hidden'
				}, {
					id: 'LEW_UAddress_Flat',
					name: 'UAddress_Flat',
					xtype: 'hidden'
				}, {
					id: 'LEW_UAddress_Address',
					name: 'UAddress_Address',
					xtype: 'hidden'
				}, {
					allowBlank: false,
					autoCreate: {
						maxLength: 14,
						tag: 'input',
						type: 'text'
					},
					enableKeyEvents: true,
					fieldLabel: lang['kod_organizatsii'],
					listeners: {
						keydown: function(inp, e) {
							if (e.getKey() == e.F2)
							{
								this.onTriggerClick();

								if ( Ext.isIE )
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								e.stopEvent();
							}

							if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								inp.ownerCt.getForm().findField('Org_Name').focus(true);
							}
						}
					},
					maskRe: /\d/,
					name: 'Org_Code',
					onTriggerClick: function() {
						var Mask = new Ext.LoadMask(Ext.get('lpu_edit_window'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..." });
						Mask.show();

						Ext.Ajax.request({
							callback: function(opt, success, resp) {
								Mask.hide();

								var form = this.findById('LpuEditForm').getForm();
								var response_obj = Ext.util.JSON.decode(resp.responseText);

								if (response_obj.Org_Code != '')
								{
									form.findField('Org_Code').setValue(response_obj.Org_Code);
								}
							}.createDelegate(this.ownerCt.ownerCt),
							url: '/?c=Org&m=getMaxOrgCode'
						});
					},
					tabIndex: TABINDEX_LEW + 13,
					triggerAction: 'all',
					triggerClass: 'x-form-plus-trigger',
					width: 400,
					xtype: 'trigger'
				}, {
					allowBlank: false,
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'LEW_Org_Name',
					listeners: {
						'keydown': function (inp, e) {
							if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								inp.ownerCt.getForm().findField('Org_Code').focus(true);
							}
						}
					},
					name: 'Org_Name',
					tabIndex: TABINDEX_LEW + 1,
					width: 400,
					xtype: 'textfield'
				}, {
					allowBlank: false,
					fieldLabel: lang['kratkoe_naimenovanie'],
					id: 'LEW_Org_Nick',
					name: 'Org_Nick',
					tabIndex: TABINDEX_LEW + 1,
					width: 400,
					xtype: 'textfield'
				},
				new Ext.TabPanel({
					activeTab: 0,
					id: 'LpuEditTabPanel',
					items: [{
						// autoHeight: true,
						height: 220,
						labelWidth: 143,
						layout: 'form',
						listeners: {
							'activate': function() {
								Ext.getCmp('LEW_UAddress_AddressText').focus(true, 300);
							}
						},
						style: 'padding: 2px',
						title: lang['1_adres'],
						items: [{
							autoHeight: true,
							title: lang['adres'],
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px',
							xtype: 'fieldset',
							items: [ new sw.Promed.TripleTriggerField ({
								//xtype: 'trigger',
								enableKeyEvents: true,
								fieldLabel: lang['yuridicheskiy_adres'],
								id: 'LEW_UAddress_AddressText',
								listeners: {
									'keydown': function(inp, e) {
										if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
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
								name: 'UAddress_AddressText',
								onTrigger2Click: function() {
									var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
									ownerForm.findById('LEW_UAddress_Zip').setValue(ownerForm.findById('LEW_PAddress_Zip').getValue());
									ownerForm.findById('LEW_UKLCountry_id').setValue(ownerForm.findById('LEW_PKLCountry_id').getValue());
									ownerForm.findById('LEW_UKLRGN_id').setValue(ownerForm.findById('LEW_PKLRGN_id').getValue());
									ownerForm.findById('LEW_UKLSubRGN_id').setValue(ownerForm.findById('LEW_PKLSubRGN_id').getValue());
									ownerForm.findById('LEW_UKLCity_id').setValue(ownerForm.findById('LEW_PKLCity_id').getValue());
									ownerForm.findById('LEW_UKLTown_id').setValue(ownerForm.findById('LEW_PKLTown_id').getValue());
									ownerForm.findById('LEW_UKLStreet_id').setValue(ownerForm.findById('LEW_PKLStreet_id').getValue());
									ownerForm.findById('LEW_UAddress_House').setValue(ownerForm.findById('LEW_PAddress_House').getValue());
									ownerForm.findById('LEW_UAddress_Corpus').setValue(ownerForm.findById('LEW_PAddress_Corpus').getValue());
									ownerForm.findById('LEW_UAddress_Flat').setValue(ownerForm.findById('LEW_PAddress_Flat').getValue());
									ownerForm.findById('LEW_UAddress_Address').setValue(ownerForm.findById('LEW_PAddress_Address').getValue());
									ownerForm.findById('LEW_UAddress_AddressText').setValue(ownerForm.findById('LEW_PAddress_AddressText').getValue());
								},
								onTrigger3Click: function() {
									var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
									ownerForm.findById('LEW_UAddress_Zip').setValue('');
									ownerForm.findById('LEW_UKLCountry_id').setValue('');
									ownerForm.findById('LEW_UKLRGN_id').setValue('');
									ownerForm.findById('LEW_UKLSubRGN_id').setValue('');
									ownerForm.findById('LEW_UKLCity_id').setValue('');
									ownerForm.findById('LEW_UKLTown_id').setValue('');
									ownerForm.findById('LEW_UKLStreet_id').setValue('');
									ownerForm.findById('LEW_UAddress_House').setValue('');
									ownerForm.findById('LEW_UAddress_Corpus').setValue('');
									ownerForm.findById('LEW_UAddress_Flat').setValue('');
									ownerForm.findById('LEW_UAddress_Address').setValue('');
									ownerForm.findById('LEW_UAddress_AddressText').setValue('');
								},
								onTrigger1Click: function() {
									var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
									var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
									getWnd('swAddressEditWindow').show({
										fields: {
											Address_ZipEdit: ownerForm.findById('LEW_UAddress_Zip').value,
											KLCountry_idEdit: ownerForm.findById('LEW_UKLCountry_id').value,
											KLRgn_idEdit: ownerForm.findById('LEW_UKLRGN_id').value,
											KLSubRGN_idEdit: ownerForm.findById('LEW_UKLSubRGN_id').value,
											KLCity_idEdit: ownerForm.findById('LEW_UKLCity_id').value,
											KLTown_idEdit: ownerForm.findById('LEW_UKLTown_id').value,
											KLStreet_idEdit: ownerForm.findById('LEW_UKLStreet_id').value,
											Address_HouseEdit: ownerForm.findById('LEW_UAddress_House').value,
											Address_CorpusEdit: ownerForm.findById('LEW_UAddress_Corpus').value,
											Address_FlatEdit: ownerForm.findById('LEW_UAddress_Flat').value,
											Address_AddressEdit: ownerForm.findById('LEW_UAddress_Address').value
										},
										callback: function(values) {
											ownerForm.findById('LEW_UAddress_Zip').setValue(values.Address_ZipEdit);
											ownerForm.findById('LEW_UKLCountry_id').setValue(values.KLCountry_idEdit);
											ownerForm.findById('LEW_UKLRGN_id').setValue(values.KLRgn_idEdit);
											ownerForm.findById('LEW_UKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
											ownerForm.findById('LEW_UKLCity_id').setValue(values.KLCity_idEdit);
											ownerForm.findById('LEW_UKLTown_id').setValue(values.KLTown_idEdit);
											ownerForm.findById('LEW_UKLStreet_id').setValue(values.KLStreet_idEdit);
											ownerForm.findById('LEW_UAddress_House').setValue(values.Address_HouseEdit);
											ownerForm.findById('LEW_UAddress_Corpus').setValue(values.Address_CorpusEdit);
											ownerForm.findById('LEW_UAddress_Flat').setValue(values.Address_FlatEdit);
											ownerForm.findById('LEW_UAddress_Address').setValue(values.Address_AddressEdit);
											ownerForm.findById('LEW_UAddress_AddressText').setValue(values.Address_AddressEdit);
											ownerForm.findById('LEW_UAddress_AddressText').focus(true, 500);
										},
										onClose: function() {
											ownerForm.findById('LEW_UAddress_AddressText').focus(true, 500);
										}
									})
								},
								readOnly: true,
								tabIndex: TABINDEX_LEW + 2,
								trigger1Class: 'x-form-search-trigger',
								trigger2Class: 'x-form-equil-trigger',
								trigger3Class: 'x-form-clear-trigger',
								width: 395
							}),
							new sw.Promed.TripleTriggerField ({
								//xtype: 'trigger',
								enableKeyEvents: true,
								fieldLabel: lang['fakticheskiy_adres'],
								id: 'LEW_PAddress_AddressText',
								listeners: {
									'keydown': function(inp, e) {
										if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
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
								name: 'PAddress_AddressText',
								onTrigger1Click: function() {
									var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
									var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
									getWnd('swAddressEditWindow').show({
										fields: {
											Address_ZipEdit: ownerForm.findById('LEW_PAddress_Zip').value,
											KLCountry_idEdit: ownerForm.findById('LEW_PKLCountry_id').value,
											KLRgn_idEdit: ownerForm.findById('LEW_PKLRGN_id').value,
											KLSubRGN_idEdit: ownerForm.findById('LEW_PKLSubRGN_id').value,
											KLCity_idEdit: ownerForm.findById('LEW_PKLCity_id').value,
											KLTown_idEdit: ownerForm.findById('LEW_PKLTown_id').value,
											KLStreet_idEdit: ownerForm.findById('LEW_PKLStreet_id').value,
											Address_HouseEdit: ownerForm.findById('LEW_PAddress_House').value,
											Address_CorpusEdit: ownerForm.findById('LEW_PAddress_Corpus').value,
											Address_FlatEdit: ownerForm.findById('LEW_PAddress_Flat').value,
											Address_AddressEdit: ownerForm.findById('LEW_PAddress_Address').value
										},
										callback: function(values) {
											ownerForm.findById('LEW_PAddress_Zip').setValue(values.Address_ZipEdit);
											ownerForm.findById('LEW_PKLCountry_id').setValue(values.KLCountry_idEdit);
											ownerForm.findById('LEW_PKLRGN_id').setValue(values.KLRgn_idEdit);
											ownerForm.findById('LEW_PKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
											ownerForm.findById('LEW_PKLCity_id').setValue(values.KLCity_idEdit);
											ownerForm.findById('LEW_PKLTown_id').setValue(values.KLTown_idEdit);
											ownerForm.findById('LEW_PKLStreet_id').setValue(values.KLStreet_idEdit);
											ownerForm.findById('LEW_PAddress_House').setValue(values.Address_HouseEdit);
											ownerForm.findById('LEW_PAddress_Corpus').setValue(values.Address_CorpusEdit);
											ownerForm.findById('LEW_PAddress_Flat').setValue(values.Address_FlatEdit);
											ownerForm.findById('LEW_PAddress_Address').setValue(values.Address_AddressEdit);
											ownerForm.findById('LEW_PAddress_AddressText').setValue(values.Address_AddressEdit);
											ownerForm.findById('LEW_PAddress_AddressText').focus(true, 500);
										},
										onClose: function() {
											ownerForm.findById('LEW_PAddress_AddressText').focus(true, 500);
										}
									})
								},
								onTrigger2Click: function() {
									var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
									ownerForm.findById('LEW_PAddress_Zip').setValue(ownerForm.findById('LEW_UAddress_Zip').getValue());
									ownerForm.findById('LEW_PKLCountry_id').setValue(ownerForm.findById('LEW_UKLCountry_id').getValue());
									ownerForm.findById('LEW_PKLRGN_id').setValue(ownerForm.findById('LEW_UKLRGN_id').getValue());
									ownerForm.findById('LEW_PKLSubRGN_id').setValue(ownerForm.findById('LEW_UKLSubRGN_id').getValue());
									ownerForm.findById('LEW_PKLCity_id').setValue(ownerForm.findById('LEW_UKLCity_id').getValue());
									ownerForm.findById('LEW_PKLTown_id').setValue(ownerForm.findById('LEW_UKLTown_id').getValue());
									ownerForm.findById('LEW_PKLStreet_id').setValue(ownerForm.findById('LEW_UKLStreet_id').getValue());
									ownerForm.findById('LEW_PAddress_House').setValue(ownerForm.findById('LEW_UAddress_House').getValue());
									ownerForm.findById('LEW_PAddress_Corpus').setValue(ownerForm.findById('LEW_UAddress_Corpus').getValue());
									ownerForm.findById('LEW_PAddress_Flat').setValue(ownerForm.findById('LEW_UAddress_Flat').getValue());
									ownerForm.findById('LEW_PAddress_Address').setValue(ownerForm.findById('LEW_UAddress_Address').getValue());
									ownerForm.findById('LEW_PAddress_AddressText').setValue(ownerForm.findById('LEW_UAddress_AddressText').getValue());
								},
								onTrigger3Click: function() {
									var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
									ownerForm.findById('LEW_PAddress_Zip').setValue('');
									ownerForm.findById('LEW_PKLCountry_id').setValue('');
									ownerForm.findById('LEW_PKLRGN_id').setValue('');
									ownerForm.findById('LEW_PKLSubRGN_id').setValue('');
									ownerForm.findById('LEW_PKLCity_id').setValue('');
									ownerForm.findById('LEW_PKLTown_id').setValue('');
									ownerForm.findById('LEW_PKLStreet_id').setValue('');
									ownerForm.findById('LEW_PAddress_House').setValue('');
									ownerForm.findById('LEW_PAddress_Corpus').setValue('');
									ownerForm.findById('LEW_PAddress_Flat').setValue('');
									ownerForm.findById('LEW_PAddress_Address').setValue('');
									ownerForm.findById('LEW_PAddress_AddressText').setValue('');
								},
								readOnly: true,
								tabIndex: TABINDEX_LEW + 2,
								trigger1Class: 'x-form-search-trigger',
								trigger2Class: 'x-form-equil-trigger',
								trigger3Class: 'x-form-clear-trigger',
								width: 395
							})]
						}, {
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px',
							title: lang['kontaktyi'],
							xtype: 'fieldset',
							items: [{
								fieldLabel: lang['inn'],
								name: 'Org_INN',
								tabIndex: TABINDEX_LEW + 3,
								xtype: 'textfield',
								width: 397
							}, {
								fieldLabel: lang['telefon'],
								id: 'LEW_Org_Phone',
								name: 'Org_Phone',
								tabIndex: TABINDEX_LEW + 3,								
								width: 397,
								xtype: 'textfield'
							}, {
								fieldLabel: 'E-mail',
								id: 'LEW_Org_Email',
								name: 'Org_Email',
								tabIndex: TABINDEX_LEW + 4,
								width: 397,
								xtype: 'textfield'
							}]
						}]
					}, {
						//autoHeight: true,
						height: 220,
						labelWidth: 60,
						layout: 'form',
						listeners: {
							'activate': function() {
								if ( !Ext.getCmp('LEW_Org_OKPO').disabled )
									Ext.getCmp('LEW_Org_OKPO').focus(true);
							}
						},
						style: 'padding: 2px',
						title: lang['2_kodifikatsiya'],

						items: [{
							fieldLabel: lang['okpo'],
							id: 'LEW_Org_OKPO', 
							name: 'Org_OKPO',
							tabIndex: TABINDEX_LEW + 6,
							width: 397,
							xtype: 'textfield'
						}, {
							fieldLabel: lang['okonh'],
							hiddenName: 'Okonh_id',
							tabIndex: TABINDEX_LEW + 6,
							width: 397,
							xtype: 'swokonhcombo'
						}, {
							fieldLabel: lang['ogrn'],
							id: 'LEW_Org_OGRN', 
							name: 'Org_OGRN',
							tabIndex: TABINDEX_LEW + 6,
							width: 397,
							xtype: 'textfield'
						}, {
							fieldLabel: lang['okato'],
							name: 'Org_OKATO',
							tabIndex: TABINDEX_LEW + 6,
							width: 397,
							xtype: 'textfield'
						}, {
							fieldLabel: lang['okogu'],
							hiddenName: 'Okogu_id',
							tabIndex: TABINDEX_LEW + 6,
							width: 397,
							xtype: 'swokogucombo'
						}, {
							fieldLabel: lang['okopf'],
							hiddenName: 'Okopf_id',
							tabIndex: TABINDEX_LEW + 6,
							width: 397,
							xtype: 'swokopfcombo'
						}, {
							fieldLabel: lang['okfs'],
							hiddenName: 'Okfs_id',
							tabIndex: TABINDEX_LEW + 6,
							width: 397,
							xtype: 'swokfscombo'
						}, {
							fieldLabel: lang['okved'],
							hiddenName: 'Okved_id',
							tabIndex: TABINDEX_LEW + 7,
							width: 397,
							xtype: 'swokvedcombo'
						}, {
							id: 'LEW_Lpu_Ouz',
							name: 'Lpu_Ouz',
							hiddenName: 'Lpu_Ouz',
							hidden: true,
							xtype: 'hidden'
						}]
					}],
					layoutOnTabChange: true
				}), {
					autoHeight: true,
					title: lang['lpu'],
					style: 'padding: 0; padding-top: 5px; margin: 0;',
					xtype: 'fieldset',
					items: [{
						allowBlank: false,
						hiddenName: 'LpuType_id',
						width: 397,
						xtype: 'swlputypecombo',
						tabIndex: TABINDEX_LEW + 8
					}, {												
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'numberfield',
								allowNegative: false,
								allowDecimals: false,
								autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
								width: 80,
								fieldLabel: lang['kod_lpu_-_pole_1'],
								name: 'Lpu_RegNomC',
								tabIndex: TABINDEX_LEW + 8
							}]
						}, {
							layout: 'form',
							labelWidth: 232,
							items: [{
								xtype: 'numberfield',
								allowNegative: false,
								allowDecimals: false,
								autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
								width: 80,
								fieldLabel: lang['kod_lpu_-_pole_2'],
								name: 'Lpu_RegNomN',
								tabIndex: TABINDEX_LEW + 9
							}]
						}]												
					}, {
						fieldLabel: lang['rabotaet_v_oms'],
						hiddenName: 'Lpu_IsOMS',
						tabIndex: TABINDEX_LEW + 10,
						width: 80,
						xtype: 'swyesnocombo'
					}]
				}]
			})],
			keys: [{
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

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.submit();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swLpuEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'fit',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	orgType: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swLpuEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;
		var form = current_window.findById('LpuEditForm');

		form.getForm().reset();

		current_window.action = null;
		current_window.callback = Ext.emptyFn;
		current_window.onHide = Ext.emptyFn;
		current_window.orgType = 'lpu';

		var org_id = 0;

		if ( arguments[0] ) {
			if ( arguments[0].action )
				current_window.action = arguments[0].action;

			if ( arguments[0].callback )
				current_window.callback = arguments[0].callback;

			if ( arguments[0].onHide )
				current_window.onHide = arguments[0].onHide;

			if ( arguments[0].Org_id )
				org_id = arguments[0].Org_id;			
		}

		form.getForm().setValues({
			Org_id: org_id
		})

		form.enable();
		current_window.buttons[0].enable();

		current_window.findById('LpuEditTabPanel').setActiveTab(1);
		current_window.findById('LpuEditTabPanel').setActiveTab(0);

		// current_window.findById('OrgEditTabPanel').doLayout();

		if ( current_window.action ) {
			switch ( current_window.action ) {
				case 'add':
					current_window.setTitle(lang['dobavlenie_lpu']);
					current_window.enableEdit(true);

					//Фокусируем на поле Наименование
					form.getForm().findField('Org_Code').focus(true, 600);

					var Mask = new Ext.LoadMask(Ext.get('lpu_edit_window'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..." });
					Mask.show();
					
					Ext.TaskMgr.start({
						run : function() {
							Ext.getCmp('LEW_UAddress_AddressText').el.removeClass('x-form-focus');
							Ext.TaskMgr.stopAll();
						},
						interval : 200
					});

					Ext.Ajax.request({
						callback: function(opt, success, resp) {
							Mask.hide();
							form.getForm().findField('Org_Code').focus(true, 600);
							Ext.TaskMgr.start({
								run : function() {
									Ext.getCmp('LEW_UAddress_AddressText').el.removeClass('x-form-focus');
									Ext.TaskMgr.stopAll();
								},
								interval : 200
							});
							var response_obj = Ext.util.JSON.decode(resp.responseText);

							if (response_obj.Org_Code != '') {
								form.getForm().findField('Org_Code').setValue(response_obj.Org_Code);
							}
						},
						params: {
							OrgType: current_window.orgType
						},
						url: '/?c=Org&m=getMaxOrgCode'
					});
					break;

				case 'edit':
					current_window.setTitle(lang['redaktirovanie_lpu']);
					current_window.enableEdit(true);
					//Фокусируем на поле Код
					var Mask = new Ext.LoadMask(Ext.get('lpu_edit_window'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..."} );
					Mask.show();

					form.getForm().load({
						failure: function() {
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { current_window.hide(); } );
						},
						params: {
							Org_id: org_id
						},
						success: function() {
							Mask.hide();
							form.getForm().findField('Org_Code').focus(true, 600);
							Ext.TaskMgr.start({
								run : function() {
									Ext.getCmp('LEW_UAddress_AddressText').el.removeClass('x-form-focus');
									Ext.TaskMgr.stopAll();
								},
								interval : 200
							});
						},
						url: '/?c=Org&m=getLpuData'
					});
					break;

				case 'view':
					current_window.setTitle(lang['prosmotr_lpu']);
					current_window.enableEdit(false);
					//Фокусируем на поле Код
					var Mask = new Ext.LoadMask(Ext.get('lpu_edit_window'), {msg:"Пожалуйста, подождите, идет загрузка данных формы..."});
					Mask.show();
					form.load({
						failure: function() {
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { current_window.hide(); } );
						}.createDelegate(current_window),
						params: {
							Org_id: org_id
						},
						success: function() {
							Mask.hide();
						}.createDelegate(current_window),
						url: '/?c=Org&m=getLpuData'
					});
					break;
			}
		}
	},
	submit: function() {
		var form = this.findById('LpuEditForm').getForm();

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}

		form.submit({
			failure: function (form, action) {
				if (action.result.Error_Code)
					sw.swMsg.alert("Ошибка", '<b>Ошибка ' + action.result.Error_Code + ' :</b><br/> ' + action.result.Error_Msg);
			}.createDelegate(this),
			params: {
				OrgType: this.orgType
			},
			success: function(form, action) {
				this.hide();

				if ( action.result.Error_Code )
					sw.swMsg.alert("Ошибка", '<b>Ошибка ' + action.result.Error_Code + ' :</b><br/> ' + action.result.Error_Msg);

				var data = new Object();
				data.OrgData = new Object();

				data.OrgData.Org_id = action.result.Org_id;				

				this.callback(data);
			}.createDelegate(this)
		});
	},
	title: lang['redaktirovanie_lpu'],
	width: 600
});