<?php
  // -------------------------------------------------------------
  //                             REQUIRES
  // -------------------------------------------------------------

  require_once __DIR__.'/vendor/autoload.php';

  function atExit($service) {
    // TEMP: Delete testing calendars.
    $calendar_list = $service->calendarList->listCalendarList();

    while(true) {
     foreach ($calendar_list->getItems() as $calendar_list_entry) {
       if ($calendar_list_entry->getSummary() == 'Fall Semester 2017') {
         $service->calendarList->delete($calendar_list_entry->getId());
       }
     }

     $page_token = $calendar_list->getNextPageToken();
     if ($page_token) {
       $opt_params = array('pageToken' => $page_token);
       $calendar_list = $service->calendarList->listCalendarList($opt_params);
     } else {
       break;
     }
    }
  }


  // -------------------------------------------------------------
  //                             DEFINES
  // -------------------------------------------------------------

  /* Excel() */
  define('GMT', '-04:00');

  /* newGoogleClient() */
  define('APPLICATION_NAME', 'ND OIT PHP Quickstart');
  define('CLIENT_SECRETS', 'client_secrets.json');
  define('SCOPES', implode(' ', array(
    Google_Service_Calendar::CALENDAR)  //  CALENDAR_READONLY --> CALENDAR
  ));
  define('ACCESS_TYPE', 'offline');

  /* newGoogleCalendar() */
  define('CAL_SUMMARY', 'Fall Semester 2017');
  define('CAL_DESCRIPTION', 'ND OIT Course  Scheduler');
  define('CAL_TIMEZONE', 'America/New_York');

  /* openCSVFile() */
  define('CSV_FILE', 'fall2017_excel_to_csv.csv');


  // -------------------------------------------------------------
  //                             CLASSES
  // -------------------------------------------------------------

  class Excel {
    public $summary;
    public $location;
    public $description;
    public $start;
    public $end;
    public $recurrence;

    function __construct($data) {
      $this->summary = $data[2] . $data[14] . ' - ' . $data[12];
      $this->location = $data[8] . ':' . $data[9];  //  [Building Code]:[Room Code]
      $this->description = 'No Description Yet';

      // e.g. DTSTART;TZID=America/New_York:20170822T153000 (YR MO DAY T HR MIN S)
      $date = explode('/', $data[4]);  //  MONTH/DAY/YEAR
      for ( $i = 0; $i < count($date); $i++) {
        $date[$i] = str_pad($date[$i], 2, '0', STR_PAD_LEFT);
      }

      // e.g. 2015-04-26T19:00:00.000+10:00 (YR-MO-DAY T HR:MIN:S +/- GMT)
      $this->start = array(
        'dateTime' => $date[2] . '-' . $date[0] . '-' . $date[1] . 'T' . substr($data[6], 0, strlen($data[6])/2) . ':' . substr($data[6], strlen($data[6])/2) . ':00' . GMT,
        'timeZone' => CAL_TIMEZONE,
      );
      $this->end = array(
        'dateTime' => $date[2] . '-' . $date[0] . '-' . $date[1] . 'T' . substr($data[7], 0, strlen($data[7])/2) . ':' . substr($data[7], strlen($data[7])/2) . ':00' . GMT,
        'timeZone' => CAL_TIMEZONE,
      );

      $this->recurrence = array(
        makeRRule($data[10]),
      );
    }
  }


  // -------------------------------------------------------------
  //                             FUNCTIONS
  // -------------------------------------------------------------

  function newGoogleClient() {
    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setAuthConfig(CLIENT_SECRETS);
    $client->addScope(SCOPES);
    $client->setAccessType(ACCESS_TYPE);
    return $client;
  }

  function newGoogleCalendar() {
    $calendar = new Google_Service_Calendar_Calendar();
    $calendar->setSummary(CAL_SUMMARY);
    $calendar->setDescription(CAL_DESCRIPTION);
    $calendar->setTimeZone(CAL_TIMEZONE);
    return $calendar;
  }

  function openCSVFile() {
    try {
      if ( !file_exists(CSV_FILE) ) {
        throw new Exception('File not found.');
      }

      $file = fopen(CSV_FILE, 'r');
      if ( !$file ) {
        throw new Exception('File open failed.');
      }

      return $file;
    }
    catch (Exception $e) {
      exit($e);
    }
  }

  function newGoogleCalendarEvent($excel) {
    $event = new Google_Service_Calendar_Event();
    $event->setSummary($excel->summary);
    $event->setDescription($excel->description);

    $start = new Google_Service_Calendar_EventDateTime();
    $start->setDateTime($excel->start['dateTime']);
    $start->setTimeZone($excel->start['timeZone']);
    $event->setStart($start);
    
    $end = new Google_Service_Calendar_EventDateTime();
    $end->setDateTime($excel->end['dateTime']);
    $end->setTimeZone($excel->end['timeZone']);
    $event->setEnd($end);

    $event->setRecurrence(array(current($excel->recurrence)));
    return $event;
  }

  function makeRRule($data) {
    $strings = explode(' ', $data);  //  R 02:00 PM - 05:30 PM --> ['R', '02:00', 'PM', '-', '05:30', 'PM']
    $days = current($strings);
    $split_days = str_split($days);
    $rrule_days = '';

    // Convert the excel days to rrule days e.g. M --> MO.
    for ( $i = 0; $i < count($split_days); $i++) {
      // TODO: Figure out Sunday and Saturday codes in excel file.
      switch ($split_days[$i]) {
        // case 'S':
        //  $rrule_days .= 'SU';
        //  break;
        case 'M':
          $rrule_days .= 'MO';
          break;
        case 'T':
          $rrule_days .= 'TU';
          break;
        case 'W':
          $rrule_days .= 'WE';
          break;
        case 'R':
          $rrule_days .= 'TH';
          break;
        case 'F':
          $rrule_days .= 'FR';
          break;
        // case '??':
        //  $rrule_days .= 'SA';
        //  break;
        default:
          exit();
      }
      if ($i != count($split_days) - 1) {
        $rrule_days .= ',';
      }
    }

    return 'RRULE:FREQ=WEEKLY;' . 'BYDAY=' . $rrule_days . ';' . 'UNTIL=20171206T115900Z';
  }
