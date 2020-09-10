/**
* swMorbusWindow - окно редактирования/добавления заболевания
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       gabdushev
* @version      december 2012
* @comment      Префикс для id компонентов PSW (MorbusWindow)
*
*
* @input        Morbus_id - идентификатор заболевания
*               Evn_pid - учетный документ, из которого редактируется заболевание
*/

sw.Promed.swMorbusWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {
		if ( this.form.Status == 'save' ) {
			return false;
		}
		var thisWindow = this;
        thisWindow.form.Status = 'save';
		this.findField('Morbus_disDT').allowBlank = !(this.findField('MorbusResult_id').getValue());
		this.findField('MorbusResult_id').allowBlank = !(this.findField('Morbus_disDT').getValue());
		if ( !this.form.getForm().isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    thisWindow.form.Status = 'edit';
                    thisWindow.form.getFirstInvalidEl().focus(true, 100);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var additionalChecks = true;

		//todo: place additional checks here. If those checks fails, show some error message to user and return false

		if (!additionalChecks) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'You have to fill some fields in the right way',
				title: 'Some error occured'
			});
			return false;
		}

		var params = new Object();
		params.Evn_pid         = this.Evn_pid;
		//эти поля есть на форме, уйдут при сабмите сами
		//params.Morbus_id       = this.form.getForm().findField('Morbus_id'      ).getValue();
		//params.Diag_id         = this.form.getForm().findField('Diag_id'        ).getValue();
		//params.Morbus_Nick     = this.form.getForm().findField('Morbus_Nick'    ).getValue();
		//params.Morbus_setDT    = this.form.getForm().findField('Morbus_setDT'   ).getValue();
		//params.Morbus_disDT    = this.form.getForm().findField('Morbus_disDT'   ).getValue();
		//params.MorbusResult_id = this.form.getForm().findField('MorbusResult_id').getValue();
		this.form.getForm().submit({
			failure: function(/*result_form, action*/) {
                //alert('Something goes wrong...');
                thisWindow.form.Status = 'edit';
            },
			params: params,
			success: function(result_form, action) {
                if (thisWindow.callbackAfterSave) {
                    thisWindow.callbackAfterSave(result_form, action);
                }
                thisWindow.hide();
            }
		});
	},
	draggable: true,
	height: 200,
	width: 680,
	tabindex: 100000,
	id: 'MorbusWindow',
	findField: function (fieldId){
		var result;
		if (this.form) {
			result = this.form.getForm().findField(fieldId);
		} else {
			result = false;
		}
		return result;
	},
	initComponent: function() {
		var thisWindow = this;
		this.form = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'MorbusForm',
			labelAlign: 'right',
			labelWidth: 130,
			layout: 'form',
			region: 'center',
			url: '/?c=Morbus&m=Save',
			items: [
                {
                    name: 'Morbus_id',
                    value: null,
                    xtype: 'hidden'
                },
				{
					allowBlank: false,
					fieldLabel: lang['data_nachala'],
					format: 'd.m.Y',
					listeners: {
						'change': function(/*field, newValue, oldValue*/) {},
						'keydown': function (/*inp, e*/) {}
					},
					name: 'Morbus_setDT',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: thisWindow.tabindex + 1,
					width: 100,
					xtype: 'swdatefield'
				},
				{
					layout: 'column',
					border: false,
					items:[
						{
							layout: 'form',
							border: false,
							width: 545,
							items:[
								{
									hiddenName: 'Diag_id',
									allowBlank: false,
									fieldLabel: lang['diagnoz'],
									id: this.id + '_DiagCombo',
									onChange: function() {},
									tabIndex: thisWindow.tabindex + 2,
									enableNativeTabSupport: false,
									width: 400,
									xtype: 'swdiagcombo'
								}
							]
						},
						{
							layout: 'form',
							border: false,
							items:[
								{
									xtype: 'button',
									text: lang['spetsifika'],
									tooltip: lang['otkryit_okno_redaktirovaniya_spetsifiki_svyazannoy_s_dannyim_zabolevaniem'],
									handler: function() {
                                        var selectedDiag = thisWindow.findField('Diag_id').getValue();
                                        if (selectedDiag) {
                                            var r = thisWindow.findField('Diag_id').getStore().getById(selectedDiag);
                                            if (r) {
                                                var specificsWindowName = '';
                                                switch (parseInt(r.data.MorbusType_id)) {
                                                    case 4://наркопсихология
                                                        specificsWindowName = 'swCrazySpecificsTestWindow';
                                                        break;
                                                    default:
                                                        //todo сделать нормальное сообщение
                                                        alert(lang['u_dannogo_zabolevaniya_net_spetsificheskih_dannyih']);
                                                        return;
                                                }
                                                //todo перед редактированием специфики сохранить заболевание и передать его идентификатор[Morbus_id] в специфику
                                                var Morbus_id = thisWindow.findField('Morbus_id').getValue();
                                                if (Morbus_id) {
                                                    var params = new Object();
                                                    params.Morbus_id = Morbus_id;
                                                    params.Evn_id = thisWindow.Evn_pid;
                                                    getWnd(specificsWindowName).show(params);
                                                } else {
                                                    //todo сделать нормальное сообщение
                                                    alert(lang['redaktirovanie_spetsifiki_nevozmojno_t_k_zabolevanie_ne_sohraneno']);
                                                }
                                            } else {
                                                //todo сделать нормальное сообщение
                                                alert(lang['ne_nayden_diagnoz_s_identifikatorom'] + selectedDiag);
                                            }
                                        } else {
                                            //todo сделать нормальное сообщение
                                            alert(lang['vyiberite_diagnoz']);
                                        }
									}
								}
							]
						}

					]
				},
				{
					allowBlank: true,
					fieldLabel: lang['data_okonchaniya'],
					format: 'd.m.Y',
					listeners: {
						'change': function(/*field, newValue, oldValue*/) {},
						'keydown': function (/*inp, e*/) {},
						'blur': function (field){
							thisWindow.findField('MorbusResult_id').allowBlank = !(field.getValue());
						}
					},
					name: 'Morbus_disDT',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: thisWindow.tabindex + 3,
					width: 100,
					xtype: 'swdatefield'
				},
				{
					comboSubject: 'MorbusResult',
					fieldLabel: lang['ishod'],
					hiddenName: 'MorbusResult_id',
					width: 200,
					xtype: 'swcommonsprcombo',
					listeners: {
						'blur': function (field){
							thisWindow.findField('Morbus_disDT').allowBlank = !(field.getValue());
						}
					}
				},
				{
					fieldLabel: lang['kratkoe_opisanie'],
					name: 'Morbus_Nick',
					width: 500,
					xtype: 'textfield',
                    maxLength:30
				}
			]
		});
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					thisWindow.doSave();
				},
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( !this.findById('EUCpxEF_EvnUslugaPanel').collapsed ) {
						this.findById('EvnUslugaComplexEditForm').getForm().findField('UslugaComplex_id').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EUCPXEF + 12,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					if ( !this.findById('EUCpxEF_EvnUslugaPanel').collapsed ) {
						if ( !this.findById('EvnUslugaComplexEditForm').getForm().findField('EvnUslugaComplex_pid').disabled ) {
							this.findById('EvnUslugaComplexEditForm').getForm().findField('EvnUslugaComplex_pid').focus(true, 100);
						}
						else {
							this.findById('EvnUslugaComplexEditForm').getForm().findField('EvnUslugaComplex_setDate').focus(true, 100);
						}
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EUCPXEF + 13,
				text: BTN_FRMCANCEL
			}],
			items: [this.form]
		});
		sw.Promed.swMorbusWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaComplexEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('EUCpxEF_EvnUslugaPanel').toggleCollapse();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.ONE
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EUCpxEF_EvnUslugaPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EUCpxEF_EvnUslugaPanel').doLayout();
		}
	},
	maximizable: false,
	modal: true,
	onCancelAction: function() {
		this.hide();
	},
	setFieldsDisabled: function(d) 
	{
		//todo: доделать просмотр
		var form = this;
		this.form.getForm().findField('Morbus_setDT').setDisabled(d);
		this.form.getForm().findField('Morbus_disDT').setDisabled(d);
		this.form.getForm().findField('MorbusResult_id').setDisabled(d);
		this.form.getForm().findField('Diag_id').setDisabled(d);
		this.form.getForm().findField('Morbus_Nick').setDisabled(d);
		form.buttons[0].setDisabled(d);
	},
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	show: function(params) {
		var thisWindow = this;
		this.form.getForm().reset();
        this.form.Status = '';
		sw.Promed.swMorbusWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(this.form.getEl(), { msg: lang['zagruzka']});
		loadMask.show();
		if (params) {
			if (params.action) {
                if (params.Evn_pid) {
                    this.Evn_pid = params.Evn_pid;
                } else {
                    //todo сделать нормальное окно
                    alert(lang['nepravilnyie_parametryi_ne_ukazan_uchetnyiy_dokument']);
                    this.hide();
                    return false;
                }
                if (params.MorbusType_id) {
                    thisWindow.findField('Diag_id').MorbusType_id = params.MorbusType_id;
                } else {
                    //todo сделать нормальное окно
                    alert(lang['nepravilnyie_parametryi_ne_ukazan_tip_zabolevaniya']);
                    this.hide();
                    return false;
                }
                switch (params.action) {
					case 'add':
						this.setFieldsDisabled(false);
						if (params.Evn_pid) {
                            this.Evn_pid = params.Evn_pid;
                        } else {
                            //todo для создания обязательно нужен учетный документ
                            //todo вывести сообщение неправильные параметры, требуется указать учетный документ
                        }
                        loadMask.hide();
					break;
					case 'view':
					case 'edit':
						if (params.action == 'edit')
						{
							this.setFieldsDisabled(false);
						}
						else
						{
							this.setFieldsDisabled(true);
						}
						if (params.Morbus_id) {
							Ext.Ajax.request({
								method: 'post',
								params: {
									Morbus_id: params.Morbus_id
								},
								callback: function (options, success, response){
									if (success) {
										var decodedJson =  Ext.util.JSON.decode(response.responseText);
										if (decodedJson[0]) {
											var formData = decodedJson[0];
											//устанавливаем значение полей
											thisWindow.form.getForm().setValues(formData);
											//выстреливаем установленный диагноз
											var diag_id = thisWindow.findField('Diag_id').getValue();
											if (diag_id != null && diag_id.toString().length > 0) {
												thisWindow.findField('Diag_id').getStore().load({
													callback: function() {
														thisWindow.findField('Diag_id').getStore().each(function(record) {
															if (record.get('Diag_id') == diag_id) {
																thisWindow.findField('Diag_id').fireEvent('select', thisWindow.findField('Diag_id'), record, 0);
															}
														});
														loadMask.hide();
													},
													params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
												});
											}
										} else {
                                            sw.swMsg.show({
                                                buttons: Ext.Msg.OK,
                                                icon: Ext.Msg.WARNING,
                                                title: lang['oshibka_prilojeniya'],
                                                msg: lang['nepravilnyiy_otvet_servera']
                                   			});
										}
									} else {
                                        sw.swMsg.show({
                                            buttons: Ext.Msg.OK,
                                            icon: Ext.Msg.WARNING,
                                            title: lang['oshibka_prilojeniya'],
                                            msg: lang['oshibka_pri_obmene_informatsiey_s_serverom']
                               			});
									}
								},
								url: '/?c=Morbus&m=load'
							});
						} else {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                icon: Ext.Msg.WARNING,
                                title: lang['nepravilnyie_parametryi'],
                                msg: lang['ne_peredan_identifikator_zabolevaniya']
                   			});
						}
					break;
					default:
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            icon: Ext.Msg.WARNING,
                            title: lang['nepravilnyie_parametryi'],
                            msg: "Недопустимое значение параметра action:" + param.action
               			});
				}
                if (params.callbackAfterSave){
                    thisWindow.callbackAfterSave = params.callbackAfterSave;
                }
			} else {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.WARNING,
                    title: lang['nepravilnyie_parametryi'],
                    msg: "Обязательный параметр action не указан"
       			});
			}
		} else {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                icon: Ext.Msg.WARNING,
                title: lang['oshibka_pri_otkryitii_formyi'],
                msg: "Не переданы обязательные параметры"
   			});
		}
	},
	title: lang['zabolevanie']
});

