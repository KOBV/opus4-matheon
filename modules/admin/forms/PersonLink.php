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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Formular für die Felder von Opus_Model_Dependent_Link_DocumentPerson.
 * 
 * Das sind folgende Felder.
 * 
 * - Role
 * - AllowEmailContact
 * - SortOrder
 */
class Admin_Form_PersonLink extends Admin_Form_AbstractDocumentSubForm {
    
    /**
     * Name fuer Formularelement fuer Feld AllowEmailContact.
     */
    const ELEMENT_ALLOW_CONTACT = 'AllowContact';
    
    /**
     * Name fuer Formularelement fuer Feld Role.
     */
    const ELEMENT_ROLE = 'Role';
    
    /**
     * Name fuer Formularelement fuer Feld SortOrder.
     */
    const ELEMENT_SORT_ORDER = 'SortOrder';
    
    private $model = null;
    
    /**
     * Erzeugt die Formularelemente.
     */
    public function init() {
        parent::init();
        
        $this->addElement('hidden', Admin_Form_Person::ELEMENT_PERSON_ID);
        $this->addElement('checkbox', self::ELEMENT_ALLOW_CONTACT, array('label' => 'AllowEmailContact'));
        $this->addElement('text', self::ELEMENT_SORT_ORDER, array('label' => 'SortOrder'));
        $this->addElement('PersonRole', self::ELEMENT_ROLE, array('label' => 'Role'));
    }
    
    public function populateFromModel($personLink) {
        if ($personLink instanceof Opus_Model_Dependent_Link_DocumentPerson) {
            $this->getElement(self::ELEMENT_ALLOW_CONTACT)->setValue($personLink->getAllowEmailContact());
            $this->getElement(self::ELEMENT_SORT_ORDER)->setValue($personLink->getSortOrder());
            $this->getElement(Admin_Form_Person::ELEMENT_PERSON_ID)->setValue($personLink->getModel()->getId());
            $role = $personLink->getRole();
            // $this->removeElement('Role'. ucfirst($role));
            $this->model = $personLink;
        }
        else {
            $this->getLog()->err('populateFromModel called with object that is not instance of '
                    . 'Opus_Model_Dependent_Link_DocumentPerson');
        }
    }
    
    public function getModel() {
        return $this->model;
    }
    
}