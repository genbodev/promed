/**
* ui.js - Интерфейсные классы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       yunitsky
* @version      26.04.2010
 */

Ext.ns('sw');
Ext.ns('sw.reports.designer.ui');
Ext.ns('sw.reports.designer.ui.content');
Ext.ns('sw.consts');
Ext.ns('sw.consts.actions');
Ext.ns('Ext.ux.tree');

sw.consts.url = function(action){
    return sw.consts.ROOT + '?c=' + action.c + '&m=' + action.m;
};

sw.consts.ROOT = '/';

sw.consts.actions.GET_SERVERS_LIST = {c : 'ReportEngine', m :'getServersList'};
sw.consts.actions.ADD_SERVER = {c : 'ReportEngine', m :'addServer'};
sw.consts.actions.EDIT_SERVER = {c : 'ReportEngine', m :'editServer'};
sw.consts.actions.DELETE_SERVER = {c : 'ReportEngine', m :'deleteServer'};

sw.consts.actions.GET_SERVER_TREE = {c : 'ReportEngine', m :'getServerTree'};
//sw.consts.actions.GET_SERVER_TREE = {c : 'ReportEngine', m :'getAllTree'};

sw.consts.actions.GET_TABLE_REPORT = {c : 'ReportEngine', m :'getReport'};
sw.consts.actions.GET_TABLE_REPORTCATALOG = {c : 'ReportEngine', m :'getReportCatalog'};
sw.consts.actions.GET_TABLE_REPORTCONTENT = {c : 'ReportEngine', m :'getReportContent'};
sw.consts.actions.GET_TABLE_REPORTCONTENTFIELDSET = {c : 'ReportEngine', m :'getReportContentFieldset'};
sw.consts.actions.GET_TABLE_REPORTCONTENTPARAMETER = {c : 'ReportEngine', m :'getReportContentParameter'};
sw.consts.actions.GET_TABLE_REPORTPARAMETER = {c : 'ReportEngine', m :'getReportParameter'};
sw.consts.actions.GET_TABLE_REPORTPARAMETERCATALOG = {c : 'ReportEngine', m :'getReportParameterCatalog'};

sw.consts.actions.CRUD_REPORTCATALOG = {c : 'ReportEngine', m :'catalogCRUD'};
sw.consts.actions.CRUD_REPORT = {c : 'ReportEngine', m :'reportCRUD'};
sw.consts.actions.CRUD_REPORTPARAMETER = {c : 'ReportEngine', m :'parameterCRUD'};
sw.consts.actions.CRUD_REPORTPARAMETERCATALOG = {c : 'ReportEngine', m :'parameterCatalogCRUD'};
sw.consts.actions.CRUD_CONTENT = {c : 'ReportEngine', m :'contentCRUD'};
sw.consts.actions.CRUD_CONTENTPARAMETER = {c : 'ReportEngine', m :'contentParameterCRUD'};
sw.consts.actions.CHECK_PARAMID_AJAX = {c : 'ReportEngine', m :'ajaxcheckParamId'};

sw.consts.actions.COMBO_PARAMETERS = {c : 'ReportEngine', m :'getParametersCombo'};

sw.consts.actions.CHECK_PARAMETER_SQL = {c : 'ReportEngine', m :'checkSql'};
sw.consts.actions.GET_REPORT_CONTENT = {c : 'ReportEngine', m :'getReportContentEngine'};
sw.consts.actions.GET_PARAM_CONTENT = {c : 'ReportEngine', m :'getParameterContentEngine'};
sw.consts.actions.GET_FORMATS = {c : 'ReportEngine', m :'getFormats'};
sw.consts.actions.CREATE_REPORT_URL = {c : 'ReportEngine', m :'createReportUrl'};

sw.consts.actions.CHECK_PARAM_ID = {c : 'ReportEngine', m :'checkParamId'};
sw.consts.actions.CHECK_UNIQUE_REPORT_CAPTION = {c: 'ReportEngine', m:'checkUniqueReportCaption'};
sw.consts.actions.CHECK_REPORT_DESCRIPTION_LENGTH = {c: 'ReportEngine',m:'checkReportDescriptionLength'};
sw.consts.actions.CHECK_REPORT_TITLE_LENGTH = {c: 'ReportEngine',m:'checkReportTitleLength'};

sw.consts.actions.SET_CATALOG_AJAX = {c : 'ReportEngine', m :'setReportCatalog'};
sw.consts.actions.COPY_REPORT_AJAX = {c : 'ReportEngine', m :'copyReport'};
sw.reports.designer.ui.ErrorPanel = function(title,status){
    Ext.apply(this,{
        layout : 'fit',
        title  : title,
        html : '<div style="background:#eeeeee;border:1px dotted #666666;margin:10px;padding:10px;font-size:12px;text-align:center">' +
               '<span style="color:gray">Не удалось подключиться к серверу.<br></span>' +
               '<span style="color:red">' + status + '</span></div>',
        border : false
    });
    sw.reports.designer.ui.ErrorPanel.superclass.constructor.call(this);
};
Ext.extend(sw.reports.designer.ui.ErrorPanel, Ext.Panel, {});


