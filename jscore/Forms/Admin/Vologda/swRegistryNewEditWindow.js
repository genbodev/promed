/**
* swRegistryNewEditWindow - окно редактирования/добавления реестра (счета) (Вологда).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @region       Vologda
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Stanislav Bykov
* @version      06.11.2018
* @comment      Префикс для id компонентов RNEF (RegistryNewEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              Registry_id - ID реестра
*/

sw.Promed.swRegistryNewEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	firstTabIndex: 15150,
	id: 'RegistryNewEditWindow',
	layout: 'form',
	modal: true,
	PasportMOAssignNaselArray: {},
	plain: true,
	resizable: false,
	split: true,
	width: 600,

	/* методы */
	callback: Ext.emptyFn,
	doSave: function() {
		var
			base_form = this.RegistryForm.getForm(),
			win = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.RegistryForm.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var
			begDate = base_form.findField('Registry_begDate').getValue(),
			endDate = base_form.findField('Registry_endDate').getValue();

		if ( typeof begDate == 'object' && typeof endDate == 'object' && begDate > endDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('Registry_begDate').focus(false);
				},
				icon: Ext.Msg.ERROR,
				msg: 'Дата окончания не может быть меньше даты начала.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		win.doSubmit();

		return true;
	},
	doSubmit: function() {
		var
			base_form = this.RegistryForm.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."}),
			win = this;

		loadMask.show();

		var Registry_accDate = base_form.findField('Registry_accDate').getValue().dateFormat('d.m.Y');

		if (
			base_form.findField('Registry_isPersFinCheckbox').getValue() == true
			&& win.RegistryType_id.toString().inlist([1,2,6])
			&& win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
		) {
			base_form.findField('Registry_isPersFin').setValue(2);
		}
		else {
			base_form.findField('Registry_isPersFin').setValue(1);
		}

		if (
			base_form.findField('Registry_isFapCheckbox').getValue() == true
		) {
			base_form.findField('Registry_IsFAP').setValue(2);
		}
		else {
			base_form.findField('Registry_IsFAP').setValue(1);
		}

		if (
			!Ext.isEmpty(win.RegistryType_id) && win.RegistryType_id.inlist([1, 2, 15 ,20])
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& base_form.findField('Registry_IsZNOCheckbox').getValue() == true
		) {
			base_form.findField('Registry_IsZNO').setValue(2);
		}
		else {
			base_form.findField('Registry_IsZNO').setValue(1);
		}

		var params = {
			RegistryType_id: base_form.findField('RegistryType_id').getValue(),
			Registry_accDate: Registry_accDate
		};

		params.KatNasel_id = base_form.findField('KatNasel_id').getValue();

		if ( base_form.findField('Registry_IsRepeated').disabled ) {
			params.Registry_IsRepeated = base_form.findField('Registry_IsRepeated').getValue();
		}

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if ( base_form.findField('OrgSmo_id').disabled ) {
			params.OrgSmo_id = base_form.findField('OrgSmo_id').getValue();
		}

		params.Registry_IsNew = 2;

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			},
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.RegistryQueue_id ) {
						var records = {
							RegistryQueue_id: action.result.RegistryQueue_id,
							RegistryQueue_Position: action.result.RegistryQueue_Position
						}

						win.callback(win.owner, action.result.RegistryQueue_id, records);
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'При выполнении операции сохранения произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
	},
	checkLpuHasVolume: function(){
		var
			win = this,
			base_form = win.RegistryForm.getForm(),
			endDate = base_form.findField('Registry_endDate').getValue();
		
		if (
			win.RegistryType_id.toString().inlist([1])
			&& endDate
		){
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert('Ошибка', 'При провеоки у МО объема «Районные МО» возникли ошибки');
				},
				url: '/?c=TariffVolumes&m=checkLpuHasVolume',
				params: {
					VolumeType_Code: '2020-РМО',
					Lpu_id: base_form.findField('Lpu_id').getValue(),
					Registry_endDate : Ext.util.Format.date(endDate,'d.m.Y')
				},
				success: function(response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( typeof response_obj == 'object') {
						win.checkLpuHas = response_obj.length>0 ?  true : false;
						win.setIsPersFinCheckboxVisibility();
					}
				}
			});
		}else{
			win.checkLpuHas=false;
			win.setIsPersFinCheckboxVisibility();
		}
	},
	enableEdit: function(enable) {
		var base_form = this.RegistryForm.getForm();

		if ( enable ) {
			if (this.RegistryType_id.toString().inlist(['1','2','14'])) {
				base_form.findField('PayType_id').enable();
				base_form.findField('PayType_id').setAllowBlank(false);
			} else {
				base_form.findField('PayType_id').disable();
				base_form.findField('PayType_id').setAllowBlank(true);
			}

			if ( this.action == 'add' ) {
				base_form.findField('Registry_IsZNOCheckbox').enable();
				base_form.findField('KatNasel_id').enable();
			}
			else {
				base_form.findField('Registry_IsZNOCheckbox').disable();
				base_form.findField('KatNasel_id').disable();
			}

			base_form.findField('DispClass_id').enable();
			base_form.findField('Lpu_cid').enable();
			base_form.findField('Org_did').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_isPersFinCheckbox').enable();
			base_form.findField('Registry_isFapCheckbox').enable();
			base_form.findField('Registry_IsRepeated').enable();
			base_form.findField('Registry_Num').enable();

			this.buttons[0].show();
		}
		else  {
			base_form.findField('DispClass_id').disable();
			base_form.findField('KatNasel_id').disable();
			base_form.findField('Lpu_cid').disable();
			base_form.findField('Org_did').disable();
			base_form.findField('PayType_id').disable();
			base_form.findField('Registry_IsZNOCheckbox').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_isPersFinCheckbox').disable();
			base_form.findField('Registry_isFapCheckbox').disable();
			base_form.findField('Registry_IsRepeated').disable();
			base_form.findField('Registry_Num').disable();

			this.buttons[0].hide();
		}
	},
	filterLpuCid: function() {
		var
			index,
			win = this,
			base_form = win.RegistryForm.getForm(),
			Lpu_cid = base_form.findField('Lpu_cid').getValue(),
			Registry_begDate = base_form.findField('Registry_begDate').getValue();

		base_form.findField('Lpu_cid').lastQuery = '';
		base_form.findField('Lpu_cid').getStore().filterBy(function(rec) {
			return (
				rec.get('Lpu_id') != getGlobalOptions().lpu_id
				&& (
					Ext.isEmpty(Registry_begDate)
					|| (
						(Ext.isEmpty(rec.get('Lpu_BegDate')) || getValidDT(rec.get('Lpu_BegDate'), '') <= Registry_begDate)
						&& (Ext.isEmpty(rec.get('Lpu_EndDate')) || getValidDT(rec.get('Lpu_EndDate'), '') >= Registry_begDate)
					)
				)
			);
		});
		base_form.findField('Lpu_cid').setBaseFilter(function(rec) {
			return (
				rec.get('Lpu_id') != getGlobalOptions().lpu_id
				&& (
					Ext.isEmpty(Registry_begDate)
					|| (
						(Ext.isEmpty(rec.get('Lpu_BegDate')) || getValidDT(rec.get('Lpu_BegDate'), '') <= Registry_begDate)
						&& (Ext.isEmpty(rec.get('Lpu_EndDate')) || getValidDT(rec.get('Lpu_EndDate'), '') >= Registry_begDate)
					)
				)
			);
		});

		if ( !Ext.isEmpty(Lpu_cid) ) {
			index = base_form.findField('Lpu_cid').getStore().findBy(function(rec) {
				return (rec.get('Lpu_id') == Lpu_cid);
			});

			if ( index >= 0 ) {
				base_form.findField('Lpu_cid').setValue(Lpu_cid);
			}
			else {
				base_form.findField('Lpu_cid').clearValue();
			}
		}
	},
	onHide: Ext.emptyFn,
	setIsPersFinCheckboxVisibility: function() {
		var
			win = this,
			base_form = win.RegistryForm.getForm();

		if (
			(
				win.RegistryType_id.toString().inlist([2,6])
			|| (win.RegistryType_id.toString().inlist([1]) && win.checkLpuHas=== true)
			)
			&& win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& !Ext.isEmpty(base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick'))
			&& base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick').inlist(['all','oblast'])
		) {
			if (win.acton == 'add') {
				base_form.findField('Registry_isPersFinCheckbox').setValue(true);
			}

			base_form.findField('Registry_isPersFinCheckbox').showContainer();
		}
		else {
			base_form.findField('Registry_isPersFinCheckbox').setValue(false);
			base_form.findField('Registry_isPersFinCheckbox').hideContainer();
		}

		win.syncSize();
		win.syncShadow();
	},
	setIsFapCheckboxVisibility: function() {
		var
			win = this,
			base_form = win.RegistryForm.getForm(),
			PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick'),
			KatNasel_SysNick = base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick'),
			Registry_isPersFinCheckbox = base_form.findField('Registry_isPersFinCheckbox').getValue();

		if (
			win.RegistryType_id.toString().inlist([2])
			&& PayType_SysNick == 'oms'
			&& !Ext.isEmpty(KatNasel_SysNick) && KatNasel_SysNick.inlist(['all','oblast'])
			&& win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
			&& Registry_isPersFinCheckbox == true
		) {
			base_form.findField('Registry_isFapCheckbox').showContainer();
		}
		else {
			base_form.findField('Registry_isFapCheckbox').setValue(false);
			base_form.findField('Registry_isFapCheckbox').hideContainer();
		}

		win.syncSize();
		win.syncShadow();
	},
	setKatNaselVisibility: function() {
		var
			win = this,
			base_form = win.RegistryForm.getForm();

		if (
			base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
		) {
			base_form.findField('KatNasel_id').setContainerVisible(true);
			base_form.findField('KatNasel_id').setAllowBlank(false);
		}
		else {
			base_form.findField('KatNasel_id').clearValue();
			base_form.findField('KatNasel_id').setAllowBlank(true);
			base_form.findField('KatNasel_id').setContainerVisible(false);
		}

		win.setOrgSmoVisibility();

		win.syncSize();
		win.syncShadow();
	},
	setOrgDidVisibility: function() {
		var
			win = this,
			base_form = win.RegistryForm.getForm();

		if (
			base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'speckont'
			|| base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'contract'
		) {
			base_form.findField('Org_did').setContainerVisible(true);
		}
		else {
			base_form.findField('Org_did').clearValue();
			base_form.findField('Org_did').setContainerVisible(false);
		}

		win.syncSize();
		win.syncShadow();
	},
	setOrgSmoVisibility: function() {
		var
			win = this,
			base_form = win.RegistryForm.getForm();

		if (
			base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick') == 'oblast'
		) {
			base_form.findField('OrgSmo_id').setContainerVisible(true);
			base_form.findField('OrgSmo_id').setAllowBlank(false);

			var index = base_form.findField('OrgSmo_id').getStore().findBy(function(rec) {
				return (rec.get('Orgsmo_f002smocod') == '35003');
			});

			if (index >= 0) {
				var record = base_form.findField('OrgSmo_id').getStore().getAt(index);
				base_form.findField('OrgSmo_id').setValue(record.get('OrgSMO_id'));
			}
		}
		else {
			base_form.findField('OrgSmo_id').clearValue();
			base_form.findField('OrgSmo_id').setAllowBlank(true);
			base_form.findField('OrgSmo_id').setContainerVisible(false);
		}

		win.syncSize();
		win.syncShadow();
	},
	setZNOCheckboxVisibility: function() {
		var
			win = this,
			base_form = win.RegistryForm.getForm();

		if (
			!Ext.isEmpty(win.RegistryType_id) && win.RegistryType_id.inlist([1, 2, 15 ,20])
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
		) {
			base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(true);
		}
		else {
			base_form.findField('Registry_IsZNOCheckbox').setValue(false);
			base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(false);
		}

		win.syncSize();
		win.syncShadow();
	},
	show: function() {
		sw.Promed.swRegistryNewEditWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.RegistryForm.getForm(),
			form = this;

		if ( !arguments[0] || !arguments[0].RegistryType_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы ' + form.id + '.<br/>Не указаны нужные входные параметры.',
				title: 'Ошибка'
			});
			form.hide();
			return false;
		}

		base_form.reset();

		form.action = 'add';
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		form.owner = null;
		form.Registry_id = null;
		form.RegistryStatus_id = null;
		form.RegistryType_id = null;

		if ( arguments[0].action ) {
			form.action = arguments[0].action;
		}

		if ( typeof arguments[0].callback == 'function' ) {
			form.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			form.onHide = arguments[0].onHide;
		}

		if ( arguments[0].owner ) {
			form.owner = arguments[0].owner;
		}

		if ( !Ext.isEmpty(arguments[0].Registry_id) ) {
			form.Registry_id = arguments[0].Registry_id;
		}
			
		if ( !Ext.isEmpty(arguments[0].RegistryStatus_id) ) {
			form.RegistryStatus_id = arguments[0].RegistryStatus_id;
		}

		if ( !Ext.isEmpty(arguments[0].RegistryType_id) ) {
			form.RegistryType_id = arguments[0].RegistryType_id;
		}

		if ( 'add' == form.action ) {
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
		}

		base_form.findField('Lpu_cid').setContainerVisible(form.RegistryType_id.toString().inlist(['20']));
		base_form.findField('Org_did').setContainerVisible(false);
		base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(false);

		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9' ]));

		if ( form.RegistryType_id.toString().inlist([ '7', '9' ]) ) {
			var dispClassList = [];

			switch ( form.RegistryType_id ) {
				case 7: // Дисп-ция взр. населения
					dispClassList = [ '1', '2' ];
					break;

				case 9: // Дисп-ция детей-сирот
					dispClassList = [ '3', '7' ];
					break;
			}

			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
		}

		if ( form.action == 'edit' ) {
			form.buttons[0].setText('Переформировать');
		}
		else {
			form.buttons[0].setText('Сохранить');
		}

		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if ( form.RegistryStatus_id == 4 ) {
			form.action = "view";
		}

		base_form.setValues(arguments[0]);
		base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());

		if ( form.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] == undefined && form.RegistryType_id.toString().inlist([1,2,6]) ) {
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert('Ошибка', 'При получении значения признака "МО имеет приписное население" возникли ошибки');
				},
				params: {
					param: 'PasportMO_IsAssignNasel',
					Lpu_id: base_form.findField('Lpu_id').getValue()
				},
				success: function(response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( typeof response_obj == 'object' && response_obj.length > 0 ) {
						form.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] = (response_obj[0].PasportMO_IsAssignNasel == 1);
					}
					
					if(form.RegistryType_id.toString().inlist([1]) ){
						form.checkLpuHasVolume();
					}else {
						form.setIsPersFinCheckboxVisibility();
					}
					
					form.setIsFapCheckboxVisibility();
				},
				url: '/?c=LpuPassport&m=getLpuPassport'
			});
		}

		form.syncSize();
		form.syncShadow();

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		switch ( form.action ) {
			case 'add':
				form.setTitle(WND_ADMIN_REGISTRYADD);
				form.enableEdit(true);

				loadMask.hide();

				base_form.findField('Registry_IsRepeated').setValue(1);
				base_form.findField('Registry_begDate').focus(true, 50);
				break;

			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				base_form.findField('Registry_IsRepeated').disable();
				base_form.findField('Registry_IsZNOCheckbox').disable();
				break;

			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
				break;
		}

		if ( form.action != 'add' ){
			base_form.load({
				params: {
					Registry_IsNew: 2,
					Registry_id: form.Registry_id,
					RegistryType_id: form.RegistryType_id
				},
				failure: function() {
					loadMask.hide();

					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
						title: 'Ошибка'
					});
				},
				success: function() {
					loadMask.hide();

					var Org_did = base_form.findField('Org_did').getValue();

					if (base_form.findField('Registry_isPersFin').getValue() == 2) {
						base_form.findField('Registry_isPersFinCheckbox').setValue(true);
					}

					if (base_form.findField('Registry_IsFAP').getValue() == 2) {
						base_form.findField('Registry_isFapCheckbox').setValue(true);
					}

					if (base_form.findField('Registry_IsZNO').getValue() == 2) {
						base_form.findField('Registry_IsZNOCheckbox').setValue(true);
					}

					base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());
					base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);

					form.filterLpuCid();

					if(form.RegistryType_id.toString().inlist([1]) ){
						form.checkLpuHasVolume();
					}else {
						form.setIsPersFinCheckboxVisibility();
					}
					
					form.setKatNaselVisibility();
					form.setOrgDidVisibility();
					form.setOrgSmoVisibility();
					form.setZNOCheckboxVisibility();
					form.setIsFapCheckboxVisibility();

					if (!Ext.isEmpty(Org_did)) {
						base_form.findField('Org_did').getStore().load({
							callback: function() {
								if ( base_form.findField('Org_did').getStore().getCount() > 0 ) {
									base_form.findField('Org_did').setValue(Org_did);
								}
								else {
									base_form.findField('Org_did').clearValue();
								}
							},
							params: {
								Org_id: Org_did
							}
						});
					}

					if ( form.action == 'edit' ) {
						base_form.findField('Registry_begDate').focus(true, 50);
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				url: '/?c=Registry&m=loadRegistry'
			});
		}
		else {
			form.filterLpuCid();

			if(form.RegistryType_id.toString().inlist([1]) ){
				form.checkLpuHasVolume();
			}else {
				form.setIsPersFinCheckboxVisibility();
			}
			
			form.setKatNaselVisibility();
			form.setOrgDidVisibility();
			form.setOrgSmoVisibility();
			form.setZNOCheckboxVisibility();
			form.setIsFapCheckboxVisibility();

		}
	},
	
	/* конструктор */
	initComponent: function() {
		// Форма с полями 
		var form = this;
		
		this.RegistryForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'RegistryEditForm',
			labelAlign: 'right',
			labelWidth: 190,
			items: [{
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryStatus_id',
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'Registry_isPersFin'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsFAP'
			}, {
				anchor: '100%',
				disabled: true,
				hiddenName: 'RegistryType_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swregistrytypecombo'
			}, {
				allowBlank: false,
				allowSysNick: true,
				anchor: '100%',
				comboSubject: 'PayType',
				fieldLabel: langs('Вид оплаты'),
				listeners: {
					'change': function(combo, newValue, oldValue) {
						if(form.RegistryType_id.toString().inlist([1]) ){
							form.checkLpuHasVolume();
						}else {
							form.setIsPersFinCheckboxVisibility();
						}
						form.setKatNaselVisibility();
						form.setOrgDidVisibility();
						form.setZNOCheckboxVisibility();
						form.setIsFapCheckboxVisibility();
					},
					'select': function(combo, newValue, oldValue) {
						if(form.RegistryType_id.toString().inlist([1]) ){
							form.checkLpuHasVolume();
						}else {
							form.setIsPersFinCheckboxVisibility();
						}
						form.setKatNaselVisibility();
						form.setOrgDidVisibility();
						form.setZNOCheckboxVisibility();
						form.setIsFapCheckboxVisibility();
					}
				},
				loadParams: {
					params: {
						where: " where PayType_SysNick in ('oms', 'bud', 'dms', 'speckont', 'contract')"
					}
				},
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				anchor: '100%',
				fieldLabel: 'Направившая организация',
				hiddenName: 'Org_did',
				tabIndex: form.firstTabIndex++,
				xtype: 'sworgcomboex'
			}, {
				fieldLabel: 'ЗНО',
				name: 'Registry_IsZNOCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				listeners: {
					'change': function(field) {
						form.filterLpuCid();
					}
				},
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				listeners: {
					'change': function (combo, newValue, oldValue) {
						if (form.RegistryType_id.toString().inlist([1])){
							form.checkLpuHasVolume();
						}
					}
				},
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				anchor: '100%',
				fieldLabel: 'Категория населения',
				hiddenName: 'KatNasel_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						if(form.RegistryType_id.toString().inlist([1]) ){
							form.checkLpuHasVolume();
						}else {
							form.setIsPersFinCheckboxVisibility();
						}
						form.setOrgSmoVisibility();
						form.setIsFapCheckboxVisibility();
					},
					'select': function(combo, newValue, oldValue) {
						if(form.RegistryType_id.toString().inlist([1]) ){
							form.checkLpuHasVolume();
						}else {
							form.setIsPersFinCheckboxVisibility();
						}
						form.setOrgSmoVisibility();
						form.setIsFapCheckboxVisibility();
					}
				},
				tabIndex: form.firstTabIndex++,
				xtype: 'swkatnaselcombo'
			}, {
				anchor: '100%',
				disabled: true,
				fieldLabel: 'СМО',
				withoutTrigger: true,
				xtype: 'sworgsmocombo',
				hiddenName: 'OrgSmo_id',
				name: 'OrgSmo_id',
				tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">' +
					'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null && values.OrgSMO_id !=8) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}' +
					'</div></tpl>'),
				tabIndex: form.firstTabIndex++,
				minChars: 1,
				onTrigger2Click: function () {
					if (this.disabled)
						return;
					var combo = this;
					getWnd('swOrgSearchWindow').show({
						object: 'smo',
						onClose: function () {
							combo.focus(true, 200);
						},
						onSelect: function (orgData) {
							if (orgData.Org_id > 0) {
								combo.setValue(orgData.Org_id);
								combo.focus(true, 250);
								combo.fireEvent('change', combo);
							}
							getWnd('swOrgSearchWindow').hide();
						}
					});
				},
				queryDelay: 1
			}, {
				fieldLabel: 'Подушевое финансирование',
				name: 'Registry_isPersFinCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox',
				handler: function() {
					form.setIsFapCheckboxVisibility();
				}
			}, {
				fieldLabel: 'ФАП',
				name: 'Registry_isFapCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				anchor: '100%',
				hiddenName: 'Lpu_cid',
				fieldLabel: 'МО-контрагент',
				xtype: 'swlpucombo',
				tabIndex: form.firstTabIndex++
			}, {
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип диспансеризации',
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: 'Повторная подача',
				hiddenName: 'Registry_IsRepeated',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swyesnocombo'
			}, {
				allowBlank: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "10",
					autocomplete: "off"
				},
				fieldLabel: 'Номер счета',
				name: 'Registry_Num',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if (form.action != 'view') {
								form.doSave(false);
							}
							break;

						case Ext.EventObject.J:
							form.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'DispClass_id' },
				{ name: 'KatNasel_id' },
				{ name: 'Lpu_cid' },
				{ name: 'Lpu_id' },
				{ name: 'Org_did' },
				{ name: 'OrgSmo_id' },
				{ name: 'PayType_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_isPersFin' },
				{ name: 'Registry_IsFAP' },
				{ name: 'Registry_IsRepeated' },
				{ name: 'Registry_IsZNO' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryStatus_id' },
				{ name: 'RegistryType_id' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveRegistry'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				tabIndex: form.firstTabIndex++,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, form.firstTabIndex++),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				tabIndex: form.firstTabIndex++,
				text: BTN_FRMCANCEL
			}],
			items: [
				form.RegistryForm
			]
		});

		sw.Promed.swRegistryNewEditWindow.superclass.initComponent.apply(this, arguments);
	}
});