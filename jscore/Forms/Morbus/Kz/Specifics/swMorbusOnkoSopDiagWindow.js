/**
* swMorbusOnkoSopDiagWindow - окно редактирования "Специальное лечение"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MorbusOnko
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      06.2015
* @comment      
*/

sw.Promed.swMorbusOnkoSopDiagWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	winTitle: 'Сопутствующие заболевания',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 700,
	height: 100,
	maximizable: true,
	autoScroll: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	doSave:  function() {
		var that = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					that.findById('MorbusOnkoSopDiagEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function() {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var formParams = this.form.getValues();
		
		Ext.Ajax.request({
			failure:function () {
				loadMask.hide();
			},
			params: formParams,
			method: 'POST',
			success: function (result) {
				loadMask.hide();
				if (result.responseText) {
					var response = Ext.util.JSON.decode(result.responseText);
					formParams.MorbusOnkoBaseDiagLink_id = response.MorbusOnkoBaseDiagLink_id;
					that.callback(formParams);
                    if(Ext.isEmpty(response.Error_Code))
                        that.hide();
				}
			},
			url:'/?c=MorbusOnkoSpecifics&m=saveMorbusOnkoSopDiagEditForm'
		});
	},
	show: function() {
		var that = this;
		sw.Promed.swMorbusOnkoSopDiagWindow.superclass.show.apply(this, arguments);
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		this.action = arguments[0].action || 'add';
		this.callback = Ext.emptyFn;
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		this.onHide = Ext.emptyFn;
		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		this.form.reset();
		if ( 'add' != this.action && !arguments[0].formParams.MorbusOnkoBaseDiagLink_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_verno_ukazanyi_vhodnyie_dannyie_1'], function() { that.hide(); });
			return false;
		}
		if ( 'add' == this.action && !arguments[0].formParams.MorbusOnkoBase_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_verno_ukazanyi_vhodnyie_dannyie_2'], function() { that.hide(); });
			return false;
		}
		this.form.setValues(arguments[0].formParams);
		switch (this.action) {
			case 'add':
				this.setTitle(this.winTitle +lang['_dobavlenie']);
				break;
			case 'edit':
				this.setTitle(this.winTitle +lang['_redaktirovanie']);
				var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет загрузка..."});
				loadMask.show();
				var formParams = arguments[0].formParams;
				Ext.Ajax.request({
					failure:function () {
						loadMask.hide();
					},
					params: formParams,
					success: function (result) {
						loadMask.hide();
						if (result.responseText) {
							var response = Ext.util.JSON.decode(result.responseText);
		                    if(!Ext.isEmpty(response.Error_Msg)){
		                    	that.hide();
		                    }
		                    if(response && response[0] && response[0].MorbusOnkoBaseDiagLink_id){
		                    	that.form.setValues(response[0]);
		                    	if(response[0].Diag_id){
		                    		var Diag_id = response[0].Diag_id;
		                    		that.form.findField('Diag_id').getStore().load({
		                    			params:{where:" where Diag_id="+Diag_id},
		                    			callback:function(){
		                    				that.form.findField('Diag_id').setValue(Diag_id);
		                    			}
		                    		});
		                    	}   
		                    }
						}
					},
					url:'/?c=MorbusOnkoSpecifics&m=loadMorbusOnkoSopDiagEditForm'
				});
				break;
			case 'view':
				this.setTitle(this.winTitle +lang['_prosmotr']);
				break;
		}
	},
	initComponent: function() {
		var that = this;
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: false,
			id: 'MorbusOnkoSopDiagEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [{
				name: 'MorbusOnkoBaseDiagLink_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnko_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoBase_id',
				xtype: 'hidden'
			}, {
				fieldLabel: 'Сопутствующее заболевание',
				hiddenName: 'Diag_id',
				xtype: 'swdiagcombo',
				allowBlank: false,
				width: 400
			}],
			url:'/?c=MorbusOnkoSpecifics&m=saveMorbusOnkoSopDiagEditForm',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusOnkoBaseDiagLink_id'}, 
				{name: 'MorbusOnko_id'}, 
				{name: 'Diag_id'}, 
				{name: 'MorbusOnkoBase_id'} 
			])
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					that.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					that.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[that.formPanel]
		});
		sw.Promed.swMorbusOnkoSopDiagWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.formPanel.getForm();
	}
});