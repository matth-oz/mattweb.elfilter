<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/**
	Параметры:
	IBLOCK_TYPE - тип инфоблока 
	IBLOCK_ID - ИД инфоблока
	ACTION_URL - Адрес страницы с компонентом для фильтрации
	USE_SECTION_FILTER - Фильровать по секциям
	ARR_PROPERTIES - массив свойств для фильтра
		PROP_NAME - Название свойства
		PROP_ID	- ИД свойства		
		PROP_TYPE - тип свойства (S - строка, N - число, F - файл, L - список, E - привязка к элементам, G - привязка к группам)
		LNKD_IBLOCK_ID  - ИД инфоблока со связанными элементами (если PROP_TYPE == E)
		FLTFIELD_TYPE - тип поля в форме-фильтре (IT - text; IS - select; ISM - select multiply; ISL - slider; ISR - ranges; ICH - checkbox; IR - radiobutton;) 
*/

if(!CModule::IncludeModule('iblock')){
	showError("Не установлен модуль Инфоблоки");
}

global $USER;

$arParams["IBLOCK_ID"] = intVal($arParams["IBLOCK_ID"]);

/* 
	если компонент выводящий результаты 
	фильтра находится на той же странице - ACTION_URL не указываем 
*/
if(empty($arParams["ACTION_URL"])){
	$arParams["ACTION_URL"] = $APPLICATION->GetCurUri()."?use_filter=y";
}
else{
	$arParams["ACTION_URL"] = trim($arParams["ACTION_URL"])."?use_filter=y";	
}

/*для комплексного компонента учитываем номер секции*/
$ccSectionID = intVal($arParams['SECTION_ID']);


$arResult = Array();
$arResult["FIELDS"] = Array();

// если параметры для фильтра получены или получен параметр для очистки фильтра
// удаляем старые данные
if($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST["filter_send"]) || isset($_POST["filter_reset"]))){
	$USER->SetParam('elfilter', '');
	$USER->SetParam('curFilterParams', '');
}
// если получены только параметры для фильтра
// сохраняем новые параметры
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["filter_send"])){
	$arrElementsFilter = Array();
	
	foreach($_POST as $key=>$val){
		if($key == "SECTIONS" && !in_array("-", $_POST["SECTIONS"])){
			// фильтр по секциям (массив)
            $arrElementsFilter["SECTION_ID"] = $_POST["SECTIONS"];
		}
		else if((strpos($key, "PROPERTY_") !== false && !is_array($val) && $val != "-" && $val != "") || (strpos($key, "PROPERTY_") !== false && is_array($val) && !in_array("-", $val))){
			// фильтр по свойствам
			$filterPropName = substr_replace($key, '', 0, strlen("PROPERTY_"));
			if(array_key_exists($filterPropName, $arParams["ARR_PROPERTIES"])){
				// получаем тип поля фильтра
				$filterFieldType = $arParams["ARR_PROPERTIES"][$filterPropName]["FLTFIELD_TYPE"];
				
				switch ($filterFieldType){
					case "IT":
						$arrElementsFilter["$key"] = "%".$val."%";
					break;
					case "IS":
						$arrElementsFilter["$key"] = $val;						
					break;
					case "ISM":
						if(is_array($val) && count($val) > 0){
							$arrElementsFilter[] = Array(
								"LOGIC" => "OR",                 
								array("$key" => $val)
							);
						}
					break;
					case "ISL":
						$rngpattern = "/^[0-9]+\-[0-9]+$/";
						if(preg_match($rngpattern, $val)){
							$arrVals = explode("-",$val);
							$arrElementsFilter["><$key"] = $arrVals;
						}
					break;
					case "ISR":
						$rngpattern = "/^[0-9]+\-[0-9]+$/";
						if(preg_match($rngpattern, $val)){
							$arrVals = explode("-",$val);
							$arrElementsFilter["><$key"] = $arrVals;
						}
					break;
					case "ICH":
						if(is_array($val) && count($val) > 0){
							$arrElementsFilter[] = Array(
								"LOGIC" => "OR",                 
								array("$key" => $val)
							);
						}
					break;
					case "IR":
						$arrElementsFilter["$key"] = $val;
					break;
				}
				
			}
		}		
	}
	
	// массив для передачи в компонент новостей
	$USER->SetParam('elfilter', $arrElementsFilter);
	// массив текущих значений фильтра для отображения в форме
	$USER->SetParam('curFilterParams', $_POST);
}

