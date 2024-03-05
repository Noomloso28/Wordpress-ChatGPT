<?php
error_reporting(0);
include "header.php";

global $wpdb;
$tablename = $wpdb->prefix.'chatgpt_content_writer';

$sql = "SELECT * FROM $tablename";
$results = $wpdb->get_results($sql);
$getApiToken = $results[0]->api_token;
$getTemperature = $results[0]->temperature;
$getMaxTokens = $results[0]->max_tokens;
$getLanguage = $results[0]->language;
$getKeywords = $results[0]->keywords;
$getQuestions = $results[0]->questions;
$getTemplate = $results[0]->template;
$getProductTemplate = $results[0]->product_template;
$getKeywordsIndex = $results[0]->keywords_index;


$languages = array("tr","en");
if(in_array($getLanguage,$languages)) {
	include "language/".$getLanguage.".php";
} else {
	include "language/en.php";
}

if(isset($_POST["submit"])){
	$temperatureValue = $_POST["temperatureValue"];
	$apiToken = $_POST["apiToken"];
	$maxTokens = $_POST["maxTokens"];
	$selectLanguage = $_POST["selectLanguage"];
    $keywords = $_POST["keywords"];
    $questions = $_POST["questions"];
    $template = $_POST["template"];
    $productTemplate = $_POST["productTemplate"];
    $keywordsIndex = $_POST["keywords_index"];


	if($results){ // UPDATE
		$id = $results[0]->id;
		$wpdb->update( $tablename, array(
			'api_token' => $apiToken,
			'temperature' => $temperatureValue,
			'max_tokens' => $maxTokens,
			'language' => $selectLanguage,
            'keywords' => $keywords,
            'questions' => $questions,
            'template' => $template,
            'product_template' => $productTemplate,
            'keywords_index' => $keywordsIndex
		),
			array(
				'id'=>$id,
			)
		);
		echo "<script>location.reload();</script>";
	}
	if(!$results){ //INSERT
		$wpdb->insert( $tablename, array(
			'api_token' => $apiToken,
			'temperature' => $temperatureValue,
			'max_tokens' => $maxTokens,
			'language' => $selectLanguage,
			'keywords' => $keywords,
			'questions' => $questions,
            'template' => $template,
            'product_template' => $productTemplate,
            'keywords_index' => $keywordsIndex,
		),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
		);
		echo "<script>location.reload();</script>";
	}

}
?>

<form method="post">
	<br>
	<div class="mb-3">
		<label class="form-label">ChatGPT API Token (sk-xxxxx):</label>
		<input type="text" id="apiToken" name="apiToken" class="form-control" value="<?php echo $getApiToken; ?>"/>
	</div>
	<div class="mb-3">
		<label class="form-label"><?php echo $lang["temperature"]; ?><b id="temperatureTextValue"><?php echo $getTemperature; ?></b></label><br>
		<small><?php echo $lang["temperatureText"]; ?></small>
		<input onchange="updateTemperature();" type="range" class="form-range" min="0" max="1" step="0.1" id="temperatureValue" name="temperatureValue" value="<?php echo $getTemperature; ?>">
	</div>
	<div class="mb-3">
		<label class="form-label"><?php echo $lang["maxTokens"]; ?> (Maximum: 3000)</label>
		<input type="number" id="maxTokens" name="maxTokens" class="form-control" value="<?php echo $getMaxTokens; ?>"/>
		<small><?php echo $lang["maxTokensText"]; ?></small>
	</div>
	<div class="mb-3">
		<label class="form-label"><?php echo $lang["selectLanguage"]; ?></label>
		<select name="selectLanguage" id="selectLanguage" class="form-select">
			<option value="en">English</option>
<!--			<option value="tr">Türkçe</option>-->
		</select>
	</div>
    <div class="mb-3">
        <label class="form-label">Keywords :</label>
        <textarea style="height:250px;" class="form-control" name="keywords" id="keywords" rows="3"><?php echo $getKeywords; ?></textarea>
        <small>Text key</small>
    </div>
    <div class="mb-3">
        <label class="form-label">Index of keywords start :</label>
        <input type="text" id="keywords_index" name="keywords_index" class="form-control" value="<?php echo $getKeywordsIndex; ?>"/>
    </div>
    <div class="mb-3">
        <label class="form-label">Questions :</label>
        <textarea style="height:250px;" class="form-control" name="questions" id="questions" rows="3"><?php echo $getQuestions; ?></textarea>
        <small>Use (,) for seperate question. Use {{var}} for insert your keyword.</small>
    </div>

    <div class="mb-3">
        <label class="form-label">Template :</label>
        <textarea style="height:250px;" class="form-control" name="template" id="template" rows="3"><?php echo $getTemplate; ?></textarea>
        <small>Insert your html code for display.</small>
    </div>

    <div class="mb-3">
        <label class="form-label">Product Template :</label>
        <textarea style="height:250px;" class="form-control" name="productTemplate" id="productTemplate" rows="3"><?php echo $getProductTemplate; ?></textarea>
        <small>Insert your html code for display.</small>
    </div>

	<button type="submit" name="submit" class="btn btn-primary"><?php echo $lang["saveSettings"]; ?></button>
</form>

<script>
	function updateTemperature() {
		document.getElementById("temperatureTextValue").innerText = document.getElementById("temperatureValue").value
	}
</script>