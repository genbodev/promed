/**
* swEvnUslugaTelemedEditWindow - окно редактирования/просмотра Оказание телемедицинской услуги
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Alexander Permyakov (alexpm)
* @version      7.11.2014
* @comment      Префикс для id компонентов EUTEF (EvnUslugaTelemedEditForm)
*/

sw.Promed.swEvnUslugaTelemedEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	id: 'EvnUslugaTelemedEditWindow',
	layout: 'border',
	listeners: {
		hide: function(win) {
			var EvnXml_id = win.EvnXmlPanel.getEvnXmlId();
			if (!Ext.isEmpty(EvnXml_id)) {
				checkNeedSignature({
					EMDRegistry_ObjectName: 'EvnXml',
					EMDRegistry_ObjectID: EvnXml_id,
					callback: function() {
						win.onHide();
					}
				});
			}
			win.onHide();
		}
	},
	/*
	maximized: true,// взрывается из-за вызова this.tools.restore.hide() т.к. closable: false и this.tools.restore.hide is undefined
	maximizable: false,
	resizable: false,
	*/
	maximized: false,
	maximizable: true,
	resizable: true,
	modal: true,
	plain: true,
	width: 900,
	height: 600,
	winTitle: WND_USLUGA_TELEMED,
	checkKardioPrivilegeConsent: function(options) {
		var wnd = this;
		var Person_id = null;
		var callback = Ext.emptyFn;
		if (options && options.params && options.callback) {
			if (!Ext.isEmpty(options.params.Person_id)) {
				Person_id = options.params.Person_id;
			}
			if (typeof options.callback == 'function') {
				callback = options.callback;
			}
		}
		if (!Ext.isEmpty(Person_id)) {
			var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: langs('Получение данных о включении пациента в программу')});
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=Privilege&m=getKardioPrivilegeConsentData',
				params: {
					Person_id: Person_id
				},
				callback: function (options, success, response) {
					loadMask.hide();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.recept_edit_allowed && response_obj.recept_edit_allowed == '1') {
							if (response_obj.need_consent && response_obj.need_consent == '1' && response_obj.EvnPS_id && response_obj.EvnPS_id > 0) {
								getWnd('swPrivilegeConsentEditWindow').show({
									Person_id: Person_id,
									Evn_id: response_obj.EvnPS_id,
									EvnPS_disDate: response_obj.EvnPS_disDate,
									onSave: function(data) {
										if (!Ext.isEmpty(data.PersonPrivilege_id)) {
											callback.call(wnd, {
												open_edit_form: true
											});
										}
									}
								});
							} else {
								callback.call(wnd, {
									open_edit_form: true
								});
							}
						} else {
							var err_msg = '';
							if (response_obj.success) {
								err_msg = 'Пациент не может быть включен в програму, так как не соответствует модели пациента.  Для включения в программу у пациента должна быть КВС с  выпиской после 1 января 2019 г., в которой должны быть указаны основной или сопутствующий диагнозы и услуги, установленные приказом МЗ ПК.';
							} else {
								err_msg = 'При проверке данных пациента произошла ошибка';
							}
							sw.swMsg.alert(langs('Ошибка'), langs(err_msg));
							callback.call(wnd, {
								open_edit_form: false
							});
						}
					} else {
						callback.call(wnd, {
							open_edit_form: false
						});
					}
				}
			});
		} else {
			callback.call(wnd, {
				open_edit_form: false
			});
		}
	},
	checkKardioPrivilegeConsent: function(options) {
        var wnd = this;
        var Person_id = null;
        var callback = Ext.emptyFn;

        if (options && options.params && options.callback) {
            if (!Ext.isEmpty(options.params.Person_id)) {
                Person_id = options.params.Person_id;
            }
            if (typeof options.callback == 'function') {
                callback = options.callback;
            }
        }

        if (!Ext.isEmpty(Person_id)) {
            var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: langs('Получение данных о включении пациента в программу')});
            loadMask.show();
            Ext.Ajax.request({
                url: '/?c=Privilege&m=getKardioPrivilegeConsentData',
                params: {
                    Person_id: Person_id
                },
                callback: function (options, success, response) {
                    loadMask.hide();
                    if (success) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        if (response_obj.recept_edit_allowed && response_obj.recept_edit_allowed == '1') {
                            if (response_obj.need_consent && response_obj.need_consent == '1' && response_obj.EvnPS_id && response_obj.EvnPS_id > 0) {
                                getWnd('swPrivilegeConsentEditWindow').show({
                                    Person_id: Person_id,
                                    Evn_id: response_obj.EvnPS_id,
									EvnPS_disDate: response_obj.EvnPS_disDate,
									action: 'add',
                                    onSave: function(data) {
                                        if (!Ext.isEmpty(data.PersonPrivilege_id)) {
                                            callback.call(wnd, {
                                                open_edit_form: true
                                            });
                                        }
                                    }
                                });
                            } else {
                                callback.call(wnd, {
                                    open_edit_form: true
                                });
                            }
                        } else {
                            var err_msg = '';
                            if (response_obj.success) {
                                err_msg = 'Пациент не может быть включен в програму, так как не соответствует модели пациента.  Для включения в программу у пациента должна быть КВС с  выпиской после 1 января 2019 г., в которой должны быть указаны основной или сопутствующий диагнозы и услуги, установленные приказом МЗ ПК.';
                            } else {
                                err_msg = 'При проверке данных пациента произошла ошибка';
                            }
                            sw.swMsg.alert(langs('Ошибка'), langs(err_msg));
                            callback.call(wnd, {
                                open_edit_form: false
                            });
                        }
                    } else {
                        callback.call(wnd, {
                            open_edit_form: false
                        });
                    }
                }
            });
        } else {
            callback.call(wnd, {
                open_edit_form: false
            });
        }
    },
	doSave: function(options) {
		// options @Object
		var me = this;
		if ( me.formStatus == 'save' || me.action == 'view' ) {
			return false;
		}
		me.formStatus = 'save';

		var base_form = me.formPanel.getForm(),
			setdate_field = base_form.findField('EvnUslugaTelemed_setDate'),
			settime_field = base_form.findField('EvnUslugaTelemed_setDate'),
			diag_combo = base_form.findField('Diag_id'),
			lpu_section_combo = base_form.findField('LpuSection_uid'),
			medstafffact_combo = base_form.findField('MedStaffFact_id');

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					me.formStatus = 'edit';
					me.formPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var params = {};
		var index = null;

		params.Lpu_uid = lpu_section_combo.getFieldValue('Lpu_id');
		if ( lpu_section_combo.disabled ) {
			params.LpuSection_uid = lpu_section_combo.getValue();
		}
		if ( base_form.findField('UslugaPlace_id').disabled ) {
			params.UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue();
		}
		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		// MedPersonal_id
		base_form.findField('MedPersonal_id').setValue(null);
		index = medstafffact_combo.getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == medstafffact_combo.getValue());
		});
		if ( index >= 0 ) {
			base_form.findField('MedPersonal_id').setValue(medstafffact_combo.getStore().getAt(index).get('MedPersonal_id'));
		}

		if ( typeof options.openChildWindow == 'function' && me.action == 'add' ) {
			params.isAutoCreate = 1;
		}
		params.EvnUslugaTelemed_Kolvo = 1;

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});
		loadMask.show();
		base_form.submit({
			failure: function(result_form, action) {
				me.formStatus = 'edit';
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				me.formStatus = 'edit';
				loadMask.hide();
				if ( action.result && action.result.EvnUslugaTelemed_id > 0 ) {
					base_form.findField('EvnUslugaTelemed_id').setValue(action.result.EvnUslugaTelemed_id);
					me.EvnXmlPanel.onEvnSave();
					if ( typeof options.openChildWindow == 'function' && me.action == 'add' ) {
						options.openChildWindow();
					} else {
						/*me.FileUploadPanel.listParams = {Evn_id: action.result.EvnUslugaTelemed_id};
						me.FileUploadPanel.saveChanges();*/
						
						var set_time = settime_field.getValue();
						if ( !set_time || set_time.length == 0 ) {
							set_time = '00:00';
						}
						var data = {
							'EvnUslugaTelemed_Kolvo': 1,
							'EvnUslugaTelemed_id': base_form.findField('EvnUslugaTelemed_id').getValue(),
							'EvnUslugaTelemed_setDate': setdate_field.getValue(),
							'EvnUslugaTelemed_setTime': set_time,
							'Diag_id': diag_combo.getValue()
						};
						me.callback(data);
						me.hide();
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	initComponent: function() {
		var me = this;

		this.personPanel = new sw.Promed.PersonInformationPanelShort({
			id: 'EUTEF_PersonInformationFrame',
			region: 'north'
		});
		/*
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			id: this.id+'_FileUploadPanel',
			win: this,
			buttonAlign: 'left',
			maxHeight: 150,
			buttonLeftMargin: 100,
			labelWidth: 100,
			commentTextfieldWidth: 250,
			folder: 'evnmedia/',
			style: 'background: transparent',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});
		*/
		this.FileViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() {
					var viewFrame = me.FileViewFrame,
						base_form = me.formPanel.getForm(),
						evn_id = base_form.findField('EvnUslugaTelemed_id').getValue();
					if (evn_id && evn_id > 0) {
						viewFrame.addFile();
					} else {
						me.doSave({
							openChildWindow: function() {
								me.EvnXmlPanel.setBaseParams({
									userMedStaffFact: me.userMedStaffFact,
									Server_id: base_form.findField('Server_id').getValue(),
									Evn_id: base_form.findField('EvnUslugaTelemed_id').getValue()
								});
								viewFrame.addFile();
							}
						});
					}
				} },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', handler: function() {
					var viewFrame = me.FileViewFrame;
					var record = viewFrame.getGrid().getSelectionModel().getSelected();
					if (record && !Ext.isEmpty(record.get('EvnMediaData_id'))) {
						Ext.Ajax.request({
							url: '/?c=EvnMediaFiles&m=remove',
							callback: function(options, success, response) {
								if (success) {
									viewFrame.refreshRecords(null,0);
								}
							},
							params: {EvnMediaData_id : record.get('EvnMediaData_id')}
						});
					}
				} },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			id: me.id+'_FileViewFrame',
			pageSize: 100,
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'EvnMediaData_id', type: 'int', header: 'ID', key: true },
				{ header: lang['put'],  type: 'string', name: 'EvnMediaData_FilePath', width: 100, hidden: true },
				{ header: lang['fayl'],  type: 'string', name: 'EvnMediaData_FileName', width: 200, hidden: true },
				{ header: 'state',  type: 'string', name: 'state', width: 100, hidden: true },
				{ header: lang['fayl'],  type: 'string', name: 'EvnMediaData_FileLink', width: 200 },
				{ header: lang['kommentariy'],  type: 'string', name: 'EvnMediaData_Comment', id: 'autoexpand', width: 300 }
			],
			toolbar: true,
			addFile: function() {
				var viewFrame = this,
					base_form = me.formPanel.getForm(),
					params = {},
					Evn_id = base_form.findField('EvnUslugaTelemed_id').getValue();
				me.FileViewFrame.setParam('Evn_id', Evn_id, false);
				params.enableFileDescription = true;
				params.saveUrl = '/?c=EvnMediaFiles&m=uploadFile';
				params.saveParams = {
					Evn_id: Evn_id
				};
				params.saveParams.saveOnce = true;
				params.callback = function(data) {
					//viewFrame.refreshRecords(null,0);
					viewFrame.loadData({
						globalFilters: {
							Evn_id: Evn_id
						},
						params: {
							Evn_id: Evn_id
						},
						noFocusOnLoad: false
					});
				};
				getWnd('swFileUploadWindow').show(params);
			},
			onRowSelect: function(sm,rowIdx,record) {
				var ds_edit = (this.readOnly || Ext.isEmpty(record.get('EvnMediaData_id')) || false);
				var ds_del = (this.readOnly || Ext.isEmpty(record.get('EvnMediaData_id')));
				this.ViewActions.action_edit.setDisabled(ds_edit);
				this.ViewActions.action_delete.setDisabled(ds_del);
			},
			onCellDblClick: function(grid, rowIdx, colIdx, event) {
				return false;
			}
		});

		this.FilePanel = new sw.Promed.Panel({
			title: lang['3_faylyi'],
			id: 'EUTEF_FilePanel',
			layout: 'form',
			border: false,
			collapsible: true,
			autoHeight: true,
			items: [this.FileViewFrame],
			listeners: {
				'expand':function(panel){
					//me.FileUploadPanel.doLayout();
				}
			}
		});

		this.ReceptKardioViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() {
					var view_frame = me.ReceptKardioViewFrame,
						base_form = me.formPanel.getForm(),
						evn_id = base_form.findField('EvnUslugaTelemed_id').getValue();
					if (evn_id && evn_id > 0) {
						view_frame.openReceptKardioEditWindow('add');
					} else {
						me.doSave({
							openChildWindow: function() {
								var evn_id = base_form.findField('EvnUslugaTelemed_id').getValue();
								view_frame.setParam('Evn_id', evn_id, true);
								view_frame.openReceptKardioEditWindow('add');
							}
						});
					}
				} },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', handler: function() { me.ReceptKardioViewFrame.openReceptKardioEditWindow('view'); } },
				{ name: 'action_delete', handler: function() {
					var view_frame = me.ReceptKardioViewFrame;
					var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
					if (selected_record && !Ext.isEmpty(selected_record.get('EvnRecept_id'))) {
						//логика определения типа удаления продублирована из ЭМК
						var DeleteType = 0; //Пометка к удалению
						if (isSuperAdmin() || isLpuAdmin() || isUserGroup('ChiefLLO')) {
							DeleteType = 1;
						} else {
							if (selected_record.get('ReceptType_Code')  == 2 && selected_record.get('EvnRecept_IsSigned') != 2 && selected_record.get('EvnRecept_IsPrinted') != 2) { //Если тип рецепта - "На листе" и рецепт не подписан
								DeleteType = 1; //Удаление
							}
						}

						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									getWnd('swEvnReceptDeleteWindow').show({
										callback: function() {
											view_frame.getGrid().getStore().reload();
										},
										EvnRecept_id: selected_record.get('EvnRecept_id'),
										DeleteType: DeleteType,
										onHide: function() {}
									});
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Удалить рецепт?'),
							title: langs('Вопрос')
						});
					}
				} },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=EvnUslugaTelemed&m=loadReceptKardioPanel',
			id: me.id+'_ReceptKardioViewFrame',
			editformclassname: 'swEvnReceptRlsEditWindow',
			pageSize: 100,
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'EvnRecept_id', type: 'int', header: 'ID', key: true },
				{name: 'ReceptType_Code', hidden: true},
				{name: 'EvnRecept_IsSigned', hidden: true},
				{name: 'EvnRecept_IsPrinted', hidden: true},
				{ header: langs('Дата выписки'), type: 'string', name: 'EvnRecept_setDate', width: 100 },
				{ header: langs('Серия'), type: 'string', name: 'EvnRecept_Ser', width: 100 },
				{ header: langs('Номер'), type: 'string', name: 'EvnRecept_Num', width: 100 },
				{ header: langs('Медикамент'), type: 'string', name: 'Drug_Name', id: 'autoexpand', width: 200 },
				{ header: langs('Количество'), type: 'string', name: 'EvnRecept_Kolvo', width: 100 }
			],
			toolbar: true,
			baseParams: {
				isKardio: true
			},
			onLoadData: function() {
				if (this.getGrid().getStore().getCount() > 0) {
					this.setActionDisabled('action_refresh', false);
				} else {
					this.setActionDisabled('action_refresh', true);
				}
			},
			openReceptKardioEditWindow: function(action) {
				var
					view_frame = this,
					base_form = me.formPanel.getForm(),
					msf_combo = base_form.findField('MedStaffFact_id'),
					diag_combo = base_form.findField('Diag_id'),
					evn_id = base_form.findField('EvnUslugaTelemed_id').getValue();
				var params = new Object();
				params.action = action;
				params.isKardio = true;
				params.MedPersonal_id = msf_combo.getValue();
				params.onHide = Ext.emptyFn;
				params.callback = function() {
					view_frame.getGrid().getStore().reload();
				};
				params.EvnUslugaTelemed = 1;		//признак открытия формы Льготные рецепты из текущей формы
				if(action == 'add') {
					if(!Ext.isEmpty(evn_id)) {
						params.EvnRecept_pid = evn_id;
						params.Person_id = me.personPanel.getFieldValue('Person_id');
						params.PersonEvn_id = me.personPanel.getFieldValue('PersonEvn_id');
						params.Server_id = me.personPanel.getFieldValue('Server_id');
						params.Diag_id = diag_combo.getValue();
						//проверка и включени пациента в программу
						if (!Ext.isEmpty(params.Person_id)) {
							me.checkKardioPrivilegeConsent({
								params: {
									Person_id: params.Person_id
								},
								callback: function (data) {
									if (data.open_edit_form) {
										getWnd(view_frame.editformclassname).show(params);
									}
								}
							});
						}
					}
				} else if (action == 'view') {
					var selected_row = view_frame.getGrid().getSelectionModel().getSelected();
					var recept_id = selected_row ? selected_row.get('EvnRecept_id') : null;
					if(!Ext.isEmpty(recept_id)) {
						params.EvnRecept_id = recept_id;
						getWnd(view_frame.editformclassname).show(params);
					}
				}
			},
			onRowSelect: function(sm,rowIdx,record) {
				var ds_del = (this.readOnly || Ext.isEmpty(record.get('EvnRecept_id')));
				this.ViewActions.action_delete.setDisabled(ds_del);
			},
			onCellDblClick: function(grid, rowIdx, colIdx, event) {
				return false;
			}
		});
		this.ReceptKardioPanel = new sw.Promed.Panel({
			title: langs('4. Рецепты ЛКО Кардио'),
			id: 'EUTEF_ReceptKardioPanel',
			layout: 'form',
			border: false,
			collapsible: true,
			autoHeight: true,
			style: 'padding-top: 0.5em;',
			items: [this.ReceptKardioViewFrame]
		});

		this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
			autoHeight: true,
			border: true,
			collapsible: true,
			style: "margin-bottom: 0.5em;",
			bodyStyle: 'padding-top: 0.5em;',
			id: 'EUTEF_TemplPanel',
			layout: 'form',
			title: lang['2_protokol_udalennoy_konsultatsii'],
			ownerWin: this,
			options: {
				//XmlType_id: sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID, // нужен раздел Заключения
				XmlType_id: 1,// костыль
				EvnClass_id: 160 // документы и шаблоны только категории EvnUslugaTelemed
			},
			signEnabled: true,
			onAfterLoadData: function(panel){
				panel.expand();
				this.syncSize();
				this.doLayout();
			},
			onAfterClearViewForm: function(panel){
				//
			},
			// определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
			onBeforeCreate: function (panel, method, params) {
				if (!panel || !method || typeof panel[method] != 'function') {
					return false;
				}
				var base_form = me.formPanel.getForm();
				var evn_id_field = base_form.findField('EvnUslugaTelemed_id');
				var evn_id = evn_id_field.getValue();
				if (evn_id && evn_id > 0) {
					// услуга была создана ранее
					// все базовые параметры уже должно быть установлены
					panel[method](params);
				} else {
					me.doSave({
						openChildWindow: function() {
							panel.setBaseParams({
								userMedStaffFact: me.userMedStaffFact,
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: evn_id_field.getValue()
							});
							panel[method](params);
						}
					});
				}
				return true;
			}
		});

		this.uslugaPanel = new sw.Promed.Panel({
			autoHeight: true,
			bodyStyle: 'padding-top: 0.5em;',
			border: true,
			layout: 'form',
			style: 'margin-bottom: 0.5em;',
			title: lang['1_usluga'],
			items: [{
				fieldLabel: 'Повторная подача',
				listeners: {
					'check': function(checkbox, value) {
						if ( getRegionNick() != 'perm' ) {
							return false;
						}

						var base_form = me.formPanel.getForm();

						var
							EvnUslugaTelemed_IndexRep = parseInt(base_form.findField('EvnUslugaTelemed_IndexRep').getValue()),
							EvnUslugaTelemed_IndexRepInReg = parseInt(base_form.findField('EvnUslugaTelemed_IndexRepInReg').getValue()),
							EvnUslugaTelemed_IsPaid = parseInt(base_form.findField('EvnUslugaTelemed_IsPaid').getValue());

						var diff = EvnUslugaTelemed_IndexRepInReg - EvnUslugaTelemed_IndexRep;

						if ( EvnUslugaTelemed_IsPaid != 2 || EvnUslugaTelemed_IndexRepInReg == 0 ) {
							return false;
						}

						if ( value == true ) {
							if ( diff == 1 || diff == 2 ) {
								EvnUslugaTelemed_IndexRep = EvnUslugaTelemed_IndexRep + 2;
							}
							else if ( diff == 3 ) {
								EvnUslugaTelemed_IndexRep = EvnUslugaTelemed_IndexRep + 4;
							}
						}
						else if ( value == false ) {
							if ( diff <= 0 ) {
								EvnUslugaTelemed_IndexRep = EvnUslugaTelemed_IndexRep - 2;
							}
						}

						base_form.findField('EvnUslugaTelemed_IndexRep').setValue(EvnUslugaTelemed_IndexRep);
					}
				},
				tabIndex: TABINDEX_EUPAREF + 57,
				name: 'EvnUslugaTelemed_RepFlag',
				xtype: 'checkbox'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: lang['data_vyipolneniya'],
						format: 'd.m.Y',
						id: 'EUTEF_EvnUslugaTelemed_setDate',
						listeners: {
							'change': function(field, newValue, oldValue) {
								if (blockedDateAfterPersonDeath('personpanelid', 'EUTEF_PersonInformationFrame', field, newValue, oldValue)) {
									return false;
								}

								var base_form = me.formPanel.getForm();

								var lpu_section_combo = base_form.findField('LpuSection_uid');
								var msf_combo = base_form.findField('MedStaffFact_id');

								var lpu_section_id = lpu_section_combo.getValue();
								var med_staff_fact_id = msf_combo.getValue();
								var med_personal_id = base_form.findField('MedPersonal_id').getValue();

								lpu_section_combo.clearValue();
								msf_combo.clearValue();

								var section_filter_params = {
									allowLowLevel: 'yes',
									ids: getUserMedStaffFactData('LpuSection_id', med_personal_id)
								};

								var medstafffact_filter_params = {
									allowLowLevel: 'yes',
									ids: getUserMedStaffFactData('MedStaffFact_id', med_personal_id)
								};

								if ( newValue ) {
									section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
									medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
								}

								setLpuSectionGlobalStoreFilter(section_filter_params);
								setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

								lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
								msf_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

								if ( lpu_section_combo.getStore().getById(lpu_section_id) ) {
									lpu_section_combo.setValue(lpu_section_id);
								}

								if ( msf_combo.getStore().getById(med_staff_fact_id) ) {
									msf_combo.setValue(med_staff_fact_id);
								}

								if (getRegionNick() == 'ekb') {
									base_form.findField('Mes_id').lastQuery = 'This query sample that is not will never appear';
									base_form.findField('Mes_id').getStore().removeAll();
									base_form.findField('Mes_id').getStore().baseParams.Mes_Date = (!Ext.isEmpty(newValue) ? Ext.util.Format.date(newValue, 'd.m.Y') : getGlobalOptions().date);
									base_form.findField('Mes_id').getStore().baseParams.query = '';
								}

								me.reloadUslugaComplexField();
								me.setDiagSetPhaseFilter();
							},
							'keydown': function (inp, e) {
								if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB && false ) {
									e.stopEvent();
									me.buttons[me.buttons.length - 1].focus();
								}
							}
						},
						name: 'EvnUslugaTelemed_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
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
						name: 'EvnUslugaTelemed_setTime',
						onTriggerClick: function() {
							var base_form = me.formPanel.getForm();
							var time_field = base_form.findField('EvnUslugaTelemed_setTime');
							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								callback: function() {
									base_form.findField('EvnUslugaTelemed_setDate').fireEvent('change', base_form.findField('EvnUslugaTelemed_setDate'), base_form.findField('EvnUslugaTelemed_setDate').getValue());
								},
								dateField: base_form.findField('EvnUslugaTelemed_setDate'),
								loadMask: true,
								setDate: true,
								setTimeMaxValue: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: 'EvnUslugaTelemedEditWindow'
							});
						},
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: false,
				comboSubject: 'UslugaPlace',
				fieldLabel: lang['mesto_vyipolneniya'],
				hiddenName: 'UslugaPlace_id',
				lastQuery: '',
				width: 500,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				hiddenName: 'LpuSection_uid',
				id: 'EUTEF_LpuSectionCombo',
				lastQuery: '',
				linkedElements: ['EUTEF_MedPersonalCombo'],
				width: 500,
				xtype: 'swlpusectionglobalcombo'
			}, {
				fieldLabel: langs('МО, выполнившая услугу'),
				hiddenName: 'Org_uid',
				width: 500,
				onTrigger1Click: function() {
					var combo = this;
					var base_form = win.findById('EvnDirectionEditForm').getForm();
					var DirType_Code = base_form.findField('DirType_id').getFieldValue('DirType_Code');

					if ( combo.disabled ) {
						return false;
					}

					getWnd('swOrgSearchWindow').show({
						object: 'org',
						onClose: function() {
							combo.focus(true, 200);
						},
						onSelect: function(org_data) {
							if ( !Ext.isEmpty(org_data.Org_id) ) {
								combo.getStore().loadData([{
									Org_id: org_data.Org_id,
									Org_Name: org_data.Org_Name
								}]);
								combo.setValue(org_data.Org_id);
								combo.fireEvent('change', combo, org_data.Org_id);
								getWnd('swOrgSearchWindow').hide();
								combo.collapse();
							}
						}
					});
				},
				xtype: 'sworgcombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['vrach_vyipolnivshiy_uslugu'],
				hiddenName: 'MedStaffFact_id',
				id: 'EUTEF_MedPersonalCombo',
				lastQuery: '',
				listWidth: 750,
				parentElementId: 'EUTEF_LpuSectionCombo',
				width: 500,
				xtype: 'swmedstafffactglobalcombo'
			}, {
				fieldLabel: 'Врач, выполнивший услугу',
				name: 'MedPersonalNotPromed_Description',
				width: 500,
				xtype: 'textfield'
			}, {
				fieldLabel: 'Специальность врача',
				hiddenName: 'MedSpec_id',
				width: 500,
				xtype: 'swmedspecfedcombo'
			}, {
				allowBlank: (getRegionNick() == 'kz'),
				fieldLabel: lang['usluga'],
				hiddenName: 'UslugaComplex_id',
				listeners: {
					'change': function (combo, newValue, oldValue) {
						if (getRegionNick() == 'ekb') {
							var base_form = me.formPanel.getForm();

							base_form.findField('Mes_id').lastQuery = 'This query sample that is not will never appear';
							base_form.findField('Mes_id').getStore().removeAll();
							base_form.findField('Mes_id').getStore().baseParams.UslugaComplex_id = newValue;
							base_form.findField('Mes_id').getStore().baseParams.query = '';
						}

						return true;
					}
				},
				listWidth: 750,
				to: 'EvnUslugaTelemed',
				width: 500,
				xtype: 'swuslugacomplexnewcombo'
			}, {
				allowBlank: false,
				hiddenName: 'PayType_id',
				width: 250,
				xtype: 'swpaytypecombo'
			}, {
				allowBlank: false,
				hiddenName: 'Diag_id',
				id: 'EUTEF_DiagCombo',
				onChange: function (combo, value) {
					var
						base_form = me.formPanel.getForm(),
						diag_code = this.getFieldValue('Diag_Code');

					if (getRegionNick() == 'ekb' && !Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() != 'Z') {
						base_form.findField('DeseaseType_id').setAllowBlank(false);
					}
					else {
						base_form.findField('DeseaseType_id').setAllowBlank(true);
					}
				},
				width: 500,
				xtype: 'swdiagcombo'
			}, {
				comboSubject: 'DiagSetPhase',
				fieldLabel: 'Состояние пациента',
				hiddenName: 'DiagSetPhase_id',
				moreFields: [
					{name: 'DiagSetPhase_begDT', type: 'date', dateFormat: 'd.m.Y' },
					{name: 'DiagSetPhase_endDT', type: 'date', dateFormat: 'd.m.Y' }
				],
				width: 500,
				xtype: 'swcommonsprcombo'
			}, {
				hiddenName: 'DeseaseType_id',
				fieldLabel: 'Характер',
				width: 500,
				xtype: 'swdeseasetypecombo'
			}, {
				allowBlank: getRegionNick() != 'ekb',
				fieldLabel: 'МЭС',
				hiddenName: 'Mes_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = me.formPanel.getForm();

						base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
						base_form.findField('UslugaComplex_id').getStore().removeAll();
						base_form.findField('UslugaComplex_id').getStore().baseParams.Mes_id = newValue;
						base_form.findField('UslugaComplex_id').getStore().baseParams.query = '';
					}
				},
				width: 500,
				forceSelection: true,
				xtype: 'swmesekbcombo'
			}, {
				allowBlank: false,
				comboSubject: 'UslugaTelemedResultType',
				fieldLabel: lang['rezultat'],
				hiddenName: 'UslugaTelemedResultType_id',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						//
					},
					'render': function(combo) {
						combo.getStore().load();
					}
				},
				width: 500,
				xtype: 'swcommonsprcombo'
			}]
		});
		
		this.formPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnUslugaTelemedEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'accessType'},
				{name: 'EvnUslugaTelemed_id'},
				{name: 'EvnUslugaTelemed_IsPaid'},
				{name: 'EvnUslugaTelemed_IndexRep'},
				{name: 'EvnUslugaTelemed_IndexRepInReg'},
				{name: 'EvnDirection_id'},
				{name: 'EvnDirection_pid'},
				{name: 'EvnUslugaTelemed_setDate'},
				{name: 'EvnUslugaTelemed_setTime'},
				{name: 'UslugaPlace_id'},
				{name: 'Org_uid'},
				{name: 'LpuSection_uid'},
				{name: 'MedPersonal_id'},
				{name: 'Person_id'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'MedStaffFact_id'},
				{name: 'MedPersonalNotPromed_Description'},
				{name: 'MedSpec_id'},
				{name: 'UslugaComplex_id'},
				{name: 'PayType_id'},
				{name: 'Diag_id'},
				{name: 'DiagSetPhase_id'},
				{name: 'DeseaseType_id'},
				{name: 'Mes_id'},
				{name: 'UslugaTelemedResultType_id'},
				{name: 'EvnReceptKardio_Exists'}
			]),
			region: 'center',
			url: '/?c=EvnUslugaTelemed&m=doSave',
			items: [{
				name: 'accessType',
				xtype: 'hidden'
			}, {
				name: 'EvnDirection_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnDirection_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaTelemed_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaTelemed_IsPaid',
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaTelemed_IndexRep',
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaTelemed_IndexRepInReg',
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
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'EvnReceptKardio_Exists',
				value: 0,
				xtype: 'hidden'
			},
				this.uslugaPanel,
				this.EvnXmlPanel,
				this.FilePanel,
				this.ReceptKardioPanel
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					me.doSave({});
				},
				iconCls: 'save16',
				onShiftTabAction: function () {
					me.buttons[me.buttons.length - 1].focus();
				},
				onTabAction: function () {
					me.buttons[me.buttons.length - 1].focus();
				},
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					me.onCancelAction();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( me.action != 'view' ) {
						me.buttons[0].focus();
					}
				},
				onTabAction: function () {
					if ( me.action != 'view' ) {
						me.buttons[0].focus();
					}
				},
				text: BTN_FRMCANCEL
			}],
			items: [
				this.personPanel,
				this.formPanel
			]
		});

		this.keys = [{
			alt: true,
			fn: function(inp, e) {
				switch ( e.getKey() ) {
					case Ext.EventObject.C:
						me.doSave({});
						break;

					case Ext.EventObject.J:
						me.onCancelAction();
						break;
				}
			},
			key: [
				Ext.EventObject.C,
				Ext.EventObject.J
			],
			stopEvent: true
		}];

		sw.Promed.swEvnUslugaTelemedEditWindow.superclass.initComponent.apply(this, arguments);
	},
	onCancelAction: function() {
		var base_form = this.formPanel.getForm(),
			me = this;
		if (me.action != 'add' || !base_form.findField('EvnUslugaTelemed_id').getValue()) {
			me.hide();
		} else {
			// удалить услугу, закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(me.getEl(), {msg: "Удаление услуги..."});
			loadMask.show();
			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();
					if ( success ) {
						me.hide();
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_uslugi_voznikli_oshibki']);
					}
				},
				params: {
					id: base_form.findField('EvnDirection_id').getValue()
				},
				url: '/?c=EvnUslugaTelemed&m=unExec'
			});
		}
	},
	reloadUslugaComplexField: function(UslugaComplex_id) {
		if ( getRegionNick() == 'kz' ) {
			return false;
		}

		var win = this;
		var base_form = this.formPanel.getForm();
		var field = base_form.findField('UslugaComplex_id');

		field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(base_form.findField('EvnUslugaTelemed_setDate').getValue(), 'd.m.Y');

		// повторно грузить одно и то же не нужно
		var newUslugaComplexParams = Ext.util.JSON.encode(field.getStore().baseParams);

		if ( newUslugaComplexParams != win.lastUslugaComplexParams ) {
			win.lastUslugaComplexParams = newUslugaComplexParams;

			var currentUslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

			field.lastQuery = 'This query sample that is not will never appear';
			field.getStore().removeAll();

			var params = {};

			if ( UslugaComplex_id ) {
				params.UslugaComplex_id = UslugaComplex_id;
				currentUslugaComplex_id = UslugaComplex_id;
			}

			win.getLoadMask(lang['zagruzka_spiska_uslug']).show();

			field.getStore().load({
				callback: function (rec) {
					win.getLoadMask().hide();
					index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
						return (rec.get('UslugaComplex_id') == currentUslugaComplex_id);
					});

					if ( index >= 0 ) {
						var record = base_form.findField('UslugaComplex_id').getStore().getAt(index);
						field.setValue(record.get('UslugaComplex_id'));
						field.setRawValue(record.get('UslugaComplex_Code') + '. ' + record.get('UslugaComplex_Name'));
					}
					else {
						field.clearValue();
					}
				},
				params: params
			});
		}
	},
	setDiagSetPhaseFilter: function() {
		var base_form = this.formPanel.getForm();
		var EvnUslugaTelemed_setDate = base_form.findField('EvnUslugaTelemed_setDate').getValue();
		var DiagSetPhase_id = base_form.findField('DiagSetPhase_id').getValue();

		base_form.findField('DiagSetPhase_id').lastQuery = '';
		base_form.findField('DiagSetPhase_id').getStore().clearFilter();
		base_form.findField('DiagSetPhase_id').getStore().filterBy(function(rec) {
			return (
				(Ext.isEmpty(rec.get('DiagSetPhase_begDT')) || rec.get('DiagSetPhase_begDT') <= EvnUslugaTelemed_setDate)
				&& (Ext.isEmpty(rec.get('DiagSetPhase_endDT')) || rec.get('DiagSetPhase_endDT') >= EvnUslugaTelemed_setDate)
			);
		});

		if ( !Ext.isEmpty(DiagSetPhase_id) ) {
			base_form.findField('DiagSetPhase_id').setFieldValue('DiagSetPhase_id', DiagSetPhase_id);
		}
	},
	showReceptKardioPanel: function() {
		if (getRegionNick() == 'perm') {
			var wnd = this;
			var evn_id = wnd.formPanel.getForm().findField('EvnUslugaTelemed_id').getValue();
			var recept_kardio_exists = wnd.formPanel.getForm().findField('EvnReceptKardio_Exists').getValue();
			if (recept_kardio_exists == 1) {
				wnd.setReceptKardioPanelVisible(true);
			} else {
				Ext.Ajax.request({
					url: '/?c=EvnRecept&m=getEvnReceptKardioVisibleData',
					params: {
						parent_object: 'EvnUslugaTelemed',
						parent_object_value: evn_id,
						Lpu_id: getGlobalOptions().lpu_id
					},
					callback: function(options, success, response) {
						if (success) {
							var visible_data = Ext.util.JSON.decode(response.responseText);
							wnd.setReceptKardioPanelVisible(visible_data.is_visible);
						}
					}
				});
			}
		}
	},
	setReceptKardioPanelVisible: function(is_visible) {
		if (is_visible) {
			if (this.action != 'add') {
				var evn_id = this.formPanel.getForm().findField('EvnUslugaTelemed_id').getValue();
				this.ReceptKardioViewFrame.setParam('Evn_id', evn_id, true);
				this.ReceptKardioViewFrame.loadData();
			}
			this.ReceptKardioPanel.show();
		} else {
			this.ReceptKardioPanel.hide();
		}
	},
	setFieldsVisibility: function() {
		
		if (getRegionNick() != 'msk') return false;
		
		var me = this,
			base_form = this.formPanel.getForm(),
			pay_type_combo = base_form.findField('PayType_id'),
			org_combo = base_form.findField('Org_uid'),
			lpu_section_combo = base_form.findField('LpuSection_uid'),
			medstafffact_combo = base_form.findField('MedStaffFact_id'),
			medpersonal_description = base_form.findField('MedPersonalNotPromed_Description'),
			medspec_combo = base_form.findField('MedSpec_id');

		me.EvnXmlPanel.setVisible(!me.isNotForSystem);
		org_combo.setContainerVisible(me.isNotForSystem);
		org_combo.setAllowBlank(!me.isNotForSystem);
		lpu_section_combo.setContainerVisible(!me.isNotForSystem);
		lpu_section_combo.setAllowBlank(me.isNotForSystem);
		medstafffact_combo.setContainerVisible(!me.isNotForSystem);
		medstafffact_combo.setAllowBlank(me.isNotForSystem);
		medpersonal_description.setContainerVisible(me.isNotForSystem);
		medpersonal_description.setAllowBlank(!me.isNotForSystem);
		medspec_combo.setContainerVisible(me.isNotForSystem);
		medspec_combo.setAllowBlank(!me.isNotForSystem);
		pay_type_combo.setValue(205); // другое
		pay_type_combo.disable();
		
		var Org_oid = org_combo.getValue();
		if (Org_oid){
			org_combo.getStore().load({
				callback: function(records, options, success) {
					org_combo.clearValue();
					if ( success ) {
						org_combo.setValue(Org_oid);
					}
				},
				params: {
					Org_id: Org_oid
				}
			});
		}
	},
	show: function() {
		sw.Promed.swEvnUslugaTelemedEditWindow.superclass.show.apply(this, arguments);

		var me = this;

		this.restore();
		this.center();

		var base_form = this.formPanel.getForm(),
			setdate_field = base_form.findField('EvnUslugaTelemed_setDate'),
			settime_field = base_form.findField('EvnUslugaTelemed_setDate'),
			diag_combo = base_form.findField('Diag_id'),
			lpu_section_combo = base_form.findField('LpuSection_uid'),
			medstafffact_combo = base_form.findField('MedStaffFact_id'),
			medpersonal_field = base_form.findField('MedPersonal_id');

		base_form.findField('DeseaseType_id').setContainerVisible(getRegionNick() == 'ekb');
		base_form.findField('Mes_id').setContainerVisible(getRegionNick() == 'ekb');
		base_form.findField('UslugaComplex_id').setContainerVisible(getRegionNick() != 'kz');
		base_form.findField('UslugaPlace_id').setContainerVisible(false);
		base_form.findField('EvnUslugaTelemed_RepFlag').hideContainer();
		
		base_form.findField('Org_uid').setContainerVisible(false);
		base_form.findField('MedPersonalNotPromed_Description').setContainerVisible(false);
		base_form.findField('MedSpec_id').setContainerVisible(false);

		base_form.reset();

		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = null;
		base_form.findField('UslugaComplex_id').lastQuery = '';
		base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'telemed' ]);
		this.lastUslugaComplexParams = null;

		if ( !arguments[0] || !arguments[0].formParams || !arguments[0].formParams.Person_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {
				me.hide();
			});
			return false;
		}

		this.archiveRecord = 0;
		if (arguments[0].archiveRecord) {
			this.archiveRecord = 1; // используется арихвная запись
			if (arguments[0].action && arguments[0].action == 'edit') {
				arguments[0].action = 'view'; // режим просмотра
			}
		}
		this.action = arguments[0].action || null;
		if (typeof arguments[0].callback == 'function') {
			this.callback = arguments[0].callback;
		} else {
			this.callback = arguments[0].onSaveUsluga || Ext.emptyFn;
		}
		this.onHide = arguments[0].onHide || Ext.emptyFn;

		//this.FileUploadPanel.reset();
		this.FileViewFrame.removeAll({clearAll: true});
		this.FilePanel.collapse();
		this.EvnXmlPanel.doReset();
		this.EvnXmlPanel.collapse();
		this.EvnXmlPanel.LpuSectionField = lpu_section_combo;
		this.EvnXmlPanel.MedStaffFactField = medstafffact_combo;

		this.ReceptKardioViewFrame.removeAll();
		this.ReceptKardioViewFrame.setActionDisabled('action_add', true);
		this.ReceptKardioViewFrame.setActionDisabled('action_refresh', true);
		this.ReceptKardioPanel.hide();

		var createByXmlTemplateDefault = function() {
			if (me.EvnXmlPanel.getEvnXmlId() > 0) {
				return true;
			}
			me.EvnXmlPanel.onBeforeCreate(me.EvnXmlPanel, 'createByXmlTemplateDefault');
			return false;
		};
		this.EvnXmlPanel.un('beforeexpand', createByXmlTemplateDefault);

		this.userMedStaffFact = arguments[0].userMedStaffFact || sw.Promed.MedStaffFactByUser.last;
		this.isNotForSystem = arguments[0].isNotForSystem || false;

		this.personPanel.load({
			Person_id: arguments[0].formParams.Person_id,
			EvnDirection_pid: arguments[0].formParams.EvnDirection_pid,
			userMedStaffFact: this.userMedStaffFact,
			callback: function() {
				clearDateAfterPersonDeath('personpanelid', 'EUTEF_PersonInformationFrame', setdate_field);
				me.ReceptKardioViewFrame.setActionDisabled('action_add', this.action == 'view');
			}
		});
		setdate_field.setMinValue(undefined);

		base_form.setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				if ( !base_form.findField('EvnDirection_id').getValue() ) {
					loadMask.hide();
					me.hide();
					return false;
				}
				me.setTitle(me.winTitle + ': ' + FRM_ACTION_ADD);
				me.enableEdit(true);

				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
				base_form.findField('UslugaPlace_id').setFieldValue('UslugaPlace_Code', 1);

				me.setFieldsVisibility();
				me.EvnXmlPanel.on('beforeexpand', createByXmlTemplateDefault);

				setCurrentDateTime({
					callback: function() {
						loadMask.hide();
						setdate_field.fireEvent('change', setdate_field, setdate_field.getValue());

						if ( !setdate_field.disabled ) {
							setdate_field.focus(true, 250);
						} else {
							settime_field.focus(true, 250);
						}

						base_form.items.each(function(f) {
							f.validate();
						});
					},
					dateField: setdate_field,
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: true,
					timeField: settime_field,
					windowId: this.id
				});

				diag_combo.getStore().removeAll();

				if ( diag_combo.getValue() ) {
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_combo.getValue() ) {
									diag_combo.setValue(diag_combo.getValue());
									diag_combo.fireEvent('select', diag_combo, record, 0);
									diag_combo.fireEvent('change', diag_combo, diag_combo.getValue());
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue() }
					});
				}
				me.FileViewFrame.setParam('Evn_id', null, false);
				me.showReceptKardioPanel();
			break;

			case 'edit':
			case 'view':
				var id = base_form.findField('EvnUslugaTelemed_id').getValue();
				if ( !id ) {
					loadMask.hide();
					me.hide();
					return false;
				}
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {me.hide();} );
					},
					params: {
						EvnUslugaTelemed_id: id,
						archiveRecord: me.archiveRecord
					},
					success: function(form, action) {
						var response = Ext.util.JSON.decode(action.response.responseText);
						if (getRegionNick() == 'msk' &&	response[0].isNotForSystem == 2) {
							me.isNotForSystem = true;
						}
						//Проверяем возможность редактирования документа
						if (me.action === 'edit') {
							Ext.Ajax.request({
								failure: function (response, options) {
									sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
										this.hide();
									}.createDelegate(this));
								},
								params: {
									Evn_id: id,
									isForm: 'EvnUslugaTelemedEditForm',
									MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
									ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
								},
								success: function (response, options) {
									if (!Ext.isEmpty(response.responseText)) {
										var response_obj = Ext.util.JSON.decode(response.responseText);

										if (response_obj.success == false) {
											sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_zagruzke_dannyih_formyi']);
											me.action = 'view';
										}
									}
									onShow();
								}.createDelegate(this),
								url: '/?c=Evn&m=CommonChecksForEdit'
							});
						} else {
							onShow();
						}

						function onShow(){
							// В зависимости от accessType переопределяем this.action
							if ( base_form.findField('accessType').getValue() == 'view' ) {
								me.action = 'view';
							}

							if ( me.action == 'edit' ) {
								me.setTitle(me.winTitle + ': ' + FRM_ACTION_EDIT);
								me.enableEdit(true);
							} else {
								me.setTitle(me.winTitle + ': ' + FRM_ACTION_VIEW);
								me.enableEdit(false);
							}

							//загружаем файлы
							me.FileViewFrame.loadData({
								globalFilters: {
									Evn_id: id
								},
								params: {
									Evn_id: id
								},
								noFocusOnLoad:true
							});
							/*me.FileUploadPanel.listParams = {
								Evn_id: id
							};
							me.FileUploadPanel.loadData({
								Evn_id: id
							});*/

							if ( getRegionNick() == 'perm' && base_form.findField('EvnUslugaTelemed_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnUslugaTelemed_IndexRepInReg').getValue()) > 0 ) {
								base_form.findField('EvnUslugaTelemed_RepFlag').showContainer();

								if ( parseInt(base_form.findField('EvnUslugaTelemed_IndexRep').getValue()) >= parseInt(base_form.findField('EvnUslugaTelemed_IndexRepInReg').getValue()) ) {
									base_form.findField('EvnUslugaTelemed_RepFlag').setValue(true);
								}
								else {
									base_form.findField('EvnUslugaTelemed_RepFlag').setValue(false);
								}
							}

							if (getRegionNick() == 'ekb') {
								base_form.findField('Mes_id').getStore().baseParams.Mes_Date = (!Ext.isEmpty(setdate_field.getValue()) ? Ext.util.Format.date(setdate_field.getValue(), 'd.m.Y') : getGlobalOptions().date);
								base_form.findField('Mes_id').getStore().baseParams.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
								base_form.findField('UslugaComplex_id').getStore().baseParams.Mes_id = base_form.findField('Mes_id').getValue();
							}

							me.setFieldsVisibility();
							
							me.EvnXmlPanel.setReadOnly('view' == me.action);
							me.EvnXmlPanel.setBaseParams({
								userMedStaffFact: me.userMedStaffFact,
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: base_form.findField('EvnUslugaTelemed_id').getValue()
							});
							me.EvnXmlPanel.doLoadData();

							me.ReceptKardioViewFrame.setReadOnly('view' == me.action);
							me.showReceptKardioPanel();

							var loadMsfCombo = function() {
								setdate_field.fireEvent('change', setdate_field, setdate_field.getValue());
							};

							if ( me.action == 'edit' ) {
								setCurrentDateTime({
									callback: function() {
										loadMsfCombo();
									},
									dateField: setdate_field,
									loadMask: false,
									setDate: false,
									setTimeMaxValue: true,
									setDateMaxValue: true,
									windowId: me.id,
									timeField: settime_field
								});
							}
							else {
								loadMsfCombo();
							}

							if ( !Ext.isEmpty(base_form.findField('UslugaComplex_id').getValue()) ) {
								me.reloadUslugaComplexField(base_form.findField('UslugaComplex_id').getValue());
							}

							diag_combo.getStore().removeAll();

							if ( diag_combo.getValue() ) {
								diag_combo.getStore().load({
									callback: function() {
										diag_combo.getStore().each(function(record) {
											if ( record.get('Diag_id') == diag_combo.getValue() ) {
												diag_combo.setValue(diag_combo.getValue());
												diag_combo.fireEvent('select', diag_combo, record, 0);
												diag_combo.fireEvent('change', diag_combo, diag_combo.getValue());
											}
										});
									},
									params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue() }
								});
							}

							if (!Ext.isEmpty(base_form.findField('Mes_id').getValue())) {
								var mes_id = base_form.findField('Mes_id').getValue();
								base_form.findField('Mes_id').clearValue();
								base_form.findField('Mes_id').getStore().load({
									params: {
										Mes_id: mes_id
									},
									callback: function () {
										if (base_form.findField('Mes_id').getStore().getCount() > 0) {
											base_form.findField('Mes_id').setValue(mes_id);
											base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), base_form.findField('Mes_id').getValue());
										}
									}
								});
							}

							if ( me.action == 'edit' ) {
								setdate_field.focus(true, 250);
							}
							else {
								me.buttons[me.buttons.length - 1].focus();
							}
							loadMask.hide();
						}
					},
					url: '/?c=EvnUslugaTelemed&m=loadEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
				return false;
			break;
		}

		return true;
	}
});
