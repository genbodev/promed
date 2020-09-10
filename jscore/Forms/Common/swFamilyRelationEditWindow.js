/**
* swFamilyRelationEditWindow - окно редактирования родственных связей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Aleksandr Chebukin 
* @version      18.11.2016
*/

/*NO PARSE JSON*/
sw.Promed.swFamilyRelationEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'FamilyRelationEditWindow',
	layout: 'border',
	maximizable: false,
	width: 600,
	height: 200,
	modal: true,
	codeRefresh: true,
	objectName: 'swFamilyRelationEditWindow',
	objectSrc: '/jscore/Forms/Reg/swFamilyRelationEditWindow.js',	
	returnFunc: function(owner) {},
	FamilyRelation_id: null,
	MedStaffFact_id: null,
	MedService_id: null,
	Resource_id: null,
	action: 'add',
	show: function() {		
		sw.Promed.swFamilyRelationEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('FamilyRelationEditForm').getForm();
		base_form.reset();

		if (arguments[0]['action']) {
			this.action = arguments[0]['action'];
		}

		if (arguments[0]['callback']) {
			this.returnFunc = arguments[0]['callback'];
		}
		
		if (arguments[0]['FamilyRelation_id']) {
			this.FamilyRelation_id = arguments[0]['FamilyRelation_id'];
		} else {
			this.FamilyRelation_id = null;
		}
		
		if (arguments[0]['Person_id']) {
			this.Person_id = arguments[0]['Person_id'];
		} else {
			this.Person_id = null;
		}
		
		switch (this.action){
			case 'add':
				this.setTitle('Родственная связь: Добавление');
				break;
			case 'edit':
				this.setTitle('Родственная связь: Редактирование');
				break;
			case 'view':
				this.setTitle('Родственная связь: Просмотр');
				break;
		}
		
		if (this.action != 'add') {
			var loadMask = new Ext.LoadMask(Ext.get('FamilyRelationEditForm'), { msg: "Подождите, идет сохранение..." });
			this.findById('FamilyRelationEditForm').getForm().load({
				url: '/?c=FamilyRelation&m=load',
				params: {
					FamilyRelation_id: this.FamilyRelation_id
				},
				success: function (form, action) {
					loadMask.hide();
					base_form.findField('FamilyRelationType_id').focus();
					base_form.findField('FamilyRelationType_id').fireEvent('change', base_form.findField('FamilyRelationType_id'), base_form.findField('FamilyRelationType_id').getValue());
					var cpid = base_form.findField('Person_cid').getValue();
					if ( cpid > 0 ) {
						base_form.findField('Person_cid').getStore().removeAll();
						base_form.findField('Person_cid').getStore().loadData([{
							Person_id: cpid,
							Person_Fio: base_form.findField('Person_cid_Fio').getValue()
						}]);
						base_form.findField('Person_cid').setValue(cpid);
					}
				},
				failure: function (form, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
						this.hide();
					}
				},
				scope: this
			});		
		} else {
			base_form.findField('FamilyRelationType_id').focus();		
			base_form.findField('Person_id').setValue(this.Person_id);
		}
		
		if (this.action=='view') {
			base_form.findField('FamilyRelationType_id').disable();
			base_form.findField('Person_cid').disable();
			base_form.findField('FamilyRelation_begDate').disable();
			base_form.findField('FamilyRelation_endDate').disable();
			this.buttons[0].disable();
		} else {
			base_form.findField('FamilyRelationType_id').enable();
			base_form.findField('Person_cid').enable();
			base_form.findField('FamilyRelation_begDate').enable();
			base_form.findField('FamilyRelation_endDate').enable();
			this.buttons[0].enable();
		}
		
	},
	doSave: function() 
	{
		var win = this;
		var form = this.findById('FamilyRelationEditForm').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('FamilyRelationEditForm'), { msg: "Подождите, идет сохранение..." });
		var params = {};
		
		if (!form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('FamilyRelationEditForm').getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		loadMask.show();		
		form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result && action.result.success) {
					win.hide();
					win.returnFunc();
				}
				else {
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_primechaniya_proizoshla_oshibka']);
				}
							
			}.createDelegate(this)
		});
	},

	initComponent: function() {
	
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			id:'FamilyRelationEditForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 150,
			items:
			[{
				name: 'FamilyRelation_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_cid_Fio',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				comboSubject: 'FamilyRelationType',
				fieldLabel: 'Тип родственной связи',
				hiddenName: 'FamilyRelationType_id',
				width: 180,
				xtype: 'swcommonsprcombo',
			}, {
				allowBlank: false,
				editable: false,
				fieldLabel: 'ФИО',
				hiddenName: 'Person_cid',
				width: 400,
				xtype: 'swpersoncombo',
				onTrigger1Click: function() {
					var ownerWindow = Ext.getCmp('PersonEditWindow');
					var combo = this;

					var
						autoSearch = false,
						fio = new Array();

					if ( !Ext.isEmpty(combo.getRawValue()) ) {
						fio = combo.getRawValue().split(' ');

						// Запускать поиск автоматически, если заданы хотя бы фамилия и имя
						if ( !Ext.isEmpty(fio[0]) && !Ext.isEmpty(fio[1]) ) {
							autoSearch = true;
						}
					}

					getWnd('swPersonSearchWindow').show({
						autoSearch: autoSearch,
						onSelect: function(personData) {
							if ( personData.Person_id > 0 )
							{
								PersonSurName_SurName = Ext.isEmpty(personData.PersonSurName_SurName)?'':personData.PersonSurName_SurName;
								PersonFirName_FirName = Ext.isEmpty(personData.PersonFirName_FirName)?'':personData.PersonFirName_FirName;
								PersonSecName_SecName = Ext.isEmpty(personData.PersonSecName_SecName)?'':personData.PersonSecName_SecName;
								
								combo.getStore().loadData([{
									Person_id: personData.Person_id,
									Person_Fio: PersonSurName_SurName + ' ' + PersonFirName_FirName + ' ' + PersonSecName_SecName
								}]);
								combo.setValue(personData.Person_id);
								combo.collapse();
								combo.focus(true, 500);
								combo.fireEvent('change', combo);
							}
							getWnd('swPersonSearchWindow').hide();
						},
						onClose: function() {combo.focus(true, 500)},
						personSurname: !Ext.isEmpty(fio[0]) ? fio[0] : '',
						personFirname: !Ext.isEmpty(fio[1]) ? fio[1] : '',
						personSecname: !Ext.isEmpty(fio[2]) ? fio[2] : ''
					});
				},
				enableKeyEvents: true,
				listeners: {
					'change': function(combo) {
					},
					'keydown': function( inp, e ) {
						if ( e.F4 == e.getKey() )
						{
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
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							inp.onTrigger1Click();
							return false;
						}
					},
					'keyup': function(inp, e) {
						if ( e.F4 == e.getKey() )
						{
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
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							return false;
						}
					}
				}
			}, {
				fieldLabel: 'Дата начала',
				width: 100,
				name: 'FamilyRelation_begDate',
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Дата окончания',
				width: 100,
				name: 'FamilyRelation_endDate',
				xtype: 'swdatefield'
			}],
			reader: new Ext.data.JsonReader({},
			[
				{ name: 'FamilyRelation_id' },
				{ name: 'Person_id' },
				{ name: 'Person_cid' },
				{ name: 'Person_cid_Fio' },
				{ name: 'FamilyRelationType_id' },
				{ name: 'FamilyRelation_begDate' },
				{ name: 'FamilyRelation_endDate' }
			]
			),
			url: '/?c=FamilyRelation&m=save'
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel],
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
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			},
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
		sw.Promed.swFamilyRelationEditWindow.superclass.initComponent.apply(this, arguments);
	}
});