<?php
/**
 * Для зубной карты и прочих стомат.плюшек
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Stom
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author		 Alexander Permyakov
 * @version      06.2014
 */
defined('BASEPATH') or die ('No direct script access allowed');

class SwJawPart
{
	protected $_code;
	protected $_isTop;
	protected $_isLeft;

	/**
	 * @param int $JawPartType_Code
	 */
	function __construct($JawPartType_Code)
	{
		$this->_code = (int) $JawPartType_Code;
		$this->_isTop = in_array($this->getCode(), array(1,2));
		$this->_isLeft = in_array($this->getCode(), array(1,4));
	}

	/**
	 * @return int
	 */
	public function getCode()
	{
		return $this->_code;
	}

	/**
	 * @return bool
	 */
	public function isLeft()
	{
		return $this->_isLeft;
	}

	/**
	 * @return bool
	 */
	public function isTop()
	{
		return $this->_isTop;
	}

}

class SwTooth
{
	protected $_jawPart;
	protected $_code;
	protected $_sysNum;
	protected $_position;
	protected $_isHasFiveSegments;
	protected $_surfacePositions = array();
	/**
	 * @var SwToothSurface[]
	 */
	protected $_surfaces = array();
	/**
	 * @var SwToothStateClass[]
	 */
	protected $_states = array();
	/**
	 * @var SwToothStateClass
	 */
	protected $_type;

