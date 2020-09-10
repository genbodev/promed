/**
 * @package
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Melentiev
 * @version      22.04.2010
 */

sw.Promed.swTrafficAccidentProductionCriminalInjuryJournal  = Ext.extend(sw.Promed.BaseForm, {
	id: 'swTrafficAccidentProductionCriminalInjuryJournal',
	title: 'Журнал ДТП, производственных и криминальных травм',
	width:410,
	autoHeight: true,
	resizable: false,
	plain: true,
	modal: true,
	autoScroll: false,
	closeAction: 'hide',
	createReport:function(){
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		if (!base_form.isValid())
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function(){	wnd.formMode = 'iddle';	},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return;
		}

		var params = {};
		params.kindJournal_id = base_form.findField('Journal_id').getValue();
		var begDate_endDate = base_form.findField('Journal_begDate_endDate').value.split(' - ');
		params.begDate = begDate_endDate[0];
		params.endDate = begDate_endDate[1];
		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите, идет формирование отчета..." });
		wnd.TextPanel.hide();
		loadMask.show();

		Ext.Ajax.request({
			timeout: 3600000,
			failure: function(response, options) {
				wnd.formMode = 'iddle';
				loadMask.hide();
			},
			params: params,
			success: function(response, action) {
				wnd.formMode = 'iddle';
				loadMask.hide();

				if ( response.responseText ) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if ( answer.success ) {
						var filename = answer.Link.replace("export/reports/", "");
						wnd.TextPanel.getEl().dom.innerHTML = '<div style="text-align:center;padding:12px;font-size:16px;"><a target="_blank" href="'+answer.Link+'">Скачать '+filename+'</a></div>';
						wnd.TextPanel.render();
						wnd.TextPanel.show();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], !Ext.isEmpty(answer.Error_Msg) ? answer.Error_Msg : lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			},
			url: '/?c=EvnPS&m=RunReportsInjuryJournal'
		});

	},
	show: function() {
		sw.Promed.swTrafficAccidentProductionCriminalInjuryJournal.superclass.show.apply(this, arguments);
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();
		wnd.formMode = 'iddle';
		wnd.TextPanel.getEl().dom.innerHTML = '';
		base_form.findField('Journal_id').setValue('');
		this.center();
	},
	initComponent: function() {
		var wnd = this;

		wnd.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			html: ''
		});


		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			autoWidth: true,
			autoheight: true,
			bodyStyle: 'padding: 5px;',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 170,
			region: 'center',
			items: [
				{
					fieldLabel: '<b>Вид журнала <span style="color:red">*</span></b>',
					allowBlank: false,
					name: 'Journal_id',
					queryMode: 'local',
					hiddenName: 'Journal_id',
					displayField: 'Journal_Name',
					valueField: 'Journal_id',
					width: 200,
					store: new Ext.data.SimpleStore({
						autoLoad: false,
						key: 'Form_id',
						editable: false,
						fields: [{name:'Journal_id', type:'int'},{name:'Journal_Name',type:'string'}],
						data: [['1','Журнал травм при ДТП'],['2','Журнал производственных травм'],['3','Журнал криминальных травм']]
					}),
					xtype: 'swbaselocalcombo',
					listeners: {
						'select': function(){
							wnd.TextPanel.getEl().dom.innerHTML = '';
						}
					}
				},{
					fieldLabel: '<b>Дата начала, окончания <span style="color:red">*</span></b>',
					allowBlank: false,
					name: 'Journal_begDate_endDate',
					hiddenName: 'Journal_begDate_endDate',
					valueField: 'Journal_begDate_endDate',
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
					],
					value: new Date().format('d.m.Y') + ' - ' + new Date().format('d.m.Y'),
					width  : 200,
					xtype: 'daterangefield',
					listeners: {
						'select': function(){
							wnd.TextPanel.getEl().dom.innerHTML = '';
						}
					}
				},
				wnd.TextPanel
			]
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel
			],
			buttons: [
				{
					handler: function () {
						wnd.createReport();
					}.createDelegate(this),
					iconCls: 'refresh16',
					text: lang['sformirovat']
				},{
					text: '-'
				},
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: lang['zakryit']
				}]
		});


		sw.Promed.swTrafficAccidentProductionCriminalInjuryJournal.superclass.initComponent.apply(this, arguments);
	}
});
