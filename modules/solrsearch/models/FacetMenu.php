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
 * @package     Module_Solrsearch
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 * @deprecated
 */
class Solrsearch_Model_FacetMenu {

    /**
     * Determines the number of facet-values to be depicted.
     * @return $facetLimit[$facetName] = number of values to be shown.
     */
    public function getFacetLimitsFromConfig() {
	    return Opus_Search_Config::getFacetLimits();
    }

    /**
     * Resolves the facet-options from URL and builds a result array with the number of facets to display.
     * @return array result[facet_name] = number
     */
    public function buildFacetArray($paramSet) {
	    return Opus_Search_Facet_Set::getFacetLimitsFromInput( $paramSet );
        $limit = 10000;
        $facetArray = array();
        if (isset($paramSet['facetNumber_author_facet'])) {
            $facetArray['author_facet'] = $limit;
        }
        if (isset($paramSet['facetNumber_year'])) {
            if (in_array('year_inverted', Opus_Search_Config::getFacetFields() )) {
                // 'year_inverted' is used in framework and result is returned as 'year'
                $facetArray['year_inverted'] = $limit;
            }
            $facetArray['year'] = $limit;
        }
        if (isset($paramSet['facetNumber_doctype'])) {
            $facetArray['doctype'] = $limit;
        }
        if (isset($paramSet['facetNumber_language'])) {
            $facetArray['language'] = $limit;
        }
        if (isset($paramSet['facetNumber_subject'])) {
            $facetArray['subject'] = $limit;
        }
        if (isset($paramSet['facetNumber_institute'])) {
            $facetArray['institute'] = $limit;
        }

        if (count($facetArray) == 0) {
            return null;
        }
        else {
            return $facetArray;
        }
    }

}
