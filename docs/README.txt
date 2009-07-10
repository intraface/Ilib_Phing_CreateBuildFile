
== Fejl ved dependencies? ==

Hvis du får en fejl noget i retningen af undefined function PEAR_Config::getRemote()
så kan det rettes ved at fjerne support for mirrors i PEAR/PackageUpdate.php;
Line 721: $mirror = false; // $mirror = $config->get('preferred_mirror'); 


