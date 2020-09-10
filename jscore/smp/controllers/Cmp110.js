/**
 * Контроллер карты закрытия вызова формы 110У
 */
Ext4.define('smp.controllers.Cmp110', {
    extend: 'Ext4.app.Controller',
	
	views: [
		'smp.views.cmp110.edit.Window',
		'smp.views.cmp110.edit.Form'
	],

	refs: [
		{
			ref: 'editWindow',
			selector: '[xtype=cmp110.edit.window]'
		},
		{
			ref: 'formPanel',
			selector: '[xtype=cmp110.edit.form]'
		}
	],
	
	requires: [
		// xtype: datetimefield
		'ux.form.field.datetime.UX_TimePickerField',
		'ux.form.field.datetime.UX_DateTimePicker',
		'ux.form.field.datetime.UX_DateTimeField',
		'ux.form.field.datetime.UX_DateTimeMenu',
	],
	
    init: function(){
		this.applyVtypes();
		
		this.listen({
			component: {
				// Окно редактирования дерева решений
				'window[xtype=cmp110.edit.window] > toolbar > button#save': {
					click: this.onEditWindowButtonSaveClick
				},
				'window[xtype=cmp110.edit.window] > toolbar > button#cancel': {
					click: this.onEditWindowButtonCancelClick
				},
				'window[xtype=cmp110.edit.window] > toolbar > button#load': {
					click: this.onEditWindowButtonLoadClick
				},
			}
		});
		
		this.callParent();
    },
	
	// @todo Вынести в базовый контроллер
	applyVtypes: function(){
		Ext4.apply(Ext4.form.field.VTypes, {
			num: function(value){
				return /^\d+$/.test(value);
			}
		});
	},

	/**
	 * Вызов окна редактирования дерева решений
	 */
	showEditWindow: function(){
		var win = Ext4.widget('cmp110.edit.window');
		win.show();
	},
	
	/**
	 * Событие клика по кнопке сохранения изменений в окне дерева решений
	 */
	onEditWindowButtonSaveClick: function(btn, e, eOpts){
//		var me = this,
//			win = this.getEditWindow();
//		win.setLoading('Сохранение...');
//		
//		Ext4.Ajax.request({
//			url: '/?c=CmpCallCard&m=saveDecigionTree',
//			params: {
//				data: Ext4.JSON.encode(this.getTree().collectData())
//			},
//			//timeout: 3600,
//			callback: function(options,success,response){
//				win.setLoading(false);
//			},
//			success: function(response,options){
//				var data = Ext4.JSON.decode(response.responseText);
//				if (!data['success']) {
//					Ext4.Msg.alert('Ошибка',data['Error_Msg']);
//				}
//				
//				me.fireEvent('onAfterDecisionTreeSave', data);
//			},
//			failure: function(response,options){
//				Ext4.Msg.alert('Ошибка','Во время сохранения возникла непредвиденная ошибка.');
//				log({response:response,options:options});
//			}
//		});
	},
	
	/**
	 * Событие клика по кнопке отмены изменений в окне дерева решений
	 */
	onEditWindowButtonCancelClick: function(btn, e, eOpts){
		this.getEditWindow().close();
	},
	
	onEditWindowButtonLoadClick: function(btn, e, eOpts){
		this.getFormPanel().load();
	}
});