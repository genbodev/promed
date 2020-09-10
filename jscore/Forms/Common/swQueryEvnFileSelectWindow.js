/**
* swQueryEvnFileSelectWindow - Выбор документа из случая лечения
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       Common
* @copyright    Copyright (c) 2018 Swan Ltd.
* @comment      
*/
sw.Promed.swQueryEvnFileSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Выбор документа из случая лечения',
	id: 'swQueryEvnFileSelectWindow',
	modal: true,
	shim: false,
	width: 700,
	height: 120,
	layout: 'border',
	resizable: false,
	maximizable: false,
	maximized: false,
	
	doSave:  function() {
		var ids = this.form.findField('EvnXml_id').getValue().split(','),
			idlist = [],
			params = {};
		this.form.findField('EvnXml_id').getStore().each(function(item){
			if(item.id.inlist(ids)) {
				idlist.push([item.id, item.get('EvnXml_Name'), item.get('FilePath')]);
			}
		});
		params.EvnXmlList = idlist;
		this.onSelect(params);
		this.hide();
	},
	
	show: function() {
        var win = this;
		sw.Promed.swQueryEvnFileSelectWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].Evn_id) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { win.hide(); });
		}
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.onSelect = arguments[0].onSelect ||  Ext.emptyFn;
		this.Evn_id = arguments[0].Evn_id;
		
		this.form.reset();
		
        var loadMask = new Ext.LoadMask(this.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		var docCombo = this.form.findField('EvnXml_id');
		docCombo.getStore().load({
			params: {Evn_id: this.Evn_id},
			callback: function() {
				loadMask.hide();
			}
		});
	},
	initComponent: function() {
		var win = this;

        var form = new Ext.form.FormPanel({
            frame: true,
			height: 50,
			border: false,
			region: 'center',
            labelAlign: 'right',
            labelWidth: 100,
            bodyStyle: 'padding: 5px 5px 0',
			defaults: {
            	width: 350
			},
            items: [{
                xtype: 'hidden',
                name: 'Evn_id'
            }, new Ext.ux.Andrie.Select({
				fieldLabel: 'Документ',
				multiSelect: true,
				mode: 'local',
				listWidth: 560,
				anchor: '100%',
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
						id: 'EvnXml_id'
					}, [
						{name: 'EvnXml_id', mapping: 'EvnXml_id'},
						{name: 'EvnXml_Name', mapping: 'EvnXml_Name'},
						{name: 'FilePath', mapping: 'FilePath'},
						{name: 'EvnXml_updDT', mapping: 'EvnXml_updDT'},
						{name: 'pmUser_Name', mapping: 'pmUser_Name'}
					]),
					url: '/?c=QueryEvn&m=doLoadEvnXmlList'
				}),
				displayField: 'EvnXml_Name',
				valueField: 'EvnXml_id',
				name: 'EvnXml_id',
				tpl: '<tpl for="."><div class="x-combo-list-item"><table height="20" style="border: 0;"><tr>'+
						'<td><b>{EvnXml_Name}</b> &nbsp;</td>'+
						'<td>{EvnXml_updDT} &nbsp;</td>'+
						'<td>{pmUser_Name}</td>'+
						'</tr></table></div></tpl>',
			})],
			reader: new Ext.data.JsonReader({
				success: function() {}
			}, [
				{name: 'Evn_id'},
			])
        });

		Ext.apply(this, {
			buttons:
			[{
				text: '-'
			}, {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}, {
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
			HelpButton(this, 0)],
			items:[form]
		});
		sw.Promed.swQueryEvnFileSelectWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});