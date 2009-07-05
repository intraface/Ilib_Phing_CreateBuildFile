<?php
/**
 * Class to create build.xml file
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
 * Class to create build.xml file
 *
 * @category Ilib_Phing
 * @package  Ilib_Phing_BuildFile
 * @author   Sune Jensen <sj@sunet.dk>
 * @author   Lars Olesen <lars@legestue.net>
 */
class Ilib_Phing_BuildFile_Create 
{
    /**
     * @var string name of package
     */
    public $package_name;
    
    /**
     * @var string package version (X.X.X)
     */
    public $package_version;
    
    /**
     * @var string package stability (alpha, beta, stable)
     */
    public $package_stability;
    
    /**
     * @var string package summary
     */
    public $package_summary;
    
    /**
     * @var string package description (might maybe only be 47 chars wide?)
     */
    public $package_description;
    
    /**
     * @var string release notes
     */
    public $release_notes;
    
    /**
     * @var array dependencies
     */
    private $dependencies = array();
    
    /**
     * @var array replacements
     */
    private $replacements = array();
    
    /**
     * @var array tests
     */
    private $tests = array();
    
    /**
     * @var array roles = array();
     */
    private $roles;
    
    /**
     * constructor
     */
    public function __construct() 
    {
    }
    
    /**
     * creates xml file
     * 
     * @return string xml file
     */
    public function create() 
    {
        $xml = "";
        
        $xml .= $this->createTaskDefinitions();
        $xml .= $this->createProperties();
        $xml .= $this->createTaskPrepare();
        $xml .= $this->createTaskExport();
        $xml .= $this->createTaskPear();
        $xml .= $this->createTaskBuild();
        $xml .= $this->createTaskMakePearPackage();
        $xml .= $this->createTaskDeploy();
        $xml .= $this->createTaskPhpcs();
        $xml .= $this->createTaskPhpDocumentor();
        $xml .= $this->createTaskTest();
        
        return $this->createHeader($this->createProject($xml));
    }
    
    /**
     * Adds dependency to package
     * 
     * @param string $name name of depedency
     * @param string $channel channel to use
     * @param string $recommended_version the recommended version of the dependency
     * @param string $minimum_version the minimum dependency version to use
     */
    public function addDependency($name, $channel, $recommended_version, $minimum_version)
    {
        $this->dependencies[] = array('name' => $name, 'channel' => $channel, 'recommended_version' => $recommended_version, 'minimum_version' => $minimum_version);
    }
    
    /**
     * return array of dependencies
     * 
     * @return array dependencies
     */
    public function getDependencies() 
    {
        return $this->dependencies;
    }
    
    /**
     * clear all dependencies in package
     * 
     */
    public function clearDependencies() 
    {
        $this->dependencies = array();
    }
    
    /**
     * Adds replacement to package
     * 
     * @param string $path name of file
     * @param string $type type of replacement
     * @param string $from what to replace
     * @param string $to what to replace with
     */
    public function addReplacement($path, $type, $from, $to)
    {
        $this->replacements[] = array('path' => $path, 'type' => $type, 'from' => $from, 'to' => $to);
    }
    
    /**
     * return array of replacements
     * 
     * @return array replacements
     */
    public function getReplacements() 
    {
        return $this->replacements;
    }
    
    /**
     * clear all replacements in package
     * 
     */
    public function clearReplacements() 
    {
        $this->replacements = array();
    }
    
    /**
     * Add test to build
     * the tests should be placed in the ${test.dir} in the properties section
     * 
     * @param string $name name of test. Should be the same as the php file except missing the .php
     */
    public function addTest($name)
    {
        $this->tests[] = array('name' => $name);
    }
    
    /**
     * clears all tests in build
     */
    public function clearTests()
    {
        $this->tests = array();
    }
    
    /**
     * Add pear roles to files
     */
    public function addFileRole($file, $role) 
    {
        $this->roles[$role][] = $file;
    }
    
    private function getFileRoles($role)
    {
        if(isset($this->roles[$role])) {
            return $this->roles[$role];
        }
        return array();
    }
    
    /**
     * reads in values from existing build file
     * 
     * @param string $file path to file
     */
    public function readValuesFromBuildFile($file) 
    {
        require_once 'Ilib/Phing/BuildFile/Parse.php';
        
        $parser = new Ilib_Phing_BuildFile_Parse($file);
        $values = $parser->parse();
        
        $this->package_name = $values['package_name'];
        $this->package_version = $values['package_version'];
        $this->package_stability = $values['package_stability'];
        $this->package_summary = $values['package_summary'];
        $this->package_description = $values['package_description']; /* 47 chars wide */
        $this->release_notes = $values['release_notes'];
        
        foreach($values['dependencies'] AS $dependency) {
            $this->addDependency($dependency['name'], $dependency['channel'], $dependency['recommended_version'], $dependency['minimum_version']);
        }
        
        foreach($values['replacements'] AS $replacement) {
            $this->addReplacement($replacement['path'], $replacement['type'], $replacement['from'], $replacement['to']);
        }
        
        /**
         * @todo: need to read in tests.
         */
        
    }
    
