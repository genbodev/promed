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
	id: 'swCmpCallCardCloseStreamWindow',
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
					sw.swMsg.alert('Ошибка', 'Ошибка при определении номера вызова');
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
			sw.swMsg.alert('Сообщение', 'Окно поиска человека уже открыто');
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
					//findField('EdNum').setValue( person_data.Polis_EdNum );
					//findField('EdNum').getEl().dom.setAttribute('readOnly', true);
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
		base_form.findField('Fam').setValue('Неизвестен');
		base_form.findField('Name').setValue('Неизвестен');
		base_form.findField('Middle').setValue('Неизвестен');		
		Ext.getCmp('psocial').allowBlank = true;
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
	},
	save_form: function( base_form, params_out){
		
		this.showLoadMask('Подождите, идет закрытие карты вызова...');
		
		base_form.submit({
			failure: function(result_form, action) {
				this.hideLoadMask();
				this.formStatus = 'edit';
				if ( action.result ) {					
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При закрытии произошли ошибки [Тип ошибки: 1]');
					}
				} else {
					sw.swMsg.alert('Ошибка', 'При закрытии произошли ошибки [Тип ошибки: 2]');
				}
			}.createDelegate(this),
			params: params_out,
			success: function(result_form, action) {
				this.hideLoadMask();
//				this.formStatus = 'edit';

				if ( action.result ) {
					if ( action.result.CmpCloseCard_id > 0 ) {
						this.callback({
							CmpCloseCard_id: action.result.CmpCloseCard_id
						});
						this.hide();

						if (this.action == 'stream') {
							this.show();

							var mb = Ext.Msg.show({
								title: 'Сообщение',
								msg: 'Карта вызова сохранена',
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
							sw.swMsg.alert('Ошибка', action.result.Error_Msg);
						} else {
							sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
						}
					}
				} else {
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}.createDelegate(this)
		});
	},


	selectLpuTransmit: function(CmpLpuId) {

		var base_form = this.FormPanel.getForm();
				
//		comboLpuTrnsimit = base_form.findField('Lpu_ppdid');
//		emptyRecord = new Ext.data.Record({
//			Lpu_id: null,
//			Lpu_Name: '',
//			Lpu_Nick: '&nbsp'
//		});
//		//убираем возникновение &nbsp
//		if(comboLpuTrnsimit.getValue() == null)
//			{
//				comboLpuTrnsimit.setValue('');
//			}
//		//при первой загрузке и вьюхе не затираем поле лпу передачи, но вставляем пустое поле
//		if ( (this.action == 'view') ||
//			(CmpLpuId == 'first') && (this.action == 'edit') )
//		{
//			//this.FormPanel.getForm().findField('Lpu_ppdid').setDisabled(true);
//			if (comboLpuTrnsimit.getStore().getAt(0))
//			{if ( (comboLpuTrnsimit.getStore().getAt(0).get('Lpu_id') != '' ) && (comboLpuTrnsimit.getStore().getAt(0).get('Lpu_id') != null ) )
//			{
//				comboLpuTrnsimit.getStore().insert(0, [emptyRecord]);
//			}}
//			return false;
//		}
		var baseForm = this.FormPanel.getForm(),
			cmpFieldReasonCode = baseForm.findField('CmpReason_id'),
			store = cmpFieldReasonCode.getStore(),
			flagOrArrayCodesIsNMP = false,
			value = cmpFieldReasonCode.getValue(),
			idx = store.findBy(function(rec) { return rec.get('CmpReason_id') == value; });


		if(idx != -1 && idx != undefined)
		{
			var	code = store.getAt(idx).get('CmpReason_Code');
			flagOrArrayCodesIsNMP = code.inlist(['04Г','04Д','09Я','11Л','11Я','12Г','12К','12Р','12У','12Э','12Я','13Л','13М','15Н','17А','13С','40Ц']);
		}

		// this.FormPanel.getForm().findField('CmpReason_id').getValue().inlist([541, 542, 595, 606, 609, 613, 616, 618, 619, 620, 621, 629, 630, 644, 632, 689])

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
			Lpu_Nick: 'Показать все...'
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
		//base_form.findField('SummTime').setValue( hours + ':' + minutes );
	},
	
	DocumentUc_id: null,
	
	doValidate: function( base_form ){
		var has_error = false,
			error = '',
			diagField = base_form.findField('Diag_id');
			
		if
		( Ext.getCmp('CMPCLOSE_CB_231').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_232').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_233').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_234').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_235').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_236').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_237').getValue()
		|| Ext.getCmp('CMPCLOSE_CB_238').getValue() ) {		
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
		
		if ( !base_form.isValid() ) {
			has_error = true;
			error = ERR_INVFIELDS_MSG;			
		} else {
			var d1 = new Date(base_form.findField('AcceptTime').value),
				d2 = new Date(base_form.findField('TransTime').value),
				d3 = new Date(base_form.findField('GoTime').value),
				d4 = new Date(base_form.findField('ArriveTime').value),
				d5 = new Date(base_form.findField('TransportTime').value),
				d6 = new Date(base_form.findField('TransportTime').value),
				//d6 = new Date(base_form.findField('ToHospitalTime').value),
				d7 = new Date(base_form.findField('EndTime').value),
				d8 = new Date(base_form.findField('BackTime').value);

			if ( !d1.isValid() ) {
				error += (error.length?'<br />':'') + 'Не указана дата приёма вызова';
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
				if ( !confirm('Данный пациент не обнаружен в базе данных пациентов РМИАС. Для оплаты карты вызова СМП, пациента необходимо добавить в базу данных пациентов РМИАС. Продолжить сохранение?') )
				{
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

		params.saveActive = false;
		
		if (
			base_form.findField('Person_id').getValue()
			&& base_form.findField('ComboValue[111]').getValue()
			&&(!base_form.findField('CmpCloseCard_id').getValue())
		)
		{
			if ( confirm('Сохранить информацию об активе в карте СМП ?') ){
				params.saveActive = true;
			}
		}
		
		if(params.saveActive){
			var KLAreaStat_idEdit = base_form.findField('KLAreaStat_idEdit').getValue();
			var City_id = base_form.findField('City_id').getValue();
			var Street_id = base_form.findField('Street_id').getValue();
			var House = base_form.findField('House').getValue();
			var Office = base_form.findField('Office').getValue();
			if(!KLAreaStat_idEdit || !City_id || !House || !Street_id){
				sw.swMsg.alert('При передаче актива в поликлинику необходимо ввести полный адрес посещения, включая территорию, страну, город, улицу, дом');
				return false;
			}
		}
		
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
		
		params.Diag_uid = base_form.findField('Diag_uid').getValue() || '';
					
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
			//base_form.findField('EndTime').setValue(base_form.findField('TransportTime').getValue());
		}
//		if (base_form.findField('ToHospitalTime').getValue().length > 16) {
//			base_form.findField('ToHospitalTime').setValue( Ext.util.Format.date(base_form.findField('ToHospitalTime').getValue(), 'd.m.Y H:i'));
//		}
		if (base_form.findField('EndTime').getValue().length > 16) {
			base_form.findField('EndTime').setValue( Ext.util.Format.date(base_form.findField('EndTime').getValue(), 'd.m.Y H:i'));
		}
		if (base_form.findField('BackTime').getValue().length > 16) {
			base_form.findField('BackTime').setValue( Ext.util.Format.date(base_form.findField('BackTime').getValue(), 'd.m.Y H:i'));		
		}
		
		if (base_form.findField('Mensis_DT').getValue().length > 16) {
			base_form.findField('Mensis_DT').setValue( Ext.util.Format.date(base_form.findField('Mensis_DT').getValue(), 'd.m.Y H:i'));
		}

		if (base_form.findField('Bad_DT').getValue().length > 16) {
			base_form.findField('Bad_DT').setValue( Ext.util.Format.date(base_form.findField('Bad_DT').getValue(), 'd.m.Y H:i'));
		}

		//this.calcSummTime();
		
		params.AcceptDT = base_form.findField('AcceptTime').getValue();
		params.TransDT = base_form.findField('TransTime').getValue();
		params.GoDT = base_form.findField('GoTime').getValue();
		params.ArriveDT = base_form.findField('ArriveTime').getValue();
		params.TransportDT = base_form.findField('TransportTime').getValue();
//		params.ToHospitalDT = base_form.findField('ToHospitalTime').getValue();
		params.EndDT = base_form.findField('EndTime').getValue();
		params.BackDT = base_form.findField('BackTime').getValue();
		
		params.ARMType = base_form.findField('ARMType').getValue();	
		
		if (this.hasUslugaViewFrame()) {
			var usluga_items = this.UslugaViewFrame.getGrid().getStore().query('CmpCallCardUsluga_id',/[^0]/).items;

			var usluga_data_array = [];

			for (var i = 0; i < usluga_items.length; i++) {
				usluga_data_array.push(usluga_items[i].data);
			};

			params.usluga_array = JSON.stringify(usluga_data_array);
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

//		if (!Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue())) {
//			params.MedPersonal_id = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id');
//		}
		
		switch (action) {
			
			case 'add':
				params.callback = function(){
					return this.addCmpCallCardUslugaGridRec.apply(this,arguments);
				}.createDelegate(this);
				params.formParams.CmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();
				params.formParams.Person_id = base_form.findField('Person_id').getValue();
				//params.formParams.CmpCallCardUsluga_setDate = AcceptTime.format('d.m.Y');
				//params.formParams.CmpCallCardUsluga_setTime = AcceptTime.format('H:i');
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
		this.showLoadMask('Назначение...');
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
		dateLabel: 'Время приема вызова',
		hiddenName: 'AcceptTime',
		allowBlank: false,
		xtype: 'swdatetimefield'
	},{
		dateLabel: 'Время передачи',
		hiddenName: 'TransTime',
		xtype: 'swdatetimefield'
	},{
		dateLabel: 'Время выезда на вызов',
		hiddenName: 'GoTime',
		xtype: 'swdatetimefield'
	},{
		dateLabel: 'Время прибытия к больному',
		hiddenName: 'ArriveTime',
		xtype: 'swdatetimefield'
	},{
		dateLabel: 'Время убытия от больного',
		hiddenName: 'TransportTime',
		allowBlank: false,
		onChange: function(field, newValue){
			//Ext.getCmp('swCmpCallCardCloseStreamWindow').FormPanel.getForm().findField('EndTime').setValue(newValue);
		}.createDelegate(this),
		xtype: 'swdatetimefield'
	},{
//		dateLabel: 'Прибытия в медицинскую организацию',
//		hiddenName: 'ToHospitalTime',
//		xtype: 'swdatetimefield'
//	},{
		dateLabel: 'Время отзвона',
		hiddenName: 'EndTime',
		name: 'EndTime',
		//readOnly: true,
		xtype: 'swdatetimefield'
	},{
		dateLabel: 'Время возвращения на подстанцию',
		hiddenName: 'BackTime',
		xtype: 'swdatetimefield'
	},{
		fieldLabel: 'Затраченное на выполнения вызова (считается автоматически)',	
		name: 'SummTime',
		width: 90,
		readOnly: true,
		xtype: 'hidden'
	}],

	/**
	 * Запоминаем предыдущее значение, чтобы подставить при обновлении комбобокса с бригадами
	 */
	updatedEmergencyTeamIdValue: null,

	/**
	 * Обновление комбобокса бригад в зависимости от времени передачи
	 */
	updateEmergencyTeamOnTransTimeChange: function(){
		if ( this.formStatus == 'save' ) {
			return false;
		}

		var form = this.FormPanel.getForm(),
			TransTime = form.findField('TransTime'),
			EmergencyTeam_id = form.findField('EmergencyTeam_id');
		
		if (TransTime.getValue()) {
			// Запоминаем текущее значение, чтобы установить его после обновления данных в хранилище
			this.updatedEmergencyTeamIdValue = EmergencyTeam_id.getValue();
			var TransTimeValue = Ext.util.Format.date(TransTime.value, 'd.m.Y H:i');
			EmergencyTeam_id.getStore().load({
				params: {
					TransTime: TransTimeValue
				},
				callback: function(records, options, success){
					// Если после обновления, в хранилище есть предыдущее значение, устанавливаем его
					if (EmergencyTeam_id.getStore().getById(this.updatedEmergencyTeamIdValue)) {
						EmergencyTeam_id.setValue(this.updatedEmergencyTeamIdValue);
					} else {
						EmergencyTeam_id.clearValue();
					}
				},
				scope: this
			});
			EmergencyTeam_id.setDisabled(false);
		} else {
			EmergencyTeam_id.clearValue();
			EmergencyTeam_id.getStore().loadData([],false);
		}
	},
	
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
			EmergencyTeamIdField;
			
		
		//regional crutches
		switch(getGlobalOptions().region.nick){			
			default:{
				FeldsherIdField = new Ext.form.Hidden({
					fieldLabel: 'Фельдшер по приему вызова',
					name: 'Feldsher_id'
				});
				
				FeldsherAcceptCallField = new Ext.form.Hidden({
					fieldLabel: 'Фельдшер по приему вызова',
					name: 'FeldsherAcceptCall',
					width: 250,
					xtype: 'swmedpersonalcombo',
					hiddenName: 'FeldsherAcceptCall',
					listeners: {
//						render: function(){
//							this.getStore().load();
//						},
//						select: function(combo,record,index){
//							var appendCombo = baseaj.FormPanel.getForm().findField('FeldsherAccept');
//							if(appendCombo)appendCombo.setValue(combo.getValue());
//						}.createDelegate(this)
					}
				});
				EmergencyTeamNumField = function(num){
					return new Ext.form.Hidden({										
						name: 'EmergencyTeamNum'
					});
				};
				EmergencyTeamIdField = function(num){
					return new sw.Promed.swEmergencyTeamCCC({
						fieldLabel:	num + '. Бригада скорой медицинской помощи',
						name: 'EmergencyTeam_id',		
						allowBlank: true,
						listWidth: 400,
						listeners: {
							select: function(combo,record,index){
								var EmergencyTeamNum = baseaj.FormPanel.getForm().findField('EmergencyTeamNum'),							
									EmergencyTeamSpecCombo = Ext.getCmp('EmergencyTeamSpec');
									
								if(EmergencyTeamNum) EmergencyTeamNum.setValue(record.get('EmergencyTeam_Num'));
								
								// EmergencyTeamSpecCombo мб hidden у которого нет метода findRecord
								if(EmergencyTeamSpecCombo && (!EmergencyTeamSpecCombo.xtype || EmergencyTeamSpecCombo.xtype !== 'hidden')) {
									var recSpec = EmergencyTeamSpecCombo.findRecord('EmergencyTeamSpec_Code', record.get('EmergencyTeamSpec_Code'));
									EmergencyTeamSpecCombo.setValue(recSpec.get('EmergencyTeamSpec_id'));
								}
							}.createDelegate(this)
						}
					});
				}
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
								items: [
									{
										autoCreate: {tag: "input", type: "text", size: "20", autocomplete: "off",  maxlength: '12'},
										fieldLabel: 'Номер вызова за день',
										maxLength: 12,
										name: 'Day_num',
										allowBlank: false,
										xtype: 'textfield',
										maskRe: /\d/
									}, {
										autoCreate: {tag: "input", type: "text", size: "20", autocomplete: "off",  maxlength: '12'},
										fieldLabel: 'Номер вызова за год',
										maxLength: 12,
										name: 'Year_num',
										allowBlank: false,
										xtype: 'textfield',
										maskRe: /\d/
									}
								]
							}
						]
					},
					{
						title: ++panelNumber + '. Время',
						xtype: 'fieldset',
						autoHeight: true,
						items: this.time_fields
					},
					{
						border: false,
						layout: 'column',
						style: 'padding: 0px;',
						items: [
							{
								border: false,
								layout: 'form',
								style: 'padding: 0px',
								items: [
									FeldsherIdField,
									FeldsherAcceptCallField,
									{
										fieldLabel: 'Номер станции (подстанции), отделения',
										name: 'StationNum',
										xtype: 'hidden',										
										maskRe: /\d/
									}, {
										fieldLabel: ++panelNumber + '. Станция (подстанция), отделение',
										hiddenName:'LpuBuilding_id',
										disabledClass: 'field-disabled',
										width: 350,
										allowBlank: false,
										listWidth: 300,
										xtype: 'swsmpunitscombo',
										listeners: {
											select: function(combo,record,index){
												var base_form = me.FormPanel.getForm();
												base_form.findField('EmergencyTeam_id').getStore().load({
													params: {
														LpuBuilding_id: record.get('LpuBuilding_id')
													}
												});
											}.createDelegate(this)
										}
									},
									EmergencyTeamNumField(panelNumber),
									EmergencyTeamIdField(++panelNumber),
									{
										fieldLabel: 'Профиль бригады скорой медицинской помощи',
										//name: 'EmergencyTeamSpec',
										comboSubject: 'EmergencyTeamSpec',
										disabledClass: 'field-disabled',
										//hiddenName: 'EmergencyTeamSpec',
										id: 'EmergencyTeamSpec',
										width: 350,
										//allowBlank: false,
										listWidth: 300,
										autoLoad: true,
										editable: true,
										//xtype: 'swcustomobjectcombo'
										xtype: 'hidden'
									},{
										allowBlank: false,
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
//												this.FormPanel.getForm().findField('MedStaffFact_id').reset();
//												setMedStaffFactGlobalStoreFilter({
//													arrayLpuUnitType: [12],
//													LpuSection_id: record.get('LpuSection_id'),
//													onDate: getGlobalOptions().date // не уволены
//												});
//												this.FormPanel.getForm().findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
											}.createDelegate(this)
										}
									},{
										allowBlank: false,
										dateFieldId: 'EVPLEF_EvnVizitPL_setDate',
										enableOutOfDateValidation: true,
										hiddenName: 'MedStaffFact_id',
										name: 'MedStaffFact_id',
										id: 'CMP_MedStaffFactRecCombo',
										lastQuery: '',
										listWidth: 600,
										parentElementId: 'CMP_LpuSectionCombo',
										//tabIndex: TABINDEX_EVPLEF + 6,
										width: 350,
										//xtype: 'swmedstafffactglobalcombo',
										xtype: 'hidden',
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
								border: false,
								layout: 'form',
								width: 400,
								labelWidth: 100,
								items: [
									{
										//xtype: 'swpaytypecombo',
										xtype: 'hidden',
										name: 'PayType_id'
									}
								]
							}
						]
					},
					{
						handler: function() {
							var parentObject = this;

							getWnd('swSelectEmergencyTeamWindow').show({
								//CmpCallCard: parentObject.FormPanel.getForm().findField('CmpCallCard_id').getValue(),
								callback: function(data) {
									var base_form = parentObject.FormPanel.getForm();
									base_form.findField('EmergencyTeamNum').setValue(data.EmergencyTeam_Num);
									//parentObject.setEmergencyTeam(base_form.findField('CmpCallCard_id').getValue(), data.EmergencyTeam_id);
									base_form.findField('EmergencyTeam_id').getStore().load({
										callback: function() {
											//this.setValue(this.getValue());
											base_form.findField('EmergencyTeam_id').setValue(data.EmergencyTeam_id);
											//this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) { return rec.get('SubRGN_id') == this.getValue(); }.createDelegate(this))));
										}.createDelegate(this)
									});
								}

							});
						}.createDelegate(this),
						id: 'BrigSelectBtn',
						text: 'Выбрать',
						xtype: 'button'

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
					/*{
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
						}.createDelegate(this),						
						id: 'BrigSelectBtn',
						disabled: (getGlobalOptions().region.nick.inlist(['pskov'])),
						hidden: (getGlobalOptions().region.nick.inlist(['pskov'])),
						text: 'Выбрать',
						xtype: 'button'
					},*/ {							
						title : ++panelNumber + '. Адрес вызова',									
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
											
											if( record.get('KLRGN_id') != '' ) {
												base_form.findField('KLRgn_id').setValue(record.get('KLRGN_id'));
											}

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
									fieldLabel: 'Район',
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
									xtype: 'swstreetcombo',
									fieldLabel: 'Улица',
									hiddenName: 'Street_id',
									name: 'Street_id',
									width: 250,
									editable: true
								},
								
								{
									disabledClass: 'field-disabled',
									disabled: true,
									fieldLabel: 'Дом',
									//name: 'CmpCallCard_Dom',
									name: 'House',
									width: 100,
									xtype: 'textfield'
								}, {
									disabledClass: 'field-disabled',
									disabled: true,
									fieldLabel: 'Корпус',
									//name: 'CmpCallCard_Dom',
									name: 'Korpus',
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: 'Квартира',
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
									fieldLabel: 'Комната',
									//name: 'CmpCallCard_Kvar',
									name: 'Room',
									width: 100,
									xtype: 'hidden'
								}, {								
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: 'Подъезд',
									//name: 'CmpCallCard_Podz',
									name: 'Entrance',
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: 'Этаж',
									//name: 'CmpCallCard_Etaj',					
									name: 'Level',					
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: 'Код замка в подъезде (домофон)',
									//name: 'CmpCallCard_Kodp',			
									name: 'CodeEntrance',			
									width: 100,
									xtype: 'textfield'
								}								
						]
					}, {						
						title : ++panelNumber + '. Сведения о больном',												
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
										text: 'Поиск',
										xtype: 'button'
									},
									{
										handler: function() {
											this.personReset();
										}.createDelegate(this),
										iconCls: 'reset16',
										id: 'CCCSEF_PersonResetBtn',
										text: 'Сброс',
										xtype: 'button'
									},
									{
										handler: function() {
											this.personUnknown();
										}.createDelegate(this),
										iconCls: 'reset16',
										id: 'CCCSEF_PersonUnknownBtn',
										text: 'Неизвестен',
										xtype: 'button'
									}]
								}, {
									border: false,
									layout: 'form',
									items : [{
										fieldLabel: 'Фамилия',							
										//name: 'Person_Surname',							
										name: 'Fam',
										//hiddenName: 'Fam',
										toUpperCase: true,
										width: 180,//								
										allowBlank: false,
										xtype: 'textfieldpmw'
									}, {
										fieldLabel: 'Имя',
										//name: 'Person_Firname',
										name: 'Name',
										toUpperCase: true,
										allowBlank: false,
										width: 180,
										xtype: 'textfieldpmw'
									}, {
										fieldLabel: 'Отчество',
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
									fieldLabel: 'Возраст',
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
									fieldLabel: 'Единица измерения возраста',
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
								fieldLabel: 'Пол',
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
								fieldLabel: 'Место работы'
							}, {
								xtype: 'textfield',
								width: 180,
								name: 'DocumentNum',
								fieldLabel: 'Серия и номер документа, удостоверяющего личность'
							}, {
								xtype: 'textfield',
								width: 180,
								name: 'PolisSerial',
								fieldLabel: 'Серия полиса'
							}, {
								xtype: 'textfield',
								width: 180,
								name: 'PolisNum',
								fieldLabel: 'Номер полиса'
							}, {
								xtype: 'hidden',
								width: 180,
								name: 'EdNum',
								fieldLabel: 'Единый номер'
							},{
								valueField: 'Lpu_id',
								//allowBlank: false,
								//disabled: true,
								autoLoad: true,
								width: 350,
								listWidth: 350,
								fieldLabel: 'ЛПУ передачи',
								disabledClass: 'field-disabled',
								hiddenName: 'Lpu_ppdid',
								displayField: 'Lpu_Nick',
								medServiceTypeId: 18,
								handler: function() {
									this.selectLpuTransmit();
								}.createDelegate(this),
								comAction: 'AllAddress',
								listeners: {
//									beforeselect: function(combo, record) {
//										var base_form = this.FormPanel.getForm();
//										if(record.get('Lpu_id') == '0')
//										{
//											combo.getStore().load({params:
//											{
//												Object: 'LpuWithMedServ',
//												comAction: 'AllAddress',
//												MedServiceType_id: 18,
//												KLAreaStat_idEdit: base_form.findField('KLAreaStat_idEdit').getValue(),
//												KLSubRgn_id: base_form.findField('Area_id').getValue(),
//												KLCity_id: base_form.findField('City_id').getValue(),
//												KLTown_id: base_form.findField('Town_id').getValue(),
//												KLStreet_id: base_form.findField('Street_id').getValue(),
//												CmpCallCard_Dom: base_form.findField('House').getValue(),
//												Person_Age: base_form.findField('Age').getValue()
//											}
//											});
//											return false;
//										}
//										//определяем метод загрузки лпу передачи
//										//this.selectLpuTransmit();
//										}.createDelegate(this)
//									,select: function(combo, record){
//										if (record.data.Lpu_id == null)
//										{
//											combo.setValue('');
//										}
//									}
								},	

								//xtype: 'swlpuwithmedservicecombo'
								xtype: 'hidden'
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
								fieldLabel: 'Дополнительная информация/ Уточненный адрес',
								toUpperCase: true,

								height: 100,
								name: 'CmpCallCard_Comm',
								// tabIndex: TABINDEX_PEF + 5,
								width: 350,
								//xtype: 'textarea'
								xtype: 'hidden'
							}
						]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						items: [
							// @todo Сделать компонент и вынести в библиотеку
							{
								xtype: 'swcommonsprcombo',
								fieldLabel: ++panelNumber + '. Кто вызывает',
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
								fieldLabel: '№ телефона вызывающего',
								name: 'Phone',
								width: 250,
								xtype: 'textfield'
							}
						]
					}, {
						xtype      : 'fieldset',
						autoHeight: true,
						border: false,
						height:0,
						items : [
//							{
//								fieldLabel: ++panelNumber + '. Фельдшер, принявший вызов',
//								hiddenName: 'FeldsherAccept',
//								allowBlank:true,
//								width: 250,
//								xtype: 'swmedpersonalcombo',
//								listeners: {
//									select: function(combo,record,index){
//										var appendCombo = this.FormPanel.getForm().findField('FeldsherAcceptCall');
//										if(appendCombo)appendCombo.setValue(combo.getValue());
//									}.createDelegate(this)
//								}
//							},
//							{
//								fieldLabel: ++panelNumber + '. Фельдшер, передавший вызов',
//								hiddenName: 'FeldsherTrans',
//								allowBlank:true,
//								width: 250,
//								xtype: 'swmedpersonalcombo'
//							}
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
						fieldLabel: ++panelNumber + '. Пациент',
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
						fieldLabel: ++panelNumber + '. Социальное положение больного',
						xtype: 'radiogroup',
						cls: 'boxbgr',
						id: 'psocial',
						allowBlank: false,
						listeners: {					
							change: function (rb, checked) {								
								if (checked) {
									if (checked.value == 2){
										Ext.getCmp('CMPCLOSE_ComboValue_582').show();
										Ext.getCmp('CMPCLOSE_ComboValue_582').setValue();
									} else {
										Ext.getCmp('CMPCLOSE_ComboValue_582').hide();
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
					fieldLabel: 'Повод талона вызова',
					hiddenName: 'CallPovod_id',
					id: 'idCallPovod_id',
					width: 350,
					listWidth: 300,
					editable: true,
					xtype: 'swcommonsprcombo'	
				}, {
					comboSubject: 'CmpReasonNew',
					disabledClass: 'field-disabled',
					fieldLabel: 'Повод для 110у',
					hiddenName: 'CallPovodNew_id',
					id: this.id+'CallPovodNew_id',
					width: 350,
					listWidth: 300,
					autoLoad: true,
					editable: true,
					xtype: 'swcustomobjectcombo'
				}, {
					name: 'Diag_uid',
					hiddenName: 'Diag_uid',
					id: 'Diag_uid',
					disabled: true,
					hidden: true,
					xtype: 'swdiagcombo'
				}, {
					//name: 'CmpCallCard_IsNMP',
					name: 'CmpCallCard_IsReceivedInPPD',
					fieldLabel: 'Неотложная помощь',					
					xtype: 'hidden'
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						comboSubject: 'CmpCallType',
						fieldLabel	   : 'Вызов',
						allowBlank: false,
						id: this.id+"_CmpCallType_id",
						fieldLabel: 'Кратность вызова',
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
						fieldLabel	   : 'Исполнение',
						width: '100%',
						xtype: 'radiogroup',
						cls: 'boxbgr',
						items: this.getCombo('Ispolnenie_id')
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel: 'Передан бригаде',
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Peredan_id')
				}]
			}, {
//				xtype      : 'fieldset',
//				autoHeight: true,
//
//				frame	   : true,
//				items      : [{
//						columns: 1,
//						vertical: true,
//						fieldLabel	   : ++panelNumber + '. Причины выезда с опозданием',
//						width: '100%',
//						xtype: 'radiogroup',
//						cls: 'boxbgr',
//						items: this.getCombo('Delay_id')
//				}]
//			}, {
//				xtype      : 'fieldset',
//				autoHeight: true,
//
//				frame	   : true,
//				items      : [{
//						columns: 1,
//						vertical: true,
//						fieldLabel	   : ++panelNumber + '. Состав бригады скорой медицинской помощи',
//						width: '100%',
//						xtype: 'checkboxgroup',
//						cls: 'boxbgr',
//						items: this.getCombo('TeamComplect_id')
//				}]
//			}, {
				xtype      : 'fieldset',
				autoHeight: true,

				frame	   : true,
				items      : [{
					columns: 1,
					vertical: true,
					fieldLabel	   : ++panelNumber + '. Место вызова',
					width: '100%',
					xtype: 'radiogroup',
					items: this.getCombo('CallPlace_id')
				}
				/*{
						columns: 1,
						vertical: true,
						fieldLabel	   : '17. Место вызова',
						width: '100%',
						xtype: 'radiogroup',
						cls: 'boxbgr',
						items: this.getCombo('CallPlace_id')
				}*/
//				{
//					comboSubject: 'CmpCallPlaceType',
//					fieldLabel	   : ++panelNumber + '. Тип места вызова',
//					hiddenName: 'CmpCallPlaceType_id',
//					name: 'CmpCallPlaceType_id',
//					xtype: 'swcommonsprcombo',
//					width: 250,
//					listWidth: 250,
//					value: 1
//				}
				]
//			}, {
//				xtype      : 'fieldset',
//				autoHeight: true,
//				frame	   : true,
//				items      : [{
//						columns: 2,
//						fieldLabel	   : ++panelNumber + '. Причина несчастного случая',
//						vertical: true,
//						width: '100%',
//						xtype: 'checkboxgroup',
//						cls: 'boxbgr',
//						items: this.getCombo('AccidentReason_id')
//				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				
				frame	   : true,
				items      : [{
						columns: 2,
						vertical: true,			
						fieldLabel	   : 'Обстоятельства травмы',
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
						columns: 1,
						vertical: true,
						fieldLabel	   : 'Полиция на месте вызова',
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Cop_id')
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : 'В РУВД сообщено',
						width: '100%',
						xtype: 'radiogroup',
						items: this.getCombo('Ruvd_id')
				}]
//			}, {
//				xtype      : 'fieldset',
//				autoHeight: true,
//				frame	   : true,
//				items      : [{
//						fieldLabel: ++panelNumber + '. Наличие клиники опьянения',
//						hiddenName: 'isAlco',
//						width: 40,
//						comboSubject: 'YesNo',
//						xtype: 'swcommonsprcombo'
//				}]
			}
		];
		
		// Жалобы 
		var jalob_fieds = [
			{
				xtype      : 'fieldset',
				autoHeight: true,				
				frame	   : true,
				labelWidth: 100,
				items      : [{
						fieldLabel	   : ++panelNumber + '. Жалобы',						
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
				labelWidth: 100,
				items      : [{
						fieldLabel: ++panelNumber + '. Анамнез',						
						name: 'Anamnez',
						//displayField: 'Diag_Name',
						width: '90%',
						xtype: 'textarea'
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				labelWidth : 200,
				items      : [{
					dateLabel: 'Время ухудшения состояния',
					hiddenName: 'Bad_DT',			
					xtype: 'swdatetimefield',
					dateFieldWidth: 100,
					timeLabelWidth: 60,
					dateLabelWidth: 100,
					dateLabelWidth1: 350,
					timeLabelWidth1: 200
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				labelWidth : 200,
				items      : [{
					dateLabel: 'Дата последних mensis',
					hiddenName: 'Mensis_DT',
					xtype: 'swdatetimefield',
					dateFieldWidth: 100,
					timeLabelWidth: 60,
					dateLabelWidth: 100,
					dateLabelWidth1: 350,
					timeLabelWidth1: 200
				}]
			},{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 300,
				items      : [{
						fieldLabel: 'Аллергия',
						name: 'Alerg',
						width: '50%',
						xtype: 'textfield'
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
						fieldLabel: 'Эпид. анамнез',
						name: 'Epid',
						width: '90%',
						xtype: 'textarea'
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : '',
				frame	   : true,
				items      : [
					{
					layout	   : 'column',
					items: [{
						xtype      : 'panel',
						title	   : 'Выезд за пределы РФ',
						frame	   : true,
						width : '20%',
						height : 130,
						items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('FromRf_id')
						}]
					}, {
						xtype      : 'panel',
						title	   : 'Выезд в сельскую местность',
						frame	   : true,
						width : '20%',
						height : 130,
						items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('InSh_id')
						}]
					}, {
						xtype      : 'panel',
						title	   : 'Контакт с инф. больными, животными, грызунами',
						frame	   : true,
						width : '20%',
						height : 130,
						items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Infect_id')
						}]
					}, {
						xtype      : 'panel',
						title	   : 'Вакцинация от гриппа',
						frame	   : true,
						width : '20%',
						height : 130,
						items : [{
								fieldLabel: 'Вакцинация от гриппа',
								hiddenName: 'isVac',
								width: 40,
								comboSubject: 'YesNo',
								xtype: 'swcommonsprcombo'
						}]
					}, {
						xtype      : 'panel',
						title	   : 'Употребление некачественных продуктов',
						frame	   : true,
						width : '20%',
						height : 130,
						items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Product_id')
						}]
					}]
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,				
				title	   : ++panelNumber + '. Объективные данные',
				frame	   : true,
				items      : [
					{
					layout	   : 'column',
					items: [
						{
							xtype      : 'panel',
							title	   : 'Общее состояние',
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
							title	   : 'Поведение',
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
							title	   : 'Положение',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Polozenie_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Сознание',
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
									fieldLabel: 'Менингеальные знаки',																
									hiddenName: 'isMenen',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]	
							}]	
						}, {
							xtype      : 'panel',
							title	   : 'Зрачки',
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
									fieldLabel: 'Нистагм',														
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
									fieldLabel: 'Анизокория',														
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
									fieldLabel: 'Реакция на свет',									
									hiddenName: 'isLight',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'	
								}]
							}]	
						}, {
							xtype      : 'panel',
							title	   : 'Большой родничок напряжен',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Rodn_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Коренальные рефлексы',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Korneal_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'С-м Белоглазова',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Beloglaz_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Видимые повреждения',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Damage_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Кожные покровы',
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
									fieldLabel: 'Акроцианоз',
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
									fieldLabel: 'Мраморность',									
									width: 50,
									hiddenName: 'isMramor',
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'	
								}]	
							}]	
						}, {
							xtype      : 'panel',
							title	   : 'Отеки',
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
							title	   : 'Сыпь',
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
							title	   : 'Зев',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									fieldLabel: 'Зев',
									name: 'Zev',
									xtype: 'textfield',
									width: 250
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Дыхание',
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
							title	   : 'Хрипы',
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
							title	   : 'Перкуторно',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									fieldLabel: 'Перкуторно',
									name: 'Perk',
									xtype: 'textfield',
									width: 250
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Одышка',
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
							title	   : 'Тоны сердца',
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
							title	   : 'Шум',
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
							title	   : 'Пульс',
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
							title	   : 'Язык',
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
							title	   : 'Живот',
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
									fieldLabel: 'Участвует в акте дыхания',									
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
									fieldLabel: 'Симптомы раздражения брюшины',									
									hiddenName: 'isPerit',
									comboSubject: 'YesNo',
									width: 40,
									xtype: 'swcommonsprcombo'	
								}]
							}]	
						}, {
							xtype      : 'panel',
							title	   : 'Печень',
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
							fieldLabel: 'Мочеиспускание',							
							name: 'Urine',
							width: 400,
							xtype: 'textfield'
					}, {						
							fieldLabel: 'Стул',
							name: 'Shit',
							xtype: 'textfield'
					}, {						
							fieldLabel: 'Другие симптомы',
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
										fieldLabel: 'Рабочее АД, мм.рт.ст.',
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
									fieldLabel: 'АД, мм.рт.ст.',
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
							fieldLabel: 'АД, мм.рт.ст.',
							name: 'AD',					
							xtype: 'hidden'				
					}, {					
							fieldLabel: 'ЧСС, мин.',
							name: 'Chss',					
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3
					}, {						
							fieldLabel: 'Пульс, уд/мин',
							name: 'Pulse',					
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3
					}, {					
							fieldLabel: 'Температура',
							name: 'Temperature',					
							xtype: 'textfield',
							plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
					}, {														
							fieldLabel: 'ЧД, мин.',
							name: 'Chd',					
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3					
					}, {														
							fieldLabel: 'Пульсоксиметрия',
							name: 'Pulsks',					
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3	
					}, {														
							fieldLabel: 'Глюкометрия',
							name: 'Gluck',					
							xtype: 'textfield',
							plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
					}, {						
							fieldLabel: 'Дополнительные объективные данные. Локальный статус.',
							name: 'LocalStatus',							
							width: 400,
							xtype: 'textarea'							
					}, {														
							fieldLabel: 'ЭКГ до оказания медицинской помощи',
							name: 'Ekg1',							
							width: 90,
							xtype: 'textfield'											
					}, {														
							fieldLabel: 'ЭКГ до оказания медицинской помощи (время)',
							name: 'Ekg1Time',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							validateOnBlur: false,
							width: 90,
							xtype: 'swtimefield'											
					}, {														
							fieldLabel: 'ЭКГ после оказания медицинской помощи',
							name: 'Ekg2',							
							width: 90,
							xtype: 'textfield'
					}, {														
							fieldLabel: 'ЭКГ после оказания медицинской помощи (время)',
							name: 'Ekg2Time',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							validateOnBlur: false,
							width: 90,
							xtype: 'swtimefield'
					}
				]
			}
		];
		
		// Диагноз 
		var diagnoz_fieds = [			
			{
				xtype      : 'panel',
				title	   : ++panelNumber + '. Диагноз',
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
							hiddenName: 'Diag_id',
							id: this.id+"_Diag_id",
							xtype: 'swdiagcombo',
							allowBlank: false,
							disabledClass: 'field-disabled',
							MKB: {
								isMain: true
							}
						}]
					}]
				}]
			}
			
			// TODO: код МКБ-10			
