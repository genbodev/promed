/**
 * Форма дерева решений
 */
Ext6.define('smp.views.decisionTree.edit.Form', {
	extend: 'Ext6.form.Panel',
	alias: 'widget.decisionTree.edit.form',
	// Разделитель для текста ответа и кода повода, если он указан
	// Например: артериальное кровотечение -> !01 
	text_delimiter: ' -> ',
	layout: {
		type: 'form',
		//align: 'stretch'
	},
	requires: [
		'smp.ux.form.field.ComboCmpReason'
	],
	initComponent: function () {
		Ext6.applyIf(this, {
			items: [
				{
					xtype: 'form',
					border: false,
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					defaults: {
						margin: '10'
					},
					items: [
						{
							xtype: 'hidden',
							name: 'AmbulanceDecigionTree_id',
						},
						{
							xtype: 'ambulanceDecigionTreeType',
							allowBlank: false,
							name: 'AmbulanceDecigionTree_Type',
							fieldLabel: 'Тип'
						},
						{
							xtype: 'textfield',
							name: 'TreeNode_Text',
							fieldLabel: 'Текст',
							maxLength: 255,
							maxLengthText: 'Максимальное количество символов - 255'
						},
						{
							xtype: 'comboCmpReason', // xtype: 'cmpReasonCombo'
							name: 'CmpReason_id',
							fieldLabel: 'Повод'
						}
					],
					buttons: [
						{
							xtype: 'button',
							text: 'Сохранить',
							itemId: 'save'
						},
						{
							xtype: 'button',
							text: 'Отменить',
							itemId: 'cancel'
						}
					]
				}
			]
		});
		this.callParent(arguments);
	}
});