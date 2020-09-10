/**
* swSmoCallCardWindow - окно импорта номеров карт вызовов 110/у
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @author       Магафуров Салават
* @version      16.11.2017
*
*
*/

sw.Promed.swSmoCallCardWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swSmoCallCardWindow',
	title: 'Импорт номеров карт вызовов 110/у',
	width: 600,
	resizable: false,
	autoHeight: true,
	onHide: Ext.emptyFn,

	initComponent: function()
	{
		var wnd = this;
		
		this.CardGrid = new sw.Promed.ViewFrame({
			id: 'CardNumber_Grid',
			autoLoadData: false,
			height: 400,
			title: 'Запрос от СМО',
			stringfields: [
				{ name: 'id', key: true, type: 'int' },
				{ name: 'CardNumber', header: 'Номера карт', width: 200 },
				{ name: 'insDate', header: 'Дата загрузки', width: 200 }
			],
			
			object: 'CardNumber',
			toolbar: false,
			paging: true,
			dataUrl: '/?c=CmpCallCard&m=getSmoQueryCallCards'
		});

		this.SmoCombo = new sw.Promed.SwOrgSMOCombo({
			id: 'SmoOrg_Combo',
			allowBlank: false,
			fieldLabel: lang['naimenovanie_smo'],
			anchor: '100%',
			lastQuery: '',
			hideTrigger2: true,
			editable: false,
			listeners: {
				select: function(combo, record, index){
					wnd.CardGrid.getGrid().getStore().baseParams = {OrgSmo_id:record.get('OrgSMO_id')};
					wnd.CardGrid.getGrid().getStore().load();
				}
			}
		});

		this.SmoCombo.getStore().on('load',function(){
			wnd.SmoCombo.getStore().filterBy(function(record){
				return record.get('KLRgn_id') === 2 && record.get('OrgSMO_endDate') === "";
			});
		});
		
		this.LoadPanel = {
			layout: 'form',
			bodyStyle: 'padding: 10px 0px 0px 0px',
			items: [
				{
					xtype: 'hidden',
					value: 'CallCardsFile',
					name: 'UploadFieldName'
				},
				{
					id: 'fileDir',
					xtype: 'fileuploadfield',
					anchor: '95%',
					emptyText: 'Выберите файл txt',
					allowBlank:false,
					fieldLabel: 'Файл номеров карт',
					name: 'CallCardsFile'
				}]
		};

		this.MainPanel = new Ext.FormPanel({
			id: 'mainPanel',
			autoHeight: true,
			bodyBorder: false,
			frame: true,
			fileUpload: true,
			labelWidth: 150,
			items: [ 
				this.SmoCombo, 
				this.CardGrid,
				this.LoadPanel
			]
		});

		Ext.apply(this,
		{
			buttons: [
			{
				id: 'rifOk',
				handler: function()
				{ 
					this.ownerCt.doSave();
				},
				iconCls: 'refresh16',
				text: 'Импортировать файл'
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
				onTabElement: 'rifOk',
				text: BTN_FRMCANCEL
			}],
			items: [this.MainPanel]
		});	

		sw.Promed.swSmoCallCardWindow.superclass.initComponent.apply(this, arguments);
	},

	doSave: function()
	{
		var form = this.MainPanel;
		if (!form.getForm().isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		this.getLoadMask("Подождите, выполняется импорт...").show();
		this.delCards();
		form.ownerCt.submit();

		return true;
	},

	submit: function()
	{
		var wnd = this,
		base_form = wnd.MainPanel.getForm();
		base_form.submit({
			url: '/?c=Utils&m=doTxtFileUpload',
			failure: function(result_form, action){
				var error = '';
				wnd.MainPanel.findById('fileDir').setValue(null);
				wnd.getLoadMask().hide();
				if (action.result && action.result.Error_Msg)
					error = action.result.Error_Msg;

				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function()
					{
					},
					icon: Ext.Msg.ERROR,
					msg: error,
					title: 'Ошибка'
				});
			},
			success: function(result_form, action) {
				wnd.MainPanel.findById('fileDir').setValue(null);
				if (action.response.responseText) {
					var result = Ext.util.JSON.decode(action.response.responseText);
					if (result) {
						result.Lpu_id = getGlobalOptions().lpu_id;
						result.pmUser_id = getGlobalOptions().pmuser_id;
						result.OrgSmo_id = Ext.getCmp('SmoOrg_Combo').getValue();
						result.insDT = Ext.util.Format.date(new Date(), 'Y.m.d H:i');
						Ext.getCmp('swSmoCallCardWindow').insCards(result);
					}
				} else {
					wnd.getLoadMask().hide();
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function()
						{
						},
						icon: Ext.Msg.ERROR,
						msg: 'Произошла ошибка загрузки файла',
						title: 'Ошибка'
					});
				}
			}
		});
	},

	show: function(){
		sw.Promed.swSmoCallCardWindow.superclass.show.apply(this, arguments);
		var form = this;
		form.onHide = Ext.emptyFn;
		Ext.getCmp('rifOk').enable();
		form.MainPanel.getForm().reset();
	},

	delCards: function(){
		Ext.Ajax.request({
			params: { OrgSmo_id: Ext.getCmp('SmoOrg_Combo').getValue() },
			url: '/?c=CmpCallCard&m=delSmoQueryCallCards',
			callback: function(opt, scs, response) {
				var responseText = Ext.util.JSON.decode(response.responseText);
				if (responseText.success) {

				} else if (!Ext.isEmpty(responseText.Error_Code) && responseText.Error_Code == 1) {
					sw.swMsg.alert('Ошибка', 'Во время выполнения операции удаления произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
				}
			}
		});
	},
	
	insCards: function(params){
		var wnd = this;
		Ext.Ajax.request({
			params: { jsondata: Ext.util.JSON.encode(params)},
			url: '/?c=CmpCallCard&m=saveSmoQuery',
			callback: function(opt, scs, response) {
				wnd.getLoadMask().hide();
				Ext.getCmp('CardNumber_Grid').getGrid().getStore().load();
				var responseText = Ext.util.JSON.decode(response.responseText);
				if (responseText.success) {

				} else if (!Ext.isEmpty(responseText.Error_Code) && responseText.Error_Code == 1) {
					sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки номеров карт произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
				}
				Ext.getCmp('swSmoCallCardWindow').setDisabled(false);
			}
		});
	}
	
});
