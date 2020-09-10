/**
* swCmpCallCardNewCloseCardWindow Наследник карты закрытия вызова
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

sw.Promed.swCmpCallCardNewCloseCardWindow = Ext.extend(sw.Promed.swMainCloseCardWindow,{
	objectName: 'swCmpCallCardNewCloseCardWindow',
	objectSrc: '/jscore/Forms/Ambulance/swCmpCallCardNewCloseCardWindow.js',
	cls: 'swCmpCallCardNewCloseCardWindow',
	
	initComponent: function(){
		sw.Promed.swCmpCallCardNewCloseCardWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(){
		sw.Promed.swCmpCallCardNewCloseCardWindow.superclass.show.apply(this, arguments);
	},
});
