<?php
/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */

require_once __DIR__. '/ReturnValues.php';

/**
 * Class for command line argument processing.
 */
final class CLAProcessor
{
    /**
     * Process commmand line arguments.
     *   --help - prints help to standard output
     *   statp arguments - returns configuration 2D array,
     *                     where keys of outer array elements contains stats output files
     *                     and inner array elements values contain statnames to write into the file
     *   no arguments - returns null
     *   invalid arguments - parameter error 
     * @param $argc - number of comman line arguments
     * @param $argv - array of command line arguments
     */
    static function getConfig($argc, $argv)
    {
        if ($argc == 1)
        {
            return null;
        }
        else if (in_array("--help", $argv))
        {
            if($argc == 2)
            {
                Self::printHelp();
                exit(ReturnValues::SUCCESS);
            }
            else
            {
                fwrite(STDERR, "PARAMETER ERROR: '--help' can't be used with any other option\n");
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
            return $config;

        }
    }

    /**
     * Prints help for parse.php.
     */
    private function printHelp()
    {
        echo "Usage: php8.1 parse.php \n";
        echo "Reads IPPcode22 source code from standard input, checks its lexical and syntactic correctness and prints its XML representation to standard output.\n";
        echo "\n";
        echo "STATP options:\n";
        echo "  --stats=file   output file filepath for stats specified as options after this option,\n";
        echo " stats:\n";
        echo "   --loc        number of lines of code \n"; 
        echo "   --comments   number of lines with comments \n"; 
        echo "   --labels     number of labels \n"; 
        echo "   --jumps      number of all jumps and returns from calls\n"; 
        echo "   --backjumps  number of backwards jumps\n"; 
        echo "   --fwjumps    number of forwards jumps\n"; 
        echo "   --badjumps   number of bad jumps\n"; 
    }
    /**
     * Process Statp stats option CL argument. Parse its path, check if its valid,
     * save it into configuration array and return the file path.
     * If argument format or filepath is invalid, exits with PARAMETER ERROR.
     * @param array $config - confugiration array
     * @param string $arg - CL argument with stats option 
     * @return string statp output file filepath
     */
    static private function processsFileArg(&$config, $arg)
    {
        if(preg_match("/^--stats=/", $arg))
        {
            $splitted = explode("=", $arg, 2);
            $file = $splitted[1];
            Self::checkFilePath($file);
            if(array_key_exists($file, $config))
            {
                fwrite(STDERR, "OUTPUT FILE ERROR: multiple STATP '--stats=file' can't contain identical filepath\n");
                exit(ReturnValues::OUTPUT_FILE_ERR);
            }
            $config[$file] = array();
            return $file;
        }
        else
        {
            fwrite(STDERR, "PARAMETER ERROR: unexpected argument. Check arguments format and order.\n");
            exit(ReturnValues::PARAMETER_ERR);
        }
    }

    /**
     * Check whether path represents a file and its parent directory is writable.
     * if not, exits with OUTPUT FILE ERROR (see ReturnValues.php)
     */
    static private function checkFilePath($path)
    {
        if (substr($path, -1) == "/" or is_dir($path) or !is_writable(dirname($path)))
        {
            fwrite(STDERR, "OUTPUT FILE ERROR: STATP '--stats=file' file filepath is invalid.\n");
            exit(ReturnValues::OUTPUT_FILE_ERR);
        }
    }
}

?>
