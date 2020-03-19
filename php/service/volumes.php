<?php 
    require_once('../comicVine.php');
    require_once('../mongo.php');
    require_once('../init_twig.php');
    require_once '../init_logger.php';

    $volumes = [];
    $character_id = $_GET['character'];
    if (isset($character_id) && $character_id) {
        $volumes = ZwappMongo\Collection::getCharacterVolumes($character_id);
    }
    else {
        $volumes = ZwappMongo\Collection::getVolumes()->getList();
    }

    foreach($volumes as $volume) {
        $template = $_GET['template'] ?: 'radial_nav__choice.html';
        $volume_formatted = [];
        
        $volume_formatted['id'] = $volume->id; 
        $volume_formatted['name'] = $volume->name;
        $volume_formatted['icon_url'] = $volume->icon_url;
        $volume_formatted['type'] = "volume";

        $twig->load($template)->display($volume_formatted);          
    }
 ?>