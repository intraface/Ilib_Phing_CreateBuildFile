<?php
/**
 * Class to create build.properties file
 * At this time it is customized for intraface packages.
 * 
 * PHP Version 4 and 5
 *
 * @category Ilib_Phing
 * @package  Ilib_Phing_BuildFile
 * @author   Sune Jensen <sj@sunet.dk>
 * @author   Lars Olesen <lars@legestue.net>
 * 
 */

/**
 * Class to parse build.xml file
 *
 * @category Ilib_Phing
 * @package  Ilib_Phing_BuildFile
 * @author   Sune Jensen <sj@sunet.dk>
 * @author   Lars Olesen <lars@legestue.net>
 */
class Ilib_Phing_BuildFile_CreateProperties 
{
    
    public $pear_channel_username;
    public $pear_channel_password;
    public $ftp_user;
    public $ftp_password;
    
    public function __construct() 
    {
        
    }
    
    public function create() 
    {
        
        $file = '';
        $file .= "pear.channel.uri      = http://public.intraface.dk/frontend.php\n";
        $file .= "pear.channel.username = ".$this->pear_channel_username."\n";
        $file .= "pear.channel.password = ".$this->pear_channel_password."\n";
        $file .= "ftp.host              = public.intraface.dk\n";
        $file .= "ftp.user              = ".$this->ftp_user."\n";
        $file .= "ftp.password          = ".$this->ftp_password."\n";
        
        return $file;
        
    }
    
    /**
     * reads in values from existing properties file
     * 
     * @param string $file path to file
     */
    public function readValuesFromBuildFile($file) {
        require_once 'Ilib/Phing/BuildFile/ParseProperties.php';
        
        $parser = new Ilib_Phing_BuildFile_ParseProperties($file);
        $values = $parser->parse();
        
        if(!empty($values['pear_channel_username'])) $this->pear_channel_username = $values['pear_channel_username'];
        if(!empty($values['pear_channel_password'])) $this->pear_channel_password = $values['pear_channel_password'];
        if(!empty($values['ftp_user'])) $this->ftp_user = $values['ftp_user'];
        if(!empty($values['ftp_password'])) $this->ftp_password = $values['ftp_password'];
    }
    
}
?>