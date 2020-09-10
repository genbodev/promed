/**
* swOrgFarmacyStorageLinkedByLpuEditWindow - окно редактирования прикрепления аптеки к МО
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
sw.Promed.swOrgFarmacyStorageLinkedByLpuEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Прикрепление подразделения МО к складу аптеки',
	layout: 'border',
	id: 'OrgFarmacyStorageLinkedByLpuEditWindow',
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
            wnd.SearchGrid.setColumnHidden('Buttons_List', true);
            wnd.buttons[0].disable();
        } else {
            wnd.SearchGrid.setColumnHidden('Buttons_List', false);
            wnd.buttons[0].enable();
        }
    },
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('OrgFarmacyStorageLinkedByLpuEditForm').getFirstInvalidEl().focus(true);
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
		sw.Promed.swOrgFarmacyStorageLinkedByLpuEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.Lpu_id = null;
		this.OrgFarmacy_id = null;
		this.WhsDocumentCostItemType_id = null;

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

		this.setTitle("Прикрепление подразделения МО к складу аптеки");
		this.form.reset();
        this.setDisabled(this.action == 'view');

        this.StorageCombo.getStore().baseParams = {OrgFarmacy_id: this.OrgFarmacy_id};
        if (this.OrgFarmacy_id) {
            this.StorageCombo.getStore().load();
        } else {
            this.StorageCombo.getStore().removeAll();
        }


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
            url:'/?c=Drug&m=saveLpuBuildingStorageLinkDataFromJSON',
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

        this.StorageCombo = new sw.Promed.SwBaseRemoteCombo({
            mode: 'local',
            hiddenName: 'Storage_id',
            displayField: 'Storage_Name',
            valueField: 'Storage_id',
            triggerAction: 'all',
            allowBlank: true,
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{Storage_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'Storage_id', mapping: 'Storage_id' },
                    { name: 'Storage_Name', mapping: 'Storage_Name' }
                ],
                key: 'Storage_id',
                sortInfo: { field: 'Storage_Name' },
                url:'/?c=Drug&m=loadOrgFarmacyStorageCombo'
            }),
            getNameById: function(id) {
                 var name = '';
                 this.getStore().each(function(record) {
                     if (record.get('Storage_id') == id) {
                         name = record.get('Storage_Name');
                         return false;
                     }
                 });
                 return name;
            }
        });

        this.SearchGrid = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 125,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=Drug&m=getLpuBuildingStorageLinkedByOrgFarmacy',
            height: 180,
            editformclassname: null,
            id: 'OrgFarmacyStorageLinkedByLpuEditGrid',
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
                {name: 'OrgFarmacyIndex_id', type: 'int', header: 'ID', key: true},
                {name: 'LpuBuilding_id', hidden: true},
                {name: 'LpuBuilding_Name', type: 'string', header: langs('Подразделения МО'), width: 250, id: 'autoexpand'},
                {name: 'LsGroup_id', hidden: true},
                {name: 'LsGroup_Name', type: 'string', header: langs('Группа ЛС'), width: 150},
                {name: 'Storage_id', header: langs('Склад'), width: 150, editor: wnd.StorageCombo, renderer: function(v, p, r) {
                    var val = '';
                    if(v > 0){
                        val = r.get('Storage_Name');
                    }
                    return val;
                }},
                {name: 'Buttons_List', header: '', width: 50, renderer: function(v, p, r) {
                    var val = '';
                    val += '<a title="Добавить" onClick="getWnd(\'swOrgFarmacyStorageLinkedByLpuEditWindow\').SearchGrid.copyRecord('+r.get('OrgFarmacyIndex_id')+');"><span class="add16" style="display: inline-block; height: 16px; width: 16px;  cursor: pointer"></span></a>';
                    val += '<a title="Удалить" onClick="getWnd(\'swOrgFarmacyStorageLinkedByLpuEditWindow\').SearchGrid.deleteRecord('+r.get('OrgFarmacyIndex_id')+');" '+(r.get('Delete_Enabled') != 1 ? ' style="display: none;"' : '')+'><span class="delete16" style="display: inline-block; height: 16px; width: 16px;  cursor: pointer"></span></a>';
                    return val;
                }},
                {name: 'Delete_Enabled', hidden: true},
                {name: 'Storage_Name', hidden: true},
                {name: 'state', hidden: true}
            ],
            onLoadData: function() {
                this.setDeleteEnabled();
            },
            onBeforeEdit: function(o) {
                if (Ext.isEmpty(o.record.get('OrgFarmacyIndex_id'))) {
                    return false;
                }
            },
            onAfterEdit: function(o) {
                o.record.set('Storage_Name', wnd.StorageCombo.getNameById(o.value));
                if (o.record.get('state') != 'add') {
                    o.record.set('state', 'edit');
                }
                o.record.commit();
            },
            getChangedData: function(){ //возвращает новые и измненные показатели
                var data = new Array();
                this.clearFilter();
                this.getGrid().getStore().each(function(record) {
                    if (!Ext.isEmpty(record.get('OrgFarmacyIndex_id')) && ((record.get('state') == 'add' && !Ext.isEmpty(record.get('Storage_id'))) || record.get('state') == 'edit' || record.get('state') == 'delete')) {
                        var item = record.data;
                        // Создаем копию объекта
                        var copy = item.constructor();
                        for (var attr in item) {
                            if (item.hasOwnProperty(attr)) copy[attr] = item[attr];
                        }
                        data.push(copy);
                    }
                });
                this.setFilter();
                return data;
            },
            getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
                var dataObj = this.getChangedData();
                return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
            },
            copyRecord: function(id) {
                var store = this.getGrid().getStore();

                this.clearFilter();

                var record_pos = store.findBy(function(r) {
                    return r.get('OrgFarmacyIndex_id') == id;
                });
                var record = store.getAt(record_pos);
                var record_obj = new Ext.data.Record.create(this.jsonData['store']);

                var data = new Object();
                data.OrgFarmacyIndex_id = 1000000+Math.floor(Math.random()*1000000); //генерируем временный идентификатор
                data.LpuBuilding_id = record.get('LpuBuilding_id');
                data.LpuBuilding_Name = record.get('LpuBuilding_Name');
                data.LsGroup_id = record.get('LsGroup_id');
                data.LsGroup_Name = record.get('LsGroup_Name');
                data.state = 'add';

                store.insert(record_pos+1, new record_obj(data));
                this.setFilter();

                this.setDeleteEnabled(data.LpuBuilding_id, data.LsGroup_id);
            },
            deleteRecord: function(id) {
                var store = this.getGrid().getStore();
                var record_pos = store.findBy(function(r) {
                    return r.get('OrgFarmacyIndex_id') == id;
                });
                var record = store.getAt(record_pos);

                if (record.get('Delete_Enabled') == 1) {
                    var lb_id = record.get('LpuBuilding_id');
                    var lsg_id = record.get('LsGroup_id');

                    if (record.get('state') == 'add') {
                        this.getGrid().getStore().remove(record);
                    } else {
                        record.set('state', 'delete');
                        record.set('Delete_Enabled', '0');
                        record.commit();
                        this.setFilter();
                    }

                    this.setDeleteEnabled(lb_id, lsg_id);
                }
            },
            setDeleteEnabled: function(lb_id, lsg_id) { //проверка допутимости удаления записей и проставление соответствующего признака, если параметры не переданы, то проверяются все записи
                var last_record = null;
                var last_lb_id = null;
                var last_lsg_id = null;
                var last_cnt = 0;

                this.getGrid().getStore().each(function(r){
                    if ((Ext.isEmpty(lb_id) || (r.get('LpuBuilding_id') == lb_id && r.get('LsGroup_id') == lsg_id)) && r.get('state') != 'delete') {
                        r.set('Delete_Enabled', '1');
                        r.commit();

                        if (r.get('LpuBuilding_id') == last_lb_id && r.get('LsGroup_id') == last_lsg_id) {
                            last_cnt++;
                        } else {
                            if (last_cnt == 1) { //если последняя запись была единственной (в пределах подразделения/группы), то запрещаем её редактирование
                                last_record.set('Delete_Enabled', '0');
                                last_record.commit();
                            }

                            last_lb_id = r.get('LpuBuilding_id');
                            last_lsg_id = r.get('LsGroup_id');
                            last_cnt = 1;
                        }

                        last_record = r;
                    }
                });

                if (last_cnt == 1) { //если последняя запись была единственной (в пределах подразделения/группы), то запрещаем её редактирование
                    last_record.set('Delete_Enabled', '0');
                    last_record.commit();
                }
            },
            clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
                this.getGrid().getStore().clearFilter();
            },
            setFilter: function() { //скрывает удаленные записи
                this.getGrid().getStore().filterBy(function(record){
                    return (record.get('state') != 'delete');
                });
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
		sw.Promed.swOrgFarmacyStorageLinkedByLpuEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});