/**
* 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Swan
* @version      01.06.2014
*/

sw.Promed.swMongoRecacheIdWindow = Ext.extend(sw.Promed.BaseForm, {
	
	modal: true,
	id:'MongoRecacheIdWindow',
	width: 600,
	autoHeight: true,
	onCancel: Ext.emptyFn,
	action:'edit',
	callback: Ext.emptyFn,
	title: lang['peresobrat_kesh_po_identifikatoru'],
	show: function() {
		sw.Promed.swMongoRecacheIdWindow.superclass.show.apply(this, arguments);
		var base_form = this.FormPanel.getForm();
		this.formStatus = 'edit';
		var win = this;
		base_form.reset();
		if ( arguments[0] )
		{
			if(arguments[0].sysCache_id && arguments[0].sysCache_id!=null){
				this.sysCache_id = arguments[0].sysCache_id;
			}
			if(arguments[0].sysCache_object && arguments[0].sysCache_object!=null){
				this.sysCache_object = arguments[0].sysCache_object
			}
			base_form.setValues(arguments[0]);
		}else{
			return false;
		}
	},
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';
		var base_form = this.FormPanel;
		if (!base_form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//this.formStatus = 'edit';
					base_form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
            this.formStatus = 'edit';
			return false;
		}

		this.submit();
	},
	submit: function() {
		var form = this.FormPanel;
		var base_form = this.FormPanel.getForm();
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		
		loadMask.show();
		form.getForm().submit({
			params: {
				IDs:Ext.util.JSON.encode(base_form.findField('Ids').getValue().split(',')),
				type:'Id'
				
			},
			url:'/?c=MongoCache&m=recacheMongoCache',
			failure: function(result_form, action) {
				loadMask.hide();
				win.formStatus = 'edit';
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				var data={};
				data.Person_id = base_form.findField('Person_id').getValue();
				win.callback(data);
				form.getForm().reset();
				win.hide();
				
			}
		});
	},
	initComponent: function() {
    	
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			height:45,
			bodyStyle: 'padding: 5px',
			buttonAlign: 'left',
			frame: true,
			id: 'MongoRecacheID',
			labelAlign: 'right',
			labelWidth: 180,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, []),
			items: [
			{
				name: 'sysCache_id',
				xtype: 'hidden'
			},

			{
				name: 'sysCache_object',
				xtype: 'hidden'
			},
			{
				layout: 'form',
				items: [
					{
						xtype:'textfield',
						allowBlank:false,
						fieldLabel: lang['identifikator'],
						hiddenName:'Ids',
						name:'Ids',
						width: 300
					}
					
				]
			}
			
				
			],
			enableKeyEvents: true,
			keys: []
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'ok16',
				tabIndex: TABINDEX_ADDREF + 19,
				text: BTN_FRMSAVE
			}
			],
			items: [
			this.FormPanel
			]
		});
		
		sw.Promed.swMongoRecacheIdWindow.superclass.initComponent.apply(this, arguments);
	}
});