/**
* swReportCopyWindow - окно настроек отчёта.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Report
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      0.001-05.12.2011
* @comment      Префикс для id компонентов RSettF (ReportSettingsForm)
*
*
* @input data: Report_id - ID отчёта
*/

sw.Promed.swReportCopyWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 300,
	id: 'ReportCopyWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('ReportSettingsWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 200,
	minWidth: 300,
	modal: true,
	reportId: null,
	plain: true,
	resizable: true,
	serverId: null,
	transeReport: function(params){
		var url = sw.consts.url(sw.consts.actions.SET_CATALOG_AJAX);
		Ext.Ajax.request({
			url    : url,
			params : params,
			success : function(response,d){
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj.success){
					sw4.showInfoMsg({
						type: 'success',
						text: 'Отчет успешно перенесен'
					});
				}
				else{
					alert(langs('Ошибка перемещения отчета'));
				}
			}
		});
	},
	copyReportAjax: function(params){
		var url = sw.consts.url(sw.consts.actions.COPY_REPORT_AJAX);
		Ext.Ajax.request({
			url    : url,
			params : params,
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					sw4.showInfoMsg({
						type: 'success',
						text: 'Отчет успешно скопирован'
					});
					if (!Ext.isEmpty(result['existRParamNames'])) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {},
							icon: Ext.Msg.WARNING,
							msg: 'Параметры '+result['existRParamNames']+' уже существуют на регионе. Необходимо проверить соответствие свойств.',
							title: 'Внимание!'
						});
					}
				} else {
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка копирования отчета. Попробуйте повторить операцию.'));
				}
			}
		});
	},
	copyReport: function(){
		var me = this,
			tree = this.Tree,
			sm = tree.getSelectionModel(),
			selNode = sm.getSelectedNode();
		var isRegionNode = (selNode && selNode.parentNode && selNode.parentNode.attributes
							&& selNode.parentNode.attributes.id !== 'catalogroot');
		var isRootNode = (selNode && selNode.attributes && selNode.attributes.id !== 'catalogroot');
		if(selNode && isRegionNode && isRootNode && selNode.attributes){
			var attr = selNode.attributes;
			var params = {
				ReportCatalog_id: attr.ReportCatalog_id,
				Report_id: me.reportId,
				Region_id: attr.Region_id
			};
			if(me.reportNode.attributes.region_id === attr.Region_id){
				me.transeReport(params);
			} else {
				me.copyReportAjax(params);
			}
		} else {
			sw4.showInfoMsg({
				type: 'error',
				text: 'Выберите доступный для копирования каталог'
			});
		}
	},
	show: function() {
		sw.Promed.swReportCopyWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.reportId = null;
		this.serverId = null;

		if ( arguments[0] ) {
			if ( arguments[0].callback ) {
				this.callback = arguments[0].callback;
			}

			if ( arguments[0].onHide ) {
				this.onHide = arguments[0].onHide;
			}

			if ( arguments[0].reportId ) {
				this.reportId = arguments[0].reportId;
			}

			if ( arguments[0].serverId ) {
				this.serverId = arguments[0].serverId;
			}

			if ( arguments[0].reportNode ) {
				this.reportNode = arguments[0].reportNode;
			}

		}

		this.Tree.getSelectionModel().selNode = false;

		this.searchDiagCode = '';
		this.searchDiagName = '';

		var Mask = new Ext.LoadMask(Ext.get('ReportCopyWindow'), { msg: LOAD_WAIT });
		Mask.show();
		var root = this.Tree.getRootNode();
		this.Tree.getLoader().load(root, function() {
			Mask.hide();
		});
		root.expand();
	},
	title: langs('Копировать в папки:'),
	width: 500,
	initComponent: function() {
		var me = this;
		this.Tree = new Ext.tree.TreePanel({
			paging: false,
			region: 'center',
			id: 'ReportCatalogTree',
			autoScroll: true,
			loaded: false,
			border: false,
			rootVisible: false,
			lastSelectedId: 0,
			root: {
				objectType : 'root',
				nodeType: 'async',
				text: langs('Сервер БД'),
				id: 'root',
				expanded: false
			},
			loader: new Ext.tree.TreeLoader({
				listeners:
					{
						'beforeload': function (tl, node)
						{
							return !!(me.reportId || me.serverId);
						}.createDelegate(this)
					},
				dataUrl: '/?c=ReportEngine&m=getServerTree',
				baseParams : {serverId : 1, objectType : ''},
				requestMethod : 'POST',
				url : '/?c=ReportEngine&m=getServerTree',
				// Переопределим функцию передачи параметров, т.к. нам нужны дополнительные
				getParams: function(node){
					var buf = [], bp = this.baseParams;
					//debugger;
					for(var key in bp){
						if(typeof bp[key] != "function"){
							buf.push(encodeURIComponent(key), "=", encodeURIComponent(bp[key]), "&");
						}
					}

					// Передадим на сервер параметры поиска, уровень и id node

					var objectType = '';
					if (node.attributes.objectType !== undefined) {
						objectType = node.attributes.objectType;
					}

					buf.push("objectType=", encodeURIComponent(objectType), "&");
					buf.push("node=", encodeURIComponent(node.id), "&");
					buf.push("mode=", encodeURIComponent("onlyCatalog"), "&");
					buf.push("reportId=", encodeURIComponent(me.reportId), "&");
					buf.push("serverId=", encodeURIComponent(me.serverId));
					return buf.join("");
				}.createDelegate(this)
			}),
			selModel: new Ext.tree.KeyHandleTreeSelectionModel()
		});


		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						me.copyReport();
					}.createDelegate(this),
					iconCls: 'copy16',
					id: 'RSettF_CopyButton',
					onShiftTabAction: function () {
						this.buttons[0].focus();
					}.createDelegate(this),
					text: 'Копировать отчет'
				},
				{
					text: '-'
				},
				{
					handler: function () {
						this.callback();
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'RSettF_CloseButton',
					onShiftTabAction: function () {
						this.buttons[0].focus();
					}.createDelegate(this),
					text: BTN_FRMCLOSE
				}
			],
			items: [
				this.Tree
			]
		});

		sw.Promed.swReportCopyWindow.superclass.initComponent.apply(this, arguments);
	},
});