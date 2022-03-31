<?php
require_once 'connection.php';
require_once 'Google_spread_sheet.php';
require_once 'db_connection.php';


function is_fb_error($project_management_id, $db)
{
	$fb_error = $db->selectAll('site_facebook',$where=['project_management_id' => $project_management_id ], $field="fb_error");

    if(!empty($fb_error) && $fb_error[0]['fb_error']==0)
    {
    	return false;
    }
    return true;
}

$now = date("Ymd");
$siteURL = $_GET['url'];
$_SESSION['user'] = 'info@turboanchor.com';
$db = new DB();
$conn = new IP(); // t_anchor database
$ga = new GA();

$project_management_id = $_GET['project_id'];

$show_this_analytics = $conn->select("select `ecommerce_analytics`, `google_ads_data`, `user_analytics`, `traffic_analytics`, `facebook_roas_data`, `facebook_ads_data`, `mcf_conversion_data`, `google_webmasters_keywords`,`google_keyword_sheet_created`, `active_users_location`, `active_pages_custom_chart`, `seo_data_google_serps`, `age_group_data`, `count_of_sessions` from ja_project_management where id = $project_management_id");

$ecommerce_analytics = $show_this_analytics[0]['ecommerce_analytics'];
$google_ads_data = $show_this_analytics[0]['google_ads_data'];
$user_analytics = $show_this_analytics[0]['user_analytics'];
$traffic_analytics = $show_this_analytics[0]['traffic_analytics'];
$facebook_roas_data = $show_this_analytics[0]['facebook_roas_data'];
$facebook_ads_data = $show_this_analytics[0]['facebook_ads_data'];
$mcf_conversion_data = $show_this_analytics[0]['mcf_conversion_data'];
$google_webmasters_keywords = $show_this_analytics[0]['google_webmasters_keywords'];

$active_users_location = $show_this_analytics[0]['active_users_location'];
$active_pages_custom_chart = $show_this_analytics[0]['active_pages_custom_chart'];
$seo_data_google_serps = $show_this_analytics[0]['seo_data_google_serps'];
$age_group_data = $show_this_analytics[0]['age_group_data'];
$count_of_sessions = $show_this_analytics[0]['count_of_sessions'];

$is_fb_error = is_fb_error($_GET['project_id'], $db);

if(isset($_GET['site']))
{
        $site = $db->getAccessToken(['name'=>$_GET['site']]);
        if($site['isSuccess'])
        {
            $site = $site['objSite'];
        }
        else
        {
            $error = $site['message'];
        }
}

function makeNotification($data=array(), $error='') {
    GLOBAL $conn;

    $notifications = $conn->select("select * from ja_notifications where `read` = 0 and `title`='Analytics Credential Fails' and `user_id`={$data['user_id']}  and project_management_id={$data['project_id']} order by id DESC limit 1 ");

    if(empty($notifications)) {
      $notification['user_id'] = $data['user_id'];
      $notification['project_management_id'] = $data['project_id'];
      $notification['title'] = 'Analytics Credential Fails';
      $uri = 'https://turboanchor.com/projectmanagement/update_project/'.$data['project_id'];
      $notification['description'] = 'Please reenter you Google Analytics Credentials details, <a href="'.$uri.'" target="_blank">Click Here</a>';
      $conn->Save('ja_notifications',$notification);
    }

    return true;
}

// generating notification and update error field to 1
$analytics = false;
$siteData = $db->selectAll('site',$where=['project_management_id' => $_GET['project_id']], $field="error");
if (!empty($siteData) && $siteData[0]['error'] == 0) {
    $analytics = true;
}

if ($analytics == false) {

    $db->update('site',['error' => 1],['name' => $_GET['site'], 'project_management_id' => $_GET['project_id']]);
    $notifyArr = [];
    $notifyArr['project_id'] = $_GET['project_id'];
    $notifyArr['site'] = $_GET['site'];
    $projectData = $conn->select("select * from ja_project_management where id = '{$notifyArr['project_id']}' and title = '{$notifyArr['site']}' limit 1 ");
    $notifyArr['user_id'] = $projectData[0]['user_id'];
    $notifyArr['title'] = 'Analytics Credential Fails';
    $show = makeNotification($notifyArr, '');
}

