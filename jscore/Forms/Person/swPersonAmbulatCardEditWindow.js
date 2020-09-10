/**
 * swPersonAmbulatCardEditWindow - окно.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @version      08.08.2011
 */
sw.Promed.swPersonAmbulatCardEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	title:"Амбулаторная карта",
	width: 800,
	minHeight:435,
	height:435,
	maxHeight:400,
	id: 'swPersonAmbulatCardEditWindow',
	listeners: {
		hide: function () {
			this.onHide();
		}
	},
	layout: 'border',
	maximizable: true,
	minHeight: 435,
	minWidth: 700,
	modal: false,
	doSave: function (callback,ignoreUniq) {
		var form = this.findById('PersonAmbulatCardEditForm');
		if (!form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit(callback,ignoreUniq);
		return true;
	},
	getPersonCardCode: function(lpu_id) {
		
		if ( this.isDMS )
			return true;
		
		var wnd = this;
		var form = this.form;
		var params = {
			Person_id: form.findField('Person_id').getValue()
		};
		params.Lpu_id = form.findField('Lpu_id').getValue()
		
		if ( this.action == 'add' && this.attachType == 1 )
		{
			params.CheckFond = 1;
		}
		
		Ext.Ajax.request({
			params: params,
        	callback: function(options, success, response) {
    	        if (success && response.responseText != '')
        	    {
            		var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success == true)
        	      		var person_card_code = response_obj.PersonCard_Code;
					else
					{
						if (response_obj.Error_Msg)
							var msg = response_obj.Error_Msg;
						else
							var msg = lang['oshibka_pri_poluchenii_nomera_kartyi'];
	                    form.ownerCt.showMessage(lang['oshibka'], msg, function() {wnd.hide()});
					}
        		}
    	    	else
		        {
    	    		form.ownerCt.showMessage(lang['oshibka'], lang['oshibka_pri_poluchenii_nomera_kartyi'], function() {form.getForm().findField('PersonCard_Code').focus(true, 100);});
        	   	}

				if (!success)
        	    	form.ownerCt.showMessage(lang['oshibka'], lang['oshibka_pri_poluchenii_nomera_kartyi'], function() {form.getForm().findField('PersonCard_Code').focus(true, 100);});

	           	if (person_card_code)
					form.findField('PersonAmbulatCard_Num').setValue(person_card_code);
	        },
            url: '?c=PersonCard&m=getPersonCardCode'
        });
	},
	submit: function (callback,ignoreUniq) {
		var form = this.findById('PersonAmbulatCardEditForm');
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		var params={};
		var AmbulatCardLoactArr = [];
		this.AmbulatCardLocatGrid.getGrid().getStore().each(function(s,d,f){
			if(s.get('isSave')==1){
				s.set('PersonAmbulatCardLocat_begD',Ext.util.Format.date(s.get('PersonAmbulatCardLocat_begD'),'Y-m-d'))
				AmbulatCardLoactArr.push(s.data);
			}
		});
		params.AmbulatCardLoactArr = Ext.util.JSON.encode(AmbulatCardLoactArr);
		//params.AmbulatCardType_id = form.getForm().findField('AmbulatCardType_id').getValue();
		params.Lpu_id = form.getForm().findField('Lpu_id').getValue();
		params.PersonFIO = form.getForm().findField('PersonFIO').getValue();
		params.action = current_window.action;
		if(ignoreUniq){
			params.ignoreUniq =1;
		}
		loadMask.show();
		form.getForm().submit({
			params: params,
			failure: function (result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#'] + action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function (result_form, action) {
				loadMask.hide();
				if (action.result) {
					log(action)
					if (action.result.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function (buttonId, text, obj) {
								if (buttonId == 'yes') {
									current_window.doSave(callback,1);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: lang['vopros']
						});
						return false;
					} else {
						current_window.hide();
					}
					
					if (action.result.PersonAmbulatCard_id) {
					    current_window.PersonAmbulatCard_id = action.result.PersonAmbulatCard_id
					    current_window.form.findField('PersonAmbulatCard_id').setValue(action.result.PersonAmbulatCard_id)
					    if(callback){
						callback();
					    }else{
							var data ={
								PersonAmbulatCard_id:action.result.PersonAmbulatCard_id,
								PersonAmbulatCard_Num:current_window.form.findField('PersonAmbulatCard_Num').getValue()
							}
					    current_window.callback(data);
					    current_window.hide();
					    }
					} else {
						sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function () {
									form.hide();
								},
								icon: Ext.Msg.ERROR,
								msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
								title: lang['oshibka']
							});
					}
				}
			}
		});
	},
	enableEdit: function (enable) {
		var form = this.findById('PersonAmbulatCardEditForm').getForm();
		var formEditCard = Ext.getCmp('PersonAmbulatCardEditFormFieldPanel');
		formEditCard.enable();
		if (enable) {
			form.findField('PersonAmbulatCard_Num').enable();
			this.buttons[0].enable();
			if(this.moveAmbulatCard){
				var formEditCard = Ext.getCmp('PersonAmbulatCardEditFormFieldPanel');
				formEditCard.disable();
			}
		} else {
			form.findField('PersonAmbulatCard_Num').disable();
			this.buttons[0].disable();
		}
	},
	show: function () {
		sw.Promed.swPersonAmbulatCardEditWindow.superclass.show.apply(this, arguments);
		var that = this;
		this.title = "Амбулаторная карта"; 
		if (!arguments[0]) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
					title: lang['oshibka'],
					fn: function () {
						this.hide();
					}
				});
		}
		this.focus();
		this.callback =Ext.emptyFn;
		if (arguments[0].PersonAmbulatCard_id) {
			this.PersonAmbulatCard_id = arguments[0].PersonAmbulatCard_id;
		} else {
			this.PersonAmbulatCard_id = null;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		} else {
			if (( this.PersonAmbulatCard_id ) && ( this.PersonAmbulatCard_id > 0 ))
				this.action = "edit";
			else
				this.action = "add";
		}
		this.moveAmbulatCard = (arguments[0].moveAmbulatCard) ? true : false;

		var form = this.findById('PersonAmbulatCardEditForm');
		form.getForm().reset();
		var grid = this.AmbulatCardLocatGrid.getGrid();
		grid.getStore().removeAll();
		
		form.getForm().setValues(arguments[0]);
		//this.AmbulatCardLocatGrid.removeAll();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		/*this.form.findField('AmbulatCardType_id').getStore().load({callback:function(){
				var AmbulatCardType= that.form.findField('AmbulatCardType_id');
				if(AmbulatCardType.getValue()>0){
				var record = AmbulatCardType.getStore().getById(AmbulatCardType.getValue());
					if(record){
						AmbulatCardType.setDisabled(true);
					}else{
						AmbulatCardType.setDisabled(false);
						AmbulatCardType.setValue(1);
					}
				}else{
					AmbulatCardType.setDisabled(false);
				}
		}});*/
		switch (this.action) {
			case 'add':
				this.form.findField('PersonAmbulatCard_id').setValue('0');
				this.setTitle(that.title + lang['_dobavlenie']);
				loadMask.hide();
				form.getForm().clearInvalid();
				var DT = new Date();
				var addRecord = {
					PersonAmbulatCardLocat_begD:DT,
					PersonAmbulatCardLocat_begT:DT.format('H:i'),
					AmbulatCardLocatType_id:1,
					Server_id:that.form.findField('Server_id').getValue(),
					PersonAmbulatCardLocat_begDate:DT.format('Y-m-d H:i'),
					AmbulatCardLocatType:"1. Регистратура",
					isSave:1
				};
				this.editNoSaveRecord(addRecord,'add');
				this.enableEdit(true);
				break;
			case 'edit':
				//this.findById('PersonAmbulatCardEditFormFieldPanel').setHeight(Ext.isIE ? 310 : 295);
				this.findById('PersonAmbulatCardEditForm').getForm().reset();
				this.setTitle(that.title + lang['_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.findById('PersonAmbulatCardEditForm').getForm().reset();
				this.setTitle(that.title + lang['_prosmotr']);
				this.enableEdit(false);
				break;
		}
		if (this.action != 'add') {
			form.getForm().load(
				{
					params: {
						PersonAmbulatCard_id: that.PersonAmbulatCard_id
					},
					failure: function () {
						loadMask.hide();
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function () {
								that.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
							title: lang['oshibka']
						});
					},
					success: function () {
						loadMask.hide();
						/*var AmbulatCardType_id= that.form.findField('AmbulatCardType_id');
						if(AmbulatCardType_id.getValue()>0){
							AmbulatCardType_id.setDisabled(true);
						}*/
					},
					url: '/?c=PersonAmbulatCard&m=loadPersonAmbulatCard'
				});
				that.reloadAmbulatCardLocatGrid();
		}
		if(!Ext.isEmpty(this.form.findField('PersonAmbulatCard_endDate').getValue())){
			this.form.findField('PersonAmbulatCard_CloseCause').setAllowBlank(false);
		}else{
			this.form.findField('PersonAmbulatCard_CloseCause').setAllowBlank(true);
		}
		var grid = this.findById('AmbulatCardLocatGrid');
		if (this.action != 'view') {
			grid.setReadOnly(false);
		} else {
			
			this.buttons[2].focus();
			grid.setReadOnly(true);
		}
	},
	
	reloadAmbulatCardLocatGrid:function(){
	    var win = this;
	    var grid = this.AmbulatCardLocatGrid.getGrid();
	    var params = {PersonAmbulatCard_id:win.PersonAmbulatCard_id}
		params.start = 0;
	    params.limit = 5;
	    var baseParams = params;
	    grid.getStore().removeAll();
	    grid.getStore().baseParams = baseParams;
	    
	   this.AmbulatCardLocatGrid.loadData({globalFilters: params});
	   /*grid.getStore().load({
		params: params
		});*/
		
	},
	openAmbulatCardLocatWindow:function(action){
	    var  params = {};
	    var grid = this.findById('AmbulatCardLocatGrid').getGrid();
	    var win = this;
		params.type='nosave';
	    if(action=='add'){
		    params.formParams = {
			PersonAmbulatCard_id:win.PersonAmbulatCard_id
		    };
		
			params.callback= function (data) {
				win.editNoSaveRecord(data,'add');
			
		}
	    }else{
		var record = grid.getSelectionModel().getSelected();
			if( record.get('isSave') != 1 ){
				params.formParams = {
					PersonAmbulatCardLocat_id:record.get('PersonAmbulatCardLocat_id')
				}
			}else{
				params.formParams=record.data;
			}
			params.callback= function (data) {
				win.editNoSaveRecord(data,'edit');
			}
	    }
		
		/*params.onHide=function(){
			win.reloadAmbulatCardLocatGrid();
		}*/
	    params.formParams.Server_id = win.form.findField('Server_id').getValue();
	    params.action = action;
	    params.Lpu_id=win.form.findField('Lpu_id').getValue();

	    getWnd('swPersonAmbulatCardLocatEditWindow').show(params);
	},
	editNoSaveRecord:function(data,action){
		var store = this.findById('AmbulatCardLocatGrid').getGrid().getStore();
		log(action,'action')
		if(action=='add'){
			if ( store.getCount() == 1 && !store.getAt(0).get('PersonAmbulatCardLocat_id') ) {
					store.removeAll();
				}
			data.PersonAmbulatCardLocat_id = -swGenTempId(store);
			store.loadData({data:[data]},true);
			log(data);
		}else{
			var index= store.findBy(function (rec){return rec.get('PersonAmbulatCardLocat_id') == data.PersonAmbulatCardLocat_id});
			var record = store.getAt(index);
			record.set('MedPersonal_id',data.MedPersonal_id);
			record.set('MedStaffFact_id',data.MedStaffFact_id);
			record.set('PersonAmbulatCardLocat_Desc',data.PersonAmbulatCardLocat_Desc);
			record.set('PersonAmbulatCardLocat_OtherLocat',data.PersonAmbulatCardLocat_OtherLocat);
			record.set('PersonAmbulatCardLocat_begD',data.PersonAmbulatCardLocat_begD);
			record.set('PersonAmbulatCardLocat_begT',data.PersonAmbulatCardLocat_begT);
			record.set('AmbulatCardLocatType_id',data.AmbulatCardLocatType_id);
			record.set('Server_id',data.Server_id);
			record.set('PersonAmbulatCardLocat_id',data.PersonAmbulatCardLocat_id);
			record.set('PersonAmbulatCardLocat_begDate',data.PersonAmbulatCardLocat_begDate);
			record.set('AmbulatCardLocatType',data.AmbulatCardLocatType);
			record.set('FIO',data.FIO);
			record.set('MedStaffFact',data.MedStaffFact);
			record.set('LpuBuilding_id',data.LpuBuilding_id);
			record.set('LpuBuilding_Name',data.LpuBuilding_Name);
			record.set('isSave',1);
			record.commit();
		}
		
	},
	initComponent: function () {
		var that = this;
		this.AmbulatCardLocatGrid= new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add',
					handler:function(){that.openAmbulatCardLocatWindow('add')}
				    },
				{
					name: 'action_edit',
					handler:function(){that.openAmbulatCardLocatWindow('edit')}
				    },
				{name: 'action_view',
					handler:function(){that.openAmbulatCardLocatWindow('view')}
				    },
				{name: 'action_refresh',hidden: true, disabled: true},
				{name: 'action_delete'},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=PersonAmbulatCard&m=getPersonAmbulatCardLocatList',
			region: 'center',
			object: 'PersonAmbulatCardLocat',
			editformclassname: 'swPersonAmbulatCardLocatEditWindow',
			id: 'AmbulatCardLocatGrid',
			paging: true,
			root: 'data',
			pageSize:5,
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'PersonAmbulatCardLocat_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonAmbulatCardLocat_begDate', type: 'string', header: langs('Дата и время движения'), id: 'autoexpand'},
				{name: 'AmbulatCardLocatType', type: 'string', header: langs('Местонахождение')},
				{name: 'FIO', type: 'string', header: langs('ФИО сотрудника')},
				{name: 'MedStaffFact', type: 'string', header: langs('Должность сотрудника')},
				{name: 'LpuBuilding_Name', type: 'string', width: 200, header: langs('Подразделение')},
				{name: 'PersonAmbulatCardLocat_Desc', type: 'string', header: langs('Коментарий к движению')},
				{name:'MedPersonal_id',type:'int',hidden:true},
				{name:'MedStaffFact_id',type:'int',hidden:true},
				{name:'AmbulatCardLocatType_id',type:'int',hidden:true},
				{name:'Server_id',type:'int',hidden:true},
				{name:'PersonAmbulatCardLocat_OtherLocat',type:'int',hidden:true},
				{name:'LpuBuilding_id',type:'int',hidden:true},
				{name:'PersonAmbulatCardLocat_begT',type:'string',hidden:true},
				{name:'PersonAmbulatCardLocat_begD',type:'date',hidden:true},
				{name:'isSave',type:'int',hidden:true}
			],
			title: lang['dvijeniya_ambulatornoy_kartyi'],
			toolbar: true
		});
		this.PersonAmbulatCardEditForm = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			buttonAlign: 'left',
			frame: false,
			region: 'north',
			id: 'PersonAmbulatCardEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			items: [
				{
					id: 'PAC_PersonAmbulatCard_id',
					name: 'PersonAmbulatCard_id',
					value: 0,
					xtype: 'hidden'
				},{
					name: 'Person_id',
					xtype: 'hidden'
				},{
					name: 'Server_id',
					xtype: 'hidden'
				},
				new Ext.Panel({
					//height: Ext.isIE ? 310 : 295,
					id: 'PersonAmbulatCardEditFormFieldPanel',
					bodyStyle: 'padding-top: 0.2em;',
					border: false,
					frame: false,
					style: 'margin-bottom: 0.1em;',
					items: [
						{
							allowBlank: false,
							enableKeyEvents: true,
							hiddenName:"PersonAmbulatCard_Num",
							anchor:'98%',
							fieldLabel: lang['№_amb_kartyi'],
							maxLength: 15,
							listeners: {
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.F2)
									{
										e.stopEvent();
										that.getPersonCardCode();
									}
								}
							},
							name: 'PersonAmbulatCard_Num',
							triggerClass: 'x-form-plus-trigger',
							selectOnFocus: true,
							tabIndex: 2110,
							onTriggerClick: function() {
								if ( !this.disabled )
									that.getPersonCardCode()
							},
							xtype: 'trigger'
						},
						{
						    allowBlank: false,
						    fieldLabel: lang['meditsinskaya_organizatsiya'],
						    xtype:'swlpucombo',
							disabled:true,
						    value:getGlobalOptions().lpu_id,
						    width: 200,
						    name: 'Lpu_id',
						    hiddenName:'Lpu_id'
						},{
						    fieldLabel: lang['patsient'],
						    xtype:'textfield',
						    hiddenName:'PersonFIO',
						    name:'PersonFIO',
						    disabled:true
						    
						},
						/*{
						    allowBlank: false,
						    xtype:'swambulatcardtypecombo',
						    hiddenName:'AmbulatCardType_id'
						},*/{
							fieldLabel: lang['data_zakryitiya'],
							format: 'd.m.Y',
							name: 'PersonAmbulatCard_endDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							hiddenName: 'PersonAmbulatCard_endDate',
							selectOnFocus: true,
							width: 100,
							xtype: 'swdatefield',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if(!Ext.isEmpty(newValue)){
										that.form.findField('PersonAmbulatCard_CloseCause').setAllowBlank(false);
									}else{
										that.form.findField('PersonAmbulatCard_CloseCause').setAllowBlank(true);
									}
								}
							}
						},{
							fieldLabel: lang['prichina_zakryitiya'],
							hiddenName: 'PersonAmbulatCard_CloseCause',
							name: 'PersonAmbulatCard_CloseCause',
							anchor:'98%',
							xtype: 'textarea'
						}
						
					],
					layout: 'form'
				})
			],
			keys: [
				{
					alt: true,
					fn: function (inp, e) {
						switch (e.getKey()) {
							case Ext.EventObject.C:
								if (this.action != 'view') {
									this.doSave(false);
								}
								break;
							case Ext.EventObject.J:
								this.hide();
								break;
						}
					},
					key: [ Ext.EventObject.C, Ext.EventObject.J ],
					scope: this,
					stopEvent: true
				}
			],
			reader: new Ext.data.JsonReader({
				success: function () {
					//
				}
			}, [
				{ name: 'PersonAmbulatCard_id' },
				{ name: 'Person_id' },
				{ name: 'Server_id' },
				//{ name: 'AmbulatCardType_id' },
				{ name: 'PersonFIO' },
				{ name: 'Lpu_id' },
				{ name: 'PersonAmbulatCard_Num' },
				{ name: 'PersonAmbulatCard_CloseCause' },
				{ name: 'PersonAmbulatCard_endDate' }
			]),
			url: '/?c=PersonAmbulatCard&m=savePersonAmbulatCard'
		});
		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE,
					onShiftTabAction: function () {
						that.focusOnGrid();
					},
					onTabAction: function () {
						that.buttons[2].focus();
					}
				},
				{
					text: '-'
				},
				{
					handler: function () {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL,
					onTabAction: function () {
						if (that.action == 'view') {
							that.focusOnGrid();
						}
					},
					onShiftTabAction: function () {
						if (that.action == 'view') {
							that.focusOnGrid();
						} else {
							that.buttons[0].focus();
						}
					}
				}
			],
			items: [this.PersonAmbulatCardEditForm,
				this.AmbulatCardLocatGrid
			]
		});
		sw.Promed.swPersonAmbulatCardEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById("PersonAmbulatCardEditForm").getForm();
		this.focusOnGrid = function () {
			var grid = that.findById('AmbulatCardLocatGrid').getGrid();
			if (grid.getStore().getCount() > 0) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}

	}
});