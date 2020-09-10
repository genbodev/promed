/**
* swPersonDopDispPlanRetryIncludeParamsWindow - параметры повторного включения
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

/*NO PARSE JSON*/
sw.Promed.swPersonDopDispPlanRetryIncludeParamsWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Параметры повторного включения',
	id: 'PersonDopDispPlanRetryIncludeParamsWindow',
	layout: 'border',
	maximizable: false,
	maximized: false,
	width: 470,
	height: 140,
	modal: true,
	codeRefresh: true,
	objectName: 'swPersonDopDispPlanRetryIncludeParamsWindow',
	objectSrc: '/jscore/Forms/Common/swPersonDopDispPlanRetryIncludeParamsWindow.js',
	returnFunc: function(owner) {},
	PersonDopDispPlan_id: null,
	action: 'add',
	show: function() {		
		sw.Promed.swPersonDopDispPlanRetryIncludeParamsWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = this.findById('PersonDopDispPlanRetryIncludeParamsForm').getForm();
		base_form.reset();
		this.ignore_period_check = null;

		if (arguments[0]['action']) {
			this.action = arguments[0]['action'];
		}

		if (arguments[0]['callback']) {
			this.returnFunc = arguments[0]['callback'];
		}
		
		if (arguments[0]['PersonDopDispPlan_id']) {
			this.PersonDopDispPlan_id = arguments[0]['PersonDopDispPlan_id'];
		} else {
			this.PersonDopDispPlan_id = null;
		}
		
		if (arguments[0]['DispClass_id']) {
			this.DispClass_id = arguments[0]['DispClass_id'];
		} else {
			this.DispClass_id = null;
		}

		if (arguments[0]['DispCheckPeriod_id']) {
			this.DispCheckPeriod_id = arguments[0]['DispCheckPeriod_id'];
		} else {
			this.DispCheckPeriod_id = null;
		}
		
		if (arguments[0]['PlanPersonList_ids']) {
			this.PlanPersonList_ids = arguments[0]['PlanPersonList_ids'];
		} else {
			this.PlanPersonList_ids = null;
		}

		base_form.findField('DispCheckPeriod_nid').getStore().baseParams = {
			PersonDopDispPlan_id: this.PersonDopDispPlan_id,
			DispClass_id: this.DispClass_id,
			isForRetryInclude: 1
		};
		base_form.findField('DispCheckPeriod_nid').getStore().load({
			callback: function() {
				var dcp_combo = base_form.findField('DispCheckPeriod_nid');
				var index = dcp_combo.getStore().indexOfId(win.DispCheckPeriod_id);
				if (index >= 0) {
					dcp_combo.setValue(win.DispCheckPeriod_id);
				}
			}
		});
	},
	doSave: function() 
	{
		var win = this;
		var form = this.findById('PersonDopDispPlanRetryIncludeParamsForm').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('PersonDopDispPlanRetryIncludeParamsForm'), { msg: "Подождите, идет сохранение..." });
		var base_form = win.findById('PersonDopDispPlanRetryIncludeParamsForm').getForm();
		var params = {};
		
		if (!form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('PersonDopDispPlanRetryIncludeParamsForm').getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=PersonDopDispPlan&m=retryIncludePlanPersonList',
			params: {
				PersonDopDispPlan_id: base_form.findField('DispCheckPeriod_nid').getFieldValue('PersonDopDispPlan_id'),
				ignore_period_check: this.ignore_period_check,
				PlanPersonList_ids: Ext.util.JSON.encode(win.PlanPersonList_ids)
			},
			callback: function(options, success, response) {
				if (success) {
					loadMask.hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if ( result.Alert_Msg && 'YesNo' == result.Error_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' && result.Error_Code == 112 ) {
									win.ignore_period_check = 1;
									win.doSave();
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: result.Alert_Msg,
							title: lang['prodoljit_sohranenie']
						});
					} else {
						win.hide();
						win.returnFunc();
					}
				}
				else{
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], 'При повторном включении возникли ошибки');
				}
			}
		});
	},

	initComponent: function() {
	
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			id:'PersonDopDispPlanRetryIncludeParamsForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 7px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 150,
			height: 200,
			items:
			[{
				name: 'PersonDopDispPlan_id',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				editable: false,
				width: 200,
				hiddenName: 'DispCheckPeriod_nid',
				fieldLabel: 'Период плана',
				lastQuery: '',
				typeCode: 'int',
				xtype: 'swbaselocalcombo',
				store: new Ext.data.JsonStore({
					key: 'DispCheckPeriod_id',
					autoLoad: false,
					fields: [
						{name:'DispCheckPeriod_id',type: 'int'},
						{name:'PersonDopDispPlan_id', type: 'int'},
						{name:'PeriodCap_id', type: 'int'},
						{name:'DispCheckPeriod_Year', type: 'int'},
						{name:'DispCheckPeriod_Name', type: 'string'}
					],
					url: '/?c=PersonDopDispPlan&m=getDispCheckPeriod'
				}),
				valueField: 'DispCheckPeriod_id',
				displayField: 'DispCheckPeriod_Name'
			}]
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [
				this.FormPanel
			],
			buttons:
			[{
				text: lang['sohranit'],
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this)
			},
			{
				text:'-'
			},
			HelpButton(this),
			{
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function()
				{
					this.hide();
				}.createDelegate(this)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.doSave();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swPersonDopDispPlanRetryIncludeParamsWindow.superclass.initComponent.apply(this, arguments);
	}
});