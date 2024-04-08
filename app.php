<?php

// connect to database
$server_name = ""; // replace with the actual server name
$database_name = ""; // replace with the actual database name
try {
// connect to database
    $port = 1; // replace with the actual port number
    $socket = ""; // replace with the actual socket
    $user = ""; // replace with the actual username
    $pass = "";


    $con = new mysqli($server_name, $user, $pass, $database_name, $port, $socket)
    or die ('Could not connect to the database server' . mysqli_connect_error());

// execute the query
    //get_and_save_cover_from_list();
    $result = get_prices();
    echo "<pre>";
    print_r($result);
    echo "</pre>";

    $con->query($result);


    $con->close();
} catch (PDOException $e) {

    $e->getMessage();

}


// a function to get the small cover jpg from the Open Library API and save the image to the folder 'covers'
function get_cover($isbn)
{
    $url = "https://covers.openlibrary.org/b/isbn/$isbn-S.jpg";
    $img = "small_covers/$isbn.jpg";

    // before saving the image, check if it already exists
    if (file_exists($img)) {
        return $img;
    }

    file_put_contents($img, file_get_contents($url));
    return $img;
}

// a function that returns a random number between 1 and 13
function random_number()
{
    return rand(1, 5);
}

function get_and_save_cover_from_list()
{
// create a variable isbn_list that contains an array of ISBNs
    $isbn_list = array("9780134093413", "9780399588198", "9781585421466", "9780486459462", "9781585421466", "9780399588198", "9780486459462", "9780785834007", "9780812993110", "9780449911778", "9780452289963", "9781878424686", "9781592409662", "9780470643471", "9781401904593", "9781401931698", "9781601632111", "9780679778318", "9781118968055", "7806611000513", "0689145739756");

// a loop that waits for n seconds between iterations
    for ($i = 0; $i < 10; $i++) {
        sleep(random_number());
        $isbn = $isbn_list[$i];
        $cover = get_cover($isbn);
        echo "<img src='$cover' alt='cover'>";

    }

}

// check prices for a book by ISBN via the ISBNdb API
function get_prices()
{
    $restKey = SECRET_KEY;

    // create a variable isbn_list that contains an array of ISBNs
    $isbn_list = array("9780134093413", "9780399588198", "9781585421466", "9780486459462", "9781585421466", "9780399588198", "9780486459462", "9780785834007", "9780812993110", "9780449911778", "9780452289963", "9781878424686", "9781592409662", "9780470643471", "9781401904593", "9781401931698", "9781601632111", "9780679778318", "9781118968055", "7806611000513", "0689145739756");

    $headers = array(
        "Content-Type: application/json; charset=utf-8",
        "Authorization: " . $restKey,
        "Access-Control-Allow-Origin: https://api2.isbndb.com"
    );

    $isbn = 0;
    $url = "";
    $sql = "";

// a loop that waits for n seconds between iterations
    for ($i = 0; $i < 10; $i++) {
        //sleep(random_number());
        $isbn = $isbn_list[$i];
        $url = 'https://api2.isbndb.com/book/' . $isbn; // . '?with_prices=1';

        $rest = curl_init();
        curl_setopt($rest, CURLOPT_URL, $url);
        curl_setopt($rest, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($rest, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($rest);
        $data = json_decode($response, true);
        curl_close($rest);

        echo json_encode($response);

        // insert row into the database
        $sql .= <<<EOD77
INSERT INTO table_name (isbn, title, author, cover_img, dimensions, height, height_units, 
        width, width_units, length, length_units, weight, weight_units) VALUES ($isbn, 
            "{$data["book"]["title"]}", "{$data["book"]["authors"]["0"]}", "{$data["book"]["image"]}", 
            "{$data["book"]["dimensions"]}", {$data["book"]["dimensions_structured"]["height"]["value"]}, 
            "{$data["book"]["dimensions_structured"]["height"]["unit"]}", 
            {$data["book"]["dimensions_structured"]["width"]["value"]}, 
            "{$data["book"]["dimensions_structured"]["width"]["unit"]}", 
            {$data["book"]["dimensions_structured"]["length"]["value"]}, 
            "{$data["book"]["dimensions_structured"]["length"]["unit"]}", 
            {$data["book"]["dimensions_structured"]["weight"]["value"]}, 
            "{$data["book"]["dimensions_structured"]["weight"]["unit"]}");

EOD77;
        // replace table_name with the actual table name
    }

    return $sql;
}
