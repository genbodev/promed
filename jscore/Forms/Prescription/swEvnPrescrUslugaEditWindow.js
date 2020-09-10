/**
* swEvnPrescrUslugaEditWindow - форма редактирования назначений услуг
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      12.2013
 *
 Логика редактирования назначения
 1. При открытии формы нет ни направления, ни заказа (форма открыта для копирования, редактирования)
 +lang['variantyi']+
 1.1. Служба для направления НЕ была выбрана
 Загрузка состава для выбранной UslugaComplex_id из справочника v_UslugaComplexComposition (в составе отмечаем те услуги, что были выбраны ранее), а также загрузка лимитов для назначения лаб.услуги
 Сохраняем только назначение
 1.2. Была выбрана служба для направления
 Загрузка состава для выбранной UslugaComplexMedService_id из v_UslugaComplexMedService (в составе по умолчанию отмечаем все услуги), а также лимитов для назначения лаб.услуги
 Сохраняем назначение, направление, заказ (для лаб.услуги должна быть хотя бы одна услуга в составе, если есть состав у услуги)
 1.3. Была перевыбрана служба для направления
 Загрузка состава для новой выбранной UslugaComplexMedService_id из v_UslugaComplexMedService (в составе по умолчанию отмечаем все услуги), а также лимитов для назначения лаб.услуги
 Сохраняем назначение, направление, заказ (для лаб.услуги должна быть хотя бы одна услуга в составе, если есть состав у услуги)
 1.4. Выбранная служба для направления была удалена (очищен комбик запись)
 Очистка панели состава, а также лимитов для назначения лаб.услуги
 Сохраняем только назначение

 2. При открытии формы у назначения есть направление и должен быть заказ
 Если назначение выполнено или заказ выполнен или нет прав для редактирования, то форма только для чтения
 +lang['variantyi']+
 2.1. Поле запись не изменялось
 Загрузка состава по UslugaComplexMedService_id из v_UslugaComplexMedService (в составе отмечаем те услуги, что были выбраны ранее из заказа), а также лимитов для назначения лаб.услуги
 Сохраняем только назначение, если был изменен заказ, то также обновляем состав в заказе
 2.2. Была перевыбрана служба для направления
 Загрузка состава для новой выбранной UslugaComplexMedService_id из v_UslugaComplexMedService (в составе по умолчанию отмечаем все услуги), а также лимитов для назначения лаб.услуги
 Отменяем старое направление и заказ.
 Сохраняем назначение, направление, заказ (для лаб.услуги должна быть хотя бы одна услуга в составе, если есть состав у услуги)
 1.4. Выбранная служба для направления была удалена (очищен комбик запись)
 Очистка панели состава, а также лимитов для назначения лаб.услуги
 Отменяем старое направление и заказ.
 Сохраняем только назначение
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrUslugaEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swEvnPrescrUslugaEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrUslugaEditWindow.js',

	action: null,
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	autoHeight: true,
	width: 700,
	closable: true,
	closeAction: 'hide',
	split: true,
	layout: 'form',
	id: 'EvnPrescrUslugaEditWindow',
	modal: true,
	plain: true,
	resizable: false,
	params: {
		pmUser_insID: null,
		PersonDetailEvnDirection_id: null,
		UslugaComplex_id: null,
		HormonalPhaseType_id: null,
		HIVContingentTypeFRMIS_id: null,
		CovidContingentType_id: null,
		UslugaComplex_List: null,
		Person_id: null,
		Sex_id: null,
		RaceType_id: null,
		PersonHeight_Height: null,
		PersonHeight_setDT: null,
		PersonWeight_WeightText: null,
		PersonWeight_setDT: null
	},
	UslugaComplex_AttributeList: [],
	hiddenFields: [
		'RaceType_id',
		'PersonHeight_Height',
		'PersonHeight_setDT',
		'PersonWeight_WeightText',
		'PersonWeight_setDT'
	],
	listeners: {
		hide: function(win) {
			for (var i in win.params) {
				win.params[i] = null;
			}
			win.unlock();
			win.onHide();
		}
    },
    /**
     * Отмена направления
     * @param ident {string}
     * @param callback {function}
     * @private
     */
    _cancelEvnDirection: function(ident, callback)
    {
		var key_parts = ident.split('_');
		return sw.Promed.Direction.cancel({
			cancelType: 'cancel',
			ownerWindow: this,
			TimetableMedService_id: ('TimetableMedService' == key_parts[0]) ? key_parts[1] : null,
			TimetableResource_id: ('TimetableResource' == key_parts[0]) ? key_parts[1] : null,
			EvnQueue_id: ('EvnQueue' == key_parts[0]) ? key_parts[1] : null,
			callback: function(cfg) {
				callback(null);
			}
		});
    },
    /**
     * Сохраняем назначение-направление
     */
    _save: function(options, callback)
    {
        var thas = this,
            wnd = this,
            base_form = this.FormPanel.getForm(),
            evndirection_combo = base_form.findField('EvnDirection_id'),
            formParams = base_form.getValues(),
            evnPrescrData = { //параметры для сохранения назначения
                PersonEvn_id: formParams.PersonEvn_id
                ,Server_id: formParams.Server_id
                ,EvnCourse_id: formParams.EvnCourse_id
                ,StudyTarget_id: formParams.StudyTarget_id
                ,parentEvnClass_SysNick: this.parentEvnClass_SysNick
                ,signature: (options.signature)?1:0
                ,HormonalPhaseType_id: !Ext.isEmpty(formParams.HormonalPhaseType_id) ? formParams.HormonalPhaseType_id : null
	            ,HIVContingentTypeFRMIS_id: !Ext.isEmpty(formParams.HIVContingentTypeFRMIS_id) ? formParams.HIVContingentTypeFRMIS_id : null
	            ,CovidContingentType_id: formParams.CovidContingentType_id || null
	            ,PersonDetailEvnDirection_id: thas.params.PersonDetailEvnDirection_id
            },
            params = { //параметры для функции создания направления
                person: {
                    Person_id: evndirection_combo.Person_id
                    ,PersonEvn_id: formParams.PersonEvn_id
                    ,Server_id: formParams.Server_id
                },
                needDirection: false,
                mode: 'nosave',
                loadMask: false,
                windowId: 'EvnPrescrUslugaEditWindow',
                callback: function(){
                    thas.formStatus = 'edit';
                    thas.getLoadMask().hide();
                    callback(evnPrescrData, null);
                }
            },
            checked = [],//список услуг для заказа
            save_url,
            prescr_code = this.prescrTypeSysNick;

        formParams.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

        switch (prescr_code) {
            case 'EvnPrescrProc':
                save_url = '/?c=EvnPrescr&m=savePolkaEvnPrescrProc';
                evnPrescrData.UslugaComplex_id = formParams.UslugaComplex_id;
                checked.push(formParams.UslugaComplex_id);
                break;
            case 'EvnPrescrOperBlock':
                save_url = '/?c=EvnPrescr&m=saveEvnPrescrOperBlock';
                evnPrescrData.UslugaComplex_id = formParams.UslugaComplex_id;
				if (evndirection_combo.getValue() > 0) evnPrescrData.EvnDirection_id = evndirection_combo.getValue();
                checked.push(formParams.UslugaComplex_id);
                break;
            case 'EvnPrescrLabDiag':
                save_url = '/?c=EvnPrescr&m=saveEvnPrescrLabDiag';
                var nodes = thas.uslugaTree.getChecked();
                for (var i=0; i < nodes.length; i++)
                {
                    if (nodes[i].childNodes.length == 0) {
                        checked.push(nodes[i].attributes.id);
                    }
                }

                var rec = evndirection_combo.getRec();
                var order_uslugalist_str = '',
                    order_uslugalist_arr = [];
                if (rec && rec.get('EvnUslugaOrder_UslugaChecked')) {
                    order_uslugalist_str = Ext.util.JSON.decode(rec.get('EvnUslugaOrder_UslugaChecked')).toString();
                    if (typeof order_uslugalist_str == 'string' && order_uslugalist_str.length > 0) {
                        order_uslugalist_arr = order_uslugalist_str.split(',');
                    }
                }
                if ((order_uslugalist_arr.length > 0 || thas.uslugaTree.getRootNode().childNodes.length > 0) && checked.length == 0) {
                    //если есть состав услуги и ни одна услуга из состава не выбрана
                    sw.swMsg.alert(lang['oshibka'], lang['naznachenie_nevozmojno_ne_vyibran_sostav_uslugi']);
                    return false;
                }

                if (evndirection_combo.getValue() > 0 && evndirection_combo.EvnPrescr_id) {
                    //для обновления срочности в направлении
                    evnPrescrData.EvnDirection_id = evndirection_combo.getValue();
                }

                if (order_uslugalist_str != checked.toString() && rec && rec.get('EvnUslugaOrder_id')) {
                    //Заказ не изменился, но изменился его состав
                    //Посылаем измененный состав для обновления заказа
                    evnPrescrData.EvnUslugaOrder_id = rec.get('EvnUslugaOrder_id');
                    evnPrescrData.EvnUslugaOrder_UslugaChecked = checked.toString();
                }

                evnPrescrData.UslugaComplex_id = formParams.UslugaComplex_id;
                evnPrescrData.EvnPrescrLabDiag_uslugaList = checked.toString();
				
				var preg = false;
				var phase = false;

                thas.EvnPrescrLimitGrid.getGrid().getStore().each(function(record) {
					if( !Ext.isEmpty(record.get('LimitType_id')) ) {
						if (record.get('LimitType_SysNick') == 'HormonalPhaseType' && !Ext.isEmpty(record.get('EvnPrescrLimit_Values'))) {
							phase = true;
						}
						
						if (record.get('LimitType_SysNick') == 'PregnancyUnitType' && !Ext.isEmpty(record.get('EvnPrescrLimit_ValuesNum'))) {
							preg = true;
						}
					}
				});
				
				if (preg && phase) {
					sw.swMsg.alert(lang['oshibka'], lang['nelzya_ukazat_odnovremenno_i_beremennost_i_fazu_tsikla']);
					return false;
				}
		
				evnPrescrData.EvnPrescrLimitData = Ext.util.JSON.encode(getStoreRecords(thas.EvnPrescrLimitGrid.getGrid().getStore(), {
					exceptionFields: [
						'LimitType_SysNick',
						'LimitType_isCatalog',
						'Limit_UnitText',
						'EvnPrescrLimit_ValuesText',
						'LimitType_Name',
						'Limit_IsActiv',
						'Limit_Unit'
					]
				}));
                break;
            case 'EvnPrescrFuncDiag':

                save_url = '/?c=EvnPrescr&m=saveEvnPrescrFuncDiag';
                evnPrescrData.EvnPrescrFuncDiag_uslugaList = formParams.UslugaComplex_id;
                evnPrescrData.FSIDI_id = formParams.FSIDI_id;
                checked.push(formParams.UslugaComplex_id);

                var duplicatedFieldsPanel = wnd.findById(wnd.id + "_" + 'ToothNumFieldsPanel');

                var toothData = duplicatedFieldsPanel.getData();
                if (toothData) { evnPrescrData.StudyTargetPayloadData = Ext.util.JSON.encode({ toothData: toothData}) }

                break;
            case 'EvnPrescrConsUsluga':
                save_url = '/?c=EvnPrescr&m=saveEvnPrescrConsUsluga';
                evnPrescrData.UslugaComplex_id = formParams.UslugaComplex_id;
                evnPrescrData.EvnPrescrConsUsluga_uslugaList = formParams.UslugaComplex_id;
                checked.push(formParams.UslugaComplex_id);
                break;
        }
        if (!save_url) {
            callback(evnPrescrData, lang['naznachenie_imeet_nepravilnyiy_tip']);
            return false;
        }

        evnPrescrData[prescr_code +'_id'] = formParams.EvnPrescr_id||null;
        evnPrescrData[prescr_code +'_pid'] = formParams.EvnPrescr_pid;
        evnPrescrData[prescr_code +'_IsCito'] = (formParams.EvnPrescr_IsCito)?'on':'off';
        evnPrescrData[prescr_code +'_setDate'] = formParams.EvnPrescr_setDate;
        evnPrescrData[prescr_code +'_Descr'] = formParams.EvnPrescr_Descr || '';

        if (this.mode=='nosave') {
            evnPrescrData.Usluga_List = formParams.UslugaComplex_id;
            this.callback(evnPrescrData);
            this.hide();
            return true;
        }

        this.formStatus = 'save';
        thas.getLoadMask(lang['pojaluysta_podojdite'] +
            lang['idet_sohranenie_naznacheniya']).show();
        Ext.Ajax.request({
            url: save_url,
            params: evnPrescrData,
            callback: function(o, s, r) {
                thas.getLoadMask().hide();
                if(s) {
                    var response_obj = Ext.util.JSON.decode(r.responseText);
                    if ( response_obj.success && response_obj.success === true) {
                        formParams.EvnPrescr_id = response_obj[prescr_code +'_id'];
                        evnPrescrData[prescr_code +'_id'] = formParams.EvnPrescr_id;
                        evndirection_combo.EvnPrescr_id = formParams.EvnPrescr_id;
                        if (!evndirection_combo.getValue() && evndirection_combo.ident) {
                            // направление надо отменить
                            thas._cancelEvnDirection(evndirection_combo.ident, function(msg){
                                callback(evnPrescrData, msg);
                            });
                        } else if (evndirection_combo.getValue()==-1) {

                            var saveDirection = function(msg){

                                if (msg) {
                                    callback(evnPrescrData, lang['obnovlenie_napravleniya']+msg);
                                    return false;
                                }

                                var rec = evndirection_combo.getRec();

                                if (!rec) {
                                    callback(evnPrescrData, 'rec is undefined');
                                    return false;
                                }

                                var direction = rec.directionData;
								direction.EvnPrescr_id = formParams.EvnPrescr_id;

								if (rec.MedServiceRecord.get('lab_MedService_id')) {
									direction.MedService_id = rec.MedServiceRecord.get('lab_MedService_id');
								}

								params.order = {
									LpuSectionProfile_id: direction.LpuSectionProfile_id
									,UslugaComplex_id: formParams.UslugaComplex_id
									,checked: Ext.util.JSON.encode(checked)
									,Usluga_isCito: (formParams.EvnPrescr_IsCito)?2:1
									,UslugaComplex_Name: rec.MedServiceRecord.get('UslugaComplex_Name')
									,UslugaComplexMedService_id: rec.MedServiceRecord.get('UslugaComplexMedService_id')
									,MedService_id: direction.MedService_id
									,MedService_pzNick: rec.MedServiceRecord.get('pzm_MedService_Nick')
									,MedService_pzid: rec.MedServiceRecord.get('pzm_MedService_id')
								};

								if (direction['TimetableMedService_id'] > 0) {
									params.Timetable_id = direction['TimetableMedService_id'];
									params.order.TimetableMedService_id = direction['TimetableMedService_id'];
									//sw.Promed.Direction.recordPerson(params);
									direction['TimetableMedService_id'] = params.Timetable_id;
									direction['order'] = Ext.util.JSON.encode(params.order);
									sw.Promed.Direction.requestRecord({
										url: C_TTMS_APPLY,
										loadMask: params.loadMask,
										windowId: params.windowId,
										params: direction,
										//date: conf.date || null,
										Timetable_id: params.Timetable_id,
										fromEmk: false,
										mode: 'nosave',
										needDirection: false,
										Unscheduled: false,
										onHide: Ext.emptyFn,
										onSaveRecord: Ext.emptyFn,
										callback: params.callback
									});
								} else if (direction['TimetableResource_id'] > 0) {
									params.Timetable_id = direction['TimetableResource_id'];
									params.order.TimetableResource_id = direction['TimetableResource_id'];
									//sw.Promed.Direction.recordPerson(params);
									direction['TimetableResource_id'] = params.Timetable_id;
									direction['order'] = Ext.util.JSON.encode(params.order);
									sw.Promed.Direction.requestRecord({
										url: C_TTR_APPLY,
										loadMask: params.loadMask,
										windowId: params.windowId,
										params: direction,
										//date: conf.date || null,
										Timetable_id: params.Timetable_id,
										fromEmk: false,
										mode: 'nosave',
										needDirection: false,
										Unscheduled: false,
										onHide: Ext.emptyFn,
										onSaveRecord: Ext.emptyFn,
										callback: params.callback
									});
								} else {
									//sw.Promed.Direction.queuePerson(params);
									if (rec.MedServiceRecord.get('pzm_MedService_id')) {
										direction.MedService_pzid = rec.MedServiceRecord.get('pzm_MedService_id');
									}
									direction['order'] = Ext.util.JSON.encode(params.order);
									direction.MedService_did = direction.MedService_id;
									direction.Resource_did = direction.Resource_id;
									direction.LpuSectionProfile_did = direction.LpuSectionProfile_id;
									direction.EvnQueue_pid = direction.EvnDirection_pid;
									direction.MedStaffFact_id = null;
									direction.Prescr = "Prescr";
									direction.EvnDirection_IsCito = (formParams.EvnPrescr_IsCito == "2")?2:1;
									/*
									 direction.LpuSection_did = null;
									 direction.MedService_did = null;
									 direction.LpuUnit_did = null;
									 */
									sw.Promed.Direction.requestQueue({
										params: direction,
										loadMask: params.loadMask,
										windowId: params.windowId,
										callback: params.callback
									});
								}
                                return true;
                            };

                            if (evndirection_combo.ident) {
                                thas._cancelEvnDirection(evndirection_combo.ident, saveDirection);
                            } else {
                                saveDirection(null);
                            }

                        } else {
                            log('направление не изменилось, сохраняем только назначение');
                            // направление не изменилось, сохраняем только назначение
                            params.callback();
                            return true;
                        }
                    } else {
                        thas.formStatus = 'edit';
                        callback(evnPrescrData, response_obj.Error_Msg);
                    }
                } else {
                    thas.formStatus = 'edit';
                    callback(evnPrescrData, lang['oshibka_servera']);
                }
                return true;
            }
        });
        return true;
    },
    doSave: function(options)
    {
        if ( this.formStatus == 'save' ) {return false;}
        if ( typeof options != 'object' ) {options = {};}

        var thas = this;
        if ( !this.FormPanel.getForm().isValid() ) {
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
        this._save(options, function(evnPrescrData, msg){
            thas.formStatus = 'edit';
            thas.getLoadMask().hide();
            if ( !msg ) {
                var data = {};
                if (thas.winForm=='uslugaInput'){
                    data = evnPrescrData;
                    data.Usluga_List = evnPrescrData[thas.prescrTypeSysNick+'_uslugaList'];
                } else {
                    data[thas.prescrTypeSysNick+'Data'] = evnPrescrData;
                }
                thas.callback(data);
                thas.hide();
            } else {
                sw.swMsg.alert(lang['oshibka'], msg);
            }
        });
        return true;
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		form.formDisabled = d;
		form.FormPanel.items.each(function(f)
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	/** Функция относительно универсальной загрузки справочников выбор в которых осуществляется при вводе букв (цифр)
	 * Пример загрузки Usluga:
	 * loadSpr('Usluga_id', { where: "where UslugaType_id = 2 and Usluga_id = " + Usluga_id });
	 */
	loadSpr: function(field_name, params, callback)
	{
		var bf = this.FormPanel.getForm();
		var combo = bf.findField(field_name);
		var value = combo.getValue();
		
		combo.getStore().removeAll();
		combo.getStore().load(
		{
			callback: function() 
			{
				combo.getStore().each(function(record) 
				{
					if (record && record.data[field_name] == value)
					{
						combo.setValue(value);
						combo.fireEvent('select', combo, record, combo.getStore().indexOfId(value));
					}
				});
				if (callback)
				{
					callback();
				}
			},
			params: params 
		});
    },
    setTitleMy: function()
    {
        var action = lang['prosmotr'];
        if ( this.action == 'edit' ) {
            action = lang['redaktirovanie'];
        }
        if ( this.action == 'copy' ) {
            action = lang['dobavlenie'];
        }
        var str = '';
        switch(this.prescrTypeSysNick) {
            case 'EvnPrescrProc': str = lang['manipulyatsiy_i_protsedur']; break;
            case 'EvnPrescrOperBlock': str = lang['operativnoy_uslugi']; break;
            case 'EvnPrescrLabDiag': str = lang['laboratornoy_diagnostiki']; break;
            case 'EvnPrescrFuncDiag': str = lang['instrumentalnoy_diagnostiki']; break;
            case 'EvnPrescrConsUsluga': str = lang['konsultatsii_sm']; break;
        }
        this.setTitle(lang['naznachenie']+' '+str+': '+ action);
	},
	reloadEvnPrescrLimitGrid: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();

		var rec = base_form.findField('EvnDirection_id').getRec();
		if (!rec) {
			return false;
		}
        var lab_MedService_id = rec.get('MedService_id');
        if (rec.MedServiceRecord && rec.MedServiceRecord.get('lab_MedService_id')) {
            lab_MedService_id = rec.MedServiceRecord.get('lab_MedService_id');
        }
		if (win.prescrTypeSysNick == 'EvnPrescrLabDiag') {
			win.EvnPrescrLimitGrid.loadData({
				params: {
					EvnPrescr_id: base_form.findField('EvnPrescr_id').getValue(),
					MedService_id: lab_MedService_id,
					UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
					Person_id: base_form.findField('EvnDirection_id').Person_id
				},
				globalFilters: {
					EvnPrescr_id: base_form.findField('EvnPrescr_id').getValue(),
					MedService_id: lab_MedService_id,
					UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
					Person_id: base_form.findField('EvnDirection_id').Person_id
				}
			});
		}
    },
    loadLabCmp: function()
    {
        var thas = this;
        if ('EvnPrescrLabDiag' == thas.prescrTypeSysNick) {
            thas.uslugaTree.getLoader().load(
                thas.uslugaTree.getRootNode(),
                function () {
                    // thas.uslugaTree.getRootNode().expand(true);
                }
            );
            thas.reloadEvnPrescrLimitGrid();
        }
    },
    clearLabCmp: function()
    {
        var root_node = this.uslugaTree.getRootNode();
        while (root_node.childNodes.length > 0) {
            root_node.removeChild( root_node.childNodes[0] );
        }
        this.EvnPrescrLimitGrid.getGrid().getStore().removeAll();
	},
	unlock: function(){
		var base_form = this.FormPanel.getForm();
		var evndirection_combo = base_form.findField('EvnDirection_id');
		if (evndirection_combo.getValue()==-1) {
			var rec = evndirection_combo.getRec();
			if (!rec || !rec.get('timetable_id')) {
				return false;
			}
			sw.Promed.Direction.unlockTime(this, 'TimetableMedService', rec.get('timetable_id'), function(){

			});
		}
		return true;
	},
    loadForm: function() {

        var wnd = this,
            form = wnd.FormPanel.getForm(),
            evndirection_combo = form.findField('EvnDirection_id'),
            uslugacomplex_combo = form.findField('UslugaComplex_id');;

        wnd.setFieldsDisabled(wnd.action == 'view');

        if (!form.findField('EvnPrescr_setDate').getValue()) {
            form.findField('EvnPrescr_setDate').setValue(evndirection_combo.EvnPrescr_setDate);
        }

        evndirection_combo.Person_id = form.findField('Person_id').getValue();
        evndirection_combo.PersonEvn_id = form.findField('PersonEvn_id').getValue();
        evndirection_combo.Server_id = form.findField('Server_id').getValue();
        evndirection_combo.EvnPrescr_id = form.findField('EvnPrescr_id').getValue();
        evndirection_combo.EvnPrescr_IsCito = form.findField('EvnPrescr_IsCito').getValue()?2:1;
        evndirection_combo.EvnPrescr_pid = form.findField('EvnPrescr_pid').getValue();

        wnd.loadSpr('UslugaComplex_id', {
            UslugaComplex_id: uslugacomplex_combo.getValue(),
            MedService_id: form.findField('MedService_id').getValue()
        }, function() {

            var rec = uslugacomplex_combo.getStore().getById(uslugacomplex_combo.getValue());

            if (rec && rec.get('UslugaComplex_id')) {
                evndirection_combo.UslugaComplex_id = rec.get('UslugaComplex_id');
            } else {
                // @todo нельзя будет создать направление, вывести предупреждение?
                evndirection_combo.UslugaComplex_id = null;
            }

            log({
                debug: 'loadSpr UslugaComplex_id',
                rec: rec,
                UslugaComplex_2011id: evndirection_combo.UslugaComplex_2011id
            })
        });

        wnd.loadSpr('EvnDirection_id', {
            EvnDirection_id: evndirection_combo.getValue(),
            EvnPrescr_id: form.findField('EvnPrescr_id').getValue()
        }, function() {
            var value = null;
            if (evndirection_combo.getStore().getCount() > 0) {
                value = evndirection_combo.getValue();
                //var record = evndirection_combo.getStore().getAt(0);
                //evndirection_combo.fireEvent('select', evndirection_combo, record, 0);
            }
            evndirection_combo.setValue(value);
        });

        if ( wnd.action == 'edit' ) {
            uslugacomplex_combo.setUslugaComplexDate(form.findField('EvnPrescr_setDate').getRawValue());
            form.findField('EvnPrescr_setDate').focus(true, 250);
        }

        uslugacomplex_combo.disable();
    },
	show: function() 
	{
		sw.Promed.swEvnPrescrUslugaEditWindow.superclass.show.apply(this, arguments);
        var thas = this;
        var wnd = this;
		this.center();
		this.formDisabled = false;

		var base_form = this.FormPanel.getForm();
		base_form.reset();
        base_form.clearInvalid();
        var uslugacomplex_combo = base_form.findField('UslugaComplex_id');
        uslugacomplex_combo.getStore().removeAll();
        uslugacomplex_combo.clearBaseParams();
        uslugacomplex_combo.showCodeField = getPrescriptionOptions().enable_show_service_code;
        var evndirection_combo = base_form.findField('EvnDirection_id');
        evndirection_combo.getStore().removeAll();
        this.clearLabCmp();

		this.parentEvnClass_SysNick = '';
		this.action = 'view';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.mode = 'save';
		this.winForm = null;
		
		if ( !arguments[0] || !arguments[0].formParams || !arguments[0].PrescriptionType_Code) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { thas.hide(); } );
			return false;
		}
		
		base_form.setValues(arguments[0].formParams);
        this.prescrTypeSysNick = sw.Promed.EvnPrescr.getPrescrTypeSysNick(arguments[0].PrescriptionType_Code);

        evndirection_combo.ident = null;
        evndirection_combo.ownerWin = this;
        evndirection_combo.PrescriptionType_id = arguments[0].PrescriptionType_id || null;
        evndirection_combo.PrescriptionType_Code = arguments[0].PrescriptionType_Code;
        evndirection_combo.userMedStaffFact = arguments[0].userMedStaffFact || null;
        evndirection_combo.Person_id = arguments[0].Person_id || null;
        evndirection_combo.Person_Firname = arguments[0].Person_Firname || null;
        evndirection_combo.Person_Surname = arguments[0].Person_Surname || null;
        evndirection_combo.Person_Secname = arguments[0].Person_Secname || null;
        evndirection_combo.Person_Birthday = arguments[0].Person_Birthday || null;
        evndirection_combo.Diag_id = arguments[0].Diag_id || null;
        evndirection_combo.EvnPrescr_setDate = arguments[0].formParams.EvnPrescr_setDate;

        if (!evndirection_combo.hasOnSelect) {
            evndirection_combo.on('select', function(combo, rec){
                if (rec.MedServiceRecord && rec.MedServiceRecord.get('UslugaComplex_id')>0 && uslugacomplex_combo.getValue()!=rec.MedServiceRecord.get('UslugaComplex_id')) {
                    uslugacomplex_combo.getStore().removeAll();
                    //uslugacomplex_combo.clearBaseParams();
                    uslugacomplex_combo.setValue(rec.MedServiceRecord.get('UslugaComplex_id'));
                    thas.loadSpr('UslugaComplex_id', {
                        UslugaComplex_id: rec.MedServiceRecord.get('UslugaComplex_id')
                    }, function() {
                        uslugacomplex_combo.setValue(rec.MedServiceRecord.get('UslugaComplex_id'));
                        if (getPrescriptionOptions().enable_show_service_code)
                            uslugacomplex_combo.setRawValue(rec.MedServiceRecord.get('UslugaComplex_Code')+' '+rec.MedServiceRecord.get('UslugaComplex_Name'));
                        else
                            uslugacomplex_combo.setRawValue(rec.MedServiceRecord.get('UslugaComplex_Name'));
                    });
                }
                thas.loadLabCmp();
            });
            evndirection_combo.hasOnSelect = true;
        }

		base_form.findField('CovidContingentType_id').hideContainer();
		base_form.findField('CovidContingentType_id').disable();
		base_form.findField('HIVContingentTypeFRMIS_id').hideContainer();
		base_form.findField('HIVContingentTypeFRMIS_id').disable();
		base_form.findField('HormonalPhaseType_id').hideContainer();
		Ext.getCmp(wnd.id + 'RaceType_FS').setVisible(getRegionNick() == 'ufa');
		Ext.getCmp(wnd.id + 'PersonWeight_FS').setVisible(getRegionNick() == 'ufa');
		Ext.getCmp(wnd.id + 'PersonHeight_FS').setVisible(getRegionNick() == 'ufa');


		if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
			this.action = arguments[0].action;
		}
		
		if ( arguments[0].parentEvnClass_SysNick && typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
			this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
		}
        evndirection_combo.isOnlyPolka = (13==evndirection_combo.PrescriptionType_id && this.parentEvnClass_SysNick.inlist(['EvnVizitPL','EvnVizitPLStom']))?1:0;
		
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		if ( arguments[0].winForm && typeof arguments[0].winForm == 'string' ) {
            this.winForm = arguments[0].winForm;
        }
		if ( arguments[0].formParams.mode && typeof arguments[0].formParams.mode == 'string' ) {
            this.mode = arguments[0].formParams.mode;
        }

        if (!this.action.inlist(['view','edit','copy'])) {
            this.hide();
            return false;
        }
        this.setTitleMy();

        this.uslugaTree.setVisible('EvnPrescrLabDiag' == this.prescrTypeSysNick);
        this.EvnPrescrLimitGrid.setVisible('EvnPrescrLabDiag' == this.prescrTypeSysNick);

        var disable_edit_ed = false;
        if (thas.winForm=='uslugaInput' || thas.mode=='nosave'){ disable_edit_ed = true; }

        evndirection_combo.setDisabled(disable_edit_ed);
        evndirection_combo.setContainerVisible(!disable_edit_ed);

        var toothsPanel = wnd.findById(wnd.id + "_" + 'ToothNumFieldsPanel');
        toothsPanel.clearPanel();

        // скрываем зубы
        if (!this.parentEvnClass_SysNick || (this.parentEvnClass_SysNick && this.parentEvnClass_SysNick != "EvnVizitPLStom")
            || !this.prescrTypeSysNick || (this.prescrTypeSysNick && this.prescrTypeSysNick != "EvnPrescrFuncDiag")
        ) {
            toothsPanel.hide();
        }
        
        //Поле инст.диагностики только для EvnPrescrFuncDiag
        if (this.prescrTypeSysNick !== "EvnPrescrFuncDiag") {
			base_form.findField('FSIDI_id').hideContainer();
			base_form.findField('FSIDI_id').disable();
		} else {
			base_form.findField('FSIDI_id').showContainer();
			base_form.findField('FSIDI_id').enable();
		}

        if (this.mode == 'nosave') { wnd.loadForm(); }
        else {

            var params = { parentEvnClass_SysNick: this.parentEvnClass_SysNick };
            params[this.prescrTypeSysNick + '_id'] = base_form.findField('EvnPrescr_id').getValue();

            this.getLoadMask(LOAD_WAIT).show();
            Ext.Ajax.request({
                url: '/?c=EvnPrescr&m=load'+this.prescrTypeSysNick+'EditForm',
                params: params,
                success: function(response) {

                    thas.getLoadMask().hide();
                    var response_obj = Ext.util.JSON.decode(response.responseText);

                    if (!response_obj || !response_obj[0] || !response_obj[0]['PersonEvn_id']) {

                        sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function(){});

                    } else {

                        var formParams = {
                            accessType: response_obj[0]['accessType'] || 'view'
                            ,UslugaComplex_id: response_obj[0]['UslugaComplex_id'] || null
                            ,EvnDirection_id: response_obj[0]['EvnDirection_id'] || null
                            ,MedService_id: response_obj[0]['MedService_id'] || null
                            ,Person_id: response_obj[0]['Person_id']
                            ,Server_id: response_obj[0]['Server_id']
                            ,PersonEvn_id: response_obj[0]['PersonEvn_id']
                            ,EvnCourse_id: response_obj[0]['EvnCourse_id'] || null
                            ,StudyTarget_id: response_obj[0]['StudyTarget_id'] || null
                            ,FSIDI_id: response_obj[0]['FSIDI_id'] || null
                        };

                        formParams.EvnPrescr_id = response_obj[0][thas.prescrTypeSysNick+'_id'];
                        formParams.EvnPrescr_pid = response_obj[0][thas.prescrTypeSysNick+'_pid'];
                        formParams.EvnPrescr_setDate = response_obj[0][thas.prescrTypeSysNick+'_setDate'];
                        formParams.EvnPrescr_IsCito = response_obj[0][thas.prescrTypeSysNick+'_IsCito'];
                        formParams.EvnPrescr_Descr = response_obj[0][thas.prescrTypeSysNick+'_Descr'];
                        formParams.EvnPrescr_uslugaList = response_obj[0][thas.prescrTypeSysNick+'_uslugaList'];

                        if (thas.action == 'copy') {
                            //нужно сначала создать новое назначение?
                            formParams.accessType = 'edit';
                            formParams.EvnPrescr_id = null;
                            formParams.EvnDirection_id = null;
                            formParams.EvnPrescr_setDate = base_form.findField('EvnPrescr_setDate').getValue()
                        }

                        base_form.setValues(formParams);
                        if ( base_form.findField('accessType').getValue() == 'view' ) { thas.action = 'view';}

                        // если есть зубы
                        if (response_obj[0]['ToothNums'] && toothsPanel) {

                            var toothList = response_obj[0]['ToothNums'].split(', ');
                            if (toothList) {

                                toothList.forEach(function(tth, k){

                                    var toothNum = parseInt(tth.trim());

                                    if (k > 0) { toothsPanel.addField()}
                                    toothsPanel.setValueByIndex(k, toothNum);
                                })
                            }
                        }
                        
						if (thas.prescrTypeSysNick === "EvnPrescrFuncDiag" && response_obj[0]['UslugaComplex_id']) {
							base_form.findField('FSIDI_id').checkVisibilityAndGost(response_obj[0]['UslugaComplex_id']);
						}

                        wnd.loadForm();
                    }
                },
                failure: function() {
                    thas.getLoadMask().hide();
                    sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function(){});
                },
            });
        }

        wnd.doLayout();
        wnd.syncSize();
        return true;
	},
	blockStartingEditing: false,
	getComboEditor: function(object) {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: true,
			comboSubject: object,
			codeField: object + '_Code',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			}
		});
	},
	getNumberFieldEditor: function(LimitType_id) {
		var win = this;
		var options = {
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			minValue: 0
		};
		if (LimitType_id == 7) {
			options.maxValue = 24;
		}
		return new Ext.form.NumberField(options);
	},
	startEditData: function() {
		if (this.blockStartingEditing) {
			return false;
		}
		var win = this;
		var grid = this.EvnPrescrLimitGrid.getGrid();
		
		// если ещё редактируется
		var editor = grid.getColumnModel().getCellEditor(4);
		if (editor && !editor.hidden) {
			return false;
		}
		
		this.blockStartingEditing = true;

		var cell = grid.getSelectionModel().getSelectedCell();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('LimitType_id') ) {
			return false;
		}
		
		/*
		grid.getSelectionModel().select(cell[0], 4);
		grid.getView().focusCell(cell[0], 4);
		*/
		
		var editor = null;
		
		if (record.get('LimitType_isCatalog') == 2 && cell[1] == 5 && !Ext.isEmpty(record.get('LimitType_SysNick'))) {
			editor = new Ext.grid.GridEditor(win.getComboEditor(record.get('LimitType_SysNick')));
		} else if (record.get('LimitType_isCatalog') == 1 && cell[1] == 6) {
			editor = new Ext.grid.GridEditor(win.getNumberFieldEditor(record.get('LimitType_id')));
		}
		
		if (!Ext.isEmpty(editor)) {
			grid.getColumnModel().setEditor(cell[1], editor);
			grid.getColumnModel().setEditable(cell[1], true);
			grid.startEditing(cell[0], cell[1]);
		} else {
			grid.getColumnModel().setEditable(cell[1], false);
		}
		
		this.blockStartingEditing = false;
	},
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		
		this.uslugaTree = new Ext.tree.TreePanel({
			title: lang['sostav_kompleksnoy_uslugi'],
			height: 180,
			autoWidth: true,
			autoScroll:true,
			animate:true,
			enableDD:true,
			containerScroll: true,
			rootVisible: false,
			autoLoad:false,
			frame: true,
			changeDisabled: false,
			root: {
				nodeType: 'async'
			},
			cls: 'x-tree-noicon',
			loader: new Ext.tree.TreeLoader(
			{
				dataUrl:'/?c=MedService&m=loadCompositionTree',
				uiProviders: {'default': Ext.tree.TreeNodeUI, tristate: Ext.tree.TreeNodeTriStateUI},
				//clearOnLoad: true,
				listeners:
				{
					load: function(tr, node)
					{
                        var base_form = form.FormPanel.getForm();
                        var rec = base_form.findField('EvnDirection_id').getRec();
						var uslugalist_str = base_form.findField('EvnPrescr_uslugaList').getValue();
                        if (rec && rec.get('EvnUslugaOrder_UslugaChecked')) {
                            uslugalist_str = Ext.util.JSON.decode(rec.get('EvnUslugaOrder_UslugaChecked')).toString();
                        }
						var uslugalist_arr = [];
                        if (typeof uslugalist_str == 'string' && uslugalist_str.length > 0) {
                            uslugalist_arr = uslugalist_str.split(',');
                        }
						var nodes = node.childNodes || [];	
						for (var i=0; i < nodes.length; i++)
						{
							if (nodes[i].childNodes.length == 0
                                && (
                                    uslugalist_arr.length == 0
                                    //в nodes[i].attributes.id значение UslugaComplex_id
                                    || nodes[i].attributes.id.toString().inlist(uslugalist_arr)
                                )
                            ) {
                                //отмечаем выбранные услуги
                                form.uslugaTree.fireEvent('checkchange', nodes[i], true);
							}
							if (form.formDisabled) {
								nodes[i].disable();
							} else {
								nodes[i].enable();
							}
						}

						if ( getRegionNick() == 'ufa' ) {
							form.loadEvnDirectionPersonDetails()
							.then(personDetails => {
								for (var i in personDetails)
									if (form.params.hasOwnProperty(i)) form.params[i] = personDetails[i];
								form.setValueToHidden();
							})
							.then(() => {
								form.params.UslugaComplex_List = [];
								return form.loadUslugaComplexList()
									.then((result) => {
										for (var i = 0; i < result.length; i++) {
											form.params.UslugaComplex_List.push(result[i].id);
										}
									});
								return true;
							})
							.then(() => {
								return form.loadUslugaComplexDetails().then( attributeList => {
									form.UslugaComplex_AttributeList = attributeList;
								})
							})
							.then(() => {
								form._processFieldsVisible();
							})
							.catch((err) => { Ext.Msg.alert(lang['oshibka'], err.message); })
							.finally(() => { form.params.finished = true; });
						}
					},
					beforeload: function (tl, node)
					{
						//form.uslugaTree.getLoadTreeMask('Загрузка дерева услуг... ').show();
                        var base_form = form.FormPanel.getForm();
						var uslugacomplex_combo = base_form.findField('UslugaComplex_id');
                        var rec = base_form.findField('EvnDirection_id').getRec();
                        var param_usluga = 'UslugaComplex_id';
                        if (rec && rec.get('UslugaComplexMedService_id')>0) {
                            param_usluga = 'UslugaComplexMedService_id';
                        }
                        tl.baseParams = {};
                        tl.baseParams.check = 1;
						
						if (node.getDepth()==0) {
                            if (rec && rec.get('UslugaComplexMedService_id')>0)
                                tl.baseParams[param_usluga] = rec.get('UslugaComplexMedService_id');
                            else if (uslugacomplex_combo.getValue()>0)
                                tl.baseParams[param_usluga] = uslugacomplex_combo.getValue();
							else
								return false;
						} else {
							tl.baseParams[node.attributes.object_id] = node.attributes.object_value;
						}
                        return true;
					}
				}
			}),
			changing: false,
			listeners: 
			{
				checkchange: function (node, checked)
				{
					if (!form.uslugaTree.changing)
					{
                        form.uslugaTree.changing = true;
						//node.expand(true, false);
						if (checked)
							node.cascade( function(node){node.getUI().toggleCheck(true)} );
						else
							node.cascade( function(node){node.getUI().toggleCheck(false)} );
						node.bubble( function(node){if (node.parentNode) node.getUI().updateCheck()} );
                        form.uslugaTree.changing = false;
					}
					if ( getRegionNick() == 'ufa' && form.params.finished) {
						form._processFieldsVisible();
					}
				}
			}
		});
		
		this.EvnPrescrLimitGrid = new sw.Promed.ViewFrame({
			actions:[
				{name:'action_add', hidden:true, disabled: true},
				{name:'action_edit', hidden:true, disabled: true},
				{name:'action_view', hidden:true, disabled: true},
				{name:'action_delete', hidden:true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print', hidden:true}
			],
			saveRecord: function() {
				// на сервер не отправляем, сохранится вместе со всей формой
			},
			id: this.id + '_Grid',
			selectionModel: 'cell',
			saveAtOnce: false, 
			saveAllParams: false, 
			onAfterEdit: function(o) {
				if (o && o.field) {
					if (o.field == 'EvnPrescrLimit_ValuesText') {
						o.record.set('EvnPrescrLimit_Values', o.value);
						o.record.set('EvnPrescrLimit_ValuesText', o.rawvalue);
					}
				}
			},
			onCellSelect: function(sm,rowIdx,colIdx) {
				form.startEditData();
			},
			autoExpandColumn:'autoexpand',
			autoExpandMin:150,
			autoLoadData:false,
			border:true,
			dataUrl:'/?c=EvnPrescrLimit&m=loadList',
			height:180,
			region:'center',
			scheme: 'lis',
			object: 'EvnPrescrLimit',
			uniqueId: true,
			paging: false,
			totalProperty: 'totalCount',
			editformclassname:'swEvnPrescrLimitEditWindow',
			style:'margin-bottom: 10px',
			stringfields:[
				{name:'LimitType_id', type:'int', header:'ID', key:true},
				{name:'EvnPrescrLimit_id', type:'int', hidden: true},
				{name:'LimitType_Name', type:'string', header: lang['naimenovanie'], width: 120, id: 'autoexpand'},
				{name:'LimitType_isCatalog', type:'int', hidden: true},
				{name:'LimitType_SysNick', type:'string', hidden: true},
				{name:'EvnPrescrLimit_ValuesText', type:'string', header: lang['spravochnoe_znachenie'], width: 120},
				{name:'EvnPrescrLimit_ValuesNum', type:'string', header: lang['chislovoe_znachenie'], width: 120},
				{name:'Limit_UnitText', type:'string', header: lang['edinitsa_izmereniya'], width: 80},
				{name:'EvnPrescrLimit_Values', type:'int', hidden: true},
				{name:'Limit_IsActiv', type:'checkcolumnedit', hidden: true},
				{name:'Limit_Unit', type:'int', hidden: true}
			],
			title:lang['parametryi_referensnyih_znacheniy'],
			toolbar:false
		});
		
		this.FormPanel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'EvnPrescrUslugaEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			region: 'center',
			items: 
			[{
				name: 'accessType', // Режим доступа
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescr_id',
				value: null,
				xtype: 'hidden'
			}, {
				name: 'MedService_id',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'EvnPrescr_pid',
				value: null,
				xtype: 'hidden'
			},
            {
                name: 'EvnCourse_id',
                value: null,
                xtype: 'hidden'
            },
            {
				name: 'Person_id',
				value: null,
				xtype: 'hidden'
			},
            {
				name: 'PersonEvn_id',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'Server_id',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'EvnPrescr_uslugaList',
				value: null,
				xtype: 'hidden'
			},
			{
				allowBlank: false,
				fieldLabel: lang['planovaya_data'],
				format: 'd.m.Y',
				name: 'EvnPrescr_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				onChange: function(field) {
					var date_str = field.getRawValue() || null;
                    form.FormPanel.getForm().findField('UslugaComplex_id').setUslugaComplexDate(date_str);
				},
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				value: null,
				fieldLabel: lang['usluga'],
				hiddenName: 'UslugaComplex_id',
				anchor:'99%',
				PrescriptionType_Code: 11,
				xtype: 'swuslugacomplexevnprescrcombo',
				listeners:
				{
					select: function(combo/*,record,index*/)
					{
                        if (!combo.disabled && 'EvnPrescrLabDiag' == form.prescrTypeSysNick) {
                            form.uslugaTree.getLoader().load(
                                form.uslugaTree.getRootNode(),
                                function () {
                                    // form.uslugaTree.getRootNode().expand(true);
                                }
                            );
                        }
					}
				}
            },
			{
				xtype: 'swfsidicombo',
				fieldLabel: langs('Инструментальная диагностика'),
				hiddenName: 'FSIDI_id',
				editable: true,
				anchor:'99%'
			},
			{
                allowBlank: true,
                value: null,
                anchor:'99%',
                hiddenName: 'EvnDirection_id',
                emptyText: '',
                xtype: 'swevnprescrevndirectioncombo',
                onClearValue: function() {
                    form.clearLabCmp();
                }
			},
			this.uslugaTree,
			this.EvnPrescrLimitGrid, {
				fieldLabel: 'Цель исследования',
				xtype: 'swcommonsprcombo',
				allowBlank: false,
				hiddenName: 'StudyTarget_id',
				comboSubject: 'StudyTarget',
				tabIndex: 14,
				anchor: '99%'
			},
			{
				xtype: 'swcommonsprcombo',
				fieldLabel: langs('Код контингента ВИЧ'),
				comboSubject: 'HIVContingentTypeFRMIS',
				hiddenName: 'HIVContingentTypeFRMIS_id',
				allowBlank: true,
				editable: true,
				ctxSerach: true,
				loadParams: { params: { where: ' where HIVContingentTypeFRMIS_Code != 100' } },
				anchor: '99%'
			}, {
				xtype: 'swcommonsprcombo',
				fieldLabel: langs('Код контингента COVID'),
				comboSubject: 'CovidContingentType',
				hiddenName: 'CovidContingentType_id',
				allowBlank: true,
				editable: true,
				ctxSerach: true,
				anchor: '99%'
			},
			{
				xtype: 'swcommonsprcombo',
				hiddenName: 'HormonalPhaseType_id',
				comboSubject: 'HormonalPhaseType',
				fieldLabel: langs('Фаза цикла'),
				anchor: '99%'
			},
			{
				id: form.id + 'RaceType_FS',
				xtype: 'fieldset',
				layout: 'column',
				border: false,
				autoHeight: true,
				labelWidth: 130,
				style: 'margin: 2px 30px; padding: 0;',
				items: [
					{
						xtype: 'panel',
						html: 'Раса: ',
						layuot: 'anchor',
						width: 120,
						style: 'margin-right: 5px;',
						bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
					},
					{
						xtype: 'swcommonsprcombo',
						fieldLabel: langs('Раса'),
						comboSubject: 'RaceType',
						hiddenName: 'RaceType_id',
						anchor: '95%',
						disabled: true
					},
					{
						xtype: 'button',
						id: form.id + 'RaceTypeAddBtn',
						style: 'margin-left: 5px;',
						text: 'Добавить',
						handler: function () {
							getWnd('swPersonRaceEditWindow').show({
								formParams: {
									PersonRace_id: 0,
									Person_id: form.params.Person_id
								},
								action: 'add',
								onHide: Ext.emptyFn,
								callback: function(data) {
									if (!data || !data.personRaceData)
										return false;
									form.formPanel.getForm()
										.findField('RaceType_id')
										.setValue(data.personRaceData.RaceType_id);
									Ext.getCmp(form.id + 'RaceTypeAddBtn').setDisabled(true);
								}
							});
						}
					}
				]
			},
			{
				id: form.id + 'PersonHeight_FS',
				xtype: 'fieldset',
				layout: 'column',
				border: false,
				autoHeight: true,
				labelWidth: 130,
				style: 'margin: 2px 30px; padding: 0;',
				items: [
					{
						xtype: 'panel',
						html: 'Рост (см): ',
						name: 'PersonHeight_Height_label',
						layuot: 'anchor',
						width: 120,
						style: 'margin-right: 5px;',
						bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma,arial,helvetica,sans-serif;'
					},
					{
						xtype: 'textfield',
						name: 'PersonHeight_Height',
						disabled: true
					},
					{
						xtype: 'panel',
						html: ' на дату: ',
						layuot: 'anchor',
						bodyStyle: 'padding: 1px 5px 0 5px; border: 0px; font: normal 12px tahoma,arial,helvetica,sans-serif;'
					},
					{
						fieldLabel : lang['okonchanie'],
						name: 'PersonHeight_setDT',
						xtype: 'swdatefield',
						disabled: true,
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
					},
					{
						xtype: 'button',
						id: form.id + 'PersonHeightAddBtn',
						text: 'Добавить',
						style: 'margin-left: 5px;',
						handler: function () {
							getWnd('swPersonHeightEditWindow').show({
								measureTypeExceptions:[1,2],
								formParams: {
									PersonHeight_id: 0,
									Person_id: form.params.Person_id
								},
								action: 'add',
								onHide: Ext.emptyFn,
								callback: function(data) {
									if (!data || !data.personHeightData)
										return false;
									form.formPanel.getForm()
										.findField('PersonHeight_Height')
										.setValue(data.personHeightData.PersonHeight_Height);
									var date = Ext.util.Format.date(new Date(data.personHeightData.PersonHeight_setDate), 'd.m.Y');
									form.formPanel.getForm()
										.findField('PersonHeight_setDT')
										.setValue(date);
								}
							});
						}
					}
				]
			},
			{
				id: form.id + 'PersonWeight_FS',
				xtype: 'fieldset',
				layout: 'column',
				border: false,
				autoHeight: true,
				labelWidth: 130,
				style: 'margin: 2px 30px; padding: 0;',
				items: [
					{
						xtype: 'panel',
						html: 'Масса: ',
						layuot: 'anchor',
						width: 120,
						style: 'margin-right: 5px;',
						bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
					},
					{
						xtype: 'textfield',
						name: 'PersonWeight_WeightText',
						disabled: true
					},
					{
						xtype: 'panel',
						html: ' на дату: ',
						layuot: 'anchor',
						bodyStyle: 'padding: 1px 5px 0 5px; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
					},
					{
						fieldLabel : lang['okonchanie'],
						name: 'PersonWeight_setDT',
						xtype: 'swdatefield',
						disabled: true,
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
					},
					{
						xtype: 'button',
						id: form.id + 'PersonWeightAddBtn',
						text: 'Добавить',
						style: 'margin-left: 5px;',
						handler: function () {
							getWnd('swPersonWeightEditWindow').show({
								measureTypeExceptions:[1,2],
								Okei_InterNationSymbol:"kg",
								formParams: {
									PersonWeight_id: 0,
									Person_id: form.params.Person_id
								},
								action: 'add',
								onHide: Ext.emptyFn,
								callback: function(data) {
									if (!data || !data.personWeightData)
										return false;
									form.formPanel.getForm()
										.findField('PersonWeight_WeightText')
										.setValue(data.personWeightData.PersonWeight_text);
									var date = Ext.util.Format.date(new Date(data.personWeightData.PersonWeight_setDate), 'd.m.Y');
									form.formPanel.getForm()
										.findField('PersonWeight_setDT')
										.setValue(date);
								}
							});
						}
					}
				]
			},
            {
                ownerWindow: form,
                xtype: 'swduplicatedfieldpanel',
                fieldLbl: 'Номер зуба',
                fieldName: 'ToothNumEvnUsluga_ToothNum',
                id: form.id + '_' + 'ToothNumFieldsPanel'
            },
            {
				boxLabel: 'Cito',
				checked: false,
				fieldLabel: '',
				labelSeparator: '',
				name: 'EvnPrescr_IsCito',
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnPrescr_Descr',
				anchor: '99%',
				xtype: 'textarea'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view')
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{name: 'accessType' },
				{name: 'EvnPrescr_id'},
				{name: 'MedService_id'},
                {name: 'EvnPrescr_pid'},
                {name: 'EvnCourse_id'},
				{name: 'Person_id'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'EvnPrescr_setDate'},
				{name: 'EvnPrescr_IsCito'},
				{name: 'EvnPrescr_Descr'},
				{name: 'EvnPrescr_uslugaList'},
                {name: 'UslugaComplex_id'},
                {name: 'FSIDI_id'},
                {name: 'EvnDirection_id'},
                {name: 'StudyTarget_id'}
			])
		});
		this.formPanel = this.FormPanel;
		Ext.apply(this, 
		{
			buttons: [{
				handler: function() {
                    form.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
                hidden: true,
				handler: function() {
                    form.doSave({signature: true});
				},
				iconCls: 'signature16',
				text: BTN_FRMSIGN
			}, {
				text: '-'
			},
			//HelpButton(this, -1),
			{
				handler: function() {
					form.hide();
				},
				onTabAction: function () {
                    form.FormPanel.getForm().findField('EvnPrescr_setDate').focus(true, 250);
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnPrescrUslugaEditWindow.superclass.initComponent.apply(this, arguments);
	},
	_processFieldsVisible: function () {
		var baseForm = this.formPanel.getForm();
		var hiddenCount = 0;

		var isUfa = getGlobalOptions().region.nick === 'ufa';
		var isLab = this.checkUslugaAttribute(8, this.UslugaComplex_AttributeList);
		var isContingentReq = isUfa && isLab && this.checkUslugaAttribute(224, this.UslugaComplex_AttributeList);
		var isContingentCovid = isUfa && isLab && this.checkUslugaAttribute(227, this.UslugaComplex_AttributeList);
		var RaceType_FS = Ext.getCmp(this.id + 'RaceType_FS');
		if (!isUfa || !isLab) {
			RaceType_FS.hide();
			hiddenCount++;
		} else {
			RaceType_FS.show();
			Ext.getCmp(this.id + 'RaceTypeAddBtn').setDisabled(!Ext.isEmpty(baseForm.findField('RaceType_id').getValue()));
		}

		if (this.action != 'add') {
			isContingentReq &= this.params.pmUser_insID == getGlobalOptions().pmuser_id || isUserGroup('hivresearch');
		}

		var HIVContingentTypeFRMIS_id = baseForm.findField('HIVContingentTypeFRMIS_id');
		HIVContingentTypeFRMIS_id.setDisabled(!isContingentReq);
		HIVContingentTypeFRMIS_id.setContainerVisible(isContingentReq);
		HIVContingentTypeFRMIS_id.setAllowBlank(!isContingentReq);
		!isContingentReq || HIVContingentTypeFRMIS_id.setValue(this.params.HIVContingentTypeFRMIS_id);

		var CovidContingentTypeField = baseForm.findField('CovidContingentType_id');
		CovidContingentTypeField.setDisabled(!isContingentCovid);
		CovidContingentTypeField.setContainerVisible(isContingentCovid);
		CovidContingentTypeField.setAllowBlank(!isContingentCovid);
		!isContingentCovid || CovidContingentTypeField.setValue(this.params.CovidContingentType_id);

		var HormonalPhaseType_id = baseForm.findField('HormonalPhaseType_id');
		if (!isUfa || !isLab || !(this.params.Sex_id == 2)) {
			HormonalPhaseType_id.hideContainer();
			hiddenCount++;
		} else {
			HormonalPhaseType_id.showContainer();
			HormonalPhaseType_id.setValue(this.params.HormonalPhaseType_id);
		}
		var PersonHeight_FS = Ext.getCmp(this.id + 'PersonHeight_FS');
		var PersonWeight_FS = Ext.getCmp(this.id + 'PersonWeight_FS');
		if (!isUfa || !isLab) {
			PersonHeight_FS.hide();
			PersonWeight_FS.hide();
			hiddenCount += 2;
		} else {
			PersonHeight_FS.show();
			PersonWeight_FS.show();
		}
		this.setHeight(this.height - hiddenCount * 20);
	},
	loadEvnDirectionPersonDetails: function () {
		var scope = this;
		var form = scope.FormPanel.getForm();
		var evndirection_combo = form.findField('EvnDirection_id');
		return new Promise(function (resolve, reject) {
			var requestParams = {
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						resolve(response_obj);
					} else {
						throw new Error('Ошибка при загрузке сигнальной информации');
					}
				},
				params: {
					Person_id: evndirection_combo.Person_id,
					EvnDirection_id: evndirection_combo.getValue()
				},
				url: '/?c=PersonDetailEvnDirection&m=getOne'
			};
			Ext.Ajax.request(requestParams);
		});
	},
	loadUslugaComplexDetails: function () {
		var scope = this;
		var UslugaComplex_id = scope.FormPanel.getForm().findField('UslugaComplex_id').getValue();
		scope.params.UslugaComplex_List.push(UslugaComplex_id);
		return new Promise(function (resolve, reject) {
			var requestParams = {
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						resolve(response_obj);
					} else {
						throw new Error('Ошибка при загрузке информации об атрибутах комплексной услиги');
					}
				},
				params: {
					uslugaComplexList: Ext.util.JSON.encode(scope.params.UslugaComplex_List),
					UslugaComplex_id: UslugaComplex_id
				},
				url: '/?c=UslugaComplex&m=loadUslugaComplexAttributeGrid'
			};
			Ext.Ajax.request(requestParams);
		});
	},
	loadUslugaComplexList: function () {
		var scope = this;
		return new Promise(function (resolve, reject) {
			var tree = scope.uslugaTree.getRootNode().childNodes;
			var response_obj = [];
			tree.forEach((el) => {
				response_obj.push(el.attributes);
			});
			resolve(response_obj);
		});
	},
	checkUslugaAttribute: function (code, attributeList) {
		var flag = false;
		var tree = this.uslugaTree.getRootNode().childNodes;
		var checkedList = [];
		var UslugaComplex_id = this.FormPanel.getForm().findField("UslugaComplex_id").getValue();
		tree.forEach((el) => {
			if (el.attributes.checked) checkedList.push(el.attributes.id);
		});
		checkedList.push(UslugaComplex_id);
		for (var i = 0; i < attributeList.length; i++) {
			if (!inlist(attributeList[i].UslugaComplex_id, checkedList)) continue;
			if (attributeList[i].UslugaComplexAttributeType_Code == code) {
				flag = true;
				break;
			}
		}
		return flag;
	}
});