if(isset($_GET["use_filter"]) && $_GET["use_filter"] == "y"){
	$arrFilterCurParams = $USER->GetParam('curFilterParams');
}

// URL для отправки формы
$arResult["ACTION_URL"] = $arParams["ACTION_URL"];

// фильтр по секциям
if(isset($arParams["USE_SECTION_FILTER"]) && $arParams["USE_SECTION_FILTER"] == "Y"){	
	$arSectFilter = Array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"GLOBAL_ACTIVE" => "Y"
	);
	// Если у инфоблока есть секции
	if(CIBlockSection::GetCount($arSectFilter) > 0){
		$arrSections = Array();
		$dbSectList = CIBlockSection::GetList(Array("SORT" => "ASC"), $arSectFilter, false, Array("ID","NAME"));
		
		// получаем список секций инфоблока (массив "ИД_РАЗДЕЛА" => "Название раздела")
		while($tmpSectArr = $dbSectList->Fetch()){
			$arrSections[$tmpSectArr["ID"]] = $tmpSectArr["NAME"];
		}
		
		// Формируем HTML на основе данных и добавляем его в $arResult["FIELDS"]
		$fld_html = '<select name="SECTIONS[]" multiple = "multiple" size="5">';
		$fld_html .= '<option value="-">-</option>';
		foreach($arrSections as $key => $value){
			if(array_key_exists("SECTIONS", $arrFilterCurParams) && in_array($key, $arrFilterCurParams["SECTIONS"]))
			{
				$fld_html .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
			}
			else{
				$fld_html .= '<option value="'.$key.'">'.$value.'</option>';
			}			
		}
		$fld_html .= '</select>';
		
		$arResult["FIELDS"][] = Array(
			"FLDNAME" => "Разделы",
			"FIELDTYPE" => "ISM",
			"VALUE" => $arrSections,
			"FLD_HTML" => $fld_html
		);		
	}
}

