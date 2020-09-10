/**
* АРМ службы Центр удалённой консультации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      10.2014
*/
sw.Promed.swWorkPlaceTelemedWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
    //объект с параметрами АРМа, с которыми была открыта форма
    userMedStaffFact: null,
    gridPanelAutoLoad: false,
    DirectionList: [],
    NotificationFor: ['Неотложная', 'Экстренная'], // типы заявок со звуковым уведомлением
    id: 'swWorkPlaceTelemedWindow',
    playNotification: function()
    {
        Ext.get('swWorkPlaceTelemedWindowNotification').dom.play();
    },
    listeners: {
		hide: function () {
			if (getRegionNick() != 'kz' && this.VideoChatBtn) {
				this.VideoChatBtn.hide();
			}
		}
	},
    /**
     * Пытаемся определить MedStaffFact_id врача в отделении, где создана служба
     * Если врач не имеет мест работы в отделении, где создана служба,
     * то сообщаем об этом
     */
	_defineMedStaffFactId: function(callback) {
		var thas = this;
		sw.Promed.MedStaffFactByUser.loadMedStaffFactId({
			onDate: getGlobalOptions().date,
			LpuSection_id: this.userMedStaffFact.LpuSection_id
		}, thas.userMedStaffFact.MedPersonal_id, function(id) {
			if (id) {
				thas.userMedStaffFact.MedStaffFact_id = id;
				if (typeof callback == 'function') {
					callback();
				}
			} else {
				sw.swMsg.alert(langs('Ошибка'), langs('Вы не имеете мест работы в отделении, где создана служба!'));
				thas.hide();
			}
		});
	},
	/**
	 * Принять пациента / Открыть ЭМК
	 *
	 * Нужно учитывать нюансы:
	 * был ли пациент принят
	 * есть ли связанное назначение (record.get('EvnPrescr_id'), record.get('EvnPrescr_IsExec'))
	 * из какого документа создано назначение (record.get('EvnPrescrParentEvn_id'), record.get('EvnPrescrParentEvnDirection_rid'), record.get('EvnPrescrParentEvnClass_SysNick'))
	 */
	_openEmk: function(record, params) {
		var thas = this;
		if (!this.userMedStaffFact.MedStaffFact_id && this.userMedStaffFact.LpuSection_id) {
			// для правильной работы ЭМК нужно определить MedStaffFact_id врача в отделении, где создана служба
			this._defineMedStaffFactId(function() {
				thas._openEmk(record, params);
			});
			return false;
		}

		params.userMedStaffFact = this.userMedStaffFact;
		params.mode = 'workplace';
		params.ARMType = 'common';
        params.accessViewFormDelegate = {};
        params.allowAddEvnUslugaTelemed = !record.get('EvnUslugaTelemed_id');
        params.openEvnUslugaTelemedEditWindowHandler = function(emk, action, callback) {
            if (!record.get('EvnUslugaTelemed_id')) {
                action = 'add';
            }
            var win = getWnd('swEvnUslugaTelemedEditWindow'),
                params = {
                    userMedStaffFact: thas.userMedStaffFact,
                    action: action,
                    formParams: {
                        EvnUslugaTelemed_id: record.get('EvnUslugaTelemed_id') || null
                    },
                    callback: function(data) {
                        emk.onSaveEvnDocument('add' == action, data, 'EvnUslugaTelemed');
						if (callback && typeof callback == 'function') {
							callback(data);
						}
                    }
                };
            params.formParams.Person_id = record.get('Person_id');
            if ('add' == action) {
                params.formParams.EvnDirection_id = record.get('EvnDirection_id');
                params.formParams.EvnDirection_pid = record.get('EvnDirection_pid');
                params.formParams.Diag_id = record.get('Diag_id');
                params.formParams.PersonEvn_id = record.get('PersonEvn_id');
                params.formParams.Server_id = record.get('Server_id');
                params.formParams.MedPersonal_id = thas.userMedStaffFact.MedPersonal_id;
                params.formParams.MedStaffFact_id = thas.userMedStaffFact.MedStaffFact_id || null;
                if (record.get('Lpu_id') == thas.userMedStaffFact.Lpu_id && thas.userMedStaffFact.LpuSection_id) {
                    // Отделение ЛПУ
                    params.formParams.UslugaPlace_id = 1;
                    params.formParams.LpuSection_uid = thas.userMedStaffFact.LpuSection_id;
                    params.formParams.Lpu_uid = null;
                } else if (record.get('Lpu_id') != thas.userMedStaffFact.Lpu_id) {
                    // Другое ЛПУ
                    params.formParams.UslugaPlace_id = 2;
                    params.formParams.LpuSection_uid = null;
                    params.formParams.Lpu_uid = thas.userMedStaffFact.Lpu_id;
                } else {
                    // пользователь должен выбрать место выполнения
                    params.formParams.UslugaPlace_id = null;
                    params.formParams.LpuSection_uid = null;
                    params.formParams.Lpu_uid = null;
                }
            }
            if (win.isVisible()) {
                //sw.swMsg.alert('Сообщение', 'Форма в данный момент открыта.');
                return false;
            }
            win.show(params);
            return true;
        };
		params.onSaveEvnDocument = function(isAdd, data, obj) {
			if (isAdd && data.EvnUslugaTelemed_id) {
                //thas.FilterPanel.getForm().findField('EvnDirection_Num').setValue(record.get('EvnDirection_Num'));
                thas.disableCheckModal = true;
                thas.doSearch();
                thas.disableCheckModal = false;
			}
		};
		params.callback = function() {
			thas.doSearch();
		};
		params.searchNodeObj = {
			parentNodeId: 'root',
			last_child: false,
			disableLoadViewForm: false,
			EvnClass_SysNick: record.get('RootEvnClass_SysNick'),
			Evn_id: record.get('EvnDirection_rid'),
			scroll_value: null
		};
		switch (record.get('RootEvnClass_SysNick')) {
			case 'EvnPS':
				//позиционируем на движении, в котором было создано направление
				params.searchNodeObj.scroll_value = ('EvnSection_data_'+ record.get('EvnDirection_pid'));
				//params.accessViewFormDelegate['EvnMediaDataList_'+ record.get('EvnDirection_rid') +'_add'] = true;
				//params.accessViewFormDelegate['EvnXmlProtokolList_'+ record.get('EvnDirection_pid') +'_adddoc'] = true;
				//params.accessViewFormDelegate['EvnXmlRecordList_'  + record.get('EvnDirection_pid') +'_adddoc'] = true;
				//params.accessViewFormDelegate['EvnXmlEpikrizList_' + record.get('EvnDirection_pid') +'_adddoc'] = true;
				//params.accessViewFormDelegate['EvnXmlOtherList_'   + record.get('EvnDirection_pid') +'_adddoc'] = true;
				break;
			case 'EvnPL':
				//позиционируем на посещении, в котором было создано направление
				params.searchNodeObj.scroll_value = ('EvnVizitPL_head_'+ record.get('EvnDirection_pid'));
				params.searchNodeObj.parentNodeId = 'EvnPL_' + record.get('EvnDirection_rid'),
				params.searchNodeObj.EvnClass_SysNick = 'EvnVizitPL';
				params.searchNodeObj.Evn_id = record.get('EvnDirection_pid');
				//params.accessViewFormDelegate['EvnMediaDataList_'+ record.get('EvnDirection_rid') +'_add'] = true;
				//params.accessViewFormDelegate['FreeDocumentList_'+ record.get('EvnDirection_pid') +'_adddoc'] = true;
				break;
			case 'EvnPLStom':
				//позиционируем на посещении, в котором было создано направление
				params.searchNodeObj.scroll_value = ('EvnVizitPLStom_data_'+ record.get('EvnDirection_pid'));
				//params.accessViewFormDelegate['EvnMediaDataList_'+ record.get('EvnDirection_pid') +'_add'] = true;
				//params.accessViewFormDelegate['FreeDocumentList_'+ record.get('EvnDirection_pid') +'_adddoc'] = true;
				break;
		}
		getWnd('swPersonEmkWindow').show(params);
		return true;
    },
	/**
	 * Добавить внешнее направление
	 */
	createDirection: function() {
		var me = this,
			grid = this.GridPanel.getGrid(),
			onHide = function() {
				me.GridPanel.focus();
			};
		getWnd('swPersonSearchWindow').show({
			onClose: onHide,
			onSelect: function(pdata)
			{
				getWnd('swPersonSearchWindow').hide();
				getWnd('swEvnDirectionEditWindow').show({
					action: 'add',
					disableQuestionPrintEvnDirection: true,
					callback: function(data) {
						if (data && data.evnDirectionData) {
							var params = Ext.apply(me.FilterPanel.getForm().getValues(), me.searchParams);
							params.EvnDirection_id = data.evnDirectionData.EvnDirection_id;
							params.begDate = Ext.util.Format.date(me.dateMenu.getValue1(), 'd.m.Y');
							params.endDate = Ext.util.Format.date(me.dateMenu.getValue2(), 'd.m.Y');
							me.GridPanel.removeAll({clearAll:true});
							me.GridPanel.loadData({
								globalFilters: params,
								callback: function() {
									var index = grid.getStore(). indexOfId(data.evnDirectionData.EvnDirection_id);
									if (index >= 0) {
										grid.getSelectionModel().selectRow(index);
										me.reception();
									}
								}
							});
						}
					},
					onHide: onHide,
					Person_id: pdata.Person_id,
					Person_Surname: pdata.Person_Surname,
					Person_Firname: pdata.Person_Firname,
					Person_Secname: pdata.Person_Secname,
					Person_Birthday: pdata.Person_Birthday,
					formParams: {
						Person_id: pdata.Person_id,
						PersonEvn_id: pdata.PersonEvn_id,
						Server_id: pdata.Server_id,
						DirType_id: 17,
						EvnDirection_IsReceive: 2,
						ARMType_id: me.userMedStaffFact.ARMType_id,
						Lpu_did: me.userMedStaffFact.Lpu_id,
						Lpu_sid: me.userMedStaffFact.Lpu_id,
						MedService_id: me.userMedStaffFact.MedService_id
					}
				});
			},
			searchMode: 'all'
		});
		return true;
	},
	deleteEvnDirection: function(cancelType) {
		if (!cancelType) {
			cancelType = 'cancel';
		}

		var win = this;
		var grid = this.GridPanel.getGrid();
        var rec = grid.getSelectionModel().getSelected();
		return sw.Promed.Direction.doDelete({
			id: rec.get('EvnDirection_id'),
			callback: function(cfg) {
				win.disableCheckModal = true;
                win.doSearch();
                win.disableCheckModal = false;
			}
		});
	},
    /**
     * Открыть ЭМК
     */
    reception: function() {
        var grid = this.GridPanel.getGrid();
        var record = grid.getSelectionModel().getSelected();
        if ( !record || !record.get('EvnDirection_id') ) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не выбрана заявка!'));
            return false;
        }
        var params = {
            Person_id: record.get('Person_id'),
            Server_id: record.get('Server_id'),
            PersonEvn_id: record.get('PersonEvn_id')
        };
        this._openEmk(record, params);
        return true;
    },
    doReset: function()
    {
        this.FilterPanel.getForm().reset();
        this.searchParams = { MedService_id: this.userMedStaffFact.MedService_id, 'wnd_id': this.id }; // для фильтрации направлений по службе
    },
    show: function() {
		sw.Promed.swWorkPlaceTelemedWindow.superclass.show.apply(this, arguments);
		var win = this;
        if (arguments[0] && arguments[0].MedService_id) {
            this.userMedStaffFact = arguments[0];
        } else if (arguments[0] && arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.MedService_id) {
            this.userMedStaffFact = arguments[0].userMedStaffFact;
        } else{
            log(arguments);
            sw.swMsg.alert(langs('Ошибка'), langs('Не указан идентификатор службы'), function() {
                this.hide();
            }.createDelegate(this));
            return false;
        }
		log(this.GridPanel,"3e423423");
		this.GridPanel.addActions({
				name:'action_deleteEvnDirection',
				text: langs('Удалить направление'),
				handler: function() {win.deleteEvnDirection();},
				iconCls : 'x-btn-text',
				icon: 'img/icons/delete16.png'
			},4);
        this.doReset();
        this.doSearch('day');
		if(getRegionNick() != 'kz') {
			if(sw.Promed.Actions.VideoChatBtn) {
				win.VideoChatBtn = sw.Promed.Actions.VideoChatBtn;
				win.VideoChatBtn.show();
			}

			this.GridPanel.addActions({
				name:'action_cancelEvnDirection',
				text: langs('Отменить направление'),
				handler: this.cancelEvnDirection.createDelegate(this),
				iconCls: 'cancel16'
			}, 5);
		}
		return true;
	},
	cancelEvnDirection: function() {
		var win = this,
			grid = win.GridPanel.getGrid(),
			rec = grid.getSelectionModel().getSelected();

		if(!rec) return;

		return sw.Promed.Direction.cancel({
				cancelType: 'cancel',
				ownerWindow: win,
				EvnDirection_id: rec.get('EvnDirection_id'),
				DirType_Code: rec.get('DirType_Code'),
				TimetableGraf_id: rec.get('TimetableGraf_id'),
				TimetableMedService_id: rec.get('TimetableMedService_id'),
				TimetableStac_id: rec.get('TimetableStac_id'),
				EvnQueue_id: rec.get('EvnQueue_id'),
				allowRedirect: true,
				userMedStaffFact: win.userMedStaffFact,
				Person_id: rec.get('Person_id'),
				callback: win.doSearch.createDelegate(win)
		});

    },
    viewEvnDirection: function(id) {
        var me = this,
            grid = me.GridPanel.getGrid(),
            rec = grid.getStore().getById(id),
            win = getWnd('swEvnDirectionEditWindow');
        if (win.isVisible()) {
            //sw.swMsg.alert('Сообщение', 'Форма "Просмотр направления" в данный момент открыта.');
            return false;
        }
        if (!rec) {
            //sw.swMsg.alert('Сообщение', 'Форма "Просмотр направления" в данный момент открыта.');
            return false;
        }
        me.GridPanel.selectedIndex = grid.getStore().indexOf(rec);
        win.show({
            action: 'view',
            formParams: {},
            EvnDirection_id: id,
            PersonEvn_id: rec.get('PersonEvn_id'),
            Person_id: rec.get('Person_id'),
            UserMedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
            UserLpuSection_id: me.userMedStaffFact.LpuSection_id,
            userMedStaffFact: me.userMedStaffFact,
            from:me.userMedStaffFact.ARMForm,
            ARMType:me.userMedStaffFact.ARMType,
            onHide: function(){
                me.GridPanel.focus();
            }
        });
        return true;
    },
	printCostTreat:function(){
		var Report_Params = '';
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		  if ( !record || !record.get('EvnUslugaTelemed_id') ) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не выбрана услуга!'));
            return false;
        }
		Report_Params = '&paramEvnUsl=' + record.get('EvnUslugaTelemed_id');

		printBirt({
			'Report_FileName': 'pan_Spravka_TeleUsl.rptdesign',
			'Report_Params': Report_Params,
			'Report_Format': 'pdf'
		});
	},
	openEvnUslugaTelemedEditWindow: function(EvnUslugaTelemed_id, EvnDirection_id)
	{
		var action = 'edit',
			form = this,
			grid = form.GridPanel.getGrid(),
			rec = grid.getStore().getById(EvnDirection_id),
			form_title,
			params = {EvnUslugaTelemed_id: EvnUslugaTelemed_id},
			callback = function (data) {
			};

		if (rec && rec.data) {
			var formParams = rec.data;
		}
		else
			return false;

		form_title = langs('Параклиническая услуга: Редактирование');
		params.onSaveUsluga = callback;
		params.formParams = formParams;
		params.onHide = function() {
			grid.getStore().reload();
		};
		form.openForm('swEvnUslugaTelemedEditWindow', 'XXX_id', params, action, form_title);
	},

	/**
	 * Открывает соответсвующую акшену форму
	 *
	 * @param {String} open_form Название открываемой формы, такое же как название объекта формы
	 * @param {type} id Наименование идентификатора таблицы, передаваемого в форму
	 * @param {type} oparams
	 * @param {type} mode
	 * @param {type} title
	 * @param {type} callback
	 * @returns {Boolean}
	 */
	openForm: function (open_form, id, oparams, mode, title, callback) {
		// Для упрощения процесса ссылки на формы назовем также как и формы
		// Получаем Id записи
		// Открываем форму, если не открыта
		var win = this;

		// Проверка
		if (getWnd(open_form).isVisible()) {
			return false;
		} else {
			var object_value;
			if (!mode)
				mode = this.isReadOnly?'view':'edit';

			var params = {
				action: mode,
				onHide: function() {
					if (callback){
						callback();
					}
				}.createDelegate(this),
				PersonEvn_id: this.PersonEvn_id,
				Server_id: this.Server_id,
				/*Person_Firname: this.PersonInfoFrame.getFieldValue('Person_Firname'),
				Person_Surname: this.PersonInfoFrame.getFieldValue('Person_Surname'),
				Person_Secname: this.PersonInfoFrame.getFieldValue('Person_Secname'),
				Person_Birthday: this.PersonInfoFrame.getFieldValue('Person_Birthday'),*/
				UserMedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
				UserLpuSection_id: this.userMedStaffFact.LpuSection_id,
				userMedStaffFact: this.userMedStaffFact,
				from:this.mode,
				ARMType:this.ARMType
			};
			// для новой формы записи
			params.personData = {
				PersonEvn_id: this.PersonEvn_id,
				Person_id: this.Person_id,
				Server_id: this.Server_id
				/*Person_Firname: this.PersonInfoFrame.getFieldValue('Person_Firname'),
				Person_Surname: this.PersonInfoFrame.getFieldValue('Person_Surname'),
				Person_Secname: this.PersonInfoFrame.getFieldValue('Person_Secname'),
				Person_Birthday: this.PersonInfoFrame.getFieldValue('Person_Birthday')*/
			};

			params = Ext.apply(params || {}, oparams || {});
			params[id] = object_value;

			getWnd(open_form).show(params);
		}
	},
	getGroupName: function(id) {
		var groups = [
			"Очередь",
			"Выполненные",
			"Отмененные"
		];
		if(id) {
			return groups[id-1];
		} else {
			return groups;
		}
	},
    initComponent: function() {
        var form = this;

        this.buttonPanelActions = {
            action_JourNotice: {
                handler: function() {
                    getWnd('swMessagesViewWindow').show();
                },
                iconCls: 'notice32',
                nn: 'action_JourNotice',
                text: langs('Журнал уведомлений'),
                tooltip: langs('Журнал уведомлений')
            },
			action_PersonPregnancy: {
				hidden: (!isUserGroup('OperPregnRegistry') && !isUserGroup('RegOperPregnRegistry')),
				iconCls: 'registry32',
				nn: 'action_PersonPregnancy',
				text: langs('Регистры'),
				tooltip: langs('Регистры'),
				menuAlign: 'tr?',
				menu: [{
					text: langs('Регистр беременных'),
					tooltip: langs('Регистр беременных'),
					hidden: !isPregnancyRegisterAccess(),
					handler: function(){
						getWnd('swPersonPregnancyWindow').show();
					}
				}, {
					text: langs('Роды'),
					tooltip: langs('Роды'),
					hidden: (!isUserGroup('OperPregnRegistry') && !isUserGroup('RegOperPregnRegistry')),
					handler: function(){
						getWnd('swPersonPregnancyFinishedWindow').show();
					}
				}, {
					text: langs('Аборты'),
					tooltip: langs('Аборты'),
					hidden: (!isUserGroup('OperPregnRegistry') && !isUserGroup('RegOperPregnRegistry')),
					handler: function(){
						getWnd('swPersonPregnancyInterruptedWindow').show();
					}
				}]
			}
        };

        this.onKeyDown = function (inp, e) {
            if (e.getKey() == Ext.EventObject.ENTER) {
                e.stopEvent();
                this.doSearch();
            }
        }.createDelegate(this);

        this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
            owner: form,
            filter: {
                title: langs('Фильтр'),
                layout: 'form',
                items: [{
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        labelWidth: 65,
                        items:
                            [{
                                xtype: 'textfieldpmw',
                                width: 150,
                                name: 'Person_SurName',
                                fieldLabel: langs('Фамилия'),
                                listeners: {
                                    'keydown': form.onKeyDown
                                }
                            }]
                    }, {
                        layout: 'form',
                        labelWidth: 45,
                        items:
                            [{
                                xtype: 'textfieldpmw',
                                width: 150,
                                name: 'Person_FirName',
                                fieldLabel: langs('Имя'),
                                listeners: {
                                    'keydown': form.onKeyDown
                                }
                            }]
                    }, {
                        layout: 'form',
                        labelWidth: 75,
                        items:
                            [{
                                xtype: 'textfieldpmw',
                                width: 150,
                                name: 'Person_SecName',
                                fieldLabel: langs('Отчество'),
                                listeners: {
                                    'keydown': form.onKeyDown
                                }
                            }]
                    }, {
                        layout: 'form',
                        labelWidth: 35,
                        items:
                            [{
                                xtype:'swdatefield',
                                format:'d.m.Y',
                                plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                name: 'Person_BirthDay',
                                fieldLabel: langs('ДР'),
                                listeners: {
                                    'keydown': form.onKeyDown
                                }
                            }]
                    }, {
                        layout: 'form',
                        labelWidth: 145,
                        items:
                            [{
                                xtype: 'textfield',
                                width: 100,
                                name: 'EvnDirection_Num',
                                fieldLabel: langs('Номер направления'),
                                listeners: {
                                    'keydown': form.onKeyDown
                                }
                            }]
                    }, {
						layout: 'form',
						labelWidth: 145,
						hidden: getRegionNick()=='kz',
						items: [{
							xtype: 'swlpulocalcombo',
							name: 'LpuCombo_id',
							hiddenName: 'LpuCombo_id',
							allowBlank: true,
							fieldLabel: langs('МО направления'),
							width: 450
						}]
					}

                    ]
                }, {
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        labelWidth: 160,
                        items: [{
                            xtype: 'swcommonsprcombo',
                            fieldLabel: langs('Цель консультирования'),
                            hiddenName: 'RemoteConsultCause_id',
                            width: 250,
                            comboSubject: 'RemoteConsultCause',
                            autoLoad: true,
                            listeners: {
                                'keydown': form.onKeyDown
                            }
                        }
						]
					},
					{
						layout: 'form',
                        labelWidth: 160,
						items: [{
							checkAccessRights: true,
							fieldLabel: 'Код диагноза с',
							hiddenName: 'Diag_Code_From',
							valueField: 'Diag_Code',
							width: 250,
							xtype: 'swdiagcombo'
							
						}]
					}, {
						layout: 'form',
                        labelWidth: 100,
						items: [{
							checkAccessRights: true,
							fieldLabel: 'по',
							hiddenName: 'Diag_Code_To',
							valueField: 'Diag_Code',
							width: 250,
							xtype: 'swdiagcombo'
							
						}]
					} 
					
					]
                }, 
				{
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        items:
                            [{
                                xtype: 'button',
                                id: form.id+'BtnSearch',
                                text: langs('Найти'),
                                iconCls: 'search16',
                                handler: function()
                                {
                                    form.doSearch();
                                }
                            }]
                    }, {
                        layout: 'form',
                        items:
                            [{
                                style: "padding-left: 10px",
                                xtype: 'button',
                                id: form.id+'BtnClear',
                                text: langs('Сброс'),
                                iconCls: 'reset16',
                                handler: function()
                                {
                                    form.doReset();
                                    form.doSearch('day');
                                }
                            }]
                    }]
                }]
            }
        });

        this.GridPanel = new sw.Promed.ViewFrame({
            lastLoadGridDate: null,
            auto_refresh: null,
            html: '<audio id="swWorkPlaceTelemedWindowNotification"><source src="/audio/web/WavLibraryNet_Sound5825.mp3" type="audio/mpeg"></audio>',
            id: 'WorkPlaceTelemedGridPanel',
            object: 'EvnDirection',
            region: 'center',
            autoExpandColumn: 'autoexpand',
            grouping: true,
            groupTextTpl: '{[[swWorkPlaceTelemedWindow.getGroupName(values.gvalue)]]} ({[sw.Promed.swWorkPlaceTelemedWindow.cntOrders(values)]} {[parseInt(sw.Promed.swWorkPlaceTelemedWindow.cntOrders(values).toString().charAt(sw.Promed.swWorkPlaceTelemedWindow.cntOrders(values).toString().length-1)).inlist([1]) ?"заявка" :(parseInt(sw.Promed.swWorkPlaceTelemedWindow.cntOrders(values).toString().charAt(sw.Promed.swWorkPlaceTelemedWindow.cntOrders(values).toString().length-1)).inlist([2,3,4]) ? "заявки" : "заявок")]})',
            groupingView: {
                showGroupName: false,
                showGroupsText: true
            },
            actions: [
                { name: 'action_add', text:langs('Внешнее направление'), tooltip: langs('Добавить внешнее направление'), handler: this.createDirection.createDelegate(this) },
                { name: 'action_view', hidden: true, disabled: true },
                { name: 'action_edit', text:langs('Открыть ЭМК'), tooltip: langs('Открыть электронную медицинскую карту пациента'), iconCls: 'open16', handler: this.reception.createDelegate(this) },
                { name: 'action_delete', text:langs('Отменить выполнение'), msg: langs('Все созданные документы будут удалены. Продолжить?'), url: '/?c=EvnUslugaTelemed&m=unExec' },
                { name: 'action_refresh' },
                {name:'action_print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT_TIP, icon: 'img/icons/print16.png', /*handler: function() {viewframe.printObjectList()}*/
					menuConfig: {
						printObject: {name: 'printObject', text: langs('Печать'), handler: function(){form.GridPanel.printObject();}},
						printObjectList: {name: 'printObjectList', text: langs('Печать текущей страницы'), handler: function(){form.GridPanel.printObjectList();}},
						printObjectListFull: {name: 'printObjectListFull', text: langs('Печать всего списка'), handler: function(){form.GridPanel.printObjectListFull();}},
						printObjectListSelected: {name: 'printObjectListSelected', text: langs('Печать списка выбранных'), handler: function(){form.GridPanel.printObjectListSelected();}}
						//printCostTreat: {name: 'printCostTreat', hidden: true, text: langs('Справка о стоимости лечения'), handler: function(){form.printCostTreat()}} больше не используется
					}
				}
            ],
            autoLoadData: false,
            pageSize: 20,
			groupSortInfo: {
				field: (getRegionNick() != 'kz')? 'deadDiff': 'deadLine',
				direction: (getRegionNick() != 'kz')? 'ASC': 'DESC'
			},
            stringfields: [
                { name: 'EvnDirection_id', type: 'int', key: true },
                { name: 'RootEvnClass_SysNick', type: 'string', hidden: true },
                { name: 'EvnDirection_pid', type: 'int', hidden: true },
                { name: 'EvnDirection_rid', type: 'int', hidden: true },
                { name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'isDel', type: 'int', hidden: true },
                // { name: 'Diag_id', type: 'int', hidden: true },
                { name: 'Person_id', type: 'int', hidden: true },
                { name: 'PersonEvn_id', type: 'int', hidden: true },
                { name: 'Server_id', type: 'int', hidden: true },
				{ name: 'RiskType_id', type: 'int', hidden: true },
                { name: 'EvnDirection_IsCito', header: 'Cito!', type: 'checkbox', width: 40},
                { name: 'EvnDirection_setDT', dateFormat: 'd.m.Y', type: 'date', header: langs('Дата направления'), width: 120, sort: false },
				{ name: 'evndirection_group', hidden: true, group: true },
				{ name: 'DirType_Code', hidden: true },
				{ name: 'TimetableMedService_id', hidden: true },
				{ name: 'TimetableStac_id', hidden: true },
				{ name: 'TimetableGraf_id', hidden: true },
				{ name: 'EvnQueue_id', hidden: true },
				{ name: 'EvnXml_id', hidden: true },
				{ name: 'EvnXml_signDT', hidden: true },
				{ name: 'pmUser_signName', hidden: true },
				{ name: 'deadLine', sort: false, type: 'datetime', header: langs('Предельный срок исполнения')},
				{ name: 'deadDiff', type:'int', hidden:true},
				{ name: 'EvnDirection_Num', header: langs('Номер направления'), width: 130, sort: false,
                    renderer: function(v, p, record)
                    {
                        if (!v){
                            return "";
                        }
                        return '<div class="x-grid3-dir-col-non-border-on x-grid3-cc-'
                            + this.id
                            + '" style="cursor: pointer; background-position:100%" id="WorkPlaceTelemedGridPanel_EvnDirectionCell_'
                            + record.get('EvnDirection_id')
                            + '">'
                            + record.get('EvnDirection_Num')
                            + '</div>';
                    }
                },
                { name: 'Lpu_Nick', header: langs('Направившее МО'), type: 'string', width: 120, sort: false  },
                { name: 'Person_Fin', header: langs('Врач'), type: 'string', width: 110, sort: false  },
                { name: 'Person_FIO', header: langs('ФИО Пациента'), type: 'string', width: 300, sort: false  },
                { name: 'Diag_FullName', header: 'Диагноз', type: 'string', width: 320, sort: false  },
				{ name: 'LpuSectionProfile_Name', header: langs('Профиль'), type: 'string', width: 1500, id: 'autoexpand', sort: false },
				{ name: 'RemoteConsultCause_Name', header: langs('Цель консультирования'), type: 'string', width: 200, sort: false },
				{ name: 'ConsultingForm_Name', header: langs('Режим оказания консультации '), type: 'string', width: 100, sort: false },
				{ name: 'ConsultationForm_Code', hidden: true },
				{ name: 'ConsultationForm_Name', header: langs('Форма оказания консультации'), type: 'string', width: 100, sort: false },
				{ name: 'evndirection_result', header: langs('Результат'), type: 'string', width: 420, sort: false },
				{ name: 'EvnUslugaTelemed_id', header: langs('Консультация'), width: 420, sort: false,
					renderer: function(v, p, r)
					{
						var str = '';
						if (!v && !r.get('evndirection_result')){
							return str;
						}
						var EvnUslugaTelemed_id = (r.get('EvnUslugaTelemed_id'))?r.get('EvnUslugaTelemed_id'):false;
						var EvnDirection_id = (r.get('EvnDirection_id'))?r.get('EvnDirection_id'):false;
						str = '<a style="display:block;float:left;" href="#" onClick="Ext.getCmp(\'swWorkPlaceTelemedWindow\').openEvnUslugaTelemedEditWindow('+EvnUslugaTelemed_id+','+EvnDirection_id+')">Протокол</a>';
						return str;
					}
				}, {
					header: langs('Документ подписан'),
					width: 200,
					name: 'EvnXml_IsSigned',
					renderer: function(v, p, r) {
						var s = '';
						if (!Ext.isEmpty(r.get('EvnXml_id'))) {
							if (!Ext.isEmpty(r.get('EvnXml_IsSigned'))) {
								if (r.get('EvnXml_IsSigned') == 2) {
									s += '<img src="/img/icons/emd/doc_signed.png">';
								} else {
									s += '<img src="/img/icons/emd/doc_notactual.png">';
								}

								s += r.get('EvnXml_signDT') + ' ' + r.get('pmUser_signName');
							} else {
								s += '<img src="/img/icons/emd/doc_notsigned.png">';
							}
						}
						return s;
					}
				}
            ],
            dataUrl: '/?c=EvnUslugaTelemed&m=loadWorkPlaceGrid',
            totalProperty: 'totalCount',
            title: langs('Журнал рабочего места'),
			/*onRender:function(ct, position){
				this.__proto__.onRender(ct, position);
				
			},*/
            onLoadData: function(sm, index, records) {
                if (!records) {

                }
                if (!this.getGrid().getStore().totalLength) {
                    this.getGrid().getStore().removeAll();
                }
                var expandedGroupId = this.getGrid().getView().getGroupId('0') + '-hd';
                if( Ext.get(expandedGroupId) ) {
                    this.getGrid().getView().toggleGroup(expandedGroupId, false);
                }
                this.getGrid().getStore().each(function(rec){

                    var el = Ext.get('WorkPlaceTelemedGridPanel_EvnDirectionCell_' + rec.get('EvnDirection_id'));
                    if (el) {
                        el.on('click', function(event, node){
                            var el = new Ext.Element(node),
                                parts = el && el.id.split('_');
                            form.viewEvnDirection(parts[2]);
                        });
                    }
                });
                // #157110 Звуковое оповещение пользователя о событии в системе
                if (getRegionNick() == 'ufa')
                {
                    var grid = this.getGrid();
                    grid.getStore().each(function(rec)
                    {
                        if (form.NotificationFor.includes(rec.get('ConsultationForm_Name')))
                        {
                            var direction_id = rec.get('EvnDirection_id');
                            if (direction_id && !form.DirectionList.includes(direction_id))
                            {
                                form.playNotification();
                                form.DirectionList.push(direction_id);
                            }
                        }
                    });
                    grid.lastLoadGridDate = new Date();
                    if(grid.auto_refresh)
                    {
                            clearInterval(grid.auto_refresh);
                    }
                    grid.auto_refresh = setInterval(
                        function()
                        {
                            var cur_date = new Date();
                            // если прошло более 2 минут с момента последнего обновления
                            if(grid.lastLoadGridDate.getTime() < (cur_date.getTime()-120))
                            {
                                form.doSearch();
                            }
                        }.createDelegate(grid),
                        120000
                    );
                }
            },
            onRowSelect: function(sm, idx, record){
				
                this.setActionDisabled('action_edit', (!record || !record.get('EvnDirection_id') || !record.get('Person_id')));
                this.setActionDisabled('action_delete', (!record || !record.get('EvnUslugaTelemed_id') || !record.get('Person_id')));
				//this.getAction('action_print').menu.printCostTreat.setDisabled(!record || !record.get('EvnUslugaTelemed_id'));
				this.setActionDisabled('action_deleteEvnDirection', (!record || !record.get('EvnDirection_id') || record.get('RootEvnClass_SysNick')!='EvnDirection'|| record.get('isDel')==0||record.get('EvnUslugaTelemed_id') || !record.get('Person_id')));
				this.setActionDisabled('action_cancelEvnDirection', (!record || !record.get('EvnDirection_id') || !record.get('Person_id')  || record.get('evndirection_group')==3));
			},/*
            onDblClick: function(grid, number, event){
                log(['onDblClick', event]);
                this.onEnter();
            },*/
            onEnter: function() {
                //
            }
        });
		
		this.GridPanel.getGrid().view.getRowClass = function (row, index)
		{
			var cls = '';

			if (row.get('RiskType_id') == 2) {
				cls = cls + 'x-grid-rowblue ';
			} else if (row.get('RiskType_id') > 2) {
				cls = cls + 'x-grid-rowred ';
			}

			if (getRegionNick() != 'kz' && row.get('evndirection_group') == 1) {
				var deadLineDT = new Date(row.get('deadLine'));
				if(deadLineDT < new Date()) {
					cls = 'danger';
				}

				if (row.get('ConsultationForm_Code') == '3') {
						cls += ' x-grid-rowbold';
				}
			}

			return cls;
		};
		
        sw.Promed.swWorkPlaceTelemedWindow.superclass.initComponent.apply(this, arguments);
	}
});

sw.Promed.swWorkPlaceTelemedWindow.cntOrders = function(values) {
    var cnt = 0;
    for (var i=0; i < values.rs.length; i++) {
        if (values.rs[i].data.EvnDirection_id > 0) {
            cnt++;
        }
    }
    return cnt;
};