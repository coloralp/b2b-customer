<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case CAN_VIEW_ALL_USERS = 'CAN VIEW ALL USERS';
    case CAN_CREATE_NEW_USER = 'CAN CREATE NEW USER';
    case CAN_UPDATE_EXISTS_USER = 'CAN UPDATE EXISTS USER';
    case CAN_LOOK_USER_DETAIL = 'CAN LOOK USER DETAIL';
    case CAN_DELETE_USER = 'CAN DELETE USER';
    case CAN_LOOK_ZIRAAT_EXTRES = 'CAN LOOK ZIRAAT EXTRES';
    case CAN_LOOK_KEBIR_PAGE = 'CAN LOOK KEBIR PAGE';
    case CAN_LOOK_GENERAL_ACCOUNT_PAGE = 'CAN LOOK GENERAL ACCOUNT PAGE';
    case CAN_SEE_FREQUENCY_DATA = 'CAN SEE FREQUENCY DATA';
    case CAN_CREATE_ROLE = 'CAN CREATE ROLE';
    case CAN_UPDATE_ROLE = 'CAN UPDATE ROLE';
    case CAN_DELETE_A_ROLE = 'CAN DELETE A ROLE';
    case CAN_LOOK_A_ROLE_PROPERTIES = 'CAN LOOK A ROLE PROPERTIES';
    case CAN_CREATE_PERMISSION = 'CAN CREATE PERMISSION';
    case CAN_UPDATE_PERMISSION = 'CAN UPDATE PERMISSION';
    case CAN_DELETE_A_PERMISSION = 'CAN DELETE A PERMISSION';
    case CAN_LOOK_A_PERMISSION_PROPERTIES = 'CAN LOOK A PERMISSION PROPERTIES';
    case ADD_NEW_PERMISSION_TO_USER = 'ADD NEW PERMISSION TO USER';
    case CAN_CREATE_CATEGORY = 'CAN CREATE CATEGORY';
    case DELETE_A_CATEGORY = 'DELETE A CATEGORY';
    case CAN_LOOK_CATEGORY = 'CAN LOOK CATEGORY';
    case CAN_CREATE_REGION = 'CAN CREATE REGION';
    case DELETE_A_REGION = 'DELETE A REGION';
    case CAN_LOOK_REGION = 'CAN LOOK REGION';
    case CAN_CREATE_PUBLISHER = 'CAN CREATE PUBLISHER';
    case DELETE_A_PUBLISHER = 'DELETE A PUBLISHER';
    case CAN_LIST_ALL_PUBLISHERS = 'CAN LIST ALL PUBLISHERS';
    case CAN_LOOK_PUBLISHER = 'CAN LOOK PUBLISHER';
    case CAN_UPDATE_PUBLISHER = 'CAN UPDATE PUBLISHER';
    case CAN_CREATE_LANGUAGE = 'CAN CREATE LANGUAGE';
    case DELETE_A_LANGUAGE = 'DELETE A LANGUAGE';
    case CAN_LIST_ALL_LANGUAGES = 'CAN LIST ALL LANGUAGES';
    case CAN_LOOK_LANGUAGE = 'CAN LOOK LANGUAGE';
    case CAN_CREATE_SUPPLIER = 'CAN CREATE SUPPLIER';
    case DELETE_A_SUPPLIER = 'DELETE A SUPPLIER';
    case CAN_LIST_ALL_SUPPLIERS = 'CAN LIST ALL SUPPLIERS';
    case CAN_LOOK_SUPPLIER = 'CAN LOOK SUPPLIER';
    case CAN_UPDATE_SUPPLIER = 'CAN UPDATE SUPPLIER';
    case CAN_CREATE_CUSTOMER = 'CAN CREATE CUSTOMER';
    case DELETE_A_CUSTOMER = 'DELETE A CUSTOMER';
    case CAN_LIST_ALL_CUSTOMERS = 'CAN LIST ALL CUSTOMERS';
    case CAN_LOOK_CUSTOMER = 'CAN LOOK CUSTOMER';
    case CAN_UPDATE_CUSTOMER = 'CAN UPDATE CUSTOMER';
    case CAN_VERIFY_CUSTOMER = 'CAN VERIFY CUSTOMER';
    case CAN_CREATE_GAME = 'CAN CREATE GAME';
    case DELETE_A_GAME = 'DELETE A GAME';
    case CAN_LIST_ALL_GAMES = 'CAN LIST ALL GAMES';
    case CAN_UPDATE_GAME = 'CAN UPDATE GAME';
    case CAN_SEE_LAST_STOCK_UPDATES = 'CAN SEE LAST STOCK UPDATES';
    case CAN_STOCK_EXPORT = 'CAN STOCK EXPORT';
    case CAN_SEE_STOCKS = 'CAN SEE STOCKS';
    case CAN_CREATE_OFFER = 'CAN CREATE OFFER';
    case DELETE_A_OFFER = 'DELETE A OFFER';
    case CAN_LIST_ALL_OFFERS = 'CAN LIST ALL OFFERS';
    case CAN_UPDATE_OFFER = 'CAN UPDATE OFFER';
    case CAN_ADD_MONEY_TO_OFFER = 'CAN ADD MONEY TO OFFER';
    case CAN_CREATE_KEY = 'CAN CREATE KEY';
    case DELETE_A_KEY = 'DELETE A KEY';
    case CAN_LIST_ALL_KEYS = 'CAN LIST ALL KEYS';
    case CAN_UPDATE_KEY = 'CAN UPDATE KEY';
    case CAN_ADD_MONEY_TO_KEY = 'CAN ADD MONEY TO KEY';
    case CAN_ARCHIVE_OR_RESTORE_THE_KEYS = 'CAN ARCHIVE OR RESTORE THE KEYS';
    case CAN_DO_FOLLOW_KEY = 'CAN DO FOLLOW KEY';
    case CAN_DELETE_COLLECT_KEYS = 'CAN DELETE COLLECT KEYS';
    case CAN_EXPORT_KEYS = 'CAN EXPORT KEYS';
    case CAN_DELETE_MULTIPLE_KEYS = 'CAN DELETE MULTIPLE KEYS';
    case CAN_CHECK_FREQUENCY_DATA = 'CAN CHECK FREQUENCY DATA';
    case CAN_SEE_KEBIR_PAGE = 'CAN SEE KEBIR PAGE';
    case CAN_SEE_KDV_AMOUNTS_PER_MONTH = 'CAN SEE KDV AMOUNTS PER MONTH';
    case CAN_CHANGE_MARKETPLASE_SETTINGS = 'CAN CHANGE MARKETPLASE SETTINGS';
    case CAN_LIST_ALL_MATCHES_GAME = 'CAN LIST ALL MATCHES GAME';
    case CAN_MATCH_A_GAME_WITH_A_VENDOR = 'CAN MATCH A GAME WITH A VENDOR';
    case CAN_CREATE_ORDER = 'CAN CREATE ORDER';
    case CAN_SHOW_DETAIL_ORDER = 'CAN SHOW DETAIL ORDER';
    case DELETE_A_ORDER = 'DELETE A ORDER';
    case CAN_LIST_ALL_ORDERS = 'CAN LIST ALL ORDERS';
    case CAN_UPDATE_ORDER = 'CAN UPDATE ORDER';
    case CAN_GET_ORDER_DETAIL_ZIP = 'CAN GET ORDER DETAIL ZIP';
    case CAN_SEE_API_SALES = 'CAN SEE API SALES';
    case CAN_SEE_ETAIL_SALES = 'CAN SEE ETAIL SALES';
    case CAN_GET_EXPORT_ORDERS = 'CAN GET EXPORT ORDERS';
    case CAN_GET_EXPORT_ETAIL_ORDERS = 'CAN GET EXPORT ETAIL ORDERS';
    case CAN_SEE_ALL_NOTIFICATIONS = 'CAN SEE ALL NOTIFICATIONS';
    case CAN_MAKE_ANALYTIC_BY_GAME = 'CAN MAKE ANALYTIC BY GAME';
    case CAN_CREATE_MONEYBOX = 'CAN CREATE MONEYBOX';
    case CAN_DELETE_A_MONEYBOX = 'CAN DELETE A MONEYBOX';
    case CAN_LIST_ALL_MONEYBOXES = 'CAN LIST ALL MONEYBOXES';
    case CAN_UPDATE_MONEYBOX = 'CAN UPDATE MONEYBOX';
    case CAN_ADD_MONEY_TO_MONEYBOX = 'CAN ADD MONEY TO MONEYBOX';
    case CAN_LIS_ALL_TRANSACTIONS = 'CAN LIS ALL TRANSACTIONS';
    case CAN_LIST_TRANSACTIONS_TO_CHANGE_KEYS = 'CAN LIST TRANSACTIONS TO CHANGE KEYS';
    case CAN_UPDATE_KEYS_AS_GROUP = 'CAN UPDATE KEYS AS GROUP';
    case CAN_LIST_CUSTOMER_TRANSACTION_REQUEST = 'CAN LIST CUSTOMER TRANSACTION REQUEST';
    case CAN_UPDATE_TRANSACTION_REQUEST_FOR_CUSTOMER = 'CAN UPDATE TRANSACTION REQUEST FOR CUSTOMER';
    case CAN_LIST_EXPENSES = 'CAN LIST EXPENSES';
    case CAN_ADD_EXPENSE = 'CAN ADD EXPENSE';
    case CAN_ADD_DELETE_EXPENSE = 'CAN ADD DELETE EXPENSE';
    case CAN_UPDATE_EXPENSE = 'CAN UPDATE EXPENSE';

}