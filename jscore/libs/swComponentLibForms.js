/**
* sw.Promed.FormPanelWithChangeEvents - класс формы с обработкой события изменения поля.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      17.09.2009
*/

sw.Promed.FormPanelWithChangeEvents = Ext.extend(Ext.FormPanel,
{
	initComponent: function()
	{
		var this_form = this;
		this.addListener('beforeadd', function(form, field) {
			{
				field.addListener('change', function(changed_field, value) {
			}
		sw.Promed.FormPanelWithChangeEvents.superclass.initComponent.apply(this, arguments);
		// добавляем обработчики onChange для каждого компонента
	},
	onFieldChange: function(field, value) {
			{
				{
					current_field.onFormParamChange(field, value);

});