    /**
     * returns the task test
     */
    private function createTaskTest()
    {
        $return = "    <target name=\"test\">\n";
        
        foreach($this->tests AS $test) {
            $return .= "        <exec dir=\"\${tests.dir}\" command=\"phpunit\n" .
                    "           --log-xml \${reports.phpunit.dir}/phpunit.xml\n" .
                    "           --log-pmd \${reports.phpunit.dir}/phpunit.pmd.xml\n" .
                    "           --coverage-xml \${reports.phpunit.dir}/phpunit.coverage.xml\n" .
                    "           --coverage-html \${reports.coverage.dir}/\n" .
                    "           ".$test['name']."\" passthru=\"true\" checkreturn=\"true\" />\n";
        }            
        $return .= "    </target>\n\n";
        return $return;
    }
    
    /**
     * returns the tast PhpDocumentor
     */
    private function createTaskPhpDocumentor()
    {
        return "    <target name=\"php-documentor\" depends=\"export\">\n" .
                "        <mkdir dir=\"\${api.dir}/\${package-name}\" />\n" .
                "        <exec dir=\"\${source.dir}\" command=\"phpdoc -ue on -t \${api.dir}/\${package-name} -d ./src\" passthru=\"true\" />\n" .
                "    </target>\n\n";
    }
    
    /**
     * returns the Task phpcs
     */
    private function createTaskPhpcs() 
    {
        return "    <target name=\"phpcs\">\n" .
                "        <exec output=\"\${reports.phpcs.dir}/checkstyle.xml\" dir=\"\${source.dir}/src\" command=\"phpcs --report=checkstyle . --standard=PEAR\" />\n" .
                "    </target>\n\n";
    }
    
    /**
     * returns task deploy
     */
    private function createTaskDeploy()
    {
        return "    <target name=\"deploy\" depends=\"build\">\n" .
                "        <property file=\"./build.properties\" />\n" .
                "        <peardeploy uri=\"\${pear.channel.uri}\" username=\"\${pear.channel.username}\" password=\"\${pear.channel.password}\">\n" .
                "            <fileset dir=\"\${build.dir}\">\n" .
                "                <include name=\"\${package-name}-\${version}.tgz\"/>\n" .
                "            </fileset>\n" .
                "        </peardeploy>\n" .
                "        <ftpdeploy host=\"\${ftp.host}\" username=\"\${ftp.user}\" password=\"\${ftp.password}\" port=\"21\" dir=\"./public.intraface.dk/docs/\" mode=\"binary\" clearfirst=\"false\">\n" . /* Very important that clearfirst=false, otherwise all documentation will be deleted! */
                "            <fileset dir=\"\${api.dir}\">\n" .
                "                <include name=\"*/**\" />\n" .
                "            </fileset>\n" .
                "        </ftpdeploy>\n" .
                "    </target>\n\n";
    }
    
    /**
     * returns task make pear package
     */
    private function createTaskMakePearPackage() 
    {
        return "    <target name=\"make-pear-package\" depends=\"pear\">\n" .
                "        <tar compression=\"gzip\" destfile=\"\${build.dir}/\${package-name}-\${version}.tgz\" basedir=\"\${build.dir}/package\" />\n" .
                "    </target>\n\n";
    }
    
    /**
     * returns the tast create build
     */
    private function createTaskBuild() 
    {
        return "    <target name=\"build\" depends=\"prepare, export, test, phpcs, php-documentor, make-pear-package\"></target>\n";
    }
    
