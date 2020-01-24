<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*
|--------------------------------------------------------------------------
| SYSTEM CONSTANTS
|--------------------------------------------------------------------------
|
*/

define('MAIN_SERVER_URL','http://ec2-34-234-138-211.compute-1.amazonaws.com/reports1/index.php/');

define('SALON_TABLE',			'plus_salon');
define('SALONIRIS_CLIENTS_TABLE',			'iris_clients');
define('SALONIRIS_APPTS_TABLE',			'iris_appointments');
define('MILL_CLIENTS_TABLE',			'mill_clients');
define('MILL_APPTS_TABLE',			'mill_appointments');
define('MILL_CONFIG_DETAILS',			'mill_config_details');
define('MILL_CONFIG_DETAILS_NEW',			'mill_config_details_new');

define('MILL_CLIENTS_TABLE_BK',			'mill_clients_bk');
define('MILL_APPTS_TABLE_BK',			'mill_appointments_bk');
define('MILL_CONFIG_DETAILS_BK',			'mill_config_details_bk');
define('MILL_CONFIG_DETAILS_ZANYA',			'mill_config_details_zanya');
define('MILL_CONFIG_DETAILS_CHARLES_IFREGAN',			'mill_config_details_charlie_Ifregan');
define('MILL_CONFIG_DETAILS_TEDDIE_STORE',			'mill_config_details_teddie_store');
define('MILL_CONFIG_DETAILS_TRU_SALON',			'mill_config_details_tru_salon');
define('MILL_CONFIG_DETAILS_VELVET_LUXE_SALON',			'mill_config_details_velvet_luxe');
//define('MILL_CRON_LOG_REPORT',			'mill_cron_log_report');
define('MILL_RUNNING_CRON_LOG_REPORT',			'mill_cron_running_logs');
define('MILL_PAST_APPTS_TABLE',			'mill_past_appointments');
define('CHARLES_IFREGAN_CONFIG_DETAILS',			'charles_ifergan_config_details');

//ADDED BY KRANTHI ON 02-03-2016
define('MILL_PACKAGE_SERIES_LIST',			'mill_package_Series_list');
define('MILL_ALL_SDK_CONFIG_DETAILS',			'mill_all_sdk_config_details');
define('MILL_PACKAGE_SERIES_SALES_BY_DATE',			'mill_package_series_sales_by_date');
define('MILL_EMPLOYEE_LISTING',			'mill_employee_listing');
define('MILL_SERVICE_CLASS_LISTING',			'mill_service_class_listing');
define('MILL_SERVICES_LISTING',			'mill_services_listing');
define('MILL_SERVICE_TOTAL_SALES_BY_CLASS',			'mill_service_total_sales_by_class');
define('MILL_SERVICE_TOTAL_SALES',			'mill_service_total_sales');
define('MILL_PRODUCT_TOTAL_SALES',			'mill_product_total_sales');
define('MILL_TRANSACTION_HEADER_BY_DATETIME',			'mill_transaction_header_by_datetime');
define('MILL_SERVICE_SALES_BY_EMPLOYEE',			'mill_service_sales_by_employee');
define('MILL_SERVICE_SALES',			'mill_service_sales');
define('MILL_PRODUCT_SALES',			'mill_product_sales');
define('MILL_GIFT_CARD_SALES_WITH_BALANCE',			'mill_gift_card_sales_with_balance');
define('MILL_GIFT_CERTIFICATE_TYPE_LISTING',			'mill_gift_certificate_type_listing');
define('SETTINGS_TABLE',			'plus_settings');
define('MILL_TRANSACTION_TIPS',			'mill_transaction_tips');
define('MILL_REPORT_CALCULATIONS_CRON',		'mill_report_calculations_cron'); //Added BY KRANTHI ON 04-12-2015
define('PLUS_RPCT_PER_STYLIST_CALCULATIONS_CRON',		'plus_rpct_per_stylist_calculations_cron'); //Added BY KRANTHI ON 04-12-2015
define('MILL_RPCT_CALCULATIONS_CRON',		'mill_rpct_calculations_cron'); //Added BY KRANTHI ON 04-12-2015
define('MILL_PREBOOK_CALCULATIONS_CRON',		'mill_prebook_calculation_cron'); //Added BY KRANTHI ON 04-12-2015
define('MILL_SERVICE_RETAIL_CALCULATIONS_CRON',		'plus_service_retail_calculations_cron'); //Added BY KRANTHI ON 04-12-2015
define('MILL_REPORT_BASED_ON_SKILLSET_CALCULATIONS_CRON',		'mill_report_based_on_skillset_calculations_cron'); //Added BY KRANTHI ON 04-12-2015


