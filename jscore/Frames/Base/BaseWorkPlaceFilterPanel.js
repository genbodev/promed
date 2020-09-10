/**
* Прототип фильтра для АРМов 
*
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      март.2012
*/

sw.Promed.BaseWorkPlaceFilterPanel = Ext.extend(Ext.form.FormPanel, {
	floatable: false,
	autoHeight: true,
	animCollapse: false,
	labelAlign: 'right',
	defaults: {
		bodyStyle: 'background: #DFE8F6;'
	},
	filter: {
		title: lang['filtr'],
		collapsed:true,
		layout: 'column'
	},
	region: 'north',
	frame: true,
	buttonAlign: 'left',
	owner: null, // родительское окно, обязательно должно быть
	initComponent: function() {
		var actions = [];
		var frame = this;

		if (!this.keys) {
			this.keys = [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					this.owner.doSearch();
				}.createDelegate(this),
				stopEvent: true
			}, {
				ctrl: true,
				fn: function(inp, e) {
					this.owner.doReset();
				},
				key: 188,
				scope: this,
				stopEvent: true
			}];
		}

		this.onkeydown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.owner.doSearch();
			}
		}.createDelegate(this);
		
		// ФФ не возвращает коды некоторых клавиш на keydown, поэтому приходится делать так
		this.onkeypress = function (inp, e) {
			// Ctrl + Б
			if ( Ext.isGecko && ( e.getKey() == 1073 || e.getKey() == 1041 ) && e.ctrlKey == true ) {
				this.owner.doReset();
			}
		}.createDelegate(this);
		
		if (!this.filter.items) {
			this.filter.items = [{
				layout: 'form',
				labelWidth: 55,
				items:
				[{
					xtype: 'textfieldpmw',
					width: 120,
					name: 'Search_SurName',
					fieldLabel: lang['familiya'],
					listeners: {
						'keydown': this.onkeydown
					}
				}]
			}, {
				layout: 'form',
				items:
				[{
					xtype: 'textfieldpmw',
					width: 120,
					name: 'Search_FirName',
					fieldLabel: lang['imya'],
					listeners: {
						'keydown': this.onkeydown
					}
				}]
			}, {
				layout: 'form',
				labelWidth: 75,
				items:
				[{
					xtype: 'textfieldpmw',
					width: 120,
					name: 'Search_SecName',
					fieldLabel: lang['otchestvo'],
					listeners: {
						'keydown': this.onkeydown
					}
				}]
			},
			{
					layout: 'form',
				items:
				[{
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'Search_BirthDay',
					fieldLabel: lang['dr'],
					listeners: {
						'keydown': this.onkeydown
					}
				}]
			},
			{
				layout: 'form',
				items:
				[{
					style: "padding-left: 10px",
					xtype: 'button',
					id: this.owner.id+'BtnSearch',
					text: lang['nayti'],
					iconCls: 'search16',
					handler: function()
					{
						this.owner.doSearch();
					}.createDelegate(this)
				}]
			},
			{
				layout: 'form',
				items:
				[{
					style: "padding-left: 10px",
					xtype: 'button',
					id: this.owner.id+'BtnClear',
					text: lang['sbros'],
					iconCls: 'resetsearch16',
					handler: function()
					{
						this.owner.doReset();
					}.createDelegate(this)
				}]
			}, {
				layout: 'form',
				items:
				[{
					style: "padding-left: 10px",
					xtype: 'button',
					text: lang['schitat_s_kartyi'],
					iconCls: 'idcard16',
					handler: function()
					{
						this.owner.readFromCard();
					}.createDelegate(this)
				}]
			}];
		}
		
		this.fieldSet = new Ext.form.FieldSet({
			style:'padding: 0px 3px 3px 6px;',
			autoHeight:true,
			listeners:{
				expand:function () {
					this.ownerCt.doLayout();
					frame.owner.syncSize();
				},
				collapse:function () {
					frame.owner.syncSize();
				}
			},
			collapsible:true,
			//collapsed:(frame.filter.collapsed)?frame.filter.collapsed:true,
			collapsed:(typeof frame.filter.collapsed == 'boolean')?frame.filter.collapsed:true,
			layout: (frame.filter.layout)?frame.filter.layout:'column',
			title: (frame.filter.title)?frame.filter.title:lang['filtr'], 
			bodyStyle:'background: #DFE8F6;',
			items: this.filter.items
		});
		
		var textfields = this.fieldSet.findBy(
			function (record) {
				if ( record.layout!='form' && record.layout!='column' ) {
					return true;
				}
				return false;
			}
		);
		
		Ext.each(textfields, function(f) {
			f.addListener('keypress', this.onkeypress, this);
		}, this);
	
		Ext.apply(this,	{
			items: [this.fieldSet]
		});
		sw.Promed.BaseWorkPlaceFilterPanel.superclass.initComponent.apply(this, arguments);
	}
});