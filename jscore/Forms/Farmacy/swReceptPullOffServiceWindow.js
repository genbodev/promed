/**
* swReceptPullOffServiceWindow - окно снятия рецепта с обслуживания
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Alexander Kurakin
* @version      05.2016
* @comment      
*/
sw.Promed.swReceptPullOffServiceWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Снятие рецепта с обслуживания',
	layout: 'border',
	id: 'swReceptPullOffServiceWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 300,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('ReceptPullOffServiceForm').getFirstInvalidEl().focus(true);
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
		params.Org_id = getGlobalOptions().org_id;
		params.OrgFarmacy_id = (getGlobalOptions().OrgFarmacy_id)?getGlobalOptions().OrgFarmacy_id:null;

		wnd.getLoadMask('Подождите, идет сохранение...').show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Msg);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				wnd.callback(wnd.owner, id);
				wnd.hide();
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swReceptPullOffServiceWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.EvnRecept_id = null;
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
		if ( arguments[0].EvnRecept_id ) {
			this.EvnRecept_id = arguments[0].EvnRecept_id;
		}
		this.setTitle("Снятие рецепта с обслуживания");
		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();

		this.form.setValues(arguments[0]);
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				var today = new Date();
				this.form.findField('WhsDocumentUcActReceptOut_setDT').setValue(today);
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				Ext.Ajax.request({
					callback: function(options, success, response) {
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);log(response_obj);
							if(response_obj.length > 0){
								if ( response_obj[0].WhsDocumentUcActReceptList_outCause ) {
									wnd.form.findField('WhsDocumentUcActReceptList_outCause').setValue(response_obj[0].WhsDocumentUcActReceptList_outCause);
								}
								if ( response_obj[0].WhsDocumentUcActReceptOut_setDT ) {
									wnd.form.findField('WhsDocumentUcActReceptOut_setDT').setValue(response_obj[0].WhsDocumentUcActReceptOut_setDT);
								}
							}
						}
					}.createDelegate(this),
					params: {EvnRecept_id:wnd.EvnRecept_id},
					url: '/?c=EvnRecept&m=getReceptOutDateAndCause'
				});
				loadMask.hide();
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
				id: 'ReceptPullOffServiceForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 120,
				labelAlign: 'right',
				collapsible: true,
				url:'/?c=EvnRecept&m=pullOffServiceRecept',
				items: [{					
					xtype: 'hidden',
					name: 'EvnRecept_id'
				}, {					
					xtype: 'hidden',
					name: 'Lpu_id'
				}, {
					xtype: 'textfield',
					disabled: true,
					fieldLabel: 'Данные о рецепте',
					anchor: '100%',
					name: 'ReceptData'
				}, {
					xtype: 'textfield',
					disabled: true,
					fieldLabel: 'МО',
					anchor: '100%',
					name: 'Lpu_Nick'
				}, {
					xtype: 'textfield',
					disabled: true,
					fieldLabel: 'Аптека',
					anchor: '100%',
					name: 'Farmacy_Nick',
					value: getGlobalOptions().OrgFarmacy_Nick
				}, {
					xtype: 'textfield',
					disabled: true,
					fieldLabel: 'Дата постановки',
					anchor: '100%',
					name: 'EvnRecept_obrDT'
				}, {
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'WhsDocumentUcActReceptOut_setDT',
					width: 120,
					fieldLabel: 'Дата снятия'
				}, {
					allowBlank: false,
					anchor: '100%',
					fieldLabel : 'Причина',
					name: 'WhsDocumentUcActReceptList_outCause',
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
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
		sw.Promed.swReceptPullOffServiceWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('ReceptPullOffServiceForm').getForm();
	}	
});