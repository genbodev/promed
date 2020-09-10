/**
* ufa_swEvnReceptRlsProvideWindow - окно выбора параметров обеспечения рецептов (Для Уфы, временная форма).
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


sw.Promed.ufa_swEvnReceptRlsProvideWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 340,
	id: 'ufa_EvnReceptRlsProvideWindow',
	layout: 'border',
	modal: true,
	plain: true,
	resizable: false,
	action: 'add',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	setSum: function() {
		var sum = 0;

		this.GridPanel.getGrid().getStore().each(function(record){
			if (record.get('Kolvo') > 0) {
				sum += record.get('DrugOstatRegistry_Cost')*record.get('Kolvo');
			}
		});

		this.form.findField('TotalSum').setValue(sum > 0 ? sum.toFixed(2) : null);
	},
	doRequest:  function (params, b) { 
		//  b - кнопка обеспечения
		var wnd = this;
		if (1 == 2) {//alert(); return;
		    Ext.Ajax.request({
					callback: function(options, success, response) {
						if (response.responseText != '') {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (success && response_obj.success) {
								wnd.setDisabled(true);
								alert('Документ успешно исполнен');
								wnd.hide();
								wnd.callback();
							} else {
								sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При исполнении документа возникла ошибка');
							}
						}
						if (wnd.owner && wnd.owner.refreshRecords) {
							wnd.owner.refreshRecords(null,0);
						}
					},
					params: {
						DocumentUc_id: 822563
					},
					url: '/?c=DocumentUc&m=farm_executeDocumentUc'
			});
		}
		else {
		    params.DocumentUc_id = wnd.DocumentUc_id;
		    //console.log('params = '); console.log(params); return;
		    Ext.Ajax.request({
			    callback: function(options, success, response) {
				    b.setDisabled(false);//активируем кнопку
				    if (success) {
					    var response_obj = Ext.util.JSON.decode(response.responseText);
					    wnd.hide();
					    wnd.callback();
				    } else {
					    sw.swMsg.alert('Ошибка', 'При обеспечении рецепта возникли ошибки');
				    }
			    }.createDelegate(this),
			    params: params,
			    url: '/?c=Farmacy&m=provideEvnRecept'
		    });
		}
	},
	doProvide: function(b) {
		var wnd = this;
		var params = new Object();
		params.EvnRecept_id = wnd.EvnRecept_id;
		params.EvnReceptGeneral_id = wnd.EvnReceptGeneral_id;
		params.MedService_id = wnd.MedService_id;
		params.DrugOstatDataJSON = wnd.GridPanel.getJSONData();
		var OtpDate =  Ext.getCmp('DrugNomen_OtpDate').getValue();
		var  Month = Number(OtpDate.getMonth()) + 1;
		params.EvnRecept_otpDate =  OtpDate.getFullYear() + '.' + Month + '.' + OtpDate.getDate()
		
		console.log(params); //return false;
		if (!Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').ownerCt.hidden &&  Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').value != '') {
			//  Если передана статья расхода
			params.WhsDocumentCostItemType_id = Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').value;
		};		
		// Проверяем корректность даты отпуска
		if ( !Ext.getCmp('DrugNomen_OtpDate').isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					Ext.getCmp('DrugNomen_OtpDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			b.setDisabled(false);//активируем кнопку 
			return false;
		};
		
		var $flag = this.checkQuantity();
		if ($flag == 1)
				this.doRequest(params, b);
		else if ($flag == 2) {
			err_msg = "Суммарное количество медикаментов не совпадает с количеством в рецепте. Обеспечить рецепт?";
			sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId) 
				{
					if ( buttonId == 'no') {
						b.setDisabled(false);//активируем кнопку 
						return false;
					}
					else  if ( buttonId == 'yes') {
						this.doRequest(params, b);
					}
					 else
						 this.hide();

				}.createDelegate(this),
										   
									
				icon: Ext.Msg.WARNING,
				msg: err_msg,
				title: 'Внимание'
			});
		}
	},
	checkQuantity: function() {
		var cnt = this.form.findField('EvnRecept_Kolvo').getValue();
                
		var grid_cnt = 0;
		var err_msg = null;

		this.GridPanel.getGrid().getStore().each(function(record){
			if (record.get('Kolvo') > 0) {
				grid_cnt += record.get('Kolvo')*1;
			}
		});
		/*
		if (cnt != grid_cnt) {
			err_msg = "Суммарное количество медикаментов не совпадает с количеством в рецепте.";
		}
		*/
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
	show: function() {
		var wnd = this;

		this.EvnRecept_id = null;
		this.EvnReceptGeneral_id = null;
		this.Drug_id = null;
		this.EvnRecept_Kolvo = 0;
		this.MedService_id = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		record = arguments[0];

		if (!arguments[0]) {
			this.hide();
			return false;
		} 
		if (arguments[0].DocumentUc_id && arguments[0].DocumentUc_id != '') {
			this.DocumentUc_id = arguments[0].DocumentUc_id;
		}
		else this.DocumentUc_id = null;
		
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
		
		if (arguments[0].operation && arguments[0].operation == 'receptNotification') {
		    wnd.setTitle ('Резервирование ЛС при оповещении');
		    wnd.action = 'receptNotification';
		    Ext.getCmp('evnRecept_btnProvide').setText('Оповестить');
		    wnd.DocumentUc_id = 0;
			Ext.getCmp('DrugNomen_OtpDate').disable();
		} else {
		    wnd.setTitle ('Обеспечение рецепта');
		    wnd.action = 'add';
		    Ext.getCmp('evnRecept_btnProvide').setText('Обеспечить');
			Ext.getCmp('DrugNomen_OtpDate').enable();
		}

		sw.Promed.ufa_swEvnReceptRlsProvideWindow.superclass.show.apply(wnd, arguments);

		wnd.GridPanel.getGrid().getStore().removeAll();
		wnd.form.reset();
		wnd.form.setValues(arguments[0]);

		if (!arguments[0].DrugNomen_Code && arguments[0].Drug_id) {
			//получение регионального кода медикамента
			Ext.Ajax.request({
				callback: function(options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if(response_obj[0] && response_obj[0].DrugNomen_Code) {
							wnd.form.findField('DrugNomen_Code').setValue(response_obj[0].DrugNomen_Code);
						}
					} else {
						sw.swMsg.alert('Ошибка', 'При получении кода медикамента возникла ошибка');
					}
				},
				params: {
					Drug_id: arguments[0].Drug_id
				},
				url: '/?c=DrugNomen&m=getDrugNomenCode'
			});
		}
		
		Ext.getCmp('DrugNomen_OtpDate').setMaxValue(null);
		Ext.getCmp('DrugNomen_OtpDate').setMaxValue(null);
		
		if (arguments[0].EvnRecept_setDate) {
		    if (arguments[0].PersonPrivilege_begDate && arguments[0].PersonPrivilege_begDate > arguments[0].EvnRecept_setDate )
			Ext.getCmp('DrugNomen_OtpDate').setMinValue(arguments[0].PersonPrivilege_begDate); 
		    else Ext.getCmp('DrugNomen_OtpDate').setMinValue(arguments[0].EvnRecept_setDate); 
		}
		else 
		    if (arguments[0].PersonPrivilege_begDate)
			Ext.getCmp('DrugNomen_OtpDate').setMinValue(arguments[0].PersonPrivilege_begDate); 
		    
		if (arguments[0].EvnRecept_DateCtrl) {
		    if (arguments[0].PersonPrivilege_endDate  && arguments[0].PersonPrivilege_endDate < arguments[0].EvnRecept_DateCtrl)
			Ext.getCmp('DrugNomen_OtpDate').setMaxValue(arguments[0].PersonPrivilege_endDate);
		    else
			Ext.getCmp('DrugNomen_OtpDate').setMaxValue(arguments[0].EvnRecept_DateCtrl);
		}
		else if (arguments[0].PersonPrivilege_endDate) {
			Ext.getCmp('DrugNomen_OtpDate').setMaxValue(arguments[0].PersonPrivilege_endDate);
			}
    
		Ext.getCmp('DrugNomen_OtpDate').setValue(new Date);

		if (arguments[0].selection == 1) {  //  Если передан параметр
			Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').ownerCt.show();
			Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').enable();
			if (arguments[0].WhsDocumentCostItemType_id == 103) {
				Ext.getCmp('DrugNomen_WhsDocumentCostItemType').getStore().filter('WhsDocumentCostItemType_Name', /^(Региональная льгота)$/)
			}
			else {
				Ext.getCmp('DrugNomen_WhsDocumentCostItemType').getStore().filter('WhsDocumentCostItemType_Name', /^(ВЗН)|(Спец.питание)$/)
			}
		} else {
			//при открытии формы, сразу открываем окно для выбора серии
			Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').ownerCt.hide();
			if (arguments[0].subAccountType_id && arguments[0].subAccountType_id == 2) {
				this.subAccountType_id = arguments[0].subAccountType_id;
				wnd.GridPanel.editGrid('receptNotification'); 
				if (wnd.DocumentUc_id != undefined && wnd.DocumentUc_id != '')  //  Если обеспечения оповещенного рецепта
					wnd.GridPanel.ViewActions.action_add.setDisabled(true);
			} else {
				this.subAccountType_id = null;
				wnd.GridPanel.ViewActions.action_add.setDisabled(false);
				wnd.GridPanel.ViewActions['action_add'].execute();
			}
		};
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
							    if (dt2 > Ext.getCmp('DrugNomen_OtpDate').minValue)
								Ext.getCmp('DrugNomen_OtpDate').setMinValue(dt2); 
							}
							WhsDocumentUcInvent_DT = response_obj[0].WhsDocumentUcInvent_DT; 
							dt = new Date(WhsDocumentUcInvent_DT);
							if (dt < Ext.getCmp('DrugNomen_OtpDate').maxValue)
							    Ext.getCmp('DrugNomen_OtpDate').setMaxValue(dt); 
						}
					} else {
						sw.swMsg.alert('Ошибка', 'При получении даты закрытия отчетного периода возникла ошибка');
					}
				},			
				url: '/?c=RegistryRecept&m=geDrugPeriodCloseDT'
			});
	},
	title: 'Обеспечение рецепта',
	width: 800,
	initComponent: function() {
		var wnd = this;

		var form = new Ext.form.FormPanel({
			region: 'north',
			autoScroll: true,
			bodyStyle: 'padding: 7px; background:#DFE8F6;',
			autoHeight: true,
			border: false,
			frame: false,
			labelWidth: 80,
			items: [{
				name: 'EvnRecept_id',
				xtype: 'hidden'
			}, {
				name: 'EvnReceptGeneral_id',
				xtype: 'hidden'
			}, {
				layout: 'form',
				bodyStyle: 'background:#DFE8F6;',
				border: false,
				labelWidth: 80,
				//width: 790,
				items: [{
					layout: 'column',
					border: false,
					bodyStyle: 'background:#DFE8F6',
					items: [{
						layout: 'form',
						bodyStyle: 'background:#DFE8F6;',
						border: false,
						items: [{
							name: 'DrugNomen_OtpDate',
							id: 'DrugNomen_OtpDate',
							fieldLabel: 'Дата',
							allowBlank: false,
							//anchor: '30%',
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						}]
					}, {
						layout: 'form',
						bodyStyle: 'left: 70px; background:#DFE8F6;',
						width: 400,
						labelWidth: 100,
						border: false,
						items: [{
							xtype: 'swwhsdocumentcostitemtypecombo',
							fieldLabel: 'Статья расхода',
							id: 'DrugNomen_WhsDocumentCostItemType2',
							name: 'WhsDocumentCostItemType_id',
							width: 200,
							//allowBlank: false,
							/*
							 listeners: {
							 change: function(combo, newValue, oldValue )) {
							 var $val = combo.getValue();
							 //console.log ('$val = ' + $val);
							 switch($val) {
							 case 1: 
							 case 3: 
							 wnd.form.findField('DrugFinance_id').setValue(3);
							 break;
							 case 2: 
							 case 34: 
							 wnd.form.findField('DrugFinance_id').setValue(27);
							 break;
							 }
							 }
							 }
							 */
						}]
					}]
				}]
			}, {
				name: 'DrugNomen_Code',
				fieldLabel: 'Код',
				disabled: true,
				xtype: 'textfield'
			}, {
				name: 'Drug_Name',
				fieldLabel: 'Медикамент',
				disabled: true,
				anchor: '100%',
				xtype: 'textfield'
			}, {
				name: 'EvnRecept_Kolvo',
				fieldLabel: 'Количество',
				disabled: true,
				xtype: 'textfield'
			}, {
				name: 'TotalSum',
				fieldLabel: 'Сумма',
				disabled: true,
				xtype: 'textfield'
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			region: 'center',
			id: this.id + 'ViewFrame',
			actions: [
				{
					name: 'action_add',
					handler: function() {
						wnd.GridPanel.editGrid('add');
					}
				},{
					name: 'action_edit',
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
				{name: 'DocumentUcStr_id', header: 'DocumentUcStr_id', hidden: true},  
				 {name: 'Drug_Name', width: 110, header: 'Медикамент', type: 'string'},
				//  Ufa
				{name: 'storage_id', width: 110, header: 'storage_id', hidden: true},
				{name: 'Lpu_Nick', width: 110, header: 'МО', type: 'string', hidden: !getGlobalOptions().region.nick == 'ufa'},
				{name: 'DrugShipment_Name', id: 'autoexpand', width: 110, header: '№ партии', type: 'string'},
				{name: 'PrepSeries_id', hidden: true},
				{name: 'DrugNomen_Code', width: 120, header: 'Код ЛС'},
				{name: 'PrepSeries_Ser', width: 120, header: 'Серия'},
				{name: 'PrepSeries_GodnDate', width: 110, header: 'Срок годности', type: 'string'},
				{name: 'PrepSeries_isDefect', width: 80, header: 'Брак', type: 'checkbox'},
				{name: 'Okei_NationSymbol', width: 80, header: 'Ед.изм.', type: 'string'},
				{name: 'Kolvo', width: 80, header: 'Кол-во', type: 'float'},
				{name: 'DocumentUcStr_Price', width: 100, header: 'Цена', type: 'money'},
				{name: 'DrugOstatRegistry_Cost', hidden:true},
				{name: 'DocumentUcStr_IsNDS', width: 80, header: 'НДС в т.ч.', type: 'checkbox'},
				{name: 'DocumentUcStr_Sum', width: 110, header: 'Сумма', renderer: function(v, p, r) {
					return sw.Promed.Format.rurMoney(r.get('DocumentUcStr_Price')*r.get('Kolvo'));
				}},
				{name: 'DrugNds_id', hidden: true},
				{name: 'DrugNds_Code', width: 80, header: 'НДС', type: 'string'},
				{name: 'DocumentUcStr_SumNds', width: 110, header: 'Сумма НДС', renderer: function(v, p, r) {
					var nds_part = r.get('DocumentUcStr_IsNDS') > 0 ? (r.get('DocumentUcStr_Price')*(r.get('DrugNds_Code')/100.0)).toFixed(2) : r.get('DrugOstatRegistry_Cost')-r.get('DocumentUcStr_Price');
					return sw.Promed.Format.rurMoney(nds_part*r.get('Kolvo'));
				}},
				{name: 'DocumentUcStr_NdsSum', width: 110, header: 'Сумма с НДС', renderer: function(v, p, r) {
					return sw.Promed.Format.rurMoney(r.get('DrugOstatRegistry_Cost')*r.get('Kolvo'));
				}}
			],
			title: null,
			toolbar: true,
			editing: true,
			editformclassname: 'swEvnReceptRlsProvideEditWindow',
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('Grid_id') > 0 && !this.readOnly && (!wnd.subAccountType_id && wnd.subAccountType_id != 2)) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
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
					//console.log ('editGrid = ' + Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').value);
					if (!Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').ownerCt.hidden &&  Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').value != '')
						//  Если передана статья расхода
						params.WhsDocumentCostItemType_id = Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').value;

					if (wnd.action == 'receptNotification') {
					    // Если работаем  с отсроченными рецептами
					    params.operation = 'receptNotification';
					    params.subAccountType_id = 2;
					}   

					getWnd(view_frame.editformclassname).show({
						action: action,
						params: params,
						callback: function(data) {
							if ( record_count == 1 && !store.getAt(0).get('Grid_id') ) {
								view_frame.removeAll({ addEmptyRecord: false });
							}
							var record = new Ext.data.Record.create(view_frame.jsonData['store']);

							data.Grid_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
							view_frame.getGrid().getStore().insert(record_count, new record(data));
							view_frame.mergeRecords('DrugOstatRegistry_id');
							wnd.setSum();
							if  (!Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').ownerCt.hidden) 
								if (view_frame.getGrid().getStore().getCount() == 0)
									Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').enable();
								else
									Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').disable();
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
						if ( !Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').ownerCt.hidden &&  Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').value != ''
								&& action == 'edit')
							//  Если передана статья расхода
							params.WhsDocumentCostItemType_id = Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').value;

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
				if (action == 'receptNotification' && wnd.DocumentUc_id != undefined &&  wnd.DocumentUc_id != 0) {
				    //var rec = new Ext.data.Record.create(view_frame.jsonData['store']);
				    //var record_count = 1;
				    var Grid_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
				    view_frame.toolbar = false;
				    Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								//console.log('response_obj = '); console.log(response_obj);
								var rec = response_obj[0]
							
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
										DocumentUcStr_Sum: rec['DocumentUcStr_Sum'],
										// DrugOstatRegistry_Cost: 5, //rec['DocumentUcStr_SumNds'], 
										//DocumentUcStr_NdsSum: rec['DocumentUcStr_Sum'],
												PrepSeries_GodnDate: rec['DocumentUcStr_godnDate'],
										Lpu_Nick: rec['Lpu_Nick']
									})
								]);
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
				view_frame.getGrid().getStore().remove(selected_record);
				wnd.setSum();
				if  (!Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').ownerCt.hidden) 
					if (view_frame.getGrid().getStore().getCount() == 0)
							Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').enable();
						else
							Ext.getCmp('DrugNomen_WhsDocumentCostItemType2').disable();                                    
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
				handler: function(b) {
					b.setDisabled(true);//деактивируем кнопку (исключение повторных нажатий)
					this.doProvide(b);
				}.createDelegate(this),
				iconCls: 'add16',
				id: 'evnRecept_btnProvide',
				text: 'Обеспечить'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
 			items: [
				 form,
				 this.GridPanel
			]
		});

		this.form = form.getForm();

		sw.Promed.ufa_swEvnReceptRlsProvideWindow.superclass.initComponent.apply(this, arguments);
	}
});

