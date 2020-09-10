/**
* swTemplSearchWindow - форма просмотра и выбора шаблонов
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swTemplSearchWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      19.02.2012
* @comment      Префикс для id компонентов ETSW.
* 
* @input data: LpuSection_id
* @input data: EvnClass_id - идентификатор класса события
* @input data: XmlType_id - идентификатор класса документов
* @input function: onSelect - функция, вызываемая при выборе шаблона, получает идентификатор шаблона в параметрах. 
*
* Использует:	окно редактирования шаблона (swXmlTemplateEditWindow)
* 				окно редактирования свойств шаблона (swXmlTemplateSettingsEditWindow)
*/

/*NO PARSE JSON*/

sw.Promed.swTemplSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swTemplSearchWindow',
	objectSrc: '/jscore/Forms/Common/swTemplSearchWindow.js',
	//maximizable: true,
	maximized: true,
	id: 'ETSW',
	layout: 'border',
	autoScroll: true,
	height: 570,
	width: 800,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: false,
	plain: false,
	collapsible: false,
	resizable: false,
	title: lang['shablonyi_dokumentov'],

	onSelect: null,
	mode: null,
    selectRecId: null,
    previewXmlTemplateId: null,
	onEnvClassStoreLoaded: function(evt_object) {//#182701
		var excluded = this.getExcludedEvnClasses(),
			combo_store = evt_object;
		if (excluded) {
			if (excluded.length > 0) {

				excluded.forEach(function (id) {
					combo_store.removeAt(combo_store.find('EvnClass_id', id));
				});
			}
		}
	},
	initComponent: function() 
	{
		var win = this;
		this.filterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'left',
			labelWidth: 120,
			region: 'north',
			items:
            [{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['filtryi'],
				collapsible: true,
				layout: 'form',
				//style: 'margin: 5',
				listeners: {
					collapse: function(p) {
						win.doLayout();
					},
					expand: function(p) {
						win.doLayout();
					}
				},
				items:
                [{
                    name: 'MedStaffFact_id',//ид рабочего места врача текущего пользователя
                    xtype: 'hidden'
                },{
                    name: 'MedPersonal_id',//ид врача текущего пользователя
                    xtype: 'hidden'
                },{
                    name: 'LpuSection_id',//отделение текущего пользователя
                    xtype: 'hidden'
                },{
                    name: 'MedService_id',//служба текущего пользователя
                    xtype: 'hidden'
                },{
                    name: 'XmlTemplateCat_id',//текущая папка
                    xtype: 'hidden'
                },{
                    layout:'column',
                    items:[{
                        layout: 'form',
                        width: 260,
                        items: [{
                            fieldLabel: lang['kategoriya'],
                            hideLabel: true,
                            emptyText: lang['vyiberite_kategoriyu'],
                            id: 'ETSW_EvnClass_id',
                            hiddenName: 'EvnClass_id',
                            width: 250,
                            tabIndex: TABINDEX_ETSW,
                            autoLoad: false,
                            xtype: 'swevnclasscombo',
                            listeners: {
								'render': function (combo) {
									combo.store.addListener('load', this.onEnvClassStoreLoaded, this);
								}.createDelegate(this),
                                select: function(combo) {
                                    // win.doLoadData();
                                    var bf = win.filterPanel.getForm();
                                    var type_combo = bf.findField('XmlType_id'),
                                        not_view_id_list = sw.Promed.EvnXml.getNotViewXmlTypeIdList(combo.getValue());
                                    type_combo.getStore().clearFilter();
									type_combo.lastQuery = '';
                                    if (combo.getValue()) {
                                        if (0 == type_combo.getStore().getCount()) {
                                            type_combo.getStore().load({
                                                callback: function() {
                                                    type_combo.funcFilterBy();
                                                    // type_combo.getStore().filterBy(function(rec) {
                                                    //     return (false == rec.get('XmlType_id').toString().inlist(not_view_id_list));
                                                    // });
                                                }
                                            });
                                        } else {
                                            type_combo.funcFilterBy();
                                            // type_combo.getStore().filterBy(function(rec) {
                                            //     return (false == rec.get('XmlType_id').toString().inlist(not_view_id_list));
                                            // });
                                        }
                                    }

                                    if (win.getInitXmlTypeId() && !win.getInitXmlTypeId().toString().inlist(not_view_id_list)) {
                                    	type_combo.setDisabled(win.allowSelectXmlType == false);
                                        type_combo.setValue(type_combo.getValue());
                                    } else {
                                        type_combo.enable();
                                        type_combo.setValue(null);
                                    }
                                    type_combo.fireEvent('select', type_combo);
                                }
                            }
                        }]
                    },{
                        layout: 'form',
                        width: 260,
                        items: [{
                            fieldLabel: lang['tip_dokumenta'],
                            hideLabel: true,
                            emptyText: lang['vyiberite_tip_dokumenta'],
                            width: 250,
                            id: 'ETSW_EvnType_id',
                            tabIndex: TABINDEX_ETSW+1,
                            comboSubject: 'XmlType',
                            allowSysNick: false,
                            autoLoad: false,
                            xtype: 'swcommonsprcombo',
                            typeCode: 'int',
                            hiddenName: 'XmlType_id',
                            listeners: {
                               'select': function(combo) {
                                    //win.doLoadData();
                                   var form = win.filterPanel.getForm(),
                                       usluga_complex_combo = form.findField('UslugaComplex_id'),
                                       xml_type_combo = form.findField('XmlTypeKind_id');
                                   //setContainerVisible
                                   if (!combo.getValue() || combo.getValue().inlist([4,7])) { //isSuperAdmin() ||
                                       usluga_complex_combo.showContainer();
                                   } else {
                                       usluga_complex_combo.hideContainer();
                                   }
                                   if (10 == combo.getValue()) { // Типы эпикриза
										xml_type_combo.getStore().filterBy(function(rec) {
											return (combo.getValue() == rec.get('XmlType_id'));
										});
                                       xml_type_combo.showContainer();
                                   } else {
                                       xml_type_combo.hideContainer();
                                   }
                               }
                            },
                            funcFilterBy: function(){
                                var bf = win.filterPanel.getForm();
                                var evnclass_id = bf.findField('EvnClass_id').getValue();
                                if(
                                    getRegionNick().inlist(['vologda','msk','ufa']) 
                                    && win.isEMK && evnclass_id == 32 && win.itemSectionCode
                                    && win.ARMType && win.ARMType.inlist(['stacpriem','stac'])
                                    && win.itemSectionCode == 'EvnXmlOther'
                                ){
                                    var view_id_list = [2,21]; //Документ в свободной форме, Опись вещей и ценностей
                                    this.getStore().filterBy(function(rec) {
                                        return (rec.get('XmlType_id').toString().inlist(view_id_list));
                                    });
                                }else{
                                    var not_view_id_list = sw.Promed.EvnXml.getNotViewXmlTypeIdList(evnclass_id);
                                    this.getStore().filterBy(function(rec) {
                                        return (false == rec.get('XmlType_id').toString().inlist(not_view_id_list));
                                    });
                                }
                            }
                        }]
                    },{
                        layout: 'form',
                        width: 310,
                        items: [{
                            fieldLabel: lang['usluga'],
                            hideLabel: true,
                            //ид услуги, для протокола которой выбирается шаблон
                            emptyText: lang['vyiberite_uslugu'],
                            id: 'UslugaComplex',
                            hiddenName: 'UslugaComplex_id',
                            listWidth: 500,
                            width: 300,
                            xtype: 'swuslugacomplexnewcombo',
                            listeners: {
                                select: function(combo, rec, i) {
                                   // win.doLoadData();
                                }
                            }
                        }, {
                            fieldLabel: 'Вид документа',
                            hideLabel: true,
                            emptyText: 'Выберите вид',
                            hiddenName: 'XmlTypeKind_id',
							moreFields: [{name: 'XmlType_id', mapping: 'XmlType_id'}],
                            listWidth: 300,
                            width: 300,
                            xtype: 'swcommonsprcombo',
                            typeCode: 'int',
                            comboSubject: 'XmlTypeKind',
                            listeners: {
                                select: function(combo, rec, i) {
                                   // win.doLoadData();
                                }
                            }
                        }]
                    }] // end base items
                },{
                    layout:'column',
                    items:[{
                        layout: 'form',
                        labelWidth: 80,
                        width: 250,
                        items: [{
                            fieldLabel: lang['iskat_tekst'],
                            name: 'templName',
                            width: 150,
                            enableKeyEvents: true,
                            disabled: false,
                            xtype: 'textfield',
                            listeners:{
                                'keydown': function (inp, e) {
                                    if (e.getKey() == Ext.EventObject.ENTER) {
                                        win.doLoadData();
                                    }
                                }
                            }
                        }]
                    },{
                        layout: 'form',
                        //width: 360,
                        width: 500,
                        items: [{
                            xtype: 'radiogroup',
                            hideLabel: true,
                            name:'templRadioGroup',
                            columns: 2,
                            vertical: true,
                            items: [
                                {boxLabel: lang['v_nazvanii'], name: 'templType', inputValue: 1, checked: true},
                                {boxLabel: lang['v_shablone'], name: 'templType', inputValue: 2}
                            ]
                        }]
                    }] // end text filters
                },{
                    width: 400,
                    hideLabel: true,
                    boxLabel: lang['tolko_shablonyi_staryih_tipov'],
                    hidden: !isSuperAdmin(),
                    xtype: 'checkbox',
                    name: 'XmlTemplate_onlyOld',
                    listeners: {
                        check: function(){
                            //win.doLoadData();
                        }
                    }
                },{
                    layout:'column',
                    items:[{
                        layout: 'form',
                        width: 100,
                        items: [{
                            xtype: 'button',
                            id: 'wpsfBtnSearch',
                            text: lang['nayti'],
                            iconCls: 'search16',
                            handler: function()
                            {
                                win.doLoadData();
                            }
                        }]
                    },{
                        layout: 'form',
                        width: 100,
                        items: [{
                            xtype: 'button',
                            id: 'wpsfBtnClear',
                            text: lang['sbros'],
                            iconCls: 'resetsearch16',
                            handler: function()
                            {
                                win.doReset();
                                win.doLoadData();
                            }
                        }]
                    }] // end items row buttons
                }] // end items fieldset
			}], // end items filterPanel
			keys: [{
				/*fn: function(e) {
					win.doLoadData();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true*/
			}]
		});

		this.XmlTemplateCatView = new Ext.Panel({
			lastLevel: null,
			lastRecord: null,
			store: new Ext.data.SimpleStore({
				key: 'XmlTemplateCat_id',
				fields:
				[
					{name: 'XmlTemplateCat_id', type: 'int'},
					{name: 'XmlTemplateCat_Level', type: 'int'},
					{name: 'XmlTemplateCat_Name', type: 'string'},
                    {name: 'accessType', type: 'string'}
				],
				data: []
			}),
			autoHeight: true,
			buttonAlign: 'left',
			//frame: true,
			region: 'north',
			layout: 'column',
			style: 'border: 0; padding: 0px; height:25px; background: #FFF;',
			items: [],
			XmlTemplateCatRoot_id: 0,
			XmlTemplateCatRoot_Name: lang['kornevaya_papka'],
			reset: function() {
				this.removeAll();
				this.store.removeAll();
				this.lastRecord = null;
				this.lastLevel = -1;
				this.addRecord({
					accessType: sw.Promed.XmlTemplateCatDefault.isAllowRootFolder() ? 'edit' : 'view',
					XmlTemplateCat_id: this.XmlTemplateCatRoot_id,
					XmlTemplateCat_Name: this.XmlTemplateCatRoot_Name
				});
			},
			update: function(data) {
				this.lastRecord = null;
				
				if(data.XmlTemplateCat_id == 0) {
					this.reset();
					win.viewFrame.ViewActions.action_upperfolder.setDisabled(true);
				} else {
					this.lastLevel = data.XmlTemplateCat_Level;
					win.viewFrame.ViewActions.action_upperfolder.setDisabled(false);
					this.store.each(function(record){
						if(record.get('XmlTemplateCat_Level') > data.XmlTemplateCat_Level) {
							this.remove('XmlTemplateCatCmp_'+record.get('XmlTemplateCat_id'));
							this.store.remove(record);
							this.doLayout();
							this.syncSize();
						}
						
						if(record.get('XmlTemplateCat_Level') == data.XmlTemplateCat_Level) {
							this.buttonIntoText(record);
							this.lastRecord = record;
						}
						
						return true;
					},this);
				}
			},
			goToUpperLevel: function(data) {
				var prev = new Ext.data.Record({
					accessType: sw.Promed.XmlTemplateCatDefault.isAllowRootFolder() ? 'edit' : 'view',
					XmlTemplateCat_id: this.XmlTemplateCatRoot_id,
					XmlTemplateCat_Name: this.XmlTemplateCatRoot_Name
				});
				var last = null;
				this.store.each(function(record){
					if (last != null) {
						prev = last;
						last = record;
					} else {
						last = record;
					}
				});
				win.doChangeXmlTemplateCat(prev.data);
			},
			buttonIntoText: function(record) {
				this.remove('XmlTemplateCatCmp_'+record.get('XmlTemplateCat_id'));
			
				this.add({
					layout: 'form',
					id: 'XmlTemplateCatCmp_'+record.get('XmlTemplateCat_id'),
					style: 'padding: 2px;',
					border: false,
					items: [
						new Ext.form.Label({
							record_id: record.id,
							html : "<img src='img/icons/folder16.png'>&nbsp;" + record.get('XmlTemplateCat_Name')
						})
					]
				});
				
			},
			textIntoButton: function(record) {
				this.remove('XmlTemplateCatCmp_'+record.get('XmlTemplateCat_id'));
			
				this.add({
					layout: 'form',
					id: 'XmlTemplateCatCmp_'+record.get('XmlTemplateCat_id'),
					style: 'padding: 2px;',
					border: false,
					items: [
						new Ext.Button({
							record_id: record.id,
							text : record.get('XmlTemplateCat_Name'),
							iconCls  : 'folder16',
							handler: function(btn,e){
								var record = this.store.getById(btn.record_id);
								if (record) {
                                    win.doChangeXmlTemplateCat(record.data);
                                }
							},
							scope: this
						})
					]
				});				
			},
			addRecord: function(data) {
				this.lastLevel++;
				var record = new Ext.data.Record({
                    accessType: data.accessType || null,
					XmlTemplateCat_id: data.XmlTemplateCat_id,
					XmlTemplateCat_Level: this.lastLevel,
					XmlTemplateCat_Name: data.XmlTemplateCat_Name
				});
				
				if(data.XmlTemplateCat_id != 0) {
					win.viewFrame.ViewActions.action_upperfolder.setDisabled(false);
				}
				
				this.store.add([record]);
				
				if (this.lastRecord != null) {
					// предыдущий текст заменяем на кнопку (удаляем текстовую, добавляем кнопку)
					this.textIntoButton(this.lastRecord);
				}
				
				// добавляем новую текстовую
				
				this.lastRecord = record;
				
				this.add({
					layout: 'form',
					id: 'XmlTemplateCatCmp_'+data.XmlTemplateCat_id,
					style: 'padding: 2px;',
					border: false,
					items: [
						new Ext.form.Label({
							record_id: record.id,
							html : "<img src='img/icons/folder16.png'>&nbsp;" + data.XmlTemplateCat_Name
						})
					]
				});
				
				this.doLayout();
				this.syncSize();
			}
		});
	
		this.viewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=XmlTemplate&m=loadGrid',
			object: 'XmlTemplate',
			editformclassname: 'swXmlTemplateEditWindow',
			actions:
			[
				{name:'action_add', tooltip: lang['dobavit_shablon'], icon: 'img/icons/copy16.png', handler: function(){win.showXmlTemplateEditWindow('add');}},
				{name:'action_edit', tooltip: lang['redaktirovat_shablon_papku'], handler: function(){win.showXmlTemplateEditWindow('edit');}},
				{name:'action_view', text: lang['svoystva'], tooltip: lang['redaktirovat_svoystva_shablona'], handler: function(){win.showXmlTemplateSettingsEditWindow();}},
				{name:'action_delete', tooltip: lang['udalit_shablon_papku'], handler: function(){win.deleteTemplate();}}
			],
			pageSize: 50,
			singleSelect:true,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{name: 'Item_Key', type: 'string', hidden: true, key: true},
				{header: lang['kategoriya'], type: 'string', name: 'EvnClass_Name', hidden: !isSuperAdmin(), width: 240},
				{id: 'autoexpand', header: lang['naimenovanie'], name: 'Item_Name', renderer: sw.Promed.Format.ItemNameColumn},
				{header: lang['avtor'], type: 'string', name: 'pmUser_Name', hidden: true},
				{header: lang['vidimost'],  type: 'string', name: 'XmlTemplateScope_Name', hidden: true},
				{header: lang['data_izmeneniya'], type: 'string', name: 'Item_updDate', hidden: true},
				{header: lang['nastroyki_pechati'], type: 'string', name: 'XmlTemplate_Settings', hidden: true},
				{name: 'accessType', type: 'string', hidden: true},
                {name: 'EvnClass_id', type: 'int', hidden: true, isparams: true},
                {name: 'XmlTemplateType_id', type: 'int', hidden: true},
				{name: 'XmlType_id', type: 'int', hidden: true, isparams: true},
				{name: 'XmlTemplateScope_id', type: 'int', hidden: true},
				{name: 'XmlTemplateScope_eid', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'LpuSection_id', type: 'int', hidden: true},
				{name: 'pmUser_insID', type: 'int', hidden: true},
				{name: 'XmlTemplate_id', type: 'int', hidden: true},
				{name: 'XmlTemplateCat_id', type: 'int', hidden: true, isparams: true},
				{name: 'XmlTemplate_Default', type: 'int', hidden: true},
                {name: 'XmlTemplate_Preview', type: 'string', hidden: true},
                {name: 'Item_Path', type: 'string', hidden: true}
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips(
				[
					{field: 'EvnClass_Name', tpl: '{pmUser_Name} {Item_updDate}'},
					{field: 'Item_Name', tpl: '{pmUser_Name} {Item_updDate}'}
				])
			],
			toolbar: true,
			onLoadData: function(flag) {
				if (win.viewMode == 'when_select_item_then_change_path') {
					win.onChangeXmlTemplateCat({});
				}
                win.previewXmlTemplateId = null;
			},
			onRowSelect: function(sm,rowIdx,record) {
                if (record.get('Item_Path')) {
                    win.XmlTemplateCatView.reset();
                    var item_path = Ext.util.JSON.decode(record.get('Item_Path'));
                    if (item_path && typeof item_path.reverse == 'function') {
                        item_path.reverse();
                        var i = 0;
                        while (item_path[i]) {
                            win.XmlTemplateCatView.addRecord(item_path[i]);
                            i++;
                        }
                    }
                }
				win.buttons[0].setDisabled(false == win.isAllowSelectTpl(record));
                // удаление папки или шаблона
                this.setActionDisabled('action_delete',(!record.get('Item_Key') || record.get('accessType') == 'view') /*|| win.viewMode == 'when_select_item_then_change_path'*/);
                // редактирование папки или шаблона
                this.setActionDisabled('action_edit',(!record.get('Item_Key')));
                // редактирование свойств шаблона
                this.setActionDisabled('action_view',(!record.get('XmlTemplate_id') || record.get('accessType') == 'view'));
                // назначение папки или шаблона как по умолчанию
                this.setActionDisabled('action_setdefault', (false == win.getParamsSetDefault(record)));
				if ( record.get('XmlTemplate_id') && record.get('XmlTemplate_id') != win.previewXmlTemplateId ) {
					var XmlTemplate_id = record.get('XmlTemplate_id');
                    win.previewXmlTemplateId = XmlTemplate_id;
					var XmlTemplate_Preview = record.get('XmlTemplate_Preview');
					var tpl = new Ext.XTemplate(XmlTemplate_Preview);
					tpl.overwrite(win.rightPanel.body, {});
					
					var url = '/?c=XmlTemplate&m=preview&XmlTemplate_id=' + XmlTemplate_id;
					
					if (win.Evn_id && win.Evn_id != null) {
						url = url + '&Evn_id=' + win.Evn_id;
					}
					if (win.EvnXml_id && win.EvnXml_id != null) {
						url = url + '&EvnXml_id=' + win.EvnXml_id;
					}
					// подгрузить предпросмотр шаблона
					Ext.Ajax.request(
					{
						url: url,
						callback: function(o, s, response) 
						{
							var response_obj = [], html_template;
                            if (s && response.responseText) {
                                response_obj = Ext.util.JSON.decode(response.responseText);
                                if (response_obj[0]) {
                                    html_template = response_obj[0].XmlTemplate_HtmlTemplate;
                                }
                            }
                            var html = XmlTemplate_Preview + '<br><br><div style="padding:10px; background: #FFF;">';
                            if (html_template) {
                                html += html_template;
                            } else {
                                html += lang['ne_udalos_poluchit_shablon'];
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
			onCellDblClick: function(grid, rowIdx, colIdx, event) {
				return false;
			},
			onDblClick: function(grid, number, object){
				this.onEnter();
			},
			onEnter: function()
			{
				var record = win.getSelectedRecord(false);
                if (!record.get('Item_Key')) {
                    return false;
                }
				if (record && !record.get('XmlTemplate_id') && record.get('XmlTemplateCat_id')) {
					win.doChangeXmlTemplateCat({accessType: record.get('accessType'), XmlTemplateCat_id: record.get('XmlTemplateCat_id'), XmlTemplateCat_Name: record.get('Item_Name'), levelUp: true});
					return true;
				}

				if (win.isAllowSelectTpl(record)) {
					win.onSelectButtonClick();
				} else if (record.get('accessType') != 'view') {
					this.runAction('action_edit');
				}
                return true;
			}
		});
		
		this.viewFrame.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';
				if (row.get('XmlTemplate_Default')==2)
					cls = cls+'x-grid-rowselect ';
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});

		this.leftPanel = 
		{
			animCollapse: false,
			bodyStyle: 'padding: 5px',
			region: 'west',
			layout: 'fit',
			border: true,
			width: 550,
			titleCollapse: true,
			items: [this.viewFrame]
		};
		
		this.rightPanel = new Ext.Panel(
		{
			collapsed: false,
			region: 'center',
			autoScroll: true,
			animCollapse: false,
			bodyStyle: 'background-color: #e3e3e3; padding: 10px;',
			minSize: 400,
			floatable: false,
			collapsible: false,
			split: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false,
				style: 'border: 0px'
			},
			html: ''
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: [
				this.filterPanel,
				{
					layout: 'border',
					region: 'center',
					border: false,
					items: [
						this.XmlTemplateCatView,
						{
							layout: 'border',
							region: 'center',
							tbar: this.viewFrame.ViewGridPanel.topToolbar,
							items: [
								this.leftPanel,
								{
									region: 'center',
									layout: 'border',
									items: [
										this.rightPanel
									]
								}
							]
						}
					]
				}
			],
			buttons: [{
				handler: function() {
					win.onSelectButtonClick();
				},
				iconCls: 'ok16',
				tabIndex: TABINDEX_ETSW + 19,
				text: lang['vyibrat']
			}, {
				text: '-'
			}, 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: TABINDEX_ETSW + 20,
				onTabElement: 'ETSW_EvnClass_id',
				handler: function() {
					if(win.onSelect && typeof win.onSelect === 'function'){
						win.onSelect({ isNotSelect: win.isEmpty });
					}
					win.hide();
				}
			}],
			enableKeyEvents: true,
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					if (e.getKey() == Ext.EventObject.ESC) {
						win.hide();
					} else if (e.getKey() == Ext.EventObject.DELETE) {
						win.deleteTemplate();
					} else if (e.getKey() == Ext.EventObject.ENTER) {
						win.viewFrame.onEnter();
					}
				},
				key: [ Ext.EventObject.ENTER, Ext.EventObject.ESC, Ext.EventObject.DELETE ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swTemplSearchWindow.superclass.initComponent.apply(this, arguments);
		this.viewFrame.ViewToolbar.on('render', function(vt){
			this.ViewActions['action_setdefault'] = new Ext.Action({name:'action_setdefault', id: 'id_action_setdefault', handler: function() {win.setDefault();}, text:lang['po_umolchaniyu'], tooltip: lang['naznachit_shablon_papku_po_umolchaniyu'], iconCls : 'x-btn-text', icon: 'img/icons/save16.png'});
			//this.ViewActions['action_setdefaultcat'] = new Ext.Action({name:'action_setdefaultcat', id: 'id_action_setdefaultcat', disabled: true, handler: function() { win.setDefaultXmlTemplateCat(); }, text:'По умолчанию', tooltip: 'Назначить папку по умолчанию', iconCls : 'x-btn-text', icon: 'img/icons/save16.png'});
			this.ViewActions['action_addfolder'] = new Ext.Action({name:'action_addfolder', id: 'id_action_addfolder', handler: function() {win.openXmlTemplateCatEditWindow();}, text:lang['dobavit_papku'], tooltip: lang['dobavit_papku'], iconCls : 'x-btn-text', icon: 'img/icons/add16.png'});
			this.ViewActions['action_upperfolder'] = new Ext.Action({name:'action_upperfolder', id: 'id_action_upperfolder', handler: function() {win.XmlTemplateCatView.goToUpperLevel();}, text:lang['na_uroven_vyishe'], disabled: true, tooltip: lang['na_uroven_vyishe'], iconCls : 'x-btn-text', icon: 'img/icons/arrow-previous16.png'});
			vt.insertButton(1,this.ViewActions['action_setdefault']);
			vt.insertButton(1,this.ViewActions['action_addfolder']);
			vt.insertButton(1,this.ViewActions['action_upperfolder']);
			//vt.insertButton(1,this.ViewActions['action_setdefaultcat']);
			return true;
		}, this.viewFrame);},
	getExcludedEvnClasses: function (){
		return this.excludedEvnClasses;
	},
	doReset: function() {
		var form = this.filterPanel.getForm(),
			grid = this.viewFrame.getGrid();
		form.reset();
		form.findField('XmlTemplateCat_id').setValue(null);
		this.XmlTemplateCatView.reset();
		grid.getStore().baseParams = {};
		grid.getStore().removeAll();
		this.viewFrame.removeAll(true);
		this.viewFrame.setParam('limit', 50);
		this.viewFrame.setParam('start', 0);
        this.previewXmlTemplateId = null;
        form.findField('XmlType_id').setValue(this.getInitXmlTypeId());
        form.findField('XmlType_id').fireEvent('select', form.findField('XmlType_id'));
        form.findField('EvnClass_id').setValue(this.getInitEvnClassId());
        //Уфа не хочет, чтобы услуга подставлялась #26842
        if (getRegionNick() != 'ufa') {
            form.findField('UslugaComplex_id').setValue(this.getInitUslugaComplexId());
        }
        form.findField('XmlTypeKind_id').setValue(this.getInitXmlTypeKindId());
        form.findField('LpuSection_id').setValue(this.getInitLpuSectionId());
        form.findField('MedService_id').setValue(this.getInitMedServiceId());
        form.findField('MedPersonal_id').setValue(this.getInitMedPersonalId());
        form.findField('MedStaffFact_id').setValue(this.getInitMedStaffFactId());
		this.buttons[0].setDisabled(true);
		/*if(form.findField('EvnClass_id').disabled)
			form.findField('EvnClass_id').focus(true, 250);
		else
			this.buttons[2].focus(true, 250);*/
	},
	doChangeXmlTemplateCat: function(data) 
	{
        var form = this.filterPanel.getForm();
		if(data.XmlTemplateCat_id == 0) {
            form.findField('XmlTemplateCat_id').setValue(null);
		} else {
            form.findField('XmlTemplateCat_id').setValue(data.XmlTemplateCat_id);
		}
        form.findField('templName').setValue(null);
        form.findField('templRadioGroup').setValue(1);
		this.viewMode = 'default';
		if(data.levelUp) {
			this.XmlTemplateCatView.addRecord(data);
		} else if(!data.XmlTemplateCat_Level || data.XmlTemplateCat_Level != this.XmlTemplateCatView.lastLevel) {
			this.XmlTemplateCatView.update(data);
		}
        this.doLoadData();
        this.onChangeXmlTemplateCat(data);
    },
    onChangeXmlTemplateCat: function(data)
    {
        //log('onChangeXmlTemplateCat', data);
        this.viewFrame.setActionDisabled('action_add', (!data.accessType || 'view' == data.accessType));
		if (data.XmlTemplateCat_id && data.XmlTemplateCat_id != this.XmlTemplateCatView.XmlTemplateCatRoot_id) {
			this.viewFrame.setActionDisabled('action_addfolder', (!data.accessType || 'view' == data.accessType || this.XmlTemplateCatView.lastLevel > 7));
		} else {
			this.viewFrame.setActionDisabled('action_addfolder', false);
		}
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
        if(typeof callback != 'function') {
            callback = Ext.emptyFn;
        }
        if(!EvnXml_id || !(EvnXml_id>0)) {
            this.viewFrame.loadData({
                globalFilters: params,
                callback: callback
            });
            return true;
        }
        var form = this.filterPanel.getForm();
        // шлем запрос
        var loadMask = this.getLoadMask(lang['podojdite_idet_opredelenie_shablona_dokumenta']);
        loadMask.show();
        Ext.Ajax.request({
            url: '/?c=EvnXml&m=getXmlTemplateId',
            callback: function(o, s, response)
            {
                loadMask.hide();
                if ( s )
                {
                    var result = Ext.util.JSON.decode(response.responseText);
                    if(result.XmlTemplate_id > 0) {
                        this.selectRecId = 'XmlTemplate_'+ result.XmlTemplate_id;
                        this.XmlTemplateCatView.reset();
                        params.XmlTemplateCat_id = result.XmlTemplateCat_id||null;
                        form.findField('XmlTemplateCat_id').setValue(params.XmlTemplateCat_id);
                        if (result.XmlTemplateCat_id) {
                            var cat_list = [{accessType: result.accessType, XmlTemplateCat_id: result.XmlTemplateCat_id, XmlTemplateCat_Name: result.XmlTemplateCat_Name}];
                            for(var i=0;i<7;i++) {
                                if(result['XmlTemplateCat_pid'+i]) {
                                    cat_list.push({accessType: result['accessType'+i], XmlTemplateCat_id: result['XmlTemplateCat_pid'+i], XmlTemplateCat_Name: result['XmlTemplateCat_Name'+i]});
                                }
                            }
                            cat_list.reverse();
                            for(i=0;i<cat_list.length;i++) {
                                this.XmlTemplateCatView.addRecord(cat_list[i]);
                            }
                            this.onChangeXmlTemplateCat(result);
                        } else {
                            this.onChangeXmlTemplateCat({
                                accessType: sw.Promed.XmlTemplateCatDefault.isAllowRootFolder() ? 'edit' : 'view',
                                XmlTemplateCat_id: this.XmlTemplateCatView.XmlTemplateCatRoot_id,
                                XmlTemplateCat_Name: this.XmlTemplateCatView.XmlTemplateCatRoot_Name
                            });
                        }
                        params.showXmlTemplate_id = result.XmlTemplate_id;
                        this.viewFrame.loadData({
                            globalFilters:params,
                            callback: callback
                        });
                    } else {
                        this.selectRecId = null;
                        params.showXmlTemplate_id = null;
                        this.viewFrame.loadData({
                            globalFilters:params,
                            callback: callback
                        });
                    }
                }
            }.createDelegate(this),
            params: {
                LpuSection_id: form.findField('LpuSection_id').getValue() || sw.Promed.MedStaffFactByUser.last.LpuSection_id,
                EvnXml_id: EvnXml_id
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
		if(typeof options.callback != 'function') {
			options.callback = Ext.emptyFn;
		}
        if (sw.Promed.XmlTemplateCatDefault.isDisableDefaults(sw.Promed.MedStaffFactByUser.last)) {
            options.callback(null);
            return true;
        }
        var xmltemplatecatdefault_id = null,
			params = {},
			form = this.filterPanel.getForm();
		params.EvnClass_id = (options.EvnClass_id)?options.EvnClass_id:form.findField('EvnClass_id').getValue();
		params.XmlType_id = (options.XmlType_id)?options.XmlType_id:form.findField('XmlType_id').getValue();
        params.MedStaffFact_id = (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedStaffFact_id) || null;
        params.MedService_id = (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedService_id) || null;
        params.MedPersonal_id = (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedPersonal_id) || null;
        params.LpuSection_id = form.findField('LpuSection_id').getValue() || sw.Promed.MedStaffFactByUser.last.LpuSection_id;
        sw.Promed.XmlTemplateCatDefault.loadId(params, function(s, res, result){
            if (s) {
                if (result.length > 0) {
                    var cat_list = [{accessType: result[0].accessType, XmlTemplateCat_id: result[0].XmlTemplateCat_id, XmlTemplateCat_Name: result[0].XmlTemplateCat_Name}];
                    xmltemplatecatdefault_id = result[0].XmlTemplateCat_id;
                    for(var i=0;i<7;i++) {
                        if(result[0]['XmlTemplateCat_pid'+i]) {
                            cat_list.push({accessType: result[0]['accessType'+i], XmlTemplateCat_id: result[0]['XmlTemplateCat_pid'+i], XmlTemplateCat_Name: result[0]['XmlTemplateCat_Name'+i]});
                        }
                    }
                    cat_list.reverse();
                    for(i=0;i<cat_list.length;i++) {
                        this.XmlTemplateCatView.addRecord(cat_list[i]);
                    }
                    form.findField('XmlTemplateCat_id').setValue(xmltemplatecatdefault_id);
                    options.callback(xmltemplatecatdefault_id);
                    this.onChangeXmlTemplateCat(result[0]);
                } else {
                    options.callback(null);
                    this.onChangeXmlTemplateCat({
                        accessType: sw.Promed.XmlTemplateCatDefault.isAllowRootFolder() ? 'edit' : 'view',
                        XmlTemplateCat_id: this.XmlTemplateCatView.XmlTemplateCatRoot_id,
                        XmlTemplateCat_Name: this.XmlTemplateCatView.XmlTemplateCatRoot_Name
                    });
                }
            } else {
                sw.swMsg.alert(lang['oshibka'], res);
                options.callback(null);
                this.onChangeXmlTemplateCat({
                    accessType: sw.Promed.XmlTemplateCatDefault.isAllowRootFolder() ? 'edit' : 'view',
                    XmlTemplateCat_id: this.XmlTemplateCatView.XmlTemplateCatRoot_id,
                    XmlTemplateCat_Name: this.XmlTemplateCatView.XmlTemplateCatRoot_Name
                });
            }
        }, this);
        return true;
	},
	doLoadData: function(is_show, rec_id)
	{
		var me = this,
            form = me.filterPanel.getForm(),
			grid = me.viewFrame.getGrid(),
			params = form.getValues();
		grid.getStore().baseParams = {};
		grid.getStore().removeAll();
        me.viewFrame.removeAll(true);
        me.selectRecId = rec_id || null;
		params.limit = 50;
        params.start = 0;
        if (form.findField('XmlTemplate_onlyOld').checked) {
            params.XmlTemplate_onlyOld = 1;
        }
		if(form.findField('EvnClass_id').disabled)
			params.EvnClass_id = form.findField('EvnClass_id').getValue();
        if(form.findField('XmlType_id').disabled)
            params.XmlType_id = form.findField('XmlType_id').getValue();
        if(form.findField('UslugaComplex_id').disabled)
            params.UslugaComplex_id = form.findField('UslugaComplex_id').getValue();
        if(form.findField('XmlTypeKind_id').disabled)
            params.XmlTypeKind_id = form.findField('XmlTypeKind_id').getValue();
        me.viewMode = params.templName ? 'when_select_item_then_change_path' : 'default';
        if (me.viewMode == 'when_select_item_then_change_path') {
            me.XmlTemplateCatView.reset();
        }
        var callback = function(recs) {
            var i= 0,
                record;
            if (me.selectRecId) {
                record = grid.getStore().getById(me.selectRecId);
            }
            if (record) {
                i = grid.getStore().indexOf(record);
                grid.getSelectionModel().selectRow(i);
                grid.getView().focusRow(i);
                me.viewFrame.onRowSelect(grid.getSelectionModel(),i,record);
            } else if (recs[0]) {
                record = recs[0];
                grid.getSelectionModel().selectRow(i);
                grid.getView().focusRow(i);
                me.viewFrame.onRowSelect(grid.getSelectionModel(),i,record);
            }
        };
		if(is_show && !this.isEmpty) {
			this.getXmlTemplateCatDefault({
                EvnClass_id: params.EvnClass_id,
				XmlType_id: params.XmlType_id,
				callback: function(id){
					if(id) params.XmlTemplateCat_id = id;
                    this.defineXmlTemplate(this.EvnXml_id, params, callback);
				}.createDelegate(this)
			});
		} else {
			this.viewFrame.loadData({
                globalFilters:params,
                callback: callback
            });
		}
	},
	getSelectedRecord: function(allow_msg) {
		var r = this.viewFrame.ViewGridPanel.getSelectionModel().getSelected();
		if (r)
		{
			return r;
		}
		else
		{
			if(allow_msg) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: lang['vyi_nichego_ne_vyibrali'],
					title: this.title
				});
			}
			return false;
		}
	},
	checkAccessEdit: function (record,allow_msg)
	{
		if(!record)
			return false;
		
		var flag = (isSuperAdmin() || 'edit' == record.get('accessType'));
		if(flag == false && allow_msg)
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['vyi_ne_mojete_redaktirovat_vyibrannuyu_zapis'],
				title: this.title
			});
		return flag;
	},
	onSelectButtonClick: function ()
	{
        if (getRegionNick() == 'astra'){ //http://redmine.swan.perm.ru/issues/32624
            if(IS_DEBUG)
                $wiki_path = 'http://192.168.36.64/wiki/main/wiki/';
            else
                $wiki_path = 'https://astrahan.promedweb.ru/wiki/main/wiki/';
        }
        else
            $wiki_path = '/wiki/index/';

		var record = this.getSelectedRecord(true);
		if (false == this.isAllowSelectTpl(record)) {
			return false;
		}
        if ( !record.get('XmlTemplateType_id').toString().inlist(['6','7','9']) ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                icon: Ext.Msg.WARNING,
                msg: lang['vami_vyibran_shablon_starogo_formata'] +
                    lang['neobhodimo_konvertirovat_vyibrannyiy_shablon_v_novyiy_format'] +
                    lang['otkroyte_shablon_na_redaktirovanie_i_sohranite_zatem_ego_mojno_budet_vyibrat'] +
					'<br><a href="'+$wiki_path+lang['shablonyi_dokumentov#instruktsiya_po_konvertatsii_shablonov_v_novyiy_format_target=_blank_>instruktsiya_po_konvertatsii'],
                title: this.title
            });
            return false;
        }
		this.onSelect(record.data);
		this.hide();
	},
	openXmlTemplateCatEditWindow: function (id)
	{
		var form = this.filterPanel.getForm();
		var evnclass_id = form.findField('EvnClass_id').getValue();
		var xmltype_id = form.findField('XmlType_id').getValue();
		var record = this.getSelectedRecord(false);
		if(record) {
			if(!evnclass_id)
				evnclass_id = record.get('EvnClass_id');
			if(!xmltype_id)
				xmltype_id = record.get('XmlType_id');
		}
        if (id && record && 'view' == record.get('accessType')) {
            return false;
        }
        var xmltemplatescope_eid = 5;
        var xmltemplatescope_id = 4;
        if (isLpuAdmin()) {
            xmltemplatescope_eid = 5;
            xmltemplatescope_id = 3;
        }
        if (isSuperAdmin()) {
            xmltemplatescope_eid = 1;
            xmltemplatescope_id = 2;
        }
		getWnd('swXmlTemplateCatEditWindow').show({
			formParams: {
				XmlTemplateCat_id: id || null,
				XmlTemplateCat_pid: form.findField('XmlTemplateCat_id').getValue(),
				LpuSection_id: form.findField('LpuSection_id').getValue(),
				EvnClass_id: evnclass_id,
				XmlType_id: xmltype_id,
				XmlTemplateScope_eid: xmltemplatescope_eid,
				XmlTemplateScope_id: xmltemplatescope_id,
                MedStaffFact_id: form.findField('MedStaffFact_id').getValue(),
                MedPersonal_id: form.findField('MedPersonal_id').getValue(),
                MedService_id: form.findField('MedService_id').getValue()
			},
			callback: function(data) {
				if ( !data ) {
                    return false;
				}
                this.doLoadData(false, 'XmlTemplateCat_'+ data.XmlTemplateCat_id);
                return true;
			}.createDelegate(this)
		});
        return true;
    },
    getParamsSetDefault: function (record)
    {
        if (sw.Promed.XmlTemplateCatDefault.isDisableDefaults(sw.Promed.MedStaffFactByUser.last)) {
            return false;
        }
        var form = this.filterPanel.getForm();
        var params = {
            XmlType_id: record.get('XmlType_id') || form.findField('XmlType_id').getValue(),
            EvnClass_id: record.get('EvnClass_id') || form.findField('EvnClass_id').getValue(),
            UslugaComplex_id: record.get('UslugaComplex_id') || form.findField('UslugaComplex_id').getValue(),
            XmlTypeKind_id: record.get('XmlTypeKind_id') || form.findField('XmlTypeKind_id').getValue(),
            MedStaffFact_id: form.findField('MedStaffFact_id').getValue() || sw.Promed.MedStaffFactByUser.last.MedStaffFact_id,
            MedPersonal_id: form.findField('MedPersonal_id').getValue() || sw.Promed.MedStaffFactByUser.last.MedPersonal_id,
            MedService_id: form.findField('MedService_id').getValue() || sw.Promed.MedStaffFactByUser.last.MedService_id,
            LpuSection_id: form.findField('LpuSection_id').getValue() || sw.Promed.MedStaffFactByUser.last.LpuSection_id
        };

		var isOk = false;
		if (record.get('XmlTemplate_id')) {
			params.XmlTemplate_id = record.get('XmlTemplate_id');
			isOk = sw.Promed.XmlTemplateCatDefault.checkParamsSetXmlTemplateDefault(params);
		}
		if (record.get('XmlTemplateCat_id')) {
			params.XmlTemplateCat_id = record.get('XmlTemplateCat_id');
			isOk = sw.Promed.XmlTemplateCatDefault.checkParamsSetXmlTemplateCatDefault(params);
		}
		
		if (false == isOk) {
			return false;
		}
		if (5 == record.get('XmlTemplateScope_id') && record.get('pmUser_insID') != getGlobalOptions().pmuser_id) {
			return false;
		}
		if (4 == record.get('XmlTemplateScope_id') && (record.get('LpuSection_id') != params.LpuSection_id || record.get('Lpu_id') != getGlobalOptions().lpu_id)) {
			return false;
		}
		if (3 == record.get('XmlTemplateScope_id') && record.get('Lpu_id') != getGlobalOptions().lpu_id) {
			return false;
		}
		return params;
	},
	setDefault: function ()
	{
		var win = this,
			record = this.getSelectedRecord(true),
			params = this.getParamsSetDefault(record);
		if (!params) {
			return false;
		}
		if (record.get('XmlTemplate_id')) {
			params.XmlTemplate_id = record.get('XmlTemplate_id');

			// шлем запрос на сохранение шаблона как по умолчанию
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite_idet_sohranenie_shablona_kak_po_umolchaniyu']});
			loadMask.show();
			Ext.Ajax.request(
			{
				url: '/?c=XmlTemplateDefault&m=save',
				callback: function(options, success, response) 
				{
					loadMask.hide();
					if ( success )
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if ( result && result['success'] && result['success'] === true )
						{
							win.doLoadData(false, record.id); // TODO: Пока просто перечитывание списка, а вообще надо сделать чтобы пробегал по записям и проставлял выделенность (и убирал поставленную) без перезагрузки
							//sw.swMsg.alert('Успех', 'Шаблон был успешно сохранен как ваш шаблон по умолчанию', function() {});
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
					this.doChangeXmlTemplateCat({accessType: record.get('accessType'), XmlTemplateCat_id: record.get('XmlTemplateCat_id'), XmlTemplateCat_Name: record.get('Item_Name'), levelUp: true});
				} else {
					sw.swMsg.alert(lang['oshibka'], res);
				}
			}, this);
			return true;
		}
		return false;
    },
	deleteTemplate: function ()
	{
		var win = this;
		var record = this.getSelectedRecord(true);
		if (false==this.checkAccessEdit(record,true))
			return false;
		var params, url;
		if (record.get('XmlTemplate_id')) {
			url = '/?c=XmlTemplate&m=destroy';
			params = {XmlTemplate_id: record.get('XmlTemplate_id'), LpuSection_id: win.filterPanel.getForm().findField('LpuSection_id').getValue()};
		} else if(record.get('XmlTemplateCat_id')){
			url = '/?c=XmlTemplateCat&m=destroy';
			params = {XmlTemplateCat_id: record.get('XmlTemplateCat_id'), LpuSection_id: win.filterPanel.getForm().findField('LpuSection_id').getValue()};
		} else {
			return false;
		}
		
		var deleteRequest = function(url, params) {
			Ext.Ajax.request(
			{
				url: url,
				callback: function(options, success, response) 
				{
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.Alert_Msg && response_obj.except_list && response_obj.Error_Msg == 'YesNo') {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										params.except_list = response_obj.except_list;
										deleteRequest(url, params);
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: response_obj.Alert_Msg,
								title: lang['prodoljit_udalenie']
							});
							return true;
						}
						if (response_obj && response_obj.Error_Msg) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								msg: response_obj.Error_Msg,
								title: win.title
							});
							return true;
						}
						if (response_obj.success) {
							win.viewFrame.runAction('action_refresh');
							return true;
						}
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING,
							msg: lang['neizvestnaya_oshibka_pri_udalenii'],
							title: win.title
						});
					}
				},
				params: params
			});
		};

		sw.swMsg.show(
		{
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit'],
			title: lang['vopros'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId) {
					deleteRequest(url, params);
				}
			}
		});
	},
	showXmlTemplateEditWindow: function (action)
	{
		var record = this.getSelectedRecord('add' != action);
        // редактируем папку
        if ( 'edit' == action && record && !record.get('XmlTemplate_id') && record.get('XmlTemplateCat_id')) {
            this.openXmlTemplateCatEditWindow(record.get('XmlTemplateCat_id'));
            return true;
        }

		var form = this.filterPanel.getForm();
		var template_id = null;
		var evnclass_id = form.findField('EvnClass_id').getValue();
		var xmltype_id = form.findField('XmlType_id').getValue();
		var xmltypekind_id = form.findField('XmlTypeKind_id').getValue();

        // редактируем или добавляем шаблон
        if ('add' == action) {
            if (!evnclass_id) {
                sw.swMsg.alert(lang['soobschenie'], lang['neobhodimo_vyibrat_kategoriyu_dokumenta_pered_sozdaniem_shablona'], function() {
                    form.findField('EvnClass_id').focus(true, 250);
                });
                return false;
            }
            if (xmltype_id == 10 && !xmltypekind_id) { 
                sw.swMsg.alert(lang['soobschenie'], 'Необходимо выбрать вид документа перед созданием шаблона', function() {
                    form.findField('XmlTypeKind_id').focus(true, 250);
                });
                return false;
            }
            /*if (!xmltype_id) {
                sw.swMsg.alert(lang['soobschenie'], lang['neobhodimo_vyibrat_tip_dokumenta_pered_sozdaniem_shablona'], function() {
                    form.findField('XmlType_id').focus(true, 250);
                });
                return false;
            }*/
            if (false && record && record.get('XmlTemplate_id')) {
                action = 'copy';
                template_id = record.get('XmlTemplate_id');
                evnclass_id = record.get('EvnClass_id');
                xmltype_id = record.get('XmlType_id');
            }
        } else {
            action = record.get('accessType');
            template_id = record.get('XmlTemplate_id');
            evnclass_id = record.get('EvnClass_id');
            xmltype_id = record.get('XmlType_id');
            if (isSuperAdmin()) {
                action = 'edit';
            }
            if (action == 'edit' && (!evnclass_id /*|| !xmltype_id*/)) {//и тип
                sw.swMsg.alert(lang['soobschenie'], lang['neobhodimo_vvesti_kategoriyu_dokumenta_v_forme_redaktirovaniya_svoystv_shablona_pered_redaktirovaniem_shablona']);
                return false;
            }
        }

		getWnd('swXmlTemplateEditWindow').show({
			action: action,
            formParams: {
                XmlTemplate_id: template_id,
                EvnClass_id: evnclass_id,
                XmlTemplateCat_id: form.findField('XmlTemplateCat_id').getValue(),
                UslugaComplex_id: form.findField('UslugaComplex_id').getValue(),
                XmlTypeKind_id: form.findField('XmlTypeKind_id').getValue(),
                XmlType_id: xmltype_id,
                XmlTemplateType_id: 6
            },
            LpuSection_id: form.findField('LpuSection_id').getValue(),
			disabledChangeEvnClass: (evnclass_id && form.findField('EvnClass_id').disabled),
            disabledChangeXmlType: (xmltype_id && form.findField('XmlType_id').disabled),
			callback: function(data) {
				if ( !data ) {
					return false;
				}
                this.previewXmlTemplateId = null;
				action == 'add'?this.doLoadData(false, 'XmlTemplate_'+ data.XmlTemplate_id):this.viewFrame.onRowSelect(this.viewFrame.getGrid().getSelectionModel(),this.viewFrame.getGrid().getStore().indexOf(record),record);
                return true;
			}.createDelegate(this)
		});
        return true;
	},
	showXmlTemplateSettingsEditWindow: function ()
	{
		var record = this.getSelectedRecord(true);
		if(false==this.checkAccessEdit(record,true))
			return false;
        var form = this.filterPanel.getForm();
        // var flag = (isSuperAdmin() || 'edit' == record.get('accessType'));
		getWnd('swXmlTemplateSettingsEditWindow').show({
			XmlTemplate_id: record.get('XmlTemplate_id'),
            disabledChangeEvnClass: (form.findField('EvnClass_id').disabled),
            disabledChangeXmlType: (form.findField('XmlType_id').disabled),
			action: 'edit',
			callback: function() {
                this.previewXmlTemplateId = null;
				this.viewFrame.onRowSelect(this.viewFrame.getGrid().getSelectionModel(),this.viewFrame.getGrid().getStore().indexOf(record),record);
                //this.doLoadData(false, record.id);
			}.createDelegate(this)
		});
        return true;
	},
	isAllowSelectTpl: function(rec) 
	{
		return (this.mode == 'select'
			&& typeof this.onSelect == 'function'
			&& rec
			&& rec.get('XmlTemplate_id') > 0
		);
	},

	show: function() 
	{
		sw.Promed.swTemplSearchWindow.superclass.show.apply(this, arguments);
		this.center();
		if ( !arguments[0] )
		{
			arguments=[{}];
		}
		
		if (typeof arguments[0].onSelect == 'function') {
			this.mode = 'select';
			this.onSelect = arguments[0].onSelect;
		} else {
			this.mode = 'view';
			this.onSelect = null;
		}
		this.buttons[0].setDisabled(true);
        this.allowSelectXmlType = arguments[0].allowSelectXmlType || false;
		var xmltype_id = arguments[0].XmlType_id || null;
        this.Evn_id = arguments[0].Evn_id || null;
        // при клике на "Выбор шаблона" в меню документа надо обязательно передавать EvnXml_id
        this.EvnXml_id = arguments[0].EvnXml_id || null;
        this.ARMType = arguments[0].ARMType || sw.Promed.MedStaffFactByUser.current.ARMType;
        this.isEMK = arguments[0].isEMK || null;
        this.itemSectionCode = arguments[0].itemSectionCode || null;
        this.isEmpty = arguments[0].isEmpty || false;
        var evnclass_id = arguments[0].EvnClass_id || null;
        var uslugacomplex_id = arguments[0].UslugaComplex_id || null;
        var xmltypekind_id = arguments[0].XmlTypeKind_id || null;
        var lpusection_id = arguments[0].LpuSection_id || (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.LpuSection_id) || null;
        var medservice_id = arguments[0].MedService_id || (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedService_id) || null;
        var medpersonal_id = arguments[0].MedPersonal_id || (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedPersonal_id) || null;
        var medstafffact_id = arguments[0].MedStaffFact_id || (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedStaffFact_id) || null;

        if(getRegionNick().inlist(['vologda','msk','ufa']) && this.isEMK && evnclass_id == 32 && this.itemSectionCode){
            if(this.ARMType && this.ARMType.inlist(['stacpriem','stac']) && this.itemSectionCode == 'EvnXmlOther') this.allowSelectXmlType = true;
        }

        this.getInitXmlTypeId = function(){
            return xmltype_id;
        };
        this.getInitEvnClassId = function(){
            return evnclass_id;
        };
        this.getInitUslugaComplexId = function(){
            return uslugacomplex_id;
        };
        this.getInitXmlTypeKindId = function(){
            return xmltypekind_id;
        };
        this.getInitLpuSectionId = function(){
            return lpusection_id;
        };
        this.getInitMedServiceId = function(){
            return medservice_id;
        };
        this.getInitMedPersonalId = function(){
            return medpersonal_id;
        };
        this.getInitMedStaffFactId = function(){
            return medstafffact_id;
        };
		this.doReset();
		
		var form = this.filterPanel.getForm(),
			grid = this.viewFrame.getGrid(),
            usluga_complex_combo = form.findField('UslugaComplex_id'),
            evnclass_combo = form.findField('EvnClass_id');

        if (this.getInitEvnClassId()) {
            evnclass_combo.disable();
        } else {
            evnclass_combo.enable();
        }

        usluga_complex_combo.getStore().removeAll();
        if ( usluga_complex_combo.getValue() ) {
            usluga_complex_combo.getStore().load({
                params: {UslugaComplex_id: usluga_complex_combo.getValue()},
                callback: function() {
                    this.setValue(this.getValue());
                }.createDelegate(usluga_complex_combo)
            });
        }

		if(evnclass_combo.getStore().getCount()==0) {
			evnclass_combo.getStore().load({
				callback: function(r,o,s){
					evnclass_combo.setValue(evnclass_id);
                    evnclass_combo.fireEvent('select', evnclass_combo);
					this.doLoadData(true);
				}.createDelegate(this)
			});
		} else {
			evnclass_combo.setValue(evnclass_id);
            evnclass_combo.fireEvent('select', evnclass_combo);
			this.doLoadData(true);
		}
		//#182701
		if (evnclass_id == 14) {
			evnclass_combo.enable();
			this.excludedEvnClasses = ['13', '22', '27', '29', '30', '32', '43', '47', '120', '160'];
		}

		this.syncSize();
		this.doLayout();
	}
});
