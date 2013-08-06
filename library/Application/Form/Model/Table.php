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
 */

/**
 * Formular für die Anzeige der Model-Tabelle (CRUD - indexAction).
 *
 * @category    Application
 * @package     Application_Form_Model
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Application_Form_Model_Table extends Application_Form_Abstract {

    /**
     * Modelle die angezeigt werden sollen.
     * @var array
     */
    private $models = null;

    /**
     * Konfiguration für Spalten.
     * @var array
     */
    private $columns = null;

    /**
     * Initialisiert Formular.
     *
     * Setzt Decorators so, daß das Rendering in einem View Script erfolgt.
     */
    public function init() {
        parent::init();

        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'modeltable.phtml'))
        ));
    }

    /**
     * Liefert die Spaltenkonfiguration.
     * @return array|null
     */
    public function getColumns() {
        return $this->columns;
    }

    /**
     * Setzt die Spaltenkonfiguration.
     * @param $columns
     */
    public function setColumns($columns) {
        $this->columns = $columns;
    }

    /**
     * Liefert das Label für eine Spalte.
     * @param $index Index der Spalte angefangen bei 0
     * @return string|null
     */
    public function getColumnLabel($index) {
        if (isset($this->columns[$index]['label'])) {
            return $this->columns[$index]['label'];
        }
        else {
            return null;
        }
    }

    /**
     * Liefert gesetzte Modelle.
     * @return array|null
     */
    public function getModels() {
        return $this->models;
    }

    /**
     * Setzt Modelle für Anzeige.
     * @param $models
     */
    public function setModels($models) {
        if (!is_null($models) && !is_array($models)) {
            throw new Application_Exception(__METHOD__ . 'Parameter must be array.');
        }
        $this->models = $models;
    }

}

