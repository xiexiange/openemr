<?php

/**
 * new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// In mobile copy, ensure we load the root interface globals.php
require_once(__DIR__ . '/../../globals.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if ($GLOBALS['full_new_patient_form']) {
    require("new_comprehensive.php");
    exit;
}

// For a layout field return 0=unused, 1=optional, 2=mandatory.
function getLayoutUOR($form_id, $field_id)
{
    $crow = sqlQuery("SELECT uor FROM layout_options WHERE " .
    "form_id = ? AND field_id = ? LIMIT 1", [$form_id, $field_id]);
    return 0 + $crow['uor'];
}

// Determine if the registration date should be requested.
$regstyle = getLayoutUOR('DEM', 'regdate') ? "" : " style='display:none'";

$form_pubpid    = $_POST['pubpid'   ] ? trim((string) $_POST['pubpid'   ]) : '';
$form_title     = $_POST['title'    ] ? trim((string) $_POST['title'    ]) : '';
$form_fname     = $_POST['fname'    ] ? trim((string) $_POST['fname'    ]) : '';
$form_mname     = $_POST['mname'    ] ? trim((string) $_POST['mname'    ]) : '';
$form_lname     = $_POST['lname'    ] ? trim((string) $_POST['lname'    ]) : '';
$form_refsource = $_POST['refsource'] ? trim((string) $_POST['refsource']) : '';
$form_sex       = $_POST['sex'      ] ? trim((string) $_POST['sex'      ]) : '';
$form_refsource = $_POST['refsource'] ? trim((string) $_POST['refsource']) : '';
$form_dob       = $_POST['DOB'      ] ? trim((string) $_POST['DOB'      ]) : '';
$form_regdate   = $_POST['regdate'  ] ? trim((string) $_POST['regdate'  ]) : date('Y-m-d');
?>
<html>

<head>

<?php
    Header::setupHeader('datetime-picker');
    include_once($GLOBALS['srcdir'] . "/options.js.php");
?>

<style>
    /* H5：新增患者页面移动端优化 */
    @media (max-width: 768px) {
        body.body_top {
            padding: 0.5rem;
            font-size: 0.9rem;
        }

        .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        h2.page-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .form-group label {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .form-control,
        .form-control-sm {
            font-size: 0.9rem;
            padding: 0.5rem;
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .text-muted {
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
    }
</style>

<script>

 function validate() {
  var f = document.forms[0];
<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
  if (f.refsource.selectedIndex <= 0) {
   alert('Please select a referral source!');
   return false;
  }
<?php } ?>
<?php if (getLayoutUOR('DEM', 'sex') == 2) { ?>
  if (f.sex.selectedIndex <= 0) {
   alert('Please select a value for sex!');
   return false;
  }
<?php } ?>
<?php if (getLayoutUOR('DEM', 'DOB') == 2) { ?>
  if (f.DOB.value.length == 0) {
   alert('Please select a birth date!');
   return false;
  }
<?php } ?>
  top.restoreSession();
  return true;
 }

$(function () {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
    $('.datetimepicker').datetimepicker({
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>

</head>

<body class="body_top" onload="javascript:document.new_patient.fname.focus();">

<div class="container-fluid mt-3">
    <h2 class="page-title"><?php echo xlt('Add Patient Record'); ?></h2>

    <form name='new_patient' method='post' action="new_patient_save.php" onsubmit='return validate()' class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

        <?php if ($GLOBALS['omit_employers']) { ?>
           <input type='hidden' name='title' value='' />
        <?php } ?>

        <div class="row">
            <div class="col-12">
                <?php if (!$GLOBALS['omit_employers']) { ?>
                <div class="form-group mb-3">
                    <label for="title"><?php echo xlt('Title'); ?>:</label>
                    <select name='title' id="title" class="form-control form-control-sm">
                        <?php
                        $ores = sqlStatement("SELECT option_id, title FROM list_options " .
                        "WHERE list_id = 'titles' AND activity = 1 ORDER BY seq");
                        while ($orow = sqlFetchArray($ores)) {
                            echo "    <option value='" . attr($orow['option_id']) . "'";
                            if ($orow['option_id'] == $form_title) {
                                echo " selected";
                            }
                            echo ">" . text($orow['title']) . "</option>\n";
                        }
                        ?>
                    </select>
                </div>
                <?php } ?>

                <div class="form-group mb-3">
                    <label for="fname"><?php echo xlt('First Name'); ?>:</label>
                    <input type='text' class='form-control form-control-sm' name='fname' id='fname' value='<?php echo attr($form_fname); ?>' required />
                </div>

                <div class="form-group mb-3">
                    <label for="mname"><?php echo xlt('Middle Name'); ?>:</label>
                    <input type='text' class='form-control form-control-sm' name='mname' id='mname' value='<?php echo attr($form_mname); ?>' />
                </div>

                <div class="form-group mb-3">
                    <label for="lname"><?php echo xlt('Last Name'); ?>:</label>
                    <input type='text' class='form-control form-control-sm' name='lname' id='lname' value='<?php echo attr($form_lname); ?>' required />
                </div>

                <div class="form-group mb-3">
                    <label for="sex"><?php echo xlt('Sex'); ?>:</label>
                    <select name='sex' id="sex" class="form-control form-control-sm">
                        <option value=''><?php echo xlt('Unassigned'); ?></option>
                        <?php
                        $ores = sqlStatement("SELECT option_id, title FROM list_options " .
                          "WHERE list_id = 'sex' AND activity = 1 ORDER BY seq");
                        while ($orow = sqlFetchArray($ores)) {
                            echo "    <option value='" . attr($orow['option_id']) . "'";
                            if ($orow['option_id'] == $form_sex) {
                                echo " selected";
                            }
                            echo ">" . text($orow['title']) . "</option>\n";
                        }
                        ?>
                    </select>
                </div>

                <?php if ($GLOBALS['inhouse_pharmacy']) { ?>
                <div class="form-group mb-3">
                    <label for="refsource"><?php echo xlt('Referral Source'); ?>:</label>
                    <select name='refsource' id="refsource" class="form-control form-control-sm">
                        <option value=''><?php echo xlt("Unassigned"); ?></option>
                        <?php
                        $ores = sqlStatement("SELECT option_id, title FROM list_options " .
                        "WHERE list_id = 'refsource' AND activity = 1 ORDER BY seq");
                        while ($orow = sqlFetchArray($ores)) {
                            echo "    <option value='" . attr($orow['option_id']) . "'";
                            if ($orow['option_id'] == $form_refsource) {
                                echo " selected";
                            }
                            echo ">" . text($orow['title']) . "</option>\n";
                        }
                        ?>
                    </select>
                </div>
                <?php } ?>

                <div class="form-group mb-3">
                    <label for="DOB"><?php echo xlt('Birth Date'); ?>:</label>
                    <input type='text' class='form-control form-control-sm datepicker' name='DOB' id='DOB'
                        value='<?php echo attr($form_dob); ?>' />
                </div>

                <div class="form-group mb-3"<?php echo $regstyle ?>>
                    <label for="regdate"><?php echo xlt('Registration Date'); ?>:</label>
                    <input type='text' class='form-control form-control-sm datepicker' name='regdate' id='regdate'
                        value='<?php echo attr($form_regdate); ?>' />
                </div>

                <div class="form-group mb-3">
                    <label for="pubpid"><?php echo xlt('Patient Number'); ?>:</label>
                    <input type='text' class='form-control form-control-sm' name='pubpid' id='pubpid' value='<?php echo attr($form_pubpid); ?>' />
                    <small class="form-text text-muted"><?php echo xlt('omit to autoassign'); ?></small>
                </div>

                <div class="form-group mb-3">
                    <button type='submit' name='form_create' class='btn btn-primary btn-submit'><?php echo xlt('Create New Patient'); ?></button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
<?php
if ($form_pubpid) {
    echo "alert(" . xlj('This patient ID is already in use!') . ");\n";
}
?>
</script>

</body>
</html>
