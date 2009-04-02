<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category   Application
 * @package    Module_Webapi
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * Loads a list of document types or present a specific type
 */
class Doctypes {

    /**
     * Holds path to xml doctype files.
     *
     * @var string
     */
    private $__xml_path = '';

    /**
     * Separator for default values
     *
     * @var string
     */
    private $__value_separator = ';';

    /**
     * Looks in a specific directory for xml files.
     *
     * @return array
     */
    protected function _getXmlDocTypeFiles() {
        $result = array();
        if (false !== ($dirhandle = @opendir($this->__xml_path))) {
            while (false !== ($file = readdir($dirhandle))) {
                $path_parts = pathinfo($file);
                $filename = $path_parts['filename'];
                $basename = $path_parts['basename'];
                $extension = $path_parts['extension'];
                if (($basename === '.') or ($basename === '..') or ($extension !== 'xml')) {
                    continue;
                }
                $result[$filename] = $filename;
            }
            closedir($dirhandle);
            asort($result);
        }
        return $result;
    }

    /**
     * Convert a Opus_Model to a xml structure.
     *
     * @param Opus_Model_Abstract $model     Document model.
     * @param DOMDocument         $xml       XML dom object
     * @param DOMElement          $container XML element dom object
     * @return void
     */
    protected function _convertModelForWebapi(Opus_Model_Abstract $model, DOMDocument $xml, DOMElement $container) {
        // iterate over every field of a document type
        foreach ($model->describe() as $fieldname) {
            $field = $model->getField($fieldname);
            $modelClass = $field->getValueModelClass();
            $fieldxml = $this->_setFieldInfo($field, $xml);
            if (false === empty($modelClass)) {
                $this->_convertModelForWebapi(new $modelClass, $xml, $fieldxml);
            }
            $container->appendChild($fieldxml);
        }
    }

    /**
     * Set field specific informations.
     *
     * @param Opus_Model_Field $field Document model.
     * @param DOMDocument      $xml   XML dom object
     * @return DOMElement
     */
    protected function _setFieldInfo(Opus_Model_Field $field, DOMDocument $xml) {
        //
        $selection = $field->isSelection();
        $checkbox = $field->isCheckbox();
        $textarea = $field->isTextarea();
        $htmltype = 'Text';
        if (true === $selection) {
            $htmltype = 'Selection';
        } else if (true === $textarea) {
            $htmltype = 'Textarea';
        } else if (true === $checkbox) {
            $htmltype = 'Checkbox';
        }

        $result = $xml->createElement($field->getName());
        if (true === $field->isMandatory()) {
            $mandatory = 'true';
        } else {
            $mandatory = 'false';
        }
        $result->setAttribute('mandatory', $mandatory);
        $result->setAttribute('multiplicity', $field->getMultiplicity());
        $result->setAttribute('htmlType', $htmltype);
        $defaults = $field->getDefault();
        if (true === is_array($defaults)) {
            if (false === is_object(current($defaults))) {
                $defaults = implode($this->__value_separator, $defaults);
            } else {
                $defaults = '';
            }
        }
        $result->setAttribute('default_values', $defaults);

        return $result;
    }

    /**
     * Constructor of class. Do some initalizing stuff.
     *
     * @param string $path (Optional) Path to document type xml files.
     */
    public function __construct($path = null) {
        if (true === empty($path)) {
            // TODO Do not use a hardcoded path to xml files
            $this->__xml_path = '../config/xmldoctypes/';
        } else {
            $this->__xml_path = $path;
        }
    }

    /**
     * Returns a xml structur for a specific Opus Document.
     * If type is not available a error will be returned.
     *
     * @param string $typename Name of type.
     * @return string
     */
    public function getType($typename) {

        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;

        $filename = $this->__xml_path . $typename . '.xml';
        if (true === file_exists($filename)) {
            $type = new Opus_Document_Type($filename);
            $document = new Opus_Document(null, $type);
            $docxml = $xml->createElement('Document');
            $docxml->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
            $docxml->setAttribute('value_separator', $this->__value_separator);
            $xml->appendChild($docxml);
            $this->_convertModelForWebapi($document, $xml, $docxml);
        } else {
            $error_element = $xml->createElement('Error', 'Requested type is not available!');
            $xml->appendChild($error_element);
        }
        return $xml->saveXML();;
    }

    /**
     * Returns a list of all available document types.
     *
     * @return string
     */
    public function getAllTypes() {
        //
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;

        $typesList = $xml->createElement('TypesList');
        $typesList->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $xml->appendChild($typesList);

        $types = $this->_getXmlDocTypeFiles();
        $view = Zend_Layout::getMvcInstance()->getView();
        $url = $view->url(array('controller' => 'doctypes', 'module' => 'webapi'), 'default', true);
        foreach ($types as $type) {
            $entry = $xml->createElement('Type', $type);
            $entry->setAttribute('xlink:href', $url . '/' . $type);
            $typesList->appendChild($entry);
        }
        return $xml->saveXML();
    }
}