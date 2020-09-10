/**
* swAccessRightsArmSmoEditWindow - окно редактирования/добавления СМО, для которой разрешен доступ к справочнику МЭСов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
* @author       Samir Abakhri
* @version      24.09.2014
*/

sw.Promed.swAccessRightsArmSmoEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 500,
	layout: 'form',
	id: 'AccessRightsArmSmoEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
    doSave: function()
	{
        var _this = this,
		    form = this.findById('AccessRightsArmSmoEditForm'),
            base_form = form.getForm(),
            data = {};

        //base_form.findField('Consumables_Name').setValue(base_form.findField('Consumables_Name').getValue().trim());

		if ( !base_form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
        loadMask.show();
        form.getForm().submit({
            failure: function(result_form, action) {
                loadMask.hide();
                if (action.result)
                {
                    if (action.result.Error_Code)
                    {
                        Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
                    }
                }
            },
            success: function(result_form, action) {
                loadMask.hide();
                if (action.result) {
                    if (action.result.AccessRightsOrg_id) {
                        _this.hide();
                        _this.callback(data);
                    } else {
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: function() {
                                _this.hide();
                            },
                            icon: Ext.Msg.ERROR,
                            msg: 'При выполнении операции сохранения произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
                            title: lang['oshibka']
                        });
                    }
                }
            }
        });
		return true;
	},
	show: function() 
	{
		sw.Promed.swAccessRightsArmSmoEditWindow.superclass.show.apply(this, arguments);

		if (!arguments[0])
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы.<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}

		var _this = this,
		    form = _this.findById('AccessRightsArmSmoEditForm'),
            base_form = form.getForm(),
            formParams = arguments[0].formParams,
            smos = arguments[0].smos;

		this.focus();
		this.findById('AccessRightsArmSmoEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (arguments[0].AccessRightsOrg_id)
			this.AccessRightsOrg_id = arguments[0].AccessRightsOrg_id;
		else
			this.AccessRightsOrg_id = null;

		if (arguments[0].deniedSmoList) {
            this.deniedSmoList = arguments[0].deniedSmoList;
        }
		else
			this.deniedSmoList = null;

		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		
		if (arguments[0].onHide) 
		{
			this.onHide = arguments[0].onHide;
		}
		
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.AccessRightsOrg_id ) && ( this.AccessRightsOrg_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
        base_form.findField('Org_id').getStore().load({callback: function() {
            base_form.findField('Org_id').getStore().filterBy(function(rec){
                return (!rec.get('Org_id').inlist(smos));
            });
            base_form.setValues(formParams);
        }});

		var AccessRightsName_Code = 1;
		if (arguments[0].formParams && arguments[0].formParams.AccessRightsName_Code)
			AccessRightsName_Code = arguments[0].formParams.AccessRightsName_Code;

		this.AccessCodeRadiogroup.setValue(AccessRightsName_Code);
		this.rgPanel.setVisible(AccessRightsName_Code!=1);
		this.syncShadow();
		switch (this.action) 
		{
			case 'add':
				if(!Ext.isEmpty(arguments[0].idPanel) && arguments[0].idPanel == 'AccessRightsArmSmoEmkGrid'){
					this.setTitle(lang['dostup_k_funktsionalu_emk']+': '+lang['dobavlenie']);
				}else{
					this.setTitle(lang['smo_s_dostupom_k_spravochniku_mes_dobavlenie']);
				}
				//this.setTitle(lang['smo_s_dostupom_k_spravochniku_mes_dobavlenie']);
				this.enableEdit(true);
				//loadMask.hide();
				base_form.clearInvalid();
				break;
			case 'edit':
				if(!Ext.isEmpty(arguments[0].idPanel) && arguments[0].idPanel == 'AccessRightsArmSmoEmkGrid'){
					this.setTitle(lang['dostup_k_funktsionalu_emk']+': '+lang['redaktirovanie']);
				}else{
					this.setTitle(lang['smo_s_dostupom_k_spravochniku_mes_redaktirovanie']);
				}
				//this.setTitle(lang['smo_s_dostupom_k_spravochniku_mes_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				if(!Ext.isEmpty(arguments[0].idPanel) && arguments[0].idPanel == 'AccessRightsArmSmoEmkGrid'){
					this.setTitle(lang['dostup_k_funktsionalu_emk']+': '+lang['prosmotr']);
				}else{
					this.setTitle(lang['smo_s_dostupom_k_spravochniku_mes_prosmotr']);
				}
				//this.setTitle(lang['smo_s_dostupom_k_spravochniku_mes_prosmotr']);
				this.enableEdit(false);
				break;
		}

	},

	initComponent: function () {
		var _this = this;
		// Форма с полями
		this.AccessCodeRadiogroup = new Ext.form.RadioGroup({
			fieldLabel: langs('Способ доступа к ЭМК'),
			xtype: 'radiogroup',
			anchor: '100%',
			id: 'rgAccessRightsName_Code',
			columns: 1,
			vertical: true,
			setValue: function(v){
				if (this.rendered){
					this.items.each(function(item){
						item.setValue(item.getRawValue() == v);
					});
				}
				else {
					for (var k in this.items) {
						this.items[k].checked = this.items[k].inputValue == v;
					}
				}
			},
			items: [
				{
					name: 'AccessRightsName_Code',
					hidden: true,
					boxLabel: langs('Код для доступа к справчнику МСЭ'),
					inputValue: '1'
				},
				{
					name: 'AccessRightsName_Code',
					boxLabel: langs('Кнопка "Открыть ЭМК" на боковой панели'),
					inputValue: '101'
				},
				{
					name: 'AccessRightsName_Code',
					boxLabel: langs('Формирование запросов на просмотр ЭМК'),
					inputValue: '111'
				}
			]
		});
		this.rgPanel = new Ext.Panel({
			hidden: true,
			layout: 'form',
			labelAlign: 'right',
			labelWidth: 170,
			items: [
				this.AccessCodeRadiogroup
			]
		});

		this.AccessRightsArmSmoEditForm = new Ext.form.FormPanel(
			{
				autoHeight: true,
				bodyStyle: 'padding: 5px',
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'AccessRightsArmSmoEditForm',
				labelAlign: 'right',
				labelWidth: 170,
				items: [
					{
						name: 'AccessRightsOrg_id',
						value: 0,
						xtype: 'hidden'
					}, {
						allowBlank: false,
						valueField: 'Org_id',
						fieldLabel: langs('Наименование СМО/ТФОМС'),
						hiddenName: 'Org_id',
						anchor: '100%',
						lastQuery: '',
						xtype: 'swogrsmocombo'
					},
					this.rgPanel
				],
				keys:
					[{
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
						key: [Ext.EventObject.C, Ext.EventObject.J],
						scope: this,
						stopEvent: true
					}],
				reader: new Ext.data.JsonReader(
					{
						success: function () {
							//
						}
					},
					[
						{name: 'AccessRightsOrg_id'},
						{name: 'Org_id'}
					]),
				url: '/?c=AccessRights&m=saveAccessRightsArmSmo'
			});
		Ext.apply(this,
			{
				buttons:
					[{
						handler: function () {
							_this.doSave();
						},
						iconCls: 'save16',
						tabIndex: TABINDEX_ORSEW + 3,
						text: BTN_FRMSAVE
					},
						{
							text: '-'
						},
						HelpButton(this),
						{
							handler: function () {
								_this.hide();
							},
							iconCls: 'cancel16',
							tabIndex: TABINDEX_ORSEW + 4,
							text: BTN_FRMCANCEL
						}],
				items: [this.AccessRightsArmSmoEditForm]
			});
		sw.Promed.swAccessRightsArmSmoEditWindow.superclass.initComponent.apply(this, arguments);
	}
});