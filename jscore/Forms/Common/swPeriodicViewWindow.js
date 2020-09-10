/**
* swPeriodicViewWindow - окно просмотра и редактирования периодик.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Ivan Pshenitcyn aka IVP (ipshon@rambler.ru)
* @version      10.09.2010
*/

sw.Promed.swPeriodicViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',	
	draggable: true,
	height: 400,
	id: 'PeriodicViewWindow',
	// соответствие PersonEvnClass_id и наименования периодик в форме редактирования человека
	// по идее, надо завязывать на сисник, но для этого надо подгонять поля формы под сисники события
	periodicSingleFields: {
		'1': 'Person_SurName',
		'3': 'Person_SecName',
		'2': 'Person_FirName',
		'4': 'Person_BirthDay',
		'6': 'Person_SNILS',
		'5': 'PersonSex_id',
		'7': 'SocStatus_id',
		'16': 'Federal_Num',
		'18': 'PersonPhone_Phone',
		'20': 'PersonInn_Inn',
		'21': 'PersonSocCardNum_SocCardNum',
		'22': 'FamilyStatus_id'
	},
	periodicStructFields: {
		'8': 'Polis',
		'9': 'Document',
		'10': 'UAddress',
		'11': 'PAddress',
		'12': 'Job',
		'23': 'NationalityStatus'
	},
	addPeriodic: function() {
		getWnd('swDateTimeSelectWindow').show({onSelect: function(date_time) {
			getWnd('swPersonEditWindow').show({
				action: 'add',
				subaction: 'addperiodic',
				fields: {
				},
				callback: function(callback_data) {
						var date = date_time.Date;
						var time = date_time.Time;
						var params = callback_data.savingData;
						params.Person_id = this.personId;
						params.Date = date;
						params.Time = time;
						params.EvnType = callback_data.changedFields.join('|');
						// TODO: вопрос использования здесь getWnd - скорее всего надо по другому.
						var loadMask = new Ext.LoadMask(getWnd('swPersonEditWindow').getEl(), { msg: "Подождите, идет сохранение..." });
						loadMask.show();
						// отправляем запрос на сохранение
						Ext.Ajax.request({
							url: '/?c=Person&m=saveAttributeOnDate',
							params: params,
							callback: function(options, success, response) {
								loadMask.hide();
								if ( success ) {
									if ( response.responseText.length > 0 ) {
										var resp_obj = Ext.util.JSON.decode(response.responseText);

										if ( resp_obj.success == false ) {
											if ( resp_obj.Error_Code && resp_obj.Error_Code == 666 && resp_obj.Person_id && resp_obj.Server_id ) {
												
											}
											else {
												Ext.Msg.alert(
													lang['oshibka'],
													resp_obj.Error_Msg,
													function() {
														//base_form.findField('Person_SurName').focus(true, 100);
														return;
													}
												);
											}
										}
										else {
											this.findById('PVW_PeriodicViewGrid').getGrid().getStore().removeAll();
											this.findById('PVW_PeriodicViewGrid').loadData();
											getWnd('swPersonEditWindow').hide();
										}
									}
								}
							}.createDelegate(this)
						});
				}.createDelegate(this),
				onClose: function() {					
				}
			});
		}.createDelegate(this)});
	}, checkPersonSnils: function(snils) {
		if ( typeof snils != 'string' ) {
			return true;
		}

		snils = snils.replace(/\-/g, '').replace(/ /g, '');

		if ( snils.length == 0 ) {
			return true;
		}

		var reg = /^\d{11}$/;

		if ( !reg.test(snils) ) {
			return false;
		}

		var
			psk = snils.substr(9, 2),
			ps = snils.substr(0, 9),
			arr = new Array(),
			z = 9,
			sum = 0,
			i;

		for ( i = 0; i < 9; i++ ) {
			arr[i] = ps.substr(i, 1);
			sum += arr[i]*z;
			z--;
		}

		while ( sum > 101 ) {
			sum = sum % 101;
		}

		if ( ((sum < 100) && (sum != psk)) || (((sum == 100) || (sum == 101)) && (psk != '00')) ) {
			return false;
		}

		return true;
	},
	checkPersonINN: function(value) {
		var result = false;

		var inn = value || '';
		var errorMsg = 'Ошибка проверки контрольной суммы в ИНН. ';
		if (typeof inn === 'number') inn = inn.toString();

		var checkDigit = function (inn, coefficients) {
			var n = 0;
			coefficients.forEach(function(item, i, coefficients){
				n += coefficients[i] * inn[i];
			});
			return parseInt(n % 11 % 10);
		};

		if (!inn.length) {
			console.warn(errorMsg + 'ИНН пуст');
		} else if (/[^0-9]/.test(inn)) {
			console.warn(errorMsg + 'ИНН может состоять только из цифр');
		} else if ([12].indexOf(inn.length) === -1) {
			console.warn(errorMsg + 'ИНН может состоять только из 12 цифр');
		} else {
			var n11 = checkDigit(inn, [7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
			var n12 = checkDigit(inn, [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
			if ((n11 === parseInt(inn[10])) && (n12 === parseInt(inn[11]))) {
				result = true;
			}
		}

		return result;
	},
	editPeriodic: function() {
		getWnd('swPersonEditWindow').show({
			action: 'edit',
			callback: function() {
				this.findById('PVW_PeriodicViewGrid').getGrid().getStore().removeAll();
				this.findById('PVW_PeriodicViewGrid').loadData();
			}.createDelegate(this),
			onClose: function() {},
			Server_id: Ext.getCmp('PVW_PeriodicViewGrid').getGrid().getSelectionModel().getSelected().get('Server_id'),
			Person_id: Ext.getCmp('PVW_PeriodicViewGrid').getGrid().getSelectionModel().getSelected().get('Person_id'),
			PersonEvn_id: Ext.getCmp('PVW_PeriodicViewGrid').getGrid().getSelectionModel().getSelected().get('PersonEvn_id')
		});
	},
	editPeriodicOld: function() {
		var person_evn_class_id = Ext.getCmp('PVW_PeriodicViewGrid').getGrid().getSelectionModel().getSelected().get('PersonEvnClass_id');
		var server_id = Ext.getCmp('PVW_PeriodicViewGrid').getGrid().getSelectionModel().getSelected().get('Server_id');
		var person_evn_object_id = Ext.getCmp('PVW_PeriodicViewGrid').getGrid().getSelectionModel().getSelected().get('PersonEvnObject_id');
		var person_evn_id = Ext.getCmp('PVW_PeriodicViewGrid').getGrid().getSelectionModel().getSelected().get('PersonEvn_id');
		var periodic_evn_class = '';
		for ( var key in this.periodicSingleFields  )
		{
			if ( key = String(person_evn_class_id) )
			{
				periodic_evn_class = this.periodicSingleFields[key];
			}			 
		}
		if ( periodic_evn_class == '' )
			return false;
		getWnd('swPersonEditWindow').show({
			action: 'add',
			subaction: 'editperiodic',
			PeriodicEvnClass: periodic_evn_class,			
			fields: {
			},
			callback: function(callback_data) {					
					var params = callback_data.savingData;
					params.Person_id = this.personId;
					params.Server_id = server_id;
					params.PersonEvn_id = person_evn_id;
					params.EvnType = callback_data.changedFields.join('|');
					params.PersonEvnObject_id = person_evn_object_id;
					// TODO: Использование getWnd нерационально - надо другой способ
					var loadMask = new Ext.LoadMask(getWnd('swPersonEditWindow').getEl(), { msg: "Подождите, идет сохранение..." });
					loadMask.show();
					// отправляем запрос на сохранение
					Ext.Ajax.request({
						url: '/?c=Person&m=editPersonEvnAttribute',
						params: params,
						callback: function(options, success, response) {
							loadMask.hide();
							if ( success ) {
								if ( response.responseText.length > 0 ) {
									var resp_obj = Ext.util.JSON.decode(response.responseText);

									if ( resp_obj.success == false ) {
										if ( resp_obj.Error_Code && resp_obj.Error_Code == 666 && resp_obj.Person_id && resp_obj.Server_id ) {
											
										}
										else {
											Ext.Msg.alert(
												lang['oshibka'],
												resp_obj.Error_Msg,
												function() {
													//base_form.findField('Person_SurName').focus(true, 100);
													return;
												}
											);
										}
									}
									else {
										this.findById('PVW_PeriodicViewGrid').getGrid().getStore().removeAll();
										this.findById('PVW_PeriodicViewGrid').loadData();
										getWnd('swPersonEditWindow').hide();
									}
								}
							}
						}.createDelegate(this)
					});
			}.createDelegate(this),
			onClose: function() {					
			}
		});
	},
	doSaveOnThePersonEvn: function(PersonEvn_id, Server_id, EvnType, SavingData, callback) {
		var params = SavingData;
		params.Person_id = this.personId;
		params.Server_id = Server_id;
		params.PersonEvn_id = PersonEvn_id;
		params.EvnType = EvnType;
		params.refresh = true;
		params.cancelCheckEvn = true;
		if(PersonEvn_id==-1){
			params.cancelCheckEvn = false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		// отправляем запрос на сохранение
		Ext.Ajax.request({
			url: '/?c=Person&m=editPersonEvnAttributeNew',
			params: params,
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success && response.responseText && response.responseText.length > 0 ) {
					var resp_obj = Ext.util.JSON.decode(response.responseText);
					if ( resp_obj.success ) {
						if ( typeof callback == 'function' )
							callback(options, success, response);
					} else if(params.EvnType == 'Polis'){
						if ( typeof callback == 'function' )
							callback(options, success, response);
					}
				}
			}.createDelegate(this)
		});
	},
	deletePeriodic: function() {
		var grid = Ext.getCmp('PVW_PeriodicViewGrid').getGrid();
		var EvnArr = [];
		var win =this;
		var person_id=0;
		grid.getStore().each(function(s,d,f){
			if(s.get('check')){
				if(s.get('PersonEvn_id')>0&&s.get('Person_id')>0&&s.get('Server_id')>=0){
					person_id=s.get('Person_id');
					EvnArr.push({
						id:d,
						Person_id:s.get('Person_id'),
						Server_id:s.get('Server_id'),
						PersonEvn_id:s.get('PersonEvn_id'),
						PersonEvnClass_Name:s.get('PersonEvnClass_Name'),
						PersonEvnClass_id:s.get('PersonEvnClass_id')
					});
				}
			}
		})
		if ( person_id==0||EvnArr.length==0 ){
		
			return false;
		}
		var text = '';
		for(var ai = 0;ai<EvnArr.length;ai++){
			var cnt = 0;
			grid.getStore().each(function(s,d,f){
				if(s.get('PersonEvnClass_id')==EvnArr[ai].PersonEvnClass_id){
					cnt++;
				}
			});
			if(cnt==1&&EvnArr[ai].PersonEvnClass_id.inlist([1,2,3,4,5,7])){
				if(text!=''){text+=', '}
				text+=EvnArr[ai].PersonEvnClass_Name+'';
				EvnArr.splice(ai,1);
				ai--;
			}
			
		}
		log(EvnArr,text)
		if ( person_id==0||EvnArr.length==0 ){
			if(text!=''){
				log(text,2)
				text=lang['udalenie_periodik']+text+lang['ne_vozmojno_tak_kak_tip_udalyaemyih_periodik_obyazatelnyiy_ili_periodiki_etogo_tipa_poslednie_u_vyibrannogo_cheloveka'];
				log(text,1)
				Ext.Msg.alert(lang['oshibka'],text,function() {});		
			}
			return false;
		}
		sw.swMsg.show({
			title:(EvnArr.length>1||(EvnArr.length==1&&text!=''))? 'Удаление периодики':'Удаление периодики',
			msg:(EvnArr.length>1||(EvnArr.length==1&&text!=''))? 'Удалить выбранные периодики?':'Удалить выбранную периодику?',
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' ) {
					var _request = function(){
						var params = {};

						params.Person_id = person_id
						params.EvnArr = Ext.util.JSON.encode(EvnArr);

						var loadMask = new Ext.LoadMask(win.getEl(), { msg: "Подождите, идет удаление..." });
						loadMask.show();
						// отправляем запрос на удаление
						Ext.Ajax.request({
							url: '/?c=Person&m=deletePersonEvnAttribute',
							params: params,
							callback: function(options, success, response) {
								loadMask.hide();
								if ( success ) {
									if ( response.responseText.length > 0 ) {
										var resp_obj = Ext.util.JSON.decode(response.responseText);
										if ( resp_obj.success == false ) {
											/*Ext.Msg.alert(
											lang['oshibka'],
											resp_obj.Error_Msg,
											function() {
												return;
											}.createDelegate(this));*/
											return false;
										}
										else {
											win.findById('PVW_PeriodicViewGrid').getGrid().getStore().removeAll();
											win.findById('PVW_PeriodicViewGrid').loadData();
											win.findById('PVW_PersonInfoFrame').load({ Person_id: win.personId, Server_id: win.serverId, callback: function() {
												win.findById('PVW_PersonInfoFrame').setPersonTitle();
											}.createDelegate(this) });
										}
									}
								}
							}.createDelegate(this)
						});	
					}
					if(text!=''){
						text=lang['udalenie_periodik']+text+lang['ne_vozmojno_tak_kak_tip_udalyaemyih_periodik_obyazatelnyiy_ili_periodiki_etogo_tipa_poslednie_u_vyibrannogo_cheloveka'];
						Ext.Msg.alert(lang['oshibka'],text,function() {_request();});		
					}else{
						_request();
					}
					
				}
				else {
					//base_form.findField('Person_SurName').focus(true, 100);
				}
			}.createDelegate(this)
		});
	},
	getFioEditor: function() {		
		return new sw.Promed.TextFieldPMW({
			allowBlank: true,
			toUpperCase: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				}
			}
		});
	},
	getSnilsEditor: function() {
		return new Ext.form.TextField({
			allowBlank: true,
			autoCreate: { tag: "input", type: "text", size: "11", maxLength: "11", autocomplete: "off" },
			fireAfterEditOnEmpty: true,
			maskRe: /\d/,
			maxLength: 11,
			minLength: 11,
			enableKeyEvents: true,
			listeners: {
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				}
			}
		});
	},
	getSexEditor: function() {
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: true,
			autoLoad: true,
			comboSubject: 'Sex',
			displayField: 'Sex_Name',
			fireAfterEditOnEmpty: true,
			lastQuery: '',
			valueField: 'Sex_id',
			codeField: 'Sex_Code',
			editable: true,
			enableKeyEvents: true,
			onLoadStore: function() {
				this.getStore().filterBy(function(record) {
					return (record.get('Sex_Code').inlist([ '1', '2' ]));
				});
			},
			listeners: {
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				}
			}
		});
	},
	getSocStatusEditor: function() {
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: true,
			autoLoad: false,
			lastQuery: '',
			codeField: 'SocStatus_Code',
			comboSubject: 'SocStatus',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			moreFields: [
				{name: 'SocStatus_begDT', mapping: 'SocStatus_begDT'},
				{name: 'SocStatus_endDT', mapping: 'SocStatus_endDT'}
			],
			listeners: {
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				},
				'render': function(combo) {
					combo.getStore().load({
						callback: function() {
							var record = this.findById('PVW_PeriodicViewGrid').getGrid().getSelectionModel().getSelected();

							var date = (typeof record == 'object' && !Ext.isEmpty(record.get('PersonEvn_insDT')) ? record.get('PersonEvn_insDT') : getValidDT(getGlobalOptions().date, ''));

							// фильтруем соц. статусы
							combo.getStore().filterBy(function(rec) {
								return (
									Ext.isEmpty(rec.get('SocStatus_endDT'))
									|| (typeof rec.get('SocStatus_endDT') == 'object' ? rec.get('SocStatus_endDT') : getValidDT(rec.get('SocStatus_endDT'), '')) >= date
								);
							});
							combo.baseFilterFn = function(rec) {
								return (
									Ext.isEmpty(rec.get('SocStatus_endDT'))
									|| (typeof rec.get('SocStatus_endDT') == 'object' ? rec.get('SocStatus_endDT') : getValidDT(rec.get('SocStatus_endDT'), '')) >= date
								);
							}
						}.createDelegate(this)
					});
				}.createDelegate(this)
			}
		});
	},
	getFamilyStatusEditor: function() {
		return new sw.Promed.SwFamilyStatusCombo({
			allowBlank: true,
			autoLoad: false,
			lastQuery: '',
			codeField: 'FamilyStatus_Code',
			comboSubject: 'FamilyStatus',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				}
			}
		});
	},
	getBirthdayEditor: function() {
		return new sw.Promed.SwDateField({
			allowBlank: true,
			fireAfterEditOnEmpty: true,
			format: 'd.m.Y',			
			maxValue: getGlobalOptions().date,
			minValue: '01.01.1861',
			plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
			enableKeyEvents: true,
			listeners: {
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				}
			}
		});
	},
	getRefuseEditor: function() {
			return new sw.Promed.SwCommonSprCombo({
			allowBlank: true,
			autoLoad: false,
			lastQuery: '',
			codeField: 'YesNo_Code',
			comboSubject: 'YesNo',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				},
				'render': function(combo) {
					combo.getStore().load({});
				}
			}
		});
	},
	getEdNumEditor: function() {
		return new Ext.form.TextField({
			allowBlank: true,
			maskRe: /\d/,
			maxLength: 16,
			minLength: 16,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				}
			}
		});
	},
	getPhoneEditor: function() {
		return new Ext.form.TextField({
			allowBlank: true,
			maskRe: /\d/,// ограничение ввода цифр
			stripCharsRe: /[^0-9]/g,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			maxLength: 10,
			enforceMaxLength: true,// не работает до 4 версии// принудительно установить длину из maxLength, которое используется для валидации
			autoCreate: {tag: 'input', maxlength: '10'},// #137611 ограничение длины вводимого номера
			minLength: 10,
			listeners: {
				'keyup': function(field){
					var val = field.getValue();
					val = val.replace(/[^0-9]/g,'');
					field.setValue(val);
				},
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				}
			}
		});
	},
	getInnEditor: function() {
		return new Ext.form.TextField({
			allowBlank: true,
			autoCreate: { tag: "input", type: "text", size: "12", maxLength: "12", autocomplete: "off" },
			fireAfterEditOnEmpty: true,
			maskRe: /\d/,
			maxLength: 12,
			minLength: 12,
			enableKeyEvents: true,
			listeners: {
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				}
			}
		});
	},
	getSocCardNumEditor: function() {
		return new Ext.form.TextField({
			allowBlank: true,
			fireAfterEditOnEmpty: true,
			maskRe: /\d/,
			enableKeyEvents: true,
			listeners: {
				'keypress': function(field, e) {
					if ( e.getKey() == e.TAB )
						field.fireEvent('blur', field);
				}
			}
		});
	},
	onCancelAdd: function() {
		var grid = this.findById('PVW_PeriodicViewGrid').getGrid();
		grid.getStore().removeAt(grid.getStore().getCount() - 1);
		// устанавливаем фокус на нужное место
		if ( grid.getStore().getCount() > 0 )
		{
			grid.getSelectionModel().select(grid.getStore().getCount() - 1, 5);
			grid.getView().focusCell(grid.getStore().getCount() - 1, 5);
		}
	},
	startAddData: function() {
		this.clearEditForm = true;
		var grid = this.findById('PVW_PeriodicViewGrid').getGrid();
		var last_record = grid.getStore().getAt(grid.getStore().getCount() - 1);
		var store = [];
		if ( last_record && (last_record.get('PersonEvn_id') == null || last_record.get('PersonEvn_id') == '') )
			return false;
		grid.getStore().loadData([{Server_id: this.serverId, Person_id: this.personId}], true);
		grid.getSelectionModel().select(grid.getStore().getCount() - 1, 5);
		grid.getView().focusCell(grid.getStore().getCount() - 1, 5);
		var cell = grid.getSelectionModel().getSelectedCell();		
		if ( !cell || cell.length == 0 || (cell[1] != 5) )
			return false;
		textINN = (getRegionNick() == 'kz')?lang['iin']:lang['inn'];
		var record = grid.getSelectionModel().getSelected();
		if ( !record )
			return false;
		
		// проверяем доступы и ограничиваем возможные варианты
		else
		// БДЗ
		if ( this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsFedLgot') != 1 && this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsBDZ') == 1 )
		{
			store = [
				[6,	lang['snils']],
				[9,	lang['dokument']],
				[10, lang['adres_registratsii']],
				[11, lang['adres_projivaniya']],
				[12, lang['mesto_rabotyi']],
				[18, lang['nomer_telefona']],
				[20, textINN],
				[23, lang['grajdanstvo']]
			]
		}
		// федеральный льготник
		if ( this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsFedLgot') == 1 && this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsBDZ') != 1 )
		{
			store = [
				//[7,	lang['sotsialnyiy_status']],
				[8,	lang['polis']],
				[9,	lang['dokument']],
				[10, lang['adres_registratsii']],
				[11, lang['adres_projivaniya']],
				[12, lang['mesto_rabotyi']],
				[18, lang['nomer_telefona']],
				[20, textINN],
				[23, lang['grajdanstvo']]
			]
		}		
		// и тот и другой
		if ( this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsFedLgot') == 1 && this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsBDZ') == 1 )
		{
			store = [
				[8,	lang['polis']],
				[18, lang['nomer_telefona']],
				[20, textINN],
				[23, lang['grajdanstvo']]
			]
		}
		
		if ( this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsFedLgot') != 1 && this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsBDZ') != 1 )
		{
			store = [
				[1,	lang['familiya']],
				[2,	lang['imya']],
				[3,	lang['otchestvo']],
				[4,	lang['data_rojdeniya']],
				[5,	lang['pol']],
				[6,	lang['snils']],
				[7,	lang['sotsialnyiy_status']],
				[8,	lang['polis']],
				[9,	lang['dokument']],
				[10, lang['adres_registratsii']],
				[11, lang['adres_projivaniya']],
				[12, lang['mesto_rabotyi']],
				[16, lang['edinyiy_nomer_polisa']],
				[18, lang['nomer_telefona']],
				[20, textINN],
				[23, lang['grajdanstvo']]
			]
		}
		
		if ( getRegionNick() == 'perm' ) {
			var tmp = [
				[1,	lang['familiya']],
				[2,	lang['imya']],
				[3,	lang['otchestvo']],
				[4,	lang['data_rojdeniya']],
				[5,	lang['pol']]
			]

			var begDate = this.findById('PVW_PersonInfoFrame').getFieldValue('Polis_begDate');
			var endDate = this.findById('PVW_PersonInfoFrame').getFieldValue('Polis_endDate');
			var currDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');

			var hasPolis = (!Ext.isEmpty(begDate) && begDate <= currDate && (Ext.isEmpty(endDate) || endDate > currDate));
			if (!hasPolis) {
				tmp.push([8, lang['polis']]);
			}

			store = store.concat(tmp).sort(function(a, b){return a[0] - b[0]});

			var length = store.length;
			for(var i=0; i<length; i++) {
				if (store[i] && store[i+1] && store[i][0] == store[i+1][0]) {
					store.splice(i, 1);
				}
			}
		}
		
		// супер админу показываем все
		if (
			isSuperAdmin() ||
			(getRegionNick().inlist(['buryatiya','vologda','adygeya']) && isUserGroup('editorperiodics'))
		)
		{
			store = [
				[1,	lang['familiya']],
				[2,	lang['imya']],
				[3,	lang['otchestvo']],
				[4,	lang['data_rojdeniya']],
				[5,	lang['pol']],
				[6,	lang['snils']],
				[7,	lang['sotsialnyiy_status']],
				[8,	lang['polis']],
				[9,	lang['dokument']],
				[10, lang['adres_registratsii']],
				[11, lang['adres_projivaniya']],
				[12, lang['mesto_rabotyi']],
				[16, lang['edinyiy_nomer_polisa']],
				[18, lang['nomer_telefona']],
				[20, textINN],
				[22, lang['semeynoe_polojenie']],
				[23, lang['grajdanstvo']]
			]

			if ( getRegionNick() != 'kz' ) {
				store.push([21, lang['nomer_sotsialnoy_kartyi']]);
			}
		}
		
		grid.getColumnModel().setEditor(
			5,
			new Ext.grid.GridEditor(
				new Ext.form.ComboBox({
					allowBlank: false,
					store: store
				}), {listeners: {'canceledit': this.onCancelAdd.createDelegate(this)}})
		);
		grid.getColumnModel().setEditable(5, true);
		grid.startEditing(cell[0], cell[1]);
	},
	personEvnClass_Values: [
		null,
		lang['familiya'],
		lang['imya'],
		lang['otchestvo'],
		lang['data_rojdeniya'],
		lang['pol'],
		lang['snils'],
		lang['sotsialnyiy_status'],
		lang['polis'],
		lang['dokument'],
		lang['adres_registratsii'],
		lang['adres_projivaniya'],
		lang['mesto_rabotyi'],
		null,
		null,
		null,
		lang['edinyiy_nomer_polisa'],
		null,
		lang['nomer_telefona'],
		null,
		(getRegionNick() == 'kz')?lang['iin']:lang['inn'],
		lang['nomer_sotsialnoy_kartyi'],
		null,
		lang['grajdanstvo']
	],
	addObjectData: null,
	onValidateEdit: function(o) {
		// редактировали новые данные, необходимо запомнить что ввели и переключиться на ввод даты
		log(o.record,o.column,'ddddd')
		if ( !(o.record.get('PersonEvn_id') > 0) && o.column == 6 ) {
			/*
			* проверяем обязательность непустого значения у полей: 
				– Фамилия; обязательное
				– Имя; обязательное
				– Отчество; не обязательное
				– Дата рождения; обязательное
				– Пол; обязательное
				– СНИЛС; обязательное
				– Социальный статус; обязательное (кроме Казахстана https://redmine.swan.perm.ru/issues/77225)
				– Полис; обязательное 
				– Документ; обязательное
				– Адрес регистрации; обязательное
				– Адрес проживания; обязательное
				– Место работы; не обязательное
				– Отказ от льготы; обязательное
				– Единый номер полиса; обязательное
				– Номер телефона; не обязательное
				– ИНН; не обязательное
				– Номер социальной карты. не обязательное
			*/
			var notRequiredPeriodicTypes = ['3', '12', '18', '20', '21'];

			if ( getRegionNick() == 'kz' ) {
				notRequiredPeriodicTypes.push('7');
			}

			if ( this.periodicSingleFields[o.record.get('PersonEvnClass_id')] && !o.record.get('PersonEvnClass_id').toString().inlist(notRequiredPeriodicTypes) && o.value == '' )
			{
				sw.swMsg.show({
					title: lang['oshibka'],
					msg: lang['znachenie_obyazatelno_k_zapolneniyu_prodoljit_vvod'],
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							// продолжаем редактирование
							o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 6);
							o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 6);
							this.startEditData();
						}
						else {
							o.grid.stopEditing(true);
							o.grid.getColumnModel().setEditable(5, false);
							o.grid.getColumnModel().setEditable(6, false);
							o.grid.getColumnModel().setEditable(7, false);
							// удаляем строку
							o.grid.getStore().removeAt(o.grid.getStore().getCount() - 1);
							// устанавливаем фокус на нужное место
							if ( o.grid.getStore().getCount() > 0 )
							{
								o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 6);
								o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 6);
							}
						}
					}.createDelegate(this)
				});
				return false;
			}
		}else if(!(o.record.get('PersonEvn_id') > 0) && o.column == 7 ){
			if ( Ext.isEmpty(o.value) )
			{
				sw.swMsg.show({
					title: lang['oshibka'],
					msg: lang['znachenie_obyazatelno_k_zapolneniyu_prodoljit_vvod'],
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							// продолжаем редактирование
							o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 7);
							o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 7);
							this.startEditData();
						}
						else {
							o.grid.stopEditing(true);
							o.grid.getColumnModel().setEditable(5, false);
							o.grid.getColumnModel().setEditable(6, false);
							o.grid.getColumnModel().setEditable(7, false);
							// удаляем строку
							o.grid.getStore().removeAt(o.grid.getStore().getCount() - 1);
							// устанавливаем фокус на нужное место
							if ( o.grid.getStore().getCount() > 0 )
							{
								o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 7);
								o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 7);
							}
						}
					}.createDelegate(this)
				});
				return false;
			}
		}
	},
	onAfterEdit: function(o) {
		o.grid.stopEditing(true);
		o.grid.getColumnModel().setEditable(5, false);
		o.grid.getColumnModel().setEditable(6, false);
		o.grid.getColumnModel().setEditable(7, false);
		if ( o.column == 2 )
		{
			var record = o.record;
			// Удаление полиса из БДЗ ну нужно никому. в том числе и суперадмину. (refs #8441)
			// А вот в Астрахани это могут делать все, кому не лень, ибо https://redmine.swan.perm.ru/issues/52142
			if (
				(
					!getRegionNick().inlist([ 'astra', 'kareliya','pskov','buryatiya','vologda' ])
					//|| (getRegionNick() == 'astra' && isSuperAdmin() == false && isLpuAdmin() == false)
					|| (getRegionNick() == 'kareliya' && isSuperAdmin() == false && isLpuAdmin() == false)
					|| (getRegionNick().inlist(['ekb','pskov','buryatiya','vologda','adygeya'])&&!isUserGroup('editorperiodics')&&!isSuperAdmin())
				)
				&& (
					(record.get('PersonEvnClass_id') == 8 && record.get('Server_id') == 0)
					|| record.get('PersonEvn_readOnly') == 1
				)
			) {
				record.set('check',false);
				record.commit();
				return true;
			} 
				

			if ( getGlobalOptions().superadmin == true||(getRegionNick().inlist(['ekb','pskov','buryatiya','vologda','adygeya'])&&isUserGroup('editorperiodics')) ){
				record.commit();
				return true;
			}

			// БДЗ
			if ( this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsBDZ') == 1 && ['1', '2', '3', '4', '5', '7', '16'].in_array(record.get('PersonEvnClass_id')) )
			{								
				record.set('check',false);
				record.commit();
				return true;
			}
			// федеральный льготник
			if ( this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsFedLgot') == 1 && ['1', '2', '3', '4', '5', '6', '16'].in_array(record.get('PersonEvnClass_id')) )
			{								
				record.set('check',false);
				record.commit();
				return true;
			}
			// вообще недоступны
			if ( ['21'].in_array(record.get('PersonEvnClass_id')) )
			{
				record.set('check',false);
				record.commit();
				return true;
			}
			record.commit();
			return true;
		}
		// редактировали новые данные, необходимо запомнить что ввели и переключиться на ввод даты
		if ( !(o.record.get('PersonEvn_id') > 0) && o.column == 6 ) {		
			// простое поле
			if ( this.periodicSingleFields[o.record.get('PersonEvnClass_id')] )
			{
				// если это СНИЛС, то проверяем формат
				if ( o.record.get('PersonEvnClass_id') == 6 )
				{
					if ( this.checkPersonSnils(o.value) === false )
					{
						// сообщаем о неправильно введеном СНИЛСе
						sw.swMsg.alert(lang['oshibka'], lang['snils_cheloveka_vveden_neverno_ne_udovletvoryaet_pravilam_formirovaniya_snils'], function() {
							o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 6);
							o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 6);
							this.startEditData();
						}.createDelegate(this));
						return false;
					}
				}
				// если единый номер полиса, то проверяем формат
				if(this.periodicSingleFields[o.record.get('PersonEvnClass_id')] == 'Federal_Num'){
					var polis_num = String(o.value);
					if (!checkEdNumFedSignature(polis_num) && getRegionNick() != 'kz') {
						switch (getGlobalOptions().enp_validation_control) {
							case 'warning':		// Выводим предупреждение с возможностью продолжения
								sw.swMsg.show({
									buttons: sw.swMsg.YESNO,
									fn: function(buttonId, text, obj) {
										if ('yes' != buttonId) {
											o.grid.getSelectionModel().select(o.row, 6);
											o.grid.getView().focusCell(o.row, 6);
											this.startEditData();
											return false;
										}
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: "Единый номер полиса не соответствует формату. Продолжить сохранение?",
									title: lang['vopros']
								});
								break;
							case 'deny':		// Выводим сообщение об ошибке
								sw.swMsg.alert('Ошибка при редактировании периодики', 'Единый номер полиса не соответствует формату', function() {
									o.grid.getSelectionModel().select(o.row, 6);
									o.grid.getView().focusCell(o.row, 6);
									this.startEditData();
								}.createDelegate(this));
								return false;
						}
					}
				}
				// если поле комбобокс, то выводим в ячейке отображаемое значение
				if ( ['5','22'].in_array(o.record.get('PersonEvnClass_id')) )
				{
					var field = o.grid.getColumnModel().getCellEditor( o.column, o.row ).field;
					var index = field.getStore().findBy(function(rec) { return rec.get(field.valueField) == o.value; });
					if ( index >= 0 )
					{
						o.record.set('PersonEvn_Value', field.getStore().getAt(index).get(field.displayField));
						o.record.commit();
					}
				}
				if ( ['7'].in_array(o.record.get('PersonEvnClass_id')) )
				{
					var field = o.grid.getColumnModel().getCellEditor( o.column, o.row ).field;
					var record = field.getStore().getById(o.value);
					if ( !Ext.isEmpty(record) )
					{
						o.record.set('PersonEvn_Value', record.get(field.displayField));
						o.record.commit();
					}
				}
				// если поле дата, то выводим в ячейке нормальное значение
				if ( ['4'].in_array(o.record.get('PersonEvnClass_id')) )
				{
					o.record.set('PersonEvn_Value', Ext.util.Format.date(o.value, 'd.m.Y'));
					o.record.commit();
					o.value = Ext.util.Format.date(o.value, 'd.m.Y');
				}
				if ( o.record.dirty ) {
					o.record.commit();
				}
				this.addObjectData = {};
				this.addObjectData[this.periodicSingleFields[o.record.get('PersonEvnClass_id')]] = o.value;
				// редактируем значение даты
				o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 7);
				o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 7);
				this.startEditData();
			}
			else
			{
				// удаляем строку
				o.grid.getStore().removeAt(o.grid.getStore().getCount() - 1);
				// устанавливаем фокус на нужное место
				if ( o.grid.getStore().getCount() > 0 )
				{
					o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 6);
					o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 6);
				}
			}
			return true;
		}
		// редактировали новую дату, необходимо сохранять
		if ( !(o.record.get('PersonEvn_id') > 0) && o.column == 7 ) {
			// проверка на пустое поле
			if ( this.periodicSingleFields[o.record.get('PersonEvnClass_id')] && !['3', '12', '18', '20', '21'].in_array(o.record.get('PersonEvnClass_id')) && o.record.get('PersonEvn_Value') == '' )
			{
				sw.swMsg.show({
					title: lang['oshibka'],
					msg: lang['znachenie_periodiki_obyazatelno_k_zapolneniyu_prodoljit_vvod'],
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							// продолжаем редактирование
							o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 6);
							o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 6);
							this.startEditData();
						}
						else {
							o.grid.stopEditing(true);
							o.grid.getColumnModel().setEditable(5, false);
							o.grid.getColumnModel().setEditable(6, false);
							o.grid.getColumnModel().setEditable(7, false);
							// удаляем строку
							o.grid.getStore().removeAt(o.grid.getStore().getCount() - 1);
							// устанавливаем фокус на нужное место
							if ( o.grid.getStore().getCount() > 0 )
							{
								o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 7);
								o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 7);
							}
						}
					}.createDelegate(this)
				});
				return false;
			}
			// проверка на больше последней даты периодики того же типа
			if ( getRegionNick() == 'perm') {
				var PersonEvnClass_id = o.record.get('PersonEvnClass_id');
				var maxDT = null;
				o.grid.getStore().each(function(rec) {
					if (rec.get('PersonEvnClass_id') == PersonEvnClass_id && (!maxDT || maxDT < rec.get('PersonEvn_insDT'))) {
						maxDT = rec.get('PersonEvn_insDT');
					}
				});

				if (Date.parseDate(Ext.util.Format.date(o.value, 'd.m.Y'), 'd.m.Y') < maxDT) {
					var tpl = new Ext.Template(
						'Для выбранного человека добавление периодики с типом «{PersonEvnClass_Name}»',
						'<br/>возможно с датой более {maxDate}'
					);
					sw.swMsg.show({
						title: lang['oshibka'],
						msg: tpl.apply({
							PersonEvnClass_Name: o.record.get('PersonEvnClass_Name'),
							maxDate: maxDT.format('d.m.Y')
						}),
						buttons: Ext.Msg.OK,
						fn: function ( buttonId ) {
							o.grid.stopEditing(true);
							o.grid.getColumnModel().setEditable(5, false);
							o.grid.getColumnModel().setEditable(6, false);
							o.grid.getColumnModel().setEditable(7, false);
							// удаляем строку
							o.grid.getStore().removeAt(o.grid.getStore().getCount() - 1);
							// устанавливаем фокус на нужное место
							if ( o.grid.getStore().getCount() > 0 )
							{
								o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 7);
								o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 7);
							}
						}.createDelegate(this)
					});
					return false;
				}
			}
			// проверка на больше текущей даты
			if ( Date.parseDate(Ext.util.Format.date(o.value, 'd.m.Y'), 'd.m.Y') > Date.parseDate(getGlobalOptions().date, 'd.m.Y') )
			{
				sw.swMsg.show({
					title: lang['oshibka'],
					msg: lang['data_nachala_deystviya_periodiki_bolshe_tekuschey_datyi_prodoljit_vvod'],
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							// продолжаем редактирование
							o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 7);
							o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 7);
							this.startEditData();
						}
						else {
							o.grid.stopEditing(true);
							o.grid.getColumnModel().setEditable(5, false);
							o.grid.getColumnModel().setEditable(6, false);
							o.grid.getColumnModel().setEditable(7, false);
							// удаляем строку
							o.grid.getStore().removeAt(o.grid.getStore().getCount() - 1);
							// устанавливаем фокус на нужное место
							if ( o.grid.getStore().getCount() > 0 )
							{
								o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 7);
								o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 7);
							}
						}
					}.createDelegate(this)
				});
				return false;
			}
			//if ( o.value > Date(getGlobalOptions().date) )
			// простое поле
			var params = this.addObjectData;			
			params.Date = Ext.util.Format.date(o.value, 'd.m.Y');
			params.Time = Ext.util.Format.date(o.value, 'H:i');
			params.PersonEvn_id = o.record.get('PersonEvn_id');
			params.PersonEvnClass_id = o.record.get('PersonEvnClass_id');
			params.Server_id = o.record.get('Server_id');
			params.Person_id = o.record.get('Person_id');
			if ( ['8', '9', '10', '11', '12', '23'].in_array(o.record.get('PersonEvnClass_id')) )
				params.EvnType = this.periodicStructFields[o.record.get('PersonEvnClass_id')];
			else
				params.EvnType = this.periodicSingleFields[o.record.get('PersonEvnClass_id')];
			if (o.ignoreOMSSprTerrDateCheck && o.ignoreOMSSprTerrDateCheck == 2) params.ignoreOMSSprTerrDateCheck = 2;
			if (o.ignoreСhecksumINN && o.ignoreСhecksumINN == 2) params.ignoreСhecksumINN = 2;
			// отправляем запрос на сохранение
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=Person&m=saveAttributeOnDate',
				params: params,
				callback: function(options, success, response) {
					loadMask.hide();
					if ( success ) {
						/*log(['sdfsd',params])
						if(params.Federal_Num){
							log('dd')
									this.doSaveOnThePersonEvn(
									params.FederalEvn_id,
									params.FederalServer_id,
									'Federal_Num',
									{Federal_Num:params.Federal_Num},
									function() {
										Ext.getCmp('PVW_PeriodicViewGrid').ViewActions.action_refresh.execute();
									}.createDelegate(this)
								);
							}*/
						if ( response.responseText.length > 0 ) {
							var resp_obj = Ext.util.JSON.decode(response.responseText);

							if ( resp_obj.success == false ) {
								Ext.getCmp('PVW_PeriodicViewGrid').ViewActions.action_refresh.execute();
							} else {
							
								if (resp_obj.Alert_Code && resp_obj.Alert_Code) {
									sw.swMsg.show({
										buttons: Ext.Msg.OKCANCEL,
										fn: function ( buttonId ) {
											if ( buttonId == 'ok' ) {
												o.ignoreOMSSprTerrDateCheck = 2;
												this.onAfterEdit(o);
											}
										}.createDelegate(this),
										msg: resp_obj.Alert_Msg,
										title: 'Вопрос'
									});
									return false;
								}

								if (resp_obj.ignoreСhecksumINN && resp_obj.ignoreСhecksumINN == 2) {
									sw.swMsg.show({
										buttons: Ext.Msg.OKCANCEL,
										fn: function ( buttonId ) {
											if ( buttonId == 'ok' ) {
												o.ignoreСhecksumINN = 2;
												this.onAfterEdit(o);
											}
										}.createDelegate(this),
										msg: resp_obj.Alert_Msg,
										title: 'Вопрос'
									});
									return false;
								}
								
								this.findById('PVW_PersonInfoFrame').load({ Person_id: this.personId, Server_id: this.serverId, callback: function() {
									this.findById('PVW_PersonInfoFrame').setPersonTitle();
								}.createDelegate(this) });
								this.findById('PVW_PeriodicViewGrid').getGrid().getStore().load({
									params: {
										Person_id: this.personId,
										Server_id: this.serverId
									},
									callback: function() {
										this.findById('PVW_PeriodicViewGrid').getGrid().getSelectionModel().select(o.row, 6);
										this.findById('PVW_PeriodicViewGrid').getGrid().getView().focusCell(o.row, 6);
									}.createDelegate(this)
								});
								
							}
						}
					}
				}.createDelegate(this)
			});
			return true;
		}
		// редактировали добавленую ячейку c новой периодикой
		if ( o.column == 5 )
		{
			// устанавливаем класс периодики
			o.record.set('PersonEvnClass_id', o.value);
			o.record.set('PersonEvnClass_Name', this.personEvnClass_Values[o.value]);
			o.record.commit();
			// редактируем значение периодики
			o.grid.getSelectionModel().select(o.grid.getStore().getCount() - 1, 6);
			o.grid.getView().focusCell(o.grid.getStore().getCount() - 1, 6);
			this.startEditData();
		}
		// редактировали данные периодики
		log('sd');
		if ( o.column == 6 )
		{
			/*
			* проверяем обязательность непустого значения у полей: 
				– Фамилия; обязательное
				– Имя; обязательное
				– Отчество; не обязательное
				– Дата рождения; обязательное
				– Пол; обязательное
				– СНИЛС; обязательное
				– Социальный статус; обязательное (кроме Казахстана https://redmine.swan.perm.ru/issues/77225)
				– Полис; обязательное 
				– Документ; обязательное
				– Адрес регистрации; обязательное
				– Адрес проживания; обязательное
				– Место работы; не обязательное
				– Отказ от льготы; обязательное
				– Единый номер полиса; обязательное
				– Номер телефона; не обязательное
				– ИНН; не обязательное
				– Номер социальной карты. не обязательное
				– Гражданство. необязательное
				-- ИНН
			*/
			var notRequiredPeriodicTypes = ['3', '12', '18', '20', '21', '23'];

			if ( getRegionNick() == 'kz' ) {
				notRequiredPeriodicTypes.push('7');
			}

			if ( this.periodicSingleFields[o.record.get('PersonEvnClass_id')] && !o.record.get('PersonEvnClass_id').toString().inlist(notRequiredPeriodicTypes) && o.value == '' )
			{
				sw.swMsg.show({
					title: lang['oshibka'],
					msg: lang['znachenie_obyazatelno_k_zapolneniyu_prodoljit_vvod'],
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							// продолжаем редактирование
							o.record.set('PersonEvn_Value', o.originalValue);
							o.record.commit();
							o.grid.getSelectionModel().select(o.row, 6);
							o.grid.getView().focusCell(o.row, 6);
							this.startEditData();
						}
						else {
							// возврат предыдущего значения и отмена редактирования							
							o.record.set('PersonEvn_Value', o.originalValue);
							o.record.commit();
							o.grid.getSelectionModel().select(o.row, 6);
							o.grid.getView().focusCell(o.row, 6);
						}
					}.createDelegate(this)
				});
				return false;
			}
			
			if ( this.periodicSingleFields[o.record.get('PersonEvnClass_id')] && o.value != o.originalValue	)
			{
				// если это СНИЛС, то проверяем формат
				if ( o.record.get('PersonEvnClass_id') == 6 )
				{
					if ( this.checkPersonSnils(o.value) === false )
					{
						// сообщаем о неправильно введеном СНИЛСе
						sw.swMsg.alert(lang['oshibka'], lang['snils_cheloveka_vveden_neverno_ne_udovletvoryaet_pravilam_formirovaniya_snils'], function() {
							o.grid.getSelectionModel().select(o.row, 6);
							o.grid.getView().focusCell(o.row, 6);
							this.startEditData();
						}.createDelegate(this));
						return false;
					}
				}
				
				// если это ИНН
				if ( o.record.get('PersonEvnClass_id') == 20 && getRegionNick() != 'kz' && Ext.isEmpty(o.ignoreСhecksumINN) )
				{
					var inn_correctness_control = (getGlobalOptions().inn_correctness_control) ? parseInt(getGlobalOptions().inn_correctness_control, 10) : 1;

					// проверку делаем только если в настройках стоит Предупреждение. При запрете проверка будет на сервере
					if ( inn_correctness_control == 2 && this.checkPersonINN(o.value) === false )
					{
						// сообщаем о неправильно введеном ИНН
						sw.swMsg.show({
							title: 'Внимание!',
							msg: 'Ошибка проверки контрольной суммы в ИНН. Убедитесь, что ИНН указан верно.',
							buttons: {yes: 'Продолжить', no: 'Отмена'},
							closable: false,
							fn: function(butn){
								if ( butn == 'yes' ) {
									o.ignoreСhecksumINN = 2;
									this.onAfterEdit(o);
								}
								else {						
									//o.record.set('PersonEvn_Value', o.originalValue);
									o.grid.getSelectionModel().select(o.row, 6);
									o.grid.getView().focusCell(o.row, 6);
									this.startEditData();
								}
							}.createDelegate(this)
						});
						return false;
					}
				}

				// если единый номер полиса, то проверяем формат
				if(this.periodicSingleFields[o.record.get('PersonEvnClass_id')] == 'Federal_Num'){
					var polis_num = String(o.value);
					if (!checkEdNumFedSignature(polis_num) && getRegionNick() != 'kz') {
						switch (getGlobalOptions().enp_validation_control) {
							case 'warning':		// Выводим предупреждение с возможностью продолжения
								sw.swMsg.show({
									buttons: sw.swMsg.YESNO,
									fn: function(buttonId, text, obj) {
										if ('yes' != buttonId) {
											o.grid.getSelectionModel().select(o.row, 6);
											o.grid.getView().focusCell(o.row, 6);
											this.startEditData();
											return false;
										}
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: "Единый номер полиса не соответствует формату. Продолжить сохранение?",
									title: lang['vopros']
								});
								break;
							case 'deny':		// Выводим сообщение об ошибке
								sw.swMsg.alert('Ошибка при редактировании периодики', 'Единый номер полиса не соответствует формату', function() {
									o.grid.getSelectionModel().select(o.row, 6);
									o.grid.getView().focusCell(o.row, 6);
									this.startEditData();
								}.createDelegate(this));
								return false;
						}
					}
				}
								
				// если поле комбобокс, то выводим в ячейке отображаемое значение
				if ( ['5','22'].in_array(o.record.get('PersonEvnClass_id')) )
				{
					var field = o.grid.getColumnModel().getCellEditor( o.column, o.row ).field;
					var index = field.getStore().findBy(function(rec) { return rec.get(field.valueField) == o.value; });
					if ( index >= 0 )
					{
						o.record.set('PersonEvn_Value', field.getStore().getAt(index).get(field.displayField));
						o.record.commit();
					}
				}
				if ( ['7'].in_array(o.record.get('PersonEvnClass_id')) )
				{
					var field = o.grid.getColumnModel().getCellEditor( o.column, o.row ).field;
					var record = field.getStore().getById(o.value);
					if ( !Ext.isEmpty(record) )
					{
						o.record.set('PersonEvn_Value', record.get(field.displayField));
						o.record.commit();
					}
				}
				// если поле дата, то выводим в ячейке нормальное значение
				if ( ['4'].in_array(o.record.get('PersonEvnClass_id')) )
				{
					o.record.set('PersonEvn_Value', Ext.util.Format.date(o.value, 'd.m.Y'));
					o.record.commit();
					o.value = Ext.util.Format.date(o.value, 'd.m.Y');
				}
				if ( o.record.dirty ) {
					o.record.commit();
				}
				var saving_data = {};
				
				saving_data[this.periodicSingleFields[o.record.get('PersonEvnClass_id')]] = o.value;
				if(typeof o.record.get('PersonEvn_insDT') !== "undefined")
				{
				    saving_data.Federal_begDate = o.record.get('PersonEvn_insDT').format('d.m.Y');
				}
				this.doSaveOnThePersonEvn(
					o.record.get('PersonEvn_id'),
					o.record.get('Server_id'),
					this.periodicSingleFields[o.record.get('PersonEvnClass_id')],
					saving_data,
					function(options, success, response) {
						if ( response.responseText.length > 0 ) {
							var result = Ext.util.JSON.decode(response.responseText);

							if ( result.success === true && result.Cancel_Error_Handle != undefined && result.Cancel_Error_Handle === true ) {
								if (result.Error_Code == 'checkFederalNumUnique') {
									Ext.Msg.alert(
										lang['oshibka'],
										result.Error_Msg,
										function() {
											o.grid.getSelectionModel().select(o.row, 6);
											o.grid.getView().focusCell(o.row, 6);
											this.startEditData();
										}.createDelegate(this)
									);
								}
							}
						}
						this.findById('PVW_PersonInfoFrame').load({ Person_id: this.personId, Server_id: this.serverId, callback: function() {
							this.findById('PVW_PersonInfoFrame').setPersonTitle();
						}.createDelegate(this) });
						
					}.createDelegate(this)
				);
				return true;
			}
		}
		// редактируем дату периодики
		if ( o.column == 7 )
		{log('sw')
			if ( o.value != o.originalValue )
			{
				// проверка на больше текущей даты
				if ( Date.parseDate(Ext.util.Format.date(o.value, 'd.m.Y'), 'd.m.Y') > Date.parseDate(getGlobalOptions().date, 'd.m.Y') )
				{
					sw.swMsg.show({
						title: lang['oshibka'],
						msg: lang['data_nachala_deystviya_periodiki_bolshe_tekuschey_datyi_prodoljit_vvod'],
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								// продолжаем редактирование
								o.grid.getSelectionModel().select(o.row, 7);
								o.grid.getView().focusCell(o.row, 7);
								this.startEditData();
							}
							else {
								o.grid.stopEditing(true);
								o.grid.getColumnModel().setEditable(5, false);
								o.grid.getColumnModel().setEditable(6, false);
								o.grid.getColumnModel().setEditable(7, false);
							}
						}.createDelegate(this)
					});
					return false;
				}

				// @todo Нужна проверка, чтобы выбранный соц. статус был доступен для выбора на указанную дату
				// @task https://redmine.swan.perm.ru/issues/89754
				if ( 'SocStatus_id' == this.periodicSingleFields[o.record.get('PersonEvnClass_id')] ) {
					var
						date = (typeof o.value == 'object' ? Date.parseDate(Ext.util.Format.date(o.value, 'd.m.Y'), 'd.m.Y') : Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
						socStatusIncorrect = false;

					this.socStatusStore.each(function(rec) {
						if (
							rec.get('SocStatus_Name') == o.record.get('PersonEvn_Value')
							&& !Ext.isEmpty(rec.get('SocStatus_endDT'))
							&& (typeof rec.get('SocStatus_endDT') == 'object' ? rec.get('SocStatus_endDT') : getValidDT(rec.get('SocStatus_endDT'), '')) < date
						) {
							socStatusIncorrect = true;
						}
					});

					if ( socStatusIncorrect == true ) {
						o.record.set('PersonEvn_insDT', o.originalValue);
						o.record.commit();
						sw.swMsg.alert(lang['oshibka'], lang['vybranniy_sotsialniy_status_nedeistvitelen_na_ukazannuyu_datu']);
						return false;
					}
				}
			
				var params = {
					Date: Ext.util.Format.date(o.value, 'd.m.Y'),
					Time: Ext.util.Format.date(o.value, 'H:i:s'),
					PersonEvn_id: o.record.get('PersonEvn_id'),
					PersonEvnClass_id: o.record.get('PersonEvnClass_id'),
					Server_id: o.record.get('Server_id')
				};
				// отправляем запрос на сохранение
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
				loadMask.show();
				Ext.Ajax.request({
					url: '/?c=Person&m=editPersonEvnDate',
					params: params,
					callback: function(options, success, response) {
						loadMask.hide();
						var result = Ext.util.JSON.decode(response.responseText);
						if(result.Error_Msg){
							Ext.Msg.alert(
								lang['oshibka'],
								result.Error_Msg,
								function() {
									o.record.set('PersonEvn_insDT', o.originalValue);
									o.record.commit();
									return false;
								}
							);
							return false;
						}
						this.findById('PVW_PersonInfoFrame').load({ Person_id: this.personId, Server_id: this.serverId, callback: function() {
							this.findById('PVW_PersonInfoFrame').setPersonTitle();
						}.createDelegate(this) });
						this.findById('PVW_PeriodicViewGrid').getGrid().getStore().load({
							params: {
								Person_id: this.personId,
								Server_id: this.serverId
							},
							callback: function() {
								this.findById('PVW_PeriodicViewGrid').getGrid().getSelectionModel().select(o.row, 7);
								this.findById('PVW_PeriodicViewGrid').getGrid().getView().focusCell(o.row, 7);
							}.createDelegate(this)
						});
					}.createDelegate(this)
				});				
			}
			else
			{
				o.record.set('PersonEvn_insDT', o.originalValue);
				o.record.commit();				
			}
		}
		return false;
	},
	onCancelEdit: function(o) {log(o,'ddddddddddddddee33');
		var grid = this.findById('PVW_PeriodicViewGrid').getGrid();
		grid.getSelectionModel().select(o.row, o.col);
		// удаляем, если эта ячейка добавлена
		if ( !(o.record.get('PersonEvn_id') > 0) )
		{
			grid.getStore().removeAt(grid.getStore().getCount() - 1);
			if ( grid.getStore().getCount() > 0 )
			{
				grid.getSelectionModel().select(grid.getStore().getCount() - 1, 6);
				grid.getView().focusCell(grid.getStore().getCount() - 1, 6);
			}
		}
	},
	startEditData: function() {
		// проверяем текущую ячейку
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
		var isPerm = (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm');
		var grid = this.findById('PVW_PeriodicViewGrid').getGrid();
		var cell = grid.getSelectionModel().getSelectedCell();		
		if ( !cell || cell.length == 0 || (cell[1] != 6 && cell[1] != 7) )
			return false;
		var record = grid.getSelectionModel().getSelected();
		if ( !record )
			return false;
		// редактируем дату
		if(getRegionNick().inlist(['ekb','pskov'])&&!isUserGroup('editorperiodics')&&!isSuperAdmin())
			return false;
		if ( cell[1] == 7 )
		{	
			log(grid);
			var DefaultDT = grid.getStore().findBy(function(rec){return(!Ext.isEmpty(rec.get('PersonEvn_id'))&&rec.get('PersonEvnClass_id')==record.get('PersonEvnClass_id'))});
			
			log(DefaultDT,record)
			
			var DateEdit =new sw.Promed.SwDateField({
				allowBlank: true,
				fireAfterEditOnEmpty: true,
				format: 'd.m.Y H:i:s',
				maxValue: getGlobalOptions().date,
				minValue: '01.01.1861 00:00:00',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 99:99:99', false) ],
				enableKeyEvents: true,
				listeners: {
					'keypress': function(field, e) {
						if ( e.getKey() == e.TAB )
							field.fireEvent('blur', field);
					}
				}
			});
			/*if(!Ext.isEmpty(record.get('PersonEvn_newDT'))&&record.get('PersonEvnClass_id').inlist([10,11])&&((record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == ''))){
				DateEdit.setValue(Ext.util.Format.date(record.get('PersonEvn_newDT'), 'd.m.Y'));
				log(DateEdit,Ext.util.Format.date(record.get('PersonEvn_newDT'), 'd.m.Y'));
				DateEdit.fireEvent('blur',DateEdit);
				return true;
			}*/
			grid.getColumnModel().setEditor( 
						7,
						new Ext.grid.GridEditor(DateEdit, {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);		
			grid.getColumnModel().setEditable(7, true);
			
			
			if(DefaultDT<0&&!record.get('PersonEvn_insDT')){
				log(DateEdit,'45454')
				
				DateEdit.on('focus',function(){DateEdit.setValue('01.01.2000 00:00:00');});
				
			}
			if(!Ext.isEmpty(record.get('PersonEvn_newDT'))&&record.get('PersonEvnClass_id').inlist([10,11])&&((record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == ''))){
				DateEdit.on('focus',function(){DateEdit.setValue(record.get('PersonEvn_newDT'));});
				log(DateEdit,record.get('PersonEvn_newDT'),Ext.util.Format.date(record.get('PersonEvn_newDT'), 'd.m.Y'))
				
				
			}
			
			
			
			grid.startEditing(cell[0], cell[1]);
							
		}
		// редактируем сами данные о событии
		if ( cell[1] == 6 )
		{
			// в зависимости от класса события
			switch ( record.get('PersonEvnClass_id') ) {
				case 1: case 2: case 3:
					grid.getColumnModel().setEditor(
						6,
						new Ext.grid.GridEditor(this.getFioEditor(), {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);
					this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, true);
					grid.startEditing(cell[0], cell[1]);
				break;
				case 4:
					grid.getColumnModel().setEditor( 
						6,
						new Ext.grid.GridEditor(this.getBirthdayEditor(), {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);
					this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, true);
					grid.startEditing(cell[0], cell[1]);
				break;
				case 5:
					grid.getColumnModel().setEditor( 
						6,
						new Ext.grid.GridEditor(this.getSexEditor(), {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);					
					this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, true);
					grid.startEditing(cell[0], cell[1]);
				break;
				case 6:
					grid.getColumnModel().setEditor( 
						6,
						new Ext.grid.GridEditor(this.getSnilsEditor(), {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);					
					this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, true);
					grid.startEditing(cell[0], cell[1]);
				break;
				case 7:
					grid.getColumnModel().setEditor( 
						6,
						new Ext.grid.GridEditor(this.getSocStatusEditor(), {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);					
					this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, true);
					grid.startEditing(cell[0], cell[1]);
				break;
				case 8:
					var that = this;
					var params = {
						clearEditForm: that.clearEditForm,
						ignoreOnClose: true,
						onClose: function() {
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
								that.onCancelAdd();
						}.createDelegate(that),
						callback: function(values) {
							// сохраняем полис
							var saving_data = {
								OMSSprTerr_id: values.OMSSprTerr_id,
								OrgSMO_id: values.OrgSMO_id,
								Polis_Ser: values.Polis_Ser,
								Polis_Num: values.Polis_Num,
								Polis_Guid: values.Polis_Guid,
								Polis_begDate: values.Polis_begDate,
								Polis_endDate: values.Polis_endDate,
								PolisType_id: values.PolisType_id,
								Federal_Num:values.Federal_Num,
								PolisFormType_id:values.PolisFormType_id
							};
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
							{
								// добавляем, поэтому запоминаем сохраняемые данные
								record.set('PersonEvn_Value', values.Polis_PolisString);
								record.set("PersonEvn_insDT","");
								record.commit();
								that.addObjectData = saving_data;
								/*if(values.FederalEvn_id){
									saving_data.FederalEvn_id = values.FederalEvn_id;
									saving_data.FederalServer_id=values.FederalServer_id;
								}else{
									saving_data.FederalEvn_id = -1;
									saving_data.FederalServer_id=21;
								}
								saving_data.Federal_Num=values.Federal_Num;*/
								// редактируем значение даты
								grid.getSelectionModel().select(grid.getStore().getCount() - 1, 7);
								grid.getView().focusCell(grid.getStore().getCount() - 1, 7);
								that.startEditData();
								return true;
							}
							else
							{
								var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
								loadMask.show();
								that.doSaveOnThePersonEvn(
									record.get('PersonEvn_id'),
									record.get('Server_id'),
									'Polis',
									saving_data,
									function(options, success, response) {
										if ( response.responseText.length > 0 ) {
											var result = Ext.util.JSON.decode(response.responseText);
											loadMask.hide();
											if ( result.success === true && result.Cancel_Error_Handle != undefined && result.Cancel_Error_Handle === true ) {
												if (result.Error_Code == 'checkFederalNumUnique') {
													Ext.Msg.alert(
														lang['oshibka'],
														result.Error_Msg,
														function() {
															if ( this.ignoreOnClose === true )
																this.onWinClose = function() {};
															return false;
														}
													);
												}
											} else {
												//record.set('PersonEvn_Value', values.Polis_PolisString);
												//record.commit();
												that.findById('PVW_PersonInfoFrame').load({ Person_id: that.personId, Server_id: that.serverId, callback: function() {
													that.findById('PVW_PersonInfoFrame').setPersonTitle();
													/*if(!values.FederalEvn_id){
														values.FederalEvn_id=-1;
														values.FederalServer_id=21;
													}
													this.doSaveOnThePersonEvn(
														values.FederalEvn_id,
														values.FederalServer_id,
														'Federal_Num',
														{Federal_Num:values.Federal_Num},
														function() {
															Ext.getCmp('PVW_PeriodicViewGrid').ViewActions.action_refresh.execute();
														}.createDelegate(this)
													);*/
													grid.getSelectionModel().select(cell[0], cell[1]);
												}.createDelegate(this) });
												if ( this.ignoreOnClose === true )
													this.onWinClose = function() {};
												this.hide();
											}
										}
										loadMask.hide();
										
									}.createDelegate(this)
								);
								
							}
						},
						fields: {Person_id: record.get('Person_id'),PersonEvn_id:record.get('PersonEvn_id'),Server_id:record.get('Server_id')},
						action: 'edit_with_load'
					};
					if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
					{
						var fedDate=null;
						grid.getStore().each(function(r){
							if(r.get('PersonEvnClass_id')==16){
								if(fedDate==null||fedDate<r.get('PersonEvn_insDT')){
									fedDate = r.get('PersonEvn_insDT');
									params.fields.Federal_Num = r.get('PersonEvn_Value');
									params.fields.FederalEvn_id = r.get('PersonEvn_id');
									params.fields.FederalServer_id = r.get('FederalServer_id');	
								}
							}
						});
						
						params.action = 'add';
					} else {
						params.readOnly = (
							(getRegionNick().inlist(['perm', 'ufa', 'buryatiya', 'vologda','adygeya'])) &&
							record.get('Server_pid') == 0 &&
							!isSuperAdmin() &&
							!isUserGroup('editorperiodics')
						);
					}
					getWnd('swPolisEditWindow').show(params);
					this.clearEditForm = false;
					return true;
				break;
				case 9:
					var params = {
						ignoreOnClose: true,
						onClose: function() {
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
								this.onCancelAdd();
							
						}.createDelegate(this),
						callback: function(values) {
							// сохраняем документ
							var saving_data = {
								DocumentType_id: values.DocumentType_id,
								Document_Ser: values.Document_Ser,
								Document_Num: values.Document_Num,
								OrgDep_id: values.OrgDep_id,
								Document_begDate: values.Document_begDate
							};
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
							{
								// добавляем, поэтому запоминаем сохраняемые данные
								record.set('PersonEvn_Value', values.Document_DocumentString);
								record.commit();
								this.addObjectData = saving_data;
								// редактируем значение даты
								grid.getSelectionModel().select(grid.getStore().getCount() - 1, 7);
								grid.getView().focusCell(grid.getStore().getCount() - 1, 7);
								this.startEditData();
								return true;
							}
							else
							{
								this.doSaveOnThePersonEvn(
									record.get('PersonEvn_id'),
									record.get('Server_id'),
									'Document',
									saving_data,
									function() {
										record.set('PersonEvn_Value', values.Document_DocumentString);
										record.commit();
										this.findById('PVW_PersonInfoFrame').load({ Person_id: this.personId, Server_id: this.serverId, callback: function() {
											this.findById('PVW_PersonInfoFrame').setPersonTitle();
											grid.getSelectionModel().select(cell[0], cell[1]);
										}.createDelegate(this) });
										
									}.createDelegate(this)
								);
							}
						}.createDelegate(this),
						fields: {Document_id: record.get('PersonEvnObject_id')},
						action: 'edit_with_load'
					};
					if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
					{
						params.action = 'add';
					}
					getWnd('swDocumentEditWindow').show(params);
					return true;
				break;
				case 10:
					var params = {
						ignoreOnClose: true,
						onClose: function() {
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
								this.onCancelAdd();
						}.createDelegate(this),
						callback: function(values) {
							// сохраняем адрес проживания
							var saving_data = {
								UAddress_Zip: values.Address_ZipEdit,
								UKLCountry_id: values.KLCountry_idEdit,
								UKLRGN_id: values.KLRgn_idEdit,
								UKLSubRGN_id: values.KLSubRGN_idEdit,
								UKLCity_id: values.KLCity_idEdit,
								UKLTown_id: values.KLTown_idEdit,
								UKLStreet_id: values.KLStreet_idEdit,
								UPersonSprTerrDop_id: values.PersonSprTerrDop_idEdit,
								UAddress_House: values.Address_HouseEdit,
								UAddress_Corpus: values.Address_CorpusEdit,
								UAddress_Flat: values.Address_FlatEdit,
								UAddress_Address: values.Address_AddressEdit/*,
								UAddress_begDate: Ext.util.Format.date(values.Address_begDateEdit, 'd.m.Y')*/
							};
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
							{
								// проверяем, пустое ли поле страна, если пустое, то удаляем
								if ( !(saving_data['UKLCountry_id'] > 0) && !(saving_data['UKLRGN_id'] > 0) )
								{
									//this.onCancelAdd();
									return false;
								}
								// добавляем, поэтому запоминаем сохраняемые данные
								record.set('PersonEvn_Value', values.Address_AddressEdit);
								/*if(values.Address_begDateEdit){
									record.set('PersonEvn_newDT',  values.Address_begDateEdit);
								}*/
								
								record.commit();
								this.addObjectData = saving_data;
								// редактируем значение даты
								var grid = this.findById('PVW_PeriodicViewGrid').getGrid();
								grid.getSelectionModel().select(grid.getStore().getCount() - 1, 7);
								grid.getView().focusCell(grid.getStore().getCount() - 1, 7);
								this.startEditData();
								return true;
							}
							else
							{
								// проверяем, пустое ли поле страна, если пустое, то удаляем
								if ( !(saving_data['UKLCountry_id'] > 0) && !(saving_data['UKLRGN_id'] > 0) )
								{
									var grid = this.findById('PVW_PeriodicViewGrid').getGrid();
									grid.getSelectionModel().select(cell[0], cell[1]);
									return false;
								}
								this.doSaveOnThePersonEvn(
									record.get('PersonEvn_id'),
									record.get('Server_id'),
									'UAddress',
									saving_data,
									function() {
										record.set('PersonEvn_Value', values.Address_AddressEdit);
										record.commit();
										this.findById('PVW_PersonInfoFrame').load({ Person_id: this.personId, Server_id: this.serverId, callback: function() {
											this.findById('PVW_PersonInfoFrame').setPersonTitle();
											grid.getSelectionModel().select(cell[0], cell[1]);
										}.createDelegate(this) });
										
									}.createDelegate(this)
								);
							}
						}.createDelegate(this),
						fields: {Address_id: record.get('PersonEvnObject_id'), showDate: true},
						action: 'edit_with_load'
					};
					if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
					{
						params.action = 'add';
					}
					getWnd('swAddressEditWindow').show(params);
					return true;
				break;
				case 11:
					var params = {
						ignoreOnClose: true,
						onClose: function() {
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
								this.onCancelAdd();
						}.createDelegate(this),
						callback: function(values) {
							// сохраняем адрес проживания
							var saving_data = {
								PAddress_Zip: values.Address_ZipEdit,
								PKLCountry_id: values.KLCountry_idEdit,
								PKLRGN_id: values.KLRgn_idEdit,
								PKLSubRGN_id: values.KLSubRGN_idEdit,
								PKLCity_id: values.KLCity_idEdit,
								PKLTown_id: values.KLTown_idEdit,
								PKLStreet_id: values.KLStreet_idEdit,
								PPersonSprTerrDop_id: values.PersonSprTerrDop_idEdit,
								PAddress_House: values.Address_HouseEdit,
								PAddress_Corpus: values.Address_CorpusEdit,
								PAddress_Flat: values.Address_FlatEdit,
								PAddress_Address: values.Address_AddressEdit/*,
								PAddress_begDate: Ext.util.Format.date(values.Address_begDateEdit, 'd.m.Y')*/
							};
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
							{
								// проверяем, пустое ли поле страна, если пустое, то удаляем
								if ( !(saving_data['PKLCountry_id'] > 0) && !(saving_data['PKLRGN_id'] > 0) )
								{
									//this.onCancelAdd();
									return false;
								}								
								// добавляем, поэтому запоминаем сохраняемые данные
								record.set('PersonEvn_Value', values.Address_AddressEdit);
								/*if(values.Address_begDateEdit){
									record.set('PersonEvn_newDT',  values.Address_begDateEdit);
								}*/
								record.commit();
								this.addObjectData = saving_data;
								// редактируем значение даты
								var grid = this.findById('PVW_PeriodicViewGrid').getGrid();
								grid.getSelectionModel().select(grid.getStore().getCount() - 1, 7);
								grid.getView().focusCell(grid.getStore().getCount() - 1, 7);
								this.startEditData();
								return true;
							}
							else
							{
								// проверяем, пустое ли поле страна, если пустое, то удаляем
								if ( !(saving_data['PKLCountry_id'] > 0) && !(saving_data['PKLRGN_id'] > 0) )
								{
									var grid = this.findById('PVW_PeriodicViewGrid').getGrid();
									grid.getSelectionModel().select(cell[0], cell[1]);
									return false;
								}
								this.doSaveOnThePersonEvn(
									record.get('PersonEvn_id'),
									record.get('Server_id'),
									'PAddress',
									saving_data,
									function() {
										record.set('PersonEvn_Value', values.Address_AddressEdit);
										record.commit();
										this.findById('PVW_PersonInfoFrame').load({ Person_id: this.personId, Server_id: this.serverId, callback: function() {
											this.findById('PVW_PersonInfoFrame').setPersonTitle();
											grid.getSelectionModel().select(cell[0], cell[1]);
										}.createDelegate(this) });
										
									}.createDelegate(this)
								);
							}
						}.createDelegate(this),
						fields: {Address_id: record.get('PersonEvnObject_id'), showDate: true},
						action: 'edit_with_load'
					};
					if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
					{
						params.action = 'add';
					}
					getWnd('swAddressEditWindow').show(params);
					return true;
				break;
				case 12:
					var params = {
						ignoreOnClose: true,
						onClose: function() {
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
								this.onCancelAdd();
						}.createDelegate(this),
						callback: function(values) {
							// сохраняем документ
							var saving_data = {
								Org_id: values.Org_id,
								OrgUnion_id: values.OrgUnion_id,
								OrgUnionNew: values.OrgUnionNew,
								Post_id: values.Post_id,
								PostNew: values.PostNew
							};
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
							{
								// добавляем, поэтому запоминаем сохраняемые данные
								record.set('PersonEvn_Value', values.Job_JobString);
								record.commit();
								this.addObjectData = saving_data;
								// редактируем значение даты
								grid.getSelectionModel().select(grid.getStore().getCount() - 1, 7);
								grid.getView().focusCell(grid.getStore().getCount() - 1, 7);
								this.startEditData();
								return true;
							}
							else
							{
								this.doSaveOnThePersonEvn(
									record.get('PersonEvn_id'),
									record.get('Server_id'),
									'Job',
									saving_data,
									function() {
										record.set('PersonEvn_Value', values.Job_JobString);
										record.commit();
										this.findById('PVW_PersonInfoFrame').load({ Person_id: this.personId, Server_id: this.serverId, callback: function() {
											this.findById('PVW_PersonInfoFrame').setPersonTitle();
											grid.getSelectionModel().select(cell[0], cell[1]);
										}.createDelegate(this) });
										
									}.createDelegate(this)
								);
							}
						}.createDelegate(this),
						fields: {Job_id: record.get('PersonEvnObject_id')},
						action: 'edit_with_load'
					};
					if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
					{
						params.action = 'add';
					}
					getWnd('swJobEditWindow').show(params);
					return true;
				break;
				case 16:
					grid.getColumnModel().setEditor( 
						6,
						new Ext.grid.GridEditor(this.getEdNumEditor(), {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);					
					this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, true);
					grid.startEditing(cell[0], cell[1]);
				break;
				case 18:
					grid.getColumnModel().setEditor( 
						6,
						new Ext.grid.GridEditor(this.getPhoneEditor(), {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);					
					this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, true);
					grid.startEditing(cell[0], cell[1]);
				break;
				case 20:
					grid.getColumnModel().setEditor( 
						6,
						new Ext.grid.GridEditor(this.getInnEditor(), {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);					
					this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, true);
					grid.startEditing(cell[0], cell[1]);
				break;
				case 21:
					grid.getColumnModel().setEditor( 
						6,
						new Ext.grid.GridEditor(this.getSocCardNumEditor(), {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);					
					this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, true);
					grid.startEditing(cell[0], cell[1]);
				break;
				case 22:
					grid.getColumnModel().setEditor( 
						6,
						new Ext.grid.GridEditor(this.getFamilyStatusEditor(), {listeners: {'canceledit': this.onCancelEdit.createDelegate(this)}})
					);					
					this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, true);
					grid.startEditing(cell[0], cell[1]);
				break;
				case 23:
					var params = {
						ignoreOnClose: true,
						onClose: function() {
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
								this.onCancelAdd();

						}.createDelegate(this),
						callback: function(values) {
							var saving_data = {
								KLCountry_id: values.KLCountry_id,
								NationalityStatus_IsTwoNation: values.NationalityStatus_IsTwoNation,
								LegalStatusVZN_id: values.LegalStatusVZN_id
							};
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
							{
								// добавляем, поэтому запоминаем сохраняемые данные
								record.set('PersonEvn_Value', values.NationalityStatus_String);
								record.commit();
								this.addObjectData = saving_data;
								// редактируем значение даты
								grid.getSelectionModel().select(grid.getStore().getCount() - 1, 7);
								grid.getView().focusCell(grid.getStore().getCount() - 1, 7);
								this.startEditData();
								return true;
							}
							else
							{
								this.doSaveOnThePersonEvn(
									record.get('PersonEvn_id'),
									record.get('Server_id'),
									'NationalityStatus',
									saving_data,
									function() {
										record.set('PersonEvn_Value', values.NationalityStatus_String);
										record.commit();
										this.findById('PVW_PersonInfoFrame').load({ Person_id: this.personId, Server_id: this.serverId, callback: function() {
											this.findById('PVW_PersonInfoFrame').setPersonTitle();
											grid.getSelectionModel().select(cell[0], cell[1]);
										}.createDelegate(this) });

									}.createDelegate(this)
								);
							}
						}.createDelegate(this),
						fields: {NationalityStatus_id: record.get('PersonEvnObject_id')},
						action: 'edit_with_load'
					};
					if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
					{
						params.action = 'add';
					}
					getWnd('swNationalityStatusEditWindow').show(params);
				break;
			}	
		}
	},
	startViewData: function() {
		// проверяем текущую ячейку
		var grid = this.findById('PVW_PeriodicViewGrid').getGrid();
		var cell = grid.getSelectionModel().getSelectedCell();		
		if ( !cell || cell.length == 0 || (cell[1] != 6 && cell[1] != 7) )
			return false;
		var record = grid.getSelectionModel().getSelected();
		if ( !record )
			return false;
		// редактируем сами данные о событии
		if ( cell[1] == 6 )
		{
			// в зависимости от класса события
			switch ( record.get('PersonEvnClass_id') ) {
				case 8:
					var params = {
						readOnly: true,
						clearEditForm: this.clearEditForm,
						ignoreOnClose: true,
						onClose: function() {
							if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
								this.onCancelAdd();
						}.createDelegate(this),
						fields: {Person_id: record.get('Person_id'),PersonEvn_id:record.get('PersonEvn_id'),Server_id:record.get('Server_id')},
						action: 'view'
					};
					if ( (record.get('PersonEvn_id') == null) || (record.get('PersonEvn_id') == '') )
					{
						params.action = 'add';
					}
					getWnd('swPolisEditWindow').show(params);
					this.clearEditForm = false;
					return true;
				break;
			}	
		}
	},
	extendPersonHistory: function() {
		var form = this;
		var Mask = new Ext.LoadMask(this.getEl(), { msg: "Перечитывается история..." });
		Mask.show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				Mask.hide();
				if (success) {
					Ext.getCmp('PVW_PeriodicViewGrid').ViewActions.action_refresh.execute();
				}
			}.createDelegate(this),
			params: {Person_id: form.personId},
			url: '/?c=Person&m=extendPersonHistory'
		});
	},
	initComponent: function() {
		Ext.apply(this, {
			buttons: [
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.returnFunc();
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}
			],
			items: [ new sw.Promed.PersonInfoPanel({
				button2Callback: function(callback_data)
				{
					var cw = Ext.getCmp('PeriodicViewWindow');
					cw.findById('PVW_PersonInfoFrame').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
				},
				button2OnHide: function()
				{					
				},
				button3OnHide: function()
				{					
				},
				id: 'PVW_PersonInfoFrame',
				additionalFields: [
					'Person_IsFedLgot'
				],
				title: lang['zagruzka'],
				collapsible: true,
				collapsed: true,
				plugins: [Ext.ux.PanelCollapsedTitle],
				floatable: false,
				border: true,
				titleCollapse: true,
				region: 'north'
			}),
			new Ext.Panel({
				region: 'center',
				layout: 'border',
				border: false,
				items: [					
		            new sw.Promed.ViewFrame(
					{
						// useArchive: 1, // убрал пока, неясно как вообще может быть работа с архивными записями в данной форме, если пользователь может вставить запись с InsDT < дата актуальности.
						actions:
						[
							{name: 'action_add', handler: function() {Ext.getCmp('PeriodicViewWindow').startAddData();}, disabled: false},
							{name: 'action_edit', handler: function() {
									Ext.getCmp('PeriodicViewWindow').startEditData();
								}, disabled: false
							},
							{name: 'action_view', handler: function() {
									Ext.getCmp('PeriodicViewWindow').startViewData();
								}, disabled: false
							},
							{name: 'action_delete', handler: function() {Ext.getCmp('PeriodicViewWindow').deletePeriodic()}, disabled: false},
							{name: 'action_refresh', disabled: false},
							{name: 'action_print'}
						],
						setFirstEditRecord: function() {
							if ( !this.findById('PVW_PeriodicViewGrid').getAction('action_edit').isDisabled() )
								this.findById('PVW_PeriodicViewGrid').getAction('action_edit').execute();
						}.createDelegate(this),
						onSelectionChange: function() {
							this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, false);
							this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(7, false);
						}.createDelegate(this),
						onCellSelect: function(sm,rowIdx,colIdx) {
							if (this.readOnly) {
								this.findById('PVW_PeriodicViewGrid').getAction('action_add').setDisabled(true);
								this.findById('PVW_PeriodicViewGrid').getAction('action_edit').setDisabled(true);
								this.findById('PVW_PeriodicViewGrid').getAction('action_delete').setDisabled(true);
								return;
							}

							this.findById('PVW_PeriodicViewGrid').getAction('action_edit').setDisabled(false);
							this.findById('PVW_PeriodicViewGrid').getAction('action_delete').setDisabled(false);
							
							var record = this.findById('PVW_PeriodicViewGrid').getGrid().getStore().getAt(rowIdx);
							// Удаление полиса из БДЗ ну нужно никому. в том числе и суперадмину. (refs #8441)
							if (
								(
									!getRegionNick().inlist([ 'astra', 'kareliya', 'ufa', 'ekb','pskov','buryatiya','vologda' ])
									//|| (getRegionNick() == 'astra' && isSuperAdmin() == false && isLpuAdmin() == false)
									|| (getRegionNick() == 'kareliya' && isSuperAdmin() == false && isLpuAdmin() == false)
									|| (getRegionNick().inlist(['ekb','pskov','buryatiya','vologda','adygeya'])&&!isUserGroup('editorperiodics')&&!isSuperAdmin())
								)
								&& (
									(record.get('PersonEvnClass_id') == 8 && record.get('Server_id') == 0)
									|| record.get('PersonEvn_readOnly') == 1
								)
							) {
								if (getRegionNick() == 'perm') {
									// можно закрывать полис, вне зависимости от человека
								} else {
									this.findById('PVW_PeriodicViewGrid').getAction('action_edit').setDisabled(!isSuperAdmin());
								}
								this.findById('PVW_PeriodicViewGrid').getAction('action_delete').setDisabled(true);
								this.findById('PVW_PeriodicViewGrid').getAction('action_view').setDisabled(false);
								return true;
							}
							
							// https://redmine.swan.perm.ru/issues/55366
							// https://redmine.swan.perm.ru/issues/57313
							if ( getRegionNick() == 'kareliya' && isSuperAdmin() == false ) {
								this.findById('PVW_PeriodicViewGrid').getAction('action_delete').setDisabled(true);
							}

							if ( isSuperAdmin()||(getRegionNick().inlist(['ekb','pskov','buryatiya','vologda','adygeya'])&&isUserGroup('editorperiodics')) )
								return true;

							// БДЗ
							if ( this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsBDZ') == 1 && ['1', '2', '3', '4', '5', '6', '7', '8', '16'].in_array(record.get('PersonEvnClass_id')) )
							{
								if ( getRegionNick() == 'kareliya' && isLpuAdmin()) {
									return true; // Амин МО на Карелии может изменять ключевые периодики БДЗ, при это человек выводится из БДЗ
								}
								// https://redmine.swan-it.ru/issues/199174#note-7
								if ( getRegionNick().inlist(['ufa', 'perm']) && record.get('PersonEvnClass_id') == 8) {
									return true; 
								}
								this.findById('PVW_PeriodicViewGrid').getAction('action_delete').setDisabled(true);
								//if( getRegionNick() == 'kareliya' && isLpuAdmin())
								this.findById('PVW_PeriodicViewGrid').getAction('action_edit').setDisabled(true);
								
								return true;
							}
							// федеральный льготник
							if ( this.findById('PVW_PersonInfoFrame').getFieldValue('Person_IsFedLgot') == 1 && ['1', '2', '3', '4', '5', '6', '16'].in_array(record.get('PersonEvnClass_id')) )
							{
								this.findById('PVW_PeriodicViewGrid').getAction('action_edit').setDisabled(true);
								this.findById('PVW_PeriodicViewGrid').getAction('action_delete').setDisabled(true);
								return true;
							}
							// вообще недоступны
							if ( ['21'].in_array(record.get('PersonEvnClass_id')) )
							{
								this.findById('PVW_PeriodicViewGrid').getAction('action_edit').setDisabled(true);
								this.findById('PVW_PeriodicViewGrid').getAction('action_delete').setDisabled(true);
							}
							return true;
						}.createDelegate(this),
						autoLoadData: false,
						border: false,
						selectionModel: 'cell',
						autoexpand: 'expand',
						dataUrl: '?c=Person&m=getAllPeriodics',
						id: 'PVW_PeriodicViewGrid',
						region: 'center',
						onAfterEditSelf: function(o) {
							return this.onAfterEdit(o);
						}.createDelegate(this),
						onValidateEditSelf: function(o) {
							return this.onValidateEdit(o);
						}.createDelegate(this),
						stringfields:
						[
							
							{name: 'PersonEvnClass_id', type: 'int', hidden: true},
							{name: 'Server_id', type: 'int', hidden: true},
							{ name: 'check', header: ' ',type:'checkcolumnedit',width: 40},
							{name: 'Person_id', type: 'int', hidden: !isSuperAdmin(), header: 'Person_id'},
							{name: 'PersonEvnObject_id', type: 'int', hidden: true},
							{name: 'PersonEvnClass_Name',  type: 'string', header: lang['naimenovanie'], width: 250},
							{name: 'PersonEvn_Value',  editor: new Ext.form.TextField(), type: 'string', header: lang['znachenie'], id: 'autoexpand'},
							{name: 'PersonEvn_insDT',  type: 'datetimesec', header: lang['data_nachala'], width: 140},
							{name: 'PersonEvnClass_begDT',  type: 'date', hidden: true},
							{name: 'PersonEvn_readOnly',  type: 'int', hidden: true},
							{name: 'PersonEvn_id', type: 'int', header: 'ID', key: true},
							{name: 'Server_pid', type: 'int', hidden: true}
						],
						toolbar: true
					})
				]
			})]
		});
		sw.Promed.swPeriodicViewWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 600,
	modal: true,
	personId: null,
	plain: true,
	resizable: true,
	returnFunc: Ext.emptyFn,
	serverId: 0,
	clearEditForm: false,
	show: function() {
		sw.Promed.swPeriodicViewWindow.superclass.show.apply(this, arguments);

		this.onHide = Ext.emptyFn;
		this.readOnly = false;

		if (arguments[0])
		{
			if (arguments[0].callback)
			{
				this.returnFunc = arguments[0].callback;
			}

			if (arguments[0].onHide)
			{
				this.onHide = arguments[0].onHide;
			}

			if (arguments[0].Person_id)
			{
				this.personId = arguments[0].Person_id;
			}
			
			if (arguments[0].Server_id)
			{
				this.serverId = arguments[0].Server_id;
			}
			this.findById('PVW_PersonInfoFrame').setTitle('...');
		}				
		
		var form = this;

		Ext.getCmp('PVW_PeriodicViewGrid').addActions({
			iconCls: 'actions16',
			name:'action_person_history_extend',
			hidden: !isSuperAdmin(),
			text:lang['perechitat_istoriyu'],
			handler: function()
			{
				this.extendPersonHistory();
			}.createDelegate(this)
		});

		this.findById('PVW_PersonInfoFrame').load(
		{
			Person_id: this.personId,
			Server_id: this.serverId,
			callback: function()
			{
				form.findById('PVW_PersonInfoFrame').setPersonTitle();
			}
		});
		
		this.findById('PVW_PeriodicViewGrid').getGrid().getStore().removeAll();
		
		this.findById('PVW_PeriodicViewGrid').getGrid().getStore().load({
			params: {
				Person_id: this.personId,
				Server_id: this.serverId
			},
			callback: function() {
				if ( this.findById('PVW_PeriodicViewGrid').getGrid().getStore().getCount() > 0 )
					this.findById('PVW_PeriodicViewGrid').getGrid().getSelectionModel().selectRow(0);
			}.createDelegate(this)
		});

		if ( this.socStatusStore.getCount() == 0 ) {
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Загрузка справочника социальных статусов..." });
			loadMask.show();

			this.socStatusStore.load({
				callback: function() {
					loadMask.hide();
				}.createDelegate(this)
			});
		}

		// по умолчанию у нас запрещено редактирование
		this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(6, false);
		this.findById('PVW_PeriodicViewGrid').getGrid().getColumnModel().setEditable(7, false);
		
		if (getRegionNick() == 'adygeya' && !(isSuperAdmin() || isUserGroup('editorperiodics'))) {
			this.readOnly = true;
		}
		
		this.findById('PVW_PeriodicViewGrid').setReadOnly(this.readOnly);
		
		this.restore();
		this.center();
		this.maximize();
	},
	socStatusStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'SocStatus_id', mapping: 'SocStatus_id' },
			{ name: 'SocStatus_Code', mapping: 'SocStatus_Code' },
			{ name: 'SocStatus_SysNick', mapping: 'SocStatus_SysNick' },
			{ name: 'SocStatus_Name', mapping: 'SocStatus_Name' },
			{ name: 'SocStatus_begDT', mapping: 'SocStatus_begDT', type: 'date', dateFormat: 'd.m.Y' },
			{ name: 'SocStatus_endDT', mapping: 'SocStatus_endDT', type: 'date', dateFormat: 'd.m.Y' }
		],
		key: 'SocStatus_id',
		sortInfo: { field: 'SocStatus_Code' },
		tableName: 'SocStatus'
	}),
	title: lang['periodiki'],
	width: 600
});