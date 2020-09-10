/**
* swAuditWindow - окно аудита (информация о том, кто и когда произвел последнее изменение записи)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      26.07.2010
*/

sw.Promed.swAuditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 250,
	id: 'AuditWindow',
	successFn: null, // функция вызывающаяся при удачном объединении
	// Функция объединения записей
	initComponent: function() {

		Ext.apply(this, {
			buttons: [
				HelpButton(this, TABINDEX_AF + 10),
				{
					handler: function() {
						this.ownerCt.returnFunc();
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					tabIndex: TABINDEX_AF + 11,
					text: BTN_FRMCLOSE
				}
			],
			items: [
				new Ext.form.FormPanel({
					id : 'AuditForm',
					height : 185,
					layout : 'form',
					border : false,
					frame : true,
					style : 'padding: 10px',
					labelWidth : 100,
					items : [
						{
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['dobavlenie_zapisi'],
							style: 'padding: 5px; margin-bottom: 5px',
							items: [
								{
									fieldLabel: lang['polzovatel'],
									name: 'InspmUser',
									readOnly: true,
									tabIndex: TABINDEX_AF + 3,
									xtype: 'textfield',
									width: 400
								}, {
									fieldLabel: lang['data'],
									name: 'InsDate',
									readOnly: true,
									tabIndex: TABINDEX_AF + 4,
									xtype: 'textfield',
									width: 140
								}
							]
						}, {
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['izmenenie_zapisi'],
							style: 'padding: 5px; margin-bottom: 5px',
							items: [
								{
									fieldLabel: lang['polzovatel'],
									name: 'UpdpmUser',
									readOnly: true,
									tabIndex: TABINDEX_AF + 1,
									xtype: 'textfield',
									width: 400
								}, {
									fieldLabel: lang['data'],
									name: 'UpdDate',
									readOnly: true,
									tabIndex: TABINDEX_AF + 2,
									xtype: 'textfield',
									width: 140
								}
							]
						}
					],
					reader: new Ext.data.JsonReader({
						success: Ext.emptyFn
					}, [
						{ name: 'UpdpmUser' },
						{ name: 'UpdDate' },
						{ name: 'InspmUser' },
						{ name: 'InsDate' }
					]),
					url : C_GET_AUDIT
				})
			]
		});
		sw.Promed.swAuditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: false,
	minHeight: 250,
	minWidth: 600,
	modal: false,
	plain: true,
	resizable: false,
	returnFunc: Ext.emptyFn,
	show: function() {
		sw.Promed.swAuditWindow.superclass.show.apply(this, arguments);

		this.deleted = 0;
		this.schema = '';
		this.onHide = Ext.emptyFn;

		if (arguments[0])
		{
			if (arguments[0].callback)
			{
				this.returnFunc = arguments[0].callback;
			}

			if (arguments[0].onHide)
			{
				this.onHide = arguments[0].onHide;
			}

			if (arguments[0].deleted)
			{
				this.deleted = arguments[0].deleted
			}

			if (arguments[0].schema)
			{
				this.schema = arguments[0].schema
			}

			if (arguments[0].key_id)
			{
				this.key_id = arguments[0].key_id
			}

			/*
			* значение Server_id
			* для случая с PersonEvn
			*/
			this.Server_id = null;
			if ( typeof arguments[0]['Server_id'] != 'undefined' )
			{
				this.Server_id = arguments[0]['Server_id'];
			}

			if (arguments[0].key_field)
			{
				this.key_field = arguments[0].key_field;
			}

			if(arguments[0].registry_id)
			{
				this.registry_id = arguments[0].registry_id;
			}
			//alert('1');
			//log(arguments[0]);
			//alert('2');
		}

		this.restore();
		this.center();

		var params = {
			deleted: this.deleted,
			schema: this.schema,
			key_id: this.key_id,
			key_field: this.key_field,
			registry_id: this.registry_id
		};
		
		if ( typeof this.Server_id != 'undefined' )
			params.Server_id = this.Server_id;

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		
		this.findById('AuditForm').getForm().reset();
		this.findById('AuditForm').getForm().load({
			url: C_GET_AUDIT,
			params: params,
			success: function (form, action)
			{
				loadMask.hide();
				result = Ext.util.JSON.decode(action.response.responseText);
				if ( result[0].success != undefined && !result[0].success )
					Ext.Msg.alert("Ошибка", result[0].Error_Msg);
			},
			failure: function (form, action)
			{
				loadMask.hide();
				Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
			}
		});
	},
	title: lang['audit_zapisi'],
	width: 600
});
