<?php
namespace App\Model\Repository;

use App\Http\Model\Table\Support\RelationQuery;
use App\Model\CoreModel;
use App\Model\User;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Model\CommonResponse;
use Schema;
use Validator;

abstract class CoreRepository
{
    /**
     * 3 modes to decide how the retrieve function will display soft_deleted model or not
     * @var string 3 modes:
     * 'normal' will only return object with havent been soft deleted
     * 'trash' only display soft deleted object
     * 'all' display both
     */
    protected $soft_delete_mode = 'normal';

    /**
     * @var null Default order by value
     */
    protected $order_by = null;

    /**
     * Multi order
     * @var null
     */
    protected $order_by_fields = null;

    /**
     * @var null Default order direction value
     */
    protected $order_dir = 'asc';

    /**
     * @var int Default Limit value
     */
    protected $limit = 50;

    /**
     * The return mode of function items or similar which will decide the format of the return function
     * @var string The format of the return function will base on the following value:
     *  'paging': This is for paging request. return format: {
     *   total: [int] Total number of item
     *   items: [array] List of items (the number might not match the total since it can be only one page)
     *  }
     *
     * 'array': Return all item in single array. This will always return all items at once. Return format:
     * [item1, item2, ...]
     */
    protected $list_item_return_format = 'paging';

    /**
     * @var array List of table will be join with current table when query.
     * Format: [[target_table_name, target_table_name.pk_field, condition(=,!=,...), 'current_table_name.fk_field']]
     */
    protected $join = [];

    /**
     * When we want to sort using custom key, we will use this map to point that custom key to the actual key
     * @var array Map for the sorting. Format ['custom_sort_key' => 'target_key']
     */
    protected $sort_map = [];

    /**
     * When we want to filter using custom key, we will use this map to point that custom key to the actual key
     * @var array Map for the sorting. Format ['custom_map_key' => 'target_key']
     */
    protected $filter_map = [];

    /**
     * Any field in this list will be handle by custom filter instead of default
     * @var array
     */
    protected $custom_filter = [];

    /**
     * Property in this array will be perform deep search when filter with LIKE.
     * @var array
     */
    protected $deep_like_field = [];

    /**
     * Property in this array will be perform LIKE search with given value
     * @var array
     */
    protected $all_search_fields = [];

    /**
     * The requester who will perform the function in this object. The class will using this variable
     * to check for permission on each function call. 
     * If this variable is set to null then no permission check will be called. It will then let the called function
     * to decided if the missing of user will be reject or not?
     * 
     * @var User
     */
    protected $requester = null;

    /**
     * Keep array data when create for custom validation
     * @var array
     */
    protected $dataCreating;

    /**
     * Keep array data when update for custom validation
     * @var array
     */
    protected $dataUpdating;

    /**
     * Keep old data of object updated
     * @var Model
     */
    protected $oldObjectUpdated;

    /**
     * Only field in this list can be used to query items in Items() function.
     * This is to prevent public user from using items function to query unexpected data
     * Note: by default set this value to field that can be access by public user such as 'name'
     * For sensitive fields, only add them if they have permission
     * @var array
     */
    public $allowQueryFields = [];

    /**
     * This model will be mostly use for fetching and query
     * @param bool $origin If set to true, will return the class model. If false, will return model which usually using for fetching (and can include extra data)
     * @return CoreModel which model is working
     */
    public abstract function getModel($origin = false);

    /**
     * Get table name.
     * Normally the system will using the getModel function then get the name. In case this function does not work,
     * we will switch to this
     */
    public function getTableName()
    {
        return null;
    }

    /**
     * Get the rule when creating the object
     * @return [array|null] [Validation format of laravel]
     */
    public function getCreateRules()
    {
        return null;
    }

    /**
     * Get the rule when update the object
     * @return [array|null] [Validation format of laravel]
     */
    public function getUpdateRules()
    {
        return null;
    }

