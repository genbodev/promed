/**
* swPolkaEvnPrescrLabDiagEditWindow - окно добавления/редактирования назначения c типом Лабораторная диагностика.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      0.001-15.03.2012
* @comment      Префикс для id компонентов EPRLDEF (PolkaEvnPrescrLabDiagEditForm)
*/
/*NO PARSE JSON*/

sw.Promed.swPolkaEvnPrescrLabDiagEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swPolkaEvnPrescrLabDiagEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swPolkaEvnPrescrLabDiagEditWindow.js',

	action: null,
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	autoHeight: true,
	width: 550,
	closable: true,
	closeAction: 'hide',
	split: true,
	layout: 'form',
	id: 'PolkaEvnPrescrLabDiagEditWindow',
	modal: true,
	plain: true,
	resizable: false,
	listeners: 
	{
		hide: function(win) 
		{
			win.onHide();
		},
		beforeshow: function(win)
		{
			//
		}
	},
	doSave: function(options) 
	{var that = this;
		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

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

		var params = new Object();
		params.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
		params.signature = (options.signature)?1:0;
		params.UslugaComplex_id= base_form.findField('UslugaComplex_id').getValue();
		var nodes = this.uslugaTree.getChecked();	
		var checked = [];
		for (i=0; i < nodes.length; i++)
		{
			if (nodes[i].childNodes.length == 0) {
				checked.push(nodes[i].attributes.id);
			}
		}
		base_form.findField('EvnPrescrLabDiag_uslugaList').setValue(checked.toString());
		
		this.formStatus = 'save';
		
	if(this.mode=='nosave'){
			var data = new Object();
			data = base_form.getValues();
			data.Usluga_List = base_form.findField('UslugaComplex_id').lastSelectionText;
			UslugaComplex_id= base_form.findField('UslugaComplex_id').getValue();
			this.callback(data);
			this.hide();
		}
		else{
			this.getLoadMask(LOAD_WAIT_SAVE).show();
			base_form.submit({
				failure: function(result_form, action) {
					this.formStatus = 'edit';
					this.getLoadMask().hide();

					if ( action.result ) {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this),
				params: params,
				success: function(result_form, action) {
					this.formStatus = 'edit';
					this.getLoadMask().hide();

					if ( action.result ) {
						var data = new Object();
						
						if(that.winForm=='uslugaInput'){
							data = base_form.getValues();
							data.Usluga_List = base_form.findField('UslugaComplex_id').lastSelectionText;
							UslugaComplex_id= base_form.findField('UslugaComplex_id').getValue();
						}
						else{
						data.EvnPrescrLabDiagData = base_form.getValues();
						data.EvnPrescrLabDiagData.EvnPrescrLabDiag_id = action.result.EvnPrescrLabDiag_id;
						}
						this.callback(data);
						this.hide();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
					}
				}.createDelegate(this)
			});
		}
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	/** Функция относительно универсальной загрузки справочников выбор в которых осуществляется при вводе букв (цифр)
	 * Пример загрузки Usluga:
	 * loadSpr('Usluga_id', { where: "where UslugaType_id = 2 and Usluga_id = " + Usluga_id });
	 */
	loadSpr: function(field_name, params, callback)
	{
		var bf = this.FormPanel.getForm();
		var combo = bf.findField(field_name);
		var value = combo.getValue();
		
		combo.getStore().removeAll();
		combo.getStore().load(
		{
			callback: function() 
			{
				combo.getStore().each(function(record) 
				{
					if (record && record.data[field_name] == value)
					{
						combo.setValue(value);
						combo.fireEvent('select', combo, record, combo.getStore().indexOfId(value));
					}
				});
				if (callback)
				{
					callback();
				}
			},
			params: params 
		});
	},
	
	show: function() 
	{
		sw.Promed.swPolkaEvnPrescrLabDiagEditWindow.superclass.show.apply(this, arguments);
		this.center();
		
		var base_form = this.FormPanel.getForm();
		base_form.reset();
        var uslugacomplex_combo = base_form.findField('UslugaComplex_id');
        uslugacomplex_combo.getStore().removeAll();
        uslugacomplex_combo.clearBaseParams();
		var root_node = this.uslugaTree.getRootNode();
        while (root_node.childNodes.length > 0) {
            root_node.removeChild( root_node.childNodes[0] );
        }

		this.parentEvnClass_SysNick = null;
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.mode = 'save';
		this.winForm = null;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		
		base_form.setValues(arguments[0].formParams);
		
		if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
			this.action = arguments[0].action;
		}
		
		if ( arguments[0].parentEvnClass_SysNick && typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
			this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
		}
		
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		if ( arguments[0].winForm && typeof arguments[0].winForm == 'string' ) {
            this.winForm = arguments[0].winForm;
        }
		if ( arguments[0].formParams.mode && typeof arguments[0].formParams.mode == 'string' ) {
            this.mode = arguments[0].formParams.mode;
        }
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['naznachenie_laboratornoy_diagnostiki_dobavlenie']);
				uslugacomplex_combo.setUslugaComplexDate(base_form.findField('EvnPrescrLabDiag_setDate').getRawValue());
				this.setFieldsDisabled(false);
				base_form.findField('EvnPrescrLabDiag_setDate').focus(true, 250);
				break;
			case 'edit':
			case 'view':
				this.getLoadMask(LOAD_WAIT).show();
				
				if(this.mode=='nosave'){
					this.getLoadMask().hide();
					base_form.clearInvalid();
					this.loadSpr('UslugaComplex_id', {UslugaComplex_id: uslugacomplex_combo.getValue()}, function() {
							//
					}.createDelegate(this));
					this.setTitle(lang['naznachenie_laboratornoy_diagnostiki_redaktirovanie']);
					uslugacomplex_combo.setUslugaComplexDate(base_form.findField('EvnPrescrLabDiag_setDate').getRawValue());
					this.setFieldsDisabled(false);
					base_form.findField('EvnPrescrLabDiag_setDate').focus(true, 250);
				}
				else{
				base_form.load({
					failure: function() {
						this.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {  }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnPrescrLabDiag_id': base_form.findField('EvnPrescrLabDiag_id').getValue()
						,'parentEvnClass_SysNick': this.parentEvnClass_SysNick
					},
					success: function(frm, act) {
						this.getLoadMask().hide();
						base_form.clearInvalid();
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}
						this.loadSpr('UslugaComplex_id', {UslugaComplex_id: uslugacomplex_combo.getValue()}, function() {
							//
						}.createDelegate(this));

						if ( this.action == 'edit' ) {
							this.setTitle(lang['naznachenie_laboratornoy_diagnostiki_redaktirovanie']);
							uslugacomplex_combo.setUslugaComplexDate(base_form.findField('EvnPrescrLabDiag_setDate').getRawValue());
							this.setFieldsDisabled(false);
							base_form.findField('EvnPrescrLabDiag_setDate').focus(true, 250);
						}
						else {
							this.setTitle(lang['naznachenie_laboratornoy_diagnostiki_prosmotr']);
							this.setFieldsDisabled(true);
						}
						if(this.winForm=='uslugaInput'){
							this.FormPanel.getForm().findField('UslugaComplex_id').disable();
						}else{
							this.FormPanel.getForm().findField('UslugaComplex_id').enable();
						}
					}.createDelegate(this),
					url: '/?c=EvnPrescr&m=loadEvnPrescrLabDiagEditForm'
				});}
				break;
			default:
				this.hide();
				break;
		}
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		
		this.uslugaTree = new Ext.tree.TreePanel({
			title: lang['sostav_kompleksnoy_uslugi'],
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
				//clearOnLoad: true,
				listeners:
				{
					load: function(tr, node, response)
					{
						var uslugalist_str = this.FormPanel.getForm().findField('EvnPrescrLabDiag_uslugaList').getValue();
						var uslugalist_arr = (typeof uslugalist_str == 'string' && uslugalist_str.length > 0)?uslugalist_str.split(','):[];
						var nodes = node.childNodes || [];	
						for (var i=0; i < nodes.length; i++)
						{
							if (nodes[i].childNodes.length == 0 && (uslugalist_arr.length == 0 || nodes[i].attributes.id.toString().inlist(uslugalist_arr))) {
								this.uslugaTree.fireEvent('checkchange', nodes[i], true);
							}
						}
					}.createDelegate(this),
					beforeload: function (tl, node)
					{
						//this.uslugaTree.getLoadTreeMask('Загрузка дерева услуг... ').show();
						var uslugacomplex_combo = this.FormPanel.getForm().findField('UslugaComplex_id');
						tl.baseParams.level = node.getDepth();
						tl.baseParams.check = 1;					
						
						if (node.getDepth()==0)
						{
							if(uslugacomplex_combo.getValue()>0)
								tl.baseParams.UslugaComplex_pid = uslugacomplex_combo.getValue();
							else
								return false;
						}
						else
						{
							tl.baseParams.UslugaComplex_pid = node.attributes.object_value;
						}
					}.createDelegate(this)
				}
			}),
			changing: false,
			listeners: 
			{
				'checkchange': function (node, checked)
				{
					if (!this.changing)
					{
						this.changing = true;
						//node.expand(true, false);
						if (checked)
							node.cascade( function(node){node.getUI().toggleCheck(true)} );
						else
							node.cascade( function(node){node.getUI().toggleCheck(false)} );
						node.bubble( function(node){if (node.parentNode) node.getUI().updateCheck()} );
						this.changing = false;
					}
				}.createDelegate(this.uslugaTree)
			}
		});
		
		
		this.FormPanel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PolkaEvnPrescrLabDiagEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			region: 'center',
			items: 
			[{
				name: 'accessType', // Режим доступа
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrLabDiag_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'EvnPrescrLabDiag_pid',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'PersonEvn_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'Server_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'EvnPrescrLabDiag_uslugaList',
				value: null,
				xtype: 'hidden'
			}, 
			{
				allowBlank: false,
				fieldLabel: lang['planovaya_data'],
				format: 'd.m.Y',
				name: 'EvnPrescrLabDiag_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				onChange: function(field, newValue, oldValue) {
					var date_str = field.getRawValue() || null;
					this.FormPanel.getForm().findField('UslugaComplex_id').setUslugaComplexDate(date_str);
				}.createDelegate(this),
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				value: null,
				fieldLabel: lang['usluga'],
				hiddenName: 'UslugaComplex_id',
				anchor:'99%',
				PrescriptionType_Code: 11,
				xtype: 'swuslugacomplexevnprescrcombo',
				listeners: 
				{
					select: function(combo,record,index)
					{
						this.uslugaTree.getLoader().load(
							this.uslugaTree.getRootNode(), 
							function () {
								// this.uslugaTree.getRootNode().expand(true);
							}.createDelegate(this)
						);
						
					}.createDelegate(this)
				}
			}, 
			this.uslugaTree 
			,{
				boxLabel: 'Cito',
				checked: false,
				fieldLabel: '',
				labelSeparator: '',
				name: 'EvnPrescrLabDiag_IsCito',
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnPrescrLabDiag_Descr',
				width: 390,
				xtype: 'textarea'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
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
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{name: 'accessType' },
				{name: 'EvnPrescrLabDiag_id'},
				{name: 'EvnPrescrLabDiag_pid'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'EvnPrescrLabDiag_setDate'},
				{name: 'EvnPrescrLabDiag_IsCito'},
				{name: 'EvnPrescrLabDiag_Descr'},
				{name: 'EvnPrescrLabDiag_uslugaList'},
				{name: 'UslugaComplex_id'}
			]),
			timeout: 600,
			url: '/?c=EvnPrescr&m=saveEvnPrescrLabDiag'
		});
		
		Ext.apply(this, 
		{
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
                hidden: true,
				handler: function() {
					this.doSave({signature: true});
				}.createDelegate(this),
				iconCls: 'signature16',
				text: BTN_FRMSIGN
			}, {
				text: '-'
			},
			//HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				onTabAction: function () {
					this.FormPanel.getForm().findField('EvnPrescrLabDiag_setDate').focus(true, 250);
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});
		sw.Promed.swPolkaEvnPrescrLabDiagEditWindow.superclass.initComponent.apply(this, arguments);
	}
});