<?php
/**
 * Class to parse build.properties file
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
 * Class to parse build.properties file
 *
 * @category Ilib_Phing
 * @package  Ilib_Phing_BuildFile
 * @author   Sune Jensen <sj@sunet.dk>
 * @author   Lars Olesen <lars@legestue.net>
 */
class Ilib_Phing_BuildFile_ParseProperties 
{
        /**
     * @var string path to file to parse
     */
    private $file;
    
    /**
     * constructor
     * 
     * @param string $file path to file
     */
    public function __construct($file) 
    {
        $this->file = $file;
    }
    
    /**
     * parse the properties file.
     * 
     * @return array with the parsed values
     */
    public function parse() 
    {
        $contents = file($this->file);
        $return = array();
        
        foreach($contents AS $line) {
            $param = trim(substr($line, 0, strpos($line, '=')));
            $value = trim(substr($line, strpos($line, '=')+1));
            
            if($param != '') {
                $param = str_replace('.', '_', $param);
                $return[$param] = $value;
            }
        }
        return $return;
    }
}
?>
