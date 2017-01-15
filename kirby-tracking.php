<?php

/**
 * Kirby tracking plugin
 * javascript-based log for visitor events in a page
 *
 * v 0.21
 */

include __DIR__ . DS . 'routes.php';

$kirby->set('blueprint', 'kirbytracking_global', __DIR__ . '/blueprints/kirbytracking_global.yml');
$kirby->set('blueprint', 'kirbytracking_visitor', __DIR__ . '/blueprints/kirbytracking_visitor.yml');

function log_event($sessionid, $data) {

  $trackingpage = getTrackingPage();

  if(!array_key_exists('epochdate', $data))
    return;
  $epochdate = intval($data['epochdate']);

  $typeOfVisitor = 'visitor';
  	if($user = site()->user() and $user->hasPanelAccess()) {
    if($trackingpage->doNotLogLogged()->bool()) {
      return;
    } else {
      $typeOfVisitor = 'admin';
    }
  } else {
    if(preg_match("/Googlebot|MJ12bot|yandexbot|Google Page Speed Insights/i", $data['useragent'])):
      $typeOfVisitor = 'bot';
    endif;
  }

  $date = date('Ymd', $epochdate/1000);

  $pagename = isset($sessionid) ? $sessionid:'visitor';

  if(!$trackingpage->find($pagename)):
    // create a page with : a TITLE, DATE, IP, BROWSER
    $currentTrackingNumber = $trackingpage->children()->visible()->count() + 1;
    $serverDateHR = date('Y-m-d • H:i:s', time());
    $dateSec = date('YmdHis', $epochdate/1000);

    $IP = array_key_exists('IP', $data) ? $data['IP'] : '';
    $browser = array_key_exists('browser', $data) ? $data['browser'] : '';
    $useragent = array_key_exists('useragent', $data) ? $data['useragent'] : '';
    $lang = array_key_exists('lang', $data) ? $data['lang'] : '';
    $window_size = array_key_exists('window_size', $data) ? $data['window_size'] : '';

    $logpage = $trackingpage->children()->create($currentTrackingNumber . '-' . $pagename, 'kirbytracking_visitor', array(
      'title' => $serverDateHR . ' — ' . $typeOfVisitor . ' sur ' . $browser,
      'date'  => $dateSec,
      'IP'  => $IP,
      'browser'  => $browser,
      'useragent'  => $useragent,
      'lang' => $lang,
      'window_size' => $window_size,
    ));

    // store first visit time in session id
    s::set('timestamp_first_visit', $epochdate);

  else:
    $logpage = $trackingpage->find($sessionid);
  endif;

  $event_page = array_key_exists('event_page', $data) ? $data['event_page'] : '-';
  $event_page = str_replace(site()->url(), '~', $event_page);

  $event_type = array_key_exists('event_type', $data) ? $data['event_type'] : '-';
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
	return $tracking;
}

