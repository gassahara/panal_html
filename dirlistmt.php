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
$maxl=0;
$lenD=1;
foreach ($l as &$m) {
    if(strlen($m)>$maxl) $maxl=strlen($m);
    $lenD++;
}
$i=1;
echo ("int main() {\nunsigned char files[$lenD][$maxl]={");
foreach ($l as &$m) {
    echo("\"$m\",");
    $i++;
}
echo("\"END\"};\n}");
echo "\n";
