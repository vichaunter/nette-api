<?php
/**
 * Created by PhpStorm.
 * User: Victor
 * Date: 04/07/2018
 * Time: 14:02
 */

namespace VicHaunter\Nette\Api;

class ApiErrors {
    
    //User
    const USER_NOT_FOUND = ['code' => 10000, 'message' => "User not found"];
    const USER_CANNOT_SAVED = ['code' => 10001, 'message' => 'Cannot save user'];
    const USER_BAD_REMOTE_SYSTEM = ['code' => 10002, 'message' => 'Bad remote system'];
    
    //Orders
    const ORDER_NO_PRODUCTS_SET = ['code' => 20000, 'message' => 'No products set'];
    const ORDER_ERROR_SAVING = ['code' => 20001, 'message' => 'Error saving order'];
    const ORDER_NOT_FOUND = ['code' => 20002, 'message' => 'Not orders found'];
    const ORDER_ITEM_COUNT_LIMITED= ['code' => 20003, 'message' => 'Items count is limited'];
    
    //Products
}