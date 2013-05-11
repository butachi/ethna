#!/bin/bash


find . -name '*php' -type f -exec sed -i ''  's/ETHNA_VERSION/Ethna_Const::ETHNA_VERSION/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/LANG_EN/Ethna_Const::LANG_EN/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/LANG_JA/Ethna_Const::LANG_JA/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/GATEWAY_WWW/Ethna_Const::GATEWAY_WWW/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/GATEWAY_CLI/Ethna_Const::GATEWAY_CLI/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/GATEWAY_XMLRPC/Ethna_Const::GATEWAY_XMLRPC/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/GATEWAY_SOAP/Ethna_Const::GATEWAY_SOAP/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/DB_TYPE_RW/Ethna_Const::DB_TYPE_RW/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/DB_TYPE_RO/Ethna_Const::DB_TYPE_RO/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/DB_TYPE_MISC/Ethna_Const::DB_TYPE_MISC/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/VAR_TYPE_INT/Ethna_Const::VAR_TYPE_INT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/VAR_TYPE_FLOAT/Ethna_Const::VAR_TYPE_FLOAT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/VAR_TYPE_STRING/Ethna_Const::VAR_TYPE_STRING/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/VAR_TYPE_DATETIME/Ethna_Const::VAR_TYPE_DATETIME/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/VAR_TYPE_BOOLEAN/Ethna_Const::VAR_TYPE_BOOLEAN/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/VAR_TYPE_FILE/Ethna_Const::VAR_TYPE_FILE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/FORM_TYPE_TEXT/Ethna_Const::FORM_TYPE_TEXT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/FORM_TYPE_PASSWORD/Ethna_Const::FORM_TYPE_PASSWORD/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/FORM_TYPE_TEXTAREA/Ethna_Const::FORM_TYPE_TEXTAREA/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/FORM_TYPE_SELECT/Ethna_Const::FORM_TYPE_SELECT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/FORM_TYPE_RADIO/Ethna_Const::FORM_TYPE_RADIO/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/FORM_TYPE_CHECKBOX/Ethna_Const::FORM_TYPE_CHECKBOX/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/FORM_TYPE_SUBMIT/Ethna_Const::FORM_TYPE_SUBMIT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/FORM_TYPE_FILE/Ethna_Const::FORM_TYPE_FILE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/FORM_TYPE_BUTTON/Ethna_Const::FORM_TYPE_BUTTON/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/FORM_TYPE_HIDDEN/Ethna_Const::FORM_TYPE_HIDDEN/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_GENERAL/Ethna_Const::E_GENERAL/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_DB_CONNECT/Ethna_Const::E_DB_CONNECT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_DB_NODSN/Ethna_Const::E_DB_NODSN/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_DB_QUERY/Ethna_Const::E_DB_QUERY/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_DB_DUPENT/Ethna_Const::E_DB_DUPENT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_DB_INVALIDTYPE/Ethna_Const::E_DB_INVALIDTYPE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_SESSION_EXPIRE/Ethna_Const::E_SESSION_EXPIRE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_SESSION_IPCHECK/Ethna_Const::E_SESSION_IPCHECK/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_APP_UNDEFINED_ACTION/Ethna_Const::E_APP_UNDEFINED_ACTION/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_APP_UNDEFINED_ACTIONCLASS/Ethna_Const::E_APP_UNDEFINED_ACTIONCLASS/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_APP_DUPENT/Ethna_Const::E_APP_DUPENT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_APP_NOMETHOD/Ethna_Const::E_APP_NOMETHOD/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_APP_LOCK/Ethna_Const::E_APP_LOCK/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_UTIL_CSV_CONTINUE/Ethna_Const::E_UTIL_CSV_CONTINUE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_WRONGTYPE_SCALAR/Ethna_Const::E_FORM_WRONGTYPE_SCALAR/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_WRONGTYPE_ARRAY/Ethna_Const::E_FORM_WRONGTYPE_ARRAY/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_WRONGTYPE_INT/Ethna_Const::E_FORM_WRONGTYPE_INT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_WRONGTYPE_FLOAT/Ethna_Const::E_FORM_WRONGTYPE_FLOAT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_WRONGTYPE_DATETIME/Ethna_Const::E_FORM_WRONGTYPE_DATETIME/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_WRONGTYPE_BOOLEAN/Ethna_Const::E_FORM_WRONGTYPE_BOOLEAN/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_WRONGTYPE_FILE/Ethna_Const::E_FORM_WRONGTYPE_FILE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_REQUIRED/Ethna_Const::E_FORM_REQUIRED/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_MIN_INT/Ethna_Const::E_FORM_MIN_INT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_MIN_FLOAT/Ethna_Const::E_FORM_MIN_FLOAT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_MIN_STRING/Ethna_Const::E_FORM_MIN_STRING/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_MIN_DATETIME/Ethna_Const::E_FORM_MIN_DATETIME/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_MIN_FILE/Ethna_Const::E_FORM_MIN_FILE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_MAX_INT/Ethna_Const::E_FORM_MAX_INT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_MAX_FLOAT/Ethna_Const::E_FORM_MAX_FLOAT/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_MAX_STRING/Ethna_Const::E_FORM_MAX_STRING/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_MAX_DATETIME/Ethna_Const::E_FORM_MAX_DATETIME/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_MAX_FILE/Ethna_Const::E_FORM_MAX_FILE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_REGEXP/Ethna_Const::E_FORM_REGEXP/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_INVALIDVALUE/Ethna_Const::E_FORM_INVALIDVALUE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_INVALIDCHAR/Ethna_Const::E_FORM_INVALIDCHAR/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_FORM_CONFIRM/Ethna_Const::E_FORM_CONFIRM/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_CACHE_INVALID_TYPE/Ethna_Const::E_CACHE_INVALID_TYPE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_CACHE_NO_VALUE/Ethna_Const::E_CACHE_NO_VALUE/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_CACHE_EXPIRED/Ethna_Const::E_CACHE_EXPIRED/' {} \;
find . -name '*php' -type f -exec sed -i ''  's/E_CACHE_GENERAL/Ethna_Const::E_CACHE_GENERAL/' {} \;
