/**
* swCmpCallCardShortEditWindow - окно редактирования карты вызова (краткий вариант для операторов СМП)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author		Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      апрель.2012
*/
/*NO PARSE JSON*/

sw.Promed.swCmpCallCardShortEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swCmpCallCardShortEditWindow',
	objectSrc: '/jscore/Forms/Ambulance/swCmpCallCardShortEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,

	save_form: function( base_form, params_out){		
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение карты вызова..."});
		loadMask.show();		
		log(base_form.findField('KLStreet_id').getValue())
		base_form.findField('LpuTransmit_id').setValue(base_form.findField('Lpu_ppdid').getValue());
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
			params: params_out,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.CmpCallCard_id > 0 ) {
						base_form.findField('CmpCallCard_id').setValue(action.result.CmpCallCard_id);
						var data = new Object();
						var index;
						var person_fio = '';
						var record;

						var CmpReason_id = base_form.findField('CmpReason_id').getValue();
						var CmpCallType_id = base_form.findField('CmpCallType_id').getValue();

						var CmpReason_Name = '';
						var CmpCallType_Name = '';

						index = base_form.findField('CmpReason_id').getStore().findBy(function(rec) {
							if ( rec.get('CmpReason_id') == CmpReason_id ) {
								return true;
							}
							else {
								return false;
							}
						});
						record = base_form.findField('CmpReason_id').getStore().getAt(index);

						if ( record ) {
							CmpReason_Name = record.get('CmpReason_Name');
						}

						if ( base_form.findField('Person_Surname').getValue() ) {
							person_fio = person_fio + base_form.findField('Person_Surname').getValue();
						}
						if ( base_form.findField('Person_Firname').getValue() ) {
							person_fio = person_fio + ' ' + base_form.findField('Person_Firname').getValue();
						}
						if ( base_form.findField('Person_Secname').getValue() ) {
							person_fio = person_fio + ' ' + base_form.findField('Person_Secname').getValue();
						}
						data.cmpCallCardData = {
							'accessType': 'edit'
							,'CmpCallCard_id': base_form.findField('CmpCallCard_id').getValue()
							,'CmpCallCard_prmDate': base_form.findField('CmpCallCard_prmDate').getValue()
							,'Person_id': base_form.findField('Person_id').getValue()
							,'Person_Surname': base_form.findField('Person_Surname').getValue()
							,'Person_Firname': base_form.findField('Person_Firname').getValue()
							,'Person_Secname': base_form.findField('Person_Secname').getValue()
							,'Person_Birthday': (typeof base_form.findField('Person_Birthday').getValue() == 'object' ? base_form.findField('Person_Birthday').getValue() : getValidDT(base_form.findField('Person_Birthday').getValue(), ''))
							,'Person_FIO': person_fio
							,'CmpReason_Name': CmpReason_Name
						};

						this.callback(data);
						this.hide();
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

	checkDuplicate: function(data, parent_object, check_dupl_params) {
		var base_form = this.FormPanel.getForm();
		Ext.Ajax.request({
			params: check_dupl_params,
			callback: function(opt, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					//log(response_obj);
					if ( response_obj.data && response_obj.data.length > 0) {
						var ConfirmDublicateWin = new Ext.Window({
							width:980,
							heigth:600,
							title:lang['vozmojno_dublirovanie_vyizova'],
							modal: false,
							draggable:false,
							resizable:false,
							closable : false,
							items:[{
									xtype: 'grid',
									columns: [
										{dataIndex: 'CmpCallCard_prmDate', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), header: lang['data_vremya'], width: 110},
										{dataIndex: 'CmpCallCard_Ngod', header: lang['№_vyizova_za_god'], width: 100},
										{dataIndex: 'Person_FIO', header: lang['patsient'], width: 180},
										{dataIndex: 'CmpCallType_Name', header: lang['tip_vyizova'], width: 120},
										{dataIndex: 'CmpReason_Name', header: lang['povod'], width: 200},
										{dataIndex: 'Adress_Name', header: lang['mesto_vyizova'], width: 250}
									],
									store:new Ext.data.GroupingStore({
										data: response_obj,
										fields: [{name: 'CmpCallCard_prmDate'},{name: 'CmpCallCard_Ngod'},{name: 'Person_FIO'},{name: 'CmpCallType_Name'},{name: 'CmpReason_Name'},{name: 'Adress_Name'}],
										reader: new Ext.data.JsonReader({
												root: 'data'
											},
											Ext.data.Record.create([
												{name: 'CmpCallCard_prmDate'},
												{name: 'CmpCallCard_Ngod'},
												{name: 'Person_FIO'},
												{name: 'CmpCallType_Name'},
												{name: 'CmpReason_Name'},
												{name: 'Adress_Name'}
											])
										)
									}),
									height: 350,
									view: new Ext.grid.GridView({
										forceFit: false
									}),
									listeners: {}
							}],
							buttons:[{
								text:lang['prodoljit_sohranenie'],
								id:'save',
								handler:function(){
									ConfirmDublicateWin.close();
									parent_object.save_form(base_form, data) ;
								}
							},
							{
								text: lang['otmenit_sohranenie'],
								handler: function(){
									this.formStatus='edit';
									ConfirmDublicateWin.close();
									parent_object.hide();
								}
							}]
						})
						ConfirmDublicateWin.show();
					}
					else {
						parent_object.save_form(base_form, data) ;
					}
				}
				else {
					this.formStatus='edit';
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_proverke_dublirovaniya_vyizova']);
					parent_object.hide();
				}
			}.createDelegate(this),
			url: '/?c=CmpCallCard&m=checkDuplicateCmpCallCard'
		});
		return false;
	},
	
	setLpuPanel: function(lpuAttach, noAddressNmpMsg) {
		var lpupanel = this.FormPanel.find('name', 'lpu_panel')[0],
			lpufield = lpupanel.find('name', 'lpu_field')[0],
			msgfield = lpupanel.find('name', 'msg_field')[0];
		if (!lpuAttach) {
			lpupanel.hide();
			return true;
		}
		if( !lpupanel.isVisible() ) {
				lpupanel.setVisible(true);
			}
		if(noAddressNmpMsg){ msgfield.getEl().update('<div style="margin-left: 5px; width: 250px; height: 32px;">Нет НМП обслуживающих данный адрес</div>'); }
		else{msgfield.getEl().update('');}
		
		if(lpuAttach.length)
		{
			lpufield.getEl().update('<div style="margin-left: 5px; width: 250px; height: 32px;">Прикреплён к '+lpuAttach+'</div>');	
		}
	},
	
	selectLpuTransmit: function(CmpLpuId) {
		//if (!CmpLpuId || this.FormPanel.getForm().findField('Lpu_ppdid').getStore().find('Lpu_ppdid', CmpLpuId, 0, false)== -1) {
			//this.FormPanel.getForm().findField('Lpu_ppdid').setValue('');
		//	return true;
		//	}
		//alert ("!!!");

		var baseForm = this.FormPanel.getForm(),
			cmpFieldReasonCode = baseForm.findField('CmpReason_id'),
			store = cmpFieldReasonCode.getStore(),
			flagOrArrayCodesIsNMP = false,
			value = cmpFieldReasonCode.getValue(),
			idx = store.findBy(function(rec) { return rec.get('CmpReason_id') == parseInt(value); }),
			age = this.FormPanel.getForm().findField('Person_Age').getValue();


		if(idx != -1 && idx != undefined)
		{
			if(("ufa" == getGlobalOptions().region.nick || "krym" == getGlobalOptions().region.nick)
				&& value && age)
			{
				Ext.Ajax.request({
					url:'/?c=CmpCallCard4E&m=getCallUrgencyAndProfile',
					autoAbort : true,
					callback: function(opt, success, response){
						if ( success && response.responseText != null)
						{
							var response_obj = Ext.decode(response.responseText),
								type_service_reason = response_obj.CmpCallCardAcceptor_SysNick;
							if (response_obj.Error_Msg)
							{
								Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
								return false;
							}
							flagOrArrayCodesIsNMP = ("nmp" == type_service_reason);
							this.setEnabledCombo(flagOrArrayCodesIsNMP);
						}

					}.bind(this),
					params:{
						CmpReason_id:value,
						Person_Age:age,
						FlagArmWithoutPlaceType: true
					}
				});
			}
			else
			{
				var	code = store.getAt(idx).get('CmpReason_Code');
				flagOrArrayCodesIsNMP = code.inlist(['04Г','04Д','09Я','11Л','11Я','12Г','12К','12Р','12У','12Э','12Я','13Л','13М','13Х','15Н','16И','17А','13С','40Ц']);
				this.setEnabledCombo(flagOrArrayCodesIsNMP);
			}
		}
		else
		{
			this.setEnabledCombo(flagOrArrayCodesIsNMP);
		}
		// this.FormPanel.getForm().findField('CmpReason_id').getValue().inlist([541, 542, 595, 606, 609, 613, 616, 618, 619, 620, 621, 629, 630, 644, 634, 648, 806, 632, 689])
		//this.FormPanel.getForm().findField('Lpu_ppdid').setValue(CmpLpuId);
	},
	setEnabledCombo: function(flagOrArrayCodesIsNMP){
		var baseForm = this.FormPanel.getForm(),
			comboLpuTransmit = baseForm.findField('Lpu_ppdid'),
			CmpCallerType_id = baseForm.findField("CmpCallerType_id");
		if	(
			flagOrArrayCodesIsNMP
				&& (baseForm.findField('Person_Age').getValue()>0)
				&& (baseForm.findField('Person_id').getValue() !=0 || getGlobalOptions().region.nick == 'ufa')
			) {
			comboLpuTransmit.setDisabled(false);
			comboLpuTransmit.focus(true);
			(function(){
				CmpCallerType_id.removeClass('x-form-focus');
				comboLpuTransmit.focus(true);
			}).defer(650);
			this.setLpuAddrLoad();
		}
		else {
			comboLpuTransmit.setDisabled(true);
			comboLpuTransmit.setValue('');
			return false;
		}
	},
	setLpuAddrLoad: function(){
		
		var base_form = this.FormPanel.getForm();
		
		if ((this.action) == 'view')
		{
			return false;
		}
		
		//проверяем - формализованный ли адрес

		if   (base_form.findField('KLStreet_id').getValue() != '')
		{
			//если у нас что-то выбрано - ведем поиск по лпу, ищем по адресу вызова
			if ( 
				(base_form.findField('KLAreaStat_idEdit').getValue() != '' ) ||				
				(base_form.findField('KLSubRgn_id').getValue() != '' )	||
				(base_form.findField('KLCity_id').getValue() != '' )	||
				(base_form.findField('KLTown_id').getValue() != '' )	||
				(base_form.findField('CmpCallCard_Dom').getValue() != '' )
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
						KLSubRgn_id: base_form.findField('KLSubRgn_id').getValue(),
						KLCity_id: base_form.findField('KLCity_id').getValue(),
						KLTown_id: base_form.findField('KLTown_id').getValue(),
						KLStreet_id: base_form.findField('KLStreet_id').getValue(),
						CmpCallCard_Dom: base_form.findField('CmpCallCard_Dom').getValue(),
						Person_Age: base_form.findField('Person_Age').getValue()
				   } ,
				   callback :function(){
					   
					comboLpuTrnsimit = base_form.findField('Lpu_ppdid');
					   
					if (comboLpuTrnsimit.getStore().getCount() == 1)
					  {
						var recordSelected = comboLpuTrnsimit.getStore().getAt(0);                     
						comboLpuTrnsimit.setValue(recordSelected.get('Lpu_id'));
					  }
					  
					if (comboLpuTrnsimit.getStore().getCount() == 0)
					{
						this.setLpuPanel(true, true);
					}
					
					if (comboLpuTrnsimit.getStore().getCount() > 0)
					{
						this.setLpuPanel(true, false);
					}
				  
					var record = new Ext.data.Record({
						Lpu_id: '0',
						Lpu_Name: '',
						Lpu_Nick: lang['pokazat_vse']
					   });
					comboLpuTrnsimit.getStore().add([record]);
					//comboLpuTrnsimit.getStore().insert(0, [emptyRecord]);
					comboLpuTrnsimit.getStore().commitChanges();	
				   }.createDelegate(this)
			 });
			 }	 
			 //иначе по старинке - все
			}
			else
			{
				//alert ('OldAddress'); 
				//comAction: 'OldAddress';
				this.FormPanel.getForm().findField('Lpu_ppdid').setValue('');
				base_form.findField('Lpu_ppdid').getStore().removeAll();
				base_form.findField('Lpu_ppdid').getStore().load({params: {Object: 'LpuWithMedServ', ComAction: 'AllAddress', MedServiceType_id: 18}});				
			}
	},
	
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();
		
