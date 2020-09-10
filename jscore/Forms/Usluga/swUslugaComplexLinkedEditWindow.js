/**
* swUslugaComplexLinkedEditWindow - редактирование состава услуги
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      18.07.2012
* @comment      Префикс для id компонентов UCLEW (UslugaComplexLinkedEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swUslugaComplexLinkedEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaComplexLinkedEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swUslugaComplexLinkedEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

        if ( typeof options != 'object' ) {
            options = new Object();
        }
		
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();
		
		var CopyAttributes = false;
		var CopyAllLinked = false;
		var CopyContent = false;
		
		if ( base_form.findField('CopyAllLinked').checked ) {
			CopyAllLinked = true;
		}
		
		if ( base_form.findField('CopyAttributes').checked ) {
			CopyAttributes = true;
		}
		
		if (options.CopyContent) {
			CopyContent = options.CopyContent
		}
		
		// проверка на копирование состава только если отсуствует соства у родительской
		if (this.CompositionCount == 0) {
			if (!options.ignoreCopyContent && base_form.findField('UslugaComplex_id').getFieldValue('CompositionCount') > 0) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						this.searchMode = false;

						if ( buttonId == 'yes' ) {
							this.doSave({
								ignoreCopyContent: true,
								CopyContent: true
							});
						} else {
							this.doSave({
								ignoreCopyContent: true,
								CopyContent: false
							});					
						}
					}.createDelegate(this),
					msg: lang['skopirovat_sostav_svyazannoy_uslugi'],
					title: lang['vopros']
				});
				
				this.formStatus = 'edit';			
				return false;
			}
		}
		
		if (this.action == 'edit') {
			rewriteExistent = true;
		} else {
			rewriteExistent = false;
		}
		
		var UslugaCategory_Name = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_Name');
		
		var UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick');
		if (UslugaCategory_SysNick == 'lpu' && base_form.findField('UslugaComplex_id').getFieldValue('Lpu_Nick')) {
			UslugaCategory_Name = UslugaCategory_Name + ' - ' + base_form.findField('UslugaComplex_id').getFieldValue('Lpu_Nick');
		}
		
		data.UslugaComplexLinkedData = {
			'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
			'UslugaComplex_pid': base_form.findField('UslugaComplex_pid').getValue(),
			'UslugaComplex_Code': base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code'),
			'UslugaComplex_Name': base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Name'),
			'UslugaCategory_id': base_form.findField('UslugaCategory_id').getValue(),
			'UslugaCategory_Name': UslugaCategory_Name,
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue(),
			'CopyContent': CopyContent,
			'CopyAllLinked': CopyAllLinked,
			'CopyAttributes': CopyAttributes,
			'pmUser_Name': getGlobalOptions().pmuser_name,
			'rewriteExistent': rewriteExistent,
			'oldUslugaComplex_id': this.oldUslugaComplex_id
		};
		
		log(data);
		
		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();
				
				this.callback(data);
				this.hide();
			break;

			case 'remote':
				base_form.submit({
					params: {
						CopyContent: CopyContent?"on":null,
						rewriteExistent: rewriteExistent?"on":null,
						oldUslugaComplex_id: this.oldUslugaComplex_id
					},
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.success ) {
							this.callback(data);
							this.hide();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	draggable: true,
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'UslugaComplexLinkedEditWindow',
	initComponent: function() {
		var form = this;
		
		this.uslugaTree = new Ext.tree.TreePanel({
			title: lang['sostav_uslugi'],
			height: 300,
			autoWidth: true,
			autoScroll:true,
			animate:true,
			enableDD:true,
			containerScroll: true,
			rootVisible: false,
			autoLoad:false,
			frame: true,
			root: {
				nodeType: 'async'
			},
			cls: 'x-tree-noicon',
			loader: new Ext.tree.TreeLoader(
			{
				dataUrl:'/?c=UslugaComplex&m=loadUslugaContentsTree',
				uiProviders: {'default': Ext.tree.TreeNodeUI, tristate: Ext.tree.TreeNodeTriStateUI},
				listeners:
				{
					load: function(p, node)
					{
						callback:
						{
							if (node.firstChild) {
								var firstChild = node.firstChild;
								node.getUI().toggleCheck(true);
								node.fireEvent('checkchange', firstChild, true);
							}
						}
					},
					beforeload: function (tl, node)
					{
						var base_form = form.FormPanel.getForm();
						
						tl.baseParams.level = node.getDepth();
						
						tl.baseParams.UslugaComplex_pid = (base_form.findField('UslugaComplex_id').getValue()>0)?base_form.findField('UslugaComplex_id').getValue():'0';
												
						if (node.getDepth()!=0)
						{
							tl.baseParams.UslugaComplex_pid = node.attributes.object_value;
						}
					}.createDelegate(this)
				}
			}),
			changing: false
		});
		
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'UslugaComplexLinkedEditForm',
			labelAlign: 'right',
			labelWidth: 110,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'UslugaComplex_id' },
				{ name: 'UslugaComplex_pid' },
				{ name: 'UslugaCategory_id' },
				{ name: 'UslugaComplexLinked_id' }
			]),
			url: '/?c=UslugaComplex&m=saveUslugaComplexLinked',
			items: [{
				name: 'UslugaComplexLinked_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaComplex_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaCategory_id',
                comboSubject: 'UslugaCategory',
				fieldLabel: lang['kategoriya'],
				allowBlank: false,
				tabIndex: TABINDEX_UCLEW,
				width: 400,
				moreFields: [
					{name: 'UslugaCategory_SysNick', mapping: 'UslugaCategory_SysNick'}
				],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						
						var uslugaCombo = base_form.findField('UslugaComplex_id');
						uslugaCombo.clearValue();

						if (!Ext.isEmpty(newValue)) {
							uslugaCombo.getStore().filterBy(function(record) {
								if (record.get('UslugaCategory_id') == newValue) {
									return true;
								} else {
									return false;
								}
							});

							uslugaCombo.getStore().baseParams.UslugaCategory_id = newValue;
							this.lastQuery = 'This query sample that is not will never appear';
						} else {
							uslugaCombo.getStore().clearFilter();
							delete uslugaCombo.getStore().baseParams.UslugaCategory_id;
							this.lastQuery = 'This query sample that is not will never appear';
						}
					}.createDelegate(this)
				},
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['usluga'],
				hiddenName: 'UslugaComplex_id',
				allowBlank: false,
				listWidth: 600,
				listeners: 
				{
					'change': function(combo, newValue, oldValue)
					{
						if (!Ext.isEmpty(newValue)) {
							this.uslugaTree.getLoader().load(
								this.uslugaTree.getRootNode(), 
								function () {
									// this.uslugaTree.getRootNode().expand(true);
								}.createDelegate(this)
							);
						} else {
							this.uslugaTree.getRootNode().reload();
						}						
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_UCLEW + 1,
				width: 400,
				xtype: 'swuslugacomplexallcombo'
			}, {
				xtype: 'checkbox',
				hideLabel: true,
				tabIndex: TABINDEX_UCLEW + 2,
				name: 'CopyAllLinked',
				boxLabel: lang['skopirovat_vse_svyazannyie_uslugi']	
			}, {
				xtype: 'checkbox',
				hideLabel: true,
				tabIndex: TABINDEX_UCLEW + 3,
				name: 'CopyAttributes',
				boxLabel: lang['skopirovat_atributyi']	
			}, 
			this.uslugaTree
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('UslugaComplex_id').disabled ) {
						base_form.findField('UslugaComplex_id').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_UCLEW + 4,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();
					if ( !base_form.findField('UslugaCategory_id').disabled ) {
						base_form.findField('UslugaCategory_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_UCLEW + 5,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swUslugaComplexLinkedEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaComplexLinkedEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swUslugaComplexLinkedEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.doLayout();
		this.center();
		
		this.uslugaTree.getRootNode().reload();
		
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formMode = 'local';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.CompositionCount = 0;
		this.oldUslugaComplex_id = null;
		
		var deniedCategoryList = [];

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		if ( arguments[0].deniedCategoryList ) {
			deniedCategoryList = arguments[0].deniedCategoryList;
		}
		
		if ( arguments[0].CompositionCount ) {
			this.CompositionCount = arguments[0].CompositionCount;
		}
		
		if (this.action != 'edit') {
			base_form.findField('UslugaCategory_id').getStore().clearFilter();
			base_form.findField('UslugaCategory_id').lastQuery = '';
			
			base_form.findField('UslugaCategory_id').getStore().filterBy(function(record) {
				if (!record.get('UslugaCategory_id').inlist(deniedCategoryList) && record.get('UslugaCategory_SysNick') != 'promed') {
					return true;
				} else {
					return false;
				}
			});
			
			// если осталось одно значение после фильтрации, то выберем его
			if (base_form.findField('UslugaCategory_id').getStore().getCount() == 1) {
				record = base_form.findField('UslugaCategory_id').getStore().getAt(0);

				if ( record ) {
					base_form.findField('UslugaCategory_id').setValue(record.get('UslugaCategory_id'));
					base_form.findField('UslugaCategory_id').fireEvent('change', base_form.findField('UslugaCategory_id'), record.get('UslugaCategory_id'), null);
				}
			}
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.getLoadMask().show();
		
		var uslugaCombo = base_form.findField('UslugaComplex_id');
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_USLUGA_LINKED_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				uslugaCombo.getStore().load({
					params: {
						UslugaComplex_id: uslugaCombo.getValue()
					},
					callback: function() {
						uslugaCombo.setValue(uslugaCombo.getValue());
					}
				});
				if ( this.action == 'edit' ) {
					this.setTitle(WND_USLUGA_LINKED_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_USLUGA_LINKED_VIEW);
					this.enableEdit(false);
				}

				this.getLoadMask().hide();
				base_form.clearInvalid();
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if (this.action == 'edit') {
			base_form.findField('UslugaCategory_id').disable();
			uslugaCombo.getStore().filterBy(function(record) {
				if (record.get('UslugaCategory_id') == base_form.findField('UslugaCategory_id').getValue()) {
					return true;
				} else {
					return false;
				}
			});
			uslugaCombo.getStore().baseParams.UslugaCategory_id = base_form.findField('UslugaCategory_id').getValue();
			this.oldUslugaComplex_id = uslugaCombo.getValue();
		}
		
		if ( !base_form.findField('UslugaCategory_id').disabled ) {
			base_form.findField('UslugaCategory_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 600
});
