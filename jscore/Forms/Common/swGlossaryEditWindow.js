/**
* swGlossaryEditWindow - окно просмотра, добавления и редактирования записей глоссария
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Aleksandr Permyakov (alexpm)
* @version      08.06.2011
* @comment      tabIndex: TABINDEX_GL + (от 21 до 40)
*/

/*NO PARSE JSON*/
sw.Promed.swGlossaryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swGlossaryEditWindow',
	objectSrc: '/jscore/Forms/Common/swGlossaryEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swGlossaryEditWindow',
	width: 600,
	height: 457,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	submit: function() {
		var win = this,
			form = this.formPanel.getForm(),
			tag_combo = form.findField('GlossaryTagType_id'),
			params = {};//form.getValues();

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		win.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				var tag_record = tag_combo.getStore().getById(tag_combo.getValue());
				var data = {};
				data.Glossary_id = action.result.Glossary_id;
				data.GlossaryTagType_Name = tag_combo.getRawValue();
				data.GlossaryTagType_id = tag_combo.getValue();
				data.GlossaryTagType_SysNick = (tag_record && tag_record.get('GlossaryTagType_SysNick')) || '';
				data.Glossary_Word = form.findField('Glossary_Word').getValue();
				data.GlossarySynonym_id = form.findField('GlossarySynonym_id').getValue() || action.result.GlossarySynonym_id;
				data.pmUser_did = form.findField('pmUser_did').getValue();
				data.Glossary_Descr = form.findField('Glossary_Descr').getValue();
				/*
				if(win.action == 'add')
				{
					sw.Promed.Glossary.store.insertLocal(data);
				}
				else
				{
					sw.Promed.Glossary.store.updateLocal(data);
				}
				*/
				if(win.owner && win.owner.id == 'GlossaryGrid')
				{
					win.callback(win.owner,action.result.Glossary_id);
				}
				else
				{
					win.callback(data);
				}
			}
		});
	},
	allowEdit: function(is_allow) {
		var win = this,
			form = this.formPanel.getForm(),
			save_btn = this.buttons[0],
			word_fld = form.findField('Glossary_Word'),
			desc_fld = form.findField('Glossary_Descr'),
			syn_combo = form.findField('GlossarySynonym_id'),
			tag_combo = form.findField('GlossaryTagType_id');
		word_fld.setDisabled(!is_allow);
		desc_fld.setDisabled(!is_allow);
		syn_combo.setDisabled(!is_allow);
		tag_combo.setDisabled(!is_allow);
		if (is_allow)
		{
			word_fld.focus(true, 250);
			save_btn.show();
		}
		else
		{
			save_btn.hide();
		}
	},
	getTagComboParams: function() {
		var tag_combo_params = {},
			filter = 'where (1=1) ',
			form = this.formPanel.getForm(),
			tag_combo = form.findField('GlossaryTagType_id'),
			GlossaryTagType_id = tag_combo.getValue();
		/*
		if(EvnClass_id)
		{
			filter = filter + 'and EvnClass_id = ' + EvnClass_id;
		}
		*/
		if(GlossaryTagType_id)
		{
			filter = filter + 'and GlossaryTagType_id = ' + GlossaryTagType_id;
		}
		if(this.GlossaryTagType_SysNick)
		{
			filter = " where GlossaryTagType_SysNick = '" + this.GlossaryTagType_SysNick +"'";
			tag_combo.getStore().removeAll();
			delete tag_combo.lastQuery;
			tag_combo.getStore().load({params: {where: filter}});
			if(tag_combo.getStore().getCount() == 0)
				filter = '';
		}
		if(filter.length > 15)
		{
			tag_combo_params.where = filter;
		}
		return tag_combo_params;
	},
	getSynComboParams: function(p) {
		var combo_params = {},
			form = this.formPanel.getForm(),
			GlossarySynonym_id = form.findField('GlossarySynonym_id').getValue(),
			GlossaryTagType_id = form.findField('GlossaryTagType_id').getValue();
		if(GlossarySynonym_id)
		{
			combo_params.Glossary_id = GlossarySynonym_id;
		}
		if(GlossaryTagType_id)
		{
			combo_params.GlossaryTagType_id = GlossaryTagType_id;
		}
		return combo_params;
	},

	initComponent: function() {
		var win = this;
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'GlossaryRecordEditForm',
			labelAlign: 'left',
			labelWidth: 120,
			region: 'center',
			items: [{
				anchor: '100%',
				allowBlank: false,
				fieldLabel: lang['fraza_termin'],
				name: 'Glossary_Word',
				id: 'GREW_Glossary_Word',
				maxLength: 892,
				tabIndex: TABINDEX_GL + 21,
				xtype: 'textfield'
			}, {
				anchor: '100%',
				fieldLabel: lang['sinonim_termina'],
				hiddenName: 'GlossarySynonym_id',
				tabIndex: TABINDEX_GL + 23,
				enableKeyEvents: true,
				xtype: 'swglossarysynonymcombo'
			}, {
				anchor: '100%',
				fieldLabel: lang['kontekst_termina'],
				hiddenName: 'GlossaryTagType_id',
				comboSubject: 'GlossaryTagType',
				allowSysNick: true,
				autoLoad: false,
				tabIndex: TABINDEX_GL + 25,
				xtype: 'swcommonsprcombo'
			}, {
				anchor: '100%',
				height: 300,
				fieldLabel: lang['tolkovanie'],
				name: 'Glossary_Descr',
				tabIndex: TABINDEX_GL + 27,
				xtype: 'textarea'
			}, {
				name: 'Glossary_id',
				xtype: 'hidden'
			}, {
				name: 'pmUser_did',
				xtype: 'hidden'
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
								this.submit();
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
				{ name: 'Glossary_id' },
				{ name: 'Glossary_Word' },
				{ name: 'Glossary_Descr' },
				{ name: 'pmUser_did' },
				{ name: 'GlossarySynonym_id' },
				{ name: 'GlossaryTagType_id' }
			]),
			timeout: 600,
			url: '/?c=Glossary&m=saveRecord'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_GL + 29,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				onTabElement: 'GREW_Glossary_Word',
				tabIndex: TABINDEX_GL + 31,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swGlossaryEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swGlossaryEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		this.GlossaryTagType_SysNick = arguments[0].GlossaryTagType_SysNick || null;

		this.doReset();
		this.center();

		var win = this,
			form = this.formPanel.getForm(),
			syn_combo = form.findField('GlossarySynonym_id'),
			tag_combo = form.findField('GlossaryTagType_id');

		syn_combo.setValue(null);
		syn_combo.setRawValue(null);
		form.setValues(arguments[0]);
		switch (this.action) {
			case 'view':
				this.setTitle(lang['prosmotr_zapisi_glossariya']);
			break;

			case 'edit':
				this.setTitle(lang['redaktirovanie_zapisi_glossariya']);
			break;

			case 'add':
				this.setTitle(lang['dobavlenie_zapisi_glossariya']);
			break;

			default:
				log('swGlossaryEditWindow - action invalid');
				return false;
			break;
		}
		
		var loadCombo = function()
		{
			syn_combo.getStore().removeAll();
			syn_combo.lastQuery = null;
			syn_combo.getStore().baseParams = {};
			syn_combo.getStore().lastOptions = {};
			syn_combo.getStore().load({
				params: win.getSynComboParams(),
				callback: function(r,o,s){
					if(r.length == 1)
					{
						syn_combo.setValue(r[0].get('GlossarySynonym_id'));
						syn_combo.fireEvent('select', syn_combo, r[0], 0);
					}
				}
			});
			tag_combo.getStore().removeAll();
			tag_combo.lastQuery = null;
			//tag_combo.getStore().baseParams = {};
			tag_combo.getStore().lastOptions = {};
			tag_combo.getStore().load({
				params: win.getTagComboParams(),
				callback: function(r,o,s){
					if(r.length == 1)
					{
						tag_combo.setValue(r[0].get('GlossaryTagType_id'));
						tag_combo.fireEvent('select', tag_combo, r[0], 0);
					}
				}
			});
		}

		if(this.action == 'add')
		{
			win.allowEdit(true);
			loadCombo();
			this.syncSize();
			this.doLayout();
		}
		else
		{
			win.allowEdit(false);
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
				},
				params: {
					Glossary_id: form.findField('Glossary_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
					if(win.action == 'edit')
					{
						win.allowEdit(true);
					}
					loadCombo();
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Glossary&m=getRecord'
			});
		}
	}
});
