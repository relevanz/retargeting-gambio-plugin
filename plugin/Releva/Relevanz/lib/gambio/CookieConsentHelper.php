<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Gambio;

use DateTime;

use CookieConsentPanelControllerFactory;
use LanguageTextManager;
use StaticGXCoreLoader;

use Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects\PurposeUpdateDto;
use Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects\PurposeWriterDto;
use Gambio\CookieConsentPanel\Services\Purposes\Entities\Purpose;

class CookieConsentHelper
{
    const CATEGORY_DEFAULT = 4;
    const CATEGORIES_SKIP = [1];
    const PURPOSE_ALIAS = 'relevanz';

    const CONFIG_KEY = 'RELEVANZ_CC_CATEGORY_ID';

    protected $ccfactory = null;
    protected $configCategoryId = null;

    protected function gccf() {
        if ($this->ccfactory === null) {
            $this->ccfactory = new CookieConsentPanelControllerFactory();
        }
        return $this->ccfactory;
    }

    public static function isActive() {
        return (bool)gm_get_conf('MODULE_CENTER_GAMBIOCOOKIECONSENTPANEL_INSTALLED')
            && class_exists('CookieConsentPanelControllerFactory');
    }

    public function getCategories() {
        if (!self::isActive()) {
            return null;
        }
        $ccc = $this->gccf()->purposeReaderService()->categories($_SESSION['languages_id']);
        $r = [];
        if (empty($ccc)) {
            return null;
        }
        foreach ($ccc as $ccat) {
            if (in_array($ccat->id(), self::CATEGORIES_SKIP)) {
                continue;
            }
            $r[$ccat->id()] = $ccat->name();
        }
        return $r;
    }

    public function getConfigCategoryId() {
        if ($this->configCategoryId !== null) {
            return $this->configCategoryId;
        }

        $qb = StaticGXCoreLoader::getDatabaseQueryBuilder();

        $row = $qb->select('*')->from(TABLE_CONFIGURATION)
            ->where('configuration_key', self::CONFIG_KEY)->get()->row_array();

        $this->configCategoryId = (isset($row['configuration_value']) && !empty($row['configuration_value']))
            ? (int)$row['configuration_value']
            : self::CATEGORY_DEFAULT;

        return $this->configCategoryId;
    }

    public function updateConfigCategoryId($categoryId) {
        $this->configCategoryId = $categoryId;

        $qb = StaticGXCoreLoader::getDatabaseQueryBuilder();

        $configRows = $qb->select('*')->from(TABLE_CONFIGURATION)
            ->where('configuration_key', self::CONFIG_KEY)
            ->get()->num_rows();

        if ($configRows === 0) {
            $now = new DateTime();
            $qb->insert(TABLE_CONFIGURATION, [
                'configuration_key'   => self::CONFIG_KEY,
                'configuration_value' => $categoryId,
                'sort_order' => 0,
                'date_added' => $now->format('Y-m-d H:i:s'),
            ]);
        } else {
            $qb->where('configuration_key', self::CONFIG_KEY)
                ->update(TABLE_CONFIGURATION, ['configuration_value' => $categoryId]);
        }
    }

    public function getRelevaPurpose() {
        if (!self::isActive()) {
            return null;
        }
        $purposes = $this->gccf()->purposeReaderService()->allPurposes();

        $purpose = null;
        foreach ($purposes as $p) {
            if ($p->alias()->value() === self::PURPOSE_ALIAS) {
                $purpose = $p;
                break;
            }
        }
        return $purpose;
    }

    public function getCurrentPurposeCategoryId() {
        $p = $this->getRelevaPurpose();
        if ($p !== null) {
            return (int)$p->category()->id();
        }
        return null;
    }

    public function uninstallPurpose() {
        if (!self::isActive()) {
            return;
        }
        $p = $this->getRelevaPurpose();
        if ($p !== null) {
            return;
        }
        $this->gccf()->purposeDeleteService()
            ->deleteByPurposeId((int)$p->category()->id());
    }

    public function installPurpose() {
        if (!self::isActive()) {
            return;
        }
        $p = $this->getRelevaPurpose();
        if ($p !== null) {
            return;
        }

        $lp = $this->gccf()->languageProvider();

        $titles = [];
        $descriptions = [];
        foreach ($lp->getAdminCodes()->getArray() as $languageCode) {
            $lid = $lp->getIdByCode($languageCode);
            $lang = new LanguageTextManager('relevanz', $lid);
            $titles[$lid] = $lang->get_text('ccp_title');
            $descriptions[$lid] = $lang->get_text('ccp_desc');
        }

        $dto = new PurposeWriterDto(
            $this->getConfigCategoryId(),
            $descriptions,
            $titles,
            true,     // status
            true,     // deletable (yes, just in case shop owner wants to delete it manually)
            self::PURPOSE_ALIAS
        );
        return $this->gccf()->purposeWriteService()->store($dto);
    }

    public function updatePurpose(?Purpose $p = null) {
        if (!self::isActive()) {
            return;
        }
        if ($p === null) {
            $p = $this->getRelevaPurpose();
        }
        $dto = new PurposeUpdateDto(
            $this->getConfigCategoryId(),
            $p->description()->value(),
            $p->name()->value(),
            true, // status
            (bool)$p->deletable()->value(),
            self::PURPOSE_ALIAS,
            (int)$p->id()->value()
        );
        $this->gccf()->purposeUpdateService()->update($dto);
    }

    public function makeItSo() {
        if (!self::isActive()) {
            return;
        }
        $p = $this->getRelevaPurpose();
        if ($p === null) {
            $this->installPurpose();
        } else {
            if ($this->getConfigCategoryId() !== (int)$p->category()->id()) {
                $this->updatePurpose($p);
            }
        }
    }

    public function getScriptTag($jsurl) {
        $ejsurl = htmlspecialchars($jsurl);
        $fallback = '<script type="text/javascript" src="' . $ejsurl . '" async="true"></script>';
        if (!self::isActive()) {
            return $fallback;
        }
        $p = $this->getRelevaPurpose();
        if ($p === null) {
            return $fallback;
        }
        if ($p->status()->isActive()) {
            return '<script async type="as-oil" '
                .'data-type="text/javascript" data-src="'.$ejsurl.'" '
                .'data-purposes="'.$p->id()->value().'" data-managed="as-oil"></script>';
        } else {
            return $fallback;
        }
    }

}
