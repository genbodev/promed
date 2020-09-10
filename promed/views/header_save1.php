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

function patientResponse( result, request )
{
    //Ext.MessageBox.alert('Даные получены с сервера ', 'Данные с сервера успешно получены' + result.responseText);
    if ( result.responseText == 'false')
    {
        myData = [['', '', '', '']];
        store.loadData(myData);    
        return 0;
    }  
    myData = [['', '', '', '']];
    store.loadData(myData);    
    JSN = Ext.util.JSON.decode(result.responseText);
    for (var i=0;i<=JSN.length - 1; i++)
        myData[i] = [JSN[i]['last'], JSN[i]['name'], JSN[i]['second'], JSN[i]['birthday']];
    store.loadData(myData);
}

function patientResponseFail()
{
    Ext.MessageBox.alert('Ошибка', 'Данные с сервера не получены, попробуйте повторить запрос.')
}

function SearchPatient()
{
   Ext.Ajax.request({
   url: window.location,
   success: patientResponse,
   failure: patientResponseFail,
   headers: {
   },
   params: { method: 'patientSearch', last: filterForm.getForm().getValues()['last'], second: filterForm.getForm().getValues()['second'],
		name: filterForm.getForm().getValues()['name']

	 }
});

}

function onFieldEnter(sender, e)
{
    if (e.getKey() == e.ENTER)
	SearchPatient();
}

Ext.BLANK_IMAGE_URL = './ext/resources/images/default/s.gif';

function pageLoad()
{
    var menu = new Ext.menu.Menu({
        id: 'mainMenu',
        items: [
            {
                text: 'I like Ext',
            },
            {
                text: 'Ext for jQuery',
            },
            {
                text: 'I donated!',
            }
        ]
    });

    var tb = new Ext.Toolbar();
    tb.render(Ext.getBody());

    tb.add({
            text:'Button w/ Menu',
            iconCls: 'bmenu',  // <-- icon
            menu: menu  // assign menu by instance
    });
}
</script>

<style>
#path{
	font-size: 12px;
	padding: 5px;
	background-color: #CCDDFF;
	margin-bottom: 10px;
	margin-top: 5px;
	border: 1px solid #B2BFCF;
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
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
</HEAD>
<BODY onLoad="pageLoad()"></BODY>
</HTML>