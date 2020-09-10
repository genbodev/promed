/**
* swPolisEditWindow - окно редактирования полиса.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      05.10.2010
*/

sw.Promed.swPolisEditWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'fit',
    width: 450,
    modal: true,
	resizable: false,
	draggable: false,
    autoHeight: true,
    closeAction : 'hide',
	id: 'polis_edit_window',
    plain: true,
	returnFunc: function() {},
    title: lang['polis_redaktirovanie'],
	listeners: {
		'hide': function() {
			this.onWinClose();
		}
	},
	onShowActions: function() {		
		var win = this;
		var base_form = this.findById('polis_edit_form').getForm();
		base_form.findField('PolisType_id').setValue(4);
		
		var cur_reg = getGlobalOptions().region ? getGlobalOptions().region['number'] : 59;
		var isUfa = (getRegionNick() == 'ufa');
		var isEkb = (getRegionNick() == 'ekb');
		var isPerm = (getRegionNick() == 'perm');
		var combowhere = "";
		var fullSprLoad = false;
		base_form.setValues(this.fields);
		var periodEd = (getRegionNick().inlist(['ekb','pskov','buryatiya','vologda','adygeya'])&&isUserGroup('editorperiodics'));
		//getRegionNick()=='pskov'&&!isUserGroup('editorperiodics')
		// если полис БДЗ то не даём редактировать. (refs #6827) или если текущий регион и не уфа.
		// а также для всех ЛПУ в Перми кроме Отделенческой КБ на ст. Пермь 2. (refs #7975) -- убрал согласно #8902: 
		// (this.action != 'edit_with_load' && !isSuperAdmin() && !isUfa && getGlobalOptions().lpu_sysnick != 'permotdkbp2')
		if (!periodEd && (getRegionNick() != 'astra' && (this.fields.BDZ_id != null || this.readOnly || (!isSuperAdmin() && !getRegionNick().inlist([ 'krym', 'ufa', 'buryatiya', 'vologda', 'adygeya' ]) && this.fields.KLRgn_id == cur_reg && (getRegionNick() != 'kareliya' || isLpuAdmin() == false))))) {
			this.findById('PolEdW_OMSSprTerr_id').disable();
			this.findById('PolEdW_PolisType').disable();
			this.findById('PolEdW_PolisFormType_id').disable();
			this.findById('PolEdW_Polis_Ser').disable();
			this.findById('PolEdW_Polis_Num').disable();
			this.findById('PolEdW_Federal_Num').disable();
			this.findById('PolEdW_OrgSMO_id').disable();
			this.findById('PolEdW_Polis_begDate').disable();
			this.findById('PolEdW_Polis_endDate').setDisabled(this.action=='view');
			this.buttons[0].setDisabled(this.action=='view');
			fullSprLoad = true; // загрузить полные справочники
		} else {
			this.findById('PolEdW_OMSSprTerr_id').enable();
			this.findById('PolEdW_PolisType').enable();
			this.findById('PolEdW_PolisFormType_id').enable();
			this.findById('PolEdW_Polis_Ser').enable();
			this.findById('PolEdW_Polis_Num').enable();
			this.findById('PolEdW_Federal_Num').enable();
			this.findById('PolEdW_OrgSMO_id').enable();
			this.findById('PolEdW_Polis_begDate').enable();
			this.findById('PolEdW_Polis_endDate').enable();
			this.buttons[0].enable();
			base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
		}
		
		
		var OMSSprTerrcombo = base_form.findField('OMSSprTerr_id');
		var OrgSMOCombo = base_form.findField('OrgSMO_id');
		var OrgSMO_id = OrgSMOCombo.getValue();

		if ( getRegionNick() == 'astra' ) {
			if (!isSuperAdmin() && !(this.fields.BDZ_id == null)) {
				combowhere = "where KLRgn_id <> " + cur_reg;
			}
		}
		
		if (fullSprLoad) {
			combowhere = "";
			OrgSMOCombo.getStore().load({	
				callback: function() 
				{
					OrgSMOCombo.setValue(OrgSMO_id);
				}
			});
		}
		
		OMSSprTerrcombo.getStore().load({	
			params: {where: combowhere},
			callback: function() 
			{
				OMSSprTerrcombo.setValue(OMSSprTerrcombo.getValue());
				OMSSprTerrcombo.fireEvent('change', OMSSprTerrcombo, OMSSprTerrcombo.getValue());
				OrgSMOCombo.setValue(OrgSMO_id);
				
		win.toFront();
			}
		});
		var beg_dt = base_form.findField('Polis_begDate').getValue();
		if(beg_dt){
			base_form.findField('Polis_endDate').setMinValue(beg_dt);
		}
		log(this);
		//OMSSprTerrcombo.focus(true, 100);
							
	},
	changeSerNumLenght: function() {
		var base_form = this.findById('polis_edit_form').getForm();

		if ( getRegionNick() == 'ufa' && base_form.findField('PolisType_id').getValue() == 4 )
		{
			base_form.findField('Polis_Num').minLength = 0;
			base_form.findField('Polis_Num').maxLength = 16;
			//return;
		}
		else
		{
			base_form.findField('Polis_Num').clearInvalid();
		}

		var PolisType_id = base_form.findField('PolisType_id').getValue();

		var polis_region = base_form.findField('OMSSprTerr_id').getFieldValue('KLRgn_id');
		var system_region = getRegionNumber();

		var combo = base_form.findField('OMSSprTerr_id');
		var number = combo.getValue();
		var idx = -1;
		var findIndex = 0;

		combo.getStore().findBy(function(r) {
			if ( r.get('OMSSprTerr_id') == number ) {
				idx = findIndex;
				return true;
			}

			findIndex++;
		});
		var KLRgn_id = null;
		if (idx >= 0) {
			KLRgn_id = combo.getStore().getAt(idx).get('KLRgn_id');
		}


		if (PolisType_id == 4) {
			base_form.findField('Polis_Ser').minLength = 0;
			base_form.findField('Polis_Num').minLength = 0;
			base_form.findField('Polis_Num').maskRe =/\d/;
			base_form.findField('Polis_Ser').setValue('');
		} else {
			if ( PolisType_id == 3 ) {
				if(getRegionNick()=='perm'){
					base_form.findField('Polis_Num').minLength = 5;
					base_form.findField('Polis_Num').maxLength = 99;
				}else{
					base_form.findField('Polis_Num').minLength = getRegionNick().inlist([ 'astra' ]) ? 6 : 9;
					base_form.findField('Polis_Num').maxLength = 9;
					if ( base_form.findField('PolisType_id').getValue() == 2&&getRegionNick()=='astra' ) {
						base_form.findField('Polis_Num').maskRe = /[a-zA-Zа-яА-ЯёЁ\d\/]/;
					}else{
						base_form.findField('Polis_Num').maskRe =/\d/;
						//base_form.findField('Polis_Num').setValue('');
					}
				}
				base_form.findField('Polis_Ser').minLength =  0;
				base_form.findField('Polis_Ser').maxLength =  undefined;
			}
			else {
				base_form.findField('Polis_Ser').minLength = 0;
				base_form.findField('Polis_Ser').maxLength = undefined;
				base_form.findField('Polis_Num').minLength = 0;
				base_form.findField('Polis_Num').maxLength = 18;

				if ( PolisType_id == 1 && getRegionNick() != 'kz' && polis_region != system_region ) {
					base_form.findField('Polis_Num').maskRe = /[0-9\.\/]/;
				}
				else {
					base_form.findField('Polis_Num').maskRe = /\d/;
				}
			}
		}

		if ( getRegionNick() == 'ufa' && KLRgn_id == 2 )
		{
			if ( base_form.findField('PolisType_id').getValue() != 3 ) {
				base_form.findField('Polis_Num').minLength = 16;
				base_form.findField('Polis_Num').maxLength = 16;
			}
			else {
				base_form.findField('Polis_Num').minLength = 9;
				base_form.findField('Polis_Num').maxLength = 9;
				base_form.findField('Polis_Num').clearInvalid();
			}
		}
	},
	show: function() {
		sw.Promed.swPolisEditWindow.superclass.show.apply(this, arguments);
		
		this.action = 'view';
		this.fields = null;
		this.ignoreOnClose = false;
		this.onWinClose = Ext.emptyFn;
		this.readOnly = false;
		this.returnFunc = Ext.emptyFn;

		if ( arguments[0] )
		{
			if ( arguments[0].callback ) {
				this.returnFunc = arguments[0].callback;
			}
			if ( arguments[0].readOnly ) {
				this.readOnly = arguments[0].readOnly;
			}
			if ( arguments[0].fields ) {
				this.fields = arguments[0].fields;
			}
			if ( arguments[0].action ) {
				this.action = arguments[0].action;
			}
			if ( arguments[0].ignoreOnClose ) {
				this.ignoreOnClose = arguments[0].ignoreOnClose;
			}
			if ( arguments[0].onClose ) {
				this.onWinClose = arguments[0].onClose;
			}
		}

		switch ( this.action ) {
			case 'view':
				this.setTitle(lang['polis_prosmotr']);
			break;

			default:
				this.setTitle(lang['polis_redaktirovanie']);
			break;
		}

		var base_form = this.findById('polis_edit_form').getForm();
		base_form.findField('Polis_endDate').setMinValue(undefined);
		if ( arguments[0].clearEditForm ) {
			// очистка полей формы
			base_form.findField('OMSSprTerr_id').clearValue();
			base_form.findField('PolisType_id').clearValue();
			base_form.findField('PolisFormType_id').setValue('');
			base_form.findField('Polis_Ser').setValue('');
			base_form.findField('Polis_Num').setValue('');
			base_form.findField('Federal_Num').setValue('');
			base_form.findField('OrgSMO_id').clearValue();
			base_form.findField('Polis_begDate').setValue('');
			base_form.findField('Polis_endDate').setValue('');
		}
		
		// если это редактирование с загрузкой данных, то загружаем данные
		if ( this.action && (this.action == 'edit_with_load'||this.action=='view') )
		{
			var loadMask = new Ext.LoadMask(
				Ext.get('polis_edit_window'),
				{ msg: "Подождите, идет загрузка...", removeMask: true }
			);
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=Person&m=loadPolisData',
				params: {Person_id: this.fields.Person_id,PersonEvn_id:this.fields.PersonEvn_id,Server_id:this.fields.Server_id},
				callback: function(options, success, response) {
					loadMask.hide();
					if ( response && response.responseText )
					{
						var resp = Ext.util.JSON.decode(response.responseText);
						if ( resp && resp[0] )
						{
							this.fields = resp[0];
							this.onShowActions();
						}
					}
				}.createDelegate(this)
			});
		}
		else
		{
			this.onShowActions();
		}
	},
	doSave: function(options) {
		var base_form = this.findById('polis_edit_form').getForm();
		if ( !base_form.isValid() )
		{
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('OMSSprTerr_id').focus(true, 100);
				}
			});
			return false;
		}
		if (this.findById('PolEdW_PolisType').getValue() != '' && Number(this.findById('PolEdW_PolisType').getValue()) == 4){
			var polis_num = String(this.findById('PolEdW_Federal_Num').getValue());
			if (!checkEdNumFedSignature(polis_num) && getRegionNick() != 'kz' && !options.ignoreENPValidationControl) {
				switch (getGlobalOptions().enp_validation_control) {
					case 'warning':		// Выводим предупреждение с возможностью продолжения
						sw.swMsg.show({
							buttons: sw.swMsg.YESNO,
							fn: function(buttonId, text, obj) {
								if ('yes' == buttonId) {
									var options = {};
									options.ignoreENPValidationControl = 1;
									this.doSave(options);
								} else {
									base_form.findField('PolEdW_Federal_Num').focus(true, 100);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: "Единый номер полиса не соответствует формату. Продолжить сохранение?",
							title: lang['vopros']
						});
						return false;
					case 'deny':		// Выводим сообщение об ошибке
						sw.swMsg.show({
							title: "Проверка номера полиса",
							msg: "Единый номер полиса не соответствует формату",
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING,
							fn: function () {
								base_form.findField('PolEdW_Federal_Num').focus(true, 100);
							}
						});
						return false;
				}
			}
		}
		var values = base_form.getValues();
		if (base_form.findField('OMSSprTerr_id').disabled) {
			values.OMSSprTerr_id = base_form.findField('OMSSprTerr_id').getValue();
		}
		if (base_form.findField('PolisType_id').disabled) {
			values.PolisType_id = base_form.findField('PolisType_id').getValue();
		}
		if (base_form.findField('PolisFormType_id').disabled) {
			values.PolisFormType_id = base_form.findField('PolisFormType_id').getValue();
		}
		if (base_form.findField('Polis_Ser').disabled) {
			values.Polis_Ser = base_form.findField('Polis_Ser').getValue();
		}
		if (base_form.findField('Polis_Num').disabled) {
			values.Polis_Num = base_form.findField('Polis_Num').getValue();
		}
		if (base_form.findField('Federal_Num').disabled) {
			values.Federal_Num = base_form.findField('Federal_Num').getValue();
		}
		if (base_form.findField('OrgSMO_id').disabled) {
			values.OrgSMO_id = base_form.findField('OrgSMO_id').getValue();
		}
		if (base_form.findField('Polis_begDate').disabled && base_form.findField('Polis_begDate').getValue()) {
			values.Polis_begDate = Ext.util.Format.date(base_form.findField('Polis_begDate').getValue(), 'd.m.Y');
		}
		if (base_form.findField('Polis_endDate').disabled && base_form.findField('Polis_endDate').getValue()) {
			values.Polis_endDate = Ext.util.Format.date(base_form.findField('Polis_endDate').getValue(), 'd.m.Y');
		}
		if (base_form.findField('PolisFormType_id').disabled) {
			values.PolisFormType_id = base_form.findField('PolisFormType_id').getValue();
		}

		values.Polis_PolisString = base_form.findField('OMSSprTerr_id').getRawValue() + ' ' + base_form.findField('OrgSMO_id').getRawValue() + ' ' + base_form.findField('Polis_Ser').getValue()  + ' ' + base_form.findField('Polis_Num').getValue();
		
		if (base_form.findField('Polis_begDate').getValue()) {
			values.Polis_PolisString = values.Polis_PolisString + ' Открыт: ' + Ext.util.Format.date(base_form.findField('Polis_begDate').getValue(), 'd.m.Y');
		}
		if (base_form.findField('Polis_endDate').getValue()) {
			values.Polis_PolisString = values.Polis_PolisString + ' Закрыт: ' + Ext.util.Format.date(base_form.findField('Polis_endDate').getValue(), 'd.m.Y');
		}
		
		Ext.callback(this.returnFunc, this, [values]);
		if (this.action == 'add') {
			if ( this.ignoreOnClose === true )
				this.onWinClose = function() {};
			this.hide();
		}
	},
	initComponent: function() {
		var win = this;
		
    	Ext.apply(this, {
 			items: [
				new Ext.form.FormPanel({
					frame: true,
            		autoHeight: true,
            		labelAlign: 'right',
					id: 'polis_edit_form',
					labelWidth: 95,
					buttonAlign: 'left',
					bodyStyle:'padding: 5px',
					items: [{
						id: 'PolEdW_OMSSprTerr_id',
						codeField: 'OMSSprTerr_Code',
						editable: true,
						allowBlank: false,
						forceSelection: true,
						hiddenName: 'OMSSprTerr_id',
						listeners: {
							'change': function(combo) {
								if ( !combo.getValue() ) {
									return false;
								}

								var base_form = this.findById('polis_edit_form').getForm();

								this.changeSerNumLenght();

								var OrgSMOCombo = base_form.findField('OrgSMO_id');
								var OrgSMO_id = OrgSMOCombo.getValue();


								OrgSMOCombo.clearValue();
								OrgSMOCombo.lastQuery = '';

								// var idx = combo.getStore().find('OMSSprTerr_id', combo.getValue());
								var number = combo.getValue();
								var idx = -1;
								var findIndex = 0;

								combo.getStore().findBy(function(r) {
									if ( r.get('OMSSprTerr_id') == number ) {
										idx = findIndex;
										return true;
									}

									findIndex++;
								});

								if ( idx >= 0 ) {
									var code = combo.getStore().getAt(idx).get('OMSSprTerr_Code');
									var klrgn_id = combo.getStore().getAt(idx).get('KLRgn_id');

									if ( code <= 61 )  {
										base_form.findField('Polis_Ser').disableTransPlug = false;
										/*if ( !(getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') ) {
											base_form.findField('Polis_Ser').setAllowBlank(false);
										}*/
									}
									else {
										base_form.findField('Polis_Ser').disableTransPlug = true;
										//base_form.findField('Polis_Ser').setAllowBlank(true);
									}

									var cur_reg = getGlobalOptions().region ? getGlobalOptions().region['number'] : 59;
													//if ( /*( code < 100 && cur_reg == 59 ) ||*/ ( cur_reg == klrgn_id ) )
									if ( cur_reg == 59 &&  cur_reg == klrgn_id )
									{
										OrgSMOCombo.baseFilterFn = function(record) {
											if ( /.+/.test(record.get('OrgSMO_RegNomC')) && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMO_id) )
												return true;
											else
												return false;
										}
										OrgSMOCombo.getStore().filterBy(function(record) {
											if ( /.+/.test(record.get('OrgSMO_RegNomC')) && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMO_id) )
												return true;
											else
												return false;
										});
									}
									else {
										OrgSMOCombo.baseFilterFn = function(record) {
											if ( klrgn_id == record.get('KLRgn_id') && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMO_id)  )
												return true;
											else
												return false;
										}
										OrgSMOCombo.getStore().filterBy(function(record) {
											if ( klrgn_id == record.get('KLRgn_id') && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMO_id)  )
												return true;
											else
												return false;
										});
										/*OrgSMOCombo.baseFilterFn = null;
										OrgSMOCombo.getStore().filter('OrgSMO_RegNomC', '');*/
									}
									if ( cur_reg == 19 ) {
										base_form.findField('Polis_Ser').disableTransPlug = true;
										if ( code == 1 ) {
											base_form.findField('Polis_Ser').maskRe = /^[smSMа-яА-ЯёЁ\d\s\-]+$/;
										} else {
											base_form.findField('Polis_Ser').maskRe = /^[a-zA-Zа-яА-ЯёЁ\d\s\-]+$/;
										}
										if(!Ext.isEmpty(base_form.findField('Polis_Ser').getValue()) && !base_form.findField('Polis_Ser').maskRe.exec(base_form.findField('Polis_Ser').getValue())) {
											base_form.findField('Polis_Ser').setValue(null);
										}
									}
								}
								//base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
							}.createDelegate(this)
						},
						onTrigger2Click: function() {
							this.findById('person_edit_form').getForm().findField('OMSSprTerr_id').clearValue();
						}.createDelegate(this),
						tabIndex: TABINDEX_POLEDW + 1,
						width: 300,
						listWidth: 400,
						xtype: 'swomssprterrcombo'
					}, {
						id: 'PolEdW_PolisType',
						allowBlank: false,
						comboSubject: 'PolisType',
						fieldLabel: lang['tip'],
						listeners: {
							'select': function(combo, record, index) {
								this.findById('polis_edit_form').getForm().findField('OrgSMO_id').clearValue();
/*
								if ( 1 == record.get('PolisType_Code') ) {
									this.findById('person_edit_form').getForm().findField('OrgSMO_id').getStore().filterBy(function(rec) {
										if ( rec.get('OrgSMO_RegNomC') == '' && rec.get('OrgSMO_RegNomN') == '' ) {
											return false;
										}
										else {
											return true;
										}
									});
								}
								else {
									this.findById('person_edit_form').getForm().findField('OrgSMO_id').getStore().clearFilter();
								}
*/
							}.createDelegate(this),
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('polis_edit_form').getForm();

								base_form.findField('Polis_begDate').setAllowBlank(false);

								this.changeSerNumLenght();

								if (newValue == 4) {
									base_form.findField('Federal_Num').setAllowBlank(false);
									base_form.findField('Polis_Num').setAllowBlank(true);
									base_form.findField('Polis_Ser').setAllowBlank(true);
									base_form.findField('Polis_Num').setValue('');
									base_form.findField('Polis_Num').disable();
									base_form.findField('Polis_Ser').setValue('');
									base_form.findField('Polis_Ser').disable();
								} else {
									if ( newValue == 2&&getRegionNick()=='astra' ) {
										base_form.findField('Polis_Num').maskRe = /[a-zA-Zа-яА-ЯёЁ\d\/]/;
									}else{
										base_form.findField('Polis_Num').maskRe =/\d/;
										if(oldValue==2)
										base_form.findField('Polis_Num').setValue('');
									}
									base_form.findField('Federal_Num').setAllowBlank(true);
									base_form.findField('Polis_Num').setAllowBlank(false);
									base_form.findField('Polis_Ser').setAllowBlank(true);
									if(getRegionNick()=='kareliya'){
										base_form.findField('Polis_Ser').setDisabled(newValue==3);
									}else{
										base_form.findField('Polis_Ser').enable();
									}
									base_form.findField('Polis_Num').enable();
								}
							}.createDelegate(this)
							
						},
						tabIndex: TABINDEX_POLEDW + 2,
						validateOnBlur: false,
						validationEvent: false,
						width: 180,
						xtype: 'swcommonsprcombo'
					},{
						id: 'PolEdW_PolisFormType_id',
						tabIndex: TABINDEX_PEF + 12,
						fieldLabel: lang['forma_polisa'],
						hiddenName:'PolisFormType_id',
						width: 190,
						xtype: 'swpolisformtypecombo'
					}, {
						id: 'PolEdW_Polis_Ser',
						//allowBlank: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'),
						fieldLabel: lang['seriya'],
						maxLength: 10,
						name: 'Polis_Ser',
						plugins: [ new Ext.ux.translit(true, true) ],
						maskRe: /./,
						tabIndex: TABINDEX_POLEDW + 3,
						width: 100,
						xtype: 'textfield'
					}, {
						id: 'PolEdW_Polis_Num',
						allowBlank: false,
						xtype: 'textfield',
						maskRe: /\d/,
						allowNegative: false,
						allowDecimals: false,
						maxLength:  (getRegionNick() == 'ufa') ? 16 : 18,
						minLength: (getRegionNick() == 'ufa') ? 16 : 0,
						width: 160,
						fieldLabel: lang['nomer'],
						name: 'Polis_Num',
						tabIndex: TABINDEX_POLEDW + 4
					},{
						name:'FederalServer_id',
						xtype:'hidden'
					},{
						name:'Polis_Guid',
						xtype:'hidden'
					},{
						name:'FederalEvn_id',
						xtype:'hidden'
					},{
						xtype: 'textfield',
						id: 'PolEdW_Federal_Num',
						maskRe: /\d/,
						maxLength: 16,
						minLength: 16,
						//autoCreate: {tag: "input", type: "text", size: "16", maxLength: "16", autocomplete: "off"},
						width: 160,
						fieldLabel: lang['ed_nomer'],
						name: 'Federal_Num',
						tabIndex: TABINDEX_PEF + 15
					},{
						id: 'PolEdW_OrgSMO_id',
						tabIndex: TABINDEX_POLEDW + 4,
						allowBlank: false,
						xtype: 'sworgsmocombo',
						minChars: 1,
						queryDelay: 1,
						hiddenName: 'OrgSMO_id',
						lastQuery: '',
						listWidth: '300',
						onTrigger2Click: function() {
							if ( this.disabled )
								return;

							var base_form = win.findById('polis_edit_form').getForm();
							var combo = this;
							var idx = base_form.findField('OMSSprTerr_id').getStore().findBy(function(rec) { return rec.get('OMSSprTerr_id') == base_form.findField('OMSSprTerr_id').getValue(); });

							if ( idx >= 0 ) {
								var omsterrcode = base_form.findField('OMSSprTerr_id').getStore().getAt(idx).get('OMSSprTerr_Code');
								var klrgn_id = base_form.findField('OMSSprTerr_id').getStore().getAt(idx).get('KLRgn_id');
							} else {
								var omsterrcode = -1;
								var klrgn_id = -1;
							}

							getWnd('swOrgSearchWindow').show({
								onSelect: function(orgData) {
									if ( orgData.Org_id > 0 )
									{
										var index = combo.getStore().findBy(function(rec) { return rec.get('Org_id') == orgData.Org_id; });
										if (index >= 0) {
											var record = combo.getStore().getAt(index);
											combo.setValue(record.get('OrgSMO_id'));
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
										}
									}

									getWnd('swOrgSearchWindow').hide();
								},
								onClose: function() {combo.focus(true, 200)},
								object: 'smo',
								KLRgn_id: klrgn_id,
								OMSSprTerr_Code: omsterrcode
							});
						},
						enableKeyEvents: true,
						forceSelection: false,
						typeAhead: true,
						typeAheadDelay: 1,
						listeners: {
							'blur': function(combo) {
								if (combo.getRawValue()=='')
									combo.clearValue();

								if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 )
									combo.clearValue();
							},
							'keydown': function( inp, e ) {
								if ( e.F4 == e.getKey() )
								{
									if ( inp.disabled )
										return;

									if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
									else
										e.browserEvent.cancelBubble = true;

									if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
									else
										e.browserEvent.returnValue = false;

									e.browserEvent.returnValue = false;
									e.returnValue = false;

									if ( Ext.isIE )
									{
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									inp.onTrigger2Click();
									inp.collapse();

									return false;
								}
							},
							'keyup': function(inp, e) {
								if ( e.F4 == e.getKey() )
								{
									if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
									else
										e.browserEvent.cancelBubble = true;

									if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
									else
										e.browserEvent.returnValue = false;

									e.browserEvent.returnValue = false;
									e.returnValue = false;

									if ( Ext.isIE )
									{
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									return false;
								}
							}
						}
					}, {
						id: 'PolEdW_Polis_begDate',
						allowBlank: false,
						fieldLabel: lang['data_vyidachi'],
						format: 'd.m.Y',
						name: 'Polis_begDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_POLEDW + 5,
						xtype: 'swdatefield',
						listeners: {
							'change': function(combo) {
								var base_form = win.findById('polis_edit_form').getForm();
									base_form.findField('Polis_endDate').setMinValue(combo.getValue());
								
									
							}
						}
					}, {
						id: 'PolEdW_Polis_endDate',
						allowBlank: true,
						fieldLabel: lang['data_zakryitiya'],
						format: 'd.m.Y',
						name: 'Polis_endDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_POLEDW + 6,
						xtype: 'swdatefield',
						listeners: {
							'change': function(combo) {
								var base_form = win.findById('polis_edit_form').getForm();
								var beg_dt = base_form.findField('Polis_begDate').getValue();
								if(beg_dt){
									base_form.findField('Polis_endDate').setMinValue(beg_dt);
								}
							}
						}
					}],
					enableKeyEvents: true,
				    keys: [{
				    	alt: true,
				        fn: function(inp, e) {
				        	Ext.getCmp('polis_edit_form').ownerCt.hide();
				        },
				        key: [ Ext.EventObject.J ],
				        stopEvent: true
				    }, {
				    	alt: true,
				        fn: function(inp, e) {
				        	this.buttons[0].handler();
				        },
				        key: [ Ext.EventObject.C ],
				        stopEvent: true
				    }]
				})
				
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_POLEDW + 7,
					iconCls: 'ok16',
					handler: function() {
						this.doSave({});
					}.createDelegate(this)
				},
				{
					text: '-'
				},
					HelpButton(this),
				{
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_POLEDW + 8,
					iconCls: 'cancel16',
					handler: function() {
						this.hide()
					}.createDelegate(this)
				}
			]
		});
		sw.Promed.swPolisEditWindow.superclass.initComponent.apply(this, arguments);
	}
});