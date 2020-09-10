/**
* swRrlExportWindow - окно выгрузки регистра региональных льготников.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 - 2012 Swan Ltd.
* @author       Vlasenko Dmitry
* @version      23.04.2012
* @comment      Префикс для id компонентов RRLEW (RrlExportWindow)
*
*/

sw.Promed.swRrlExportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'RrlExportWindow',
	title: lang['vyigruzka_rrl'],
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function()
	{
		var that = this;
		
		this.Panel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'RrlExportForm',
			labelAlign: 'right',
			labelWidth: 200,
			layout: 'form',
			items: [{
				checked: true,
				name: 'PersonPrivilege_onlyValid',
				fieldLabel: lang['tolko_deystvuyuschie_lgotyi'],
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['data_nachala_lgotyi'],
				name: 'PersonPrivilege_begDate',
				format: 'd.m.Y',
				width: 100,
				xtype: 'swdatefield',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}, {
				fieldLabel: lang['data_okonchaniya_lgotyi'],
				name: 'PersonPrivilege_endDate',
				format: 'd.m.Y',
				width: 100,
				xtype: 'swdatefield',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}, {
				fieldLabel: lang['data_nachala_lgotyi_ot'],
				name: 'PersonPrivilege_begDateFrom',
				format: 'd.m.Y',
				width: 100,
				xtype: 'swdatefield',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}, {
				fieldLabel: lang['data_nachala_lgotyi_do'],
				name: 'PersonPrivilege_begDateTo',
				format: 'd.m.Y',
				width: 100,
				xtype: 'swdatefield',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}, {
				fieldLabel: lang['data_okonchaniya_lgotyi_ot'],
				name: 'PersonPrivilege_endDateFrom',
				format: 'd.m.Y',
				width: 100,
				xtype: 'swdatefield',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}, {
				fieldLabel: lang['data_okonchaniya_lgotyi_do'],
				name: 'PersonPrivilege_endDateTo',
				format: 'd.m.Y',
				width: 100,
				xtype: 'swdatefield',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}],
			validateOnBlur: true
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				id: 'RRLEW_Ok',
				handler: function() 
				{
					that.doExport();
				},
				iconCls: 'refresh16',
				text: lang['vyigruzit']
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
				onTabElement: 'RRLEW_Ok',
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swRrlExportWindow.superclass.initComponent.apply(this, arguments);
	},
	doExport: function()
	{
		var that = this;
		var base_form = this.findById('RrlExportForm').getForm();
		
		var params = {
			PersonPrivilege_begDate: Ext.util.Format.date(base_form.findField('PersonPrivilege_begDate').getValue(), 'd.m.Y'),
			PersonPrivilege_endDate: Ext.util.Format.date(base_form.findField('PersonPrivilege_endDate').getValue(), 'd.m.Y'),
			PersonPrivilege_begDateFrom: Ext.util.Format.date(base_form.findField('PersonPrivilege_begDateFrom').getValue(), 'd.m.Y'),
			PersonPrivilege_endDateFrom: Ext.util.Format.date(base_form.findField('PersonPrivilege_endDateFrom').getValue(), 'd.m.Y'),
			PersonPrivilege_begDateTo: Ext.util.Format.date(base_form.findField('PersonPrivilege_begDateTo').getValue(), 'd.m.Y'),
			PersonPrivilege_endDateTo: Ext.util.Format.date(base_form.findField('PersonPrivilege_endDateTo').getValue(), 'd.m.Y'),
			PersonPrivilege_onlyValid: base_form.findField('PersonPrivilege_onlyValid').getValue() === true ? 1 : 0
		};
		
		that.getLoadMask().show();
		
		Ext.Ajax.request(
		{
			url: '/?c=Privilege&m=ExportRrl',
			params: params,
			callback: function(options, success, response) 
			{
				that.getLoadMask().hide();
				if ( success )
				{
					var result = Ext.util.JSON.decode(response.responseText);

					files = 'Результат экспорта РРЛ: <a target="_blank" href="' + result['link'] + '">' + result['filename'] + '</a>';
					
					if (result['errorlink'].length > 0) {
						files = files + '<br>Есть записи с ошибками: <a target="_blank" href="' + result['errorlink'] + '">' + result['errorfilename'] + '</a>';
					}

					Ext.Msg.alert(lang['zagruzka_fayla'], files);
				}
				else 
				{
					var result = Ext.util.JSON.decode(response.responseText);
					Ext.Msg.alert(lang['oshibka'], result.Error_Msg);
				}
			}
		});
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
		}
		return this.loadMask;
	},
	show: function() 
	{
		sw.Promed.swRrlExportWindow.superclass.show.apply(this, arguments);
		var base_form = this.findById('RrlExportForm').getForm();
		base_form.findField('PersonPrivilege_onlyValid').focus(true);
	}
});