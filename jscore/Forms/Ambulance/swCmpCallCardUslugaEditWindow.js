/**
 * swCmpCallCardUslugaEditWindow - окно редактирования услуг в карте вызова СМП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Ambulance
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			19.12.2014
 */

sw.Promed.swCmpCallCardUslugaEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	width: 600,
	layout: 'form',
	id: 'swCmpCallCardUslugaEditWindow',
	listeners:
	{
		hide: function()
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	CmpCallCard_setDT: null,
	getPayTypeSysNickById: function (id)
	{
		var base_form = this.FormPanel.getForm(),
			PayType = base_form.findField('PayType_id'),
			store = PayType.getStore(),
			recIdx = store.find('PayType_id', id),
			record = store.getAt(recIdx);

		if (record != null)
		{
			return record.get('PayType_SysNick');
		}

		return -1;
	},
	loadUslugaComplexTariff: function() {
		var base_form = this.FormPanel.getForm();

		var date_field = base_form.findField('CmpCallCardUsluga_setDate');
		var lpu_section_id = base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id');
		var PayType_id = base_form.findField('PayType_id').getValue();

		if (
			Ext.isEmpty(date_field)
			|| Ext.isEmpty(base_form.findField('UslugaComplex_id').getValue())
			|| Ext.isEmpty(PayType_id)
			|| Ext.isEmpty(base_form.findField('Person_id').getValue())
		) {
			return;
		}

		base_form.findField('UslugaComplexTariff_id').clearParams();

		if (getRegionNick().inlist(['perm']) && this.getPayTypeSysNickById(PayType_id) == 'ovd')
		{
			PayType_id = 1; // если вид оплаты 9 МВД, то загрузим тарифы как для 1 ОМС #127234
		}

		base_form.findField('UslugaComplexTariff_id').setParams({
			UslugaComplexTariff_Date: Ext.util.Format.date(date_field.getValue(), 'd.m.Y'),
			UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
			PayType_id: PayType_id,
			LpuSection_id: lpu_section_id,
			Person_id: base_form.findField('Person_id').getValue()
		});

		base_form.findField('UslugaComplexTariff_id').params.IsSmp = 1;

		base_form.findField('UslugaComplexTariff_id').loadUslugaComplexTariffList();
	},
	setUslugaComplexCodeList: function() {
		var base_form = this.FormPanel.getForm(),
			CmpCallCardUsluga_setDate = base_form.findField('CmpCallCardUsluga_setDate').getValue(),
			UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue(),
			CmpCallCard_isShortEditVersion = base_form.findField('CmpCallCard_isShortEditVersion').getValue();

		switch ( getRegionNick() ) {
			case 'perm':
			case 'ekb':
				var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Проверка наличия у МО объёма 'СМП_Сокр'..."}),
					codeList = new Array(),
					DateX1 = new Date(2015,5,1),
					DateX2 = new Date(2017,5,1),
					DateX3 = new Date(2018,0,1),
					setDate = base_form.findField('CmpCallCardUsluga_setDate').getValue(),
					UslugaComplexField = base_form.findField('UslugaComplex_id');


				if( Ext.isEmpty(setDate) ) return false;
				 //если полная карта АДИС
				if(CmpCallCard_isShortEditVersion == 1){

					if ( new Date(2015,5,1) <= setDate && setDate <= new Date(2017,11,31) )
					{
						// После 1.06.2015 до 31.12.2017
						codeList.push('B01.044.002.999', 'B01.044.001.999');
					};
					if ( new Date(2015,5,1) <= setDate && setDate <= new Date(2017,5,30) )
					{
						// После 1.06.2015 до 30.06.2017
						codeList.push('A11.12.003.999.001', 'A11.12.003.999.002');
					};
					if ( new Date(2017,6,1) <= setDate && setDate <= new Date(2017,11,31) )
					{
						// После 1.07.2017 до 31.12.2017
						codeList.push('A11.12.003.999.003');
					};
					if ( setDate >=  new Date(2015,5,1) )
					{
						// После 1.06.2015
						codeList.push('B01.044.002', 'B02.001.002', 'B01.044.001', 'B01.031.001', 'B01.003.001', 'B01.032.001', 'B01.023.001',
							'B01.015.001', 'A23.30.042.002');
					};
					if ( setDate >= new Date(2018,0,1) )
					{
						// После 1.01.2018
						codeList.push('B01.044.002.999', 'B01.044.001.999', 'A11.12.003.002.001');
					};
					
					// Услуги COVID-19 после 1.04.2020
					if ( setDate >= new Date(2020,3,1) && getRegionNick() === 'perm' )
					{
						codeList.push('B01.044.002.1', 'B02.001.002.1', 'B01.044.001.1','B01.031.001.1','B01.003.001.1',
							'B01.023.001.1','B01.015.001.1','A23.30.042.002.1');
						
					}
				}
				else {
					//если сокращенная карта АДИС

					// После 1 янв 2018 коды услуг меняются
					if ( setDate >= DateX3)
					{
						codeList = ['B01.023.001.001',  'B01.023.001.999', 'A23.30.042.002', 'B01.032.001.001'];
						if ( getRegionNick().inlist(['perm']) )
						{
							codeList.push('A23.30.042.001');
						}
					} else {
						codeList = [ 'B01.023.001.999.001', 'B01.032.001.999.001', 'A23.30.042.002' ];
					}

					// Услуги COVID-19 после 1.04.2020
					if ( setDate >= new Date(2020,3,1) && getRegionNick() === 'perm' )
					{
						codeList.push('B01.044.002.1', 'B02.001.002.1', 'B01.044.001.1','B01.031.001.1','B01.003.001.1',
							'B01.023.001.1','B01.015.001.1','A23.30.042.002.1');

					}
				};

				UslugaComplexField.setUslugaComplexCodeList(codeList);
				UslugaComplexField.setUslugaComplexDate(typeof CmpCallCardUsluga_setDate == 'object' ? Ext.util.Format.date(CmpCallCardUsluga_setDate, 'd.m.Y') : CmpCallCardUsluga_setDate);

				UslugaComplexField.getStore().load({
					callback: function() {
						UslugaComplexField.clearValue();

						if ( !Ext.isEmpty(UslugaComplex_id) ) {
							var index = UslugaComplexField.getStore().findBy(function(rec) {
								return (rec.get('UslugaComplex_id') == UslugaComplex_id);
							});

							if ( index >= 0 ) {
								UslugaComplexField.setValue(UslugaComplex_id);
								UslugaComplexField.fireEvent('select', UslugaComplexField, UslugaComplexField.getStore().getAt(index));
							}
						}
					}
				});
					 /*
					 //Проверяем наличие у МО вида обёма "СМП_Сокр"
					 Ext.Ajax.request({
						 callback: function(options, success, response) {
							 loadMask.hide();
							 if ( success ) {
								 var response_obj = Ext.util.JSON.decode(response.responseText);
								 if ( response_obj.success == false ) {
									 sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : "Ошибка при проверке наличия у МО объёма 'СМП_Сокр'");
								 }
								 else {
									 if ( response_obj.length ) {

										 // После 1 янв 2018 коды услуг меняются
										 if ( ! Ext.isEmpty(setDate) && setDate >= DateX3)
										 {
											 codeList = ['B01.023.001.001',  'B01.023.001.999', 'A23.30.042.002', 'B01.032.001.001'];
											 if ( getRegionNick().inlist(['perm']) )
											 {
												 codeList.push('A23.30.042.001');
											 }
										 } else {
											 codeList = [ 'B01.023.001.999.001', 'B01.032.001.999.001', 'A23.30.042.002' ];
										 }

									 }
									 else {
										 if ( !Ext.isEmpty(setDate) && setDate >= DateX1 ) {
											 codeList = [
												 'B01.044.002', 'B02.001.002', 'B01.044.001', 'B01.031.001', 'B01.003.001', 'B01.032.001', 'B01.023.001',
												 'B01.015.001', 'B01.044.002.999', 'B01.044.001.999', 'A23.30.042.002'
											 ];

											 if ( setDate >= DateX2 ) {
												 if ( setDate < DateX3 )
												 {	// до 31 дек 2017 включительно
													 codeList.push('A11.12.003.999.003');
												 } else
												 {
													 //Начиная с 1 янв 2018
													 codeList.push('A11.12.003.002.001');
												 }
											 }
											 else {
												 codeList.push('A11.12.003.999.001');
												 codeList.push('A11.12.003.999.002');
											 }
										 }
										 else {
											 codeList = [ 'A11.12.003.999.001', 'A11.12.003.999.002' ];
										 }
									 }

									 base_form.findField('UslugaComplex_id').setUslugaComplexCodeList(codeList);
									 base_form.findField('UslugaComplex_id').setUslugaComplexDate(typeof CmpCallCardUsluga_setDate == 'object' ? Ext.util.Format.date(CmpCallCardUsluga_setDate, 'd.m.Y') : CmpCallCardUsluga_setDate);

									 base_form.findField('UslugaComplex_id').getStore().load({
										 callback: function() {
											 base_form.findField('UslugaComplex_id').clearValue();

											 if ( !Ext.isEmpty(UslugaComplex_id) ) {
												 var index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
													 return (rec.get('UslugaComplex_id') == UslugaComplex_id);
												 });

												 if ( index >= 0 ) {
													 base_form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
													 base_form.findField('UslugaComplex_id').fireEvent('select', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getStore().getAt(index));
												 }
											 }
										 }
									 });
								 }
							 }
							 else {
								 sw.swMsg.alert('Ошибка', "Ошибка при проверке наличия у МО объёма 'СМП_Сокр'");
							 }
						 }.createDelegate(this),
						 url: '/?c=TariffVolumes&m=checkLpuHasSmpSokrVolume'
					 });
					 */


			break;

			case 'astra': // Астрахань
				base_form.findField('UslugaComplex_id').setUslugaComplexCodeList(['A11.12.003.002']);
			break;

			case 'ufa': // Уфа
			break;

			case 'kareliya': // Карелия
				var DateX = new Date(2017,5,1);

				if ( Ext.isEmpty(CmpCallCardUsluga_setDate) || CmpCallCardUsluga_setDate < DateX ) {
					base_form.findField('UslugaComplex_id').setUslugaComplexCodeList(['A11.12.003.002', 'A25.30.036.001', 'A25.30.036.002']);
				}
				else {
					base_form.findField('UslugaComplex_id').setUslugaComplexCodeList(['A25.30.036.001', 'A25.30.036.002']);
				}

				base_form.findField('UslugaComplex_id').getStore().load({
					callback: function() {
						base_form.findField('UslugaComplex_id').clearValue();

						if ( !Ext.isEmpty(UslugaComplex_id) ) {
							var index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
								return (rec.get('UslugaComplex_id') == UslugaComplex_id);
							});

							if ( index >= 0 ) {
								base_form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
								base_form.findField('UslugaComplex_id').fireEvent('select', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getStore().getAt(index));
							}
						}
					}
				});
			break;
			
			case 'buryatiya': // Бурятия
				base_form.findField('UslugaComplex_id').setUslugaComplexCodeList(['021301','080005','180005']);
			break;
			
			case 'krym':
				base_form.findField('UslugaComplex_id').setUslugaComplexCodeList(['A11.12.003.002']);
			break;

			case 'penza':
				base_form.findField('UslugaComplex_id').setUslugaComplexCodeList(['A11.12.003.002']);
			break;

			default: 
				base_form.findField('UslugaComplex_id').setUslugaComplexCodeList(['A11.12.003.999.001', 'A11.12.003.999.002']);
				base_form.findField('UslugaComplex_id').getStore().load({
					callback: function() {
						base_form.findField('UslugaComplex_id').clearValue();

						if ( !Ext.isEmpty(UslugaComplex_id) ) {
							var index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
								return (rec.get('UslugaComplex_id') == UslugaComplex_id);
							});

							if ( index >= 0 ) {
								base_form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
								base_form.findField('UslugaComplex_id').fireEvent('select', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getStore().getAt(index));
							}
						}
					}
				});
			break;
		}

		return true;
	},
	//Проверяем, была ли выполнена услуга позднее, чем 24 часа и раньше даты и времени приема;
	checkUslugaTime: function() {
		
		if ( (typeof this.CmpCallCard_setDT !== 'object') || (typeof this.CmpCallCard_setDT.add !== 'function') ) {
			return false;
		}
		
		var base_form = this.FormPanel.getForm();
		var uslugaDT = Date.parseDate( base_form.findField('CmpCallCardUsluga_setDate').getRawValue() + ' ' + base_form.findField('CmpCallCardUsluga_setTime').getRawValue() , 'd.m.Y H:i' , true);

		// Убрал секунды из даты-времени приема вызова
		// @task https://redmine.swan.perm.ru/issues/98349
		var minDate = this.CmpCallCard_setDT.add(Date.SECOND, -this.CmpCallCard_setDT.getSeconds());
		var maxDate = this.CmpCallCard_setDT.add(Date.HOUR,24);

		return (uslugaDT >= minDate) && (uslugaDT <= maxDate);
		
	},
	doSave: function(options)
	{
		
		//Для Перми проверяем, что услуга выполнена в предыдущие 24 часа
		if (getGlobalOptions().region.number == 59) {
			
			if ( !this.checkUslugaTime() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function()
					{
						this.FormPanel.getForm().findField('CmpCallCardUsluga_setTime').focus(true);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['usluga_doljna_byit_vyipolnena_ne_ranshe_datyi_i_vremeni_priema_i_ne_pozdnee_chem_24_chasa'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			
		}
		
		options = options || {};
		var base_form = this.FormPanel.getForm();
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit(options);
		return true;
	},
	submit: function(options)
	{
		var base_form = this.FormPanel.getForm();

		base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'));

		var params = new Object();
		
		var usluga_complex_store = base_form.findField('UslugaComplex_id').getStore(),
			usluga_complex_rec_num = usluga_complex_store.findBy(function(rec) { return rec.get('UslugaComplex_id') == base_form.findField('UslugaComplex_id').getValue(); }),
			usluga_complex_rec = usluga_complex_store.getAt(usluga_complex_rec_num);
			
		if (usluga_complex_rec) {
			params.UslugaComplex_Code = usluga_complex_rec.get('UslugaComplex_Code');
			params.UslugaComplex_Name = usluga_complex_rec.get('UslugaComplex_Name');
		}

		var result = base_form.getValues();
		
		for (var key in params) {
			if (params.hasOwnProperty(key)) {
				result[key] = params[key];
			}
		}
		
		if (typeof this.callback == 'function') {
			this.callback(result);
			this.hide();
		}
		
		
		// var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		// loadMask.show();
		// base_form.submit({
		// 	params: params,
		// 	failure: function(result_form, action)
		// 	{
		// 		loadMask.hide();
		// 		if (action.result){
		// 			if (action.result.Alert_Msg) {
		// 				sw.swMsg.show({
		// 					buttons: Ext.Msg.YESNO,
		// 					fn: function(buttonId, text, obj) {
		// 						if ( buttonId == 'yes' ) {
		// 							options.ignoreUslugaComplexTariffCountCheck = 1;

		// 							this.doSave(options);
		// 						}
		// 					}.createDelegate(this),
		// 					icon: Ext.MessageBox.QUESTION,
		// 					msg: action.result.Alert_Msg,
		// 					title: 'Продолжить сохранение?'
		// 				});
		// 			}
		// 			/*if (action.result.Error_Code){
		// 				Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
		// 			}*/
		// 		}
		// 	}.createDelegate(this),
		// 	success: function(result_form, action)
		// 	{
		// 		loadMask.hide();
		// 		if (action.result){
		// 			if (action.result.CmpCallCardUsluga_id ){
		// 				this.callback();
		// 				this.hide();
		// 			} else {
		// 				sw.swMsg.show({
		// 					buttons: Ext.Msg.OK,
		// 					fn: function()
		// 					{
		// 						this.hide();
		// 					},
		// 					icon: Ext.Msg.ERROR,
		// 					msg: 'Произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
		// 					title: 'Ошибка'
		// 				});
		// 			}
		// 		}
		// 	}.createDelegate(this)
		// });
	},

	show: function()
	{
		sw.Promed.swCmpCallCardUslugaEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = this.FormPanel.getForm();

		this.MedPersonal_id = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.action = 'view';

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].MedPersonal_id) {
			this.MedPersonal_id = arguments[0].MedPersonal_id;
		}

		base_form.reset();
		base_form.setValues(arguments[0].formParams);

		var pay_type_combo = base_form.findField('PayType_id');
		var usluga_category_combo = base_form.findField('UslugaCategory_id');
		var usluga_complex_combo = base_form.findField('UslugaComplex_id');	
		
		this.CmpCallCard_setDT = arguments[0]['CmpCallCard_setDT'];

		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['vyipolnenie_uslugi_dobavlenie']);
				this.enableEdit(true);

				pay_type_combo.setFieldValue('PayType_SysNick', 'oms');
				
				var defaultUslugaCategoty = 'gost2011';
				
				if (getGlobalOptions().region.nick == 'buryatiya') {//#69826
					defaultUslugaCategoty = 'tfoms';
				}
				
				usluga_category_combo.setFieldValue('UslugaCategory_SysNick', defaultUslugaCategoty);
				
				/*if ( Ext.isEmpty(usluga_category_combo.getValue()) ) {
					usluga_category_combo.setFieldValue('UslugaCategory_SysNick', 'gost2011');
				}*/
				base_form.findField('CmpCallCardUsluga_Kolvo').setValue(1);

				base_form.findField('CmpCallCardUsluga_setDate').fireEvent('change', base_form.findField('CmpCallCardUsluga_setDate'), base_form.findField('CmpCallCardUsluga_setDate').getValue());

				if ( !Ext.isEmpty(this.MedPersonal_id) ) {
					var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
						return (rec.get('MedPersonal_id') == this.MedPersonal_id);
					}.createDelegate(this));
					
					if ( index >= 0 ) {
						base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
					}
				}

				usluga_complex_combo.setPayType(pay_type_combo.getValue());
				usluga_complex_combo.setUslugaCategoryList([usluga_category_combo.getFieldValue('UslugaCategory_SysNick')]);
			break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['vyipolnenie_uslugi_redaktirovanie']);
					this.enableEdit(true);
				} else {
					this.setTitle(lang['vyipolnenie_uslugi_redaktirovanie']);
					this.enableEdit(false);
				}

				var cmp_call_card_usluga_id = base_form.findField('CmpCallCardUsluga_id').getValue();

				var
					UslugaComplex_id = usluga_complex_combo.getValue(),
					UslugaComplexTariff_id = base_form.findField('UslugaComplexTariff_id').getValue();

				usluga_complex_combo.setPayType(pay_type_combo.getValue());
				usluga_complex_combo.setUslugaCategoryList([usluga_category_combo.getFieldValue('UslugaCategory_SysNick')]);

				base_form.findField('CmpCallCardUsluga_setDate').fireEvent('change', base_form.findField('CmpCallCardUsluga_setDate'), base_form.findField('CmpCallCardUsluga_setDate').getValue());

				//После предыдущих действий UslugaComplexTariff_id затирается
				base_form.findField('UslugaComplexTariff_id').setValue(UslugaComplexTariff_id);
				this.loadUslugaComplexTariff();
				
				// win.getLoadMask(LOAD_WAIT).show();
				// base_form.load({
				// 	url: '/?c=CmpCallCard&m=loadCmpCallCardUslugaForm',
				// 	params: {CmpCallCardUsluga_id: cmp_call_card_usluga_id},
				// 	success: function ()
				// 	{
				// 		win.getLoadMask().hide();

				// 	}.createDelegate(this),
				// 	failure: function ()
				// 	{
				// 		win.getLoadMask().hide();
				// 		Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
				// 	}
				// });
			break;
		}

	},
	initComponent: function()
	{
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'CCCUEW_StorageForm',
			labelAlign: 'right',
			labelWidth: 120,
			items: [{
				xtype: 'hidden',
				name: 'CmpCallCardUsluga_id'
			}, {
				xtype: 'hidden',
				name: 'CmpCallCard_id'
			}, {
				name: 'MedPersonal_id',
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'Person_id'
			}, {
				xtype: 'hidden',
				name: 'CmpCallCard_isShortEditVersion'
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						allowBlank: false,
						xtype: 'swdatefield',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						id: 'CCCUE_CmpCallCardUsluga_setDate',
						name: 'CmpCallCardUsluga_setDate',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();
								//this.loadUslugaComplexTariff();

								// Загрузка списка мест работы
								var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
								base_form.findField('MedStaffFact_id').clearValue();

								var msfFilterParams = {
									allowLowLevel: 'yes',
									withoutLpuSection: true
								};

								if ( !Ext.isEmpty(newValue) ) {
									msfFilterParams.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
								}

								base_form.findField('MedStaffFact_id').getStore().removeAll();

								setMedStaffFactGlobalStoreFilter(msfFilterParams);

								base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

								if ( !Ext.isEmpty(MedStaffFact_id) ) {
									base_form.findField('MedStaffFact_id').setFieldValue('MedStaffFact_id', MedStaffFact_id);
									//base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
								}

								this.setUslugaComplexCodeList();
							}.createDelegate(this)
						},
						fieldLabel: lang['data_vyipolneniya']
					}]
				}, {
					layout: 'form',
					labelWidth: 49,
					items: [{
						allowBlank: false,
						xtype: 'swtimefield',
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						name: 'CmpCallCardUsluga_setTime',
						fieldLabel: lang['vremya'],
						onTriggerClick: function() {
							var base_form = this.FormPanel.getForm();

							var time_field = base_form.findField('CmpCallCardUsluga_setTime');
							var date_field = base_form.findField('CmpCallCardUsluga_setDate');

							setCurrentDateTime({
								dateField: date_field,
								loadMask: true,
								setDate: true,
								setDateMaxValue: false,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: 'swCmpCallCardUslugaEditWindow',
								callback: function() {
									time_field.validate();
									date_field.fireEvent('change', date_field, date_field.getValue());
								}
							});
						}.createDelegate(this),
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						width: 60
					}]
				}]
			}, {
				allowBlank: false,
				dateFieldId: 'CCCUE_CmpCallCardUsluga_setDate',
				enableOutOfDateValidation: true,
				hiddenName: 'MedStaffFact_id',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						this.loadUslugaComplexTariff();
					}.createDelegate(this)
				},
				listWidth: 650,
				width: 400,
				xtype: 'swmedstafffactglobalcombo'
			}, {
				allowBlank: false,
				xtype: 'swpaytypecombo',
				hiddenName: 'PayType_id',
				fieldLabel : lang['vid_oplatyi'],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm(),
							UslugaComplexTariff = base_form.findField('UslugaComplexTariff_id');

						if ( Ext.isEmpty(newValue) ) {
							base_form.findField('UslugaComplex_id').setPayType();
						} else {
							base_form.findField('UslugaComplex_id').setPayType(newValue);
						}

						this.loadUslugaComplexTariff();

						if ( getRegionNick().inlist(['perm']) && this.getPayTypeSysNickById(newValue) == 'oms')
						{
							UslugaComplexTariff.setAllowBlank(false);
						} else
						{
							UslugaComplexTariff.setAllowBlank(true);
						}

					}.createDelegate(this),
					select: function (combo, rec) {
						var base_form = this.FormPanel.getForm(),
							data = rec.data;

						base_form.findField('UslugaComplex_id').store.baseParams.PayType_id = data.PayType_id;
						base_form.findField('UslugaComplex_id').store.load();
					}.createDelegate(this)
				},
				width: 250
			}, {
				allowBlank: false,
				fieldLabel: lang['kategoriya_uslugi'],
				hiddenName: 'UslugaCategory_id',
				listeners: {
					'change': function (combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						base_form.findField('UslugaComplex_id').clearValue();
						base_form.findField('UslugaComplex_id').getStore().removeAll();

						if ( Ext.isEmpty(newValue) ) {
							base_form.findField('UslugaComplex_id').setUslugaCategoryList();
						} else {
							base_form.findField('UslugaComplex_id').setUslugaCategoryList([combo.getFieldValue('UslugaCategory_SysNick')]);
						}
					}.createDelegate(this)
				},
				listWidth: 400,
				width: 250,
				xtype: 'swuslugacategorycombo'
			}, {
				allowBlank:false,
				xtype: 'swuslugacomplexnewcombo',
				hiddenName: 'UslugaComplex_id',
				fieldLabel: lang['usluga'],
				listWidth: 450,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						this.loadUslugaComplexTariff();
					}.createDelegate(this)
				},
				width: 400
			}, {
				//allowBlank: !inlist(getGlobalOptions().region.number,[59]),
				xtype: 'swuslugacomplextariffcombo',
				hiddenName: 'UslugaComplexTariff_id',
				isAllowSetFirstValue: true,
				listeners: {
					'select': function (combo, record, index) {
						combo.fireEvent('change', combo, record.get('UslugaComplexTariff_id'));
					}.createDelegate(this),
					'change': function (combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						if (!Ext.isEmpty(newValue)) {
							base_form.findField('CmpCallCardUsluga_Cost').setValue(combo.getFieldValue('UslugaComplexTariff_Tariff'));
						} else {
							base_form.findField('CmpCallCardUsluga_Cost').setValue(null);
						}
					}.createDelegate(this)
				},
				listWidth: 600,
				width: 400
			}, {
				allowNegative: false,
				xtype: 'numberfield',
				name: 'CmpCallCardUsluga_Cost',
				fieldLabel: lang['tsena']
			}, {
				allowNegative: false,
				allowDecimal: false,
				xtype: 'numberfield',
				name: 'CmpCallCardUsluga_Kolvo',
				fieldLabel: lang['kolichestvo']
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//
				}
			},
			[
				{name: 'CmpCallCardUsluga_id'},
				{name: 'CmpCallCard_id'},
				{name: 'CmpCallCardUsluga_setDate'},
				{name: 'CmpCallCardUsluga_setTime'},
				{name: 'MedPersonal_id'},
				{name: 'MedStaffFact_id'},
				{name: 'PayType_id'},
				{name: 'UslugaCategory_id'},
				{name: 'UslugaComplex_id'},
				{name: 'UslugaComplexTariff_id'},
				{name: 'CmpCallCardUsluga_Cost'},
				{name: 'CmpCallCardUsluga_Kolvo'},
				{name: 'Person_id'}
			]),
			url: '/?c=CmpCallCard&m=saveCmpCallCardUsluga'
		});

		Ext.apply(this, {
			buttons:
			[{
				handler: function()
				{
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}],
			items: [this.FormPanel]
		});
		sw.Promed.swCmpCallCardUslugaEditWindow.superclass.initComponent.apply(this, arguments);
	}
});