<?php

/**
 * This file is used specifically to look up drug names when
 * writing a prescription. See the file:
 * templates/prescription/general_edit.html
 * for additional information
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jason Morrill <jason@italktech.net>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008 Jason Morrill <jason@italktech.net>
 * @copyright Copyright (c) 2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}
// will never be both
$is_rxnorm = $_GET['use_rxnorm'] == "true";
$is_rxcui = $_GET['use_rxcui'] == "true";

if (isset($_GET['term'])) {
    $return_arr = [];
    $term = filter_input(INPUT_GET, "term");
    if ($is_rxnorm) {
        $sql = "SELECT `str` as name, `RXCUI` as `rxnorm` FROM `rxnconso` WHERE `SAB` = 'RXNORM' AND `str` LIKE ? GROUP BY `RXCUI` ORDER BY `str` LIMIT 100";
    } elseif ($is_rxcui) {
        $sql = "SELECT `code_text` as name, `code` as rxnorm FROM `codes` WHERE `code_text` LIKE ? AND `code_type` = ? GROUP BY `code` ORDER BY `code_text` LIMIT 100";
    } else {
        // Search from Inventory Management (drugs table) with fuzzy matching
        // Support searching for drugs like "drug" matching "drug1", "drug2", "drug3"
        $sql = "SELECT `drug_id`, `name`, `ndc_number`, `form`, `size`, `unit`, `drug_code` as rxnorm 
                FROM `drugs` 
                WHERE `active` = 1 AND `name` LIKE ? 
                ORDER BY `name` LIMIT 100";
    }
    // Use %term% for fuzzy search instead of term% for prefix-only search
    $val = ['%' . $term . '%'];
    if ($is_rxcui) {
        $code_type = sqlQuery("SELECT ct_id FROM `code_types` WHERE `ct_key` = ? AND `ct_active` = 1", ['RXCUI']);
        $val = [$term . '%', $code_type['ct_id']];
        if (empty($code_type['ct_id'])) {
            throw new \Exception(xlt('Install RxCUI monthly via Native Load or enable in Lists!'));
        }
    }
    $res = sqlStatement($sql, $val);
    while ($row = sqlFetchArray($res)) {
        // Build display name with drug details
        $display_parts = [text($row['name'])];
        
        // Add size and unit if available
        if (!empty($row['size']) && !empty($row['unit'])) {
            $display_parts[] = $row['size'] . $row['unit'];
        } elseif (!empty($row['size'])) {
            $display_parts[] = $row['size'];
        }
        
        // Add form if available
        if (!empty($row['form']) && $row['form'] != '0') {
            // Get form name from list_options if possible
            $form_name = $row['form'];
            $form_query = sqlQuery("SELECT title FROM list_options WHERE list_id = 'drug_form' AND option_id = ?", [$row['form']]);
            if ($form_query) {
                $form_name = $form_query['title'];
            }
            $display_parts[] = "(" . $form_name . ")";
        }
        
        // Add NDC number if available
        if (!empty($row['ndc_number'])) {
            $display_parts[] = "[" . $row['ndc_number'] . "]";
        }
        
        $display_name = implode(' ', $display_parts);
        
        $return_arr[] = [
            'display_name' => $display_name,
            'id_name' => text($row['name']),
            'rxnorm' => text($row['rxnorm'] ?? ''),
            'drug_id' => $row['drug_id'] ?? null
        ];
    }

    /* Toss back results as json encoded array. */
    echo json_encode($return_arr);
}
