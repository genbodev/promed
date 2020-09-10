/**
* swEvnDirectionCviEditWindow - Направление по внешнюю лабораторию по КВИ 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

/*NO PARSE JSON*/
sw.Promed.swEvnDirectionCviEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'EvnDirectionCviEditWindow',
	layout: 'border',
	maximizable: false,
	width: 600,
	height: 580,
	modal: true,
	objectName: 'swEvnDirectionCviEditWindow',
	objectSrc: '/jscore/Forms/Common/swEvnDirectionCviEditWindow.js',	
	returnFunc: Ext.emptyFn,
	action: 'add',
	tests: {
		Smear: 'Мазок/отделяемое из носоглотки и ротоглотки',
		Blood: 'Кровь (сыворотка)',
		Sputum: 'Мокрота',
		Lavage: 'Бронхоальвеолярный лаваж',
		Aspirate: 'Аспират из трахеи',
		Autopsy: 'Аутопсийный материал'
	},
	doPrint: function() {
		var evn_id = this.MainPanel.getForm().findField('EvnDirectionCVI_id').getValue();
		
		if (!evn_id) {
			this.doSave(true);
			return false;
		}
		
		printBirt({
			'Report_FileName': 'EvnDirectionCVI_Print.rptdesign',
			'Report_Params': '&paramEvnDirectionCVI=' + evn_id,
			'Report_Format': 'pdf'
		});
	},
	processFields: function() {	
		if (this.action == 'view') return false;
		var base_form = this.MainPanel.getForm();
		
		for (var test in this.tests) {
			
			var is_disabled = !base_form.findField('EvnDirectionCVI_is'+test).getValue();
			base_form.findField('EvnDirectionCVI_'+test+'Number').setDisabled(is_disabled);
			if (is_disabled) {
				base_form.findField('EvnDirectionCVI_'+test+'Number').setValue('');
			}
			
			is_disabled = !base_form.findField('EvnDirectionCVI_'+test+'Number').getValue();
			base_form.findField('EvnDirectionCVI_'+test+'Result').setDisabled(is_disabled);
			if (is_disabled) {
				base_form.findField('EvnDirectionCVI_'+test+'Result').setValue('');
			}
		}
	},
	loadDiagCombo: function() {	
		var base_form = this.MainPanel.getForm(),
			diag_combo = base_form.findField('Diag_id'),
			diag_id = diag_combo.getValue();
		if (!!diag_id) {
			diag_combo.getStore().load({
				callback: function() {
					diag_combo.setValue(diag_id);
				},
				params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
			});
		}
	},
	show: function() {		
		sw.Promed.swEvnDirectionCviEditWindow.superclass.show.apply(this, arguments);

		var win = this,
			base_form = this.MainPanel.getForm();
			
		base_form.reset();
		
		this.EvnDirectionCVI_id = arguments[0].EvnDirectionCVI_id || null;
		this.action = arguments[0].action || 'add';
		this.returnFunc = arguments[0].callback || Ext.emptyFn;
		
		base_form.findField('CVIStatus_id').clearFilter();
		
		switch (this.action) {
			case 'add':
				this.setTitle('Направление во внешнюю лабораторию по КВИ: Добавление');
				break;
			case 'edit':
				this.setTitle('Направление во внешнюю лабораторию по КВИ: Редактирование');
				break;
			case 'view':
				this.setTitle('Направление во внешнюю лабораторию по КВИ: Просмотр');
				break;
		}

		if (this.action == 'add') {
			this.enableEdit(true);
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.setValues(arguments[0].formParams);
			base_form.findField('MedPersonal_tid').getStore().load({
				params: {Lpu_id: getGlobalOptions().lpu_id}
			});
			var medpersonal_id = base_form.findField('MedPersonal_id').getValue();
			base_form.findField('MedPersonal_id').getStore().load({
				params: {Lpu_id: base_form.findField('Lpu_id').getValue()},
				callback: function () {
					if (!!medpersonal_id) {
						base_form.findField('MedPersonal_id').setValue(medpersonal_id);
					}
				}
			});
			win.processFields();

			if (getRegionNick() != 'kz') {
				win.loadDiagCombo();
			} else {
				base_form.findField('Diag_id').clearValue();
				base_form.findField('MedPersonal_id').clearValue();
				base_form.findField('MedStaffFact_id').clearValue();
				
				setMedStaffFactGlobalStoreFilter({
					Lpu_id: getGlobalOptions().lpu_id,
				});
				base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
				
				Ext.Ajax.request({
					success: function(response) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						
						if (response_obj.success) {
							base_form.findField('EvnDirectionCVILink_Address').setValue(response_obj.Address_Address);
							base_form.findField('EvnDirectionCVILink_Phone').setValue(response_obj.PersonPhone_Phone);
							base_form.findField('EvnDirectionCVILink_WorkPlace').setValue(response_obj.Org_Name);
						}
					},
					params: {'Person_id': base_form.findField('Person_id').getValue()},
					url: '?c=EvnDirectionCVI&m=getPersonAddressPhone'
				});
			}
			win.refreshFieldsVisibility();
			base_form.findField('EvnDirectionCVI_RegNumber').focus(true, 100);
		} else {
			this.enableEdit(this.action != 'view');
			var loadMask = new Ext.LoadMask(Ext.get('EvnDirectionCVIEditForm'), { msg: LOAD_WAIT });
			loadMask.show();
			win.MainPanel.getForm().load({
				url: '/?c=EvnDirectionCVI&m=load',
				params: {
					EvnDirectionCVI_id: win.EvnDirectionCVI_id
				},
				success: function (form, action) {
					var response_obj = Ext.util.JSON.decode(action.response.responseText);
					
					if (response_obj[0].EvnDirectionCVILink_lisIsSuccess && response_obj[0].EvnDirectionCVILink_lisIsSuccess == 2){ 
						win.enableEdit(response_obj[0].EvnDirectionCVILink_lisIsSuccess == 1);
						win.setTitle('Направление во внешнюю лабораторию по КВИ: Просмотр');
					}
					
					loadMask.hide();
					base_form.findField('EvnDirectionCVI_RegNumber').focus(true, 100);
					win.processFields();
					
					var medpersonal_tid = base_form.findField('MedPersonal_tid').getValue();
					base_form.findField('MedPersonal_tid').getStore().load({
						params: {Lpu_id: getGlobalOptions().lpu_id},
						callback: function () {
							if (!!medpersonal_tid) {
								base_form.findField('MedPersonal_tid').setValue(medpersonal_tid);
							}
						}
					});
					var medpersonal_id = base_form.findField('MedPersonal_id').getValue();
					base_form.findField('MedPersonal_id').getStore().load({
						params: {Lpu_id: base_form.findField('Lpu_id').getValue()},
						callback: function () {
							if (!!medpersonal_id) {
								base_form.findField('MedPersonal_id').setValue(medpersonal_id);
							}
						}
					});
					
					if (getRegionNick() != 'kz') {
						win.loadDiagCombo();
					} else {
						base_form.findField('CVIPurposeSurvey_id').fireEvent('change',base_form.findField('CVIPurposeSurvey_id'),base_form.findField('CVIPurposeSurvey_id').getValue());
						
						setMedStaffFactGlobalStoreFilter({
							Lpu_id: getGlobalOptions().lpu_id,
							medPersonalIdList: [medpersonal_tid]
						});
						base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						
						var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
							return rec.get('MedStaffFact_id') == base_form.findField('MedStaffFact_id').getValue();
						});
						
						if (index != -1)
							base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
					}
					
					win.refreshFieldsVisibility();
				},
				failure: function (form, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
						win.hide();
					}
				}
			});	
		}
	},
	doSave: function(print) {
		var win = this,
			loadMask = new Ext.LoadMask(Ext.get('EvnDirectionCVIEditForm'), { msg: LOAD_WAIT_SAVE }),
			base_form = win.MainPanel.getForm(),
			params = {};
		
		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var isTests = false;
		var isNumbers = false;
		for (var test in this.tests) {
			if (!!base_form.findField('EvnDirectionCVI_is'+test).getValue()) {
				isTests = true;
			}
			if (!!base_form.findField('EvnDirectionCVI_'+test+'Number').getValue()) {
				isNumbers = true;
			}
		}
		
		if (!isTests && getRegionNick() != 'kz') {
			sw.swMsg.alert(langs('Ошибка'), 'Сохранение невозможно. Укажите хотя бы один биоматериал, который необходимо направить в лабораторию');
			return false;
		}
		
		if (!!base_form.findField('EvnDirectionCVI_takeDate').getValue() && !isNumbers && getRegionNick() != 'kz') {
			sw.swMsg.alert(langs('Ошибка'), 'Сохранение невозможно. Укажите хотя бы один номер взятого образца');
			return false;
		}
		
		if (
			!!base_form.findField('EvnDirectionCVI_takeDate').getValue() && 
			base_form.findField('EvnDirectionCVI_takeDate').getValue() < base_form.findField('EvnDirectionCVI_setDate').getValue()
		) {
			sw.swMsg.alert(langs('Ошибка'), 'Сохранение невозможно. Дата взятия образца не может быть раньше, чем дата заболевания');
			return false;
		}

		if (base_form.findField('Diag_id').disabled) {
			params.Diag_id = base_form.findField('Diag_id').getValue();
		}

		if (base_form.findField('EvnDirectionCVI_setDate').disabled) {
			params.EvnDirectionCVI_setDate = Ext.util.Format.date(base_form.findField('EvnDirectionCVI_setDate').getValue(), 'd.m.Y');
		}

		if (base_form.findField('MedPersonal_id').disabled) {
			params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		}

		loadMask.show();		
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result && action.result.success) {
					base_form.findField('EvnDirectionCVI_id').setValue(action.result.EvnDirectionCVI_id);
					win.returnFunc({ evnDirectionData: {
						EvnDirection_id: action.result.EvnDirectionCVI_id, 
						EvnDirection_pid: base_form.findField('EvnDirectionCVI_pid').getValue()
					}});
					if (!!print) {
						win.doPrint();
					} else {
						win.hide();
					}
				}	
			}
		});
	},

	refreshFieldsVisibility: function() {
		
		if (getRegionNick() == 'kz') return;
		
		var win = this,
			base_form = win.MainPanel.getForm();
			
		if (this.action == 'view') return;
		
		var fromEvn = !!base_form.findField('EvnDirectionCVI_pid').getValue() 
			&& base_form.findField('EvnDirectionCVI_pid').getValue() != base_form.findField('EvnDirectionCVI_id').getValue();
		var isTakeDate = !!base_form.findField('EvnDirectionCVI_takeDate').getValue();
		var isSendDate = !!base_form.findField('EvnDirectionCVI_sendDate').getValue();
		var isMpTid = !!base_form.findField('MedPersonal_tid').getValue();
		var isLab = !!base_form.findField('EvnDirectionCVI_Lab').getValue();
		var isTests = false;
		
		for (var test in this.tests) {
			if (!!base_form.findField('EvnDirectionCVI_'+test+'Number').getValue()) {
				isTests = true;
			}
		}
			
		base_form.findField('EvnDirectionCVI_Lab').setAllowBlank(!isSendDate);
				
		base_form.findField('Diag_id').setDisabled(fromEvn);
		base_form.findField('Diag_id').setAllowBlank(fromEvn);
				
		base_form.findField('EvnDirectionCVI_setDate').setDisabled(fromEvn);
		base_form.findField('EvnDirectionCVI_setDate').setAllowBlank(fromEvn);
		
		base_form.findField('MedPersonal_id').setDisabled(fromEvn);
		base_form.findField('MedPersonal_id').setAllowBlank(fromEvn);
		
		base_form.findField('EvnDirectionCVI_takeDate').setAllowBlank(!isMpTid);
		base_form.findField('EvnDirectionCVI_takeTime').setAllowBlank(!isTakeDate);
		
		base_form.findField('EvnDirectionCVI_sendDate').setAllowBlank(!isLab);
		base_form.findField('EvnDirectionCVI_sendTime').setAllowBlank(!isSendDate);
		
		base_form.findField('MedPersonal_tid').setAllowBlank(!isTakeDate && !isTests);
	},

	initComponent: function() {
	
		var win = this;
		
		this.TestList = new Ext.form.FieldSet({
			title: 'Лабораторный биоматериал',
			height: 220,
			hidden: getRegionNick() == 'kz',
			style: 'margin-top: 15px;',
			items: [{
				xtype: 'panel',
				layout: 'column',
				border: false,
				style: 'font-size: 1.1em; margin-bottom: 0.7em',
				defaults: {
					xtype: 'panel',
					layout: 'form',
					border: false,
				},
				items: [{
					width: 310,
					items: [{
						html: '<br>Биоматериал'
					}]
				}, {
					width: 120,
					items: [{
						html: '<br>Номер образца'
					}]
				}, {
					width: 100,
					items: [{
						html: 'Результат исследования'
					}]
				}]
			}]
		});
		
		this.MainPanel = new Ext.form.FormPanel({
			id:'EvnDirectionCVIEditForm',
			border: false,
			frame: true,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 220,
			items: [{
				name: 'EvnDirectionCVI_id',
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionCVI_pid',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				fieldLabel : 'Регистрационный номер',
				width: 100,
				name: 'EvnDirectionCVI_RegNumber',
				xtype: 'textfield',
				maskRe: /\d/,
				allowBlank: getRegionNick() != 'kz',
				autoCreate: {tag: "input", maxLength: 12, autocomplete: "off"}
			}, {
				fieldLabel : (getRegionNick() != 'kz')?'Контактный с':'Место работы',
				width: 300,
				name: (getRegionNick() != 'kz')?'EvnDirectionCVI_Contact':'EvnDirectionCVILink_WorkPlace',
				xtype: 'textfield',
				autoCreate: {tag: "input", maxLength: 80, autocomplete: "off"}
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					allowBlank: getRegionNick() != 'kz',
					comboSubject: 'CVIBiomaterial',
					fieldLabel: 'Биоматериал',
					prefix: 'r101_',
					width: 300,
					xtype: 'swcommonsprcombo'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					allowBlank: getRegionNick() != 'kz',
					comboSubject: 'CVISampleStatus',
					fieldLabel: 'Статус отбора пробы',
					prefix: 'r101_',
					width: 300,
					xtype: 'swcommonsprcombo'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() == 'kz',
				items: [{
					fieldLabel: 'Лаборатория, куда сдали образцы',
					width: 300,
					name: 'EvnDirectionCVI_Lab',
					xtype: 'textfield',
					autoCreate: {tag: "input", maxLength: 120, autocomplete: "off"},
					listeners: {
						change: function () {
							win.refreshFieldsVisibility()
						}
					}
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() == 'kz',
				items: [{
					fieldLabel: 'Предварительный диагноз',
					width: 300,
					name: 'Diag_id',
					disabled: true,
					xtype: 'swdiagcombo'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() == 'kz',
				items: [{
					fieldLabel: 'Дата заболевания',
					width: 100,
					name: 'EvnDirectionCVI_setDate',
					disabled: true,
					xtype: 'swdatefield'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() == 'kz',
				items: [{
					fieldLabel: 'Направивший врач',
					hiddenName: 'MedPersonal_id',
					allowBlank: true,
					width: 300,
					listWidth: 400,
					xtype: 'swmedpersonalcombo'
				}]
			}, {
				xtype: 'panel',
				layout: 'column',
				border: false,
				items: [{
					xtype: 'panel',
					layout: 'form',
					labelWidth: 220,
					border: false,
					items: [{
						fieldLabel: (getRegionNick() != 'kz')?'Дата взятия образца':'Дата взятия биоматериала',
						allowBlank: getRegionNick() != 'kz',
						width: 100,
						name: 'EvnDirectionCVI_takeDate',
						maxValue: new Date(),
						xtype: 'swdatefield',
						listeners: {
							change: function() {
								win.refreshFieldsVisibility()
							}
						}
					}]
				}, {
					xtype: 'panel',
					border: false,
					layout: 'form',
					labelWidth: 50,
					items: [{
						fieldLabel: 'Время',
						allowBlank: getRegionNick() != 'kz',
						width: 80,
						name: 'EvnDirectionCVI_takeTime',
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						xtype: 'swtimefield',
						format: 'H:i',
						onTriggerClick: function() {
							var bf = this.MainPanel.getForm();
							var time_field = bf.findField('EvnDirectionCVI_takeTime');
							if (time_field.disabled) {
								return false;
							}
							setCurrentDateTime({
								callback: function() {
									win.refreshFieldsVisibility()
								},
								dateField: bf.findField('EvnDirectionCVI_takeDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: 'EvnDirectionCVIEditForm'
							});
						}.createDelegate(this)
					}]
				}]
			}, {
				xtype: 'panel',
				layout: 'column',
				border: false,
				hidden: getRegionNick() == 'kz',
				items: [{
					xtype: 'panel',
					layout: 'form',
					labelWidth: 220,
					border: false,
					items: [{
						fieldLabel: 'Дата отправки образца в лабораторию',
						width: 100,
						name: 'EvnDirectionCVI_sendDate',
						maxValue: new Date(),
						xtype: 'swdatefield',
						listeners: {
							change: function() {
								win.refreshFieldsVisibility()
							}
						}
					}]
				}, {
					xtype: 'panel',
					border: false,
					layout: 'form',
					labelWidth: 50,
					items: [{
						fieldLabel: 'Время',
						width: 80,
						name: 'EvnDirectionCVI_sendTime',
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						xtype: 'swtimefield',
						format: 'H:i',
						onTriggerClick: function() {
							var bf = this.MainPanel.getForm();
							var time_field = bf.findField('EvnDirectionCVI_sendTime');
							if (time_field.disabled) {
								return false;
							}
							setCurrentDateTime({
								callback: function() {
									win.refreshFieldsVisibility()
								},
								dateField: bf.findField('EvnDirectionCVI_sendDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: 'EvnDirectionCVIEditForm'
							});
						}.createDelegate(this)
					}]
				}]
			}, {
				fieldLabel: (getRegionNick() != 'kz')?'Лицо, отбиравшее биоматериал':'Сотрудник, отбиравший биоматериал',
				hiddenName: 'MedPersonal_tid',
				allowBlank: getRegionNick() != 'kz',
				width: 300,
				listWidth: 400,
				xtype: 'swmedpersonalcombo',
				listeners: {
					change: function(combo,newValue) {
						win.refreshFieldsVisibility()
						
						if (getRegionNick() == 'kz') {
							
							var base_form = win.findById('EvnDirectionCVIEditForm').getForm();
							
							base_form.findField('MedStaffFact_id').clearValue();
							
							setMedStaffFactGlobalStoreFilter({
								Lpu_id: getGlobalOptions().lpu_id,
								medPersonalIdList: [newValue]
							});
							
							base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}
					}
				}
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					allowBlank: getRegionNick() != 'kz',
					hiddenName: 'MedStaffFact_id',
					name: 'MedStaffFact_id',
					listWidth: 600,
					width: 300,
					xtype: 'swmedstafffactglobalcombo',
					fieldLabel: 'Место работы сотрудника',
					displayField: 'PostMed_Name'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() == 'kz',
				items: [{
					boxLabel: 'Cito!',
					labelSeparator: '',
					name: 'EvnDirectionCVI_IsCito',
					xtype: 'swcheckbox'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					fieldLabel: 'Телефон сотрудника',
					width: 100,
					name: 'EvnDirectionCVILink_PhonePersonal',
					xtype: 'textfield',
					maskRe: /\d/,
					allowBlank: getRegionNick() != 'kz',
					autoCreate: {tag: "input", maxLength: 12, autocomplete: "off"}
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					allowBlank: getRegionNick() != 'kz',
					comboSubject: 'CVIOrderType',
					fieldLabel: 'Тип заказа на исследование',
					prefix: 'r101_',
					width: 300,
					xtype: 'swcommonsprcombo'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					fieldLabel: 'МО направления биоматериала',
					allowBlank: getRegionNick() != 'kz',
					hiddenName: 'EvnDirectionCVILink_ReceiverMoID',
					width: 300,
					xtype: 'swlpulocalcombo'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					allowBlank: getRegionNick() != 'kz',
					comboSubject: 'CVIPurposeSurvey',
					fieldLabel: 'Цель исследования',
					prefix: 'r101_',
					listeners: {
						'change': function(combo,newValue) {
							
							var base_form = win.findById('EvnDirectionCVIEditForm').getForm();
							
							var cvi_status_id = base_form.findField('CVIStatus_id').getValue();
							
							base_form.findField('CVIStatus_id').getStore().filterBy(function(rec){
								return rec.get('CVIPurposeSurvey_id') == newValue;
							});
							
							var index = base_form.findField('CVIStatus_id').getStore().findBy(function(rec) {
								return rec.get('CVIStatus_id') == cvi_status_id;
							});
							
							if (index == -1) base_form.findField('CVIStatus_id').clearValue();
						}	
					},
					width: 300,
					xtype: 'swcommonsprcombo'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					allowBlank: getRegionNick() != 'kz',
					comboSubject: 'CVIStatus',
					fieldLabel: 'Статус пациента',
					prefix: 'r101_',
					moreFields: [
						{ name: 'CVIPurposeSurvey_id', mapping: 'CVIPurposeSurvey_id' }
					],
					width: 300,
					xtype: 'swcommonsprcombo'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					allowBlank: getRegionNick() != 'kz',
					hiddenName: 'EvnDirectionCVILink_IsSymptom',
					fieldLabel: 'Наличие клинических симптомов COVID-19',
					prefix: 'r101_',
					width: 70,
					xtype: 'swyesnocombo'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					allowBlank: getRegionNick() != 'kz',
					fieldLabel: 'Адрес фактического проживания',
					name: 'EvnDirectionCVILink_Address',
					width: 300,
					xtype: 'textarea'
				}]
			}, {
				layout: 'form',
				border: false,
				hidden: getRegionNick() != 'kz',
				items: [{
					allowBlank: getRegionNick() != 'kz',
					fieldLabel: 'Контактный телефон',
					name: 'EvnDirectionCVILink_Phone',
					width: 100,
					xtype: 'textfield',
					maskRe: /\d/,
					autoCreate: {tag: "input", maxLength: 12, autocomplete: "off"}
				}]
			},
			this.TestList],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'EvnDirectionCVI_id'},
				{ name: 'EvnDirectionCVI_pid'},
				{ name: 'Person_id'},
				{ name: 'CVIBiomaterial_id'},
				{ name: 'CVISampleStatus_id'},
				{ name: 'EvnDirectionCVILink_PhonePersonal'},
				{ name: 'CVIOrderType_id'},
				{ name: 'EvnDirectionCVILink_ReceiverMoID'},
				{ name: 'CVIPurposeSurvey_id'},
				{ name: 'CVIStatus_id'},
				{ name: 'EvnDirectionCVILink_IsSymptom'},
				{ name: 'EvnDirectionCVILink_Address'},
				{ name: 'EvnDirectionCVILink_Phone'},
				{ name: 'EvnDirectionCVILink_WorkPlace'},
				{ name: 'PersonEvn_id'},
				{ name: 'Server_id'},
				{ name: 'Lpu_id'},
				{ name: 'Diag_id'},
				{ name: 'EvnDirectionCVI_RegNumber'},
				{ name: 'EvnDirectionCVI_Contact'},
				{ name: 'EvnDirectionCVI_Lab'},
				{ name: 'EvnDirectionCVI_setDate'},
				{ name: 'MedPersonal_id'},
				{ name: 'EvnDirectionCVI_takeDate'},
				{ name: 'EvnDirectionCVI_takeTime'},
				{ name: 'EvnDirectionCVI_sendDate'},
				{ name: 'EvnDirectionCVI_sendTime'},
				{ name: 'MedPersonal_tid'},
				{ name: 'EvnDirectionCVI_IsCito'},
				{ name: 'EvnDirectionCVI_isSmear'},
				{ name: 'EvnDirectionCVI_SmearNumber'},
				{ name: 'EvnDirectionCVI_SmearResult'},
				{ name: 'EvnDirectionCVI_isBlood'},
				{ name: 'EvnDirectionCVI_BloodNumber'},
				{ name: 'EvnDirectionCVI_BloodResult'},
				{ name: 'EvnDirectionCVI_isSputum'},
				{ name: 'EvnDirectionCVI_SputumNumber'},
				{ name: 'EvnDirectionCVI_SputumResult'},
				{ name: 'EvnDirectionCVI_isLavage'},
				{ name: 'EvnDirectionCVI_LavageNumber'},
				{ name: 'EvnDirectionCVI_LavageResult'},
				{ name: 'EvnDirectionCVI_isAspirate'},
				{ name: 'EvnDirectionCVI_AspirateNumber'},
				{ name: 'EvnDirectionCVI_AspirateResult'},
				{ name: 'EvnDirectionCVI_isAutopsy'},
				{ name: 'EvnDirectionCVI_AutopsyNumber'},
				{ name: 'MedStaffFact_id'},
				{ name: 'EvnDirectionCVI_AutopsyResult'}
			]),
			url: '/?c=EvnDirectionCVI&m=save'
		});
		
		for (var test in this.tests) {
			var panel = {
				xtype: 'panel',
				layout: 'column',
				border: false,
				defaults: {
					xtype: 'panel',
					layout: 'form',
					border: false
				},
				items: [{
					width: 310,
					items: [{
						hideLabel: true,
						boxLabel: this.tests[test],
						labelSeparator: '',
						name: 'EvnDirectionCVI_is'+test,
						xtype: 'swcheckbox',
						listeners: {
							'check': function() {
								win.processFields();
								win.refreshFieldsVisibility();
							}
						}
					}]
				}, {
					width: 120,
					items: [{
						hideLabel: true,
						width: 100,
						name: 'EvnDirectionCVI_'+test+'Number',
						xtype: 'textfield',
						maskRe: /\d/,
						autoCreate: {tag: "input", maxLength: 8, autocomplete: "off"},
						enableKeyEvents: true,
						listeners: {
							'keyup': function() {
								win.processFields();
								win.refreshFieldsVisibility();
							}
						}
					}]
				}, {
					width: 100,
					items: [{
						hideLabel: true,
						width: 80,
						hiddenName: 'EvnDirectionCVI_'+test+'Result',
						xtype: 'swyesnocombo',
					}]
				}]
			};
			
			this.TestList.add(panel);
		}
		
		Ext.apply(this, {
			xtype: 'panel',
			border: false,
			items: [this.MainPanel],
			buttons: [{
				text: '<u>С</u>охранить',
				iconCls: 'save16',
				handler: function()	{
					this.doSave();
				}.createDelegate(this)
			}, {
				text: 'Печать',
				hidden: getRegionNick() == 'kz',
				iconCls: 'print16',
				handler: function()	{
					this.doPrint();
				}.createDelegate(this)
			}, {
				text:'-'
			}, {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
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
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swEvnDirectionCviEditWindow.superclass.initComponent.apply(this, arguments);
	}
});