/**
* swEvnDirectionTubWindow - окно редактирования "Направление на проведение микроскопических исследований на туберкулез"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A. Markoff
* @version      2012/11
* @comment      
*/

sw.Promed.swEvnDirectionTubWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'form',
	modal: true,
	width: 670,
	titleWin: lang['napravlenie_na_provedenie_mikroskopicheskih_issledovaniy_na_tuberkulez'],
	autoHeight: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var that = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					that.findById('swEvnDirectionTubEditForm').getFirstInvalidEl().focus(true);
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
	submit: function(onlySave) {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = that.action;
		params.Server_id = that.InformationPanel.getFieldValue('Server_id');
		params.PersonEvn_id = that.InformationPanel.getFieldValue('PersonEvn_id');
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
				if (action.result) {
					var id = action.result.EvnDirectionTub_id;
					if (id) {
						if (!onlySave || (onlySave!==1)) {
							that.callback(that.owner, id);
							that.hide();
						}
						else
						{
							that.form.findField('EvnDirectionTub_id').setValue(id);
							that.grid.params = 
							{
								EvnDirectionTub_id: id,
								Person_id: that.person_id
							};
							that.grid.gFilters = 
							{
								EvnDirectionTub_id: id,
								Person_id: that.person_id
							};
							that.action = 'edit';
							that.grid.run_function_add = false;
							that.grid.getAction('action_add').execute();
						}
					}
				}
			}
		});
	},
	setFieldsDisabled: function(d)
	{
		var form = this;
		this.form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() {
		var that = this;
		sw.Promed.swEvnDirectionTubWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.EvnDirectionTub_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].EvnDirectionTub_id ) {
			this.EvnDirectionTub_id = arguments[0].EvnDirectionTub_id;
		}

		this.form.reset();
		this.grid.removeAll({clearAll: true});
		switch (arguments[0].action) {
			case 'add':
				this.setTitle(this.titleWin+lang['_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(this.titleWin+lang['_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(this.titleWin+lang['_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		that.person_id = arguments[0].formParams.Person_id;
		this.getLoadMask().show();
		switch (arguments[0].action) {
			case 'add':
				that.form.setValues(arguments[0].formParams);
				that.InformationPanel.load({
					Person_id: that.person_id
				});
				that.form.findField('MedPersonal_id').getStore().load({
					callback: function() {
						that.form.findField('MedPersonal_id').setValue(that.form.findField('MedPersonal_id').getValue());
						that.form.findField('MedPersonal_id').fireEvent('change', that.form.findField('MedPersonal_id'), that.form.findField('MedPersonal_id').getValue());
						that.form.findField('MedPersonal_lid').getStore().load({
							callback: function() {
								that.form.findField('MedPersonal_lid').setValue(that.form.findField('MedPersonal_lid').getValue());
								that.form.findField('MedPersonal_lid').fireEvent('change', that.form.findField('MedPersonal_lid'), that.form.findField('MedPersonal_lid').getValue());
							}.createDelegate(this)
						});
					}.createDelegate(this)
				});
				that.grid.setParam('Person_id', that.person_id, false);
				that.form.findField('EvnDirectionTub_setDT').focus(true,200);
				that.getLoadMask().hide();
			break;
			case 'edit':
			case 'view':
				
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						that.getLoadMask().hide();
					},
					params:{
						EvnDirectionTub_id: that.EvnDirectionTub_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: that.person_id
						});
						that.form.findField('MedPersonal_id').getStore().load({
							callback: function() {
								that.form.findField('MedPersonal_id').setValue(that.form.findField('MedPersonal_id').getValue());
								that.form.findField('MedPersonal_id').fireEvent('change', that.form.findField('MedPersonal_id'), that.form.findField('MedPersonal_id').getValue());
								that.form.findField('MedPersonal_lid').getStore().load({
									callback: function() {
										that.form.findField('MedPersonal_lid').setValue(that.form.findField('MedPersonal_lid').getValue());
										that.form.findField('MedPersonal_lid').fireEvent('change', that.form.findField('MedPersonal_lid'), that.form.findField('MedPersonal_lid').getValue());
									}.createDelegate(this)
								});
							}.createDelegate(this)
						}); 
						that.form.findField('EvnDirectionTub_setDT').focus(true,200);
						that.grid.loadData({globalFilters:{EvnDirectionTub_id:that.EvnDirectionTub_id, Person_id: that.person_id}, params: {EvnDirectionTub_id:that.EvnDirectionTub_id, Person_id: that.person_id}, noFocusOnLoad:true})
					},
					url:'/?c=MorbusTub&m=loadEvnDirectionTub'
				});				
			break;	
		}
	},
	initComponent: function() {
		
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.MainRecordAdd = function() {
		
			if (this.form.isValid()) {
				
				if (this.form.findField('EvnDirectionTub_id').getValue()>0) {
					this.grid.run_function_add = false;
					this.grid.getAction('action_add').execute();
				} else {
					this.submit(1);
				}
				
			}
			return false;
		}.createDelegate(this);

		this.grid = new sw.Promed.ViewFrame(
		{
			title:lang['rezultatyi_mikroskopicheskih_issledovaniy'],
			object: 'TubMicrosResult',
            obj_isEvn: false,
			editformclassname: 'swTubMicrosResultWindow',
			dataUrl: '/?c=MorbusTub&m=loadTubMicrosResult',
			autoLoadData: false,
			stringfields:
			[
				{name: 'TubMicrosResult_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnDirectionTub_id', type: 'int', hidden: true, isparams: true},
				{name: 'TubMicrosResult_MicrosDT', type: 'date', header: lang['data_issledovaniya']},
				{name: 'TubMicrosResult_Num', type: 'string', header: lang['obrazets']},
				{name: 'TubMicrosResultType_Name', type: 'string', header: lang['rezultat'], autoexpand: true},
				{name: 'TubMicrosResult_Comment', type: 'string', header: lang['primechanie'], width: 120}
			],
			actions:
			[
				{name:'action_add', func: function() { this.MainRecordAdd() }.createDelegate(this)},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print', visible: false}
			],
			//focusOn: {name:'lrOk',type:'button'},
			focusPrev: {name:'EvnDirectionTub_ResDT',type:'field'},
			focusOnFirstLoad: false
		});
		
		var form = new Ext.form.FormPanel({
			autoHeight: true,
			id: 'swEvnDirectionTubEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 240,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'EvnDirectionTub_id', xtype: 'hidden', value: null},
				{name: 'MorbusTub_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['data_napravleniya'],
					name: 'EvnDirectionTub_setDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['diagnosticheskiy_material'],
					anchor:'100%',
					hiddenName: 'TubDiagnosticMaterialType_id',
					xtype: 'swcommonsprcombo',
					typeCode: 'int',
					allowBlank:false,
					sortField:'TubDiagnosticMaterialType_Code',
					comboSubject: 'TubDiagnosticMaterialType'
				}, {
					fieldLabel: lang['tsel_issledovaniya'],
					anchor:'100%',
					hiddenName: 'TubTargetStudyType_id',
					xtype: 'swcommonsprcombo',
					typeCode: 'int',
					allowBlank:false,
					sortField:'TubTargetStudyType_Code',
					comboSubject: 'TubTargetStudyType'
				}, {
					fieldLabel: lang['regionalnyiy_reg_№_patsienta'],
					anchor:'100%',
					name: 'EvnDirectionTub_PersonRegNum',
					xtype: 'textfield',
					allowBlank:true
				}, {
					fieldLabel: lang['napravivshiy_medrabotnik'],
					hiddenName: 'MedPersonal_id',
					anchor: '100%',
					xtype: 'swmedpersonalcombo',
					allowBlank: false
				}, {
					fieldLabel: lang['medrabotnik_sobravshiy_obraztsyi'],
					hiddenName: 'MedPersonal_lid',
					anchor: '100%',
					xtype: 'swmedpersonalcombo',
					allowBlank: false
				}, {
					fieldLabel: lang['lab_№_issledovaniya'],
					anchor:'100%',
					name: 'EvnDirectionTub_NumLab',
					autoCreate: {tag: "input", size:14, maxLength: "6", autocomplete: "off"},
					xtype: 'textfield',
					allowBlank:true
				}, {
					fieldLabel: lang['data_vyidachi_rezultata'],
					name: 'EvnDirectionTub_ResDT',
					allowBlank:true,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'EvnDirectionTub_id'},
				{name: 'MorbusTub_id'},
				{name: 'EvnDirection_id'},
				{name: 'EvnDirectionTub_setDT'},
				{name: 'EvnDirectionTub_PersonRegNum'},
				{name: 'TubDiagnosticMaterialType_id'},
				{name: 'EvnDirectionTub_OtherMeterial'},
				{name: 'TubTargetStudyType_id'},
				{name: 'EvnDirectionTub_NumLab'},
				{name: 'MedPersonal_lid'},
				{name: 'MedPersonal_id'},
				{name: 'EvnDirectionTub_ResDT'}
			]),
			url: '/?c=MorbusTub&m=saveEvnDirectionTub'
		});
		Ext.apply(this, {
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
			items:[this.InformationPanel,form, this.grid]
		});
		sw.Promed.swEvnDirectionTubWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swEvnDirectionTubEditForm').getForm();
	}	
});