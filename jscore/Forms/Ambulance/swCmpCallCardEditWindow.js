/**
* swCmpCallCardEditWindow - окно редактирования карты вызова
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @co-author	Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      05.01.2010
*/
/*NO PARSE JSON*/

sw.Promed.swCmpCallCardEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swCmpCallCardEditWindow',
	objectSrc: '/jscore/Forms/Ambulance/swCmpCallCardEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	formStatus: 'edit',
	height: 550,
	id: 'CmpCallCardEditWindow',
	maximizable: true,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onCancelAction: Ext.emptyFn,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	width: 750,
	
	changePerson: function() {
		if ( !(getRegionNick().inlist(['perm','ekb','kareliya'])) ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();

		if ( !base_form.findField('CmpCallCard_id').getValue() ) {
			return false;
		}

		var params = {
			CmpCallCard_id: base_form.findField('CmpCallCard_id').getValue()
		}

		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				params.Person_id = person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id = person_data.Server_id;

				this.setAnotherPersonForDocument(params);
			}.createDelegate(this),
			personFirname: base_form.findField('PersonIdent_Firname').getValue(),
			personSecname: base_form.findField('PersonIdent_Secname').getValue(),
			personSurname: base_form.findField('PersonIdent_Surname').getValue(),
			searchMode: 'all'
		});
	},
	setAnotherPersonForDocument: function(params) {
		var form = this;
		var loadMask = new Ext.LoadMask(getWnd('swPersonSearchWindow').getEl(), {msg: "Переоформление карты СМП на другого человека..."});
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_pereoformlenii_kartyi_smp_na_drugogo_cheloveka']);
					}
					else if ( response_obj.Alert_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									switch ( response_obj.Alert_Code ) {
										case 1:
											params.allowEvnStickTransfer = 2;
										case 2:
											params.ignoreAgeFioCheck = 2;
										break;
									}

									form.setAnotherPersonForDocument(params);
								}
							},
							msg: response_obj.Alert_Msg,
							title: 'Вопрос'
						});
					}
					else {
						getWnd('swPersonSearchWindow').hide();
						this.hide();
                        var info_msg = lang['karta_smp_uspeshno_pereoformlena_na_drugogo_cheloveka'];
                        if (response_obj.Info_Msg) {
                            info_msg += '<br>' + response_obj.Info_Msg;
                        }
                        sw.swMsg.alert(lang['soobschenie'], info_msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_pereoformlenii_kartyi_smp_na_drugogo_cheloveka_proizoshli_oshibki']);
				}
			}.createDelegate(this),
			params: params,
			url: C_CHANGEPERSONFORDOC
		});
	},
	//Метод редактирования записи в гриде услуг
	editCmpCallCardUslugaGridRec: function(data) {
		
		if (!data.CmpCallCardUsluga_id) {
			return false;
		}
		
		var grid = this.findById('CCCEF_CmpCallCardUslugaGrid').getGrid(),
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
		
		//rec.set('CmpCallCardUsluga_id',Math.floor(Math.random() * (-100000)));
		this.findById('CCCEF_CmpCallCardUslugaGrid').getGrid().getStore().add(rec);
		
	},
	//Метод удаления записи из грида услуг
	deleteCmpCallCardUslguga: function() {
		
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('CCCEF_CmpCallCardUslugaGrid').getGrid();
		
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
		var grid = this.findById('CCCEF_CmpCallCardUslugaGrid').getGrid();

		var params = {
			action: action
		};

		if (!Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue())) {
			params.MedPersonal_id = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id');
		}
		
		params.CmpCallCard_setDT = Date.parseDate( base_form.findField('CmpCallCard_prmDate').getRawValue()+' '+base_form.findField('CmpCallCard_prmTime').getRawValue() , 'd.m.Y H:i' , true);
		if(!params.CmpCallCard_setDT){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'Установите дату и время приема',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		params.formParams = {};
		
		switch (action) {
			
			case 'add':
				params.callback = function(){
					return this.addCmpCallCardUslugaGridRec.apply(this,arguments);
				}.createDelegate(this);
				params.formParams.CmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();
				params.formParams.Person_id = base_form.findField('Person_id').getValue();
				params.formParams.CmpCallCardUsluga_setDate = base_form.findField('CmpCallCard_prmDate').getValue();
				params.formParams.CmpCallCardUsluga_setTime = base_form.findField('CmpCallCard_prmTime').getValue();
				break;
				
			case 'edit':
				params.callback = function(){
					return this.editCmpCallCardUslugaGridRec.apply(this,arguments);
				}.createDelegate(this);
				var record = grid.getSelectionModel().getSelected();
				if (!record && !record.get('CmpCallCardUsluga_id')) {
					return;
				}
				params.formParams = record.data;
				break;
				
			default:
				params.callback = Ext.emptyFn;
				break;
		}

		params.formParams.CmpCallCard_isShortEditVersion = base_form.findField('CmpCallCard_isShortEditVersion').getValue();
		
		getWnd('swCmpCallCardUslugaEditWindow').show(params);
		
	},
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

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

		var params = base_form.getValues();

		if ( typeof base_form.findField('Person_BirthDay').getValue() == 'object' ) {
			params.Person_BirthDay = Ext.util.Format.date(base_form.findField('Person_BirthDay').getValue(), 'd.m.Y');
		}
		else if ( typeof base_form.findField('Person_BirthDay').getValue() == 'string' ) {
			params.Person_BirthDay = base_form.findField('Person_BirthDay').getValue();
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

		if ( base_form.findField('Person_FirName').disabled ) {
			params.Person_FirName = base_form.findField('Person_FirName').getValue();
		}

		if ( base_form.findField('Person_SecName').disabled ) {
			params.Person_SecName = base_form.findField('Person_SecName').getValue();
		}

		if ( base_form.findField('Person_SurName').disabled ) {
			params.Person_SurName = base_form.findField('Person_SurName').getValue();
		}

		if ( base_form.findField('Sex_id').disabled ) {
			params.Sex_id = base_form.findField('Sex_id').getValue();
		}
        params.CmpCallCard_Numv = base_form.findField('CmpCallCard_Numv').getValue();
        params.CmpCallCard_Ngod = base_form.findField('CmpCallCard_Ngod').getValue();
		params.MedPersonal_id = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id');
		params.CmpCallCardInputType_id = (base_form.findField('CmpCallCard_isShortEditVersion').getValue() == 2) ? 2 : 1;

		if (base_form.findField('CmpCallCard_isShortEditVersion').getValue() == 2) {
			base_form.findField('CmpLpu_id').setValue(base_form.findField('ShortEdit_CmpLpu_id').getValue());
		}
		params.CmpLpu_id = base_form.findField('CmpLpu_id').getValue();
		var usluga_items = this.UslugaViewFrame.getGrid().getStore().query('CmpCallCardUsluga_id',/[^0]/).items;

		var usluga_data_array = [];

		for (var i = 0; i < usluga_items.length; i++) {
			usluga_data_array.push(usluga_items[i].data);
		};

		// https://jira.is-mis.ru/browse/PROMEDWEB-2937 проверка услуг МБТ
		if (getRegionNick().inlist(['perm']) && params.PayType_id.inlist(['219', '237'])) {
			if (!usluga_data_array.length) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: 'Не указано ни одной услуги относящейся к виду оплаты "' + base_form.findField('PayType_id').getSelectedRecordData().PayType_Name + '": измените вид оплаты или добавьте в карту соответствующую услугу',
					title: 'Ошибка'
				});
				this.formStatus = 'edit';
				return false;
			} else {
				var checked = false;

				for (var o in usluga_data_array) {
					if (usluga_data_array[o].PayType_id == params.PayType_id && !checked) {
						checked = true;
					}
				}

				if (!checked) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						msg: 'Не указано ни одной услуги относящейся к виду оплаты "' + base_form.findField('PayType_id').getSelectedRecordData().PayType_Name + '": измените вид оплаты или добавьте в карту соответствующую услугу',
						title: 'Ошибка'
					});
					this.formStatus = 'edit';
					return false;
				}
			}
		}

		var prmDate = Ext.util.Format.date(base_form.findField('CmpCallCard_prmDate').getValue(), 'd.m.Y'),
			timefields = ['CmpCallCard_Tper','CmpCallCard_Vyez','CmpCallCard_Tiz1','CmpCallCard_Przd','CmpCallCard_Tgsp','CmpCallCard_Tsta','CmpCallCard_Tisp','CmpCallCard_Tvzv'];

		timefields.forEach(function(item) {
			if ( !Ext.isEmpty(base_form.findField(item).getValue()) ) {
				params[item] = prmDate + ' ' + base_form.findField(item).getValue();
			}
		});
		
		params.usluga_array = JSON.stringify(usluga_data_array);

		this.save_form(base_form, params);
		/*
		var check_dupl_params = new Object();
		check_dupl_params.CmpCallCard_Numv = base_form.findField('CmpCallCard_Numv').getValue();
		check_dupl_params.CmpCallCard_Ngod = base_form.findField('CmpCallCard_Ngod').getValue();
		check_dupl_params.CmpCallCard_prmDate = Ext.util.Format.date(base_form.findField('CmpCallCard_prmDate').getValue(), 'd.m.Y');
		check_dupl_params.CmpCallCard_prmTime = base_form.findField('CmpCallCard_prmTime').getValue();
		this.checkDuplicate(params, this, check_dupl_params);
		*/
	},
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'CmpArea_gid',
			'CmpArea_id',
			'CmpArea_pid',
			'CmpCallCard_City',
			'CmpCallCard_Comm',
			'CmpCallCard_D201',
			'CmpCallCard_Dlit',
			'CmpCallCard_Dokt',
			'MedStaffFact_id',
			'CmpCallCard_Dom',
			'CmpCallCard_Korp',
			'CmpCallCard_Room',
			'CmpCallCard_Dsp1',
			'CmpCallCard_Dsp2',
			'CmpCallCard_Dsp3',
			'CmpCallCard_Dspp',
			'CmpCallCard_Etaj',
			'CmpCallCard_Expo',
			'CmpCallCard_IsAlco',
			'RankinScale_id',
			'CmpCallCard_IsPoli',
			'CmpCallCard_Izv1',
			'CmpCallCard_Kakp',
			'CmpCallCard_Kilo',
			'CmpCallCard_Kodp',
			'CmpCallPlaceType_id',
			'CmpCallerType_id',
			'CmpCallCard_Kvar',
			'CmpCallCard_Line',
			'CmpCallCard_Ncar',
			//'CmpCallCard_Ngod',
			'CmpCallCard_Numb',
			//'CmpCallCard_Numv',
			'CmpCallCard_PCity',
			'CmpCallCard_PDom',
			'CmpCallCard_PKvar',
			'CmpCallCard_Podz',
			'CmpCallCard_Prdl',
			'CmpCallCard_prmDate',
			'CmpCallCard_prmTime',
			'CmpCallCard_Prty',
			'CmpCallCard_Przd',
			'CmpCallCard_PUlic',
			'CmpCallCard_RCod',
			'CmpCallCard_Sect',
			'CmpCallCard_Smpb',
			'CmpCallCard_Smpp',
			'CmpCallCard_Smpt',
			'CmpCallCard_Stan',
			'CmpCallCard_Stbb',
			'CmpCallCard_Stbr',
			'CmpCallCard_Tab2',
			'CmpCallCard_Tab3',
			'CmpCallCard_Tab4',
			'CmpCallCard_TabN',
			'CmpCallCard_Telf',
			'CmpCallCard_Tgsp',
			'CmpCallCard_Tisp',
			'CmpCallCard_Tiz1',
			'CmpCallCard_Tper',
			'CmpCallCard_Tsta',
			'CmpCallCard_Tvzv',
			'CmpCallCard_Ulic',
			'CmpCallCard_Vr51',
			'CmpCallCard_Vyez',
			'CmpCallType_id',
			'CmpDiag_aid',
			'CmpDiag_oid',
			'CmpLpu_id',
			'CmpPlace_id',
			'CmpProfile_bid',
			'CmpProfile_cid',
			'CmpReason_id',
			'CmpResult_id',
			'ResultDeseaseType_id',
			'LeaveType_id',
			'CmpTalon_id',
			'CmpTrauma_id',
			'Diag_sid',
			'Diag_uid',
			'Diag_sopid',
			//'Lpu_oid',
			'Person_Age',
			'Person_FirName',
			'Polis_Num',
			'Person_SecName',
			'Person_SurName',
			'Sex_id',
			'PersonIdent_Age',
			'PersonIdent_Firname',
			'PolisIdent_Num',
			'PersonIdent_Secname',
			'PersonIdent_Surname',
			'SexIdent_id',
			//'Lpu_id',
			'UslugaComplex_id',
			
			'ShortEdit_CmpLpu_id',
			'CmpCallCard_Condition',
			'CmpCallCard_Recomendations',
			'LpuSection_id',
			'Lpu_cid'
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
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	initComponent: function() {
		var win = this;
		
		this.UslugaViewFrame = new sw.Promed.ViewFrame({
			id: 'CCCEF_CmpCallCardUslugaGrid',
			object: 'CmpCallCardUsluga',
			dataUrl: '/?c=CmpCallCard&m=loadCmpCallCardUslugaGrid',
			height: 200,
			autoLoadData: false,
			border: false,
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
				{name: 'status', type: 'string', hidden: true},
				
			],
			actions: [
				{name:'action_add', handler: function(){this.openCmpCallCardUslgugaEditWindow('add')}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openCmpCallCardUslgugaEditWindow('edit')}.createDelegate(this)},
				{name:'action_view', hidden: true, handler: function(){this.openCmpCallCardUslgugaEditWindow('view')}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteCmpCallCardUslguga()}.createDelegate(this)},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_print', hidden: true, disabled: true}
			]
		})
		
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'CmpCallCardEditForm',
			labelAlign: 'right',
			labelWidth: 250,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{name: 'accessType'},
				{name: 'CmpCallCard_IsPaid'},
				{name: 'CmpCallCard_IndexRep'},
				{name: 'CmpCallCard_IndexRepInReg'},
				{name: 'CmpCallCard_id'},
				{name: 'CmpCloseCard_id'},
				{name: 'CmpArea_gid'},
				{name: 'CmpArea_id'},
				{name: 'CmpArea_pid'},
				{name: 'CmpCallCard_City'},
				{name: 'CmpCallCard_Comm'},
				{name: 'CmpCallCard_D201'},
				{name: 'CmpCallCard_Dlit'},
				{name: 'CmpCallCard_Dokt'},
				{name: 'MedStaffFact_id'},
				{name: 'CmpCallCard_IsMedPersonalIdent'},
				{name: 'CmpCallCard_Dom'},
				{name: 'CmpCallCard_Korp'},
				{name: 'CmpCallCard_Room'},
				{name: 'CmpCallPlaceType_id'},
				{name: 'CmpCallCard_Dsp1'},
				{name: 'CmpCallCard_Dsp2'},
				{name: 'CmpCallCard_Dsp3'},
				{name: 'CmpCallCard_Dspp'},
				{name: 'CmpCallCard_Etaj'},
				{name: 'CmpCallCard_Expo'},
				{name: 'CmpCallCard_IsAlco'},
				{name: 'RankinScale_id'},
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
				{name: 'CmpCallCard_Tisp'},
				{name: 'CmpCallCard_Tiz1'},
				{name: 'CmpCallCard_Tper', convert: function(v){return !Ext.isEmpty(v) ? new Date(v).format('H:i') : null;}},
                {name: 'CmpCallCard_Vyez', convert: function(v){return !Ext.isEmpty(v) ? new Date(v).format('H:i') : null;}},
                {name: 'CmpCallCard_Przd', convert: function(v){return !Ext.isEmpty(v) ? new Date(v).format('H:i') : null;}},
                {name: 'CmpCallCard_Tgsp', convert: function(v){return !Ext.isEmpty(v) ? new Date(v).format('H:i') : null;}},
				{name: 'CmpCallCard_Tsta', convert: function(v){return !Ext.isEmpty(v) ? new Date(v).format('H:i') : null;}},
                {name: 'CmpCallCard_Tisp', convert: function(v){return !Ext.isEmpty(v) ? new Date(v).format('H:i') : null;}},
				{name: 'CmpCallCard_Tvzv', convert: function(v){return !Ext.isEmpty(v) ? new Date(v).format('H:i') : null;}},
				{name: 'CmpCallCard_Ulic'},
				{name: 'CmpCallCard_Vr51'},
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
				{name: 'LeaveType_id'},
				{name: 'CmpTalon_id'},
				{name: 'CmpTrauma_id'},
				{name: 'Diag_sid'},
				{name: 'Diag_uid'},
				{name: 'Diag_sopid'},
				{name: 'Lpu_oid'},
				{name: 'Person_Age'},
				{name: 'Person_BirthDay'},
				{name: 'Person_FirName'},
				{name: 'Person_id'},
				{name: 'Polis_Num'},
				{name: 'Person_SecName'},
				{name: 'Person_SurName'},
				{name: 'Sex_id'},
				{name: 'PersonIdent_Age'},
				{name: 'PersonIdent_Firname'},
				{name: 'PolisIdent_Num'},
				{name: 'PersonIdent_Secname'},
				{name: 'PersonIdent_Surname'},
				{name: 'SexIdent_id'},
				{name: 'Lpu_id'},
				{name: 'LpuBuilding_id'},
				{name: 'UslugaComplex_id'},
				{name: 'CmpCallCardCostPrint_setDT'},
				{name: 'CmpCallCardCostPrint_IsNoPrint'},
				
				{name: 'ShortEdit_CmpLpu_id'},
				{name: 'CmpCallCard_Condition'},
				{name: 'CmpCallCard_Recomendations'},
				{name: 'LpuSection_id'},
				{name: 'CmpCallCard_isShortEditVersion'},
				{name: 'PayType_id'},

				{name: 'Lpu_cid'}
				
			]),
			region: 'center',
			url: '/?c=CmpCallCard&m=saveCmpCallCard',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_isShortEditVersion',
				value: '1',
				xtype: 'hidden'
			}, {
				name:'CmpCallCard_IsPaid',
				xtype:'hidden'
			}, {
				name:'CmpCallCard_IndexRep',
				xtype:'hidden'
			}, {
				name:'CmpCallCard_IndexRepInReg',
				xtype:'hidden'
			}, {
				name: 'CmpCallCard_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCloseCard_id',
				xtype: 'hidden'
			}, {
				disabled: true,
				name: 'Person_BirthDay',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_IsMedPersonalIdent',
				value: 1,
				xtype: 'hidden'
			},			
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding: 0.5em;',
				border: true,
				collapsible: true,
				id: 'CCCEF_PersonPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						// this.FormPanel.getForm().findField('PrehospDirect_id').focus(true);
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['1_patsient'],

				items: [{
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					title: lang['dannyie_patsienta'],
					id: 'CCCEF_PersonDataFieldset',
					xtype: 'fieldset',
					items: [{
						disabledClass: 'field-disabled',
						fieldLabel: lang['familiya'],
						listeners: {
							'keydown': function (inp, e) {
								if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this)
						},
						name: 'Person_SurName',
						// tabIndex: TABINDEX_PEF + 1,
						toUpperCase: true,
						width: 180,
						xtype: 'textfieldpmw'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['imya'],
						name: 'Person_FirName',
						// tabIndex: TABINDEX_PEF + 2,
						toUpperCase: true,
						width: 180,
						xtype: 'textfieldpmw'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['otchestvo'],
						name: 'Person_SecName',
						// tabIndex: TABINDEX_PEF + 3,
						toUpperCase: true,
						width: 180,
						xtype: 'textfieldpmw'
					}, {
						allowDecimals: false,
						allowNegative: false,
						disabledClass: 'field-disabled',
						fieldLabel: lang['vozrast'],
						name: 'Person_Age',
						// tabIndex: TABINDEX_PEF + 4,
						toUpperCase: true,
						width: 180,
						xtype: 'numberfield'
					}, {
						comboSubject: 'Sex',
						disabledClass: 'field-disabled',
						fieldLabel: lang['pol'],
						hiddenName: 'Sex_id',
						// tabIndex: TABINDEX_PEF + 5,
						width: 130,
						xtype: 'swcommonsprcombo'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['nomer_polisa'],
						name: 'Polis_Num',
						// tabIndex: TABINDEX_PEF + 6,
						width: 130,
						xtype: 'textfield'
					}]
				}, {
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					id: 'CCCEF_PersonIndentDataFieldset',
					title: lang['dannyie_identifitsirovannogo_patsienta'],
					xtype: 'fieldset',
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							width: 450,
							items: [{
								disabledClass: 'field-disabled',
								fieldLabel: lang['familiya'],
								name: 'PersonIdent_Surname',
								// tabIndex: TABINDEX_PEF + 1,
								toUpperCase: true,
								width: 180,
								xtype: 'textfieldpmw'
							}]
						}, {
							border: false,
							layout: 'form',
							width: 200,
							items: [{
								handler: function() {
									this.changePerson();
								}.createDelegate(this),
								hidden: !(getRegionNick().inlist(['perm','ekb'])),
								icon: 'img/icons/doubles16.png', 
								iconCls: 'x-btn-text',
								id: 'CCCEF_PersonChangeButton',
								text: lang['smenit_patsienta'],
								tooltip: lang['smenit_patsienta'],
								xtype: 'button'
							}]
						}]
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['imya'],
						name: 'PersonIdent_Firname',
						// tabIndex: TABINDEX_PEF + 2,
						toUpperCase: true,
						width: 180,
						xtype: 'textfieldpmw'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['otchestvo'],
						name: 'PersonIdent_Secname',
						// tabIndex: TABINDEX_PEF + 3,
						toUpperCase: true,
						width: 180,
						xtype: 'textfieldpmw'
					}, {
						allowDecimals: false,
						allowNegative: false,
						disabledClass: 'field-disabled',
						fieldLabel: lang['vozrast'],
						name: 'PersonIdent_Age',
						// tabIndex: TABINDEX_PEF + 4,
						toUpperCase: true,
						width: 180,
						xtype: 'numberfield'
					}, {
						comboSubject: 'Sex',
						disabledClass: 'field-disabled',
						fieldLabel: lang['pol'],
						hiddenName: 'SexIdent_id',
						// tabIndex: TABINDEX_PEF + 5,
						width: 130,
						xtype: 'swcommonsprcombo'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['nomer_polisa'],
						name: 'PolisIdent_Num',
						// tabIndex: TABINDEX_PEF + 6,
						width: 130,
						xtype: 'textfield'
					}]
				}, {
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					id: 'CCCEF_PersonAddressFieldset',
					title: lang['adres_projivaniya'],
					xtype: 'fieldset',

					items: [{
						comboSubject: 'CmpArea',
						disabledClass: 'field-disabled',
						fieldLabel: lang['kod_rayona_projivaniya'],
						hiddenName: 'CmpArea_pid',
						// tabIndex: TABINDEX_PEF + 5,
						width: 350,
						xtype: 'swcommonsprcombo'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['naselennyiy_punkt'],
						name: 'CmpCallCard_PCity',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['ulitsa'],
						name: 'CmpCallCard_PUlic',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['dom'],
						name: 'CmpCallCard_PDom',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['kvartira'],
						name: 'CmpCallCard_PKvar',
						maxLength: 5,
						autoCreate: {tag: "input", size:14, maxLength: "5", autocomplete: "off"},
						//maskRe: /^([а-яА-Я0-9]{1,5})$/,
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfieldpmw'
					}]
				}, {
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					title: lang['uslugi'],
					xtype: 'fieldset',
					hidden: getRegionNick() != 'ekb',

					items: [{
						disabledClass: 'field-disabled',
						fieldLabel: lang['osnovnaya_usluga'],
						hiddenName: 'UslugaComplex_id',
						width: 350,
						listWidth: 600,
						xtype: 'swuslugacomplexnewcombo'
					}]
				},{
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					id: 'CCCEF_HostpitFieldset',
					title: lang['gospitalizatsiya'],
					xtype: 'fieldset',

					items: [{
						comboSubject: 'CmpArea',
						disabledClass: 'field-disabled',
						fieldLabel: lang['v_kakom_rayone_gospitalizirovan'],
						hiddenName: 'CmpArea_gid',
						// tabIndex: TABINDEX_PEF + 5,
						width: 350,
						xtype: 'swcommonsprcombo'
					}/*, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['kuda_dostavlen'],
						hiddenName: 'Lpu_oid',
						listeners: {
							'render': function(combo) {
								combo.getStore().load();
							}
						},
						listWidth: 400,
						// tabIndex: TABINDEX_PEF + 5,
						width: 250,
						xtype: 'swlpucombo'
					}*/, {
						comboSubject: 'CmpLpu',
						disabledClass: 'field-disabled',
						fieldLabel: lang['kuda_dostavlen'],
						hiddenName: 'CmpLpu_id',
						orderBy: 'Name',
						// tabIndex: TABINDEX_PEF + 5,
						width: 350,
						xtype: 'swcommonsprcombo'
					}]
				}, {
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					title: lang['diagnozyi'],
					xtype: 'fieldset',

					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [
								new sw.Promed.SwDiagCombo({
									checkAccessRights: !(getRegionNick().inlist(['perm'])),
								disabledClass: 'field-disabled',
								fieldLabel: lang['osnovnoy_diagnoz_po_mkb-10'],
								dotSubstitute: true,
								hiddenName: 'Diag_uid',
								name: 'Diag_uid',
								id: win.id + '_DiagCombo',
								withGroups: true,
								// tabIndex: TABINDEX_PEF + 5,
								width: 250
							})]
						}, {
							border: false,
							labelWidth: 100,
							layout: 'form',
							items: [{
								comboSubject: 'CmpDiag',
								disabledClass: 'field-disabled',
								fieldLabel: lang['diagnoz_adis'],
								hiddenName: 'CmpDiag_oid',
								// tabIndex: TABINDEX_PEF + 5,
								width: 145,
								listWidth: 300,
								xtype: 'swcommonsprcombo'
							}]
						}]
					}, {
						xtype: 'swdiagcombo',
						checkAccessRights: true,
						disabledClass: 'field-disabled',
						fieldLabel: 'Сопутствующий диагноз',
						hiddenName: 'Diag_sopid',
						name: 'Diag_sopid',
						id: win.id + '_Diag_sopid',
						withGroups: true,
						// tabIndex: TABINDEX_PEF + 5,
						width: 350
					}, {
						comboSubject: 'CmpDiag',
						disabledClass: 'field-disabled',
						fieldLabel: lang['diagnoz_oslojnenie'],
						hiddenName: 'CmpDiag_aid',
						// tabIndex: TABINDEX_PEF + 5,
						width: 350,
						xtype: 'swcommonsprcombo'
					}, {
						comboSubject: 'CmpTrauma',
						disabledClass: 'field-disabled',
						fieldLabel: lang['vid_zabolevaniya'],
						hiddenName: 'CmpTrauma_id',
						// tabIndex: TABINDEX_PEF + 5,
						width: 350,
						xtype: 'swcommonsprcombo'
					}, {
						comboSubject: 'YesNo',
						disabledClass: 'field-disabled',
						fieldLabel: lang['alkogolnoe_narkoticheskoe_opyanenie'],
						hiddenName: 'CmpCallCard_IsAlco',
						// tabIndex: TABINDEX_PEF + 5,
						width: 100,
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: lang['znachenie_po_shkale_renkina'],
						width: 450,
						comboSubject: 'RankinScale',
						hiddenName: 'RankinScale_id',
						xtype: 'swcommonsprcombo'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['diagnoz_statsionara'],
						hiddenName: 'Diag_sid',
						allowBlank: true,
						withGroups: true,
						// tabIndex: TABINDEX_PEF + 5,
						width: 500,
						xtype: 'swdiagcombo'
					}, {
						comboSubject: 'CmpTalon',
						disabledClass: 'field-disabled',
						fieldLabel: lang['priznak_rashojdeniya_diagnozov_ili_prichina_otkaza_statsionara'],
						hiddenName: 'CmpTalon_id',
						// tabIndex: TABINDEX_PEF + 5,
						width: 350,
						xtype: 'swcommonsprcombo'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['ekspertnaya_otsenka'],
						name: 'CmpCallCard_Expo',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'numberfield',
						maskRe: /[0-9]/
					}, {
						comboSubject: 'YesNo',
						disabledClass: 'field-disabled',
						fieldLabel: lang['aktiv_v_polikliniku'],
						hiddenName: 'CmpCallCard_IsPoli',
						// tabIndex: TABINDEX_PEF + 5,
						width: 100,
						xtype: 'swcommonsprcombo'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['sostoyanie'],
						height: 100,
						name: 'CmpCallCard_Condition',
						width: 350,
						xtype: 'textarea'
					},{
						disabledClass: 'field-disabled',
						fieldLabel: lang['rekomendatsii'],
						height: 100,
						name: 'CmpCallCard_Recomendations',
						width: 350,
						xtype: 'textarea'
					}, {
						comboSubject: 'CmpLpu',
						disabledClass: 'field-disabled',
						fieldLabel: lang['kuda_dostavlen'],
						hiddenName: 'ShortEdit_CmpLpu_id',
						orderBy: 'Name',
						// tabIndex: TABINDEX_PEF + 5,
						width: 350,
						xtype: 'swcommonsprcombo'
					}]
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding: 0.5em;',
				border: true,
				collapsible: true,
				id: 'CCCEF_CallPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						// this.FormPanel.getForm().findField('PrehospDirect_id').focus(true);
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['2_vyizov'],

				items: [{
					xtype: 'fieldset',
					autoHeight: true,
					id: 'CmpCallCard_RepFlagId',
					items: [{
						fieldLabel: lang['povtornaya_podacha'],
						listeners: {
							'check': function(checkbox, value) {
								if ( getRegionNick() != 'perm' ) {
									return false;
								}

								var base_form = this.FormPanel.getForm();

								var
									CmpCallCard_IndexRep = parseInt(base_form.findField('CmpCallCard_IndexRep').getValue()),
									CmpCallCard_IndexRepInReg = parseInt(base_form.findField('CmpCallCard_IndexRepInReg').getValue()),
									CmpCallCard_IsPaid = parseInt(base_form.findField('CmpCallCard_IsPaid').getValue());

								var diff = CmpCallCard_IndexRepInReg - CmpCallCard_IndexRep;

								if ( CmpCallCard_IsPaid != 2 || CmpCallCard_IndexRepInReg == 0 ) {
									return false;
								}

								if ( value == true ) {
									if ( diff == 1 || diff == 2 ) {
										CmpCallCard_IndexRep = CmpCallCard_IndexRep + 2;
									}
									else if ( diff == 3 ) {
										CmpCallCard_IndexRep = CmpCallCard_IndexRep + 4;
									}
								}
								else if ( value == false ) {
									if ( diff <= 0 ) {
										CmpCallCard_IndexRep = CmpCallCard_IndexRep - 2;
									}
								}

								base_form.findField('CmpCallCard_IndexRep').setValue(CmpCallCard_IndexRep);

							}.createDelegate(this)
						},
						name: 'CmpCallCard_RepFlag',					
						xtype: 'checkbox'
					}]
				}, {
					autoCreate: {tag: "input", type: "text", size: "20", autocomplete: "off",  maxlength: '12'},
					disabledClass: 'field-disabled',
					fieldLabel: lang['nomer_vyizova_za_den'],
					maxLength: 12,
					name: 'CmpCallCard_Numv',
					disabled: getGlobalOptions().region.nick.inlist(['perm']),
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'textfield',
					maskRe: /[0-9]/
				}, {
					autoCreate: {tag: "input", type: "text", size: "20", autocomplete: "off",  maxlength: '12'},
					disabledClass: 'field-disabled',
					fieldLabel: lang['nomer_s_nachala_goda'],
					maxLength: 12,
					name: 'CmpCallCard_Ngod',
					disabled: getGlobalOptions().region.nick.inlist(['perm']),
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'textfield',
					maskRe: /[0-9]/
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['kod_territorialnoy_stantsii_smp'],
					name: 'CmpCallCard_Smpt',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'numberfield',
					maskRe: /[0-9]/
				}, {
					//valueField: 'Lpu_id',
					//allowBlank: false,
					//autoLoad: true,
					width: 350,
					listWidth: 350,
					fieldLabel: lang['territorialnaya_stantsiya_smp'],
					disabledClass: 'field-disabled',
					hiddenName: 'LpuBuilding_id',
					//displayField: 'Lpu_Nick',
					//medServiceTypeId: 18,					
					//comAction: 'AllAddress',					
					//xtype: 'swlpuwithmedservicecombo'					
					//xtype: 'swcmpstationcombo'
					xtype: 'swsmpunitscombo'
				}, {
					hiddenName: 'Lpu_id',					
					xtype: 'hidden'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['data_priema'],
					format: 'd.m.Y',
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();

							var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

							base_form.findField('MedStaffFact_id').clearValue();
							base_form.findField('MedStaffFact_id').getStore().removeAll();

							var msfFilter ={
								Lpu_id: getGlobalOptions().lpu_id,
								withoutLpuSection: true
							};

							if ( !Ext.isEmpty(newValue) ) {
								msfFilter.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
							}

							setMedStaffFactGlobalStoreFilter(msfFilter);

							base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

							if ( !Ext.isEmpty(MedStaffFact_id) ) {
								var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
									return (rec.get('MedStaffFact_id') == MedStaffFact_id);
								});

								if ( index >= 0 ) {
									base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
								}
							}

							base_form.findField('Diag_uid').setFilterByDate(newValue);
							base_form.findField('Diag_sid').setFilterByDate(newValue);
							base_form.findField('CmpCallType_id').setFilterByDate(newValue);
						}.createDelegate(this)
					},
					name: 'CmpCallCard_prmDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					selectOnFocus: true,
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'swdatefield'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['vremya_priema'],
					name: 'CmpCallCard_prmTime',				
					plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
					// tabIndex: TABINDEX_PEF + 6,
					validateOnBlur: false,
					width: 60,
					xtype: 'swtimefield'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['pult_priema'],
					name: 'CmpCallCard_Line',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'numberfield',
					maskRe: /[0-9]/
				}, {
					allowDecimals: false,
					allowNegative: false,
					disabledClass: 'field-disabled',
					fieldLabel: lang['prioritet'],
					name: 'CmpCallCard_Prty',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'numberfield'
				}, {
					allowDecimals: false,
					allowNegative: false,
					disabledClass: 'field-disabled',
					fieldLabel: lang['sektor'],
					name: 'CmpCallCard_Sect',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'numberfield'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['tip_vyizova'],
					hiddenName: 'CmpCallType_id',
					// tabIndex: TABINDEX_PEF + 5,
					width: 350,
					xtype: 'swcmpcalltypecombo'
				}, {
					comboSubject: 'CmpProfile',
					disabledClass: 'field-disabled',
					fieldLabel: lang['profil_vyizova'],
					hiddenName: 'CmpProfile_cid',
					loadParams: {params: {where: getRegionNick().inlist([ 'krym' ]) ? ' where Region_id = ' + getRegionNumber() : ''}},
					moreFields: [
						{ name: 'Region_id', type: 'int' }
					],
					// tabIndex: TABINDEX_PEF + 5,
					width: 350,
					xtype: 'swcommonsprcombo'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['nomer_p_s'],
					name: 'CmpCallCard_Stan',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'numberfield',
					maskRe: /[0-9]/
				}, {
					comboSubject: 'CmpResult',
					disabledClass: 'field-disabled',
					fieldLabel: lang['rezultat'],
					hiddenName: 'CmpResult_id',
					listeners: {
						'change': function(c,n,o) {
							this.checkForCostPrintPanel();
							var base_form = this.FormPanel.getForm();
							if ( !Ext.isEmpty(n) ) {
								var index = c.getStore().findBy(function(r) {
									return (r.get(c.valueField) == n);
								});
								var rec = c.getStore().getAt(index);
								if ( typeof rec == 'object' && !Ext.isEmpty(rec.get('LeaveType_id')) ) {
									index = base_form.findField('LeaveType_id').getStore().findBy(function(r) {
										return (rec.get('LeaveType_id') == r.get('LeaveType_id'));
									})
									var LeaveTypeRec = base_form.findField('LeaveType_id').getStore().getAt(index);
									base_form.findField('LeaveType_id').fireEvent('select', base_form.findField('LeaveType_id'),LeaveTypeRec);
								}
								else{
									base_form.findField('LeaveType_id').clearValue();
									base_form.findField('LeaveType_id').fireEvent('select', base_form.findField('LeaveType_id'));
								}
							}
							else {
								base_form.findField('LeaveType_id').clearValue();
								base_form.findField('LeaveType_id').fireEvent('select', base_form.findField('LeaveType_id'));
							}
						}.createDelegate(this)
					},
					moreFields: [
						{ name: 'LeaveType_id', type: 'int' }
					],
					// tabIndex: TABINDEX_PEF + 5,
					width: 350,
					xtype: 'swcommonsprcombo'
				},{
					fieldLabel: lang['fed_rezultat'],
					id: this.id + '_LeaveType_id',
					hiddenName: 'LeaveType_id',
					listWidth: 600,
					lastQuery:'',
					width: 350,
					xtype: 'swleavetypefedcombo'
				}, {
					allowBlank:false,
					disabledClass: 'field-disabled',
					fieldLabel: lang['ishod'],
					hiddenName: 'ResultDeseaseType_id',
					loadParams: {params: {where: ' where ResultDeseaseType_Code in (401, 402, 403)'}},
					// tabIndex: TABINDEX_PEF + 5,
					width: 350,
					xtype: 'swresultdeseasetypefedcombo'
				},
				{
					name: 'PayType_id',
					xtype: 'swpaytypecombo',
					lastQuery: '',
					allowBlank: false,
					enableKeyEvents: true,
					labelWidth: 100,
					listWidth: 300
				},
