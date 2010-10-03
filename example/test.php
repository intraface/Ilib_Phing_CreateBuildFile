<?php
require_once 'PEAR/PackageUpdate.php';
$ppu = PEAR_PackageUpdate::factory('Cli', 'Ilib_Countries', 'public.intraface.dk');
if ($ppu != false) {
    fwrite(STDOUT,"FETCHING LATEST RELEASE. Please wait...\n");

    $release = $ppu->getLatestRelease();
    if ($release != false) {
        fwrite(STDOUT,"LATEST RELEASE: ".$release['state']." ".$release['version']."\n");
    }
}
die("finish\n");