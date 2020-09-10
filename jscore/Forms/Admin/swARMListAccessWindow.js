/**
 * swARMListAccessWindow - окно со списком армов и правами доступа для этих армов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2012, Swan.
 * @author       Storozhev Dmitry
 * @version      05.09.2012 
 */

sw.Promed.swARMListAccessWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 500,
	width: 800,
	id: 'swARMListAccessWindow',
	resizable: false,
	modal: true,
	title: lang['armyi_prava_dostupa'],
	show: function() {
		sw.Promed.swARMListAccessWindow.superclass.show.apply(this, arguments);

		if( !arguments[0] || (!arguments[0].Report_id && !arguments[0].ReportContentParameter_id) ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], this.hide.createDelegate(this));
			return false;
		}
		if (arguments[0].ReportContentParameter_id) {
			this.idField = 'ReportContentParameter_id';
		} else {
			this.idField = 'Report_id';
		}

        this[this.idField] = arguments[0][this.idField];
        this.Grid.ViewGridPanel.getStore().baseParams = {};
		this.Grid.ViewGridPanel.getStore().baseParams[this.idField] = arguments[0][this.idField];
		this.Grid.ViewGridPanel.getStore().baseParams['idField'] = this.idField;
		this.Grid.ViewGridPanel.getStore().load();	
	},
	
	doSave: function(o) {
		var rec = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if(!rec) return false;
		
		Ext.Ajax.request({
			url: '/?c=User&m=saveReportARM'
		});
	},

    changeARMAccess: function(action) {
        var that = this;
        var params = {action: action};

        params[this.idField] = this[this.idField];
        params['idField'] = this.idField;

        Ext.Ajax.request({
            url: '/?c=User&m=saveReportARMAccessAll',
            params: params,
            callback: function(o, s, r) {
                that.Grid.ViewGridPanel.getStore().baseParams[that.idField] = that[that.idField];
                that.Grid.ViewGridPanel.getStore().load();
            }
        });
    },

	initComponent: function() {
		var	win = this;

		this.Grid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			id: this.id + '_Grid',
			autoScroll: true,
			autoLoadData: false,
			root: 'data',
			paging: true,
			toolbar: false,
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_save', url: '/?c=User&m=saveReportARM' },
				{ name: 'action_print', hidden: true }
			],
			saveAllParams: true,
			stringfields: [
				{ name: 'ARMType_id', type: 'int', hidden: true, key: true },
				{ name: 'Report_id', isparams: true, type: 'int', hidden: true },
				{ name: 'ReportContentParameter_id', isparams: true, type: 'int', hidden: true},
				{ name: 'ReportARM_id', type: 'int', isparams: true, hidden: true },
				{ name: 'ReportContentParameterLink_id', type: 'int', isparams: true, hidden: true},
				{ name: 'ARMType_Code', type: 'int', isparams: true, hidden: true },
				{ name: 'ARMType_Name', id: 'autoexpand', type: 'string', header: lang['naimenovanie_arma'] },
				{ name: 'isAccess', header: lang['dostup'], type: 'checkcolumnedit', width: 150 }
			],
			saveRecord: function(o) {
				var record = o.record,
					store = o.grid.getStore(),
					sm = o.grid.getSelectionModel();
				sm.selectRow(store.indexOf(record), true);
				var params = record.data;
				params[win.idField] = win[win.idField];
				params['idField'] = win.idField;

				Ext.Ajax.request({
					url: '/?c=User&m=saveReportARM',
					params: params,
					callback: function(o, s, r) {
						if( s ) {
							var obj = Ext.decode(r.responseText);
							if( obj.success ) {
								record.set('isAccess', record.get('isAccess'));
								record.commit();
							}
						}
					}
				});
			},
			dataUrl: '/?c=User&m=loadARMAccessGrid',
			totalProperty: 'totalCount'
		});
		
		Ext.apply(this, {
			layout: 'fit',
			buttons: [
            {
                handler: function(){
                    var that = this;
                    this.changeARMAccess('add');
                }.createDelegate(this),
                text: lang['vyibrat_vse']
            },
            {
                handler: function(){
                    var that = this;
                    this.changeARMAccess('remove');
                }.createDelegate(this),
                text: lang['snyat_vse']
            },
            {
				text: '-'
			}, {
				handler: this.doSave.createDelegate(this),
				iconCls: 'save16',
				hidden: true,
				text: lang['sohranit']
			}, {
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: this.hide.createDelegate(this, [])
			}],
			items:[this.Grid]
		});
		sw.Promed.swARMListAccessWindow.superclass.initComponent.apply(this, arguments);
	}
});

