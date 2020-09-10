/**
* swConvertEditWindow - тестовая форма
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Khorev Sergey (ipshon@rambler.ru)
* @version      03.02.2012
*/
/*NO PARSE JSON*/
sw.Promed.swConvertEditWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh: true,
    objectName: 'swConvertEditWindow',
    objectSrc: '/jscore/Forms/Admin/swConvertEditWindow.js',
	width : 500,
	//height : 450,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : true,
	show: function() {
        sw.Promed.swConvertEditWindow.superclass.show.apply(this, arguments);
		this.center();
        this.findById("TablesPanel").getForm().reset();
        this.findById("TablesPanel").getForm().findField("TABLE_SCHEME").getStore().load();
        this.findById("TablesPanel").getForm().findField("TABLE_NAME").getStore().removeAll();
        this.findById("TablesPanel").getForm().findField("FIELD_NAME").getStore().removeAll();
	},
	title: 'Конвертация (смена кодировки)',
	initComponent: function() {
    	Ext.apply(this, {
			buttonAlign : "left",
			buttons : 
			      [{
					text : "Сменить кодировку",
					handler : function()
                    {
                        //this.findById("TablesPanel").getForm().findField("FIELD_NAME").setValue(null);
                        this.doSave();
					}.createDelegate(this)
				  },
                  {
                    text: "-"
                  },
                  HelpButton(this, -1),
                  {
					text : "Закрыть",
					iconCls: 'close16',
					handler : function(button, event) {
						button.ownerCt.hide();
					}
				  }],					
			items:
              [
                new Ext.form.FormPanel
                ({
                id: 'TablesPanel',
                url: C_CONVERT_FIELDS,
				timeout: 120000,
                autoHeight: true,
                labelAlign: 'right',
                items:
			      [{
                      fieldLabel : "Схема",
                      valueField: 'TABLE_SCHEME',
                      hiddenName: 'TABLE_SCHEME',
                      displayField: 'TABLE_SCHEME',
                      allowBlank: false,
                      anchor: '100%',
                      listeners: {
                          'select': function(combo,record)
                          {
                              var newValue = record.get('TABLE_SCHEME');
                              this.findById("TablesPanel").getForm().findField("FIELD_NAME").clearValue();
                              this.findById("TablesPanel").getForm().findField("TABLE_NAME").clearValue();
                              this.findById("TablesPanel").getForm().findField('TABLE_NAME').getStore().load({
                                  params: {
                                      'TABLE_SCHEME': newValue
                                  }
                              });
                          }.createDelegate(this)
                      },
                      store: new Ext.data.JsonStore({
                          autoLoad :false,
                          fields:
                              [
                                  {name :'TABLE_SCHEME', type :'string'}
                              ],
                          key: 'TABLE_SCHEME',
                          sortInfo: {field: 'TABLE_SCHEME'},
                          url: C_LOAD_SCHEMES
                      }),
                      tpl: new Ext.XTemplate(
                          '<tpl for="."><div class="x-combo-list-item">',
                          '<div>{TABLE_SCHEME}</div>',
                          '</div></tpl>'
                      ),
                      xtype: 'swbaselocalcombo'
                  },
                  {
					fieldLabel : "Таблица",
                    valueField: 'TABLE_NAME',
                    hiddenName: 'TABLE_NAME',
                    displayField: 'TABLE_NAME',
                    allowBlank: false,
                    anchor: '100%',
                    listeners: {
                                'select': function(combo,record)
                                {
                                    var newValue = record.get('TABLE_NAME');
                                    this.findById("TablesPanel").getForm().findField("FIELD_NAME").clearValue();
                                    this.findById("TablesPanel").getForm().findField('FIELD_NAME').getStore().load({
                                        params: {
                                            'TABLE_NAME': newValue,
                                            'TABLE_SCHEME': this.findById("TablesPanel").getForm().findField("TABLE_SCHEME").getValue()
                                        }
                                    });
                                }.createDelegate(this)
                    },
					store: new Ext.data.JsonStore({
						autoLoad :false,
						fields: 
							[
								{name :'TABLE_NAME', type :'string'}
							],
						key: 'TABLE_NAME',
						sortInfo: {field: 'TABLE_NAME'},
						url: C_LOAD_TABLES
					}),
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '<div>{TABLE_NAME}</div>',
                        '</div></tpl>'
                    ),
					xtype: 'swbaselocalcombo'
				  },{
                      fieldLabel : "Поле",
                      valueField: 'FIELD_NAME',
                      hiddenName: 'FIELD_NAME',
                     // id: 'FIELD_NAME',
                      displayField: 'FIELD_NAME',
                      allowBlank: false,
                      anchor: '100%',
                      store: new Ext.data.JsonStore({
                          autoLoad :false,

                          fields:
                              [
                                  {name :'FIELD_NAME', type : 'string'}//,
                                  //{name : 'FIELD_TYPE', type : 'string'}
                              ],
                          key: 'FIELD_NAME',
                          sortInfo: {field: 'FIELD_NAME'},
                          url: C_LOAD_FIELDS
                      }),
                      tpl: new Ext.XTemplate(
                          '<tpl for="."><div class="x-combo-list-item">',
                          '<div>{FIELD_NAME}</div>',
                          '</div></tpl>'
                      ),
                      xtype: 'swbaselocalcombo'
                  }]
                })
              ]

		});
		sw.Promed.swConvertEditWindow.superclass.initComponent.apply(this, arguments);
	},
    doSave: function()
    {
        this.formStatus = 'save';

        var base_form = this.findById('TablesPanel').getForm();
        var form = this.findById('TablesPanel');


        if ( !base_form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                    form.getFirstInvalidEl().focus(true);
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }
        base_form.submit(
        {
            failure: function()
            {
                sw.swMsg.alert('Ошибка', 'При смене кодировки произошли ошибки');
            }.createDelegate(this),
            success: function()
            {
                sw.swMsg.alert('','Конвертирование успешно завершено');
            }.createDelegate(this)
        });
    }
});