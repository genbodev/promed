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
		this.addListener('beforeadd', function(form, field) {			if ( field.events['change'] && field.isFormField )
			{
				field.addListener('change', function(changed_field, value) {					this_form.onFieldChange(changed_field, value);				});
			}		});
		sw.Promed.FormPanelWithChangeEvents.superclass.initComponent.apply(this, arguments);
		// добавляем обработчики onChange для каждого компонента
	},
	onFieldChange: function(field, value) {  		this.items.each(function(current_field) {  			if ( current_field.onFormParamChange )
			{				if ( Ext.type(current_field.onFormParamChange) == 'function' )
				{					// вызываем метод
					current_field.onFormParamChange(field, value);				}			}  		})	}

});


