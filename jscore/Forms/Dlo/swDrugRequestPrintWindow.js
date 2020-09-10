/**
* swDrugRequestPrintWindow - окно с фильтрами для печати заявки.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-18.10.2009
* @comment      Префикс для id компонентов DRPW (DrugRequestPrintWindow)
*
*
* @input data: MedPersonal_id - ID врача
*              DrugRequestPeriod_id - период заявки
*/

sw.Promed.swDrugRequestPrintWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	drugRequestPeriodId: null,
	filterLpuId: null,
	id: 'DrugRequestPrintWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.print();
				}.createDelegate(this),
				iconCls: 'print16',
				// tabIndex: 204,
				text: BTN_FRMPRINT
			}, {				text: '-'
			},
			HelpButton(this/*, 206*/),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabElement: 'DRPW_PrintTypeCombo',
				// tabIndex: 207,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'DrugRequestPrintForm',
				labelAlign: 'right',
				labelWidth: 130,
				items: [{
					allowBlank: false,
					codeField: 'PrintType_Code',
					displayField: 'PrintType_Name',
					editable: false,
					fieldLabel: lang['tip_pechati'],
					hiddenName: 'PrintType_id',
					id: 'DRPW_PrintTypeCombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							//
						}.createDelegate(this)
					},
					lastQuery: '',
					listWidth: 500,
					store: new Ext.data.SimpleStore({
						autoLoad: true,
						data: [
							[ 1, 1, lang['pechat_s_gruppirovkoy_po_medikamentam_zayavlennyim_vrachami_lpu'] ],
							[ 2, 2, lang['pechat_s_gruppirovkoy_po_patsientam_iz_zayavok_vrachey_lpu'] ],
							[ 3, 3, lang['pechat_s_gruppirovkoy_po_patsientu_i_so_spiskom_medikamentov'] ],
							[ 4, 4, lang['itogovyie_svedeniya_po_zayavke_lpu'] ],
							[ 5, 5, lang['polnaya_zayavka_na_patsientov'] ],
							[ 6, 6, lang['zayavki_vrachey'] ],
							[ 7, 7, lang['svodnaya_zayavka_na_prikreplennyih_k_lpu_patsientov'] ],
							[ 8, 8, lang['pechat_zayavki_s_dannyimi_patsienta'] ],
							[ 9, 9, lang['prevyishenie_limita'] ],
							[ 10, 10, lang['zayavka_drugih_lpu_na_prikreplennyih'] ],
							[ 11, 11, lang['zayavka_mz_na_prikreplennyih'] ],
							[ 12, 12, lang['zayavka_onkodispanserom_na_prikreplennyih'] ],
							[ 13, 13, lang['zayavka_onkogematologiey_na_prikreplennyih'] ],
							[ 14, 14, lang['sootvetstvie_vyipiski_i_zayavki_s_gruppirovkoy_po_patsientu_i_so_spiskom_medikamentov'] ],
							[ 15, 15, lang['nesootvetstvie_tipa_lgotyi_i_tipa_zayavki'] ],
							[ 16, 16, lang['zayavka_na_prikreplennyih_k_drugim_lpu'] ],
							[ 17, 17, lang['svodnaya_zayavka_po_medikamentam_otchet_dlya_mz'] ]
						],
						fields: [
							{ name: 'PrintType_id', type: 'int' },
							{ name: 'PrintType_Code', type: 'int' },
							{ name: 'PrintType_Name', type: 'string' }
						],
						key: 'PrintType_id',
						sortInfo: { field: 'PrintType_Code' }
					}),
					// tabIndex: TABINDEX_PRIVSF + 44,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{PrintType_Code}</font>&nbsp;{PrintType_Name}',
						'</div></tpl>'
					),
					valueField: 'PrintType_id',
					width: 300,
					xtype: 'swbaselocalcombo'
				},
				new sw.Promed.SwDrugRequestTypeCombo({
					allowBlank: false,
					anchor: null,
					// emptyText: 'Федеральная и региональная',
					fieldLabel: lang['tip_zayavki'],
					id: 'DRPW_DrugRequestTypeCombo',
					width: 300
				}), {
					allowBlank: false,
					// disabled: true,
					enableKeyEvents: true,
					fieldLabel: lang['data_aktualnosti'],
					format: 'd.m.Y',
					name: 'DrugRequestRow_actDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					// tabIndex: TABINDEX_ERPW + 1,
					width: 100,
					xtype: 'swdatefield'
				}],
				keys: [{
					alt: true,
					fn: function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.G:
								this.print();
							break;

							case Ext.EventObject.J:
								this.hide();
							break;
						}
					},
					key: [
						Ext.EventObject.G,
						Ext.EventObject.J
					],
					scope: this,
					stopEvent: true
				}]
			})]
		});
		sw.Promed.swDrugRequestPrintWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	lpuSectionId: null,
	lpuUnitId: null,
	medPersonalId: null,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	print: function() {
		if ( this.drugRequestPeriodId == null) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_period_zayavki']);
			return false;
		}

		var base_form = this.findById('DrugRequestPrintForm').getForm();

		if ( !base_form.findField('DrugRequestRow_actDate').isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernoe_znachenie_polya_data_aktualnosti'], function() { base_form.findField('DrugRequestRow_actDate').focus(true); });
			return false;
		}

		var drug_request_actual_date = Ext.util.Format.date(base_form.findField('DrugRequestRow_actDate').getValue(), 'd.m.Y');
		var drug_request_period_id = this.drugRequestPeriodId;
		var drug_request_type_id = this.findById('DRPW_DrugRequestTypeCombo').getValue();
		var filter_lpu_id = this.filterLpuId;
		var lpu_section_id = this.lpuSectionId;
		var lpu_unit_id = this.lpuUnitId;
		var med_personal_id = this.medPersonalId;
		var print_type_id = this.findById('DRPW_PrintTypeCombo').getValue();

		var query_string = C_DRUGREQUEST_PRINT + '&DrugRequestRow_actDate=' + drug_request_actual_date;

		if ( drug_request_period_id )
			query_string = query_string + '&DrugRequestPeriod_id=' + drug_request_period_id;

		if ( drug_request_type_id )
			query_string = query_string + '&DrugRequestType_id=' + drug_request_type_id;

		if ( filter_lpu_id )
			query_string = query_string + '&FilterLpu_id=' + filter_lpu_id;

		if ( lpu_section_id )
			query_string = query_string + '&LpuSection_id=' + lpu_section_id;

		if ( lpu_unit_id )
			query_string = query_string + '&LpuUnit_id=' + lpu_unit_id;

		if ( med_personal_id )
			query_string = query_string + '&MedPersonal_id=' + med_personal_id;

		if ( print_type_id )
			query_string = query_string + '&PrintType_id=' + print_type_id;

		if ( !drug_request_type_id ) {
			this.findById('DRPW_DrugRequestTypeCombo').focus();
			return false;
		}

		switch ( print_type_id ) {
			case 5:
			case 14:
				if ( !med_personal_id ) {
					sw.swMsg.alert(lang['oshibka'], lang['dlya_polucheniya_vyibrannogo_otcheta_neobhodimo_ukazat_vracha']);
					return false;
				}
			break;
		}

		window.open(query_string, '_blank');
	},
	resizable: false,
	show: function() {
		sw.Promed.swDrugRequestPrintWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('DrugRequestPrintForm').getForm();

		base_form.reset();

		base_form.findField('PrintType_id').getStore().filterBy(function(record) {
			if ( record.get('PrintType_Code') == 17 && !isAccessTreatment() ) {
				return false;
			}
			else {
				return true;
			}
		});

		this.drugRequestPeriodId = null;
		this.filterLpuId = null;
		this.lpuSectionId = null;
		this.lpuUnitId = null;
		this.medPersonalId = null;
		this.onHide = Ext.emptyFn;

		var dt = new Date();

		base_form.findField('PrintType_id').setValue(1);
		base_form.findField('DrugRequestType_id').setValue(1);
		base_form.findField('DrugRequestType_id').enable();
		base_form.findField('DrugRequestRow_actDate').setValue(dt);

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].DrugRequestPeriod_id ) {
			this.drugRequestPeriodId = arguments[0].DrugRequestPeriod_id;
		}

		if ( arguments[0].FilterLpu_id ) {
			this.filterLpuId = arguments[0].FilterLpu_id;
		}

		if ( arguments[0].LpuSection_id ) {
			this.lpuSectionId = arguments[0].LpuSection_id;
		}

		if ( arguments[0].LpuUnit_id ) {
			this.lpuUnitId = arguments[0].LpuUnit_id;
		}

		if ( arguments[0].MedPersonal_id ) {
			this.medPersonalId = arguments[0].MedPersonal_id;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
	},
	title: lang['pechat_zayavki'],
	width: 500
});