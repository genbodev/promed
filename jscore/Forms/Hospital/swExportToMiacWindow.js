/**
* Копия формы Стационар: Форма выгрузки данных по коечному фонду
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      16.12.2011
*/

sw.Promed.swExportToMiacWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['eksport_statistiki_v_miats'],
	modal: true,
	height: 145,
	width: 350,
	shim: false,
	plain: true,
	resizable: false,
	onSelect: Ext.emptyFn,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swExportToMiacWindow',
	closeAction: 'hide',
	id: 'swExportToMiacWindow',
	objectSrc: '/jscore/Forms/Hospital/swExportToMiacWindow.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.exportToDbf();
			},
			iconCls: 'ok16',
			text: lang['vyigruzit_v_dbf']
		},
		'-',
		{
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		hide: function(w){
			w.overwriteTpl();
			w.CommonForm.getForm().reset();
		}
	},
	
	show: function()
	{
		sw.Promed.swExportToMiacWindow.superclass.show.apply(this, arguments);
		
		this.CommonForm.getForm().findField('begDate').focus(true, 100);
		this.syncSize();
		this.center();
	},
	
	exportToDbf: function(){
		var form = this.CommonForm.getForm(),
			lm = this.getLoadMask(lang['vyigruzka']),
			that = this;
		if(!form.isValid()){
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya']);
			return false;
		}
		this.overwriteTpl();
		lm.show();
		form.submit({
            timeout: 1210,
            failure: function(f, r){
				lm.hide();
				var obj = Ext.util.JSON.decode(r.response.responseText);
				if (!obj.warning) {
					obj.warning = lang['v_protsesse_vyigruzki_proizoshli_oshibki_pojaluysta_obratites_k_razrabotchikam'];
				}
				sw.swMsg.alert(lang['vyigruzka_ne_vyipolnena'], obj.warning);
				lm.hide();
			},
			success: function(f, r){
				lm.hide();
				var obj = Ext.util.JSON.decode(r.response.responseText),
					msg = lang['vyigruzka_uspeshno_vyipolnena'];
				msg += '<a target="_blank" href="'+obj.Link+'">скачать</a>';
				that.overwriteTpl({msg: msg});
				if (obj.warning) {
					sw.swMsg.alert(lang['vyigruzka_vyipolnena_s_preduprejdeniyami'], obj.warning);
				}
			}
		});
		
	},
	
	overwriteTpl: function(obj)
	{
		var h = 145;
		if(!obj){
			var obj = {};
			obj.msg = '';
		} else {
			h += this.ResultExp.height;
		}
		this.ResultExp.tpl = new Ext.Template(this.Tpl);
		this.ResultExp.tpl.overwrite(this.ResultExp.body, obj);
		this.setHeight(h);
		this.doLayout();
	},
	
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.Tpl = [
			'<tpl for="."><p style="font-size: 10pt;">{msg}</p></tpl>'
		];
		
		this.ResultExp = new sw.Promed.Panel({
			height: 30,
			bodyStyle: 'padding: 10px;',
			tpl: ''
		});
		
		this.CommonForm = new Ext.form.FormPanel({
			url: '/?c=EvnPS&m=exportToDbfBedFond',
			frame: true,
			bodyStyle: 'padding: 3px;',
			items: [
				{
					layout: 'form',
					labelWidth: 120,
					border: false,
					labelAlign: 'right',
					items: [
						{
							xtype: 'swdatefield',
							allowBlank: false,
							name: 'begDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							fieldLabel: lang['data_nachala']
						}, {
							xtype: 'swdatefield',
							allowBlank: false,
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'endDate',
							fieldLabel: lang['data_okonchaniya']
						}				
					]
				},
				this.ResultExp
			]
		});
		
		Ext.apply(this,	{
			bodyStyle: 'padding: 5px;',
			items: [this.CommonForm]
		});
		sw.Promed.swExportToMiacWindow.superclass.initComponent.apply(this, arguments);
	}
});