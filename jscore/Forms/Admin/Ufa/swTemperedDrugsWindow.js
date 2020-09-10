/**
* swTemperedDrugsWindow - окно импорта отпущенных ЛС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      30.12.2009
*
*
*/

sw.Promed.swTemperedDrugsWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 150,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'TemperedDrugsWindow',
	title: 'Импорт отпущенных ЛС',
	width: 500,
	//layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function()
	{
		this.TemperedDrugsPanel = new Ext.Panel(
		{
			id: 'TemperedDrugsPanel',
			bodyStyle: 'padding:2px',
			layout: 'fit',
			border: true,
			frame: false,
			autoHeight: true,
            autoWidth: true
		});

		this.TextPanel = new Ext.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			fileUpload: true,
			bodyStyle: 'padding: 5px 5px 0',
			frame: true,
			id: 'TemperedDrugsTextPanel',
			labelWidth: 70,
			//url: '/?c=TemperedDrugs&m=importDrugsFromDbf',
			url: '/?c=Utils&m=doFileUpload',
			defaults:
			{
				anchor: '95%',
				allowBlank: false,
				msgTarget: 'side'
			},
			items:
			[{
				xtype: 'hidden',
				value: 'DrugsFile',
				name: 'UploadFieldName'
			},{
				xtype: 'fileuploadfield',
				id: 'riwuDrugsFile',
				anchor: '95%',
				emptyText: 'Выберите файл ЛС',
				fieldLabel: 'Файл ЛС',
				name: 'DrugsFile'
			},
			this.TemperedDrugsPanel]
		});        

        this.Panel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'TemperedDrugsPanelPanel',
			labelAlign: 'right',
			labelWidth: 100,
            items: [this.TextPanel]
		});

		Ext.apply(this,
		{
			autoHeight: true,
			buttons: [
			{
				id: 'rifOk',
				handler: function()
				{
                    this.ownerCt.doSave();
				},
				iconCls: 'refresh16',
				text: 'Импортировать ЛС'
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
			items: [this.Panel]
		});        

        sw.Promed.swTemperedDrugsWindow.superclass.initComponent.apply(this, arguments);
    },
	doSave: function()
	{
		var form = this.TextPanel;
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
        form.ownerCt.ownerCt.submit();
		
		return true;
	},    
	submit: function()
	{
		var form = this.TextPanel,
			base_form = form.getForm();

		var loadMask = new Ext.LoadMask(Ext.get('TemperedDrugsWindow'), { msg: "Подождите, выполняется загрузка файла на сервер..." });

		loadMask.show();
		base_form.submit({
			failure: function(result_form, action){
				loadMask.hide();

				var r = '';

				if (action.result && action.result.Error_Msg)
						r = action.result.Error_Msg;

				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function()
					{
					},
					icon: Ext.Msg.ERROR,
					msg: 'В процессе запроса возникла ошибка.\n\r'+r,
					title: 'Ошибка'
				});
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.response.responseText) {
					var result = Ext.util.JSON.decode(action.response.responseText);
					if (result) {
						loadMask = new Ext.LoadMask(Ext.get('TemperedDrugsWindow'), { msg: "Подождите, выполняется импорт отпущенных ЛС..." });
						loadMask.show();
						Ext.Ajax.request({
							callback: function(opt, scs, response) {
								loadMask.hide();
								var responseText = Ext.util.JSON.decode(response.responseText);

								if (responseText.success) {
									loadMask.hide();
									sw.swMsg.alert('Успех', responseText.Message+'<br/>'+responseText.Count);
									Ext.getCmp('rifOk').disable();
									form.ownerCt.ownerCt.hide();
								} else if (!Ext.isEmpty(responseText.Error_Code) && responseText.Error_Code == 1) {
									loadMask.hide();
									sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки отпущенных ЛС произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
								}
							}.createDelegate(this),
							params: result,
							url: '/?c=TemperedDrugs&m=importDrugsFromDbf',
							timeout: 0
						});
					}
				} else {
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
        sw.Promed.swTemperedDrugsWindow.superclass.show.apply(this, arguments);
		var form = this;
        form.onHide = Ext.emptyFn;
        Ext.getCmp('rifOk').enable();
		form.TextPanel.getForm().reset();
    }
});
