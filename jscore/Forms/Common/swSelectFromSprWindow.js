/**
* swLpuOperEnvWindow - Оперативная обстановка по выбранному ЛПУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dyomin Dmitry
* @version      01.10.2012
*/

sw.Promed.swSelectFromSprWindow = Ext.extend(sw.Promed.BaseForm, {
	
	
	modal: true,
	id:'swSelectFromSprWindow',
	width: 600,
	autoHeight: true,
	type:null,
	onCancel: Ext.emptyFn,
	action:'edit',
	callback: Ext.emptyFn,
	onSelect: Ext.emptyFn,
	closeAction:'destroy',
	comboSubject:'',
	show: function() {
		sw.Promed.swSelectFromSprWindow.superclass.show.apply(this, arguments);
		var base_form = this.FormPanel.getForm();
		this.formStatus = 'edit';
		var win = this;
		base_form.reset();
		if ( arguments[0] )
		{
			if(arguments[0].onSelect){
				this.onSelect=arguments[0].onSelect;
			}
			if(arguments[0].callback){
				this.callback=arguments[0].callback;
			}
			if(arguments[0].comboSubject){
				this.comboSubject=arguments[0].comboSubject;
			}
			
		}
	
	},
	onSelect: function(){
		var base_form =this.FormPanel.getForm();
		this.callback(base_form.findField('PrehospTrauma').getValue());
		this.hide();
	},
	initComponent: function() {
    	
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			height:50,
			bodyStyle: 'padding: 5px',
			buttonAlign: 'left',
			frame: true,
			id: 'PersEvalEditForm',
			labelAlign: 'right',
			labelWidth: 220,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
			]),
			items: [
				
			],
			enableKeyEvents: true,
			keys: []
		});
		
		/*this.FormPanel.add({
					comboSubject: this.comboSubject,
					fieldLabel: lang['travma'],
					hiddenName: this.comboSubject+'_id',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				}	);*/
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.onSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
			this.FormPanel
			]
			
		});
		this.FormPanel.add({
					comboSubject: 'PrehospTrauma',
					fieldLabel: lang['vid_travmyi_vneshnego_vozdeystviya'],
					hiddenName: 'PrehospTrauma',//Времено так
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				}	);
		sw.Promed.swSelectFromSprWindow.superclass.initComponent.apply(this, arguments);
		
		
		
	}
});