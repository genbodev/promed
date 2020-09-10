/**
* swDocumentEditWindow - окно редактирования документа.
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

sw.Promed.swDocumentEditWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'fit',
    width: 450,
    modal: true,
	resizable: false,
	draggable: false,
    autoHeight: true,
    closeAction : 'hide',
	id: 'document_edit_window',
    plain: true,
	returnFunc: function() {},
    title: lang['dokument_redaktirovanie'],
	listeners: {
		'hide': function() {
			this.onWinClose();
		}
	},
	onShowActions: function() {
		var base_form = this.findById('document_edit_form').getForm();
		base_form.reset();
		base_form.setValues(this.fields);
		if ( base_form.findField('OrgDep_id').getValue() > 0 ) {
				base_form.findField('OrgDep_id').getStore().load({
				params: {
					Object:'OrgDep',
					OrgDep_id: base_form.findField('OrgDep_id').getValue(),
					OrgDep_Name: ''
				},
				callback: function() {
					base_form.findField('OrgDep_id').setValue(base_form.findField('OrgDep_id').getValue());
				}
			});
		}

		var doc_type_field = base_form.findField('DocumentType_id');
		if ( doc_type_field.getValue() > 0 ) {
			var doc_type_record = doc_type_field.getStore().getById(doc_type_field.getValue());
			if (doc_type_record) {
				doc_type_field.fireEvent('select',doc_type_field, doc_type_record);
			}
		}

		base_form.findField('DocumentType_id').focus();
	},
	show: function() {
		sw.Promed.swDocumentEditWindow.superclass.show.apply(this, arguments);
		
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
				Ext.get('document_edit_window'),
				{ msg: "Подождите, идет загрузка...", removeMask: true }
			);
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=Person&m=loadDocumentData',
				params: {Document_id: this.fields.Document_id},
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
					id: 'document_edit_form',
					labelWidth: 95,
					buttonAlign: 'left',
					bodyStyle:'padding: 5px',
					items: [{
						fieldLabel: lang['tip'],
						allowBlank: false,
						listeners: {
							'select': function(combo, record, index) {
								var base_form = this.findById('document_edit_form').getForm();
								var mask = null;
								if ( typeof record == 'object' && !Ext.isEmpty(record.get('DocumentType_id')) ) {
									if ( !Ext.isEmpty(record.get('DocumentType_MaskSer')) ) {
										mask = new RegExp(record.get('DocumentType_MaskSer'));
										base_form.findField('Document_Ser').regex = mask;
									}

									if ( !Ext.isEmpty(record.get('DocumentType_MaskNum')) ) {

										base_form.findField('Document_Num').regex =new RegExp( record.get('DocumentType_MaskNum') );
									}

									//this.disableDocumentFields(false);
									if(mask==null||(mask!=null && mask.test(''))){
										base_form.findField('Document_Ser').setAllowBlank(true);
										base_form.findField('Document_Ser').regex = undefined;
										base_form.findField('Document_Ser').clearInvalid();
									}else{
										base_form.findField('Document_Ser').setAllowBlank(false);
									}
									base_form.findField('Document_Num').setAllowBlank(false);
								}
								else {
									base_form.findField('Document_Ser').regex = undefined;
									base_form.findField('Document_Num').regex = undefined;

									//this.disableDocumentFields(true);

									base_form.findField('Document_Num').setAllowBlank(true);
									base_form.findField('Document_Ser').clearInvalid();
									base_form.findField('Document_Num').clearInvalid();
								}
							}.createDelegate(this),
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('document_edit_form').getForm();
								var record = combo.getStore().getById(newValue);
								//----- установка маски https://redmine.swan.perm.ru/issues/86620
								var mask = null;
								if ( typeof record == 'object' && !Ext.isEmpty(record.get('DocumentType_id')) ) {
									if ( !Ext.isEmpty(record.get('DocumentType_MaskSer')) ) {
										mask = new RegExp(record.get('DocumentType_MaskSer'));
										base_form.findField('Document_Ser').regex = mask;
									}
									if ( !Ext.isEmpty(record.get('DocumentType_MaskNum')) ) {

										base_form.findField('Document_Num').regex =new RegExp( record.get('DocumentType_MaskNum') );
									}
									if(mask==null||(mask!=null && mask.test(''))){
										base_form.findField('Document_Ser').setAllowBlank(true);
										base_form.findField('Document_Ser').regex = undefined;
										base_form.findField('Document_Ser').clearInvalid();
									}else{
										base_form.findField('Document_Ser').setAllowBlank(false);
									}
									base_form.findField('Document_Num').setAllowBlank(false);
								}
								else {
									base_form.findField('Document_Ser').regex = undefined;
									base_form.findField('Document_Num').regex = undefined;
									base_form.findField('Document_Num').setAllowBlank(true);
									base_form.findField('Document_Ser').clearInvalid();
									base_form.findField('Document_Num').clearInvalid();
								}
							}.createDelegate(this)
						},
						onTrigger2Click: function() {
							this.findById('').getForm().findField('DocumentType_id').clearValue();
						}.createDelegate(this),
						listWidth: 400,
						tabIndex: TABINDEX_DEW + 1,
						width: 300,
						xtype: 'swdocumenttypecombo'
					}, {
						fieldLabel: lang['seriya'],
						maxLength: 10,
						name: 'Document_Ser',
						tabIndex: TABINDEX_DEW + 2,
						width: 94,
						xtype: 'textfield',
						id: 'DEW_Document_Ser'
					}, {
						fieldLabel: lang['nomer'],
						maxLength: 20,
						name: 'Document_Num',
						tabIndex: TABINDEX_DEW + 3,
						width: 130,
						xtype: 'textfield',
						id: 'DEW_Document_Num'
					}, {
						allowBlank: true,
						editable: false,
						enableKeyEvents: true,
						hiddenName: 'OrgDep_id',
						listeners: {
							'keydown': function( inp, e ) {
								if ( inp.disabled )
									return;
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
						},
						listWidth: 300,
						width: 300,
						onTrigger1Click: function() {
							if ( this.disabled )
								return;
							var ownerWindow = this.ownerCt.ownerCt;
							var combo = this;
							getWnd('swOrgSearchWindow').show({
								onSelect: function(orgData) {
									if ( orgData.Org_id > 0 )
									{
										combo.getStore().load({
											params: {
												Object:'OrgDep',
												OrgDep_id: orgData.Org_id,
												OrgDep_Name: ''
											},
											callback: function()
											{
												combo.setValue(orgData.Org_id);
												combo.focus(true, 500);
												combo.fireEvent('change', combo);
											}
										});
									}
									getWnd('swOrgSearchWindow').hide();
								},
								onClose: function() {combo.focus(true, 200)},
								object: 'dep'
							});
						},
						tabIndex: TABINDEX_DEW + 4,
						triggerAction: 'none',
						xtype: 'sworgdepcombo'
					}, {
						allowBlank: true,
						tabIndex: TABINDEX_DEW + 5,
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						format: 'd.m.Y',
						fieldLabel: lang['data_vyidachi'],
						width: 94,
						name: 'Document_begDate'
					}],
					enableKeyEvents: true,
				    keys: [{
				    	alt: true,
				        fn: function(inp, e) {
				        	Ext.getCmp('document_edit_form').ownerCt.hide();
				        },
				        key: [ Ext.EventObject.J ],
				        stopEvent: true
				    }, {
				    	alt: true,
				        fn: function(inp, e) {
				        	Ext.getCmp('document_edit_form').buttons[0].handler();
				        },
				        key: [ Ext.EventObject.C ],
				        stopEvent: true
				    }]
				})
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_DEW + 8,
			        iconCls: 'ok16',
					handler: function() {
						var base_form = this.findById('document_edit_form').getForm();
						if ( !base_form.isValid() ) {
							Ext.MessageBox.show({
								title: "Проверка данных формы",
								msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								fn: function() {
									this.findField('DocumentType_id').focus();
								}.createDelegate(base_form)
							});
							return false;
						}
						var values = base_form.getValues();
						values.Document_DocumentString = base_form.findField('DocumentType_id').getRawValue() + ' ' + base_form.findField('Document_Ser').getValue()  + ' ' + base_form.findField('Document_Num').getValue() + ' ' + Ext.util.Format.date(base_form.findField('Document_begDate').getValue(), 'd.m.Y');
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
					tabIndex: TABINDEX_DEW + 9,
			        iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			]
		});
		sw.Promed.swDocumentEditWindow.superclass.initComponent.apply(this, arguments);
	}
});