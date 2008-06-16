<?php
/**
 * Class to parse build.xml file
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
class Ilib_Phing_BuildFile_Parse {
    
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
     * parse the xml file.
     * 
     * @return array with the parsed values
     */
    public function parse() {
        
        
        $content = file_get_contents($this->file);
        
        $xml_parser = xml_parser_create();
        
        $values = array();
        $indexes = array();
        
        xml_parse_into_struct($xml_parser, $content, $values, $indexes);
        
        
        // find package_name
        foreach($indexes['PROJECT'] AS $key) {
            if(!empty($values[$key]['attributes']['NAME'])) {
                $return['package_name'] = $values[$key]['attributes']['NAME'];
                break;
            }
        }
        
        // find version and stability
        foreach($indexes['PROPERTY'] AS $key) {
            
            if(!empty($values[$key]['attributes']['NAME'])) {
                switch($values[$key]['attributes']['NAME']) {
                    case 'version':
                        $return['package_version'] = $values[$key]['attributes']['VALUE'];
                        break;
                    case 'stability':
                        $return['package_stability'] = $values[$key]['attributes']['VALUE'];
                        break;
                }
            }
        }
        
        // find summary
        if(!empty($indexes['SUMMARY'][0]) && !empty($values[$indexes['SUMMARY'][0]]['value'])) {
            $return['package_summary'] = $values[$indexes['SUMMARY'][0]]['value'];
        }
        
        // find package_description
        if(!empty($indexes['DESCRIPTION'][0]) && !empty($values[$indexes['DESCRIPTION'][0]]['value'])) {
            $return['package_description'] = $values[$indexes['DESCRIPTION'][0]]['value'];
        }
        
        // release notes
        if(!empty($indexes['NOTES'][0]) && !empty($values[$indexes['NOTES'][0]]['value'])) {
            $return['release_notes'] = $values[$indexes['NOTES'][0]]['value'];
        }
        
        // dependencies
        $return['dependencies'] = array();
        $i = 0;
        if(!empty($indexes['PACKAGE'])) {
            foreach($indexes['PACKAGE'] AS $key) {
                $return['dependencies'][$i]['name'] = $values[$key]['attributes']['NAME'];
                $return['dependencies'][$i]['channel'] = $values[$key]['attributes']['CHANNEL'];
                if(!empty($values[$key]['attributes']['RECOMMENDED_VERSION'])) {
                    $return['dependencies'][$i]['recommended_version'] = $values[$key]['attributes']['RECOMMENDED_VERSION'];
                }
                else {
                    $return['dependencies'][$i]['recommended_version'] = '';
                }
                $return['dependencies'][$i]['minimum_version'] = $values[$key]['attributes']['MINIMUM_VERSION'];
                $i++;
            }
        }
        
        return $return;
    }    
}
?>