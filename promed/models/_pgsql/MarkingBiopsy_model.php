<?php
class MarkingBiopsy_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление
	 */
	public function deleteMarkingBiopsy($data) {
		return $this->queryResult("
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_MarkingBiopsy_del(
				MarkingBiopsy_id := :MarkingBiopsy_id
			)
		", array(
			'MarkingBiopsy_id' => $data['MarkingBiopsy_id']
		));
	}

	/**
	 * Получение данных для редактирования
	 */
	public function loadMarkingBiopsyEditForm($data) {
		return $this->queryResult("
			select
				MarkingBiopsy_id as \"MarkingBiopsy_id\",
				EvnDirectionHistologic_id as \"EvnDirectionHistologic_id\",
				MarkingBiopsy_NumBot as \"MarkingBiopsy_NumBot\",
				MarkingBiopsy_LocalProcess as \"MarkingBiopsy_LocalProcess\",
				MarkingBiopsy_NatureProcess as \"MarkingBiopsy_NatureProcess\",
				MarkingBiopsy_ObjKolvo as \"MarkingBiopsy_ObjKolvo\",
				AnatomicLocal_id as \"AnatomicLocal_id\",
				MaterialChange_id as \"MaterialChange_id\",
				MarkingBiopsy_Size as \"MarkingBiopsy_Size\",
				MarkingBiopsy_Shape as \"MarkingBiopsy_Shape\",
				MarkingBiopsy_Border as \"MarkingBiopsy_Border\",
				MarkingBiopsy_Consistence as \"MarkingBiopsy_Consistence\",
				MarkingBiopsy_ColorSkin as \"MarkingBiopsy_ColorSkin\"
			from
				v_MarkingBiopsy
			where
				MarkingBiopsy_id = :MarkingBiopsy_id
		", array(
			'MarkingBiopsy_id' => $data['MarkingBiopsy_id']
		));
	}


	/**
	 * Получение списка записей для раздела "Маркировка материала"
	 */
	public function loadMarkingBiopsyGrid($data) {
		return $this->queryResult("
			select
				MB.MarkingBiopsy_id as \"MarkingBiopsy_id\",
				MB.MarkingBiopsy_NumBot as \"MarkingBiopsy_NumBot\",
				MB.MarkingBiopsy_LocalProcess as \"MarkingBiopsy_LocalProcess\",
				COALESCE(AL.AnatomicLocal_Name, MB.MarkingBiopsy_LocalProcess) as \"AnatomicLocal_Text\",
				MB.MarkingBiopsy_NatureProcess as \"MarkingBiopsy_NatureProcess\",
				MB.MarkingBiopsy_ObjKolvo as \"MarkingBiopsy_ObjKolvo\",
				MB.AnatomicLocal_id as \"AnatomicLocal_id\",
				MB.MaterialChange_id as \"MaterialChange_id\",
				MB.MarkingBiopsy_Size as \"MarkingBiopsy_Size\",
				MB.MarkingBiopsy_Shape as \"MarkingBiopsy_Shape\",
				MB.MarkingBiopsy_Border as \"MarkingBiopsy_Border\",
				MB.MarkingBiopsy_Consistence as \"MarkingBiopsy_Consistence\",
				MB.MarkingBiopsy_ColorSkin as \"MarkingBiopsy_ColorSkin\",
				1 as \"RecordStatus_Code\"
			from
				v_MarkingBiopsy MB
				LEFT JOIN nsi.v_AnatomicLocal AL ON AL.AnatomicLocal_id = MB.AnatomicLocal_id
			where
				MB.EvnDirectionHistologic_id = :EvnDirectionHistologic_id
		", array(
			'EvnDirectionHistologic_id' => $data['EvnDirectionHistologic_id']
		));
	}

	/**
	 * Сохранение
	 */
	public function saveMarkingBiopsy($data) {
		return $this->queryResult("
			select
				MarkingBiopsy_id as \"MarkingBiopsy_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_MarkingBiopsy_" . (!empty($data['MarkingBiopsy_id']) ? 'upd' : 'ins') . "(
				MarkingBiopsy_id := :MarkingBiopsy_id,
				EvnDirectionHistologic_id := :EvnDirectionHistologic_id,
				MarkingBiopsy_NumBot := :MarkingBiopsy_NumBot,
				MarkingBiopsy_LocalProcess := :MarkingBiopsy_LocalProcess,
				MarkingBiopsy_NatureProcess := :MarkingBiopsy_NatureProcess,
				MarkingBiopsy_ObjKolvo := :MarkingBiopsy_ObjKolvo,
				@AnatomicLocal_id := :AnatomicLocal_id,
				@MaterialChange_id := :MaterialChange_id,
				@MarkingBiopsy_Size := :MarkingBiopsy_Size,
				@MarkingBiopsy_Shape := :MarkingBiopsy_Shape,
				@MarkingBiopsy_Border := :MarkingBiopsy_Border,
				@MarkingBiopsy_Consistence := :MarkingBiopsy_Consistence,
				@MarkingBiopsy_ColorSkin := :MarkingBiopsy_ColorSkin,
				pmUser_id := :pmUser_id
			)
		", array(
			'MarkingBiopsy_id' => (!empty($data['MarkingBiopsy_id']) ? $data['MarkingBiopsy_id'] : null),
			'EvnDirectionHistologic_id' => $data['EvnDirectionHistologic_id'],
			'MarkingBiopsy_NumBot' => $data['MarkingBiopsy_NumBot'],
			'MarkingBiopsy_LocalProcess' => $data['MarkingBiopsy_LocalProcess'],
			'MarkingBiopsy_NatureProcess' => $data['MarkingBiopsy_NatureProcess'],
			'MarkingBiopsy_ObjKolvo' => $data['MarkingBiopsy_ObjKolvo'],
			'AnatomicLocal_id' => (!empty($data['AnatomicLocal_id'])) ? $data['AnatomicLocal_id'] : null,
			'MaterialChange_id' => (!empty($data['MaterialChange_id'])) ? $data['MaterialChange_id'] : null,
			'MarkingBiopsy_Size' => (!empty($data['MarkingBiopsy_Size'])) ? $data['MarkingBiopsy_Size'] : null,
			'MarkingBiopsy_Shape' => (!empty($data['MarkingBiopsy_Shape'])) ? $data['MarkingBiopsy_Shape'] : null,
			'MarkingBiopsy_Border' => (!empty($data['MarkingBiopsy_Border'])) ? $data['MarkingBiopsy_Border'] : null,
			'MarkingBiopsy_Consistence' => (!empty($data['MarkingBiopsy_Consistence'])) ? $data['MarkingBiopsy_Consistence'] : null,
			'MarkingBiopsy_ColorSkin' => (!empty($data['MarkingBiopsy_ColorSkin'])) ? $data['MarkingBiopsy_ColorSkin'] : null,
			'pmUser_id' => $data['pmUser_id'],
		));
	}
}
