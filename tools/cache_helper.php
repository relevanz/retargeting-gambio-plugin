<?php
/* --------------------------------------------------------------
   Hilfswerkzeug um die Caches des Shops zu leeren.

   Bei der Deinstallation einiger Plugins kann es notwenig sein, dass
   direkt nach dem das Plugin aus GXModules gelöscht wurde der Cache
   für die Modulinformationen geleert werden muss, da der Shop aufgrund
   von fatalen Fehlern unbedienbar wird.
   Idealerweise sollten Sie schon, bevor das Plugin gelöscht wird,
   die Shop-Admin Seite der Cache-Verwaltung offen haben, um dierekt
   im Anschluss den Cache für die Modulinformationen zu leeren.
   Sollten Sie das versäumt haben, können Sie diese Datei in das
   Wurzelverzeichnis Ihres Shops laden und unter der entsprechenden
   URL (z. B. https://www.mein-shop.de/cache_helper.php)
   die Caches leeren, auch wenn Sie sich aufgrund von fatalen Fehlern
   nicht mehr in Ihren Shop einloggen können.

   --------------------------------------------------------------

   Helper tool to clear the caches of the shop.

   When uninstalling some plugins, it may be necessary to clear the
   cache for the module information immediately after the plugin has
   been deleted from GXModules, as the shop becomes unusable due to
   fatal errors.
   Ideally, you should have the shop admin page of the cache manager
   open before deleting the plugin, in order to empty the cache for
   the module information immediately afterwards.
   If you have failed to do so, you can upload this file into the
   root directory of your shop and empty the caches under the
   corresponding URL (e.g. https://www.mein-shop.de/cache_helper.php),
   even if you can no longer log into your shop due to fatal errors.

   --------------------------------------------------------------

   Released under the GNU General Public License
   [http://www.gnu.org/licenses/gpl-2.0.html]

   based on:
   (c) 2000-2001 The Exchange Project
   (c) 2002-2003 osCommerce coding standards (a typical file) www.oscommerce.com
   (c) 2003      nextcommerce (start.php,1.5 2004/03/17); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
   (c) 2018 Gambio GmbH - http://www.gambio.de
   --------------------------------------------------------------
*/

class GambioCacheControl {
    protected $lang = null;
    protected $cc = null;

    protected function getCacheControl() {
        if ($this->cc === null) {
            $this->cc = MainFactory::create_object('CacheControl');
        }
        return $this->cc;
    }

    public function trans($key, $cat) {
        if ($this->lang === null) {
            $this->lang = MainFactory::create_object('LanguageTextManager', [], true);
        }
        return $this->lang->get_text($key, $cat);
    }

    protected function clearOutput() {
        $cc = $this->getCacheControl();
        $cc->clear_content_view_cache();
        $cc->clear_templates_c();
        $cc->clear_template_cache();
        $cc->clear_google_font_cache();
        $cc->clear_css_cache();
        $cc->clear_expired_shared_shopping_carts();
        $cc->remove_reset_token();
    }

    protected function clearData() {
        $this->getCacheControl()->clear_data_cache();
    }

    protected function clearText() {
        $this->clearData();
        MainFactory::create_object('PhraseCacheBuilder', [])->build();
    }

    public function clearSubmenu() {
        $this->getCacheControl()->rebuild_categories_submenus_cache();
    }

    public function clearCategoriesIndex() {
        $this->getCacheControl()->rebuild_products_categories_index();
    }

    public function clearProductsPropertiesIndex() {
        $this->getCacheControl()->rebuild_products_properties_index();
    }

    public function clearFeatureIndex() {
        $this->getCacheControl()->rebuild_feature_index();
    }

    public function clearMailTemplates() {
        MainFactory::create_object('MailTemplatesCacheBuilder')->build();
    }

    public function clear($type) {
        $caches = $this->getCaches();
        $m = [$this, 'clear'.str_replace(' ', '', ucwords(str_replace('-', ' ', $type)))];
        if (is_callable($m)) {
            call_user_func($m);
            return [
                'm' => $this->trans('CLEAR_'.$caches[$type].'_CACHE_SUCCESS', 'clear_cache'),
                't' => 'success'
            ];
        }
        return [
            'm' => 'Dieser Cache-Typ existiert nicht.',
            't' => 'error'
        ];
    }

