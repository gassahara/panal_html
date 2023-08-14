<?php
function listdir($dir='.') { if (!is_dir($dir)) { return false; }   
                           $files = array();
                           listdiraux($dir, $files);
                           return $files;
}
function listdiraux($dir, &$files) { $handle = opendir($dir);
    while (($file = readdir($handle)) !== false) {
        if ($file == '.' || $file == '..') {continue; }
        $filepath = $dir == '.' ? $file : $dir . '/' . $file;
        if (is_link($filepath)) continue;
        if (is_file($filepath)) $files[] = $filepath;
        //        else if (is_dir($filepath)) { $files[] = $filepath; listdiraux($filepath, $files);}
    } closedir($handle);
}
$l=listdir("msgs/");
foreach ($l as &$m) {
print("$m\n");
}
echo "\n";
