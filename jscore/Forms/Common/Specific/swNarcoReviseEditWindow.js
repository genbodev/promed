/**
* swNarcoReviseListWindow- окно просмотра, добавления и редактирования организаций
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Registry
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       swanuser (info@swan.ru)
* @version      23.07.2014
* @comment      comment
*/
sw.Promed.swNarcoReviseEditWindow = Ext.extend(sw.Promed.BaseForm, {
	
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['sverka_dannyih'],
	draggable: true,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	width: 400,
	//height: 230,
	autoHeight: true,
	modal: true,
	resizable: false,
	id:'NarcoReviseEditWindow',
	onCancel: Ext.emptyFn,
	action:'edit',
	callback: Ext.emptyFn,
	plain: true,
	getNumber: function() {
		var num_field = this.FormPanel.getForm().findField('ReviseList_Code');

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение номера..."});
		loadMask.show();

		var params = new Object();



		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					num_field.setValue(response_obj.Num);
					num_field.focus(true);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera']);
				}
			},
			params: params,
			url: '/?c=NarcoRevise&m=getNumber'
		});
    },
	show: function() {
		sw.Promed.swNarcoReviseEditWindow.superclass.show.apply(this, arguments);
		var win = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.ReviseList_id = null;
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}
		if(arguments[0].action){
			this.action = arguments[0].action;
		}
		if(arguments[0].ReviseList_id){
			this.ReviseList_id = arguments[0].ReviseList_id;
		}
		if(this.action == 'add'){
			this.getNumber();
			
		}
		if ( this.action != 'add' ) {
			var params = {};
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			base_form.reset();
			
				
				params.ReviseList_id = this.ReviseList_id;
				base_form.load({
					failure: function() {
						win.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera']);
					}.createDelegate(this),
					url:'/?c=NarcoRevise&m=loadNarcoReviseEditWindow',
					params:params,
					success: function(fm,rec,d) {
						
					}
				})
			win.getLoadMask().hide();
			
			
		}
		this.setFieldDisable();
		this.syncShadow();
		
	},
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		//this.formStatus = 'save';
		var base_form = this.FormPanel;
		if (!base_form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
            this.formStatus = 'edit';
			return false;
		}

		this.submit();
	},
	submit: function(mode,onlySave) {
		var form = this.FormPanel;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		var base_form = this.FormPanel.getForm();
		var params = {};
		params.ReviseList_Performer = base_form.findField('ReviseList_Performer').getValue();
		loadMask.show();
		form.getForm().submit({
			url:'/?c=NarcoRevise&m=saveNarcoReviseEditWindow',
			params:params,
			failure: function(result_form, response) {
				this.formStatus = 'edit';
				/*log(result_form,1)
				Ext.getCmp('NarcoReviseListWindow').doSearch();
				loadMask.hide();
				win.hide();*/
				var response_obj = Ext.util.JSON.decode(response.response.responseText);
				
				if (response_obj) {
					if (response_obj.Error_Msg) {
						Ext.Msg.alert(lang['oshibka'], response_obj.Error_Msg);
						loadMask.hide();
						win.hide();
					}
				}
			},
			success: function(result_form, response,s) {
				var response_obj = Ext.util.JSON.decode(response.response.responseText);
				
				if (response_obj) {
					log(response_obj)
				}
				Ext.getCmp('NarcoReviseListWindow').doSearch(response_obj.ReviseList_id);
				loadMask.hide();
				win.hide();
			}
		});
	},
	setFieldDisable:function(){
		var form = this.FormPanel;
		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.items.each(function(item,s,f){
			if(item.ncd&&item.ncd==true){
				item.setDisabled(true);
			}else
			item.setDisabled(win.action=='view');
		});
		if(win.action=='view'){
			this.findById('sourceDL').hide();
			this.buttons[0].disable();
		}else{
			this.buttons[0].enable();
			this.findById('sourceDL').show();
		}
	
	},
	initComponent: function() {
		var win = this;
		this.FormPanel = new Ext.form.FormPanel({
			timeout: 60000,
			fileUpload: true,
			errorReader:{
				read: function (resp){
					var result = false;
					win.getLoadMask().hide();
					try {
						result = Ext.decode(resp.responseText);

					} catch (e) {
						sw.swMsg.alert(lang['oshibka_pri_vyipolnenii_importa'],lang['pri_vyipolnenii_importa_proizoshla_oshibka'] +
							lang['pojaluysta_obratites_k_razrabotchkam_soobschiv_sleduyuschuyu_otladochnuyu_informatsiyu'] +
							'<pre style="overflow: scroll; height: 200px; width: 100%;" >При отправке формы произошла ошибка. Ответ сервера: ' + resp.responseText + '</pre>')
					}
					return result;
				}
			},
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
			{
				name: 'ReviseList_id'
			},

			{
				name: 'ReviseList_Code'
			},

			{
				name: 'ReviseList_setDate'
			},

			{
				name: 'PermitType_id'
			},

			{
				name: 'Org_id'
			},

			{
				name: 'ReviseList_Performer'
			},
			{
				name:'inpFile'
			}
			
			]),
			autoHeight: true,
			frame: true,
			id: 'NarcoReviseEditForm',
			labelAlign: 'right',
			labelWidth: 100,
			region: 'north',
			items: [
				{
					name:'ReviseList_id',
					xtype:'hidden'
				},{
					allowBlank: false,
					enableKeyEvents: true,
					fieldLabel: lang['nomer'],
					listeners: {
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.F4:
									e.stopEvent();
									this.getNumber();
									break;
							}
						}.createDelegate(this)
					},
					name: 'ReviseList_Code',
					onTriggerClick: function() {
						this.getNumber();
					}.createDelegate(this),
					tabIndex: this.tabindex + 2,
					triggerClass: 'x-form-plus-trigger',
					validateOnBlur: false,
					width: 150,
					xtype: 'trigger'
				},{
					allowBlank: false,
					fieldLabel: lang['data'],
					format: 'd.m.Y',
					maxValue: getGlobalOptions().date,
					minValue: getMinBirthDate(),
					setDate:true,
					value:getGlobalOptions().date,
					name: 'ReviseList_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 95,
					xtype: 'swdatefield'
				},{
					allowBlank: false,
					anchor: '100%',
					comboSubject: 'PermitType',
					hiddenName: 'PermitType_id',
					fieldLabel: lang['profil'],
					xtype: 'swcommonsprcombo'
				},{
					fieldLabel: lang['initsiator'],
					anchor: '100%',
					hiddenName: 'Org_id',
					allowBlank: false,
					xtype: 'sworgcombo',
					onTrigger1Click: function() {
						var combo = this;
						if (this.disabled) {
							return false;
						}

						getWnd('swOrgSearchWindow').show({
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 ) {
									combo.getStore().load({
										params: {
											Object:'Org',
											Org_id: orgData.Org_id,
											Org_Name:''
										},
										callback: function() {
											combo.setValue(orgData.Org_id);
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
										}
									});
								}

								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					}
				},{
					ncd:true,
					fieldLabel:lang['ispolnitel'],
					anchor: '100%',
					name:'ReviseList_Performer',
					xtype:'textfield',
					value:getGlobalOptions().CurMedPersonal_FIO
				},{layout:'form',
					id:'sourceDL',
						items:[{
						allowBlank: false,
						xtype: 'textfield',
						inputType: 'file',
						fieldLabel: lang['ishodnyiy_fayl'],
						name:'sourcefiles'

					}]
				}
			]
		});
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: lang['zagruzit']
			}, {
				text: '-'
			},
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				text: lang['zakryit']
			}],
			items: [ 
				this.FormPanel
			]
		});
		sw.Promed.swNarcoReviseEditWindow.superclass.initComponent.apply(this, arguments);
        
	}
	
});