    public function getCaches() {
        return [
            'output' => 'OUTPUT',
            'data' => 'DATA',
            'text' => 'TEXT',
            'submenu' => 'SUBMENUS',
            'categories-index' => 'CATEGORIES',
            'products-properties-index' => 'PROPERTIES',
            'feature-index' => 'FEATURES',
            'mail-templates' => 'MAIL_TEMPLATES',
        ];
    }
}

$gc = new GambioCacheControl();
$messages = [];
if (!file_exists(__DIR__.'/includes/application_top.php')) {
    $messages[] = [
        'm' => 'Das Tool befindet sich nicht im Wurzelverzeichnis vom Shop.',
        't' => 'fatal'
    ];
} else {
    // needed functions
    require_once(__DIR__.'/includes/application_top.php');
    if (!class_exists('MainFactory')) {
        $messages[] = [
            'm' => 'Dies ist kein Gambio-Shop.',
            't' => 'fatal'
        ];
    } else if (isset($_GET['cache'])) {
        $messages[] = $gc->clear($_GET['cache']);
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="x-ua-compatible" content="IE=edge">
        <meta charset="utf-8">
        <title>Cache</title>
        <style type="text/css">
body {
    font-family: Roboto,"Open Sans","Helvetica Neue",Helvetica,Arial,sans-serif;
    font-size: 12px;
    line-height: 1.3em;
}
h1 {
    border-bottom: 1px solid #ccc;
    padding-bottom: 0.2rem;
}
table {
    border-spacing: 0;
}
table td {
    vertical-align: top;
    padding: 0.5rem 1.25rem;
}
table tr:nth-child(odd) td {
    background-color: #f4f4f4;
    border-top: 1px solid #efefef;
    border-bottom: 1px solid #efefef;
}
.btn {
    cursor: pointer;
    padding: 0.35rem 1rem;
    margin: 0;
    min-width: 6rem;
    font-weight: 400;
    text-align: center;
    text-decoration: none;
    outline: 0;
    border-radius: 0.1rem;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    background-image: linear-gradient(whitesmoke, #ebebeb);
    border-width: 1px;
    border-style: solid;
    border-color: #dedede #dedede #cfcfcf;
    color: #737373;
    display: inline-block;
}
.message {
    position: relative;
    border: 1px solid #2f2f2f;
    border-left-width: 4px;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
    padding: 0.75rem 1.25rem;
    background-color: #dddddd;
    color: #606060;
}
.message.message-info {
    background-color: #dcf4f9;
    color: #0070b3;
    border-color: #359ddb;
}
.message.message-success {
    background-color: #d2f2a3;
    color: #35530a;
    border-color: #73b512;
}
.message.message-error {
    background-color: #f2a3a3;
    color: #530a0a;
    border-color: #b51212;
}
.message.message-fatal {
    background-color: #df6481;
    color: #4b0e1c;
    border-color: #970039;
}
        </style>
    </head>
    <body>
        <h1>Caches</h1>
        <?php
        $fatal = false;
        foreach ($messages as $msg) {
            echo '<div class="message message-'.$msg['t'].'">'.$msg['m'].'</div>'."\n";
            if ($msg['t'] === 'fatal') {
                $fatal = true;
            }
        }
        if ($fatal) {
            echo '</body></html>';
            return -1;
        }
        ?>
        <table>
            <tbody>
<?php
foreach ($gc->getCaches() as $cache => $label) {
    echo '
                <tr>
                    <td><label>'.$gc->trans('BUTTON_'.$label.'_CACHE', 'clear_cache').'</label></td>
                    <td><a class="btn" href="?cache='.$cache.'">'.$gc->trans('execute', 'buttons').'</a></td>
                    <td>'.$gc->trans('TEXT_'.$label.'_CACHE', 'clear_cache').'</td>
                </tr>';
}
?>
            </tbody>
        </table>
    </body>
</html>
