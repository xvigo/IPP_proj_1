<?php
/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */
require_once __DIR__. '/ReturnValues.php';
/**
 * Class for statp stats extension.
 */
final class Stats
{

    /**
     * Property for storing stats.
     */
    private $statsCount = array( "loc" => 0,
                                 "comments" => 0,
                                 "labels" => 0,
                                 "jumps" => 0,
                                 "fwjumps" => 0,
                                 "backjumps" => 0,
                                 "badjumps" => 0);
    
    /**
     * Property for Stats configuration - which stat to which file.
     */
    private $config = [];
    public function __construct($printConfig)
    {   
        $this->config = $printConfig;
    }

    /**
     * Includes new instruction in the stats.
     */
    public function addInstruction()
    {
        $this->statsCount["loc"]++;
    }

    /**
     * Includes new comment in the stats.
     */
    public function addComment()
    {
        $this->statsCount["comments"]++;
    }

    /**
     * Includes new label in the stats.
     * @param $label name of the label
     */
    public function addLabel($label)
    {
        $this->defineLabel($label);
    }

    /**
     * Includes new label in the stats.
     * @param $target name of the jump target label
     */
    public function addJump($target)
    {
        $this->statsCount["jumps"]++;
        if ($this->wasLabelDefined($target))
        {
            $this->statsCount["backjumps"]++;
        }
        else
        {
            $this->unclassifiedJumpTargets[] = $target;
        }
    }

    /**
     * Includes new return in the stats.
     */
    public function addReturn()
    {
        $this->statsCount["jumps"]++;
    }

    /**
     * Inform stats that program has ended so stats can be calculated.
     */
    public function endProgram()
    {
        $this->getUnclassifiedJumpsStats();
    }

    /**
     * Prints stats into output file based on config property.
     */
    public function printStats()
    {
        foreach(array_keys($this->config) as $path)
        {
            $file = $this->openFile($path);
            foreach($this->config[$path] as $statName)
            {
                $this->writeStatToFile($file, $statName);
            }
            $this->closeFile($file);
        }
    }

    /**
     * Property for storing unique labels.
     */
    private $labelsSet = [];
    /**
     * Property for storing jump targets that were not backwards jumps for further classification.
     */
    private $unclassifiedJumpTargets = [];

    /**
     * Calculates statistics for jump targets that weren't calssified.
     */
    private function getUnclassifiedJumpsStats()
    {
        foreach($this->unclassifiedJumpTargets as $target)
        {
            if($this->wasLabelDefined($target))
            {
                $this->statsCount["fwjumps"]++;
            }
            else
            {
                $this->statsCount["badjumps"]++;
            }
        }
    }

    /**
     * Check whether label was defined before, if not adds it into labelsSet property.
     */
    private function defineLabel($label)
    {
        if(!$this->wasLabelDefined($label))
        {
            $this->labelsSet[] = $label;
            $this->statsCount["labels"]++;
        }
    }

    /**
     * Check whether label was defined before, i.e. whether exists in labelsSet property.
     */
    private function wasLabelDefined($label)
    {
        return in_array($label, $this->labelsSet);
    }

    /**
     * Opens file given as argument for writing and returns file pointer.
     * if error occurs - exits with INTERNAL ERROR(see ReturnCodes.php)
     * @param string $path path to file to be opened
     * @return $file opened file pointer
     */
    private function openFile($path)
    {    
        $file = fopen($path, "w");
        if ($file === false)
        {
            exit(ReturnValues::INTERNAL_ERR);
        }
        return $file;
    }

    /**
     * Write single stat value to file. 
     * @param $file pointer to file opened for writing
     * @param string $statName name of the stat to be writen into file
     * @param bool $debug if true also writes statnames in fornt of stat value (default: false)
     */
    private function writeStatToFile($file, $statName, $debug = false)
    {
        if ($debug)
        {
            $line = $statName . ": " . $this->statsCount[$statName] . "\n";

        }
        else
        {
            $line = $this->statsCount[$statName] . "\n";

        }
        if (fwrite($file, $line) === false)
        {
            exit(ReturnValues::INTERNAL_ERR);
        }
    }

    /**
     * Close file given as argument.
     * if error occurs - exits with INTERNAL ERROR(see ReturnCodes.php)
     * @param string $file pointer to the file to be closed
     */
    private function closeFile($file)
    {
        if (fclose($file) === false)
        {
            exit(ReturnValues::INTERNAL_ERR);
        }
    }
}

?>
