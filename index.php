<?php

set_time_limit(1800); // 30 minutes in seconds

function get_location($ip) {
    $url = "http://ip-api.com/csv/{$ip}?fields=continent,continentCode,country,countryCode,region,city,lat,lon,timezone,isp";
    $response = file_get_contents($url);
    
    if ($response !== false) {
        $data = explode(',', trim($response));
        list($continent, $continentCode, $country, $countryCode, $region, $city, $lat, $lon, $timezone, $isp) = $data;
        return array(
            "continent" => $continent,
            "continentCode" => $continentCode,
            "country" => $country,
            "countryCode" => $countryCode,
            "region" => $region,
            "city" => $city,
            "lat" => $lat,
            "lon" => $lon,
            "timezone" => $timezone,
            "isp" => $isp
        );
    }
    
    return array(
        "continent" => null,
        "continentCode" => null,
        "country" => null,
        "countryCode" => null,
        "region" => null,
        "city" => null,
        "lat" => null,
        "lon" => null,
        "timezone" => null,
        "isp" => null
    );
}

function update_csv($input_file, $output_file, $batch_size, $sleep_duration) {
    $input_handle = fopen($input_file, "r");
    $output_handle = fopen($output_file, "w");
    
    $header = fgetcsv($input_handle);
    $header = array_merge($header, array("Continent", "Continent Code", "Country", "Country Code", "Region", "City", "Latitude", "Longitude", "Timezone", "ISP"));
    fputcsv($output_handle, $header);
    
    $batch = array();
    $count = 0;
    
    while (($row = fgetcsv($input_handle)) !== false) {
        $ip = $row[0];
        $location = get_location($ip);
        $batch[] = array_merge($row, $location);
        $count++;
        
        if ($count >= $batch_size) {
            foreach ($batch as $batch_row) {
                fputcsv($output_handle, $batch_row);
            }
            $batch = array();
            $count = 0;
            sleep($sleep_duration);
        }
    }
    
    if (!empty($batch)) {
        foreach ($batch as $batch_row) {
            fputcsv($output_handle, $batch_row);
        }
    }
    
    fclose($input_handle);
    fclose($output_handle);
}

$input_csv_file = "input.csv";
$output_csv_file = "output.csv";
$batch_size = 150;
$sleep_duration = 3; // in seconds

update_csv($input_csv_file, $output_csv_file, $batch_size, $sleep_duration);
echo "CSV file updated with additional details.";

?>