	/**
	 * Возвращает фильтр получения списка зубов по умолчанию
	 * @param int $Person_age
	 * @param string $alias
	 * @return string
	 */
	static public function getDefaultFilter($Person_age, $alias = 'v_Tooth')
	{
		switch (true) {
			case (5 > $Person_age):
				//До 5 лет вместо зубов, для которых состояния отсутствуют в БД,
				// отображать 20 молочных зубов
				$where_clause = "({$alias}.Tooth_Code > 50)";
				break;
			case (14 < $Person_age):
				//С 14 лет вместо зубов, для которых состояния отсутствуют в БД,
				// отображать коренные
				$where_clause = "({$alias}.Tooth_Code < 49)";
				break;
			default:
				//С 5 до 14 вместо зубов, для которых состояния отсутствуют в БД,
				// отображать 20 молочных и остальные коренные без 8-к
				$where_clause = "(
					{$alias}.Tooth_Code > 50 or
					{$alias}.Tooth_Code in (16,17,26,27,36,37,46,47)
				)";
				break;
		}
		return $where_clause;
	}
	
	/**
	 * @param int $Tooth_Code
	 * @param SwJawPart $jawPart
	 * @param array $states
	 */
	function __construct($Tooth_Code, SwJawPart $jawPart, $states = array())
	{
		$this->_jawPart = $jawPart;
		$this->_code = (int) $Tooth_Code;
		$this->_sysNum = (int) substr($Tooth_Code, 1, 1);
		$this->_isHasFiveSegments = ($this->_sysNum > 3);
		$isTop = $this->getJawPart()->isTop();
		$isLeft = $this->getJawPart()->isLeft();
		/*
        switch (true) {
            case (pos == 1): id = isTop ? 1 : 3; break;
            case (pos == 3): id = isTop ? 3 : 1; break;
            case (pos == 2): id = isLeft ? 2 : 4; break;
            case (pos == 4): id = isLeft ? 4 : 2; break;
            case (pos == 5 && isHasFiveSegments): id = 5; break;
        }
		 */
		$this->_surfacePositions = array(
			// ToothSurfaceType_Code => pos
			'1' => $isTop ? '1' : '3', // Вестибулярная
			'3' => $isTop ? '3' : '1', // Язычная
			'2' => $isLeft ? '2' : '4', // Мезиальная
			'4' => $isLeft ? '4' : '2', // Дистальная
		);
		if ($this->_isHasFiveSegments) {
			$this->_surfacePositions['5'] = '5'; // Окклюзионная
		}

		$this->_states = array();
		foreach ($states as $state) {
			if ($state['ToothSurfaceType_Code'] > 0 && in_array($state['ToothStateClass_SysNick'], array(
				'caries','seal',
			))) {
				$segment = $this->getSurface($state['ToothSurfaceType_Code']);
				if ($segment) {
					$segment->addState($state['ToothStateClass_Code'], $state['ToothStateClass_SysNick']);
				}
			} else if (in_array($state['ToothStateClass_SysNick'], array(
				'milk','const','nothing','implant',
			))) {
				$this->_type = new SwToothStateClass($state['ToothStateClass_Code'], $state['ToothStateClass_SysNick'], $this);
			} else {
				$this->addState($state['ToothStateClass_Code'], $state['ToothStateClass_SysNick']);
			}
		}
		//if (62 == $Tooth_Code) exit(var_export($this->_type, true));
		if (empty($this->_type)) {
			$this->_states = array();
			$this->_surfaces = array();
		}
		switch (true) {
			case ($isTop && $this->_isHasFiveSegments):
				$this->_position = '1';
				break;
			case ($isTop && !$this->_isHasFiveSegments):
				$this->_position = '2';
				break;
			case (!$isTop && $this->_isHasFiveSegments):
				$this->_position = '3';
				break;
			case (!$isTop && !$this->_isHasFiveSegments):
				$this->_position = '4';
				break;
			default:
				$this->_position = null;
				break;
		}
	}

	/**
	 * @param string $code ToothStateClass_Code
	 * @param string $sysNick ToothStateClass_SysNick
	 * @return int
	 */
	public function addState($code, $sysNick)
	{
		$this->_states[] = new SwToothStateClass($code, $sysNick, $this);;
	}

	/**
	 * @return int
	 */
	public function getCode()
	{
		return $this->_code;
	}

	/**
	 * Возвращает последнюю цифру номера зуба
	 * @return int
	 */
	function getSysNum()
	{
		return $this->_sysNum;
	}

	/**
	 * @return SwJawPart
	 */
	public function getJawPart()
	{
		return $this->_jawPart;
	}

	/**
	 * Имеет ли зуб 5 поверхностей
	 * @return bool
	 */
	function isHasFiveSegments()
	{
		return $this->_isHasFiveSegments;
	}

	/**
	 * Возвращает часть имени файла изображения состояния зуба,
	 * соответствующую положению и числу поверхностей зуба
	 * @return string
	 */
	function getPosition()
	{
		return $this->_position;
	}

	/**
	 * @return array
	 */
	public function getSurfacePositions()
	{
		return $this->_surfacePositions;
	}

	/**
	 * Возвращает имя файла изображения состояния зуба
	 * @param string $layerName
	 * @return string
	 */
	function getImageFileName($layerName)
	{
		$position = $this->getPosition();
		$name = ToothMap::getImageName($layerName, $position);
		$filename = ToothMap::getBasePath() . '/' . $name . '.png';
		return $filename;
	}

	/**
	 * @param int $ToothSurfaceType_Code
	 * @param string $position
	 * @return array|SwToothSurface
	 * @throws Exception
	 */
	public function getSurface($ToothSurfaceType_Code = null, $position = null)
	{
		if (empty($ToothSurfaceType_Code) && empty($position)) {
			return $this->_surfaces;
		}
		if (!empty($position) && !empty($this->_surfaces[$position])) {
			return $this->_surfaces[$position];
		}
		if (!empty($ToothSurfaceType_Code) && empty($position)) {
			$ToothSurfaceType_Code .= '';
			$ToothSurfaceTypes = $this->getSurfacePositions();
			if (isset($ToothSurfaceTypes[$ToothSurfaceType_Code])) {
				$position = $ToothSurfaceTypes[$ToothSurfaceType_Code];
			}
		}
		if (empty($position)) {
			return null;
		}
		if (empty($this->_surfaces[$position])) {
			$this->_surfaces[$position] = new SwToothSurface($ToothSurfaceType_Code, $this, $position);
		}
		return $this->_surfaces[$position];
	}

	/**
	 * @return SwToothStateClass[]
	 */
	public function getStates()
	{
		return $this->_states;
	}

	/**
	 * @return SwToothSurface[]
	 */
	public function getSurfaces()
	{
		return $this->_surfaces;
	}

	/**
	 * @return SwToothStateClass
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Список кодов состояний для отображения
	 * @return array
	 */
	public function getStateClassCodeList()
	{
		$codeList = array();
		if (empty($this->_type)) {
			return $codeList;
		}
		//$codeList[] = $this->_type->getCode();
		foreach ($this->_states as $state) {
			$codeList[] = $state->getCode();
		}
		foreach ($this->_surfaces as $surface) {
			$states = $surface->getStates();
			foreach ($states as $state) {
				$codeList[] = $state->getCode();
			}
		}
		$codeList = array_unique($codeList);
		if ($this->getJawPart()->isTop()) {
			$codeList = array_reverse($codeList);
		}
		return $codeList;
	}

	/**
	 * Входит ли состояние зуба в список взаимноисключающих при отображении
	 * @param $sysNick
	 * @return bool
	 */
	public function isClassSysNick($sysNick)
	{
		return in_array($sysNick, array(
			'milk','const','cheektooth','crown','nothing','implant',
		));
	}

	/**
	 * Список состояний, которые могут быть отображены как зуб
	 * и при отображении взаимно исключают друг друга
	 * @return array
	 */
	public function getClassSysNickList()
	{
		$sysNickList = array();
		if (!empty($this->_type)) {
			$sysNickList[] = $this->_type->getSysNick();
		}
		foreach ($this->_states as $state) {
			if ($this->isClassSysNick($state->getSysNick())) {
				$sysNickList[] = $state->getSysNick();
			}
		}
		return $sysNickList;
	}

	/**
	 * Список состояний зуба, которые могут быть отображены над зубом
	 * @return array
	 */
	public function getStateClassSysNickList()
	{
		$sysNickList = array();
		foreach ($this->_states as $state) {
			if (!$this->isClassSysNick($state->getSysNick())) {
				$sysNickList[] = $state->getSysNick();
			}
		}
		return $sysNickList;
	}
}

