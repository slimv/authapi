<?php
/**
 * Trait which will be used for model who using Closure Table approach to handle tree relationship
 * https://coderwall.com/p/lixing/closure-tables-for-browsing-trees-in-sql
 *
 * Note: to use this trail require object need to have following data
 * - function getClosureModel()
 * - property table
 */

namespace App\Model\Traits;
use DB;
use Illuminate\Support\Facades\Log;
use VDateTime;

trait ClosureTableModelTrait
{
    /**
     * If set to true, this closure wont care about rank (order will using your selected field)
     * @return boolean [description]
     */
    public function isClosureRankDisabled()
    {
        return ((property_exists($this, 'disableClosureRank') && $this->disableClosureRank) ? $this->disableClosureRank : false);
    }

    /**
     * Get current model primary column name
     * @return [string] [Name of primary column]
     */
    public function getClosurePrimaryColumnName()
    {
        return ((property_exists($this, 'closurePrimaryColumnName') && $this->closurePrimaryColumnName) ? $this->closurePrimaryColumnName : 'id');
    }

    /**
     * Get the parent column name
     * @return [string] [Name of parent column]
     */
    public function getClosureParentColumn()
    {
        return ((property_exists($this, 'closureParentColumnName') && $this->closureParentColumnName) ? $this->closureParentColumnName : 'parent_id');
    }

    /**
     * similar with getClosureParentColumn but this one target the field which contain the actual object
     * @return [string] [Name of parent column]
     */
    public function getClosureParentObjectColumn()
    {
        return ((property_exists($this, 'closureParentObjectColumnName') && $this->closureParentObjectColumnName) ? $this->closureParentObjectColumnName : 'parent');
    }

    /**
     * Normally the target model will have a rank column to store its rank. Depend on the database design this field might be changed
     * so this function allow user to update this properties name
     * @return [string] [Rank column name]
     */
    public function getRankModelName()
    {
        return ((property_exists($this, 'closureRankColumnName') && $this->closureRankColumnName) ? $this->closureRankColumnName : 'rank');
    }

    /**
     * Rank value will have following format: 00000...0000[id].
     * The number of zero will be set by this function
     * @return [type] [description]
     */
    public function getRankZeroPadLength()
    {
        return ((property_exists($this, 'closureRankZeroPadLength') && $this->closureRankZeroPadLength) ? $this->closureRankZeroPadLength : 10);
    }

    /**
     * Rank will need a numeric column to used as target to generate rank value. By default it will use the id column. Set this value if 
     * you want to use different column
     * @return [string] [Rank column name]
     */
    public function getRankTargetColumn()
    {
        return ((property_exists($this, 'closureRankTargetColumnName') && $this->closureRankTargetColumnName) ? $this->closureRankTargetColumnName : 'id');
    }

    /**
     * Rank will need a numeric column to used as target to generate rank value. By default it will use the id column. Set this value if 
     * you want to use different column
     * @return [string] [Rank column name]
     */
    public function getSoftDeleteColumn()
    {
        return ((property_exists($this, 'closureSoftDeleteColumnName') && $this->closureSoftDeleteColumnName) ? $this->closureSoftDeleteColumnName : 'deleted_at');
    }

    /**
     * Get current model rank value, ignore the full path, only this
     * @return [type] [description]
     */
    public function getCurrentModelRankValue()
    {
        $zeroPad = $this->getRankZeroPadLength();
        $targetColumnName = $this->getRankTargetColumn();
        $targetColumnValue = (string)$this->$targetColumnName;

        if(!$targetColumnValue || $zeroPad < $targetColumnValue->length) {
            return null;
        } else {
            $remainRezoPad = $zeroPad - $targetColumnValue->length;
            $pad = '';
            for($i = 0; $i < $remainRezoPad; $i++) {
                $pad = $pad . '0';
            }

            return $pad . $targetColumnValue;
        }
    }