//				{
//					comboSubject: 'CmpReason',
//					disabledClass: 'field-disabled',
//					fieldLabel: 'Повод',
//					allowBlank: false,
//					hiddenName: 'CmpReason_id',
//					tabIndex: TABINDEX_PEF + 5,
//					width: 350,
//					xtype: 'swcommonsprcombo'
//				}, 
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
								if (this.getStore().getCount() == 1) {
									this.select(0);
								}
							}
						},
						select: function(c, r, i) {
							this.setValue(r.get('CmpReason_id'));
							this.setRawValue(r.get('CmpReason_Code')+'.'+r.get('CmpReason_Name'));
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
					xtype: 'swcommonsprcombo',
					fieldLabel: lang['kto_vyizyivaet'],
					comboSubject: 'CmpCallerType',
					hiddenName: 'CmpCallerType_id',
					displayField: 'CmpCallerType_Name',
					disabledClass: 'field-disabled',
					width: 350
				}, {
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					title: lang['mesto_vyizova'],
					xtype: 'fieldset',

					items: [{
						comboSubject: 'CmpArea',
						disabledClass: 'field-disabled',
						fieldLabel: lang['kod_rayona'],
						hiddenName: 'CmpArea_id',
						// tabIndex: TABINDEX_PEF + 5,
						width: 350,
						xtype: 'swcommonsprcombo'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['naselennyiy_punkt'],
						name: 'CmpCallCard_City',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['ulitsa'],
						name: 'CmpCallCard_Ulic',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['dom'],
						name: 'CmpCallCard_Dom',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['korpus'],
						name: 'CmpCallCard_Korp',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						maxLength: 5,
						autoCreate: {tag: "input", size:14, maxLength: "5", autocomplete: "off"},
						//maskRe: /^([а-яА-Я0-9]{1,5})$/,
						fieldLabel: lang['kvartira'],
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
					},
					{
						comboSubject: 'CmpCallPlaceType',
						fieldLabel	   : lang['tip_mesta_vyizova'],
						hiddenName: 'CmpCallPlaceType_id',
						name: 'CmpCallPlaceType_id',
						xtype: 'swcommonsprcombo',
						width: 250,
						listWidth: 250,
						value: 1
					}
					]
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['telefon'],
					name: 'CmpCallCard_Telf',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'textfield'
				}, {
					comboSubject: 'CmpPlace',
					disabledClass: 'field-disabled',
					fieldLabel: lang['mestonahojdenie_bolnogo'],
					hiddenName: 'CmpPlace_id',
					// tabIndex: TABINDEX_PEF + 5,
					width: 350,
					xtype: 'swcommonsprcombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var form = win.FormPanel.getForm();
							
							var lpu_cid = form.findField('Lpu_cid');
							if ( lpu_cid ) {
								//lpu_cid.allowBlank = !(newValue == 7);
								lpu_cid.setVisible( (newValue == 7) );
								lpu_cid.getEl().up('.x-form-item').setDisplayed( (newValue == 7) );
								lpu_cid.setDisabled( !(newValue == 7) );
							}
							
							form.isValid();
						}
					}
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['mo_vyizova'],
					hiddenName: 'Lpu_cid',
					hidden: true,
					disabled: true,
					width: 350,
					xtype: 'swlpucombo'
				}, {
					disabledClass: 'field-disabled',
					fieldLabel: lang['dopolnitelnaya_informatsiya'],
					height: 100,
					name: 'CmpCallCard_Comm',
					// tabIndex: TABINDEX_PEF + 5,
					width: 350,
					xtype: 'textarea'
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding: 0.5em;',
				border: true,
				collapsible: true,
				id: 'CCCEF_CallManagementPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						// this.FormPanel.getForm().findField('PrehospDirect_id').focus(true);
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['3_upravlenie_vyizovom'],

				items: [{
					disabledClass: 'field-disabled',
					fieldLabel: lang['kod_ssmp_priema_vyizova'],
					name: 'CmpCallCard_Smpp',
					// tabIndex: TABINDEX_PEF + 6,
					width: 100,
					xtype: 'numberfield',
					maskRe: /[0-9]/
				}, {
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					title: lang['sotrudniki'],
					xtype: 'fieldset',

					items: [{
						disabledClass: 'field-disabled',
						fieldLabel: lang['starshiy_vrach_smenyi'],
						name: 'CmpCallCard_Vr51',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['starshiy_dispetcher_smenyi'],
						name: 'CmpCallCard_D201',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['prinyal'],
						name: 'CmpCallCard_Dsp1',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['naznachil'],
						name: 'CmpCallCard_Dsp2',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['peredal'],
						name: 'CmpCallCard_Dspp',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['zakryil'],
						name: 'CmpCallCard_Dsp3',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}]
				}, {
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					title: lang['kontrol'],
					xtype: 'fieldset',

					items: [{
						disabledClass: 'field-disabled',
						fieldLabel: lang['dlitelnost_priema_vyizova_v_sek'],
						name: 'CmpCallCard_Dlit',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'numberfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['nomer_stroki_iz_spiska_predlojeniy'],
						name: 'CmpCallCard_Prdl',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['vremya_peredachi_izvescheniya'],
						name: 'CmpCallCard_Tiz1',
						//onTriggerClick: Ext.emptyFn,
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_PEF + 6,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['izveschenie'],
						name: 'CmpCallCard_Izv1',
						// tabIndex: TABINDEX_PEF + 6,
						width: 350,
						xtype: 'textfield'
					}]
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding: 0.5em;',
				border: true,
				collapsible: true,
				id: 'CCCEF_SMPBrigadePanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						// this.FormPanel.getForm().findField('PrehospDirect_id').focus(true);
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['4_brigada_smp'],
				items: [{
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					title: lang['brigada'],
					xtype: 'fieldset',

					items: [{
						disabledClass: 'field-disabled',
						fieldLabel: lang['nomer_brigadyi'],
						name: 'CmpCallCard_Numb',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'numberfield',
						maskRe: /[0-9]/
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['kod_stantsii_smp_brigadyi'],
						name: 'CmpCallCard_Smpb',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'numberfield',
						maskRe: /[0-9]/
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['nomer_p_s_brigadyi_po_upravleniyu'],
						name: 'CmpCallCard_Stbr',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'numberfield',
						maskRe: /[0-9]/
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['nomer_p_s_bazirovaniya_brigadyi'],
						name: 'CmpCallCard_Stbb',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'numberfield',
						maskRe: /[0-9]/
					}, {
						comboSubject: 'CmpProfile',
						disabledClass: 'field-disabled',
						fieldLabel: lang['profil_brigadyi'],
						hiddenName: 'CmpProfile_bid',
						loadParams: {params: {where: getRegionNick().inlist([ 'krym' ]) ? ' where Region_id = ' + getRegionNumber() : ''}},
						moreFields: [
							{ name: 'Region_id', type: 'int' }
						],
						// tabIndex: TABINDEX_PEF + 5,
						width: 350,
						xtype: 'swcommonsprcombo'
					}, {
						allowDecimals: false,
						allowNegative: false,
						disabledClass: 'field-disabled',
						fieldLabel: lang['nomer_mashinyi'],
						name: 'CmpCallCard_Ncar',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'numberfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['kod_ratsii'],
						name: 'CmpCallCard_RCod',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield'
					}]
				}, {
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					title: lang['sotrudniki'],
					xtype: 'fieldset',

					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								disabledClass: 'field-disabled',
								fieldLabel: lang['starshiy_v_brigade_nomer'],
								name: 'CmpCallCard_TabN',
								// tabIndex: TABINDEX_PEF + 6,
								width: 100,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							labelWidth: 80,
							layout: 'form',
							items: [{
								disabledClass: 'field-disabled',
								fieldLabel: lang['familiya'],
								name: 'CmpCallCard_Dokt',
								// tabIndex: TABINDEX_PEF + 6,
								width: 350,
								xtype: 'textfield'
							}, {
								allowBlank: true,
								fieldLabel: lang['vrach'],
								hiddenName: 'MedStaffFact_id',
								lastQuery: '',
								listWidth: 650,
								anchor: '100%',
								xtype: 'swmedstafffactglobalcombo'
							}]
						}]
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['nomer_1-go_pomoschnika'],
						name: 'CmpCallCard_Tab2',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['nomer_2-go_pomoschnika'],
						name: 'CmpCallCard_Tab3',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['voditel'],
						name: 'CmpCallCard_Tab4',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield'
					}]
				}, {
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
					title: lang['kontrol'],
					xtype: 'fieldset',

					items: [{
						disabledClass: 'field-disabled',
						fieldLabel: lang['kak_poluchen'],
						name: 'CmpCallCard_Kakp',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'numberfield'
					}, {
						allowBlank: false,
						disabledClass: 'field-disabled',
						fieldLabel: lang['vremya_peredachi'],
						name: 'CmpCallCard_Tper',
						//onTriggerClick: Ext.emptyFn,
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_PEF + 6,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						allowBlank: false,
						disabledClass: 'field-disabled',
						fieldLabel: lang['vremya_vyiezda'],
						name: 'CmpCallCard_Vyez',
						//onTriggerClick: Ext.emptyFn,
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_PEF + 6,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						allowBlank: false,
						disabledClass: 'field-disabled',
						fieldLabel: lang['vremya_pribyitiya_na_adres'],
						name: 'CmpCallCard_Przd',
						//onTriggerClick: Ext.emptyFn,
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_PEF + 6,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['vremya_otzvona_o_gospitalizatsii'],
						name: 'CmpCallCard_Tgsp',
						//onTriggerClick: Ext.emptyFn,
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_PEF + 6,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['vremya_pribyitiya_v_statsionar'],
						name: 'CmpCallCard_Tsta',
						//onTriggerClick: Ext.emptyFn,
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_PEF + 6,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						allowBlank: false,
						disabledClass: 'field-disabled',
						fieldLabel: lang['vremya_ispolneniya_osvobojdeniya'],
						name: 'CmpCallCard_Tisp',
						//onTriggerClick: Ext.emptyFn,
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_PEF + 6,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['vremya_vozvrascheniya_na_stantsiyu'],
						name: 'CmpCallCard_Tvzv',
						//onTriggerClick: Ext.emptyFn,
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_PEF + 6,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						allowDecimals: true,
						allowNegative: false,
						disabledClass: 'field-disabled',
						fieldLabel: lang['kilometraj_zatrachennyiy_na_vyizov'],
						maxValue: 9999.99,
						name: 'CmpCallCard_Kilo',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'numberfield'
					},{
						allowBlank:false,
						hiddenName:'LpuSection_id',
						xtype: 'swlpusectioncombo'
					}]
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				border: true,
				collapsible: true,
				id: 'CCCEF_SMPUslugaPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						var grid = this.findById('CCCEF_CmpCallCardUslugaGrid').getGrid();
						var base_form = this.FormPanel.getForm();

						if (!this.UslugaGridLoaded) {
							this.UslugaGridLoaded = true;
							grid.getStore().load({
								params: {
									CmpCallCard_id: base_form.findField('CmpCallCard_id').getValue()
								}
							});
						}
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['5_uslugi'],
				items: [this.UslugaViewFrame]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				border: true,
				collapsible: true,
				layout: 'form',
				hidden: (getRegionNick() != 'ekb'),
				style: 'margin-bottom: 0.5em;',
				title: lang['6_standart_med_pomoshi'],
				items: [
					{
						name: 'EmergencyStandart',
						fieldLabel: 'Стандарт медицинской помощи',
						width: 200,
						xtype: 'textfield'
					}
				]
			}),
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				height: 100,
				id: 'CCCEF_CostPrintPanel',
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['6_spravka_o_stoimosti_lecheniya'],
				hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
				items: [{
					bodyStyle: 'padding-top: 0.5em;',
					border: false,
					height: 90,
					layout: 'form',
					region: 'center',
					items: [{
						fieldLabel: lang['data_vyidachi_spravki_otkaza'],
						tabIndex: this.tabindex + 51,
						width: 100,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'CmpCallCardCostPrint_setDT',
						xtype: 'swdatefield'
					},{
						fieldLabel: lang['otkaz'],
						tabIndex: this.tabindex + 52,
						hiddenName: 'CmpCallCardCostPrint_IsNoPrint',
						width: 60,
						xtype: 'swyesnocombo'
					}]
				}]
			})]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'CCCEF_PersonInformationFrame',
			region: 'north'
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
		
