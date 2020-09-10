/**
 * Контроллер формы EvnStickEditWindow
 * импорт методов из формы ext2 
 * версия импорта: редакция 130912 (ext2)
 *
*/
Ext6.define('common.Stick.EvnStickEditWindowController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnStickEditWindowController',        
	logging: true,//нужно ли всегда держать включенное логирование, как в ext2?
	logstack: [],
	log: function(msg) {
		if(!this.logging) return;
		if(!msg && arguments && arguments.callee && arguments.callee.caller && arguments.callee.caller.$name) msg = arguments.callee.caller.$name;
		if(msg) {
			console.log(msg); this.logstack.push(msg);
		}
	},
	swGenTempId: function(store, idProperty) {
		var tmpId = 0;
		do {
			tmpId = Math.floor(Math.random() * 1000000);
		} while (store.find(idProperty, tmpId)>=0);
		return tmpId; 
	},
	//повторяющаяся в каждом методе инициализация стандартных переменных и логирование
	//выполняется как eval(this.ini)
	ini: 'this.log(); var _this = this, me = this.getView(), win = me, vm = this.getViewModel(), base_form = me.FormPanel.getForm();',
	suspendChange: function(suspend) {
		eval(this.ini);
		var fields = win.query('field', win),
			i = 0;
		for (; i < fields.length; i++) {
			//можно было так, но ведь нужно чтобы и viewmodel обновлялось
			//~ if(suspend==0) fields[i].suspendCheckChange=0;
			//~ else if(fields[i].suspendCheckChange+suspend>=0) fields[i].suspendCheckChange+=suspend;
			
			//...в результате самодельный аналог suspendCheckChange:
			if(Ext6.isEmpty(fields[i].stop)) fields[i].stop = 0;
			if(suspend==0) fields[i].stop=0;
			else fields[i].stop+=suspend;
		}
	},
	updateInvalidValue: function(field) {///чтобы в vm было актуальное значение, иначе с !allowblank пустое значение не запишется.
		//field.name должно совпадать с bind.value
		this.getViewModel().set(field.name, field.getValue());
	},
	onEnableEdit: function(enable) {///заменяет собой базовые enableEdit() и onEnableEdit()
		eval(this.ini);
		vm.set('readOnly', enable == false);//для некоторых полей disable => hide

		if(vm.get('action') != 'view') {
			this.refreshFormPartsAccess();
		}
		this.checkSaveButtonEnabled();

		if (getRegionNick() != 'kz'){
			this.checkGetEvnStickNumButton();
		}
	},
	beforeHide: function() {
		eval(this.ini);
		var RegistryESStorage_id = base_form.findField('RegistryESStorage_id').getValue();
		var EvnStick_id = base_form.findField('EvnStick_id').getValue();

		if (
			getRegionNick() != 'kz' &&
			vm.get('action') == 'add' &&
			(
				Ext6.isEmpty(EvnStick_id) ||
				EvnStick_id == "0"
			) &&
			! Ext6.isEmpty(RegistryESStorage_id)
		) {
			// разбронировать номер.
			this._doUnbookEvnStickNum(RegistryESStorage_id)
		}
	},
	_doUnbookEvnStickNum: function(RegistryESStorage_id){/// разбронировать номер.
		Ext6.Ajax.request({
			url: '/?c=RegistryESStorage&m=unbookEvnStickNum',
			params: {
				RegistryESStorage_id: RegistryESStorage_id
			}
		});
	},
	_checkSnils: function() {
		eval(this.ini);
		if( !Ext6.isEmpty(win.Person_Snils) ) {
			base_form.findField('PersonSnils').setValue(win.Person_Snils);
			win.queryById('setSnilsButton').hide();
		} else if( 
			!Ext6.isEmpty(base_form.findField('Person_id').getValue()) 
			&& base_form.findField('Person_id').getValue() != 0 
		) {
			base_form.findField('PersonSnils').setValue('Не указан СНИЛС');
			win.queryById('setSnilsButton').show();
		} else {
			base_form.findField('PersonSnils').setValue('')
			win.queryById('setSnilsButton').hide();
		}
	},
	setSnilsButtonOnClick: function() {
		eval(this.ini);
		getWnd('swPersonEditWindow').show({
			Person_id: base_form.findField('Person_id').getValue(),
			focused: 'Person_SNILS',
			callback: function(result) {
				if(!Ext6.isEmpty(result.PersonData.Person_Snils)) {
					win.Person_Snils = result.PersonData.Person_Snils.replace(/-/g,'');
				} else {
					win.Person_Snils = null;
				}
				_this._checkSnils();
			}
		});
	},
	checkGetEvnStickNumButton: function() { ///все кроме checkConsentInAnotherLvn переведено на bind
		//~ eval(this.ini);
		// вторая проверка, первая при вызове (желательно эту проверку убрать, пока оставил на всякий)
		if (getRegionNick() != 'kz'){
			this.checkConsentInAnotherLvn();
			//ext6: остальное убрано в bind
		}

		return true;
	},
	checkStatus_EvnStick: function(){/// Статус ЛВН (не учитываем т.к. для открытия доступа статус может быть любым)
		// base_form.findField('StickLeaveType_id') - любое значение подходит
		return true;
	},

	_applyParams: function(args){///
		eval(this.ini);

		me.params = args;
		me.advanceParams = args;

		if(args.formParams.StickReg) {
			me.StickReg = args.formParams.StickReg;
		}
		if(args.formParams.CurLpuSection_id) {
			me.CurLpuSection_id = args.formParams.CurLpuSection_id;
		}
		if(args.formParams.CurLpuUnit_id) {
			me.CurLpuUnit_id = args.formParams.CurLpuUnit_id;
		}
		if(args.formParams.CurLpuBuilding_id) {
			me.CurLpuBuilding_id = args.formParams.CurLpuBuilding_id;
		}

		if(args.formParams.IngoreMSFFilter) {
			me.IngoreMSFFilter = args.formParams.IngoreMSFFilter;
		}

		if ( ! me.advanceParams.stacBegDate){
			me.advanceParams.stacBegDate = null;
		}
		if ( ! me.advanceParams.stacEndDate){
			me.advanceParams.stacEndDate = null;
		}

		if ( args.action && typeof args.action == 'string' ) {
			vm.set('action', args.action);
		}

		if ( args.callback && typeof args.callback == 'function' ) {
			me.callback = args.callback;
		}

		if ( args.evnStickType ) {
			me.evnStickType = args.evnStickType;
		}

		if ( args.JobOrg_id ) {
			me.JobOrg_id = args.JobOrg_id;
		}

		if ( args.link ) {
			vm.set('link', args.link);
		}

		if ( !Ext6.isEmpty(args.onHide) && typeof args.onHide == 'function' ) {
			me.onHideFn = args.onHide;
		}

		if ( args.parentClass ){
			me.parentClass = args.parentClass;
		}

		if ( args.parentNum ){
			me.parentNum = args.parentNum;
		}

		if ( args.Person_Post ) {
			me.Person_Post = args.Person_Post;
		}

		if ( args.isTubDiag ) {
			me.isTubDiag = true;
		}

		if (args.formParams.EvnStick_id){
			me.EvnStick_id = args.formParams.EvnStick_id;
		}

		if (args.formParams.EvnStick_mid){
			me.EvnStick_mid = args.formParams.EvnStick_mid;
		}

		if (args.formParams.EvnStick_pid){
			me.EvnStick_pid = args.formParams.EvnStick_pid;
		}



		if ( args.UserMedStaffFact_id ) {
			me.userMedStaffFactId = args.UserMedStaffFact_id;
		}

		if(args.fromList) {
			me.fromList = true;
		}

		return me;
	},

	_toggleFormElements: function(){///
		eval(this.ini);	

		if(me.queryById('EStEF_EvnStickCarePersonPanel').refreshTitle())
			me.queryById('EStEF_EvnStickCarePersonPanel').expand();
		if(me.queryById('EStEF_EvnStickWorkReleasePanel').refreshTitle())
			me.queryById('EStEF_EvnStickWorkReleasePanel').expand();
		me.queryById('EStEF_MSEPanel').collapse();
		me.queryById('EStEF_StickLeavePanel').collapse();
		me.queryById('EStEF_StickRegimePanel').collapse();

		me.queryById('swSignStickLeave').hide();
		//~ me.queryById('swSignStickLeaveList').hide();//ибо bind readonly
		//~ me.queryById('swSignStickIrrList').hide();//--//--
		//~ me.queryById('swSignStickIrrCheck').hide();//--//--
		
		me.queryById('swSignStickLeaveCheck').hide();
		me.queryById('swSignStickIrr').hide();
		
		me.queryById('EStEF_EvnStickCarePersonPanel').isLoaded = false;
		me.queryById('EStEF_EvnStickWorkReleasePanel').isLoaded = false;

		me.queryById('EStEF_EvnStickCarePersonGrid').getStore().removeAll();
		me.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().removeAll();

		me.queryById('SLeaveStatus_Name').setText('');
		me.queryById('SIrrStatus_Name').setText('');

		if ( me.parentClass == 'EvnPS' ) {
			me.queryById('EStEF_StickRegimePanel').expand();
		}

		if(vm.get('action') == 'add'){
			me.queryById('EStEF_EvnStickCarePersonPanel').isLoaded = true;
			me.queryById('EStEF_EvnStickWorkReleasePanel').isLoaded = true;
		}

		me.queryById('EvnStickLast_id').getStore().removeAll();
		me.queryById('EvnStick_oid').getStore().removeAll();
		return me;
	},
	
	onSelect_StickLeaveType_id: function(combo, record){
		eval(this.ini);
		this.resetSignStatus();
		if ( !record || !record.get('StickLeaveType_id') ) {
			base_form.findField('EvnStick_disDate').setContainerVisible(false);
			base_form.findField('EvnStick_disDate').setAllowBlank(true);
			base_form.findField('EvnStick_disDate').setRawValue('');
			base_form.findField('Lpu_oid').reset();
			base_form.findField('Lpu_oid').setContainerVisible(false);
			base_form.findField('EvnStick_NumNext').setContainerVisible(false);
			base_form.findField('MedStaffFact_id').reset();
			base_form.findField('MedStaffFact_id').setAllowBlank(true);
			base_form.findField('MedStaffFact_id').setContainerVisible(false);
			if (!getRegionNick().inlist(['kz','astra'])) {
				base_form.findField('EvnStick_sstNum').setAllowBlank(true);
			}
			win.queryById('SLeaveStatus_Name').getEl().dom.innerHTML = '';
			//~ this.queryById('SLeaveStatus_Name').render();//зачем?
			win.queryById('swSignStickLeave').hide();
			return false;
		}

		var stick_cause_sys_nick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');

		if (getRegionNick()!='kz') {
			base_form.findField('InvalidGroupType_id').setContainerVisible(true);
			base_form.findField('EvnStick_IsDisability').setContainerVisible(false);
		} else {
			base_form.findField('InvalidGroupType_id').setContainerVisible(false);
			base_form.findField('EvnStick_IsDisability').setContainerVisible(true);
		}

		if (!getRegionNick().inlist(['kz', 'astra']) && base_form.findField('EvnStick_sstNum').isVisible()) {
			base_form.findField('EvnStick_sstNum').setAllowBlank(false);
		}

		base_form.findField('EvnStick_disDate').setContainerVisible(true);
		base_form.findField('EvnStick_disDate').setAllowBlank(false);
		base_form.findField('MedStaffFact_id').setContainerVisible(true);
		base_form.findField('MedStaffFact_id').setAllowBlank(false);

		this.checkLastEvnStickWorkRelease();
		this.loadMedStaffFactList();

		if ( record.get('StickLeaveType_Code').inlist([ '31', '32', '33', '37' ]) ) {
			base_form.findField('Lpu_oid').setContainerVisible(true);
		}
		else {
			base_form.findField('Lpu_oid').reset();
			base_form.findField('Lpu_oid').setContainerVisible(false);
		}

		if ( record.get('StickLeaveType_Code').inlist([ '31', '37' ]) ) {
			base_form.findField('EvnStick_NumNext').setContainerVisible(true);
		}
		else {
			base_form.findField('EvnStick_NumNext').setContainerVisible(false);
		}

		this.setEvnStickDisDate();

		var EvnStick_stacBegDate = base_form.findField('EvnStick_stacBegDate').getValue();
		if( ! Ext6.isEmpty(EvnStick_stacBegDate)){
			base_form.findField('EvnStick_stacEndDate').setAllowBlank(false);
		}


		if (vm.get('action') != 'view') {
			win.queryById('swSignStickLeave').show();
		}
	},
	
	_setDefaultValueTo_EvnStick_stacBegDate: function(){/// Значение по умолчанию при создании ЛВН
		eval(this.ini);

		if(me.parentClass == 'EvnPL'){
			return false;
		}

		var EvnSectionDates = this.getBegEndDatesInStac();

		if(
			! Ext6.isEmpty(EvnSectionDates) &&
			! Ext6.isEmpty(EvnSectionDates.EvnSection_setDate) &&
			this._checkAccessToField_EvnStick_stacBegDate() == true
		){
			me.FormPanel.getForm().findField('EvnStick_stacBegDate').setValue(EvnSectionDates.EvnSection_setDate);
		}

		return true;
	},
	
	_setProcessValueTo_EvnStick_stacBegDate: function(){/// Обработка значения при редактировании или просмотре ЛВН
		eval(this.ini);

		var EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue();

		if (EvnSection_setDate.length > 0) {
			if(me.advanceParams.stacBegDate == undefined){
				me.advanceParams.stacBegDate = EvnSection_setDate;
			} else {

				if(Date.parseDate(EvnSection_setDate, 'd.m.Y') < Date.parseDate(me.advanceParams.stacBegDate, 'd.m.Y')){
					me.advanceParams.stacBegDate = EvnSection_setDate;
				}

				base_form.findField('EvnSection_setDate').setValue(me.advanceParams.stacBegDate);

			}
		}

		return me;
	},
	
	_setProcessValueTo_EvnStick_stacEndDate: function(){/// Обработка значения при редактировании или просмотре ЛВН при первоначальном открытии формы
		eval(this.ini);
		// -------------------------------------------------------------------------------------------------------------
		// ext2: проверить логичность всего блока для этой функции
		var EvnSection_disDate = base_form.findField('EvnSection_disDate').getValue();
		if (EvnSection_disDate.length > 0) {
			if(me.advanceParams.stacBegDate == undefined){
				me.advanceParams.stacEndDate = EvnSection_disDate;
			} else {

				if(EvnSection_disDate.length == 0 || me.advanceParams.stacEndDate == null){
					me.advanceParams.stacEndDate = null;
				}
				else if(Date.parseDate(EvnSection_disDate, 'd.m.Y') > Date.parseDate(me.advanceParams.stacEndDate, 'd.m.Y')){
					me.advanceParams.stacEndDate = EvnSection_disDate;
				}

				// этот код логически не подходит для этой функции
				base_form.findField('EvnSection_disDate').setValue(me.advanceParams.stacEndDate);
			}
		}
		// -------------------------------------------------------------------------------------------------------------

		var EvnStick_stacBegDate = base_form.findField('EvnStick_stacBegDate').getValue();
		if( ! Ext6.isEmpty(EvnStick_stacBegDate)){
			base_form.findField('EvnStick_stacEndDate').setAllowBlank(false);
		}

		return me;
	},

	loadField_Org_id: function(Org_id){///
		eval(this.ini);

		if(Ext6.isEmpty(Org_id) || Org_id == undefined){
			return me;
		}
		
		base_form.findField('Org_id').getStore().load({
			callback: function(records, options, success) {
				if ( success ) {
					base_form.findField('Org_id').setValue(Org_id);
					var orgNick = base_form.findField('Org_id').getFieldValue('Org_StickNick');
					/*if(!Ext6.isEmpty(orgNick)) base_form.findField('EvnStick_OrgNick').setValue(orgNick);
					*/
					
					if ( 
						Ext6.isEmpty(base_form.findField('EvnStick_OrgNick').getValue())
						&& orgNick
					) {
						base_form.findField('EvnStick_OrgNick').setValue(orgNick);
					} else if( Ext6.isEmpty(base_form.findField('EvnStick_OrgNick').getValue()) ) {
						base_form.findField('EvnStick_OrgNick').setValue(base_form.findField('Org_id').getRawValue());
					}
					
				}
			},
			params: {
				Org_id: Org_id,
				OrgType: 'org'
			}
		});

		return me;
	},
	_openAccessToPanelStickLeave: function(){///
		eval(this.ini);

		vm.set('isAccessToStickLeave', true);
		//~ me.getFields('EStEF_StickLeavePanel').forEach(this.enableField); // без enableField

		return true;
	},
	_closeAccessToPanelStickLeave: function(){///
		eval(this.ini);

		vm.set('isAccessToStickLeave', false);
		//~ me.getFields('EStEF_StickLeavePanel').forEach(this.disableField); // без disableField

		return true;
	},
	
	/*_openAccessToField_EStEF_btnSetMinDateFromPS: function(){ //нет кнопки для старого метода
		var me = this.getView(),
			vm = this.getViewModel();

		vm.set('isAccessToField_EStEF_btnSetMinDateFromPS', false);
		me.queryById('EStEF_btnSetMinDateFromPS').setVisible(true);

		return me;
	},
	_closeAccessToField_EStEF_btnSetMinDateFromPS: function(){ //нет кнопки для старого метода
		var me = this.getView(),
			vm = this.getViewModel();

		vm.set('isAccessToField_EStEF_btnSetMinDateFromPS', false);
		me.queryById('EStEF_btnSetMinDateFromPS').setVisible(false);

		return me;
	},*/
	
	doSign_StickRegime: function(options){///
		eval(this.ini);

		if (typeof options != 'object') {
			options = new Object();
		}

		if (!options.ignoreSave) {
			// предварительно всегда сохраняем весь ЛВН.
			options.ignoreSave = true;
			this.doSave({
				callback: function () {
					_this.doSign_StickRegime(options);
				}
			});
			return false;
		}

		var params = {};
		params.SignObject = 'irr';
		params.Evn_id = base_form.findField('EvnStick_id').getValue();
		params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();


		this._doSign(getOthersOptions().doc_signtype, params);
	},
	doSign_WorkRelease: function (options) {///
		eval(this.ini);
		if (typeof options != 'object') {
			options = new Object();
		}

		var selected_record = this._getSelected_WorkRelease();
		if( ! selected_record){
			return false;
		}

		if (!options.ignoreSave) {
			// предварительно всегда сохраняем весь ЛВН.
			options.ignoreSave = true;
			this.doSave({
				callback: function () {
					_this.doSign_WorkRelease(options);
				}
			});
			return false;
		}


		var params = {};
		params.SignObject = options.SignObject; // VK или MP
		params.Evn_id = selected_record.get('EvnStickWorkRelease_id');
		params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();


		this._doSign(getOthersOptions().doc_signtype, params);
	},
	doSign_StickLeave: function(options){///
		eval(this.ini);

		if (typeof options != 'object') {
			options = new Object();
		}

		if(getRegionNick() != 'kz'){
			if( ! options.ignoreStickLeaveType){
				// StickLeaveType_id 31 or 37
				var StickLeaveType_Code = base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code');
				var EvnStick_NumNext = base_form.findField('EvnStick_NumNext').getValue();

				// поле «Исход ЛВН» имеет значение «31» («Продолжает болеть») или «37» («Долечивание»)
				if(StickLeaveType_Code == 31 || StickLeaveType_Code == 37){
					// EvnStick_NumNext is empty
					if(Ext6.isEmpty(EvnStick_NumNext)){
						Ext6.Msg.show({
							buttons: Ext6.Msg.YESNO,
							fn: function(buttonId, text, obj) {

								// –	При выборе варианта «Да» выполняется подписание исхода ЛВН.
								if ( 'yes' == buttonId ) {

									options.ignoreStickLeaveType = true;
									_this.doSign_StickLeave(options);
								// –	При выборе варианта «Нет», сообщение закрывается. Подписание не производится.
								} else {
									// ничего не делаем
								}
							}.createDelegate(me),
							icon: Ext6.MessageBox.QUESTION,
							msg: langs('Для успешной сдачи в ФСС ЛВН с исходом «Продолжает болеть» и «Долечивание» рекомендуется подписывать после создания ЛВН-продолжения. Всё равно выполнить подписание?'),
							title: langs('Вопрос')
						});

						return false;
					}
				}
			}
		}

		if (!options.ignoreSave && vm.get('action') != 'view') {
			// предварительно всегда сохраняем весь ЛВН.
			options.ignoreSave = true;
			this.doSave({
				callback: function () {
					_this.doSign_StickLeave(options);
				}
			});
			return false;
		}

		var params = {};
		params.SignObject = 'leave';
		params.Evn_id = base_form.findField('EvnStick_id').getValue();
		params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();

		this._doSign(getOthersOptions().doc_signtype, params);

	},

	_getSslHash: function(data){
		eval(this.ini);

		var sshHashData = {
			xml: null,
			Base64ToSign: null,
			Hash: null
		};

		me.mask(langs('Получение данных для подписи ЛВН'));

		$.ajax({
			type: "POST",
			url: '/?c=Stick&m=getWorkReleaseSslHash',
			data: data,
			async: false,
			success: function(response){
				var result = Ext6.util.JSON.decode(response);

				if ( ! result.success) {
					Ext6.Msg.alert(langs('Ошибка'), result.Error_Msg);
				}

				if (result.xml) {

					sshHashData.xml = result.xml;
					sshHashData.Base64ToSign = result.Base64ToSign;
					sshHashData.Hash = result.Hash;
				}

			}
		});

		me.unmask();

		return sshHashData;
	},
	_successSign: function(params){
		eval(this.ini);

		// если подписывали освобождение от работы
		if ( ! params.SignObject.inlist(['leave', 'irr'])) {
			_this._reload_WorkRelease();
		}
		else {
			// Если подписывали исход или режим
			_this.getEvnStickSignStatus({object: params.SignObject});
		}

		return me;
	},
	_sign: function(data){
		eval(this.ini);
		var isSign = false;

		me.mask('Подписание');

		$.ajax({
			type: "POST",
			url: '/?c=Stick&m=signWorkRelease',
			data: data,
			async: false,
			success: function(response){
				var result = Ext6.util.JSON.decode(response);

				if (result.success){
					isSign = true;
				} else {
					Ext6.Msg.alert(langs('Ошибка'), result.Error_Msg);
				}
			}
		});

		me.unmask();

		return isSign;

	},
	_doSign: function(signType, params){/// запускаем процесс подписи
		eval(this.ini);
		var isSign = false;
		// выбираем сертификат
		getWnd('swCertSelectWindow').show({
			signType: signType,
			callback: function (cert) {
				params.SignedToken = cert.Cert_Base64;

				if (signType && signType.inlist(['authapplet', 'authapi', 'authapitomee'])) {
					params.needHash = 1;
				}

				var sshHashData = _this._getSslHash(params);

				if (sshHashData.xml) {

					switch(signType){
						case 'authapplet':
							sw.Applets.AuthApplet.signText({
								text: sshHashData.Base64ToSign,
								Cert_Thumbprint: cert.Cert_Thumbprint,
								callback: function(sSignedData){
									params.signType = signType;
									params.Hash = sshHashData.Hash;
									params.SignedData = sSignedData;
									params.xml = sshHashData.xml;

									isSign = _this._sign(params);

									if(isSign){
										_this._successSign(params);
									}
								}
							});
							break;
						case 'authapi':
						case 'authapitomee':
							sw.Applets.AuthApi.signText({
								win: me,
								text: sshHashData.Base64ToSign,
								Cert_Thumbprint: cert.Cert_Thumbprint,
								callback: function(sSignedData){
									params.signType = signType;
									params.Hash = sshHashData.Hash;
									params.SignedData = sSignedData;
									params.xml = sshHashData.xml;

									isSign = _this._sign(params);

									if(isSign){
										_this._successSign(params);
									}
								}
							});
							break;
						default:
							sw.Applets.CryptoPro.signXML({
								xml: sshHashData.xml,
								Cert_Thumbprint: cert.Cert_Thumbprint,
								callback: function(sSignedData){
									params.signType = 'cryptopro';
									params.xml = sSignedData;

									isSign = _this._sign(params);

									if(isSign){
										_this._successSign(params);
									}
								}
							});
							break;
					}

				}

			}
		});
	},
		
	_checkAccessToField_EvnStick_stacEndDate: function(){///
		//сразу устанавливает нужный isAccessToField_EvnStick_stacEndDate
		//поэтому метод _openAccessToField_EvnStick_stacEndDate не нужен
		eval(this.ini);
		var isOpen = false;

		// Оператор
		if(this.isOperator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				var checkUslovie3_1 = false;
				var checkUslovie3_2 = false;

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3_1 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3_1 = true;
						}
					}

				}

				// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
				// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
				// «дата окончания» не пустые.
				if(vm.get('action') == 'edit'){

					var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
					var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

					if( ! Ext6.isEmpty(stacBegDate) || ! Ext6.isEmpty(stacEndDate)){
						checkUslovie3_2 = true;
					}
				}


				if(checkUslovie3_1 == true || checkUslovie3_2 == true){
					checkUslovie3 = true;
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true  && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Статистик
		if(this.isStatistick() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				var checkUslovie3_1 = false;
				var checkUslovie3_2 = false;

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3_1 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3_1 = true;
						}
					}

				}

				// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
				// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
				// «дата окончания» не пустые.
				if(vm.get('action') == 'edit'){

					var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
					var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

					if( ! Ext6.isEmpty(stacBegDate) || ! Ext6.isEmpty(stacEndDate)){
						checkUslovie3_2 = true;
					}
				}

				if(checkUslovie3_1 == true || checkUslovie3_2 == true){
					checkUslovie3 = true;
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true  && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Регистратор ЛВН
		if(this.isRegistratorLVN() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС
				
				var checkUslovie3_1 = false;
				var checkUslovie3_2 = false;

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3_1 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3_1 = true;
						}
					}
				}
				
				// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
				// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
				// «дата окончания» не пустые.
				if(vm.get('action') == 'edit'){

					var stacBegDate = base_form.findField('EvnStick_stacBegDate').getValue();
					var stacEndDate = base_form.findField('EvnStick_stacEndDate').getValue();

					if( ! Ext6.isEmpty(stacBegDate) || ! Ext6.isEmpty(stacEndDate)){
						checkUslovie3_2 = true;
					}
				}


				if(checkUslovie3_1 == true || checkUslovie3_2 == true){
					checkUslovie3 = true;
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Регистратор
		if(this.isRegistrator() == true){
			//isOpen = false;
		}

		// Врач
		if(this.isVrach() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3 = true;
						}
					}
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}

		}

		// Врач, регистратор (одновременно)
		if(this.isVrachAndRegistrator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3 = true;
						}
					}
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		vm.set('isAccessToField_EvnStick_stacEndDate', isOpen);//ext6
		return isOpen;
	},

	
	doPrintEvnStick: function() {///Печать ЛВН
		eval(this.ini);
		var params = new Object(),
			form = me.FormPanel.getForm();

		params.EvnStick_id = form.findField('EvnStick_id').getValue();
		params.evnStickType = me.evnStickType;
		params.StickLeaveType_id = form.findField('StickLeaveType_id').getValue();
		params.StickOrder_id = form.findField('StickOrder_id').getRawValue();
		params.StickCause_SysNick = form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
		params.PridStickLeaveType_Code = this.getPridStickLeaveTypeCode();
		params.RegistryESStorage_id = form.findField('RegistryESStorage_id').getValue();
		params.firstEndDate = null;

		//Берём дату окончания из первого периода нетрудоспособности, заведённого орагнизацией, указанной в поле Санаторий
		if (form.findField('Org_did').getValue() == getGlobalOptions().org_id) {
			me.queryById(me.id+'EStEF_EvnStickWorkReleaseGrid').getStore().each(function(rec){
				if (Ext6.isEmpty(params.firstEndDate) || params.firstEndDate > rec.get('EvnStickWorkRelease_endDate')){
					params.firstEndDate = rec.get('EvnStickWorkRelease_endDate');
				}
			});
		}
		
		getWnd('swEvnStickPrintWindow').show(params);
	},
	
	doPrintTruncELN: function(){
		eval(this.ini);
		var EvnStick_id = base_form.findField('EvnStick_id').getValue();
		var Report_Params = '&paramEvnStick=' + EvnStick_id;
		printBirt({
			'Report_FileName': 'ELN_EvnStickPrint_short.rptdesign',
			'Report_Params': Report_Params,
			'Report_Format': 'pdf'
		});
	},

	CheckWorkRelease: function() {/// https://redmine.swan.perm.ru/issues/83780
		eval(this.ini);
		var EvnStick_id = base_form.findField('EvnStick_id').getValue();
		if(getRegionNick() == 'ekb'){
			Ext6.Ajax.request({
				url: '/?c=Stick&m=WorkReleaseMedStaffFactCheck',
				params: {
					EvnStickBase_id: EvnStick_id
				},
				callback: function(opt, success, response) {
					if (success && response.responseText.length > 0) {
						var result = Ext6.util.JSON.decode(response.responseText);
						if (!Ext6.isEmpty(result[0]) && !Ext6.isEmpty(result[0].EvnStickWorkRelease_id)) {
							var b_date = result[0].evnStickWorkRelease_begDT;
							var e_date = result[0].evnStickWorkRelease_endDT;
							var msg = langs('Продление и/или выдача ЛВН осуществлена через ВК. Укажите врача №2 в освобождении от работы От ') + b_date + " " + langs('До') + " " + e_date;
							Ext6.Msg.show(
								{
									buttons: Ext6.Msg.OK,
									fn: function()
									{
										return 1;
									},
									icon: Ext6.Msg.WARNING,
									msg: msg,
									title: langs('Ошибка')
								});
						}
						else
						{
							_this.doPrintEvnStick();
						}
					}
					else
					{
						_this.doPrintEvnStick();
					}
				}
			});
		}
		else
			_this.doPrintEvnStick();
	},

	checkIsLvnFromFSS: function(){/// Проверяем является ли ЛВН из ФСС (EvnStickBase_IsFSS)
		eval(this.ini);
		var isFromFSS = false;

		// Проверям по факту наличия даных!!!
		// EvnStickBase_IsFSS = true если есть данные (StickFSSData_id) !!!

		// Общие данные ЭЛН для отправки в ФСС
		var StickFSSData_id = me.FormPanel.getForm().findField('StickFSSData_id').getValue();

		// если есть данные, значит ЛВН из ФСС
		if ( ! Ext6.isEmpty(StickFSSData_id)){
			isFromFSS = true;
		}
		return isFromFSS;
	},

	checkIsLvnELN: function(){///
		eval(this.ini);
		var isELN = false;
		// Общие данные ЭЛН для отправки в ФСС
		var RegistryESStorage_id = me.FormPanel.getForm().findField('RegistryESStorage_id').getValue();
		// если есть данные, значит ЛВН из ФСС
		if ( ! Ext6.isEmpty(RegistryESStorage_id)){
			isELN = true;
		}
		return isELN;
	},
	
	checkRebUhod: function() {//
		eval(this.ini);
		var grid = win.queryById('EStEF_EvnStickCarePersonGrid');

		var care_person_index = grid.getStore().findBy(function(rec) { return rec.get('Person_id') == win.PersonInfo.getFieldValue('Person_id'); });
		var care_person_record = grid.getStore().getAt(care_person_index);
		var stick_cause = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
		var env_stick_set_date = base_form.findField('EvnStick_setDate').getValue();

		if (!care_person_record || !stick_cause || !stick_cause.inlist(['uhodnoreb','uhod','uhodreb','rebinv', 'zabrebmin'])) {
			win.queryById('openEvnStickWorkReleaseCalculationWindow').hide();
			return;
		}

		var params = {
			Person_id: care_person_record.get('Person_id'),
			PrivilegeType_id: 84,		//Дети-инвалиды
			Privilege_begDate: Ext6.util.Format.date(env_stick_set_date, 'd.m.Y')
		};

		Ext6.Ajax.request({
			url: '/?c=Privilege&m=checkPersonPrivilege',
			params: params,
			success: function(response) {
				var response_obj = Ext6.util.JSON.decode(response.responseText);

				if (response_obj.success) {
					var limit_age = response_obj.check ? 15 : 7;

					if (care_person_record.get('Person_Age') < limit_age) {
						win.queryById('openEvnStickWorkReleaseCalculationWindow').show();
					} else {
						win.queryById('openEvnStickWorkReleaseCalculationWindow').hide();
					}
				} else {
					win.queryById('openEvnStickWorkReleaseCalculationWindow').hide();
				}
			},
			failure: function() {
				win.queryById('openEvnStickWorkReleaseCalculationWindow').hide();
			}
		});
	},

	// -----------------------------------------------------------------------------------------------------------------
	// Роли пользователя
	// -----------------------------------------------------------------------------------------------------------------
	
	isOperator: function(){/// Оператор
		return isOperator();
	},

	
	isStatistick: function(){/// Статистик
		return isMedStatUser();
	},
	
	isRegistratorLVN: function(){/// Регистратор ЛВН
		return isRegLvn();
	},

	isRegistrator: function(){/// Регистратор
		return isPolkaRegistrator();
	},

	
	isVrach: function(){/// Врач
		return userIsDoctor() || isPolkaVrach() || isStacVrach() || isStacReceptionVrach();
	},

	isVrachVK: function(){/// Врач ВК
		return haveArmType('vk');
	},

	
	isPredsedatelVK: function(){/// Председатель ВК
		return haveArmType('vk');
	},

	
	isVrachAndRegistrator: function(){/// одновременно и Врач, и регистратор
		return this.isVrach() && this.isRegistrator();
	},
	//==================================================
	
	openEvnStickWorkReleaseCalculationWindow: function() {/// Дней нетрудоспособности в году
		eval(this.ini);

		var params = {
			Person_id: win.PersonInfo.getFieldValue('Person_id'),
			StickCause_id: base_form.findField('StickCause_id').getValue()
		};

		getWnd('swEvnStickWorkReleaseCalculationWindow').show(params);
	},
	
	fetchAndSetEvnStickProd: function(EvnStick_oid){// Получаем и устанавливаем значение для поля "ЛВН-продолжение"
		eval(this.ini);
		this.getEvnStickProdValues(EvnStick_oid, function(result) {
			var EvnStick_NumNext = base_form.findField('EvnStick_NumNext').getValue();

			if(Ext6.isEmpty(EvnStick_NumNext)){
				base_form.findField('EvnStick_NumNext').setValue(result.EvnStick_Title);
			}
		});
		/* //убрано,актуализировано ред.142420
		var EvnStick_prod = this.getEvnStickProdValues(EvnStick_oid);

		var EvnStick_NumNext = base_form.findField('EvnStick_NumNext').getValue();

		if(Ext6.isEmpty(EvnStick_NumNext)){
			base_form.findField('EvnStick_NumNext').setValue(EvnStick_prod.EvnStick_Title);
		}*/

		return true;
	},
	
	getEvnStickProdValues: function(EvnStick_id, callback){/// продолжение
		eval(this.ini);
		var result = null;

		$.ajax({
			method: "POST",
			url: '/?c=Stick&m=getEvnStickProdValues',
			data: {
				'EvnStick_id': EvnStick_id
			},
			async: false,
			success: function(response){
				result = Ext6.util.JSON.decode(response);
				callback(result);
			}
		});

		return result;
	},
	
/*	getEvnStickOriginInfo: function(EvnStick_id){/// Метод не используется
		eval(this.ini);
		var result = null;

		$.ajax({
			method: "POST",
			url: '/?c=Stick&m=getEvnStickOriginInfo',
			data: {
				'EvnStick_id': EvnStick_id
			},
			async: false,
			success: function(response){
				result = Ext6.util.JSON.decode(response);
			}
		});

		return result;
	},*/

/*	getEvnStickInfo: function(EvnStick_id){/// Метод не используется
		eval(this.ini);
		var result = null;

		$.ajax({
			method: "POST",
			url: '/?c=Stick&m=getEvnStickInfo',
			data: {
				'EvnStick_id': EvnStick_id
			},
			async: false,
			success: function(response){
				result = Ext6.util.JSON.decode(response);
			}
		});

		return result;
	},*/
	
	/*_closeAccessToField_EvnStick_stacEndDate: function(){/// поглощен _checkAccessToField_EvnStick_stacEndDate
		var me = this.getView(),
			vm = this.getViewModel();

		vm.set('isAccessToField_EvnStick_stacEndDate', false);
		me.FormPanel.getForm().findField('EvnStick_stacEndDate').disable();

		return me;
	},*/
	
