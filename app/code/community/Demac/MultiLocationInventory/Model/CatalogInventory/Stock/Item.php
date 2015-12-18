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