/**
* swDocumentUcStorageWorkCreateWindow - окно создания нарядов для документа учета
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @author       Salakhov R.
* @version      02.2017
* @comment      
*/
sw.Promed.swDocumentUcStorageWorkCreateWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Наряд на выполнение работ: Добавление',
	layout: 'border',
	id: 'DocumentUcStorageWorkCreateWindow',
	modal: true,
	shim: false,
	width: 420,
    height: 159,//258,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DocumentUcStorageWorkCreateForm').getFirstInvalidEl().focus(true);
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

        params.DocumentUc_id = wnd.DocumentUc_id;
        params.DrugDocumentType_Code = wnd.DrugDocumentType_Code;
        params.WhsDocumentUcInventDrug_List = wnd.WhsDocumentUcInventDrug_List.length > 0 ? wnd.WhsDocumentUcInventDrug_List.join(',') : null;
        params.DocumentUcTypeWork_id = this.form.findField('DocumentUcTypeWork_id').getValue();

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
				if (action.result) {
					wnd.callback(wnd.owner, id);
					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDocumentUcStorageWorkCreateWindow.superclass.show.apply(this, arguments);		
		this.callback = Ext.emptyFn;
		this.DocumentUc_id = null;
        this.DrugDocumentType_Code = null;
        this.WhsDocumentUcInventDrug_List = new Array();
        this.AllowedTypes_List = new Array(); //Cписок кодов типов работ доступных для выбора. Если список пуст, то доступны все типы.
        this.mode = ''; //режим работы формы

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DocumentUc_id ) {
			this.DocumentUc_id = arguments[0].DocumentUc_id;
		}
		if ( arguments[0].DrugDocumentType_Code ) {
			this.DrugDocumentType_Code = arguments[0].DrugDocumentType_Code;
		}
		if ( arguments[0].WhsDocumentUcInventDrug_List ) {
			this.WhsDocumentUcInventDrug_List = arguments[0].WhsDocumentUcInventDrug_List;
		}
		this.form.reset();

        var type_combo = this.form.findField('DocumentUcTypeWork_id');
        type_combo.getStore().clearFilter();
        type_combo.enable();

        var org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
        this.form.findField('PersonWork_cid').getStore().baseParams.Org_id = org_id;
        this.form.findField('PersonWork_eid').getStore().baseParams.Org_id = org_id;

        //определение режима работы формы
        if (wnd.DocumentUc_id > 0) {
            this.mode = 'doc_uc';
        }
        if (wnd.WhsDocumentUcInventDrug_List.length > 0) {
            this.mode = 'invent';
        }

        //определение пути сохранения
        if (wnd.mode == 'doc_uc') {
            this.form.url = '/?c=DocumentUc&m=createDocumentUcStorageWork';
        }
        if (wnd.mode == 'invent') {
            this.form.url = '/?c=WhsDocumentUcInvent&m=createDocumentUcStorageWork';
        }

        //определение типов работ доступных для выбора
        if (wnd.DrugDocumentType_Code == '6') { //6 - Приходная накладная.
            this.AllowedTypes_List = ['1']; //1 - Прием товара.
        }
        if (wnd.DrugDocumentType_Code && wnd.DrugDocumentType_Code.inlist(['10', '15'])) { //10 - Расходная накладная; 15 - Накладная на внутреннее перемещение.
            this.AllowedTypes_List = ['3']; //3 - Сборка.
        }
        if (wnd.mode == 'invent') {
            this.AllowedTypes_List = ['4']; //4 - Снятие остатков (инвентаризация)
            type_combo.setValue(4);
            type_combo.disable();
        }

        //var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        //loadMask.show();
        //loadMask.hide();
	},
	initComponent: function() {
		var wnd = this;

        var form = new Ext.form.FormPanel({
            id: 'DocumentUcStorageWorkCreateForm',
            url: '/?c=DocumentUc&m=createDocumentUcStorageWork',
            region: 'center',
            autoHeight: true,
            frame: true,
            labelAlign: 'right',
            labelWidth: 100,
            bodyStyle: 'padding: 5px 5px 0',
            items: [{
                xtype: 'swcommonsprcombo',
                comboSubject: 'DocumentUcTypeWork',
                hiddenName: 'DocumentUcTypeWork_id',
                fieldLabel: 'Вид работ',
                allowBlank: false,
                width: 250,
                listeners: {
                    'expand': function() {
                        this.getStore().filterBy(function(record) {
                            if (wnd.AllowedTypes_List.length == 0 || record.get('DocumentUcTypeWork_Code').inlist(wnd.AllowedTypes_List)) {
                                return true;
                            } else {
                                return false;
                            }
                        });
                    }
                }
            }, {
                xtype: 'swpersonworkcombo',
                fieldLabel: 'Заказчик',
                hiddenName: 'PersonWork_cid',
                allowBlank: false,
                width: 250,
                ownerWindow: wnd,
                setLinkedFieldValues: function(event_name) {
                    var rdt = this.getSelectedRecordData();
                    var person_id = null;
                    var post_id = null;
                    if (!Ext.isEmpty(rdt.PersonWork_id)) {
                        person_id = rdt.Person_id;
                        post_id = rdt.Post_id;
                    }
                    wnd.form.findField('Person_cid').setValue(person_id);
                    wnd.form.findField('Post_cid').setValue(post_id);
                }
            }, {
                xtype: 'hidden',
                fieldLabel: 'Person_cid',
                name: 'Person_cid'
            }, {
                xtype: 'hidden',
                fieldLabel: 'Post_cid',
                name: 'Post_cid'
            }, {
                xtype: 'swpersonworkcombo',
                fieldLabel: 'Исполнитель',
                hiddenName: 'PersonWork_eid',
                allowBlank: false,
                width: 250,
                ownerWindow: wnd,
                setLinkedFieldValues: function(event_name) {
                    var rdt = this.getSelectedRecordData();
                    var person_id = null;
                    var post_id = null;
                    if (!Ext.isEmpty(rdt.PersonWork_id)) {
                        person_id = rdt.Person_id;
                        post_id = rdt.Post_id;
                    }
                    wnd.form.findField('Person_eid').setValue(person_id);
                    wnd.form.findField('Post_eid').setValue(post_id);
                }
            }, {
                xtype: 'hidden',
                fieldLabel: 'Person_eid',
                name: 'Person_eid'
            }, {
                xtype: 'hidden',
                fieldLabel: 'Post_eid',
                name: 'Post_eid'
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
		sw.Promed.swDocumentUcStorageWorkCreateWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});