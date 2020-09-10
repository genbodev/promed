/**
* swAboutWindow - окно О программе
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      24.04.2009
*/

sw.Promed.swAboutWindow = Ext.extend(sw.Promed.BaseForm, {
	width : 400,
	height : 300,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	show: function() {
        sw.Promed.swAboutWindow.superclass.show.apply(this, arguments);
		this.center();
	},
	testId: 'wnd_about',
	title: lang['o_programme'],
	initComponent: function() {
    	Ext.apply(this, {
			buttonAlign : "right",
			buttons : [{
					text : lang['zakryit'],
					iconCls: 'close16',
					testId: 'wnd_about_btn_close',
					handler : function(button, event) {
						this.hide();
					}.createDelegate(this)
				}],
			items : [ {
					height: 240,
					layout:'form',
					border : false,
					frame: false,
					items: [
						{
									border : false,
									style: 'padding: 5px; margin: 5px;',
									html: "<img src='/img/promed-web-logo.png' align='left'/> <h2>&nbsp;&nbsp;&nbsp;<big>"+project_name+"</big><br/>&nbsp;&nbsp;&nbsp;Версия: "+PromedVer+"<br/>&nbsp;&nbsp;&nbsp;Ревизия: "+Revision+"<br/>&nbsp;&nbsp;&nbsp;<small>"+PromedVerDate+"</small><br/>&nbsp;&nbsp;&nbsp;<a href='"+promed_site_url+"' target=_blank>"+promed_site_url+"</a></h2>"
						},
						{
						xtype: 'fieldset',
							autoHeight: true,
							title: lang['razrabotchiki'],
							style: 'padding: 5px; margin: 5px 5px;',
							items:[{
									border : false,
								   html: "<b>&copy; 2009-2013 "+promed_dev_name+". Все права защищены.<br/>"+project_name+" является торговой маркой "+promed_dev_name+".</b><br/><br/>Использовано программное обеспечение:<br/><a href='http://extjs.com' target=_blank>ExtJS</a> от <a href='http://extjs.com' target=_blank>Ext JS, LLC</a>, <a href='http://codeigniter.com' target=_blank>CodeIgniter</a> от <a href='http://ellislab.com/' target=_blank>EllisLab, Inc.</a></b>"
								}
							]
					}]
				}, {
				height : 0
			}
			]
		});
		sw.Promed.swAboutWindow.superclass.initComponent.apply(this, arguments);
	}
});