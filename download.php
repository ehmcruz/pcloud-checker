<?php

header("Content-Type: text/plain");

$credentialPath = "./data/app.cred";

require_once("./lib-pcloud/autoload.php");

if (isset($_GET["folderid"]))
	$folderid = (int)$_GET["folderid"];
else
	$folderid = 0;

if (isset($_GET["type"]))
	$type = $_GET["type"];
else
	$type = "sha1";

try {
	$pCloudApp = new pCloud\App();

	$cred = pCloud\Auth::getAuth($credentialPath);

	$access_token = $cred['access_token'];
	$locationid = 1;

	$pCloudApp->setAccessToken($access_token);
	$pCloudApp->setLocationId($locationid);

	$pCloudFolder = new pCloud\Folder($pCloudApp);

	$meta = $pCloudFolder->getMetadata($folderid)->metadata;
	
	$content = $pCloudFolder->getContent($folderid);
	
	foreach ($content as $item) {
		if (!$item->isfolder) {
			$pCloudFile = new pCloud\File($pCloudApp);
			$info = $pCloudFile->getInfo($item->fileid);
			$mf = $info->metadata;
			
			echo "{$info->sha1}  {$item->name}\n";
		}
	}
} catch (Exception $e) {
	echo $e->getMessage();
}

?>