    /**
     * Get the validation message which will be used to return error message when validation happen
     */
    public function getValidationMessages()
    {
        return [
            "required" => __("This field is required"),
            "exists" => __("There is already item with this value"),
            "unique" => __("This value already be used by another"),
            "date" => __("Invalid date format"),
            "email" => __("Invalid email format"),
            "numeric" => __("Value have to be numeric"),
            "digits_between" => __("Value is outsite of allowed range"),
            "string" => __("Value have to be string"),
            "max" => __("Value is too long"),
            "min" => __("Value is too short")
        ];
    }

    /**
     * Set function for Requesters
     */
    public function setRequester($requester)
    {
        $this->requester = $requester;
    }

    public function allowViewSoftDeleted()
    {
        $this->soft_delete_mode = 'all';
    }
    public function allowViewSoftDeletedOnly()
    {
        $this->soft_delete_mode = 'trash';
    }

    public function disableViewSoftDeleted()
    {
        $this->soft_delete_mode = 'normal';
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Change the list format mode
     * @param [string] $mode [array or paging]
     */
    public function setListFormatMode($mode)
    {
        $this->list_item_return_format = $mode;
    }

    /**
     * Function to get the error object which will be used in data quering or CRUD
     * @return [stdClass] [Error object]
     */
    public function generateErrorObject()
    {   
        $report = new \stdClass();
        $report->success = false;
        $report->message = null;
        $report->data = null;
        $report->code = 500;
        $report->errors = null;
        return $report;  
    }

    /**
     * Assign custom filter to current model for selected field
     * @param $model    Eloquent model
     * @param $field    Filter field
     * @param $condition    Filter condtion (can be text, object,...)
     * @return null|Eloquent model
     */
    protected function parseCustomFilter($model, $field, $condition){
        return null;
    }

    /**
     * This function will read the data which is expected to be passed to items function
     * and only get the one that is match the allowed format
     * @param  [array] $data [Fetch configuration]
     * @return [array]       [The array data after remove unneccessary data]
     */
    public function fetchFilter($data)
    {
        $query = $data;
        if(isset($data['where'])) {
           $query['where'] =  json_decode($data['where'], true);
        }
        if(isset($data['orders'])) {
           $query['orders'] =  json_decode($data['orders'], true);
        }

        return $query;
    }

    /**
     * Extend of getModel, simply add trash state permission
     * @param  boolean $origin [description]
     * @return [type]          [description]
     */
    public function getStateModel($origin = false)
    {
        $model = $this->getModel($origin);

        if($this->soft_delete_mode == 'trash') {
            $model = $model->onlyTrashed();
        } elseif($this->soft_delete_mode == 'all') {
            $model = $model->withTrashed();
        }

        return $model;
    }

    /**
     * Function to get user from id, return null if not found
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function find($id)
    {
        try{
            if(!$id){
                return null;
            }

            $model = $this->getStateModel();

            $result = $model->find($id);

            if(!$result){
                throw new ModelNotFoundException();
                return null;
            }

            return $result;
        }
        catch(ModelNotFoundException $e){
            Log::error("Cannot found the object:", ['error' => $e->getMessage()]);
            throw new ModelNotFoundException($e->getMessage());
            return null;
        }
        catch(Exception $e){
            Log::error("Cannot fetch object due to following error:", ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
            return null;
        }
    }

    /**
     * Function to get user from id, return null if not found
     * @param  [array] $id [List of ids]
     * @param  [boolean] $allowOneNotFound [If true, even if one object is not found, it will still return the list. Otherwise it will return failed if one item is not found]
     * @return [type]     [description]
     */
    public function findIn($ids, $allowOneNotFound = false)
    {
        try{
            if(!$ids){
                return (new CommonResponse(400, [], __("Invalid data")));
            }

            $model = $this->getStateModel();

            $rows = $model->whereIn('id', $ids)->get();

            if($allowOneNotFound) {
                return (new CommonResponse(200, $rows));
            } else {
                if(count($rows) != count($ids)) {
                    return (new CommonResponse(400, [], __("Contain invalid id")));
                } else {
                    return (new CommonResponse(200, $rows));
                }
            }

            return $result;
        }
        catch(Exception $e){
            return (new CommonResponse(500, [], __("Unexpect error")));
        }
    }

    /**
     * Get the list of item for current model, base on the query
     * @param $params array contain config to get data. The format will be as following:
     * {
     *  filter {
     *   where: {
     *    field: {              //field will take the name of the atribute which will be used to compare. For example column "name" of table User
     *     like: 'value',
     *     lessOrEqual: number_value,
     *     moreOrEqual: number_value,
     *     more: number_value,
     *     less: number_value,
     *     equal: 'value',
     *     notEqual: 'value'
     *    }
     *   },
     *   orders: [
     *       "field1 asc",
     *       "field2 desc"
     *   ],
     *   limit: number_value,
     *   offset: number_value
     *  }
     * }
     * @param $extraCondition array contain extra condition for the query
     * @param bool $countOnly true if return total items only, false if return full data
     * @return mixed
     * @throws Exception
     */
    public function items($params, $extraCondition = null, $countOnly = false)
    {
        try{
            $query = $this->fetchFilter($params);

            $model = $this->getStateModel();

            if(method_exists($model, 'getTable')) {
                $tableName = $model->getTable();
            }
            else {
                $tableName = $this->getTableName();
            }
            
            //with
            if($this->join && count($this->join) > 0) {
                foreach($this->join as $join) {
                    if(count($join) == 4)
                        $model = $model->select($tableName.'.*')->leftJoin($join[0], $join[1], $join[2], $join[3]);
                }
            } else {
                $model = $model->select($tableName.'.*');
            }

            //conditions
            if(isset($query['where'])) {
                $model = $this->parseCondition($model, $query['where'], $tableName);
            }

            //extra condition
            if($extraCondition) {
                $model = $this->parseCondition($model, $extraCondition, $tableName);
            }

            //get total here
            $total = $model->count();

            if ($countOnly) {
                return [
                    'total' => $total
                ];
            }

            //order
            if(isset($query['orders']) && is_array($query['orders'])) {
                for($i = 0; $i < count($query['orders']); $i++) {
                    $iOrder = $query['orders'][$i];

                    $parts = explode(' ', $iOrder);
                    if(count($parts) > 1) {
                        $dir = (strtolower($parts[1]) === 'desc')?'desc':'asc';
                        $field = $parts[0];
                    } elseif(count($parts) == 1) {
                        $dir = 'asc';
                        $field = $parts[0];
                    }

                    //check the sort map
                    if(isset($this->sort_map[$field])) {
                        $field = $this->sort_map[$field];
                    }

                    if ($field instanceof RelationQuery) {
                       $field->order($model, $dir, $this->getTableName());
                    } else {
                        $model = $model->orderBy($field, $dir);
                    }
                }
            } else {
                if($this->order_by) {
                    $model = $model->orderBy($this->order_by, $this->order_dir);
                } else {
                    if($this->order_by_fields) {
                        for($i = 0; $i < count($this->order_by_fields); $i++) {
                            $iOrder = $this->order_by_fields[$i];

                            if(count($iOrder) > 1) {
                                $dir = (strtolower($iOrder[1]) === 'desc')?'desc':'asc';
                                $field = $iOrder[0];
                            } elseif(count($iOrder) == 1) {
                                $dir = 'asc';
                                $field = $iOrder[0];
                            }

                            $model = $model->orderBy($field, $dir);
                        }
                    }
                }
            }

            //limit
            $noLimit = false;
            if(isset($query['limit'])) {
                if($query['limit'] < 0) {
                    //no limit
                    $noLimit = true;
                } else {
                    $model = $model->limit($query['limit']);
                }
            } else {
                if($this->limit > 0) {
                    $model = $model->limit($this->limit);
                } else {
                    $noLimit = true;
                }
            }

            //offset
            if(isset($query['offset']) && $noLimit == false) {
                $offset = intval($query['offset']);
                $model = $model->offset($offset);
            }

            $items = $model->get();
            $items = $this->parseItemsAfterFetch($items);

            if($this->list_item_return_format == 'array') {
                $result = $items;
            } else {
                $result = [
                    'total' => $total,
                    'items' => $items,
                ];
            }

            return $result;
        }
        catch(Exception $e) {
            Log::error("Cannot fetch object due to following error:", ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
            return null;
        }
    }

    /**
     * Override this function to update items after fetching
     * @param  [type] $items [description]
     * @return [type]        [description]
     */
    public function parseItemsAfterFetch($items)
    {
        return $items;
    }

    /**
     * Parse the condition for the given query
     * @param $model    The model object
     * @param $conditions   Condition dict
     * @param $tableName    Name of the working table
     * @return Eloquent|null
     */
    public function parseCondition($model, $conditions, $tableName)
    {
        if(isset($conditions)) {
            foreach($conditions as $subCondition) {
                $field = isset($subCondition['field']) ? $subCondition['field'] : null;
                $op = isset($subCondition['op']) ? $subCondition['op'] : null;
                $value = isset($subCondition['value']) ? $subCondition['value'] : null;
                $whereType = isset($subCondition['type']) ? $subCondition['type'] : 'and';

                if(!$field || ($field != 'all' && !$op) || !$whereType || ($whereType != 'and' && $whereType != 'or') ) {
                    continue;
                }

                if($field != 'all' && !in_array($field, $this->allowQueryFields)) {
                    continue;
                }

                $condition = [];
                $condition[$op] = $value;

                if($field && $condition) {
                    //check the custom filter first
                    if(in_array($field, $this->custom_filter)) {
                        $customModel = $this->parseCustomFilter($model, $field, $condition);
                        if($customModel) {
                            $model = $customModel;
                        }
                    } elseif($field == 'all') {
                        //this is the case when user want to search for everything that match the given value. We will have to check all fields that
                        //is set to be used in "all" case
                        if($condition && count($this->all_search_fields) > 0) {
                            $allFields = $this->all_search_fields;
                            $model->where(function($query) use ($allFields, $value, $tableName, $model){
                                for($i = 0; $i < count($allFields); $i++) {
                                    if(in_array($allFields[$i], $this->custom_filter)) {
                                        $iField = $allFields[$i];
                                        $query->orWhere(function($queryCustomDeep) use ($iField, $value){
                                            $customModel = $this->parseCustomFilter($queryCustomDeep, $iField, ['like' => $value]);
                                            if($customModel) {
                                                $queryCustomDeep = $customModel;
                                            }
                                        });
                                    } else if(is_string($allFields[$i])) {
                                        $isDeepSearch = false;

                                        if(in_array($allFields[$i], $this->deep_like_field)) {
                                            $isDeepSearch = true;
                                        }

                                        if(strpos($allFields[$i], ".") === false) {
                                            $iField = $tableName . '.' . $allFields[$i];
                                        } else {
                                            $iField = $allFields[$i];
                                        }

                                        if($isDeepSearch) {
                                            $parts = explode(' ', $value);
                                            $query->orWhere(function($queryDeep) use ($iField, $parts) {
                                                foreach($parts as $part) {
                                                    $queryDeep->where($iField, 'like','%'.$part.'%');
                                                }
                                            });
                                        } else {
                                            $query->orWhere($iField, 'like','%'.$value.'%');
                                        }
                                    } else if ($allFields[$i] instanceof RelationQuery) {
                                        $allFields[$i]->searchAll($model, $query, $value, $this->getTableName());
                                    }
                                }
                            });
                        }
                    } else {
                        //check the map
                        if(isset($this->filter_map[$field])) {
                            $field = $this->filter_map[$field];
                        }

                        //if field dont have ., we will connect current table name into it
                        if(strpos($field, ".") === false) {
                            $field = $tableName . '.' . $field;
                        }

                        if(isset($condition['like'])) {
                            $value = $condition['like'];

                            if(in_array($field, $this->deep_like_field)) {
                                $parts = explode(' ', $value);
                                foreach($parts as $part) {
                                    if($whereType == 'or') {
                                        $model->orWhere($field, 'like','%'.$part.'%');
                                    } else {
                                        $model->where($field, 'like','%'.$part.'%');
                                    }
                                }
                            } else {
                                if($whereType == 'or') {
                                    $model->orWhere($field, 'like','%'.$value.'%');
                                } else {
                                    $model->where($field, 'like','%'.$value.'%');
                                }
                            }
                        }

                        if(isset($condition['lessOrEqual'])) {
                            $value = $condition['lessOrEqual'];
                            if($whereType == 'or') {
                                $model->orWhere($field, '<=',$value);
                            } else {
                                $model->where($field, '<=',$value);
                            }
                        }

                        if(isset($condition['moreOrEqual'])) {
                            $value = $condition['moreOrEqual'];
                            if($whereType == 'or') {
                                $model->orWhere($field, '>=',$value);
                            } else {
                                $model->where($field, '>=',$value);
                            }
                        }

                        if(isset($condition['more'])) {
                            $value = $condition['more'];
                            if($whereType == 'or') {
                                $model->orWhere($field, '>',$value);
                            } else {
                                $model->where($field, '>',$value);
                            }
                        }

                        if(isset($condition['less'])) {
                            $value = $condition['less'];
                            if($whereType == 'or') {
                                $model->orWhere($field, '<',$value);
                            } else {
                                $model->where($field, '<',$value);
                            }
                        }

                        if(isset($condition['equal'])) {
                            $value = $condition['equal'];
                            if($whereType == 'or') {
                                $model->orWhere($field, $value);
                            } else {
                                $model->where($field, $value);
                            }
                        }

                        if(isset($condition['notEqual'])) {
                            $value = $condition['notEqual'];
                            if($whereType == 'or') {
                                $model->orWhere($field, '<>',$value);
                            } else {
                                $model->where($field, '<>',$value);
                            }
                        }

                        if($op == 'null') {
                            if($whereType == 'or') {
                                $model->orWhereNull($field);
                            } else {
                                $model->whereNull($field);
                            }
                        }

                        if($op == 'notNull') {
                            if($whereType == 'or') {
                                $model->orWhereNotNull($field);
                            } else {
                                $model->whereNotNull($field);
                            }
                        }

                        if(isset($condition['in'])) {
                            $value = $condition['in'];
                            $parseValue = null;
                            if(is_array($value)) {
                                $parseValue = $value;
                            } else {
                                $parseValue = json_decode($value);
                                if(!is_array($parseValue)) $parseValue = [];
                            }
                            if($whereType == 'or') {
                                $model->orWhereIn($field, $parseValue);
                            } else {
                                $model->whereIn($field, $parseValue);
                            }
                        }

                        if(isset($condition['notIn'])) {
                            $value = $condition['notIn'];
                            $parseValue = null;
                            if(is_array($value)) {
                                $parseValue = $value;
                            } else {
                                $parseValue = json_decode($value);
                                if(!is_array($parseValue)) $parseValue = [];
                            }
                            if($whereType == 'or') {
                                $model->orWhereNotIn($field, $parseValue);
                            } else {
                                $model->whereNotIn($field, $parseValue);
                            }
                        }
                    }
                }
            }
        }

        return $model;
    }

    /**
     * This function will help remove the selected items.
     * Note: this function only work if the model have the function remove() and lock() inherited
     * @param  [array]  $items            [List of items will be removed]
     * @param  boolean $permanent        [true if the item will be removed completly]
     * @param  boolean $recoveryIfFailed [if set to true, a single faise will cause the whole process to be revert to previous state. If false, we will report which one success, which one failed]
     * @param string $reason [reason why this item got lock]
     * @return [dict | null]                    [{
     *  totalSuccess: number
     *  totalFailure: number
     *  status: 'applied' or 'reverted',  //applied if the success ones have been applied. Revert if the data have been revert to before the function call (no thing updated)
     *  report: {
     *   scrubId: null or string.[null if delete success, string if failed],
     *   scrubId: null or string.[null if delete success, string if failed]
     *  }
     * }]
     * If the result result is null, it mean the input data is invalid
     */
    public function delete($items, $permanent = false, $recoveryIfFailed = false, $reason = null){
        try{
            if(!$items || count($items) == 0){
                return (new CommonResponse(400, [], __("Request cannot be processed due to contain invalid data")));
            }

            DB::beginTransaction();

            $haveFailure = false;
            $reports = [
                'totalSuccess' => 0,
                'totalFailure' => 0,
                'report' => []
            ];

            foreach ($items as $item) {
                $result = null;

                if($permanent) {
                    if(method_exists($item, 'remove')) {
                        try{
                            $result = $item->remove();
                        }
                        catch(Exception $ex) {
                            $result = $ex->getMessage();
                        }
                    } else {
                        Log::error("Cannot remove item since this item dont have remove function override", ['item' => $item]);
                        $result = __("Cannot remove selected item due to internal error.");
                    }
                } else {
                    if(method_exists($item, 'lock')) {
                        try{
                            $result = $item->lock($reason);
                        }
                        catch(Exception $ex) {
                            $result = $ex->getMessage();
                        }
                    } else {
                        Log::error("Cannot lock item since this item dont have lock function override", ['item' => $item]);
                        $result = __("Cannot lock selected item due to internal error.");
                    }
                }

                $reports['report'][$item->getPublicIdentity()] = $result;
                if($result) {
                    $haveFailure = true;
                    $reports['totalFailure']++;
                } else {
                    $reports['totalSuccess']++;
                }
            }

            if($haveFailure && $recoveryIfFailed) {
                $reports['totalSuccess'] = 0;           //since no thing is applied
                DB::rollBack();
            } else {
                DB::commit();
            }

            return (new CommonResponse(200, $reports));
        }
        catch(Exception $e){
            return (new CommonResponse(500, [], __("Request cannot be processed due to unexpected data")));
        }
    }

    /**
     * Function to recovery deleted items from deleted state
     * @param  [array]  $items            [List of items will be unlocked]
     * @param  boolean $recoveryIfFailed [if set to true, a single faise will cause the whole process to be revert to previous state. If false, we will report which one success, which one failed]
     * @param  string $reason [reason why this item got unlock]
     * @return [dict | null]                    [{
     *  totalSuccess: number
     *  totalFailure: number
     *  status: 'applied' or 'reverted',  //applied if the success ones have been applied. Revert if the data have been revert to before the function call (no thing updated)
     *  report: {
     *   scrubId: null or string.[null if delete success, string if failed],
     *   scrubId: null or string.[null if delete success, string if failed]
     *  }
     * }]
     * If the result result is null, it mean the input data is invalid
     */
    public function recovery($items, $recoveryIfFailed = false, $reason = null)
    {
        try{
            if(!$items || count($items) == 0){
                return (new CommonResponse(400, [], __("Request cannot be processed due to contain invalid data")));
            }

            DB::beginTransaction();

            $haveFailure = false;
            $reports = [
                'totalSuccess' => 0,
                'totalFailure' => 0,
                'report' => []
            ];

            foreach ($items as $item) {
                $result = null;

                if(method_exists($item, 'unlock')) {
                    try{
                        $result = $item->unlock($reason);
                    }
                    catch(Exception $ex) {
                        $result = $ex->getMessage();
                    }
                } else {
                    Log::error("Cannot unlock item since this item dont have unlock function override", ['item' => $item]);
                    $result = __("Cannot unlock selected item due to internal error.");
                }

                $reports['report'][$item->getPublicIdentity()] = $result;
                if($result) {
                    $haveFailure = true;
                    $reports['totalFailure']++;
                } else {
                    $reports['totalSuccess']++;
                }
            }

            if($haveFailure && $recoveryIfFailed) {
                $reports['totalSuccess'] = 0;           //since no thing is applied
                DB::rollBack();
            } else {
                DB::commit();
            }

            return (new CommonResponse(200, $reports));
        }
        catch(Exception $e){
            return (new CommonResponse(500, [], __("Request cannot be processed due to unexpected data")));
        }
    }

    /**
     * Create new item with given data
     * @param $data array set (in array format)
     * @param array $niceNames a mapping between name of field in database and meaningful name to return to client
     * @return \stdClass [object] [object content the result:
     *  success: [boolean] [true if creating success]
     *  code: [int] [200 if success. other if there is error]
     *  message: [string] [error message]
     *  data: [object | null] The created object if success
     *  errors: [array] List of invalidate message if failed to created. If status code is 400, this is a dict error. Other case it will only be a message
     * ]
     */
    public function create($data, $niceNames = null){
        $this->dataCreating = $data;

        try{
            if(!$data) {
                return (new CommonResponse(400, [], __("Create request cannot be processed due to contain invalid data")));
            }

            $this->dataCreating = $data;
            $createRules = $this->getCreateRules();
            if($createRules) {
                //validate
                $messages = $this->getValidationMessages();
                $validator = Validator::make($data, $createRules, $messages);
                if ($niceNames) {
                    $validator->setAttributeNames($niceNames);
                }

                if ($validator->fails()) {
                    return (new CommonResponse(400, $validator->errors(), __("Create request cannot be processed due to contain invalid data")));
                }
            }

            $item = $this->getModel(true);

            foreach($data as $propertyName => $value){
                // we will only update property that this object have, and ignore the rest. Also we will ignore primary key too
                // And we will only accept primary data only
                if($propertyName !== 'id' && $propertyName !== 'scrub_id'){
                    try{
                        if(Schema::hasColumn($item->getTable(), $propertyName)){
                            $item->$propertyName = $value;
                        }
                    }
                    catch(Exception $e){
                        //ignore and carry on
                    }
                }
            }

            $createResult = $item->save();

            if($createResult) {
                return (new CommonResponse(200, $item));
            } else {
                return (new CommonResponse(500, [], __("Create request cannot be processed due to unexpected error")));
            }
        }
        catch(Exception $e){
            return (new CommonResponse(500, $e->getMessage(), __("Create request cannot be processed due to unexpected error")));
        }
    }

    /**
     * Update selected item with given data
     * @param $item Model The item will be updated
     * @param $data array set (in array format)
     * @param array $niceNames a mapping between name of field in database and meaningful name to return to client
     * @return [object] [object content the result:
     *  success: [boolean] [true if update success]
     *  code: [int] [200 if success. other if there is error]
     *  message: [string] [error message]
     *  data: [object | null] The updated object if success
     *  errors: [array] List of invalidate message if failed to update. If status code is 400, this is a dict error. Other case it will only be a message
     * ]
     */
    public function update($item, $data, $niceNames = null){
        $this->dataUpdating = $data;
        $this->oldObjectUpdated = clone $item;

        try{
            if(!$item || !$data){
                return (new CommonResponse(400, [], __("Update request cannot be processed due to contain invalid data")));
            }

            //remove unmodifiable data
            if(isset($data['updated_at'])) {
                unset($data['updated_at']);
            }
            if(isset($data['created_at'])) {
                unset($data['created_at']);
            }
            if(isset($data['id'])) {
                unset($data['id']);
            }
            if(isset($data['scrub_id'])) {
                unset($data['scrub_id']);
            }

            $updateRules = $this->getUpdateRules();
            if($updateRules) {
                //validate
                $messages = $this->getValidationMessages();
                $validator = Validator::make($data, $updateRules, $messages);
                if ($niceNames) {
                    $validator->setAttributeNames($niceNames);
                }

                if ($validator->fails()) {
                    return (new CommonResponse(400, $validator->errors(), __("Update request cannot be processed due to contain invalid data")));
                }
            }

            foreach($data as $propertyName => $value){
                // we will only update property that this object have, and ignore the rest. Also we will ignore primary key too
                // And we will only accept primary data only
                if($propertyName !== 'id'){
                    try{
                        if(Schema::hasColumn($item->getTable(), $propertyName)){
                            $item->$propertyName = $value;
                        }
                    }
                    catch(Exception $e){
                        //ignore and carry on
                    }
                }
            }

            $updateResult = $item->save();

            if($item->id) {
                $item = $this->find($item->id);
            }

            if($updateResult) {
                return (new CommonResponse(200, $item));
            } else {
                return (new CommonResponse(500, [], __("Update request cannot be processed due to unexpected error")));
            }
        }
        catch(Exception $e){
            die(var_dump($e->getMessage()));
            return (new CommonResponse(500, [], __("Update request cannot be processed due to unexpected data")));
        }
    }

    public function getOldObjectUpdated()
    {
        return $this->oldObjectUpdated;
    }
}