$report =array();
if(isset($_GET['sheet_name']) && !empty($_GET['sheet_name']) && !empty($show_this_analytics[0]['google_keyword_sheet_created']))
{

    $report = $conn->select("select * from ja_fallow_links where project_management_id = {$project_management_id}");
}
if (isset($_GET['project_id'])) {

    $projectID = $_GET['project_id'];
    $facebook_account_id = $db->selectAll('site',$where=['project_management_id =' => $projectID],$field="fb_account_id");

    $fb_currency_code_result =  $conn->select("Select currency_code from ja_fb_currency_code WHERE project_management_id = '" . $projectID . "'");
    $fb_currency_code =  $fb_currency_code_result[0]['currency_code'];
     $fb_currency_symbol_result = $conn->select("Select symbol from ja_currency WHERE code = '$fb_currency_code' LIMIT 1");
     $fb_currency_symbol = $fb_currency_symbol_result[0]['symbol'];
    $currencySymbol = $currencyData[0]['symbol'];

    $projectData = $conn->select("Select currency_id from ja_project_management WHERE id = $projectID LIMIT 1");
    $currency_id = $projectData[0]['currency_id'];

    $currencyData = $conn->select("Select symbol from ja_currency WHERE id = $currency_id LIMIT 1");
    $currencySymbol = $currencyData[0]['symbol'];

    $maxDate = $conn->select("Select max(date) from ja_keyword_position_report");
    $keywordData = array();
}

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Google Analytics - Multisite | Deadlock</title>
        <?php require_once 'head.php'; ?>
        <link rel="stylesheet" href="assets/vendor/jvectormap-next/jquery-jvectormap.css">
        <link href="assets/css/jquery-ui.min.css" rel="stylesheet">
        <!-- ======================= PAGE LEVEL VENDOR STYLES ========================-->
        <link rel="stylesheet" href="assets/css/vendor/bootstrap.css">
        <link rel="stylesheet" href="assets/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css">
        <link rel="stylesheet" href="assets/vendor/bootstrap-daterangepicker/daterangepicker.css">
        <link href="assets/css/dataTables.bootstrap.css" rel="stylesheet">
        <link href="assets/css/font-awesome.min.css" rel="stylesheet">
        <link href="assets/css/dataTables.responsive.css" rel="stylesheet">
        <link href="assets/css/carbon-components.min.css" rel="stylesheet">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css">

    <style type="text/css">

    .overlayDiv
    {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        z-index: 1;
        margin-top: 0px;
        cursor: pointer;
        cursor: -moz-zoom-in;
        cursor: -webkit-zoom-in;
        cursor: zoom-in;
    }

    .graph-sub-parent
    {
        position: relative;
    }
    .container-fluid {
        overflow: hidden;
    }
    .arrow-icon img {
        width: 19px;
        height: 22px;
        position: relative;
        top: 7px;
    }
    .positive-performance {
        background-color: #28a745;
        padding: 7px;
        border-radius: 2px;
        color: #fff;
        font-size: .8em;
    }
    .negative-performance {
        background-color: #dc3545;
        padding: 7px;
        border-radius: 2px;
        color: #fff;
        font-size: .8em;
    }
    .same-performance {
        background-color: #484757;
        padding: 7px;
        border-radius: 2px;
        color: #fff;
        font-size: .8em;
    }

    #webmasters .row{
        width: 100%;
    }

	.pagination {
  		display: inline-block;
	}

	.pagination li {
  		color: black;
  		float: left;
  		padding: 8px 16px;
  		text-decoration: none;
    	border:1px solid #DEDEDE;
	}
	.pagination li.active {
 		/*background-color: #38978d;*/
        background-color: #294c86;
  		color: white;
	}
    .pagination li.active a {
        color: #fff;
    }
	.pagination li:hover:not(.active) {
        /*background-color: #ddd;*/
        background-color: #3D70B2;
        color: #fff;
    }
    .pagination a:hover {
            color: #d7d7d7;
    }

    .disabled {
        cursor: not-allowed;
        pointer-events: none;
    }

    .warning-heading
    {
        margin-left: 30px;
    }

    .warning-container
    {
        margin-bottom: 15px;
    }

    /* Tooltip design */
    .tooltip {
        text-align: center;
        outline: none;
        border: none;
    }

    .tooltip > .tooltip-inner {
        max-width: auto;
        min-width: auto;
        color: #000;
        text-align: center;
        background-color: #fff !important;
        border-radius: 5px !important;
        outline: none !important;
        border: none !important;
        box-shadow: 0px 0px 5px 3px #d8d7d7 !important;
    }

    .tooltip-arrow {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
    .tooltip .arrow::before {
        display: none !important;
    }

    .tooltip-i-icon {
        color: #5f5e5e;
        font-size: .7rem;
        position: relative;
        top: -5px;
        cursor: pointer;
    }
    .display-date {
        margin-left: auto;
        font-size: 0.8rem;
        font-weight: normal;
        color: #718599;
        float: right;
        /*text-transform: lowercase;*/
    }

    .nav.nav-tabs {
        border-bottom: none;
    }
    .nav .nav-item .nav-link:hover {

    }

    @media (min-width: 576px){
        #myModal .modal-dialog {
            max-width: 700px !important;
        }
    }

    @media (max-width: 425px){
        .p-sm--0 {
            padding: 0 !important;
        }
    }

    @media (max-width: 767px){
        .display-date {
            position: absolute;
            top: 55px;
            right: 20px;
        }
    }


    <?php if($_GET['fix']== 'no'){?>
        a.turbo-info-child,a.turbo-info,a.fix{
            display: none !important;
        }
    <?php }?>

    </style>

    </head>

    <body class="layout-horizontal" style="background:#ffffff !important;">

        <!-- START APP WRAPPER -->

        <input type="hidden" id="ecommerce_analytics" value="<?php echo $ecommerce_analytics ?>">
        <input type="hidden" id="google_ads_data" value="<?php echo $google_ads_data ?>">
        <input type="hidden" id="user_analytics" value="<?php echo $user_analytics ?>">
        <input type="hidden" id="traffic_analytics" value="<?php echo $traffic_analytics ?>">
        <input type="hidden" id="facebook_roas_data" value="<?php echo $facebook_roas_data ?>">
        <input type="hidden" id="facebook_ads_data" value="<?php echo $facebook_ads_data ?>">
        <input type="hidden" id="mcf_conversion_data" value="<?php echo $mcf_conversion_data ?>">
        <input type="hidden" id="google_webmasters_keywords" value="<?php echo $google_webmasters_keywords ?>">
        <!-- New -->
        <input type="hidden" id="active_users_location" value="<?php echo $active_users_location ?>">
        <input type="hidden" id="active_pages_custom_chart" value="<?php echo $active_pages_custom_chart ?>">
        <input type="hidden" id="seo_data_google_serps" value="<?php echo $seo_data_google_serps ?>">
        <input type="hidden" id="age_group_data" value="<?php echo $age_group_data ?>">
        <input type="hidden" id="count_of_sessions" value="<?php echo $count_of_sessions ?>">

        <input type="hidden" value="<?php echo $site->name; ?>" id="site" />
    	<input type="hidden" value="<?php echo $_GET['url']; ?>" id="url" />
        <input type="hidden" value="<?php echo $_GET['project_id']; ?>" id="projectId" />
        <input type="hidden" value="<?php echo $facebook_account_id[0]['fb_account_id']; ?>" id="facebook_account_id" />

        <input type="hidden" value="<?php echo  $projectID; ?>" id="project_management_id" />
        <div id="app">
            <?php if(isset($site)) { ?>
            <div class="content-wrapper">
                <div class="content container-fluid">
                    <section class="page-content">
                        <!-- New section added by Zubair -->
                        <div class="row m-b-40">
                            <div class="col">
                                <div class="card" id="">
                                    <div class="card-toolbar top-right">
                                        <ul class="nav nav-pills nav-pills-primary justify-content-end chart_duration" id="pills-demo-1" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link" id="pills-1-tab" data-toggle="pill" href="#pills-1" role="tab" aria-controls="pills-1" aria-selected="true">Today<span class="period" style="display: none;">Today</span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="pills-2-tab" data-toggle="pill" href="#pills-2" role="tab" aria-controls="pills-2" aria-selected="true">Yesterday<span class="period" style="display: none;">Yesterday</span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link active" id="pills-3-tab" data-toggle="pill" href="#pills-3" role="tab" aria-controls="pills-3" aria-selected="true">07 Days<span class="period" style="display: none;">Week</span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="pills-4-tab" data-toggle="pill" href="#pills-4" role="tab" aria-controls="pills-4" aria-selected="false">30 Days<span class="period" style="display: none;">Month</span></a>
                                            </li>
                                            <!-- <li class="nav-item">
                                                <a class="nav-link" id="pills-5-tab" data-toggle="pill" href="#pills-5" role="tab" aria-controls="pills-5" aria-selected="false">01 Year<span class="period" style="display: none;">Year</span></a>
                                            </li> -->
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ======================================
                        ============== Tabs Start ============ -->
                        <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                            <!-- 1st eCommerce_analytics_tab -->
                            <?php if($ecommerce_analytics == 1) { ?>
                            <li class="nav-item" id="eCommerce_analytics_tab_button">
                                <a class="nav-link active" data-toggle="tab" href="#eCommerce_analytics_tab" role="tab" aria-controls="home" aria-selected="true">eCommerce Analytics</a>
                            </li>
                            <?php } ?>
                            <!-- 2nd mcf_conversion_tab -->
                            <?php if($mcf_conversion_data == 1) { ?>
                            <li class="nav-item" id="mcf_conversion_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#mcf_conversion_tab" role="tab" aria-controls="profile" aria-selected="false">MCF Conversion</a>
                            </li>
                            <?php } ?>
                            <!-- 3rd google_ads_tab -->
                            <?php if($google_ads_data == 1) { ?>
                            <li class="nav-item" id="google_ads_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#google_ads_tab" role="tab" aria-controls="contact" aria-selected="false">Google Ads</a>
                            </li>
                            <?php } ?>
                            <!-- 4th users_analytics_tab -->
                            <?php if($user_analytics == 1) { ?>
                            <li class="nav-item" id="users_analytics_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#users_analytics_tab" role="tab" aria-controls="contact" aria-selected="false">Users Analytics</a>
                            </li>
                            <?php } ?>
                            <!-- 5th traffic_analytics_tab -->
                            <?php if($traffic_analytics == 1) { ?>
                            <li class="nav-item" id="traffic_analytics_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#traffic_analytics_tab" role="tab" aria-controls="contact" aria-selected="false">Traffic Analytics</a>
                            </li>
                            <?php } ?>
                            <!-- 6th facebook_roas_data_tab -->
                            <?php if($facebook_roas_data == 1) { ?>
                            <li class="nav-item" id="facebook_roas_data_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#facebook_roas_data_tab" role="tab" aria-controls="contact" aria-selected="false">Facebook ROAS Data</a>
                            </li>
                            <?php } ?>
                            <!-- 7th facebook_ads_data_tab -->
                            <?php if($facebook_ads_data == 1) { ?>
                            <li class="nav-item" id="facebook_ads_data_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#facebook_ads_data_tab" role="tab" aria-controls="contact" aria-selected="false">Facebook Ads</a>
                            </li>
                            <?php } ?>
                            <!-- 8th active_users_location_tab -->
                            <?php if($active_users_location == 1) { ?>
                            <li class="nav-item" id="active_users_location_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#active_users_location_tab" role="tab" aria-controls="contact" aria-selected="false">Active Users/Location</a>
                            </li>
                            <?php } ?>
                            <!-- 9th active_pages_custom_chart -->
                            <?php if($active_pages_custom_chart == 1) { ?>
                            <li class="nav-item" id="active_pages_custom_chart_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#active_pages_custom_chart_tab" role="tab" aria-controls="contact" aria-selected="false">Active Pages/Custom Chart</a>
                            </li>
                            <?php } ?>
                            <!-- 10th seo_data_google_serps_tab -->
                            <?php if($seo_data_google_serps == 1) { ?>
                            <li class="nav-item" id="seo_data_google_serps_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#seo_data_google_serps_tab" role="tab" aria-controls="contact" aria-selected="false">SEO/Google SERPs</a>
                            </li>
                            <?php } ?>
                            <!-- 11th age_group_data_tab -->
                            <?php if($age_group_data == 1) { ?>
                            <li class="nav-item" id="age_group_data_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#age_group_data_tab" role="tab" aria-controls="contact" aria-selected="false">Age Group</a>
                            </li>
                            <?php } ?>
                            <!-- 12th count_of_sessions_tab -->
                            <?php if($count_of_sessions == 1) { ?>
                            <li class="nav-item" id="count_of_sessions_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#count_of_sessions_tab" role="tab" aria-controls="contact" aria-selected="false">Count of sessions</a>
                            </li>
                            <?php } ?>
                            <!-- 13th google_console_keywords_tab -->
                            <?php if($google_webmasters_keywords == 1) { ?>
                            <li class="nav-item" id="google_console_keywords_tab_button">
                                <a class="nav-link" data-toggle="tab" href="#google_console_keywords_tab" role="tab" aria-controls="contact" aria-selected="false">Console Keywords</a>
                            </li>
                            <?php } ?>
                        </ul> <!-- ul nav-tabs -->

                        <div class="tab-content" id="myTabContent">
                            <!-- 1st eCommerce_analytics_tab -->
                            <?php if($ecommerce_analytics == 1) { ?>
                            <div class="tab-pane fade show active" id="eCommerce_analytics_tab" role="tabpanel" aria-labelledby="eCommerce_analytics_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">
                                            <div class="card box-design" id="current_performance">
                                                <?php if ($analytics) { ?>
                                                <h5 class="card-header p-t-25 p-b-20">
                                                    <img src='assets/img/analytics_icon.png' width="30" />&nbsp;
                                                    eCommerce Analytics
                                                    <i class="fa fa-info-circle tooltip-i-icon" id="ecommerce_analytics_tooltip" aria-hidden="true" data-pram1="ECommerce Analytics" data-pram2="ECommerce Analytics" data-pram3="definition" onmouseover="ajaxTooltip(this)" title="" style="margin-left: 5px;top: -2px;"></i>
                                                    <div class="eCommerce-display-date display-date" style=""></div>
                                                </h5>
                                                <div class="card-body" id="">
                                                    <!-- REVENUE BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5">
                                                            REVENUE
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="ECommerce Analytics" data-pram2="Revenue" data-pram3="definition" onmouseover="ajaxTooltip(this)" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-revenue">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-revenue">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small revenue-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                             <div class="col-md-4 col-sm-4 revenue_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Conversion Rate BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            CONVERSION RATE
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="ECommerce Analytics" data-pram2="Conversion Rate" data-pram3="definition" onmouseover="ajaxTooltip(this)" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous_conversion_rate">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-conversion-rate">0</span>
                                                                <!-- Percent Value -->
                                                                <br><span class="percent-value small conversion-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 conversion_rate_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Transaction BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            TRANSACTIONS
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="ECommerce Analytics" data-pram2="Transactions" data-pram3="definition" onmouseover="ajaxTooltip(this)" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-transactions"></span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-transactions">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small transaction-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 transaction_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Avg Order Val BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            AVG. ORDER VALUE
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="ECommerce Analytics" data-pram2="Avg Order Value" data-pram3="definition" onmouseover="ajaxTooltip(this)" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-avgOrderVal"></span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-avgOrderVal">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small avgOrderVal-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                             <div class="col-md-4 col-sm-4 avg_order_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- ROI BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            ROI VALUE
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="ECommerce Analytics" data-pram2="ROI Value" data-pram3="definition" onmouseover="" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-roi"></span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-roi">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small roi-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                             <div class="col-md-4 col-sm-4 roi_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php } else { ?>
                                                <h5 class="card-header">
                                                    <img src='assets/img/analytics_icon.png' width="30" />&nbsp;
                                                    eCommerce Analytics
                                                    <?php
                                                      include("notification_for_analytics_error.php");
                                                    ?>
                                                </h5>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 2nd mcf_conversion_tab -->
                            <?php if($mcf_conversion_data == 1) { ?>
                            <div class="tab-pane fade" id="mcf_conversion_tab" role="tabpanel" aria-labelledby="mcf_conversion_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card box-design">
                                            <?php if ($analytics) { ?>
                                            <h5 class="card-header p-t-25 p-b-20">
                                                <img src='assets/img/analytics_icon.png' width="30" />&nbsp;
                                                MCF Conversion
                                            </h5>
                                            <div class="card-body">
                                                <!-- 1st BOX -->
                                                <div id="mcf_table_body">
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5">MCF Source</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    MCF <br>
                                                                    <span class="time-period">Conversion</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    MCF <br>
                                                                    <span class="time-period">Conversion Value</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value ">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    MCF <br>
                                                                    <span class="time-period">Conversion in %</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value">0</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- 2nd Box -->
                                            </div>
                                            <?php } else {
                                            ?>
                                            <h5 class="card-header p-t-25 p-b-20">
                                                MCF Conversion
                                            <?php
                                                include("notification_for_analytics_error.php");
                                            ?>
                                            </h5>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 3rd google_ads_tab -->
                            <?php if($google_ads_data == 1) { ?>
                            <div class="tab-pane fade" id="google_ads_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">
                                            <div class="card box-design">
                                                <?php if ($analytics) { ?>
                                                <h5 class="card-header p-t-25 p-b-20" id="main_adwords_analytics">
                                                    <img src='assets/img/adword_icon.png' width="30" />&nbsp;
                                                    Google Ads
                                                    <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="Google Ads Data" data-pram2="Google Ads Data" data-pram3="definition" onmouseover="ajaxTooltip(this)" title="" style="margin-left: 5px;top: -2px;"></i>
                                                    <div class="ads-display-date display-date"></div>
                                                </h5>
                                                <div class="card-body" id="" style="flex: none;">
                                                    <!-- Ads Clicks BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5">
                                                            ADS CLICKS
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="Google Ads Data" data-pram2="Ads Clicks" data-pram3="definition" onmouseover="ajaxTooltip(this)" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-ads-clicks">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-ads-clicks">0</span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small ads-clicks-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                         <div class="col-md-4 col-sm-4 google_ads_click_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Ads Cost BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            ADS COST
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="Google Ads Data" data-pram2="Ads Cost" data-pram3="definition" onmouseover="ajaxTooltip(this)" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-ads-cost">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-ads-cost">0</span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small ads-cost-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 google_ads_cost_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Ads CPC BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            ADS CPC
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="Google Ads Data" data-pram2="Ads CPC" data-pram3="definition" onmouseover="ajaxTooltip(this)" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-ads-cpc">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-ads-cpc">0</span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small ads-cpc-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 google_ads_cpc_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> <!-- main -->
                                                <?php } else {?>
                                                  <h5 class="card-header">
                                                    <img src='assets/img/adword_icon.png' width="30" />&nbsp;
                                                    Google Ads
                                                    <?php
                                                      include("notification_for_analytics_error.php");
                                                    ?>
                                                </h5>
                                               <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 4th Users Analytics -->
                            <?php if($user_analytics == 1) { ?>
                            <div class="tab-pane fade" id="users_analytics_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">
                                            <div class="card box-design">
                                                <?php if ($analytics) { ?>
                                                <h5 class="card-header p-t-25 p-b-20" id="main-ai-simple-users-box">
                                                    <img src='assets/img/analytics_icon.png' width="30" />&nbsp;
                                                    Users Analytics
                                                    <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="Users Analytics" data-pram2="Users Analytics" data-pram3="definition" onmouseover="ajaxTooltip(this)" style="margin-left: 5px;top: -2px;" title=""></i>
                                                    <div class="users-display-date display-date"></div>
                                                </h5>
                                                <div class="card-body" id="ai-simple-users-box">
                                                    <!-- New Visitors BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5">
                                                            New Visitors
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Users Analytics" data-pram2="New Visitors" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-new-visitors">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-new-visitors">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small new-visitors-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 google_new_visitors_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Returning Visitors BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Returning Visitors
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="Users Analytics" data-pram2="Returning Visitors" data-pram3="definition" onmouseover="ajaxTooltip(this)" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-returning-visitors">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-returning-visitors">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small returning-visitors-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 google_returning_visitors_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Users BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Users
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" data-pram1="Users Analytics" data-pram2="Users" data-pram3="definition" onmouseover="ajaxTooltip(this)" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-users">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-users">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small users-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 google_total_users_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Mobile Users BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Mobile Users
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Users Analytics" data-pram2="Mobile Users" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-mobile-users">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-mobile-users">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small mobile-users-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 google_total_mobile_users_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Desktop Users BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Desktop Users
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Users Analytics" data-pram2="Desktop Users" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-desktop-users">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-desktop-users">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small desktop-users-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 google_total_desktop_users_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Tablet Users BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Tablet Users
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Users Analytics" data-pram2="Tablet Users" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-tablet-users">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-tablet-users">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small tablet-users-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 google_total_tablet_users_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> <!-- body -->
                                                <?php } else { ?>
                                                    <h5 class="card-header p-t-25 p-b-20" id="main-ai-simple-users-box">Users Analytics
                                                    <?php
                                                    include("notification_for_analytics_error.php");
                                                    ?>
                                                    </h5>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 5th Traffic Analytics -->
                            <?php if($traffic_analytics == 1) { ?>
                            <div class="tab-pane fade" id="traffic_analytics_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">
                                            <!-- =======================================
                                            ============== AI Traffic Data ============ -->
                                            <div class="card box-design">

                                                <?php if ($analytics) { ?>
                                                    <h5 class="card-header p-t-25 p-b-20" id="main-ai-simple-traffic-box">
                                                        <img src='assets/img/analytics_icon.png' width="30" />&nbsp;
                                                        Traffic Analytics
                                                        <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Traffic Analytics" data-pram3="definition" style="margin-left: 5px;top: -2px;" title=""></i>
                                                        <div class="traffic-display-date display-date"></div>
                                                    </h5>
                                                <div class="card-body" id="ai-simple-traffic-box" style="flex: none;">
                                                    <!-- Pageviews BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5">
                                                            Pageviews
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Pageviews" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-pageviews">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-pageviews">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small pageviews-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                           <div class="col-md-4 col-sm-4 google_pageviews_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Bounce Rate BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Bounce Rate
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Bounce Rate" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-bounce-rate">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-bounce-rate">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small bounce-rate-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 bounce_rate_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Session Duration BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Session Duration
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Session Duration" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-session-duration">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-session-duration">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small session-duration-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 session_duration_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Organic User BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Organic Users
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Organic Users" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-organic-user">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-organic-user">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small organic-user-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 organic_users_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Paid User BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Paid Users
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Paid Users" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-paid-user">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-paid-user">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small paid-user-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 paid_users_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Direct User BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Direct Users
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Direct Users" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-direct-user">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-direct-user">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small direct-user-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 direct_users_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Facebook Hits BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Facebook Clicks
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Facebook Clicks" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-fb-clicks">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-fb-clicks">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small fb-clicks-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 fb_clicks_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Twitter Hits BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Twitter Clicks
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Twitter Clicks" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-twitter-clicks">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-twitter-clicks">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small twitter-clicks-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 twitter_clicks_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Pinterest Hits BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Pinterest Clicks
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Pinterest Clicks" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-pinterest-clicks">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-pinterest-clicks">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small pinterest-clicks-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 pinterest_clicks_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Instagram Hits BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">
                                                            Instagram Clicks
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Traffic Analytics" data-pram2="Instagram Clicks" data-pram3="definition" title=""></i>
                                                        </h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value previous-instagram-clicks">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value current-instagram-clicks">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small instagram-clicks-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span>0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 instagram_clicks_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php } else {
                                                    ?>
                                                    <h5 class="card-header p-t-25 p-b-20" id="main-ai-simple-traffic-box">
                                                        Traffic Analytics
                                                    <?php
                                                    include("notification_for_analytics_error.php");
                                                    ?>
                                                    </h5>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 6th Facebook ROAS Data -->
                            <?php if($facebook_roas_data == 1) { ?>
                            <div class="tab-pane fade" id="facebook_roas_data_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">
                                            <div class="card box-design">
                                                <?php if (!$is_fb_error) { ?>
                                                <h5 class="card-header p-t-25 p-b-20" id="facebook_roas_current_performance">
                                                    <img src='assets/img/facebook-icon.png?v=2' width="30" />&nbsp;
                                                    Facebook ROAS <a href="#" class="turbo-info" onclick="suggistion_display('Facebook ROAS Data' ,'Facebook ROAS Data','definition')"><img src="assets/img/info-icon.png" width="12"></a>
                                                    <div class="display-date"></div>
                                                </h5>
                                                <div class="card-body" id="total_purchase_roas_g">
                                                    <!-- Total Purchase Roas -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5">Total Purchase Roas</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value last_tenure_purchase_roas"></span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value total_purchase_roas">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small purchase-roas-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span class="arrow-up">0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 total_purchase_roas_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Website Purchase Roas -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">Website Purchase Roas</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value last_tenure_website_purchase_roas">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value website_purchase_roas">0</span>
                                                                <!-- Percent Value -->
                                                                <br>
                                                                <span class="percent-value small website_purchase_roas-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span class="arrow-up">0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 website_purchase_roas_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Transaction BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">Mobile Purchase Roas</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value last_tenure_mobile_app_purchase_roas"></span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value mobile_app_purchase_roas">
                                                                </span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small mobile_app_purchase_roas-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span class="arrow-up">0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                           <div class="col-md-4 col-sm-4 mobile_purchase_roas_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                           <?php } else {?>
                                                  <h5 class="card-header">
                                                    <img src='assets/img/facebook-icon.png?v=2' width="30" />&nbsp;
                                                    Facebook ROAS
                                                    <?php
                                                      include("notification_for_fb_analytics_error.php");
                                                    ?>
                                                </h5>
                                               <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 7th Facebook Ads Data -->
                            <?php if($facebook_ads_data == 1) { ?>
                            <div class="tab-pane fade" id="facebook_ads_data_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">
                                            <div class="card box-design">
                                                <?php if (!$is_fb_error) { ?>
                                                <h5 class="card-header p-t-25 p-b-20" id="">
                                                    <img src='assets/img/facebook-icon.png?v=2' width="30" />&nbsp;
                                                    Facebook Ads <a href="#" class="turbo-info" onclick="suggistion_display('Facebook Ads Data' ,'Facebook Ads Data','definition')"><img src="assets/img/info-icon.png" width="12"></a>
                                                    <div class="display-date"></div>
                                                </h5>
                                                <div class="card-body" id="" style="flex: none;">
                                                    <!-- Ads Clicks BOX -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5">Total Cost</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value last_tenure_cost">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value facebook_total_ads_cost">0</span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small facebook_total_ads_cost-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span class="arrow-up">0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                             <div class="col-md-4 col-sm-4 cost_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Total Ad Clicks -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">Total Ad Clicks</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value last_tenure_clicks">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value facebook_total_ads_click">0</span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small facebook_total_ads_click-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span class="arrow-up">0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                           <div class="col-md-4 col-sm-4 ads_click_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Total Link Clicks -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">Total Link Clicks</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value last_tenure_action_type_link_click">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value facebook_total_link_clicks">0</span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small facebook_total_link_clicks-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span class="arrow-up">0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                             <div class="col-md-4 col-sm-4 link_click_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Total Link Clicks -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">Total Page Likes</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value last_tenure_action_type_like">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value facebook_total_page_likes">0</span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small facebook_total_page_likes-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span class="arrow-up">0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 action_type_like_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Total Reaches -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">Total Reaches</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value last_tenure_reach">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value facebook_total_ads_reaches">0</span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small facebook_total_ads_reaches-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span class="arrow-up">0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>

                                                             <div class="col-md-4 col-sm-4 reach_parent_container">
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <!-- Total Impressions -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">Total Impressions</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value last_tenure_impressions">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value facebook_total_ads_impressions">0</span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small facebook_total_ads_impressions-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span class="arrow-up">0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>

                                                            <div class="col-md-4 col-sm-4 impression_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Total CPC -->
                                                    <div class="box-container">
                                                        <h6 class="bx--data-table-v2-header m-l-5 m-t-20">Total CPC</h6>
                                                        <div class="row box-d">
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading primary-heading-sm">
                                                                    Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value last_tenure_cpc">0</span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4">
                                                                <h6 class="m-b-10 primary-heading">
                                                                    Current <span class="small">vs</span> Previous <br>
                                                                    <span class="time-period">07 Days</span>
                                                                </h6>
                                                                <!-- Value -->
                                                                <span class="h2 primary-value facebook_ads_cpc_value">0</span>
                                                                <!-- Percentage Value -->
                                                                <br><span class="percent-value small facebook_ads_cpc_value-percent">
                                                                    <img src='assets/img/arrow-up.png' width="12" />
                                                                    <span class="arrow-up">0%</span>
                                                                </span>
                                                                <div class="separator"></div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-4 cpc_parent_container">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> <!-- main -->
                                                <?php } else {?>
                                                  <h5 class="card-header">
                                                    <img src='assets/img/facebook-icon.png?v=2' width="30" />&nbsp;
                                                    Facebook Ads
                                                    <?php
                                                      include("notification_for_fb_analytics_error.php");
                                                    ?>
                                                </h5>
                                               <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 8th Users by Location & Live Active Users -->
                            <?php if($active_users_location == 1) { ?>
                            <div class="tab-pane fade" id="active_users_location_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">
                                            <div class="card">
                                                <?php if ($analytics) { ?>
                                                <h5 class="card-header p-t-25 p-b-20">
                                                    New Users by Location
                                                    <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="New Users by Location" data-pram2="New Users by Location" data-pram3="definition" title=""></i>
                                                    <div class="display-date"></div>
                                                </h5>
                                                <div class="card-body">
                                                    <div id="world-map" style="height: 300px"> </div>
                                                </div>
                                                <?php } else { ?>
                                                <h5 class="card-header border-none warning-heading">New Users by Location</h5>
                                                <div class="warning-container">
                                                    <?php
                                                  include("notification_for_analytics_error.php");
                                                  ?> </div> <?php
                                                } ?>
                                            </div>
                                            <div class="card">
                                                <?php if ($analytics) { ?>
                                                <h5 class="card-header p-t-25 p-b-20">
                                                    Live Active Users
                                                    <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Live Active Users" data-pram2="Live Active Users" data-pram3="definition" title=""></i>
                                                    <div class="display-date"></div>
                                                </h5>
                                                <div class="card-body">
                                                    <div class="icon-rounded icon-rounded-primary float-left m-r-20">
                                                        <i class="icon dripicons-graph-bar"></i>
                                                    </div>
                                                    <h5 class="card-title m-b-5 counter" data-count="0" id="live_users">0</h5>
                                                    <h6 class="text-muted m-t-10">
                                                        Active Users
                                                    </h6>
                                                </div>
                                                 <?php } else {
                                                    ?>
                                                <h5 class="card-header border-none warning-heading">Live Active Users</h5>
                                                    <div class="warning-container">

                                                    <?php
                                                  include("notification_for_analytics_error.php");
                                                  ?> </div> <?php
                                                } ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 9th Top Active Pages % Custom Chart -->
                            <?php if($active_pages_custom_chart == 1) { ?>
                            <div class="tab-pane fade" id="active_pages_custom_chart_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">

                                            <div class="card">
                                                <?php if ($analytics) { ?>
                                                <h5 class="card-header p-t-25 p-b-20">
                                                    Top Active Pages
                                                    <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Top Active Pages" data-pram2="Top Active Pages" data-pram3="definition" title=""></i>
                                                </h5>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col"></th>
                                                                    <th scope="col">Active Page</th>
                                                                    <th scope="col">Active Users</th>
                                                                    <th scope="col">% New Sessions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="active_pages"></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <?php } else {
                                                    ?>
                                                <h5 class="card-header border-none warning-heading">Top Active Pages</h5>
                                                <div class="warning-container">
                                                <?php
                                                  include("notification_for_analytics_error.php");
                                                  ?> </div> <?php
                                                } ?>
                                            </div>
                                            <div class="card">
                                                <?php if ($analytics) { ?>
                                                <h5 class="card-header p-t-25 p-b-20">
                                                    Custom Chart
                                                    <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Custom Chart" data-pram2="Custom Chart" data-pram3="definition" title=""></i>
                                                </h5>
                                                <div class="card-body">
                                                <?php
                                                $arrMatrics = [];
                                                $arrMatrics['ga:pageviews'] = [
                                                    'lable'=>"Pageviews",
                                                    'dimension'=>[
                                                        'ga:userType'=>'User Type',
                                                        'ga:medium'=>'Medium',
                                                        'ga:source'=>'Source',
                                                        'ga:keyword'=>'Keyword',
                                                        'ga:socialNetwork'=>'Social Network',
                                                        'ga:browser'=>'Browser',
                                                        'ga:operatingSystem'=>'Operating System',
                                                        'ga:deviceCategory'=>'Device Category',
                                                        'ga:language'=>'Language',
                                                        'ga:screenResolution'=>'Screen Resolution'
                                                    ]
                                                ];
                                                $arrMatrics['ga:newUsers'] = [
                                                    'lable'=>"New Users",
                                                    'dimension'=>[
                                                        'ga:userType'=>'User Type',
                                                        'ga:medium'=>'Medium',
                                                        'ga:source'=>'Source',
                                                        'ga:keyword'=>'Keyword',
                                                        'ga:socialNetwork'=>'Social Network',
                                                        'ga:browser'=>'Browser',
                                                        'ga:operatingSystem'=>'Operating System',
                                                        'ga:deviceCategory'=>'Device Category',
                                                        'ga:language'=>'Language',
                                                        'ga:screenResolution'=>'Screen Resolution'
                                                    ]
                                                ];
                                                $arrMatrics['ga:sessions'] = [
                                                    'lable'=>"Sessions",
                                                    'dimension'=>[
                                                        'ga:userType'=>'User Type',
                                                        'ga:medium'=>'Medium',
                                                        'ga:source'=>'Source',
                                                        'ga:keyword'=>'Keyword',
                                                        'ga:socialNetwork'=>'Social Network',
                                                        'ga:browser'=>'Browser',
                                                        'ga:operatingSystem'=>'Operating System',
                                                        'ga:deviceCategory'=>'Device Category',
                                                        'ga:language'=>'Language',
                                                        'ga:screenResolution'=>'Screen Resolution'
                                                    ]
                                                ];
                                                $arrMatrics['ga:hits'] = [
                                                    'lable'=>"Hits",
                                                    'dimension'=>[
                                                        'ga:userType'=>'User Type',
                                                        'ga:medium'=>'Medium',
                                                        'ga:source'=>'Source',
                                                        'ga:keyword'=>'Keyword',
                                                        'ga:socialNetwork'=>'Social Network',
                                                        'ga:browser'=>'Browser',
                                                        'ga:operatingSystem'=>'Operating System',
                                                        'ga:deviceCategory'=>'Device Category',
                                                        'ga:language'=>'Language',
                                                        'ga:screenResolution'=>'Screen Resolution'
                                                    ]
                                                ];
                                                ?>
                                                    <form class="form-inline text-right" method="post" id="custom_form">
                                                        <label class="sr-only" for="inlineFormInputName2">Metrics</label>
                                                        <select name="metrics" class="form-control mb-2 mr-sm-2 metrics">
                                                            <option value="">Metrics</option>
                                                            <?php foreach($arrMatrics as $key => $matrics){ ?>
                                                            <option data-dimension='<?php echo json_encode($matrics['dimension']); ?>' value="<?php echo $key; ?>"><?php echo $matrics['lable']; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <label class="sr-only" for="inlineFormInputName2">Dimension</label>
                                                        <select name="dimension" class="form-control mb-2 mr-sm-2 dimension">
                                                            <option value="">Dimension</option>
                                                        </select>
                                                        <label class="sr-only" for="inlineFormInputName2">Duration</label>
                                                        <input type="text" name="dates" class="form-control mb-2 mr-sm-2 duration" />
                                                        <button type="button" class="btn btn-primary mb-2">Refresh</button>
                                                    </form>
                                                </div>
                                                <div class="card-body">
                                                    <table class="table table-striped">
                                                        <tbody>
                                                            <div class="row m-0 col-border-xl">
                                                                <div class="col-md-12 p-sm--0">
                                                                    <div class="card-body p-sm--0" id="container_custom_chart">
                                                                        <div class="text-center" id="chart_loader_custom" style="display:none;"><img src='assets/img/loader_t.gif' /></div>
                                                                        <canvas id="chart_custom" style="width:100%"></canvas>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </tbody>
                                                    </table>
                                                    <?php } else {
                                                        ?>
                                                        <h5 class="card-header p-t-25 p-b-20">
                                                        Custom Chart

                                                         <?php
                                                      include("notification_for_analytics_error.php");
                                                      ?> </h5> <?php
                                                    } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 10th SEO Data & Google SERPs -->
                            <?php if($seo_data_google_serps == 1) { ?>
                            <div class="tab-pane fade" id="seo_data_google_serps_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row">
                                    <div class="col">
                                        <div class="card box-design" id="">
                                            <?php if ($analytics) { ?>
                                            <h5 class="card-header p-t-25 p-b-20">
                                                SEO
                                                <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="SEO Data" data-pram2="SEO Data" data-pram3="definition" title="" style="margin-left: 5px;top: -2px;"></i>
                                            </h5>
                                            <div class="card-body p-0">
                                                <div class="row m-0 col-border-xl">
                                                    <div class="col-md-6 p-20">
                                                        <!-- Table 1 -->
                                                        <div class="bx--data-table-v2-container" data-table-v2>
                                                          <h6 class="bx--data-table-v2-header">
                                                            Backlink Profile
                                                            <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="SEO Data" data-pram2="Backlink Profile" data-pram3="definition" title=""></i>
                                                          </h6>
                                                            <?php if (isset($_GET['sheet_name']) && !empty($_GET['sheet_name'])) { ?>
                                                            <section class="bx--table-toolbar">
                                                            </section>
                                                            <table class="bx--data-table-v2 bx--data-table-v2--zebra">
                                                              <thead>
                                                                <tr>
                                                                    <th style="min-width: 130px;">
                                                                      <button class="bx--table-sort-v2" data-event="sort">
                                                                        <span class="bx--table-header-label">Date</span>
                                                                        <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                          <title>Sort rows by this header in descending order</title>
                                                                          <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                        </svg>
                                                                      </button>
                                                                    </th>
                                                                    <th>
                                                                      <button class="bx--table-sort-v2" data-event="sort">
                                                                        <span class="bx--table-header-label">Total Dofollow</span>
                                                                        <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                          <title>Sort rows by this header in descending order</title>
                                                                          <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                        </svg>
                                                                      </button>
                                                                    </th>
                                                                    <th>
                                                                      <button class="bx--table-sort-v2" data-event="sort">
                                                                        <span class="bx--table-header-label">Total Nofollow</span>
                                                                        <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                          <title>Sort rows by this header in descending order</title>
                                                                          <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                        </svg>
                                                                      </button>
                                                                    </th>
                                                                    <th>
                                                                    </th>
                                                                </tr>
                                                              </thead>
                                                              <!-- Table Header End -->
                                                              <!-- Table body Start -->
                                                              <tbody>
                                                                <?php
                                                                    $id = 2;
                                                                    foreach ($report as $data) {
                                                                ?>
                                                                <tr>
                                                                    <td><?php echo $data['date']; ?></td>
                                                                    <td><?php echo $data['do_follow']; ?></td>
                                                                    <td><?php echo $data['no_follow']; ?></td>
                                                                    <td></td>
                                                                </tr>
                                                                <?php
                                                                    $id++;
                                                                    }
                                                                ?>
                                                              </tbody>
                                                            </table>
                                                            <?php } else { ?> <!-- Google sheet -->
                                                            <div data-notification="" class="bx--inline-notification bx--inline-notification--warning" role="alert">
                                                              <div class="bx--inline-notification__details">
                                                                  <svg class="bx--inline-notification__icon" width="12" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16zm1-3V7H7v6h2zM8 5a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"></path>
                                                                  </svg>
                                                                <div class="bx--inline-notification__text-wrapper">
                                                                  <p class="bx--inline-notification__title">Warning!</p>
                                                                  <p class="bx--inline-notification__subtitle">Please Provide Google Sheet Detail.&nbsp;<a href="https://turboanchor.com/projectmanagement/update_project/<?php echo $projectID; ?>" target="_blank">Click Here.</a></p>
                                                                </div>
                                                              </div>
                                                              <button data-notification-btn="" class="bx--inline-notification__close-button" type="button" aria-label="close">
                                                                  <svg aria-hidden="true" class="bx--inline-notification__close-icon" width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M6.32 5L10 8.68 8.68 10 5 6.32 1.32 10 0 8.68 3.68 5 0 1.32 1.32 0 5 3.68 8.68 0 10 1.32 6.32 5z" fill-rule="nonzero"></path>
                                                                  </svg>
                                                              </button>
                                                            </div>
                                                        <?php } ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 p-20" id="google_serp_container">
                                                        <!-- Table 2 -->
                                                        <div class="bx--data-table-v2-container" data-table-v2>
                                                            <h6 class="bx--data-table-v2-header">
                                                                Google SERPs
                                                                <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="SEO Data" data-pram2="Google SERPs" data-pram3="definition" title=""></i>
                                                            </h6>
                                                            <section class="bx--table-toolbar">
                                                            </section>
                                                            <table class="bx--data-table-v2 bx--data-table-v2--zebra" id="HTMLtoPDF">
                                                              <thead>
                                                                <tr>
                                                                    <th style="min-width: 130px;">
                                                                      <button class="bx--table-sort-v2" data-event="sort">
                                                                        <span class="bx--table-header-label">Keyword</span>
                                                                        <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                          <title>Sort rows by this header in descending order</title>
                                                                          <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                        </svg>
                                                                      </button>
                                                                    </th>
                                                                    <th>
                                                                      <button class="bx--table-sort-v2" data-event="sort">
                                                                        <span class="bx--table-header-label">Desktop Rank</span>
                                                                        <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                          <title>Sort rows by this header in descending order</title>
                                                                          <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                        </svg>
                                                                      </button>
                                                                    </th>
                                                                    <th>
                                                                      <button class="bx--table-sort-v2" data-event="sort">
                                                                        <span class="bx--table-header-label">Mobile Rank</span>
                                                                        <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                          <title>Sort rows by this header in descending order</title>
                                                                          <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                        </svg>
                                                                      </button>
                                                                    </th>
                                                                    <th>
                                                                    </th>
                                                                </tr>
                                                              </thead>
                                                              <!-- Table Header End -->
                                                              <!-- Table body Start -->
                                                              <tbody id="google_serp">

                                                              </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } else {
                                            ?>
                                            <h5 class="card-header p-t-25 p-b-20">
                                                SEO
                                            <?php
                                                include("notification_for_analytics_error.php");
                                            ?>
                                            </h5>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 11th Age group table -->
                            <?php if($age_group_data == 1) { ?>
                            <div class="tab-pane fade" id="age_group_data_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">
                                            <div class="card" id="demographics_age_data">
                                                <?php if ($analytics) { ?>
                                                <h5 class="card-header p-t-25 p-b-20">
                                                    Demographics: Age
                                                    <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Top Active Pages" data-pram2="Top Active Pages" data-pram3="definition" title=""></i>
                                                    <div class="display-date"></div>
                                                </h5>
                                                <div class="card-body">
                                                    <table class="bx--data-table-v2 bx--data-table-v2--zebra">
                                                        <thead>
                                                        <tr>
                                                            <th style="min-width: 130px;">
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Age</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <title>Sort rows by this header in descending order</title>
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Total Users</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <title>Sort rows by this header in descending order</title>
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">New Users</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Sessions</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Bounce Rate</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Pages / Session</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Avg. Session Duration</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Transactions</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Revenue</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Ecommerce Conversion Rate</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                            </th>
                                                        </tr>
                                                        </thead>
                                                        <tbody id="demographics_table_body">
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php } else { ?>
                                                    <h5 class="card-header border-none warning-heading">Demographics: Age</h5>
                                                    <div class="warning-container">
                                                        <?php
                                                          include("notification_for_analytics_error.php");
                                                        ?>
                                                    </div>
                                                <?php } ?>
                                            </div> <!-- card -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 12th Count of sessions -->
                            <?php if($count_of_sessions == 1) { ?>
                            <div class="tab-pane fade" id="count_of_sessions_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">
                                            <div class="card" id="count_of_sessions">
                                                <?php if ($analytics) { ?>
                                                <h5 class="card-header p-t-25 p-b-20">
                                                    Frequency & Recency
                                                    <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Top Active Pages" data-pram2="Top Active Pages" data-pram3="definition" title=""></i>
                                                    <div class="display-date"></div>
                                                </h5>
                                                <div class="card-body">
                                                    <table class="bx--data-table-v2 bx--data-table-v2--zebra">
                                                        <thead>
                                                        <tr>
                                                            <th style="min-width: 130px;">
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Count of Sessions</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <title>Sort rows by this header in descending order</title>
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Sessions</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <title>Sort rows by this header in descending order</title>
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                              <button class="bx--table-sort-v2" data-event="sort">
                                                                <span class="bx--table-header-label">Pageviews</span>
                                                                <svg class="bx--table-sort-v2__icon" width="10" height="5" viewBox="0 0 10 5" aria-label="Sort rows by this header in descending order" alt="Sort rows by this header in descending order">
                                                                  <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                                                                </svg>
                                                              </button>
                                                            </th>
                                                            <th>
                                                            </th>
                                                        </tr>
                                                        </thead>
                                                        <tbody id="sessions_count_table_body">
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php } else { ?>
                                                    <h5 class="card-header border-none warning-heading">Demographics: Age</h5>
                                                    <div class="warning-container">
                                                        <?php
                                                          include("notification_for_analytics_error.php");
                                                        ?>
                                                    </div>
                                                <?php } ?>
                                            </div> <!-- card -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- 13th Google Search Console Keywords -->
                            <?php if($google_webmasters_keywords == 1) { ?>
                            <div class="tab-pane fade" id="google_console_keywords_tab" role="tabpanel" aria-labelledby="google_ads_tab">
                                <div class="row m-b-30">
                                    <div class="col">
                                        <div class="card-deck">
                                            <div class="card box-design" id="google_webmaster">
                                                <?php if ($analytics) { ?>
                                                <h5 class="card-header p-t-25 p-b-20">
                                                    Google Search Console Keywords
                                                    <i class="fa fa-info-circle tooltip-i-icon" aria-hidden="true" onmouseover="ajaxTooltip(this)" data-pram1="Google Search Console Keywords" data-pram2="Google Search Console Keywords" data-pram3="definition" title="" style="margin-left: 5px;top: -2px;"></i>
                                                </h5>
                                                <div class="card-body">
                                                    <div id="webmasters"></div>
                                                </div>
                                                <?php } else {
                                                    ?>
                                                     <h5 class="card-header">Google Search Console Keywords
                                                    <?php
                                                  include("notification_for_analytics_error.php");
                                                  ?> </h5> <?php
                                                } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div> <!-- tab-content -->



















                    </section>
                </div>
            </div>
            <?php } ?>
        </div>
        <!-- END CONTENT WRAPPER -->

        <div class="modal fade" id="myModal">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header" style="background-color: #6a4ddf;">
                <h4 class="modal-title" style="color: #fff;">Modal title</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              </div>
              <div class="modal-body">
                <p>One fine body…</p>
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <script>
        <?php
        $dirr=dirname($_SERVER['REQUEST_URI']);
        ?>
            var url = "<?php if($dirr==''){echo ''; }else{$dirr.'/';} ?>getChartLocal.php";
            var currencySymbol = "<?php echo $currencySymbol; ?>";
        </script>
        <?php require_once 'footer.php' ?>
        <!-- ================== PAGE LEVEL VENDOR SCRIPTS ==================-->
        <script src="assets/vendor/countup.js/dist/countUp.min.js"></script>
        <!-- <script src="assets/vendor/flot/jquery.flot.js"></script> -->
        <!-- <script src="assets/vendor/jquery.flot.tooltip/js/jquery.flot.tooltip.min.js"></script> -->
        <script src="assets/vendor/flot.curvedlines/curvedLines.js"></script>
        <script src="assets/vendor/d3/dist/d3.min.js"></script>
        <script src="assets/vendor/c3/c3.min.js"></script>
        <!-- ================== MAP SCRIPTS ==================-->
        <script src="assets/vendor/jvectormap-next/jquery-jvectormap.min.js"></script>
        <script src="assets/vendor/jvectormap-next/jquery-jvectormap-world-mill.js"></script>
        <script src="assets/js/jquery-ui.min.js"></script>
        <!-- ================== PAGE LEVEL SCRIPTS ==================-->
        <script src="assets/vendor/chart.js/dist/Chart.bundle.min.js"></script>
        <script src="assets/js/charts/chartjs-init-local.js?v=<?php echo time(); ?>"></script>
        <!-- ================== DATE SCRIPTS ==================-->
        <script src="assets/vendor/moment/min/moment.min.js"></script>
        <script src="assets/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
        <script src="assets/vendor/bootstrap-daterangepicker/daterangepicker.js"></script>

        <script src="assets/js/jquery.dataTables.min.js"></script>
        <script src="assets/js/dataTables.bootstrap.min.js"></script>
        <script src="assets/js/dataTables.responsive.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/js/bootstrap-dialog.min.js"></script>
        <script>

            function IsJsonString(str) {
                try {
                    JSON.parse(str);
                } catch (e) {
                    return false;
                }
                return true;
            }

            $(document).ready(function() {

                // $('[data-toggle="tooltip"]').tooltip();

                // $('body').tooltip({
                //     selector: "[data-toggle='tooltip']",
                //     trigger: "click"
                // });

                $('input[name="dates"]').daterangepicker({
                    opens: 'left',
                    startDate: moment().subtract(29, 'days'),
                    endDate: moment(),
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    }
                });

            });

            function setCustomChartOptions() {
                //var merticsKey = $('.metrics option').prop('selected', true).val();
                var merticsKey = [];
                $('.metrics option').each(function(){
                    if ($(this).val() == 'ga:sessions') {
                        $(this).attr('selected', 'selected');
                        $('.metrics').change();
                    }
                   merticsKey.push($(this).val());
                });
            }

            $(document).on("change", ".metrics", function() {
                var dimension = ($(this).find(':selected').data('dimension'));
                $(".dimension").html('');
                var html = '<option value="">Dimension</option>';
                $.each(dimension, function(k, v) {
                    html += '<option value="' + k + '" '+ (v == 'Medium' ? 'selected' : '') +' >' + v + '</option>';
                });
                $(".dimension").html(html);
            });

            // changing AI titles according to seleted time period
            $(document).on("click", ".chart_duration .nav-item .nav-link", function () {

                var duration = $(".chart_duration .nav-item .active .period").html();
                if (duration == 'Today') {

                    $(".time-period").text('01 Day');
                    $("#main-facebook_analytics").text("Facebook Ads data for today");
                    $("#main-facebook_roas").text("Facebook ROAS data for today");
                    $("#main_ecommerce_analytics").text('Ecommerce Data for last 1 day');
                    $("#main-ai-ecommerce-box").text('Ecommerce AI Forecast for next 1 day');
                    $("#main-ai-ads-box").text('Google ads AI Forecast for next 1 day');

                } else if (duration == 'Yesterday') {

                    $(".time-period").text('01 Day');
                    $("#main-facebook_analytics").text("Facebook Ads data for last 1 day");
                    $("#main-facebook_roas").text("Facebook ROAS data for last 1 day");
                    $("#main_ecommerce_analytics").text('Ecommerce Data for last 1 day');
                    $("#main-ai-ecommerce-box").text('Ecommerce AI Forecast for next 1 day');
                    $("#main-ai-ads-box").text('Google ads AI Forecast for next 1 day');

                } else if (duration == 'Week') {

                    $(".time-period").text('07 Days');
                    $("#main-facebook_analytics").text("Facebook Ads data for last 7 days");
                    $("#main-facebook_roas").text("Facebook ROAS data for last 7 days");
                    $("#main_ecommerce_analytics").text('Ecommerce Data for last 7 days');
                    $("#main-ai-ecommerce-box").text('Ecommerce AI Forecast for next 7 days');
                    $("#main-ai-ads-box").text('Google ads AI Forecast for next 7 days');

                } else if (duration == 'Month') {

                    $(".time-period").text('30 Days');
                    $("#main-facebook_analytics").text("Facebook Ads data for last 30 days");
                    $("#main-facebook_roas").text("Facebook ROAS data for last 30 days");
                    $("#main_ecommerce_analytics").text('Ecommerce Data for last 30 days');
                    $("#main-ai-ecommerce-box").text('Ecommerce AI Forecast for next 30 days');
                    $("#main-ai-ads-box").text('Google ads AI Forecast for next 30 days');

                } else if (duration == 'Year') {

                    $(".time-period").text('01 Year');
                    $("#main-facebook_analytics").text("Facebook Ads data for last 1 year");
                    $("#main-facebook_roas").text("Facebook ROAS data for last 1 year");
                    $("#main_ecommerce_analytics").text('Ecommerce Data for last 1 year');
                    $("#main-ai-ecommerce-box").text('Ecommerce AI Forecast for next Year');
                    $("#main-ai-ads-box").text('Google ads AI Forecast for next Year');

                }
            });

            setCustomChartOptions();

            $(window).on('load', function(){
                window.setTimeout(function(){
                    $('#custom_form button').click();
                }, 200);
            });
        </script>

        <script>
            // show popup from where it clicks not at the top
            window.addEventListener('message', function(event) {
              var messageContent = event.data.split(':');
              var topOffset = messageContent[0];
              var currentScroll = messageContent[1];

              //calculate padding value and update the modal top-padding
              $('#myModal').css("top", currentScroll+'px');

            }, false);

        </script>
    </body>
</html>
