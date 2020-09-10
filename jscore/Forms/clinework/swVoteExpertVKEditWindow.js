/**
* Форма Решение эксперта
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
*/

sw.Promed.swVoteExpertVKEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Решение эксперта',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 500,
	onHide: Ext.emptyFn,
	shim: false,
	buttonAlign: "right",
	objectName: 'swVoteExpertVKEditWindow',
	closeAction: 'hide',
	id: 'swVoteExpertVKEditWindow',
	objectSrc: '/jscore/Forms/clinework/swVoteExpertVKEditWindow.js',
	buttons: [
		{
			handler: function() {
				this.ownerCt.save();
			},
			iconCls: 'save16',
			text: 'Сохранить'
		},
		'-',
		HelpButton(this, -1),
		{
			text      : 'Отмена',
			tabIndex  : -1,
			tooltip   : 'Отмена',
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		'hide': function(p) {
			p.findById('VoteExpertVKEditForm').getForm().reset();
		}
	},
	
	show: function() {
		sw.Promed.swVoteExpertVKEditWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] || !arguments[0].VoteExpertVK_id ) {
			sw.swMsg.alert('Ошибка', 'Неверные параметры!');
			this.hide();
			return false;
		}
		
		var win = this;
		var b_f = this.findById('VoteExpertVKEditForm').getForm();
		this.center();

		this.VoteExpertVK_id = arguments[0].VoteExpertVK_id;

		var lm = win.getLoadMask();
		lm.show();
		b_f.load({
			url: '/?c=VoteListVK&m=getDecision',
			params: {
				VoteExpertVK_id: this.VoteExpertVK_id
			},
			success: function (f, r) {
				lm.hide();
				b_f.findField('VoteExpertVK_isInternalRequest').fireEvent('change', b_f.findField('VoteExpertVK_isInternalRequest'), b_f.findField('VoteExpertVK_isInternalRequest').getValue());
			}
		});
	},
	
	save: function() {

		var form = this.findById('VoteExpertVKEditForm').getForm();
		if(!form.isValid()) {
			sw.swMsg.alert('Ошибка', 'Не все обязательные поля заполнены!');
			return false;
		}
		
		var params = form.getValues();
		params.VoteExpertVK_isInternalRequest = form.findField('VoteExpertVK_isInternalRequest').getValue() ? 2 : 1;

		var lm = this.getLoadMask('Сохранение...');
		lm.show();
		Ext.Ajax.request({
			params: params,
			callback: function(options, success, response) {
				lm.hide();
				if (success) {
					lm.hide();
					this.hide();
					this.onHide();
				}
			}.createDelegate(this),
			url: '/?c=VoteListVK&m=saveDecision'
		});
	},
	
	setDescrAllowBlack: function() {
		var form = this.findById('VoteExpertVKEditForm').getForm();
		var isInternalRequest = form.findField('VoteExpertVK_isInternalRequest').getValue();
		var isApproved = form.findField('VoteExpertVK_isApproved').getValue();
		form.findField('VoteExpertVK_Descr').setAllowBlank(isInternalRequest != true && isApproved != 1);
	},
	
	initComponent: function() {	
		Ext.apply(this, {
			layout: 'fit',
			defaults: {
				border: false,
				bodyStyle: 'padding: 5px;'
			},
			items: [{
				region: 'center',
				labelAlign: 'right',
				xtype: 'form',
				autoHeight: true,
				frame: true,
				border: false,
				id: 'VoteExpertVKEditForm',
				labelWidth: 120,
				items: [{
					xtype: 'hidden',
					name: 'VoteExpertVK_id'
				}, {
					xtype: 'swcheckbox',
					name: 'VoteExpertVK_isInternalRequest',
					labelSeparator: '',
					boxLabel: 'Запросить очную экспертизу',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var form = this.findById('VoteExpertVKEditForm').getForm();
							form.findField('VoteExpertVK_isApproved').setContainerVisible(newValue != true);
							form.findField('VoteExpertVK_isApproved').setAllowBlank(newValue == true);
							if (newValue == true) {
								form.findField('VoteExpertVK_isApproved').setValue('');
							}
							this.setDescrAllowBlack();
							this.syncShadow();
						}.createDelegate(this)
					}
				}, {
					xtype: 'swyesnocombo',
					hiddenName: 'VoteExpertVK_isApproved',
					fieldLabel: 'Решение эксперта',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get('YesNo_id') == newValue);
							});
							combo.fireEvent('select', combo, combo.getStore().getAt(index));
						},
						'select': function(combo, record, index) {
							this.setDescrAllowBlack();
						}.createDelegate(this)
					}
				}, {
					xtype: 'textarea',
					name: 'VoteExpertVK_Descr',
					anchor: '100%',
					fieldLabel: 'Комментарий эксперта'
				}],
				reader: new Ext.data.JsonReader({
					success: function() {}
				}, [
					{ name: 'VoteExpertVK_id' },
					{ name: 'VoteExpertVK_isInternalRequest' },
					{ name: 'VoteExpertVK_isApproved' },
					{ name: 'VoteExpertVK_Descr' }
				])
			}]
		});
		sw.Promed.swVoteExpertVKEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
