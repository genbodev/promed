/**
* swEvnReceptProcessWindow - окно обработки рецептов в аптеках.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-28.12.2009
* @comment      Префикс для id компонентов ERPW (EvnReceptProcessWindow)
*/

sw.Promed.swEvnReceptProcessWindow = Ext.extend(sw.Promed.BaseForm, {
	// autoScroll: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	deleteDocumentUcStr: function() {
		var view_frame = this.findById('ERPW_EvnReceptDocumentUcStrGrid');

		if ( !view_frame || !view_frame.getGrid() || !view_frame.getGrid().getSelectionModel().getSelected() || !view_frame.getGrid().getSelectionModel().getSelected().get('DocumentUcStr_id') ) {
			return false;
		}

		view_frame.getGrid().getStore().remove(view_frame.getGrid().getSelectionModel().getSelected());

		if ( view_frame.getGrid().getStore().getCount() == 0 ) {
			view_frame.addEmptyRecord(view_frame.getGrid().getStore());
		}

		view_frame.focus();

		this.recountTotalSum();
	},
	doEvnReceptProcess: function(processingType) {
	/**
	 * Допустимые значения processingType:
	 *		release - отоваривание
	 *		reserve - резервирование
	 */
		if ( !processingType ) {
			return false;
		}

		switch ( processingType ) {
			case 'release':
				if ( this.buttons[0].hidden ) {
					return false;
				}
			break;

			case 'reserve':
				if ( this.buttons[0].hidden ) {
					return false;
				}
			break;

			default:
				return false;
			break;
		}

		var base_form = this.findById('EvnReceptProcessForm').getForm();
		var view_frame = this.findById('ERPW_EvnReceptDocumentUcStrGrid');

		var documentUcStrData = new Object();
		var params = new Object();

		var document_uc_str_oid = base_form.findField('DocumentUcStr_oid').getValue();
		var document_uc_str_rash_count = base_form.findField('DocumentUcStr_RashCount').getValue();
		var document_uc_str_sum = base_form.findField('DocumentUcStr_Sum').getValue();
		var document_uc_str_sum_nds_r = base_form.findField('DocumentUcStr_SumNdsR').getValue();
		var evn_recept_id = base_form.findField('EvnRecept_id').getValue();
		var evn_recept_obr_date = base_form.findField('EvnRecept_obrDate').getValue();
		var evn_recept_otp_date = base_form.findField('EvnRecept_otpDate').getValue();
		var recept_finance_code = this.findById('ERPW_EvnReceptInfo').getFieldValue('ReceptFinance_Code');

		if ( !evn_recept_id || evn_recept_id <= 0 ) {
			return false;
		}

		if ( !base_form.findField('EvnRecept_obrDate').isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('EvnRecept_obrDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( (this.processMode == 'single' && !document_uc_str_oid) || (this.processMode == 'multi' && (view_frame.getGrid().getStore().getCount() == 0 || (view_frame.getGrid().getStore().getCount() == 1 && !view_frame.getGrid().getStore().getAt(0).get('DocumentUcStr_id')))) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					switch ( this.processMode ) {
						case 'single':
							base_form.findField('DocumentUcStr_oid').focus(true);
						break;

						case 'multi':
							view_frame.focus();
						break;
					}
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['ne_vyibran_ni_odin_medikament'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( processingType == 'release' && !evn_recept_otp_date ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('EvnRecept_otpDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['ne_ukazana_data_otpuska'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		switch ( this.processMode ) {
			case 'single':
				if ( !document_uc_str_rash_count || document_uc_str_rash_count <= 0 ) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							base_form.findField('DocumentUcStr_RashCount').focus(true);
						},
						icon: Ext.Msg.WARNING,
						msg: lang['nevernoe_kolichestvo'],
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}

				if ( !document_uc_str_sum_nds_r || !base_form.findField('DocumentUcStr_SumNdsR').isValid() ) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							base_form.findField('DocumentUcStr_SumNdsR').focus(true);
						},
						icon: Ext.Msg.WARNING,
						msg: lang['nevernoe_znachenie_summyi'],
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}

				documentUcStrData = Ext.util.JSON.encode([{
					DocumentUcStr_oid: document_uc_str_oid,
					DocumentUcStr_RashCount: document_uc_str_rash_count,
					DocumentUcStr_Sum: document_uc_str_sum,
					DocumentUcStr_SumNdsR: document_uc_str_sum_nds_r
				}]);
			break;

			case 'multi':
				documentUcStrData = Ext.util.JSON.encode(getStoreRecords(view_frame.getGrid().getStore(), {
					exceptionFields: [ 'DocumentUcStr_id', 'Drug_id', 'DocumentUcStr_Name', 'DocumentUcStr_Price', 'DocumentUcStr_PriceR' ]
				}));
			break;

			default:
				sw.swMsg.alert(lang['oshibka'], lang['nepravilnyiy_rejim_sohraneniya'], function() {
					base_form.findField('EvnRecept_obrDate').focus(true);
				}.createDelegate(this) );
			break;
		}

		params.documentUcStrData = documentUcStrData;
		params.EvnRecept_id = evn_recept_id;
		params.EvnRecept_obrDate = Ext.util.Format.date(evn_recept_obr_date, 'd.m.Y');
		params.EvnRecept_otpDate = Ext.util.Format.date(evn_recept_otp_date, 'd.m.Y');
		params.ProcessingType_Name = processingType;
		params.ReceptFinance_Code = recept_finance_code;

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, выполняется сохранение..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg, function() {
							if ( processingType == 'release' ) {
								this.buttons[0].focus();
							}
							else {
								this.buttons[1].focus();
							}
						}.createDelegate(this) );
						return false;
					}

					var callback_data = new Object();

					if ( processingType == 'release' ) {
						callback_data.DelayType_Name = lang['obslujen'];
					}
					else {
						callback_data.DelayType_Name = lang['rezerv'];
					}

					callback_data.EvnRecept_id = evn_recept_id;
					callback_data.EvnRecept_Num = this.findById('ERPW_EvnReceptInfo').getFieldValue('EvnRecept_Num');
					callback_data.EvnRecept_Ser = this.findById('ERPW_EvnReceptInfo').getFieldValue('EvnRecept_Ser');
					callback_data.EvnRecept_SumDiscount = base_form.findField('EvnRecept_Summa').getValue();

					switch ( this.processMode ) {
						case 'single':
							callback_data.EvnRecept_Sum = base_form.findField('DocumentUcStr_SumNdsR').getValue();
						break;

						case 'multi':
							var sum = 0;

							view_frame.getGrid().getStore().each(function(rec) {
								sum = sum + rec.get('DocumentUcStr_SumNdsR');
							});

							callback_data.EvnRecept_Sum = sum;
						break;

						default:
							sw.swMsg.alert(lang['oshibka'], lang['nepravilnyiy_rejim_sohraneniya'], function() {
								base_form.findField('EvnRecept_obrDate').focus(true);
							}.createDelegate(this) );
						break;
					}

					this.callback(callback_data);

					sw.swMsg.alert(lang['soobschenie'], lang['retsept_byil_uspeshno_obrabotan'], function() { this.doReset(); }.createDelegate(this) );
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_obrabotke_retsepta']);
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=Farmacy&m=evnReceptProcess'
		});
	},
	doReset: function() {
		var base_form = this.findById('EvnReceptProcessForm').getForm();
		var evn_recept_obr_date = base_form.findField('EvnRecept_obrDate').getValue();
		// ivp: запоминаю серию рецепта
		var evn_recept_ser = base_form.findField('EvnRecept_Ser').getValue();

		this.buttons[1].hide();
		this.buttons[2].hide();

		base_form.reset();

		base_form.findField('EvnRecept_obrDate').setValue(evn_recept_obr_date);

		base_form.findField('EvnRecept_otpDate').disable();
		base_form.findField('EvnRecept_otpDate').fireEvent('change', base_form.findField('EvnRecept_otpDate'), null);

		this.findById('ERPW_EvnReceptInfo').getStore().removeAll();

		this.findById('ERPW_DocumentUcStrSinglePanel').expand();

		// ivp: восстанавливаю серию рецепта
		base_form.findField('EvnRecept_Ser').setRawValue(evn_recept_ser);
		base_form.findField('EvnRecept_Ser').focus(true, 250);
	},
	doSearch: function() {
		var base_form = this.findById('EvnReceptProcessForm').getForm();

		var evn_recept_num = base_form.findField('EvnRecept_Num').getValue();
		var evn_recept_ser = base_form.findField('EvnRecept_Ser').getValue();

		if ( !evn_recept_ser ) {
			sw.swMsg.alert(lang['oshibka'], lang['pole_seriya_obyazatelno_dlya_zapolneniya'], function() { base_form.findField('EvnRecept_Ser').focus(true); });
			return false;
		}
		else if ( !evn_recept_num ) {
			sw.swMsg.alert(lang['oshibka'], lang['pole_nomer_obyazatelno_dlya_zapolneniya'], function() { base_form.findField('EvnRecept_Num').focus(true); });
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет поиск..." });
		loadMask.show();

		base_form.findField('EvnRecept_id').setValue(0);

		base_form.findField('EvnRecept_otpDate').setRawValue('');
		base_form.findField('EvnRecept_otpDate').disable();
		base_form.findField('EvnRecept_otpDate').fireEvent('change', base_form.findField('EvnRecept_otpDate'), null);

		this.findById('ERPW_EvnReceptInfo').getStore().removeAll();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj && response_obj.EvnRecept_id ) {
						this.getEvnReceptInfo(response_obj.EvnRecept_id);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_poiske_retsepta_proizoshli_oshibki']);
				}
			}.createDelegate(this),
			params: {
				EvnRecept_Num: evn_recept_num,
				EvnRecept_Ser: evn_recept_ser
			},
			url: '/?c=Farmacy&m=searchEvnRecept'
		});
	},
	draggable: true,
	getEvnReceptInfo: function(evn_recept_id) {
		var base_form = this.findById('EvnReceptProcessForm').getForm();

		if ( !evn_recept_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyiy_identifikator_retsepta'], function() { base_form.findField('EvnRecept_Ser').focus(true); });
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение информации о рецепте..." });
		loadMask.show();

		base_form.findField('EvnRecept_otpDate').setMaxValue(undefined);
		base_form.findField('EvnRecept_otpDate').setMinValue(undefined);

		this.findById('ERPW_EvnReceptInfo').getStore().load({
			callback: function() {
				loadMask.hide();

				var evn_recept_exp_date = this.findById('ERPW_EvnReceptInfo').getFieldValue('EvnRecept_expDate');
				var evn_recept_set_date = this.findById('ERPW_EvnReceptInfo').getFieldValue('EvnRecept_setDate');

				base_form.findField('EvnRecept_otpDate').setMaxValue(evn_recept_exp_date);
				base_form.findField('EvnRecept_otpDate').setMinValue(evn_recept_set_date);

				if ( !Ext.isEmpty(this.findById('ERPW_EvnReceptInfo').getFieldValue('ReceptDelayType_id')) ) {
					switch ( this.findById('ERPW_EvnReceptInfo').getFieldValue('ReceptDelayType_id').toString() ) {
						case '1':
						case '3':
						case '4':
							this.buttons[1].hide();
							this.buttons[2].hide();
						break;

						case '2':
							this.buttons[1].hide();
							this.buttons[2].show();
						break;

						default:
							this.buttons[1].show();
							this.buttons[2].hide();
						break;
					}
				}
				else {
					this.buttons[1].show();
					this.buttons[2].hide();
				}

				if ( this.findById('ERPW_EvnReceptInfo').getFieldValue('ReceptDelayType_id') == 0 || this.findById('ERPW_EvnReceptInfo').getFieldValue('ReceptDelayType_id') == 2 ) {
					// К обработке допускаются выписаные и отложенные рецепты

					if ( getGlobalOptions().FarmacyOtdel_id == 19 && this.findById('ERPW_EvnReceptInfo').getFieldValue('ReceptFinance_Code').toString() == '1' ) {
						// Проверка медикамента на попадание в справочники v_DrugFed и v_Drug7Noz, если отдел "Дорогостой",
						// а тип финансирования рецепта "Федеральный бюджет"

						loadMask = new Ext.LoadMask(this.getEl(), { msg: "Проверка возможности отоваривания рецепта..." });
						loadMask.show();

						Ext.Ajax.request({
							callback: function(options, success, response) {
								loadMask.hide();

								if ( success ) {
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if ( response_obj)  {
										if ( response_obj.EvnRecept_id == evn_recept_id && response_obj.Error_Msg.toString().length == 0 ) {
											base_form.findField('EvnRecept_id').setValue(evn_recept_id);

											base_form.findField('EvnRecept_otpDate').enable();
											base_form.findField('EvnRecept_otpDate').focus(true);
										}
										else if ( response_obj.Error_Msg.toString().length > 0 ) {
											sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg, function() { base_form.findField('EvnRecept_Ser').focus(true); });
										}
										else {
											base_form.findField('EvnRecept_Ser').focus(true);
										}
									}
									else {
										sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_proverke_vozmojnosti_otovarit_retsept'], function() { base_form.findField('EvnRecept_Ser').focus(true); });
									}
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_proverke_vozmojnosti_otovarivaniya_retsepta_proizoshli_oshibki']);
								}
							}.createDelegate(this),
							params: {
								EvnRecept_id: evn_recept_id
							},
							url: '/?c=Farmacy&m=checkEvnReceptProcessAbilty'
						});
					}
					else {
						var drug_finance_flag = false;

						switch ( this.findById('ERPW_EvnReceptInfo').getFieldValue('ReceptFinance_Code').toString() ) {
							case '1':
								if ( getGlobalOptions().FarmacyOtdel_id == 17 || getGlobalOptions().FarmacyOtdel_id == 18 ) {
									drug_finance_flag = true;
								}
							break;

							case '2':
								if ( getGlobalOptions().FarmacyOtdel_id == 20 || getGlobalOptions().FarmacyOtdel_id == 21 ) {
									drug_finance_flag = true;
								}
							break;

							case '3':
								if ( getGlobalOptions().FarmacyOtdel_id == 19 ) {
									drug_finance_flag = true;
								}
							break;
						}

						if ( drug_finance_flag == true ) {
							base_form.findField('EvnRecept_id').setValue(evn_recept_id);

							base_form.findField('EvnRecept_otpDate').enable();
							base_form.findField('EvnRecept_otpDate').focus(true);
						}
						else {
							base_form.findField('EvnRecept_Ser').focus(true);
						}
					}
				}
				else {
					base_form.findField('EvnRecept_Ser').focus(true);
				}
			}.createDelegate(this),
			params: {
				EvnRecept_id: evn_recept_id
			}
		});
	},
	height: 550,
	id: 'EvnReceptProcessWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doEvnReceptProcess('release');
				}.createDelegate(this),
				iconCls: 'ok16',
				onShiftTabAction: function() {
					if ( !this.findById('ERPW_DocumentUcStrMultiPanel').collapsed ) {
						this.findById('ERPW_EvnReceptDocumentUcStrGrid').focus();
					}
					else if ( !this.findById('EvnReceptProcessForm').getForm().findField('DocumentUcStr_SumNdsR').disabled ) {
						this.findById('EvnReceptProcessForm').getForm().findField('DocumentUcStr_SumNdsR').focus(true);
					}
					else if ( !this.findById('EvnReceptProcessForm').getForm().findField('DocumentUcStr_RashCount').disabled ) {
						this.findById('EvnReceptProcessForm').getForm().findField('DocumentUcStr_RashCount').focus(true);
					}
					else if ( !this.findById('EvnReceptProcessForm').getForm().findField('DocumentUcStr_oid').disabled ) {
						this.findById('EvnReceptProcessForm').getForm().findField('DocumentUcStr_oid').focus(true);
					}
					else if ( !this.findById('EvnReceptProcessForm').getForm().findField('EvnRecept_otpDate').disabled ) {
						this.findById('EvnReceptProcessForm').getForm().findField('EvnRecept_otpDate').focus(true);
					}
					else {
						this.findById('EvnReceptProcessForm').getForm().findField('EvnRecept_Num').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function() {
					if ( !this.buttons[1].hidden ) {
						this.buttons[1].focus();
					}
					else if ( !this.buttons[2].hidden ) {
						this.buttons[2].focus();
					}
					else {
						this.buttons[3].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_ERPW + 17,
				text: lang['otpustit'],
				tooltip: lang['otovarit_retsept']
			}, {
				handler: function() {
					this.putEvnReceptOnDelay();
				}.createDelegate(this),
				iconCls: 'receipt-ondelay16',
				tabIndex: TABINDEX_ERPW + 18,
				text: lang['postavit_na_otsrochku'],
				tooltip: lang['postavit_na_otsrochku']
			}, {
				handler: function() {
					this.doEvnReceptProcess('reserve');
				}.createDelegate(this),
				iconCls: 'receipt-reserve16',
				tabIndex: TABINDEX_ERPW + 19,
				text: lang['zarezervirovat'],
				tooltip: lang['rezervirovanie_medikamenta']
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'reset16',
				tabIndex: TABINDEX_ERPW + 20,
				text: BTN_FRMRESET,
				tooltip: lang['sbros']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[3].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.findById('EvnReceptProcessForm').getForm().findField('EvnRecept_obrDate').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_ERPW + 21,
				text: BTN_FRMCANCEL,
				tooltip: lang['zakryit_okno']
			}],
			items: [ new Ext.form.FormPanel({
				autoScroll: true,
				bodyStyle: 'padding: 0.5em;',
				border: false,
				frame: false,
				id: 'EvnReceptProcessForm',
				labelAlign: 'right',
				region: 'center',
				url: '/?c=Farmacy&m=evnReceptProcess',
				items: [{
					name: 'EvnRecept_id',
					value: 0,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					enableKeyEvents: true,
					fieldLabel: lang['data_obrascheniya'],
					format: 'd.m.Y',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					name: 'EvnRecept_obrDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: TABINDEX_ERPW + 1,
					width: 100,
					xtype: 'swdatefield'
				},
				new Ext.Panel({
					bodyStyle: 'padding: 0.5em;',
					style: 'margin-bottom: 0.5em;',
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							labelWidth: 70,
							layout: 'form',
							width: 220,
							items: [{
								enableKeyEvents: true,
								fieldLabel: lang['seriya'],
								listeners: {
									'keydown': function(inp, e) {
										if ( e.getKey() == Ext.EventObject.ENTER ) {
											e.stopEvent();
											this.doSearch();
										}
									}.createDelegate(this)
								},
								name: 'EvnRecept_Ser',
								tabIndex: TABINDEX_ERPW + 2,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							labelWidth: 70,
							layout: 'form',
							width: 220,
							items: [{
								allowDecimals: false,
								allowNegative: false,
								enableKeyEvents: true,
								fieldLabel: lang['nomer'],
								listeners: {
									'keydown': function(inp, e) {
										if ( e.getKey() == Ext.EventObject.ENTER ) {
											e.stopEvent();
											this.doSearch();
										}
									}.createDelegate(this)
								},
								maskRe: /\d/,
								name: 'EvnRecept_Num',
								tabIndex: TABINDEX_ERPW + 3,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							width: 100,
							items: [{
								handler: function() {
									this.doSearch();
								}.createDelegate(this),
								minWidth: 80,
								tabIndex: TABINDEX_ERPW + 4,
								text: BTN_FRMSEARCH,
								iconCls: 'search16',
								xtype: 'button'
							}]
						}, {
							border: false,
							items: [{
								handler: function() {
									this.findById('EvnReceptProcessForm').getForm().findField('EvnRecept_Num').setRawValue('');
									this.findById('EvnReceptProcessForm').getForm().findField('EvnRecept_Ser').setRawValue('');
									this.findById('EvnReceptProcessForm').getForm().findField('EvnRecept_Ser').focus(true);
								}.createDelegate(this),
								tabIndex: TABINDEX_ERPW + 5,
								minWidth: 80,
								text: lang['sbros'],
								iconCls: 'resetsearch16',
								xtype: 'button'
							}]
						}]
					}],
					title: lang['poisk_retsepta']
				}),
				new Ext.Panel({
					autoScroll: true,
					bodyStyle: 'padding: 0.5em;',
					border: true,
					height: 280,
					id: 'ERPW_EvnReceptInfoPanel',
					layout: 'fit',
					style: 'margin-bottom: 0.5em;',
					title: lang['informatsiya_o_retsepte'],
					items: [ new Ext.DataView({
						autoHeight: true,
						border: false,
						emptyText: lang['net_informatsii_o_retsepte'],
						frame: false,
						getFieldValue: function(field) {
							if ( this.getStore().getCount() == 1 ) {
								return this.getStore().getAt(0).get(field);
							}
							else {
								return null;
							}
						},
						id: 'ERPW_EvnReceptInfo',
						itemSelector: 'div',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'Drug_Code' },
								{ name: 'Drug_id' },
								{ name: 'Drug_rlsid' },
								{ name: 'Drug_IsKEK_Name' },
								{ name: 'Drug_IsMnn_Code' },
								{ name: 'Drug_Name' },
								{ name: 'DrugComplexMnn_id' },
								{ name: 'DrugMnn_id' },
								{ name: 'DrugMnn_Name' },
								{ name: 'DrugMnn_NameLat' },
								{ name: 'DrugTorg_id' },
								{ name: 'DrugTorg_Name' },
								{ name: 'DrugTorg_NameLat' },
								{ name: 'EvnRecept_expDate', dateFormat: 'd.m.Y', type: 'date' },
								{ name: 'EvnRecept_Kolvo' },
								{ name: 'EvnRecept_Num' },
								{ name: 'EvnRecept_Ser' },
								{ name: 'EvnRecept_setDate', dateFormat: 'd.m.Y', type: 'date' },
								{ name: 'Lpu_Nick' },
								{ name: 'MedPersonal_Fio' },
								{ name: 'OrgFarmacy_Name' },
								{ name: 'OrgFarmacy_oid' },
								{ name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date' },
								{ name: 'Person_Firname' },
								{ name: 'Person_Secname' },
								{ name: 'Person_Snils' },
								{ name: 'Person_Surname' },
								{ name: 'PrivilegeType_Code' },
								{ name: 'PrivilegeType_Name' },
								{ name: 'ReceptDelayType_id' },
								{ name: 'ReceptDelayType_Name' },
								{ name: 'ReceptDiscount_Code' },
								{ name: 'ReceptFinance_Code' },
								{ name: 'ReceptFinance_Name' },
								{ name: 'Sex_Name' }
							],
							url: '/?c=Farmacy&m=loadEvnReceptData'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for=".">',
							'<div style="font-weight: bold;">Пациент:</div>',
							'<div>ФИО: <font style="color: blue; font-weight: bold;">{Person_Surname} {Person_Firname} {Person_Secname}</font> Д/р: <font style="color: blue;">{[Ext.util.Format.date(values.Person_Birthday, "d.m.Y")]}</font> Пол: <font style="color: blue;">{Sex_Name}</font> СНИЛС: <font style="color: blue;">{Person_Snils}</font></div>',
							'<div style="font-weight: bold;">Рецепт:</div>',
							'<div>Серия: <font style="color: blue;">{EvnRecept_Ser}</font> Номер: <font style="color: blue;">{EvnRecept_Num}</font> Дата выписки: <font style="color: blue;">{[Ext.util.Format.date(values.EvnRecept_setDate, "d.m.Y")]}</font></div>',
							'<div>ЛПУ: <font style="color: blue;">{Lpu_Nick}</font> ФИО врача: <font style="color: blue;">{MedPersonal_Fio}</font></div>',
							'<div style="font-weight: bold;">Медикамент:</div>',
							'<div>МНН: <font style="color: blue;">{DrugMnn_Name}</font> МНН (лат.): <font style="color: blue;">{DrugMnn_NameLat}</font></div>',
							'<div>Торговое: <font style="color: blue;">{DrugTorg_Name}</font> Торговое (лат.): <font style="color: blue;">{DrugTorg_NameLat}</font></div>',
							'<div>Справочник: <font style="color: blue;">{Drug_Name}</font></div>',
							'<div>Код ГЕС: <font style="color: blue; font-weight: bold;">{Drug_Code}</font></div>',
							'<div>Количество: <font style="color: blue;">{EvnRecept_Kolvo}</font></div>',
							'<div style="font-weight: bold;">Выписка через ВК: <span style="color: blue;">{Drug_IsKEK_Name}</span></div>',
							'<div style="font-weight: bold;">Тип финансирования:</div>',
							'<div style="font-weight: bold; color: blue; font-size: 12pt;">{ReceptFinance_Name}</div>',
							'<div style="font-weight: bold;">Льгота:</div>',
							'<div>Код категории: <font style="color: blue;">{PrivilegeType_Code}</font> Наименование: <font style="color: blue;">{PrivilegeType_Name}</font></div>',
							'<div style="font-weight: bold;">Статус рецепта: <span style=\'color: blue;\'>{ReceptDelayType_Name}</span></div>',
							'<div style="font-weight: bold;">Аптека: <span style=\'color: blue;\'>{OrgFarmacy_Name}</div>',
							'</tpl>'
						)
					})]
				}), {
					enableKeyEvents: true,
					fieldLabel: lang['data_otpuska'],
					format: 'd.m.Y',
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = this.findById('EvnReceptProcessForm').getForm();

							base_form.findField('DocumentUcStr_SumNdsR').maxValue = undefined;
							base_form.findField('DocumentUcStr_SumNdsR').minValue = undefined;

							this.findById('ERPW_EvnReceptDocumentUcStrGrid').getAction('action_add').setDisabled(true);
							this.findById('ERPW_EvnReceptDocumentUcStrGrid').getAction('action_print').setDisabled(true);

							base_form.findField('DocumentUcStr_oid').clearValue();
							base_form.findField('DocumentUcStr_oid').getStore().removeAll();
							base_form.findField('DocumentUcStr_oid').fireEvent('change', base_form.findField('DocumentUcStr_oid'), null, 1);

							this.findById('ERPW_EvnReceptDocumentUcStrGrid').removeAll();

							if ( !newValue ) {
								base_form.findField('DocumentUcStr_RashCount').setValue('');
								base_form.findField('DocumentUcStr_SumNdsR').setValue('');

								base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), '', 1);

								base_form.findField('DocumentUcStr_oid').disable();
								base_form.findField('DocumentUcStr_RashCount').disable();
								base_form.findField('DocumentUcStr_SumNdsR').disable();

								if ( !field.disabled ) {
									field.focus();
								}

								return false;
							}

							this.findById('ERPW_EvnReceptDocumentUcStrGrid').getAction('action_add').setDisabled(false);
							this.findById('ERPW_EvnReceptDocumentUcStrGrid').getAction('action_print').setDisabled(false);

							base_form.findField('DocumentUcStr_oid').enable();
							base_form.findField('DocumentUcStr_RashCount').enable();
							base_form.findField('DocumentUcStr_SumNdsR').enable();

							base_form.findField('DocumentUcStr_RashCount').setValue(this.findById('ERPW_EvnReceptInfo').getFieldValue('EvnRecept_Kolvo'));
							base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), this.findById('ERPW_EvnReceptInfo').getFieldValue('EvnRecept_Kolvo'), 0);

							base_form.findField('DocumentUcStr_oid').getStore().load({
								callback: function() {
									if ( !base_form.findField('DocumentUcStr_oid').disabled ) {
										base_form.findField('DocumentUcStr_oid').focus();
									}
								}.createDelegate(this),
								params: {
									'Drug_id': this.findById('ERPW_EvnReceptInfo').getFieldValue('Drug_id'),
									'DrugMnn_id': this.findById('ERPW_EvnReceptInfo').getFieldValue('DrugMnn_id'),
									'DrugTorg_id': this.findById('ERPW_EvnReceptInfo').getFieldValue('DrugTorg_id'),
									'EvnRecept_otpDate': Ext.util.Format.date(newValue, 'd.m.Y'),
									'mode': 'recept',
									'ReceptFinance_Code': this.findById('ERPW_EvnReceptInfo').getFieldValue('ReceptFinance_Code')
								}
							});
						}.createDelegate(this)
					},
					name: 'EvnRecept_otpDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: TABINDEX_ERPW + 6,
					width: 100,
					xtype: 'swdatefield'
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding: 0.5em;',
					border: true,
					id: 'ERPW_DocumentUcStrSinglePanel',
					labelWidth: 150,
					layout: 'form',
					listeners: {
						'collapse': function(panel) {
							this.findById('ERPW_DocumentUcStrMultiPanel').expand();
						}.createDelegate(this),
						'expand': function(panel) {
							this.processMode = 'single';
							this.findById('ERPW_DocumentUcStrMultiPanel').collapse();

							if ( !this.findById('EvnReceptProcessForm').getForm().findField('DocumentUcStr_oid').disabled ) {
								this.findById('EvnReceptProcessForm').getForm().findField('DocumentUcStr_oid').focus(true);
							}
							else {
								this.findById('EvnReceptProcessForm').getForm().findField('EvnRecept_Ser').focus(true);
							}

							this.recountTotalSum();

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['1_medikament_odnaya_seriya'],
					items: [{
						displayField: 'DocumentUcStr_Name',
						fieldLabel: lang['medikament'],
						forceSelection: true,
						hiddenName: 'DocumentUcStr_oid',
						listWidth: 600,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnReceptProcessForm').getForm();

								base_form.findField('DocumentUcStr_Count').setValue('');
								// base_form.findField('DocumentUcStr_EdCount').setValue('');
								base_form.findField('DocumentUcStr_Price').setValue('');
								base_form.findField('DocumentUcStr_PriceR').setValue('');

								base_form.findField('DocumentUcStr_RashCount').maxValue = undefined;

								base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), '', 1);

								var record = combo.getStore().getById(newValue);

								if ( record ) {
									base_form.findField('DocumentUcStr_Count').setValue(record.get('DocumentUcStr_Count'));
									// base_form.findField('DocumentUcStr_EdCount').setValue(record.get('DocumentUcStr_EdCount'));
									base_form.findField('DocumentUcStr_Price').setValue(record.get('DocumentUcStr_Price'));
									base_form.findField('DocumentUcStr_PriceR').setValue(record.get('DocumentUcStr_PriceR'));

									base_form.findField('DocumentUcStr_RashCount').maxValue = record.get('DocumentUcStr_Count');
									base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), base_form.findField('DocumentUcStr_RashCount').getValue(), 0);
								}

								return true;
							}.createDelegate(this)
						},
						resizable: true,
						selectOnFocus: true,
						store: new Ext.data.Store({
							autoLoad: false,
							reader: new Ext.data.JsonReader({
								id: 'DocumentUcStr_id'
							}, [
								{ name: 'DocumentUcStr_id', mapping: 'DocumentUcStr_id' },
								{ name: 'DocumentUcStr_Name', mapping: 'DocumentUcStr_Name' },
								{ name: 'DocumentUcStr_Count', mapping: 'DocumentUcStr_Count' },
								{ name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
								{ name: 'DocumentUcStr_Price', mapping: 'DocumentUcStr_Price' },
								{ name: 'DocumentUcStr_PriceR', mapping: 'DocumentUcStr_PriceR' },
								{ name: 'DocumentUcStr_Ser', mapping: 'DocumentUcStr_Ser' }
							]),
							url: '/?c=Farmacy&m=loadDocumentUcStrList'
						}),
						tabIndex: TABINDEX_ERPW + 7,
						tpl: new Ext.XTemplate(
							'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: center;">',
							'<td style="padding: 2px; width: 25%;">Медикамент</td>',
							'<td style="padding: 2px; width: 15%;">Цена (опт.)</td>',
							'<td style="padding: 2px; width: 15%;">Цена (розн.)</td>',
							'<td style="padding: 2px; width: 15%;">Остаток</td>',
							'<td style="padding: 2px; width: 15%;">Ист. финанс.</td>',
							'<td style="padding: 2px; width: 15%;">Серия</td>',
							'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
							'<td style="padding: 2px;">{DocumentUcStr_Name}&nbsp;</td>',
							'<td style="padding: 2px; text-align: right;">{DocumentUcStr_Price}&nbsp;</td>',
							'<td style="padding: 2px; text-align: right;">{DocumentUcStr_PriceR}&nbsp;</td>',
							'<td style="padding: 2px; text-align: right;">{DocumentUcStr_Count}&nbsp;</td>',
							'<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
							'<td style="padding: 2px;">{DocumentUcStr_Ser}&nbsp;</td>',
							'</tr></tpl>',
							'</table>'
						),
						valueField: 'DocumentUcStr_id',
						width: 480,
						xtype: 'swbaselocalcombo'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								decimalPrecision: 3,
								disabled: true,
								fieldLabel: lang['ostatok_ed_uch'],
								name: 'DocumentUcStr_Count',
								tabIndex: TABINDEX_ERPW + 8,
								width: 100,
								xtype: 'numberfield'
							}]
						}/*, {
							border: false,
							labelWidth: 150,
							layout: 'form',
							items: [{
								decimalPrecision: 3,
								disabled: true,
								fieldLabel: lang['ostatok_ed_doz'],
								name: 'DocumentUcStr_EdCount',
								tabIndex: TABINDEX_ERPW + 9,
								width: 100,
								xtype: 'numberfield'
							}]
						}*/]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								decimalPrecision: 2,
								disabled: true,
								fieldLabel: lang['tsena_opt_bez_nds'],
								name: 'DocumentUcStr_Price',
								tabIndex: TABINDEX_ERPW + 10,
								width: 100,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							labelWidth: 150,
							layout: 'form',
							items: [{
								decimalPrecision: 2,
								disabled: true,
								fieldLabel: lang['tsena_rozn_s_nds'],
								name: 'DocumentUcStr_PriceR',
								tabIndex: TABINDEX_ERPW + 11,
								width: 100,
								xtype: 'textfield'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowDecimals: true,
								allowNegative: false,
								decimalPrecision: 2,
								disabled: false,
								fieldLabel: lang['kolichestvo_ed_uch'],
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = this.findById('EvnReceptProcessForm').getForm();

										var price = base_form.findField('DocumentUcStr_Price').getValue();
										var price_r = base_form.findField('DocumentUcStr_PriceR').getValue();

										if ( price.toString().length > 0 && price_r.toString().length > 0 && newValue.toString().length > 0 ) {
											base_form.findField('DocumentUcStr_SumNdsR').maxValue = Number(price_r * newValue + 0.1).toFixed(2);
											base_form.findField('DocumentUcStr_SumNdsR').minValue = (price_r * newValue - 0.1 >= 0 ? Number(price_r * newValue - 0.1).toFixed(2) : 0);

											base_form.findField('DocumentUcStr_Sum').setValue(Number(price * newValue).toFixed(2));
											base_form.findField('DocumentUcStr_SumNdsR').setValue(Number(price_r * newValue).toFixed(2));
										}
										else {
											base_form.findField('DocumentUcStr_SumNdsR').maxValue = undefined;
											base_form.findField('DocumentUcStr_SumNdsR').minValue = undefined;

											base_form.findField('DocumentUcStr_Sum').setValue('');
											base_form.findField('DocumentUcStr_SumNdsR').setValue('');
										}

										base_form.findField('DocumentUcStr_SumNdsR').fireEvent('change', base_form.findField('DocumentUcStr_SumNdsR'), base_form.findField('DocumentUcStr_SumNdsR').getValue());
									}.createDelegate(this)
								},
								minValue: 0.01,
								name: 'DocumentUcStr_RashCount',
								tabIndex: TABINDEX_ERPW + 12,
								width: 100,
								xtype: 'numberfield'
							}]
						}/*, {
							border: false,
							labelWidth: 150,
							layout: 'form',
							items: [{
								allowDecimals: false,
								allowNegative: false,
								decimalPrecision: 2,
								disabled: true,
								fieldLabel: lang['kolichestvo_ed_doz'],
								name: 'DocumentUcStr_RashEdCount',
								tabIndex: TABINDEX_ERPW + 13,
								width: 100,
								xtype: 'numberfield'
							}]
						}*/]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowDecimals: false,
								allowNegative: false,
								decimalPrecision: 2,
								disabled: true,
								fieldLabel: lang['summa_opt_bez_nds'],
								name: 'DocumentUcStr_Sum',
								tabIndex: TABINDEX_ERPW + 14,
								width: 100,
								xtype: 'numberfield'
							}]
						}, {
							border: false,
							labelWidth: 150,
							layout: 'form',
							items: [{
								allowDecimals: false,
								allowNegative: false,
								decimalPrecision: 2,
								disabled: true,
								enableKeyEvents: true,
								fieldLabel: lang['summa_rozn_s_nds'],
								listeners: {
									'change': function(field, newValue, oldValue) {
										this.recountTotalSum();
									}.createDelegate(this)
								},
								name: 'DocumentUcStr_SumNdsR',
								tabIndex: TABINDEX_ERPW + 15,
								width: 100,
								xtype: 'numberfield'
							}]
						}]
					}]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding: 0px;',
					border: true,
					id: 'ERPW_DocumentUcStrMultiPanel',
					labelWidth: 150,
					layout: 'form',
					listeners: {
						'collapse': function(panel) {
							this.findById('ERPW_DocumentUcStrSinglePanel').expand();
						}.createDelegate(this),
						'expand': function(panel) {
							this.processMode = 'multi';
							this.findById('ERPW_DocumentUcStrSinglePanel').collapse();
							this.findById('ERPW_EvnReceptDocumentUcStrGrid').focus();

							this.recountTotalSum();

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['2_medikament_neskolko_seriy'],
					items: [ new sw.Promed.ViewFrame({
						actions: [
							{ name: 'action_add', handler: function() { this.openDocumentUcStrRashEditWindow('add'); }.createDelegate(this) },
							{ name: 'action_edit', handler: function() { this.openDocumentUcStrRashEditWindow('edit'); }.createDelegate(this) },
							{ name: 'action_view', handler: function() { this.openDocumentUcStrRashEditWindow('view'); }.createDelegate(this) },
							{ name: 'action_delete', handler: function() { this.deleteDocumentUcStr(); }.createDelegate(this) },
							{ name: 'action_refresh', disabled: true },
							{ name: 'action_print'}
						],
						autoLoadData: false,
						border: false,
						dataUrl: '/?c=Farmacy&m=loadEvnReceptDocumentUcStrGrid',
						id: 'ERPW_EvnReceptDocumentUcStrGrid',
						object: 'DocumentUcStr',
						onLoadData: function(result) {
							//
						},
						onRowSelect: function(sm, index, record) {
							//
						},
						paging: false,
						region: 'center',
						// root: 'data',
						stringfields: [
							// Поля для отображение в гриде
							{ name: 'DocumentUcStr_id', type: 'int', header: 'ID', key: true },
							{ name: 'DocumentUcStr_oid', type: 'int', hidden: true },
							{ name: 'Drug_id', type: 'int', hidden: true },
							{ name: 'DocumentUcStr_Name', type: 'string', header: lang['medikament'], id: 'autoexpand', autoExpandMin: 250 },
							{ name: 'DocumentUcStr_RashCount', type: 'float', header: lang['kol-vo'], width: 100 },
							{ name: 'DocumentUcStr_Price', type: 'float', header: lang['tsena'], width: 100 },
							{ name: 'DocumentUcStr_PriceR', type: 'float', header: lang['tsena_rozn_s_nds'], width: 100 },
							{ name: 'DocumentUcStr_Sum', type: 'float', header: lang['summa'], width: 100 },
							{ name: 'DocumentUcStr_SumNdsR', type: 'float', header: lang['summa_rozn_s_nds'], width: 100 }
						],
						toolbar: true // ,
						// totalProperty: 'totalCount'
					})]
				}), {
					border: false,
					labelWidth: 200,
					layout: 'form',
					items: [{
						allowDecimals: true,
						allowNegative: false,
						decimalPrecision: 2,
						disabled: true,
						fieldLabel: lang['summa_k_oplate_rozn_s_nds'],
						name: 'EvnRecept_Summa',
						tabIndex: TABINDEX_ERPW + 16,
						width: 100,
						xtype: 'numberfield'
					}]
				}]
			})]
		});
		sw.Promed.swEvnReceptProcessWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnReceptProcessWindow');

			e.stopEvent();

			if ( e.browserEvent.stopPropagation ) {
				e.browserEvent.stopPropagation();
			}
			else {
				e.browserEvent.cancelBubble = true;
			}

			if ( e.browserEvent.preventDefault ) {
				e.browserEvent.preventDefault();
			}
			else {
				e.browserEvent.returnValue = false;
			}

			e.returnValue = false;

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			switch (e.getKey()) {
				case Ext.EventObject.C:
					current_window.doEvnReceptProcess('release');
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.H:
					current_window.putEvnReceptOnDelay();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('ERPW_DocumentUcStrSinglePanel').expand();
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					current_window.findById('ERPW_DocumentUcStrMultiPanel').expand();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J,
			Ext.EventObject.H,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.ONE,
			Ext.EventObject.TWO
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		},
		'maximize': function(win) {
			win.findById('ERPW_DocumentUcStrMultiPanel').doLayout();
			win.findById('ERPW_DocumentUcStrSinglePanel').doLayout();
			win.findById('ERPW_EvnReceptInfoPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('ERPW_DocumentUcStrMultiPanel').doLayout();
			win.findById('ERPW_DocumentUcStrSinglePanel').doLayout();
			win.findById('ERPW_EvnReceptInfoPanel').doLayout();
		}
	},
	maximizable: true,
	maximized: true,
	minHeight: 550,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	openDocumentUcStrRashEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnReceptProcessForm').getForm();
		var view_frame = this.findById('ERPW_EvnReceptDocumentUcStrGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swDocumentUcStrRashEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_rashodnoy_pozitsii_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var record;

		if ( action == 'add' ) {
			// Генерируем DocumentUcStr_id
			params.DocumentUcStr_id = - swGenTempId(view_frame.getGrid().getStore());
		}
		else {
			var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('DocumentUcStr_id') ) {
				return false;
			}

			params = selected_record.data;
		}

		params.Drug_id = this.findById('ERPW_EvnReceptInfo').getFieldValue('Drug_id');
		params.DrugMnn_id = this.findById('ERPW_EvnReceptInfo').getFieldValue('DrugMnn_id');
		params.DrugTorg_id = this.findById('ERPW_EvnReceptInfo').getFieldValue('DrugTorg_id');
		// params.EvnRecept_Kolvo = this.findById('ERPW_EvnReceptInfo').getFieldValue('EvnRecept_Kolvo');
		params.EvnRecept_otpDate = Ext.util.Format.date(base_form.findField('EvnRecept_otpDate').getValue(), 'd.m.Y');

		getWnd('swDocumentUcStrRashEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.documentUcStrData ) {
					return false;
				}

				var index = view_frame.getGrid().getStore().findBy(function(rec) {
					if ( rec.get('DocumentUcStr_oid') == data.documentUcStrData.DocumentUcStr_oid ) {
						return true;
					}
					else {
						return false;
					}
				});
				var record = view_frame.getGrid().getStore().getAt(index);

				if ( !record ) {
					if ( view_frame.getGrid().getStore().getCount() == 1 && !view_frame.getGrid().getStore().getAt(0).get('DocumentUcStr_oid') ) {
						view_frame.removeAll({
							addEmptyRecord: false
						});
					}

					view_frame.getGrid().getStore().loadData([ data.documentUcStrData ], true);
				}
				else {
					var document_uc_str_fields = new Array();
					var i = 0;

					view_frame.getGrid().getStore().fields.eachKey(function(key, item) {
						document_uc_str_fields.push(key);
					});

					for ( i = 0; i < document_uc_str_fields.length; i++ ) {
						record.set(document_uc_str_fields[i], data.documentUcStrData[document_uc_str_fields[i]]);
					}

					record.commit();
				}

				this.recountTotalSum();
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				view_frame.focus();
			}.createDelegate(this),
			ReceptFinance_Code: this.findById('ERPW_EvnReceptInfo').getFieldValue('ReceptFinance_Code')
		});
	},
	plain: true,
	processMode: 'single',
	putEvnReceptOnDelay: function() {
		// Постановка рецепта на отсрочку
		if ( this.buttons[1].hidden ) {
			return false;
		}

		var base_form = this.findById('EvnReceptProcessForm').getForm();

		var params = new Object();

		var evn_recept_id = base_form.findField('EvnRecept_id').getValue();
		var evn_recept_obr_date = base_form.findField('EvnRecept_obrDate').getValue();

		if ( !evn_recept_id || evn_recept_id <= 0 ) {
			return false;
		}

		if ( !base_form.findField('EvnRecept_obrDate').isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('EvnRecept_obrDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		params.EvnRecept_obrDate = Ext.util.Format.date(evn_recept_obr_date, 'd.m.Y');
		params.EvnRecept_id = evn_recept_id;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					// AJAX запрос к серверу;
					// url = '/?c=Farmacy&m=putEvnReceptOnDelay'
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, выполняется постановка рецепта на отсрочку..." });
					loadMask.show();

					Ext.Ajax.request({
						callback: function(options, success, response) {
							loadMask.hide();

							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg, function() { this.hide(); }.createDelegate(this) );
									return false;
								}

								this.callback({
									DelayType_Name: lang['otlojen'],
									EvnRecept_id: evn_recept_id,
									EvnRecept_Num: this.findById('ERPW_EvnReceptInfo').getFieldValue('EvnRecept_Num'),
									EvnRecept_Ser: this.findById('ERPW_EvnReceptInfo').getFieldValue('EvnRecept_Ser'),
									EvnRecept_Sum: ''
								});

								sw.swMsg.alert(lang['soobschenie'], lang['retsept_byil_uspeshno_postavlen_na_otsrochku'], function() { this.doReset(); }.createDelegate(this) );
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_postanovke_retsepta_na_otsrochku']);
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=Farmacy&m=putEvnReceptOnDelay'
					});
				}
				else {
					this.buttons[1].focus();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['retsept_popadaet_v_razryad_otsrochennyih_prodoljit'],
			title: lang['podtverjdenie']
		});

		return true;
	},
	recountTotalSum: function() {
		// Считает сумму к оплате
		var base_form = this.findById('EvnReceptProcessForm').getForm();
		var recept_discount_code = this.findById('ERPW_EvnReceptInfo').getFieldValue('ReceptDiscount_Code');
		var recept_discount = 0;
		var sum_to_pay = 0;
		var total_sum = 0;
		var view_frame = this.findById('ERPW_EvnReceptDocumentUcStrGrid');

		base_form.findField('EvnRecept_Summa').setValue('');

		switch ( this.processMode ) {
			case 'multi':
				view_frame.getGrid().getStore().each(function(rec) {
					total_sum = total_sum + rec.get('DocumentUcStr_SumNdsR');
				});
			break;

			case 'single':
				total_sum = base_form.findField('DocumentUcStr_SumNdsR').getValue();
			break;
		}

		switch ( recept_discount_code ) {
			case 2:
				sum_to_pay = Number(total_sum * 0.5).toFixed(2);
			break;
		}

		base_form.findField('EvnRecept_Summa').setValue(sum_to_pay);

		return true;
	},
	resizable: false,
	show: function() {
		sw.Promed.swEvnReceptProcessWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		this.findById('ERPW_DocumentUcStrMultiPanel').collapse();
		this.findById('ERPW_DocumentUcStrSinglePanel').expand();

		this.doReset();

		var base_form = this.findById('EvnReceptProcessForm').getForm();

		setCurrentDateTime({
			callback: function(date) {
				base_form.findField('EvnRecept_otpDate').setMaxValue(date);
			},
			dateField: base_form.findField('EvnRecept_obrDate'),
			loadMask: true,
			setDate: true,
			setDateMaxValue: true,
			setDateMinValue: false,
			windowId: 'EvnReceptProcessWindow'
		});

		if ( arguments[0] ) {
			if ( arguments[0].callback ) {
				this.callback = arguments[0].callback;
			}

			if ( arguments[0].EvnRecept_Ser ) {
				// ivp: добавил установку значения поля серии, переданного из формы поточного ввода
				base_form.findField('EvnRecept_Ser').setRawValue(arguments[0].EvnRecept_Ser);
			}

			if ( arguments[0].onHide ) {
				this.onHide = arguments[0].onHide;
			}

			if ( arguments[0].EvnRecept_id ) {
				this.getEvnReceptInfo(arguments[0].EvnRecept_id);
			}
		}
	},
	title: lang['obrabotka_retseptov'],
	width: 700
});
