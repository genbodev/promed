/**
* swMorbusTubMDRStudyResultWindow - окно редактирования "Результаты исследований" специфики туберкулеза с МЛУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      TubRegistry
* @access       public
* @copyright    Copyright (c) 2009-1014 Swan Ltd.
* @author       A. Permyakov
* @version      12/2014    
*/

sw.Promed.swMorbusTubMDRStudyResultWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['rezultatyi_issledovaniy'],
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
					that.findById('swMorbusTubMDRStudyResultEditForm').getFirstInvalidEl().focus(true);
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
		var params = {};
		params.action = that.action;
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
					var id = action.result.MorbusTubMDRStudyResult_id;
					if (id) {
						if (!onlySave || (onlySave!==1)) {
							that.callback(that.owner, id);
							that.hide();
						}
						else
						{
							that.form.findField('MorbusTubMDRStudyResult_id').setValue(id);
							that.MorbusTubMDRStudyResult_id = id;
							that.grid.params = 
							{
								MorbusTubMDRStudyResult_id: id,
								Person_id: that.Person_id
							};
							that.grid.gFilters = 
							{
								MorbusTubMDRStudyResult_id: id,
								Person_id: that.Person_id
							};
							that.action = 'edit';
							that.grid.run_function_add = false;
							that.grid.getAction('action_add').execute();
							that.doCancel = function() {
								var loadMask = new Ext.LoadMask(that.getEl(), {msg: "Отмена внесенных изменений..."});
								loadMask.show();
								Ext.Ajax.request({
									failure: function(response, options) {
										loadMask.hide();
									},
									params: {
										MorbusTubMDRStudyResult_id: that.MorbusTubMDRStudyResult_id
									},
									success: function(response, options) {
										loadMask.hide();
										that.hide();
									},
									url: '/?c=MorbusTub&m=deleteMorbusTubMDRStudyResult'
								});
							};
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
		sw.Promed.swMorbusTubMDRStudyResultWindow.superclass.show.apply(this, arguments);
		if ( !arguments[0] || !arguments[0].formParams || !arguments[0].formParams.Person_id) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		this.callback = Ext.emptyFn;
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		this.action = arguments[0].action || '';
		this.ARMType = arguments[0].ARMType || null;
		this.owner = arguments[0].owner || null;
		this.MorbusTubMDRStudyResult_id = arguments[0].MorbusTubMDRStudyResult_id || null;
		
		this.doCancel = function() {
			that.hide();
		};

		this.form.reset();
		this.grid.removeAll({clearAll: true});
		switch (arguments[0].action) {
			case 'add':
				this.setTitle(this.titleWin+lang['_dobavlenie']);
				this.setFieldsDisabled(false);
				this.grid.setParam('Person_id', arguments[0].formParams.person_id, false);
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
		that.Person_id = arguments[0].formParams.Person_id;
		that.grid.params = 
		{
			MorbusTubMDRStudyResult_id: that.MorbusTubMDRStudyResult_id,
			Person_id: that.Person_id
		};
		that.grid.gFilters = 
		{
			MorbusTubMDRStudyResult_id: that.MorbusTubMDRStudyResult_id,
			Person_id: that.Person_id
		};
		this.getLoadMask().show();
		switch (arguments[0].action) {
			case 'add':
				that.form.setValues(arguments[0].formParams);
				that.InformationPanel.load({
					Person_id: that.Person_id
				});
				that.form.findField('MorbusTubMDRStudyResult_setDT').focus(true,200);
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
						MorbusTubMDRStudyResult_id: that.MorbusTubMDRStudyResult_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: that.Person_id
						});
						that.form.findField('MorbusTubMDRStudyResult_setDT').focus(true,200);
						that.grid.loadData({
							globalFilters:{
								MorbusTubMDRStudyResult_id:that.MorbusTubMDRStudyResult_id,
								Person_id: that.Person_id
							},
							params: {
								MorbusTubMDRStudyResult_id:that.MorbusTubMDRStudyResult_id,
								Person_id: that.Person_id
							},
							noFocusOnLoad:true
						});
						return true;
					},
					url:'/?c=MorbusTub&m=loadMorbusTubMDRStudyResult'
				});
			break;
		}
		return true;
	},
	initComponent: function() {
		var me = this;
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.beforeAddAction = function() {
			if (me.form.isValid()) {
				if (me.form.findField('MorbusTubMDRStudyResult_id').getValue()>0) {
					me.grid.setParam('MorbusTubMDRStudyResult_id', me.form.findField('MorbusTubMDRStudyResult_id').getValue(), false);
					me.grid.run_function_add = false;
					me.grid.getAction('action_add').execute();
				} else {
					me.submit(1);
				}
			}
			return false;
		};

		this.grid = new sw.Promed.ViewFrame(
		{
			title:lang['test_na_lekarstvennuyu_chuvstvitelnost'],
			object: 'MorbusTubMDRStudyDrugResult',
            obj_isEvn: false,
			editformclassname: 'swMorbusTubMDRStudyDrugResultWindow',
			dataUrl: '/?c=MorbusTub&m=loadMorbusTubMDRStudyDrugResult',
			autoLoadData: false,
			stringfields:
			[
				{name: 'MorbusTubMDRStudyDrugResult_id', type: 'int', header: 'ID', key: true},
				{name: 'MorbusTubMDRStudyResult_id', type: 'int', hidden: true, isparams: true},
				{name: 'TubDiagResultType_id', type: 'int', hidden: true},
				{name: 'TubDrug_id', type: 'int', hidden: true},
				{name: 'MorbusTubMDRStudyDrugResult_setDT', type: 'date', header: lang['data']},
				{name: 'TubDrug_Name', type: 'string', header: lang['preparat']},
				{id: 'autoexpand', name: 'TubDiagResultType_Name',  type: 'string', header: lang['rezultat']}
			],
			actions:
			[
				{name:'action_add', func: function() { me.beforeAddAction(); }},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print', visible: false}
			],
			//focusOn: {name:'lrOk',type:'button'},
			focusPrev: {name:'MorbusTubMDRStudyResult_Comment',type:'field'},
			focusOnFirstLoad: false
		});
		
		var form = new Ext.form.FormPanel({
			autoHeight: true,
			id: 'swMorbusTubMDRStudyResultEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusTubMDRStudyResult_id', xtype: 'hidden', value: null},
				{name: 'MorbusTubMDR_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['mesyats_lecheniya'],
					name: 'MorbusTubMDRStudyResult_Month',
					xtype: 'textfield',
					maxLength: 10
				}, {
					title: lang['rezultatyi_bakteriologicheskogo_issledovaniya'],
					xtype: 'fieldset',
					autoHeight: true,
					style: 'padding: 0; padding-left: 10px',
					items: [{
						fieldLabel: lang['data_sbora'],
						name: 'MorbusTubMDRStudyResult_setDT',
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}, {
						fieldLabel: lang['laboratornyiy_nomer'],
						name: 'MorbusTubMDRStudyResult_NumLab',
						xtype: 'textfield',
						maxLength: 10
					}, {
						fieldLabel: lang['mikroskopiya'],
						hiddenName: 'TubMicrosResultType_id',
						anchor:'100%',
						sortField:'TubMicrosResultType_Code',
						comboSubject: 'TubMicrosResultType',
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: lang['mgm'],
						hiddenName: 'TubHistolResultType_id',
						anchor:'100%',
						sortField:'TubHistolResultType_Code',
						comboSubject: 'TubHistolResultType',
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: lang['kultura'],
						hiddenName: 'TubSeedResultType_id',
						anchor:'100%',
						sortField:'TubSeedResultType_Code',
						comboSubject: 'TubSeedResultType',
						isMDR: true,
						xtype: 'swtubcommonsprcombo'
					}]
				}, {
					fieldLabel: lang['rezultat_rentgenologicheskogo_obsledovaniya'],
					hiddenName: 'TubXrayResultType_id',
					anchor:'100%',
					sortField:'TubXrayResultType_Code',
					comboSubject: 'TubXrayResultType',
					isMDR: true,
					xtype: 'swtubcommonsprcombo'
				}, {
					fieldLabel: lang['primechanie'],
					name: 'MorbusTubMDRStudyResult_Comment',
					xtype: 'textfield',
					anchor:'100%',
					maxLength: 100
				}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusTubMDRStudyResult_id'},
				{name: 'MorbusTubMDR_id'},
				{name: 'MorbusTubMDRStudyResult_setDT'},
				{name: 'TubSeedResultType_id'},
				{name: 'TubHistolResultType_id'},
				{name: 'TubMicrosResultType_id'},
				{name: 'TubXrayResultType_id'},
				{name: 'MorbusTubMDRStudyResult_Month'},
				{name: 'MorbusTubMDRStudyResult_NumLab'},
				{name: 'MorbusTubMDRStudyResult_Comment'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusTubMDRStudyResult'
		});
		Ext.apply(this, {
			buttons:
			[{
				handler: function() 
				{
					me.doSave();
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
					me.doCancel();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.InformationPanel,form, this.grid]
		});
		sw.Promed.swMorbusTubMDRStudyResultWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusTubMDRStudyResultEditForm').getForm();
	}	
});