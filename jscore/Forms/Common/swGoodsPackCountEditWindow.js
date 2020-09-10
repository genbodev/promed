/**
* swGoodsPackCountEditWindow - окно редактирования информации о количестве ед. измерения в упаковке
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Salakhov R.
* @version      04.2018
* @comment      
*/
sw.Promed.swGoodsPackCountEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Редактирование',
	layout: 'border',
	id: 'GoodsPackCountEditWindow',
	modal: true,
	shim: false,
	width: 600,
    height: 202,
	resizable: false,
	maximizable: false,
	maximized: false,
    setTitleByAction: function() {
        this.setTitle("Количество товара в потребительской упаковке");
        switch (this.action) {
            case 'add':
                this.setTitle(this.title + ": Добавление");
                break;
            case 'edit':
            case 'view':
                this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
                break;
        }
    },
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('GoodsPackCountEditForm').getFirstInvalidEl().focus(true);
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
    loadData: function(callback) {
        var wnd = this;
        var load_params = new Object();
        var gu_id = this.form.findField('GoodsUnit_id').getValue();
        var user_org_id = getGlobalOptions().org_id;

        if (!Ext.isEmpty(gu_id)) {
            //загрузка данных

            var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
            //loadMask.show();

            load_params.Drug_id = this.Drug_id;
            load_params.DrugComplexMnn_id = this.DrugComplexMnn_id;
            load_params.Tradenames_id = this.Tradenames_id;
            load_params.GoodsUnit_id = this.GoodsUnit_id;
            load_params.UserOrg_id = user_org_id > 0 ? user_org_id : null;

            Ext.Ajax.request({
                params: load_params,
                failure: function () {
                    sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
                    loadMask.hide();
                    wnd.hide();
                },
                success: function (response) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    if (!result[0]) {
                        return false
                    }
                    wnd.form.setValues(result[0]);
                    if (typeof callback == 'function') {
                        callback();
                    }
                    loadMask.hide();
                },
                url:'/?c=GoodsPackCount&m=load'
            });
        }
    },
	submit: function() {
		var wnd = this;
		var params = new Object();

        //params.GoodsUnit_id = this.form.findField('GoodsUnit_id').getValue();
        params.UserOrg_id = getGlobalOptions().org_id;

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
				if (action.result && action.result.GoodsPackCount_id > 0) {
					//wnd.callback(wnd.owner, id);
                    wnd.onSave(action.result);
					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swGoodsPackCountEditWindow.superclass.show.apply(this, arguments);		
		this.action = 'edit';
		//this.owner = null;
		//this.callback = Ext.emptyFn;
		this.onSave = Ext.emptyFn;
		this.Drug_id = null;
		this.DrugComplexMnn_id = null;
		this.Tradenames_id = null;
		this.GoodsUnit_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

        if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
            this.onSave = arguments[0].onSave;
        }
		if ( arguments[0].Drug_id ) {
			this.Drug_id = arguments[0].Drug_id;
		}
		if ( arguments[0].DrugComplexMnn_id ) {
			this.DrugComplexMnn_id = arguments[0].DrugComplexMnn_id;
		}
		if ( arguments[0].Tradenames_id ) {
			this.Tradenames_id = arguments[0].Tradenames_id;
		}
		if ( arguments[0].GoodsUnit_id ) {
			this.GoodsUnit_id = arguments[0].GoodsUnit_id;
		}
		this.form.reset();

		this.form.setValues({
            GoodsUnit_id: this.GoodsUnit_id
        });

        this.loadData(function() {
            wnd.setTitleByAction();
        });
	},
	initComponent: function() {
		var wnd = this;

        var form = new Ext.form.FormPanel({
            id: 'GoodsPackCountEditForm',
            url:'/?c=GoodsPackCount&m=save',
            region: 'center',
            autoHeight: true,
            frame: true,
            labelAlign: 'right',
            labelWidth: 120,
            bodyStyle: 'padding: 5px 5px 0',
            items: [{
                xtype: 'hidden',
                fieldLabel: 'DrugComplexMnn_id',
                name: 'DrugComplexMnn_id'
            }, {
                xtype: 'hidden',
                fieldLabel: 'TRADENAMES_ID',
                name: 'TRADENAMES_ID'
            }, {
                xtype: 'textarea',
                fieldLabel: 'Медикамент',
                name: 'Drug_Name',
                disabled: true,
                anchor: '100%'
            }, {
                xtype: 'swgoodsunitcombo',
                fieldLabel: 'Ед. измерения',
                hiddenName: 'GoodsUnit_id',
                allowBlank: false,
                width: 120,
                listeners: {
                    'select': function(combo, record, idx) {
                        if (record.get('GoodsUnit_id') > 0) {
                            wnd.GoodsUnit_id = record.get('GoodsUnit_id');
                            wnd.loadData();
                        }
                    },
                    'change': function(combo, new_value, old_value) {
                        if (Ext.isEmpty(new_value)) {
                            wnd.GoodsUnit_id = null;
                            wnd.form.findField('GoodsPackCount_Count').setValue(null);
                        }
                    }
                }
            }, {
                xtype: 'numberfield',
                fieldLabel: 'Количество',
                name: 'GoodsPackCount_Count',
                allowBlank: false,
                allowNegative: false,
                allowDecimal: true,
                width: 120
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
		sw.Promed.swGoodsPackCountEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});