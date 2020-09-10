/**
 * swVaccinationTypePrepEditWindow - Окно добавления препаратов для вакцинации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * https://rtmis.ru/
 *
 *
 * @package      Common
 * @access       public
 */

Ext6.define('common.VaccinationType.swVaccinationTypePrepEditWindow', {
	/* свойства */
	alias: 'widget.swVaccinationTypePrepEditWindow',
	addCodeRefresh: Ext.emptyFn,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	autoHeight: true,
	autoWidth: true,
	cls: 'arm-window-new',
	extend: 'base.BaseForm',
	layout: 'border',
	modal: true,
	callback: Ext6.emptyFn,
	renderTo: main_center_panel.body.dom,
	resizable: false,
	title: 'Препарат для вакцинации',
	doSave: function() {
		var win = this;
		var form = win.MainPanel.getForm();

		if ( !form.isValid() ) {
			Ext6.MessageBox.show({
				title: 'Проверка данных формы',
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING
			});
			return false;
		}

		win.mask('Добавление препарата...');
		form.submit({
			url: '/?c=VaccinationType&m=saveVaccinationPrep',
			failure: function (form, action) {
				win.unmask();
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

		this.callParent(arguments);

		if(arguments[0].action && arguments[0].VaccinationType_id) {
			this.action = arguments[0].action;
			base_form.findField('VaccinationType_id').setValue(arguments[0].VaccinationType_id);

			if ((arguments[0].action == 'edit' || arguments[0].action == 'view') && arguments[0].VaccinationTypePrep_id) {

				base_form.findField('VaccinationTypePrep_id').setValue(arguments[0].VaccinationTypePrep_id);
				this.setTitle('Редактирование препарата для вакцинации');
				base_form.load({
					url: '/?c=VaccinationType&m=getVaccinationTypePrep',
					params: { VaccinationTypePrep_id: arguments[0].VaccinationTypePrep_id }
				});
				if(this.action == 'view') {
					win.down("#addButton").setVisible(false);
					base_form.owner.items.items.forEach(function (f) {
						f.items.items.forEach(function (f1) {
							f1.setDisabled(true);
						});
					});
				}
			} else if (arguments[0].action == 'add') {
				this.setTitle('Добавление препарата для вакцинации');
			}
		}else{
			this.hide();
			return false;
		}

		this.callback = arguments[0].callback;

		base_form.findField('Prep_id').getStore().load({ params:{ VaccinationType_isReaction: (arguments[0].VaccinationType_isReaction == "2")? '2' : '1' } });
	},
	initComponent: function() {
		var win = this;

		Ext6.define(win.id + '_FormModel', {
			extend:'Ext6.data.Model',
			fields:[
				{name: 'VaccinationTypePrep_id'},
				{name: 'VaccinationTypePrep_begDate'},
				{name: 'VaccinationTypePrep_endDate'}
			]
		});

		win.MainPanel = new Ext6.form.FormPanel({
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 29px 29px 0px 29px;',
			items: [{
				border: false,
				layout: 'vbox',
				defaults: {
					padding: '0px 0px 5px 0px',
					labelWidth: 110,
					labelStyle: 'text-align: right;',
					height:32,
					width: 250
				},
				items: [{
					width: 550,
					allowBlank: false,
					fieldLabel: 'Препарат',
					name: 'Prep_id',
					valueField: 'Prep_id',
					displayField: 'Prep_Name',
					queryMode: 'local',
					store: {
						fields: [
							{ name: 'Prep_id', mapping: 'Prep_id', type: 'int', hidden: 'true'},
							{ name: 'Prep_Name', mapping: 'Prep_Name' }
						],
						sorters: { property: 'Prep_id', direction: 'ASC' },
						proxy: {
							type: 'ajax',
							actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
							url : '/?c=VaccinationType&m=loadVaccinationTypePrepComboList',
							reader: { type: 'json' },
						},
						mode: 'remote'
					},
					xtype: 'baseCombobox',
				},
				{
					xtype: 'datefield',
					name: 'VaccinationTypePrep_begDate',
					format: 'd.m.Y',
					fieldLabel: 'Дата начала',
					allowBlank: false,
					value: new Date(),
					listeners: {
						'change': function(cmp, val){
							var win = this;
							var base_form = win.MainPanel.getForm();
							base_form.findField('VaccinationTypePrep_endDate').setMinValue(val);
						}.createDelegate(this)
					}
				},
				{
					xtype: 'datefield',
					name: 'VaccinationTypePrep_endDate',
					format: 'd.m.Y',
					fieldLabel: 'Дата окончания',
					listeners: {
						'select': function(cmp, val){
							var win = this;
							var base_form = win.MainPanel.getForm();
							cmp.minValue = base_form.findField('VaccinationTypePrep_begDate').getValue();
						}.createDelegate(this)
					}
				},
				{ xtype: 'textfield', name: 'VaccinationTypePrep_id', hidden:true},
				{ xtype: 'textfield', name: 'VaccinationType_id', hidden:true}
				]
			}],
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
					handler: function(){ win.hide(); }
				},
				{
					text: langs('Добавить'),
					handler: function(){ win.doSave(); },
					itemId:'addButton'
				}
			]
		});

		this.callParent(arguments);
	}
});