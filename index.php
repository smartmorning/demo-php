<?php
    $action = isset($_GET['action']) ? $_GET['action'] : NULL; //gets action type
    switch ($action) {
        case 'set':
            setTime(getValues(), readData());
            break;
        case 'get':
            getTime(readData());
            break;
        case 'next':
            getNext(readData());
            break;
        default:
            print '{"result": "error", "message": "No action is set. Use ?action=set to set a new time, ?action=get all upcoming alarms, ?action=next get only the first upcoming alarm."}';
            break;
    }

    function getValues() {
        $values = new stdClass();
        //getting values (year,month,day....etc) from  url, if field is not set, will be NULL by default
        $values->hour = isset($_GET['hour']) ? $_GET['hour'] : NULL;
        $values->minute = isset($_GET['minute']) ? $_GET['minute'] : NULL;
        $values->day = isset($_GET['day']) ? $_GET['day'] : NULL;
        $values->month = isset($_GET['month']) ? $_GET['month'] : NULL;
        $values->year = isset($_GET['year']) ? $_GET['year'] : NULL;
        $values->person = isset($_GET['person']) ? $_GET['person'] : NULL;

        //returning all the values in an object
        return $values;
    }

    function readData() {
        $file = "timestamps.json"; 
        $sorted_timestamp_array = array();
        
        // If there is not yet a data file, return an empty array 
        if (!file_exists($file)) {
            return array();
        }

        $timestamp_json = file_get_contents($file);
        if ($timestamp_json === FALSE) {
            throw new Exception("Cannot access " . $file . " to read contents.");
        }
            
        
        // Only keep alarm timestamps that are in the future
        foreach (json_decode($timestamp_json) as $person => $alarm_timestamp) {
            if ($alarm_timestamp > time()) {
                $sorted_timestamp_array[$person] = $alarm_timestamp;
            }
        }
        asort($sorted_timestamp_array); // sorting the array in descending order
        return $sorted_timestamp_array;
    }

    function formatAlarm($person, $alarm_timestamp){
        $output = new stdClass();
        $output->person = $person;
        $output->alarm = $alarm_timestamp;
        $output->alarm_iso = strftime("%Y-%m-%d %H:%M:%S", $alarm_timestamp);
        $output->alarm_delta_in_minutes = round((time()-$alarm_timestamp)/60);
        return $output;
    }

    function setTime($values, $timestamp_array) {
        $current_timestamp = time(); //gets current time
        $new_timestamp_array = []; //the array in which the filter data will be stored to
        $sum = $values->hour + $values->minute + $values->day + $values->month + $values->year; //adding the Date time to check for null later..
        $timestamp = strtotime($values->day . "-" . $values->month . "-" . $values->year . " " . $values->hour . ":" . $values->minute); //cretaing the new timestamp
        
        //if the new timestamp is not null, and greater then the current time, it is added 
        if ($sum > 0 && $timestamp > $current_timestamp) {
            $new_timestamp_array[$values->person] = $timestamp;
        }

        foreach ($timestamp_array as $key => $value) {
            //filtering the old timestamps for expired ones, and similar to the new one
            if ($value > $current_timestamp && $key != $values->person) { 
                $new_timestamp_array[$key] = $value;
            }
        }
        if (file_put_contents("timestamps.json", json_encode($new_timestamp_array))) {
            echo json_encode($new_timestamp_array);
        } else
            print '{"result": "error", "message": "Error while writing file timestamps.json"}';
        ;
    }

    function getTime($timestamp_array) {
        $output = new stdClass();
        $output->result = 'ok';
        $output->alarms = array();
        
        foreach ($timestamp_array as $person => $alarm_timestamp) {
            $alarm = new stdClass();
            $alarm->person = $person;
            $alarm->alarm = $alarm_timestamp;
            $alarm->alarm_iso = strftime("%Y-%m-%d %H:%M:%S", $alarm_timestamp);

            $output->alarms[] = $alarm;
        }
        print json_encode($output);
    }

    function getNext($timestamp_array) {
        $output = false;
           
        foreach ($timestamp_array as $person => $alarm_timestamp) {
            $output = formatAlarm($person, $alarm_timestamp);
            break;
        }
        if ($output) {
            $output->result = 'ok';
        } else {
            $output->result = 'error';
            $output->message = 'No upcoming alarms';
        }
        print json_encode($output);
    }