sw.reports.designer.ui.TopMenu = function(){

    var _instance = null;

    return {

        getComponent : function(){
            if(_instance === null){
                _instance = new Ext.Panel({});
            }
            return _instance;
        }
    }

}();

sw.reports.designer.ui.ServerTree = function(record,serverTitle){
    this.server = record;
    var _loader = new Ext.tree.TreeLoader({
        baseParams : {serverId : record.get('id'), objectType : ''},
        requestMethod : 'POST',
        url : sw.consts.url(sw.consts.actions.GET_SERVER_TREE)
    });
    _loader.on('beforeload',function(loader,node){
        loader.baseParams.objectType = node.attributes.objectType;
    },this);
    Ext.apply(this,{
        root  : {id : 'root' , text : serverTitle, objectType : 'root', iconCls : 'rpt-server',viewClass : 'EmptyContent'},
        rootVisible : true,
        loader : _loader,
        autoScroll : true,
        animate: false,
        enableDD: true,
        dragConfig: {
			/*beforeDragOut: function (target, evt, id) {
				 console.log('beforeDragOut');
				 debugger;
			 },
			 afterDragOut: function (target, evt, id) {
				 console.log('beforeDragOut');
				debugger;
			 },
			 beforeDragEnter: function (target, evt, id) {
				 console.log('beforeDragEnter');
				debugger;
			 },
			 onBeforeDrag: function(data, e){
				 debugger;
				 var n = data.node;
				 return n && n.draggable && !n.disabled;
			 }*/
		}
    });
    sw.reports.designer.ui.ServerTree.superclass.constructor.call(this);
};

Ext.extend(sw.reports.designer.ui.ServerTree,Ext.tree.TreePanel,{
    useArrows  : true,
    reloadTree : function(){
        this.loader.load(this.getRootNode());
    }
});

sw.reports.designer.ui.ServerTreePanel = function(server,title){
    var me = this;
    var _onNodeClick = function(node,event){
        var parentId = node.parentNode ? node.parentNode.id : null;
        if(parentId){
            if(parentId.charAt(0) == '#'){
                parentId = parentId.slice(3);
            }
        }
        var nodeId = node.id.charAt(0) == '#' ? node.id.slice(3) : node.id;
        this.fireEvent('onobjectclick',node,this.server,node.attributes.viewClass,nodeId,parentId);
    }
    var _refreshTree = function(){
        this.tree.reloadTree();
    }
    var _expandTree = function(){
        this.tree.expandAll();
    }

    this.server = server;
    var temp = server.get('hostname') /*+ '/' + server.get('database') + '@' + server.get('username')*/
    this.tree = new sw.reports.designer.ui.ServerTree(server,temp);
    this.tree.on('click',_onNodeClick,this);
	/*var _onNodeDrop = function(dropEvent){
		var node = dropEvent.dropNode;
		var parentId = node.parentNode ? node.parentNode.id : null;
		if(parentId){
			if(parentId.charAt(0) == '#'){
				parentId = parentId.slice(3);
			}
		}
		var nodeId = node.id.charAt(0) == '#' ? node.id.slice(3) : node.id;
	};
	var _beforeMoveNode = function( tree, node, oldParent, newParent, index ){
		return false;
	};
	this.tree.on('beforenodedrop', _onNodeDrop, this);
	this.tree.on('beforemovenode', _beforeMoveNode, this);*/
	this.tree.on('nodedragover', function (dragOverEvent) {
		var target = dragOverEvent.target,
			node = dragOverEvent.dropNode,
			Region_id = node.attributes.Region_id,
			ReportCatalog_id = node.attributes.ReportCatalog_id;
		return (target.attributes && target.attributes.objectType == 'catalog'
			&& target.attributes.Region_id == Region_id
			&& target.attributes.ReportCatalog_id != ReportCatalog_id);
	}, this);
	this.tree.on('movenode', function (tree, node, oldParent, newParent, index) {
	    if(node && node.attributes && node.attributes.Report_id
            && newParent && newParent.attributes && newParent.attributes.ReportCatalog_id){
			var url = sw.consts.url(sw.consts.actions.SET_CATALOG_AJAX);
			var params = {
				Report_id   : node.attributes.Report_id,
				ReportCatalog_id : newParent.attributes.ReportCatalog_id
			};
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
						me.tree.reloadTree();
					}
				}
			});
			return true;
        } else {
	        return false;
        }
	}, this);
	/*this.tree.on('startdrag',function(tree,node,e){
		debugger;
		return (node.attributes.objectType == 'report');
		},this);*/

	this.tree.dragConfig.onBeforeDrag = function (data, e) {
		var n = data.node;
		var isReport = (n && n.attributes && n.attributes.objectType == 'report');
		return isReport && n.draggable && !n.disabled;
	};

    Ext.apply(this,{
        layout : 'fit',
        title  : title,
        border : false,
        hideCollapseTool : true,
        tools  : [
            {
                id      : 'refresh',
                handler : _refreshTree,
                scope   : this
            },
            {
                id      : 'plus',
                handler : _expandTree,
                scope   : this
            }
        ],
        items  : [this.tree]
    });
    sw.reports.designer.ui.ServerTreePanel.superclass.constructor.call(this);
    this.addEvents('onobjectclick');
};

