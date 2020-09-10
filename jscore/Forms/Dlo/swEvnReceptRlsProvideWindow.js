/**
* swEvnReceptRlsProvideWindow - окно выбора параметров обеспечения рецептов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      	DLO
* @access       	public
* @copyright    	Copyright (c) 2013 Swan Ltd.
* @author       	Salakhov R.
* @version      	18.10.2013
*/

//var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');

sw.Promed.swEvnReceptRlsProvideWindow = Ext.extend(sw.Promed.BaseForm, {
	//height: 750,
    //autoHeight: true,
	id: 'EvnReceptRlsProvideWindow',
	layout: 'border',
	modal: true,
	maximizable: true,
	maximized: true,
	minHeight: 550,
	minWidth: 900,
	plain: true,
	resizable: true,
	listeners: {
		'hide': function() {
			this.onHide();
		},
        activate: function(){
            sw.Applets.BarcodeScaner.startBarcodeScaner({callback: this.getDrugFromScanner.createDelegate(this), readObject: 'EAN'});
			this.ScanCodeService.start();
        },
        deactivate: function() {
            sw.Applets.BarcodeScaner.stopBarcodeScaner();
			this.ScanCodeService.stop();
        }
	},
	uuidv4: function () {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
			var r = Math.random() * 16 | 0,
					v = c == 'x' ? r : (r & 0x3 | 0x8);
			return v.toString(16);
		});
	},
    getDrugFromScanner: function(drug_data)
    {
        this.AddDrug(drug_data)
    },
    AddDrug: function(drug_data)
    {
        var drug_EAN = drug_data;
        var params = new Object();
        var wnd = this;
        var view_frame = wnd.GridPanel;
        var store = view_frame.getGrid().getStore();
        wnd.form.findField('EvnRecept_EAN').setValue(drug_data);
        Ext.getCmp('ERRPW_Drug_id').clearValue();
        /*params.EvnRecept_id = wnd.EvnRecept_id;
        params.EvnReceptGeneral_id = wnd.EvnReceptGeneral_id;
        params.EvnRecept_Kolvo = wnd.EvnRecept_Kolvo;
        params.Drug_id = wnd.Drug_id;
        params.MedService_id = wnd.MedService_id;*/
        params.EvnRecept_Kolvo = wnd.EvnRecept_Kolvo;
        params.MedService_id = wnd.MedService_id;
        params.Drug_ean = drug_EAN;
        params.DrugFinance_id = wnd.form.findField('EvnRecept_DrugFinance').getValue();
        params.WhsDocumentCostItemType = wnd.form.findField('EvnRecept_WhsDocumentCostItemType_id').getValue();
        params.DrugComplexMnn_id = wnd.form.findField('DrugComplexMnn_id').getValue();
        params.Sin_check = wnd.form.findField('Sin_check').getValue()==true?1:0;
        //wnd.form.findField('EvnRecept_Finance').setValue(response_obj[0].EvnRecept_Finance_id);
        //wnd.form.findField('EvnRecept_WhsDocumentCostItemType').setValue(response_obj[0].EvnRecept_WhsDocumentCostItemType);
        var record_count = store.getCount();
        if ( record_count == 1 && !store.getAt(0).get('Grid_id') ) {
            view_frame.removeAll({ addEmptyRecord: false });
            record_count = 0;
        }
        getWnd(view_frame.editformclassname).show({
            action: 'add',
            params: params,
            callback: function(data) {
                if ( record_count == 1 && !store.getAt(0).get('Grid_id') ) {
                    view_frame.removeAll({ addEmptyRecord: false });
                }
                var record = new Ext.data.Record.create(view_frame.jsonData['store']);

                data.Grid_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
                data.BarCode_Data = '';
                view_frame.getGrid().getStore().insert(record_count, new record(data));
                view_frame.mergeRecords('DrugOstatRegistry_id');
                wnd.setSum();
            }
        });
    },

	setSum: function() {
		var sum = 0;
        var count_array = new Object();
        var gu_array = new Array();
        var gu_id = 0;

        //alert(sum);
		this.GridPanel.getGrid().getStore().each(function(record){
			if (record.get('Kolvo') > 0) {
				//sum += record.get('DrugOstatRegistry_Cost')*record.get('Kolvo');
                sum += record.get('DocumentUcStr_Price')*record.get('Kolvo');
                gu_id = record.get('GoodsUnit_id') > 0 ? record.get('GoodsUnit_id') : 0;
                if (count_array[gu_id] === undefined) {
                    count_array[gu_id] = 0;
                    gu_array.push({
                        GoodsUnit_id: gu_id,
                        GoodsUnit_Nick: gu_id > 0 ? record.get('GoodsUnit_Nick') : 'уп. (не опр)'
                    });
                }
                count_array[gu_id] += record.get('Kolvo')*1;
			}
		});

        var cnt_str = "";
        for (var i = 0; i < gu_array.length; i++) {
            gu_id = gu_array[i].GoodsUnit_id;
            var prx_str = cnt_str.length > 0 ? ", " : "";
            cnt_str += (count_array[gu_id] > 0 ? (prx_str+count_array[gu_id].toFixed(2)+" "+gu_array[i].GoodsUnit_Nick) : null);
        }

        Ext.getCmp('TotalSum').setValue(sum > 0 ? sum.toFixed(2) : null);
        Ext.getCmp('TotalCount').setValue(cnt_str);
        if(this.form.findField('EvnRecept_Discount').getValue() == '100%')
            Ext.getCmp('SumPaid').setValue(0);
        else
            Ext.getCmp('SumPaid').setValue(sum/2);
        //wnd.form.findField('EvnRecept_Discount')
	},
    setFieldsByBarCode: function(bar_code) {
        alert('На данный момент эта функция не доступна');
    },
	doProvide: function() {
		var wnd = this;
		var params = new Object();
		params.EvnRecept_id = wnd.EvnRecept_id;
		params.EvnReceptGeneral_id = wnd.EvnReceptGeneral_id;
		params.MedService_id = wnd.MedService_id;
		params.DrugOstatDataJSON = wnd.GridPanel.getJSONData();

		if(getGlobalOptions().region.nick == 'ufa') { // Для Уфимского региона
			// Проверяем корректность даты отпуска
			if ( !Ext.getCmp('ERRPW_OtpDate').isValid() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						Ext.getCmp('ERRPW_OtpDate').focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			};
			var OtpDate =  Ext.getCmp('ERRPW_OtpDate').getValue();
			var  Month = Number(OtpDate.getMonth()) + 1;
			params.EvnRecept_otpDate =  OtpDate.getFullYear() + '.' + Month + '.' + OtpDate.getDate()
			
			if (!Ext.getCmp('DrugNomen_WhsDocumentCostItemType').disabled && Ext.getCmp('DrugNomen_WhsDocumentCostItemType').value != '') {
				//  Если передана статья расхода
				params.WhsDocumentCostItemType_id = Ext.getCmp('DrugNomen_WhsDocumentCostItemType').value;
			};
			params.DocumentUc_id = wnd.DocumentUc_id;
		}

        wnd.buttons[0].setDisabled(true);//деактивируем кнопку (исключение повторных нажатий)
        wnd.setDisabled(true); // деактивируем окно
        wnd.getLoadMask().show();

		if (this.checkQuantity()) {
			Ext.Ajax.request({
				callback: function(options, success, response) {	
					wnd.setDisabled(false);
                    wnd.buttons[0].setDisabled(false);
					wnd.getLoadMask().hide();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if ( getRegionNick() == 'ufa' && wnd.Marking == true) {
							var params = new Object();
							params.rvRequestId = wnd.uuidv4();
							params.EvnRecept_id = wnd.EvnRecept_id;
							params.type = 'registerMarksByRequisites';
							Ext.Ajax.request({
								params: params,
								url: '/?c=MDLP&m=QueueUp'
							});
						}
						wnd.hide();
						wnd.callback();
					} else {
						sw.swMsg.alert(langs('Ошибка'), langs('При обеспечении рецепта возникли ошибки'));
					}
				}.createDelegate(this),
				params: params,
				url: '/?c=Farmacy&m=provideEvnRecept'
			});
		} else {
            wnd.setDisabled(false);
            wnd.buttons[0].setDisabled(false);
            wnd.getLoadMask().hide();
        }
	},
	checkQuantity: function() {
		var cnt = this.form.findField('EvnRecept_Kolvo').getValue();
		var grid_cnt = 0;
		var err_msg = null;
		var coeff = 1; //коэфицент пересчета количества для синонимов

		this.GridPanel.getGrid().getStore().each(function(record){
			if (record.get('Kolvo') > 0) {
				coeff = record.get('WhsDocumentSupplySpecDrug_Coeff') * 1;
				if (coeff <= 0) {
					coeff = 1;
				}
				grid_cnt += record.get('Kolvo') / coeff;
			}
		});

		if (cnt != grid_cnt) {
			if (getGlobalOptions().region.nick != 'ufa')
				//  Для Уфы контроль количества осуществляется отдельно
				err_msg = "Суммарное количество медикаментов не совпадает с количеством в рецепте.";

			if (grid_cnt <= 0) {
				err_msg = "Необходимо указать хотя бы одну серию.";
			}
			;
			if (err_msg != null) {
				sw.swMsg.alert(langs('Ошибка'), err_msg);
				return false;
			}
		}
		return true;
	},
	checkQuantity_Ufa: function() {
		var cnt = this.form.findField('EvnRecept_Kolvo').getValue();
                
		var grid_cnt = 0;
		var err_msg = null;

		this.GridPanel.getGrid().getStore().each(function(record){
			if (record.get('Kolvo') > 0) {
				grid_cnt += record.get('Kolvo')*1;
			}
		});

		if (grid_cnt <= 0) {
			err_msg = "Необходимо указать хотя бы одну серию.";
			sw.swMsg.alert('Ошибка', err_msg);
			return 0;
		}
		if (cnt != grid_cnt) 
			return 2;
		else
			return 1;
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Подождите. Выполняется сохранение данных.' });
		}
		return this.loadMask;
	},
	show: function() {
		var wnd = this;

		this.EvnRecept_id = null;
		this.EvnReceptGeneral_id = null;
		this.Drug_id = null;
		this.EvnRecept_Kolvo = 0;
		this.MedService_id = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		Ext.getCmp('ERRPW_Drug_id').getStore().baseParams = new Object();
		Ext.getCmp('ERRPW_Drug_id').setDisabled(false);

		if (!arguments[0]) {
			this.hide();
			return false;
		}

		if (arguments[0].EvnRecept_id && arguments[0].EvnRecept_id > 0) {
			this.EvnRecept_id = arguments[0].EvnRecept_id;
		}

		if (arguments[0].EvnReceptGeneral_id && arguments[0].EvnReceptGeneral_id > 0) {
			this.EvnReceptGeneral_id = arguments[0].EvnReceptGeneral_id;
		}

		if (arguments[0].Drug_id && arguments[0].Drug_id > 0) {
			this.Drug_id = arguments[0].Drug_id;
		}

		if (arguments[0].EvnRecept_Kolvo && arguments[0].EvnRecept_Kolvo > 0) {
			this.EvnRecept_Kolvo = arguments[0].EvnRecept_Kolvo;
		}

		if (arguments[0].MedService_id && arguments[0].MedService_id > 0) {
			this.MedService_id = arguments[0].MedService_id;
		}

		if (arguments[0].callback && typeof(arguments[0].callback) == 'function') {
			this.callback = arguments[0].callback;
		}

		if (arguments[0].onHide && typeof(arguments[0].onHide) == 'function') {
			this.onHide = arguments[0].onHide;
		}
		
		if (arguments[0].Marking ) {
			this.Marking = arguments[0].Marking;
		}
		else {
			this.Marking  = false;
		}

		sw.Promed.swEvnReceptRlsProvideWindow.superclass.show.apply(wnd, arguments);

		//сброс блокировки формы
        wnd.setDisabled(false);
        wnd.buttons[0].setDisabled(false);
        wnd.getLoadMask().hide();
		Ext.getCmp('SearchByEAN').setDisabled(false);
		wnd.form.findField('EvnRecept_Drugnomen_Code').ownerCt.hide(); 
		//wnd.form.findField('BarCodeInput_Field').ownerCt.show();
		
		//  Уфа. Региональные особенности
		if(getGlobalOptions().region.nick == 'ufa') { // Для Уфимского региона
			if (arguments[0].DocumentUc_id && arguments[0].DocumentUc_id != '') {
				wnd.DocumentUc_id = arguments[0].DocumentUc_id;
			}
			else wnd.DocumentUc_id = null;
			if (arguments[0].selection == 1 || arguments[0].WhsDocumentCostItemType_id == 103) {  //  Если передан параметр
				Ext.getCmp('DrugNomen_WhsDocumentCostItemType').ownerCt.show();
				Ext.getCmp('DrugNomen_WhsDocumentCostItemType').enable();
				if (arguments[0].WhsDocumentCostItemType_id == 103) {
					Ext.getCmp('DrugNomen_WhsDocumentCostItemType').getStore().filter('WhsDocumentCostItemType_Name', /^(Региональная льгота)|(БСК)$/)
				}
				else {
					Ext.getCmp('DrugNomen_WhsDocumentCostItemType').getStore().filter('WhsDocumentCostItemType_Name', /^(ВЗН)|(Спец.питание)$/)
				}
			} else {
				Ext.getCmp('DrugNomen_WhsDocumentCostItemType').getStore().filter.clear;			
				Ext.getCmp('DrugNomen_WhsDocumentCostItemType').disable();
				//Ext.getCmp('ERRPW_DrugFinance_id').ownerCt.show();		
				if (arguments[0].subAccountType_id && arguments[0].subAccountType_id == 2) {
					this.subAccountType_id = arguments[0].subAccountType_id;;
					if (wnd.DocumentUc_id != undefined && wnd.DocumentUc_id != '') {  //  Если обеспечения оповещенного рецепта
						wnd.GridPanel.ViewActions.action_add.setDisabled(true);
						Ext.getCmp('SearchByEAN').setDisabled(true);
					}
				} else {
					this.subAccountType_id = null;
					wnd.GridPanel.ViewActions.action_add.setDisabled(false);
				}
			}
			if (arguments[0].operation && arguments[0].operation == 'receptNotification') {
				wnd.setTitle ('Резервирование ЛС при оповещении');
				wnd.action = 'receptNotification';
				wnd.buttons[0].setText('Оповестить');
				wnd.DocumentUc_id = 0;
				//Ext.getCmp('ERRPW_OtpDate').disable();
			} else {
				wnd.setTitle ('Обеспечение рецепта');
				wnd.action = 'add';
				wnd.buttons[0].setText('Обеспечить');
				//Ext.getCmp('ERRPW_OtpDate').enable();
			}
			wnd.form.findField('EvnRecept_Drugnomen_Code').ownerCt.show();
			//wnd.form.findField('BarCodeInput_Field').ownerCt.hide();
		}
        var EvnRecept_id = this.EvnRecept_id;
        Ext.Ajax.request({
            callback: function(options,success,response) {
                if(success){
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    wnd.GridPanel.getGrid().getStore().removeAll();
                    wnd.form.reset();

                    wnd.form.findField('EvnRecept_SerNumDate').setValue(response_obj[0].EvnRecept_SerNumDate);
                    wnd.form.findField('EvnRecept_LpuName').setValue(response_obj[0].EvnRecept_LpuName);
                    wnd.form.findField('EvnRecept_MedPersonal').setValue(response_obj[0].EvnRecept_MedPersonal);
                    wnd.form.findField('EvnRecept_DrugTorgName').setValue(response_obj[0].EvnRecept_DrugTorgName);
                    wnd.form.findField('DrugComplexMnn_RusName').setValue(response_obj[0].DrugComplexMnn_RusName);
                    wnd.form.findField('EvnRecept_WhsDocumentCostItemType').setValue(response_obj[0].EvnRecept_WhsDocumentCostItemType);
                    wnd.form.findField('EvnRecept_WhsDocumentCostItemType_id').setValue(response_obj[0].EvnRecept_WhsDocumentCostItemType_id);
					 wnd.form.findField('EvnRecept_WhsDocumentCostItemType_id').baseValue =  wnd.form.findField('EvnRecept_WhsDocumentCostItemType_id').getValue();
                    wnd.form.findField('EvnRecept_Privilege').setValue(response_obj[0].EvnRecept_PrivilegeC);
                    wnd.form.findField('EvnRecept_Discount').setValue(response_obj[0].EvnRecept_Discount);
                    wnd.form.findField('EvnRecept_VK').setValue(langs('Да'));
                    wnd.form.findField('EvnRecept_Kolvo').setValue(response_obj[0].EvnRecept_Kolvo);
                    wnd.form.findField('EvnRecept_DrugFinance').setValue(response_obj[0].EvnRecept_DrugFinance_id);
                    wnd.form.findField('DrugComplexMnn_id').setValue(response_obj[0].DrugComplexMnn_id);

					var Sin_check = Ext.getCmp('ERRPW_Sin_check').getValue()==true?1:0;
					Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.Sin_check = Sin_check;
					Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.EvnRecept_id = wnd.EvnRecept_id;
					Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.MedService_id = wnd.MedService_id;
					Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.Contragent_id = getGlobalOptions().Contragent_id;
					if(getGlobalOptions().region.nick == 'ufa') { // Для Уфимского региона
						if (arguments[0].WhsDocumentCostItemType_id == 103)
							Ext.getCmp('DrugNomen_WhsDocumentCostItemType').setValue(arguments[0].WhsDocumentCostItemType_id); 
						if (wnd.action == 'receptNotification') {  //Если оповещение
							// Если работаем  с отсроченными рецептами
							Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.subAccountType_id = 2;
						}
						if (Ext.getCmp('DrugNomen_WhsDocumentCostItemType').disabled)
							Ext.getCmp('DrugNomen_WhsDocumentCostItemType').setValue(response_obj[0].EvnRecept_WhsDocumentCostItemType_id);
					}
					Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.DrugFinance_id = Ext.getCmp('ERRPW_DrugFinance_id').getValue();
					Ext.getCmp('ERRPW_Drugnomen_Code').setValue(response_obj[0].Drug_Code);
						Ext.getCmp('ERRPW_Drug_id').getStore().load({
							callback: function(){
								if (getGlobalOptions().region.nick == 'ufa' && wnd.subAccountType_id == 2) {
									wnd.GridPanel.editGrid('receptNotification'); 
								}
								Ext.getCmp('ERRPW_Drugnomen_Code').fireEvent('change',Ext.getCmp('ERRPW_Drugnomen_Code'),Ext.getCmp('ERRPW_Drugnomen_Code').getValue());
								if (getRegionNick() == 'ufa')
									wnd.form.findField('EvnRecept_Drugnomen_Code').focus();
								else 
									wnd.form.findField('EvnRecept_EAN').focus();
							}
					});
                }
            },
            params: {
                EvnRecept_id: EvnRecept_id
            },
            url: '/?c=EvnRecept&m=SearchReceptFromBarcode'
        });
		
				
		if(getGlobalOptions().region.nick == 'ufa') { // Для Уфимского региона
			//  Обрабатываем дату обеспечения
			Ext.getCmp('ERRPW_OtpDate').setMaxValue(null);
			record = arguments[0];
			
			if (arguments[0].EvnRecept_setDate) {
				if (arguments[0].PersonPrivilege_begDate && arguments[0].PersonPrivilege_begDate > arguments[0].EvnRecept_setDate )
				Ext.getCmp('ERRPW_OtpDate').setMinValue(arguments[0].PersonPrivilege_begDate); 
				else Ext.getCmp('ERRPW_OtpDate').setMinValue(arguments[0].EvnRecept_setDate); 
			}
			else 
				if (arguments[0].PersonPrivilege_begDate)
				Ext.getCmp('ERRPW_OtpDate').setMinValue(arguments[0].PersonPrivilege_begDate); 

			if (arguments[0].EvnRecept_DateCtrl) {
				if (arguments[0].PersonPrivilege_endDate  && arguments[0].PersonPrivilege_endDate < arguments[0].EvnRecept_DateCtrl)
				Ext.getCmp('ERRPW_OtpDate').setMaxValue(arguments[0].PersonPrivilege_endDate);
				else
				Ext.getCmp('ERRPW_OtpDate').setMaxValue(arguments[0].EvnRecept_DateCtrl);
			}
			else if (arguments[0].PersonPrivilege_endDate) {
				Ext.getCmp('ERRPW_OtpDate').setMaxValue(arguments[0].PersonPrivilege_endDate);
				};
			Ext.getCmp('ERRPW_OtpDate').setValue(new Date);
			//  Получаем дату закрытия отчетного периода
			Ext.Ajax.request({
					params: {
						Org_id: getGlobalOptions().org_id
					},
					callback: function(options, success, response) {
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if(response_obj[0] && response_obj[0].DrugPeriodClose_DT) {
								DrugPeriodClose_DT = response_obj[0].DrugPeriodClose_DT;
								dt = record.EvnRecept_setDate;

								// Преобразуем дату в строку в формат ГГГ/ММ/ДД
								dt1 = dt.getFullYear() + ((dt.getMonth() < 9) ? '/0': '/') + (dt.getMonth() + 1) + '/' + ((dt.getDate() < 10) ? '0': '') + dt.getDate();
								// Сравниваем даты
								if (DrugPeriodClose_DT >= dt1) {
									dt2 = new Date(DrugPeriodClose_DT)
									if (dt2 > Ext.getCmp('ERRPW_OtpDate').minValue)
									Ext.getCmp('ERRPW_OtpDate').setMinValue(dt2); 
								}
								WhsDocumentUcInvent_DT = response_obj[0].WhsDocumentUcInvent_DT; 
								dt = new Date(WhsDocumentUcInvent_DT);
								if (dt < Ext.getCmp('ERRPW_OtpDate').maxValue)
									Ext.getCmp('ERRPW_OtpDate').setMaxValue(dt); 
							}
							Ext.getCmp('ERRPW_OtpDate').setValue(new Date);
						} else {
							sw.swMsg.alert('Ошибка', 'При получении даты закрытия отчетного периода возникла ошибка');
						}
					},			
					url: '/?c=RegistryRecept&m=geDrugPeriodCloseDT'
				});
				Ext.getCmp('ERRPW_OtpDate').setValue(new Date);
			}
	},
	
	title: langs('Отпуск лекарственных средств'),
	width: 900,
	initComponent: function() {
		var wnd = this;
        this.onKeyDown = function (inp, e) {
            if (e.getKey() == Ext.EventObject.ENTER) {
                e.stopEvent();
            }
        }.createDelegate(this);

		this.ScanCodeService = new sw.Promed.ScanCodeService({
			onGetDrugPackData: function(drugPackObject) {
				wnd.GridPanel.addRecordByBarCode(drugPackObject, 'scanner_data');
			}
		});

		var form = new Ext.form.FormPanel({
			region: 'north',
			autoScroll: true,
			bodyStyle: 'padding: 7px; background:#DFE8F6;',
			autoHeight: true,
			border: false,
			frame: false,
			items: [
                {
				    name: 'EvnRecept_id',
				    xtype: 'hidden'
			    },
                {
				    name: 'EvnReceptGeneral_id',
				    xtype: 'hidden'
			    },
                {
                    autoHeight: true,
                    style: 'padding: 4px;',
                    title: langs('Выписанный рецепт'),
                    layout: 'form',
                    anchor: '100%',
                    xtype: 'fieldset',
                    labelWidth: 110,
                    items:[
                        {
                            name: 'EvnRecept_SerNumDate',
                            fieldLabel: langs('Серия, номер'),
                            disabled: true,
                            anchor: '50%',
                            xtype: 'textfield'
                        },
                        {
                            name: 'EvnRecept_LpuName',
                            fieldLabel: langs('МО'),
                            disabled: true,
                            anchor: '50%',
                            xtype: 'textfield'
                        },
                        {
                            name: 'EvnRecept_MedPersonal',
                            fieldLabel: langs('Врач'),
                            disabled: true,
                            anchor: '50%',
                            xtype: 'textfield'
                        },
                        {
                            layout: 'column',
                            bodyStyle: 'padding: 0px; background:#DFE8F6;',
                            border: false,
                            items:[{
                                bodyStyle: 'padding: 0px; background:#DFE8F6;',
                                layout: 'form',
                                border: false,
                                labelWidth: 110,
                                items:[{
                                    xtype: 'textfieldpmw',
                                    width: 300,
                                    disabled: true,
                                    name: 'EvnRecept_WhsDocumentCostItemType',
                                    id: 'EvnRecept_WhsDocumentCostItemType',
                                    fieldLabel: langs('Программа')
                                }]
                                }, {
                                    bodyStyle: 'padding-left: 20px; background:#DFE8F6;',
                                    layout: 'form',
                                    border: false,
                                    labelWidth: 65,
                                    items:[{
                                        xtype: 'textfieldpmw',
                                        width: 120,
                                        disabled: true,
                                        name: 'EvnRecept_Privilege',
                                        id: 'EvnRecept_Privilege',
                                        fieldLabel: langs('Категория')
                                    }]
                                },
                                {
                                    bodyStyle: 'padding-left: 20px; background:#DFE8F6;',
                                    layout: 'form',
                                    border: false,
                                    labelWidth: 45,
                                    items:[{
                                        xtype: 'textfieldpmw',
                                        width: 50,
                                        disabled: true,
                                        name: 'EvnRecept_Discount',
                                        id: 'EvnRecept_Discount',
                                        fieldLabel: langs('Скидка')
                                    }]
                                }]
                        },
                        {
                            name: 'EvnRecept_WhsDocumentCostItemType_id',
                            id: 'ERRPW_WhsDocumentCostItemType_id',
                            hidden: true,
                            hideLabel: true,
                            labelWidth: 220,
                            xtype: 'textfield'
                        },
                        {
                            name: 'DrugComplexMnn_id',
                            id: 'ERRPW_DrugComplexMnn_id',
                            hidden: true,
                            hideLabel: true,
                            xtype: 'textfield'
                        },
                        {
                            name: 'EvnRecept_DrugFinance_id',
                            hidden: true,
                            hideLabel: true,
                            labelWidth: 220,
                            xtype: 'textfield'
                        },
                        {
                            autoHeight: true,
                            style: 'padding: 7px;',
                            anchor: '100%',
                            xtype: 'fieldset',
							labelWidth: 105,
                            title: langs('Медикамент'),
                            items: [
                                {
                                    name: 'DrugComplexMnn_RusName',
                                    fieldLabel: langs('Наименование'),
                                    disabled: true,
                                    anchor: '50%',
                                    xtype: 'textfield'
                                },
                                {
                                    name: 'EvnRecept_DrugTorgName',
                                    fieldLabel: langs('Торг. наим.'),
                                    disabled: true,
                                    anchor: '50%',
                                    xtype: 'textfield'
                                },
                                {
                                    layout: 'column',
                                    bodyStyle: 'padding: 0px; background:#DFE8F6;',
                                    border: false,
                                    items:[{
                                        bodyStyle: 'padding: 0px; background:#DFE8F6;',
                                        layout: 'form',
                                        border: false,
                                        labelWidth: 105,
                                        items:[{
                                            xtype: 'textfieldpmw',
                                            width: 100,
                                            disabled: true,
                                            name: 'EvnRecept_VK',
                                            id: 'EvnRecept_VK',
                                            fieldLabel: langs('Решение ВК')
                                        }]
                                    }, {
                                        bodyStyle: 'padding-left: 20px; background:#DFE8F6;',
                                        layout: 'form',
                                        border: false,
                                        labelWidth: 80,
                                        items:[{
                                            xtype: 'textfieldpmw',
                                            width: 100,
                                            disabled: true,
                                            name: 'EvnRecept_Kolvo',
                                            id: 'EvnRecept_Kolvo',
                                            fieldLabel: langs('Кол-во (уп.)')
                                        }]
                                    }]
                                }
                            ]
                        }
                    ]
                },
                {
                    autoHeight: true,
                    style: 'padding: 7px;',
                    title: langs('Обеспечение рецепта'),
                    layout: 'form',
                    //width: 600,
                    anchor: '100%',
                    xtype: 'fieldset',
                    labelWidth: 110,
					items:[{
							layout: 'column',
							bodyStyle: 'padding: 0px; background:#DFE8F6;',
							border: false,
							width: 1000,
							items: [
								{
									layout: 'form',
									bodyStyle: 'background:#DFE8F6;',
									border: false,
									hidden: getGlobalOptions().region.nick != 'ufa',
									items: [
										{
											name: 'ERRPW_OtpDate',
											id: 'ERRPW_OtpDate',
											fieldLabel: 'Дата обеспечения',
											allowBlank: false,
											xtype: 'swdatefield',
											format: 'd.m.Y',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											width: 200
										}]
								}
					   ]},
						{
							bodyStyle: 'background:#DFE8F6;',
							layout: 'form',
							border: false,
							width: 550,
							labelWidth: 110,
							items: [{
								allowBlank: true,
								name: 'EvnRecept_Drugnomen_Code',
								id: 'EvnRecept_Drugnomen_Code', 
								fieldLabel: langs('Код маркировки'),
								disabled: false,
								width: 400,
								xtype: 'textfieldpmen',		
								listeners: {
									'valid': function (obj) {
											newValue =  obj.getValue();
											if (newValue.length > 27) {
												if (newValue.length == 3 || newValue.length == 5 || newValue.length == 27 || getRegionNick() == 'ufa') {
													if (wnd.form.findField('BarCode_AutoInsert').getValue() === true) {
														// вставки данных в список медикаментов
														wnd.GridPanel.addRecordByBarCode(newValue, 'test_str');
													} else {
														// заполнения данных блока "Обеспечение рецепта"
														wnd.setFieldsByBarCode(newValue);
													}
												}
												obj.setValue(null);
											}
									}
								}
							}]
						},
						{
							layout: 'column',
							bodyStyle: 'padding: 0px; background:#DFE8F6;',
							border: false,
							hidden: getRegionNick() == 'ufa',
							items:[{
                                layout: 'form',
                                bodyStyle: 'background:#DFE8F6;',
                                border: false,
                                items: [{
                                    xtype: 'textfieldpmen',
                                    hiddenName: 'BarCodeInput_Field',
                                    fieldLabel: 'Штрих-код',
                                    border: false,
                                    width: 200,
                                    listeners: {
                                        change: function(field, newValue) {
                                            if (newValue.length == 3 || newValue.length == 5 || newValue.length == 27 || getGlobalOptions().region.nick == 'ufa' ) {
                                                if (wnd.form.findField('BarCode_AutoInsert').getValue() === true) {
                                                    // вставки данных в список медикаментов
                                                    wnd.GridPanel.addRecordByBarCode(newValue, 'test_str');
                                                } else {
                                                    // заполнения данных блока "Обеспечение рецепта"
                                                    wnd.setFieldsByBarCode(newValue);
                                                }
                                            }
                                        }
                                    }
                                }]
                            }, {
                                layout: 'form',
                                bodyStyle: 'padding-left: 20px; background:#DFE8F6;',
                                border: false,
                                labelWidth: 145,
                                items: [{
                                    xtype: 'checkbox',
                                    name: 'BarCode_AutoInsert',
                                    hiddenName:'BarCode_AutoInsert',
                                    fieldLabel: 'Добавить код в список',
                                    checked: true,
                                    disabled: true
                                }]
                            }]
                        }, {
							layout: 'column',
							bodyStyle: 'padding: 0px; background:#DFE8F6;',
							border: false,
							items:[{
                                bodyStyle: 'background:#DFE8F6;',
                                layout: 'form',
                                border: false,
								hidden: getGlobalOptions().region.nick == 'ufa',
                                      items:[{
                                    allowBlank: false,
                                    autoLoad: false,
                                    //comboSubject: 'ReceptFinance',
                                    comboSubject: 'DrugFinance',
                                    fieldLabel: langs('Финансирование'),
                                    hiddenName: 'EvnRecept_DrugFinance',
                                    id: 'ERRPW_DrugFinance_id',
                                    lastQuery: '',
                                    listWidth: 200,
                                    tabIndex: TABINDEX_EREF + 10,
                                    validateOnBlur: true,
                                    width: 200,
                                    xtype: 'swcommonsprcombo',
                                    listeners:{
                                        'change': function(combo, value)
                                        {
                                            var Sin_check = Ext.getCmp('ERRPW_Sin_check').getValue()==true?1:0;

                                            var Drug_combo = Ext.getCmp('ERRPW_Drug_id');
                                            Drug_combo.getStore().baseParams.Sin_check = Sin_check;
                                            Drug_combo.getStore().baseParams.EvnRecept_id = wnd.EvnRecept_id;
                                            Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.MedService_id = wnd.MedService_id;
                                            Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.Contragent_id = getGlobalOptions().Contragent_id;
                                            Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.DrugFinance_id = value;
                                            Drug_combo.getStore().load();
                                        }.createDelegate(this)
                                    }
                                }]
                            }, 
							{layout: 'form',
									bodyStyle: 'padding-left: 0px; background:#DFE8F6;',
									border: false,
									//labelWidth: 110,
									hidden: getGlobalOptions().region.nick != 'ufa',
									items: [
										{
											xtype: 'swwhsdocumentcostitemtypecombo',
											fieldLabel: 'Статья расхода',
											//fieldLabel: langs('Финансирование'),
											id: 'DrugNomen_WhsDocumentCostItemType', //ERRPW_WhsDocumentCostItemType
											name: 'WhsDocumentCostItemType_id',
											width: 200,
											listeners: {
												'change': function (combo, value)
												{
													if (value != '') {
														//Ext.getCmp('ERRPW_WhsDocumentCostItemType_id').baseValue = Ext.getCmp('ERRPW_WhsDocumentCostItemType_id').getValue();
														Ext.getCmp('ERRPW_WhsDocumentCostItemType_id').setValue(value)
													}
													else {
														Ext.getCmp('ERRPW_WhsDocumentCostItemType_id').setValue(Ext.getCmp('ERRPW_WhsDocumentCostItemType_id').baseValue);
														}
														
													
													var Sin_check = Ext.getCmp('ERRPW_Sin_check').getValue() == true ? 1 : 0;

													var Drug_combo = Ext.getCmp('ERRPW_Drug_id');
													Drug_combo.getStore().baseParams.Sin_check = Sin_check;
													Drug_combo.getStore().baseParams.EvnRecept_id = wnd.EvnRecept_id;
													Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.MedService_id = wnd.MedService_id;
													Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.Contragent_id = getGlobalOptions().Contragent_id;
													Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.DrugFinance_id = Ext.getCmp('ERRPW_Drug_id').value;
													Ext.getCmp('ERRPW_Drug_id').getStore().baseParams.WhsDocumentCostItemType_id = value;
													Drug_combo.getStore().load();
												}.createDelegate(this)
											}
										}]
								},
								{
								bodyStyle: 'padding-left: 20px; background:#DFE8F6;',
								layout: 'form',
								border: false,
								labelWidth: 145,
								items:[{
									name:'Sin_check',
									hiddenName:'Sin_check',
									id: 'ERRPW_Sin_check',
									fieldLabel:langs('Синонимическая замена'),
									xtype: 'checkbox',
                                    tabIndex: TABINDEX_EREF + 20,
									listeners:{
										'check': function(checkbox,value){
											var Sin_check = value ==true?1:0;
											var Drug_combo = Ext.getCmp('ERRPW_Drug_id');
											Drug_combo.getStore().baseParams.Sin_check = Sin_check;
											Drug_combo.getStore().baseParams.EvnRecept_id = wnd.EvnRecept_id;
											Drug_combo.getStore().baseParams.MedService_id = wnd.MedService_id;
											Drug_combo.getStore().baseParams.Contragent_id = getGlobalOptions().Contragent_id;
											Drug_combo.getStore().baseParams.DrugFinance_id = Ext.getCmp('ERRPW_DrugFinance_id').getValue();
											Drug_combo.fireEvent('beforequery',Drug_combo);
											Drug_combo.getStore().load();
										}.createDelegate(this)
									}
								}]
							}]
						},
						{
							layout: 'column',
							bodyStyle: 'padding: 0px; background:#DFE8F6;',
							border: false,
							items:[{
								bodyStyle: 'padding: 0px; background:#DFE8F6;',
								layout: 'form',
								border: false,
								labelWidth: 110,
								items:[{
									allowBlank: true,
									name: 'EvnRecept_EAN',
									id:'ERRPW_EvnRecept_EAN',
									fieldLabel: langs('Код EAN13/EAN8'),
									disabled: false,
									width: 200,
									xtype: 'textfield',
                                    tabIndex: TABINDEX_EREF + 30,
									listeners:{
										'change': function(field, value) {
											Ext.getCmp('ERRPW_Drugnomen_Code').setValue('');
											Ext.getCmp('ERRPW_Drug_id').clearValue();
											wnd.Drug_id = null;
											var drug_combo = Ext.getCmp('ERRPW_Drug_id');
											var index = drug_combo.getStore().findBy(function(rec) {
												return (rec.get('Drug_Ean') == value);
											});
											if(index >= 0) {
												var drug_rec = drug_combo.getStore().getAt(index);
												var Drug_id = drug_rec.get('Drug_id');
												drug_combo.setValue(Drug_id);
												drug_combo.fireEvent('change',drug_combo,Drug_id);
											}
										}
									}
								}]
							}, {
								bodyStyle: 'padding-left: 20px; background:#DFE8F6;',
								layout: 'form',
								border: false,
								labelWidth: 25,
								items:[{
									allowBlank: true,
									name: 'ERRPW_Drugnomen_Code',
									id:'ERRPW_Drugnomen_Code',
									fieldLabel: langs('Код'),
									disabled: false,
									width: 150,
									xtype: 'textfield',
                                    tabIndex: TABINDEX_EREF + 40,
									listeners:{
										'change': function(field,value)
										{
											Ext.getCmp('ERRPW_Drug_id').clearValue();
											Ext.getCmp('ERRPW_EvnRecept_EAN').setValue('');
											wnd.Drug_id = null;
											var drug_combo = Ext.getCmp('ERRPW_Drug_id');
											var index = drug_combo.getStore().findBy(function(rec) {
												return (rec.get('Drug_Code') == value);
											});
											if(index >= 0){
												var drug_rec = drug_combo.getStore().getAt(index);
												var Drug_id = drug_rec.get('Drug_id');
												drug_combo.setValue(Drug_id);
												drug_combo.fireEvent('select', drug_combo, drug_rec, index);
											}
										}
									}
								}]
							}]
						},
                        {
                            allowBlank: true,
                            id: 'ERRPW_Drug_id',
                            fieldLabel: langs('Медикамент'),
                            listWidth: 900,
                            loadingText: langs('Идет поиск...'),
                            minLengthText: langs('Поле должно быть заполнено'),
                            onTrigger2Click: Ext.emptyFn,
                            tabIndex: TABINDEX_EREF + 50,
                            trigger2Class: 'hideTrigger',
                            validateOnBlur: false,
                            width: 917,
                            xtype: 'swdrugrlscombo',
                            listeners:{
                                'keydown': wnd.onKeyDown,
                                'beforequery': function(){
                                    var Sin_check = Ext.getCmp('ERRPW_Sin_check').getValue()==true?1:0;
                                    this.getStore().baseParams.Sin_check = Sin_check;
                                    this.getStore().baseParams.EvnRecept_id = wnd.EvnRecept_id;
                                    this.getStore().baseParams.MedService_id = wnd.MedService_id;
                                    this.getStore().baseParams.Contragent_id = getGlobalOptions().Contragent_id;
                                    this.getStore().baseParams.DrugFinance_id = Ext.getCmp('ERRPW_DrugFinance_id').getValue();
                                    //Ext.getCmp('ERRPW_EvnRecept_EAN').setValue('');
                                    //Ext.getCmp('ERRPW_Drugnomen_Code').setValue('');
                                },
                                'select': function(combo, record, index)  {
                                    combo.setLinkedFieldValues(record);
                                }
                            },
                            clearValue: function() {
                                sw.Promed.SwDrugRlsCombo.superclass.clearValue.apply(this, arguments);
                                this.setLinkedFieldValues(null);
                            },
                            setLinkedFieldValues: function(record) {
                                if(record && !Ext.isEmpty(record.get('Drug_id'))) {
                                    Ext.getCmp('ERRPW_Drugnomen_Code').setValue(record.get('Drug_Code'));
                                    Ext.getCmp('ERRPW_EvnRecept_EAN').setValue(record.get('Drug_Ean'));
                                    wnd.form.findField('Drug_Textarea').setValue(record.get('Drug_Name'));
                                } else {
                                    wnd.Drug_id = null;
                                    Ext.getCmp('ERRPW_EvnRecept_EAN').setValue('');
                                    Ext.getCmp('ERRPW_Drugnomen_Code').setValue('');
                                    wnd.form.findField('Drug_Textarea').setValue(null);
                                }
                            }
                        },
                        {
                            xtype: 'textarea',
                            name: 'Drug_Textarea',
                            disabled: true,
                            width: 900,
                            labelSeparator: '',
							hidden: getRegionNick() == 'ufa'
                        },
                        {
                            xtype: 'button',
                            id: 'SearchByEAN',
                            text: langs('Добавить в список'),
                            tabIndex: TABINDEX_EREF + 60,
                            handler: function()	{
                                var drug_ean = wnd.form.findField('EvnRecept_EAN').getValue();
                                var drug_id = wnd.form.findField('Drug_id').getValue();
                                if(!Ext.isEmpty(drug_id)) {
                                    var view_frame = wnd.GridPanel;
                                    var store = view_frame.getGrid().getStore();
                                    var record_count = store.getCount();
                                    if ( record_count == 1 && !store.getAt(0).get('Grid_id') ) {
                                        view_frame.removeAll({ addEmptyRecord: false });
                                        record_count = 0;
                                    }

                                    var params = new Object();
                                    if(Ext.getCmp('ERRPW_Drug_id').getValue()){
                                        params.DrugRls_id = drug_id;
                                        params.Drugnomen_Code = null;
                                    } else {
                                        params.DrugRls_id = null;
                                        params.Drugnomen_Code = Ext.getCmp('ERRPW_Drugnomen_Code').getValue();
                                    }

                                    params.EvnRecept_id = wnd.EvnRecept_id;
                                    params.MedService_id = wnd.MedService_id;
                                    params.EvnRecept_Kolvo = wnd.EvnRecept_Kolvo;
                                    params.MedService_id = wnd.MedService_id;
                                    params.DrugFinance_id = wnd.form.findField('EvnRecept_DrugFinance').getValue();
                                    params.WhsDocumentCostItemType = wnd.form.findField('EvnRecept_WhsDocumentCostItemType_id').getValue();
                                    params.DrugComplexMnn_id = wnd.form.findField('DrugComplexMnn_id').getValue();
                                    params.Sin_check = wnd.form.findField('Sin_check').getValue()==true?1:0;
									if (wnd.action == 'receptNotification') {  //Если оповещение
										// Если работаем  с отсроченными рецептами
										params.subAccountType_id = 2;
									}
									
                                    getWnd(view_frame.editformclassname).show({
                                        action: 'add',
                                        params: params,
                                        callback: function(data) {
                                            if ( record_count == 1 && !store.getAt(0).get('Grid_id') ) {
                                                view_frame.removeAll({ addEmptyRecord: false });
                                            }
											
                                            var record = new Ext.data.Record.create(view_frame.jsonData['store']);
											if (!data.BarCode_Data && getGlobalOptions().region.nick == 'ufa') {	//  Вставил для Уфы 
												data.BarCode_Data = '';
											};
                                            data.Grid_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
                                            data.BarCode_Data = '';
                                            view_frame.getGrid().getStore().insert(record_count, new record(data));
                                            view_frame.mergeRecords('DrugOstatRegistry_id');
                                            wnd.setSum();
                                        }
                                    });
                                } else if(!Ext.isEmpty(drug_ean)) {
                                    wnd.AddDrug(drug_ean);
                                }
                            }
                        }
                    ]
                }
            ]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			region: 'center',
			id: this.id + 'ViewFrame',
			actions: [
				{
					name: 'action_add',
                    hidden: true,
					handler: function() {
						wnd.GridPanel.editGrid('add');
					}
				},{
					name: 'action_edit',
                    hidden: false,
					handler: function() {
						wnd.GridPanel.editGrid('edit');
					}
				},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete'},
				{name: 'action_print'},
				{name: 'action_save'},
				{name: 'action_refresh', disabled: true, hidden: true},
				{name: 'action_print', disabled: true, hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			height: 180,
			paging: false,
			stringfields: [
				{name: 'Grid_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugOstatRegistry_id', hidden: true},
				{name: 'DocumentUcStr_id', hidden: true},
                {name: 'PrepSeries_Ser', width: 60, header: langs('Серия')},
                {name: 'PrepSeries_GodnDate', width: 70, header: langs('Годен до'), type: 'string'},
                {name: 'PrepSeries_isDefect', width: 40, header: langs('Брак'), type: 'checkbox'},
				{name: 'storage_id', width: 110, header: 'storage_id', hidden: true},
				{name: 'Storage_Name', width: 110, header: langs('Склад'), type: 'string',  hidden: getGlobalOptions().region.nick != 'ufa'},
                {name: 'DrugRls_id', hidden: true},
                {name: 'Drug_Name', width: 200, header: langs('Медикамент'), type: 'string'},
                {name: 'Kolvo', width: 60, header: langs('Кол-во'), type: 'float'},
                {name: 'PackKolvo', hidden: true},
                {name: 'GoodsUnit_id', hidden: true},
                {name: 'GoodsUnit_Nick', width: 60, header: langs('Ед.учета'), type: 'float'},
                {name: 'WhsDocumentSupplySpecDrug_Coeff', hidden: true},
                {name: 'GoodsPackCount_Count', hidden: true},
                {name: 'Okei_NationSymbol', width: 80, header: langs('Ед. изм.'), type: 'string', hidden: true},
                {name: 'DrugOstatRegistry_Cost', width: 60, header: langs('Цена'), type: 'money'},
                {name: 'DrugOstatRegistry_PackCost', hidden: true},
				{name: 'DrugShipment_Name', width: 115, header: langs('№ партии'), type: 'string', hidden: false},
                {name: 'DocumentUcStr_Sum', width: 70, hidden: true, header: langs('Сумма'), renderer: function(v, p, r) {
                    return sw.Promed.Format.rurMoney(r.get('DrugOstatRegistry_Cost')*r.get('Kolvo'));
                }},
                {name: 'Finance_and_CostItem', header: langs('Финансирование/ Ст.расхода'),width: 190, type: 'string'},
				{name: 'PrepSeries_id', hidden: true},
				{name: 'DocumentUcStr_Price', hidden:true},
				{name: 'DocumentUcStr_IsNDS', width: 80, header: langs('НДС в т.ч.'), type: 'checkbox', hidden:true},
				{name: 'DrugNds_id', hidden: true},
				        //{name: 'DrugNds_Code', width: 80, header: 'НДС', type: 'string'},
				{name: 'DocumentUcStr_SumNds', width: 110, header: langs('Сумма НДС'), hidden:true, renderer: function(v, p, r) {
					var nds_part = r.get('DocumentUcStr_IsNDS') > 0 ? (r.get('DocumentUcStr_Price')*(r.get('DrugNds_Code')/100.0)).toFixed(2) : r.get('DrugOstatRegistry_Cost')-r.get('DocumentUcStr_Price');
					return sw.Promed.Format.rurMoney(nds_part*r.get('Kolvo'));
				}},
				{name: 'DocumentUcStr_NdsSum', width: 110, header: langs('Сумма с НДС'), hidden:true, renderer: function(v, p, r) {
					return sw.Promed.Format.rurMoney(r.get('DrugOstatRegistry_Cost')*r.get('Kolvo'));
				}},
				{name: 'BarCode_Data', hidden: true},
                {name: 'BarCode_List', width: 110, header: (getRegionNick() != 'ufa') ? 'Штрих-коды' : 'Коды маркировки', renderer: function(v, p, r) {
                    var bc_array = !Ext.isEmpty('BarCode_Data') ? r.get('BarCode_Data').split(',') : new Array();
                    for(var i = 0; i < bc_array.length; i++) {
                        if (bc_array[i].indexOf('|') > -1) {
                            bc_array[i] = bc_array[i].substr(bc_array[i].indexOf('|')+1);
                        }
                    }
                    return bc_array.length > 0 ? bc_array.join('<br/>') : '';
                }}
			],
			title: null,
			toolbar: true,
			editing: true,
			editformclassname: 'swEvnReceptRlsProvideEditWindow',
			onRowSelect: function(sm,rowIdx,record) {
				if(getGlobalOptions().region.nick != 'ufa' || this.subAccountType_id == 2){ //оставил это странное условие только для Уфы (добавлено в редакции r124493), для прочих регионов вернул блок на пржнее место
					// Если не Уфа 
					if (record.get('Grid_id') > 0 && !this.readOnly) {
						this.ViewActions.action_edit.setDisabled(false);
						this.ViewActions.action_view.setDisabled(false);
						this.ViewActions.action_delete.setDisabled(false);
					} else {
						this.ViewActions.action_edit.setDisabled(true);
						this.ViewActions.action_view.setDisabled(true);
						this.ViewActions.action_delete.setDisabled(true);
					}
				}
			},
			editGrid: function (action) {
				if (action == null)	action = 'add';

				var view_frame = this;
				var store = view_frame.getGrid().getStore();

				if (action == 'add') {
					var record_count = store.getCount();
					if ( record_count == 1 && !store.getAt(0).get('Grid_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
						record_count = 0;
					}

					var params = new Object();
					params.EvnRecept_id = wnd.EvnRecept_id;
					params.EvnReceptGeneral_id = wnd.EvnReceptGeneral_id;
					params.EvnRecept_Kolvo = wnd.EvnRecept_Kolvo;
					params.Drug_id = wnd.Drug_id;
					params.MedService_id = wnd.MedService_id;
                    params.Sin_check = wnd.form.findField('Sin_check').getValue() == true ? 1 : 0;
					
					if (!Ext.getCmp('DrugNomen_WhsDocumentCostItemType').ownerCt.hidden && Ext.getCmp('DrugNomen_WhsDocumentCostItemType').value != '')
						//  Если передана статья расхода
						params.WhsDocumentCostItemType_id = Ext.getCmp('DrugNomen_WhsDocumentCostItemType').value;

					getWnd(view_frame.editformclassname).show({
						action: action,
						params: params,
						callback: function(data) {
							if ( record_count == 1 && !store.getAt(0).get('Grid_id') ) {
								view_frame.removeAll({ addEmptyRecord: false });
							}
							var record = new Ext.data.Record.create(view_frame.jsonData['store']);

							data.Grid_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
                            data.BarCode_Data = '';
							view_frame.getGrid().getStore().insert(record_count, new record(data));
							view_frame.mergeRecords('DrugOstatRegistry_id');
							wnd.setSum();
						}
					});
				}
				if (action == 'edit' || action == 'view') {
					var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
					if (selected_record.get('Grid_id') > 0) {
						var params = selected_record.data;
						params.EvnRecept_id = wnd.EvnRecept_id;
						params.EvnReceptGeneral_id = wnd.EvnReceptGeneral_id;
						params.MedService_id = wnd.MedService_id;
                        params.Sin_check = wnd.form.findField('Sin_check').getValue() == true ? 1 : 0;

						getWnd(view_frame.editformclassname).show({
							action: action,
							params: params,
							callback: function(data) {
								for(var key in data) {
									selected_record.set(key, data[key]);
								}
								selected_record.commit();
								view_frame.mergeRecords('DrugOstatRegistry_id');
								wnd.setSum();
							}
						});
					}
				}

				if (action == 'receptNotification' && wnd.DocumentUc_id != undefined && wnd.DocumentUc_id != 0) {
					Ext.getCmp('ERRPW_Drug_id').setDisabled(true);
					//var rec = new Ext.data.Record.create(view_frame.jsonData['store']);
					//var record_count = 1;
					var Grid_id = Math.floor(Math.random() * 10000); //генерируем временный идентификатор
					view_frame.toolbar = false;
					Ext.Ajax.request({
						callback: function (options, success, response) {
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								//view_frame.addRecords(response_obj);

								var rec = response_obj[0]
								if (rec['BarCode_Data'] == undefined) {
									rec['BarCode_Data'] = '';
								}		
								view_frame.getGrid().store.insert(0, [
									new Ext.data.Record({
										Grid_id: Grid_id,
										Drug_Name: rec['Drug_Name'],
										
										DocumentUcStr_id: rec['DocumentUcStr_id'],
										DrugShipment_Name: rec['DrugShipment_Name'],
										Kolvo: rec['DocumentUcStr_Count'],
										DrugOstatRegistry_Cost: rec['DocumentUcStr_Price'],
										DocumentUcStr_Price: rec['DocumentUcStr_Price'],
										PrepSeries_Ser: rec['DocumentUcStr_Ser'],
										DocumentUcStr_Sum: rec['DocumentUcStr_Sum'],
										DrugNds_Code: rec['DrugNds_Code'],
										DrugNomen_Code: rec['DrugNomen_Code'],
										Finance_and_CostItem: rec['Finance_and_CostItem'], 
//												//DocumentUcStr_NdsSum: rec['DocumentUcStr_Sum'],
										PrepSeries_GodnDate: rec['DocumentUcStr_godnDate'],
										Storage_Name: rec['Lpu_Nick'],
										BarCode_Data: rec['BarCode_Data'],	
										Lpu_Nick: rec['Lpu_Nick']
									})
								]);
								wnd.setSum();
							} else {
								sw.swMsg.alert('Ошибка', 'При получении данных возникла ошибка');
							}
						},
						params: {
							DocumentUc_id: wnd.DocumentUc_id
						},
						url: '/?c=DocumentUc&m=farm_loadDocumentUcStrList'
					});
				}
				
			},
			deleteRecord: function() {
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
                if (selected_record) {
                    view_frame.getGrid().getStore().remove(selected_record);
                    wnd.setSum();
                }
			},
			mergeRecords: function(field) { //обьединяем строки с одинаковым значением в поле field
				var unique_arr = new Object();
				var store = this.getGrid().getStore();
				store.each(function(record){
					var key = record.get(field);
					if (!unique_arr[key]) {
						unique_arr[key] = {
							Grid_id: record.get('Grid_id')
						};
					} else {
						var idx = store.findBy(function(rec) { return rec.get('Grid_id') == unique_arr[key].Grid_id; });
						if (idx > -1) {
							var rec = store.getAt(idx);
							rec.set('Kolvo', (rec.get('Kolvo')*1)+(record.get('Kolvo')*1));
							rec.commit();
						}
						store.remove(record);
					}
				});
			},
            addRecords: function(data_arr){
                var view_frame = this;
                var store = view_frame.getGrid().getStore();
                var record_count = store.getCount();
                var record = new Ext.data.Record.create(view_frame.jsonData['store']);

                if ( record_count == 1 && !store.getAt(0).get('Grid_id') ) {
                    view_frame.removeAll({addEmptyRecord: false});
                    record_count = 0;
                }

                for (var i = 0; i < data_arr.length; i++) {
                    data_arr[i].Grid_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
                    data_arr[i].state = 'add';
                    if (data_arr[i].BarCode_Data == undefined) {
                        data_arr[i].BarCode_Data = '';
                    }
                    store.insert(record_count, new record(data_arr[i]));
                }
                wnd.setSum();
            },
            addRecordByBarCode: function(bar_code_data, mode) { //функция обработки штрих-кода. mode (режим): 'test_str' - тестовый код длинной 5 символов, 'str' - код длинной 27 символов, 'scanner_data' - данные со сканера расшифрованные сервисом
                var view_frame = this;
                var bar_code = null;
                /*var storage_id = wnd.form.findField('Storage_sid').getValue();

                if (Ext.isEmpty(storage_id)) {
                    Ext.Msg.alert(langs('Ошибка'), 'Не выбран склад поставщика');
                    return false;
                }*/

                switch(mode) {
                    case 'test_str':
                    case 'str':
                        bar_code = bar_code_data;
                        break
                    case 'scanner_data':
                        bar_code = !Ext.isEmpty(bar_code_data.indSerNum) ? bar_code_data.indSerNum : null;
                        break
                }

                if (!Ext.isEmpty(bar_code)/* && !Ext.isEmpty(storage_id)*/) {
                    Ext.Ajax.request({
                        params: {
                            DrugPackageBarCode_BarCode: bar_code,
                            MedService_id: wnd.MedService_id,
							EvnRecept_id: wnd.EvnRecept_id
                        },
                        url: '/?c=Farmacy&m=getDrugOstatForProvideFromBarcode',
                        callback: function(options, success, response) {
                            if (response.responseText != '') {
                                var response_obj = Ext.util.JSON.decode(response.responseText);
								if (!response_obj[0]['success']) {
									sw.swMsg.alert(langs('Внимание'), response_obj[0]['Error_Msg']);
									return false;
								}							
                                for(var i = 0; i < response_obj.length; i++) {
                                    var idx = view_frame.getGrid().getStore().findBy(function(rec) { return rec.get('DrugOstatRegistry_id') == response_obj[i].DrugOstatRegistry_id; });
                                    if (idx >= 0) { //не нужно повторно добавлять строку, только добавить шотрих-код
                                        var idx_rec = view_frame.getGrid().getStore().getAt(idx);
                                        if ((idx_rec.get('BarCode_Data')+',').indexOf('|'+bar_code+',') < 0) { //проверяем нет ли штрих-кода уже в списке
                                            idx_rec.set('Kolvo', idx_rec.get('Kolvo')*1+1)
                                            idx_rec.set('BarCode_Data', idx_rec.get('BarCode_Data')+','+response_obj[i].DrugPackageBarCode_id+'|'+bar_code)
                                        }
                                        idx_rec.commit();
                                        response_obj.splice(i,1);
                                    } else {
                                        response_obj[i].state = 'add';
                                        response_obj[i].Kolvo = 1;
                                        response_obj[i].BarCode_Data = response_obj[i].DrugPackageBarCode_id+'|'+bar_code;
                                    }
                                }
                                view_frame.addRecords(response_obj);
                            }
                        }
                    });
                }


            },
			getJSONData: function(){ //данные грида в виде закодированной JSON строки
				var data = new Array();
				this.getGrid().getStore().each(function(record) {
					data.push(record.data);
				});
				return data.length > 0 ? Ext.util.JSON.encode(data) : "";
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons: [{
				handler: function() {
					if(getGlobalOptions().region.nick != 'ufa') { 
						this.doProvide();
					}
					else {
						// Для Уфимского региона
						var $flag = this.checkQuantity_Ufa();
						if ($flag == 1)
							//this.doRequest();
							this.doProvide();
						else if ($flag == 2) {
							err_msg = "Суммарное количество медикаментов не совпадает с количеством в рецепте. Обеспечить рецепт?";
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId) 
								{
									if ( buttonId == 'yes') {
										this.doProvide();
									} else {
										return false;
									}			 
								}.createDelegate(this),
								icon: Ext.Msg.WARNING,
								msg: err_msg,
								title: 'Внимание'
							});
						};
					};
				}.createDelegate(this),
				iconCls: 'add16',
				text: langs('Обеспечить'),
                tabIndex: TABINDEX_EREF + 200
			},
			{
				xtype: 'button',
				id: 'ERRPW_oldForm',
				text: 'Старая форма',
				tabIndex: TABINDEX_EREF + 201,
				hidden: getGlobalOptions().region.nick != 'ufa',
				handler: function()	{
					this.hide();
					getWnd('ufa_swEvnReceptRlsProvideWindow').show(record);
				}.createDelegate(this)
			},			
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL,
                tabIndex: TABINDEX_EREF + 210
			}],
 			items: [
				 form,
				 this.GridPanel,
                 new Ext.form.FormPanel({
                     region: 'south',
                     autoScroll: true,
                     bodyStyle: 'padding: 1px; background:#DFE8F6;',
                     autoHeight: true,
                     border: false,
                     frame: false,
                     items: [
                         {
                             layout: 'column',
                             bodyStyle: 'padding: 1px; background:#DFE8F6;',
                             border: false,
                             items:[{
                                 bodyStyle: 'padding: 7px; background:#DFE8F6;',
                                 layout: 'form',
                                 border: false,
                                 labelWidth: 100,
                                 items:[{
                                     xtype: 'textfieldpmw',
                                     width: 120,
                                     name: 'TotalCount',
                                     id: 'TotalCount',
                                     fieldLabel: langs('Всего, Ед.учета'),
									 disabled: true,
                                     tabIndex: TABINDEX_EREF + 100
                                 }]
                             }, {
                                 bodyStyle: 'padding: 7px; background:#DFE8F6;',
                                 layout: 'form',
                                 border: false,
                                 labelWidth: 60,
                                 items:[{
                                     xtype: 'textfieldpmw',
                                     width: 120,
                                     name: 'TotalSum',
                                     id: 'TotalSum',
                                     fieldLabel: langs('На сумму'),
									 disabled: true,
                                     tabIndex: TABINDEX_EREF + 110
                                 }]
                             }, {
                                 bodyStyle: 'padding: 7px; background:#DFE8F6;',
                                 layout: 'form',
                                 border: false,
                                 labelWidth: 60,
                                 items:[{
                                     xtype: 'textfieldpmw',
                                     width: 120,
                                     name: 'SumPaid',
                                     id: 'SumPaid',
                                     fieldLabel: langs('К оплате'),
									 disabled: true,
                                     tabIndex: TABINDEX_EREF + 120
                                 }]
                             }]
                         }
                         ]
                     })
			]
		});

		this.form = form.getForm();

		sw.Promed.swEvnReceptRlsProvideWindow.superclass.initComponent.apply(this, arguments);
	}
});