//		console.log(base_form.findField('CmpCallCard_TabN').getValue());
//		console.log('@@@@@@');
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();
		var check_dupl_params = new Object();

		if ( typeof base_form.findField('Person_Birthday').getValue() == 'object' ) {
			params.Person_Birthday = Ext.util.Format.date(base_form.findField('Person_Birthday').getValue(), 'd.m.Y');
		}
		else if ( typeof base_form.findField('Person_Birthday').getValue() == 'string' ) {
			params.Person_Birthday = base_form.findField('Person_Birthday').getValue();
		}
		if (base_form.findField('CmpCallCard_Numv').disabled) { 
			params.CmpCallCard_Numv = base_form.findField('CmpCallCard_Numv').getValue();
		}
		if (base_form.findField('CmpCallCard_Ngod').disabled) { 
			params.CmpCallCard_Ngod = base_form.findField('CmpCallCard_Ngod').getValue();
		}
		if ( base_form.findField('CmpCallCard_prmDate').disabled ) {
			params.CmpCallCard_prmDate = Ext.util.Format.date(base_form.findField('CmpCallCard_prmDate').getValue(), 'd.m.Y');
		}
		if ( base_form.findField('CmpCallCard_prmTime').disabled ) {
			params.CmpCallCard_prmTime =base_form.findField('CmpCallCard_prmTime').getValue();
		}		
		if ( base_form.findField('Person_Age').disabled ) {
			params.Person_Age = base_form.findField('Person_Age').getValue();
		}
		if ( base_form.findField('Person_Firname').disabled ) {
			params.Person_Firname = base_form.findField('Person_Firname').getValue();
		}
		if ( base_form.findField('Person_Secname').disabled ) {
			params.Person_Secname = base_form.findField('Person_Secname').getValue();
		}
		if ( base_form.findField('Person_Surname').disabled ) {
			params.Person_Surname = base_form.findField('Person_Surname').getValue();
		}
		
		if (Ext.isEmpty(base_form.findField('Person_Firname').getValue()) && Ext.isEmpty(base_form.findField('Person_Surname').getValue()) && Ext.isEmpty(base_form.findField('Person_Secname').getValue())) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('Person_Surname').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['hotya_byi_odno_iz_poley_familiya_imya_otchestvo_doljno_byit_zapolneno'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( base_form.findField('Sex_id').disabled ) {
			params.Sex_id = base_form.findField('Sex_id').getValue();
		}
		
		if ( base_form.findField('Polis_Ser').disabled ) {
			params.Polis_Ser = base_form.findField('Polis_Ser').getValue();
		}
		
		if ( base_form.findField('Polis_Num').disabled ) {
			params.Polis_Num = base_form.findField('Polis_Num').getValue();
		}
		
		if ( base_form.findField('Polis_EdNum').disabled ) {
			params.Polis_EdNum = base_form.findField('Polis_EdNum').getValue();
		}
		check_dupl_params.CmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();
		check_dupl_params.CmpCallCard_prmDate = Ext.util.Format.date(base_form.findField('CmpCallCard_prmDate').getValue(), 'd.m.Y');
		check_dupl_params.CmpCallCard_prmTime = base_form.findField('CmpCallCard_prmTime').getValue();
		//Пациент
		check_dupl_params.Person_Surname = base_form.findField('Person_Surname').getValue();
		check_dupl_params.Person_Firname = base_form.findField('Person_Firname').getValue();
		check_dupl_params.Person_Secname = base_form.findField('Person_Secname').getValue();
		check_dupl_params.Person_Birthday = params.Person_Birthday;
		check_dupl_params.Person_Age = base_form.findField('Person_Age').getValue();
		check_dupl_params.Sex_id = base_form.findField('Sex_id').getValue();
		check_dupl_params.Person_PolisSer = base_form.findField('Polis_Ser').getValue();
		check_dupl_params.Person_PolisNum = base_form.findField('Polis_Num').getValue();
		//Место 
		check_dupl_params.KLSubRgn_id = base_form.findField('KLSubRgn_id').getValue();
		check_dupl_params.KLCity_id = base_form.findField('KLCity_id').getValue();
		check_dupl_params.KLTown_id = base_form.findField('KLTown_id').getValue();
		check_dupl_params.KLStreet_id = base_form.findField('KLStreet_id').getValue();
		check_dupl_params.CmpCallCard_Dom = base_form.findField('CmpCallCard_Dom').getValue();
		check_dupl_params.CmpCallCard_Kvar = base_form.findField('CmpCallCard_Kvar').getValue();
		check_dupl_params.CmpCallCard_Podz = base_form.findField('CmpCallCard_Podz').getValue();
		check_dupl_params.CmpCallCard_Etaj = base_form.findField('CmpCallCard_Etaj').getValue();
		//Вызов
		//check_dupl_params.CmpCallType_id = base_form.findField('CmpCallType_id').getValue();
		check_dupl_params.CmpReason_id = base_form.findField('CmpReason_id').getValue();
		check_dupl_params.CmpCallCard_Comm = base_form.findField('CmpCallCard_Comm').getValue();
		this.checkDuplicate(params, this, check_dupl_params);
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			//'CmpCallCard_City',
			'CmpCallCard_Comm',
			'CmpCallCard_Dom',
			'CmpCallCard_Korp',
			'CmpCallCard_Room',
			'CmpCallCard_Etaj',
			'CmpCallCard_Kodp',
			'CmpCallerType_id',
			'CmpCallCard_Kvar',
//			'CmpCallCard_Ngod',
//			'CmpCallCard_Numv',
			'CmpCallCard_Podz',
			'CmpCallCard_Telf',
			//'CmpCallCard_Ulic',
			'CmpReason_id',
			'CmpCallType_id',
			'Person_Age',
			'Person_Firname',
			'Person_Secname',
			'Person_Surname',
			'Person_Birthday',
			'Sex_id',
			'Polis_Ser',
			'Polis_Num',
			'Polis_EdNum',
			'KLAreaStat_idEdit',
			'KLSubRgn_id',
			'KLCity_id',
			'KLTown_id',
			'Lpu_ppdid',
			'StreetAndUnformalizedAddressDirectory_id'
		);
		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			// this.findById('CCCSEF_PersonIdentBtn').show();
			this.findById('CCCSEF_PersonResetBtn').show();
			this.findById('CCCSEF_PersonSearchBtn').show();

			this.buttons[0].show();
		}
		else {
			// this.findById('CCCSEF_PersonIdentBtn').hide();
			this.findById('CCCSEF_PersonResetBtn').hide();
			this.findById('CCCSEF_PersonSearchBtn').hide();

			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	getCmpCallCardNumber: function() {
		var base_form = this.FormPanel.getForm();

		this.getLoadMask().show();

		Ext.Ajax.request({
			callback: function(opt, success, response) {
				this.getLoadMask().hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					base_form.findField('CmpCallCard_Ngod').setValue(response_obj[0].CmpCallCard_Ngod);
					base_form.findField('CmpCallCard_Numv').setValue(response_obj[0].CmpCallCard_Numv);

					base_form.findField('CmpCallCard_Numv').focus(true);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_vyizova'], function() {base_form.findField('CmpCallCard_Numv').focus(true);}.createDelegate(this) );
				}
			}.createDelegate(this),
			url: '/?c=CmpCallCard&m=getCmpCallCardNumber'
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite']});
		}

		return this.loadMask;
	},
	height: 550,
	id: 'CmpCallCardShortEditWindow',
	
	reloadAllFields: function(data) {
		var frm = this.FormPanel.getForm(),
			rc = null;
		frm.findField('KLAreaStat_idEdit').getStore().each(function(r) {
			if( r.get('KLSubRGN_id') > 0 ) {
				if( data.KLSubRegion_id > 0 && data.KLSubRegion_id == r.get('KLSubRGN_id') ) {
					rc = r;
				}
			}
			else if( r.get('KLCity_id') > 0 ) {
				if( data.KLCity_id > 0 && data.KLCity_id == r.get('KLCity_id') ) {
					rc = r;
				}
			}
		});
		if( rc != null ) {
			frm.findField('KLAreaStat_idEdit').setValue(rc.get('KLAreaStat_id'));
			frm.findField('KLAreaStat_idEdit').fireEvent('beforeselect', frm.findField('KLAreaStat_idEdit'), rc);
		}
		frm.findField('KLTown_id').getStore().load({
			params: {city_id: data.KLSubRegion_id > 0 ? data.KLSubRegion_id : data.KLCity_id},
			callback: function() {
				this.each(function(r) {
					if( data.KLTown_id && data.KLTown_id == r.get('Town_id') ) {
						frm.findField('KLTown_id').setValue(r.get('Town_id'));
						frm.findField('KLTown_id').fireEvent('beforeselect', frm.findField('KLTown_id'), r);
					}
				});
			}
		});
	},
	
	setStatusIdentification: function(e) {
		var statuspanel = this.FormPanel.find('name', 'status_panel')[0],
			statusfield = statuspanel.find('name', 'status_field')[0],
			bf = this.FormPanel.getForm(),
			parentObject = this;
		
		if(
			(
				( bf.findField('Person_Surname').getValue() != '' && bf.findField('Person_Firname').getValue() != '' && bf.findField('Person_Secname').getValue() != '' )
				&& ( bf.findField('Person_Birthday').getValue() != '' || bf.findField('Person_Age').getValue() != '' )
			)
			||
			(
				bf.findField('Polis_Ser').getValue() != '' && bf.findField('Polis_Num').getValue() != ''
			)
			||
			bf.findField('Polis_EdNum').getValue() != ''
		) {
			if( !statuspanel.isVisible() ) {
				statuspanel.setVisible(true);
			}
			var src = '/extjs/resources/images/default/grid/loading.gif';
			statusfield.getEl().update('<div style="margin-left: 50px; width: 200px; height: 16px; background-image: url('+src+'); background-repeat: no-repeat">Идентификация пациента...</div>');
			Ext.Ajax.request({
				//url: '/?c=CmpCallCard&m=identifiPerson',
				url: '/?c=Person&m=getPersonSearchGrid',
				params: {
					/*
					Person_Surname: bf.findField('Person_Surname').getValue()
					,Person_Firname: bf.findField('Person_Firname').getValue()
					,Person_Secname: bf.findField('Person_Secname').getValue()
					,Person_Birthday: bf.findField('Person_Birthday').getValue() != '' ? bf.findField('Person_Birthday').getValue().format('d.m.Y') : null
					,Person_Age: bf.findField('Person_Age').getValue()
					,Polis_Ser: bf.findField('Polis_Ser').getValue()
					,Polis_Num: bf.findField('Polis_Num').getValue()
					,Sex_id: bf.findField('Sex_id').getValue()
					*/
					PersonFirName_FirName: bf.findField('Person_Firname').getValue()
					,PersonSecName_SecName: bf.findField('Person_Secname').getValue()
					,PersonSurName_SurName: bf.findField('Person_Surname').getValue()
					,PersonAge_AgeFrom: bf.findField('Person_Age').getValue()
					,PersonAge_AgeTo: bf.findField('Person_Age').getValue()
					,PersonBirthDay_BirthDay: bf.findField('Person_Birthday').getValue() != '' ? bf.findField('Person_Birthday').getValue().format('d.m.Y') : null
					,Polis_Num: bf.findField('Polis_Num').getValue()
					,Polis_Ser: bf.findField('Polis_Ser').getValue()
					,limit: 100
					,searchMode: 'all'
					,start: 0
					,Sex_id: bf.findField('Sex_id').getValue()
					,Polis_EdNum: bf.findField('Polis_EdNum').getValue()
				},
				callback: function(o, s, r) {
					if( s ) {
						var resp = Ext.util.JSON.decode(r.responseText),
							msg = '';
						if( resp.Error_Msg ) {
							statusfield.getEl().update(resp.Error_Msg);
							return false;
						} else {
							if( resp.totalCount == 1 ) {
								msg += lang['patsient_identifitsirovan'];
								var data = resp.data[0];
								data['Person_Firname'] = data['PersonFirName_FirName'];
								data['Person_Secname'] = data['PersonSecName_SecName'];
								data['Person_Surname'] = data['PersonSurName_SurName'];
								data['Person_Birthday'] = data['PersonBirthDay_BirthDay'];
								parentObject.selectLpuTransmit(data['CmpLpu_id']);
								parentObject.setLpuPanel(data['Lpu_Nick']);
								bf.setValues(data);
							} else if( resp.totalCount > 1 ) {
								msg += lang['naydeno'] + resp.totalCount + lang['patsientov_najmite_knopku_poisk_dlya_identifikatsii_patsienta'];
								parentObject.selectLpuTransmit();
								parentObject.setLpuPanel();
							} else {
								msg += lang['patsient_ne_nayden'];
								parentObject.selectLpuTransmit();
								parentObject.setLpuPanel();
							}
							statusfield.getEl().update(msg);
						}
					}
				}.createDelegate(this)
			});
		} else {
			this.setLpuPanel();
			statusfield.getEl().update('');
			statuspanel.setVisible(false);
		}
	},
	
	initComponent: function() {
		var parentObject = this;
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			id: 'CmpCallCardEditForm',
			labelAlign: 'right',
			labelWidth: 220,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{name: 'accessType'},
				{name: 'CmpCallCard_id'},
				{name: 'CmpArea_gid'},
				{name: 'CmpArea_id'},
				{name: 'CmpArea_pid'},
				{name: 'CmpCallCard_City'},
				{name: 'CmpCallCard_Comm'},
				{name: 'CmpCallCard_D201'},
				{name: 'CmpCallCard_Dlit'},
				{name: 'CmpCallCard_Dokt'},
				{name: 'CmpCallCard_Dom'},
				{name: 'CmpCallCard_Korp'},
				{name: 'CmpCallCard_Room'},
				{name: 'CmpCallCard_Dsp1'},
				{name: 'CmpCallCard_Dsp2'},
				{name: 'CmpCallCard_Dsp3'},
				{name: 'CmpCallCard_Dspp'},
				{name: 'CmpCallCard_Etaj'},
				{name: 'CmpCallCard_Expo'},
				{name: 'CmpCallCard_IsAlco'},
				{name: 'CmpCallCard_IsPoli'},
				{name: 'CmpCallCard_Izv1'},
				{name: 'CmpCallCard_Kakp'},
				{name: 'CmpCallCard_Kilo'},
				{name: 'CmpCallCard_Kodp'},
				{name: 'CmpCallerType_id'},
				{name: 'CmpCallCard_Kvar'},
				{name: 'CmpCallCard_Line'},
				{name: 'CmpCallCard_Ncar'},
				{name: 'CmpCallCard_Ngod'},
				{name: 'CmpCallCard_Numb'},
				{name: 'CmpCallCard_Numv'},
				{name: 'CmpCallCard_PCity'},
				{name: 'CmpCallCard_PDom'},
				{name: 'CmpCallCard_PKvar'},
				{name: 'CmpCallCard_Podz'},
				{name: 'CmpCallCard_Prdl'},
				{name: 'CmpCallCard_prmDate'},
				{name: 'CmpCallCard_prmTime'},
				{name: 'CmpCallCard_Prty'},
				{name: 'CmpCallCard_Przd'},
				{name: 'CmpCallCard_PUlic'},
				{name: 'CmpCallCard_RCod'},
				{name: 'CmpCallCard_Sect'},
				{name: 'CmpCallCard_Smpb'},
				{name: 'CmpCallCard_Smpp'},
				{name: 'CmpCallCard_Smpt'},
				{name: 'CmpCallCard_Stan'},
				{name: 'CmpCallCard_Stbb'},
				{name: 'CmpCallCard_Stbr'},
				{name: 'CmpCallCard_Tab2'},
				{name: 'CmpCallCard_Tab3'},
				{name: 'CmpCallCard_Tab4'},
				{name: 'CmpCallCard_TabN'},
				{name: 'CmpCallCard_Telf'},
				{name: 'CmpCallCard_Tgsp'},
				{name: 'CmpCallCard_Tisp'},
				{name: 'CmpCallCard_Tiz1'},
				{name: 'CmpCallCard_Tper'},
				{name: 'CmpCallCard_Tsta'},
				{name: 'CmpCallCard_Tvzv'},
				{name: 'CmpCallCard_Ulic'},
				{name: 'CmpCallCard_Vr51'},
				{name: 'CmpCallCard_Vyez'},
				{name: 'CmpCallType_id'},
				{name: 'CmpDiag_aid'},
				{name: 'CmpDiag_oid'},
				{name: 'CmpLpu_id'},
				{name: 'CmpPlace_id'},
				{name: 'CmpProfile_bid'},
				{name: 'CmpProfile_cid'},
				{name: 'CmpReason_id'},
				{name: 'CmpResult_id'},
				{name: 'ResultDeseaseType_id'},
				{name: 'CmpTalon_id'},
				{name: 'CmpTrauma_id'},
				{name: 'Diag_sid'},
				{name: 'Diag_uid'},
				{name: 'Lpu_oid'},
				{name: 'Person_Age'},
				{name: 'Person_Birthday'},
				{name: 'Person_Firname'},
				{name: 'Person_id'},
				{name: 'Polis_Ser'},
				{name: 'Polis_Num'},
				{name: 'Polis_EdNum'},
				{name: 'Person_Secname'},
				{name: 'Person_Surname'},
				{name: 'Sex_id'},
				{name: 'Lpu_id'}, 
				{name: 'Lpu_ppdid'},
				{name: 'ARMType'},
				{name: 'KLRgn_id'},
				{name: 'KLSubRgn_id'},
				{name: 'KLCity_id'},
				{name: 'KLTown_id'},
				{name: 'KLStreet_id'},
				{name: 'UnformalizedAddressDirectory_id'},
				{name: 'StreetAndUnformalizedAddressDirectory_id'},
				
				{name: 'CmpCallCard_Inf1'},
				{name: 'CmpCallCard_Inf2'},
				{name: 'CmpCallCard_Inf3'},
				{name: 'CmpCallCard_Inf4'},
				{name: 'CmpCallCard_Inf5'},
				{name: 'CmpCallCard_Inf6'}
				
			]),
			region: 'center',
			url: '/?c=CmpCallCard&m=saveCmpCallCard',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpArea_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_PCity',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_PUlic',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_PDom',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_PKvar',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpArea_gid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpLpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpDiag_oid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpDiag_aid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpTrauma_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_IsAlco',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Diag_uid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Diag_sid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpTalon_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Expo',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_IsPoli',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Smpt',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Line',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Prty',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Sect',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpProfile_cid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Stan',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpResult_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'ResultDeseaseType_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpPlace_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Smpp',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Vr51',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_D201',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Dsp1',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Dsp2',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Dspp',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Dsp3',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Dlit',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Prdl',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Tiz1',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Izv1',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Numb',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Smpb',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Stbr',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Stbb',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpProfile_bid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Ncar',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_RCod',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Dokt',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Tab2',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Tab3',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Tab4',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_TabN',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Kakp',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Tper',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Vyez',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Przd',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Tgsp',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Tsta',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Tisp',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'LpuTransmit_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'CmpCallCard_Tvzv',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Kilo',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'ARMType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'KLStreet_id',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'UnformalizedAddressDirectory_id',
				value: '',
				xtype: 'hidden'
			}, /*{
				disabled: true,
				name: 'Person_Birthday',
				value: '',
				xtype: 'hidden'
			}, */{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						disabled: true,
						fieldLabel: lang['data_vyizova'],
						format: 'd.m.Y',
						name: 'CmpCallCard_prmDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						// tabIndex: TABINDEX_EVPLEF + 1,
						width: 100,
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();

								base_form.findField('CmpCallType_id').setFilterByDate(newValue);
							}.createDelegate(this)
						},
						xtype: 'swdatefield'
					}, {
						autoCreate: {tag: "input", type: "text", size: "20", autocomplete: "off",  maxlength: '12'},
						disabledClass: 'field-disabled',
						fieldLabel: lang['№_vyizova_za_den'],
						maskRe: /\d/,
						maxLength: 12,
						name: 'CmpCallCard_Numv',
						allowBlank: false,
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield',
						disabled: true
					}]
				}, {
					border: false,
					labelWidth: 120,
					layout: 'form',
					items: [{
						disabled: true,
						fieldLabel: lang['vremya'],
						allowBlank: false,
						name: 'CmpCallCard_prmTime',
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_EVPLEF + 2,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						autoCreate: {tag: "input", type: "text", size: "20", autocomplete: "off",  maxlength: '12'},
						disabledClass: 'field-disabled',
						fieldLabel: lang['№_vyizova_za_god'],
						maskRe: /\d/,
						maxLength: 12,
						name: 'CmpCallCard_Ngod',
						allowBlank: false,
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield',
						disabled: true
					}]
				}]
			}, {
				autoHeight: true,
				style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
				title: lang['patsient'],
				xtype: 'fieldset',

				items: [{
					border: false,
					layout: 'column',
					style: 'padding: 0px;',
					width: 800,
					items: [{
						border: false,
						layout: 'form',
						style: 'padding: 0px',
						items: [{
							disabledClass: 'field-disabled',
							fieldLabel: lang['familiya'],
							listeners: {
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this),
								change: this.setStatusIdentification.createDelegate(this)
							},
							name: 'Person_Surname',
							// tabIndex: TABINDEX_PEF + 1,
							toUpperCase: true,
							width: 180,
							xtype: 'textfieldpmw'
						}, {
							disabledClass: 'field-disabled',
							fieldLabel: lang['imya'],
							name: 'Person_Firname',
							listeners: {
								change: this.setStatusIdentification.createDelegate(this)
							},
							// tabIndex: TABINDEX_PEF + 2,
							toUpperCase: true,
							width: 180,
							xtype: 'textfieldpmw'
						}, {
							disabledClass: 'field-disabled',
							fieldLabel: lang['otchestvo'],
							listeners: {
								change: this.setStatusIdentification.createDelegate(this)
							},
							name: 'Person_Secname',
							// tabIndex: TABINDEX_PEF + 3,
							toUpperCase: true,
							width: 180,
							xtype: 'textfieldpmw'
						}, {
							name: 'Person_Birthday',
							maxValue: (new Date()),
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							listeners: {
								//change: this.setStatusIdentification.createDelegate(this)
								change: function(f, nV, oV) {
									if( f.isValid() ) {
										this.FormPanel.getForm().findField('Person_Age').setValue(swGetPersonAge(f.getValue(), new Date()));
										this.setStatusIdentification();
									} else {
										this.FormPanel.getForm().findField('Person_Age').reset();
									}
								}.createDelegate(this)
							},
							fieldLabel: lang['data_rojdeniya'],
							xtype: 'swdatefield'
						}, {
							allowDecimals: false,
							allowNegative: false,
							disabledClass: 'field-disabled',
							fieldLabel: lang['vozrast'],
							listeners: {
								change: this.setStatusIdentification.createDelegate(this)
							},
							name: 'Person_Age',
							// tabIndex: TABINDEX_PEF + 4,
							toUpperCase: true,
							width: 180,
							xtype: 'numberfield'
						}, {
							comboSubject: 'Sex',
							disabledClass: 'field-disabled',							
							fieldLabel: lang['pol'],
							style: 'border: 1px solid #ff0000',							
							listeners: {
								change: this.setStatusIdentification.createDelegate(this)
							},
							hiddenName: 'Sex_id',
							// tabIndex: TABINDEX_PEF + 5,
							width: 130,
							xtype: 'swcommonsprcombo'
						}, {
							xtype: 'textfield',
							width: 180,
							listeners: {
								change: this.setStatusIdentification.createDelegate(this)
							},
							name: 'Polis_Ser',
							fieldLabel: lang['seriya_polisa']
						}, {
							xtype: 'numberfield',
							width: 180,
							listeners: {
								change: this.setStatusIdentification.createDelegate(this)
							},
							name: 'Polis_Num',
							fieldLabel: lang['nomer_polisa']
						}, {
							xtype: 'numberfield',
							width: 180,
							listeners: {
								change: this.setStatusIdentification.createDelegate(this)
							},
							name: 'Polis_EdNum',
							fieldLabel: lang['edinyiy_nomer']
						}]
					}, {
						border: false,
						layout: 'form',
						style: 'padding-left: 10px;',
						items: [{
							handler: function() {
								this.personSearch();
							}.createDelegate(this),
							iconCls: 'search16',
							id: 'CCCSEF_PersonSearchBtn',
							text: lang['poisk'],
							xtype: 'button'
						},/* {
							handler: function() {
								this.personIdent();
							}.createDelegate(this),
							iconCls: 'admin16',
							id: 'CCCSEF_PersonIdentBtn',
							text: lang['identifikatsiya_po_bd'],
							xtype: 'button'
						}, */{
							handler: function() {
								this.personReset();
							}.createDelegate(this),
							iconCls: 'reset16',
							id: 'CCCSEF_PersonResetBtn',
							text: lang['sbros'],
							xtype: 'button'
						}]
					}, {
						border: false,
						width: 300,
						layout: 'form',
						items: [
							{
								xtype: 'panel',
								frame: true,
								border: false,
								hidden: true,
								name: 'status_panel',
								style: 'margin-left: 5px; margin-bottom: 5px;',
								bodyStyle: 'padding: 3px;',
								items: [{
									html: '',
									style: 'text-align: center;',
									name: 'status_field'
								}]
							}
						]
					}]
				}]
			}, {
				autoHeight: true,
				style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
				title: lang['mesto_vyizova'],
				xtype: 'fieldset',
				items: [{
					enableKeyEvents: true,
					hiddenName: 'KLAreaStat_idEdit',
					listeners: {
						beforeselect: function(combo, record) {
							if ( typeof record === 'undefined' ) {
								return;
							}
							
							if( record.get('KLAreaStat_id') == '' ) {
								combo.onClearValue();
								return;
							}
							
							var base_form = this.FormPanel.getForm();
							base_form.findField('KLSubRgn_id').reset();
							base_form.findField('KLCity_id').reset();
							base_form.findField('KLTown_id').reset();
							base_form.findField('StreetAndUnformalizedAddressDirectory_id').reset();
							
							if( record.get('KLSubRGN_id') != '' ) {
								base_form.findField('KLSubRgn_id').setValue(record.get('KLSubRGN_id'));
								base_form.findField('KLSubRgn_id').getStore().removeAll();
								base_form.findField('KLSubRgn_id').getStore().load({
									params: {region_id: record.get('KLRGN_id')},
									callback: function() {
										this.setValue(this.getValue());
										this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) { return rec.get('SubRGN_id') == this.getValue(); }.createDelegate(this))));
									}.createDelegate(base_form.findField('KLSubRgn_id'))
								});
							} else if( record.get('KLCity_id') != '' ) {
								base_form.findField('KLCity_id').setValue(record.get('KLCity_id'));
								base_form.findField('KLCity_id').getStore().removeAll();
								base_form.findField('KLCity_id').getStore().load({
									params: {subregion_id: record.get('KLRGN_id')},
									callback: function() {
										this.setValue(this.getValue());
										this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) { return rec.get('City_id') == this.getValue(); }.createDelegate(this))));
									}.createDelegate(base_form.findField('KLCity_id'))
								});
							}
						}.createDelegate(this)
					},
					onClearValue: function() {
						var base_form = this.FormPanel.getForm();
						base_form.findField('KLAreaStat_idEdit').clearValue();
						base_form.findField('KLSubRgn_id').enable();
						base_form.findField('KLCity_id').enable();
						base_form.findField('KLTown_id').enable();
						base_form.findField('KLTown_id').reset();
						base_form.findField('KLTown_id').getStore().removeAll();
						base_form.findField('StreetAndUnformalizedAddressDirectory_id').enable();
						base_form.findField('StreetAndUnformalizedAddressDirectory_id').reset();
						base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().removeAll();
					}.createDelegate(this),
					width: 180,
					xtype: 'swklareastatcombo'
				}, {
					hiddenName: 'KLSubRgn_id',
					listeners: {
						beforeselect: function(combo, record) {
							combo.setValue(record.get(combo.valueField));
							var base_form = this.FormPanel.getForm();
							if( record.get('SubRGN_id') > 0 ) {
								base_form.findField('KLCity_id').reset();
								//base_form.findField('KLCity_id').disable();
								
								base_form.findField('KLAreaStat_idEdit').getStore().each(function(r) {
									if( r.get('KLSubRGN_id') > 0 ) {
										if( record.get('SubRGN_id') == r.get('KLSubRGN_id') ) {
											base_form.findField('KLAreaStat_idEdit').setValue(r.get('KLAreaStat_id'));
										}
									}
								});
								base_form.findField('KLTown_id').getStore().removeAll();
								base_form.findField('KLTown_id').getStore().load({params: {city_id: record.get('SubRGN_id')}});								
								base_form.findField('KLCity_id').getStore().removeAll();
								base_form.findField('KLCity_id').getStore().load({params: {subregion_id: record.get('SubRGN_id')}});
								base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().removeAll();
								base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({params: {town_id: record.get('SubRGN_id'), showSocr: 1}});
							} else {
								base_form.findField('KLCity_id').enable();
							}
						}.createDelegate(this)
					},
					minChars: 0,
					onClearValue: function() {
						var base_form = this.FormPanel.getForm();

						base_form.findField('KLCity_id').clearValue();
						base_form.findField('KLTown_id').clearValue();
						base_form.findField('StreetAndUnformalizedAddressDirectory_id').clearValue();
						var PID = 0;

						base_form.findField('KLCity_id').getStore().removeAll();
						base_form.findField('KLCity_id').getStore().load({params: {subregion_id: PID}});

						base_form.findField('KLTown_id').getStore().removeAll();
						base_form.findField('KLTown_id').getStore().load({params: {city_id: PID}});

						base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().removeAll();
						base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({params: {town_id: PID, showSocr: 1}});
					}.createDelegate(this),
					/*onTrigger2Click: function() {
						if ( this.disabled ) return;

						this.clearValue();
						this.onClearValue();
					},*/
					width: 180,
					xtype: 'swsubrgncombo'
				}, {
					hiddenName: 'KLCity_id',
					listeners: {
						beforeselect: function(combo, record) {
							combo.setValue(record.get(combo.valueField));
							var base_form = this.FormPanel.getForm();
							if( record.get('City_id') > 0 ) {
								base_form.findField('KLSubRgn_id').reset();
								//base_form.findField('KLSubRgn_id').disable();
								
								base_form.findField('KLAreaStat_idEdit').getStore().each(function(r) {
									if( r.get('KLCity_id') > 0 ) {
										if( record.get('City_id') == r.get('KLCity_id') ) {
											base_form.findField('KLAreaStat_idEdit').setValue(r.get('KLAreaStat_id'));
										}
									}
								});
								base_form.findField('KLTown_id').clearValue();
								base_form.findField('KLTown_id').getStore().removeAll();
								base_form.findField('KLTown_id').getStore().load({params: {city_id: record.get('City_id')}});
								base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().removeAll();
								base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({params: {town_id: record.get('City_id'), showSocr: 1}});
							} else {
								base_form.findField('KLSubRgn_id').enable();
							}
						}.createDelegate(this)
					},
					minChars: 0,
					onClearValue: function() {
						var base_form = this.FormPanel.getForm();
						base_form.findField('PersonSprTerrDop_idEdit').clearValue();

						var PID = 0;

						if ( base_form.findField('KLSubRgn_idEdit').getValue() ) {
							PID = base_form.findField('KLSubRgn_idEdit').getValue();
						}
						else if ( base_form.findField('KLRgn_idEdit').getValue() ) {
							PID = base_form.findField('KLRgn_idEdit').getValue();
						}


						this.refreshFullAddress();
					}.createDelegate(this),
					onTrigger2Click: function() {
						if ( this.disabled ) return;
						
						this.clearValue();
						this.onClearValue();
					},
					width: 180,
					xtype: 'swcitycombo'
				}, {
					enableKeyEvents: true,
					hiddenName: 'KLTown_id',
					listeners: {
						beforeselect: function(combo, record) {
							combo.setValue(record.get(combo.valueField));							
							var base_form = this.FormPanel.getForm();
							//base_form.findField('KLCity_id').clearValue();
							base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().removeAll();
							base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({
								params: {town_id: combo.getValue(), showSocr: 1}
							});
						}.createDelegate(this),				
						keydown: function (inp, e) {							
							if ( e.shiftKey == false && e.getKey() == Ext.EventObject.F4 ) {
								e.stopEvent();
								inp.onTrigger2Click();
							}							
							
						}
					},
					minChars: 0,
					onClearValue: function() {
						var base_form = this.FormPanel.getForm();
						base_form.findField('StreetAndUnformalizedAddressDirectory_id').clearValue();
						var PID = 0;

						if ( base_form.findField('KLCity_id').getValue()  ) {
							PID = base_form.findField('KLCity_id').getValue();
						}
						else if ( base_form.findField('KLSubRgn_id').getValue() ) {
							PID = base_form.findField('KLSubRgn_id').getValue();
						}
						
						base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().removeAll();
						base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({
							params: {town_id: PID, showSocr: 1}
						});
					}.createDelegate(this),
					onTrigger2Click: function() {
						var base_form = this.FormPanel.getForm(),
							klcity_id = 0,
							klcity_name = '',
							klsubrgn_id = 0,
							klsubrgn_name = '';

						if ( base_form.findField('KLCity_id').getValue() ) {
							klcity_id = base_form.findField('KLCity_id').getValue();
							klcity_name = base_form.findField('KLCity_id').getRawValue();
						}

						if ( base_form.findField('KLSubRgn_id').getValue() ) {
							klsubrgn_id = base_form.findField('KLSubRgn_id').getValue();
							klsubrgn_name = base_form.findField('KLSubRgn_id').getRawValue();
						}
						getWnd('swKLTownSearchWindow').show({
							onSelect: function(response_data) {
								base_form.findField('KLAreaStat_idEdit').onClearValue();
								this.reloadAllFields(response_data);
							}.createDelegate(this),
							params: {
								KLCity_id: klcity_id,
								KLSubRegion_id: klsubrgn_id,
								KLCity_Name: klcity_name,
								KLSubRegion_Name: klsubrgn_name
							}
						});
					}.createDelegate(this),
					width: 250,
					xtype: 'swtowncombo'
				},{
					xtype: 'swstreetandunformalizedaddresscombo',
					fieldLabel: lang['ulitsa_mesto'],
					hiddenName: 'StreetAndUnformalizedAddressDirectory_id',
					listeners: {
						beforeselect: function(combo, record) {
							combo.setValue(record.get(combo.valueField));
							var base_form = this.FormPanel.getForm();
							base_form.findField('UnformalizedAddressDirectory_id').setValue(record.get('UnformalizedAddressDirectory_id'));
							base_form.findField('KLStreet_id').setValue(record.get('KLStreet_id'));
							this.selectLpuTransmit();	
						}.createDelegate(this)										
					},
					width: 250,
					editable: true	
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['dom'],
					name: 'CmpCallCard_Dom',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					toUpperCase: true,
						listeners: {
							blur: function() {
								this.selectLpuTransmit();
							}.createDelegate(this)
						},
					xtype: 'textfield'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['korpus'],
					name: 'CmpCallCard_Korp',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					toUpperCase: true,						
					xtype: 'textfield'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['kvartira'],
					maxLength: 5,
					autoCreate: {tag: "input", size:14, maxLength: "5", autocomplete: "off"},
					//maskRe: /^([а-яА-Я0-9]{1,5})$/,
					name: 'CmpCallCard_Kvar',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'textfieldpmw'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['komnata'],
					name: 'CmpCallCard_Room',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					toUpperCase: true,						
					xtype: 'textfield'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['podyezd'],
					name: 'CmpCallCard_Podz',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'textfield'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['etaj'],
					name: 'CmpCallCard_Etaj',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'textfield'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['kod_zamka_v_podyezde_domofon'],
					name: 'CmpCallCard_Kodp',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'textfield'
				}]
			}, {
				autoHeight: true,
				style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
				title: lang['vyizov'],
				xtype: 'fieldset',

				items: [{
					border: false,
					layout: 'column',
					style: 'padding: 0px;',
					width: 1000,
					items: [{
						border: false,
						layout: 'form',
						style: 'padding: 0px',
						items: [
						{
							disabledClass: 'field-disabled',
							fieldLabel: lang['tip_vyizova'],
							listeners: {
								change: this.setStatusIdentification.createDelegate(this)
							},
							hiddenName: 'CmpCallType_id',
							displayField: 'CmpCallType_Name',
							width: 300,
							xtype: 'swcmpcalltypecombo'
						}, 
						{
							disabledClass: 'field-disabled',
							fieldLabel: lang['povod'],
							allowBlank: false,
							hiddenName: 'CmpReason_id',
							width: 250,
							store: new Ext.db.AdapterStore({
								dbFile: 'Promed.db',
								fields: [
									{name: 'CmpReason_id', mapping: 'CmpReason_id'},
									{name: 'CmpReason_Code', mapping: 'CmpReason_Code'},
									{name: 'CmpReason_Name', mapping: 'CmpReason_Name'}
								],
								autoLoad: true,
								key: 'CmpReason_id',
								sortInfo: {field: 'CmpReason_Code'},
								tableName: 'CmpReason'
							}),
							mode: 'local',
							triggerAction: 'all',
							listeners: {
								//render: function() { this.getStore().load(); },
								keydown: function(inp, e) {
									if ( e.getKey() == 40) {//down arrow
										if ((this.selectedIndex > this.store.getCount()-1)||(this.store.getCount()==1)) {
											this.select(0);	
										}
									}
									if (e.getKey() == 9) {//tab
										e.preventDefault();
										var CmpCallerType_id = Ext.getCmp("CmpCallerType_id"),
											lpu_ppdid =  Ext.getCmp("lpu_ppdid");
										if (this.getStore().getCount() == 1) {
											this.select(0);
										}

										/*e.preventDefault();
										e.stopPropagation();*/
										parentObject.selectLpuTransmit();
										/*
										var baseform = parentObject.FormPanel.getForm();
										var CmpCallerType_id = baseform.findField('CmpCallerType_id')
										function(){
											if(lpu_ppdid.disabled)
												CmpCallerType_id.focus(true);
										}).defer(600);*/
									}
								},
								select: function(c, r, i) {
									this.setValue(r.get('CmpReason_id'));
									this.setRawValue(r.get('CmpReason_Code')+'.'+r.get('CmpReason_Name'));
									parentObject.selectLpuTransmit();
								},
								blur: function() {
									this.collapse();
									if ( this.getRawValue() == '' ) {
										this.setValue('');
										if ( this.onChange && typeof this.onChange == 'function' ) {
											this.onChange(this, '');
										}
									} else {
										var store = this.getStore(),
											val = this.getRawValue().toString().substr(0, 5);
										val = LetterChange(val);
										if ( val.charAt(3) != '.' && val.length > 3 ) {
											val = val.slice(0,3) + '.' + val.slice(3, 4);
										}
										val = val.replace(' ', '');

										var yes = false;
										store.each(function(r){
											if ( r.get('CmpReason_Code') == val ) {
												this.setValue(r.get(this.valueField));
												this.fireEvent('select', this, r, 0);

												this.fireEvent('change', this, r.get(this.valueField), '');

												if ( this.onChange && typeof this.onChange == 'function') {
													this.onChange(this, r.get(this.valueField));
												}
												yes = true;
												return true;
											}
										}.createDelegate(this));
										/*if (!yes) {
											this.setValue(null);
											this.fireEvent('change', this, null, '');
											if ( this.onChange && typeof this.onChange == 'function') {
												this.onChange(this, null);
											}
										}*/
									}
								}										
							},
							doQuery: function(q) {
								var c = this;
								this.getStore().load({
									callback: function() {
										this.filter('CmpReason_Code', q);
										this.loadData(getStoreRecords(this));
										if( this.getCount() == 0 ) {
											c.setRawValue(q.slice(0, q.length-1));
											c.doQuery(c.getRawValue());
										}
										c[ c.expanded ? 'collapse' : 'expand' ]();
									}
								});
							},
							onTriggerClick: function() {
								this.focus();		
								if( this.isExpanded() ) {
									this.collapse();
								}
								if( this.getStore().getCount() == 0) {											
									this.doQuery(this.getRawValue());											
								}
								if(this.getValue() > 0) {										
									this[ this.isExpanded() ? 'collapse' : 'expand' ]();
								} else {											
									this.doQuery(this.getRawValue());
								}
							},
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{CmpReason_Code}</font>.{CmpReason_Name}',
								'</div></tpl>'
							),
							valueField: 'CmpReason_id',
							codeField: 'CmpReason_Code',
							displayField: 'CmpReason_Name',
							xtype: 'swbaselocalcombo'
						}, {
								valueField: 'Lpu_id',
								id: 'lpu_ppdid',
								allowBlank: false,
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
									/*keydown: function(inp, e) {
										if (e.getKey() == 9) {//tab
											alert('tab');
											var CmpCallerType_id = Ext.getCmp("CmpCallerType_id");
											CmpCallerType_id.focus(true);
										}
									},*/
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
												KLSubRgn_id: base_form.findField('KLSubRgn_id').getValue(),
												KLCity_id: base_form.findField('KLCity_id').getValue(),
												KLTown_id: base_form.findField('KLTown_id').getValue(),
												KLStreet_id: base_form.findField('KLStreet_id').getValue(),
												CmpCallCard_Dom: base_form.findField('CmpCallCard_Dom').getValue(),
												Person_Age: base_form.findField('Person_Age').getValue()
											} 
										  });
										  return false;
										}							
									}.createDelegate(this)								
								},	
								xtype: 'swlpuwithmedservicecombo'
					},{
						xtype: 'swcommonsprcombo',
						//id: 'CmpCallerType_id',
						fieldLabel: lang['kto_vyizyivaet'],
						comboSubject: 'CmpCallerType',
						hiddenName: 'CmpCallerType_id',
						displayField: 'CmpCallerType_Name',
						disabledClass: 'field-disabled',
						width: 350
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['telefon'],
						name: 'CmpCallCard_Telf',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['dopolnitelnaya_informatsiya'],
						height: 100,
						name: 'CmpCallCard_Comm',
						// tabIndex: TABINDEX_PEF + 5,
						width: 350,
						xtype: 'textarea'
					}]},  
					{
						border: false,
						width: 300,
						height: 200,
						layout: 'form',
						items: [
							{
								xtype: 'panel',
								frame: true,
								border: false,
								hidden: true,
								name: 'lpu_panel',
								style: 'margin-left: 5px; margin-bottom: 5px; margin-top:45px;',
								bodyStyle: 'padding: 3px;',
								items: [{
									html: '',
									style: 'text-align: center;',
									name: 'lpu_field'
								},
								{
									html: '',
									style: 'text-align: center;',
									name: 'msg_field'
								}
								]
							}]
					}]
				}]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function() {
					if ( !this.FormPanel.getForm().findField('CmpCallCard_Comm').disabled ) {
						this.FormPanel.getForm().findField('CmpCallCard_Comm').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				// tabIndex: TABINDEX_CCCEF + 15,
				text: BTN_FRMSAVE
			}, {
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
				// tabIndex: TABINDEX_CCCEF + 16,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swCmpCallCardShortEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('CmpCallCardShortEditWindow');

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
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			win.onCancelAction();
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.doLayout();
		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	maximizable: true,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onCancelAction: Ext.emptyFn,
	onHide: Ext.emptyFn,
	personIdent: function() {
		if ( this.action == 'view' ) {
			return false;
		}
	},
	personReset: function() {
		if ( this.action == 'view' ) {
			return false;
		}
		
		var base_form = this.FormPanel.getForm(),
			fields = [
				'Person_Surname'
				,'Person_Firname'
				,'Person_Secname'
				,'Person_Birthday'
				,'Person_Age'
				,'Sex_id'
				,'Polis_Ser'
				,'Polis_Num'
				,'Polis_EdNum'
			];

		base_form.findField('Person_id').setValue(0);
		for(var i=0; i<fields.length; i++) {
			base_form.findField(fields[i]).enable();
			base_form.findField(fields[i]).reset();
		}
		this.setStatusIdentification();
		this.selectLpuTransmit();
	},
	personSearch: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		var base_form = this.FormPanel.getForm(),
			parentObject = this;

		getWnd('swPersonSearchWindow').show({
			onClose: Ext.emptyFn,
			onSelect: function(person_data) {
				//log(person_data);
				with(base_form) {
					findField('Person_id').setValue(person_data.Person_id);

					findField('Person_Age').disable();
					findField('Person_Firname').disable();
					findField('Person_Secname').disable();
					findField('Person_Surname').disable();
					findField('Sex_id').disable();
					findField('Person_Birthday').disable();
					findField('Polis_Ser').disable();
					findField('Polis_Num').disable();
					findField('Polis_EdNum').disable();

					findField('Person_Age').setValue(swGetPersonAge(person_data.Person_Birthday, new Date()));				
					findField('Person_Birthday').setValue( (person_data.Person_Birthday).format('d.m.Y') );				
					findField('Person_Firname').setValue(person_data.Person_Firname != 'null' ? person_data.Person_Firname : '');
					findField('Person_Secname').setValue(person_data.Person_Secname != 'null' ? person_data.Person_Secname : '');
					findField('Person_Surname').setValue(person_data.Person_Surname != 'null' ? person_data.Person_Surname : '');
					findField('Sex_id').clearValue();
					findField('Sex_id').setValue(person_data.Sex_id);
					findField('Polis_Ser').setValue(person_data.Polis_Ser != null ? person_data.Polis_Ser : '');
					findField('Polis_Num').setValue(person_data.Polis_Num != null ? person_data.Polis_Num : '');
					findField('Polis_EdNum').setValue(person_data.Polis_EdNum != null ? person_data.Polis_EdNum : '');
					findField('CmpLpu_id').setValue(person_data.CmpLpu_id != null ? person_data.CmpLpu_id : '');
					parentObject.selectLpuTransmit(person_data.CmpLpu_id);
					parentObject.setLpuPanel(person_data.Lpu_Nick);
				}
				getWnd('swPersonSearchWindow').hide();
			},
			personFirname: base_form.findField('Person_Firname').getValue(),
			personSecname: base_form.findField('Person_Secname').getValue(),
			personSurname: base_form.findField('Person_Surname').getValue(),
			Person_Age: base_form.findField('Person_Age').getValue(),
			PersonBirthDay_BirthDay: (function(f) {return f.getValue() != '' ? f.getValue().format('d.m.Y') : '';})(base_form.findField('Person_Birthday')),
			Polis_Ser: base_form.findField('Polis_Ser').getValue(),
			Polis_Num: base_form.findField('Polis_Num').getValue(),
			Polis_EdNum: base_form.findField('Polis_EdNum').getValue(),
			searchMode: 'all'
		});
		if(
			base_form.findField('Person_Firname').getValue() != ''
			|| base_form.findField('Person_Secname').getValue() != ''
			|| base_form.findField('Person_Surname').getValue() != ''
		)
			getWnd('swPersonSearchWindow').doSearch();
	},
	plain: true,
	resizable: false,
	show: function() {			
		sw.Promed.swCmpCallCardShortEditWindow.superclass.show.apply(this, arguments);
		this.doLayout();
		this.restore();
		this.center();
		this.maximize();
		var base_form = this.FormPanel.getForm();		
		base_form.reset();
		this.setLpuPanel();
		this.action = null;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
			
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		
		var statuspanel = this.FormPanel.find('name', 'status_panel')[0],
			statusfield = statuspanel.find('name', 'status_field')[0];
			statusfield.getEl().update('');
			statuspanel.setVisible(false);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		switch ( this.action ) {
			case 'add':				
				this.setTitle(WND_AMB_CCCEFADD);
				this.enableEdit(true);
				loadMask.hide();

				setCurrentDateTime({
					dateField: base_form.findField('CmpCallCard_prmDate'),
					loadMask: true,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: true,
					timeField: base_form.findField('CmpCallCard_prmTime'),
					windowId: this.id,
					callback: function(date) {
						base_form.findField('CmpCallType_id').setFilterByDate(date);
					}
				});

				base_form.clearInvalid();
				base_form.findField('ARMType').setValue(arguments[0].formParams.ARMType);
				base_form.findField('KLAreaStat_idEdit').setValue(43); // г.Пермь

				var idx = base_form.findField('KLAreaStat_idEdit').getStore().findBy(function(rec) { return rec.get('KLAreaStat_id') == base_form.findField('KLAreaStat_idEdit').getValue(); }),
					record = base_form.findField('KLAreaStat_idEdit').getStore().getAt(idx);
				if( record ) {
					base_form.findField('KLAreaStat_idEdit').fireEvent('beforeselect', base_form.findField('KLAreaStat_idEdit'), record);
				} else {
					base_form.findField('KLAreaStat_idEdit').getStore().load({
						callback: function() {
							base_form.findField('KLAreaStat_idEdit').setValue(base_form.findField('KLAreaStat_idEdit').getValue());
							var idx = this.findBy(function(rec) { return rec.get('KLAreaStat_id') == base_form.findField('KLAreaStat_idEdit').getValue(); });
							base_form.findField('KLAreaStat_idEdit').fireEvent('beforeselect', base_form.findField('KLAreaStat_idEdit'), this.getAt(idx));
						}
					});
				}
//ставим неактивным выбор лпу				
				base_form.findField('Lpu_ppdid').disable();
				
				this.getCmpCallCardNumber();
			break;

			case 'edit':
			case 'view':
				var cmp_call_card_id = base_form.findField('CmpCallCard_id').getValue();

				if ( !cmp_call_card_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						CmpCallCard_id: cmp_call_card_id
					},
					success: function() {
						loadMask.hide();
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_AMB_CCCEFEDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_AMB_CCCEFVIEW);
							this.enableEdit(false);
						}

						var index;
						var person_id = base_form.findField('Person_id').getValue();
						var record;

						if ( this.action == 'edit' && Number(person_id) > 0 ) {
							base_form.findField('Person_Age').disable();
							base_form.findField('Person_Birthday').disable();
							base_form.findField('Person_Firname').disable();
							base_form.findField('Person_Secname').disable();
							base_form.findField('Person_Surname').disable();
							base_form.findField('Sex_id').disable();
							base_form.findField('Polis_Ser').disable();
							base_form.findField('Polis_Num').disable();
							base_form.findField('Polis_EdNum').disable();
							base_form.findField('Sex_id').disable();
						}

						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							if ( !base_form.findField('CmpCallCard_Numv').disabled ) {
								base_form.findField('CmpCallCard_Numv').focus(true, 250);
							}
							else {
								this.buttons[this.buttons.length - 1].focus();
							}
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
						
						if( base_form.findField('KLSubRgn_id').getValue() != null ) {
							base_form.findField('KLSubRgn_id').getStore().load({
								params: {
									'no':true
								},
								callback: function() {
									base_form.findField('KLSubRgn_id').setValue(base_form.findField('KLSubRgn_id').getValue());
									
								}
							})
						}
						if( base_form.findField('KLCity_id').getValue() != null ) {
							base_form.findField('KLCity_id').getStore().load({
								params: {
									subregion_id: (base_form.findField('KLSubRgn_id').getValue() > 0) ? base_form.findField('KLSubRgn_id').getValue() : 59
								},
								callback: function() {
									base_form.findField('KLCity_id').setValue(base_form.findField('KLCity_id').getValue());
								}
							})
						}
															
						if( base_form.findField('StreetAndUnformalizedAddressDirectory_id').getValue() != null ) {
							base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({
								params: {
									town_id: (base_form.findField('KLTown_id').getValue() > 0) ? base_form.findField('KLTown_id').getValue() : (base_form.findField('KLSubRgn_id').getValue() > 0) ? base_form.findField('KLSubRgn_id').getValue() : base_form.findField('KLCity_id').getValue(),
									showSocr: 1
								},
								callback: function() {
									base_form.findField('StreetAndUnformalizedAddressDirectory_id').setValue(base_form.findField('StreetAndUnformalizedAddressDirectory_id').getValue());
								}
							})
						}
						
						if( Number(base_form.findField('CmpReason_id').getRawValue()) > 0 ) {
							base_form.findField('CmpReason_id').getStore().load({
								callback: function() {
									base_form.findField('CmpReason_id').setValue(base_form.findField('CmpReason_id').getRawValue());
								}
							});
						}

						base_form.findField('CmpCallType_id').setFilterByDate(base_form.findField('CmpCallCard_prmDate').getValue());
					//this.setLpuAddrLoad();
					}.createDelegate(this),
					url: '/?c=CmpCallCard&m=loadCmpCallCardEditForm'
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