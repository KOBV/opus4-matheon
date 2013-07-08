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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit Tests für MulitSubForm Formular das mehrere Unterformular des gleichen Typs verwalten kann.
 */
class Admin_Form_DocumentMultiSubFormTest extends ControllerTestCase {
    
    public function testConstructForm() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentIdentifier', 'Identifier');
        
        $this->assertNotNull($form->getElement('Add'));
        $this->assertNotNull($form->getLegend());
        $this->assertEquals($form->getLegend(), 'admin_document_section_identifier');
    }
    
    public function testConstructFormWithValidator() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleParent', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $this->assertNotNull($form->getElement('Add'));
        $this->assertNotNull($form->getLegend());
        $this->assertEquals($form->getLegend(), 'admin_document_section_titleparent');
    }
    
    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage Validator ist keine Instanz von Form_Validate_IMultiSubForm.
     */
    public function testConstructFormWithBadValidator() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleParent', 
                'NotAValidClass');
    }
    
    public function testPopulateFromModel() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleSub', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $document = new Opus_Document(146);
        
        $form->populateFromModel($document);
        
        $this->assertEquals(2, count($form->getSubForms()), 'Formular sollte zwei Unterformulare (2 Untertitel) haben.');
        
        $form1 = $form->getSubForm('TitleSub0');
        
        $this->assertEquals('deu', $form1->getElementValue('Language'));
        $this->assertEquals('Service-Zentrale', $form1->getElementValue('Value'));

        $form2 = $form->getSubForm('TitleSub1');
        
        $this->assertEquals('eng', $form2->getElementValue('Language'));
        $this->assertEquals('Service Center', $form2->getElementValue('Value'));
    }
    
    public function testPopulateFromModelWithEmptyModel() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleSub', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $document = new Opus_Document();
        
        $form->populateFromModel($document);
        
        $this->assertEquals(0, count($form->getSubForms()), 'Formular sollte keine Unterformulare haben.');
    }
    
    public function testGetFieldValues() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleSub', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $document = new Opus_Document(146);
        
        $values = $form->getFieldValues($document);
        
        $this->assertEquals(2, count($values));
        $this->assertTrue($values[0] instanceof Opus_Title);
        $this->assertEquals('sub', $values[0]->getType());
    }
    
    public function testContructFromPost() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleParent', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $post = array(
            'TitleParent0' => array(
                'Language' => 'deu',
                'Value' => 'Titel 1'
            ),
            'TitleParent1' => array(
                'Language' => 'eng',
                'Value' => 'Titel 2'
            ),
            'TitleParen2' => array(
                'Language' => 'fra',
                'Value' => 'Titel 3'
            )
        );
        
        $this->assertEquals(0, count($form->getSubForms()));
        
        $form->constructFromPost($post);
        
        $this->assertEquals(3, count($form->getSubForms()));
        $this->assertNotNull($form->getSubForm('TitleParent0'));
        $this->assertNotNull($form->getSubForm('TitleParent1'));
        $this->assertNotNull($form->getSubForm('TitleParent2'));
    }
    
    public function testProcessPostAdd() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleParent', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $post = array('Add' => 'Hinzufügen');
        
        $this->assertEquals(0, count($form->getSubForms()));
        
        $this->assertEquals(Admin_Form_Document::RESULT_SHOW, $form->processPost($post, $post));
        
        $this->assertEquals(1, count($form->getSubForms()));
        $this->assertNotNull($form->getSubForm('TitleParent0'));
        
        $form->getSubForm('TitleParent0')->getElement('Value')->setValue('Titel 1');
        
        $this->assertEquals(Admin_Form_Document::RESULT_SHOW, $form->processPost($post, $post));
        
        $this->assertEquals(2, count($form->getSubForms()));
        $this->assertNotNull($form->getSubForm('TitleParent0'));
        $this->assertNotNull($form->getSubForm('TitleParent1'));

        // Prüfen, dass neues Formular als zweites (letztes) hinzugefügt wurde
        $this->assertEquals('Titel 1', $form->getSubForm('TitleParent0')->getElementValue('Value'));
        $this->assertNull($form->getSubForm('TitleParent1')->getElementValue('Value'));
    }
    
    public function testProcessPostRemove() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleSub', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $document = new Opus_Document(146);
        
        $form->populateFromModel($document);
        
        $this->assertEquals(2, count($form->getSubForms()));
        $this->assertEquals('Service Center', $form->getSubForm('TitleSub1')->getElementValue('Value'));
        
        $post = array(
            'TitleSub0' => array(
                'Remove' => 'Entfernen'
            ),
            'TitleSub1' => array(
            )
        );
        
        $this->assertEquals(Admin_Form_Document::RESULT_SHOW, $form->processPost($post, $post));
        
        $this->assertEquals(1, count($form->getSubForms()));
        $this->assertNotNull($form->getSubForm('TitleSub0')); // Aus TitleSub1 wird TitleSub0
        $this->assertEquals('Service Center', $form->getSubForm('TitleSub0')->getElementValue('Value'));
        $this->assertNotNull($form->getSubForm('TitleSub0')->getDecorator('CurrentAnker'));
    }
    
    public function testUpdateModel() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleSub', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());

        $form->appendSubForm();
        $form->getSubForm('TitleSub0')->getElement('Language')->setValue('deu');
        $form->getSubForm('TitleSub0')->getElement('Value')->setValue('Titel 1');
        
        $form->appendSubForm();
        $form->getSubForm('TitleSub1')->getElement('Language')->setValue('eng');
        $form->getSubForm('TitleSub1')->getElement('Value')->setValue('Title 2');
        
        $document = new Opus_Document();
        
        $form->updateModel($document);
        
        $titles = $document->getTitleSub();
        
        $this->assertEquals(2, count($titles));
        $this->assertEquals('deu', $titles[0]->getLanguage());
        $this->assertEquals('Titel 1', $titles[0]->getValue());
        $this->assertEquals('eng', $titles[1]->getLanguage());
        $this->assertEquals('Title 2', $titles[1]->getValue());
    }
    
    public function testGetSubFormModels() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleSub', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $document = new Opus_Document(146);
        
        $form->populateFromModel($document);
        $form->appendSubForm();
        $form->getSubForm('TitleSub2')->getElement('Language')->setValue('fra');
        $form->getSubForm('TitleSub2')->getElement('Value')->setValue('Titel 3');
        
        $titles = $form->getSubFormModels();
        
        $this->assertEquals(3, count($titles));
        $this->assertNotNull($titles[0]->getId());
        $this->assertNotNull($titles[1]->getId());
        $this->assertNull($titles[2]->getId()); // Neuer Titel noch nicht gespeichert (ohne ID)
    }
    
    public function testCreateSubForm() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleSub', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $subform = $form->createSubForm();
        
        $this->assertNotNull($subform);
        $this->assertNotNull($subform->getElement('Remove'));
    }
    
    public function testCreateNewSubFormInstance() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleSub', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $subform = $form->createNewSubFormInstance();
        
        $this->assertNotNull($subform);
        $this->assertTrue($subform instanceof Admin_Form_DocumentTitle);
    }
    
    /**
     * Bei diesem Test geht es nur um die Ermittlung des richtigen Unterformulars für den Anker. Beim Test werden keine
     * Unterformulare entfernt.
     */
    public function testDetermineSubFormForAnker() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleSub', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $document = new Opus_Document(146);
        
        $this->assertEquals($form, $form->determineSubFormForAnker(0));

        $form->populateFromModel($document);
        
        $this->assertEquals('TitleSub0', $form->determineSubFormForAnker(0)->getName());
        $this->assertEquals('TitleSub1', $form->determineSubFormForAnker(1)->getName());
        $this->assertEquals('TitleSub1', $form->determineSubFormForAnker(2)->getName()); // letztes Subform entfernt
    }
    
    public function testIsValidTrue() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleParent', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $form->appendSubForm();
        $form->appendSubForm();
        
        $post = array(
            'TitleParent0' => array(
                'Language' => 'deu',
                'Value' => 'Titel 1'
            ),
            'TitleParent1' => array(
                'Language' => 'eng',
                'Value' => 'Title 2'
            )
        );
                
        $this->assertTrue($form->isValid($post, $post));
    }
    
    public function testIsValidFalse() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentTitle', 'TitleParent', 
                new Form_Validate_MultiSubForm_RepeatedLanguages());
        
        $form->appendSubForm();
        $form->appendSubForm();
        
        $post = array(
            'Parent' => array(
                'TitleParent0' => array(
                    'Language' => 'deu',
                    'Value' => 'Titel 1'
                ),
                'TitleParent1' => array(
                    'Language' => 'deu',
                    'Value' => 'Titel 2'
                )
            )
        );
                
        $this->assertFalse($form->isValid($post, $post));
    }
    
    public function testIsEmptyTrue() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentIdentifier', 'Identifier');
        
        $this->assertTrue($form->isEmpty());
    }
    
    public function testIsEmptyFalse() {
        $form = new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentIdentifier', 'Identifier');
        $form->appendSubForm();
        
        $this->assertFalse($form->isEmpty());
    }
    
}