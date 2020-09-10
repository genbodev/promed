/**
* swEvnAggEditWindow - окно редактирования/добавления осложнения.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.002-16.11.2009
* @comment      Префикс для id компонентов EAEF (EvnAggEditForm)
*               tabIndex: 3001
*
*
* @input data: action - действие (add, edit, view)
*/

sw.Promed.swEvnAggEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	callback: Ext.emptyFn,
	usluga_setDate:null,
	usluga_setTime:null,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var evn_agg_set_time = base_form.findField('EvnAgg_setTime').getValue();

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});
		loadMask.show();
		if(!this.checkTime()){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					loadMask.hide();
					this.formStatus = 'edit';
					base_form.findField('EvnAgg_setTime').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['vremya_oslojneniya_ne_mojet_byit_ranshe_datyi-vremeni_uslugi'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
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
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.EvnAgg_id > 0 ) {
					base_form.findField('EvnAgg_id').setValue(action.result.EvnAgg_id);

					var data = new Object();

					var agg_type_id = base_form.findField('AggType_id').getValue();
					var agg_type_name = '';
					var agg_when_id = base_form.findField('AggWhen_id').getValue();
					var agg_when_name = '';
					var evn_agg_set_time = base_form.findField('EvnAgg_setTime').getValue();
					var record = null;

					record = base_form.findField('AggType_id').getStore().getById(agg_type_id);
					if ( record ) {
						agg_type_name = record.get('AggType_Name');
					}

					record = base_form.findField('AggWhen_id').getStore().getById(agg_when_id);
					if ( record ) {
						agg_when_name = record.get('AggWhen_Name');
					}

					data.EvnAggData = [{
						'accessType': 'edit',
						'EvnAgg_id': base_form.findField('EvnAgg_id').getValue(),
						'EvnAgg_pid': base_form.findField('EvnAgg_pid').getValue(),
						'Person_id': base_form.findField('Person_id').getValue(),
						'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
						'Server_id': base_form.findField('Server_id').getValue(),
						'AggType_id': agg_type_id,
						'AggType_Name': agg_type_name,
						'AggWhen_id': agg_when_id,
						'AggWhen_Name': agg_when_name,
						'EvnAgg_setDate': base_form.findField('EvnAgg_setDate').getValue(),
						'EvnAgg_setTime': evn_agg_set_time
					}];

					this.callback(data);
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();

		if ( enable ) {
			base_form.findField('EvnAgg_setDate').enable();
			base_form.findField('EvnAgg_setTime').enable();
			base_form.findField('AggType_id').enable();
			base_form.findField('AggWhen_id').enable();
			this.buttons[0].show();
		}
		else {
			base_form.findField('EvnAgg_setDate').disable();
			base_form.findField('EvnAgg_setTime').disable();
			base_form.findField('AggType_id').disable();
			base_form.findField('AggWhen_id').disable();
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'EvnAggEditWindow',
	setDateTime:function (params) {
		var win = this;
	
        if ( !params || typeof params != 'object' || !params.dateField || typeof params.dateField != 'object' || !params.windowId ) {
            return false;
        }

        if ( params.loadMask ) {
            var loadMask = new Ext.LoadMask(Ext.get(params.windowId), {msg: "Получение текущих даты и времени"});
            loadMask.show();
        }

        if ( !params.addMaxDateDays ) {
            params.addMaxDateDays = 0;
        }

        if ( !params.addMinDateDays ) {
            params.addMinDateDays = 0;
        }
        var date;
        if (params.dateField.format == 'd.m.Y H:i') {
            date = Date.parseDate(win.usluga_setDate + ' ' + win.usluga_setTime, params.dateField.format);
        } else {
            date = Date.parseDate(win.usluga_setDate, 'd.m.Y');
        }
        if ( !date ) {
            return false;
        }
        var time = Date.parseDate(win.usluga_setTime,'H:i');

        if ( params.setTime && params.timeField && typeof params.timeField == 'object' && params.timeField.setRawValue ) {
            params.timeField.setRawValue(Ext.util.Format.date(time,'H:i'));
        }

        if ( params.setDate && !params.dateField.getValue() ) {
            params.dateField.setValue(date);
        }

        if ( params.setDateMaxValue ) {
            params.dateField.setMaxValue(date.add(Date.DAY, params.addMaxDateDays));
            if ( params.setTimeMaxValue ) {
                params.timeField.setMaxValue(time.add('H:i', 0),params.dateField.getValue());
            }
            // params.dateField.setMaxValue(date);
        }else{
            params.dateField.setMaxValue(undefined);
            params.timeField.setMaxValue(undefined,undefined);
        }

        if ( params.setDateMinValue ) {
            params.dateField.setMinValue(date.add(Date.DAY, params.addMinDateDays));
            // params.dateField.setMinValue(date);
        }

        if ( params.callback && typeof params.callback == 'function' ) {
            params.callback(date);
        }
        return true;
    },
		
		


	initComponent: function() {
		var win = this;
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			buttonAlign: 'left',
			frame: false,
			id: 'EvnAggEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'accessType'},
				{name: 'EvnAgg_id'},
				{name: 'EvnAgg_pid'},
				{name: 'Person_id'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'AggType_id'},
				{name: 'AggWhen_id'},
				{name: 'EvnAgg_setDate'},
				{name: 'EvnAgg_setTime'}
			]),
			url: '/?c=EvnAgg&m=saveEvnAgg',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnAgg_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnAgg_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: 0,
				xtype: 'hidden'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: lang['data_oslojneniya'],
						format: 'd.m.Y',
						listeners: {
							'blur': function( field, The, eOpts ) {
								var date=Date.parse('2019-12-01');								
								var base_form = win.FormPanel.getForm();
								if (field.getValue()<date){
									base_form.findField("AggWhen_id").getStore().filterBy(
										function(record){
										  return record.get("AggWhen_Code")!=3;
										}
									);
								}else{
									base_form.findField("AggWhen_id").getStore().filterBy(
										function(record){
										  return true;
										}
									);
								}
							},
							'change': function(field, newValue, oldValue) {
								blockedDateAfterPersonDeath('personpanelid', 'EAEF_PersonInformationFrame', field, newValue, oldValue);
							},
							'keydown': function (inp, e) {
								if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus(true);
								}
							}.createDelegate(this)
						},
						name: 'EvnAgg_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: 3001,
						width: 100,
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
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
						name: 'EvnAgg_setTime',
						onTriggerClick: function() {
							var base_form = this.FormPanel.getForm();
							var time_field = base_form.findField('EvnAgg_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							win.setDateTime({
								dateField: base_form.findField('EvnAgg_setDate'),
								loadMask: false,
								setDate: false,
								setTimeMaxValue: (base_form.findField('AggWhen_id')==1),
								setDateMaxValue: (base_form.findField('AggWhen_id')==1),
								setDateMinValue: true,
								setTime: true,
								timeField: base_form.findField('EvnAgg_setTime'),
								windowId: win.id
							});
						}.createDelegate(this),
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: 3002,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: false,
				comboSubject: 'AggType',
				fieldLabel: lang['vid_oslojneniya'],
				hiddenName: 'AggType_id',
				lastQuery: '',
				tabIndex: 3003,
				width: 450,
				orderBy: (getRegionNick() == 'kareliya' ? 'Name' : 'Code'),
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'AggWhen',
				fieldLabel: lang['kontekst_oslojneniya'],
				hidddenName: 'AggWhen_id',
				lastQuery: '',
				tabIndex: 3004,
				width: 450,
				listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = win.FormPanel.getForm();
								switch(newValue){
									case 1:
									win.setDateTime({
										dateField: base_form.findField('EvnAgg_setDate'),
										loadMask: false,
										setDate: true,
										setTimeMaxValue:true,
										setDateMaxValue: true,
										setDateMinValue: true,
										setTime: true,
										timeField: base_form.findField('EvnAgg_setTime'),
										windowId: win.id
									});
										break;
									case 2:
										win.setDateTime({
										dateField: base_form.findField('EvnAgg_setDate'),
										loadMask: false,
										setDate: false,
										setTimeMaxValue: null,
										setDateMaxValue: undefined,
										setDateMinValue: true,
										setTime: false,
										timeField: base_form.findField('EvnAgg_setTime'),
										windowId: win.id
									});
										break;
									
								}
							}
				},
				xtype: 'swcommonsprcombo'
			}]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EAEF_PersonInformationFrame'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					this.FormPanel.getForm().findField('AggWhen_id').focus(true);
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: 3005,
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
						this.FormPanel.getForm().findField('EvnAgg_setDate').focus(true);
					}
				}.createDelegate(this),
				tabIndex: 3006,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			]
		});

		sw.Promed.swEvnAggEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnAggEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					if ( current_window.action != 'view' ) {
						current_window.doSave();
					}
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
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	checkTime:function(){
		var win = this;
		var base_form = this.FormPanel.getForm();
		var cur_time = Date.parseDate(base_form.findField('EvnAgg_setDate').getRawValue()
						+' '+base_form.findField('EvnAgg_setTime').getRawValue(),'d.m.Y H:i');
					log([win.minDatecur_time])
		if(win.minDate>cur_time){
			return false;
		}
		return true;
	},
	show: function() {
		sw.Promed.swEvnAggEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();

		this.center();
		base_form.reset();

		this.action = null;
		this.minDate = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.usluga_setTime=null;
		this.usluga_setDate=null;
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
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
		if(arguments[0].formParams.Evn_setTime){
			this.usluga_setTime=arguments[0].formParams.Evn_setTime;
		}
		if(arguments[0].formParams.Evn_setDate){
			this.usluga_setDate=arguments[0].formParams.Evn_setDate;
		}
		if ( arguments[0].minDate ) {
			this.minDate = arguments[0].minDate;
		}
		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnAgg_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EAEF_PersonInformationFrame', field);
			}
		});

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		var win = this;
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_EAGGADD);
				this.enableEdit(true);
				this.setDateTime({
					dateField: base_form.findField('EvnAgg_setDate'),
					loadMask: false,
					setDate: true,
					setTimeMaxValue:false,
					setDateMaxValue: false,
					setDateMinValue: true,
					setTime: false,
					timeField: base_form.findField('EvnAgg_setTime'),
					windowId: this.id
				});
				
				loadMask.hide();

				//base_form.clearInvalid();

				base_form.findField('EvnAgg_setDate').focus(false, 250);
			break;

			case 'edit':
			case 'view':
				var evn_agg_id = base_form.findField('EvnAgg_id').getValue();

				if ( !evn_agg_id ) {
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
						EvnAgg_id: evn_agg_id
					},
					success: function(result_form, action) {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_POL_EAGGEDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_POL_EAGGVIEW);
							this.enableEdit(false);
						}

						if ( this.action == 'edit' ) {
							this.setDateTime({
								dateField: base_form.findField('EvnAgg_setDate'),
								loadMask: false,
								setDate: false,
								setTimeMaxValue: (base_form.findField('AggWhen_id')==1),
								setDateMaxValue: (base_form.findField('AggWhen_id')==1),
								setDateMinValue: true,
								setTime: false,
								timeField: base_form.findField('EvnAgg_setTime'),
								windowId: this.id
							});
						}

						loadMask.hide();

						//base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnAgg_setDate').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnAgg&m=loadEvnAggEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 650
});