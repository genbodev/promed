/**
* swOnkoConsultEditWindow - окно редактирования сведений о консилиуме
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Morbus
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
*/

/*NO PARSE JSON*/
sw.Promed.swOnkoConsultEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'OnkoConsultEditWindow',
	maximizable: false,
	width: 600,
	//autoHeight: true,
	height: 600,
	modal: true,
	codeRefresh: true,
	objectName: 'swOnkoConsultEditWindow',
	objectSrc: '/jscore/Forms/Morbus/Specifics/swOnkoConsultEditWindow.js',
	returnFunc: function (owner) { },
	OnkoConsult_id: null,
	autoScroll: true,
	MorbusOnko_id: null,
	MorbusOnkoVizitPLDop_id: null,
	MorbusOnkoLeave_id: null,
	MorbusOnkoDiagPLStom_id: null,
	action: 'add',
	rattling: false, //!!!!!!!!!!!!!!!
	show: function () {
		sw.Promed.swOnkoConsultEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = this.findById('OnkoConsultEditForm').getForm();
		base_form.reset();

		if (arguments[0]['action']) {
			this.action = arguments[0]['action'];
		}

		if (arguments[0]['callback']) {
			this.returnFunc = arguments[0]['callback'];
		}

		if (arguments[0]['OnkoConsult_id']) {
			this.OnkoConsult_id = arguments[0]['OnkoConsult_id'];
		} else {
			this.OnkoConsult_id = null;
		}

		if (arguments[0]['MorbusOnko_id']) {
			this.MorbusOnko_id = arguments[0]['MorbusOnko_id'];
		} else {
			this.MorbusOnko_id = null;
		}

		if (arguments[0]['MorbusOnkoVizitPLDop_id']) {
			this.MorbusOnkoVizitPLDop_id = arguments[0]['MorbusOnkoVizitPLDop_id'];
		} else {
			this.MorbusOnkoVizitPLDop_id = null;
		}

		if (arguments[0]['MorbusOnkoDiagPLStom_id']) {
			this.MorbusOnkoDiagPLStom_id = arguments[0]['MorbusOnkoDiagPLStom_id'];
		} else {
			this.MorbusOnkoDiagPLStom_id = null;
		}

		if (arguments[0]['MorbusOnkoLeave_id']) {
			this.MorbusOnkoLeave_id = arguments[0]['MorbusOnkoLeave_id'];
		} else {
			this.MorbusOnkoLeave_id = null;
		}

		if (arguments[0]['EvnVizitPL_id']) {
			this.EvnVizitPL_id = arguments[0]['EvnVizitPL_id'];
		}

		if (arguments[0]['EvnSection_id']) {
			this.EvnSection_id = arguments[0]['EvnSection_id'];
		} else {
			this.EvnSection_id = null;
		}


		// А.И.Г. 25.11.2019 Улучшение #169863
		if (getRegionNick() == 'ufa') {
			this.rattling = false;
			win.UslugaListPanel.reset();
			win.UslugaListPanel.hide();
			win.DrugTherapySchemeListPanel.reset();
			win.DrugTherapySchemeListPanel.hide();
			win.UslugaListPanel.action = this.action;
			win.DrugTherapySchemeListPanel.action = this.action;
		}
		switch (this.action) {
			case 'add':
				this.setTitle(langs('Сведения о проведении консилиума: Добавление'));
				break;
			case 'edit':
				this.setTitle(langs('Сведения о проведении консилиума: Редактирование'));
				break;
			case 'view':
				this.setTitle(langs('Сведения о проведении консилиума: Просмотр'));
				break;
		}

		if (this.action != 'add') {
			var loadMask = new Ext.LoadMask(Ext.get('OnkoConsultEditForm'), { msg: "Загрузка..." });
			this.findById('OnkoConsultEditForm').getForm().load({
				url: '/?c=OnkoConsult&m=load',
				params: {
					OnkoConsult_id: this.OnkoConsult_id
				},
				success: function (form, action) {
					var data = action.result.data,
						healTypeField = base_form.findField('OnkoHealType_id'),
						consultResultField = base_form.findField('OnkoConsultResult_id');

					loadMask.hide();
					this.rattling = true;

					if (getRegionNick() == 'ufa') {
						var medStaffFactField = base_form.findField('MedStaffFact_id'),
							medStaffFactStore = medStaffFactField.getStore(),
							medStaffFactPIDField = base_form.findField('MedStaffFact_pid'),
							medStaffFactRIDField = base_form.findField('MedStaffFact_rid');
						//uslugaComplexField = base_form.findField('UslugaComplex_id');

						if (data.MedStaffFact_id && !medStaffFactStore.getById(data.MedStaffFact_id)) {
							medStaffFactStore.load({ params: { Lpu_id: data.MSFLpu_id } });
						}

						if (!data.MedStaffFact_id) {
							win.loadMedStaffFactStore();
						}
						//у всех один сторис
						medStaffFactStore.on('load', function (store) {
							if (store.getById(data.MedStaffFact_id))
								medStaffFactField.setValue(medStaffFactField.getValue());
							if (store.getById(data.MedStaffFact_pid))
								medStaffFactPIDField.setValue(medStaffFactPIDField.getValue());
							if (store.getById(data.MedStaffFact_rid))
								medStaffFactRIDField.setValue(medStaffFactRIDField.getValue())
						});

						win.setUslugaComplexAttributeByHealType();

						//if (data.UslugaComplex_id) uslugaComplexField.getStore().load({ params: { UslugaComplex_id: data.UslugaComplex_id } });

						// uslugaComplexField.getStore().on('load', function () {
						// 	uslugaComplexField.setValue(data.UslugaComplex_id);
						// });
					}

					if (win.action != 'view') {
						healTypeField.fireEvent('change', healTypeField);
						consultResultField.fireEvent('change', consultResultField);
					}
					if (getRegionNick() == 'ufa') {
						// А.И.Г. 03.12.2019 Улучшение #169863
						this.rattling = false;
						//Анализ типа лечения и установка фильтра на услуги !!!!!!!!!!!!!!!
						var attribType = [];
						switch (healTypeField.getFieldValue('OnkoHealType_Code')) {
							case 1:
								attribType.push('oper');
								win.UslugaListPanel.show();
								win.DrugTherapySchemeListPanel.hide();
								break;
							case 2:
								win.DrugTherapySchemeListPanel.show();
								win.UslugaListPanel.hide();
								break;
							case 3:
								attribType.push('ray', 'LuchLech');
								win.UslugaListPanel.show();
								win.DrugTherapySchemeListPanel.hide();
								break;
							case 4:
								attribType.push('ray', 'LuchLech');
								win.UslugaListPanel.show();
								win.DrugTherapySchemeListPanel.show();
								break;
							case 6:
								//attribType.push('ray', 'LuchLech');
								win.UslugaListPanel.show();
								win.DrugTherapySchemeListPanel.hide();
								break;
							default:
								if (healTypeField.getFieldValue('OnkoHealType_Code') != null) {
									win.UslugaListPanel.hide();
									win.DrugTherapySchemeListPanel.hide();
								}
								break;
						}

						if (!win.DrugTherapySchemeListPanel.hidden) {
							win.DrugTherapySchemeListPanel.setValuesDrugTherapyScheme(data.ListDrugTherapyScheme);
						}

						if (!win.UslugaListPanel.hidden) {
							win.UslugaListPanel.AttributeList = attribType;
							win.UslugaListPanel.setValuesUsluga(data.ListUsluga);
						}
					}
					
				},
				failure: function (form, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
						this.hide();
					}
				},
				scope: this
			});
		} else {
			base_form.findField('OnkoConsult_consDate').focus();
			base_form.findField('MorbusOnko_id').setValue(this.MorbusOnko_id);
			base_form.findField('MorbusOnkoVizitPLDop_id').setValue(this.MorbusOnkoVizitPLDop_id);
			base_form.findField('MorbusOnkoLeave_id').setValue(this.MorbusOnkoLeave_id);
			base_form.findField('MorbusOnkoDiagPLStom_id').setValue(this.MorbusOnkoDiagPLStom_id);
			if (getRegionNick() == 'ufa') {
				win.loadMedStaffFactStore();
			}
		}
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		if (this.action == 'view') {
			win.setDisabledFields(true);
			win.setDisableTherapyFields(true);
			base_form.findField('OnkoConsultResult_id').disable();
			this.buttons[0].disable();
		} else {
			win.setDisabledFields(false);
			base_form.findField('OnkoConsultResult_id').enable();
			this.buttons[0].enable();
		}

		if (this.EvnSection_id) {
			var params = {
				EvnSection_id: win.EvnSection_id
			};
		} else {
			var params = {
				EvnVizitPL_id: win.EvnVizitPL_id
			};
		}
		//фильтруем поле "Тип лечения" по дате окончания случая лечения, либо по текущей дате если лечение не окончено.
		Ext.Ajax.request({
			url: '/?c=EvnPL&m=getLastVizitDT',
			params: params,
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				var endTreatDate;

				if (!result.endTreatDate) {
					var dateStr = getGlobalOptions().date;
					var d = dateStr.substr(0, 2);
					var m = dateStr.substr(3, 2) - 1;
					var y = dateStr.substr(6);
					endTreatDate = new Date(y, m, d);
				} else {
					endTreatDate = Date.parseDate(result.endTreatDate, 'd.m.Y');
					endTreatDate.setHours(0, 0, 0, 0); // фильтруем только по дате
				}
				win.endTreatDate = endTreatDate;
				base_form.findField('OnkoHealType_id').getStore().clearFilter();
				base_form.findField('OnkoHealType_id').getStore().filterBy(function (rec) {
					var uslugaBegDate,
						uslugaEndDate,
						dateArr;
					if (rec.get('OnkoHealType_begDT')) {
						dateArr = rec.get('OnkoHealType_begDT').split('.');
						uslugaBegDate = new Date(dateArr[2], dateArr[1] - 1, dateArr[0]);

					}

					if (rec.get('OnkoHealType_endDT')) {
						dateArr = rec.get('OnkoHealType_endDT').split('.');
						uslugaEndDate = new Date(dateArr[2], dateArr[1] - 1, dateArr[0]);
					}
					return (
						(!uslugaEndDate || uslugaEndDate >= endTreatDate)
						&& (!uslugaBegDate || uslugaBegDate <= endTreatDate)
					);
				});
				base_form.findField('OnkoHealType_id').lastQuery = '';
				base_form.findField('OnkoConsultResult_id').getStore().reload();
			}
		});
	},
	doSave: function () {
		var win = this;
		var form = this.findById('OnkoConsultEditForm').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('OnkoConsultEditForm'), { msg: "Подождите, идет сохранение..." });
		var params = {};

		if (getRegionNick() == 'ufa') {
			//А.И.Г. 26.11.2019 #169863 валидация перечня услуг и схем лечения
			var UslugaListPanel = win.MainPanel.getForm().formPanel.find("refId", "UslugaListPanel")[0];
			if (!UslugaListPanel.hidden && win.UslugaListPanel.getValues().length == 0) {
				sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						width: 600,
						msg: "Не указана услуга!",
						title: ERR_INVFIELDS_TIT
					});

				return false;
			}
			params.UslugaList = Ext.util.JSON.encode(win.UslugaListPanel.getValues());

			var DrugTherapySchemeListPanel = win.MainPanel.getForm().formPanel.find("refId", "DrugTherapySchemeListPanel")[0];
			//console.log('DrugTherapySchemeListPanel=', DrugTherapySchemeListPanel);
			if (!DrugTherapySchemeListPanel.hidden && win.DrugTherapySchemeListPanel.getValues().length == 0) {
				sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						width: 600,
						msg: "Не выбрана схема лечения!",
						title: ERR_INVFIELDS_TIT
					});

				return false;
			}
			params.DrugTherapySchemeList = Ext.util.JSON.encode(win.DrugTherapySchemeListPanel.getValues());
		}

		if (!form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					win.findById('OnkoConsultEditForm').getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		//loadMask.show();
		form.submit({
			params: params,
			failure: function (result_form, action) {
				loadMask.hide();
			},
			success: function (result_form, action) {
				loadMask.hide();
				if (action.result && action.result.success) {
					win.hide();
					win.returnFunc();
				}
				else {
					Ext.Msg.alert(langs('Ошибка'), langs('При сохранении произошла ошибка'));
				}

			}.createDelegate(this)
		});
	},

	initComponent: function () {

		var
			win = this,
			region = getRegionNick();

		if (region == 'ufa') {
			this.UslugaListPanel = new sw.Promed.UslugaListPanelOnkoConsult_({
				win: this,
				width: 600,
				buttonAlign: 'left',
				objectSpr: 'SwUslugaComplexNewCombo',
				refId: 'UslugaListPanel',
				id: 'UslugaList',
				hiddenName: 'UslugaListPanel',
				buttonLeftMargin: 150,
				labelWidth: 150,
				fieldWidth: 400,
				style: 'background: transparent; margin: 0; padding: 0;',
				fieldLabel: 'Услуга',
				action: '',
				onChange: function () {
				}
			});
	
			this.DrugTherapySchemeListPanel = new sw.Promed.UslugaListPanelOnkoConsult_({
				win: this,
				width: 600,
				buttonAlign: 'left',
				objectSpr: 'SwCommonSprCombo',//swcommonsprcombo
				refId: 'DrugTherapySchemeListPanel',
				id: 'DrugTherapySchemeList',
				hiddenName: 'DrugTherapySchemeListPanel',
				buttonLeftMargin: 150,
				labelWidth: 150,
				fieldWidth: 400,
				style: 'background: transparent; margin: 0; padding: 0;',
				fieldLabel: 'Схема лекарственной терапии',
				onChange: function () {
				}
			});
		}
		

		var items = [{
			name: 'OnkoConsult_id',
			xtype: 'hidden'
		}, {
			name: 'MorbusOnko_id',
			xtype: 'hidden'
		}, {
			name: 'MorbusOnkoVizitPLDop_id',
			xtype: 'hidden'
		}, {
			name: 'MorbusOnkoLeave_id',
			xtype: 'hidden'
		}, {
			name: 'MorbusOnkoDiagPLStom_id',
			xtype: 'hidden'
		}, {
			fieldLabel: 'Дата проведения',
			width: 100,
			allowBlank: false,
			name: 'OnkoConsult_consDate',
			xtype: 'swdatefield',
			listeners: {
				change: function () {
					win.filterOnkoConsultResultStore();
				}
			}
		}, {
			comboSubject: 'OnkoHealType',
			fieldLabel: 'Тип лечения',
			hiddenName: 'OnkoHealType_id',
			width: 300,
			xtype: 'swcommonsprcombo',
			listeners: {
				change: function (me, newValue) {
					var baseForm = win.MainPanel.getForm();

					if (region == 'ufa') {
						var commentaryField = baseForm.findField('OnkoConsult_Commentary'),
							code = me.getCode(),
							//доступно для ввода если лекарственная противоопухолевая или химиолучевая терапия
							disable = !code.inlist([2, 4]);

						win.setDisableTherapyFields(disable);
						commentaryField.allowBlank = code != 5; ////неспецифическое лечение
						commentaryField.validate();
						win.setUslugaComplexAttributeByHealType();
					}

					if (region == 'ekb') {
						var code = me.getCode();
						baseForm.findField('DrugTherapyScheme_id').setAllowBlank(code != 2 && code != 4);
					}
				}
			}
		}, {
			allowBlank: false,
			comboSubject: 'OnkoConsultResult',
			fieldLabel: 'Результат проведения',
			hiddenName: 'OnkoConsultResult_id',
			lastQuery: '',
			width: 300,
			xtype: 'swcommonsprcombo',
			moreFields: [{ name: 'OnkoConsultResult_begDate', type: 'date' }, { name: 'OnkoConsultResult_endDate', type: 'date' }],
			onLoadStore: function () {
				win.filterOnkoConsultResultStore();
			},
			listeners: {
				change: function (cmp, rec) {
					if (region == 'kz') return;

					var disable = cmp.getCode() === 0 || cmp.getCode() == 4;

					win.setDisabledFields(disable);

					//А.И.Г. 20.12.2019 #169863 валидация перечня услуг и схем лечения
					if (region == 'ufa') {
						if (disable) {
							//Убираем обнуляем
							win.UslugaListPanel.hide();
							win.DrugTherapySchemeListPanel.hide();
							win.UslugaListPanel.clear();
							win.DrugTherapySchemeListPanel.clear();

						}
						else {
							//открываем окна
							win.setUslugaComplexAttributeByHealType();
						}
					}

				}
			}
		}];

		// if (region == 'ufa') {
		// 	items.push({
		// 		fieldLabel: langs('Услуга'),
		// 		hiddenName: 'UslugaComplex_id',
		// 		xtype: 'swuslugacomplexnewcombo',
		// 		disabled: region != 'ufa',
		// 		width: 300,
		// 		listWidth: 700
		// 	});
		// }

		if (region == 'ufa') {
			items.push(this.UslugaListPanel);
		}

		//if (region.inlist(['ekb', 'ufa'])) {
		if (region.inlist(['ekb'])) {
			items.push({
				fieldLabel: 'Схема лекарственной терапии',
				hiddenName: 'DrugTherapyScheme_id',
				xtype: 'swcommonsprcombo',
				comboSubject: 'DrugTherapyScheme',
				disabled: true,
				allowBlank: region != 'ufa',
				editable: true,
				width: 300,
				listWidth: 700
			});
		}

		if (region == 'ufa') {
			items.push(this.DrugTherapySchemeListPanel);
		}

		if (region == 'ekb') {
			items.push({
				fieldLabel: 'Планируемая дата начала лечения',
				width: 100,
				name: 'OnkoConsult_PlanDT',
				xtype: 'swdatefield'
			});
		}

		if (region == 'ufa') {
			items.push({
				fieldLabel: 'Вид химиотерапии',
				hiddenName: 'OnkoChemForm_id',
				xtype: 'swcommonsprcombo',
				comboSubject: 'OnkoChemForm',
				disabled: true,
				allowBlank: false,
				width: 300
			});

			items.push({
				fieldLabel: 'Планируемое количество курсов',
				name: 'OnkoConsult_CouseCount',
				xtype: 'numberfield',
				disabled: true,
				allowBlank: false,
				default: 1,
				minValue: 1,
				maxValue: 99,
				width: 30
			});

			items.push({
				fieldLabel: 'Комментарий',
				name: 'OnkoConsult_Commentary',
				xtype: 'textarea',
				allowBlank: true,
				width: 300,
				maxLength: 500,
				height: 70

			});

			items.push({
				fieldLabel: 'МО проведения лечения',
				hiddenName: 'Lpu_hid',
				xtype: 'swlpuopenedcombo',
				disabled: region != 'ufa',
				allowBlank: true,
				width: 300
			});

			items.push({
				xtype: 'hidden',
				name: 'MSFLpu_id',
				disabled: region != 'ufa'
			});

			items.push({
				fieldLabel: 'Врач 1',
				hiddenName: 'MedStaffFact_id',
				xtype: 'swmedstafffactbylpucombo',
				disabled: region != 'ufa',
				allowBlank: false,
				editable: true,
				anchor: '',
				width: 300
			});

			items.push({
				fieldLabel: 'Врач 2',
				hiddenName: 'MedStaffFact_pid',
				xtype: 'swmedstafffactbylpucombo',
				disabled: region != 'ufa',
				allowBlank: false,
				editable: true,
				anchor: '',
				width: 300
			});

			items.push({
				fieldLabel: 'Врач 3',
				hiddenName: 'MedStaffFact_rid',
				xtype: 'swmedstafffactbylpucombo',
				disabled: region != 'ufa',
				allowBlank: false,
				editable: true,
				anchor: '',
				width: 300
			});
		}

		this.MainPanel = new Ext.form.FormPanel({
			id: 'OnkoConsultEditForm',
			border: false,
			frame: true,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 150,
			items: items,
			reader: new Ext.data.JsonReader({}, [
				{ name: 'OnkoConsult_id' },
				{ name: 'MorbusOnko_id' },
				{ name: 'MorbusOnkoVizitPLDop_id' },
				{ name: 'MorbusOnkoLeave_id' },
				{ name: 'MorbusOnkoDiagPLStom_id' },
				{ name: 'OnkoConsult_consDate' },
				{ name: 'OnkoConsult_PlanDT' },
				{ name: 'OnkoHealType_id' },
				{ name: 'OnkoConsultResult_id' },
				{ name: 'UslugaComplex_id' },
				{ name: 'DrugTherapyScheme_id' },
				{ name: 'OnkoChemForm_id' },
				{ name: 'OnkoConsult_CouseCount' },
				{ name: 'OnkoConsult_Commentary' },
				{ name: 'Lpu_hid' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedStaffFact_pid' },
				{ name: 'MedStaffFact_rid' },
				{ name: 'MSFLpu_id' },
				{ name: 'ListUsluga' },
				{ name: 'ListDrugTherapyScheme' }
			]),
			url: '/?c=OnkoConsult&m=save'
		});

		Ext.apply(this,
			{
				xtype: 'panel',
				border: false,
				items: [this.MainPanel],
				buttons:
					[{
						text: langs('Сохранить'),
						iconCls: 'save16',
						handler: function () {
							this.doSave();
						}.createDelegate(this)
					},
					{
						text: '-'
					},
					{
						text: BTN_FRMHELP,
						iconCls: 'help16',
						handler: function (button, event) {
							ShowHelp(this.title);
						}.createDelegate(this)
					},
					{
						text: BTN_FRMCANCEL,
						iconCls: 'cancel16',
						handler: function () {
							this.hide();
						}.createDelegate(this)
					}],
				keys: [{
					alt: true,
					fn: function (inp, e) {
						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						if (e.getKey() == Ext.EventObject.J) {
							this.hide();
							return false;
						}

						if (e.getKey() == Ext.EventObject.C) {
							this.doSave();
							return false;
						}
					},
					key: [Ext.EventObject.J, Ext.EventObject.C],
					scope: this,
					stopEvent: false
				}]
			});
		sw.Promed.swOnkoConsultEditWindow.superclass.initComponent.apply(this, arguments);
	},
	filterOnkoConsultResultStore: function () {
		var win = this,
			base_form = win.findById('OnkoConsultEditForm').getForm(),
			date = base_form.findField('OnkoConsult_consDate').getValue(),
			store = base_form.findField('OnkoConsultResult_id').getStore();

		if (getRegionNick() == 'kz') return;

		if (Ext.isEmpty(date)) {
			date = win.endTreatDate;
		}

		store.filterBy(function (rec) {
			return (!rec.get('OnkoConsultResult_begDate') || rec.get('OnkoConsultResult_begDate') <= date) &&
				(!rec.get('OnkoConsultResult_endDate') || rec.get('OnkoConsultResult_endDate') >= date);
		})

	},
	setUslugaComplexAttributeByHealType: function () {
		var win = this,
			base_form = win.MainPanel.getForm(),
			healTypeField = base_form.findField('OnkoHealType_id');
		uslugaComplexField = base_form.findField('UslugaComplex_id');

		//А.И.Г. 25.11.2019 #169863
		//var UslugaListPanel = win.MainPanel.getForm().formPanel.find("refId", "UslugaListPanel");
		//var DrugTherapySchemeListPanel = win.MainPanel.getForm().formPanel.find("refId", "DrugTherapySchemeListPanel");

		var attribType = [];

		switch (healTypeField.getFieldValue('OnkoHealType_Code')) {
			case 1:
				attribType.push('oper');
				win.UslugaListPanel.show();
				win.DrugTherapySchemeListPanel.hide();
				break;
			case 2:
				win.DrugTherapySchemeListPanel.show();
				win.UslugaListPanel.hide();
				break;
			case 3:
				attribType.push('ray', 'LuchLech');
				win.UslugaListPanel.show();
				win.DrugTherapySchemeListPanel.hide();
				break;
			case 4:
				attribType.push('ray', 'LuchLech');
				win.UslugaListPanel.show();
				win.DrugTherapySchemeListPanel.show();
				break;
			case 6:
					//attribType.push('ray', 'LuchLech');
					win.UslugaListPanel.show();
					win.DrugTherapySchemeListPanel.hide();
					break;
			default:
				if (healTypeField.getFieldValue('OnkoHealType_Code') != null) {
					win.UslugaListPanel.hide();
					win.DrugTherapySchemeListPanel.hide();
					//UslugaListPanel[0].hide();
				}
				break;
		}
		//uslugaComplexField.setAllowedUslugaComplexAttributeList(attribType);

		win.UslugaListPanel.AttributeList = attribType;
		if (healTypeField.getFieldValue('OnkoHealType_Code') != null && !win.rattling) {
			win.UslugaListPanel.clear();
			win.DrugTherapySchemeListPanel.clear();
		}
	},
	setDisabledFields: function (disable) {
		var win = this,
			form = win.MainPanel.getForm(),
			fields = ['OnkoHealType_id', 'OnkoConsult_consDate'];

		if (getRegionNick() == 'ufa') {
			fields.push('OnkoConsult_Commentary');
			fields.push('Lpu_hid');
			fields.push('MedStaffFact_id');
			fields.push('MedStaffFact_pid');
			fields.push('MedStaffFact_rid');
			//fields.push('UslugaComplex_id');
		}

		if (getRegionNick() == 'ekb') {
			fields.push('DrugTherapyScheme_id');
			fields.push('OnkoConsult_PlanDT');
		}

		fields.forEach(function (fieldName) {
			var field = form.findField(fieldName);
			field.setDisabled(disable);
			if (disable)
				field.setValue(null);
			if (win.action != 'view')
				field.fireEvent('change', field);
		})
	},
	setDisableTherapyFields: function (disable) {
		var form = this.MainPanel.getForm();
		var fields = ['DrugTherapyScheme_id', 'OnkoChemForm_id', 'OnkoConsult_CouseCount'];
		if (getRegionNick() == 'ufa')
			fields = ['OnkoChemForm_id', 'OnkoConsult_CouseCount'];

		fields.forEach(function (fieldName) {
			var field = form.findField(fieldName);
			field.setDisabled(disable);
			if (disable) field.setValue(null);
		});
	},
	loadMedStaffFactStore: function () {
		var form = this.MainPanel.getForm(),
			medStaffFactStore = form.findField('MedStaffFact_id').getStore(),
			lpu_id = getGlobalOptions().lpu_id,
			opt = medStaffFactStore.lastOptions;
		if (!(opt && opt.params && opt.params.Lpu_id == lpu_id))
			medStaffFactStore.load({ params: { Lpu_id: lpu_id } });
	},
	refresh: function () {
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.close();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];
	},
	listeners: {
		'hide': function () {
			this.refresh();
		}
	}
});

