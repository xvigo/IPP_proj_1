<?php
/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */

require_once __DIR__. '/ReturnValues.php';

/**
 * Class for command lines argument processing.
 */
final class CLAProcessor
{
    /**
     * Process commmand line arguments.
     *   --help - prints help to standard output
     *   no arguments - does nothing
     *   invalid arguments - parameter error 
     * @param $argc - number of comman line arguments
     * @param $argv - array of command line arguments
     */
    static function process($argc, $argv)
    {
        if ($argc == 1)
        {
            return;
        }
        else if (in_array("--help", $argv))
        {
            if($argc == 2)
            {
                echo "Usage: php8.1 parse.php \n";
                echo "Reads IPPcode22 source code from standard input, checks its lexical and syntactic correctness and prints its XML representation to standard output.\n";
                exit(ReturnValues::SUCCESS);
            }
            else
            {
                fwrite(STDERR, "ERROR: '--help' can't be used with any other parameter");
                exit(ReturnValues::PARAMETER_ERR);
            }
        }
        else
        {               
            $config = [];

            array_shift($argv);
            $path = Self::processsFileArg($config, $argv[0]);
            array_shift($argv);

            foreach($argv as $arg)
            {
                switch ($arg) {
                    case '--loc':
                    case '--comments':
                    case '--labels':
                    case '--jumps':
                    case '--fwjumps':
                    case '--backjumps':
                    case '--badjumps':
                            $config[$path][] = trim($arg, "-");
                            break;
                    
                    default:
                        $path = Self::processsFileArg($config, $arg);
                        break;
                }
            }
            if (Self::arrayHasDuplicities($config))
            {
                exit(ReturnValues::OUTPUT_FILE_ERR);
            }
            return $config;

        }
    }
    static private function processsFileArg($config, $arg)
    {
        if(preg_match("/^--file=/", $arg))
        {
            $splitted = explode("=", $arg, 2);
            $file = $splitted[1];
            Self::checkFilePath($file);
            $config[$file] = array();
            return $file;
        }
        else
        {
            echo $arg , "\n";
            exit(ReturnValues::PARAMETER_ERR);
        }
    }

    static private function checkFilePath($path)
    {
        if (substr($path, -1) == "/" or is_dir($path) or !is_writeable(dirname($path)))
        {
            exit(ReturnValues::PARAMETER_ERR);
        }
    }

    static private function arrayHasDuplicities($array) 
    {
        return count($array) != count(array_unique($array));
    }
}

?>
