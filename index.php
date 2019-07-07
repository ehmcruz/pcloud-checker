<?php

function cmp_flist ($a, $b)
{
    return strcmp($a->name, $b->name);
}

$credentialPath = "./data/app.cred";

require_once("./lib-pcloud/autoload.php");

if (isset($_GET["folderid"]))
	$folderid = (int)$_GET["folderid"];
else
	$folderid = 0;

try {
	$pCloudFolder = new pCloud\Folder();

	echo "<ul style=\"list-style-type: none;\">";
	
	$meta = $pCloudFolder->getMetadata($folderid)->metadata;
	
	$content = $pCloudFolder->getContent($folderid);
	
	echo "Current folder: {$meta->name} (<a href=\"download.php?folderid=$folderid&type=md5\">md5</a>)<br>\n<br>\n";
	
	if ($folderid != 0)
		echo "<li><a href=\"index.php?folderid={$meta->parentfolderid}\">..</a></li>\n";

	usort($content, "cmp_flist");

	foreach ($content as $item) {
		echo "<li>";
		
		if ($item->isfolder) {
			echo "<a href=\"index.php?folderid={$item->folderid}\">".$item->name."</a>";
		}
		else {
//			$pCloudFile = new pCloud\File();
//			$info = $pCloudFile->getInfo($item->fileid);
//			$mf = $info->metadata;
			
			echo "{$item->name} ({$item->size} bytes)";
		}
		
		echo "</li>\n";
	}
	
//	var_dump($meta);
	
	if ($folderid != 0)
		echo "<li><a href=\"index.php?folderid={$meta->parentfolderid}\">..</a></li>\n";
	
	echo "</ul>\n";
} catch (Exception $e) {
	echo $e->getMessage();
}

?>
