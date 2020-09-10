/**
 * sw.Promed.BaseFrame. Класс базового фрейма
 *
 *
 * @project  PromedWeb
 * @copyright  (c) Swan Ltd, 2009
 * @package frames
 * @author  Марков Андрей
 * @class sw.Promed.BaseFrame
 * @extends Ext.form.FormPanel
 * @version 20.02.2009
 */

sw.Promed.BaseFrame = function(config)
{
	//Ext.apply(this, config);
	sw.Promed.BaseFrame.superclass.constructor.call(this);
};

Ext.extend(sw.Promed.BaseFrame, Ext.Panel,
{
	region: 'frame',
	id: 'baseframe',
	collapsible   : true,
	labelWidth: 120,
	width: '100%',
	//plain: false,
	defaults:
		{
		width: '100%',
		msgTarget: 'side'
		},
	layoutConfig:
		{
		labelSeparator: '' // разделитель Labels, по умолчанию = :
		},

	initComponent: function() {
		sw.Promed.BaseFrame.superclass.initComponent.apply(this, arguments);
	}
});
