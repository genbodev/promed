/**
* swMorbusTubMDRPrescrWindow - окно редактирования "Лечебные мероприятия" специфики туберкулеза с МЛУ
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

sw.Promed.swMorbusTubMDRPrescrWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['lechebnyie_meropriyatiya'],
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
					that.findById('swMorbusTubMDRPrescrEditForm').getFirstInvalidEl().focus(true);
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
		sw.Promed.swMorbusTubMDRPrescrWindow.superclass.show.apply(this, arguments);		
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
		if ( arguments[0].MorbusTubMDRPrescr_id ) {
			this.MorbusTubPrescr_id = arguments[0].MorbusTubMDRPrescr_id;
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
				that.form.findField('MorbusTubPrescr_setDT').focus(true,200);
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
						that.form.findField('MorbusTubPrescr_setDT').focus(true,200);
						that.grid.loadData({
                            globalFilters:{
                                MorbusTubPrescr_id:that.MorbusTubPrescr_id,
                                Person_id: that.person_id
                            },
                            params: {
                                MorbusTubPrescr_id:that.MorbusTubPrescr_id,
                                Person_id: that.person_id
                            },
                            noFocusOnLoad:true
                        });
                        var bf = that.form;
                        var ls_field = bf.findField('LpuSection_id');
                        ls_field.getStore().removeAll();
                        ls_field.lastQuery = '';
                        ls_field.getStore().baseParams.Lpu_id = bf.findField('Lpu_id').getValue();
                        ls_field.getStore().load({callback: function(){
                            ls_field.setValue(ls_field.getValue());
                        }});
                        return true;
					},
					url:'/?c=MorbusTub&m=loadMorbusTubMDRPrescr'
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
				if (me.form.findField('MorbusTubPrescr_id').getValue()>0) {
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
			title:lang['grafik_ispolneniya_naznacheniya_protsedur'],
			object: 'MorbusTubPrescrTimetable',
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
				{name:'action_add', func: function() { me.beforeAddAction(); }},
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
			id: 'swMorbusTubMDRPrescrEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 240,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusTubPrescr_id', xtype: 'hidden', value: null},
                {name: 'MorbusTub_id', xtype: 'hidden', value: null},
                {name: 'MorbusTubMDR_id', xtype: 'hidden', value: null},
                {name: 'Evn_id', xtype: 'hidden', value: null},
                {name: 'Person_id', xtype: 'hidden', value: null},
                {name: 'PersonWeight_id', xtype: 'hidden', value: null},
				{
                    fieldLabel: lang['data_nachala'],
                    name: 'MorbusTubPrescr_setDT',
                    allowBlank: false,
                    xtype: 'swdatefield',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
                    fieldLabel: lang['data_okonchaniya'],
                    name: 'MorbusTubPrescr_endDate',
                    allowBlank: true,
                    xtype: 'swdatefield',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
                    fieldLabel: lang['preparat'],
                    sortField:'TubDrug_Code',
                    comboSubject: 'TubDrug',
					anchor:'100%',
					hiddenName: 'TubDrug_id',
					xtype: 'swtubcommonsprcombo',
                    isMDR: true,
					allowBlank: false
				}, {
					fieldLabel: lang['dozirovka'],
					name: 'MorbusTubPrescr_DoseDay',
					xtype: 'textfield',
                    maxLength: 30
				}, {
                    fieldLabel: lang['prinyato_doz'],
                    name: 'MorbusTubPrescr_DoseTotal',
                    xtype: 'textfield',
                    maxLength: 30
                }, {
                    fieldLabel: lang['propuscheno_doz'],
                    name: 'MorbusTubPrescr_DoseMiss',
                    xtype: 'textfield',
                    maxLength: 30
                }, {
                    fieldLabel: lang['naznacheno_dney_lecheniya'],
                    name: 'MorbusTubPrescr_SetDay',
                    xtype: 'textfield',
                    maxLength: 30
                }, {
                    fieldLabel: lang['propuscheno_dney_lecheniya'],
                    name: 'MorbusTubPrescr_MissDay',
                    xtype: 'textfield',
                    maxLength: 30
                }, {
                    fieldLabel: lang['ves_na_nachalo_lecheniya'],
                    name: 'PersonWeight_Weight',
                    xtype: 'numberfield',
                    autoCreate: {tag: "input", size:14, autocomplete: "off"},
                    allowDecimals: true,
                    allowNegative: false
                }, {
                    hiddenName: 'Lpu_id',
                    allowBlank: false,
                    lastQuery: '',
                    listWidth: 500,
                    anchor:'100%',
                    xtype: 'swlpucombo',
                    listeners: {
                        change: function(field, newValue) {
                            var bf = me.form;
                            var ls_field = bf.findField('LpuSection_id');
                            ls_field.getStore().removeAll();
                            ls_field.setValue(null);
                            ls_field.lastQuery = '';
                            ls_field.getStore().baseParams.Lpu_id = newValue;
                            ls_field.getStore().load();
                        }
                    }
                }, {
                    fieldLabel: lang['otdelenie'],
                    hiddenName: 'LpuSection_id',
                    lastQuery: '',
                    listWidth: 500,
                    anchor:'100%',
                    xtype: 'swlpusectioncombo'
                }, {
                    fieldLabel: lang['primechanie'],
                    name: 'MorbusTubPrescr_Comment',
                    xtype: 'textfield',
                    anchor:'100%',
                    maxLength: 100
				}],
                /*
                 – Вес на начало лечения, число
                 – МО, комбобокс, выбор из справочника МО, обязательно для выбора
                 – Отделение, выбор из справочника отделений выбранной МО
                 */
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusTubPrescr_id'},
				{name: 'MorbusTub_id'},
				{name: 'MorbusTubMDR_id'},
                {name: 'MorbusTubPrescr_setDT'},
                {name: 'MorbusTubPrescr_endDate'},
                {name: 'TubDrug_id'},
                {name: 'Lpu_id'},
                {name: 'LpuSection_id'},
                {name: 'Person_id'},
                {name: 'PersonWeight_id'},
                {name: 'PersonWeight_Weight'},
                {name: 'MorbusTubPrescr_SetDay'},
                {name: 'MorbusTubPrescr_MissDay'},
                {name: 'MorbusTubPrescr_DoseMiss'},
				{name: 'MorbusTubPrescr_DoseDay'},
				{name: 'MorbusTubPrescr_Comment'},
                {name: 'MorbusTubPrescr_DoseTotal'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusTubMDRPrescr'
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
					me.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.InformationPanel,form, this.grid]
		});
		sw.Promed.swMorbusTubMDRPrescrWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusTubMDRPrescrEditForm').getForm();
	}	
});