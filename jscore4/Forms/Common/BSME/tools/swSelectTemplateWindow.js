Ext.define('common.BSME.tools.swSelectTemplateWindow', {
	extend: 'Ext.window.Window',
    autoShow: true,
	height: '80%',
	width: '80%',
	closable: true,
	title: 'Выбор шаблона',
	id: 'SelectTemplateWindow',
	border: false,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	callback: Ext.emptyFn,
	EvnClass_id:null,
	XmlType_id:11, 
	UslugaComplex_id:null,
	LpuSection_id:null,
	MedService_id:null,
	MedPersonal_id:null,
	MedStaffFact_id:null,
	onSelect: null,
	//Массив идентификаторов типов документов, по которому будет фильтроваться комбобокс
	//Чтобы отображались только необходимые типы документов
	XmlTypeFilterValues:[],
	initComponent: function() {
		var win = this;
		
		win.XmlTemplateCombo = Ext.create('sw.swXmlTypeCombo',{
			value: win.XmlType_id,
			listeners: {
				'change': function(oV,nV,combo){
					win.grid.getStore().getProxy().setExtraParam('XmlType_id',nV);
					win.doLoadData(true);
				}
			}
		});
	
		this.XmlTemplateCatView = Ext.create('Ext.panel.Panel',{
			height: 50,
			lastLevel: null,
			lastRecord: null,
			
			
			//autoHeight: true,
			buttonAlign: 'left',
			layout: {
				type: 'hbox',
				align:'stretch',
				pack: 'start'
			},
			items: [],
			XmlTemplateCatRoot_id: 0,
			XmlTemplateCatRoot_Name: 'Корневая папка',
			
			reset: function() {
				this.removeAll();
				this.lastRecord = null;
				this.lastLevel = -1;
				this.addRecord({
					XmlTemplateCat_id: this.XmlTemplateCatRoot_id,
					XmlTemplateCat_Name: this.XmlTemplateCatRoot_Name
				});
			},
			update: function(data) {
				this.lastRecord = null;
				if(data.XmlTemplateCat_id == 0) {
					this.reset();
					win.toolbar.getByItemId('action_upperfolder').setDisabled(true);
				} else {
					this.lastLevel = data.XmlTemplateCat_Level;
					win.toolbar.getByItemId('action_upperfolder').setDisabled(false);
					
					this.items.each(function(item){
						if (!item.data) {
							return;
						}
						
						if(item.data.XmlTemplateCat_Level > data.XmlTemplateCat_Level) {
							this.remove('XmlTemplateCatCmp_'+item.data.XmlTemplateCat_id);
							this.doLayout();
						}
						
						if(item.data.XmlTemplateCat_Level == data.XmlTemplateCat_Level) {
							this.buttonIntoText(item.data);
							this.lastRecord = item.data;
						}
						
						return true;
					}, this)
				}
			},
			goToUpperLevel: function(data) {
				win.toolbar.getByItemId('action_setdefault').setDisabled(true);
				var prev = {
					XmlTemplateCat_id: this.XmlTemplateCatRoot_id,
					XmlTemplateCat_Name: this.XmlTemplateCatRoot_Name
				};			
				var last = null;	
				
				this.items.each(function(item){
					if (!item.data) {
						return;
					}
					if (last != null) {
						prev = last;
						last = item.data;
					} else {
						last = item.data;
					}
				},this);

				win.doChangeXmlTemplateCat(prev);
			},
			buttonIntoText: function(data) {
				if (!data|| !data['XmlTemplateCat_id'] || !data['XmlTemplateCat_Name']) {
					return false;
				}
				
				this.remove('XmlTemplateCatCmp_'+data['XmlTemplateCat_id']);
			
				this.add({
					xtype: 'container',
					autoHeight: true,
					layout: 'anchor',
					data : data,
					id: 'XmlTemplateCatCmp_'+data['XmlTemplateCat_id'],
					style: 'padding: 2px;',
					border: false,
					items: [
						{
							xtype: 'label',
							record_id: data['XmlTemplateCat_id'],
							html : "<img src='img/icons/folder16.png'>&nbsp;" + data['XmlTemplateCat_Name']
						}
					]
				});
				
			},
			textIntoButton: function(data) {
				
				if (!data|| (data['XmlTemplateCat_id'] !== 0 && !data['XmlTemplateCat_id']) || !data['XmlTemplateCat_Name']) {
					return false;
				}
				this.remove('XmlTemplateCatCmp_'+data['XmlTemplateCat_id']);
				
				this.add({
					
					
					data: data,
					xtype: 'container',
					id: 'XmlTemplateCatCmp_'+data['XmlTemplateCat_id'],
					padding: '2 2 2 2',
					
					
					items: [
						{
							cls: 'x-form-file-btn',//Дополнительный класс, чтобы кнопка была нормальной высоты
							xtype: 'button',
							record_id: data['XmlTemplateCat_id'],
							text : data['XmlTemplateCat_Name'],
							iconCls  : 'folder16',
							handler: function(btn,e){
//								var record = this.store.getById(btn.record_id);
//								if(record)
//									win.doChangeXmlTemplateCat(record.data);
									win.doChangeXmlTemplateCat(data)
							},
							scope: this
						}
					]
				});				
			},
			addRecord: function(data) {
				this.lastLevel++;
				var record = {
					XmlTemplateCat_id: data.XmlTemplateCat_id,
					XmlTemplateCat_Level: this.lastLevel,
					XmlTemplateCat_Name: data.XmlTemplateCat_Name
				}
				
				if(data.XmlTemplateCat_id != 0) {
					win.toolbar.getByItemId('action_upperfolder').setDisabled(false);
				}
				
				//this.store.add(record);
				
				if (this.lastRecord != null) {
					// предыдущий текст заменяем на кнопку (удаляем текстовую, добавляем кнопку)
					this.textIntoButton(this.lastRecord);
				}
				
				// добавляем новую текстовую
				
				this.lastRecord = record;
				
				this.add({
					data: record,
					xtype: 'container',
					id: 'XmlTemplateCatCmp_'+record.XmlTemplateCat_id,
					padding: '2 2 2 2',
					items: [
						{
							xtype:'label',
							record_id: record.XmlTemplateCat_id,
							html : "<img src='img/icons/folder16.png'>&nbsp;" + record.XmlTemplateCat_Name
						}
					]
				});
				
				this.doLayout();
			}
		});
		
		var gridStore = new Ext.data.Store({
			autoLoad: false,
			pageSize: 25,
			storeId: this.id+'GridStore',
			idProperty: 'Item_Key',
			fields: [
				{name: 'Item_Key', type: 'string'},
				{name: 'EvnClass_Name', type: 'string'},
				{name: 'Item_Name',  type: 'string'},
				{name: 'pmUser_Name', type: 'string'},
				{name: 'XmlTemplateScope_Name',type: 'string'},
				{name: 'Item_updDate', type: 'string'},
				{name: 'XmlTemplate_Settings', type: 'string'},
				{name: 'accessType', type: 'string'},
				{name: 'EvnClass_id', type: 'int'},
				{name: 'XmlTemplateType_id', type: 'int'},
				{name: 'XmlType_id', type: 'int'},
				{name: 'XmlTemplate_id', type: 'int'},
				{name: 'XmlTemplateCat_id', type: 'int'},
				{name: 'XmlTemplate_Default', type: 'int'},
				{name: 'XmlTemplate_Preview', type: 'string'}
			],
			proxy: {
				extraParams: {
					EvnClass_id:this.EvnClass_id,
					XmlType_id:this.XmlType_id, 
					UslugaComplex_id:this.UslugaComplex_id
				},
				type: 'ajax',
				url: '/?c=XmlTemplate&m=loadGrid',
				reader: {
					type: 'json',
					//successProperty: 'success',
					totalProperty: 'totalCount',
					root: 'data'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				}
			}
		});
		this.grid = Ext.create('Ext.grid.Panel',{
			flex: 1,
			height: '100%',
			viewConfig: {
				forceFit: true
			},
			autoHeight: true,
			autoScroll: true,
			columns: [
				{ dataIndex: 'Item_Key', text: 'ID', key: true, hidden: true, hideable: false },
				{ text: 'Наименование', dataIndex: 'Item_Name', flex: 1, renderer: function(v, metaData, record)
					{
						if(!record){
							return '';
						}
						var name;
						if (record.get('Item_Key')) {
							name = record.get('Item_Key').split('_')[0];
						}
						switch(name) {
							case 'XmlTemplate':
								return '<div class="x-grid3-xmltemplate-col-'+record.get('accessType')+' x-grid3-cc-'+this.id+'">'+v+'</div>';
							break;
							case 'XmlTemplateCat':
								return '<div class="x-grid3-xmltemplatecat-col-'+record.get('accessType')+' x-grid3-cc-'+this.id+'">'+v+'</div>';
							break;
							default:
								return v;
						}
					}
				}
			],
			store: gridStore,
			bbar: {
				store: gridStore,
				xtype: 'pagingtoolbar',
				displayInfo: false,
				cls: 'paginator',
				beforePageText : 'Стр.'
			}
			,listeners: {
				itemkeydown:function(view, record, item, index, e){
					if ( (e.getKey()==e.ENTER) )
					{
						//win.down('button[refId=choosePatient]').handler();
					}
				},
				select: function( grid, record, index, eOpts ) {
						
					win.toolbar.getByItemId('action_setdefault').setDisabled(false);
					
					if ( record.get('XmlTemplate_id') && record.get('XmlTemplate_id') != win.previewXmlTemplateId) {
						var XmlTemplate_id = record.get('XmlTemplate_id');
						win.previewXmlTemplateId = XmlTemplate_id;
						var XmlTemplate_Preview = record.get('XmlTemplate_Preview');
						var tpl = new Ext.XTemplate(XmlTemplate_Preview);
						tpl.overwrite(win.rightPanel.body, {});

						var url = '/?c=XmlTemplate&m=preview&XmlTemplate_id=' + XmlTemplate_id;

						if (win.Evn_id && win.Evn_id != null) {
							url = url + '&Evn_id=' + win.Evn_id;
						}

						// подгрузить предпросмотр шаблона
						Ext.Ajax.request(
						{
							url: url,
							callback: function(o, s, response) 
							{
								var response_obj = [], 
									html_template;
								if (s && response.responseText) {
									response_obj = Ext.JSON.decode(response.responseText);
									if (response_obj[0]) {
										html_template = response_obj[0].XmlTemplate_HtmlTemplate;
									}
								}
								var html = XmlTemplate_Preview + '<br><br><div style="padding:10px; background: #FFF;">';
								if (html_template) {
									html += html_template;
								} else {
									html += 'Не удалось получить шаблон';
								}
								html += '</div>';
								var tpl = new Ext.XTemplate(html);
								tpl.overwrite(win.rightPanel.body, {});
							}
						});
					} else {
						var tpl = new Ext.XTemplate('');
						tpl.overwrite(win.rightPanel.body, {});
					}
				},
				itemdblclick: function( gridview, record, item, index, e, eOpts ) {
					this.onEnter();
				},
				scope: win.grid
			},
			onEnter: function() {
				var record = win.getSelectedRecord(false);
                if (!record.get('Item_Key')) {
                    return false;
                }
				if (record && !record.get('XmlTemplate_id') && record.get('XmlTemplateCat_id')) {
					win.doChangeXmlTemplateCat({XmlTemplateCat_id: record.get('XmlTemplateCat_id'), XmlTemplateCat_Name: record.get('Item_Name'), levelUp: true});
					return true;
				}
				
				win.onSelectButtonClick();
				
                return true;
			}
		})
		

		
		this.rightPanel = Ext.create('Ext.panel.Panel',{
			flex: 2,
//			minWidth: 400,
			collapsed: false,
//			region: 'center',
			autoScroll: true,
			animCollapse: false,
//			bodyStyle: 'background-color: #e3e3e3; padding: 10px;',
//			minSize: 400,
//			floatable: false,
//			collapsible: false,
			split: true,
//			layoutConfig:
//			{
//				titleCollapse: true,
//				animate: true,
//				activeOnTop: false,
//				style: 'border: 0px'
//			},
			html: ''
		});
		
		this.toolbar = Ext.create('Ext.toolbar.Toolbar',{
			items: [
				{
					xtype: 'button',
					disabled: true,
					itemId: 'action_setdefault',
					text: 'По умолчанию',
					tooltip: 'Назначить шаблон/папку по умолчанию', 
					iconCls : 'x-btn-text', 
					icon: 'img/icons/save16.png',
					handler: function() {
						win.setDefault();
					}
				},
				{
					xtype: 'button',
					disabled: true,
					itemId: 'action_upperfolder',
					text: 'На уровень выше',
					tooltip: 'На уровень выше', 
					iconCls : 'x-btn-text', 
					icon: 'img/icons/arrow-previous16.png',
					handler: function() {
						win.XmlTemplateCatView.goToUpperLevel();
					}
				},
				{
					xtype: 'button',
					itemId: 'action_refresh',
					text: 'Обновить',
					tooltip: 'Обновить', 
					iconCls : 'x-btn-text', 
					icon: 'img/icons/refresh16.png',
					handler: function() {
						win.grid.getStore().reload();
					}
				}
			],
			getByItemId: function(itemId) {
				if (!itemId) {
					return false;
				}
				return this.down('[itemId='+itemId+']')
			}
		});
		
		Ext.apply(this, 
		{
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			items: [{
				xtype:'BaseForm',
				cls: 'mainFormNeptune',
				autoScroll: true,
				id: this.id+'_BaseForm',
				flex: 1,
				width: '100%',
				height: '100%',
				layout: {
					padding: '0 0 0 0', // [top, right, bottom, left]
					align: 'stretch',
					type: 'vbox'
				},
				items: [
					win.XmlTemplateCombo,
					this.XmlTemplateCatView,
					{
						flex: 1,
						xtype: 'panel',
						id: 'testid',
						layout: {
							type: 'hbox',
							pack: 'start',
							align: 'stretch'
						},
						tbar: this.toolbar,
						items: [
							this.grid,
							this.rightPanel
						]
					}
				]
			}],
			listeners: {
				scope: this,
				show: function() 
				{
					
					this.filterXmlTypeCombo();
					
					this.doReset();
					this.doLoadData(true);
				}
			},
			buttons: [{
				handler: function() {
					win.onSelectButtonClick();
				},
				iconCls: 'ok16',
				//tabIndex: TABINDEX_ETSW + 19,
				text: 'Выбрать'
			}, '->'  ,
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				//tabIndex: TABINDEX_ETSW + 20,
				//onTabElement: 'ETSW_EvnClass_id',
				handler: function() {
					win.close();
				}
			}]
			//enableKeyEvents: true
//			keys: 
//			[{
//				alt: true,
//				fn: function(inp, e) 
//				{
//					if (e.getKey() == Ext.EventObject.ESC) {
//						win.hide();
//					} else if (e.getKey() == Ext.EventObject.DELETE) {
//						win.deleteTemplate();
//					} else if (e.getKey() == Ext.EventObject.ENTER) {
//						win.viewFrame.onEnter();
//					}
//				},
//				key: [ Ext.EventObject.ENTER, Ext.EventObject.ESC, Ext.EventObject.DELETE ],
//				scope: this,
//				stopEvent: false
//			}]
		});
		
		win.callParent(arguments);
	},

	doReset: function() {
		var grid = this.grid;
		this.XmlTemplateCatView.reset();
        this.previewXmlTemplateId = null;
	},
	doChangeXmlTemplateCat: function(data) 
	{
		
		this.grid.store.getProxy().setExtraParam('XmlTemplateCat_id',(data.XmlTemplateCat_id == 0)?null:data.XmlTemplateCat_id);

		if(data.levelUp) {
			this.XmlTemplateCatView.addRecord(data);
		} else if(!data.XmlTemplateCat_Level || data.XmlTemplateCat_Level != this.XmlTemplateCatView.lastLevel) {
			this.XmlTemplateCatView.update(data);
		}
		this.doLoadData();
    },
    /**
     * Если указан документ, то определяем его шаблон и
     * после загрузки выделяем его в гриде
     * @param EvnXml_id
     * @param params
     * @param callback
     * @return {Boolean}
     */
    defineXmlTemplate: function(EvnXml_id, params, callback)
    {
		var win = this;
		
        if(typeof callback != 'function') {
            callback = Ext.emptyFn;
        }
        if(!EvnXml_id || !(EvnXml_id>0)) {
            win.grid.getStore().loadPage(1,{
                params: params,
                callback: callback
            });
            return true;
        }
        //var form = this.filterPanel.getForm();
        // шлем запрос
		var loadMask =  new Ext.LoadMask(win, {msg:"Подождите, идет определение шаблона документа..."});
        loadMask.show();
        Ext.Ajax.request({
            url: '/?c=EvnXml&m=getXmlTemplateId',
            params: {EvnXml_id: EvnXml_id},
            callback: function(o, s, response)
            {
                loadMask.hide();
                if ( s )
                {
                    var result = Ext.JSON.decode(response.responseText);
                    if(result.XmlTemplate_id > 0) {
                        win.selectRecId = 'XmlTemplate_'+ result.XmlTemplate_id;
                        win.XmlTemplateCatView.reset();
                        params.XmlTemplateCat_id = result.XmlTemplateCat_id||null;
                        //form.findField('XmlTemplateCat_id').setValue(params.XmlTemplateCat_id);
                        if (result.XmlTemplateCat_id) {
                            var cat_list = [{XmlTemplateCat_id: result.XmlTemplateCat_id, XmlTemplateCat_Name: result.XmlTemplateCat_Name}];
                            for(var i=0;i<7;i++) {
                                if(result['XmlTemplateCat_pid'+i]) {
                                    cat_list.push({XmlTemplateCat_id: result['XmlTemplateCat_pid'+i], XmlTemplateCat_Name: result['XmlTemplateCat_Name'+i]});
                                }
                            }
                            cat_list.reverse();
                            for(i=0;i<cat_list.length;i++) {
                                win.XmlTemplateCatView.addRecord(cat_list[i]);
                            }
                        }
                        params.showXmlTemplate_id = result.XmlTemplate_id;
                        win.grid.getStore().loadPage(1,{
                            params:params,
                            callback: callback
                        });
                    } else {
                        win.selectRecId = null;
                        params.showXmlTemplate_id = null;
                        win.grid.getStore().load(1,{
                            params:params,
                            callback: callback
                        });
                    }
                }
            }
        });
        return true;
    },
    /**
     * Определяем папку по умолчанию.
     * @param options
     * @return {Boolean}
     */
    getXmlTemplateCatDefault: function(options)
    {
		
		var win = this;
		
		if(typeof options.callback != 'function') {
			options.callback = Ext.emptyFn;
		}
		var xmltemplatecatdefault_id = null,
			params = {};
			//form = this.filterPanel.getForm();
		params.EvnClass_id = (options.EvnClass_id)?options.EvnClass_id:win.EvnClass_id;
		params.XmlType_id = (options.XmlType_id)?options.XmlType_id:win.XmlType_id;
		params.MedStaffFact_id = (getGlobalOptions().CurMedStaffFact_id) || null;
		
		params.LpuSection_id = (getGlobalOptions().CurLpuSection_id) || null;
		params.MedService_id = (getGlobalOptions().CurMedService_id) || null;
		params.MedPersonal_id = (getGlobalOptions().CurMedPersonal_id) || null;
				
        sw.Promed.XmlTemplateCatDefault.loadId(params, function(s, res, result){
            if (s) {
                if (result.length > 0) {
                    var cat_list = [{XmlTemplateCat_id: result[0].XmlTemplateCat_id, XmlTemplateCat_Name: result[0].XmlTemplateCat_Name}];
                    xmltemplatecatdefault_id = result[0].XmlTemplateCat_id;
                    for(var i=0;i<7;i++) {
                        if(result[0]['XmlTemplateCat_pid'+i]) {
                            cat_list.push({XmlTemplateCat_id: result[0]['XmlTemplateCat_pid'+i], XmlTemplateCat_Name: result[0]['XmlTemplateCat_Name'+i]});
                        }
                    }
                    cat_list.reverse();
                    for(i=0;i<cat_list.length;i++) {
                        this.XmlTemplateCatView.addRecord(cat_list[i]);
                    }
                    //form.findField('XmlTemplateCat_id').setValue(xmltemplatecatdefault_id);
                    options.callback(xmltemplatecatdefault_id);
                } else {
                    //автоматически создать папку
                    sw.Promed.XmlTemplateCatDefault.create(params, function(s, res, result){
                        if (s) {
                            if (result.length > 0) {
                                var cat_list = [{XmlTemplateCat_id: result[0].XmlTemplateCat_id, XmlTemplateCat_Name: result[0].XmlTemplateCat_Name}];
                                xmltemplatecatdefault_id = result[0].XmlTemplateCat_id;
                                for(i=0;i<cat_list.length;i++) {
                                    this.XmlTemplateCatView.addRecord(cat_list[i]);
                                }
                                //form.findField('XmlTemplateCat_id').setValue(xmltemplatecatdefault_id);
                                options.callback(xmltemplatecatdefault_id);
                            }
                        } else {
							Ext.Msg.alert('Ошибка', res);
                            //options.callback(null);
                        }
                    }, this);
                }
            } else {
                Ext.Msg.alert('Ошибка', res);
                //options.callback(null);
            }
        }, this);
	},
	doLoadData: function(is_show, rec_id)
	{
		var win = this
			,grid = this.grid
			,params = {
				EvnClass_id : win.EvnClass_id || null,
				XmlType_id : win.XmlTemplateCombo.getValue() || win.XmlType_id || null,
				UslugaComplex_id : win.UslugaComplex_id || null
			};
		//grid.getStore().baseParams = {};
		//grid.getStore().removeAll();
		//this.viewFrame.removeAll(true);
        this.selectRecId = rec_id || null;
//		params.limit = 50;
//        params.start = 0;
//        if (form.findField('XmlTemplate_onlyOld').checked) {
//            params.XmlTemplate_onlyOld = 1;
//        }
		
//		if(form.findField('EvnClass_id').disabled)
//			params.EvnClass_id = form.findField('EvnClass_id').getValue();
//        if(form.findField('XmlType_id').disabled)
//            params.XmlType_id = form.findField('XmlType_id').getValue();
//        if(form.findField('UslugaComplex_id').disabled)
//            params.UslugaComplex_id = form.findField('UslugaComplex_id').getValue();
		
		
        var callback = function(recs) {
            var i= 0,
                record;
            if (this.selectRecId) {
                record = grid.getStore().getById(this.selectRecId);
            }
            if (record) {
                i = grid.getStore().indexOf(record);
                grid.getSelectionModel().select(i);
				
				// function( grid, record, index, eOpts ) {
				grid.fireEvent('select',grid, record, i);
                //this.viewFrame.onRowSelect(grid.getSelectionModel(),i,record);
            } else if (recs[0]) {
                record = recs[0];
                grid.getSelectionModel().select(i);
				grid.fireEvent('select',grid, record, 0);
                //this.viewFrame.onRowSelect(grid.getSelectionModel(),i,record);
            }
        }.bind(this);

		
		if(is_show) {
			this.getXmlTemplateCatDefault({
                EvnClass_id: params.EvnClass_id,
				XmlType_id: params.XmlType_id,
				callback: function(id){
					if(id) params.XmlTemplateCat_id = id;
                    this.defineXmlTemplate(this.EvnXml_id, params, callback);
				}.bind(this)
			});
		} else {
			this.grid.getStore().loadPage(1,{
				params:params,
				callback: callback
			});
		}
	},
	getSelectedRecord: function(allow_msg) {
		var selection = this.grid.getSelectionModel().getSelection();
		if (!selection || !selection[0]) {
			if(allow_msg) {
				Ext.Msg.alert(this.title, 'Отсутствует выбранный элемент');
			}
			return false;
		}
		var r = selection[0];
		if (r)
		{
			return r;
		}
		else
		{
			
			if(allow_msg) {
				Ext.Msg.alert(this.title, 'Отсутствует выбранный элемент');
			}
			return false;
		}
	},
	onSelectButtonClick: function ()
	{
		
		var record = this.getSelectedRecord(true);
		
		if(!record || !record.get('XmlTemplate_id') || typeof this.onSelect != 'function') {
			return false;
		}
		
        if ( !record.get('XmlTemplateType_id').toString().inlist(['6','7']) ) {
			
			var $wiki_path;
		
			if (getRegionNick() == 'astra'){ //http://redmine.swan.perm.ru/issues/32624
				if(IS_DEBUG)
					$wiki_path = 'http://192.168.36.64/wiki/main/wiki/';
				else
					$wiki_path = 'https://astrahan.promedweb.ru/wiki/main/wiki/';
			}
			else
				$wiki_path = '/wiki/index/';

			
			var msg = '<b>Вами выбран шаблон старого формата!</b>' +
                    '<br>Необходимо конвертировать выбранный шаблон в новый формат!' +
                    '<br>Откройте шаблон на редактирование и сохраните, затем его можно будет выбрать.' +
					'<br><a href="'+$wiki_path+'Шаблоны_документов#Инструкция_по_конвертации_шаблонов_в_новый_формат" target="blank">Инструкция по конвертации</a>';
				
			Ext.Msg.alert(this.title, msg);
            return false;
        }
		this.onSelect(record.data);
		
		this.close();
	},
	filterXmlTypeCombo: function() {
		if (Ext.isArray(this.XmlTypeFilterValues) && this.XmlTypeFilterValues.length) {
			this.XmlTemplateCombo.getStore().filter(this.XmlTemplateCombo.valueField ,new RegExp(this.XmlTypeFilterValues.join('|')));
		}
	},
	setDefault: function ()
    {
		var win = this,
			selection = win.grid.getSelectionModel().getSelection(),
			record = (selection.length)?selection[0]:null,
            params = {
				XmlType_id: win.XmlTemplateCombo.getValue() || win.XmlType_id || null,
				EvnClass_id: win.EvnClass_id || null,
				UslugaComplex_id: win.UslugaComplex_id || null,
				MedStaffFact_id: (getGlobalOptions().CurMedStaffFact_id) || null,
				MedPersonal_id: (getGlobalOptions().CurMedPersonal_id) || null,
				MedService_id: (getGlobalOptions().CurMedService_id) || null,
				LpuSection_id: (getGlobalOptions().CurLpuSection_id) || null
			};
		if (!record) {
			Ext.Msg.alert('Ошибка','Выбреите запись');
			return false;
		}
		
		if (record.get('XmlTemplate_id')) {
			params.XmlTemplate_id = record.get('XmlTemplate_id');

			var loadMask = new Ext.LoadMask(win, {msg: 'Подождите, идет сохранение шаблона как по умолчанию...'});
			loadMask.show();
			Ext.Ajax.request(
			{
				url: '/?c=XmlTemplateDefault&m=save',
				callback: function(options, success, response) 
				{
					loadMask.hide();
					if ( success )
					{
						var result = Ext.JSON.decode(response.responseText);
						if ( result && result['success'] && result['success'] === true )
						{					
							win.onSelect(record.data);
							win.close();
						}
					}
				},
				params: params
			});
            return true;
		} else if(record.get('XmlTemplateCat_id')) {
			params.XmlTemplateCat_id = record.get('XmlTemplateCat_id');
			// шлем запрос на сохранение папки по умолчанию для текущего пользователя на текущем рабочем месте
            sw.Promed.XmlTemplateCatDefault.save(params, function(s, res){
                if (s) {
                    this.doChangeXmlTemplateCat({XmlTemplateCat_id: record.get('XmlTemplateCat_id'), XmlTemplateCat_Name: record.get('Item_Name'), levelUp: true});
                } else {
                    Ext.Msg.alert('Ошибка', res);
                }
            }, this);
            return true;
		}
        return false;
    }
});