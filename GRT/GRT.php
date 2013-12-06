<?php


class API {

  protected $data_directory = '.';

  public function csv_to_array($filename, $header=NULL, $delimiter=',') {
    if(!file_exists($filename) || !is_readable($filename)) {
      return false;
    }

    $data = array();

    if(($handle = fopen($filename, 'r')) !== FALSE) {
        while(($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            $row = array_map("trim", $row);
            if($header == NULL) {
                $header = $row;
            } else {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }

    return $data;
  }

}


class GRT extends API {

  public function get_stops_list() {
    $file = $this->data_directory.'/grt/stops.txt';
    return $this->csv_to_array($file);
  }


  public function get_trip_details($trip_id) {
    return $trip_id;
  }


  private function helper_xyz() {
    return 42;
  }

}


?>
