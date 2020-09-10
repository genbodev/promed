/**
 * swAccessRightsTestEditWindow - окно редактирования групп тестов для ограничения доступа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Abakhri Samir
 * @version			09.07.2015
 */

/*NO PARSE JSON*/

sw.Promed.swAccessRightsTestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAccessRightsTestEditWindow',
	width: 640,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	listeners: {
		'hide': function() {
			var base_form = this.FormPanel.getForm();
			var TestPanel = this.findById('ARTEW_TestPanel');
			TestPanel.items.each(function(fieldSet){
				fieldSet.items.each(function(item) {
					base_form.items.removeKey(item.id);
				});
			});
			TestPanel.removeAll();
			this.FormPanel.initFields();
			this.syncShadow();
		}
	},

	doSave: function(options) {
		options = options || {};
		var base_form = this.FormPanel.getForm();
		var TestPanel = this.findById('ARTEW_TestPanel');

		if ( !base_form.isValid() ) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if ( TestPanel.items.getCount() == 0 ) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: lang['doljen_byit_ukazan_hotya_byi_odin_test'],
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		//loadMask.show();

		var values = base_form.getValues();
		var AccessRightsData = [];
		var DiagCodes = [];
		var index = 0;

		for (var num in this.DiagState) {
			var diag_state = this.DiagState[num];

			if (diag_state.status == 1) {
				for (var field in diag_state.origValues) {
					if (values[field+'_'+num] && values[field+'_'+num] != diag_state.origValues[field]) {
						diag_state.status = 2;
						break;
					}
				}
			}

			/*DiagCodes.push([]);
			DiagCodes[index].push(base_form.findField('UslugaComplex_id_'+num).getFieldValue('Diag_Code'));
			if (values['Diag_tid_'+num]!=undefined) {
				DiagCodes[index].push(base_form.findField('Diag_tid_'+num).getFieldValue('Diag_Code'));
			}
			index++;*/

			switch (diag_state.status) {
				case 0:
				case 2:
					AccessRightsData.push({
						AccessRightsTest_id: values['AccessRightsTest_id_'+num],
						UslugaComplex_id: values['UslugaComplex_id_'+num],
						RecordStatus_Code: diag_state.status
					});
					break;
				case 3:
					AccessRightsData.push({
						AccessRightsTest_id: diag_state.AccessRightsTest_id,
						UslugaComplex_id: null,
						RecordStatus_Code: diag_state.status
					});
					break;
			}

		}

		/*var str = '';
		DiagCodes.forEach(function(item, i) {
			if (i > 0) {str += ', '}
			if (item.length == 1) {
				str += item[0];
			} else if (item.length == 2) {
				str += item[0] + ' - '+ item[1];
			}
		});*/

		var data = {
			AccessRightsName_id: values.AccessRightsName_id,
			AccessRightsName_Name: values.AccessRightsName_Name,
			AccessRightsType_id: 4,
			allowIntersection: !Ext.isEmpty(options.allowIntersection)?options.allowIntersection:null,
			AccessRightsData: Ext.util.JSON.encode(AccessRightsData)
		};

		Ext.Ajax.request({
			params: data,
			url: this.FormPanel.url,
			failure: function() {
				loadMask.hide();
			},
			success: function(response) {
				loadMask.hide();
				var responseObj = Ext.util.JSON.decode(response.responseText);
				if (responseObj.success) {
					this.callback();
					this.hide();
				} else if (responseObj.Alert_Msg) {
					sw.swMsg.show({
						buttons: Ext.Msg.OKCANCEL,
						fn: function ( buttonId ) {
							if ( buttonId == 'ok' ) {
								switch ( responseObj.Alert_Code ) {
									case 1:
										options.allowIntersection = 1;
										break;
								}
								this.doSave(options);
							}
						}.createDelegate(this),
						msg: responseObj.Alert_Msg,
						title: lang['prodolijt_sohranenie']
					});
				} else if (responseObj.Error_Msg) {
					sw.swMsg.alert(lang['oshibka'], responseObj.Error_Msg);
				}
			}.createDelegate(this)
		});
	},

	addTestFieldSet: function(options) {
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var TestPanel = this.findById('ARTEW_TestPanel');

		this.DiagLastNum++;
		var num = this.DiagLastNum;

		this.DiagState[num] = {
			status: 0,
			AccessRightsTest_id: null,
			origValues: {UslugaComplex_id: null}
		};

		var config = {
			layout: 'form',
			id: 'DiagFieldSet_'+num,
			autoHeight: true,
			cls: 'AccessRigthsFieldSet',
			width: 570,
			items: []
		};

		if (options && options.isRange) {
			config.items = [{
				html: '<div id="DiagHeader_'+num+'" class="AccessRightsFieldSetHeader">' +
					'<div class="AccessRightsFieldSetBlock"></div>' +
					'<div class="AccessRightsFieldSetLabel">Диапазон тестов</div>' +
					'<div class="AccessRightsFieldSetLine" style="width: 361px;"></div>' +
					'</div>',
				style: 'margin-bottom: 5px;'
			}, {
				xtype: 'hidden',
				name: 'AccessRightsTest_id_'+num
			}, {
				fieldLabel: lang['diapazon_testov'],
				allowBlank: false,
				hiddenName: 'UslugaComplex_id',
				listWidth: 600,
				labelSeparator: '',
				xtype: 'swuslugacomplexallcombo',
				anchor: '98%'
			}];
		} else {
			config.items = [{
				html: '<div id="DiagHeader_'+num+'" class="AccessRightsFieldSetHeader">' +
					'<div class="AccessRightsFieldSetBlock"></div>' +
					'<div class="AccessRightsFieldSetLabel">Тест</div>' +
					'<div class="AccessRightsFieldSetLine" style="width: 430px;"></div>' +
					'</div>',
				style: 'margin-bottom: 5px;'
			}, {
				xtype: 'hidden',
				name: 'AccessRightsTest_id_'+num
			}, {
				allowBlank: false,
				anchor: '98%',
				fieldLabel: '',
				labelSeparator: '',
				hiddenName: 'UslugaComplex_id_'+num,
				xtype: 'swuslugacomplexnewcombo'
			}];
		}

		var DiagFieldSet = TestPanel.add(config);
		this.doLayout();
		this.syncSize();
		this.FormPanel.initFields();

		if (options && options.data) {
			this.DiagState[num].status = options.data.RecordStatus_Code;
			this.DiagState[num].AccessRightsTest_id = options.data.AccessRightsTest_id;
			this.DiagState[num].origValues.UslugaComplex_id = options.data.UslugaComplex_id;
			//this.DiagState[num].origValues.Diag_tid = options.data.Diag_tid;

			base_form.findField('AccessRightsTest_id_'+num).setValue(options.data.AccessRightsTest_id);
			this.setTestByName('UslugaComplex_id_'+num, options.data.UslugaComplex_id);
			/*if (options.isRange) {
				this.setDiagByName('Diag_tid_'+num, options.data.Diag_tid);
			}*/
		}

		var delButton = new Ext.Button({
			iconCls:'delete16',
			text: lang['udalit'],
			style: 'display: inline-block; vertical-align: middle;',
			handler: function()
			{
				if (wnd.DiagState[num].status != 0) {
					wnd.DiagState[num].status = 3;
				} else {
					delete wnd.DiagState[num];
				}

				DiagFieldSet.items.each(function(item) {
					wnd.FormPanel.getForm().items.removeKey(item.id);
				});
				TestPanel.remove(DiagFieldSet.id);
				wnd.doLayout();
				wnd.syncShadow();
				wnd.FormPanel.initFields();
			}
		});
		delButton.render('DiagHeader_'+num);
	},

	setTestByName: function(name, value) {
		var field = this.FormPanel.getForm().findField(name);

		field.getStore().load({
			params: { UslugaComplex_id: value },
			callback: function() {
				field.getStore().each(function(record) {
					if ( record.get('UslugaComplex_id') == value ) {
						field.setValue(value);
						field.fireEvent('select', field, record, 0);
						field.fireEvent('change', field, value);
					}
				});
			}
		});
	},

	show: function() {
		sw.Promed.swAccessRightsTestEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		this.DiagLastNum = 0;
		this.DiagState = {};
		this.callback = Ext.emptyFn;
		this.action = 'view';

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['gruppa_testov_dobavlenie']);
				this.enableEdit(true);

				loadMask.hide();
			break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['gruppa_testov_redaktirovanie']);
					this.enableEdit(true);
				} else {
					this.setTitle(lang['gruppa_testov_prosmotr']);
					this.enableEdit(false);
				}

				base_form.load({
					url: '/?c=AccessRightsTest&m=loadAccessRightsForm',
					params: {AccessRightsName_id: base_form.findField('AccessRightsName_id').getValue()},
					failure: function() {
						loadMask.hide();
						//
					},
					success: function(form, action) {
						if (action.result.data.AccessRightsData) {
							var TestData = Ext.util.JSON.decode(action.result.data.AccessRightsData);

							for (var i = 0; i < TestData.length; i++) {
								this.addTestFieldSet({
									data: TestData[i],
									isRange: false
									//isRange: Ext.isEmpty(TestData[i].Diag_tid) ? false : true
								});
							}
						}
						loadMask.hide();
					}.createDelegate(this)
				});
			break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'ARTEW_AccessRightsTestEditForm',
			url: '/?c=AccessRightsTest&m=saveAccessRights',
			bodyStyle: 'padding: 10px 5px 10px 20px;',
			labelAlign: 'right',

			items: [{
				xtype: 'hidden',
				name: 'AccessRightsName_id'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				fieldLabel: lang['nazvanie_gruppyi'],
				name: 'AccessRightsName_Name',
				width: 320
			}, {
				layout: 'form',
				id: 'ARTEW_TestPanel',
				cls: 'AccessRigthsPanel',
				autoHeight: true,
				items: []
			}, {
				layout: 'column',
				id: 'ARTEW_ButtonTestPanel',
				cls: 'AccessRigthsFieldSet',
				height: 25,
				style: 'margin-left: 100px; margin-top: 10px;',
				items: [/*{
					layout: 'form',
					items: [{
						xtype: 'button',
						iconCls:'add16',
						text: lang['dobavit_diapazon_diagnozov'],
						handler: function() {
							this.addTestFieldSet({isRange: true});
						}.createDelegate(this)
					}]
				},*/ {
					layout: 'form',
					style: 'margin-left: 10px',
					items: [{
						xtype: 'button',
						iconCls:'add16',
						text: lang['dobavit_test'],
						handler: function() {
							this.addTestFieldSet({isRange: false});
						}.createDelegate(this)
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: function() {
					//
				}
			}, [
				{name: 'AccessRightsName_id'},
				{name: 'AccessRightsName_Name'},
				{name: 'AccessRightsData'}
			]),
			keys: [{
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					id: 'ARTEW_ButtonSave',
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'ARTEW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swAccessRightsTestEditWindow.superclass.initComponent.apply(this, arguments);
	}
});