/**
* swRegionalLocalDbListEditWindow - окно редактирования запроса справочника
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @author       Kurakin A.
* @version      04.2017
* @comment      
*/
sw.Promed.swRegionalLocalDbListEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'SQL Запрос',
	layout: 'border',
	id: 'RegionalLocalDbListEditWindow',
	modal: true,
	shim: false,
	width: 815,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('RegionalLocalDbListEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit();
		return true;
	},
	submit: function() {
		var wnd = this;
		var params = new Object();

		wnd.getLoadMask('Подождите, идет сохранение...').show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Msg) {
						Ext.Msg.alert('Ошибка', action.result.Error_Msg);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.RegionalLocalDbList_id > 0) {
					var id = action.result.RegionalLocalDbList_id;
					wnd.form.findField('RegionalLocalDbList_id').setValue(id);
					wnd.callback(wnd.owner, id);
					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swRegionalLocalDbListEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.RegionalLocalDbList_id = null;
		this.LocalDbList_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].RegionalLocalDbList_id ) {
			this.RegionalLocalDbList_id = arguments[0].RegionalLocalDbList_id;
		}
		if ( arguments[0].LocalDbList_id ) {
			this.LocalDbList_id = arguments[0].LocalDbList_id;
		}
		this.setTitle("SQL Запрос");
		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				this.form.findField('LocalDbList_id').setValue(this.LocalDbList_id);
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					params: {
						LocalDbList_id: this.LocalDbList_id,
						RegionalLocalDbList_id: this.RegionalLocalDbList_id
					},
					failure: function (response)
					{
						loadMask.hide();
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.Error_Msg) {
							// Ошибку уже показали
						} else {
							Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
						}
						this.hide();
					}.createDelegate(this),
					success: function(response) {
						loadMask.hide();
						var result = Ext.util.JSON.decode(response.responseText);
						if(result && result[0]) {
							wnd.form.setValues(result[0]);
						}
					},
					url: '/?c=MongoDBWork&m=getRegionalLocalDbListRecord'
				});
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				break;
		}
	},
	initComponent: function() {
		var wnd = this;

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'RegionalLocalDbListEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 100,
				collapsible: true,
				url:'/?c=MongoDBWork&m=saveRegionalLocalDbList',
				items: [{
					xtype: 'hidden',
					name: 'RegionalLocalDbList_id'
				}, {
					xtype: 'hidden',
					name: 'LocalDbList_id'
				}, {
					xtype: 'textfield',
					fieldLabel: 'Регион',
					name: 'Region_id',
					listeners: {
						render: function(c) {
						    Ext.QuickTips.register({
						        target: c.getEl(),
						        text: 'Если не указать регион, то запрос будет использоваться для всех регионов, исключая те, которым будет добавлен свой региональный запрос.',
						        enabled: true,
						        showDelay: 20,
						        trackMouse: true,
						        autoShow: true
						    });
					    }
					}
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelAlign: 'top',
						style: 'margin-right: 10px;',
						items: [{
							width:380,
							height:350,
							fieldLabel: 'MSSQL',
							name: 'RegionalLocalDbList_Sql',
							defaultValue: '',
							xtype: 'textarea',
							autoCreate: {tag: "textarea", wrap: "off"}
						}]
					}, {
						layout: 'form',
						labelAlign: 'top',
						items: [{
							width:380,
							height:350,
							fieldLabel: 'PostgreSQL',
							name: 'RegionalLocalDbList_PgSql',
							defaultValue: '',
							xtype: 'textarea',
							autoCreate: {tag: "textarea", wrap: "off"}
						}]
					}]
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
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
			items:[form]
		});
		sw.Promed.swRegionalLocalDbListEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('RegionalLocalDbListEditForm').getForm();
	}	
});