define('MILL_ALL_SERVICE_DATA_FOR_REPORTS','mill_all_service_data_for_reports');
define('MILL_ALL_RETAIL_DATA_FOR_REPORTS','mill_all_retail_data_for_reports');
define('MILL_OWNER_REPORT_CALCULATIONS_CRON','mill_owner_report_calculations_cron');


//ADDED BY KRANTHI ON 02-03-2016

define('MILL_GIFT_CARDS_OWNER_REPORTS','mill_gift_cards_owner_reports');
define('MILL_NEW_GUEST_OWNER_REPORTS','mill_new_guest_owner_reports');
define('MILL_PERCENT_BOOKED_OWNER_REPORTS','mill_percent_booked_owner_reports');
define('MILL_PERCENT_COLOR_OWNER_REPORTS','mill_percent_color_owner_reports');
define('MILL_PERCENT_PREBOOKED_OWNER_REPORTS','mill_percent_prebooked_owner_reports');
define('MILL_REPEAT_GUEST_OWNER_REPORTS','mill_repeat_guest_owner_reports');
define('MILL_RETAIL_OWNER_REPORTS','mill_retail_owner_reports');
define('MILL_RPCT_OWNER_REPORTS','mill_rpct_owner_reports');
define('MILL_SERVICE_OWNER_REPORTS','mill_service_owner_reports');
define('MILL_TOTAL_REVENUE_REPORTS','mill_total_revenue_reports');
define('MILL_EMPLOYEE_SCHEDULE_HOURS','mill_employee_schedule_hours');

//By Rajeev on Jun 22
define('SALONBIZ_REPORTS_TABLE', 'plus_salonbiz_reports');
define('SALONBIZ_OWNER_REPORTS_TABLE', 'plus_salonbiz_owner_reports');
define('STAFF2_TABLE',			'plus_staff2');
define('SERVICE_PROVIDER',			'plus_service_provider');
define('MILL_TOPFIVE_SERVICES_OWNER_REPORT','mill_topfive_services_owner_report');
define('MILL_PERCENT_BOOKED_STAFF_REPORTS','mill_percent_booked_staff_reports');
define('MILL_AVG_RETAIL_TICKET_OWNER_REPORTS','mill_avg_retail_ticket_owner_reports');
define('MILL_AVG_SERVICE_TICKET_OWNER_REPORTS','mill_avg_service_ticket_owner_reports');
define('MILL_REBOOK_PERCENTAGE_OWNER_REPORTS','mill_rebook_percentage_owner_reports');
define('MILL_RUCT_OWNER_REPORTS','mill_ruct_owner_reports');
define('MILL_CLIENTS_SERVED_OWNER_REPORTS','mill_clients_served_owner_reports');
define('MILL_RUCT_CALCULATION_CRON','mill_ruct_calculation_cron');
define('MILL_CLIENT_BUYING_RETAIL_STAFF_REPORTS','mill_client_buying_retail_staff_reports');
define('LOG_FILES_PATH', 'uploads_log/');
/* End of file constants.php */
/* Location: ./application/config/constants.php */


// Anas
define('GET_SALON_INFO_FR_SALONID_BY_SALONCLOUDSPLUS', 'https://saloncloudsplus.com/wsInfotoIntServer/getSalonInfoFromSalonId');
define('GET_MILL_SALON_INFO_URL', 'https://saloncloudsplus.com/wsInfotoIntServer/getMillSalonsInfo');
define('GETALLSTAFFMEMBERS_URL', 'https://saloncloudsplus.com/wsInfotoIntServer/getAllStaffMembers');
define('MILLAPPOINTMENTANDSALONINFOFORCRON_URL', 'https://saloncloudsplus.com/wsInfotoIntServer/millAppointmentAndSalonInfoForCron');

define('GET_STAFF_FROM_PLUS_SERVER_URL','https://saloncloudsplus.com/wsInfotoIntServer/getStaff');
define('GETMILLAPPOINTMENTANDSALONINFO', 'https://saloncloudsplus.com/wsInfotoIntServer/millAppointmentAndSalonInfo');


define('TODAY', 'today');
define('LASTWEEK', 'lastweek');
define('LASTMONTH', 'lastmonth');
define('MONTHLY', 'Monthly');
define('LAST90DAYS', 'last90days');
define('THREEMONTHS', 'threemonths');
define('YEARLY', 'Yearly');
define('CUSTOMDATE', 'customdate');
define('PREVIOUSMONTHS','Previousmonths');
define('LASTYEAR','Lastyear');
define('CURRENTMONTH','Currentmonth');
define('WITHPHPWEEKS','WITHPHPWEEKS');

define('MILL_PERCENTAGE_RETAIL_TO_SERVICE_SALES_OWNER_REPORTS','mill_percentage_retail_to_service_sales_owner_reports');




