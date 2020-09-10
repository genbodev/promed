/**
* Проверка списка ЛПУ, содержащегося в отправляемом файле на наличие их в БД Промед
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      18.05.2012
*/

sw.Promed.swUploadFileForCheckLpuWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: langs('Проверить ЛПУ ФРМР'),
	modal: true,
	height: 100,
	width: 400,
	shim: false,
	plain: true,
	buttonAlign: 'right',
	closeAction: 'hide',
	id: 'swUploadFileForCheckLpuWindow',
	
	show: function()
	{
		sw.Promed.swUploadFileForCheckLpuWindow.superclass.show.apply(this, arguments);
		
		this.center();
	},
	
	listeners: {
		hide: function() {
			this.showLink(false);
			this.FormPanel.getForm().findField('file').reset();
		}
	},
	
	createReport: function() {
		var form = this.FormPanel.getForm();
		if( form.findField('file').getValue() == '' )
			return;
		
		form.submit({
			success: function(f, r) {
				var obj = Ext.util.JSON.decode(r.response.responseText);
				if( obj.success ) {
					var lf = this.FormPanel.find('name', 'link')[0],
						ff = this.FormPanel.find('name', 'filerow')[0];
					
					lf.tpl = new Ext.Template(this.linkTpl);
					lf.tpl.overwrite(lf.body, obj);
					this.showLink(true);
				} else {
					sw.swMsg.alert(lang['oshibka'], obj.Error_Msg);
				}
			}.createDelegate(this)
		});
	},
	
	showLink: function(m) {
		var form = this.FormPanel,
			lf = form.find('name', 'link')[0],
			ff = form.find('name', 'filerow')[0];
		lf.setVisible(m);
		ff.setVisible(!m);
		this.buttons[0].setDisabled(m);
		this.doLayout();
	},
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.linkTpl = '<a href="{Link}" target="_blank" style="font-size: 10pt">скачать отчет</a>';
		
		this.FormPanel = new Ext.FormPanel({
			layout: 'form',
			frame: true,
			url: '/?c=LpuStructure&m=checkLpuFRMP2Dbf',
			fileUpload: true,
			labelWidth: 35,
			labelAlign: 'right',
			items: [
				{
					layout: 'form',
					name: 'filerow',
					items: [
						{
							xtype: 'fileuploadfield',
							anchor: '100%',
							name: 'file',
							buttonText: lang['vyibrat'],
							fieldLabel: lang['fayl'],
							listeners: {
								fileselected: function(f, v) {
									if( f.hidden ) return false;
									var accessTypes = ['csv']; // TO-DO: для xml тоже надо будет сделать
									if( !(new RegExp(accessTypes.join('|'), 'ig')).test(v) ) {
										var errmsg = lang['dannyiy_tip_fayla_ne_podderjivaetsya_podderjivaemyie_tipyi'];
										for(var i=0; i<accessTypes.length; i++) {
											errmsg += '*' + accessTypes[i] + (i+1 < accessTypes.length ? ', ' : '' );
										}
										sw.swMsg.alert(lang['oshibka'], errmsg, f.reset.createDelegate(f));
										return false;
									}
								}
							}
						}
					]
				}, {
					name: 'link',
					hidden: true
				}
			]
		});
		
		
		Ext.apply(this,	{
			items: [this.FormPanel],
			buttons: [
				{
					text: lang['sformirovat'],
					tooltip: lang['sformirovat_otchet'],
					iconCls: 'refresh16',
					handler: this.createReport.createDelegate(this)
				},
				'-',
				{
					text: lang['zakryit'],
					tabIndex: -1,
					tooltip: lang['zakryit'],
					iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			]
		});
		sw.Promed.swUploadFileForCheckLpuWindow.superclass.initComponent.apply(this, arguments);
	}
});