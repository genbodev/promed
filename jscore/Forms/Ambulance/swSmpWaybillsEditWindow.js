/**
* swSmpWaybillsEditWindow - окно редактирования путевых листов и ГСМ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author		Dyomin Dmitry
* @since      09.2012
*/

sw.Promed.swSmpWaybillsEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	id: 'swSmpWaybillsEditWindow',
	objectName: 'swSmpWaybillsEditWindow',
	objectSrc: '/jscore/Forms/Ambulance/swSmpWaybillsEditWindow.js',
	formStaus: 'edit',
	action: null,
	buttonAlign: 'left',
	layout: 'form',
	closable: true,
	collapsible: false,
	draggable: true,
	//width: 900,
	maximizable: true,
	maximized: false,	
	resizable: true,
	minHeight: 500,
	minWidth: 900,
	height: 500,
	width: 900,
	modal: true,
	shim: false,
	plain: true,
	callback: Ext.emptyFn,
	onCancelAction: Ext.emptyFn,
	onHide: Ext.emptyFn,
	
	loadMask: false,
	getLoadMask: function( msg ){
		if ( !this.loadMask ) {
			if ( typeof msg == 'undefined' ) {
				msg = LOAD_WAIT;
			}
			this.loadMask = new Ext.LoadMask(this.getEl(),{msg: msg});
		}
		return this.loadMask;
	},
	
	storeFields: function( gridFields ){
		var storeFields = [];
		for( var i=0, cnt=gridFields.length; i<cnt; i++ ){
			storeFields.push({
				mapping: gridFields[i].dataIndex,
				name: gridFields[i].dataIndex
			});
		}
		return storeFields;
	},
	
	enableEdit: function( enable ){
		this.FormPanel.getForm().items.each(function(item){
			item.setDisabled( enable ? false : true );
		});
		
		//var RouteGrid = this.FormPanel.findById(this.id+'RouteGrid');
		//RouteGrid.ViewActions.action_add.setDisabled(true);
	},
	
	initComponent: function(){
		
		var timeFieldSettings = {
			format: 'H:i',
			plugins: [ new Ext.ux.InputTextMask('99:99', true) ]
		};
		
		var gridFields = [
			{ name: 'WaybillRoute_id', type: 'int', header: 'ID', key: true },
			{ name: 'WaybillRoute_CustCode', header: lang['kod_zakazchika'], editor: new Ext.form.TextField(), width: 100 },
			{ name: 'WaybillRoute_PointStart', header: lang['mesto_otpravleniya'], editor: new Ext.form.TextField(), width: 200 },
			{ name: 'WaybillRoute_PointFinish', header: lang['mesto_naznacheniya'], editor: new Ext.form.TextField(), width: 200 },
			{ name: 'WaybillRoute_TimeStart', header: lang['vremya_vyiezda'], editor: new Ext.form.TimeField({
																						format: 'H:i',
																						plugins: [ new Ext.ux.InputTextMask('99:99', true) ]
																					}), width: 120 },
			{ name: 'WaybillRoute_TimeFinish', header: lang['vozvrascheniya'], editor: new Ext.form.TimeField({
																						format: 'H:i',
																						plugins: [ new Ext.ux.InputTextMask('99:99', true) ]
																					}), width: 120 },
			{ name: 'WaybillRoute_Trip', header: lang['proydeno_km'], editor: new Ext.form.TextField(), width: 100 }
		];
		
		var gridActions = [
			{ name: 'action_add', handler: function(){ this.findById(this.id+'RouteGrid').addEmptyRow(); }.createDelegate(this), hidden: false },
			{ name: 'action_edit', handler: function(){ this.findById(this.id+'RouteGrid').editSelectedCell(); }.createDelegate(this), disabled: true },
			{ name: 'action_view', disabled: true, hidden: true },
			{ name: 'action_delete', handler: function(){ this.findById(this.id+'RouteGrid').deleteRow(); }.createDelegate(this), disabled: true },
			{ name: 'action_refresh', disabled: true, hidden: true },
			{ name: 'action_print', disabled: true, hidden: true },
			{ name: 'action_save', disabled: true, hidden: true }
		];

		var RouteGrid = new sw.Promed.ViewFrame({
			actions: gridActions,
			autoLoadData: false,
			autoexpand: 'expand',
			border: true,
			dataUrl: '/?c=Waybill&m=loadWaybillRoute',
			height: 130,
			id: this.id+'RouteGrid',
			region: 'center',
			saveAtOnce: false,
			selectionModel: 'cell',
			stringfields: gridFields,
			onLoadData: function(){
				/*
				var store = this.getGrid().getStore();
				store.each(function(el){
					if ( el.data.EvnUslugaPar_Result) {
						var anres = (Ext.util.JSON.decode(el.data.EvnUslugaPar_Result));
						var rec = store.getById(el.data.UslugaComplex_id);
						if (rec) {
							rec.set('EUD_value', anres.EUD_value);
							rec.set('EUD_lower_bound', anres.EUD_lower_bound);
							rec.set('EUD_upper_bound', anres.EUD_upper_bound);
							rec.set('EUD_unit_of_measurement', anres.EUD_unit_of_measurement);
							rec.commit();
						}
					}
				});
				if ( that.EvnUslugaPar_Result ) {
					var UslugaResults = Ext.util.JSON.decode(that.EvnUslugaPar_Result);
					if ( typeof UslugaResults == 'object' ) {
						for( var i=0, cnt=UslugaResults.length; i<cnt; i++ ){
							var rec = this.getGrid().getStore().getById(UslugaResults[i].UslugaComplex_id);
							rec.set('EUD_lower_bound',UslugaResults[i].EUD_lower_bound);
							rec.set('EUD_unit_of_measurement',UslugaResults[i].EUD_unit_of_measurement);
							rec.set('EUD_upper_bound',UslugaResults[i].EUD_upper_bound);
							rec.set('EUD_value',UslugaResults[i].EUD_value);
							rec.commit();
						}
					}
				}
				*/
			},
			addEmptyRow: function() {
				var grid = this.getGrid();
				
				// Генерируем значение идентификатора с отрицательным значением
				// чтобы оперировать несохраненными записями
				var id = - swGenTempId( grid.getStore() );

				grid.getStore().loadData([{ WaybillRoute_id: id }], true);
				
				var rowsCnt = grid.getStore().getCount() - 1;
				var rowSel = 1;
				grid.getSelectionModel().select( rowsCnt, rowSel );
				grid.getView().focusCell( rowsCnt, rowSel );

				var cell = grid.getSelectionModel().getSelectedCell();
				if ( !cell || cell.length == 0 || cell[1] != rowSel ) {
					return false;
				}

				var record = grid.getSelectionModel().getSelected();
				if ( !record ) {
					return false;
				}

				grid.getColumnModel().setEditable( rowSel, true );
				grid.startEditing( cell[0], cell[1] );
			},
			deleteRow: function() {
				var grid = this.getGrid();

				var record = grid.getSelectionModel().getSelected();
				if ( !record ) {
					alert('no record');
					return false;
				}

				var id = record.get('WaybillRoute_id');
				if ( !id ) {
					sw.swMsg.alert( lang['oshibka'], lang['ne_udalos_poluchit_identifikator_putevogo_lista'] );
					return false;
				}
				
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					msg: lang['udalit_zapis'],
					title: lang['udalenie_zapisi'],
					fn: function( buttonId ) {
						if ( buttonId != 'yes' ) {
							return false;
						}
						
						// Запись еще не сохранена? Просто вычеркиваем
						if ( id < 1 ) {
							grid.getStore().remove(record);
							if ( grid.getStore().getCount() > 0 ) {
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						} else {
							// Здесь мы можем удалить лишнее через аякс
							// Но я хочу чтобы это было только через общую
							// кнопку сохранить. Иниипет
							grid.getStore().remove(record);
							if ( grid.getStore().getCount() > 0 ) {
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							
							/*
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
							*/
						}
					}.createDelegate(this)
				});
			},
			onCellSelect: function(sm,rowIdx,colIdx){
				var grid = this.getGrid();
				var record = grid.getSelectionModel().getSelected();
				this.getAction('action_edit').setDisabled( record.get('WaybillRoute_id') === null );
				this.getAction('action_delete').setDisabled( record.get('WaybillRoute_id') === null );
			},
			editSelectedCell: function(){
				var grid = this.getGrid();
				
				var rowsCnt = grid.getStore().getCount() - 1;
				var rowSel = 1;
				var cell = grid.getSelectionModel().getSelectedCell();
				if ( !cell || cell.length == 0 ) {
					return false;
				}

				var record = grid.getSelectionModel().getSelected();
				if ( !record ) {
					return false;
				}

				grid.getColumnModel().setEditable( rowSel, true );
				grid.startEditing( cell[0], cell[1] );
			}
		});
		
		var items = [{
			border: false,
			layout: 'form',
			items: [{
				name: 'Waybill_id',
				value: 0,
				xtype: 'hidden'
			},{
				autoHeight: true,
				style: 'padding: 5px;',
				title: lang['brigada'],
				xtype: 'fieldset',
				items: [{
					xtype: 'swemergencyteamcombo',
					id: 'emergencyteam'
				}]
			},{
				autoHeight: true,
				style: 'padding: 5px;',
				title: lang['obschie_svedeniya'],
				xtype: 'fieldset',
				items: [{
					xtype: 'textfield',
					fieldLabel: lang['seriya_pl'],
					name: 'Waybill_Series',
					width: 100,
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',	
					fieldLabel: lang['nomer_pl'],
					name: 'Waybill_Num',
					width: 100,
					regex: new RegExp(/^[0-9]+$/),
					allowBlank: false,
					disabledClass: 'field-disabled'
				},{
					xtype: 'swdatefield',
					fieldLabel: lang['data_pl'],
					name: 'Waybill_Date',
					value: new Date(),
					allowBlank: false
				},{
					xtype: 'textfield',
					fieldLabel: lang['garajnyiy_nomer'],
					name: 'Waybill_GarageNum',
					width: 100,
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['tabelnyiy_nomer'],
					name: 'Waybill_EmployeeNum',
					width: 100,
					allowBlank: false,
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['nomer_udostovereniya'],
					name: 'Waybill_IdentityNum',
					width: 100,
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['klass'],
					name: 'Waybill_Class',
					width: 100,
					disabledClass: 'field-disabled'
				},{
					xtype:			'swbaselocalcombo',
					fieldLabel:		lang['litsenzionnaya_kartochka'],
					displayField:	'Waybill_LicenseCard_Name',
					codeField:		'Waybill_LicenseCard_Code',
					valueField:		'Waybill_LicenseCard_Code',
					hiddenName:		'Waybill_LicenseCard',
					editable:		false,
					anchor:			'100%',
					store: new Ext.data.SimpleStore({
						key: 'Waybill_LicenseCard',
						autoLoad: true,
						fields:	[
							{name:'Waybill_LicenseCard_Code', type:'int'},
							{name:'Waybill_LicenseCard_Name', type:'string'}
						],
						data : [
							[1, lang['standartnaya']],
							[2, lang['ogranichennaya']]
						]
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">'
						+'<font color="red">{Waybill_LicenseCard_Code}</font>&nbsp;{Waybill_LicenseCard_Name}'
						+'</div></tpl>'
					)
				},{
					xtype: 'textfield',
					fieldLabel: lang['registratsionnyiy_№'],
					name: 'Waybill_RegNum',
					width: 100,
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['seriya'],
					name: 'Waybill_RegSeries',
					width: 100,
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['nomer'],
					name: 'Waybill_RegNum2',
					width: 100,
					disabledClass: 'field-disabled'
				}]
			},{
				autoHeight: true,
				style: 'padding: 5px;',
				title: lang['zadanie_voditelyu'],
				xtype: 'fieldset',
				items: [{
					xtype: 'textfield',
					fieldLabel: lang['adres_podachi'],
					name: 'Waybill_Address',
					anchor: '100%',
					allowBlank: false,
					disabledClass: 'field-disabled'
				},{
					xtype: 'swtimefield',
					fieldLabel: lang['vremya_vyiezda_iz_garaja'],
					name: 'Waybill_TimeStart',
					allowBlank: false,
					plugins: [ new Ext.ux.InputTextMask('99:99', true) ]
				},{
					xtype: 'swtimefield',
					fieldLabel: lang['vremya_vozvrascheniya_v_garaj'],
					name: 'Waybill_TimeFinish',
					plugins: [ new Ext.ux.InputTextMask('99:99', true) ]
				},{
					xtype: 'textarea',
					fieldLabel: lang['opozdaniya_ojidaniya_prostoi_zaezdyi_v_garaj_i_t_p'],
					name: 'Waybill_Justification',
					anchor: '100%',
					height: 60
				}]
			},{
				autoHeight: true,
				style: 'padding: 5px;',
				title: lang['uchet_gsm'],
				xtype: 'fieldset',
				items: [{
					xtype: 'textfield',
					fieldLabel: lang['pokazaniya_spidometra_pri_vyiezde_km'],
					name: 'Waybill_OdometrBefore',
					width: 100,
					allowBlank: false,
					regex: new RegExp(/^[0-9]+$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['pokazaniya_spidometra_pri_vozvraschenii_km'],
					name: 'Waybill_OdometrAfter',
					width: 100,
					regex: new RegExp(/^[0-9]+$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'swcommonsprcombo',
					fieldLabel: lang['marka_goryuchego'],
					comboSubject: 'WaybillGas',
					hiddenName: 'WaybillGas_id',
					displayField: 'WaybillGas_Name',
					anchor: '100%',
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['№_zapravochnogo_lista'],
					name: 'Waybill_RefillCardNum',
					width: 100,
					regex: new RegExp(/^[0-9]+$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['vyidano_po_zapravochnomu_listu_l'],
					name: 'Waybill_FuelGet',
					width: 100,
					regex: new RegExp(/^[0-9]+$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['ostatok_pri_vyiezde_l'],
					name: 'Waybill_FuelBefore',
					width: 100,
					regex: new RegExp(/^[0-9]+$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['ostatok_pri_vozvraschenii_l'],
					name: 'Waybill_FuelAfter',
					width: 100,
					regex: new RegExp(/^[0-9]+$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['rashod_po_norme_l'],
					name: 'Waybill_FuelConsumption',
					width: 100,
					regex: new RegExp(/^[0-9]+.?[0-9]{0,2}$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['rashod_fakticheskiy_l'],
					name: 'Waybill_FuelFact',
					width: 100,
					regex: new RegExp(/^[0-9]+.?[0-9]{0,2}$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['ekonomiya_l'],
					name: 'Waybill_FuelEconomy',
					width: 100,
					regex: new RegExp(/^[0-9]+$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['pererashod_l'],
					name: 'Waybill_FuelOverrun',
					width: 100,
					regex: new RegExp(/^[0-9]+$/),
					disabledClass: 'field-disabled'
				}]
			},{
				autoHeight: true,
				style: 'padding: 5px;',
				title: lang['marshrut'],
				xtype: 'fieldset',
				items: RouteGrid
			},{
				autoHeight: true,
				style: 'padding: 5px;',
				title: lang['dopolnitelnyie_svedeniya'],
				xtype: 'fieldset',
				items: [{
					xtype: 'textfield',
					fieldLabel: lang['vsego_v_naryade_chasov'],
					name: 'Waybill_PersonCnt',
					width: 100,
					regex: new RegExp(/^[0-9]+$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['proydeno_km'],
					name: 'Waybill_Trip',
					width: 100,
					regex: new RegExp(/^[0-9]+$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['za_kilometraj_rub_kop'],
					name: 'Waybill_PaymentOdometr',
					width: 100,
					regex: new RegExp(/^[0-9]+[.,]?[0-9]*$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['za_chasyi_rub_kop'],
					name: 'Waybill_PaymentTime',
					width: 100,
					regex: new RegExp(/^[0-9]+[.,]?[0-9]*$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['itogo_rub_kop'],
					name: 'Waybill_PaymentTotal',
					width: 100,
					regex: new RegExp(/^[0-9]+[.,]?[0-9]*$/),
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['doljnost_proizvodivshego_raschet'],
					name: 'Waybill_CalcMakePost',
					anchor: '100%',
					disabledClass: 'field-disabled'
				},{
					xtype: 'textfield',
					fieldLabel: lang['fio_proizvodivshego_raschet'],
					name: 'Waybill_CalcMakeName',
					anchor: '100%',
					disabledClass: 'field-disabled'
				}]
			}]
		}];
		
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 5px 5px',
			border: false,
			frame: true,
			id: this.id + 'EditForm',
			labelAlign: 'right',
			labelWidth: 200,
			region: 'center',
			url: '/?c=Waybill&m=saveWaybill',
			items: items,
			reader: new Ext.data.JsonReader({},[
				{ name: 'Waybill_id' },
				{ name: 'EmergencyTeam_id' },
				{ name: 'Waybill_Series' },
				{ name: 'Waybill_Num' },
				{ name: 'Waybill_Date' },
				{ name: 'Waybill_GarageNum' },
				{ name: 'Waybill_EmployeeNum' },
				{ name: 'Waybill_IdentityNum' },
				{ name: 'Waybill_Class' },
				{ name: 'Waybill_LicenseCard' },
				{ name: 'Waybill_RegNum' },
				{ name: 'Waybill_RegSeries' },
				{ name: 'Waybill_RegNum2' },
				{ name: 'Waybill_Address' },
				{ name: 'Waybill_TimeStart' },
				{ name: 'Waybill_TimeFinish' },
				{ name: 'Waybill_Justification' },
				{ name: 'Waybill_OdometrBefore' },
				{ name: 'Waybill_OdometrAfter' },
				{ name: 'WaybillGas_id' },
				{ name: 'Waybill_RefillCardNum' },
				{ name: 'Waybill_FuelGet' },
				{ name: 'Waybill_FuelBefore' },
				{ name: 'Waybill_FuelAfter' },
				{ name: 'Waybill_FuelConsumption' },
				{ name: 'Waybill_FuelFact' },
				{ name: 'Waybill_FuelEconomy' },
				{ name: 'Waybill_FuelOverrun' },
				{ name: 'Waybill_PersonCnt' },
				{ name: 'Waybill_Trip' },
				{ name: 'Waybill_PaymentOdometr' },
				{ name: 'Waybill_PaymentTime' },
				{ name: 'Waybill_PaymentTotal' },
				{ name: 'Waybill_CalcMakePost' },
				{ name: 'Waybill_CalcMakeName' }
			])
		});

		Ext.apply(this, {
			buttons: [
			{
				handler: function(){
					this.saveWaybill();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},{
				text: '-'
			},
			{
				handler: function(){ShowHelp(this.ownerCt.title);},
				text: BTN_FRMHELP,
				iconCls: 'help16'				
			},	
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}
			],
			items: [ this.FormPanel ],
			layout: 'border'
		});

		sw.Promed.swSmpWaybillsEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swSmpWaybillsEditWindow.superclass.show.apply(this, arguments);

		this.doLayout();
		this.restore();
		this.center();
		
		var base_form = this.FormPanel.getForm();		
		base_form.reset();
		
		this.formStatus = 'edit';

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		
		this.FormPanel.findById('emergencyteam').getStore().load();
		
		// Очищаем список маршрутов
		this.FormPanel.findById(this.id+'RouteGrid').removeAll({ addEmptyRecord: false });

		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['putevoy_list_dobavlenie']);
				this.enableEdit( true );
				loadMask.hide();
				base_form.clearInvalid();
			break;

			case 'edit':
			case 'view':
				if ( !arguments[0].Waybill_id ) {
					loadMask.hide();
					sw.swMsg.alert( lang['oshibka'], lang['ne_peredan_identifikator_putevogo_lista'], function(){ this.hide(); }.createDelegate(this) );
				}
				
				var params = {
					Waybill_id: arguments[0].Waybill_id
				}
				
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function(){ this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: params,
					success: function(){
						if ( this.action == 'edit' ) {
							this.setTitle(lang['putevoy_list_redaktirovanie']);
							this.enableEdit( true );
						} else {
							this.setTitle(lang['putevoy_list_prosmotr']);
							this.enableEdit( false );
						}
						
						var WaybillRouteGrid = this.findById(this.id+'RouteGrid');
						WaybillRouteGrid.loadData({
							params: params,
							globalFilters: params,
							noFocusOnLoad: true
				        });
						
						loadMask.hide();
					}.createDelegate(this),
					url: '/?c=Waybill&m=loadWaybill'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	
	saveWaybill: function(){
		
		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		this.formStatus = 'save';

		var base_form = this.findById(this.id + 'EditForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function(){
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = this.getLoadMask(lang['sohranenie_dannyih_putevogo_lista']);
		loadMask.show();
		
		// Доп. параметры
		var params = {};
	
		// Получаем список маршрутов
		var waybill_route = getStoreRecords( this.findById(this.id+'RouteGrid').getGrid().getStore(), {} );
		params.WaybillRoute = Ext.util.JSON.encode( waybill_route );

		base_form.submit({
			params: params,
			failure: function(result_form, action){
				this.formStatus = 'edit';
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_informatsii_proizoshla_oshibka']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.Waybill_id ) {
						if ( typeof this.callback == 'function' ){
							//todo Сериализовать данные формы для передачи их функции
							this.callback();
						}
						this.hide();
					} else if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['rezultat_sohraneniya_ne_vernul_ojidaemyih_dannyih']);
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_informatsii_proizoshla_oshibka']);
				}
			}.createDelegate(this)
		});
	}
	
});