class SwToothSurface
{
	protected $_code;
	protected $_tooth;
	protected $_position;
	/**
	 * @var SwToothStateClass[]
	 */
	protected $_states = array();

	/**
	 * @param int $ToothSurfaceType_Code
	 * @param SwTooth $tooth
	 * @param string $position
	 */
	function __construct($ToothSurfaceType_Code, SwTooth $tooth, $position = null)
	{
		$this->_code = (int) $ToothSurfaceType_Code;
		$this->_tooth = $tooth;
		$this->_position = $position;
	}

	/**
	 * @param string $code ToothStateClass_Code
	 * @param string $sysNick ToothStateClass_SysNick
	 * @return int
	 */
	public function addState($code, $sysNick)
	{
		$this->_states[] = new SwToothStateClass($code, $sysNick, $this->_tooth, $this);
	}

	/**
	 * @return int
	 */
	public function getCode()
	{
		return $this->_code;
	}

	/**
	 * @return SwTooth
	 */
	public function getTooth()
	{
		return $this->_tooth;
	}

	/**
	 * Возвращает часть имени файла изображения состояния поверхности зуба,
	 * соответствующую положению зуба и поверхности, а также числу поверхностей зуба
	 * @return string
	 */
	function getPosition()
	{
		return $this->_position;
	}

	/**
	 * Возвращает имя файла изображения состояния поверхности зуба
	 * @param string $layerName
	 * @return string
	 */
	function getImageFileName($layerName)
	{
		$position = $this->getTooth()->getPosition();
		$segment = $this->getPosition();
		$name = ToothMap::getImageName($layerName, $position, $segment);
		$filename = ToothMap::getBasePath() . '/' . $name . '.png';
		return $filename;
	}

	/**
	 * @return SwToothStateClass[]
	 */
	public function getStates()
	{
		return $this->_states;
	}

	/**
	 * Список состояний, которые могут быть отображены над зубом
	 * @return array
	 */
	public function getStateClassSysNickList()
	{
		$sysNickList = array();
		foreach ($this->_states as $state) {
			$sysNickList[] = $state->getSysNick();
		}
		return $sysNickList;
	}
}

class SwToothStateClass
{
	protected $_code;
	protected $_sysNick;
	protected $_tooth;
	protected $_surface;

	/**
	 * @param string $ToothStateClass_Code
	 * @param string $ToothStateClass_SysNick
	 * @param SwTooth $tooth
	 * @param SwToothSurface $surface
	 */
	function __construct($ToothStateClass_Code, $ToothStateClass_SysNick, SwTooth $tooth, SwToothSurface $surface = null)
	{
		$this->_code = $ToothStateClass_Code;
		$this->_sysNick = $ToothStateClass_SysNick;
		$this->_tooth = $tooth;
		$this->_surface = $surface;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->_code;
	}

	/**
	 * @return string
	 */
	public function getSysNick()
	{
		return $this->_sysNick;
	}

	/**
	 * @return SwToothSurface
	 */
	public function getSurface()
	{
		return $this->_surface;
	}

	/**
	 * @return SwTooth
	 */
	public function getTooth()
	{
		return $this->_tooth;
	}
}

class ToothMap
{
	//const BASE_IMAGE_BORDER_TOP_WIDTH = 1;
	//const BASE_IMAGE_BORDER_BOTTOM_WIDTH = 1;
	const BASE_IMAGE_BORDER_SEP_WIDTH = 1;
	const BASE_IMAGE_WIDTH = 721;
	const BASE_IMAGE_HEIGHT = 323;
	const BASE_IMAGE_OFFSET_X = 2;
	const BASE_IMAGE_OFFSET_Y = 1;
	const BASE_IMAGE_CENTER_X = 359;
	const BASE_IMAGE_CENTER_Y = 157;
	const STATE_IMAGE_WIDTH = 42;
	const STATE_IMAGE_HEIGHT = 77;
	const TOOTH_NUM_MARGIN_Y = 6;//HEIGHT = 6
	const TOOTH_STATES_INTERVAL_Y = 3;//HEIGHT = 5

	static public $isForPrint = false;
	static private $_im;
	static private $maxt;
	static private $maxb;

