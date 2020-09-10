/**
* swMorbusOnkoBasePersonStateWindow - окно редактирования "Общее состояние пациента"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MorbusOnko
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @comment      
*/

Ext6.define('common.MorbusOnko.swMorbusOnkoBasePersonStateWindow', {
	/* свойства */
	requires: [
		'common.EMK.PersonInfoPanelShort',
	],
	alias: 'widget.swMorbusOnkoBasePersonStateWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'MorbusOnkoBasePersonStateeditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Общее состояние пациента',
	winTitle: 'Общее состояние пациента',
	width: 800,
    isSave: false,
	
    doSave: function() {
        var form = this.FormPanel.getForm();
        this.isSave = true;
        this.checkMorbusParams(form.findField('MorbusOnkoBasePersonState_setDT').getValue(), form.findField('MorbusOnkoBase_id').getValue());
        return false;
    },
	
    submit: function() {
        var win = this;
		win.mask(LOAD_WAIT_SAVE);
        var formParams = this.form.getValues();
        Ext.Ajax.request({
            failure:function () {
                win.unmask();
            },
            params: formParams,
            method: 'POST',
            success: function (result, action) {
                win.unmask();
                if (result.responseText) {
                    var response = Ext.util.JSON.decode(result.responseText);
                    formParams.MorbusOnkoBasePersonState_id = response.MorbusOnkoBasePersonState_id;
                    win.callback(formParams);
                    win.hide();
                }
            },
            url:'/?c=MorbusOnkoBasePersonState&m=save'
        });
    },

	openMorbusOnkoTumorStatusWindow: function(action) {
        if (!action || !action.toString().inlist(['edit', 'view'])) {
            return false;
        }

        if (getWnd('swMorbusOnkoTumorStatusWindowExt6').isVisible()) {
            getWnd('swMorbusOnkoTumorStatusWindowExt6').hide();
        }

        var grid = this.MorbusOnkoTumorStatusFrame;
        var selected_record = grid.getStore().getAt(grid.recordMenu.rowIndex);
        if (!selected_record) {
            return false;
        }

        var params = {};
        params.action = action;
        params.callback = function(data) {
            if (!data) {
                return false;
            }
            // Обновить запись в grid
            selected_record.set('OnkoTumorStatusType_id', data['OnkoTumorStatusType_id'] || null);
            selected_record.set('OnkoTumorStatusType_Name', data['OnkoTumorStatusType_Name'] || '');
            selected_record.commit();
            return true;
        };
        params.formParams = selected_record.data;

        getWnd('swMorbusOnkoTumorStatusWindowExt6').show(params);
	},
	
    setFieldsDisabled: function(d)
    {
        var form = this;
        this.FormPanel.items.each(function(f){
            if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false)) {
                f.setDisabled(d);
            }
        });
		//form.MorbusOnkoTumorStatusFrame.setReadOnly(d);
        //form.buttons[0].setDisabled(d);
    },
	
    onLoadForm: function(formParams) {
        var accessType = formParams.accessType || 'edit';
        this.setFieldsDisabled(this.action == 'view' || accessType == 'view');

        this.form.setValues(formParams);

        var grid = this.MorbusOnkoTumorStatusFrame;
        grid.getStore().removeAll();
        if (this.action != 'add') {
            grid.getStore().load({
                params: {MorbusOnkoBasePersonState_id: formParams.MorbusOnkoBasePersonState_id}
            });
        }
    },
	
    checkMorbusParams: function(newValue, MorbusOnkoBase_id) {
        var win = this,
            grid = win.MorbusOnkoTumorStatusFrame;
			
		win.mask(langs('Загрузка списка состояний опухолевого процесса...'));	

        if (this.action == 'edit' && win.isSave) {
            win.submit();
            return true;
        };
        Ext.Ajax.request({
            failure:function () {
                sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
				win.unmask();
            },
            params: {
                MorbusOnkoBasePersonState_setDT: Ext.util.Format.date(newValue, 'd.m.Y'),
                MorbusOnkoBase_id: MorbusOnkoBase_id
            },
            method: 'POST',
            success: function (response) {
                var result = Ext.util.JSON.decode(response.responseText);
                    win.unmask();
                if(result.Error_Msg&&result.Error_Msg!=''){
                    sw.swMsg.alert(langs('Ошибка'), result.Error_Msg + langs(' Сохранение невозможно.'));
                    win.error = result.Error_Msg;
                    return false;
                } else {
                    win.error = null;
                    win.form.findField('MorbusOnkoBasePersonState_id').setValue(result.MorbusOnkoBasePersonState_id);
                    win.action = 'edit';
                    win.setTitle(win.winTitle +langs(': Редактирование'));
                    grid.getStore().load({
                        params: {MorbusOnkoBasePersonState_id: result.MorbusOnkoBasePersonState_id},
                        globalFilters: {MorbusOnkoBasePersonState_id: result.MorbusOnkoBasePersonState_id}
                    });

                    if (Ext.isEmpty(win.error) && win.isSave){
                        if ( !win.formPanel.getForm().isValid() ) {
                            sw.swMsg.show({
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								msg: ERR_INVFIELDS_MSG,
								title: ERR_INVFIELDS_TIT
							});
                            return false;
                        } else {
							win.submit();
							return true;
                        }
                    }
                }
            },
            url:'/?c=MorbusOnkoBasePersonState&m=create'
        });
    },
	
	onSprLoad: function(arguments) {
		
		var win = this;
		this.form = this.FormPanel.getForm();
		
        this.action = 'add';
        this.callback = Ext6.emptyFn;
		
        if ( !arguments[0] || !arguments[0].formParams || !arguments[0].formParams.Person_id) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { win.hide(); });
            return false;
        }
		
        this.Person_id = arguments[0].formParams.Person_id;
		
        if ( arguments[0].action ) {
            this.action = arguments[0].action;
        }
		
        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }
		
        this.form.reset();
		
		this.PersonInfoPanel.load({
			Person_id: arguments[0].formParams.Person_id
		});

        switch (arguments[0].action) {
            case 'add':
                this.setTitle(this.winTitle +langs(': Добавление'));
                this.onLoadForm(arguments[0].formParams);
                break;
            case 'edit':
                this.setTitle(this.winTitle +langs(': Редактирование'));
                break;
            case 'view':
                this.setTitle(this.winTitle +langs(': Просмотр'));
                break;
        }
		
		win.mask(LOAD_WAIT);
        switch (this.action) {
            case 'add':
				win.unmask();
                break;
            case 'edit':
            case 'view':
                Ext.Ajax.request({
                    failure:function () {
                        sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
                        win.unmask();
                        win.hide();
                    },
                    params:{
                        MorbusOnkoBasePersonState_id: arguments[0].formParams.MorbusOnkoBasePersonState_id
                    },
                    method: 'POST',
                    success: function (response) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (!result[0]) { return false; }
						
                        win.onLoadForm(result[0]);
                        win.unmask();
                        return true;
                    },
                    url:'/?c=MorbusOnkoBasePersonState&m=load'
                });
                break;
        }
	},

	show: function() {
		this.callParent(arguments);
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		Ext6.define(win.id + '_FormModel', {
			extend: 'Ext6.data.Model'
		});
		
		win.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanelShort', {
			region: 'north',
			addToolbar: false,
			bodyPadding: '3 20 0 25',
			border: false,
			height: 70,
			userMedStaffFact: this.userMedStaffFact,
			ownerWin: this
		});

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			collapsible: true,
			cls: 'emk_forms accordion-panel-window',
			bodyPadding: '15 25 15 37',
			title: 'СОСТОЯНИЕ ПАЦИЕНТА',
			header: {
				cls: 'arrow-expander-panel',
				titlePosition: 2
			},
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=MorbusOnkoBasePersonState&m=save',
			items: [{
                name: 'MorbusOnkoBasePersonState_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'MorbusOnkoBase_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: langs('Дата наблюдения'),
                name: 'MorbusOnkoBasePersonState_setDT',
                xtype: 'datefield',
                allowBlank: false,
                listeners: {
                    'change':function (field, newValue, oldValue) {
                        var grid = win.MorbusOnkoTumorStatusFrame,
                            MorbusOnkoBase_id = win.form.findField('MorbusOnkoBase_id').getValue(),
                            MorbusOnkoBasePersonState_id = win.form.findField('MorbusOnkoBasePersonState_id').getValue();
                        win.isSave = false;
                        if (
                            newValue
                            && !oldValue
                            && !MorbusOnkoBasePersonState_id
                            && win.action == 'add'
                            && grid.getStore().getCount() == 0
                        ) {
                            win.checkMorbusParams(newValue, MorbusOnkoBase_id);
                        }
                    }
                }
                // При открытии формы на добавление список обновлять после проставления значения в поле «Дата наблюдения»
                // (первоначально список пустой).
            }, {
                fieldLabel: langs('Общее состояние пациента'),
                allowBlank: false,
                name: 'OnkoPersonStateType_id',
                xtype: 'commonSprCombo',
                sortField:'OnkoPersonStateType_Code',
                comboSubject: 'OnkoPersonStateType',
				anchor:'100%'
            }]
		});

		win.MorbusOnkoTumorStatusFrame = Ext6.create('Ext6.grid.Panel', {
			height: 200,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openMorbusOnkoTumorStatusWindow('edit');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			store: new Ext6.data.Store({
				fields: [
					'MorbusOnkoTumorStatus_id',
					'MorbusOnkoBasePersonState_id',
					'Diag_id',
					'OnkoTumorStatusType_id',
					'MorbusOnkoTumorStatus_NumTumor',
					'MorbusOnkoTumorStatus_DatePeriod',
					'Diag_Name',
					'OnkoTumorStatusType_Name'
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					reader: {
						type: 'json',
						rootProperty: ''
					},
					url: '/?c=MorbusOnkoTumorStatus&m=readList'
				}
			}),
			columns: [
                {dataIndex: 'MorbusOnkoTumorStatus_id', tdCls: 'nameTdCls', type: 'int', header: 'ID', hidden: true},
                {dataIndex: 'MorbusOnkoBasePersonState_id', tdCls: 'nameTdCls', type: 'int', hidden: true},
                {dataIndex: 'Diag_id', tdCls: 'nameTdCls', type: 'int', hidden: true},
                {dataIndex: 'OnkoTumorStatusType_id', tdCls: 'nameTdCls', type: 'int', hidden: true},
                {dataIndex: 'MorbusOnkoTumorStatus_NumTumor', tdCls: 'nameTdCls', type: 'string', header: langs('№ опухоли'), width: 100},
                {dataIndex: 'Diag_Name', tdCls: 'nameTdCls', type: 'string', header: langs('Топография'), width: 240},
                {dataIndex: 'OnkoTumorStatusType_Name', tdCls: 'nameTdCls', type: 'string', header: langs('Состояние'), flex: 1},
				{
					width: 40,
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.MorbusOnkoTumorStatusFrame.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
					}
				}
			],
			listeners: {
				
			}
		});
		
		win.MorbusOnkoTumorStatusPanel = new Ext6.form.FormPanel({
			border: false,
			collapsible: true,
			cls: 'emk_forms accordion-panel-window',
			bodyPadding: '15 25 15 37',
			title: 'СОСТОЯНИЕ ОПУХОЛЕВОГО ПРОЦЕССА',
			header: {
				cls: 'arrow-expander-panel',
				titlePosition: 1
			},
			tools: [],
			items: [
				win.MorbusOnkoTumorStatusFrame
			]
		});

        Ext6.apply(win, {
			items: [
				win.PersonInfoPanel,
				win.FormPanel,
				win.MorbusOnkoTumorStatusPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					win.hide();
				}
			},{
				xtype: 'SubmitButton',
				handler:function () {
					win.doSave();
				}
			}]
		});

		this.callParent(arguments);
    }
});