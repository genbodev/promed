/**
* swDocNakSelectWindow - окно выбора накладной для возврата
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      02.2015
* @comment      
*/
sw.Promed.swDocNakSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['vyibor_nakladnoy_dlya_vozvrata'],
	layout: 'border',
	id: 'DocNakSelectWindow',
	modal: true,
	shim: false,
	width: 450,
	height: 155,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSelect: function() {
		var wnd = this;
		var data = new Object();

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DocNakEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = wnd.form.getValues();
		if (params.DocumentUc_setDate) {
			params.DocumentUc_setDate = wnd.form.findField('DocumentUc_setDate').getValue().dateFormat('Y-m-d');
		}
		params.List = wnd.form.findField('Copy_List').checked ? 1 : null;
		params.Org_id = getGlobalOptions().org_id ? getGlobalOptions().org_id : null;

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if (response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj && !response_obj.Error_Msg) {
						wnd.onSelect(response_obj);
						wnd.hide();
					} else {
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При исполнении документа возникла ошибка');
					}
				}
			},
			params: params,
			url: '/?c=DocumentUc&m=getDocNakData'
		});

		return true;		
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDocNakSelectWindow.superclass.show.apply(this, arguments);		
		this.onSelect = Ext.emptyFn;

		if ( arguments[0].onSelect && typeof arguments[0].onSelect == 'function' ) {
			this.onSelect = arguments[0].onSelect;
		}

		this.form.reset();
	},
	initComponent: function() {
		var wnd = this;		
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'DocNakEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 90,
				collapsible: true,
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'textfield',
							fieldLabel: lang['nakladnaya_№'],
							name: 'DocumentUc_Num',
							allowBlank: false
						}]
					},  {
						layout: 'form',
						labelAlign: 'right',
						labelWidth: 50,
						items: [{
							xtype: 'swdatefield',
							fieldLabel: lang['ot'],
							name: 'DocumentUc_setDate',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							allowBlank: false
						}]
					}]
				}, {
					layout: 'form',
					labelWidth: 205,
					items: [{
						name: 'Copy_List',
						fieldLabel: lang['kopirovat_spisok_medikamentov'],
						xtype: 'checkbox'
					}]
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSelect();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swDocNakSelectWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('DocNakEditForm').getForm();
	}	
});