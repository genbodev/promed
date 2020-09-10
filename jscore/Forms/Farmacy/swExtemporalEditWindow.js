/**
* swExtemporalEditWindow - окно редактирования экстемпоральной рецептуры
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
sw.Promed.swExtemporalEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Экстемпоральная рецептура',
	layout: 'border',
	id: 'ExtemporalEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		'beforehide': function(win) {
			var wnd = win;
			win.removeExtemporal(wnd);
		}
	},
	removeExtemporal:function(win){
		if(win.action == 'add' && !win.checkAdd && win.Extemporal_id){
			Ext.Ajax.request({
				url: '/?c=Extemporal&m=deleteExtemporal',
				params: { Extemporal_id: win.Extemporal_id},
				callback: function(options, success, response) {
					if ( success ) {
						
					}
				}
			});
		}
	},
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('ExtemporalEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if(!this.ignoreNameCheck){
			var nameparams = {};
			nameparams.Extemporal_Name = this.form.findField('Extemporal_Name').getValue();
			nameparams.Extemporal_id = this.form.findField('Extemporal_id').getValue();
			Ext.Ajax.request({
				url: '/?c=Extemporal&m=checkExtemporalName',
				params: nameparams,
				callback: function(options, success, response) {
					if ( success ) {
						var result = Ext.util.JSON.decode(response.responseText);
						if(result[0].cnt>0){
							Ext.Msg.alert(lang['soobschenie'], 'Добавление рецептуры не возможно, т.к. уже есть рецептура с таким же наименованием');
	 						return false;
						} else {
							wnd.ignoreNameCheck = true;
							wnd.doSave();
							return true;
						}
					}
				}
			});
			return false;
		}
		this.ignoreNameCheck = false;
		if(!this.presave){
			var store = this.CompGrid.getGrid().getStore();
			if(store.data.length>0){
				var index = store.findBy(function(rec){
					return (rec.get('ExtemporalCompType_id')==1);
				});
			}
			if(store.data.length == 0 || index < 0){
				Ext.Msg.alert(lang['soobschenie'], 'Не указан состав лекарственного средства');
	 			return false;
			} else {
				var params = this.form.getValues();
				params.count = this.CompGrid.getGrid().getStore().data.length;
				var actmatters = '';
				var tradenames = '';
				this.CompGrid.getGrid().getStore().each(function(rec){
					if(rec.get('RlsActMatters_id')>0){
						actmatters += (rec.get('RlsActMatters_id')+',');
					} else if(rec.get('RlsTradenames_id')>0){
						tradenames += (rec.get('RlsTradenames_id')+',');
					}
				});
				params.actmatters = actmatters;
				params.tradenames = tradenames;
				Ext.Ajax.request({
					url: '/?c=Extemporal&m=checkExtemporal',
					params: params,
					callback: function(options, success, response) {
						if ( success ) {
							var result = Ext.util.JSON.decode(response.responseText);
							if(result[0].cnt>0){
								Ext.Msg.alert(lang['soobschenie'], 'Добавление рецептуры не возможно, т.к. такая рецептура уже есть в системе');
		 						return false;
							} else {
								wnd.submit();
								return true;
							}
						}
					}
				});
				return false;
			}
		} else {
			this.submit();
			return true;
		}
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
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.Extemporal_id > 0) {
					var id = action.result.Extemporal_id;
					wnd.form.findField('Extemporal_id').setValue(id);
					wnd.Extemporal_id = id;
					if(!wnd.presave){
						wnd.checkAdd = true;
						wnd.callback(wnd.owner, id);
						wnd.hide();
					} else {
						wnd.presave = false;
						wnd.addExtemporalComp();
					}
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swExtemporalEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.ExtemporalFn;
		this.ignoreNameCheck = false;
		this.Extemporal_id = null;
		this.presave = false;
		this.copy = false;
		this.fromNomenSpr = false;
        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
        this.removeExtemporal(wnd);
        this.checkAdd = false;
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
		if ( arguments[0].copy ) {
			this.copy = arguments[0].copy;
		}
		if ( arguments[0].fromNomenSpr ) {
			this.fromNomenSpr = arguments[0].fromNomenSpr;
		}
		this.setTitle("Экстемпоральная рецептура");
		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				this.disableFields(false);
				wnd.CompGrid.getGrid().getStore().removeAll();
				this.form.findField('Extemporal_IsClean').setValue(1);
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				if(this.action == 'edit'){
					this.disableFields(false);
				} else {
					this.disableFields(true);
				}
				var bf = this.form;
				if(this.fromNomenSpr){
					var extData = arguments[0];
					Ext.Ajax.request({
						url: '/?c=Extemporal&m=loadExtemporalList',
						params: {
							Extemporal_id: extData.Extemporal_id,
							withoutPaging: 1
						},
						callback: function(options, success, response) {
							var result = Ext.util.JSON.decode(response.responseText);
							if(result && result[0] && result[0].Extemporal_id){
								bf.setValues(result[0]);
								bf.findField('Extemporal_IsClean').setValue(result[0].Extemporal_IsClean);
							}
						}
					});
				} else {
					if(this.copy){
						var extData = arguments[0];
					} else {
						var extData = arguments[0].owner.getGrid().getSelectionModel().getSelected().data;
					}
					bf.setValues(extData);
					bf.findField('Extemporal_IsClean').setValue(extData.Extemporal_IsClean);
				}
				
				var prams = new Object();
				prams.Extemporal_id = extData.Extemporal_id;
				wnd.CompGrid.getGrid().getStore().removeAll();
				wnd.CompGrid.getGrid().getStore().load({params:prams});
				loadMask.hide();
				break;
		}
	},
	disableFields: function(s) {log(this.CompGrid);
		this.form.items.each(function(f) {
			if( (f.xtype && f.xtype != 'hidden') || f.name == 'Extemporal_Code' ) {
				f.setDisabled(s);
			}
		});
		if(s){
			this.buttons[0].setVisible(false);
			this.CompGrid.ViewToolbar.items.items[0].disable();
			this.CompGrid.ViewToolbar.items.items[1].disable();
			this.CompGrid.ViewToolbar.items.items[3].disable();
		} else {
			this.buttons[0].setVisible(true);
			this.CompGrid.ViewToolbar.items.items[0].enable();
			if(this.CompGrid.getGrid().getStore().data.length > 0 && this.CompGrid.getGrid().getSelectionModel().getSelected()){
				this.CompGrid.ViewToolbar.items.items[1].enable();
				this.CompGrid.ViewToolbar.items.items[3].enable();
			}
		}
	},
	addExtemporalComp: function() {
		var wnd = this;
		var Extemporal_id = this.form.findField('Extemporal_id').getValue();
		if(!Extemporal_id){
			wnd.presave = true;
			wnd.doSave();
		} else {
			var params = new Object();
			params.Extemporal_id = this.form.findField('Extemporal_id').getValue();
			params.action = 'add';
			params.callback = function(){
				wnd.CompGrid.getGrid().getStore().removeAll();
				wnd.CompGrid.getGrid().getStore().load({params:{Extemporal_id:wnd.Extemporal_id}});
			};
			getWnd('swExtemporalCompEditWindow').show(params);
		}
	},
	deleteExtemporalComp: function() {
		var wnd = this;
		var record = this.CompGrid.ViewGridPanel.getSelectionModel().getSelected();
		if( !record ) return false;
		Ext.Msg.show({
			title: lang['vnimanie'],
			scope: this,
			msg: 'Вы действительно хотите удалить компонент?',
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					wnd.getLoadMask('Удаление компонента').show();
					Ext.Ajax.request({
						url: '/?c=Extemporal&m=deleteExtemporalComp',
						params: { ExtemporalComp_id: record.get('ExtemporalComp_id')},
						callback: function(options, success, response) {
							wnd.getLoadMask().hide();
							if ( success ) {
								wnd.CompGrid.getGrid().getStore().removeAll();
								wnd.CompGrid.getGrid().getStore().load({params:{Extemporal_id:wnd.Extemporal_id}});
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},
	initComponent: function() {
		var wnd = this;	

		this.CodeField = new Ext.form.TriggerField({
			allowBlank: false,
			autoCreate: {tag: "input", autocomplete: "off"},
			width: 180,
			readOnly : false,
			forceSelection: true,
			name: 'Extemporal_Code',
			fieldLabel: 'Код',
			typeAhead: false,
			triggerClass: 'x-form-plus-trigger',
			firsttime: true,
			onTriggerClick: function() {
				var wnd = this;
				if(this.CodeField.disabled){
					return false;
				}
				Ext.Ajax.request({
					url: '/?c=Extemporal&m=getExtemporalCode',
					callback: function(options, success, response) {
						if ( success ) {
							var result = Ext.util.JSON.decode(response.responseText);
							if(result[0].code>0){
								wnd.CodeField.setValue(result[0].code);
							} else {
								Ext.Msg.alert(lang['soobschenie'], 'Ошибка при получении номера кода, заполните поле вручную');
							}
						}
					}
				});
			}.createDelegate(this),
		});	
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 190,
			border: false,			
			frame: true,
			region: 'north',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'ExtemporalEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 200,
				collapsible: true,
				url:'/?c=Extemporal&m=saveExtemporal',
				items: [{					
					xtype: 'hidden',
					name: 'Extemporal_id'
				}, 
				this.CodeField,
				{
					allowBlank: false,
					width: 300,
					xtype: 'swcommonsprcombo',
					fieldLabel: 'Вид прописи',
					comboSubject: 'ExtemporalType'
				}, {
					allowBlank: false,
					fieldLabel: lang['lekarstvennaya_forma'],
					xtype: 'swrlsclsdrugformscombo',
					width: 300
				}, {
					allowBlank: false,
					fieldLabel: 'Стерильно',
					hiddenName: 'Extemporal_IsClean',
					width: 100,
					xtype: 'swyesnocombo'
				}, {
					allowBlank: false,
					width: 300,
					xtype: 'textfield',
					fieldLabel: 'Наименование',
					name: 'Extemporal_Name'
				}, {
					fieldLabel: 'Период действия записи',
					xtype: 'daterangefield',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
					width: 180,
					name: 'Extemporal_daterange'
				}]
			}]
		});
		this.CompGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function(){this.addExtemporalComp();}.createDelegate(this)},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', handler: function(){this.deleteExtemporalComp();}.createDelegate(this)},
				{name: 'action_print'},
				{name: 'action_refresh', handler: function(){
					var wnd = this;
					if(!wnd.Extemporal_id) {
						return false;
					}
					wnd.CompGrid.getGrid().getStore().removeAll();
					wnd.CompGrid.getGrid().getStore().load({params:{Extemporal_id:wnd.Extemporal_id}});
				}.createDelegate(this) }
			],
			region: 'center',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Extemporal&m=loadExtemporalCompList',
			height: 300,
			object: 'ExtemporalComp',
			editformclassname: 'swExtemporalCompEditWindow',
			id: 'ExtemporalCompGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'ExtemporalComp_id', type: 'int', header: 'ID', key: true },
				{ name: 'ExtemporalComp_Code', type: 'int', header: 'Код', width: 80 },
				{ name: 'ExtemporalComp_Name', type: 'string', header: 'Наименование', id: 'autoexpand' },
				{ name: 'ExtemporalComp_LatName', type: 'string', header: 'На латинском языке', width: 180 },
				{ name: 'ExtemporalComp_Count', type: 'string', header: 'Количество', width: 160 },
				{ name: 'GoodsUnit_id', type: 'int', hidden: true },
				{ name: 'GoodsUnit_Name', type: 'string', header: 'Ед.измерения', width: 160 },
				{ name: 'ExtemporalCompType_id', type: 'int', hidden: true },
				{ name: 'ExtemporalCompType_Name', type: 'string', header: 'Вид компонента', width: 180 },
				{ name: 'RlsActMatters_id', type: 'int', hidden: true },
				{ name: 'RlsTradenames_id', type: 'int', hidden: true },
				{ name: 'Extemporal_id', type: 'int', hidden: true }
			],
			title: 'Компоненты',
			toolbar: true
		});
		this.CompGrid.getGrid().getSelectionModel().on('rowselect', function(sm, rIdx, rec) {
            if(this.action == 'view'){
            	this.CompGrid.ViewToolbar.items.items[0].disable();
            	this.CompGrid.ViewToolbar.items.items[1].disable();
				this.CompGrid.ViewToolbar.items.items[3].disable();
            }
		}, this);
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
			items:[form,this.CompGrid]
		});
		sw.Promed.swExtemporalEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('ExtemporalEditForm').getForm();
	}	
});