Ext6.define('common.Drug.controllers.DrugWindowController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.DrugWindowController',

	doSave: function()
	{
		var wnd = this.getView(),
			wndVm = this.getViewModel(),
			formPanel = wnd.down('form'),
			form = formPanel.getForm(),
			vm = formPanel.getViewModel(),
			MSF = sw.Promed.MedStaffFactByUser.last || {},

			formData = form.getValues(),

			Drug = form.findField('Drug_id'),

			parentAction = vm.get('parentAction'),
			Drug_Code = Drug.getFieldValue('Drug_Code') ||'',
			Drug_Name = Drug.getFieldValue('Drug_FullName') || '',
			Drug_id = vm.get('Drug_id'),
			DrugPrepFas_id = vm.get('DrugPrepFas_id'),
			DocumentUcStr_oid = vm.get('DocumentUcStr_oid'),
			Storage_id = vm.get('Storage_id'),
			Mol_id = vm.get('Mol_id'),
			EvnDrug_setDate = vm.get('EvnDrug_setDate'),
			EvnDrug_setTime = vm.get('EvnDrug_setTime'),
			EvnDrug_Kolvo = form.findField('EvnDrug_Kolvo').getValue(),
			EvnDrug_Kolvo_Show = form.findField('EvnDrug_Kolvo_Show').getValue(),
			EvnDrug_Price = form.findField('EvnDrug_Price').getValue(),
			EvnDrug_Sum = form.findField('EvnDrug_Sum').getValue(),
			EvnDrug_KolvoEd = vm.get('EvnDrug_KolvoEd'),
			EvnPrescr_id = vm.get('EvnPrescr_id');


		var loadMask = new Ext6.LoadMask(wnd, {msg: "Сохранение..."});
		loadMask.show();

		if ( ! form.isValid() )
		{
			if (IS_DEBUG)
			{
				form.getFields().filterBy(function(field){console.log(field); console.log(field.name + ' = ' + field.validate())})
			}

			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					loadMask.hide();
					formPanel.getFirstInvalidEl().focus(true);
					log(formPanel.getFirstInvalidEl());
				},
				icon: Ext6.Msg.WARNING,
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				title: 'Проверка данных формы'
			});
			return false;
		}


		// возвращаем просто запись в грид, в базу не сохраняем
		if (parentAction === 'add')
		{
			Ext6.applyIf(formData, {
				MSF_LpuSection_id: MSF.LpuSection_id,
				MSF_MedPersonal_id : MSF.MedPersonal_id,
				MSF_MedService_id : MSF.MedService_id,

				Drug_Code: Drug_Code,
				Drug_Name: Drug_Name,
				EvnDrug_Price: EvnDrug_Price,
				EvnDrug_Sum: EvnDrug_Sum,

				//accessType: 'edit',
				//EvnClass_SysNick: 'EvnDrug',
			});


			var data = {
				evnDrugData: [formData]
			};


			wnd.callback(data);
			wnd.close();

			return;
		}


		//if ( ! this.show_diff_gu)
		//{ //перед сохранением, если ед. списания скрыты, приравниваем их к ед. учета
		//	form.findField('GoodsUnit_id').setValue(form.findField('GoodsUnit_bid').getValue());
		//	form.findField('EvnDrug_KolvoEd').setValue(form.findField('EvnDrug_Kolvo').getValue());
		//}

		form.submit({
			params:
				{	MSF_LpuSection_id: MSF.LpuSection_id,
					MSF_MedPersonal_id : MSF.MedPersonal_id,
					MSF_MedService_id : MSF.MedService_id,
					EvnDrug_Price: EvnDrug_Price,
					EvnDrug_Sum: EvnDrug_Sum
				},
			
			failure: function(form, reply)
			{
				loadMask.hide();

				if ( reply.result )
				{
					if ( reply.result.Error_Msg )
					{
						Ext6.Msg.alert(langs('Ошибка'), reply.result.Error_Msg);
					}
					else
					{
						Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
					}
				}
			},
			
			success: function(form, reply)
			{
				loadMask.hide();

				//form.findField('EvnDrug_id').setValue(reply.result.EvnDrug_id);

				var Drug_Code = Drug.getFieldValue('Drug_Code') ||'',
					Drug_Name = Drug.getFieldValue('Drug_FullName') || '',
					EvnPrescrTreatDrug_FactCount = reply.result.EvnPrescrTreatDrug_FactCount || 1,
					data = {};


				data.evnDrugData = {
					accessType: 'edit',
					EvnClass_SysNick: 'EvnDrug',
					EvnDrug_id: reply.result.EvnDrug_id,
					Drug_id:  Drug_id,
					DrugPrepFas_id:  DrugPrepFas_id,
					DocumentUcStr_oid:  DocumentUcStr_oid,
					Storage_id:  Storage_id,
					Mol_id:  Mol_id,
					Drug_Code: Drug_Code,
					Drug_Name: Drug_Name,
					EvnDrug_setDate:  EvnDrug_setDate,
					EvnDrug_setTime:  EvnDrug_setTime,
					EvnDrug_Kolvo:  EvnDrug_Kolvo,
					EvnDrug_KolvoEd:  EvnDrug_KolvoEd,
					EvnPrescrTreatDrug_FactCount: EvnPrescrTreatDrug_FactCount,
					EvnPrescr_id: EvnPrescr_id
				};

				wnd.callback(data);
				wnd.close();
			}
		});
		return true;
	}
});