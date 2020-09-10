/* 
 * Форма выдачи результата
 */


Ext.define('common.BSME.tools.swRequestIssueWindow', {
	extend: 'Ext.window.Window',
    autoShow: true,
	modal: true,
	width: '50%',
//	height: '',
	refId: 'requestissuewnd',
	closable: true,
//    header: false,
	title: 'Выдача результата',
	id: 'RequestIssueWindow',
	border: false,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	callback: Ext.emptyFn,
	EvnForensic_id: null,
	BaseForm: {},
	listeners: {
		show: function(wnd,eOpts) {
			
			var BaseForm = wnd.BaseForm.getForm();
			
			if (!wnd.EvnForensic_id) {
				Ext.Msg.alert('Ошибка', 'Не указан идентификатор родительской заявки');
				wnd.close();
				return false;
			}
						
			BaseForm.findField('EvnForensic_ResultOutDate').setValue(new Date())
			BaseForm.findField('EvnForensic_ResultOutTime').setValue(new Date())
			BaseForm.findField('PostTicket_Date').setValue(new Date())
			
			
			wnd.checkIssueType();
		},
		close: function(wnd) {
			log(arguments);
			if (typeof wnd.callback == 'function') {
				wnd.callback();
			}
		}
	},
	checkIssueType: function() {
		var me = this;
		
		var baseForm = me.BaseForm.getForm();
		var issueTypeGroupVal = baseForm.findField('issueTypeGroup').getValue(); //Лично в руки - 1; По почте - 2
		var issueType = issueTypeGroupVal.issueType;
		var postFields = [
			'PostTicket_Num',
			'PostTicket_Date',
		];
		
		var personFields = [
			'PersonContainer',
			'Person_FIO',
			'RecipientIdentity_Num',
			'EvnForensic_ResultOutDate',
			'EvnForensic_ResultOutTime'
		]
		
		var setActive = function(array,active) {
			for (var i=0; i<array.length; i++) {
				me.BaseForm.down('[name='+array[i]+']').setDisabled(!active).setVisible(active).allowBlank  = !active;
			}
		}
		
		setActive( (issueType == 1)? personFields : postFields , true );
		setActive( (issueType == 1)? postFields : personFields, false );
		
		
	},
	
	initComponent: function(){
		var me = this;		
		
		this.BaseForm = Ext.create('sw.BaseForm',{
			cls: 'mainFormNeptune',
			autoScroll: true,
			id: this.id+'_BaseForm',
			flex: 1,
			width: '100%',
			height: '100%',
			layout: {
				padding: '0 0 0 0', // [top, right, bottom, left]
				align: 'stretch',
				type: 'vbox'
			},
			items: [{
				xtype: 'container',
				padding: '0 10 0 0',
				width: '100%',
				bodyPadding: 10,
				autoHeight: true,
				defaults: {
					labelAlign: 'left',
					labelWidth: 250,
					msgTarget: 'under'
				},
				items: [{
					xtype: 'radiogroup',
					fieldLabel: 'Тип выдачи',
					// Arrange radio buttons into two columns, distributed vertically
					columns: 1,
					name: 'issueTypeGroup',
					vertical: true,
					listeners: {
						scope: me,
						change: function( group, newValue, oldValue, eOpts ) {
							me.checkIssueType()
						}
					},
					items: [{
						boxLabel: 'Лично в руки',
						name: 'issueType',
						inputValue: '1',
						checked: true
					}, {
						boxLabel: 'По почте',
						name: 'issueType',
						inputValue: '2'
					}]
				},	
				{
					xtype: 'PersonField',
					name: 'PersonContainer',
					searchCallback: function() {
						me.defaultFocus = '[name=RecipientIdentity_Num]';
					},
					onChange: Ext.emptyFn,
					fieldLabel:'Получающее лицо',
					idName: 'Person_gid',
					FioName: 'Person_FIO',
					//allowBlank: true,
					width: '80%'
				},{
					xtype: 'textfield',
					name: 'RecipientIdentity_Num',
					maxLength: 100,
					padding: '0 0 0 0', // [top, right, bottom, left]
					fieldLabel: 'Номер удостоверения получающего лица'
				},{
					xtype: 'swdatefield',
					fieldLabel: 'Дата выдачи',
					name: 'EvnForensic_ResultOutDate'
				},{
					xtype: 'swtimefield',
					fieldLabel: 'Время выдачи',
					name: 'EvnForensic_ResultOutTime'
				},{
					xtype: 'textfield',
					name: 'PostTicket_Num',
					maxLength: 100,
					padding: '0 0 0 0', // [top, right, bottom, left]
					fieldLabel: 'Номер почтовой квитанции'
				},{
					xtype: 'swdatefield',
					fieldLabel: 'Дата почтовой квитанции',
					padding: '0 0 0 0', // [top, right, bottom, left]
					name: 'PostTicket_Date'
				}]
			}]
		});
		
		
		
		Ext.apply(me,{
			items: [
				this.BaseForm
			], 
			buttons: [{
				xtype: 'button',	
				text: 'Готово',
				handler: function(btn,evnt) {
					var params = {},
						baseForm = this.BaseForm.getForm();

					params['Person_gid'] = baseForm.findField('Person_gid').getValue();
					params['EvnForensic_id'] = me.EvnForensic_id;

					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."}); 
					loadMask.show();
					
					
					this.BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveEvnForensicResultOut',
						success: function(form, action) {
							loadMask.hide();
							if (!action.result.EvnForensic_id) {
								Ext.Msg.alert('Ошибка', "Не получен идентификатор случая");
							} else {
								if (me.BaseForm.getForm().findField('issueTypeGroup').getValue().issueType == 1) { //Лично в руки - 1; 
									Ext.MessageBox.confirm('Сообщение', 
										'Распечатать заключение?',function(btn){
											if( btn === 'yes' ){

											}
										}.bind(this)
									)
								} 
								me.close();
							}
						},
						failure: function(form, action) {
							loadMask.hide();
							switch (action.failureType) {
								case Ext.form.action.Action.CLIENT_INVALID:
									Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
									break;
								case Ext.form.action.Action.CONNECT_FAILURE:
									Ext.Msg.alert('Ошибка', 'Ошибка соединения с сервером');
									break;
								case Ext.form.action.Action.SERVER_INVALID:
									Ext.Msg.alert('Ошибка', action.result.Error_Msg);
						   }
						},
						callback: function() {
							loadMask.hide();
						}
					});
				}.bind(this)
			}]
		})
		
		me.callParent(arguments)
	}
})