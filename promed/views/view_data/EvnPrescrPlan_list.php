<?php
if (isset($item_arr) && is_array($item_arr)) {
	if (empty($forRightPanel)) {
		$forRightPanel = 0;
	}
	$view = new swPrescriptionGroupView('EvnPrescrPlan', $item_arr, $forRightPanel);
	echo $view->getView();
} else {
	echo '';
}