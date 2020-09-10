/**
* Форма комментария невключения извещения по онкологии в регистр 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Alexander Kurakin
* @copyright    Copyright (c) 2017 Swan Ltd.
* @version      2017
*/

sw.Promed.swEvnOnkoNotifyNotIncludeCommentWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 450,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	owner: null,
	shim: false,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swEvnOnkoNotifyNotIncludeCommentWindow',
	
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},
	
	show: function() {
		sw.Promed.swEvnOnkoNotifyNotIncludeCommentWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		this.option = {};
		this.callback = null;

		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if( arguments[0].option ) {
			this.option = arguments[0].option;
		}

		this.action = arguments[0].action;

		this.setTitle('Комментарий');
		
		var bf = this.Form.getForm();

		bf.setValues(arguments[0]);
		
		this.disableFields( this.action == 'view' );
		this.buttons[0].setDisabled( this.action == 'view' );

		this.center();
	},

	doSave: function() {
		var bf = this.Form.getForm();
		if( !bf.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_korrektno']);
			return false;
		}

		var params = this.option;
		params.EvnOnkoNotify_Comment = bf.findField('EvnOnkoNotify_Comment').getValue();
		if(params.EvnOnkoNotify_Comment.length > 720){
			this.fromStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'], 'Комментарий должен содержать не более 720 символов');
			return false;
		}

		if(typeof this.callback == 'function'){
			this.callback(params);
		} else {
			sw.swMsg.alert(lang['oshibka'], 'Ошибка при попытке сохранения комментария');
			return false;
		}
		this.hide();
	},
	
	disableFields: function(s) {
		this.Form.findBy(function(f) {
			if( f.xtype && f.xtype != 'hidden' ) {
				f.setDisabled(s);
			}
		});
	},
	
	initComponent: function() {

		this.Form = new Ext.FormPanel({
			frame: true,
			defaults: {
				labelAlign: 'right'
			},
			layout: 'form',
			labelWidth: 100,
			items: [{
				layout: 'form',
				items: [{
					layout: 'form',
					items: [{
						fieldLabel : 'Комментарий',
						name: 'EvnOnkoNotify_Comment',
						width: 300,
						xtype: 'textarea'
					}]
				}]
			}]
		});
		
		Ext.apply(this, {
			items: [this.Form],
			buttons: [{
				handler: this.doSave,
				scope: this,
				iconCls: 'save16',
				text: lang['sohranit']
			},
			'-',
			{
				text: lang['otmena'],
				tabIndex: -1,
				tooltip: lang['otmena'],
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}]
		});
		sw.Promed.swEvnOnkoNotifyNotIncludeCommentWindow.superclass.initComponent.apply(this, arguments);
	}
});