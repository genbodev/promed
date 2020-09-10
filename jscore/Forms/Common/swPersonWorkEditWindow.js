/**
* swPersonWorkEditWindow - окно редактирования информации о сотруднике организации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @author       Salakhov R.
* @version      02.2017
* @comment      
*/
sw.Promed.swPersonWorkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	title: 'Сотрудник организации: Редактирование',
	id: 'PersonWorkEditWindow',
	modal: true,
	shim: false,
	width: 500,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('PersonWorkEditForm').getFirstInvalidEl().focus(true);
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
	submit: function() {
		var wnd = this;
		var params = {};

		if (this.form.findField('pmUserCacheOrg_id').disabled) {
			params.pmUserCacheOrg_id = this.form.findField('pmUserCacheOrg_id').getValue();
		}

		wnd.getLoadMask('Подождите, идет сохранение...').show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.PersonWork_id > 0) {
					var id = action.result.PersonWork_id;
					wnd.form.findField('PersonWork_id').setValue(id);
					wnd.callback(wnd.owner, id);

                    var data = wnd.form.getValues();
                    wnd.onSave(data);

					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swPersonWorkEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.onSave = Ext.emptyFn;
		this.PersonWork_id = null;

		this.setTitle("Сотрудник организации");
		this.form.reset();

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].PersonWork_id ) {
			this.PersonWork_id = arguments[0].PersonWork_id;
		}
		if ( arguments[0].Org_id ) {
			this.form.findField('Org_id').setValue(arguments[0].Org_id);
		} else {
			this.form.findField('Org_id').setValue(getGlobalOptions().org_id);
		}

        var loadMask = new Ext.LoadMask(this.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		switch (this.action) {
			case 'add':
                var current_date = new Date();
				this.setTitle(this.title + ": Добавление");
				this.enableEdit(true);

                this.form.findField('PersonWork_begDate').setValue(current_date);

				var orgCombo = this.form.findField('Org_id');
				orgCombo.getStore().load({
					params: {Org_id: orgCombo.getValue()},
					callback: function() {
						orgCombo.setValue(orgCombo.getValue());
						orgCombo.fireEvent('change', orgCombo, orgCombo.getValue());
					}
				});

				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				this.enableEdit(this.action == 'edit');

				this.form.load({
					url: '/?c=Person&m=loadPersonWorkForm',
					params: {PersonWork_id: this.PersonWork_id},
					success: function(form, response) {
						loadMask.hide();

						this.form.findField('Person_id').setRawValue(response.result.data.Person_Fio);

						var orgCombo = this.form.findField('Org_id');
						orgCombo.getStore().load({
							params: {Org_id: orgCombo.getValue()},
							callback: function() {
								orgCombo.setValue(orgCombo.getValue());
							}
						});

						var orgStructCombo = this.form.findField('OrgStruct_id');
						orgStructCombo.getStore().load({
							params: {Org_id: orgCombo.getValue()},
							callback: function() {
								orgStructCombo.setValue(orgStructCombo.getValue());
							}
						});

						var postCombo = this.form.findField('Post_id');
						postCombo.setOrgId(orgCombo.getValue());
						postCombo.getStore().load({
							params: {Post_id: postCombo.getValue()},
							callback: function() {
								postCombo.lastQuery = 'This query sample that is not will never appear';
								postCombo.setValue(postCombo.getValue());
							}
						});

						var userCombo = this.form.findField('pmUserCacheOrg_id');
						if (this.action == 'edit') userCombo.enable();
						userCombo.getStore().baseParams.Org_id = orgCombo.getValue();
						userCombo.getStore().load({
							params: {pmUserCacheOrg_id: userCombo.getValue()},
							callback: function() {
								userCombo.lastQuery = 'This query sample that is not will never appear';
								userCombo.setValue(userCombo.getValue());
							}
						});
					}.createDelegate(this),
					failure: function() {
						loadMask.hide();
					}.createDelegate(this)
				});
				break;
		}
	},
	initComponent: function() {
		var wnd = this;

        var form = new Ext.form.FormPanel({
            url:'/?c=Person&m=savePersonWork',
            autoHeight: true,
            frame: true,
            labelAlign: 'right',
            labelWidth: 100,
            bodyStyle: 'padding: 5px 5px 0',
			defaults: {
            	width: 300
			},
            items: [{
                xtype: 'hidden',
                name: 'PersonWork_id'
            }, {
            	xtype: 'sworgcomboex',
				hiddenName: 'Org_id',
				fieldLabel: 'Организация',
				allowBlank: false,
				listeners: {
            		'change': function(combo, newValue, oldValue) {
						var postCombo = this.form.findField('Post_id');
            			var userCombo = this.form.findField('pmUserCacheOrg_id');
						var orgStructCombo = this.form.findField('OrgStruct_id');

            			if (Ext.isEmpty(newValue)) {
            				userCombo.reset();
            				userCombo.disable();
							userCombo.getStore().baseParams.Org_id = null;
							userCombo.lastQuery = 'This query sample that is not will never appear';

							orgStructCombo.reset();
							orgStructCombo.getStore().removeAll();

							postCombo.reset();
							postCombo.setOrgId(null);
							postCombo.lastQuery = 'This query sample that is not will never appear';
						} else {
							userCombo.enable();
							userCombo.getStore().baseParams.Org_id = newValue;
							userCombo.lastQuery = 'This query sample that is not will never appear';

							orgStructCombo.reset();
							orgStructCombo.getStore().load({params: {Org_id: newValue}});

							postCombo.reset();
							postCombo.setOrgId(newValue);
							postCombo.lastQuery = 'This query sample that is not will never appear';
						}
					}.createDelegate(this)
				}
			}, {
				xtype: 'sworgstructcombo',
				hiddenName: 'OrgStruct_id',
				fieldLabel: 'Структ. уровень'
			}, {
                xtype: 'swpostsearchcombo',
                comboSubject: 'Post',
                hiddenName: 'Post_id',
                fieldLabel: 'Должность',
                //searchMode: 'all',
                allowBlank: false
            }, {
                xtype: 'swpersoncomboex',
                fieldLabel: 'ФИО',
                hiddenName: 'Person_id',
                allowBlank: false
            }, {
                xtype: 'swdatefield',
                fieldLabel: 'Дата начала',
                name: 'PersonWork_begDate',
                allowBlank: false,
				width: 160
            }, {
            	xtype: 'swdatefield',
				fieldLabel: 'Дата окончания',
				name: 'PersonWork_endDate',
				width: 160,
				hidden: !isOrgAdmin()
			}, {
				xtype: 'swpmusercacheorgcombo',
				hiddenName: 'pmUserCacheOrg_id',
				fieldLabel: 'Учетная запись',
				displayField: 'pmUser_Login',
				width: 160,
				listWidth: 300,
				disabled: true,
				hidden: !isOrgAdmin()
			}],
			reader: new Ext.data.JsonReader({
				success: function() {}
			}, [
				{name: 'PersonWork_id'},
				{name: 'Org_id'},
				{name: 'OrgStruct_id'},
				{name: 'Post_id'},
				{name: 'Person_id'},
				{name: 'Person_Fio'},
				{name: 'PersonWork_begDate'},
				{name: 'PersonWork_endDate'},
				{name: 'pmUserCacheOrg_id'}
			])
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
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swPersonWorkEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});