//	getFields: function(component) {},///Метод не используется в ext6 ( =>bind )
	
	setStatus: function(status_id, elname) {
		eval(this.ini);
		var id = parseInt(status_id);
		var icon = me.queryById('S'+elname+'Status_Icon');
		var item = me.queryById('swSignStick'+elname);
		var list = me.queryById('swSignStick'+elname+'List');
		var check = me.queryById('swSignStick'+elname+'Check');
		var stat = '';
		var opacity = 1;

		switch(id) {
			case 0:
				icon.hide();
				item.hide();
				list.hide();
				check.hide();
				break;
			case 1:
			case 3:
				icon.show();
				item.hide();
				list.show();
				check.show();
				break;
			case 2:
			default:
				icon.show();
				item.show();
				list.hide();
				check.hide();
				break;
		}

		switch(id) {
			case 1: stat = 'lock'; break;
			case 2: stat = 'unlock'; opacity=0.6; break;
			case 3: stat = 'nonactual'; break;
		}
		if(opacity<1) {
			icon.addCls('emk-forms-element-light');
			me.queryById('S'+elname+'Status_Name').addCls('emk-forms-element-light');
		} else {
			icon.removeCls('emk-forms-element-light');
			me.queryById('S'+elname+'Status_Name').removeCls('emk-forms-element-light');
		}
		icon.tpl.overwrite(icon.body, {'status':stat});
	},
		
	getEvnStickSignStatus: function(options) {/// Получаем Статус подписи документы, отображаем его и устанавливаем Signatures_iid или Signatures_id
		eval(this.ini);
		var options = options || {};
		var params = {};
		var signobject = options.object || 'leave';
		var elname = options.object && options.object == 'irr' ? 'Irr' : 'Leave';
		_this.setStatus(0, elname);
		params.SignObject = signobject;
		params.EvnStick_id = base_form.findField('EvnStick_id').getValue();
		win.queryById('swSignStick'+elname+'List').hide();
		win.queryById('swSignStick'+elname+'Check').hide();
		Ext6.Ajax.request({
			url: '/?c=Stick&m=getEvnStickSignStatus',
			params: params,
			success: function(response, options) {
				var result = Ext6.util.JSON.decode(response.responseText);
				if (result.SStatus_id) {
					var w = win.queryById('S'+elname+'Status_Name');
					_this.setStatus(result.SStatus_id, elname);
					
					win.queryById('S'+elname+'Status_Name').getEl().dom.innerHTML = result.SStatus_Name;
					if (result.SStatus_id.inlist([1,3])) {
						if (signobject == 'irr') {
							vm.set('Signatures_iid', result.Signatures_id);
							
							win.signedRegime_MedPersonal_id = result.MedPersonal_id;
							_this.refreshFormPartsAccess();
						}
						else {
							vm.set('Signatures_id', result.Signatures_id);
						}
					}
				}
			}
		});

		return me;
	},
	
	_setDefaultValueTo_EvnStick_stacEndDate: function(){/// Значение по умолчанию при создании ЛВН
		eval(this.ini);

		if(me.parentClass == 'EvnPL'){
			return false;
		}

		var EvnSectionDates = this.getBegEndDatesInStac();

		if( ! Ext6.isEmpty(EvnSectionDates) && ! Ext6.isEmpty(EvnSectionDates.EvnSection_disDate) && this._checkAccessToField_EvnStick_stacEndDate() == true){
			base_form.findField('EvnStick_stacEndDate').setValue(EvnSectionDates.EvnSection_disDate);
		}

		return true;
	},
	
	

	findKVC: function(){
		// Поиск движений возможен:
		// 1. ЛВН заведён из КВС, тогда ЛВН по полю EvnStick_mid ссылается на EvnPS_id
		// 2. ЛВН заведён из ТАП, но связан с КВС, тогда ЛВН связан с КВС через EvnLink
		eval(this.ini);


		// if(me.EvnPS_id != null){ //закоммичено в ext2
		// 	return me.EvnPS_id;
		// }

		var parentClass = me.parentClass;

		// При добалении EvnStick_id равен 0
		if(vm.get('action') == 'add'){

			// ЛВН заведен из КВС
			if(parentClass == 'EvnPS'){
				me.EvnPS_id = me.EvnStick_mid;

				// ЛВН заведен из ТАП
			} else if(parentClass == 'EvnPL'){
				// никак не можем узнать КВС
			}
		}

		if(vm.get('action') == 'edit' || vm.get('action') == 'view'){

			// ЛВН заведен из КВС
			if(parentClass == 'EvnPS'){
				me.EvnPS_id = me.EvnStick_mid;

				// ЛВН заведен из ТАП
			} else if(parentClass == 'EvnPL'){
				// пробуем найти КВС через EvnLink

				$.ajax({
					method: "POST",
					url: '/?c=Stick&m=getEvnPSFromEvnLink',
					data: {
						'EvnStick_id': me.EvnStick_id
					},
					async: false,
					success: function(response){
						var result = Ext6.util.JSON.decode(response);
						if (result && result[0] && result[0]['EvnPS_Id']) {
							me.EvnPS_id = result[0]['EvnPS_id'];
						}
					}
				});

			}
		}

		return me.EvnPS_id;
	},
	
	checkHasDvijenia: function() {
		eval(this.ini);

		if (me.isHasDvijenia != null) {
			return me.isHasDvijenia;
		}

		var isHas = false;
		var EvnSectionList = this.getDvijeniaKVC();

		if (!Ext6.isEmpty(EvnSectionList) && EvnSectionList.length > 0) {
			isHas = true;
		}

		me.isHasDvijenia = isHas;

		return isHas;
	},

	checkHasDvijeniaInStac24: function() {
		eval(this.ini);

		if (vm.get('isHasDvijeniaInStac24') != null) {
			return vm.get('isHasDvijeniaInStac24');
		}

		var isHas = false;
		var EvnSectionList = this.getDvijeniaKVC();

		if (!Ext6.isEmpty(EvnSectionList) && EvnSectionList.length > 0) {
			for (var i = 0; i < EvnSectionList.length; i++) {
				if (EvnSectionList[i].LpuUnitType_SysNick == 'stac') {
					isHas = true;
				}
			}
		}

		vm.set('isHasDvijeniaInStac24', isHas);

		return isHas;
	},

	_closeAccessToField_StickLeave_Sign: function(){///
		eval(this.ini);

		vm.set('isAccessToField_StickLeave_Sign', false);
		me.queryById('swSignStickLeave').hide();

		return me;
	},
	_openAccessToField_StickLeave_Sign: function(){///
		eval(this.ini);

		vm.set('isAccessToField_StickLeave_Sign', true);
		me.queryById('swSignStickLeave').enable();

		return me;
	},

	_checkAccessToField_StickLeave_Sign: function(){///
		eval(this.ini);
		var isOpen = false;

		// Оператор
		if(this.isOperator() == true){
			// Доступ не открываем
		}

		// Статистик
		if(this.isStatistick() == true){
			// Доступ не открываем
		}

		// Регистратор ЛВН
		if(this.isRegistratorLVN() == true){
			// Доступ не открываем
		}

		// Регистратор
		if(this.isRegistrator() == true){
			// Доступ не открываем
		}

		// Врач
		if(this.isVrach() == true){

			var checkUslovie1 = false;
			// Врач, указан в качестве врача, установившего исход
			if(getGlobalOptions().medpersonal_id == base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id')){
				checkUslovie1 = true;
			}

			var checkUslovie2 = false;
			// Если ЭЛН дубликат, то дубликат оформлен текущим врачом
			if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 2 && base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') == getGlobalOptions().medpersonal_id) {
				checkUslovie2 = true;
			}

			if (checkUslovie1 == true || checkUslovie2 == true) {
				isOpen = true;
			}

		}

		if(this.isVrachVK() == true){
			// Доступ не открываем #145565
		}

		// Врач, регистратор (одновременно)
		if(this.isVrachAndRegistrator() == true){
			// Доступ не открываем
		}


		return isOpen;
	},

	_checkAccessToPanelStickLeave: function() {//- Проверяем доступность блока
		// @returns {boolean} false - доступ закрыт, true - доступ открыт
		eval(this.ini);
		var isOpen = false;// Флаг открытия

		// Оператор
		if (this.isOperator() == true) {
			// ---------------------------------------------------------------------------------------------------------
			// Условие 1
			//
			// 1. ЛВН без признака «В реестре» И без признака «Принят ФСС»
			var checkUslovie1 = false;
			if (!me.isPaid && !me.isInReg) {
				checkUslovie1 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			// ---------------------------------------------------------------------------------------------------------
			// Условие 2
			//
			// 2. В зависимости от наличия исхода ЛВН:
			// 		2.1 Если ЭЛН оригинал и Исход ЛВН указан:
			// 			2.1.1 Исход ЛВН указан врачом МО пользователя, и
			// 			2.1.2 Не указан ЛВН-продолжение.
			// 		2.2 Если ЭЛН дубликат и исход ЭЛН указан (подтянулся из оригинала):
			// 			2.2.1 Дубликат оформлен в МО Пользователя
			// 		2.3 Если ЭЛН дубликат и исход ЭЛН указан (в оригинале не было данных об исходе):
			// 			2.3.1 Исход ЛВН указан в МО Пользователя
			// 		2.4 Исход ЛВН не указан (дополнительных условий нет.)
			var checkUslovie2 = false;

			// Исход ЛВН (почему определяется именно так, я хз!) (c) m.kalyuzhniy
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

			// Исход ЛВН указан
			if (!Ext6.isEmpty(MedStaffFact_id)) {

				// 2.1 Если ЭЛН оригинал
				if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 1) {
					if (
						// исход ЛВН указан врачом МО пользователя
						getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id') &&

						// не указан ЛВН-продолжение
						Ext6.isEmpty(base_form.findField('EvnStick_NumNext').getValue())
					) {
						checkUslovie2 = true;
					}
				}

				// 2.2 Если ЭЛН дубликат
				if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 2) {
					var record = _this._getSelectedRecord_EvnStick_oid();
					if (record) {
						var StickLeaveType_id = record.get('StickLeaveType_id');

						// исход ЭЛН подтянулся из оригинала
						if (!Ext6.isEmpty(StickLeaveType_id)) {
							if (me.Lpu_id == getGlobalOptions().lpu_id) {
								checkUslovie2 = true;
							}

							// в оригинале не было данных об исходе
						} else {
							if (getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id')) {
								checkUslovie2 = true;
							}
						}
					}
				}


				// Исход ЛВН не указан
			} else if (Ext6.isEmpty(MedStaffFact_id)) {
				checkUslovie2 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			if (checkUslovie1 && checkUslovie2) {
				isOpen = true;
			}


		}

		// Статистик
		if (this.isStatistick() == true) {
			// ---------------------------------------------------------------------------------------------------------
			// Условие 1
			//
			// 1. ЛВН без признака «В реестре» И без признака «Принят ФСС»
			var checkUslovie1 = false;
			if (!me.isPaid && !me.isInReg) {
				checkUslovie1 = true;
			}


			// ---------------------------------------------------------------------------------------------------------
			// Условие 2
			//
			// 2. В зависимости от наличия исхода ЛВН:
			// 		2.1 Если ЭЛН оригинал и Исход ЛВН указан:
			// 			2.1.1 Исход ЛВН указан врачом МО пользователя, и
			// 			2.1.2 Не указан ЛВН-продолжение.
			// 		2.2 Если ЭЛН дубликат и исход ЭЛН указан (подтянулся из оригинала):
			// 			2.2.1 Дубликат оформлен в МО Пользователя
			// 		2.3 Если ЭЛН дубликат и исход ЭЛН указан (в оригинале не было данных об исходе):
			// 			2.3.1 Исход ЛВН указан в МО Пользователя
			// 		2.4 Исход ЛВН не указан (дополнительных условий нет.)
			var checkUslovie2 = false;

			// Исход ЛВН (почему определяется именно так, я хз!) (c) m.kalyuzhniy
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

			// Исход ЛВН указан
			if (!Ext6.isEmpty(MedStaffFact_id)) {

				// 2.1 Если ЭЛН оригинал
				if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 1) {
					if (
						// исход ЛВН указан врачом МО пользователя
						getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id') &&

						// не указан ЛВН-продолжение
						Ext6.isEmpty(base_form.findField('EvnStick_NumNext').getValue())
					) {
						checkUslovie2 = true;
					}
				}

				// 2.2 Если ЭЛН дубликат
				if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 2) {
					var record = _this._getSelectedRecord_EvnStick_oid();
					if (record) {
						var StickLeaveType_id = record.get('StickLeaveType_id');

						// исход ЭЛН подтянулся из оригинала
						if (!Ext6.isEmpty(StickLeaveType_id)) {
							if (me.Lpu_id == getGlobalOptions().lpu_id) {
								checkUslovie2 = true;
							}

							// в оригинале не было данных об исходе
						} else {
							if (getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id')) {
								checkUslovie2 = true;
							}
						}
					}
				}


				// Исход ЛВН не указан
			} else if (Ext6.isEmpty(MedStaffFact_id)) {
				checkUslovie2 = true;
			}
			// ---------------------------------------------------------------------------------------------------------
			if (checkUslovie1 && checkUslovie2) {
				isOpen = true;
			}
		}

		// Регистратор ЛВН
		if (this.isRegistratorLVN() == true) {
			// ---------------------------------------------------------------------------------------------------------
			// Условие 1
			//
			// 1. ЛВН без признака «В реестре» И без признака «Принят ФСС»
			var checkUslovie1 = false;
			if (!me.isPaid && !me.isInReg) {
				checkUslovie1 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			// ---------------------------------------------------------------------------------------------------------
			// Условие 2
			//
			// 2. В зависимости от наличия исхода ЛВН:
			// 		2.1 Если ЭЛН оригинал и Исход ЛВН указан:
			// 			2.1.1 Исход ЛВН указан врачом МО пользователя, и
			// 			2.1.2 Не указан ЛВН-продолжение.
			// 		2.2 Если ЭЛН дубликат и исход ЭЛН указан (подтянулся из оригинала):
			// 			2.2.1 Дубликат оформлен в МО Пользователя
			// 		2.3 Если ЭЛН дубликат и исход ЭЛН указан (в оригинале не было данных об исходе):
			// 			2.3.1 Исход ЛВН указан в МО Пользователя
			// 		2.4 Исход ЛВН не указан (дополнительных условий нет.)
			var checkUslovie2 = false;

			// Исход ЛВН (почему определяется именно так, я хз!)
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

			// Исход ЛВН указан
			if (!Ext6.isEmpty(MedStaffFact_id)) {

				// 2.1 Если ЭЛН оригинал
				if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 1) {
					if (
						// исход ЛВН указан врачом МО пользователя
						getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id') &&

						// не указан ЛВН-продолжение
						Ext6.isEmpty(base_form.findField('EvnStick_NumNext').getValue())
					) {
						checkUslovie2 = true;
					}
				}

				// 2.2 Если ЭЛН дубликат
				if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 2) {
					var record = _this._getSelectedRecord_EvnStick_oid();
					if (record) {
						var StickLeaveType_id = record.get('StickLeaveType_id');

						// исход ЭЛН подтянулся из оригинала
						if (!Ext6.isEmpty(StickLeaveType_id)) {
							if (me.Lpu_id == getGlobalOptions().lpu_id) {
								checkUslovie2 = true;
							}

							// в оригинале не было данных об исходе
						} else {
							if (getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id')) {
								checkUslovie2 = true;
							}
						}
					}
				}


			// Исход ЛВН не указан
			} else if (Ext6.isEmpty(MedStaffFact_id)) {
				checkUslovie2 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			if (checkUslovie1 && checkUslovie2) {
				isOpen = true;
			}

		}


		// Регистратор
		if (this.isRegistrator() == true) {
			//
		}


		// Врач
		if (this.isVrach() == true) {
			// ---------------------------------------------------------------------------------------------------------
			// Условие 1
			//
			// 1. ЛВН без признака «В реестре» И нет признака «Принят ФСС».
			var checkUslovie1 = false;
			if (!me.isPaid && !me.isInReg) {
				checkUslovie1 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			// ---------------------------------------------------------------------------------------------------------
			// Условие 2
			//
			// 2. В зависимости от наличия исхода ЛВН
			// 		2.1 Если ЭЛН оригинал и исход ЛВН указан:
			// 			2.1.1 исход ЛВН указан этим врачом, и
			// 			2.1.2 не указан ЛВН-продолжение.
			// 		2.2 Если ЭЛН дубликат и исход ЭЛН указан (подтянулся из оригинала):
			// 			2.2.1 Дубликат оформлен текущим врачом
			// 		2.3 Если ЭЛН дубликат и исход ЭЛН указан (в оригинале не было данных об исходе):
			// 			2.3.1 Исход ЛВН указан текущим врачом
			// 		2.4 Исход ЛВН не указан:
			// 			2.4.1 дополнительных условий нет.
			var checkUslovie2 = false;

			// Исход ЛВН (почему определяется именно так, я хз!) по идее это врач указавший исход
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
			if (!Ext6.isEmpty(MedStaffFact_id)) {

				// 2.1 Если ЭЛН оригинал
				if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 1) {

					// 2.1.1 исход ЛВН указан этим врачом, и
					// 2.1.2 не указан ЛВН-продолжение.
					if (
						// исход ЛВН указан врачом МО пользователя
						getGlobalOptions().medpersonal_id == base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') &&

						// не указан ЛВН-продолжение
						Ext6.isEmpty(base_form.findField('EvnStick_NumNext').getValue())
					) {
						checkUslovie2 = true;
					}

				}


				// 2.2 Если ЭЛН дубликат
				if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 2) {

					var record = _this._getSelectedRecord_EvnStick_oid();
					var StickLeaveType_id_fromOriginal = null;
					if (record) {
						StickLeaveType_id_fromOriginal = record.get('StickLeaveType_id');
					}


					// исход ЭЛН указан (подтянулся из оригинала)
					// видимо когда мы выбираем оригинал при создании ЛВН
					// я думаю что это только при создании ЛВН, проверить что будет при редактировании если выбрать
					// оригинал с исходом, что будет с сохраненным исходом, замениться ли он
					if (!Ext6.isEmpty(StickLeaveType_id_fromOriginal)) {

						// 2.2.1 Дубликат оформлен текущим врачом (то есть если МО врача выбранного в исходе совпадает с МО Пользователя)
						// узнать как сохраняется врач создавший дубликат
						//if (base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') == getGlobalOptions().medpersonal_id) {
						
						// 2.2.1 Дубликат оформлен текущим врачом
						if (vm.get('action') == 'add' || base_form.findField('pmUser_insID').getValue() == getGlobalOptions().pmuser_id) {
							checkUslovie2 = true;
						}
					}


					// исход ЭЛН указан (в оригинале не было данных об исходе)
					// логически данная ситуация возможна только при редактировании ЛВН т.к. при создании исход будет
					// пустым
					if (!Ext6.isEmpty(StickLeaveType_id_fromOriginal)) {

						// 2.3.1 Исход ЛВН указан текущим врачом
						// узнать как сохраняется врач указавший исход
						// В ИТОГЕ видимо врач указавший исход указывает себя в поле врач ниже "MedStaffFact_id" (смотри поле StickLeaveType_id событие select)
						if (base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') == getGlobalOptions().medpersonal_id) {
							checkUslovie2 = true;
						}
					}

				}


				// Исход ЛВН не указан
			} else {
				checkUslovie2 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			if (checkUslovie1 == true && checkUslovie2 == true) {
				isOpen = true;
			}

		}

		// Врач, регистратор (одновременно)
		if (this.isVrachAndRegistrator() == true) {
			// ---------------------------------------------------------------------------------------------------------
			// Условие 1
			//
			// 1. ЛВН без признака «В реестре» И нет признака «Принят ФСС».
			var checkUslovie1 = false;
			if (!me.isPaid && !me.isInReg) {
				checkUslovie1 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			// ---------------------------------------------------------------------------------------------------------
			// Условие 2
			//
			// 2. В зависимости от наличия исхода ЛВН
			// 		2.1 Если ЭЛН оригинал и исход ЛВН указан:
			// 			2.1.1 исход ЛВН указан этим врачом, и
			// 			2.1.2 не указан ЛВН-продолжение.
			// 		2.2 Если ЭЛН дубликат и исход ЭЛН указан (подтянулся из оригинала):
			// 			2.2.1 Дубликат оформлен текущим врачом
			// 		2.3 Если ЭЛН дубликат и исход ЭЛН указан (в оригинале не было данных об исходе):
			// 			2.3.1 Исход ЛВН указан текущим врачом
			// 		2.4 Исход ЛВН не указан:
			// 			2.4.1 дополнительных условий нет.
			var checkUslovie2 = false;

			// Исход ЛВН (почему определяется именно так, я хз!) по идее это врач указавший исход
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
			if (!Ext6.isEmpty(MedStaffFact_id)) {

				// 2.1 Если ЭЛН оригинал
				if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 1) {

					// 2.1.1 исход ЛВН указан этим врачом, и
					// 2.1.2 не указан ЛВН-продолжение.
					if (
						// исход ЛВН указан врачом МО пользователя
						getGlobalOptions().medpersonal_id == base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') &&

						// не указан ЛВН-продолжение
						Ext6.isEmpty(base_form.findField('EvnStick_NumNext').getValue())
					) {
						checkUslovie2 = true;
					}

				}


				// 2.2 Если ЭЛН дубликат
				if (base_form.findField('EvnStick_IsOriginal').getRawValue() == 2) {

					var record = _this._getSelectedRecord_EvnStick_oid();
					var StickLeaveType_id_fromOriginal = null;
					if (record) {
						StickLeaveType_id_fromOriginal = record.get('StickLeaveType_id');
					}


					// исход ЭЛН указан (подтянулся из оригинала)
					// видимо когда мы выбираем оригинал при создании ЛВН
					// я думаю что это только при создании ЛВН, проверить что будет при редактировании если выбрать
					// оригинал с исходом, что будет с сохраненным исходом, замениться ли он
					if (!Ext6.isEmpty(StickLeaveType_id_fromOriginal)) {

						// 2.2.1 Дубликат оформлен текущим врачом (то есть если МО врача выбранного в исходе совпадает с МО Пользователя)
						// узнать как сохраняется врач создавший дубликат
						if (base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') == getGlobalOptions().medpersonal_id) {
							checkUslovie2 = true;
						}
					}


					// исход ЭЛН указан (в оригинале не было данных об исходе)
					// логически данная ситуация возможна только при редактировании ЛВН т.к. при создании исход будет
					// пустым
					if (Ext6.isEmpty(StickLeaveType_id_fromOriginal)) {

						// 2.3.1 Исход ЛВН указан текущим врачом
						// узнать как сохраняется врач указавший исход
						// В ИТОГЕ видимо врач указавший исход указывает себя в поле врач ниже "MedStaffFact_id" (смотри поле StickLeaveType_id событие select)
						if (base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') == getGlobalOptions().medpersonal_id) {
							checkUslovie2 = true;
						}
					}

				}


				// Исход ЛВН не указан
			} else {
				checkUslovie2 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			if (checkUslovie1 == true && checkUslovie2 == true) {
				isOpen = true;
			}

		}

		this._openAccessToPanelStickLeave();
		return isOpen;
	},

	_checkAccessToPanelStickRegime: function(){///
		/**
		 * Проверяем доступность блока
		 * @returns {boolean} false - доступ закрыт, true - доступ открыт
		 * @private
		 */
		eval(this.ini);
		// Флаг открытия
		var isOpen = false;
		
		// Оператор
		if(this.isOperator() == true){
			// refs #136152
			var checkUslovie1 = true; //false; //коммент из ext2: ВРЕМЕННО!!! Так как ошибка немедленная, то пока просто откроем на редактирование.
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.У ЭЛН нет признака «Принят ФСС» (IsPaid) И «В реестре» (IsInReg)
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// Регион: Все, кроме Казахстана
			if(getRegionNick() != 'kz'){

				// 2. Статус ЛВН в Промед: открыт, закрыт.
				if(this.checkStatus_EvnStick() == true){
					checkUslovie2 = true;
				}

			// Если Казахстан
			} else {
				checkUslovie2 = true;
			}


			// 3. Только ЛВН, созданные в МО. Пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}


		// Статистик
		if(this.isStatistick() == true){
			// refs #136152
			var checkUslovie1 = true; //false; ext2: ВРЕМЕННО!!! Так как ошибка немедленная, то пока просто откроем на редактирование.
			var checkUslovie2 = false;
			var checkUslovie3 = false;


			// 1.У ЭЛН нет признака «Принят ФСС» (IsPaid) И «В реестре» (IsInReg)
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}


			// Регион: Все, кроме Казахстана
			if(getRegionNick() != 'kz'){

				// 2. Статус ЛВН в Промед: открыт, закрыт.
				if(this.checkStatus_EvnStick() == true){
					checkUslovie2 = true;
				}

				// Если Казахстан
			} else {
				checkUslovie2 = true;
			}


			// 3. Только ЛВН, созданные в МО. Пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}


		// Регистратор ЛВН
		if(this.isRegistratorLVN() == true){
			// refs #136152
			var checkUslovie1 = true; //false; ВРЕМЕННО!!! Так как ошибка немедленная, то пока просто откроем на редактирование.
			var checkUslovie2 = false;
			var checkUslovie3 = false;


			// 1.У ЭЛН нет признака «Принят ФСС» (IsPaid) И «В реестре» (IsInReg)
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}


			// Регион: Все, кроме Казахстана
			if(getRegionNick() != 'kz'){

				// 2. Статус ЛВН в Промед: открыт, закрыт.
				if(this.checkStatus_EvnStick() == true){
					checkUslovie2 = true;
				}

				// Если Казахстан
			} else {
				checkUslovie2 = true;
			}


			// 3. Только ЛВН, созданные в МО. Пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
			if(_this.checkOwn_Lpu() == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}


		// Регистратор
		if(this.isRegistrator() == true){
			isOpen = false;
		}


		// Врач
		if(this.isVrach() == true){
			// refs #136152
			var checkUslovie1 = true; //false; ext2: ВРЕМЕННО!!! Так как ошибка немедленная, то пока просто откроем на редактирование.
			var checkUslovie2 = false;
			var checkUslovie3 = false;
			var checkUslovie4 = false;

			// Регион: Все, кроме Казахстана
			if(getRegionNick() != 'kz'){

				// 1. У ЭЛН нет признака «Принят ФСС» (IsPaid) И нет признака «В реестре» (IsInReg)  (Регион: Все, кроме Казахстана).
				if(vm.get('isPaid') == false && vm.get('isInReg') == false){
					checkUslovie1 = true;
				}

				// Если Казахстан
			} else {
				checkUslovie1 = true;
			}


			// 2. Статус ЛВН в Промед: открыт, закрыт.
			if(this.checkStatus_EvnStick() == true){
				checkUslovie2 = true;
			}

			// 3. ЭЛН добавлен в МО пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
			//~ if(this.checkOwn_Lpu() == true){
				//~ checkUslovie3 = true;
			//~ }

			// 4. Врач указан в качестве врача в любом периоде  «Освобождения от работы» или исходе
			//~ if (this.checkMedPersonalInWorkRelease() == true || base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') == getGlobalOptions().medpersonal_id || vm.get('action') == 'add') {
				//~ checkUslovie4 = true;
			//~ }
			
			//#156721 Поле «Нарушение режима» доступно для редактирования независимо от МО создания ЛВН и присутствия врача в освобождении от работы
			checkUslovie3 = true; 
			checkUslovie4 = true;

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true && checkUslovie4 == true){
				isOpen = true;
			}
		}


		// Врач, регистратор (одновременно)
		if(this.isVrachAndRegistrator() == true){
			// refs #136152
			var checkUslovie1 = true; //false; ext2: ВРЕМЕННО!!! Так как ошибка немедленная, то пока просто откроем на редактирование.
			var checkUslovie2 = false;
			var checkUslovie3 = false;
			var checkUslovie4 = false;

			// Регион: Все, кроме Казахстана
			if(getRegionNick() != 'kz'){

				// 1. У ЭЛН нет признака «Принят ФСС» (IsPaid) И нет признака «В реестре» (IsInReg)  (Регион: Все, кроме Казахстана).
				if(vm.get('isPaid') == false && vm.get('isInReg') == false){
					checkUslovie1 = true;
				}

				// Если Казахстан
			} else {
				checkUslovie1 = true;
			}


			// 2. Статус ЛВН в Промед: открыт, закрыт.
			if(this.checkStatus_EvnStick() == true){
				checkUslovie2 = true;
			}

			// 3. ЭЛН добавлен в МО пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie3 = true;
			}

			// 4. Врач указан в качестве врача в любом периоде  «Освобождения от работы» или исходе
			if (this.checkMedPersonalInWorkRelease() == true || base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') == getGlobalOptions().medpersonal_id && vm.get('action') == 'add') {
				checkUslovie4 = true;
			}


			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true && checkUslovie4 == true){
				isOpen = true;
			}
		}
		
		vm.set('isAccessToStickRegime', isOpen);
		return isOpen;
	},

	checkMedPersonalInWorkRelease: function() {///:boolean; Проверка, что текущий врач есть в одном из освобождений от работы
		//из редакции 130912 ext2, вместо checkFirstVrachAsThisVrach редакции 113317
		eval(this.ini);
		var work_release_grid = me.queryById('EStEF_EvnStickWorkReleaseGrid');

		var isInWorkRelease = false;
		work_release_grid.getStore().each(function(rec) {
			if (rec && rec.get('MedPersonal_id') && rec.get('MedPersonal_id') == getGlobalOptions().medpersonal_id) {
				isInWorkRelease = true;
			}
		});

		return isInWorkRelease;
	},

	doClearEvnStickNumButton: function() {///ext6 : код из конструктора формы
		eval(this.ini);
		win.mask(langs('Отмена бронирования номера...'));
		
		// разбронировать номер.
		Ext6.Ajax.request({
			url: '/?c=RegistryESStorage&m=unbookEvnStickNum',
			params: {
				RegistryESStorage_id: base_form.findField('RegistryESStorage_id').getValue()
			},
			callback: function() {
				win.unmask();
				base_form.findField('RegistryESStorage_id').setValue(null);
				base_form.findField('EvnStick_Num').setValue('');
				//~ base_form.findField('EvnStick_Num').fireEvent('change', base_form.findField('EvnStick_Num'), base_form.findField('EvnStick_Num').getValue());
				_this.refreshFormPartsAccess();
			}
		});
	},

	doBeforePrintEvnStick: function() {///
		eval(this.ini);
		switch ( vm.get('action') ) {
			case 'add':
			case 'edit':
				this.doSave({
					print: true
				});
			break;

			case 'view':
				this.CheckWorkRelease();
			break;
		}
	},
	
	doPrintESSConsent: function() {// Печать согласия
		eval(this.ini);
		var evn_stick_id = base_form.findField('EvnStick_id').getValue(),
			person_id = base_form.findField('Person_id').getValue(),
			stickcause_id = base_form.findField('StickCause_id').getValue(),
			consent_dt = base_form.findField('EvnStickBase_consentDT').getValue();
		if (!consent_dt) return false;
		consent_dt = consent_dt.format('d.m.Y');
		printBirt({
			'Report_FileName': 'Person_Soglasie_Stick.rptdesign',
			'Report_Params': '&paramPerson=' + person_id + '&paramLpu=' + getGlobalOptions().lpu_id + '&paramStickCause=' + stickcause_id + '&paramDate=' + consent_dt,
			'Report_Format': 'pdf'
		});
	},

	getPersonJobInfo: function(Person_id){//+ Для поля "Должность"
		eval(this.ini);
		var job_info = null;

		if( !Ext6.isEmpty(Person_id)){
			$.ajax({
				method: "POST",
				url: '/?c=Person&m=getPersonJobInfo',
				data: {
					'Person_id': Person_id
				},
				async: false,
				success: function(response){
					var result = Ext6.util.JSON.decode(response);
					if( ! Ext6.isEmpty(result) &&  ! Ext6.isEmpty(result[0])){
						job_info = result[0];
					}
				}
			});
		}

		return job_info;
	},

	checkOwn_Lpu: function(){// Только ЛВН, созданные в МО. Пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
		this.log();
		// тоже не учитываем, точно не знаю почему, но возможно: При открытии лвн его же добавляют в той мо, к которой пользователь его заносящий относится.
		return true;
	},

	_enableButtonSave: function() {
		this.getView().queryById('button_save').enable();
	},
	_disableButtonSave: function() {
		this.getView().queryById('button_save').disable();
	},
	checkSaveButtonEnabled: function() {//+ блокируем или разблокируем кнопку "Сохранить"
		// При связывании ЛВН с учетным докуметом, кнопка сохранить доступна, если:
		// 1. есть хотя бы одно освобождение в своей МО
		// 2. указан исход и он в своей МО
		eval(this.ini);

		var hasOwnWorkRelease = false;
		win.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().each(function(rec) {
			if (rec && rec.get('Lpu_id') && rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
				hasOwnWorkRelease = true;
			}
		});

		if (
			vm.get('link') != true
			|| hasOwnWorkRelease
			|| (!Ext6.isEmpty(base_form.findField('MedStaffFact_id').getValue()) && base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id') == getGlobalOptions().lpu_id)
		) {
			this._enableButtonSave();
			return true;
		} else {
			this._disableButtonSave();
			return false;
		}
	},

	setMaxDateForSetDate: function() {//++ Дата выдачи
		eval(this.ini);
		base_form.findField('EvnStick_setDate').setMaxValue(Date.parseDate((getGlobalOptions().date), 'd.m.Y').add(Date.DAY, 2));
		if (base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'pregn' && base_form.findField('StickOrder_id').getFieldValue('StickOrder_Code') == 2) {
			base_form.findField('EvnStick_setDate').setMaxValue(null);
		}
	},
	doGetEvnStickNumButton: function() { //ext6
		eval(this.ini);
		if (!Ext6.isEmpty(win.Person_Snils)) {
			win.mask(langs('Получение номера ЭЛН...'));
			Ext6.Ajax.request({
				url: '/?c=RegistryESStorage&m=getEvnStickNum',
				callback: function(opt, success, response) {
					win.unmask();
					var base_form = win.FormPanel.getForm();
					var responseObj = Ext6.util.JSON.decode(response.responseText);
					if (responseObj.EvnStick_Num) {
						base_form.findField('EvnStick_Num').setValue(responseObj.EvnStick_Num);
						base_form.findField('RegistryESStorage_id').setValue(responseObj.RegistryESStorage_id);
					} else {
						base_form.findField('EvnStick_Num').setValue('');
						base_form.findField('RegistryESStorage_id').setValue(null);
					}
					//~ base_form.findField('EvnStick_Num').fireEvent('change', base_form.findField('EvnStick_Num'), base_form.findField('EvnStick_Num').getValue());
					
					if(responseObj.EvnStick_Num && getRegionNick() != 'kz' && !Ext6.isEmpty(win.queryById('buttonPrintTruncELN'))){
						win.queryById('buttonPrintTruncELN').enable();
					}
					
					_this.refreshFormPartsAccess();
				}
			});
		} else {
			var Person_Fio = base_form.findField('EvnStickFullNameText').getValue();
			Ext6.Msg.show({
				icon: Ext6.MessageBox.ERROR,
				title: 'Ошибка',
				msg: 'Для ' + Person_Fio + ' не указан СНИЛС. Электронные больничные без указания СНИЛС не принимаются ФСС и не подлежат оплате. Введите СНИЛС в реквизитах пациента или оформите ЛВН на бланке',
				buttons: Ext6.Msg.OK,
				fn: function() {
					base_form.findField('EvnStick_Num').focus();
				}

			});
		}
	},
	
	onClearFio: function() {//то же что onTrigger3Click для EvnStickFullNameText из ext2
		eval(this.ini);
		var base_form = win.FormPanel.getForm();

		if ( base_form.findField('EvnStickFullNameText').disabled ) {
			return false;
		}

		base_form.findField('EvnStickFullNameText').setRawValue('');
		base_form.findField('Person_id').setValue(0);
		base_form.findField('PersonEvn_id').setValue(0);
		base_form.findField('Server_id').setValue(-1);

		win.Person_Snils = null;
		_this._checkSnils();
	},

	_closeAccessToPanelStickRegime: function(){//
		eval(this.ini);

		vm.set('isAccessToStickRegime', false);//~ win.getFields('EStEF_StickRegimePanel').forEach(win.disableField);

		return true;
	},

	/*_openAccessToPanelStickRegime: function(){//+ убрал в _checkAccessToPanelStickRegime
		eval(this.ini);

		vm.set('isAccessToStickRegime', true);//~ win.getFields('EStEF_StickRegimePanel').forEach(win.enableField);

		return true;
	},*/
	
	getDvijeniaKVC:  function(){//+
		eval(this.ini);

		if( ! Ext6.isEmpty(me.EvnSectionList)){
			return me.EvnSectionList;
		}

		me.EvnSectionList = null;

		var EvnPS_id = this.findKVC();

		// Если у ЛВН есть связанная КВС
		if( ! Ext6.isEmpty(EvnPS_id)){

			$.ajax({
				method: "POST",
				url: '/?c=Stick&m=getEvnSectionList',
				data: {
					'EvnPS_id': EvnPS_id
				},
				async: false,
				success: function(response){
					var result = Ext6.util.JSON.decode(response);
					if( ! Ext6.isEmpty(result)){
						me.EvnSectionList = result;
					}
				}
			});

			// нам нужно получить движения с признаком "тип"
		}

		return me.EvnSectionList;
	},
	
	getBegEndDatesInStac: function(){
		eval(this.ini);

		me.EvnSectionDates = null;


		var EvnPS_id = this.findKVC();

		// Если у ЛВН есть связанная КВС
		if( ! Ext6.isEmpty(EvnPS_id)){

			$.ajax({
				method: "POST",
				url: '/?c=Stick&m=getBegEndDatesInStac',
				data: {
					'EvnPS_id': EvnPS_id
				},
				async: false,
				success: function(response){
					var result = Ext6.util.JSON.decode(response);
					if( ! Ext6.isEmpty(result)){
						me.EvnSectionDates = result[0];
					}
				}
			});

			// нам нужно получить движения с признаком "тип"
		}

		return me.EvnSectionDates;
	},
	
	loadMedStaffFactList: function() {
		eval(this.ini);
		var evn_stick_work_release_end_date = null;
		var evn_stick_work_release_store = me.queryById('EStEF_EvnStickWorkReleaseGrid').getStore();

		evn_stick_work_release_store.each(function(record) {
			if ( evn_stick_work_release_end_date == null || record.get('EvnStickWorkRelease_endDate') > evn_stick_work_release_end_date ) {
				evn_stick_work_release_end_date = record.get('EvnStickWorkRelease_endDate');
			}
		});

		var
			index = -1,
			MedPersonal_id = base_form.findField('MedPersonal_id').getValue(),
			MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

		if (evn_stick_work_release_end_date == null) {
			evn_stick_work_release_end_date = getValidDT(getGlobalOptions().date, '');
		}

		setMedStaffFactGlobalStoreFilter({
			onDate: Ext6.util.Format.date(evn_stick_work_release_end_date, 'd.m.Y')
		});

		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		if ( !Ext6.isEmpty(MedStaffFact_id) ) {
			index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
				return (rec.get('MedStaffFact_id') == MedStaffFact_id);
			});
		}

		if ( index == -1 && !Ext6.isEmpty(MedPersonal_id) ) {
			index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
				return (rec.get('MedPersonal_id') == MedPersonal_id);
			});
		}

		if ( index >= 0 ) {
			base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
		}
	},
	
	_checkAccessToField_EStEF_btnSetMinDateFromPS: function(){
		eval(this.ini);
		var isOpen = false;

		// Оператор
		if(this.isOperator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				var checkUslovie3_1 = false;
				var checkUslovie3_2 = false;

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3_1 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3_1 = true;
						}
					}

				}

				// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
				// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
				// «дата окончания» не пустые.
				if(vm.get('action') == 'edit'){

					var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
					var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

					if( ! Ext6.isEmpty(stacBegDate) || ! Ext6.isEmpty(stacEndDate)){
						checkUslovie3_2 = true;
					}
				}


				if(checkUslovie3_1 == true && checkUslovie3_2 == true){
					checkUslovie3 = true;
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Статистик
		if(this.isStatistick() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				var checkUslovie3_1 = false;
				var checkUslovie3_2 = false;

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3_1 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3_1 = true;
						}
					}

				}

				// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
				// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
				// «дата окончания» не пустые.
				if(vm.get('action') == 'edit'){

					var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
					var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

					if( ! Ext6.isEmpty(stacBegDate) || ! Ext6.isEmpty(stacEndDate)){
						checkUslovie3_2 = true;
					}
				}


				if(checkUslovie3_1 == true && checkUslovie3_2 == true){
					checkUslovie3 = true;
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}

		}

		// Регистратор ЛВН
		if(this.isRegistratorLVN() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3 = true;
						}
					}
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Регистратор
		if(this.isRegistrator() == true){
			//isOpen = false;
		}

		// Врач
		if(this.isVrach() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3 = true;
						}
					}
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}

		}

		// Врач, регистратор (одновременно)
		if(this.isVrachAndRegistrator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3 = true;
						}
					}
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}
		vm.set('isAccessToField_EStEF_btnSetMinDateFromPS', isOpen);
		return isOpen;
	},
	
	getWorkReleaseSumm: function(EvnStick_id) { /// получаем сумму освобождений из предыдущих ЛВН
		eval(this.ini);

		Ext6.Ajax.request({
			url: '/?c=Stick&m=getWorkReleaseSumPeriod',
			params: {
				'EvnStick_id': EvnStick_id
			},
			callback: function(opt, success, response) {
				if (success && response.responseText.length > 0) {
					var result = Ext6.util.JSON.decode(response.responseText);
					if (result.success) {
						base_form.findField('WorkReleaseSumm').setValue(result.WorkReleaseSumm);
					}
				}
			}
		});
	},
	
	getPridStickLeaveTypeCode: function() {///
		eval(this.ini);
		var code1 = base_form.findField('PridStickLeaveType_Code1').getValue();
		var code2 = base_form.findField('PridStickLeaveType_Code2').getValue();

		if (!Ext6.isEmpty(code2) && code2 != '0') return code2;
		if (!Ext6.isEmpty(code1) && code1 != '0') return code1;
		return '0';
	},
	
	deleteGridRecord: function(object) {/// таблица освобождений
		eval(this.ini);
		
		if ( vm.get('action') == 'view' ) {
			return false;
		}

		if ( typeof object != 'string' || !(object.inlist([ 'EvnStickCarePerson', 'EvnStickWorkRelease' ])) ) {
			return false;
		}

		var grid = win.queryById('EStEF_' + object + 'Grid');

		if ( !grid || Ext6.isEmpty(grid.recordMenu.data_id) || grid.recordMenu.data_id<0 || !grid.getStore().getAt(grid.recordMenu.data_id)) {
			return false;
		}

		var record = grid.getStore().getAt(grid.recordMenu.data_id);

		if (getRegionNick() != 'kz' && record.get('EvnStickWorkRelease_IsInReg') && record.get('EvnStickWorkRelease_IsInReg') == 2) {
			Ext6.Msg.alert(langs('Ошибка'), 'Выбранный период освобождения от работы отправлен в ФСС. Удаление невозможно');
			return false;
		}

		if (object == 'EvnStickWorkRelease' && record.get('EvnVK_id') ) {
			Ext6.Msg.alert(langs('Ошибка'), 'Освобождение связано с протоколом заседания врачебной комиссии № '+record.get('EvnVK_NumProtocol')+'.');
			return false;
		}

		switch ( Number(record.get('RecordStatus_Code')) ) {
			case 0:
				grid.getStore().remove(record);
			break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
			break;
		}
		win.panelEvnStickWorkRelease.refreshTitle();
		this.checkLastEvnStickWorkRelease('del');
	},
	
	setEvnStickDisDate: function() {// Устанавливаем "Дата исхода ЛВН"
	// «Дата исхода ЛВН» рассчитывается исходя из существующих в ЛВН по совместительству освобождениях от работы
		eval(this.ini);
		var StickLeaveType_Code = base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code');
		if (!StickLeaveType_Code) return false;
		StickLeaveType_Code = StickLeaveType_Code.toString();
		// Получаем дату окончания последнего освобождения от работы
		var evn_stick_work_release_end_date = null;
		var evn_stick_work_release_store = win.queryById('EStEF_EvnStickWorkReleaseGrid').getStore();
		var evn_stick_dis_date = base_form.findField('EvnStick_disDate').getValue();

		evn_stick_work_release_store.each(function(record) {
			if ( evn_stick_work_release_end_date == null || record.get('EvnStickWorkRelease_endDate') > evn_stick_work_release_end_date ) {
				evn_stick_work_release_end_date = record.get('EvnStickWorkRelease_endDate');
			}
		});

		switch ( StickLeaveType_Code ) {
			case '01':
				if ( evn_stick_work_release_end_date ) {
					base_form.findField('EvnStick_disDate').setValue(evn_stick_work_release_end_date.add(Date.DAY, 1));
				}
				break;

			case '32':
				if ( base_form.findField('EvnStick_mseExamDate').getValue() ) {
					base_form.findField('EvnStick_disDate').setValue(base_form.findField('EvnStick_mseExamDate').getValue());
				}
				else if ( evn_stick_dis_date ) {
					base_form.findField('EvnStick_disDate').setValue(evn_stick_dis_date);
				}
				break;

			case '31':
			case '35':
			case '36':
			case '37':
			case 'W.8':
				if ( evn_stick_work_release_end_date ) {
					// base_form.findField('EvnStick_disDate').setValue(evn_stick_work_release_end_date.add(Date.DAY, 1));
					base_form.findField('EvnStick_disDate').setValue(evn_stick_work_release_end_date);
				}
				else if ( evn_stick_dis_date ) {
					base_form.findField('EvnStick_disDate').setValue(evn_stick_dis_date);
				}
				break;

			case '33':
				// Изменена группа инвалидности
				break;

			case '34':
				// Дата смерти
				break;
		}
	},
	
	updateEvnStickWorkReleaseGrid: function() {
		eval(this.ini);
		var stick_grid = win.queryById('EStEF_EvnStickWorkReleaseGrid');
		var grid_update_callback = function (result, add_work_release) {
			for (var i = 0; i < result.length; i++) {
				var record = stick_grid.getStore().getAt(i);
				if ( typeof record == 'object' ) {
					if (result[i].EvnStickWorkRelease_updDT > record.get('EvnStickWorkRelease_updDT')) {
						record.set('EvnStickWorkRelease_begDate', getValidDT(result[i].EvnStickWorkRelease_begDate, ''));
						record.set('EvnStickWorkRelease_endDate', getValidDT(result[i].EvnStickWorkRelease_endDate, ''));
						record.set('RecordStatus_Code', 2);
						record.commit();
					}
				} else if ( add_work_release > 0 && add_work_release <= (result.length - stick_grid.getStore().getCount()) ) {
					add_work_release--;
					/*var tmpId = 0;
					do {
						tmpId = -Math.floor(Math.random() * 1000000);
					} while (stick_grid.getStore().find('EvnStickWorkRelease_id', tmpId)>=0);
					result[i].EvnStickWorkRelease_id = tmpId; //-swGenTempId(stick_grid.getStore());
					*/
					result[i].EvnStickWorkRelease_id = -this.swGenTempId(stick_grid.getStore(),'EvnStickWorkRelease_id');
					
					result[i].EvnStickWorkRelease_IsInReg = 1;
					result[i].EvnStickWorkRelease_IsPaid = 1;
					result[i].SMP_Status_Name = 'Документ не подписан';
					result[i].SMP_updDT = null;
					result[i].SMP_updUser_Name = null;
					result[i].SVK_Status_Name = 'Документ не подписан';
					result[i].SVK_updDT = null;
					result[i].SVK_updUser_Name = null;
					result[i].Signatures_mid = null;
					result[i].Signatures_wid = null;
					stick_grid.getStore().loadData([result[i]], true);
				}
			}
			_this.setEvnStickDisDate();
			_this.loadMedStaffFactList();
		};

		Ext6.Ajax.request({
			url: '/?c=Stick&m=updateEvnStickWorkReleaseGrid',
			params: {
				'EvnStick_id': base_form.findField('EvnStick_id').getValue(),
				'EvnStick_pid': base_form.findField('EvnStickDop_pid').getValue(),
				'ignoreRegAndPaid': 1
			},
			callback: function(opt, success, response) {
				if (success && response.responseText.length > 0) {
					var result = Ext6.util.JSON.decode(response.responseText);
					if (result.length > stick_grid.getStore().getCount()) {
						if ((result.length - stick_grid.getStore().getCount()) == 1 ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										grid_update_callback(result, 1);
									} else {
										grid_update_callback(result, 0);
									}
								},
								icon: Ext6.MessageBox.QUESTION,
								msg: langs('В ЛВН по основному месту работы найдено освобождение от работы, отсутствующее в ЛВН по совместительству. Перенести?'),
								title: langs('Вопрос')
							});
						} else if ((result.length - stick_grid.getStore().getCount()) == 2) {
							Ext6.Msg.show({
									buttons: {
									yes: langs('Перенести освобождение №2 и №3'),
									no: langs('Перенести только освобождение №2'),
									cancel: langs('Отмена')
								},
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										grid_update_callback(result, 2);
									} else if ( buttonId == 'no' ){
										grid_update_callback(result, 1);
									} else {
										grid_update_callback(result, 0);
									}
								},
								icon: Ext6.MessageBox.QUESTION,
								msg: langs('В ЛВН по основному месту работы найдены освобождения от работы, отсутствующие в ЛВН по совместительству. Перенести?'),
								title: langs('Вопрос')
							});
						}
					} else if (result.length > 0) {
						grid_update_callback(result, 0);
					}
				}
			}
		});
	},
	
	refreshFormPartsAccess: function() {//+-
		eval(this.ini);
		var lastWorkReleaseAccess = false,
			work_release_grid = win.queryById('EStEF_EvnStickWorkReleaseGrid');

		if (vm.get('action') == 'view') {
			return;
		}
		var hasWorkReleaseIsInReg = false;
		var hasWorkReleaseIsPaid = false;

		var firstMedPersonal_id = null;
		var maxDate = null;
		var minDate = null;
		work_release_grid.getStore().each(function(rec) {
			if ( rec && rec.get('EvnStickWorkRelease_begDate') != '' ) {
				if (rec.get('EvnStickWorkRelease_IsInReg') == 2) {
					hasWorkReleaseIsInReg = true;
				}
				if (rec.get('EvnStickWorkRelease_IsPaid') == 2) {
					hasWorkReleaseIsPaid = true;
				}

				if (maxDate == null || rec.get('EvnStickWorkRelease_begDate') > maxDate) {
					maxDate = rec.get('EvnStickWorkRelease_begDate');
					if (rec.get('accessType') == 'edit') {
						lastWorkReleaseAccess = true;
					} else {
						lastWorkReleaseAccess = false;
					}
				}

				if (minDate == null || rec.get('EvnStickWorkRelease_begDate') < minDate) {
					firstMedPersonal_id = rec.get('MedPersonal_id');
					minDate = rec.get('EvnStickWorkRelease_begDate');
				}
			}
		});

		// Основной раздел
		var mainPanelAccess = false;
		//~ vm.set('mainPanelAccess', true);
		//~ vm.set('mainPanelAccess', false);

		// Основной раздел, но только поля Номер, Организация, Наименование для печати
		var mainPanelSomeFieldsAccess = false;

		// Основной раздел, но только поля Код изм. нетрудоспособности и Доп. код нетрудоспособности
		var mainPanelSomeMainFieldsKodyNetrudAccess = true;

		//Основной раздел, поля СКЛ
		var mainPanelSSTFieldsAccess = false;

		// Список пациентов нуждающихся в уходе, МСЭ
		var carePersonMSEAccess = false;

		// Освобождение от работы
		var workReleaseAccess = false;


		// EvnStick_IsPaid - «Принят ФСС»
		// EvnStick_IsInReg - «В реестре»

		// если Оператор, Статистик или Регистратор ЛВН, то
		if (isOperator() || isMedStatUser() || isRegLvn()) {
			if (getRegionNick() == 'kz' || ( !vm.get('isPaid') && ! vm.get('isInReg') && !hasWorkReleaseIsInReg
				&& !hasWorkReleaseIsPaid /*vm.get('hasWorkReleaseIsPaid')*/ )) {
				// Все разделы ЭЛН без признака «Принят ФСС» (IsPaid) или «В реестре» (IsInReg) Регион: Все, кроме Казахстана
				if (Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {

					// Основной раздел
					mainPanelAccess = true;

					// Основной раздел, но только поля Номер, Организация, Наименование для печати
					mainPanelSomeFieldsAccess = true;

					//Основной раздел, поля СКЛ
					mainPanelSSTFieldsAccess = true;
				}
			}

			if (getRegionNick() == 'kz' || ( !vm.get('isPaid') && !vm.get('isInReg')) ) {
				// У ЭЛН нет признака «Принят ФСС» (IsPaid) И «В реестре» (IsInReg) Регион: Все, кроме Казахстана
				if (Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
					// Список пациентов нуждающихся в уходе, МСЭ
					carePersonMSEAccess = true;
				}
			}
			
			if ( !vm.get('isPaid') && !vm.get('isInReg')) {
				// Освобождение от работы
				workReleaseAccess = true;
			}
		}
		// если Регистратор, то
		if (isPolkaRegistrator()) {
			if (getRegionNick() == 'kz' || (!vm.get('isPaid') && !vm.get('isInReg') 
				&& !hasWorkReleaseIsInReg && !hasWorkReleaseIsPaid)) {
				// Все разделы ЭЛН без признака «Принят ФСС» (IsPaid) или «В реестре» (IsInReg) Регион: Все, кроме Казахстана
				// Только поля: Номер, Организация, Наименование для печати
				if (Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
					mainPanelSomeFieldsAccess = true;
				}
			}
		}
		// если Врач или Председатель ВК то
		if (this.isVrach() || this.isVrachVK()) {

			if (
				(getRegionNick() == 'kz' || (!vm.get('isPaid') && !vm.get('isInReg') 
				&& !hasWorkReleaseIsInReg && !hasWorkReleaseIsPaid))
			) {
				if (Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
					// Все разделы ЭЛН без признака «Принят ФСС» (IsPaid) или «В реестре» (IsInReg) Регион: Все, кроме Казахстана
					mainPanelSomeFieldsAccess = true;
					if (vm.get('action') == 'add' || firstMedPersonal_id == getGlobalOptions().medpersonal_id) {
						// Только если врач указан в первом освобождении от работы как врач 1 или ЛВН открыт на добавление.
						mainPanelAccess = true;
						mainPanelSSTFieldsAccess = true;
					}
				}
			}

			if (getRegionNick() == 'kz' || ( ! vm.get('isPaid') && ! vm.get('isInReg'))) {
				// У ЭЛН нет признака «Принят ФСС» (IsPaid) И «В реестре» (IsInReg) Регион: Все, кроме Казахстана
				if (Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
					carePersonMSEAccess = true;
				}
			}
			
			if (!vm.get('isPaid') && !vm.get('isInReg')) {
				workReleaseAccess = true;
			}

			// доступность разделов если в освобождении указано рабочее место из МО пользователя #135678
			if (getRegionNick() == 'kz' && vm.get('MedStaffFactInUserLpu')) {
				workReleaseAccess = true;
				mainPanelAccess = true;
				carePersonMSEAccess = true;
			}
		}

		if (getRegionNick() == 'kz' || (!vm.get('isPaid') && !vm.get('isInReg') && !hasWorkReleaseIsInReg && !hasWorkReleaseIsPaid)) {
			if (base_form.findField('Org_did').getValue() == getGlobalOptions().org_id) {
				mainPanelSSTFieldsAccess = true;
			}
		}
		
		if(getRegionNick() != 'kz' && getGlobalOptions().lpu_id != win.Lpu_id) {
			carePersonMSEAccess = false;
			if(base_form.findField('Org_did').getValue() != getGlobalOptions().org_id) {
				workReleaseAccess = false;
			}
		}
		
		vm.set('hasWorkReleaseIsPaid', hasWorkReleaseIsPaid);
		vm.set('hasWorkReleaseIsInReg', hasWorkReleaseIsInReg);
		
		vm.set('mainPanelAccess', mainPanelAccess);
		vm.set('mainPanelSomeFieldsAccess', mainPanelSomeFieldsAccess);
		vm.set('mainPanelSomeMainFieldsKodyNetrudAccess', mainPanelSomeMainFieldsKodyNetrudAccess);
		vm.set('mainPanelSSTFieldsAccess', mainPanelSSTFieldsAccess);
		vm.set('carePersonMSEAccess', carePersonMSEAccess);
		vm.set('workReleaseAccess', workReleaseAccess);
		
		/* //vm.get('mainPanelAccess');
		var enableField = this.enableField;
		var disableField = this.disableField;

		
		// основной раздел
		if(vm.get('mainPanelAccess')){
			win.getFields(win.formMainFields).forEach(enableField);
		} else {
			win.getFields(win.formMainFields).forEach(disableField);
		}

		// некоторые поля основного раздела: Номер, Организация, Наименование для печати
		if(vm.get('mainPanelSomeFieldsAccess')){
			win.getFields(win.formSomeMainFields).forEach(enableField);
		} else {
			win.getFields(win.formSomeMainFields).forEach(disableField);
		}


		// некоторые поля основного раздела: Код изм. нетрудоспособности и Доп. код нетрудоспособности
		if(win.mainPanelSomeMainFieldsKodyNetrudAccess){
			win.getFields(win.formSomeMainFields_KodyNetrud).forEach(enableField);
		} else {
			win.getFields(win.formSomeMainFields_KodyNetrud).forEach(disableField);
		}


		// Основной раздел, поля СКЛ
		if (win.mainPanelSSTFieldsAccess) {
			win.getFields(win.formSSTFields).forEach(enableField);
		} else {
			win.getFields(win.formSSTFields).forEach(disableField);
		}

		// Список пациентов нуждающихся в уходе, МСЭ
		if (win.carePersonMSEAccess) {
			//~ win.queryById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].enable();
			win.getFields('EStEF_MSEPanel').forEach(enableField);
		}
		else {
			//~ win.queryById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].disable();
			win.getFields('EStEF_MSEPanel').forEach(disableField);
		}

		// Освобождение от работы
		if(win.workReleaseAccess) {
			work_release_grid.getTopToolbar().items.items[0].enable();
		}
		else {
			work_release_grid.getTopToolbar().items.items[0].disable();
		}
*/
		//~ win.panelEvnStickCarePerson.tools.plusbutton.setVisible(carePersonMSEAccess);
		//~ win.panelEvnStickWorkRelease.tools.plusbutton.setVisible(workReleaseAccess);
		

		// -------------------------------------------------------------------------------------------------------------
		// Исход ЛВН
		// -------------------------------------------------------------------------------------------------------------

		// Закрываем доступ
		this._closeAccessToPanelStickLeave();

		// Проверяем доступность блока и открываем доступ при необходимости
		this._checkAccessToPanelStickLeave();//ext6: убрал this._openAccessToPanelStickLeave();
		

		// -------------------------------------------------------------------------------------------------------------
		// Кнопка "Подписать" в блоке "Исход ЛВН"
		// -------------------------------------------------------------------------------------------------------------

		// Закрываем доступ
		this._closeAccessToField_StickLeave_Sign();

		// Проверяем доступность блока и открываем доступ при необходимости
		if(this._checkAccessToField_StickLeave_Sign() == true){
			this._openAccessToField_StickLeave_Sign();
		}
		// -------------------------------------------------------------------------------------------------------------
		// Блок МСЭ
		// -------------------------------------------------------------------------------------------------------------

		// поля: Дата регистрации документов в бюро МСЭ, Дата освидетельствования в бюро МСЭ, Установлена/изменена группа инвалидности
		if( !this.checkIsLvnELN() ) {
			base_form.findField('EvnStick_mseRegDate').enable();
			base_form.findField('EvnStick_mseExamDate').enable();
			base_form.findField('InvalidGroupType_id').enable();
		} else {
			base_form.findField('EvnStick_mseRegDate').disable();
			base_form.findField('EvnStick_mseExamDate').disable();
			base_form.findField('InvalidGroupType_id').disable();
		}

		// Дата направления в бюро МСЭ
		if (
			base_form.findField('EvnStick_IsDateInReg').getValue() == 2
			|| base_form.findField('EvnStick_IsDateInFSS').getValue() == 2
		) {
			base_form.findField('EvnStick_mseDate').disable();
		} else {
			base_form.findField('EvnStick_mseDate').enable();
		}
		// -------------------------------------------------------------------------------------------------------------
		// Блок Режим:  Дата начала, Дата окончания лечения в стационаре
		// -------------------------------------------------------------------------------------------------------------

		// Закрываем доступ
		this._closeAccessToPanelStickRegime();

		// Проверяем доступность блока и открываем доступ при необходимости
		// ext2: ВАЖНО!!! - Не забываем что при открытии блока мы так же проверяем доступность каждого
		// 		 поля в методе checkFieldDisabled()
		// ext6: метод checkFieldDisabled убран, доступность полей см. в bind
		this._checkAccessToPanelStickRegime(); // ext6: вместе с open <- bind
		
		this._closeAccessToField_swSignStickIrr();
		this._checkAccessToField_swSignStickIrr();// ext6: вместе с open <- bind

		// Дата начала
		this._checkAccessToField_EvnStick_stacBegDate();//вместо:
		/*this._closeAccessToField_EvnStick_stacBegDate();
		if(this._checkAccessToField_EvnStick_stacBegDate() == true){
			this._openAccessToField_EvnStick_stacBegDate();
		}*/

		// кнопка "=" рядом с полем "Дата начала"
		this._checkAccessToField_EStEF_btnSetMinDateFromPS();//вместо:
		/*this._closeAccessToField_EStEF_btnSetMinDateFromPS();
		if(this._checkAccessToField_EStEF_btnSetMinDateFromPS() == true){
			me._openAccessToField_EStEF_btnSetMinDateFromPS();
		}*/

		// Дата окончания
		this._checkAccessToField_EvnStick_stacEndDate();//вместо:
		/*this._closeAccessToField_EvnStick_stacEndDate();
		if(this._checkAccessToField_EvnStick_stacEndDate() == true){
			me._openAccessToField_EvnStick_stacEndDate();
		}*/

		// кнопка "=" рядом с полем "Дата окончания" 
		this._checkAccessToField_EStEF_btnSetMaxDateFromPS();//вместо:
		/*._closeAccessToField_EStEF_btnSetMaxDateFromPS();
		if(me._checkAccessToField_EStEF_btnSetMaxDateFromPS() == true){
			._openAccessToField_EStEF_btnSetMaxDateFromPS();
		}*/
	},
	
	_checkAccessToField_EStEF_btnSetMaxDateFromPS: function(){
		eval(this.ini);
		var isOpen = false;

		// Оператор
		if(this.isOperator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				var checkUslovie3_1 = false;
				var checkUslovie3_2 = false;

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3_1 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3_1 = true;
						}
					}

				}

				// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
				// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
				// «дата окончания» не пустые.
				if(vm.get('action') == 'edit'){

					var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
					var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

					if( ! Ext6.isEmpty(stacBegDate) || ! Ext6.isEmpty(stacEndDate)){
						checkUslovie3_2 = true;
					}
				}

				if(checkUslovie3_1 == true && checkUslovie3_2 == true){
					checkUslovie3 = true;
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Статистик
		if(this.isStatistick() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				var checkUslovie3_1 = false;
				var checkUslovie3_2 = false;

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3_1 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3_1 = true;
						}
					}

				}

				// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
				// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
				// «дата окончания» не пустые.
				if(vm.get('action') == 'edit'){

					var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
					var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

					if( ! Ext6.isEmpty(stacBegDate) || ! Ext6.isEmpty(stacEndDate)){
						checkUslovie3_2 = true;
					}
				}

				if(checkUslovie3_1 == true && checkUslovie3_2 == true){
					checkUslovie3 = true;
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}

		}

		// Регистратор ЛВН
		if(this.isRegistratorLVN() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3 = true;
						}
					}
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Регистратор
		if(this.isRegistrator() == true){
			//isOpen = false;
		}

		// Врач
		if(this.isVrach() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3 = true;
						}
					}
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}

		}

		// Врач, регистратор (одновременно)
		if(this.isVrachAndRegistrator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3 = true;
						}
					}
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		vm.set('isAccessToField_EStEF_btnSetMaxDateFromPS', isOpen);//ext6
		return isOpen;
	},

	_setFormFields: function(){
		eval(this.ini);
		this.suspendChange(1);
		base_form.setValues(me.params.formParams);
		this.suspendChange(0);
		//~ base_form.findField('UAddress_AddressText').triggers[2].hide();//триггера пока нет
		base_form.findField('EvnStick_IsOriginal').setValue(false);//1
		base_form.findField('CountDubles').setValue(0);
		base_form.findField('EvnStick_oid').setAllowBlank(true);
		base_form.findField('EvnStick_oid').hide();
		base_form.findField('EvnStick_StickDT').setAllowBlank(true);
		base_form.findField('EvnStick_StickDT').hide();
		base_form.findField('EvnStick_adoptDate').hide();


		if (getRegionNick() == 'kz' && vm.get('action') != 'add') {
			base_form.findField('StickCauseDopType_id').hide();
			base_form.findField('StickCause_did').hide();

			//определяем указано ли в освобождении рабочее место из МО пользователя #135678
			Ext6.Ajax.request({
				url: '/?c=Stick&m=checkWorkReleaseMedstaffFact',
				params: {
					EvnStick_id: me.EvnStick_id
				},
				success: function(response) {
					var result = Ext6.util.JSON.decode(response.responseText);
					me.MedStaffFactInUserLpu = result.MedStaffFactInUserLpu;

				}
			});
		}

		if(me.params.PersonEvn_id) {
			base_form.findField('PersonEvn_id').setValue(me.params.PersonEvn_id);
		}

		if(me.params.Person_id) {
			base_form.findField('Person_id').setValue(me.params.Person_id);
		}

		base_form.findField('EvnStickBase_IsFSS').setValue(false);
		if (!Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			base_form.findField('EvnStickBase_IsFSS').setValue(true);
		}
		base_form.findField('EvnStickDop_pid').getStore().removeAll();

		// зависит от panelStickLeave
		// base_form.findField('MedStaffFact_id').getStore().removeAll();

		if (getRegionNick() == 'kz') {
			base_form.findField('EvnStick_Ser').setAllowBlank( ! me.StickReg);
			base_form.findField('EvnStick_Num').setAllowBlank( ! me.StickReg);
			// #136679 Регион: Казахстан Длина номера составляет 7 цифр
			base_form.findField('EvnStick_Num').maxLength = 7;
			base_form.findField('EvnStick_Num').minLength = 7;
		}

		base_form.findField('EvnStick_Ser').setContainerVisible(getRegionNick() == 'kz');
		base_form.findField('EvnStick_sstBegDate').enable();
		
		if(vm.get('action') != 'add') {
			Ext6.Ajax.request({
				url: '/?c=Stick&m=checkELN',
				params: {
					EvnStick_id: me.EvnStick_id
				},
				success: function(response) {
					var result = Ext6.util.JSON.decode(response.responseText);
					me.isELN = result.isELN;
					
					if(getRegionNick() != 'kz'){
						if(me.isELN && !Ext6.isEmpty(me.queryById('buttonPrintTruncELN'))){
							me.queryById('buttonPrintTruncELN').enable();
						}
					}
				}
			});
		} else {
			Ext6.Ajax.request({//заполняем поле "Дата направления в бюро МСЭ" значением из направления
				url: '/?c=Mse&m=getEvnMse',
				params: {
					EvnPL_id: me.EvnStick_pid 
				},
				success: function(response) {
					var result = Ext6.util.JSON.decode(response.responseText);
					
					if (result[0] && result[0].EvnPrescrMse_issueDT) {
						base_form.findField('EvnStick_mseDate').setValue(result[0].EvnPrescrMse_issueDT);
					}
				}
			});
		}

		return me;
	},

	doHideForm: function() {//+
		var win = this.getView();
		win.hide();
		return win;
	},

	checkOrgFieldDisabled: function() {//+
		eval(this.ini);
		if (
			getRegionNick() != 'kz' // кроме Казахстана
			&& !Ext6.isEmpty(base_form.findField('RegistryESStorage_id').getValue()) // Номер ЛВН получен из хранилища номеров ЭЛН
			&& base_form.findField('StickWorkType_id').getValue()
			&& base_form.findField('StickWorkType_id').getValue().inlist([1, 2]) // Тип занятости выбрано значение: «основная работа» или  «работа по совместительству»
		) {
			base_form.findField('Org_id').setAllowBlank(false);
			base_form.findField('EvnStick_OrgNick').setAllowBlank(false);
		} else {
			base_form.findField('Org_id').setAllowBlank(true);
			base_form.findField('EvnStick_OrgNick').setAllowBlank(true);
		}
	},

	checkConsentInAnotherLvn: function () {//+
		eval(this.ini);
		var EvnStickDop = base_form.findField('EvnStickDop_pid'),
			EvnStickDop_pid = EvnStickDop.getValue();

		if (EvnStickDop_pid < 0) {
			vm.set('ConsentInAnotherLvn', false);
			return false;
		}

		var index = EvnStickDop.getStore().find('EvnStick_id', EvnStickDop_pid);

		if (index === -1) {
			vm.set('ConsentInAnotherLvn', false);
			return false;
		}

		var record = EvnStickDop.getStore().getAt(index);

		if ( Ext6.isEmpty(record.get('EvnStickBase_consentDT')) ) {
			vm.set('ConsentInAnotherLvn', false);
			return false;
		}
		vm.set('ConsentInAnotherLvn', true);
		return true;
	},

	

	checkIsLvnOpenFromKVS: function(){// Проверяем открыт ли ЛВН из КВС
		eval(this.ini);
		var isFromKVS = false;

		if(me.parentClass == 'EvnPS'){
			isFromKVS = true;
		}

		return isFromKVS;
	},

	_checkAccessToField_EvnStick_stacBegDate: function(){/// 
		//сразу устанавливает нужный isAccessToField_EvnStick_stacBegDate
		//поэтому метод _openAccessToField_EvnStick_stacBegDate не нужен
		eval(this.ini);
		var	isOpen = false;

		// Оператор +
		if(this.isOperator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				var checkUslovie3_1 = false;
				var checkUslovie3_2 = false;

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3_1 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3_1 = true;
						}
					}

				}

				// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
				// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
				// «дата окончания» не пустые.
				if(vm.get('action') == 'edit'){

					var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
					var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

					if( ! Ext6.isEmpty(stacBegDate) || ! Ext6.isEmpty(stacEndDate)){
						checkUslovie3_2 = true;
					}
				}


				if(checkUslovie3_1 == true || checkUslovie3_2 == true){
					checkUslovie3 = true;
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Статистик +
		if(this.isStatistick() == true){

			var checkUslovie1 = false,
				checkUslovie2 = false,
				checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if( vm.get('isPaid') == false && vm.get('isInReg') == false ){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				var checkUslovie3_1 = false;
				var checkUslovie3_2 = false;

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3_1 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3_1 = true;
						}
					}

				}

				// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
				// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
				// «дата окончания» не пустые.
				if(vm.get('action') == 'edit'){

					var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
					var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

					if( ! Ext6.isEmpty(stacBegDate) || ! Ext6.isEmpty(stacEndDate)){
						checkUslovie3_2 = true;
					}
				}


				if(checkUslovie3_1 == true || checkUslovie3_2 == true){
					checkUslovie3 = true;
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true  && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Регистратор ЛВН +
		if(this.isRegistratorLVN() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС
				
				var checkUslovie3_1 = false;
				var checkUslovie3_2 = false;

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3_1 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3_1 = true;
						}
					}
				}
				
				// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
				// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
				// «дата окончания» не пустые.
				if(vm.get('action') == 'edit'){

					var stacBegDate = base_form.findField('EvnStick_stacBegDate').getValue();
					var stacEndDate = base_form.findField('EvnStick_stacEndDate').getValue();

					if( ! Ext6.isEmpty(stacBegDate) || ! Ext6.isEmpty(stacEndDate)){
						checkUslovie3_2 = true;
					}
				}

				if(checkUslovie3_1 == true || checkUslovie3_2 == true){
					checkUslovie3 = true;
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Регистратор
		if(this.isRegistrator() == true){
			//isOpen = false;
		}

		// Врач
		if(this.isVrach() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС
				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){
					if(getRegionNick() != 'kz'){

						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3 = true;
						}
					}
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}

		}

		// Врач, регистратор (одновременно)
		if(this.isVrachAndRegistrator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(vm.get('isPaid') == false && vm.get('isInReg') == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(this.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// 3. Правила в п. 2.1.9. Для ЛВН из ФСС не учитывается.
			if(this.checkIsLvnFromFSS() == false){ // ЛВН не из ФСС

				// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
				if(this.checkIsLvnOpenFromKVS() == true){

					if(getRegionNick() != 'kz'){
						if(this.checkHasDvijeniaInStac24() == true){
							checkUslovie3 = true;
						}

						// (#128729 Для Казахстана тип стационара не учитывается)
					} else {
						if(this.checkHasDvijenia() == true){
							checkUslovie3 = true;
						}
					}
				}

			} else {
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}
		
		if(vm.get('isAccessToField_EvnStick_stacBegDate') != isOpen)
			vm.set('isAccessToField_EvnStick_stacBegDate', isOpen);//ext6
		return isOpen;
	},

	/*_closeAccessToField_EvnStick_stacBegDate: function(){//поглощено _checkAccessToField_EvnStick_stacBegDate
		eval(this.ini);

		vm.set('isAccessToField_EvnStick_stacBegDate', false);
		me.FormPanel.getForm().findField('EvnStick_stacBegDate').disable();

		return me;
	},*/

	/*_openAccessToField_EvnStick_stacBegDate: function(){//поглощено _checkAccessToField_EvnStick_stacBegDate
		eval(this.ini);

		vm.set('isAccessToField_EvnStick_stacBegDate', true);
		me.FormPanel.getForm().findField('EvnStick_stacBegDate').enable();

		return me;
	},*/

	_applyValueToFields_EvnStick_oid: function(newValue){
		/**
		 * Функция выбора оригинала если выбран тип текущего ЛВН - "Дубликат"
		 * Если выбранное значение поля "EvnStick_oid", то изменяем значения других полей которые зависят от поля EvnStick_oid
		 *
		 * @param record
		 * @returns {sw.Promed.swEvnStickEditWindow}
		 * @private
		 */
		eval(this.ini);

		var index = base_form.findField('EvnStick_oid').getStore().findBy(function(rec) {
			return (rec.get('EvnStick_id') == newValue);
		});
		var record = base_form.findField('EvnStick_oid').getStore().getAt(index);
		win.dataFromOriginal = record;

		// если запись найдена и существует
		if(record && record.get('EvnStick_id')){
			// Устанавливаем значения полей
			base_form.findField('EvnStick_disDate').setValue(Date.parseDate(record.get('EvnStick_disDate'), 'd.m.Y'));
			base_form.findField('EvnStick_IsDisability').setValue(record.get('EvnStick_IsDisability'));
			base_form.findField('InvalidGroupType_id').setValue(record.get('InvalidGroupType_id'));
			base_form.findField('EvnStick_StickDT').setValue(record.get('EvnStick_StickDT'));
			base_form.findField('EvnStick_mseDate').setValue(Date.parseDate(record.get('EvnStick_mseDate'), 'd.m.Y'));
			base_form.findField('EvnStick_mseExamDate').setValue(Date.parseDate(record.get('EvnStick_mseExamDate'), 'd.m.Y'));
			base_form.findField('EvnStick_mseRegDate').setValue(Date.parseDate(record.get('EvnStick_mseRegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_prid').setValue(record.get('EvnStick_prid'));
			base_form.findField('EvnStick_nid').setValue(record.get('EvnStick_nid'));
			base_form.findField('EvnStick_setDate').setValue(Date.parseDate(record.get('EvnStick_setDate'), 'd.m.Y'));
			base_form.findField('EvnStick_stacBegDate').setValue(Date.parseDate(record.get('EvnStick_stacBegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_stacEndDate').setValue(Date.parseDate(record.get('EvnStick_stacEndDate'), 'd.m.Y'));
			
			if (!record.get('StickCause_SysNick').inlist(['karantin', 'uhod', 'uhodnoreb', 'uhodreb', 'rebinv', 'postvaccinal', 'vich'])) {
				base_form.findField('EvnStickFullNameText').setValue(record.get('Person_Fio'));
				base_form.findField('PersonSnils').setValue(record.get('Person_Snils'));
				base_form.findField('Person_id').setValue(record.get('Person_id'));
				base_form.findField('PersonEvn_id').setValue(record.get('PersonEvn_id'));
				base_form.findField('Server_id').setValue(record.get('Server_id'));

				me.Person_Snils = record.get('Person_Snils');
				_this._checkSnils();
				
			} else {
				base_form.findField('EvnStickFullNameText').enable();
			}

			base_form.findField('EvnStickFullNameText').setValue(record.get('Person_Fio'));
			vm.set('EvnStickFullNameText',record.get('Person_Fio'));

			base_form.findField('EvnStickLast_id').setValue(record.get('EvnStickLast_id')); //todo: проверить что работает. Было на EvnStickLast_Title
			base_form.findField('Lpu_oid').setValue(record.get('Lpu_oid'));
			base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
			base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
			//~ base_form.findField('Person_id').setValue(record.get('Person_id'));//перешло выше в StickCause_SysNick .inlist
			//~ base_form.findField('PersonEvn_id').setValue(record.get('PersonEvn_id'));
			//~ base_form.findField('Server_id').setValue(record.get('Server_id'));
			base_form.findField('StickCause_did').setValue(record.get('StickCause_did'));

			base_form.findField('StickCause_id').setValue(record.get('StickCause_id'));
			base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), base_form.findField('StickCause_id').getValue());

			base_form.findField('StickIrregularity_id').setValue(record.get('StickIrregularity_id'));
			base_form.findField('StickIrregularity_id').fireEvent('change', base_form.findField('StickIrregularity_id'), base_form.findField('StickIrregularity_id').getValue());

			base_form.findField('EvnStick_irrDate').setValue(Date.parseDate(record.get('EvnStick_irrDate'), 'd.m.Y'));
			base_form.findField('EvnStick_IsRegPregnancy').setValue(record.get('EvnStick_IsRegPregnancy'));
			base_form.findField('EvnStick_BirthDate').setValue(Date.parseDate(record.get('EvnStick_BirthDate'), 'd.m.Y'));
			base_form.findField('StickCauseDopType_id').setValue(record.get('StickCauseDopType_id'));

			// Исход ЛВН
			vm.set('fromStickLeave','orig');
			base_form.findField('StickLeaveType_id').setValue(record.get('StickLeaveType_id'));

			base_form.findField('StickOrder_id').setValue(record.get('StickOrder_id'));
			base_form.findField('Post_Name').setValue(record.get('Post_Name'));
			base_form.findField('EvnStick_OrgNick').setValue(record.get('EvnStick_OrgNick'));
			base_form.findField('EvnStickBase_consentDT').setValue(record.get('EvnStickBase_consentDT'));
			base_form.findField('Org_id').setValue(record.get('Org_id'));
			base_form.findField('EvnStick_sstBegDate').setValue(Date.parseDate(record.get('EvnStick_sstBegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_sstEndDate').setValue(Date.parseDate(record.get('EvnStick_sstEndDate'), 'd.m.Y'));
			base_form.findField('EvnStick_sstNum').setValue(record.get('EvnStick_sstNum'));

			base_form.findField('StickWorkType_id').setValue(record.get('StickWorkType_id'));
			if (record.get('StickWorkType_id') == 2) {
				base_form.findField('EvnStickDop_pid').setValue(record.get('EvnStickDop_pid'));
				me.evnStickType = 2;
			} else {
				me.evnStickType = 1;
			}
			base_form.findField('StickWorkType_id').fireEvent('change', base_form.findField('StickWorkType_id'), base_form.findField('StickWorkType_id').getValue());

			if ( record.get('Org_id') && Number(record.get('Org_id')) > 0 ) {
				this.loadField_Org_id(record.get('Org_id'));
			}

			if ( record.get('Org_did') && Number(record.get('Org_did')) > 0 ) {
				this._loadStore_Org_did(record.get('Org_did'));
			}

			// подгружаем пациентов нуждающихся в уходе
			this._loadStoreEvnStickCarePerson({
				EvnStick_id: record.get('EvnStick_GridId')
			});

			if(vm.get('action') == 'add'){
				// подгружаем только один период освобождения (общий)
				this._load_WorkRelease({
					EvnStick_id: record.get('EvnStick_GridId'),
					LoadSummPeriod: '1'
				});
			}

			// получаем и устанавливаем номер продолжения
			// @TODO ext2 разбить на 2 отдельных функции
			this.fetchAndSetEvnStickProd(record.get('EvnStick_id'));

			this.changeStickCauseDopType();
		}
		else {
			base_form.findField('EvnStick_BirthDate').setRawValue('');
			base_form.findField('EvnStick_disDate').setRawValue('');
			base_form.findField('EvnStick_irrDate').setRawValue('');
			base_form.findField('EvnStick_IsDisability').clearValue();
			base_form.findField('InvalidGroupType_id').reset();
			base_form.findField('EvnStick_StickDT').setRawValue('');
			base_form.findField('EvnStick_IsRegPregnancy').clearValue();
			base_form.findField('EvnStick_mseDate').setRawValue('');
			base_form.findField('EvnStick_mseExamDate').setRawValue('');
			base_form.findField('EvnStick_mseRegDate').setRawValue('');
			base_form.findField('EvnStick_setDate').setRawValue('');
			base_form.findField('EvnStick_sstBegDate').setRawValue('');
			base_form.findField('EvnStick_sstEndDate').setRawValue('');
			base_form.findField('EvnStick_sstNum').setRawValue('');
			base_form.findField('EvnStick_stacBegDate').setRawValue('');
			base_form.findField('EvnStick_stacEndDate').setRawValue('');
			base_form.findField('UAddress_AddressText').setRawValue('');
			base_form.findField('Lpu_oid').reset();
			base_form.findField('MedStaffFact_id').reset();
			base_form.findField('Org_did').reset();
			base_form.findField('Org_id').reset();
			base_form.findField('EvnStick_OrgNick').setRawValue('');
			base_form.findField('StickCause_did').reset();

			base_form.findField('StickCause_id').reset();
			base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), base_form.findField('StickCause_id').getValue());

			base_form.findField('StickCauseDopType_id').reset();
			base_form.findField('StickIrregularity_id').reset();

			// Исход ЛВН
			vm.set('fromStickLeave','orig');
			base_form.findField('StickLeaveType_id').reset();

			base_form.findField('StickOrder_id').reset();
			base_form.findField('EvnStick_oid').reset();

			// Очищаем списки пациентов, нуждающихся в уходе
			this._clearEvnStickCarePersonGrid();

			// Очищаем списки освобождений от работы
			this._removeAll_WorkRelease();
		}

		// т.к. мы подтянули исход из оригинала или очистили его при отмене выбора оригинала, то вызываем событие change
		base_form.findField('StickLeaveType_id').fireEvent('change', base_form.findField('StickLeaveType_id'), base_form.findField('StickLeaveType_id').getValue());

		base_form.findField('StickOrder_id').fireEvent('change', base_form.findField('StickOrder_id'), base_form.findField('StickOrder_id').getValue());

		return me;
	},

	_loadStore_EvnStick_oid: function(callback){// Загрузка списка оригиналов
		eval(this.ini);
		var EvnStick_id = base_form.findField('EvnStick_id').getValue();
		
		base_form.findField('EvnStick_oid').getStore().load({
			callback: function() {
				if(callback){
					callback();
				}
			},
			params: {
				'EvnStick_mid': base_form.findField('EvnStick_mid').getValue(),
				'EvnStick_id': base_form.findField('EvnStick_id').getValue()
			}
		});
	},

	_setDefaultValueTo_Post_Name: function(){//+
		eval(this.ini);
		var Person_id = base_form.findField('Person_id').getValue();

		if(getRegionNick() != 'kz'){
			if(	// Тип занятости: основная работа
				base_form.findField('StickWorkType_id').getValue() == 1 &&
				// оригинал
				base_form.findField('EvnStick_IsOriginal').getRawValue() == 1
			){
				var jobInfo = this.getPersonJobInfo(Person_id);

				if(jobInfo && ! Ext6.isEmpty(jobInfo.Post_Name)){
					base_form.findField('Post_Name').setValue(jobInfo.Post_Name);
				}
			}
		} else if(getRegionNick() == 'kz'){
			if( //оригинал
				base_form.findField('EvnStick_IsOriginal').getRawValue() == 1
			){
				var jobInfo = this.getPersonJobInfo(Person_id);
				if(jobInfo && ! Ext6.isEmpty(jobInfo.Post_Name)){
					base_form.findField('Post_Name').setValue(jobInfo.Post_Name);
				}
			}
		}

		return true;
	},

	_getSelectedRecord_EvnStick_oid: function(){// Поле "Оригинал ЛВН" (EvnStick_oid)
		eval(this.ini);
		var EvnStick_oid = base_form.findField('EvnStick_oid').getValue();

		var index = base_form.findField('EvnStick_oid').getStore().findBy(function(rec) {
			return rec.get('EvnStick_id') == EvnStick_oid;
		});

		return base_form.findField('EvnStick_oid').getStore().getAt(index);
	},

	onLoadEvnStick_oid: function() {
		eval(this.ini);
		this.refreshFormPartsAccess();
		//TODO: откуда остальное?:
		var field = me.FormPanel.getForm().findField('EvnStick_oid');

		// если в списке только 1 значение, то сразу его выбираем
		if (field.getStore().getCount() == 1 && vm.get('action') == 'add') {
			var newValue = field.getStore().getAt(0).get('EvnStick_id');
			field.setValue(newValue);

			// функция выбора оригинала
			this._applyValueToFields_EvnStick_oid(newValue);
		} else {
			field.setValue(field.getValue());
		}
	},

	_clearEvnStickCarePersonGrid: function(){
		var me = this.getView();
		me.queryById('EStEF_EvnStickCarePersonGrid').getStore().removeAll();
		return me;
	},

	_setDefaultValueTo_Org_id: function(){//+ Организация (Место работы)
		eval(this.ini);
		var Person_id = base_form.findField('Person_id').getValue();

		if(getRegionNick() != 'kz'){
			if(
				Person_id &&
				// Тип занятости: основная работа
				base_form.findField('StickWorkType_id').getValue() == 1 &&
				// оригинал
				base_form.findField('EvnStick_IsOriginal').getRawValue() == 1
			){
				var jobInfo = this.getPersonJobInfo(Person_id);
				if(jobInfo && ! Ext6.isEmpty(jobInfo.Org_id)){
					this.loadField_Org_id(jobInfo.Org_id);
				}
			}
		} else if(getRegionNick() == 'kz'){
			if(
				Person_id &&
				// оригинал
				base_form.findField('EvnStick_IsOriginal').getRawValue() == 1
			){
				var jobInfo = this.getPersonJobInfo(Person_id);
				if(jobInfo && ! Ext6.isEmpty(jobInfo.Org_id)){
					this.loadField_Org_id(jobInfo.Org_id);
				}
			}
		}
		return true;
	},

	openEvnStickCarePersonEditWindow: function(action) {//"уход за", "список пациентов, нуждающихся в уходе" - действия в меню
		eval(this.ini);
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view'])) ) {
			return false;
		}

		if ( vm.get('action') == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnStickCarePersonEditWindow').isVisible() ) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Окно редактирования пациента, нуждающегося в уходе, уже открыто'));
			return false;
		}

		var base_form = win.FormPanel.getForm();
		var grid = win.queryById('EStEF_EvnStickCarePersonGrid');
		var params = new Object();

		if ( action == 'add' ) {
			if ( grid.getStore().getCount() >= 2 ) {
				Ext6.Msg.alert(langs('Ошибка'), langs('Разрешено добавление только 2-х записей о пациентах, нуждающихся в уходе'));
				return false;
			}
		}

		params.action = action;
		params.evnStickSetDate = base_form.findField('EvnStick_setDate').getValue();
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.evnStickCarePersonData != 'object' ) {
				return false;
			}

			data.evnStickCarePersonData.RecordStatus_Code = 0;
			var record = grid.getStore().getAt(grid.recordMenu.data_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.evnStickCarePersonData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().model.fields.forEach(function(field) {
					grid_fields.push(field.name);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnStickCarePersonData[grid_fields[i]]);
				}
				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnStickCarePerson_id') ) {
					grid.getStore().removeAll();
				}

				/*var tmpId = 0;
				do {
					tmpId = -Math.floor(Math.random() * 1000000);
				} while (grid.getStore().find('EvnStickCarePerson_id', tmpId)>=0);*/
				data.evnStickCarePersonData.EvnStickCarePerson_id = -this.swGenTempId(grid.getStore(), 'EvnStickCarePerson_id');

				grid.getStore().loadData([ data.evnStickCarePersonData ], true);
			}
			win.panelEvnStickCarePerson.refreshTitle();

			_this.checkRebUhod();
		}
		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				
			};
			params.formParams.Person_pid = base_form.findField('Person_id').getValue();
		}
		else {
			if ( !grid || !grid.recordMenu || Ext6.isEmpty(grid.recordMenu.data_id) || grid.recordMenu.data_id<0 || !grid.getStore().getAt(grid.recordMenu.data_id) ) {
				return false;
			}

			var record = grid.getStore().getAt(grid.recordMenu.data_id);

			params.formParams = record.data;
			params.onHide = function() {
				
			};
		}
		getWnd('swEvnStickCarePersonEditWindow').show(params);
	},

	_loadStore_Org_did: function(Org_did){//+
		eval(this.ini);

		if(Ext6.isEmpty(Org_did) || Org_did == undefined){
			return me;
		}

		base_form.findField('Org_did').getStore().load({
			callback: function(records, options, success) {
				if ( success ) {
					base_form.findField('Org_did').setValue(Org_did);
				}
			},
			params: {
				Org_id: Org_did,
				OrgType: 'org'
			}
		});
	},

	_loadStoreEvnStickCarePerson: function(params, callback){//+
		var me = this.getView();
		me.queryById('EStEF_EvnStickCarePersonGrid').getStore().load({
			params: params,
			callback: callback
		});

		return me;
	},

	_getStoreEvnStickCarePerson: function(){
		return this.getView().queryById('EStEF_EvnStickCarePersonGrid').getStore();
	},

	_isFocusAccessCarePersonPanel: function(){//+
		eval(this.ini);
		if(
			! win.queryById('EStEF_EvnStickCarePersonPanel').hidden &&
			! win.queryById('EStEF_EvnStickCarePersonPanel').collapsed &&
			win.queryById('EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0
		) {
			return true;
		}

		return false;
	},

	getStickRegimeId: function() {//+
		eval(this.ini);
		var StickRegime_id = null;

		if (getRegionNick() == 'kz') {
			if (base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'kurort') {
				StickRegime_id = 4;
			} else if (me.parentClass.inlist(['EvnPL','EvnPLStom'])) {
				StickRegime_id = 1;
			} else if (me.parentClass.inlist(['EvnPS'])) {
				StickRegime_id = 2;
			}
		}
		return StickRegime_id;
	},
	
	filterStickCause: function() {///
		eval(this.ini);
		var stick_cause_combo = base_form.findField('StickCause_id');

		var set_date = base_form.findField('EvnStick_setDate').getValue();
		if (Ext6.isEmpty(set_date)) {
			set_date = new Date(new Date().format('Y-m-d'));
		}

		stick_cause_combo.getStore().clearFilter();
		stick_cause_combo.lastQuery = '';

		stick_cause_combo.getStore().filterBy(function(rec){
			var flag = true;
			var sysNick = rec.get('StickCause_SysNick');

			if (getRegionNick() == 'kz') {
				if (me.parentClass && me.parentClass != 'EvnPL' && sysNick.inlist(['adopt','karantin'])) {
					flag = false;
				}
				if (me.parentClass && me.parentClass != 'EvnPS' && sysNick.inlist(['protez'])) {
					flag = false;
				}
			}

			if (
				(!Ext6.isEmpty(rec.get('StickCause_begDate')) && rec.get('StickCause_begDate') > set_date) ||
				(!Ext6.isEmpty(rec.get('StickCause_endDate')) && rec.get('StickCause_endDate') < set_date)
			) {
				flag = false;
			}

			return flag;
		});
	},
	
	_getSelected_WorkRelease: function(){// Получаем выделенную запись в списке
		eval(this.ini);
		var grid = me.queryById('EStEF_EvnStickWorkReleaseGrid'),
			index = grid.recordMenu.data_id;
		if(index>=0) 
			return grid.getStore().getAt(index);
		else return false;
	},
	
	doVerifySign_WorkRelease: function(options) {// Кнопка "Верификация документа (ВК)" и "Верификация документа (Врач)"
		eval(this.ini);

		if (typeof options != 'object') {
			options = new Object();
		}

		var params = {};
		var selected_record = this._getSelected_WorkRelease();
		var SignObject = options.SignObject;
		params.SignObject = options.SignObject;

		if ( ! selected_record && ! SignObject.inlist(['leave', 'irr'])) return false;
		params.Evn_id = !SignObject.inlist(['leave', 'irr']) ? selected_record.get('EvnStickWorkRelease_id') : base_form.findField('EvnStick_id').getValue();
		params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();

		var doc_signtype = getOthersOptions().doc_signtype;
		if (doc_signtype && doc_signtype.inlist(['authapplet', 'authapi', 'authapitomee'])) {
			params.needVerifyOpenSSL = 1;
		}

		Ext6.Ajax.request({
			url: '/?c=Stick&m=verifyEvnStickSign',
			params: params,
			success: function(response, options) {
				var result = Ext6.util.JSON.decode(response.responseText);
				if (doc_signtype && doc_signtype.inlist(['authapplet', 'authapi', 'authapitomee'])) {
					if (result.verifyStatus) {
						if (result.verifyStatus == 'valid') {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: function() {
									if (!SignObject.inlist(['leave', 'irr'])) {
										_this._reload_WorkRelease();
									}
									else {
										_this.getEvnStickSignStatus({object: SignObject});
									}
								}.createDelegate(win),
								icon: Ext6.Msg.INFO,
								msg: 'Документ подписан',
								title: 'Верификация'
							});
						} else {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: function() {
									params.Signatures_id = result.Signatures_id;
									params.SignaturesStatus_id = 3;
									Ext6.Ajax.request({
										url: '/?c=Stick&m=setSignStatus',
										params: params,
										success: function(response, options) {
											if (!SignObject.inlist(['leave', 'irr'])) {
												_this._reload_WorkRelease();
											}
											else {
												_this.getEvnStickSignStatus({object: SignObject});
											}
										}
									});
								}.createDelegate(win),
								icon: Ext6.Msg.INFO,
								msg: 'Документ не актуален',
								title: 'Верификация'
							});
						}
					}
				} else {
					if (result.xml) {
						sw.Applets.CryptoPro.verifySignedXML({
							xml: result.xml,
							callback: function(success) {
								if (success) {
									Ext6.Msg.show({
										buttons: Ext6.Msg.OK,
										fn: function() {
											if (!SignObject.inlist(['leave', 'irr'])) {
												_this._reload_WorkRelease();
											}
											else {
												_this.getEvnStickSignStatus({object: SignObject});
											}
										}.createDelegate(win),
										icon: Ext6.Msg.INFO,
										msg: 'Документ подписан',
										title: 'Верификация'
									});
								} else {
									Ext6.Msg.show({
										buttons: Ext6.Msg.OK,
										fn: function() {
											params.Signatures_id = result.Signatures_id;
											params.SignaturesStatus_id = 3;
											Ext6.Ajax.request({
												url: '/?c=Stick&m=setSignStatus',
												params: params,
												success: function(response, options) {
													if ( ! SignObject.inlist(['leave', 'irr'])) {
														_this._reload_WorkRelease();
													}
													else {
														_this.getEvnStickSignStatus({object: SignObject});
													}
												}
											});
										}.createDelegate(win),
										icon: Ext6.Msg.INFO,
										msg: 'Документ не актуален',
										title: 'Верификация'
									});
								}
							}
						});
					}
				}
			}
		});
	},
	
	_getStore_WorkRelease: function(){
		eval(this.ini);
		return me.queryById('EStEF_EvnStickWorkReleaseGrid').getStore();
	},

	_reload_WorkRelease: function(){
		eval(this.ini);
		var store = this._getStore_WorkRelease();
		store.reload({callback: Ext6.emptyFn});

		return me;
	},
		
	openEvnStickWorkReleaseEditWindow: function(action) {//+ fix. действия в разделе освобождения
		eval(this.ini);

		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view'])) ) {
			return false;
		}
		var grid = win.queryById('EStEF_EvnStickWorkReleaseGrid');
		if ( vm.get('action') == 'view' ) {
			if ( action == 'add') {
				grid.recordMenu.data_id = null;
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( isVisibleWnd('swEvnStickWorkReleaseEditWindow'+(vm.get('ext2')?'':'Ext6')) ) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Окно редактирования освобождения от работы уже открыто'));
			return false;
		}

		
		var disableBegDate = null;
		var begDate = null;
		var endDate = null;
		var maxDate = null;
		var sumDate = parseInt(base_form.findField('WorkReleaseSumm').getValue()) || 0; // подставляем сумму из предыдущих ЛВН.

		var params = new Object();
        params.StickReg = win.StickReg;
        params.CurLpuSection_id = win.CurLpuSection_id;
        params.CurLpuUnit_id = win.CurLpuUnit_id;
        params.CurLpuBuilding_id = win.CurLpuBuilding_id;
        params.IngoreMSFFilter = win.IngoreMSFFilter;
		params.isTubDiag = vm.get('isTubDiag');
		var access_type = 'view';
		var selected_record = grid.getStore().getAt(grid.recordMenu.data_id);
		var signatures = {};
		var EvnStickWorkRelease_IsInReg = null;

		if ( selected_record ) {
			access_type = selected_record.get('accessType');
			EvnStickWorkRelease_IsInReg = selected_record.get('EvnStickWorkRelease_IsInReg');
			// Даннные о подписании должны сохраняться
			signatures = {
				Signatures_mid: selected_record.get('Signatures_mid'),
				SMPStatus_id: selected_record.get('SMPStatus_id'),
				SMP_Status_Name: selected_record.get('SMP_Status_Name'),
				SMP_updDT: selected_record.get('SMP_updDT'),
				SMP_updUser_Name: selected_record.get('SMP_updUser_Name'),
				Signatures_wid: selected_record.get('Signatures_wid'),
				SVKStatus_id: selected_record.get('SVKStatus_id'),
				SVK_Status_Name: selected_record.get('SVK_Status_Name'),
				SVK_updDT: selected_record.get('SVK_updDT'),
				SVK_updUser_Name: selected_record.get('SVK_updUser_Name')
			}
		}

		if (access_type != 'edit' && action == 'edit') {
			action = 'view';
		}

		if (getRegionNick() != 'kz' && EvnStickWorkRelease_IsInReg == 2 && action == 'edit') {
			action = 'view';
		}

		if (
			getRegionNick() == 'ufa'
			&& this.getPridStickLeaveTypeCode() == '37'
			&& base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'dolsan'
			&& base_form.findField('Org_did').getValue() == getGlobalOptions().org_id
			&& !Ext6.isEmpty(base_form.findField('EvnStick_sstBegDate'))
		) {
			var f_s_record = null;
			grid.getStore().each(function(record){
				if (
					record.get('Org_id') == base_form.findField('Org_did').getValue()
					&& (!f_s_record || record.get('EvnStickWorkRelease_begDate') < f_s_record.get('EvnStickWorkRelease_begDate'))
				) {
					f_s_record = record;
				}
			});
			if ((action == 'add' && !f_s_record) || (action == 'edit' && f_s_record && f_s_record.id == selected_record.id)) {
				begDate = base_form.findField('EvnStick_sstBegDate').getValue();
				disableBegDate = true;
			}
		}

		if (getRegionNick() == 'kz') {
			if (win.parentClass == 'EvnPS') {
				begDate = base_form.findField('EvnStick_stacBegDate').getValue();
				endDate = base_form.findField('EvnStick_stacEndDate').getValue();
			} else {
				begDate = base_form.findField('EvnStick_setDate').getValue();
			}
		}

		if ( action == 'add' ) {
			var maxCount = getRegionNick()=='kz' ? 4 : 3;
			if ( grid.getStore().getCount() >= maxCount ) {
				Ext6.Msg.alert(langs('Ошибка'), langs('Разрешено добавление только ')+maxCount+langs('-х записей об освобождении от работы'));
				return false;
			}

			grid.getStore().each(function(record) {
				if ( record && record.get('EvnStickWorkRelease_endDate') != '' ) {
					if (!maxDate || record.get('EvnStickWorkRelease_endDate') > maxDate) {
						maxDate = record.get('EvnStickWorkRelease_endDate');
					}
					// считаем сумму периодов
					sumDate = sumDate + Math.round((record.get('EvnStickWorkRelease_endDate') - record.get('EvnStickWorkRelease_begDate')) / 86400000) + 1;
				}
			});

		} else {

			if ( Ext6.isEmpty(grid.recordMenu.data_id) || grid.getStore().getCount()==0 || Ext6.isEmpty(grid.getStore().getAt(grid.recordMenu.data_id).get('EvnStickWorkRelease_id')) ) {
				return false;
			}

			var selrecord = grid.getStore().getAt(grid.recordMenu.data_id);

			grid.getStore().each(function(record) {
				if ( record && record.get('EvnStickWorkRelease_endDate') != '' && (record.get('EvnStickWorkRelease_begDate') < selrecord.get('EvnStickWorkRelease_begDate')) ) {
					if (!maxDate || record.get('EvnStickWorkRelease_endDate') > maxDate) {
						maxDate = record.get('EvnStickWorkRelease_endDate');
					}
					// считаем сумму периодов
					sumDate = sumDate + Math.round((record.get('EvnStickWorkRelease_endDate') - record.get('EvnStickWorkRelease_begDate')) / 86400000) + 1;
				}
			});
		}

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.evnStickWorkReleaseData != 'object' ) {
				return false;
			}

			data.evnStickWorkReleaseData.RecordStatus_Code = 0;

			
			var indx = grid.getStore().findBy(function(rec) {
				return rec.get('EvnStickWorkRelease_id') == data.evnStickWorkReleaseData.EvnStickWorkRelease_id;
			});
			if ( indx>=0 ) {
				var record = grid.getStore().getAt(indx);
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.evnStickWorkReleaseData.RecordStatus_Code = 2;
				}

				Ext6.apply(data.evnStickWorkReleaseData, signatures);

				var grid_fields = new Array();

				grid.getStore().model.fields.forEach(function(field) {
					if(field.name!='id') grid_fields.push(field.name);
				});

				var ESWRChanged = false;
				for ( i = 0; i < grid_fields.length; i++ ) {
					if (record.get(grid_fields[i]) != data.evnStickWorkReleaseData[grid_fields[i]]) {
						ESWRChanged = true;
					}
					record.set(grid_fields[i], data.evnStickWorkReleaseData[grid_fields[i]]);
				}

				if( getRegionNick() == 'kz' && ESWRChanged ) { // освобождение было изменено

					if( record.get('SMPStatus_id') == 1 || record.get('SVKStatus_id') == 1 ) {//освобождение было подписано
						Ext6.Msg.alert(langs('Предупреждение'), langs('В освобождение от работы были внесены изменения, необходимо подписать документ.'));	
					}
					
					record.set('SMPStatus_id', 2)
					record.set('SMP_Status_Name', 'Документ не подписан');
					record.set('SMP_updDT', null);
					record.set('SMP_updUser_Name', null);
					record.set('SVKStatus_id', 2);
					record.set('SVK_Status_Name', 'Документ не подписан');
					record.set('SVK_updDT', null);
					record.set('SVK_updUser_Name', null);

					//блокируем верификацию документа
					grid.recordMenu.queryById('leaveActionsCheck').disable();
					grid.recordMenu.queryById('leaveActionsListVK').disable();
				}
				record.commit();
			}
			else {
				if ( 
					grid.getStore().getCount() == 1 
					&& !grid.getStore().getAt(0).get('EvnStickWorkRelease_id') 
					&& !grid.getStore().getAt(0).get('StickFSSType_Name')
				) {
					grid.getStore().removeAll();
				}
				data.evnStickWorkReleaseData.EvnStickWorkRelease_id = -this.swGenTempId(grid.getStore(), 'EvnStickWorkRelease_id');

				grid.getStore().loadData([ data.evnStickWorkReleaseData ], true);
			}
			win.panelEvnStickWorkRelease.isLoaded = true;
			win.panelEvnStickWorkRelease.refreshTitle();
			// Разворачиваем панель "Исход"
			win.queryById('EStEF_StickLeavePanel').expand();
			this.checkLastEvnStickWorkRelease();

			if(vm.get('action') != 'view') {
				this.refreshFormPartsAccess();
			}

			this.checkSaveButtonEnabled();
			this.loadMedStaffFactList();

			base_form.findField('StickLeaveType_id').fireEvent('change', base_form.findField('StickLeaveType_id'), base_form.findField('StickLeaveType_id').getValue());
		}.createDelegate(this);
		params.formParams = new Object();
		params.disableBegDate = disableBegDate;
		params.begDate = begDate;
		params.endDate = endDate;
		params.maxDate = maxDate;
		params.sumDate = sumDate;

		var recordsc = base_form.findField('StickCause_id').getStore().getById(base_form.findField('StickCause_id').getValue());
		if ( recordsc ) {
			params.StickCause_SysNick = recordsc.get('StickCause_SysNick');
		}

		params.StickOrder_Code = base_form.findField('StickOrder_id').getFieldValue('StickOrder_Code');

		params.parentClass = win.parentClass;
		// данные о том кому выдается ЛВН
		params.Person_id = win.PersonInfo.getFieldValue('Person_id');
		params.Person_Birthday = win.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = win.PersonInfo.getFieldValue('Person_Firname');
		params.Person_Secname = win.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = win.PersonInfo.getFieldValue('Person_Surname');

		// данные о больном.
		var carePersonGrid = win.queryById('EStEF_EvnStickCarePersonGrid');
		if ( carePersonGrid.getStore().getCount() > 0 && carePersonGrid.getStore().getAt(0).get('EvnStickCarePerson_id') ) {
			params.StickPerson_Birthday = carePersonGrid.getStore().getAt(0).get('Person_Birthday');
			params.StickPerson_Firname = carePersonGrid.getStore().getAt(0).get('Person_Firname');
			params.StickPerson_Secname = carePersonGrid.getStore().getAt(0).get('Person_Secname');
			params.StickPerson_Surname = carePersonGrid.getStore().getAt(0).get('Person_Surname');
		} else {
			params.StickPerson_Birthday = win.PersonInfo.getFieldValue('Person_Birthday');
			params.StickPerson_Firname = win.PersonInfo.getFieldValue('Person_Firname');
			params.StickPerson_Secname = win.PersonInfo.getFieldValue('Person_Secname');
			params.StickPerson_Surname = win.PersonInfo.getFieldValue('Person_Surname');
		}

		params.EvnStick_setDate = base_form.findField('EvnStick_setDate').getValue();
		params.EvnStick_IsOriginal = base_form.findField('EvnStick_IsOriginal').getRawValue();
		params.evnStickType = win.evnStickType;

		params.EvnStick_stacBegDate = base_form.findField('EvnStick_stacBegDate').getValue();
		params.EvnStick_stacEndDate = base_form.findField('EvnStick_stacEndDate').getValue();
		params.EvnStick_IsOriginal = base_form.findField('EvnStick_IsOriginal').getRawValue();
		params.isHasDvijeniaInStac24 = vm.get('isHasDvijeniaInStac24');
		params.parentClass = me.parentClass;
		
		if ( action == 'add' ) {
			params.formParams.EvnStickBase_id = base_form.findField('EvnStick_id').getValue();
			params.onHide = function() {
				
			};
		}
		else {
			if( Ext6.isEmpty(grid.recordMenu.data_id) || grid.getStore().getCount()==0 || Ext6.isEmpty(grid.getStore().getAt(grid.recordMenu.data_id).get('EvnStickWorkRelease_id')) ) {
			//if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnStickWorkRelease_id') ) {
				return false;
			}

			var record = grid.getStore().getAt(grid.recordMenu.data_id);

			params.formParams = record.data;
			params.onHide = function() {
				
			};
		}

		if ( win.userMedStaffFactId ) {
			params.UserMedStaffFact_id = win.userMedStaffFactId;
		}

		// Если полка, и предыдущий ЛВН закрыт с причиной долечивание в квс => ограничение по длительности ЛВН будет 10 дней.
		if ( win.parentClass.inlist([ 'EvnPL', 'EvnPLStom' ]) ) {
			if (base_form.findField('MaxDaysLimitAfterStac').getValue() == 2) {
				params.MaxDaysLimitAfterStac = true;
			} else {
				params.MaxDaysLimitAfterStac = false;
			}
		}
		
		params.isELN = win.isELN;

		getWnd('swEvnStickWorkReleaseEditWindow'+(vm.get('ext2')?'':'Ext6')).show(params);
	},
	
	openESSConsent: function(action) {
		eval(this.ini);
		var evn_stick_id = base_form.findField('EvnStick_id').getValue(),
			consent_dt = base_form.findField('EvnStickBase_consentDT').getValue(),
			evnstick_disdate = base_form.findField('EvnStick_disDate').getValue();

		if(!Ext6.isEmpty(evnstick_disdate) && base_form.findField('EvnStick_IsOriginal').getRawValue() != 2 && base_form.findField('StickWorkType_id').getValue() != 2) return false;
		if(action == 'add' && consent_dt) return false;
		if(action == 'edit' && !consent_dt) return false;

		var params = {
			EvnStickBase_consentDT: consent_dt,
			EvnStick_setDate: base_form.findField('EvnStick_setDate').getValue(),
			EvnStick_disDate: base_form.findField('EvnStick_disDate').getValue(),
			allowPrint: base_form.findField('EvnStickFullNameText').getValue() && base_form.findField('StickCause_id').getValue(),
			callback: function(EvnStickBase_consentDT) {
				if (!Ext6.isEmpty(EvnStickBase_consentDT)) {
					base_form.findField('EvnStickBase_consentDT').setValue(EvnStickBase_consentDT);
					
					if (base_form.findField('EvnStickFullNameText').getValue() && base_form.findField('StickCause_id').getValue()) {
						_this.doPrintESSConsent();
					}
					if (getRegionNick() != 'kz'){
						_this.checkGetEvnStickNumButton();
					}
				}
			}
		};
		getWnd('swEvnStickESSConfirmEditWindowExt6').show(params);
	},
	
	checkLastEvnStickWorkRelease: function(action) {//+
		// проверяем МО в последнем освобождении
		// Если МО в последнем освобождении отлична от МО пользователя, необходимо заполнить обязательные поля
		eval(this.ini);
		var evn_stick_work_release_grid = me.queryById('EStEF_EvnStickWorkReleaseGrid');
		var Org_id = null;
		var maxDate = null;
		var minDate = null;
		evn_stick_work_release_grid.getStore().each(function(rec) {
			if ( rec && rec.get('EvnStickWorkRelease_begDate') != '' ) {
				if (maxDate == null || rec.get('EvnStickWorkRelease_begDate') > maxDate) {
					Org_id = rec.get('Org_id');
					maxDate = rec.get('EvnStickWorkRelease_begDate');
				}

				if (minDate == null || rec.get('EvnStickWorkRelease_begDate') < minDate) {
					minDate = rec.get('EvnStickWorkRelease_begDate');
				}
			}
		});

		var otherOrg = (!Ext6.isEmpty(Org_id) && Org_id != getGlobalOptions().org_id);

		if (getRegionNick() == 'kz' && otherOrg && vm.get('action') != 'view') {
			// Исход ЛВН. Выпадающий список ТОЛЬКО из значений: 31/37
			// Дата исхода ЛВН.
			if (base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code') && !base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code').inlist(['31','37'])) {
				base_form.findField('StickLeaveType_id').reset();
				base_form.findField('StickLeaveType_id').fireEvent('change', base_form.findField('StickLeaveType_id'), base_form.findField('StickLeaveType_id').getValue());
			}
			base_form.findField('StickLeaveType_id').getStore().clearFilter();
			base_form.findField('StickLeaveType_id').lastQuery = '';
			base_form.findField('StickLeaveType_id').getStore().filterBy(function(rec) {
				if (rec.get('StickLeaveType_Code').inlist(['31','37'])) {
					return true;
				}
				return false;
			});
			base_form.findField('StickLeaveType_id').setAllowBlank(false);
			base_form.findField('MedStaffFact_id').setAllowBlank(true);
			base_form.findField('MedStaffFact_id').setContainerVisible(false);
		} else {
			base_form.findField('StickLeaveType_id').getStore().clearFilter();
			base_form.findField('StickLeaveType_id').lastQuery = '';
			base_form.findField('StickLeaveType_id').setAllowBlank(true);

			var showMedStaffFact = !Ext6.isEmpty(base_form.findField('StickLeaveType_id').getValue());
			var allowBlankMedStaffFact = (!showMedStaffFact || otherOrg);

			base_form.findField('MedStaffFact_id').setAllowBlank(allowBlankMedStaffFact);
			base_form.findField('MedStaffFact_id').setContainerVisible(showMedStaffFact);
		}

		if(me.fromList)
		{
			base_form.findField('StickLeaveType_id').getStore().clearFilter();
			base_form.findField('StickLeaveType_id').lastQuery = '';
			base_form.findField('StickLeaveType_id').getStore().filterBy(function(rec) {
				if (rec.get('StickLeaveType_Code').inlist(['31','37'])) {
					return true;
				}
				return false;
			});
			base_form.findField('StickLeaveType_id').setAllowBlank(false);
			win.queryById('EStEF_StickLeavePanel').expand();
			var index = base_form.findField('StickLeaveType_id').getStore().findBy(function(rec) {
				if(rec.get('StickLeaveType_Code') == '31')
					return true;
				else
					return false;

			});
			var record = base_form.findField('StickLeaveType_id').getStore().getAt(index);
			base_form.findField('StickLeaveType_id').setValue(record.get('StickLeaveType_id'));
		}

		if(vm.get('action') != 'view' && me.StickReg == 1)
		{
			me.queryById('EStEF_StickLeavePanel').expand();
			if (!vm.get('isInReg') && !vm.get('isPaid')) {
				base_form.findField('StickLeaveType_id').enable();
			}
		}
	},
	
	getPridStickLeaveTypeCode: function() {
		eval(this.ini);
		var code1 = base_form.findField('PridStickLeaveType_Code1').getValue();
		var code2 = base_form.findField('PridStickLeaveType_Code2').getValue();

		if (!Ext6.isEmpty(code2) && code2 != '0') return code2;
		if (!Ext6.isEmpty(code1) && code1 != '0') return code1;
		return '0';
	},
	
	isUhod: function(record, person_age) {//ext6: used in onStickCause_id
		if(!record) return false;
		switch(record.get('StickCause_SysNick')) {
			case 'uhod':
			case 'uhodreb':
			case 'uhodnoreb':
			case 'rebinv':
			case 'vich':
			case 'zabrebmin': return true;
			case 'karantin': if(person_age<=18) return true;
			default: return false;
		}
	},
	
	getMedPersonalInfo: function(MedStaffFact_id, callback) {// получение информации о враче //
		if(MedStaffFact_id) {
			Ext6.Ajax.request({
				params: {
					MedStaffFact_id: MedStaffFact_id
				},
				success: function(response, options) {
					var result = Ext6.util.JSON.decode(response.responseText);

					callback(result);
				},
				url: '/?c=MedPersonal&m=getMedPersonalInfo'
			});
		}
	},
	
	hasWorkRelease: function() { // проверка, что в ЛВН есть хотя бы один добавленный период освобождения от работы
		eval(this.ini);
		var hasWorkRelease = false;
		win.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().each(function(rec) {
			if (rec && rec.get('EvnStickWorkRelease_id')) {
				hasWorkRelease = true;
			}
		});

		return hasWorkRelease;
	},
	
	
	_checkAccessToField_swSignStickIrr: function() {// Проверка доступности кнопки "подписать режим"
		eval(this.ini);
		var isOpen = false;

		// Оператор
		if(this.isOperator() == true){
			// Доступ не открываем
		}

		// Статистик
		if(this.isStatistick() == true){
			// Доступ не открываем
		}

		// Регистратор ЛВН
		if(this.isRegistratorLVN() == true){
			// Доступ не открываем
		}

		// Регистратор
		if(this.isRegistrator() == true){
			// Доступ не открываем
		}
		
		// Врач
		if(this.isVrach() == true){

			var checkUslovie1 = false;
			// Врач указан в качестве врача в любом периоде  «Освобождения от работы» или исходе
			if (this.checkMedPersonalInWorkRelease() == true || base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') == getGlobalOptions().medpersonal_id) {
				checkUslovie1 = true;
			}
			
			var checkUslovie2 = false;
			var checkUslovie2_1 = false;
			var checkUslovie2_2 = false;

			// Режим подписан
			if ( vm.get('Signatures_iid') ) {
				checkUslovie2_1 = true;
				// Режим подписан текущим врачом
				if( me.signedRegime_MedPersonal_id == getGlobalOptions().medpersonal_id ) {
					checkUslovie2_2 = true;
				}
			}
			// режим не подписан или его пописал текущий врач
			if ( !checkUslovie2_1 || checkUslovie2_2 ) {
				checkUslovie2 = true;
			}


			var checkUslovie3 = false;
			// Поле "Нарушение режима" заполнено
			if ( base_form.findField('StickIrregularity_id').getValue() ) {
				checkUslovie3 = true;
			}
			

			if ( checkUslovie1 && checkUslovie2 && checkUslovie3 ) {
				isOpen = true;
			}
		}

		if(this.isVrachVK() == true){
			var checkUslovie1 = false;
			// Поле "Нарушение режима" заполнено
			if ( base_form.findField('StickIrregularity_id').getValue() ) {
				checkUslovie1 = true;
			}


			var checkUslovie2 = false;
			var checkUslovie2_1 = false;
			var checkUslovie2_2 = false;
			// Режим подписан
			if ( vm.get('Signatures_iid') ) {
				checkUslovie2_1 = true;
				// Режим подписан текущим врачом
				if( me.signedRegime_MedPersonal_id == getGlobalOptions().medpersonal_id ) {
					checkUslovie2_2 = true;
				}
			}
			// режим не подписан или его пописал текущий врач
			if ( !checkUslovie2_1 || checkUslovie2_2 ) {
				checkUslovie2 = true;
			}
			
			if ( checkUslovie1 && checkUslovie2 ) {
				isOpen = true;
			}
		}

		// Врач, регистратор (одновременно)
		if(this.isVrachAndRegistrator() == true){
			// Доступ не открываем
		}
		vm.set('isAccessToField_swSignStickIrr', isOpen);//ext6
		return isOpen;

	},
	
	_closeAccessToField_swSignStickIrr: function() {
		eval(this.ini);
		vm.set('isAccessToField_swSignStickIrr', false);
		return true;
	},
	
	// устанавливаем статус подписи ЛВН "Документ не актуален"
	resetSignStatus: function() {
		eval(this.ini);
		var status = win.queryById('SLeaveStatus_Name')
		if (
			getRegionNick() != 'kz'
			&& status.getEl().dom.innerHTML
		) {
			status.getEl().dom.innerHTML = 'Документ не актуален';
			//status.render();

			win.queryById('swSignStickLeaveCheck').hide();
		}
	},
	
	checkWorkReleaseMenu: function() {//+ext6 из ext2 rowselect в gridEvnStickWorkRelease
		eval(this.ini);
		
		var grid = win.gridEvnStickWorkRelease,
			gridmenu = grid.recordMenu;
		var access_type = 'view';
		var sign_access = 'view';
		var id = null;
		var selected_record = grid.getSelectedRecord();
		if ( selected_record ) {
			access_type = selected_record.get('accessType');
			sign_access = selected_record.get('signAccess');
			id = selected_record.get('EvnStickWorkRelease_id');
		}
		gridmenu.queryById('WRmenuEdit').disable();
		gridmenu.queryById('WRmenuDelete').disable();
		gridmenu.queryById('WRmenuActions').disable();
		gridmenu.queryById('WRmenuActions').menu.items.items.forEach(function(item) {
			item.disable();
		});
		if ( id ) {
			gridmenu.queryById('WRmenuView').enable();
			if ( win.evnStickType.inlist([1,2]) && vm.get('action')!='view' && access_type == 'edit' ) {
				gridmenu.queryById('WRmenuEdit').enable();
				gridmenu.queryById('WRmenuDelete').enable();
				
			}
			if ( win.evnStickType.inlist([1,2]) && vm.get('action')!='view' && sign_access == 'edit' ) {
				gridmenu.queryById('WRmenuActions').enable();
				
				_this._checkAccessToField_WorkRelease_Sign();// Доступ к кнопке "Подписать (Врачом)"
				_this._checkAccessToField_WorkRelease_SignVK();// Доступ к кнопке "Подписать (Врачом ВК)"
				
				if (selected_record.get('SMPStatus_id') == 1) {
					gridmenu.queryById('leaveActionsList').enable();
					gridmenu.queryById('leaveActionsCheck').enable();
				}
				if (selected_record.get('SVKStatus_id') == 1) {
					gridmenu.queryById('leaveActionsListVK').enable();
					gridmenu.queryById('leaveActionsCheckVK').enable();
				}
			}
		} else {
			gridmenu.queryById('WRmenuView').disable();
		}
	},
	
	_checkAccessToField_WorkRelease_Sign: function(){
		eval(this.ini);
		var grid = me.gridEvnStickWorkRelease;
		var isOpen = false;
		var selected_record = grid.getSelectedRecord();

		// Оператор
		if(this.isOperator() == true){
			// Доступ не открываем
		}

		// Статистик
		if(this.isStatistick() == true){
			// Доступ не открываем
		}

		// Регистратор ЛВН
		if(this.isRegistratorLVN() == true){
			// Доступ не открываем
		}

		// Регистратор
		if(this.isRegistrator() == true){
			// Доступ не открываем
		}

		// Врач
		if(this.isVrach() == true){
			// ---------------------------------------------------------------------------------------------------------
			var checkUslovie1 = false;

			// Врач указан в качестве врача 1 в периоде  «Освобождения от работы»
			if(getGlobalOptions().medpersonal_id){

				//~ var selected_record = me._getSelected_WorkRelease();

				// Врач указан в качестве врача 1 в периоде  «Освобождения от работы»
				if(
					// Врач указан в качестве врача 1
					getGlobalOptions().medpersonal_id.inlist([
						selected_record.get('MedPersonal_id')
					]) 
				){
					checkUslovie1 = true;
				}
			}

			var checkUslovie2 = false;
			// Если ЭЛН дубликат, то дубликат оформлен текущим врачом
			if (base_form.findField('EvnStick_IsOriginal').getValue() == 2 && base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') == getGlobalOptions().medpersonal_id) {
				checkUslovie2 = true;
			}

			if (checkUslovie1 == true || checkUslovie2 == true) {
				isOpen = true;
			}
		}

		// Врач
		if(this.isVrachVK() == true){
			// Доступ не открываем
		}

		// Врач, регистратор (одновременно)
		if(this.isVrachAndRegistrator() == true){
			// Доступ не открываем
		}

		grid.recordMenu.queryById('leaveActionsSign').setDisabled(!isOpen);
		return isOpen;
	},
	
	_checkAccessToField_WorkRelease_SignVK: function(){
		eval(this.ini);
		var grid = me.gridEvnStickWorkRelease;
		var isOpen = false;
		var selected_record = grid.getSelectedRecord();

		// Оператор
		if(this.isOperator() == true){
			// Доступ не открываем
		}

		// Статистик
		if(this.isStatistick() == true){
			// Доступ не открываем
		}

		// Регистратор ЛВН
		if(this.isRegistratorLVN() == true){
			// Доступ не открываем
		}

		// Регистратор
		if(this.isRegistrator() == true){
			// Доступ не открываем
		}

		// Врач
		if(this.isVrach() == true){
			// Доступ не открываем
		}

		// Врач ВК
		if(this.isVrachVK() == true){
			// ---------------------------------------------------------------------------------------------------------
			var checkUslovie1 = false;

			// Врач указан в качестве врача 3 в периоде  «Освобождения от работы» и проставлен флаг "Председатель ВК"
			if(getGlobalOptions().medpersonal_id && selected_record.get('EvnStickWorkRelease_IsPredVK')){
				if(
					getGlobalOptions().medpersonal_id.inlist([selected_record.get('MedPersonal3_id')]) 
					&& selected_record.get('EvnStickWorkRelease_IsPredVK') == 1
				){
					checkUslovie1 = true;
				}
			}
			// ---------------------------------------------------------------------------------------------------------


			if(checkUslovie1 == true){
				isOpen = true;
			}
		}

		// Врач, регистратор (одновременно)
		if(this.isVrachAndRegistrator() == true){
			// Доступ не открываем
		}

		grid.recordMenu.queryById('leaveActionsSignVK').setDisabled(!isOpen);
		return isOpen;
	},
	
	/* TAG: обработчики onChange и onBlur */
	onEvnStick_disDate: function() {
		this.resetSignStatus();
		this.checkGetEvnStickNumButton();
	},
	onLoadEvnStickWorkReleaseGrid: function(store) {
		eval(this.ini);
		me.panelEvnStickWorkRelease.refreshTitle();
		this.loadMedStaffFactList();
		this.checkLastEvnStickWorkRelease();

		if(vm.get('actoin') != 'view') {
			this.refreshFormPartsAccess();
		}

		var paid_counter = 0;
		//var update_button = win.queryById('updateEvnStickWorkReleaseGrid');//кнопки апдейт нет?
		/*
		store.each(function(rec) {
			if (rec.get('EvnStickWorkRelease_IsPaid') == 2) {
				paid_counter ++;
			}
		});

		if(paid_counter >= 3) {
			update_button.disable();
		} else {
			update_button.enable();
		}*/
		this.checkSaveButtonEnabled();					
	},
	onLoadEvnStickCarePersonGrid: function(store) {
		eval(this.ini);
		if ( store.getCount() == 0 ) {
			this._clearEvnStickCarePersonGrid();
		} else {			
			if (
				vm.get('action') == 'add'
				&& base_form.findField('EvnStick_IsOriginal').getRawValue() == 2 //Дубликат
			) {
				win.queryById('EStEF_EvnStickCarePersonGrid').getStore().each(function(rec) {
					rec.set('EvnStickCarePerson_id', -1);
					rec.set('RecordStatus_Code', 0);
				});
			}
		}
		me.panelEvnStickCarePerson.refreshTitle();
	},
	onEvnStickFullNameText: function(field, newVal, oldVal) {/// поле ФИО
		eval(this.ini);
		if(field.stop>0) { field.stop--; return; }// else if(field.stop<0) field.stop*=-1;
		vm.set('isEvnStickFullNameText', !Ext6.isEmpty(newVal));
		this._setDefaultValueTo_Org_id();
		this._setDefaultValueTo_Post_Name();
		this.checkGetEvnStickNumButton();
	},
	
	onEvnStick_Num: function(field) {
		eval(this.ini);
		//~ vm.set('EvnStick_Num', field.getValue());//если allowBlank==false
		if (getRegionNick() != 'kz'){
			this.checkGetEvnStickNumButton();
		}
		this.checkOrgFieldDisabled();
	},
	
	onStickOrder_id: function(field, newValue, oldValue, fire) {/// checkbox. без bind value
		//if(field.stop>0) { field.stop--; return; } //else if(field.stop<0) field.stop*=-1;
		eval(this.ini);
		//TAG:onchange
		this.setMaxDateForSetDate();

		if (Ext6.isEmpty(base_form.findField('RegistryESStorage_id').getValue())) {
			base_form.findField('EvnStick_Num').setRawValue('');
			base_form.findField('EvnStick_Num').fireEvent('change', base_form.findField('EvnStick_Num'), base_form.findField('EvnStick_Num').getValue());
		}
		base_form.findField('EvnStick_Ser').setRawValue('');

		if ( field.getValue() == false ) { // по ext2: StickOrder_id==2
			//~ base_form.findField('EvnStickLast_id').setVisible(true);
			//~ base_form.findField('EvnStickLast_id').setAllowBlank(false);
		}
		else {
			base_form.findField('EvnStick_prid').setValue(0);
			base_form.findField('EvnStickLast_id').setRawValue('');
			//~ base_form.findField('EvnStickLast_id').setVisible(false);
			//~ base_form.findField('EvnStickLast_id').setAllowBlank(true);
		}
	},

	onEvnStick_IsOriginal: function(field, newValue, oldValue){/// checkbox. bind value = EvnStick_IsNotOriginal
		//if(field.stop>0) { field.stop--; return; } //else if(field.stop<0) field.stop*=-1;
		eval(this.ini);
		//TAG:onchange.
		base_form.findField('EvnStick_oid').reset();
		base_form.findField('EvnStick_oid').fireEvent('blur', base_form.findField('EvnStick_oid'));

		if ( newValue ) { // дубликат
			// Загружаем список ЛВН - оригиналов и дизаблим все параметры.
			this._loadStore_EvnStick_oid();

			//~ this.queryById('EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].disable();
			//~ win.panelEvnStickCarePerson.tools.plusbutton.hide();
			//~ this.queryById('EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].disable();
			//~ win.panelEvnStickWorkRelease.tools.plusbutton.hide();
			
			//~ this.queryById('EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[1].disable();
			win.queryById('EStEF_EvnStickCarePersonGrid').recordMenu.queryById('CPmenuEdit').hide();
			//~ this.queryById('EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[1].disable();
			win.gridEvnStickWorkRelease.recordMenu.queryById('WRmenuEdit').hide();
			
			//~ this.queryById('EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[3].disable();
			win.queryById('EStEF_EvnStickCarePersonGrid').recordMenu.queryById('CPmenuDelete').hide();
			//~ this.queryById('EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[3].disable();
			win.gridEvnStickWorkRelease.recordMenu.queryById('WRmenuDelete').hide();

			//~ this.queryById('EStEF_btnSetMinDateFromPS').setVisible(false);
			//~ this.queryById('EStEF_btnSetMaxDateFromPS').setVisible(false);

			base_form.findField('EvnStick_oid').setAllowBlank(false);
			base_form.findField('EvnStick_oid').setContainerVisible(true);
			//~ win.queryById('EvnStickBase_consentDT').enable();
		}
		else {
			// Отменяем дизаблинг параметров
			if ( vm.get('action') != 'view' ) {
				//~ this.queryById('EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].enable();
				//~ win.panelEvnStickCarePerson.tools.plusbutton.show();
				//~ this.queryById('EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].enable();
				//~ win.panelEvnStickWorkRelease.tools.plusbutton.show();
				
				//~ this.queryById('EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[1].enable();
				win.queryById('EStEF_EvnStickCarePersonGrid').recordMenu.queryById('CPmenuEdit').show();
				//~ this.queryById('EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[1].enable();
				win.gridEvnStickWorkRelease.recordMenu.queryById('WRmenuEdit').show();
				
				//~ this.queryById('EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[3].enable();
				win.queryById('EStEF_EvnStickCarePersonGrid').recordMenu.queryById('CPmenuDelete').show();
				//~ this.queryById('EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[3].enable();
				win.gridEvnStickWorkRelease.recordMenu.queryById('WRmenuDelete').show();
				
				this.onEnableEdit(true);

				//~ this.queryById('EStEF_btnSetMinDateFromPS').setVisible(true);
				//~ this.queryById('EStEF_btnSetMaxDateFromPS').setVisible(true);
			}

			base_form.findField('EvnStick_oid').getStore().removeAll();
			base_form.findField('EvnStick_oid').setAllowBlank(true);
			base_form.findField('EvnStick_oid').setContainerVisible(false);
			//~ win.queryById('EvnStickBase_consentDT').disable();
		}
	},
	
	onMedStaffFact_id: function(combo, newValue, oldValue) {
		if(combo.stop>0) { combo.stop--; return; }
		this.checkSaveButtonEnabled();
		// определяем доступность кнопки подписания исхода
		this._checkAccessToField_StickLeave_Sign();
		this.resetSignStatus();
	},

	onStickIrregularity_id: function(combo, newValue, oldValue) {/// TAG:onchange
		eval(this.ini);
		if(combo.stop>0) { combo.stop--; return; } //else if(combo.stop<0) combo.stop*=-1;
		var record = combo.getStore().getById(newValue);

		if ( newValue ) {
			base_form.findField('EvnStick_irrDate').setContainerVisible(true);
			base_form.findField('EvnStick_irrDate').setAllowBlank(false);
		}
		else {
			base_form.findField('EvnStick_irrDate').setContainerVisible(false);
			base_form.findField('EvnStick_irrDate').setAllowBlank(true);
			base_form.findField('EvnStick_irrDate').setRawValue('');
			win.queryById('SIrrStatus_Name').setText('');
			win.queryById('SIrrStatus_Icon').hide();
			//win.queryById('swSignStickIrr').hide(); //ред.145611
		}
		this.refreshFormPartsAccess();
	},
	onStickCause_id: function(combo, newValue, oldValue) {///
		if(combo.stop>0) { combo.stop--; return; } //else if(combo.stop<0) combo.stop*=-1;
		eval(this.ini);

		this.setMaxDateForSetDate();
		this.checkGetEvnStickNumButton();
		var oldRecord = combo.getStore().getById(oldValue);
		var record = combo.getStore().getById(newValue);
		var person_age = swGetPersonAge(win.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnStick_setDate').getValue());

		var storeEvnStickCarePerson = this._getStoreEvnStickCarePerson();

		var stick_cause_sys_nick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
		var isRegPregnancy = base_form.findField('EvnStick_IsRegPregnancy');
		
		if(record) vm.set('StickCause_SysNick',record.get('StickCause_SysNick'));
		//поле должно быть обязательным для ввода, если причина нетрудоспособности - отпуск по беременности и родам
		if(
			stick_cause_sys_nick == 'pregn' 
			&& base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') != '020'
		) {
			isRegPregnancy.setAllowBlank(false);
		} else {
			isRegPregnancy.setAllowBlank(true);
		}
		if(
			stick_cause_sys_nick == 'pregn' 
			&& base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') == '020'
			&& vm.get('action') == 'add' && base_form.findField('EvnStick_IsOriginal').getRawValue() != 2
		) {
			base_form.findField('EvnStick_stacBegDate').setValue('');
			base_form.findField('EvnStick_stacEndDate').setValue('');
		}
						
		//isRegPregnancy.setAllowBlank(!(stick_cause_sys_nick == 'pregn'));//убрано в ред.140708 #158062

		// Вопрос о смене причины с "Уход" на что-то другое
		
		/*if ( oldRecord && oldRecord.get('StickCause_SysNick').inlist([ 'karantin', 'uhod', 'uhodreb', 'uhodnoreb', 'rebinv', 'vich' ])
								&& (!record || !record.get('StickCause_SysNick').inlist([ 'karantin', 'uhod', 'uhodreb', 'uhodnoreb', 'rebinv', 'vich' ]))
								&& !ignoreCareFlag && this.queryById(win.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0
								&& this.queryById(win.id+'EStEF_EvnStickCarePersonGrid').getStore().getAt(0).get('EvnStickCarePerson_id') > 0
							)
							
		if ( oldRecord && oldRecord.get('StickCause_SysNick').inlist([ 'karantin', 'uhod', 'uhodreb', 'uhodnoreb', 'rebinv', 'vich', 'zabrebmin' ])
							&& (!record || !record.get('StickCause_SysNick').inlist([ 'karantin', 'uhod', 'uhodreb', 'uhodnoreb', 'rebinv', 'vich', 'zabrebmin' ]))
							&& !ignoreCareFlag && storeEvnStickCarePerson.getCount() > 0
							&& storeEvnStickCarePerson.getAt(0).get('EvnStickCarePerson_id') > 0
						)*/
		
		if ( this.isUhod(oldRecord, person_age) && !this.isUhod(record, person_age) //ext6: подправил логику, уточнить.
			&& storeEvnStickCarePerson.getCount() > 0
			&& storeEvnStickCarePerson.getAt(0).get('EvnStickCarePerson_id') > 0
		) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( 'yes' == buttonId ) {
						//~ combo.fireEvent('change', combo, newValue, oldValue, true);
					}
					else {
						combo.setValue(oldValue);
					}
				},
				icon: Ext6.MessageBox.QUESTION,
				msg: langs('Данные из списка пациентов, нуждающихся в уходе, будут удалены. Изменить причину нетрудоспособности?'),
				title: langs('Вопрос')
			});
			return false;
		}
		
		// разблокируем поле Выдан ФИО при оформлении дубликата если есть список пациентов нужающихся в уходе
		if(
			vm.get('action') == 'add' 
			&& base_form.findField('EvnStick_IsOriginal').getRawValue() == 2
			&& !Ext6.isEmpty(win.dataFromOriginal)
		) {
			if (record && record.get('StickCause_SysNick').inlist(['karantin', 'uhod', 'uhodnoreb', 'uhodreb', 'rebinv', 'postvaccinal', 'vich'])) {
				//~ base_form.findField('EvnStickFullNameText').enable();//увел в bind
			} else {
				//меняем поля выдан ФИО и СНИЛС на значения из оригинала
				base_form.findField('EvnStickFullNameText').setValue(win.dataFromOriginal.get('Person_Fio'));								
				base_form.findField('Person_id').setValue(win.dataFromOriginal.get('Person_id'));
				base_form.findField('PersonEvn_id').setValue(win.dataFromOriginal.get('PersonEvn_id'));
				base_form.findField('Server_id').setValue(win.dataFromOriginal.get('Server_id'));

				win.Person_Snils = win.dataFromOriginal.get('Person_Snils');
				_this._checkSnils();

				base_form.findField('EvnStickFullNameText').disable();
			}
		}

		base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
		base_form.findField('EvnStick_BirthDate').setContainerVisible(false);
		base_form.findField('EvnStick_BirthDate').setRawValue('');

		base_form.findField('EvnStick_adoptDate').setAllowBlank(true);
		base_form.findField('EvnStick_adoptDate').setContainerVisible(false);
		base_form.findField('EvnStick_adoptDate').setRawValue('');
		base_form.findField('EvnStick_sstBegDate').setAllowBlank(true);
		base_form.findField('EvnStick_sstBegDate').setContainerVisible(false);
		base_form.findField('EvnStick_sstBegDate').setRawValue('');
		base_form.findField('EvnStick_sstEndDate').setAllowBlank(true);
		base_form.findField('EvnStick_sstEndDate').setContainerVisible(false);
		base_form.findField('EvnStick_sstEndDate').setRawValue('');
		base_form.findField('EvnStick_sstNum').setAllowBlank(true);
		base_form.findField('EvnStick_sstNum').setContainerVisible(false);
		base_form.findField('EvnStick_sstNum').setRawValue('');
		base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(false);
		base_form.findField('EvnStick_IsRegPregnancy').clearValue();
		base_form.findField('Org_did').setAllowBlank(true);
		base_form.findField('Org_did').setContainerVisible(false);
		base_form.findField('Org_did').setRawValue('');

		if ( record ) {
			switch ( record.get('StickCause_SysNick') ) {
				case 'dolsan':
				case 'kurort':
					win.queryById('EStEF_EvnStickCarePersonPanel').hide();
					base_form.findField('EvnStick_sstBegDate').setAllowBlank(false);
					base_form.findField('EvnStick_sstBegDate').setContainerVisible(true);
					base_form.findField('EvnStick_sstEndDate').setContainerVisible(true);

					if (getRegionNick()=='astra') {
						if (record.get('StickCause_Code') == '08') {
							base_form.findField('EvnStick_sstNum').setAllowBlank(false);
							base_form.findField('EvnStick_sstNum').setContainerVisible(true);
						}
						base_form.findField('EvnStick_sstEndDate').setAllowBlank(false);
					} else {
						if (!Ext6.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
							base_form.findField('EvnStick_sstNum').setAllowBlank(false);
						}
						base_form.findField('EvnStick_sstNum').setContainerVisible(true);
					}
					if (
						getRegionNick() == 'ufa'
						&& this.getPridStickLeaveTypeCode() == '37'
						&& ! Ext6.isEmpty(base_form.findField('PridEvnStickWorkRelease_endDate').getValue())
					) {
						base_form.findField('EvnStick_sstBegDate').setValue(base_form.findField('PridEvnStickWorkRelease_endDate').getValue());
						base_form.findField('EvnStick_sstBegDate').disable();
					}
					base_form.findField('Org_did').setAllowBlank(getRegionNick()=='kz');
					base_form.findField('Org_did').setContainerVisible(true);
					break;

				// Причина нетрудоспособности «05. Отпуск по беременности и родам»
				case 'pregn':
					win.queryById('EStEF_EvnStickCarePersonPanel').hide();

					// refs #120282
					if ( getRegionNick() != 'kz' ) {
						base_form.findField('EvnStick_BirthDate').setAllowBlank(false);
					} else {
						base_form.findField('EvnStick_BirthDate').setAllowBlank(true); // согласно задаче #6269 (c) Night, 2011-09-18
					}

					base_form.findField('EvnStick_BirthDate').setContainerVisible(true);

					base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(true);
					if (base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') == '020') {
						base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
					}
					break;

				case 'adopt':
					base_form.findField('EvnStick_adoptDate').setAllowBlank(true);
					base_form.findField('EvnStick_adoptDate').setContainerVisible(true);
					break;

				case 'uhod':
				case 'uhodnoreb':
				case 'uhodreb':
				case 'rebinv':
				case 'vich':
				case 'postvaccinal':

				// Заболевание ребенка из перечня Минздрава
				case 'zabrebmin':
					win.queryById('EStEF_EvnStickCarePersonPanel').show();
					break;

				case 'karantin':
					if (person_age <= 18) {
						win.queryById('EStEF_EvnStickCarePersonPanel').show();
					} else {
						win.queryById('EStEF_EvnStickCarePersonPanel').hide();
					}
					break;

				default:
					win.queryById('EStEF_EvnStickCarePersonPanel').hide();
					break;
			}
		}

		_this.checkRebUhod();

		var stick_cause_d = base_form.findField('StickCause_did');
		if (!Ext6.isEmpty(stick_cause_d.getValue())) {
			stick_cause_d.fireEvent('change', stick_cause_d, stick_cause_d.getValue());
		}

	},

	onEvnStick_stacBegDate: function(field, newValue, oldValue) {///
		eval(this.ini);
		var base_form = this.getView().FormPanel.getForm();
		base_form.findField('EvnStick_stacEndDate').setMinValue(newValue);
	},

	onEvnStickDop_pid: function(combo, newValue, oldValue){
		if(combo.stop>0) { combo.stop--; return; } //else if(combo.stop<0) combo.stop*=-1;
		
		if ( newValue <= 0 || Ext6.isEmpty(newValue) ) {
			return true;
		}

		eval(this.ini);
		
		var stick_workrelease_grid = this.queryById('EStEF_EvnStickWorkReleaseGrid');
		
		var i = 0;

		// Получаем выбранную запись
		var index = combo.getStore().findBy(function(rec) {
			return (rec.get('EvnStick_id') == newValue);
		});
		var record = combo.getStore().getAt(index);

		// блокируем кнопку добавить в списке пациентов нуждающихся в уходе
		win.queryById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].disable();

		// блокируем кнопку добавить в списке периодов освобождений
		me.panelEvnStickWorkRelease.queryById('plus').hide();

		// скрываем кнопку "=" рядом с дата начала в блоке режим в зоне "лечение в стационаре"
		win.queryById('EStEF_btnSetMinDateFromPS').setVisible(false);

		// скрываем кнопку "=" рядом с дата окончания в блоке режим в зоне "лечение в стационаре"
		win.queryById('EStEF_btnSetMaxDateFromPS').setVisible(false);


		base_form.findField('EvnStick_oid').disable();
		base_form.findField('EvnStick_BirthDate').disable();
		base_form.findField('EvnStick_irrDate').disable();
		base_form.findField('EvnStick_IsDisability').disable();
		base_form.findField('EvnStick_StickDT').disable();
		base_form.findField('EvnStick_IsRegPregnancy').disable();
		base_form.findField('EvnStick_mseDate').disable();
		base_form.findField('EvnStick_mseExamDate').disable();
		base_form.findField('EvnStick_mseRegDate').disable();
		base_form.findField('EvnStick_sstBegDate').disable();
		base_form.findField('EvnStick_sstEndDate').disable();
		base_form.findField('EvnStick_sstNum').disable();
		//~ base_form.findField('EvnStick_stacBegDate').disable();
		//~ base_form.findField('EvnStick_stacEndDate').disable();
		base_form.findField('EvnStickFullNameText').disable();
		base_form.findField('InvalidGroupType_id').disable();
		base_form.findField('UAddress_AddressText').disable();
		base_form.findField('Lpu_oid').disable();
		base_form.findField('MedStaffFact_id').disable();
		base_form.findField('Org_did').disable();
		base_form.findField('StickCause_id').disable();
		base_form.findField('StickIrregularity_id').disable();
		base_form.findField('StickLeaveType_id').disable();

		base_form.findField('StickOrder_id').disable();

		if ( record ) {
			// Устанавливаем значения полей
			base_form.findField('EvnStick_IsDisability').setValue(record.get('EvnStick_IsDisability'));
			base_form.findField('InvalidGroupType_id').setValue(record.get('InvalidGroupType_id'));
			base_form.findField('EvnStick_mseDate').setValue(Date.parseDate(record.get('EvnStick_mseDate'), 'd.m.Y'));
			base_form.findField('EvnStick_mseExamDate').setValue(Date.parseDate(record.get('EvnStick_mseExamDate'), 'd.m.Y'));
			base_form.findField('EvnStick_mseRegDate').setValue(Date.parseDate(record.get('EvnStick_mseRegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_prid').setValue(record.get('EvnStick_prid'));
			base_form.findField('EvnStick_setDate').setValue(Date.parseDate(record.get('EvnStick_setDate'), 'd.m.Y'));
			base_form.findField('EvnStick_stacBegDate').setValue(Date.parseDate(record.get('EvnStick_stacBegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_stacEndDate').setValue(Date.parseDate(record.get('EvnStick_stacEndDate'), 'd.m.Y'));

			// Предыдущий ЛВН
			base_form.findField('EvnStickLast_id').setValue(record.get('EvnStickLast_id'));//todo: проверить что работает. Было на EvnStickLast_Title

			base_form.findField('Lpu_oid').setValue(record.get('Lpu_oid'));
			base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
			base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
			base_form.findField('Person_id').setValue(record.get('Person_id'));
			base_form.findField('PersonEvn_id').setValue(record.get('PersonEvn_id'));
			base_form.findField('Server_id').setValue(record.get('Server_id'));
			base_form.findField('StickCause_did').setValue(record.get('StickCause_did'));
			base_form.findField('StickCause_id').setValue(record.get('StickCause_id'));
			base_form.findField('StickIrregularity_id').setValue(record.get('StickIrregularity_id'));
			base_form.findField('EvnStick_IsRegPregnancy').setValue(record.get('EvnStick_IsRegPregnancy'));
			base_form.findField('EvnStick_irrDate').setValue(Date.parseDate(record.get('EvnStick_irrDate'), 'd.m.Y'));
			base_form.findField('EvnStick_BirthDate').setValue(Date.parseDate(record.get('EvnStick_BirthDate'), 'd.m.Y'));
			base_form.findField('StickCauseDopType_id').setValue(record.get('StickCauseDopType_id'));
			base_form.findField('StickLeaveType_id').setValue(record.get('StickLeaveType_id'));

			base_form.findField('StickOrder_id').setValue(record.get('StickOrder_id'));

			base_form.findField('PridStickLeaveType_Code1').setValue(record.get('PridStickLeaveType_Code'));
			base_form.findField('EvnStick_sstBegDate').setValue(Date.parseDate(record.get('EvnStick_sstBegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_sstEndDate').setValue(Date.parseDate(record.get('EvnStick_sstEndDate'), 'd.m.Y'));
			base_form.findField('EvnStick_sstNum').setValue(record.get('EvnStick_sstNum'));

			base_form.findField('EvnStickFullNameText').setValue(record.get('Person_Fio'));
			//~ if(record.get('Person_Fio') == ''){
				//~ base_form.findField('EvnStick_IsOriginal').disable();
			//~ }

			base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), base_form.findField('StickCause_id').getValue());
			base_form.findField('StickIrregularity_id').fireEvent('change', base_form.findField('StickIrregularity_id'), base_form.findField('StickIrregularity_id').getValue());

			this.setEvnStickDisDate();

			if ( record.get('Org_did') && Number(record.get('Org_did')) > 0 ) {
				this._loadStore_Org_did(record.get('Org_did'));
			}

			if ( record.get('Org_id') && Number(record.get('Org_id')) > 0 ) {
				this.loadField_Org_id(record.get('Org_id'));
			}

			this._loadStoreEvnStickCarePerson({
				EvnStick_id: record.get('EvnStick_id')
			});
			// далее код из _func1GridEvnStickWorkRelease (ext2)
			var prev_records = getStoreRecords(
				stick_workrelease_grid.getStore(),
				{
					clearFilter: true
				}
			);

			var params = {
				EvnStick_id: record.get('EvnStick_id')
			}
			// если добавляется дубликат
			if(vm.get('action') == 'add' && base_form.findField('EvnStick_IsOriginal').getRawValue() == 2) {
				params.LoadSummPeriod = '1';
			}
			//если добавляется ЛВН по совместительству
			if( vm.get('action') == 'add' && base_form.findField('StickWorkType_id').getValue() == 2 ) {
				params.ignoreRegAndPaid = '1';
			}
			stick_workrelease_grid.getStore().load({
				params: params,
				callback: function() {
					me.setEvnStickDisDate();
					stick_workrelease_grid.getStore().each(function(record){
						record.set('EvnStickWorkRelease_id', -this.swGenTempId(stick_workrelease_grid.getStore()), 'EvnStickWorkRelease_id');

						// Устанавливаем данные о подписании
						record.set('SMPStatus_id', 2);
						record.set('Signatures_mid', null);
						record.set('SMP_Status_Name', null);
						record.set('SMP_updDT', null);
						record.set('SMP_updUser_Name', null);
						record.set('SVKStatus_id', 2);
						record.set('Signatures_wid', null);
						record.set('SVK_Status_Name', null);
						record.set('SVK_updDT', null);
						record.set('SVK_updUser_Name', null);
						record.set('EvnStickWorkRelease_IsInReg', null);
						record.set('EvnStickWorkRelease_IsPaid', null);
						
						// Usually called by the Ext.data.Store which owns the Record. Commits all changes made to the Record since either creation, or the last commit operation.
						// Developers should subscribe to the Ext.data.Store.update event to have their code notified of commit operations.
						record.commit();
					});

					prev_records.forEach(function(record) {
						var index = stick_workrelease_grid.getStore().find('EvnStickWorkRelease_id', record.EvnStickWorkRelease_id);
						if (record.RecordStatus_Code != 0 && record.EvnStickBase_id == base_form.findField('EvnStick_id').getValue() && index < 0) {
							record.RecordStatus_Code = 3;
							stick_workrelease_grid.getStore().loadData([record], true);
						}
					});
					stick_workrelease_grid.getStore().filterBy(function(record) {
						return record.get('RecordStatus_Code') != 3;
					});

					this.refreshFormPartsAccess();
				}.createDelegate(this)
			});

			_this.changeStickCauseDopType();
		}
		else {

			base_form.findField('EvnStick_BirthDate').setRawValue('');
			base_form.findField('EvnStick_disDate').setRawValue('');
			base_form.findField('EvnStick_irrDate').setRawValue('');
			base_form.findField('EvnStick_IsDisability').clearValue();
			base_form.findField('InvalidGroupType_id').reset();
			base_form.findField('EvnStick_StickDT').setRawValue('');
			base_form.findField('EvnStick_IsRegPregnancy').clearValue();
			base_form.findField('EvnStick_mseDate').setRawValue('');
			base_form.findField('EvnStick_mseExamDate').setRawValue('');
			base_form.findField('EvnStick_mseRegDate').setRawValue('');
			base_form.findField('EvnStick_setDate').setRawValue('');
			base_form.findField('EvnStick_sstBegDate').setRawValue('');
			base_form.findField('EvnStick_sstEndDate').setRawValue('');
			base_form.findField('EvnStick_sstNum').setRawValue('');
			base_form.findField('EvnStick_stacBegDate').setRawValue('');
			base_form.findField('EvnStick_stacEndDate').setRawValue('');
			base_form.findField('UAddress_AddressText').setRawValue('');
			base_form.findField('Lpu_oid').reset();
			base_form.findField('MedStaffFact_id').reset();
			base_form.findField('Org_did').reset();
			base_form.findField('StickCause_did').reset();

			base_form.findField('StickCause_id').reset();
			base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), base_form.findField('StickCause_id').getValue());

			base_form.findField('StickCauseDopType_id').reset();
			base_form.findField('StickIrregularity_id').reset();
			base_form.findField('StickLeaveType_id').reset();
			base_form.findField('StickOrder_id').reset();
			base_form.findField('EvnStick_oid').reset();


			// Очищаем списки пациентов, нуждающихся в уходе, и освобождений от работы
			me._clearEvnStickCarePersonGrid();

			stick_workrelease_grid.getStore().each(function(record) {
				if (record.get('RecordStatus_Code') != 0 && record.get('EvnStickBase_id') == base_form.findField('EvnStick_id').getValue()) {
					record.set('RecordStatus_Code', 3);
					record.commit();
				} else {
					stick_workrelease_grid.getStore().remove(record);
				}
			});
			stick_workrelease_grid.getStore().filterBy(function(record) {
				return record.get('RecordStatus_Code') != 3;
			});
		}

		base_form.findField('StickLeaveType_id').fireEvent('change', base_form.findField('StickLeaveType_id'), base_form.findField('StickLeaveType_id').getValue());
		base_form.findField('StickOrder_id').fireEvent('change', base_form.findField('StickOrder_id'), base_form.findField('StickOrder_id').getValue());
	},

	onEvnStick_oid: function(field, newValue, oldValue) {/// ==listenerChange_EvnStick_oid
		//TAG: onchange
		eval(this.ini);

		this._applyValueToFields_EvnStick_oid(base_form.findField('EvnStick_oid').getValue());
	},

	onEvnStickBase_consentDT: function(field, newVal, oldVal) {/// в base_form.load не исп.
		eval(this.ini);
		//if(field.stop>0) { field.stop--; return; } //else if(field.stop<0) field.stop*=-1;
		this.getViewModel().set('EvnStickBase_consentDT', newVal);
		this.getView().queryById('EvnStickBase_consentDT').setText(field.getRawValue());
	},
	onOrg_id: function(combo, newVal, oldVal) {//ext6
		combo.triggers.search.setVisible(Ext6.isEmpty(newVal));
		combo.triggers.clear.setVisible(!Ext6.isEmpty(newVal));
		if(combo.stop>0) { combo.stop--; return; } //else if(combo.stop<0) combo.stop*=-1;
		eval(this.ini);
		base_form.findField('EvnStick_OrgNick').setValue(combo.getFieldValue('Org_StickNick'));
	},
	onOrg_did: function(field, newValue, oldValue) {
		if(field.stop>0) { field.stop--; return; } //else if(field.stop<0) field.stop*=-1;
		eval(this.ini);
		if ( getRegionNick() != 'kz' && ! Ext6.isEmpty(newValue) ) {
			// refs #120282 - Номер путевки (Все, кроме Казахстана) Обязательно если редактируется Пользователем, место работы которого связано с организацией, указанной в поле «Санаторий».
			if( ! base_form.findField('EvnStick_sstNum').hidden && ! base_form.findField('EvnStick_sstNum').disabled){
				base_form.findField('EvnStick_sstNum').setAllowBlank(true);
				if(getGlobalOptions().org_id == newValue){
					base_form.findField('EvnStick_sstNum').setAllowBlank(false);
				}
			}

			Ext6.Ajax.request({
				url: '/?c=Org&m=getOrgOGRN',
				params: {Org_id: newValue},
				success: function(response, options){
					var responseObj = Ext6.util.JSON.decode(response.responseText);
					if (Ext6.isEmpty(responseObj.Org_OGRN)) {
						Ext6.Msg.alert(langs('Внимание'),langs('ОГРН для данной организации не указан. Обратитесь к администратору для заполнения.'));
						return false;
					}
				}
			});
		}
	},
	onEvnStick_sstNum: function(combo, newValue, oldValue) {
		eval(this.ini);
		base_form.findField('EvnStick_sstEndDate').setAllowBlank(true);

		// refs #120282 Дата окончания СКЛ (Все, кроме Казахстана) Должно быть обязательным для заполнения, если указан номер путевки.
		// EvnStick_sstEndDate - Дата окончания СКЛ
		if (getRegionNick() != 'kz' && !Ext6.isEmpty(newValue)) {
			base_form.findField('EvnStick_sstEndDate').setAllowBlank(false);
		}
	},
	onStickWorkType_id: function(combo) {// на изменение поля 'Тип занятости'
		eval(this.ini);
		if(combo.stop>0) { combo.stop--; return; } //else if(combo.stop<0) combo.stop*=-1;
		var newValue = combo.getValue();
		var i = 0;
		
		base_form.findField('EvnStickDop_pid').reset();
		base_form.findField('EvnStickDop_pid').getStore().removeAll();
		base_form.findField('EvnStickDop_pid').setAllowBlank(true);
		base_form.findField('EvnStickDop_pid').hide();

		if (
			getRegionNick() != 'kz'
			&& (
				newValue == 1
				|| newValue == 2
			)
		) {
			base_form.findField('EvnStick_OrgNick').setAllowBlank(false);
		} else {
			base_form.findField('EvnStick_OrgNick').setAllowBlank(true);
		}
		
		switch(parseInt(newValue)){

			// основная работа
			case 1:
				win.evnStickType = 1;

				base_form.findField('EvnStickDop_pid').reset();

				//~ if ( vm.get('action') != 'view' ) {
					//~ this.enableEdit(true);
					//~ win.queryById('EStEF_btnSetMinDateFromPS').setVisible(true);
					//~ win.queryById('EStEF_btnSetMaxDateFromPS').setVisible(true);
				//~ }

				win.queryById('EStEF_OrgFieldset').show();

				if( ! base_form.findField('Org_id').getValue()){
					this._setDefaultValueTo_Org_id();
				}

				if( ! base_form.findField('Post_Name').getValue()){
					this._setDefaultValueTo_Post_Name();
				}
				break;

			// работа по совместительству
			case 2:
				win.evnStickType = 2;
				// Загружаем список ЛВН, выданных по основному месту работы
				base_form.findField('EvnStickDop_pid').getStore().load({
					callback: function() {
						if ( base_form.findField('EvnStickDop_pid').getStore().getCount() == 1 ) {
							base_form.findField('EvnStickDop_pid').setValue(base_form.findField('EvnStickDop_pid').getStore().getAt(0).get('EvnStick_id'));//with event
						}
						if ( base_form.findField('EvnStickDop_pid').getStore().getCount() == 0 ) {
							base_form.findField('EvnStickDop_pid').getStore().loadData([{
								EvnStick_id: -1,
								EvnStick_Num: 'Отсутствует',
								EvnStick_Title: 'Отсутствует'
							}]);
						}

						base_form.findField('EvnStickDop_pid').setAllowBlank(false);
						base_form.findField('EvnStickDop_pid').show();
					}, //.createDelegate(this),
					params: {
						'EvnStick_mid': base_form.findField('EvnStick_mid').getValue()
					}
				});

				win.queryById('EStEF_OrgFieldset').show();
				break;

			// стоит на учете в службе занятости
			case 3:
				win.evnStickType = 1;
				base_form.findField('Org_id').reset();
				base_form.findField('EvnStick_OrgNick').setRawValue('');
				base_form.findField('Post_Name').setRawValue('');

				win.queryById('EStEF_OrgFieldset').hide();
				break;
			default:
				base_form.findField('Org_id').reset();
				base_form.findField('EvnStick_OrgNick').setRawValue('');
				base_form.findField('Post_Name').setRawValue('');

				win.queryById('EStEF_OrgFieldset').hide();
				break;
		}

		this.checkOrgFieldDisabled();
	},

	changeStickCauseDopType: function(field, newValue, oldValue){/// на изменение поля "Доп. код нетрудоспособности"
		if(field && field.stop>0) { field.stop--; return; } //else if(field.stop<0) field.stop*=-1;
		eval(this.ini);
		var combo = base_form.findField('StickCauseDopType_id');

		var stick_cause_sys_nick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');

		if (getRegionNick() != 'kz') {
			if (combo.getValue() && combo.getFieldValue('StickCauseDopType_Code').inlist(['017','018','019'])) {
				['EvnStick_sstBegDate', 'EvnStick_sstEndDate', 'Org_did'].forEach(function(el){
					base_form.findField(el).setAllowBlank(false);
					base_form.findField(el).setContainerVisible(true);
					base_form.findField(el).setValue(base_form.findField(el).getValue());
				});
				base_form.findField('EvnStick_sstEndDate').setAllowBlank(getRegionNick()!='astra');
				base_form.findField('EvnStick_sstNum').setContainerVisible(true);
				if (getRegionNick()=='astra' || !Ext6.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
					base_form.findField('EvnStick_sstNum').setAllowBlank(false);
				}
				base_form.findField('EvnStick_sstNum').setValue(base_form.findField('EvnStick_sstNum').getValue());
			} else if (!Ext6.isEmpty(oldValue)) {
				var stick_cause = base_form.findField('StickCause_id');
				stick_cause.fireEvent('change', stick_cause, stick_cause.getValue());
			}
		}
		
		var isRegPregnancy = base_form.findField('EvnStick_IsRegPregnancy');
		//поле должно быть обязательным для ввода, если причина нетрудоспособности - отпуск по беременности и родам
		if(
			stick_cause_sys_nick == 'pregn' 
			&& base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') != '020'
		) {
			isRegPregnancy.setAllowBlank(false);
		} else {
			isRegPregnancy.setAllowBlank(true);
		}
		
		if(
			stick_cause_sys_nick == 'pregn' 
			&& base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') == '020'
			&& vm.get('action') == 'add' && base_form.findField('EvnStick_IsOriginal').getRawValue() != 2
		) {
			base_form.findField('EvnStick_stacBegDate').setValue('');
			base_form.findField('EvnStick_stacEndDate').setValue('');
		}

		if (stick_cause_sys_nick == 'pregn' && combo.getFieldValue('StickCauseDopType_Code') != '020') {
			base_form.findField('EvnStick_BirthDate').setAllowBlank(false);
		} else {
			base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
		}
		/* убрано в ред.146337
		 * if (stick_cause_sys_nick == 'pregn' && combo.getFieldValue('StickCauseDopType_Code') == '020') {
			base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
		}*/
	},

	onStickCause_did: function(combo, newValue, oldValue) {///
		if(combo.stop>0) { combo.stop--; return; } //else if(combo.stop<0) combo.stop*=-1;
		eval(this.ini);

		if (getRegionNick() != 'kz') {
			if ( newValue ) {
				base_form.findField('EvnStick_StickDT').setContainerVisible(true);
				base_form.findField('EvnStick_StickDT').setAllowBlank(false);
			} else if(!Ext6.isEmpty(oldValue)) {
				base_form.findField('EvnStick_StickDT').setContainerVisible(false);
				combo.stop = 1;
				base_form.findField('EvnStick_StickDT').setValue(null);
				base_form.findField('EvnStick_StickDT').setAllowBlank(true);

				var stick_cause = base_form.findField('StickCause_id');
				stick_cause.fireEvent('change', stick_cause, stick_cause.getValue());
			}

			switch(combo.getFieldValue('StickCause_SysNick')) {
				case 'pregn':
					base_form.findField('EvnStick_BirthDate').setAllowBlank(getRegionNick()!='astra');
					base_form.findField('EvnStick_BirthDate').setContainerVisible(true);
					base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(true);
					break;
				case 'dolsan':
					base_form.findField('EvnStick_sstBegDate').setAllowBlank(false);
					base_form.findField('EvnStick_sstBegDate').setContainerVisible(true);
					base_form.findField('EvnStick_sstEndDate').setContainerVisible(true);
					base_form.findField('EvnStick_sstEndDate').setAllowBlank(getRegionNick()!='astra');
					base_form.findField('EvnStick_sstNum').setContainerVisible(true);
					if (getRegionNick()=='astra' || !Ext6.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
						base_form.findField('EvnStick_sstNum').setAllowBlank(false);
					}
					base_form.findField('EvnStick_sstNum').setAllowBlank(false);
					base_form.findField('Org_did').setAllowBlank(false);
					base_form.findField('Org_did').setContainerVisible(true);
					break;
			}
		}
	},
	/* TAG: конец обработчиков onChange и onBlur */
	
	triggerEvnStick_OrgNick: function() {
		eval(this.ini);

		if ( base_form.findField('EvnStick_OrgNick').disabled ) {
			return false;
		}

		base_form.findField('EvnStick_OrgNick').setValue(base_form.findField('Org_id').getRawValue().substr(0,255));
		/*
		var record = base_form.findField('Org_id').getStore().getAt(0);

		if (record && record.get('Org_StickNick') && record.get('Org_StickNick').length > 0) {
			return false;
		}

		base_form.findField('EvnStick_OrgNick').setValue(base_form.findField('Org_id').getRawValue().substr(0,255));*/
	},

	_findEvnStick_stacDates: function() {//+
		log('_findEvnStick_stacDates');
		// Поиск дат (дата начала первого движения и дату окончания последнего движения) для блока "Лечение в стационаре"
		eval(this.ini);
		var EvnSectionDates = this.getBegEndDatesInStac();
		if(Ext6.isEmpty(win.advanceParams.stacBegDate) && EvnSectionDates){
			if( ! Ext6.isEmpty(EvnSectionDates) && ! Ext6.isEmpty(EvnSectionDates.EvnSection_setDate)){
				win.advanceParams.stacBegDate = EvnSectionDates.EvnSection_setDate;
			}
		}

		if(Ext6.isEmpty(win.advanceParams.stacEndDate) && EvnSectionDates){
			if( ! Ext6.isEmpty(EvnSectionDates) && ! Ext6.isEmpty(EvnSectionDates.EvnSection_disDate)){
				win.advanceParams.stacEndDate = EvnSectionDates.EvnSection_disDate;
			}
		}
	},

	

	_isLoaded_WorkRelease: function(){//+
	/**
	 * Проверяем загружены ли периоды освобождений от работы
	 * @returns bool (true - данные загружены, false - данные не загружены)
	 * @private
	 */
		eval(this.ini);
		// загружаем данные только если список пустой
		//return win.gridEvnStickWorkRelease.getStore().getCount() > 0;
		if(win.panelEvnStickWorkRelease.isLoaded == true ){
			return true;
		}
		return false;
	},
	
	onExpand_EvnStickWorkReleasePanel: function(panel) {
		eval(this.ini);
		if(this._isLoaded_WorkRelease() == false){

			var evn_stick_id = base_form.findField('EvnStick_id').getValue();
			var evn_stick_dop_pid = base_form.findField('EvnStickDop_pid').getValue();

			this._load_WorkRelease({
				EvnStick_id: evn_stick_id,
				EvnStickDop_pid: (evn_stick_dop_pid > 0)? evn_stick_dop_pid:null
			}, function(){
				panel.isLoaded = true;

				if (Ext6.isEmpty(base_form.findField('EvnStick_disDate').getValue())) {
					_this.setEvnStickDisDate();
				}

				if ( panel.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0 ) {
					win.queryById('EStEF_StickLeavePanel').expand();
				}
			}.createDelegate(win));
		}
	},

	_defaultLoad_WorkRelease: function(){//+
	/**
	 * Загружаем данные с параметрами по умолчанию
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
		eval(this.ini);

		var evn_stick_dop_pid = win.FormPanel.getForm().findField('EvnStickDop_pid').getValue();
		if(Ext6.isEmpty(evn_stick_dop_pid)){
			evn_stick_dop_pid = null;
		}

		this._load_WorkRelease({
			EvnStick_id:  win.FormPanel.getForm().findField('EvnStick_id').getValue(),
			EvnStickDop_pid: evn_stick_dop_pid
		}, function(){
			win.panelEvnStickWorkRelease.isLoaded = true;

			if (Ext6.isEmpty(win.FormPanel.getForm().findField('EvnStick_disDate').getValue())) {
				_this.setEvnStickDisDate();
			}
		});
		return win;
	},

	_load_WorkRelease: function(params, callback){//+
		/**
		 * Загружаем данные с переданными параметрами
		 * @param params
		 * @param callback
		 * @private
		 */
		eval(this.ini);
		win.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().load({
			params: params,
			callback: callback
		});
		win.panelEvnStickWorkRelease.isLoaded = true;
	},

	_removeAll_WorkRelease: function(){///
	/**
	 * Очищаем список
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
		eval(this.ini);
		win.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().removeAll();
		win.panelEvnStickWorkRelease.isLoaded = false;

		return me;
	},

	_loadPersonInfo: function(){/// Информация о пациенте
		eval(this.ini);

		if(Ext6.isEmpty(win.PersonInfo)){
			return false;
		}

		win.PersonInfo.load({
			Person_id: (win.params.Person_id ? win.params.Person_id : ''),
			Person_Birthday: (win.params.Person_Birthday ? win.params.Person_Birthday : ''),
			Person_Firname: (win.params.Person_Firname ? win.params.Person_Firname : ''),
			Person_Secname: (win.params.Person_Secname ? win.params.Person_Secname : ''),
			Person_Surname: (win.params.Person_Surname ? win.params.Person_Surname : ''),
			callback: function() {
				win.advanceParams.Person_Snils = win.Person_Snils;
				if(vm.get('action')=='add') {
					win.Person_Snils = win.PersonInfo.getFieldValue('Person_Snils');
					_this._checkSnils();
				}

				//~ clearDateAfterPersonDeath('personpanelid', 'EStEF_PersonInformationFrame', base_form.findField('EvnStick_setDate'));//похоже, это уже не нужно

				Ext6.Ajax.request({
					url: '/?c=Person&m=getAddressByPersonId',
					params: {
						Person_id: base_form.findField('Person_id').getValue()
					},
					success: function(response){
						var resp = Ext6.util.JSON.decode(response.responseText);
						base_form.findField('UAddress_Zip').setValue(resp[0].UAddress_Zip);
						base_form.findField('UKLCountry_id').setValue(resp[0].UKLCountry_id);
						base_form.findField('UKLRGN_id').setValue(resp[0].UKLRGN_id);
						base_form.findField('UKLSubRGN_id').setValue(resp[0].UKLSubRGN_id);
						base_form.findField('UKLCity_id').setValue(resp[0].UKLCity_id);
						base_form.findField('UPersonSprTerrDop_id').setValue(resp[0].UPersonSprTerrDop_id);
						base_form.findField('UKLTown_id').setValue(resp[0].UKLTown_id);
						base_form.findField('UKLStreet_id').setValue(resp[0].UKLStreet_id);
						base_form.findField('UAddress_House').setValue(resp[0].UAddress_House);
						base_form.findField('UAddress_Corpus').setValue(resp[0].UAddress_Corpus);
						base_form.findField('UAddress_Flat').setValue(resp[0].UAddress_Flat);
						base_form.findField('UAddress_AddressText').setValue(resp[0].UAddress_AddressText);
						base_form.findField('UAddress_Address').setValue(resp[0].UAddress_Address);
					}
				});
			}
		});
	},

	_resetForm: function(){//+
		eval(this.ini);
		me.FormPanel.getForm().reset();
		return me;
	},

	_resetConfig: function(){//+
		eval(this.ini);
		vm.set('regionNick', getRegionNick());
		vm.set('action', null);
		me.callback = Ext6.emptyFn;
		me.evnStickType = 1;
		me.formStatus = 'edit';
		me.JobOrg_id = null;
		vm.set('link',false);
		me.parentClass = '';
		me.parentNum = null;
		me.Person_Post = null;
		me.userMedStaffFactId = null;
		me.CurLpuSection_id = 0;
		me.CurLpuUnit_id = 0;
		me.CurLpuBuilding_id = 0;
		me.IngoreMSFFilter = 0;
		me.StickReg = 0;
		vm.set('Signatures_id', null);
		vm.set('Signatures_iid', null);
		vm.set('isTubDiag', false);
		me.hasWorkReleaseIsInReg = false;
		me.EvnStick_id = null;
		me.EvnStick_mid = null;
		me.EvnStick_pid = null;
		vm.set('fromList', false);
		vm.set('isPaid', false);
		vm.set('isInReg', false);
		vm.set('isHasDvijeniaInStac24', null);
		_this.setStatus(0, 'Irr');
		_this.setStatus(0, 'Leave');
		return me;
	},

	
	doOpenSignHistory_WorkRelease: function() {//переделано без доп.окон
		/*eval(this.ini);
		var params = {};
		var grid = win.queryById('EStEF_EvnStickWorkReleaseGrid');
		var selected_record = grid.getSelectedRecord();
		var SignObject = options.SignObject;
		params.SignObject = options.SignObject;
		if (!selected_record && !SignObject.inlist(['leave', 'irr'])) return false;
		switch(SignObject) {
			case 'MP':
				params.Signatures_id = selected_record.get('Signatures_mid');
				break;
			case 'VK':
				params.Signatures_id = selected_record.get('Signatures_wid');
				break;
			case 'leave':
				params.Signatures_id = win.Signatures_id;
				break;
			case 'irr':
				params.Signatures_id = win.Signatures_iid;
				break;
		}
		getWnd('swStickVersionListWindow').show(params);*/
	},
	openEvnStickSignHistory: function(options, label) {//неактуально
		eval(this.ini);
		var grid = win.queryById('EStEF_EvnStickWorkReleaseGrid'),
			selected_record = grid.getSelectedRecord(),
			SignObject = options.SignObject;
		var params = {};
		params.SignObject = options.SignObject;
		if (!selected_record && !SignObject.inlist(['leave', 'irr'])) return false;
		switch(SignObject) {
			case 'MP':
				params.Signatures_id = selected_record.get('Signatures_mid');
				break;
			case 'VK':
				params.Signatures_id = selected_record.get('Signatures_wid');
				break;
			case 'leave':
				params.Signatures_id = vm.get('Signatures_id');
				break;
			case 'irr':
				params.Signatures_id = vm.get('Signatures_iid');
				break;
		}
		
		if(Ext6.isEmpty(params.Signatures_id)) return;
		Ext6.Ajax.request({
			params: params,
			url: '/?c=Stick&m=loadStickVersionList',
			success: function(response){
				
				var data = Ext6.util.JSON.decode(response.responseText);
				
				if(!Ext6.isEmpty(data.length>0)) {
					data.push(data[0]);
					label.labeltip = Ext6.create('Ext6.tip.ToolTip', {
						html: '',
						autoHide: true,
						closable: false
					});
					
					var s = '<table class="lvn-sign-table"><tr><th>Версия</th><th>Дата и время</th><th>Пользователь</th></tr>';
					data.forEach(function(rec) {
						s+='<tr>';
						s+='<td>'+rec.Signatures_Version+'</td><td>'+rec.SignaturesHistory_insDT+'</td><td>'+rec.PMUser_Name+'</td>';
						s+='</tr>';
					});
					s+='</table>';
					
					label.labeltip.setHtml(s);
					label.labeltip.setBodyStyle('padding','0px')
					win.labeltip = label.labeltip;
					
					var rect = label.getBoundingClientRect();
					label.labeltip.showAt([rect.x+rect.width, rect.y+rect.height]);
					
				}
			}
		});
	},
	triggerSearchPerson: function() {
		this.test();//пусть будет
		eval(this.ini);

		if ( base_form.findField('EvnStickFullNameText').disabled ) {
			return false;
		}

		getWnd('swPersonSearchWindowExt6').show({
			onSelect: function(person_data) {
				if(!Ext6.isEmpty(person_data.Person_Snils)) {
					me.Person_Snils = person_data.Person_Snils;
				} else {
					me.Person_Snils = null;
				}
				
							
				base_form.findField('Person_id').setValue(person_data.Person_id);
				base_form.findField('PersonEvn_id').setValue(person_data.PersonEvn_id);
				base_form.findField('Server_id').setValue(person_data.Server_id);

				_this._checkSnils();

				var newValueEvnStickFullNameText = person_data.PersonSurName_SurName + ' ' + person_data.PersonFirName_FirName + ' ' + person_data.PersonSecName_SecName;
				base_form.findField('EvnStickFullNameText').setValue(newValueEvnStickFullNameText);
				//~ base_form.findField('EvnStickFullNameText').fireEvent('change', base_form.findField('EvnStickFullNameText'), newValueEvnStickFullNameText);


				if ( base_form.findField('EvnStickFullNameText').getValue() != '' ) {
					base_form.findField('EvnStick_IsOriginal').enable();
				} else {
					base_form.findField('EvnStick_IsOriginal').disable();
				}


				getWnd('swPersonSearchWindowExt6').hide();

				win.mask(langs('Получение адреса регистрации...'));
				Ext6.Ajax.request({
					params: {Person_id: person_data.Person_id},
					url: '/?c=Person&m=getAddressByPersonId',
					success: function(response){
						win.unmask();
						var resp = Ext6.util.JSON.decode(response.responseText);
						base_form.findField('UAddress_Zip').setValue(resp[0].UAddress_Zip);
						base_form.findField('UKLCountry_id').setValue(resp[0].UKLCountry_id);
						base_form.findField('UKLRGN_id').setValue(resp[0].UKLRGN_id);
						base_form.findField('UKLSubRGN_id').setValue(resp[0].UKLSubRGN_id);
						base_form.findField('UKLCity_id').setValue(resp[0].UKLCity_id);
						base_form.findField('UPersonSprTerrDop_id').setValue(resp[0].UPersonSprTerrDop_id);
						base_form.findField('UKLTown_id').setValue(resp[0].UKLTown_id);
						base_form.findField('UKLStreet_id').setValue(resp[0].UKLStreet_id);
						base_form.findField('UAddress_House').setValue(resp[0].UAddress_House);
						base_form.findField('UAddress_Corpus').setValue(resp[0].UAddress_Corpus);
						base_form.findField('UAddress_Flat').setValue(resp[0].UAddress_Flat);
						base_form.findField('UAddress_AddressText').setValue(resp[0].UAddress_AddressText);
						base_form.findField('UAddress_Address').setValue(resp[0].UAddress_Address);
					}
				});
			}.createDelegate(this),
			searchMode: 'all'
		});
	},
	onSprLoad: function(args) {
		this.show(args[0]);
	},
	//открытие формы:
	show: function(args){
		eval(this.ini);
		win.restore();
		win.center();
		win.maximize();
		win.queryById('buttonMenuPrint').show();

		win.mask(LOAD_WAIT);
		
		this._resetConfig();
		
		this.suspendChange(1);//для reset
		var fields = win.query('field', win);
		for (i=0; i < fields.length; i++) if(fields[i].getValue && Ext6.isEmpty(fields[i].getValue())) fields[i].stop--;//отмена для тех полей что не сработают
		this._resetForm();
		this.suspendChange(0);//после reset
		
		if ( ! args || ! args.formParams ) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function(){
				this.doHideForm();
			});

			return false;
		}

		// Сохраняем полученные данные
		this._applyParams(args);

		// hide-им, expand-им, enable-им, disable-им, collapse-им элементы формы
		this._toggleFormElements();

		// Обрабаываем поля формы в зависимости от полученных данных
		this._setFormFields();
		
		win.panelEvnStickCarePerson.refreshTitle();
		win.panelEvnStickWorkRelease.refreshTitle();

		// Загружаем данные пациента
		this._loadPersonInfo();

		this._removeAll_WorkRelease();

		// Фильтруем причину нетрудоспособности
		this.filterStickCause();

		// Поиск движений
		this.getDvijeniaKVC();

		this._findEvnStick_stacDates();

		var base_form = win.FormPanel.getForm();
		//~ base_form.findField('EvnStickLast_id').getStore().removeAll();
		win.EvnStickLast_val = {};

		switch(vm.get('action')){
			// При добавлении действия одинаковые
			case 'add':
				this.showAsAdd();
				break;

			case 'edit':
			case 'view':
			case 'copy':
				this.showAsEditOrView();
				break;
			default:
				this.doHideForm();
				break;
		}
		base_form.findField('EvnStickLast_id').getStore().proxy.extraParams = {'Person_id': base_form.findField('Person_id').getValue()};
		base_form.findField('EvnStickLast_id').getStore().load();

		base_form.findField('EvnStick_oid').getStore().proxy.extraParams = {'EvnStick_mid': base_form.findField('EvnStick_mid').getValue()};
			
		base_form.isValid();
	},

	showAsAdd: function() {
		eval(this.ini);
		
		if(me.FormPanel.getForm().findField('EvnStickCopy_id').getValue()) {
			_this._loadStore_EvnStick_oid(function() {
				var ind = me.queryById('EvnStick_oid').getStore().findBy(function(rec) {
					return rec.get('EvnStick_id')==me.FormPanel.getForm().findField('EvnStickCopy_id').getValue();
				});
				base_form.findField('EvnStick_IsOriginal').suspendEvents();
				base_form.findField('EvnStick_IsOriginal').setValue(true);
				base_form.findField('EvnStick_IsOriginal').resumeEvents();
				if(ind>=0) {
					var el = me.queryById('EvnStick_oid').getStore().getAt(ind);
					base_form.findField('EvnStick_oid').setValue(el.get('EvnStick_id'));
					base_form.findField('EvnStick_oid').fireEvent('blur', base_form.findField('EvnStick_oid'));
					me.queryById('EvnStick_oid').show();
				} else {
					Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось загрузить данные оригинала ЛВН'));
					win.hide();
				}
			});
			win.unmask();
			return;
		}

		win.Lpu_id = getGlobalOptions().lpu_id;

		this.onEnableEdit(true);// открываем все элементы для редактирования

		this.checkOrgFieldDisabled();// Устанавливаем обязательность поля "Организация"

		if (!Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			base_form.findField('EvnStick_IsOriginal').clearValue();
			vm.set('EvnStick_IsNotOriginal', false);
			base_form.findField('EvnStick_setDate').setValue(null);
		}

		if (Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			// Устанавливаем тип занятости по умолчанию - Основная работа
			
			base_form.findField('StickWorkType_id').setValue(1);//with event
		}
		
		//base_form.findField('EvnStick_OrgNick').setAllowBlank(getRegionNick() == 'kz');
		
		base_form.findField('StickWorkType_id').stop = 0;
		base_form.findField('StickWorkType_id').fireEvent('change', base_form.findField('StickWorkType_id'), base_form.findField('StickWorkType_id').getValue());

		if (Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			// Устанавливаем причину нетрудоспособности по умолчанию - Заболевание
			var StickCause_SysNick = 'desease';
			if (win.params.StickCause_SysNick) {
				StickCause_SysNick = win.params.StickCause_SysNick;
			}
			var index = base_form.findField('StickCause_id').getStore().findBy(function(rec) {
				if (rec.get('StickCause_SysNick') == StickCause_SysNick) {
					return true;
				}
				else {
					return false;
				}
			});
			var record = base_form.findField('StickCause_id').getStore().getAt(index);

			if (record) {
				base_form.findField('StickCause_id').setValue(record.get('StickCause_id'));
				//this.setFieldValue(
			}
		}

		if (Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			// Устанавливаем порядок выдачи по умолчанию - Первичный ЛВН
			var StickOrder_Code = '1';
			if (win.params.StickOrder_Code) {
				StickOrder_Code = win.params.StickOrder_Code;
			}
			
			base_form.findField('StickOrder_id').setValue(StickOrder_Code == '1'); // вместо:
			/*index = base_form.findField('StickOrder_spr').getStore().findBy(function(rec) {
				if (rec.get('StickOrder_Code') == StickOrder_Code) {
					return true;
				}
				else {
					return false;
				}
			});
			record = base_form.findField('StickOrder_spr').getStore().getAt(index);

			if (record) {
				base_form.findField('StickOrder_id').setRawValue(record.get('StickOrder_id')=='1');
			}*/
		}

		base_form.findField('StickOrder_id').fireEvent('change', base_form.findField('StickOrder_id'), base_form.findField('StickOrder_id').getValue());

		if (win.params.EvnStick_prid) {
			Ext6.Ajax.request({
				url: '/?c=Stick&m=getEvnStickPridValues',
				params: {
					EvnStick_prid: win.params.EvnStick_prid
				},
				callback: function(opt, success, response) {
					if (success && response.responseText.length > 0) {
						var result = Ext6.util.JSON.decode(response.responseText);
						if (result.success) {
							base_form.findField('EvnStick_prid').setValue(win.params.EvnStick_prid);
							if (result.StickWorkType_id != 2) {
								this.setValue(base_form.findField('StickWorkType_id'), result.StickWorkType_id);
							} else {
								base_form.findField('StickWorkType_id').setValue(result.StickWorkType_id);
							}

							base_form.findField('PridStickLeaveType_Code2').setValue(result.PridStickLeaveType_Code2);
							base_form.findField('MaxDaysLimitAfterStac').setValue(result.MaxDaysLimitAfterStac);
							this.getWorkReleaseSumm(win.params.EvnStick_prid);
							base_form.findField('EvnStickLast_id').setValue(result.EvnStickLast_id);
							base_form.findField('PridEvnStickWorkRelease_endDate').setValue(result.PridEvnStickWorkRelease_endDate);


							if (
								getRegionNick() == 'ufa'
								&& this.getPridStickLeaveTypeCode() == '37'
								&& ! Ext6.isEmpty(base_form.findField('PridEvnStickWorkRelease_endDate').getValue())
							) {

								// Дата начала СКЛ
								base_form.findField('EvnStick_sstBegDate').setValue(base_form.findField('PridEvnStickWorkRelease_endDate').getValue());
								base_form.findField('EvnStick_sstBegDate').disable();
							}
						}
					}
				}
			});
		}
		base_form.findField('StickIrregularity_id').fireEvent('change', base_form.findField('StickIrregularity_id'), base_form.findField('StickIrregularity_id').getValue());
		//~ this.fireEventChange(base_form.findField('StickLeaveType_id'));
		base_form.findField('StickLeaveType_id').fireEvent('select');

		/*if (win.JobOrg_id) {//удалено в ред.152835
			var org_id = win.JobOrg_id;

			if (org_id != null && Number(org_id) > 0) {
				base_form.findField('Org_id').getStore().load({
					callback: function(records, options, success) {
						if ( success ) {
							base_form.findField('Org_id').setValue(org_id);

							var rec = base_form.findField('Org_id').getStore().getAt(0);

							if ( rec && rec.get('Org_StickNick') && rec.get('Org_StickNick').length > 0 ) {
								base_form.findField('EvnStick_OrgNick').setValue(rec.get('Org_StickNick'));
							}
						}
					},
					params: {
						Org_id: org_id,
						OrgType: 'org'
					}
				});
			}
		}*/
		
		//устанавливаем значение по умолчанию в поле "Наименование для указания ЛВН"
		/*Ext6.Ajax.request({//убрано в ред.154879
			url: '/?c=Org&m=getOrgData',
			params: {
				Org_id: getGlobalOptions().lpu_id
			},
			success: function(response, opt) {
				var result = Ext6.util.JSON.decode(response.responseText);
				
				if( !Ext6.isEmpty(result[0]) && !Ext6.isEmpty(result[0].Org_StickNick) ) {
					base_form.findField('EvnStick_OrgNick').setValue(result[0].Org_StickNick);
				}
			}
		});*/

		this._setDefaultValueTo_Org_id();

		if (win.params.Org_did) {
			var org_id = win.params.Org_did;

			if (org_id != null && Number(org_id) > 0) {
				base_form.findField('Org_did').getStore().load({
					callback: function(records, options, success) {
						if ( success ) {
							base_form.findField('Org_did').setValue(org_id);
						}
					},
					params: {
						Org_id: org_id,
						OrgType: 'org'
					}
				});
			}
		}

		if (win.Person_Post) {
			base_form.findField('Post_Name').setValue(win.Person_Post);
		}

		base_form.findField('EvnStick_setDate').setValue(
			Ext6.isEmpty(win.advanceParams.stacEndDate) || base_form.findField('EvnStickBase_IsFSS').getValue()
			? base_form.findField('EvnStick_setDate').getValue()
			: win.advanceParams.stacEndDate);

		setCurrentDateTime({
			dateField: base_form.findField('EvnStick_setDate'),
			loadMask: false,
			setDate: (Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue()) && Ext6.isEmpty(base_form.findField('EvnStick_setDate').getValue()) ? true : false),
			windowId: win.id,
			callback: function() {
				var advanceParams = win.advanceParams;
				var person_age = sw4.GetPersonAge(win.advanceParams.Person_Birthday, base_form.findField('EvnStick_setDate').getValue()); //#132979 формат даты дня рождения  Ext6.Date.parse(, "Y-m-dTH:i:s")
				//~ var person_age = swGetPersonAge(me.advanceParams.Person_Birthday, base_form.findField('EvnStick_setDate').getValue());//вместо
				var person_fio = advanceParams.Person_Surname + ' ' + advanceParams.Person_Firname + ' ' + advanceParams.Person_Secname;

				if (advanceParams.EvnStick_Num) {
					base_form.findField('EvnStick_Num').setValue(advanceParams.EvnStick_Num);
					//~ this.fireEventChange(base_form.findField('EvnStick_Num'));
					base_form.findField('EvnStick_Num').fireEvent('change', base_form.findField('EvnStick_Num'), base_form.findField('EvnStick_Num').getValue());
				}

				_this.filterStickCause();

				if ( person_age < 18 && Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue()) ) {
					index = base_form.findField('StickCause_id').getStore().findBy(function(rec) {
						if ( rec.get('StickCause_SysNick') == 'uhodnoreb' ) {
							return true;
						}
						else {
							return false;
						}
					});

					record = base_form.findField('StickCause_id').getStore().getAt(index);

					if ( record ) {
						base_form.findField('StickCause_id').setValue(record.get('StickCause_id'));
					}

					// Добавляем строку в EvnStickCarePersonGrid

					if(base_form.findField('Person_id').getValue() == -1){
						base_form.findField('Person_id').setValue(advanceParams.Person_id);
					}

					win.queryById('EStEF_EvnStickCarePersonGrid').getStore().loadData([{
						accessType: 'edit',
						EvnStickCarePerson_id: -1,
						Person_id: base_form.findField('Person_id').getValue(),
						RecordStatus_Code: 0,
						Person_Age: person_age,
						Person_Fio: person_fio
					}]);
					_this.checkRebUhod();

					_this.clearFio();//~ base_form.findField('EvnStickFullNameText').clearFio();
				}
				else {
					// Установить человека в поле "Выдан ФИО"
					base_form.findField('EvnStickFullNameText').setValue(person_fio);
					base_form.findField('Person_id').setValue(advanceParams.Person_id);
					//~ if ( base_form.findField('EvnStickFullNameText').getValue == '' ) {
						//~ base_form.findField('EvnStick_IsOriginal').disable();
					//~ }

					//~ _this.checkFieldDisabled('EvnStickFullNameText');
				}
			} //.createDelegate(this)
		});


		this.setMaxDateForSetDate();

		this._setDefaultValueTo_EvnStick_stacBegDate();
		this._setDefaultValueTo_EvnStick_stacEndDate();

		base_form.findField('EvnStickFullNameText').focus(true, 250);

		win.unmask();
	},

	showAsEditOrView: function(){//+
		eval(this.ini);
		var regionNick = getRegionNick();

		// В зависимости от accessType переопределяем action
		if(base_form.findField('accessType').getValue() == 'view'){
			vm.set('action', 'view');
		}
		_this.suspendChange(1);//для base_form.load
		base_form.load({
			url: ((win.evnStickType == 1)?'/?c=Stick&m=loadEvnStickEditForm':'/?c=Stick&m=loadEvnStickDopEditForm'),
			params: {
				EvnStick_id: base_form.findField('EvnStick_id').getValue(),// EvnStick_id,
				EvnStick_pid: base_form.findField('EvnStick_pid').getValue(), //EvnStick_pid,
				archiveRecord: win.archiveRecord
			},
			failure: function() {
				win.unmask();
				Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() {win.hide();});
			},
			success: function(frm, act) {
				//TAG: заполнение формы значениями
				_this.suspendChange(0);//после base_form.load
				_this.suspendChange(1);//для base_form.setValues
				for(k in act.result.data) if(act.result.data[k]==null && base_form.findField(k)) base_form.findField(k).stop-=2;//за Load и setValues
				base_form.setValues(act.result.data);//не срабатывает модель на form.load
				
				base_form.findField('PersonSnils').setValue(act.result.data.Person_Snils);
				me.delAccessType = act.result.data.delAccessType;
				me.cancelAccessType = act.result.data.cancelAccessType;
				
				{//ext6
					win.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().proxy.extraParams = {'EvnStick_id': win.FormPanel.getForm().findField('EvnStick_id').getValue()};

					if(act.result.data.EvnStick_prid && act.result.data.EvnStick_prid!=0) {
						win.FormPanel.getForm().findField('EvnStickLast_id').setValue(act.result.data.EvnStick_prid);
						win.FormPanel.getForm().findField('EvnStickLast_id').LoadedValue = act.result.data.EvnStick_prid;
					}
				}

				win.Lpu_id = base_form.findField('Lpu_id').getValue();
				if(vm.get('action') != 'view'){
					this.refreshFormPartsAccess();
				}

				this.checkSaveButtonEnabled();

				if ( regionNick != 'kz' ) {
					var field_EvnStick_Ser = base_form.findField('EvnStick_Ser');
					field_EvnStick_Ser.setVisible(!Ext6.isEmpty(field_EvnStick_Ser.getValue()));
				}
				
				/*if (
					getRegionNick() != 'kz'
					&& !Ext6.isEmpty(base_form.findField('StickWorkType_id').getValue())
					&& base_form.findField('StickWorkType_id').getValue().inlist([1,2])
				) {
					base_form.findField('EvnStick_OrgNick').setAllowBlank(false);
				} else {
					base_form.findField('EvnStick_OrgNick').setAllowBlank(true);
				}*/
				
				if( !Ext6.isEmpty(base_form.findField('PersonSnils').getValue()) ) {
					me.Person_Snils = base_form.findField('PersonSnils').getValue();
				}
				_this._checkSnils();
				
				{//ext6
					base_form.findField('EvnStick_IsOriginal').stop++;
					base_form.findField('EvnStick_IsOriginal').setValue(act.result.data['EvnStick_IsOriginal']!=base_form.findField('EvnStick_IsOriginal').uncheckedValue);
					vm.set('EvnStick_IsNotOriginal', base_form.findField('EvnStick_IsOriginal').getValue());
					
					base_form.findField('StickOrder_id').stop++;
					base_form.findField('StickOrder_id').setValue(act.result.data['StickOrder_id']!=base_form.findField('StickOrder_id').uncheckedValue);
				}

				this.onEnableEdit(false);

				if (vm.get('action') == 'edit') {
					// Если есть дубликаты то открываем в режиме просмотра
					if(parseInt(base_form.findField('CountDubles').getValue()) > 0){
						Ext6.Msg.alert(
							langs('Внимание'),
							langs('Данный ЛВН нельзя редактировать, т.к. на него есть дубликат.'),
							function(){}
						);
						vm.set('action', 'view');
					}
					this.onEnableEdit(true);

					setCurrentDateTime({
						dateField: base_form.findField('EvnStick_setDate'),
						loadMask: false,
						setDate: false,
						windowId: win.id,
						callback: function() {
							_this.filterStickCause();
						}
					});
					this.setMaxDateForSetDate();
				}

				if (act.result.data.isTubDiag) {
					vm.set('isTubDiag', true);
				}
				
				if (getRegionNick() != 'kz') {
					this.checkGetEvnStickNumButton();
				}

				if(
					base_form.findField('EvnStickBase_IsFSS').getValue() === true // из ФСС
					&& base_form.findField('StickWorkType_id').getValue() == 2 //2.Работа по совместительству
				) {
					base_form.findField('Org_id').clearValue();
				}
				/*
				me.checkFieldDisabled('StickWorkType_id');//on bind
				if (me.link == true) {
					me.checkFieldDisabled('EvnStick_Num');
					me.checkFieldDisabled('EvnStick_Ser');
					me.checkFieldDisabled('EvnStick_setDate');
					me.checkFieldDisabled('EvnStickFullNameText');
					me.checkFieldDisabled('EvnStickLast_Title');
					me.checkFieldDisabled('StickOrder_id');
				}*/

				win.queryById('EStEF_EvnStickCarePersonPanel').hide();

				var record;
				var i;
				var index;
				
				// обработка поля врач в исходе если врача нет в списке
				var msf_field = base_form.findField('MedStaffFact_id');
				if(msf_field.getValue()) {
					index = msf_field.getStore().findBy(function(rec) { 
						return  rec.get('MedStaffFact_id') == msf_field.getValue();
					});
					if(index == -1) {
						this.getMedPersonalInfo(msf_field.getValue(), 
						function(response) {
							var fio = response[0].Person_Fio;
							var lpu_nick = response[0].Org_Nick;

							msf_field.setRawValue(fio +' (' + lpu_nick + ')'); //TODO: а что будет со значением? ревизия 140144
						});
					}
				}
				
				// Обработка поля "ЛВН-продолжение" (EvnStick_NumNext) в блоке "Исход ЛВН"
				var evn_stick_oid = base_form.findField('EvnStick_oid').getValue();
				if( ! Ext6.isEmpty(evn_stick_oid)){
					this.fetchAndSetEvnStickProd(evn_stick_oid);
				}

				// Обработка полей "EvnStick_stacBegDate" и "EvnStick_stacEndDate" при редактировании или просмотре ЛВН
				this._setProcessValueTo_EvnStick_stacBegDate();
				this._setProcessValueTo_EvnStick_stacEndDate();

				//~ win.queryById('EStEF_btnSetMinDateFromPS').setVisible(true); //кнопок нет в макете
				//~ win.queryById('EStEF_btnSetMaxDateFromPS').setVisible(true);

				if(vm.get('action') == 'edit'){
					if(win.parentClass.inlist(['EvnPL', 'EvnPLStom'])){
						//~ win.queryById('EStEF_btnSetMinDateFromPS').setVisible(false);
						//~ win.queryById('EStEF_btnSetMaxDateFromPS').setVisible(false);
					}
				}

				// Обработка значения поля StickOrder_id
				if (!base_form.findField('StickOrder_id').getValue()) { // не первичный == продолжение лвн
					//~ base_form.findField('EvnStickLast_id').show();
					//~ base_form.findField('EvnStickLast_id').setAllowBlank(false);

				}
				else {
					base_form.findField('EvnStick_prid').setValue(0);
					//ext6: EvnStickLast_Title - заменил поле на EvnStickLast_id
					//~ base_form.findField('EvnStickLast_id').setValue(null);
					//~ base_form.findField('EvnStickLast_id').hide();
					//~ base_form.findField('EvnStickLast_id').setAllowBlank(true);
				}

				// EvnStick_BirthDate
				base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
				base_form.findField('EvnStick_BirthDate').hide();


				// EvnStick_sstBegDate
				base_form.findField('EvnStick_sstBegDate').setAllowBlank(true);
				base_form.findField('EvnStick_sstBegDate').hide();


				// EvnStick_sstEndDate
				base_form.findField('EvnStick_sstEndDate').setAllowBlank(true);
				base_form.findField('EvnStick_sstEndDate').hide();

				// EvnStick_sstNum
				base_form.findField('EvnStick_sstNum').setAllowBlank(true);
				base_form.findField('EvnStick_sstNum').hide();

				base_form.findField('EvnStick_IsRegPregnancy').hide();

				// Org_did
				base_form.findField('Org_did').setAllowBlank(true);
				base_form.findField('Org_did').hide(false);

				// Обработка значения поля StickCause_id
				var stick_cause_id = base_form.findField('StickCause_id').getValue();
				index = base_form.findField('StickCause_id').getStore().findBy(function(rec) {
					return rec.get('StickCause_id') == stick_cause_id;
				});

				record = base_form.findField('StickCause_id').getStore().getAt(index);
				if (record) {
					// StickCause - Причина нетрудоспособности
					switch (record.get('StickCause_SysNick')) {

						// Санаторно-курортное лечение
						case 'kurort':

						// Долечивание в санатории
						case 'dolsan':
							base_form.findField('EvnStick_sstBegDate').setAllowBlank(false);
							base_form.findField('EvnStick_sstBegDate').show();
							base_form.findField('EvnStick_sstEndDate').show();
							base_form.findField('EvnStick_sstNum').show();

							if (regionNick == 'astra') {
								base_form.findField('EvnStick_sstEndDate').show();
								if (record.get('StickCause_SysNick') == 'dolsan') {
									base_form.findField('EvnStick_sstNum').setAllowBlank(false);
									base_form.findField('EvnStick_sstEndDate').setAllowBlank(false);
								}
							}
							else if ( ! Ext6.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
								base_form.findField('EvnStick_sstNum').setAllowBlank(false);
							}

							var value_PridEvnStickWorkRelease_endDate = base_form.findField('PridEvnStickWorkRelease_endDate').getValue();
							var pridStickLeaveTypeCode = this.getPridStickLeaveTypeCode();

							if (
								regionNick == 'ufa' &&
								pridStickLeaveTypeCode == '37' &&
								! Ext6.isEmpty(value_PridEvnStickWorkRelease_endDate)
							) {
								base_form.findField('EvnStick_sstBegDate').disable();
							}

							var field_Org_did = base_form.findField('Org_did');

							field_Org_did.setAllowBlank(regionNick == 'kz');

							base_form.findField('Org_did').show();
							break;

						// Отпуск по беременноcти и родам
						case 'pregn':
							base_form.findField('EvnStick_BirthDate').show();

							if (base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseType_Code') == '020') {
								base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
							} else {
								base_form.findField('EvnStick_BirthDate').setAllowBlank(false);
							}

							// ТРЭБО СДЕЛАТЬ: Учесть пол пациента!
							base_form.findField('EvnStick_IsRegPregnancy').show();
							break;

						// ?????
						case 'adopt':
							base_form.findField('EvnStick_adoptDate').show();
							break;

						// Уход за больным членом семьи
						case 'uhod':

						// Уход за больным членом семьи
						case 'uhodnoreb':

						// Уход за больным ребенком до 7 лет с диагнозом по 255-ФЗ
					 	case 'uhodreb':

						// Ребенок-инвалид
						case 'rebinv':

						// ВИЧ-инфицированный ребенок
						case 'vich':
						// Заболевание ребенка из перечня Минздрава
						case 'zabrebmin':
						// Поствакцинальное осложнение или злокачественное новообразование у ребенка
						case 'postvaccinal':
							win.queryById('EStEF_EvnStickCarePersonPanel').show();
							break;

						// Карантин
						case 'karantin':
							var person_age = swGetPersonAge(me.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnStick_setDate').getValue());

							if (person_age <= 18) {
								win.queryById('EStEF_EvnStickCarePersonPanel').show();
							} else {
								win.queryById('EStEF_EvnStickCarePersonPanel').hide();
							}
							break;
					}
				}

				_this.checkRebUhod();

				// Дубликат == 2 или Оригинал == 1
				var stick_is_original = base_form.findField('EvnStick_IsOriginal').getRawValue();
				if (stick_is_original == 2) {
					var evn_stick_oid = base_form.findField('EvnStick_oid').getValue();
					var evn_stick_dop_pid = base_form.findField('EvnStickDop_pid').getValue();

					this._loadStore_EvnStick_oid(function(){
						index = base_form.findField('EvnStickDop_pid').getStore().findBy(function(rec) {
							return rec.get('EvnStick_id') == evn_stick_oid;
						});

						record = base_form.findField('EvnStickDop_pid').getStore().getAt(index);

						if ( record ) {
							base_form.findField('EvnStickDop_pid').stop++;
							base_form.findField('EvnStickDop_pid').setValue(evn_stick_dop_pid);
							vm.set('EvnStickDop_pid', evn_stick_dop_pid);
						}
					});

					base_form.findField('EvnStick_oid').setAllowBlank(false);
					base_form.findField('EvnStick_oid').show();


					/*if ( vm.get('action') != 'view' ) {
						win.queryById('EStEF_btnSetMinDateFromPS').setVisible(false); //кнопок нет в макете
						win.queryById('EStEF_btnSetMaxDateFromPS').setVisible(false);
					}*/
				}

				// Обработка значения поля StickWorkType_id - Тип занятости
				var evn_stick_dop_pid = base_form.findField('EvnStickDop_pid').getValue();
				var stick_work_type_id = base_form.findField('StickWorkType_id').getValue();
				if (stick_work_type_id == 2){
					// Загружаем список ЛВН, выданных по основному месту работы
					base_form.findField('EvnStickDop_pid').getStore().load({
						params: {
							'EvnStick_mid': base_form.findField('EvnStick_mid').getValue(),
							'EvnStick_id': base_form.findField('EvnStickDop_pid').getValue()
						},
						callback: function() {
							if (base_form.findField('EvnStickDop_pid').getStore().getCount() == 0 || !Ext6.isEmpty(base_form.findField('EvnStick_NumPar').getValue())) {
								base_form.findField('EvnStickDop_pid').getStore().loadData([
									{
										EvnStick_id: -1,
										EvnStick_Num: !Ext6.isEmpty(base_form.findField('EvnStick_NumPar').getValue()) ? base_form.findField('EvnStick_NumPar').getValue() : 'Отсутствует',
										EvnStick_Title: !Ext6.isEmpty(base_form.findField('EvnStick_NumPar').getValue()) ? base_form.findField('EvnStick_NumPar').getValue() : 'Отсутствует'
									}
								]);
								base_form.findField('EvnStickDop_pid').stop++;
								base_form.findField('EvnStickDop_pid').setValue(-1);
								vm.set('EvnStickDop_pid',-1);
							}
							else {
								index = base_form.findField('EvnStickDop_pid').getStore().findBy(function(rec) {
									return rec.get('EvnStick_id') == evn_stick_dop_pid;
								});
								record = base_form.findField('EvnStickDop_pid').getStore().getAt(index);

								if ( record ) {
									base_form.findField('EvnStickDop_pid').stop++;
									base_form.findField('EvnStickDop_pid').setValue(evn_stick_dop_pid);
									vm.set('EvnStickDop_pid', evn_stick_dop_pid);
								}

								if (vm.get('action') != 'view') {
									base_form.findField('EvnStick_BirthDate').disable();
									base_form.findField('EvnStick_irrDate').disable();
									base_form.findField('EvnStick_IsDisability').disable();
									base_form.findField('InvalidGroupType_id').disable();
									base_form.findField('EvnStick_StickDT').disable();
									base_form.findField('EvnStick_IsRegPregnancy').disable();
									base_form.findField('EvnStick_mseDate').disable();
									base_form.findField('EvnStick_mseExamDate').disable();
									base_form.findField('EvnStick_mseRegDate').disable();
									base_form.findField('EvnStick_sstBegDate').disable();
									base_form.findField('EvnStick_sstEndDate').disable();
									base_form.findField('EvnStick_sstNum').disable();
									//~ base_form.findField('EvnStick_stacBegDate').disable();
									//~ base_form.findField('EvnStick_stacEndDate').disable();
									base_form.findField('EvnStickFullNameText').disable();
									base_form.findField('UAddress_AddressText').disable();
									base_form.findField('Lpu_oid').disable();
									base_form.findField('MedStaffFact_id').disable();
									base_form.findField('Org_did').disable();
									base_form.findField('StickCause_did').disable();
									base_form.findField('StickCause_id').disable();
									base_form.findField('StickCauseDopType_id').disable();
									base_form.findField('StickIrregularity_id').disable();
									base_form.findField('StickLeaveType_id').disable();
									base_form.findField('StickOrder_id').disable();
									base_form.findField('EvnStick_oid').disable();

									//win.queryById('EStEF_btnSetMinDateFromPS').setVisible(false); //кнопок нет в макете
									//win.queryById('EStEF_btnSetMaxDateFromPS').setVisible(false); //
								}
							}
							win.queryById('EStEF_EvnStickCarePersonPanel').refreshTitle(true);
							win.queryById('EStEF_EvnStickWorkReleasePanel').refreshTitle(true);
							//~ win.queryById('EStEF_EvnStickCarePersonPanel').fireEvent('expand', win.queryById('EStEF_EvnStickCarePersonPanel'));
							//~ win.queryById('EStEF_EvnStickWorkReleasePanel').fireEvent('expand', win.queryById('EStEF_EvnStickWorkReleasePanel'));
						}.createDelegate(this)
					});
				}
				else {
					win.evnStickType = 1;

					win.queryById('EStEF_EvnStickWorkReleasePanel').fireEvent('expand', win.queryById('EStEF_EvnStickWorkReleasePanel'));
				}

				// Тип занятости
				if ( ! stick_work_type_id || stick_work_type_id == 3) {
					base_form.findField('EvnStickDop_pid').setAllowBlank(true);
					base_form.findField('EvnStickDop_pid').hide();

					win.queryById('EStEF_OrgFieldset').hide();
				}
				else {
					win.queryById('EStEF_OrgFieldset').show();

					if(stick_work_type_id == 2){
						base_form.findField('EvnStickDop_pid').setAllowBlank(false);
						base_form.findField('EvnStickDop_pid').show();
					}
					else {
						base_form.findField('EvnStickDop_pid').setAllowBlank(true);
						base_form.findField('EvnStickDop_pid').hide();
					}
				}

				this.checkOrgFieldDisabled();

				// Обработка значения поля StickLeaveType_id (Исход ЛВН)
				vm.set('fromStickLeave','save');
				var stick_leave_type_id = base_form.findField('StickLeaveType_id').getValue();

				index = base_form.findField('StickLeaveType_id').getStore().findBy(function(rec) {
					return rec.get('StickLeaveType_id') == stick_leave_type_id;
				});
				record = base_form.findField('StickLeaveType_id').getStore().getAt(index);

				if ( ! record || ! record.get('StickLeaveType_id') ) {
					base_form.findField('EvnStick_disDate').hide();
					base_form.findField('EvnStick_disDate').setAllowBlank(true);
					base_form.findField('EvnStick_disDate').setRawValue('');
					base_form.findField('Lpu_oid').reset();
					base_form.findField('Lpu_oid').hide();
					base_form.findField('EvnStick_NumNext').hide();
					base_form.findField('MedStaffFact_id').reset();
					base_form.findField('MedStaffFact_id').setAllowBlank(true);
					base_form.findField('MedStaffFact_id').hide();
					win.queryById('swSignStickLeave').hide();
				}
				else {
					base_form.findField('EvnStick_disDate').show();
					base_form.findField('EvnStick_disDate').setAllowBlank(false);
					base_form.findField('MedStaffFact_id').show();
					base_form.findField('MedStaffFact_id').setAllowBlank(false);
					win.queryById('swSignStickLeave').show();

					if ( record.get('StickLeaveType_Code').inlist([ '31', '32', '33', '37' ]) ) {
						base_form.findField('Lpu_oid').show();
					}

					if ( record.get('StickLeaveType_Code').inlist([ '31', '37' ]) ) {
						base_form.findField('EvnStick_NumNext').show();
					}
				}
				// Org_id
				var org_id = base_form.findField('Org_id').getValue();
				if (org_id != null && Number(org_id) > 0){
					base_form.findField('Org_id').getStore().load({
						callback: function(records, options, success) {
							if(success){
								base_form.findField('Org_id').setValue(org_id);//TAG: ?change
							}
						},
						params: {
							Org_id: org_id,
							OrgType: 'org'
						}
					});
				}

				if (regionNick != 'kz' && ! Ext6.isEmpty(base_form.findField('StickCause_did').getValue())){
					base_form.findField('EvnStick_StickDT').setAllowBlank(false);
					base_form.findField('EvnStick_StickDT').show();
				}

				//точно не нужен setValue ?
				base_form.findField('StickCauseDopType_id').fireEvent('change', base_form.findField('StickCauseDopType_id'), base_form.findField('StickCauseDopType_id').getValue());
				base_form.findField('StickCause_did').fireEvent('change', base_form.findField('StickCause_did'), base_form.findField('StickCause_did').getValue());

				// Org_did
				var org_did = base_form.findField('Org_did').getValue();
				if (org_did != null && Number(org_did) > 0) {
					base_form.findField('Org_did').getStore().load({
						callback: function(records, options, success) {
							if (success) {
								base_form.findField('Org_did').setValue(org_did);//TAG: ?change
							}
						},
						params: {
							Org_id: org_did,
							OrgType: 'org'
						}
					});
				}

				if (win.evnStickType == 1) {
					win.queryById('EStEF_EvnStickCarePersonPanel').fireEvent('expand', win.queryById('EStEF_EvnStickCarePersonPanel'));
				}

				if (vm.get('action') == 'edit'){
					if(win.evnStickType == 2){
						base_form.findField('EvnStick_Ser').focus(true, 250);
					}
					else if(win.link == true){
						base_form.findField('Org_id').focus(true, 250);
					}
					else {
						base_form.findField('EvnStickFullNameText').focus(true, 250);
					}
				}

				// StickLeaveType_id
				if (base_form.findField('StickLeaveType_id').getValue()) {
					_this.getEvnStickSignStatus({object: 'leave'});
				} else {
					_this.setStatus(0, 'Leave');//ext6
				}

				// Обработка значения поля StickIrregularity_id
				//~ this.fireEventChange(base_form.findField('StickIrregularity_id'));
				base_form.findField('StickIrregularity_id').fireEvent('change', base_form.findField('StickIrregularity_id'), base_form.findField('StickIrregularity_id').getValue());

				if (base_form.findField('StickIrregularity_id').getValue()) {
					_this.getEvnStickSignStatus({object: 'irr'});
				}

				// загружаем список периодов освобождений если он не был загружен ранее
				if(this._isLoaded_WorkRelease() == false) {
					this._defaultLoad_WorkRelease();
				}

				if (isMseDepers()) {
					base_form.findField('EvnStickFullNameText').setValue('* * *');//TAG: ?change
					win.queryById('buttonMenuPrint').hide();
				}
				
				win.unmask();
				
				//_this.suspendChange(0);
				
			}.createDelegate(this)
		});
	},
	
	deleteEvnStick: function() {///Удаление ЛВН
		eval(this.ini);
		var evnstick_id = base_form.findField('EvnStick_id').getValue();
		var evnstick_pid = base_form.findField('EvnStick_pid').getValue();
		var delAccessType = win.params.delAccessType;
		
		var Evn_pid = win.params.Evn_pid;
		var EvnStick_closed = base_form.findField('StickLeaveType_id').getValue() > 0;
		
		if ( !evnstick_pid || delAccessType == 'view' )
		{
			return false;
		}

		var error, question, url,
			params = new Object();
		
		if (EvnStick_closed == 1) {
			error = langs('При удалении ЛВН возникли ошибки');
			question = langs('Вы уверены, что хотите удалить закрытый ЛВН?');
		} else {
			error = langs('При удалении ЛВН возникли ошибки');
			question = langs('Вы действительно хотите удалить лист временной нетрудоспособности?');
		}
		url = '/?c=Stick&m=deleteEvnStick';
		params['EvnStick_id'] = evnstick_id;
		params['EvnStick_mid'] = evnstick_pid;

		Ext6.Msg.show({
			buttons: Ext6.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					_this.doDeleteEvnStick({
						error: error,
						params: params,
						evnstick_pid: evnstick_pid,
						evnstick_id: evnstick_id,
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext6.MessageBox.QUESTION,
			msg: question,
			title: langs('Вопрос')
		});
	},
	doDeleteEvnStick: function(options) {
		eval(this.ini);
		
		win.mask(langs("Удаление записи..."));

		var alert = sw.Promed.EvnStick.getDeleteAlertCodes({
			callback: function(options) {
				_this.doDeleteEvnStick(options);
			},
			options: options,
			Ext: Ext6
		});

		Ext6.Ajax.request({
			failure: function(response, options) {
				win.unmask();
				Ext6.Msg.alert(langs('Ошибка'), options.error);
			},
			params: options.params,
			success: function(response, opts) {
				win.unmask();

				var response_obj = Ext6.util.JSON.decode(response.responseText);

				if ( response_obj.success == false ) {
					if (response_obj.Alert_Msg) {
						if (response_obj.Alert_Code == 705) {
							getWnd('swStickCauseDelSelectWindow').show({
								countNotPaid: response_obj.countNotPaid,
								existDuplicate: response_obj.existDuplicate,
								callback: function(StickCauseDel_id) {
									if (StickCauseDel_id) {
										options.params.StickCauseDel_id = StickCauseDel_id;
										_this.doDeleteEvnStick(options);
									}
								}.createDelegate(this)
							});
						} else {
							var a_params = alert[response_obj.Alert_Code];
							Ext6.Msg.show({
								buttons: a_params.buttons,
								fn: function(buttonId) {
									a_params.fn(buttonId, win);
								}.createDelegate(this),
								msg: response_obj.Alert_Msg,
								icon: Ext6.MessageBox.QUESTION,
								title: 'Вопрос'
							});
						}
					} else {
						Ext6.Msg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : options.error);
					}
				}
				else {
					if (response_obj.IsDelQueue) {
						Ext6.Msg.alert('Внимание', 'ЛВН добавлен в очередь на удаление');
					}
					win.hide();
				}
			},
			url: options.url
		});
	},
	
	doCopy: function() {///
		eval(this.ini);
		var my_params = win.params;
		
		my_params.action = 'add';
		my_params.formParams.EvnStick_id = 0;
		my_params.formParams.EvnStickCopy_id = base_form.findField('EvnStick_id').getValue();
		
		win.show(my_params);

	},
	doSave: function(options) {
		eval(this.ini);
		
		// options @Object
		// options.ignoreSetDateError @Boolean Игнорировать проверку даты выдачи ЛВН и даты начала первого освобождения от работы
		// options.ignoreSetDateDieError @Boolean Игнорировать проверку (даты ЛВН = даты КВС и исхода, если исход = умер)
		// options.ignoreSetDateDopError @Boolean Игнорировать сравнение даты выдачи основного и дополнительного ЛВН
		// options.ignoreEvnStickContQuestion @Boolean Игнорировать вопрос на выписку продолжения ЛВН
		// options.ignorePregnancyReleasePeriodQuestion @Boolean Игнорировать вопрос о сроке освобождения от работы (для случая, когда ЛВН выдан по беременности и родам)
		// options.ignoreLeaveTypeErrors @Boolean Игнорировать ошибки, связанные с выбранным значением поля "Исход ЛВН"
		// options.print @Boolean Вызывать печать ЛВН, если true

		if ( win.formStatus == 'save' || vm.get('action') == 'view' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		win.formStatus = 'save';

		var advanceParams = win.advanceParams;
		var base_form = win.FormPanel.getForm();
		var form = win.FormPanel;
		var evn_stick_care_person_grid = win.queryById('EStEF_EvnStickCarePersonGrid');
		var evn_stick_work_release_grid = win.queryById('EStEF_EvnStickWorkReleaseGrid');

		if ( !base_form.isValid() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(true);
				}.createDelegate(win),
				icon: Ext6.Msg.WARNING,
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные.\nНекорректно заполненные поля выделены особо.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!this.checkSaveButtonEnabled()) {
			// Сохранение не возможно
			Ext6.Msg.alert(langs('Ошибка'), 'Должно быть заполнено хотя бы одно освобождение от работы или исход ЛВН');
			win.formStatus = 'edit';
			return false;
		}

		if (
			! Ext6.isEmpty(base_form.findField('EvnStickBase_consentDT').getValue())
			&& ! Ext6.isEmpty(base_form.findField('EvnStick_setDate').getValue())
			&& base_form.findField('EvnStick_setDate').getValue() >= new Date('2019-2-22') // отключаем контроль если выдан раньше 22.02.2019
			&& base_form.findField('EvnStick_setDate').getValue() < base_form.findField('EvnStickBase_consentDT').getValue()
			&& base_form.findField('EvnStick_IsOriginal').getRawValue() != 2
			&& !this.checkIsLvnFromFSS()
		) {
			Ext6.Msg.alert(langs('Ошибка'), 'Согласие не может быть получено после выдачи ЛВН.');
			win.formStatus = 'edit';
			return false;
		}
		
		var isVK = (win.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().findBy(function(rec){
			return rec.get('EvnStickWorkRelease_IsPredVK');
		})) != -1;
		
		if(
			!Ext6.isEmpty(base_form.findField('EvnStick_disDate').getValue())
			&& !Ext6.isEmpty(base_form.findField('EvnStick_setDate').getValue())
			&& !Ext6.isEmpty(base_form.findField('EvnStickBase_consentDT').getValue())
			&& !isVK 
			&& base_form.findField('EvnStick_IsOriginal').getRawValue() != 2
			&& base_form.findField('EvnStick_disDate').getValue() < base_form.findField('EvnStick_setDate').getValue()
		) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Выдача ЛВН за прошедшие дни возможна только по решению врачебной комиссии.'));
			win.formStatus = 'edit';
			return false;
		}

		if (
			! base_form.findField('EvnStick_sstBegDate').hidden
			&& ! base_form.findField('EvnStick_sstEndDate').hidden
			&& ! Ext6.isEmpty(base_form.findField('EvnStick_sstBegDate').getValue())
			&& ! Ext6.isEmpty(base_form.findField('EvnStick_sstEndDate').getValue())
			&& base_form.findField('EvnStick_sstEndDate').getValue() < base_form.findField('EvnStick_sstBegDate').getValue()
			&& getRegionNick() != 'kz'
		) {
			Ext6.Msg.alert(langs('Ошибка'), 'Дата начала СКЛ должна быть не больше, чем дата окончания');
			win.formStatus = 'edit';
			return false;
		}

		if (getRegionNick() == 'astra' && !options.ignoreSnilsCheck && !Ext6.isEmpty(base_form.findField('Person_id').getValue()) && !Ext6.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
			// проверяем СНИЛС у человека которому выдан ЛВН
			Ext6.Ajax.request({
				url: '/?c=Person&m=getPersonSnils',
				params: {
					Person_id: base_form.findField('Person_id').getValue()
				},
				callback: function(opt, success, response) {
					win.formStatus = 'edit';
					if (success && response.responseText.length > 0) {
						var result = Ext6.util.JSON.decode(response.responseText);
						if (Ext6.isEmpty(result.Person_Snils)) {
							var Person_Fio = base_form.findField('EvnStickFullNameText').getValue();
							Ext6.Msg.show({
								buttons: {
									ok: {text: langs('Редактировать атрибуты пациента')},
									cancel: true
								},
								fn: function (buttonId) {
									if (buttonId == 'ok') {
										getWnd('swPersonEditWindow').show({
											action: 'edit',
											Person_id: base_form.findField('Person_id').getValue(),
											callback: function (data) {

											}
										});
									}
								},
								icon: Ext6.Msg.QUESTION,
								msg: langs('У получателя ЛВН ') + Person_Fio + langs(' не указан СНИЛС, в связи с этим закрытие ЛВН невозможно. Редактировать персональные данные пациента?'),
								title: langs('Внимание')
							});
							return false;
						}

						options.ignoreSnilsCheck = true;
						_this.doSave(options);
					}
				}
			});

			return false;
		}

		if (Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue()) && ! win.parentClass.inlist([ 'EvnPL', 'EvnPLStom' ])) {
			if ( ! options.ignoreControlEvnSectionDates ) {

				if (
					(getRegionNick() != 'kz' && win.advanceParams.LpuUnitType_SysNick == 'stac') ||
					(getRegionNick() == 'kz' && win.advanceParams.LpuUnitType_SysNick && win.advanceParams.LpuUnitType_SysNick.inlist(['stac', 'dstac', 'hstac', 'pstac']))
				) {
					// контроль на совпадение дат лечения в стационаре с датами движений (refs #7872)

					if (win.advanceParams.stacBegDate == null) {
						win.advanceParams.stacBegDate = '';
					}

					if (
						(win.advanceParams.stacBegDate - base_form.findField('EvnStick_stacBegDate').getValue() != 0) ||
						(
							! Ext6.isEmpty(win.advanceParams.stacEndDate) && (win.advanceParams.stacEndDate - base_form.findField('EvnStick_stacEndDate').getValue() != 0)
						) ||
						(Ext6.isEmpty(win.advanceParams.stacEndDate) && ! Ext6.isEmpty(base_form.findField('EvnStick_stacEndDate').getValue()) )
					) {
						Ext6.Msg.show({
							buttons: Ext6.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								win.formStatus = 'edit';

								if ( 'yes' == buttonId ) {
									options.ignoreControlEvnSectionDates = true;
									_this.doSave(options);
								}
								else {
									win._focusButtonSave();
								}
							}.createDelegate(win),
							icon: Ext6.MessageBox.QUESTION,
							msg: langs('Период лечения в стационаре в ЛВН не совпадает с данными движений связанных КВС, Продолжить?'),
							title: langs('Вопрос')
						});
						return false;
					}

				}
			}
		}

		// Получаем даты освобождения от работы
		var evn_stick_work_release_beg_date;
		var evn_stick_work_release_end_date;
		var index;
		var days_count = 0;
		var months_count = 0;
		var IsDraft = false;
		var IsSpecLpu = false;
		var hasMedPersonal2 = false;
		var Org_Nick = '', LpuUnitType_SysNick = '';
		var stac_end_date = base_form.findField('EvnStick_stacEndDate').getValue();
		var days_count_after_stac = 0;
		var person_age = sw4.GetPersonAge(win.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnStick_setDate').getValue());

		evn_stick_work_release_grid.getStore().each(function(rec) {
			if ( typeof evn_stick_work_release_beg_date != 'object' || rec.get('EvnStickWorkRelease_begDate') < evn_stick_work_release_beg_date ) {
				evn_stick_work_release_beg_date = rec.get('EvnStickWorkRelease_begDate');
				if(typeof evn_stick_work_release_beg_date == 'string') evn_stick_work_release_beg_date = Date.parseDate(evn_stick_work_release_beg_date,'d.m.Y');
				LpuUnitType_SysNick = rec.get('LpuUnitType_SysNick');
				Org_Nick = rec.get('Org_Name');
			}

			if ( typeof evn_stick_work_release_end_date != 'object' || rec.get('EvnStickWorkRelease_endDate') > evn_stick_work_release_end_date ) {
				evn_stick_work_release_end_date = rec.get('EvnStickWorkRelease_endDate');
				if(typeof evn_stick_work_release_end_date == 'string') evn_stick_work_release_end_date = Date.parseDate(evn_stick_work_release_end_date,'d.m.Y');
			}

			if (rec.get('EvnStickWorkRelease_IsDraft')) {
				IsDraft = true;
			}
			if (rec.get('EvnStickWorkRelease_IsSpecLpu')) {
				IsSpecLpu = true;
			}
			if (!Ext6.isEmpty(rec.get('MedStaffFact2_id'))) {
				hasMedPersonal2 = true;
			}
		});

		var EvnStatus_Name = '';
		if (IsDraft) {
			EvnStatus_Name = langs('Черновик');
		}

		if (!Ext6.isEmpty(evn_stick_work_release_beg_date) && !Ext6.isEmpty(evn_stick_work_release_end_date)) {
			days_count = daysBetween(evn_stick_work_release_beg_date, evn_stick_work_release_end_date);

			months_count = evn_stick_work_release_beg_date.getMonthsBetween(evn_stick_work_release_end_date);
		}
		if (!Ext6.isEmpty(stac_end_date) && !Ext6.isEmpty(evn_stick_work_release_end_date)) {
			//Дней между окончанием лечения в стационаре и окончанием освобождения
			days_count_after_stac = daysBetween(stac_end_date, evn_stick_work_release_end_date);
		}


		//[gabdushev] #6146: Запретить выбирать значения с кодом, начинающимся на букву. (для ЛВН c датой выдачи начиная с 1 июля 2011г.)
		var permitBefore = '01.07.2011'; //Дата, до которой разрешается использование причин нетрудоспособности, код которых начинается на букву
		var aStickBeginDate = Date.parseDate(base_form.findField('EvnStick_setDate').value, 'd.m.Y');
		var letterBeginCauseCodePermitted = (aStickBeginDate < Date.parseDate(permitBefore,'d.m.Y'));
		var ok = true;
		if (!letterBeginCauseCodePermitted){
			function checkLetterBegin(field_id, field_code, field_name){
				var result = true;
				var causeField = base_form.findField(field_id);
				var aCauseId = causeField.getValue();
				if (!Ext6.isEmpty(aCauseId)) {
					var causeCode = causeField.getFieldValue(field_code);
					if (!Ext6.isEmpty(causeCode)) {
						var firstIsALetter = ((causeCode[0]>'A' && causeCode[0]<'Z') || (causeCode[0]>'a' && causeCode[0]<'z'));
						if (firstIsALetter) {
							var causeName = causeCode + '. ' + causeField.getFieldValue(field_name);
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								icon: Ext6.Msg.WARNING,
								fn: function() {
									causeField.focus(true);
								}.createDelegate(win),
								msg: 'Согласно приказу № 347н от 26.04.2011 выбранное значение справочника <br /> "' + causeName + '" может быть использовано только для листков временной нетрудоспособности, выданных ранее ' + permitBefore,
								title: ERR_INVFIELDS_TIT
							});
							result = false;
						}
					}
				}
				return result;
			}
			ok = (ok && checkLetterBegin('StickCause_id','StickCause_Code','StickCause_Name'));
			ok = (ok && checkLetterBegin('StickCause_did','StickCause_Code','StickCause_Name'));
			ok = (ok && checkLetterBegin('StickLeaveType_id','StickLeaveType_Code','StickLeaveType_Name'));
			ok = (ok && checkLetterBegin('StickIrregularity_id','StickIrregularity_Code','StickIrregularity_Name'));
		}
		if (!ok) {
			win.formStatus = 'edit';
			return false;
		}
		//[/gabdushev] #6146

		// в поле порядок выдачи «продолжение ЛВН», исходом первичного ЛВН «37. Долечивание» и при указанной причине нетрудоспособности «08. Долечивание в санатории» снята обязательность заполнения раздела «Освобождение от работы» (refs #72473)
		var workReleaseCanBeEmpty = false;
		if (
			base_form.findField('StickOrder_id').getValue() == 2
			&& this.getPridStickLeaveTypeCode() == '37'
			&& base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'dolsan'
		) {
			workReleaseCanBeEmpty = true;
		}
		if (!Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			workReleaseCanBeEmpty = true;
		}

		// Проверка на заполнение хотя бы одной записи в таблице "Освобождение от работы"
		if ( !workReleaseCanBeEmpty && !_this.hasWorkRelease() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					win.queryById('EStEF_EvnStickWorkReleasePanel').refreshTitle(true);//вместо:
					//~ if ( win.queryById('EStEF_EvnStickWorkReleasePanel').collapsed ) {
						//~ win.queryById('EStEF_EvnStickWorkReleasePanel').expand();
					//~ }
				}.createDelegate(win),
				icon: Ext6.Msg.WARNING,
				msg: langs('Должно быть заполнено хотя бы одно освобождение от работы'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			// 1. Проверка на наличие периодов нетрудоспособности или исхода ЛВН для ЛВН с флагом «ЛВН из ФСС»:
			// если ЛВН имеет флаг «ЛВН из ФСС», данные ЛВН должны содержать хотя бы один период нетрудоспособности или исход ЛВН.
			if (!_this.hasWorkRelease() && Ext6.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						win.formStatus = 'edit';
						win.queryById('EStEF_EvnStickWorkReleasePanel').refreshTitle(true);//вместо:
						//~ if ( win.queryById('EStEF_EvnStickWorkReleasePanel').collapsed ) {
							//~ win.queryById('EStEF_EvnStickWorkReleasePanel').expand();
						//~ }
					}.createDelegate(win),
					icon: Ext6.Msg.WARNING,
					msg: 'Должно быть заполнено хотя бы одно освобождение от работы или исход ЛВН',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		if ( win.parentClass.inlist(['EvnPL','EvnPLStom']) ) {
			// [2015-01-30] Поменял определение выписки задним числом
			// @task https://redmine.swan.perm.ru/issues/54724
			index = win.gridEvnStickWorkRelease.getStore().findBy(function(rec){
				return (
					rec.get('EvnStickWorkRelease_IsDraft') != 1
					&& base_form.findField('EvnStick_setDate').getValue() > rec.get('EvnStickWorkRelease_begDate')
					&& !rec.get('EvnStickWorkRelease_IsPredVK')
					&& rec.get('Org_id') == getGlobalOptions().org_id
					&& rec.get('LpuUnitType_SysNick') == 'polka'
				);
			});

			if ( index >= 0 ) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						win.formStatus = 'edit';
						win.queryById('EStEF_EvnStickWorkReleasePanel').refreshTitle(true);//вместо:
						//~ if ( win.queryById('EStEF_EvnStickWorkReleasePanel').collapsed ) {
							//~ win.queryById('EStEF_EvnStickWorkReleasePanel').expand();
						//~ }
					}.createDelegate(win),
					icon: Ext6.Msg.WARNING,
					msg: 'Вы выписываете листок нетрудоспособности за прошедшие дни. Выдача ЛВН должна осуществляться по решению врачебной комиссии. Необходимо указать членов ВК.',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		if ( !Ext6.isEmpty(base_form.findField('StickCause_id').getValue()) && base_form.findField('StickCause_id').getValue() == base_form.findField('StickCause_did').getValue() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					base_form.findField('StickCause_did').focus(true);
				}.createDelegate(win),
				icon: Ext6.Msg.WARNING,
				msg: langs('Поля "Причина нетрудоспособности" и "Код изм. нетрудоспособности" не могут иметь одинаковые значения'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var stick_cause_sysnick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
		
		if (getRegionNick() == 'kz') {
			var disallowSave;
			win.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().each(function(rec) {
				var begReleaseDate = rec.data.EvnStickWorkRelease_begDate;
				var endReleaseDate = rec.data.EvnStickWorkRelease_endDate;//TODO: check format (date)

				if(rec.data.LpuUnitType_SysNick == 'stac') {
					if(
						stac_end_date
						&& daysBetween(stac_end_date, endReleaseDate) > 4 //TODO: check
						&& stick_cause_sysnick && stick_cause_sysnick.inlist(['desease','trauma','uhod']) 	
					) {
						Ext6.Msg.show({
							buttons: Ext6.Msg.OK,
							fn: function() {
								win.formStatus = 'edit';
							}.createDelegate(win),
							icon: Ext6.Msg.WARNING,
							msg: 'Дата окончания освобождения от работы не может быть позднее даты окончания лечения в стационаре, более чем на 4 дня',
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}
				
					if (stick_cause_sysnick && stick_cause_sysnick == 'protez' && daysBetween(begReleaseDate, endReleaseDate) > 30) {
						Ext6.Msg.show({
							buttons: Ext6.Msg.OK,
							fn: function() {
								win.formStatus = 'edit';
							}.createDelegate(win),
							icon: Ext6.Msg.WARNING,
							msg: 'По выбранной причине нетрудоспособности период освобождения от работы не может превышать 30 дней',
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}
				} else if(win.isTubDiag) {
					if (IsSpecLpu && begReleaseDate.getMonthsBetween(endReleaseDate) > 15) {
						Ext6.Msg.show({
							buttons: Ext6.Msg.OK,
							fn: function() {
								win.formStatus = 'edit';
							}.createDelegate(win),
							icon: Ext6.Msg.WARNING,
							msg: 'По причине туберкулезного заболевания освобождения от работы не может превышать 15 месяцев',
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}
					if (!IsSpecLpu && daysBetween(begReleaseDate, endReleaseDate) > 3) {
						Ext6.Msg.show({
							buttons: Ext6.Msg.OK,
							fn: function() {
								win.formStatus = 'edit';
							}.createDelegate(win),
							icon: Ext6.Msg.WARNING,
							msg: 'По причине туберкулезного заболевания освобождения от работы, выдаваемое МО общей практики, не может превышать 3-х дней',
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}
				} else {
					if (
							stick_cause_sysnick && stick_cause_sysnick.inlist(['desease','trauma']) 
							&& daysBetween(begReleaseDate, endReleaseDate) > 6 
							&& hasMedPersonal2 == false
						) {
						Ext6.Msg.show({
							buttons: Ext6.Msg.OK,
							fn: function() {
								win.formStatus = 'edit';
								//base_form.findField('StickCause_id').focus(true);
							}.createDelegate(win),
							icon: Ext6.Msg.WARNING,
							msg: langs('При выбранной причине нетрудоспособности, продолжительность освобождения от работы не должна превышать 6 дней'),
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}
					if (stick_cause_sysnick && stick_cause_sysnick == 'uhod'
							&& daysBetween(begReleaseDate, endReleaseDate) > 10) {
						Ext6.Msg.show({
							buttons: Ext6.Msg.OK,
							fn: function() {
								win.formStatus = 'edit';
							}.createDelegate(win),
							icon: Ext6.Msg.WARNING,
							msg: 'По выбранной причине нетрудоспособности период освобождения от работы не может превышать 10 дней',
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}
					if ( stick_cause_sysnick && stick_cause_sysnick == 'adopt' ) {
						var adopt_date = base_form.findField('EvnStick_adoptDate').getValue();

						if (!options.ignoreAdoptReleaseBegDate && Ext6.util.Format.date(begReleaseDate, 'd.m.Y') != Ext6.util.Format.date(adopt_date, 'd.m.Y')) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									win.formStatus = 'edit';

									if ( 'yes' == buttonId ) {
										options.ignoreAdoptReleaseBegDate = true;
										_this.doSave(options);
									}
									else {
										me._focusButtonSave();
									}
								}.createDelegate(win),
								icon: Ext6.MessageBox.QUESTION,
								msg: langs('Лист временной нетрудоспособности должен выдаваться со дня усыновления или удочерения. Продолжить сохранение?'),
								title: langs('Вопрос')
							});
							disallowSave = true;
							return false;
						}

						var birth_date = win.PersonInfo.getFieldValue('Person_Birthday');
						if (!options.ignoreAdoptReleaseEndDate && daysBetween(birth_date, endReleaseDate) != 56) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									win.formStatus = 'edit';

									if ( 'yes' == buttonId ) {
										options.ignoreAdoptReleaseEndDate = true;
										_this.doSave(options);
									}
									else {
										me._focusButtonSave();
									}
								}.createDelegate(win),
								icon: Ext6.MessageBox.QUESTION,
								msg: langs('Лист временной нетрудоспособности по усыновлению или удочерению должен выдаваться до истечения 56 дней со дня рождения ребенка. Продолжить сохранение?'),
								title: langs('Вопрос')
							});
							disallowSave = true;
							return false;
						}
					}
				}
			}.createDelegate(win));
			
			if(disallowSave) {
				win.formStatus = 'edit';
				return false;
			}
			
			if(base_form.findField('EvnStick_mid').getValue() != base_form.findField('EvnStick_pid').getValue() && !win.parentClass.inlist(['EvnPL','EvnPLStom']) )
				options.ignorePregnancyReleasePeriodQuestion = true;
				
			if( win.parentClass == 'EvnPS' )
				options.ignorePregnancyReleasePeriodQuestion = true;
		}

		if ( !options.ignorePregnancyReleasePeriodQuestion && stick_cause_sysnick && stick_cause_sysnick == 'pregn' ) {
			// При заполнении больничного листа у беременных по отпуску по беременности и родам добавить контроль на количество дней по ЛВН
			// не равное 140 дням. Добавить предупреждение "Лист временной нетрудоспособности по беременности и родам имеет продолжительность
			// не равную 140 дням. Продолжить сохранение?"
			var pregn_days = 140;
			if (getRegionNick() == 'kz') {
				pregn_days = 126;
			}
			if ( (typeof evn_stick_work_release_beg_date == 'object') && (typeof evn_stick_work_release_end_date == 'object') && (Ext6.util.Format.date(evn_stick_work_release_beg_date.add(Date.DAY, pregn_days - 1), 'd.m.Y') != Ext6.util.Format.date(evn_stick_work_release_end_date, 'd.m.Y')) ) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						win.formStatus = 'edit';

						if ( 'yes' == buttonId ) {
							options.ignorePregnancyReleasePeriodQuestion = true;
							_this.doSave(options);
						}
						else {
							me._focusButtonSave();
						}
					}.createDelegate(win),
					icon: Ext6.MessageBox.QUESTION,
					msg: langs('Лист временной нетрудоспособности по беременности и родам имеет продолжительность, не равную ')+pregn_days+langs('  дням. Продолжить сохранение?'),
					title: langs('Вопрос')
				});
				return false;
			}
		}
		else if (stick_cause_sysnick && stick_cause_sysnick.inlist([/*'karantin', */'uhodnoreb', 'uhodreb', 'uhod', 'rebinv', 'vich', 'zabrebmin'])
			|| (stick_cause_sysnick && stick_cause_sysnick.inlist(['karantin']) && person_age <= 18)
			|| (stick_cause_sysnick && stick_cause_sysnick == 'postvaccinal' && !getRegionNick().inlist(['kz']))
		) {
			// Проверка правильности заполнения таблицы "Список пациентов, нуждающихся в уходе"

			// Должна быть хотя бы одна запись
			if ( evn_stick_care_person_grid.getStore().getCount() == 0 ) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						win.formStatus = 'edit';
						win.queryById('EStEF_EvnStickCarePersonPanel').refreshTitle();//вместо:
						//~ if ( win.queryById('EStEF_EvnStickCarePersonPanel').collapsed ) {
							//~ win.queryById('EStEF_EvnStickCarePersonPanel').expand();
						//~ }
					}.createDelegate(win),
					icon: Ext6.Msg.WARNING,
					msg: langs('При выбранной причине выдачи ЛВН, должен быть указан хотя бы один пациент, нуждающийся в уходе'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			// Должны быть заполнены обязательные поля Person_id, RelatedLinkType_id
			// #39804 Для казахстана RelatedLinkType_id не обязательно
			index = evn_stick_care_person_grid.getStore().findBy(function(rec) {
				return !rec.get('Person_id') || (!rec.get('RelatedLinkType_id') && getRegionNick()!='kz');
			});

			if ( index >= 0 ) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						win.formStatus = 'edit';
						win.queryById('EStEF_EvnStickCarePersonPanel').refreshTitle();//вместо:
						//~ if ( win.queryById('EStEF_EvnStickCarePersonPanel').collapsed ) {
							//~ win.queryById('EStEF_EvnStickCarePersonPanel').expand();
						//~ }
					}.createDelegate(win),
					icon: Ext6.Msg.WARNING,
					msg: langs('Не заполнены обязательные поля в информации о пациенте, нуждающемся в уходе'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		// Установлена инвалидность
		// [2012-12-21] В справочнике отсутствует причина с кодом 32!!!
		else if ( stick_cause_sysnick && stick_cause_sysnick == '32' ) {
			var mseExamDate = base_form.findField('EvnStick_mseExamDate').getValue();

			if ( !mseExamDate ) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						win.formStatus = 'edit';

						if ( win.queryById('EStEF_MSEPanel').collapsed ) {
							win.queryById('EStEF_MSEPanel').expand();
						}

						base_form.findField('EvnStick_mseExamDate').focus(true);
					}.createDelegate(win),
					icon: Ext6.Msg.WARNING,
					msg: langs('При указанном исходе ЛВН "Установлена инвалидность" поле "Дата освидетельствования в бюро МСЭ" обязательно для заполнения'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( mseExamDate.add(Date.DAY, -1) != evn_stick_work_release_end_date ) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						win.formStatus = 'edit';
						win.queryById('EStEF_EvnStickWorkReleasePanel').refreshTitle(true);//вместо:
						//~ if ( win.queryById('EStEF_EvnStickWorkReleasePanel').collapsed ) {
							//~ win.queryById('EStEF_EvnStickWorkReleasePanel').expand();
						//~ }
					}.createDelegate(win),
					icon: Ext6.Msg.WARNING,
					msg: langs('Дата последнего освобождения от работы должна быть на 1 день меньше, чем значение поля "Дата освидетельствования в бюро МСЭ"'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		if ( !options.ignoreSetDateError ) {
			if (
				typeof evn_stick_work_release_beg_date == 'object' && evn_stick_work_release_beg_date < base_form.findField('EvnStick_setDate').getValue()
				&& !Ext6.isEmpty(LpuUnitType_SysNick) && !LpuUnitType_SysNick.inlist([ 'stac', 'dstac', 'hstac', 'pstac' ])
			) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						win.formStatus = 'edit';

						if ( 'yes' == buttonId ) {
							options.ignoreSetDateError = true;
							_this.doSave(options);
						}
						else {
							me._focusButtonSave();
						}
					}.createDelegate(win),
					icon: Ext6.MessageBox.QUESTION,
					msg: langs('Вы выписываете листок нетрудоспособности задним числом. Продолжить?'),
					title: langs('Вопрос')
				});
				return false;
			}
		}

		if ( win.evnStickType == 2 && !options.ignoreSetDateDopError ) {
			if ( base_form.findField('EvnStickDop_pid').getFieldValue('EvnStick_setDate') != Ext6.util.Format.date(base_form.findField('EvnStick_setDate').getValue(), 'd.m.Y') ) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						win.formStatus = 'edit';

						if ( 'yes' == buttonId ) {
							options.ignoreSetDateDopError = true;
							_this.doSave(options);
						}
						else {
							me._focusButtonSave();
						}
					}.createDelegate(win),
					icon: Ext6.MessageBox.QUESTION,
					msg: langs('Дата выдачи ЛВН не совпадает с датой выдачи основного ЛВН. Продолжить?'),
					title: langs('Вопрос')
				});
				return false;
			}
		}

		var params = new Object();

		params.evnStickType = win.evnStickType;

		params.EvnStick_oid = base_form.findField('EvnStick_oid').getValue();

		params.EvnStick_IsOriginal = base_form.findField('EvnStick_IsOriginal').getRawValue();
		params.Signatures_id = vm.get('Signatures_id');
		params.Signatures_iid = vm.get('Signatures_iid');

		if ( base_form.findField('EvnStickBase_consentDT').disabled ) {
			params.EvnStickBase_consentDT = Ext6.util.Format.date(base_form.findField('EvnStickBase_consentDT').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStickBase_IsFSS').disabled ) {
			params.EvnStickBase_IsFSS = base_form.findField('EvnStickBase_IsFSS').getValue();
		}

		if ( base_form.findField('EvnStick_Num').disabled ) {
			params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();
		}

		if ( base_form.findField('EvnStick_Ser').disabled ) {
			params.EvnStick_Ser = base_form.findField('EvnStick_Ser').getValue();
		}

		if ( base_form.findField('EvnStick_setDate').disabled ) {
			params.EvnStick_setDate = Ext6.util.Format.date(base_form.findField('EvnStick_setDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_IsRegPregnancy').disabled ) {
			params.EvnStick_IsRegPregnancy = base_form.findField('EvnStick_IsRegPregnancy').getValue();
		}

		if ( base_form.findField('StickIrregularity_id').disabled ) {
			params.StickIrregularity_id = base_form.findField('StickIrregularity_id').getValue();
		}

		if ( base_form.findField('EvnStick_irrDate').disabled ) {
			params.EvnStick_irrDate = Ext6.util.Format.date(base_form.findField('EvnStick_irrDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_stacBegDate').disabled ) {
			params.EvnStick_stacBegDate = Ext6.util.Format.date(base_form.findField('EvnStick_stacBegDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_stacEndDate').disabled ) {
			params.EvnStick_stacEndDate = Ext6.util.Format.date(base_form.findField('EvnStick_stacEndDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_BirthDate').disabled ) {
			params.EvnStick_BirthDate = Ext6.util.Format.date(base_form.findField('EvnStick_BirthDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStickDop_pid').disabled ) {
			params.EvnStickDop_pid = base_form.findField('EvnStickDop_pid').getValue();
		}

		if ( base_form.findField('StickOrder_id').disabled ) {
			params.StickOrder_id = base_form.findField('StickOrder_id').getRawValue();
		}

		if ( base_form.findField('StickCause_id').disabled ) {
			params.StickCause_id = base_form.findField('StickCause_id').getValue();
		}

		if ( base_form.findField('StickWorkType_id').disabled ) {
			params.StickWorkType_id = base_form.findField('StickWorkType_id').getValue();
		}

		if ( base_form.findField('Org_id').disabled ) {
			params.Org_id = base_form.findField('Org_id').getValue();
		}

		if ( base_form.findField('EvnStick_OrgNick').disabled ) {
			params.EvnStick_OrgNick = base_form.findField('EvnStick_OrgNick').getValue();
		}

		if ( base_form.findField('Post_Name').disabled ) {
			params.Post_Name = base_form.findField('Post_Name').getValue();
		}

		if ( base_form.findField('StickCauseDopType_id').disabled ) {
			params.StickCauseDopType_id = base_form.findField('StickCauseDopType_id').getValue();
		}

		if ( base_form.findField('StickCause_did').disabled ) {
			params.StickCause_did = base_form.findField('StickCause_did').getValue();
		}

		if ( base_form.findField('EvnStick_StickDT').disabled ) {
			params.EvnStick_StickDT = Ext6.util.Format.date(base_form.findField('EvnStick_StickDT').getValue());
		}

		if ( base_form.findField('EvnStick_sstBegDate').disabled ) {
			params.EvnStick_sstBegDate = Ext6.util.Format.date(base_form.findField('EvnStick_sstBegDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_sstEndDate').disabled ) {
			params.EvnStick_sstEndDate = Ext6.util.Format.date(base_form.findField('EvnStick_sstEndDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_sstNum').disabled ) {
			params.EvnStick_sstNum = base_form.findField('EvnStick_sstNum').getValue();
		}

		if ( base_form.findField('Org_did').disabled ) {
			params.Org_did = base_form.findField('Org_did').getValue();
		}

		// данные поля отправляем даже задисабленные, только если заполнен исход (регистратор не может редактировать исход)
		if (!Ext6.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
			base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'));

			if ( base_form.findField('StickLeaveType_id').disabled ) {
				params.StickLeaveType_id = base_form.findField('StickLeaveType_id').getValue();
			}

			if ( base_form.findField('EvnStick_disDate').disabled ) {
				params.EvnStick_disDate = Ext6.util.Format.date(base_form.findField('EvnStick_disDate').getValue(), 'd.m.Y');
			}

			if ( base_form.findField('MedStaffFact_id').disabled ) {
				params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
			}

			if ( base_form.findField('Lpu_oid').disabled ) {
				params.Lpu_oid = base_form.findField('Lpu_oid').getValue();
			}
		}

		if ( win.link == true ) {
			params.link = 1;
		}

		if ( win.evnStickType == 1 ) {
			// Собираем данные из гридов
			evn_stick_care_person_grid.getStore().clearFilter();

			if ( evn_stick_care_person_grid.getStore().getCount() > 0 && evn_stick_care_person_grid.getStore().getAt(0).get('EvnStickCarePerson_id') ) {
				var evn_stick_care_person_data = sw4.getStoreRecords(evn_stick_care_person_grid.getStore(), {
					exceptionFields: [
						'accessType',
						'Person_Age',
						'Person_Fio',
						'RelatedLinkType_Name'
					]
				});

				var i = 0;

				// Если причина нетрудоспособности не подразумевает наличия пациентов, нуждающихся в уходе,
				// то записи из списка пациентов, нуждающихся в уходе, помечаются на удаление
				if ( stick_cause_sysnick && !stick_cause_sysnick.inlist([ 'karantin', 'uhodnoreb', 'uhodreb', 'uhod', 'rebinv', 'vich', 'zabrebmin' ])
					&& !(stick_cause_sysnick == 'postvaccinal' && !getRegionNick().inlist(['kz']))
				) {
					for ( i in evn_stick_care_person_data ) {
						if ( evn_stick_care_person_data[i].RecordStatus_Code > 0 ) {
							evn_stick_care_person_data[i].RecordStatus_Code = 3;
						}
						else {
							delete evn_stick_care_person_data[i];
						}
					}
				}
				else {
					for ( i in evn_stick_care_person_data ) {
						if ( evn_stick_care_person_data[i].RecordStatus_Code != 3 && evn_stick_care_person_data[i].Person_id == base_form.findField('Person_id').getValue() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: function() {
									win.formStatus = 'edit';
									base_form.findField('EvnStickFullNameText').focus(true);
								}.createDelegate(win),
								icon: Ext6.Msg.WARNING,
								msg: langs('Человек не может быть указан одновременно как получивший ЛВН и как пациент, нуждающийся в уходе.'),
								title: ERR_INVFIELDS_TIT
							});
							return false;
						}
					}
				}

				params.evnStickCarePersonData = Ext6.util.JSON.encode(evn_stick_care_person_data);

				evn_stick_care_person_grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
			}

			var personExists = false;

			// Проверяем, чтобы человек, на которого заведен учетный документ, был указан в качестве получателя ЛВН или присутствовал в списке
			// пациентов, нуждающихся в уходе, если указана соответствующая причина нетрудоспособности
			if ( Ext6.isEmpty(base_form.findField('StickFSSData_id').getValue()) && base_form.findField('Person_id').getValue() != advanceParams.Person_id ) {
				if ( stick_cause_sysnick && stick_cause_sysnick.inlist([ 'karantin', 'uhodnoreb', 'uhodreb', 'uhod', 'rebinv', 'vich', 'zabrebmin' ])
					|| (stick_cause_sysnick && stick_cause_sysnick == 'postvaccinal' && !getRegionNick().inlist(['kz']))
				) {
					evn_stick_care_person_grid.getStore().each(function(rec) {
						if ( rec.get('Person_id') == advanceParams.Person_id ) {
							personExists = true;
						}
					});
				}

				if ( personExists == false ) {
					Ext6.Msg.show({
						buttons: Ext6.Msg.OK,
						fn: function() {
							win.formStatus = 'edit';
							base_form.findField('EvnStickFullNameText').focus(true);
						}.createDelegate(win),
						icon: Ext6.Msg.WARNING,
						msg: langs('Человек, на которого ') + (win.parentClass.inlist([ 'EvnPL', 'EvnPLStom' ]) ? langs('заведен ТАП') : langs('заведена КВС')) + langs(', должен быть указан в качестве получателя ЛВН или присутствовать в списке пациентов, нуждающихся в уходе.'),
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}

			// Проверяем, чтобы человек, которому выдается ЛВН, отсутствовал в списке пациентов, нуждающихся в уходе,
			// если указана соответствующая причина нетрудоспособности
			if ( stick_cause_sysnick && stick_cause_sysnick.inlist([ 'karantin', 'uhodnoreb', 'uhodreb', 'uhod', 'rebinv', 'vich', 'zabrebmin' ])
				|| (stick_cause_sysnick && stick_cause_sysnick == 'postvaccinal' && !getRegionNick().inlist(['astra', 'kz']))
			) {
				personExists = false;

				evn_stick_care_person_grid.getStore().each(function(rec) {
					if ( rec.get('Person_id') == base_form.findField('Person_id').getValue() ) {
						personExists = true;
					}
				});

				if ( personExists == true ) {
					Ext6.Msg.show({
						buttons: Ext6.Msg.OK,
						fn: function() {
							win.formStatus = 'edit';
							base_form.findField('EvnStickFullNameText').focus(true);
						}.createDelegate(win),
						icon: Ext6.Msg.WARNING,
						msg: langs('Человек, который указан в качестве получателя ЛВН, присутствует в списке пациентов, нуждающихся в уходе.'),
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
		}

		if ( win.evnStickType.inlist([1,2]) ) {
			evn_stick_work_release_grid.getStore().clearFilter();

			if (this.hasWorkRelease()) {
				var evn_stick_work_release_data = sw4.getStoreRecords(evn_stick_work_release_grid.getStore(), {
					convertDateFields: true,
					exceptionFields: [
						'accessType',
						'Org_Name',
						'MedPersonal_Fio'
					]
				});

				if ( win.evnStickType == 2 ) {
					for(i=0; i<evn_stick_work_release_data.length; i++) {
						if (evn_stick_work_release_data[i].RecordStatus_Code.inlist([1,2]) && evn_stick_work_release_data[i].EvnStickBase_id == base_form.findField('EvnStickDop_pid').getValue()) {
							/*var tmpId = 0;
							do {
								tmpId = -Math.floor(Math.random() * 1000000);
							} while (evn_stick_work_release_grid.getStore().find('EvnStickWorkRelease_id', tmpId)>=0);*/
							evn_stick_work_release_data[i].EvnStickWorkRelease_id = -this.swGenTempId(evn_stick_work_release_grid.getStore(),'EvnStickWorkRelease_id');
							evn_stick_work_release_data[i].EvnStickBase_id = base_form.findField('EvnStick_id').getValue();
							evn_stick_work_release_data[i].RecordStatus_Code = 0;
						}
					}
				}

				params.evnStickWorkReleaseData = Ext6.util.JSON.encode(evn_stick_work_release_data);

				evn_stick_work_release_grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
			}
		}

		params.StickRegime_id = this.getStickRegimeId();

		if (options.ignoreStickOrderCheck) {
			params.ignoreStickOrderCheck = options.ignoreStickOrderCheck;
		}

		if(me.ignoreCheckEvnStickOrg == 1){
			params.ignoreCheckEvnStickOrg = me.ignoreCheckEvnStickOrg;
		}


		if (options.doUpdateJobInfo) {
			params.doUpdateJobInfo = options.doUpdateJobInfo;
		}

		win.mask(LOAD_WAIT_SAVE);

		// Необходимо, что бы ЛВН закрывался датой смерти пациента.
		// ajax запрос на проверку + калбэк.
		var checkparams = params;
		checkparams.EvnStick_pid = base_form.findField('EvnStick_pid').getValue();

		if ( base_form.findField('EvnStick_disDate').getValue() ) {
			checkparams.EvnStick_disDate = base_form.findField('EvnStick_disDate').getValue().format('d.m.Y');
		} else {
			checkparams.EvnStick_disDate = '01.01.1970';
		}
		checkparams.StickLeaveType_id = base_form.findField('StickLeaveType_id').getValue();

		if(params.EvnStick_pid && params.EvnStick_id){
			if(params.EvnStick_pid == params.EvnStick_id){
				alert('params.EvnStick_pid == params.EvnStick_id')
			}
		}

		Ext6.Ajax.request(
			{
				url: '/?c=Stick&m=CheckEvnStickDie',
				params: checkparams,
				callback: function(opt, scs, response)
				{
					if ( !options.ignoreSetDateDieError ) {

						if ( win.parentClass == 'EvnPS' ) {

							if (scs)
							{
								if ( response.responseText.length > 0 )
								{
									var result = Ext6.util.JSON.decode(response.responseText);
									if (!result.success)
									{
										Ext6.Msg.show({
											buttons: Ext6.Msg.YESNO,
											fn: function(buttonId, text, obj) {
												win.formStatus = 'edit';

												if ( 'yes' == buttonId ) {
													options.ignoreSetDateDieError = true;
													_this.doSave(options);
												}
												else {
													win.unmask();
													me._focusButtonSave();
												}
											}.createDelegate(win),
											icon: Ext6.MessageBox.QUESTION,
											msg: langs('Исход госпитализации и исход ЛВН не совпадают, либо отличаются даты смерти в ЛВН и КВС, Продолжить?'),
											title: langs('Вопрос')
										});
										return false;
									}
								}
							}
						}
					}

					base_form.submit({
						failure: function(result_form, action) {
							win.formStatus = 'edit';
							win.unmask();

							if ( action.result ) {
								if ( action.result.Alert_Msg && action.result.Error_Msg == 'YesNo' ) {
									var msg = action.result.Alert_Msg;

									Ext6.Msg.show({
										buttons: Ext6.Msg.YESNO,
										fn: function(buttonId, text, obj) {
											me.formStatus = 'edit';

											if (buttonId == 'yes'){
												switch (true) {
													case (101 == action.result.Error_Code):
														options.ignoreStickOrderCheck = 1;
														break;

													case (102 == action.result.Error_Code):
														options.doUpdateJobInfo = 1;
														break;

													case (103 == action.result.Error_Code):
														me.ignoreCheckEvnStickOrg = 1;
														break;

												}
												_this.doSave(options);
											}

											if (buttonId == 'no'){

												if(103 != action.result.Error_Code){
													me.hide();
												} else {
													me.ignoreCheckEvnStickOrg = 1;
												}
											}
										},
										icon: Ext6.MessageBox.QUESTION,
										msg: msg,
										title: langs(' Продолжить сохранение?')
									});

									return false;
								} else if(action.result.Alert_Msg) {
								switch (true) {
									case (201 == action.result.Error_Code):
										Ext6.Msg.show({
											buttons: Ext6.Msg.OK,
											fn: function(buttonId, text, obj) {
												if(buttonId == 'ok') {
													win.queryById('EStEF_MSEPanel').expand();
													base_form.findField('EvnStick_mseDate').focus();
												}
											},
											msg: action.result.Alert_Msg,
											icon: Ext6.Msg.WARNING,
											title: langs('Ошибка')
										});
										break;
								}

								} else if ( action.result.Error_Msg ) {
									Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
								}
								else {
									Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
								}

								if (action.result.Error_Code && action.result.Error_Code == '401') {
									// очищаем поле номер
									base_form.findField('RegistryESStorage_id').setValue(null);
									base_form.findField('EvnStick_Num').setRawValue('');
									base_form.findField('EvnStick_Num').fireEvent('change', base_form.findField('EvnStick_Num'), base_form.findField('EvnStick_Num').getValue());
								}
							}
						}.createDelegate(win),
						params: params,
						success: function(result_form, action) {

							win.unmask();

							if ( action.result.EvnStick_id > 0 ) {
								base_form.findField('EvnStick_id').setValue(action.result.EvnStick_id);
							}
							else {
								Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
								return false;
							}

							win.formStatus = 'edit';


							if ( action.result ) {
								var evn_stick_id = base_form.findField('EvnStick_id').getValue();
								var stick_order_id = base_form.findField('StickOrder_id').getValue();
								var stick_order_name = base_form.findField('StickOrder_id').getFieldValue('StickOrder_Name');
								var stick_work_type_id = base_form.findField('StickWorkType_id').getValue();
								var stick_work_type_name = base_form.findField('StickWorkType_id').getFieldValue('StickWorkType_Name');

								var data = new Object();

								data.evnStickData = [{
									'accessType': 'edit',
									'delAccessType': win.delAccessType,
									'cancelAccessType': win.cancelAccessType,
									'evnStickType': win.evnStickType,
									'EvnStick_disDate': base_form.findField('EvnStick_disDate').getValue(),
									'EvnStick_id': base_form.findField('EvnStick_id').getValue(),
									'EvnStick_mid': base_form.findField('EvnStick_mid').getValue(),
									'EvnStick_Num': base_form.findField('EvnStick_Num').getValue(),
									'EvnStick_ParentNum': (base_form.findField('EvnStick_mid').getValue() != base_form.findField('EvnStick_pid').getValue() ? win.parentNum : ''),
									'EvnStick_ParentTypeName': (base_form.findField('EvnStick_mid').getValue() != base_form.findField('EvnStick_pid').getValue() ? (win.parentClass == 'EvnPL' ? langs('ТАП') : (win.parentClass == 'EvnPLStom' ? langs('Стом. ТАП') : langs('КВС'))) : langs('Текущий')),
									'EvnStick_IsOriginal' : (base_form.findField('EvnStick_IsOriginal').getRawValue() == 2)?langs('Дубликат'):langs('Оригинал'),
									'EvnStick_stacBegDate' : Ext6.util.Format.date(base_form.findField('EvnStick_stacBegDate').getValue(), 'd.m.Y'),
									'EvnStick_stacEndDate' : Ext6.util.Format.date(base_form.findField('EvnStick_stacEndDate').getValue(), 'd.m.Y'),
									'EvnSection_setDate' : base_form.findField('EvnSection_setDate').getValue(),
									'EvnSection_disDate' : base_form.findField('EvnSection_disDate').getValue(),
									'EvnStick_pid': base_form.findField('EvnStick_pid').getValue(),
									'EvnStick_Ser': base_form.findField('EvnStick_Ser').getValue(),
									'EvnStick_setDate': base_form.findField('EvnStick_setDate').getValue(),
									'EvnStickWorkRelease_begDate': evn_stick_work_release_beg_date,
									'EvnStickWorkRelease_endDate': evn_stick_work_release_end_date,
									'parentClass': win.parentClass,
									'Person_id': base_form.findField('Person_id').getValue(),
									'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
									'Server_id': base_form.findField('Server_id').getValue(),
									'StickOrder_Name': stick_order_name,
									'StickType_Name': langs('ЛВН'),
									'StickWorkType_Name': stick_work_type_name,
									'Org_Nick': Org_Nick,
									'EvnStatus_Name': EvnStatus_Name
								}];

								win.callback(data);


								if(
									getRegionNick() != 'kz' &&
									(_this.checkIsLvnFromFSS() || _this.checkIsLvnELN()) &&
									// если ЛВН является продолжением
									base_form.findField('StickOrder_id').getFieldValue('StickOrder_Code') == 2 &&
									vm.get('action') == 'add'
								){
									Ext6.Msg.show({
										buttons: Ext6.Msg.YESNO,
										fn: function(buttonId, text, obj) {

											if ( 'yes' == buttonId ) {

												win.params.action = 'edit';
												win.params.evnStickType = stick_work_type_id == 2 ? 2 : 1;
												win.params.formParams.EvnStick_id = base_form.findField('EvnStick_prid').getValue();
												win.params.formParams.Person_id = win.params.Person_id;

												if(win.params.PersonEvn_id) {
													win.params.formParams.PersonEvn_id = win.params.PersonEvn_id;
												} else {
													win.params.formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
												}

												if(win.params.Server_id) {
													win.params.formParams.Server_id = win.params.Server_id;
												}
												getWnd('swEvnStickEditWindow').show(win.params);
											}

										}.createDelegate(win),
										icon: Ext6.MessageBox.QUESTION,
										msg: langs('Для успешной сдачи сведений об ЛВН в ФСС после создания ЛВН-продолжения необходимо подписывать первичный ЛВН. Открыть первичный ЛВН?».'),
										title: langs('Вопрос')
									});
									win.hide();
								}

								if ( options && (options.print || options.ignoreEvnStickContQuestion) ) {
									if ( options.print ) {
										_this.CheckWorkRelease();

										// Перезагружаем списки
										// https://redmine.swan.perm.ru/issues/8568
										var evn_stick_id = base_form.findField('EvnStick_id').getValue();
										var evn_stick_dop_pid = base_form.findField('EvnStickDop_pid').getValue();

										win.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().load({
											params: {
												EvnStick_id: evn_stick_id,
												EvnStickDop_pid: (evn_stick_dop_pid > 0)?evn_stick_dop_pid:null
											}
										});

										if ( win.evnStickType == 2 ) {
											evn_stick_id = win.FormPanel.getForm().findField('EvnStickDop_pid').getValue();
										}
										else {
											evn_stick_id = win.FormPanel.getForm().findField('EvnStick_id').getValue();
										}

										win.queryById('EStEF_EvnStickCarePersonGrid').getStore().load({
											params: {
												EvnStick_id: evn_stick_id
											}
										});
									}
								}
								else if ( options.callback && typeof options.callback == 'function' ) {
									// надо обновить список освобождений от работы и запустить callback
									var evn_stick_id = base_form.findField('EvnStick_id').getValue();

									var EvnStickWorkReleaseGrid = win.queryById('EStEF_EvnStickWorkReleaseGrid');
									
									EvnStickWorkReleaseGrid.getStore().load({
										params: {
											EvnStick_id: evn_stick_id
										},
										callback: function() {
											
											options.callback();
										}
									});

									if ( win.evnStickType == 2 ) {
										evn_stick_id = win.FormPanel.getForm().findField('EvnStickDop_pid').getValue();
									}
									else {
										evn_stick_id = win.FormPanel.getForm().findField('EvnStick_id').getValue();
									}

									win.queryById('EStEF_EvnStickCarePersonGrid').getStore().load({
										params: {
											EvnStick_id: evn_stick_id
										}
									});
								}
								else if (
									Ext6.isEmpty(base_form.findField('EvnStickNext_id').getValue()) // если ещё нет продолжения
									&& Ext6.isEmpty(base_form.findField('EvnStick_NumNext').getValue()) // если нет ЛВН-продолжение в блоке "Исход"
									&& !Ext6.isEmpty(base_form.findField('EvnStick_id').getValue())
									&& base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code')
									&& base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code').inlist(['31','37']) // и исход 31 или 37
								) {
									Ext6.Msg.show({
										buttons: Ext6.Msg.YESNO,
										fn: function(buttonId, text, obj) {
											if ( 'yes' == buttonId ) {
												win.params.EvnStick_id = null;
												win.params.EvnStick_Num = null;
												win.params.JobOrg_id = null;
												win.params.Person_Post = null;
												win.params.StickOrder_Code = '2';
												win.params.EvnStick_prid = base_form.findField('EvnStick_id').getValue();
												if (base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code') == '37') {
													win.params.StickCause_SysNick = 'dolsan';
													// win.params.Org_did = base_form.findField('Lpu_oid').getFieldValue('Org_id');
												}

												win.params.action = 'add';
												win.params.formParams.EvnStick_id = null;
												win.params.formParams.EvnStick_setDate = null;
												win.params.formParams.StickFSSData_id = null;
												win.params.formParams.Person_id = win.params.Person_id;
												win.params.formParams.EvnStick_pid = win.params.formParams.EvnStick_mid; // должен привязаться к текущему случаю, а не к тому, куда был привязан первичный ЛВН.


												if(win.params.PersonEvn_id) {
													win.params.formParams.PersonEvn_id = win.params.PersonEvn_id;
												} else {
													win.params.formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
												}

												if(win.params.Server_id) {
													win.params.formParams.Server_id = win.params.Server_id;
												}
												win.params.link = 0;

												getWnd('swEvnStickEditWindow').show(win.params);
											}
										}.createDelegate(win),
										icon: Ext6.MessageBox.QUESTION,
										msg: 'Согласно правилам оформления ЛВН при закрытии ЛВН с исходом «Продолжает болеть» и «Долечивание» необходимо заполнять ЛВН-продолжение. Заполнить ЛВН-продолжение сейчас?',
										title: langs('Вопрос')
									});
									win.hide();
								}
								else {
									win.hide();
								}
							}
							else {
								Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
							}
						}.createDelegate(win)
					});
				}.createDelegate(win)
			});
	}
});