/**
* swEvnUslugaParStreamInputWindow - окно потокового ввода параклинических услуг.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Parka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-12.05.2010
* @comment      Префикс для id компонентов EUPSIF (EvnUslugaParStreamInputForm)
*/

sw.Promed.swEvnUslugaParStreamInputWindow = Ext.extend(sw.Promed.BaseForm, {
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	autoScroll: true,
	closeAction: 'hide',
	collapsible: false,
	deleteEvnUslugaPar: function() {
		var grid = this.findById('EUPSIF_EvnUslugaParGrid').getGrid();
		var view_frame = this.findById('EUPSIF_EvnUslugaParGrid');

		if ( !view_frame || !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_nayden_spisok_uslug']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_usluga_iz_spiska']);
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var evn_usluga_par_id = selected_record.get('EvnUslugaPar_id');

		if ( !evn_usluga_par_id ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_usdushi_voznikli_oshibki_[tip_oshibki_2]']);
						},
						params: {
							EvnUslugaPar_id: evn_usluga_par_id
						},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_paraklinicheskoy_uslugi']);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
									view_frame.addEmptyRecord(grid.getStore());
								}
							}

							view_frame.focus();
						},
						url: '/?c=EvnUslugaPar&m=deleteEvnUslugaPar'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_paraklinicheskuyu_uslugu'],
			title: lang['vopros']
		});
	},
	draggable: true,
	height: 550,
	id: 'EvnUslugaParStreamInputWindow',
	printCost: function() {
		var grid = this.findById('EUPSIF_EvnUslugaParGrid').getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnUslugaPar_id')) {
			getWnd('swCostPrintWindow').show({
				Evn_id: selected_record.get('EvnUslugaPar_id'),
				type: 'EvnUslugaPar',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	filterLpuCombo: function() {
		var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();
		// фильтр на МО (отображать только открытые действующие)
		var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		if ( !Ext.isEmpty(base_form.findField('EvnUslugaPar_setDate').getValue()) ) {
			curDate = base_form.findField('EvnUslugaPar_setDate').getValue();
		}
		base_form.findField('Lpu_uid').lastQuery = '';
		base_form.findField('Lpu_uid').getStore().clearFilter();
		base_form.findField('Lpu_uid').setBaseFilter(function(rec, id) {
			if (!Ext.isEmpty(rec.get('Lpu_EndDate'))) {
				var lpuEndDate = Date.parseDate(rec.get('Lpu_EndDate'), 'd.m.Y');
				if (lpuEndDate < curDate) {
					return false;
				}
			}
			if (!Ext.isEmpty(getGlobalOptions().lpu_id) && rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
				return false;
			}
			return true;
		});
	},
	filterLpuSection: function() {
		var base_form = this.findById('EvnUslugaParStreamInputForm').getForm(),
			Lpu_id = base_form.findField('Lpu_uid').getValue(),
			MedSpecOms_id = base_form.findField('MedSpecOms_id').getValue(),
			UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue(),
			LpuSection_id_field = base_form.findField('LpuSection_id'),
			LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();

		//Фильтруем отделения и врачей
		if (!Ext.isEmpty(Lpu_id) && !Ext.isEmpty(UslugaPlace_id) && UslugaPlace_id == 2) {

			LpuSection_id_field.getStore().clearFilter();

			if (!Ext.isEmpty(LpuSectionProfile_id)){

				var LpuSection_id = LpuSection_id_field.getValue();
				LpuSection_id_field.getStore().clearFilter();
				LpuSection_id_field.setBaseFilter(function(rec, id) {
					return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id || (!Ext.isEmpty(rec.get('LpuSectionLpuSectionProfileList')) && LpuSectionProfile_id.inlist(rec.get('LpuSectionLpuSectionProfileList').split(','))));
				});

				LpuSection_id_field.clearValue();
				LpuSection_id_field.getStore().findBy(function(rec) {
					if ( rec.get('LpuSection_id') == LpuSection_id ) {
						LpuSection_id_field.setValue(rec.get('LpuSection_id'));
						return true;
					}
					return false;
				});
			}

			//Фильтруем записи по врачам
			this.filterMedStaff();
		}
	},
	filterMedStaff: function() {

		var base_form = this.findById('EvnUslugaParStreamInputForm').getForm(),
			MedSpecOms_id = base_form.findField('MedSpecOms_id').getValue(),
			UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue(),
			Lpu_id = base_form.findField('Lpu_uid').getValue(),
			MedStaffFact_id_field = base_form.findField('MedStaffFact_id'),
			MedStaffFact_id = MedStaffFact_id_field.getValue(),
			MedPersonal_id = MedStaffFact_id_field.getFieldValue('MedPersonal_id'),
			MedStaffFact_sid_field = base_form.findField('MedStaffFact_sid'),
			MedStaffFact_sid = MedStaffFact_sid_field.getValue(),
			MedPersonal_sid = MedStaffFact_sid_field.getFieldValue('MedPersonal_id'),
			LpuSection_id_field = base_form.findField('LpuSection_id'),
			LpuSection_id = base_form.findField('LpuSection_id').getValue(),
			LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();

		if (!Ext.isEmpty(UslugaPlace_id) && UslugaPlace_id == 2) {
			if (!Ext.isEmpty(Lpu_id)) {
				MedStaffFact_id_field.clearBaseFilter();
				MedStaffFact_sid_field.clearBaseFilter();

				var LpuSectionRecords = [];
				LpuSection_id_field.getStore().each(function (rec) {
					LpuSectionRecords.push(rec.get('LpuSection_id'));
				});

				//var MedStaffFact_id = MedStaffFact_id_field.getValue();

				MedStaffFact_id_field.setBaseFilter(function (rec) {
					if (!Ext.isEmpty(LpuSection_id) && !Ext.isEmpty(MedSpecOms_id)) {
						log((rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id') == LpuSection_id));
						return (rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id') == LpuSection_id);
					} else if (!Ext.isEmpty(LpuSection_id)) {
						return ( rec.get('LpuSection_id') == LpuSection_id);
					} else if (!Ext.isEmpty(MedSpecOms_id)) {
						return ( rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id').inlist(LpuSectionRecords));
					} else {
						return (rec.get('LpuSection_id').inlist(LpuSectionRecords));
					}
				});

				checkValueInStore(base_form, 'MedStaffFact_id', 'MedStaffFact_id', MedStaffFact_id);
				if (Ext.isEmpty(MedStaffFact_id_field.getValue())) {
					checkValueInStore(base_form, 'MedStaffFact_id', 'MedPersonal_id', MedPersonal_id);
				}

				MedStaffFact_sid_field.setBaseFilter(function (rec) {
					if (!Ext.isEmpty(LpuSection_id) && !Ext.isEmpty(MedSpecOms_id)) {
						return ( rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id') == LpuSection_id);
					} else if (!Ext.isEmpty(LpuSection_id)) {
						return ( rec.get('LpuSection_id') == LpuSection_id);
					} else if (!Ext.isEmpty(MedSpecOms_id)) {
						return ( rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id').inlist(LpuSectionRecords));
					} else {
						return ( rec.get('LpuSection_id').inlist(LpuSectionRecords));
					}
				});

				checkValueInStore(base_form, 'MedStaffFact_sid', 'MedStaffFact_id', MedStaffFact_sid);
				if (Ext.isEmpty(MedStaffFact_sid_field.getValue())) {
					checkValueInStore(base_form, 'MedStaffFact_sid', 'MedPersonal_id', MedPersonal_sid);
				}
			} else {
				base_form.findField('MedStaffFact_id').getStore().removeAll();
				base_form.findField('MedStaffFact_sid').getStore().removeAll();
			}
		}
	},
	filterProfile: function() {
		var base_form = this.findById('EvnUslugaParStreamInputForm').getForm(),
			Lpu = base_form.findField('LpuSection_id'),
			Profile = base_form.findField('LpuSectionProfile_id'),
			Profile_id = Lpu.getFieldValue('LpuSectionProfile_id'),
			Profile_list = Lpu.getFieldValue('LpuSectionLpuSectionProfileList');
		
		//сначало чистим
		Profile.setValue(null);

		if (!Ext.isEmpty(Profile_id)) {
			Profile.getStore().findBy(function(rec) {
				return (rec.get('LpuSectionProfile_id') === Profile_id);
			});

			Profile.getStore().findBy(function(rec) {
				if ( rec.get('LpuSectionProfile_id') === Profile_id ) {
					Profile.setValue(Profile_id);
					return true;
				}
				return false;
			});

			if (Ext.isEmpty(Profile.getValue())) {
				Profile.getStore().findBy(function(rec) {
					if (!Ext.isEmpty(Profile_list)
						&& rec.get('LpuSectionProfile_id').inlist(Profile_list.split(','))) {
						Profile.setValue(rec.get('LpuSectionProfile_id'));
						return true;
					}
					return false;
				});
			}
		}
	},
	loadLpuData: function() {

		var base_form = this.findById('EvnUslugaParStreamInputForm').getForm(),
			_this = this,
			Lpu_id = base_form.findField('Lpu_uid').getValue(),
			UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue(),
			MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue(),
			LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();

		//Фильтруем поле отделение по профилю и МО
		if (!Ext.isEmpty(Lpu_id) && !Ext.isEmpty(UslugaPlace_id) && UslugaPlace_id == 2) {

			var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
			loadMask.show();
			base_form.findField('LpuSection_id').getStore().baseParams.Lpu_id = Lpu_id;
			base_form.findField('LpuSection_id').getStore().baseParams.mode = 'addSubProfile';
			base_form.findField('LpuSection_id').getStore().removeAll();
			base_form.findField('LpuSection_id').lastQuery = 'The string that will never appear';
			base_form.findField('LpuSection_id').getStore().load({
                callback: function() {
					
					//грузим данные по врачам
					base_form.findField('MedStaffFact_id').getStore().baseParams.Lpu_id = Lpu_id;
					base_form.findField('MedStaffFact_id').getStore().baseParams.mode = 'addSubProfile';
					base_form.findField('MedStaffFact_id').getStore().baseParams.isDoctor = 1;
					base_form.findField('MedStaffFact_id').getStore().baseParams.andWithoutLpuSection = 3;
					base_form.findField('MedStaffFact_id').getStore().removeAll();
					base_form.findField('MedStaffFact_id').lastQuery = 'The string that will never appear';
					base_form.findField('MedStaffFact_id').getStore().load({
						callback: function() {
							base_form.findField('MedStaffFact_sid').getStore().baseParams.Lpu_id = Lpu_id;
							base_form.findField('MedStaffFact_sid').getStore().baseParams.mode = 'addSubProfile';
							base_form.findField('MedStaffFact_sid').getStore().baseParams.isMidMedPersonal = 1;
							base_form.findField('MedStaffFact_sid').getStore().baseParams.andWithoutLpuSection = 3;
							base_form.findField('MedStaffFact_sid').getStore().removeAll();
							base_form.findField('MedStaffFact_sid').lastQuery = 'The string that will never appear';
							base_form.findField('MedStaffFact_sid').getStore().load({
								callback: function() {
									loadMask.hide();
									_this.filterLpuSection();
								}
							});
						}
					});
				}
			});
		} else {
			base_form.findField('LpuSection_id').clearValue();
			base_form.findField('LpuSection_id').getStore().removeAll();
			base_form.findField('MedStaffFact_id').clearValue();
			base_form.findField('MedStaffFact_id').getStore().removeAll();
			base_form.findField('MedStaffFact_sid').clearValue();
			base_form.findField('MedStaffFact_sid').getStore().removeAll();
		}
	},
	initComponent: function() {
		var win = this;

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'EUPSIF_CancelButton',
				onTabAction: function () {
					this.findById('EvnUslugaParStreamInputForm').getForm().findField('EvnDirection_setDate_From').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EUPSIF + 11,
				text: lang['zakryit']
			}],
			items: [{
				//autoHeight: true,
				//height: 370,//610 теперь, вроде, не нужно указывать
				layout: 'form',
				region: 'center',//north - был, south - у ViewFrame ниже #132866
				autoScroll: true,
				items: [ new Ext.form.FormPanel({
					bodyStyle: 'padding: 5px',
					border: false,
					frame: false,
					id: 'EUPSIF_StreamInformationForm',
					items: [{
						disabled: true,
						fieldLabel: lang['polzovatel'],
						id: 'EUPSIF_pmUser_Name',
						width: 380,
						xtype: 'textfield'
					}, {
						disabled: true,
						fieldLabel: lang['data_nachala_vvoda'],
						id: 'EUPSIF_Stream_begDateTime',
						width: 130,
						xtype: 'textfield'
					}],
					labelAlign: 'right',
					labelWidth: 120
				}),
				new Ext.form.FormPanel({
					animCollapse: false,
					autoHeight: true,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					buttonAlign: 'left',
					frame: false,
					id: 'EvnUslugaParStreamInputForm',
					items: [{
						autoHeight: true,
						hidden: getRegionNick().inlist(['perm', 'ekb']),
						style: 'padding: 0px;',
						title: lang['poisk_elektronnyih_napravleniy'],
						width: 780,
						xtype: 'fieldset',

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['data_napravleniya_s'],
									format: 'd.m.Y',
									listeners: {
										'change': function(field, newValue, oldValue) {
											this.loadEvnDirectionGrid();
										}.createDelegate(this),
										'keydown': function(inp, e) {
											if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
												e.stopEvent();
												this.buttons[this.buttons.length - 1].focus();
											}
										}.createDelegate(this)
									},
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
									name: 'EvnDirection_setDate_From',
									tabIndex: TABINDEX_EUPSIF + 1,
									width: 100,
									xtype: 'swdatefield'
								}]
							}, {
								border: false,
								labelWidth: 30,
								layout: 'form',
								items: [{
									fieldLabel: lang['po'],
									format: 'd.m.Y',
									listeners: {
										'change': function(field, newValue, oldValue) {
											this.loadEvnDirectionGrid();
										}.createDelegate(this)
									},
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
									name: 'EvnDirection_setDate_To',
									tabIndex: TABINDEX_EUPSIF + 2,
									width: 100,
									xtype: 'swdatefield'
								}]
							}]
						}, {
							hiddenName: 'LpuUnit_id',
							id: 'EUPSIF_LpuUnitCombo',
							linkedElements: [
								'EUPSIF_LpuSectionCombo'
							],
							listWidth: 600,
							tabIndex: TABINDEX_EUPSIF + 3,
							width: 450,
							xtype: 'swlpuunitglobalcombo'
						}, {
							hiddenName: 'LpuSection_uid',
							id: 'EUPSIF_LpuSectionCombo',
							/*linkedElements: [
								'EUPSIF_MedStaffFactCombo',
								'EUPSIF_MedStaffFactCombo2'
							],*/
							listWidth: 600,
							parentElementId: 'EUPSIF_LpuUnitCombo',
							tabIndex: TABINDEX_EUPSIF + 4,
							width: 450,
							xtype: 'swlpusectionglobalcombo'
						}]
					},
					new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', disabled: true },
						{ name: 'action_edit', disabled: true },
						{ name: 'action_view', disabled: true },
						{ name: 'action_delete', disabled: true },
						{ name: 'action_refresh', handler: function() { this.loadEvnDirectionGrid(); }.createDelegate(this) },
						{ name: 'action_print', disabled: true }
					],
					hidden: getRegionNick().inlist(['perm', 'ekb']),
					autoExpandColumn: 'autoexpand_dir',
					autoExpandMin: 150,
					autoLoadData: false,
					clearSelectionsOnTab: false,
					dataUrl: '/?c=EvnUslugaPar&m=loadEvnDirectionList',
					focusOn: {
						name: 'EUPSIF_EvnUslugaParGrid',
						type: 'grid'
					},
					focusPrev: {
						name: 'EUPSIF_PayTypeCombo',
						type: 'field'
					},
					height: 150,
					id: 'EUPSIF_EvnDirectionGrid',
					pageSize: 100,
					paging: false,
					style: 'margin-bottom: 10px;',
					// region: 'center',
					// root: 'data',
					stringfields: [
						{ name: 'EvnDirection_id', type: 'int', header: 'ID', key: true },
						{ name: 'Person_id', type: 'int', hidden: true },
						{ name: 'PersonEvn_id', type: 'int', hidden: true },
						{ name: 'Server_id', type: 'int', hidden: true },
						{ name: 'EvnDirection_Num', type: 'string', hidden: true },
						{ name: 'Lpu_did', type: 'int', hidden: true },
						{ name: 'EvnDirection_setDate', type: 'date', format: 'd.m.Y', header: lang['data'], width: 100 },
						{ name: 'EvnDirection_setTime', type: 'string', header: lang['vremya'], width: 100 },
						{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150 },
						{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150 },
						{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150 },
						{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 100 },
						{ name: 'Lpu_Name', type: 'string', header: lang['lpu'], width: 200 },
						{ name: 'MedPersonal_Fio', type: 'string', header: lang['vrach'], id: 'autoexpand_dir' }
					],
					toolbar: true //,
					// totalProperty: 'totalCount'
				}), {
						layout: 'form',
						border: false,
						hidden: getRegionNick().inlist(['perm', 'ekb']),
						items: [{
							xtype: 'swdatefield',
							name: 'EvnDirection_setDate',
							fieldLabel: lang['data_napravleniya']
						}]
					}, {
						enableKeyEvents: true,
						listeners: {
							'change':function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function (rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							},
							'select': function(combo, record, idx) {
								var base_form = win.findById('EvnUslugaParStreamInputForm').getForm();
								if ( typeof record == 'object' && !Ext.isEmpty(record.get('UslugaPlace_id')) && record.get('UslugaPlace_id') == 2 ) {
									base_form.findField('LpuSection_id').disableLinkedElements();
									base_form.findField('MedStaffFact_id').disableParentElement();
									base_form.findField('MedStaffFact_sid').disableParentElement();
									base_form.findField('Lpu_uid').showContainer();
									base_form.findField('Lpu_uid').setAllowBlank(false);
									base_form.findField('LpuSection_id').setAllowBlank(true);
									base_form.findField('MedStaffFact_id').setAllowBlank(true);
									base_form.findField('MedStaffFact_sid').setAllowBlank(true);

								} else {
									base_form.findField('LpuSection_id').enableLinkedElements();
									base_form.findField('MedStaffFact_id').enableParentElement();
									base_form.findField('MedStaffFact_sid').enableParentElement();
									base_form.findField('Lpu_uid').hideContainer();
									base_form.findField('Lpu_uid').clearValue();
									base_form.findField('Lpu_uid').setAllowBlank(true);
									base_form.findField('MedStaffFact_id').enable();

									setLpuSectionGlobalStoreFilter();
									setMedStaffFactGlobalStoreFilter();

									base_form.findField('LpuSection_uid').clearBaseFilter();
									base_form.findField('MedStaffFact_id').clearBaseFilter();
									base_form.findField('MedStaffFact_sid').clearBaseFilter();

									base_form.findField('LpuUnit_id').getStore().loadData(getStoreRecords(swLpuUnitGlobalStore));
									base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
									base_form.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
								}
                                base_form.findField('LpuSectionProfile_id').onChangeUslugaPlaceField(combo, record.get('UslugaPlace_Code'));
                                base_form.findField('MedSpecOms_id').onChangeUslugaPlaceField(combo, record.get('UslugaPlace_Code'));
							}
						},
						hiddenName: 'UslugaPlace_id',
						allowBlank: false,
						tabIndex: TABINDEX_EUPSIF + 5,
						validateOnBlur: false,
						width: 350,
						fieldLabel: lang['mesto_vyipolneniya'],
						comboSubject: 'UslugaPlace',
						xtype: 'swcommonsprcombo',
						value: 1
					}, {
						comboSubject: 'Lpu',
						fieldLabel: lang['mo'],
						xtype: 'swcommonsprcombo',
						editable: true,
						forceSelection: true,
						displayField: 'Lpu_Nick',
						codeField: 'Lpu_Code',
						orderBy: 'Nick',
						listeners: {
							'change':function(combo, newValue, oldValue) {
								log('change lpu');
								//Грузим данные по отделениям и врачам с базы при выборе МО
								win.loadLpuData();
							}
						},
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Lpu_Nick}',
							'</div></tpl>'
						),
						moreFields: [
							{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
							{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'}
						],
						tabIndex: TABINDEX_EUPSIF + 5,
						width: 350,
						hiddenName: 'Lpu_uid',
						onLoadStore: function() {
							win.filterLpuCombo();
						}
					}, {
                        hidden: true,
                        lastQuery: '',
                        xtype: 'swlpusectionprofilewithfedcombo',
						tabIndex: TABINDEX_EUPSIF + 5,
						width: 350,
						onTrigger2Click: function(){
							win.findById('EvnUslugaParStreamInputForm').getForm().findField('LpuSectionProfile_id').clearValue();
							win.filterLpuSection();
						},
						listeners: {
							'change':function(combo, newValue, oldValue) {
								win.filterLpuSection();
							}
						},
						hiddenName: 'LpuSectionProfile_id'
					}, {
                        hidden: true,
                        lastQuery: '',
                        xtype: 'swmedspecomswithfedcombo',
						tabIndex: TABINDEX_EUPSIF + 5,
						width: 350,
						onTrigger2Click: function(){
							win.findById('EvnUslugaParStreamInputForm').getForm().findField('MedSpecOms_id').clearValue();
							win.filterMedStaff();
						},
						listeners: {
							'change':function(combo, newValue, oldValue) {
								//Фильтруем список врачей
								win.filterMedStaff();
							}
						},
						hiddenName: 'MedSpecOms_id'
					}, {
						xtype: 'swlpusectionglobalcombo',
						hiddenName: 'LpuSection_id',
						id: 'EUPSIF_LpuSectionCombo2',
						listWidth: 600,
						tabIndex: TABINDEX_EUPSIF + 5,
						lastQuery: '',
						listeners: {
							'change':function(combo, newValue, oldValue) {
								//обновляем профиль если поставили отделение
								win.filterProfile();
								//Фильтруем список врачей
								win.filterMedStaff();
							}
						},
						linkedElements: [
							'EUPSIF_MedStaffFactCombo',
							'EUPSIF_MedStaffFactCombo2'
						],
						width: 450,
						fieldLabel: lang['otdelenie']
					}, {
						fieldLabel: lang['vrach_vyipolnivshiy_uslugu'],
						hiddenName: 'MedStaffFact_id',
						id: 'EUPSIF_MedStaffFactCombo',
						listWidth: 600,
						ignoreDisableInDoc: true,
						lastQuery: '',
						listeners: {
							'change':function(combo, newValue, oldValue) {
								//обновляем профиль если поставили врача
								win.filterProfile();

								var base_form = win.findById('EvnUslugaParStreamInputForm').getForm();
								if (base_form.findField('UslugaPlace_id').getValue() == 2 && !Ext.isEmpty(combo.getFieldValue('MedSpecOms_id'))) {

									var index = base_form.findField('MedSpecOms_id').getStore().findBy(function(rec) {
										return (rec.get('MedSpecOms_id') == combo.getFieldValue('MedSpecOms_id'));
									});

									if (index >= 0) {
										base_form.findField('MedSpecOms_id').setValue(combo.getFieldValue('MedSpecOms_id'));
									}
								}
							},
							'select': function (combo, record) {
								combo.fireEvent('change', combo, -1, combo.getValue());
							}
						},
						parentElementId: 'EUPSIF_LpuSectionCombo2',
						tabIndex: TABINDEX_EUPSIF + 5,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						fieldLabel: lang['cred_medpersonal_vyipolnivshiy_uslugu'],
						hiddenName: 'MedStaffFact_sid',
						lastQuery: '',
						ignoreDisableInDoc: true,
						id: 'EUPSIF_MedStaffFactCombo2',
						parentElementId: 'EUPSIF_LpuSectionCombo2',
						listWidth: 600,
						tabIndex: TABINDEX_EUPSIF + 6,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						layout: 'column',
						border: false,
						items: [{
							layout: 'form',
							border: false,
							items: [{
								fieldLabel: lang['data_nachala_vyipolneniya'],
								format: 'd.m.Y',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
								name: 'EvnUslugaPar_setDate',
								tabIndex: TABINDEX_EUPSIF + 7,
								width: 100,
								xtype: 'swdatefield',
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();
										var lpu_section_combo = base_form.findField('LpuSection_id');
										var med_staff_fact_combo = base_form.findField('MedStaffFact_id');

										// Сохраняем старые значения полей
										var lpu_section_id = lpu_section_combo.getValue();
										var med_staff_fact_id = med_staff_fact_combo.getValue();

										lpu_section_combo.clearValue();
										lpu_section_combo.getStore().removeAll();
										med_staff_fact_combo.clearValue();
										med_staff_fact_combo.getStore().removeAll();

										var section_filter_params = {
											allowLowLevel: 'yes',
											onDate: Ext.util.Format.date(newValue, 'd.m.Y')
										}

										var medstafffact_filter_params = {
											allowLowLevel: 'yes',
											onDate: Ext.util.Format.date(newValue, 'd.m.Y')
										}

										if ( getGlobalOptions().region ) {
											medstafffact_filter_params.regionCode = getGlobalOptions().region.number;
											section_filter_params.regionCode = getGlobalOptions().region.number;
										}

										// Фильтруем список отделений на выбранную дату
										setLpuSectionGlobalStoreFilter(section_filter_params);

										// Фильтруем список врачей на выбранную дату
										setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

										// Загружаем список отделений
										lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
										// Загружаем список врачей
										med_staff_fact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

										// Ищем в списках записи, соответствующие старым значениям
										var lpu_section_record = lpu_section_combo.getStore().getById(lpu_section_id);
										var med_staff_fact_record = med_staff_fact_combo.getStore().getById(med_staff_fact_id);

										// Если запись не найдена
										if ( !lpu_section_record ) {
											// значение комбо не устанавливается, вызывается снятие фильтра по отделению со списка врачей
											lpu_section_combo.fireEvent('change', lpu_section_combo, -1, lpu_section_id);
										}
										else {
											// устанавливается старое значение комбо
											lpu_section_combo.setValue(lpu_section_id);
										}

										// Если запись не найдена
										if ( !med_staff_fact_record ) {
											// значение комбо не устанавливается, вызывается снятие фильтра по отделению со списка врачей
											med_staff_fact_combo.fireEvent('change', med_staff_fact_combo, -1, med_staff_fact_id);
										}
										else {
											// устанавливается старое значение комбо
											med_staff_fact_combo.setValue(med_staff_fact_id);
										}

										this.filterLpuCombo();
										base_form.findField('LpuSectionProfile_id').onChangeDateField(field, newValue);
										base_form.findField('MedSpecOms_id').onChangeDateField(field, newValue);
									}.createDelegate(this)
								}
							}]
						}, {
							layout: 'form',
							labelWidth: 50,
							border: false,
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
								name: 'EvnUslugaPar_setTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();

									var time_field = base_form.findField('EvnUslugaPar_setTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnUslugaPar_setDate'),
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
								tabIndex: TABINDEX_EUPSIF + 7,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					}, {
						border: false,
						hidden: getRegionNick().inlist(['perm', 'ekb']),
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							width: 400,
							items: [{
								hiddenName: 'PrehospDirect_id',
								listWidth: 300,
								tabIndex: TABINDEX_EUPSIF + 8,
								width: 200,
								xtype: 'swprehospdirectcombo',
								listeners: {
									'change': function(combo, newValue, oldValue)
									{
										var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();

										if (newValue != 1) {
											base_form.findField('LpuSection_did').clearValue();
										}
									}.createDelegate(this)
								}
							}]
						}, {
							border: false,
							layout: 'form',
							width: 400,
							labelWidth: 90,
							items: [{
								hiddenName: 'LpuSection_did',
								id: 'EUPSIF_LpuSectionDirCombo',
								lastQuery: '',
								tabIndex: TABINDEX_EUPSIF + 8,
								width: 300,
								listWidth: 600,
								xtype: 'swlpusectionglobalcombo',
								listeners: {
									'change': function(combo, newValue, oldValue)
									{
										var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();

										if (newValue > 0) {
											base_form.findField('PrehospDirect_id').setValue(1);
										}
									}.createDelegate(this)
								}
							}]
						}]
					}, /*{
						fieldLabel: lang['usluga'],
						hiddenName: 'Usluga_id',
						listWidth: 600,
						tabIndex: TABINDEX_EUPSIF + 9,
						width: 450,
						xtype: 'swuslugacombo'
					},*/
					{
						fieldLabel: lang['kategoriya_uslugi'],
						hiddenName: 'UslugaCategory_id',
						listeners: {
							'select': function (combo, record) {
								var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();

								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').getStore().removeAll();

								if ( !record ) {
									base_form.findField('UslugaComplex_id').setUslugaCategoryList();
									return false;
								}

								base_form.findField('UslugaComplex_id').setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);

								return true;
							}.createDelegate(this)
						},
						loadParams: (getRegionNick() == 'kz' ? {params: {where: "where UslugaCategory_SysNick in ('classmedus')"}} : null),
						tabIndex: TABINDEX_EUPSIF + 9,
						width: 200,
						xtype: 'swuslugacategorycombo'
					}, {
						fieldLabel: lang['usluga'],
						hiddenName: 'UslugaComplex_id',
						listWidth: 700,
						tabIndex: TABINDEX_EUPSIF + 10,
						width: 450,
						listeners: {
							'select': function(combo, newValue, oldValue) {
								win.findById('EvnUslugaParStreamInputForm').getForm().findField('FSIDI_id').checkVisibilityAndGost(combo.value);
							},
							'change': function(combo, newValue, oldValue) {
								if (getRegionNick() == 'ekb') {
									var base_form = win.findById('EvnUslugaParStreamInputForm').getForm();
									var Diag_Code = null;
									var UslugaComplex_Code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
									switch (UslugaComplex_Code) {
										case 'A06.09.006':
										case 'A06.09.006.888':
											Diag_Code = 'Z11.1';
											break;
										case 'A06.20.004':
                                        case 'A06.20.004.888':
                                            Diag_Code = 'Z01.8';
											break;
										case 'A06.30.003.001':
										case 'A06.30.003.002':
											Diag_Code = 'Z03.8';
											break;
									}

									if (Diag_Code) {
										base_form.findField('Diag_id').getStore().load({
											params: {where: "where Diag_Code = '" + Diag_Code + "'"},
											callback: function () {
												if (base_form.findField('Diag_id').getStore().getCount() > 0) {
													var diag_id = base_form.findField('Diag_id').getStore().getAt(0).get('Diag_id');
													base_form.findField('Diag_id').setValue(diag_id);
													base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
												}
											}
										});
									}
								}
							}
						},
						xtype: 'swuslugacomplexnewcombo'
					}, {
						xtype: 'swfsidicombo',
						hiddenName: 'FSIDI_id',
						width: 480,
						listWidth: 500,
						labelWidth: 250,
						hideOnInit: true
					}, {
						hiddenName: 'Diag_id',
						tabIndex: TABINDEX_EUPSIF + 10,
						fieldLabel: 'Диагноз',
						width: 450,
						xtype: 'swdiagcombo'
					}, {
						useCommonFilter: true,
						id: 'EUPSIF_PayTypeCombo',
						listeners: {
                            'change': function (combo, newValue, oldValue) {
                                var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();
                                base_form.findField('LpuSectionProfile_id').onChangePayTypeField(combo, combo.getFieldValue('PayType_SysNick'));
                                base_form.findField('MedSpecOms_id').onChangePayTypeField(combo, combo.getFieldValue('PayType_SysNick'));
                            }.createDelegate(this),
							'keydown': function(inp, e) {
								if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
									e.stopEvent();

									if ( this.findById('EUPSIF_EvnDirectionGrid').getGrid().getStore().getCount() > 0 ) {
										this.findById('EUPSIF_EvnDirectionGrid').focus();
									}
									else if ( this.findById('EUPSIF_EvnUslugaParGrid').getGrid().getStore().getCount() > 0 ) {
										this.findById('EUPSIF_EvnUslugaParGrid').focus();
									}
									else {
										this.buttons[this.buttons.length - 1].focus();
									}
								}
							}.createDelegate(this)
						},
						listWidth: 300,
						tabIndex: TABINDEX_EUPSIF + 11,
						width: 200,
						xtype: 'swpaytypecombo'
					}],
					labelAlign: 'right',
					labelWidth: 165,
					title: lang['parametryi_vvoda']
				})]
			},
			new sw.Promed.ViewFrame({
				region: 'south',//center теперь у блока выше #132866
				actions: [
					{ name: 'action_add', handler: function() { this.openEvnUslugaParEditWindow('add'); }.createDelegate(this) },
					{ name: 'action_edit', handler: function() { this.openEvnUslugaParEditWindow('edit'); }.createDelegate(this) },
					{ name: 'action_view', handler: function() { this.openEvnUslugaParEditWindow('view'); }.createDelegate(this) },
					{ name: 'action_delete', handler: function() { this.deleteEvnUslugaPar(); }.createDelegate(this) },
					{ name: 'action_refresh', handler: function() { this.refreshEvnUslugaParGrid(); }.createDelegate(this), disabled: true },
					{ name: 'action_print',
						menuConfig: {
							printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost() }}
						}
					}
				],
				autoExpandColumn: 'autoexpand_usl',
				autoExpandMin: 150,
				height: 160,
				autoLoadData: false,
				dataUrl: '/?c=EvnUslugaPar&m=loadEvnUslugaParStreamList',
				focusOn: {
					name: 'EUPSIF_CancelButton',
					type: 'button'
				},
				focusPrev: {
					name: 'EUPSIF_EvnDirectionGrid',
					type: 'grid'
				},
				id: 'EUPSIF_EvnUslugaParGrid',
				pageSize: 100,
				paging: false,
				root: 'data',
				stringfields: [
					{ name: 'EvnUslugaPar_id', type: 'int', header: 'ID', key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'PersonEvn_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 150 },
					{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150 },
					{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 100 },
					{ name: 'EvnUslugaPar_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата'), width: 100 },
					{ name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 150 },
					{ name: 'MedPersonal_Fio', type: 'string', header: langs('Врач'), width: 150 },
					{ name: 'Usluga_Code', type: 'string', header: langs('Код услуги'), width: 100 },
					{ name: 'Usluga_Name', type: 'string', header: langs('Наименование услуги'), id: 'autoexpand_usl' },
					{ name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 100 },
					{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
					{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), header: langs('Справка о стоимости лечения'), width: 150 }
				],
				toolbar: true,
				totalProperty: 'totalCount'
			})
		]
		});
		sw.Promed.swEvnUslugaParStreamInputWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EUPSIF_EvnDirectionGrid').addListenersFocusOnFields();
		this.findById('EUPSIF_EvnUslugaParGrid').addListenersFocusOnFields();

		this.findById('EUPSIF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			this.loadEvnDirectionGrid();
		}.createDelegate(this));
	},
	keys: [{
		fn: function(inp, e) {
			Ext.getCmp('EvnUslugaParStreamInputWindow').openEvnUslugaParEditWindow('add');
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnUslugaParStreamInputWindow').hide();
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	layout: 'border',
	loadEvnDirectionGrid: function() {
		var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();
		var view_frame = this.findById('EUPSIF_EvnDirectionGrid');

		view_frame.removeAll();

		if ( !base_form.findField('LpuSection_id').getValue() ) {
			return false;
		}

		var evn_direction_set_date_from = Ext.util.Format.date(base_form.findField('EvnDirection_setDate_From').getValue(), 'd.m.Y');
		var evn_direction_set_date_to = Ext.util.Format.date(base_form.findField('EvnDirection_setDate_To').getValue(), 'd.m.Y');
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();

		view_frame.loadData({
			globalFilters: {
				EvnDirection_setDate_From: evn_direction_set_date_from,
				EvnDirection_setDate_To: evn_direction_set_date_to,
				LpuSection_id: lpu_section_id
			},
			noFocusOnLoad: true
		});
	},
	maximized: true,
	modal: false,
	openEvnUslugaParEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnUslugaParEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_paraklinicheskoy_uslugi_uje_otkryito']);
			return false;
		}

		var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();
		var grid = this.findById('EUPSIF_EvnUslugaParGrid').getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnUslugaData ) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.evnUslugaData.EvnUslugaPar_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnUslugaPar_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ data: [ data.evnUslugaData ]}, true);
			}
			else {
				var evn_usluga_par_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					evn_usluga_par_fields.push(key);
				});

				for ( i = 0; i < evn_usluga_par_fields.length; i++ ) {
					record.set(evn_usluga_par_fields[i], data.evnUslugaData[evn_usluga_par_fields[i]]);
				}

				record.commit();
			}

			grid.getStore().each(function(record) {
				if ( record.get('Person_id') == data.evnUslugaData.Person_id ) {
					record.set('Person_Birthday', data.evnUslugaData.Person_Birthday);
					record.set('Person_Surname', data.evnUslugaData.Person_Surname);
					record.set('Person_Firname', data.evnUslugaData.Person_Firname);
					record.set('Person_Secname', data.evnUslugaData.Person_Secname);
					record.set('Server_id', data.evnUslugaData.Server_id);

					record.commit();
				}
			});
		};

		if ( action == 'add' ) {
			params.EvnUslugaPar_setDate = base_form.findField('EvnUslugaPar_setDate').getValue();
			params.LpuSection_uid = base_form.findField('LpuSection_id').getValue();
			params.LpuSection_did = base_form.findField('LpuSection_did').getValue();
			params.MedStaffFact_uid = base_form.findField('MedStaffFact_id').getValue();
			params.MedStaffFact_sid = base_form.findField('MedStaffFact_sid').getValue();

			params.UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue();
			params.Lpu_uid = base_form.findField('Lpu_uid').getValue();
			params.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
			params.MedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();

			params.PayType_id = base_form.findField('PayType_id').getValue();
			params.PrehospDirect_id = base_form.findField('PrehospDirect_id').getValue();
			//params.Usluga_id = base_form.findField('Usluga_id').getValue();
			params.UslugaCategory_id = base_form.findField('UslugaCategory_id').getValue();
			params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			params.FSIDI_id = base_form.findField('FSIDI_id').getValue();
			params.Diag_id = base_form.findField('Diag_id').getValue();
			params.EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue();

			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					var form_params = new Object();

					form_params.EvnUslugaPar_Kolvo = 1;
					form_params.EvnUslugaPar_setDate = undefined;
					form_params.EvnUslugaPar_setTime = undefined;
					form_params.LpuSection_did = undefined;
					form_params.LpuSection_uid = undefined;
					form_params.MedStaffFact_did = undefined;
					form_params.MedStaffFact_uid = undefined;

					form_params.UslugaPlace_id = undefined;
					form_params.Lpu_uid = undefined;
					form_params.LpuSectionProfile_id = undefined;
					form_params.MedSpecOms_id = undefined;

					form_params.Org_did = undefined;
					form_params.PayType_id = undefined;
					form_params.PrehospDirect_id = undefined;
					//form_params.Usluga_id = undefined;
					form_params.UslugaCategory_id = undefined;
					form_params.UslugaComplex_id = undefined;
					form_params.FSIDI_id = undefined;

					Ext.apply(form_params, params);

					if ( getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_Kolvo ) {
						form_params.EvnUslugaPar_Kolvo = getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_Kolvo;
					}

					if ( getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_setDate ) {
						form_params.EvnUslugaPar_setDate = getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_setDate;
					}

					if ( getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_setTime ) {
						form_params.EvnUslugaPar_setTime = getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_setTime;
					}

					if ( getWnd('swPersonSearchWindow').formParams.LpuSection_did ) {
						form_params.LpuSection_did = getWnd('swPersonSearchWindow').formParams.LpuSection_did;
					}

					if ( getWnd('swPersonSearchWindow').formParams.LpuSection_uid ) {
						form_params.LpuSection_uid = getWnd('swPersonSearchWindow').formParams.LpuSection_uid;
					}

					if ( getWnd('swPersonSearchWindow').formParams.MedStaffFact_did ) {
						form_params.MedStaffFact_did = getWnd('swPersonSearchWindow').formParams.MedStaffFact_did;
					}

					if ( getWnd('swPersonSearchWindow').formParams.MedStaffFact_uid ) {
						form_params.MedStaffFact_uid = getWnd('swPersonSearchWindow').formParams.MedStaffFact_uid;
					}


					if ( getWnd('swPersonSearchWindow').formParams.UslugaPlace_id ) {
						form_params.UslugaPlace_id = getWnd('swPersonSearchWindow').formParams.UslugaPlace_id;
					}

					if ( getWnd('swPersonSearchWindow').formParams.Lpu_uid ) {
						form_params.Lpu_uid = getWnd('swPersonSearchWindow').formParams.Lpu_uid;
					}

					if ( getWnd('swPersonSearchWindow').formParams.LpuSectionProfile_id ) {
						form_params.LpuSectionProfile_id = getWnd('swPersonSearchWindow').formParams.LpuSectionProfile_id;
					}

					if ( getWnd('swPersonSearchWindow').formParams.MedSpecOms_id ) {
						form_params.MedSpecOms_id = getWnd('swPersonSearchWindow').formParams.MedSpecOms_id;
					}

					if ( getWnd('swPersonSearchWindow').formParams.Org_did ) {
						form_params.Org_did = getWnd('swPersonSearchWindow').formParams.Org_did;
					}

					if ( getWnd('swPersonSearchWindow').formParams.PayType_id ) {
						form_params.PayType_id = getWnd('swPersonSearchWindow').formParams.PayType_id;
					}

					if ( getWnd('swPersonSearchWindow').formParams.PrehospDirect_id ) {
						form_params.PrehospDirect_id = getWnd('swPersonSearchWindow').formParams.PrehospDirect_id;
					}

					/*if ( getWnd('swPersonSearchWindow').formParams.Usluga_id ) {
						form_params.Usluga_id = getWnd('swPersonSearchWindow').formParams.Usluga_id;
					}*/

					if ( getWnd('swPersonSearchWindow').formParams.UslugaCategory_id ) {
						form_params.UslugaCategory_id = getWnd('swPersonSearchWindow').formParams.UslugaCategory_id;
					}

					if ( getWnd('swPersonSearchWindow').formParams.UslugaComplex_id ) {
						form_params.UslugaComplex_id = getWnd('swPersonSearchWindow').formParams.UslugaComplex_id;
					}

					if ( getWnd('swPersonSearchWindow').formParams.FSIDI_id ) {
						form_params.FSIDI_id = getWnd('swPersonSearchWindow').formParams.FSIDI_id;
					}

					form_params.onHide = function() {
						getWnd('swPersonSearchWindow').formParams = new Object();
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					form_params.Person_Birthday = person_data.Person_Birthday;
					form_params.Person_Firname = person_data.Person_Firname;
					form_params.Person_id = person_data.Person_id;
					form_params.Person_Secname = person_data.Person_Secname;
					form_params.Person_Surname = person_data.Person_Surname;
					form_params.PersonEvn_id = person_data.PersonEvn_id;
					form_params.Server_id = person_data.Server_id;

					getWnd('swEvnUslugaParEditWindow').show(form_params);
				},
				searchMode: 'all',
				searchWindowOpenMode: 'EvnUslugaPar'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			var evn_usluga_par_id = selected_record.get('EvnUslugaPar_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');

			if ( evn_usluga_par_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.EvnUslugaPar_id = evn_usluga_par_id;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};
				params.Person_Birthday = selected_record.get('Person_Birthday');
				params.Person_Firname = selected_record.get('Person_Firname');
				params.Person_id = person_id;
				params.Person_Secname = selected_record.get('Person_Secname');
				params.Person_Surname = selected_record.get('Person_Surname');
				params.Server_id = server_id;

				getWnd('swEvnUslugaParEditWindow').show(params);
			}
		}
	},
	openEvnUslugaParEditWindowAdv: function() {
		if ( getWnd('swEvnUslugaParEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_paraklinicheskoy_uslugi_uje_otkryito']);
			return false;
		}

		var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();
		var evn_direction_grid = this.findById('EUPSIF_EvnDirectionGrid').getGrid();
		var evn_usluga_par_grid = this.findById('EUPSIF_EvnUslugaParGrid').getGrid();
		var params = new Object();

		var evn_direction_record = evn_direction_grid.getSelectionModel().getSelected();

		if ( !evn_direction_record || !evn_direction_record.get('EvnDirection_id') ) {
			// sw.swMsg.alert('Сообщение', 'Не выбрано направление из списка направлений');
			return false;
		}

		params.action = 'add';
		params.callback = function(data) {
			if ( !data || !data.evnUslugaData ) {
				return false;
			}

			// Обновить запись в evn_usluga_par_grid
			var record = evn_usluga_par_grid.getStore().getById(data.evnUslugaData.EvnUslugaPar_id);

			if ( !record ) {
				if ( evn_usluga_par_grid.getStore().getCount() == 1 && !evn_usluga_par_grid.getStore().getAt(0).get('EvnUslugaPar_id') ) {
					evn_usluga_par_grid.getStore().removeAll();
				}

				evn_usluga_par_grid.getStore().loadData({ data: [ data.evnUslugaData ]}, true);
			}
			else {
				var evn_usluga_par_fields = new Array();

				evn_usluga_par_grid.getStore().fields.eachKey(function(key, item) {
					evn_usluga_par_fields.push(key);
				});

				for ( i = 0; i < evn_usluga_par_fields.length; i++ ) {
					record.set(evn_usluga_par_fields[i], data.evnUslugaData[evn_usluga_par_fields[i]]);
				}

				record.commit();
			}

			evn_usluga_par_grid.getStore().each(function(record) {
				if ( record.get('Person_id') == data.evnUslugaData.Person_id ) {
					record.set('Person_Birthday', data.evnUslugaData.Person_Birthday);
					record.set('Person_Surname', data.evnUslugaData.Person_Surname);
					record.set('Person_Firname', data.evnUslugaData.Person_Firname);
					record.set('Person_Secname', data.evnUslugaData.Person_Secname);
					record.set('Server_id', data.evnUslugaData.Server_id);

					record.commit();
				}
			});
		};

		params.EvnDirection_id = evn_direction_record.get('EvnDirection_id');
		params.EvnDirection_Num = evn_direction_record.get('EvnDirection_Num');
		params.EvnDirection_setDate = evn_direction_record.get('EvnDirection_setDate');
		params.EvnUslugaPar_Kolvo = 1;
		params.EvnUslugaPar_setDate = base_form.findField('EvnUslugaPar_setDate').getValue();
		params.LpuSection_uid = base_form.findField('LpuSection_id').getValue();
		params.LpuSection_did = base_form.findField('LpuSection_did').getValue();
		params.MedStaffFact_uid = base_form.findField('MedStaffFact_id').getValue();
		params.MedStaffFact_sid = base_form.findField('MedStaffFact_sid').getValue();

		params.UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue();
		params.Lpu_uid = base_form.findField('Lpu_uid').getValue();
		params.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
		params.MedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();

		params.Org_did = evn_direction_record.get('Lpu_did');
		params.PayType_id = base_form.findField('PayType_id').getValue();
		params.Person_Birthday = evn_direction_record.get('Person_Birthday');
		params.Person_Firname = evn_direction_record.get('Person_Firname');
		params.Person_id = evn_direction_record.get('Person_id');
		params.Person_Secname = evn_direction_record.get('Person_Secname');
		params.Person_Surname = evn_direction_record.get('Person_Surname');
		params.PersonEvn_id = evn_direction_record.get('PersonEvn_id');
		params.PrehospDirect_id = 2;
		params.Server_id = evn_direction_record.get('Server_id');
		//params.Usluga_id = base_form.findField('Usluga_id').getValue();
		params.UslugaCategory_id = base_form.findField('UslugaCategory_id').getValue();
		params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		params.FSIDI_id = base_form.findField('FSIDI_id').getValue();

		getWnd('swEvnUslugaParEditWindow').show(params);
	},
	plain: true,
	pmUser_Name: null,
	refreshEvnUslugaParGrid: function() {
		var grid = this.findById('EUPSIF_EvnUslugaParGrid').getGrid();

		grid.getSelectionModel().clearSelections();
		grid.getStore().reload();

		if ( grid.getStore().getCount() > 0 ) {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	resizable: false,
	setBegDateTime: function() {
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					this.begDate = response_obj.begDate;
					this.begTime = response_obj.begTime;

					this.findById('EUPSIF_StreamInformationForm').findById('EUPSIF_pmUser_Name').setValue(response_obj.pmUser_Name);
					this.findById('EUPSIF_StreamInformationForm').findById('EUPSIF_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
					this.findById('EUPSIF_EvnUslugaParGrid').getGrid().getStore().baseParams.begDate = response_obj.begDate;
					this.findById('EUPSIF_EvnUslugaParGrid').getGrid().getStore().baseParams.begTime = response_obj.begTime;
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	show: function() {
		sw.Promed.swEvnUslugaParStreamInputWindow.superclass.show.apply(this, arguments);

		this.begDate = null;
		this.begTime = null;
		this.pmUser_Name = null;

		if ( !this.findById('EUPSIF_EvnUslugaParGrid').getAction('action_add_with_direction') ) {
			this.findById('EUPSIF_EvnUslugaParGrid').addActions({
				handler: function() {
					this.openEvnUslugaParEditWindowAdv();
				}.createDelegate(this),
				iconCls: 'add16',
				name: 'action_add_with_direction',
				text: lang['dobavit_po_napravleniyu']
			});
		}

		var base_form = this.findById('EvnUslugaParStreamInputForm').getForm();
		base_form.reset();
        base_form.findField('LpuSectionProfile_id').onShowWindow(this);
        base_form.findField('MedSpecOms_id').onShowWindow(this);
		base_form.findField('UslugaPlace_id').lastQuery = '';
		base_form.findField('UslugaPlace_id').getStore().filterBy(function(rec) {
			return rec.get('UslugaPlace_Code').toString().inlist([ '1', '2' ]);
		});
		base_form.findField('UslugaPlace_id').fireEvent('change', base_form.findField('UslugaPlace_id'), base_form.findField('UslugaPlace_id').getValue());

		base_form.findField('Diag_id').setContainerVisible(getRegionNick() == 'ekb');

		// Заполнение полей "Пользователь" и "Дата начала ввода"
		this.setBegDateTime();

		setCurrentDateTime({
			dateField: base_form.findField('EvnUslugaPar_setDate'),
			loadMask: false,
			setDate: false,
			setDateMaxValue: true,
			windowId: this.id
		});

		setLpuSectionGlobalStoreFilter();
		setMedStaffFactGlobalStoreFilter();

		base_form.findField('LpuUnit_id').getStore().loadData(getStoreRecords(swLpuUnitGlobalStore));
		base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		base_form.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		this.findById('EUPSIF_EvnDirectionGrid').getGrid().getStore().removeAll();
		this.findById('EUPSIF_EvnDirectionGrid').addEmptyRecord(this.findById('EUPSIF_EvnDirectionGrid').getGrid().getStore());

		this.findById('EUPSIF_EvnUslugaParGrid').getGrid().getStore().removeAll();
		this.findById('EUPSIF_EvnUslugaParGrid').addEmptyRecord(this.findById('EUPSIF_EvnUslugaParGrid').getGrid().getStore());
		this.findById('EUPSIF_EvnUslugaParGrid').focus();

		var ucat_cmb = base_form.findField('UslugaCategory_id');
		var ucat_rec, index;

		if ( getRegionNick().inlist([ 'ekb' ]) ) {
			index = ucat_cmb.getStore().findBy(function(rec) {
				if(rec.get('UslugaCategory_SysNick') == 'tfoms')return true;
			});
			ucat_rec = ucat_cmb.getStore().getAt(index);
		} else if ( getRegionNick().inlist([ 'perm' ]) ) {
			index = ucat_cmb.getStore().findBy(function(rec) {
				if(rec.get('UslugaCategory_SysNick') == 'gost2011')return true;
			});
			ucat_rec = ucat_cmb.getStore().getAt(index);
		}
		else {
			ucat_rec = ucat_cmb.getStore().getById(ucat_cmb.getValue());
		}

		if ( ucat_rec ) {
			ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
		}
	},
	title: WND_PARKA_EUPSTIN,
	width: 800
});