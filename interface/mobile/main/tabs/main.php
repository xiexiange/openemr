<?php

/**
 * main.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$sessionAllowWrite = true;
// In mobile copy, globals.php lives three directories up (interface/globals.php)
require_once(__DIR__ . '/../../../globals.php');
require_once $GLOBALS['srcdir'] . '/ESign/Api.php';

use ESign\Api;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Events\Main\Tabs\RenderEvent;
use OpenEMR\Menu\MainMenuRole;
use OpenEMR\Services\LogoService;
use OpenEMR\Services\ProductRegistrationService;
use OpenEMR\Telemetry\TelemetryService;
use Symfony\Component\Filesystem\Path;

$logoService = new LogoService();
$menuLogo = $logoService->getLogo('core/menu/primary/');
// Registration status and options.
$productRegistration = new ProductRegistrationService();
$product_row = $productRegistration->getProductDialogStatus();
$allowRegisterDialog = $product_row['allowRegisterDialog'] ?? 0;
$allowTelemetry = $product_row['allowTelemetry'] ?? null; // for dialog
$allowEmail = $product_row['allowEmail'] ?? null; // for dialog
// If running unit tests, then disable the registration dialog
if ($_SESSION['testing_mode'] ?? false) {
    $allowRegisterDialog = false;
}
// If the user is not a super admin, then disable the registration dialog
if (!AclMain::aclCheckCore('admin', 'super')) {
    $allowRegisterDialog = false;
}

// Ensure token_main matches so this script can not be run by itself
//  If tokens do not match, then destroy the session and go back to log in screen
if (
    (empty($_SESSION['token_main_php'])) ||
    (empty($_GET['token_main'])) ||
    ($_GET['token_main'] != $_SESSION['token_main_php'])
) {
// Below functions are from auth.inc, which is included in globals.php
    authCloseSession();
    authLoginScreen(false);
}
// this will not allow copy/paste of the link to this main.php page or a refresh of this main.php page
//  (default behavior, however, this behavior can be turned off in the prevent_browser_refresh global)
if ($GLOBALS['prevent_browser_refresh'] > 1) {
    unset($_SESSION['token_main_php']);
}

$esignApi = new Api();
$twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();

?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo text($openemr_name); ?></title>

    <script>
        // This is to prevent users from losing data by refreshing or backing out of OpenEMR.
        //  (default behavior, however, this behavior can be turned off in the prevent_browser_refresh global)
        <?php if ($GLOBALS['prevent_browser_refresh'] > 0) { ?>
        window.addEventListener('beforeunload', (event) => {
            if (!timed_out) {
                event.returnValue = <?php echo xlj('Recommend not leaving or refreshing or you may lose data.'); ?>;
            }
        });
        <?php } ?>

        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        // Since this should be the parent window, this is to prevent calls to the
        // window that opened this window. For example when a new window is opened
        // from the Patient Flow Board or the Patient Finder.
        window.opener = null;
        window.name = "main";

        // This flag indicates if another window or frame is trying to reload the login
        // page to this top-level window.  It is set by javascript returned by auth.inc.php
        // and is checked by handlers of beforeunload events.
        var timed_out = false;
        // some globals to access using top.variable
        // note that 'let' or 'const' does not allow global scope here.
        // only use var
        var isPortalEnabled = "<?php echo $GLOBALS['portal_onsite_two_enable'] ?>";
        // Set the csrf_token_js token that is used in the below js/tabs_view_model.js script
        var csrf_token_js = <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
        var userDebug = <?php echo js_escape($GLOBALS['user_debug']); ?>;
        var webroot_url = <?php echo js_escape($web_root); ?>;
        var jsLanguageDirection = <?php echo js_escape($_SESSION['language_direction']); ?> ||
        'ltr';
        var jsGlobals = {};
        // used in tabs_view_model.js.
        jsGlobals.enable_group_therapy = <?php echo js_escape($GLOBALS['enable_group_therapy']); ?>;
        jsGlobals.languageDirection = jsLanguageDirection;
        jsGlobals.date_display_format = <?php echo js_escape($GLOBALS['date_display_format']); ?>;
        jsGlobals.time_display_format = <?php echo js_escape($GLOBALS['time_display_format']); ?>;
        jsGlobals.timezone = <?php echo js_escape($GLOBALS['gbl_time_zone'] ?? ''); ?>;
        jsGlobals.assetVersion = <?php echo js_escape($GLOBALS['v_js_includes']); ?>;
        var WindowTitleAddPatient = <?php echo($GLOBALS['window_title_add_patient_name'] ? 'true' : 'false'); ?>;
        var WindowTitleBase = <?php echo js_escape($openemr_name); ?>;
        const isSms = "<?php echo !empty($GLOBALS['oefax_enable_sms'] ?? null); ?>";
        const isFax = "<?php echo !empty($GLOBALS['oefax_enable_fax']) ?? null?>";
        const isServicesOther = (isSms || isFax);
        var telemetryEnabled = <?php echo js_escape((new TelemetryService())->isTelemetryEnabled()); ?>;

        /**
         * Async function to get session value from the server
         * Usage Example
         * let authUser;
         * let sessionPid = await top.getSessionValue('pid');
         * // If using then() method a promise is returned instead of the value.
         * await top.getSessionValue('authUser').then(function (auth) {
         *    authUser = auth;
         *    console.log('authUser', authUser);
         * });
         * console.log('session pid', sessionPid);
         * console.log('auth User', authUser);
         */
        async function getSessionValue(key) {
            restoreSession();
            let csrf_token_js = <?php echo js_escape(CsrfUtils::collectCsrfToken('default')); ?>;
            const config = {
                url: `${webroot_url}/library/ajax/set_pt.php?csrf_token_form=${csrf_token_js}`,
                method: 'POST',
                data: {
                    mode: 'session_key',
                    key: key
                }
            };
            try {
                const response = await $.ajax(config);
                restoreSession();
                return response;
            } catch (error) {
                throw error;
            }
        }

        function goRepeaterServices() {
            // Ensure send the skip_timeout_reset parameter to not count this as a manual entry in the
            // timing out mechanism in OpenEMR.

            // Send the skip_timeout_reset parameter to not count this as a manual entry in the
            // timing out mechanism in OpenEMR. Notify App for various portal and reminder alerts.
            // Combined portal and reminders ajax to fetch sjp 06-07-2020.
            // Incorporated timeout mechanism in 2021
            restoreSession();
            let request = new FormData;
            request.append("skip_timeout_reset", "1");
            request.append("isPortal", isPortalEnabled);
            request.append("isServicesOther", isServicesOther);
            request.append("isSms", isSms);
            request.append("isFax", isFax);
            request.append("csrf_token_form", csrf_token_js);
            fetch(webroot_url + "/library/ajax/dated_reminders_counter.php", {
                method: 'POST',
                credentials: 'same-origin',
                body: request
            }).then((response) => {
                if (response.status !== 200) {
                    console.log('Reminders start failed. Status Code: ' + response.status);
                    return;
                }
                return response.json();
            }).then((data) => {
                if (data.timeoutMessage && (data.timeoutMessage == 'timeout')) {
                    // timeout has happened, so logout
                    timeoutLogout();
                }
                if (isPortalEnabled) {
                    let mail = data.mailCnt;
                    let chats = data.chatCnt;
                    let audits = data.auditCnt;
                    let payments = data.paymentCnt;
                    let total = data.total;
                    let enable = ((1 * mail) + (1 * audits)); // payments are among audits.
                    // Send portal counts to notification button model
                    // Will turn off button display if no notification!
                    app_view_model.application_data.user().portal(enable);
                    if (enable > 0) {
                        app_view_model.application_data.user().portalAlerts(total);
                        app_view_model.application_data.user().portalAudits(audits);
                        app_view_model.application_data.user().portalMail(mail);
                        app_view_model.application_data.user().portalChats(chats);
                        app_view_model.application_data.user().portalPayments(payments);
                    }
                }
                if (isServicesOther) {
                    let sms = data.smsCnt;
                    let fax = data.faxCnt;
                    let total = data.serviceTotal;
                    let enable = ((1 * sms) + (1 * fax));
                    // Will turn off button display if no notification!
                    app_view_model.application_data.user().servicesOther(enable);
                    if (enable > 0) {
                        app_view_model.application_data.user().serviceAlerts(total);
                        app_view_model.application_data.user().smsAlerts(sms);
                        app_view_model.application_data.user().faxAlerts(fax);
                    }
                }
                // Always send reminder count text to model
                app_view_model.application_data.user().messages(data.reminderText);
            }).catch(function (error) {
                console.log('Request failed', error);
            });

            // run background-services
            // delay 10 seconds to prevent both utility trigger at close to same time.
            // Both call globals so that is my concern.
            setTimeout(function () {
                restoreSession();
                request = new FormData;
                request.append("skip_timeout_reset", "1");
                request.append("ajax", "1");
                request.append("csrf_token_form", csrf_token_js);
                fetch(webroot_url + "/library/ajax/execute_background_services.php", {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: request
                }).then((response) => {
                    if (response.status !== 200) {
                        console.log('Background Service start failed. Status Code: ' + response.status);
                    }
                }).catch(function (error) {
                    console.log('HTML Background Service start Request failed: ', error);
                });
            }, 10000);

            // auto run this function every 60 seconds
            var repeater = setTimeout("goRepeaterServices()", 60000);
        }

        function isEncounterLocked(encounterId) {
            <?php if ($esignApi->lockEncounters()) { ?>
            // If encounter locking is enabled, make a synchronous call (async=false) to check the
            // DB to see if the encounter is locked.
            // Call restore session, just in case
            // @TODO next clean up pass, turn into await promise then modify tabs_view_model.js L-309
            restoreSession();
            let url = webroot_url + "/interface/esign/index.php?module=encounter&method=esign_is_encounter_locked";
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    encounterId: encounterId
                },
                success: function (data) {
                    encounter_locked = data;
                },
                dataType: 'json',
                async: false
            });
            return encounter_locked;
            <?php } else { ?>
            // If encounter locking isn't enabled then always return false
            return false;
            <?php } ?>
        }
    </script>

    <?php Header::setupHeader(['knockout', 'tabs-theme', 'i18next', 'hotkeys', 'i18formatting']); ?>
    <script>
        // set up global translations for js
        function setupI18n(lang_id) {
            restoreSession();
            return fetch(<?php echo js_escape($GLOBALS['webroot']) ?> +"/library/ajax/i18n_generator.php?lang_id=" + encodeURIComponent(lang_id) + "&csrf_token_form=" + encodeURIComponent(csrf_token_js), {
                credentials: 'same-origin',
                method: 'GET'
            }).then((response) => {
                if (response.status !== 200) {
                    console.log('I18n setup failed. Status Code: ' + response.status);
                    return [];
                }
                return response.json();
            })
        }

        setupI18n(<?php echo js_escape($_SESSION['language_choice']); ?>).then(translationsJson => {
            i18next.init({
                lng: 'selected',
                debug: false,
                nsSeparator: false,
                keySeparator: false,
                resources: {
                    selected: {
                        translation: translationsJson
                    }
                }
            });
        }).catch(error => {
            console.log(error.message);
        });

        /**
         * Assign and persist documents to portal patients
         * @var int patientId pid
         */
        function assignPatientDocuments(patientId) {
            let url = top.webroot_url + '/portal/import_template_ui.php?from_demo_pid=' + encodeURIComponent(patientId);
            dlgopen(url, 'pop-assignments', 'modal-lg', 850, '', '', {
                allowDrag: true,
                allowResize: true,
                sizeHeight: 'full',
            });
        }
    </script>

    <script src="js/custom_bindings.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/user_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/patient_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/therapy_group_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/tabs_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/application_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/frame_proxies.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/dialog_utils.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/shortcuts.js?v=<?php echo $v_js_includes; ?>"></script>

    <?php
    // Below code block is to prepare certain elements for deciding what links to show on the menu
    // prepare newcrop globals that are used in creating the menu
    if ($GLOBALS['erx_enable']) {
        $newcrop_user_role_sql = sqlQuery("SELECT `newcrop_user_role` FROM `users` WHERE `username` = ?", [$_SESSION['authUser']]);
        $GLOBALS['newcrop_user_role'] = $newcrop_user_role_sql['newcrop_user_role'];
        if ($GLOBALS['newcrop_user_role'] === 'erxadmin') {
            $GLOBALS['newcrop_user_role_erxadmin'] = 1;
        }
    }

    // prepare track anything to be used in creating the menu
    $track_anything_sql = sqlQuery("SELECT `state` FROM `registry` WHERE `directory` = 'track_anything'");
    $GLOBALS['track_anything_state'] = ($track_anything_sql['state'] ?? 0);
    // prepare Issues popup link global that is used in creating the menu
    $GLOBALS['allow_issue_menu_link'] = (
        (AclMain::aclCheckCore('encounters', 'notes', '', 'write')
        || AclMain::aclCheckCore('encounters', 'notes_a', '', 'write'))
        && AclMain::aclCheckCore('patients', 'med', '', 'write')
    );

    // we use twig templates here so modules can customize some of these files
    // at some point we will twigify all of main.php so we can extend it.
    echo $twig->render("interface/main/tabs/tabs_template.html.twig", []);
    echo $twig->render("interface/main/tabs/menu_template.html.twig", []);
    // TODO: patient_data_template.php is a more extensive refactor that could be done in a future feature request but to not jeopardize 7.0.3 release we will hold off.
    ?>
    <?php require_once("templates/patient_data_template.php"); ?>
    <?php
    echo $twig->render("interface/main/tabs/therapy_group_template.html.twig", []);
    echo $twig->render("interface/main/tabs/user_data_template.html.twig", [
        'openemr_name' => $GLOBALS['openemr_name'],
        'isAdmin' => AclMain::aclCheckCore('admin', 'super')
    ]);
    // Collect the menu then build it
    $menuMain = new MainMenuRole($GLOBALS['kernel']->getEventDispatcher());
    $menu_restrictions = $menuMain->getMenu();
    echo $twig->render("interface/main/tabs/menu_json.html.twig", ['menu_restrictions' => $menu_restrictions]);
    ?>
    <?php $userQuery = sqlQuery("select * from users where username = ?", [$_SESSION['authUser']]); ?>

    <script>
        <?php
        if ($_SESSION['default_open_tabs']) :
            // For now, only the first tab is visible, this could be improved upon by further customizing the list options in a future feature request
            $visible = "true";
            foreach ($_SESSION['default_open_tabs'] as $i => $tab) :
                $_unsafe_url = preg_replace('/(\?.*)/m', '', Path::canonicalize($fileroot . DIRECTORY_SEPARATOR . $tab['notes']));
                if (realpath($_unsafe_url) === false || !str_starts_with($_unsafe_url, (string) $fileroot)) {
                    unset($_SESSION['default_open_tabs'][$i]);
                    continue;
                }
                $url = json_encode($webroot . "/" . $tab['notes']);
                $target = json_encode($tab['option_id']);
                $label = json_encode(xl("Loading") . " " . $tab['title']);
                $loading = xlj("Loading");
                echo "app_view_model.application_data.tabs.tabsList.push(new tabStatus($label, $url, $target, $loading, true, $visible, false));\n";
                $visible = "false";
            endforeach;
        endif;
        ?>

        app_view_model.application_data.user(new user_data_view_model(<?php echo json_encode($_SESSION["authUser"])
            . ',' . json_encode($userQuery['fname'])
            . ',' . json_encode($userQuery['lname'])
            . ',' . json_encode($_SESSION['authProvider']); ?>));
    </script>
    <style>
      html,
      body {
        width: 100%;
        max-width: 100%;
        min-height: 100vh;
      }

      body {
        overflow: hidden;
        background-color: #f8f9fa;
      }

      #mainBox {
        max-width: 100%;
      }

      #mainFrames_div {
        min-height: 0;
      }

      .mainFrames {
        overflow: hidden;
      }

      #framesDisplay {
        height: 100%;
      }

      /* App-like shell: fixed header + scrollable content + bottom tabs on mobile */
      @media (max-width: 768px) {
        body {
          overflow: hidden;
        }

        /* Stick the main navbar to the top, like an app header */
        nav.navbar {
          padding-left: .5rem;
          padding-right: .5rem;
        }

        #mainBox {
          padding-top: 0;       /* header不再吸顶，顶部不需要预留空间 */
          padding-bottom: 60px; /* 仍然为底部 tabs 预留空间 */
        }

        /* Compact attendant data on mobile */
        #attendantData {
          padding-left: .5rem;
          padding-right: .5rem;
        }

        /* Make the tabs controls behave like a bottom tab bar */
        #tabs_div {
          position: fixed;
          bottom: 0;
          left: 0;
          right: 0;
          z-index: 1030;
          background-color: #ffffff;
          box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.08);
        }

        /*
         * H5: 防止 iframe 视图切换后“顶栏被顶掉/被盖住”
         * 让滚动发生在 iframe 内部，而不是外层容器滚动。
         */
        #mainFrames_div {
          flex: 1 1 auto;
          overflow: hidden;
          min-height: 0;
        }

        #framesDisplay,
        #framesDisplay .frameDisplay {
          height: 100%;
          min-height: 0;
        }

        #framesDisplay iframe {
          width: 100%;
          height: 100%;
          border: 0;
          display: block;
          overflow: auto;
          -webkit-overflow-scrolling: touch;
        }

        /* Slightly increase hit area for nav items in the bottom bar */
        #tabs_div .nav-link,
        #tabs_div button {
          padding-top: .4rem !important;
          padding-bottom: .4rem !important;
        }

        /* H5: 隐藏移动端顶部栏的刷新按钮 */
        nav.navbar button[title*="刷新"],
        nav.navbar button[title*="Refresh"],
        nav.navbar button[title*="refresh"],
        nav.navbar button .fa-sync,
        nav.navbar button .fa-refresh,
        nav.navbar button .fa-redo,
        nav.navbar button .fa-rotate-right,
        nav.navbar a[title*="刷新"],
        nav.navbar a[title*="Refresh"],
        nav.navbar a[title*="refresh"],
        nav.navbar a .fa-sync,
        nav.navbar a .fa-refresh,
        nav.navbar a .fa-redo,
        nav.navbar a .fa-rotate-right {
          display: none !important;
        }
        
        /* 隐藏包含刷新图标的父元素 */
        nav.navbar button:has(.fa-sync),
        nav.navbar button:has(.fa-refresh),
        nav.navbar button:has(.fa-redo),
        nav.navbar button:has(.fa-rotate-right),
        nav.navbar a:has(.fa-sync),
        nav.navbar a:has(.fa-refresh),
        nav.navbar a:has(.fa-redo),
        nav.navbar a:has(.fa-rotate-right) {
          display: none !important;
        }
      }
    </style>
