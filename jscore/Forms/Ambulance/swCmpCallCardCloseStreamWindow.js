/**
* swCmpCallCardCloseStreamWindow - окно поточного ввода карты вызова
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author		Popkov
* @version      май.2014
*/
/*NO PARSE JSON*/

sw.Promed.swCmpCallCardCloseStreamWindow = Ext.extend(sw.Promed.BaseForm, swod = {
	// Базовые атрибуты класса
	objectName: 'swCmpCallCardCloseStreamWindow',
	objectSrc: '/jscore/Forms/Ambulance/swCmpCallCardCloseStreamWindow.js',
	modal: true,
	plain: true,
	resizable: false,
	codeRefresh: true,
	layout: 'fit',
	// -/-

	// Дополнительные атрибуты класса
	allfields: null,
	action: null,
	callback: Ext.emptyFn,
	formStatus: 'edit',
	onCancelAction: Ext.emptyFn,
	onHide: Ext.emptyFn,
	diagFinanceConfirm: false,
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = this;
			var current_tab = this.tabPanel;
			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;
				case Ext.EventObject.E:
					//current_tab.setactive(pnum, id)
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.E,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	listeners: {
		beforehide: function(win) {
			win.onCancelAction();
		},
		hide: function(win) {
			win.onHide();
		},
		maximize: function(win) {
			win.doLayout();
		},
		restore: function(win) {
			win.fireEvent('maximize', win);
		}
	},

	hasUslugaViewFrame: function() {
		return true; // getRegionNick().inlist([ 'buryatiya', 'kareliya' ]);
	},

	getCmpCallCardNumber: function() {
		var base_form = this.FormPanel.getForm();

		this.showLoadMask(LOAD_WAIT);

		Ext.Ajax.request({
			callback: function(opt, success, response) {
				this.hideLoadMask();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					base_form.findField('Year_num').setValue(response_obj[0].CmpCallCard_Ngod);
					base_form.findField('Day_num').setValue(response_obj[0].CmpCallCard_Numv);
					base_form.findField('Day_num').focus(true);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_vyizova']);
				}
			}.createDelegate(this),
			url: '/?c=CmpCallCard&m=getCmpCallCardNumber'
		});
	},

	personSearch: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		var base_form = this.FormPanel.getForm();

		var autoSearchFlag = ( base_form.findField('Name').getValue()!='' || base_form.findField('Middle').getValue()!='' || base_form.findField('Fam').getValue()!='' );

		var parentObject = this;
		getWnd('swPersonSearchWindow').show({
			autoSearch: autoSearchFlag,
			getPersonWorkFields: true,
			onClose: Ext.emptyFn,
			onSelect: function(person_data){
				with(base_form) {
					findField('Person_id').setValue(person_data.Person_id);

					// Наименования из формы поиска приходят с разным регистром
					// в зависимости от того: добавили или выбрали человека
					findField('Name').setValue( person_data.Person_FirName || person_data.Person_Firname );
					findField('Name').getEl().dom.setAttribute('readOnly', true);
					findField('Middle').setValue( person_data.Person_SecName || person_data.Person_Secname );
					findField('Middle').getEl().dom.setAttribute('readOnly', true);
					findField('Fam').setValue( person_data.Person_SurName || person_data.Person_Surname );
					findField('Fam').getEl().dom.setAttribute('readOnly', true);
					findField('PolisSerial').setValue( person_data.Polis_Ser);
					findField('PolisSerial').getEl().dom.setAttribute('readOnly', true);
					findField('PolisNum').setValue( person_data.Polis_Num );
					findField('PolisNum').getEl().dom.setAttribute('readOnly', true);
					findField('EdNum').setValue( person_data.Polis_EdNum );
					findField('EdNum').getEl().dom.setAttribute('readOnly', true);
					findField('DocumentNum').setValue( ((person_data.Document_Ser != null)?person_data.Document_Ser:'') + ' ' +  ((person_data.Document_Num)?person_data.Document_Num:''));
					findField('DocumentNum').getEl().dom.setAttribute('readOnly', true);
					findField('Work').setValue( person_data.Person_Work );
					findField('Work').getEl().dom.setAttribute('readOnly', true);
					findField('Sex_id').setValue( person_data.PersonSex_id || person_data.Sex_id );
					findField('Sex_id').getEl().dom.setAttribute('readOnly', true);
					//findField('Age').setValue( swGetPersonAge( person_data.Person_BirthDay || person_data.Person_Birthday, new Date()) );
					// Выбираем ед.измерения в годах
					//Ext.getCmp('CMPCLOSE_CB_219').setValue(true);
				}

				var bth = person_data.Person_BirthDay || person_data.Person_Birthday;
				if (bth != null && bth != '') {
					var acceptDate = new Date();
					if (base_form.findField('AcceptTime').getStringValue() != '') {
						acceptDate = Date.parseDate( base_form.findField('AcceptTime').getStringValue(), 'd.m.Y H:i' );
					}

					var b_days = Math.floor(swGetPersonAgeDay(bth, acceptDate));
					var b_month = swGetPersonAgeMonth(bth, acceptDate);
					var b_year = swGetPersonAge(bth, acceptDate);

					if (b_days >= 0 && b_days <= 30) {
						//дни
						base_form.findField('Age').setValue(b_days);
						Ext.getCmp('CMPCLOSE_CB_219').setValue(false);
						Ext.getCmp('CMPCLOSE_CB_220').setValue(false);
						Ext.getCmp('CMPCLOSE_CB_221').setValue(true);
					}
					if (b_days > 30 && b_year == 0) {
						//Месяцы
						base_form.findField('Age').setValue(b_month);
						Ext.getCmp('CMPCLOSE_CB_219').setValue(false);
						Ext.getCmp('CMPCLOSE_CB_220').setValue(true);
						Ext.getCmp('CMPCLOSE_CB_221').setValue(false);
					}
					if (b_year > 0) {
						//Годы
						base_form.findField('Age').setValue(b_year);
						Ext.getCmp('CMPCLOSE_CB_219').setValue(true);
						Ext.getCmp('CMPCLOSE_CB_220').setValue(false);
						Ext.getCmp('CMPCLOSE_CB_221').setValue(false);
					}
				}

				base_form.findField('Age').getEl().dom.setAttribute('readOnly', true);
				getWnd('swPersonSearchWindow').hide();
			},
			personFirname: base_form.findField('Name').getValue(),
			personSecname: base_form.findField('Middle').getValue(),
			personSurname: base_form.findField('Fam').getValue(),
			searchMode: 'all'
		});
	},

	personUnknown: function() {
		this.personReset();
		var base_form = this.FormPanel.getForm();
		base_form.findField('Fam').setValue(lang['neizvesten']);
		base_form.findField('Name').setValue(lang['neizvesten']);
		base_form.findField('Middle').setValue(lang['neizvesten']);
		Ext.getCmp('psocial').allowBlank = true;

		//вся штука ниже на сброс радиобаттона
		if(getGlobalOptions().region.nick == 'kareliya')
		{
			Ext.getCmp('CMPCLOSE_CB_219').setValue(false);
			Ext.getCmp('CMPCLOSE_CB_220').setValue(false);
			Ext.getCmp('CMPCLOSE_CB_221').setValue(false);
		}

		base_form.findField('Sex_id').allowBlank = true;
		base_form.findField('Age').allowBlank = true;

		//base_form.findField('Fam').disable();
		//base_form.findField('Name').disable();
		//base_form.findField('Middle').disable();
	},

	personReset: function() {
		if ( this.action == 'view' ) {
			return false;
		}
		var base_form = this.FormPanel.getForm(),
			fields = ['Fam','Name','Middle','Sex_id','Age'];

		for(var i=0; i<fields.length; i++) {
			base_form.findField(fields[i]).getEl().dom.removeAttribute('readOnly');
			base_form.findField(fields[i]).reset();
		}
		Ext.getCmp('CMPCLOSE_CB_219').enable();
		Ext.getCmp('CMPCLOSE_CB_219').setValue(true);
		Ext.getCmp('CMPCLOSE_CB_220').enable();
		Ext.getCmp('CMPCLOSE_CB_220').setValue(false);
		Ext.getCmp('CMPCLOSE_CB_221').enable();
		Ext.getCmp('CMPCLOSE_CB_221').setValue(false);
		base_form.findField('Sex_id').allowBlank = false;
		base_form.findField('Age').allowBlank = false;
	},
	save_form: function( base_form, params_out){
		
		//console.log('params_out', params_out); return;
		this.showLoadMask(lang['podojdite_idet_zakryitie_kartyi_vyizova']);

		base_form.submit({
			failure: function(result_form, action) {
				this.hideLoadMask();
				this.formStatus = 'edit';
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_zakryitii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_zakryitii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this),
			params: params_out,
			success: function(result_form, action) {
				this.hideLoadMask();
				this.formStatus = 'edit';

				if ( action.result ) {
					if ( action.result.CmpCloseCard_id > 0 ) {
						this.callback({
							CmpCloseCard_id: action.result.CmpCloseCard_id
						});
						this.hide();

						if (this.action == 'stream') {
							this.show();

							var mb = Ext.Msg.show({
								title: lang['soobschenie'],
								msg: lang['karta_vyizova_sohranena'],
								icon: Ext.Msg.INFO,
								buttons: false,
								modal: false,
								animEl: this
							});
							setTimeout(function () {
								mb.hide();
							}.createDelegate(this), 1000);
						}
					} else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},


	selectLpuTransmit: function(CmpLpuId) {

		var base_form = this.FormPanel.getForm();

		comboLpuTrnsimit = base_form.findField('Lpu_ppdid');
		emptyRecord = new Ext.data.Record({
			Lpu_id: null,
			Lpu_Name: '',
			Lpu_Nick: '&nbsp'
		});
		//убираем возникновение &nbsp
		if(comboLpuTrnsimit.getValue() == null)
			{
				comboLpuTrnsimit.setValue('');
			}
		//при первой загрузке и вьюхе не затираем поле лпу передачи, но вставляем пустое поле
		if ( (this.action == 'view') ||
			(CmpLpuId == 'first') && (this.action == 'edit') )
		{
			//this.FormPanel.getForm().findField('Lpu_ppdid').setDisabled(true);
			if (comboLpuTrnsimit.getStore().getAt(0))
			{if ( (comboLpuTrnsimit.getStore().getAt(0).get('Lpu_id') != '' ) && (comboLpuTrnsimit.getStore().getAt(0).get('Lpu_id') != null ) )
			{
				comboLpuTrnsimit.getStore().insert(0, [emptyRecord]);
			}}
			return false;
		}

		var	cmpFieldReasonCode = base_form.findField('CmpReason_id'),
			store = cmpFieldReasonCode.getStore(),
			flagOrArrayCodesIsNMP = false,
			value = cmpFieldReasonCode.getValue(),
			idx = store.findBy(function(rec) { return rec.get('CmpReason_id') == value; });


		if(idx != -1 && idx != undefined)
		{
			var	code = store.getAt(idx).get('CmpReason_Code');
			flagOrArrayCodesIsNMP = code.inlist(['04Г','04Д','09Я','11Л','11Я','12Г','12К','12Р','12У','12Э','12Я','13Л','13М','15Н','17А','13С','40Ц']);
		}

		//this.FormPanel.getForm().findField('CmpReason_id').getValue().inlist([541, 542, 595, 606, 609, 613, 616, 618, 619, 620, 621, 629, 630, 644, 632, 689])&&

		//поводы для передачи в ППД 12Я; 12Э; 12У; 12Р; 12К; 12Г; 13Л; 11Я; 11Л; 04Д; 04Г; 13М; 09Я; 15
		if	(flagOrArrayCodesIsNMP &&
			(this.FormPanel.getForm().findField('Age').getValue()>0)&&
			(this.FormPanel.getForm().findField('Person_id').getValue() !=0 ) )
			{
				this.FormPanel.getForm().findField('Lpu_ppdid').setDisabled(false);
				this.setLpuAddrLoad();
			}
			else {
				this.FormPanel.getForm().findField('Lpu_ppdid').setDisabled(true);
				this.FormPanel.getForm().findField('Lpu_ppdid').setValue('');
				return false;
			}
		//if (!CmpLpuId || this.FormPanel.getForm().findField('Lpu_ppdid').getStore().find('Lpu_ppdid', CmpLpuId, 0, false)== -1) {
			//this.FormPanel.getForm().findField('Lpu_ppdid').setValue('');
		//	return true;
		//	}
		//this.FormPanel.getForm().findField('Lpu_ppdid').setValue(CmpLpuId);
	},


	setLpuAddrLoad: function(){

		var base_form = this.FormPanel.getForm();

		comboLpuTrnsimit = base_form.findField('Lpu_ppdid');

		emptyRecord = new Ext.data.Record({
			Lpu_id: null,
			Lpu_Name: '',
			Lpu_Nick: '&nbsp'
		});

		record = new Ext.data.Record({
			Lpu_id: '0',
			Lpu_Name: '',
			Lpu_Nick: lang['pokazat_vse']
		});
		//если у карты вызова есть значение (при загрузке), то не загружаем значения
		//если карта вызова редактируется или просматривается, то не загружаем
		//а должно быть - если загружаем и значение есть - выводим загруженное значение + остальные
		//если просматриваем, то


		//проверяем - формализованный ли адрес
		if  (base_form.findField('Street_id').getValue() != '')
		{
			//если у нас что-то выбрано - ведем поиск по лпу, ищем по адресу вызова
			if (
				(base_form.findField('KLAreaStat_idEdit').getValue() != '' ) ||
				(base_form.findField('Area_id').getValue() != '' )	||
				(base_form.findField('City_id').getValue() != '' )	||
				(base_form.findField('Town_id').getValue() != '' )	||
				(base_form.findField('House').getValue() != '' )
				)
			 {
				this.FormPanel.getForm().findField('Lpu_ppdid').setValue('');
				base_form.findField('Lpu_ppdid').getStore().removeAll();
				base_form.findField('Lpu_ppdid').getStore().load({
					params: {
						Object: 'LpuWithMedServ',
						comAction: 'CallAddress',
						MedServiceType_id: 18,
						KLAreaStat_idEdit: base_form.findField('KLAreaStat_idEdit').getValue(),
						KLSubRgn_id: base_form.findField('Area_id').getValue(),
						KLCity_id: base_form.findField('City_id').getValue(),
						KLTown_id: base_form.findField('Town_id').getValue(),
						KLStreet_id: base_form.findField('Street_id').getValue(),
						CmpCallCard_Dom: base_form.findField('House').getValue(),
						Person_Age: base_form.findField('Age').getValue()
					},
					callback :function(){
						if (comboLpuTrnsimit.getStore().getCount() == 1)
						{
							var recordSelected = comboLpuTrnsimit.getStore().getAt(0);
							comboLpuTrnsimit.setValue(recordSelected.get('Lpu_id'));
						}
						comboLpuTrnsimit.getStore().add([record]);
						comboLpuTrnsimit.getStore().insert(0, [emptyRecord]);
						comboLpuTrnsimit.getStore().commitChanges();
				   }
			 });


			 }
			 //иначе по старинке - все
			}
			else
			{
				base_form.findField('Lpu_ppdid').getStore().removeAll();
				comboLpuTrnsimit.getStore().add([record]);
				comboLpuTrnsimit.getStore().insert(0, [emptyRecord]);
				comboLpuTrnsimit.getStore().commitChanges();
			}
	},

	calcSummTime: function(){
		var base_form = this.FormPanel.getForm(),
			time_start = null,
			time_finish = null,
			hours = '00',
			minutes = '00';
		/*
		for( var i=0,cnt=this.time_fields.length; i<cnt; i++ ) {
			var item = this.time_fields[i];
			if ( typeof item.hiddenName === 'undefined' || ( item.name && item.name === 'SummTime' ) ) {
				continue;
			}
			var current_time = new Date(base_form.findField(item.hiddenName).value);
			if ( !current_time.isValid() ) {
				continue;
			}
			if ( time_start === null || time_start > current_time ) {
				time_start = current_time;
			}
			if ( time_finish === null || time_finish < current_time ) {
				time_finish = current_time;
			}
		}
		*/

		time_start =  new Date(base_form.findField('AcceptTime').value);
		time_finish = new Date(base_form.findField('EndTime').value);

		if ( time_start !== null && time_finish !== null && time_start <= time_finish) {
			var dd_diff = ( time_finish - time_start ) / 1000; // в секундах
			hours = Math.floor( dd_diff / 60 / 60 );
			dd_diff -= hours * 60 * 60;
			minutes = Math.floor( dd_diff / 60);
			dd_diff -= minutes * 60;
			if ( hours < 10 ) { hours = '0'+hours; }
			if ( minutes < 10 ) { minutes = '0'+minutes; }
		}
		base_form.findField('SummTime').setValue( hours + ':' + minutes );
	},
	
	loadEmergencyTeamsWorkedInATime: function(){
		var base_form = this.FormPanel.getForm(),
			time_start = new Date(base_form.findField('AcceptTime').value),
			formattedTS = Ext.util.Format.date(time_start, 'd.m.Y H:i:s');
			
		base_form.findField('EmergencyTeam_id').getStore().load({
			params: { teamTime: formattedTS },
			callback: function() {}
		});
	},

	DocumentUc_id: null,

	doValidate: function( base_form ){
		var has_error = false,
			error = '',
			unknown = lang['neizvesten'],
			diagField = base_form.findField('Diag_id'),
			payTypeCombo = base_form.findField('PayType_id'),
			callCauseField = base_form.findField('CallPovod_id'),
			fam = base_form.findField('Fam').getValue(),
			name_field= base_form.findField('Name').getValue(),
			middle = base_form.findField('Middle').getValue(),
			age = base_form.findField('Age'),
			callCauseIsError = (!Ext.isEmpty(callCauseField.getValue()) && callCauseField.getValue().inlist([509]));
		//если повод ошибка - то результат выезда, результат оказания смп, диагноз - необязательные
		if(callCauseIsError){
			Ext.getCmp(this.id+"_ResultEmergencyTrip").allowBlank = true;
			Ext.getCmp(this.id+"_ResultId").allowBlank = true;
			diagField.allowBlank = true;
		}

		if
		( Ext.getCmp('CMPCLOSE_CB_231').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_232').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_233').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_234').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_235').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_236').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_237').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_238').getValue()
		|| (getGlobalOptions().region.nick == 'kareliya' && payTypeCombo && !payTypeCombo.hidden && (!payTypeCombo.getValue().inlist([51])))
		) {
			diagField.allowBlank = true;
			diagField.validate();
			Ext.getCmp(this.id+"_ResultId").allowBlank = true;
			Ext.getCmp(this.id+"_ResultId").validate();
		} else {
			diagField.allowBlank = false;
			diagField.validate();
			Ext.getCmp(this.id+"_ResultId").allowBlank = false;
			Ext.getCmp(this.id+"_ResultId").validate();
		}

		// при неизвестном пациенте возраст не обязателен

		if(fam == unknown && name_field == unknown && middle == unknown && getGlobalOptions().region.nick == 'kareliya')
			age.allowBlank = true;
		else
			age.allowBlank = false;

		if ( !base_form.isValid() ) {
			has_error = true;
			error = ERR_INVFIELDS_MSG;
		} else {
			if(!callCauseIsError/* && !diagField.allowBlank*/){
				if (
					!( Ext.getCmp('CMPCLOSE_CB_231').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_232').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_233').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_234').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_235').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_236').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_237').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_238').getValue()
					|| (payTypeCombo && !payTypeCombo.hidden && (!payTypeCombo.getValue().inlist([51])))
					)
					&& (((diagField.getValue() == '') || (diagField.getValue() == null)) && !diagField.allowBlank)
				) {
					diagField.allowBlank = false;
					diagField.validate();
					error += (error.length?'<br />':'') + 'Заполните поле «Диагноз».';
				}
				if (
					!( Ext.getCmp('CMPCLOSE_CB_231').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_232').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_233').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_234').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_235').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_236').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_237').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_238').getValue() )
					&& (
						!Ext.getCmp('CMPCLOSE_CB_106').getValue()
						&& !Ext.getCmp('CMPCLOSE_CB_107').getValue()
						&& !Ext.getCmp('CMPCLOSE_CB_108').getValue()
					)
				) {
					Ext.getCmp(this.id+"_ResultId").allowBlank = false;
					Ext.getCmp(this.id+"_ResultId").validate();
					error += (error.length?'<br />':'') + 'Заполните поле «Результат оказания скорой медицинской помощи».';
				}

				if (
					!(Ext.getCmp('CMPCLOSE_CB_224').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_225').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_226').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_227').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_228').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_229').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_230').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_231').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_232').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_233').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_234').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_235').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_236').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_237').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_238').getValue()
					|| Ext.getCmp('CMPCLOSE_CB_239').getValue() )
					)
				{
					error += (error.length?'<br />':'') + 'Заполните поле «Результат выезда».';
				}

			}

			var d1 = new Date(base_form.findField('AcceptTime').value),
				d2 = new Date(base_form.findField('TransTime').value),
				d3 = new Date(base_form.findField('GoTime').value),
				d4 = new Date(base_form.findField('ArriveTime').value),
				d5 = new Date(base_form.findField('TransportTime').value),
				d6 = new Date(base_form.findField('ToHospitalTime').value),
				d7 = new Date(base_form.findField('EndTime').value),
				d8 = new Date(base_form.findField('BackTime').value);

			if ( !d1.isValid() ) {
				error += (error.length?'<br />':'') + 'Не указана дата приёма вызова';
			} else
			if(!d3.isValid() && getGlobalOptions().region.nick == 'krym'){
				error += (error.length?'<br />':'') + 'Не указана дата выезда на вызов';
			} else
			if(!d4.isValid() && getGlobalOptions().region.nick == 'krym' ){
				error += (error.length?'<br />':'') + 'Не указана дата прибытия на место вызова';
			} else {
				var dd = d1;

				if ( d2.isValid() ) {
					if ( dd > d2 ) {
						error += (error.length?'<br />':'') + 'Передача вызова не может совершиться раньше.';
					} else {
						dd = d2;
					}
				}
				if ( d3.isValid() ) {
					if ( dd > d3 ) {
						error += (error.length?'<br />':'') + 'Выезд на вызов не может совершиться раньше.';
					} else {
						dd = d3;
					}
				}
				if ( d4.isValid() ) {
					if ( dd > d4 ) {
						error += (error.length?'<br />':'') + 'Прибытие на вызов не может совершиться раньше.';
					} else {
						dd = d4;
					}
				}
				if ( d5.isValid() ) {
					if ( dd > d5 ) {
						error += (error.length?'<br />':'') + 'Транспортировка не может совершиться раньше.';
					} else {
						dd = d5;
					}
				}
				if ( d6.isValid() ) {
					if ( dd > d6 ) {
						error += (error.length?'<br />':'') + 'Прибытие в МО не может совершиться раньше.';
					} else {
						dd = d6;
					}
				}
				if ( d7.isValid() ) {
					if ( dd > d7 ) {
						error += (error.length?'<br />':'') + 'Окончание вызова не может совершиться раньше.';
					} else {
						dd = d7;
					}
				}
				if ( d8.isValid() ) {
					if ( dd > d8 ) {
						error += (error.length?'<br />':'') + 'Возвращение на станцию не может совершиться раньше.';
					} else {
						dd = d8;
					}
				}
			}
		}

		if ( error.length ) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function(){
					/*diagField.allowBlank = (getGlobalOptions().region.nick != 'buryatiya');
					Ext.getCmp(this.id+"_ResultId").allowBlank = true;
					*/
					var invalid = this.FormPanel.getInvalid()[0];
					if ( invalid ) {
						invalid.ensureVisible().focus();
					}
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: error,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		} else {
			// Проверка указанного пациента непосредственно перед сохранением
			if ( base_form.findField('Person_id').getValue() == 0 ||
				base_form.findField('Person_id').getValue() == null ||
				base_form.findField('Person_id').getValue() == ''
			) {
				if ( !confirm(lang['dannyiy_patsient_ne_obnarujen_v_baze_dannyih_patsientov_rmias_dlya_oplatyi_kartyi_vyizova_smp_patsienta_neobhodimo_dobavit_v_bazu_dannyih_patsientov_rmias_prodoljit_sohranenie']) )
				{
					this.formStatus = 'edit';
					return false;
				}
			} else {
				//условие для крыма и типа оплаты ОМС refs #90628
				if (getGlobalOptions().region.nick == 'krym' && payTypeCombo.getValue() == 171 && this.diagFinanceConfirm !== true) {
					Ext.Ajax.request({
						params: {
							Diag_id: base_form.findField('Diag_id').getValue(),
							Person_id: base_form.findField('Person_id').getValue(),
							PayType_id: base_form.findField('PayType_id').getValue()
						},
						url: '/?c=CmpCallCard&m=checkDiagFinance',
						callback: function (obj, success, response) {
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.DiagFinance_IsOms == 0 || (response_obj.Diag_Sex != null && response_obj.Diag_Sex != response_obj.Sex_id)) {
									if (confirm('Внимание! Введенный диагноз для данного пациента не оплачивается по ОМС. Продолжить сохранение?')) {
										this.diagFinanceConfirm = true;
										this.doSave();
									}
								} else {
									this.diagFinanceConfirm = true;
									this.doSave();
								}
							}
						}.createDelegate(this)
					});
					this.formStatus = 'edit';
					return false;
				}
			}
			return true;
		}
	},

	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		//validate
		/*var base_form = this.FormPanel.getForm(),
			diagField = Ext.getCmp(this.id+'_Diag_id');
			*/
		var base_form = this.FormPanel.getForm();

		if ( !this.doValidate( base_form ) ) {
			return false;
		}
		this.diagFinanceConfirm = false;
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					var invalid = this.FormPanel.getInvalid()[0];
					if ( invalid ) {
						invalid.ensureVisible().focus();
					}
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();

		if ( base_form.findField('Area_id').disabled ) {
			params.Area_id = base_form.findField('Area_id').getValue() || '';
		}
		if ( base_form.findField('City_id').disabled ) {
			params.City_id = base_form.findField('City_id').getValue() || '';
		}
		if ( base_form.findField('Town_id').disabled ) {
			params.Town_id = base_form.findField('Town_id').getValue() || '';
		}
		if ( base_form.findField('Street_id').disabled ) {
			params.Street_id = base_form.findField('Street_id').getValue() || '';
		}
		if ( base_form.findField('House').disabled ) {
			params.House = base_form.findField('House').getValue();
		}
		if ( base_form.findField('Office').disabled ) {
			params.Office = base_form.findField('Office').getValue();
		}
		if ( base_form.findField('Entrance').disabled ) {
			params.Entrance = base_form.findField('Entrance').getValue();
		}
		if ( base_form.findField('Level').disabled ) {
			params.Level = base_form.findField('Level').getValue();
		}
		if ( base_form.findField('CodeEntrance').disabled ) {
			params.CodeEntrance = base_form.findField('CodeEntrance').getValue();
		}

		if ( this.DocumentUc_id !== null ) {
			params.DocumentUc_id = this.DocumentUc_id;
		}
		
		if ( base_form.findField('Diag_uid').disabled ) {
			params.Diag_uid = base_form.findField('Diag_uid').getValue();
		}

		//params.Diag_uid = base_form.findField('Diag_uid').getValue() || '';

		if (base_form.findField('AcceptTime').getValue().length > 16) {
			base_form.findField('AcceptTime').setValue( Ext.util.Format.date(base_form.findField('AcceptTime').getValue(), 'd.m.Y H:i'));
		}
		if (base_form.findField('TransTime').getValue().length > 16) {
			base_form.findField('TransTime').setValue( Ext.util.Format.date(base_form.findField('TransTime').getValue(), 'd.m.Y H:i'));
		}
		if (base_form.findField('GoTime').getValue().length > 16) {
			base_form.findField('GoTime').setValue( Ext.util.Format.date(base_form.findField('GoTime').getValue(), 'd.m.Y H:i'));
		}
		if (base_form.findField('ArriveTime').getValue().length > 16) {
			base_form.findField('ArriveTime').setValue( Ext.util.Format.date(base_form.findField('ArriveTime').getValue(), 'd.m.Y H:i'));
		}
		if (base_form.findField('TransportTime').getValue().length > 16) {
			base_form.findField('TransportTime').setValue( Ext.util.Format.date(base_form.findField('TransportTime').getValue(), 'd.m.Y H:i'));
		}
		if (base_form.findField('ToHospitalTime').getValue().length > 16) {
			base_form.findField('ToHospitalTime').setValue( Ext.util.Format.date(base_form.findField('ToHospitalTime').getValue(), 'd.m.Y H:i'));
		}
		if (base_form.findField('EndTime').getValue().length > 16) {
			base_form.findField('EndTime').setValue( Ext.util.Format.date(base_form.findField('EndTime').getValue(), 'd.m.Y H:i'));
		}
		if (base_form.findField('BackTime').getValue().length > 16) {
			base_form.findField('BackTime').setValue( Ext.util.Format.date(base_form.findField('BackTime').getValue(), 'd.m.Y H:i'));
		}

		this.calcSummTime();

		params.AcceptDT = base_form.findField('AcceptTime').getValue();
		params.TransDT = base_form.findField('TransTime').getValue();
		params.GoDT = base_form.findField('GoTime').getValue();
		params.ArriveDT = base_form.findField('ArriveTime').getValue();
		params.TransportDT = base_form.findField('TransportTime').getValue();
		params.ToHospitalDT = base_form.findField('ToHospitalTime').getValue();
		params.EndDT = base_form.findField('EndTime').getValue();
		params.BackDT = base_form.findField('BackTime').getValue();
		
		if(base_form.findField('CmpCloseCard_Street').getValue()){
			//params.CmpCloseCard_Street = base_form.findField('CmpCloseCard_Street').getValue();
			params.Street_id = null;
		}

		params.ARMType = base_form.findField('ARMType').getValue();

		if (this.hasUslugaViewFrame()) {
			var usluga_items = this.UslugaViewFrame.getGrid().getStore().query('CmpCallCardUsluga_id',/[^0]/).items;

			var usluga_data_array = [];

			for (var i = 0; i < usluga_items.length; i++) {
				usluga_data_array.push(usluga_items[i].data);
			};

			params.usluga_array = JSON.stringify(usluga_data_array);
		}

		if (getGlobalOptions().region.nick == 'pskov') {
			if (base_form.findField('DisStart').getValue().length > 16) {
				base_form.findField('DisStart').setValue( Ext.util.Format.date(base_form.findField('DisStart').getValue(), 'd.m.Y H:i'));
			}
			params.DisStartDate = base_form.findField('DisStart').getValue();
		}

        params.CmpCallCardDrugJSON = this.DrugGrid.getJSONChangedData();

		this.save_form(base_form, params) ;

	},
	//Метод редактирования записи в гриде услуг
	editCmpCallCardUslugaGridRec: function(data) {

		if (!data.CmpCallCardUsluga_id) {
			return false;
		}

		var grid = this.UslugaViewFrame.getGrid(),
			rec_num = grid.getStore().findBy(function(rec) { return rec.get('CmpCallCardUsluga_id') == data.CmpCallCardUsluga_id; }),
			rec = grid.getStore().getAt(rec_num);

		if (!rec) {
			return false;
		}

		for (var key in data) {
			if (data.hasOwnProperty(key)) {
				rec.set(key,data[key]);
			}
		}

		rec.set('status','edited');
		rec.commit();

	},
	//Метод добавления записи в грид услуг
	addCmpCallCardUslugaGridRec: function(data) {

		data.CmpCallCardUsluga_id = null;

		var rec = new Ext.data.Record(data);

		rec.set('status','added');

		rec.set('CmpCallCardUsluga_id',Math.floor(Math.random() * (-100000)));
		this.UslugaViewFrame.getGrid().getStore().add(rec);

	},
	//Метод удаления записи из грида услуг
	deleteCmpCallCardUslguga: function() {

		var grid = this.UslugaViewFrame.getGrid();

		var record = grid.getSelectionModel().getSelected();
		if (!record) {
			return;
		}

		if (record.get('CmpCallCardUsluga_id') < 0) {
			grid.getStore().remove(record);
		} else {
			record.set('status','deleted');
		}


	},

	openCmpCallCardUslgugaEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.UslugaViewFrame.getGrid();
		var AcceptTime = Date.parseDate(base_form.findField('AcceptTime').getValue(), 'd.m.Y H:i');

		var params = {
			action: action,
			CmpCallCard_setDT: AcceptTime,
			formParams: {}
		};

		switch (action) {

			case 'add':
				params.callback = function(){
					return this.addCmpCallCardUslugaGridRec.apply(this,arguments);
				}.createDelegate(this);
				params.formParams.CmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();
				params.formParams.Person_id = base_form.findField('Person_id').getValue();
				//params.formParams.CmpCallCardUsluga_setDate = AcceptTime.format('d.m.Y');
				//params.formParams.CmpCallCardUsluga_setTime = AcceptTime.format('H:i');
				if (!Ext.isEmpty(base_form.findField('MedStaffFactDoc_id').getValue())) {
					params.MedPersonal_id = base_form.findField('MedStaffFactDoc_id').getFieldValue('MedPersonal_id');
				}
				break;

			case 'edit':
				params.callback = function(){
					return this.editCmpCallCardUslugaGridRec.apply(this,arguments);
				}.createDelegate(this);
				var record = grid.getSelectionModel().getSelected();
				if (!record || !record.get('CmpCallCardUsluga_id')) {
					return false;
				}
				params.formParams = record.data;
				break;

			default:
				params.callback = Ext.emptyFn;
				break;
		}

		getWnd('swCmpCallCardUslugaEditWindow').show(params);

	},

	setEmergencyTeam: function(CmpCallCard_id,EmergencyTeam_data) {
		var cb = this.setStatusCmpCallCard;
		var cb2 = this.closeCmpCallCard;
		this.showLoadMask(lang['naznachenie']);
		var parentObject = this;
		Ext.Ajax.request({
			params: {
				EmergencyTeam_id: EmergencyTeam_data,
				CmpCallCard_id: CmpCallCard_id
			},
			url: '/?c=CmpCallCard&m=setEmergencyTeamWithoutSending',
			callback: function(o, s, r) {
				this.hideLoadMask();
			}.createDelegate(this)
		});
	},

	time_fields: [{
		dateLabel: lang['priema_vyizova'],
		hiddenName: 'AcceptTime',
		allowBlank: false,
		xtype: 'swdatetimefield'
	},{
		dateLabel: lang['peredachi_vyizova_brigade_smp'],
		hiddenName: 'TransTime',
		xtype: 'swdatetimefield'
	},{
		dateLabel: lang['vyiezda_na_vyizov'],
		hiddenName: 'GoTime',
		allowBlank: !(getGlobalOptions().region.nick == 'krym'),
		xtype: 'swdatetimefield'
	},{
		dateLabel: lang['pribyitiya_na_mesto_vyizova'],
		hiddenName: 'ArriveTime',
		allowBlank: !(getGlobalOptions().region.nick == 'krym'),
		xtype: 'swdatetimefield'
	},{
		dateLabel: lang['nachalo_transportirovki_bolnogo'],
		hiddenName: 'TransportTime',
		xtype: 'swdatetimefield'
	},{
		dateLabel: lang['pribyitiya_v_meditsinskuyu_organizatsiyu'],
		hiddenName: 'ToHospitalTime',
		xtype: 'swdatetimefield'
	},{
		dateLabel: lang['okonchaniya_vyizova'],
		hiddenName: 'EndTime',
		allowBlank: false,
		xtype: 'swdatetimefield'
	},{
		dateLabel: lang['vozvrascheniya_na_stantsiyu_podstantsiyu_otdelenie'],
		hiddenName: 'BackTime',
		xtype: 'swdatetimefield'
	},{
		fieldLabel: lang['zatrachennoe_na_vyipolneniya_vyizova_schitaetsya_avtomaticheski'],
		name: 'SummTime',
		width: 90,
		readOnly: true,
		xtype: 'textfield'
	}],

	initComponent: function() {
		var baseaj = this;
		$.ajax({
			url: "/?c=CmpCallCard&m=getComboxAll",
			async: false,
			cache: true
		}).done(function ( data ) {
			baseaj.allfields = JSON.parse(data);
		});

		var panelNumber = 0,
			FeldsherIdField = {},
			FeldsherAcceptCallField = {},
			EmergencyTeamNumField,
			EmergencyTeamIdField,
			EmergencyTeamSpecField;


		//regional crutches
		switch(getGlobalOptions().region.nick){
			case 'kareliya': {
				FeldsherIdField = new Ext.form.Hidden({
					fieldLabel: lang['feldsher_po_priemu_vyizova'],
					name: 'Feldsher_id',
					maskRe: /\d/
				});
				FeldsherAcceptCallField = function(num){
					return new sw.Promed.SwMedPersonalCombo({
						fieldLabel: lang['feldsher_po_priemu_vyizova'],
						name: 'FeldsherAcceptCall',
						width: 350,
						xtype: 'swmedpersonalcombo',
						hiddenName: 'FeldsherAcceptCall',
						allowBlank: true,
						listeners: {
							render: function(){
								this.getStore().load();
							},
							select: function(combo,record,index){
								var appendCombo = this.FormPanel.getForm().findField('FeldsherAccept');
								if(appendCombo)appendCombo.setValue(combo.getValue());
							}.createDelegate(this)
						}
					});
				};
				EmergencyTeamNumField = function(num){
					return new Ext.form.TextField({
						fieldLabel: lang['nomer_brigadyi_skoroy_meditsinskoy_pomoschi'],
						name: 'EmergencyTeamNum',
						allowBlank: false,
						maskRe: /\d/
					});
				};
				EmergencyTeamIdField = function(num){
					return new Ext.Container({
						autoEl: {},
						layout: 'form',
						hidden: true,
						items: [{
							xtype: 'swemergencyteamorepenvcombo',
							fieldLabel:	'Бригада скорой медицинской помощи',
							hiddenName: 'EmergencyTeam_id',		
							allowBlank: !(getGlobalOptions().region.nick == 'krym'),
							width: 350,
							listWidth: 350,
							listeners: {
								select: function(combo,record,index){
									
									var EmergencyTeamNum = baseaj.FormPanel.getForm().findField('EmergencyTeamNum'),
										EmergencyTeamSpec = baseaj.FormPanel.getForm().findField('EmergencyTeamSpec_id'),
										rec = EmergencyTeamSpec.findRecord('EmergencyTeamSpec_Code', record.get('EmergencyTeamSpec_Code'));
									
									if(rec)EmergencyTeamSpec.setValue(rec.get('EmergencyTeamSpec_id'));

									if(EmergencyTeamNum) EmergencyTeamNum.setValue(record.get('EmergencyTeam_Num'));
								}.createDelegate(this)
							}
						
						}]
					})
				}
				EmergencyTeamSpecField = function(num){
					return new Ext.Container({
						autoEl: {},
						layout: 'form',
						hidden: false,
						//width: 750,
						items: [
						{
							fieldLabel: lang['profil_brigadyi_skoroy_meditsinskoy_pomoschi'],
							//name: 'EmergencyTeamSpec',
							comboSubject: 'EmergencyTeamSpec',
							disabledClass: 'field-disabled',
							//hiddenName: 'EmergencyTeamSpec',
							id: 'EmergencyTeamSpec',
							width: 350,
							allowBlank: false,
							listWidth: 300,
							autoLoad: true,
							editable: true,
							xtype: 'swcustomobjectcombo'
						}]
					})
				};
				CmpCloseCard_IsNMPisSMP = function(){
					return new Ext.form.Checkbox({
						name: 'CmpCloseCard_IsNMP',
						id: this.id+'_CmpCloseCard_IsNMP',					
						fieldLabel: 'Неотложная помощь',
						xtype: 'checkbox'
					});
				};
				break;
			}
			case 'krym' :
			case 'perm' :
			{
				FeldsherIdField = new Ext.form.Hidden({
					fieldLabel: lang['feldsher_po_priemu_vyizova'],
					name: 'Feldsher_id'
				});
				FeldsherAcceptCallField =  function(num){
					return new Ext.Container({
						autoEl: {},
						layout: 'form',
						hidden: true,
						//width: 750,
						items: [
						{
							fieldLabel: lang['feldsher_po_priemu_vyizova'],
							name: 'FeldsherAcceptCall',
							width: 250,
							allowBlank: true,
							xtype: 'swmedpersonalcombo',
							hiddenName: 'FeldsherAcceptCall',
							listeners: {
								render: function(){
									this.getStore().load();
								},
								select: function(combo,record,index){
									var appendCombo = baseaj.FormPanel.getForm().findField('FeldsherAccept');
									if(appendCombo)appendCombo.setValue(combo.getValue());
								}.createDelegate(this)
							}
						}
						]
					})
				};
				
				/*FeldsherAcceptCallField =  function(num){
					return new Ext.form.Hidden({
						name: 'FeldsherAcceptCall',
						hiddenName: 'FeldsherAcceptCall'
					});
				};*/
				/*
				FeldsherAcceptCallField = new sw.Promed.SwMedPersonalCombo({
					fieldLabel: ++panelNumber + lang['feldsher_po_priemu_vyizova'],
					name: 'FeldsherAcceptCall',
					width: 250,
					xtype: 'swmedpersonalcombo',
					hiddenName: 'FeldsherAcceptCall',
					listeners: {
						render: function(){
							this.getStore().load();
						},
						select: function(combo,record,index){
							var appendCombo = baseaj.FormPanel.getForm().findField('FeldsherAccept');
							if(appendCombo)appendCombo.setValue(combo.getValue());
						}.createDelegate(this)
					}
				});
				*/
				EmergencyTeamNumField = function(num){
					return new Ext.form.Hidden({
						name: 'EmergencyTeamNum'
					});
				};
				EmergencyTeamIdField = function(num){
					return new sw.Promed.swEmergencyTeamOperEnvCombo({
						fieldLabel:	lang['brigada_skoroy_meditsinskoy_pomoschi'],
						name: 'EmergencyTeam_id',
						allowBlank: true,
						listWidth: 350,
						width: 350,
						listeners: {
							select: function(combo,record,index){
								var EmergencyTeamNum = baseaj.FormPanel.getForm().findField('EmergencyTeamNum');
								if(EmergencyTeamNum) EmergencyTeamNum.setValue(record.get('EmergencyTeam_Num'));
							}.createDelegate(this)
						}
					});
				};
				EmergencyTeamSpecField = function(num){
					return new Ext.Container({
						autoEl: {},
						layout: 'form',
						hidden: false,
						//width: 750,
						items: [
						{
							fieldLabel: lang['profil_brigadyi_skoroy_meditsinskoy_pomoschi'],
							//name: 'EmergencyTeamSpec',
							comboSubject: 'EmergencyTeamSpec',
							disabledClass: 'field-disabled',
							//hiddenName: 'EmergencyTeamSpec',
							id: 'EmergencyTeamSpec',
							width: 350,
							allowBlank: false,
							listWidth: 300,
							autoLoad: true,
							editable: true,
							xtype: 'swcustomobjectcombo'
						}]
					})
				};
				CmpCloseCard_IsNMPisSMP = function(){
					if(getGlobalOptions().region.nick == 'krym'){
						return new sw.Promed.SwCmpCallCardTypeCombo({
							hiddenName: 'CmpCallCard_IsNMP',
							allowBlank: false,
							disabled: false
						});
					}
					else{
						return new Ext.form.Checkbox({
							name: 'CmpCloseCard_IsNMP',
							id: this.id+'_CmpCloseCard_IsNMP',					
							fieldLabel: 'Неотложная помощь',
							xtype: 'checkbox'
						});						
					}
				};
				break;
			}
			default:{
				FeldsherIdField = new Ext.form.Hidden({
					fieldLabel: lang['feldsher_po_priemu_vyizova'],
					name: 'Feldsher_id'
				});

				FeldsherAcceptCallField = function(num){
					return new Ext.Container({
						autoEl: {},
						layout: 'form',
						//width: 750,
						items: [{
							fieldLabel: lang['feldsher_po_priemu_vyizova'],
							name: 'FeldsherAcceptCall',
							width: 350,
							listWidth: 300,
							xtype: 'swmedpersonalcombo',
							hiddenName: 'FeldsherAcceptCall',
							listeners: {
								render: function(){
									this.getStore().load();
								},
								select: function(combo,record,index){
									var appendCombo = baseaj.FormPanel.getForm().findField('FeldsherAccept');
									if(appendCombo)appendCombo.setValue(combo.getValue());
								}.createDelegate(this)
							}
						}]
					});
				};
				EmergencyTeamNumField = function(num){
					return new Ext.form.Hidden({
						name: 'EmergencyTeamNum'
					});
				};
				EmergencyTeamIdField = function(num){
					return new Ext.Container({
						autoEl: {},
						layout: 'form',
						hidden: true,
						items: [{
							xtype: 'swemergencyteamorepenvcombo',
							fieldLabel:	'Бригада скорой медицинской помощи',
							hiddenName: 'EmergencyTeam_id',		
							allowBlank: (getGlobalOptions().region.nick.inlist(['astra','buryatiya'])),
							width: 350,
							listWidth: 350,
							listeners: {
								select: function(combo,record,index){
									
									var EmergencyTeamNum = baseaj.FormPanel.getForm().findField('EmergencyTeamNum'),
										EmergencyTeamSpec = baseaj.FormPanel.getForm().findField('EmergencyTeamSpec_id'),
										rec = EmergencyTeamSpec.findRecord('EmergencyTeamSpec_Code', record.get('EmergencyTeamSpec_Code'));
									
									if(rec)EmergencyTeamSpec.setValue(rec.get('EmergencyTeamSpec_id'));

									if(EmergencyTeamNum) EmergencyTeamNum.setValue(record.get('EmergencyTeam_Num'));
								}.createDelegate(this)
							}
						
						}]
					})
				};
				
				EmergencyTeamSpecField = function(num){
					return new Ext.Container({
						autoEl: {},
						layout: 'form',
						hidden: !(getGlobalOptions().region.nick == 'astra'),
						//width: 750,
						items: [
						{
							fieldLabel: lang['profil_brigadyi_skoroy_meditsinskoy_pomoschi'],
							//name: 'EmergencyTeamSpec',
							comboSubject: 'EmergencyTeamSpec',
							disabledClass: 'field-disabled',
							//hiddenName: 'EmergencyTeamSpec',
							id: 'EmergencyTeamSpec',
							width: 350,
							allowBlank: !(getGlobalOptions().region.nick == 'astra'),
							listWidth: 300,
							autoLoad: true,
							editable: true,
							xtype: 'swcustomobjectcombo'
						}]
					})
				};
				
				CmpCloseCard_IsNMPisSMP = function(){
					return new Ext.form.Checkbox({
						name: 'CmpCloseCard_IsNMP',
						id: this.id+'_CmpCloseCard_IsNMP',					
						fieldLabel: 'Неотложная помощь',
						xtype: 'checkbox'
					});
				};
				
				break;
			}
		}

		// Персональные данные
		var person_fieds = [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				labelWidth : 400,
				items      : [
					{
						border: false,
						layout: 'column',
						style: 'padding: 0px;',
						items: [
							{
								border: false,
								layout: 'form',
								style: 'padding: 0px',
								width: getRegionNick().inlist(['ufa', 'krym'])?'auto':800,
								items: [
									{
										autoCreate: {tag: "input", type: "text", size: "20", autocomplete: "off",  maxlength: '12'},
										fieldLabel: lang['nomer_vyizova_za_den'],
										maxLength: 12,
										name: 'Day_num',
										allowBlank: false,
										xtype: 'numberfield',
										maskRe: /\d/,
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}		
									}, {
										autoCreate: {tag: "input", type: "text", size: "20", autocomplete: "off",  maxlength: '12'},
										fieldLabel: lang['nomer_vyizova_za_god'],
										maxLength: 12,
										name: 'Year_num',
										allowBlank: false,
										xtype: 'numberfield',
										maskRe: /\d/,
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}		
									}									
								]
							},
							{
								border: false,
								layout: 'form',
								width: 400,
								labelWidth: 100,
								items: [
									{
										xtype: 'swpaytypecombo',
										allowBlank: false,
										disabledClass: 'field-disabled',
										checkAllowLinkedFields: function(recId){
											var base_form = baseaj.FormPanel.getForm(),
												diagField = base_form.findField('Diag_id');

												//если полис не ОМС то делаем поле результат выезда необязательным
												//иначе - обязательный
												diagField.allowBlank = !recId.inlist([51]);
										},
										listeners: {
											change: function(cmp, newVal){
												cmp.checkAllowLinkedFields(newVal);
											},
											select: function(cmp, rec, ind){
												cmp.checkAllowLinkedFields(rec.id);
											}
										}
									}
								]
							}
						]
					},
					{
						border: false,
						layout: 'column',
						style: 'padding: 0px;',
						items: [
						]
					},
					{
						xtype: 'hidden',
						name: 'MedPersonal_id'
					},
					// {
						// fieldLabel: 'Врач',
						// hiddenName: 'MedPersonal_id',
						// name: 'MedPersonal_id',
						// id: 'CMP_MedStaffFactRecCombo',
						// enableOutOfDateValidation: true,
						// ignoreDisableInDoc: true,
						// listWidth: 600,
						// width: 350,
						// listeners:
						// {
							// select: function(combo, record, index)
							// {
								// if (record.data.LpuSection_id > 0) {
									// setLpuSectionGlobalStoreFilter({
										// arrayLpuUnitType: [12]
									// });
									// this.ownerCt.ownerCt.findById('CMP_LpuSectionCombo').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									// this.ownerCt.ownerCt.findById('CMP_LpuSectionCombo').setValue(record.data.LpuSection_id);
								// }
							// }
						// },
						// anchor: null,
						/*xtype: 'swmedpersonalallcombo'*/
						// xtype: 'swmedstafffactglobalcombo'
					// },
					{
						title: ++panelNumber + '. ' + lang['vremya'],
						xtype: 'fieldset',
						autoHeight: true,
						items: this.time_fields
					}, 
					{
						title: ++panelNumber + '. ' + lang['podrazdelenie_smp'],
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								border: false,
								layout: 'form',
								style: 'padding: 0px',
								items: [
									FeldsherIdField,
									FeldsherAcceptCallField(panelNumber),
									{
										fieldLabel: 'Станция (подстанция), отделение',
										name: 'StationNum',
										xtype: 'hidden',
										maskRe: /\d/
									}, {
										fieldLabel: 'Станция (подстанция), отделение',
										hiddenName:'LpuBuilding_id',
										disabledClass: 'field-disabled',
										width: 350,
										allowBlank: (getGlobalOptions().region.nick.inlist(['kareliya'])),
										listWidth: 300,
										xtype: 'swsmpunitscombo'
									},
									EmergencyTeamNumField(panelNumber),
									EmergencyTeamIdField(panelNumber),
									EmergencyTeamSpecField(panelNumber),
									/*{
										fieldLabel: lang['profil_brigadyi_skoroy_meditsinskoy_pomoschi'],
										//name: 'EmergencyTeamSpec',
										comboSubject: 'EmergencyTeamSpec',
										disabledClass: 'field-disabled',
										//hiddenName: 'EmergencyTeamSpec',
										id: 'EmergencyTeamSpec',
										width: 350,
										allowBlank: false,
										listWidth: 300,
										autoLoad: true,
										editable: true,
										xtype: 'swcustomobjectcombo'
									},*/
									{
										allowBlank: getGlobalOptions().region.nick.inlist(['astra']),
										hiddenName: 'LpuSection_id',
										name: 'LpuSection_id',
										id: 'CMP_LpuSectionCombo',
										lastQuery: '',
										listWidth: 600,
										tabIndex: TABINDEX_EVPLEF + 5,
										width: 350,
										xtype: 'swlpuunitcmpcombo',
										listeners: {
											select: function(combo,record,index){
												this.FormPanel.getForm().findField('MedStaffFactDoc_id').reset();
												setMedStaffFactGlobalStoreFilter({
													arrayLpuUnitType: [12],
													LpuSection_id: record.get('LpuSection_id'),
													onDate: getGlobalOptions().date // не уволены
												});
												this.FormPanel.getForm().findField('MedStaffFactDoc_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
											}.createDelegate(this)
										}
									},{
										allowBlank: false,
										dateFieldId: 'EVPLEF_EvnVizitPL_setDate',
										enableOutOfDateValidation: true,
										hiddenName: 'MedStaffFactDoc_id',
										name: 'MedStaffFactDoc_id',
										id: 'CMP_MedStaffFactRecCombo',
										lastQuery: '',
										listWidth: 600,
										parentElementId: 'CMP_LpuSectionCombo',
										//tabIndex: TABINDEX_EVPLEF + 6,
										width: 350,
										xtype: 'swmedstafffactglobalcombo',
										listeners: {
											select: function(combo, record, index){
												if (record.data.MedPersonal_id > 0) {
													this.FormPanel.getForm().findField('MedPersonal_id').setValue(record.data.MedPersonal_id);
												}
											}.createDelegate(this)
										}
									}
								]
							},
							{
								id: 'BrigSelectBtn',
								disabled: (getGlobalOptions().region.nick.inlist(['pskov'])),
								hidden: (getGlobalOptions().region.nick.inlist(['pskov'])),
								text: lang['vyibrat'],
								xtype: 'button',
								handler: function() {
									var parentObject = this;
									getWnd('swSelectEmergencyTeamWindow').show({
										callback: function(data) {
											//parentObject.setEmergencyTeam(record, data, flag)
											//parentObject.EmergencyTeamNum = data.EmergencyTeam_Num;
											Ext.getCmp('EmergencyTeamNum').setValue(data.EmergencyTeam_Num);
											parentObject.setEmergencyTeam(parentObject.FormPanel.getForm().findField('CmpCallCard_id').getValue(), data.EmergencyTeam_id);
										}

									});
								}.createDelegate(this)								
							}
						]
					},
					{
						title : ++panelNumber + '. ' + lang['adres_vyizova'],
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
								{
									enableKeyEvents: true,
									hiddenName: 'KLAreaStat_idEdit',
									listeners: {
										beforeselect: function(combo, record) {
											if ( typeof record != 'undefined' ) {
											if( record.get('KLAreaStat_id') == '' ) {
												combo.onClearValue();
												return;
											}

											var base_form = this.FormPanel.getForm();
											base_form.findField('Area_id').reset();
											base_form.findField('City_id').reset();
											base_form.findField('Town_id').reset();
											base_form.findField('Street_id').reset();
											base_form.findField('CmpCloseCard_Street').reset();

											if( record.get('KLSubRGN_id') != '' ) {
												base_form.findField('Area_id').setValue(record.get('KLSubRGN_id'));
												base_form.findField('Area_id').getStore().removeAll();
												base_form.findField('Area_id').getStore().load({
													params: {region_id: record.get('KLRGN_id')},
													callback: function() {
														this.setValue(this.getValue());
														this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) { return rec.get('SubRGN_id') == this.getValue(); }.createDelegate(this))));
													}.createDelegate(base_form.findField('Area_id'))
												});
											} else if( record.get('KLCity_id') != '' ) {
												base_form.findField('City_id').setValue(record.get('KLCity_id'));
												base_form.findField('City_id').getStore().removeAll();
												base_form.findField('City_id').getStore().load({
													params: {subregion_id: record.get('KLRGN_id')},
													callback: function() {
														this.setValue(this.getValue());
														this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) { return rec.get('City_id') == this.getValue(); }.createDelegate(this))));
													}.createDelegate(base_form.findField('City_id'))
												});
											}
											//KLTown_id
											}
										}.createDelegate(this)
									},
									onClearValue: function() {
										var base_form = this.FormPanel.getForm();
										base_form.findField('KLAreaStat_idEdit').clearValue();
										base_form.findField('Area_id').enable();
										base_form.findField('City_id').enable();
										base_form.findField('Town_id').enable();
										base_form.findField('Town_id').reset();
										base_form.findField('Town_id').getStore().removeAll();
										base_form.findField('Street_id').enable();
										base_form.findField('CmpCloseCard_Street').reset();
										base_form.findField('Street_id').reset();
										base_form.findField('Street_id').getStore().removeAll();
									}.createDelegate(this),
									width: 180,
									xtype: 'swklareastatcombo'
								},
								{
									name: 'KLRgn_id',
									value: 0,
									xtype: 'hidden'
								},{
									disabled: true,
									enableKeyEvents: true,
									fieldLabel: lang['rayon'],
									hiddenName: 'Area_id',
									width: 180,
									listeners: {
										'beforeselect': function(combo, record) {
											combo.setValue(record.get(combo.valueField));
											var base_form = this.FormPanel.getForm();
											if( record.get('SubRGN_id') > 0 ) {
												base_form.findField('City_id').reset();
												base_form.findField('City_id').getStore().removeAll();
												base_form.findField('City_id').getStore().load({params: {subregion_id: record.get('SubRGN_id')}});
												base_form.findField('Town_id').getStore().removeAll();
												base_form.findField('Town_id').getStore().load({params: {city_id: record.get('SubRGN_id')}});
												base_form.findField('Street_id').getStore().removeAll();
												base_form.findField('Street_id').getStore().load({params: {town_id: record.get('SubRGN_id')}});
											}
										}.createDelegate(this)
									},
									xtype: 'swsubrgncombo'
								}, {
									hiddenName: 'City_id',
									disabled: true,
									name: 'City_id',
									width: 180,
									xtype: 'swcitycombo',
									listeners: {
										'beforeselect': function(combo, record) {
											if ( typeof record != 'undefined' ) {combo.setValue(record.get(combo.valueField));}
											var base_form = this.FormPanel.getForm();
											if( typeof record != 'undefined' && record.get('City_id') > 0 ) {

												base_form.findField('Town_id').getStore().removeAll();
												base_form.findField('Town_id').getStore().load({params: {city_id: record.get('City_id')}});
												base_form.findField('Street_id').getStore().removeAll();
												base_form.findField('Street_id').getStore().load({params: {town_id: record.get('City_id'), showSocr: 1}});
											}
										}.createDelegate(this)
									}
								}, {
									disabled: true,
									enableKeyEvents: true,
									listeners: {
										beforeselect: function(combo, record) {
											combo.setValue(record.get(combo.valueField));
											var base_form = this.FormPanel.getForm();
											base_form.findField('Street_id').getStore().removeAll();
											base_form.findField('Street_id').getStore().load({
												params: {town_id: combo.getValue()}
											});
										}.createDelegate(this)
									},
									minChars: 0,
									hiddenName: 'Town_id',
									name: 'Town_id',
									width: 250,
									xtype: 'swtowncombo'
								}, {
									disabled: true,
									//xtype: 'swstreetcombo',
									xtype: 'swstreetandunformalizedaddresscombo',
									fieldLabel: lang['ulitsa'],
									hiddenName: 'Street_id',
									name: 'Street_id',
									width: 250,
									editable: true,	
									listeners: {									
										'blur': function(c){
											var base_form = this.FormPanel.getForm();
											if(!c.store.getCount() || c.store.findBy(function(rec) { return rec.get('Street_Name') == c.getRawValue(); }) == -1 ){
												base_form.findField('CmpCloseCard_Street').setValue(c.getRawValue());
											}
										}.createDelegate(this),
										'beforeselect': function(combo, record) {
											//if ( typeof record != 'undefined' ) {combo.setValue(record.get(combo.valueField));}
											var base_form = this.FormPanel.getForm();
											if( typeof record != 'undefined' && record.get('Street_id') > 0 ) {
												base_form.findField('CmpCloseCard_Street').reset();
											}
										}.createDelegate(this)
									}
								},

								{
									disabledClass: 'field-disabled',
									disabled: true,
									fieldLabel: lang['dom'],
									//name: 'CmpCallCard_Dom',
									name: 'House',
									width: 100,
									xtype: 'textfield'
								}, {
									disabledClass: 'field-disabled',
									disabled: true,
									fieldLabel: lang['korpus'],
									//name: 'CmpCallCard_Dom',
									name: 'Korpus',
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['kvartira'],
									maxLength: 5,
									autoCreate: {tag: "input", size:14, maxLength: "5", autocomplete: "off"},
									//maskRe: /^([а-яА-Я0-9]{1,5})$/,
									//name: 'CmpCallCard_Kvar',
									name: 'Office',
									width: 100,
									xtype: 'textfieldpmw'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['komnata'],
									//name: 'CmpCallCard_Kvar',
									name: 'Room',
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['podyezd'],
									//name: 'CmpCallCard_Podz',
									name: 'Entrance',
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['etaj'],
									//name: 'CmpCallCard_Etaj',
									name: 'Level',
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['kod_zamka_v_podyezde_domofon'],
									//name: 'CmpCallCard_Kodp',
									name: 'CodeEntrance',
									width: 100,
									xtype: 'textfield'
								}
						]
					}, {
						title : ++panelNumber + '. ' + lang['svedeniya_o_bolnom'],
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								layout: 'column',
								items :[{
									border: false,
									layout: 'form',
									items : [{
										handler: function() {
											this.personSearch();
										}.createDelegate(this),
										iconCls: 'search16',
										id: 'CCCSEF_PersonSearchBtn',
										text: lang['poisk'],
										xtype: 'button'
									},
									{
										handler: function() {
											this.personReset();
										}.createDelegate(this),
										iconCls: 'reset16',
										id: 'CCCSEF_PersonResetBtn',
										text: lang['sbros'],
										xtype: 'button'
									},
									{
										handler: function() {
											this.personUnknown();
										}.createDelegate(this),
										iconCls: 'reset16',
										id: 'CCCSEF_PersonUnknownBtn',
										text: lang['neizvesten'],
										xtype: 'button'
									}]
								}, {
									border: false,
									layout: 'form',
									items : [{
										fieldLabel: lang['familiya'],
										//name: 'Person_Surname',
										name: 'Fam',
										//hiddenName: 'Fam',
										toUpperCase: true,
										width: 180,//
										allowBlank: false,
										xtype: 'textfieldpmw'
									}, {
										fieldLabel: lang['imya'],
										//name: 'Person_Firname',
										name: 'Name',
										toUpperCase: true,
										allowBlank: false,
										width: 180,
										xtype: 'textfieldpmw'
									}, {
										fieldLabel: lang['otchestvo'],
										//name: 'Person_Secname',
										name: 'Middle',
										toUpperCase: true,
										width: 180,
										xtype: 'textfieldpmw'
									}]
								}]
							},
							{
								xtype      : 'fieldset',
								autoHeight: true,
								items      : [
								{
									allowDecimals: false,
									allowNegative: false,
									disabledClass: 'field-disabled',
									fieldLabel: lang['vozrast'],
									allowBlank: false,
									//name: 'Person_Age',
									name: 'Age',
									toUpperCase: true,
									width: 180,
									xtype: 'numberfield',
									validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
									listeners: {
										change: function() {
											this.setMKB();
										}.createDelegate(this)
									}
								}, new Ext.form.RadioGroup({
									fieldLabel: lang['edinitsa_izmereniya_vozrasta'],
									columns: 1,
									vertical: true,
									width: '100%',
									cls: 'boxbgr',
									items: this.getCombo('AgeType_id'),
									listeners: {
										change: function() {
											this.setMKB();
										}.createDelegate(this)
									}
								})
								]
							},
							{
								comboSubject: 'Sex',
								disabledClass: 'field-disabled',
								fieldLabel: lang['pol'],
								//hiddenName: 'Sex_id',
								hiddenName: 'Sex_id',
								allowBlank: false,
								width: 130,
								xtype: 'swcommonsprcombo',
								listeners: {
									change: function() {
										this.setMKB();
									}.createDelegate(this)
								}
							}, {
								xtype: 'textfield',
								width: 180,
								name: 'Work',
								fieldLabel: lang['mesto_rabotyi']
							}, {
								xtype: 'textfield',
								width: 180,
								name: 'DocumentNum',
								fieldLabel: lang['seriya_i_nomer_dokumenta_udostoveryayuschego_lichnost']
							}, {
								xtype: 'textfield',
								width: 180,
								name: 'PolisSerial',
								fieldLabel: lang['seriya_polisa']
							}, {
								xtype: 'textfield',
								width: 180,
								name: 'PolisNum',
								fieldLabel: lang['nomer_polisa']
							}, {
								xtype: 'textfield',
								width: 180,
								name: 'EdNum',
								fieldLabel: lang['edinyiy_nomer']
							},{
								valueField: 'Lpu_id',
								//allowBlank: false,
								//disabled: true,
								autoLoad: true,
								width: 350,
								listWidth: 350,
								fieldLabel: lang['lpu_peredachi'],
								disabledClass: 'field-disabled',
								hiddenName: 'Lpu_ppdid',
								displayField: 'Lpu_Nick',
								medServiceTypeId: 18,
								handler: function() {
									this.selectLpuTransmit();
								}.createDelegate(this),
								comAction: 'AllAddress',
								listeners: {
									beforeselect: function(combo, record) {
										var base_form = this.FormPanel.getForm();
										if(record.get('Lpu_id') == '0')
										{
											combo.getStore().load({params:
											{
												Object: 'LpuWithMedServ',
												comAction: 'AllAddress',
												MedServiceType_id: 18,
												KLAreaStat_idEdit: base_form.findField('KLAreaStat_idEdit').getValue(),
												KLSubRgn_id: base_form.findField('Area_id').getValue(),
												KLCity_id: base_form.findField('City_id').getValue(),
												KLTown_id: base_form.findField('Town_id').getValue(),
												KLStreet_id: base_form.findField('Street_id').getValue(),
												CmpCallCard_Dom: base_form.findField('House').getValue(),
												Person_Age: base_form.findField('Age').getValue()
											}
											});
											return false;
										}
										//определяем метод загрузки лпу передачи
										//this.selectLpuTransmit();
										}.createDelegate(this)
									,select: function(combo, record){
										if (record.data.Lpu_id == null)
										{
											combo.setValue('');
										}
									}
								},

								xtype: 'swlpuwithmedservicecombo'
							},{
								xtype: 'panel',
								frame: true,
								border: false,
								hidden: true,
								name: 'lpu_panel',
								style: 'margin: 5px;',
								bodyStyle: 'padding: 3px;',
								items: [{
									html: '',
									style: 'text-align: center;',
									name: 'lpu_field'
								}]
							}, {
								disabledClass: 'field-disabled',
								fieldLabel: lang['dopolnitelnaya_informatsiya_utochnennyiy_adres'],
								toUpperCase: true,

								height: 100,
								name: 'CmpCallCard_Comm',
								// tabIndex: TABINDEX_PEF + 5,
								width: 350,
								xtype: 'textarea'
							}
						]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						items: [
							// @todo Сделать компонент и вынести в библиотеку
							{
								xtype: 'swcommonsprcombo',
								fieldLabel: ++panelNumber + lang['kto_vyizyivaet'],
								comboSubject: 'CmpCallerType',
								hiddenName: 'Ktov',
								displayField: 'CmpCallerType_Name',
								disabledClass: 'field-disabled',
								editable: true,
								forceSelection: false,
								width: 350,
								listeners: {
									blur: function(el){
										var base_form = baseaj.FormPanel.getForm(),
											CmpCallerTypeField = base_form.findField('CmpCallerType_id'),
											raw_value = el.getRawValue(),
											rec = el.findRecord( el.displayField, raw_value );

										// Запись в комбобоксе присутствует
										if ( rec ) {
											CmpCallerTypeField.setValue( rec.get( el.valueField ) );
										}
										// Пользователь указал свое значение
										else {
											CmpCallerTypeField.setValue(null);
										}
										el.setValue(raw_value);
									}
								}
							},
							{
								xtype: 'hidden',
								name: 'CmpCallerType_id'
							},
							{
								fieldLabel: lang['№_telefona_vyizyivayuschego'],
								name: 'Phone',
								width: 250,
								xtype: 'textfield'
							}
						]
					}, {
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								fieldLabel: ++panelNumber + lang['feldsher_prinyavshiy_vyizov'],
								hiddenName: 'FeldsherAccept',
								allowBlank:true,
								width: 250,
								xtype: 'swmedpersonalcombo',
								listeners: {
									select: function(combo,record,index){
										var appendCombo = this.FormPanel.getForm().findField('FeldsherAcceptCall');
										if(appendCombo)appendCombo.setValue(combo.getValue());
									}.createDelegate(this)
								}
							},
							{
								fieldLabel: ++panelNumber + lang['feldsher_peredavshiy_vyizov'],
								hiddenName: 'FeldsherTrans',
								allowBlank:true,
								width: 250,
								xtype: 'swmedpersonalcombo'
							}
						]
					}
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				items      : [{
						columns: 2,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						cls: 'boxbgr',
						fieldLabel: ++panelNumber + lang['mesto_registratsii_bolnogo'],
						listeners:
						{
							'change': function(rb,checked)
							{
								if(checked){
									if (checked.value==2)
									{
										Ext.getCmp('CMPCLOSE_ComboValue_141').show();
										Ext.getCmp('CMPCLOSE_ComboValue_141').setValue();
									}
									else
									{
										Ext.getCmp('CMPCLOSE_ComboValue_141').hide();
									}
								}
							}.createDelegate(this)
						},
						items: this.getCombo('PersonRegistry_id')
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,

				items: [{
						columns: 2,
						vertical: true,
						width: '600',
						fieldLabel: ++panelNumber + lang['sotsialnoe_polojenie_bolnogo'],
						xtype: 'radiogroup',
						cls: 'boxbgr',
						//hiddenName: 'SocStatus_id',	
						//name: 'SocStatus_id',	
						id: 'psocial',
						allowBlank: false,
						listeners: {
							change: function (rb, checked) {
								if (checked) {
									if (checked.value == 2){
										Ext.getCmp('CMPCLOSE_ComboValue_153').show();
										Ext.getCmp('CMPCLOSE_ComboValue_153').setValue();
									} else {
										Ext.getCmp('CMPCLOSE_ComboValue_153').hide();
									}
								}
							}.createDelegate(this)
						},
						items: this.getCombo('PersonSocial_id')
					}]
			}
		];


		// Повод к вызову
		var povod_fieds = [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
					comboSubject: 'CmpReason',
					disabledClass: 'field-disabled',
					fieldLabel: ++panelNumber + lang['povod'],
					allowBlank: !getGlobalOptions().region.nick.inlist(['astra']),
					hiddenName: 'CallPovod_id',
					id: 'idCallPovod_id',
					// tabIndex: TABINDEX_PEF + 5,
					width: 350,
					listWidth: 300,
					editable: true,
					listeners: {
						beforeselect: function(combo, record) {
							
							if(getGlobalOptions().region.nick.inlist(['pskov'])){
								var emergencyTeamSpecField = this.FormPanel.getForm().findField('Diag_uid'),
								emergencyTeamSpecFieldId = this.FormPanel.getForm().findField('Diag_uid');								
								if(record.get('CmpReason_Code') == '352' || record.get('CmpReason_Code') == '353') {
									emergencyTeamSpecField.show();
									emergencyTeamSpecField.getEl().up('.x-form-item').setDisplayed(true);
									emergencyTeamSpecFieldId.show();
									emergencyTeamSpecFieldId.getEl().up('.x-form-item').setDisplayed(true);
								} else {
									emergencyTeamSpecField.hide();
									emergencyTeamSpecField.getEl().up('.x-form-item').setDisplayed(false);
									emergencyTeamSpecFieldId.hide();
									emergencyTeamSpecFieldId.getEl().up('.x-form-item').setDisplayed(false);
								}
							}
							
						}.createDelegate(this),
						change: function(cmp, newVal){
							var radioGroupResultTrip = Ext.getCmp(baseaj.id+"_ResultEmergencyTrip"),
								radioGroupResult = Ext.getCmp(baseaj.id+"_ResultId"),
								base_form = baseaj.FormPanel.getForm(),
								diagField = Ext.getCmp(baseaj.id+"_Diag_id");

							//если повод - "ошибка" то делаем поле результат выезда необязательным
							//иначе - обязательный
							if(newVal.inlist([509])){
								radioGroupResultTrip.allowBlank = true;
								radioGroupResult.allowBlank = true;
								diagField.allowBlank = true;
							}
							else{
								radioGroupResultTrip.allowBlank = false;
								radioGroupResult.allowBlank = false;
								diagField.allowBlank = false;
							}
						}
					},
					xtype: 'swreasoncombo'
				}, {
					comboSubject: 'CmpReasonNew',
					disabledClass: 'field-disabled',
					fieldLabel: lang['povod'],
					hiddenName: 'CallPovodNew_id',
					id: 'CallPovodNew_id',
					width: 350,
					listWidth: 300,
					autoLoad: true,
					editable: true,
					xtype: 'swcustomobjectcombo'
				}, /*{
					name: 'Diag_uid',
					hiddenName: 'Diag_uid',
					id: 'Diag_uid',
					disabled: true,
					hidden: true,
					xtype: 'swdiagcombo'
				},*/
				CmpCloseCard_IsNMPisSMP()
				/*new sw.Promed.SwCmpCallCardTypeCombo({
					hiddenName: 'CmpCallCard_IsNMP',
					allowBlank: false
				})*/
				/*{
					//name: 'CmpCallCard_IsNMP',
					name: 'CmpCloseCard_IsNMP',
					fieldLabel: lang['neotlojnaya_pomosch'],
					xtype: 'checkbox'
				}*/]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						comboSubject: 'CmpCallType',
						fieldLabel	   : ++panelNumber + lang['vyizov'],
						allowBlank: false,
						id: this.id+"_CmpCallType_id",
						//fieldLabel: 'Тип вызова',
						//hiddenName: 'CmpCallType_id',
						hiddenName: 'CallType_id',
						//displayField: 'CmpCallType_Name',
						xtype: 'swcommonsprcombo',
						width: 300,
						listWidth: 300
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,

				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : ++panelNumber + lang['mesto_polucheniya_vyizova_brigadoy_skoroy_meditsinskoy_pomoschi'],
						width: '100%',
						xtype: 'radiogroup',
						cls: 'boxbgr',
						items: this.getCombo('CallTeamPlace_id')
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,

				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : ++panelNumber + lang['prichinyi_vyiezda_s_opozdaniem'],
						width: '100%',
						xtype: 'radiogroup',
						cls: 'boxbgr',
						items: this.getCombo('Delay_id')
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,

				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : ++panelNumber + lang['sostav_brigadyi_skoroy_meditsinskoy_pomoschi'],
						width: '100%',
						xtype: 'checkboxgroup',
						cls: 'boxbgr',
						items: this.getCombo('TeamComplect_id')
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,

				frame	   : true,
				items      : [
				/*{
						columns: 1,
						vertical: true,
						fieldLabel	   : lang['17_mesto_vyizova'],
						width: '100%',
						xtype: 'radiogroup',
						cls: 'boxbgr',
						items: this.getCombo('CallPlace_id')
				}*/
				{
					comboSubject: 'CmpCallPlaceType',
					fieldLabel	   : ++panelNumber + lang['tip_mesta_vyizova'],
					hiddenName: 'CmpCallPlaceType_id',
					name: 'CmpCallPlaceType_id',
					xtype: 'swcommonsprcombo',
					width: 250,
					listWidth: 250,
					value: 1
				}
				]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 2,
						fieldLabel	   : ++panelNumber + lang['prichina_neschastnogo_sluchaya'],
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						cls: 'boxbgr',
						items: this.getCombo('AccidentReason_id')
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,

				frame	   : true,
				items      : [{
						columns: 2,
						vertical: true,
						fieldLabel	   : lang['travma'],
						width: '100%',
						xtype: 'checkboxgroup',
						cls: 'boxbgr',
						items: this.getCombo('Trauma_id')
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						fieldLabel: ++panelNumber + lang['nalichie_kliniki_opyaneniya'],
						hiddenName: 'isAlco',
						width: 40,
						comboSubject: 'YesNo',
						xtype: 'swcommonsprcombo'
				}]
			}
		];

		if (getGlobalOptions().region.nick == 'krym') {
			povod_fieds.push({
				xtype: 'fieldset',
				autoHeight: true,
				frame: true,
				items: [{
					columns: 1,
					vertical: true,
					fieldLabel: ++panelNumber + '. Причина длительного доезда',
					width: '100%',
					xtype: 'radiogroup',
					items: this.getCombo('LongDirect_id')
				}]
			});
		}
		// Жалобы
		var jalob_fieds = [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
						fieldLabel	   : ++panelNumber + lang['jalobyi'],
						name: 'Complaints',
						//displayField: 'Diag_Name',
						width: '90%',
						xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 150,
				items      : [{
					fieldLabel: lang['data_nachala_zabolevaniya'],
					name: 'DisStart',
					xtype: 'swdatefield'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
						fieldLabel: ++panelNumber + lang['anamnez'],
						name: 'Anamnez',
						//displayField: 'Diag_Name',
						width: '90%',
						xtype: 'textarea'
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++panelNumber + lang['obyektivnyie_dannyie'],
				frame	   : true,
				items      : [
					{
					layout	   : 'column',
					items: [
						{
							xtype      : 'panel',
							title	   : lang['obschee_sostoyanie'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									cls: 'boxbgr',
									items: this.getCombo('Condition_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['povedenie'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									cls: 'boxbgr',
									items: this.getCombo('Behavior_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['soznanie'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									cls: 'boxbgr',
									items: this.getCombo('Cons_id')
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 150,
								items : [{
									fieldLabel: lang['meningealnyie_znaki'],
									hiddenName: 'isMenen',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['zrachki'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 3,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									cls: 'boxbgr',
									items: this.getCombo('Pupil_id')
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 100,
								items : [{
									fieldLabel: lang['nistagm'],
									hiddenName: 'isNist',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 100,
								items : [{
									fieldLabel: lang['anizokoriya'],
									hiddenName: 'isAnis',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 100,
								items : [{
									fieldLabel: lang['reaktsiya_na_svet'],
									hiddenName: 'isLight',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['kojnyie_pokrovyi'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									cls: 'boxbgr',
									items: this.getCombo('Kozha_id')
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 80,
								items : [{
									fieldLabel: lang['akrotsianoz'],
									width: 50,
									hiddenName: 'isAcro',
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 80,
								items : [{
									fieldLabel: lang['mramornost'],
									width: 50,
									hiddenName: 'isMramor',
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['oteki'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									cls: 'boxbgr',
									items: this.getCombo('Hypostas_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['syip'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									cls: 'boxbgr',
									items: this.getCombo('Crop_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['dyihanie'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									cls: 'boxbgr',
									items: this.getCombo('Hale_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['hripyi'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									cls: 'boxbgr',
									items: this.getCombo('Rattle_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['odyishka'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									cls: 'boxbgr',
									items: this.getComboRadio('Shortwind_id')
							}]
						},
						// Органы системы кровообращения
						{
							xtype      : 'panel',
							title	   : lang['tonyi_serdtsa'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									cls: 'boxbgr',
									items: this.getCombo('Heart_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['shum'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									cls: 'boxbgr',
									items: this.getCombo('Noise_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['puls'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									cls: 'boxbgr',
									items: this.getCombo('Pulse_id')
							}]
						},
						// Органы пищеварения
						{
							xtype      : 'panel',
							title	   : lang['yazyik'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									cls: 'boxbgr',
									items: this.getCombo('Lang_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['jivot'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									cls: 'boxbgr',
									items: this.getCombo('Gaste_id')
							}, {
								xtype      : 'fieldset',
								labelWidth: 160,
								autoHeight: true,
								items : [{
									fieldLabel: lang['uchastvuet_v_akte_dyihaniya'],
									hiddenName: 'isHale',
									comboSubject: 'YesNo',
									width: 40,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 200,
								items : [{
									fieldLabel: lang['simptomyi_razdrajeniya_bryushinyi'],
									hiddenName: 'isPerit',
									comboSubject: 'YesNo',
									width: 40,
									xtype: 'swcommonsprcombo'
								}]
							}]
						}, {
							xtype      : 'panel',
							title	   : lang['pechen'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									cls: 'boxbgr',
									items: this.getCombo('Liver_id')
							}]
						}]
					}, {
						height: 20
					}, {
							fieldLabel: lang['mocheispuskanie'],
							name: 'Urine',
							width: 400,
							xtype: 'textfield'
					}, {
							fieldLabel: lang['stul'],
							name: 'Shit',
							xtype: 'textfield'
					}, {
							fieldLabel: lang['drugie_simptomyi'],
							name: 'OtherSympt',
							width: 400,
							xtype: 'textarea'
					},
						{
							xtype: 'container',
							autoEl: {},
							layout: 'column',
							items:
							[
								{
									xtype: 'fieldset',
									border: false,
									autoHeight: true,
									width: 310,
									labelWidth : 220,
									items: [{
										fieldLabel: lang['rabochee_ad_mm_rt_st'],
										name: 'sub1WorkAD',
										width: 55,
										xtype: 'textfield',
										maskRe: /\d/,
										maxLength:3,
										listeners: {
											'blur': function(me){
												var baseform = this.FormPanel.getForm(),
													workadfield = baseform.findField('WorkAD'),
													workad2field = baseform.findField('sub2WorkAD');

												workadfield.setValue(me.getValue()+'/'+workad2field.getValue());
											}.createDelegate(this)
										}
									}]
								},
								{
									xtype: 'label',
									text: '/'
									//style: 'padding: 0 10px;'
								},
								{
									xtype: 'textfield',
									name: 'sub2WorkAD',
									width: 60,
									maskRe: /\d/,
									maxLength:3,
									style: 'margin: 0 0 0 10px;',
									listeners: {
										'blur': function(me){
											var baseform = this.FormPanel.getForm(),
												workadfield = baseform.findField('WorkAD'),
												workad1field = baseform.findField('sub1WorkAD');

											workadfield.setValue(workad1field.getValue()+'/'+me.getValue());
										}.createDelegate(this)
									}
								}
							]
					},	{
							name: 'WorkAD',
							xtype: 'hidden'
					},
					{
						xtype: 'container',
						autoEl: {},
						layout: 'column',
						items:
						[
							{
								xtype: 'fieldset',
								border: false,
								autoHeight: true,
								width: 310,
								labelWidth : 220,
								items: [{
									fieldLabel: lang['ad_mm_rt_st'],
									name: 'sub1AD',
									width: 55,
									xtype: 'textfield',
									maskRe: /\d/,
									maxLength:3,
									listeners: {
										'blur': function(me){
											var baseform = this.FormPanel.getForm(),
												workadfield = baseform.findField('AD'),
												workad2field = baseform.findField('sub2AD');

											workadfield.setValue(me.getValue()+'/'+workad2field.getValue());
										}.createDelegate(this)
									}
								}]
							},
							{
								xtype: 'label',
								text: '/'
								//style: 'padding: 0 10px;'
							},
							{
								xtype: 'textfield',
								name: 'sub2AD',
								width: 60,
								maskRe: /\d/,
								maxLength:3,
								style: 'margin: 0 0 0 10px;',
								listeners: {
									'blur': function(me){
										var baseform = this.FormPanel.getForm(),
											workadfield = baseform.findField('AD'),
											workad1field = baseform.findField('sub1AD');

										workadfield.setValue(workad1field.getValue()+'/'+me.getValue());
									}.createDelegate(this)
								}
							}
						]
					},
					{
							fieldLabel: lang['ad_mm_rt_st'],
							name: 'AD',
							xtype: 'hidden'
					}, {
							fieldLabel: lang['chss_min'],
							name: 'Chss',
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3
					}, {
							fieldLabel: lang['puls_ud_min'],
							name: 'Pulse',
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3
					}, {
							fieldLabel: lang['temperatura'],
							name: 'Temperature',
							xtype: 'textfield',
							plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
					}, {
							fieldLabel: lang['chd_min'],
							name: 'Chd',
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3
					}, {
							fieldLabel: lang['pulsoksimetriya'],
							name: 'Pulsks',
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3
					}, {
							fieldLabel: lang['glyukometriya'],
							name: 'Gluck',
							xtype: 'textfield',
							plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
					}, {
							fieldLabel: lang['dopolnitelnyie_obyektivnyie_dannyie_lokalnyiy_status'],
							name: 'LocalStatus',
							width: 400,
							xtype: 'textarea'
					}, {
							fieldLabel: lang['ekg_do_okazaniya_meditsinskoy_pomoschi'],
							name: 'Ekg1',
							width: 90,
							xtype: 'textfield'
					}, {
							fieldLabel: lang['ekg_do_okazaniya_meditsinskoy_pomoschi_vremya'],
							name: 'Ekg1Time',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							validateOnBlur: false,
							width: 90,
							xtype: 'swtimefield'
					}, {
							fieldLabel: lang['ekg_posle_okazaniya_meditsinskoy_pomoschi'],
							name: 'Ekg2',
							width: 90,
							xtype: 'textfield'
					}, {
							fieldLabel: lang['ekg_posle_okazaniya_meditsinskoy_pomoschi_vremya'],
							name: 'Ekg2Time',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							validateOnBlur: false,
							width: 90,
							xtype: 'swtimefield'
					}
				]
			}
		];

		if(getGlobalOptions().region.nick == 'krym'){

			var fieldItemsKrym = [
				{
					fieldLabel: 'Тема беседы',
					name: 'CmpCloseCard_Topic',
					width: 400,
					xtype: 'textarea'
				}, {
					fieldLabel: 'Эпид. анамнез',
					name: 'CmpCloseCard_Epid',
					width: 90,
					xtype: 'textarea'
				}, {
					fieldLabel: 'Оценка по шкале Глазго',
					name: 'CmpCloseCard_Glaz',
					width: 90,
					xtype: 'textfield'
				}, {
					fieldLabel: 'Оценка по шкале Глазго после проведенных метоприятий',
					name: 'CmpCloseCard_GlazAfter',
					width: 90,
					xtype: 'textfield'
				}, {
					fieldLabel: 'E',
					name: 'CmpCloseCard_e1',
					width: 90,
					xtype: 'textfield'
				}, {
					fieldLabel: 'M',
					name: 'CmpCloseCard_m1',
					width: 90,
					xtype: 'textfield'
				}, {
					fieldLabel: 'V',
					name: 'CmpCloseCard_v1',
					width: 90,
					xtype: 'textfield'
				}, {
					fieldLabel: 'E после проведенных метоприятий',
					name: 'CmpCloseCard_e2',
					width: 90,
					xtype: 'textfield'
				}, {
					fieldLabel: 'M после проведенных метоприятий',
					name: 'CmpCloseCard_m2',
					width: 90,
					xtype: 'textfield'
				}, {
					fieldLabel: 'V после проведенных метоприятий',
					name: 'CmpCloseCard_v2',
					width: 90,
					xtype: 'textfield'
				}
			];

			var comboItemsKrym = [
				{
					xtype      : 'panel',
					title	   : 'Аллергические реакции в анамнезе',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Allergic_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Посещение эпид. неблаг. стран и регионов за 3 года',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('VisitEpid_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Инф. заболев, в анамнезе',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Infec_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Инъекции, оперативные вмеш-ва за последние 6 мес',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Injections_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Согласие на медицинское вмешательство получено',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Agreement_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Травм, повреждения',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Injury_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Наруш. Дефикации',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Defik_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Наруш. Диуреза',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Diuresis_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Сердцебиение',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Heartbeat_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Границы сердца',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('BordersHeart_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Рефлексы',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Reflexes_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Запах алкоголя',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('SmellOfAlc_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Отказ',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 2,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('Renouncement_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Ротоглотка',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 2,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('Fauces_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Перкуссия',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 2,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('Percussion_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Мышечный',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 2,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('Muscular_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Аускультация',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 2,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('Auscultation_id')
					}]
				}, {
					xtype      : 'panel',
					title	   : 'Осмотр на педикулез',
					frame	   : true,
					width : '25%',
					height : 200,
					items : [{
						columns: 2,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('OsmPed_id')
					}]
				}];

			fieldItemsKrym.forEach(function(item){
				jalob_fieds[3].items.push(item)
			});
			comboItemsKrym.forEach(function(item){
				jalob_fieds[3].items[0].items.push(item)
			});
		}

		// Диагноз
		var diagnoz_fieds = [
			{
				xtype      : 'panel',
				title	   : ++panelNumber + lang['diagnoz'],
				frame	   : true,
				labelWidth: 200,
				items: [{
					columns: 2,
					layout	   : 'column',
					width:'100%',
					items: [{
						xtype: 'fieldset',
						border: false,
						autoHeight: true,
						width: 400,
						labelWidth : 100,
						items: [{
							checkAccessRights: true,
							name: 'Diag_id',
							//hiddenName: 'Diag_id',
							id: this.id+"_Diag_id",
							xtype: 'swdiagcombo',
							allowBlank: false,
							disabledClass: 'field-disabled',
							MKB: {
								isMain: true
							},
                            withGroups: (getGlobalOptions().region.nick == 'perm')
							/*listeners: {
								change: function(combo, newValue, oldValue){
									if(newValue.length == 0){
										var diag_uid = Ext.getCmp(baseaj.id+'_Diag_uid');
										diag_uid.clearValue();
										diag_uid.setDisabled(true);
									}
								},
								select: function(combo, select_item){

									if (getGlobalOptions().region.nick == 'perm') {
										var diag_uid = Ext.getCmp(baseaj.id+'_Diag_uid');
										//var	level_not_two = true;

										if(typeof select_item.get('DiagLevel_id') !== 'undefined' && select_item.get('DiagLevel_id') == 3){
											diag_uid.setDisabled(false);
											diag_uid.Diag_level3_code =  select_item.get('Diag_Code');
											diag_uid.doQuery(); //обновляем данные поля "Уточненный диагноз"
										}else{
											diag_uid.clearValue();
											diag_uid.setDisabled(true);
										}

									}
								}
							}*/
						}]
					}, {
						xtype: 'fieldset',
						border: false,
						autoHeight: true,
						width: 500,
						hidden: (getGlobalOptions().region.nick != 'perm'),
						labelWidth : 100,
						items: [{
							fieldLabel: lang['utochnenie'],
							name: 'Diag_add',
							width: 300,
							xtype: 'textfield'
						},
						new sw.Promed.SwDiagCombo({
							name: 'Diag_uid',
							hiddenName: 'Diag_uid',
							fieldLabel: 'Уточненный диагноз',
							disabled: true,
							enabled: false,
							hideTrigger: false,
							autoShow: true,
							id: this.id+"_Diag_uid",
							checkAccessRights: true,
							allowBlank: (!getGlobalOptions().region.nick.inlist(['perm'])),
							//allowBlank: false,
							disabledClass: 'field-disabled',
							MKB: {
								isMain: true
							}
							/*listeners: {
								focus: function(){
									if(this.getValue().length > 0){
										this.getStore().reload();
									}
								}
							}*/
						})
						]
					}, {
						xtype: 'fieldset',
						border: false,
						autoHeight: true,
						width: 500,
						hidden: (getGlobalOptions().region.nick != 'krym'),
						labelWidth : 100,
						items: [
							new sw.Promed.SwDiagCombo({
								hiddenName: 'Diag_sid',
								fieldLabel: 'Сопутствующий диагноз',
								hideTrigger: false,
								autoShow: true,
								id: this.id+"_Diag_sid",
								checkAccessRights: true,
								allowBlank: true,//(getGlobalOptions().region.nick != 'krym'),
								disabledClass: 'field-disabled',
								MKB: {
									isMain: true
								}
							})
						]
					}]
				}]
			},

			// TODO: код МКБ-10
			{
				xtype      : 'panel',
				title	   : ++panelNumber + lang['oslojneniya'],
				frame	   : true,
				items      : [{
						columns: 3,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						cls: 'boxbgr',
						items: this.getCombo('Complicat_id')
				}]
			}, {
				xtype      : 'panel',
				title	   : ++panelNumber + lang['effektivnost_meropriyatiy_pri_oslojnenii'],
				frame	   : true,
				items      : [{
						columns: 3,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						cls: 'boxbgr',
						items :this.getCombo('ComplicatEf_id')
				}]
			}
		];

		// Манипуляции
		var procedure_fieds = [
			{
				xtype      : 'panel',
				title	   : ++panelNumber + lang['okazannaya_pomosch_na_meste_vyizova'],
				frame	   : true,
				items      : [{
						name: 'HelpPlace',
						width: '99%',
						xtype: 'textarea'
				}]
			}, {
				xtype      : 'panel',
				title	   : ++panelNumber + lang['okazannaya_pomosch_v_avtomobile_skoroy_meditsinskoy_pomoschi'],
				frame	   : true,
				items      : [{
						name: 'HelpAuto',
						width: '99%',
						xtype: 'textarea'
				}]
			}, {
				xtype      : 'panel',
				title	   : ++panelNumber + lang['effektivnost_provedennyih_meropriyatiy'],
				frame	   : true,
				layout	   : 'form',
				items: [
						{
							xtype: 'container',
							autoEl: {},
							layout: 'column',
							items:
							[
								{
									xtype: 'container',
									autoEl: {},
									layout: 'column',
									columnWidth: .25,
									items: [
									{
										xtype: 'fieldset',
										border: false,
										autoHeight: true,
										//width: 310,
										labelWidth : 120,
										items: [{
											fieldLabel: lang['ad_mm_rt_st'],
											name: 'sub1EAD',
											width: 55,
											xtype: 'numberfield',
											validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
											maxLength:3,
											listeners: {
												'blur': function(me){
													var baseform = this.FormPanel.getForm(),
														workadfield = baseform.findField('EfAD'),
														workad2field = baseform.findField('sub2EAD');

													workadfield.setValue(me.getValue()+'/'+workad2field.getValue());
												}.createDelegate(this)
											}
										}]
									},
									{
										xtype: 'label',
										text: '/'
										//style: 'padding: 0 10px;'
									},
									{
										xtype: 'numberfield',
										name: 'sub2EAD',
										width: 60,
										maskRe: /\d/,
										maxLength:3,
										style: 'margin: 0 0 0 10px;',
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
										listeners: {
											'blur': function(me){
												var baseform = this.FormPanel.getForm(),
													workadfield = baseform.findField('EfAD'),
													workad1field = baseform.findField('sub1EAD');

												workadfield.setValue(workad1field.getValue()+'/'+me.getValue());
											}.createDelegate(this)
										}
									}
									]
								},
								{
									fieldLabel: lang['ad_mm_rt_st'],
									name: 'EfAD',
									xtype: 'hidden'
								},
								/*
								{
									xtype: 'fieldset',
									autoHeight: true,
									labelWidth: "150px",
									columnWidth: .25,
									style: 'border: none;',
									items: [{
											fieldLabel: lang['ad_mm_rt_st'],
											name: 'EfAD',
											xtype: 'textfield'
										}]
								},*/
								{
									xtype: 'fieldset',
									autoHeight: true,
									labelWidth: "150px",
									columnWidth: .25,
									border: false,
									items: [{
										fieldLabel: lang['temperatura'],
										name: 'EfTemperature',
										xtype: 'textfield',
										plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
									}]
								},
								{
									xtype: 'fieldset',
									autoHeight: true,
									labelWidth: "150px",
									columnWidth: .25,
									border: false,
									items: [{
											fieldLabel: lang['chss_min'],
											name: 'EfChss',
											xtype: 'numberfield',
											validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
											maxLength:3
										}]
								}, {
									xtype: 'fieldset',
									autoHeight: true,
									labelWidth: "150px",
									columnWidth: .25,
									border: false,
									items: [{
											fieldLabel: lang['puls_ud_min'],
											name: 'EfPulse',
											xtype: 'numberfield',
											validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
											maxLength:3
										}]
								}
							]
						},

						{
							xtype: 'container',
							autoEl: {},
							layout: 'column',
							items:
							[
								{
									xtype: 'fieldset',
									autoHeight: true,
									labelWidth: "150px",
									columnWidth: .25,
									border: false,
									items: [{
											fieldLabel: lang['chd_min'],
											name: 'EfChd',
											xtype: 'numberfield',
											validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
											maxLength:3
										}]
								}, {
									xtype: 'fieldset',
									autoHeight: true,
									labelWidth: "150px",
									columnWidth: .25,
									border: false,
									items: [{
											fieldLabel: lang['pulsoksimetriya'],
											name: 'EfPulsks',
											xtype: 'numberfield',
											validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
											maxLength:3
										}]
								}, {
									xtype: 'fieldset',
									autoHeight: true,
									labelWidth: "150px",
									columnWidth: .25,
									border: false,
									items: [{
										fieldLabel: lang['glyukometriya'],
										name: 'EfGluck',
										xtype: 'textfield',
										plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
									}]
								}
							]
						}

					/*{
						xtype: 'fieldset',
						autoHeight: true,
						labelWidth: "150px",
						items: [{
								fieldLabel: lang['ad_mm_rt_st'],
								name: 'EfAD',
								xtype: 'textfield'
							}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						labelWidth: "150px",
						items: [{
								fieldLabel: lang['chss_min'],
								name: 'EfChss',
								xtype: 'textfield'
							}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						labelWidth: "150px",
						items: [{
								fieldLabel: lang['puls_ud_min'],
								name: 'EfPulse',
								xtype: 'textfield'
							}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						labelWidth: "150px",
						items: [{
								fieldLabel: lang['temperatura'],
								name: 'EfTemperature',
								xtype: 'textfield'
							}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						labelWidth: "150px",
						items: [{
								fieldLabel: lang['chd_min'],
								name: 'EfChd',
								xtype: 'textfield'
							}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						labelWidth: "150px",
						items: [{
								fieldLabel: lang['pulsoksimetriya'],
								name: 'EfPulsks',
								xtype: 'textfield'
							}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						labelWidth: "150px",
						items: [{
								fieldLabel: lang['glyukometriya'],
								name: 'EfGluck',
								xtype: 'textfield'
							}]
					}*/]
			}
		];



		if ( this.hasUslugaViewFrame() ) {

			this.UslugaViewFrame = new sw.Promed.ViewFrame({
				id: 'CCCCS_CmpCallCardUslugaGrid',
				object: 'CmpCallCardUsluga',
				dataUrl: '/?c=CmpCallCard&m=loadCmpCallCardUslugaGrid',
				height: 200,
				autoLoadData: false,
				border: true,
				useEmptyRecord: false,
				stringfields: [
					{name: 'CmpCallCardUsluga_id', type: 'int', header: 'ID', key: true},
					{name: 'CmpCallCard_id', type: 'int', hidden: true},
					{name: 'UslugaComplex_id', type: 'int', hidden: true},
					{name: 'MedPersonal_id', type: 'int', hidden: true},
					{name: 'MedStaffFact_id', type: 'int', hidden: true},
					{name: 'Person_id', type: 'int', hidden: true},
					{name: 'PayType_id', type: 'int', hidden: true},
					{name: 'UslugaCategory_id', type: 'int', hidden: true},
					{name: 'UslugaComplex_id', type: 'int', hidden: true},
					{name: 'UslugaComplexTariff_id', type: 'int', hidden: true},
					{name: 'CmpCallCardUsluga_setDate', type: 'string', header: lang['data'], width: 120},
					{name: 'CmpCallCardUsluga_setTime', type: 'string', header: lang['vremya'], width: 120},
					{name: 'UslugaComplex_Code', type: 'string', header: lang['kod'], width: 160},
					{name: 'UslugaComplex_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand'},
					{name: 'CmpCallCardUsluga_Cost', type: 'int', header: lang['tsena']},
					{name: 'CmpCallCardUsluga_Kolvo', type: 'int', header: lang['kolichestvo']},
					{name: 'status', type: 'string', hidden: true}
				],
				actions: [
					{name:'action_add', handler: function(){this.openCmpCallCardUslgugaEditWindow('add')}.createDelegate(this)},
					{name:'action_edit', handler: function(){this.openCmpCallCardUslgugaEditWindow('edit')}.createDelegate(this)},
					{name:'action_view', hidden: true, handler: function(){this.openCmpCallCardUslgugaEditWindow('view')}.createDelegate(this)},
					{name:'action_delete', handler: function(){this.deleteCmpCallCardUslguga()}.createDelegate(this)},
					{name:'action_refresh', hidden: true, disabled: true},
					{name:'action_print', hidden: true, disabled: true}
				]
			});

			this.UslugaViewFrame.getGrid().getStore().on('add',function(store){
				this.UslugaViewFrame.ViewActions.action_edit.setDisabled((store.find('status',/added|edited|unchanged/) == -1))
				this.UslugaViewFrame.ViewActions.action_delete.setDisabled((store.find('status',/added|edited|unchanged/) == -1))
			}.createDelegate(this));

			this.UslugaViewFrame.getGrid().getStore().on('update',function(store){
				this.UslugaViewFrame.ViewActions.action_edit.setDisabled((store.find('status',/added|edited|unchanged/) == -1))
				this.UslugaViewFrame.ViewActions.action_delete.setDisabled((store.find('status',/added|edited|unchanged/) == -1))
				this.UslugaViewFrame.getGrid().getStore().filterBy(function(rec,ind){
					return (rec.get('status')!=='deleted');
				});
			}.createDelegate(this));

			procedure_fieds.push(new sw.Promed.Panel({
				autoHeight: true,
				border: true,
				collapsible: true,
				id: 'CCCCS_SMPUslugaPanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: ++panelNumber + ' ' + lang['uslugi'],
				items: [this.UslugaViewFrame]
			}));

		}

		if ( getGlobalOptions().region.nick.inlist(['pskov']) ) {
			procedure_fieds.push({
				xtype: 'panel',
				title: lang['ispolzovannoe_oborudovanie_na_meste_v_mashine'],
				autoHeight: true,
				frame: true,
				items: this.getEquipment()
			});
		}

		//Результат оказания скорой медицинской помощи:

		var result_fieds = [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				labelWidth: 500,
				items : [{
					fieldLabel: ++panelNumber + lang['soglasie_na_meditsinskoe_vmeshatelstvo'],
					hiddenName: 'isSogl',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: ++panelNumber + lang['otkaz_ot_meditsinskogo_vmeshatelstva'],
					hiddenName: 'isOtkazMed',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: ++panelNumber + lang['otkaz_ot_transportirovki_dlya_gospitalizatsii_v_statsionar'],
					hiddenName: 'isOtkazHosp',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: lang['otkaz_ot_podpisi'],
					hiddenName: 'isOtkazSign',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: lang['prichina_otkaza_ot_podpisi'],
					name: 'OtkazSignWhy',
					width: 90,
					xtype: 'textfield'
				}
				]
			},
			{
				xtype      : 'panel',
				title	   : ++panelNumber + lang['rezultat_okazaniya_skoroy_meditsinskoy_pomoschi'],
				frame	   : true,
				items      : [{
					columns: 3,
					vertical: true,
					width: '100%',
					allowBlank: false,
					disabledClass: 'field-disabled',
					xtype: 'radiogroup',
					cls: 'boxbgr',
					id: this.id+'_ResultId',
					items :this.getCombo('Result_id')
				}]
			}, {
				xtype      : 'panel',
				title	   : ++panelNumber + lang['bolnoy'],
				frame	   : true,
				items      : [{
					columns    : 2,
					layout	   : 'column',
					items : [{
						columns: 1,
						vertical: true,
						width: 400,
						listeners:
						{
							'change': function(rb,checked)
							{
								this.clearPatientComboxFields();

								Ext.getCmp('CMPCLOSE_ComboValue_110').hide();

								Ext.getCmp('CMPCLOSE_ComboValue_111').hide();
								Ext.get(Ext.DomQuery.selectNode(String.format('label[for="CMPCLOSE_ComboValue_111"]'))).hide();
								Ext.getCmp('CMPCLOSE_ComboValue_112').hide();


								if (checked.id=='CMPCLOSE_CB_110')
								{
									Ext.getCmp('CMPCLOSE_ComboValue_110').show();
									//Ext.getCmp('CMPCLOSE_ComboValue_110').setValue();
								}

								if (checked.id=='CMPCLOSE_CB_111')
								{
									Ext.getCmp('CMPCLOSE_ComboValue_111').show();
									Ext.get(Ext.DomQuery.selectNode(String.format('label[for="CMPCLOSE_ComboValue_111"]'))).show();
									//Ext.getCmp('CMPCLOSE_ComboValue_111').setValue();
									//Ext.getCmp('CMPCLOSE_ComboValue_111').allowBlank = false;
								} else {
									//Ext.getCmp('CMPCLOSE_ComboValue_111').allowBlank = true;
								}

								if (checked.id=='CMPCLOSE_CB_112')
								{
									Ext.getCmp('CMPCLOSE_ComboValue_112').show();
									//Ext.getCmp('CMPCLOSE_ComboValue_112').setValue();
								}

							}.createDelegate(this)
						},
						xtype      : 'radiogroup',
						cls: 'boxbgr',
						items : this.getCombo('Patient_id')
					}, {
						width: 400,
						xtype: 'fieldset',
						border: false,
						autoHeight: true,
						labelWidth : 100,
						frame	   : true,
						items: [{
								height: 50
						},{
							fieldLabel: lang['vyiberite_lpu'],
							name: 'ComboValue[111]',
							listWidth: 400,
							hiddenName: 'ComboValue[111]',
							id: 'CMPCLOSE_ComboValue_111',
							allowBlank: true,
							xtype: 'swlpucombo'
						}]
					}]
				}]
			}, {
				xtype      : 'panel',
				title	   : ++panelNumber + lang['sposob_dostavki_bolnogo_v_avtomobil_skoroy_meditsinskoy_pomoschi'],
				frame	   : true,
				items      : [{
						columns: 3,
						vertical: true,
						width: '100%',
						xtype: 'radiogroup',
						cls: 'boxbgr',
						items :this.getCombo('TransToAuto_id')
				}]
			},{
				xtype: 'panel',
				title: ++panelNumber + lang['rezultat_vyiezda'],
				frame: true,
				items: [{
					columns: 1,
					vertical: true,
					width: '100%',
					xtype: 'radiogroup',
					cls: 'boxbgr',
					id: this.id+'_ResultEmergencyTrip',
					allowBlank: false,
					items: this.getCombo('ResultUfa_id'),
					listeners: {
						scope: this,
						change: function( obj, checked ){
							this.hideResultUfaComboxFields();
							this.clearResultUfaComboxFields();
							if ( !checked ) {
								return;
							}

							var id = checked.getEl().dom.value,
								wrapper = checked.getEl().up('.x-panel-body');

							wrapper.select( '.ResultUfa-parent-' + id ).each(function(el){
								this.showField( Ext.getCmp( el.dom.id ) );
							}, this);
						}
					}
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++panelNumber + lang['kilometraj'],
				labelWidth : 100,
				frame	   : true,
				items : [{
					allowDecimals: true,
					allowNegative: false,
					fieldLabel: lang['kilometraj'],
					name: 'Kilo',
					xtype: 'numberfield',
					msgTarget: 'under'
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++panelNumber + lang['primechaniya'],
				labelWidth : 100,
				frame	   : true,
				items : [{
					fieldLabel: lang['primechaniya'],
					name: 'DescText',
					xtype: 'textarea',
					width: '90%'
				}]
			}
		];

        //Использование медикаментов
        this.DrugGrid = new sw.Promed.ViewFrame({
            actions: [
                {name: 'action_add', handler: function() { baseaj.DrugGrid.editGrid('add') }},
                {name: 'action_edit', handler: function() { baseaj.DrugGrid.editGrid('edit') }},
                {name: 'action_view', handler: function() { baseaj.DrugGrid.editGrid('view') }},
                {name: 'action_delete', handler: function() { baseaj.DrugGrid.deleteRecord() }},
                {name: 'action_refresh', hidden: true},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 125,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=CmpCallCard&m=loadCmpCallCardDrugList',
            height: 360,
            object: 'CmpCallCardDrug',
            editformclassname: 'swCmpCallCardDrugEditWindow',
            id: 'CCCCS_CmpCallCardDrugGrid',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: [
                { name: 'CmpCallCardDrug_id', type: 'int', header: 'ID', key: true },
                { name: 'state', type: 'string', header: 'state', hidden: true },
                { name: 'Contragent_id', hidden: true },
                { name: 'Lpu_id', hidden: true },
                { name: 'Org_id', hidden: true },
                { name: 'MedStaffFact_id', hidden: true },
                { name: 'CmpCallCardDrug_setDate', hidden: true },
                { name: 'CmpCallCardDrug_setTime', hidden: true },
                { name: 'LpuBuilding_id', hidden: true },
                { name: 'Storage_id', hidden: true },
                { name: 'Mol_id', hidden: true },
                { name: 'DrugPrepFas_id', hidden: true },
                { name: 'Drug_id', hidden: true },
                { name: 'DrugFinance_id', hidden: true },
                { name: 'WhsDocumentCostItemType_id', hidden: true },
                { name: 'CmpCallCardDrug_Cost', hidden: true },
                { name: 'CmpCallCardDrug_Kolvo', hidden: true },
                { name: 'GoodsUnit_id', hidden: true },
                { name: 'CmpCallCardDrug_KolvoUnit', type: 'float', header: 'Количество', width: 150 },
                { name: 'CmpCallCardDrug_Sum', hidden: true },
                { name: 'DocumentUc_id', hidden: true },
                { name: 'DocumentUcStr_id', hidden: true },
                { name: 'DocumentUcStr_oid', hidden: true },
                { name: 'PrepSeries_id', hidden: true },
                { name: 'GoodsUnit_Name', type: 'string', header: 'Ед.измерения', width: 150 },
                { name: 'Drug_Name', type: 'string', header: 'Наименование', id: 'autoexpand' }
            ],
            title: null,
            toolbar: true,
            onRowSelect: function(sm, rowIdx, record) {
                if (record.get('CmpCallCardDrug_id') > 0 && (Ext.isEmpty(record.get('DrugDocumentStatus_Code')) || record.get('DrugDocumentStatus_Code') == 1)) {
                    this.ViewActions.action_edit.setDisabled(false);
                    this.ViewActions.action_delete.setDisabled(false);
                } else {
                    this.ViewActions.action_edit.setDisabled(true);
                    this.ViewActions.action_delete.setDisabled(true);
                }
                this.ViewActions.action_view.setDisabled(Ext.isEmpty(record.get('CmpCallCardDrug_id')));
            },
            editGrid: function (action) {
                if (action == null) {
                    action = 'add';
                }

                var base_form = baseaj.FormPanel.getForm();
                var view_frame = this;
                var store = view_frame.getGrid().getStore();

                var Person_FIO = '';
                Person_FIO += base_form.findField('Fam').getValue()+' ';
                Person_FIO += base_form.findField('Name').getValue()+' ';
                Person_FIO += base_form.findField('Middle').getValue();

                var Age = base_form.findField('Age').getValue();
                var AgeType_id = base_form.getValues()['ComboCheck_AgeType_id'];
                if (!Ext.isEmpty(Person_FIO) && !Ext.isEmpty(Age)) {
                    Person_FIO += ', возраст: '+Age;
                    switch (AgeType_id) {
                        case '221': //Дни
                            Person_FIO += ' дней';
                            break;
                        case '220': //Месяцы
                            Person_FIO += ' месяцов';
                            break;
                        case '219': //Годы
                            Person_FIO += ' лет';
                            break
                    }
                }

                var EmergencyTeam_id = base_form.findField('EmergencyTeam_id').getValue();
                var EmergencyTeam_Name = base_form.findField('EmergencyTeamNum').getValue();

                if (action == 'add') {
                    var record_count = store.getCount();
                    if ( record_count == 1 && !store.getAt(0).get('CmpCallCardDrug_id') ) {
                        view_frame.removeAll({ addEmptyRecord: false });
                        record_count = 0;
                    }

                    var params = new Object();
                    //params.WhsDocumentUc_Date = wnd.form.findField('WhsDocumentUc_Date').getValue();
                    params.Person_FIO = Person_FIO;
                    params.EmergencyTeam_id = EmergencyTeam_id;
                    params.EmergencyTeam_Name = EmergencyTeam_Name;
                    params.CmpCallCardDrug_KolvoUnit = 1;

                    var a_date = base_form.findField('ArriveTime').getStringValue();
                    if (!Ext.isEmpty(a_date)) {
                        var a_date_arr = a_date.split(' ');
                        if (a_date_arr.length == 2) {
                            params.CmpCallCardDrug_setDate = a_date_arr[0];
                            params.CmpCallCardDrug_setTime = a_date_arr[1];
                        } else if (a_date.length > 16) {
                            params.CmpCallCardDrug_setDate = Ext.util.Format.date(a_date, 'd.m.Y');
                            params.CmpCallCardDrug_setTime = Ext.util.Format.date(a_date, 'H:i');
                        }
                    }

                    getWnd(view_frame.editformclassname).show({
                        owner: view_frame,
                        action: action,
                        params: params,
                        onSave: function(data) {
                            if ( record_count == 1 && !store.getAt(0).get('CmpCallCardDrug_id') ) {
                                view_frame.removeAll({ addEmptyRecord: false });
                            }
                            var record = new Ext.data.Record.create(view_frame.jsonData['store']);
                            view_frame.clearFilter();
                            data.CmpCallCardDrug_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
                            data.state = 'add';
                            store.insert(record_count, new record(data));
                            view_frame.setFilter();
                            view_frame.initActionPrint();
                        }
                    });
                }
                if (action == 'edit' || action == 'view') {
                    var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
                    if (selected_record.get('CmpCallCardDrug_id') > 0) {
                        var params = selected_record.data;
                        //params.WhsDocumentUc_Date = wnd.form.findField('WhsDocumentUc_Date').getValue();
                        params.Person_FIO = Person_FIO;
                        params.EmergencyTeam_id = EmergencyTeam_id;
                        params.EmergencyTeam_Name = EmergencyTeam_Name;

                        getWnd(view_frame.editformclassname).show({
                            owner: view_frame,
                            action: action,
                            params: params,
                            onSave: function(data) {
                                view_frame.clearFilter();
                                for(var key in data) {
                                    selected_record.set(key, data[key]);
                                }
                                if (selected_record.get('state') != 'add') {
                                    selected_record.set('state', 'edit');
                                }
                                selected_record.commit();
                                view_frame.setFilter();
                                view_frame.initActionPrint();
                            }
                        });
                    }
                }
            },
            deleteRecord: function(){
                var view_frame = this;
                var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
                if (selected_record.get('state') == 'add') {
                    view_frame.getGrid().getStore().remove(selected_record);
                } else {
                    selected_record.set('state', 'delete');
                    selected_record.commit();
                    view_frame.setFilter();
                    view_frame.initActionPrint();
                }
            },
            getChangedData: function(){ //возвращает новые и измненные показатели
                var data = new Array();
                this.clearFilter();
                this.getGrid().getStore().each(function(record) {
                    if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete') {
                        data.push(record.data);
                    }
                });
                this.setFilter();
                return data;
            },
            getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
                var dataObj = this.getChangedData();
                return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
            },
            clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
                this.getGrid().getStore().clearFilter();
            },
            setFilter: function() { //скрывает удаленные записи
                this.getGrid().getStore().filterBy(function(record){
                    return (record.get('state') != 'delete');
                });
            }
        });

        var drug_fields = [this.DrugGrid];

		if (getGlobalOptions().region.nick == 'pskov') result_fieds.push({
				xtype      : 'panel',
				title	   : ++panelNumber + lang['formirovanie_otchetnosti'],
				frame	   : true,
				items : [{
					columns: 2,
					layout	   : 'column',
					width:'100%',
					items: [{
						xtype: 'fieldset',
						border: false,
						autoHeight: true,
						width: 500,
						labelWidth : 100,
						items: [{
							comboSubject: 'CmpCloseCardWhereReported',
							disabledClass: 'field-disabled',
							fieldLabel: lang['kuda_soobscheno'],
							hiddenName: 'CmpCloseCardWhereReported_id',
							//allowBlank: false,
							width: 250,
							listWidth: 250,
							xtype: 'swcommonsprcombo'
						}, {
							fieldLabel: lang['kommentarii'],
							name: 'CmpCloseCard_Comm',
							xtype: 'textfield',
							width: '90%'
						}, {
							fieldLabel: lang['№_soobscheniya'],
							name: 'MessageNum',
							xtype: 'textfield',
							width: '90%'
						}]
					}, {
						xtype: 'fieldset',
						border: false,
						autoHeight: true,
						width: 500,
						labelWidth : 100,
						items: [{
							comboSubject: 'CmpCloseCardCause',
							disabledClass: 'field-disabled',
							fieldLabel: lang['prichina'],
							hiddenName: 'CmpCloseCardCause_id',
							//allowBlank: false,
							width: 250,
							listWidth: 250,
							xtype: 'swcommonsprcombo'
						}, {
							fieldLabel: lang['vremya_peredachi'],
							timeLabelWidth1: 150,
							name: 'CmpCloseCardWhere_DT',
							id: this.id+'_'+'CmpCloseCardWhere_DT',
							xtype: 'swtimefield'
						}, {
							fieldLabel: lang['fio_prinyavshego'],
							name: 'AcceptFio',
							xtype: 'textfield',
							width: 250
						}]
					}]
				}]
			});

		// ВКЛАДКИ. ОСНОВНАЯ ФОРМА


		var flds = [
			{name: 'Day_num'},
			{name: 'Year_num'},
			{name: 'Feldsher_id'},
			{name: 'StationNum'},
			{name: 'LpuBuilding_id'},
			{name: 'EmergencyTeamNum'},
			{name: 'EmergencyTeam_id'},
			{name: 'Person_id'},

			{name: 'EmergencyTeamSpec_id'},
			{name: 'LpuSection_id'},
//				{name: 'MedPersonal_id'},
			{name: 'MedStaffFactDoc_id'},
			{name: 'PayType_id'},

			{name: 'AcceptTime'},

			{name: 'Area_id'},
			{name: 'Town_id'},

			{name: 'City_id'},
			{name: 'Street_id'},
			{name: 'House'},
			{name: 'Korpus'},
			{name: 'Office'},
			{name: 'Room'},
			{name: 'Entrance'},
			{name: 'Level'},
			{name: 'CodeEntrance'},

			{name: 'Fam'},
			{name: 'Name'},
			{name: 'Middle'},
			{name: 'Age'},
			{name: 'Sex_id'},
			{name: 'AgeType_id2'},
			{name: 'SocStatusNick'},
			{name: 'SocStatus_id'},
			//{name: 'BirthType'},
			//{name: 'BirthDay'},

			//{name: 'Ktov'},
			{name: 'CmpCallerType_id'},
			{name: 'Phone'},
			//{name: 'FeldsherAcceptPskov'},
			{name: 'FeldsherAccept'},
			//{name: 'FeldsherTransPskov'},
			{name: 'FeldsherTrans'},
			{name: 'CallType_id'},
			{name: 'CmpCallCard_id'},
			{name: 'ARMType'},
			{name: 'CmpCloseCard_id'},

			{name: 'CallPovodNew_id'},
			//{name: 'CmpCallCard_IsNMP'},
			{name: 'CmpCloseCard_IsNMP'},
			//{name: 'AcceptTime'},
			{name: 'TransTime'},
			{name: 'GoTime'},
			{name: 'ArriveTime'},
			{name: 'TransportTime'},
			{name: 'ToHospitalTime'},
			{name: 'EndTime'},
			{name: 'BackTime'},
			{name: 'SummTime'},
			{name: 'Work'},
			{name: 'DocumentNum'},
			{name: 'CallType_id'},
			{name: 'CallPovod_id'},
			{name: 'isAlco'},
			{name: 'Complaints'},
			{name: 'Anamnez'},
			{name: 'isMenen'},
			{name: 'isAnis'},
			{name: 'isNist'},
			{name: 'isLight'},
			{name: 'isAcro'},
			{name: 'isMramor'},
			{name: 'isHale'},
			{name: 'isPerit'},

			{name: 'isSogl'},
			{name: 'isOtkazMed'},
			{name: 'isOtkazHosp'},

			{name: 'Urine'},
			{name: 'Shit'},
			{name: 'OtherSympt'},
			{name: 'WorkAD'},
			{name: 'AD'},
			{name: 'Pulse'},
			{name: 'Chss'},
			{name: 'Chd'},
			{name: 'Temperature'},
			{name: 'Pulsks'},
			{name: 'Gluck'},
			{name: 'LocalStatus'},
			{name: 'Ekg1Time'},
			{name: 'Ekg1'},
			{name: 'Ekg2Time'},
			{name: 'Ekg2'},
			{name: 'Diag_id'},
			{name: 'Diag_uid'},
			{name: 'Diag_sid'},
			{name: 'EfAD'},
			{name: 'EfChss'},
			{name: 'EfPulse'},
			{name: 'EfTemperature'},
			{name: 'EfChd'},
			{name: 'EfPulsks'},
			{name: 'EfGluck'},
			{name: 'Kilo'},
			{name: 'HelpPlace'},
			{name: 'HelpAuto'},
			{name: 'DescText'}
			//{name: 'FeldsherAcceptName'},
			//{name: 'FeldsherTransName'}
		];
		if (getGlobalOptions().region.nick == 'krym') {
			flds.push({name: 'CmpCloseCard_Epid'});
			flds.push({name: 'CmpCloseCard_Glaz'});
			flds.push({name: 'CmpCloseCard_GlazAfter'});
			flds.push({name: 'CmpCloseCard_e1'});
			flds.push({name: 'CmpCloseCard_m1'});
			flds.push({name: 'CmpCloseCard_v1'});
			flds.push({name: 'CmpCloseCard_e2'});
			flds.push({name: 'CmpCloseCard_m2'});
			flds.push({name: 'CmpCloseCard_v2'});
			flds.push({name: 'CmpCloseCard_Topic'});
		}
		if (getGlobalOptions().region.nick == 'pskov') {
			flds.push({name: 'CmpCloseCardCause_id'});
			flds.push({name: 'CmpCloseCardWhereReported_id'});
			flds.push({name: 'CmpCloseCard_Comm'});
			flds.push({name: 'MessageNum'});
			flds.push({name: 'AcceptFio'});
			flds.push({name: 'CmpCloseCardWhere_DT'});
			flds.push({name: 'Diag_add'});
			flds.push({name: 'isOtkazSign'});
			flds.push({name: 'OtkazSignWhy'});
			flds.push({name: 'DisStart'});
		}

		this.tabPanel = new Ext.TabPanel({
			name: 'CMPCLOSE_TabPanel',
			activeTab : 0,
			deferredRender: false,

			// Скролл внутри табов
			cls: 'x-tab-panel-autoscroll',
			// -/-

			defaults: {
				border: false
//				layout: 'form',
			},

			items: [
				{
					title: lang['1_informaciya_o_vizove'],
					id: 'CMPCLOSE_TabPanel_FirstShowedTab',
					items: person_fieds
				}, {
					title: lang['2_povod_k_vyizovu'],
					items: povod_fieds
				}, {
					title: lang['3_jalobyi_i_obyektivnyie_dannyie'],
					items: jalob_fieds
				}, {
					title: lang['4_diagnoz'],
					items: diagnoz_fieds
				}, {
					title: lang['5_manipulyatsii'],
					items: procedure_fieds
				}, {
					title: lang['6_rezultat'],
					items: result_fieds
				}, {
                    title: '<b>7.</b> Использование медикаментов',
                    items: drug_fields
                }
			]
		});

		this.FormPanel = new Ext.form.FormPanel({
//			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 220,
			layout: 'fit',
			region: 'center',
			reader: new Ext.data.JsonReader({ success: Ext.amptyFn }, flds),
			url: '/?c=CmpCallCard&m=saveCmpStreamCard',

			items: [
			{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'AgeType_id2',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'SocStatusNick',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'CmpCallCard_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name: 'ARMType',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'Person_id',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'CmpCloseCard_id',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'CmpCloseCard_Street',
				value: '',
				xtype: 'hidden'
			},
			this.tabPanel]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function(){
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: BTN_FRMSAVE
			},{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function() {
					if ( !this.FormPanel.getForm().findField('Person_Surname').disabled ) {
						this.FormPanel.getForm().findField('Person_Surname').focus(true);
					}
				}.createDelegate(this),
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swCmpCallCardCloseStreamWindow.superclass.initComponent.apply(this, arguments);
		
		var diagField = Ext.getCmp(this.id+"_Diag_id");
		
		diagField.addListener('select', function(combo, select_item) {
			if (getGlobalOptions().region.nick == 'perm') {
				var diag_uid = Ext.getCmp(baseaj.id+'_Diag_uid');
				//var	level_not_two = true;

				if(select_item.get('DiagLevel_id') == 3){
					diag_uid.setDisabled(false);
					diag_uid.Diag_level3_code =  select_item.get('Diag_Code');
					diag_uid.doQuery(); //обновляем данные поля "Уточненный диагноз"
				}else{
					diag_uid.clearValue();
					diag_uid.setDisabled(true);
				}

			}
		})	
		
		diagField.addListener('change', function(combo, newValue, oldValue) {
			if(newValue.length == 0){
				var diag_uid = Ext.getCmp(baseaj.id+'_Diag_uid');
				diag_uid.clearValue();
				diag_uid.setDisabled(true);
			}
		});

		var diagUidField = Ext.getCmp(this.id+"_Diag_uid");

		diagUidField.addListener('focus', function() {
			if(this.getValue().length > 0){
				this.getStore().reload();
			}
		});
	},

	hideField: function(field){
		// typical elements
		if ( field.getEl().up('.x-form-item') ) {
			field.disable();// for validation
			field.hide();
			field.getEl().up('.x-form-item').setDisplayed(false); // hide label
		}
		// date with time elements
		else {
			// @todo add child disable for validation
			field.hide();
		}
	},

	showField: function(field){
		// typical elements
		if ( field.getEl().up('.x-form-item') ) {
			field.enable();
			field.show();
			field.getEl().up('.x-form-item').setDisplayed(true);// show label
		}
		// date with time elements
		else {
			field.show();
		}
	},

	// Скрываем необходимые поля на вкладке "Результат выезда"
	hideResultUfaComboxFields: function(){
		// Список идентификаторов из CmpCloseCardCombo
		var fields_hide = [241,242,243,244,245,246,247,248];
		for( var i=0,cnt=fields_hide.length; i<cnt; i++ ){
			var field = Ext.getCmp('CMPCLOSE_ComboValue_'+fields_hide[i]);
			if ( field ) {
				this.hideField( field );
			}
		}
	},

	clearResultUfaComboxFields: function(){
		var fields_hide = [241,242,243,244,245,246,247,248];
		for( var i=0,cnt=fields_hide.length; i<cnt; i++ ){
			var field = Ext.getCmp('CMPCLOSE_ComboValue_' + fields_hide[i] );
			if ( field ) {
				//log(field.xtype,' было ',field.getValue());
				this.clearField( field );
				//log(field.xtype,' стало ',field.getValue());
				//log(field);
			}
		}
	},

	clearPatientComboxFields: function(){
		var fields_hide = [110,111,112];
		for( var i=0,cnt=fields_hide.length; i<cnt; i++ ){
			var field = Ext.getCmp('CMPCLOSE_ComboValue_' + fields_hide[i] );
			if ( field ) {
				this.clearField( field );
			}
		}
	},

	clearField: function(field){
		if ( typeof field === 'string' ) {
			field = this.FormPanel.getForm().findField( field );
		}
		if ( !field ) {
			log(lang['nevozmojno_ochistit_pole'],field);
			return;
		}
		if (field.xtype == 'swdatetimefield') {
			document.getElementById(field.name).value = null;
		}
		if (field.xtype == 'swlpucombo') {
			field.setValue(null);
		} else {
			field.setValue();
		}
	},

	show: function() {
		sw.Promed.swCmpCallCardCloseStreamWindow.superclass.show.apply(this, arguments);

		this.doLayout();
		// @todo Разобраться почему окно не максимизируется если просто указать параметр maximized = true
		this.maximize();

		var those = this,
			base_form = this.FormPanel.getForm();
		base_form.reset();

        this.DrugGrid.setParam('CmpCallCard_id', null, true);
        this.DrugGrid.removeAll();

		this.action = 'stream'; // по умолчанию форма поточного ввода
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if (arguments) {
			if (arguments[0] && arguments[0].action) {
				this.action = arguments[0].action;
			}
			if (arguments[0] && arguments[0].callback) {
				this.callback = arguments[0].callback;
			}
			if (arguments[0] && arguments[0].onHide) {
				this.onHide = arguments[0].onHide;
			}
		}

		this.showLoadMask(LOAD_WAIT);

		var params = {};

		if (this.hasUslugaViewFrame()) {
			this.UslugaViewFrame.getGrid().getStore().removeAll();
		}

		this.ARMType = 'smpadmin';

		params.CmpCallCard_id = '0';

		this.toggleVal('153',false);
		this.toggleVal('141',false);
		this.toggleVal('110',false);
		this.toggleVal('111',false);
		this.toggleVal('112',false);
		//this.toggleAll('241',false);
		
		if (!getRegionNick().inlist(['kz'])){
			Ext.getCmp('CMPCLOSE_ComboValue_241').getStore().load();
			Ext.getCmp('CMPCLOSE_ComboValue_244').getStore().load();
		}

		if (!getGlobalOptions().region.nick.inlist(['kareliya', 'ekb'])) {
			var SpecField = this.FormPanel.getForm().findField('LpuSection_id');
			SpecField.allowBlank = true;
			SpecField.hide();
			SpecField.getEl().up('.x-form-item').setDisplayed(false);
		}

		var pt = this.FormPanel.getForm().findField( 'PayType_id' );
		if (getRegionNick().inlist(['perm','ekb'])){
			this.hideField(pt);
		} else {
			// Установка значения "ОМС" по умолчанию
			if ( Ext.isEmpty(pt.getValue()) ) {
				pt.setFieldValue('PayType_SysNick', 'oms');
			}
		}

		if (getGlobalOptions().region.nick == 'pskov') {
			//var SpecField = this.FormPanel.getForm().findField('CmpCallCard_IsNMP');
			var SpecField = this.FormPanel.getForm().findField('CmpCloseCard_IsNMP');
			SpecField.hide();
			SpecField.getEl().up('.x-form-item').setDisplayed(false);
		} else {
			var SpecField = this.FormPanel.getForm().findField('isOtkazSign');
			SpecField.hide();
			SpecField.getEl().up('.x-form-item').setDisplayed(false);
			var SpecField = this.FormPanel.getForm().findField('OtkazSignWhy');
			SpecField.hide();
			SpecField.getEl().up('.x-form-item').setDisplayed(false);
			var SpecField = this.FormPanel.getForm().findField('Diag_add');
			SpecField.hide();
			SpecField.getEl().up('.x-form-item').setDisplayed(false);
			var SpecField = this.FormPanel.getForm().findField('DisStart');
			SpecField.hide();
			SpecField.getEl().up('.x-form-item').setDisplayed(false);
		}


		if (getGlobalOptions().region.nick == 'astra') {

			this.FormPanel.getForm().findField('isSogl').hide().getEl().up('.x-form-item').setDisplayed(false);

			this.FormPanel.getForm().findField('isOtkazMed').hide().getEl().up('.x-form-item').setDisplayed(false);

			this.FormPanel.getForm().findField('isOtkazHosp').hide().getEl().up('.x-form-item').setDisplayed(false);

			//this.FormPanel.getForm().findField('CmpCallCard_IsNMP').hide().getEl().up('.x-form-item').setDisplayed(false);
			this.FormPanel.getForm().findField('CmpCloseCard_IsNMP').hide().getEl().up('.x-form-item').setDisplayed(false);
//
//			Ext.get(Ext.DomQuery.selectNode(String.format('label[for="'+this.id+'MedPersonal_id"]'))).hide();
//			Ext.get(Ext.DomQuery.selectNode(String.format('label[for="'+this.id+'MedPersonal_id"]'))).setHeight('0');
//			Ext.getCmp(this.id+'MedPersonal_id').hide();
//			Ext.getCmp(this.id+'MedPersonal_id').setHeight('0');
		}
			//this.FormPanel.getForm().findField('Diag_uid').hide().getEl().up('.x-form-item').setDisplayed(false);

//		if (getGlobalOptions().region.nick == 'pskov') {
//			Ext.get(Ext.DomQuery.selectNode(String.format('label[for="idCallPovod_id"]'))).hide();
//			Ext.get(Ext.DomQuery.selectNode(String.format('label[for="idCallPovod_id"]'))).setHeight('0');
//			Ext.getCmp('idCallPovod_id').hide();
//			Ext.getCmp('idCallPovod_id').setHeight('0');
//		} else {
			Ext.get(Ext.DomQuery.selectNode(String.format('label[for="CallPovodNew_id"]'))).hide();
			Ext.get(Ext.DomQuery.selectNode(String.format('label[for="CallPovodNew_id"]'))).setHeight('0');
			Ext.getCmp('CallPovodNew_id').hide();
			Ext.getCmp('CallPovodNew_id').setHeight('0');
//		}

		// Скрываем необходимые поля на вкладке "Результат выезда"
		this.hideResultUfaComboxFields();

		//Ext.getCmp('CMPCLOSE_TabPanel_id').setActiveTab('CMPCLOSE_TabPanel_FirstShowedTab');

		this.enableEdit(true);

		var opts = getGlobalOptions();
		if( base_form.findField('Area_id').getValue() != null ) {
			base_form.findField('Area_id').getStore().load({
				params: {
					region_id:opts.region.number
				},
				callback: function() {
					base_form.findField('Area_id').setValue(base_form.findField('Area_id').getValue());
				}
			})
		}


		if( base_form.findField('City_id').getValue() != null ) {
			base_form.findField('City_id').getStore().load({
				params: {
					subregion_id: opts.region.number
				},
				callback: function() {
					base_form.findField('City_id').setValue(base_form.findField('City_id').getValue());
				}
			})
		}

		// Фильтруем врачей, привязанных к отделениям, которые привязаны к СМП
		// (LpuUnitType=12) в этом случае будет корректно выбираться отделение
		// в списке? а так же при выборе отделений, будет оставаться корректный
		// список врачей
		// @todo Возможно понадобится дополнительная фильтрация врачей
		// привязанных непосредственно к службе СМП (MedServiceType=19)
		setMedStaffFactGlobalStoreFilter({
			arrayLpuUnitType: [12],
			onDate: getGlobalOptions().date // не уволены
		});

		base_form.findField('MedStaffFactDoc_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		// Фильтруем отделения которые привязаны к службе СМП
		setLpuSectionGlobalStoreFilter({
			arrayLpuUnitType: [12]
		});

		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

        //загрузка грида "Использование медикаментов"
        if (base_form.findField('CmpCallCard_id').getValue() > 0) {
            this.DrugGrid.setParam('CmpCallCard_id', base_form.findField('CmpCallCard_id').getValue(), true);
            this.DrugGrid.loadData();
        }

        this.setTitle(WND_AMB_CCCEFCLOSE);
		var index;
		var record;
		//base_form.findField('ARMType').setValue(those.ARMType);
		base_form.findField('ARMType').setValue('smpadmin');
		base_form.clearInvalid();
		//this.getLpuAddressTerritory();

		// Событие для пересчета затраченного времени на выполнение вызова
		for( var i=0,cnt=this.time_fields.length; i<cnt; i++ ) {
			var item = this.time_fields[i];
			if ( typeof item.hiddenName === 'undefined' || ( item.name && item.name == 'SummTime' ) ) {
				continue;
			}
			this.FormPanel.getForm().findField( item.hiddenName ).addListener('change',function(){
				those.calcSummTime();
				those.loadEmergencyTeamsWorkedInATime();
			});
			
				
		}
		base_form.findField('FeldsherAcceptCall').getStore().load();
		base_form.findField('FeldsherAccept').getStore().load();
		base_form.findField('FeldsherTrans').getStore().load();

		if (getGlobalOptions().region.nick.inlist(['pskov','astra'])) {
			//base_form.findField('CallPovod_id').setFilterByDate();
			//base_form.findField('FeldsherAcceptPskov').getStore().load();
			//base_form.findField('FeldsherTransPskov').getStore().load();

			//base_form.findField('EmergencyTeamNum').validate();
			base_form.findField('LpuSection_id').validate();
			base_form.findField('FeldsherAcceptCall').validate();
			base_form.findField('PayType_id').validate();
			base_form.findField('Age').validate();
			base_form.findField('Sex_id').validate();
			base_form.findField('CallType_id').validate();

		}

		if(base_form.findField('LpuBuilding_id'))
			base_form.findField('LpuBuilding_id').validate();
		if(base_form.findField('EmergencyTeam_id'))
			base_form.findField('EmergencyTeam_id').validate();
		if(base_form.findField('EmergencyTeamNum'))
			base_form.findField('EmergencyTeamNum').validate();
		if(base_form.findField('EmergencyTeamSpec'))
			base_form.findField('EmergencyTeamSpec').validate();
		if(base_form.findField('MedStaffFactDoc_id'))
			base_form.findField('MedStaffFactDoc_id').validate();

		base_form.findField('Fam').validate();
		base_form.findField('Name').validate();
		base_form.findField('Age').validate();
		base_form.findField('psocial').validate();
		base_form.findField('Diag_id').enable();
		base_form.findField('Diag_id').reset();
		base_form.findField('Diag_id').validate();
		base_form.findField('AcceptTime').validate();
		base_form.findField('EndTime').validate();
		base_form.findField('ArriveTime').validate();
		base_form.findField('GoTime').validate();
		Ext.getCmp(this.id+"_ResultId").validate();
		Ext.getCmp(this.id+"_CmpCallType_id").validate();
		Ext.getCmp(this.id+"_ResultEmergencyTrip").validate();

		var dt = new Date();
		base_form.findField('AcceptTime').setValue(dt);

		if (getGlobalOptions().region.nick != 'pskov') {
			base_form.findField('TransTime').setValue(dt);
			base_form.findField('GoTime').setValue(dt);
			base_form.findField('ArriveTime').setValue(dt);
			base_form.findField('TransportTime').setValue(dt);
			base_form.findField('ToHospitalTime').setValue(dt);
			base_form.findField('EndTime').setValue(dt);
			base_form.findField('BackTime').setValue(dt);
		}

		// Фокус на первый видимый элемент
		var active = this.FormPanel.getFirstActiveField();
		if (active) {
			active.ensureVisible().focus();
		}

		this.getCmpCallCardNumber();
		this.hideLoadMask();
	},

	toggleVal: function(field, st) {
		if (st) {
			Ext.getCmp('CMPCLOSE_ComboValue_'+field).show();
			Ext.get(Ext.DomQuery.selectNode(String.format('label[for="CMPCLOSE_ComboValue_'+field+'"]'))).show();
		} else {
			if (Ext.getCmp('CMPCLOSE_ComboValue_'+field)) {
				Ext.getCmp('CMPCLOSE_ComboValue_'+field).hide();
				Ext.get(Ext.DomQuery.selectNode(String.format('label[for="CMPCLOSE_ComboValue_'+field+'"]'))).hide();
			}
		}
	},

	toggleHeight: function(field, st) {
		if (st) {
			Ext.getCmp('CMPCLOSE_ComboValue_'+field).setHeight('20');
			Ext.get(Ext.DomQuery.selectNode(String.format('label[for="CMPCLOSE_ComboValue_'+field+'"]'))).setHeight('20');
		} else {
			if (Ext.getCmp('CMPCLOSE_ComboValue_'+field)) {
				//var base_form = this.FormPanel.getForm();
				//base_form.findField('CMPCLOSE_ComboValue_'+field).setValue('');
				Ext.getCmp('CMPCLOSE_ComboValue_'+field).setHeight('0');
				Ext.get(Ext.DomQuery.selectNode(String.format('label[for="CMPCLOSE_ComboValue_'+field+'"]'))).setHeight('0');
			}
		}
	},

	toggleAll: function(field, st) {
		this.toggleVal(field, st);
		this.toggleHeight(field, st);
	},

	getComboRadio: function(field) {
		var res = this.allfields[field];
		if(res){
			res.push({
				boxLabel: lang['net'],
				name: field,
				value: null
			});
			
			 return res;			
		}		
		else {
			log('! radiobutton '+field+' нет в базе');
			//return (new Ext.form.Hidden({name: field}));
		}
		
	},

	getCombo: function(field) {
		//var tt = null;
//		$.ajax({
//			url: "/?c=CmpCallCard&m=getCombox",
//			async: false,
//			cache: false,
//			data: {combo_id: field}
//		}).done(function ( data ) {
//			tt = data;
//		});


	    //for (var i = 0; i < this.allfields.length; i++)
	      //  if (this.allfields[i][0] == field)
			//	return this.allfields[i][1];
		if(this.allfields[field])return this.allfields[field];
		else {
			log('! поля '+field+' нет в базе');
			return [(new Ext.form.Hidden({name: field}))];
		}

		//return JSON.parse(tt);
	},

	getEquipment: function(){
		var items = [],
			columns = 3;

		$.ajax({
			url: "/?c=CmpCallCard&m=loadCmpEquipmentCombo",
			async: false,
			cache: false
		}).done(function(data){
			data = JSON.parse(data);

			var col_length = Math.ceil( data.length / columns );
			var column = [];

			for( var i=0, cnt=data.length; i<cnt; i++ ){
				var item = data[i];
				column.push({
					layout: 'column',
					items: [
						{
							layout: 'form',
							labelWidth: 200,
							border: false,
							bodyStyle: 'background: transparent',
							items: [
								new Ext.form.NumberField({
									fieldLabel: item.CmpEquipment_Name,
									name: 'CmpEquipment[' + item.CmpEquipment_id + '][UsedOnSpotCnt]',
									value: '',
									allowDecimals: false,
									allowNegative: false,
									width: 50,
									validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}
								})
							]
						},{
							layout: 'form',
							labelWidth: 15,
							border: false,
							bodyStyle: 'background: transparent',
							items: [
								new Ext.form.NumberField({
									fieldLabel: '/',
									name: 'CmpEquipment[' + item.CmpEquipment_id + '][UsedInCarCnt]',
									value: '',
									allowDecimals: false,
									allowNegative: false,
									width: 50,
									validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}
								})
							]
						}
					]
				});

				if ( i>0 && ( (i%col_length) === 0 || cnt == (i+1) ) ) {
					items.push({
						layout: 'column',
						items: column
					});
				}
			}
		});

		return items;
	},

	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'Feldsher_id',
			'StationNum',
			'LpuBuilding_id',
			'EmergencyTeamNum',
			'AcceptTime',
			'Area_id',
			'Town_id',
			'City_id',
			'Street_id',
			'House',
			'Korpus',
			'Room',
			'Office',
			'Entrance',
			'Level',
			'CodeEntrance',
			'Fam',
			'Name',
			'Middle',
			'Age',
			'Sex_id',
			//'Ktov',
			'CmpCallerType_id',
			'Phone',
			'FeldsherAccept',
			//'FeldsherAcceptPskov',
			'FeldsherTrans',
			//'FeldsherTransPskov',
			'CallType_id',
			'CmpCallCard_id',
//			'CallPovodNew_id',
			//'AcceptTime',
			'TransTime',
			'GoTime',
			'ArriveTime',
			'TransportTime',
			'ToHospitalTime',
			'EndTime',
			'BackTime',
			'SummTime',
			'Work',
			'DocumentNum',
			'CallType_id',
			'CallPovod_id',
			'isAlco',
			'Complaints',
			'Anamnez',
			'isMenen',
			'isAnis',
			'isNist',
			'isLight',
			'isAcro',
			'isMramor',
			'isHale',
			'isPerit',

			'isSogl',
			'isOtkazMed',
			'isOtkazHosp',

			'Urine',
			'Shit',
			'OtherSympt',
			'WorkAD',
			'AD',
			'Pulse',
			'Chss',
			'Chd',
			'Temperature',
			'Pulsks',
			'Gluck',
			'LocalStatus',
			'Ekg1Time',
			'Ekg1',
			'Ekg2Time',
			'Ekg2',
			'Diag_id',
			//'Diag_uid',
			'Diag_sid',
			'EfAD',
			'EfChss',
			'EfPulse',
			'EfTemperature',
			'EfChd',
			'EfPulsks',
			'EfGluck',
			'Kilo',
			'HelpPlace',
			'HelpAuto',
			'DescText'
			//'FeldsherAcceptName',
			//'FeldsherTransName'
		);

		if (getGlobalOptions().region.nick == 'pskov') {
			form_fields.push('CmpCloseCardCause_id');
			form_fields.push('CmpCloseCardWhereReported_id');
			form_fields.push('CmpCloseCard_Comm');
			form_fields.push('MessageNum');
			form_fields.push('AcceptFio');
			form_fields.push('CmpCloseCardWhere_DT');
			form_fields.push('Diag_add');
			form_fields.push('isOtkazSign');
			form_fields.push('OtkazSignWhy');
			form_fields.push('DisStart');
		}

		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			} else {
				base_form.findField(form_fields[i]).disable();
			}
		}

//		checkboxGroupArray = Ext.getCmp('CMPCLOSE_TabPanel_id').findByType('checkboxgroup');
//		radioGroupArray = Ext.getCmp('CMPCLOSE_TabPanel_id').findByType('radiogroup');
		checkboxGroupArray = this.tabPanel.findByType('checkboxgroup');
		radioGroupArray = this.tabPanel.findByType('radiogroup');
		for ( i = 0; i < checkboxGroupArray.length; i++ ) {
			checkboxGroupArray[i].setDisabled(!enable);
		}
		for ( i = 0; i < radioGroupArray.length; i++ ) {
			radioGroupArray[i].setDisabled(!enable);
		}
		//base_form.findField('FeldsherAcceptName').disable();

	},

	//Фильтруем диагнозы (только для Карелии)
	setMKB: function(){

		//Карелия
		if (getGlobalOptions().region.number !== 10) {
			return;
		}
		var base_form = this.FormPanel.getForm(),
			ageFieldValue = base_form.findField('Age').getValue(),
			AgeType_id = base_form.getValues()['AgeType_id'],
			sex_id = base_form.findField('Sex_id').getValue(),
			age;
		switch (AgeType_id) {
			case '221': //Дни
				age = Math.round(ageFieldValue/365);
				break;
			case '220': //Месяцы
				age = Math.round(ageFieldValue/12);
				break;
			case '219': //Годы
			default:
				age = ageFieldValue;
				break;
		}
		base_form.findField('Diag_id').setMKBFilter(age,sex_id,true);
	}
});
