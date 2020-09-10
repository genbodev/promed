/**
* swReagentConsumptionParamsSelectWindow - окно учета расхода реактивов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Salakhov R.
* @version      11.2013
* @comment      
*/
sw.Promed.swReagentConsumptionParamsSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['uchet_rashoda_reaktivov_vyibor_parametrov'],
	layout: 'border',
	id: 'ReagentConsumptionParamsSelectWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 150,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSelect:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('ReagentConsumptionParamsSelectForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var params = this.form.getValues();
		this.onSelect(params);
		this.hide();
		return true;		
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swReagentConsumptionParamsSelectWindow.superclass.show.apply(this, arguments);

		this.onSelect = Ext.emptyFn;
		if (arguments[0] && arguments[0].onSelect && typeof(arguments[0].onSelect) == 'function') {
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
			height: 105,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'ReagentConsumptionParamsSelectForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 170,
				collapsible: true,
				items: [{
					allowBlank: false,
					comboSubject: 'DrugFinance',
					fieldLabel: lang['istochnik_finansirovaniya'],
					hiddenName: 'DrugFinance_id',
					anchor: '100%',
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					comboSubject: 'WhsDocumentCostItemType',
					fieldLabel: lang['statya_rashoda'],
					hiddenName: 'WhsDocumentCostItemType_id',
					anchor: '100%',
					xtype: 'swcommonsprcombo'
				}]
			}]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
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
			items:[
				form
			]
		});
		sw.Promed.swReagentConsumptionParamsSelectWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('ReagentConsumptionParamsSelectForm').getForm();
	}
});