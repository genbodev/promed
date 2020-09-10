/**
 * swActmattersEditWindow - Редактирование\Добавление Действующего вещества
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		Rls
 * @access		public
 * @copyright	Copyright (c) 2020 Swan Ltd.
 * @template	Salakhov R.
 * @developer	Troshkov R.
 * @version		2020.07
 * @comment
 */
sw.Promed.swActmattersEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	wndName: 'Действующее вещество',
	layout: 'border',
	id: 'swActmattersEditWindow',
	modal: true,
	shim: false,
	width: 400,
	height: 276,
	resizable: false,
	doSave:  function() {
		var wnd = this;

		if ( !wnd.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('ActmattersEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var 
			strongConfirm = false, 
			strongMessage = '', 
			narcoConfirm  = false, 
			narcoMessage  = '',
			_confirm = (needConfirm, confirmText, callBack) => {
				if (needConfirm) {
					sw.swMsg.confirm('', confirmText, (btn) => { if ( btn === 'yes' && 'function' === typeof callBack ) callBack(); } );
				} else {
					if ( 'function' === typeof callBack ) callBack();
				}
			}
		;
		if ( wnd.form.findField('Actmatters_StrongGroupID').getValue()*1 > 0 ) {
			strongConfirm = true;
			strongMessage = 'Вы уверены, что вещество относится к группе Сильнодействующих веществ?';
		}
		if ( wnd.form.findField('Actmatters_NarcoGroupID').getValue()*1 > 0 ) {
			narcoConfirm = true;
			narcoMessage = 'Вы уверены, что вещество относится к группе Наркосодержащих веществ?';
		}
		_confirm(strongConfirm, strongMessage, () => {
			_confirm(narcoConfirm, narcoMessage, () => {
				wnd.submit();
			});
		})
	},
	submit: function() {
		var wnd = this;
		var params = {};
		
		params.changeRusName = wnd.rusNameChange ? 1 : 0;

		wnd.getLoadMask('Подождите, идет сохранение...').show();
		wnd.form.submit({
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
				if (action.result && action.result.Actmatters_id > 0) {
					var id = action.result.Actmatters_id;
					wnd.form.findField('Actmatters_id').setValue(id);
					wnd.callback(wnd.owner, id);
					wnd.onSave();
					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		wnd.form.reset();
		
		sw.Promed.swActmattersEditWindow.superclass.show.apply(wnd, arguments);
        if ( !arguments[0] || !arguments[0].action ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		var 
			loadMask = new Ext.LoadMask(wnd.form.getEl(), {msg:'Загрузка...'}),
			argument = arguments[0] || {}
        ;
		loadMask.show();

		wnd.owner = argument.owner || null;
		wnd.action = argument.action;

		wnd.callback = ( argument.callback && typeof argument.callback == 'function' ) ? argument.callback : Ext.emptyFn;
		wnd.onSave = ( argument.onSave && typeof argument.onSave == 'function' ) ? argument.onSave : Ext.emptyFn;
		
		wnd.rusName = '';
		wnd.rusNameChange = false;
		wnd.Actmatters_id = argument.Actmatters_id || null;

		// Для режима просмотра дисейблим все поля
		wnd.form.items.each( rec => rec.setDisabled(( 'view' === wnd.action )) );

		switch (wnd.action) {
			case 'add':
				wnd.setTitle(wnd.wndName + ": Добавление", null);
				break;
			case 'edit':
			case 'view':
				wnd.setTitle(wnd.wndName + (wnd.action === "edit" ? ": Редактирование" : ": Просмотр"), null);
				if ( wnd.Actmatters_id ) {
					Ext.Ajax.request({
						async: false,
						params: { Actmatters_id: wnd.Actmatters_id },
						url: '/?c=RlsDrug&m=loadActmatters',
						callback: function(options, success, response) {
							if (success) {
								var result = Ext.util.JSON.decode(response.responseText);
								wnd.form.setValues(result);
								wnd.rusName = result.Actmatters_Names || null;

								// Запрещаем редактировать наименование на русском, если действующее уже используется
								if ( 'edit' === wnd.action && result.usedBy*1 > 0 ) wnd.form.findField('Actmatters_Names').setDisabled(true);
							} 
							else {
								sw.swMsg.alert('Ошибка', 'Не удалось загрузить действующее вещество', function() { wnd.hide(); });
								return false;
							}
						}
					});
				} else {
					sw.swMsg.alert('Ошибка', 'Не указано действующее вещество', function() { wnd.hide(); });
					return false;
				}
				break;
		}

		loadMask.hide();
	},
	initComponent: function() {
		var wnd = this;

        var form = new Ext.form.FormPanel({
            url: '/?c=RlsDrug&m=saveActmatters',
            region: 'center',
            autoHeight: true,
            frame: true,
            labelAlign: 'right',
            labelWidth: 130,
            bodyStyle: 'padding: 5px',
            items: [{
                xtype: 'hidden',
                name: 'Actmatters_id'
            }, {
				autoHeight: true,
				title: langs('Наименование'),
				xtype: 'fieldset',
				id: 'ActmattersNameFieldset',
				labelWidth: 100,
				items: [{
					allowBlank: false,
					xtype: 'textfield',
					name: 'Actmatters_Names',
					anchor: '100%',
					listeners: {
						change: function(combo, newValue, oldValue) {
							wnd.rusNameChange = ( 'edit' === wnd.action && newValue !== wnd.rusName );
						}.createDelegate(this)
					},
					fieldLabel: langs('На русском')
				}, {
					allowBlank: false,
					xtype: 'textfield',
					name: 'Actmatters_LatName',
					anchor: '100%',
					fieldLabel: langs('На лат. (им.п.)')
				}, {
					allowBlank: false,
					xtype: 'textfield',
					name: 'Actmatters_LatNameGen',
					anchor: '100%',
					fieldLabel: langs('На лат. (род.п.)')
				}]
			}, {
				xtype: 'swrlsstronggroupscombo',
				fieldLabel: langs('Сильнодействующие'),
				anchor: '100%',
				hiddenName: 'Actmatters_StrongGroupID'
			}, {
				xtype: 'swrlsnarcogroupscombo',
				fieldLabel: langs('Наркотические'),
				anchor: '100%',
				hiddenName: 'Actmatters_NarcoGroupID'
			}, {
				xtype: 'checkbox',
				name: 'Actmatters_isMNN',
				hideLabel: true,
				inputValue: 1,
				anchor: '100%',
				boxLabel: langs('Включено в классификацию МНН')
			}]
        });

		Ext.apply(wnd, {
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
			HelpButton(this, 0),//todo проставить табиндексы
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
		sw.Promed.swActmattersEditWindow.superclass.initComponent.apply(wnd, arguments);
		wnd.form = form.getForm();
	}	
});