</head>

<body class="min-vw-100">
    <?php
    // fire off an event here
    if (!empty($GLOBALS['kernel']->getEventDispatcher())) {
        $dispatcher = $GLOBALS['kernel']->getEventDispatcher();
        $dispatcher->dispatch(new RenderEvent(), RenderEvent::EVENT_BODY_RENDER_PRE);
    }
    ?>
    <!-- Below iframe is to support logout, which needs to be run in an inner iframe to work as intended -->
    <iframe name="logoutinnerframe" id="logoutinnerframe" style="visibility:hidden; position:absolute; left:0; top:0; height:0; width:0; border:none;" src="about:blank"></iframe>
    <?php // mdsupport - app settings
    $disp_mainBox = '';
    if (isset($_SESSION['app1']) && $_SESSION['app1'] !== 'H5') {
        $rs = sqlquery(
            "SELECT title app_url FROM list_options WHERE activity=1 AND list_id=? AND option_id=?",
            ['apps', $_SESSION['app1']]
        );
        if ($rs['app_url'] != "main/main_screen.php") {
            echo '<iframe name="app1" src="../../' . attr($rs['app_url']) . '"
            style="position: absolute; left: 0; top: 0; height: 100%; width: 100%; border: none;" />';
            $disp_mainBox = 'style="display: none;"';
        }
    }
    ?>
    <div id="mainBox" class="container-fluid p-0 d-flex flex-column min-vh-100" <?php echo $disp_mainBox ?>>
        <nav class="navbar navbar-expand-xl navbar-light bg-light py-0 shadow-sm">
            <button class="navbar-toggler mr-auto" type="button" data-toggle="collapse" data-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainMenu" data-bind="template: {name: 'menu-template', data: application_data}"></div>
            <?php if ($GLOBALS['search_any_patient'] != 'none') : ?>
                <form name="frm_search_globals" class="form-inline">
                    <div class="input-group">
                        <input type="text" id="anySearchBox" class="form-control-sm <?php echo $any_search_class ?> form-control" name="anySearchBox" placeholder="<?php echo xla("搜索患者") ?>" autocomplete="off">
                        <div class="input-group-append">
                            <button type="button" id="search_globals" class="btn btn-sm btn-secondary <?php echo $search_globals_class ?>" title='<?php echo xla("Search for patient by entering whole or part of any demographics field information"); ?>' data-bind="event: {mousedown: viewPtFinder.bind( $data, '<?php echo xla("The search field cannot be empty. Please enter a search term") ?>', '<?php echo attr($search_any_type); ?>')}">
                                <i class="fa fa-search">&nbsp;</i></button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
            <!--Below is the user data section that contains the user information and the attendant data-->
            <span id="userData" data-bind="template: {name: 'user-data-template', data: application_data}"></span>
            <?php
            // fire off a nav event
            $dispatcher->dispatch(new RenderEvent(), RenderEvent::EVENT_BODY_RENDER_NAV);
            ?>
        </nav>
        <div id="attendantData" class="body_title acck" data-bind="template: {name: app_view_model.attendant_template_type, data: application_data}"></div>
        <div class="body_title pt-1" id="tabs_div" data-bind="template: {name: 'tabs-controls', data: application_data}"></div>
        <div class="mainFrames d-flex flex-column flex-fill" id="mainFrames_div">
            <div id="framesDisplay" data-bind="template: {name: 'tabs-frames', data: application_data}"></div>
        </div>
        <?php echo $twig->render("product_registration/product_registration_modal.html.twig", [
            'webroot' => $webroot,
            'allowEmail' => $allowEmail ?? false,
            'allowTelemetry' => $allowTelemetry ?? false]); ?>
    </div>
    <script>
        ko.applyBindings(app_view_model);

        $(function () {
            $('.dropdown-toggle').dropdown();
            $('#patient_caret').click(function () {
                $('#attendantData').slideToggle();
                $('#patient_caret').toggleClass('fa-caret-down').toggleClass('fa-caret-up');
            });
            if ($('body').css('direction') == "rtl") {
                $('.dropdown-menu-right').each(function () {
                    $(this).removeClass('dropdown-menu-right');
                });
            }
        });
        $(function () {
            $('#logo_menu').focus();
        });
        $('#anySearchBox').keypress(function (event) {
            if (event.which === 13 || event.keyCode === 13) {
                event.preventDefault();
                $('#search_globals').mousedown();
            }
        });
        document.addEventListener('touchstart', {}); //specifically added for iOS devices, especially in iframes
        
        // H5: 隐藏移动端顶部栏的刷新按钮
        $(function () {
            function hideRefreshButtons() {
                // 隐藏所有包含刷新图标的按钮和链接
                $('nav.navbar button:has(.fa-sync), nav.navbar button:has(.fa-refresh), nav.navbar button:has(.fa-redo), nav.navbar button:has(.fa-rotate-right)').hide();
                $('nav.navbar a:has(.fa-sync), nav.navbar a:has(.fa-refresh), nav.navbar a:has(.fa-redo), nav.navbar a:has(.fa-rotate-right)').hide();
                // 隐藏标题包含"刷新"或"Refresh"的按钮
                $('nav.navbar button[title*="刷新"], nav.navbar button[title*="Refresh"], nav.navbar button[title*="refresh"]').hide();
                $('nav.navbar a[title*="刷新"], nav.navbar a[title*="Refresh"], nav.navbar a[title*="refresh"]').hide();
            }
            // 立即执行
            hideRefreshButtons();
            // 监听DOM变化，确保动态添加的刷新按钮也被隐藏
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function(mutations) {
                    hideRefreshButtons();
                });
                var navbar = document.querySelector('nav.navbar');
                if (navbar) {
                    observer.observe(navbar, { childList: true, subtree: true });
                }
            }
            // 延迟执行，确保所有内容都已加载
            setTimeout(hideRefreshButtons, 500);
            setTimeout(hideRefreshButtons, 1000);
        });
        
        <?php if (($_ENV['OPENEMR__NO_BACKGROUND_TASKS'] ?? 'false') !== 'true') { ?>
        $(function () {
            goRepeaterServices();
        });
        <?php } ?>

        // H5：统一拦截菜单点击，在真正的菜单动作函数执行后，自动收起整个 tabs 列表
        // 说明：
        // - Knockout 在模板中使用的是全局函数 menuActionClick 作为 click 处理器
        // - 这里对它做一次“包装”，不改变原有逻辑，只是在成功调用后做一次折叠
    </script>
    <?php

    // fire off an event here
    $dispatcher->dispatch(new RenderEvent(), RenderEvent::EVENT_BODY_RENDER_POST);

    if (!empty($allowRegisterDialog)) { // disable if running unit tests.
        // Include the product registration js, telemetry and usage data reporting dialog
        echo $twig->render("product_registration/product_reg.js.twig", ['webroot' => $webroot]);
    }

    ?>
</body>

</html>