    /**
     * Set the closure rank for current model
     */
    public function setClosureRank()
    {
        //dont do anything if disabled
        if($this->isClosureRankDisabled()) {
            return true;
        }

        $parentColumn = $this->getClosureParentColumn();
        $parentObjectColumn = $this->getClosureParentObjectColumn();
        if(!$parentColumn) {
            return false;
        }
        $parentValue = $this->$parentColumn;

        $rankColumn = $this->getRankModelName();
        if(!$parentValue) {
            //this model is the root element, the ranking value will only have its own value
            $this->$rankColumn = $this->getCurrentModelRankValue();
            
            if(!$this->save()) {
                return false;
            }
        } else {
            $parent = $this->$parentObjectColumn;
            if($parent) {
                $this->$rankColumn = $parent->$rankColumn . '-' . $this->getCurrentModelRankValue();
                if(!$this->save()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the name of the table which contain the closure relationship
     * @return [string] [Table name]
     */
    public function getClosureTableName()
    {
        return ($this->closureTableName ? $this->closureTableName : ($this->table . '_closure'));
    }

    /**
     * This function generate the closure data for current model
     * @return [type] [description]
     */
    public function generateClosureData()
    {
        $closureTableName = $this->getClosureTableName();
        $primaryColumn = $this->getClosurePrimaryColumnName();
        $parentObjectColumn = $this->getClosureParentObjectColumn();
        $parentColumn = $this->getClosureParentColumn();
        $parentValue = $this->$parentColumn;

        //first we make sure there is no item with this model id
        DB::table($closureTableName)->where('ancestor_id', $this->$primaryColumn)->orWhere('descendant_id', $this->$primaryColumn)->delete();

        //second we will create the closure which point to itself first
        $closureToInsert = [
            [
                'ancestor_id' => $this->$primaryColumn, 
                'descendant_id' => $this->$primaryColumn,
                'depth' => 0
            ]
        ];

        //third, we create closure which point to above level (if there is)
        if(!$parentValue) {
            //this is root level, we only need to create a pointer to root (0)
            array_push($closureToInsert, [
                'ancestor_id' => 0, 
                'descendant_id' => $this->$primaryColumn,
                'depth' => 0
            ]);
        } else {
            //we get the parent closure value, increase rank by one, replace its descendant value to current model id
            $parentClosures = DB::table($closureTableName)->where('descendant_id', $parentValue)->get();
            if($parentClosures && count($parentClosures) > 0) {
                foreach($parentClosures as $parentClosure) {
                    $newData = [
                        'ancestor_id' => $parentClosure->ancestor_id, 
                        'descendant_id' => $this->$primaryColumn,
                        'depth' => $parentClosure->depth + 1
                    ];

                    array_push($closureToInsert, $newData);
                }
            }
        }

        if(count($closureToInsert) > 0) {
            DB::table($closureTableName)->insert($closureToInsert);
        }

        return true;
    }

    /**
     * Remove all relate closure
     */
    public function removeRelateClosure()
    {
        $closureTableName = $this->getClosureTableName();
        $primaryColumn = $this->getClosurePrimaryColumnName();
        $result = DB::table($closureTableName)->where('ancestor_id', $this->$primaryColumn)->orWhere('descendant_id', $this->$primaryColumn)->delete();
        return true;
    }

    /**
     * Return the current item id and its children id in array format
     * Note: The array also include its id
     * @return [array] [List of Id]
     */
    public function getClosureTreeIdList()
    {
        $primaryColumn = $this->getClosurePrimaryColumnName();
        $closureTableName = $this->getClosureTableName();

        //now we get id of children and this item
        $children = DB::table($closureTableName)->where('ancestor_id', $this->$primaryColumn)->orderBy('depth')->get();
        $childrenIds = $children->pluck('descendant_id')->toArray();

        return $childrenIds;
    }

    /**
     * Call this function when this model created to generate its data
     */
    public function onClosureModelCreated()
    {
        return $this->setClosureRank() && $this->generateClosureData();
    }

    /**
     * Call this function when this model is updated
     * @param  [array] $oldData [Old data]
     */
    public function onClosureModelUpdated($oldData)
    {
        DB::beginTransaction();
        try {
            $parentColumn = $this->getClosureParentColumn();
            if($oldData[$parentColumn] != $this->$parentColumn) {
                //remove all data
                $closureTableName = $this->getClosureTableName();
                $primaryColumn = $this->getClosurePrimaryColumnName();

                //get the children of this item, also update them
                $itemPrimaryList = $this->getClosureTreeIdList();
                //only do this if it is not this item
                if (($searchKey = array_search($this->$primaryColumn, $itemPrimaryList)) !== false) {
                    unset($itemPrimaryList[$searchKey]);
                }

                $result = DB::table($closureTableName)->where('ancestor_id', $this->$primaryColumn)->orWhere('descendant_id', $this->$primaryColumn)->delete();

                //generate new data
                $this->setClosureRank() && $this->generateClosureData();

                //remove all close sure
                $result = DB::table($closureTableName)->whereIn('ancestor_id', $itemPrimaryList)->orWhereIn('descendant_id', $itemPrimaryList)->delete();

                //now we get the sub item, need to order by depth because we need to generate item from the top to bottom
                $ids_ordered = implode(',', $itemPrimaryList);
                $subItems = DB::table($this->table)->whereIn($primaryColumn, $itemPrimaryList)->orderByRaw(DB::raw("FIELD(".$primaryColumn.", $ids_ordered)"))->get();

                //regenerate
                foreach($subItems as $subItem) {
                    $thisModel = $this->getClosureModel();
                    $modelSubItem = $thisModel->find($subItem->$primaryColumn);
                    $modelSubItem->setClosureRank() && $modelSubItem->generateClosureData();
                }

                DB::commit();

                return true;
            } else {
                DB::commit();
            }
        }
        catch(\Exception $e) {
            Log::info(['asdasd'=>$e->getMessage()]);
            DB::rollBack();
            return false;
        }
    }

    /**
     * Call this function when this model is forced delete
     */
    public function onClosureModelDeleted()
    {   
        //first we remove relate closure, then we remove its childrent
        return true;
    }

    /**
     * Call this function when this model is forced deleting
     */
    public function onClosureModelDeleting()
    {
        DB::beginTransaction();
        try {
            //get all children
            $primaryColumn = $this->getClosurePrimaryColumnName();
            $itemPrimaryList = $this->getClosureTreeIdList();
            $closureTableName = $this->getClosureTableName();

            //remove all children
            $result = DB::table($this->table)->whereIn($primaryColumn, $itemPrimaryList)->delete();

            //remove all closure
            $result = DB::table($closureTableName)->whereIn('ancestor_id', $itemPrimaryList)->orWhereIn('descendant_id', $itemPrimaryList)->delete();

            $this->removeRelateClosure();

            DB::commit();
            return true;
        }
        catch(\Exception $e) {
            DB::rollBack();
            return false;
        }

        //get all children
        $primaryColumn = $this->getClosurePrimaryColumnName();
        $itemPrimaryList = $this->getClosureTreeIdList();
        
        //first we remove relate closure, then we remove its childrent
        return $this->removeRelateClosure();
    }

    /**
     * Call this function when this model is soft deleted
     */
    public function onClosureModelSoftDeleted()
    {
        //get sub items, soft delete those
        //first we get children items
        $itemPrimaryList = $this->getClosureTreeIdList();
        $primaryColumnName = $this->getClosurePrimaryColumnName();
        $softDeleteColumnName = $this->getSoftDeleteColumn();

        //update
        $result = DB::table($this->table)->whereIn($primaryColumnName, $itemPrimaryList)->update([$softDeleteColumnName => VDateTime::now()]);
    }

    /**
     * Call this function when this model is restored
     */
    public function onClosureModelRestored()
    {
        //get sub items, soft delete those
        //first we get children items
        $itemPrimaryList = $this->getClosureTreeIdList();
        $primaryColumnName = $this->getClosurePrimaryColumnName();
        $softDeleteColumnName = $this->getSoftDeleteColumn();

        //update
        $result = DB::table($this->table)->whereIn($primaryColumnName, $itemPrimaryList)->update([$softDeleteColumnName => null]);
    }

    /**
     * Function to get a item total number of children
     * @return [int] [Total children]
     */
    public function getClosureTotalChildren($onlyDirectChild = false)
    {
        $primaryColumn = $this->getClosurePrimaryColumnName();
        $closureTableName = $this->getClosureTableName();
        if($onlyDirectChild) {
            //now we count the childrent (depth = 1 mean direct child) , ignore itself
            return DB::table($closureTableName)->where('ancestor_id', $this->$primaryColumn)->where('descendant_id', "<>", $this->$primaryColumn)->where('depth', 1)->count();
        } else {
            //now we count the childrent, ignore itself
            return DB::table($closureTableName)->where('ancestor_id', $this->$primaryColumn)->where('descendant_id', "<>", $this->$primaryColumn)->count();
        }
        
    }
}