	/**
	 * Генерируем изображение зубной карты для отображения в документах и отчетах
	 * @param array $toothStates Состояния зубов
	 * @param array $defaultTooth Данные по зубам без состояний соответственно возрасту
	 * @param array $evnData Данные стомат.посещения
	 * @throws Exception
	 */
	static function applyData($toothStates, $defaultTooth, $evnData) {
		if (self::$_im) {
			imagedestroy(self::$_im);
		}
		self::$_im = imagecreatefrompng(self::getBaseFileName());
		if (!self::$_im) {
			throw new Exception('Не удалось открыть базовое изображение');
		}
		self::$maxt = self::BASE_IMAGE_HEIGHT;
		self::$maxb = 0;
		/*
		Проблема отображения в отчетах в pdf кроется вовсе не в способе создания картинки
		$width = self::BASE_IMAGE_WIDTH;
		$height = self::BASE_IMAGE_HEIGHT;
		self::$_im = @imagecreatetruecolor($width, $height);
		$src_im = imagecreatefrompng(self::getBaseFileName());
		imagecopy(self::$_im, $src_im, 0, 0, 0, 0, $width, $height);
		*/
		//var_export($defaultTooth); exit('');
		//var_export($toothStates); exit('');
		$jawParts = self::processingToothStates($toothStates, $defaultTooth, $evnData, true);
		foreach ($jawParts as $jaw => $data) {
			$jawPart = new SwJawPart($jaw);
			foreach ($data['ToothList'] as $row) {
				$tooth = new SwTooth($row['Tooth_Code'], $jawPart, $row['states']);
				self::renderTooth($tooth);
			}
		}
		// обрезаем 
		self::$maxt -= 8;
		$img_oh = self::$maxb - self::$maxt;
		$img_o = imagecreatetruecolor(self::BASE_IMAGE_WIDTH, $img_oh);
		imagecopy($img_o, self::$_im, 0, 0, 0, self::$maxt, self::BASE_IMAGE_WIDTH, self::BASE_IMAGE_HEIGHT);
		$black = imagecolorallocate($img_o, 0, 0, 0);
		imageline($img_o, 0, 0, self::BASE_IMAGE_WIDTH, 0, $black);
		imageline($img_o, 0, $img_oh-1, self::BASE_IMAGE_WIDTH, $img_oh-1, $black);
		self::$_im = $img_o;
	}

	/**
	 * Координаты левого верхнего угла изображения состояния
	 * относительно базового изображения
	 * @param SwTooth $tooth
	 * @return array
	 */
	static private function getStateXY(SwTooth $tooth)
	{
		if ($tooth->getJawPart()->isLeft()) {
			$x = self::BASE_IMAGE_CENTER_X
				- ($tooth->getSysNum() * self::STATE_IMAGE_WIDTH)
				- self::BASE_IMAGE_OFFSET_X;
		} else {
			$x = self::BASE_IMAGE_CENTER_X
				+ (($tooth->getSysNum() - 1) * self::STATE_IMAGE_WIDTH)
				+ self::BASE_IMAGE_BORDER_SEP_WIDTH
				+ self::BASE_IMAGE_OFFSET_X;
		}
		if ($tooth->getJawPart()->isTop()) {
			$y = self::BASE_IMAGE_CENTER_Y
				- self::STATE_IMAGE_HEIGHT
				- self::BASE_IMAGE_OFFSET_Y;
		} else {
			$y = self::BASE_IMAGE_CENTER_Y
				+ self::BASE_IMAGE_BORDER_SEP_WIDTH
				+ self::BASE_IMAGE_OFFSET_Y;
		}
		return array($x, $y);
	}

	/**
	 * Накладывает на базовое изображение изображение состояния
	 * @param string $srcFileName
	 * @param int $dst_x
	 * @param int $dst_y
	 * @return boolean
	 * @throws Exception
	 */
	static private function mergeImageState($srcFileName, $dst_x, $dst_y)
	{
		if (!$srcFileName || !file_exists($srcFileName)) {
			return false;
		}
		$src_im = imagecreatefrompng($srcFileName);
		$src_w = self::STATE_IMAGE_WIDTH;
		$src_h = self::STATE_IMAGE_HEIGHT;
		return imagecopy(self::$_im, $src_im, $dst_x, $dst_y, 0, 0, $src_w, $src_h);
		//$pct = 100;//прозрачность
		//return imagecopymerge(self::$_im, $src_im, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
	}

