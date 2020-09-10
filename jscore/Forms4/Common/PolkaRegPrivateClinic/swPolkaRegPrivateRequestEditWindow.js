/**
 * swPolkaRegPrivateRequestEditWindow - редактирование заявки
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    2020, brotherhood of swan developers
 */
Ext6.define('common.PolkaRegPrivateClinic.swPolkaRegPrivateRequestEditWindow', {
    alias: 'widget.swPolkaRegPrivateRequestEditWindow',
    requires: [
        'common.PolkaRegPrivateClinic.model.EvnQueueRecRequest'
    ],
    formModel: 'common.PolkaRegPrivateClinic.model.EvnQueueRecRequest',
    cls: 'arm-window-new',
    extend: 'base.BaseForm',
    title: 'Новая заявка',
    layout: 'border',
    width: 550,
    height: 400,
    resizable: true,
    maximizable: false,
    closable: true,
    modal: true,
    header: true,
    constrain: true,
    getFormData: function(){
       return this.requestForm.getValues();
    },
	setRequestStatus: function(params){

    	const win = this;

		Ext6.Ajax.request({
			params:{
				EvnQueue_id: win.request.get('EvnQueue_id'),
				EvnStatus_SysNick: params.EvnStatus_SysNick
			},
			url: '/?c=RegPrivate&m=setRequestStatus',
			success: function (response, opts) {
				const resp = Ext.decode(response.responseText);
				log('successResponse', response);
				if (resp.success) {
					if (params.callback && typeof params.callback === 'function') {
						params.callback();
					}
				} else {
					let err = resp.Error_Msg ?  ': '+resp.Error_Msg : '';
					Ext6.Msg.alert(langs('Ошибка'), langs('При смене статуса заявки произошла ошибка' + err));
					return false;
				}

			},
			failure: function (response, opts) {
				log('failureResponse', response);
				Ext6.Msg.alert(langs('Ошибка'), langs('При смене статуса заявки произошла ошибка'));
				return false;
			}
		});
	},
	beforeHide: function() {

    	const win = this;
    	log('unblock request');

    	// разблокировка заявки при закрытии
		if (win.action === 'process') {
			win.setRequestStatus({
				EvnStatus_SysNick: 'Queued', // новая
				callback: function(){
					if (win.onSetRequestStatus && typeof win.onSetRequestStatus === 'function') {
						win.onSetRequestStatus({
							EvnStatus_SysNick: 'Queued'
						});
					}
				}
			});
		}
	},
    show: function () {

        this.callParent(arguments);

        if (!arguments || !arguments[0]) {
            this.hide();
            Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
            return false;
        }

        const win = this;

        if (arguments[0].action) {
            win.action = arguments[0].action;
        }

        if (arguments[0].request) {
            win.request = arguments[0].request;
        }

		if (arguments[0].onSubmitSuccess) {
			win.onSubmitSuccess = arguments[0].onSubmitSuccess;
		} else {
			win.onSubmitSuccess = Ext6.emptyFn
		}

		if (arguments[0].onSetRequestStatus) {
			win.onSetRequestStatus = arguments[0].onSetRequestStatus;
		} else {
			win.onSetRequestStatus = Ext6.emptyFn
		}

		const buttonDecline = this.getFormCmp('#rp-request-form-buttonDecline');
		const buttonAccept = this.getFormCmp('#rp-request-form-buttonAccept');
		const buttonCancel = this.getFormCmp('#rp-request-form-buttonCancel');

		this.removeListener('beforeHide', win.beforeHide);

		this.requestForm.reset();
		this.disabledFieldsByDefault = [];

		// определим поля дизабленные по умолчанию
		win.getDisabledFields(this.requestForm.config.items[0].items);

		win.loadData({
			EvnQueue_id: win.request.get('EvnQueue_id'),
			callback: function(){
				if (win.action === 'process') {

					log('block request');

					// блокируем заявку
					win.setRequestStatus({
						EvnStatus_SysNick: 'InProc', // в обработке
						callback: function(){
							if (win.onSetRequestStatus && typeof win.onSetRequestStatus === 'function') {
								win.onSetRequestStatus();
							}
						}
					});
				}
			}
		});

        switch(win.action) {
			case 'process':

				win.on('beforeHide', win.beforeHide);

				win.enableFields();
				if (buttonAccept.isHidden()) buttonAccept.show();
				if (buttonDecline.isHidden()) buttonDecline.show();
				if (!buttonCancel.isHidden()) buttonCancel.hide();

				buttonAccept.setText('Подтвердить');

                break;
            case 'edit':

				this.enableFields();
				if (buttonAccept.isHidden()) buttonAccept.show();
				if (buttonCancel.isHidden()) buttonCancel.show();
				if (!buttonDecline.isHidden()) buttonDecline.hide();

				buttonAccept.setText('Сохранить');

                break;
			case 'view':

				this.disableFields();
				if (!buttonAccept.isHidden()) buttonAccept.hide();
				if (buttonCancel.isHidden()) buttonCancel.show();
				if (!buttonDecline.isHidden()) buttonDecline.hide();

				break;
        }
    },
	// рекурсия
	getDisabledFields: function(fields){
    	const win = this;
		fields.forEach(function(field){

			if (field.disabled) {
				win.disabledFieldsByDefault.push(field.name);
			}

			if (field.items && field.items.length > 0) {
				win.getDisabledFields(field.items);
			}
		});
	},
	disableFields: function() {
    	const fields = this.requestForm.getForm().getFields();
    	fields.each(function(field){
			field.disable();
		})
	},
	enableFields: function() {

    	const win = this;
		const fields = this.requestForm.getForm().getFields();

		fields.each(function(field){

			let fieldDisabledByDefault = false;

			win.disabledFieldsByDefault.some(function(fieldName){
				if (fieldName === field.getName()) {
					fieldDisabledByDefault = true;
					return true; // == break
				}
			});

			if (!fieldDisabledByDefault && field.isDisabled()) field.enable();
		})
	},
	getFormCmp: function(selector, scope){
		let cmp = Ext6.ComponentQuery.query(selector, scope);
		if (cmp[0]) cmp = cmp[0];
    	return cmp;
	},
    loadData: function(params){

		const win = this;
		win.getLoadMask('Загрузка формы заявки').show();

        this.requestForm.load({
            url: '/?c=RegPrivate&m=loadRequestData',
            params: params,
            success: function(form, action) {
				win.getLoadMask().hide();
                log('actionSuccess', action);

				const data = action.result.data;
				let title = '';

				if (
					win.action === 'process'
					&& data.EvnStatus_id === 51
					&& data.EvnStatus_pmUser_insID
					&& getGlobalOptions().pmuser_id != data.EvnStatus_pmUser_insID
				) {
					Ext6.Msg.alert(langs('Ошибка'), langs('Заявка уже в обработке'));
					if (win.onSubmitSuccess && typeof win.onSubmitSuccess === 'function') {
						win.onSubmitSuccess();
					}

					win.removeListener('beforeHide', win.beforeHide);

					win.hide();
					return false;
				}

				if (data.Person_FullName) {
					title = data.Person_FullName;
				}

				if (data.Person_BirthDay) {
					title += ' ' + data.Person_BirthDay;
				}

				if (title) win.setTitle(title);
				win.loadMedStaffFactCombo({
					LpuSectionProfile_Code: data.LpuSectionProfile_Code,
					MedStaffFact_id: data.MedStaffFact_id
				});

				//userForm.loadRecord(user);
				if (params.callback && typeof params.callback === 'function') {
					params.callback(action.result.data);
				}
            },
            failure: function(form, action) {
				win.getLoadMask().hide();
                log('actionFailure', action);
                Ext6.Msg.alert(langs('Ошибка'), 'Ошибка при загрузке данных формы');
            }
        })
    },
    submit: function(params){

    	const win = this;
    	if (!params) params = {};

		win.getLoadMask('Сохранение заявки').show();

        this.requestForm.submit({
            url: '/?c=RegPrivate&m=saveRequest',
            success: function(form, action) {

				win.getLoadMask().hide();
                log('actionSuccess', action);

				win.removeListener('beforeHide', win.beforeHide);

				if (win.onSubmitSuccess && typeof win.onSubmitSuccess === 'function') {
					win.onSubmitSuccess();
				}
				win.hide();
            },
			params: params,
            failure: function(form, action) {

				win.getLoadMask().hide();
                log('actionFailure', action);
                if (action.result.Error_Msg) {
                    Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
                } else if (action.result.Warning_Msg) {

                	let title = 'Предупреждение';
                	let saveParams = {};

					// здесь копим варнинги
					if (action.result.warnings) {
						action.result.warnings.forEach(function(paramName){
							saveParams[paramName] = 1;
						})
					}

                	if (action.result.Warning_Param) {
						saveParams[action.result.Warning_Param] = 1;
					}

					Ext6.Msg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function (buttonId) {
							if (buttonId === 'yes') {
								win.submit(saveParams);
							}
						},
						msg: action.result.Warning_Msg,
						title: title
					});
				} else {
                    Ext6.Msg.alert(langs('Ошибка'), 'Ошибка при подтверждении данных формы');
                }
            }
        })
    },
    loadMedStaffFactCombo: function(params) {

        const win = this;

        let filter_params = {
            allowLowLevel: 'yes',
            lpuSectionProfileCode: params.LpuSectionProfile_Code ? params.LpuSectionProfile_Code : null
        };

        setMedStaffFactGlobalStoreFilter(filter_params, sw4.swMedStaffFactGlobalStore);
        win.requestForm.getForm().findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));

        if (params.MedStaffFact_id){
            win.requestForm.getForm().findField('MedStaffFact_id').setValue(params.MedStaffFact_id);
        }
    },
	declineRequest: function(params){

    	const win = this;
		const EvnDirection_id = this.requestForm.getForm().findField('EvnDirection_id').getValue();

		if (EvnDirection_id) {
			getWnd('swSelectEvnStatusCauseWindow').show({
				EvnClass_id: 27,
				formType: 'regprivate',
				winTitle: 'Выбор причины отмены заявки',
				btnAcceptText: 'Отклонить',
				callback: function(cause) {
					Ext6.Ajax.request({
						params:{
							EvnDirection_id: EvnDirection_id,
							EvnStatusCause_id: cause.EvnStatusCause_id,
							EvnStatusHistory_Cause: cause.EvnStatusHistory_Cause
						},
						url: '/?c=RegPrivate&m=declineRequest',
						success: function (response, opts) {
							const resp = Ext.decode(response.responseText);
							log('successResponse', response);
							if (resp.success) {

								win.removeListener('beforeHide', win.beforeHide);

								Ext6.Msg.alert(langs('Успех'), langs('Заявка успешно отклонена'));
								if (win.onSubmitSuccess && typeof win.onSubmitSuccess === 'function') {
									win.onSubmitSuccess();
								}

								win.hide();

							} else {
								let err = resp.Error_Msg ?  ': '+resp.Error_Msg : '';
								Ext6.Msg.alert(langs('Ошибка'), langs('При отклонении заявки произошла ошибка' + err));
							}

						},
						failure: function (response, opts) {
							log('failureResponse', response);
							Ext6.Msg.alert(langs('Ошибка'), langs('При отклонении заявки произошла ошибка'));
						}
					});
				}
			});
		}
	},
    initComponent: function() {

        const win = this;

        win.requestForm = Ext6.create('Ext6.form.FormPanel', {
            autoScroll: true,
            region: 'center',
            border: false,
            bodyStyle: 'padding: 20px 20px 20px 20px;',
            fieldDefaults: {
                labelAlign: 'left',
                msgTarget: 'side'
            },
            defaults: {
                border: false,
                xtype: 'panel',
                layout: 'anchor'
            },
            layout: 'vbox',
            reader: {
                type: 'json',
                model: win.formModel,
                rootProperty: 'data'
            },
            items: [
                {
                    defaults: {
                        width: 450,
						labelSeparator: '',
                    },
                    items: [
						{
							xtype: 'hidden',
							name: 'TimetableGraf_id',
						},
                        {
                            xtype: 'hidden',
                            name: 'EvnQueue_id',
                        },
                        {
                            xtype: 'hidden',
                            name: 'EvnDirection_id',
                        },
                        {
                            xtype: 'hidden',
                            name: 'Person_id',
                        },
                        {
                            layout: 'hbox',
                            border: false,

                            margin: '0 0 5 0',
                            items: [{
                                xtype: 'datefield',
                                plugins: [new Ext6.ux.InputTextMask('99.99.9999', true)],
                                fieldLabel: langs('Дата заявки'),
                                allowBlank: false,
                                width: 230,
                                name: 'EvnQueue_insDT_date',
                                disabled: true,
								labelSeparator: '',
                            },
                                {xtype: 'tbspacer', width: 10},
                                {
                                    xtype: 'swTimeField',
                                    allowBlank: false,
                                    width: 100,
                                    hideLabel: true,
                                    name: 'EvnQueue_insDT_time',
                                    disabled: true,
									labelSeparator: '',
                                }]
                        },
                        {
                            xtype: 'textfield',
                            plugins: [ new Ext6.ux.InputTextMask('+7 999 999 99 99', true) ],
                            fieldLabel: 'Телефон',
                            width: 240,
                            name: 'Person_Phone',
							getValue: function() {
								var v = this.getRawValue();
								if(v && v.length>0) {
									v = v.replace(/[ \(\)_]/g,'');
									if(v.length==12 && v.slice(0,2)=='+7') return v;
									else return '';
								} else return '';
							},
							setValue: function(x) {
								if(!x) { this.setRawValue(''); return '';}
								var regexp = /^(\+?7)?[\s\-]?\(?(\d{3})\)?[\s\-]?(\d{3})[\s\-]?(\d{2})[\s\-]?(\d{2})$/;
								x = x.replace(/[ \(\)_]/g,'');
								if ( !regexp.test(x) ) {
									this.setRawValue('');
								} else {
									this.setRawValue(x.replace(regexp,'+7 $2 $3 $4 $5'));
								}
							}
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'E-mail',
                            name: 'Person_Email',
							disabled: true
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Комментарий',
                            name: 'EvnDirection_Descr',
                            disabled: true
                        },
						{
							xtype: 'swLpuSectionProfileCombo',
							fieldLabel: 'Профиль',
							anyMatch: true,
							name: 'LpuSectionProfile_id',
							reference: 'LpuSectionProfile_id',
							allowBlank: true,
							disabled: true,
							anchor: '-5',
							listeners: {
								select: function(combo, record, index) { }
							},
							tpl: new Ext6.XTemplate(
								'<tpl for="."><div class="x6-boundlist-item">',
								'<table>',
								'<tr><td style="color: red; width:60px;">{LpuSectionProfile_Code}</td>',
								'<td>{LpuSectionProfile_Name}</td>',
								'</tr></table>',
								'</div></tpl>'
							)
						},
                        {
                            xtype: 'swMedStaffFactCombo',
                            fieldLabel: 'Врач',
                            minWidth: 450,
                            anyMatch: true,
                            name: 'MedStaffFact_id',
                            anchor: '-5',
                            allowBlank: true,
                            lastQuery: '',
                            displayTpl: new Ext6.XTemplate(
                                '<tpl for=".">',
                                '{MedPersonal_SurName}' + ' ',
                                '{MedPersonal_FirName}' + ' ',
                                '{MedPersonal_SecName}' + ' ',
                                '</tpl>'
                            )
                        },
                        {
                            layout: 'hbox',
                            border: false,

                            margin: '0 0 5 0',
                            items: [{
                                xtype: 'datefield',
                                plugins: [new Ext6.ux.InputTextMask('99.99.9999', true)],
                                fieldLabel: langs('Дата записи'),
                                allowBlank: true,
                                width: 230,
                                name: 'TimetableGraf_begTime_date',
								labelSeparator: ''
                            },
                                {xtype: 'tbspacer', width: 10},
                                {
                                    xtype: 'swTimeField',
                                    allowBlank: true,
                                    width: 100,
                                    hideLabel: true,
                                    name: 'TimetableGraf_begTime_time',
									labelSeparator: ''
                            }]
                        }
                    ]
                }
            ]
        });

        Ext6.apply(win, {
            items: [
                win.requestForm
            ],
            buttons: ['->',{
				handler: function() {
					win.hide();
				},
				cls: 'buttonCancel',
				itemId: 'rp-request-form-buttonCancel',
				text: 'Отмена'
			}, {
                handler: function () {
					win.declineRequest({
						callback: function(){
							win.hide();
						}
					})
                },
                cls: 'buttonCancel',
				itemId: 'rp-request-form-buttonDecline',
                text: 'Отклонить'
            },{
                handler: function () {
                    win.submit();
                },
                cls: 'buttonAccept',
				itemId: 'rp-request-form-buttonAccept',
                text: 'Подтвердить'
            }]
        });

        this.callParent(arguments);
    }
});