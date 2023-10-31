<?php
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',1);
@ob_end_clean();
set_time_limit(0);
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
$cad="";
ini_set('display_errors', 1);
foreach ($l as &$m) {
    if(strlen($m)>$maxl) $maxl=strlen($m);
    $lenD++;
}
$cad=$cad . "int main() {\nunsigned char files[$lenD][$maxl]={";
foreach ($l as &$m) {
    $cad=$cad . ("\"$m\",");
}
$cad=$cad . ("\"END\"};\n}\n");
$count=0;
while(file_exists("msgs/dirlistmt.l.$count")) {
    $count++;
}
$g=0;
while($g<strlen($cad)) {
    $g=file_put_contents("msgs/dirlistmt.l.$count", $cad, LOCK_EX);
    $dir = 'msgs';
    $files = glob('msgs/dirlistmt.l.*');
    foreach ($files as $file) {
        if (time() - filemtime($file) > 120) {
            unlink($file);
        }
    }
}
echo "var error=\"OK\";var file=\"msgs/dirlistmt.l.$count\";";