	/**
	 * Отрисовка зуба и его состояний
	 * @param SwTooth $tooth
	 * @throws Exception
	 */
	static private function renderTooth(SwTooth $tooth)
	{
		$ToothStateClassCodeList = $tooth->getStateClassCodeList();
		if ($tooth->getJawPart()->isTop()) {
			$ToothStateClassCodeList = array_reverse($ToothStateClassCodeList);
		}
		$ToothTypeList = $tooth->getClassSysNickList();
		$dst_xy = self::getStateXY($tooth);
		// рисуем зуб
		$type = $tooth->getType();
		switch (true) {
			case (empty($type) && self::isVisibleImage('germ', $ToothStateClassCodeList)):
				// Зуб без состояний в БД (невыросший)
				self::mergeImageState($tooth->getImageFileName('germ'),
					$dst_xy[0], $dst_xy[1]);
				break;
			case (self::isVisibleImage('const|milk', $ToothTypeList)):
				// Зуб без отклонений
				self::mergeImageState($tooth->getImageFileName('const|milk'),
					$dst_xy[0], $dst_xy[1]);
				break;
			case (self::isVisibleImage('implant+crown', $ToothTypeList)):
				// Искусственный зуб + коронка
				self::mergeImageState($tooth->getImageFileName('implant+crown'),
					$dst_xy[0], $dst_xy[1]);
				break;
			case (self::isVisibleImage('implant', $ToothTypeList)):
				// Искусственный зуб
				self::mergeImageState($tooth->getImageFileName('implant'),
					$dst_xy[0], $dst_xy[1]);
				break;
			case (self::isVisibleImage('cheektooth', $ToothTypeList)):
				// Корень
				self::mergeImageState($tooth->getImageFileName('cheektooth'),
					$dst_xy[0], $dst_xy[1]);
				break;
			case (self::isVisibleImage('nothing', $ToothTypeList)):
				// Отсутствующий зуб (удален или выпал)
				self::mergeImageState($tooth->getImageFileName('nothing'),
					$dst_xy[0], $dst_xy[1]);
				break;
		}
		// рисуем состояния зуба
		if (self::isVisibleImage('crown', $ToothTypeList)) {
			// Коронка
			self::mergeImageState($tooth->getImageFileName('crown'),
				$dst_xy[0], $dst_xy[1]);
		}
		$ToothStateList = $tooth->getStateClassSysNickList();
		if (self::isVisibleImage('firstdegree|seconddegree|thirddegree', $ToothStateList)) {
			// Подвижность I - III степени
			self::mergeImageState($tooth->getImageFileName('firstdegree|seconddegree|thirddegree'),
				$dst_xy[0], $dst_xy[1]);
		}
		if (self::isVisibleImage('periodontitis', $ToothStateList)) {
			// Периодонтит
			self::mergeImageState($tooth->getImageFileName('periodontitis'),
				$dst_xy[0], $dst_xy[1]);
		}
		if (self::isVisibleImage('pulpitis', $ToothStateList)) {
			// Пульпит
			self::mergeImageState($tooth->getImageFileName('pulpitis'),
				$dst_xy[0], $dst_xy[1]);
		}
		if (self::isVisibleImage('alveolysis', $ToothStateList)) {
			// Пародонтоз
			self::mergeImageState($tooth->getImageFileName('alveolysis'),
				$dst_xy[0], $dst_xy[1]);
		}

		// рисуем поверхности
		$segments = array('1','2','3','4');
		if ($tooth->isHasFiveSegments()) {
			$segments[] = '5';
		}
		foreach ($segments as $segment) {
			$surface = $tooth->getSurface(null, $segment);
			if ($surface) {
				$stateList = $surface->getStateClassSysNickList();
				if (self::isVisibleImage('caries+seal', $stateList)) {
					// Пломбированный + Кариес
					self::mergeImageState($surface->getImageFileName('caries+seal'),
						$dst_xy[0], $dst_xy[1]);
				} else if (self::isVisibleImage('seal', $stateList)) {
					// Пломбированный
					self::mergeImageState($surface->getImageFileName('seal'),
						$dst_xy[0], $dst_xy[1]);
				} else if (self::isVisibleImage('caries', $stateList)) {
					// Кариес
					self::mergeImageState($surface->getImageFileName('caries'),
						$dst_xy[0], $dst_xy[1]);
				}
			}
		}

		// рисуем номер зуба встроенным шрифтом
		$x = (int) ($dst_xy[0] + (self::STATE_IMAGE_WIDTH/2) - 5);
		if ($tooth->getJawPart()->isTop()) {
			$y = self::BASE_IMAGE_CENTER_Y
				- self::BASE_IMAGE_BORDER_SEP_WIDTH
				- self::TOOTH_NUM_MARGIN_Y
				- 12;
		} else {
			$y = self::BASE_IMAGE_CENTER_Y
				+ self::BASE_IMAGE_BORDER_SEP_WIDTH
				+ self::TOOTH_NUM_MARGIN_Y;
		}
		imagestring(self::$_im, 2, $x, $y, $tooth->getCode(), self::getTextColor());

		// рисуем вертикальную строку с кодами состояний
		//$ToothStateClassCodeList = array('1', 'R', 'Pt', 'П3');
		if ($tooth->getJawPart()->isTop()) {
			$y = $dst_xy[1] + 4;
			self::$maxt = min(self::$maxt, $y);
		} else {
			$y = $dst_xy[1] + self::STATE_IMAGE_HEIGHT + 4;
			self::$maxb = max(self::$maxb, $y);
		}
		foreach ($ToothStateClassCodeList as $text) {
			$text = toUTF($text);
			$text = html_entity_decode($text);
			$size = 8;
			if ($tooth->getJawPart()->isTop()) {
				$y = $y - self::TOOTH_STATES_INTERVAL_Y;
			} else {
				$y = $y + self::TOOTH_STATES_INTERVAL_Y;
			}
			imagettftext (self::$_im, $size, 0, $x, $y,
				self::getTextColor(), self::getFont(), $text);
			if ($tooth->getJawPart()->isTop()) {
				$y = $y - $size;
				self::$maxt = min(self::$maxt, $y);
			} else {
				$y = $y + $size;
				self::$maxb = max(self::$maxb, $y);
			}
		}
	}

