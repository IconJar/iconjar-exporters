<?php

use iconjar\exporters\IconJar;
use iconjar\Group;
use iconjar\Icon;
use iconjar\License;
use iconjar\Set;

require_once 'iconjar/autoloader.php';

date_default_timezone_set('UTC');

// example license
$license = new License('Example License');
$license->url = 'https://geticonjar.com';
$license->description = 'Do whatever you please :-)';

// example group
$group = new Group('Just a test Group');

// example icon
$icon = new Icon('Some Bear', __DIR__.DIRECTORY_SEPARATOR.'example.svg');
$icon->license = $license;
$icon->add_tags(['example', 'tags', 'hello', 'world']);

// example set
$set = new Set('Example Set');
$set->add_icon($icon);
$group->add_set($set);

// iconjar exporter
$exporter = new IconJar('example set');
$exporter->add_group($group);

// export, might need to chmod the directory its going to be saved in
$exporter->save(__DIR__.DIRECTORY_SEPARATOR.'exports');