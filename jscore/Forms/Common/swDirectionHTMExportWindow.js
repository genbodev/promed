/**
* swDirectionHTMExportWindow - окно выгрузки остатков и поставок по ОНЛС и ВЗН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Aleksandr Chebukin
* @version      03.2016
* @comment      
*/
sw.Promed.swDirectionHTMExportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Выгрузка направлений на ВМП',
	layout: 'border',
	id: 'DirectionHTMExportWindow',
	modal: true,
	shim: false,
	width: 350,
	height: 160,
	resizable: false,
	maximizable: false,
	maximized: false,
	setDefaultValues: function() {
		var curr_date = new Date();
		var last_month = curr_date.getMonth();
		if (last_month > 0) {
			this.form.findField('Year').setValue(curr_date.getFullYear());
			this.form.findField('Month').setValue(last_month);
		} else {
			this.form.findField('Year').setValue(curr_date.getFullYear()-1);
			this.form.findField('Month').setValue(12);
		}
	},
	baseName: function(str) {
		var base = new String(str).substring(str.lastIndexOf('/') + 1);
		return base;
	},
	doExport:  function() {
		var wnd = this;
		var params = new Object();

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DirectionHTMExportForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		params = wnd.form.getValues();
		params.Month = wnd.form.findField('Month').getValue();

		wnd.getLoadMask(lang['formirovanie_fayla']).show();
		wnd.TextPanel.getEl().dom.innerHTML = '';
		wnd.setHeight(160);
		Ext.Ajax.request({
			scope: this,
			params: params,
			url:'/?c=EvnDirectionHTM&m=exportDirectionHTM',
			callback: function(options, success, response) {
				wnd.getLoadMask().hide();
				if (success && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.link) {
						wnd.setHeight(160 + result.link.length * 15);
						for (var i=0; i < result.link.length; i++) {
							wnd.TextPanel.getEl().dom.innerHTML += '<a target="_blank" href="'+result.link[i]+'">Скачать архив: '+this.baseName(result.link[i])+'</a><br>';
						}
					}
				}
			}
		});

		return true;		
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDirectionHTMExportWindow.superclass.show.apply(this, arguments);		

		this.form.reset();
		this.setDefaultValues();
		this.TextPanel.getEl().dom.innerHTML = '';
		this.setHeight(160);
		this.doLayout();
	},
	initComponent: function() {
		var wnd = this;

		this.monthCombo = new Ext.form.ComboBox({
			allowBlank: false,
			fieldLabel: 'Месяц',
			width: 150,
			triggerAction: 'all',
			store: [
				[1, lang['yanvar']],
				[2, lang['fevral']],
				[3, lang['mart']],
				[4, lang['aprel']],
				[5, lang['may']],
				[6, lang['iyun']],
				[7, lang['iyul']],
				[8, lang['avgust']],
				[9, lang['sentyabr']],
				[10, lang['oktyabr']],
				[11, lang['noyabr']],
				[12, lang['dekabr']]
			],
			name: 'Month'
		});
		
		this.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			style: 'padding: 10px 5px 0; text-align: center;',
			html: ''
		});
		
		this.FormPanel = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'DirectionHTMExportForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 100,
				labelAlign: 'right',
				collapsible: true,
				items: [{
					xtype: 'numberfield',
					fieldLabel: 'Год',
					name: 'Year',
					allowDecimals: false,
					allowNegative: false,
					allowBlank: false,
					width: 70,
					plugins: [new Ext.ux.InputTextMask('9999', false)],
					minLength: 4
				},
				wnd.monthCombo,
				{
					xtype: 'swcommonsprcombo',
					name: 'HTMFinance_id',
					comboSubject: 'HTMFinance',
					hiddenName: 'HTMFinance_id',
					fieldLabel: lang['vid_oplatyi'],
					width: 150,
					labelWidth: 300,
					listeners: {
						render: function() {
							if(this.getStore().getCount()==0)
								this.getStore().load();
						}
					}
				},
					wnd.TextPanel
				]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:[{
				handler: function() 
				{
					this.ownerCt.doExport();
				},
				iconCls: 'ok16',
				text: 'Сформировать'
			}, {
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.FormPanel]
		});
		sw.Promed.swDirectionHTMExportWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('DirectionHTMExportForm').getForm();
	}	
});