/**
* swExtemporalCompStandartEditWindow - окно редактирования Нормы выхода и тарифа на изготовление экстемпорального лекарственного средства
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Alexander Kurakin
* @version      07.2016
* @comment      
*/
sw.Promed.swExtemporalCompStandartEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	height: 280,
	title: 'Норма выхода и тариф на изготовление экстемпорального лекарственного средства',
	layout: 'border',
	id: 'swExtemporalCompStandartEditWindow',
	modal: true,
	shim: false,
	width: 700,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('ExtemporalCompStandartEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = this.form.getValues();
		Ext.Ajax.request({
			url: '/?c=Extemporal&m=checkExtemporalCompStandart',
			params: params,
			callback: function(options, success, response) {
				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);
					if(result[0].cnt>0){
						Ext.Msg.alert(lang['soobschenie'], 'Добавление данных не возможно, т.к. в организации '+getGlobalOptions().org_nick+' для выбранной рецептуры уже указаны нормы выхода и тариф на изготовление ЛС');
 						return false;
					} else {
						wnd.submit();
						return true;
					}
				}
			}
		});
		return false;
	},
	submit: function() {
		var wnd = this;
		var params = {};

		wnd.getLoadMask('Подождите, идет сохранение...').show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.ExtemporalCompStandart_id > 0) {
					var id = action.result.ExtemporalCompStandart_id;
					wnd.form.findField('ExtemporalCompStandart_id').setValue(id);
					if ( wnd.callback && typeof wnd.callback == 'function' ) {
						wnd.callback(wnd.owner, id);
					}
					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swExtemporalCompStandartEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.EmptyFn;
		this.Extemporal_id = null;
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
		if ( arguments[0].Extemporal_id ) {
			this.Extemporal_id = arguments[0].Extemporal_id;
		}
		if ( arguments[0].Extemporal_Name ) {
			this.Extemporal_Name = arguments[0].Extemporal_Name;
		}
		this.setTitle("Норма выхода и тариф на изготовление экстемпорального лекарственного средства");
		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				this.disableFields(false);
				this.form.findField('Extemporal_id').setValue(this.Extemporal_id);
				this.form.findField('Extemporal_Name').setValue(this.Extemporal_Name);
				this.form.findField('Org_id').setValue(getGlobalOptions().org_id);
				this.form.findField('Org_Nick').setValue(getGlobalOptions().org_nick);
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				var bf = this.form;
				var extData = arguments[0].owner.getGrid().getSelectionModel().getSelected().data;
				bf.setValues(extData);
				if(this.action == 'edit'){
					this.disableFields(false);
				}
				
				if(this.action == 'view'){
					this.disableFields(true);
				}
				loadMask.hide();
				break;
		}
	},
	disableFields: function(s) {
		this.form.items.each(function(f) {
			if( (f.xtype && f.xtype != 'hidden') ) {
				if(f.name=='Extemporal_Name' || f.name=='Org_Nick'){
					f.setDisabled(true);
				} else {
					f.setDisabled(s);
				}
			}
		});
		if(s){
			this.buttons[0].setVisible(false);
		} else {
			this.buttons[0].setVisible(true);
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
				id: 'ExtemporalCompStandartEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 180,
				collapsible: true,
				url:'/?c=Extemporal&m=saveExtemporalCompStandart',
				items: [{					
					xtype: 'hidden',
					name: 'Extemporal_id'
				}, {					
					xtype: 'hidden',
					name: 'Org_id'
				}, {					
					xtype: 'hidden',
					name: 'ExtemporalCompStandart_id'
				}, {
					xtype: 'textfield',
					width: 300,
					fieldLabel: 'Рецептура',
					name: 'Extemporal_Name'
				}, {
					xtype: 'textfield',
					width: 300,
					fieldLabel: 'Организация',
					name: 'Org_Nick'
				}, {
					height: 90,
					labelWidth: 170,
					title: 'Норма выхода',
					xtype: 'fieldset',
					layout:'form',
					items:[{
						allowBlank: false,
						xtype: 'numberfield',
						width: 200,
						fieldLabel: 'Количество ЛС',
						name: 'ExtemporalCompStandart_Count'
					}, {
						allowBlank: false,
						editable: true,
						xtype: 'swcommonsprcombo',
						width: 200,
						comboSubject: 'GoodsUnit',
						fieldLabel: 'Единица измерения'
					}]
				}, {
					layout:'form',
					items:[{
						layout:'column',
						items:[{
							layout:'form',
							items:[{
								allowBlank: false,
								xtype: 'numberfield',
								width: 300,
								fieldLabel: 'Тариф на изготовление',
								name: 'ExtemporalCompStandart_Tariff',
							}]
						}, {
							layout:'form',
							width: 50,
							style: 'padding-left:5px',
							items:[{
								xtype: 'label',
								width: 50,
								html: ((getRegionNick()=='kz')?'<div>тенге</div>':'<div>руб.</div>')
							}]
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
			HelpButton(this, 0),
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
		sw.Promed.swExtemporalCompStandartEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('ExtemporalCompStandartEditForm').getForm();
	}	
});