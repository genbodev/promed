/**
 * swLpuBuildingOfficeMedStaffLinkEditWindow - связка кабинета с местом работы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 * @author      Bykov Stanislav
 * @version     11.2017
 */
sw.Promed.swLpuBuildingOfficeMedStaffLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	id: 'swLpuBuildingOfficeMedStaffLinkEditWindow',
	layout: 'form',
	maximizable: false,
	modal: true,
	resizable: false,
	title: 'Назначение связи кабинет - место работы',
	width: 600,

	/* методы */
	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var wnd = this,
			base_form = this.FormPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});

		/* проверки */
		if ( !base_form.isValid() ) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		if ( Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue()) && Ext.isEmpty(base_form.findField('MedService_id').getValue()) ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено одно из обязательных полей. Укажите значение в поле «Служба» или «Место работы».'));
			return false;
		}
		else if ( !Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue()) && !Ext.isEmpty(base_form.findField('MedService_id').getValue()) ) {
			sw.swMsg.alert(langs('Ошибка'), 'Одновременное заполнение полей «Служба» и «Место работы» недопустимо.');
			return false;
		}

		/* сбор данных */
		var i = 0, LpuBuildingOfficeVizitTimeData = new Array(), RemovedDays = [];

		for ( i = 1; i <= 7; i++ ) {
			if ( base_form.findField('CalendarWeek_id_' + i).getValue() == true ) {
				if ( base_form.findField('LpuBuildingOfficeVizitTime_begDate_' + i).getValue() > base_form.findField('LpuBuildingOfficeVizitTime_endDate_' + i).getValue() ) {
					sw.swMsg.alert(lang['oshibka'], 'Время начала не может быть больше времени окончания рабочего периода', function() {
						base_form.findField('LpuBuildingOfficeVizitTime_begDate_' + i).focus(true, 250);
					});
					return false;
				}

				var StateChanged = true;
				if (wnd.worktime && wnd.worktime[i]) {

					var item = wnd.worktime[i];

					if (item.LpuBuildingOfficeVizitTime_begDate == base_form.findField('LpuBuildingOfficeVizitTime_begDate_' + i).getValue()
						&& item.LpuBuildingOfficeVizitTime_endDate == base_form.findField('LpuBuildingOfficeVizitTime_endDate_' + i).getValue()
					) {
						StateChanged = false;
					}
				}

				LpuBuildingOfficeVizitTimeData.push({
					CalendarWeek_id: i,
					StateChanged: StateChanged,
					LpuBuildingOfficeVizitTime_begDate: base_form.findField('LpuBuildingOfficeVizitTime_begDate_' + i).getValue(),
					LpuBuildingOfficeVizitTime_endDate: base_form.findField('LpuBuildingOfficeVizitTime_endDate_' + i).getValue()
				});
			} else {

				if (wnd.worktime && wnd.worktime[i]) {
					RemovedDays.push(i);
				}
			}
		}

		//log(LpuBuildingOfficeVizitTimeData);
		base_form.findField('LpuBuildingOfficeVizitTimeData').setValue(Ext.util.JSON.encode(LpuBuildingOfficeVizitTimeData));
		base_form.findField('LpuBuildingOfficeVizitTimeRemovedDays').setValue(Ext.util.JSON.encode(RemovedDays));

		var params = {
			LpuBuildingOffice_id: base_form.findField('LpuBuildingOffice_id').getValue()
		};

		if ( options.ignoreMedStaffFactDoubles == true ) {
			params.ignoreMedStaffFactDoubles = 1;
		}

		if ( options.ignoreLpuBuildingDoubles == true ) {
			params.ignoreLpuBuildingDoubles = 1;
		}

		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( !Ext.isEmpty(action.result.Alert_Msg) ) {
                        sw.swMsg.show({
                            buttons: Ext.Msg.YESNO,
                            fn: function(buttonId, text, obj) {
                                if ( buttonId == 'yes' ) {
									switch ( true ) {
										case (1 == action.result.Error_Code):
											options.ignoreMedStaffFactDoubles = true;
											break;
										case (2 == action.result.Error_Code):
											options.ignoreLpuBuildingDoubles = true;
											break;
									}

                                    wnd.doSave(options);
                                }
                            },
                            icon: Ext.MessageBox.QUESTION,
                            msg: action.result.Alert_Msg,
                            title: lang['vopros']
                        });
					}
					else {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
				}
			},
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					wnd.hide();
					wnd.callback();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
				}
			}
		});

		return true;
	},
	enableEdit: function(enable) {
		var
			base_form = this.FormPanel.getForm(),
			form_fields = new Array(
				'MedService_id',
				'MedStaffFact_id',
				'LpuBuildingOfficeMedStaffLink_begDate',
				'LpuBuildingOfficeMedStaffLink_endDate'
			),
			i = 0;

		for ( i = 1; i <= 7; i++ ) {
			form_fields.push('CalendarWeek_id_' + i);
		}

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	setMedServiceFilter: function() {
		var
			base_form = this.FormPanel.getForm(),
			begDate = base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').getValue(),
			endDate = base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').getValue(),
			index,
			LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue(),
			MedService_id = base_form.findField('MedService_id').getValue();

		if(!LpuBuilding_id)
			LpuBuilding_id = base_form.findField('LpuBuildingOffice_id').getFieldValue('LpuBuilding_id');

		base_form.findField('MedService_id').clearValue();

		setMedServiceGlobalStoreFilter({
			LpuBuilding_id: LpuBuilding_id,
			dateFrom: (typeof begDate == 'object' ? begDate.format('d.m.Y') : begDate),
			dateTo: (typeof endDate == 'object' ? endDate.format('d.m.Y') : endDate)
		});

		var recordList = getStoreRecords(swMedServiceGlobalStore);

		for ( var i in recordList ) {
			if ( Ext.isEmpty(recordList[i].MedService_endDT) ) {
				recordList[i].sortID = 1;
			}
			else {
				recordList[i].sortID = 2;
			}
		}

		base_form.findField('MedService_id').getStore().loadData(recordList);

		if ( !Ext.isEmpty(MedService_id) ) {
			index = base_form.findField('MedService_id').getStore().findBy(function(rec) {
				return (rec.get('MedService_id') == MedService_id);
			});

			if ( index >= 0 ) {
				base_form.findField('MedService_id').setValue(MedService_id);
			}
		}

		return true;
	},
	setMedStaffFactFilter: function() {
		var
			base_form = this.FormPanel.getForm(),
			begDate = base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').getValue(),
			endDate = base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').getValue(),
			index,
			LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue(),
			MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		
		if(!LpuBuilding_id)
			LpuBuilding_id = base_form.findField('LpuBuildingOffice_id').getFieldValue('LpuBuilding_id');

		base_form.findField('MedStaffFact_id').clearValue();

		setMedStaffFactGlobalStoreFilter({
			LpuBuilding_id: LpuBuilding_id,
			dateFrom: (typeof begDate == 'object' ? begDate.format('d.m.Y') : begDate),
			dateTo: (typeof endDate == 'object' ? endDate.format('d.m.Y') : endDate)
		});

		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		if ( !Ext.isEmpty(MedStaffFact_id) ) {
			index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == MedStaffFact_id);
			});

			if ( index >= 0 ) {
				base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
			}
		}

		return true;
	},
	show: function() {
		sw.Promed.swLpuBuildingOfficeMedStaffLinkEditWindow.superclass.show.apply(this, arguments);

		var wnd = this,
			base_form = this.FormPanel.getForm(),
			loadMask = new Ext.LoadMask(
				wnd.getEl(),{
					msg: LOAD_WAIT
				}
			);

		wnd.action = null;
		wnd.worktime = null;
		wnd.callback = Ext.emptyFn;

		if ( !arguments[0] || typeof arguments[0].formParams != 'object' ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				title: lang['oshibka'],
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				fn: function() {
					wnd.hide();
				}
			});

			return false;
		}

		wnd.setTitle("Назначение связи кабинет - место работы");

		var args = arguments[0];
		base_form.reset();

		for ( var field_name in args ) {
			log(field_name +':'+ args[field_name]);
			wnd[field_name] = args[field_name];
		}

		loadMask.show();

		base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').setMaxValue(undefined);
		base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').setMinValue(undefined);
		base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').setAllowBlank(true);
		base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').setMaxValue(undefined);
		base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').setMinValue(undefined);

		if (args.formParams.LpuBuildingOfficeVizitTimeData) {

			var worktime = Ext.util.JSON.decode(args.formParams.LpuBuildingOfficeVizitTimeData);
			wnd.worktime = [];

			worktime.forEach(function(item) {
				wnd.worktime[item.CalendarWeek_id] = item;
			})
		}

		base_form.setValues(args.formParams);

		base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').fireEvent('change', base_form.findField('LpuBuildingOfficeMedStaffLink_begDate'), base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').getValue());
		base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').fireEvent('change', base_form.findField('LpuBuildingOfficeMedStaffLink_endDate'), base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').getValue());

		for ( var i = 1; i <= 7; i++ ) {
			base_form.findField('CalendarWeek_id_' + i).fireEvent('check', base_form.findField('CalendarWeek_id_' + i), false);
		}

		base_form.findField('LpuBuildingOffice_id').getStore().load({
			callback: function() {
				switch ( wnd.action ) {
					case 'add':
						wnd.setTitle(wnd.title + ": Добавление");
						wnd.enableEdit(true);

						for ( var i = 1; i <= 5; i++ ) {
							base_form.findField('CalendarWeek_id_' + i).setValue(true);
							base_form.findField('CalendarWeek_id_' + i).fireEvent('check', base_form.findField('CalendarWeek_id_' + i), true);
						}
						break;

					case 'edit':
					case 'view':
						wnd.setTitle(wnd.title + (wnd.action == "edit" ? ": Редактирование" : ": Просмотр"));
						wnd.enableEdit(wnd.action == "edit");

						var LpuBuildingOfficeVizitTimeData = base_form.findField('LpuBuildingOfficeVizitTimeData').getValue();

						if ( !Ext.isEmpty(LpuBuildingOfficeVizitTimeData) ) {
							var
								CalendarWeek_id,
								i,
								LpuBuildingOfficeVizitTimeArray = Ext.util.JSON.decode(LpuBuildingOfficeVizitTimeData);

							for ( var i in LpuBuildingOfficeVizitTimeArray ) {
								if ( typeof LpuBuildingOfficeVizitTimeArray[i] == 'object' ) {
									CalendarWeek_id = LpuBuildingOfficeVizitTimeArray[i].CalendarWeek_id;

									base_form.findField('CalendarWeek_id_' + CalendarWeek_id).setValue(true);
									base_form.findField('LpuBuildingOfficeVizitTime_begDate_' + CalendarWeek_id).setValue(LpuBuildingOfficeVizitTimeArray[i].LpuBuildingOfficeVizitTime_begDate);
									base_form.findField('LpuBuildingOfficeVizitTime_endDate_' + CalendarWeek_id).setValue(LpuBuildingOfficeVizitTimeArray[i].LpuBuildingOfficeVizitTime_endDate);
									base_form.findField('CalendarWeek_id_' + CalendarWeek_id).fireEvent('check', base_form.findField('CalendarWeek_id_' + CalendarWeek_id), true);
								}
							}
						}

						break;
				}

				base_form.findField('LpuBuildingOffice_id').setValue(base_form.findField('LpuBuildingOffice_id').getValue());
				base_form.findField('LpuBuildingOffice_id').fireEvent('change', base_form.findField('LpuBuildingOffice_id'), base_form.findField('LpuBuildingOffice_id').getValue());

				loadMask.hide();

				if ( wnd.action == 'view' ) {
					wnd.buttons[wnd.buttons.length - 1].focus();
				}
				else {
					base_form.findField('MedStaffFact_id').focus(true, 100);
				}
			},
			params: {
				Lpu_id: getGlobalOptions().lpu_id
			}
		});
	},

	/* конструктор */
	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			//trackResetOnLoad:true,
			items: [{
				name: 'LpuBuildingOfficeMedStaffLink_id',
				xtype: 'hidden'
			}, {
				name: 'LpuBuilding_id',
				xtype: 'hidden'
			}, {
				name: 'LpuBuildingOfficeVizitTimeData',
				xtype: 'hidden'
			},{
				name: 'LpuBuildingOfficeVizitTimeRemovedDays',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				disabled: true,
				displayField: 'LpuBuildingOffice_Display',
				fieldLabel: 'Кабинет',
				hiddenName: 'LpuBuildingOffice_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var
							base_form = wnd.FormPanel.getForm(),
							index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});

						if ( index >= 0 ) {
							var record = combo.getStore().getAt(index);

							if ( !Ext.isEmpty(record.get('LpuBuildingOffice_begDate')) ) {
								base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').setMinValue(Ext.util.Format.date(record.get('LpuBuildingOffice_begDate'), 'd.m.Y'));
								base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').setMinValue(Ext.util.Format.date(record.get('LpuBuildingOffice_begDate'), 'd.m.Y'));
							}

							if ( !Ext.isEmpty(record.get('LpuBuildingOffice_endDate')) ) {
								base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').setMaxValue(Ext.util.Format.date(record.get('LpuBuildingOffice_endDate'), 'd.m.Y'));
								base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').setMaxValue(Ext.util.Format.date(record.get('LpuBuildingOffice_endDate'), 'd.m.Y'));
								base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').setAllowBlank(false);
							}
						}
					}
				},
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'LpuBuildingOffice_id', mapping: 'LpuBuildingOffice_id' },
						{ name: 'LpuBuildingOffice_Number', mapping: 'LpuBuildingOffice_Number' },
						{ name: 'LpuBuildingOffice_Name', mapping: 'LpuBuildingOffice_Name' },
						{ name: 'LpuBuildingOffice_begDate', mapping: 'LpuBuildingOffice_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'LpuBuildingOffice_endDate', mapping: 'LpuBuildingOffice_endDate', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
						{ name: 'LpuBuildingOffice_Display',
							convert: function(val,row) {
								return row.LpuBuildingOffice_Number + '. ' + row.LpuBuildingOffice_Name;
							}	
						}
					],
					key: 'LpuBuildingOffice_id',
					sortInfo: { field: 'LpuBuildingOffice_Name' },
					url:'/?c=LpuBuildingOffice&m=loadLpuBuildingOfficeCombo'
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{LpuBuildingOffice_Number}</font>&nbsp;{LpuBuildingOffice_Name}',
					'</div></tpl>'
				),
				valueField: 'LpuBuildingOffice_id',
				width: 350,
				xtype: 'swbaselocalcombo'
			}, {
				fieldLabel: 'Место работы',
				hiddenName: 'MedStaffFact_id',
				id: wnd.id + '_MedStaffFact_id',
				lastQuery: '',
				listWidth: 600,
				width: 350,
				xtype: 'swmedstafffactglobalcombo'
			}, {
				fieldLabel: 'Служба',
				hiddenName: 'MedService_id',
				id: wnd.id + '_MedService_id',
				lastQuery: '',
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<div><h3>{MedService_Name}&nbsp;</h3></div>',
					'<div style="font-size: 10px;">{[!Ext.isEmpty(values.MedService_begDT) ? "Действует с " + values.MedService_begDT:""]} {[!Ext.isEmpty(values.MedService_endDT) ? " по " + this.formatDate(values.MedService_endDT) : ""]}</div>',
					'</div></tpl>',
					{
						formatDate: function(date) {
							var fixed = (typeof date == 'object' ? Ext.util.Format.date(date, 'd.m.Y') : date);
							return fixed;
						}
					}
				),
				width: 350,
				xtype: 'swmedserviceglobalcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата начала',
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = wnd.FormPanel.getForm();

						if ( !Ext.isEmpty(newValue) ) {
							base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').setMinValue(Ext.util.Format.date(newValue, 'd.m.Y'));
						}
						else if ( !Ext.isEmpty(base_form.findField('LpuBuildingOffice_id').getFieldValue('LpuBuildingOffice_begDate')) ) {
							base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').setMinValue(Ext.util.Format.date(base_form.findField('LpuBuildingOffice_id').getFieldValue('LpuBuildingOffice_begDate'), 'd.m.Y'));
						}
						else {
							base_form.findField('LpuBuildingOfficeMedStaffLink_endDate').setMinValue(undefined);
						}

						wnd.setMedServiceFilter();
						wnd.setMedStaffFactFilter();
					}
				},
				name: 'LpuBuildingOfficeMedStaffLink_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Дата окончания',
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = wnd.FormPanel.getForm();

						if ( !Ext.isEmpty(newValue) ) {
							base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').setMaxValue(Ext.util.Format.date(newValue, 'd.m.Y'));
						}
						else if ( !Ext.isEmpty(base_form.findField('LpuBuildingOffice_id').getFieldValue('LpuBuildingOffice_endDate')) ) {
							base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').setMaxValue(Ext.util.Format.date(base_form.findField('LpuBuildingOffice_id').getFieldValue('LpuBuildingOffice_endDate'), 'd.m.Y'));
						}
						else {
							base_form.findField('LpuBuildingOfficeMedStaffLink_begDate').setMaxValue(undefined);
						}

						wnd.setMedServiceFilter();
						wnd.setMedStaffFactFilter();
					}
				},
				name: 'LpuBuildingOfficeMedStaffLink_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				autoHeight: true,
				layout: 'form',
				title: 'Время приема',
				xtype: 'fieldset',
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						defaults: {
							labelSeparator: '',
							listeners: {
								'check': function(field, checked) {
									var base_form = wnd.FormPanel.getForm();

									base_form.findField('LpuBuildingOfficeVizitTime_begDate_' + field.CalendarWeek_id).setAllowBlank(checked == false);
									base_form.findField('LpuBuildingOfficeVizitTime_endDate_' + field.CalendarWeek_id).setAllowBlank(checked == false);
									base_form.findField('LpuBuildingOfficeVizitTime_begDate_' + field.CalendarWeek_id).setDisabled(checked == false);
									base_form.findField('LpuBuildingOfficeVizitTime_endDate_' + field.CalendarWeek_id).setDisabled(checked == false);

									if ( checked == false ) {
										base_form.findField('LpuBuildingOfficeVizitTime_begDate_' + field.CalendarWeek_id).setValue('');
										base_form.findField('LpuBuildingOfficeVizitTime_endDate_' + field.CalendarWeek_id).setValue('');
									}
								}
							}
						},
						layout: 'form',
						xtype: 'panel',
						items: [{
							CalendarWeek_id: 1,
							fieldLabel: 'Понедельник',
							name: 'CalendarWeek_id_1',
							xtype: 'checkbox'
						}, {
							CalendarWeek_id: 2,
							fieldLabel: 'Вторник',
							name: 'CalendarWeek_id_2',
							xtype: 'checkbox'
						}, {
							CalendarWeek_id: 3,
							fieldLabel: 'Среда',
							name: 'CalendarWeek_id_3',
							xtype: 'checkbox'
						}, {
							CalendarWeek_id: 4,
							fieldLabel: 'Четверг',
							name: 'CalendarWeek_id_4',
							xtype: 'checkbox'
						}, {
							CalendarWeek_id: 5,
							fieldLabel: 'Пятница',
							name: 'CalendarWeek_id_5',
							xtype: 'checkbox'
						}, {
							CalendarWeek_id: 6,
							fieldLabel: 'Суббота',
							name: 'CalendarWeek_id_6',
							xtype: 'checkbox'
						}, {
							CalendarWeek_id: 7,
							fieldLabel: 'Воскресенье',
							name: 'CalendarWeek_id_7',
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						defaults: {
							labelSeparator: ''
						},
						labelWidth: 20,
						layout: 'form',
						xtype: 'panel',
						items: [{
							fieldLabel: 'с',
							name: 'LpuBuildingOfficeVizitTime_begDate_1',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'с',
							name: 'LpuBuildingOfficeVizitTime_begDate_2',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'с',
							name: 'LpuBuildingOfficeVizitTime_begDate_3',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'с',
							name: 'LpuBuildingOfficeVizitTime_begDate_4',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'с',
							name: 'LpuBuildingOfficeVizitTime_begDate_5',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'с',
							name: 'LpuBuildingOfficeVizitTime_begDate_6',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'с',
							name: 'LpuBuildingOfficeVizitTime_begDate_7',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}]
					}, {
						border: false,
						defaults: {
							labelSeparator: ''
						},
						labelWidth: 30,
						layout: 'form',
						xtype: 'panel',
						items: [{
							fieldLabel: 'по',
							name: 'LpuBuildingOfficeVizitTime_endDate_1',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'по',
							name: 'LpuBuildingOfficeVizitTime_endDate_2',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'по',
							name: 'LpuBuildingOfficeVizitTime_endDate_3',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'по',
							name: 'LpuBuildingOfficeVizitTime_endDate_4',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'по',
							name: 'LpuBuildingOfficeVizitTime_endDate_5',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'по',
							name: 'LpuBuildingOfficeVizitTime_endDate_6',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}, {
							fieldLabel: 'по',
							name: 'LpuBuildingOfficeVizitTime_endDate_7',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						},
							]
					}]
				}]
			}],
			labelAlign: 'right',
			layout: 'form',
			labelWidth: 200,
			region: 'north',
			reader: new Ext.data.JsonReader( {
				success: Ext.emptyFn
			}, [
				{ name: 'LpuBuildingOfficeMedStaffLink_id' },
				{ name: 'LpuBuildingOffice_id' },
				{ name: 'MedService_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'LpuBuildingOfficeMedStaffLink_begDate' },
				{ name: 'LpuBuildingOfficeMedStaffLink_endDate' },
				{ name: 'LpuBuildingOfficeVizitTimeData' }
			]),
			url: '/?c=LpuBuildingOfficeMedStaffLink&m=save'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					wnd.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'close16',
				handler: function() {
					wnd.hide();
				},
				text: BTN_FRMCLOSE
			}],
			items: [
				wnd.FormPanel
			]
		});

		sw.Promed.swLpuBuildingOfficeMedStaffLinkEditWindow.superclass.initComponent.apply(this, arguments);

		wnd.findById(wnd.id + '_MedService_id').addListener('select', function(combo, rec) {
			var base_form = wnd.FormPanel.getForm();

			if ( typeof rec == 'object' && !Ext.isEmpty(rec.get('MedService_id')) ) {
				base_form.findField('MedStaffFact_id').clearValue();
			}
		}.createDelegate(this));

		wnd.findById(wnd.id + '_MedStaffFact_id').addListener('select', function(combo, rec) {
			var base_form = wnd.FormPanel.getForm();

			if ( typeof rec == 'object' && !Ext.isEmpty(rec.get('MedStaffFact_id')) ) {
				base_form.findField('MedService_id').clearValue();
			}
		}.createDelegate(this));
	}
});