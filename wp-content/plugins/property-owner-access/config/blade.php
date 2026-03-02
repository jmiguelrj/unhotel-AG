<?php
use eftec\bladeone\BladeOne;

$views = plugin_dir_path( __FILE__ ) . '../views';
$cache = plugin_dir_path( __FILE__ ) . '../cache';
$blade = new BladeOne($views,$cache,BladeOne::MODE_DEBUG); // MODE_DEBUG allows to pinpoint troubles.