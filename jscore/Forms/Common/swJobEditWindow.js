/**
* swJobEditWindow - окно редактирования места работы.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      06.10.2010
*/

sw.Promed.swJobEditWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'fit',
    width: 450,
    modal: true,
	resizable: false,
	draggable: false,
    autoHeight: true,
    closeAction : 'hide',
	id: 'job_edit_window',
    plain: true,
	returnFunc: function() {},
    title: lang['mesto_rabotyi_redaktirovanie'],
	listeners: {
		'hide': function() {
			this.onWinClose();
		}
	},
	onShowActions: function() {
		var base_form = this.findById('job_edit_form').getForm();
		base_form.setValues(this.fields);
		if ( base_form.findField('Org_id').getValue() > 0 )
				base_form.findField('Org_id').getStore().load({
					params: {
						Object:'Org',
						Org_id: base_form.findField('Org_id').getValue(),
						Org_Name:''
					},
					callback: function()
					{
						base_form.findField('Org_id').setValue(base_form.findField('Org_id').getValue());
					}
				});
		if ( base_form.findField('Org_id').getValue() > 0 )
		{
			var Org_id = base_form.findField('Org_id').getValue();
			base_form.findField('OrgUnion_id').getStore().load({
				params: {
					Object:'OrgUnion',
					OrgUnion_id:'',
					OrgUnion_Name:'',
					Org_id: Org_id
				},
				callback: function()
				{
					base_form.findField('OrgUnion_id').setValue(base_form.findField('OrgUnion_id').getValue());
				}
			});
		}
		base_form.findField('Post_id').getStore().load({
				params: {
					Object:'Post',
					Post_id:'',
					Post_Name:''
				},
				callback: function() {
					if ( base_form.findField('Post_id').getValue() > 0 )
						base_form.findField('Post_id').setValue(base_form.findField('Post_id').getValue());
				}
		});
		
		base_form.findField('Org_id').focus(true, 100);
	},
	show: function() {
		sw.Promed.swJobEditWindow.superclass.show.apply(this, arguments);
		
		if ( arguments[0] )
		{
			if ( arguments[0].callback )
				this.returnFunc = arguments[0].callback;
			if ( arguments[0].ignoreOnClose )
				this.ignoreOnClose = arguments[0].ignoreOnClose;
			else
				this.ignoreOnClose = false;
			if ( arguments[0].fields )
				this.fields = arguments[0].fields;
			if ( arguments[0].action )
				this.action = arguments[0].action;
			if ( arguments[0].onClose )
				this.onWinClose = arguments[0].onClose;
			else
				this.onWinClose = function() {};
		}
		// если это редактирование с загрузкой данных, то загружаем данные
		if ( this.action && this.action == 'edit_with_load' )
		{
			var loadMask = new Ext.LoadMask(
				Ext.get('job_edit_window'),
				{ msg: "Подождите, идет загрузка...", removeMask: true }
			);
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=Person&m=loadJobData',
				params: {Job_id: this.fields.Job_id},
				callback: function(options, success, response) {
					loadMask.hide();
					if ( response && response.responseText )
					{
						var resp = Ext.util.JSON.decode(response.responseText);
						if ( resp && resp[0] )
						{
							this.fields = resp[0];
							this.onShowActions();
						}
					}
				}.createDelegate(this)
			});
		}
		else
			this.onShowActions();
	},
	initComponent: function() {
    	Ext.apply(this, {
 			items: [
				new Ext.form.FormPanel({
					frame: true,
            		autoHeight: true,
            		labelAlign: 'right',
					id: 'job_edit_form',
					labelWidth: 95,
					buttonAlign: 'left',
					bodyStyle:'padding: 5px',
					items: [{
						xtype: 'sworgcombo',
						hiddenName: 'Org_id',
						editable: false,
						fieldLabel: lang['mesto_rabotyi_uchebyi'],
						triggerAction: 'none',
						anchor: '95%',
						tabIndex: TABINDEX_JEW + 1,
						onTrigger1Click: function() {
							var ownerWindow = this.ownerCt.ownerCt;
							var combo = this;

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
						},
						enableKeyEvents: true,
						listeners: {
							'change': function(combo) {
								combo.ownerCt.findById('JEW_OrgUnion_id').clearValue();
								combo.ownerCt.findById('JEW_OrgUnion_id').getStore().load({
									params: {
										Object:'OrgUnion',
										OrgUnion_id:'',
										OrgUnion_Name:'',
										Org_id: combo.getValue()
									}
								});
							},
							'keydown': function( inp, e ) {
								if ( e.F4 == e.getKey() ) {
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

									if ( Ext.isIE ) {
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
						id: 'JEW_OrgUnion_id',
						hiddenName: 'OrgUnion_id',
						xtype: 'sworgunioncombo',
						minChars: 0,
						queryDelay: 1,
						tabIndex: TABINDEX_JEW + 2,
						selectOnFocus: true,
						anchor: '95%',
						forceSelection: false
					}, {
						xtype: 'swpostcombo',
						minChars: 0,
						queryDelay: 1,
						tabIndex: TABINDEX_JEW + 3,
						hidden: false,
						hideLabel: false,
						hiddenName: 'Post_id',
						fieldLabel: lang['doljnost'],
						selectOnFocus: true,
						anchor: '95%',
						forceSelection: false
					}],
					enableKeyEvents: true,
				    keys: [{
				    	alt: true,
				        fn: function(inp, e) {
				        	Ext.getCmp('job_edit_form').ownerCt.hide();
				        },
				        key: [ Ext.EventObject.J ],
				        stopEvent: true
				    }, {
				    	alt: true,
				        fn: function(inp, e) {
				        	Ext.getCmp('job_edit_form').buttons[0].handler();
				        },
				        key: [ Ext.EventObject.C ],
				        stopEvent: true
				    }]
				})
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_JEW + 7,
			        iconCls: 'ok16',
					handler: function() {
						var base_form = this.findById('job_edit_form').getForm();
						if ( !base_form.isValid() ) {
							Ext.MessageBox.show({
								title: "Проверка данных формы",
								msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								fn: function() {
									this.findField('Org_id').focus(true, 100);
								}.createDelegate(base_form)
							});
							return false;
						}
						var values = base_form.getValues();
						if ( base_form.findField('Post_id').getValue() == '' )
						{
							values.PostNew = base_form.findField('Post_id').getRawValue();
						}
						else
						{
							// ищем уже существующее значение
							var id = -1;
							base_form.findField('Post_id').getStore().findBy(function(record) {
								if ( record.get('Post_Name') == base_form.findField('Post_id').getRawValue())
								{
									id = record.get('Post_id');
									return true;
								}
							});

							if ( id != -1 )
							{
								values.PostNew = '';
							}
							else
							{
								values.PostNew = base_form.findField('Post_id').getRawValue().replace(/\-+|\++|\.+|\,+/ig,'').replace(/\s{2,}/ig,' '); //TODO: Filter Post_id  Уничтожаем ненужные символы
								values.Post_id='';
							}
						}
						if ( base_form.findField('OrgUnion_id').getValue() == '' )
						{
							values.OrgUnionNew = base_form.findField('OrgUnion_id').getRawValue();
						}
						else
						{
							if (base_form.findField('OrgUnion_id').getStore().findBy(function(rec) { return rec.get('OrgUnion_Name') == base_form.findField('OrgUnion_id').getRawValue(); }) >= 0)
							{
								values.OrgUnionNew = '';
							}
							else
							{
								values.OrgUnionNew = base_form.findField('OrgUnion_id').getRawValue().replace(/\-+|\++|\.+|\,+/ig,'').replace(/\s{2,}/ig,' '); //TODO: Filter OrgUnion
								values.OrgUnion_id='';
							}
						}
						values.Job_JobString = base_form.findField('Org_id').getRawValue();
						if ( this.ignoreOnClose === true )
							this.onWinClose = function() {};
						Ext.callback(this.returnFunc, this, [values]);
						this.hide();
					}.createDelegate(this)
				},
				{
					text: '-'
				},
					HelpButton(this),
				{
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_JEW + 8,
			        iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			]
		});
		sw.Promed.swJobEditWindow.superclass.initComponent.apply(this, arguments);
	}
});