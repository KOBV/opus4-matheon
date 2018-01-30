<?php
/**
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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller handles transitions of documents between states.
 */
class Admin_WorkflowController extends Application_Controller_Action {

    /**
     * Helper for verifying document IDs.
     * @var Application_Controller_Action_Helper_Documents
     */
    private $_documentsHelper;

    /**
     * Helper for workflow functionality.
     * @var Application_Controller_Action_Helper_Workflow
     */
    private $_workflowHelper;

    private $_confirmChanges = true;

    /**
     * Initializes controller.
     */
    public function init() {
        parent::init();
        $this->_documentsHelper = $this->_helper->getHelper('Documents');
        $this->_workflowHelper = $this->_helper->getHelper('Workflow');

        $config = $this->getConfig();

        if (isset($config->confirmation->document->statechange->enabled)) {
            $this->_confirmChanges = ($config->confirmation->document->statechange->enabled == 1) ? true : false;
        }
        else {
            $this->_confirmChanges = true;
        }
    }

    /**
     * Switches the status of a document to target state.
     */
    public function changestateAction() {
        $docId = $this->getRequest()->getParam('docId');
        $targetState = $this->getRequest()->getParam('targetState');

        $document = $this->_documentsHelper->getDocumentForId($docId);

        // Check if document identifier is valid
        if (!isset($document)) {
            return $this->_helper->Redirector->redirectTo(
                'index', array('failure' => $this->view->translate(
                    'admin_document_error_novalidid'
                )), 'documents', 'admin'
            );
        }

        // Check if valid target state
        if (!$this->_workflowHelper->isValidState($targetState)) {
            return $this->_helper->Redirector->redirectTo(
                'index', array('failure' => $this->view->translate(
                    'admin_workflow_error_invalidstate'
                )), 'document', 'admin', array('id' => $docId)
            );
        }

        // Check if allowed target state
        if (!$this->_workflowHelper->isTransitionAllowed($document, $targetState)) {
            return $this->_helper->Redirector->redirectTo(
                'index', array('failure' => $this->view->translate(
                    'admin_workflow_error_illegal_transition', $targetState
                )), 'document', 'admin', array('id' => $docId)
            );
        }

        // Check if document is already in target state
        if ($document->getServerState() === $targetState) {
            // if defined used custom message for state, other use common key
            $key = 'admin_workflow_error_already_' . $targetState;
            if (!$this->view->translate()->getTranslator()->isTranslated($key)) {
                $key = 'admin_workflow_error_alreadyinstate';
            }
            return $this->_helper->Redirector->redirectTo(
                'index', array('failure' => $this->view->translate($key, $targetState)),
                'document', 'admin', array('id' => $docId)
            );
        }

        if ($this->_confirmChanges) {
            if ($this->getRequest()->isPost()) {
                $form = $this->_getConfirmationForm($document, $targetState);
                $sureyes = $this->getRequest()->getPost('sureyes');
                if ($form->isValid($this->getRequest()->getPost()) && isset($sureyes) === true) {
                    return $this->_changeState($document, $targetState, $form);
                }
                return $this->_helper->Redirector->redirectTo(
                    'index', null, 'document', 'admin', array('id' => $docId)
                );
            }

            // show confirmation page
            $this->view->documentAdapter = new Application_Util_DocumentAdapter($this->view, $document);
            $this->view->title = $this->view->translate('admin_workflow_' . $targetState);
            $this->view->text = $this->view->translate('admin_workflow_' . $targetState . '_sure', $docId);
            $this->view->form = $this->_getConfirmationForm($document, $targetState);
        }
        else {
            return $this->_changeState($document, $targetState);
        }
    }

    private function _changeState($document, $targetState, $form = null) {
        try {
            $this->_workflowHelper->changeState($document, $targetState);

            if ($targetState == 'published') {
                $this->_sendNotification($document, $form);
            }
        }
        catch (Exception $e) {
            return $this->_helper->Redirector->redirectTo(
                'index', array('failure' => $e->getMessage()), 'documents', 'admin'
            );
        }

        $key = 'admin_workflow_' . $targetState . '_success';
        if (!$this->view->translate()->getTranslator()->isTranslated($key)) {
            $key = 'admin_workflow_success';
        }
        $message = $this->view->translate($key, $document->getId(), $targetState);

        if ($targetState === 'removed') {
            return $this->_helper->Redirector->redirectTo('index', $message, 'documents', 'admin');
        }
        return $this->_helper->Redirector->redirectTo(
            'index', $message, 'document', 'admin', array('id' => $document->getId())
        );
    }

