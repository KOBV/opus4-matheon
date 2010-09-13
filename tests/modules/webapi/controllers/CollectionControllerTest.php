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
 * @category    Tests
 * @package     Application
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Webapi_CollectionControllerTest extends ControllerTestCase {

    /**
     * ...
     */
    public function setUp() {
        // Needs initialization, because it's used in Controller_Rest.
        $_SERVER['HTTP_HOST'] = '127.0.0.1';

        parent::setUp();
    }

    /**
     * Helper to create collection to test with.
     *
     * TODO: Move to test fixture?
     */
    private function createDummyCollection($role_name) {
        $role = Opus_CollectionRole::fetchByName($role_name);

        if (is_null($role)) {
            $role = new Opus_CollectionRole();
            $role->setName($role_name)->store();
        }

        $collection_number = "test-number-" . rand();
        $collection_name = "test-name-" . rand();
        $collection = new Opus_Collection();
        $collection->setNumber($collection_number);
        $collection->setName($collection_name);
        $collection->setRoleId($role->getId());
        $collection->store();

        return $collection;
    }

    /**
     * @todo Implement testGetAction().
     */
    public function testGetAction() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );

    }

    /**
     * Create collection and check if we can update it...
     */
    public function testUpdateActionForExistingCollection() {
        $role_name  = "matheon_projects";
        $new_name   = "neuer Titel";
        $collection = $this->createDummyCollection($role_name);

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'role'  => $role_name,
                    'key'   => $collection->getNumber(),
                    'title' => $new_name,
                ));
        $this->dispatch('/webapi/collection/update');

//        echo $this->getResponse()->getBody();

        $this->assertResponseCode(200);
        $this->assertController('collection');
        $this->assertAction('update');

        $collection = new Opus_Collection( $collection->getId() );
        $this->assertStringStartsWith($new_name, $collection->getName());
    }

    /**
     * Test if we handline non-existing collections properly.
     */
    public function testUpdateActionForNonExistingCollection() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'role'  => 'foo',
                    'key'   => 'Axxx',
                    'title' => 'neuer Titel',
                ));
        $this->dispatch('/webapi/collection/update');

        echo $this->getResponse()->getHttpResponseCode();
        echo $this->getResponse()->getBody();

        $this->assertResponseCode(500);
        $this->assertController('collection');
        $this->assertAction('update');
    }

}

?>
