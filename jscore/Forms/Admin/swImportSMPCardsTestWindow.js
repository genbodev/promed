/**
* swImportSMPCardsTestWindow - форма для проверки импорта карт СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright	Copyright (c) 2013 Swan Ltd.
* @author		Dmitry Vlasenko
* @version		13.07.2013
*/
/*NO PARSE JSON*/
sw.Promed.swImportSMPCardsTestWindow = Ext.extend(sw.Promed.BaseForm, {
	width : 500,
	resizable: false,
	autoHeight: true,
	border : false,
	plain : true,
	id: 'swImportSMPCardsTestWindow',
	show: function() {
		sw.Promed.swImportSMPCardsTestWindow.superclass.show.apply(this, arguments);
		this.center();
		this.formPanel.getForm().reset();
	},
	title: lang['test_importa_kart_smp'],
	initComponent: function() {
		var win = this;
		
		this.formPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px',
			url: '/?c=CmpCallCard&m=importSMPCardsTest',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 150,
			items:
			[{
				name: 'Lpu_Name',
				fieldLabel: lang['nazvanie_lpu'],
				xtype: 'textfield'
			}, {
				name: 'CmpCallCard_insDT1',
				fieldLabel: lang['data_nachala'],
				allowBlank: false,
				xtype: 'swdatefield'
			}, {
				name: 'CmpCallCard_insDT2',
				fieldLabel: lang['data_kontsa'],
				allowBlank: false,
				xtype: 'swdatefield'
			}]
		});
		Ext.apply(this, {
			buttonAlign : "left",
			buttons : 
				  [{
					text : "Выполнить поиск",
					handler : function()
					{
						win.doSave();
					}
				  },
				  {
					text: "-"
				  },
				  HelpButton(this, -1),
				  {
					text : lang['zakryit'],
					iconCls: 'close16',
					handler : function(button, event) {
						win.hide();
					}
				  }],					
			items:
			[
				this.formPanel
			]
		});
		sw.Promed.swImportSMPCardsTestWindow.superclass.initComponent.apply(this, arguments);
	},
	doSave: function()
	{
		var win = this;

		var base_form = this.formPanel.getForm();
		var form = this.formPanel;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		win.getLoadMask(lang['vyipolnyaetsya_poisk_kart_smp']).show();
		base_form.submit(
		{
			failure: function()
			{
				win.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				win.getLoadMask().hide();
				if (!Ext.isEmpty(action.result.cnt)) {
					sw.swMsg.alert(lang['vnimanie'],lang['kolichestvo_kart_smp'] + action.result.cnt);
				} else {
					sw.swMsg.alert(lang['vnimanie'],lang['oshibka_podscheta_kart_smp']);
				}
			}.createDelegate(this)
		});
	}
});