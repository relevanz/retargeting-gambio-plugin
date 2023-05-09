<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base\Export\Item;

use Releva\Retargeting\Base\Utf8Util;

/**
 * Product export item
 *
 * Simple data wrapper object that represents a product.
 */
class ProductExportItem implements ExportItemInterface
{
    /** @var int|string */
    protected $id;
    /** @var int[]|string[] */
    protected $categoryIds = [];
    /** @var string */
    protected $name;
    /** @var string */
    protected $descriptionShort;
    /** @var string */
    protected $descriptionLong;
    /** @var float */
    protected $priceNet;
    /** @var float */
    protected $priceGross;
    /** @var float */
    protected $priceOfferNet;
    /** @var float */
    protected $priceOfferGross;
    /** @var string */
    protected $basePriceUnit;
    /** @var float */
    protected $basePriceValue;
    /** @var string */
    protected $link;
    /** @var string */
    protected $image;

    public function __construct($id = null, array $cIds = [], $name = null, $descShort = null, $descLong = null, $priceGross = null, $priceOfferGross = null, $link = null, $image = null) {
        $this->setId($id);
        $this->setCategoryIds($cIds);
        $this->setName($name);
        $this->setDescriptionShort($descShort);
        $this->setDescriptionLong($descLong);
        $this->setPrice(null, $priceGross);
        $this->setPriceOffer(null, $priceOfferGross);
        $this->setLink($link);
        $this->setImage($image);
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setCategoryIds(array $cIds) {
        $this->categoryIds = $cIds;
        return $this;
    }

    public function setName($name) {
        $this->name = $name === null ? null : Utf8Util::toUtf8((string)$name);
        return $this;
    }

    public function setDescriptionShort($descShort) {
        $this->descriptionShort = $descShort === null
            ? null
            : str_replace(["\r\n", "\r"], "\n", Utf8Util::toUtf8((string)$descShort));;
        return $this;
    }

    public function setDescriptionLong($descLong) {
        $this->descriptionLong = $descLong === null
            ? null
            : str_replace(["\r\n", "\r"], "\n", Utf8Util::toUtf8((string)$descLong));
        return $this;
    }

    private static function calcPrices($priceNet, $priceGross, $taxPercent) {
        if (($priceNet > 0) && ($priceGross === null) && ($taxPercent > 0)) {
            $priceGross = $priceNet + $priceNet / 100 * $taxPercent;
        } elseif (($priceNet === null) && ($priceGross > 0) && ($taxPercent > 0)) {
            $priceNet = $priceGross / (($taxPercent + 100) / 100);
        }
        return [$priceNet, $priceGross];
    }

    private function santizePices() {
        $this->priceGross      = max($this->priceGross, $this->priceOfferGross);
        $this->priceOfferGross = min($this->priceGross, $this->priceOfferGross);
        if ($this->priceOfferGross <= 0.0) {
            $this->priceOfferGross = $this->priceGross;
        }

        $this->priceNet      = max($this->priceNet, $this->priceOfferNet);
        $this->priceOfferNet = min($this->priceNet, $this->priceOfferNet);
        if ($this->priceOfferNet <= 0.0) {
            $this->priceOfferNet = $this->priceNet;
        }
    }

    public function setPrice($priceNet = null, $priceGross = null, $taxPercent = null) {
        $prices = self::calcPrices($priceNet, $priceGross, $taxPercent);
        $this->priceNet   = $prices[0] === null ? null : round((float)$prices[0], 4);
        $this->priceGross = $prices[1] === null ? null : round((float)$prices[1], 2);
        return $this;
    }

    public function setPriceOffer($priceNet = null, $priceGross = null, $taxPercent = null) {
        $prices = self::calcPrices($priceNet, $priceGross, $taxPercent);
        $this->priceOfferNet   = $prices[0] === null ? null : round((float)$prices[0], 4);
        $this->priceOfferGross = $prices[1] === null ? null : round((float)$prices[1], 2);
        return $this;
    }

    public function setBasePrice($unit, $value) {
        $this->basePriceUnit  = $unit  === null ? null : (string)$unit;
        $this->basePriceValue = $value === null ? null : (float)$value;
        return $this;
    }

    public function setLink($link) {
        $this->link = $link === null ? null : $link;
        return $this;
    }

    public function setImage($image) {
        $this->image = $image === null ? null : $image;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function getCategoryIds() {
        return $this->categoryIds;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescriptionShort() {
        return $this->descriptionShort;
    }

    public function getDescriptionLong() {
        return $this->descriptionLong;
    }

    public function getPriceNet() {
        $this->santizePices();
        return $this->priceNet;
    }

    public function getPriceGross() {
        $this->santizePices();
        return $this->priceGross;
    }

    public function getPriceOfferNet() {
        $this->santizePices();
        return $this->priceOfferNet;
    }

    public function getPriceOfferGross() {
        $this->santizePices();
        return $this->priceOfferGross;
    }

    public function getBasePriceUnit() {
        return $this->basePriceUnit;
    }

    public function getBasePriceValue() {
        return $this->basePriceNet;
    }

    public function getLink() {
        return $this->link;
    }

    public function getImage() {
        return $this->image;
    }

    public function getData() {
        $this->santizePices();
        return [
            'id' => $this->id,
            'categoryIds' => $this->categoryIds,
            'name' => $this->name,
            'descriptionShort' => $this->descriptionShort,
            'descriptionLong' => $this->descriptionLong,
            'priceNet' => $this->priceNet,
            'price' => $this->priceGross,
            'priceOfferNet' => $this->priceOfferNet,
            'priceOffer' => $this->priceOfferGross,
            'basePriceUnit' => $this->basePriceUnit,
            'basePriceValue' => $this->basePriceValue,
            'link' => $this->link,
            'image' => $this->image,
        ];
    }

    public function getKeys() {
        return array_keys($this->getData());
    }

}
