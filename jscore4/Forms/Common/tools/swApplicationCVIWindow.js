Ext.define('sw.tools.swApplicationCVIWindow', {
	alias: 'widget.swApplicationCVIWindow',
	extend: 'sw.standartToolsWindow',
	width: 600,
	height: null,
	title: 'Анкета КВИ',
	autoShow: true,
	//closeAction: 'hide',
	border: false,
	show: function () {
		var win = this,
			baseForm = win.getForm();
		win.callParent();
		!arguments[0] || baseForm.setValues(arguments[0]);
		baseForm.isValid();
	},
	getForm: function() {
		return this.down('BaseForm').getForm();
	},
	isValid: function() {
		return this.getForm().isValid();
	},
	initComponent: function () {
		var win = this, countryCombo = Ext.create('sw.KLCountryCombo', {
			name: 'KLCountry_id',
			fieldLabel: 'Страна',
			disabled: true,
			allowBlank: false
		}), terrCombo = Ext.create('sw.OmsSprTerrCombo', {
			name: 'OMSSprTerr_id',
			fieldLabel: 'Регион',
			disabled: true,
			allowBlank: false
		}), arrivalDateField = Ext.create('sw.DateField', {
			name: 'ApplicationCVI_arrivalDate',
			fieldLabel: 'Дата прибытия',
			disabled: true,
			allowBlank: false
		}), flightField = Ext.create('Ext.form.TextField', {
			name: 'ApplicationCVI_flightNumber',
			fieldLabel: 'Рейс',
			disabled: true,
			allowBlank: false
		});

		win.formPanel = {
			layout: 'form',
			padding: '5',
			border: false,
			items: [
				{
					xtype: 'hidden',
					name: 'CmpCallCard_id',
					allowBlank: false
				},
				{
					name: 'PlaceArrival_id',
					xtype: 'swPlaceArrivalCombo',
					fieldLabel: 'Прибытие',
					allowBlank: false,
					listeners: {
						change: function (combo, value) {
							var isReturnedFromCountry = value === 1,
								isReturnedFromRegion = value === 2;
							countryCombo.setDisabled(!isReturnedFromCountry);
							terrCombo.setDisabled(!isReturnedFromRegion);
							arrivalDateField.setDisabled(!isReturnedFromRegion && !isReturnedFromCountry);
							flightField.setDisabled(!isReturnedFromRegion && !isReturnedFromCountry);
							countryCombo.clearValue();
							terrCombo.clearValue();
							arrivalDateField.reset();
							flightField.reset();
							win.isValid();
						}
					}
				},
				countryCombo,
				terrCombo,
				arrivalDateField,
				flightField,
				{
					xtype: 'container',
					layout: 'hbox',
					flex: 1,
					items: [
						{
							xtype: 'label',
							text: 'Контакт с человеком с подтвержденным диагнозом КВИ:',
							width: 350
						},
						{
							name: 'ApplicationCVI_isContact',
							//fieldLabel: 'Контакт с человеком с подтвержденным диагнозом КВИ',
							xtype: 'swYesNoCombo',
							flex: 1,
							allowBlank: false
						}
					]
				}, {
					xtype: 'container',
					layout: 'hbox',
					flex: 1,
					items: [
						{
							xtype: 'label',
							text: 'Повышенная температура:',
							width: 208
						},
						{
							name: 'ApplicationCVI_isHighTemperature',
							fieldLabel: '',
							labelAlign: 'right',
							xtype: 'swYesNoCombo',
							flex: 1,
							allowBlank: false
						}
					]
				},
				{
					name: 'Cough_id',
					fieldLabel: 'Кашель',
					labelWidth: 100,
					xtype: 'swCoughCombo',
					allowBlank: false
				}, {
					name: 'Dyspnea_id',
					fieldLabel: 'Одышка',
					xtype: 'swDyspneaCombo',
					allowBlank: false
				}, {
					name: 'ApplicationCVI_Other',
					fieldLabel: 'Иное',
					xtype: 'textareafield',
					allowBlank: true
				}
			]
		};

		this.addEvents({
			saveForm: true
		});

		win.configComponents = {
			center: [ win.formPanel ],
			leftButtons: {
				xtype: 'button',
				text: 'Сохранить',
				iconCls: 'save16',
				handler: function(){
					var baseForm = win.getForm();

					if( !baseForm.isValid() ) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}

					var savedParams = baseForm.getValues();
					savedParams['isSavedCVI'] = 1;
					win.fireEvent('saveForm', savedParams);
					win.close();
				}
			}
		};

		win.callParent(arguments);
	}
});