    private function _sendNotification($document, $form = null) {
        $notification = new Application_Util_Notification();
        $url = $this->view->url(
            array(
                "module" => "frontdoor",
                "controller" => "index",
                "action" => "index",
                "docId" => $document->getId()
            ),
            null,
            true
        );

        $authorsBitmask = array();
        $notifySubmitter = true;

        if (!is_null($form)) {
            foreach ($form->getValues() as $key => $val) {
                $pos = strpos($key, 'author_');
                if ($pos !== false && $pos === 0) {
                    array_push($authorsBitmask, $val == '1');
                }
            }
            $notifySubmitter = $form->getValue('submitter') == '1';
        }

        $notification->prepareMail(
            $document,
            Application_Util_Notification::PUBLICATION,
            $this->view->serverUrl() . $url,
            $notifySubmitter,
            $authorsBitmask
        );
    }

    /**
     * Returns form for asking yes/no question like 'Delete file?'.
     *
     * @param Opus_Document $document
     * @param string $action Target action that needs to be confirmed
     * @return Admin_Form_YesNoForm
     */
    private function _getConfirmationForm($document, $targetState) {
        $form = new Admin_Form_YesNoForm();
        $form->setAction(
            $this->view->url(
                array('controller' => 'workflow', 'action' => 'changestate', 'targetState' => $targetState)
            )
        );
        $form->setMethod('post');

        $idElement = new Zend_Form_Element_Hidden('id');
        $idElement->setValue($document->getId());
        $form->addElement($idElement);

        $config = Zend_Registry::get('Zend_Config');
        if ($targetState == 'published' && isset($config->notification->document->published->enabled)
            && $config->notification->document->published->enabled == 1) {
            $this->_addPublishNotificationSelection($document, $form);
        }
        return $form;
    }

    /**
     * add a checkbox for each PersonSubmitter and PersonAuthor (used to select
     * recipients for publish notification email)
     *
     * @param Opus_Document $document
     * @param Zend_Form $form
     *
     */
    private function _addPublishNotificationSelection($document, $form) {
        $form->addElement(
            'hidden', 'plaintext',
            array(
                'description' => '<br/><p><strong>' . $this->view->translate('admin_workflow_notification_headline')
                    . '</strong></p>'
                    . '<p>' . $this->view->translate('admin_workflow_notification_description') . '</p>',
                'ignore' => true,
                'decorators' => array(array('Description', array('escape' => false, 'tag' => '')))
            )
        );

        $submitters = $document->getPersonSubmitter();
        if (!is_null($submitters) && count($submitters) > 0) {
            $label = $this->view->translate('admin_workflow_notification_submitter') . ' '
                . trim($submitters[0]->getLastName()) . ", " . trim($submitters[0]->getFirstName());
            $element = null;
            if (trim($submitters[0]->getEmail()) == '') {
                // email notification is not possible since no email address is specified for submitter
                $label .= ' (' . $this->view->translate('admin_workflow_notification_noemail') . ')';
                $element = new Zend_Form_Element_Checkbox(
                    'submitter', array('checked' => false, 'disabled' => true,
                    'label' => $label)
                );
                $element->getDecorator('Label')->setOption('class', 'notification-option option-not-available');
            }
            else {
                $label .= ' (' . trim($submitters[0]->getEmail()) . ')';
                $element = new Zend_Form_Element_Checkbox('submitter', array('checked' => true, 'label' => $label));
                $element->getDecorator('Label')->setOption('class', 'notification-option');
            }
            $form->addElement($element);
        }

        $authors = $document->getPersonAuthor();
        if (!is_null($authors)) {
            $index = 1;
            foreach ($authors as $author) {
                $id = 'author_' . $index;
                $label = $index . '. ' . $this->view->translate('admin_workflow_notification_author') . ' '
                    . trim($author->getLastName()) . ", " . trim($author->getFirstName());
                $element = null;
                if (trim($author->getEmail()) == '') {
                    // email notification is not possible since no email address is specified for author
                    $label .= ' (' . $this->view->translate('admin_workflow_notification_noemail') . ')';
                    $element = new Zend_Form_Element_Checkbox(
                        $id, array('checked' => false, 'disabled' => true, 'label' => $label)
                    );
                    $element->getDecorator('Label')->setOption('class', 'notification-option option-not-available');
                }
                else {
                    $label .= ' (' . trim($author->getEmail()) . ')';
                    $element = new Zend_Form_Element_Checkbox(
                        $id, array('checked' => true, 'label' => 'foo', 'label' => $label)
                    );
                    $element->getDecorator('Label')->setOption('class', 'notification-option');
                }
                $form->addElement($element);
                $index++;
            }
        }
    }

}

