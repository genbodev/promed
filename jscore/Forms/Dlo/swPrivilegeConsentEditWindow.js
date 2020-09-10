/**
* swPrivilegeConsentEditWindow - окно получения согласия на участие в программе.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Salakhov R.
* @version      27.06.2019
* @comment      Префикс для id компонентов PrivCEF (PrivilegeConsentEditForm)
*
*
* @input data: Person_id - ID человека
*              Evn_id - ID ТАП или ID КВС
*/

sw.Promed.swPrivilegeConsentEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	title: 'Запрос согласия на участие в программе',
    id: 'PrivilegeConsentEditWindow',
    layout: 'form',
    modal: true,
    onHide: Ext.emptyFn,
    plain: true,
    resizable: false,
    width: 700,
    listeners: {
        'hide': function() {
            this.onHide();
        }
    },
    setConsent: function(consent) {
		var wnd = this;
        var base_form = wnd.findById('PrivilegeConsentEditForm').getForm();
		if (consent) {
			if (this.action == 'add') {
				this.setDateDefaultValue();
			}
            base_form.findField('Privilege_begDate').showContainer();
            base_form.findField('Privilege_endDate').showContainer();

            wnd.buttons[0].enable();
            wnd.buttons[1].show();
		} else {
            base_form.findField('Privilege_begDate').hideContainer();
            base_form.findField('Privilege_endDate').hideContainer();

            wnd.buttons[0].disable();
            wnd.buttons[1].hide();
		}
        this.syncShadow();
	},
	setDateDefaultValue: function() {
		var base_form = this.findById('PrivilegeConsentEditForm').getForm();
		var default_beg_date = (this.is_stac && !Ext.isEmpty(this.EvnPS_disDate)) ? Date.parseDate(this.EvnPS_disDate, 'd.m.Y') : new Date(); //если выписка в рамках стационара, и передана дата выписки из стационара, то она и является значением по умолчанию
		var default_end_date = !Ext.isEmpty(this.EvnPS_disDate) ? Date.parseDate(this.EvnPS_disDate, 'd.m.Y') : new Date();

		if (default_end_date.getMonth() == 1 && default_end_date.getDate() == 29) {
			default_end_date.setDate(default_end_date.getDate() - 1);
		}
		default_end_date.setFullYear(default_end_date.getFullYear() + 1);

		base_form.findField('Privilege_begDate').setValue(default_beg_date);
		base_form.findField('Privilege_endDate').setValue(default_end_date);
	},
	printConsent: function() {
		var Report_Params = !Ext.isEmpty(this.Evn_id) ? '&paramEvn_id=' + this.Evn_id : '&paramPerson_id=' + this.Person_id;
		printBirt({
			'Report_FileName': 'PF_ConsentDLOCardioProgram.rptdesign',
			'Report_Params': Report_Params,
			'Report_Format': 'pdf'
		});
	},
	//проверка года в дате на високосность
	/*isLeapYear: function (year)
	{
		var feb29 = new Date (year, 1, 29);
		return (feb29.getMonth () == 1);
	},*/
	//вычисляем попадает ли 29 февраля в период даты
	/*isFeb29: function (begDate, endDate){

		var begYear = begDate.getFullYear();
		var endYear = endDate.getFullYear();
		var f29 = false;
		if ( (this.isLeapYear(begYear) && begDate <= new Date (begYear, 1, 29)) || (this.isLeapYear(endYear) && endDate > new Date (endYear, 1, 29)) ) {
			var f29 = true;
		}
		return f29;
	},*/
	doPrint: function() {
		var wnd = this;

		if (this.action == 'add') {
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: 'Выполнить включение пациента в программу и распечатать согласие?',
				title: 'Подтверждение',
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						wnd.doSave(true);
					}
				}
			});
		} else {
			wnd.printConsent();
		}
	},
	doSave: function(print_consent) {
		var wnd = this;

		var form = wnd.findById('PrivilegeConsentEditForm');

		if (!form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var begDate = form.getForm().findField('Privilege_begDate').getValue(),
			endDate = form.getForm().findField('Privilege_endDate').getValue();
		//получаем разницу в днях между датами начала и окончания программы (так как даты больше не редактируются, то проверка больше не нужна)
		/*var	daysDiff = Math.ceil(Math.abs(endDate.getTime() - begDate.getTime()) / (1000 * 3600 * 24));
		if (( daysDiff > 365 && !this.isFeb29(begDate, endDate)) || daysDiff > 366) {
			sw.swMsg.alert('Внимание','Период включения в программу не может превышать 1 год!');
			return false;
		}*/

		var post_data = new Object();
		post_data.PrivilegeType_id = form.getForm().findField('PrivilegeType_id').getValue();
		if (this.PersonPrivilege_id) {
			post_data.PersonPrivilege_id = this.PersonPrivilege_id;
		}
		post_data.Person_id = this.Person_id;
		post_data.is_stac = this.is_stac ? 1 : 0;
		//опции для форматирования даты в вид дд.мм.гггг
		var options = {
			year: 'numeric',
			month: 'numeric',
			day: 'numeric',
			timezone: 'UTC'
		};
		post_data.Privilege_begDate = new Date(form.getForm().findField('Privilege_begDate').getValue()).toLocaleString("ru", options);
		post_data.Privilege_endDate = new Date(form.getForm().findField('Privilege_endDate').getValue()).toLocaleString("ru", options);

		var loadMask = new Ext.LoadMask(Ext.get('PrivilegeConsentEditWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		form.getForm().submit({
            params: post_data,
			success: function(form_temp, action) {
				loadMask.hide();
				if (action.result && action.result.success) {
                    sw.swMsg.alert(langs('Сообщение'), langs('Пациент успешно включен в программу'), function() {
						wnd.printConsent();
                    	if (typeof wnd.onSave == 'function') {
                    		wnd.onSave(action.result);
						}
                    	wnd.hide();
                    });
				} else {
					if (action.result.Error_Msg) {
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					} else {
						sw.swMsg.alert(langs('Ошибка'), langs('При включении пациента в программу произошли ошибки'));
					}
				}
			},
            failure: function(form_temp, action) {
                loadMask.hide();
                var err_msg = langs('При сохранении произошли ошибки');
                if (action.result && !Ext.isEmpty(action.result.Error_Msg)) {
                    err_msg =  action.result.Error_Msg;
				}
                sw.swMsg.alert(langs('Ошибка'), err_msg);
            }
		});
	},
    show: function() {
        sw.Promed.swPrivilegeConsentEditWindow.superclass.show.apply(this, arguments);
	    var wnd = this;
        var form = wnd.findById('PrivilegeConsentEditForm');
        var base_form = form.getForm();
		wnd.action = null;
        wnd.Person_id = null;
        wnd.Evn_id = null;
        wnd.EvnPS_disDate = null; //дата выписки из стационара
        wnd.is_stac = false; //признак включения в программу в рамках стационара
        wnd.callback = Ext.emptyFn;
        wnd.onHide = Ext.emptyFn;
        wnd.onSave = Ext.emptyFn;
        wnd.ARMType = '';
		wnd.PersonPrivilege_id = null;

        if (!arguments[0] || Ext.isEmpty(arguments[0].Person_id)) {
            sw.swMsg.alert(langs('Сообщение'), langs('Неверный параметр'));
            return false;
        } else {
            wnd.Person_id = arguments[0].Person_id;
		}

		if (arguments[0].Evn_id) {
			wnd.Evn_id = arguments[0].Evn_id;
		}

		if (arguments[0].EvnPS_disDate) {
			wnd.EvnPS_disDate = arguments[0].EvnPS_disDate;
		}

		if (arguments[0].is_stac) {
			wnd.is_stac = arguments[0].is_stac;
		}

		if (arguments[0].callback) {
            wnd.callback = arguments[0].callback;
        }

        if (arguments[0].onHide) {
            wnd.onHide = arguments[0].onHide;
        }

        if (arguments[0].onSave) {
            wnd.onSave = arguments[0].onSave;
        }

        if (arguments[0].ARMType) {
            wnd.ARMType = arguments[0].ARMType;
        }

		if (arguments[0].action) {
			wnd.action = arguments[0].action;
		}

		if (arguments[0].PersonPrivilege_id) {
			wnd.PersonPrivilege_id = arguments[0].PersonPrivilege_id;
		}

		if (arguments[0].Privilege_begDate) {
			wnd.Privilege_begDate = arguments[0].Privilege_begDate;
		}

		if (arguments[0].Privilege_endDate) {
			wnd.Privilege_endDate = arguments[0].Privilege_endDate;
		}

        var loadMask = new Ext.LoadMask(Ext.get('PrivilegeConsentEditWindow'), { msg: LOAD_WAIT });
        loadMask.show();

        form.getForm().reset();
        form.getForm().findField('PrivilegeType_id').getStore().clearFilter();
        form.getForm().setValues(arguments[0]);


		//фильтрация категорий льготы (на данный момент доступна только категоря "ДЛО Кардио")
		var pt_combo = form.getForm().findField('PrivilegeType_id');
		pt_combo.getStore().filterBy(function (rec) {
            return (rec.get('PrivilegeType_SysNick') == 'kardio');
		});
		if (pt_combo.getStore().getCount() > 0) {
			pt_combo.setValue(pt_combo.getStore().getAt(0).get('PrivilegeType_id'));
		}

		switch ( this.action ) {
			case 'add':
				form.getForm().findField('Consent').setValue(false);
				wnd.setConsent(false);
				break;

			case 'edit':
				form.getForm().findField('Consent').setValue(true);
				wnd.setConsent(true);
				form.getForm().findField('Privilege_begDate').disable();
				if (wnd.Privilege_endDate) {
					form.getForm().findField('Privilege_endDate').setValue(wnd.Privilege_endDate);
				} else {
					var date = form.getForm().findField('Privilege_begDate').getValue();
					date.setFullYear(date.getFullYear() + 1);
					form.getForm().findField('Privilege_endDate').setValue(date);
				}
				break;

			default:
				break;
		}
		loadMask.hide();
    },
	initComponent: function() {
		var wnd = this;
		Ext.apply(this, {
			buttons: [
			{
				handler: function() {
					wnd.doSave();
				},
				iconCls: 'save16',
				tabIndex: 452,
				text: langs('Включить в программу')
			},
			{
				handler: function() {
					wnd.doPrint();
				},
				iconCls: 'print16',
				tabIndex: 457,
				hidden:true,
				text: langs('Печать согласия')
			},{
				text: '-'
			},
			HelpButton(this, 453),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: 454,
				text: BTN_FRMCANCEL
			}],
			items: [
			new Ext.form.FormPanel({
				autoHeight: true,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'PrivilegeConsentEditForm',
				labelAlign: 'right',
				items: [{
					xtype: 'swbaselocalcombo',
					fieldLabel: langs('Категория'),
					valueField: 'PrivilegeType_id',
					hiddenName: 'PrivilegeType_id',
					codeField: 'PrivilegeType_VCode',
					displayField: 'PrivilegeType_Name',
					editable: false,
					disabled: true,
					allowBlank: false,
					anchor: '100%',
					lastQuery: '',
					store: new Ext.db.AdapterStore({
						autoLoad: false,
						dbFile: 'Promed.db',
						fields: [
							{ name: 'PrivilegeType_id', type: 'int'},
							{ name: 'PrivilegeType_Code', type: 'int'},
							{ name: 'PrivilegeType_VCode', type: 'string'},
							{ name: 'PrivilegeType_Name', type: 'string'},
							{ name: 'PrivilegeType_SysNick', type: 'string'},
							{ name: 'ReceptDiscount_id', type: 'int'},
							{ name: 'ReceptFinance_id', type: 'int'},
							{ name: 'PrivilegeType_begDate', type: 'date', dateFormat: 'd.m.Y'},
							{ name: 'PrivilegeType_endDate', type: 'date', dateFormat: 'd.m.Y'}
						],
						key: 'PrivilegeType_id',
						sortInfo: { field: 'PrivilegeType_VCode' },
						tableName: 'PrivilegeType'
					}),
					tabIndex: 455,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{PrivilegeType_VCode}</font>&nbsp;{PrivilegeType_Name}',
						'</div></tpl>'
					)
				}, {
					xtype: 'checkbox',
					name: 'Consent',
					fieldLabel: langs('Согласен(-сна)'),
					listeners: {
                        check: function(field, newValue) {
							wnd.setConsent(newValue);
						}
					}
				}, {
					allowBlank: false,
					fieldLabel: langs('Дата начала'),
					format: 'd.m.Y',
					name: 'Privilege_begDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: 456,
					validateOnBlur: true,
					xtype: 'swdatefield',
					disabled: true
				}, {
					allowBlank: false,
					fieldLabel: langs('Дата окончания'),
					format: 'd.m.Y',
					name: 'Privilege_endDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: 451,
					xtype: 'swdatefield',
					disabled: true,
					qtip: 'Период участия в программе составляет 1 год от даты выписки из стационара'
				}],
				keys: [{
					fn: function() {
						this.doSave();
					},
					key: Ext.EventObject.ENTER,
					scope: this,
					stopEvent: true
				}],
				reader: new Ext.data.JsonReader({
					success: function() { alert('All Right!'); }
				}, [
					{ name: 'PersonPrivilege_id' },
					{ name: 'Privilege_begDate' },
					{ name: 'Privilege_endDate' },
					{ name: 'PrivilegeType_id' },
					{ name: 'SubCategoryPrivType_id' },
					{ name:'PersonPrivilege_IsAddMZ'}
				]),
				url: '/?c=Privilege&m=savePrivilegeConsent'
			})]
		});
		sw.Promed.swPrivilegeConsentEditWindow.superclass.initComponent.apply(this, arguments);
	}
});