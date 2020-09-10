/**
* swEvnDirectionMorfoHistologicEditWindow - направление на патоморфогистологическое исследование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Direction
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      14.10.2010
* @comment      Префикс для id компонентов EDMHEF (EvnDirectionMorfoHistologicEditForm)
*
*
* Использует: -
*/

sw.Promed.swEvnDirectionMorfoHistologicEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	addItem: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var grid = this.ItemList.getGrid();
		var id = - swGenTempId(grid.getStore());

		grid.getStore().loadData([{ EvnDirectionMorfoHistologicItems_id: id }], true);
		grid.getSelectionModel().select(grid.getStore().getCount() - 1, 2);
		grid.getView().focusCell(grid.getStore().getCount() - 1, 2);

		var cell = grid.getSelectionModel().getSelectedCell();		

		if ( !cell || cell.length == 0 || (cell[1] != 2) ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		if ( !record ) {
			return false;
		}
/*		
		grid.getColumnModel().setEditor(
			2,
			new Ext.grid.GridEditor(new Ext.form.ComboBox({
				allowBlank: false,
				store: [
					[ 1, lang['dokument'] ],
					[ 2, lang['snimok'] ],
					[ 3, lang['predmet_tsennost'] ]
				]
			}), {
				listeners: {
					'canceledit': this.onItemAddCancel.createDelegate(this)
				}
			})
		);
*/
		grid.getColumnModel().setEditable(2, true);
		grid.startEditing(cell[0], cell[1]);
	},
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	deleteItem: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var grid = this.ItemList.getGrid();
		var record = grid.getSelectionModel().getSelected();

		var id = record.get('EvnDirectionMorfoHistologicItems_id');

		if ( !id ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' ) {
					if ( id < 0 ) {
						// Удаляем сразу
						grid.getStore().remove(record);

						if ( grid.getStore().getCount() > 0 ) {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					}
					else {
						Ext.Ajax.request({
							failure: function(response, options) {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_zapisi_voznikli_oshibki_[tip_oshibki_2]']);
							},
							params: {
								EvnDirectionMorfoHistologicItems_id: id
							},
							success: function(response, options) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_zapisi_voznikli_oshibki_[tip_oshibki_3]']);
								}
								else {
									grid.getStore().remove(record);
								}

								if ( grid.getStore().getCount() > 0 ) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}.createDelegate(this),
							url: '/?c=EvnDirectionMorfoHistologic&m=deleteEvnDirectionMorfoHistologicItems'
						});
					}
				}
			}.createDelegate(this),
			msg: lang['udalit_zapis'],
			title: lang['udalenie_zapisi']
		});
	},
	refreshOuterFieldsAccess: function() {
		var base_form = this.FormPanel.getForm();
		if(this.outer) {
			base_form.findField('EvnDirectionMorfoHistologic_Ser').enable();
			if(this.action == 'add') {
				base_form.findField('EvnDirectionMorfoHistologic_Ser').setValue('');
				base_form.findField('EvnDirectionMorfoHistologic_Num').setValue('');
				base_form.findField('Org_did').setValue(getGlobalOptions().lpu_name);
				base_form.findField('Lpu_did').setValue(getGlobalOptions().lpu_id);
			}
			
			base_form.findField('EvnDirectionMorfoHistologic_Num').enable();

			base_form.findField('PrehospDirect_id').showContainer();
			base_form.findField('PrehospDirect_id').getStore().filterBy(function(rec) {
				return rec.get('PrehospDirect_Code').inlist([2,3]);
			});
			base_form.findField('PrehospDirect_id').setValue(2);
			base_form.findField('PrehospDirect_id').fireEvent( 'change', base_form.findField('PrehospDirect_id'), 2 );

			base_form.findField('Org_sid').showContainer();
			base_form.findField('Org_did').setDisabled(true);
			
			base_form.findField('Lpu_sid').showContainer();
			base_form.findField('Lpu_sid').setAllowBlank(false);

			base_form.findField('EvnDirectionMorfoHistologic_LawDocumentDate').showContainer();
		} else {
			base_form.findField('EvnDirectionMorfoHistologic_Ser').disable();
			base_form.findField('EvnDirectionMorfoHistologic_Num').disable();
			base_form.findField('Org_did').setDisabled(false);

			base_form.findField('Lpu_sid').hideContainer();
			base_form.findField('Lpu_sid').setAllowBlank(true);

			base_form.findField('PrehospDirect_id').hideContainer();

			base_form.findField('Org_sid').hideContainer();

			base_form.findField('EvnDirectionMorfoHistologic_LawDocumentDate').hideContainer();
		}
	},
	doSave: function(options) {
		// options @Object
		// options.print @Boolean Вызывать печать направления на патоморфогистологическое исследование, если true

		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var win = this;
		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var record = base_form.findField('MedStaffFact_id').getStore().getById(base_form.findField('MedStaffFact_id').getValue());
		if ( record ) {
			base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
		}

		var items_list = getStoreRecords(this.ItemList.getGrid().getStore(), { exceptionFields: [ 'MorfoHistologicItemsType_Name' ]});

		var params = new Object();

		if (base_form.findField('EvnDirectionMorfoHistologic_Num').disabled) {
			params.EvnDirectionMorfoHistologic_Num = base_form.findField('EvnDirectionMorfoHistologic_Num').getValue();
		}
		if (base_form.findField('EvnDirectionMorfoHistologic_Ser').disabled) {
			params.EvnDirectionMorfoHistologic_Ser = base_form.findField('EvnDirectionMorfoHistologic_Ser').getValue();
		}
		params.EvnDirectionMorfoHistologicItemsList = Ext.util.JSON.encode(items_list);

		if ( base_form.findField('LpuSection_id').disabled ) {
			params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение направления..." });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnDirectionMorfoHistologic_id ) {
						var evn_direction_morfo_histologic_id = action.result.EvnDirectionMorfoHistologic_id;
						var org_id = base_form.findField('Org_did').getValue();
						var org_name = '';

						base_form.findField('EvnDirectionMorfoHistologic_id').setValue(evn_direction_morfo_histologic_id);

						var index = base_form.findField('Org_did').getStore().findBy(function(rec) {
							return rec.get('Org_id') == org_id;
						});

						record = base_form.findField('Org_did').getStore().getAt(index);
						if ( record ) {
							org_name = record.get('Org_Name');
						}

						var data = new Object();

						data.evnDirectionMorfoHistologicData = {
							'accessType': 'edit',
							'EvnDirectionMorfoHistologic_id': base_form.findField('EvnDirectionMorfoHistologic_id').getValue(),
							'Person_id': base_form.findField('Person_id').getValue(),
							'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue(),
							'EvnDirectionMorfoHistologic_Ser': base_form.findField('EvnDirectionMorfoHistologic_Ser').getValue(),
							'EvnDirectionMorfoHistologic_Num': base_form.findField('EvnDirectionMorfoHistologic_Num').getValue(),
							'EvnDirectionMorfoHistologic_setDate': base_form.findField('EvnDirectionMorfoHistologic_setDate').getValue(),
							'EvnDirectionMorfoHistologic_deathDate': base_form.findField('EvnDirectionMorfoHistologic_deathDate').getValue(),
							'Person_Surname': this.PersonInfo.getFieldValue('Person_Surname'),
							'Person_Firname': this.PersonInfo.getFieldValue('Person_Firname'),
							'Person_Secname': this.PersonInfo.getFieldValue('Person_Secname'),
							'Person_Birthday': this.PersonInfo.getFieldValue('Person_Birthday'),
							'OrgAnatom_Name': org_name
						};

						this.callback(data);

						if ( options && options.print ) {
							this.ItemList.loadData({
								globalFilters: {
									EvnDirectionMorfoHistologic_id: evn_direction_morfo_histologic_id
								}
							});

							this.buttons[1].focus();
							window.open('/?c=EvnDirectionMorfoHistologic&m=printEvnDirectionMorfoHistologic&EvnDirectionMorfoHistologic_id=' + evn_direction_morfo_histologic_id, '_blank');
						}
						else {
							this.hide();
						}
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
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'Diag_id',
			'Diag_oid',
			'Diag_sid',
			'EvnDirectionMorfoHistologic_deathDate',
			'EvnDirectionMorfoHistologic_deathTime',
			'EvnDirectionMorfoHistologic_Descr',
			'EvnDirectionMorfoHistologic_Phone',
			'EvnDirectionMorfoHistologic_setDate',
			'EvnPS_Title',
			'LpuSection_id',
			'MedStaffFact_id',
			'Lpu_sid',
			'Org_did',
			'PrehospType_did'
		);
		var i = 0;

		if ( this.outer ) {
			form_fields.push('EvnDirectionMorfoHistologic_LpuSectionName');
			form_fields.push('EvnDirectionMorfoHistologic_MedPersonalFIO');
			form_fields.push('EvnDirectionMorfoHistologic_Ser');
			form_fields.push('EvnDirectionMorfoHistologic_Num');
			form_fields.push('EvnDirectionMorfoHistologic_LawDocumentDate');
			form_fields.push('Org_sid');
			form_fields.push('PrehospDirect_id');
		}

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
			this.findById('EDMHEF_EvnDirectionMorfoHistologicItemsGrid').setReadOnly(false);
		}
		else {
			this.buttons[0].hide();
			this.findById('EDMHEF_EvnDirectionMorfoHistologicItemsGrid').setReadOnly(false);
		}
	},
	formStatus: 'edit',
	height: 550,
	id: 'EvnDirectionMorfoHistologicEditWindow',
	initComponent: function() {
		var win = this;
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnDirectionMorfoHistologicEditForm',
			labelAlign: 'right',
			labelWidth: 210,
			reader: new Ext.data.JsonReader({
				success: Ext.EmptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'Diag_id' },
				{ name: 'Diag_oid' },
				{ name: 'Diag_sid' },
				{ name: 'EvnDirectionMorfoHistologic_deathDate' },
				{ name: 'EvnDirectionMorfoHistologic_deathTime' },
				{ name: 'EvnDirectionMorfoHistologic_Descr' },
				{ name: 'EvnDirectionMorfoHistologic_id' },
				{ name: 'EvnDirectionMorfoHistologic_Num' },
				{ name: 'EvnDirectionMorfoHistologic_Phone' },
				{ name: 'EvnDirectionMorfoHistologic_Ser' },
				{ name: 'EvnDirectionMorfoHistologic_setDate' },
				{ name: 'EvnDirectionMorfoHistologic_LpuSectionName' },
				{ name: 'EvnDirectionMorfoHistologic_MedPersonalFIO' },
				{ name: 'EvnPS_id' },
				{ name: 'EvnPS_Title' },
				{ name: 'LpuSection_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'Org_did' },
				{ name: 'Lpu_did' },
				{ name: 'Lpu_sid'},
				{ name: 'OrgAnatom_did' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'pmUser_Name' },
				{ name: 'PrehospType_did' },
				{ name: 'Server_id' },
				{ name: 'EvnDirectionMorfoHistologic_LawDocumentDate' },
				{ name: 'Org_sid' }
			]),
			region: 'center',
			url: '/?c=EvnDirectionMorfoHistologic&m=saveEvnDirectionMorfoHistologic',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionMorfoHistologic_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnPS_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				border: false,
				hidden: true,
				id: 'EDMHEF_Caption',
				layout: 'form',
				xtype: 'panel',

				items: [{
					fieldLabel: lang['annulirovano'],
					name: 'pmUser_Name',
					readOnly: true,
					style: 'color: #ff8870',
					width: 430,
					xtype: 'textfield'
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						disabled: true,
						fieldLabel: lang['seriya_nomer_napravleniya'],
						name: 'EvnDirectionMorfoHistologic_Ser',
						width: 100,
						xtype: 'textfield'
					}]
				}, {
					border: false,
					labelWidth: 50,
					layout: 'form',
					items: [{
						allowBlank: false,
						disabled: true,
						fieldLabel: '',
						labelSeparator: '',
						name: 'EvnDirectionMorfoHistologic_Num',
						width: 100,
						xtype: 'textfield'
					}]
				}]
			}, {
				hiddenName: 'PrehospDirect_id',
				codeField: null,
				lastQuery: '',
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">{PrehospDirect_Name}</div></tpl>'
				),
				listeners: {
                    'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

                    	if ( newValue == 2 ) {
                    		//направившая МО
							base_form.findField('Lpu_sid').setAllowBlank(false);
							base_form.findField('Lpu_sid').enable();
							//Отделения
							base_form.findField('LpuSection_id').setAllowBlank(false);
							base_form.findField('LpuSection_id').enable();

							base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').setAllowBlank(false);
							base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').enable();

							//Мед. работник, направивший тело
							base_form.findField('MedStaffFact_id').setAllowBlank(false);
							base_form.findField('MedStaffFact_id').enable();

							base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').setAllowBlank(false);
							base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').enable();

							//организация
							base_form.findField('Org_sid').setAllowBlank(true);
							base_form.findField('Org_sid').clearValue();
							base_form.findField('Org_sid').disable();

							//обоснование направления
							base_form.findField('EvnDirectionMorfoHistologic_Descr').setAllowBlank(true);
							base_form.findField('EvnDirectionMorfoHistologic_Descr').setValue('');
							base_form.findField('EvnDirectionMorfoHistologic_Descr').disable();

							base_form.findField('EvnDirectionMorfoHistologic_LawDocumentDate').setAllowBlank(true);
							base_form.findField('EvnDirectionMorfoHistologic_LawDocumentDate').setValue( null );
							base_form.findField('EvnDirectionMorfoHistologic_LawDocumentDate').disable();

						} else {
							//направившая МО
							base_form.findField('Lpu_sid').setAllowBlank(true);
							base_form.findField('Lpu_sid').clearValue();
							base_form.findField('Lpu_sid').disable();

							//Отделения
							base_form.findField('LpuSection_id').setAllowBlank(true);
							base_form.findField('LpuSection_id').clearValue();
							base_form.findField('LpuSection_id').disable();

							base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').setAllowBlank(true);
							base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').setValue('');
							base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').disable();

							//Мед. работник, направивший тело
							base_form.findField('MedStaffFact_id').setAllowBlank(true);
							base_form.findField('MedStaffFact_id').clearValue();
							base_form.findField('MedStaffFact_id').disable();

							base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').setAllowBlank(true);
							base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').setValue('');
							base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').disable();

							base_form.findField('Org_sid').setAllowBlank(false);
							base_form.findField('Org_sid').enable();

							base_form.findField('EvnDirectionMorfoHistologic_Descr').setAllowBlank(false);
							base_form.findField('EvnDirectionMorfoHistologic_Descr').enable();

							base_form.findField('EvnDirectionMorfoHistologic_LawDocumentDate').setAllowBlank(false);
							base_form.findField('EvnDirectionMorfoHistologic_LawDocumentDate').enable();
						}

                    }.createDelegate(this),
                    'select': function(combo, record, index) {
						combo.fireEvent( 'change', combo, record.get( 'PrehospDirect_id') );
                    }.createDelegate(this)
                },
				width: 300,
				xtype: 'swprehospdirectcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата документа правоохранительных органов',
				format: 'd.m.Y',
				name: 'EvnDirectionMorfoHistologic_LawDocumentDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				xtype: 'swdatefield'
			}, {
				xtype: 'sworgcombo',
				hiddenName: 'Org_sid',
				editable: false,
				fieldLabel: 'Организация',
				triggerAction: 'none',
				width: 610,
				onTrigger1Click: function() {
					var combo = this;
					if (this.disabled) return false;
					getWnd('swOrgSearchWindow').show({
						enableOrgType: true,
						onSelect: function(orgData) {
							if ( orgData.Org_id > 0 ) {
								combo.getStore().load({
									params: {
										Object:'Org',
										Org_id: orgData.Org_id,
										Org_Name:''
									},
									callback: function() {
										combo.setValue(orgData.Org_id);
									}
								});
							}
							getWnd('swOrgSearchWindow').hide();
						},
					});
				},
				enableKeyEvents: true
			}, {
				allowBlank: false,
				fieldLabel: lang['data_napravleniya'],
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						if ( this.outer && base_form.findField('PrehospDirect_id').getValue() == 3 ) {
							return false
						}

						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

						base_form.findField('LpuSection_id').clearValue();
						base_form.findField('MedStaffFact_id').clearValue();

						/*if ( Ext.isEmpty(newValue )) {
							base_form.findField('LpuSection_id').disable();
							base_form.findField('MedStaffFact_id').disable();
							return false;
						}*/

						var lpu_section_filter_params = {
							// isStacAndPolka: true,
							onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
							regionCode: getGlobalOptions().region.number
						};

						var medstafffact_filter_params = {
							// isStacAndPolka: true,
							onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
							regionCode: getGlobalOptions().region.number
						};

						if ( this.action != 'view' ) {
							base_form.findField('LpuSection_id').enable();
							base_form.findField('MedStaffFact_id').enable();
						}

						base_form.findField('LpuSection_id').getStore().removeAll();
						base_form.findField('MedStaffFact_id').getStore().removeAll();

						if ( this.action == 'add' ) {
							// фильтр или на конкретное место работы или на список мест работы
							if ( this.UserLpuSection_id && this.UserMedStaffFact_id ) {
								lpu_section_filter_params.id = this.UserLpuSection_id;
								medstafffact_filter_params.id = this.UserMedStaffFact_id;
							}
							else if ( typeof this.UserLpuSectionList == 'object' && this.UserLpuSectionList.length > 0 && typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0 ) {
								lpu_section_filter_params.ids = this.UserLpuSectionList;
								medstafffact_filter_params.ids = this.UserMedStaffFactList;
							}
						}

						// загружаем локальные списки отделений и мест работы
						setLpuSectionGlobalStoreFilter(lpu_section_filter_params);
						setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

						base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

						if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
							base_form.findField('LpuSection_id').setValue(lpu_section_id);
							base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
						}

						if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
							base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
						}

						/*
							если форма открыта на редактирование и задано отделение и 
							место работы или задан список мест работы, то не даем редактировать вообще
						*/
						if ( this.action == 'edit' && ((this.UserLpuSection_id && this.UserMedStaffFact_id) || (typeof this.UserLpuSectionList == 'object' && this.UserLpuSectionList.length > 0 && typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0)) ) {
							base_form.findField('LpuSection_id').disable();
							base_form.findField('MedStaffFact_id').disable();
						}

						// Если форма открыта на добавление...
						if ( this.action == 'add' ) {
							// ... и задано отделение и место работы...
							if ( this.UserLpuSection_id && this.UserMedStaffFact_id ) {
								// ... то устанавливаем их и не даем редактировать поля
								base_form.findField('LpuSection_id').setValue(this.UserLpuSection_id);
								base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), this.UserLpuSection_id);
								base_form.findField('MedStaffFact_id').setValue(this.UserMedStaffFact_id);
							}
							// или задан список отделений и мест работы...
							else if ( typeof this.UserLpuSectionList == 'object' && this.UserLpuSectionList.length > 0 && typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0 ) {
								// ... выбираем место работы текущего пользователя
								base_form.findField('MedStaffFact_id').setValue(getGlobalOptions().CurMedStaffFact_id);
								base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), this.UserMedStaffFactList[0]);

								// Если в списке мест работы всего одна запись...
								if ( this.UserMedStaffFactList.length == 1 ) {
									// ... закрываем поля для редактирования
									base_form.findField('LpuSection_id').disable();
									base_form.findField('MedStaffFact_id').disable();
								}
							}
						}
					}.createDelegate(this),
					'keydown': function(inp, e) {
						if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
							e.stopEvent();
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this)
				},
				name: 'EvnDirectionMorfoHistologic_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_EDMHEF + 1,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: lang['otdelenie'],
				hiddenName: 'LpuSection_id',
				id: 'EDMHEF_LpuSectionCombo',
				linkedElements: [
					'EDMHEF_MedStaffFactCombo'
				],
				listWidth: 650,
				tabIndex: TABINDEX_EDMHEF + 2,
				width: 430,
				xtype: 'swlpusectionglobalcombo'
			}, {
				allowBlank: false,
				fieldLabel: langs('Отделение'),
				name: 'EvnDirectionMorfoHistologic_LpuSectionName',	
				tabIndex: TABINDEX_EDMHEF + 2,
				width: 430,
				xtype: 'textfield'
			}, {
				allowBlank: true,
				enableKeyEvents: true,
				fieldLabel: lang['karta_statsionarnogo_bolnogo'],
				listeners: {
					'keydown': function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.F4:
								e.stopEvent();
								this.openEvnPSListWindow();
							break;
						}
					}.createDelegate(this)
				},
				name: 'EvnPS_Title',
				onTriggerClick: function() {
					this.openEvnPSListWindow();
				}.createDelegate(this),
				readOnly: true,
				tabIndex: TABINDEX_EDMHEF + 3,
				triggerClass: 'x-form-search-trigger',
				width: 200,
				xtype: 'trigger'
			}, {
				allowBlank: true,
				fieldLabel: lang['kontaktnyiy_telefon'],
				name: 'EvnDirectionMorfoHistologic_Phone',
				tabIndex: TABINDEX_EDMHEF + 4,
				width: 430,
				xtype: 'textfield'
			}, {
				allowBlank: true,
				comboSubject: 'PrehospType',
				fieldLabel: lang['tip_gospitalizatsii'],
				hiddenName: 'PrehospType_did',
				tabIndex: TABINDEX_EDMHEF + 5,
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: lang['data_smerti'],
						format: 'd.m.Y',
						name: 'EvnDirectionMorfoHistologic_deathDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: TABINDEX_EDMHEF + 6,
						width: 100,
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
					labelWidth: 50,
					layout: 'form',
					items: [{
						fieldLabel: lang['vremya'],
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnDirectionMorfoHistologic_deathTime',
						onTriggerClick: function() {
							var base_form = this.FormPanel.getForm();
							var time_field = base_form.findField('EvnDirectionMorfoHistologic_deathTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								dateField: base_form.findField('EvnDirectionMorfoHistologic_deathDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: this.id
							});
						}.createDelegate(this),
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: TABINDEX_EDMHEF + 7,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				autoHeight: true,
				style: 'padding: 2px 0px 0px 0px;',
				title: lang['diagnozyi'],
				xtype: 'fieldset',
				items: [{
					allowBlank: false,
					fieldLabel: lang['osnovnoy'],
					hiddenName: 'Diag_id',
					tabIndex: TABINDEX_EDMHEF + 8,
					width: 430,
					xtype: 'swdiagcombo'
				}, {
					allowBlank: true,
					fieldLabel: lang['oslojnenie'],
					hiddenName: 'Diag_oid',
					tabIndex: TABINDEX_EDMHEF + 9,
					width: 430,
					xtype: 'swdiagcombo'
				}, {
					allowBlank: true,
					fieldLabel: lang['soputstvuyuschiy'],
					hiddenName: 'Diag_sid',
					tabIndex: TABINDEX_EDMHEF + 10,
					width: 430,
					xtype: 'swdiagcombo'
				}]
			}, {
				xtype: 'hidden',
				name: 'Lpu_did'
			}, {
				xtype: 'hidden',
				name: 'OrgAnatom_did'
			}, {
				allowBlank: false,
				displayField: 'Org_Name',
				editable: false,
				enableKeyEvents: true,
				fieldLabel: lang['kuda_napravlen'],
				hiddenName: 'Org_did',
				listeners: {
					'keydown': function( inp, e ) {
						if ( inp.disabled )
							return;

						if ( e.F4 == e.getKey() ) {
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;

							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;

							e.returnValue = false;

							if ( Ext.isIE ) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							inp.onTrigger1Click();
							return false;
						}
					},
					'keyup': function(inp, e) {
						if ( e.F4 == e.getKey() ) {
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;

							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
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
				mode: 'local',
				onTrigger1Click: function() {
					var base_form = this.FormPanel.getForm();
					var combo = base_form.findField('Org_did');

					if ( combo.disabled ) {
						return false;
					}

					getWnd('swOrgSearchWindow').show({
						object: 'anatom',
						activeInDate: base_form.findField('EvnDirectionMorfoHistologic_setDate').getValue().toString('yyyy-MM-dd'),
						onClose: function() {
							combo.focus(true, 200)
						},
						onSelect: function(org_data) {
							if ( org_data.Org_id > 0 ) {
								combo.getStore().loadData([{
									Org_id: org_data.Org_id,
									Org_Name: org_data.Org_Name
								}]);
								combo.setValue(org_data.Org_id);
								if (org_data.OrgAnatom_id > 0) {
									base_form.findField('OrgAnatom_did').setValue(org_data.OrgAnatom_id);
								} else {
									base_form.findField('Lpu_did').setValue(org_data.Lpu_id);
								}
								getWnd('swOrgSearchWindow').hide();
								combo.collapse();
							}
						}
					});
				}.createDelegate(this),
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'Org_id', type: 'int' },
						{ name: 'Org_Nick', type: 'string' },
						{ name: 'Org_Name', type: 'string' },
						{ name: 'Lpu_id', type: 'string' },
						{ name: 'OrgAnatom_id', type: 'string' }
					],
					key: 'Org_id',
					sortInfo: {
						field: 'Org_Name'
					},
					url: C_ORG_LIST
				}),
				tabIndex: TABINDEX_EDMHEF + 11,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{Org_Name}',
					'</div></tpl>'
				),
				trigger1Class: 'x-form-search-trigger',
				triggerAction: 'none',
				valueField: 'Org_id',
				width: 430,
				xtype: 'swbaseremotecombo'
			}, {
				fieldLabel: langs('Направившая МО'),
				hiddenName: 'Lpu_sid',
				tabIndex: TABINDEX_EDMHEF + 12,
				width: 430,
				listeners: {//--
					'change': function(combo, newValue, oldValue) {
						var base_form = win.FormPanel.getForm();

						var LpuInSystem = combo.getFieldValue('Lpu_IsNotForSystem') != '2';

						base_form.findField('LpuSection_id').setAllowBlank(!LpuInSystem);
						base_form.findField('MedStaffFact_id').setAllowBlank(!LpuInSystem);
						base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').setAllowBlank(LpuInSystem);
						base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').setAllowBlank(LpuInSystem);

						if (LpuInSystem) {
							base_form.findField('LpuSection_id').showContainer();
							base_form.findField('MedStaffFact_id').showContainer();
							base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').hideContainer();
							base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').hideContainer();
							base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').setValue('');
							base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').setValue('');

						} else {
							base_form.findField('LpuSection_id').hideContainer();
							base_form.findField('MedStaffFact_id').hideContainer();
							base_form.findField('LpuSection_id').clearValue();
							base_form.findField('MedStaffFact_id').clearValue();
							base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').showContainer();
							base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').showContainer();
						}

						if(LpuInSystem && newValue != oldValue) {
							base_form.findField('LpuSection_id').clearValue();
							base_form.findField('LpuSection_id').getStore().removeAll();
							base_form.findField('LpuSection_id').getStore().load({
								params: {
									Lpu_id: newValue,
									mode: 'combo'
								}
							});

							base_form.findField('MedStaffFact_id').clearValue();
							base_form.findField('MedStaffFact_id').getStore().removeAll();
							base_form.findField('MedStaffFact_id').getStore().load({
								params: {
									Lpu_id: newValue,
									mode: 'combo'
								}
							});
						}	
					}
				},
				xtype: 'swlpulocalcombo'
			}, {
				allowBlank: true,
				fieldLabel: lang['obosnovanie_napravleniya'],
				name: 'EvnDirectionMorfoHistologic_Descr',
				tabIndex: TABINDEX_EDMHEF + 13,
				width: 430,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['med_rabotnik_napravivshiy_telo'],
				hiddenName: 'MedStaffFact_id',
				id: 'EDMHEF_MedStaffFactCombo',
				listWidth: 650,
				parentElementId: 'EDMHEF_LpuSectionCombo',
				tabIndex: TABINDEX_EDMHEF + 14,
				width: 430,
				xtype: 'swmedstafffactglobalcombo'
			}, {
				allowBlank: false,
				fieldLabel: langs('Мед. работник, направивший тело'),
				name: 'EvnDirectionMorfoHistologic_MedPersonalFIO',
				tabIndex: TABINDEX_EDMHEF + 14,
				width: 430,
				xtype: 'textfield'
			}]
		});

		this.ItemList = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.addItem(); }.createDelegate(this), disabled: false },
				{ name: 'action_edit', handler: Ext.emptyFn, hidden: true },
				{ name: 'action_view', handler: Ext.emptyFn, hidden: true },
				{ name: 'action_delete', handler: function() { this.deleteItem(); }.createDelegate(this), disabled: false },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print' }
			],
			autoexpand: 'expand',
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnDirectionMorfoHistologic&m=loadEvnDirectionMorfoHistologicItemsGrid',
			id: 'EDMHEF_EvnDirectionMorfoHistologicItemsGrid',
			onAfterEditSelf: function(o) {
				return this.onAfterEdit(o);
			}.createDelegate(this),
			onCellSelect: function(sm, rowIdx, colIdx) {
				//
			}.createDelegate(this),
			onSelectionChange: function() {
				//
			}.createDelegate(this),
			onValidateEditSelf: function(o) {
				//
			}.createDelegate(this),
			region: 'south',
			selectionModel: 'cell',
			setFirstEditRecord: function() {
				//
			}.createDelegate(this),
			stringfields: [
				{ name: 'EvnDirectionMorfoHistologicItems_id', type: 'int', header: 'ID', key: true },
				{ name: 'MorfoHistologicItemsType_id', type: 'int', hidden: true },
				{ name: 'MorfoHistologicItemsType_Name',  editor: new Ext.form.ComboBox({ allowBlank: false, store: [ [ 1, 'документ' ], [ 2, 'снимок' ], [ 3, 'предмет (ценность)' ] ]}), type: 'string', header: 'Тип', width: 150 },
				{ name: 'EvnDirectionMorfoHistologicItems_Descr', editor: new Ext.form.TextField(), type: 'string', header: lang['opisanie'], id: 'autoexpand' },
				{ name: 'EvnDirectionMorfoHistologicItems_Count', editor: new Ext.form.NumberField({ allowBlank: true, allowDecimals: false, allowNegative: false, minValue: 1 }), type: 'int', header: lang['kolichestvo'], width: 100 }
			],
			title: lang['prilagaemyie_dokumentyi_i_predmetyi'],
			toolbar: true
		});

		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				if ( this.action == 'view' ) {
					this.buttons[this.buttons.length - 1].focus();
				}
				else {
					this.FormPanel.getForm().findField('EvnDirectionMorfoHistologic_setDate').focus(true);
				}
			}.createDelegate(this),
			button2Callback: function(callback_data) {
				this.FormPanel.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
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
			collapsed: true,
			floatable: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north',
			title: lang['zagruzka'],
			titleCollapse: true
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else {
						base_form.findField('MedStaffFact_id').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EDMHEF + 14,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnDirectionMorfoHistologic();
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EDMHEF + 15,
				text: BTN_FRMPRINT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.FormPanel.getForm().findField('EvnDirectionMorfoHistologic_setDate').focus(true);
					}
					else {
						this.buttons[1].focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EDMHEF + 16,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel,
				this.ItemList
			],
			layout: 'border'
		});

		sw.Promed.swEvnDirectionMorfoHistologicEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnDirectionMorfoHistologicEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'beforehide': function(win) {
			// 
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: true,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	morfoHistologicItemsTypeArray: [
		null,
		lang['dokument'],
		lang['snimok'],
		lang['predmet_tsennost']
	],
	onAfterEdit: function(o) {
		o.grid.stopEditing(true);

		if ( o.column == 2 ) {
			// устанавливаем класс периодики
			o.record.set('MorfoHistologicItemsType_id', o.value);
			o.record.set('MorfoHistologicItemsType_Name', this.morfoHistologicItemsTypeArray[o.value]);
			o.record.commit();
		}

		return false;
	},
	onHide: Ext.emptyFn,
	onItemAddCancel: Ext.emptyFn,
	openEvnPSListWindow: function() {
		var base_form = this.FormPanel.getForm();

		if ( base_form.findField('EvnPS_Title').disabled ) {
			return false;
		}

		if ( getWnd('swEvnPSListWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_prosmotra_spiska_kvs_uje_otkryito']);
			return false;
		}

		var params = new Object();

		params.callback = function(data) {
			if ( !data ) {
				return false;
			}

			base_form.findField('EvnDirectionMorfoHistologic_deathDate').setValue(data.EvnPS_deathDate);
			base_form.findField('EvnDirectionMorfoHistologic_deathTime').setValue(data.EvnPS_deathTime);
			base_form.findField('EvnPS_id').setValue(data.EvnPS_id);
			base_form.findField('EvnPS_Title').setValue(data.title);
			base_form.findField('PrehospType_did').setValue(data.PrehospType_id);
		}.createDelegate(this);
		params.onHide = function() {
			base_form.findField('EvnPS_Title').focus();
		}.createDelegate(this);
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');

		getWnd('swEvnPSListWindow').show(params);
	},
	plain: true,
	printEvnDirectionMorfoHistologic: function() {
		switch ( this.action ) {
			case 'add':
			case 'edit':
				this.doSave({
					print: true
				});
			break;

			case 'view':
				var evn_direction_morfo_histologic_id = this.FormPanel.getForm().findField('EvnDirectionMorfoHistologic_id').getValue();
				window.open('/?c=EvnDirectionMorfoHistologic&m=printEvnDirectionMorfoHistologic&EvnDirectionMorfoHistologic_id=' + evn_direction_morfo_histologic_id, '_blank');
			break;
		}
	},
	resizable: true,
	setEvnDirectionMorfoHistologicNumber: function() {
		var base_form = this.FormPanel.getForm();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					base_form.findField('EvnDirectionMorfoHistologic_Num').setValue(response_obj.EvnDirectionMorfoHistologic_Num);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_napravleniya'], function() { base_form.findField('EvnDirectionMorfoHistologic_setDate').focus(true); }.createDelegate(this) );
				}
			}.createDelegate(this),
			url: '/?c=EvnDirectionMorfoHistologic&m=getEvnDirectionMorfoHistologicNumber'
		});
	},
	fillFields: function(){

		var base_form = this.FormPanel.getForm(),
			_this = this,
			diag_id = base_form.findField('Diag_id').getValue(),
			diag_oid = base_form.findField('Diag_oid').getValue(),
			diag_sid = base_form.findField('Diag_sid').getValue(),
			index,
			lpu_section_id = base_form.findField('LpuSection_id').getValue(),
			med_personal_id = base_form.findField('MedPersonal_id').getValue(),
			med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

		setCurrentDateTime({
			dateField: base_form.findField('EvnDirectionMorfoHistologic_setDate'),
			loadMask: false,
			setDate: true,
			setDateMaxValue: true,
			windowId: this.id
		});

		index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
			return record.get('MedStaffFact_id') == med_staff_fact_id;
		});

		if ( index >= 0 ) {
			base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
		}

		if ( !Ext.isEmpty(diag_id) ) {
			base_form.findField('Diag_id').getStore().load({
				callback: function() {
					base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
				},
				params: {
					where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
				}
			});
		}

		if ( !Ext.isEmpty(diag_oid) ) {
			base_form.findField('Diag_oid').getStore().load({
				callback: function() {
					base_form.findField('Diag_oid').fireEvent('select', base_form.findField('Diag_oid'), base_form.findField('Diag_oid').getStore().getAt(0), 0);
				},
				params: {
					where: "where DiagLevel_id = 4 and Diag_id = " + diag_oid
				}
			});
		}

		if ( !Ext.isEmpty(diag_sid) ) {
			base_form.findField('Diag_sid').getStore().load({
				callback: function() {
					base_form.findField('Diag_sid').fireEvent('select', base_form.findField('Diag_sid'), base_form.findField('Diag_sid').getStore().getAt(0), 0);
				},
				params: {
					where: "where DiagLevel_id = 4 and Diag_id = " + diag_sid
				}
			});
		}
	},
	setDiagFilter: function(){
		var _this = this;
		if (!Ext.isEmpty(_this.Diag_filter) && !Ext.isEmpty(_this.Diag_filter[0])) {
			['Diag_id', 'Diag_sid', 'Diag_oid'].forEach(function(rec){
				_this.FormPanel.getForm().findField(rec).setBaseFilter(function(rec) {
					return rec.get('Diag_id').inlist(_this.Diag_filter);
				}, _this);
				_this.FormPanel.getForm().findField(rec).filterDiag = _this.Diag_filter;
			});
		}
	},
	show: function() {
		var win = this;
		sw.Promed.swEvnDirectionMorfoHistologicEditWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.UserLpuSection_id = null;
		this.UserLpuSectionList = new Array();
		this.UserMedStaffFact_id = null;
		this.UserMedStaffFactList = new Array();

		this.findById('EDMHEF_Caption').hide();
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		// определенный медстафффакт
		if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 ) {
			this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}
		// если в настройках есть medstafffact, то имеем список мест работы
		else if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 ) {
			this.UserMedStaffFactList = Ext.globalOptions.globals['medstafffact'];
		}

		// определенный LpuSection
		if ( arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0 ) {
			this.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}
		// если в настройках есть lpusection, то имеем список мест работы
		else if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 ) {
			this.UserLpuSectionList = Ext.globalOptions.globals['lpusection'];
		}

		base_form.setValues(arguments[0].formParams);

		if( arguments[0].outer ) {
			this.outer = arguments[0].outer;
		} else {
			this.outer = false;
		}


		if( arguments[0].formParams.PersonEvn_id ){
			base_form.findField('PersonEvn_id').setValue(arguments[0].formParams.PersonEvn_id);
		}

		if( arguments[0].formParams.Person_id ){
			base_form.findField('Person_id').setValue(arguments[0].formParams.Person_id);			
		}

		if( arguments[0].formParams.Server_id ){
			base_form.findField('Server_id').setValue(arguments[0].formParams.Server_id);			
		}

		this.ItemList.removeAll({
			addEmptyRecord: false
		});

		this.ItemList.getGrid().getTopToolbar().items.items[0].enable();
		this.ItemList.getGrid().getTopToolbar().items.items[1].disable();
		this.ItemList.getGrid().getTopToolbar().items.items[2].disable();
		this.ItemList.getGrid().getTopToolbar().items.items[3].disable();

		this.ItemList.getGrid().getColumnModel().setEditable(2, true);
		this.ItemList.getGrid().getColumnModel().setEditable(3, true);
		this.ItemList.getGrid().getColumnModel().setEditable(4, true);

		this.PersonInfo.setTitle('...');
		this.PersonInfo.load({
			callback: function() {
				this.PersonInfo.setPersonTitle();
			}.createDelegate(this),
			Person_id: base_form.findField('Person_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue()
		});

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].Diag_filter ) {
			this.Diag_filter = arguments[0].Diag_filter;
		} else {
			this.Diag_filter = null;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		base_form.findField('LpuSection_id').showContainer();
		base_form.findField('MedStaffFact_id').showContainer();
		base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').hideContainer();
		base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').hideContainer();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PATHOMORPH_EDMHEFADD);
				this.enableEdit(true);

				setCurrentDateTime({
					callback: function() {
						base_form.findField('EvnDirectionMorfoHistologic_setDate').fireEvent('change', base_form.findField('EvnDirectionMorfoHistologic_setDate'), base_form.findField('EvnDirectionMorfoHistologic_setDate').getValue());
					}.createDelegate(this),
					dateField: base_form.findField('EvnDirectionMorfoHistologic_deathDate'),
					timeField: base_form.findField('EvnDirectionMorfoHistologic_deathTime'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: true,
					windowId: this.id
				});

				// Генерируем серию направления
				var lpu_id = Ext.globalOptions.globals.lpu_id;

				var lpu_store = new Ext.db.AdapterStore({
					autoLoad: false,
					dbFile: 'Promed.db',
					fields: [
						{ name: 'Lpu_id', type: 'int' },
						{ name: 'Lpu_Ouz', type: 'int' }
					], 
					key: 'Lpu_id',
					tableName: 'Lpu'
				});

				base_form.findField('Lpu_sid').fireEvent('change', base_form.findField('Lpu_sid'));

				base_form.findField('EvnDirectionMorfoHistologic_LpuSectionName').setValue('');
				base_form.findField('EvnDirectionMorfoHistologic_MedPersonalFIO').setValue('');

				lpu_store.load({
					callback: function(records, options, success) {
						var serial = '';

						for ( var i = 0; i < records.length; i++ ) {
							if ( records[i].get('Lpu_id') == lpu_id ) {
								serial = records[i].get('Lpu_Ouz');
							}
						}

						base_form.findField('EvnDirectionMorfoHistologic_Ser').setValue(lang['m'] + serial);
						win.refreshOuterFieldsAccess();
					}
				});

				// Получаем номер направления
				if(!win.outer) {
					this.setEvnDirectionMorfoHistologicNumber();
				}

				//Если с формы редактирования КВС
				this.setDiagFilter();
				this.fillFields();
				loadMask.hide();

				base_form.clearInvalid();

				base_form.findField('EvnDirectionMorfoHistologic_setDate').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				var evn_direction_morfo_histologic_id = base_form.findField('EvnDirectionMorfoHistologic_id').getValue();

				this.ItemList.loadData({
					globalFilters: {
						EvnDirectionMorfoHistologic_id: evn_direction_morfo_histologic_id
					}
				});

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnDirectionMorfoHistologic_id': evn_direction_morfo_histologic_id
					},
					success: function() {
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if( !Ext.isEmpty(base_form.findField('Lpu_sid').getValue()) ) {
							win.outer = true;
							base_form.findField('Lpu_sid').fireEvent('change', base_form.findField('Lpu_sid'));
							base_form.findField('PrehospDirect_id').setValue(2);
						}

						if( !Ext.isEmpty(base_form.findField('Org_sid').getValue()) ) {
							win.outer = true;
							var org_sid_combo = base_form.findField('Org_sid');

							base_form.findField('Org_sid').getStore().load({
								params: {
									Object:'Org',
									Org_id: org_sid_combo.getValue(),
									Org_Name:''
								},
								callback: function() {
									if ( org_sid_combo.getStore().getCount() == 1 ) {
										org_sid_combo.setValue( org_sid_combo.getStore().getAt(0).get('Org_id') );
									}
								}
							});

							base_form.findField('PrehospDirect_id').setValue(3);
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_PATHOMORPH_EDMHEFEDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_PATHOMORPH_EDMHEFVIEW);
							this.enableEdit(false);

							this.ItemList.getGrid().getTopToolbar().items.items[0].disable();
							this.ItemList.getGrid().getTopToolbar().items.items[3].disable();
						}

						if ( base_form.findField('pmUser_Name').getValue().toString().length > 0 ) {
							this.findById('EDMHEF_Caption').show();
						}

						if ( this.action == 'edit' ) {
							setCurrentDateTime({
								dateField: base_form.findField('EvnDirectionMorfoHistologic_setDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: this.id
							});
						}
						else {
							this.ItemList.getGrid().getColumnModel().setEditable(2, false);
							this.ItemList.getGrid().getColumnModel().setEditable(3, false);
							this.ItemList.getGrid().getColumnModel().setEditable(4, false);
						}

						var diag_id = base_form.findField('Diag_id').getValue();
						var diag_oid = base_form.findField('Diag_oid').getValue();
						var diag_sid = base_form.findField('Diag_sid').getValue();
						var index;
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var med_personal_id = base_form.findField('MedPersonal_id').getValue();
						var org_did = base_form.findField('Org_did').getValue();
						var record;

						if ( this.action == 'edit' ) {
							base_form.findField('EvnDirectionMorfoHistologic_setDate').fireEvent('change', base_form.findField('EvnDirectionMorfoHistologic_setDate'), base_form.findField('EvnDirectionMorfoHistologic_setDate').getValue());

							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
								if ( record.get('LpuSection_id') == lpu_section_id && record.get('MedPersonal_id') == med_personal_id ) {
									return true;
								}
								else {
									return false;
								}
							});

							if ( index >= 0 ) {
								base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}
						else {
							base_form.findField('LpuSection_id').getStore().load({
								callback: function() {
									index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
										if ( rec.get('LpuSection_id') == lpu_section_id ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
										base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
									}

									if(win.action == 'edit') {
										win.refreshOuterFieldsAccess();
									} else if(win.outer) {
										base_form.findField('Lpu_sid').showContainer();
										base_form.findField('EvnDirectionMorfoHistologic_LawDocumentDate').showContainer();
										base_form.findField('Org_sid').showContainer();
										base_form.findField('PrehospDirect_id').showContainer();
									} else {
										base_form.findField('Lpu_sid').hideContainer();
										base_form.findField('EvnDirectionMorfoHistologic_LawDocumentDate').hideContainer();
										base_form.findField('Org_sid').hideContainer();
										base_form.findField('PrehospDirect_id').hideContainer();
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: lpu_section_id,
									mode: 'combo'
								}
							});

							base_form.findField('MedStaffFact_id').getStore().load({
								callback: function() {
									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: lpu_section_id,
									MedPersonal_id: med_personal_id
								}
							});
						}

						if ( diag_id ) {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
								},
								params: {
									where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
								}
							});
						}

						if ( diag_oid ) {
							base_form.findField('Diag_oid').getStore().load({
								callback: function() {
									base_form.findField('Diag_oid').fireEvent('select', base_form.findField('Diag_oid'), base_form.findField('Diag_oid').getStore().getAt(0), 0);
								},
								params: {
									where: "where DiagLevel_id = 4 and Diag_id = " + diag_oid
								}
							});
						}

						if ( diag_sid ) {
							base_form.findField('Diag_sid').getStore().load({
								callback: function() {
									base_form.findField('Diag_sid').fireEvent('select', base_form.findField('Diag_sid'), base_form.findField('Diag_sid').getStore().getAt(0), 0);
								},
								params: {
									where: "where DiagLevel_id = 4 and Diag_id = " + diag_sid
								}
							});
						}

						if ( org_did ) {
							base_form.findField('Org_did').getStore().load({
								callback: function(records, options, success) {
									base_form.findField('Org_did').setValue(org_did);
								},
								params: {
									Org_id: org_did,
									OrgType: 'anatom'
								}
							});
						}

						loadMask.hide();

						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnDirectionMorfoHistologic_setDate').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnDirectionMorfoHistologic&m=loadEvnDirectionMorfoHistologicEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 750
});