Ext.extend(sw.reports.designer.ui.ServerTreePanel, Ext.Panel, {});

sw.reports.designer.ui.RepositoryPanel = function(){

    var _instance = null;
    /**
     * @private
     * внешний делегат для обработки нажатия на ноду дерева
     */
    var _handler = null;
    /**
     * @private
     * текущий сервер
     */
    var _server = null;

    var _node = null;

    var _store = sw.reports.stores.Servers.getInstance();

    var _loadServers = function(){
        _store.load({
            callback : _onLoad,
            scope    : this
        });
    }

    var _onObjectClick = function(node,server,type,id,ownerId){
        _node = node;
        _server = server;
        if(_handler){
            if(_handler.scope){
                _handler.call(_handler.scope,[server,type,id,ownerId])
            } else {
                _handler(server,type,id,ownerId);
            }
        }
    }

    var _onLoad = function(){
        _instance.items.clear();
        _store.each(_addServer,this);
        _server = _store.getAt(0);
        _instance.doLayout();
    }

    var _addServer = function(record){
        var _title = '<b>' + record.get('title') + '</b> ';
        //var _conn = '(' + record.get('hostname') + '/' + record.get('database') + '@' + record.get('username') + ')';
        var _conn = record.get('hostname');
        if(record.get('status') == 'OK') {
            _title += '<span style="color:green">' + _conn +  '</span>';
            var _panel = new sw.reports.designer.ui.ServerTreePanel(record,_title);
            _panel.on('onobjectclick',_onObjectClick,this);
            _instance.add(_panel);
        } else {
            _title += '<span style="color:red">' + _conn + '</span>';
            _instance.add(new sw.reports.designer.ui.ErrorPanel(_title,record.get('status')));
        }
    }

    var _filterField = new Ext.form.TextField({
        width      : 120,
        listeners  : {
            specialkey : function(cmp,e){
                if(e.getKey() == Ext.EventObject.ENTER){
                    var tree = _instance.layout.activeItem.items.items[0];
                    tree.expandAll();
                    var filter = new Ext.ux.tree.TreeFilterX(tree);
                    var value = cmp.getValue();
                    filter.filterBy(function(node){
                        return node.text.indexOf(value) != -1;
                    },this);
                }
            }
        }
    });

    var _searchField = new Ext.form.TextField({
        width      : 120,
        listeners  : {
            specialkey : function(cmp,e){
                if(e.getKey() == Ext.EventObject.ENTER){
                    var value = cmp.getValue();
                    var tree = _instance.layout.activeItem.items.items[0];
                    tree.disable();
                    var findInAll = function(node){
                        if(node.text.indexOf(value) != -1) return node;
                        if(!node.isLeaf()){
                            if(node.isLoaded()){
                                node.expand();
                                for(var i = 0; i < node.childNodes.length; i++){
                                    var temp = findInAll(node.childNodes[i]);
                                    if(temp) return temp;
                                }
                            } else {
                                tree.getLoader().on('load',function(l,node){
                                    var temp = findInAll(node);
                                    if(temp) return temp;
                                },this);
                                node.expand();
                            }
                        }
                        return null;
                    };
                    tree.enable();
                    var node = findInAll(tree.getRootNode());
                    if(node) node.select();
                }
            }
        }
    });
    return {

        getComponent : function(){
            if(_instance === null){
                _instance = new Ext.Panel({
                    collapsible : true,
                    region      : 'west',
                    width       : 400,
                    title       : lang['serveryi'],
                    split       : true,
                    iconCls     : 'rpt-server',
                    layout      : 'accordion',
                    layoutConfig: {animate : false}
//                    tbar        : new Ext.Toolbar({
//                        items : [
//                            {
//                                text     : 'Поиск',
//                                disabled : true
//                                //style : 'padding-left:4px;padding-right:10px'
//                            },
//                            _searchField,
//                            '->',
//                            {
//                                text     : 'Фильтр',
//                                disabled : true
//                                //style : 'padding-left:4px;padding-right:10px'
//                            },
//                            _filterField
//                        ]
//                    })
                });
            }
            return _instance;
        },
        reloadServers : function(){
            _loadServers();
        },
        registerHandler : function(handler,scope){
            _handler = handler;
            _handler.scope = scope;
        },
        getServer : function(){
            return _server;
        },
        getSelectedNode : function(){
            return _node;
        },
        refreshNode : function(id){
            var tree = _instance.layout.activeItem.items.items[0];
            var node = tree.getNodeById(id);
            if(node){
                tree.getLoader().load(node);
            }
        }
    }

}();

