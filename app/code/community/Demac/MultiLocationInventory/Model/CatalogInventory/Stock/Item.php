<?php
/**
 * Created by PhpStorm.
 * User: Allan MacGregor - Magento Practice Lead <allan@demacmedia.com>
 * Company: Demac Media Inc.
 * Date: 5/7/14
 * Time: 1:17 PM
 */

/**
 * Class Demac_MultiLocationInventory_Model_CatalogInventory_Stock_Item
 *
 * @method Demac_MultiLocationInventory_Model_CatalogInventory_Resource_Stock_Item _getResource()
 */
class Demac_MultiLocationInventory_Model_CatalogInventory_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    /**
     * Adding stock data to product
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function assignProduct(Mage_Catalog_Model_Product $product)
    {
        if (!$this->getId() || !$this->getProductId()) {
            $this->_getResource()->loadByProduct($this, $product);
            $this->setOrigData();
        }

        $this->setProduct($product);
        $product->setStockItem($this);

        $product->setIsInStock($this->getIsInStock());
        Mage::getSingleton('cataloginventory/stock_status')
            ->assignProduct($product, $this->getStockId(), $this->getStockStatus());
        return $this;
    }

    /**
     * Load item data by product
     *
     * @param   mixed $product
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function loadByProduct($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {

            $this->_getResource()
                ->loadByProduct($this, $product);

            $this->setOrigData();

            return $this;
        } else {
            return parent::loadByProduct($product);
        }
    }
}