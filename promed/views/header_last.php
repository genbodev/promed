<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<HEAD>
<!-- основные стили -->
<link rel="stylesheet" type="text/css" href="extjs/resources/css/ext-all.css" />
<!-- основная тема по умолчанию -->
<link rel="stylesheet" type="text/css" href="extjs/resources/css/xtheme-default.css" />
<!-- JavaScripts -->
<script type="text/javascript" src="extjs/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="extjs/ext-all.js"></script>
<script type="text/javascript" src="extjs/source/locale/ext-lang-ru.js"></script>
<!-- Common Styles for the examples -->
<link rel="stylesheet" type="text/css" href="extjs/examples/shared/examples.css" />
<link rel="stylesheet" type="text/css" href="extjs/examples/grid/grid-examples.css"/>

    <style type="text/css">
    .x-panel-body p {
        margin:10px;
        font-size:12px;
    }
    </style>

<script language="javascript">
var filterForm; // форма фильтров
var grid; // таблица
var myData; // данные таблицы
var store; // хранилище данных
var win; // окно
var tree; // дерево
var reestr_grid; // таблица реестров
var reestr_store; // хранилище данных реестров
var person_information_frame; // панель вывода данных о человеке


Ext.BLANK_IMAGE_URL = '/extjs/resources/images/default/s.gif';

function patientResponse( result, request )
{
    if ( result.responseText == 'false')
    {
        myData = [];
        store.loadData(myData);
        grid.setTitle("Человек ( по этому запросу записей не найдено. )" );
        return 0;
    }
    myData = [];
    store.loadData(myData);
    JSN = Ext.util.JSON.decode(result.responseText);
    for (var i=0;i<=JSN.length - 1; i++)
    {
	    myData[i] = [JSN[i]['last'], JSN[i]['name'], JSN[i]['second'], JSN[i]['birthday']];
    }
    grid.setTitle("Человек ( найдено записей: " + myData.length + " )" );
    store.loadData(myData);
    grid.getSelectionModel().selectFirstRow();
    grid.getView().focusRow( 1 );
}

function patientResponseFail()
{
    Ext.MessageBox.alert('Ошибка', 'Данные с сервера не получены, попробуйте повторить запрос.')
}

function SearchPatient()
{
   if ( !filterForm.getForm().isValid() )
   {
       Ext.MessageBox.alert('Ошибка ввода даных', 'Данные формы введены некорректно, проверьте правильность ввода.');
       return;
   }	
   Ext.Ajax.request({
   url: '/',
   success: patientResponse,
   failure: patientResponseFail,
   headers: {
   },
   params: { last: filterForm.getForm().getValues()['last'], second: filterForm.getForm().getValues()['second'],
		name: filterForm.getForm().getValues()['name'], birthday: filterForm.getForm().getValues()['birthday'], 
                agefrom: filterForm.getForm().getValues()['agefrom'], ageto: filterForm.getForm().getValues()['ageto'],
                yearfrom: filterForm.getForm().getValues()['yearfrom'], yearto: filterForm.getForm().getValues()['yearto']
	 }
});

}

function onFieldEnter(sender, e)
{
    if (e.getKey() == e.ENTER)
	SearchPatient();
}

function reestrTreeClick(node, e)
{
    if ( node.id != 'root' )
	reestr_store.load({params: {node: node.id}});
}