sw.reports.designer.ui.ContentManager = function(){

    /**
     * @private
     * Текущий набор контентов, панели не удаляются
     * а просто скрываются.
     * Ключ - id контента
     */
    var _views = [];

    /**
     * @private
     * Текущаий видимый контент
     */
    var _currentView;

    /**
     * @private
     * Текущий FormPanel (если есть)
     */
    var _currentForm;

    /**
     * Нода дерева соответствующая текущей панели
     */
    var _objectNode = null;

    /**
     * @private
     * Сабмитит форму на сервер
     * В __mode передается тип действия add|edit|delete
     */
    var _submitForm = function(){
        if(_currentForm){
            // формируем json из id выбранных регионов
            var Region_ids = null;
            if (_currentForm.formName && _editor.state != 'delete') {
                var table_id = '';
                switch (_currentForm.formName) {
                    case 'reportEditForm':
                        table_id = 'REWR_RegionGrid';
                        break;
                    case 'catalogEditForm':
                        table_id = 'REWC_RegionGrid';
                        break;
                    case 'contentParamEditForm':
                        table_id = 'REWP_RegionGrid';

                        // если в редакторе поле SQL пустое то очищаем его на форме
                        if( 
                            _currentForm.findById('REWP_Parameter_SQL_editor')
                            && typeof _currentForm.findById('REWP_Parameter_SQL_editor') == 'object'
                            && !_currentForm.findById('REWP_Parameter_SQL_editor').getValue() 
                        ) {
                            _currentForm.getForm().findField('ReportContentParameter_SQL').setValue(''); 
                        }
                        break;
                    case 'contentParamEditForm2':
                        table_id = 'REWP2_RegionGrid';

                        // если в редакторе поле SQL пустое то очищаем его на форме
                        if( 
                            _currentForm.findById('REWP2_Parameter_SQL_editor')
                            && typeof _currentForm.findById('REWP2_Parameter_SQL_editor') == 'object'
                            && !_currentForm.findById('REWP2_Parameter_SQL_editor').getValue() 
                        ) {
                            _currentForm.getForm().findField('ReportContentParameter_SQL').setValue(''); 
                        }
                        break;
                }
                
                    var regionGrid = Ext.getCmp(table_id);
                if (typeof regionGrid == 'object' && typeof regionGrid.getStore == 'function') {
                    Region_ids = [];
                    regionGrid.getStore().each(function(rec) {
                        if (rec.get('RegionSelected')) {
                            Region_ids.push(rec.get('id'));
                        }  
                    });
                    Region_ids = Ext.util.JSON.encode(Region_ids);
                }
                
            }
            
            _currentForm.getForm().submit({
                params : {
                    __mode   : _editor.state,
                    serverId : sw.reports.designer.ui.RepositoryPanel.getServer().get('id'),
                    Region_ids: Region_ids
                },
                success : function(form,action){
                    var id = null;
                    if(_currentView.isEditable){
                        if(_editor.state == 'add'){
							var result = Ext.util.JSON.decode(action.response.responseText);
                            id = result.id;
                        } else {
                            if(_currentView.getSelected()){
                                id = _currentView.getSelected().id;
                            }
                        }
                    }
                    _currentView.refreshContent(id);
                    if(_objectNode){
                        _objectNode.getOwnerTree().getLoader().load(_objectNode);
                    }
                },
                failure : function(form,action){
                    Ext.Msg.alert(lang['oshibka'],action.result.msg);
                }
            });
            _currentForm.un('clientvalidation');
            _currentForm = null;
        }
        _editor.hide();
    }

    /**
     * @private
     * Контейнер для форм
     */
    var _editor = new Ext.Window({
        state      : 'add',
        title      : 'overload',
        autoHeight : true,
//        autoWidth  : true,
        resizable  : false,
        plain      : true,
        modal      : true,
        autoScroll : false,
        closeAction: 'hide',
        width      : 100,
        buttons    : [
            {
				text: lang['prava'],
				hidden: !isSuperAdmin(),
				handler: function() {
                    var idField;
                    if (_currentForm.formName == 'contentParamEditForm') {
                        idField = 'ReportContentParameter_id';
                    } else {
                        idField = 'Report_id';
                    }
					var reportFueld = _currentForm.getForm().findField(idField);
					if( reportFueld ) {
                        var params = {};
                        params[idField] = reportFueld.getValue()
						getWnd('swARMListAccessWindow').show(params);
					} else {
						//
					}
				}
			}, {
                text    : lang['ok'],
                handler : _submitForm,
                scope   : this
            },
            {
                text    : lang['otmenit'],
                handler : function(){
                    _editor.hide();
                    _currentForm.un('clientvalidation');
                    _currentForm = null;
                },
                scope   : this
            }
        ]
    });

    /**
     * @private
     * Валидатор формы. Дизаблит кнопку ОК
     */
    var _validForm = function(form,valid){
        if(valid) {
            _editor.buttons[1].enable();
        } else {
            _editor.buttons[1].disable();
        }
        if (form.formName == 'contentParamEditForm') {
            var idField = form.getForm().findField('ReportContentParameter_id');
        } else {
            var idField = form.getForm().findField('Report_id');
        }

		if(Ext.isEmpty(idField) || !idField.getValue()){
			_editor.buttons[0].hide();
		}
		else
		{
			if(isSuperAdmin())
				_editor.buttons[0].show();
			else
				_editor.buttons[0].hide();
		}
    }


    var _deepValidaiton = function(item){
        if(item.items){
            item.items.each(_deepValidaiton)
        } else if(item.validate) item.validate();
    }

    var _addEditHandler = function(state){
        if(state instanceof Ext.grid.GridPanel) state = 'edit';
        var temp = state == 'edit' ? lang['redaktirovanie'] : lang['dobavlenie'];
        _editor.state = state;

        if (state && state.inlist(['addReportCatalog', 'addReport', 'addParameterCatalog','addParameterGroup', 'addParameter'])) {
            _currentForm = _currentView.getForm(state);
            _editor.state = 'add';
        } else {
            _currentForm = _currentView.getForm();
        }
        
        _currentForm.on('clientvalidation',_validForm,this);
        _editor.removeAll();
        _editor.iconCls = _currentView.iconCls;
        _editor.setTitle(_currentView.title +  ' - ' + temp);
        if(_currentForm.isParametersForm === true){
            _editor.setWidth(650);
//            _editor.autoHeight = false;
//            _editor.setHeight(400);
        } else {
            _editor.setWidth(_currentForm.autoScroll ? _currentForm.defaults.width + 66 : _currentForm.defaults.width + 46)
            _editor.autoHeight = true;
//            _editor.setHeight(100);
        }
        _editor.add(_currentForm);
        _editor.show();
//        _editor.doLayout();
        if( state && state.inlist(['add', 'addReportCatalog', 'addReport', 'addParameterCatalog','addParameterGroup', 'addParameter']) ){
            _currentForm.getForm().reset();
            _deepValidaiton(_currentForm);
        } else {
            var record = _currentView.getSelected();
            _currentForm.getForm().loadRecord(record);

            if (
                //record.tableName && record.tableName.inlist(['folderGrid', 'paramGrid'])
                _currentForm.formName && _currentForm.formName.inlist(['parameterEditForm', 'catalogEditForm'])
                && !_currentForm.getForm().findField('Region_id').getValue() 
            ) {
                _currentForm.getForm().findField('Region_id').setValue(null);
            }
            
            if (typeof record == 'object' && record.get('Region_ids')) {
                var Region_ids = Ext.util.JSON.decode(record.get('Region_ids'));
                if (typeof Region_ids == 'object' && _currentForm.formName) {
                    var table_id = '';

                    switch (_currentForm.formName) {
                        case 'catalogEditForm':
                            table_id = 'REWC_RegionGrid';
                            break;
                        case 'reportEditForm':
                            table_id = 'REWR_RegionGrid';
                            break;
                        case 'contentParamEditForm':
                            table_id = 'REWP_RegionGrid';
                            break;
                        case 'contentParamEditForm2':
                            table_id = 'REWP2_RegionGrid';
                            break;
                    }

                    //добавил таймаут т.к. на этот момент таблица почему-то ещё не успела загрузиться(событие load у таблицы раньше)
                    setTimeout(function() {
                        var grid = Ext.getCmp(table_id);
                        if (typeof grid == 'object') {
                           grid.loadRegions(Region_ids);
                        }
                         
                    }, 10);
                }
            }
        }
    }

    var _deleteHandler = function(){
        Ext.Msg.show({
            title   : _currentView.title + lang['-_udalenie_zapisi'],
            msg     : lang['vyi_deystvitelno_hotite_udalit_zapis_otmenit_operatsiyu_budet_nevozmojno'],
            buttons: Ext.MessageBox.YESNO,
            scope : this,
            fn: function(btn){
                if(btn == "yes") {
                    _currentView.deleteContent(function(){
                        _currentView.refreshContent();
                        if(_objectNode){
                            _objectNode.getOwnerTree().getLoader().load(_objectNode);
                        }
                    },this);
                }
            },
            icon: Ext.MessageBox.QUESTION
        });
    }

    /**
     * Тестирование отчета
     */
    var _testHandler = function(){
        // 1. Получаем данные об отчете
        var server = sw.reports.designer.ui.RepositoryPanel.getServer();
        var reportId = _objectNode.attributes.Report_id;
		var reportCaption = _objectNode.attributes.Report_Caption;
        sw.ParamFactory.getReportContent(server.data.id,reportId,function(err,data){
            if(err){
                Ext.Msg.alert(lang['oshibka'],err);
            } else {
                var win = new sw.reports.designer.ui.forms.ReportTester({
                    reportId   : reportId,
					reportCaption: reportCaption,
                    reportData : data,
                    serverId   : server.data.id
                });
                win.show();
            }
        })
    };
	
    /**
     * Настройки отчета
     */
    var _copyReportHandler = function(){
		var params = new Object();
		var reportId = _objectNode.attributes.Report_id;
		var server = sw.reports.designer.ui.RepositoryPanel.getServer();
		params.reportId = reportId;
		params.serverId = server.data.id;
		params.reportNode = _objectNode;
		getWnd('swReportCopyWindow').show(params);
    };

	/**
	 * Копирование отчета
	 */
    var _settingsHandler = function(){
		var params = new Object();
		var reportId = _objectNode.attributes.Report_id;
		var server = sw.reports.designer.ui.RepositoryPanel.getServer();
		params.reportId = reportId;
		params.serverId = server.data.id;
		getWnd('swReportSettingsWindow').show(params);
	};

    var _refreshButton = new Ext.Button({
        text : lang['obnovit'],
        iconCls  : 'refresh16',
        disabled : true,
        handler  : function(){
            if(_currentView) _currentView.refreshContent();
        },
        scope : this
    });

    var _addReportButton = new Ext.Button({
        text : langs('Добавить отчёт'),
        iconCls  : 'add16',
        hidden : true,
        handler : function(){
            _addEditHandler('addReport');
        },
        scope : this
    });
    var _addReportCatalogButton = new Ext.Button({
        text : langs('Добавить каталог отчётов'),
        iconCls  : 'add16',
        hidden : true,
        handler : function(){
            _addEditHandler('addReportCatalog');
        },
        scope : this
    });

    var _addParameterGroupButton = new Ext.Button({
        text : langs('Добавить группу параметров'),
        iconCls  : 'add16',
        hidden : true,
        handler : function(){
            _addEditHandler('addParameterGroup');
        },
        scope : this
    });

    var _addParameterButton = new Ext.Button({
        text : langs('Добавить параметр'),
        iconCls  : 'add16',
        hidden : true,
        handler : function(){
            _addEditHandler('addParameter');
        },
        scope : this
    });

    var _addParameterCatalogButton = new Ext.Button({
        text : langs('Добавить каталог параметров'),
        iconCls  : 'add16',
        hidden : true,
        handler : function(){
            _addEditHandler('addParameterCatalog');
        },
        scope : this
    });

    var _editButton = new Ext.Button({
        text : lang['izmenit'],
        iconCls  : 'edit16',
        disabled : true,
        handler : function(){
            _addEditHandler('edit');
        },
        scope : this
    });

    var _deleteButton = new Ext.Button({
        text : lang['udalit'],
        iconCls  : 'delete16',
        disabled : true,
        handler : _deleteHandler,
        scope : this
    });

    var _testButton = new Ext.Button({
        text : langs('Протестировать отчет'),
        iconCls  : 'rpt-test',
        disabled : true,
        handler : _testHandler,
        scope : this
    });
	
    var _settingsButton = new Ext.Button({
        text : lang['nastroyki_otcheta'],
        iconCls  : 'settings-global16',
        disabled : true,
        handler : _settingsHandler,
        scope : this
    });

	var _copyReportButton = new Ext.Button({
		text : langs('Копировать отчет'),
		iconCls  : 'rpt-copy',
		disabled : true,
		handler : _copyReportHandler,
		scope : this
	});

    var _toolbar = new Ext.Toolbar({
        items : [ _refreshButton,_addReportButton,_addReportCatalogButton,_addParameterGroupButton,_addParameterCatalogButton,_addParameterButton,_editButton,_deleteButton,'-',_settingsButton, _testButton, _copyReportButton]
    });

    var _setupButtons = function(disable){
        _refreshButton.disable();
        _editButton.disable();
        _deleteButton.disable();
        _testButton.disable();
		_copyReportButton.disable();
		_settingsButton.disable();
        _addReportButton.disable();
        _addReportCatalogButton.disable();
        _addParameterGroupButton.disable();
        _addParameterCatalogButton.disable();
        _addParameterButton.disable();
        if(disable) return;
        _refreshButton.enable();
        if(_currentView.isEditable){
            _addReportCatalogButton.enable();
            
            _addParameterGroupButton.enable();
            _addParameterCatalogButton.enable();
            _addParameterButton.enable();

            // блокируем добавление отчётов если выбрана папка региона (у папок регионов не числовые id)
            if (Number(_currentView.objectId) == _currentView.objectId) {
                _addReportButton.enable();
            }
            if(_currentView.getSelected()){
                _editButton.enable();
                _deleteButton.enable();
            }
        }
        if(_currentView.isTestable){
            _testButton.enable();
			_copyReportButton.enable();
			_settingsButton.enable();
        }

        _addReportCatalogButton.hide();
        _addReportButton.hide();
        _addParameterGroupButton.hide();
        _addParameterCatalogButton.hide();
        _addParameterButton.hide();

        switch (_currentView.viewName) {
            case 'ReportFolderView':
                _addReportButton.show();
                _addReportCatalogButton.show();
                break;

            case 'ReportView':
                _addParameterGroupButton.show();
                _addParameterButton.show();
                break;

            case 'ParamFolderView':
                _addParameterButton.show();
                _addParameterCatalogButton.show();
                break;
            case 'FieldsetView':
                _addParameterButton.show();
                break; 
        }

    };

    var _onContentChange = function(content){
        _setupButtons(false);
    };

    var _content = new Ext.Panel({
        region   : 'center',
        tbar     : _toolbar,
        layout   : 'card',
        autoDestroy: false
    });

    return {

        selectView : function(server,viewClass,id,ownerId){
            if(viewClass == 'EmptyContent') {
                //_setupButtons(true);
                return;
            }
            if(!_views[viewClass]){
                _views[viewClass] =  new sw.reports.designer.ui.content[viewClass];
                _views[viewClass].iconCls = sw.reports.designer.ui.RepositoryPanel.getSelectedNode().attributes.iconCls;
                if(_views[viewClass].isEditable) {
                    _views[viewClass].on('contentchange',_onContentChange,this);
                }
                _content.add(_views[viewClass]);
            }
            _currentView = _views[viewClass];
            _objectNode = sw.reports.designer.ui.RepositoryPanel.getSelectedNode();
            _currentView.setup(server,id,ownerId);
            _views[viewClass].items.each(function(item){
                if(item instanceof Ext.grid.GridPanel){
                    item.on('rowdblclick',_addEditHandler);
                }
            },this);
            _content.layout.setActiveItem(_currentView.id);
            _setupButtons(true);
        },

        getComponent : function(){
            return _content;
        }

    }

}();