//А.И.Г. 26.11.2019 #169863
/**
* Панель со списком услуг для окна проведения консилиума (регистр онкологии)
*/
sw.Promed.UslugaListPanelOnkoConsult_ = Ext.extend(Ext.Panel, {
	win: null,
	refId: '',
	firstTabIndex: null,
	objectSpr: null,
	loadParams: null,
	baseParams: null,
	PrescriptionType_Code: null,
	disabledAddDiag: false,
	autoHeight: true,
	bodyBorder: false,
	border: false,
	frame: false,
	header: false,
	labelAlign: 'right',
	labelWidth: 100,
	layout: 'form',
	lastItemsIndex: 0,
	buttonAlign: 'left',
	fieldLabel: 'Диагноз',
	disabled: false,
	fieldWidth: 400,
	width: 900,
	showOsl: false,
	AttributeList: [],
	action: '',
	deleteCombo: function (index) {
		var win = this;
		var combo = this.findById(this.id + 'Diag_id' + index).items.items[0].items.items[0];
		if (this.disabled) return false;
		if (win.action == 'edit' && !Ext.isEmpty(combo.getValue())) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						if (this.findById(this.id + 'Diag_id' + index).isFirst) {
							var combo = this.findById(this.id + 'Diag_id' + index).items.items[0].items.items[0];
				
							var next_item = new Object();
							var next_item_exists = false;
							this.items.each(function (cur_item) {
								if (!cur_item.isFirst && !next_item_exists) {
									next_item = cur_item;
									next_item_exists = true;
								}
							});
							if (!next_item_exists) {
								combo.clearValue();
							}
							else {
								next_item.isFirst = true;
								next_item.items.items[0].items.items[0].labelSeparator = ':';
								next_item.items.items[0].items.items[0].setFieldLabel(this.fieldLabel);
								this.remove(this.findById(this.id + 'Diag_id' + index), true);
							}
						} else {
							this.remove(this.findById(this.id + 'Diag_id' + index), true);
						}
						return;
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Вы хотите удалить запись?'),
				title: langs('Подтверждение')
			});
		}
		else
		{
			if (this.findById(this.id + 'Diag_id' + index).isFirst) {
				var combo = this.findById(this.id + 'Diag_id' + index).items.items[0].items.items[0];
	
				var next_item = new Object();
				var next_item_exists = false;
				this.items.each(function (cur_item) {
					if (!cur_item.isFirst && !next_item_exists) {
						next_item = cur_item;
						next_item_exists = true;
					}
				});
				if (!next_item_exists) {
					combo.clearValue();
				}
				else {
					next_item.isFirst = true;
					next_item.items.items[0].items.items[0].labelSeparator = ':';
					next_item.items.items[0].items.items[0].setFieldLabel(this.fieldLabel);
					this.remove(this.findById(this.id + 'Diag_id' + index), true);
				}
			} else {
				this.remove(this.findById(this.id + 'Diag_id' + index), true);
			}
		}

		

	},
	getValues: function () {
		var res = new Array();
		this.items.each(function (item, index, length) {
			var res_it = new Array();
			var combo = item.items.items[0].items.items[0];
			if (combo.getValue()) {
				//res_it.push(combo.getValue());
				//res_it.push(combo_descr.getValue());
				//res_it.push(combo_sub);
				res.push(combo.getValue());
			}
		}, this);
		return res;
	},
	getFirstCombo: function () {
		return this.firstCombo;
	},
	reset: function (callback) {
		this.items.each(function (item, index, length) {
			this.remove(item, true);
		}, this);
		return this.addCombo(true);
	},
	clear: function () {
		this.items.each(function (item, index, length) {
			if (item.isFirst != true) {
				this.remove(this.findById(item.id), true);
			}
			else {
				var combo = this.findById(item.id).items.items[0].items.items[0];
				combo.clearValue();
				if(this.AttributeList.length > 0)
				{
					combo.setAllowedUslugaComplexAttributeList(this.AttributeList);
				}
				
			}
		}, this);
	},

	setValuesUsluga: function (values_arr) {
		if (!values_arr || !Ext.isArray(values_arr))
			values_arr = [null];

		win = this;
		var combo = this.findById(this.id + 'Diag_id0').items.items[0].items.items[0];

		combo.setAllowedUslugaComplexAttributeList(this.AttributeList);

		combo.getStore().load({
			callback: function () {
				var uslugaStore = combo.getStore();
				combo.setValue(values_arr[0]);
				for (var i = 0; i < values_arr.length; i++) {
					//console.log("AttributeList=", win.AttributeList);

					if (i > 0) {

						win.addCombo(false);
						var comboNew = win.findById(win.id + 'Diag_id' + i).items.items[0].items.items[0];
						comboNew.store = uslugaStore;
						comboNew.setValue(values_arr[i]);
					};
				}

				if (win.action == 'view') {
					win.disable();
				}

			}
		});
	},
	setValuesDrugTherapyScheme: function (values_arr) {
		if (!values_arr || !Ext.isArray(values_arr))
			values_arr = [null];
		win = this;
		var combo = this.findById(this.id + 'Diag_id0').items.items[0].items.items[0];
		combo.setValue(values_arr[0]);

		for (var i = 0; i < values_arr.length; i++) {
			if (i > 0) {

				win.addCombo(false);
				var comboNew = win.findById(win.id + 'Diag_id' + i).items.items[0].items.items[0];
				comboNew.setValue(values_arr[i]);
			};
		};

		if (win.action == 'view') {
			win.disable();
		}
	},
	disable: function () {

		this.buttons[0].setDisabled(true);
		this.items.each(function (item, index, length) {
			 item.items.items[1].disable();
			var combo = item.items.items[0].items.items[0];
			combo.disable();
		}, this);
	},
	// enable: function () {
	// 	this.disabled = false;
	// 	this.items.each(function (item, index, length) {
	// 		var combo = item.items.items[0].items.items[0];
	// 		combo.enable();
	// 	}, this);
	// },
	onChange: Ext.emptyFn,
	addCombo: function (is_first) {
		if (this.disabled) return false;
		var panel = this;
		if (is_first)
			this.lastItemsIndex = 0;
		else
			this.lastItemsIndex++;

		var comboSubject = '';
		if (this.objectSpr == 'SwCommonSprCombo') {
			comboSubject = 'DrugTherapyScheme';
		}

		var conf_combo = {
			allowBlank: true,
			value: null,
			bodyStyle: 'padding-left: 0 !important;',
			listWidth: 700,
			/* onChange: function (combo, value) {
				combo_descr = combo.ownerCt.ownerCt.items.items[1].items.items[0];
				combo_osl = panel.showOsl ? combo.ownerCt.ownerCt.items.items[2].items.items[0] : null;
				if (Ext.isEmpty(value)) {
					combo_descr.setValue('');
					combo_descr.disable();
					Ext.QuickTips.register({
						target: combo_decr.getEl(),
						text: '',
						enabled: false,
						showDelay: 5,
						trackMouse: true,
						autoShow: true
					});
					if (panel.showOsl) {
						combo_osl.reset();
						combo_osl.disable();
					}
				}
				else {
					combo_descr.enable();
					if (panel.showOsl) {
						combo_osl.enable();
					}
				}
				panel.onChange();
			}, */

			//comboSubject: 'DrugTherapyScheme',
			comboSubject: comboSubject,
			fieldLabel: is_first ? this.fieldLabel : null,
			labelSeparator: is_first ? ':' : '',
			hiddenName: 'Diag_id' + this.lastItemsIndex,
			width: 300
		};
		if (this.firstTabIndex) {
			conf_combo.tabIndex = this.firstTabIndex + this.lastItemsIndex;
		}
		conf_combo.onTrigger2Click = function () {
			if (this.disabled) {
				return;
			}
		};

		if (this.objectSpr == 'SwUslugaComplexNewCombo') {
			var combo = new sw.Promed.SwUslugaComplexNewCombo(conf_combo);
		}
		if (this.objectSpr == 'SwCommonSprCombo') {
			var combo = new sw.Promed.SwCommonSprCombo(conf_combo);
			combo.comboSubject = 'DrugTherapyScheme';
			combo.store.load();
		}

		var cp = new Ext.Panel({
			id: this.id + 'Diag_id' + this.lastItemsIndex,
			layout: 'column',
			//width: this.width,
			//width: 800,
			border: false,
			autoWidth: true,
			hideLabel: false,
			isFirst: is_first,
			defaults: {
				border: false,
				bodyStyle: 'background: transparent;'
			},
			items: [
				new Ext.Panel({
					layout: 'form',
					labelPad: this.labelAlign == 'top' ? 0 : 5,
					labelWidth: this.labelAlign == 'top' ? 1 : this.labelWidth,
					width: 460,
					items: [combo]
					//items: items12
				}),

				new Ext.Panel({
					height: 25,
					width: 50,
					style: this.labelAlign == 'top' ? 'margin: 20px 10px 0;' : 'margin: 2px 10px 0;',
					html: '<a href="#" onclick="Ext.getCmp(\'' + this.id + '\').deleteCombo(\'' + this.lastItemsIndex + '\');">Удалить</a>'
				}),

				// new Ext.Panel({
				// 	height: 25,
				// 	width: 100,
				// 	style: this.labelAlign == 'top' ? 'margin: 20px 10px 0;' : 'margin: 2px 10px 0;',
				// 	html: '<a href="#" onclick="Ext.getCmp(\'' + this.id + '\').forexp(\'' + this.lastItemsIndex + '\');">Изменить</a>'
				// })
			]
		});
		var cb = this.add(cp);
		this.syncSize();
		this.doLayout();
		if (this.win) this.win.syncSize();

		if (!is_first && this.objectSpr == 'SwUslugaComplexNewCombo') {
			var combo2 = this.findById(this.id + 'Diag_id' + this.lastItemsIndex).items.items[0].items.items[0];
			combo2.setAllowedUslugaComplexAttributeList(this.AttributeList);
		}
		return cb;
	},
	forexp: function (index) {
		//alert("1234");

		var combo = this.findById(this.id + 'Diag_id' + index).items.items[0].items.items[0];

		var attribType = [];
		attribType.push('oper');

		combo.setAllowedUslugaComplexAttributeList(attribType);
	},
	initComponent: function () {
		if (!this.disabledAddDiag) {
			var conf_add_btn = new Ext.Panel({
				height: 20,
				width: 60,
				border: false,
				hiddenName: 'buttonAdd',
				style: 'margin: -10px 0 0;',
				html: '<a href="#" onclick="Ext.getCmp(\'' + this.id + '\').addCombo();">Добавить</a>'
			});

			if (this.buttonLeftMargin) {
				conf_add_btn.style += 'margin-left: ' + this.buttonLeftMargin + 'px;';
			}
			this.buttons = [conf_add_btn];
		}
		if (typeof this.win != 'object')
			this.win = false;
		if (typeof this.loadParams != 'object')
			this.loadParams = {};
		if (typeof this.baseParams != 'object')
			this.baseParams = { level: 0 };

		sw.Promed.UslugaListPanelOnkoConsult_.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swuslugalistpanelonkoconsult_', sw.Promed.UslugaListPanelOnkoConsult_);