////////////////////////// Окно ////////////////////////////////////////////////////////////////

    function patientWindowShow() {
        var parent = Ext.getBody();
	    if(!win){
            win = new Ext.Window({
                applyTo     : 'patientSearchWindow',
                layout      : 'fit',
                width       : 800,
                height       : 500,
                closeAction :'hide',
                plain       : true,
                items       : new Ext.TabPanel({
                    applyTo        : 'innertabs',
                    autoTabs       : true,
                    activeTab      : 0,
                    deferredRender : false,
                    border         : false
                }),

                buttons: [{
                    text     : 'Закрыть',
                    handler  : function(){
                        win.hide();
                    }
                }]
            });
        }
        win.show(parent);
    }
   
    function createPersonInformationFrame( person_id )
    {
    	var Employee = Ext.data.Record.create([
   		{name: 'last', mapping: 'last'}, {name: 'name', mapping: 'name'}, {name: 'second', mapping: 'last'}
	]);

	personInformationFrame = new Ext.FormPanel({
	    url: '/?c=main&m=index&method=PersonInformationFrame',
            labelAlign: 'right',
            items: [{fieldLabel: 'Имя',
                     xtype: 'textfield',
                     style: 'border: none; background: none; color: blue; width: 100%',
                     disabled: true,
                     name: 'name'
		   }, {fieldLabel: 'Фамилия',
                     xtype: 'textfield',
                     style: 'border: none; background: none; color: blue; width: 100%',
                     disabled: true,
                     name: 'last'
		   }, {fieldLabel: 'Отчество',
                     xtype: 'textfield',
                     style: 'border: none; background: none; color: blue; width: 100%',
                     disabled: true,
                     name: 'second'
		   },{fieldLabel: 'Дата рождения',
                     xtype: 'textfield',
                     style: 'border: none; background: none; color: blue; width: 100%',
                     disabled: true,
                     name: 'birthday'
		   }, {fieldLabel: 'Пол',
                     xtype: 'textfield',
                     style: 'border: none; background: none; color: blue; width: 100%',
                     disabled: true,
                     name: 'sex'
		   }, {fieldLabel: 'Атрибуты',
                     xtype: 'textfield',
                     style: 'border: none; background: none; color: blue; width: 100%',
                     disabled: true,
                     name: 'attrs'
		   }],
            reader: new Ext.data.JsonReader ({totalRecords: "results",
   		record: "row",
   		id: "id"}, Employee)
     	});
        personInformationFrame.getForm().load({waitMsg:'Loading'});
        return personInformationFrame;
    }

    function pageLoad()
    {
        t = createPersonInformationFrame( 1 );
	t.render(Ext.getBody());
        Ext.QuickTips.init();
        ////////////////////////// МЕНЮ /////////////////////////////////////////////////////////////
        var menu = new Ext.menu.Menu({
            id: 'mainMenu',
            items: [
                {
                    text: 'Поиск: пациент',
                    xtype: 'tbbutton',
                    handler: function() {patientWindowShow()}
                }
            ]
        });

        var tb = new Ext.Toolbar();
        tb.render(Ext.getBody());
        tb.add({
            text:'Сервис',
            menu: menu
        });

        ////////////////////////// ФОРМА ПОИСКА /////////////////////////////////////////////////////
    	Ext.QuickTips.init();
    	// отправка формы по ENTER
    	filterForm = new Ext.FormPanel({
        labelAlign: 'top',
        frame:true,
        title:'Параметры поиска',
        bodyStyle:'padding:0px 0px 0; width: 100%',
            items: [{
	            layout:'column',
	            width: 800,
	            items:[{
	                columnWidth:.33,
	                layout: 'form',
	                items: [{
	                    xtype:'textfield',
	                    fieldLabel: 'Фамилия',
	                    name: 'last',
	                    anchor:'95%',
	                    listeners:
	                    {
	                	specialkey: function(sender, e) {onFieldEnter(sender, e);}
			    }
	                }]
	            },{
	                columnWidth:.33,
	                layout: 'form',
	                items: [{
	                    xtype:'textfield',
	                    fieldLabel: 'Имя',
	                    name: 'name',
	                    anchor:'95%',
	                    listeners:
	                    {
	                	specialkey: function(sender, e) {onFieldEnter(sender, e);}
			    }

	                }]
	            }, {
	                columnWidth:.33,
	                layout: 'form',
	                items: [{
	                    xtype:'textfield',
	                    fieldLabel: 'Отчество',
	                    name: 'second',
	                    anchor:'95%',
	                    listeners:
	                    {
	                	specialkey: function(sender, e) {onFieldEnter(sender, e);}
			    }

	                }]
	            }]
        },{
	            layout:'column',
	            width: 800,
	            items:[{
	                columnWidth:.19,
	                layout: 'form',
	                items: [{
                            xtype:'datefield',
	                    fieldLabel: 'Дата рождения',
                            format: 'd.m.Y',
	                    name: 'birthday',
	                    anchor:'90%',
	                    listeners:
	                    {
	                	specialkey: function(sender, e) {onFieldEnter(sender, e);}
			    }
	                }]
	            },{
	                columnWidth:.19,
	                layout: 'form',
	                items: [{
	                    xtype:'numberfield',
	                    fieldLabel: 'Возраст с',
                            maxLength: 3,
                            name: 'agefrom',
	                    anchor:'98%',
	                    listeners:
	                    {
	                	specialkey: function(sender, e) {onFieldEnter(sender, e);}
			    }

	                }]
	            }, {
	                columnWidth:.19,
	                layout: 'form',
	                items: [{
	                    xtype:'numberfield',
	                    fieldLabel: 'по',
                            maxLength: 3,
	                    name: 'ageto',
	                    anchor:'90%',
	                    listeners:
	                    {
	                	specialkey: function(sender, e) {onFieldEnter(sender, e);}
			    }

	                }]
	            }, {
	                columnWidth:.19,
	                layout: 'form',
	                items: [{
	                    xtype:'numberfield',
	                    fieldLabel: 'Год рожд. с',
			    maxLength: 4,
	                    name: 'yearfrom',
	                    anchor:'98%',
	                    listeners:
	                    {
	                	specialkey: function(sender, e) {onFieldEnter(sender, e);}
			    }

	                }]
	            }, {
	                columnWidth:.19,
	                layout: 'form',
	                items: [{
	                    xtype:'numberfield',
	                    fieldLabel: 'по',
                            maxLength: 4,
	                    name: 'yearto',
	                    anchor:'98%',
	                    listeners:
	                    {
	                	specialkey: function(sender, e) {onFieldEnter(sender, e);}
			    }

	                }]
	            }]
        }],

        buttons: [{
            text: 'Поиск',
		    handler  : function(){
                         SearchPatient();
                    }

        },{
            text: 'Очистить',
            	    handler  : function(){
                        filterForm.getForm().reset();
                    }

        }]

    });
    filterForm.render("patientTab");

////////////////////////// СОХРАНЕНИЕ СОСТОЯНИЯ ФОРМЫ /////////////////////////////////////////

    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

    // example of custom renderer function
    function change(val){
        if(val > 0){
            return '<span style="color:green;">' + val + '</span>';
        }else if(val < 0){
            return '<span style="color:red;">' + val + '</span>';
        }
        return val;
    }

    // example of custom renderer function
    function pctChange(val){
        if(val > 0){
            return '<span style="color:green;">' + val + '%</span>';
        }else if(val < 0){
            return '<span style="color:red;">' + val + '%</span>';
        }
        return val;
    }

////////////////////////// ХРАНИЛИЩЕ ДАННЫХ ///////////////////////////////////////////////////

    // create the data store
    store = new Ext.data.SimpleStore({
        fields: [
           {name: 'last'},
           {name: 'name'},
           {name: 'second'},
           {name: 'birthday', type: 'date', dateFormat: 'd.m.Y'}
        ]
    });
    myData = [];
    store.loadData(myData);
///////////////////////////ПАНЕЛЬ СТРАНИЦ ////////////////////////////////////////////////////
   var pagingBar = new Ext.PagingToolbar({
        pageSize: 25,
        store: store,
        displayInfo: true,
        displayMsg: 'Показаны записи {0} - {1} из {2}',
        emptyMsg: "No topics to display",
        
        items:[
            '-'
        ]
    });
////////////////////////// РЕЗУЛЬТИРУЮЩАЯ ТАБЛИЦА /////////////////////////////////////////////
    // create the Grid
    grid = new Ext.grid.GridPanel({
        store: store,
        columns: [
            {id:'last', header: "Фамилия", width: 130, sortable: true, dataIndex: 'last'},
            {header: "Имя", width: 130, sortable: true, dataIndex: 'name'},
            {header: "Отчество", width: 140, sortable: true, dataIndex: 'second'},
            {header: "Дата рождения", width: 145, sortable: true, renderer: Ext.util.Format.dateRenderer('d.m.Y'), dataIndex: 'birthday'}
        ],
        stripeRows: true,
        autoExpandColumn: 'last',
        height:240,
        title:'Человек',
        bbar: pagingBar
    });

    grid.render('patientTab');

    var tree = new Ext.tree.TreePanel({
        el: Ext.getBody(),
        useArrows:true,
        autoScroll:false,
        animate:true,
        height: 150,
        enableDD:false,
        containerScroll: true,
        loader: new  Ext.tree.TreeLoader({dataUrl:'/', baseParams: {method:'getReestrTree'}}),
        root: {
            nodeType: 'async',
            text: 'Реестр заболеваний',
            draggable:false,
            id:'root'
        }
    });

    // render the tree
    tree.on('click', function (node, e) {reestrTreeClick(node, e)});
    tree.render();
    tree.getRootNode().expand();
    
    // create the data store
    reestr_store = new Ext.data.JsonStore({
        fields: [
           {name: 'last'},
           {name: 'name'},
           {name: 'second'},
           {name: 'birthday', type: 'date', dateFormat: 'd.m.Y'},
           {name: 'mkb'},
           {name: 'insert_date', type: 'date', dateFormat: 'd.m.Y'},
           {name: 'dis_date', type: 'date', dateFormat: 'd.m.Y'}
        ],
	url: '/?c=main&m=index&method=getReestrList'
    });

    reestr_grid = new Ext.grid.GridPanel({
        store: reestr_store,
        columns: [
            {id:'last', header: "Фамилия", width: 130, sortable: true, dataIndex: 'last'},
            {header: "Имя", width: 130, sortable: true, dataIndex: 'name'},
            {header: "Отчество", width: 140, sortable: true, dataIndex: 'second'},
            {header: "Дата рождения", width: 145, sortable: true, renderer: Ext.util.Format.dateRenderer('d.m.Y'), dataIndex: 'birthday'},
	    {header: "Код MKB10", width: 140, sortable: true, dataIndex: 'mkb'},
            {header: "Дата занесения", width: 145, sortable: true, renderer: Ext.util.Format.dateRenderer('d.m.Y'), dataIndex: 'insert_date'},
            {header: "Дата исключения", width: 145, sortable: true, renderer: Ext.util.Format.dateRenderer('d.m.Y'), dataIndex: 'dis_date'}
        ],
        stripeRows: true,
        autoExpandColumn: 'last',
        height:240,
        title:'Регистр по заболеваниям',
        bbar: pagingBar
    });
    reestr_grid.render(Ext.getBody());
}
</script>
<style>
body
{
padding: 0px;
}

#menuItem {
	font-size: 12px;
	padding: 5px;
	background-color: #C0DD0F;
	margin-bottom: 10px;
	margin-top: 5px;
	width: 100px;
        border-collapse: collapse;
	border: 1px solid #B2BFCF;
        text-align: center;
        cursor: pointer;
}

#menuItem:hover{
	background-color: #B2BFCF;
	border: 1px solid #C0DD0F;
}

</style>
<TITLE>Промед ВЕБ</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
</HEAD>
<BODY onLoad="pageLoad()">
<div id="patientSearchWindow" class="x-hidden">
    <div class="x-window-header">Окно поиска пациентов</div>
    <div id="innertabs">
        <div id="patientTab" class="x-tab" title="1. Пациент">
        </div>
        <div class="x-tab" title="2. Адрес">
        <p>Здесь будет форма поиска пациента по адресу.</p>
        </div>
    </div>
</div>
</BODY>
</HTML>