/**
 * Creates new TreeFilterX
 * @constructor
 * @param {Ext.tree.TreePanel} tree The tree panel to attach this filter to
 * @param {Object} config A config object of this filter
 */
Ext.ux.tree.TreeFilterX = Ext.extend(Ext.tree.TreeFilter, {

    // {{{
    /**
     * Filter the data by a specific attribute.
     *
     * @param {String/RegExp} value Either string that the attribute value
     * should start with or a RegExp to test against the attribute
     * @param {String} attr (optional) The attribute passed in your node's attributes collection. Defaults to "text".
     */
     filter:function(value, attr, startNode) {

        var animate = this.tree.animate;
        this.tree.animate = false;
        this.tree.expandAll();
        this.tree.animate = animate;
        Ext.ux.tree.TreeFilterX.superclass.filter.apply(this, arguments);

    } // eo function filter
    // }}}
    // {{{
    /**
     * Filter by a function. The passed function will be called with each
     * node in the tree (or from the startNode). If the function returns true, the node is kept
     * otherwise it is filtered. If a node is filtered, its children are also filtered.
     * Shows parents of matching nodes.
     *
     * @param {Function} fn The filter function
     * @param {Object} scope (optional) The scope of the function (defaults to the current node)
     */
    ,filterBy:function(fn, scope, startNode) {
        startNode = startNode || this.tree.root;
        if(this.autoClear) {
            this.clear();
        }
        var af = this.filtered, rv = this.reverse;

        var f = function(n) {
            if(n === startNode) {
                return true;
            }
            if(af[n.id]) {
                return false;
            }
            var m = fn.call(scope || n, n);
            if(!m || rv) {
                af[n.id] = n;
                n.ui.hide();
                return true;
            }
            else {
                n.ui.show();
                var p = n.parentNode;
                while(p && p !== this.root) {
                    p.ui.show();
                    p = p.parentNode;
                }
                return true;
            }
            return true;
        };
        startNode.cascade(f);

        if(this.remove){
           for(var id in af) {
               if(typeof id != "function") {
                   var n = af[id];
                   if(n && n.parentNode) {
                       n.parentNode.removeChild(n);
                   }
               }
           }
        }
    } 
});

