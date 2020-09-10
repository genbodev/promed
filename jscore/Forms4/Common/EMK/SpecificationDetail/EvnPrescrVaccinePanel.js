/**
 * EvnPrescrVaccinePanel - Добавление вакцины
 *
 */
Ext6.define('common.EMK.SpecificationDetail.EvnPrescrVaccinePanel', {
	/* свойства */
	alias: 'widget.EvnPrescrVaccinePanel',
	autoShow: false,
	cls: 'EvnPrescrVaccinePanel',
	constrain: true,
	extend: 'base.BaseFormPanel',
	header: false,
	border: false,
	scrollable: true,
	title: 'Назначение Вакцины',
	width: '100%',
	autoHeight: true,
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	data: {},
	parentPanel: {},
	inModalWindow: true,
	setValuesMode: true,
	reset: function(){
		console.log('reset')
	},
	setData: function(data){
		this.data = data;
	},
	doSave: function() {
		console.log('doSave')
	},
	show: function(data) {
		this.callParent(arguments);
		var me = this,
			base_form = me.formPanel.getForm();
		me.setData(data);

		me.action = (typeof data.record == 'object' ? 'edit' : 'add');
		me.callback = (typeof data.callback == 'function' ? data.callback : Ext6.emptyFn);
		me.formParams = (typeof data.formParams == 'object' ? data.formParams : {});
		me.mask('Подождите, идет загрузка...');

		switch(me.action){
			case 'edit':
				base_form.load({
					failure: function() {
						me.unmask();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'));
					},
					params: {
						'EvnPrescr_id': data.record.get('EvnPrescr_id'),
						'parentEvnClass_SysNick': data.parentEvnClass_SysNick
					},
					success: function() {
						me.unmask();
						console.log('data upload success')
					},
					url: '/?c=controller&m=method'
				});
				break;
			case 'add':
				me.reset();
				me.unmask();
				break;
			default:
				me.unmask();
		}
		me.formStatus = 'edit';
	},
	initComponent: function() {
		var me = this;
		this.formPanel = Ext6.create('Ext6.form.Panel', {
			// url: '/?c=controller&m=method',
			// reader: Ext6.create('Ext6.data.reader.Json', {
			// 	type: 'json',
			// 	model: Ext6.create('Ext6.data.Model', {
			// 		fields: [
			// 			{ name: 'EvnPrescr_id' },
			// 			{ name: 'VaccineType_id' },
			// 			{ name: 'EvnPrescr_Descr' }
			// 		]
			// 	})
			// }),
			layout: 'anchor',
			bodyPadding: '20 30',
			border: false,
			defaults: {
				border: false,
				padding: '15 0 0 0',
				anchor: '100%',
				width: 615,
				maxWidth: 615 + 145,
				labelWidth: 100
			},
			items: [
				{
					name: 'EvnPrescr_id', // Идентификатор назначения
					value: null,
					xtype: 'hidden'
				}, {
					xtype: 'commonSprCombo',
					fieldLabel: 'Тип вакцины',
					allowBlank: false,
					name: 'VaccineType_id'
				}, {
					xtype: 'textarea',
					fieldLabel: 'Комментарий',
					name: 'EvnPrescr_Descr',
					notWithPacket: true,
					flex: 1
				}
			]
		});
		if(this.inModalWindow) {
			me.buttons = [
				{
					handler: function () {
						me.doSave();
					},
					cls: 'button-primary',
					text: 'СОХРАНИТЬ',
					margin: '0 10 0 19'
				},
				{
					cls: 'button-secondary',
					iconCls: 'menu-lvn-del',
					text: 'УДАЛИТЬ НАЗНАЧЕНИЕ',
					handler: function () {
						Ext6.Msg.show({
							closable: true,
							title: 'Удаление назначения',
							msg: '<span class="msg-alert-text">Вы действительно хотите удалить назначение?</span>',
							buttons: Ext6.Msg.OKCANCEL,
							buttonText: {
								ok: 'Удалить',
								cancel: 'Отмена'
							},
							fn: function (btn) {
								if (btn == 'ok') {
									me.deletePrescr();
								}
							}
						});
					}
				},
				'->', {
					hidden: true,
					xtype: 'tbtext',
					userCls: 'save-tbar-text',
					html: 'Данные сохранены'
				}
			];
		}
		Ext6.apply(me, {
			items: [
				this.formPanel
			]
		});

		this.callParent(arguments);
	}
});

