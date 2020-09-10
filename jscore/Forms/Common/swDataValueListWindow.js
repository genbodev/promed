/**
* swXmlDataSectionListWindow - окно просмотра списка разделов XmlDataSection
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Alexander Permyakov (alexpm)
* @version      07.2013
* @comment      
*/

/*NO PARSE JSON*/

sw.Promed.swDataValueListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swDataValueListWindow',
	objectSrc: '/jscore/Forms/Common/swDataValueListWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['spisok_razdelov'],
	draggable: true,
	id: 'swXmlDataSectionListWindow',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	//входные параметры
	action: null,
	EvnClass_id:null,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	doReset: function() {
		var	grid = this.viewFrame.getGrid();
		grid.getStore().baseParams = {};
		//this.viewFrame.removeAll(true);
		//this.viewFrame.ViewGridPanel.getStore().removeAll();
        this.viewFrame.setActionDisabled('action_select', true);
	},
	doSearch: function() 
	{
		var	params = {};
		//this.viewFrame.removeAll(true);
		//params.start = 0;
		//params.limit = 100;
        if (!this.hasLoad) {
            this.viewFrame.loadData({globalFilters:params});
            this.hasLoad = true;
        } else {
            this.viewFrame.ViewGridModel.clearSelections();
        }
    },
    onOkButtonClick: function() {
	    var records = this.viewFrame.getGrid().getSelectionModel().getSelections();
		if (!records)
		{
			sw.swMsg.alert(lang['soobschenie'], lang['vyi_nichego_ne_vyibrali_1']);
			return false;
		}
		var data = [];
		for (var i=0; records.length>i; i++) {
            if (records[i].get('XmlDataSection_SysNick')) {
                data.push(records[i].data);
            } else {
                sw.swMsg.alert(lang['soobschenie'], lang['vyi_nichego_ne_vyibrali_2']);
                return false;
            }
		}
		this.onSelect(data);
        this.hide();
        return true;
	},

	initComponent: function() {
		var win = this;

		this.viewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			id: 'XmlDataSectionGrid',
			dataUrl: '/?c=EvnXml&m=loadXmlDataSectionList',
			object: 'XmlDataSection',
			//editformclassname: 'swParameterValueEditWindow',
			actions:
			[
				{name:'action_add', disabled:true, hidden:true },
				{name:'action_edit', disabled:true, hidden:true },
				{name:'action_view', disabled:true, hidden:true },
				{name:'action_delete', disabled:true, hidden:true }
			],
			pageSize: 100,
			paging: false,
			multi:true,
			selectionModel:'multiselect',
			totalProperty: 'totalCount',
			region: 'center',
            rec: null,
			stringfields: [
                { header: 'ID', type: 'int', name: 'XmlDataSection_id', key: true },
                { header: lang['kod'], type: 'int', name: 'XmlDataSection_Code', hidden: true },
                { header: lang['naimenovanie'],  type: 'string', name: 'XmlDataSection_Name', width: 170, isparams: true },
                { header: lang['razdel'],  type: 'string', name: 'XmlDataSection_SysNick', id: 'autoexpand', isparams: true },
				{ header: lang['znachenie_po_umolchaniyu'],  type: 'string', name: 'defaultValue', editor:new Ext.form.TextField({}), width: 250 }
			],
			onAfterEditSelf: function(o) {
				o.value = o.rawvalue;
				o.record.set('defaultValue',o.rawvalue);
				o.record.commit();
			},
			onLoadData: function(flag) {
				if (flag) //this.ViewGridPanel.getStore().getCount()>0
				{
					if (this.selectionModel!='cell') {
						
						this.onRowSelect(this.ViewGridPanel.getSelectionModel(),0,this.ViewGridPanel.getSelectionModel().getSelected());
						this.ViewGridModel.clearSelections();
					}
				}
			},
			onRowSelect: function(sm,rowIdx,record) {
                this.setActionDisabled('action_select',(!record.get('XmlDataSection_SysNick')));
                if (record.get('XmlDataSection_SysNick')) {
                    this.rec = record;
                } else {
                    this.rec = null;
                }
			},
			onDblClick: function() {
                if (this.rec) {
                    this.getGrid().getSelectionModel().selectRecords([this.rec]);
                    win.onOkButtonClick();
                }
			}
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [
				this.viewFrame
			]
		});
		sw.Promed.swDataValueListWindow.superclass.initComponent.apply(this, arguments);
        this.viewFrame.ViewToolbar.on('render', function(vt){
            this.ViewActions['action_select'] = new Ext.Action({
                name:'action_select',
                handler: function() { win.onOkButtonClick(); },
                text:lang['vyibrat'],
                tooltip: lang['vyibrat_i_vstavit_v_shablon'],
                iconCls : 'ok16'
            });
            vt.insertButton(1,this.ViewActions['action_select']);
            return true;
        }, this.viewFrame);
	},

	show: function() {
		sw.Promed.swDataValueListWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.onSelect = arguments[0].onSelect || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		
        this.viewFrame.ViewActions['action_select'].setHidden(typeof arguments[0].onSelect != 'function');
		
		this.doReset();
		this.doSearch();
	}
});