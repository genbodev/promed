/**
* swSelectPersonRegisterTypeWindow - окно выбора типа регистра
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Salakhov R.
* @version      10.07.2013
*/

sw.Promed.swSelectPersonRegisterTypeWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: false,
	width : 400,
	height : 115,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	onHide: Ext.emptyFn,
	onSelect: Ext.emptyFn,
	callback: Ext.emptyFn,
	title: lang['vyibor_tipa_registra'],
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	show: function()  {
		sw.Promed.swSelectPersonRegisterTypeWindow.superclass.show.apply(this, arguments);
		var form = this.findById('SelectPersonRegisterTypeForm');
		if (!arguments[0]) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}		
		if (arguments[0].callback)
			this.callback = arguments[0].callback;
		if (arguments[0].onHide)
			this.onHide = arguments[0].onHide;

		this.onSelect = (arguments[0].onSelect) ? arguments[0].onSelect : Ext.emptyFn;
		
		this.SelectPersonRegisterTypeForm.getForm().reset();

		this.buttons[0].focus();
	},
	initComponent: function() {
		var wnd = this;
		
		this.SelectPersonRegisterTypeForm = new Ext.form.FormPanel({
			autoHeight: true,
			layout: 'form',
			border: false,
			bodyStyle:'width:100%;background-color:transparent;padding:5px;',
			frame: true,
			labelWidth: 40,
			items: [{
				fieldLabel: lang['tip'],
				hiddenName: 'PersonRegisterType_id',
				xtype: 'swcommonsprcombo',
				sortField:'PersonRegisterType_Code',
				comboSubject: 'PersonRegisterType',
				width: 300,
				allowBlank:true
			}]
		});
		Ext.apply(this, {
			items : [this.SelectPersonRegisterTypeForm],
			buttons : 
			[{
				text : lang['vyibrat'],
				iconCls : 'ok16',
				handler : function(button, event) {
					var type_combo = wnd.SelectPersonRegisterTypeForm.getForm().findField('PersonRegisterType_id');
					var type_id = type_combo.getValue();
					if (type_id > 0) {
						var type_rec_num = type_combo.store.findBy(function(rec) { return rec.get('PersonRegisterType_id') == type_id; });
						wnd.onSelect({
							PersonRegisterType_id: type_id,
							PersonRegisterType_Name: type_rec_num >= 0 ? type_combo.store.getAt(type_rec_num).get('PersonRegisterType_Name') : null
						});
						wnd.hide();
					}
				}.createDelegate(this)
			}, 
			{
				text: '-'
			}, 
			HelpButton(this),
			{
				id: 'sowCancelButton',
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: lang['otmena']
			}
			],
			buttonAlign : "right"
		});
		sw.Promed.swSelectPersonRegisterTypeWindow.superclass.initComponent.apply(this, arguments);
	}
});