	/**
	 * Обработка для шаблона или изображения
	 * @param array $toothStates
	 * @param array $defaultTooth
	 * @param array $output
	 * @param bool $forImage
	 * @return array
	 * @throws Exception
	 */
	static function processingToothStates($toothStates, $defaultTooth, $output, $forImage = false)
	{
		$jawParts = array();
		foreach ($defaultTooth as $row) {
			$jaw = $row['JawPartType_Code'].'';
			$tooth = $row['Tooth_Num'].'';
			if (empty($jawParts[$jaw])) {
				$jawParts[$jaw] = array();
				if (!$forImage) {
					$jawParts[$jaw]['history_date'] = $output['history_date'];
					$jawParts[$jaw]['Person_id'] = $output['Person_id'];
					$jawParts[$jaw]['JawPartType_Code'] = $jaw;
				}
				$jawParts[$jaw]['ToothList'] = array();
			}
			if (empty($jawParts[$jaw]['ToothList'][$tooth])) {
				$jawParts[$jaw]['ToothList'][$tooth] = array();
				//$jawParts[$jaw]['ToothList'][$tooth]['debug'] = 'fromDefaultTooth';
				$jawParts[$jaw]['ToothList'][$tooth]['Tooth_Code'] = $row['Tooth_Code'];
				$jawParts[$jaw]['ToothList'][$tooth]['states'] = array();
				if (!$forImage) {
					$jawParts[$jaw]['ToothList'][$tooth]['Tooth_SysNum'] = $tooth;
					$jawParts[$jaw]['ToothList'][$tooth]['PersonToothCard_IsSuperSet'] = NULL;
					$jawParts[$jaw]['ToothList'][$tooth]['ToothPositionType_aid'] = NULL;
					$jawParts[$jaw]['ToothList'][$tooth]['ToothPositionType_bid'] = NULL;
				}
			}
		}
		foreach ($toothStates as $row) {
			if ($row['PersonToothCard_IsSuperSet'] == 2) {
				continue;
			}
			$jaw = $row['JawPartType_Code'].'';
			$tooth = $row['Tooth_Num'].'';
			if (empty($jawParts[$jaw]['ToothList'][$tooth])) {
				$jawParts[$jaw]['ToothList'][$tooth] = array();
				$jawParts[$jaw]['ToothList'][$tooth]['states'] = array();
				if (!$forImage) {
					$jawParts[$jaw]['ToothList'][$tooth]['Tooth_SysNum'] = $tooth;
				}
			}
			//$jawParts[$jaw]['ToothList'][$tooth]['debug'] = 'fromToothStates';
			$jawParts[$jaw]['ToothList'][$tooth]['Tooth_Code'] = $row['Tooth_Code'];
			if (!$forImage) {
				$jawParts[$jaw]['ToothList'][$tooth]['PersonToothCard_IsSuperSet'] = $row['PersonToothCard_IsSuperSet'];
				$jawParts[$jaw]['ToothList'][$tooth]['ToothPositionType_aid'] = $row['ToothPositionType_aid'];
				$jawParts[$jaw]['ToothList'][$tooth]['ToothPositionType_bid'] = $row['ToothPositionType_bid'];
			}
			$jawParts[$jaw]['ToothList'][$tooth]['states'][] = array(
				'ToothSurfaceType_id' => $row['ToothSurfaceType_id'],
				'ToothSurfaceType_Code' => $row['ToothSurfaceType_id'],
				'ToothStateClass_SysNick' => $row['ToothStateClass_SysNick'],
				'ToothStateClass_Code' => $row['ToothStateClass_Code'],
			);
		}
		//exit(var_export($jawParts['4'], true));
		foreach ($jawParts as $code => $jawPart) {
			$num = $code + 0;
			if (in_array($num, array(1,4))) {
				$jawParts[$code]['ToothList'] = array_reverse($jawPart['ToothList']);
			}
		}
		return $jawParts;
	}

	/**
	 * Возвращает имя изображения
	 * @param string $layerName
	 * @param string $position
	 * @param string $segment
	 * @return string
	 */
	static function getImageName($layerName, $position = '', $segment = '')
	{
		switch ($layerName) {
			case 'hover_segments':
				// Ховер на все сегменты зуба
				$imageName = "tooth{$position}hover";
				break;
			case 'hover_segment':
				// Ховер сегмента зуба
				$imageName = "tooth{$position}hover" . $segment;
				break;
			case 'caries+seal':
				// Пломбированный + Кариес
				$imageName = "tooth{$position}cs" . $segment;
				break;
			case 'seal':
				// Пломбированный
				$imageName = "tooth{$position}s" . $segment;
				break;
			case 'caries':
				// Кариес
				$imageName = "tooth{$position}c" . $segment;
				break;
			case 'alveolysis':
				// Пародонтоз
				$imageName = "tooth{$position}a";
				break;
			case 'pulpitis':
				// Пульпит
				$imageName = "tooth{$position}p";
				break;
			case 'periodontitis':
				// Периодонтит
				$imageName = "tooth{$position}pt";
				break;
			case 'firstdegree|seconddegree|thirddegree':
				// Подвижность I - III степени
				$imageName = "tooth{$position}m";
				break;
			case 'const|milk':
				// Зуб без отклонений
				$imageName = "tooth{$position}";
				break;
			case 'implant+crown':
				// Искусственный зуб + коронка
				$imageName = "tooth{$position}4k";
				break;
			case ('implant'):
				// Искусственный зуб
				$imageName = "tooth{$position}4";
				break;
			case 'crown':
				// Коронка
				$imageName = "tooth{$position}k";
				break;
			case 'cheektooth':
				// Корень
				$imageName = "tooth{$position}r";
				break;
			case 'nothing':
				// Отсутсвующий зуб (удален или выпал)
				$imageName = "tooth{$position}none";
				break;
			case 'hover_full':
				// Ховер на весь зуб
				$imageName = in_array($position, array(1,2)) ? "hover12" : "hover34";
				break;
			default:
				// Невыросший зуб
				$imageName = "tooth{$position}null2";
				break;
		}
		return $imageName;
	}

