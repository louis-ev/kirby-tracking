<?php

/**
 * Kirby tracking plugin
 * javascript-based log for visitor events in a page
 *
 * v 0.21
 */

include __DIR__ . DS . 'routes.php';

$kirby->set('blueprint', 'kirbytracking_global',  __DIR__ . DS . 'blueprints' . DS . 'kirbytracking_global.yml' );
$kirby->set('blueprint', 'kirbytracking_monthly', __DIR__ . DS . 'blueprints' . DS . 'kirbytracking_monthly.yml');
$kirby->set('blueprint', 'kirbytracking_visitor', __DIR__ . DS . 'blueprints' . DS . 'kirbytracking_visitor.yml');
$kirby->set('widget', 'analytics', __DIR__ . DS . 'widgets' . DS . 'analytics');

function log_event($sessionid, $data) {

  $trackingpage = getTrackingPage();

  if(!array_key_exists('epochdate', $data))
    return;
  $epochdate = strtotime(esc($data['epochdate']));

  $typeOfVisitor = 'visitor';
    if($user = site()->user() and $user->hasPanelAccess()) {
    if($trackingpage->doNotLogLogged()->bool()) {
      return;
    } else {
      $typeOfVisitor = 'admin';
    }
  } else {
    if(preg_match("/Googlebot|MJ12bot|yandexbot|Google Page Speed Insights|crawler|spider|robot|crawling|baidu|bing|msn|duckduckgo|teoma|slurp|yandex|Coda,/i", $data['useragent'])):
      if($trackingpage->doNotLogBots()->bool()) {
        return;
      } else {
        $typeOfVisitor = 'bot';
      }
    endif;
  }

  // make a page name : either a sessionid if there's one, or the epoch if not
  $pagename = !empty($sessionid) ? $sessionid : 'v'.$epochdate;

  // LOGS
  $logs = array(
  );


  if(!$trackingpage->find($pagename)):

    // create a page with : a TITLE, DATE, IP, BROWSER
    $currentTrackingNumber = $trackingpage->children()->visible()->count() + 1;
    $serverDateHR = date('Y-m-d • H:i:s', time());
    $dateSec = date('YmdHis', $epochdate);

    $IP = array_key_exists('IP', $data) ?                                     esc($data['IP']) : '';
    $browser = array_key_exists('browser', $data) ?                           esc($data['browser']) : '';
    $useragent = array_key_exists('useragent', $data) ?                       esc($data['useragent']) : '';
    $lang = array_key_exists('lang', $data) ?                                 esc($data['lang']) : '';
    $window_size = array_key_exists('window_size', $data) ?                   esc($data['window_size']) : '';

    $logpage = $trackingpage->children()->create($currentTrackingNumber . '-' . $pagename, 'kirbytracking_visitor', array(
      'title' => $serverDateHR . ' — ' . $typeOfVisitor . ' on ' . $browser,
      'date'  => $dateSec,
      'IP'  => $IP,
      'browser'  => $browser,
      'useragent'  => $useragent,
      'lang' => $lang,
      'window_size' => $window_size,
      'log' => var_export($logs, true)
    ));

    // store first visit time in session id
    s::set('timestamp_first_visit', $epochdate);

  else:
    $logpage = $trackingpage->find($sessionid);
  endif;

  $event_page = array_key_exists('event_page', $data) ? esc($data['event_page']) : '-';
  $event_page = str_replace(site()->url(), '~', $event_page);

  $event_type = array_key_exists('event_type', $data) ? esc($data['event_type']) : '-';
  $event_page = str_replace(site()->url(), '~', $event_page);

  // measure the time delta since first visit
  $deltaSinceFirstVisit = $epochdate - s::get('timestamp_first_visit');

  $timeSinceFirstVisit = gmdate('H:i:s', $deltaSinceFirstVisit/1000) . '.' . $deltaSinceFirstVisit % 1000;

  $event = array(
    'event_time' => $timeSinceFirstVisit,
    'event_page' => $event_page,
    'event_type' => $event_type
  );

  return addToStructure($logpage, 'events', $event);

}


// from https://forum.getkirby.com/t/add-method-to-append-to-structure-field/494/6
function addToStructure($page, $field, $data = array()){
  $fieldData = page($page)->$field()->yaml();
  $fieldData[] = $data;
  $fieldData = yaml::encode($fieldData);
  try {
    page($page)->update(array($field => $fieldData));
    return true;
  } catch(Exception $e) {
    return $e->getMessage();
  }
}

// from https://github.com/FabianSperrle/kirby-stats/blob/master/site/widgets/stats/helpers.php
function getTrackingPage() {

  // find or create tracking page
  $tracking = page('kirby-tracking');
  if (!$tracking) {
    try {
      $tracking = site()->create('kirby-tracking', 'kirbytracking_global', array(
        'title' => 'Tracking'
      ));
    } catch (Exception $e) {
      exit;
    }
  }

  // find the month page
  $currentMonth = date('F Y');
  $monthlyPage = $tracking->children()->find(str::slug($currentMonth));

  if (!$monthlyPage) {
    try {
      $folderPrefix = date('Ymd');

      $monthlyPage = $tracking->children()->create($folderPrefix . '-' . str::slug($currentMonth), 'kirbytracking_monthly', array(
        'title' => $currentMonth,
        'date'  => $folderPrefix
      ));
    } catch (Exception $e) {
      exit;
    }
  }

  return $monthlyPage;
}
