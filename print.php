<?php
require_once __DIR__.'/require.php';
session_start();
$client = newGoogleClient();

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  /* Create new Google_Service_Calendar object with access token */
  $client->setAccessToken($_SESSION['access_token']);
  $service = new Google_Service_Calendar($client);
//  register_shutdown_function('atExit', $service);

  /* Create new Google_Service_Calendar_Calendar object and insert into service */
  $calendar = newGoogleCalendar();
  $new_calendar = $service->calendars->insert($calendar);

  /* Create new Excel object from CSV data */
  $file = openCSVFile();
  $data = fgetcsv($file, 1000, ",");  //  Get and discard the first line.

  for ($i = 0; $i < 3; $i++) {
    $data = fgetcsv($file, 1000, ",");  //  Get first set of data (change to for-loop later).
    $excel = new Excel($data);

    // Create new Google_Service_Calendar_Event object from Excel object and insert into new_calendar.
    $event = newGoogleCalendarEvent($excel);
    $event = $service->events->insert($new_calendar->getId(), $event);
  }
  fclose($file);


  // Print the next 10 events on the newly created calendar.
  $calendar_id = $new_calendar->getId();
  $optParams = array(
    'maxResults' => 10,
    'orderBy' => 'startTime',
    'singleEvents' => TRUE,
    'timeMin' => date('c'),
  );
  $results = $service->events->listEvents($calendar_id, $optParams);

  if (count($results->getItems()) == 0) {
    echo "<br>No upcoming events found.</br>";
  } else {
    echo sprintf("<br>Calendar for %s</br>", $_SESSION["netid"]);
    echo "<br>Upcoming events in new calendar:</br>";
    foreach ($results->getItems() as $event) {
      $start = $event->start->dateTime;
      if (empty($start)) {
        $start = $event->start->date;
      }
      echo sprintf("<br>%s (%s)</br>", $event->getSummary(), $start);
    }
  }
}
else {
  echo "Error: access_token should be set";
}
?>
