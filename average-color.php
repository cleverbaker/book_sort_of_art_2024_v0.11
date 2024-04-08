<?php

// initialize a associative array with id's "id", "isbn", and "cover_img"
$books = array(
    array("id" => 3, "isbn" => "9780399588198", "cover_img" => "https://images.isbndb.com/covers/81/98/9780399588198.jpg"),
    array("id" => 4, "isbn" => "9780134093413", "cover_img" => "https://images.isbndb.com/covers/34/13/9780134093413.jpg"),
    array("id" => 5, "isbn" => "9780134093413", "cover_img" => "https://images.isbndb.com/covers/34/13/9780134093413.jpg"),
    array("id" => 6, "isbn" => "9780399588198", "cover_img" => "https://images.isbndb.com/covers/81/98/9780399588198.jpg"),
    array("id" => 7, "isbn" => "9781585421466", "cover_img" => "https://images.isbndb.com/covers/14/66/9781585421466.jpg"),
    array("id" => 8, "isbn" => "9780486459462", "cover_img" => "https://images.isbndb.com/covers/94/62/9780486459462.jpg"),
    array("id" => 9, "isbn" => "9781585421466", "cover_img" => "https://images.isbndb.com/covers/14/66/9781585421466.jpg"),
    array("id" => 10, "isbn" => "9780399588198", "cover_img" => "https://images.isbndb.com/covers/81/98/9780399588198.jpg"),
    array("id" => 11, "isbn" => "9780486459462", "cover_img" => "https://images.isbndb.com/covers/94/62/9780486459462.jpg"),
    array("id" => 12, "isbn" => "9780785834007", "cover_img" => "https://images.isbndb.com/covers/40/07/9780785834007.jpg"),
    array("id" => 13, "isbn" => "9780812993110", "cover_img" => "https://images.isbndb.com/covers/31/10/9780812993110.jpg"),
    array("id" => 14, "isbn" => "9780449911778", "cover_img" => "https://images.isbndb.com/covers/17/78/9780449911778.jpg"),
    array("id" => 15, "isbn" => "9780134093413", "cover_img" => "https://images.isbndb.com/covers/34/13/9780134093413.jpg"),
    array("id" => 16, "isbn" => "9780399588198", "cover_img" => "https://images.isbndb.com/covers/81/98/9780399588198.jpg"),
    array("id" => 17, "isbn" => "9781585421466", "cover_img" => "https://images.isbndb.com/covers/14/66/9781585421466.jpg"),
    array("id" => 18, "isbn" => "9780486459462", "cover_img" => "https://images.isbndb.com/covers/94/62/9780486459462.jpg"),
    array("id" => 19, "isbn" => "9781585421466", "cover_img" => "https://images.isbndb.com/covers/14/66/9781585421466.jpg"),
    array("id" => 20, "isbn" => "9780399588198", "cover_img" => "https://images.isbndb.com/covers/81/98/9780399588198.jpg"),
    array("id" => 21, "isbn" => "9780486459462", "cover_img" => "https://images.isbndb.com/covers/94/62/9780486459462.jpg"),
    array("id" => 22, "isbn" => "9780785834007", "cover_img" => "https://images.isbndb.com/covers/40/07/9780785834007.jpg"),
    array("id" => 23, "isbn" => "9780812993110", "cover_img" => "https://images.isbndb.com/covers/31/10/9780812993110.jpg"),
    array("id" => 24, "isbn" => "9780449911778", "cover_img" => "https://images.isbndb.com/covers/17/78/9780449911778.jpg")
);

// initialize a variable of an associative array of book ISBNs and their average color
$average_colors = array();

// initialize a variable called $html_out to be an empty string
$html_out = "";

// initialize a variable called $sql_out to be an empty string
$sql_out = "";
$sql_out1 = "";

$div_start = "<div";
$div_style_start = " style='display: inline-block; width: 5em; margin:1em; height: 5em; background-color: rgb(";
$div_style_end = ")'>";
$div_end = "</div>";

