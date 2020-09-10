Ext.define('sw.tools.subtools.swOrgFilialEditWindow', {
    extend: 'Ext.window.Window',

//    requires: [
//        'Ext.container.Container',
//        'Ext.button.Button'
//    ],
//	refId: 'swOrgFilialEditWindow',
	width: 500,
    height: 250,	
    title: 'Филиалы',
	Org_id: null,
	saveOrgFilialEditWindow: function(callback){
		if (!this.down('form').getForm().isValid()){
			Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
		}
		
		var form = this.down('form').getForm(),
			params = form.getValues();			
			params.Org_id = this.Org_id;
		Ext.Ajax.request({
			url: '/?c=OrgStruct&m=saveOrgFilial',
			params: params,			

			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText);
					
					if (res.OrgFilial_id) {						
						callback(res.OrgFilial_id);
					} else {
						Ext.Msg.alert('Ошибка',res.Error_Msg);
					}
				}

			}
		});
	},
	
	getBaseForm: function(){
		var me = this;
		return Ext.create('sw.BaseForm',{
			xtype: 'BaseForm',
			cls: 'mainFormNeptune',	
			items: [{
				xtype: 'container',
				padding: '10 0 0 0',
				width: '100%',
				bodyPadding: 10,				
				layout: 'column',
				defaults: {
					labelAlign: 'left',
					labelWidth: 250
				},
				items: [{
					border: false,
					layout: 'form',					
					labelWidth: 220,
					width:450,
					padding: '10 10 10 10',
					items: [
//						{
//						name: 'OrgFilial_oldid',
//						xtype: 'hidden'
//					}, 
					{
						fieldLabel: 'Организация',
						name: 'Org_id',
						allowBlank: false,						
						xtype: 'textfield',
						hidden: true,
						width: 800,
						readOnly: true						
					}, {
						fieldLabel: 'Филиал',
						name: 'OrgFilial_id',
						allowBlank: false,
						xtype: 'dOrgCombo',
						width: 800
					}]
				}]				
			}]
		});
	},	
		
    initComponent: function() {
        var me = this,
			conf = me.initialConfig;
			
		
		/*		 
		 * SHOW		 
		 */
		
		
		me.on('render', function(cmp){
			var form = me.down('form').getForm();
			form.findField('Org_id').setValue(conf.Org_id);
			me.Org_id = conf.Org_id;
		})
		
		
		/*
		 * поехали
		 */
		
        Ext.applyIf(me, {
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
               this.getBaseForm()
            ],
			dockedItems: [{
				xtype: 'container',
				dock: 'bottom',
				layout: {
					type: 'hbox',
					align: 'stretch',
					padding: 4
				},
				items: [{
					xtype: 'container',
					layout: 'column',
					items: [{
						xtype: 'button',
						iconCls: 'add16',
						text: 'Добавить',
						handler: function(){
							me.saveOrgFilialEditWindow(function(){
								Ext.Msg.alert('ОК', 'Филиал добавлен.');
								me.close();
							});
						//	me.close();
						}
					}]
				}, {
					xtype: 'container',
					flex: 1,
					layout: {
						type: 'hbox',
						align: 'stretch',
						pack: 'end'
					},
					items: [{
						xtype: 'button',
						iconCls: 'cancel16',
						text: 'Закрыть',
						margin: '0 5',
						handler: function(){
							me.close()
						}
					}]
				}]
			}]
        });
        me.callParent(arguments);
    }
});