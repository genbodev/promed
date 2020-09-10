/**
 * swDispNotificationsDiagEditWindow - окно редактирования установленных режимов рассылки напоминаний госпитализации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @version			10.07.2019
 */

/*NO PARSE JSON*/

sw.Promed.swNoticeModeSettingsEditWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'swNoticeModeSettingsEditWindow',
    width: 640,
    autoHeight: true,
    modal: true,

    formStatus: 'edit',
    action: 'view',
    callback: Ext.emptyFn,
	doSave: function(options) {
		var options = options||{};
		if (options.needCheck == undefined) {
			options.needCheck = true;
		}
		
		let me = this,
			base_form = me.getBaseForm();

        if ( !base_form.isValid() ) {
            sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					me.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(me),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        if ( !(base_form.findField('NoticeModeSettings_IsSMS').getValue() || base_form.findField('NoticeModeSettings_IsEmail').getValue())) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'Необходимо выбрать хотя бы один способ уведомлений.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        
		if (!options.withoutMode&&me.grid.getGrid().getStore().getCount()==1&&me.grid.getGrid().getStore().getAt(0).get('NoticeModeLink_id')==null) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'Необходимо установить хотя бы один режим уведомлений.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if (me.action == 'add') {
			var lpu = base_form.findField('Lpu_sid');
			if (lpu.getValue() > 0) {
				if (options.needCheck && me.checkLpuSettingsExist(lpu.getValue(), options)) {
					return false;
				} else if (options.Lpu_exist){
					me.showWarning('Для ' + lpu.lastSelectionText + ' уже установлены параметры уведомлений. Измените уже имеющуюся запись.');
					me.hide();
					return false;
				}
			} else if (lpu.getValue()== '') {
				if (options.needCheck && me.checkLpuSettingsExist(0, options)) {
					return false;
				} else if(options.Lpu_exist) {
					me.showWarning('Стандартные параметры уведомлений для всех МО уже установлены. Измените уже имеющуюся запись.');
					me.hide();
					return false;
				}
			}
		}
		
		this.submit(options);
		return true;
	},
	showWarning: function(text) {
		sw.swMsg.show({
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.WARNING,
			msg: text,
			title: ERR_INVFIELDS_TIT
		});
	},
	submit: function(options) {
		let me = this,
			base_form = me.getBaseForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."}),
			params = new Object();
		
		params.action = me.action;
		loadMask.show();
		
		base_form.submit({
			params: params,
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					let id = action.result.NoticeModeSettings_id;
					if (id) {
						if (!options.onlySave || (options.onlySave!==1)) {
							me.callback(me.owner, id);
							me.hide();
						} else {
							base_form.findField('NoticeModeSettings_id').setValue(id);
							me.grid.params = {
								NoticeModeSettings_id: id,
								parentWin: me,
								callback: function () {me.loadGrid()}
							};
							me.grid.gFilters = {
								NoticeModeSettings_id: id
							};
							me.action = 'edit';
							me.grid.run_function_add = false;
							me.grid.getAction('action_add').execute();
						}
					}
				}
			},
			failure: function() {
				loadMask.hide();
				me.hide();
			}
		});
	},
    show: function() {
        sw.Promed.swNoticeModeSettingsEditWindow.superclass.show.apply(this, arguments);
		let me = this,
			base_form = me.getBaseForm();

        base_form.reset();
		me.grid.removeAll({clearAll: true});

		me.callback = Ext.emptyFn;
		me.action = 'view';

        if (arguments[0].action) {
			me.action = arguments[0].action;
        }
        if (arguments[0].callback) {
			me.callback = arguments[0].callback;
        }
        if (arguments[0].formParams) {
            base_form.setValues(arguments[0].formParams);
        }

        me.loadMask = new Ext.LoadMask(me.getEl(),{msg: LOAD_WAIT});
		me.loadMask.show();
		
        switch (me.action)
        {
            case 'add':
				me.setTitle(langs('Настройка уведомлений о предстоящей госпитализации: Добавление'));
				me.enableEdit(true);
				me.loadMask.hide();
                break;

            case 'edit':
            case 'view':
                if (me.action == 'edit') {
					me.setTitle(langs('Настройка уведомлений о предстоящей госпитализации: Редактирование'));
					me.enableEdit(true);
                } else {
					me.setTitle(langs('Настройка уведомлений о предстоящей госпитализации: Просмотр'));
					me.enableEdit(false);
                }

                me.loadGrid();
                break;
        }
    },
	checkLpuSettingsExist: function(lpu_id, options) {
    	var me = this;
		Ext.Ajax.request({
			url: '/?c=NoticeModeSettings&m=checkLpuSettingsExist',
			params: {
				Lpu_sid : lpu_id
			},
			success: function(response) {
				let result = Ext.util.JSON.decode(response.responseText);
				options.needCheck = false;
				options.Lpu_exist = result.exist;
				me.doSave(options);
			}
		});
		return true;
	},
	loadGrid: function(){
    	let me = this,
			base_form = me.getBaseForm();
    	
		base_form.load({
			url: '/?c=NoticeModeSettings&m=loadNoticeModeSettingsForm',
			params: {NoticeModeSettings_id: base_form.findField('NoticeModeSettings_id').getValue()},
			failure: function() {
				me.loadMask.hide();
			},
			success: function(form, response) {
				let result = Ext.util.JSON.decode(response.response.responseText);
				base_form.findField('Lpu_sid').setValue(result[0].Lpu_id ? result[0].Lpu_id : null);
				me.grid.loadData({
					globalFilters:{NoticeModeSettings_id: result[0].NoticeModeSettings_id}, 
					params: {NoticeModeSettings_id: result[0].NoticeModeSettings_id, 
					callback: function () {me.loadGrid()},
					parentWin: me,
					noFocusOnLoad:true
				}});
				me.loadMask.hide();
			}
		});
	},
	getBaseForm: function(){
    	return this.FormPanel.getForm();
	},
	MainRecordAdd: function() {
    	let me = this,
			base_form = me.getBaseForm();
    	
		if (base_form.isValid()) {
			if (base_form.findField('NoticeModeSettings_id').getValue()>0) {
				me.grid.run_function_add = false;
				me.grid.getAction('action_add').execute();
			} else {
				me.doSave({onlySave:1, withoutMode: 1});
			}
		}
		return false;
	},
    initComponent: function() {
    	let me = this;
		
		me.FormPanel = new Ext.form.FormPanel({
            bodyBorder: false,
            border: false,
            buttonAlign: 'left',
            frame: true,
            id: 'NMSEW_NoticeModeSettingsForm',
            url: '/?c=NoticeModeSettings&m=saveNoticeModeSettings',
            bodyStyle: 'padding: 10px 5px 10px 20px;',
            labelAlign: 'right',

            items: [{
                xtype: 'hidden',
                name: 'NoticeModeSettings_id'
            }, {
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_sid',
				fieldLabel: 'МО',
                width: 380
            }, {
				checked: false,
				fieldLabel: 'СМС',
                name: 'NoticeModeSettings_IsSMS',
				xtype: 'checkbox',
				inputValue: 1,
				uncheckedValue: 0
			}, {
				checked: false,
				fieldLabel: 'Email',
				name: 'NoticeModeSettings_IsEmail',
				xtype: 'checkbox',
				inputValue: 1,
				uncheckedValue: 0
			}],
            reader: new Ext.data.JsonReader({
                success: function() {}
            }, [
                {name: 'Lpu_id'},
                {name: 'NoticeModeSettings_IsSMS'},
                {name: 'NoticeModeSettings_IsEmail'}
            ]),
            keys: [{
                fn: function(e) { this.doSave(); }.createDelegate(this),
                key: Ext.EventObject.ENTER,
                stopEvent: true
            }]
        });

		me.grid = new sw.Promed.ViewFrame({
			object: 'NoticeModeLink',
			editformclassname: 'swNoticeModeUnitsWindow',
			dataUrl: '/?c=NoticeModeSettings&m=loadNoticeModeLinkGrid',
			autoLoadData: false,
			stringfields: [
					{name: 'NoticeModeLink_id', type: 'int', header: 'ID', key: true},
					{name: 'NoticeModeSettings_id', type: 'int', hidden: true, isparams: true},
					{name: 'NoticeModesType_Name', type: 'string', header: 'Режим', isparams: true, width: 180},
					{name: 'NoticeModeLink_Frequency', type: 'string', header: 'Частота', isparams: true},
					{name: 'NoticeFreqUnitsType_Name', type: 'string', header: 'Единицы измерения', isparams: true}
			],
			actions: [
					{name:'action_add', func: function() {me.MainRecordAdd()}},
					{name:'action_edit'},
					{name:'action_delete'},
					{name:'action_refresh'},
					{name:'action_view', hidden: true},
					{name:'action_print', hidden: true}
			],
			focusOnFirstLoad: false
		});
		
        Ext.apply(me, {
            items: [ me.FormPanel, me.grid],
            buttons: [
                {
                    text: BTN_FRMSAVE,
                    id: 'NMSEW_ButtonSave',
                    tooltip: langs('Сохранить'),
                    iconCls: 'save16',
                    handler: function()
                    {
						me.doSave();
                    }.createDelegate(this)
                }, { text: '-' },
                HelpButton(this, 1),
                {
                    handler: function () {
						me.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    id: 'NMSEW_CancelButton',
                    text: langs('Отменить')
                }]
        });

        sw.Promed.swNoticeModeSettingsEditWindow.superclass.initComponent.apply(me, arguments);
    }
});