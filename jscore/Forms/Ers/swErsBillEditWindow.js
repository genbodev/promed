/**
* Форма Счет на оплату
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swErsBillEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Счет на оплату',
	modal: true,
	resizable: false,
	maximized: false,
	width: 550,
	height: 360,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',

	doSave: function() {
		var win = this,
			base_form = this.MainPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE }),
			params = {
				ErsBill_Date: Ext.util.Format.date(base_form.findField('ErsBill_Date').getValue(), 'd.m.Y'),
                ErsBill_BillAmount: base_form.findField('ErsBill_BillAmount').getValue()
			};
		
		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		loadMask.show();	
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.success) {
						win.hide();
						win.callback();
					}	
				}
				else {
					Ext.Msg.alert('Ошибка', 'При сохранении произошла ошибка');
				}
				
			}
		});
	},

    getBillAmount: function() {

        var win = this,
            base_form = this.MainPanel.getForm(),
            lm = this.getLoadMask('Получение суммы счета'),
            params = {
                ErsRegistry_id: base_form.findField('ErsRegistry_id').getValue()
            };

        lm.show();
        Ext.Ajax.request({
            url: '/?c=ErsBill&m=gerBillAmount',
            params: params,
            method: 'post',
            callback: function(opt, success, response) {
                lm.hide();
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if (success && !Ext.isEmpty(response_obj.ErsBill_BillAmount)) {
                    base_form.findField('ErsBill_BillAmount').setValue(response_obj.ErsBill_BillAmount);
                }
                else {
                    sw.swMsg.alert('Ошибка', 'При получении суммы счета произошла ошибка');
                }
            }
        });
    },
	
	show: function() {
		sw.Promed.swErsBillEditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.ErsBill_id = arguments[0].ErsBill_id || null;
        this.ErsRegistry_id = arguments[0].ErsRegistry_id || null;
		
		base_form.reset();
		
		switch (this.action){
			case 'add':
				this.setTitle('Счет на оплату: Добавление');
				log(win.buttons[0]);
				win.buttons[0].setText('Сформировать');
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle('Счет на оплату: Редактирование');
				win.buttons[0].setText('Переформировать');
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle('Счет на оплату: Просмотр');
				this.enableEdit(false);
				break;
		}
		
		switch (this.action){
			case 'add':
				base_form.findField('ErsBill_Date').setValue(getGlobalOptions().date);
				base_form.findField('ErsRegistry_id').setValue(this.ErsRegistry_id);
				base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
				this.onLoad();
				break;
			case 'edit':
			case 'view':
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();
				base_form.load({
					url: '/?c=ErsBill&m=load',
					params: {
						ErsBill_id: win.ErsBill_id
					},
					success: function (form, action) {
						loadMask.hide();
						win.onLoad();
					},
					failure: function (form, action) {
						loadMask.hide();
						if (!action.result.success) {
							Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
							this.hide();
						}
					}
				});
				break;
		}
	},
	
	onLoad: function() {
		
		var win = this,
			base_form = this.MainPanel.getForm();
			
		if (this.action == 'view') return false;
		
		var lpufsscontract_combo = base_form.findField('LpuFSSContract_id');
		
		lpufsscontract_combo.getStore().load({
			params: {Lpu_id: base_form.findField('Lpu_id').getValue()},
			callback: function () {
				lpufsscontract_combo.fireEvent('change', lpufsscontract_combo, lpufsscontract_combo.getValue());
			}
		});
		
		var or_combo = base_form.findField('OrgRSchet_id');
		or_combo.getStore().load({
			params: {Lpu_id: base_form.findField('Lpu_id').getValue()},
			callback: function () {
				or_combo.fireEvent('change', or_combo, or_combo.getValue());
			}
		});

        if (this.action == 'add') {
            this.getBillAmount();
        }
	},	
	
	initComponent: function() {
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoheight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: true,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			labelWidth: 250,
			items: [{
				name: 'ErsBill_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'ErsRegistry_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				codeField: 'LpuFSSContract_Num',
				displayField: 'LpuFSSContractType_Name',
				allowBlank: false,
				editable: false,
				fieldLabel: 'Номер договора с ФСС',
				hiddenName: 'LpuFSSContract_id',
				listWidth: 550,
				width: 250,
				store: new Ext.data.JsonStore({
					autoLoad: false,
					url: '/?c=LpuFSSContract&m=loadList',
					fields: [
						{name: 'LpuFSSContract_id', mapping: 'LpuFSSContract_id'},
						{name: 'LpuFSSContract_Num', mapping: 'LpuFSSContract_Num'},
						{name: 'LpuFSSContractType_Name', mapping: 'LpuFSSContractType_Name'},
						{name: 'LpuFSSContract_begDate', mapping: 'LpuFSSContract_begDate'}
					],
					key: 'LpuFSSContract_id',
					sortInfo: {field: 'LpuFSSContract_Num'}
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{LpuFSSContract_Num}.</font>&nbsp;{LpuFSSContractType_Name}'+
					'</div></tpl>'
				),
				valueField: 'LpuFSSContract_id',
				listeners: {
					'change': function(combo, nv, ov) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == nv);
						});

						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function (combo, record) {
						var base_form = win.MainPanel.getForm();
						if (combo.getValue() > 0) {
							base_form.findField('LpuFSSContract_begDate').setValue(record.get('LpuFSSContract_begDate'));
						}
					}
				},
				xtype: 'swbaselocalcombo'
			}, {
				xtype: 'textfield',
				disabled: true,
				width: 100,
				name: 'LpuFSSContract_begDate',
				fieldLabel: 'Дата договора с ФСС'
			}, {
				xtype: 'textfield',
				name: 'ErsBill_Name',
				width: 150,
				fieldLabel: 'Наименование платежного документа'
			}, {
				xtype: 'textfield',
				name: 'ErsBill_Number',
				allowBlank: false,
				regex: /[\d]/i,
				maskRe: /\d/i,
				width: 150,
				fieldLabel: 'Счет №'
			}, {
				width: 250,
				listWidth: 300,
				hiddenName: 'OrgRSchet_id',
				xtype: 'sworgrschetcombo',
				store: new Ext.data.JsonStore({
					url: '/?c=ErsBill&m=getOrgRSchet',
					editable: false,
					key: 'OrgRSchet_id',
					autoLoad: false,
					fields: [
						{ name: 'OrgRSchet_id', type: 'int' },
						{ name: 'OrgRSchet_Name', type: 'string' },
						{ name: 'OrgRSchet_RSchet', type: 'string' },
						{ name: 'Org_Name', type: 'string' },
						{ name: 'OrgBank_BIK', type: 'string' },
						{ name: 'OrgBank_KSchet', type: 'string' },
					],
					sortInfo: {
						field: 'OrgRSchet_Name'
					}
				}),
				listeners: {
					'change': function(combo, nv, ov) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == nv);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function (combo, record) {
						var base_form = win.MainPanel.getForm();
						if (combo.getValue() > 0) {
							base_form.findField('ErsBill_BankCheckingAcc').setValue(record.get('OrgRSchet_RSchet'));
							base_form.findField('ErsBill_BankName').setValue(record.get('Org_Name'));
							base_form.findField('ErsBill_BankBIK').setValue(record.get('OrgBank_BIK'));
							base_form.findField('ErsBill_BankCorrAcc').setValue(record.get('OrgBank_KSchet'));
						}
					}
				},
				fieldLabel: 'Счет МО'
			}, {
				xtype: 'swdatefield',
				name: 'ErsBill_Date',
				disabled: true,
				width: 100,
				fieldLabel: 'Дата счета'
			}, {
				xtype: 'textfield',
				name: 'ErsBill_BankName',
				width: 250,
				fieldLabel: 'Наименование банка'
			}, {
				xtype: 'textfield',
				name: 'ErsBill_BankCheckingAcc',
				width: 250,
				fieldLabel: '№ расчетного счета'
			}, {
				xtype: 'textfield',
				name: 'ErsBill_BankBIK',
				width: 250,
				fieldLabel: 'БИК банка'
			}, {
				xtype: 'textfield',
				name: 'ErsBill_BankCorrAcc',
				width: 250,
				fieldLabel: 'Корреспондентский счет'
			}, {
				xtype: 'textfield',
				name: 'ErsBill_BillAmount',
				disabled: true,
				width: 150,
				fieldLabel: 'Сумма на оплату, руб'
			}],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'ErsBill_id' },
				{ name: 'ErsRegistry_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuFSSContract_id' },
				{ name: 'ErsBill_Name' },
				{ name: 'ErsBill_Number' },
				{ name: 'OrgRSchet_id' },
				{ name: 'ErsBill_Date' },
				{ name: 'ErsBill_BankCheckingAcc' },
				{ name: 'ErsBill_BankName' },
				{ name: 'ErsBill_BankBIK' },
				{ name: 'ErsBill_BankCorrAcc' },
				{ name: 'ErsBill_BillAmount' },
			]),
			url: '/?c=ErsBill&m=save'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.MainPanel
			],
			buttons: [{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}]
		});
		
		sw.Promed.swErsBillEditWindow.superclass.initComponent.apply(this, arguments);
	}
});