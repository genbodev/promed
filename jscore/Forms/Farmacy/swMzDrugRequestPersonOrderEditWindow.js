/**
* swMzDrugRequestPersonOrderEditWindow - окно редактирования строки персональной разнарядки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      07.2015
* @comment      
*/
sw.Promed.swMzDrugRequestPersonOrderEditWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 394,
	title: lang['stroka_personalnoy_raznaryadki'],
	layout: 'border',
	id: 'MzDrugRequestPersonOrderEditWindow',
	modal: true,
	shim: false,
	width: 640,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	setWindowHeight: function() {
		var h = this.default_height;
		if (!this.tradenames_combo.ownerCt.hidden) {
			h += 42;
		}
		
		if (!this.protokolVK_combo.ownerCt.ownerCt.hidden) {
			h += 42;
		}
		this.setHeight(h);
	},
    askConfirm: function(msg, callback) {
        sw.swMsg.show({
            icon: Ext.MessageBox.QUESTION,
            msg: msg,
            title: 'Вопрос',
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ('yes' == buttonId) {
                    callback();
                }
            }
        });
    },
	setEvnReceptKolvo: function(data) {
		var wnd = this;
		var mnn_id = data && !Ext.isEmpty(data.DrugComplexMnn_id) ? data.DrugComplexMnn_id : this.mnn_combo.getValue();
		var tn_id = data && !Ext.isEmpty(data.Tradenames_id) ? data.Tradenames_id : this.tradenames_combo.getValue();

		if (mnn_id > 0 || tn_id > 0) {
			Ext.Ajax.request({
				callback: function(options, success, response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result) {
						var kolvo_field = wnd.form.findField('DrugRequestPersonOrder_OrdKolvo');
						var recept_kolvo = result.EvnRecept_SumKolvo > 0 ? result.EvnRecept_SumKolvo : null;

						wnd.form.findField('DrugRequestPersonOrder_Kolvo').setValue(recept_kolvo);
						if (Ext.isEmpty(kolvo_field.getValue()) && !Ext.isEmpty(recept_kolvo)) {
							kolvo_field.setValue(recept_kolvo);
						}
					}
				},
				params: {
					DrugRequest_id: wnd.DrugRequest_id,
					Person_id: wnd.Person_id,
					DrugComplexMnn_id: mnn_id,
					Tradenames_id: tn_id
				},
				url: '/?c=MzDrugRequest&m=getEvnReceptSumKolvoByParams'
			});
		}
	},
	loadContextData: function() {
		var wnd = this;

		if (wnd.DrugRequest_id > 0 && wnd.Person_id > 0) {
			Ext.Ajax.request({
				callback: function(options, success, response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result) {
						wnd.form.findField('Lpu_Information').setValue(result.Lpu_Information);
						wnd.form.findField('DrugRequest_Information').setValue(result.DrugRequest_Information);
					}
				},
				params: {
					DrugRequest_id: wnd.DrugRequest_id,
					Person_id: wnd.Person_id
				},
				url: '/?c=MzDrugRequest&m=getDrugRequestPersonOrderContext'
			});
		}
	},
    checkExistPersonDrugInRegionFirstCopy: function(person_id, dcm_id, tn_id, callback) {
        Ext.Ajax.request({
            url: '/?c=MzDrugRequest&m=checkExistPersonDrugInRegionFirstCopy',
            params: {
                DrugRequestFirstCopy_id: this.DrugRequestFirstCopy_id,
                Person_id: person_id,
                DrugComplexMnn_id: dcm_id,
                Tradenames_id: tn_id
            },
            callback: function(options, success, response) {
                if (success) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    callback(result);
                }
            }
        });
    },
    checkBeforeSubmit: function(callback) {
        var wnd = this;
        var DrugComplexMnn_id = wnd.mnn_combo.getValue();
        var Tradenames_id = wnd.tradenames_combo.getValue();
        var kolvo = wnd.form.findField('DrugRequestPersonOrder_OrdKolvo').getValue();

        if (!Ext.isEmpty(wnd.DrugRequestFirstCopy_id)) {
            wnd.checkExistPersonDrugInRegionFirstCopy(wnd.Person_id, DrugComplexMnn_id, Tradenames_id, function(check_data) {
                if (check_data && check_data.drpo_cnt != undefined) {
                    if (check_data.drpo_cnt == 0) {
                        wnd.askConfirm('Медикамент не был включен в реальную потребность пациента. Медикамент может быть добавлен в реальную потребность без возможности удаления.  Добавить медикамент?', function() {callback();})
                    } else if (kolvo > check_data.drpo_kolvo) {
                        sw.swMsg.alert(langs('Ошибка'), langs('Количество в заявке не может превышать количество, указанное в заявке с реальной потребностью'));
                    } else {
                        callback();
                    }
                } else {
                    sw.swMsg.alert(langs('Ошибка'), langs('При проверке данных о реальной потребности произошла ошибка'));
                }
            });
        } else {
            callback();
        }
    },
	doSave:  function() {
		var wnd = this;

		if (!wnd.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.base_form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        /*Ext.Ajax.request({
            params:{
                DrugRequestPersonOrder_OrdKolvo: wnd.form.findField('DrugRequestPersonOrder_OrdKolvo').getValue(),
                DrugComplexMnn_id: wnd.mnn_combo.getValue(),
                Tradenames_id: wnd.tradenames_combo.getValue(),
                DrugRequest_id: wnd.DrugRequest_id,
                MedPersonal_id: getGlobalOptions().medpersonal_id > 0 ? getGlobalOptions().medpersonal_id : wnd.MedPersonal_id,
                DrugRequestPersonOrder_id: wnd.DrugRequestPersonOrder_id
            },
            success: function (response) {
                var result = Ext.util.JSON.decode(response.responseText);
                if(result && result[0] && typeof result[0] == 'object' && result[0].hasOwnProperty('distinction')){
                    if(result[0].distinction > 0 || result[0].distinction === 0){
                        wnd.checkBeforeSubmit(function(){wnd.submit()});
					} else {
						sw.swMsg.alert(lang['oshibka'], 'В разнарядку включено медикаментов больше, чем есть в резерве врача заявки. Уменьшите количество ЛС в разнарядке');	
						return false;
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], 'Ошибка при проверке количества ЛС в заявке');		
                    wnd.checkBeforeSubmit(function(){wnd.submit()});
                }
                return true;
            },
            url:'/?c=MzDrugRequest&m=checkDrugAmount'
        });*/
        wnd.checkBeforeSubmit(function(){wnd.submit()});

        return true;
    },
	submit: function() {
		var wnd = this;
		wnd.getLoadMask(lang['podojdite_idet_sohranenie']).show();

		var params = new Object();
		params.DrugRequest_id = wnd.DrugRequest_id;
		params.DrugRequestFirstCopy_id = wnd.DrugRequestFirstCopy_id;
		params.DrugRequestPersonOrder_id = wnd.DrugRequestPersonOrder_id;
		params.Person_id = wnd.Person_id;
		params.MedPersonal_id = getGlobalOptions().medpersonal_id > 0 ? getGlobalOptions().medpersonal_id : wnd.MedPersonal_id;
		params.DrugComplexMnn_id = wnd.mnn_combo.getValue();
		params.Tradenames_id = wnd.tradenames_combo.getValue();
		if (!params.Tradenames_id) {
			wnd.protokolVK_combo.setValue(null);
		}
		params.EvnVK_id = wnd.protokolVK_combo.getValue();
		params.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
		params.DrugRequestPersonOrder_Kolvo = wnd.form.findField('DrugRequestPersonOrder_Kolvo').getValue();
		params.DrugRequestPersonOrder_OrdKolvo = wnd.form.findField('DrugRequestPersonOrder_OrdKolvo').getValue();
		params.DrugRequestPersonOrder_begDate = wnd.form.findField('DrugRequestPersonOrder_DateRange').getValue1() ? wnd.form.findField('DrugRequestPersonOrder_DateRange').getValue1().dateFormat('d.m.Y') : '';
		params.DrugRequestPersonOrder_endDate = wnd.form.findField('DrugRequestPersonOrder_DateRange').getValue2() ? wnd.form.findField('DrugRequestPersonOrder_DateRange').getValue2().dateFormat('d.m.Y') : '';
		params.DrugRequestExceptionType_id = wnd.form.findField('DrugRequestExceptionType_id').getValue();

		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.DrugRequestPersonOrder_id > 0) {
					wnd.DrugRequestPersonOrder_id = action.result.DrugRequestPersonOrder_id;
				}
				if (typeof wnd.callback == 'function' ) {
					wnd.callback(wnd.owner, action.result.DrugRequestPersonOrder_id);
				}
				if (typeof wnd.onSave == 'function' ) {
					wnd.onSave();
				}
			}
		});
	},
	setDisabled: function(disable) {
		var wnd = this;

		var field_arr = [
			'DrugComplexMnn_id',
			'TRADENAMES_id',
			'DrugRequestPersonOrder_OrdKolvo',
			'DrugRequestPersonOrder_DateRange',
			'DrugRequestExceptionType_id',
			'DrugFinance_id',
			'EvnVK_id'
		];

		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			var combo = wnd.form.findField(field_arr[i]);
			if (disable || combo.enable_blocked || wnd.form.enable_blocked || (field_arr[i] == 'DrugFinance_id' && wnd.action != 'add')) {
				combo.disable();
			} else {
				combo.enable();
			}
		}

		if (disable) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}
	},
	show: function() {
        var wnd = this;
		sw.Promed.swMzDrugRequestPersonOrderEditWindow.superclass.show.apply(this, arguments);

		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.onSave = Ext.emptyFn;
		this.DrugRequest_id = null;
		this.DrugRequestFirstCopy_id = null;
		this.DrugRequestStatus_Code = null;
		this.MedPersonal_id = null;
		this.DrugRequestPersonOrder_id = null;
		this.Person_id = null;
		this.DrugComplexMnn_id = null;
		this.Tradenames_id = null;
		this.PersonRegisterType_id = null;
		this.DrugFinance_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugRequest_id ) {
			this.DrugRequest_id = arguments[0].DrugRequest_id;
		}
		if ( arguments[0].DrugRequestFirstCopy_id ) {
			this.DrugRequestFirstCopy_id = arguments[0].DrugRequestFirstCopy_id;
		}
		if ( arguments[0].DrugRequestStatus_Code ) {
			this.DrugRequestStatus_Code = arguments[0].DrugRequestStatus_Code;
		}
		if ( arguments[0].MedPersonal_id ) {
			this.MedPersonal_id = arguments[0].MedPersonal_id;
		}
		if ( arguments[0].DrugRequestPersonOrder_id ) {
			this.DrugRequestPersonOrder_id = arguments[0].DrugRequestPersonOrder_id;
		}
		if ( arguments[0].Person_id ) {
			this.Person_id = arguments[0].Person_id;
		}
		if ( arguments[0].DrugComplexMnn_id ) {
			this.DrugComplexMnn_id = arguments[0].DrugComplexMnn_id;
		}
		if ( arguments[0].Tradenames_id ) {
			this.Tradenames_id = arguments[0].Tradenames_id;
		}
		if ( arguments[0].PersonRegisterType_id ) {
			this.PersonRegisterType_id = arguments[0].PersonRegisterType_id;
		}
		if ( arguments[0].DrugFinance_id ) {
			this.DrugFinance_id = arguments[0].DrugFinance_id;
		}

		this.form.reset();
		this.form.findField('DrugRequestExceptionType_id').setAllowBlank(true);

		wnd.mnn_combo.store.baseParams.DrugRequest_id = wnd.DrugRequest_id;
		wnd.mnn_combo.store.baseParams.DrugFinance_id = null;
		wnd.tradenames_combo.store.baseParams.DrugRequest_id = wnd.DrugRequest_id;
		wnd.tradenames_combo.store.baseParams.DrugCommplexMnn_id = null;
		//wnd.tradenames_combo.store.baseParams.fromPersonOrder = 1;

		wnd.default_height = 371;
		wnd.protokolVK_combo.show(false);
		wnd.tradenames_combo.show(false); //не только скрывает комбо, но и устанавливает высоту окна по умолчанию

		wnd.mnn_combo.enable_blocked = wnd.action != 'add';
		wnd.tradenames_combo.enable_blocked = wnd.action != 'add';
		wnd.protokolVK_combo.enable_blocked = wnd.action != 'add';
		wnd.setDisabled(wnd.action == 'view');
		wnd.form.findField('DrugFinance_id').getStore().load({
			callback: function(){
				if(wnd.PersonRegisterType_id && wnd.PersonRegisterType_id == 1 && wnd.Person_id > 0){
					
					Ext.Ajax.request({
						params:{
							Person_id: wnd.Person_id
						},
						success: function (response) {
							var result = Ext.util.JSON.decode(response.responseText);
							var finTypes = new Array();
							var fed = false;
							var reg = false;
							if(result && result[0] && result[0].DrugFinance_id){
								for(var i = 0;i<result.length;i++){
									var res = result[i].DrugFinance_id;
									if(res == 3 && !fed){
										fed = true;
										finTypes.push(res);
									} else if(res == 27 && !reg){
										reg = true;
										finTypes.push(res);
									}
								}
							}
							switch (finTypes.length){
								case 0:
									sw.swMsg.alert(lang['oshibka'], 'У пациента нет льгот, добавление медикаментов не доступно', function() { wnd.hide(); });
									return false;
									//wnd.form.findField('DrugFinance_id').getStore().removeAll();
									break;
								case 1:
									wnd.form.findField('DrugFinance_id').getStore().load({
										params: {where:" where DrugFinance_id = "+finTypes[0]},
										callback: function(){
											if(wnd.action == 'add'){
                                                wnd.form.findField('DrugFinance_id').enable();
												wnd.form.findField('DrugFinance_id').setValue(finTypes[0]);
												wnd.form.findField('DrugFinance_id').fireEvent('change',wnd.form.findField('DrugFinance_id'),finTypes[0]);
											}
										}
									});
									break;
								case 2:
									wnd.form.findField('DrugFinance_id').getStore().load({
										params: {where:" where DrugFinance_id = "+finTypes[0]+" or DrugFinance_id = "+finTypes[1]},
										callback: function(){
                                            if(wnd.action == 'add'){
											    wnd.form.findField('DrugFinance_id').enable();
                                            }
										}
									});
									break;
							}
						},
						url:'/?c=MzDrugRequest&m=getPersonPrivilegeData'
					});
				} else {
					wnd.form.findField('DrugFinance_id').disable();
				}
			}
		});

		wnd.protokolVK_combo.store.baseParams.Person_id = wnd.Person_id;
		wnd.protokolVK_combo.store.baseParams.CauseTreatmentType_id = 7;
		wnd.protokolVK_combo.setValue(null);
		wnd.protokolVK_combo.store.removeAll()
		wnd.protokolVK_combo.show(false);
		wnd.protokolVK_combo.setAllowBlank(true);

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
		loadMask.show();
		switch (wnd.action) {		
			case 'add':
				wnd.PersonPanel.load({
					Person_id: wnd.Person_id
				});
				wnd.loadContextData();
				if (wnd.DrugComplexMnn_id > 0) {
					wnd.tradenames_combo.store.baseParams.DrugComplexMnn_id = wnd.DrugComplexMnn_id;
					//wnd.tradenames_combo.store.baseParams.fromPersonOrder = 1;

					wnd.mnn_combo.store.load({callback: function() {
						var idx = wnd.mnn_combo.getStore().findBy(function(rec) { return rec.get('DrugComplexMnn_id') == wnd.DrugComplexMnn_id; });

						if (idx >= 0) {
							wnd.mnn_combo.setValue(wnd.DrugComplexMnn_id);
                            wnd.mnn_combo.setLinkedFields(wnd.mnn_combo.getStore().getAt(idx));
						} else {
							wnd.mnn_combo.setValue(null);
                            wnd.mnn_combo.setLinkedFields(null);
						}

						//wnd.mnn_combo.focus();
						//wnd.mnn_combo.collapse();
					}});

					wnd.tradenames_combo.store.load({callback: function() {
						if (wnd.tradenames_combo.getStore().findBy(function(rec) { return rec.get('TRADENAMES_ID') == wnd.Tradenames_id; }) >= 0) {
							wnd.tradenames_combo.setValue(wnd.Tradenames_id);
						} else {
							wnd.tradenames_combo.setValue(null);
						}
						wnd.tradenames_combo.show(wnd.tradenames_combo.getStore().getCount() > 0);
						wnd.tradenames_combo.collapse();
						if (wnd.Person_id && getRegionNick() == 'ufa') {
							wnd.protokolVK_combo.show(wnd.tradenames_combo.getValue());
							wnd.protokolVK_combo.setAllowBlank(!wnd.tradenames_combo.getValue());
						}
					}});
				} else {
					wnd.mnn_combo.store.load({callback: function() {
						wnd.mnn_combo.focus();
						wnd.mnn_combo.collapse();
					}});
					wnd.tradenames_combo.store.load({callback: function() {
						wnd.tradenames_combo.collapse();
					}});
				}

				if (wnd.Person_id)  {
					wnd.protokolVK_combo.store.load({callback: function() {
						if (wnd.protokolVK_combo.getStore().getCount() > 0) {
							if (wnd.protokolVK_combo.getStore().getCount() == 1) {
									var val = wnd.protokolVK_combo.getStore().data.items[0].id
									wnd.protokolVK_combo.setValue(val);
							}
							else wnd.protokolVK_combo.setValue(null);	
							wnd.protokolVK_combo.show(wnd.tradenames_combo.getValue());
						}
					}});
				} 
				wnd.form.findField('DrugFinance_id').setValue(wnd.DrugFinance_id);
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						DrugRequestPersonOrder_id: wnd.DrugRequestPersonOrder_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);

						if (!result[0]) {
							return false
						}

						var mnn_id = result[0].DrugComplexMnn_id;
						var torg_id = result[0].Tradenames_id;
						var vk_id = result[0].EvnVK_id;

						wnd.form.setValues(result[0]);
						wnd.tradenames_combo.store.baseParams.DrugComplexMnn_id = mnn_id;
						//wnd.tradenames_combo.store.baseParams.fromPersonOrder = 1;

						wnd.mnn_combo.store.load({callback: function() {
							var idx = wnd.mnn_combo.getStore().findBy(function(rec) { return rec.get('DrugComplexMnn_id') == mnn_id; });

							if (idx >= 0) {
								wnd.mnn_combo.setValue(mnn_id);
							} else {
								wnd.mnn_combo.setValue(null);
							}

							wnd.mnn_combo.focus();
							wnd.mnn_combo.collapse();
						}});

						wnd.tradenames_combo.store.load({callback: function() {
							if (wnd.tradenames_combo.getStore().findBy(function(rec) { return rec.get('TRADENAMES_ID') == torg_id; }) >= 0) {
								wnd.tradenames_combo.setValue(torg_id);
							} else {
								wnd.tradenames_combo.setValue(null);
							}
							wnd.tradenames_combo.show(wnd.tradenames_combo.getStore().getCount() > 0);
							wnd.tradenames_combo.collapse();
						}});

						if (wnd.Person_id)  {
							wnd.protokolVK_combo.store.load({callback: function() {
								wnd.protokolVK_combo.show(wnd.protokolVK_combo.getStore().getCount() > 0 && torg_id);
								if (wnd.protokolVK_combo.getStore().findBy(function(rec) { return rec.get('EvnVK_id') == vk_id; }) >= 0) {
								wnd.protokolVK_combo.setValue(vk_id);
							} else {
								wnd.protokolVK_combo.setValue(null);
								//wnd.protokolVK_combo.show(false);
							}
						}});
						}

						if (!Ext.isEmpty(result[0].DrugRequestPersonOrder_begDate) || !Ext.isEmpty(result[0].DrugRequestPersonOrder_endDate)) {
							var date_str = '';
							if (!Ext.isEmpty(result[0].DrugRequestPersonOrder_begDate)) {
								date_str = result[0].DrugRequestPersonOrder_begDate;
							}
							if (!Ext.isEmpty(result[0].DrugRequestPersonOrder_endDate)) {
								date_str += ' - ' + result[0].DrugRequestPersonOrder_endDate;
								wnd.form.findField('DrugRequestExceptionType_id').setAllowBlank(false);
							}
							wnd.form.findField('DrugRequestPersonOrder_DateRange').setValue(date_str);
						}

						wnd.Person_id = result[0].Person_id;
						wnd.PersonPanel.load({
							Person_id: result[0].Person_id
						});
						wnd.loadContextData();
						loadMask.hide();
					},
					url:'/?c=MzDrugRequest&m=loadDrugRequestPersonOrder'
				});

			break;	
		}
	},
	initComponent: function() {
		var wnd = this;

		wnd.PersonPanel = new sw.Promed.PersonInformationPanelShort({
			id: 'mdrpoe_PersonPanel',
			region: 'north'
		});

		wnd.mnn_combo = new Ext.form.ComboBox({
			anchor: '100%',
			allowBlank: false,
			displayField: 'DrugComplexMnn_RusName',
			enableKeyEvents: true,
			fieldLabel: lang['naimenovanie_ls'],
			forceSelection: false,
			hiddenName: 'DrugComplexMnn_id',
			loadingText: lang['idet_poisk'],
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			mode: 'remote',
			onTrigger2Click: Ext.emptyFn,
			resizable: true,
			selectOnFocus: true,
			triggerAction: 'all',
			valueField: 'DrugComplexMnn_id',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item"{[values.DrugListRequest_IsProblem > 0 ? " style=\'color: #ff0000\'" : ""]}>',
				'<span style="float:left;">{DrugComplexMnn_Price}&nbsp;р.&nbsp;&nbsp;</span><h3>{DrugComplexMnn_RusName}</h3>',
				'</div></tpl>'
			),
			listeners: {
				'select': function(combo, record, index) {
					var tnc_store = wnd.tradenames_combo.getStore();
					tnc_store.baseParams.DrugComplexMnn_id = combo.getValue();
					if (wnd.Person_id) {  //  Если персональная разнорядка, то даем право на торговое наименование
					tnc_store.load({callback: function() {
						if (tnc_store.findBy(function(rec) { return rec.get('TRADENAMES_ID') == combo.getValue(); }) < 0) {
							wnd.tradenames_combo.setValue(null);
							wnd.setEvnReceptKolvo();
						}
						wnd.tradenames_combo.show(wnd.tradenames_combo.getStore().getCount() > 0);
						wnd.tradenames_combo.focus();
						wnd.tradenames_combo.collapse();
					}});
                    this.setLinkedFields(record);
					}
				},
                'change':  function(combo, newValue, oldValue) {
                    if (newValue < 1) {
                        combo.setLinkedFields(null);
                    }
                }
			},
            setLinkedFields: function(record) {
                wnd.form.findField('DrugListRequest_Comment').setValue(null);

                if (record) {
                    wnd.form.findField('DrugListRequest_Comment').setValue(record.get('DrugListRequest_Comment'));
                }
            },
			initComponent: function() {
				sw.Promed.SwDrugComplexMnnCombo.superclass.initComponent.apply(this, arguments);
				this.store = new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'DrugComplexMnn_id'
						},
						[
							{name: 'DrugComplexMnn_id', mapping: 'DrugComplexMnn_id'},
							{name: 'DrugComplexMnn_Price', mapping: 'DrugComplexMnn_Price'},
							{name: 'DrugListRequest_IsProblem', mapping: 'DrugListRequest_IsProblem'},
							{name: 'DrugListRequest_Comment', mapping: 'DrugListRequest_Comment'},
							{name: 'ATX_Code', mapping: 'ATX_Code'},
							{name: 'DrugComplexMnn_RusName', mapping: 'DrugComplexMnn_RusName'},
							{name: 'DrugComplexMnnName_Name', mapping: 'DrugComplexMnnName_Name'},
							{name: 'ClsDrugForms_Name', mapping: 'ClsDrugForms_Name'},
							{name: 'DrugComplexMnnDose_Name', mapping: 'DrugComplexMnnDose_Name'},
							{name: 'DrugComplexMnnFas_Name', mapping: 'DrugComplexMnnFas_Name'}
						]),
					url: '/?c=MzDrugRequest&m=loadDrugComplexMnnCombo'
				});
			}
		});
		this.protokolVK_combo = new Ext.form.ComboBox({
			fieldLabel: langs('Протокол ВК'),
			hiddenName: 'EvnVK_id',
			displayField:'protokolVK_name',
			valueField: 'EvnVK_id',
			anchor: '100%',
			allowBlank: true,
			enableKeyEvents: true,
			forceSelection: false,
			loadingText: langs('Идет поиск...'),
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			mode: 'remote',
			onTrigger2Click: Ext.emptyFn,
			resizable: true,
			selectOnFocus: true,
			triggerAction: 'all',
			initComponent: function() {
				Ext.form.ComboBox.superclass.initComponent.apply(this, arguments);
				this.store = new Ext.data.JsonStore({
					url: '/?c=MzDrugRequest&m=loadProtokolVKCombo',
					key: 'EvnVK_id',
					autoLoad: false,
					fields: [
						{name: 'EvnVK_id', type:'int'},
						{name: 'protokolVK_name', type:'string'},
						{name: 'EvnVK_NumProtocol', type:'int'},
						{name: 'EvnVK_setDate', type:'date'},
						{name: 'CauseTreatmentType_id', type:'int'}
					]
				});
			},
			show: function(show) {
				if (show) {
					this.ownerCt.ownerCt.show();
				} else {
					this.setAllowBlank(true)
					this.ownerCt.ownerCt.hide();
				}
				wnd.setWindowHeight();
			}
		});

		this.tradenames_combo = new Ext.form.ComboBox({
			fieldLabel: lang['torg_naimenovanie'],
			hiddenName: 'TRADENAMES_id',
			displayField:'NAME',
			valueField: 'TRADENAMES_ID',
			anchor: '100%',
			allowBlank: true,
			enableKeyEvents: true,
			forceSelection: false,
			loadingText: lang['idet_poisk'],
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			mode: 'remote',
			onTrigger2Click: Ext.emptyFn,
			resizable: true,
			selectOnFocus: true,
			triggerAction: 'all',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item"{[values.DrugListRequestTorg_IsProblem > 0 ? " style=\'color: #ff0000\'" : ""]}>',
				'<table style="border:0;"><td nowrap>{NAME}&nbsp;</td></tr></table>',
				'</div></tpl>'
			),
			initComponent: function() {
				Ext.form.ComboBox.superclass.initComponent.apply(this, arguments);
				this.store = new Ext.data.JsonStore({
					url: '/?c=MzDrugRequest&m=loadTradenamesCombo',
					key: 'TRADENAMES_ID',
					autoLoad: false,
					fields: [
						{name: 'TRADENAMES_ID', type:'int'},
						{name: 'NAME', type:'string'},
						{name: 'DrugListRequestTorg_IsProblem', type:'int'},
						{name: 'Tradenames_Price', type:'string'}
					]
				});
			},
			show: function(show) {
				if (show) {
					this.ownerCt.show();
				} else {
					this.ownerCt.hide();
				}
				wnd.setWindowHeight();
			},
			listeners: {
				'select': function(combo, record, index) {
					wnd.setEvnReceptKolvo();
					if (wnd.Person_id && getRegionNick() == 'ufa')  {
						if (index > 0) {
							wnd.protokolVK_combo.setAllowBlank(false)
							wnd.protokolVK_combo.show(true);
				}
						else {
							wnd.protokolVK_combo.show(false);
							wnd.protokolVK_combo.setAllowBlank(true)
			}
					}
				}
			}
		});
		
		var form = new Ext.Panel({
			region: 'center',
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			//autoHeight: true,
			border: false,			
			frame: true,
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'MzDrugRequestPersonOrderEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 140,
				collapsible: true,
				url:'/?c=MzDrugRequest&m=saveDrugRequestPersonOrder',
				items: [{
					xtype: 'textfield',
					fieldLabel: lang['prikreplenie'],
					name: 'Lpu_Information',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: lang['zayavka'],
					name: 'DrugRequest_Information',
					anchor: '100%',
					disabled: true
				},
				{
					fieldLabel: lang['finansirovanie'],
					xtype: 'swcommonsprcombo',
					comboSubject: 'DrugFinance',
					hiddenName: 'DrugFinance_id',
					anchor: '100%',
					listeners: {
						'change': function(c,n){
							if(wnd.PersonRegisterType_id && wnd.PersonRegisterType_id == 1){
								if(n == 3 || n == 27){
									wnd.mnn_combo.store.baseParams.DrugFinance_id = n;
									var mnn_id = wnd.mnn_combo.getValue();
									wnd.mnn_combo.store.load({callback: function() {
										var idx = wnd.mnn_combo.getStore().find('DrugComplexMnn_id', mnn_id);

										if (idx >= 0) {
											wnd.mnn_combo.setValue(mnn_id);
										} else {
											wnd.mnn_combo.setValue(null);
										}

										wnd.mnn_combo.focus();
										wnd.mnn_combo.collapse();
									}});
								}
							}
						}
					}
				},
				wnd.mnn_combo,
                {
                    xtype: 'textfield',
                    fieldLabel: lang['primechanie'],
                    name: 'DrugListRequest_Comment',
                    anchor: '100%',
                    disabled: true
                }, {
					layout: 'form',
					items: [
						wnd.tradenames_combo,
						{
							xtype: 'panel',
							bodyStyle: 'color: red; padding-left: 136px; padding-bottom: 5px;',
							html: lang['primechanie_torgovoe_naimenovanie_mojet_byit_zakazano_pri_nalichii_resheniya_vk']
						}
					]
				}, {
							border: false,
							layout: 'column',
							items: [{
									layout: 'form',
									width: 290,
									items: [
										wnd.protokolVK_combo
									]}, {
									layout: 'form',
									width: 290,
									items: [
										{
											style: "padding-left: 0px",
											xtype: 'button',
											//id: 'mdrpoe_BtnViweBK',
											tooltip: langs('Просмотреть'),
											iconCls: 'view16',
											handler: function () {
												if ( wnd.protokolVK_combo.getValue()) {
													var args = {};
													args.showtype = 'view';
													args.EvnVK_id = wnd.protokolVK_combo.getValue()
													getWnd('swClinExWorkEditWindow').show(args);
												}
												
											}
										}
									]}
							]
				}, {
					xtype: 'numberfield',
					fieldLabel: lang['kolichestvo'],
					name: 'DrugRequestPersonOrder_OrdKolvo',
					allowBlank: false,
					allowNegative: false,
                    listeners: {
                        'change': function(field, newValue, oldValue) {
                            if (newValue <= 0) {
                                field.setValue(null);
                            }
                        }
                    }
				}, {
					xtype: 'textfield',
					fieldLabel: lang['vyipisano'],
					name: 'DrugRequestPersonOrder_Kolvo',
					disabled: true
				}, {
					xtype: 'daterangefield',
					fieldLabel: lang['period_vklyucheniya'],
					name: 'DrugRequestPersonOrder_DateRange',
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 220,
					listeners: {
						blur: function(field) {
							wnd.form.findField('DrugRequestExceptionType_id').setAllowBlank(Ext.isEmpty(field.getValue2()));
						}
					}
				}, {
					xtype: 'swcommonsprcombo',
					fieldLabel: lang['prichina_isklyucheniya'],
					name: 'DrugRequestExceptionType_id',
					comboSubject: 'DrugRequestExceptionType',
					width: 220
				}]
			}]
		});

		Ext.apply(this, {
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				wnd.PersonPanel,
				form
			]
		});
		sw.Promed.swMzDrugRequestPersonOrderEditWindow.superclass.initComponent.apply(this, arguments);
		this.base_form = this.findById('MzDrugRequestPersonOrderEditForm');
		this.form = this.base_form.getForm();
	}	
});