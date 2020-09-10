/* 
	Выбор талона вызова для отказа
*/


Ext.define('sw.tools.swSimplifyRejectSmpCallCardWindow', {
	alias: 'widget.swSimplifyRejectSmpCallCardWindow',
	extend: 'Ext.window.Window',
	title: 'Отказ от вызова',
	refId: 'swSimplifyRejectSmpCallCardWindow',
	width: 600,
	height: null,
	resizable: false,
	modal: true,
	closeAction: 'hide',

	initComponent: function() {
		var win = this,
			region = getRegionNick();

		win.curArm = 'default';

		win.addEvents({
			saveRejectReason: true
		});
		
		win.ReasonStore = new Ext.data.JsonStore({
			autoLoad: false,
			storeId: 'CmpRejectionReason',
			fields: [
				{name: 'CmpRejectionReason_id', type: 'int'},
				{name: 'CmpRejectionReason_code', type: 'string'},
				{name: 'CmpRejectionReason_name', type: 'string'}
			],
			filters: [
				function(rec){
					switch(true) {
						case win.curArm.inlist(['smpheaddoctor']) && win.params.CmpReason_Code == '999':
							return true;
						case win.curArm.inlist(['smpheaddoctor']):
							return !rec.get('CmpRejectionReason_code').inlist([5]);
						default: 
							return !rec.get('CmpRejectionReason_code').inlist([4,5]);
					}
				}
			],
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=CmpCallCard4E&m=getRejectionReason',
				reader: {
					type: 'json',
					successProperty: 'success',
					root: 'data'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				}
			}
		});

		Ext.apply(this, {
			buttonAlign: 'right',
			layout: {
				align: 'stretch',
				type: 'vbox'
			},
			items: [
				{
					xtype: 'BaseForm',
					border: false,
					id: this.id + 'BaseForm',
					defaults: {
						labelWidth: 200,
						style: 'margin: 10px;'
					},
					layout: {
						align: 'stretch',
						type: 'vbox'
					},
					items: [
						{
							xtype: 'combobox',
							name: 'CmpRejectionReason_id',
							queryMode: 'remote',
							labelAlign: 'right',
							fieldLabel: 'Причина отмены',
							size: 16,
							editable: false,
							tpl: Ext.create('Ext.XTemplate',
								'<tpl for=".">' +
									'<div class="enlarged-font x-boundlist-item">' +
									'<font color="red">{CmpRejectionReason_code}.</font> {CmpRejectionReason_name}' +
									'</div></tpl>'),
							displayTpl: '<tpl for="."> {CmpRejectionReason_code}. {CmpRejectionReason_name} </tpl>',
							store: win.ReasonStore,
							tableName: 'CmpRejectionReason',
							valueField: 'CmpRejectionReason_id',
							codeField: 'CmpRejectionReason_code',
							displayField: 'CmpRejectionReason_name',
							fields: [
								{name: 'CmpRejectionReason_id', type:'int'},
								{name: 'CmpRejectionReason_code', type:'string'},
								{name: 'CmpRejectionReason_name', type:'string'}
							],
							width: 200,
							allowBlank: false,
							listeners: {
								change: function(combo, newVal) {
									if(region=='ufa' && win.curArm == 'smpheaddoctor') {
										//Поле МО виден и обязателен для заполнения при поводе "999. Решение старшего врача" и причине отмены "5. Передан в другую МО"
										var LpuBuildingField = win.down('[name=LpuBuilding_id]'),
											visible = newVal == 5 && win.params.CmpReason_Code == '999'
										LpuBuildingField.setVisible(visible);
										LpuBuildingField.setDisabled(!visible);
									}
								}
							}

						},
						{
							xtype: 'nestedLpuBuildingCombo',
							hidden: true,
							width: 200,
							name: 'LpuBuilding_id',
							allowBlank: false,
							disabled: true,
							params: {
								where: "where Lpu_id <> " + getGlobalOptions().lpu_id
							},
							getLpu: function() {
								var value = this.getValue();

								if(!value) return;

								var rec = this.store.findRecord("LpuBuilding_id", value);

								if(!rec) return;

								return rec.get('Lpu_id');
							}
						},
						{
							xtype: 'textarea',
							fieldLabel: 'Комментарий',
							name: 'CmpCallCardStatus_Comment',
							size: 16,
							height: 100,
							labelAlign: 'right',
							width: 200,
							displayField: 'Person_Surname',
							storeName: this.id + '_comboCommonStore',
							enableKeyEvents: true,
							allowBlank: true
						}
					]
				}
			],
			buttons: [
				{
					text: 'Сохранить',
					iconCls: 'ok16',
					refId: 'selectButton',
					handler: function () {
						var form = win.down('form').getForm(),
							data = form.getValues(),
							cmpRejectionReason = form.findField('CmpRejectionReason_id'),
							lpuBuildingField = form.findField('LpuBuilding_id'),
							lpuBuilding_id = lpuBuildingField.getValue(),
							lpu_id = lpuBuildingField.getLpu(),
							lpu_nick = lpuBuildingField.getRawValue();

						var rec = cmpRejectionReason.getStore().findRecord('CmpRejectionReason_id', cmpRejectionReason.getValue());
						data.CmpRejectionReason_Name = rec.get('CmpRejectionReason_name');
						data.lpuBuilding_id = lpuBuilding_id;
						data.lpu_id = lpu_id;
						data.lpu_nick = lpu_nick;
						//data.CmpRejectionReasonName = form.findField('CmpRejectionReason_id').getRawValue();

						if(!form.isValid())
							return false;

						win.fireEvent('saveRejectReason', data);
					}
				},
				'->',
				{
					xtype: 'button',
					iconCls: 'cancel16',
					refId: 'cancelButton',
					text: 'Закрыть',
					handler: function () {
						//var data = {'cancelBtn' : true};
						//win.callback( data );
						win.close();

					}
				}
			]

		});

		win.callParent(arguments);
	},
	
	show: function(){
		this.callParent(arguments);

		var me = this,
			form = me.down('form').getForm();

		if(arguments[0]) {
			me.curArm = arguments[0].armtype || 'default';
			me.params = arguments[0].params || {};
		}
		form.findField('CmpRejectionReason_id').reset();
		form.findField('CmpCallCardStatus_Comment').reset();
		form.isValid();
	}
},
function() {
    /**
     * @singleton
     * Singleton instance of {@link Ext.window.MessageBox}.
     */
    new this();
});