	/**
	 * Должно ли быть видимо изображение
	 * @param string $layerName
	 * @param array $state
	 * @return boolean
	 */
	static function isVisibleImage($layerName, $state = array())
	{
		switch ($layerName) {
			case 'caries+seal':
				$result = (in_array('caries', $state) && in_array('seal', $state));
				break;
			case 'seal':
				$result = (!in_array('caries', $state) && in_array('seal', $state));
				break;
			case 'caries':
				$result = (in_array('caries', $state) && !in_array('seal', $state));
				break;
			case 'firstdegree|seconddegree|thirddegree':
				$result = (in_array('firstdegree', $state) ||
					in_array('seconddegree', $state) ||
					in_array('thirddegree', $state)
				);
				break;
			case 'const|milk':
				$result = (!in_array('cheektooth', $state) &&
					(in_array('const', $state) || in_array('milk', $state))
				);
				break;
			case 'implant+crown':
				$result = (!in_array('cheektooth', $state) &&
					in_array('crown', $state) && in_array('implant', $state));
				break;
			case ('implant'):
				$result = (!in_array('cheektooth', $state) &&
					!in_array('crown', $state) && in_array('implant', $state));
				break;
			case 'crown':
				$result = (!in_array('cheektooth', $state) &&
					in_array('crown', $state) && !in_array('implant', $state));
				break;
			case 'germ':
				$result = empty($state);
				break;
			default:
				$result = in_array($layerName, $state);
				break;
		}
		return $result;
	}

	/**
	 * Возвращает данные слоев
	 * @param SwTooth $tooth
	 * @param array $ToothStateClassCodeList
	 * @return array
	 */
	static function getLayers($tooth, $ToothStateClassCodeList)
	{
		$ToothStateList = $tooth->getStateClassSysNickList();
		$ToothTypeList = $tooth->getClassSysNickList();
		$layers = array();
		// 0 Невыросший зуб
		$layers[] = self::getLayerData('germ', $tooth, $ToothTypeList);
		// 1 Ховер на весь зуб
		if (!self::$isForPrint) {
			$layers[] = self::getLayerData('hover_full', $tooth);
		}
		// 2 Отсутсвующий зуб (удален или выпал)
		$layers[] = self::getLayerData('nothing', $tooth, $ToothTypeList);
		// 3 Корень
		$layers[] = self::getLayerData('cheektooth', $tooth, $ToothTypeList);
		// 4 Искусственный зуб
		$layers[] = self::getLayerData('implant', $tooth, $ToothTypeList);
		// 6 Искусственный зуб + коронка
		$layers[] = self::getLayerData('implant+crown', $tooth, $ToothTypeList);
		// 7 Зуб без отклонений
		$layers[] = self::getLayerData('const|milk', $tooth, $ToothTypeList);
		// 5 Коронка
		$layers[] = self::getLayerData('crown', $tooth, $ToothTypeList);
		// 8 Подвижность I - III степени
		$layers[] = self::getLayerData('firstdegree|seconddegree|thirddegree',
			$tooth, $ToothStateList);
		// 9 Периодонтит
		$layers[] = self::getLayerData('periodontitis', $tooth, $ToothStateList);
		// 10 Пульпит
		$layers[] = self::getLayerData('pulpitis', $tooth, $ToothStateList);
		// 11 Пародонтоз
		$layers[] = self::getLayerData('alveolysis', $tooth, $ToothStateList);

		$segments = array('1','2','3','4');
		if ($tooth->isHasFiveSegments()) {
			$segments[] = '5';
		}
		$surfaces = array();
		foreach ($segments as $segment) {
			$surfaces[$segment] = array();
			$surface = $tooth->getSurface(null, $segment);
			if ($surface) {
				$surfaces[$segment] = $surface->getStateClassSysNickList();
			}
		}
		// Пломбированный + Кариес
		foreach ($surfaces as $segment => $stateList) {
			$layers[] = self::getLayerData('caries+seal', $tooth, $stateList, $segment);
		}
		// Пломбированный
		foreach ($surfaces as $segment => $stateList) {
			$layers[] = self::getLayerData('seal', $tooth, $stateList, $segment);
		}
		// Кариес
		foreach ($surfaces as $segment => $stateList) {
			$layers[] = self::getLayerData('caries', $tooth, $stateList, $segment);
		}
		// Ховер сегмента зуба
		if (!self::$isForPrint) {
			foreach ($surfaces as $segment => $stateList) {
				$layers[] = self::getLayerData('hover_segment', $tooth, $stateList, $segment);
			}
			// Ховер на все сегменты зуба
			//$layers[] = self::getLayerData('hover_segments', $tooth);
		}
		return $layers;
	}

