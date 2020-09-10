/**
* swPrintTemplateSelectWindow - форма выбора шаблона для печати
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swPrintTemplateSelectWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2016 Swan Ltd.
* @comment      Префикс для id компонентов PTSW.
*/

/*NO PARSE JSON*/

sw.Promed.swPrintTemplateSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPrintTemplateSelectWindow',
	objectSrc: '/jscore/Forms/Common/swPrintTemplateSelectWindow.js',
	//maximizable: true,
	maximized: true,
	id: 'swPrintTemplateSelectWindow',
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
	title: 'Печать шаблона документа',
	onSelect: null,
	mode: null,
    selectRecId: null,
	excludedEvnClasses: null,
	onEnvClassStoreLoaded: function(evt_object) {

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
			labelAlign: 'right',
			labelWidth: 75,
			region: 'north',
			items:
            [{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['filtryi'],
				collapsible: true,
				layout: 'form',
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
                    layout:'column',
                    items:[{
                        layout: 'form',
                        width: 320,
                        items: [{
                            fieldLabel: lang['kategoriya'],
                            emptyText: lang['vyiberite_kategoriyu'],
                            id: 'PTSW_EvnClass_id',
                            hiddenName: 'EvnClass_id',
                            width: 250,
                            autoLoad: false,
							editable: false,
                            xtype: 'swevnclasscombo',
							listeners: {
								'render': function (combo) {
									combo.store.addListener('load', this.onEnvClassStoreLoaded, this);
								}.createDelegate(this),
								'select': function (combo) {
									var bf = win.filterPanel.getForm();
                                    var type_combo = bf.findField('XmlType_id'),
                                        not_view_id_list = sw.Promed.EvnXml.getNotViewXmlTypeIdList(combo.getValue());
                                    type_combo.getStore().clearFilter();
									type_combo.lastQuery = '';
                                    if (combo.getValue()) {
                                        if (0 == type_combo.getStore().getCount()) {
                                            type_combo.getStore().load({
                                                callback: function() {
                                                    type_combo.getStore().filterBy(function(rec) {
                                                        return (false == rec.get('XmlType_id').toString().inlist(not_view_id_list));
                                                    });
                                                }
                                            });
                                        } else {
                                            type_combo.getStore().filterBy(function(rec) {
                                                return (false == rec.get('XmlType_id').toString().inlist(not_view_id_list));
                                            });
                                        }
                                    }
                                    if (type_combo.getValue().inlist(not_view_id_list)) {
                                        type_combo.setValue(null);
                                    }
                                    type_combo.fireEvent('select', type_combo);
								}.createDelegate(this)
							}
                        }]
                    },{
                        layout: 'form',
                        width: 380,
						labelWidth: 100,
                        items: [{
                            fieldLabel: lang['tip_dokumenta'],
                            //hideLabel: true,
                            id: 'PTSW_XmlType_id',
                            hiddenName: 'XmlType_id',
                            width: 250,
							editable: false,
							comboSubject: 'XmlType',
                            xtype: 'swcommonsprcombo'
                        }]
                    },{
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
			}] // end items filterPanel
		});

		this.TemplateList = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=XmlTemplate&m=loadGridForPrint',
			object: 'XmlTemplate',
			editformclassname: 'swXmlTemplateEditWindow',
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', handler: function(){win.showXmlTemplateEditWindow('edit');}},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_print', hidden: true, disabled: true}
			],
			pageSize: 50,
			singleSelect:true,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{name: 'XmlTemplate_id', type: 'int', hidden: true, key: true},
				{name: 'XmlTemplate_Caption', header: lang['naimenovanie'], id: 'autoexpand', renderer: sw.Promed.Format.ItemNameColumn},
                {name: 'XmlType_Name', header: 'Тип документа', type: 'string', width: 250},
                {name: 'XmlTemplateCat_Name', header: 'Папка', type: 'string', width: 250},
                {name: 'pmUser_Name', header: lang['avtor'], type: 'string', width: 200},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'XmlType_id', type: 'int', hidden: true},
				{name: 'XmlTemplateCat_id', type: 'int', hidden: true},
				{name: 'accessType', type: 'string', hidden: true}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				this.ViewActions.action_edit.setDisabled(Ext.isEmpty(record.get('XmlTemplate_id')));
			},
			onDblClick: function(grid, number, object) {
				win.onPrintButtonClick();
			},
			onEnter: function() {
				win.onPrintButtonClick();
			}
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
						this.TemplateList
					]
				}
			],
			buttons: [{
				handler: function() {
					win.onPrintButtonClick();
				},
				iconCls: 'print16',
				text: lang['pechat']
			}, {
				text: '-'
			},
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				onTabElement: 'PTSW_EvnClass_id',
				handler: function() {
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
					}
				},
				key: [ Ext.EventObject.ESC ],
				scope: this,
				stopEvent: false
			}]
		});

		Ext.getCmp('PTSW_EvnClass_id').store.addListener('load',this.onEnvClassStoreLoaded, this);

		sw.Promed.swPrintTemplateSelectWindow.superclass.initComponent.apply(this, arguments);
	},
	getExcludedEvnClasses: function (){

		return this.excludedEvnClasses;
	},
	doReset: function() {
		var form = this.filterPanel.getForm(),
			grid = this.TemplateList.getGrid();
		form.reset();
		grid.getStore().baseParams = {};
		grid.getStore().removeAll();
		this.TemplateList.removeAll(true);
		this.TemplateList.setParam('limit', 50);
		this.TemplateList.setParam('start', 0);
        form.findField('EvnClass_id').setValue(this.getInitEvnClassId());
        form.findField('LpuSection_id').setValue(this.getInitLpuSectionId());
        form.findField('MedService_id').setValue(this.getInitMedServiceId());
        form.findField('MedPersonal_id').setValue(this.getInitMedPersonalId());
        form.findField('MedStaffFact_id').setValue(this.getInitMedStaffFactId());
	},
	doLoadData: function(is_show, rec_id)
	{
		var me = this,
            form = me.filterPanel.getForm(),
			grid = me.TemplateList.getGrid(),
			params = form.getValues();
		grid.getStore().baseParams = {};
		grid.getStore().removeAll();
        me.TemplateList.removeAll(true);
        me.selectRecId = rec_id || null;
		params.limit = 50;
        params.start = 0;
		if(form.findField('EvnClass_id').disabled)
			params.EvnClass_id = form.findField('EvnClass_id').getValue();
        this.TemplateList.loadData({
			globalFilters: params,
		});
	},
	getSelectedRecord: function(allow_msg) {
		return this.TemplateList.getGrid().getSelectionModel().getSelected();
	},
	showXmlTemplateEditWindow: function (action)
	{
		var record = this.getSelectedRecord();
		if (!record) return false;
		var form = this.filterPanel.getForm();
		var template_id = null;
		var evnclass_id = form.findField('EvnClass_id').getValue();
		var xmltype_id = null;
		var xmltypekind_id = null;
		var xmltemplatecat_id = null;

        // редактируем  шаблон
		action = record.get('accessType');
		template_id = record.get('XmlTemplate_id');
		evnclass_id = record.get('EvnClass_id');
		xmltype_id = record.get('XmlType_id');
		xmltemplatecat_id = record.get('XmlTemplateCat_id');

		getWnd('swXmlTemplateEditWindow').show({
			action: action,
            formParams: {
                XmlTemplate_id: template_id,
                EvnClass_id: evnclass_id,
                XmlTemplateCat_id: xmltemplatecat_id,
                XmlTypeKind_id: xmltype_id,
                XmlType_id: xmltype_id,
                XmlTemplateType_id: 6
            },
            LpuSection_id: form.findField('LpuSection_id').getValue(),
            disabledChangeEvnClass: (evnclass_id && form.findField('EvnClass_id').disabled),
            disabledChangeXmlType: true,
			callback: function(data) {
				if ( !data ) {
					return false;
				}
				this.doLoadData(false, 'XmlTemplate_'+ data.XmlTemplate_id);
                return true;
			}.createDelegate(this)
		});
        return true;
	},
	onPrintButtonClick: function ()
	{
		var record = this.getSelectedRecord();
		if (!record || !record.get('XmlTemplate_id')) return false;

		var xmltemplate_id = record.get('XmlTemplate_id'),
			evn_id = this.getEvnId(),
			person_id = this.getPersonId(),
			msg = '«Не удалось получить значение для следующих маркеров: %markers. Продолжить печатать документа без указанных маркеров?',
			url_print = '',
			url_check = '',
			exists_markers_empty = false;


		if (evn_id) {
			url_print = '/?c=EvnXml&m=doDirectPrint&XmlTemplate_id=' + xmltemplate_id + '&Evn_id=' + evn_id, '_blank';
			url_check = '/?c=EvnXml&m=checkIsMarkeryBezDannix&XmlTemplate_id=' + xmltemplate_id + '&Evn_id=' + evn_id, '_blank';

		} else if (person_id) {
			url_print = '/?c=EvnXml&m=doDirectPrint&XmlTemplate_id=' + xmltemplate_id + '&Person_id=' + person_id, '_blank';
			url_check = '/?c=EvnXml&m=checkIsMarkeryBezDannix&XmlTemplate_id=' + xmltemplate_id + '&Person_id=' + person_id, '_blank';
		}




		Ext.Ajax.request({
			async: false,
			url: url_check,
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);

					if( ! Ext.isEmpty(result.markers) && result.markers != null){
						exists_markers_empty = true;

						msg = msg.replace('%markers', result.markers.join(', '));
					}


					if(exists_markers_empty == true){
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ('yes' == buttonId) {
									window.open(url_print);
								}
								else {
									this.buttons[0].focus();
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: msg,
							title: 'Вопрос'
						});
					} else {
						window.open(url_print);
					}


				}
			}
		});


        return true;
	},
	show: function()
	{
		sw.Promed.swPrintTemplateSelectWindow.superclass.show.apply(this, arguments);
		this.center();
		if ( !arguments[0] ) {
			arguments=[{}];
		}

		if (typeof arguments[0].onSelect == 'function') {
			this.mode = 'select';
			this.onSelect = arguments[0].onSelect;
		} else {
			this.mode = 'view';
			this.onSelect = null;
		}
        this.allowSelectXmlType = arguments[0].allowSelectXmlType || false;
		var xmltype_id = arguments[0].XmlType_id || null;
        this.Evn_id = arguments[0].Evn_id || null;
        this.Person_id = arguments[0].Person_id || null;
        // при клике на "Выбор шаблона" в меню документа надо обязательно передавать EvnXml_id
        this.EvnXml_id = arguments[0].EvnXml_id || null;
        var evnclass_id = arguments[0].EvnClass_id || null;
        var uslugacomplex_id = arguments[0].UslugaComplex_id || null;
        var xmltypekind_id = arguments[0].XmlTypeKind_id || null;
        var lpusection_id = arguments[0].LpuSection_id || (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.LpuSection_id) || null;
        var medservice_id = arguments[0].MedService_id || (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedService_id) || null;
        var medpersonal_id = arguments[0].MedPersonal_id || (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedPersonal_id) || null;
        var medstafffact_id = arguments[0].MedStaffFact_id || (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedStaffFact_id) || null;
        var evn_id = arguments[0].Evn_id  || null;
        var person_id = arguments[0].Person_id || null;

		var enable_other_evn_classes = arguments[0].enableOtherEvnClasses  || null;
		this.excludedEvnClasses = arguments[0].excludedEvnClasses  || [];

		if (!evn_id && !person_id) {
			sw.swMsg.alert(lang['oshibka'], 'Не указан идентификатор документа');
			this.hide();
			return false;
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
        this.getEvnId = function(){
            return evn_id;
        };
        this.getPersonId = function(){
            return person_id;
        };

		this.doReset();

		var form = this.filterPanel.getForm(),
			grid = this.TemplateList.getGrid(),
            evnclass_combo = form.findField('EvnClass_id'),
            xmltype_combo = form.findField('XmlType_id');
			
		xmltype_combo.setValue(2);
		
        if (this.getInitEvnClassId()) {

			if (enable_other_evn_classes) {

				evnclass_combo.allowBlank = false;
				evnclass_combo.enable();
			}
			else
				evnclass_combo.disable();

        } else {
            evnclass_combo.enable();
        }

		if (evnclass_combo.getStore().getCount()==0) {
			evnclass_combo.getStore().load({

				callback: function(records,o,s){

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

		this.syncSize();
		this.doLayout();
	}
});
