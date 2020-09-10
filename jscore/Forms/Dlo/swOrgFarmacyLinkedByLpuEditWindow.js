/**
* swOrgFarmacyLinkedByLpuEditWindow - окно редактирования прикрепления аптеки к МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Salakhov R.
* @version      10.2018
* @comment      
*/
sw.Promed.swOrgFarmacyLinkedByLpuEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Прикрепление аптеки к Подразделениям МО',
	layout: 'border',
	id: 'OrgFarmacyLinkedByLpuEditWindow',
	modal: true,
	shim: false,
	width: 600,
	resizable: false,
	maximizable: false,
	maximized: false,
    setDisabled: function(disable) {
        var wnd = this;

        wnd.SearchGrid.setReadOnly(disable);

        if (disable) {
            wnd.buttons[0].disable();
        } else {
            wnd.buttons[0].enable();
        }
    },
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('OrgFarmacyLinkedByLpuEditForm').getFirstInvalidEl().focus(true);
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

		params.Lpu_id = wnd.Lpu_id;
		params.OrgFarmacy_id = wnd.OrgFarmacy_id;
		params.WhsDocumentCostItemType_id = wnd.WhsDocumentCostItemType_id;
        params.LinkDataJSON = wnd.SearchGrid.getJSONChangedData();

        if (params.LinkDataJSON.length > 0) {
            //TODO: restore
            //wnd.getLoadMask('Подождите, идет сохранение...').show();
            this.form.submit({
                params: params,
                failure: function(result_form, action) {
                    //TODO: restore
                    //wnd.getLoadMask().hide();
                    if (action.result) {
                        if (action.result.Error_Code) {
                            Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
                        }
                    }
                },
                success: function(result_form, action) {
                    //TODO: restore
                    //wnd.getLoadMask().hide();
                    if (action.success) {
                        wnd.callback(wnd.owner, null);
                        wnd.hide();
                    }
                }
            });
        } else { //если сохранять нечего, просто закрываем форму
            wnd.hide();
        }
	},
	show: function() {
        var wnd = this;
		sw.Promed.swOrgFarmacyLinkedByLpuEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.Lpu_id = null;
		this.OrgFarmacy_id = null;
		this.WhsDocumentCostItemType_id = null;
        this.IsNarko = false; //признак наличия у аптеки лицензии на наркотику

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
		if ( arguments[0].Lpu_id ) {
			this.Lpu_id = arguments[0].Lpu_id;
		}
		if ( arguments[0].OrgFarmacy_id ) {
			this.OrgFarmacy_id = arguments[0].OrgFarmacy_id;
		}
		if ( arguments[0].WhsDocumentCostItemType_id ) {
			this.WhsDocumentCostItemType_id = arguments[0].WhsDocumentCostItemType_id;
		}
        if ( arguments[0].IsNarko ) {
            this.IsNarko = true;
        }

		this.setTitle("Прикрепление аптеки к подразделениям МО");
		this.form.reset();
        this.setDisabled(this.action == 'view');

        if ( arguments[0].Lpu_Nick ) {
            this.form.findField('Lpu_Nick').setValue(arguments[0].Lpu_Nick);
        }
        if ( arguments[0].OrgFarmacy_Nick ) {
            var of_name = arguments[0].OrgFarmacy_Nick;
            if ( arguments[0].OrgFarmacy_HowGo ) {
                of_name += ', '+arguments[0].OrgFarmacy_HowGo;
            }
            this.form.findField('OrgFarmacy_FullName').setValue(of_name);
        }
        if ( arguments[0].WhsDocumentCostItemType_Name ) {
            this.form.findField('WhsDocumentCostItemType_Name').setValue(arguments[0].WhsDocumentCostItemType_Name);
        }

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();

		this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
		loadMask.hide();

		//загрузка грида
        wnd.SearchGrid.removeAll();
        var params = new Object();

        params.Lpu_id = this.Lpu_id;
        params.OrgFarmacy_id = this.OrgFarmacy_id;
        params.WhsDocumentCostItemType_id = this.WhsDocumentCostItemType_id;

        if (params.Lpu_id > 0 && params.OrgFarmacy_id > 0) {
            wnd.SearchGrid.loadData({params: params, globalFilters: params});
        }
	},
	initComponent: function() {
		var wnd = this;

        var form = new Ext.form.FormPanel({
            url:'/?c=Drug&m=saveLpuBuildingLinkDataFromJSON',
            region: 'north',
            autoHeight: true,
            frame: true,
            labelAlign: 'right',
            labelWidth: 110,
            bodyStyle: 'padding: 5px 5px 0',
            items: [{
                xtype: 'textfield',
                fieldLabel: langs('МО'),
                name: 'Lpu_Nick',
				disabled: true,
				anchor: '100%'
            }, {
                xtype: 'textfield',
                fieldLabel: langs('Аптека'),
                name: 'OrgFarmacy_FullName',
                disabled: true,
                anchor: '100%'
            }, {
                xtype: 'textfield',
                fieldLabel: langs('Программа ЛЛО'),
                name: 'WhsDocumentCostItemType_Name',
                disabled: true,
                anchor: '100%'
            }]
        });

        this.LsGroupCombo = new Ext.form.ComboBox({
            hiddenName: 'LsGroup_id',
            displayField: 'LsGroup_Name',
            valueField: 'LsGroup_id',
            mode: 'local',
            triggerAction: 'all',
            allowBlank: false,
			store: new Ext.data.SimpleStore({

                fields: [
                    {name: 'LsGroup_id', type: 'int'},
                    {name: 'LsGroup_Name', type: 'string'}
                ],
                data: [[1, langs('Все ЛП')], [2,langs('Все кроме НС и ПВ')], [3,langs('НС и ПВ')]]
            })
        });

        this.SearchGrid = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 125,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=Drug&m=getLpuBuildingLinkedByOrgFarmacy',
            height: 180,
            editformclassname: null,
            id: 'OrgFarmacyLinkedByLpuEditGrid',
            region: 'center',
            paging: false,
            saveAtOnce: false,
            style: 'margin-bottom: 10px',
            title: null,
            toolbar: false,
            contextmenu: false,
            actions: [
                {name: 'action_add', disabled: true},
                {name: 'action_edit', disabled: true},
                {name: 'action_view', disabled: true},
                {name: 'action_delete', disabled: true},
                {name: 'action_print', disabled: true}
            ],
            stringfields: [
                {name: 'LpuBuilding_id', type: 'int', header: 'ID', key: true},
                {name: 'IsVkl', type: 'checkcolumnedit', header: '&nbsp;',  width: 65},
                {name: 'LpuBuilding_Name', type: 'string', header: langs('Подразделения МО'), width: 250, id: 'autoexpand'},
                {name: 'LsGroup_id', header: langs('Группа ЛС'), width: 150, editor: wnd.LsGroupCombo, renderer: function(v, p, r) {
                    var val = '';
                    switch(v){
                        case 1: val = 'Все ЛП'; break;
                        case 2: val = 'Все кроме НС и ПВ'; break;
                        case 3: val = 'НС и ПВ'; break;
                    }
                    return val;
                }},
                {name: 'state', hidden: true}
            ],
            onBeforeEdit: function(o) {
                if (Ext.isEmpty(o.record.get('LpuBuilding_id')) || Ext.isEmpty(o.record.get('IsVkl')) || !o.record.get('IsVkl') || o.record.get('IsVkl') == 'false' || !wnd.IsNarko) {  //если нет лицензии на наркотику, группу редактировать нельзя
                    return false;
                }
            },
            onAfterEdit: function(o) {
                var state = o.record.get('state');
                var default_group_id = wnd.IsNarko ? 1 : 2; //если у аптеки есть лицензия на наркотику, група по умолчанию - 'Все ЛП', иначе - 'Все кроме НС и ПВ'

                if (o.field == 'IsVkl') {
                    if (o.value) {
                        if (Ext.isEmpty(state) || state == 'add') {
                            o.record.set('state', 'add');
                            o.record.set('LsGroup_id', 1);
                        } else {
                            o.record.set('state', 'edit');
                        }
                        o.record.set('LsGroup_id', default_group_id);
                    } else {
                        if (state == 'edit' || state == 'saved') {
                            o.record.set('state', 'delete');
                        }
                        if (state == 'add') {
                            o.record.set('state', '');
                        }
                        o.record.set('LsGroup_id', 0);
                    }
                }
                if (o.field == 'LsGroup_id') {
                    if (state == 'saved') {
                        o.record.set('state', 'edit');
                    }
                }

                o.record.commit();
            },
            getChangedData: function(){ //возвращает новые и измненные показатели
                var data = new Array();
                this.getGrid().getStore().each(function(record) {
                    if (!Ext.isEmpty(record.get('LpuBuilding_id')) && (record.get('state') == 'add' || record.get('state') == 'edit' ||  record.get('state') == 'delete')) {
                        var item = record.data;
                        // Создаем копию объекта
                        var copy = item.constructor();
                        for (var attr in item) {
                            if (item.hasOwnProperty(attr)) copy[attr] = item[attr];
                        }
                        data.push(copy);
                    }
                });
                return data;
            },
            getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
                var dataObj = this.getChangedData();
                return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
            }
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
			items:[
				form,
                this.SearchGrid
			]
		});
		sw.Promed.swOrgFarmacyLinkedByLpuEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});