	/**
	 * Возвращает данные для слоя по его имени
	 * @param string $name Имя слоя
	 * @param SwTooth $tooth
	 * @param array $state
	 * @param string $segment
	 * @return array
	 */
	static private function getLayerData($name, $tooth, $state = array(), $segment = null)
	{
		$layerData = array(
			'{folder}' => (self::$isForPrint ? 'toothmap_print' : 'toothmap'),
			'{className}' => $name,
			'{Image_Name}' => self::getImageName($name, $tooth->getPosition(), $segment),
			'{Tooth_Code}' => $tooth->getCode(),
			'{display}' => self::isVisibleImage($name, $state) ? 'block' : 'none',
			'{attr}'=>'',
			'{z-index}'=>'0'
		);//
		switch ($name) {
			case 'hover_full':
				// Ховер на все
				$layerData['{className}'] = 'hover hoverFull';
				break;
			case 'hover_segments':
				// Ховер на все сегменты зуба
				$layerData['{className}'] = 'hover hoverSegments';
				break;
			case 'hover_segment':
				$layerData['{className}'] = 'hover hoverSegment segment' . $segment;
				// Ховер сегмента зуба
				//$layerData['{attr}'] = 'onmouseover=""';
				//$layerData['{z-index}']="4";
				break;
			case 'caries+seal':
				// Пломбированный + Кариес
				$layerData['{className}'] = 'cariesSeal';
				break;
			case 'firstdegree|seconddegree|thirddegree':
				// Подвижность I - III степени
				$layerData['{className}'] = 'mobility';
				break;
			case 'const|milk':
				// Зуб без отклонений
				$layerData['{className}'] = 'milkOrConst';
				break;
			case 'implant+crown':
				// Искусственный зуб + коронка
				$layerData['{className}'] = 'implantAndCrown';
				break;
			case 'cheektooth':
				// Корень
				$layerData['{className}'] = 'radixOnly';
				break;
		}
		return $layerData;
	}

	/**
	 * @return string
	 */
	static function getBasePath() {
		return $_SERVER['DOCUMENT_ROOT'] . '/img/toothmap_print';
	}

	/**
	 * @return string
	 */
	static function getBaseFileName() {
		return self::getBasePath() . '/base.png';
	}

	/**
	 * @return string
	 */
	static function getFont() {
		return BASEPATH . 'fonts/arial.ttf';
	}

	/**
	 * Выводим готовое PNG изображение в браузер
	 * @throws Exception
	 */
	static function output() {
		if (!self::$_im) {
			throw new Exception('Не удалось открыть изображение');
		}
		self::finish();
	}

	/**
	 * Определяем цвет текста
	 * @return int
	 */
	static private function getTextColor() {
		return imagecolorallocate(self::$_im, 0, 0, 0);
	}

	/**
	 * Выводим в браузер изображение c текстом ошибки
	 * @param $text Строка в кодировке win-1251
	 */
	static function outputError($text) {
		if (self::$_im) {
			imagedestroy(self::$_im);
		}
		$width = self::BASE_IMAGE_WIDTH;
		$height = self::BASE_IMAGE_HEIGHT;
		self::$_im = @imagecreatetruecolor($width, $height)
			or die('Невозможно инициализировать GD поток');
		$white = imagecolorallocate(self::$_im, 255, 255, 255);
		imagefill(self::$_im, 0, 0, $white);  // белый фон
		//imagefilledrectangle($im, 0, 0, $width-1, $height-1, $white);

		$x = 15;
		$y = self::BASE_IMAGE_CENTER_Y;

		// встроенный шрифт не понимает кирриллицу
		//$size = 1;
		//imagestring($im, $size, $x, $y, $text, $black);

		$size = 10;
		$text = toUTF($text);
		$text = html_entity_decode($text);
		imagettftext(self::$_im , $size , 0, $x , $y ,
			self::getTextColor(), self::getFont(), $text);
		self::finish();
	}

	/**
	 * finish
	 */
	static private function finish() {
		header ('Content-Type: image/png');
		//header('Content-Type: image/gif');
		header('Pragma: no-cache');
		imagepng(self::$_im);
		//imagegif(self::$_im);
		imagedestroy(self::$_im);
		self::$_im = null;
	}
}