<?php
/**
 * Class Demac_MultiLocationInventory_Model_Resource_Stock_Status_Index
 */
class Demac_MultiLocationInventory_Model_Resource_Stock_Status_Index
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Standard resource model init
     */
    protected function _construct()
    {
        $this->_init('demac_multilocationinventory/stock_status_index', 'product_id');
    }

    /**
     * Update core stock status.
     *
     * @param bool|array $productIds
     */
    public function updateCoreStockStatus($productIds = false)
    {
        $query = Mage::helper('demac_multilocationinventory/indexer')->getUpdateCoreStockStatusQuery($productIds);
        $this->_getWriteAdapter()->query($query);
    }

    /**
     * Update core stock item.
     *
     * @param bool|array $productIds
     */
    public function updateCoreStockItem($productIds = false)
    {
        $coreStockItemUpdateQuery = Mage::helper('demac_multilocationinventory/indexer')->getUpdateCoreStockItemQuery($productIds);
        $this->_getWriteAdapter()->query($coreStockItemUpdateQuery);
    }
    
    /**
     * _min_qty
     * 
     * (default value: array())
     * 
     * @var array
     * @access protected
     */
    protected $_min_qty = array();
    
    /**
     * markMinQtyOutOfStock function.
     * 
     * @access public
     * @param mixed $products
     * @return void
     */
    public function markMinQtyOutOfStock($productIds)
    {
        $productFilter = '';
        if($productIds !== false) {
            $productFilter = 'AND dms.product_id IN(' . implode(',', $productIds) . ')';
        }
        
        if(!$this->_min_qty)
        {
            $_minQtysResource = Mage::getResourceModel('demac_multilocationinventory/location_collection')
                    ->addFieldToSelect(array('id','min_qty'))
                    ->addFieldToFilter('status', 1);
            foreach($_minQtysResource as $_minqtys)
            {
                $this->_min_qty[] = " ( dms.location_id = '" . $_minqtys->getId() . "' AND qty <= '" . $_minqtys->getMinQty() ."' )";
            }
        }
        
        if($this->_min_qty)
        {
            $query = "UPDATE " . $this->_getWriteAdapter()->getTableName('demac_multilocationinventory_stock') . " AS dms"
                    . " SET dms.is_in_stock = '0' WHERE dms.is_in_stock = '1' " . $productFilter
                    . " AND (" . implode(" OR ", $this->_min_qty) . ")" ;
            $this->_getWriteAdapter()->query($query);
        }
    }
    
    /**
     * Create missing multi location inventory stock rows.
     *
     * @param bool|array $productIds
     */
    public function createMissingStockRows($productIds)
    {
        $productFilter = '';
        if($productIds !== false) {
            $productFilter = ' AND e.entity_id IN(' . implode(',', $productIds) . ')';
        }
        $missingStockRowsQuery       = $this->_getWriteAdapter()
            ->select()
            ->from(
                array(
                    'e' => 'catalog_product_entity'
                ),
                array('product_id' => 'e.entity_id')
            )
            ->columns(new Zend_Db_Expr('0 as qty'))
            ->join(
                array(
                    'location' => 'demac_multilocationinventory_location'
                ),
                '',
                'location.id as location_id'
            )
            ->joinLeft(
                array(
                    'stock' => 'demac_multilocationinventory_stock'
                ),
                'e.entity_id = stock.product_id AND location.id = stock.location_id',
                ''
            )
            ->join(
                array(
                    'stores' => 'demac_multilocationinventory_stores'
                ),
                'location.id = stores.location_id',
                ''
            )
            ->where('stock_id IS NULL' . $productFilter)
            ->group('CONCAT(location.id, \'_\', e.entity_id)');
        $missingStockRowsQueryString = $missingStockRowsQuery->__toString();

        //Create empty stock rows
        $query =
            'INSERT INTO demac_multilocationinventory_stock (product_id, qty, location_id)'
            . ' (' . $missingStockRowsQueryString . ')';
        $this->_getWriteAdapter()->query($query);
    }

    /**
     * Create missing multi location inventory stock status index rows.
     *
     * @param bool|array $productIds
     */
    public function createMissingStockIndexRows($productIds)
    {
        $query =
            'INSERT INTO demac_multilocationinventory_stock_status_index (store_id, product_id, qty, backorders, is_in_stock)'
            . ' ('
            . ' SELECT'
            . '   store.store_id as store_id,'
            . '   e.entity_id as product_id,'
            . '   0 as qty,'
            . '   0 as backorders,'
            . '   0 as is_in_stock'
            . ' FROM catalog_product_entity AS e'
            . ' JOIN core_store AS store'
            . ' LEFT OUTER JOIN demac_multilocationinventory_stock_status_index as stock_idx'
            . '   ON e.entity_id = stock_idx.product_id'
            . '      AND store.store_id = stock_idx.store_id'
            . ' WHERE stock_idx.qty IS NULL';
        if(is_array($productIds)) {
            $query .= ' AND e.entity_id IN(' . implode(',', $productIds) . ')';
        }
        $query .=
            ' GROUP BY CONCAT(store.store_id, \'_\', e.entity_id)'
            . ')';
        $this->_getWriteAdapter()->query($query);
    }
}
