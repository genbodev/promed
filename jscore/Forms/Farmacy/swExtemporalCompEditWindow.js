/**
* swExtemporalCompEditWindow - окно редактирования компонента экстемпоральной рецептуры
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
sw.Promed.swExtemporalCompEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	height: 270,
	title: 'Компонент экстемпоральной рецептуры',
	layout: 'border',
	id: 'ExtemporalCompEditWindow',
	modal: true,
	shim: false,
	width: 650,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('ExtemporalCompEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = this.form.getValues();
		Ext.Ajax.request({
			url: '/?c=Extemporal&m=checkExtemporalComp',
			params: params,
			callback: function(options, success, response) {
				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);
					if(result[0].cnt>0){
						Ext.Msg.alert(lang['soobschenie'], 'Добавление компонента не возможно, т.к. такой компонент уже включен в рецептуру');
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
				if (action.result && action.result.ExtemporalComp_id > 0) {
					var id = action.result.ExtemporalComp_id;
					wnd.form.findField('ExtemporalComp_id').setValue(id);
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
		sw.Promed.swExtemporalCompEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.EmptyFn;
		this.ExtemporalComp_id = null;
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
		if ( arguments[0].ExtemporalComp_id ) {
			this.ExtemporalComp_id = arguments[0].ExtemporalComp_id;
		}
		if ( arguments[0].Extemporal_id ) {
			this.Extemporal_id = arguments[0].Extemporal_id;
		}
		this.setTitle("Компонент экстемпоральной рецептуры");
		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				this.disableFields(false);
				this.form.findField('Extemporal_id').setValue(this.Extemporal_id);
				this.findById('ExtC_actmatterscombo').showContainer();
	            this.findById('ExtC_actmatterscombo').enable();
				this.findById('ExtC_tradenamescombo').setValue('');
        		this.findById('ExtC_tradenamescombo').disable();
        		this.findById('ExtC_tradenamescombo').hideContainer();
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				var bf = this.form;
				var compData = arguments[0].owner.getGrid().getSelectionModel().getSelected().data;
				bf.setValues(compData);
				if(this.action == 'edit'){
					this.disableFields(false);
				}
				if(compData.RlsTradenames_id){
					this.sprtype_combo.setValue(2);
					this.findById('ExtC_actmatterscombo').setValue('');
            		this.findById('ExtC_actmatterscombo').disable();
            		this.findById('ExtC_actmatterscombo').hideContainer();
            		this.findById('ExtC_tradenamescombo').showContainer();
            		this.findById('ExtC_tradenamescombo').enable();
            		this.findById('ExtC_tradenamescombo').setValue(compData.RlsTradenames_id);
            		this.findById('ExtC_tradenamescombo').fireEvent('change',wnd.findById('ExtC_tradenamescombo'),compData.RlsTradenames_id,0)
				} else {
					if(compData.RlsActMatters_id){
						this.sprtype_combo.setValue(1);
	            		this.findById('ExtC_actmatterscombo').setValue(compData.RlsActMatters_id);
	            		this.findById('ExtC_actmatterscombo').fireEvent('change',this.findById('ExtC_actmatterscombo'),compData.RlsActMatters_id,0)
					}
					this.findById('ExtC_tradenamescombo').setValue('');
            		this.findById('ExtC_tradenamescombo').disable();
            		this.findById('ExtC_tradenamescombo').hideContainer();
            		this.findById('ExtC_actmatterscombo').showContainer();
            		this.findById('ExtC_actmatterscombo').enable();
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
			if( (f.xtype && f.xtype != 'hidden')||f.hiddenName=='SprType_id' ) {
				f.setDisabled(s);
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

		this.sprtype_combo = new sw.Promed.SwBaseLocalCombo({
            hiddenName: 'SprType_id',
            valueField: 'SprType_id',
            displayField: 'SprType_Name',
            fieldLabel: 'Вид справочника',
            allowBlank: false,
            editable: false,
            width: 450,
            store: new Ext.data.SimpleStore({
                key: 'SprType_id',
                autoLoad: false,
                fields: [
                    {name: 'SprType_id', type: 'int'},
                    {name: 'SprType_Name', type: 'string'}
                ],
                data: [
                    [1, 'МНН, действующие вещества ЛС'],
                    [2, 'Торговые наименования, в т.ч. вспомогательные вещества']
                ]
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{SprType_Name}&nbsp;',
                '</div></tpl>'
            ),
            listeners: {
                select: function(comp,newval) {
                	if(newval){
                		if(newval.data.SprType_id == 1){
                			this.findById('ExtC_tradenamescombo').setValue('');
	                		this.findById('ExtC_tradenamescombo').disable();
	                		this.findById('ExtC_tradenamescombo').hideContainer();
	                		this.findById('ExtC_actmatterscombo').showContainer();
	                		this.findById('ExtC_actmatterscombo').enable();
	                	} else if(newval.data.SprType_id == 2){
	                		this.findById('ExtC_actmatterscombo').setValue('');
	                		this.findById('ExtC_actmatterscombo').disable();
	                		this.findById('ExtC_actmatterscombo').hideContainer();
	                		this.findById('ExtC_tradenamescombo').showContainer();
	                		this.findById('ExtC_tradenamescombo').enable();
	                	}
                	}
                }.createDelegate(this),
                change: function(comp,newval) {
                	if(newval){
                		if(newval == 1){
                			this.findById('ExtC_tradenamescombo').setValue('');
	                		this.findById('ExtC_tradenamescombo').disable();
	                		this.findById('ExtC_tradenamescombo').hideContainer();
	                		this.findById('ExtC_actmatterscombo').showContainer();
	                		this.findById('ExtC_actmatterscombo').enable();
	                	} else if(newval == 2){
	                		this.findById('ExtC_actmatterscombo').setValue('');
	                		this.findById('ExtC_actmatterscombo').disable();
	                		this.findById('ExtC_actmatterscombo').hideContainer();
	                		this.findById('ExtC_tradenamescombo').showContainer();
	                		this.findById('ExtC_tradenamescombo').enable();
	                	}
                	}
                }.createDelegate(this)
            }
        });		
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
				id: 'ExtemporalCompEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 120,
				collapsible: true,
				url:'/?c=Extemporal&m=saveExtemporalComp',
				items: [{					
					xtype: 'hidden',
					name: 'ExtemporalComp_id'
				}, {					
					xtype: 'hidden',
					name: 'Extemporal_id'
				}, {
					layout:'form',
					items:[{
						allowBlank: false,
						xtype: 'swextemporalcomptypecombo',
						fieldLabel: 'Вид компонента',
						comboSubject: 'ExtemporalCompType',
						width: 450,
						listeners: {
							select: function(combo,val,vv){
								var win = this;
								if(val){
									if(val.data.ExtemporalCompType_id == 1){
										this.sprtype_combo.setValue(1);
										this.sprtype_combo.fireEvent('change',win.sprtype_combo,1,0);
									} else {
										this.sprtype_combo.setValue(2);
										this.sprtype_combo.fireEvent('change',win.sprtype_combo,2,0);
									}
								}
							}.createDelegate(this),
							change: function(combo,val){
								var win = this;
								if(val){
									if(val == 1){
										this.sprtype_combo.setValue(1);
										this.sprtype_combo.fireEvent('change',win.sprtype_combo,1,0);
									} else {
										this.sprtype_combo.setValue(2);
										this.sprtype_combo.fireEvent('change',win.sprtype_combo,2,0);
									}
								}
							}.createDelegate(this)
						}
					}]
				}, {
					layout:'form',
					items:[wnd.sprtype_combo]
				}, {
					layout:'form',
					items:[{
						allowBlank: false,
						width: 450,
						listWidth: 450,
						id: 'ExtC_actmatterscombo',
						xtype: 'swrlsactmatterscombo',
						fieldLabel: 'Компонент',
						listeners: {
							'change':function(comp,newval){
								var val = comp.getStore().getById(newval);log(val);
								this.form.findField('ExtemporalComp_Name').setValue(val.get('RlsActmatters_RusName'));
								var tr_name_id = newval; 
								Ext.Ajax.request({
									url: '/?c=Extemporal&m=getLatName',
            						params: {Actmatters_id:tr_name_id},
            						callback: function(options, success, response) {
            							if ( success ) {
											var result = Ext.util.JSON.decode(response.responseText);
											wnd.form.findField('ExtemporalComp_LatName').setValue(result[0].LatinName);
										}
            						}
								}); 
							}.createDelegate(this)
						}
					}]
				}, {
					layout:'form',
					items:[{
							xtype: 'combo',
							width: 450,
							hidden: true,
							allowBlank: false,
							id: 'ExtC_tradenamescombo',
							displayField: 'NAME',
							enableKeyEvents: true,
							mode: 'local',
							triggerAction: 'none',
							doQuery: function(q, forceAll)
							{
								var combo = this;
								if(q.length<2)
									return false;
								combo.fireEvent('beforequery', combo);
								var where = ' and LOWER(NAME) like LOWER(\''+q+'%\')';
								combo.getStore().load({
									params: {where: where}
								});
							},
							listeners: {
								change: function(c,newval)
								{
									if(typeof c.getValue() == 'string')
									{
										c.reset();
										return false;
									}
									var index = c.getStore().findBy(function(rec){
										return (rec.get('TRADENAMES_ID')==newval);
									});
									if(index > -1){
										var val = c.getStore().getAt(index);
										var tr_name_id = newval;
										var wnd = this;
										this.form.findField('ExtemporalComp_Name').setValue(val.get('NAME')); 
										Ext.Ajax.request({
											url: '/?c=Extemporal&m=getLatName',
		            						params: {Tradename_id:tr_name_id},
		            						callback: function(options, success, response) {
		            							if ( success ) {
													var result = Ext.util.JSON.decode(response.responseText);
													wnd.form.findField('ExtemporalComp_LatName').setValue(result[0].LatinName);
												}
		            						}
										});
									}
								}.createDelegate(this)
							},
							valueField: 'TRADENAMES_ID',
							hiddenName: 'Tradenames_id',
							store: new Ext.data.Store({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'TRADENAMES_ID'
								}, [{
									mapping: 'TRADENAMES_ID',
									name: 'TRADENAMES_ID',
									type: 'int'
								},{
									mapping: 'NAME',
									name: 'NAME',
									type: 'string'
								}]),
								url: '/?c=Rls&m=getTorgNames'
							}),
							emptyText: lang['vvedite_nazvanie'],
							fieldLabel: 'Вид прописи'
						}
					]
				}, {
					layout:'form',
					items:[{
						xtype: 'textfield',
						width: 450,
						fieldLabel: 'Наименование',
						name: 'ExtemporalComp_Name',
						allowBlank: false
					}]
				}, {
					layout:'form',
					items:[{
						xtype: 'textfield',
						width: 450,
						fieldLabel: 'На латинском (р.п.)',
						name: 'ExtemporalComp_LatName'
					}]
				}, {
					layout:'form',
					items:[{
						layout:'column',
						items:[{
							layout:'form',
							width: 250,
							items:[{
								xtype: 'numberfield',
								width: 100,
								fieldLabel: 'Количество',
								name: 'ExtemporalComp_Count',
								decimalPrecision: 5
							}]
						}, {
							layout:'form',
							width: 330,
							items:[{
								editable: true,
								hiddenName: 'GoodsUnit_id',
								xtype: 'swgoodsunitcombo',
								width: 200,
								fieldLabel: 'Единица измерения'
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
		sw.Promed.swExtemporalCompEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('ExtemporalCompEditForm').getForm();
	}	
});