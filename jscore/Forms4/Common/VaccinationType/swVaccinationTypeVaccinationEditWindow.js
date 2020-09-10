/**
 * swVaccinationTypeVaccinationEditWindow - Форма добавления прививки/реакции в тип вакцинации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * https://rtmis.ru/
 *
 *
 * @package      Common
 * @access       public
 */

Ext6.define('common.VaccinationType.swVaccinationTypeVaccinationEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swVaccinationTypeVaccinationEditWindow',
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	renderTo: main_center_panel.body.dom,
	cls: 'arm-window-new',
	title: 'Добавление прививки / реакции',
	constrain: true,
	draggable: true,
	modal: true,
	autoHeight: true,
	autoWidth: true,
	layout: 'border',
	callback: Ext6.emptyFn,
	resizable: false,
	doSave: function() {
		var win = this;
		var base_form = win.MainPanel.getForm();

		if ( !base_form.isValid() ) {
			Ext6.MessageBox.show({
				title: 'Проверка данных формы',
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING
			});
			return false;
		}

		win.mask('Добавление препарата...');
		base_form.submit({
			url: '/?c=VaccinationType&m=saveVaccination',
			failure: function (form, action) {
				win.unmask();
			},
			params: {
				Vaccination_isNacCal: (base_form.findField('Vaccination_isNacCal').getValue()) ? '2' : '1',
				Vaccination_isEpidemic: (base_form.findField('Vaccination_isEpidemic').getValue()) ? '2' : '1'
			},
			success: function (form, action) {
				win.unmask();
				win.callback();
				win.hide();
			}
		});
	},
	show: function() {
		var win = this;
		var base_form = win.MainPanel.getForm();
		base_form.reset();

		base_form.findField('Vaccination_pid').getStore().load({ params:{ VaccinationType_id: arguments[0].VaccinationType_id } });
		base_form.findField('VaccinationRiskGroupAccess_id').getStore().load();
		
		this.isReaction = (arguments[0].VaccinationType_isReaction == '2') ? true : false;

		this.callParent(arguments);
		if(arguments[0].action)
			this.action = arguments[0].action;
		else {
			this.hide();
			return false;
		}
		if(!arguments[0].VaccinationType_id) {
			this.hide(); return false;
		}
		this.callback = arguments[0].callback;

		this.setTitle( ((this.action == 'add') ? 'Добавление' : 'Редактирование') + ' ' + ((this.isReaction) ? 'реакции' : 'прививки'));

		base_form.findField('VaccinationType_id').setValue(arguments[0].VaccinationType_id);
		base_form.findField('Vaccination_pid').setVisible(!this.isReaction);
		
		base_form.findField('Vaccination_isNacCal').setValue(!this.isReaction);
		base_form.findField('Vaccination_isNacCal').setVisible(!this.isReaction);
		base_form.findField('Vaccination_isNacCal').setDisabled(false);

		base_form.findField('Vaccination_isEpidemic').setVisible(!this.isReaction);
		base_form.findField('Vaccination_isEpidemic').setDisabled(false);

		base_form.findField('Vaccination_isReactionLevel').setVisible(this.isReaction);

		base_form.findField('Vaccination_begAge').setFieldLabel('Период с предыдущей '+ ((this.isReaction) ? 'реакции' : 'прививки') +' (Возраст пациента)');
		base_form.findField('Vaccination_isSingle').setBoxLabel('Несовместима с другими '+ ((this.isReaction) ? 'реакциями' : 'прививками'));

		base_form.findField('Vaccination_Name').setAllowBlank(false);
		base_form.findField('VaccinationRiskGroupAccess_id').setAllowBlank(false);
		base_form.findField('Vaccination_begDate').setAllowBlank(false);
		base_form.findField('Vaccination_begAge').setAllowBlank(base_form.findField('Vaccination_pid').getValue() == null || base_form.findField('Vaccination_pid').getValue() == '');
		
		if(this.action == 'edit' || this.action == 'view') {
			if (arguments[0].VaccinationType_id && arguments[0].Vaccination_id) {
				base_form.findField('Vaccination_id').setValue(arguments[0].Vaccination_id);
				base_form.load({
					url: '/?c=VaccinationType&m=getVaccination',
					params: { Vaccination_id: arguments[0].Vaccination_id },
					success: function(fm) {
						base_form.findField('Vaccination_Name').setAllowBlank(false);
						base_form.findField('VaccinationRiskGroupAccess_id').setAllowBlank(false);
						base_form.findField('Vaccination_begDate').setAllowBlank(false);
					}
				});
			} else {
				this.hide();
				return false;
			}
			if(this.action == 'view') {
				win.down("#addButton").setVisible(false);
				base_form.owner.items.items.forEach(function (f) {
					if (f.items)
						f.items.items.forEach(function (f1) { f1.setDisabled(true); });
					else
						f.setDisabled(true);
				});
			}
		}
	},
	declinationDate: function (age, ageType) {
		let ageTypeName = [];
		switch(ageType){
			case 1: ageTypeName = ["День", "Дня", "Дней"]; break;
			case 2: ageTypeName = ["Месяц", "Месяца", "Месяцев"]; break;
			case 3: ageTypeName = ["Год", "Года", "Лет"]; break;
		}
		if ((age % 100) >= 5 && (age % 100) <= 20) {
			return ageTypeName[2];
		} else {
			if ((age % 10) == 1) {
				return ageTypeName[0];
			} else if ((age % 10) >= 2 && (age % 10) <= 4) {
				return ageTypeName[1];
			} else {
				return ageTypeName[2];
			}
		}
	},
	initComponent: function() {
		var win = this;

		Ext6.define(win.id + '_FormModel', {
			extend:'Ext6.data.Model',
			fields:[
				{name: 'Vaccination_id'},
				{name: 'Vaccination_Name'},
				{name: 'Vaccination_Code'},
				{name: 'Vaccination_Nick'},
				{name: 'Vaccination_pid'},
				{name: 'Vaccination_isNacCal'},
				{name: 'Vaccination_isEpidemic'},
				{name: 'Vaccination_begAge'},
				{name: 'VaccinationAgeType_bid'},
				{name: 'Vaccination_endAge'},
				{name: 'VaccinationAgeType_eid'},
				{name: 'VaccinationRiskGroupAccess_id'},
				{name: 'Vaccination_isSingle'},
				{name: 'Vaccination_isReactionLevel'},
				{name: 'Vaccination_begDate'},
				{name: 'Vaccination_endDate'}
			]
		});

		win.MainPanel = new Ext6.form.FormPanel({
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 29px 0 29px 69px;',
			defaults: {
				anchor: '100%',
				width: 400,
				maxWidth: 400 + 220,
				labelWidth: 240,
				height:32,
				padding:'5px 0',
				labelStyle: 'text-align: right;margin:5px 20px 0 20px;padding:5px 15px 0 0;',
			},
			items: [
				{ name: 'Vaccination_Name', xtype: 'textfield', fieldLabel: 'Наименование', width: 550, allowBlank: false },
				{ name: 'Vaccination_Code', xtype: 'textfield', fieldLabel: 'Код', width: 380 },
				{ name: 'Vaccination_Nick', xtype: 'textfield', fieldLabel: 'Наименование в Ф063у', width: 550 },
				{
					width: 550,
					fieldLabel: 'Предыдущая схема прививки',
					name: 'Vaccination_pid',
					valueField: 'Vaccination_id',
					displayField: 'Vaccination_Name',
					queryMode: 'local',
					store: {
						fields: [
							{name: 'Vaccination_id', type: 'int'},
							{name: 'Vaccination_Name', type: 'string'},
							{name: 'Vaccination_isNacCal', type: 'string'},
							{name: 'Vaccination_isEpidemic', type: 'string'}
						],
						proxy: {
							type: 'ajax',
							actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
							url: '/?c=VaccinationType&m=loadVaccinationPrevComboList',
							reader: {type: 'json'}
						},
						sorters: { property: 'Vaccination_id', direction: 'ASC' }
					},
					xtype: 'baseCombobox',
					listeners: {
						select: function (cmp, val) {
							var data = val.getData();
							var win = this;
							var base_form = win.MainPanel.getForm();

							base_form.findField('Vaccination_isNacCal').setValue((data.Vaccination_isNacCal == '2'));
							base_form.findField('Vaccination_isNacCal').setDisabled((data.Vaccination_Name !== ''));

							if (data.Vaccination_Name == '')
								base_form.findField('Vaccination_isNacCal').setValue(true);

							base_form.findField('Vaccination_isEpidemic').setValue((data.Vaccination_isEpidemic == '2'));
							base_form.findField('Vaccination_isEpidemic').setDisabled((data.Vaccination_Name !== ''));

							base_form.findField('Vaccination_begAge').setAllowBlank(data.Vaccination_Name == '');

						}.createDelegate(this)
					}
				},
				{ name: 'Vaccination_isNacCal', xtype: 'checkbox', boxLabel: langs('Национальный календарь'), padding: '0 0 0 243px', inputValue: 2 },
				{ name: 'Vaccination_isEpidemic', xtype: 'checkbox', boxLabel: langs('Эпидемиологические показания'), padding: '0 0 0 243px', inputValue: 2 },
				{
					border: false,
					layout: 'column',
					columnWidth: 0.3,
					margin: '0 0 0 20',
					width: 600,
					maxWidth: 600 + 220,
					height:'32',
					bodyStyle: 'background-color:transparent;',
					defaults: {
						border: false,
						labelWidth: 220,
						height: 32,
						padding:'5px 0',
						labelStyle: 'text-align: right;margin:0 20px 0 20px',
					},
					items: [{
						xtype: 'numberfield',
						name: 'Vaccination_begAge',
						fieldLabel: langs('Период с предыдущей прививки (Возраст пациента)'),
						labelStyle: 'text-align: right;padding-top:0px',
						height: 32,
						padding:'0px 0',
						labelWidth: 220,
						margin: '0 10px 0 0 ',
						width: 360,
						maxValue: 366,
						minValue: 1,
						listeners: {
							'change': function (cmp, val) {
								var win = this;
								var base_form = win.MainPanel.getForm();
								base_form.findField('VaccinationAgeType_bid').allowBlank = (val == '' || val == 0 || val == null);
								base_form.findField('VaccinationAgeType_bid').clearValue(val == '' || val == 0 || val == null);
								base_form.findField('VaccinationAgeType_bid').setDisabled((val == '' || val == 0 || val == null) || this.action == 'view');
								if(val != '' || val != null) {
									base_form.findField('VaccinationAgeType_bid').getStore().setData([
										{"VaccinationAgeType_bid": 1, "VaccinationAgeType_bName": win.declinationDate(val, 1)},
										{"VaccinationAgeType_bid": 2, "VaccinationAgeType_bName": win.declinationDate(val, 2)},
										{"VaccinationAgeType_bid": 3, "VaccinationAgeType_bName": win.declinationDate(val, 3)}
									]);
								}

							}.createDelegate(this)
						}
					}, {
						width: 120,
						allowBlank: true,
						name: 'VaccinationAgeType_bid',
						valueField: 'VaccinationAgeType_bid',
						displayField: 'VaccinationAgeType_bName',
						labelWidth: 0,
						disabled: true,
						height: 34,
						margin:'-5px 0px',
						queryMode: 'local',
						hideLabel: true,
						store: {
							fields: [
								{name: 'VaccinationAgeType_bid', type: 'int'},
								{name: 'VaccinationAgeType_bName', type: 'string'}
							],
							data: [
								{"VaccinationAgeType_bid": 1, "VaccinationAgeType_bName": "Дней"},
								{"VaccinationAgeType_bid": 2, "VaccinationAgeType_bName": "Месяцев"},
								{"VaccinationAgeType_bid": 3, "VaccinationAgeType_bName": "Лет"},
							]
						},
						xtype: 'baseCombobox'
					}]

				},
				{
					border: false,
					layout: 'column',
					xtype: 'container',
					height:40,
					width: 600,
					maxWidth: 600 + 220,
					columnWidth: 0.3,
					type: 'combo',
					padding:'0 0',
					margin: '0 0 0 20',
					bodyStyle: 'background-color:transparent;',
						defaults: {
							border: false,
							labelWidth: 220,
							height: 32,
							padding:'5px 0',
							labelStyle: 'text-align: right;margin:0 20px 0 20px;padding: 5px 5px 0 0',
						},
						items: [{
							xtype: 'numberfield',
							fieldLabel: langs('Максимальный возраст пациента'),
							inputValue: 1,
							labelWidth: 220,
							height: 32,
							width:360,
							name: 'Vaccination_endAge',
							maxValue: 366,
							minValue: 1,
							listeners: {
								'change': function (cmp, val) {
									var win = this;
									var base_form = win.MainPanel.getForm();
									base_form.findField('VaccinationAgeType_eid').allowBlank = (val == '' || val == 0 || val == null);
									base_form.findField('VaccinationAgeType_eid').clearValue(val == '' || val == 0 || val == null);
									base_form.findField('VaccinationAgeType_eid').setDisabled(val == '' || val == 0 || val == null);

									if(val != '' || val != null) {
										base_form.findField('VaccinationAgeType_eid').getStore().setData([
											{"VaccinationAgeType_eid": 1, "VaccinationAgeType_eName": win.declinationDate(val, 1)},
											{"VaccinationAgeType_eid": 2, "VaccinationAgeType_eName": win.declinationDate(val, 2)},
											{"VaccinationAgeType_eid": 3, "VaccinationAgeType_eName": win.declinationDate(val, 3)}
										]);
									}
								}.createDelegate(this)
							}
						}, {
							width: 120,
							height: 32,
							margin:'0px 10px',
							allowBlank: true,
							disabled: true,
							name: 'VaccinationAgeType_eid',
							valueField: 'VaccinationAgeType_eid',
							displayField: 'VaccinationAgeType_eName',
							queryMode: 'local',
							hideLabel: true,

							store: {
								fields: [
									{name: 'VaccinationAgeType_eid', type: 'int'},
									{name: 'VaccinationAgeType_eName', type: 'string'}
								],
								data : [
									{"VaccinationAgeType_eid": 1, "VaccinationAgeType_eName":"Дней"},
									{"VaccinationAgeType_eid": 2, "VaccinationAgeType_eName":"Месяцев"},
									{"VaccinationAgeType_eid": 3, "VaccinationAgeType_eName":"Лет"},
								]
							},
							xtype: 'baseCombobox'
					}]
			},
				{
					width: 550,
					allowBlank: false,
					fieldLabel: 'Доступна пациентам',
					name: 'VaccinationRiskGroupAccess_id',
					valueField: 'VaccinationRiskGroupAccess_id',
					displayField: 'VaccinationRiskGroupAccess_Name',
					queryMode: 'local',
					height:32,
					store: {
						fields: [
							{name: 'VaccinationRiskGroupAccess_id', type: 'int'},
							{name: 'VaccinationRiskGroupAccess_Name', type: 'string'}
						],
						proxy: {
							type: 'ajax',
							actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
							url: '/?c=VaccinationType&m=loadVaccinationRiskGroupAccessComboList',
							reader: {type: 'json'}
						},
						sorters: { property: 'VaccinationRiskGroupAccess_id', direction: 'ASC' }
					},
					xtype: 'baseCombobox'
				},
				{ name: 'Vaccination_isSingle', xtype: 'checkbox', boxLabel: langs('Несовместима с другими прививками'), padding: '0 0 0 243px', inputValue: 2 },
				{ name: 'Vaccination_isReactionLevel', xtype: 'checkbox', boxLabel: langs('Имеет степень выраженности'), padding: '0 0 0 243px', inputValue: 2 },
				{
					name: 'Vaccination_begDate',
					xtype: 'datefield',
					fieldLabel: 'Дата начала',
					format: 'd.m.Y',
					width: 380,
					allowBlank: false,
					listeners: {
						'change': function(cmp, val){
							var win = this;
							var base_form = win.MainPanel.getForm();
							base_form.findField('Vaccination_endDate').setMinValue(val);
						}.createDelegate(this)
					}
				},
				{
					name: 'Vaccination_endDate',
					xtype: 'datefield',
					fieldLabel: 'Дата окончания',
					format: 'd.m.Y',
					width: 380,
					listeners: {
						'select': function(cmp, val){
							var win = this;
							var base_form = win.MainPanel.getForm();
							cmp.minValue = base_form.findField('Vaccination_begDate').getValue();
						}.createDelegate(this)
					}
				},
				{ name: 'VaccinationType_id', xtype: 'textfield', fieldLabel: 'Вид вакцинации', hidden: true },
				{ name: 'Vaccination_id', xtype: 'textfield', fieldLabel: 'Прививка', hidden: true }
			],
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
		});

		Ext6.apply(win, {
			layout: 'anchor',
			items: [ win.MainPanel ],
			buttons: [
				'->',
				{
					text: BTN_FRMCANCEL,
					handler:function () { win.hide(); },
					itemId: 'cancelButton'
				},
				{
					text: langs('Добавить'),
					handler: function() { win.doSave(); },
					cls: 'flat-button-primary',
					itemId: 'addButton'
				}
			]
		});

		this.callParent(arguments);
	}
});