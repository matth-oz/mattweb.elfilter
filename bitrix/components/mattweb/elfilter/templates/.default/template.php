<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
/*
	http://refreshless.com/nouislider/ - form filed slider
*/
?>
<?CJSCore::Init(array("jquery"));?>
<script>
	$(function(){
		$('#filter_reset').click(function(){
			var form_act = $(this).parents('form').attr('action');
			form_act = form_act.replace('?use_filter=y','');
			$(this).parents('form').attr('action', form_act);		
		});
	});
</script>
<?if(!empty($arResult["ERRORS"])):?>
	<?foreach($arResult["ERRORS"] as $err_mess):?>
		<?ShowError($err_mess);?><br />
	<?endforeach;?>
<?endif;?>

<?if(isset($arResult["USE_SLIDER"]) && count($arResult["SLIDER"]) > 0):?>
	<?if(isset($_GET["use_filter"])):?>
		<?$curFilterParams = $USER->GetParam('curFilterParams');?>
	<?endif;?>
	<script type="text/javascript">
	$(function(){
	<?foreach($arResult["SLIDER"] as $arSlSett):?>
		$('#<?=$arSlSett["SLDIVID"]?>').noUiSlider({
			 range: [<?=$arSlSett["STRANGEVAL"]?>,<?=$arSlSett["FINRANGEVAL"]?>]
			 <?if(isset($curFilterParams[$arSlSett["SLINPMINID"]]) && isset($curFilterParams[$arSlSett["SLINPMAXID"]])):?>
			 ,start: [<?=$curFilterParams[$arSlSett["SLINPMINID"]]?>,<?=$curFilterParams[$arSlSett["SLINPMAXID"]]?>]
			 <?else:?>
			 ,start: [<?=$arSlSett["STSELRNGVAL"]?>,<?=$arSlSett["FINSELRNGVAL"]?>]
			 <?endif;?>
			,handles: <?=$arSlSett["HANDLES"]?>
			,connect: true
			,step: <?=$arSlSett["STEP"]?>
			,serialization: {
				 to: [ $('#<?=$arSlSett["SLINPMINID"]?>'), $('#<?=$arSlSett["SLINPMAXID"]?>') ]
				,resolution: 1
			},
			slide: function(){
				var rval = $('#<?=$arSlSett["SLINPMINID"]?>').val()+'-'+$('#<?=$arSlSett["SLINPMAXID"]?>').val();				
				$('#<?=$arSlSett["SLINPVAL"]?>').val(rval);
			}
		});	
	<?endforeach;?>
	
	var rval = $('#<?=$arSlSett["SLINPMINID"]?>').val()+'-'+$('#<?=$arSlSett["SLINPMAXID"]?>').val();				
	$('#<?=$arSlSett["SLINPVAL"]?>').val(rval);

	});
	</script>	
<?endif?>

<?$slider = $arResult["FIELDS"][2];?>
<div class="form_wrap">
	<form action="<?=$arResult["ACTION_URL"]?>" method="POST" id="elfilter_form">
		<?foreach($arResult["FIELDS"] as $arField):?>
			<div>
				<p class="flabel"><?=$arField["FLDNAME"]?></p>
				<?=$arField["FLD_HTML"]?>
			</div>
		<?endforeach;?>
		<div class="butts">
			<input type="submit" name="filter_send" id="filter_send" value="Фильтр" />
			<input type="submit" name="filter_reset" id="filter_reset" value="Очистить" />
		</div>	
	</form>
</div>

 