/**
* swMzDrugRequestLpuRegionEditWindow - окно редактирования участка в заявке
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Salakhov R.
* @version      08.2018
* @comment      
*/
sw.Promed.swMzDrugRequestLpuRegionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Изменение участка в заявке врача',
	layout: 'border',
	id: 'MzDrugRequestLpuRegionEditWindow',
	modal: true,
	shim: false,
    height: 179,
	width: 550,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('MzDrugRequestLpuRegionEditForm').getFirstInvalidEl().focus(true);
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

		params.DrugRequest_id = wnd.DrugRequest_id;

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
				if (action.result && action.result.DrugRequest_id > 0) {
					wnd.onSave();
					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swMzDrugRequestLpuRegionEditWindow.superclass.show.apply(this, arguments);		
		this.onSave = Ext.emptyFn;
		this.DrugRequest_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
		if ( arguments[0].DrugRequest_id ) {
			this.DrugRequest_id = arguments[0].DrugRequest_id;
		}

		this.form.reset();
		this.lpuregion_combo.fullReset();

        wnd.getLoadMask('Загрузка...').show();
        Ext.Ajax.request({
            params:{
                DrugRequest_id: wnd.DrugRequest_id
            },
            url:'/?c=MzDrugRequest&m=loadDrugRequestLpuRegion',
            success: function (response) {
                var result = Ext.util.JSON.decode(response.responseText);
                if (result[0]) {
                    wnd.form.setValues(result[0]);
                    wnd.lpuregion_combo.getStore().baseParams.Lpu_id = result[0].Lpu_id;
                    if (!Ext.isEmpty(result[0].LpuRegion_id) || !Ext.isEmpty(result[0].DefaultLpuRegion_id)) {
                        wnd.lpuregion_combo.setValueById(!Ext.isEmpty(result[0].LpuRegion_id) ? result[0].LpuRegion_id : result[0].DefaultLpuRegion_id);
					} else {
                        wnd.lpuregion_combo.loadData();
					}
                }
                wnd.getLoadMask().hide();
            },
            failure:function () {
                sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные заявки'));
                wnd.getLoadMask().hide();
                wnd.hide();
            }
        });
    },
	initComponent: function() {
		var wnd = this;

        this.lpuregion_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Участок'),
            hiddenName: 'LpuRegion_id',
            displayField: 'LpuRegion_Name',
            valueField: 'LpuRegion_id',
            allowBlank: false,
            anchor: '100%',
			store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'LpuRegion_id', mapping: 'LpuRegion_id' },
                    { name: 'LpuRegion_Name', mapping: 'LpuRegion_Name' }
                ],
                key: 'LpuRegion_id',
                sortInfo: { field: 'LpuRegion_Name' },
                url:'/?c=MzDrugRequest&m=loadLpuRegionCombo'
            }),
			tip: 'Участковый врач должен указать участок, по которому формируется заявка',
            listeners: {
                render: function(c) {
                    var tt = new Ext.ToolTip({
                        target: c.getEl(),
                        html: c.tip
                    });
                }
            },
            setValueById: function(id) {
                var combo = this;
                combo.store.baseParams.LpuRegion_id = id;
                combo.store.load({
                    callback: function(){
                        combo.setValue(id);
                        combo.store.baseParams.LpuRegion_id = null;
                    }
                });
            },
            loadData: function() {
                var combo = this;
                combo.store.load({
                    callback: function(){
                        combo.setValue(null);
                    }
                });
            }
        });

        var form = new Ext.form.FormPanel({
            url:'/?c=MzDrugRequest&m=saveDrugRequestLpuRegion',
			id: 'MzDrugRequestLpuRegionEditForm',
            region: 'center',
            autoHeight: true,
            frame: true,
            labelAlign: 'right',
            labelWidth: 70,
            bodyStyle: 'padding: 5px 5px 0',
            items: [
            	{
					xtype: 'textarea',
					fieldLabel : langs('Заявка'),
					name: 'DrugRequest_Name',
					anchor: '100%',
					disabled: true
				},
                wnd.lpuregion_combo
			]
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
		sw.Promed.swMzDrugRequestLpuRegionEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});