/**
* swMainCloseCardWindowRegional Новая Карта закрытия вызова
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dyomin Dmitry
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      21.01.2013
*/

sw.Promed.swMainCloseCardWindowRegional = Ext.extend(sw.Promed.swMainCloseCardWindow,{
	objectName: 'swMainCloseCardWindowRegional',
	objectSrc: '/jscore/Forms/Ambulance/swMainCloseCardWindowRegional.js',
	cls: 'swMainCloseCardWindowRegional',
	
	initComponent: function(){
		sw.Promed.swMainCloseCardWindowRegional.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(){
		sw.Promed.swMainCloseCardWindowRegional.superclass.show.apply(this, arguments);
	},
});