/**
 * swImportPersonRpnWindow - окно получениея пациентов, прикрепленных к МО из РПН Казахстана
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			02.09.2015
 */

/*NO PARSE JSON*/

sw.Promed.swImportPersonRpnWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'swImportPersonRpnWindow',
	title: lang['poluchenie_dannyih_patsientov_s_portala_rpn'],
	width: 450,
	//layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	inProcess: false,

	listeners: {
		'beforehide': function(win) {
			win.inProcess = false;
			//win.importMask.hide();
			clearInterval(win.intervalId);
			win.intervalId = null;
		}
	},

	initComponent: function()
	{

		this.ImportTpl = new Ext.Template(
		[
			'<div>{message}</div>'
		]);

		this.ImportPanel = new Ext.Panel(
		{
			id: 'IPRW_ImportPanel',
			bodyStyle: 'padding:2px',
			style: 'font-size: 12px;',
			layout: 'fit',
			border: false,
			frame: false,
			height: 85,
			//maxSize: 30,
			html: ''
		});

		this.Panel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			frame: true,
			id: 'IPRW_ImportPanelPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [
				{
					bodyStyle: 'padding:2px',
					style: 'font-size: 12px;',
					border: false,
					html: lang['poluchenie_dannyih_patsientov_i_ih_aktivnyih_prikrepleniy_po_mo_s_portala_rpn']
				},
				this.ImportPanel
			]
		});

		Ext.apply(this,
		{
			autoHeight: true,
			buttons: [
			{
				id: 'IPRW_StartImportBtn',
				handler: function()
				{
					this.ownerCt.startImport();
				},
				iconCls: 'start16',
				text: lang['nachat_zagruzku'],
				tooltip: lang['nachat_zagruzku']

			},
			{
				id: 'IPRW_StopImportBtn',
				handler: function()
				{
					this.ownerCt.stopImport();
				},
				iconCls: 'stop16',
				text: lang['ostanovit_zagruzku'],
				tooltip: lang['ostanovit_zagruzku']
			},
			{
				id: 'IPRW_ResetImportBtn',
				handler: function()
				{
					this.ownerCt.resetImport();
				},
				//iconCls: 'stop16',
				text: 'Сбросить загрузку',
				tooltip: 'Сбросить состояние загрузки'
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [this.Panel]
		});
		sw.Promed.swImportPersonRpnWindow.superclass.initComponent.apply(this, arguments);
	},
	setInProcess: function(value) {
		var win = this;
		win.inProcess = value;
		if (value) {
			win.inProcess = true;
			Ext.getCmp('IPRW_ResetImportBtn').disable();
			Ext.getCmp('IPRW_StartImportBtn').hide();
			Ext.getCmp('IPRW_StopImportBtn').show();
			if (win.stop) {
				Ext.getCmp('IPRW_StopImportBtn').disable();
				//win.importMask = new Ext.LoadMask(win.Panel.getEl(), { msg: "Остановка загрузки данных. Подождите..." });
			} else {
				//win.importMask = new Ext.LoadMask(win.Panel.getEl(), { msg: "Выполняется загрузка данных. Подождите..." });
				Ext.getCmp('IPRW_StopImportBtn').enable();
			}
			//win.importMask.show();
		} else {
			win.inProcess = false;
			//win.importMask.hide();
			Ext.getCmp('IPRW_ResetImportBtn').enable();
			Ext.getCmp('IPRW_StartImportBtn').show();
			Ext.getCmp('IPRW_StopImportBtn').hide();
			Ext.getCmp('IPRW_StartImportBtn').enable();
			Ext.getCmp('IPRW_StopImportBtn').enable();
		}
	},
	checkImportStatus: function() {
		var win = this;

		Ext.Ajax.request({
			url: '/?c=ServiceRPN&m=checkImportPersonListStatus',
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				var stopTimeLimit = 30*60;

				if (response_obj.success) {
					if (!Ext.isEmpty(response_obj.Error_Msg)) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
					}
					if (!Ext.isEmpty(response_obj.message)) {
						win.ImportTpl.overwrite(win.ImportPanel.body, {
							message: lang['rezultat'] + ': ' + response_obj.message
						});
					}
					win.stop = (response_obj.stop==1);
					if (!Ext.isEmpty(response_obj.inProcess)) {
						win.setInProcess(response_obj.inProcess==1);
					} else {
						win.setInProcess(false);
					}

					if (response_obj.stop && response_obj.stopTime && response_obj.time && (response_obj.time - response_obj.stopTime) > stopTimeLimit) {
						win.resetImportParams(['inProcess','stop','stopTime']);
					}
				} else {
					win.setInProcess(false);
				}
			},
			failure:function(response) {
			}
		});
	},
	startImport: function()
	{
		var win = this;

		win.setInProcess(false);

		Ext.Ajax.request({
			url: '/?c=ServiceRPN&m=startImportPersonList',
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.Error_Msg) {
					sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
				}
			},
			failure:function(response) {
			}
		});

		win.setInProcess(true);

		setTimeout(function(){
			win.checkImportStatus();
		}, 500);
	},
	stopImport: function()
	{
		var win = this;

		/*win.importMask.hide();
		win.importMask = new Ext.LoadMask(win.Panel.getEl(), { msg: "Остановка загрузки данных. Подождите..." });
		win.importMask.show();*/

		Ext.Ajax.request({
			url: '/?c=ServiceRPN&m=stopImportPersonList',
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				win.checkImportStatus();

				if (response_obj.Error_Msg) {
					sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
				}
			},
			failure:function(response) {
			}
		});
	},
	resetImport: function()
	{
		var win = this;

		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					win.resetImportParams();
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:'Сбросить состояние загрузки?',
			title:'Подтверждение'
		});
	},
	resetImportParams: function(paramList)
	{
		var win = this;
		var params = {};

		if (Ext.isArray(paramList)) {
			params.paramList = Ext.util.JSON.encode(paramList);
		}

		/*win.importMask.hide();
		win.importMask = new Ext.LoadMask(win.Panel.getEl(), { msg: "Сброс состояния загрузки. Подождите..." });
		win.importMask.show();*/

		Ext.Ajax.request({
			url: '/?c=ServiceRPN&m=resetImportPersonListParams',
			params: params,
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				win.checkImportStatus();

				if (response_obj.Error_Msg) {
					sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
				}
			},
			failure:function(response) {
			}
		});
	},
	show: function()
	{
		sw.Promed.swImportPersonRpnWindow.superclass.show.apply(this, arguments);

		this.stop = null;
		this.intervalId = null;
		this.inProcess = null;

		Ext.getCmp('IPRW_StartImportBtn').show();
		Ext.getCmp('IPRW_StopImportBtn').hide();
		Ext.getCmp('IPRW_ResetImportBtn').disable();

		this.ImportTpl.overwrite(this.ImportPanel.body,{});

		/*this.importMask = new Ext.LoadMask(this.Panel.getEl(), { msg: "Проверка статуса загрузки. Подождите..." });
		this.importMask.show();*/

		if (!this.intervalId) {
			this.intervalId = setInterval(this.checkImportStatus.createDelegate(this), 7000);
		}
		this.checkImportStatus();
	}
});