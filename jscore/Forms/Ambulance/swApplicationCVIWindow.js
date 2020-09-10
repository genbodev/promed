/**
 * swApplicationCVIWindow - окно редактирования Анкеты по КВИ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		Ambulance
 * @access		public
 * @copyright	Copyright (c) 2020 Витасмарт
 * @author		Chernykh Konstantin
 * @version		март.2020
 */

sw.Promed.swApplicationCVIWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swApplicationCVIWindow',
	id: 'swApplicationCVIWindow',
	title: langs('Анкета по КВИ'),
	modal: true,
	maximized: false,
	width: 450,
	height: 400,
	layout: 'form',
	params: null,
	baseForm: null,

	show: function () {
		this.params = arguments[0];
		this.baseForm = this.FormPanel.getForm();
		this.baseForm.findField('Person_id').setValue(this.params.Person_id);
		this.baseForm.reset();
		if (!Ext.isEmpty(this.params.fields)) {
			if (!Ext.isEmpty(this.params.fields.CVICountry_id)) {
				this.params.fields.KLCountry_id = this.params.fields.CVICountry_id;
			}
			this.baseForm.setValues(this.params.fields);
			this.baseForm.findField('ApplicationCVI_arrivalDate').setValue(this.params.fields.ApplicationCVI_arrivalDate);
		}
		if (this.params.action != 'add') {
			if (this.params.action == 'view') {
				this.FormPanel.disable();
			}
			this.buttons.find(function(button){
				if (button.iconCls == 'save16') {
					button.setDisabled(this.params.action == 'view');
					button.setVisible(this.params.action != 'view');
				}
			}.createDelegate(this));
		} else {
			this.FormPanel.enable();
			this.buttons.find(function(button){
				button.setDisabled(false);
				button.setVisible(true);
			});
		}
		this.PlaceArrivalTrigger(this.baseForm.findField('PlaceArrival_id').getValue());
		sw.Promed.swApplicationCVIWindow.superclass.show.apply(this, arguments);
	},
	
	saveForm: function () {
		if (!this.baseForm.isValid()) {
			sw.swMsg.alert(langs('Ошибка'), langs('Проверьте обязательные для заполнения поля'));
			return false;
		}
		var parentObj = getWnd(this.params.forObject);
		if (parentObj.isVisible()) {
			parentForm = Ext.isEmpty(parentObj.FormPanel) ? parentObj.MainPanel.getForm() : parentObj.FormPanel.getForm();
			var fields = {
				isSavedCVI: 1,
				PlaceArrival_id: this.baseForm.findField('PlaceArrival_id').getValue(),
				OMSSprTerr_id: this.baseForm.findField('OMSSprTerr_id').getValue(),
				ApplicationCVI_arrivalDate: Ext.util.Format.date(this.baseForm.findField('ApplicationCVI_arrivalDate').getValue(), 'd.m.Y'),
				ApplicationCVI_flightNumber: this.baseForm.findField('ApplicationCVI_flightNumber').getValue(),
				ApplicationCVI_isContact: this.baseForm.findField('ApplicationCVI_isContact').getValue(),
				ApplicationCVI_isHighTemperature: this.baseForm.findField('ApplicationCVI_isHighTemperature').getValue(),
				Cough_id: this.baseForm.findField('Cough_id').getValue(),
				Dyspnea_id: this.baseForm.findField('Dyspnea_id').getValue(),
				ApplicationCVI_Other: this.baseForm.findField('ApplicationCVI_Other').getValue()
			};
			if (this.params.forObject == 'swHomeVisitAddWindow') {
				fields.CVICountry_id = this.baseForm.findField('KLCountry_id').getValue();
			} else {
				fields.KLCountry_id = this.baseForm.findField('KLCountry_id').getValue();
			}
			parentForm.setValues(fields);
		}
		this.hide();
	},

	reset: function () {
		this.baseForm.reset();
	},

	PlaceArrivalTrigger: function(value) {
		switch (1*value) {
			case 1:
				this.baseForm.findField('KLCountry_id').enable();
				this.baseForm.findField('KLCountry_id').allowBlank = false;
				this.baseForm.findField('OMSSprTerr_id').disable();
				this.baseForm.findField('OMSSprTerr_id').clearValue();
				this.baseForm.findField('ApplicationCVI_arrivalDate').enable();
				this.baseForm.findField('ApplicationCVI_arrivalDate').allowBlank = false;
				this.baseForm.findField('ApplicationCVI_flightNumber').enable();
				this.baseForm.findField('ApplicationCVI_flightNumber').allowBlank = false;
			break;
			case 2:
				this.baseForm.findField('OMSSprTerr_id').enable();
				this.baseForm.findField('OMSSprTerr_id').allowBlank = false;
				this.baseForm.findField('KLCountry_id').disable();
				this.baseForm.findField('KLCountry_id').clearValue();
				this.baseForm.findField('ApplicationCVI_arrivalDate').enable();
				this.baseForm.findField('ApplicationCVI_arrivalDate').allowBlank = false;
				this.baseForm.findField('ApplicationCVI_flightNumber').enable();
				this.baseForm.findField('ApplicationCVI_flightNumber').allowBlank = false;
			break;
			case 3:
			default:
				this.baseForm.findField('OMSSprTerr_id').disable();
				this.baseForm.findField('OMSSprTerr_id').clearValue();
				this.baseForm.findField('KLCountry_id').disable();
				this.baseForm.findField('KLCountry_id').clearValue();
				this.baseForm.findField('ApplicationCVI_arrivalDate').disable();
				this.baseForm.findField('ApplicationCVI_arrivalDate').setValue('');
				this.baseForm.findField('ApplicationCVI_flightNumber').disable();
				this.baseForm.findField('ApplicationCVI_flightNumber').setValue('');
				this.baseForm.findField('ApplicationCVI_flightNumber').allowBlank = true;
			break;
		}
	},

	initComponent: function () {
		var win = this;
		win.FormPanel = new sw.Promed.FormPanel({
			style: 'padding: 0 10px 0 10px;',
			xtype: 'form',
			layout: 'form',
			labelWidth: 150,
			items: [
				{
					xtype: 'hidden',
					id: 'Person_id'
				}, {
					xtype: 'swplacearrivalcombo',
					fieldLabel: 'Прибытие',
					name: 'PlaceArrival_id',
					allowBlank: false,
					width: 250,
					listeners: {
						change: function(combo, newValue) {
							win.PlaceArrivalTrigger(newValue);
						}
					}
				}, {
					xtype: 'swklcountrycombo',
					fieldLabel: 'Страна',
					name: 'KLCountry_id',
					disabled: true,
					allowBlank: true,
					width: 250
				}, {
					xtype: 'swomssprterrcombo',
					fieldLabel: 'Регион',
					name: 'OMSSprTerr_id',
					disabled: true,
					allowBlank: true,
					width: 250
				}, {
					xtype: 'swdatefield',
					fieldLabel: 'Дата прибытия',
					name: 'ApplicationCVI_arrivalDate',
					disabled: true,
					allowBlank: true,
				}, {
					xtype: 'textfield',
					fieldLabel: 'Рейс',
					name: 'ApplicationCVI_flightNumber',
					disabled: true,
					allowBlank: true,
					width: 250
				}, {
					xtype: 'swyesnocombo',
					fieldLabel: 'Контакт с человеком с подтвержденным диагнозом КВИ',
					name: 'ApplicationCVI_isContact',
					allowBlank: false,
					width: 70
				}, {
					xtype: 'swyesnocombo',
					fieldLabel: 'Повышенная температура',
					name: 'ApplicationCVI_isHighTemperature',
					allowBlank: false,
					width: 70
				}, {
					xtype: 'swcommonsprcombo',
					comboSubject: 'Cough',
					fieldLabel: 'Кашель',
					name: 'Cough_id',
					allowBlank: false,
					width: 150
				}, {
					xtype: 'swcommonsprcombo',
					comboSubject: 'Dyspnea',
					fieldLabel: 'Одышка',
					name: 'Dyspnea_id',
					allowBlank: false,
					width: 150
				}, {
					xtype: 'textareapmw',
					fieldLabel: 'Иное',
					name: 'ApplicationCVI_Other',
					allowBlank: true,
					width: 250
				}
			]
		});
		Ext.apply(win, { items: win.FormPanel });
		sw.Promed.swApplicationCVIWindow.superclass.initComponent.apply(this, arguments);
	}
});