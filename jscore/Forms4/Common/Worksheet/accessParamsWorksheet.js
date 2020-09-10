Ext6.define('common.Worksheet.accessParamsWorksheet', {
	extend: 'base.BaseForm',
	alias: 'widget.accessParamsWorksheet',
//	height:400,
	width: 400,
	title: 'Параметры доступа к анкете',
	constrain: true,
	layout: 'vbox',
	show:function(data){
		var me = this;

		if(data && data.MedicalForm_id){
			me.MedicalForm_id = data.MedicalForm_id;

			Ext6.Ajax.request({
				url: '/?c=MedicalForm&m=getMedicalForm',
				params: data,
				callback: function(opt, success, response){
					if (success && response && response.responseText) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						if (response_obj.success) {
							me.down('segmentedbutton[name=sex_id]').setValue(parseInt(response_obj.data.Sex_id));
							me.down('segmentedbutton[name=age]').setValue(parseInt(response_obj.data.PersonAgeGroup_id));
						}
					}
				}
			});
		}

		me.callParent(arguments);
	},
	items:[{
		border: false,
		xtype: 'form',
		layout: 'vbox',
		padding: 10,
		items: [{
			xtype: 'container',
			layout: {
				type: 'hbox',
				pack: 'start',
				align: 'stretch'
			},
			items : [
				{
					xtype: 'label',
					text: 'Пол',
					width: 100
				},
				{
					xtype: 'segmentedbutton',
					name: 'sex_id',
					items: [{
						text: 'Все',
						value: 3
					},{
						text: 'Мужчины',
						value: 1
					},{
						text: 'Женщины',
						value: 2
					}]
				}
			]
		},	{
			xtype: 'container',
			layout: {
				type: 'hbox',
				pack: 'start',
				align: 'stretch'
			},
			items : [
				{
					xtype: 'label',
					text: 'Возраст',
					width: 100
				},
				{
					xtype: 'segmentedbutton',
					name: 'age',
					items: [{
							text: 'Все',
							value: 3
						},
						{
							text: 'до 14 лет',
							value: 1
						},
						{
							text: 'после 14 лет',
							value: 2
						}
					]
				}
			]
		}]
	}],
	buttons: [
		'->',
		{
			//cls: 'textBtn gray',
			xtype: 'button',
			text: 'Отмена',
			handler: function (cmp) {
				this.up('window').close()
			}
		}, {
			//cls: 'textBtn blue',
			xtype: 'button',
			text: 'Сохранить',
			handler: function () {
				var wnd = this.up('window'),
					sex_id  = wnd.down('segmentedbutton[name=sex_id]').getValue(),
					age = wnd.down('segmentedbutton[name=age]').getValue(),
					MedicalForm_id = wnd.MedicalForm_id;

				if(!sex_id || !age){
					Ext6.Msg.alert("Ошибка", "Укажите значения");
					return false;
				}

				Ext6.Ajax.request({
					url: '/?c=MedicalForm&m=saveMedicalForm',
					params: {
						PersonAgeGroup_id: age,
						Sex_id: sex_id,
						MedicalForm_id: MedicalForm_id
					},
					success: function (res) {
						Ext6.data.StoreManager.lookup('allMedicalForms').reload();
						wnd.close();
					}
				});
			}
		}
	]
});