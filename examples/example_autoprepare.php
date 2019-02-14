<?php
ini_set('display_errors', 1);

/* 
 * Dane aktualizowane na zadanie naszego serwera poprzez webhook 
 */
$hasActiveCampaigns = true;

include './vendor/autoload.php';

use Adquesto\SDK\Content;
use Adquesto\SDK\InMemoryStorage;
use Adquesto\SDK\CurlHttpClient;
use Adquesto\SDK\PositioningSettings;
use Adquesto\SDK\ElementsContextProvider;

const API_URL = 'https://api.adquesto.com/v1/publishers/services/';

$adquesto = new Content(
    API_URL,
    '__paste Service UUID here__',
    new InMemoryStorage,
    new CurlHttpClient,
    PositioningSettings::factory(PositioningSettings::STRATEGY_UPPER)
);

try {
    $javascript = $adquesto->javascript([
        $elementsProvider = new ElementsContextProvider(null, null, 0, $hasActiveCampaigns)
    ]);
} catch (Adquesto\SDK\NetworkErrorException $e) {
    var_dump($e);  // Handle exception here
}

$mainQuestElementId = $elementsProvider->mainQuestId();
$reminderQuestElementId = $elementsProvider->reminderQuestId();

$originalContent = <<<STR
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Blandit massa enim nec dui nunc mattis enim ut. Diam quam nulla porttitor massa id neque aliquam vestibulum morbi. Duis at tellus at urna condimentum mattis pellentesque. Tortor id aliquet lectus proin nibh. Urna et pharetra pharetra massa massa. At urna condimentum mattis pellentesque id nibh tortor id. Faucibus a pellentesque sit amet porttitor. Diam ut venenatis tellus in metus vulputate eu scelerisque. Sed sed risus pretium quam vulputate dignissim suspendisse in.</p>
<p>Curabitur gravida arcu ac tortor dignissim. Id leo in vitae turpis massa sed elementum. Lacus laoreet non curabitur gravida arcu. Turpis tincidunt id aliquet risus feugiat in ante metus. Euismod lacinia at quis risus sed vulputate. Lorem mollis aliquam ut porttitor leo a. Sit amet est placerat in egestas erat imperdiet sed. Egestas dui id ornare arcu odio. Turpis in eu mi bibendum neque egestas congue quisque. Tempor nec feugiat nisl pretium fusce. Potenti nullam ac tortor vitae purus faucibus ornare. Tincidunt ornare massa eget egestas purus viverra accumsan in. Cursus sit amet dictum sit amet justo donec enim diam. Porttitor rhoncus dolor purus non enim. Adipiscing commodo elit at imperdiet. Sed odio morbi quis commodo odio aenean. Sagittis purus sit amet volutpat consequat mauris nunc congue nisi. Non pulvinar neque laoreet suspendisse interdum.</p>
<p>Laoreet sit amet cursus sit amet dictum. Scelerisque mauris pellentesque pulvinar pellentesque habitant morbi tristique. Aliquet nibh praesent tristique magna sit amet purus. Facilisi morbi tempus iaculis urna id. Sed euismod nisi porta lorem mollis. Id neque aliquam vestibulum morbi. Vehicula ipsum a arcu cursus vitae congue mauris rhoncus. Posuere ac ut consequat semper viverra nam libero justo laoreet. Nulla malesuada pellentesque elit eget gravida cum sociis natoque penatibus. Eu tincidunt tortor aliquam nulla facilisi cras. Non curabitur gravida arcu ac tortor. Cras tincidunt lobortis feugiat vivamus at augue eget. Morbi tristique senectus et netus et malesuada fames. Quis hendrerit dolor magna eget est lorem ipsum. Eu turpis egestas pretium aenean pharetra magna ac. Sapien nec sagittis aliquam malesuada bibendum arcu vitae elementum.</p>
<p>Augue ut lectus arcu bibendum. Pellentesque eu tincidunt tortor aliquam nulla facilisi. Lobortis scelerisque fermentum dui faucibus in ornare quam viverra orci. Turpis egestas sed tempus urna et pharetra pharetra. Pretium vulputate sapien nec sagittis aliquam malesuada bibendum arcu. At volutpat diam ut venenatis tellus in. Eget lorem dolor sed viverra ipsum. Vitae nunc sed velit dignissim sodales ut eu sem integer. Volutpat maecenas volutpat blandit aliquam etiam. Nisl tincidunt eget nullam non nisi. Habitant morbi tristique senectus et netus et malesuada fames ac. Urna nec tincidunt praesent semper feugiat nibh sed. Ut sem viverra aliquet eget sit. Et magnis dis parturient montes nascetur ridiculus mus mauris vitae. Pellentesque habitant morbi tristique senectus et netus et malesuada. Pellentesque adipiscing commodo elit at imperdiet. Accumsan tortor posuere ac ut consequat. Sollicitudin aliquam ultrices sagittis orci a scelerisque purus semper eget. Tristique senectus et netus et malesuada fames.</p>
<p>Consequat interdum varius sit amet mattis vulputate enim nulla aliquet. Iaculis nunc sed augue lacus viverra. Orci eu lobortis elementum nibh tellus molestie nunc non. Convallis convallis tellus id interdum velit. Pretium vulputate sapien nec sagittis aliquam malesuada bibendum. Nunc id cursus metus aliquam. Quis enim lobortis scelerisque fermentum dui faucibus in ornare quam. Nascetur ridiculus mus mauris vitae ultricies. Urna duis convallis convallis tellus id. Aliquet sagittis id consectetur purus ut faucibus pulvinar. Sed turpis tincidunt id aliquet risus feugiat in ante metus. Ullamcorper dignissim cras tincidunt lobortis. Blandit cursus risus at ultrices mi tempus imperdiet nulla. Eu mi bibendum neque egestas congue quisque. Cras semper auctor neque vitae tempus quam pellentesque. Malesuada nunc vel risus commodo viverra maecenas accumsan lacus vel. Quis ipsum suspendisse ultrices gravida dictum fusce ut placerat orci. Mattis nunc sed blandit libero volutpat.</p>
STR;

$content = $adquesto->autoPrepare(
    $originalContent,
    '<div id="' . $mainQuestElementId . '"></div>',
    '<div id="' . $reminderQuestElementId . '"></div>'
);
$content->setJavaScript($javascript);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Demo page - presenting custom integration using Adquesto PHP-SDK</title>
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link rel="icon" type="image/png" href="images/favicon.png">
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="twelve column" style="margin-top: 5%">
        <h4>Demo page - presenting custom integration using Adquesto PHP-SDK</h4>
        <?php echo $content; ?>
      </div>
    </div>
  </div>
</body>
</html>
