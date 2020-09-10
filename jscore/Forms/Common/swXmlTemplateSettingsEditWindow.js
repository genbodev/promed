/**
* swXmlTemplateSettingsEditWindow - Форма редактирования свойств шаблона
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Aleksandr Permyakov (alexpm)
* @version      19.02.2012
* @comment      tabIndex: TABINDEX_XTSEW + (от 21 до 50)
*/

/*NO PARSE JSON*/
sw.Promed.swXmlTemplateSettingsEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swXmlTemplateSettingsEditWindow',
	objectSrc: '/jscore/Forms/Common/swXmlTemplateSettingsEditWindow.js',

	buttonAlign: 'left',
	layout: 'form',
	listeners: {
		'hide': function(p) {
			if (p.action == 'add' && !p.isSaved && !p.withoutSave) {
				p.deleteSavedTemplate();
			}
			this.onHide();
		}
	},
	title: lang['svoystva_shablona_redaktirovanie'],
	id: 'swXmlTemplateSettingsEditWindow',
	width: 700,
    autoHeight: true,
    closable: true,
    closeAction: 'hide',
    collapsible: false,
	resizable: false,
    draggable: true,
	isSaved: false,
	
	deleteSavedTemplate: function() {
		var form = this.formPanel.getForm(),
			XmlTemplate_id = form.findField('XmlTemplate_id').getValue(),
			LpuSection_id = form.findField('LpuSection_id').getValue();
		if (!XmlTemplate_id) return false;
		Ext.Ajax.request({
			url: '/?c=XmlTemplate&m=destroy',
			params: {XmlTemplate_id: XmlTemplate_id, LpuSection_id: LpuSection_id},
		});
	},
	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
    },
    beforeSubmit: function() {
        var form = this.formPanel.getForm(),
            evnclass_combo = form.findField('EvnClass_id'),
            xml_type_combo = form.findField('XmlType_id'),
            usluga_complex_list = [],
            usluga_complex_grid = this.UslugaComplexFrame.getGrid(),
            usluga_complex_combo = form.findField('UslugaComplex_id'),
            params = {};
        if ( !form.isValid() ) {
            sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
            return false;
        }
        if ( evnclass_combo.disabled ) {
            params.EvnClass_id = evnclass_combo.getValue();
        }
        if ( xml_type_combo.disabled ) {
            params.XmlType_id = xml_type_combo.getValue();
        }
        params.LpuSection_id = getGlobalOptions().CurLpuSection_id || null;
        switch (true) {
            case (xml_type_combo.getValue() == sw.Promed.EvnXml.LAB_USLUGA_PROTOCOL_TYPE_ID):
                if ( usluga_complex_combo.getValue() ) {
                    usluga_complex_list.push(usluga_complex_combo.getValue());
                }
                break;
            case (xml_type_combo.getValue() == sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID):
                usluga_complex_grid.getStore().each(function(rec) {
                    if (rec.get('UslugaComplex_id')) {
                        usluga_complex_list.push(rec.get('UslugaComplex_id'));
                    }
                    return true;
                });
                break;
            default:
                usluga_complex_list = [];
                break;
        }
        form.findField('UslugaComplex_id_list').setValue(usluga_complex_list.join(','));
        return params;
    },
    deleteUslugaComplex: function() {
        var thas = this;
        var usluga_complex_grid = thas.UslugaComplexFrame.getGrid();
        var rec = usluga_complex_grid.getSelectionModel().getSelected();
        if (rec) {
            usluga_complex_grid.getStore().remove(rec);
        }
    },
    showSelectUslugaComplexWindow: function() {
        var thas = this;
        var win = getWnd('swSelectUslugaComplexWindow');
        win.show({
            mode: 'all',
            baseParams: {
                uslugaCategoryList: Ext.util.JSON.encode(this.getUslugaCategoryList())
            },
            onSelect: function(record) {
                var usluga_complex_grid = thas.UslugaComplexFrame.getGrid();
                var data = {
                    UslugaComplex_id: record.get('UslugaComplex_id'),
                    UslugaComplex_Code: record.get('UslugaComplex_Code'),
                    UslugaComplex_Name: record.get('UslugaComplex_Name'),
                    UslugaCategory_Name: record.get('UslugaCategory_Name')
                };
                var newRecord = new Ext.data.Record(data);
                usluga_complex_grid.getStore().add([newRecord]);
                usluga_complex_grid.getStore().commitChanges();
                var i = usluga_complex_grid.getStore().indexOf(newRecord);
                usluga_complex_grid.getSelectionModel().selectRow(i);
                win.hide();
                //usluga_complex_grid.getView().focusRow(i);
                //thas.UslugaComplexFrame.onRowSelect(usluga_complex_grid.getSelectionModel(), i, newRecord);
            },
            onHide: function() {
                var usluga_complex_grid = thas.UslugaComplexFrame.getGrid();
                var rec = usluga_complex_grid.getSelectionModel().getSelected();
                if (!rec) {
                    rec = usluga_complex_grid.getStore().getAt(0);
                }
                var i = usluga_complex_grid.getStore().indexOf(rec);
                if (rec) {
                    usluga_complex_grid.getSelectionModel().selectRow(i);
                    usluga_complex_grid.getView().focusRow(i);
                    thas.UslugaComplexFrame.onRowSelect(usluga_complex_grid.getSelectionModel(), i, rec);
                }
            }
        });
        return true;
    },
    getUslugaCategoryList: function() {
        var uslugacategorylist = ['gost2011','syslabprofile','lpu','lpulabprofile'];
        //региональные категории
		switch ( getRegionNick() ) {
			case 'kz': //только 13. Классификатор мед.услуг
				uslugacategorylist = [ 'classmedus' ];
				break;

			case 'perm': //услуги пермского ТФОМС
				uslugacategorylist.push('tfoms');
				break;

			case 'pskov': //услуги Псковского ТФОМС
				uslugacategorylist.push('pskov_foms');
				break;
		}
        return uslugacategorylist;
    },
    loadUslugaComplexCmp: function() {
        var form = this.formPanel.getForm(),
            xml_type_combo = form.findField('XmlType_id'),
            usluga_complex_list_field = form.findField('UslugaComplex_id_list'),
            usluga_complex_list = [],
            usluga_complex_combo = form.findField('UslugaComplex_id');

        if (usluga_complex_list_field && usluga_complex_list_field.getValue()) {
            usluga_complex_list = usluga_complex_list_field.getValue().toString().split(',');
        }

        this.UslugaComplexFrame.removeAll({addEmptyRecord: false, clearAll: true});
        switch (true) {
            case (xml_type_combo.getValue() == sw.Promed.EvnXml.LAB_USLUGA_PROTOCOL_TYPE_ID):
                this.UslugaComplexFrame.setVisible(false);
                usluga_complex_combo.setContainerVisible(true);
                usluga_complex_combo.setAllowBlank(true);
                usluga_complex_combo.getStore().removeAll();
                usluga_complex_combo.setUslugaCategoryList(this.getUslugaCategoryList());
                if ( usluga_complex_list.length > 0 ) {
                    usluga_complex_combo.getStore().load({
                        params: {UslugaComplex_id: usluga_complex_list[0]},
                        callback: function() {
                            usluga_complex_combo.setValue(usluga_complex_list[0]);
                        }
                    });
                }
                break;
            case (xml_type_combo.getValue() == sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID):
                this.UslugaComplexFrame.setVisible(true);
                var params = {
                    XmlTemplate_id: form.findField('XmlTemplate_id').getValue()
                };
                this.UslugaComplexFrame.loadData({
                    params: params,
                    globalFilters: params
                });
                usluga_complex_combo.setContainerVisible(false);
                usluga_complex_combo.setAllowBlank(true);
                usluga_complex_combo.clearValue();
                break;
            default:
                this.UslugaComplexFrame.setVisible(false);
                usluga_complex_combo.setContainerVisible(false);
                usluga_complex_combo.setAllowBlank(true);
                usluga_complex_combo.clearValue();
                break;
        }
        this.formPanel.doLayout();
        this.formPanel.syncSize();
        this.doLayout();
        this.syncSize();
    },
    loadXmlTemplateCatCombo: function(params) {
		var me = this,
			form = this.formPanel.getForm(),
			combo = form.findField('XmlTemplateCat_id');
		var XmlTemplateCat_pid = combo.getValue();
		if (sw.Promed.XmlTemplateCatDefault.isAllowRootFolder()) {
			combo.setAllowBlank(true);
			combo.emptyText = lang['kornevaya_papka'];
		} else {
			combo.setAllowBlank(false);
			combo.emptyText = null;
		}
		if (!params) {
            params = {};
        }
        combo.getStore().baseParams = {
            XmlTemplateCat_id: null,
            LpuSection_id: getGlobalOptions().CurLpuSection_id || null
        };
        //params.XmlTemplateCat_pid = combo.getValue();

        combo.lastQuery = '';
		combo.getStore().removeAll();
		combo.getStore().load({
			params: params,
			callback: function(r,o,s){
                if (combo.getStore().getCount() == 0) {
                    // нет ни одной папки доступной для редактирования
                    sw.Promed.XmlTemplateCatDefault.create({
                        MedStaffFact_id: sw.Promed.MedStaffFactByUser.last.MedStaffFact_id || null,
                        EvnClass_id: form.findField('EvnClass_id').getValue(),
                        XmlType_id: form.findField('XmlType_id').getValue()
                    }, function(success, result, records){
                        if (success) {
                            combo.setValue(result['XmlTemplateCat_id']);
                            me.loadXmlTemplateCatCombo();
                        } else {
                            sw.swMsg.alert(lang['oshibka'], result['Error_Msg']);
                        }
                    }, me);
                    return true;
                }
				var index = combo.getStore().findBy(function(rec) {
                    return ( rec.get('XmlTemplateCat_id') == combo.getValue() );
				});
                if ( index >= 0 ) {
                    combo.setValue(XmlTemplateCat_pid);
                } else {
                    combo.clearValue();
                }
                return true;
			}
		});
    },
    submit: function() {
		var win = this,
			form = this.formPanel.getForm(),
			params = this.beforeSubmit();
        if ( !params ) {
            return false;
        }
		if ( win.withoutSave ) {
			params.callback = function(){ win.hide(); };
			win.callback(Ext.apply(form.getValues(), params));
			return true;
		}
		win.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
                var result = Ext.util.JSON.decode(action.response.responseText);
                if ( result && result['XmlTemplate_id'] && result['success'] && result['success'] === true )
                {
					win.isSaved = true;
                    win.hide();
                    params.XmlTemplate_id = result['XmlTemplate_id'];
                    win.callback(params);
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                }
			}
		});
        return true;
	},

	initComponent: function() {
		var win = this;
		
		this.PaperFormatStore = new Ext.data.SimpleStore({
			key: 'PaperFormat_id',
			fields:
			[
				{name: 'PaperFormat_id', type: 'int'},
				{name: 'PaperFormat_Name', type: 'string'}
			],
			data: [
				[1,'A4'], 
				[2,'A5']
			]
		});

		this.PaperOrientStore = new Ext.data.SimpleStore({
			key: 'PaperOrient_id',
			fields:
			[
				{name: 'PaperOrient_id', type: 'int'},
				{name: 'PaperOrient_Name', type: 'string'}
			],
			data: [
				[1,lang['albomnaya']], 
				[2,lang['knijnaya']]
			]
		});

		this.FontSizeStore = new Ext.data.SimpleStore({
			key: 'FontSize_id',
			fields:
			[
				{name: 'FontSize_id', type: 'int'},
				{name: 'FontSize_Name', type: 'string'}
			],
			data: [
				[6,'6'], 
				[8,'8'], 
				[10,'10'], 
				[12,'12'], 
				[14,'14']
			]
		});

		sw.Promed.marginField = Ext.extend(Ext.form.NumberField, {
			allowBlank: false,
			allowDecimals: false,
			allowNegative: false,
			hideLabel: true, 
			maxValue: 50,
			minValue: 5,
			value: 10,
			width: 30,
			initComponent: function() {
				sw.Promed.marginField.superclass.initComponent.apply(this, arguments);
			},
			onRender:function() {
				sw.Promed.marginField.superclass.onRender.apply(this, arguments);
			}
		});
		Ext.reg('marginfield', sw.Promed.marginField);

        this.UslugaComplexFrame = new sw.Promed.ViewFrame({
            title: lang['uslugi'],
            actions: [
                {name: 'action_add', handler: function() {
                    win.showSelectUslugaComplexWindow();
                }},
                {name: 'action_edit', hidden: true, disabled: true},
                {name: 'action_view', hidden: true, disabled: true},
                {name: 'action_delete', handler: function() {
                    win.deleteUslugaComplex();
                }},
                {name: 'action_refresh', hidden: true, disabled: true},
                {name: 'action_print', hidden: true, disabled: true}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=XmlTemplate&m=loadXmlTemplateLinkList',
            collapsible: false,
            id: 'XmlTemplateLinkList',
            paging: false,
            stringfields: [
                {name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true},
                {name: 'UslugaComplex_Code', type: 'string', header: lang['kod'], width: 100},
                {name: 'UslugaComplex_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand'},
                {name: 'UslugaCategory_Name', type: 'string', header: lang['kategoriya'], width: 150}
            ],
            toolbar: true,
            onLoadData: function(flag) {
                if (flag) {
                    this.ViewGridPanel.getView().focusRow(0);
                    if (this.selectionModel!='cell') {
                        this.ViewGridPanel.getSelectionModel().selectFirstRow();
                        this.onRowSelect(this.ViewGridPanel.getSelectionModel(),0,this.ViewGridPanel.getSelectionModel().getSelected());
                        this.ViewGridModel.clearSelections();
                    }
                } else {
                    this.removeAll({addEmptyRecord: false, clearAll: true});
                }
            },
            onRowSelect: function(sm, i, rec) {
                this.setActionDisabled('action_delete', false);
            }
        });

        this.accessRightsPanel = new sw.Promed.XmlTemplateScopePanel({
            object: 'XmlTemplate',
            tabIndexStart: TABINDEX_XTSEW+25
        });

		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: false,
            border: false,
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0',
			id: 'XmlTemplateSettingsEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			items: [{
                name: 'XmlTemplate_id',
                xtype: 'hidden'
            }, {
                name: 'UslugaComplex_id_list',
                xtype: 'hidden'
            }, {
				fieldLabel: lang['kategoriya'],
				allowBlank: false,
				width: 280,
				tabIndex: TABINDEX_XTSEW+21,
				//disabled: !isSuperAdmin(),
				autoLoad: false,
				xtype: 'swevnclasscombo',
				listeners: {
					change: function(field, newValue) {
                        var bf = win.formPanel.getForm();
                        var type_combo = bf.findField('XmlType_id'),
                            not_view_id_list = sw.Promed.EvnXml.getNotViewXmlTypeIdList(newValue);
                        /* не всегда работает
                        type_combo.getStore().clearFilter();
                        type_combo.getStore().filterBy(function(rec) {
                            return (false == rec.get('XmlType_id').toString().inlist(not_view_id_list));
                        });
                        */
                        type_combo.getStore().removeAll();
                        type_combo.getStore().load({
                            callback: function() {
                                type_combo.getStore().each(function(rec) {
                                    if (rec.get('XmlType_id').toString().inlist(not_view_id_list)) {
                                        type_combo.getStore().remove(rec);
                                        type_combo.setDisabled(false);
                                    }
                                });
                                type_combo.setValue(type_combo.getValue());
                                type_combo.fireEvent('change', type_combo, type_combo.getValue(), null);
                            }
                        });
					}
				}
			},{
				fieldLabel: lang['tip_dokumenta'],
				allowBlank: false,
				width: 263,
				tabIndex: TABINDEX_XTSEW+22,
				comboSubject: 'XmlType',
                typeCode: 'int',
                hiddenName: 'XmlType_id',
				autoLoad: false,
				xtype: 'swcommonsprcombo',
				listeners: {
					change: function(combo, newValue, oldValue) {
						var bf = win.formPanel.getForm(),
							xml_type_combo = bf.findField('XmlTypeKind_id');
						var index = combo.getStore().findBy(function(rec) {
							return ( rec && rec.get('XmlType_id') == newValue );
						});
						if ( index < 0 ) {
							combo.setValue(null);
						} else {
							combo.setValue(newValue);
						}
                        win.loadUslugaComplexCmp();
						if (10 == newValue) { // Типы эпикриза
							xml_type_combo.getStore().filterBy(function(rec) {
								return (newValue == rec.get('XmlType_id'));
							});
							xml_type_combo.showContainer();
							xml_type_combo.setAllowBlank(false);
						} else {
							xml_type_combo.hideContainer();
							xml_type_combo.setAllowBlank(true);
						}
						win.doLayout();
						win.syncSize();
					},
					'render': function(combo) {
                        /*
						combo.getStore().addListener('load',function(store, records, options) {
							if(!isSuperAdmin()) {
								for(var i=0; i<records.length; i++) {
									if(records[i].get('XmlType_id') == 1) {
										store.remove(records[i]);
									}
								}
							}
						});
                        */
					}
				}
			},{
				fieldLabel: 'Вид документа',
				hiddenName: 'XmlTypeKind_id',
				moreFields: [{name: 'XmlType_id', mapping: 'XmlType_id'}],
				tabIndex: TABINDEX_XTSEW+22.5,
				width: 263,
				xtype: 'swcommonsprcombo',
				typeCode: 'int',
				comboSubject: 'XmlTypeKind',
				listeners: {
					select: function(combo, rec, i) {
					   // win.doLoadData();
					}
				}
			},{
                //ид услуги, для протокола которой создается шаблон
                hiddenName: 'UslugaComplex_id',
                listWidth: 500,
                width: 330,
                xtype: 'swuslugacomplexnewcombo'
            },{
				width: 263,
				tabIndex: TABINDEX_XTSEW+23,
				xtype: 'swxmltemplatecatcombo',
                editable: true,
                mode: 'remote'
			},{
				width: 250,
				allowBlank: false,
				id: 'XTSEW_XmlTemplate_Caption',
				fieldLabel: lang['naimenovanie'],
				name: 'XmlTemplate_Caption',
				value: lang['novyiy_shablon'],
				maxLength: 100,
				tabIndex: TABINDEX_XTSEW + 24,
				xtype: 'textfield'
			}, this.accessRightsPanel, {
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['nastroyki_pechati'],
				style: 'padding: 0; padding-left: 10px',
				items: [
				new Ext.form.Label({
					style: 'padding: 0; padding-left:350px;',
					html: lang['otstupyi_mm_pdf']
				}),
				{
					layout: 'column',
                    border: false,
					items: [{
						layout: 'form',
                        border: false,
						items: [{
							fieldLabel: lang['orientatsiya_pdf'],
							allowBlank: false,
							width: 100,
							tabIndex: TABINDEX_XTSEW+27,
							mode: 'local',
							value: 2,
							hiddenName: 'PaperOrient_id',
							editable: false,
							triggerAction: 'all',
							displayField: 'PaperOrient_Name',
							valueField: 'PaperOrient_id',
							tpl: '<tpl for="."><div class="x-combo-list-item">{PaperOrient_Name}</div></tpl>',
							store: this.PaperOrientStore,
							xtype: 'combo'
						},{
							fieldLabel: lang['razmer_bumagi_pdf'],
							allowBlank: false,
							width: 100,
							tabIndex: TABINDEX_XTSEW+29,
							mode: 'local',
							value: 1,
							hiddenName: 'PaperFormat_id',
							editable: false,
							triggerAction: 'all',
							displayField: 'PaperFormat_Name',
							valueField: 'PaperFormat_id',
							tpl: '<tpl for="."><div class="x-combo-list-item">{PaperFormat_Name}</div></tpl>',
							store: this.PaperFormatStore,
							xtype: 'combo'
						},{
							fieldLabel: lang['shrift'],
							allowBlank: false,
							width: 100,
							tabIndex: TABINDEX_XTSEW+31,
							mode: 'local',
							value: 10,
							hiddenName: 'FontSize_id',
							editable: false,
							triggerAction: 'all',
							displayField: 'FontSize_Name',
							valueField: 'FontSize_id',
							tpl: '<tpl for="."><div class="x-combo-list-item">{FontSize_Name}</div></tpl>',
							store: this.FontSizeStore,
							xtype: 'combo'
						}]
					},{
						layout: 'form',
						style: 'padding: 0; padding-left: 110px; padding-top: 27px;',
                        border: false,
						items: [{
							tabIndex: TABINDEX_XTSEW+33,
							name: 'margin_left',
							xtype: 'marginfield'
						}]
					},{
						layout: 'form',
						style: 'padding: 0;',
                        border: false,
						items: [{
							tabIndex: TABINDEX_XTSEW+34,
							name: 'margin_top',
							xtype: 'marginfield'
						}, new Ext.Panel({
							style: 'padding: 0; padding-bottom: 3px;',
                            border: false,
							height: 30,
							width: 30,
							html: '<div style="text-align: center;"><img height="24" width="24" src="/img/icons/document24.png" border="0" /></div>'
						}),{
							tabIndex: TABINDEX_XTSEW+36,
							name: 'margin_bottom',
							xtype: 'marginfield'
						}]
					},{
						layout: 'form',
						style: 'padding: 0; padding-right: 40px; padding-top: 27px;',
                        border: false,
						items: [{
							tabIndex: TABINDEX_XTSEW+35,
							name: 'margin_right',
							xtype: 'marginfield'
						}]
					}]
				}]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: 'Базовые настройки шрифта',
				style: 'padding: 0; padding-left: 10px',
				items: [
				{
					layout: 'column',
                    border: false,
					items: [{
						layout: 'form',
                        border: false,
						items: [{
							fieldLabel: 'Размер',
							allowBlank: true,
							width: 100,
							value: 10,
							minValue: 6,
							maxValue: 100,
							name: 'base_fontsize',
							allowDecimals: false,
							allowNegative: false,
							xtype: 'numberfield'
						}]
					},{
						layout: 'form',
                        border: false,
						items: [{
							fieldLabel: 'Шрифт',
							allowBlank: true,
							width: 200,
							value: 8,
							mode: 'local',
							editable: false,
							triggerAction: 'all',
							displayField: 'BaseFontFamily_Name',
							valueField: 'BaseFontFamily_id',
							hiddenName: 'base_fontfamily',
							store: new Ext.data.SimpleStore({
								key: 'BaseFontFamily_id',
								fields:
								[
									{name: 'BaseFontFamily_id', type: 'int'},
									{name: 'BaseFontFamily_Name', type: 'string'}
								],
								data: [
									[1,'Arial'],
									[2,'Comic Sans MS'],
									[3,'Courier New'],
									[4,'Georgia'],
									[5,'Lucida Sans Unicode'],
									[6,'Lucida Grande'],
									[7,'Tahoma'],
									[8,'Times New Roman'],
									[9,'Trebuchet MS'],
									[10,'Verdana']
								]
							}),
							xtype: 'combo'
						}]
					}]
				}]
			},
                this.UslugaComplexFrame
            ],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.submit();
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'XmlTemplate_id' },
				{ name: 'XmlTemplate_Caption' },
				{ name: 'EvnClass_id' },
                { name: 'UslugaComplex_id_list' },
                { name: 'XmlTemplateCat_id' },
				{ name: 'XmlType_id' },
				{ name: 'XmlTypeKind_id' },
				{ name: 'XmlTemplateScope_id' },
				{ name: 'XmlTemplateScope_eid' },
				{ name: 'LpuSection_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuSection_Name' },
				{ name: 'Lpu_Name' },
				{ name: 'PMUser_Name' },
				{ name: 'PaperFormat_id' },
				{ name: 'PaperOrient_id' },
				{ name: 'FontSize_id' },
				{ name: 'base_fontsize' },
				{ name: 'base_fontfamily' },
				{ name: 'margin_left' },
				{ name: 'margin_top' },
				{ name: 'margin_bottom' },
				{ name: 'margin_right' }
			]),
			timeout: 600,
			url: '/?c=XmlTemplate&m=saveSettings'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_XTSEW + 48,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			//HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				onTabElement: 'XTSEW_XmlTemplate_Caption',
				tabIndex: TABINDEX_XTSEW + 49,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swXmlTemplateSettingsEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swXmlTemplateSettingsEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.withoutSave = arguments[0].withoutSave || false;
		this.overrideData = arguments[0].overrideData || null;
		this.action = arguments[0].action || 'add';
		this.isSaved = false;

		this.doReset();
		this.center();

		var win = this,
			form = this.formPanel.getForm(),
            evnclass_combo = form.findField('EvnClass_id'),
			xmltype_combo = form.findField('XmlType_id');
        this.UslugaComplexFrame.setReadOnly(false);
		form.setValues(arguments[0]);
        evnclass_combo.setDisabled(arguments[0].disabledChangeEvnClass||false);
        xmltype_combo.setDisabled(arguments[0].disabledChangeXmlType||false);
		this.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
		this.formPanel.load({
			failure: function() {
				win.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
			},
			params: {
				XmlTemplate_id: form.findField('XmlTemplate_id').getValue()
			},
			success: function() {
				win.getLoadMask().hide();

				if(evnclass_combo.getStore().getCount()==0) {
					evnclass_combo.getStore().load({
						callback: function(r,o,s){
							evnclass_combo.setValue(evnclass_combo.getValue());
                            evnclass_combo.fireEvent('change', evnclass_combo, evnclass_combo.getValue(), null);
						}
					});
				} else {
					evnclass_combo.setValue(evnclass_combo.getValue());
                    evnclass_combo.fireEvent('change', evnclass_combo, evnclass_combo.getValue(), null);
				}
				if (win.overrideData) {
					form.findField('PMUser_Name').setValue(win.overrideData.PMUser_Name);
					form.findField('LpuSection_Name').setValue(win.overrideData.LpuSection_Name);
					form.findField('LpuSection_id').setValue(win.overrideData.LpuSection_id);
					form.findField('Lpu_Name').setValue(win.overrideData.Lpu_Name);
					form.findField('Lpu_id').setValue(win.overrideData.Lpu_id);
					form.findField('XmlTemplateScope_id').setValue(win.overrideData.XmlTemplateScope_id);
					form.findField('XmlTemplateScope_eid').setValue(win.overrideData.XmlTemplateScope_eid);
				} 

                win.accessRightsPanel.onLoadForm(form, win.action);
                win.loadXmlTemplateCatCombo();
			},
			url: '/?c=XmlTemplate&m=getSettings'
		});
	}
});
