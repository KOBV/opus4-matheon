<?php
/*
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
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
 * @category    Application
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular für Actionbox für Metadaten-Formular.
 * 
 * Die Actiobox zeigt wichtige Statusinformationen zu einem Dokument und bietet
 * Navigation und direkten Zugang zu Funktionen wie Speichern und Abbrechen.
 */
class Admin_Form_ActionBox extends Admin_Form_AbstractDocumentSubForm {
    
    const ELEMENT_SAVE = 'Save';
    
    const ELEMENT_CANCEL = 'Cancel';
    
    private $document;

    public function init() {
        parent::init();
        
        $element = new Zend_Form_Element_Submit(self::ELEMENT_SAVE);
        $element->setValue('save');
        $element->removeDecorator('DtDdWrapper');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit(self::ELEMENT_CANCEL);
        $element->setValue('cancel');
        $element->removeDecorator('DtDdWrapper');
        $this->addElement($element);
    }
    
    public function populateFromModel($document) {
        $this->document= $document;
    }
    
    public function constructFromPost($post, $document = null) {
        $this->document = document;
    }
    
    public function getDocument() {
        return $this->document;
    }
    
    /**
     * 
     * @return string
     * 
     * TODO Über clevere Lösung zum automatischen Generieren aus der Formularstruktur nachdenken.
     */
    public function getJumpLinks() {
        $links = array();
        
        $links['#fieldset-General'] = 'Allgemeines';
        $links['#fieldset-Persons'] = 'Personen';
        $links['#fieldset-Titles'] = 'Titles';
        $links['#fieldset-Series'] = 'Schriftenreihe';
        $links['#fieldset-Enrichments'] = 'Erweiterung';
        $links['#fieldset-Collections'] = 'Collection';
        $links['#fieldset-Identifiers'] = 'Identifier';
        $links['#fieldset-Licences'] = 'Lizenzen';
        $links['#fieldset-Patents'] = 'Patentinformationen';
        $links['#fieldset-Notes'] = 'Bemerkung';
        
        return $links;
    }
    
    public function loadDefaultDecorators() {
        $this->setDecorators(array(array(
            'ViewScript', array('viewScript' => 'actionbox.phtml'))));
    }
    
}
