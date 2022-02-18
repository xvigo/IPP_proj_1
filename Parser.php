<?php

/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */

require_once __DIR__. '/IppXmlWriter.php';
require_once __DIR__. '/ReturnValues.php';

final class Parser
{
    private static $xml;

    static function parseAndPrintXML()
    {
        Self::$xml = new IppXmlWriter();
        $headerFound = false;

        while($line = fgets(STDIN))
        {
            $comment_splitted = explode("#", $line, 2); // separate comment from code
            $line = $comment_splitted[0]; // save code without comment

            $separated = preg_split("/[\n\t ]+/", $line, 0, PREG_SPLIT_NO_EMPTY); // separate opcode and operands
            
            if(empty($separated))
            { // skip empty lines
                continue;
            }

            if (!$headerFound)
            {
                if(strcasecmp($separated[0], ".IPPcode22") == 0)
                {
                    $headerFound = true;
                    Self::$xml->startDocument();
                    Self::$xml->startProgram();
                    continue;
                }
                else
                {
                    exit(ReturnValues::HEADER_ERR);
                }
            }

            $separated[0] = strtoupper($separated[0]);
            switch($separated[0])
            {
                // no operand
                case "CREATEFRAME":
                case "PUSHFRAME":
                case "POPFRAME":
                case "RETURN":
                case "BREAK":
                    Self::check_operands_count($separated, 1);
                    Self::$xml->startInstruction($separated[0]);
                    Self::$xml->endInstruction();
                    continue 2;
                
                // <var>
                case "DEFVAR":
                case "POPS":
                    Self::check_operands_count($separated, 2);
                    Self::$xml->startInstruction($separated[0]);
                    Self::parse_VAR($separated[1]);
                    Self::$xml->endInstruction();

                    continue 2;
                
                // <label>
                case "CALL":
                case "LABEL":
                case "JUMP":
                    Self::check_operands_count($separated, 2);
                    Self::$xml->startInstruction($separated[0]);
                    Self::parse_LABEL($separated[1]);
                    Self::$xml->endInstruction();

                    continue 2;
                
                // <symb>
                case "PUSHS":
                case "WRITE":
                case "EXIT":
                case "DPRINT":
                    Self::check_operands_count($separated, 2);
                    Self::$xml->startInstruction($separated[0]);
                    Self::parse_SYMB($separated[1]);
                    Self::$xml->endInstruction();

                    continue 2;
                
                // <var> <symb>
                case "MOVE":
                case "INT2CHAR":
                case "STRLEN":
                case "TYPE":
                    Self::check_operands_count($separated, 3);
                    Self::$xml->startInstruction($separated[0]);
                    Self::parse_VAR($separated[1]);
                    Self::parse_SYMB($separated[2]);
                    Self::$xml->endInstruction();

                    continue 2;

                // <var> <type>
                case "READ":
                    Self::check_operands_count($separated, 3);
                    Self::$xml->startInstruction($separated[0]);
                    Self::parse_VAR($separated[1]);
                    Self::parse_TYPE($separated[2]);
                    Self::$xml->endInstruction();

                    continue 2;

                // <var> <symb1> <symb2>
                case "ADD":
                case "SUB":
                case "MUL":
                case "IDIV":
                case "LT":
                case "GT":
                case "EQ":
                case "AND":
                case "OR":
                case "NOT":
                case "STRI2INT":
                case "CONCAT":
                case "GETCHAR":
                case "SETCHAR":
                    Self::check_operands_count($separated, 4);
                    Self::$xml->startInstruction($separated[0]);
                    Self::parse_VAR($separated[1]);
                    Self::parse_SYMB($separated[2]);
                    Self::parse_SYMB($separated[3]);
                    Self::$xml->endInstruction();

                    continue 2;

                // <label> <symb1> <symb2>
                case "JUMPIFEQ":
                case "JUMPIFNEQ":
                    Self::check_operands_count($separated, 4);
                    Self::$xml->startInstruction($separated[0]);
                    Self::parse_LABEL($separated[1]);
                    Self::parse_SYMB($separated[2]);
                    Self::parse_SYMB($separated[3]);
                    Self::$xml->endInstruction();

                    continue 2;
                // invalid command
                default:
                    exit(ReturnValues::OPCODE_ERR);

            } // switch end
        } // while end

        Self::$xml->endProgram();
        Self::$xml->endDocument();
        Self::$xml->xmlPrint();
    }

    // OPERANDS
    private const O_VAR = '/^(LF|TF|GF)@[a-zA-Z_\-$&%\*!\?][0-9a-zA-Z_\-$&%\*!\?]*$/';
    private const O_LABEL = "/^[a-zA-Z_\-$&%\*!\?][0-9a-zA-Z_\-$&%\*!\?]*$/";
    
    // VARIABLE TYPES
    private const T_INT = "/^int@[+-]?[0-9]+$/";
    private const T_STRING = '/^string@(\\\\[0-9]{3}|[^\s#\\\\])*$/u';
    private const T_BOOL = "/^bool@(true|false)$/";
    private const T_NIL = "/^nil@nil$/";
    
    private static function parse_VAR($operand)
    {
        if (preg_match(Self::O_VAR, $operand))
        {
            Self::$xml->addArgument('var', $operand);
        }
        else
        {
            exit(ReturnValues::OPCODE_ERR);
        }
    }

    private static function parse_SYMB($operand)
    {
        if (preg_match(Self::T_INT, $operand))
        {
            $separated = explode('@', $operand, 2);
            Self::$xml->addArgument('int', $separated[1]);
        }
        elseif (preg_match(Self::T_STRING, $operand))
        {
            $separated = explode('@', $operand, 2);
            Self::$xml->addArgument('string', $separated[1]);
        }
        elseif (preg_match(Self::T_BOOL, $operand))
        {
            $separated = explode('@', $operand, 2);
            Self::$xml->addArgument('bool', $separated[1]);
        }
        elseif (preg_match(Self::T_NIL, $operand))
        {
            $separated = explode('@', $operand, 2);
            Self::$xml->addArgument('nil', $separated[1]);
        }
        elseif (preg_match(Self::O_VAR, $operand))
        {
            Self::$xml->addArgument('var', $operand);
        }
        else
        {
            exit(ReturnValues::OPCODE_ERR);
        }
    }

    private static function parse_LABEL($operand)
    {
        if (preg_match(Self::O_LABEL, $operand))
        {
            Self::$xml->addArgument('label', $operand);
        }
        else
        {
            exit(ReturnValues::OPCODE_ERR);
        }
    }

    private static function parse_TYPE($operand)
    {
        switch($operand)
        {
            case "int":
            case "string":
            case "bool":
                Self::$xml->addArgument($operand, '');
                break;
                
            default:
                exit(ReturnValues::OPCODE_ERR);
        }
    }

    private static function check_operands_count($operands, $count)
    {
        if (count($operands) != $count)
        {
            exit(ReturnValues::OPCODE_ERR);
        }
    }
}
?>