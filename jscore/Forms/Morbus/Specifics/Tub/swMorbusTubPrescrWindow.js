/**
* swMorbusTubPrescrWindow - окно редактирования "Лекарственные назначения"
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

sw.Promed.swMorbusTubPrescrWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['Grafic_ispolneniya_naznacheniya_prozedur'],
	autoHeight: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var that = this;
		if (Ext.isEmpty(this.form.findField('Drug_id').getValue()) && Ext.isEmpty(this.form.findField('TubDrug_id').getValue())) {			
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['pole_preparat_ili_preparat_rls_doljno_byit_zapolneno'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					that.findById('swMorbusTubPrescrEditForm').getFirstInvalidEl().focus(true);
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
					var id = action.result.MorbusTubPrescr_id;
					if (id) {
						if (!onlySave || (onlySave!==1)) {
							that.callback(that.owner, id);
							that.hide();
						}
						else
						{
							that.form.findField('MorbusTubPrescr_id').setValue(id);
							that.grid.params = 
							{
								MorbusTubPrescr_id: id,
								Person_id: that.person_id
							};
							that.grid.gFilters = 
							{
								MorbusTubPrescr_id: id,
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
		sw.Promed.swMorbusTubPrescrWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MorbusTubPrescr_id = null;
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
		if ( arguments[0].MorbusTubPrescr_id ) {
			this.MorbusTubPrescr_id = arguments[0].MorbusTubPrescr_id;
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
				that.grid.setParam('Person_id', that.person_id, false);
				that.form.findField('TubStageChemType_id').focus(true,200);
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
						MorbusTubPrescr_id: that.MorbusTubPrescr_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: that.person_id
						});
						var combo = that.form.findField('Drug_id');
						var drug_id = combo.getValue();
						combo.getStore().baseParams.Drug_id=drug_id;
						combo.getStore().baseParams.query=null;
						combo.getStore().load({
							params: {Drug_id: drug_id},
							callback: function() {
								this.setValue(drug_id);
								combo.getStore().baseParams.Drug_id = null;
							}.createDelegate(combo)
						});
						that.form.findField('TubStageChemType_id').focus(true,200);
						that.grid.loadData({globalFilters:{MorbusTubPrescr_id:that.MorbusTubPrescr_id, Person_id: that.person_id}, params: {MorbusTubPrescr_id:that.MorbusTubPrescr_id, Person_id: that.person_id}, noFocusOnLoad:true})
					},
					url:'/?c=MorbusTub&m=loadMorbusTubPrescr'
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
				
				if (this.form.findField('MorbusTubPrescr_id').getValue()>0) {
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
			title:lang['grafik_ispolneniya_naznacheniya'],
			object: 'MorbusTubPrescrTimetable',
            obj_isEvn: false,
			editformclassname: 'swMorbusTubPrescrTimetableWindow',
			dataUrl: '/?c=MorbusTub&m=loadMorbusTubPrescrTimetable',
			autoLoadData: false,
			stringfields:
			[
				{name: 'MorbusTubPrescrTimetable_id', type: 'int', header: 'ID', key: true},
				{name: 'MorbusTubPrescr_id', type: 'int', hidden: true, isparams: true},
				{name: 'MorbusTubPrescrTimetable_IsExec', type: 'int', hidden: true},
				{name: 'MorbusTubPrescrTimetable_setDT', type: 'date', header: lang['data']},
				{name: 'MorbusTubPrescrTimetable_IsExec_Name', type: 'string', header: lang['vyipolnena']},
				{name: 'MedPersonal_id', type: 'int', hidden: true, isparams:true},
				{id: 'autoexpand', name: 'MedPersonal_Fio',  type: 'string', header: lang['vrach_vyipolnivshiy_naznachenie']}
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
			focusPrev: {name:'MorbusTubPrescr_Schema',type:'field'},
			focusOnFirstLoad: false
		});
		
		var form = new Ext.form.FormPanel({
			autoHeight: true,
			id: 'swMorbusTubPrescrEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 240,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusTubPrescr_id', xtype: 'hidden', value: null},
				{name: 'MorbusTub_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['faza_himioterapii'],
					anchor:'100%',
					hiddenName: 'TubStageChemType_id',
					xtype: 'swcommonsprcombo',
					typeCode: 'int',
					allowBlank:false,
					sortField:'TubStageChemType_Code',
					comboSubject: 'TubStageChemType',
					loadParams: {params: {where: 'where TubStageChemType_id in (3,5)'}}
				}, {
                    fieldLabel: lang['data_naznacheniya'],
                    name: 'MorbusTubPrescr_setDT',
                    allowBlank:false,
                    xtype: 'swdatefield',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
                    fieldLabel: lang['data_otmenyi'],
                    name: 'MorbusTubPrescr_endDate',
                    allowBlank:true,
                    xtype: 'swdatefield',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
                    fieldLabel: lang['preparat'],
                    sortField:'TubDrug_Code',
                    comboSubject: 'TubDrug',
                    anchor:'100%',
                    hiddenName: 'TubDrug_id',
                    xtype: 'swtubcommonsprcombo',
                    isMDR: false
				}, {
					hiddenName: 'Drug_id',
					displayField: 'Drug_Name',
					valueField: 'Drug_id',
					fieldLabel: lang['preparat_rls'],
					xtype: 'swbaseremotecombo',
                    anchor: '100%',
					triggerAction: 'none',
					trigger1Class: 'x-form-search-trigger',
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'Drug_id'
						}, [
							{name: 'Drug_id', type:'int'},
							{name: 'Drug_Code', type:'int'},
							{name: 'DrugForm_Name', type: 'string'},
							{name: 'Drug_Name', type: 'string'}
						]),
						url: '/?c=RlsDrug&m=loadDrugSimpleList'
					}),
					onTrigger1Click: function() 
					{
						if (this.disabled)
							return false;
						var combo = this;
						// Именно для этого комбо логика несколько иная 
						if (!this.formList)
						{
							if (Ext.getCmp('DrugPrepWinSearch')) {
								this.formList = Ext.getCmp('DrugPrepWinSearch');
							} else {
								this.formList = new sw.Promed.swListSearchWindow(
								{
									//params: {
										title: lang['poisk_medikamenta'],
										id: 'DrugPrepWinSearch',
										object: 'Drug',
										modal: false,
										//maximizable: true,
										maximized: true,
										paging: true,
										prefix: 'dprws',
										dataUrl: '/?c=Farmacy&m=loadDrugMultiList',
										columns: true,
										stringfields:
										[
											{name: 'Drug_id', key: true},
											{name: 'DrugPrepFas_id', hidden: true},
											{name: 'DrugTorg_Name', autoexpand: true, header: lang['torgovoe_naimenovanie'], isfilter:true, columnWidth: '.4'},
											{name: 'DrugForm_Name', header: lang['forma_vyipuska'], width: 140, isfilter:true, columnWidth: '.15'},
											{name: 'Drug_Dose', header: lang['dozirovka'], width: 100, isfilter:true, columnWidth: '.15'},
											{name: 'Drug_Fas', header: lang['fasovka'], width: 100},
											{name: 'Drug_PackName', header: lang['upakovka'], width: 100},
											{name: 'Drug_Firm', header: lang['proizvoditel'], width: 200, isfilter:true, columnWidth: '.3'},
											{name: 'Drug_Ean', header: 'EAN', width: 100},
											{name: 'Drug_RegNum', header: lang['ru'], width: 120}
										],
										useBaseParams: true
									//}
								});
							}
						}
						var params = (combo.getStore().baseParams)?combo.getStore().baseParams:{};
						params.Drug_id = null;
						combo.collapse();
						this.collapse();
						this.formList.show(
						{
							params:params,
							onSelect: function(data) 
							{
								combo.hasFocus = false;
								combo.getStore().baseParams.Drug_id=data['Drug_id'];
								combo.getStore().baseParams.query=null;
								combo.getStore().load({
									params: {Drug_id: data['Drug_id']},
									callback: function() {
										this.setValue(data['Drug_id']);
										combo.hasFocus = true;
										combo.getStore().baseParams.Drug_id=null;
									}.createDelegate(combo)
								});
							}.createDelegate(this), 
							onHide: function() 
							{
								this.focus(false);
							}.createDelegate(this)
						});
						return false;
					}
				}, {
					fieldLabel: lang['sutochnaya_doza'],
					name: 'MorbusTubPrescr_DoseDay',
					xtype: 'numberfield',
					minValue: 0,
					autoCreate: {tag: "input", size:14, maxLength: "30", autocomplete: "off"},
					allowDecimals: true,
					allowNegative: false,
					allowBlank:true,
                    listeners: {
                        change: function(field, newValue) {
                            var bf = this.form;
                            var grid = this.grid.getGrid();
                            var totaldose_field = bf.findField('MorbusTubPrescr_DoseTotal');
                            if (grid.getStore().getCount() > 0) {
                                totaldose_field.setValue(newValue*grid.getStore().getCount());
                            } else {
                                totaldose_field.setValue(newValue);
                            }
                        }.createDelegate(this)
                    }
				}, {
					fieldLabel: lang['shema'],
					name: 'MorbusTubPrescr_Schema',
					xtype: 'textfield',
					autoCreate: {tag: "input", size:14, maxLength: "40", autocomplete: "off"},
					maxLength: 40,
                    anchor: '100%',
					allowBlank:true
                }, {
                    fieldLabel: lang['obschee_kolichestvo_doz'],
                    name: 'MorbusTubPrescr_DoseTotal',
                    xtype: 'numberfield',
                    minValue: 0,
                    autoCreate: {tag: "input", size:14, maxLength: "30", autocomplete: "off"},
                    allowDecimals: true,
                    allowNegative: false,
                    allowBlank:true
				}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusTubPrescr_id'},
				{name: 'MorbusTub_id'},
				{name: 'TubStageChemType_id'},
                {name: 'MorbusTubPrescr_setDT'},
                {name: 'MorbusTubPrescr_endDate'},
				{name: 'TubDrug_id'},
				{name: 'Drug_id'},
				{name: 'MorbusTubPrescr_DoseDay'},
				{name: 'MorbusTubPrescr_Schema'},
                {name: 'MorbusTubPrescr_DoseTotal'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusTubPrescr'
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
		sw.Promed.swMorbusTubPrescrWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusTubPrescrEditForm').getForm();
        this.grid.getGrid().getStore().on('load', function(store, records) {
            if (this.action != 'view') {
                var bf = this.form;
                var daydose_field = bf.findField('MorbusTubPrescr_DoseDay');
                var totaldose_field = bf.findField('MorbusTubPrescr_DoseTotal');
                if (records.length > 0) {
                    totaldose_field.setValue(daydose_field.getValue()*records.length);
                } else {
                    totaldose_field.setValue(daydose_field.getValue());
                }
            }
        }, this);
	}	
});