<?php
/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */
require_once __DIR__. '/ReturnValues.php';
/**
 * Class for clean working with IPPcode22 XML.
 */
final class Stats
{


    private $statsCount = array(  "loc" => 0,
                                 "comments" => 0,
                                 "labels" => 0,
                                 "jumps" => 0,
                                 "fwjumps" => 0,
                                 "backjumps" => 0,
                                 "badjumps" => 0);
    
    private $config = [];
    public function __construct($printConfig)
    {   
        $this->config = $printConfig;
    }

    public function addInstruction()
    {
        $this->statsCount["loc"]++;
    }

    public function addComment()
    {
        $this->statsCount["comments"]++;
    }

    public function addLabel($label)
    {
        $this->defineLabel($label);
    }

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

    public function addReturn()
    {
        $this->statsCount["jumps"]++;
    }

    public function endProgram()
    {
        $this->getUnclassifiedJumpsStats();
    }

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
    
    public function printStats()
    {
        foreach(array_keys($this->config) as $path)
        {
            $file = $this->openFile($path);
            foreach($this->config[$path] as $statName)
            {
                $this->writeStatToFile($file, $statName, true);
            }
            $this->closeFile($file);
        }
    }

    private $labelsSet = [];
    private $unclassifiedJumpTargets = [];

    private function defineLabel($label)
    {
        if(!$this->wasLabelDefined($label))
        {
            $this->labelsSet[] = $label;
            $this->statsCount["labels"]++;
        }
    }

    private function wasLabelDefined($label)
    {
        return in_array($label, $this->labelsSet);
    }

    private function openFile($path)
    {    
        $file = fopen($path, "w");
        if ($file === false)
        {
            exit(ReturnValues::INTERNAL_ERR);
        }
        return $file;
    }

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

    private function closeFile($file)
    {
        if (fclose($file) === false)
        {
            exit(ReturnValues::INTERNAL_ERR);
        }
    }
}

?>