if(count($arParams["ARR_PROPERTIES"]) > 0)
{	
	foreach($arParams["ARR_PROPERTIES"] as $arProp){		
		if($arProp["PROP_TYPE"] == "E" && !empty($arProp["LNKD_IBLOCK_ID"]))
		{
			// привязка к элементам
			//get unique property values
			$arpropvals = Array();
			
			$propcd = "PROPERTY_".strtoupper($arProp["PROP_ID"]);
			$arSelect = Array("ID", $propcd);
			
			$arFilter = Array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],				
				"INCLUDE_SUBSECTION" => "Y"
			);
		
			if(!empty($ccSectionID)) $arFilter["SECTION_ID"] = $ccSectionID;
			
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($ob = $res->GetNextElement()){
				$arFlds = $ob->GetFields();
				if(!in_array($arFlds[$propcd."_VALUE"], $arpropvals)){
					$arpropvals[] = $arFlds[$propcd."_VALUE"];				
				}				
			}
		
			$arSelect = Array("ID","NAME");
			// Add unique property values into the filter
			$arFilter = Array(
				"IBLOCK_ID" => $arProp["LNKD_IBLOCK_ID"],
				"INCLUDE_SUBSECTION" => "Y",
				"ID" => $arpropvals,
			);
			
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			$lellst = Array();
			while($ob = $res->GetNextElement())
			{
			  $arFields = $ob->GetFields();				  
			  $lellst[$arFields["ID"]] = $arFields["NAME"];			  
			}
			
			if(count($lellst) > 0)
			{
				asort($lellst);
				$fld_html = '<select name="PROPERTY_'.$arProp["PROP_ID"];
				if($arProp["FLTFIELD_TYPE"] == "ISM") $fld_html .= '[]" multiple = "multiple" size="5">';
				if($arProp["FLTFIELD_TYPE"] == "IS"){
					$fld_html .= '">';					
				} 
				$fld_html .= '<option value="-">-</option>';
				foreach($lellst as $key => $value){
					if(array_key_exists("PROPERTY_".$arProp["PROP_ID"], $arrFilterCurParams) && in_array($key, $arrFilterCurParams["PROPERTY_".$arProp["PROP_ID"]]))
					{
						$fld_html .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
					}
					else
					{
						$fld_html .= '<option value="'.$key.'">'.$value.'</option>';
					}
					
				}
				$fld_html .= '</select>';
								
				$arResult["FIELDS"][] = Array(
					"FLDNAME" => $arProp["PROP_NAME"],
					"PROPTYPE" => $arProp["PROP_TYPE"],
					"FIELDTYPE" => $arProp["FLTFIELD_TYPE"],
					"VALUE" => $lellst,
					"FLD_HTML" => $fld_html
				);	
			}
			else
			{
				$arResult["ERRORS"][] = "У свойства ".$arProp["PROP_ID"]." нет значений для фильтра";
			}	
		}
		elseif($arProp["PROP_TYPE"] == "S")
		{
			//строка
			
			$arpropvals = Array();
			
			$propcd = "PROPERTY_".strtoupper($arProp["PROP_ID"]);
			$arSelect = Array("ID", $propcd);
			
			$arFilter = Array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],								
				"INCLUDE_SUBSECTION" => "Y"
			);
			
			if(!empty($ccSectionID)) $arFilter["SECTION_ID"] = $ccSectionID;
			
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($ob = $res->GetNextElement()){
				$arFlds = $ob->GetFields();
				if(!in_array($arFlds[$propcd."_VALUE"], $arpropvals)){
					$arpropvals[$arFlds[$propcd."_VALUE_ID"]] = $arFlds[$propcd."_VALUE"];				
				}				
			}
			if(count($arpropvals) > 0)
			{
				asort($arpropvals);
				if($arProp["FLTFIELD_TYPE"] == "IS" || $arProp["FLTFIELD_TYPE"] == "ISM"){
					//select
					$fld_html = '<select name="PROPERTY_'.$arProp["PROP_ID"];
					if($arProp["FLTFIELD_TYPE"] == "ISM") $fld_html .= '[]" multiple = "multiple" size="5">';
					if($arProp["FLTFIELD_TYPE"] == "IS") {
						$fld_html .= '">';						
					}
					$fld_html .= '<option value="-">-</option>';
					foreach($arpropvals as $key => $value){
						if(empty($key) || empty($value)) continue;
						if(array_key_exists("PROPERTY_".$arProp["PROP_ID"], $arrFilterCurParams) && $arrFilterCurParams["PROPERTY_".$arProp["PROP_ID"]] == $value)
						{
							$fld_html .= '<option value="'.$value.'" selected="selected">'.$value.'</option>';
						}
						else
						{
							$fld_html .= '<option value="'.$value.'">'.$value.'</option>';
						}						
					}
					$fld_html .= '</select>';
				}
				else{
					// input type="text"
					$fld_html = '<input type="text" name="PROPERTY_'.$arProp["PROP_ID"].'" id="fld_'.$arProp["PROP_ID"].'"/>';
				}			
								
				$arResult["FIELDS"][] = Array(
					"FLDNAME" => $arProp["PROP_NAME"],
					"PROPTYPE" => $arProp["PROP_TYPE"],
					"FIELDTYPE" => $arProp["FLTFIELD_TYPE"],
					"VALUE" => $arpropvals,
					"FLD_HTML" => $fld_html
				);
			}
			else
			{
				$arResult["ERRORS"][] = "У свойства ".$arProp["PROP_ID"]." нет значений для фильтра";
			}			
		}
		elseif($arProp["PROP_TYPE"] == "N"){
			// число
			
			$arpropvals = Array();
			
			$propcd = "PROPERTY_".strtoupper($arProp["PROP_ID"]);
			$arSelect = Array("ID", $propcd);
			
			$arFilter = Array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"INCLUDE_SUBSECTION" => "Y"
			);
			
			if(!empty($ccSectionID)) $arFilter["SECTION_ID"] = $ccSectionID;
			
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($ob = $res->GetNextElement()){
				$arFlds = $ob->GetFields();
				if(!in_array($arFlds[$propcd."_VALUE"], $arpropvals)){
					$arpropvals[$arFlds[$propcd."_VALUE_ID"]] = $arFlds[$propcd."_VALUE"];				
				}				
			}
			
			if(count($arpropvals) > 0){			
			
				// remove repeated values from array and sort result 
				$arpropvals = array_unique($arpropvals);			
				sort($arpropvals);
				$lastel_key = count($arpropvals)-1;
				
				if($arProp["FLTFIELD_TYPE"] == "ISR" && !empty($arProp["RNG_PARTS"])){
					//select - ranges					
					$range_parts = intVal($arProp["RNG_PARTS"]);
					
					$minPropVal = $arpropvals[0];
					$maxPropVal = $arpropvals[$lastel_key];
					
					$step = floor($maxPropVal/$range_parts);
					
					//echo $minPropVal."<br />".$maxPropVal."<br />".$step."<br />";
					$fld_html = '<select name="PROPERTY_'.$arProp["PROP_ID"].'">';
					$fld_html .= '<option value="-">-</option>';
					for($i = $minPropVal; $i <= $maxPropVal; $i += $step)
						{
							$ii = ($i == $minPropVal) ? $i : $i + 1;
							$a = (($i+$step) > $maxPropVal) ? $maxPropVal : $i+$step;
							
							if($i < $maxPropVal){								
								
								if(array_key_exists("PROPERTY_".$arProp["PROP_ID"], $arrFilterCurParams) && $arrFilterCurParams["PROPERTY_".$arProp["PROP_ID"]] == $ii."-".$a)
								{
									$fld_html .= '<option value="'.$ii.'-'.$a.'" selected="selected">'.$ii."-".$a.'</option>';
								}
								else
								{
									$fld_html .= '<option value="'.$ii.'-'.$a.'">'.$ii."-".$a.'</option>';
								}								
							}					
							
						}
					$fld_html .= '</select>';
					
				}
				elseif($arProp["FLTFIELD_TYPE"] == "ISL"){
					//slider noUiSlider
					
					$arResult["USE_SLIDER"] = "Y";				
					
					$arSliderSettings = Array();
					
					//range
					$strangeval = $arpropvals[0];
					$finrangeval = $arpropvals[$lastel_key];
					
					// step - шаг в слайдере
					$step = floor($finrangeval / count($arpropvals));
					
					// selected range
					$stselrng = floor($finrangeval/4);
					$finselrng = floor($finrangeval - ($finrangeval/4));
					
					$arSliderSettings["SLDIVID"] = "slider_".$arProp["PROP_ID"];
					$arSliderSettings["STRANGEVAL"] = $strangeval;
					$arSliderSettings["FINRANGEVAL"] = $finrangeval;
					$arSliderSettings["STSELRNGVAL"] = $stselrng;
					$arSliderSettings["FINSELRNGVAL"] = $finselrng;
					$arSliderSettings["STEP"] = $step;
					$arSliderSettings["HANDLES"] = 2;				
					$arSliderSettings["SLINPMINID"] = "filter_min".$arProp["PROP_ID"];
					$arSliderSettings["SLINPMAXID"] = "filter_max".$arProp["PROP_ID"];
					$arSliderSettings["SLINPVAL"] = "filter_".$arProp["PROP_ID"];
					
					$fld_html = '<div id="slider_'.$arProp["PROP_ID"].'"></div>';
					$fld_html .= '<div class="mnmxvals"><input type="text" name="filter_min'.$arProp["PROP_ID"].'" id="filter_min'.$arProp["PROP_ID"].'" />';
					$fld_html .= '<input type="text" name="filter_max'.$arProp["PROP_ID"].'" id="filter_max'.$arProp["PROP_ID"].'" />';
					$fld_html .= '<input type="hidden" name="PROPERTY_'.$arProp["PROP_ID"].'" id="filter_'.$arProp["PROP_ID"].'" value="" /></div>';
					
					// Slider settings
					$arResult["SLIDER"][] = $arSliderSettings;
				}
				else{
					// input type=text
					$fld_html = '<input type="text" name="PROPERTY_'.$arProp["PROP_ID"].'" id="fld_'.$arProp["PROP_ID"].'"/>';
				}			
				
				$arResult["FIELDS"][] = Array(
					"FLDNAME" => $arProp["PROP_NAME"],
					"PROPTYPE" => $arProp["PROP_TYPE"],
					"FIELDTYPE" => $arProp["FLTFIELD_TYPE"],
					"FLD_HTML" => $fld_html
				);
			
			}
			else
			{
				$arResult["ERRORS"][] = "У свойства ".$arProp["PROP_ID"]." нет значений для фильтра";
			}
			
		}
		elseif($arProp["PROP_TYPE"] == "L"){
			
			// список		
			
			$arpropvals = Array();
			
			$arFilter = Array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "CODE" => $arProp["PROP_ID"]);

			$property_enums = CIBlockPropertyEnum::GetList(Array(), $arFilter);
			while($enum_fields = $property_enums -> GetNext())
			{			
				$arpropvals[$enum_fields["ID"]] = $enum_fields["VALUE"];
			}
			
			if(count($arpropvals) > 0)
			{
				asort($arpropvals);
				$fld_html = '';
				if($arProp["FLTFIELD_TYPE"] == "IR"){
					foreach($arpropvals as $key => $value){
						if(array_key_exists("PROPERTY_".$arProp["PROP_ID"], $arrFilterCurParams) && $arrFilterCurParams["PROPERTY_".$arProp["PROP_ID"]] == $key){
							$fld_html .= '<input type="radio" name="PROPERTY_'.$arProp["PROP_ID"].'" value="'.$key.'" checked="checked">&nbsp;'.$value;
						}
						else{
							$fld_html .= '<input type="radio" name="PROPERTY_'.$arProp["PROP_ID"].'" value="'.$key.'">&nbsp;'.$value;
						}
						
					}
				}
				elseif($arProp["FLTFIELD_TYPE"] == "ICH"){
					foreach($arpropvals as $key => $value){
						if(array_key_exists("PROPERTY_".$arProp["PROP_ID"], $arrFilterCurParams) && in_array($key, $arrFilterCurParams["PROPERTY_".$arProp["PROP_ID"]])){
							$fld_html .= '<input type="checkbox" name="PROPERTY_'.$arProp["PROP_ID"].'[]" value="'.$key.'" checked="checked">&nbsp;'.$value;
						}
						else{
							$fld_html .= '<input type="checkbox" name="PROPERTY_'.$arProp["PROP_ID"].'[]" value="'.$key.'">&nbsp;'.$value;
						}
					}
				}
				elseif($arProp["FLTFIELD_TYPE"] == "IS" || $arProp["FLTFIELD_TYPE"] == "ISM"){
					$fld_html = '<select name="PROPERTY_'.$arProp["PROP_ID"];
					
					if($arProp["FLTFIELD_TYPE"] == "ISM") $fld_html .= '[]" multiple = "multiple" size="5">';
					if($arProp["FLTFIELD_TYPE"] == "IS") {
						$fld_html .= '">';						
					}
						$fld_html .= '<option value="-">-</option>';					
						foreach($arpropvals as $key => $value){
							if(array_key_exists("PROPERTY_".$arProp["PROP_ID"], $arrFilterCurParams) && $arrFilterCurParams["PROPERTY_".$arProp["PROP_ID"]] == $key)
							{
								$fld_html .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
							}						
							else
							{
								$fld_html .= '<option value="'.$key.'">'.$value.'</option>';
							}						
						}
					$fld_html .= '</select>';
				}
			}
			else
			{
				$arResult["ERRORS"][] = "У свойства ".$arProp["PROP_ID"]." нет значений для фильтра";
			}
			
			$arResult["FIELDS"][] = Array(
				"FLDNAME" => $arProp["PROP_NAME"],
				"PROPTYPE" => $arProp["PROP_TYPE"],
				"FIELDTYPE" => $arProp["FLTFIELD_TYPE"],
				"VALUE" => $arpropvals,
				"FLD_HTML" => $fld_html
			);
		}
		elseif($arProp["PROP_TYPE"] == "G"){
			// привязка к группам
			$arpropvals = Array();
			
			$propcd = "PROPERTY_".strtoupper($arProp["PROP_ID"]);
			$arSelect = Array("ID", $propcd);
			
			$arFilter = Array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],				
				"INCLUDE_SUBSECTION" => "Y"
			);
			
			if(!empty($ccSectionID)) $arFilter["SECTION_ID"] = $ccSectionID;
			
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($ob = $res->GetNextElement()){
				$arFlds = $ob->GetFields();
				if(!in_array($arFlds[$propcd."_VALUE"], $arpropvals)){
					$arpropvals[] = $arFlds[$propcd."_VALUE"];				
				}				
			}
			
			if(count($arpropvals) > 0){
				asort($arpropvals);
				$arFilter = Array("ID" => $arpropvals);
				$res = CIBlockSection::GetList(Array(), $arFilter, false, Array("ID","NAME"));
				$fld_html = '<select name="PROPERTY_'.$arProp["PROP_ID"];
					if($arProp["FLTFIELD_TYPE"] == "ISM") $fld_html .= '[]" multiple = "multiple" size="5">';
					if($arProp["FLTFIELD_TYPE"] == "IS") {
						$fld_html .= '">';						
					}
					$fld_html .= '<option value="-">-</option>';
					while($sinf = $res-> GetNext()){
						if(array_key_exists("PROPERTY_".$arProp["PROP_ID"], $arrFilterCurParams) && $arrFilterCurParams["PROPERTY_".$arProp["PROP_ID"]] == $sinf["ID"])
						{
							$fld_html .= '<option value="'.$sinf["ID"].'" selected="selected">'.$sinf["NAME"].'</option>';
						}						
						else
						{
							$fld_html .= '<option value="'.$sinf["ID"].'">'.$sinf["NAME"].'</option>';
						}
											
					}
				$fld_html .= '</select>';
				
				$arResult["FIELDS"][] = Array(
					"FLDNAME" => $arProp["PROP_NAME"],
					"PROPTYPE" => $arProp["PROP_TYPE"],
					"FIELDTYPE" => $arProp["FLTFIELD_TYPE"],
					"VALUE" => $arpropvals,
					"FLD_HTML" => $fld_html
				);			
			}
			else
			{
				$arResult["ERRORS"][] = "У свойства ".$arProp["PROP_ID"]." нет значений для фильтра";
			}
			
		}
	}
}
else{
	$arResult["ERRORS"][] = "Компоненту не передано ни одного свойства для фильтрации";
}

$this->IncludeComponentTemplate(); 
?>