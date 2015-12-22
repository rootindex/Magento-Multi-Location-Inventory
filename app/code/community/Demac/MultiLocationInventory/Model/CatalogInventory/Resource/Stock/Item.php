<?php
/**
 * Class Demac_MultiLocationInventory_Model_CatalogInventory_Resource_Stock_Item
 */
class Demac_MultiLocationInventory_Model_CatalogInventory_Resource_Stock_Item extends Mage_CatalogInventory_Model_Resource_Stock_Item
{

    /**
     * Loading stock item data by product
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $item
     * @param int                                    $productId
     * @param int                                    $storeId
     *
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
    private function _loadByProductAndStoreId(Mage_CatalogInventory_Model_Stock_Item $item, $productId, $storeId)
    {
        $arrDefaultData = array(
            'product_id'                  => $productId,
            'stock_id'                    => 0,
            'qty'                         => 0.0000,
            'min_qty'                     => 0.0000,
            'use_config_min_qty'          => 1,
            'is_qty_decimal'              => 0,
            'backorders'                  => 0,
            'use_config_backorders'       => 1,
            'min_sale_qty'                => 1.0000,
            'use_config_min_sale_qty'     => 1,
            'max_sale_qty'                => 0.0000,
            'use_config_max_sale_qty'     => 1,
            'is_in_stock'                 => 0,
            'low_stock_date'              => null,
            'notify_stock_qty'            => null,
            'use_config_notify_stock_qty' => 1,
            'manage_stock'                => 0,
            'use_config_manage_stock'     => 1,
            'stock_status_changed_auto'   => 0,
            'use_config_qty_increments'   => 1,
            'qty_increments'              => 0.0000,
            'use_config_enable_qty_inc'   => 1,
            'enable_qty_increments'       => 0,
            'is_decimal_divided'          => 0
        );

        // Only configurable products seem to be in the
        $select = $this->_getLoadSelect('product_id', $productId, $item)
            ->where('stock_id = :stock_id');

        $data   = $this->_getReadAdapter()
            ->fetchRow($select, array(':stock_id' => $item->getStockId()));

        if(empty($data)) {
            $data = $arrDefaultData;
        }

        $stockStatusCollection = Mage::getModel('demac_multilocationinventory/stock_status_index')
            ->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('store_id', $storeId);

        $stockStatus = $stockStatusCollection->getFirstItem();

        if ($data && $stockStatus->getId()) {
            $data['qty']          = $stockStatus->getQty();
            $data['backorders']   = $stockStatus->getBackorders();
            $data['is_in_stock']  = $stockStatus->getIsInStock();
            $data['manage_stock'] = $stockStatus->getManageStock();
            //@TODO support use_config_backorders
            //$data['use_config_backorders'] = 1;//override...
            //@TODO support use_config_manage_stock
            //$data['use_config_manage_stock'] = 1;
            $item->setData($data);
        }

        $this->_afterLoad($item);

        return $this;
    }


    /**
     * Loading stock item data by product
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $item
     * @param Mage_Catalog_Model_Product             $product
     *
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
    public function loadByProduct(Mage_CatalogInventory_Model_Stock_Item $item, Mage_Catalog_Model_Product $product)
    {
        return $this->_loadByProductAndStoreId($item, $product->getId(), $product->getStoreId());
    }


    /**
     * Loading stock item data by product
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $item
     * @param int                                    $productId
     *
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
    public function loadByProductId(Mage_CatalogInventory_Model_Stock_Item $item, $productId)
    {
        $storeId = Mage::app()->getStore()->getId();

        return $this->_loadByProductAndStoreId($item, $productId, $storeId);
    }


    /**
     * Add join for catalog in stock field to product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $productCollection
     *
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
    public function addCatalogInventoryToProductCollection($productCollection)
    {
        $productCollection->getSelect()->joinLeft(
            array('cisi' => Mage::getSingleton('core/resource')->getTableName('demac_multilocationinventory/stock_status_index')),
            'e.entity_id = cisi.product_id' .
            ' AND cisi.store_id = ' . Mage::app()->getStore()->getId(),
            array(
                'is_saleable'        => 'is_in_stock',
                'inventory_in_stock' => 'is_in_stock'
            )
        );

        return $this;
    }

}