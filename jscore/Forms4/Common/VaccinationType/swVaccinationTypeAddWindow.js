/**
 * swVaccinationTypeEditWindow - Форма обалвения типов прививок и реакций
 * common.VaccinationType.swVaccinationTypeEditWindow
 * widget.swVaccinationTypeEditWindow
 * PromedWeb - The New Generation of Medical Statistic Software
 * https://rtmis.ru/
 *
 *
 * @package      Common
 * @access       public
 */
Ext6.define('common.VaccinationType.swVaccinationTypeAddWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swVaccinationTypeAddWindow',
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	renderTo: main_center_panel.body.dom,
	cls: 'arm-window-new',
	title: 'Добавление прививки/реакции',
	constrain: true,
	draggable: true,
	modal:true,
	autoHeight: true,
	autoWidth: true,
	layout: 'border',
	callback: Ext6.emptyFn,
	resizable: false,
	show: function(data) {
		var win = this;
		var base_form = win.MainPanel.getForm();
		base_form.reset();
		win.MainPanel.queryById('isVaccination').setPressed(true);
		base_form.findField('VaccinationType_Name').setAllowBlank(false);
		base_form.findField('VaccinationType_begDate').setAllowBlank(false);

		this.callParent(arguments);
		this.action = arguments[0].action;
		this.callback = arguments[0].callback;
	},
	doSave: function(){
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
		win.mask('Сохранение...');

		base_form.submit({
			url: '/?c=VaccinationType&m=saveVaccinationType',
			params: { VaccinationType_isReaction: win.MainPanel.queryById('VaccinationType_isReaction').getValue() },
			failure: function (form, action) {
				win.unmask();
				sw.swMsg.alert(langs('Ошибка'), langs('При сохранении формы произошла ошибка.'));
			},
			success: function (form, action, response) {
				win.unmask();
				win.callback();
				win.hide();
			}
		});
	},
	initComponent: function() {
		var win = this;

		win.MainPanel = new Ext6.form.FormPanel({
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 29px 69px;',
			items: [{
				border: false,
				layout: 'vbox',
				defaults: {
					padding: '0px 0px 0px 0px',
					labelWidth: 110,
					labelStyle: 'text-align: right;',
					height:32,
					width: 250
				},
				items: [{
					xtype: 'segmentedbutton',
					userCls: 'segmentedButtonGroup',
					itemId:'VaccinationType_isReaction',
					width: 180,
					height:36,
					padding:'2px 0 6px 116px',
					items: [{
						text: 'Прививка',
						itemId:'isVaccination',
						pressed: true,
						enableToggle: true,
						value:'1',
						padding:0,
						width:90
					}, {
						text: 'Реакция',
						itemId:'isReaction',
						enableToggle: true,
						value:'2',
						padding:0,
						width:90
					}]
				},
				{
					xtype: 'textfield',
					fieldLabel: 'Код',
					name: 'VaccinationType_Code',
					width: 250
				},
				{
					xtype: 'textfield',
					fieldLabel: 'Наименование',
					name: 'VaccinationType_Name',
					allowBlank: false,
					width: 400,
				}, {
					xtype: 'datefield',
					fieldLabel: 'Начало',
					format: 'd.m.Y',
					width: 250,
					name: 'VaccinationType_begDate',
					allowBlank: false,
					listeners: {
						'change': function(cmp, val){
							var win = this;
							var base_form = win.MainPanel.getForm();
							base_form.findField('VaccinationType_endDate').setMinValue(val);
						}.createDelegate(this)
					}
				}, {
					xtype: 'datefield',
					fieldLabel: 'Окончание',
					format: 'd.m.Y',
					width: 250,
					name: 'VaccinationType_endDate',
					listeners: {
						'select': function(cmp, val){
							var win = this;
							var base_form = win.MainPanel.getForm();
							cmp.minValue = base_form.findField('VaccinationType_begDate').getValue();
						}.createDelegate(this)
					}
				}
				]
			}]
		});

		Ext6.apply(win, {
			layout: 'anchor',
			items: [
				win.MainPanel
			],
			buttons: ['->',
				{
					text: 'Отменить',
					userCls: 'buttonCancel',
					margin: 0,
					handler: function() {
						win.hide();
					}
				},
				{
					text: langs('Добавить'),
					cls: 'buttonAccept',
					handler: function() {
						win.doSave();
					}
				}
			]
		});

		this.callParent(arguments);
	}
});
