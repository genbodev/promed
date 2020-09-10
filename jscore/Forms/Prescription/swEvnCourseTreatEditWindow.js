
/**
* swEvnCourseTreatEditWindow - окно создания/редактирования/просмотра курса назначений c типом Лекарственное лечение.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      0.001-15.03.2012
* @comment      Префикс для id компонентов EPRTREF (EvnCourseTreatEditForm)
*				tabIndex: TABINDEX_EVNPRESCR + (от 100 до 129)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnCourseTreatEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnCourseTreatEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnCourseTreatEditWindow.js',

	action: null,
    onHide: Ext.emptyFn,
    callback: Ext.emptyFn,
    formStatus: 'edit',
    winTitle: lang['kurs_lekarstvennogo_lecheniya'],
    id: 'EvnCourseTreatEditWindow',
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
    draggable: true,
    width: 850,
    layout: 'form',
    listeners: {
        hide: function(win) {
            win.onHide();
        }
		,
		success: function(source, params) {
																		
			/* source - string - источник события (например форма)
			 * params - object - объект со свойствами в завис-ти от источника
			 */
			//log('params = ', params);
			if (source == 'swEvnCourseTreatTimeEntryWindow') {
				var base_form = this.FormPanel.getForm();
				//var val;
				if (params.countDay != 0)
					base_form.findField('EvnCourseTreat_CountDay').setValue(params.countDay);
				this.arr_time = params.arr_time;
				this.setEvnPrescrTreatTime(base_form, this.arr_time);
				this.syncShadow();//перерисовка тени под изменившееся окно
			}
		}
    },
    loadMask: null,
    maximizable: false,
    maximized: false,
    modal: true,
    plain: true,
    resizable: false,
	setEvnPrescrTreatTime: function(base_form, arr_time) {
		var val = '';
		if (arr_time.length > 0) {
			for (i = 0; i < arr_time.length; i++) {
				val += arr_time[i].time + ', ';
			}
			val = val.slice(0, -2);
		}
			
		base_form.findField('EvnPrescrTreat_Time').setValue(val);
		base_form.findField('EvnPrescrTreat_Time').ownerCt.setVisible(this.arr_time.length != 0)
		base_form.findField('EvnCourseTreat_CountDay').setDisabled(this.arr_time.length != 0);
	},
	checkPersonDrugReactionInEvn: function(DrugComplexMnn_id){
		if(getRegionNick() == 'kz')	return false
		var me = this;
		var form = this.FormPanel.getForm();
		var EvnCourseTreat_setDate = form.findField('EvnCourseTreat_setDate').getValue();
		var Person_id = form.findField('Person_id').getValue();
		var Evn_id = form.findField('Evn_id').getValue();

		var store = me.TreatDrugListPanel.getStore();
		var data = store.data.items;

		var DrugComplexMnn_ids = '';
		for(var i = 0; i < data.length; i++){
			DrugComplexMnn_ids = DrugComplexMnn_ids + data[i].data.DrugComplexMnn_id + ',';
		}
		DrugComplexMnn_ids = DrugComplexMnn_ids.slice(0, -1);
		
		Ext.Ajax.request({
			url: '/?c=PersonAllergicReaction&m=checkPersonDrugReactionInEvn',
			params: {
				EvnCourseTreat_setDate: Ext.util.Format.date(EvnCourseTreat_setDate, 'Y-m-d'),
				Evn_id: Evn_id,
				Person_id: Person_id,
				DrugComplexMnn_id: DrugComplexMnn_id,
				DrugComplexMnn_ids: DrugComplexMnn_ids
			},
			callback: function (opt, success, response) {
				if (response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.Drug_Name) {
						Ext6.MessageBox.show({
							title: 'Внимание!',
							msg: 'При использовании препарата ' + result.Drug_Name + ' в комплексе с препаратом ' + result.AntagonistDrug_Names + ' возможны побочные эффекты',
							buttons: Ext6.Msg.OK,
							icon: Ext6.Msg.WARNING
						});
					}
				}
			}
		});
	},
	getDrugInteractionsDescription: function(DrugComplexMnn_id = null){
		if(getRegionNick() == 'kz')
			return false

		var me = this;
		var form = this.FormPanel.getForm();
		var EvnCourseTreat_setDate = form.findField('EvnCourseTreat_setDate').getValue();
		var Person_id = form.findField('Person_id').getValue();
		var Evn_id = form.findField('Evn_id').getValue();
		var store = me.TreatDrugListPanel.getStore();
		var data = store.getData().items;
		me.mask('Подождите, идет загрузка...');
		var DrugComplexMnn_ids = '';
		for(var i = 0; i < data.length; i++){
			DrugComplexMnn_ids = DrugComplexMnn_ids + data[i].data.DrugComplexMnn_id + ',';
		}
		DrugComplexMnn_ids = DrugComplexMnn_id != null ? DrugComplexMnn_ids + DrugComplexMnn_id : DrugComplexMnn_ids.slice(0, -1);

		if(DrugComplexMnn_ids == ''){
			me.unmask();
			return false;
		}


		Ext.Ajax.request({
			url: '/?c=PersonAllergicReaction&m=getDrugInteractionsDescription',
			params: {
				EvnCourseTreat_setDate: Ext.util.Format.date(EvnCourseTreat_setDate, 'Y-m-d'),
				Evn_id: Evn_id,
				Person_id: Person_id,
				DrugComplexMnn_ids: DrugComplexMnn_ids
			},
			callback: function (opt, success, response) {
				if (response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.tpl) {
						for(var key in result.tpl){
							var gridStore = me.TreatDrugListGrid.getStore();
							var data = gridStore.getData().items;
							var panelStoreData = me.TreatDrugListPanel.getStore().getData().items;
							for(var i = 0; i < data.length;i++){
								var mnn_id = key.split('_');
								if(data[i].data.DrugComplexMnn_id == mnn_id[0]){
									if (panelStoreData[i])
										panelStoreData[i].data.Description = result.tpl[key];
								}
							}
						}
						me.TreatDrugListGrid.reconfigure();
						me.unmask();
					}
				}
			}
		});
	},
    setDrugName: function() {
        var drug_name = null;
        var base_form = this.FormPanel.getForm();
        var combo = base_form.findField('MethodInputDrug_id').getActiveCombo();

        if (combo) {
            var value = combo.getValue();
            if (value > 0) {
                var idx = combo.getStore().findBy(function(record) {
                    return (record.get(combo.valueField) == value);
                })
                if (idx > -1) {
                    drug_name = combo.getStore().getAt(idx).get(combo.displayField);
                }
            }
        }

        base_form.findField('DrugNameTextarea').setValue(drug_name);
    },
    loadDrugPackData: function (object_name, object_id, callback) {
        var params = new Object();

        if (object_id > 0) {
            //ищем данные для данного медикамента среди ранее загруженых
            if (!Ext.isEmpty(this.DrugPackData) && !Ext.isEmpty(this.DrugPackData[object_name]) && !Ext.isEmpty(this.DrugPackData[object_name][object_id])) {
                var response_obj = this.DrugPackData[object_name][object_id];
                callback(response_obj);
            } else {
                params[object_name] = object_id;
                Ext.Ajax.request({
                    params: params,
                    url: '/?c=EvnPrescr&m=getDrugPackData',
                    callback: function(opt, scs, response) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        if (!Ext.isEmpty(response_obj)) {
                            callback(response_obj);
                        }
                    }
                });
            }
        }
    },
    setDefaultDrugPackValues: function() {
        var wnd = this;
        var base_form = this.FormPanel.getForm();
        var combo = base_form.findField('MethodInputDrug_id').getActiveCombo();

        var object_id = combo.getValue();
        var object_name = combo.valueField;

        if (Ext.isEmpty(this.DrugPackData)) {
            this.DrugPackData = new Object();
        }
        if (Ext.isEmpty(this.DrugPackData[object_name])) {
            this.DrugPackData[object_name] = new Object();
        }

        if (object_id > 0) {
            wnd.loadDrugPackData(
                object_name,
                object_id,
                function(response_obj) {
                    if (
                        /*wnd.action == 'edit'
                        &&*/ wnd.TreatDrugListPanel.store
                        && wnd.TreatDrugListPanel.store.data
                        && wnd.TreatDrugListPanel.store.data.length > 0
                    ) {
                        var index = wnd.TreatDrugListPanel.getStore().findBy(function(rec){
                            return (rec.get(object_name) == object_id);
                        });
                        if (index >= 0) {
                            var record = wnd.TreatDrugListPanel.getStore().getAt(index);
							if (getGlobalOptions().region.nick == 'ufa') {  // Региональные особенности
								// Фиксируем выписанные значения
								response_obj.Written_GoodsUnit_id = record.get('GoodsUnit_id');  
								response_obj.Written_Kolvo = record.get('Kolvo');
								
								wnd.FormPanel.getForm().findField('GoodsUnit_id').setValue(record.get('GoodsUnit_id'));
								if (wnd.FormPanel.getForm().findField('Kolvo').getValue() == '') {
									wnd.FormPanel.getForm().findField('Kolvo').setValue(record.get('Kolvo'));								
								}
							} else {
								if(response_obj.Fas_Kolvo && response_obj.Fas_Kolvo > 0){
									//response_obj.DoseMass_Kolvo = record.get('Kolvo');
									response_obj.GoodsUnit_id = record.get('GoodsUnit_sid');
									response_obj.DoseMass_GoodsUnit_id = record.get('GoodsUnit_id');
								}
								if(response_obj.FasMass_Kolvo && response_obj.FasMass_Kolvo > 0){
										//response_obj.FasMass_Kolvo = record.get('Kolvo');
										response_obj.FasMass_GoodsUnit_id = record.get('GoodsUnit_id'); 
								}
							}
                            response_obj.KolvoEd = record.get('KolvoEd'); // Кол-во ЛС на 1 прием при редактировании
                        }
                    }
					if (getGlobalOptions().region.nick != 'ufa') {
						if (!response_obj.Fas_Kolvo && !response_obj.FasMass_Kolvo && response_obj.DoseMass_GoodsUnit_id) {
							// Присваиваем значения объектам формы, чтобы отработать в setDrugPackFields
							wnd.FormPanel.getForm().findField('GoodsUnit_id').setValue(response_obj.DoseMass_GoodsUnit_id);
							if (wnd.FormPanel.getForm().findField('Kolvo').getValue() == '') {
								wnd.FormPanel.getForm().findField('Kolvo').setValue(response_obj.DoseMass_Kolvo);
							}
						}
					}
                    base_form.findField('GoodsUnit_id').filterList = response_obj.DoseMass_GoodsUnit_id + ',' + response_obj.FasMass_GoodsUnit_id;
                    wnd.DrugPackData[object_name][object_id] = response_obj;
                    wnd.setDrugPackFields(wnd.DrugPackData[object_name][object_id]);
                }
            );
        }
    },
    setDrugPackFields: function(data) {
        var values = {
            Kolvo: null,
            KolvoEd: null,
            GoodsUnit_id: null,
            GoodsUnit_sid: null
        };

        if (!Ext.isEmpty(data)) {
            if (data.KolvoEd && data.KolvoEd > 0) {  // Если KolvoEd заполнено при редактировании
                values.KolvoEd = data.KolvoEd
            } else {
                values.KolvoEd = 1;
            }

            //Доза на 1 прием
			if (data.Written_Kolvo && data.Written_GoodsUnit_id) {
				values.Kolvo = data.Written_Kolvo;
				values.GoodsUnit_id = data.Written_GoodsUnit_id;
			} else if (data.Fas_Kolvo > 0 && data.DoseMass_Kolvo > 0) { //если указано значащее количество лекарственных форм в первичной упаковке
                values.Kolvo = data.DoseMass_Kolvo * values.KolvoEd;
                values.GoodsUnit_id = data.DoseMass_GoodsUnit_id; //в качестве ед. изм. - лекарственная форма
            } else if (data.FasMass_Kolvo > 0) {
                values.Kolvo = data.FasMass_Kolvo * values.KolvoEd;
                values.GoodsUnit_id = data.FasMass_GoodsUnit_id;
            } else {  //  Оставляем прежние значения
				values.Kolvo = this.FormPanel.getForm().findField('Kolvo').getValue();
				values.GoodsUnit_id = this.FormPanel.getForm().findField('GoodsUnit_id').getValue();
			}

			values.GoodsUnit_sid = data.GoodsUnit_id; //в качестве ед. изм. - лекарственная форма
			/*
			if (getGlobalOptions().region.nick == 'ufa') {
				values.KolvoEd = 1;
                values.GoodsUnit_sid = data.GoodsUnit_id; //в качестве ед. изм. - лекарственная форма
			} else {
				if (data.Fas_Kolvo > 0) { //если указано значащее количество лекарственных форм в первичной упаковке
					values.KolvoEd = 1;
					values.GoodsUnit_sid = data.GoodsUnit_id; //в качестве ед. изм. - лекарственная форма
				} else if (data.FasMass_Kolvo > 0) {
					values.KolvoEd = data.FasMass_Kolvo;
					values.GoodsUnit_sid = data.FasMass_GoodsUnit_id;
				}
			}
			*/
        }

        this.FormPanel.getForm().setValues(values);
        this.TreatDrugListPanel.reCount();
    },
    resetDrugPackData: function() {
        this.DrugPackData = new Object();
    },
    getDrugPackData: function() {
        var combo = this.FormPanel.getForm().findField('MethodInputDrug_id').getActiveCombo();
        var object_id = combo.getValue();
        var object_name = combo.valueField;
        var data = {
            Fas_Kolvo: null,
            Fas_NKolvo: null,
            FasMass_Kolvo: null,
            FasMass_GoodsUnit_id: null,
            FasMass_GoodsUnit_Nick: null,
            DoseMass_Kolvo: null,
            DoseMass_Type: null,
            DoseMass_GoodsUnit_id: null,
            DoseMass_GoodsUnit_Nick: null,
            GoodsUnit_id: null,
            GoodsUnit_Nick: null
        };

        if (!Ext.isEmpty(this.DrugPackData[object_name]) && !Ext.isEmpty(this.DrugPackData[object_name][object_id])) {
            Ext.apply(data, this.DrugPackData[object_name][object_id]);
        }
        return data;
    },
	checkRecomendDose: function(record) {
		if (getRegionNick() == 'kz') {return false}
		var win = this;
		
		if (!Ext.isEmpty(record.get('RlsActmatters_id'))) {
			var Person_Age = !Ext.isEmpty(win.parentWin) ? win.parentWin.PersonInfoFrame.getFieldValue('Person_Age') : null;
			var Diag_id = !Ext.isEmpty(win.Diag_id) ? win.Diag_id : null;
			var ActMatter_id = record.get('RlsActmatters_id');
			var EvnClass = win.parentEvnClass_SysNick;
			
			var params = {
				Person_Age: Person_Age,
				Diag_id: Diag_id,
				ActMatter_id: ActMatter_id,
				EvnClass: EvnClass
			};
			
			Ext.Ajax.request({
				params: params,
				url: '/?c=CureStandart&m=loadRecommendedDoseForDrug',
				callback: function(opt, scs, response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (!Ext.isEmpty(response_obj)) {
						
						var DoseDay = response_obj.data.CureStandartTreatmentDrug_ODD;
						var DayUnit = response_obj.data.ODDDoseUnit_Name || '';
						var DoseKurs = response_obj.data.CureStandartTreatmentDrug_EKD;
						var KursUnit = response_obj.data.EKDDoseUnit_Name || '';

						if (DoseDay != '' || DoseKurs != '') {
							var DayDoseIconCmp = Ext.getCmp('DoseDay_warnIcon');
							var KursDoseIconCmp = Ext.getCmp('PrescrDose_warnIcon');
							DayDoseIconCmp.setVisible(true);
							KursDoseIconCmp.setVisible(true);
							DayDoseIconCmp.getEl().dom.innerHTML = '<img title="Суточная доза не должна превышать: '+ DoseDay +' ' + DayUnit +'" src="/img/icons/warn_yellow.png" style="padding-top: 3px"/>'
							KursDoseIconCmp.getEl().dom.innerHTML = '<img title="Курсовая доза не должна превышать: '+ DoseKurs +' ' + KursUnit +'" src="/img/icons/warn_yellow.png" style="padding-top: 3px"/>'
						}
					}
				}
			});
		}
	},
	hideIcons: function() {
		var DayDoseIconCmp = Ext.getCmp('DoseDay_warnIcon');
		var KursDoseIconCmp = Ext.getCmp('PrescrDose_warnIcon');
		DayDoseIconCmp.setVisible(false);
		KursDoseIconCmp.setVisible(false);
	},
	doSave: function(options) {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = {};
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();
        var thas = this;
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    thas.formStatus = 'edit';
                    thas.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        var params = {};

        params.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
        if(options.signature) {
            params.signature = 1;
        } else {
            params.signature = 0;
        }

        if (base_form.findField('DrugComplexMnn_id').getValue()) {
            var form_data = thas.TreatDrugListPanel.getDrugFormData();
            if (thas.TreatDrugListPanel._isValidData(form_data)) {
                thas.TreatDrugListPanel.saveRecord();
            } else if (thas.TreatDrugListPanel.firstNotValidField) {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    fn: function() {
                        thas.TreatDrugListPanel.firstNotValidField.focus(true);
                    },
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
                return false;
            } else {
                return false;
            }
        }
        var DrugListData = thas.TreatDrugListPanel.getDrugListData();

        //log(DrugListData);
        if (DrugListData.length==0) {
            this.formStatus = 'edit';
            sw.swMsg.alert(lang['oshibka'], lang['kurs_doljen_soderjat_hotya_byi_odno_lekarstvennoe_sredstvo']);
            return false;
        }
        var countDel = 0;
        for(var i=0;i<DrugListData.length;i++){
            if(DrugListData[i].status == 'deleted'){
                countDel++;
            }
        }
        if (DrugListData.length==countDel) {
            this.formStatus = 'edit';
            sw.swMsg.alert(lang['oshibka'], lang['kurs_doljen_soderjat_hotya_byi_odno_lekarstvennoe_sredstvo']+' Если вы хотите удалить Курс воспользуйтесь кнопкой Отменить в меню действий Курса.');
            return false;
        }
        DrugListData = Ext.util.JSON.encode(DrugListData);
        base_form.findField('DrugListData').setValue(DrugListData);

        if( base_form.findField('DurationType_id').disabled ) {
            params.DurationType_id = base_form.findField('DurationType_id').getValue();
        }

        if( base_form.findField('DurationType_recid').disabled ) {
            params.DurationType_recid = base_form.findField('DurationType_recid').getValue();
        }

        if( base_form.findField('DurationType_intid').disabled ) {
            params.DurationType_intid = base_form.findField('DurationType_intid').getValue();
        }

        if( base_form.findField('PerformanceType_id').disabled ) {
            params.PerformanceType_id = base_form.findField('PerformanceType_id').getValue();
        }

		if (inlist(getRegionNick(),['ufa', 'vologda'])) {
			params.arr_time = Ext.util.JSON.encode(this.arr_time); 
			base_form.findField('EvnCourseTreat_CountDay').setDisabled(false);	

		}

        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
        loadMask.show();
        base_form.submit({
            failure: function(result_form, action) {
                thas.formStatus = 'edit';
                loadMask.hide();

                if ( action.result ) {
                    if ( action.result.Error_Msg ) {
                        sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                    }
                    else {
                        sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
                    }
                }
            },
            params: params,
            success: function(result_form, action) {
                thas.formStatus = 'edit';
                loadMask.hide();

                if ( action.result ) {
                    var data = base_form.getValues();
                    data.EvnCourseTreat_id = action.result.EvnCourseTreat_id;
                    thas.callback(data);
                    thas.hide();
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                }
            }
        });
        return true;
	},
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var formFields = [
			,'EvnCourseTreat_setDate'
			,'EvnCourseTreat_CountDay'
			,'EvnCourseTreat_Duration'
			,'EvnCourseTreat_ContReception'
			,'EvnCourseTreat_Interval'
			,'DurationType_id'
			,'DurationType_recid'
			,'DurationType_intid'
            ,'PerformanceType_id'
			,'EvnPrescrTreat_IsCito'
			,'EvnPrescrTreat_Descr'
		];
		for (var i = 0; i < formFields.length; i++ ) {
			if ( enable ) {
				base_form.findField(formFields[i]).enable();
			}
			else {
				base_form.findField(formFields[i]).disable();
			}
		}

        this.TreatDrugListPanel.setEnableEdit(enable);

		if ( enable ) {
			this.buttons[2].show();
            base_form.findField('EvnCourseTreat_setDate').setAllowBlank(false);
            base_form.findField('EvnCourseTreat_CountDay').setAllowBlank(false);
            base_form.findField('EvnCourseTreat_Duration').setAllowBlank(false);
            base_form.findField('EvnCourseTreat_ContReception').setAllowBlank(false);
			base_form.findField('PerformanceType_id').setAllowBlank(false);
			//Заведение графика сделать возможным только в днях
			if (this.parentEvnClass_SysNick == 'EvnSection') {
				base_form.findField('DurationType_id').setValue(1);
				base_form.findField('DurationType_recid').setValue(1);
				base_form.findField('DurationType_intid').setValue(1);
				base_form.findField('PerformanceType_id').setValue(2);
				base_form.findField('DurationType_id').disable();
				base_form.findField('DurationType_recid').disable();
				base_form.findField('DurationType_intid').disable();
				base_form.findField('PerformanceType_id').setDisabled(getRegionNick() !== 'ufa');
				base_form.findField('PerformanceType_id').setAllowBlank(getRegionNick() !== 'ufa');
				base_form.findField('PerformanceType_id').getStore().filterBy(function(rec) {
					if(getRegionNick() == 'ufa' && !rec.get('PerformanceType_Code').inlist([1,2])) 
						base_form.findField('PerformanceType_id').getStore().remove(rec);
				});
                base_form.findField('DurationType_id').setAllowBlank(true);
                base_form.findField('DurationType_recid').setAllowBlank(true);
                base_form.findField('DurationType_intid').setAllowBlank(true);
			} else {
                base_form.findField('DurationType_id').setAllowBlank(false);
                base_form.findField('DurationType_recid').setAllowBlank(false);
                base_form.findField('DurationType_intid').setAllowBlank(false);
            }
		}
		else {
			this.buttons[2].hide();
		}
	},
    setOstatFilterType: function(type, load_combo) { //устанавливаем режим фильтрации по остаткам (auto - автоматическое определение, all - неактивный режим (не должен использоваться), lpu_section - по отделению, storage - по складу)
        var wnd = this;
	    var base_form = this.FormPanel.getForm();
	    var cf_field = base_form.findField('CentralStorageOstatFilter_isActive');

	    //определение видимости блока с фильтрами по остаткам
        var show_filter = (!Ext.isEmpty(this.UserLpuSection_id) && this.UserLpuUnitType_id != '2'); //2 - Поликлиника

        cf_field.setValue(false);
        cf_field.hideContainer();

        if (show_filter) {
            base_form.findField('OstatFilter_isActive').ownerCt.ownerCt.ownerCt.show();
            if (type == 'auto') {
                //автоматическое определение
                type = getDrugControlOptions().drugcontrol_module == '2' ? 'storage' : 'lpu_section'; //модуль учета: 1 - Аптека МО; 2 - АРМ Товароведа.
            }
            if (!Ext.isEmpty(type)) {
                base_form.findField('OstatFilter_isActive').setValue(true);
            } else {
                base_form.findField('OstatFilter_isActive').setValue(false);
            }
            if (type == 'lpu_section') {
                this.lpu_section_combo.showContainer();
                if (load_combo) {
                    this.lpu_section_combo.onLoadData = function () {
                        //если есть, выбираем первую запись (это должно быть отделение пользователя)
                        if (wnd.lpu_section_combo.getStore().getCount() > 0) {
                            var id = wnd.lpu_section_combo.getStore().getAt(0).get('LpuSection_id');
                            wnd.lpu_section_combo.setValue(id);
                            wnd.lpu_section_combo.fireEvent('change', wnd.lpu_section_combo, id);
                            wnd.storage_combo.onLoadData = Ext.emptyFn;
                        }
                    };
                    this.lpu_section_combo.loadData();
                }
            } else {
                this.lpu_section_combo.hideContainer();
            }
            if (type == 'storage') {
                if (getRegionNick() == 'penza') {
                    cf_field.setValue(true);
                    cf_field.showContainer();
                }

                this.storage_combo.showContainer();
                if (load_combo) {
                    this.storage_combo.onLoadData = function () {
                        //если есть, выбираем первую запись (это должно быть склад отделения пользователя)
                        if (wnd.storage_combo.getStore().getCount() > 0) {
                            var id = wnd.storage_combo.getStore().getAt(0).get('Storage_id');
                            wnd.storage_combo.setValue(id);
                            wnd.storage_combo.fireEvent('change', wnd.storage_combo, id);
                            wnd.storage_combo.onLoadData = Ext.emptyFn;
                        }
                    };
                    this.storage_combo.loadData();
                }
            } else {
                this.storage_combo.hideContainer();
            }

            this.OstatFilter_Type = type;

            //если не предусмотрена автозагрузка комбобоксов на пенели фильтров, то нужно обновить фильтры на медикаментах
            if (!load_combo) {
                this.setOstatFilter();
            }
        } else {
            base_form.findField('OstatFilter_isActive').ownerCt.ownerCt.ownerCt.hide();
            this.OstatFilter_Type = null;

            //необходимо очистить фильтры на комбобоксах для выбора медикаментов
            this.setOstatFilter();
        }
    },
    setOstatFilter: function() {
	    var base_params = new Object();
	    var lpu_section_id = this.OstatFilter_Type == 'lpu_section' ? this.lpu_section_combo.getValue() : null;
	    var storage_id = this.OstatFilter_Type == 'storage' ? this.storage_combo.getValue() : null;
	    var dcm_combo = this.FormPanel.getForm().findField('DrugComplexMnn_id');
	    var d_combo = this.FormPanel.getForm().findField('Drug_id');
        var cf_field = this.FormPanel.getForm().findField('CentralStorageOstatFilter_isActive');

        base_params.LpuSection_id = lpu_section_id;
        base_params.Storage_id = storage_id;

        if (!Ext.isEmpty(lpu_section_id) || !Ext.isEmpty(storage_id)) {
            base_params.isFromDocumentUcOst = 'on';
            base_params.UserLpuSection_id = this.UserLpuSection_id;
            base_params.LpuSection_id = lpu_section_id;
            base_params.Storage_id = storage_id;
		}
		else if (getGlobalOptions().region.nick == 'ufa' &&  !this.storage_combo.hidden) {
			 base_params.isFromDocumentUcOst = 'on';
            base_params.LpuSection_id = this.UserLpuSection_id;
        } else {
            base_params.isFromDocumentUcOst = 'off';
            base_params.LpuSection_id = this.UserLpuSection_id;
        }

        base_params.isFromCentralStorageOst = (!Ext.isEmpty(storage_id) && cf_field.checked) ? 'on' : 'off';

        dcm_combo.setValue(null);
        dcm_combo.reset();
        delete dcm_combo.lastQuery;

        d_combo.setValue(null);
        d_combo.reset();
        delete d_combo.lastQuery;

        dcm_combo.getStore().baseParams = base_params;
        d_combo.getStore().baseParams = base_params;
    },
	initComponent: function() {
        var thas = this;

        this.keys = [{
            alt: true,
            fn: function(inp, e) {
                switch ( e.getKey() ) {
                    case Ext.EventObject.C:
                        thas.doSave();
                        break;

                    case Ext.EventObject.J:
                        thas.hide();
                        break;
                }
            },
            key: [
                Ext.EventObject.C,
                Ext.EventObject.J
            ],
            scope: thas,
            stopEvent: false
        }];

        this.itemBodyStyle = 'padding: 5px 5px 0';
        this.labelAlign = 'right';
        this.labelWidth = 130;

        thas.treatDrugAddBtn = new Ext.Button({
            text: lang['dobavit_medikament'],
            width: 160,
            tabIndex: TABINDEX_EVNPRESCR + 126,
            handler: function() {
                thas.TreatDrugListPanel.saveRecord();
            }
        });
        thas.treatDrugResetBtn = new Ext.Button({
            text: lang['sbros'],
            width: 80,
            tabIndex: TABINDEX_EVNPRESCR + 127,
            handler: function() {
                thas.TreatDrugListPanel.onClearDrug();
            }
        });

        /**
         * Представление списка медикаментов
         * Содержит как сохраненные, так и не сохраненные медикаменты.
         * Не сохраненные медикаменты можно или добавить в БД или удалить из store или отредактировать без сохранения
         * Сохраненные медикаменты можно или удалить из БД или отредактировать с сохранением
         * @type {Ext.DataView}
         */
        var titleTreatDrugListPanel = '<dt><span style="' +
            'color: rgb(21, 66, 139);' +
            'font: bold 11px tahoma,arial,helvetica,sans-serif;' +
            '">Добавленные медикаменты</span></dt>';
        var contentTreatDrugListPanelAttr = ' style="padding: 10px;"';
        this.TreatDrugListPanel = new Ext.DataView({
            id:"TreatDrugListDataView",
            store: new Ext.data.Store({
                autoLoad:false,
                reader:new Ext.data.JsonReader({
                    id:'num'
                }, [
                    {name: 'num', mapping: 'num', key:true},
                    {name: 'id', mapping: 'id'},
                    {name: 'number', mapping: 'number'},
                    {name: 'status', mapping: 'status'},// 'updated', 'new', 'saved'
                    // ниже данные для сохранения
                    {name: 'DrugComplexMnnDose_Mass', mapping: 'DrugComplexMnnDose_Mass'},
                    {name: 'EdUnits_id', mapping: 'EdUnits_id'},
                    {name: 'Drug_id', mapping: 'Drug_id'},
                    {name: 'DrugComplexMnn_id', mapping: 'DrugComplexMnn_id'},
                    {name: 'MethodInputDrug_id', mapping: 'MethodInputDrug_id'},
                    // ниже данные для отображения
                    {name: 'Drug_Name', mapping: 'Drug_Name'},
                    {name: 'Kolvo', mapping: 'Kolvo'},
                    {name: 'EdUnits_Nick', mapping: 'EdUnits_Nick'},
                    {name: 'KolvoEd', mapping: 'KolvoEd'},
                    {name: 'DrugForm_Nick', mapping: 'DrugForm_Nick'},
                    {name: 'DoseDay', mapping: 'DoseDay'},
                    {name: 'PrescrDose', mapping: 'PrescrDose'},
                    {name: 'GoodsUnit_id', mapping: 'GoodsUnit_id'},
                    {name: 'GoodsUnit_Nick', mapping: 'GoodsUnit_Nick'},
                    {name: 'GoodsUnit_sid', mapping: 'GoodsUnit_sid'}
                ])
            }),
            autoHeight: true,
            itemSelector: 'ul',
            tpl : new Ext.XTemplate(
                '<div>',
                    '<dl>',
                        titleTreatDrugListPanel,
                        '<dd>',
                            '<ol type="1" style="list-style-image: none; list-style-type: decimal; list-style-position: outside; list-style: decimal outside none; ">',
                                '<tpl for=".">',
                                    '<li'+ contentTreatDrugListPanelAttr +'>',
                                    '<tpl if="Drug_Name&&!this.isEnabledSel()">',
                                    '<span style="color: green;">{Drug_Name}</span>',
                                    '</tpl>',
                                    '<tpl if="Drug_Name&&this.isEnabledSel()">',
                                    '<span style="color: green; text-decoration: underline; cursor: pointer;" onclick="Ext.getCmp(\'TreatDrugListDataView\').onSelectRecord(\'{num}\');">{Drug_Name}</span>',
                                    '</tpl>',
                                    '<tpl if="this.isEnabledDel()">',
                                    ' <span style="color: red; text-decoration: underline; cursor: pointer;" onclick="Ext.getCmp(\'TreatDrugListDataView\').onDeleteRecord(\'{num}\');">Удалить</span>',
                                    '</tpl>',
                                    '<tpl if="Kolvo&&GoodsUnit_Nick">',
                                    "<br>Доза разовая - {Kolvo} {GoodsUnit_Nick}",
                                    '</tpl>',
                                    '<tpl if="!Kolvo&&!GoodsUnit_Nick&&KolvoEd&&DrugForm_Nick">',
                                    '<br>Доза разовая - {KolvoEd} {DrugForm_Nick}',
                                    '</tpl>',
                                    '<tpl if="DoseDay">',
                                    '; Доза дневная - {DoseDay}',
                                    '</tpl>',
                                    '<tpl if="PrescrDose">',
                                    '; Доза курсовая - {PrescrDose}.',
                                    '</tpl>',
                                    '<tpl if="!PrescrDose">',
                                    '.',
                                    '</tpl>',
                                    '<div>{Description}</div>',
                                    '</li>',
                                '</tpl>',
                            '</ol>',
                        '</dd>',
                    '</dl>',
                '</div>',
                {
                    isEnabledSel: function() {
                        return (thas.TreatDrugListPanel.getEnableEdit());
                    },
                    isEnabledDel: function() {
                        return (/*thas.TreatDrugListPanel.getCountItems()>1 && */thas.TreatDrugListPanel.getEnableEdit());
                    }
                }
            ),
            emptyText : '<div><dl>'+ titleTreatDrugListPanel +'<dd'+ contentTreatDrugListPanelAttr +'><span style="color: gray">Для добавления заполните поля выше и нажмите кнопку "Добавить медикамент"</span></dd></dl></div>',
            limitCountItem: 13,
            defaultMethodInputDrug_id: 1,
            objectDrug: 'EvnCourseTreatDrug',
            doseData: {
                kolvo: null,
                EdUnits_id: null
            },
            /**
             * @param {String} dose
             * @param {Float} dose_mass DrugComplexMnnDose_Mass
             * @param {String} drugform_nick
             * @return {Object}
             */
            parseDose: function(dose, dose_mass, drugform_nick)
            {
                //log('parseDose', dose, dose_mass, drugform_nick);
                if (!dose_mass) {
                    dose = '';
                    dose_mass = null;
                }
                if (!drugform_nick) {
                    drugform_nick = '';
                }
                var doseData = {
                    DrugComplexMnnDose_Mass: dose_mass,
                    kolvo: null,
                    EdUnits_id: null
                };
                dose = dose.trim();
                if (dose.length==0) {
                    return doseData;
                }
                var res = dose.match(/^[\d\.]+/i);
                if (res.length==0) {
                    return doseData;
                }
                doseData.kolvo = this.floatRound(res[0]);
                if (dose_mass) {
                    doseData.kolvo = this.floatRound(dose_mass);
                }
                var ed = dose.replace(res[0] ,"").trim();
                this._edUnitsStore.each(function(rec){
                    //log([rec.get('EdUnits_Name'),ed]);
                    if (rec.get('EdUnits_Name')==ed) {
                        doseData.EdUnits_id = rec.get('EdUnits_id');
                        return false;
                    }
                    return true;
                });
                if (dose_mass && !doseData.EdUnits_id) {
                    // Если не удалось автоматически определить единицы измерения, то для растворов и капель подставлять МЛ, а для таблеток, порошков - МГ.
                    if (drugform_nick.toString().inlist([lang['rastvor'],lang['kapli']])) {
                        this._edUnitsStore.each(function(rec){
                            if (rec.get('EdUnits_Name')==lang['ml']) {
                                doseData.EdUnits_id = rec.get('EdUnits_id');
                                return false;
                            }
                            return true;
                        });
                    }
                    if (drugform_nick.toString().inlist([lang['tabl'],lang['por']])) {
                        this._edUnitsStore.each(function(rec){
                            if (rec.get('EdUnits_Name')==lang['mg']) {
                                doseData.EdUnits_id = rec.get('EdUnits_id');
                                return false;
                            }
                            return true;
                        });
                    }
                }
                return doseData;
            },
            getRegimeFormParams: function() {
                var base_form = thas.FormPanel.getForm();
                return {
                    setDate: base_form.findField('EvnCourseTreat_setDate').getRawValue(),
                    CountDay: base_form.findField('EvnCourseTreat_CountDay').getValue(),
                    Duration: base_form.findField('EvnCourseTreat_Duration').getValue(),
                    DurationType_id: base_form.findField('DurationType_id').getValue(),
                    DurationType_Nick: base_form.findField('DurationType_id').getRawValue(),
                    ContReception: base_form.findField('EvnCourseTreat_ContReception').getValue(),
                    DurationType_recid: base_form.findField('DurationType_recid').getValue(),
                    Interval: base_form.findField('EvnCourseTreat_Interval').getValue(),
                    DurationType_intid: base_form.findField('DurationType_intid').getValue()
                };
            },
            getEnableEdit: function()
            {
                return this._enableEdit||false;
            },
            setEnableEdit: function(enable)
            {
                this._enableEdit = enable;
            },
            setDisabledAddBtn: function(disabled)
            {
                var me = this;
                if (thas.treatDrugAddBtn) {
                    if (!disabled) {
                        disabled = (me.getCountItems()==me.limitCountItem);
                    }
                    thas.treatDrugAddBtn.setDisabled(disabled);
                }
            },
            reCountAll: function()
            {
                var num, me = this, data;
                me.reCount();
                for (num in me._drugListData) {
                    if ('deleted' != me._drugListData[num].status) {
                        data = Ext.apply(me._drugListData[num], me.getRegimeFormParams());
                        data = me._reCountData(data);
                        me._drugListData[num].DoseDay = data['DoseDay'];
                        me._drugListData[num].PrescrDose = data['PrescrDose'];
                        if ('saved' == me._drugListData[num].status) {
                            me._drugListData[num].status = 'updated';
                        }
                    }
                }
                me._loadDataView();
            },
            onChangeDrug: function(name, combo, newValue)
            {
                //log('onChangeDrug');
                var record = combo.getStore().getById(newValue);
                if (!record) {
                    //log('fail onChangeDrug');
                    return false;
                }
                var fieldKolvoEd = thas.FormPanel.getForm().findField('KolvoEd'),//Ед. дозировки
                    fieldKolvo = thas.FormPanel.getForm().findField('Kolvo'),
                    fieldEdUnits = thas.FormPanel.getForm().findField('EdUnits_id'),
                    labelFormNick = thas.findById(thas.id +'_TreatDrugForm_Nick'),
                    doseMass = record.get('DrugComplexMnnDose_Mass')||null,
                    kolvo = null,
                    EdUnits_id = null,
                    kolvoEd = null,
                    drugform_name,
                    dose = '';
                thas.FormPanel.getForm().findField('DrugComplexMnnDose_Mass').setValue(doseMass);
                if ('Drug_id' == name) {
                    dose = record.get('Drug_Dose')||'';
                    drugform_name = record.get('DrugForm_Name')||'';
                    thas.FormPanel.getForm().findField('DrugComplexMnn_id').setValue(record.get('DrugComplexMnn_id')||null);
                } else {
                    dose = record.get('DrugComplexMnn_Dose')||'';
                    drugform_name = record.get('RlsClsdrugforms_Name')||'';
                }
                this.setDrugForm(drugform_name);
                this.doseData = this.parseDose(dose, doseMass, this.getDrugFormNick(drugform_name, 'for_dose_count'));
                if (this.doseData.kolvo) {
                    kolvoEd = labelFormNick.text ? 1 : null;
                    kolvo = this.doseData.kolvo;
                }
                if (this.doseData.EdUnits_id) {
                    EdUnits_id = this.doseData.EdUnits_id;
                }
                fieldEdUnits.setValue(EdUnits_id);
                fieldKolvo.setValue(kolvo);
                fieldKolvoEd.setValue(kolvoEd);
                this.reCount();
                return true;
            },
            /**
             * Срабатывает после onChangeDrug или после загрузки панели
             * @param name
             * @param combo
             * @param record
             */
            onSelectDrug: function(name, combo, record)
            {
                //log('onSelectDrug', record);
                var drugform_name,
                    dose;
                if ('Drug_id' == name) {
                    dose = record.get('Drug_Dose')||'';
                    if (record.get('Drug_Name')) {
                        drugform_name = record.get('Drug_Name');
                    }
                    if (record.get('DrugForm_Name')) {
                        drugform_name = record.get('DrugForm_Name');
                    }
                } else {
                    dose = record.get('DrugComplexMnn_Dose')||'';
                    if (record.get('DrugComplexMnn_Name')) {
                        drugform_name = record.get('DrugComplexMnn_Name');
                    }
                    if (record.get('RlsClsdrugforms_Name')) {
                        drugform_name = record.get('RlsClsdrugforms_Name');
                    }
                }
                this.setDrugForm(drugform_name);
                this.doseData = this.parseDose(dose, record.get('DrugComplexMnnDose_Mass'), this.getDrugFormNick(drugform_name, 'for_dose_count'));
            },
            getDrugFormNick: function(drugform_name, mode)
            {
                if (!drugform_name) {
                    drugform_name = '';
                }
                drugform_name = drugform_name.toString().toLowerCase();
                var drugform_nick;
                switch (true) {
                    case (drugform_name.indexOf('pulv.') >= 0 && 'for_signa' != mode):
                    case (drugform_name.indexOf(lang['poroshok']) >= 0 && 'for_signa' != mode):
                    case (drugform_name.indexOf(lang['por']) >= 0 && 'for_signa' != mode):
                        drugform_nick = lang['por']; break;
                    case (drugform_name.indexOf('sol.') >= 0 && 'for_signa' != mode):
                    case (drugform_name.indexOf(lang['rastvor']) >= 0 && 'for_signa' != mode):
                    case (drugform_name.indexOf(lang['r-r']) >= 0 && 'for_signa' != mode):
                        drugform_nick = lang['rastvor']; break;
                    case (drugform_name.indexOf('qtt.') >= 0 && 'for_signa' != mode):
                    case (drugform_name.indexOf(lang['kapli']) >= 0 && 'for_signa' != mode):
                        drugform_nick = lang['kapli']; break;
                    case (drugform_name.indexOf('briketi') >= 0):
                    case (drugform_name.indexOf(lang['briketyi']) >= 0):
                        drugform_nick = lang['briketyi']; break;
                    case (drugform_name.indexOf('granuli') >= 0):
                    case (drugform_name.indexOf(lang['gran']) >= 0):
                        drugform_nick = lang['gran']; break;
                    case (drugform_name.indexOf('dragees') >= 0):
                    case (drugform_name.indexOf(lang['draje']) >= 0):
                        drugform_nick = lang['draje']; break;
                    case (drugform_name.indexOf('capsulae') >= 0):
                    case (drugform_name.indexOf(lang['kaps']) >= 0):
                        drugform_nick = lang['kaps']; break;
                    case (drugform_name.indexOf('supp.') >= 0):
                    case (drugform_name.indexOf(lang['supp']) >= 0):
                        drugform_nick = lang['supp']; break;
                    case (drugform_name.indexOf('tabl.') >= 0):
                    case (drugform_name.indexOf(lang['tabl']) >= 0):
                        drugform_nick = lang['tabl']; break;
                    case (drugform_name.indexOf('ppl.') >= 0):
                    case (drugform_name.indexOf(lang['pilyuli']) >= 0):
                        drugform_nick = lang['pilyuli']; break;
                    default: drugform_nick = ''; break;
                }
                return drugform_nick;
            },
            setDrugForm: function(drugform_name)
            {
                //log('setDrugForm', drugform_name);
                var fDrugForm = thas.FormPanel.getForm().findField('DrugForm_Name');
                if (drugform_name && fDrugForm) {
                    thas.FormPanel.getForm().findField('DrugForm_Name').setValue(drugform_name);
                }
                thas.findById(thas.id +'_TreatDrugForm_Nick').setText(this.getDrugFormNick(drugform_name, 'for_signa'));
            },
            onClearDrug: function()
            {
                //log('onClearDrug');
                this.doseData = this.parseDose('',null,'');
                thas.FormPanel.getForm().findField('DrugComplexMnn_id').setValue(null);
                thas.FormPanel.getForm().findField('Drug_id').setValue(null);
                thas.FormPanel.getForm().findField('KolvoEd').setValue(null);
                thas.FormPanel.getForm().findField('Kolvo').setValue(null);
                thas.FormPanel.getForm().findField('EdUnits_id').setValue(null);
                thas.FormPanel.getForm().findField('DrugComplexMnnDose_Mass').setValue(null);
                thas.FormPanel.getForm().findField('DrugForm_Name').setValue('');
                thas.FormPanel.getForm().findField('DoseDay').setValue('');
                thas.FormPanel.getForm().findField('PrescrDose').setValue('');
                thas.FormPanel.getForm().findField('GoodsUnit_id').setValue(null);
                thas.FormPanel.getForm().findField('GoodsUnit_sid').setValue(null);
                thas.FormPanel.getForm().findField('DrugNameTextarea').setValue('');
                thas.findById(thas.id +'_TreatDrugForm_Nick').setText('');
                this.setDisabledAddBtn(true);
				thas.FormPanel.getForm().findField('GoodsUnit_id').getStore().clearFilter();
            },
            onSelectMethodInputDrug: function(value, allow_onClearDrug)
            {
                //log('onSelectMethodInputDrug', value, allow_onClearDrug);
                var me = this,
                    f2 = thas.FormPanel.getForm().findField('DrugComplexMnn_id'),
                    f3 = thas.FormPanel.getForm().findField('Drug_id');
                if (value == 1 && allow_onClearDrug && f3 && f3.getValue() && f3.getFieldValue('DrugComplexMnn_id')) {
                    // если в комбо “Медикамент” f3 выбрано торговое наименование, подставлять его DrugComplexMnn в комбо f2
                    f2.setValue(f3.getFieldValue('DrugComplexMnn_id'));
                    f2.setContainerVisible(true);
                    f3.setValue(null);
                    f3.setRawValue('');
                    f3.setContainerVisible(false);
                    me.loadCombo(f2, {
                        DrugComplexMnn_id: f2.getValue()
                    }, function (rec) {
                        if (rec) {
                            f2.fireEvent('select', f2, rec);
                        } else {
                            f2.setValue(null);
                            f2.setRawValue('');
                            me.onClearDrug();
                        }
                    });
                } else {
                    me.setFieldVisible('DrugComplexMnn_id', (value == 1), allow_onClearDrug);
                    me.setFieldVisible('Drug_id', (value != 1), allow_onClearDrug);
                    if (allow_onClearDrug) {
                        me.onClearDrug();
                    }
                }
            },
            onSelectRecord: function(id)
            {
                var me = this,
                    enableEdit = me.getEnableEdit(),
                    f = thas.FormPanel.getForm().findField('MethodInputDrug_id'),
                    f2 = thas.FormPanel.getForm().findField('DrugComplexMnn_id'),
                    f3 = thas.FormPanel.getForm().findField('Drug_id'),
                    fieldDoseMass = thas.FormPanel.getForm().findField('DrugComplexMnnDose_Mass'),
                    fDrugForm = thas.FormPanel.getForm().findField('DrugForm_Name'),
                    fEdUnits = thas.FormPanel.getForm().findField('EdUnits_id'),
                    fId = thas.FormPanel.getForm().findField('id'),
                    fKolvoEd = thas.FormPanel.getForm().findField('KolvoEd'),
                    fKolvo = thas.FormPanel.getForm().findField('Kolvo'),
                    fDoseDay = thas.FormPanel.getForm().findField('DoseDay'),
                    fPrescrDose = thas.FormPanel.getForm().findField('PrescrDose'),
                    data;
                if (id) {
                    me.selectedRec = me.getStore().getById(id) || null;
                }
                if (!f) {
                    //log('fail applyDrugData');
                    return false;
                }
                /*
                me.setFieldAllowBlank('MethodInputDrug_id', false);
                me.setFieldAllowBlank('Drug_id', false);
                me.setFieldAllowBlank('DrugComplexMnn_id', false);
                me.setFieldAllowBlank('EdUnits_id', false);
                me.setFieldAllowBlank('KolvoEd', false);
                me.setFieldAllowBlank('Kolvo', false);
                */
                //log(['applyDrugData', data]);
                if (!me.selectedRec) {
                    fId.setValue(null);
                    f.setCheckedValue(me.defaultMethodInputDrug_id);
                    me.onSelectMethodInputDrug(me.defaultMethodInputDrug_id,true);
                } else {
                    data = me.selectedRec.data;
                    //log('onSelectRecord');
                    f.setCheckedValue(data.MethodInputDrug_id);
                    me.onSelectMethodInputDrug(data.MethodInputDrug_id,false);
                    f2.setValue(data.DrugComplexMnn_id||null);
                    f3.setValue(data.Drug_id||null);
                    /*
                    if (data.Drug_Name) {
                        if (1 == data.MethodInputDrug_id) {
                            if (f2.rendered) {
                                f2.setRawValue(data.Drug_Name);
                            } else {
                                f2.on('render', function(){
                                    f2.setRawValue(data.Drug_Name);
                                });
                            }
                        } else {
                            if (f3.rendered) {
                                f3.setRawValue(data.Drug_Name);
                            } else {
                                f3.on('render', function(){
                                    f3.setRawValue(data.Drug_Name);
                                });
                            }
                        }
                    }
                    */
                    fieldDoseMass.setValue(data.DrugComplexMnnDose_Mass||null);
                    fDrugForm.setValue(data.DrugForm_Name||null);
                    fEdUnits.setValue(data.EdUnits_id||null);
                    /*
                    if (data.EdUnits_Nick) {
                        if (fEdUnits.rendered) {
                            fEdUnits.setRawValue(data.EdUnits_Nick);
                        } else {
                            fEdUnits.on('render', function(){
                                fEdUnits.setRawValue(data.EdUnits_Nick);
                            });
                        }
                    }
                    */
                    fId.setValue(data['id']||null);
                    fKolvoEd.setValue(data['KolvoEd']||null);
                    fKolvo.setValue(data['Kolvo']||null);
                    fDoseDay.setValue(data['DoseDay']||null);
                    fPrescrDose.setValue(data['PrescrDose']||null);
                }

                f.setDisabled(!enableEdit||'EvnCourseTreatDrug' != me.objectDrug);
                f2.setDisabled(!enableEdit||'EvnCourseTreatDrug' != me.objectDrug);
                f3.setDisabled(!enableEdit||'EvnCourseTreatDrug' != me.objectDrug);
                fEdUnits.setDisabled(!enableEdit||'EvnCourseTreatDrug' != me.objectDrug);
                fKolvoEd.setDisabled(!enableEdit);
                fKolvo.setDisabled(!enableEdit);

                var baseParams = {
                    LpuSection_id: me.LpuSection_id,
                    isFromDocumentUcOst: 'off'
                };
                if ( me.parentEvnClass_SysNick && me.parentEvnClass_SysNick == 'EvnSection' ) {
                    baseParams.isFromDocumentUcOst = 'on';
                }
                f2.getStore().baseParams = baseParams;
                f3.getStore().baseParams = baseParams;
                //log('onSetData');
                if (f.getValue() == 1) {
                    if (f2 && f2.getValue()) {
                        me.loadCombo(f2, {
                            DrugComplexMnn_id: f2.getValue()
                        }, function (rec) {
                            if (rec) {
								var form = thas.FormPanel.getForm();
                                thas.TreatDrugListPanel.onSelectDrug('DrugComplexMnn_id', f2, rec);
                                thas.setDrugName();
                                form.findField('GoodsUnit_id').setValue(data.GoodsUnit_id);
                                form.findField('GoodsUnit_sid').setValue(data.GoodsUnit_sid);
                                f2.fireEvent('select', f2, rec);
                            }
                        });
                    } else {
                        //log('fail field DrugComplexMnn_id - is not loadCombo');
                    }
                } else {
                    if (f3 && f3.getValue()) {
                        me.loadCombo(f3, {
                            Drug_id: f3.getValue()
                        }, function (rec) {
                            if (rec) {
                                f3.fireEvent('select', f3, rec);
                            }
                        });
                    } else {
                        //log('fail field Drug_id - is not loadCombo');
                    }
                }
                //log('onLoadCombo');
                return true;
            },
            loadCombo: function(combo, params, callback) {
                combo.getStore().load({
                    callback: function() {
                        if ( combo.getStore().getCount() > 0 && combo.getValue() > 0 ) {
                            combo.setValue(combo.getValue());
                            if (typeof callback == 'function') {
                                var rec = combo.getStore().getById(combo.getValue());
                                callback(rec);
                            }
                        }
                    },
                    params: params
                });
            },
            onDeleteRecord: function(id)
            {
                var rec, me = this;
                if (me._drugListData[id] && me._drugListData[id].id) {
                    me._drugListData[id].status = 'deleted';
                }
                if (me._drugListData[id] && !me._drugListData[id].id) {
                    delete me._drugListData[id];
                }
                rec = this.getStore().getById(id);
                if (rec) {
                    me.getStore().remove(rec);
                    me.refresh();
                }
            },
            setFieldVisible: function(name, flag, allow_clear)
            {
                var f = thas.FormPanel.getForm().findField(name);
                if (!f || !f.rendered) {
                    return false;
                }
                //log(['setFieldVisible', f, arguments]);
                if (allow_clear) {
                    f.setValue(null);
                    f.setRawValue('');
                }
                f.setContainerVisible(flag);
                return true;
            },
            setFieldAllowBlank: function(name, flag)
            {
                var f = thas.FormPanel.getForm().findField(name);
                if (!f) {
                    return false;
                }
                var requiredEditableFields = [
                    'MethodInputDrug_id',
                    'DrugComplexMnn_id',
                    'Drug_id',
                    'KolvoEd',
                    'Kolvo',
                    'EdUnits_id'
                ];
                var cnt = this.getCountItems();
                if (!flag && !name.inlist(requiredEditableFields) && cnt > 1) {
                    //если поле надо сделать обязательным
                    // и его нет в списке обязательных полей
                    // и это не единственная видимая панель,
                    //то поле должно остаться необязательным
                    flag = true;
                }
                f.setAllowBlank(flag);
                if (!flag && f.rendered) {
                    f.clearInvalid();
                }
                return true;
            },
            reCount: function(data)
            {
                if (!data) {
                    data = this.getRegimeFormParams();
                }
                //log('reCount', data);
                var fieldKolvoEd = thas.FormPanel.getForm().findField('KolvoEd'),
                    //fieldEdUnits = thas.FormPanel.getForm().findField('EdUnits_id'),
                    fieldGoodsUnit = thas.FormPanel.getForm().findField('GoodsUnit_id'),
                    fieldKolvo = thas.FormPanel.getForm().findField('Kolvo');
                data['KolvoEd'] = fieldKolvoEd.getValue() || null;
                //data['EdUnits_id'] = fieldEdUnits.getValue() || null;
                data['GoodsUnit_id'] = fieldGoodsUnit.getValue() || null;
                data['Kolvo'] = fieldKolvo.getValue() || null;
                data = this._reCountData(data);
                thas.FormPanel.getForm().findField('DoseDay').setValue(data['DoseDay']);
                thas.FormPanel.getForm().findField('PrescrDose').setValue(data['PrescrDose']);
                this.setDisabledAddBtn(data['PrescrDose'].length==0);
            },
            _reCountData: function(data)
            {
                //log(['_reCountData', data]);
                // Расчет суточной и курсовой доз
                var dd_text='', kd_text='', dd=0, kd=0, ed = '', rec;
                var fieldGoodsUnit = thas.FormPanel.getForm().findField('GoodsUnit_id');

                //Дневная доза – Прием в ед. измерения (либо количество ед. дозировки*дозировку)*Приемов в сутки
                if ( data['Kolvo'] && data['GoodsUnit_id'] /*&& data['EdUnits_id']*/ ) {
                    // в ед. измерения
                    dd = this.floatRound(data.CountDay*data['Kolvo']);
                    /*rec = this._edUnitsStore.getById(data['EdUnits_id']);
                    if (rec) {
                        ed = rec.get('EdUnits_Name');
                        dd_text = dd +' '+ ed;
                    }*/
                    rec = fieldGoodsUnit.getStore().getById(data['GoodsUnit_id']);
                    if (rec) {
                        ed = rec.get('GoodsUnit_Nick');
                        dd_text = dd +' '+ ed;
                    }
                }
                if (data['KolvoEd'] && !data['Kolvo']) {
                    // в ед. дозировки только если не указано в ед.измерения
                    dd = this.floatRound(data.CountDay*data['KolvoEd']);
                    ed = thas.findById(thas.id +'_TreatDrugForm_Nick').text;
                    dd_text = dd +' '+ ed;
                }
                if ('EvnCourseTreatDrug' == this.objectDrug && dd > 0 && data.Duration>0 && data.ContReception>0) {
                    switch (true) {
                        case (data.DurationType_id == 2): data.Duration *= 7; break;
                        case (data.DurationType_id == 3): data.Duration *= 30; break;
                        case (data.DurationType_recid == 2): data.ContReception *= 7; break;
                        case (data.DurationType_recid == 3): data.ContReception *= 30; break;
                        case (data.Interval > 0 && data.DurationType_intid == 2): data.Interval *= 7; break;
                        case (data.Interval > 0 && data.DurationType_intid == 3): data.Interval *= 30; break;
                    }
                    if (data.Interval > 0) {
                        //формула ниже считает исходя из того, что продолжительность включает в себя перерывы
                        //kd = dd*(data.Duration-(data.Interval*Math.floor(data.Duration/(data.Interval+data.ContReception))));
                        //формула ниже считает исходя из того, что продолжительность равна числу дней приема (на сервере так и считает)
                        kd = this.floatRound(dd*data.Duration);
                    } else {
                        kd = this.floatRound(dd*data.Duration);
                    }
                    kd_text=kd +' '+ ed;
                }
                data['DoseDay'] = dd_text;
                data['PrescrDose'] = kd_text;
                return data;
            },
            floatRound: function(value)
            {
                value = parseFloat(value);
                var value_str = value.toString();
                var parts = value_str.split('.');
                if (parts.length > 1 && parts[1].length > 5) {
                    value = parseFloat(value.toPrecision(5 + parts[0].length));
                }
                return value;
            },
            reset: function(only_form)
            {
                //log('reset', only_form);
                if (!only_form) {
                    this.setDrugListData('[]');
                }
                this.selectedRec = null;
                var f = thas.FormPanel.getForm().findField('MethodInputDrug_id');
                if (f) {
                    f.setCheckedValue(this.defaultMethodInputDrug_id);
                    this.onSelectMethodInputDrug(this.defaultMethodInputDrug_id,true);
                }
            },
            onLoadForm: function(str)
            {
                var me = this;
                if ('add' == thas.action || !str) {
                    str = '[]';
                }
                me.setDrugListData(str);
                me._loadEdUnitsStore(thas.FormPanel.getForm(), function(bf) {
                });
            },
            getDrugListData: function()
            {
                var me = this.TreatDrugListPanel, arr = [], num, rec;
                for (num in me._drugListData) {
                    rec = me.getStore().getById(num);
                    if ('deleted' == me._drugListData[num].status) {
                        me._drugListData[num].FactCount = 0;
                        arr.push(me._drugListData[num]);
                        if (me._isValidData(me._drugListData[num])) {
                            //rec.data.FactCount = 0;
                            //arr.push(rec.data);
                        }
                    } else if (me._isValidData(me._drugListData[num])) {
                        //'new'
                        //'saved'
                        //'updated'
                        me._drugListData[num].FactCount = 0;
                        arr.push(me._drugListData[num]);
                    }
                }
                return arr;
            }.createDelegate(this),
            setDrugListData: function(str)
            {
                var me = this, arr, i = 0, num, number = 0;
                try {
                    arr = Ext.util.JSON.decode(str);
                    if (!Ext.isArray(arr)) {
                        //log('setDrugListData error', arr, str);
                        return false;
                    }
                    me._drugListData = {};
                    me.resetLastNum();
                    //log('setDrugListData', arr);
                    while (arr.length > i) {
                        num = me.getNewNum();
                        me._drugListData[num] = arr[i];
                        me._drugListData[num].status = 'saved';
                        i++;
                    }
                    me._loadDataView();
                } catch (e) {
                    //log('setDrugListData error', str);
                    return false;
                }
                return true;
            },
            getCountItems: function()
            {
                return this.getStore().getCount();
            },
            getDrugFormData: function()
            {
                var data = {};
                data['MethodInputDrug_id'] = thas.FormPanel.getForm().findField('MethodInputDrug_id').getValue();
                if (1 == data['MethodInputDrug_id']) {
                    data['Drug_Name'] = thas.FormPanel.getForm().findField('DrugComplexMnn_id').getRawValue();
                    data['Drug_id'] = null;
                } else {
                    data['Drug_Name'] = thas.FormPanel.getForm().findField('Drug_id').getRawValue();
                    data['Drug_id'] = thas.FormPanel.getForm().findField('Drug_id').getValue();
                }
                data['DrugComplexMnn_id'] = thas.FormPanel.getForm().findField('DrugComplexMnn_id').getValue() || null;
                data['DrugForm_Name'] = thas.FormPanel.getForm().findField('DrugForm_Name').getValue();
                data['KolvoEd'] = thas.FormPanel.getForm().findField('KolvoEd').getValue() || null;
                data['DrugForm_Nick'] = thas.findById(thas.id +'_TreatDrugForm_Nick').text || null;
                data['Kolvo'] = thas.FormPanel.getForm().findField('Kolvo').getValue() || null;
                data['EdUnits_id'] = thas.FormPanel.getForm().findField('EdUnits_id').getValue() || null;
                data['EdUnits_Nick'] = thas.FormPanel.getForm().findField('EdUnits_id').getRawValue() || null;
                data['GoodsUnit_id'] = thas.FormPanel.getForm().findField('GoodsUnit_id').getValue() || null;
                data['GoodsUnit_Nick'] = thas.FormPanel.getForm().findField('GoodsUnit_id').getRawValue() || null;
                data['DrugComplexMnnDose_Mass'] = thas.FormPanel.getForm().findField('DrugComplexMnnDose_Mass').getValue() || null;
                data['DoseDay'] = thas.FormPanel.getForm().findField('DoseDay').getValue() || null;
                data['PrescrDose'] = thas.FormPanel.getForm().findField('PrescrDose').getValue() || null;
                data['GoodsUnit_id'] = thas.FormPanel.getForm().findField('GoodsUnit_id').getValue() || null;
                data['GoodsUnit_sid'] = thas.FormPanel.getForm().findField('GoodsUnit_sid').getValue() || null;
                return data
            },
            saveRecord: function()
            {
                var me = this, num, allow_commit = false, data = me.getDrugFormData();
                if (me._isValidData(data)) {
                    if (me.selectedRec) {
                        num = me.selectedRec.id;
                    } else {
                        num = me.getNewNum();
                    }
                    if (me._drugListData[num]) {
                        //log('updateRecord', data);
                        if (me._drugListData[num].id) {
                            me._drugListData[num].status = 'updated';
                        }
                        allow_commit = true;
                        me._drugListData[num]['Drug_Name'] = data['Drug_Name'];
                        me._drugListData[num]['MethodInputDrug_id'] = data['MethodInputDrug_id'];
                        me._drugListData[num]['DrugComplexMnn_id'] = data['DrugComplexMnn_id'];
                        me._drugListData[num]['Drug_id'] = data['Drug_id'];
                        me._drugListData[num]['DrugForm_Name'] = data['DrugForm_Name'];
                        me._drugListData[num]['KolvoEd'] = data['KolvoEd'];
                        me._drugListData[num]['DrugForm_Nick'] = data['DrugForm_Nick'];
                        me._drugListData[num]['Kolvo'] = data['Kolvo'];
                        me._drugListData[num]['EdUnits_id'] = data['EdUnits_id'];
                        me._drugListData[num]['EdUnits_Nick'] = data['EdUnits_Nick'];
                        me._drugListData[num]['DrugComplexMnnDose_Mass'] = data['DrugComplexMnnDose_Mass'];
                        me._drugListData[num]['DoseDay'] = data['DoseDay'];
                        me._drugListData[num]['PrescrDose'] = data['PrescrDose'];
                        me._drugListData[num]['GoodsUnit_id'] = data['GoodsUnit_id'];
                        me._drugListData[num]['GoodsUnit_sid'] = data['GoodsUnit_sid'];
                        me._drugListData[num]['GoodsUnit_Nick'] = data['GoodsUnit_Nick'];
                    } else if (me.getCountItems() < me.limitCountItem) {
                        //log('addRecord', data);
                        data.status = 'new';
                        data.id = null;
                        allow_commit = true;
                        me._drugListData[num] = data;
                    }
                } else if (me.firstNotValidField) {
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function() {
                            me.firstNotValidField.focus(true);
                        },
                        icon: Ext.Msg.WARNING,
                        msg: ERR_INVFIELDS_MSG,
                        title: ERR_INVFIELDS_TIT
                    });
                }
                if (allow_commit) {
                    me._loadDataView();
                    me.reset(true);
                    me.setDisabledAddBtn(false);
                } else {
                    me.setDisabledAddBtn(true);
                }
            },
            _loadDataView: function()
            {
                var number = 0, num, me = this, arr = [], data;
                for (num in me._drugListData) {
                    if ('deleted' != me._drugListData[num].status) {
                        data = Ext.apply({}, me._drugListData[num]);
                        data.num = num;
                        number++;
                        data.number = number;
                        arr.push(data);
                    }
                }
                me.getStore().removeAll();
                me.getStore().loadData(arr, false);
                me.refresh();
            },
            _isValidData: function(data)
            {
                this.firstNotValidField = null;
                if ( !data['MethodInputDrug_id']
                    || (2 == data['MethodInputDrug_id'] && !data['Drug_id'])
                    || !data['DrugComplexMnn_id']
                    || (data['DrugComplexMnnDose_Mass'] && (!data['Kolvo']
                        || (data['DrugForm_Nick'] && !data['KolvoEd'])
                        || !data['EdUnits_id']
                        || !data['DoseDay']
                        || ('EvnCourseTreatDrug' == this.objectDrug && !data['PrescrDose'])
                        )
                    )
                ) {
                    //log(['is not valid data', data]);
                    switch (true) {
                        case (!data['MethodInputDrug_id']):
                            this.firstNotValidField = thas.FormPanel.getForm().findField('MethodInputDrug_id');
                            break;
                        case (2 == data['MethodInputDrug_id'] && !data['Drug_id']):
                        case (2 == data['MethodInputDrug_id'] && !data['DrugComplexMnn_id']):
                            this.firstNotValidField = thas.FormPanel.getForm().findField('Drug_id');
                            break;
                        case (1 == data['MethodInputDrug_id'] && !data['DrugComplexMnn_id']):
                            this.firstNotValidField = thas.FormPanel.getForm().findField('DrugComplexMnn_id');
                            break;
                        case (!data['Kolvo']):
                            this.firstNotValidField = thas.FormPanel.getForm().findField('Kolvo');
                            break;
                        case (!data['EdUnits_id']):
                            this.firstNotValidField = thas.FormPanel.getForm().findField('EdUnits_id');
                            break;
                        case (!data['KolvoEd']):
                            this.firstNotValidField = thas.FormPanel.getForm().findField('KolvoEd');
                            break;
                    }
                    return false;
                }
                return true;
            },
            _loadEdUnitsStore: function(item, callback)
            {
                if (!this._edUnitsStore) {
                    this._edUnitsStore = new Ext.data.Store({
                        autoLoad: false,
                        reader: new Ext.data.JsonReader({
                            id: 'EdUnits_id'
                        }, [
                            {name: 'EdUnits_id', mapping: 'EdUnits_id'},
                            {name: 'EdUnits_Code', mapping: 'EdUnits_Code', type: 'int'},
                            {name: 'EdUnits_Name', mapping: 'EdUnits_Name'},
                            {name: 'EdUnits_FullName', mapping: 'EdUnits_FullName'}
                        ]),
                        url: '/?c=EvnPrescr&m=loadEdUnitsList'
                    });
                }
                var edUnitsStore = this._edUnitsStore;
                if (edUnitsStore.getCount()>0) {
                    item.findField('EdUnits_id').getStore().loadData(getStoreRecords(edUnitsStore));
                    callback(item);
                } else {
                    edUnitsStore.load({
                        callback: function() {
                            item.findField('EdUnits_id').getStore().loadData(getStoreRecords(edUnitsStore));
                            callback(item);
                        }
                    });
                }
            },
            lastNum: 0,
            getNewNum: function() {
                this.lastNum++;
                return this.lastNum;
            },
            resetLastNum: function() {
                this.lastNum = 0;
            }
        });

        this.lpu_section_combo = new sw.Promed.SwCustomRemoteCombo({
            id: 'ecte_OF_LpuSection_id',
            fieldLabel: langs('Отделение'),
            hiddenName: 'OF_LpuSection_id',
            displayField: 'LpuSection_Name',
            valueField: 'LpuSection_id',
            editable: true,
            allowBlank: true,
            anchor: '100%',
            listWidth: 400,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{LpuSection_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'LpuSection_id'
                }, [
                    {name: 'LpuSection_id', mapping: 'LpuSection_id'},
                    {name: 'LpuSection_Name', mapping: 'LpuSection_Name'}
                ]),
                url: '/?c=EvnPrescr&m=loadLpuSectionCombo'
            }),
            setLinkedFieldValues: function(event_name) {
                if (event_name == 'change' || event_name == 'clear') {
                    thas.setOstatFilter();
                }
            }
        });
        
        this.storage_combo = new sw.Promed.SwCustomRemoteCombo({
            id: 'ecte_OF_Storage_id',
            fieldLabel: langs('Склад'),
            hiddenName: 'OF_Storage_id',
            displayField: 'Storage_Name',
            valueField: 'Storage_id',
            editable: true,
            allowBlank: true,
            anchor: '100%',
            listWidth: 400,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{Storage_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'Storage_id'
                }, [
                    {name: 'Storage_id', mapping: 'Storage_id'},
                    {name: 'Storage_Name', mapping: 'Storage_Name'}
                ]),
                url: '/?c=EvnPrescr&m=loadStorageCombo'
            }),
            setLinkedFieldValues: function(event_name) {
                if (event_name == 'change' || event_name == 'clear') {
                    thas.setOstatFilter();
                }
            }
        });
        
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			frame: false,
			id: 'EvnCourseTreatEditForm',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'DrugListData' },

                { name: 'EvnCourseTreat_id' },
                { name: 'EvnCourseTreat_pid' },
                { name: 'MedPersonal_id' },
                { name: 'LpuSection_id' },
                { name: 'Morbus_id' },
				{ name: 'EvnCourseTreat_setDate' },
                { name: 'EvnCourseTreat_CountDay' },
                //{ name: 'EvnCourseTreat_MaxCountDay' },
                //{ name: 'EvnCourseTreat_MinCountDay' },
				{ name: 'EvnCourseTreat_Duration' },
                { name: 'DurationType_id' },
				{ name: 'EvnCourseTreat_ContReception' },
                { name: 'DurationType_recid' },
				{ name: 'EvnCourseTreat_Interval' },
                { name: 'DurationType_intid' },
                //{ name: 'EvnCourseTreat_FactCount' },
                //{ name: 'EvnCourseTreat_PrescrCount' },
                //{ name: 'ResultDesease_id' },
                { name: 'PrescriptionIntroType_id' },
                { name: 'PrescriptionTreatType_id' },
                //{ name: 'PrescriptionStatusType_id' },
				{ name: 'PerformanceType_id' },

				{ name: 'EvnPrescrTreat_IsCito' },
				{ name: 'EvnPrescrTreat_Descr' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'EvnPrescrTreat_Time' }
			]),
			region: 'center',
			url: '/?c=EvnPrescr&m=saveEvnCourseTreat',

			items: [{
                name: 'accessType', // Режим доступа
                value: '',
                xtype: 'hidden'
            }, {
                name: 'DrugListData',
                value: '',
                xtype: 'hidden'
            }, {
				name: 'EvnCourseTreat_id', // Идентификатор курса
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnCourseTreat_pid', // Идентификатор события
				value: null,
				xtype: 'hidden'
			}, {
                name: 'MedPersonal_id',
                value: null,
                xtype: 'hidden'
            }, {
                name: 'LpuSection_id',
                value: null,
                xtype: 'hidden'
            }, {
                name: 'Morbus_id',
                value: null,
                xtype: 'hidden'
            }, {
                name: 'PrescriptionTreatType_id',
                value: null,
                xtype: 'hidden'
            }, {
				name: 'PersonEvn_id', // Идентификатор состояния человека
				value: null,
				xtype: 'hidden'
			}, {
				name: 'Server_id', // Идентификатор сервера
				value: null,
				xtype: 'hidden'
			}, {
				name: 'PrescriptionStatusType_id', // Идентификатор (Рабочее,Подписанное,Отмененное)
				value: null,
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'Evn_id',
			}, {
				xtype: 'hidden',
				name: 'Person_id',
			},{
                border: false,
                layout: 'column',
                items: [{
                    title: lang['obschie_parametryi_kursa'],
                    xtype: 'fieldset',
                    autoHeight: true,
                    columnWidth: 0.5,
                    style: 'margin: 5px; padding: 10px;',
                    layout: 'form',
                    labelAlign: this.labelAlign,
                    labelWidth: this.labelWidth,
                    items: [{
                        comboSubject: 'PrescriptionIntroType',
                        typeCode: 'int',
                        fieldLabel: lang['sposob_primeneniya'],
                        allowBlank: false,
                        anchor: '100%',
                        tabIndex: TABINDEX_EVNPRESCR + 101,
                        listeners: {
                            change: function(combo, newValue) {
                                combo.fireEvent('select', combo, combo.getStore().getById(newValue));
                            },
                            select: function(combo, record) {
                                if (thas.parentEvnClass_SysNick == 'EvnSection') {
                                    return true;
                                }
                                var base_form = thas.FormPanel.getForm();
                                if(record && record.get('PrescriptionIntroType_Code').toString().inlist(['1','2','3','4','12','13'])) {
                                    base_form.findField('PerformanceType_id').setValue(1);
                                }
                                if(record && record.get('PrescriptionIntroType_Code').toString().inlist(['7','8','9','10','11'])) {
                                    base_form.findField('PerformanceType_id').setValue(2);
                                }
                                if(record && record.get('PrescriptionIntroType_Code').toString().inlist(['5','6'])) {
                                    base_form.findField('PerformanceType_id').setValue(3);
                                }
                                return true;
                            }
                        },
                        xtype: 'swcommonsprcombo'
                    }, {
                        fieldLabel: lang['nachat'],
                        format: 'd.m.Y',
                        allowBlank: false,
                        name: 'EvnCourseTreat_setDate',
                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                        selectOnFocus: true,
                        width: 100,
                        tabIndex: TABINDEX_EVNPRESCR + 103,
                        listeners: {
                            change: function() {
                                thas.TreatDrugListPanel.reCountAll();
                            }
                        },
                        xtype: 'swdatefield'
                    },
					{
						border: false,
						layout: 'column',
						items: [{
								border: false,
								labelWidth: 130,
								layout: 'form',
								items: [
									{
                        allowDecimals: false,
                        allowNegative: false,
                        fieldLabel: lang['priemov_v_sutki'],
                        value: 1,
                        minValue: 1,
                        style: 'text-align: right;',
                        name: 'EvnCourseTreat_CountDay',
                        width: 100,
                        tabIndex: TABINDEX_EVNPRESCR + 104,
                        listeners: {
                            change: function() {
                                thas.TreatDrugListPanel.reCountAll();
                            }
                        },
                        xtype: 'numberfield'
									}]
							},
							{
                        border: false,
								labelWidth: 130,
								layout: 'form',
								items: [
									{
										style: "padding-left: 10px",
										xtype: 'button',
										//id: 'DrugTurnover_BtnClear',
										hidden: !inlist(getRegionNick(),['ufa', 'vologda']),
										text: langs('Время'),
										tabIndex: TABINDEX_EVNPRESCR + 105,
										handler: function () {
											var base_form = thas.FormPanel.getForm();
											var params = new Object();
											params.parent_id = 'EvnCourseTreatEditWindow';
											params.countDay = base_form.findField('EvnCourseTreat_CountDay').getValue();
											params.arr_time = thas.arr_time;
											//base_form.findField('EvnPrescrTreat_Time').setValue(val);
											getWnd('swEvnCourseTreatTimeEntryWindow').show(params);
										}
									}
								]}
						]}, 
					{
//						border: false,
//						layout: 'column',
//						hidden: true, //getGlobalOptions().region.nick != 'ufa',
//						items: [{
								border: false,
								labelWidth: 130,
								width: 315,
								layout: 'form',
								//hidden: true,
								items: [{
                        fieldLabel: langs('Время приема'),
                        height: 40,
                        name: 'EvnPrescrTreat_Time',
						id: 'EvnPrescrTreat_Time',
                        anchor: '100%',
                        //tabIndex: TABINDEX_EVNPRESCR + 115,
                        xtype: 'textarea',
						disabled: true
						//hidden: true
                    }]
					//}
								
						//}]
						/*,
						{
								border: false,
								labelWidth: 130,
								layout: 'form',
								items: [
									{
										style: "padding-left: 10px",
										xtype: 'button',
										//id: 'DrugTurnover_BtnClear',
										width: 200,
										text: langs('Удалить'),
										iconCls: 'delete16',  
										//iconCls: 'reset16',
										tabIndex: TABINDEX_EVNPRESCR + 105,
										handler: function () {
											}
									}
								]}
							*/

						
					//]
				},
					{
                        border: false,
                        layout: 'column',
                        items: [{
                            border: false,
                            labelWidth: 130,
                            layout: 'form',
                            items: [{
                                allowDecimals: false,
                                allowNegative: false,
                                fieldLabel: 'Продолжительность',//Продолжительность курса
                                value: 1,
                                minValue: 1,
                                style: 'text-align: right;',
                                name: 'EvnCourseTreat_Duration',
                                width: 100,
                                tabIndex: TABINDEX_EVNPRESCR + 106,
                                listeners: {
                                    change: function(field, newValue) {
                                        var base_form = thas.FormPanel.getForm();
                                        base_form.findField('EvnCourseTreat_ContReception').setValue(newValue);
                                        thas.TreatDrugListPanel.reCountAll();
                                    }
                                },
                                xtype: 'numberfield'
                            },{
                                allowDecimals: false,
                                allowNegative: false,
                                fieldLabel: lang['nepreryivnyiy_priem'],
                                value: 1,
                                minValue: 1,
                                style: 'text-align: right;',
                                name: 'EvnCourseTreat_ContReception',
                                width: 100,
                                tabIndex: TABINDEX_EVNPRESCR + 108,
                                listeners: {
                                    change: function() {
                                        thas.TreatDrugListPanel.reCountAll();
                                    }
                                },
                                xtype: 'numberfield'
                            },{
                                allowDecimals: false,
                                allowNegative: false,
                                fieldLabel: lang['pereryiv'],
                                value: 0,
                                minValue: 0,
                                style: 'text-align: right;',
                                name: 'EvnCourseTreat_Interval',
                                width: 100,
                                tabIndex: TABINDEX_EVNPRESCR + 110,
                                listeners: {
                                    change: function() {
                                        thas.TreatDrugListPanel.reCountAll();
                                    }
                                },
                                xtype: 'numberfield'
                            }]
                        }, {
                            border: false,
                            layout: 'form',
                            style: 'margin-left: 10px; padding: 0px;',
                            items: [{
                                hiddenName: 'DurationType_id',//Тип продолжительности
                                width: 70,
                                value: 1,
                                tabIndex: TABINDEX_EVNPRESCR + 107,
                                listeners: {
                                    change: function(combo, newValue) {
                                        var base_form = thas.FormPanel.getForm();
                                        var record = combo.getStore().getById(newValue);
                                        if ( !record ) {
                                            return false;
                                        }
                                        base_form.findField('DurationType_recid').setValue(newValue);
                                        base_form.findField('DurationType_intid').setValue(newValue);
                                        thas.TreatDrugListPanel.reCountAll();
                                        return true;
                                    }
                                },
                                xtype: 'swdurationtypecombo'
                            },{
                                hiddenName: 'DurationType_recid',//Тип Непрерывный прием
                                width: 70,
                                value: 1,
                                tabIndex: TABINDEX_EVNPRESCR + 109,
                                listeners: {
                                    change: function() {
                                        thas.TreatDrugListPanel.reCountAll();
                                    }
                                },
                                xtype: 'swdurationtypecombo'
                            },{
                                hiddenName: 'DurationType_intid',//Тип Перерыв
                                width: 70,
                                value: 1,
                                tabIndex: TABINDEX_EVNPRESCR + 111,
                                listeners: {
                                    change: function() {
                                        thas.TreatDrugListPanel.reCountAll();
                                    }
                                },
                                xtype: 'swdurationtypecombo'
                            }]
                        }]
                    }, {
                        comboSubject: 'PerformanceType',
                        fieldLabel: lang['ispolnenie'],
                        anchor: '100%',
                        tabIndex: TABINDEX_EVNPRESCR + 113,
                        xtype: 'swcommonsprcombo'
                    }, {
                        boxLabel: 'Cito',
                        checked: false,
                        fieldLabel: '',
                        labelSeparator: '',
                        name: 'EvnPrescrTreat_IsCito',
                        tabIndex: TABINDEX_EVNPRESCR + 114,
                        xtype: 'checkbox'
                    }, {
                        fieldLabel: lang['kommentariy'],
                        height: 70,
                        name: 'EvnPrescrTreat_Descr',
                        anchor: '100%',
                        tabIndex: TABINDEX_EVNPRESCR + 115,
                        xtype: 'textarea'
                    }]
                }, {
                    title: lang['medikament'],
                    xtype: 'fieldset',
                    autoHeight: true,
                    columnWidth: 0.5,
                    style: 'margin: 5px; margin-left: 0; padding: 10px;',
                    layout: 'form',
                    labelAlign: this.labelAlign,
                    labelWidth: 160,
                    items: [{
                        name: 'id',
                        value: null,
                        xtype: 'hidden'
                    }, {
                        name: 'DrugForm_Name',
                        value: null,
                        xtype: 'hidden'
                    }, {
                        name: 'DrugComplexMnnDose_Mass',
                        value: null,
                        xtype: 'hidden'
                    }, {
                        id: 'ecte_OstatFilterPanel',
                        layout: 'form',
                        border: false,
                        labelWidth: 80,
                        width: 395,
                        items: [{
                            layout: 'column',
                            border: false,
                            items: [{
                                layout: 'form',
                                border: false,
                                style: 'margin-top: -1px;',
                                items: [{
                                    xtype: 'checkbox',
                                    name: 'OstatFilter_isActive',
                                    boxLabel: '<span style="font-size: 12px;">Из остатков</span>',
                                    hideLabel: true,
                                    listeners: {
                                        check: function(field, newValue) {
                                            if (newValue) {
                                                thas.setOstatFilterType('auto');
                                            } else {
                                                thas.setOstatFilterType(null);
                                            }
                                        }
                                    }
                                }]
                            }, {
                                layout: 'form',
                                border: false,
                                width: 305,
                                items: [
                                    this.lpu_section_combo,
                                    this.storage_combo
                                ]
                            }]
                        }, {
                            xtype: 'checkbox',
                            name: 'CentralStorageOstatFilter_isActive',
                            boxLabel: 'Из остатков центрального склада',
                            hideLabel: true,
                            listeners: {
                                check: function(field, newValue) {
                                    thas.setOstatFilter();
                                }
                            }
                        }]
                    }, {
                        xtype: 'radiogroup',
                        hideLabel: true,
                        name: 'MethodInputDrug_id',
                        anchor: '100%',
                        columns: 2,
                        items: [
                            {
                                boxLabel: lang['mnn'],
                                name: 'MethodInputDrug_id',
                                value: 1,
                                tabIndex: TABINDEX_EVNPRESCR + 120,
                                listeners: {
                                    check: function(radio, checked) {
                                        if (checked) {
                                            thas.TreatDrugListPanel.onSelectMethodInputDrug(1, true);
                                            thas.setDefaultDrugPackValues();
                                            thas.setDrugName();
											thas.hideIcons();
                                        }
                                    }
                                }
                            },
                            {
                                boxLabel: lang['torgovoe_naimenovanie'],
                                name: 'MethodInputDrug_id',
                                value: 2,
                                tabIndex: TABINDEX_EVNPRESCR + 121,
                                listeners: {
                                    check: function(radio, checked) {
                                        if (checked) {
                                            thas.TreatDrugListPanel.onSelectMethodInputDrug(2, true);
                                            thas.setDefaultDrugPackValues();
                                            thas.setDrugName();
											thas.hideIcons();
                                        }
                                    }
                                }
                            }
                        ],
                        getValue: function() {
                            var out = [];
                            this.items.each(function(item){
                                if (item.checked){
                                    out.push(item.value);
                                }
                            });
                            return out.join(',');
                        },
                        setCheckedValue: function(v) {
                            this.items.each(function(item){
                                item.setValue(false);
                                if(item.value == v) {
                                    item.setValue(true);
                                }
                            });
                            return true;
                        },
                        getActiveCombo: function() {
                            var combo_name = this.getValue() == 1 ? 'DrugComplexMnn_id' : 'Drug_id';
                            return thas.FormPanel.getForm().findField(combo_name)
                        }
                    }, {
                        hiddenName: 'DrugComplexMnn_id',
                        hideLabel: true,
                        listWidth: 500,
                        onClearValue: thas.TreatDrugListPanel.onClearDrug,
                        anchor: '100%',
                        tabIndex: TABINDEX_EVNPRESCR + 122,
                        listeners: {
                            change: function(combo, newValue) {
                                thas.TreatDrugListPanel.onChangeDrug('DrugComplexMnn_id', combo, newValue);
                                thas.setDefaultDrugPackValues();
                                thas.setDrugName();
                            },
                            select: function(combo, record) {
                                thas.TreatDrugListPanel.onSelectDrug('DrugComplexMnn_id', combo, record);
                                thas.setDefaultDrugPackValues();
                                thas.setDrugName();
								thas.checkRecomendDose(record);
								thas.checkPersonDrugReactionInEvn(record.id);
                            }
                        },
                        xtype: 'swdrugcomplexmnncombo'
                    }, {
                        hiddenName: 'Drug_id',
                        hideLabel: true,
                        listWidth: 500,
                        onClearValue: thas.TreatDrugListPanel.onClearDrug,
                        anchor: '100%',
                        tabIndex: TABINDEX_EVNPRESCR + 122,
                        listeners: {
                            change: function(combo, newValue) {
                                thas.TreatDrugListPanel.onChangeDrug('Drug_id', combo, newValue);
                                thas.setDefaultDrugPackValues();
                                thas.setDrugName();
                            },
                            select: function(combo, record) {
                                thas.TreatDrugListPanel.onSelectDrug('Drug_id', combo, record);
                                thas.setDefaultDrugPackValues();
                                thas.setDrugName();
                            }
                        },
                        xtype: 'swdrugsimplecombo'
                    }, {
                        xtype: 'textarea',
                        name: 'DrugNameTextarea',
                        hideLabel: true,
                        anchor: '100%',
                        disabled: true
                    }, {
                        layout: 'column',
                        border: false,
                        items: [{
                            layout: 'form',
                            border: false,
                            items: [{
                                name: 'KolvoEd',
                                allowDecimals: true,
                                allowNegative: false,
                                decimalPrecision: 5,
                                fieldLabel: 'Кол-во ЛС на 1 прием',
                                width: 90,
                                tabIndex: TABINDEX_EVNPRESCR + 123,
                                listeners: {
                                    render: function(e) {
                                        Ext.QuickTips.register({
                                            target: e.getEl(),
                                            text: 'Указывается количество лекарственного средства (медикамента) в виде количества лекарственных форм,  которое должно быть принято за 1 прием – таблетки, мл, капли и т.п.'
                                        });
                                    },
                                    change: function(fieldKolvoEd, newValue) {
                                        /*var fieldKolvo = thas.FormPanel.getForm().findField('Kolvo'),
                                            fieldEdUnits = thas.FormPanel.getForm().findField('EdUnits_id'),
                                            kolvo = null,
                                            kolvoEd = null;
                                        if (!newValue) {
                                            fieldKolvo.setValue(kolvo);
                                            fieldKolvoEd.setValue(kolvoEd);
                                        } else if (thas.TreatDrugListPanel.doseData.kolvo) {
                                            kolvoEd = newValue;
                                            kolvo = thas.TreatDrugListPanel.doseData.kolvo*newValue;
                                            fieldKolvo.setValue(kolvo);
                                            fieldKolvoEd.setValue(kolvoEd);
                                            if (thas.TreatDrugListPanel.doseData.EdUnits_id) {
                                                fieldEdUnits.setValue(thas.TreatDrugListPanel.doseData.EdUnits_id);
                                            }
                                        }*/
                                        var fieldKolvo = thas.FormPanel.getForm().findField('Kolvo');
                                        var kolvo = fieldKolvo.getValue();
                                        var kolvoEd = newValue > 0 ? newValue : 0;
                                        var dpd = thas.getDrugPackData();

										if (getGlobalOptions().region.nick != 'ufa') {
											if (dpd.GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_sid').getValue() &&
												((dpd.FasMass_GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_id').getValue() && dpd.DoseMass_Type == 'Concen')
													|| (dpd.DoseMass_GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_id').getValue() && dpd.DoseMass_Type == 'Mass'))) { // Если емкость первичной упаковки совпадает ед. измерения дозировки;
												if (kolvoEd > 0 && dpd.Fas_NKolvo > 0) { //если фасовка № 1 перерасчет не происходит
													if (dpd.Fas_Kolvo > 0 && dpd.DoseMass_Kolvo > 0 && (dpd.DoseMass_Type == 'Mass' || dpd.DoseMass_Type == 'KolACT')) { //если указано значащее количество лекарственных форм в первичной упаковке; если дозировка задана ед. изм. массы или активными ед.
														kolvo = kolvoEd * dpd.DoseMass_Kolvo;
													} else if (dpd.FasMass_Kolvo > 0) { //указаны или масса или объем первичной упаковки
														kolvo = kolvoEd*dpd.FasMass_Kolvo;
													}
												}

												fieldKolvo.setValue(kolvo);
												thas.TreatDrugListPanel.reCount();
											}
										} else {				
											//  #115813
											if (dpd.GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_sid').getValue()) {
												//log(dpd.FasMass_GoodsUnit_id , dpd.DoseMass_GoodsUnit_id, thas.FormPanel.getForm().findField('GoodsUnit_id').getValue());
												if (dpd.FasMass_GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_id').getValue()) {
													// Если ед. изм.  - фасовка
													kolvo = kolvoEd*dpd.FasMass_Kolvo; 
												} 
												else if (dpd.DoseMass_GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_id').getValue()) {
													// Если ед. изм. - дозировка
													kolvo = kolvoEd * dpd.DoseMass_Kolvo; 
												}
											}
											fieldKolvo.setValue(kolvo);
											thas.TreatDrugListPanel.reCount();
											}
										}
                                },
                                minValue: 0.0001,
                                xtype: 'numberfield'
                            }]
                        }, {
                            layout: 'form',
                            border: false,
                            style: 'padding: 0; padding-left: 5px;',
                            items: [{
                                xtype: 'swgoodsunitcombo',
                                hideLabel: true,
                                listWidth: 160,
                                width: 132,
                                hiddenName: 'GoodsUnit_sid',
                                tabIndex: TABINDEX_EVNPRESCR + 123
                            }]
                        }]
                    }, {
                        layout: 'column',
                        border: false,
                        items: [{
                            border: false,
                            layout: 'form',
                            items: [{
                                name: 'Kolvo',
                                allowDecimals: true,
                                allowNegative: false,
                                decimalPrecision: 5,
                                fieldLabel: lang['doza_na_1_priem'],
                                width: 90,
                                tabIndex: TABINDEX_EVNPRESCR + 125,
                                listeners: {
                                    render: function(e) {
                                        Ext.QuickTips.register({
                                            target: e.getEl(),
                                            text: 'Количество действующего вещества в дозе должно быть указано в мерах веса или объема лекарственного вещества – граммах, мили- или микрограммах,  миллилитрах, каплях, или в специальных единицах измерения – Ед, МЕ и др.'
                                        });
                                    },
                                    change: function(fieldKolvo, newValue) {
                                        /*var fieldKolvoEd = thas.FormPanel.getForm().findField('KolvoEd'),
                                            fieldEdUnits = thas.FormPanel.getForm().findField('EdUnits_id'),
                                            kolvo = null,
                                            kolvoEd = null;
                                        if (!newValue) {
                                            fieldKolvo.setValue(kolvo);
                                            fieldKolvoEd.setValue(kolvoEd);
                                        } else if (thas.TreatDrugListPanel.doseData.kolvo) {
                                            kolvo = newValue;
                                            kolvoEd = newValue/thas.TreatDrugListPanel.doseData.kolvo;
                                            fieldKolvo.setValue(kolvo);
                                            fieldKolvoEd.setValue(kolvoEd);
                                            if (thas.TreatDrugListPanel.doseData.EdUnits_id) {
                                                fieldEdUnits.setValue(thas.TreatDrugListPanel.doseData.EdUnits_id);
                                            }
                                        }*/

                                        var fieldKolvoEd = thas.FormPanel.getForm().findField('KolvoEd');
                                        var kolvoEd = fieldKolvoEd.getValue();
                                        var kolvo = newValue > 0 ? newValue : 0;
                                        var dpd = thas.getDrugPackData();
										
										if (getGlobalOptions().region.nick != 'ufa') {
											if (dpd.GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_sid').getValue() &&
												((dpd.FasMass_GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_id').getValue() && dpd.DoseMass_Type == 'Concen')
													|| (dpd.DoseMass_GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_id').getValue() && dpd.DoseMass_Type == 'Mass'))) { // Если емкость первичной упаковки совпадает ед. измерения дозировки;
												if (kolvo > 0 && dpd.Fas_NKolvo > 0) { //если фасовка № 1 перерасчет не происходит
													if (dpd.Fas_Kolvo > 0 && dpd.DoseMass_Kolvo > 0 && (dpd.DoseMass_Type == 'Mass' || dpd.DoseMass_Type == 'KolACT')) { //если указано значащее количество лекарственных форм в первичной упаковке; если дозировка задана ед. изм. массы или активными ед.
														kolvoEd = kolvo/dpd.DoseMass_Kolvo;
													} else if (dpd.FasMass_Kolvo > 0) { //указаны или масса или объем первичной упаковки
														kolvoEd = kolvo/dpd.FasMass_Kolvo;
													}
												}

												fieldKolvoEd.setValue(kolvoEd);
												thas.TreatDrugListPanel.reCount();
											}
										} else {
											//  #115813
											if (dpd.GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_sid').getValue()) {
												if (dpd.FasMass_GoodsUnit_id ==  thas.FormPanel.getForm().findField('GoodsUnit_id').getValue()) {
													// Если ед. изм.  - фасовка
													kolvoEd = kolvo/dpd.FasMass_Kolvo;
												} else {
														/*
														Если в справочнике дозировка в граммах
														а пользователь вводит в мг -
														при расчете переводим в одну ед. измерения
														*/
														$DoseMass_GoodsUnit_id = dpd.DoseMass_GoodsUnit_id;
														$GoodsUnit_id = thas.FormPanel.getForm().findField('GoodsUnit_id').getValue();
														$DoseMass_Kolvo = dpd.DoseMass_Kolvo;
													if ($DoseMass_GoodsUnit_id == 1 && $GoodsUnit_id == 2) {
														$DoseMass_GoodsUnit_id = $GoodsUnit_id;
														$DoseMass_Kolvo = $DoseMass_Kolvo * 1000;
													}											
													if ($DoseMass_GoodsUnit_id ==  $GoodsUnit_id) {
														// Если ед. изм. - дозировка
														kolvoEd = kolvo/$DoseMass_Kolvo;
													}
												}
												kolvoEd = kolvoEd.toFixed(6); 
												fieldKolvoEd.setValue(kolvoEd);
												thas.TreatDrugListPanel.reCount();
											}
										}
									}
								},
                                xtype: 'numberfield'
                            }]
                        }, {
                            layout: 'form',
                            border: false,
                            style: 'padding: 0; padding-left: 5px;',
                            items: [{
                                xtype: 'swgoodsunitcombo',
                                hideLabel: true,
                                listWidth: 160,
                                width: 132,
                                hiddenName: 'GoodsUnit_id',
								filterList: '',
                                tabIndex: TABINDEX_EVNPRESCR + 125,
                                listeners: {
                                    change: function() {
										if (getGlobalOptions().region.nick != 'ufa') {
											thas.TreatDrugListPanel.reCount();
										} else {
											//KolvoEd = thas.FormPanel.getForm().findField('KolvoEd').getValue();
											//thas.FormPanel.getForm().findField('KolvoEd').fireEvent('change', KolvoEd, KolvoEd);
											Kolvo = thas.FormPanel.getForm().findField('Kolvo').getValue();
											thas.FormPanel.getForm().findField('Kolvo').fireEvent('change', Kolvo, Kolvo);
										}
                                    },
									expand: function(combo){
										if (getGlobalOptions().region.nick == 'ufa') {
											thas.FormPanel.getForm().findField('GoodsUnit_id').getStore().clearFilter();
											if (combo.filterList != '') {
												var $list = combo.filterList.split(',');
												thas.FormPanel.getForm().findField('GoodsUnit_id').getStore().filterBy(function(record) {return record.get('GoodsUnit_id').inlist($list);})
											}
										}
									}
                                }
                            }]
                        }, {
                            border: false,
                            layout: 'form',
                            style: 'padding: 0; padding-left: 5px;',
                            hidden: true,
                            items: [{
                                hiddenName: 'EdUnits_id',
                                hideLabel: true,
                                width: 100,
                                tabIndex: TABINDEX_EVNPRESCR + 124,
                                listeners: {
                                    change: function() {
                                        thas.TreatDrugListPanel.reCount();
                                    }
                                },
                                xtype: 'swedunitscombo'
                            }, new Ext.form.Label({
                                id: thas.id +'_TreatDrugForm_Nick',
                                style: 'font-size: 9pt;',
                                width: 100,
                                text: ''
                            })]
                        }]
                    },{
						layout: 'column',
						border: false,
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('Суточная доза'),
								name: 'DoseDay',
								readOnly: true,
								width: 90,
								xtype: 'textfield',
								tabIndex: TABINDEX_EVNPRESCR + 126
							}]
						}, {
							layout: 'form',
							border: false,
							style: 'padding: 0; padding-left: 5px;',
							items: [{
								xtype: 'label',
								id: 'DoseDay_warnIcon',
								hidden: true
							}]
						}]
					}, {
						layout: 'column',
						border: false,
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['kursovaya_doza'],
								name: 'PrescrDose',
								readOnly: true,
								width: 90,
								xtype: 'textfield',
								tabIndex: TABINDEX_EVNPRESCR + 126
							}]
						}, {
							layout: 'form',
							border: false,
							style: 'padding: 0; padding-left: 5px;',
							items: [{
								xtype: 'label',
								id: 'PrescrDose_warnIcon',
								hidden: true
							}]
						}]
					},{
                        layout: 'column',
                        border: false,
                        items: [{
                            border: false,
                            layout: 'form',
                            items: [this.treatDrugAddBtn]
                        }, {
                            border: false,
                            layout: 'form',
                            style: 'padding: 0; padding-left: 5px;',
                            items: [this.treatDrugResetBtn]
                        }]
                    }]
                }]
            }, {
                xtype: 'panel',
                autoScroll: true,
                layout:'fit',
                border: false,
                frame: false,
                style: 'margin: 5px; padding: 10px;',
                items: [
                    this.TreatDrugListPanel
                ]
            }]
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			//HelpButton(this, -1),
			{
                handler: function() {
                    thas.doSave();
                },
                iconCls: 'save16',
                tabIndex: TABINDEX_EVNPRESCR + 128,
                text: BTN_FRMSAVE
			},
			{
				handler: function() {
                    thas.hide();
				},
				onTabAction: function () {
                    thas.FormPanel.getForm().findField('PrescriptionIntroType_id').focus(true, 250);
                },
				iconCls: 'cancel16',
				tabIndex: TABINDEX_EVNPRESCR + 129,
				text: BTN_FRMCANCEL
            }],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnCourseTreatEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swEvnCourseTreatEditWindow.superclass.show.apply(this, arguments);

        var thas = this;
		thas.arr_time = new Array();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
        this.TreatDrugListPanel.reset();
		Ext.getCmp('EvnPrescrTreat_Time').ownerCt.hide();
		base_form.findField('EvnCourseTreat_CountDay').setDisabled(false);

		this.parentWin = null;
		this.Diag_id = null;
		this.parentEvnClass_SysNick = null;
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.OstatFilter_Type = null;
		this.UserLpuSection_id = null;
		this.UserLpuUnitType_id = null;
		if (getGlobalOptions().region.nick == 'ufa') {	
			var tplMNN =  new Ext.XTemplate(
				'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
				'<td style="padding: 2px;; word-wrap:break-word;">Медикамент</td>',
				'<td style="padding: 5px;;">Остаток с учетом назначения</td>',
				'<td style="padding: 5px;">Остаток на складе</td>',
				'<td style="padding: 5px;">Назначено</td>',

				'</tr><tpl for="."><tr class="x-combo-list-item" >',
				'<td><div style="padding: 5px; width: 450px; overflow: hidden; text-overflow: ellipsis;">{DrugComplexMnn_Name}&nbsp;</div></td>',
				'<td style="padding: 5px; text-align: right; width: 10%;">{Ostat_Kolvo}&nbsp;</td>',
				'<td style="padding: 5px; text-align: right; width: 10%;">{DrugOstatRegistry_Kolvo}&nbsp;</td>',
				'<td style="padding: 5px; text-align: right; width: 10%;">{EvnCourseTreatDrug_Count}&nbsp;</td>',
				 '</tr></tpl>',
				'</table>'
			);
						
			var mnn_combo = thas.FormPanel.getForm().findField('DrugComplexMnn_id');
			mnn_combo.tpl = tplMNN;
			mnn_combo.listWidth = 720;
			
			var tplTorg =  new Ext.XTemplate(
				'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
				'<td style="padding: 2px; width: 70%;  word-wrap:break-word;">Медикамент</td>',
				'<td style="padding: 5px;width: 10%;">Остаток с учетом назначения</td>',
				'<td style="padding: 5px;width: 10%;">Остаток на складе</td>',
				'<td style="padding: 5px;width: 10%;">Назначено</td>',

				'</tr><tpl for="."><tr class="x-combo-list-item" >',
				'<td><div style="padding: 5px; width: 450px; overflow: hidden; text-overflow: ellipsis;">{Drug_Name}&nbsp;</div></td>',
				'<td style="padding: 5px; idth: 10%; text-align: right;">{Ostat_Kolvo}&nbsp;</td>',
				'<td style="padding: 5px; idth: 10%; text-align: right;">{DrugOstatRegistry_Kolvo}&nbsp;</td>',
				'<td style="padding: 5px; idth: 10%; text-align: right;">{EvnCourseTreatDrug_Count}&nbsp;</td>',
				'</tr></tpl>',
				'</table>'
			);
					
			var d_combo = thas.FormPanel.getForm().findField('Drug_id');
			d_combo.tpl = tplTorg;
			d_combo.listWidth = 720;	
		}

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { thas.hide(); } );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
			this.action = arguments[0].action;
		}
		
		if ( arguments[0].parentWin && typeof arguments[0].parentWin == 'object' ) {
			this.parentWin = arguments[0].parentWin;
		}
		
		if ( arguments[0].Diag_id && typeof arguments[0].Diag_id == 'string' ) {
			this.Diag_id = arguments[0].Diag_id;
		}

		if ( arguments[0].parentEvnClass_SysNick && typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
			this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].userMedStaffFact ) {
            if (!Ext.isEmpty(arguments[0].userMedStaffFact.LpuSection_id)) {
                this.UserLpuSection_id = arguments[0].userMedStaffFact.LpuSection_id;
            }
            if (!Ext.isEmpty(arguments[0].userMedStaffFact.LpuUnitType_id)) {
                this.UserLpuUnitType_id = arguments[0].userMedStaffFact.LpuUnitType_id;
            }
        }

        if (!Ext.isEmpty(arguments[0].UserLpuSection_id)) {
            this.UserLpuSection_id = arguments[0].UserLpuSection_id;
        }
        if (!Ext.isEmpty(arguments[0].UserLpuUnitType_id)) {
            this.UserLpuUnitType_id = arguments[0].UserLpuUnitType_id;
        };
		if (!Ext.isEmpty(arguments[0].formParams.EvnCourseTreat_pid)) {
			base_form.findField('Evn_id').setValue(arguments[0].formParams.EvnCourseTreat_pid);
		}
		if (!Ext.isEmpty(arguments[0].formParams.Person_id)) {
			base_form.findField('Person_id').setValue(arguments[0].formParams.Person_id);
		};

		this.hideIcons();

        var setDateField = base_form.findField('EvnCourseTreat_setDate'),
            firstField = base_form.findField('PrescriptionIntroType_id');

        this.resetDrugPackData();

        this.lpu_section_combo.fullReset();
        this.storage_combo.fullReset();
        this.lpu_section_combo.getStore().baseParams.UserLpuSection_id = this.UserLpuSection_id;
        this.storage_combo.getStore().baseParams.UserLpuSection_id = this.UserLpuSection_id;
        this.setOstatFilterType('auto', true);

		this.getLoadMask(LOAD_WAIT).show();

		switch ( this.action ) {
            case 'add':
				this.getLoadMask().hide();
				base_form.clearInvalid();
				this.setTitle(this.winTitle + lang['_dobavlenie']);
				this.enableEdit(true);
                //чтобы выбирать с остатков отделения
                this.TreatDrugListPanel.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
                this.TreatDrugListPanel.LpuSection_id = base_form.findField('LpuSection_id').getValue();
                if (!setDateField.getValue()) {
                    setDateField.setValue(getGlobalOptions().date);
                }
                this.TreatDrugListPanel.onLoadForm();
                this.TreatDrugListPanel.onSelectRecord(null);
                if (firstField) {
                    firstField.focus(true, 250);
                }
                //подсвечиваем обязательные поля
                base_form.isValid();
			break;
			case 'edit':
			case 'view':
				base_form.load({
					failure: function() {
                        thas.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { thas.hide(); } );
					},
					params: {
						EvnCourseTreat_id: base_form.findField('EvnCourseTreat_id').getValue(),
						parentEvnClass_SysNick: this.parentEvnClass_SysNick
					},
					success: function() {
                        thas.getLoadMask().hide();
						base_form.clearInvalid();
						if ( base_form.findField('accessType').getValue() == 'view' ) {
                            thas.action = 'view';
						}
						if ( thas.action == 'edit' ) {
                            thas.setTitle(thas.winTitle + lang['_redaktirovanie']);
                            thas.enableEdit(true);
						} else {
                            thas.setTitle(thas.winTitle + lang['_prosmotr']);
                            thas.enableEdit(false);
						}

                        //чтобы выбирать с остатков отделения
                        thas.TreatDrugListPanel.parentEvnClass_SysNick = thas.parentEvnClass_SysNick;
                        thas.TreatDrugListPanel.LpuSection_id = base_form.findField('LpuSection_id').getValue();
                        thas.TreatDrugListPanel.onLoadForm(base_form.findField('DrugListData').getValue());
						//Для Уфы и Вологды Обработка времени приема
						if (inlist(getRegionNick(),['ufa', 'vologda'])) {
							thas.arr_time = eval(base_form.findField('EvnPrescrTreat_Time').getValue()); //Преобразование строки в массив объектов
							if (thas.arr_time) {
								thas.setEvnPrescrTreatTime(base_form, thas.arr_time);
							} 
							base_form.findField('EvnPrescrTreat_Time').ownerCt.setVisible(thas.arr_time);
							base_form.findField('EvnPrescrTreat_Time').ownerCt.doLayout();
							thas.syncShadow(); //перерисовка тени под изменившееся окно
						}					
						
						if ( thas.action == 'edit' ) {
							if ( firstField ) {
                                firstField.focus(true, 250);
                            }
						} else {
                            thas.buttons[1].focus();
						}
					},
					url: '/?c=EvnPrescr&m=loadEvnCourseTreatEditForm'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}

        this.center();
        return true;
	}
});