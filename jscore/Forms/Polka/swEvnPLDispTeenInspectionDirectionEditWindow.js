/**
* swEvnPLDispTeenInspectionDirectionEditWindow - окно редактирования/добавления Направления на диспансеризацию
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Polka
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Dmitry Vlasenko
* @originalauthor	Ivan Petukhov aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
* @version			01.08.2013
* @comment			Префикс для id компонентов EPLDTIDEW (swEvnPLDispTeenInspectionDirectionEditWindow)
*
*
* Использует: окно редактирования талона по доп. диспансеризации (swEvnPLDispTeenInspectionEditWindow)
*/

sw.Promed.swEvnPLDispTeenInspectionDirectionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	buttons: [{
		handler: function() {
			this.ownerCt.doSave();
		},
		iconCls: 'save16',
		id: 'EPLDTIDEW_SaveButton',
		tabIndex: TABINDEX_EPLDTIDEW+8,
		text: BTN_FRMSAVE
	}, '-', HelpButton(this, TABINDEX_EPLDTIDEW+9), {
		handler: function() {
			this.ownerCt.hide();
		},
		iconCls: 'cancel16',
		id: 'EPLDTIDEW_CancelButton',
		onTabAction: function() {
			Ext.getCmp('EPLDTIDEW_EvnUslugaDispDop_setDate').focus(true, 200);
		},
		onShiftTabAction: function() {
			Ext.getCmp('EPLDTIDEW_SaveButton').focus(true, 200);
		},
		tabIndex: TABINDEX_EPLDTIDEW+10,
		text: BTN_FRMCANCEL
	}],
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: true,
	formStatus: 'edit',
	doSave: function(callback, nothide) {
		var win = this;
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}
		win.formStatus = 'save';
		
		// проверяем заполненность, отправляем на сервер
		var base_form = this.findById('EPLDTIDEW_EvnUslugaDispDopEditForm').getForm();
		
		if ( !base_form.isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					win.findById('EPLDTIDEW_EvnUslugaDispDopEditForm').getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		win.getLoadMask("Подождите, идет сохранение...").show();
		base_form.submit({
			url: '/?c=EvnPLDispTeenInspection&m=saveEvnUslugaDispDopDirection',
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			},
			params: {
				PersonDispOrp_id: win.PersonDispOrp_id,
				Person_id: win.Person_id
			},
			success: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();
				if ( action.result ) {
					if ( action.result.EvnUslugaDispDop_id && action.result.EvnPLDispTeenInspection_id ) {
						base_form.findField('EvnUslugaDispDop_id').setValue(action.result.EvnUslugaDispDop_id)
						base_form.findField('EvnPLDispTeenInspection_id').setValue(action.result.EvnPLDispTeenInspection_id)
						var params = {};
						params.EvnPLDispTeenInspection_id = action.result.EvnPLDispTeenInspection_id;
						win.callback(params);
						if (typeof callback == 'function') {
							callback();
						}
						if (!nothide) {
							win.hide();
						}
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
			}
		});
    },
	draggable: true,
    height: 200,
	id: 'EvnPLDispTeenInspectionDirectionEditWindow',
    initComponent: function() {
		var win = this;
		
        Ext.apply(this, {
            items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'EPLDTIDEW_PersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					autoScroll: true,
					bodyBorder: false,
					border: false,
					frame: false,
					id: 'EPLDTIDEW_EvnUslugaDispDopEditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [{
						id: 'EPLDTIDEW_EvnUslugaDispDop_id',
						name: 'EvnUslugaDispDop_id',
						value: 0,
						xtype: 'hidden'
					}, {
						name: 'EvnPLDispTeenInspection_id',
						xtype: 'hidden'
					}, {
						name: 'EvnVizitDispDop_pid',
						xtype: 'hidden'
					}, {
						name: 'DispClass_id',
						xtype: 'hidden'
					}, {
						name: 'SurveyTypeLink_id',
						xtype: 'hidden'
					}, {
						name: 'PersonEvn_id',
						xtype: 'hidden'
					}, {
						name: 'MedPersonal_id',
						xtype: 'hidden'
					}, {
						name: 'Server_id',
						xtype: 'hidden'
					}, {
						title: lang['napravlenie'],
						bodyStyle: 'padding: 5px',
						layout: 'form',
						items: [{
							editable: false,
							enableKeyEvents: true,
							fieldLabel: lang['mesto_provedeniya'],
							maxLength: 150,
							name: 'EvnUslugaDispDop_ExamPlace',
							tabIndex: TABINDEX_EPLDTIDEW + 00,
							width: 500,
							xtype: 'textfield'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: false,
									enableKeyEvents: true,
									fieldLabel: lang['data'],
									format: 'd.m.Y',
									id: 'EPLDTIDEW_EvnUslugaDispDop_setDate',
									listeners: {
										'keydown':  function(inp, e) {
											if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
											{
												e.stopEvent();
												Ext.getCmp('EPLDTIDEW_CancelButton').focus(true, 200);
											}
										}				
									},
									name: 'EvnUslugaDispDop_setDate',
									//maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									tabIndex: TABINDEX_EPLDTIDEW+01,
									width: 100,
									xtype: 'swdatefield'
								}]
							}, {
								border: false,
								labelWidth: 80,
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
									name: 'EvnUslugaDispDop_setTime',
									onTriggerClick: function() {
										var base_form = this.findById('EPLDTIDEW_EvnUslugaDispDopEditForm').getForm();
										var time_field = base_form.findField('EvnUslugaDispDop_setTime');

										if ( time_field.disabled ) {
											return false;
										}

										setCurrentDateTime({
											callback: function() {
												base_form.findField('EvnUslugaDispDop_setDate').fireEvent('change', base_form.findField('EvnUslugaDispDop_setDate'), base_form.findField('EvnUslugaDispDop_setDate').getValue());
											},
											dateField: base_form.findField('EvnUslugaDispDop_setDate'),
											loadMask: true,
											setDate: true,
											setDateMaxValue: false,
											setDateMinValue: false,
											setTime: true,
											timeField: time_field,
											windowId: 'EvnPLDispTeenInspectionDirectionEditWindow'
										});
									}.createDelegate(this),
									plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
									tabIndex: TABINDEX_EPLDTIDEW + 0,
									validateOnBlur: false,
									width: 60,
									xtype: 'swtimefield'
								}]
							}]
						}]
					}],
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: function() { }
					}, [
						{ name: 'EvnUslugaDispDop_id' },
						{ name: 'EvnPLDispTeenInspection_id' },
						{ name: 'EvnVizitDispDop_pid' },
						{ name: 'SurveyTypeLink_id' },
						{ name: 'PersonEvn_id' },
						{ name: 'Server_id' },
						{ name: 'EvnUslugaDispDop_ExamPlace' },
						{ name: 'EvnUslugaDispDop_setDate' },
						{ name: 'EvnUslugaDispDop_setTime' }
					]),
					region: 'center'
				})
			]
        });
    	sw.Promed.swEvnPLDispTeenInspectionDirectionEditWindow.superclass.initComponent.apply(this, arguments);
    },
    keys: [{
    	alt: true,
        fn: function(inp, e) {
            e.stopEvent();

            if (e.browserEvent.stopPropagation)
                e.browserEvent.stopPropagation();
            else
                e.browserEvent.cancelBubble = true;

            if (e.browserEvent.preventDefault)
                e.browserEvent.preventDefault();
            else
                e.browserEvent.returnValue = false;

            e.browserEvent.returnValue = false;
            e.returnValue = false;

            if (Ext.isIE)
            {
            	e.browserEvent.keyCode = 0;
            	e.browserEvent.which = 0;
            }

        	var current_window = Ext.getCmp('EvnPLDispTeenInspectionDirectionEditWindow');

            if (e.getKey() == Ext.EventObject.J)
            {
            	current_window.hide();
            }
			else if (e.getKey() == Ext.EventObject.C)
			{
	        	if ('view' != current_window.action)
	        	{
	            	current_window.doSave();
	            }
			}
        },
        key: [ Ext.EventObject.C, Ext.EventObject.J ],
        scope: this,
        stopEvent: false
    }],
    layout: 'border',
    listeners: {
    	'hide': function() {
    		this.onHide();
    	}
    },
    maximizable: true,
    minHeight: 200,
    minWidth: 700,
    modal: true,
    onHide: Ext.emptyFn,
	plain: true,
    resizable: true,
	show: function() {
		sw.Promed.swEvnPLDispTeenInspectionDirectionEditWindow.superclass.show.apply(this, arguments);

		this.formStatus = 'edit';
		var current_window = this;

		current_window.restore();
		current_window.center();

        var form = current_window.findById('EPLDTIDEW_EvnUslugaDispDopEditForm');
		var base_form = form.getForm();
		base_form.reset();

       	current_window.callback = Ext.emptyFn;
       	current_window.OmsSprTerr_Code = null;
       	current_window.onHide = Ext.emptyFn;
       	current_window.Sex_Code = null;

        if (!arguments[0] || !arguments[0].formParams || !arguments[0].Person_id || !arguments[0].PersonDispOrp_id || !arguments[0].SurveyTypeLink_id || !arguments[0].SurveyType_Code)
        {
        	Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); } );
        	return false;
        }
		
		current_window.object = 'EvnPLDispTeenInspection';
		current_window.Person_id = arguments[0].Person_id;
		current_window.PersonDispOrp_id = arguments[0].PersonDispOrp_id;
		
        if (arguments[0].object)
        {
        	current_window.object = arguments[0].object;
        }
		
		base_form.setValues(arguments[0].formParams);
		
		this.SurveyType_Code = arguments[0].SurveyType_Code;
		this.SurveyTypeLink_id = arguments[0].SurveyTypeLink_id;

        if (arguments[0].action)
        {
        	current_window.action = arguments[0].action;
        }
		
		if (arguments[0].set_date)
        {
        	current_window.set_date = arguments[0].set_date;
        }

        if (arguments[0].callback)
        {
            current_window.callback = arguments[0].callback;
        }

        if (arguments[0].onHide)
        {
        	current_window.onHide = arguments[0].onHide;
        }

        if ( !Ext.isEmpty(arguments[0].Sex_Code) ) {
        	current_window.Sex_Code = arguments[0].Sex_Code;
        }

        if ( !Ext.isEmpty(arguments[0].OmsSprTerr_Code) ) {
        	current_window.OmsSprTerr_Code = arguments[0].OmsSprTerr_Code;
        }

		current_window.findById('EPLDTIDEW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

  		var loadMask = new Ext.LoadMask(Ext.get('EvnPLDispTeenInspectionDirectionEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();
		
        form.getForm().clearInvalid();

		var sex_id = arguments[0].Sex_id;
		var age = arguments[0].Person_Age;
		
		var med_personal_id = 0; //arguments[0].formParams.MedPersonal_id;
		
		this.age = arguments[0].Person_Age;
		this.Sex_id = arguments[0].Sex_id;
		this.Person_Birthday = arguments[0].Person_Birthday;
		
		this.wintitle = lang['osmotr_issledovanie'];
		if (arguments[0].SurveyType_Name) {
			this.wintitle = arguments[0].SurveyType_Name;
		}
		
		switch (current_window.action)
		{
        	case 'edit':
			case 'view':
				if (current_window.action == 'edit') {
					current_window.setTitle(this.wintitle + lang['_redaktirovanie']);
					current_window.enableEdit(true);
				} else {
					current_window.setTitle(this.wintitle + lang['_prosmotr']);
					current_window.enableEdit(false);
				}
				loadMask.hide();
				
				// если уже было сохранено надо грузить с сервера
				if (!Ext.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
					loadMask.show();
					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { current_window.hide(); } );
						}.createDelegate(this),
						params: {
							EvnUslugaDispDop_id: base_form.findField('EvnUslugaDispDop_id').getValue()
						},
						success: function(result_form, action) {
							loadMask.hide();
							
							current_window.findById('EPLDTIDEW_EvnUslugaDispDop_setDate').fireEvent('change', current_window.findById('EPLDTIDEW_EvnUslugaDispDop_setDate'), current_window.findById('EPLDTIDEW_EvnUslugaDispDop_setDate').getValue());
							
							base_form.clearInvalid();
						}.createDelegate(this),
						url: '/?c=EvnPLDispTeenInspection&m=loadEvnUslugaDispDopDirection'
					});
				}

				current_window.findById('EPLDTIDEW_EvnUslugaDispDop_setDate').fireEvent('change', current_window.findById('EPLDTIDEW_EvnUslugaDispDop_setDate'), current_window.findById('EPLDTIDEW_EvnUslugaDispDop_setDate').getValue());
				current_window.findById('EPLDTIDEW_EvnUslugaDispDop_setDate').focus(false, 250);
			break;
			
			default:
				current_window.hide();
        }

        form.getForm().clearInvalid();
    },
    width: 700
});