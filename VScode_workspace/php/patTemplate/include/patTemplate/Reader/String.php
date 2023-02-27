<?php
/**
 * patTemplate Reader that reads from a string
 *
 * @package     patTemplate
 * @subpackage  Readers
 * @author      Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate Reader that reads from a string
 *
 * @package     patTemplate
 * @subpackage  Readers
 * @author      Stephan Schmidt <schst@php.net>
 */
class patTemplate_Reader_String extends patTemplate_Reader
{
    /**
     * Read templates from a string
     *
     * @final
     * @access   public
     * @param    string  string to parse
     * @param    array   options, not implemented in current versions, but future versions will allow passing of options
     * @return   array   templates
     */
    public function readTemplates($input, $options = array())
    {
        $this->_currentInput = $input;

        $templates  =   $this->parseString($input);

        return  $templates;
    }
}