sw.reports.designer.ui.DefaultCombo = Ext.extend(Ext.form.ComboBox,{
    mode           : 'remote',
    triggerAction  : 'all',
    typeAhead      : true,
    loadingText    : lang['poisk'],
    emptyText      : lang['vyiberite'],
    selectOnFocus  : true,
    minChars       : 1,
    editable       : true,
    forceSelection : true,
    valueField     : 'id',
    displayField   : 'title'
});

Ext.reg('sp.ui.defaultcombo',sw.reports.designer.ui.DefaultCombo);

Ext.namespace('Ext.ux.form');

Ext.ux.form.EditArea = Ext.extend(Ext.form.TextArea, {
    initComponent: function() {
        this.eaid = this.id;
        Ext.ux.form.EditArea.superclass.initComponent.apply(this, arguments);
        this.mon(this,'resize', function(ta, width, height) {
            var el = Ext.get('frame_' + this.eaid);
            if (el) {
                el.setSize(width,height);
            }

        });
    },
    onRender: function() {
        Ext.ux.form.EditArea.superclass.onRender.apply(this, arguments);
        editAreaLoader.init({
				id: this.eaid,
				start_highlight: this.initialConfig.start_highlight || true,
				language: 'ru',
				syntax: this.initialConfig.syntax,
				syntax_selection_allow: "sql",
				allow_toggle: false,
				allow_resize: this.initialConfig.allow_resize || false,
				replace_tab_by_spaces: this.initialConfig.replace_tab_by_spaces || 4,
				toolbar: "undo,redo",
				is_editable: this.initialConfig.is_editable || true,
				show_line_colors: true,
                                baseURL : 'jscore/Forms/Report',
				//plugins: "autocomplite",
				autocompletion:true
			});
    },
    getValue: function() {
        var v = editAreaLoader.getValue(this.eaid);
        return v;
    },
    setValue: function(v) {
        Ext.ux.form.EditArea.superclass.setValue.apply(this, arguments);
        editAreaLoader.setValue(this.eaid, v);
    },
    validate: function() {
        this.getValue();
        Ext.ux.form.EditArea.superclass.validate.apply(this, arguments);
    },
	insertText: function(text){
		editAreaLoader.setSelectedText(this.eaid, text);
	},
	hide: function(){
		editAreaLoader.hide(this.eaid);
	},
	show: function(){
		editAreaLoader.show(this.eaid)
	}

});
Ext.reg('ux-editarea', Ext.ux.form.EditArea);

