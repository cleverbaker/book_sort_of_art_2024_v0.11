<?php

// connect to database and get the table data
$server_name = ''; // replace with the actual server name
$port     = 1; // replace with the actual port number
$socket   = ""; // replace with the actual socket
$database_name = ""; // replace with the actual database name
$user = ""; // replace with the actual username
$pass = "";

// get the table data
$con = new mysqli($server_name, $user, $pass, $database_name, $port, $socket)
or die ('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT * FROM table_name"; // replace table_name with the actual table name
$result = $con->query($sql);

// initialize variable table_weight to 0
$table_weight = 0;

// initialize empty html_books string
$html_books = "";

// in a html table, display the data of isbn, title, height, length, and weight
echo "<table border='1'>";
echo "<tr>";
echo "<th>ISBN</th>";
echo "<th>Title</th>";
echo "<th>Height</th>";
echo "<th>Length</th>";
echo "<th>Weight</th>";
echo "</tr>";

// loop through the result and display the values of isbn, title, height, length, and weight
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['isbn'] . "</td>";
    echo "<td>" . $row['title'] . "</td>";
    echo "<td>" . $row['height'] . "</td>";
    echo "<td>" . $row['width'] . "</td>";
    echo "<td>" . $row['weight'] . "</td>";
    echo "</tr>";

    // add the weight of the current row to the table_weight
    $table_weight += $row['weight'];

    // initialize width variable to be the minimum of the length and width of the book, unless that number is less
    // than 0.01
    $width = min($row['length'], $row['width']);
    if ($width < 0.001) {
        $width = max($row['length'], $row['width']);
    }

    // if height value exits, add the book element
    if ($row['height'] > 0) {
        // add the div element to the html_books string
        $html_books .= create_book_div($row['height'], $width, $row['title'], $row['author'], $row['isbn'], $row['cover_img'], $row['average_color']);
    }
}

echo "</table>";

// display the total weight of the books
echo "<h2>Total weight of the books: $table_weight</h2>";

// add style to .book class
echo <<<STYLE0
    <style> .book {
                    border: 1px solid black; 
                    display:inline-block; 
                    border-radius: 1px 1px 0 0 !important;
                    position: relative;
                    text-align: center;
             }
             
             .book-details {
                display: none;
                position: absolute;
                top: calc(100% + 12px);
                margin-left: -50%;
             }
             
             .book:hover .book-details {
                display: inline-block;
                max-width: 300px;
                max-height: 300px;
                overflow: hidden;
             }
             
             .book-details img {
                max-width: 100%;
                max-height: 100%;
             }
     </style>
STYLE0;

// display the books in a row
echo "<div style='margin-top:20px;'>";
echo $html_books;
echo "</div>";


// create an HTML div element with height of the height value of the book and the width of the length value of the book
function create_book_div($height, $width, $title, $author, $isbn, $cover_img, $average_color) {
    $height = $height * 8;
    $width = $width * 8;
    return "<div style='height:{$height}px; width:{$width}px; background-color: rgb{$average_color}; margin: auto 2px 0; border-top-left-radius: 3px; border-top-right-radius: 3px; border-width: 1px' class='book'>".create_book_details_div($title, $author, $isbn, $cover_img)."</div>";
}

// create a function that creates a HTML div element with the title of the book, author, ISBN, and cover image
function create_book_details_div($title, $author, $isbn, $cover_img) {
    return "<div style='min-height:200px; min-width:200px; background-color:lightgoldenrodyellow; margin: auto 2px 0; border-width: 1px' class='book-details'><h3>$title</h3><p>$author</p><p>$isbn</p><img src='$cover_img' alt='cover image'></div>";
}

$con->close();