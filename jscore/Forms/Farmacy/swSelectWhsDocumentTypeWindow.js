/**
* swSelectWhsDocumentTypeWindow - окно выбора типа договора поставки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      08.12.2012
*/

sw.Promed.swSelectWhsDocumentTypeWindow = Ext.extend(sw.Promed.BaseForm, {
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
	title: lang['vyibor_tipa_dogovora'],
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	show: function()  {
		sw.Promed.swSelectWhsDocumentTypeWindow.superclass.show.apply(this, arguments);
		var form = this.findById('SelectWhsDocumentTypeForm');
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
		
		this.SelectWhsDocumentTypeForm.getForm().reset();

		this.buttons[0].focus();
	},
	initComponent: function() {
		var wnd = this;
		
		this.SelectWhsDocumentTypeForm = new Ext.form.FormPanel({
			autoHeight: true,
			layout: 'form',
			border: false,
			bodyStyle:'width:100%;background-color:transparent;padding:5px;',
			frame: true,
			labelWidth: 40,
			items: [{
				fieldLabel: lang['tip'],
				hiddenName: 'WhsDocumentType_id',
				xtype: 'swcommonsprcombo',
				sortField:'WhsDocumentType_Code',
				comboSubject: 'WhsDocumentType',
				width: 300,
				allowBlank:true,
				initComponent: function() {									
					sw.Promed.SwCommonSprCombo.prototype.initComponent.apply(this, arguments);														
					this.store.addListener('load', function(store){
						store.each(function(rec) { //удаляем все кроме 3 - "Контракт на поставку", 6 - "Контракт на поставку и отпуск" и 18 - "Контракт ввода остатков"
							if (rec.get('WhsDocumentType_Code') != 3 && rec.get('WhsDocumentType_Code') != 6 && rec.get('WhsDocumentType_Code') != 18) {
								store.remove(rec);
							}
						});
					});
				}
			}]
		});
		Ext.apply(this, {
			items : [this.SelectWhsDocumentTypeForm],
			buttons : 
			[{
				text : lang['vyibrat'],
				iconCls : 'ok16',
				handler : function(button, event) {
					var type_combo = wnd.SelectWhsDocumentTypeForm.getForm().findField('WhsDocumentType_id');
					var type_id = type_combo.getValue();
					if (type_id > 0) {
						var type_rec_num = type_combo.store.findBy(function(rec) { return rec.get('WhsDocumentType_id') == type_id; });
						wnd.onSelect({
							WhsDocumentType_id: type_id,
							WhsDocumentType_Name: type_rec_num >= 0 ? type_combo.store.getAt(type_rec_num).get('WhsDocumentType_Name') : null
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
		sw.Promed.swSelectWhsDocumentTypeWindow.superclass.initComponent.apply(this, arguments);
	}
});