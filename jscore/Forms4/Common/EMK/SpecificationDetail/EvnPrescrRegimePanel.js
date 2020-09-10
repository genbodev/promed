/**
 * EvnPrescrRegimePanel - Добавление режима
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.SpecificationDetail.EvnPrescrRegimePanel', {
	/* свойства */
	alias: 'widget.EvnPrescrRegimePanel',
	autoShow: false,
	cls: 'EvnPrescrRegimePanel',
	constrain: true,
	extend: 'base.BaseFormPanel',
	header: false,
	border: false,
	scrollable: true,
	title: 'Назначение режима',
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
	deletePrescr: function (){
		var me = this,
			specPan = me.parentPanel,
			cntr = specPan.getController();
		me.mask('Удаление назначения');
		if (cntr) {
			var grid = cntr.getGridByObject('EvnPrescrRegime');
			if (grid) {
				var rec = grid.getSelectionModel().getSelectedRecord();
				if (rec){
					var cbFn = function(){
						me.unmask();
						if(grid.getStore().getCount() > 0){
							grid.getSelectionModel().select(0);
							cntr.openSpecification('EvnPrescrRegime', grid, grid.getSelectionModel().getSelectedRecord(), true);
						}
						else {
							cntr.openSpecification();
						}
					};
					grid.deleteItem(rec,cbFn);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при поиске удаляемой записи'));
					me.unmask();
				}
			}
		}
	},
	reset: function(){
		var me = this,
			base_form = me.formPanel.getForm(),
			isNotForPacket = Ext6.isEmpty(me.PacketPrescr_id);
		// Скрываем все поля, которые для режима в пакете не нужны
		var notWithPacket = me.query('[notWithPacket="true"]');
		notWithPacket.forEach(function(f){f.setVisible(isNotForPacket);});
		// уменьшаем ширину полей и окна, если открыто для пакета
		//me.EvnPrescr_dayNum.setFlex(isNotForPacket?1:false);
		if(!isNotForPacket)
			me.EvnPrescr_dayNum.setSize(200);
		var parent = me.findParentByType();
		if(parent)
			parent.setSize(isNotForPacket?700:500);
		base_form.reset();
		base_form.isValid();
		me.EvnPrescr_setDate.setValue(new Date());
	},
	setData: function(data){
		this.data = data;
	},
	/* конструктор */
	show: function(data) {
		this.setValuesMode = true; // режим автоматического изменения данных формы
		this.callParent(arguments);

		var me = this,
			base_form = me.formPanel.getForm();
		me.setData(data);

		me.action = (typeof data.record == 'object' ? 'edit' : 'add');
		me.callback = (typeof data.callback == 'function' ? data.callback : Ext6.emptyFn);
		me.formParams = (typeof data.formParams == 'object' ? data.formParams : {});
		me.mask('Подождите, идет загрузка...');

		me.PacketPrescr_id = data.PacketPrescr_id?data.PacketPrescr_id:null;

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
						base_form.findField('PrescriptionRegimeType_id').focus(true, 250);
					},
					url: '/?c=EvnPrescr&m=loadEvnPrescrRegimeEditForm'
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
	doSave: function() {
		var me = this,
			base_form = me.formPanel.getForm();

		if ( this.formStatus == 'save' ) {

			return false;
		}
		if ( !base_form.isValid() ) {
			me.formStatus = 'edit';
			return false;
		}
		var params = {};
		params.parentEvnClass_SysNick = me.data.parentEvnClass_SysNick;
		params.PrescriptionType_id = 1;
		params.EvnPrescr_pid = me.data.Evn_id;
		params.PersonEvn_id = me.data.PersonEvn_id;
		params.Server_id = me.data.Server_id;
		base_form.url = '/?c=EvnPrescr&m=saveEvnPrescrRegime';
		if(me.PacketPrescr_id){
			base_form.url = '/?c=PacketPrescr&m=createPacketPrescrRegime';
			params.PacketPrescr_id = me.PacketPrescr_id;
		}

		this.formStatus = 'save';
		this.mask(LOAD_WAIT_SAVE);

		base_form.submit({
			failure: function(result_form, action) {
				me.formStatus = 'edit';
				me.unmask();
				var text = 'Ошибка сохранения данных.';
				if ( action.result && action.result.Error_Msg )
					text = action.result.Error_Msg;
				sw4.showInfoMsg({
					panel: me,
					type: 'error',
					text: text
				});
			},
			params: params,
			success: function(result_form, action) {
				me.formStatus = 'edit';
				me.unmask();
				if ( action.result ) {
					sw4.showInfoMsg({
						panel: me,
						type: 'success',
						text: 'Данные сохранены.'
					});
					var data = {};
					data.evnPrescrData = base_form.getValues();
					data.evnPrescrData.EvnPrescr_id = action.result.EvnPrescr_id;
					me.reset();
					me.callback(data);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошла ошибка'));
				}
			}
		});
	},
	initComponent: function() {
		var me = this;

		this.EvnPrescr_setDate = Ext6.create('Ext6.form.field.Date',{
			format: 'd.m.Y',
			allowBlank: false,
			fieldLabel: 'Начать',
			labelWidth: 100,
			maxWidth: 224,
			name: 'EvnPrescr_setDate',
			plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
			value: new Date(),
			minValue: new Date(),
			notWithPacket: true
		});
		this.EvnPrescr_dayNum = Ext6.create('Ext6.form.field.Number',{
			//flex: 1,
			fieldLabel: 'Продолжать',
			maxWidth: 220,
			name: 'EvnPrescr_dayNum',
			boxLabel: 'дней',
			minValue: 1,
			maxValue: 999
		});

		this.formPanel = Ext6.create('Ext6.form.Panel', {
			url: '/?c=EvnPrescr&m=saveEvnPrescrRegime',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{ name: 'accessType' },
						{ name: 'EvnPrescr_id' },
						{ name: 'EvnPrescr_pid' },
						{ name: 'PrescriptionRegimeType_id' },
						{ name: 'EvnPrescr_setDate' },
						{ name: 'EvnPrescr_dayNum' },
						{ name: 'EvnPrescr_Descr' },
						{ name: 'PersonEvn_id' },
						{ name: 'Server_id' }
					]
				})
			}),
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
					name: 'accessType', // Режим доступа
					value: null,
					xtype: 'hidden'
				}, {
					name: 'EvnPrescr_id', // Идентификатор назначения
					value: null,
					xtype: 'hidden'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'PrescriptionRegimeType',
					fieldLabel: 'Тип режима',
					allowBlank: false,
					name: 'PrescriptionRegimeType_id'
				}, {
					border: false,
					layout: {
						type: 'hbox'
					},
					items: [
						me.EvnPrescr_setDate,
						{
							xtype: 'checkboxfield',
							boxLabel: 'Продолжать бессрочно',
							disabled: false,
							name: 'continue',
							notWithPacket: true,
							padding: '0 10'
						},
						{
							xtype: 'tbspacer',
							flex: 1,
							notWithPacket: true
						},
						me.EvnPrescr_dayNum
					]
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