// loop through all of the image files in the 'small_covers' folder, detect the color of the pixel at top-left
// corner, and save the average color to the $average_colors array
foreach (glob("small_covers/*.jpg") as $filename) {
    $image = imagecreatefromjpeg($filename);

    // initialize the variable isbn to be the filename without the extension
    $isbn = pathinfo($filename, PATHINFO_FILENAME);

    $rgb = imagecolorat($image, 0, 0);
    $r = ($rgb >> 16) & 0xFF;
    $g = ($rgb >> 8) & 0xFF;
    $b = $rgb & 0xFF;
    $average_colors[$isbn] = array($r, $g, $b);
    imagedestroy($image);

    $html_out .= $div_start . $div_style_start . $r . "," . $g . "," . $b . $div_style_end . $div_end;
}

echo "<div style='text-align:center; max-width: 800px; word-wrap: anywhere'>";
echo $html_out;
echo "</div>";
var_dump($average_colors);


// loop through books array and update the 'average_color' column with the top-left color
foreach ($books as $book) {
    $isbn = $book['isbn'];
    $average_color = get_top_left_color($book['cover_img'], $isbn);
    $average_color = "({$average_color[0]}, {$average_color[1]}, {$average_color[2]})";
    $sql_out1 .= "UPDATE table_name SET average_color='".$average_color."' WHERE id={$book['id']}; "; // replace table_name with the actual table name
}
echo <<<ABB
\n
$sql_out1
\n
ABB;


// connect to the database and get data from the table
$server_name = ""; // server name
$database_name = ""; // database name
$port = 1; // port number
$socket = ""; // socket
$user = ""; // username
$pass = "";

$con2 = new mysqli($server_name, $user, $pass, $database_name, $port, $socket) or die ('Could not connect to the database server' . mysqli_connect_error());


// initialize a sql query to get id, isbn, cover_img from the table
$sql = "SELECT id, isbn, cover_img FROM table_name"; // replace table_name with the actual table name
$result = $con2->query($sql);

// loop through the result and update the 'average_color' column with the average color of the book cover
while ($row = $result->fetch_assoc()) {
    $isbn = $row['isbn'];
    $average_color = get_top_left_color($row['book_cover'], $isbn);
    $average_color = "({$average_color[0]}, {$average_color[1]}, {$average_color[2]})";
    $sql_out .= "UPDATE table_name SET average_color='".$average_color."' WHERE isbn='$isbn'; "; // replace table_name with the actual table name
}
echo $sql_out;
//$con2->query($sql_out);

// create a function that returns the top-left color of the book cover image from the book_cover URL in the result set
function get_top_left_color($url, $isbn)
{
    $img_saved_name = "covers/$isbn.jpg";
    $image = imagecreatefromjpeg($url);
    $rgb = imagecolorat($image, 0, 0);
    $r = ($rgb >> 16) & 0xFF;
    $g = ($rgb >> 8) & 0xFF;
    $b = $rgb & 0xFF;

    // before saving the image, check if it already exists
    if (!file_exists($img_saved_name)) {
        file_put_contents($img_saved_name, file_get_contents($url));
    }


    imagedestroy($image);
    return array($r, $g, $b);
}

/*function save_image($url) {

        $img = "small_covers/$isbn.jpg";

        // before saving the image, check if it already exists
        if (file_exists($img)) {
            return $img;
        }

        file_put_contents($img, file_get_contents($url));
        return $img;

}*/


/*
 * // loop through the result and update the 'average_color' column with the average color of the book cover
while ($row = $result->fetch_assoc()) {
    $isbn = $row['isbn'];
    $average_color = implode(",", $average_colors[$isbn]);
    $sql = "UPDATE table_name SET average_color='$average_color' WHERE isbn='$isbn'"; // replace table_name with the actual table name
    $con2->query($sql);
}
 */