//			{
//				xtype      : 'panel',
//				title	   : ++panelNumber + '. Осложнения',
//				frame	   : true,
//				items      : [{
//						columns: 3,
//						vertical: true,
//						width: '100%',
//						xtype: 'checkboxgroup',
//						cls: 'boxbgr',
//						items: this.getCombo('Complicat_id')
//				}]
//			}, {
//				xtype      : 'panel',
//				title	   : ++panelNumber + '. Эффективность мероприятий при осложнении',
//				frame	   : true,
//				items      : [{
//						columns: 3,
//						vertical: true,
//						width: '100%',
//						xtype: 'radiogroup',
//						cls: 'boxbgr',
//						items :this.getCombo('ComplicatEf_id')
//				}]
//			}
		];
		
		// Манипуляции
		var procedure_fieds = [
			{
				xtype      : 'panel',
				title	   : ++panelNumber + '. Оказанная помощь на месте вызова',
				frame	   : true,
				items      : [{
						name: 'HelpPlace',
						width: '99%',
						xtype: 'textarea'
				}]
			}, {
				xtype      : 'panel',
				title	   : ++panelNumber + '. Оказанная помощь в автомобиле скорой медицинской помощи',
				frame	   : true,
				items      : [{
						name: 'HelpAuto',
						width: '99%',
						xtype: 'textarea'
				}]
			}, {
				xtype      : 'panel',
				title	   : ++panelNumber + '. Эффективность проведенных мероприятий',
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
											fieldLabel: 'АД, мм.рт.ст.',
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
									fieldLabel: 'АД, мм.рт.ст.',
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
											fieldLabel: 'АД, мм.рт.ст.',
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
										fieldLabel: 'Температура',
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
											fieldLabel: 'ЧСС, мин.',
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
											fieldLabel: 'Пульс, уд/мин',
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
											fieldLabel: 'ЧД, мин.',
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
											fieldLabel: 'Пульсоксиметрия',
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
										fieldLabel: 'Глюкометрия',
										name: 'EfGluck',
										xtype: 'textfield',
										plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
									}]
								},{
									xtype      : 'fieldset',
									autoHeight: true,								
									labelWidth: 100,
									border: false,
									width: 300,
									items : [{
										fieldLabel: 'Купированы боли/приступ',
										hiddenName: 'isKupir',
										width: 40,
										comboSubject: 'YesNo',
										xtype: 'swcommonsprcombo'	
									}]	
								}
							]
						}
						
					]
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
					//{name: 'MedStaffFact_id', type: 'int', hidden: true},
					{name: 'Person_id', type: 'int', hidden: true},
					{name: 'PayType_id', type: 'int', hidden: true},
					{name: 'UslugaCategory_id', type: 'int', hidden: true},
					{name: 'UslugaComplex_id', type: 'int', hidden: true},
					{name: 'UslugaComplexTariff_id', type: 'int', hidden: true},
					{name: 'CmpCallCardUsluga_setDate', type: 'string', header: 'Дата', width: 120},
					{name: 'CmpCallCardUsluga_setTime', type: 'string', header: 'Время', width: 120},
					{name: 'UslugaComplex_Code', type: 'string', header: 'Код', width: 160},
					{name: 'UslugaComplex_Name', type: 'string', header: 'Наименовение', id: 'autoexpand'},
					{name: 'CmpCallCardUsluga_Cost', type: 'int', header: 'Цена'},
					{name: 'CmpCallCardUsluga_Kolvo', type: 'int', header: 'Количество'},
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
				title: ++panelNumber + '. Услуги',
				items: [this.UslugaViewFrame]
			}));
		
		}
		
		if ( getGlobalOptions().region.nick.inlist(['pskov']) ) {
			procedure_fieds.push({
				xtype: 'panel',
				title: 'Использованное оборудование (на месте/в машине)',
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
					fieldLabel: ++panelNumber + '. Согласие на медицинское вмешательство',					
					hiddenName: 'isSogl',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'	
				}, {
					fieldLabel: ++panelNumber + '. Отказ от медицинского вмешательства',
					hiddenName: 'isOtkazMed',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'	
				}, {
					fieldLabel: ++panelNumber + '. Отказ от транспортировки для госпитализации в стационар.',
					hiddenName: 'isOtkazHosp',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'	
				}
				]	
			},
			{ 
				xtype      : 'panel',
				title	   : ++panelNumber + '. Результат оказания скорой медицинской помощи',
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
				title	   : ++panelNumber + '. Больной',
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
								
								this.hideField(Ext.getCmp('CMPCLOSE_ComboValue_111'));
								this.hideField(Ext.getCmp('CmpCloseCardHomeVisitDate'));
								
								//Ext.getCmp('CMPCLOSE_ComboValue_111').hide();
								//Ext.get(Ext.DomQuery.selectNode(String.format('label[for="CMPCLOSE_ComboValue_111"]'))).hide();
								Ext.getCmp('CMPCLOSE_ComboValue_112').hide();
								
								
								if (checked && checked.id=='CMPCLOSE_CB_110') 
								{																		
									Ext.getCmp('CMPCLOSE_ComboValue_110').show();								
									//Ext.getCmp('CMPCLOSE_ComboValue_110').setValue();								
								}

								if (checked && checked.id=='CMPCLOSE_CB_111')
								{
	
									this.showField(Ext.getCmp('CmpCloseCardHomeVisitDate'));
									this.showField(Ext.getCmp('CMPCLOSE_ComboValue_111'));
										
									//Ext.getCmp('CMPCLOSE_ComboValue_111').show();
									Ext.get(Ext.DomQuery.selectNode(String.format('label[for="CMPCLOSE_ComboValue_111"]'))).show();
									//Ext.getCmp('CMPCLOSE_ComboValue_111').setValue();
									Ext.getCmp('CMPCLOSE_ComboValue_111').allowBlank = false;
								} else {
									Ext.getCmp('CMPCLOSE_ComboValue_111').allowBlank = true;
								}

								if (checked && checked.id=='CMPCLOSE_CB_112')
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
						},
						{
							fieldLabel: 'Выберите ЛПУ',
							name: 'ComboValue[111]',
							listWidth: 400,
							hiddenName: 'ComboValue[111]',
							id: 'CMPCLOSE_ComboValue_111',
							//xtype: 'swlpucombo'
							xtype: 'swLpuHomeVisitStorageCombo',
							allowBlank: true
						},
						{
							fieldLabel: 'Дата',
							name: 'CmpCloseCardHomeVisitDate',							
							id: 'CmpCloseCardHomeVisitDate',
							value:  new Date(),
							xtype: 'swdatefield'			
						}
						]
					}]
				}]			
			}, {
				xtype      : 'panel',
				title	   : ++panelNumber + '. Способ доставки больного в автомобиль скорой медицинской помощи',
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
				title: ++panelNumber + '. Результат выезда',
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
							var resultIdCombo = Ext.getCmp(this.id+'_ResultId'),
								DiagIdCombo = Ext.getCmp(this.id+"_Diag_id");
							/** refs(#93928)
							 НЕобязательные для заполнения, если в поле выбрано одно из значений:
							 	Больной не найден на месте;
							 	Отказ от помощи (от осмотра);
							 	Адрес не найден;
							 	Вызов ложный;
							 	Смерть до приезда;
							 	Больной увезен до прибытия СМП;
							 	Больной обслужен врачом поликлиники до приезда СМП;
							 	Отмена вызова по рации.
							 */
							if(checked.inputValue.inlist([231,232,233,234,235,236,237,238])){
								resultIdCombo.allowBlank(true);
								DiagIdCombo.allowBlank(true);
							}
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
				title	   : ++panelNumber + '. Километраж',
				labelWidth : 100,
				frame	   : true,
				items : [{
					allowDecimals: true,
					allowNegative: false,
					fieldLabel: 'Километраж',
					maxValue: 9999.99,
					name: 'Kilo',
					xtype: 'numberfield',
					msgTarget: 'under'
				}]			
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++panelNumber + '. Примечания',
				labelWidth : 100,
				frame	   : true,
				items : [{
					fieldLabel: 'Примечания',
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
			//{name: 'MedStaffFact_id'},
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
			//{name: 'BirthType'},
			//{name: 'BirthDay'},

			//{name: 'Ktov'},
			{name: 'CmpCallerType_id'},
			{name: 'Phone'},
			//{name: 'FeldsherAcceptPskov'},				
			//{name: 'FeldsherAccept'},
			//{name: 'FeldsherTransPskov'},
			//{name: 'FeldsherTrans'},
			{name: 'CallType_id'},
			{name: 'CmpCallCard_id'},
			{name: 'ARMType'},
			{name: 'CmpCloseCard_id'},

			{name: 'CallPovod_id'},
			{name: 'CallPovodNew_id'},
			//{name: 'CmpCallCard_IsNMP'},
			{name: 'CmpCallCard_IsReceivedInPPD'},
			//{name: 'AcceptTime'},
			{name: 'TransTime'},
			{name: 'GoTime'},
			{name: 'ArriveTime'},
			{name: 'TransportTime'},
			//{name: 'ToHospitalTime'},
			{name: 'EndTime'},
			{name: 'BackTime'},
			{name: 'SummTime'},
			{name: 'Work'},
			{name: 'DocumentNum'},
			{name: 'CallType_id'},			
			//{name: 'isAlco'},
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
			{name: 'EfAD'},
			{name: 'EfChss'},
			{name: 'EfPulse'},
			{name: 'EfTemperature'},
			{name: 'EfChd'},
			{name: 'EfPulsks'},
			{name: 'EfGluck'},

			{name: 'Zev'},
			{name: 'Perk'},
			{name: 'Epid'},
			{name: 'Alerg'},
			{name: 'Mensis_DT'},
			{name: 'Bad_DT'},
			{name: 'isKupir'},
			{name: 'isVac'},
			
			{name: 'Kilo'},
			{name: 'HelpPlace'},
			{name: 'HelpAuto'},
			{name: 'DescText'}
		];			
		
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
					title: '<b>1.</b> Информация о вызове',
					id: 'CMPCLOSE_TabPanel_FirstShowedTab',
					items: person_fieds,
					autoHeight: true
				}, {
					title: '<b>2.</b> Повод к вызову',
					items: povod_fieds
				}, {
					title: '<b>3.</b> Жалобы и объективные данные',
					items: jalob_fieds
				}, {
					title: '<b>4.</b> Диагноз',
					items: diagnoz_fieds
				}, {
					title: '<b>5.</b> Манипуляции',
					items: procedure_fieds
				}, {
					title: '<b>6.</b> Результат',
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
					if(window.parent.Ext.getCmp('myFFFrame')) {
						window.parent.Ext.getCmp('myFFFrame').close();
					} else {
						this.hide();
					}
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
		var fields_hide = [241,242,243,244,245,246,247,248,568,566,567];
		for( var i=0,cnt=fields_hide.length; i<cnt; i++ ){
			var field = Ext.getCmp('CMPCLOSE_ComboValue_'+fields_hide[i]);
			if ( field ) {
				this.hideField( field );
			}
		}
	},
	
	clearResultUfaComboxFields: function(){		
		var fields_hide = [241,242,243,244,245,246,247,248,568,566,567];
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
			log('Невозможно очистить поле:',field);
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
		
		this.toggleVal('582',false);
		this.toggleVal('141',false);		
		this.toggleVal('110',false);		
		this.toggleVal('111',false);		
		this.toggleVal('112',false);		
		//this.toggleAll('241',false);
		
		base_form.findField('TransTime').on('change', function(){
			those.updateEmergencyTeamOnTransTimeChange();
		});
		
		Ext.getCmp('CMPCLOSE_ComboValue_241').getStore().load();
		Ext.getCmp('CMPCLOSE_ComboValue_244').getStore().load();
		
		base_form.findField('ComboValue[111]').getStore().load();

		if (!getGlobalOptions().region.nick.inlist(['kareliya', 'ekb'])) {
			var SpecField = this.FormPanel.getForm().findField('LpuSection_id');	
			SpecField.allowBlank = true;
			SpecField.hide();
			SpecField.getEl().up('.x-form-item').setDisplayed(false);
		}
		
//		var pt = this.FormPanel.getForm().findField( 'PayType_id' );
//		if (getRegionNick().inlist(['perm','ekb'])){
//			this.hideField(pt);
//		} else {
//			// Установка значения "ОМС" по умолчанию
//			var pt_idx;
//			if ( pt.getValue() == '' && (pt_idx = pt.getStore().find('PayType_Code',1) ) !== -1 ) {
//				var pt_record = pt.getStore().getAt(pt_idx);
//				pt.setValue(pt_record.get('PayType_id'))
//			}
//		}
		
		this.FormPanel.getForm().findField('Diag_uid').hide().getEl().up('.x-form-item').setDisplayed(false);
		

		// Скрываем необходимые поля на вкладке "Результат выезда"
		this.hideResultUfaComboxFields();
		
		this.hideField(Ext.getCmp('CmpCloseCardHomeVisitDate'));
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
//		setMedStaffFactGlobalStoreFilter({
//			arrayLpuUnitType: [12],
//			onDate: getGlobalOptions().date // не уволены
//		});
//
		//base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
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
//		for( var i=0,cnt=this.time_fields.length; i<cnt; i++ ) {
//			var item = this.time_fields[i];
//			if ( typeof item.hiddenName === 'undefined' || ( item.name && item.name == 'SummTime' ) ) {
//				continue;
//			}
//			this.FormPanel.getForm().findField( item.hiddenName ).addListener('change',function(){
//				those.calcSummTime();
//			});
//		}
//
		//base_form.findField('FeldsherAcceptCall').getStore().load();
		//base_form.findField('FeldsherAccept').getStore().load();
		//base_form.findField('FeldsherTrans').getStore().load();
		
		if (getGlobalOptions().region.nick.inlist(['pskov','astra'])) {
			//base_form.findField('FeldsherAcceptPskov').getStore().load();
			//base_form.findField('FeldsherTransPskov').getStore().load();
			
			//base_form.findField('EmergencyTeamNum').validate();
			base_form.findField('LpuSection_id').validate();			
			base_form.findField('FeldsherAcceptCall').validate();
			//base_form.findField('MedStaffFact_id').validate();
			base_form.findField('EmergencyTeam_id').validate();
			base_form.findField('PayType_id').validate();
			base_form.findField('Age').validate();
			base_form.findField('Sex_id').validate();
			base_form.findField('EmergencyTeamSpec').validate();
			base_form.findField('CallType_id').validate();
			base_form.findField('Diag_id').enable();
			base_form.findField('Diag_id').reset();
			base_form.findField('Diag_id').validate();
			Ext.getCmp(this.id+"_ResultId").validate();
			Ext.getCmp(this.id+"_ResultEmergencyTrip").validate();
			Ext.getCmp("psocial").validate();


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
			base_form.findField('Diag_id').validate();
			base_form.findField('AcceptTime').validate();
			base_form.findField('EndTime').validate();
			base_form.findField('TransportTime').validate();
			Ext.getCmp(this.id+"_ResultId").validate();
			Ext.getCmp(this.id+"_ResultEmergencyTrip").validate();
			Ext.getCmp(this.id+"_CmpCallType_id").validate();
		
		var dt = new Date();
		base_form.findField('AcceptTime').setValue(dt);		
		
		base_form.findField('TransTime').setValue(dt);
		/*
		base_form.findField('GoTime').setValue(dt);
		base_form.findField('ArriveTime').setValue(dt);
		base_form.findField('TransportTime').setValue(dt);
		//base_form.findField('ToHospitalTime').setValue(dt);
		base_form.findField('EndTime').setValue(dt);
		base_form.findField('BackTime').setValue(dt);
		*/
		
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
		res.push({
			boxLabel: 'Нет',
			name: field,
			value: null
		});
		
		return res;
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
		
		return this.allfields[field];
		
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
			//'Feldsher_id',
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
			//'Room',
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
			//'FeldsherAccept',
			//'FeldsherAcceptPskov',
			//'FeldsherTrans',
			//'FeldsherTransPskov',
			'CallType_id',
			'CmpCallCard_id',
			//'CallPovodNew_id',
			//'AcceptTime',
			'TransTime',
			'GoTime',
			'ArriveTime',
			'TransportTime',
			//'ToHospitalTime',
			'EndTime',
			'BackTime',
			//'SummTime',
			'Work',
			'DocumentNum',
			'CallType_id',			
			//'isAlco',
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
			'Diag_uid',
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
		);			
		
		
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
