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
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Model_Languages extends Admin_Model_AbstractModel {


    /**
     * Checks if a field value with a specific language already exists for
     * document.
     *
     * @param Opus_Document $doc
     * @param string $fieldName Name of field (e.g. TitleMain)
     * @param string $language Value for language (e.g. 'deu', 'eng')
     * @return boolean TRUE - if the language has already been used
     */
    public function isLanguageUsed($doc, $fieldName, $language) {
        $field = $doc->getField($fieldName);

        $values = $field->getValue();

        if (!empty($values)) {
            foreach ($values as $index => $value) {
                if ($value instanceof Opus_Model_Abstract) {
                    $lang = $value->getLanguage();
                    if ($lang === $language) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

}