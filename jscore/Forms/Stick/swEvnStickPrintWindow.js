/**
* swEvnStickPrintWindow - окно печати ЛВН. (префикс ESPW)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Stick
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      0.001-05.03.2012
*/

sw.Promed.swEvnStickPrintWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	draggable: true,
	width: 600,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['lvn_pechat'],
	id: 'EvnStickPrintWindow',
	
	initComponent: function() {

		var _this = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.onSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: 'ESPW_SelectButton',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: lang['ok']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'ESPW_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [
				new Ext.form.FormPanel
				({
					id: 'ESPW_EvnStickPrintTypePanel',
					style : 'padding: 3px',
					autoheight: true,
					region: 'center',
					layout : 'form',
					border : false,
					frame : true,
					items: [{
						fieldLabel: lang['tip_pechati'],
						valueField: 'EvnStickPrintType_id',
						displayField: 'EvnStickPrintType_descr',
						hiddenName : 'EvnStickPrintType_id',
						readOnly: false,
						autoHeight: true,
						anchor: '95%',
						allowBlank: false,
						store: new Ext.data.SimpleStore({
							fields: ['EvnStickPrintType_id', 'EvnStickPrintType_descr'],
							data : [['1', lang['raspechatat_vse_dannyie_lvn']], ['2', lang['dopechatat_lvn']]] 
						}),
						listeners: {
							'change': function(field, newValue, oldValue) {
								var index = field.getStore().findBy(function(rec) {
									return (rec.get(field.valueField) == newValue);
								});
								field.fireEvent('select', field, field.getStore().getAt(index), index);
							},
							'select': function(field, record, index) {
								var fieldset = this.findById("ESPW_Fields");
								var evnFieldset = Ext.getCmp('ESPW_PersonEvnsFieldSet');
								var form = this.findById("ESPW_EvnStickPrintTypePanel").getForm(),
									SstEndDate_checkBox = this.findById("SstEndDate_id");
								
								form.findField('ParamLeaveTypeIsOn').setContainerVisible(_this.allowChoseFieldsToPrint && record.get('EvnStickPrintType_id') != 2);

								SstEndDate_checkBox.setValue(false);
								if ( typeof record == 'object' ) {
									if ( record.get('EvnStickPrintType_id') == 2 ) {
										fieldset.show();


										if (_this.allowPrintEndDate){
											SstEndDate_checkBox.setValue(true);
											SstEndDate_checkBox.showContainer();
										}

										// скрыть поля CarePerson1 и Person
										var hideFieldset = true;
										evnFieldset.items.each(function(item) {
											if (item.name.inlist(['CarePerson1', 'Person'])) {
												item.hideContainer();
											} else if (this.PersonEvnRecords[item.name] && this.PersonEvnRecords[item.name][1]) {
												hideFieldset = false;
											}
										}.createDelegate(this));
										if (hideFieldset) {
											evnFieldset.hide();
										}
										this.center();
									} else {
										fieldset.hide();
										// показать поля CarePerson1 и Person
										var showFieldset = false;
										evnFieldset.items.each(function(item) {
											if (this.PersonEvnRecords[item.name] && this.PersonEvnRecords[item.name][1] && (item.name == 'CarePerson1' || item.name == 'Person')) { 
												item.showContainer(); 
												showFieldset = true; 
											}
										}.createDelegate(this));
										if (showFieldset) {
											evnFieldset.show();
										}
										this.center();
									}
								}
								else {
									fieldset.hide();
									evnFieldset.hide();

									evnFieldset.items.each(function(item) {
										item.hideContainer(); 
									}.createDelegate(this));

									this.center();
								}
							}.createDelegate(this)
						},
						tpl: new Ext.XTemplate(
						  '<tpl for="."><div class="x-combo-list-item">',
						  '<div>{EvnStickPrintType_descr}</div>',
						  '</div></tpl>'
						),
						value: 1,
						xtype: 'swcommonsprcombo'
					}, {
						//boxLabel: 'Печатать штрих-код',
						checked: true,
						disabled: false,
						hideLabel: true,
						name: 'ParamShtrihIsOn',
						vfield: 'checked',
						xtype: 'checkbox',
						//autoHeight: true,
						height: 50,
						boxLabel: lang['pechatat_shtrih-kod_shtrih-kod_sootvetstvuet_tekuschey_informatsii_vvedennoy_na_forme_i_pri_dalneyshem_izmenenii_i_dopechatyivanii_lvn_shtrih-kod_izmenit_nelzya_esli_shtrih-kod_raspechatan_na_blanke_ranee_to_povtornaya_ego_pechat_ne_dopustima_eto_mojet_privesti_k_porche_blanka']
					}, {
						boxLabel: lang['pechatat_ishod'],
						checked: true,
						disabled: false,
						hideLabel: true,
						name: 'ParamLeaveTypeIsOn',
						vfield: 'checked',
						xtype: 'checkbox'
					},{
						autoHeight: true,
						id: 'ESPW_Fields',
						labelWidth: 200,
						style: 'padding: 2px 0px 0px 4px;',
						title: lang['polya_dlya_pechati'],
						hidden: true,
						xtype: 'fieldset',
						items: 
						[{
							xtype: 'checkbox',
							hideLabel: true,			
							name: 'StickCauseDopType',
							hidden: false,
							boxLabel: lang['dop_kod_netrudosposobnosti']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: 'StickCauseDid',
							hidden: false,
							boxLabel: lang['kod_izm_netrudosposobnosti']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: 'CarePerson',
							hidden: false,
							boxLabel: lang['dannyie_vtorogo_patsienta_nujdayuschegosya_v_uhode_vozrast_rodstvennaya_svyaz_fio_nujdayuschegosya_v_uhode']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: 'Irregularity',
							hidden: false,
							boxLabel: lang['narusheniya_rejima_kod_narusheniya_data_narusheniya']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: 'SstData',
							hidden: false,
							boxLabel: lang['dannyie_po_sanatorno-kurortnomu_lecheniyu_dve_datyi_nomer_putevki_ogrn_sanatoriya']
						},{
							xtype: 'checkbox',
							hideLabel: true,
							name: 'SstEndDate',
							id:'SstEndDate_id',
							hidden: true,
							boxLabel: lang['dopechatat_dannyie_ob_osvobojdenii_ot_rabotyi']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: 'Pregnancy',
							hidden: false,
							boxLabel: lang['uchet_beremennyih_do_12_nedel_flag_da_net']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: 'MseDates',
							hidden: false,
							boxLabel: lang['dannyie_po_mse_data_napravleniya_na_mse_data_registratsii_dokumentov_v_mse_data_osvidetelstvovaniya_v_mse_ustanovlena_invalidnost']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: '2ndWorkRelease',
							hidden: false,
							boxLabel: lang['vtoroy_period_osvobojdeniya_s_kakogo_chisla_po_kakoe_chislo_doljnost_vracha_fio_vracha']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: '3rdWorkRelease',
							hidden: false,
							boxLabel: lang['tretiy_period_osvobojdeniya_s_kakogo_chisla_po_kakoe_chislo_doljnost_vracha_fio_vracha']
						},{
							xtype: 'checkbox',
							hideLabel: true,
							name: '4thWorkRelease',
							hidden: false,
							boxLabel: lang['chetvertyiy_period_osvobojdeniya_s_kakogo_chisla_po_kakoe_chislo_doljnost_vracha_fio_vracha']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: 'LeaveType',
							hidden: false,
							boxLabel: lang['ishod_lvn_pristupit_k_rabote_s_kod_ishoda_lvn_data_ishoda']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: 'StacDates',
							hidden: false,
							boxLabel: lang['period_lecheniya_v_statsionare_data_nachala_lecheniya_v_statsionare_data_okonchaniya_lecheniya_v_statsionare']
						},{
							xtype: 'checkbox',
							hideLabel: true,			
							name: 'NextEvnStickNum',
							hidden: false,
							boxLabel: lang['nomer_prodoljeniya_lvn_vyidan_lvn-prodoljenie_№']
						},{
							xtype: 'checkbox',
							hideLabel: true,
							name: 'withoutSheetBegin',
							hidden: getRegionNick() != 'kz',
							boxLabel: langs('Без корешка')
        				}]
					}, {
						autoHeight: true,
						id: 'ESPW_PersonEvnsFieldSet',
						labelWidth: 200,
						style: 'padding: 2px 4px 0px 4px;',
						title: lang['personalnyie_dannyie_byili_izmenenyi_v_period_deystviya_lvn_vyiberite_dannyie_kotoryie_budut_vyivedenyi_na_pechat'],
						hidden: true,
						xtype: 'fieldset',
						items: 
						[]
					}]
				})
			]
		});
		sw.Promed.swEvnStickPrintWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnStickPrintWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	minWidth: 600,
	onSelect: function() {

		var form = this.findById("ESPW_EvnStickPrintTypePanel").getForm();
		var printtype = form.findField("EvnStickPrintType_id").getValue();
		
		var periodic_list = "";
		var evnFieldset = Ext.getCmp('ESPW_PersonEvnsFieldSet');
		
		var templist = [];

		evnFieldset.items.each(function(item) {
		
			var PersonEvn_id = "null";
			var Server_id = "null";
			if (this.PersonEvnRecords[item.name]) {
					if ( item.checked ) {
						PersonEvn_id = this.PersonEvnRecords[item.name][item.inputValue].PersonEvn_id;
						Server_id = this.PersonEvnRecords[item.name][item.inputValue].Server_id;
						periodic_list = periodic_list + "&"+item.name+"_PersonEvn_id="+PersonEvn_id+"&"+item.name+"_Server_id="+Server_id;
						templist.push(item.name);
					}
			}
		}.createDelegate(this));
		
		if ( !'Person'.inlist(templist) ) {
			periodic_list = periodic_list + "&Person_PersonEvn_id=null&Person_Server_id=null";
		}
		if ( !'CarePerson1'.inlist(templist) ) {
			periodic_list = periodic_list + "&CarePerson1_PersonEvn_id=null&CarePerson1_Server_id=null";
		}
		if ( !'CarePerson2'.inlist(templist) ) {
			periodic_list = periodic_list + "&CarePerson2_PersonEvn_id=null&CarePerson2_Server_id=null";
		}
		var ParamShtrihIsOn = form.findField("ParamShtrihIsOn");
		if (printtype == 1) {
			if ( getRegionNick() == 'ufa' && this.isELN == true ) {
				printBirt({
					'Report_FileName': 'EvnStickPrintMini.rptdesign',
					'Report_Params': '&paramEvnStick=' + this.EvnStick_id,
					'Report_Format': 'pdf'
				});
			}
			else {
				var Report_Params = '&paramEvnStick=' + this.EvnStick_id + '&evnStickType=' + this.evnStickType + periodic_list;

				if ( Ext.globalOptions.evnstick ) {
					Report_Params = Report_Params + '&marginLeft=' + Ext.globalOptions.evnstick.evnstick_print_leftmargin + 'mm';
					Report_Params = Report_Params + '&marginTop=' + Ext.globalOptions.evnstick.evnstick_print_topmargin + 'mm';
				}
				if(ParamShtrihIsOn.getValue() == true)
				{
					Report_Params = Report_Params + '&ParamShtrihIsOn=2';
				}
				else {
					Report_Params = Report_Params + '&ParamShtrihIsOn=1';
				}

				if(form.findField("ParamLeaveTypeIsOn").getValue() == true) {
					Report_Params = Report_Params + '&LeaveType=1';
				} else {
					Report_Params = Report_Params + '&LeaveType=0';
				}

				printBirt({
					'Report_FileName': this.isELN?'ELN_EvnStickPrint.rptdesign':'EvnStickPrint.rptdesign',
					'Report_Params': Report_Params,
					'Report_Format': 'pdf'
				});
			}

			this.hide();
		} else {
			// печать выбранных полей.
			var fieldset = this.findById("ESPW_Fields");
			var table_list = "";
			var nothingChecked = true;
			fieldset.items.each(function(item) {
				if ( item.checked ) {
					table_list = table_list + "&" + item.name + "=1";
					nothingChecked = false;	
				} else {
					table_list = table_list + "&" + item.name + "=0";
				}
			});
			
			if ( nothingChecked ) {
				sw.swMsg.alert(lang['soobschenie'], lang['vyiberite_hotya_byi_odno_pole_dlya_dopechatyivaniya']);
				return false;
			}
			
			var Report_Params = '&paramEvnStick=' + this.EvnStick_id + '&evnStickType=' + this.evnStickType + table_list + periodic_list;

			if ( Ext.globalOptions.evnstick ) {
				Report_Params = Report_Params + '&marginLeft=' + Ext.globalOptions.evnstick.evnstick_print_leftmargin + 'mm';
				Report_Params = Report_Params + '&marginTop=' + Ext.globalOptions.evnstick.evnstick_print_topmargin + 'mm';
			}
			if(ParamShtrihIsOn.getValue() == true)
			{
				Report_Params = Report_Params + '&ParamShtrihIsOn=2';
			}
			else {
				Report_Params = Report_Params + '&ParamShtrihIsOn=1';
			}
			printBirt({
				'Report_FileName': 'EvnStickPrintFields.rptdesign',
				'Report_Params': Report_Params,
				'Report_Format': 'pdf'
			});
			
			this.hide();
		}
		
	},
	listeners: {
		'beforehide': function()
		{
			var fieldset = Ext.getCmp('ESPW_Fields');
			fieldset.hide();
			
			var evnFieldset = Ext.getCmp('ESPW_PersonEvnsFieldSet');
			evnFieldset.hide();
		}
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	show: function() {
		sw.Promed.swEvnStickPrintWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		
		this.EvnStick_id = null;
		this.evnStickType = null;
		this.PridStickLeaveType_Code = null;
		this.StickOrder_id = null;
		this.StickCause_SysNick = null;
		this.firstEndDate = null;
		this.RegistryESStorage_id = null;
		this.fieldsToPrint = [];
		this.isELN = false;

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].EvnStick_id ) {
			this.EvnStick_id = arguments[0].EvnStick_id;
		}

		if ( arguments[0].evnStickType ) {
			this.evnStickType = arguments[0].evnStickType;
		}

		if ( arguments[0].PridStickLeaveType_Code ) { // код исхода предыдущего ЛВН
			this.PridStickLeaveType_Code = arguments[0].PridStickLeaveType_Code;
		}

		if ( arguments[0].StickOrder_id ) { // порядок выдачи
			this.StickOrder_id = arguments[0].StickOrder_id;
		}

		if ( arguments[0].StickCause_SysNick ) { // Причина нетрудоспособности
			this.StickCause_SysNick = arguments[0].StickCause_SysNick;
		}

		if ( arguments[0].firstEndDate ) { // Дата окончания первой нетрудоспособности указанной организацией из поля "Санаторий" в продолжении ЛВН
			this.firstEndDate = arguments[0].firstEndDate;
		}

		if ( arguments[0].RegistryESStorage_id ) {
			this.RegistryESStorage_id = arguments[0].RegistryESStorage_id;
		}

		this.allowChoseFieldsToPrint = this.StickOrder_id == 2 && this.PridStickLeaveType_Code == 37;
		this.allowPrintEndDate = this.StickOrder_id == 2 && this.PridStickLeaveType_Code == 37 && this.StickCause_SysNick == 'dolsan' && !Ext.isEmpty(this.firstEndDate);
		// запрос инфы о ЛВН
		var url = '/?c=Stick&m=loadEvnStick' + (this.evnStickType == 1 ? '' : 'Dop') + 'EditForm';
		var formPanel = this.findById("ESPW_EvnStickPrintTypePanel");
		var form = this.findById("ESPW_EvnStickPrintTypePanel").getForm();
		var ParamShtrihIsOn = form.findField("ParamShtrihIsOn");
		ParamShtrihIsOn.setValue(true);
		if ( arguments[0].evnStickType ) {
			ParamShtrihIsOn.setValue(!Ext.isEmpty(arguments[0].StickLeaveType_id));
		}

		this.getLoadMask("Загрузка информации о ЛВН...").show();
		
		var evnFieldset = Ext.getCmp('ESPW_PersonEvnsFieldSet');
		evnFieldset.hide();

		form.findField('ParamLeaveTypeIsOn').setContainerVisible(this.allowChoseFieldsToPrint);

		Ext.Ajax.request({
			params: {
				'EvnStick_id': this.EvnStick_id,
				'EvnStick_pid': 0,
				'LoadForPrintStick': 1 // Флаг загрузки различных периодик
			},
			success: function(response, options) {

				this.getLoadMask().hide();
				
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj[0]) {
					response_obj = response_obj[0];
				}
				
				// поля доступные для печати
				if (response_obj.StickCauseDopType_id && response_obj.StickCauseDopType_id != '') {
					this.fieldsToPrint.push('StickCauseDopType');
				}
				
				if (response_obj.StickCause_did && response_obj.StickCause_did != '') {
					this.fieldsToPrint.push('StickCauseDid');
				}
				
				if (response_obj.StickIrregularity_id && response_obj.StickIrregularity_id != '') {
					this.fieldsToPrint.push('Irregularity');
				}

				if ((response_obj.EvnStick_mseDate && response_obj.EvnStick_mseDate != '') ||
					(response_obj.EvnStick_mseRegDate && response_obj.EvnStick_mseRegDate != '') ||
					(response_obj.EvnStick_mseExamDate && response_obj.EvnStick_mseExamDate != '') ||
					(response_obj.EvnStick_IsDisability && response_obj.EvnStick_IsDisability != '')) {
					this.fieldsToPrint.push('MseDates');
				}

				if ((response_obj.EvnStick_stacBegDate && response_obj.EvnStick_stacBegDate != '') ||
					(response_obj.EvnStick_stacEndDate && response_obj.EvnStick_stacEndDate != '')) {
					this.fieldsToPrint.push('StacDates');
				}
				
				if (response_obj.StickCause_Code && response_obj.StickCause_Code.inlist(['03','09', '12'])) {
					if (response_obj.CarePersonCount && response_obj.CarePersonCount > 1) {
						this.fieldsToPrint.push('CarePerson');
					}
				}
				
				if (response_obj.StickCause_Code && response_obj.StickCause_Code.inlist(['08'])) {
					this.fieldsToPrint.push('SstData');
				}
				
				if (response_obj.StickCause_Code && response_obj.StickCause_Code.inlist(['05'])) {
					this.fieldsToPrint.push('Pregnancy');
				}

				if (response_obj.StickLeaveType_id && response_obj.StickLeaveType_id != '') {
					this.fieldsToPrint.push('LeaveType');
				}

				if (response_obj.WorkReleaseCount && response_obj.WorkReleaseCount > 1) {
					this.fieldsToPrint.push('2ndWorkRelease');
				}
				
				if (response_obj.WorkReleaseCount && response_obj.WorkReleaseCount > 2) {
					this.fieldsToPrint.push('3rdWorkRelease');
				}

				if (response_obj.WorkReleaseCount && response_obj.WorkReleaseCount > 3) {
					this.fieldsToPrint.push('4thWorkRelease');
				}
				
				if (response_obj.EvnStickNext_id && response_obj.EvnStickNext_id != '') {
					this.fieldsToPrint.push('NextEvnStickNum');
				}

				if (getRegionNick() == 'kz') {
					this.fieldsToPrint.push('withoutSheetBegin');
				}

				form.findField('EvnStickPrintType_id').getStore().clearFilter();
				form.findField("EvnStickPrintType_id").setValue(1);

				if (Ext.isEmpty(this.RegistryESStorage_id) && response_obj.WorkReleaseCount && response_obj.WorkReleaseCount > 0) {
					if (response_obj.WorkReleaseCountInOwnLpu < 1) {
						// печать не досутпна
						sw.swMsg.alert(lang['soobschenie'], lang['pechat_ne_dostupna_t_k_vse_periodyi_osvobojdeniya_vnesenyi_za_druguyu_mo']);
						this.hide();
						return false;
					}

					if (response_obj.WorkReleaseCountInOwnLpu != response_obj.WorkReleaseCount) {
						// только допечатывание
						form.findField('EvnStickPrintType_id').lastQuery = '';
						form.findField('EvnStickPrintType_id').getStore().filterBy(function(rec) {
							if (rec.get('EvnStickPrintType_id').inlist([2])) {
								return true;
							}
							return false;
						});
						form.findField('EvnStickPrintType_id').setValue(2);
					}
				}

				if (response_obj.PersonEvnRecords) {
					// есть разные периодики
					this.PersonEvnRecords = response_obj.PersonEvnRecords;
				}
				
				if (this.fieldsToPrint.length == 0) {
					form.findField("EvnStickPrintType_id").hideContainer();
				} else {
					form.findField("EvnStickPrintType_id").showContainer();
				}


				evnFieldset.items.each(function(item) {
					var e = item.el.up( '.x-form-item' );
					evnFieldset.remove(item);
					if (e) {
						e.remove();
					}
				});

				var showFieldset = false;
				var first = true;
				
				for (var name in this.PersonEvnRecords) {
					
					// добавить разделитель
					if (!first && this.PersonEvnRecords[name][1]) {
						var hr = new Ext.Component({
						  autoEl: 'hr',
						  style: 'color:#EEE;padding:0px;margin:0px;'
						});
						evnFieldset.add(hr);
					}
					
					first = false;
					
					for (var key in this.PersonEvnRecords[name]) {
						if (this.PersonEvnRecords[name][key].Person_Descr) {
							var hidden = true;
							
							if (this.PersonEvnRecords[name][1]) { // если записей больше двух
								hidden = false;
								showFieldset = true;
							}
							
							var checked = false;
							if (key == 0) {
								checked = true;
							}
							
							var config = {
								hideLabel: true,
								name: name,
								hidden: hidden,
								inputValue: key,
								checked: checked,
								boxLabel: this.PersonEvnRecords[name][key].Person_Descr
							}
							var rb = new Ext.form.Radio(config);
							evnFieldset.add(rb);
							evnFieldset.doLayout();
						}
					}
				}
				
				this.center();
				
				if (showFieldset) {
					// нет отображаемых периодик
					evnFieldset.show();
				}
				//Убрал этот кусок, т.к. появилось еще одно поле https://redmine.swan.perm.ru/issues/51514
				/*else if (this.fieldsToPrint.length == 0) {
					// нет полей для допечати и нет разных периодик, просто печать.
					this.onSelect();
				}*/

				if (getRegionNick() != 'kz' && (response_obj.RegistryESStorage_id && response_obj.RegistryESStorage_id > 0 || response_obj.EvnStickBase_IsFSS)) {
					// сразу печать, отдельная форма
					this.isELN = true;
					this.onSelect();
					this.hide();
					return false;
				}

				var fieldset = this.findById("ESPW_Fields");
				fieldset.hide();
				
				fieldset.items.each(function(item) {
					item.reset();
					item.showContainer();

					if (!item.name.inlist(this.fieldsToPrint)) {
						item.hideContainer();
					}
					
				}.createDelegate(this));

				form.findField("EvnStickPrintType_id").fireEvent('change', form.findField("EvnStickPrintType_id"), form.findField("EvnStickPrintType_id").getValue());

				this.center();
				
			}.createDelegate(this),
			url: url
		});
	}
});