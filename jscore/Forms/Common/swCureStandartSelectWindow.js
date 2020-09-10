/**
* swCureStandartSelectWindow - окно выбора стандарта лечения для случая, когда для данной нозологии их несколько
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       private
* @class 		sw.Promed.swCureStandartSelectWindow
* @extends 		sw.Promed.BaseForm
* @copyright    Copyright (c) 2009 Swan Ltd.
*/
/*NO PARSE JSON*/

sw.Promed.swCureStandartSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swCureStandartSelectWindow',
	objectSrc: '/jscore/Forms/Common/swCureStandartSelectWindow.js',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	id: 'CureStandartSelectWindow',
	initComponent: function() {
		this.grid = new Ext.grid.GridPanel({
			height: 200,
			id: 'CureStandartSelectGrid',
			region: 'center',
			autoExpandColumn: 'autoExpand',
			autoExpandMin: 140,
			/*
			viewConfig: 
			{
				forceFit: true,
				autoFill: true
			},*/
			colModel: new Ext.grid.ColumnModel({
				columns: [{
                    header: 'ID',
					dataIndex: 'CureStandart_id',
					hidden: true
				}, 
				{
					dataIndex: 'Row_Num',
					header: lang['№'],
					resizable: false,
					sortable: false,
					width: 50
				},
				{
					dataIndex: 'html',
					header: lang['standart'],
					resizable: false,
					sortable: false,
					id: 'autoExpand'
				}]
			}),
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					this.onSelect();
				}.createDelegate(this)
			},
			store: sw.Promed.EvnPrescr.storeCureStandart
		});

		Ext.apply(this, {
			keys: [{
				key: [ Ext.EventObject.ENTER ],
				fn: function() {
					this.onSelect();
				}.createDelegate(this)
			}],
			buttons: [{
			    text: lang['vyibrat'],
			    handler: function(){
					this.onSelect();
			    }.createDelegate(this),
			    iconCls: 'checked16'
			}, {
                text: lang['pechat'],
                handler: function(){
                    this.doPrint();
                }.createDelegate(this),
                iconCls: 'print16'
            }, {
				text: '-'
			}, {
				handler: function()  {
					this.hide()
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			defaults: {
				split: true
			},
			layout: 'border',
			items: [
				this.grid
			]
		});

		sw.Promed.swCureStandartSelectWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function() {
			//
		}
	},
	modal: true,
    _getSelectedCureStandartId: function() {
        var sm = this.grid.getSelectionModel();
        if ( sm.hasSelection() ) {
            var record = sm.getSelected();
            if ( !record || !record.get('CureStandart_id') ) {
                return false;
            }
            return record.get('CureStandart_id');
        } else {
            sw.swMsg.alert(lang['oshibka'], lang['vyiberite_stroku_v_tablitse']);
            return false;
        }
    },
    onSelect: function() {
        var id = this._getSelectedCureStandartId();
        if (id > 0) {
            this.onSelectParams.CureStandart_id = id;
            sw.Promed.EvnPrescr.selectCureStandart(this.onSelectParams);
            this.hide();
        }
    },
    doPrint: function() {
        var id = this._getSelectedCureStandartId();
        if (id > 0) {
            sw.Promed.EvnPrescr.selectCureStandart({
                isForPrint: true,
                CureStandart_id: id
            });
        }
	},
	show: function() {
		sw.Promed.swCureStandartSelectWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].onSelectParams )
		{
			return false;
		}
        this.onSelectParams = arguments[0].onSelectParams;
        this.isForPrint = this.onSelectParams.isForPrint||false;
        this.buttons[1].setVisible(this.isForPrint == false);
	},
	title: lang['vyiberite_standart_lecheniya'],
	width: 700,
	height: 400
});
