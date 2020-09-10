/**
 * swCostPrintWindow - Справка о стоимости лечения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      10.2014
 * @comment
 */
sw.Promed.swCostPrintWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	title: lang['spravka_o_stoimosti_lecheniya'],
	layout: 'form',
	id: 'CostPrintWindow',
	modal: true,
	shim: false,
	width: 500,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function(options) {
		// логика сохранения справки
		if ( this.formStatus == 'save' ) {
			return false;
		}

		var win = this;
		var base_form = this.FormPanel.getForm();

		this.formStatus = 'save';
		base_form.findField('CostPrint_IsNoPrint').setValue(options.CostPrint_IsNoPrint);

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		//Если вводим Представителя вручную
		if ( base_form.findField('Person_pid').getValue() == base_form.findField('Person_pid').getRawValue()) {
			var deputyFIO = base_form.findField('Person_pid').getRawValue();

			base_form.findField('Person_pid').setValue(null);
			base_form.findField('Person_pid').hiddenValue = null;
			base_form.findField('Person_pid').setRawValue(deputyFIO);
		}

		win.getLoadMask(lang['sohranenie_fakta_pechati_spravki']).show();
		base_form.submit({
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();

				var
					format = 'pdf',
					personPred = '&paramDeputyFIO=null',
					pattern = '',
					params = '';

				if (getPrintOptions().cost_print_extension == 2) {
					format = 'xls';
				} else if (getPrintOptions().cost_print_extension == 3) {
					format = 'html';
				}

				if (!base_form.findField('Person_pid').disabled) {
					personPred = '&paramDeputyFIO='+encodeURIComponent(base_form.findField('Person_pid').getRawValue());
				}

				var pattyear = '';
				if (getRegionNick() == 'perm' && win.Cost_Year >= 2015) {
					pattyear = '_2015';
				}

				if (options.CostPrint_IsNoPrint == 1) {
					// печатаем справку
					if (!Ext.isEmpty(base_form.findField('Evn_id').getValue())) {
						switch(win.type) {
							case 'EvnFuncRequest':
								// для заявок ФД
								pattern = 'pan_Spravka_FuncRequest.rptdesign';
								params = '&paramEvnFuncRequest=' + base_form.findField('Evn_id').getValue() + personPred;
								break;
							case 'EvnUslugaPar':
								// для параклиники
								pattern = 'pan_Spravka_ParUsl.rptdesign';
								params = '&paramEvnUsl=' + base_form.findField('Evn_id').getValue() + personPred;
							break;
							case 'EvnPL':
							case 'EvnPLStom':
								// для полки
								if((win.ByDay==1)&&(getRegionNick() == 'astra')) //https://redmine.swan.perm.ru/issues/55589
								{
									pattern = 'pan_Spravka_PL_DAY.rptdesign';
									var person_id = win.Person_id;
									var paramDate = Ext.util.Format.date(base_form.findField('CostPrint_setDate').getValue(),'d.m.Y');
									params = '&paramDate='+paramDate+'&paramPerson='+person_id + personPred;
								}
								else
								{
									// Если дата случая передана, и дата случая больше чем 01.11.2015 или если даты случая почему-то нет - считаем по новому
									var Evn_setDate = base_form.findField('Evn_setDate').getValue();
									var withDKL = ((Evn_setDate && (Date.parseDate(Evn_setDate, 'd.m.Y')>=Date.parseDate('01.11.2015', 'd.m.Y'))) || (!Evn_setDate));
									if (getRegionNick() == 'perm' && win.type == 'EvnPLStom' && withDKL) {
										pattern = 'pan_Spravka_PLStom'+pattyear+'.rptdesign';
									} else {
										pattern = 'pan_Spravka_PL'+pattyear+'.rptdesign';
									}
									params = '&paramEvnPL=' + base_form.findField('Evn_id').getValue() + personPred;
									if (getRegionNick() == 'astra') {
										params += '&paramLpu=' + getGlobalOptions().lpu_id;
									}
								}
							break;
							case 'EvnPS':
								// для стаца
								pattern = 'hosp_Spravka_KSG'+pattyear+'.rptdesign';
								params = '&paramEvnPS=' + base_form.findField('Evn_id').getValue() + personPred;
								if (getRegionNick() == 'astra') {
									params += '&paramLpu=' + getGlobalOptions().lpu_id;
								}
							break;
							case 'EvnPLDispDop13':
								// для дд взрослых
								pattern = 'pan_Spravka_PLDD'+pattyear+'.rptdesign';
								params = '&paramEvnPLDisp=' + base_form.findField('Evn_id').getValue() + personPred;
							break;
							case 'EvnPLDispProf':
								// для осмотров взрослых
								if (getRegionNick() == 'ufa') {
									pattern = 'pan_Spravka_PLDD'+pattyear+'.rptdesign';
									params = '&paramEvnPLDisp=' + base_form.findField('Evn_id').getValue() + personPred;
								} else {
									pattern = 'pan_Spravka_PLProf'+pattyear+'.rptdesign';
									params = '&paramEvnPLDisp=' + base_form.findField('Evn_id').getValue() + personPred;
								}
							break;
							case 'EvnPLDispOrp':
								// для дд детей
								pattern = 'pan_Spravka_PLOrp'+pattyear+'.rptdesign';
								params = '&paramEvnPLDisp=' + base_form.findField('Evn_id').getValue() + personPred;
							break;

							case 'EvnPLDispTeenInspection':
								// для осмотров детей
								if (getRegionNick() == 'ufa') {
									pattern = 'pan_Spravka_PLOrp'+pattyear+'.rptdesign';
									params = '&paramEvnPLDisp=' + base_form.findField('Evn_id').getValue() + personPred;
								} else {
									pattern = 'pan_Spravka_PLProfTeen'+pattyear+'.rptdesign';
									params = '&paramEvnPLDisp=' + base_form.findField('Evn_id').getValue() + personPred;
								}
							break;
						}
					} else if (!Ext.isEmpty(base_form.findField('CmpCallCard_id').getValue())) {
						// для СМП
						pattern = 'pan_Spravka_SMP'+pattyear+'.rptdesign';
						params = '&paramCmpCallCard=' + base_form.findField('CmpCallCard_id').getValue() + personPred;
					} else if (!Ext.isEmpty(base_form.findField('Person_id').getValue())){
						pattern = 'pan_Spravka_all.rptdesign';
						params = '&paramLpu=' + getGlobalOptions().lpu_id + '&paramPerson=' + base_form.findField('Person_id').getValue() + '&paramBegDate=' + Ext.util.Format.date(base_form.findField('CostPrint_begDate').getValue(),'d.m.Y') + '&paramEndDate=' + Ext.util.Format.date(base_form.findField('CostPrint_endDate').getValue(),'d.m.Y') + personPred;
					}
				} else {
					// печатаем отказ
					if (!Ext.isEmpty(base_form.findField('Evn_id').getValue())) {
						// для Evn
						pattern = 'pan_Spravka_Otkaz.rptdesign';
						params = '&paramEvn_id=' + base_form.findField('Evn_id').getValue() + personPred;
						if (getRegionNick() == 'astra') {
							params += '&paramLpu=' + getGlobalOptions().lpu_id;
						}
					} else if (!Ext.isEmpty(base_form.findField('CmpCallCard_id').getValue())) {
						// для СМП
						pattern = 'pan_Spravka_Otkaz_SMP.rptdesign';
						params = '&paramCmpCallCard=' + base_form.findField('CmpCallCard_id').getValue() + personPred;
					}else if (!Ext.isEmpty(base_form.findField('Person_id').getValue())){
						pattern = 'pan_Spravka_Otkaz_all.rptdesign';
						params = '&paramLpu=' + getGlobalOptions().lpu_id + '&paramPerson=' + base_form.findField('Person_id').getValue() + '&paramBegDate=' + Ext.util.Format.date(base_form.findField('CostPrint_begDate').getValue(),'d.m.Y') + '&paramEndDate=' + Ext.util.Format.date(base_form.findField('CostPrint_endDate').getValue(),'d.m.Y') + personPred;
					}
				}

				if (!Ext.isEmpty(pattern)) {
					printBirt({
						'Report_FileName': pattern,
						'Report_Params': params,
						'Report_Format': format
					});
				} else {
					sw.swMsg.alert(lang['soobschenie'], lang['ne_nayden_shablon_dlya_pechati']);
					log(lang['ne_nayden_shablon_dlya_pechati'], win.type, getRegionNick());
				}
				win.callback();
				win.hide();
			}
		});
	},
	type: '',
	show: function() {
		sw.Promed.swCostPrintWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = this.FormPanel.getForm();
		this.formStatus = 'edit';
		base_form.reset();

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { win.hide(); });
			return false;
		}

		this.callback = Ext.emptyFn;
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		this.type = '';
		if (arguments[0].type) {
			this.type = arguments[0].type;
		}

		this.ByDay = 0;	//https://redmine.swan.perm.ru/issues/55589
		if(arguments[0].ByDay){
			this.ByDay = arguments[0].ByDay;
		}
		base_form.findField('CostPrint_setDate').hideContainer();
		base_form.findField('CostPrint_setDate').setAllowBlank(true);

		base_form.findField('CostPrint_begDate').hideContainer();
		base_form.findField('CostPrint_begDate').setAllowBlank(true);
		base_form.findField('CostPrint_endDate').hideContainer();
		base_form.findField('CostPrint_endDate').setAllowBlank(true);

		var params = {};

		// для печати должен быть задан или Evn_id или CmpCallCard_id или Person_id.
		// if(arguments[0].Person_id && this.ByDay==1 && (getRegionNick() == 'astra')){ //https://redmine.swan.perm.ru/issues/55589
		// 	this.setTitle(langs('Справка о стоимости лечения за день'));
		// 	this.Person_id = arguments[0].Person_id;
		// 	params.Person_id = arguments[0].Person_id;
		// 	base_form.findField('CostPrint_setDate').showContainer();
		// 	base_form.findField('CostPrint_setDate').setAllowBlank(false);
		// }
		if ( arguments[0].Evn_id || arguments[0].CmpCallCard_id ) {
			// печать по конкретному случаю
			if (arguments[0].Evn_id) {
				params.Evn_id = arguments[0].Evn_id;
			}
			if (arguments[0].CmpCallCard_id) {
				params.CmpCallCard_id = arguments[0].CmpCallCard_id;
			}
		} else if ( arguments[0].Person_id ) {
			// печать за период
			base_form.findField('CostPrint_begDate').showContainer();
			base_form.findField('CostPrint_begDate').setAllowBlank(false);
			base_form.findField('CostPrint_endDate').showContainer();
			base_form.findField('CostPrint_endDate').setAllowBlank(false);
			params.Person_id = arguments[0].Person_id;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['neverno_ukazanyi_vhodnyie_dannyie'], function() { win.hide(); });
			return false;
		}

		base_form.setValues(arguments[0]);
		this.syncShadow();
		this.Cost_Year = 2015;

		// при открытии формы подгружаем представителя и дату печати справки
		win.getLoadMask(lang['poluchenie_informatsii_po_predstavitelyu']).show();
		Ext.Ajax.request({
			url: '/?c=CostPrint&m=getCostPrintData',
			params: params,
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.success) {
					if (result.Person_Pred) {
						//base_form.findField('Person_pid').getStore().loadData([{ 'Person_id': result.Person_pid, 'Person_Name': result.Person_Pred }]);
						//base_form.findField('Person_pid').setValue(result.Person_pid);
						base_form.findField('Person_pid').setValue(result.Person_pid);
						base_form.findField('Person_pid').hiddenValue = result.Person_pid;
						base_form.findField('Person_pid').setRawValue(result.Person_Pred);

						base_form.findField('CPW_ByPred').setValue(true);
						base_form.findField('CPW_Own').setValue(false);
					} else {
						base_form.findField('CPW_ByPred').setValue(false);
						base_form.findField('CPW_Own').setValue(true);
					}

					if (result.CostPrint_setDT) {
						base_form.findField('CostPrint_setDT').setValue(result.CostPrint_setDT);
					}
					if (result.Evn_setDate) {
						base_form.findField('Evn_setDate').setValue(result.Evn_setDate);
					}

					if (result.Cost_Year) {
						win.Cost_Year = result.Cost_Year;
					}
				} else {
					win.hide();
				}
			}
		});

		return true;
	},
	callback: Ext.emptyFn,
	initComponent: function() {
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			buttonAlign: 'left',
			frame: false,
			labelAlign: 'right',
			labelWidth: 170,
			url: '/?c=CostPrint&m=saveCostPrint',
			items: [{
				name: 'CostPrint_IsNoPrint',
				xtype: 'hidden'
			}, {
				name: 'Evn_id',
				xtype: 'hidden'
			}, {
				name: 'CostPrint_setDT',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'Evn_setDate',
				xtype: 'hidden'
			}, {
				html: 'Выберите вариант печати и укажите кому выдаётся справка',
				style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
				xtype: 'label'
			}, {
				layout: 'form',
				border: false,
				style: 'margin-left: 50px;',
				items: [{
					xtype: 'radio',
					checked: true,
					hideLabel: true,
					boxLabel: lang['lichno'],
					inputValue: 0,
					id: 'CPW_Own',
					name: 'Person_IsPred'
				}, {
					xtype: 'radio',
					hideLabel: true,
					id: 'CPW_ByPred',
					boxLabel: lang['predstavitel'],
					listeners: {
						'check': function(field, value) {
							var base_form = win.FormPanel.getForm();
							if (value) {
								// ФИО представителя доступно для редактирования
								base_form.findField('Person_pid').enable();
								base_form.findField('Person_pid').setAllowBlank(false);
							} else {
								base_form.findField('Person_pid').disable();
								base_form.findField('Person_pid').setAllowBlank(true);
							}
						}
					},
					inputValue: 1,
					name: 'Person_IsPred'
				}]
			}, {
				fieldLabel: lang['fio_predstavitelya'],
				hiddenName: 'Person_pid',
				readOnly: true,
				disabled: true,
				anchor: '-10',
				xtype: 'swpersoncomboex'
			},
			{ //https://redmine.swan.perm.ru/issues/55589
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 170,
					items: [{
						fieldLabel: lang['data'],
						name: 'CostPrint_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						width: 100,
						xtype: 'swdatefield'
					}]
				}]
			},
			{
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 170,
					items: [{
						fieldLabel: lang['period_formirovaniya_ot'],
						name: 'CostPrint_begDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						width: 100,
						xtype: 'swdatefield'
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 20,
					items: [{
						fieldLabel: lang['do'],
						name: 'CostPrint_endDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						width: 100,
						xtype: 'swdatefield'
					}]
				}]
			}]
		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function() {
						win.doSave({
							CostPrint_IsNoPrint: 1
						});
					},
					iconCls: 'save16',
					text: lang['pechat_spravki']
				},
				{
					handler: function() {
						win.doSave({
							CostPrint_IsNoPrint: 2
						});
					},
					iconCls: 'save16',
					text: lang['pechat_otkaza']
				},
				{
					text: '-'
				},
				HelpButton(this, 0),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items: [
				win.FormPanel
			]
		});
		sw.Promed.swCostPrintWindow.superclass.initComponent.apply(this, arguments);
	}
});