    /**
     * returns the task pear
     */
    private function createTaskPear() 
    {
        
        $return = "    <target name=\"pear\" depends=\"export\">\n" .
                "        <d51pearpkg2 dir=\"\${build.dir}/package/\${package-name}-\${version}/\" baseinstalldir=\"/\">\n" .
                "            <name>\${package-name}</name>\n" .
                "            <summary>".$this->package_summary."</summary>\n" .
                "            <channel>\${pear.channel}</channel>\n" .
                "            <description>".str_replace('\n', "\n", $this->package_description)."</description>\n" .
                "            <lead user=\"lsolesen\" name=\"Lars Olesen\" email=\"lars@legestue.net\" />\n" .
                "            <lead user=\"sune.t.jensen\" name=\"Sune Jensen\" email=\"sj@sunet.dk\" />\n" .
                "            <license>LGPL</license>\n" .
                "            <version release=\"\${version}\" api=\"\${version}\" />\n" .
                "            <stability release=\"\${stability}\" api=\"\${stability}\" />\n";
         # Find roles to install as
        $roles = array('script', 'doc', 'www');
        foreach($roles AS $role) {
            $files = $this->getFileRoles($role);
            if(count($files) > 0) {
                $return .= "            <dirroles key=\"".$role."\">".$role."</dirroles>\n";
            }
        }
        $return .= "            <release>\n";
        foreach($roles AS $role) {
            $files = $this->getFileRoles($role);
            foreach($files AS $file) {
                $return .= "                <install as=\"".$file."\" name=\"".$role."/".$file."\" />\n";
            }
        }
        
        $return .= "            </release>\n" .
                "            <dependencies>\n" .
                "                <php minimum_version=\"5.2.0\" />\n" .
                "                <pear minimum_version=\"1.6.0\" recommended_version=\"1.6.1\" />\n";
    
        foreach($this->dependencies AS $dependency) {
            $return .= "                <package name=\"".$dependency['name']."\" channel=\"".$dependency['channel']."\" recommended_version=\"".$dependency['recommended_version']."\" minimum_version=\"".$dependency['minimum_version']."\" />\n";
        }
    
        $return .= "            </dependencies>\n" .
                "            <notes>".$this->release_notes."</notes>\n";
        
        foreach($this->replacements AS $replacement) {
            $return .= "            <replacement path=\"".$replacement['path']."\" type=\"".$replacement['type']."\" from=\"".$replacement['from']."\" to=\"".$replacement['to']."\" />\n";
        }
        
        $return .= "        </d51pearpkg2>\n" .
                "    </target>\n\n";
        
        return $return;
    }
    
    /**
     * returns the task export
     */
    private function createTaskExport() 
    {
        return "    <target name=\"export\">\n" .
                "        <echo msg=\"Exporting SVN files\" />\n" .
                "        <exec command=\"svn export \${source.dir}/src \${build.dir}/temp\" />\n" .
                "        <mkdir dir=\"\${build.dir}/package/\${package-name}-\${version}\" />\n" .
                "        <copy todir=\"\${build.dir}/package/\${package-name}-\${version}\">\n" .
                "            <filterchain>\n" .
                "                <replacetokens begintoken=\"@@\" endtoken=\"@@\">\n" .
                "                    <token key=\"VERSION\" value=\"\${version}\" />\n" .
                "                </replacetokens>\n" .
                "            </filterchain>\n" .
                "            <fileset dir=\"\${build.dir}/temp\">\n" .
                "                <include name=\"**\" />\n" .
                "            </fileset>\n" .
                "        </copy>\n" .
                "    </target>\n\n";
        // trying to move package file but unsuccessfull!
        //                 "        <move file=\"\${build.dir}/package/\${package-name}-\${version}/package.xml\" tofile=\"\${build.dir}/package/package.xml\" overwrite=\"true\" />" .
        
    }
    
    /**
     * returns the tast prepare
     */
    private function createTaskPrepare() 
    {
        return "    <target name=\"prepare\">\n" .
                "        <delete dir=\"\${build.dir}\" />\n" .
                "        <mkdir dir=\"\${build.dir}\" />\n" .
                "    </target>\n\n";
    }
    
    /**
     * returns the properties section
     */
    private function createProperties() 
    {
        return "    <property name=\"package-name\" value=\"\${phing.project.name}\" />\n" .
                "    <property name=\"version\" value=\"".$this->package_version."\" />\n" .
                "    <property name=\"stability\" value=\"".$this->package_stability."\" />\n" .
                "    <property name=\"pear.channel\" value=\"public.intraface.dk\" />\n\n" .
                "    <property name=\"source.dir\" value=\".\" />\n" .
                "    <property name=\"tests.dir\" value=\"./tests\" />\n" .
                "    <property name=\"build.dir\" value=\"../build\" />\n" .
                "    <property name=\"reports.phpcs.dir\" value=\"../build/logs\" />\n" .
                "    <property name=\"reports.dir\" value=\"../build/logs\" />\n" .
                "    <property name=\"reports.phpunit.dir\" value=\"../../build/logs\" />\n" .
                "    <property name=\"reports.coverage.dir\" value=\"../../build/logs/coverage\" />\n" .
                "    <property name=\"api.dir\" value=\"../build/api\" />\n\n";
    }
    
    /**
     * returns task definitions section
     */
    private function createTaskDefinitions() 
    {
        return "    <taskdef classname=\"phing.tasks.ext.d51PearPkg2Task\" name=\"d51pearpkg2\" />\n" .
               "    <taskdef classname=\"phing.tasks.ext.IlibPearDeployerTask\" name=\"peardeploy\" />\n\n";
    }
    
    /**
     * returns procejct 
     */
    private function createProject($content)
    {
        return "<project name=\"".$this->package_name."\" basedir=\".\" default=\"build\">\n".$content."</project>\n";
    }
    
    /**
     * returns header.
     */
    private function createHeader($content) 
    {
        return "<?xml version=\"1.0\" ?>\n".$content;
    }   
}