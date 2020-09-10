/**
 * swReportEndUserWindow.js - окно отчетов для конечного пользователя
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Expression package is undefined on line 8, column 19 in Templates/Other/javascript.js.
 * @access       public
 * @copyright    Copyright (c) 2010 Swan Ltd.
 * @author       yunitsky
 * @version      19.08.2010
 */


sw.Promed.swReportEndUserWindow = Ext.extend(sw.Promed.BaseForm, {
	title      : langs('Отчеты'),
	id         : 'ReportEndUserWindow',
	maximized  : true,
	maximizable: false,
	iconCls    : 'rpt-report',
	closable   : true,
	closeAction: 'hide',
	collapsible: true,
	layout     : 'border',
	buttons    : 
	[
		{
			handler: function()
			{
				this.ownerCt.getReplicationInfo();
			},
			iconCls: 'ok16',
			text: langs('Актуальность данных: (неизвестно)')
		},
		{
			id: 'rvwBtnShowReportRun',
			handler: function() 
			{
				this.ownerCt.showReportRun()
			},
			//iconCls: 'queue16',
			text: langs('Очередь/история')
		},
		{
			text: '-'
		},
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : langs('Закрыть'),
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
				this.ownerCt.destroy();
				window[this.ownerCt.objectName] = null;
				delete sw.Promed[this.ownerCt.objectName];
            }
        }
    ],
	setParams: function(data, params) {
		var findField = function(fields, field_id) {
			for (var i=0; i<fields.length; i++) {
				if (fields[i].id == field_id) {
					return fields[i];
				}
			}
			return null;
		}
		for (var key in params) {
			if (Ext.isEmpty(params[key])) {
				continue;
			}
			var field = findField(data.params, key);
			if (field) {
				if (params[key] !== null) {
                    field['default'] = params[key];
				}
				field.disabled = true;
			}
		}
	},
	getReplicationInfo: function () {
		var win = this;
		if (win.buttons[0].isVisible()) {
			win.getLoadMask().show();
			getReplicationInfo('report', function(text) {
				win.getLoadMask().hide();
				win.buttons[0].setText(text);
			});
		}
	},
	showReportRun: function ()
	{
		getWnd('swReportViewWindow').show({});
	},
	getTreePanelDataUrl: function()
	{
		var dataUrl = '/?c=ReportEndUser&m=getTree';

		//var isCallCenter = ((isCallCenterAdmin() && !isLpuAdmin() && !isSuperAdmin) || (!Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && sw.Promed.MedStaffFactByUser.current.ARMType == 'callcenter'));
		//var isHeadNurse = (!Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && sw.Promed.MedStaffFactByUser.current.ARMType == 'headnurse');
		var isCallCenter = ((isCallCenterAdmin() && !isLpuAdmin() && !isSuperAdmin) || (this.ARMType == 'callcenter'));
		var isHeadNurse = (this.ARMType == 'headnurse');
		var isOusSpec = (this.ARMType == 'ouzspec');
		if (isCallCenter) {
			dataUrl = '/?c=ReportEndUser&m=getTreeCC';
		} /*else if (isHeadNurse) {
			dataUrl = '/?c=ReportEndUser&m=getTreeHN';
		}*/
		else if (isOusSpec) {
			dataUrl = '/?c=ReportEndUser&m=getTreeOuzSpec';
		}else if(this.ARMType == 'nmp'){
			dataUrl = '/?c=ReportEndUser&m=getTreeNmp';
		}
		return dataUrl;
	},
	show: function()
	{
		sw.Promed.swReportEndUserWindow.superclass.show.apply(this, arguments);
		this.ARMType = '';
		Ext.getCmp('rvwBtnShowReportRun').setVisible(true);
		//if (isLpuAdmin() || isSuperAdmin() || isUserGroup('OuzSpecMPC')) {
			this.getReplicationInfo();
		//}
		this.ReportParams = null;
		if (arguments[0] && arguments[0].ReportParams) {
			this.ReportParams = arguments[0].ReportParams;
		}
		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		this.treePanel.loader = new Ext.tree.TreeLoader({
			dataUrl: this.getTreePanelDataUrl()
		});

		var root = this.treePanel.getRootNode();
		var w = this;
		this.treePanel.getRootNode().loaded = true;
		this.treePanel.getRootNode().loading = false;
		this.treePanel.getLoader().load(root,function(){
			w.treePanel.getRootNode().expand(false);
		});

	},
    initComponent : function(){
		var wnd = this;

        this.contentPanel = new Ext.Panel({
            region : 'center',
            layout : 'fit'
        });


        this.treePanel = new Ext.tree.TreePanel({
            region : 'west',
            width  : 300,
            title  : langs('Каталог отчетов'),
            split  : true,
			id	   : 'ReportEndUserTree',
            autoScroll:true,
            rootVisible: false,
			root: new Ext.tree.AsyncTreeNode({
				id : 'root',
				expanded : false
			}),
            containerScroll: true,
            selModel : new Ext.tree.DefaultSelectionModel({
                listeners : {
                    selectionchange : {
                        fn : this.onNodeSelect,
                        scope : this
                    }
                }
            })
        });

        Ext.apply(this, {
            items : [ this.contentPanel,this.treePanel ]
        });

        sw.Promed.swReportEndUserWindow.superclass.initComponent.apply(this,arguments);
    },

    onNodeSelect : function(selModel,node){
		var that = this, nodeId = node.id;

		if (node.id.search('#_rr') >= 0) //Отчеты из папки "Мои отчеты" имеют другой префикс #_rr https://redmine.swan.perm.ru/issues/56057
			mt = node.id.match(/#_rr(\d+)/);
		else
			mt = node.id.match(/#rr(\d+)/);
		if (mt) {
			var reportId = mt[1];
			sw.ParamFactory.getReportContent(1, reportId, (function (err, data) {
				if (err) {
					Ext.Msg.alert(langs('Ошибка'), err);
				} else {
					if (data.params.length > 0 && this.ReportParams) {
						this.setParams(data, this.ReportParams);
					}
					var parentNode = node.parentNode.id;
					var myFolderMode = 'add';
					if (parentNode == '#rcmyfolder') //Если нажали на отчет из папки "Мои отчеты" https://redmine.swan.perm.ru/issues/56057
					{
						myFolderMode = 'del';
						var reportCaption = node.attributes.Report_Caption;
						var win = new sw.reports.designer.ui.forms.ReportPanel({
							reportId: reportId,
							reportCaption: reportCaption,
							reportData: data,
							serverId: 1,
							myFolderMode: myFolderMode
						});
						that.contentPanel.removeAll(true);
						that.contentPanel.add(win);
						that.contentPanel.doLayout();
					} else {
						//Проверяем, есть ли уже отчет в папке "Мои отчеты" https://redmine.swan.perm.ru/issues/56057

						Ext.Ajax.request({
							url: '/?c=ReportEndUser&m=getReportFromMyReports',
							params: {
								Report_id: reportId,
								pmUser_id: getGlobalOptions().pmuser_id
							},
							callback: function (opt, success, response) {
								if (success && response.responseText != '') {
									var result = Ext.util.JSON.decode(response.responseText);
									if (result == 'true')
										myFolderMode = 'del';
									else
										myFolderMode = 'add';
								}
								var reportCaption = node.attributes.Report_Caption;
								var win = new sw.reports.designer.ui.forms.ReportPanel({
									reportId: reportId,
									reportCaption: reportCaption,
									reportData: data,
									serverId: 1,
									myFolderMode: myFolderMode
								});
								that.contentPanel.removeAll(true);
								that.contentPanel.add(win);
								that.contentPanel.doLayout();
							}
						});

					}
				}
			}).createDelegate(this));
		}

    }
});

