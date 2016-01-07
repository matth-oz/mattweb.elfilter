<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("фильтр");
?>
<?
// компонент фильтр
$APPLICATION->IncludeComponent("mattweb:elfilter", ".default", Array(
    "IBLOCK_TYPE" => "books",    // Тип информационного блока (используется только для проверки)
    "IBLOCK_ID" => "6",  // Код информационного блока
    "ACTION_URL" => " /filtered.php",
    "USE_SECTION_FILTER" => "Y", // Фильтровать по разделам	
    "ARR_PROPERTIES" => Array(
        "AUTHORS" => Array(
            "PROP_NAME" => "Автор(ы)",
            "PROP_ID" => "AUTHORS",
            "PROP_TYPE" => "E",
            "LNKD_IBLOCK_ID" => "5",         
            "FLTFIELD_TYPE" => "ISM"
        ),
        "PUBLISHER" => Array(
            "PROP_NAME" => "Издатель",
            "PROP_ID" => "PUBLISHER",
            "PROP_TYPE" => "S",          
            "FLTFIELD_TYPE" => "IS"         
        ),
        "BK_PRICE" => Array(
            "PROP_NAME" => "Цена",
            "PROP_ID" => "BK_PRICE",
            "PROP_TYPE" => "N",          
            "FLTFIELD_TYPE" => "ISL"
        ),
        "PAGES" => Array(
            "PROP_NAME" => "Число страниц",
            "PROP_ID" => "PAGES",
            "PROP_TYPE" => "N",
            "FLTFIELD_TYPE" => "ISR",
            "RNG_PARTS" => "5"
        ),
        "RENT_SELL" => Array(
            "PROP_NAME" => "Прокат/Продажа",
            "PROP_ID" => "RENT_SELL",
            "PROP_TYPE" => "L",
            "FLTFIELD_TYPE" => "IR"
        ),
        "IMAGES_SECTION" => Array(
            "PROP_NAME" => "Иллюстрации",
            "PROP_ID" => "IMAGES_SECTION",
            "PROP_TYPE" => "G",
            "FLTFIELD_TYPE" => "ISM"
        ),
    ),
    ),
  false
); 
 
if(isset($_GET['use_filter'])){
	$arrElementsFilter =  $USER->GetParam('elfilter');
		
	// компонент со списком результатов с фильтром
	$APPLICATION->IncludeComponent("bitrix:news.list", "filtered", array(
		"IBLOCK_TYPE" => "books",
		"IBLOCK_ID" => "6",
		"NEWS_COUNT" => "5",
		"SORT_BY1" => "ACTIVE_FROM",
		"SORT_ORDER1" => "DESC",
		"SORT_BY2" => "SORT",
		"SORT_ORDER2" => "ASC",
		"FILTER_NAME" => "arrElementsFilter",
		"FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"PROPERTY_CODE" => array(
			0 => "PAGES",
			1 => "PUBLISHER",
			2 => "RENT_SELL",
			3 => "BK_PRICE",
			4 => "IMAGES_SECTION",
			5 => "AUTHORS",
			6 => "",
		),
		"CHECK_DATES" => "Y",
		"DETAIL_URL" => "",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "36000000",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"PREVIEW_TRUNCATE_LEN" => "",
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"SET_TITLE" => "Y",
		"SET_STATUS_404" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"PARENT_SECTION" => "",
		"PARENT_SECTION_CODE" => "",
		"INCLUDE_SUBSECTIONS" => "Y",
		"PAGER_TEMPLATE" => ".default",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_BASE_LINK_ENABLE" => "Y",
		"PAGER_BASE_LINK" => $APPLICATION->GetCurPage()."?use_filter=y", 	
		"PAGER_TITLE" => "Элементы",
		"PAGER_SHOW_ALWAYS" => "Y",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "Y",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"AJAX_OPTION_ADDITIONAL" => ""
		),
		false
	);
}
else{
	$USER->SetParam('elfilter', '');	
	// компонент со списком результатов без фильтра
	$APPLICATION->IncludeComponent("bitrix:news.list", "filtered", array(
		"IBLOCK_TYPE" => "books",
		"IBLOCK_ID" => "6",
		"NEWS_COUNT" => "5",
		"SORT_BY1" => "ACTIVE_FROM",
		"SORT_ORDER1" => "DESC",
		"SORT_BY2" => "SORT",
		"SORT_ORDER2" => "ASC",
		"FILTER_NAME" => "",
		"FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"PROPERTY_CODE" => array(
			0 => "PAGES",
			1 => "PUBLISHER",
			2 => "RENT_SELL",
			3 => "BK_PRICE",
			4 => "IMAGES_SECTION",
			5 => "AUTHORS",
			6 => "",
		),
		"CHECK_DATES" => "Y",
		"DETAIL_URL" => "",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "36000000",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"PREVIEW_TRUNCATE_LEN" => "",
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"SET_TITLE" => "Y",
		"SET_STATUS_404" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"PARENT_SECTION" => "",
		"PARENT_SECTION_CODE" => "",
		"INCLUDE_SUBSECTIONS" => "Y",
		"PAGER_TEMPLATE" => ".default",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",		 	
		"PAGER_TITLE" => "Элементы",
		"PAGER_SHOW_ALWAYS" => "Y",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "Y",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"AJAX_OPTION_ADDITIONAL" => ""
		),
		false
	);
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>