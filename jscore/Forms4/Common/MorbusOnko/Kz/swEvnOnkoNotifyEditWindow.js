/**
 * swEvnOnkoNotifyEditWindow - Извещение на онкобольного
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @comment
 */

Ext6.define('common.MorbusOnko.swEvnOnkoNotifyEditWindow', {
	/* свойства */
	requires: [
		'common.EMK.PersonInfoPanelShort',
	],
	alias: 'widget.swEvnOnkoNotifyEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'EvnOnkoNotifyeditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Извещение',
	width: 800,

	/* методы */
	save: function (options) {
		
		if ( !options || typeof options != 'object' ) {
			return false;
		}
		
		var
			base_form = this.FormPanel.getForm(),
			win = this,
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG);
			return false;
		}

		win.mask(LOAD_WAIT_SAVE);

		params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		var date = base_form.findField('EvnOnkoNotify_setDT').getValue();
		params.EvnOnkoNotify_setDT = Ext6.util.Format.date(date,'d.m.Y');
		
		base_form.submit({
			params: params,
			success: function(form, action) {
				win.unmask();

				if ( !Ext6.isEmpty(action.result.Error_Msg) || !action.result.EvnOnkoNotify_id ) {
					sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					return false;
				}
				
				var data = action.result;
				
				win.callback(data);
				if (options.print) {
					win.action = 'view';
					win.setFieldsDisabled(true);
					win.EvnOnkoNotify_id = data.EvnOnkoNotify_id;
					win.printNotification(win.EvnOnkoNotify_id);
				} else {
					win.hide();
				}
			},
			failure: function(form, action) {
				win.unmask();
			}
		});
	},
	
	setFieldsDisabled: function(d) {
        this.FormPanel.items.each(function(f){
            if (f && f.name && f.name.inlist(['Ethnos_id','SocStatus_id','Post_id','CitizenType'])) {
                f.setDisabled(true);
            } else if (f && (f.xtype == 'panel')) {
				f.items.each(function(ff){
					if (ff && (ff.xtype!='hidden') && (ff.xtype!='fieldset')) {
						ff.setDisabled(d);
					}
				});
            } else if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')) {
                f.setDisabled(d);
            }
        });
		this.queryById(this.id+'-save-btn').setVisible(!d);
	},
	
	doPrint: function() {
		if (this.action == 'add') {
			this.save({print: true});
		} else {
			this.printNotification(this.EvnOnkoNotify_id);
		}
	},
	
	printNotification: function(EvnOnkoNotify_id) {
		if ( !EvnOnkoNotify_id ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'OnkoNotify.rptdesign',
			'Report_Params': '&paramEvnOnkoNotify=' + EvnOnkoNotify_id,
			'Report_Format': 'pdf'
		});
	},
	onSprLoad: function(arguments) {
        var win = this;

		if (!arguments[0]) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function() {
                    win.hide();
				}
			});
            return false;
		}
		
		this.focus();
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		
		if (arguments[0].EvnOnkoNotify_id) 
			this.EvnOnkoNotify_id = arguments[0].EvnOnkoNotify_id;
		else 
			this.EvnOnkoNotify_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}	
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) 
		{
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.EvnOnkoNotify_id ) && ( this.EvnOnkoNotify_id > 0 ) )
				this.action = "view";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		win.PersonInfoPanel.load({
			Person_id: arguments[0].formParams.Person_id
		});
		
		base_form.findField('Lpu_sid').getStore().load({
			params: { object: 'Lpu' },
			callback: function () {
				var combo = base_form.findField('Lpu_sid');
				if ( base_form.findField('Lpu_sid').getStore().getCount() == 1 && win.action == 'add' ) {
					combo.setValue(combo.getStore().getAt(0).get('Lpu_id'))
				}
			}.createDelegate(this)
		});

		if (this.action != 'add') {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                icon: Ext.Msg.ERROR,
                msg: langs('Ошибка открытия формы.<br/>Форма может быть открыта только для создания извещения!'),
                title: langs('Ошибка'),
                fn: function() {
                    win.hide();
                }
            });
            return false;
		}
		
		var medstafffact_filter_params = {
			medPersonalIdList: [base_form.findField('MedPersonal_id').getValue()]
		};

		setMedStaffFactGlobalStoreFilter(medstafffact_filter_params, sw4.swMedStaffFactGlobalStore);
		base_form.findField('MedPersonal_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
		base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
		base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
		
        base_form.findField('EvnOnkoNotify_setDT').setValue(getGlobalOptions().date);

        Ext.Ajax.request({
			url: '/?c=Person&m=getPersonEditWindow',
			params: {
				person_id: arguments[0].formParams.Person_id
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if(result && result[0]) {
					this.FormPanel.getForm().setValues(result[0]);
					if (result[0].Post_id) {
						base_form.findField('Post_id').getStore().load({
							params: { object: 'Post', Post_id: result[0].Post_id },
							callback: function () {
								var combo = base_form.findField('Post_id');
								if (base_form.findField('Post_id').getStore().getCount()) {
									combo.setValue(result[0].Post_id);
								}
							}.createDelegate(this)
						});
					}
				}
			}.createDelegate(this)
		});
				
		switch (this.action) 
		{
			case 'add':
				this.setTitle(langs('Извещение: Добавление'));
				this.setFieldsDisabled(false);
				base_form.findField('EvnOnkoNotify_setDT').disable();
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
			style: 'border-bottom: 1px solid #d0d0d0;',
			ownerWin: this
		});
		
		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			bodyPadding: '15 25 15 37',
			defaults: {
				labelAlign: 'left',
				labelWidth: 350
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=EvnOnkoNotify&m=save',
			items: 
			[{
				name: 'EvnOnkoNotify_id',
				xtype: 'hidden'
			}, {
				name: 'EvnOnkoNotify_pid',
				xtype: 'hidden'
			}, {
				name: 'Morbus_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'Diag_id',
				xtype: 'hidden'
			}, {
				width: 720,
				allowBlank: false,
				fieldLabel: 'Мәлiмдеме (Извещение направлено в)',//langs('Направить извещение'),
				name: 'Lpu_sid',
				triggerAction: 'all',
				valueField: 'Lpu_id',
				displayField: 'Lpu_Nick',
				queryMode: 'local',
				store: {
					fields: [
						{name: 'Lpu_id', type: 'int'},
						{name: 'Lpu_Nick', type: 'string'},
						{name: 'LpuType_Code', type: 'int'}
					],
					proxy: {
						type: 'ajax',
						actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
						url: C_GETOBJECTLIST,
						reader: {type: 'json'}
					},
					sorters: {
						property: 'Lpu_Nick',
						direction: 'ASC'
					}
				},
				xtype: 'baseCombobox'
			}, {
				comboSubject: 'Ethnos',
				fieldLabel: 'Ұлты (Национальность)',//langs('Национальность'),
				name: 'Ethnos_id',
				typeCode: 'int',
				width: 720,
				xtype: 'commonSprCombo'
			}, {
				width: 720,
				fieldLabel: 'Тұрғыны (Житель)',
				name: 'CitizenType',
				valueField: 'CitizenType_id',
				displayField: 'CitizenType_Name',
				queryMode: 'local',
				store: new Ext6.data.SimpleStore({
					autoLoad: false,
					fields: [
						{ name: 'CitizenType_id', type: 'int' },
						{ name: 'CitizenType_Code', type: 'string' },
						{ name: 'CitizenType_Name', type: 'string' }
					],
					data: [
						[0, '0', 'Города'],
						[1, '1', 'Села']
					]
				}),
				xtype: 'baseCombobox'
			}, {
				width: 720,
				fieldLabel: 'Кәсiбi (Профессия)',
				name: 'Post_id',
				valueField: 'Post_id',
				displayField: 'Post_Name',
				queryMode: 'local',
				store: {
					pageSize: null,
					fields: [
						{name: 'Post_id', type: 'int'},
						{name: 'Post_Name', type: 'string'}
					],
					proxy: {
						type: 'ajax',
						actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
						url: C_GETOBJECTLIST,
						reader: {type: 'json'}
					}
				},
				xtype: 'baseCombobox'
			}, {
				comboSubject: 'SocStatus',
				name: 'SocStatus_id',
				width: 720,
				typeCode: 'int',
				fieldLabel: 'Әлеуметтiк жағдайы (Социальное положение)',//langs('Соц. статус'),
				xtype: 'commonSprCombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Алғашқы қаралған күнi (Дата первичного обращения)',//langs('Дата заполнения извещения'),
				name: 'EvnOnkoNotify_setFirstDT',
				width: 500,
				xtype: 'datefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Диагнозы қойылған күн (Дата установления диагноза)',//langs('Дата заполнения извещения'),
				name: 'EvnOnkoNotify_setDiagDT',
				width: 500,
				xtype: 'datefield'
			}, {
				fieldLabel: 'Қатерлi iсiктiң анықталу жағдайлары (Обстоятельства выявления опухоли)',//'Обстоятельства выявления опухоли',
				comboSubject: 'TumorCircumIdentType',
				typeCode: 'int',
				name: 'TumorCircumIdentType_id',
				xtype: 'commonSprCombo',
				width: 720
			}, {
				fieldLabel: 'Iсiк процесiнiң кезеңi (Стадия опухолевого процесса)',//langs('Стадия опухолевого процесса'),
				name: 'TumorStage_id',
				xtype: 'commonSprCombo',
				typeCode: 'int',
				displayCode: false,
				sortField:'TumorStage_Code',
				comboSubject: 'TumorStage',
				width: 720
			}, {
				layout: 'column',
				border: false,
				anchor:'100%',
				defaults: {
					border: false,
					labelWidth: 200,
					padding: '5 0 8',
					width: 107
				},
				items: [{
					xtype: 'label',
					width: 360,
					anchor: null,
					padding: '3 10 0 0',
					style: 'color: #000;',
					html: 'Диагнозы (кезеңi мен TNM бойынша таралу дәрежесi) (стадия и степень распространенности  по TNM)',
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'OnkoT',
					labelWidth: 15,
					style: 'margin-right: 20px;',
					displayCode: false,
					typeCode: 'int',
					fieldLabel: 'T',
					name: 'OnkoT_id'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'OnkoN',
					labelWidth: 15,
					style: 'margin-right: 20px;',
					displayCode: false,
					typeCode: 'int',
					fieldLabel: 'N',
					name: 'OnkoN_id'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'OnkoM',
					labelWidth: 15,
					displayCode: false,
					typeCode: 'int',
					fieldLabel: 'M',
					name: 'OnkoM_id'
				}]
			}, {
				fieldLabel: 'Диагноз',
				name: 'Diag_id',
				xtype: 'swDiagCombo',
				width: 720
			}, {
				fieldLabel: 'Диагнозды растау әдiстерi (тек бiр негiзгi әдiстi көрсетiңiз) (Метод подтверждения диагноза)',
				comboSubject: 'OnkoDiagConfType',
				name: 'OnkoDiagConfType_id',
				xtype: 'commonSprCombo',
				typeCode: 'int',
				width: 720
			}, {
				fieldLabel: 'Мәлiмдеме толтырылды (Извещение заполнено в)',
				comboSubject: 'NotifyFillPlace',
				name: 'NotifyFillPlace_id',
				xtype: 'commonSprCombo',
				typeCode: 'int',
				width: 720
			}, {
				fieldLabel: 'Науқас қайда жіберілді (Куда направлен больной)',
				comboSubject: 'NotifyDirectType',
				name: 'NotifyDirectType_id',
				xtype: 'commonSprCombo',
				typeCode: 'int',
				width: 720
			}, {
				allowBlank: false,
				fieldLabel: 'Мәлiмдеме толтырыған күн (Дата заполнения извещения)',//langs('Дата заполнения извещения'),
				name: 'EvnOnkoNotify_setDT',
				width: 500,
				xtype: 'datefield'
			}, {
				changeDisabled: false,
				disabled: true,
				fieldLabel: langs('Врач, заполнивший извещение'),
				name: 'MedPersonal_id',
				listWidth: 750,
				width: 720,
				xtype: 'swMedStaffFactCombo',
				valueField: 'MedPersonal_id',
				anchor: false
			}]
		});

        Ext6.apply(win, {
			items: [
				win.PersonInfoPanel,
				win.FormPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					win.hide();
				}
			},{
				xtype: 'SimpleButton',
				text: langs('Печать'), 
				handler:function () {
					win.doPrint();
				}
			},{
				id: win.getId()+'-save-btn',
				xtype: 'SubmitButton',
				handler:function () {
					win.save({print: false});
				}
			}]
		});

		this.callParent(arguments);
    }
});