function insertAtCursor(myField, myValue) {
    //IE support
    if (document.selection) {
        myField.focus();
        var sel = document.selection.createRange();
        sel.text = myValue;
    }
    //Mozilla/Firefox/Netscape 7+ support
    else if (myField.selectionStart || myField.selectionStart == '0'){
        var startPos = myField.selectionStart;
        var endPos = myField.selectionEnd;
        myField.value = myField.value.substring(0, startPos) + myValue +
            myField.value.substring(endPos, myField.value.length);
    } else {
        myField.value += myValue;
    }
}

// возвращает панель выбора региона, с ид таблицы = <table_id>
sw.reports.designer.ui.getRegionSelectPanel = function(table_id) {
    if (table_id === undefined) {return false;}

    var regionCheckRenderer = function(v, p, record, table_id) {
        var name = 'checkboxRegion_'+record.get('id');
        var value = 'value="'+name+'"';
        var checked = record.get('RegionSelected') == true ? ' checked="checked"' : '';
        var onclick = 'onClick="Ext.getCmp(\'' + table_id + '\').checkOne(this.value);"';
        var disabled = '';

        return '<input type="checkbox" '+value+' '+checked+' '+onclick+' '+disabled+'>';
    };
    var regionPanel = new Ext.Panel({
        layout : 'form',
        labelAlign:'top',
        defaultType: 'textfield',
        autoHeight : true,
        border: false,
        bodyStyle:'background:transparent;padding:10px;',
        items: [{
			layout: 'border',
			width: 455,
			height: 450,
			xtype: 'panel',
			items: [
				new Ext.grid.GridPanel({
					autoExpandColumn: 'autoexpand',
					border: false,
					region: 'center',
					id: table_id,
					columns: [
						{
							dataIndex: 'id',
							hidden: true,
						}, {
							id: 'autoexpand',
							dataIndex: 'RegionName',
							hidden: false
						}, {
							dataIndex: 'RegionSelected',
							hidden: false,
							width: 25,
							renderer: function(v, p, record) {
								return regionCheckRenderer(v, p, record, table_id);
							}
						}
					],
					checkOne: function(name) {
						var grid = this;
						var Region_id = name.split('_')[1];
						var record = grid.getStore().getAt(grid.getStore().findBy(function(rec) {
							return rec.get('id') == Region_id;
						}));
						if (record) {
							record.set('RegionSelected', !record.get('RegionSelected'));
							record.commit();
						}
					},
					loadRegions: function(Regions_arr) {
						var grid = this;
						grid.getStore().each(function(rec) {

							var elem = Regions_arr.find(function(elem, index) {
								return rec.get('id') == elem
							});

							if (elem !== undefined) {
								rec.set('RegionSelected', true);
							} else {
								rec.set('RegionSelected', false);
							}
							rec.commit();
						});
					},
					setAllRegions: function(action) {
						var grid = this;

						grid.getStore().each(function(rec) {
							rec.set('RegionSelected', (action == 'select'));
							rec.commit();
						});
					},
					store: new Ext.data.SimpleStore({
						fields: ['id', 'RegionName', 'RegionSelected'],
						sortInfo: {
							field: 'RegionName',
							direction: 'ASC'
						},
						data: [
							['10', langs('Карелия'), false],
							['19', langs('Хакасия'), false],
							['30', langs('Астрахань'), false],
							['60', langs('Псков'), false],
							['63', langs('Самара'), false],
							['64', langs('Саратов'), false],
							['77', langs('Москва'), false],
							['101', langs('Казахстан'), false],
							['66', langs('Екатеринбург'), false],
							['59', langs('Пермь'), false],
							['1', langs('Адыгея'), false],
							['2', langs('Уфа'), false],
							['3', langs('Бурятия'), false],
							['201', langs('Беларусь'), false],
							['58', langs('Пенза'), false],
							['40', langs('Калуга'), false],
							['91', langs('Крым'), false],
							['11', langs('Сыктывкар'), false],
							['12', langs('Марий Эл'), false],
							['35', langs('Вологда'), false],
							['50', langs('Московская область'), false],
							['24', langs('Красноярский край'), false],
							['26', langs('Ставропольский край'), false],
							['76', langs('Ярославль'), false]
						]
					})
				})
			]
		}, {
			layout: 'column',
			xtype: 'panel',
			bodyStyle: 'background: transparent;',
			border: false,
			items: [{
				text: 'Выбрать все',
				xtype: 'button',
				handler: function() {
					Ext.getCmp(table_id).setAllRegions('select');
				}.createDelegate(this)
			},
			{
				text : 'Убрать все',
				xtype: 'button',
				handler: function() {
					 Ext.getCmp(table_id).setAllRegions('unselect');
				}.createDelegate(this)
			}]
		}]
    });

    return regionPanel;
}

sw.reports.designer.ui.BaseFormPanel = Ext.extend(Ext.FormPanel,{
    monitorValid : true,
    labelAlign:'top',
    defaultType: 'textfield',
    defaults: {
        width : 500,
        blankText  : "Поле обязательно для заполнения",
        allowBlank : false,
        selectOnFocus : true
    },
    border    : false,
    bodyStyle :'background:transparent;padding:10px;'
});

Ext.reg('sw.baseformpanel',sw.reports.designer.ui.BaseFormPanel);