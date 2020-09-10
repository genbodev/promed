/**
* swEvnDiagPSEditWindow - окно редактирования/добавления диагноза в стационаре.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Hospital
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-12.03.2010
* @comment      Префикс для id компонентов EDPSEF (EvnDiagPSEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              type - тип диагноза (hosp, recep)
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*/
/*NO PARSE JSON*/
sw.Promed.swEvnDiagPSEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnDiagPSEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnDiagPSEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		if ( typeof this.diagPSType != 'string' ) {
			return false;
		}
		else if ( this.formStatus == 'save' || this.action == 'view' ) {
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
		var setDate = Date.parseDate(base_form.findField('EvnDiagPS_setDate').getValue().format('d.m.Y')
						+' '+base_form.findField('EvnDiagPS_setTime').getValue(),'d.m.Y H:i');
		if(this.maxDate!=null){
			if(setDate>this.maxDate||setDate<this.minDate){
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], lang['data_i_vremya_ustanovleniya_soputstvuyuschego_oslojneniya_osnovnogo_diagnoza_vyihodyat_za_ramki_perioda_dvijeniya']);
				return false;
			}
		}else{
			if(setDate<this.minDate){
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], lang['soputstvuyuschiy_oslojnenie_osnovnogo_diagnoza_ne_mojet_byit_ustanovleno_ranee_postupleniya_patsienta']);
				return false;
			}
		}
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();
		var diag_code = '';
		var diag_id = base_form.findField('Diag_id').getValue();
		var diag_name = '';
		var diag_set_class_id = base_form.findField('DiagSetClass_id').getValue();
		var diag_set_class_name = '';
		var diag_set_phase_id = base_form.findField('DiagSetPhase_id').getValue();
		var diag_set_phase_name = '';
		var index;
		var params = new Object();
		var record;

		index = base_form.findField('Diag_id').getStore().findBy(function(rec) {
			if ( rec.get('Diag_id') == diag_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			diag_code = base_form.findField('Diag_id').getStore().getAt(index).get('Diag_Code');
			diag_name = base_form.findField('Diag_id').getStore().getAt(index).get('Diag_Name');
		}

		index = base_form.findField('DiagSetClass_id').getStore().findBy(function(rec) {
			if ( rec.get('DiagSetClass_id') == diag_set_class_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			diag_set_class_name = base_form.findField('DiagSetClass_id').getStore().getAt(index).get('DiagSetClass_Name');
		}

		index = base_form.findField('DiagSetPhase_id').getStore().findBy(function(rec) {
			if ( rec.get('DiagSetPhase_id') == diag_set_phase_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			diag_set_phase_name = base_form.findField('DiagSetPhase_id').getStore().getAt(index).get('DiagSetPhase_Name');
		}

		data.evnDiagPSData = [{
			'Diag_Code': diag_code,
			'Diag_id': diag_id,
			'Diag_Name': diag_name,
			'DiagSetClass_id': diag_set_class_id,
			'DiagSetClass_Name': diag_set_class_name,
			'DiagSetPhase_id': diag_set_phase_id,
			'DiagSetPhase_Name': diag_set_phase_name,
			'DiagSetType_id': base_form.findField('DiagSetType_id').getValue(),
			'EvnDiagPS_PhaseDescr': base_form.findField('EvnDiagPS_PhaseDescr').getValue(),
			'EvnDiagPS_pid': base_form.findField('EvnDiagPS_pid').getValue(),
			'EvnDiagPS_setDate': base_form.findField('EvnDiagPS_setDate').getValue(),
			'EvnDiagPS_setTime': base_form.findField('EvnDiagPS_setTime').getValue(),
			'Person_id': base_form.findField('Person_id').getValue(),
			'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
			'Server_id': base_form.findField('Server_id').getValue()
		}];

		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();

				data.evnDiagPSData[0].EvnDiagPS_id = base_form.findField('EvnDiagPS_id').getValue();

				this.callback(data);
				this.hide();
			break;

			case 'remote':
				params.DiagPSType = this.diagPSType;

				if ( base_form.findField('EvnDiagPS_setDate').disabled ) {
					params.EvnDiagPS_setDate = Ext.util.Format.date(base_form.findField('EvnDiagPS_setDate').getValue(), 'd.m.Y');
				}
				
				//Убираем секунды, если попались
				var strTime = base_form.findField('EvnDiagPS_setTime').getRawValue(),
					arrTime = strTime.split(':');
				if (arrTime.length > 2) {
					strTime = arrTime[0] + ":" + arrTime[1];
					base_form.findField('EvnDiagPS_setTime').setValue(strTime);
				}

				if ( base_form.findField('EvnDiagPS_setTime').disabled ) {
					params.EvnDiagPS_setTime = base_form.findField('EvnDiagPS_setTime').getRawValue();
				}

				if ( base_form.findField('DiagSetClass_id').disabled ) {
					params.DiagSetClass_id = base_form.findField('DiagSetClass_id').getValue();
				}

				if ( base_form.findField('DiagSetType_id').disabled ) {
					params.DiagSetType_id = base_form.findField('DiagSetType_id').getValue();
				}
				
				base_form.submit({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.EvnDiagPS_id > 0 ) {
							base_form.findField('EvnDiagPS_id').setValue(action.result.EvnDiagPS_id);

							data.evnDiagPSData[0].accessType = 'edit';
							data.evnDiagPSData[0].EvnDiagPS_id = base_form.findField('EvnDiagPS_id').getValue();

							this.callback(data);
							this.hide();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();

		if ( enable ) {
			base_form.findField('Diag_id').enable();
			base_form.findField('DiagSetClass_id').enable();
			base_form.findField('DiagSetPhase_id').enable();
			base_form.findField('DiagSetType_id').enable();
			base_form.findField('EvnDiagPS_PhaseDescr').enable();
			base_form.findField('EvnDiagPS_setDate').enable();
			base_form.findField('EvnDiagPS_setTime').enable();

			this.buttons[0].show();
		}
		else {
			base_form.findField('Diag_id').disable();
			base_form.findField('DiagSetClass_id').disable();
			base_form.findField('DiagSetPhase_id').disable();
			base_form.findField('DiagSetType_id').disable();
			base_form.findField('EvnDiagPS_PhaseDescr').disable();
			base_form.findField('EvnDiagPS_setDate').disable();
			base_form.findField('EvnDiagPS_setTime').disable();

			this.buttons[0].hide();
		}
	},
	id: 'EvnDiagPSEditWindow',
	initComponent: function() {
		var curwin = this;
		this.diagPanel = new sw.Promed.swDiagPanel({
			labelWidth: 120,
			bodyStyle: 'padding: 0px;',
			phaseDescrName: 'EvnDiagPS_PhaseDescr',
			diagSetPhaseName: 'DiagSetPhase_id',
			showHSN: true,
			diagField: {
				allowBl: false,
				checkAccessRights: true,
				hiddenName: 'Diag_id',
				listWidth: 580,
				tabIndex: this.tabIndex + 5,
				width: 480,
				xtype: 'swdiagcombo'
			}
		});
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnDiagPSEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{ name: 'EvnDiagPS_id' }
			]),
			url: '/?c=EvnDiag&m=saveEvnDiagPS',

			items: [{
				name: 'EvnDiagPS_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnDiagPS_pid',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: lang['data_ustanovki'],
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								curwin.FormPanel.getForm().findField('Diag_id').setFilterByDate(newValue);
								blockedDateAfterPersonDeath('personpanelid', 'EDPSEF_PersonInformationFrame', field, newValue, oldValue);
								curwin.setMKB();
							}
						},
						name: 'EvnDiagPS_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: this.tabIndex + 1,
						width: 100,
						xtype: 'swdatefield',
						listeners:{
							'change':function (field, newValue, oldValue) {
								curwin.setMKB();
							}
						}
					}]
				}, {
					border: false,
					labelWidth: 50,
					layout: 'form',
					items: [{
						fieldLabel: lang['vremya'],
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnDiagPS_setTime',
						onTriggerClick: function() {
							var base_form = this.FormPanel.getForm();
							var time_field = base_form.findField('EvnDiagPS_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								callback: function() {
									base_form.findField('EvnDiagPS_setDate').fireEvent('change', base_form.findField('EvnDiagPS_setDate'), base_form.findField('EvnDiagPS_setDate').getValue());
								}.createDelegate(this),
								dateField: base_form.findField('EvnDiagPS_setDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: false,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: this.id
							});
						}.createDelegate(this),
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: this.tabIndex + 2,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: false,
				autoLoad: false,
				comboSubject: 'DiagSetClass',
				fieldLabel: lang['vid_diagnoza'],
				hiddenName: 'DiagSetClass_id',
				tabIndex: this.tabIndex + 3,
				typeCode: 'int',
				width: 480,
				listeners: {
					'change': function (c, n, o) {
						if (n == 2) {
							//2 - осложнение основгоо диагноза
							//показываем поля ХСН
							curwin.diagPanel.showHSN = true;
							curwin.diagPanel.refreshHSN();								
						} else {							
							curwin.diagPanel.showHSN = false;
							curwin.diagPanel.hideHSNField();							
						}
					}

				},
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				autoLoad: false,
				comboSubject: 'DiagSetType',
				fieldLabel: lang['tip_diagnoza'],
				hiddenName: 'DiagSetType_id',
				tabIndex: this.tabIndex + 4,
				typeCode: 'int',
				width: 480,
				xtype: 'swcommonsprcombo'
			},
			this.diagPanel
		]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EDPSEF_PersonInformationFrame'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: this.tabIndex + 6,
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
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						if ( !this.FormPanel.getForm().findField('DiagSetClass_id').disabled ) {
							this.FormPanel.getForm().findField('DiagSetClass_id').focus(true);
						}
						else if ( !this.FormPanel.getForm().findField('DiagSetType_id').disabled ) {
							this.FormPanel.getForm().findField('DiagSetType_id').focus(true);
						}
						else {
							this.FormPanel.getForm().findField('Diag_id').focus(true);
						}
					}
				}.createDelegate(this),
				tabIndex: this.tabIndex + 7,
				text: BTN_FRMCANCEL
			}],
			items: [
				 this.PersonInfo
				,this.FormPanel
			],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if ( this.action != 'view' ) {
								this.doSave();
							}
						break;

						case Ext.EventObject.J:
							this.hide();
						break;
					}
				}.createDelegate(this),
				key: [
					 Ext.EventObject.C
					,Ext.EventObject.J
				],
				scope: this,
				stopEvent: true
			}]
		});

		sw.Promed.swEvnDiagPSEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	setMKB: function(){
		var parentWin =this
		var base_form = this.FormPanel.getForm();
		var sex = parentWin.findById('EDPSEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('EDPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'),base_form.findField('EvnDiagPS_setDate').getValue());
		base_form.findField('Diag_id').setMKBFilter(age,sex,false);
	},
	show: function() {
		sw.Promed.swEvnDiagPSEditWindow.superclass.show.apply(this, arguments);
		var curwin = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.center();
		this.maxDate = null;
		this.minDate = null;
		this.action = null;
		this.callback = Ext.emptyFn;
		this.diagPSType = null;
		this.histClin = null;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.diagPanel.personId = arguments[0].Person_id;
		this.diagPanel.hideHSNField();

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'local' ) {
			this.formMode = arguments[0].formMode;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].type ) {
			this.diagPSType = arguments[0].type;
		}
		if ( arguments[0].minDate ) {
			this.minDate = arguments[0].minDate;
		}
		if ( arguments[0].maxDate ) {
			this.maxDate = arguments[0].maxDate;
		}
		if ( arguments[0].type ) {
			this.diagPSType = arguments[0].type;
		}
		if ( arguments[0].histClin ) {
			this.histClin = arguments[0].histClin;
		} else {
			this.histClin = "2,3";
			switch(getRegionNick()) {
				case 'kz':
				case 'ufa':
				case 'msk':
					this.histClin = "2,3,6,7";
					break;
				case 'kareliya':
					this.histClin = "2,3,6";
					break;
			}
		}

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnDiagPS_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EDPSEF_PersonInformationFrame', field);
				curwin.setMKB();
			}
		});

		var diag_combo = base_form.findField('Diag_id');
		diag_combo.filterDate = null;
		var diag_set_class_combo = base_form.findField('DiagSetClass_id');
		var diag_set_type_combo = base_form.findField('DiagSetType_id');

		diag_set_class_combo.getStore().removeAll();
		diag_set_type_combo.getStore().removeAll();

		var diag_set_type_value;

		switch ( this.diagPSType ) {
			case 'die':
				diag_set_type_value = 5;
				
			break;

			case 'hosp':
				diag_set_type_value = 1;
			break;

			case 'recep':
				diag_set_type_value = 2;
			break;

			case 'sect':
				diag_set_type_value = 3;
			break;

			default:
				sw.swMsg.alert(lang['oshibka'], lang['ukazan_nevernyiy_tip_diagnoza'], function() { this.hide(); }.createDelegate(this));
				return false;
			break;
		}

		diag_set_type_combo.getStore().load({
			params: {
				where: 'where DiagSetType_Code = ' + diag_set_type_value
			},
			callback: function(){
				diag_set_type_combo.setValue(diag_set_type_value);
			}
		});

		diag_set_class_combo.getStore().load({
			params: {
				where: 'where DiagSetClass_Code in ('+this.histClin+')'
			},
			callback: function(){
			    if(this.histClin=='1'){
				diag_set_class_combo.setValue("1");
			    }else{
					diag_set_class_combo.setValue(diag_set_class_combo.getValue());
				}
			}
		});

		base_form.setValues(arguments[0].formParams);
		this.diagPanel.hideMsg =  arguments[0].action != 'add';
		this.diagPanel.Diag_Code = arguments[0].formParams.Diag_Code;
		//2 - осложнение диагноза
		this.diagPanel.showHSN = arguments[0].formParams.DiagSetClass_id == 2;
		this.diagPanel.refreshHSN();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_HOSP_EDPSADD);
				this.enableEdit(true);
				if(this.histClin!=1){
				    if ( this.diagPSType != 'die') {
					    //base_form.findField('EvnDiagPS_setDate').disable();
				    }
				}else{
				    base_form.findField('DiagSetClass_id').setValue(1);
				    base_form.findField('DiagSetType_id').setValue(3);
				}
				base_form.findField('DiagSetClass_id').fireEvent('change', base_form.findField('DiagSetClass_id'), base_form.findField('DiagSetClass_id').getValue());
				loadMask.hide();

				diag_combo.filterDate = Ext.util.Format.date(base_form.findField('EvnDiagPS_setDate').getValue(), 'd.m.Y');
				if ( !base_form.findField('EvnDiagPS_setDate').disabled ) {
					base_form.findField('EvnDiagPS_setDate').focus(false, 250);
				}
				else if ( diag_set_class_combo.getValue() ) {
					diag_combo.focus(false, 250);
				}
				else {
					diag_set_class_combo.focus(false, 250);
				}
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(WND_HOSP_EDPSEDIT);
					this.enableEdit(true);

					if ( this.diagPSType != 'die' ) {
						//base_form.findField('EvnDiagPS_setDate').disable();
						//base_form.findField('EvnDiagPS_setTime').disable();
					}
				}
				else {
					this.setTitle(WND_HOSP_EDPSVIEW);
					this.enableEdit(false);
				}

				var diag_id = diag_combo.getValue();

				if ( diag_id != null && diag_id.toString().length > 0 ) {
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_id ) {
									diag_combo.fireEvent('select', diag_combo, record, 0);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				}

				base_form.findField('DiagSetClass_id').fireEvent('change', base_form.findField('DiagSetClass_id'), base_form.findField('DiagSetClass_id').getValue());

				loadMask.hide();

				if ( !base_form.findField('EvnDiagPS_setDate').disabled ) {
					base_form.findField('EvnDiagPS_setDate').focus(false, 250);
				}
				/*
				#22847 кое-кому не нравится, что комбо разворачиваются, когда на них фокус
				переходит при открытии формы
				else if ( !diag_set_class_combo.disabled ) {
					diag_set_class_combo.focus(true, 250);
				}
				else if ( !diag_set_type_combo.disabled ) {
					diag_set_type_combo.focus(true, 250);
				}*/
				else if ( !diag_combo.disabled ) {
					diag_combo.focus(true, 250);
				}
				else {
					this.buttons[this.buttons.length - 1].focus();
				}
			break;

			default:
				this.hide();
			break;
		}

		//base_form.clearInvalid();
	},
	tabIndex: TABINDEX_EDPSEF,
	width: 650
});