/*
		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				this.buttons[this.buttons.length - 1].focus();
			}.createDelegate(this),
			button2Callback: function(callback_data) {
				this.buttons[this.buttons.length - 1].focus();
			}.createDelegate(this),
			button2OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button3OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button4OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button5OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			collapsible: true,
			collapsed: true,
			floatable: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north',
			title: lang['zagruzka'],
			titleCollapse: true
		});
*/
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: Ext.emptyFn,
				onTabAction: Ext.emptyFn,
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
				onShiftTabAction: Ext.emptyFn,
				onTabAction: Ext.emptyFn,
				// tabIndex: TABINDEX_CCCEF + 16,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swCmpCallCardEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById(this.id + '_DiagCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.FormPanel.getForm();

			if (getRegionNick() == 'ekb') {
				if (base_form.findField('Diag_uid').getFieldValue('DiagFinance_IsRankin') && base_form.findField('Diag_uid').getFieldValue('DiagFinance_IsRankin') == 2) {
					base_form.findField('RankinScale_id').showContainer();
					base_form.findField('RankinScale_id').setAllowBlank(false);
				} else {
					base_form.findField('RankinScale_id').hideContainer();
					base_form.findField('RankinScale_id').clearValue();
					base_form.findField('RankinScale_id').setAllowBlank(true);
				}
			}
		}.createDelegate(this));

		this.findById(this.id + '_LeaveType_id').on('select', function (combo, record) {
			var base_form = this.FormPanel.getForm();
			sw.Promed.EvnPL.filterFedResultDeseaseType({
				fieldFedLeaveType: base_form.findField('LeaveType_id'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_id')
			});
		}.createDelegate(this));
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('CmpCallCardEditWindow');

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
	layout: 'border',
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
	
		//получение стандарта медицинской помощи
	checkEmergencyStandart: function(){
		var me = this,
			base_form = me.FormPanel.getForm(),
			emergencyStandartField = base_form.findField('EmergencyStandart'),
			personField = base_form.findField('Person_id'),
			diagField = base_form.findField('Diag_uid');
		
		if(!emergencyStandartField) return false;
		emergencyStandartField.reset();
		if(personField && personField.getValue() && diagField && diagField.getValue())
		{
			Ext.Ajax.request({
				params: {
					Diag_id: diagField.getValue(),
					Person_id: personField.getValue()
				},
				url: '/?c=CmpCallCard&m=checkEmergencyStandart',
				callback: function (obj, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						
						if(response_obj[0] && response_obj[0]["EmergencyStandart_Code"]){
							emergencyStandartField.setValue(response_obj[0]["EmergencyStandart_Code"]);
						}
					}
				}.createDelegate(this)
			});
		}
	},
	
	checkForCostPrintPanel: function() {
		var base_form = this.FormPanel.getForm();

		this.findById('CCCEF_CostPrintPanel').hide();
		base_form.findField('CmpCallCardCostPrint_setDT').setAllowBlank(true);
		base_form.findField('CmpCallCardCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		if (!Ext.isEmpty(base_form.findField('CmpCloseCard_id').getValue()) && !Ext.isEmpty(base_form.findField('CmpCallCardCostPrint_setDT').getValue()) && getRegionNick().inlist(['perm', 'kz', 'ufa'])) {
			this.findById('CCCEF_CostPrintPanel').show();
			// поля обязтаельные
			base_form.findField('CmpCallCardCostPrint_setDT').setAllowBlank(false);
			base_form.findField('CmpCallCardCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	setFieldsVisibilityByFormVersion: function(ShortEditVersion) {

		var base_form = this.FormPanel.getForm(),
			i;

		var isShortEditVersion = (ShortEditVersion == 2);
		//var isShortEditVersion = base_form.findField('CmpCallCard_isShortEditVersion').getValue() == 2;

		var setVisibleField = function( field_name , show ) {
			var field = base_form.findField( field_name );
			if ( !field ) {
				return;
			}
			field.setVisible( show );
			field.getEl().up('.x-form-item').setDisplayed( show );
		}
		
		var shortVersionVisibleFields = [
			'ShortEdit_CmpLpu_id',
			'CmpCallCard_Condition',
			'CmpCallCard_Recomendations',
			'LpuSection_id'
		];
		
		var shortVersionInvisiblePanels = [
			'CCCEF_PersonDataFieldset',
			'CCCEF_PersonIndentDataFieldset',
			'CCCEF_PersonAddressFieldset',
			'CCCEF_HostpitFieldset',
			'CCCEF_CallManagementPanel',
		];
		var shortVersionInvisibleFields = [
			'CmpDiag_oid',
			'Diag_sopid',
			'CmpCallCard_Stan',
			'CmpCallType_id',
			'CmpCallCard_Sect',
			'CmpCallCard_Prty',
			'CmpCallCard_Line',
			'Lpu_id',
			'CmpCallCard_Smpt',
			//'CmpCallCard_RepFlag',
			'CmpCallCard_Kakp',
			'CmpCallCard_Tab4',
			'CmpCallCard_Tab3',
			'CmpCallCard_Tab2',
			'CmpCallCard_Dokt',
			'CmpCallCard_TabN',
			'CmpCallCard_RCod',
			'CmpCallCard_Ncar',
			'CmpCallCard_Stbb',
			'CmpCallCard_Stbr',
			'CmpCallCard_Smpb',
			'CmpCallCard_Numb'
		];
		
		for (var i = 0; i < shortVersionVisibleFields.length; i++) {
			setVisibleField( shortVersionVisibleFields[i] , isShortEditVersion )
			var field = base_form.findField( shortVersionVisibleFields[i] );
			if ( field ) {
				field.setDisabled( !isShortEditVersion );
			}
		};
		
		for (var i = 0; i < shortVersionInvisibleFields.length; i++) {
			setVisibleField( shortVersionInvisibleFields[i] , !isShortEditVersion )
			var field = base_form.findField( shortVersionInvisibleFields[i] );
			if ( field ) {
				field.setDisabled( isShortEditVersion );
			}
		};
		
		for (var i = 0; i < shortVersionInvisiblePanels.length; i++) {
			var svip = Ext.getCmp( shortVersionInvisiblePanels[i] );
			if ( svip ) {
				svip.setVisible( !isShortEditVersion );
				svip.setDisabled( isShortEditVersion );
			}
		};
		
		
	},
	checkDuplicate: function(data, parent_object, check_dupl_params){
        var base_form = this.FormPanel.getForm();
        if(getGlobalOptions().region.nick == 'perm' && this.action == 'add'){
            Ext.Ajax.request({
                params: check_dupl_params,
                callback: function (opt, success, response) {
                    if (success) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);

                        if ( response_obj.data && response_obj.data.length > 0) {
                            sw.swMsg.alert(lang['oshibka'], lang['sohranenie_dublirueshey_karty_nevozmozno']);
                            this.formStatus = 'edit';
                        }
                        else {
                            parent_object.save_form(base_form, data);
                        }
                    }
                    else {
                        this.formStatus = 'edit';
                        sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_proverke_dublirovaniya_vyizova']);
                        parent_object.hide();
                    }
                }.createDelegate(this),
                url: '/?c=CmpCallCard&m=checkDuplicateCmpCallCard'
            });
        }else{
            this.save_form(base_form,data)
        }

	},
    save_form: function(base_form,params){
        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение карты вызова..."});
        loadMask.show();

		//проверка на суицид и добавление в регистр
		if(getRegionNick() == 'perm'){
			var Diag_sopid = base_form.findField('Diag_sopid');
			if(Diag_sopid.getValue() > 0){
				var rec = Diag_sopid.getStore().getById(Diag_sopid.getValue());
				if(rec){
					var diag_code = rec.get('Diag_Code').substr(0, 3);
					if((diag_code >= 'X60') && (diag_code <= 'X84')){

						Ext.Ajax.request({
							url: '/?c=PersonRegister&m=save',
							params: {
								PersonRegister_setDate:base_form.findField('CmpCallCard_prmDate').getRawValue(),
								Diag_id:Diag_sopid.getValue(),
								Person_id:base_form.findField('Person_id').getValue(),
								PersonRegisterType_SysNick:'suicide',
								PersonRegisterType_id:62, //суицид
								MorbusType_SysNick:'suicide',
								Lpu_iid:getGlobalOptions().lpu_id,
								MedPersonal_iid:getGlobalOptions().medpersonal_id
							},
							success: function(){
								sw.swMsg.alert('Информация', 'Пациент был включён в регистр лиц, совершивших суицидальные попытки');
							}
						})
					}
				}
			}
		}

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=saveCmpCallCard',
			failure: function (result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if (action.result) {
					if (action.result.Error_Msg) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function (response, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if (!Ext.isEmpty(response.responseText)) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success) {
						if (response_obj.CmpCallCard_id > 0) {
							base_form.findField('CmpCallCard_id').setValue(response_obj.CmpCallCard_id);

							var data = new Object();
							var index;
							var person_fio = '';
							var record;

							if (base_form.findField('Person_SurName').getValue()) {
								person_fio = person_fio + base_form.findField('Person_SurName').getValue();
							}

							if (base_form.findField('Person_FirName').getValue()) {
								person_fio = person_fio + ' ' + base_form.findField('Person_FirName').getValue();
							}

							if (base_form.findField('Person_SecName').getValue()) {
								person_fio = person_fio + ' ' + base_form.findField('Person_SecName').getValue();
							}

                        data.cmpCallCardData = {
                            'CmpCallCard_id': base_form.findField('CmpCallCard_id').getValue()
                            ,'accessType': 'edit'
                            ,'Person_id': base_form.findField('Person_id').getValue()
                            ,'MedPersonal_id': base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id')
                            ,'MedStaffFact_id': base_form.findField('MedStaffFact_id').getValue()
                            ,'CmpCloseCard_id': base_form.findField('CmpCloseCard_id').getValue()
                            ,'CmpCallCard_prmDate': base_form.findField('CmpCallCard_prmDate').getValue()
                            ,'CmpCallCard_prmTime': base_form.findField('CmpCallCard_prmTime').getValue()
                            ,'CmpCallCard_Numv': base_form.findField('CmpCallCard_Numv').getValue()
                            ,'Person_Surname': base_form.findField('Person_SurName').getValue()
                            ,'Person_Firname': base_form.findField('Person_FirName').getValue()
                            ,'Person_Secname': base_form.findField('Person_SecName').getValue()
                            ,'Person_Birthday': (typeof base_form.findField('Person_BirthDay').getValue() == 'object' ? base_form.findField('Person_BirthDay').getValue() : getValidDT(base_form.findField('Person_BirthDay').getValue(), ''))
                            ,'Person_IsIdentified': (!Ext.isEmpty(base_form.findField('Person_id').getValue()) && base_form.findField('Person_id').getValue() != '0' ? "true" : "false")
                            ,'CmpReason_Name': base_form.findField('CmpReason_id').getFieldValue('CmpReason_Name')
                            ,'CmpLpu_Name': base_form.findField('CmpLpu_id').getFieldValue('CmpLpu_Name')
                            ,'CmpDiag_Name': base_form.findField('CmpDiag_oid').getFieldValue('CmpDiag_Name')
                            ,'StacDiag_Name': base_form.findField('Diag_sid').getFieldValue('Diag_Name')
                            ,'Person_Address': ''
                            ,'Person_FIO': person_fio
                            ,'CmpCallCardCostPrint_setDT': base_form.findField('CmpCallCardCostPrint_setDT').getValue()
                            ,'CmpCallCardCostPrint_IsNoPrintText': base_form.findField('CmpCallCardCostPrint_IsNoPrint').getFieldValue('YesNo_Name')
                            ,'CmpCallCardInputType_id': params.CmpCallCardInputType_id
                        };

							this.callback(data);
							this.hide();
						}
						else {
							if (response_obj.Error_Msg) {
								sw.swMsg.alert(lang['oshibka'], response_obj.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
					}
				}
			}.createDelegate(this)
		});
    },
	show: function() {
		sw.Promed.swCmpCallCardEditWindow.superclass.show.apply(this, arguments);

		this.PersonInfo.hide();
		this.doLayout();
		
		this.findById('CCCEF_CallManagementPanel').expand();
		this.findById('CCCEF_CallPanel').expand();
		this.findById('CCCEF_PersonPanel').expand();
		this.findById('CCCEF_SMPBrigadePanel').expand();
		this.findById('CCCEF_CostPrintPanel').expand();
		
		this.findById('CCCEF_CmpCallCardUslugaGrid').getGrid().getStore().removeAll();
		
		this.restore();
		this.center();
		this.maximize();
		var form  = this.findById('CmpCallCardEditForm')
		/*form.getForm().findField('LeaveType_id').on('select', function (combo, record) {
                var base_form = form.getForm();
                sw.Promed.EvnPL.filterFedResultDeseaseType({
					fieldFedLeaveType: base_form.findField('LeaveType_id'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_id')
				});
            });*/
		/*form.getForm().findField('ResultDeseaseType_id').on('select', function (combo, record) {
			var base_form = form.getForm();
			sw.Promed.EvnPL.filterFedLeaveType({
				fieldFedLeaveType: base_form.findField('LeaveType_id'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_id')
			});
		});*/
			
		var base_form = this.FormPanel.getForm();
		
		base_form.findField('RankinScale_id').hideContainer();
		base_form.findField('RankinScale_id').clearValue();
		base_form.findField('RankinScale_id').setAllowBlank(true);
		
		base_form.findField('CmpPlace_id').fireEvent('change',base_form.findField('CmpPlace_id'), null);	
		
		base_form.reset();

		this.checkForCostPrintPanel();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.UslugaGridLoaded = false;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		var isShortEditVersion = arguments[0].formParams.CmpCallCard_isShortEditVersion;
		var PersonEvn_id = arguments[0].PersonEvn_id;
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
		base_form.findField('LeaveType_id').getStore().filterBy(function(rec) {
						return (rec.get('LeaveType_USLOV') == '4');
				});
		//base_form.findField('CmpCallCard_RepFlag').hideContainer();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		
		switch ( this.action ) {
			case 'add':
				if (!arguments[0].formParams || ( (arguments[0].formParams.CmpCallCard_isShortEditVersion == 2) && ( !arguments[0].formParams.Person_id || !arguments[0].formParams.Server_id) ) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['ne_zadan_identifikator_patsienta'], function() {this.hide();}.createDelegate(this) );
					return false;
				}
				this.enableEdit(true);
				this.findById('CCCEF_CmpCallCardUslugaGrid').setReadOnly(false);

				this.setFieldsVisibilityByFormVersion(isShortEditVersion);

				Ext.getCmp('CmpCallCard_RepFlagId').hide();

				if ( Number(base_form.findField('Person_id').getValue()) > 0 ) {
					this.PersonInfo.show();
					this.doLayout();

					this.PersonInfo.load({
						Person_id: base_form.findField('Person_id').getValue()
					});
				}

				//base_form.findField('Lpu_id').getStore().load();
				base_form.findField('Diag_uid').allowBlank = false;
				base_form.findField('CmpCallCard_prmDate').setValue(new Date());
				base_form.findField('CmpCallCard_prmDate').fireEvent('change', base_form.findField('CmpCallCard_prmDate'), base_form.findField('CmpCallCard_prmDate').getValue());

				base_form.findField('LpuSection_id').getStore().load({
					params: {
						Object: 'LpuSection',
						LpuSection_id: '',
						Lpu_id: getGlobalOptions().lpu_id,
						LpuUnit_id: '',
						LpuSection_Name: ''
					}
				});
				
				this.setTitle(getRegionNick().inlist(['perm','ekb','kareliya'])?WND_AMB_CCCEFADISADD:WND_AMB_CCCEFADD);
				loadMask.hide();
				
				base_form.isValid();
				break;
			case 'edit':
			case 'view':
				var cmp_call_card_id = base_form.findField('CmpCallCard_id').getValue();

				if ( !cmp_call_card_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}
				base_form.findField('Diag_uid').allowBlank = true;
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						CmpCallCard_id: cmp_call_card_id
					},
					success: function(form,resultData) {
						//Проверяем возможность редактирования документа
						if (this.action === 'edit') {
							Ext.Ajax.request({
								failure: function (response, options) {
									sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
										this.hide();
									}.createDelegate(this));
								},
								params: {
									Evn_id: cmp_call_card_id,
									isForm: 'CmpCallCardEditWindow',
									isCMPCloseCard: 2,
									MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
									ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
								},
								success: function (response, options) {
									if (!Ext.isEmpty(response.responseText)) {
										var response_obj = Ext.util.JSON.decode(response.responseText);

										if (response_obj.success == false) {
											sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_zagruzke_dannyih_formyi']);
											this.action = 'view';
										}
									}

									// продолжение show вынес в функцию onShow
									this.onShow(form, resultData, isShortEditVersion);
								}.createDelegate(this),
								url: '/?c=Evn&m=CommonChecksForEdit'
							});
						} else {
							this.onShow(form, resultData, isShortEditVersion);
						}
						loadMask.hide();
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
	onShow: function (form, resultData, isShortEditVersion){
		var base_form = this.FormPanel.getForm();
		var formData = resultData.result.data;

		base_form.findField('CmpCallPlaceType_id').setValue(formData.CmpCallPlaceType_id);

		base_form.findField('CmpCallCard_isShortEditVersion').setValue(isShortEditVersion);

		if ( base_form.findField('accessType').getValue() == 'view' ) {
			this.action = 'view';
		}

		this.checkForCostPrintPanel();

		if ( Number(base_form.findField('Person_id').getValue()) > 0 ) {
			this.PersonInfo.show();
			this.doLayout();

			this.PersonInfo.load({
				Person_id: base_form.findField('Person_id').getValue()
			});
		}

		if ( this.action == 'edit' ) {
			this.setTitle(getGlobalOptions().region.nick.inlist(['perm','ekb','kareliya'])?WND_AMB_CCCEFADISEDIT:WND_AMB_CCCEFEDIT);
			this.enableEdit(true);

			this.findById('CCCEF_PersonChangeButton').show();
			this.findById('CCCEF_CmpCallCardUslugaGrid').setReadOnly(false);
		}
		else {
			this.setTitle(getGlobalOptions().region.nick.inlist(['perm','ekb','kareliya'])?WND_AMB_CCCEFADISVIEW:WND_AMB_CCCEFVIEW);
			this.enableEdit(false);

			this.findById('CCCEF_PersonChangeButton').hide();
			this.findById('CCCEF_CmpCallCardUslugaGrid').setReadOnly(true);
		}

		this.setFieldsVisibilityByFormVersion(isShortEditVersion);

		this.findById('CCCEF_SMPUslugaPanel').fireEvent('expand', this.findById('CCCEF_SMPUslugaPanel'));

		var diag_sid = base_form.findField('Diag_sid').getValue();
		var diag_uid = base_form.findField('Diag_uid').getValue();
		var diag_sopid = base_form.findField('Diag_sopid').getValue();
		//var lpu_id = base_form.findField('Lpu_id').getValue();
		var index;
		var person_id = base_form.findField('Person_id').getValue();
		var record;

		if ( getRegionNick() == 'perm' && base_form.findField('CmpCallCard_IsPaid').getValue() == 2 && parseInt(base_form.findField('CmpCallCard_IndexRepInReg').getValue()) > 0 ) {
			Ext.getCmp('CmpCallCard_RepFlagId').show();

			if ( parseInt(base_form.findField('CmpCallCard_IndexRep').getValue()) >= parseInt(base_form.findField('CmpCallCard_IndexRepInReg').getValue()) ) {
				base_form.findField('CmpCallCard_RepFlag').setValue(true);
			}
			else {
				base_form.findField('CmpCallCard_RepFlag').setValue(false);
			}
		}
		else {
			Ext.getCmp('CmpCallCard_RepFlagId').hide();
		}

		base_form.findField('CmpCallCard_prmDate').fireEvent('change', base_form.findField('CmpCallCard_prmDate'), base_form.findField('CmpCallCard_prmDate').getValue());
		/*
		base_form.findField('Lpu_id').getStore().load({
			callback: function() {
				if ( lpu_id ) {
					base_form.findField('Lpu_id').setValue(lpu_id);
				}
				//base_form.findField('Lpu_id').fireEvent('select', base_form.findField('Lpu_id'), base_form.findField('Lpu_id').getStore().getAt(0), 0);
			},
			params: {
				Lpu_id: lpu_id
			}
		});
		*/
	   
	   	base_form.findField('LpuBuilding_id').getStore().load({
			callback: function() {								
				base_form.findField('LpuBuilding_id').setValue( base_form.findField('LpuBuilding_id').getValue());
			}							
		});
		
		if ( diag_sid ) {
			base_form.findField('Diag_sid').getStore().load({
				callback: function() {
					base_form.findField('Diag_sid').fireEvent('select', base_form.findField('Diag_sid'), base_form.findField('Diag_sid').getStore().getAt(0), 0);
				},
				params: {
				 	where: "where Diag_id = " + diag_sid
				}
			});
		}

		if ( diag_sopid ) {
			base_form.findField('Diag_sopid').getStore().load({
				callback: function() {
					base_form.findField('Diag_sopid').fireEvent('select', base_form.findField('Diag_sopid'), base_form.findField('Diag_sopid').getStore().getAt(0), 0);
				},
				params: {
				 	where: "where Diag_id = " + diag_sopid
				}
			});
		}

		if ( diag_uid ) {
			base_form.findField('Diag_uid').getStore().load({
				callback: function() {
					base_form.findField('Diag_uid').getStore().each(function (rec) {
						if (rec.get('Diag_id') == diag_uid) {
							base_form.findField('Diag_uid').setValue(diag_uid);
							base_form.findField('Diag_uid').fireEvent('select', base_form.findField('Diag_uid'), rec, 0);
							base_form.findField('Diag_uid').fireEvent('change', base_form.findField('Diag_uid'), base_form.findField('Diag_uid').getValue());
						}
					});
				},
				params: {
					where: "where Diag_id = " + diag_uid
				}
			});
		}
		
		

		base_form.findField('LpuSection_id').getStore().load({
			params: {
				Object: 'LpuSection',
				LpuSection_id: '',
				Lpu_id: getGlobalOptions().lpu_id,
				LpuUnit_id: '',
				LpuSection_Name: ''
			}, callback: function(){
				base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getValue());
			}
		});

		if (getRegionNick() == 'ekb') {
			var usluga_complex_combo = base_form.findField('UslugaComplex_id');

			usluga_complex_combo.lastQuery = undefined;
			usluga_complex_combo.getStore().baseParams.UslugaComplexPartition_CodeList = '[400]';
			if (Number(person_id) > 0) {
				usluga_complex_combo.getStore().baseParams.Person_id = person_id;
			}
			if ( usluga_complex_combo.getValue() > 0 ) {
				usluga_complex_combo.getStore().load({
					params: {UslugaComplex_id: usluga_complex_combo.getValue()},
					callback: function() {
						usluga_complex_combo.setValue(usluga_complex_combo.getValue());
					}
				});
			}
		}

		if ( this.action == 'edit' && Number(person_id) > 0 ) {
			base_form.findField('Person_Age').disable();
			base_form.findField('Person_FirName').disable();
			base_form.findField('Person_SecName').disable();
			base_form.findField('Person_SurName').disable();
			base_form.findField('Sex_id').disable();
		}
		
		base_form.findField('PersonIdent_Age').disable();
		base_form.findField('PersonIdent_Firname').disable();
		base_form.findField('PersonIdent_Secname').disable();
		base_form.findField('PersonIdent_Surname').disable();
		base_form.findField('SexIdent_id').disable();
		base_form.findField('PolisIdent_Num').disable();
		
		if (base_form.findField('CmpCallCard_isShortEditVersion').getValue() == 2) {
			base_form.findField('ShortEdit_CmpLpu_id').setValue(base_form.findField('CmpLpu_id').getValue());
		}
		
		base_form.findField('CmpPlace_id').fireEvent('change',base_form.findField('CmpPlace_id'), base_form.findField('CmpPlace_id').getValue());	
		
		// loadMask.hide();

		base_form.clearInvalid();
		base_form.isValid();

		if ( this.action == 'edit' ) {
			if ( !base_form.findField('Person_SurName').disabled ) {
				base_form.findField('Person_SurName').focus(true, 250);
			}
			else {
				base_form.findField('CmpArea_pid').focus(true, 250);
			}
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	}
});